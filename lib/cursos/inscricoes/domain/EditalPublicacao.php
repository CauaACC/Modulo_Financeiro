<?php

require_once __DIR__ . '/../lib/vendor/autoload.php';

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\SimpleType\JcTable;

/**
 * EditalPublicacao
 *
 * Helper centralizado pra geracao do edital de publicacao de resultado de
 * uma etapa de avaliacao. Cada fase (1, 2, Final) tem sua propria regra
 * (titulo, intro, colunas, criterios de desempate) configurada em
 * CONFIG_FASES — assim quando uma nova fase entrar, basta adicionar um item.
 *
 * Fluxo de uso:
 *   $obj = new EditalPublicacao();
 *   $obj->gerarDOCX(2, 'todos');   // streaming direto do download
 *
 * Equivalente ao Python GerarAvaliacaoFase2_129.py + GerarAvaliacaoFase2PDF_129.py,
 * porem totalmente embutido no fluxo do admin, sem exportar CSV intermediario.
 */
class EditalPublicacao extends EditalPublicacaoVO
{
    private $objDAO;

    /**
     * Mapa de rank de titulacao (mesma tabela usada no GerarClassificacao do 115).
     * Usado pro desempate dos DEFERIDOs. Sao consultadas as chaves do
     * avaliacao_json->itens[chave]->qtd: se qtd>0 na primeira chave (de cima
     * pra baixo), retorna o rank correspondente.
     */
    const TITULACAO_RANK = array(
        '1.9.2' => 90,
        '1.9.1' => 80,
        '1.8.2' => 70,
        '1.8.1' => 60,
        '1.7.2' => 50,
        '1.7.1' => 40,
        '1.4.1' => 30,
        '1.4.2' => 30,
        '1.3'   => 20,
        '1.1'   => 10,
    );

    /**
     * Categorias do avaliacao_json que contam como meses de experiencia.
     */
    const EXPERIENCIA_CHAVES = array('3.1', '3.2', '3.3', '3.4');

    /**
     * Configuracao por fase. Adicione uma entrada quando uma nova etapa virar
     * edital publicavel.
     *
     * - titulo: aceita {num_edital} (substituido por getNumeroEdital())
     * - intro: paragrafo abaixo do titulo
     * - rodape_totais: padrao "Total de inscritos: N | Deferidos: X | Indeferidos: Y"
     */
    public static function getConfigFases()
    {
        // 'fase_pre_requisito': numero da fase anterior cuja habilitacao final
        // (H) eh pre-requisito pra aparecer neste edital. null = sem pre-req
        // (todos os inscritos elegiveis, ex: Fase 1).
        return array(
            1 => array(
                'titulo'   => 'RESULTADO DA AVALIA\xc3\x87\xc3\x83O INICIAL (FASE 1) \xe2\x80\x93 EDITAL DE PROCESSO SELETIVO N\xc2\xba {num_edital}',
                'intro'    => '', // preencher quando publicar Fase 1 via este modulo
                'colunas'  => array('seq', 'id_publico', 'nome', 'pontos', 'situacao'),
                'fase_pre_requisito' => null,
            ),
            2 => array(
                'titulo'   => "RESULTADO DA AVALIA\xc3\x87\xc3\x83O DE DOCUMENTOS (FASE 2) \xe2\x80\x93 EDITAL DE PROCESSO SELETIVO N\xc2\xba {num_edital}",
                'intro'    => "Processo seletivo para contrata\xc3\xa7\xc3\xa3o de profissionais e forma\xc3\xa7\xc3\xa3o de cadastro de reserva para atua\xc3\xa7\xc3\xa3o no Programa de Resposta Estrat\xc3\xa9gica \xc3\xa0 Neurodiversidade na Educa\xc3\xa7\xc3\xa3o \xe2\x80\x93 Pr\xc3\xb3-Rede",
                'colunas'  => array('seq', 'id_publico', 'nome', 'pontos', 'situacao'),
                'fase_pre_requisito' => 1,
            ),
            3 => array(
                'titulo'   => "RESULTADO FINAL \xe2\x80\x93 EDITAL DE PROCESSO SELETIVO N\xc2\xba {num_edital}",
                'intro'    => "Processo seletivo para contrata\xc3\xa7\xc3\xa3o de profissionais e forma\xc3\xa7\xc3\xa3o de cadastro de reserva para atua\xc3\xa7\xc3\xa3o no Programa de Resposta Estrat\xc3\xa9gica \xc3\xa0 Neurodiversidade na Educa\xc3\xa7\xc3\xa3o \xe2\x80\x93 Pr\xc3\xb3-Rede",
                'colunas'  => array('classificacao', 'id_publico', 'nome', 'pontos', 'situacao'),
                'fase_pre_requisito'   => null,
                'ordem_etapa_origem'   => 2,    // le os registros da etapa 2 (consolidada)
                'usa_campos_finais'    => true, // usa pontos_total_final / classificacao_status_final / classificacao_final
            ),
        );
    }

    /**
     * Numero do edital por evento. Hardcoded por enquanto — pode virar coluna
     * em tab_eventos_configuracao quando precisar generalizar.
     */
    private function getNumeroEdital($codEvento)
    {
        $mapa = array(
            115 => '001/2025',
            129 => '003/2026',
        );
        return isset($mapa[$codEvento]) ? $mapa[$codEvento] : '___/____';
    }

    /**
     * Numero de vagas convocadas no edital final. Os primeiros N deferidos
     * recebem situacao "Convocado"; os demais ficam como "Cad.Reserva".
     * 0 = todos os deferidos viram Cad.Reserva (caso 003/2026).
     *
     * Publico estatico para que a area do inscrito tambem use a mesma regra
     * (sem duplicar o mapa de vagas por evento).
     */
    public static function getNumeroVagasConvocados($codEvento)
    {
        $mapa = array(
            115 => 0,
            129 => 0,
        );
        return isset($mapa[$codEvento]) ? (int) $mapa[$codEvento] : 0;
    }

    public function __construct()
    {
        $this->setCodEvento(COD_EVENTO);
        $this->objDAO = new EditalPublicacaoDAO();
    }

    /**
     * Acessores de lista (PadraoVO ja resolve set/get via __call,
     * mas explicitamos pra clareza).
     */
    public function setListaEditais(array $lista) { $this->listaEditais = $lista; }
    public function getListaEditais()             { return isset($this->listaEditais) ? $this->listaEditais : array(); }

    /**
     * Le do banco e devolve a lista crua (uma EditalPublicacao por linha).
     *
     * A config da fase pode trazer flags especiais que mudam o SQL:
     *   - 'ordem_etapa_origem': le os registros desta outra ordem (ex.: Fase Final
     *                          le da etapa 2 onde os _final foram consolidados)
     *   - 'usa_campos_finais': le pontos_total_final / classificacao_status_final
     *                          / classificacao_final em vez de fazer COALESCE
     */
    public function carregarListaEdital($ordemEtapa, $filtro = 'todos', $debug = false)
    {
        $configs = self::getConfigFases();
        $cfg = isset($configs[$ordemEtapa]) ? $configs[$ordemEtapa] : array();
        $this->objDAO->carregarListaEdital($this, $ordemEtapa, $filtro, $cfg, $debug);
        return $this->getListaEditais();
    }

    /**
     * Processa a lista crua aplicando regras de classificacao e desempate:
     *   - Marca situacao DEFERIDO/INDEFERIDO conforme classificacao_status
     *   - Calcula nivel_titulacao e meses_experiencia a partir do avaliacao_json
     *   - Ordena DEFERIDOs por pontos DESC, titulacao DESC, experiencia DESC, idade DESC
     *   - INDEFERIDOs ordenados por nome
     *   - Numera classificacao apenas dos DEFERIDOs
     *   - Numera seq de 1..N pra todos
     *
     * Quando $usaCamposFinais=true (Fase Final), pula o calculo de desempate
     * e usa a classificacao_final ja consolidada no banco para ordenar.
     *
     * Retorna a lista processada (substitui a lista interna).
     */
    public function processarLista($usaCamposFinais = false)
    {
        $lista = $this->getListaEditais();

        $deferidos = array();
        $indeferidos = array();

        foreach ($lista as $linha) {
            $statusH = ($linha->getClassificacaoStatus() === 'H');
            $linha->setSituacao($statusH ? 'DEFERIDO' : ($linha->getCodAvaliacao() ? 'INDEFERIDO' : 'PENDENTE'));

            if ($usaCamposFinais) {
                // Classificacao ja vem consolidada do banco em $linha->classificacao
                // (mapeada via classificacao_final AS classificacao no SELECT).
            } else {
                $avJson = $this->parseAvaliacaoJson($linha->getAvaliacaoJson());
                $linha->setNivelTitulacao($statusH ? $this->calcTitulacao($avJson) : 0);
                $linha->setMesesExperiencia($statusH ? $this->calcExperiencia($avJson) : 0);
            }

            if ($statusH) {
                $deferidos[] = $linha;
            } else {
                $indeferidos[] = $linha;
            }
        }

        if ($usaCamposFinais) {
            // Ja consolidado — ordena pela classificacao_final (deferidos) e
            // indeferidos por nome. Nao recalcula desempate.
            usort($deferidos, function ($a, $b) {
                return ((int) $a->getClassificacao()) <=> ((int) $b->getClassificacao());
            });
        } else {
            usort($deferidos, function ($a, $b) {
                // 4 criterios de desempate em ordem decrescente
                $cmp = ((float)$b->getPontos()) <=> ((float)$a->getPontos());
                if ($cmp !== 0) return $cmp;
                $cmp = ((int)$b->getNivelTitulacao()) <=> ((int)$a->getNivelTitulacao());
                if ($cmp !== 0) return $cmp;
                $cmp = ((int)$b->getMesesExperiencia()) <=> ((int)$a->getMesesExperiencia());
                if ($cmp !== 0) return $cmp;
                return ((int)$b->getIdade()) <=> ((int)$a->getIdade());
            });

            $classif = 0;
            foreach ($deferidos as $linha) {
                $linha->setClassificacao(++$classif);
            }
        }

        usort($indeferidos, function ($a, $b) {
            return strcasecmp((string)$a->getNomeInscrito(), (string)$b->getNomeInscrito());
        });

        $final = array_merge($deferidos, $indeferidos);
        $seq = 0;
        foreach ($final as $linha) {
            $linha->setSeq(++$seq);
        }

        $this->setListaEditais($final);
        return $final;
    }

    /**
     * Stream do DOCX (envia direto pro browser, sem salvar disco).
     * Headers HTTP devem nao ter sido enviados antes desta chamada.
     */
    public function gerarDOCX($ordemEtapa, $filtro = 'todos', $debug = false)
    {
        $configs = self::getConfigFases();
        if (!isset($configs[$ordemEtapa])) {
            throw new Exception("Fase " . htmlspecialchars($ordemEtapa) . " nao tem configuracao de edital cadastrada.");
        }
        $cfg = $configs[$ordemEtapa];

        $usaCamposFinais = !empty($cfg['usa_campos_finais']);
        $mostrarColunaClassif = in_array('classificacao', $cfg['colunas'], true);

        $this->carregarListaEdital($ordemEtapa, $filtro, $debug);
        $lista = $this->processarLista($usaCamposFinais);

        // Conta totais
        $totalDef = 0; $totalInd = 0; $totalPend = 0;
        foreach ($lista as $linha) {
            if ($linha->getSituacao() === 'DEFERIDO') $totalDef++;
            elseif ($linha->getSituacao() === 'INDEFERIDO') $totalInd++;
            else $totalPend++;
        }
        $total = count($lista);

        $titulo = str_replace('{num_edital}', $this->getNumeroEdital($this->getCodEvento()), $cfg['titulo']);

        // ============================================================
        // Monta DOCX (PhpWord)
        // ============================================================
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Helvetica');
        $phpWord->setDefaultFontSize(10);

        $section = $phpWord->addSection(array(
            'marginTop'    => 720,  // 1.27cm em twips (720)
            'marginBottom' => 720,
            'marginLeft'   => 720,
            'marginRight'  => 720,
            'pageNumberingStart' => 2,  // continua o edital institucional (pagina 1)
        ));

        // Titulo
        $section->addText(
            $titulo,
            array('bold' => true, 'size' => 12),
            array('alignment' => Jc::CENTER, 'spaceAfter' => 120)
        );

        // Intro
        if (!empty($cfg['intro'])) {
            $section->addText(
                $cfg['intro'],
                array('size' => 10),
                array('alignment' => Jc::CENTER, 'spaceAfter' => 240)
            );
        }

        // Tabela
        $tbl = $section->addTable(array(
            'borderSize'  => 6,
            'borderColor' => '000000',
            'cellMargin'  => 50,
            'alignment'   => JcTable::CENTER,
            'width'       => 100 * 50, // 100% via pct (50 = 1%)
            'unit'        => 'pct',
        ));

        // Header da tabela (repete em cada pagina)
        $headerStyle = array('bold' => true, 'size' => 9);
        $headerCellStyle = array('bgColor' => 'D9D9D9', 'valign' => 'center');
        $headerRowOpts = array('tblHeader' => true, 'cantSplit' => true);

        // Fase Final ($mostrarColunaClassif=true): colunas
        //   Seq | Id Publico | Nome | Pontuacao | Classificacao | Situacao
        // Fase 1/2 ($mostrarColunaClassif=false): colunas
        //   Seq | Id Publico | Nome | Pontuacao | Situacao
        $larguraNome = $mostrarColunaClassif ? 3700 : 4800;
        $vagasConvocados = $mostrarColunaClassif ? self::getNumeroVagasConvocados($this->getCodEvento()) : 0;

        $tbl->addRow(400, $headerRowOpts);
        $tbl->addCell(700,  $headerCellStyle)->addText('Seq',              $headerStyle, array('alignment' => Jc::CENTER));
        $tbl->addCell(1500, $headerCellStyle)->addText("Id P\xc3\xbablico", $headerStyle, array('alignment' => Jc::CENTER));
        $tbl->addCell($larguraNome, $headerCellStyle)->addText('Nome Candidato', $headerStyle, array('alignment' => Jc::START));
        $tbl->addCell(1300, $headerCellStyle)->addText("Pontua\xc3\xa7\xc3\xa3o", $headerStyle, array('alignment' => Jc::CENTER));
        if ($mostrarColunaClassif) {
            $tbl->addCell(1100, $headerCellStyle)->addText("Classifica\xc3\xa7\xc3\xa3o", $headerStyle, array('alignment' => Jc::CENTER));
        }
        $tbl->addCell(1700, $headerCellStyle)->addText("Situa\xc3\xa7\xc3\xa3o",   $headerStyle, array('alignment' => Jc::CENTER));

        // Linhas
        $rowStyle = array('size' => 9);
        foreach ($lista as $linha) {
            $isDef = ($linha->getSituacao() === 'DEFERIDO');

            // Pontuacao
            if ($mostrarColunaClassif) {
                // Edital final: deferidos com pontos formatados (139.00), indeferidos em branco
                $pontosFmt = $isDef ? number_format((float)$linha->getPontos(), 2, '.', '') : '';
            } else {
                $pontosFmt = $isDef ? number_format((float)$linha->getPontos(), 2, ',', '.') : '-';
            }

            // Classificacao (so na Fase Final): numero pros deferidos, 'Inabilitado' pros indeferidos
            $classifFmt = '';
            if ($mostrarColunaClassif) {
                if ($isDef && $linha->getClassificacao() !== '' && $linha->getClassificacao() !== null) {
                    $classifFmt = (string) $linha->getClassificacao();
                } else {
                    $classifFmt = 'Inabilitado';
                }
            }

            // Situacao
            if ($mostrarColunaClassif) {
                if ($isDef) {
                    $pos = (int) $linha->getClassificacao();
                    $situacaoFmt = ($vagasConvocados > 0 && $pos > 0 && $pos <= $vagasConvocados)
                        ? 'Convocado'
                        : 'Cad.Reserva';
                } else {
                    $situacaoFmt = 'Inabilitado';
                }
            } else {
                $situacaoFmt = $linha->getSituacao();
            }

            $tbl->addRow(300, array('cantSplit' => true));
            $tbl->addCell(700)->addText($linha->getSeq(),                $rowStyle, array('alignment' => Jc::CENTER));
            $tbl->addCell(1500)->addText($linha->getIdPublico(),         $rowStyle, array('alignment' => Jc::CENTER));
            $tbl->addCell($larguraNome)->addText($linha->getNomeInscrito(), $rowStyle);
            $tbl->addCell(1300)->addText($pontosFmt,                     $rowStyle, array('alignment' => Jc::END));
            if ($mostrarColunaClassif) {
                $tbl->addCell(1100)->addText($classifFmt,                $rowStyle, array('alignment' => Jc::CENTER));
            }
            $tbl->addCell(1700)->addText($situacaoFmt,                   $rowStyle, array('alignment' => Jc::CENTER));
        }

        // Rodape de totais (terminologia diferente na Fase Final)
        if ($mostrarColunaClassif) {
            // Conta Convocados x Cad.Reserva dentro dos deferidos
            $totalConv = 0;
            $totalCR   = 0;
            foreach ($lista as $linha) {
                if ($linha->getSituacao() !== 'DEFERIDO') continue;
                $pos = (int) $linha->getClassificacao();
                if ($vagasConvocados > 0 && $pos > 0 && $pos <= $vagasConvocados) {
                    $totalConv++;
                } else {
                    $totalCR++;
                }
            }
            $rodapeTexto = sprintf(
                "Total de candidatos: %d   |   Convocados: %d   |   Cadastro de Reserva: %d   |   Inabilitados: %d",
                $total, $totalConv, $totalCR, $totalInd
            );
        } else {
            $rodapeTexto = sprintf(
                "Total de inscritos: %d   |   Deferidos: %d   |   Indeferidos: %d",
                $total, $totalDef, $totalInd
            );
        }
        $section->addText(
            $rodapeTexto,
            array('bold' => true, 'size' => 10),
            array('spaceBefore' => 300)
        );

        // Numeracao de paginas no rodape (alinhada a direita).
        // {PAGE} usa pageNumberingStart configurado no section (comeca em 2).
        // O field NUMPAGES eh substituido por uma formula "= NUMPAGES + 1"
        // apos o save do PhpWord (ver final deste metodo). Isso soma a pagina 1
        // do edital institucional (PDF assinado, anexado em separado) ao total.
        $footer = $section->addFooter();
        $footer->addPreserveText(
            "P\xc3\xa1gina {PAGE} de {NUMPAGES}",
            array('size' => 9),
            array('alignment' => Jc::END)
        );

        // ============================================================
        // Envia pro browser
        // ============================================================
        $codEvento  = (int) $this->getCodEvento();
        $numEdital  = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->getNumeroEdital($codEvento));
        $nomeArq    = sprintf('edital_%s_evento_%d_fase%d.docx', $numEdital, $codEvento, $ordemEtapa);

        // Limpa qualquer output buffering pendente (rotas as vezes deixam BOM)
        while (ob_get_level() > 0) { ob_end_clean(); }

        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment; filename="' . $nomeArq . '"');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Salva em arquivo temporario para injetar a formula NUMPAGES+1 no footer.
        // O PhpWord nao tem field tipo FORMULA nativo nem suporta field aninhado em
        // addPreserveText. Solucao: pos-processamos o XML do footer dentro do ZIP.
        $tmpFile = tempnam(sys_get_temp_dir(), 'edital_') . '.docx';
        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($tmpFile);

        if (class_exists('ZipArchive')) {
            $zip = new \ZipArchive();
            if ($zip->open($tmpFile) === true) {
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $name = $zip->getNameIndex($i);
                    if (strpos($name, 'word/footer') === 0 && substr($name, -4) === '.xml') {
                        $xml = $zip->getFromIndex($i);
                        // Substitui o field "NUMPAGES" puro por "= NUMPAGES + 1 \* MERGEFORMAT"
                        // (mantem outros instrTexts intactos com lookahead simples)
                        $xml = str_replace(
                            '<w:instrText xml:space="preserve">NUMPAGES</w:instrText>',
                            '<w:instrText xml:space="preserve">= NUMPAGES + 1 \* MERGEFORMAT</w:instrText>',
                            $xml
                        );
                        $zip->addFromString($name, $xml);
                    }
                }
                $zip->close();
            }
        }

        readfile($tmpFile);
        @unlink($tmpFile);
        exit();
    }

    private function parseAvaliacaoJson($raw)
    {
        if (!$raw) return null;
        $s = str_replace('\\"', '"', (string)$raw);
        $decoded = json_decode($s, true);
        return is_array($decoded) ? $decoded : null;
    }

    private function calcTitulacao($avJson)
    {
        if (!is_array($avJson) || !isset($avJson['itens'])) return 0;
        $itens = $avJson['itens'];
        foreach (self::TITULACAO_RANK as $chave => $rank) {
            $qtd = isset($itens[$chave]['qtd']) ? (int)$itens[$chave]['qtd'] : 0;
            if ($qtd > 0) return $rank;
        }
        return 0;
    }

    private function calcExperiencia($avJson)
    {
        if (!is_array($avJson) || !isset($avJson['itens'])) return 0;
        $itens = $avJson['itens'];
        $total = 0;
        foreach (self::EXPERIENCIA_CHAVES as $chave) {
            $qtd = isset($itens[$chave]['qtd']) ? (int)$itens[$chave]['qtd'] : 0;
            $total += $qtd;
        }
        return $total;
    }
}

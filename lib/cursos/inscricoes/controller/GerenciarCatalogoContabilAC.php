<?php

/**
 * Configuracao inicial da contabilidade de um evento: popula plano de contas,
 * rateios, centro de custo e fontes de recurso sem cadastro item a item.
 *
 * Duas origens:
 *   1) COPIAR DE OUTRO EVENTO do mesmo gestor -- o caso normal, ja que o
 *      catalogo costuma ser o mesmo dentro do gestor. Os registros copiados
 *      sao registros comuns, entao o evento segue livre para personalizar.
 *   2) MODELO PADRAO -- monta o catalogo base do zero, para quando nao existe
 *      nenhum evento anterior para copiar.
 *
 * Nada e sobrescrito: o que ja existe no evento e preservado e a copia so
 * acrescenta o que falta (comparando pelo codigo contabil / nome).
 */
class GerenciarCatalogoContabilAC {

    /**
     * Catalogo base (estrutura dos balancetes da fundacao). Serve so como
     * semente do "modelo padrao" -- depois de criado, vira cadastro comum e
     * cada evento personaliza o que quiser.
     */
    private $modeloContas = array(
        // codigo, nome, tipo, natureza, aceita_lancamento, codigo do pai
        array('01',   'RECEITAS',                            'RECEITA', 'CREDITO', 0, null),
        array('0106', 'CURSOS E EVENTOS',                    'RECEITA', 'CREDITO', 1, '01'),
        array('0112', 'RECEITAS FINANCEIRAS',                'RECEITA', 'CREDITO', 1, '01'),
        array('0119', 'TAXA HUTEC',                          'RECEITA', 'DEBITO',  1, '01'),
        array('0130', 'TAXA DIRECAO (CENTRO/DEPARTAMENTO)',  'RECEITA', 'DEBITO',  1, '01'),
        array('02',   'DESPESAS',                            'DESPESA', 'DEBITO',  0, null),
        // Rubricas levantadas dos balancetes reais da fundacao (codigo e nome
        // conforme aparecem la), para o balancete daqui sair no mesmo formato.
        array('0209', 'BOLSA',                               'DESPESA', 'DEBITO',  1, '02'),
        array('0210', 'COFINS CSLL PIS',                     'DESPESA', 'DEBITO',  1, '02'),
        array('0217', 'HOSPEDAGEM',                          'DESPESA', 'DEBITO',  1, '02'),
        array('0218', 'DESP. C/ REFEICOES E COFFEE BREAK',   'DESPESA', 'DEBITO',  1, '02'),
        array('0226', 'DESP. C/ PASSAGENS',                  'DESPESA', 'DEBITO',  1, '02'),
        array('0227', 'DESP. C/ SEGUROS',                    'DESPESA', 'DEBITO',  1, '02'),
        array('0230', 'DESPESAS BANCARIAS',                  'DESPESA', 'DEBITO',  1, '02'),
        array('0249', 'INSS',                                'DESPESA', 'DEBITO',  1, '02'),
        array('0255', 'IRRF',                                'DESPESA', 'DEBITO',  1, '02'),
        array('0256', 'ISS',                                 'DESPESA', 'DEBITO',  1, '02'),
        array('0258', 'LOCACAO',                             'DESPESA', 'DEBITO',  1, '02'),
        array('0271', 'RESSARCIMENTO',                       'DESPESA', 'DEBITO',  1, '02'),
        array('0273', 'ST-PESSOA FISICA',                    'DESPESA', 'DEBITO',  1, '02'),
        array('0277', 'SERVICOS DE TERCEIROS',               'DESPESA', 'DEBITO',  1, '02'),
        array('0280', 'TRANSFERENCIAS (DESPESAS)',           'DESPESA', 'DEBITO',  1, '02'),
        array('0283', 'DESP. E DEV. C/ INSCRICAO EM CURSOS', 'DESPESA', 'DEBITO',  1, '02'),
        array('0297', 'DESP. C/ CERTIFICADO',                'DESPESA', 'DEBITO',  1, '02'),
    );

    private $modeloFontes = array('PAGSEGURO', 'ITAU', 'CAIXA');

    /** meio de pagamento, percentual, valor fixo */
    /**
     * Deducoes institucionais, medidas nos balancetes da fundacao:
     * TAXA HUTEC 10% da receita em todos os eventos; e, quando ha
     * co-organizacao, um bloco de direcao de 10% fatiado entre os
     * departamentos (5% cada). Estruturado como rateio pai -> filhos, que e
     * como a tela de Rateios trabalha.
     * Formato: nome, percentual, rubrica de destino, nome do rateio pai.
     */
    private $modeloInstitucional = array(
        array('TAXA HUTEC',         10.00, '0119', null),
        array('TAXA DIRECAO UEL',   10.00, '0130', null),
        array('TAXA DIR. CCS',       5.00, '0130', 'TAXA DIRECAO UEL'),
        array('TAXA DIR. FISIO',     5.00, '0130', 'TAXA DIRECAO UEL'),
    );

    private $modeloTaxas = array(
        array('PIX',            1.89, null),
        array('CARTAO_CREDITO', 3.99, null),
        array('CARTAO',         3.99, null),
        array('BOLETO',         3.99, 1.00),
    );

    private $conex;
    private $codEvento;
    private $codGestor;

    function __construct() {
        FWA_exigirPermissao([enumSistema::SIS_GERENCIAR_ADM_GERAL, enumSistema::SIS_CONTROLE_FINANCEIRO]);
        ConexaoDB::getInstance();
        $this->conex = ConexaoDB::getConexaoDB();
        $this->codEvento = COD_EVENTO;
        $this->codGestor = $this->resolverGestor();
    }

    // ------------------------------------------------------------------ acoes

    /**
     * Situacao do evento atual + eventos do mesmo gestor que podem servir de
     * origem (com a contagem de cada bloco, pra escolher com criterio).
     */
    public function listarOrigens($fws_, $debug = false) {
        $ok = false;
        $msg = '';
        $dados = array();

        try {
            $dados = array(
                'atual'   => $this->contarCatalogo($this->codEvento),
                'origens' => $this->carregarOrigens(),
                'gestor'  => $this->codGestor,
            );
            $ok = true;
        } catch (Exception $e) {
            $msg = $e->getMessage();
        }

        $this->responder($ok, $msg, $dados);
    }

    /**
     * Detalha o que cada origem vai criar, para a tela mostrar antes de aplicar.
     */
    public function detalharOrigem($fws_, $debug = false) {
        $ok = false;
        $msg = '';
        $dados = array();

        try {
            $origem = intval($fws_['codEventoOrigem']);
            $dados = $origem ? $this->detalharEvento($origem) : $this->detalharModelo();
            $ok = true;
        } catch (Exception $e) {
            $msg = $e->getMessage();
        }

        $this->responder($ok, $msg, $dados);
    }

    /** O que existe no evento de origem, item a item. */
    private function detalharEvento($origem) {
        $contas = array();
        $sql = sprintf("SELECT codigo_contabil, nome_plano_contas, tipo_conta, aceita_lancamento
                        FROM fin_plano_contas WHERE cod_evento = %d AND status = 'ATIVADA'
                        ORDER BY codigo_contabil", $origem);
        $rs = $this->conex->Execute($sql);
        if ($rs) {
            foreach ($rs as $r) {
                $contas[] = trim($r['codigo_contabil'] . ' ' . $this->paraUtf8($r['nome_plano_contas']))
                          . ($r['aceita_lancamento'] ? '' : ' (grupo)');
            }
        }

        $rateios = array();
        $sql = sprintf("SELECT nome_rateio, percentual, valor_fixo, meio_pagamento
                        FROM fin_rateios WHERE cod_evento = %d AND status = 'ATIVADA'
                        ORDER BY cod_rateio", $origem);
        $rs = $this->conex->Execute($sql);
        if ($rs) {
            foreach ($rs as $r) {
                $txt = $this->paraUtf8($r['nome_rateio']) . ' — ' . rtrim(rtrim(number_format($r['percentual'], 2, ',', '.'), '0'), ',') . '%';
                if ($r['valor_fixo'] > 0) {
                    $txt .= ' + R$ ' . number_format($r['valor_fixo'], 2, ',', '.');
                }
                $rateios[] = $txt;
            }
        }

        $fontes = array();
        $sql = sprintf("SELECT nome_fontes_recurso FROM fin_fontes_recurso
                        WHERE cod_evento = %d AND status = 'ATIVADA'", $origem);
        $rs = $this->conex->Execute($sql);
        if ($rs) {
            foreach ($rs as $r) {
                $fontes[] = $this->paraUtf8($r['nome_fontes_recurso']);
            }
        }

        return array(
            'titulo'  => 'Ser&aacute; copiado do evento ' . $origem,
            'contas'  => $contas,
            'rateios' => $rateios,
            'fontes'  => $fontes,
            'centro'  => array($this->paraUtf8($this->nomeDoEvento()) . '  (c&oacute;digo do projeto em branco, para preencher depois)'),
            'institucional' => array(),
        );
    }

    /** O que o modelo padrao cria, item a item. */
    private function detalharModelo() {
        $contas = array();
        foreach ($this->modeloContas as $m) {
            $contas[] = $m[0] . ' ' . $m[1] . ($m[4] ? '' : ' (grupo)');
        }
        $rateios = array();
        foreach ($this->modeloTaxas as $t) {
            $txt = 'Taxa ' . $t[0] . ' — ' . number_format($t[1], 2, ',', '.') . '%';
            if ($t[2]) {
                $txt .= ' + R$ ' . number_format($t[2], 2, ',', '.');
            }
            $rateios[] = $txt;
        }
        $inst = array();
        foreach ($this->modeloInstitucional as $it) {
            $inst[] = ($it[3] ? '    ' : '') . $it[0] . ' — ' . number_format($it[1], 2, ',', '.') . '%'
                    . ' (rubrica ' . $it[2] . ')' . ($it[3] ? ' dentro de ' . $it[3] : '');
        }
        return array(
            'perfil'  => array('Fornecedor  (perfil do evento, para cadastrar quem recebe pagamento)'),
            'titulo'  => 'Ser&aacute; criado pelo modelo padr&atilde;o',
            'contas'  => $contas,
            'rateios' => $rateios,
            'fontes'  => $this->modeloFontes,
            'centro'  => array($this->paraUtf8($this->nomeDoEvento()) . '  (c&oacute;digo do projeto em branco, para preencher depois)'),
            'institucional' => $inst,
        );
    }

    /**
     * Copia os blocos escolhidos de um evento de origem para o evento atual.
     */
    public function copiarDeEvento($fws_, $debug = false) {
        $ok = false;
        $msg = '';
        $resumo = array();

        try {
            $origem = intval($fws_['codEventoOrigem']);
            if (!$origem) {
                throw new Exception('Escolha o evento de origem.');
            }
            if ($origem == $this->codEvento) {
                throw new Exception('O evento de origem tem que ser diferente deste evento.');
            }
            $blocos = $this->blocosEscolhidos($fws_);
            $resumo = $this->executarCopia($origem, $blocos, $debug);
            $ok = true;
            $msg = $this->mensagemResumo($resumo);
        } catch (Exception $e) {
            $msg = $e->getMessage();
        }

        $this->responder($ok, $msg, $resumo);
    }

    /**
     * Cria o catalogo base do zero no evento atual.
     */
    public function aplicarModeloPadrao($fws_, $debug = false) {
        $ok = false;
        $msg = '';
        $resumo = array();

        try {
            $blocos = $this->blocosEscolhidos($fws_);
            $resumo = $this->executarModelo($blocos, $debug);
            $ok = true;
            $msg = $this->mensagemResumo($resumo);
        } catch (Exception $e) {
            $msg = $e->getMessage();
        }

        $this->responder($ok, $msg, $resumo);
    }

    // ------------------------------------------------------------------ copia

    private function executarCopia($origem, $blocos, $debug = false) {
        $resumo = array('contas' => 0, 'centro' => 0, 'fontes' => 0, 'rateios' => 0);
        $mapaContas = $this->mapaContasExistentes($this->codEvento);
        $codCentro = $this->primeiroCentro($this->codEvento);

        // 1) plano de contas -- pais primeiro, depois amarra a hierarquia
        if (!empty($blocos['contas'])) {
            $sql = sprintf("SELECT cod_plano_contas, cod_plano_contas_pai, nome_plano_contas, codigo_contabil,
                                   descricao, tipo_conta, natureza_saldo, aceita_lancamento
                            FROM fin_plano_contas
                            WHERE cod_evento = %d AND status = 'ATIVADA'
                            ORDER BY (cod_plano_contas_pai IS NOT NULL), codigo_contabil", $origem);
            $rs = $this->conex->Execute($sql);
            $deParaAntigo = array();   // cod antigo -> codigo_contabil
            $novos = array();          // codigo_contabil -> cod novo

            if ($rs) {
                foreach ($rs as $r) {
                    $codigo = trim($r['codigo_contabil']);
                    $deParaAntigo[$r['cod_plano_contas']] = $codigo;

                    if ($codigo !== '' && isset($mapaContas[$codigo])) {
                        $novos[$codigo] = $mapaContas[$codigo];   // ja existe aqui
                        continue;
                    }
                    $novoId = $this->inserirConta($r, $debug);
                    if ($novoId) {
                        $resumo['contas']++;
                        if ($codigo !== '') {
                            $novos[$codigo] = $novoId;
                            $mapaContas[$codigo] = $novoId;
                        }
                        $deParaAntigo['novo_' . $r['cod_plano_contas']] = $novoId;
                    }
                }
                // hierarquia: agora que todos existem, aponta cada filho ao pai
                $this->amarrarHierarquia($origem, $deParaAntigo, $novos, $debug);
            }
        }

        // 2) centro de custo -- criado com o NOME DESTE evento (decisao do
        //    usuario); o codigo contabil fica em branco pra informar o numero
        //    do projeto depois
        if (!empty($blocos['centro']) && !$codCentro) {
            $codCentro = $this->criarCentroDoEvento($debug);
            if ($codCentro) {
                $resumo['centro']++;
            }
        }

        // 3) fontes de recurso
        if (!empty($blocos['fontes'])) {
            $sql = sprintf("SELECT nome_fontes_recurso, descricao, tipo_fontes_recurso
                            FROM fin_fontes_recurso WHERE cod_evento = %d AND status = 'ATIVADA'", $origem);
            $rs = $this->conex->Execute($sql);
            if ($rs) {
                foreach ($rs as $r) {
                    if ($this->inserirFonte($r['nome_fontes_recurso'], $r['descricao'], $r['tipo_fontes_recurso'], $debug)) {
                        $resumo['fontes']++;
                    }
                }
            }
        }

        // 4) rateios -- remapeia as contas de origem/destino e o centro
        if (!empty($blocos['rateios'])) {
            $mapaContas = $this->mapaContasExistentes($this->codEvento);
            $sql = sprintf("SELECT r.nome_rateio, r.descricao, r.percentual, r.valor_fixo, r.meio_pagamento,
                                   o.codigo_contabil AS cod_origem, d.codigo_contabil AS cod_destino
                            FROM fin_rateios r
                            LEFT JOIN fin_plano_contas o ON o.cod_plano_contas = r.cod_conta_origem
                            LEFT JOIN fin_plano_contas d ON d.cod_plano_contas = r.cod_conta_destino
                            WHERE r.cod_evento = %d AND r.status = 'ATIVADA'", $origem);
            $rs = $this->conex->Execute($sql);
            if ($rs) {
                foreach ($rs as $r) {
                    $contaOrigem  = $this->traduzirConta($r['cod_origem'], $mapaContas);
                    $contaDestino = $this->traduzirConta($r['cod_destino'], $mapaContas);
                    if ($this->inserirRateio($r, $contaOrigem, $contaDestino, $codCentro, $debug)) {
                        $resumo['rateios']++;
                    }
                }
            }
        }

        return $resumo;
    }

    /**
     * Depois de inserir todas as contas, liga cada filho ao pai correspondente
     * (a origem referencia por ID; aqui a ponte e o codigo contabil).
     */
    private function amarrarHierarquia($origem, $deParaAntigo, $novos, $debug) {
        $sql = sprintf("SELECT cod_plano_contas, cod_plano_contas_pai, codigo_contabil
                        FROM fin_plano_contas
                        WHERE cod_evento = %d AND status = 'ATIVADA' AND cod_plano_contas_pai IS NOT NULL", $origem);
        $rs = $this->conex->Execute($sql);
        if (!$rs) {
            return;
        }
        foreach ($rs as $r) {
            $codigoFilho = trim($r['codigo_contabil']);
            $codigoPai = isset($deParaAntigo[$r['cod_plano_contas_pai']]) ? $deParaAntigo[$r['cod_plano_contas_pai']] : '';
            if ($codigoFilho === '' || $codigoPai === '' || !isset($novos[$codigoFilho]) || !isset($novos[$codigoPai])) {
                continue;
            }
            $sqlUp = sprintf("UPDATE fin_plano_contas SET cod_plano_contas_pai = %d
                              WHERE cod_plano_contas = %d AND cod_evento = %d AND cod_plano_contas_pai IS NULL",
                             $novos[$codigoPai], $novos[$codigoFilho], $this->codEvento);
            $this->conex->debug = $debug;
            $this->conex->Execute($sqlUp);
        }
    }

    // ----------------------------------------------------------------- modelo

    private function executarModelo($blocos, $debug = false) {
        $resumo = array('contas' => 0, 'centro' => 0, 'fontes' => 0, 'rateios' => 0, 'perfil' => 0);
        $mapaContas = $this->mapaContasExistentes($this->codEvento);
        $codCentro = $this->primeiroCentro($this->codEvento);

        // O perfil de Fornecedor faz parte do modelo padrao: sem ele nao ha como
        // cadastrar quem recebe pagamento, e o plano de contas de despesa fica
        // sem uso. Nasce ja classificado -- perfil antigo fica com categoria
        // NULL de proposito (ver sql/perfil_categoria.sql).
        if (!empty($blocos['contas'])) {
            if ($this->criarPerfilFornecedor($debug)) {
                $resumo['perfil']++;
            }
            $criadas = array();
            foreach ($this->modeloContas as $m) {
                list($codigo, $nome, $tipo, $natureza, $aceita, $codigoPai) = $m;
                if (isset($mapaContas[$codigo])) {
                    $criadas[$codigo] = $mapaContas[$codigo];
                    continue;
                }
                $pai = ($codigoPai && isset($criadas[$codigoPai])) ? $criadas[$codigoPai] : null;
                $novoId = $this->inserirConta(array(
                    'cod_plano_contas_pai' => $pai,
                    'nome_plano_contas'    => $nome,
                    'codigo_contabil'      => $codigo,
                    'descricao'            => '',
                    'tipo_conta'           => $tipo,
                    'natureza_saldo'       => $natureza,
                    'aceita_lancamento'    => $aceita,
                ), $debug, $pai);
                if ($novoId) {
                    $criadas[$codigo] = $novoId;
                    $mapaContas[$codigo] = $novoId;
                    $resumo['contas']++;
                }
            }
        }

        if (!empty($blocos['centro']) && !$codCentro) {
            $codCentro = $this->criarCentroDoEvento($debug);
            if ($codCentro) {
                $resumo['centro']++;
            }
        }

        if (!empty($blocos['fontes'])) {
            foreach ($this->modeloFontes as $nome) {
                if ($this->inserirFonte($nome, '', 'INTERNA', $debug)) {
                    $resumo['fontes']++;
                }
            }
        }



        if (!empty($blocos['rateios'])) {
            $mapaContas = $this->mapaContasExistentes($this->codEvento);
            $receita = isset($mapaContas['0106']) ? $mapaContas['0106'] : null;
            $taxa    = isset($mapaContas['0230']) ? $mapaContas['0230'] : null;
            if (!$receita || !$taxa) {
                throw new Exception('Para criar as regras de taxa o plano de contas precisa ter a rubrica de receita e a de despesas bancarias. Marque tambem o plano de contas.');
            }
            foreach ($this->modeloTaxas as $t) {
                list($meio, $pct, $fixo) = $t;
                $linha = array(
                    'nome_rateio'    => 'Taxa ' . $meio,
                    'descricao'      => 'Deduzido de cada pagamento (' . $meio . ')',
                    'percentual'     => $pct,
                    'valor_fixo'     => $fixo,
                    'meio_pagamento' => $meio,
                );
                if ($this->inserirRateio($linha, $receita, $taxa, $codCentro, $debug)) {
                    $resumo['rateios']++;
                }
            }
        }

        // Deducoes institucionais: bloco separado, porque nem todo evento paga
        // taxa de direcao (o 122, por exemplo, nao paga nenhuma).
        if (!empty($blocos['institucional'])) {
            $mapaContas = $this->mapaContasExistentes($this->codEvento);
            $receita = isset($mapaContas['0106']) ? $mapaContas['0106'] : null;
            if (!$receita) {
                throw new Exception('Para criar as taxas institucionais o plano de contas precisa ter a rubrica de receita. Marque tambem o plano de contas.');
            }
            $paisCriados = array();
            foreach ($this->modeloInstitucional as $it) {
                list($nome, $pct, $codRubrica, $nomePai) = $it;
                $destino = isset($mapaContas[$codRubrica]) ? $mapaContas[$codRubrica] : null;
                if (!$destino) {
                    continue;   // rubrica nao existe neste evento: pula a regra
                }
                $linha = array(
                    'nome_rateio'    => $nome,
                    'descricao'      => $pct . '% da receita de inscricoes',
                    'percentual'     => $pct,
                    'valor_fixo'     => null,
                    'meio_pagamento' => null,
                );
                $pai = ($nomePai && isset($paisCriados[$nomePai])) ? $paisCriados[$nomePai] : null;
                $novoId = $this->inserirRateio($linha, $receita, $destino, $codCentro, $debug, $pai);
                if ($novoId) {
                    $resumo['rateios']++;
                    $paisCriados[$nome] = $novoId;
                }
            }
        }

        return $resumo;
    }

    // ------------------------------------------------------------- gravadores

    private function inserirConta($r, $debug, $paiForcado = null) {
        $codigo = isset($r['codigo_contabil']) ? trim($r['codigo_contabil']) : '';
        $pai = $paiForcado !== null ? $paiForcado : null;

        $sql = sprintf("INSERT INTO fin_plano_contas
            (cod_plano_contas_pai, cod_evento, cod_gestor, nome_plano_contas, codigo_contabil,
             descricao, tipo_conta, natureza_saldo, aceita_lancamento, status, data_inclusao, id_inclusao)
            VALUES (%s, %d, %s, %s, %s, %s, %s, %s, %d, 'ATIVADA', %s, %s)",
            $pai ? intval($pai) : 'NULL',
            $this->codEvento,
            $this->codGestor ? intval($this->codGestor) : 'NULL',
            $this->conex->qstr($r['nome_plano_contas']),
            $codigo === '' ? 'NULL' : $this->conex->qstr($codigo),
            $this->conex->qstr(isset($r['descricao']) ? $r['descricao'] : ''),
            $this->conex->qstr($r['tipo_conta']),
            $this->conex->qstr($r['natureza_saldo']),
            intval($r['aceita_lancamento']),
            $this->conex->qstr(date('Y-m-d H:i:s')),
            $this->conex->qstr($this->usuario()));

        $this->conex->debug = $debug;
        $this->conex->Execute($sql);
        return $this->conex->Insert_ID();
    }

    /**
     * Cria o perfil "Fornecedor" do evento, se ainda nao houver um.
     * receber_inscricao = 0 (nao aceita inscricao aberta) e perfil_isento = 0
     * (nao e inscrito isento -- simplesmente nao se inscreve).
     */
    private function criarPerfilFornecedor($debug) {
        $sqlEx = sprintf("SELECT 1 FROM tab_opcoes_perfis
                           WHERE cod_evento = %d
                             AND (categoria_perfil = 'FORNECEDOR' OR titulo LIKE '%%ornecedor%%')
                           LIMIT 1", $this->codEvento);
        $rs = $this->conex->Execute($sqlEx);
        if ($rs && !$rs->EOF) {
            return false;
        }
        $sql = sprintf("INSERT INTO tab_opcoes_perfis
            (cod_evento, cod_perfil, ordem, titulo, categoria_perfil, status,
             receber_inscricao, liberar_certificado, perfil_isento, data_inclusao)
            SELECT %d, COALESCE(MAX(cod_perfil),0)+1, 99, 'Fornecedor', 'FORNECEDOR', 'ATIVADA',
                   0, 0, 0, %s
              FROM tab_opcoes_perfis",
            $this->codEvento, $this->conex->qstr(date('Y-m-d H:i:s')));
        $this->conex->debug = $debug;
        $this->conex->Execute($sql);
        return true;
    }

    private function criarCentroDoEvento($debug) {
        $nome = $this->nomeDoEvento();
        $sql = sprintf("INSERT INTO fin_centro_custo
            (nome_centro_custo, descricao, status, cod_evento, cod_gestor, data_inclusao, id_inclusao)
            VALUES (%s, %s, 'ATIVADA', %d, %s, %s, %s)",
            $this->conex->qstr($nome),
            $this->conex->qstr('Centro de custo do projeto. Informe o codigo contabil do projeto na fundacao.'),
            $this->codEvento,
            $this->codGestor ? intval($this->codGestor) : 'NULL',
            $this->conex->qstr(date('Y-m-d H:i:s')),
            $this->conex->qstr($this->usuario()));
        $this->conex->debug = $debug;
        $this->conex->Execute($sql);
        return $this->conex->Insert_ID();
    }

    private function inserirFonte($nome, $descricao, $tipo, $debug) {
        $sqlEx = sprintf("SELECT 1 FROM fin_fontes_recurso WHERE cod_evento = %d AND nome_fontes_recurso = %s LIMIT 1",
                         $this->codEvento, $this->conex->qstr($nome));
        $rs = $this->conex->Execute($sqlEx);
        if ($rs && !$rs->EOF) {
            return false;
        }
        $sql = sprintf("INSERT INTO fin_fontes_recurso
            (nome_fontes_recurso, descricao, tipo_fontes_recurso, status, cod_evento, cod_gestor, data_inclusao, id_inclusao)
            VALUES (%s, %s, %s, 'ATIVADA', %d, %s, %s, %s)",
            $this->conex->qstr($nome),
            $this->conex->qstr($descricao),
            $this->conex->qstr($tipo ? $tipo : 'INTERNA'),
            $this->codEvento,
            $this->codGestor ? intval($this->codGestor) : 'NULL',
            $this->conex->qstr(date('Y-m-d H:i:s')),
            $this->conex->qstr($this->usuario()));
        $this->conex->debug = $debug;
        $this->conex->Execute($sql);
        return $this->conex->Insert_ID();
    }


    /**
     * Devolve o cod_rateio criado (ou false se ja existia). $codRateioPai monta
     * a hierarquia -- e assim que o bloco de direcao (10%) se divide entre os
     * departamentos (5% cada), no mesmo formato da tela de Rateios.
     */
    private function inserirRateio($r, $contaOrigem, $contaDestino, $codCentro, $debug, $codRateioPai = null) {
        $meio = isset($r['meio_pagamento']) ? $r['meio_pagamento'] : null;
        if ($meio) {
            $sqlEx = sprintf("SELECT 1 FROM fin_rateios WHERE cod_evento = %d AND meio_pagamento = %s LIMIT 1",
                             $this->codEvento, $this->conex->qstr($meio));
            $rs = $this->conex->Execute($sqlEx);
            if ($rs && !$rs->EOF) {
                return false;
            }
        } else {
            // regra institucional: nao repete o mesmo nome no evento
            $sqlEx = sprintf("SELECT 1 FROM fin_rateios WHERE cod_evento = %d AND nome_rateio = %s LIMIT 1",
                             $this->codEvento, $this->conex->qstr($r['nome_rateio']));
            $rs = $this->conex->Execute($sqlEx);
            if ($rs && !$rs->EOF) {
                return false;
            }
        }
        $sql = sprintf("INSERT INTO fin_rateios
            (cod_rateio_principal, codigo_contabil_rateio,
             cod_conta_origem, cod_conta_destino, cod_centro_custo, percentual, valor_fixo, meio_pagamento,
             nome_rateio, descricao, status, cod_evento, cod_gestor, data_inclusao, id_inclusao)
            VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, 'ATIVADA', %d, %s, %s, %s)",
            $codRateioPai ? intval($codRateioPai) : 'NULL',
            $this->conex->qstr($this->proximoCodigoContabilRateio($codRateioPai)),
            $contaOrigem  ? intval($contaOrigem)  : 'NULL',
            $contaDestino ? intval($contaDestino) : 'NULL',
            $codCentro    ? intval($codCentro)    : 'NULL',
            $r['percentual'] !== null && $r['percentual'] !== '' ? floatval($r['percentual']) : 'NULL',
            $r['valor_fixo'] !== null && $r['valor_fixo'] !== ''  ? floatval($r['valor_fixo'])  : 'NULL',
            $meio ? $this->conex->qstr($meio) : 'NULL',
            $this->conex->qstr($r['nome_rateio']),
            $this->conex->qstr($r['descricao']),
            $this->codEvento,
            $this->codGestor ? intval($this->codGestor) : 'NULL',
            $this->conex->qstr(date('Y-m-d H:i:s')),
            $this->conex->qstr($this->usuario()));
        $this->conex->debug = $debug;
        $this->conex->Execute($sql);
        return $this->conex->Insert_ID();
    }

    /**
     * Mesmo formato do RateiosDAO::gerarCodigoContabilRateio: raiz e sequencial
     * dentro do evento (1, 2, 3...) e filho e "codigo do pai"."N".
     */
    private function proximoCodigoContabilRateio($codRateioPai) {
        if (!$codRateioPai) {
            $sql = sprintf("SELECT MAX(CAST(codigo_contabil_rateio AS UNSIGNED)) FROM fin_rateios
                            WHERE cod_evento = %d AND (cod_rateio_principal IS NULL OR cod_rateio_principal = '')",
                           $this->codEvento);
            $ultimo = $this->conex->GetOne($sql);
            return (string)(intval($ultimo) + 1);
        }
        $sql = sprintf("SELECT codigo_contabil_rateio FROM fin_rateios
                        WHERE cod_rateio = %d AND cod_evento = %d", intval($codRateioPai), $this->codEvento);
        $codigoPai = $this->conex->GetOne($sql);
        $sql = sprintf("SELECT MAX(CAST(SUBSTRING_INDEX(codigo_contabil_rateio, '.', -1) AS UNSIGNED))
                        FROM fin_rateios WHERE cod_rateio_principal = %d AND cod_evento = %d",
                       intval($codRateioPai), $this->codEvento);
        $ultimoNivel = $this->conex->GetOne($sql);
        return $codigoPai . '.' . (intval($ultimoNivel) + 1);
    }

    // ------------------------------------------------------------- auxiliares

    private function blocosEscolhidos($fws_) {
        $blocos = array(
            'contas'  => !empty($fws_['blocoContas']),
            'centro'  => !empty($fws_['blocoCentro']),
            'fontes'  => !empty($fws_['blocoFontes']),
            'rateios' => !empty($fws_['blocoRateios']),
            'institucional' => !empty($fws_['blocoInstitucional']),
        );
        if (!array_filter($blocos)) {
            throw new Exception('Escolha ao menos um item para trazer.');
        }
        return $blocos;
    }

    private function contarCatalogo($codEvento) {
        $sql = sprintf("SELECT
            (SELECT COUNT(*) FROM fin_plano_contas   WHERE cod_evento = %1\$d AND status = 'ATIVADA') AS contas,
            (SELECT COUNT(*) FROM fin_centro_custo   WHERE cod_evento = %1\$d AND status = 'ATIVADA') AS centro,
            (SELECT COUNT(*) FROM fin_fontes_recurso WHERE cod_evento = %1\$d AND status = 'ATIVADA') AS fontes,
            (SELECT COUNT(*) FROM fin_rateios        WHERE cod_evento = %1\$d AND status = 'ATIVADA') AS rateios",
            $codEvento);
        $rs = $this->conex->Execute($sql);
        if (!$rs || $rs->EOF) {
            return array('contas' => 0, 'centro' => 0, 'fontes' => 0, 'rateios' => 0);
        }
        return array(
            'contas'  => intval($rs->fields['contas']),
            'centro'  => intval($rs->fields['centro']),
            'fontes'  => intval($rs->fields['fontes']),
            'rateios' => intval($rs->fields['rateios']),
        );
    }

    /**
     * Eventos do mesmo gestor que ja tem plano de contas cadastrado.
     */
    private function carregarOrigens() {
        $filtroGestor = '';
        if ($this->codGestor) {
            $filtroGestor = sprintf(" AND UPPER(e.gestor) = (SELECT UPPER(nome_gestor) FROM sis_gestor WHERE cod_gestor = %d)",
                                    $this->codGestor);
        }
        $sql = sprintf("SELECT e.cod_evento, COALESCE(NULLIF(TRIM(e.abrev_evento),''), e.titulo) AS nome_evento,
                               COUNT(DISTINCT pc.cod_plano_contas) AS contas,
                               (SELECT COUNT(*) FROM fin_rateios r WHERE r.cod_evento = e.cod_evento AND r.status = 'ATIVADA') AS rateios,
                               (SELECT COUNT(*) FROM fin_fontes_recurso f WHERE f.cod_evento = e.cod_evento AND f.status = 'ATIVADA') AS fontes
                        FROM tab_eventos e
                        INNER JOIN fin_plano_contas pc ON pc.cod_evento = e.cod_evento AND pc.status = 'ATIVADA'
                        WHERE e.cod_evento <> %d %s
                        GROUP BY e.cod_evento, e.abrev_evento, e.titulo
                        HAVING contas > 0
                        ORDER BY e.cod_evento DESC", $this->codEvento, $filtroGestor);

        $rs = $this->conex->Execute($sql);
        $lista = array();
        if ($rs) {
            foreach ($rs as $r) {
                $lista[] = array(
                    'cod_evento' => $r['cod_evento'],
                    'nome'       => $this->paraUtf8($r['nome_evento']),
                    'contas'     => intval($r['contas']),
                    'rateios'    => intval($r['rateios']),
                    'fontes'     => intval($r['fontes']),
                );
            }
        }
        return $lista;
    }

    private function mapaContasExistentes($codEvento) {
        $sql = sprintf("SELECT codigo_contabil, cod_plano_contas FROM fin_plano_contas
                        WHERE cod_evento = %d AND status = 'ATIVADA' AND codigo_contabil IS NOT NULL", $codEvento);
        $rs = $this->conex->Execute($sql);
        $mapa = array();
        if ($rs) {
            foreach ($rs as $r) {
                $mapa[trim($r['codigo_contabil'])] = $r['cod_plano_contas'];
            }
        }
        return $mapa;
    }

    private function traduzirConta($codigoContabil, $mapa) {
        $codigo = trim($codigoContabil);
        return ($codigo !== '' && isset($mapa[$codigo])) ? $mapa[$codigo] : null;
    }

    private function primeiroCentro($codEvento) {
        $sql = sprintf("SELECT cod_centro_custo FROM fin_centro_custo
                        WHERE cod_evento = %d AND status = 'ATIVADA'
                        ORDER BY cod_centro_custo LIMIT 1", $codEvento);
        $rs = $this->conex->Execute($sql);
        return ($rs && !$rs->EOF) ? $rs->fields['cod_centro_custo'] : null;
    }

    private function nomeDoEvento() {
        $sql = sprintf("SELECT COALESCE(NULLIF(TRIM(abrev_evento),''), titulo) AS nome
                        FROM tab_eventos WHERE cod_evento = %d LIMIT 1", $this->codEvento);
        $rs = $this->conex->Execute($sql);
        $nome = ($rs && !$rs->EOF) ? trim($rs->fields['nome']) : '';
        return $nome !== '' ? $nome : ('Evento ' . $this->codEvento);
    }

    private function resolverGestor() {
        $sql = sprintf("SELECT g.cod_gestor FROM tab_eventos e
                        INNER JOIN sis_gestor g ON UPPER(g.nome_gestor) = UPPER(e.gestor)
                        WHERE e.cod_evento = %d LIMIT 1", $this->codEvento);
        $rs = $this->conex->Execute($sql);
        return ($rs && !$rs->EOF) ? $rs->fields['cod_gestor'] : null;
    }

    private function usuario() {
        return isset($_SESSION['_cod_usuario']) ? $_SESSION['_cod_usuario'] : '';
    }

    private function mensagemResumo($r) {
        $partes = array();
        if ($r['contas'])  $partes[] = $r['contas']  . ' conta(s) do plano';
        if ($r['centro'])  $partes[] = '1 centro de custo';
        if ($r['fontes'])  $partes[] = $r['fontes']  . ' fonte(s) de recurso';
        if ($r['rateios']) $partes[] = $r['rateios'] . ' regra(s) de rateio';
        if (!empty($r['perfil'])) $partes[] = 'o perfil de Fornecedor';
        if (!$partes) {
            return 'Nada foi criado -- o evento ja tinha esses itens cadastrados.';
        }
        return 'Criado: ' . implode(', ', $partes) . '.';
    }

    /**
     * So converte o que ainda nao e utf8 valido (o banco devolve ora latin1,
     * ora utf8, conforme o SET NAMES da conexao).
     */
    private function paraUtf8($texto) {
        if ($texto === null || $texto === '') {
            return '';
        }
        return mb_detect_encoding($texto, 'UTF-8', true) ? $texto : utf8_encode($texto);
    }

    private function responder($ok, $msg, $dados) {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(array(
            'statusMessage' => $ok,
            'message'       => $this->paraUtf8($msg),
            'dados'         => $dados
        ));
        exit();
    }
}

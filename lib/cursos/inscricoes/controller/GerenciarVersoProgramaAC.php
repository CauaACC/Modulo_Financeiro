<?php

/**
 * Controller do Editor de Programa (verso do certificado).
 *
 * Fluxo: o admin cola/edita o programa numa tela, ajusta fonte/colunas/fundo,
 * visualiza e gera um PDF A4 PAISAGEM de 1 pagina em certificados/verso/.
 * Esse PDF e exatamente o que o GerenciarCertificadoAC anexa como verso, entao
 * o modulo de certificado NAO precisa ser alterado. Opcionalmente registra o
 * arquivo no modulo Midia (slot PROGRAMA) para aparecer na lista de midias.
 *
 * Seguranca: todos os metodos publicos passam por autenticar() (mesmo conjunto
 * de perfis do consulta-dados-congresso-seguranca.php da view), porque a rota
 * CommandAC e acessivel direto por POST e NAO passa pelo gate da view.
 */
class GerenciarVersoProgramaAC
{
    public $codEvento;

    function __construct()
    {
        $this->codEvento = COD_EVENTO;
    }

    /**
     * Gate de acesso admin. Espelha os perfis do arquivo de seguranca da view.
     * Lanca Exception (tratada pelo CommandAC) quando sem permissao.
     */
    private function autenticar()
    {
        FWA_exigirPermissao(array(
            enumSistema::SIS_GERENCIAR_ADM_GERAL,
            enumSistema::SIS_GERENCIAR_CERTIFICADO,    // 30 — direito de certificado
            enumSistema::SIS_GERENCIAR_INSCRITO,       // 13 — quem ja entrava por aqui continua entrando
            enumSistema::SIS_GERENCIAR_AREAS_TEMATICAS,
            enumSistema::SIS_CONSULTAR_PAINEL_BORDO,
            enumSistema::SIS_CONSULTAR_INSCRITOS,
        ));
    }

    /* =================================================================
     *  Acoes chamadas pela view (via CommandAC: c/m/_submit_check)
     * ================================================================= */

    /**
     * Carrega as opcoes salvas para o escopo (evento + oficina) e devolve JSON.
     * cod_oficina vazio/0 => programa geral do evento.
     */
    public function carregar($fws_, $debug = false)
    {
        $this->autenticar();

        $obj = new VersoPrograma();
        $obj->setCodEvento($this->codEvento);
        $obj->setCodOficina(isset($fws_['codOficina']) ? (int) $fws_['codOficina'] : 0);
        $encontrou = $obj->carregarPorEscopo($debug);

        // Sugestao de titulo: quando ainda nao ha programa salvo (ou o titulo
        // esta vazio), pre-preenche com o nome do evento.
        $titulo = $this->paraUtf8($obj->getTitulo());
        if (trim($titulo) === '') {
            $titulo = $this->paraUtf8($this->getTituloEvento());
        }

        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(array(
            'encontrou'     => $encontrou ? 1 : 0,
            'titulo'        => $titulo,
            'rodape'        => $this->paraUtf8($obj->getRodape()),
            'conteudoHtml'  => $this->paraUtf8($obj->getConteudoHtml()),
            'modoFundo'     => $obj->getModoFundo(),
            'fonteTamanho'  => $obj->getFonteTamanho(),
            'fonteTitulo'   => $obj->getFonteTitulo(),
            'fonteRodape'   => $obj->getFonteRodape(),
            'margemConteudoTopo'  => $obj->getMargemConteudoTopo(),
            'margemConteudoBaixo' => $obj->getMargemConteudoBaixo(),
            'numColunas'    => $obj->getNumColunas(),
            'margemTopo'    => (int) ($obj->getMargemTopo() ?: 15),
            'margemBaixo'   => (int) $obj->getMargemBaixo(),
            'margemLateral' => (int) ($obj->getMargemLateral() ?: 15),
            'arquivoGerado' => $obj->getArquivoGerado(),
            // status do fundo cadastrado no Midia (p/ a tela mostrar "enviado")
            'temFundoPdf'   => $this->resolverImagemMidia('CERTIFICADO-VERSO-FUNDO-PDF') ? 1 : 0,
            'temFundoImg'   => $this->resolverImagemMidia('CERTIFICADO-VERSO-FUNDO') ? 1 : 0,
        ));
        exit;
    }

    /**
     * Lista os escopos do evento que ja tem programa, para o coordenador ver
     * de uma vez o que existe e nao recriar por engano. Devolve JSON.
     */
    public function listarEscopos($fws_, $debug = false)
    {
        $this->autenticar();

        $obj = new VersoPrograma();
        $obj->setCodEvento($this->codEvento);

        $itens = array();
        foreach ($obj->listarEscopos($debug) as $e) {
            $codOficina = (int) $e['cod_oficina'];
            $itens[] = array(
                'codOficina' => $codOficina,
                'escopo'     => $codOficina > 0
                    ? $this->nomeOficina($codOficina)
                    : 'Geral do evento',
                'titulo'     => $this->paraUtf8($e['titulo']),
                'arquivo'    => $e['arquivo_gerado'],
                // sem arquivo_gerado = so rascunho salvo, o PDF nunca foi gerado
                'gerado'     => !empty($e['arquivo_gerado']) ? 1 : 0,
            );
        }

        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(array('itens' => $itens));
        exit;
    }

    /** Nome da oficina para exibir na lista de escopos (nunca lanca). */
    private function nomeOficina($codOficina)
    {
        try {
            $o = new Oficina();
            $o->setCodEvento($this->codEvento);
            $o->setCodOficina($codOficina);
            $o->getOficinaById();
            // A coluna e 'titulo' (tab_opcoes_oficinas), nao 'nome'.
            $nome = trim((string) $o->getTitulo());
            return $nome !== '' ? $this->paraUtf8($nome) : ('Atividade ' . $codOficina);
        } catch (Exception $e) {
            return 'Atividade ' . $codOficina;
        }
    }

    /**
     * Mostra o fundo cadastrado (PDF ou imagem) para conferencia na tela.
     *
     * O arquivo mora fora do htdocs (WB_PATH_LOG), entao nao tem URL direta:
     * este metodo le e devolve o conteudo, passando pelo mesmo gate do modulo.
     * O caminho vem sempre do resolverImagemMidia() com slot fixo, nunca do
     * POST -- nao da para pedir um arquivo arbitrario por aqui.
     */
    public function verFundo($fws_, $debug = false)
    {
        $this->autenticar();

        // PDF tem prioridade, igual a regra usada na renderizacao do verso.
        $arquivo = $this->resolverImagemMidia('CERTIFICADO-VERSO-FUNDO-PDF');
        if (!$arquivo) {
            $arquivo = $this->resolverImagemMidia('CERTIFICADO-VERSO-FUNDO');
        }
        if (!$arquivo || !is_file($arquivo)) {
            header('Content-Type: text/html; charset=UTF-8');
            echo '<p style="font-family:sans-serif;padding:20px;">'
                . 'Nenhum fundo cadastrado para este evento.</p>';
            exit;
        }

        $ext = strtolower(pathinfo($arquivo, PATHINFO_EXTENSION));
        $tipos = array(
            'pdf'  => 'application/pdf',
            'png'  => 'image/png',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif'  => 'image/gif',
            'webp' => 'image/webp',
        );
        $mime = isset($tipos[$ext]) ? $tipos[$ext] : 'application/octet-stream';

        while (ob_get_level() > 0) { ob_end_clean(); }
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($arquivo));
        header('Content-Disposition: inline; filename="fundo-verso.' . $ext . '"');
        readfile($arquivo);
        exit;
    }

    /**
     * Salva (persiste) as opcoes do programa, sem gerar PDF.
     */
    public function salvar($fws_, $debug = false)
    {
        $this->autenticar();

        $ok = false;
        $msg = '';
        try {
            $obj = $this->montarObjDoPost($fws_);
            $obj->salvar($debug);
        } catch (OKException $e) {
            $ok = true;
            $msg = $e->getMessage();
        } catch (Exception $e) {
            $msg = $e->getMessage();
        }

        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(array('statusMessage' => $ok ? '1' : '', 'message' => $this->paraUtf8($msg)));
        exit;
    }

    /**
     * Renderiza o verso inline (visualizacao) a partir do conteudo ATUAL do
     * editor (nao precisa estar salvo). Sai como PDF no navegador.
     */
    public function visualizar($fws_, $debug = false)
    {
        $this->autenticar();
        $obj = $this->montarObjDoPost($fws_);
        // Visualizacao nao valida "vazio" com rigor: apenas mostra o que houver.
        $this->renderizarPdf($obj, 'I', null, $debug);
        exit;
    }

    /**
     * Salva as opcoes, gera o PDF final em certificados/verso/ e (best-effort)
     * registra no modulo Midia como PROGRAMA. Devolve JSON.
     */
    public function gerar($fws_, $debug = false)
    {
        $this->autenticar();

        $ok = false;
        $msg = '';
        $arquivo = '';
        try {
            $obj = $this->montarObjDoPost($fws_);
            $obj->validatorObj(); // exige conteudo nao-vazio antes de gerar

            // 1) persiste as opcoes (nao interrompe a geracao se falhar o commit)
            try { $obj->salvar($debug); } catch (OKException $e) {} catch (Exception $e) {}

            // 2) gera o PDF fisico no lugar que o certificado le
            $arquivo = $obj->gerarNomeArquivoPrograma();
            $destino = $this->diretorioVerso() . $arquivo;
            $this->garantirDiretorio($this->diretorioVerso());
            $paginas = $this->renderizarPdf($obj, 'F', $destino, $debug);

            if (!is_file($destino)) {
                throw new Exception("O PDF do programa nao foi gerado.");
            }

            // O verso do certificado TEM que ser 1 pagina: o merge com a arte de
            // fundo usa importPage(1), entao qualquer pagina extra seria jogada
            // fora sem aviso. Melhor recusar e dizer o que reduzir.
            if ($paginas > 1) {
                @unlink($destino);
                // Com margens absolutas a altura util e uma subtracao direta, e
                // o vilao quase sempre e a margem superior do conteudo alta
                // demais (usada para desviar da arte de fundo), nao a fonte.
                $topo = (int) $obj->getMargemConteudoTopo();
                $base = (int) $obj->getMargemConteudoBaixo();
                $alturaUtil = 210 - $topo - $base;

                if ($alturaUtil < 100) {
                    $dica = "Sobrou pouca altura: a <b>margem superior do conteudo esta em "
                        . $topo . "mm</b> e a inferior em <b>" . $base . "mm</b>. "
                        . "Use o botao <b>Regua (mm)</b> para ver onde a arte de fundo "
                        . "termina e deixe so o necessario.";
                } elseif ($obj->getNumColunas() < 2) {
                    $dica = "Comece aumentando as COLUNAS para 2 &mdash; costuma resolver sozinho.";
                } else {
                    $dica = "Reduza a fonte do conteudo ou aumente as colunas.";
                }

                throw new Exception(
                    "O programa ocupou " . $paginas . " paginas e o verso do certificado "
                    . "precisa ter apenas 1.<br><br>"
                    . "O conteudo tem <b>" . $alturaUtil . "mm</b> de altura disponivel "
                    . "(de " . $topo . "mm a " . (210 - $base) . "mm da pagina).<br>"
                    . "Configuracao usada: <b>" . $obj->getNumColunas() . " coluna(s)</b>, "
                    . "fonte do conteudo <b>" . $obj->getFonteTamanho() . "pt</b>, "
                    . "margem lateral <b>" . $obj->getMargemLateral() . "mm</b>.<br><br>"
                    . $dica . " Use o botao <b>Visualizar</b> para conferir antes de gerar."
                );
            }

            // 3) registra no modulo Midia (best-effort; nao bloqueia)
            $this->registrarNaMidia($arquivo, $obj, $debug);

            $ok = true;
            $msg = "Verso gerado com sucesso: " . $arquivo;
        } catch (Exception $e) {
            $msg = $e->getMessage();
        }

        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(array(
            'statusMessage' => $ok ? '1' : '',
            'message'       => $this->paraUtf8($msg),
            'arquivo'       => $arquivo,
        ));
        exit;
    }

    /**
     * Gera uma REGUA em PDF: A4 paisagem com grade em milimetros desenhada por
     * cima da arte de fundo (quando houver). Serve para o admin descobrir em
     * que altura a arte termina e digitar o valor certo em "Ate o conteudo",
     * em vez de ir no chute.
     */
    public function regua($fws_, $debug = false)
    {
        $this->autenticar();

        if (!class_exists('\\Mpdf\\Mpdf')) {
            require_once(dirname(__FILE__) . '/../lib/vendor/autoload.php');
        }

        $L = 297; // A4 paisagem: largura em mm
        $A = 210; // altura em mm

        $mpdf = new \Mpdf\Mpdf(array(
            'format' => 'A4', 'orientation' => 'L', 'unit' => 'mm',
            'margin_left' => 0, 'margin_right' => 0,
            'margin_top' => 0, 'margin_bottom' => 0,
            'margin_header' => 0, 'margin_footer' => 0,
        ));
        $mpdf->SetDisplayMode('fullpage');
        $mpdf->AddPage();
        $mpdf->SetFont('dejavusanscondensed', '', 5);

        // Grade fina de 10 em 10 mm.
        $mpdf->SetLineWidth(0.05);
        $mpdf->SetDrawColor(170, 170, 170);
        for ($x = 10; $x < $L; $x += 10) { $mpdf->Line($x, 0, $x, $A); }
        for ($y = 10; $y < $A; $y += 10) { $mpdf->Line(0, $y, $L, $y); }

        // Linhas de referencia de 50 em 50 mm (mais visiveis).
        $mpdf->SetLineWidth(0.25);
        $mpdf->SetDrawColor(210, 0, 0);
        for ($x = 50; $x < $L; $x += 50) { $mpdf->Line($x, 0, $x, $A); }
        for ($y = 50; $y < $A; $y += 50) { $mpdf->Line(0, $y, $L, $y); }

        // Numeros: topo (horizontal) e lateral esquerda (vertical).
        $mpdf->SetTextColor(210, 0, 0);
        for ($x = 10; $x < $L; $x += 10) { $mpdf->Text($x + 0.6, 4.2, (string) $x); }
        for ($y = 10; $y < $A; $y += 10) { $mpdf->Text(0.8, $y - 0.8, (string) $y); }

        // ---- Quadros pontilhados: onde CADA faixa esta caindo hoje ----
        // As alturas sao MEDIDAS, nao estimadas pela fonte: tamanho de fonte e
        // altura de faixa sao coisas diferentes (um titulo longo quebra em duas
        // linhas e dobra a faixa). Quem sabe a altura real e o proprio mPDF,
        // via o mesmo 'stretch' que empurra o conteudo.
        $obj = $this->montarObjDoPost($fws_);
        $ml  = (int) $obj->getMargemLateral();
        $mt  = (int) $obj->getMargemTopo();
        $mb  = (int) $obj->getMargemBaixo();
        $larguraUtil = $L - (2 * $ml);

        // Alturas MEDIDAS das faixas (nao estimadas pela fonte).
        list($hTitulo, $hRodape) = $this->medirAlturaFaixas($obj);

        // Com margens absolutas o conteudo fica onde o usuario mandou...
        $inicio = (int) $obj->getMargemConteudoTopo();
        $fim    = $A - (int) $obj->getMargemConteudoBaixo();
        // ...a menos que a faixa invada: ai o 'stretch' do mPDF empurra.
        $inicioReal = max($inicio, $mt + $hTitulo);
        $fimReal    = min($fim, $A - $mb - $hRodape);

        $mpdf->SetLineWidth(0.4);
        $mpdf->SetDash(1.5, 1.2);
        $mpdf->SetFont('dejavusanscondensed', 'B', 6);

        // Titulo (azul)
        if ($hTitulo > 0) {
            $mpdf->SetDrawColor(0, 110, 220);
            $mpdf->Rect($ml, $mt, $larguraUtil, $hTitulo);
            $mpdf->SetTextColor(0, 110, 220);
            $mpdf->Text($ml + 1, $mt - 1.2,
                'TITULO  (' . $mt . 'mm ate ' . round($mt + $hTitulo, 1) . 'mm)');
        }

        // Conteudo (verde)
        $mpdf->SetDrawColor(0, 150, 60);
        $mpdf->Rect($ml, $inicioReal, $larguraUtil, max(0, $fimReal - $inicioReal));
        $mpdf->SetTextColor(0, 130, 50);
        $mpdf->Text($ml + 1, $inicioReal - 1.2,
            'CONTEUDO  (' . round($inicioReal, 1) . 'mm ate ' . round($fimReal, 1) . 'mm  =  '
            . round($fimReal - $inicioReal, 1) . 'mm de altura)');

        // Rodape (laranja)
        if ($hRodape > 0) {
            $topoRodape = $A - $mb - $hRodape;
            $mpdf->SetDrawColor(215, 140, 0);
            $mpdf->Rect($ml, $topoRodape, $larguraUtil, $hRodape);
            $mpdf->SetTextColor(190, 120, 0);
            $mpdf->Text($ml + 1, $topoRodape - 1.2,
                'RODAPE  (' . round($topoRodape, 1) . 'mm ate ' . round($topoRodape + $hRodape, 1) . 'mm)');
        }

        $mpdf->SetDash();

        // Avisa quando a margem pedida foi ignorada porque a faixa invadiria.
        $avisos = array();
        if ($inicioReal > $inicio) {
            $avisos[] = 'A margem superior do conteudo (' . $inicio . 'mm) e menor que o fim do '
                . 'titulo (' . round($mt + $hTitulo, 1) . 'mm). O conteudo foi empurrado para '
                . round($inicioReal, 1) . 'mm.';
        }
        if ($fimReal < $fim) {
            $avisos[] = 'A margem inferior do conteudo deixaria o texto sobre o rodape. '
                . 'O conteudo foi cortado em ' . round($fimReal, 1) . 'mm.';
        }
        if ($avisos) {
            $mpdf->SetTextColor(200, 0, 0);
            $mpdf->SetFont('dejavusanscondensed', 'B', 7);
            $y = $A - 6;
            foreach (array_reverse($avisos) as $av) {
                $mpdf->Text($ml + 1, $y, 'ATENCAO: ' . $av);
                $y -= 4;
            }
        }

        // Fundo da arte (se houver): a grade vai POR CIMA dele.
        $fundoPdf = $this->resolverImagemMidia('CERTIFICADO-VERSO-FUNDO-PDF');
        if ($fundoPdf) {
            $this->garantirDiretorio($this->diretorioVerso());
            $tmp = $this->diretorioVerso() . 'tmp_regua_' . uniqid() . '.pdf';
            $mpdf->Output($tmp, 'F');
            unset($mpdf);

            require_once(dirname(__FILE__) . '/../lib/vendor/setasign/fpdf/Fpdf.php');
            $fpdi = new \setasign\Fpdi\Fpdi();
            // Ordem obrigatoria: setSourceFile -> importPage -> AddPage -> useTemplate.
            $fpdi->setSourceFile($fundoPdf);
            $tplFundo = $fpdi->importPage(1);
            $fpdi->AddPage('L', 'A4');
            $fpdi->useTemplate($tplFundo, 0, 0, $L, $A);
            // Grade por cima da arte.
            $fpdi->setSourceFile($tmp);
            $tplGrade = $fpdi->importPage(1);
            $fpdi->useTemplate($tplGrade, 0, 0, $L, $A);
            while (ob_get_level() > 0) { ob_end_clean(); }
            $fpdi->Output('regua-verso.pdf', 'I');
            unset($fpdi);
            @unlink($tmp);
            exit;
        }

        while (ob_get_level() > 0) { ob_end_clean(); }
        $mpdf->Output('regua-verso.pdf', 'I');
        exit;
    }

    /**
     * Mede a ALTURA REAL das faixas de titulo e rodape, em mm.
     *
     * Nao da para deduzir isso do tamanho da fonte: a altura depende de quantas
     * linhas o texto ocupou depois de quebrar. Um titulo longo em 20pt com
     * margem lateral folgada chega a 44mm, contra os 8,8mm que a fonte sugere.
     *
     * Como medir: renderiza um PDF descartavel com a margem do conteudo colada
     * na margem da faixa. Assim o 'stretch' precisa empurrar exatamente a
     * altura da faixa, e a diferenca revela essa altura.
     *
     * @return array [alturaTituloMm, alturaRodapeMm]
     */
    private function medirAlturaFaixas(VersoPrograma $obj)
    {
        $A  = 210;
        $mt = (int) $obj->getMargemTopo();
        $mb = (int) $obj->getMargemBaixo();

        try {
            $sonda = clone $obj;
            $sonda->setMargemConteudoTopo($mt);   // cola no titulo
            $sonda->setMargemConteudoBaixo($mb);  // cola no rodape

            $tmp = $this->novoMpdf($sonda);
            $this->escreverPrograma($tmp, $sonda, false);
            $hTitulo = $obj->temTitulo() ? max(0, (float) $tmp->tMargin - $mt) : 0;
            $hRodape = $obj->temRodape() ? max(0, (float) $tmp->bMargin - $mb) : 0;
            unset($tmp);

            return array(round($hTitulo, 1), round($hRodape, 1));
        } catch (Exception $e) {
            // Recurso de apoio: se a medicao falhar, cai na estimativa pela
            // fonte em vez de derrubar a regua inteira.
            return array(
                $obj->temTitulo() ? round($obj->getFonteTitulo() * 1.25 * 0.3528, 1) : 0,
                $obj->temRodape() ? round($obj->getFonteRodape() * 1.30 * 0.3528, 1) : 0,
            );
        }
    }

    /* =================================================================
     *  Helpers internos
     * ================================================================= */

    /** Monta a Entidade a partir do POST, com o HTML ja sanitizado. */
    private function montarObjDoPost($fws_)
    {
        $obj = new VersoPrograma();
        $obj->setCodEvento($this->codEvento);
        $obj->setCodOficina(isset($fws_['codOficina']) ? (int) $fws_['codOficina'] : 0);
        $obj->setCodPerfil(isset($fws_['codPerfil']) && $fws_['codPerfil'] !== '' ? (int) $fws_['codPerfil'] : 0);
        $obj->setTitulo(isset($fws_['titulo']) ? trim(strip_tags($fws_['titulo'])) : '');
        $obj->setRodape(isset($fws_['rodape']) ? trim(strip_tags($fws_['rodape'])) : '');
        $obj->setConteudoHtml($this->sanitizarHtml(isset($fws_['conteudoHtml']) ? $fws_['conteudoHtml'] : ''));
        $obj->setModoFundo(isset($fws_['modoFundo']) ? $fws_['modoFundo'] : 'branco');
        $obj->setFonteTamanho(isset($fws_['fonteTamanho']) ? (int) $fws_['fonteTamanho'] : 10);
        // Titulo e rodape tem fonte propria (nao seguem a fonte do corpo).
        $obj->setFonteTitulo(isset($fws_['fonteTitulo']) ? (int) $fws_['fonteTitulo'] : 14);
        $obj->setFonteRodape(isset($fws_['fonteRodape']) ? (int) $fws_['fonteRodape'] : 9);
        // Espacos ENTRE as faixas e o conteudo (descontados da area util).
        $obj->setMargemConteudoTopo(isset($fws_['margemConteudoTopo']) ? (int) $fws_['margemConteudoTopo'] : 25);
        $obj->setMargemConteudoBaixo(isset($fws_['margemConteudoBaixo']) ? (int) $fws_['margemConteudoBaixo'] : 25);
        $obj->setNumColunas(isset($fws_['numColunas']) ? (int) $fws_['numColunas'] : 1);
        $obj->setMargemTopo(isset($fws_['margemTopo']) ? (int) $fws_['margemTopo'] : 15);
        $obj->setMargemBaixo(isset($fws_['margemBaixo']) ? (int) $fws_['margemBaixo'] : 15);
        $obj->setMargemLateral(isset($fws_['margemLateral']) ? (int) $fws_['margemLateral'] : 15);
        return $obj;
    }

    /**
     * Sanitiza o HTML colado do editor: remove <script>/<style>, handlers on*
     * e URLs javascript:. Mantem as tags de formatacao usuais. Como o destino
     * e o mPDF (nao o navegador do usuario final) e o acesso e restrito a admin,
     * este saneamento e defensivo/basico.
     */
    private function sanitizarHtml($html)
    {
        if ($html === null) {
            return '';
        }
        // remove blocos perigosos inteiros
        $html = preg_replace('#<\s*(script|style|iframe|object|embed)[^>]*>.*?<\s*/\s*\1\s*>#is', '', $html);
        // remove handlers inline on*="..." e on*='...'
        $html = preg_replace('#\son\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)#i', '', $html);
        // neutraliza javascript: em href/src
        $html = preg_replace('#(href|src)\s*=\s*(["\']?)\s*javascript:[^"\'>]*\2#i', '$1="#"', $html);
        return $html;
    }

    private function diretorioVerso()
    {
        return rtrim(WB_PATH_LOG_CERTIFICADOS, '/\\') . DIRECTORY_SEPARATOR . 'verso' . DIRECTORY_SEPARATOR;
    }

    /** Cria a pasta destino resolvendo o ".." de WB_PATH_LOG (armadilha mkdir). */
    private function garantirDiretorio($dir)
    {
        $dir = rtrim($dir, '/\\');
        $pai = dirname($dir);
        if (is_dir($pai)) {
            $paiReal = realpath($pai);
            if ($paiReal !== false) {
                $dir = $paiReal . DIRECTORY_SEPARATOR . basename($dir);
            }
        }
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        @chmod($dir, 0777);
        if (!is_dir($dir) || !is_writable($dir)) {
            throw new Exception("Pasta do verso indisponivel para escrita: " . $dir);
        }
        return $dir;
    }

    /**
     * Renderiza o programa em PDF A4 PAISAGEM (uma pagina).
     * $modo: 'I' inline (visualizar) ou 'F' salva em arquivo ($destino).
     *
     * Modo de fundo:
     *  - 'completo' com PDF cadastrado -> importa a pagina do PDF de fundo como
     *    base (FPDI) e sobrepoe o texto do programa (mesmo mecanismo do verso do
     *    certificado). Fidelidade vetorial.
     *  - 'completo' com imagem -> background do <body> no mPDF.
     *  - 'branco' -> so o texto.
     */
    private function renderizarPdf(VersoPrograma $obj, $modo = 'I', $destino = null, $debug = false)
    {
        // Fundo em PDF tem prioridade (arte vetorial, A4 paisagem).
        $fundoPdf = ($obj->getModoFundo() === 'completo')
            ? $this->resolverImagemMidia('CERTIFICADO-VERSO-FUNDO-PDF')
            : '';

        if ($fundoPdf) {
            return $this->renderizarComFundoPdf($obj, $fundoPdf, $modo, $destino);
        }

        // Sem PDF de fundo: mPDF direto (imagem via body-background, ou branco).
        $mpdf = $this->novoMpdf($obj);
        $paginas = $this->escreverPrograma($mpdf, $obj, true);
        if ($modo === 'F' && $destino) {
            $mpdf->Output($destino, 'F');
        } else {
            while (ob_get_level() > 0) { ob_end_clean(); }
            $mpdf->Output('programa-preview.pdf', 'I');
        }
        unset($mpdf);
        return $paginas;
    }

    /** Cria o mPDF A4 paisagem com as margens/colunas do programa. */
    private function novoMpdf(VersoPrograma $obj)
    {
        if (!class_exists('\\Mpdf\\Mpdf')) {
            require_once(dirname(__FILE__) . '/../lib/vendor/autoload.php');
        }
        // Todas as margens sao ABSOLUTAS (mm a partir da borda da pagina), que e
        // exatamente o que se le na Regua (mm):
        //   margem_topo           -> onde o TITULO comeca
        //   margem_conteudo_topo  -> onde o CONTEUDO comeca
        //   margem_conteudo_baixo -> onde o CONTEUDO termina (da base)
        //   margem_baixo          -> onde o RODAPE fica (da base)
        $ml = (int) $obj->getMargemLateral();
        $mpdf = new \Mpdf\Mpdf(array(
            'format'        => 'A4',
            'orientation'   => 'L',
            'unit'          => 'mm',
            'margin_left'   => $ml,
            'margin_right'  => $ml,
            'margin_top'    => (int) $obj->getMargemConteudoTopo(),
            'margin_bottom' => (int) $obj->getMargemConteudoBaixo(),
            'margin_header' => (int) $obj->getMargemTopo(),
            'margin_footer' => (int) $obj->getMargemBaixo(),
            'autoMarginPadding' => 0,
        ));

        // Rede de seguranca: o 'stretch' so AUMENTA a margem do conteudo, e
        // apenas se o titulo/rodape reais forem altos a ponto de invadi-lo
        // (titulo longo que quebra em 2 linhas). Com margens bem escolhidas ele
        // nao faz nada; sem ele, o texto escreveria por cima da faixa.

        // Aqui esta o "desconto" da area util: o mPDF mede a altura renderizada
        // do titulo/rodape e empurra a margem do corpo, em vez de deixar o texto
        // passar por cima das faixas.
        $mpdf->setAutoTopMargin    = 'stretch';
        $mpdf->setAutoBottomMargin = 'stretch';

        $mpdf->SetDisplayMode('fullpage');
        // NAO liga colunas aqui: elas valem so para o CORPO do programa.
        return $mpdf;
    }

    /**
     * Escreve o programa no mPDF.
     *
     * Layout: titulo e rodape sao FAIXAS FIXAS (header/footer nativos do mPDF),
     * que por definicao ocupam a linha toda e ficam FORA do fluxo de colunas.
     * O corpo do programa recebe apenas a altura que sobra entre as duas faixas.
     *
     * Retorna o numero de paginas geradas — o verso do certificado tem que ser
     * 1 pagina; ver a trava em gerar().
     */
    private function escreverPrograma(\Mpdf\Mpdf $mpdf, VersoPrograma $obj, $permitirFundoImagem = true)
    {
        $p = $this->montarHtmlPrograma($obj, $permitirFundoImagem);

        // Faixas primeiro: o mPDF precisa saber a altura delas para calcular a
        // area util antes de comecar a escrever o corpo.
        if ($p['titulo'] !== '') {
            $mpdf->SetHTMLHeader($p['titulo']);
        }
        if ($p['rodape'] !== '') {
            $mpdf->SetHTMLFooter($p['rodape']);
        }

        // Corpo: aqui sim em colunas, se configurado (1 coluna = sem divisao).
        $colunas = $obj->getNumColunas();
        if ($colunas > 1) {
            $mpdf->SetColumns($colunas, '', 6);
        }
        // CSS e corpo na MESMA chamada (modo documento completo): e assim que o
        // mPDF processa o <style> e aplica o background-image do <body>. Separar
        // em HEADER_CSS + HTML_BODY faria o fundo por imagem sumir.
        $mpdf->WriteHTML($p['css'] . $p['conteudo']);
        if ($colunas > 1) {
            $mpdf->SetColumns(0); // fecha as colunas e descarrega o buffer
        }

        return (int) $mpdf->page;
    }

    /**
     * Gera o texto do programa (transparente) e sobrepoe no PDF de fundo via FPDI.
     * O PDF de fundo entra como base; o texto (fundo transparente) vai por cima.
     */
    private function renderizarComFundoPdf(VersoPrograma $obj, $fundoPdf, $modo, $destino)
    {
        // 1) Gera o texto do programa em PDF temporario (sem qualquer fundo).
        $this->garantirDiretorio($this->diretorioVerso());
        $tmpTexto = $this->diretorioVerso() . 'tmp_texto_' . uniqid() . '.pdf';
        $mpdf = $this->novoMpdf($obj);
        $paginas = $this->escreverPrograma($mpdf, $obj, false);
        $mpdf->Output($tmpTexto, 'F');
        unset($mpdf);

        // 2) Funde fundo (base) + texto (por cima) com FPDI (mesmo esquema do certificado).
        require_once(dirname(__FILE__) . '/../lib/vendor/setasign/fpdf/Fpdf.php');
        $fpdi = new \setasign\Fpdi\Fpdi();

        $fpdi->setSourceFile($fundoPdf);
        $tplFundo = $fpdi->importPage(1);
        $fpdi->AddPage('L', 'A4');
        $fpdi->useTemplate($tplFundo, 0, 0, 297, 210);

        $fpdi->setSourceFile($tmpTexto);
        $tplTexto = $fpdi->importPage(1);
        $fpdi->useTemplate($tplTexto, 0, 0, 297, 210);

        if ($modo === 'F' && $destino) {
            $fpdi->Output($destino, 'F');
        } else {
            while (ob_get_level() > 0) { ob_end_clean(); }
            $fpdi->Output('programa-preview.pdf', 'I');
        }
        unset($fpdi);
        @unlink($tmpTexto);
        return $paginas;
    }

    /**
     * Monta o HTML do programa em PARTES (css / titulo / conteudo / rodape),
     * porque cada uma e escrita num momento diferente do fluxo do mPDF:
     * titulo e rodape fora das colunas, corpo dentro. Ver escreverPrograma().
     *
     * $permitirFundoImagem: quando true e o modo for 'completo' com IMAGEM
     * cadastrada, aplica o background no <body>. No caminho de fundo-PDF passamos
     * false para o texto sair transparente (o fundo vem do proprio PDF base).
     */
    private function montarHtmlPrograma(VersoPrograma $obj, $permitirFundoImagem = true)
    {
        $fonte    = $obj->getFonteTamanho();
        $titulo   = $this->paraUtf8($obj->getTitulo());
        $rodape   = $this->paraUtf8($obj->getRodape());
        $conteudo = $this->paraUtf8($obj->getConteudoHtml());

        // Fundo completo por IMAGEM = background do <body> (atras do texto).
        $bodyBg = '';
        if ($permitirFundoImagem && $obj->getModoFundo() === 'completo') {
            $fundo = $this->resolverImagemMidia('CERTIFICADO-VERSO-FUNDO');
            if ($fundo) {
                $bodyBg = ' background-image: url(\'' . $fundo . '\'); background-image-resize: 6; background-repeat: no-repeat;';
            }
        }

        $css = '
            <style>
                body { font-family: DejaVuSans, Helvetica, Arial, sans-serif; font-size:' . $fonte . 'pt; color:#111; line-height:1.35;' . $bodyBg . ' }
                h1,h2,h3,h4 { margin:0 0 4pt 0; }
                ul,ol { margin:2pt 0 6pt 14pt; padding:0; }
                p { margin:0 0 4pt 0; }
                table { width:100%; border-collapse:collapse; }
            </style>';

        // Titulo e rodape sao OPCIONAIS (vazio = nao renderiza) e viram FAIXAS
        // (header/footer nativos). Estilo INLINE de proposito: header e footer
        // do mPDF nao enxergam o <style> do documento.
        //
        // O espacamento ate o conteudo vai como PADDING dentro da propria faixa.
        // Assim ele entra na altura medida pelo 'stretch' e e descontado do
        // conteudo — em vez de virar margem solta que o mPDF ignoraria.
        // Sem padding: a distancia ate o conteudo agora vem das margens
        // absolutas do mPDF, nao de um espaco embutido na faixa.
        $tituloHtml = '';
        if ($titulo !== '') {
            $tituloHtml = '<div style="width:100%; text-align:center; font-weight:bold;'
                . ' font-family: DejaVuSans, Helvetica, Arial, sans-serif;'
                . ' font-size:' . $obj->getFonteTitulo() . 'pt; color:#111; line-height:1.25;">'
                . htmlspecialchars($titulo) . '</div>';
        }

        $rodapeHtml = '';
        if ($rodape !== '') {
            $rodapeHtml = '<div style="width:100%; text-align:center;'
                . ' font-family: DejaVuSans, Helvetica, Arial, sans-serif;'
                . ' font-size:' . $obj->getFonteRodape() . 'pt; color:#111; line-height:1.3;">'
                . htmlspecialchars($rodape) . '</div>';
        }

        return array(
            'css'      => $css,
            'titulo'   => $tituloHtml,
            'conteudo' => '<div class="vp-conteudo">' . $conteudo . '</div>',
            'rodape'   => $rodapeHtml,
        );
    }

    /**
     * Resolve o caminho fisico de um arquivo cadastrado no modulo Midia para o
     * slot informado (ex.: CERTIFICADO-VERSO-FUNDO-PDF). Retorna '' se nao houver.
     * Nunca lanca: fundo e opcional e nao pode bloquear a geracao.
     */
    private function resolverImagemMidia($slot)
    {
        try {
            $m = new MidiaUpload();
            $m->setArrayTipoArquivo('');   // limpa o filtro amplo do construtor
            $m->setTipoArquivo($slot);
            $lista = $m->getAll();
            if (empty($lista)) {
                return '';
            }
            $o = $lista[0];
            $o->setLocalMidia($slot);
            $nome = basename($o->getArquivoGerado());
            if ($nome === '' || $nome === '.' || $nome === '..') {
                return '';
            }
            $path = rtrim($o->getMidiaCaminho(), '/\\') . DIRECTORY_SEPARATOR . $nome;
            $real = realpath($path);
            return ($real !== false && is_file($real)) ? $real : '';
        } catch (Exception $e) {
            return '';
        }
    }

    /**
     * Registra o PDF gerado no modulo Midia como PROGRAMA (best-effort).
     * Assim o verso aparece na tela Gerenciar Midia, igual a um upload manual.
     */
    private function registrarNaMidia($nomeArquivo, VersoPrograma $obj, $debug = false)
    {
        try {
            $m = new MidiaUpload();
            $codOficina = (int) $obj->getCodOficina();
            $m->registrarProgramaGerado(
                $nomeArquivo,
                $codOficina > 0 ? $codOficina : '',
                (int) $obj->getCodPerfil() > 0 ? (int) $obj->getCodPerfil() : '',
                $debug
            );
        } catch (OKException $e) {
            // ok
        } catch (Exception $e) {
            // best-effort: registro na midia nao pode derrubar a geracao
            @executarLog('Falha registrar PROGRAMA na midia: ' . $e->getMessage(), 'verso-programa', true);
        }
    }

    /** Nome/titulo do evento atual (usado p/ pre-preencher o campo Titulo). */
    private function getTituloEvento()
    {
        try {
            $ev = new Evento();
            $ev->setCodEvento($this->codEvento);
            $ev->getEventoById();
            return $ev->getTitulo();
        } catch (Exception $e) {
            return '';
        }
    }

    /** Garante UTF-8 para saida JSON e para o mPDF, venha o dado como for. */
    private function paraUtf8($txt)
    {
        if ($txt === null || $txt === '') {
            return '';
        }
        if (!mb_check_encoding($txt, 'UTF-8')) {
            return mb_convert_encoding($txt, 'UTF-8', 'ISO-8859-1');
        }
        return $txt;
    }
}

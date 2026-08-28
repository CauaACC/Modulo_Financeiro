<?php

/**
 * Painel de Certificados (somente leitura).
 *
 * Junta num lugar so o que hoje esta pulverizado entre Midia, Verso do
 * Certificado e as opcoes de perfil, para o coordenador conferir DE UMA VEZ
 * se o certificado do evento esta pronto para emitir:
 *   - as pecas gerais (arte da frente, assinaturas, fundo do verso)
 *   - uma linha por tipo (perfil) que libera certificado, mostrando qual
 *     verso (programa) sera usado e se ele ja foi gerado
 *
 * NAO cadastra nada e NAO altera nenhum fluxo existente: apenas le
 * tab_opcoes_perfis, tab_arquivo (via MidiaUpload), tab_verso_programa e os
 * arquivos em certificados/verso/. Os links levam para as telas que ja
 * fazem a configuracao (Midia, Verso do Certificado).
 */
class PainelCertificadosAC
{
    public $codEvento;

    function __construct()
    {
        $this->codEvento = COD_EVENTO;
    }

    private function autenticar()
    {
        FWA_exigirPermissao(array(
            enumSistema::SIS_GERENCIAR_ADM_GERAL,
            enumSistema::SIS_GERENCIAR_CERTIFICADO,     // 30 — o direito de certificado de verdade
            enumSistema::SIS_GERENCIAR_INSCRITO,        // 13 — segue valendo, nao tira acesso de quem ja tinha
            enumSistema::SIS_GERENCIAR_AREAS_TEMATICAS,
            enumSistema::SIS_CONSULTAR_PAINEL_BORDO,
            enumSistema::SIS_CONSULTAR_INSCRITOS,
        ));
    }

    /**
     * Resumo de APTOS a emitir (presente no evento). O sistema NAO registra
     * download de certificado, entao "apto" (presente, que e o gate) e o numero
     * real que da para contar. Para o cabecalho do Painel.
     *
     * @return array [total, presentes] (inscritos ATIVADA do evento)
     */
    public function resumoCertificados($debug = false)
    {
        $this->autenticar();
        try {
            $i = new Inscrito();
            return $i->contarPresencaEvento($this->codEvento, $debug);
        } catch (Exception $e) {
            return array('total' => 0, 'presentes' => 0);
        }
    }

    /* =================================================================
     *  Dados para a view (chamados direto pela PainelCertificados.php)
     * ================================================================= */

    /**
     * Pecas gerais do certificado (valem para todos os perfis). Cada item diz
     * se a midia existe e o slot usado, para a view desenhar o "checklist".
     *
     * @return array de arrays: [rotulo, slot, ok(bool), tipo('pdf'|'img')]
     */
    public function pecasGerais($debug = false)
    {
        $this->autenticar();

        // emitir_certificado = Nao -> nada (certificado desligado).
        //
        // A assinatura do coordenador NAO e mais escondida quando o evento esta
        // com assinatura simples: escondendo, sobrava so o card do presidente e
        // quem queria subir a assinatura do coordenador subia nele -- gravando a
        // imagem errada no slot do presidente, sem nenhum aviso. Agora os dois
        // cards aparecem sempre (o tipo fica explicito) e a config so decide se
        // a peca SAI no certificado, o que a propria tela informa.
        $cfg = $this->configCertificado($debug);

        $out = array();
        foreach ($this->definicaoPecas() as $p) {
            if (!$cfg['emite']) { continue; }
            $usada = !($p['chave'] === 'coordenador' && !$cfg['dupla']);

            $arquivo = '';
            $slotUsado = $p['slots'][0];
            foreach ($p['slots'] as $slot) {
                $arquivo = $this->resolverMidia($slot);
                if ($arquivo !== '') {
                    $slotUsado = $slot;
                    break;
                }
            }
            $ext = $arquivo !== '' ? strtolower(pathinfo($arquivo, PATHINFO_EXTENSION)) : '';
            $out[] = array(
                'rotulo'     => $p['rotulo'],
                // slot de UPLOAD (o primeiro; ex.: fundo do verso sobe como PDF ou imagem
                // e a extensao decide, igual ao gerador — ver a view de Pecas).
                'slotUpload' => $p['slotUpload'],
                'aceita'     => $p['aceita'],
                // Personalizada neste evento? (existe upload proprio). Quando NAO,
                // as pecas com padrao do gestor usam o default do evento 000;
                // o fundo do verso (temPadrao=false) e opcional (verso branco).
                'ok'         => $arquivo !== '' ? 1 : 0,
                'temPadrao'  => $p['temPadrao'],
                'nome'       => $arquivo !== '' ? basename($arquivo) : '',
                'ehPdf'      => $ext === 'pdf' ? 1 : 0,
                // 0 = a peca existe e pode ser configurada, mas NAO sai no
                // certificado com a configuracao atual do evento.
                'usada'      => $usada ? 1 : 0,
                'slotUsado'  => $slotUsado,
            );
        }
        return $out;
    }

    /**
     * Definicao unica das pecas do certificado (rotulo + slots de leitura +
     * slot de upload + tipos aceitos). Usada por pecasGerais() e verPeca().
     */
    private function definicaoPecas()
    {
        return array(
            // chave = identifica a peca para o filtro por config (ver pecasGerais).
            // temPadrao = tem default do gestor (evento 000); upload = substituir.
            // A ARTE DA FRENTE saiu daqui: esta tela e so de ASSINATURAS.
            array('chave' => 'presidente', 'rotulo' => 'Assinatura do presidente',
                  'slots' => array('ASSINATURA-DO-PRESIDENTE'),
                  'slotUpload' => 'ASSINATURA-DO-PRESIDENTE', 'aceita' => 'image/*', 'temPadrao' => 1),
            array('chave' => 'coordenador', 'rotulo' => 'Assinatura do coordenador geral',
                  'slots' => array('ASSINATURA-DO-COORDENADOR-GERAL'),
                  'slotUpload' => 'ASSINATURA-DO-COORDENADOR-GERAL', 'aceita' => 'image/*', 'temPadrao' => 1),
            // O FUNDO DO VERSO nao entra aqui: e enviado na tela que gera o verso
            // (Programas -> editor), junto do programa. Poe-lo em Artes confundia.
        );
    }

    /**
     * Le os flags de certificado da Configuracao do evento. Sao eles que
     * definem o que aparece na lista de Artes (ver pecasGerais). Nunca lanca.
     *
     * @return array [emite, selo, dupla]
     */
    public function configCertificado($debug = false)
    {
        $this->autenticar();

        $emite = false; $selo = false; $dupla = false;
        try {
            $c = new EventoConfiguracao();
            $c->setCodEvento($this->codEvento);
            $c->getEventoConfiguracaoById($debug);
            $emite = ((int) $c->getEmitirCertificado() === 1);
            $selo  = ((int) $c->getCertificadoSelo() === 1);
            $dupla = ((int) $c->getCertificadoAssinaturaDupla() === 1);
        } catch (Exception $e) {
            // sem config legivel: assume que emite (nao esconde por engano)
            $emite = true;
        }
        return array('emite' => $emite, 'selo' => $selo, 'dupla' => $dupla);
    }

    /**
     * Serve uma peca (arte/assinatura/fundo) para preview. O slot vem do POST
     * mas so vale se estiver na lista branca da definicao — sem caminho arbitrario.
     */
    public function verPeca($fws_, $debug = false)
    {
        $this->autenticar();

        // O preview inline chama por GET (<img src>), entao o slot pode vir em
        // $fws_ (POST) OU no $_GET. A whitelist abaixo protege em ambos os casos.
        $slotPedido = isset($fws_['slot']) ? $fws_['slot']
            : (isset($_GET['slot']) ? $_GET['slot'] : '');
        $permitidos = array();
        foreach ($this->definicaoPecas() as $p) {
            foreach ($p['slots'] as $s) { $permitidos[] = $s; }
        }
        if (!in_array($slotPedido, $permitidos, true)) {
            header('Content-Type: text/html; charset=UTF-8');
            echo '<p style="font-family:sans-serif;padding:20px;">Pe&ccedil;a inv&aacute;lida.</p>';
            exit;
        }

        $arquivo = $this->resolverMidia($slotPedido);
        // Fundo do verso: se o slot imagem estiver vazio, tenta o PDF.
        if ($arquivo === '' && $slotPedido === 'CERTIFICADO-VERSO-FUNDO') {
            $arquivo = $this->resolverMidia('CERTIFICADO-VERSO-FUNDO-PDF');
        }
        if ($arquivo === '' || !is_file($arquivo)) {
            header('Content-Type: text/html; charset=UTF-8');
            echo '<p style="font-family:sans-serif;padding:20px;">Nenhum arquivo enviado para esta pe&ccedil;a.</p>';
            exit;
        }

        $ext = strtolower(pathinfo($arquivo, PATHINFO_EXTENSION));
        $tipos = array('pdf' => 'application/pdf', 'png' => 'image/png',
            'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'gif' => 'image/gif', 'webp' => 'image/webp');
        $mime = isset($tipos[$ext]) ? $tipos[$ext] : 'application/octet-stream';

        while (ob_get_level() > 0) { ob_end_clean(); }
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($arquivo));
        header('Content-Disposition: inline; filename="peca.' . $ext . '"');
        readfile($arquivo);
        exit;
    }

    /**
     * Uma linha por perfil que LIBERA certificado. Para cada um, resolve o
     * verso (programa) que sera usado, com a MESMA logica do certificado:
     * como o programa por perfil nao existe no fluxo de emissao, todo perfil
     * (sem atividade) recebe o programa GERAL do evento -> programa.pdf.
     *
     * @return array de arrays com rotulo, flags e info do verso
     */
    public function tiposCertificado($debug = false)
    {
        $this->autenticar();

        $linhas = array();

        try {
            $perfil = new Perfil();
            $perfil->setCodEvento($this->codEvento);
            $perfil->carregarListaPerfil('', $debug);
            $lista = $perfil->getListaPerfil();
        } catch (Exception $e) {
            $lista = array();
        }

        // Verso geral do evento (programa.pdf): existe registro? existe o PDF?
        $versoGeral = $this->infoVersoGeral();

        // Aptos (presentes no evento) por perfil -- so leitura.
        $aptos = array();
        try { $aptos = (new Inscrito())->contarAptosPorPerfil($this->codEvento, $debug); } catch (Exception $e) {}

        if (is_array($lista)) {
            foreach ($lista as $p) {
                $libera = ((int) $p->getLiberarCertificado() === 1);
                $cp = (int) $p->getCodPerfil();
                $linhas[] = array(
                    'titulo'        => $this->paraUtf8($p->getTitulo()),
                    'libera'        => $libera ? 1 : 0,
                    'totalInscritos' => (int) $p->getTotalInscritos(),
                    'aptos'         => isset($aptos[$cp]) ? (int) $aptos[$cp] : 0,
                    // Sem atividade, o verso de qualquer perfil e o geral.
                    'versoArquivo'  => $versoGeral['arquivo'],
                    'versoGerado'   => $versoGeral['gerado'],
                    'versoTitulo'   => $versoGeral['titulo'],
                );
            }
        }

        return $linhas;
    }

    /**
     * Lista os PROGRAMAS (versos) do evento: o geral + um por atividade.
     * Cada linha diz o escopo, o arquivo esperado e se ja foi gerado/enviado.
     * Serve a secao "Programas" dentro de Artes & Assinaturas.
     *
     * Escopo suportado: geral e por atividade (o certificado nao usa perfil).
     *
     * @return array de arrays
     */
    public function programasVerso($debug = false)
    {
        $this->autenticar();

        $out = array();

        // 1) Programa geral do evento (programa.pdf).
        $geral = $this->infoVersoGeral();
        $out[] = array(
            'codOficina' => 0,
            'escopo'     => 'Geral do evento',
            'detalhe'    => 'usado por todos os perfis (sem atividade)',
            'arquivo'    => $geral['arquivo'],
            'gerado'     => $geral['gerado'],
            'titulo'     => $geral['titulo'],
        );

        // 2) Um por atividade do evento.
        try {
            $of = new Oficina();
            $of->setCodEvento($this->codEvento);
            $of->carregarListaOficina('', $debug);
            $lista = $of->getListaOficina();
        } catch (Exception $e) {
            $lista = array();
        }
        if (is_array($lista)) {
            foreach ($lista as $o) {
                $cod = (int) $o->getCodOficina();
                $arquivo = 'programa-' . numberPad($cod, 7) . '.pdf';
                $gerado = false;
                try { $gerado = (bool) $o->verifProgramaCertificado(); } catch (Exception $e) {}
                $out[] = array(
                    'codOficina' => $cod,
                    'escopo'     => $this->paraUtf8($o->getTitulo()),
                    'detalhe'    => 'programa da atividade',
                    'arquivo'    => $arquivo,
                    'gerado'     => $gerado ? 1 : 0,
                    'titulo'     => $this->paraUtf8($o->getTituloCertificado()),
                );
            }
        }

        return $out;
    }

    /**
     * Recebe um PDF PRONTO e salva como o verso do escopo (geral ou atividade),
     * no mesmo lugar/nome que o gerador usa e que o certificado le. Registra no
     * Midia (best-effort), igual ao gerador. Devolve JSON.
     *
     * codOficina 0 = geral (programa.pdf); >0 = programa-{oficina}.pdf.
     */
    public function salvarProgramaPronto($fws_, $debug = false)
    {
        $this->autenticar();

        $ok = false;
        $msg = '';
        try {
            if (!isset($_FILES['arquivo']) || $_FILES['arquivo']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('Selecione um arquivo PDF.');
            }
            $tmp = $_FILES['arquivo']['tmp_name'];
            $nomeOrig = strtolower($_FILES['arquivo']['name']);
            if (substr($nomeOrig, -4) !== '.pdf') {
                throw new Exception('O programa pronto deve ser um arquivo PDF.');
            }

            $cod = isset($fws_['codOficina']) ? (int) $fws_['codOficina'] : 0;

            // Mesmo nome/caminho que o gerador usa e que o certificado le.
            $obj = new VersoPrograma();
            $obj->setCodEvento($this->codEvento);
            $obj->setCodOficina($cod);
            $nomeArquivo = $obj->gerarNomeArquivoPrograma();       // programa.pdf | programa-{7}.pdf
            $destino = $this->diretorioVerso() . $nomeArquivo;

            $this->garantirDiretorioVerso();
            if (!@move_uploaded_file($tmp, $destino)) {
                if (!@copy($tmp, $destino)) {
                    throw new Exception('Nao foi possivel salvar o arquivo no destino.');
                }
            }
            @chmod($destino, 0666);

            // Registra no Midia como PROGRAMA (best-effort), igual ao gerador.
            try {
                $m = new MidiaUpload();
                $m->registrarProgramaGerado($nomeArquivo, $cod > 0 ? $cod : '', '', $debug);
            } catch (OKException $e) {
            } catch (Exception $e) {}

            $ok = true;
            $msg = 'Programa enviado: ' . $nomeArquivo;
        } catch (Exception $e) {
            $msg = $e->getMessage();
        }

        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(array('statusMessage' => $ok ? '1' : '', 'message' => $this->paraUtf8($msg)));
        exit;
    }

    /** Cria a pasta do verso resolvendo o ".." (mesma armadilha do gerador). */
    private function garantirDiretorioVerso()
    {
        return $this->garantirDiretorio($this->diretorioVerso());
    }

    /** Pasta das assinaturas (mesma que verifAssinaturaCoordenador le). */
    /**
     * Slots de assinatura que ESTA tela pode excluir.
     * Whitelist fechada: o slot vem do POST e, sem isso, daria para apagar
     * qualquer midia do evento passando outro nome.
     */
    private function slotsAssinaturaPermitidos()
    {
        return array('ASSINATURA-DO-PRESIDENTE', 'ASSINATURA-DO-COORDENADOR-GERAL');
    }

    /**
     * Exclui a assinatura do EVENTO (presidente / coordenador geral): apaga o
     * arquivo e o registro de midia. Nao mexe em mais nada.
     *
     * Depois de excluir, a peca volta ao padrao do gestor (evento 000) -- e por
     * isso que excluir aqui e seguro: nao deixa o certificado sem assinatura,
     * so remove a personalizacao deste evento.
     */
    public function excluirPeca($fws_, $debug = false)
    {
        $this->autenticar();

        $ok = false;
        $msg = '';
        try {
            $slot = isset($fws_['slot']) ? trim($fws_['slot']) : '';
            if (!in_array($slot, $this->slotsAssinaturaPermitidos(), true)) {
                throw new Exception('Assinatura invalida para exclusao.');
            }

            $m = new MidiaUpload();
            $m->setArrayTipoArquivo('');
            $m->setTipoArquivo($slot);
            $lista = $m->getAll();
            if (empty($lista)) {
                throw new Exception('Nao ha assinatura propria deste evento para excluir.');
            }

            $apagados = 0;
            foreach ($lista as $o) {
                // 1) arquivo fisico (best-effort: registro orfao e pior que arquivo orfao)
                $o->setLocalMidia($slot);
                $nome = basename($o->getArquivoGerado());
                if ($nome !== '' && $nome !== '.' && $nome !== '..') {
                    $path = realpath(rtrim($o->getMidiaCaminho(), '/\\') . DIRECTORY_SEPARATOR . $nome);
                    if ($path !== false && is_file($path)) {
                        @unlink($path);
                    }
                }
                // 2) registro
                try {
                    $o->excluir($debug);
                } catch (OKException $e) {
                    $apagados++;
                }
            }

            $ok = true;
            $msg = 'Assinatura excluida. O certificado volta a usar o padrao do gestor.';
        } catch (Exception $e) {
            $msg = $e->getMessage();
        }

        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(array('statusMessage' => $ok ? '1' : '', 'message' => $this->paraUtf8($msg)));
        exit;
    }

    /**
     * Exclui a assinatura de uma ATIVIDADE (coordenador-{oficina}.jpg).
     * O nome do arquivo e montado aqui a partir do codigo -- nunca vem do POST.
     */
    public function excluirAssinaturaAtividade($fws_, $debug = false)
    {
        $this->autenticar();

        $ok = false;
        $msg = '';
        try {
            $cod = isset($fws_['codOficina']) ? (int) $fws_['codOficina'] : 0;
            if ($cod <= 0) {
                throw new Exception('Atividade nao informada.');
            }
            $nomeArquivo = 'coordenador-' . numberPad($cod, 7) . '.jpg';
            $dir = realpath(rtrim($this->diretorioAssinaturas(), '/\\'));
            if ($dir === false) {
                throw new Exception('Pasta de assinaturas nao encontrada.');
            }
            $destino = $dir . DIRECTORY_SEPARATOR . $nomeArquivo;
            if (!is_file($destino)) {
                throw new Exception('Esta atividade nao tem assinatura propria.');
            }
            if (!@unlink($destino)) {
                throw new Exception('Nao foi possivel excluir o arquivo da assinatura.');
            }

            $ok = true;
            $msg = 'Assinatura da atividade excluida.';
        } catch (Exception $e) {
            $msg = $e->getMessage();
        }

        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(array('statusMessage' => $ok ? '1' : '', 'message' => $this->paraUtf8($msg)));
        exit;
    }

    /**
     * Exclui o PDF do programa (verso): geral do evento ou de uma atividade.
     * Igual ao acima, o nome do arquivo e derivado do codigo, nunca do POST.
     *
     * Nao apaga o registro do editor de programa (tab_verso_programa): o texto
     * do programa continua salvo e da para gerar de novo. Aqui some so o PDF,
     * que e o que o certificado anexa no verso.
     */
    public function excluirPrograma($fws_, $debug = false)
    {
        $this->autenticar();

        $ok = false;
        $msg = '';
        try {
            $cod = isset($fws_['codOficina']) ? (int) $fws_['codOficina'] : 0;
            $nomeArquivo = $cod > 0
                ? 'programa-' . numberPad($cod, 7) . '.pdf'
                : 'programa.pdf';

            $dir = realpath(rtrim($this->diretorioVerso(), '/\\'));
            if ($dir === false) {
                throw new Exception('Pasta do verso nao encontrada.');
            }
            $destino = $dir . DIRECTORY_SEPARATOR . $nomeArquivo;
            if (!is_file($destino)) {
                throw new Exception('Nao ha programa gerado para este escopo.');
            }
            if (!@unlink($destino)) {
                throw new Exception('Nao foi possivel excluir o PDF do programa.');
            }

            $ok = true;
            $msg = 'Programa excluido: ' . $nomeArquivo . '. O texto continua salvo no editor.';
        } catch (Exception $e) {
            $msg = $e->getMessage();
        }

        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(array('statusMessage' => $ok ? '1' : '', 'message' => $this->paraUtf8($msg)));
        exit;
    }

    private function diretorioAssinaturas()
    {
        return rtrim(WB_PATH_LOG_CERTIFICADOS, '/\\') . DIRECTORY_SEPARATOR
            . '..' . DIRECTORY_SEPARATOR . 'assinaturas' . DIRECTORY_SEPARATOR;
    }

    /** Cria a pasta destino resolvendo o ".." (armadilha do WB_PATH_LOG). */
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
        if (!is_dir($dir)) { @mkdir($dir, 0777, true); }
        @chmod($dir, 0777);
        if (!is_dir($dir) || !is_writable($dir)) {
            throw new Exception('Pasta indisponivel para escrita: ' . basename($dir));
        }
        return $dir;
    }

    /**
     * Recebe uma imagem PRONTA e salva como a assinatura do coordenador de uma
     * ATIVIDADE, no mesmo arquivo que o certificado le (coordenador-{oficina}.jpg).
     * Espelha salvarProgramaPronto: upload direto, sem passar pelo cadastro.
     * Devolve JSON.
     */
    public function salvarAssinaturaPronta($fws_, $debug = false)
    {
        $this->autenticar();

        $ok = false;
        $msg = '';
        try {
            if (!isset($_FILES['arquivo']) || $_FILES['arquivo']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('Selecione um arquivo de imagem.');
            }
            $cod = isset($fws_['codOficina']) ? (int) $fws_['codOficina'] : 0;
            if ($cod <= 0) {
                throw new Exception('Atividade nao informada.');
            }
            $ext = strtolower(pathinfo($_FILES['arquivo']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, array('jpg', 'jpeg', 'png'))) {
                throw new Exception('A assinatura deve ser JPG ou PNG.');
            }

            // Nome/caminho que verifAssinaturaCoordenador (e o certificado) leem.
            $nomeArquivo = 'coordenador-' . numberPad($cod, 7) . '.jpg';
            $dir = $this->garantirDiretorio($this->diretorioAssinaturas());
            $destino = $dir . DIRECTORY_SEPARATOR . $nomeArquivo;

            if (!@move_uploaded_file($_FILES['arquivo']['tmp_name'], $destino)) {
                if (!@copy($_FILES['arquivo']['tmp_name'], $destino)) {
                    throw new Exception('Nao foi possivel salvar a assinatura no destino.');
                }
            }
            @chmod($destino, 0666);

            $ok = true;
            $msg = 'Assinatura enviada: ' . $nomeArquivo;
        } catch (Exception $e) {
            $msg = $e->getMessage();
        }

        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(array('statusMessage' => $ok ? '1' : '', 'message' => $this->paraUtf8($msg)));
        exit;
    }

    /**
     * Serve o PDF do verso GERAL ja gerado (certificados/verso/programa.pdf).
     * O arquivo mora fora do htdocs; o nome e fixo (nunca vem do POST), entao
     * nao ha como pedir um caminho arbitrario por aqui.
     */
    public function verVerso($fws_, $debug = false)
    {
        $this->autenticar();

        // Verso geral (programa.pdf) ou por atividade (programa-{oficina}.pdf).
        // codOficina vem do POST mas so escolhe entre nomes de padrao fixo,
        // sempre saneado por (int) -> sem caminho arbitrario.
        $codOficina = isset($fws_['codOficina']) ? (int) $fws_['codOficina'] : 0;
        $nome = $codOficina > 0 ? 'programa-' . numberPad($codOficina, 7) . '.pdf' : 'programa.pdf';
        $caminho = $this->diretorioVerso() . $nome;
        $real = realpath($caminho);
        if ($real === false || !is_file($real)) {
            header('Content-Type: text/html; charset=UTF-8');
            echo '<p style="font-family:sans-serif;padding:20px;">'
                . 'O verso ainda nao foi gerado. Abra o Editor de Programa e clique em '
                . '"Gerar verso (PDF)".</p>';
            exit;
        }

        while (ob_get_level() > 0) { ob_end_clean(); }
        header('Content-Type: application/pdf');
        header('Content-Length: ' . filesize($real));
        header('Content-Disposition: inline; filename="verso-programa.pdf"');
        readfile($real);
        exit;
    }

    /**
     * Uma linha por ATIVIDADE (oficina) do evento, com o estado de certificado:
     * se libera, carga horaria, titulo do certificado e se o verso proprio da
     * atividade (programa-{oficina}.pdf) ja foi gerado. So leitura.
     *
     * Fica vazio em eventos sem atividades (a view esconde a secao).
     *
     * @return array de arrays
     */
    public function atividadesCertificado($debug = false)
    {
        $this->autenticar();

        $linhas = array();
        try {
            $of = new Oficina();
            $of->setCodEvento($this->codEvento);
            $of->carregarListaOficina('', $debug);
            $lista = $of->getListaOficina();
        } catch (Exception $e) {
            $lista = array();
        }

        // Aptos por atividade (presente + atividade libera) -- view canonica.
        $aptosOf = array();
        try { $aptosOf = (new Inscrito())->contarAptosPorOficina($this->codEvento, $debug); } catch (Exception $e) {}

        if (is_array($lista)) {
            foreach ($lista as $o) {
                $libera = ((int) $o->getLiberarCertificado() === 1);
                $codOf  = (int) $o->getCodOficina();
                // Verso proprio da atividade: programa-{oficina}.pdf.
                $temVerso = false;
                try { $temVerso = (bool) $o->verifProgramaCertificado(); } catch (Exception $e) {}
                // Assinatura do coordenador da atividade: coordenador-{oficina}.jpg.
                $temAssinatura = false;
                try { $temAssinatura = (bool) $o->verifAssinaturaCoordenador(); } catch (Exception $e) {}
                // "Tem certificado proprio configurado?" = libera OU algum texto
                // de certificado preenchido (campos que ja existem na oficina).
                $temTexto = trim((string) $o->getTituloCertificado()) !== ''
                    || trim((string) $o->getQualificacaoCertificado()) !== ''
                    || trim((string) $o->getPeriodoCertificado()) !== ''
                    || trim((string) $o->getDataEmissaoCertificado()) !== '';
                $temCertProprio = $libera || $temTexto;

                $linhas[] = array(
                    'codOficina'    => (int) $o->getCodOficina(),
                    'titulo'        => $this->paraUtf8($o->getTitulo()),
                    'libera'        => $libera ? 1 : 0,
                    'aptos'         => isset($aptosOf[$codOf]) ? (int) $aptosOf[$codOf] : 0,
                    'cargaHoraria'  => $this->paraUtf8($o->getCargaHoraria()),
                    'tituloCert'    => $this->paraUtf8($o->getTituloCertificado()),
                    'versoGerado'   => $temVerso ? 1 : 0,
                    'versoArquivo'  => 'programa-' . numberPad((int) $o->getCodOficina(), 7) . '.pdf',
                    'temAssinatura' => $temAssinatura ? 1 : 0,
                    'temCertProprio' => $temCertProprio ? 1 : 0,
                );
            }
        }

        return $linhas;
    }

    /**
     * Dados de certificado de UMA atividade, para popular o modal de edicao.
     * Devolve JSON.
     */
    public function carregarAtividade($fws_, $debug = false)
    {
        $this->autenticar();

        $cod = isset($fws_['codOficina']) ? (int) $fws_['codOficina'] : 0;
        $o = new Oficina();
        $o->setCodEvento($this->codEvento);
        $o->setCodOficina($cod);
        $o->getOficinaById($debug);

        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(array(
            'codOficina'              => $cod,
            'titulo'                  => $this->paraUtf8($o->getTitulo()),
            'liberar_certificado'     => (int) $o->getLiberarCertificado(),
            'titulo_certificado'      => $this->paraUtf8($o->getTituloCertificado()),
            'qualificacao_certificado'=> $this->paraUtf8($o->getQualificacaoCertificado()),
            'carga_horaria'           => $this->paraUtf8($o->getCargaHoraria()),
            'carga_horaria_extenso'   => $this->paraUtf8($o->getCargaHorariaExtenso()),
            'periodo_certificado'     => $this->paraUtf8($o->getPeriodoCertificado()),
            'data_emissao_certificado'=> $this->paraUtf8($o->getDataEmissaoCertificado()),
            'local'                   => $this->paraUtf8($o->getLocal()),
            // O que o certificado usa quando o campo fica em branco. A tela mostra
            // isso no lugar do exemplo generico: campo vazio nao sai vazio no PDF,
            // ele herda do evento (GerenciarCertificadoAC.php:873-880).
            'heranca'                 => $this->herancaCertificadoAtividade($o, $debug),
        ));
        exit;
    }

    /**
     * O que o certificado realmente imprime quando o campo da atividade esta em
     * branco. Nao e' chute: e' a mesma heranca que GerenciarCertificadoAC aplica
     * na hora de gerar o PDF --
     *   qualificacao / periodo / data de emissao: caem no valor do evento;
     *   titulo: cai no titulo da propria atividade;
     *   carga horaria: extenso -> carga da participacao -> carga da atividade;
     *   local: cai na cidade do evento.
     *
     * Serve para a tela dizer "se ficar vazio, sai <isto>" em vez de um exemplo
     * inventado.
     */
    private function herancaCertificadoAtividade($o, $debug = false)
    {
        $periodo = '';
        $cidade  = '';
        $emissao = '';

        try {
            $ev = new Evento();
            $ev->setCodEvento($this->codEvento);
            $ev->getEventoById($debug);
            $periodo = trim((string) $ev->getPeriodo());
            $cidade  = trim((string) $ev->getCidadeUf());
        } catch (Exception $e) { /* evento sem dados nao quebra a tela */ }

        try {
            $conf = EventoConfiguracao::getInstancePopulado($this->codEvento);
            $dataConf = trim((string) $conf->getCertificadoDataEmissao());
            if ($dataConf !== '' && substr($dataConf, 0, 10) !== '0000-00-00') {
                // Mesmo formato do gerador: "Londrina (PR), 22 de Agosto de 2025".
                $emissao = 'Londrina (PR), ' . formatDateViewPorExtenso(substr($dataConf, 0, 10), false);
            }
        } catch (Exception $e) { /* sem configuracao, fica o padrao de baixo */ }

        // Sem data na configuracao do evento, o gerador monta "<cidade>, <hoje>" --
        // uma data que muda todo dia, entao nao adianta mostrar como se fosse fixa:
        // fica vazio e a tela explica a regra.
        if ($emissao === '' && $cidade !== '') {
            $emissao = $cidade . ', ' . date('d') . ' de ' . getMonthName(date('m')) . ' de ' . date('Y')
                     . ' (a data do dia da emissão)';
        }

        return array(
            'titulo'      => $this->paraUtf8($o->getTitulo()),
            'qualificacao'=> '',   // nao ha heranca: vazio simplesmente nao sai no PDF
            // A carga que o certificado usa vem da PARTICIPACAO do inscrito e muda
            // de pessoa para pessoa; nao ha um valor unico para mostrar aqui.
            'cargaHoraria'=> '',
            'periodo'     => $this->paraUtf8($periodo),
            'dataEmissao' => $this->paraUtf8($emissao),
            'local'       => $this->paraUtf8($cidade),
        );
    }

    /**
     * Grava os campos de certificado de UMA atividade (UPDATE direcionado).
     * Devolve JSON.
     */
    public function gravarAtividade($fws_, $debug = false)
    {
        $this->autenticar();

        $ok = false;
        $msg = '';
        try {
            $o = new Oficina();
            $o->setCodEvento($this->codEvento);
            $o->setCodOficina(isset($fws_['codOficina']) ? (int) $fws_['codOficina'] : 0);
            $o->setLiberarCertificado(isset($fws_['liberar_certificado']) ? (int) $fws_['liberar_certificado'] : 0);
            $o->setTituloCertificado($this->campoTexto($fws_, 'titulo_certificado'));
            $o->setQualificacaoCertificado($this->campoTexto($fws_, 'qualificacao_certificado'));
            $o->setCargaHoraria($this->campoTexto($fws_, 'carga_horaria'));
            $o->setCargaHorariaExtenso($this->campoTexto($fws_, 'carga_horaria_extenso'));
            $o->setPeriodoCertificado($this->campoTexto($fws_, 'periodo_certificado'));
            $o->setDataEmissaoCertificado($this->campoTexto($fws_, 'data_emissao_certificado'));
            $o->setLocal($this->campoTexto($fws_, 'local'));
            $o->gravarCertificado($debug);
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

    /** Le um campo de texto do POST com trim; ausente = ''. */
    private function campoTexto($fws_, $nome)
    {
        return isset($fws_[$nome]) ? trim($fws_[$nome]) : '';
    }

    /* =================================================================
     *  Helpers de leitura (nao escrevem nada)
     * ================================================================= */

    /** Estado do verso GERAL do evento (registro salvo + PDF fisico). */
    private function infoVersoGeral()
    {
        $arquivo = 'programa.pdf';
        $titulo  = '';
        $temRegistro = false;

        try {
            $vp = new VersoPrograma();
            $vp->setCodEvento($this->codEvento);
            $vp->setCodOficina(0);
            if ($vp->carregarPorEscopo()) {
                $temRegistro = true;
                $titulo = $this->paraUtf8($vp->getTitulo());
            }
        } catch (Exception $e) {
            // segue com defaults
        }

        $caminho = $this->diretorioVerso() . $arquivo;
        $gerado = is_file($caminho) ? 1 : 0;

        return array(
            'arquivo'  => $arquivo,
            'gerado'   => $gerado,
            'titulo'   => $titulo,
            'registro' => $temRegistro ? 1 : 0,
        );
    }

    private function diretorioVerso()
    {
        return rtrim(WB_PATH_LOG_CERTIFICADOS, '/\\') . DIRECTORY_SEPARATOR . 'verso' . DIRECTORY_SEPARATOR;
    }

    /**
     * Caminho fisico de uma midia pelo slot, ou '' se nao houver. Reusa a
     * mesma abordagem do modulo Verso (nunca lanca).
     */
    private function resolverMidia($slot)
    {
        try {
            $m = new MidiaUpload();
            $m->setArrayTipoArquivo('');
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

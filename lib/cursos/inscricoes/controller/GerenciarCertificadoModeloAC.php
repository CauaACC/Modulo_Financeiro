<?php

/**
 * Editor do MODELO da frente do certificado.
 *
 * O coordenador posiciona os blocos (texto, assinaturas, selo, QR, data) sobre
 * a arte de fundo, em milimetros, e confere no "Visualizar" -- que gera um PDF
 * pelo MESMO caminho da emissao real (GerenciarCertificadoAC), nao por um
 * atalho proprio. Se o preview usasse outro renderizador, ele aprovaria uma
 * coisa e emitiria outra.
 */
class GerenciarCertificadoModeloAC
{
    private $codEvento;

    function __construct()
    {
        $this->codEvento = COD_EVENTO;
    }

    /** Mesmo gate das demais telas de configuracao de certificado. */
    private function autenticar()
    {
        FWA_exigirPermissao(enumSistema::SIS_GERENCIAR_ADM_GERAL);
    }

    private function paraUtf8($txt)
    {
        if (is_string($txt) && !mb_check_encoding($txt, 'UTF-8')) {
            return mb_convert_encoding($txt, 'UTF-8', 'ISO-8859-1');
        }
        return $txt;
    }

    private function campo($fws_, $nome, $default = '')
    {
        return isset($fws_[$nome]) ? trim($fws_[$nome]) : $default;
    }

    /**
     * Monta o objeto a partir do POST do editor, SEM tocar no banco.
     * Serve tanto para salvar quanto para o preview de valores nao salvos.
     */
    private function montarObjDoPost($fws_)
    {
        $obj = new CertificadoModelo();
        $obj->setCodEvento($this->codEvento);
        $obj->setCodCertificadoModelo((int) $this->campo($fws_, 'cod_certificado_modelo', 0));
        $obj->setTitulo($this->campo($fws_, 'titulo'));
        $obj->setArteFundo($this->campo($fws_, 'arte_fundo'));

        $obj->setTextoTopo($this->campo($fws_, 'texto_topo'));
        $obj->setTextoEsquerda($this->campo($fws_, 'texto_esquerda'));
        $obj->setTextoLargura($this->campo($fws_, 'texto_largura'));
        $obj->setTextoFonteTamanho($this->campo($fws_, 'texto_fonte_tamanho'));
        $obj->setTextoAlturaLinha($this->campo($fws_, 'texto_altura_linha'));
        $obj->setTextoAlinhamento($this->campo($fws_, 'texto_alinhamento'));

        $obj->setAss1Topo($this->campo($fws_, 'ass1_topo'));
        $obj->setAss1Esquerda($this->campo($fws_, 'ass1_esquerda'));
        $obj->setAss1Largura($this->campo($fws_, 'ass1_largura'));
        $obj->setAss2Topo($this->campo($fws_, 'ass2_topo'));
        $obj->setAss2Esquerda($this->campo($fws_, 'ass2_esquerda'));
        $obj->setAss2Largura($this->campo($fws_, 'ass2_largura'));

        $obj->setSeloTopo($this->campo($fws_, 'selo_topo'));
        $obj->setSeloEsquerda($this->campo($fws_, 'selo_esquerda'));
        $obj->setSeloLargura($this->campo($fws_, 'selo_largura'));

        $obj->setQrTopo($this->campo($fws_, 'qr_topo'));
        $obj->setQrEsquerda($this->campo($fws_, 'qr_esquerda'));
        $obj->setQrLargura($this->campo($fws_, 'qr_largura'));

        $obj->setDataTopo($this->campo($fws_, 'data_topo'));
        $obj->setDataEsquerda($this->campo($fws_, 'data_esquerda'));
        $obj->setDataLargura($this->campo($fws_, 'data_largura'));
        $obj->setDataFonteTamanho($this->campo($fws_, 'data_fonte_tamanho'));

        return $obj;
    }

    /** Serializa um modelo para o formulario/JS (ja com os defaults do VO). */
    private function objParaArray(CertificadoModelo $o)
    {
        return array(
            'cod_certificado_modelo' => (int) $o->getCodCertificadoModelo(),
            'titulo'                 => $this->paraUtf8($o->getTitulo()),
            'arte_fundo'             => $o->getArteFundo(),
            'texto_topo'             => $o->getTextoTopo(),
            'texto_esquerda'         => $o->getTextoEsquerda(),
            'texto_largura'          => $o->getTextoLargura(),
            'texto_fonte_tamanho'    => $o->getTextoFonteTamanho(),
            'texto_altura_linha'     => $o->getTextoAlturaLinha(),
            'texto_alinhamento'      => $o->getTextoAlinhamento(),
            'ass1_topo'              => $o->getAss1Topo(),
            'ass1_esquerda'          => $o->getAss1Esquerda(),
            'ass1_largura'           => $o->getAss1Largura(),
            'ass2_topo'              => $o->getAss2Topo(),
            'ass2_esquerda'          => $o->getAss2Esquerda(),
            'ass2_largura'           => $o->getAss2Largura(),
            'selo_topo'              => $o->getSeloTopo(),
            'selo_esquerda'          => $o->getSeloEsquerda(),
            'selo_largura'           => $o->getSeloLargura(),
            'qr_topo'                => $o->getQrTopo(),
            'qr_esquerda'            => $o->getQrEsquerda(),
            'qr_largura'             => $o->getQrLargura(),
            'data_topo'              => $o->getDataTopo(),
            'data_esquerda'          => $o->getDataEsquerda(),
            'data_largura'           => $o->getDataLargura(),
            'data_fonte_tamanho'     => $o->getDataFonteTamanho(),
        );
    }

    private function jsonSaida($array)
    {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($array);
        exit;
    }

    /* ================= leitura ================= */

    /** Modelos do evento + qual e o padrao configurado. */
    public function listar($fws_, $debug = false)
    {
        $this->autenticar();

        $obj = new CertificadoModelo();
        $obj->setCodEvento($this->codEvento);

        $itens = array();
        foreach ($obj->listar($debug) as $m) {
            $itens[] = array(
                'cod'    => (int) $m['cod_certificado_modelo'],
                'titulo' => $this->paraUtf8($m['titulo']),
            );
        }

        $this->jsonSaida(array(
            'itens'  => $itens,
            'padrao' => (int) $this->modeloPadraoDoEvento(),
        ));
    }

    /** cod_certificado_modelo padrao do evento (0 = nenhum). */
    private function modeloPadraoDoEvento()
    {
        try {
            $objConf = EventoConfiguracao::getInstancePopulado($this->codEvento);
            return (int) $objConf->getModeloCertificado();
        } catch (Exception $e) {
            return 0;
        }
    }

    /** Carrega um modelo para o formulario. Sem cod = defaults do VO. */
    public function carregar($fws_, $debug = false)
    {
        $this->autenticar();

        $obj = new CertificadoModelo();
        $obj->setCodEvento($this->codEvento);
        $cod = (int) $this->campo($fws_, 'cod_certificado_modelo', 0);
        if ($cod > 0) {
            $obj->setCodCertificadoModelo($cod);
            $obj->carregarPorCod($debug);
        }

        $this->jsonSaida($this->objParaArray($obj));
    }

    /* ================= escrita ================= */

    public function salvar($fws_, $debug = false)
    {
        $this->autenticar();

        $ok = false;
        $msg = '';
        $cod = 0;
        try {
            $obj = $this->montarObjDoPost($fws_);
            $obj->salvar($debug);
        } catch (OKException $e) {
            $ok  = true;
            $msg = $e->getMessage();
            $cod = (int) $obj->getCodCertificadoModelo();
        } catch (Exception $e) {
            $msg = $e->getMessage();
        }

        $this->jsonSaida(array(
            'statusMessage'          => $ok ? '1' : '',
            'message'                => $this->paraUtf8($msg),
            'cod_certificado_modelo' => $cod,
        ));
    }

    public function excluir($fws_, $debug = false)
    {
        $this->autenticar();

        $ok = false;
        $msg = '';
        try {
            $obj = new CertificadoModelo();
            $obj->setCodEvento($this->codEvento);
            $obj->setCodCertificadoModelo((int) $this->campo($fws_, 'cod_certificado_modelo', 0));
            $obj->excluir($debug);
        } catch (OKException $e) {
            $ok  = true;
            $msg = $e->getMessage();
        } catch (Exception $e) {
            $msg = $e->getMessage();
        }

        $this->jsonSaida(array(
            'statusMessage' => $ok ? '1' : '',
            'message'       => $this->paraUtf8($msg),
        ));
    }

    /**
     * Define (ou limpa) o modelo padrao do evento.
     * cod 0 = limpar -> volta ao comportamento de hoje.
     */
    public function definirPadraoEvento($fws_, $debug = false)
    {
        $this->autenticar();

        $ok = false;
        $msg = '';
        try {
            $cod = (int) $this->campo($fws_, 'cod_certificado_modelo', 0);
            $objConf = EventoConfiguracao::getInstancePopulado($this->codEvento);
            $objConf->setModeloCertificado($cod > 0 ? $cod : null);
            $objConf->salvarModeloCertificado($debug);
            $ok = true;
            $msg = $cod > 0
                ? "Modelo definido como padrao do evento."
                : "Padrao removido: o certificado volta ao layout antigo.";
        } catch (OKException $e) {
            $ok = true;
            $msg = $e->getMessage();
        } catch (Exception $e) {
            $msg = $e->getMessage();
        }

        $this->jsonSaida(array(
            'statusMessage' => $ok ? '1' : '',
            'message'       => $this->paraUtf8($msg),
        ));
    }

    /* ================= preview ================= */

    /**
     * Encontra um inscrito do evento que consiga emitir certificado, para
     * servir de exemplo no preview.
     *
     * Por que inscrito REAL e nao ficticio: o carregarTextoCertificado() carrega
     * a pessoa do banco e aborta sem pagamento/isencao. Fabricar alguem falso
     * exigiria cirurgia num metodo de ~1400 linhas que emite documento oficial.
     * Alem disso o preview com dados reais e mais honesto -- e o certificado que
     * vai sair, nao uma aproximacao.
     */
    private function inscritoParaPreview($debug = false)
    {
        $objL = new ListarInscritosAC();
        $condicoes = $objL->carregarCondicoes(array(), $debug);
        $condicoes['cod_evento']        = $this->codEvento;
        $condicoes['mostrarInscritos']  = 1;
        $condicoes['perspectiva']       = 'default';
        $condicoes['limite_registros']  = 40;

        $objInscrito = new Inscrito();
        $objInscrito->carregarListaInscritos($condicoes, $debug);
        $lista = $objInscrito->getListaInscritos();
        if (!$lista) {
            return 0;
        }

        foreach ($lista as $i) {
            if ($i->getStatus() != 'ATIVADA') { continue; }
            if (trim((string) $i->getNomeInscrito()) == '') { continue; }
            // Mesma trava do carregarTextoCertificado: sem isso o preview
            // morreria com "Emissao de certificado bloqueada".
            if ($i->getCodVoucher() || $i->getCodIsento()
                || $i->getTotalPagamentos() || $i->getTotalPerfisIsentos()) {
                return (int) $i->getCodInscrito();
            }
        }
        return 0;
    }

    /**
     * Gera o certificado de exemplo com as posicoes do FORMULARIO (nao precisa
     * estar salvo) e devolve o PDF inline -- mesmo padrao do Visualizar do
     * editor de verso.
     */
    public function visualizar($fws_, $debug = false)
    {
        $this->autenticar();

        try {
            $codInscrito = (int) $this->campo($fws_, 'cod_inscrito', 0);
            if ($codInscrito <= 0) {
                $codInscrito = $this->inscritoParaPreview($debug);
            }
            if ($codInscrito <= 0) {
                throw new Exception(
                    "Nao ha nenhum inscrito apto a emitir certificado neste evento ainda.<br>"
                    . "O preview usa uma pessoa real (com pagamento ou isencao) para mostrar "
                    . "exatamente o que vai sair na emissao."
                );
            }

            $objModelo = $this->montarObjDoPost($fws_);

            $objCert = new GerenciarCertificadoAC();
            $objCert->modeloCertificadoForcado = $objModelo;

            $fwsCert = array(
                'tipoCertificado' => $this->campo($fws_, 'tipoCertificado', 'congressista'),
                'cod_inscrito'    => $codInscrito,
                'cod_evento'      => $this->codEvento,
                'cod_oficina'     => $this->campo($fws_, 'cod_oficina', ''),
                'cod_trabalho'    => '',
            );

            $codInscritoSessao = isset($_SESSION['_cod_inscrito']) ? $_SESSION['_cod_inscrito'] : null;
            $_SESSION['_cod_inscrito'] = $codInscrito;
            $arquivo = $objCert->gerarCertificadoEmLote($fwsCert, $debug);
            $_SESSION['_cod_inscrito'] = $codInscritoSessao;

            if (!$arquivo || !is_file($arquivo)) {
                throw new Exception("Nao foi possivel gerar o certificado de exemplo.");
            }

            while (ob_get_level() > 0) { ob_end_clean(); }
            header('Content-Type: application/pdf');
            header('Content-Length: ' . filesize($arquivo));
            header('Content-Disposition: inline; filename="preview-certificado.pdf"');
            readfile($arquivo);
            exit;

        } catch (Exception $e) {
            while (ob_get_level() > 0) { ob_end_clean(); }
            header('Content-Type: text/html; charset=UTF-8');
            echo '<div style="font-family:sans-serif;padding:24px;max-width:640px;">'
                . '<h3 style="color:#c00;margin-top:0;">Nao foi possivel gerar o preview</h3>'
                . '<p>' . $this->paraUtf8($e->getMessage()) . '</p></div>';
            exit;
        }
    }
}

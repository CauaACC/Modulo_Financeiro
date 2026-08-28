<?php

class GerenciarCartaAceiteAC {

    function __construct(){
       /* Sem gate de permissao administrativa: quem emite a carta e o proprio
        * INSCRITO logado (mesmo caso do GerenciarReciboAC). Quem exige sessao de
        * inscrito e o carregarTextoCartaAceite(). */
       $this->codEvento = COD_EVENTO;
       $objConf = new GerenciarConfiguracaoEventoAC();
       $objConf->carregarConfiguracao($this);
    }

    public function gerarCartaAceite($fws_, $debug = false) {

        /*carregando texto*/
        try {
            $fws_ = $this->carregarTextoCartaAceite($fws_);
        } catch (Exception $exc) {
            $this->handleException($exc);
            return;
        }
        $htmlContent = $this->renderHtmlTemplate($fws_);
        $pdfContent  = $this->convertHtmlToPdf($htmlContent, $fws_);
        /* Grava o PDF em disco: e esse arquivo que o link publico de validacao le */
        file_put_contents($fws_['dirNomeArquivoDestino'], $pdfContent);
        $this->outputPdf($pdfContent, $fws_['nomeArquivoCartaAceite']);
    }

    /**
     * @param type $fws_
     * @param type $debug
     * @return string
     * @throws Exception
     */
    function carregarTextoCartaAceite($fws_, $debug = false) {

        if(!$_SESSION['_cod_inscrito']){
            throw new Exception("Sessão do usuário encerrada, retorno ao link anterior.");
        }

        $objInscrito = new Inscrito();
        $objInscrito->setCodInscrito($_SESSION['_cod_inscrito']);
        $objInscrito->getInscritoById();
        $objInscrito->carregarObjEvento();
        $objEv = $objInscrito->getObjEvento();
        $objConf = $objEv->getEventoConfiguracao();
        
        $objPopularConf = new GerenciarConfiguracaoEventoAC();
        $objPopularConf->carregarConfiguracaoByObjEvento($this, $objEv);   
        
        if(!$objInscrito->getCodIsento() and !$objInscrito->getTotalPagamentos()){
            throw new Exception("Emissão de carta de aceite bloqueada.<br>Caso necessário, "
            . "entre em contato com a administração do evento para regularizar sua inscrição.");
        }

        if($objInscrito->getNomeInscrito()==''){
            throw new Exception("Inscrito não localizado.");
        }
        
        $fws_['nome_inscrito'] = maiusculo($objInscrito->getNomeInscrito());
        $fws_['cod_inscrito'] = $objInscrito->getCodInscrito();
        
        switch ($fws_['tipoCartaAceite']) {
            case 'carta_aceite_trabalho':
                $objTrabalho = new InscritoTrabalho();
                $objTrabalho->setCodTrabalho($fws_['cod_trabalho']);
                $objTrabalho->setCodInscrito($objInscrito->getCodInscrito());
                $objTrabalho->carregarTrabalhoById();
                
//                if(!$objTrabalho->getFlgAprovado()){
//                    throw new Exception("Trabalho não aprovado");
//                }
//                if(!$objTrabalho->getFlgPresenca()){
//                    throw new Exception("Presença não confirmada durante a apresentação");
//                }
                if($objTrabalho->getAvaliacao()!='APROVADO'){
                    throw new Exception("Trabalho não aprovado");
                }
               
                if($objTrabalho->getTituloTrabalho()){
                    $fws_['titulo_trabalho'] = $objTrabalho->getTituloTrabalho();
                }
                if(!$fws_['titulo_trabalho']){
                    throw new Exception("Inscrito não esta associado a trabalho, ou trabalho não encontrado");
                }
                $fws_['cod_trabalho']    = $objTrabalho->getCodTrabalho();
                if (!file_exists(WB_PATH_LOG_CARTAS_ACEITE)) {
                    mkdir(WB_PATH_LOG_CARTAS_ACEITE, 0755);
                }
                $fws_['nomeArquivoCartaAceite']    = $fws_['tipoCartaAceite'] . '-' . $fws_['cod_inscrito']. '-' . $fws_['cod_trabalho'] .'.pdf';
                $fws_['dirNomeArquivoDestino'] = WB_PATH_LOG_CARTAS_ACEITE . $fws_['nomeArquivoCartaAceite'];
                $fws_['nomeArquivoValidacao'] = $fws_['tipoCartaAceite'] . '-' . $fws_['cod_inscrito']. '-' . $fws_['cod_trabalho'];

                $coautores ="";
                if ($objConf->getCodEvento()==70) {
                   $coautores .= $objTrabalho->getAutorApresentadorNome()? " Sendo co-autor apresentador: ".$objTrabalho->getAutorApresentadorNome() : "";
                   $coautores .= $objTrabalho->getNomeCoAutor1()? ", co-autor: ".$objTrabalho->getNomeCoAutor1() : "";
                   $coautores .= $objTrabalho->getNomeCoAutor2()? ", co-autor: ".$objTrabalho->getNomeCoAutor2() : "";
                   $coautores .= $objTrabalho->getNomeCoAutor3()? ", co-autor: ".$objTrabalho->getNomeCoAutor3() : "";
                   $coautores .= $objTrabalho->getNomeCoAutor4()? ", co-autor: ".$objTrabalho->getNomeCoAutor4() : "";
                   $coautores .= $objTrabalho->getNomeCoAutor5()? ", co-autor: ".$objTrabalho->getNomeCoAutor5() : "";
                   $coautores .= $objTrabalho->getNomeCoAutor6()? ", co-autor: ".$objTrabalho->getNomeCoAutor6() : "";
                   $coautores .= $objTrabalho->getNomeCoAutor7()? ", co-autor: ".$objTrabalho->getNomeCoAutor7() : "";
                   $coautores .= $objTrabalho->getNomeCoAutor8()? ", co-autor: ".$objTrabalho->getNomeCoAutor8() : "";
                }else{
                   $coautores .= $objTrabalho->getNomeInscrito()? " Sendo co-autor: ".$objTrabalho->getNomeInscrito() : "";
                   $coautores .= $objTrabalho->getNomeCoAutor1()? ", co-autor: ".$objTrabalho->getNomeCoAutor1() : "";
                   $coautores .= $objTrabalho->getNomeCoAutor2()? ", co-autor: ".$objTrabalho->getNomeCoAutor2() : "";
                   $coautores .= $objTrabalho->getNomeCoAutor3()? ", co-autor: ".$objTrabalho->getNomeCoAutor3() : "";
                   $coautores .= $objTrabalho->getNomeCoAutor4()? ", co-autor: ".$objTrabalho->getNomeCoAutor4() : "";
                   $coautores .= $objTrabalho->getNomeCoAutor5()? ", co-autor: ".$objTrabalho->getNomeCoAutor5() : "";
                   $coautores .= $objTrabalho->getNomeCoAutor6()? ", co-autor: ".$objTrabalho->getNomeCoAutor6() : "";
                   $coautores .= $objTrabalho->getNomeCoAutor7()? ", co-autor: ".$objTrabalho->getNomeCoAutor7() : "";
                   $coautores .= $objTrabalho->getNomeCoAutor8()? ", co-autor: ".$objTrabalho->getNomeCoAutor8() : "";
                }      

                /* Sem font-size aqui: a tipografia toda e' do modelo, para a carta
                 * nao ficar com tamanhos de fonte diferentes no meio do texto. */
                $fws_['texto_carta_aceite'] = ""
                . "Prezado(a) Participante {$objInscrito->getNomeInscrito()}"
                . "<br><br>A Comissão Organizadora do <b>". $this->nomeEvento ."</b>,"
                . " informa que o trabalho abaixo relacionado foi aceito pela Comissão Científica para apresentação neste evento "
                . "a ser realizado na cidade de ".$this->cidadeEvento.", no período de ".$this->periodoEvento."."
                . "<br><br>Trabalho:<br>{$fws_['titulo_trabalho']}"
                . "".$coautores
                . "<br><br><br>Por ser verdade firmo o presente."
                . " ";

                $fws_['modelo_template'] = "modelo-carta-aceite.php";
                break;
            default:
                throw new Exception("Tipo de carta de aceite não informado.");
                break;
        }
        
        $fws_['cabecalho'] = "<br>".$this->nomeOrganizador. "<br>".$this->enderecoOrganizador;
        if($this->cnpjOrganizador!=''){
            $fws_['cabecalho'] .= " - CNPJ: ".$this->cnpjOrganizador;
        }
        $fws_['cabecalho'] .= "";

        $fws_['data_emissao'] = $this->dataEmissao;
        $fws_['rodape'] = ""
        . $objEv->nomeOrganizador. "<br>".$objEv->enderecoOrganizador.", CNPJ: ".$objEv->cnpjOrganizador
        . "";        
        $fws_['link_validacao'] = UrlInscBase($objInscrito).'carta_aceite/validar/?id='.$fws_['nomeArquivoValidacao'];
        $fws_['qrcode_validacao'] = $this->gerarQrCodeValidacao($fws_['link_validacao']);
        $fws_['assinatura'] = $this->assinaturaOrganizador;
        $fws_['assinatura'] .= $this->telefoneOrganizador? '<br>Telefone: '.$this->telefoneOrganizador : '';

        $dirImage = dirname(__FILE__);
        if(file_exists($dirImage.'/../resources/image/logo-recibo-'.$objEv->getGestor().'.jpg')){
            $fws_['url_logo_recibo'] = 'resources/image/logo-recibo-'.$objEv->getGestor().'.jpg';
        }else if(file_exists($dirImage.'/../resources/image/logo-60X235pxN-'.COD_EVENTO.'.png')){
            $fws_['url_logo_recibo'] = 'resources/image/logo-60X235pxN-'.COD_EVENTO.'.png';
        }else if(file_exists($dirImage.'/../resources/image/logo-60X235pxN-'.$objEv->getGestor().'.png')){
            $fws_['url_logo_recibo'] = 'resources/image/logo-60X235pxN-'.$objEv->getGestor().'.png';
        }else if(file_exists($dirImage.'/../resources/image/logo-60X235pxN-'. strtoupper($objEv->getGestor()).'.png')){
            $fws_['url_logo_recibo'] = 'resources/image/logo-60X235pxN-'.strtoupper($objEv->getGestor()).'.png';
        }else{
            $fws_['url_logo_recibo'] = 'resources/image/logo-OPCTEC.jpg';
        }
        return $fws_;
    }

    /**
     * QR Code do link de validacao, em data URI para o modelo.
     * Mesma biblioteca ja usada no certificado e no voucher (Image_QRCode).
     */
    private function gerarQrCodeValidacao($link) {
        if ($link == '') {
            return '';
        }
        require_once(dirname(__FILE__) . '/../lib/Image_QRCode/Image_QRCode-0.1.3/Image/QRCode.php');
        $options = array(
            "image_type"    => "png",
            "module_size"   => 3,
            "error_correct" => "M",
            "version"       => 5,
            "output_type"   => "return"
        );
        $qr = new \Image_QRCode();
        $gd = $qr->makeCode($link, $options);
        ob_start();
        imagepng($gd);
        $png = ob_get_clean();
        imagedestroy($gd);
        return 'data:image/png;base64,' . base64_encode($png);
    }

    private function renderHtmlTemplate($fws_) {
        ob_start();
        include(dirname(__FILE__) . '/../view/carta_aceite/modelos/' . $fws_['modelo_template']);
        return ob_get_clean();
    }

    private function convertHtmlToPdf($htmlContent, $fws_) {
        /* mPDF (o html2pdf antigo nao existe mais). Mesmo caminho do GerenciarReciboAC:
         * o modelo devolve o HTML em ISO-8859-1 e aqui ele vira UTF-8 para o mPDF. */
        require_once(dirname(__FILE__) . '/../lib/vendor/autoload.php');
        $htmlContent = mb_convert_encoding($htmlContent, 'UTF-8', 'ISO-8859-1');

        /* A carta de aceite tem que caber em UMA pagina. Titulo de trabalho longo e
         * muitos co-autores empurram o rodape para a pagina 2 — nesse caso reduz a
         * escala do documento e refaz, ate caber. */
        $escalas = array(1, 0.92, 0.85, 0.78, 0.70, 0.62, 0.55);
        $ultima  = count($escalas) - 1;
        foreach ($escalas as $i => $escala) {
            $mpdf = new \Mpdf\Mpdf([
                'format' => 'A4',
                'orientation' => 'P',
                'unit' => 'pt',
                'disable_html5_parser' => true
            ]);
            $mpdf->SetDisplayMode('fullpage');
            $mpdf->WriteHTML($this->escalarHtml($htmlContent, $escala));
            if ($mpdf->page <= 1 or $i == $ultima) {
                return $mpdf->Output('', 'S');
            }
        }
    }

    /**
     * Multiplica as medidas em px do modelo pelo fator informado (fontes, margens,
     * larguras). Serve para encolher a carta ate ela caber em uma pagina.
     */
    private function escalarHtml($htmlContent, $escala) {
        if ($escala >= 1) {
            return $htmlContent;
        }
        return preg_replace_callback(
            '/(\d+(?:\.\d+)?)px/',
            function ($m) use ($escala) {
                return round($m[1] * $escala, 1) . 'px';
            },
            $htmlContent
        );
    }

    private function outputPdf($pdfContent, $filename) {
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename=' . $filename);
        header('Content-Length: ' . strlen($pdfContent));
        echo $pdfContent;
        exit();
    }

    private function handleException($exc) {
        echo $exc->getMessage();
        exit();
    }

    public function validarCartaAceite($fws_, $debug = false) {
        $idArquivo=$fws_['idArquivo'];
        $filepath = WB_PATH_LOG_CARTAS_ACEITE;
        $arquivo = $idArquivo.".pdf";
//        echo $filepath.$arquivo."<br>";
//        exit();
        if (file_exists($filepath.$arquivo)) {
            header("Content-Description: File Transfer");
            header("Cache-Control: private"); // work-around for IE
            header("Content-Type: application/pdf");
//            header("Content-Type: application/force-download");
            header("Content-Disposition: attachment; filename=\"".$arquivo."\"");
            header("Content-Length: " . @filesize($filepath.$arquivo));
            header("Pragma: public");
            header("Expires: 0");
            readfile($filepath.$arquivo) or die();
        } else {
            echo "<script language=\"Javascript\"> alert('Arquivo não existe!'); window.location='".WB_URL_VIEW."';</script>";
        }
    }

    /**
     *
     * @param Trabalho $obj
     * @param type $retorno BOOLEAN, MSG
     * @param type $debug
     */
    public static function comunicadoCartaAceiteLiberado($obj, $retorno = 'BOOLEAN', $debug = false) {

        $msgTo = '';
        /** Atribui o assunto */
        $msgSubject = ABREV_EVENTO." - Comunicado de Liberação de CartaAceites";
        $msgBody = '';
        /** Monta o corpo da mensagem */
        $msgBody = "";
        $msgBody .= $msgSubject."\r";
        $msgBody .= "Ola: " . $obj->getNomeInscrito()."\r\r";
        $msgBody .= "A carta de aceite de seu trabalho já estão liberada.\r";
        $msgBody .= "Para emissão acesse sua área de inscrito.\r\r";
        $msgBody .= "Login: {$obj->getEmailInscrito()}\r";
        $msgBody .= "Senha: {$obj->getCodInscrito()}\r";
        $msgBody .= $this->urlBase."/cartaaceites\r\r";
        $msgBody .= "Esta é uma mensagem automática, favor não responder.\r";
        $msgBody .= "att. Comissão Organizadora do ".ABREV_EVENTO."\r\n";
        $msgBody .= "Dúvidas: ".EMAIL_CONTATO."\r\n";
        $msgBody .= "";

        if ($debug) {
            //echo $msgBody . "<hr>";
        }

        /** Envia o e-mail */
        $msgTo = $obj->getEmailInscrito();
        //$msgTo = "contato@opctec.com.br";
        //$enviou = mail($msgTo, $msgSubject, $msgBody, HEADER_EMAIL);
        $enviou = GerenciarEmailAC::enviarEmail($msgTo, $msgSubject, $msgBody);
        //$enviou = $enviarEmail();
        //$enviou = false;
        if (!$enviou) {
            if ($retorno == 'BOOLEAN') { return false; } else { echo "<font color='red'><b>E-mail não enviado</b></font><br>"; }
        }else{
            if ($retorno == 'BOOLEAN'){ return true; }else{ echo "E-mail enviado - {$obj->getEmailInscrito()}<br>"; }
        }
    }
}
?>

<?php

class GerenciarDeclaracaoPresencaAC {

    function __construct(){
       $this->codEvento = COD_EVENTO;
       $objConf = new GerenciarConfiguracaoEventoAC();
       $objConf->carregarConfiguracao($this);
    }

    public function gerarDeclaracaoPresenca($fws_, $debug = false) {

        /*carregando texto*/
        try {
            FWA_exigirInscrito();
            $fws_ = $this->carregarTextoDeclaracaoPresenca($fws_);
        } catch (Exception $exc) {
            echo $exc->getMessage();
            exit();
        }

        /*mesclando com o modelo*/
        ob_start();
        include(dirname(__FILE__) . '/../view/declaracao_presenca/modelos/'.$fws_['modelo_template']);
        $content = ob_get_clean();

        /*gerando pdf (mPDF, mesmo padrao do comprovante de inscricao)*/
        require_once(dirname(__FILE__) . '/../lib/vendor/autoload.php');
        $content = mb_convert_encoding($content, 'UTF-8', 'ISO-8859-1');
        $mpdf = new \Mpdf\Mpdf([
            'format' => 'A4',
            'orientation' => 'P',
            'unit' => 'pt',
            'disable_html5_parser' => true
        ]);
        $mpdf->SetDisplayMode('fullpage');
        $mpdf->WriteHTML($content);
        $pdfContent = $mpdf->Output('', 'S');

        if (!file_exists(WB_PATH_LOG_DECLARACAO_PRESENCA)) {
            mkdir(WB_PATH_LOG_DECLARACAO_PRESENCA, 0755);
        }
        $arquivoPDF = WB_PATH_LOG_DECLARACAO_PRESENCA . '/' . $fws_['nomeArquivoDeclaracaoPresenca'];
        file_put_contents($arquivoPDF, $pdfContent); // grava em disco (usado tambem na validacao por link)

        // exibe o PDF gerado ao usuario
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $fws_['nomeArquivoDeclaracaoPresenca'] . '"');
        header('Content-Length: ' . strlen($pdfContent));
        echo $pdfContent;
        exit();
    }

    /**
     * @param type $fws_
     * @param type $debug
     * @return string
     * @throws Exception
     */
    function carregarTextoDeclaracaoPresenca($fws_, $debug = false) {

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

        if($objInscrito->getNomeInscrito()==''){
            throw new Exception("Inscrito não localizado.");
        }
        if($objInscrito->getImpressaoCracha()=='' and $objInscrito->getFlgPresencaInscrito()==''){
            throw new Exception("Registro de presença não confirmado.");
        }
        $fws_['relalizadoOurelalizado'] = "";
        if ($objEv->getDescLocalRealizacao()){
            $fws_['relalizadoOurelalizado'] = " realizado no(a) ".$objEv->getDescLocalRealizacao().", ";
        }
        $fws_['cidadeEvento'] = $objEv->getCidadeUf();
        $fws_['periodo'] = $objEv->getPeriodo();
        $fws_['nome_inscrito'] = maiusculo($objInscrito->getNomeInscrito());
        $fws_['cod_inscrito'] = $objInscrito->getCodInscrito();
        $fws_['tipoDeclaracaoPresenca'] = 'declaracao_presenca';
        $fws_['data_emissao'] = $objEv->getCidadeUf().", ".formatDateViewPorExtenso($objEv->getDataEmissaoDoc());
        $fws_['assinatura'] = $objEv->getPresidente(). '<br>'.$objEv->getPresidenteTitulo();
        
        switch ($fws_['tipoDeclaracaoPresenca']) {
            case 'declaracao_presenca':
                $fws_['nomeArquivoDeclaracaoPresenca']    = $fws_['tipoDeclaracaoPresenca'] . '-' . $fws_['cod_inscrito'] .'.pdf';
                $fws_['nomeArquivoValidacao'] = $fws_['tipoDeclaracaoPresenca'] . '-' . $fws_['cod_inscrito'];

                $fws_['texto_declaracao_presenca'] = ""
                . "Declaramos para os devidos fins que ".$fws_['nome_inscrito'].", inscrita no CPF: ".$objInscrito->getCpf().","
                . " esteve presente"
                . " ".$this->noOuNa." ".$objEv->getTitulo().","
                . " ".$fws_['relalizadoOurelalizado']." na cidade de ".$fws_['cidadeEvento'].", no período de ".$fws_['periodo'].". "
                . "<br><br><br> Por ser verdade firmo o presente."
                . " ";

                $fws_['modelo_template'] = "modelo-declaracao-presenca.php";
                break;
            default:
                throw new Exception("Tipo do comprovante não informado.");
                break;
        }
        
        $fws_['cabecalho'] = ""
        . "<br>".$this->nomeOrganizador. "<br><span style='font-size:12px'>".$this->enderecoOrganizador."</span>"
        . "";
        $fws_['data_emissao'] = $this->dataEmissao;
        $fws_['link_validacao'] = $this->urlBase.'/declaracao_presenca/validar/?id='.$fws_['nomeArquivoValidacao'];

        $fws_['assinatura'] = $this->assinaturaOrganizador;

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

    public function validarDeclaracaoPresenca($fws_, $debug = false) {
        $idArquivo=$fws_['idArquivo'];
        $filepath = WB_PATH_LOG_DECLARACAO_PRESENCA;
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
    public static function comunicadoDeclaracaoPresencaLiberado($obj, $retorno = 'BOOLEAN', $debug = false) {

        $msgTo = '';
        /** Atribui o assunto */
        $msgSubject = ABREV_EVENTO." - Comunicado de Liberação de Comprovante de Inscrição";
        $msgBody = '';
        /** Monta o corpo da mensagem */
        $msgBody = "";
        $msgBody .= $msgSubject."\r";
        $msgBody .= "Ola: " . $obj->getNomeInscrito()."\r\r";
        $msgBody .= "Os comprovantes de inscrição no evento já estão liberados.\r";
        $msgBody .= "Para emissão dos comprovantes acesse sua área de inscrito.\r\r";
        $msgBody .= "Login: {$obj->getEmailInscrito()}\r";
        $msgBody .= "Senha: {$obj->getCodInscrito()}\r";
        $msgBody .= $this->urlBase."/recibos\r\r";
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
//        $enviou = mail($msgTo, $msgSubject, $msgBody, HEADER_EMAIL);
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


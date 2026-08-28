<?php

class GerenciarComprovanteInscricaoAC {

    function __construct(){
       $this->codEvento = COD_EVENTO;
       $this->anoEvento = ANO_EVENTO;
       $objConf = new GerenciarConfiguracaoEventoAC();
       $objConf->carregarConfiguracao($this);
    }
    public function gerarComprovanteInscricao($fws_, $debug = false) {

        /*carregando texto*/
        try {
            $fws_ = $this->carregarTextoComprovanteInscricao($fws_);
        } catch (Exception $exc) {
            $this->handleException($exc);
            return;
        }
        $htmlContent = $this->renderHtmlTemplate($fws_);
        $pdfContent = $this->convertHtmlToPdf($htmlContent, $fws_);
        // Grava o PDF em disco
        file_put_contents($fws_['dirNomeArquivoDestino'], $pdfContent);
        $this->outputPdf($pdfContent, $fws_['nomeArquivo']);
    }
    public function gerarComprovanteInscricaoOld($fws_, $debug = false) {

        /*carregando texto*/
        try {
            $fws_ = $this->carregarTextoComprovanteInscricao($fws_);
        } catch (Exception $exc) {
            echo $exc->getMessage();
            exit();
        }

        /*mesclando com o modelo*/
        ob_start();
        header('Content-Type: text/html; charset=ISO-8859-1');
        include(dirname(__FILE__) . '/../view/comprovante_inscricao/modelos/'.$fws_['modelo_template']);
        $content = ob_get_clean();

        /*gerando pdf*/
        require_once(dirname(__FILE__) . '/../lib/html2pdf/html2pdf.class.php');
        $html2pdf = new HTML2PDF('P', 'A4', 'pt');
        $html2pdf->setTestTdInOnePage(false);
        $html2pdf->WriteHTML($content, isset($_GET['vuehtml']));
//        $html2pdf->WriteHTML($content, 1);

        if (!file_exists(WB_PATH_LOG_COMPROVANTES_INSCRICAO)) {
            mkdir(WB_PATH_LOG_COMPROVANTES_INSCRICAO, 0755);
        }
        $html2pdf->Output(WB_PATH_LOG_COMPROVANTES_INSCRICAO. '/' . $fws_['nomeArquivo'], 'F'); // grava o arquivo em disco na primeira vez que ? visualizado
        $html2pdf->Output($fws_['nomeArquivo']); // grava o arquivo em disco na primeira vez que ? visualizado
        exit();
    }

    /**
     * @param type $fws_
     * @param type $debug
     * @return string
     * @throws Exception
     */
    function carregarTextoComprovanteInscricao($fws_, $debug = false) {

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
        
        $fws_['nome_inscrito'] = maiusculo($objInscrito->getNomeInscrito());
        $fws_['cod_inscrito'] = $objInscrito->getCodInscrito();
        $fws_['tipoArquivo'] = 'comprovante_inscricao';
        
        $fws_['cabecalho'] = ""
        . "<br>".$this->nomeOrganizador. "<br><span style='font-size:12px'>".$this->enderecoOrganizador."</span>"
        . "";
        
        switch ($fws_['tipoArquivo']) {
            case 'comprovante_inscricao':
                $fws_['nomeArquivo']    = $fws_['tipoArquivo'] . '-' . $fws_['cod_inscrito'] .'.pdf';
                $fws_['nomeArquivoValidacao'] = $fws_['tipoArquivo'] . '-' . $fws_['cod_inscrito'];
                $txtDescLocalRealizacao = "";
                if ($objEv->getDescLocalRealizacao()){
                    $txtDescLocalRealizacao = $objEv->getDescLocalRealizacao().", ";
                }

                /* O campo "periodo" do evento e texto livre e raramente e'
                 * preenchido -- ficava "no período de . " no comprovante.
                 * Sem ele, cai para data_ini/data_fim (sempre preenchidos);
                 * sem nenhum dos dois, a frase toda e' omitida. */
                $periodoTexto = trim((string) $this->periodoEvento);
                if ($periodoTexto === '' && trim((string) $objEv->getDataIni()) !== '' && $objEv->getDataIni() != '0000-00-00') {
                    $dataIniFmt = formatDateViewPorExtenso(substr($objEv->getDataIni(), 0, 10));
                    $dataFimFmt = '';
                    if (trim((string) $objEv->getDataFim()) !== '' && substr($objEv->getDataFim(), 0, 10) !== substr($objEv->getDataIni(), 0, 10)) {
                        $dataFimFmt = formatDateViewPorExtenso(substr($objEv->getDataFim(), 0, 10));
                    }
                    $periodoTexto = $dataFimFmt !== '' ? "{$dataIniFmt} a {$dataFimFmt}" : $dataIniFmt;
                }
                $txtPeriodo = $periodoTexto !== '' ? "no período de {$periodoTexto}. " : "";

                /* Atividades escolhidas pelo inscrito. Sem try/catch aqui,
                 * carregarListaOficinas() lanca Exception quando nao ha
                 * nenhuma associada -- e derrubaria o comprovante inteiro
                 * para quem nao escolheu (ou o evento nao usa) atividades. */
                $txtAtividades = "";
                try {
                    $objInscrito->carregarListaOficinas($debug);
                    $listaOficinas = $objInscrito->getListaOficinas();
                    if (is_array($listaOficinas) && count($listaOficinas) > 0) {
                        $itensAtividades = "";
                        foreach ($listaOficinas as $objOf) {
                            $itensAtividades .= "<li>" . $objOf->getTitulo() . "</li>";
                        }
                        $txtAtividades = "<br><br>Atividades escolhidas:"
                            . "<ul style='margin:4px 0 0 0; padding-left:18px;'>{$itensAtividades}</ul>";
                    }
                } catch (Exception $exc) {
                    // Inscrito sem atividade associada -- segue sem essa secao.
                }

                $fws_['texto_comprovante_inscricao'] = ""
                . "Registramos o cadastro de {$objInscrito->getNomeInscrito()}, CPF: {$objInscrito->getCpfView()},"
                . " para participar ".$this->doOuDa." ".$this->nomeEventoCompleto.", "
                .   $txtDescLocalRealizacao. $txtPeriodo
                . " Este documento não vale como comprovante de quitação."
                . $txtAtividades
                . "<br><br><br> Por ser verdade firmo o presente."
                . " ";

                $fws_['modelo_template'] = "modelo-comprovante-inscricao.php";
                break;
            default:
                throw new Exception("Tipo do comprovante não informado.");
                break;
        }
        
        $fws_['data_emissao'] = $this->dataEmissao;
        $fws_['link_validacao'] = $this->urlBase.'/comprovante_inscricao/validar/?id='.$fws_['nomeArquivoValidacao'];
        $fws_['assinatura'] = $this->assinaturaOrganizador;

        if (!file_exists(WB_PATH_LOG_COMPROVANTES_INSCRICAO)) {
            mkdir(WB_PATH_LOG_COMPROVANTES_INSCRICAO, 0755);
        }
        $fws_['nomeArquivo']    = $fws_['tipoArquivo'] .'-'. $fws_['cod_inscrito'] .'.pdf';
        $fws_['dirNomeArquivoDestino'] = WB_PATH_LOG_COMPROVANTES_INSCRICAO . $fws_['nomeArquivo'];
        $fws_['nomeArquivoValidacao'] =  $fws_['tipoArquivo'] . $fws_['cod_inscrito'];
        //$fws_['modelo_template'] = "modelo-recibo.php";

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

    public function validarComprovanteInscricao($fws_, $debug = false) {
        $idArquivo=$fws_['idArquivo'];
        $filepath = WB_PATH_LOG_COMPROVANTES_INSCRICAO;
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
    public static function comunicadoComprovanteInscricaoLiberado($obj, $retorno = 'BOOLEAN', $debug = false) {

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
    private function renderHtmlTemplate($fws_) {
        ob_start();
        include(dirname(__FILE__) . '/../view/comprovante_inscricao/modelos/' . $fws_['modelo_template']);
        return ob_get_clean();
    }

    private function convertHtmlToPdf($htmlContent, $fws_) {
        // require_once(dirname(__FILE__) . '/../lib/html2pdf/html2pdf.class.php');
        // $html2pdf = new HTML2PDF('P', 'A4', 'pt');
        // $html2pdf->setTestTdInOnePage(false);
        // $html2pdf->WriteHTML($htmlContent, isset($_GET['vuehtml']));
        // $html2pdf->output($fws_['dirNomeArquivoDestino'], 'F');
        // return $html2pdf->output('', 'S');
        
            require_once(dirname(__FILE__) . '/../lib/vendor/autoload.php');
            $htmlContent = mb_convert_encoding($htmlContent, 'UTF-8', 'ISO-8859-1');
            // echo $htmlContent;
            // exit();
            $mpdf = new \Mpdf\Mpdf([
                'format' => 'A4', // Define o tamanho da página como A4
                'orientation' => 'P', // 'P' para retrato, 'L' para paisagem
                'unit' => 'pt',
                'disable_html5_parser' => true

            ]);
            // Definir a codificação de entrada
            $mpdf->SetDisplayMode('fullpage');
            $mpdf->WriteHTML($htmlContent);
            return $mpdf->Output('', 'S');

    }

    private function outputPdf($pdfContent, $filename) {
//        if (!file_exists(WB_PATH_LOG_COMPROVANTES_INSCRICAO)) {
//            mkdir(WB_PATH_LOG_COMPROVANTES_INSCRICAO, 0755);
//        }
//        $html2pdf->Output(WB_PATH_LOG_COMPROVANTES_INSCRICAO. '/' . $fws_['nomeArquivoRecibo'], 'F'); // grava o arquivo em disco na primeira vez que ? visualizado
//        $html2pdf->Output($fws_['nomeArquivoRecibo']); // grava o arquivo em disco na primeira vez que ? visualizado
//        exit();

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
}

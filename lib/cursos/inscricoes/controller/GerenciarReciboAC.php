<?php

class GerenciarReciboAC {

    function __construct(){
       $this->codEvento = COD_EVENTO;
       $this->anoEvento = ANO_EVENTO;
       $objConf = new GerenciarConfiguracaoEventoAC();
       $objConf->carregarConfiguracao($this);
    }

    public function gerarRecibo($fws_, $debug = false) {

        /*carregando texto*/
        try {
            $fws_ = $this->carregarTextoRecibo($fws_);
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

    /**
     * @param type $fws_
     * @param type $debug
     * @return string
     * @throws Exception
     */
    function carregarTextoRecibo($fws_, $debug = false) {

        $objPagamento = new Pagamento();
        $objPagamento->setCodPagamento($fws_['cod_pagamento']);
        $objPagamento->carregarPagamentoById();
        $objPedido = $objPagamento->getPedidoByIdPagamento();
        
        if(!$_SESSION['_cod_inscrito']){
            if($_SESSION['_administrativo']=='1'){
                if(!$objPagamento->getFormaPagamento()){
                    throw new Exception("Inscrito não esta associado a forma de pagamento.");
                }
                $fws_['cod_inscrito']=$objPagamento->getCodInscrito();
            }else{
                throw new Exception("Sessão do usuário encerrada, retorno ao link anterior.");
            }
        }else{
            //$fws_['cod_inscrito'] = $_SESSION['_cod_inscrito'];
            $fws_['cod_inscrito']=$objPagamento->getCodInscrito();
        }

        $objInscrito = new Inscrito();
        $objInscrito->setCodInscrito($fws_['cod_inscrito']);
        $objInscrito->getInscritoById();
        $objInscrito->carregarObjEvento();
        
        $objEv = $objInscrito->getObjEvento();
        $objConf = $objEv->getEventoConfiguracao();
        
        $objPopularConf = new GerenciarConfiguracaoEventoAC();
        $objPopularConf->carregarConfiguracaoByObjEvento($this, $objEv);   
        
        if($objInscrito->getCodIsento() and !$objInscrito->getTotalPagamentos()){
            throw new Exception("Emissão de recibo bloqueada.<br>Caso necessário, "
            . "entre em contato com a administração do evento para regularizar sua inscrição.");
        }

        if($objInscrito->getNomeInscrito()==''){
            throw new Exception("Inscrito não localizado.");
        }

        if(!$objPagamento->getFormaPagamento()){
            throw new Exception("Inscrito não esta associado a forma de pagamento.");
        }
        
        $fws_['nome_inscrito'] = maiusculo($objInscrito->getNomeInscrito());
        $fws_['cod_inscrito'] = $objInscrito->getCodInscrito();
        if ($objInscrito->getInstitucional() == 1){
            $fws_['tipoArquivo'] = 'recibo_empresa';
        }else{
            $fws_['tipoArquivo'] = 'recibo_pessoal';
        }
        
        $fws_['valor'] = formatMoneyView($objPagamento->getValor());
        $fws_['cod_pagamento'] = $objPagamento->getCodPagamento();
        $fws_['forma_pagamento'] = $objPagamento->getFormaPagamento();
        $fws_['numero_empenho'] = $objPagamento->getNumeroEmpenho();
        $fws_['data_pagamento'] = substr($objPagamento->getDataPagamento(), 0, 10);
        
        $fws_['observacao_recibo'] = '';
        if(trim($objPagamento->getObservacaoRecibo())!=''){
            $fws_['observacao_recibo'] = " ".$objPagamento->getObservacaoRecibo().", ";
        }
        if($fws_['data_pagamento']!=''){
            $this->dataEmissao = $this->cidadeOrganizador.", ".formatDateViewPorExtenso($fws_['data_pagamento']);
        }
        if($objPagamento->getDataEmissaoRecibo()!='' and $objPagamento->getDataEmissaoRecibo()!='0000-00-00'){
            $this->dataEmissao = $this->cidadeOrganizador.", ".formatDateViewPorExtenso($objPagamento->getDataEmissaoRecibo());
        }
   
        $textoIdPagador = "{$objInscrito->getNomeInscrito()}, {$objInscrito->getTipoDocCpfCnpjInscrito()}:{$objInscrito->getCpfView()}";
        if($fws_['tipoArquivo']=='recibo_empresa'){            
            $textoIdPagador = "{$objInscrito->getNomeRecibo()}, {$objInscrito->getTipoDocCpfCnpjRecibo()}:{$objInscrito->getCpfReciboView()}";
        }
        
        $textoIdInscrito = "";
        if($fws_['tipoArquivo']=='recibo_empresa'){
            $textoIdInscrito = " de {$objInscrito->getNomeInscrito()}, {$objInscrito->getTipoDocCpfCnpjInscrito()}:{$objInscrito->getCpfView()}, ";
        }

        if (!file_exists(WB_PATH_LOG_RECIBOS)) {
            mkdir(WB_PATH_LOG_RECIBOS, 0755);
        }
        $fws_['nomeArquivo']    = $fws_['tipoArquivo'] . '-' . $fws_['cod_inscrito'] .'-'.$fws_['cod_pagamento'].'.pdf';
        $fws_['dirNomeArquivoDestino'] = WB_PATH_LOG_RECIBOS . $fws_['nomeArquivo'];
        $fws_['nomeArquivoValidacao'] = $fws_['tipoArquivo'] . '-' . $fws_['cod_inscrito'] .'-'.$fws_['cod_pagamento'];
        $fws_['modelo_template'] = "modelo-recibo.php";
        
        if($this->periodoEvento){
            $this->periodoEvento = ", no período de ".$this->periodoEvento;
        }
        /**
         * @todo verificar no texto do pedido, para inserir o texto do campo observacao_recibo
         */
        /*referente ao pagamento de */
        if($objEv->getCodEvento()==45){
            $textoPedido = "(".$objPedido->getDescricao(). ") para o ".$this->nomeEventoCompleto;;
        }else{
            $textoPedido = "inscrição para o ".$this->nomeEventoCompleto;
        }    
        /**@todo implementar módulo para gerenciar complemento ou formato de texto para recibo de agendamento */
        if(in_array($objEv, array(35,52,72,90,105,119))){
            $objInscrito->carregarOjbPerfil($debug);
            $objPerfil = $objInscrito->getObjPerfil();
            $objInscrito->carregarListaOficinas($debug);
            foreach ($objInscrito->getListaOficinas() as $objOf) {
                if($objOf->getTipo()=='Agenda' and $objOf->getStatus()=='ATIVADA'){
                    $horario = formatDateHoraTrans($objOf->getDataInicio(), $formatIn='YYYYMM-DD', $formatOut='DD/MM/YYYY'). " - " .$objOf->getTitulo();
                }
            }
            $textoPedido = "agendamento de visita ao ".$this->nomeEventoCompleto
                .", para ".$objPerfil->getTitulo()
                .", agendada para o dia ".$horario;
        }
                
        switch ($fws_['forma_pagamento']) {
            case 'TRANSFERENCIA':
                $fws_['texto_recibo'] = ""
                . "Recebemos de(a) {$textoIdPagador},"
                . $fws_['observacao_recibo']
                . " a importância de R$({$fws_['valor']}),"
                . " resgitrada na inscrição número {$objInscrito->getCodInscrito()},"
                . " referente a pagamento de ".$textoPedido.","
                . " realizado na cidade de ".$this->cidadeEvento.$this->periodoEvento."."
                . "<br><br><br><br> Por ser verdade firmo o presente."
                . " ";
            break;
            case 'DOACAO':
                $fws_['texto_recibo'] = ""
                . "Recebemos de(a) {$textoIdPagador},"
                . " a importância de R$({$fws_['valor']}),"
                . " resgitrada na inscrição número {$objInscrito->getCodInscrito()}, "
                . " em Doação para o ".$this->nomeEvento.","
                . " realizado na cidade de ".$this->cidadeEvento.$this->periodoEvento."."
                . "<br><br><br><br> Por ser verdade firmo o presente."
                . " ";
            break;
            case 'APOIO':
                $fws_['texto_recibo'] = ""
                . "Recebemos de(a) {$textoIdPagador},"
                . $fws_['observacao_recibo']
                . " a importância de R$({$fws_['valor']}),"
                . " resgitrada na inscrição número {$objInscrito->getCodInscrito()},"
                . " em Apoio ao ".$this->nomeEvento.","
                . " realizado na cidade de ".$this->cidadeEvento.$this->periodoEvento."."
                . "<br><br><br><br> Por ser verdade firmo o presente."
                . " ";
            break;
            case 'EMPENHO':
                if(trim($fws_['numero_empenho'])!=''){
                    $fws_['texto_recibo'] = ""
                    . "Recebemos de(a) {$textoIdPagador},"
                    . $fws_['observacao_recibo']
                    . " a importância de R$({$fws_['valor']}), via empenho número {$fws_['numero_empenho']},"
                    . " registrado na inscrição número {$objInscrito->getCodInscrito()},"
                    . $textoIdInscrito
                    . " referente a pagamento de ".$textoPedido.","
                    . " realizado na cidade de ".$this->cidadeEvento.$this->periodoEvento."."
                    . "<br><br><br><br> Por ser verdade firmo o presente."
                    . " ";
                }else{
                    $fws_['texto_recibo'] = ""
                    . "Recebemos de(a) {$textoIdPagador},"
                    . $fws_['observacao_recibo']
                    . " a importância de R$({$fws_['valor']}), via empenho,"
                    . " registrado na inscrição número {$objInscrito->getCodInscrito()},"
                    . $textoIdInscrito
                    . " referente a pagamento de ".$textoPedido.","
                    . " realizado na cidade de ".$this->cidadeEvento.$this->periodoEvento."."
                    . "<br><br><br><br> Por ser verdade firmo o presente."
                    . " ";
                }
            break;
            case 'INEXIGIBILIDADE':
                if(trim($fws_['numero_empenho'])!=''){
                    $fws_['texto_recibo'] = ""
                    . "Recebemos de(a) {$textoIdPagador},"
                    . " a importância de R$({$fws_['valor']}), inexigibilidade número {$fws_['numero_empenho']},"
                    . " registrado na inscrição número {$objInscrito->getCodInscrito()},"
                    . $textoIdInscrito
                    . " referente a pagamento de ".$textoPedido.","
                    . " realizado na cidade de ".$this->cidadeEvento.$this->periodoEvento."."
                    . "<br><br><br><br> Por ser verdade firmo o presente."
                    . " ";
                }else{
                    $fws_['texto_recibo'] = ""
                    . "Recebemos de(a) {$textoIdPagador},"
                    . " a importância de R$({$fws_['valor']}), via inexigibilidade,"
                    . " registrado na inscrição número {$objInscrito->getCodInscrito()},"
                    . $textoIdInscrito
                    . " referente a pagamento de ".$textoPedido.","
                    . " realizado na cidade de ".$this->cidadeEvento.$this->periodoEvento."."
                    . "<br><br><br><br> Por ser verdade firmo o presente."
                    . " ";
                }
            break;
            default :
                $fws_['texto_recibo'] = ""
                . "Recebemos de(a) {$textoIdPagador},"
                . " a importância de R$({$fws_['valor']}),"
                . $fws_['observacao_recibo']
                . " registrada na inscrição número {$objInscrito->getCodInscrito()},"
                . $textoIdInscrito
                . " referente a pagamento de ".$textoPedido.","
                . " realizado na cidade de ".$this->cidadeEvento.$this->periodoEvento."."
                . "<br><br><br><br> Por ser verdade firmo o presente."
                . " ";
        }
                
        $fws_['cabecalho'] = ""
        . "<br>".$objEv->getTitulo()
        . "";
        $fws_['rodape'] = ""
        . $objEv->nomeOrganizador. "<br>".$objEv->enderecoOrganizador.", CNPJ: ".$objEv->cnpjOrganizador
        . "";
        
        $fws_['data_emissao'] = $this->dataEmissao;
        $fws_['link_validacao'] = UrlInscBase($objInscrito).'recibos/validar/?id='.$fws_['nomeArquivoValidacao'];
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
                    
//        $fws_['url_assinatura'] = "view/recibos/modelos/img/assinatura.jpg";        
        $fws_['url_assinatura'] = "";        
        
        return $fws_;
    }

    public function validarRecibo($fws_, $debug = false) {
        $idArquivo=$fws_['idArquivo'];
        $filepath = WB_PATH_LOG_RECIBOS;
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
    public static function comunicadoReciboLiberado($obj, $retorno = 'BOOLEAN', $debug = false) {

        $msgTo = '';
        /** Atribui o assunto */
        $msgSubject = ABREV_EVENTO." - Comunicado de Liberação de Recibos";
        $msgBody = '';
        /** Monta o corpo da mensagem */
        $msgBody = "";
        $msgBody .= $msgSubject."\r";
        $msgBody .= "Ola: " . $obj->getNomeInscrito()."\r\r";
        $msgBody .= "Os recibos do evento já estão liberados.\r";
        $msgBody .= "Para emissão de recibos acesse sua área de inscrito.\r\r";
        $msgBody .= "Login: {$obj->getEmailInscrito()}\r";
        $msgBody .= "Senha: {$obj->getCodInscrito()}\r";
        $msgBody .= UrlInscView($objInscrito)."/recibos\r\r";
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
    private function renderHtmlTemplate($fws_) {
        ob_start();
        include(dirname(__FILE__) . '/../view/recibos/modelos/' . $fws_['modelo_template']);
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
//        if (!file_exists(WB_PATH_LOG_RECIBOS)) {
//            mkdir(WB_PATH_LOG_RECIBOS, 0755);
//        }
//        $html2pdf->Output(WB_PATH_LOG_RECIBOS. '/' . $fws_['nomeArquivo'], 'F'); // grava o arquivo em disco na primeira vez que ? visualizado
//        $html2pdf->Output($fws_['nomeArquivo']); // grava o arquivo em disco na primeira vez que ? visualizado
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

<?php
// error_reporting(E_ALL);
// ini_set("display_errors", "On");


class GerenciarRegistroAtividadePdfAC {
    function __construct(){
       $this->codEvento = COD_EVENTO;
       $objConf = new GerenciarConfiguracaoEventoAC();
       $objConf->carregarConfiguracao($this);
    }
    public function gerarRegistroAtividadePdf($fws_, $debug = false) {

        /*carregando texto*/
        try {
            $fws_ = $this->carregarTextoRegistroAtividadePdf($fws_);
            // dd($fws_);
        } catch (Exception $exc) {
            // echo __LINE__;
            // dd($fws_);
            echo $exc->getMessage();
            exit();
        }

        /*mesclando com o modelo*/
        ob_start();
        header('Content-Type: text/html; charset=ISO-8859-1');
//        header("Content-Type: application/pdf");
//        header("Content-Disposition: inline; filename=".$fws_['nomeArquivoRegistroAtividadePdf']);
        include(dirname(__FILE__) . '/../view/carta_aceite/modelos/'.$fws_['modelo_template']);
        // echo __LINE__;
        // echo "OI";        
        // echo "Então agora ele vai pegar tudo que tem dentro do obstar, e jogar para dentro do html ou do pdf, no caso agora sera o pdf";        
        $content = ob_get_clean();
        /*gerando pdf*/
        // require_once(dirname(__FILE__) . '/../lib/html2pdf/html2pdf.class.php');
        // require_once(dirname(__FILE__) . '/../lib/html2pdf/HTML2PDF.php');
        $html2pdf = new Spipu\Html2Pdf\Html2Pdf('P', 'A4', 'pt');
        // $html2pdf = new Html2Pdf('P', 'A4', 'pt');
        $html2pdf->setTestTdInOnePage(false);
        //$html2pdf->WriteHTML($content, isset($_GET['vuehtml']));
        $html2pdf->WriteHTML($content, false);
        /** não esta usando isso abaixo */
        // if (!file_exists(WB_PATH_LOG_CARTAS_ACEITE)) {
        //     mkdir(WB_PATH_LOG_CARTAS_ACEITE, 0755);
        // }
        $html2pdf->Output('registro.pdf', 'I');
        // $html2pdf->Output(WB_PATH_LOG_CARTAS_ACEITE. '/' . $fws_['nomeArquivoRegistroAtividadePdf'], 'F'); // grava o arquivo em disco na primeira vez que ? visualizado
        // $html2pdf->Output($fws_['nomeArquivoRegistroAtividadePdf'], 'I'); // grava o arquivo em disco na primeira vez que ? visualizado
        exit();
    }

    /**
     * @param type $fws_
     * @param type $debug
     * @return string
     * @throws Exception
     */
    function carregarTextoRegistroAtividadePdf($fws_, $debug = false) {

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
        
        // if(!$objInscrito->getCodIsento() and !$objInscrito->getTotalPagamentos()){
        //     throw new Exception("Emissão de carta de aceite bloqueada.<br>Caso necessário, "
        //     . "entre em contato com a administração do evento para regularizar sua inscrição.");
        // }

        if($objInscrito->getNomeInscrito()==''){
            throw new Exception("Inscrito não localizado.");
        }
        
        $fws_['nome_inscrito'] = maiusculo($objInscrito->getNomeInscrito());
        $fws_['cod_inscrito'] = $objInscrito->getCodInscrito();
        
        /**esse switch era para fazer uma validação do tipo do documento, nada muito importante para esse caso */
        // switch ($fws_['tipoRegistroAtividadePdf']) {
        //     case 'carta_aceite_trabalho':
            /**troquei doe objL para objRA, só para ficar mais parecido, 
             * eu uso objL para ficar facil e naõ precisar ficar mudando muita coisa */
                 $objRA = new RegistroAtividade();
                 $objRA->setCodInscrito($objInscrito->getCodInscrito());

                 /**na verdade agora seria carregar a lsita de registro de atividade fazer aquela tabela, ou ao inves de instanciar 
                  * RegistraAtividade, talvez pudesse instanciar GerenciarRegistroAtividadeAC
                   ai tem que pensar agora como trazer as informações para ca
                  */
//                 $objL->carregarTrabalhoById();
                
// //                if(!$objL->getFlgAprovado()){
// //                    throw new Exception("Trabalho não aprovado");
// //                }
// //                if(!$objL->getFlgPresenca()){
// //                    throw new Exception("Presença não confirmada durante a apresentação");
// //                }
//                 if($objL->getAvaliacao()!='APROVADO'){
//                     throw new Exception("Trabalho não aprovado");
//                 }
               
//                 if($objL->getTituloTrabalho()){
//                     $fws_['titulo_trabalho'] = $objL->getTituloTrabalho();
//                 }
//                 if(!$fws_['titulo_trabalho']){
//                     throw new Exception("Inscrito não esta associado a trabalho, ou trabalho não encontrado");
//                 }
//                 $fws_['cod_trabalho']    = $objL->getCodTrabalho();
                $mes = '12-2024';
                $fws_['nomeArquivoRegistroAtividadePdf'] = 'Regsitro-Atividade-' . $fws_['cod_inscrito']. '-' . $mes .'.pdf';
                /**Não sei porque esta sem o pdf no final dessa string, depois convefere nos outros com esta
                 * aqui é o nome do arquivo, que temos que bolar o nome como vai ficar para esse documento, mas agora sera +- isso
                 */
                $fws_['nomeArquivoValidacao'] = 'Regsitro-Atividade-' . $fws_['cod_inscrito']. '-' . $mes;

   

                $fws_['texto_carta_aceite'] = ""
                . "Prezado(a) Participante {$objInscrito->getNomeInscrito()}"
                . "<span style='font-size:13px'>"
                . "<br>A Comissão Organizadora do <b>". $this->nomeEvento ."</b>,"
                . " informa que o trabalho abaixo relacionado foi aceito pela Comissão Científica para apresentação neste evento "
                . "a ser realizado na cidade de ".$this->cidadeEvento.", no período de ".$this->periodoEvento."."
                . "</span>"
                . "<br><br><br>Por ser verdade firmo o presente."
                . " ";

                /**vamos usar esse mesmo modelo, depois verifica se já existe o modelo do registro e troca aqui */
                 $fws_['modelo_template'] = "modelo-carta-aceite.php";
        //         break;
        //     default:
        //         throw new Exception("Tipo de carta de aceite não informado.");
        //         break;
        // } rapidin, eu consigo usar a variavel definida no javascript da outra tela?o #itens no caso
        // não, é que vc misturou um pouco as ideias
        // o que vc consegue fazer a instanciar talvez o ocontroler, e já traser ele aqui desenhado
        // vamos testar isso que ja fis.
        
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

    public function validarRegistroAtividadePdf($fws_, $debug = false) {
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
    public static function comunicadoRegistroAtividadePdfLiberado($obj, $retorno = 'BOOLEAN', $debug = false) {

        $msgTo = '';
        /** Atribui o assunto */
        $msgSubject = ABREV_EVENTO." - Comunicado de Liberação de RegistroAtividadePdfs";
        $msgBody = '';
        /** Monta o corpo da mensagem */
        $msgBody = "";
        $msgBody .= $msgSubject."\r";
        $msgBody .= "Ola: " . $obj->getNomeInscrito()."\r\r";
        $msgBody .= "A carta de aceite de seu trabalho já estão liberada.\r";
        $msgBody .= "Para emissão acesse sua área de inscrito.\r\r";
        $msgBody .= "Login: {$obj->getEmailInscrito()}\r";
        $msgBody .= "Senha: {$obj->getCodInscrito()}\r";
        $msgBody .= $this->urlBase."/registroAtividadePdfs\r\r";
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

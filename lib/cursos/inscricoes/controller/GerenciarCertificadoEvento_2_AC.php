<?php

class GerenciarCertificado_2_AC {

    function __construct(){
       $this->codEvento = COD_EVENTO;
    }

    public function gerarCertificado($fws_, $debug = false) {
        
        /*carregando texto*/
        try {
            $fws_ = $this->carregarTextoCertificado($fws_);
        } catch (Exception $exc) {
            echo $exc->getMessage();
            exit();
        }
        
        /*mesclando com o modelo*/
        ob_start();
        header('Content-Type: text/html; charset=ISO-8859-7');
        header("Content-Type: application/pdf");
        header("Content-Disposition: inline; filename=".$fws_['nomeArquivoCertificado']);
        include(dirname(__FILE__) . '/../view/certificados/modelos/'.$fws_['modelo_template']);
        $content = ob_get_clean();

        /*gerando pdf*/
        require_once(dirname(__FILE__) . '/../lib/html2pdf/html2pdf.class.php');
        $html2pdf = new HTML2PDF('L', 'A4', 'pt');
        $html2pdf->setTestTdInOnePage(false);
        $html2pdf->WriteHTML($content, isset($_GET['vuehtml']));
//        $html2pdf->WriteHTML($content, 1);

        if (!file_exists(WB_PATH_LOG_CERTIFICADOS)) {
            mkdir(WB_PATH_LOG_CERTIFICADOS, 0755);
        }
        $html2pdf->Output(WB_PATH_LOG_CERTIFICADOS. '/' . $fws_['nomeArquivoCertificado'], 'F'); // grava o arquivo em disco na primeira vez que � visualizado
        $html2pdf->Output($fws_['nomeArquivoCertificado']); // grava o arquivo em disco na primeira vez que � visualizado
        exit();
    }

    /**
     * @param type $fws_
     * @param type $debug
     * @return string
     * @throws Exception
     */
    function carregarTextoCertificado($fws_, $debug = false) {
        
        if(!$_SESSION['_cod_inscrito']){
            throw new Exception("Sessão do usuário encerrada, retorno ao link anterior.");
        }
        
        $objInscrito = new Inscrito();
        $objInscrito->setCodInscrito($_SESSION['_cod_inscrito']);
        $objInscrito->getInscritoById();
        
        if(!$objInscrito->getCodIsento() and !$objInscrito->getTotalPagamentos()){
            throw new Exception("Emissão de certificado bloqueada.<br>Caso necessário, entre em contato com a administração do evento para regularizar sua inscrição.");
        }
        
        if($objInscrito->getNomeInscrito()==''){
            throw new Exception("Inscrito não localizado.");
        }

        $fws_['nome_inscrito'] = maiusculo($objInscrito->getNomeInscrito());
        $fws_['cod_inscrito'] = $objInscrito->getCodInscrito();
//        if (mb_detect_encoding($fws_['nome_inscrito'] . 'x', 'UTF-8, ISO-8859-1') == 'UTF-8') {
//            $fws_['nome_inscrito'] = utf8_decode($fws_['nome_inscrito']);
//        }
        $fws_['tipoCertificado']= removerEspacos(removerAcentos(minusculo($fws_['tipoCertificado'])));
        switch ($fws_['tipoCertificado']) {
            case 'apresentador_trabalho':
                $objTrabalho = new InscritoTrabalho();
                $objTrabalho->setCodTrabalho($fws_['cod_trabalho']);
                $objTrabalho->setCodInscrito($objInscrito->getCodInscrito());
                $objTrabalho->carregarTrabalhoById();
                
//                if(!$objTrabalho->getFlgAprovado()){
//                    throw new Exception("Trabalho não aprovado");
//                }
                if(!$objTrabalho->getFlgPresenca()){
                    throw new Exception("Presenção não confirmada durante a apresentação");
                }
                if($objTrabalho->getTituloTrabalho()){
                    $fws_['titulo_trabalho'] = $objTrabalho->getTituloTrabalho();
                }
                if(!$fws_['titulo_trabalho']){
                    throw new Exception("Inscrito não esta associado a trabalho, ou trabalho não encontrado");
                }
                $fws_['nomeArquivoCertificado'] = $fws_['tipoCertificado'] . '-' . $fws_['cod_inscrito'] . '-'. $fws_['cod_trabalho'] .'.pdf';
                $fws_['nomeArquivoValidacao']   = $fws_['tipoCertificado'] . '-' . $fws_['cod_inscrito'] . '-'. $fws_['cod_trabalho'];
                            
//                $fws_['coAutores'] .= $objTrabalho->getNomeCoAutor1()? " e co-autor(es)" : "";
//                if($_SERVER['REMOTE_ADDR']=='189.90.70.56'){
//                    echo minusculo($objInscrito->getNomeInscrito())."<->".minusculo($objTrabalho->getNomeCoAutor1())."<br>";
//                }
                
                /*para ignorar nome do inscrito e manter a ordem de cadastro de co-autores*/
                if($objInscrito->getCodInscrito()=='1379'){
                    $coAutores = $objTrabalho->getNomeCoAutor1();
                }else{
                    $coAutores = $fws_['nome_inscrito'];
                }
                
                if(!@stristr(minusculo($coAutores), minusculo($objTrabalho->getNomeCoAutor1())))
                    $coAutores .= $objTrabalho->getNomeCoAutor1()? "; ".$objTrabalho->getNomeCoAutor1() : "";

                if(!@stristr(minusculo($coAutores), minusculo($objTrabalho->getNomeCoAutor2())))
                    $coAutores .= $objTrabalho->getNomeCoAutor2()? "; ".$objTrabalho->getNomeCoAutor2() : "";

                if(!@stristr(minusculo($coAutores), minusculo($objTrabalho->getNomeCoAutor3())))
                    $coAutores .= $objTrabalho->getNomeCoAutor3()? "; ".$objTrabalho->getNomeCoAutor3() : "";

                if(!@stristr(minusculo($coAutores), minusculo($objTrabalho->getNomeCoAutor4())))
                    $coAutores .= $objTrabalho->getNomeCoAutor4()? "; ".$objTrabalho->getNomeCoAutor4() : "";
                
                if(!@stristr(minusculo($coAutores), minusculo($objTrabalho->getNomeCoAutor5())))
                    $coAutores .= $objTrabalho->getNomeCoAutor5()? "; ".$objTrabalho->getNomeCoAutor5() : "";
                
                if(!@stristr(minusculo($coAutores), minusculo($objTrabalho->getNomeCoAutor6())))
                    $coAutores .= $objTrabalho->getNomeCoAutor6()? "; ".$objTrabalho->getNomeCoAutor6() : "";
        
                $fws_['coAutores'] = maiusculo($coAutores);
                
                $fws_['texto_certificado'] = ""
                . "<b>A SOCIEDADE BRASILEIRA DE MELHORAMENTO DE PLANTAS (SBMP)</b> certifica que o trabalho"
                . "<br><br><b>'{$fws_['titulo_trabalho']}'</b>,"
                . "<br><br> de autoria de <b>{$fws_['coAutores']}</b>"
                . ", foi apresentado na Sessão de Poster<br> do 9º Congresso Brasileiro de Melhoramento de Plantas (CBMP).";
                
                $fws_['modelo_template'] = "modelo-certificado-apresentador-trabalho.php";
                break;
            case 'oficina':
                
                $fws_['minicurso'] = substr($objInscrito->getNomeOficina(),7);
                if(!$fws_['minicurso']){
                    throw new Exception("Inscrito não esta associado a oficina.");
                }
                $fws_['nomeArquivoCertificado'] = $fws_['tipoCertificado'] . '-' . $fws_['cod_inscrito'] .'.pdf';
                $fws_['nomeArquivoValidacao']   = $fws_['tipoCertificado'] . '-' . $fws_['cod_inscrito'];
                
                $fws_['texto_certificado'] = ""
                . "<b>A SOCIEDADE BRASILEIRA DE MELHORAMENTO DE PLANTAS</b> certifica que"
                . "<br><b>{$fws_['nome_inscrito']}</b>,"
                . "<br> participou do Minicurso intitulado"
                . "<br><b>'{$fws_['minicurso']}'</b>,"
                . "<br> durante o 9º Congresso Brasileiro de Melhoramento de Plantas (CBMP),"
                . " com carga horária total de 4 horas.";

                $fws_['modelo_template'] = "modelo-certificado-congressista.php";
                break;
            case 'congressista':
            case 'participante':
                $fws_['nomeArquivoCertificado'] = $fws_['tipoCertificado'] . '-' . $fws_['cod_inscrito'] .'.pdf';
                $fws_['nomeArquivoValidacao']   = $fws_['tipoCertificado'] . '-' . $fws_['cod_inscrito'];
                $fws_['texto_certificado'] = ""
                . "<b>A SOCIEDADE BRASILEIRA DE MELHORAMENTO DE PLANTAS</b> certifica que"
                . "<br><b>{$fws_['nome_inscrito']}</b>,"
                . "<br> participou das atividades inerentes ao 9º Congresso Brasileiro de Melhoramento de Plantas (CBMP),"
                . "<br> com carga horária total de 23 horas.";
                    
                $fws_['modelo_template'] = "modelo-certificado-congressista.php";
                break;
            case 'equipedeapoio':
                $fws_['nomeArquivoCertificado'] = $fws_['tipoCertificado'] . '-' . $fws_['cod_inscrito'] .'.pdf';
                $fws_['nomeArquivoValidacao']   = $fws_['tipoCertificado'] . '-' . $fws_['cod_inscrito'];
                $fws_['texto_certificado'] = ""
                . "<b>A SOCIEDADE BRASILEIRA DE MELHORAMENTO DE PLANTAS</b> certifica que"
                . "<br><b>{$fws_['nome_inscrito']}</b>,"
                . "<br> participou das atividades inerentes ao 9º Congresso Brasileiro de Melhoramento de Plantas (CBMP),"
                . "<br> como membro da <b>Equipe de Apoio</b>.";
                    
                $fws_['modelo_template'] = "modelo-certificado-congressista.php";
                break;
            case 'diretoria':
                $fws_['nomeArquivoCertificado'] = $fws_['tipoCertificado'] . '-' . $fws_['cod_inscrito'] .'.pdf';
                $fws_['nomeArquivoValidacao']   = $fws_['tipoCertificado'] . '-' . $fws_['cod_inscrito'];
                $fws_['texto_certificado'] = ""
                . "<b>A SOCIEDADE BRASILEIRA DE MELHORAMENTO DE PLANTAS</b> certifica que"
                . "<br><b>{$fws_['nome_inscrito']}</b>,"
                . "<br> participou das atividades inerentes ao 9º Congresso Brasileiro de Melhoramento de Plantas (CBMP),"
                . "<br> como membro da <b>Diretoria</b>.";
                    
                $fws_['modelo_template'] = "modelo-certificado-congressista.php";
                break;
            case 'moderador':
                $fws_['nomeArquivoCertificado'] = $fws_['tipoCertificado'] . '-' . $fws_['cod_inscrito'] .'.pdf';
                $fws_['nomeArquivoValidacao']   = $fws_['tipoCertificado'] . '-' . $fws_['cod_inscrito'];
                $fws_['texto_certificado'] = ""
                . "<b>A SOCIEDADE BRASILEIRA DE MELHORAMENTO DE PLANTAS</b> certifica que"
                . "<br><b>{$fws_['nome_inscrito']}</b>,"
                . "<br> participou das atividades inerentes ao 9º Congresso Brasileiro de Melhoramento de Plantas (CBMP),"
                . "<br> na qualidade de <b>Moderador</b>.";
                    
                $fws_['modelo_template'] = "modelo-certificado-congressista.php";
                break;
            case 'comitecientifico':
                $fws_['nomeArquivoCertificado'] = $fws_['tipoCertificado'] . '-' . $fws_['cod_inscrito'] .'.pdf';
                $fws_['nomeArquivoValidacao']   = $fws_['tipoCertificado'] . '-' . $fws_['cod_inscrito'];
                $fws_['texto_certificado'] = ""
                . "<b>A SOCIEDADE BRASILEIRA DE MELHORAMENTO DE PLANTAS</b> certifica que"
                . "<br><b>{$fws_['nome_inscrito']}</b>,"
                . "<br> participou das atividades inerentes ao 9º Congresso Brasileiro de Melhoramento de Plantas (CBMP),"
                . "<br> como membro do <b>Comitê Científico</b>.";
                    
                $fws_['modelo_template'] = "modelo-certificado-congressista.php";
                break;
            case 'coordenadores':
                $fws_['nomeArquivoCertificado'] = $fws_['tipoCertificado'] . '-' . $fws_['cod_inscrito'] .'.pdf';
                $fws_['nomeArquivoValidacao']   = $fws_['tipoCertificado'] . '-' . $fws_['cod_inscrito'];
                $fws_['texto_certificado'] = ""
                . "<b>A SOCIEDADE BRASILEIRA DE MELHORAMENTO DE PLANTAS</b> certifica que"
                . "<br><b>{$fws_['nome_inscrito']}</b>,"
                . "<br> participou das atividades inerentes ao 9º Congresso Brasileiro de Melhoramento de Plantas (CBMP),"
                . "<br> como <b>Coordenador do Comitê Científico</b>.";
                    
                $fws_['modelo_template'] = "modelo-certificado-congressista.php";
                break;
            case 'palestrante':
                $fws_['nomeArquivoCertificado'] = $fws_['tipoCertificado'] . '-' . $fws_['cod_inscrito'] .'.pdf';
                $fws_['nomeArquivoValidacao']   = $fws_['tipoCertificado'] . '-' . $fws_['cod_inscrito'];
                $fws_['texto_certificado'] = ""
                . "<b>A SOCIEDADE BRASILEIRA DE MELHORAMENTO DE PLANTAS</b> certifica que"
                . "<br><b>{$fws_['nome_inscrito']}</b>,"
                . "<br> participou das atividades inerentes ao 9º Congresso Brasileiro de Melhoramento de Plantas (CBMP),"
                . "<br> na qualidade de <b>Palestrante</b>.";
                    
                $fws_['modelo_template'] = "modelo-certificado-congressista.php";
                break;
            case 'comissaoorganizadora':
                $fws_['nomeArquivoCertificado'] = $fws_['tipoCertificado'] . '-' . $fws_['cod_inscrito'] .'.pdf';
                $fws_['nomeArquivoValidacao']   = $fws_['tipoCertificado'] . '-' . $fws_['cod_inscrito'];
                $fws_['texto_certificado'] = ""
                . "<b>A SOCIEDADE BRASILEIRA DE MELHORAMENTO DE PLANTAS</b> certifica que"
                . "<br><b>{$fws_['nome_inscrito']}</b>,"
                . "<br> participou das atividades inerentes ao 9º Congresso Brasileiro de Melhoramento de Plantas (CBMP)"
                . "<br> como membro da <b>Comissão Organizadora</b>.";
                    
                $fws_['modelo_template'] = "modelo-certificado-congressista.php";
                break;
            case 'genmelhor':
                $fws_['nomeArquivoCertificado'] = $fws_['tipoCertificado'] . '-' . $fws_['cod_inscrito'] .'.pdf';
                $fws_['nomeArquivoValidacao']   = $fws_['tipoCertificado'] . '-' . $fws_['cod_inscrito'];
                $fws_['texto_certificado'] = ""
                . "<b>A SOCIEDADE BRASILEIRA DE MELHORAMENTO DE PLANTAS</b> certifica que"
                . "<br><b>{$fws_['nome_inscrito']}</b>,"
                . "<br> participou da organização do 1° Simpósio de Pós-Graduação em Genética e Melhoramento: Projetando o Futuro,"
                . "<br> durante o 9º Congresso Brasileiro de Melhoramento de Plantas (CBMP).";
                    
                $fws_['modelo_template'] = "modelo-certificado-congressista.php";
                break;
            case 'expositordestand':
                $fws_['nomeArquivoCertificado'] = $fws_['tipoCertificado'] . '-' . $fws_['cod_inscrito'] .'.pdf';
                $fws_['nomeArquivoValidacao']   = $fws_['tipoCertificado'] . '-' . $fws_['cod_inscrito'];
                $fws_['texto_certificado'] = ""
                . "<b>A SOCIEDADE BRASILEIRA DE MELHORAMENTO DE PLANTAS</b> certifica que"
                . "<br><b>{$fws_['nome_inscrito']}</b>,"
                . "<br> participou como <b>Expositor de Stand</b> durante o 9º Congresso Brasileiro de Melhoramento de Plantas (CBMP).";
                
                $fws_['modelo_template'] = "modelo-certificado-congressista.php";
                break;
//            case 'comite cientifico':
//                
//                $objInscrito->carregarListaPerfil($condicoes = false, $debug);
//                if($objInscrito->getListaPerfil()){
//                    foreach ($objInscrito->getListaPerfil() as $objPerfil) {
//                        if(strtolower($objPerfil->getTitulo())=='comite cientifico'){
//
//                        $fws_['nomeArquivoCertificado'] = $fws_['tipoCertificado'] . '-' . $fws_['cod_inscrito'] .'.pdf';
//                        $fws_['nomeArquivoValidacao']   = $fws_['tipoCertificado'] . '-' . $fws_['cod_inscrito'];
//                        $fws_['texto_certificado'] = ""
//                        . "<b>A SOCIEDADE BRASILEIRA DE MELHORAMENTO DE PLANTAS</b> certifica que"
//                        . "<br><b>{$fws_['nome_inscrito']}</b>,"
//                        . "<br> participou como Membro do Comite Científico no 9º Congresso Brasileiro de Melhoramento de Plantas (CBMP),"
//                        . "<br> com carga horária total de 4 horas.";
//
//                        $fws_['modelo_template'] = "modelo-certificado-congressista.php";
//                            
//                        }
//                    }
//                }
//
//                break;
            default:
                throw new Exception("Tipo do certificado não informado.");
                break;
        }
        $fws_['data_emissao'] = "Londrina, 18 de julho de 2018.";
        $fws_['link_validacao'] = URL_OFICIAL.'/certificados/validar/?id='.$fws_['nomeArquivoValidacao'];
        $fws_['assinatura'] = PRESIDENTE.'<br>Presidente do '.DESC_EVENTO;
        
        return $fws_;
    }
  
    public static function linkOld() {
        ?>
        <a href="../certificados/certificado.php" target='_blank'>
            <img style="margin-right:2px;" src="../imgs/icones/certificado-2.jpg" border="0" width="40px" align="left"/>
            <font size="3">
                <b>Certificado</b><br>Clique aqui para visualizar e imprimir o certificado.
                <br>
            </font></a>
        <?php
    }

    public static function link() {
        ?>
        <p style="text-align: justify; ">Para acessar, entre no link</p>
        <p style="text-align: justify; ">Além do número do CPF, será solicitado o número de inscrição no EVENTO,
        o qual poderá ser recuperado com a opção&nbsp;</p>
        <p style="text-align: justify; ">Em caso de dúvidas, favor entrar em contato</p>
        <?php
    }
    public function validarCertificado($fws_, $debug = false) {
        $idArquivo=$fws_['idArquivo'];
        $filepath = WB_PATH_LOG_CERTIFICADOS;
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
    public static function comunicadoCertificadoLiberado($obj, $retorno = 'BOOLEAN', $debug = false) {
        
        $msgTo = '';
        /** Atribui o assunto */
        $msgSubject = ABREV_EVENTO." - Comunicado de Liberação de Certificados";
        $msgBody = '';
        /** Monta o corpo da mensagem */
        $msgBody = "";
        $msgBody .= $msgSubject."\r";
        $msgBody .= "Ola: " . $obj->getNomeInscrito()."\r\r";
        $msgBody .= "Os certificados do evento já estão liberados.\r";
        $msgBody .= "Para emissão de certificados acesse sua área de inscrito.\r\r";
        $msgBody .= "Login: {$obj->getEmailInscrito()}\r";
        $msgBody .= "Senha: {$obj->getCodInscrito()}\r";
        $msgBody .= WB_URL_LOGIN."\r\r";
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
        GerenciarEmailAC::enviarEmail($msgTo, $subject, $message);
        //$enviou = $enviarEmail();
        //$enviou = false;
        if (!$enviou) {
            if ($retorno == 'BOOLEAN') { return false; } else { echo "<font color='red'><b>E-mail não enviado</b></font><br>"; }
        }else{
            if ($retorno == 'BOOLEAN'){ return true; }else{ echo "E-mail enviado - {$obj->getEmailInscrito()}<br>"; }
        }
    }
    /**
     * 
     * @param Trabalho $obj
     * @param type $retorno BOOLEAN, MSG
     * @param type $debug
     */
    public static function comunicadoIsentos($obj, $retorno = 'BOOLEAN', $debug = false) {
        
        $msgTo = '';
        /** Atribui o assunto */
        $msgSubject = ABREV_EVENTO." - Comunicado de Cadastramento de Isentos";
        $msgBody = '';
        /** Monta o corpo da mensagem */
        $msgBody = "";
        $msgBody .= $msgSubject."\r";
        $msgBody .= ".\r\r";
        $msgBody .= "Ola: " . $obj->getNomeInscrito()."\r\r";
        $msgBody .= "Sua inscrição para o ".ABREV_EVENTO.", foi efetivada automáticamente após confirmação de sua isenção.\r";
        $msgBody .= "Login: {$obj->getEmailInscrito()}\r";
        $msgBody .= "Senha: {$obj->getCodInscrito()}\r";
        $msgBody .= WB_URL_LOGIN."/\r\r";
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
?>

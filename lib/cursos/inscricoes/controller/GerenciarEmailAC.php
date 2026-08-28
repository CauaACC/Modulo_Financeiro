<?php
// use SendGrid\Mail\Mail;
use PHPMailer\PHPMailer\PHPMailer; 
use PHPMailer\PHPMailer\SMTP; 
use PHPMailer\PHPMailer\Exception; 
//require_once($_SERVER["DOCUMENT_ROOT"]."/system/insc/lib/util/enviarMensagemMail.php");
class GerenciarEmailAC{
    
    private $msgTo;
    private $msgSubject;
    private $msgBody;
    private $msgDefaultFoot = "
    Esta é uma mensagem automática, favor não responder.<br><br>
    OPCTEC Tecnologia
    <br>";
    
    public function carregarInscrito($fws_, $debug = false) {
        FWA_exigirPermissao(enumSistema::SIS_GERENCIAR_ADM_GERAL);
        $obj = new Inscrito();
        $obj->setCodInscrito($fws_['codInscrito']);
        $obj->getInscritoById($debug);
        return $obj;
    }

    /**
     * Registra na fila/historico de e-mails (tab_email_fila) um envio feito por
     * esta classe (ex.: aviso de pagamento, certificados), para aparecer em
     * GerenciarEmailFila. Nunca interrompe o fluxo principal de envio.
     * Obs.: este arquivo faz "use PHPMailer\PHPMailer\Exception", por isso os
     * catch usam \OKException e \Exception (namespace global).
     */
    private function registrarNaFila($objInscrito, $assunto, $corpo, $enviou) {
        try {
            $objFila = new EmailFila();
            $objFila->setCodInscrito($objInscrito->getCodInscrito());
            $objFila->setEmailDestinatario($objInscrito->getEMail());
            $objFila->setNomeDestinatario($objInscrito->getNomeInscrito());
            $objFila->setAssunto($assunto);
            $objFila->setCorpo($corpo);
            $objFila->setDatProgramado(date('Y-m-d H:i:s'));
            $objFila->setDatEnviado($enviou ? date('Y-m-d H:i:s') : null);
            $objFila->setStatus($enviou ? 'ENVIADO' : 'ERRO');
            $objFila->setTentativas(1);
            if (!$enviou) {
                $objFila->setErroMensagem('Falha no envio via GerenciarEmailAC::enviarEmail');
            }
            $objFila->salvar(false);
        } catch (\OKException $e) {
            // salvar() lanca OKException em caso de sucesso
        } catch (\Exception $e) {
            // falha ao gravar na fila nao deve interromper o envio
        }
    }

    public function enviarCertificados($fws_, $debug=false){
        FWA_exigirPermissao(enumSistema::SIS_GERENCIAR_ADM_GERAL);
        
        $objInscrito = $this->carregarInscrito($fws_);
        $msgTo = $objInscrito->getEMail();
        if($msgTo){
            if($debug){
                $msgTo = "zakacc@gmail.com";
            }
            try {
                ob_start();
                GerenciarInscricaoAC::gerarLinksEmailCertificados($objInscrito, $fws_, $debug);
                $contents = ob_get_contents();
                ob_end_clean();
            } catch (Exception $exc) {
                echo $exc->getMessage();
            }
            $msgFormatoHtml = true;
            $msgSubject="".ABREV_EVENTO.": Certificado disponível no sistema";
            $msgBody=""
                    ."Olá ".$objInscrito->getNomeInscrito()
                    ."<br>Segue abaixo informações para acessar certificado(s):"
                    ."<br> ".$contents
                    ."<br> ".EMAIL_CONTATO_LINK_LOGIN
                    ."<br> ".EMAIL_CONTATO_ATT
                    ."<br> ".NOME_EVENTO   
                    ."<br> ".EMAIL_CONTATO
                    ."<br>"
                    //."\r\n Data: ".Date('d/m/Y H:i')
                    ."";

            echo $debug? "E-mail debug:".$msgTo."<br>" : "";
            echo $debug? "E-mail real:".$objInscrito->getEMail()."<br>" : "";
            echo $debug? $msgSubject."<br>" : "";
            echo $debug? $msgBody."<br>" : "";

            //$enviou = mail($msgTo, $msgSubject, $msgBody, HEADER_EMAIL);
            $enviou = GerenciarEmailAC::enviarEmail($msgTo, $msgSubject, $msgBody, false, $msgFormatoHtml);
            $this->registrarNaFila($objInscrito, $msgSubject, $msgBody, $enviou);
            if($enviou){
                $message = "email Certificado enviado com sucesso!";
                $ok = true;
            }else{
                $message = "email Certificado NÃO enviado com sucesso!";
                $ok = false;
            }
        }else{
            $message = "email NÃO informado!";
            $ok = false;
        }
        echo '{"message":"' . tratarJson($message) . '","statusMessage":"' . $ok . '"}';
        exit();
    }
    
    public function enviarEmailLoginConvidado($fws_, $debug=false){
        FWA_exigirPermissao(enumSistema::SIS_GERENCIAR_ADM_GERAL);
        
        $objConvidado = new Convidado();
        $objConvidado->setCodConvidado($fws_['cod_convidado']);
        $objConvidado->getConvidadoById();
        $objLogin = $objConvidado->getObjLoginByIdUsuario($debug);
        
        $msgTo = $objConvidado->getEMail();
        if($msgTo){
            if($debug){
                $msgTo = "zakacc@gmail.com";    
            }
            $txtAviso = "";
            if(COD_EVENTO==70){
                $linkGuiaCoordenador = "https://docs.google.com/document/d/1ehzOOse6Z5VrfPs7UxQP7b9Pymhrg8-oh_fxg-j3wNU/edit?usp=sharing";
                $linkGuiaCoordenador = "<a href='".$linkGuiaCoordenador."'>Guia do Coordenador de Eixo</a>";
                $txtCoordenador = "Você foi convidado a ser coordenador de Eixo do ".NOME_EVENTO."\r\nClique no link a seguir para acessar: ".$linkGuiaCoordenador;

                $linkGuiaAvaliador = "https://docs.google.com/document/d/157LnMQoAGZZxc0C17dnBr6ox1Vca6T2Y-RjpIUSNezg/edit?usp=sharing";
                $linkGuiaAvaliador = "<a href='".$linkGuiaAvaliador."'>Guia do Avaliador</a>";
                $txtAvaliador = "Você foi convidado a ser coordenador de Eixo do ".NOME_EVENTO;

                $txtAviso = $txtCoordenador;
            }


            $msgSubject="".ABREV_EVENTO.": Acesso ao sistema administrativo";
            $msgBody=""
                    ."\r\n Olá ".$objConvidado->getNomeConvidado()
                    ."\r\n".$txtAviso
                    ."\r\n\r\n Segue abaixo os dados necessários para se logar ao sistema:"
                    ."\r\n"
                    ."\r\n Login: ".$objLogin->getLogin()
                    ."\r\n Senha: ".$objLogin->getSenha()
                    ."\r\n url: ".WB_URL_LOGIN_ADM
                    ."\r\n ".EMAIL_CONTATO_ATT
                    ."\r\n ".NOME_EVENTO                        
                    ."\r\n ".EMAIL_CONTATO
                    ."\r\n"
                    //."\r\n Data: ".Date('d/m/Y H:i')
                    ."";

            echo $debug? "E-mail debug:".$msgTo."<br>" : "";
            echo $debug? "E-mail real:".$objConvidado->getEMail()."<br>" : "";
            echo $debug? $msgSubject."<br>" : "";
            echo $debug? $msgBody."<br>" : "";

            // $msgTo = "zakacc@gmail.com";
            //$enviou = mail($msgTo, $msgSubject, $msgBody, HEADER_EMAIL);
            $enviou = GerenciarEmailAC::enviarEmail($msgTo, $msgSubject, $msgBody);
//            $enviou = GerenciarEmailAC::enviarEmail('zakacc@gmail.com', $msgSubject, $msgBody);
            if($enviou){
                $message =  "email enviado com sucesso para ".$objConvidado->getEMail()."!";
                $ok =  true;
            }else{
                $message =  "email NÃO enviado com sucesso!";
                $ok =  false;
            }
        }else{
            $message =  "email NÃO informado!";
            $ok =  false;
        }
        echo '{"message":"' . tratarJson($message) . '","statusMessage":"' . $ok . '"}';
        exit();            
    }

    public function enviarEmailLoginConvidadoAvaliador($fws_, $debug=false){
        FWA_exigirPermissao(enumSistema::SIS_GERENCIAR_ADM_GERAL);
        
        $objConvidado = new Convidado();
        $objConvidado->setCodConvidado($fws_['cod_convidado']);
        $objConvidado->getConvidadoById();
        $objConvidado->carregarObjLogin($debug);

        $msgTo = $objConvidado->getEMail();
        if($msgTo){
            if($debug){
                $msgTo = "zakacc@gmail.com";    
            }
            $txtAviso = "";
            if(COD_EVENTO==70){
                $linkGuiaAvaliador = "https://docs.google.com/document/d/157LnMQoAGZZxc0C17dnBr6ox1Vca6T2Y-RjpIUSNezg/edit?usp=sharing";
                $linkGuiaAvaliador = "<a href='".$linkGuiaAvaliador."'>Guia do Avaliador</a>";
                $txtAvaliador = $linkGuiaAvaliador;
                $txtAviso = $txtAvaliador;
            }

            $msgSubject="".ABREV_EVENTO.": Acesso ao sistema de avaliação de resumos do ".ABREV_EVENTO;
            $msgBody=""
                    ."\r\n Olá ".$objConvidado->getNomeConvidado()
                    ."\r\n\r\n Informamos que o sistema de avaliação de resumos do ".ABREV_EVENTO."".NOME_EVENTO." já pode ser acessado."
                    ."\r\n Segue abaixo os dados necessários para se logar ao sistema:"
                    ."\r\n"
                    ."\r\n Login: ".$objConvidado->getObjLogin()->getLogin()
                    ."\r\n Senha: ".$objConvidado->getObjLogin()->getSenha()
                    ."\r\n url: ".WB_URL_LOGIN_AVALIADOR
                    ."\r\n ".EMAIL_CONTATO_ATT
                    ."\r\n ".NOME_EVENTO   
                    ."\r\n ".EMAIL_CONTATO
                    ."\r\n"
                    //."\r\n Data: ".Date('d/m/Y H:i')
                    ."";

            echo $debug? "E-mail debug:".$msgTo."<br>" : "";
            echo $debug? "E-mail real:".$objConvidado->getEMail()."<br>" : "";
            echo $debug? $msgSubject."<br>" : "";
            echo $debug? $msgBody."<br>" : "";

            //$enviou = mail($msgTo, $msgSubject, $msgBody, HEADER_EMAIL);
            $enviou = GerenciarEmailAC::enviarEmail($msgTo, $msgSubject, $msgBody);
            if($enviou){
                $message = "email enviado com sucesso!";
                $ok = false;
            }else{
                $message = "email NÃO enviado com sucesso!";
                $ok = false;
            }
        }else{
            $message = "email NÃO informado!";
            $ok = false;
        }
        echo '{"message":"' . tratarJson($message) . '","statusMessage":"' . $ok . '"}';
        exit();  
    }

    public function enviarEmailAvisoPadraoBoleto($fws_, $debug=false){
        FWA_exigirPermissao(enumSistema::SIS_GERENCIAR_ADM_GERAL);
        
        $objInscrito = $this->carregarInscrito($fws_);
        $msgTo = $objInscrito->getEMail();
        if($msgTo){
            if($debug){
                $msgTo = "zakacc@gmail.com";    
            }

            $msgSubject="".ABREV_EVENTO.": Aviso boleto disponível no sistema";
            $msgBody=""
                    ."\r\n Olá ".$objInscrito->getNomeInscrito()
                    ."\r\n"
                    ."\r\n Segue abaixo informações para acessar boleto bancário referente:"
                    ."\r\n ".ABREV_EVENTO."-".NOME_EVENTO."."
                    ."\r\n Logue no sistema utilizando as credenciais abaixo:"
                    ."\r\n Acesse: " .URL_BOLETO
                    ."\r\n Login: ".$objInscrito->getEMail()
                    ."\r\n Senha: ".$objInscrito->getCodInscrito()
                    ."\r\n ".EMAIL_CONTATO_ATT
                    ."\r\n ".NOME_EVENTO   
                    ."\r\n ".EMAIL_CONTATO
                    ."\r\n"
                    //."\r\n Data: ".Date('d/m/Y H:i')
                    ."";

            echo $debug? "E-mail debug:".$msgTo."<br>" : "";
            echo $debug? "E-mail real:".$objInscrito->getEMail()."<br>" : "";
            echo $debug? $msgSubject."<br>" : "";
            echo $debug? $msgBody."<br>" : "";

            //$enviou = mail($msgTo, $msgSubject, $msgBody, HEADER_EMAIL);
            $enviou = GerenciarEmailAC::enviarEmail($msgTo, $msgSubject, $msgBody);
            if($enviou){
                $message = "email enviado com sucesso!";
                $ok = true;
            }else{
                $message = "email NÃO enviado com sucesso!";
                $ok = false;
            }
        }else{
            $message = "email NÃO informado!";
            $ok = false;
        }
        echo '{"message":"' . tratarJson($message) . '","statusMessage":"' . $ok . '"}';
    }

    public function enviarAvisoMensalidade($fws_, $debug=false){
        FWA_exigirPermissao(enumSistema::SIS_GERENCIAR_ADM_GERAL);
        $objInscrito = $this->carregarInscrito($fws_);
        $objInscrito->carregarObjEvento();
        $objEv = $objInscrito->getObjEvento();
        $msgTo = $objInscrito->getEMail();
        if($msgTo){
            if($debug){
                $msgTo = "zakacc@gmail.com";    
            }
            $urlLink = 'https://'.FWA_Dominio().'/'. strtolower($objEv->getGestor()).'/'.$objEv->getCodEvento().'/inscricoes/'.$objInscrito->getQrCode();

            $link = '<a title="Listar Boletos" target="_blank" href="'.$urlLink.'">Acessar boletos de mensalidades</a>';

            $msgSubject= "Mensalidade - ".NOME_EVENTO;
            $msgBody=""
                ."Olá ".$objInscrito->getPreNome()
                ."\r\n Informamos que o acesso para mensalidades do ".NOME_EVENTO.", já estão disponíveis."
                ."\r\n Para acessar e efetuar o pagamento das mensalidades, clique no link abaixo:"
                ."\r\n ".$urlLink
                ."\r\n Se você já realizou o pagamento, desconsidere esta mensagem. "
                ."\r\n Atenciosamente"
                ."\r\n ".EMAIL_CONTATO_ATT
                ."\r\n ".NOME_EVENTO                        
                ."\r\n ".EMAIL_CONTATO
                ."";


            echo $debug? "E-mail debug:".$msgTo."<br>" : "";
            echo $debug? "E-mail real:".$objInscrito->getEMail()."<br>" : "";
            echo $debug? $msgSubject."<br>" : "";
            echo $debug? $msgBody."<br>" : "";

            //$enviou = mail($msgTo, $msgSubject, $msgBody, HEADER_EMAIL);
            $enviou = GerenciarEmailAC::enviarEmail($msgTo, $msgSubject, $msgBody);
            if($enviou){
                $message = "email Mensalidade enviado com sucesso!";
                $ok = true;
            }else{
                $message = "email Mensalidade NÃO enviado com sucesso!";
                $ok = false;
            }
        }else{
            $message = "email NÃO informado!";
            $ok = false;
        }
        echo '{"message":"' . tratarJson($message) . '","statusMessage":"' . $ok . '"}';
        exit();
    }
    
    public function enviarAvisoPagamento($fws_, $debug=false){
        FWA_exigirPermissao(enumSistema::SIS_GERENCIAR_ADM_GERAL);
        $objInscrito = $this->carregarInscrito($fws_);
        $objInscrito->carregarObjEvento();
        $objEv = $objInscrito->getObjEvento();
        $msgTo = $objInscrito->getEMail();
        if($msgTo){
            if($debug){
                $msgTo = "zakacc@gmail.com";    
            }
            $urlLink = 'https://'.FWA_Dominio().'/'. strtolower($objEv->getGestor()).'/'.$objEv->getCodEvento().'/inscricoes/'.$objInscrito->getQrCode();

            $link = '<a title="Listar Boletos" target="_blank" href="'.$urlLink.'">Acessar boletos de mensalidades</a>';

            $msgSubject= "Inscrição Pendente - ".NOME_EVENTO;
            $msgBody=""
                . "Olá ".$objInscrito->getPreNome()
                . "\r\n "                    
                . "\r\n Você se inscreveu para participar do"
                . "\r\n ".NOME_EVENTO
                . "\r\n Até o momento, não detectamos o pagamento de sua inscrição"
                . "\r\n Para efetuar o pagamento de sua inscrição, clique no link abaixo:"
                . "\r\n ".$urlLink
                . "\r\n "
                . "\r\n Se você já realizou o pagamento ou não tem mais interesse, desconsidere esta mensagem. "
                . "\r\n Qualquer dúvida, entre em contato Atenciosamente"
                . "\r\n ".EMAIL_CONTATO_ATT
                . "\r\n ".NOME_EVENTO                        
                . "\r\n ".EMAIL_CONTATO
                . "";
//                . "\r\n OPCTEC - Plataforma de Inscrições Online"
//                . "\r\n Whats: https://api.whatsapp.com/send?phone=5543996967790&text=Meu%20Codigo%20de%20Inscrição%20é%20".$objInscrito->getCodInscrito()."%20Me%20informe%20sobre:"


            echo $debug? "E-mail debug:".$msgTo."<br>" : "";
            echo $debug? "E-mail real:".$objInscrito->getEMail()."<br>" : "";
            echo $debug? $msgSubject."<br>" : "";
            echo $debug? $msgBody."<br>" : "";

            //$enviou = mail($msgTo, $msgSubject, $msgBody, HEADER_EMAIL);
            $enviou = GerenciarEmailAC::enviarEmail($msgTo, $msgSubject, $msgBody);
            $this->registrarNaFila($objInscrito, $msgSubject, $msgBody, $enviou);
            if($enviou){
                $message = "email Aviso Pagamento enviado com sucesso!";
                $ok = true;
            }else{
                $message = "email Aviso Pagamento NÃO enviado com sucesso!";
                $ok = false;
            }
        }else{
            $message = "email NÃO informado!";
            $ok = false;
        }
        echo '{"message":"' . tratarJson($message) . '","statusMessage":"' . $ok . '"}';
        exit();
    }
    
    public function enviarAvisoTrabalho($fws_, $debug=false){
        FWA_exigirPermissao(enumSistema::SIS_GERENCIAR_ADM_GERAL);
        $objInscrito = $this->carregarInscrito($fws_);
        $objInscrito->carregarObjEvento();
        $objEv = $objInscrito->getObjEvento();
        $msgTo = $objInscrito->getEMail();
        if($msgTo){
            if($debug){
                $msgTo = "zakacc@gmail.com";    
            }
            $urlLink = 'https://'.FWA_Dominio().'/'. strtolower($objEv->getGestor()).'/'.$objEv->getCodEvento().'/inscricoes';

            $link = '<a title="Listar Boletos" target="_blank" href="'.$urlLink.'">Acessar boletos de mensalidades</a>';

            $msgSubject= "Envio de Trabalho Não Detectado - ".NOME_EVENTO;
            $msgBody=""
                . "Olá ".$objInscrito->getPreNome()
                . "\r\n "
                . "\r\n Você se inscreveu para participar do"
                . "\r\n ".NOME_EVENTO
                . "\r\n Até o momento, não detectamos o envio de seu trabalho"
                . "\r\n Para enviar seu trabalho faça login no sistema utilizando as credenciais abaixo:"
                . "\r\n Acesse: " .$urlLink
                . "\r\n "
                . "\r\n Login: ".$objInscrito->getEMail()
                . "\r\n Senha: ".$objInscrito->getCodInscrito()
                . "\r\n "   
                . "\r\n Se você já realizou o envio ou não tem interesse de enviar trabalho, desconsidere essa mensagem. "
                . "\r\n Qualquer dúvida, entre em contato"
                . "\r\n Atenciosamente"
                ."\r\n ".EMAIL_CONTATO_ATT
                ."\r\n ".NOME_EVENTO                        
                ."\r\n ".EMAIL_CONTATO
                . "\r\n Whats: https://api.whatsapp.com/send?phone=5543996967790&text=Meu%20Codigo%20de%20Inscrição%20é%20".$objInscrito->getCodInscrito()."%20Me%20informe%20sobre:"
                . "";


            echo $debug? "E-mail debug:".$msgTo."<br>" : "";
            echo $debug? "E-mail real:".$objInscrito->getEMail()."<br>" : "";
            echo $debug? $msgSubject."<br>" : "";
            echo $debug? $msgBody."<br>" : "";

            //$enviou = mail($msgTo, $msgSubject, $msgBody, HEADER_EMAIL);
            $enviou = GerenciarEmailAC::enviarEmail($msgTo, $msgSubject, $msgBody);
            if($enviou){
                $message = "email Trabalho enviado com sucesso!";
                $ok = true;
            }else{
                $message = "email Trabalho NÃO enviado com sucesso!";
                $ok = false;
            }
        }else{
            $message = "email NÃO informado!";
            $ok = false;
        }
        echo '{"message":"' . tratarJson($message) . '","statusMessage":"' . $ok . '"}';
        exit();
    }
    
    public function enviarAvisoLinkAcesso($fws_, $debug=false){
        FWA_exigirPermissao(enumSistema::SIS_GERENCIAR_ADM_GERAL);
        $objInscrito = $this->carregarInscrito($fws_);
        $objInscrito->carregarObjEvento();
        $objEv = $objInscrito->getObjEvento();
        $msgTo = $objInscrito->getEMail();
        if($msgTo){
            if($debug){
                $msgTo = "zakacc@gmail.com";    
            }
            $msgSubject= "Acesso Online para - ".NOME_EVENTO;
            $msgBody=""
                . "Olá ".$objInscrito->getPreNome()
                . "\r\n "
                . "\r\n Você se inscreveu para participar do"
                . "\r\n ".NOME_EVENTO
//                . "\r\n Hora: 31 ago. 2023 08:00 da manhã America/Bahia"
                . "\r\n "
//                . "\r\n Entrar na reunião Zoom"
//                . "\r\n https://us06web.zoom.us/j/83399965628?pwd=Zk5JUTlHZzByRk5SOG5KeEJkOGNmUT09"
//                . "\r\n "
//                . "\r\n ID da reunião: 833 9996 5628 " 
//                . "\r\n Senha de acesso: 648102 "
                . "\r\n "
                . "\r\n Atenciosamente"
                . "\r\n Coordenação do evento"
                . "";


            echo $debug? "E-mail debug:".$msgTo."<br>" : "";
            echo $debug? "E-mail real:".$objInscrito->getEMail()."<br>" : "";
            echo $debug? $msgSubject."<br>" : "";
            echo $debug? $msgBody."<br>" : "";

            //$enviou = mail($msgTo, $msgSubject, $msgBody, HEADER_EMAIL);
            $enviou = GerenciarEmailAC::enviarEmail($msgTo, $msgSubject, $msgBody);
            if($enviou){
                $message = "email Link Acesso enviado com sucesso!";
                $ok = true;
            }else{
                $message = "email Link Acesso NÃO enviado com sucesso!";
                $ok = false;
            }
        }else{
            $message = "email NÃO informado!";
            $ok = false;
        }
        echo '{"message":"' . tratarJson($message) . '","statusMessage":"' . $ok . '"}';
        exit();
    }
    
    public function enviarEmailInscricao($objEv, $objInscrito, $objPerfil, $debug = false){
        $objEvConf = EventoConfiguracao::getInstance($objInscrito->getCodEvento());
        $complemento = '';

        /**@todo implementar módulo para gerenciar complemento ou formato de texto para e-mail de agendamento */
        // if(in_array($objEv->getCodEvento(), array(35,52,72,90,105))){
        if($objEvConf->getAtividadeTipo()=='Agenda'
            || $objEvConf->getTipoFluxoInscricao()=='AGENDA'){
            $objInscrito->carregarListaOficinas();
            $listaOficinas = $objInscrito->getListaOficinas();
            if($listaOficinas!=''){
                foreach ($listaOficinas as $objOf) {
                    $complemento .= formatDateHoraTrans($objOf->getDataInicio(), $formatIn='YYYYMM-DD', $formatOut='DD/MM/YYYY')." - ".$objOf->getTitulo()."\r\n";
                }
            }
            $complemento = $complemento!=''? "\r\n".$complemento : '';
        }

        if($objInscrito->getCodEvento()==80){
            $complemento .= "\r\nInscrições Encerram-se hoje, 15/01/2025, às 18h.
Por favor, verifique se sua inscrição está completa:
Arquivo da documentação: anexado.
Gabarito: preenchido corretamente.";
        }        

        $to = $objInscrito->getEMail();
        $subject="".ABREV_EVENTO." - ".$objInscrito->getNomeApelido()."";
        $message=""
                ."\r\n\r\nOlá ".$objInscrito->getNomeApelido()
                ."\r\n\r\nSeu cadastro no ".NOME_EVENTO." foi registrado com sucesso."
                ."\r\nCategoria: ".$objPerfil->getTitulo()
                .$complemento
                ."\r\n\r\nIdentificador Público: ".$objInscrito->getIdPublico()
                ."\r\n\r\nLogin: ".$objInscrito->getEMail()
                ."\r\nSenha: ".$objInscrito->getCodInscrito()
                ."\r\nLink de Acesso: \n\r". EMAIL_CONTATO_LINK_LOGIN
                ."\r\n"
                ."\r\nDúvidas: ".EMAIL_CONTATO
                ."\r\nData: ".Date('d/m/Y H:i')
                ."";
        if($_SERVER['HTTP_HOST']!='eventos.test'){
            /**Envio aviso de inscrição */
            GerenciarEmailAC::enviarEmail($to, $subject, $message);
            if($objInscrito->getEMail2()!='' and $objInscrito->getEMail2()!=$objInscrito->getEMail()){
                GerenciarEmailAC::enviarEmail($objInscrito->getEMail2(), $subject, $message);
            }

            /**Email de monitoramento */
            //GerenciarEmailAC::enviarEmail('eventos@opctec.com.br', 'Monitorado: '.$subject, $message);

            /*Aviso de recebimento de inscrição para coordenador*/
            if($objEv->getEmailBcc()!=''){
                $message=""
                ."\r\n\r\nAviso de registro de inscrição:"
                ."\r\n\r\n".ABREV_EVENTO." - ".NOME_EVENTO
                ."\r\n\rNome do inscrito:".$objInscrito->getNomeApelido()
                ."\r\n".$objPerfil->getTitulo()
                .$complemento
                ."\r\n\r\nE-mail: ".$objInscrito->getEMail()
                ."\r\nCód.Inscrição: ".$objInscrito->getCodInscrito()
                //."\r\n\r\nPorém, somente estará confirmada após pagamento."
                ."\r\nData: ".Date('d/m/Y H:i')
                ."";

                $toCoordenador = $objEv->getEmailBcc();
                GerenciarEmailAC::enviarEmail($toCoordenador, $subject, $message);
            }

        }
        
        /**enviando via whats */
        $txtComplemento = "";
        $email = $objInscrito->getEMail();
        $msgSubject = $subject;
        $subjectExtra = '';
        
        if (strpos($email, '@hotmail.com') !== false || strpos($email, '@hotmail.com.br') !== false) {
            // if (!$fws_['receber_via_whats']) {
            //     $fws_['receber_via_whats'] = true;
            //     $subjectExtra = ' (Forçado via WhatsApp)';
            // }
        } else {
            if ($fws_['receber_via_whats']) {
                $subjectExtra = ' (Solicitado via WhatsApp)';
            }
        }
        if($fws_['receber_via_whats']){
            ob_start();
            echo $objInscrito->getEMail(). " :";
            echo "<a target='_blank' href='";
            echo "https://".$_SERVER["SERVER_NAME"]."/".$objInscrito->getUrlPrefixo()."/inscricoes";
            echo "/index.php?content=view/admin/inscritos-cadastro-ok.php&e_mail=";
            echo $objInscrito->getEMail(). "'>";
            echo "Localizar inscrição via e-mail.</a>";
            /**
            echo "<a target='_blank' href='https://api.whatsapp.com/send?phone=".limpaFoneWhats($objInscrito->getFoneContatoDdd().$objInscrito->getFoneContato())
                . "&text=Olá ".$objInscrito->getPreNome()
                . ". Segue seus dados para acesso: "
                . " https://".$_SERVER["SERVER_NAME"]."/".$objInscrito->getUrlPrefixo()."/inscricoes"
                . " login: ". $objInscrito->getEMail()
                . " senha: ". $objInscrito->getCodInscrito()
                . "'>Enviar login via whats</a>";
            */
            $msgBody = ob_get_clean();
            GerenciarEmailAC::enviarEmail('eventos@opctec.com.br', $subjectExtra.' '.$msgSubject, $msgBody);  
        }      
    }
    
    public function enviarEmailInscricao2($objEv, $objInscrito, $objPerfil, $debug = false){
        $to = $objInscrito->getEMail();
        $subject="".ABREV_EVENTO." - Inscrição: ".$objInscrito->getNomeApelido()."";
        $message=""
        ."\r\n\r\nOlá ".$objInscrito->getNomeApelido()
        ."\r\n\r\nSua inscrição no ".ABREV_EVENTO." foi alterada."
        //."\r\n\r\nPorém, somente estará confirmada após pagamento."
        ."\r\n\r\nLogin: ".$objInscrito->getEMail()
        ."\r\nSenha: ".$objInscrito->getCodInscrito()
        ."\r\nurl:".EMAIL_CONTATO_LINK_LOGIN 
        ."\r\n\r\n".ABREV_EVENTO." - ".NOME_EVENTO
        ."\r\nDúvidas: ".EMAIL_CONTATO
        ."\r\nData: ".Date('d/m/Y H:i')
        ."";
        //                mail($to, $subject, $message, HEADER_EMAIL);
        //                if($_SERVER['HTTP_HOST']!='eventos.test'){
        GerenciarEmailAC::enviarEmail($to, $subject, $message);

        /*Aviso de recebimento de inscrição para coordenador*/

        if($objEv->getEmailBcc()!=''){
        $message=""
        ."\r\n\r\nAviso de alteração de inscrição:"
        ."\r\n\r\n".ABREV_EVENTO." - ".NOME_EVENTO
        ."\r\n\rnNome do inscrito:".$objInscrito->getNomeApelido()
        ."\r\n\r\nE-mail: ".$objInscrito->getEMail()
        ."\r\nCód.Inscrição: ".$objInscrito->getCodInscrito()
//        ."\r\n\r\nPorém, somente estará confirmada após pagamento."
        ."\r\nData: ".Date('d/m/Y H:i')
        ."";
        $toCoordenador = $objEv->getEmailBcc();
        GerenciarEmailAC::enviarEmail($toCoordenador, $subject, $message);
        }
    }

    public static function enviarEmail($msgTo, $msgSubject, $msgBody, $emailBcc=false, $msgFormatoHtml = false)
    {
        // ---- TRAVA DE AMBIENTE (2026-08-20) ------------------------------
        // Em ambiente local/teste NENHUM e-mail pode sair para o endereco
        // real: os enderecos vem de uma copia do banco de producao, sao de
        // gente de verdade, e e-mail enviado nao se recolhe.
        //
        // Ate aqui a protecao dependia de cada chamador lembrar de usar
        // destinoParaAmbienteDeTeste(). Um teste local do cancelamento pela
        // API tentou escrever para a inscrita real — so nao saiu porque o
        // cURL do PHP CLI estava sem certificado. Pelo navegador teria saido.
        //
        // A deteccao e por HOST (localhost, 127.0.0.1, *.test, *.local): um
        // dominio de producao nunca casa com esses padroes, entao esta trava
        // NAO consegue derrubar o e-mail de producao. Sem host (CLI/cron),
        // cai para WB_FOCUS_NFE_AMBIENTE.
        if (self::ehAmbienteDeTeste()) {
            $emailTeste = defined('WB_EMAIL_TESTE') ? trim(WB_EMAIL_TESTE) : '';
            if ($emailTeste == '') {
                executarLog('BLOQUEADO (ambiente de teste, sem WB_EMAIL_TESTE)'
                    . ' Email to:' . $msgTo . ' Subject:' . $msgSubject,
                    "GerenciarEmailAC", true);
                return false;
            }
            $msgBody    = '[AMBIENTE DE TESTE - destinatario real: ' . $msgTo . ']<br><br>' . $msgBody;
            $msgSubject = '[TESTE] ' . $msgSubject;
            $msgTo      = $emailTeste;
            $emailBcc   = false;
        }

        // Roteamento por provedor: WB_PROVIDER_EMAIL no env.php ('sendgrid' | 'postmark').
        // Se a constante nao existir ou for invalida, mantem SendGrid (comportamento atual).
        $provedor = defined('WB_PROVIDER_EMAIL') ? strtolower(WB_PROVIDER_EMAIL) : 'sendgrid';
        if ($provedor === 'postmark' && defined('WB_POSTMARK_TOKEN') && WB_POSTMARK_TOKEN !== '') {
            return GerenciarEmailAC::enviarEmailPostmark($msgTo, $msgSubject, $msgBody, $emailBcc, $msgFormatoHtml);
        }
        return GerenciarEmailAC::enviarEmailSendGrid($msgTo, $msgSubject, $msgBody, $emailBcc, $msgFormatoHtml);
        // $SMTP = false;
        // if ((strpos($msgTo, '@hotmail.com') !== false || strpos($msgTo, '@hotmail.com.br') !== false)or($SMTP==true)) {
        //     return GerenciarEmailAC::enviarEmailSMTP($msgTo, $msgSubject, $msgBody, $msgFormatoHtml);
        // }else{
        //     return GerenciarEmailAC::enviarEmailSendGrid($msgTo, $msgSubject, $msgBody, $emailBcc, $msgFormatoHtml);
        // }

    }

    /**
     * Estamos rodando FORA de producao?
     *
     * Criterio principal: o HOST. Producao responde por dominio real
     * (opctec.net.br, opctec.com.br, sisevento.*, ...); local/homologacao
     * responde por localhost, 127.0.0.1, *.test ou *.local. Por isso esta
     * checagem nao tem como bloquear e-mail de producao por engano — que era
     * a razao de destinoParaAmbienteDeTeste() nunca ter sido ligada no canal.
     *
     * Sem host (CLI, cron, fila) nao ha o que inspecionar: cai para a flag
     * de ambiente do env.php. Producao precisa ter WB_FOCUS_NFE_AMBIENTE =
     * 'producao' (ja tem, desde a emissao de NFS-e).
     */
    public static function ehAmbienteDeTeste()
    {
        $host = '';
        if (!empty($_SERVER['HTTP_HOST'])) {
            $host = $_SERVER['HTTP_HOST'];
        } elseif (!empty($_SERVER['SERVER_NAME'])) {
            $host = $_SERVER['SERVER_NAME'];
        }
        $host = strtolower(trim(preg_replace('/:\d+$/', '', $host)));

        if ($host === '') {
            // CLI/cron: sem host, decide a flag de ambiente
            $amb = defined('WB_FOCUS_NFE_AMBIENTE') ? WB_FOCUS_NFE_AMBIENTE : 'homologacao';
            return ($amb != 'producao');
        }

        if ($host == 'localhost' || $host == '127.0.0.1' || $host == '::1') {
            return true;
        }
        foreach (array('.test', '.local', '.localhost', '.dev') as $sufixo) {
            if (substr($host, -strlen($sufixo)) === $sufixo) {
                return true;
            }
        }
        return false;
    }

    /**
     * Resolve o destinatario em ambiente que NAO e de producao.
     *
     * Existe porque testar um envio nao pode significar escrever para uma
     * pessoa real: enderecos que vem do banco sao de gente de verdade, e
     * e-mail enviado nao se recolhe.
     *
     * Fica aqui, no canal, para a regra ser UMA so — cada chamador repetindo
     * o proprio "if ambiente" e como a protecao se perde no lugar esquecido.
     * NAO e aplicada automaticamente em enviarEmail(): quem quer a protecao
     * chama. Ligar no canal inteiro pararia todo o e-mail do sistema se a
     * deteccao de ambiente falhasse.
     *
     * Ambiente vem de WB_FOCUS_NFE_AMBIENTE (unica flag de ambiente que o
     * sistema tem hoje). Havendo uma flag geral no futuro, troca-se aqui.
     *
     * @return array|null array('email' => para onde mandar, 'desviado' => bool)
     *                    ou NULL quando o envio deve ser bloqueado
     */
    public static function destinoParaAmbienteDeTeste($emailReal)
    {
        $ambiente = defined('WB_FOCUS_NFE_AMBIENTE') ? WB_FOCUS_NFE_AMBIENTE : 'homologacao';
        if ($ambiente == 'producao') {
            return array('email' => $emailReal, 'desviado' => false);
        }

        $emailTeste = defined('WB_EMAIL_TESTE') ? trim(WB_EMAIL_TESTE) : '';
        if ($emailTeste == '') {
            return null; // sem endereco de teste definido: nao sai nada
        }
        return array('email' => $emailTeste, 'desviado' => true);
    }

    /**
     * Grava um e-mail em tab_email_fila (status PENDENTE) para o worker de
     * cron enviar de verdade depois (GerenciarCronAC::processarFilaEmail()).
     * Fica aqui, unico ponto de gravacao, para enviarConfirmacaoPagamento()
     * e enviarAvisoVencimentoProximo() nao duplicarem a mesma logica — e
     * para uma fila futura (ex.: trigger de banco pegando INSERT feito por
     * fora do sistema) cair no MESMO consumidor, sem processo novo.
     *
     * NAO checa ambiente de teste aqui — gravar na fila e so uma escrita
     * local, sem risco de vazar e-mail. A trava de verdade e a de sempre,
     * dentro de GerenciarEmailAC::enviarEmail(), no momento do envio real
     * (processarFilaEmail()).
     */
    private static function enfileirar($codEvento, $codInscrito, $emailDestinatario, $nomeDestinatario, $assunto, $corpo, $debug = false) {
        try {
            $objFila = new EmailFila();
            $objFila->setCodEvento($codEvento);
            $objFila->setCodInscrito($codInscrito);
            $objFila->setEmailDestinatario($emailDestinatario);
            $objFila->setNomeDestinatario($nomeDestinatario);
            $objFila->setAssunto($assunto);
            $objFila->setCorpo($corpo);
            $objFila->setStatus('PENDENTE');
            $objFila->setTentativas(0);
            $objFila->setDatProgramado(date('Y-m-d H:i:s'));
            $objFila->salvar($debug);
            return true;
        } catch (OKException $e) {
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Aviso automatico de pagamento CONFIRMADO ao inscrito. Chamado logo
     * apos o INSERT em tab_pagamentos ter sucesso (camada OO em
     * Pagamento::salvar()/salvarCargaPagamento()/salvarCargaPgSisPrime(), e
     * o cadastro manual em inscritos-pagamento-cadastro-incluir-grava.php).
     * Nao envia na hora — so ENFILEIRA em tab_email_fila; quem manda de
     * verdade e GerenciarCronAC::processarFilaEmail() (proxima execucao do
     * cron).
     *
     * So dispara se o evento tiver aviso_pagamento_confirmado_habilitado=1
     * (tab_eventos_configuracao) e o pagamento nao tiver nascido ja
     * cancelado/devolvido/estornado. Nunca lanca excecao para fora — o
     * aviso nao pode quebrar a gravacao do pagamento.
     */
    public static function enviarConfirmacaoPagamento($objPagamento, $debug = false)
    {
        try {
            $config = EventoConfiguracao::getInstancePopulado($objPagamento->getCodEvento());
            if ($config->getAvisoPagamentoConfirmadoHabilitado() != 1) {
                return;
            }

            $status = trim((string)$objPagamento->getStatus());
            if (in_array($status, array('CANCELADO', 'DEVOLVIDO', 'ESTORNADO'))) {
                return;
            }

            $objInscrito = new Inscrito();
            $objInscrito->setCodEvento($objPagamento->getCodEvento());
            $objInscrito->setCodInscrito($objPagamento->getCodInscrito());
            $objInscrito->getInscritoById($debug);
            $emailInscrito = trim((string)$objInscrito->getEMail());
            if ($emailInscrito == '') {
                return;
            }

            $objEv = new InscEvento();
            $objEv->setCodEvento($objPagamento->getCodEvento());
            $objEv->getEventoById($debug);
            $urlLink = 'https://' . FWA_Dominio() . '/' . strtolower($objEv->getGestor())
                     . '/' . $objEv->getCodEvento() . '/inscricoes/' . $objInscrito->getQrCode();

            $assunto = ABREV_EVENTO . ' - Pagamento confirmado';
            $corpo  = "Olá " . $objInscrito->getNomeApelido() . ",<br><br>";
            $corpo .= "Confirmamos o recebimento do seu pagamento de R$ " . formatMoneyView($objPagamento->getValor())
                    . " (cód. " . $objPagamento->getCodPagamento() . "), referente à sua inscrição no "
                    . ABREV_EVENTO . ".<br><br>";
            $corpo .= "Forma de pagamento: " . $objPagamento->getFormaPagamento() . "<br><br>";
            $corpo .= "<a href='" . $urlLink . "'>Acessar área de inscrição</a><br><br>";
            $corpo .= "Dúvidas: " . EMAIL_CONTATO . "<br>";

            self::enfileirar($objPagamento->getCodEvento(), $objPagamento->getCodInscrito(),
                $emailInscrito, $objInscrito->getNomeApelido(), $assunto, $corpo, $debug);
        } catch (Exception $ignore) {
            // aviso de confirmacao nunca deve travar a gravacao do pagamento
        }
    }

    /**
     * Aviso de VENCIMENTO PROXIMO ao inscrito, para um pedido pendente.
     * Chamado por GerenciarCronAC::executarAvisoVencimento() (5 dias e 1 dia
     * antes do vencimento) — nunca pelo fluxo web. Nao envia na hora — so
     * ENFILEIRA em tab_email_fila; quem manda de verdade e
     * GerenciarCronAC::processarFilaEmail(). So dispara se o evento tiver
     * aviso_vencimento_habilitado=1; quem controla nao repetir o mesmo aviso
     * e a tabela tab_pedidos_aviso_vencimento_enviado
     * (PedidoDAO::buscarPedidosVencendoEm() ja filtra por ela).
     *
     * IMPORTANTE: NAO usa as constantes ABREV_EVENTO/EMAIL_CONTATO (definidas
     * uma vez por processo, por evento, em config-N.php) — este metodo e
     * chamado para VARIOS eventos dentro do MESMO processo PHP (o cron
     * processa todos os eventos elegiveis num loop so, sem reiniciar o
     * processo a cada evento). Usa os dados do proprio evento carregado via
     * Evento (getAbrevEvento()/getEmailContato() — colunas de tab_eventos,
     * populadas pelo getter magico de EventoVO extends PadraoVO).
     *
     * @return bool true SOMENTE se o aviso foi enfileirado com sucesso — o
     *         chamador so deve marcar como enviado (evitar reenfileirar)
     *         quando true. false por qualquer motivo (flag desligada, sem
     *         e-mail, falha ao gravar na fila) significa que o aviso
     *         continua pendente e pode ser tentado de novo depois.
     */
    public static function enviarAvisoVencimentoProximo($objPedido, $dias, $debug = false)
    {
        try {
            $objEv = new Evento();
            $objEv->setCodEvento($objPedido->getEventoId());
            $objEv->getEventoById($debug);

            $config = new EventoConfiguracao($objPedido->getEventoId());
            $config->getEventoConfiguracaoById($debug);
            if ($config->getAvisoVencimentoHabilitado() != 1) {
                return false;
            }

            $objInscrito = new Inscrito();
            $objInscrito->setCodEvento($objPedido->getEventoId());
            $objInscrito->setCodInscrito($objPedido->getInscritoId());
            $objInscrito->getInscritoById($debug);
            $emailInscrito = trim((string)$objInscrito->getEMail());
            if ($emailInscrito == '') {
                return false;
            }

            // Eventos antigos costumam nao ter abrev_evento/email_contato preenchidos
            // em tab_eventos (esses dados vivem so no config-N.php hardcoded, que nao
            // da pra usar aqui — ver nota da IMPORTANTE acima). Cadeia de fallback ate
            // chegar em algo utilizavel.
            $abrevEvento = $objEv->getAbrevEvento();
            if ($abrevEvento == '') $abrevEvento = $objEv->getNomeOrganizador();
            if ($abrevEvento == '') $abrevEvento = $objEv->getTitulo();
            if ($abrevEvento == '') $abrevEvento = 'INESCO';

            $emailContato = $objEv->getEmailContato();
            if ($emailContato == '') $emailContato = $objEv->getEmailOrganizador();
            if ($emailContato == '') $emailContato = 'eventos@opctec.com.br';

            $urlLink = 'https://' . FWA_Dominio() . '/' . strtolower($objEv->getGestor())
                     . '/' . $objEv->getCodEvento() . '/inscricoes/' . $objInscrito->getQrCode();

            $dias = (int)$dias;
            $assunto = $abrevEvento . ' - Boleto vence em ' . $dias . ($dias == 1 ? ' dia' : ' dias');
            $corpo  = "Olá " . $objInscrito->getNomeApelido() . ",<br><br>";
            $corpo .= "Seu pagamento referente à inscrição no " . $abrevEvento . " vence em " . $dias
                    . ($dias == 1 ? ' dia' : ' dias') . " (" . date('d/m/Y', strtotime($objPedido->getDatVencimento())) . ").<br><br>";
            $corpo .= "Valor: R$ " . formatMoneyView($objPedido->getValorTotal()) . "<br><br>";
            $corpo .= "Para evitar atraso, efetue o pagamento antes do vencimento.<br>";
            $corpo .= "<a href='" . $urlLink . "'>Acessar meus boletos</a><br><br>";
            $corpo .= "Se você já efetuou o pagamento, desconsidere esta mensagem.<br>";
            $corpo .= "Dúvidas: " . $emailContato . "<br>";

            return self::enfileirar($objPedido->getEventoId(), $objPedido->getInscritoId(),
                $emailInscrito, $objInscrito->getNomeApelido(), $assunto, $corpo, $debug);
        } catch (Exception $ignore) {
            // aviso de vencimento nunca deve travar a tarefa de cron
            return false;
        }
    }

    /**
     * Envio COM ANEXO pelo canal centralizado.
     *
     * O enviarEmailSendGridComAnexo() so funciona no SendGrid, que esta
     * desativado — por isso este roteia igual ao enviarEmail() e implementa o
     * anexo tambem no Postmark, que e o provedor em uso.
     *
     * @param array $anexos lista de array('caminho' => arquivo em disco,
     *                      'nome' => nome que o destinatario ve,
     *                      'tipo' => content-type, opcional)
     * @return bool
     * @throws Exception quando o anexo nao existe ou o provedor nao suporta —
     *         quem chama precisa saber que o e-mail NAO foi com a nota, em vez
     *         de mandar um e-mail vazio achando que deu certo.
     */
    public static function enviarEmailComAnexo($msgTo, $msgSubject, $msgBody, array $anexos, $emailBcc = false)
    {
        foreach ($anexos as $anexo) {
            $caminho = isset($anexo['caminho']) ? $anexo['caminho'] : '';
            if ($caminho == '' || !is_file($caminho)) {
                throw new Exception("Anexo nao encontrado: " . basename((string) $caminho));
            }
        }

        // Mesma trava de ambiente do enviarEmail(): nada sai para endereco
        // real fora de producao.
        if (self::ehAmbienteDeTeste()) {
            $emailTeste = defined('WB_EMAIL_TESTE') ? trim(WB_EMAIL_TESTE) : '';
            if ($emailTeste == '') {
                executarLog('BLOQUEADO (ambiente de teste, sem WB_EMAIL_TESTE)'
                    . ' Email to:' . $msgTo . ' Subject:' . $msgSubject . ' [com anexo]',
                    "GerenciarEmailAC", true);
                return false;
            }
            $msgBody    = '[AMBIENTE DE TESTE - destinatario real: ' . $msgTo . ']<br><br>' . $msgBody;
            $msgSubject = '[TESTE] ' . $msgSubject;
            $msgTo      = $emailTeste;
            $emailBcc   = false;
        }

        $provedor = defined('WB_PROVIDER_EMAIL') ? strtolower(WB_PROVIDER_EMAIL) : 'sendgrid';
        if ($provedor === 'postmark' && defined('WB_POSTMARK_TOKEN') && WB_POSTMARK_TOKEN !== '') {
            return GerenciarEmailAC::enviarEmailPostmarkComAnexo($msgTo, $msgSubject, $msgBody, $anexos, $emailBcc);
        }

        $primeiro = reset($anexos);
        return GerenciarEmailAC::enviarEmailSendGridComAnexo($msgTo, $msgSubject, $msgBody, $emailBcc, true, array(
            'arquivo_anexo_pasta' => dirname($primeiro['caminho']) . DIRECTORY_SEPARATOR,
            'arquivo_anexo_nome'  => basename($primeiro['caminho']),
        ));
    }

    /**
     * Postmark aceita anexo em Base64 dentro do proprio JSON. Limite de 10 MB
     * no payload inteiro — PDF de NFS-e fica na casa de 50 KB, folgado.
     *
     * @return string MessageID do Postmark (serve de comprovante do envio)
     * @throws Exception se o Postmark recusar
     */
    public static function enviarEmailPostmarkComAnexo($msgTo, $msgSubject, $msgBody, array $anexos, $emailBcc = false)
    {
        if ($msgTo == '') {
            throw new Exception("E-mail nao informado");
        }
        if (!defined('WB_POSTMARK_TOKEN') || WB_POSTMARK_TOKEN === '') {
            throw new Exception("WB_POSTMARK_TOKEN nao configurado em env.php");
        }

        $fromEmail = defined('WB_POSTMARK_FROM_EMAIL') ? WB_POSTMARK_FROM_EMAIL : 'eventos@opctec.com.br';
        $fromNome  = defined('ABREV_EVENTO_EMAIL') ? ABREV_EVENTO_EMAIL : 'OPCTEC';
        $stream    = defined('WB_POSTMARK_STREAM') ? WB_POSTMARK_STREAM : 'outbound';

        $listaAnexos = array();
        foreach ($anexos as $anexo) {
            $caminho = $anexo['caminho'];
            $conteudo = @file_get_contents($caminho);
            if ($conteudo === false) {
                throw new Exception("Nao foi possivel ler o anexo: " . basename($caminho));
            }
            $listaAnexos[] = array(
                'Name'        => isset($anexo['nome']) && $anexo['nome'] != '' ? $anexo['nome'] : basename($caminho),
                'Content'     => base64_encode($conteudo),
                'ContentType' => isset($anexo['tipo']) && $anexo['tipo'] != '' ? $anexo['tipo'] : 'application/octet-stream',
            );
        }

        $payload = array(
            'From'          => $fromNome . ' <' . $fromEmail . '>',
            'To'            => $msgTo,
            'Subject'       => $msgSubject,
            'HtmlBody'      => $msgBody,
            'TextBody'      => strip_tags(str_replace(array('<br>', '<br/>', '<br />'), "
", $msgBody)),
            'MessageStream' => $stream,
            'TrackOpens'    => true,
            'Attachments'   => $listaAnexos,
        );
        if ($emailBcc !== false && $emailBcc !== '') {
            $payload['Bcc'] = $emailBcc;
        }

        $ch = curl_init('https://api.postmarkapp.com/email');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Accept: application/json',
            'Content-Type: application/json',
            'X-Postmark-Server-Token: ' . WB_POSTMARK_TOKEN,
        ));
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        $resposta = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($resposta === false) {
            executarLog('ERRO Postmark anexo (cURL): ' . $curlErr . " Email to:" . $msgTo, "GerenciarEmailAC", true);
            throw new Exception("Falha ao contatar o servidor de e-mail: " . $curlErr);
        }

        $json = json_decode($resposta, true);
        if ($httpCode !== 200 || !is_array($json) || !isset($json['ErrorCode']) || $json['ErrorCode'] !== 0) {
            $erroMsg = isset($json['Message']) ? $json['Message'] : ('HTTP ' . $httpCode . ' - ' . $resposta);
            executarLog('ERRO Postmark anexo: ' . $erroMsg . " Email to:" . $msgTo, "GerenciarEmailAC", true);
            throw new Exception("O servidor de e-mail recusou o envio: " . $erroMsg);
        }

        $messageId = isset($json['MessageID']) ? $json['MessageID'] : '';
        executarLog($messageId . '-' . $msgSubject . " Email to:" . $msgTo . " - [postmark/anexo]", "GerenciarEmailAC", true);
        return $messageId;
    }

    public static function enviarEmailSendGrid($msgTo, $msgSubject, $msgBody, $emailBcc=false, $msgFormatoHtml = false)
    {
        // if ($_SERVER['REMOTE_ADDR']=="177.82.0.28"){
        $urlErro = false;
        $urlOk = false;
        $msgLogBody = '';
        try {
            if ($msgTo == '') {
                throw new Exception("E-mail não informado");
            }

            $email = new SendGrid\Mail\Mail();
            $sgToken = defined('WB_SENDGRID_TOKEN') ? WB_SENDGRID_TOKEN : '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';
            $sendgrid = new \SendGrid($sgToken);

            $email->setFrom("eventos@opctec.com.br", ABREV_EVENTO_EMAIL);
            $email->setSubject($msgSubject);
            $email->addTo($msgTo, $msgTo);
            if($emailBcc!=''){
                $email->addBcc($emailBcc, $emailBcc);            
            }
            // if($emailReplayTo!=''){
            //     $email->setReplyTo($emailReplayTo, $nomeReplayTo);          
            // }
            $email->addContent("text/plain", $msgBody);
            $email->addContent(
                "text/html", nl2br($msgBody)
            );

            $response = $sendgrid->send($email);

            $headers = $response->headers();
            $email_id = $headers[5];
            $txtLog = $email_id.'-'.$msgSubject." Email to:".$msgTo." - ";
            executarLog($txtLog,"GerenciarEmailAC",true);       

            If (@!$urlOk) { # se não estiver difinido retorna valor lógico, verdadeiro.
                return true;
            } Else {
                header("Location: $urlOk");    # se estiver difinido retorna a pagina de ok.
            }                
        } catch (Exception $e) {
            // echo 'Capturado exceção: '. $e->getMessage() ."\n";
            // se a mensagem foi enviada, em caso de erro entra.
            If (@!$urlErro) {         # se não estiver difinido retorna valor lógico, falso.
                return false;
            } Else {
                header("Location: $urlErro");  # se estiver difinido retorna a pagina de erro.
            }                
        }       
        exit();        
        // }

    }

    public static function enviarEmailSendGridComAnexo($msgTo, $msgSubject, $msgBody, $emailBcc=false, $msgFormatoHtml = false, $fws_ = false)
   {
        // if ($_SERVER['REMOTE_ADDR']=="177.82.0.28"){
        $urlErro = false;
        $urlOk = false;
        $msgLogBody = '';
        try {
            if ($msgTo == '') {
                throw new Exception("E-mail não informado");
            }

            $email = new SendGrid\Mail\Mail();
            $sgToken = defined('WB_SENDGRID_TOKEN') ? WB_SENDGRID_TOKEN : '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';
            $sendgrid = new \SendGrid($sgToken);

            $email->setFrom("eventos@opctec.com.br", ABREV_EVENTO_EMAIL);
            $email->setSubject($msgSubject);
            $email->addTo($msgTo, $msgTo);
            if($emailBcc!=''){
                $email->addBcc($emailBcc, $emailBcc);            
            }
            // if($emailReplayTo!=''){
            //     $email->setReplyTo($emailReplayTo, $nomeReplayTo);          
            // }
            $email->addContent("text/plain", $msgBody);
            $email->addContent(
                "text/html", nl2br($msgBody)
            );
            $email = new SendGrid\Mail\Mail();
            $sgToken = defined('WB_SENDGRID_TOKEN') ? WB_SENDGRID_TOKEN : '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';
            $sendgrid = new \SendGrid($sgToken);
            
            $email->setFrom("eventos@opctec.com.br", ABREV_EVENTO_EMAIL);
            $email->setSubject($msgSubject);
            $email->addTo($msgTo, $msgTo);
            if($emailBcc != '') {
                $email->addBcc($emailBcc, $emailBcc);            
            }
            
            $email->addContent("text/plain", $msgBody);
            $email->addContent("text/html", nl2br($msgBody));
            
            // Caminho do arquivo a ser anexado
            if($fws_['arquivo_anexo_nome']){
                $filePath = $fws_['arquivo_anexo_pasta']; 
                $fileName = $fws_['arquivo_anexo_nome'];
            
                // Lendo o arquivo e convertendo para base64
                $fileContent = base64_encode(file_get_contents($filePath));
                
                // Adicionando o anexo
                $email->addAttachment(
                    $fileContent,
                    "application/pdf",  // Tipo MIME (mude conforme necessário)
                    $fileName,
                    "attachment"
                );
            }
            
            $response = $sendgrid->send($email);
            
            $response = $sendgrid->send($email);

            $headers = $response->headers();
            $email_id = $headers[5];
            $txtLog = $email_id.'-'.$msgSubject." Email to:".$msgTo." - ";
            executarLog($txtLog,"GerenciarEmailAC",true);       

            If (@!$urlOk) { # se não estiver difinido retorna valor lógico, verdadeiro.
                return true;
            } Else {
                header("Location: $urlOk");    # se estiver difinido retorna a pagina de ok.
            }                
        } catch (Exception $e) {
            // echo 'Capturado exceção: '. $e->getMessage() ."\n";
            // se a mensagem foi enviada, em caso de erro entra.
            If (@!$urlErro) {         # se não estiver difinido retorna valor lógico, falso.
                return false;
            } Else {
                header("Location: $urlErro");  # se estiver difinido retorna a pagina de erro.
            }                
        }       
        exit();
        // }

    }

    /**
     * Envio via Postmark (https://postmarkapp.com) usando cURL nativo — sem dependencia Composer.
     * Mesma assinatura/retorno que enviarEmailSendGrid para ser drop-in.
     * Token e remetente vem de env.php: WB_POSTMARK_TOKEN, WB_POSTMARK_FROM_EMAIL, WB_POSTMARK_STREAM.
     */
    public static function enviarEmailPostmark($msgTo, $msgSubject, $msgBody, $emailBcc = false, $msgFormatoHtml = false)
    {
        $urlErro = false;
        $urlOk = false;
        try {
            if ($msgTo == '') {
                throw new Exception("E-mail nao informado");
            }
            if (!defined('WB_POSTMARK_TOKEN') || WB_POSTMARK_TOKEN === '') {
                throw new Exception("WB_POSTMARK_TOKEN nao configurado em env.php");
            }

            $fromEmail = defined('WB_POSTMARK_FROM_EMAIL') ? WB_POSTMARK_FROM_EMAIL : 'eventos@opctec.com.br';
            $fromNome  = defined('ABREV_EVENTO_EMAIL') ? ABREV_EVENTO_EMAIL : 'OPCTEC';
            $stream    = defined('WB_POSTMARK_STREAM') ? WB_POSTMARK_STREAM : 'outbound';

            $payload = array(
                'From'          => $fromNome . ' <' . $fromEmail . '>',
                'To'            => $msgTo,
                'Subject'       => $msgSubject,
                'TextBody'      => $msgBody,
                'HtmlBody'      => nl2br($msgBody),
                'MessageStream' => $stream,
                'TrackOpens'    => true,
            );
            if ($emailBcc !== false && $emailBcc !== '') {
                $payload['Bcc'] = $emailBcc;
            }

            $ch = curl_init('https://api.postmarkapp.com/email');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Accept: application/json',
                'Content-Type: application/json',
                'X-Postmark-Server-Token: ' . WB_POSTMARK_TOKEN,
            ));
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            $resposta = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlErr  = curl_error($ch);
            curl_close($ch);

            if ($resposta === false) {
                throw new Exception("Falha cURL Postmark: " . $curlErr);
            }
            $json = json_decode($resposta, true);
            if ($httpCode !== 200 || !is_array($json) || !isset($json['ErrorCode']) || $json['ErrorCode'] !== 0) {
                $erroMsg = isset($json['Message']) ? $json['Message'] : ('HTTP ' . $httpCode . ' - ' . $resposta);
                throw new Exception("Postmark recusou: " . $erroMsg);
            }

            $messageId = isset($json['MessageID']) ? $json['MessageID'] : '';
            $txtLog = $messageId . '-' . $msgSubject . " Email to:" . $msgTo . " - [postmark]";
            executarLog($txtLog, "GerenciarEmailAC", true);

            if (@!$urlOk) {
                return true;
            } else {
                header("Location: $urlOk");
            }
        } catch (Exception $e) {
            $txtLog = 'ERRO Postmark: ' . $e->getMessage() . " Email to:" . $msgTo . " Subject:" . $msgSubject;
            executarLog($txtLog, "GerenciarEmailAC", true);
            if (@!$urlErro) {
                return false;
            } else {
                header("Location: $urlErro");
            }
        }
        exit();
    }

    /**@todo fazer teste com envio de email via aws */
    public static function enviarEmailAwsSes($msgTo, $msgSubject, $msgBody, $emailBcc = false, $msgFormatoHtml = false, $usarIpDedicado = false){
        $urlErro = false;
        $urlOk = false;

        // require_once 'vendor/autoload.php';
        // use Aws\Ses\SesClient;
        // use Aws\Exception\AwsException;

        try {
            if ($msgTo == '') {
                throw new Exception("E-mail não informado");
            }

            // CONFIGURAÇÕES
            $awsKey = 'SUA_AWS_KEY';
            $awsSecret = 'SUA_AWS_SECRET';
            $region = 'us-east-1';
            $sourceEmail = 'xxx@xxxxx.com.br'; // remetente
            $configurationSet = 'meu_config_set'; // opcional

            $client = new SesClient([
                'version' => '2010-12-01',
                'region' => $region,
                'credentials' => [
                    'key' => $awsKey,
                    'secret' => $awsSecret,
                ],
            ]);

            $params = [
                'Destination' => [
                    'ToAddresses' => [$msgTo],
                    'BccAddresses' => $emailBcc ? [$emailBcc] : [],
                ],
                'ReplyToAddresses' => [$sourceEmail],
                'Source' => $sourceEmail,
                'Message' => [
                    'Body' => [],
                    'Subject' => [
                        'Charset' => 'UTF-8',
                        'Data' => $msgSubject,
                    ],
                ],
            ];

            if ($msgFormatoHtml) {
                $params['Message']['Body']['Html'] = [
                    'Charset' => 'UTF-8',
                    'Data' => nl2br($msgBody),
                ];
            } else {
                $params['Message']['Body']['Text'] = [
                    'Charset' => 'UTF-8',
                    'Data' => $msgBody,
                ];
            }

            if ($usarIpDedicado && $configurationSet) {
                $params['ConfigurationSetName'] = $configurationSet;
            }

            $result = $client->sendEmail($params);
            $email_id = $result['MessageId'];

            $txtLog = $email_id . ' - ' . $msgSubject . " Email to:" . $msgTo . " - ";
            executarLog($txtLog, "GerenciarEmailAC", true);

            if (@!$urlOk) {
                return true;
            } else {
                header("Location: $urlOk");
            }

        } catch (Exception $e) {
            if (@!$urlErro) {
                return false;
            } else {
                header("Location: $urlErro");
            }
        }

        exit();
    }


    public static function enviarEmailSMTP($msgTo, $msgSubject, $msgBody, $msgFormatoHtml = false){
        //        $smtpServer = "br800.hostgator.com.br";
        //        $debug = false;
        //        if($_SERVER['REMOTE_ADDR']=='189.14.38.219'){
        //            $debug = 1;
        ////             $smtpServer = "opctec.com.br";
        ////             error_reporting(E_ALL ^ E_NOTICE);
        ////             ini_set("display_errors", "On");
        //        }
        //        if($_SERVER["SERVER_NAME"]!="opctec.com.br"){
        //            $msgSubject = "[dese]".$msgSubject;
        //            $msgTo = 'zakacc@gmail.com';
        //        }
        //
        //        $msgLogBody="\nUsuário Logado:
        //        Chapa Logado: ".$_SESSION['_cod_usuario']."
        //        Nome Logado: ".$_SESSION['_nome_usuario']."
        //        Browser: ".getHttpUserAgent()."
        //        IP:".getIp()."\nMensagem de Log:\n ".$msgErro."\n\n";
        //        
        //        $msgLogSubject="[prod][".$_SERVER["SERVER_NAME"]."]".get_class($this);
        //        if(ENVIAR_EMAIL==false){
        //            echo "Envio de E-mail bloqueado";
        //            return false;
        //        }
                
        if ($msgTo == '') {
            echo "E-mail não informado";
            return false;
        }

        //        $txtLog = $msgSubject."\n".$msgLogBody."\nEmail to:".$msgTo."<-\n".$msgBody."\n";
        //executarLog($txtLog,"GerenciarEmailAC",true);    
        date_default_timezone_set('America/Sao_Paulo');
        #---------------------------------------------------------------------
        //        require_once(dirname(__FILE__)."/../../lib/PHPMailer_5.2.4/class.phpmailer.php");
        require_once(dirname(__FILE__)."/../lib/vendor/phpmailer/phpmailer/src/Exception.php");
        require_once(dirname(__FILE__)."/../lib/vendor/phpmailer/phpmailer/src/PHPMailer.php");
        require_once(dirname(__FILE__)."/../lib/vendor/phpmailer/phpmailer/src/SMTP.php");
        //         // Carrega o autoloader do Composer 
        //        require dirname(__FILE__).'/../lib/vendor/autoload.php'; 
        //        if($_SERVER['REMOTE_ADDR']=='189.14.38.219'){
        //            $mail = new PHPMailer();
        //            $mail->IsSMTP(); // enable SMTP
        //            $mail->SMTPDebug = 1; // debugging: 1 = errors and messages, 2 = messages only
        //            $mail->SMTPAuth = true; // authentication enabled
        //            $mail->SMTPSecure = 'ssl'; // secure transfer enabled REQUIRED for Gmail
        //            $mail->Host = "br800.hostgator.com.br";
        //            $mail->Port = 465; // or 587
        //            $mail->IsHTML(true);
        //            $mail->Username = "eventos@opctec.com.br";
        //            $mail->Password = "Ss&C)0Y4KiSx";
        //            $mail->SetFrom("eventos@opctec.com.br");
        //            $mail->Subject = "Assunto da mensagem";
        //            $mail->Body = "Escreva o texto do email aqui";
        //            $mail->AddAddress("zakacc@gmail.com");
        //               if(!$mail->Send()) {
        //                  echo "Mailer Error: " . $mail->ErrorInfo;
        //               } else {
        //                  echo "Mensagem enviada com sucesso";
        //               }
        //               exit();
        //        }
        #---------------------------------------------------------------------
        #Instanciando objeto
        #---------------------------------------------------------------------
        $mail = new PHPMailer();
        $mail->IsSMTP();                     # send via SMTP # NÃo Alterar
        $mail->isHTML($msgFormatoHtml);  // Seta o formato do e-mail para aceitar 
        //        $mail->SMTPDebug = $debug; // debugging: 1 = errors and messages, 2 = messages only
        $mail->CharSet = 'UTF-8';
        $mail->Host = "smtp.titan.email";         # SMTP servers  # NÃo Alterar
        $mail->SMTPSecure = "ssl"; 
        $mail->Port = "465";
        $mail->SMTPAuth = true;              # turn on SMTP authentication
        $mail->Username = "eventos@opctec.com.br"; # SMTP username - login do remetente
        // $mail->Password = "Ss&C)0Y4KiSx";        # SMTP password - senha do remetente
        $mail->Password = "8uLD#mb2FpA";        # SMTP password - senha do remetente
        #Alguns servidores filtram os email q nÃo tenham remetente
        $mail->From = "eventos@opctec.com.br";
        $mail->FromName = ABREV_EVENTO_EMAIL;
        $mail->AddAddress($msgTo, $msgTo);   #email e nome do destinatario
        // $mail->AddBCC("contato@opctec.com.br"); //there may be foreach loop
        if(EMAIL_BCC!=""){
            $mail->AddBCC(EMAIL_BCC); //there may be foreach loop
        }
        if(EMAIL_REPLAY_TO!=''){
            $mail->AddReplyTo(EMAIL_REPLAY_TO, ABREV_EVENTO_EMAIL);                                        # define largura do texto
        }
        $mail->Subject = $msgSubject;     # Subject
        $mail->Body = $msgBody;       # corpo do email
        #---------------------------------------------------------------------
        #Enviando Email
        #---------------------------------------------------------------------
        If (!$mail->Send()) {               # envia e testa se a mensagem foi enviada, em caso de erro entra.
            If (@!$urlErro) {         # se não estiver difinido retorna valor lógico, falso.
                return false;
            } Else {
            header("Location: $urlErro");  # se estiver difinido retorna a pagina de erro.
            }
        } Else {
            If (@!$urlOk) { # se não estiver difinido retorna valor lógico, verdadeiro.
            return true;
            } Else {
            header("Location: $urlOk");    # se estiver difinido retorna a pagina de ok.
            }
        }
    }
}

<?php

class GerenciarEmailAvaliacaoAC {
    
    
    public static function comunicadoAprovacaoNovo($objL = false, $msg = false, $debug = false) {
        echo "agendando envio de mensagem";        
        /*agendarEnvio*/
        $objComunicadoEnvio = new ComunicadoEnvio();
        $objComunicadoEnvio->setForma('EMAIL');
        $objComunicadoEnvio->setTipo('COMUNICADO-APROVACAO');
        $objComunicadoEnvio->agendarEnvioComunicadoAprovado($objL);
        
//        $enviou = GerenciarEmailAC::enviarEmail($msgTo, $msgSubject, $msgBody);
    }
    public static function comunicadoAprovacao($objL = false, $msg = false, $debug = false){
        $msgTo = '';
        $msgSubject = '';
        $msgBody = '';
        /** Monta o corpo da mensagem */
        $msgBody = "";
        $msgBody .= "Ola " . maiusculo($objL->getNomeInscrito())."\r";
//        $msgBody .= "Agradecemos pela submissão do seu trabalho para o \r".DESC_EVENTO.".\r";
        $msgBody .= "Estamos honrados em anunciar que seu trabalho intitulado: \r";
        $msgBody .= maiusculo($objL->getTituloTrabalho())."\r";
        $msgBody .= "foi considerado APROVADO pela Comissão Científica da ".DESC_EVENTO." \r";
        $msgBody .= "Horário e local de apresentação serão divulgados no site oficial do evento e em sua área de inscrito.\r";
        if(($objL->getTotalPagamentos()=='0') and ($objL->getCodVoucher()=='') and ($objL->getCodIsento()=='')){
//        $msgBody .= "Não perca tempo, gera seu boleto até o dia 10/06/2019 e garanta seu desconto, ou caso possua voucher, utilize o seu até o dia 10/06/2019.\r";
        }
        $msgBody .= "\r";
        $msgBody .= "att. ".EMAIL_CONTATO_ATT."\r\n";
        $msgBody .= "Dúvidas: ".EMAIL_CONTATO."\r\n";
        $msgBody .= "";

        /** Atribui o assunto */
        $msgSubject = ABREV_EVENTO_EMAIL." - Comunicado de Resultado de Avaliação";
        if ($debug) {
            echo $msgBody . "<hr>";
        }

        /** Envia o e-mail */
        $msgTo = $objL->getEMail();
//        $msgTo = "zakacc@gmail.com";

        try {
            $objComunicadoEnvio = new ComunicadoEnvio();
            $objComunicadoEnvio->setTipo('COMUNICADO APROVACAO TRABALHO');
            $objComunicadoEnvio->setDestinatarioEmail($msgTo);
            $objComunicadoEnvio->setCodDestinatario($objL->getCodInscrito());
            $objComunicadoEnvio->setCodTrabalho($objL->getCodTrabalho());
            $objComunicadoEnvio->setEnvioSituacao('ENVIADO');
            $objComunicadoEnvio->setEnvioData(Date('Y-m-d H:i:s'));
            $objComunicadoEnvio->setEnvioForma('EMAIL');

            if(!$objComunicadoEnvio->getEmailEnviado()){
                $enviou = GerenciarEmailAC::enviarEmail($msgTo, $msgSubject, $msgBody);
//                $enviou = true;
                $msg = "";
                $enviou? $objComunicadoEnvio->salvar() : "";
            }else{
                $msg = "comunicado já cadastrado<br>";
            }
        } catch (Exception $exc) { } catch (OKException $exc) { }            
            
        if (!$enviou) {
            if ($retorno == 'BOOLEAN') { return false; } else { echo $msg."<font color='red'><b>E-mail não enviado</b></font><br>"; }
        }else{
            if ($retorno == 'BOOLEAN'){ return true; }else{ echo "E-mail enviado<br>"; }
        }
    }
    public static function comunicadoReprovacao($objL = false, $msg = false, $debug = false){
        $msgTo = '';
        $msgSubject = '';
        $msgBody = '';
        /** Monta o corpo da mensagem */
        $msgBody = "";
        $msgBody .= "Ola " . $objL->getNomeInscrito()."\r";
        $msgBody .= "Agradecemos pela submissão do seu trabalho para o \r".DESC_EVENTO.".\r";
        $msgBody .= "Sentimos muito em anunciar que seu trabalho intitulado:\r";
        $msgBody .= $objL->getTituloTrabalho()."\r";
        $msgBody .= "foi considerado NÃO APROVADO pela Comissão Científica.\r";
        $msgBody .= "\r";
        $msgBody .= "att. ".EMAIL_CONTATO_ATT."\r\n";
        $msgBody .= "Dúvidas: ".EMAIL_CONTATO."\r\n";
        $msgBody .= "";

        /** Atribui o assunto */
        $msgSubject = ABREV_EVENTO_EMAIL." - Comunicado de Resultado de Avaliação";
        if ($debug) {
            echo $msgBody . "<hr>";
        }

        /** Envia o e-mail */
        $msgTo = $objL->getEMail();
//        $msgTo = "zakacc@gmail.com";

        try {
            $objComunicadoEnvio = new ComunicadoEnvio();
            $objComunicadoEnvio->setTipo('COMUNICADO REPROVACAO TRABALHO');
            $objComunicadoEnvio->setDestinatarioEmail($msgTo);
            $objComunicadoEnvio->setCodDestinatario($objL->getCodInscrito());
            $objComunicadoEnvio->setCodTrabalho($objL->getCodTrabalho());
            $objComunicadoEnvio->setEnvioSituacao('ENVIADO');
            $objComunicadoEnvio->setEnvioData(Date('Y-m-d H:i:s'));
            $objComunicadoEnvio->setEnvioForma('EMAIL');

            if(!$objComunicadoEnvio->getEmailEnviado()){
                $enviou = GerenciarEmailAC::enviarEmail($msgTo, $msgSubject, $msgBody);
                $msg = "";
                $enviou? $objComunicadoEnvio->salvar() : "";
            }else{
                $msg = "comunicado já cadastrado<br>";
            }
        } catch (Exception $exc) { } catch (OKException $exc) { }            
            
        if (!$enviou) {
            if ($retorno == 'BOOLEAN') { return false; } else { echo $msg."<font color='red'><b>E-mail não enviado</b></font><br>"; }
        }else{
            if ($retorno == 'BOOLEAN'){ return true; }else{ echo "E-mail enviado<br>"; }
        }
    }
    /**
     * 
     * @param Trabalho $obj
     * @param type $retorno BOOLEAN, MSG
     * @param type $debug
     */
    public static function comunicadoIndicacaoAvaliador($objTrabalho,$objTrabalhoHistorico,$objInscrito,$objAvaliador, $retorno = 'BOOLEAN', $debug = false) {
        try {
            if($objAvaliador->getLogin()!=''){
                $login = $objAvaliador->getLogin();
                $senha = $objAvaliador->getSenha();
            }else{
                $objAvaliador->carregarObjLogin();
                $objLogin = $objAvaliador->getObjLogin();
                $login = $objLogin->getLogin();
                $senha = $objLogin->getSenha();
            }    
            if($login==''){
                throw new Exception('Comunicado não pode ser enviado, entrar em contato com adminsitrador');
            }

            $msgTo = '';
            $msgSubject = '';
            $msgBody = '';
            /** Monta o corpo da mensagem */
            $msgBody = "";
            $msgBody .= "Ola: " . $objAvaliador->getNomeConvidado()."\r";
            $msgBody .= "Informamos que o sistema de avaliação de resumos do \r".DESC_EVENTO." já está disponível.\r";
            $msgBody .= "\r";
            $msgBody .= "Solicitamos que primeiramente faça login no sistema.\r";
            $msgBody .= EMAIL_CONTATO_LINK_AVALIACAO."\r";
            $msgBody .= "\r";
            $msgBody .= "Utilize os dados abaixo:\r";
            $msgBody .= "Login: ".$login."\r";
            $msgBody .= "Senha: ".$senha."\r";
            $msgBody .= "\r";
            $msgBody .= "Em seguida, proceda à avaliação do trabalho abaixo, utilizando as janelas específicas com critérios para cada tipo de resumo, que facilitarão seu julgamento.\r";
            $msgBody .= "\rTitulo do Trabalho:\r";
            $msgBody .= "(".maiusculo($objTrabalho->getTituloTrabalho())."\r";
            $msgBody .= "\r";
            $msgBody .= "É importante observar atentamente o prazo para a avaliação.\r";
            $msgBody .= "\r";
            $msgBody .= "att. ".EMAIL_CONTATO_ATT."\r\n";
            $msgBody .= "Dúvidas: ".EMAIL_CONTATO."\r\n";
            $msgBody .= "";

            /** Atribui o assunto */
            $msgSubject = ABREV_EVENTO_EMAIL." - Comunicado de Indicacao de Avaliador";
            if ($debug) {
                echo $msgBody . "<hr>";
            }

            /** Envia o e-mail */
            $msgTo = $objAvaliador->getEMail();
    //        $msgTo = "contato@opctec.com.br";
    //        $msgTo = "zakacc@gmail.com.br";
    //        $msgTo = "zaka_cc@hotmail.com";
    //        $enviou = mail($msgTo, $msgSubject, $msgBody, HEADER_EMAIL);
            $enviou = GerenciarEmailAC::enviarEmail($msgTo, $msgSubject, $msgBody);
            //$enviou = false;
            if (!$enviou) {
                if ($retorno == 'BOOLEAN') { return false; } else { echo "<font color='red'><b>E-mail não enviado</b></font><br>"; }
            }else{
                if ($retorno == 'BOOLEAN'){ return true; }else{ echo "E-mail enviado<br>"; }
            }
        } catch (Exception $exc) {
            if ($retorno == 'BOOLEAN') { return false; } else { echo "<font color='red'><b>$exc->getMessage()</b></font><br>"; }
        }
    }
    /**
     * 
     * @param Trabalho $obj
     * @param type $retorno BOOLEAN, MSG
     * @param type $debug
     */
    public static function cobrarAvaliador($objTrabalho,$objTrabalhoHistorico,$objInscrito,$objAvaliador, $retorno = 'BOOLEAN', $debug = false) {
        try{
            if($objAvaliador->getLogin()!=''){
                $login = $objAvaliador->getLogin();
                $senha = $objAvaliador->getSenha();
            }else{
                $objAvaliador->carregarObjLogin();
                $objLogin = $objAvaliador->getObjLogin();
                $login = $objLogin->getLogin();
                $senha = $objLogin->getSenha();
            }    
            if($login==''){
                throw new Exception('Comunicado não pode ser enviado, entrar em contato com adminsitrador');
            }
            
            $msgTo = '';
            $msgSubject = '';
            $msgBody = '';
            /** Monta o corpo da mensagem */
            $msgBody = "";
            $msgBody .= "Ola: " . $objAvaliador->getNomeConvidado()."\r";
            $msgBody .= "Informamos que o sistema de avaliação de resumos do \r".DESC_EVENTO." já está disponível.\r";
            $msgBody .= "\r";
            $msgBody .= "Solicitamos que primeiramente faça login no sistema.\r";
            $msgBody .= EMAIL_CONTATO_LINK_AVALIACAO."\r";
            $msgBody .= "\r";
            $msgBody .= "Utilize os dados abaixo:\r";
            $msgBody .= "Login: ".$login."\r";
            $msgBody .= "Senha: ".$senha."\r";
            $msgBody .= "\r";
            $msgBody .= "Em seguida, proceda à avaliação do trabalho abaixo, utilizando as janelas específicas com critérios para cada tipo de resumo, que facilitarão seu julgamento\r";
            $msgBody .= "\rTitulo do Trabalho:\r";
            $msgBody .= "(".  maiusculo($objTrabalho->getTituloTrabalho()).")\r";
            $msgBody .= "\r";
            $msgBody .= "É importante observar atentamente o prazo para a avaliação.\r";
            $msgBody .= "\r";
            $msgBody .= "att. ".EMAIL_CONTATO_ATT."\r\n";
            $msgBody .= "Dúvidas: ".EMAIL_CONTATO."\r\n";
            $msgBody .= "";

            /** Atribui o assunto */
            $msgSubject = ABREV_EVENTO_EMAIL." - Re-aviso de Indicacao de Avaliador";
            if ($debug) {
                echo $msgBody . "<hr>";
            }

            /** Envia o e-mail */
            $msgTo = $objAvaliador->getEMail();

            // $enviou = GerenciarEmailAC::enviarEmail($msgTo, $msgSubject, $msgBody);
            $enviou = false;
            if (!$enviou) {
                if ($retorno == 'BOOLEAN') { return false; } else { echo "<font color='red'><b>E-mail não enviado</b></font><br>"; }
            }else{
                if ($retorno == 'BOOLEAN'){ return true; }else{ echo "E-mail enviado<br>"; }
            }
        } catch (Exception $exc) {
            if ($retorno == 'BOOLEAN') { return false; } else { echo "<font color='red'><b>$exc->getMessage()</b></font><br>"; }
        }
    }
    /**
     *
     * @param Trabalho $obj
     * @param type $retorno BOOLEAN, MSG
     * @param type $debug
     */
    public static function comunicadoTrocaAvaliador($objTrabalho,$objTrabalhoHistorico,$objInscrito,$objAvaliador, $retorno = 'BOOLEAN', $debug = false) {
        try{
            
            $msgTo = '';
            $msgSubject = '';
            $msgBody = '';
            /** Monta o corpo da mensagem */
            $msgBody = "";
            $msgBody .= "Ola: " . $objAvaliador->getNomeConvidado()."\r";
            $msgBody .= "Informamos que o resumo informado abaixo, referente ao \r".DESC_EVENTO.".\r";
            $msgBody .= "Foi removido de sua lista de trabalhos a serem avaliados.\r";
            $msgBody .= "\rTitulo do Trabalho:\r";
            $msgBody .= "(".  maiusculo($objTrabalho->getTituloTrabalho()).")\r";
            $msgBody .= "\r";
            $msgBody .= "Aviso enviado automaticamente.\r";
            $msgBody .= "\r";
            $msgBody .= "att. ".EMAIL_CONTATO_ATT."\r\n";
            $msgBody .= "Dúvidas: ".EMAIL_CONTATO."\r\n";
            $msgBody .= "";

            /** Atribui o assunto */
            $msgSubject = ABREV_EVENTO_EMAIL." - Remoção de Trabalho Indicacao para Avaliação";
            if ($debug) {
                echo $msgBody . "<hr>";
            }

            /** Envia o e-mail */
            $msgTo = $objAvaliador->getEMail();

            // $enviou = GerenciarEmailAC::enviarEmail($msgTo, $msgSubject, $msgBody);
            $enviou = false;
            if (!$enviou) {
                if ($retorno == 'BOOLEAN') { return false; } else { echo "<font color='red'><b>E-mail não enviado</b></font><br>"; }
            }else{
                if ($retorno == 'BOOLEAN'){ return true; }else{ echo "E-mail enviado<br>"; }
            }
        } catch (Exception $exc) {
            if ($retorno == 'BOOLEAN') { return false; } else { echo "<font color='red'><b>$exc->getMessage()</b></font><br>"; }
        }
    }

    public static function comunicadoDevolvido($obj, $retorno = 'BOOLEAN', $debug = false) {
        
        $msgTo = '';
        $msgSubject = '';
        $msgBody = '';
        /** Monta o corpo da mensagem */
        $msgBody = "";
        $msgBody .= "Ola: " . $obj->getNomeApelido()."\r";
        $msgBody .= "Seu Trabalho: " . $obj->getTituloTrabalho()."\r";
        $msgBody .= "Foi avaliado e devolvido para correção.\r";
        $msgBody .= "Consulte o sistema de inscrição para acessar seu trabalho.\r";
        $msgBody .= "Após efetuar as alterações solicitadas, anexo o trabalho novamente no sistema e o encaminhe novamente para avaliação até o dia ".formatDateView($FWS_Data_Trabalho_Tramitacao_Fim)."\r";
        $msgBody .= "Esta é uma mensagem automática, favor não responder.\r";
        $msgBody .= "att. ".EMAIL_CONTATO_ATT."\r\n";
        $msgBody .= "Dúvidas: ".EMAIL_CONTATO."\r\n";
        $msgBody .= "";


        /** Atribui o assunto */
        $msgSubject = ABREV_EVENTO_EMAIL." - Comunicado de Retorno da Avaliacao";
        if ($debug) {
            //echo $msgBody . "<hr>";
        }

        /** Envia o e-mail */
        $msgTo = $obj->getEmail();
        //$msgTo = "contato@opctec.com.br";
//        $msgTo = "zaka_cc@hotmail.com";
//        $enviou = mail($msgTo, $msgSubject, $msgBody, HEADER_EMAIL);
        $enviou = GerenciarEmailAvaliacaoAC::enviarEmail($msgTo, $msgSubject, $msgBody);

        //$enviou = false;
        if (!$enviou) {
            if ($retorno == 'BOOLEAN') { return false; } else { echo "<font color='red'><b>E-mail não enviado</b></font><br>"; }
        }else{
            if ($retorno == 'BOOLEAN'){ return true; }else{ echo "E-mail enviado<br>"; }
        }
    }
    /**
     * 
     * @param Trabalho $obj
     * @param type $retorno BOOLEAN, MSG
     * @param type $debug
     */
    public static function comunicadoReSubmetido($obj, $retorno = 'BOOLEAN', $debug = false) {
        
        $msgTo = '';
        $msgSubject = '';
        $msgBody = '';
        /** Monta o corpo da mensagem */
        $msgBody = "";
        $msgBody .= "Ola: " . $obj->getNomeAvaliador()."\r";
        $msgBody .= "O Trabalho: " . $obj->getTituloTrabalho()."\r";
        $msgBody .= "Foi encaminhado novamente para avaliação.\r";
        $msgBody .= "Esta é uma mensagem automática, favor não responder.\r";
        $msgBody .= "att. ".EMAIL_CONTATO_ATT."\r\n";
        $msgBody .= "Dúvidas: ".EMAIL_CONTATO."\r\n";
        $msgBody .= "";


        /** Atribui o assunto */
        $msgSubject = ABREV_EVENTO_EMAIL." - Comunicado de Retorno para Avaliador";
        if ($debug) {
            //echo $msgBody . "<hr>";
        }

        /** Envia o e-mail */
        $msgTo = $obj->getEMailAvaliador();
        //$msgTo = "contato@opctec.com.br";
//        $msgTo = "zaka_cc@hotmail.com";
        $enviou = GerenciarEmailAC::enviarEmail($msgTo, $msgSubject, $msgBody);
        //$enviou = false;
        if (!$enviou) {
            if ($retorno == 'BOOLEAN') { return false; } else { echo "<font color='red'><b>E-mail não enviado</b></font><br>"; }
        }else{
            if ($retorno == 'BOOLEAN'){ return true; }else{ echo "E-mail enviado<br>"; }
        }
    }
}


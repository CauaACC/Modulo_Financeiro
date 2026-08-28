<?php

class GerenciarEmailAvaliacaoPremiacaoAC {
    
    
    public static function comunicadoAprovacao(){
        //Prezado XXXXXXXXXXXXX ( NOME DO AUTOR)
        //
        //Agradecemos pela submissão do seu trabalho no DESC_EVENTO.
        //Estamos honrados em anunciar que o resumo intitulado
        //XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
        //XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
        //foi considerado “ APROVADO” pela Comissão Científica do evento e deverá ser
        //apresentado em Sessões de Comunicação Coordenada no primeiro dia do evento, 18/7,
        //quarta-feira, entre 9 e 12:30h e entre 14:30 e 18:30.
        //O horário e local de apresentação serão divulgados no site oficial do evento a partir de 1/7 e
        //haverá entre 3 e 4 trabalhos/hora/sala/sessão coordenada.
        //Atenciosamente
        //
        //Comissão Científica do ABREV_EVENTO
    }
    public static function comunicadoReprovacao(){
        //Prezado  XXXXXXXXXXXXX ( NOME DO AUTOR) 
        //
        //Agradecemos pela submissão do seu trabalho no DESC_EVENTO. 
        //Sentimos muito em anunciar que o resumo intitulado  XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX 
        //      foi considerado “  NÃO APROVADO”  pela Comissão Científica, mas esperamos poder contar com sua presença no evento.
        //
        //
        //Atenciosamente
        //
        //Comissão Científica do ABREV_EVENTO
    }
    /**
     * 
     * @param Trabalho $obj
     * @param type $retorno BOOLEAN, MSG
     * @param type $debug
     */
    public static function comunicadoIndicacaoAvaliador($objTrabalho,$objTrabalhoHistorico,$objInscrito,$objAvaliador, $retorno = 'BOOLEAN', $debug = false) {
        
        $objAvaliador->carregarObjLogin();
        $objLogin = $objAvaliador->getObjLogin();
        
        $msgTo = '';
        $msgSubject = '';
        $msgBody = '';
        /** Monta o corpo da mensagem */
        $msgBody = "";
        $msgBody .= "Ola: " . $objAvaliador->getNomeConvidado()."\r";
        $msgBody .= "Você foi indicado a realizar avaliação para premiação de resumos do \r".DESC_EVENTO.".\r";
        $msgBody .= "\r";
        $msgBody .= "Solicitamos que primeiramente faça login no sistema.\r";
        $msgBody .= "http://www.opctec.com.br/inesco/5congresso/avaliacao_premiacao\r";
        $msgBody .= "\r";
        $msgBody .= "Utilize os dados abaixo:\r";
        $msgBody .= "Login: ".$objLogin->getLogin()."\r";
        $msgBody .= "Senha: ".$objLogin->getSenha()."\r";
        $msgBody .= "\r";
        $msgBody .= "Em seguida, proceda à avaliação do trabalho abaixo, utilizando as janelas específicas com critérios para cada tipo de resumo, que facilitarão seu julgamento.\r";
        $msgBody .= "\r";
        $msgBody .= $objTrabalho->getTituloTrabalho()."\r";
        $msgBody .= "\r";
        $msgBody .= "É importante observar atentamente o prazo para a avaliação.\r";
        $msgBody .= "\r";
        $msgBody .= "att. ".EMAIL_CONTATO_ATT."\r\n";
        $msgBody .= "Dúvidas: ".EMAIL_CONTATO."\r\n";
        $msgBody .= "";

        /** Atribui o assunto */
        $msgSubject = ABREV_EVENTO_EMAIL." - Comunicado de Indicacao de Avaliador da Premiação";
        if ($debug) {
            echo $msgBody . "<hr>";
        }

        /** Envia o e-mail */
        $msgTo = $objAvaliador->getEMail();
//        $msgTo = "contato@opctec.com.br";
//        $msgTo = "zaka_cc@hotmail.com";
//        $enviou = mail($msgTo, $msgSubject, $msgBody, HEADER_EMAIL);
            $enviou = GerenciarEmailAC::enviarEmail($msgTo, $msgSubject, $msgBody);

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
    public static function cobrarAvaliador($objTrabalho,$objTrabalhoHistorico,$objInscrito,$objAvaliador, $retorno = 'BOOLEAN', $debug = false) {
        
        $objAvaliador->carregarObjLogin();
        $objLogin = $objAvaliador->getObjLogin();
        
        $msgTo = '';
        $msgSubject = '';
        $msgBody = '';
        /** Monta o corpo da mensagem */
        $msgBody = "";
        $msgBody .= "Ola: " . $objAvaliador->getNomeConvidado()."\r";
        $msgBody .= "Você foi indicado a realizar avaliação para premiação de resumos do \r".DESC_EVENTO.".\r";
        $msgBody .= "\r";
        $msgBody .= "Solicitamos que primeiramente faça login no sistema.\r";
        $msgBody .= "http://www.opctec.com.br/inesco/5congresso/avaliacao_premiacao\r";
        $msgBody .= "\r";
        $msgBody .= "Utilize os dados abaixo:\r";
        $msgBody .= "Login: ".$objLogin->getLogin()."\r";
        $msgBody .= "Senha: ".$objLogin->getSenha()."\r";
        $msgBody .= "\r";
        $msgBody .= "Em seguida, proceda à avaliação do trabalho abaixo, utilizando as janelas específicas com critérios para cada tipo de resumo, que facilitarão seu julgamento.\r";
        $msgBody .= "\r";
        $msgBody .= "".  maiusculo($objTrabalho->getTituloTrabalho())."\r";
        $msgBody .= "\r";
        $msgBody .= "É importante observar atentamente o prazo para a avaliação.\r";
        $msgBody .= "\r";
        $msgBody .= "att. ".EMAIL_CONTATO_ATT."\r\n";
        $msgBody .= "Dúvidas: ".EMAIL_CONTATO."\r\n";
        $msgBody .= "";

        /** Atribui o assunto */
        $msgSubject = ABREV_EVENTO_EMAIL." - Re-aviso de Indicacao de Avaliador da Premiação";
        if ($debug) {
            echo $msgBody . "<hr>";
        }

        /** Envia o e-mail */
        $msgTo = $objAvaliador->getEMail();
//        $msgTo = "elaine.rossi@hotmail.com";
//        $msgTo = "zaka_cc@hotmail.com";
//        $enviou = mail($msgTo, $msgSubject, $msgBody, HEADER_EMAIL);
        $enviou = GerenciarEmailAC::enviarEmail($msgTo, $msgSubject, $msgBody);
        //$enviou = false;
        if (!$enviou) {
            if ($retorno == 'BOOLEAN') { return false; } else { echo "<font color='red'><b>E-mail não enviado</b></font><br>"; }
        }else{
            if ($retorno == 'BOOLEAN'){ return true; }else{ echo "E-mail enviado<br>"; }
        }
    }
    public static function comunicadoDevolvido($obj, $retorno = 'BOOLEAN', $debug = false) {
        
        $msgTo = '';
        $msgSubject = '';
        $msgBody = '';
        /** Monta o corpo da mensagem */
        $msgBody = "";
//        $msgBody .= ABREV_EVENTO_EMAIL." - Comunicado de Retorno da Avaliação\r";
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
        $enviou = GerenciarEmailAC::enviarEmail($msgTo, $msgSubject, $msgBody);
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
//        $msgBody .= ABREV_EVENTO_EMAIL." - Comunicado de Retorno para Avaliador\r";
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
//        $enviou = mail($msgTo, $msgSubject, $msgBody, HEADER_EMAIL);
        $enviou = GerenciarEmailAC::enviarEmail($msgTo, $msgSubject, $msgBody);
        //$enviou = false;
        if (!$enviou) {
            if ($retorno == 'BOOLEAN') { return false; } else { echo "<font color='red'><b>E-mail não enviado</b></font><br>"; }
        }else{
            if ($retorno == 'BOOLEAN'){ return true; }else{ echo "E-mail enviado<br>"; }
        }
    }
}
?>

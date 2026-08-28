<?php

class GerenciarEmailComunicadoAC
{
        /**
     * 
     * @param Trabalho $obj
     * @param type $retorno BOOLEAN, MSG
     * @param type $debug
     */
    // public static function corpoNovoModeloTrabalho($obj, $fws_, $retorno = 'BOOLEAN', $debug = true)
    // {
    //     echo __LINE__.'-';
    // }
    public static function acaoEnviarComunicados($objL, $fws_, $retorno = 'BOOLEAN', $debug = true)
    {
        if($fws_['comunicado']===0){
            echo "<br>Não enviar<br>";
            return false;
        }
        if ($fws_['acaoEmail']=='') {
            echo "Nenhuma ação selecionada";
            exit();
        }    
        // /** emails bloqueados */
        // if($liberado==true){
        //     if($fws_['comunicado']==1){
        //         if($objL->getAvaliacao()=='DEVOLVIDO'){
        //             echo "Comunicado devolução para correção: <br>";
        //             GerenciarEmailAvaliacaoAC::comunicadoDevolvido($objL,'MSG',false);
        //         }
    
        //         if($objL->getAvaliacao()=='RE-SUBMETIDO'){
        //             echo "Comunicado retorno para avaliação:<br>";
        //             GerenciarEmailAvaliacaoAC::comunicadoReSubmetido($objL,'MSG',false);
        //         }
        //     }
        //     if($fws_['comunicado']=='certificadoLiberacao'){
        //         echo "<br>Comunicado certificado liberado:<br>";
        //         GerenciarCertificadoAC::comunicadoCertificadoLiberado($objL,'MSG',false);
        //     }
        //     if($fws_['comunicado']=='emailIsentos'){
        //         echo "<br>Comunicado email Isentos:<br>";
        //         GerenciarCertificadoAC::comunicadoIsentos($objL,'MSG',false);
        //     }
        // }

        if($fws_['comunicado']=='emailHutecFormadores'){
            echo "Comunicado Hutec para formadores: <br>";
            GerenciarEmailComunicadoAC::corpoEmailHutecFormadores($objL,$fws_, 'MSG',false);
        }
        if($fws_['comunicado']=='emailFormadoresConvocados'){
            echo "Comunicado formadores convocados: <br>";
            GerenciarEmailComunicadoAC::corpoEmailFormadoresConvocados($objL,$fws_, 'MSG',false);
        }
        if($fws_['comunicado']=='emailComunicadoDesligamento'){
            echo "Comunicado desligamento: <br>";
            GerenciarEmailComunicadoAC::corpoEmailComunicadoDesligamento($objL,$fws_, 'MSG',false);
        }
        if($fws_['comunicado']=='emailSolicitacaoAtualizacaoDados'){
            echo "Comunicado atualização dados: <br>";
            GerenciarEmailComunicadoAC::corpoEmailSolicitacaoAtualizacaoDados($objL,$fws_, 'MSG',false);
        }
        // if($fws_['comunicado']=='novoModelo'){
        //     echo "Novo Modelo: <br>";
        //     GerenciarEmailComunicadoAC::corpoNovoModelo($objL,$fws_, 'MSG',false);
        // }
        if($fws_['comunicado']!=''){
            // Carrega template do banco pelo ID se assunto/corpo não vieram no POST
            if(empty($fws_['email_template_body']) && is_numeric($fws_['comunicado'])){
                $objTemplate = new EmailTemplate();
                $objTemplate->setCodEmailTemplate($fws_['comunicado']);
                $objTemplate->getEmailTemplateById(false);
                if($objTemplate->getAssunto()){
                    $fws_['email_subject'] = $objTemplate->getAssunto();
                    $fws_['email_template_body'] = $objTemplate->getCorpo();
                }
            }
            if($fws_['email_bcc']!=''){
                echo "Com cópia para: ".$fws_['email_bcc']."<br>";
            }
            GerenciarEmailComunicadoAC::corpoNovoModelo($objL,$fws_, 'MSG',false);
        }
    }

    public static function corpoNovoModelo($obj, $fws_, $retorno = 'BOOLEAN', $debug = true)
    {
        $msgTo = '';
        $msgSubject = '';
        $msgBody = '';
        $msgDefaultFoot = " Esta é uma mensagem automática, favor não responder.<br><br>";
        // $objEvConf = EventoConfiguracao::getInstancePopulado(COD_EVENTO);
        if($obj->getUrlPrefixo()==''){
            $urlPrefixo = WB_GRUPO.'/'.WB_PROJETO;
        }else{
            $urlPrefixo = $obj->getUrlPrefixo();
        }
        
        $LINK_PAGAMENTO = "https://".FWA_Dominio()."/".$urlPrefixo."/inscricoes/".$obj->getQrCode();
        $LINK_AREA_INSCRITO = "https://".FWA_Dominio()."/".$urlPrefixo."/inscricoes/";
        // if($obj->getCodEvento()==80){
        //     $LINK_AREA_INSCRITO = "https://".FWA_Dominio()."/hutec/80/inscricoes/";
        // }else{
        //     $LINK_AREA_INSCRITO = "https://".FWA_Dominio()."/".$urlPrefixo."/inscricoes/";
        // }
        
        $NOME_INSCRITO  = $obj->getNomeInscrito();
        $PRE_NOME       = $obj->getPreNome();
        $LOGIN     = $obj->getEmail();
        if ($obj->getEmail()=='') {
            $LOGIN     = $obj->getEMail();
        }
        $CREDENCIAL     = $obj->getCodInscrito();
        $NOME_PROJETO   = ABREV_EVENTO;

        // Arrays de buscas e substituições
        $search     = array('[LINK_PAGAMENTO]','[LINK_AREA_INSCRITO]', '[NOME_INSCRITO]','[PRE_NOME]','[LOGIN]','[CREDENCIAL]','[NOME_PROJETO]');
        $replace    = array($LINK_PAGAMENTO, $LINK_AREA_INSCRITO, $NOME_INSCRITO,$PRE_NOME,$LOGIN,$CREDENCIAL, $NOME_PROJETO,$NOME_PROJETO);
        // Substituindo os placeholders em cópia local (preserva template original para o loop)
        $corpoEmail = str_replace($search, $replace, $fws_['email_template_body']);

        /** Monta o corpo da mensagem */
        $msgBody = "<html><body>";
        $msgBody .= "Assunto: ".$fws_['email_subject']."<br>\n";
        $msgBody .= $corpoEmail;
        $msgBody .= "\n<br>\n";
        $msgBody .= "------------------------------------------------------\n<br>\n";
        $msgBody .= $msgDefaultFoot;
        $msgBody .= "</body></html>";

        $msgTo = $obj->getEmail();
        if($msgTo==''){
            $msgTo = $obj->getEMail();
        }
        /** Atribui o assunto */
        $msgSubject = "" . ABREV_EVENTO . " - ".$fws_['email_subject'];
        if ($fws_['acaoEmail']=='testarEmail') {
            echo "<hr>".$msgSubject . "";
            echo "<br>".$msgBody . "";
        }
        if ($fws_['acaoEmail']=='enviarEmail') {
            /** Envia o e-mail */
            //$msgTo = "contato@opctec.com.br";
            // $msgTo = "zaka_cc@hotmail.com";
            // $msgTo = "zakacc@gmail.com";
            $emailBcc = '';
            if($fws_['email_bcc']!=''){
                $emailBcc = $fws_['email_bcc'];
            }
            $enviou = GerenciarEmailAC::enviarEmail($msgTo, $msgSubject, $msgBody, $emailBcc);
            //$enviou = false;

            // Registrar na fila de emails (historico)
            try {
                $objFila = new EmailFila();
                $objFila->setCodEmailTemplate(is_numeric($fws_['comunicado']) ? $fws_['comunicado'] : null);
                $objFila->setCodInscrito($obj->getCodInscrito());
                $objFila->setEmailDestinatario($msgTo);
                $objFila->setNomeDestinatario($obj->getNomeInscrito());
                $objFila->setAssunto($msgSubject);
                $objFila->setCorpo($msgBody);
                $objFila->setDatProgramado(date('Y-m-d H:i:s'));
                $objFila->setDatEnviado($enviou ? date('Y-m-d H:i:s') : null);
                $objFila->setStatus($enviou ? 'ENVIADO' : 'ERRO');
                $objFila->setTentativas(1);
                if (!$enviou) {
                    $objFila->setErroMensagem('Falha no envio via GerenciarEmailAC::enviarEmail');
                }
                $objFila->salvar(false);
            } catch (OKException $e) {
                // gravou com sucesso, segue normalmente
            } catch (Exception $e) {
                // falha ao gravar na fila nao deve interromper o fluxo
            }

            if (!$enviou) {
                if ($retorno == 'BOOLEAN') {
                    return false;
                } else {
                    echo "<font color='red'><b>E-mail não enviado: ".$msgTo."</b></font><br>";
                }
            } else {
                if ($retorno == 'BOOLEAN') {
                    return true;
                } else {
                    echo "E-mail enviado: ".$msgTo."<br>";
                }
            }
        }else{
            echo "Não enviado: ".$msgTo."<br>";
        }
        echo "---------------------------------<br>";
    }

    /**
     * 
     * @param Trabalho $obj
     * @param type $retorno BOOLEAN, MSG
     * @param type $debug
     */
    public static function corpoEmailHutecFormadores(Inscrito $obj, $fws_, $retorno = 'BOOLEAN', $debug = true)
    {

        $msgTo = '';
        $msgSubject = '';
        $msgBody = '';
        $msgDefaultFoot = " Esta é uma mensagem automática, favor não responder.<br><br>";

        /** Monta o corpo da mensagem */
        $msgBody = "<html><body>";
        $msgBody .= "Assunto: Comunicado Referente ao Projeto de Extensão
Caros Colaboradores e Parceiros,

Disponibilizamos este comunicado para pontuar sobre alguns procedimento administrativos referente ao Projeto de Extensão de Formação Continuada.
Para acesso completo ao comunicado, [clique aqui](Inserir Link Aqui) ou no link a seguir: [Inserir Link Aqui].
        
Agradecemos sua atenção e comprometimento contínuo com o projeto.
        
Atenciosamente,
        
Prof. Dr. Francisco Eugênio Alves de Souza
Presidente HUTEC
";
        $msgBody .= "\n<br>\n";
        $msgBody .= "------------------------------------------------------\n<br>\n";
        $msgBody .= $msgDefaultFoot;
        $msgBody .= "</body></html>";

        $msgTo = $obj->getEmail();
        /** Atribui o assunto */
        $msgSubject = "" . ABREV_EVENTO . " - " . NOME_EVENTO . "";
        if ($fws_['acaoEmail']=='testarEmail') {
            echo $msgSubject . "<hr>";
            echo $msgBody . "<hr>";
        }
        if ($fws_['acaoEmail']=='enviarEmail') {
            /** Envia o e-mail */
            //$msgTo = "contato@opctec.com.br";
            // $msgTo = "zaka_cc@hotmail.com";
            // $msgTo = "zaka_cc@gmail.com";
            $enviou = GerenciarEmailAC::enviarEmail($msgTo, $msgSubject, $msgBody);
            //$enviou = false;
            if (!$enviou) {
                if ($retorno == 'BOOLEAN') {
                    return false;
                } else {
                    echo "<font color='red'><b>E-mail não enviado</b></font><br>";
                }
            } else {
                if ($retorno == 'BOOLEAN') {
                    return true;
                } else {
                    echo "E-mail enviado<br>";
                }
            }
        }

    }
    /**
     * 
     * @param Trabalho $obj
     * @param type $retorno BOOLEAN, MSG
     * @param type $debug
     */
    public static function corpoEmailFormadoresConvocados(Inscrito $obj, $fws_, $retorno = 'BOOLEAN', $debug = true)
    {
        $objEvConf = $fws_['objEvConf'];
        $msgTo = '';
        $msgSubject = '';
        $msgBody = '';
        if(COD_EVENTO==46){
            $link = "https://hutec.org.br/curso/edital-de-chamamento-publico-no-010-2023";
            $abrevEvento = "Paraná Educação Ed. 010/2023";
        }
        if(COD_EVENTO==38){
            $link = "https://hutec.org.br/curso/edital-de-chamamento-publico-no-002-2023/";
            $abrevEvento = "Paraná Educação Ed. 002/2023";
        }
        if(COD_EVENTO==37){
            $link = "https://hutec.org.br/curso/edital-de-chamamento-publico-001-2023/";
            $abrevEvento = "Paraná Educação Ed. 001/2023";
        }
        $msgSubject = "" . $abrevEvento . " - Comunicado";
        $msgDefaultFoot = " Esta é uma mensagem automática, favor não responder.<br><br>";

        /** Monta o corpo da mensagem */
        $msgBody = "<html><body>";
        $msgBody .= "Comunicado.\n<br>";
        $msgBody .= $msgSubject . "\n<br>";
        $msgBody .= "Informamos que um novo edital de convocação foi publicado.
        Encorajamos todos os interessados a consultá-lo para obter informações detalhadas sobre o processo e as próximas etapas.
        Para acessar o edital completo, <a href=".$link.">[clique aqui]</a> ou visite o link a seguir: [".$link."].
        Agradecemos sua atenção
        ";
        $msgBody .= $msgDefaultFoot;
        
        $msgBody .= "</body></html>";

        $msgTo = $obj->getEmail();
        if ($fws_['acaoEmail']=='testarEmail') {
            echo "<b>Assunto:</b><br>".$msgSubject . "<hr>";
            echo "<b>Mensagem:</b><br>".$msgBody . "<hr>";
        }
        if ($fws_['acaoEmail']=='enviarEmail') {
            /** Envia o e-mail */
            //$msgTo = "contato@opctec.com.br";
            // $msgTo = "zaka_cc@hotmail.com";
            // $msgTo = "zakacc@gmail.com";
            $enviou = GerenciarEmailAC::enviarEmail($msgTo, $msgSubject, $msgBody);
            //$enviou = false;
            if (!$enviou) {
                if ($retorno == 'BOOLEAN') {
                    return false;
                } else {
                    echo "<font color='red'><b>E-mail não enviado</b></font><br>";
                }
            } else {
                if ($retorno == 'BOOLEAN') {
                    return true;
                } else {
                    echo "E-mail enviado<br>";
                }
            }
        }
    }
    /**
     * 
     * @param Trabalho $obj
     * @param type $retorno BOOLEAN, MSG
     * @param type $debug
     */
    public static function corpoEmailComunicadoDesligamento(Inscrito $obj, $fws_, $retorno = 'BOOLEAN', $debug = true)
    {
        $objEvConf = $fws_['objEvConf'];
        $msgTo = '';
        $msgSubject = '';
        $msgBody = '';
        if(COD_EVENTO==46){
            $link = "https://hutec.org.br/curso/edital-de-chamamento-publico-no-010-2023";
            $abrevEvento = "Paraná Educação Edital 010/2023";
        }
        if(COD_EVENTO==38){
            $link = "https://hutec.org.br/curso/edital-de-chamamento-publico-no-002-2023/";
            $abrevEvento = "Paraná Educação Edital 002/2023";
        }
        if(COD_EVENTO==37 or COD_EVENTO==44){
            $link = "https://hutec.org.br/curso/edital-de-chamamento-publico-001-2023/";
            $abrevEvento = "Paraná Educação Edital 001/2023";
            $linkDownloadTermo = "https://hutec.org.br/wp-content/uploads/2024/05/TERMO-DE-DESLIGAMENTO-DO-PROJETO-PSICOLOGO-EDITAL-001.docx";
        }
        $msgSubject = "" . $abrevEvento . " - Solicitação de Assinatura de Termo de Desligamento";
        $msgDefaultFoot = " Esta é uma mensagem automática, favor não responder.<br><br>";

        /** Monta o corpo da mensagem */
        $msgBody = "";
        // $msgBody = "<html><body>";
        // $msgBody .= "Comunicado.\n<br>";
        // $msgBody .= $msgSubject . "\n<br>";
        $msgBody .= "Olá ".$obj->getPreNome()."
        Como parte do processo de desligamento, segue neste e-mail o link para download do termo de desligamento que precisa ser assinado digitalmente.e após assinatura ser anexado no sistema ou encaminhado para esse email.
        Isso nos ajudará a formalizar sua saída de forma organizada e sem pendências.
        Se houver qualquer dúvida ou se precisar de assistência durante esse processo, por favor, entre em contato conosco.

        Clique no link a seguir para downlaod do termo de deslitamento [".$linkDownloadTermo."]

        Segue seus dados para acesso:
        ".$link."
        login: ".$obj->getEmail()."
        senha: ".$obj->getCodInscrito()."  
        
        Agradecemos novamente pelo seu trabalho e colaboração.
        ";
        $msgBody .= $msgDefaultFoot;
        
        // $msgBody .= "</body></html>";

        $msgTo = $obj->getEmail();
        if ($fws_['acaoEmail']=='testarEmail') {
            echo "<b>Assunto:</b><br>".$msgSubject . "<hr>";
            echo "<b>Mensagem:</b><br>".$msgBody . "<hr>";
            echo "<b>Para:</b><br>".$msgTo . "<hr>";
        }

        if ($fws_['acaoEmail']=='enviarEmail') {
            /** Envia o e-mail */
            //$msgTo = "contato@opctec.com.br";
            // $msgTo = "zaka_cc@hotmail.com";
            // $msgTo = "zakacc@gmail.com";
            $enviou = GerenciarEmailAC::enviarEmail($msgTo, $msgSubject, $msgBody);
            //$enviou = false;
            if (!$enviou) {
                if ($retorno == 'BOOLEAN') {
                    return false;
                } else {
                    echo "<font color='red'><b>E-mail não enviado</b></font><br>";
                }
            } else {
                if ($retorno == 'BOOLEAN') {
                    return true;
                } else {
                    echo "E-mail enviado: ".$msgTo."<br>";
                }
            }
        }
    }
    /**
     * 
     * @param Trabalho $obj
     * @param type $retorno BOOLEAN, MSG
     * @param type $debug
     */
    public static function corpoEmailSolicitacaoAtualizacaoDados(Inscrito $obj, $fws_, $retorno = 'BOOLEAN', $debug = true)
    {
        $objEvConf = $fws_['objEvConf'];
        $msgTo = '';
        $msgSubject = '';
        $msgBody = '';
        if (in_array(COD_EVENTO, array(46,50))) {
            $link = "https://hutec.org.br/curso/edital-de-chamamento-publico-no-010-2023";
            $abrevEvento = "Paraná Educação Edital 010/2023";
        }
        if (in_array(COD_EVENTO, array(38,42))) {
            $link = "https://hutec.org.br/curso/edital-de-chamamento-publico-no-002-2023/";
            $abrevEvento = "Paraná Educação Edital 002/2023";
        }
        if (in_array(COD_EVENTO, array(37,44))) {
            $link = "https://hutec.org.br/curso/edital-de-chamamento-publico-001-2023/";
            $abrevEvento = "Paraná Educação Edital 001/2023";
            $linkDownloadTermo = "https://hutec.org.br/wp-content/uploads/2024/05/TERMO-DE-DESLIGAMENTO-DO-PROJETO-PSICOLOGO-EDITAL-001.docx";
        }
        $msgSubject = "" . $abrevEvento . " - Atualização dos dados cadastrais";
        $msgDefaultFoot = " Esta é uma mensagem automática, favor não responder.<br><br>";

        /** Monta o corpo da mensagem */
        $msgBody = "";
        // $msgBody = "<html><body>";
        // $msgBody .= "Comunicado.\n<br>";
        // $msgBody .= $msgSubject . "\n<br>";
        $msgBody .= "Olá ".$obj->getPreNome()."
        Estamos entrando em contato para solicitar que você acesse o sistema e atualize seus dados cadastrais.
        Algumas informações podem estar faltando ou precisam ser atualizadas.
        Utilize os dados abaixo para acessar a área do prestador de serviço 
        Segue seus dados para acesso:
        ".$link."
        login: ".$obj->getEmail()."
        senha: ".$obj->getCodInscrito()."  

        Após efetuar o login, clique no botão [Editar dados de sua inscrição], faça uma revisão nos dados informados e clique em salvar.

        Para dúvidas entre em contato conosco: [<a href='https://wa.me/5543996967790' target='_blank'>WhatsApp Atendimento</a>]
        
        Agradecemos antecipadamente pela colaboração.
        ";
        $msgBody .= $msgDefaultFoot;
        
        // $msgBody .= "</body></html>";

        $msgTo = $obj->getEmail();
        if ($fws_['acaoEmail']=='testarEmail') {
            echo "<b>Assunto:</b><br>".$msgSubject . "<hr>";
            echo "<b>Mensagem:</b><br>".$msgBody . "<hr>";
            echo "<b>Para:</b><br>".$msgTo . "<hr>";
        }

        if ($fws_['acaoEmail']=='enviarEmail') {
            /** Envia o e-mail */
            //$msgTo = "contato@opctec.com.br";
            // $msgTo = "zaka_cc@hotmail.com";
            // $msgTo = "zakacc@gmail.com";
            $enviou = GerenciarEmailAC::enviarEmail($msgTo, $msgSubject, $msgBody);
            //$enviou = false;
            if (!$enviou) {
                if ($retorno == 'BOOLEAN') {
                    return false;
                } else {
                    echo "<font color='red'><b>E-mail não enviado</b></font><br>";
                }
            } else {
                if ($retorno == 'BOOLEAN') {
                    return true;
                } else {
                    echo "E-mail enviado: ".$msgTo."<br>";
                }
            }
        }
    }
}

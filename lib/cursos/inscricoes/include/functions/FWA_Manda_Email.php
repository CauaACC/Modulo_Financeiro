<?php
/**
 * @deprecated 2026-05-02 — função legada de evento muito antigo.
 * Envia via PHPMailer com SMTP hardcoded (smtp.opctec.com.br), ignorando o
 * switch SendGrid/Postmark do GerenciarEmailAC::enviarEmail().
 * Mantida apenas pra preservar histórico e não quebrar o require_once
 * em functions.php. NÃO usar — qualquer chamada é abortada com die().
 * @todo remover
 */
Function FWA_Manda_Email($Var_Pagina_Proprietaria, $Var_Url_Erro, $Var_Url_Ok, $Var_Endereco_Destinatario, $Var_Nome_Destinatario, $Var_Endereco_Remetente, $Var_Nome_Remetente, $Var_caminho_arquivo, $Var_Htm, $Var_Subject, $Var_Corpo){
    die('O arquivo ou função está descontinuado, entrar em contato com a administração.');


#---------------------------------------------------------------------
#Definição das variaveis utilizadas neste script
#---------------------------------------------------------------------
/*
  echo $Var_Pagina_Proprietaria;      #Endereço da pagina que esta enviado o email

  echo $Var_Url_Erro;                 #Endereço de uma pagina informando que houve erro no envo dos dados
  echo $Var_Url_Ok;                   #Endereço de uma pagina que informe que houve sucesso no envio dos dados

  echo $Var_Endereco_Destinatario;    #O email da pessoa que recebera os dados do formulario
  echo $Var_Nome_Destinatario;        #O nome da pessoa que recebera os dados do email

  echo $Var_Endereco_Remetente;
  echo $Var_Nome_Destinatario;

  echo $FWS_caminho_arquivo;          # caminho do arquivo a ser atachado, o mesmo ja deve estar no servidor... enviado atravez do upload

  echo $FWS_Htm;
  echo $FWS_Subject;
  echo $FWS_Corpo = $FWS_Corpo."\n\n----------------------------------------\n".$Var_Pagina_Proprietaria;

  #$AltBody
*/
#---------------------------------------------------------------------
#Classes requeridas
#---------------------------------------------------------------------
  require_once(dirname(__FILE__)."/../../include/functions/email/class.phpmailer.php");

#---------------------------------------------------------------------
#Devinir smtp de acordo com o servidor de hospedagem da pagina
/*
  Switch ($_SERVER['SERVER_NAME']){
    Case "zaka":
      $FWA_SMTP = "smtp.sercomtel.com.br";
      break;
    Default:
      $FWA_SMTP = "smtp.pop.com.br";
      return false;
    break;
  }
*/
#---------------------------------------------------------------------
#Instanciando objeto
#---------------------------------------------------------------------
  $mail = new PHPMailer();

  $mail->PluginDir= $_SERVER["DOCUMENT_ROOT"]."/congresso/inscricoes/include/functions/email";                     # send via SMTP # Não Alterar


  $mail->IsSMTP();                     # send via SMTP # Não Alterar

  $mail->Host     = $FWA_SMTP;         # SMTP servers  # Não Alterar
  $mail->SMTPAuth = true;              # turn on SMTP authentication
  $mail->Username = "manda-email-web"; # SMTP username - login do remetente
  $mail->Password = "asdfasdf";        # SMTP password - senha do remetente

  #Alguns servidores filtram os email q não tenham remetente
  $mail->From     = $Var_Endereco_Remetente;                            #email do remetente
  $mail->FromName = $Var_Nome_Remetente;                                #nome do remetente

  $mail->AddAddress($Var_Endereco_Destinatario,$Var_Nome_Destinatario); #email e nome do destinatario

  $mail->AddBCC("zaka_cc@hotmail.com",$Var_Nome_Destinatario);          #email e nome do destinatario

  $mail->WordWrap = 50;                                                 # define largura do texto
  If (@$Var_caminho_arquivo){
    $mail->AddAttachment(@$Var_caminho_arquivo);   # caminho do arquivo a ser atachado, o mesmo ja deve estar no servidor... enviado atravez do upload
  }
  #$mail->AddAttachment("/tmp/image.jpg", "new.jpg");

  If (@!$Var_Htm){
    $Var_Htm = true;
  }Else{
    $Var_Htm = @$Var_Htm;
  }
  $mail->IsHTML($Var_Htm);           # tipo do email, se é HTML ou texto

  $mail->Subject = $Var_Subject;     # Subject
  $mail->Body    = $Var_Corpo;       # corpo do email
 #$mail->AltBody = $Var_AltBody;     # conteudo acima do corpo da mensagem

#---------------------------------------------------------------------
#Enviando Email
#---------------------------------------------------------------------
  If(!$mail->Send()){               # envia e testa se a mensagem foi enviada, em caso de erro entra.
      If (@!$Var_Url_Erro){         # se não estiver difinido retorna valor lógico, falso.
          return false;
      }Else{
          header("Location: $Var_Url_Erro");  # se estiver difinido retorna a pagina de erro.
      }
      #echo $Var_Url_Erro;
  }Else{
      If (@!$Var_Url_Ok){ # se não estiver difinido retorna valor lógico, verdadeiro.
          return true;
      }Else{
          header("Location: $Var_Url_Ok");    # se estiver difinido retorna a pagina de ok.
      }
      #echo $Var_Url_Ok;
  }

  #return;
#---------------------------------------------------------------------
}
?>

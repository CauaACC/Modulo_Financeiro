<?
Function MandaEmail($Var_Pagina_Proprietaria, $Var_Url_Erro, $Var_Url_Ok, $Var_Endereco_Destinatario, $Var_Nome_Destinatario, $Var_Endereco_Remetente, $Var_Nome_Remetente, $Var_caminho_arquivo, $Var_Htm, $Var_Subject, $Var_Corpo){


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

  include_once($_SERVER["APPL_PHYSICAL_PATH"]."/congresso/inscricoes/classes/email/class.phpmailer.php");

#---------------------------------------------------------------------
#Instanciando objeto
#---------------------------------------------------------------------
  $mail = new PHPMailer();

  $mail->PluginDir= $_SERVER["DOCUMENT_ROOT"]."/congresso/inscricoes/classes/email";                     # send via SMTP # Não Alterar
  $mail->IsSMTP();                     # send via SMTP # Não Alterar
  $mail->Host     = "smtp.opctec.com.br";     # SMTP servers  # Não Alterar
  $mail->SMTPAuth = true;              # turn on SMTP authentication
  $mail->Username = "manda-email-web"; # SMTP username - login do remetente
  $mail->Password = "asdfasdf";        # SMTP password - senha do remetente

  #Alguns servidores filtram os email q não tenham remetente
  $mail->From     = $Var_Endereco_Remetente;    #email do remetente
  $mail->FromName = $Var_Nome_Remetente;        #nome do remetente

  $mail->AddAddress($Var_Endereco_Destinatario,$Var_Nome_Destinatario);   #email e nome do destinatario

  $mail->AddAddress("zaka@opctec.com.br","WEB-SCRIPT");          #email e nome do destinatario
  #$mail->AddAddress("zaka@opctec.com.br");                           #email e nome do destinatario de mais um destinatario

  $mail->WordWrap = 50;                                        # define largura do texto
  If (@$Var_caminho_arquivo){
    $mail->AddAttachment(@$Var_caminho_arquivo);   # caminho do arquivo a ser atachado, o mesmo ja deve estar no servidor... enviado atravez do upload
  }
  #$mail->AddAttachment("/tmp/image.jpg", "new.jpg");

  If (@!$Var_Htm){
    $Var_Htm = true;
  }Else{
    $Var_Htm = @$Var_Htm;
  }
  $mail->IsHTML($Var_Htm);           # tipo do email, se HTML ou texto

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

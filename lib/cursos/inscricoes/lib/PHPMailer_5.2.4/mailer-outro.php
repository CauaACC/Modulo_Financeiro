<?php
include_once ('PHPMailer_5.2.4/class.phpmailer.php');
# Instancia objeto PHPMailer
$mail = new PHPMailer();
$mail->SMTPDebug = 1;
# Configuração do envio de e-mail
$mail->IsSMTP();             # Enviar via SMTP
$mail->SMTPSecure = "ssl";
$mail->Host = "smtp.gmail.com";             # Servidor SMTP do portal
$mail->Port = 465;
$mail->SMTPAuth = 1;              # Ligar a autenticação SMTP
//$mail->Username = "zaka@uel.br";       # Username SMTP do Portal
//$mail->Password = "Emc27519";          # Senha SMTP do Portal
//$mail->From = "zakacc@gmail.com";    # Endereço do remetente

$mail->Username = "portalposgraduacao@uel.br";       # Username SMTP do Portal
$mail->Password = "Posgraduacao2017"; 		       # Senha SMTP do Portal
$mail->From = "portalposgraduacao@uel.br";    # Endereço do remetente

#$mail->Username = "web";       # Username SMTP do Portal
#$mail->Password = "Yth98317"; 		       # Senha SMTP do Portal
#$mail->From = "web@uel.br";    # Endereço do remetente

$mail->FromName = "Portal do Estudante Pos-Graduacao";   # Nome do remetente

$mail->AddAddress('zakacc@gmail.com');
$mail->WordWrap = 50;                      # Set word wrap
$mail->IsHTML(true);                       # Enviar como HTML
$data = date("d/m/Y H:i:s");               # Data que está sendo enviado o e-mail
$mail->Subject = "SSL - Testando email de " . $mail->From;           # Assunto
$mail->Body = "Corpo do email teste " . $mail->From;           # Adicionado o corpo do email
# Envia o e-mail
if ($mail->Send()) {
    echo "enviou";
} else {
    echo "não enviou<hr>";

    var_dump($mail->ErrorInfo);
    echo "<hr>";
}
echo Date('d-m-Y H:i:s');
?>
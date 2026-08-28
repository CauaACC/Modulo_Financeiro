<?php
error_reporting(~E_NOTICE);
ini_set('display_errors', 'On');

$ok = $_POST['submit_ok'];
$cpf = trim($_POST['cpf']);
        
$cpf = str_replace("-", "", $cpf);
$cpf = str_replace(".", "", $cpf);
$cpf = addslashesopctec($cpf);

$msg_erro = "";
$ip = $_SERVER['REMOTE_ADDR'];
if ((!is_numeric($cpf)) && ($cpf != "")) {
    $msg_erro = "CPF inválido ou não preenchido!";
}
if (($ok == "OK") && ($msg_erro == "")) {
    //include "connect.php";
    include "conecta/bd.php";
    include "envia_email.php";

    $query = "select nome, cpf, email, senha from pessoa where cpf='" . $cpf . "'";
    //echo $query;
    $result = pg_query($query);
    if (!$result) {
        $msg_erro = "CPF não encontrado ou erro na consulta, acesse o link contato ou entre em contato com sua instituição.";
    } else {
        $dados = pg_fetch_object($result);
        if ($dados->cpf != "") {
            $to = $dados->email;
            $subject = "[" . $fws['siglaEvento'] . "] Recuperacao de senha";
            $message = "Sr.(a) " . $dados->nome . " \r\n\r\n" .
                    "sua senha para acesso ao site do " . $fws['siglaEvento'] . ": \r\n" .
                    "Senha: " . $dados->senha . "\r\n\r\n\r\n" .
                    "Seu endereco de IP foi registrado para garantir sua seguranca." . "\r\n" .
                    "IP: " . $ip . "\r\n\r\n" .
                    "\r\n\r\n" .
                    "http://www.opctec.com.br\r\n\r\n" .
                    "* * * Nao responda a essa mensagem, pois foi gerada automaticamente. * * *";

            $from = $fws['emailEvento'];

//            $headers["From"] = $fws['emailEvento'];
//            $headers["To"] = $to;
//            $headers["Subject"] = $subject;
//            $headers["Content-Type"] = 'text/html; charset=UTF-8';
//            $headers["Content-Transfer-Encoding"]= "8bit";
//
//
//            $mime = new Mail_mime;
//            $mime->setTXTBody($message);
//            //$mime->setHTMLBody($html_message);
//            $mimeparams=array();
//
//            $mimeparams['text_encoding']="8bit";
//            $mimeparams['text_charset']="UTF-8";
//            $mimeparams['html_charset']="UTF-8";
//            $mimeparams['head_charset']="UTF-8";
//
//            $mimeparams["debug"] = "True";
//
//            $body = $mime->get($mimeparams);
//            $headers = $mime->headers($headers);
//            //$page_content = "Mail now.";
//
//
//            // SMTP server name, port, user/passwd
//            $smtpinfo["host"] = "smtp.opctec.com.br";
//            //$smtpinfo["port"] = "25";
//            //$smtpinfo["auth"] = true;
//            //$smtpinfo["username"] = "...";
//            //$smtpinfo["password"] = "...";
//            //$smtpinfo["debug"] = "True";
//
//            
//            
//            // Create the mail object using the Mail::factory method
//            $mail=& Mail::factory("smtp", $smtpinfo);
//
//            $mail->send($to, $headers, $body);
//            mail($to, $subject, $message, "From: " . $fws['emailEvento'] . "\r\n" . $fws['headers']);
            
            
            if (enviaEmail($from, $to, $subject, $message)){
                $msg = "Sua senha foi enviada para " . $dados->email;
                echo "<script language='JavaScript'>
                      alert('Mensagem enviada com sucesso!\\nSua senha foi enviada para " . $dados->email . " ');
                      window.location = 'index.php?op=recup';
                      </script>";
                //header("Location: index.php?op=recup");
            }else{
                echo "<script language='JavaScript'>
                  alert('A senha nao pode ser enviada!\\nTente novamente mais tarde.');
                  window.location = 'index.php?op=recup';
                  </script>";
            }
        } else {
            $msg_erro = "CPF n&atilde;o cadastrado.";
        }
    }
}
?>
<script language="JavaScript">
    function recupera(){
        var cpf;
        cpf = document.getElementById('cpf').value;
        if (cpf == ""){
            alert ('Digite um CPF');
        } else {
            document.getElementById('ok').value = 'OK';
            document.recupera_senha.submit();
        }
    }
</script> 
<?
if ($debugMostraArquivo) {
    echo "<br>" . __FILE__ . "<br>";
}
?>
<br /><br /><br />
<form method="post" name="recupera_senha" action="index.php?op=recup">
    <table  align="center">
        <tr>
            <td colspan="2" align="center">
                Para recuperar sua senha &eacute; necess&aacute;rio seu CPF:
            </td>
        </tr>    
        <tr>
            <td align="right" width="35%">
                CPF:
            </td>
            <td>
                <input type="text" size="11" maxlength="14" name="cpf" id="cpf">
            </td>
        </tr>
        <tr>
            <td></td>
            <td align="left">
                <input name="submit_ok" id="ok" type="hidden" value="">
                <input type="button" value=" Recuperar " onclick="recupera();">
            </td>
        </tr>
        <tr>
            <td colspan="2">
                Depois de recuperar a senha, preste aten&ccedil;&atilde;o &agrave; mensagem que aparece, onde 
                &eacute; mostrado o email para o qual ela foi enviada. Consulte a lixeira, caixa de spam, etc.,
                pois o email poder&aacute; ir para l&aacute;, dependendo do seu provedor. Caso o email seja diferente daquele 
                usado por voc&ecirc;, ser&aacute; preciso atualizar seus dados tamb&eacute;m na sua institui&ccedil;&atilde;o, pois ele 
                veio do cadastro de l&aacute;.
            </td>
        </tr>
<!--        <tr>
            <td colspan="2">
                ATEN&Ccedil&Atilde;O: funcionalidade temporariamente indispon&iacute;vel at&eacute; 26/06/12, &agrave; tarde, 
                para resolu&ccedil;&atilde;o dos problemas de envio de senha.
            </td>
        </tr>-->
        <tr>
            <td colspan="2" align="center">
                <br>
                <?php
                if ($msg_erro != "") {
                    echo "<font color=red>".$msg_erro."</font>";
                } else {
                    echo "<font color=green>".$msg."</font>";
                }
                ?>
            </td>
        </tr>    
    </table>
</form>

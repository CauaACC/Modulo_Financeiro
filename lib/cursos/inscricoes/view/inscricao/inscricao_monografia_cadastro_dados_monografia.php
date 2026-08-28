<?php
#require_once(dirname(__FILE__)."/../../include/inscricao_request_variaveis.php");

?>

<script language="JavaScript1.2" src="resources/js/default.js"></script>

<link rel="stylesheet" type="text/css" href="resources/css/redeunida.css">

<?php
#Includes
require_once(dirname(__FILE__)."/../../include/config.php");

#Fazendo conexão com o banco
$Conexao_db = FWA_Conexao("OpcTec");

?>

<SCRIPT LANGUAGE="JAVASCRIPT">
//------------------------------------------------------------------------

function criticar() {

  if (document.form.autor_nome.value == ""){
      alert ("O campo Nome do Autor não está preenchido!");
      document.form.autor_nome.focus();
      return false ;
  }

  // testa se o email foi preenchido e se está no formato correto
  if (!isEmail(document.form.autor_e_mail.value)) {
    alert ("O email deve ser preenchido no formato correto (usuário@dominio).");
    document.form.autor_e_mail.focus();
    return false;
  }
  
  if (document.form.autor_pseudonimo.value == ""){
      alert ("O campo PseudÃ´nimo do Autor não está preenchido!");
      document.form.autor_pseudonimo.focus();
      return false ;
  }



  if (document.form.instituicao_monografia.value == ""){
      alert ("O campo Instituição da Monografia não está preenchido!");
      document.form.instituicao_monografia.focus();
      return false ;
  }

  if (document.form.titulo_monografia.value == ""){
      alert ("O campo Titulo Monografia não está preenchido!");
      document.form.titulo_monografia.focus();
      return false ;
  }

  if (document.form.arquivo.value == ""){
      alert ("Ã necessário anexar o arquivo da monografia!");
      document.form.arquivo.focus();
      return false ;
  }

}
// -->

</SCRIPT>

</HEAD>
<BODY>

<FORM NAME="form" METHOD="POST" ACTION="index.php"  enctype="multipart/form-data" onsubmit="return criticar();">

     <br><br>
   <blockquote>
   <table width='520' border='1' bgcolor='FFFFFF' CELLSPACING='0' CELLPADDING='10'><tr><td>
     <FONT FACE='verdana' SIZE='3'>
       <B>Envio de Monografia - Dados da Monografia</B>
     </FONT>
     <br><br>


<?php
echo "<blockquote>";
require_once('inscricao_monografia_consulta_premiacao_cientifica.php');
echo "</blockquote>";

If (@$Selecionou_Curso==1){

echo <<<corpo
      <fieldset width='500'><legend> Dados da Monografia </legend>

      <TABLE WIDTH='500' BORDER='0' CELLSPACING='0' CELLPADDING='4'>

        <TR>
          <TD VALIGN='top'>
              Nome do Autor:
          </TD>
        </TR>
        <TR>
          <TD VALIGN='top'>
            <INPUT CLASS='form_campos' TYPE='text' NAME='autor_nome' value='{$_SESSION['autor_nome']}' SIZE='60' maxlength='255' onBlur='trim(this);' onkeypress='javascript: return isAlfa(window.event.keyCode);'>
          </TD>
        </TR>
        <TR>
          <TD VALIGN='top'>
              E-mail do Autor:
          </TD>
        </TR>
        <TR>
          <TD VALIGN='top'>
            <INPUT CLASS='form_campos' TYPE='text' NAME='autor_e_mail' value='{$_SESSION['autor_e_mail']}' SIZE='60' maxlength='255' onBlur='trim(this);'>
          </TD>
        </TR>

        <TR>
          <TD VALIGN='top'>
              PseudÃ´nimo do Autor:
          </TD>
        </TR>
        <TR>
          <TD VALIGN='top'>
            <INPUT CLASS='form_campos' TYPE='text' NAME='autor_pseudonimo' value='{$_SESSION['autor_pseudonimo']}' SIZE='60' maxlength='255' onBlur='trim(this);'>
          </TD>
        </TR>

        <TR>
          <TD VALIGN='top'>
              Instituição:<br>
          </TD>
        </TR>
        <TR>
          <TD VALIGN='top'>
            <INPUT CLASS='form_campos' TYPE='text' NAME='instituicao_monografia' value='{$_SESSION['instituicao_monografia']}' SIZE='60' maxlength='255' onBlur='trim(this);' onkeypress='javascript: return isAlfa(window.event.keyCode);'>
          </TD>
        </TR>
        <TR>
          <TD VALIGN='top'>
              Titulo da Monografia:
          </TD>
        </TR>
        <TR>
          <TD VALIGN='top'>
            <INPUT CLASS='form_campos' TYPE='text' NAME='titulo_monografia' value='{$_SESSION['titulo_monografia']}' SIZE='60' maxlength='255' onBlur='trim(this);'>
          </TD>
        </TR>

        <TR>
          <TD VALIGN='top'>
              Anexar Monografia:
          </TD>
        </TR>
        <TR>
          <TD VALIGN='top'>
            <INPUT CLASS='form_campos' TYPE='file' NAME='arquivo' SIZE='30' maxlength='255' value='{$_SESSION['arquivo']}'>
          </TD>
        </TR>





        <TR>
          <TD COLSPAN='2'>&nbsp;</TD>
        </TR>
       </TABLE>

       <TABLE BORDER='0' ALIGN='CENTER'>
        <TR BGCOLOR='#FFFFFF'  VALIGN='middle'>
          <TD  VALIGN='middle'>
              <INPUT TYPE="hidden" NAME="content" VALUE="view/inscricao/inscricao_monografia_confirmar.php">
              <INPUT TYPE='hidden' NAME='acao'    VALUE='Inserir'>
              <INPUT TYPE=SUBMIT   VALUE=Avançar>
              <INPUT TYPE=RESET    VALUE=Limpar>
            </FORM>
          </TD>

          <TD VALIGN='middle'>
            <FORM NAME='exit' METHOD='POST' ACTION='index.php'>
               <INPUT TYPE='hidden' VALUE='inscricao_monografia_seleciona_premiacao_cientifica.php' NAME='content'>
               <INPUT TYPE='hidden' VALUE='Voltar' NAME='acao'>
               <INPUT TYPE='SUBMIT' VALUE='Voltar'>
            </FORM>
          </TD>
        </TR>
       </TABLE>

corpo;
}
?>
    </td>
  </tr>
</table>

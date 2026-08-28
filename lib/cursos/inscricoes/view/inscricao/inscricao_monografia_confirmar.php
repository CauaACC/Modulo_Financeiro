<?php
#Includes
require_once(dirname(__FILE__)."/../../include/config.php");

#Fazendo conexão com o banco
$Conexao_db = FWA_Conexao("OpcTec");

#require_once(dirname(__FILE__)."/../../include/inscricao_request_variaveis.php");


echo "
   <blockquote>
   <table width='520' border='1' bgcolor='FFFFFF' CELLSPACING='0' CELLPADDING='10'><tr><td>
     <FONT FACE='verdana' SIZE='3'>
       <B>Envio de Monografia - Confirmar Dados da Monografia</B>
     </FONT>
     <br><br>
     ";

#-----------------------------------------------------------------------------------------
#--------------------------------------------------------------
#Inicio Dados da Area Tematica

echo "<fieldset width='500'><legend> Opção de Premiação Científica </legend><blockquote>";
require_once('inscricao_monografia_consulta_premiacao_cientifica.php');
echo "</fieldset>";
#-----------------------------------------------------------------------------------------
require_once('inscricao_monografia_consulta_dados_monografia.php');
#--------------------------------------------------------------
require_once('inscricao_monografia_upload.php');
#--------------------------------------------------------------
#Inicio Concluir Inscrição
?>
<script language="JavaScript1.2" src="resources/js/inscricao_monografia.js"></script>

<?php
echo <<<corpo
<fieldset width='500'><legend> Concluir Inscrição </legend><blockquote>

       <TABLE BORDER='0' ALIGN='CENTER' WIDTH='400'>
        <TR BGCOLOR='#FFFFFF'  VALIGN='middle'>
          <TD  VALIGN='middle'>

<FORM NAME='form' METHOD='POST' ACTION='inscricao_monografia_grava.php'  onsubmit='return criticar_tudo();'>
        <INPUT TYPE='hidden' VALUE='Inserir' NAME='acao'>
        <INPUT TYPE='SUBMIT' VALUE='Confirmar Envio de Monografia'>
corpo;

  $FWS_Campos = array(
                       "cod_premiacao_cientifica"
                      ,"cod_inscrito"
                      ,"autor_nome"
                      ,"autor_e_mail"
                      ,"autor_pseudonimo"
                      ,"titulo_monografia"
                      ,"instituicao_monografia"
                      );


  #ECHO $FWS_Campos."<BR>";
  For ($ii=0;$ii<count($FWS_Campos);$ii++){
    If($_SESSION[$FWS_Campos[$ii]]==" "){
      $_SESSION[$FWS_Campos[$ii]]="";
    }
    #echo "<br>."$FWS_Campos[$ii]." -> ";
    echo "<input type='hidden' name='".$FWS_Campos[$ii]."' value='".$_SESSION[$FWS_Campos[$ii]]."'>";
  }
    echo "<input type='hidden' name='arquivo_upload' value='".$arquivo_name."'>";

echo <<<corpo
        </FORM>

      </TD>
       <TD VALIGN='middle'>

        <FORM NAME='exit' METHOD='POST' ACTION='index.php'>
           <INPUT TYPE='hidden' VALUE='inscricao_monografia_cadastro_dados_monografia.php' NAME='content'>
           <INPUT TYPE='hidden' VALUE='Voltar' NAME='acao'>
           <INPUT TYPE='SUBMIT' VALUE='Editar Monografia'>
        </FORM>

      </TD>
    </TR>
   </TABLE>

</blockquote></fieldset>
corpo;

#--------------------------------------------------------------
#--------------------------------------------------------------
#echo $Selecionou_Curso;
echo "</td></tr></table></blockquote>";
?>


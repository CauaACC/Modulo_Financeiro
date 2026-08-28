<?php
require_once(dirname(__FILE__)."/../../include/config.php");
#require_once(dirname(__FILE__)."/../../include/inscricao_request_variaveis.php");
#Fazendo conexão com o banco
$Conexao_db = FWA_Conexao("OpcTec");
?>

   <blockquote>
   <table width='520' border='1' bgcolor='FFFFFF' CELLSPACING='0' CELLPADDING='10'><tr><td>
     <FONT FACE='verdana' SIZE='3'>
       <B>Envio de Trabalho - Confirma Dados</B>
     </FONT>
     <br><br>
<?php  
echo "<fieldset width='500'><legend> Área Temática para Apresentação do Trabalho </legend><blockquote>";
require_once('inscricao_trabalhos_consulta_area_tematica.php');
echo "</blockquote></fieldset>";
#-----------------------------------------------------------------------------------------
require_once('inscricao_trabalhos_consulta_dados_trabalho.php');
#--------------------------------------------------------------
#--------------------------------------------------------------
#Inicio Concluir Inscrição
?>
<script language="JavaScript1.2" src="resources/js/inscrito_login.js"></script>

<?php
echo <<<corpo
<fieldset width='500'><legend> Concluir Inscrição </legend><blockquote>

       <TABLE BORDER='0' ALIGN='CENTER' WIDTH='400'>
        <TR BGCOLOR='#FFFFFF'  VALIGN='middle'>
          <TD  VALIGN='middle'>

<FORM NAME='form' METHOD='POST' ACTION='inscricao_trabalhos_grava.php'  onsubmit='return criticar_tudo();'>
        <INPUT TYPE='hidden' VALUE='Inserir' NAME='acao'>
        <INPUT TYPE='SUBMIT' VALUE='Confirmar Envio de Trabalho'>
corpo;

  $FWS_Campos = array(
                       "cod_area_tematica"
                      ,"nome_inscrito"
                      ,"cod_inscrito"
                      ,"cpf"
                      ,"e_mail"
                      ,"autor_apresentador_nome"
                      ,"autor_apresentador_e_mail"
                      ,"titulo_trabalho"
                      ,"instituicao_trabalho"
                      ,"palavras_chave"
                      ,"resumo_trabalho");


  #ECHO $FWS_Campos."<BR>";
  For ($ii=0;$ii<count($FWS_Campos);$ii++){
    If($_SESSION[$FWS_Campos[$ii]]==" "){
      $_SESSION[$FWS_Campos[$ii]]="";
    }
    #echo "<br>."$FWS_Campos[$ii]." -> ";
    echo "<input type='hidden' name='".$FWS_Campos[$ii]."' value='".$_SESSION[$FWS_Campos[$ii]]."'>";
  }

echo <<<corpo
</FORM>

</TD>

<TD VALIGN='middle'>

<FORM NAME='exit' METHOD='POST' ACTION='index.php'>
   <INPUT TYPE='hidden' VALUE='inscricao_trabalhos_cadastro_dados_trabalho.php' NAME='content'>
   <INPUT TYPE='hidden' VALUE='Voltar' NAME='acao'>
   <INPUT TYPE='SUBMIT' VALUE='Editar Trabalho'>
</FORM>

          </TD>
        </TR>
       </TABLE>

</blockquote></fieldset>
corpo;

#--------------------------------------------------------------
#--------------------------------------------------------------

echo "</td></tr></table></blockquote>";
?>

<?php
require_once(dirname(__FILE__)."/../../include/config.php");
#require_once(dirname(__FILE__)."/../../include/inscricao_request_variaveis.php");

#Fazendo conexão com o banco
$Conexao_db = FWA_Conexao("OpcTec");
?>
<h3>Área do Inscrito</h3>  
<div class="panel-group">
    <div class="panel panel-default">
        <div class="panel-heading"><h4>Envio de Trabalho - Ficha de Cadastro</h4></div>
        <div class="panel-body">
            <FORM NAME="form" METHOD="POST" ACTION="view/inscricao/inscricao_trabalhos_grava.php"  onsubmit="return criticar();">

            <?php require_once('inscricao_trabalhos_consulta_area_tematica.php'); ?>
        </div>
    </div>
    <?php If (@$Selecionou_Curso == 1) { ?>
    <div class="panel panel-default">
        <div class="panel-heading"><h4>Dados do Trabalho</h4></div>
        <div class="panel-body">  

            <label for="titulo_trabalho">Titulo do Trabalho:</label>
            <input type="text" id="titulo_trabalho" name="titulo_trabalho" value="<?php echo  $_SESSION['titulo_trabalho'] ?>"
                   class="form-control" placeholder="Titulo do Trabalho" required autofocus>
            <br>

            <label >Nomes dos Co-Autores: </label> (máximo 5 co-autores)
            <input type="text" id="nome_co_autor_1" name="nome_co_autor_1" value="<?php echo  $_SESSION['nome_co_autor_1'] ?>"
                   class="form-control" placeholder="1º Co-autor">

            <label for="nome_co_autor_2" class="sr-only">2º Co-autor</label>
            <input type="text" id="nome_co_autor_2" name="nome_co_autor_2" value="<?php echo  $_SESSION['nome_co_autor_2'] ?>"
                   class="form-control" placeholder="2º Co-autor">

            <label for="nome_co_autor_3" class="sr-only">3º Co-autor</label>
            <input type="text" id="nome_co_autor_3" name="nome_co_autor_3" value="<?php echo  $_SESSION['nome_co_autor_3'] ?>"
                   class="form-control" placeholder="3º Co-autor">

            <label for="nome_co_autor_4" class="sr-only">4º Co-autor</label>
            <input type="text" id="nome_co_autor_4" name="nome_co_autor_4" value="<?php echo  $_SESSION['nome_co_autor_4'] ?>"
                   class="form-control" placeholder="4º Co-autor">

            <label for="nome_co_autor_5" class="sr-only">5º Co-autor</label>
            <input type="text" id="nome_co_autor_5" name="nome_co_autor_5" value="<?php echo  $_SESSION['nome_co_autor_5'] ?>"
                   class="form-control" placeholder="5º Co-autor">
            <br>
            <label>Instituição:</label>
            <input type="text" id="instituicao_trabalho" name="instituicao_trabalho" value="<?php echo  $_SESSION['instituicao_trabalho'] ?>"
                   class="form-control" placeholder="Instituição" required autofocus>

            <br>
            <label>Palavras-chave: </label> (máximo 3 palavras-chave, separadas por um sinal de 'ponto e virgula', ou seja ';')
            <input type="text" id="palavras_chave" name="palavras_chave" value="<?php echo  $_SESSION['palavras_chave'] ?>"
                   class="form-control" placeholder="Palavras chave" required autofocus>             

            <br>
            <label>Resumo: </label> (máximo 2100 caracteres, com espaços)
            <br><font id=Digitado color=red>0</font> Caracteres digitados &nbsp; ==> &nbsp; restam <font id=Restante color=red>2100</font>
            <textarea id="resumo_trabalho" name="resumo_trabalho" rows='15' cols='80' maxlength='2100'
                      onkeyup='MaxCaracter(this, 2100)' onkeypress='MaxCaracter(this, 2100)'
                      class="form-control" placeholder="Resumo Trabalho" required autofocus><?php echo  $_SESSION['resumo_trabalho'] ?></textarea>

            <input type="hidden" value="Inserir" name="acao">
                      
            <!--<div class='btn-group'>-->
                <button class='btn btn-primary' type="submit">Confirmar Envio de Trabalho</button>
                <a href="index.php?content=view/inscricao/inscricao_trabalhos_cadastro_dados_trabalho.php"
                   class="btn btn-sx btn-primary" role="button">Editar</a>
            <!--</div>-->
            <?php
        }
        ?>
        </div>
    </div>
</div>
<script language="JavaScript1.2" src="resources/js/default.js"></script>
<SCRIPT LANGUAGE="JAVASCRIPT">
<!--
function criticar() {

    if (document.form.autor_apresentador_nome.value == "") {
        alert("O campo Nome Autor Apresentador não está preenchido!");
        document.form.autor_apresentador_nome.focus();
        return false;
    }

    // testa se o email foi preenchido e se está no formato correto
    if (!isEmail(document.form.autor_apresentador_e_mail.value)) {
        alert("O email deve ser preenchido no formato correto (usuário@dominio).");
        document.form.autor_apresentador_e_mail.focus();
        return false;
    }

    if (document.form.instituicao_trabalho.value == "") {
        alert("O campo Instituição do trabalho não está preenchido!");
        document.form.instituicao_trabalho.focus();
        return false;
    }

    if (document.form.titulo_trabalho.value == "") {
        alert("O campo Titulo Trabalho não está preenchido!");
        document.form.titulo_trabalho.focus();
        return false;
    }

    if (document.form.palavras_chave.value == "") {
        alert("O campo Palavras-Chave não está preenchido!");
        document.form.palavras_chave.focus();
        return false;
    }

    if (document.form.resumo_trabalho.value == "") {
        alert("O campo Resumo Trabalho não está preenchido!");
        document.form.resumo_trabalho.focus();
        return false;
    }

    return true;
}
// -->
</SCRIPT>
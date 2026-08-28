<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/oficina-cabecalho.php");
require_once(dirname(__FILE__)."/../../view/admin/oficina-cadastro-busca.php");

?>
<hr size='1' width='100%'>
<FONT SIZE=2 FACE=VERDANA> <B> Ficha de Cadastro de Atividades</B></FONT><br>
<FONT SIZE=1 FACE=VERDANA> <B> * Campos Obrigatórios</B></FONT>
<FONT SIZE=1 FACE=VERDANA>

<FORM method="POST" name="form" action="view/admin/oficina-cadastro-incluir-grava.php" onsubmit="return criticar()">

<fieldset width=570><legend> Dados da Atividade </legend>

<?php
$camposEditar = true;
$objOficina= new Oficina();
include_once("oficina-cadastro-campos.php");
?>

</fieldset>

<table border=0 width=565>
<tr><td colspan=2 align=left>
        <br><br>
        <input type='submit' value='Salvar' class='button btn btn-primary btn-xs'>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <input type='reset' value='Limpar' class='button btn btn-primary btn-xs'>
</td></tr>
</table>

</FORM>
</div></div></div></div>

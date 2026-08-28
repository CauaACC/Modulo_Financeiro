<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/atuacoes-convidados-seguranca.php");

require_once(dirname(__FILE__)."/../../view/admin/atuacoes-convidados-cabecalho.php");
require_once(dirname(__FILE__)."/../../view/admin/atuacoes-convidados-cadastro-busca.php");

?>
<hr size='1' width='100%'>
<FONT SIZE=2 FACE=VERDANA> <B> Ficha de Cadastro de Atuações dos Convidados</B></FONT><br>
<FONT SIZE=1 FACE=VERDANA> <B> * Campos Obrigatórios</B></FONT>
<FONT SIZE=1 FACE=VERDANA>

<FORM method="POST" name="form" action="view/admin/atuacoes-convidados-cadastro-incluir-grava.php" onsubmit="return criticar()">

<fieldset width=570><legend> Dados da Atuação do Convidado </legend>

<?php
include_once("atuacoes-convidados-cadastro-campos.php");
?>

</fieldset>

<table border=0 width=565>
<tr><td colspan=2 align=center>
      <br><br>
      <input type='submit' value='Cadastrar'> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='reset' value='Limpar'>
</td></tr>
</table>
</FORM>
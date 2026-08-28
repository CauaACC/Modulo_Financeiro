<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/perfil-cabecalho.php");
require_once(dirname(__FILE__)."/../../view/admin/perfil-cadastro-busca.php");
FWA_Mostrar_Mensagem($_SESSION['MSG']);
?>
<hr size='1' width='100%'>
<FONT SIZE=2 FACE=VERDANA> <B> Ficha de Cadastro de Perfis</B></FONT><br>
<FONT SIZE=1 FACE=VERDANA> <B> * Campos Obrigatórios</B></FONT>
<FONT SIZE=1 FACE=VERDANA>

<FORM method="POST" name="form" action="view/admin/perfil-cadastro-incluir-grava.php" onsubmit="return criticar()">

<fieldset width=570><legend> Dados da Perfil </legend>

<?php
$objPerfil = new Perfil();
include_once("perfil-cadastro-campos.php");
?>

</fieldset>

<table border=0 width=565>
<tr><td colspan=2 align=center>
      <br><br>
      <input type='submit' value='Salvar'> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='reset' value='Limpar'>
</td></tr>
</table>

</FORM>
</div></div></div></div>
</body>
</html>

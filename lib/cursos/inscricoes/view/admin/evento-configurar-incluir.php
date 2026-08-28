<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/evento-cabecalho.php");
require_once(dirname(__FILE__)."/../../view/admin/evento-configurar-busca.php");
?>

<hr size='1' width='100%'>
<FONT SIZE=2 FACE=VERDANA> <B> Ficha de Cadastro</B></FONT><br>
<FONT SIZE=1 FACE=VERDANA> <B> * Campos Obrigatórios</B></FONT>
<FONT SIZE=1 FACE=VERDANA>

<FORM method="POST" name="form" action="view/admin/evento-configurar-incluir-grava.php" onsubmit="return criticar()">

<fieldset width=570><legend> Configuração do Evento</legend>

<?php
include_once("evento-configurar-campos.php");
?>

</fieldset>

<!-- Botoes no padrao da aplicacao (modalBtnSalvar/modalBtnCancelar). -->
<div style="text-align:center;margin:24px 0;">
    <button type="submit" class="btn btn-primary modalBtnSalvar">
        <i class="fa fa-check"></i> Salvar
    </button>
    &nbsp;&nbsp;
    <button type="reset" class="btn btn-default modalBtnCancelar">Limpar</button>
</div>

</FORM>

</div></div></div></div>
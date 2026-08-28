<?php
require_once(dirname(__FILE__)."/../../include/config.php");

require_once(dirname(__FILE__)."/../../view/admin/inscritos-cabecalho.php");

require_once(dirname(__FILE__)."/../../view/admin/inscritos-cadastro-busca.php");

?>
<?php /* Mesma moldura da tela de alteracao: os campos vem do mesmo
         inscritos-cadastro-campos.php, entao incluir e alterar tem que ser a
         mesma ficha. */ ?>
<SCRIPT LANGUAGE="JavaScript1.2" src="resources/js/inscritos-cadastro.js"></SCRIPT>

<FORM method="POST" name="form" action="view/admin/inscritos-cadastro-incluir-grava.php"  onsubmit="return criticarConvidado()">

<?php
/* Um jogo de botoes so, o mesmo em cima e embaixo. */
$BOTOES_FICHA = '
        <button type="submit" class="btn btn-primary btn-sm">
            <i class="glyphicon glyphicon-ok"></i> Cadastrar</button>
        <input type="reset" class="btn btn-default btn-sm" value="Limpar">
        <button type="button" class="btn btn-default btn-sm" onclick="history.back()">Voltar</button>';
?>
<div class="dp-cab">
    <span>Nova inscrição</span>
    <span class="dp-cab-acoes"><?php echo $BOTOES_FICHA; ?></span>
</div>
<div style="font-size:11px; color:#999; margin-bottom:4px;">* Campos obrigat&oacute;rios</div>

<?php
$liberarCadastroSemCpf = true;
include_once("inscritos-cadastro-campos.php");
?>

<div class="dp-cab" style="border-bottom:0; border-top:1px solid #eee;
                           padding-top:10px; margin-top:14px;">
    <span></span>
    <span class="dp-cab-acoes"><?php echo $BOTOES_FICHA; ?></span>
</div>

</FORM>

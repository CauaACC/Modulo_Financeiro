<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/consulta-dados-congresso-seguranca.php");

require_once(dirname(__FILE__)."/../../view/admin/titulo_area_admin.php");
require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_administracao.php");
require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_financeiro.php");

$objCB = new GerenciarCargaBoletoAC();
?>
<div class="panel-group">
<div class="panel panel-default">
    <div class="panel-heading"><h4>Carga de Boletos Pagos</h4></div>
    <div class="panel-body">
    <?php
    $objCB->carregarBoletos($fws_, $debug);
    ?>
<table>
<?php if($erro or $certo){?>
    <tr><td><div class="erro"><br><?php echo $erro; ?></div><strong><br><?php echo $certo; ?></strong></td></tr>
<?php }?>
<tr>
    <td>
    <fieldset><legend>Arquivo Retorno</legend>
    <table>
    <form action="" name="f_arquivo" method="post" enctype="multipart/form-data">
    <tr>
            <td><input type="file" name="ed_arquivo"></td>
            <td align="center"><input type="submit" name="btn_envia" value="Enviar"></td>
    </tr>
    </form>
    </table>
    </fieldset>
    </td>
</tr>
</table>
<br>
<table>
    <tr><td><?php echo $nomeArquivo["name"]?></td></tr>
    <?php
    if($listaValor){
    foreach($listaValor as $numero => $valor){
        $valor = str_replace(".", ",", $valor)?>
        <tr>
            <td>Número do Boleto: <?php echo $numero?>&nbsp;&nbsp;&nbsp;&nbsp;</td>
            <td>Valor do Boleto: R$<?php echo $valor?></td>
        </tr>
    <?php
    }}
    ?>
</table>
<br>
<table>
    <tr><td><b>Lista de arquivos já carregados</b></td></tr>
    <?php
        $objCB->listarArquivosCarregados($fws_, $debug);
    ?>
</table>
</div>
</div>
</div>
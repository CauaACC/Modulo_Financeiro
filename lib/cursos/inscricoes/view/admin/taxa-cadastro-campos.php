<!-- <SCRIPT LANGUAGE="JavaScript1.2" src="resources/js/taxa-cadastro.js"></SCRIPT> -->

<?php
if($status==''){
  $status='ATIVADA';
}
if($cod_perfil==''){
  $cod_perfil=$fwsGet_['cod_perfil'];
}
?>
<table border='0' width='570'  cellspacing="10" cellpadding="10" class="dataTable">
<tr><td>&nbsp; Status:           </td>
    <td>
        <select name='STATUS' id='status'>
            <option selected value='-1'>Alterar opção</option>
            <option <?php echo verifItemSelected($status, 'ATIVADA')  ?> value='ATIVADA'>ATIVADA</option>
            <option <?php echo verifItemSelected($status, 'DESATIVA') ?> value='DESATIVA'>DESATIVA</option>
        </select>
    </td>
</tr>

<tr><td>&nbsp; Perfil: </td>
    <td>
        <select name='cod_perfil' id='cod_perfil'>
            <?php //echo '-'.$codPerfil.'-';?>
            <option selected value='-1'>Alterar opção</option>
            <?php
            $objPerfil = new Perfil();
            $objPerfil->carregarListaPerfilOptions($debug);
            foreach ($objPerfil->getListaPerfil() as $objL) {
                echo "<option ". verifItemSelected($objL->getCodPerfil(),$cod_perfil)." value='".$objL->getCodPerfil()."'>".$objL->getTitulo()."</option>";
            }
            ?>
        </select>
    </td></tr>
<tr><td>&nbsp; Titulo:  </td><td> <input type='text' name='titulo' value='<?php echo $titulo; ?>' size='60' maxlength='255' onBlur="trim(this);" ></td></tr>
<tr><td>&nbsp; Valor:           </td><td> <input type='text' name='tx_unica_a_vista_valor' id='tx_unica_a_vista_valor' value='<?php echo $tx_unica_a_vista_valor; ?>' size='20' maxlength='255' onBlur="trim(this);"></td></tr>
<tr><td colspan='2'>
    &nbsp; Período Inicial: 
    <input type='datetime-local' name='periodo_inicial' id='periodo_inicial'  value='<?php echo formatDateHoraTrans($periodo_inicial,'YYYY-MM-DD HH:II:SS','YYYY-MM-DDTHH:II'); ?>'        size='20' maxlength='255' onBlur="trim(this);"></td></tr>
<tr><td colspan='2'>
    &nbsp; Período Final:
    <input type='datetime-local' id='periodo_final' name='periodo_final' id='periodo_final' value='<?php echo formatDateHoraTrans($periodo_final,'YYYY-MM-DD HH:II:SS','YYYY-MM-DDTHH:II'); ?>'          size='20' maxlength='255' onBlur="trim(this);"></td></tr>
<tr><td colspan='2'>
    &nbsp; Qtd Dias para calculo de vencimento: 
    <input type='number' id='dias_vencimento' name='dias_vencimento'  value='<?php echo $dias_vencimento; ?>' size='20' maxlength='255' onBlur="trim(this);"></td></tr>
<tr><td colspan='2'>
    &nbsp; Max Dat. Vencimento: 
    <input type='date' id='max_data_vencimento' name='max_data_vencimento'  value='<?php echo $max_data_vencimento; ?>' size='20' maxlength='255' onBlur="trim(this);"></td></tr>
<tr><td colspan=2>&nbsp; Observação: </td></tr>
<tr><td colspan=2>&nbsp; <textarea rows=5 cols=70 name='observacao' size='100' onBlur='trim(this);' onkeypress='javascript: return isAlfa(window.event.keyCode);' class='textob'><?php echo $OBSERVACAO; ?></textarea></td></tr>
</table>

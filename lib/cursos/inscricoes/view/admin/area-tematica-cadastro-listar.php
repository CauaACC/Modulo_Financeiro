<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/area-tematica-cabecalho.php");
?>
<?php
/*
<fieldset>
    <!--
    <ul class="nav nav-tabs">
        <li class="active"><a href="#"><b>Localizar Cadastrados</b></a></li>
        <?php if ($objEvConf->getTrabSubmissao()==1) { ?>
        <li><a href="<?php echo WB_URL_VIEW?>?content=view/admin/consulta-dados-congresso-listagem-trabalhos.php">Localizar Trabalhos </a></li>
        <?php } ?>
        <?php if (COD_EVENTO == 1) { ?>
        <li><a href="<?php echo WB_URL_VIEW?>?content=view/admin/consulta-dados-congresso-listagem-trabalhos-premiacao.php">Definir Premiações</a></li>
        <?php } ?>
    </ul>
        -->
    <br>
    <form method="post" id="formListaInscritos" action="<?php echo WB_URL_VIEW?>?content=view/admin/consulta-dados-congresso-listagem-inscritos.php#rsltdoListaInscritos">
    </form>
</fieldset>
*/
?>
<hr size='1' width='100%'>

  <table class="table table-hover table-condensed tablesorter">
    <thead>
      <tr>
        <th>Ação</th>
        <th>Titulo da Atividade</th>
        <th>Ordem</th>
        <th>Status</th>
        <!-- <th>Recebendo<br>Inscrição</th>
        <th>Vagas<br>Limitadas</th>-->
        <th>Total<br>Inscritos/Vagas</th>
        <!--<th>Valor<br>Individual</th>
        <th>Liberar<br>Certificado</th> -->
      </tr>
    </thead>
    <tbody>
<?php
try {

    $objAreaTematica = new AreaTematica();
    if(in_array($objEv->getCodEvento(), array(35,52,72))){
        $orderBy = ' data_inicio, ordem, cod_area_tematica, titulo ';
        $agruparData = true;
    }else{
        $orderBy = ' ordem, cod_area_tematica, titulo ';
        $agruparData = false;
    }
    $objAreaTematica->carregarListaAreaTematica($orderBy,false);
    $totalAtividade = 0;
    $totalInscritos = 0;
    foreach ($objAreaTematica->getListaAreasTematicas() as $objL) {
        $totalAtividade = $totalAtividade+1;
        $totalInscritos = $totalInscritos + $objL->getTotalInscritos();
        $classText = 'txtPadrao';
        if($agruparData){
            if($dataHoraAnt != $objL->getDataInicio().$objL->getHorarioIni()){
                echo "<tr valign='middle' bgcolor='{$bgcolor}'> "
                . "<td width='100%' colspan='9' style='padding: 4px 4px 6px 4px;'>"
                . "<h5>".formatDateView($objL->getDataInicio())." - "
                . "{$objL->getHorarioIni()}</h5>"
                . "</td></tr>";
            }
            $dataHoraAnt = $objL->getDataInicio().$objL->getHorarioIni();
        }
        ?>
        <tr data-id="<?php echo $totalAtividade ?>"> 
        <td width="160px" >
            <a title="Alterar Atividade <?php echo numberPad($objL->getCodAreaTematica(),2) ?>" href="<?php echo WB_URL_VIEW?>?content=view/admin/area-tematica-cadastro-alterar.php&COD_AREA_TEMATICA=<?php echo $objL->getCodAreaTematica()?>" class="button btn btn-default btn-xs" target="_blank">
            <span class="glyphicon glyphicon-folder-open"></span></a>

            <a title="Excluir Atividade <?php echo numberPad($objL->getCodAreaTematica(),2) ?>" href="<?php echo WB_URL_VIEW?>?content=view/admin/area-tematica-cadastro-excluir-grava.php&COD_AREA_TEMATICA=<?php echo $objL->getCodAreaTematica()?>" class="button btn btn-default btn-xs" onclick="return confirm('Você tem certeza que deseja excluir esta atividade?');">
            <span class="glyphicon glyphicon-trash"></span></a>

            <a title="Listar inscritos nesta atividade" href="<?php echo WB_URL_VIEW?>?content=view/admin/consulta-dados-congresso-listagem-trabalhos.php&cod_area_tematica=<?php echo $objL->getCodAreaTematica() ?>" class="button btn btn-default btn-xs" target="_blank">
            <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 640 512"><!--! Font Awesome Free 6.4.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. --><path d="M144 0a80 80 0 1 1 0 160A80 80 0 1 1 144 0zM512 0a80 80 0 1 1 0 160A80 80 0 1 1 512 0zM0 298.7C0 239.8 47.8 192 106.7 192h42.7c15.9 0 31 3.5 44.6 9.7c-1.3 7.2-1.9 14.7-1.9 22.3c0 38.2 16.8 72.5 43.3 96c-.2 0-.4 0-.7 0H21.3C9.6 320 0 310.4 0 298.7zM405.3 320c-.2 0-.4 0-.7 0c26.6-23.5 43.3-57.8 43.3-96c0-7.6-.7-15-1.9-22.3c13.6-6.3 28.7-9.7 44.6-9.7h42.7C592.2 192 640 239.8 640 298.7c0 11.8-9.6 21.3-21.3 21.3H405.3zM224 224a96 96 0 1 1 192 0 96 96 0 1 1 -192 0zM128 485.3C128 411.7 187.7 352 261.3 352H378.7C452.3 352 512 411.7 512 485.3c0 14.7-11.9 26.7-26.7 26.7H154.7c-14.7 0-26.7-11.9-26.7-26.7z"/></svg></a>
        </td>
        <td width="450px">
            <a class='link' title="Listar inscritos nesta atividade" href='<?php echo WB_URL_VIEW?>?content=view/admin/consulta-dados-congresso-listagem-inscritos.php&cod_area_tematica=<?php echo $objL->getCodAreaTematica() ?>'>
                <span style="font-size:14px;"><?php echo $objL->getTitulo() ?></span></a>
        </td>
        <td ><?php echo numberPad($objL->getOrdem(),2) ?></td>
        <td ><?php echo ($objL->getDesStatus()=='D')? "<font color=red>".$objL->getDesStatus()."</font>" : $objL->getDesStatus(); ?></td>
        <!--<td >
            <?php
            if($objL->getReceberInscricao()==1){
                if($objL->getNumeroVagas()<=$objL->getTotalInscritos() and $objL->getNumeroVagas()!='' and $objL->getNumeroVagas()!=0){
                    echo "<font color=red>Esgotada</font>";
                }else{
                    if ((formatDateTimeSemSeparador($objL->getDataFimInscricao()) < Date("YmdHis")) and ($objL->getDataFimInscricao()!='')){
                        echo "<font color=red>Encerrada</font>";
                    }else{
                        if($objL->getStatusInscricao()=='ATIVADA'){
                            echo 'Sim';
                        }else{
                            echo ($objL->getStatusInscricao()=='Não')? "<font color=red>".$objL->getStatusInscricao()."</font>" : $objL->getStatusInscricao();
                        }
                    }
                }
            }else{
                echo ($objL->getDesReceberInscricao()=='Não')? "<font color=red>".$objL->getDesReceberInscricao()."</font>" : $objL->getDesReceberInscricao();
            }
            ?>
        </td>-->
        <!-- <td>
            <?php
            if(intval($objL->getNumeroVagas())!=''){
                echo 'Sim';
            }else{
                echo 'Não';
            }
            ?>
        </td>-->
        <td >
            <a class='link' title="Listar inscritos nesta atividade" href='<?php echo WB_URL_VIEW?>?content=view/admin/consulta-dados-congresso-listagem-trabalhos.php&cod_area_tematica=<?php echo $objL->getCodAreaTematica() ?>'>
            <?php
                echo numberPad($objL->getTotalInscritos(),3);
            ?>
            </a>
        </td>
        <!-- <td >
            <?php
                if(intval($objL->getValorAtividade())!=''){
                    echo 'R$ '.$objL->getValorAtividade(); 
                }else{
                    echo 'Não';
                }
            ?> 
        </td>-->
        <!-- <td >
            <?php echo descSimNao($objL->getLiberarCertificado()) ?>
            <?php echo $objL->getCargaHoraria()? "<br>ch: ".$objL->getCargaHoraria()."h" : ""; ?>
        </td> -->
        </tr>
        <?php
    }
    echo "<tr><td colspan=\"5\">
        Total Eixos: " . $totalAtividade . "
        <br>Total Cadastrados: " . $totalInscritos . "
    </td></tr>";
} catch (Exception $exc) {
    echo "<tr><td colspan=\"5\">" . $exc->getMessage() . "</td></tr>";
}
echo "</table>";
?>
<script>
$(document).ready(function () {
    $("#sortable-table tbody").sortable({
        handle: '.order-handle',
        update: function (event, ui) {
            // Após a reordenação, obtenha a nova ordem e envie para o servidor
            var newOrder = $(this).sortable('toArray', { attribute: 'data-id' });
            console.log(newOrder)
            // Envie a nova ordem para o servidor (usando AJAX e PHP)
//            $.ajax({
//                type: "POST",
//                url: "update_order.php", // Substitua pelo caminho real do seu script PHP
//                data: { order: newOrder },
//                success: function (response) {
//                    // Trate a resposta do servidor conforme necessário
//                }
//            });
        }
    });

    // Adicione aqui código para lidar com a exclusão de linhas
    $(".delete-btn").on("click", function () {
        // Implemente a lógica de exclusão aqui
    });
});
</script>
<!--<style>
.table tbody {
    cursor: move;
}

.order-handle {
    cursor: move;
}   
</style>-->


<?php
require_once(dirname(__FILE__)."/../../include/config.php");

echo "<hr size='1' width='100%'>";
?>
  <table class="table table-hover table-condensed tablesorter">
    <thead>
      <tr>
        <th>Ação</th>
        <th>Status</th>
        <th>Ordem.</th>
        <th>Cod.</th>
        <th>Total<br>Inscritos</th>
        <th>Titulo da Taxa</th>
      </tr>
    </thead>
    <tbody>
<?php
try {

    $objTaxa = new Taxa();
    $objTaxa->carregarListaTaxa('cod_taxa desc',false);

    foreach ($objTaxa->getListaTaxa() as $objL) {
        if($dataHoraAnt != $objL->getDataIni().$objL->getHorarioIni()){
            echo "<tr valign='middle' bgcolor='{$bgcolor}'>"
            . "<td width='100%' colspan='7' style='padding: 4px 4px 6px 4px;'>"
            . "<h4>{$objL->getDataIni()} - "
            . "{$objL->getHorarioIni()}</h4>"
            . "</td></tr>";
        }
        $dataHoraAnt = $objL->getDataIni().$objL->getHorarioIni();
        ?>     
        <tr>
        <td width="400px">
            <a title="Visualizar Taxa"        href="<?php echo WB_URL_VIEW?>?content=view/admin/taxa-cadastro-ok.php&cod_taxa=<?php echo $objL->getCodTaxa()?>" class="button btn btn-default btn-xs">
            <span class="glyphicon glyphicon-eye-open"></span></a>
            <a title="Alterar Taxa" href="<?php echo WB_URL_VIEW?>?content=view/admin/taxa-cadastro-alterar.php&cod_taxa=<?php echo $objL->getCodTaxa()?>" class="button btn btn-default btn-xs">
            <span class="glyphicon glyphicon-folder-open"></span></a>
        </td>
        <td width="50px"><?php echo $objL->getDesStatus() ?></td>
        <td width="50px"><?php echo numberPad($objL->getOrdem(),2) ?></td>
        <td width="50px"><?php echo numberPad($objL->getCodTaxa(),2) ?></td>
        <td width="50px">(<?php echo numberPad($objL->getTotalInscritos(),3) ?>)</td>
        <td>
            <a class='link' title="Listar inscritos nesta taxa" href='<?php echo WB_URL_VIEW?>?content=view/admin/consulta-dados-congresso-listagem-inscritos.php&cod_taxa=<?php echo $objL->getCodTaxa() ?>'>
            <?php echo $objL->getTitulo() ?></a>
        </td>
        </tr>
        <?php
    }
} catch (Exception $exc) {
    echo "<tr><td colspan=\"5\">" . $exc->getMessage() . "</td></tr>";
}
echo "</table>";
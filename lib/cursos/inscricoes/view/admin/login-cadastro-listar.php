<?php
require_once(dirname(__FILE__)."/../../include/config.php");

echo "<hr size='1' width='100%'>";
?>
  <table class="table table-hover table-condensed tablesorter">
    <thead>
      <tr>
        <th>Ação</th>
        <th>Status</th>
        <th>Login</th>
        <th>Nome</th>
        <th>Grupos</th>
      </tr>
    </thead>
    <tbody>
<?php
try {

    $objPerfil = new Perfil();
    $objPerfil->carregarListaPerfil(' order by ordem, cod_perfil desc',$debug);
    
    foreach ($objPerfil->getListaPerfil() as $objL) {
        if($dataHoraAnt != $objL->getDataIni().$objL->getHorarioIni()){
            echo "<tr valign='middle' bgcolor='{$bgcolor}'>"
            . "<td width='100%' colspan='7' style='padding: 4px 4px 6px 4px;'>"
            . "<h4>{$objL->getDataIni()} - "
            . "{$objL->getHorarioIni()}</h4>"
            . "</td></tr>";
        }
        $dataHoraAnt = $objL->getDataIni().$objL->getHorarioIni();
        ?>     
        <tr id='<?php echo $objL->getCodPerfil()?>'>
        <td width="50px" >
<!--            <a title="Visualizar Perfil <?php echo $objL->getCodPerfil()?>"        href="<?php echo WB_URL_VIEW?>?content=view/admin/perfil-cadastro-ok.php&cod_perfil=<?php echo $objL->getCodPerfil()?>" class="button btn btn-default btn-xs">
            <span class="glyphicon glyphicon-eye-open"></span></a>-->
            <a title="Alterar Perfil <?php echo $objL->getCodPerfil()?>" href="<?php echo WB_URL_VIEW?>?content=view/admin/perfil-cadastro-alterar.php&cod_perfil=<?php echo $objL->getCodPerfil()?>" class="button btn btn-default btn-xs">
            <span class="glyphicon glyphicon-folder-open"></span></a>
            
            <a title="Excluir Perfil <?php echo numberPad($objL->getCodPerfil(),2) ?>" href="<?php echo WB_URL_VIEW?>?content=view/admin/perfil-cadastro-excluir-grava.php&cod_perfil=<?php echo $objL->getCodPerfil()?>" class="button btn btn-default btn-xs">
            <span class="glyphicon glyphicon-trash"></span></a>

        </td>
        <td>
            <a style='font-size:16px;' class='link' title="Listar inscritos nesta perfil" href='<?php echo WB_URL_VIEW?>?content=view/admin/consulta-dados-congresso-listagem-inscritos.php&cod_perfil=<?php echo $objL->getCodPerfil() ?>'>
            <?php echo $objL->getTitulo() ?></a>
            <br>Taxas/Períodos ou Lotes:
            <div style='margin-top:0px;margin-bottom: 5px;'>
            <?php
            try {
                $orderby = 'periodo_inicial, periodo_final';
                $objL->carregarListaTaxas('', $orderby, $debug);
                foreach ($objL->getListaTaxas() as $objLT) {
                    ?>
                    <br><a title="Alterar Taxa" href="<?php echo WB_URL_VIEW?>?content=view/admin/taxa-cadastro-alterar.php&cod_taxa=<?php echo $objLT->getCodTaxa()?>"
                    class="button btn btn-default btn-xs">
                    <span class="glyphicon glyphicon-folder-open"></span></a>
                    <?php echo " R$ ".$objLT->getTxUnicaAVistaValor()." - Período: ".formatDateView($objLT->getPeriodoInicial())." - ".formatDateView($objLT->getPeriodoFinal())." - Venc.: ".formatDateHoraTrans($objLT->getMaxDataVencimento(),'YYYY-MM-DD','DD/MM/YYYY')." - status: ".$objLT->getDesStatus(); ?>
                 <?php    
                }
            } catch (Exception $exc) {
                //echo $exc->getMessage();
            }
            ?>
            </div>
            <a style="margin-bottom:10px;" title="Adcionar mais uma taxa" href="<?php echo WB_URL_VIEW?>?content=view/admin/taxa-cadastro-incluir.php&cod_perfil=<?php echo $objL->getCodPerfil()?>">adc. + Taxa</a>
        </td>
        <td ><?php echo numberPad($objL->getOrdem(),2) ?></td>
        <td ><?php echo $objL->getDesStatus() ?></td>
        <td >
            <?php echo descSimNao($objL->getReceberInscricao()) ?>
            <?php
            if($objL->getVerificarInscritoApto()==true){
                ?>
                <br>
                <a style="margin-bottom:10px;" title="Gerenciar Inscritos Aptos"
               href="<?php echo WB_URL_VIEW?>?content=view/admin/GerenciarInscritoApto.php">Somente Aptos Ativado</a>
                <?php
            }
            ?>
        </td>
        <td ><?php echo descSimNao($objL->getVerificarVagasLimitadas()) ?></td>
        <td width="50px"><?php echo numberPad($objL->getTotalInscritos(),3) .' / '.numberPad($objL->getVagas(),3); ?> </td>
        <td ><?php echo descSimNao($objL->getPerfilIsento()) ?></td>
        <td ><?php echo descSimNao($objL->getVerificarEnvioTrabalho()) ?></td>
        <td ><?php echo descSimNao($objL->getVerificarSelecionarAtividade()) ?></td>
        <td >
            <?php echo descSimNao($objL->getLiberarCertificado()) ?>
            <?php echo $objL->getCargaHorariaCertificado()? "<br>ch: ".$objL->getCargaHorariaCertificado()."h" : ""; ?>
        </td>
        </tr>
        <?php
    }
} catch (Exception $exc) {
    echo "<tr><td colspan=\"5\">" . $exc->getMessage() . "</td></tr>";
}
echo "</table>";
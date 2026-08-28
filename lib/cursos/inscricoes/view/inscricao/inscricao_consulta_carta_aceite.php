<?php
require_once(dirname(__FILE__)."/../../include/config.php");
#require_once(dirname(__FILE__)."/../../include/inscricao_request_variaveis.php");
#Fazendo conexão com o banco
$Conexao_db = FWA_Conexao("OpcTec");
?>
<form name="abrirCartaAceite" id="abrirCartaAceite" method="post" action="<?php echo WB_URL_ACTION?>?content=view/carta_aceite/carta_aceite.php" target="_blank">
    <input type="hidden" value="" name="tipoCartaAceite" id="tipoCartaAceite">
    <input type="hidden" value="" name="cod_trabalho"    id="cod_trabalho_aceite">
</form>
<script language="JavaScript" type="text/javascript">
    function gerarCartaAceite(tipoCartaAceite,codTrabalho){
        $('#tipoCartaAceite').val(tipoCartaAceite);
        $('#cod_trabalho_aceite').val(codTrabalho);
        $('#abrirCartaAceite').submit();
    }
</script> 
<?php 
echo "<div><br>";
try {
               
    if(!$objInscrito->getCodIsento() and !$objInscrito->getTotalPagamentos() and !$objInscrito->getInscritoPossuiVoucher()){
        throw new Exception("Emissão da carta de aceite bloqueada.<br>Caso necessário, entre em contato com a administração do evento para regularizar sua inscrição.");
    }

    if($objInscrito->getTotalTrabalhos()>=1){
        $aoMenoUma = false;
        echo "<ul>";
        $objInscrito->carregarListaTrabalhosAceitosByIdInscrito();
        if($objInscrito->getListaTrabalhos()){
            foreach ($objInscrito->getListaTrabalhos() as $objTrabalho) {
//                if(($objTrabalho->getFlgPresenca()==1) and ($objTrabalho->getAvaliacaoFinal()=='APROVADO')){
                if($objTrabalho->getAvaliacaoFinal()=='APROVADO'){
                    $aoMenoUma = true;
//                    if($_SERVER['REMOTE_ADDR']=='200.203.142.25'){
                        echo "<li><a href='javascript:void(0);' onclick=\"gerarCartaAceite('carta_aceite_trabalho','".$objTrabalho->getCodTrabalho()."');\">Carta de Aceite do Trabalho: ".$objTrabalho->getCodTrabalho()."</a></li>";
//                    }else{
//                        echo "<li>Carta de aceite do trabalho ".$objTrabalho->getCodTrabalho()." en manutenção, previsão de liberação a partir das 14h do dia 09/10/2023</li>";
//                    }
                }
            }
        }
        if($aoMenoUma==false){
            echo "<li>Aguardando aceite</li>";
        }
        echo "</ul>";
    }

} catch (Exception $exc) {
    echo $exc->getMessage();
}

echo "</div>";
?>

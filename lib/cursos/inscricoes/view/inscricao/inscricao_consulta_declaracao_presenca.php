<?php
require_once(dirname(__FILE__)."/../../include/config.php");
#require_once(dirname(__FILE__)."/../../include/inscricao_request_variaveis.php");
#Fazendo conexão com o banco
$Conexao_db = FWA_Conexao("OpcTec");
?>
<form name="abrirDeclaracaoPresenca" id="abrirDeclaracaoPresenca" method="post" action="<?php echo WB_URL_ACTION?>?content=view/declaracao_presenca/declaracao_presenca.php" target="_blank">
    <input type="hidden" value="" name="cod_inscrito" id="cod_inscrito">
</form>
<script language="JavaScript" type="text/javascript">
    function gerarDeclaracaoPresenca(codInscrito){
        $('#cod_inscrito').val(codInscrito);
        $('#abrirDeclaracaoPresenca').submit();
    }
</script> 
<?php 
echo "<div><br>";
try {
               
    echo "<ul>";
    echo "<li><a href='javascript:void(0);' onclick=\"gerarDeclaracaoPresenca('".$objInscrito->getCodInscrito()."');\">"
    . "Declaração de presença - cód.: ".$objInscrito->getCodInscrito()."</a></li>";
    echo "</ul>";

} catch (Exception $exc) {
    echo $exc->getMessage();
}

echo "</div>";
?>

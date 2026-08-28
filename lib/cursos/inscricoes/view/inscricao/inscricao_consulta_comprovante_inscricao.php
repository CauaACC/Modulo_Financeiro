<?php
require_once(dirname(__FILE__)."/../../include/config.php");
#require_once(dirname(__FILE__)."/../../include/inscricao_request_variaveis.php");
#Fazendo conexão com o banco
$Conexao_db = FWA_Conexao("OpcTec");
?>
<form name="abrirComprovanteInscricao" id="abrirComprovanteInscricao" method="post" action="<?php echo WB_URL_ACTION?>?content=view/comprovante_inscricao/comprovante_inscricao.php" target="_blank">
    <input type="hidden" value="" name="cod_inscrito" id="cod_inscrito">
</form>
<script language="JavaScript" type="text/javascript">
    function gerarComprovanteInscricao(codInscrito){
        $('#cod_inscrito').val(codInscrito);
        $('#abrirComprovanteInscricao').submit();
    }
</script> 
<?php 
echo "<div><br>";
try {
               
    echo "<ul>";
    echo "<li><a href='javascript:void(0);' onclick=\"gerarComprovanteInscricao('".$objInscrito->getCodInscrito()."');\">"
    . "Comprovante de Inscrição - cód.: ".$objInscrito->getCodInscrito()."</a></li>";
    echo "</ul>";

} catch (Exception $exc) {
    echo $exc->getMessage();
}

echo "</div>";
?>

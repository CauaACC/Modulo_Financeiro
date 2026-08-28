<?php
require_once(dirname(__FILE__)."/../../include/config.php");
#require_once(dirname(__FILE__)."/../../include/inscricao_request_variaveis.php");
#Fazendo conexão com o banco
//$Conexao_db = FWA_Conexao("OpcTec");
?>
<form name="abrirCertificado" id="abrirCertificado" method="post" action="<?php echo WB_URL_ACTION?>?content=view/certificados/certificado.php" target="_blank">
    <input type="hidden" value="" name="tipoCertificado" id="tipoCertificado">
    <input type="hidden" value="" name="cod_trabalho"    id="cod_trabalho">
    <input type="hidden" value="" name="cod_inscrito"    id="cod_inscrito">
    <input type="hidden" value="" name="cod_oficina"     id="cod_oficina">
    <input type="hidden" value="" name="cod_perfil"      id="cod_perfil">
</form>
<script language="JavaScript" type="text/javascript">
    function gerarCertificado(tipoCertificado){
        $('#tipoCertificado').val(tipoCertificado);
        $('#abrirCertificado').submit();
    }
    function gerarCertificadoPerfil(tipoCertificado,codPerfil){
        $('#tipoCertificado').val(tipoCertificado);
        $('#cod_perfil').val(codPerfil);
        $('#abrirCertificado').submit();
    }
    function gerarCertificadoTrabalho(tipoCertificado,codTrabalho){
        $('#tipoCertificado').val(tipoCertificado);
        $('#cod_trabalho').val(codTrabalho);
        $('#abrirCertificado').submit();
    }
    function gerarCertificadoAtividade(tipoCertificado,codInscrito,codOficina){
        $('#tipoCertificado').val(tipoCertificado);
        $('#cod_inscrito').val(codInscrito);
        $('#cod_oficina').val(codOficina);
        $('#abrirCertificado').submit();
    }
</script> 
<?php 
echo "<div><br>";
try {       
    GerenciarInscricaoAC::gerarLinksCertificados($objInscrito, $fws_, $debug);
} catch (Exception $exc) {
    echo $exc->getMessage();
}
//}
echo "</div>";

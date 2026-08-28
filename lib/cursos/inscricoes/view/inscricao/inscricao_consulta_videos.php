<?php
require_once(dirname(__FILE__)."/../../include/config.php");
#require_once(dirname(__FILE__)."/../../include/inscricao_request_variaveis.php");
#Fazendo conexão com o banco
$Conexao_db = FWA_Conexao("OpcTec");
?>
<form name="abrirCertificado" id="abrirCertificado" method="post" action="<?php echo WB_URL_ACTION?>?content=view/certificados/certificado.php" target="_blank">
    <input type="hidden" value="" name="tipoCertificado" id="tipoCertificado">
    <input type="hidden" value="" name="cod_trabalho"    id="cod_trabalho">
    <input type="hidden" value="" name="cod_inscrito"    id="cod_inscrito">
    <input type="hidden" value="" name="cod_oficina"     id="cod_oficina">
</form>
<script language="JavaScript" type="text/javascript">
    function gerarCertificado(tipoCertificado){
        $('#tipoCertificado').val(tipoCertificado);
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
try {
               
    if(!$objInscrito->getCodVoucher() and !$objInscrito->getCodIsento() and !$objInscrito->getTotalPagamentos() and !$objInscrito->getTotalPerfisIsentos()){
        throw new Exception("Acesso bloqueado.<br>Caso necessário, entre em contato com a administração do evento (".EMAIL_CONTATO.") para regularizar sua inscrição.");
    }
//    if(!$objInscrito->getFlgPresencaInscrito()){
//        throw new Exception("Acesso bloqueada.<br>Presença não foi confirmada.");
//    }
//    if(!$_SESSION['_logadoComo']){
//        if ($objInscrito->getAvaliouEvento() !='1') {
//            throw new Exception("<a href='".WB_URL_AVALIAR_EVENTO."'>Clique aqui para Avaliar o Evento e Baixar Certificados</a>");
//        }
//    }
?>
<!--    
    <div class="video-responsive">
        <br>
        <iframe src="https://youtu.be/pxRlaOmRx9Y"
            frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" 
            allowfullscreen></iframe>
        <br>&nbsp;
    </div>-->
<br>
<ul style="list-style-type:none;">
    <li>
        <a style="width: 350px;" href="https://www.youtube.com/watch?v=Tqf7JRYZtB8&t=6s" target="_blank" class="btn btn-primary" role="button"
           class="button">Cerimônia de Abertura</a><br>&nbsp;
    </li>
    <li>
        <a style="width: 350px;" href="https://www.youtube.com/playlist?list=PLe-1YNwVuMRqJvLJW_yt4CtVmg8_MEFL7" target="_blank"  class="btn btn-primary" role="button"
           class="button">O Congresso</a><br>&nbsp;
    </li>
    <li>
        <a style="width: 350px;" href="https://www.youtube.com/playlist?list=PLe-1YNwVuMRpZlC6CBT_vANw6LMh5WY1k" target="_blank"  class="btn btn-primary" role="button"
           class="button">Mesas-Redondas</a><br>&nbsp;
    </li>
    <li>
        <a style="width: 350px;" href="https://www.youtube.com/playlist?list=PLe-1YNwVuMRoJZBG1Y3W55VVOYrJDM9dH" target="_blank"  class="btn btn-primary" role="button"
           class="button">Painéis</a><br>&nbsp;
    </li>
    <li>
        <a style="width: 350px;" href="https://www.youtube.com/playlist?list=PLe-1YNwVuMRrKyzEmWSAJ39QnuUPJpvkN" target="_blank"  class="btn btn-primary" role="button"
           class="button">Minicursos</a><br>&nbsp;
    </li>
    <li>
        <a style="width: 350px;" href="https://youtu.be/uw2TLwrp5gQ" target="_blank"  class="btn btn-primary" role="button"
           class="button">II Encontro dos Profissionais de Educação Física</a><br>&nbsp;
    </li>
    <li>
        <a style="width: 350px;" href="https://youtu.be/ZEh5uM60K-4?list=PLe-1YNwVuMRqJvLJW_yt4CtVmg8_MEFL7" target="_blank"  class="btn btn-primary" role="button"
           class="button">5º Prêmio - Inova Sáude Paraná</a><br>&nbsp;
    </li>
    <li>
        <a style="width: 350px;" href="https://youtu.be/EQXYIpOv2So?list=PLe-1YNwVuMRqJvLJW_yt4CtVmg8_MEFL7" target="_blank"  class="btn btn-primary" role="button"
           class="button">Sessão de Encerramento</a><br>&nbsp;
    </li>
</ul>
        

<?php
} catch (Exception $exc) {
    echo "<div><br>";
    echo $exc->getMessage();
    echo "</div>";
}

?>

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
    <div class="video-responsive">
        <br>    
        <iframe src="https://youtu.be/PZs4SMJkOpk"
            frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" 
            allowfullscreen></iframe>
        <br>&nbsp;
    </div>

<?php
} catch (Exception $exc) {
    echo "<div><br>";
    echo $exc->getMessage();
    echo "</div>";
}

?>
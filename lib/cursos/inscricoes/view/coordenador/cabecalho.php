<div class="page-header">
    <div class="page-context">&Aacute;rea do Coordenador</div>
</div>
<?php
if ($COD_EVENTO != '' and $COD_EVENTO != 'COD_EVENTO') {
    echo '<div class="panel-heading" style="margin-bottom:8px;"><h4>' . htmlspecialchars($objEv->getTitulo()) . '</h4></div>';
} else {
    $objEvAlt = new Evento();
    $objEvAlt->setCodEvento($cod_evento);
    $objEvAlt->getEventoById();
    if ($objEvAlt->getTitulo()) {
        echo '<div class="panel-heading" style="margin-bottom:8px;"><h4>Evento: ' . htmlspecialchars($objEvAlt->getTitulo()) . '</h4></div>';
    }
}
?>

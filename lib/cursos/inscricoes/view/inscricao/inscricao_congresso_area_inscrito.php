<h4>Área do Inscrito</h4>
<?php
$objEv = new Evento();
$objEv->setCodEvento(COD_EVENTO);
$objEv->getEventoById();
echo "<h4>".$objEv->getTitulo()."</h4>";

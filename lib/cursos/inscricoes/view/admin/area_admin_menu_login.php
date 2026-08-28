<?php if (FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_GERENCIAR_EVENTO, 5, $cod_msg, false)) { ?> 
<div class="row">
<div class="col-sm-12 submenu-area">
<a class='button btn btn-primary btn-xs' href='index.php?content=view/admin/login-cadastro-ok.php'
       >Gerenciar Login</a>
<a class='button btn btn-primary btn-xs' href='index.php?content=view/admin/ligacao-usuarios-grupos-listagem.php'
       >Gerenciar Grupos</a>
<a class='button btn btn-primary btn-xs' href='index.php?content=view/admin/consulta-avaliacao-trabalhos-listagem.php'
       >Gerenciar Avaliação Trabalhos</a>

<a class='button btn btn-primary btn-xs' href='index.php?content=view/admin/GerenciarAvaliacaoInscrito.php'
       >Gerenciar Avaliação Inscrito</a>
</div></div>
<?php } ?>

<?php
// $objEvAlt = new Evento();
// $objEvAlt->setCodEvento($cod_evento);
// $objEvAlt->getEventoById();
// echo "<h4>Evento: ".$objEvAlt->getTitulo()."</h4>";
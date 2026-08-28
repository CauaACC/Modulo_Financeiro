<?php
$dir = dirname(__FILE__);
require_once($dir."/../inscricoes/include/config.php");
$url = "". WB_URL_VIEW."?content=view/transparencia/projetos.php";
header("Location: $url");
echo "<a href='".$url."'>redirecionando para transparência do gestor, caso não redirecione clique aqui ...</a>";
echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $url . "\"></HTML>";
exit();
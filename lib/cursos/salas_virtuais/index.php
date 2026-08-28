<?php
require_once(dirname(__FILE__)."/../inscricoes/include/config.php");
$url = "". WB_URL_VIEW."?content=view/inscricao/inscricao_congresso_hall_virtual.php";
//@header("Location: $url");
echo "<a href='".$url."'>redirecionando para o Hall Virtual, caso não redirecione clique aqui ...</a>";
echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $url . "\"></HTML>";
exit();
?>  
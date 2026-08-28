<?php
// Redirect para a nova pagina de Carga Projetos
// Mantido para nao quebrar links existentes
require_once(dirname(__FILE__)."/../../include/config.php");
echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . WB_URL_VIEW . "?content=view/admin/GerenciarCargaTransparenciaProjetos.php\"></HTML>";
exit;

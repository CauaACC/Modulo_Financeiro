<?php
require_once(dirname(__FILE__)."/../../include/config.php");
if(COD_EVENTO==1){
    include(dirname(__FILE__)."/../view/admin/consulta-dados-congresso-listagem-trabalhos-ensalados-versao-01.php");
}else{
    if($_GET['versao2']){
        include(dirname(__FILE__)."/../view/admin/consulta-dados-congresso-listagem-trabalhos-ensalados-versao-02.php");
    }else if ($_GET['versao1']){
        include(dirname(__FILE__)."/../view/admin/consulta-dados-congresso-listagem-trabalhos-ensalados-versao-03.php");
    }else if ($_GET['chamada']){
        include(dirname(__FILE__)."/../view/admin/consulta-dados-congresso-listagem-trabalhos-ensalados-versao-lista-chamada.php");
    }else{
        include(dirname(__FILE__)."/../view/admin/consulta-dados-congresso-listagem-trabalhos-ensalados-versao-03.php");
    }
}
?>

<?php
require_once(dirname(__FILE__)."/../../include/inscricao_request_variaveis.php");
require_once(dirname(__FILE__)."/../../view/admin/consulta-dados-congresso-seguranca.php");

switch($content){
  case "view/admin/consulta-dados-congresso-listagem-trabalhos-estado.php":
    $FWS_Sub_Titulo = "Consulta Trabalhos de um Determinado Estado";
    $FWS_Form_Detalhes = true;
  break;

  case "view/admin/consulta-dados-congresso-listagem-inscritos-oficinas.php":
    $FWS_Sub_Titulo = "Consulta Inscritos em uma Determinada Oficina";
    $FWS_Form_Detalhes = true;
  break;

  case "view/admin/consulta-dados-congresso-listagem-monografias.php":
    $FWS_Sub_Titulo = "Consulta Inscritos em uma Determinada Premiação Científica";
    $FWS_Form_Detalhes = true;
  break;  

  case ($_SERVER["DOCUMENT_ROOT"]."/consulta-dados-congresso-listagem-inscritos.php")or("consulta-dados-congresso-listagem-todos-inscritos-ordem.php"):
    $FWS_Sub_Titulo = "Consulta Inscritos no Projeto - ".$_SESSION['status'];
    $FWS_Form_Detalhes = true;
  break;  
  
  case "view/admin/consulta-dados-congresso-listagem-inscritos-estado.php":
    $FWS_Sub_Titulo = "Consulta Inscritos no Projeto do Estado: ".$_SESSION['estado'];
    $FWS_Form_Detalhes = true;
  break;  


  default:
    $FWS_Sub_Titulo = "";
  break;
}

//If($FWS_Sub_Titulo!=""){
//  echo "<span style class='subtitulo'> <B>".$FWS_Sub_Titulo."</B></span>";
//}

?>
<?php
require_once(dirname(__FILE__)."/../../view/admin/titulo_area_admin.php");
require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_administracao.php");
require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_inscricoes.php");
?>
<div class="panel-group">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4>Painel do Projeto:</h4>
        </div>
        <div class="panel-body">      
<?php      
        
        

<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/ligacao-usuarios-grupos-seguranca.php");
$fws_['cod_usuario']==""? $fws_['cod_usuario']=$_GET['cod_usuario'] : "";
$fws_['cod_usuario']==""? $fws_['cod_usuario']=$_GET['COD_USUARIO'] : "";
?>
<?php
// require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_configuracao.php");
require_once(dirname(__FILE__)."/../../view/admin/titulo_area_admin.php");
require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_administracao.php"); 
require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_usuarios.php"); 
?>
<div class="panel-group">
<div class="panel panel-default">
    <div class="panel-heading"><h4>Gerenciar Grupos de Usuários</h4></div>
    <div class="panel-body">

<a class='titulo' href='index.php?content=view/admin/ligacao-usuarios-grupos-listagem.php'>
<B>Ligação de Usuarios a Grupos</B>
</a>
<?php

switch($content){
  case "view/admin/avaliacao-trabalhos-listagem.php":
    #$FWS_Sub_Titulo = "Consulta Trabalhos de um Determinado Tema";
    $FWS_Form_Detalhes = true;
  break;

  default:
    $FWS_Sub_Titulo = "";
  break;
}

If($FWS_Sub_Titulo!=""){
  echo "<span style class='subtitulo'> <B> - ".$FWS_Sub_Titulo."</B></span>";
}

If($FWS_Form_Detalhes==true){
  echo "
        <form action='index.php' method='post'>
          <input type='hidden' name='content' value='".$content."'>
          &nbsp;&nbsp;&nbsp;<input type='radio' name='detalhes' value='1' > Trabalhos Avaliados
          &nbsp;&nbsp;&nbsp;<input type='radio' name='detalhes' value='0' > Trabalhos a serem Avaliados
          &nbsp;&nbsp;&nbsp;<input type='submit' value='ok'>
        </form>
       ";
}

?>
<span style class='texto'><hr size='1' width='100%'>

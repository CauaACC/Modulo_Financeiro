<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/consulta-avaliacao-trabalhos-seguranca.php");

require_once(dirname(__FILE__)."/../../view/admin/titulo_area_admin.php");
require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_administracao.php");
require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_avaliacao.php");
?>
<div class="panel-group">
<div class="panel panel-default">
    <div class="panel-heading"><h4><a href='index.php?content=view/admin/consulta-avaliacao-trabalhos-listagem.php'>Gerenciar Avaliação de Trabalhos</a></h4></div>
    <div class="panel-body">
<?php
@$cod_area_tematica = $_GET['cod_area_tematica'];
If(@$cod_area_tematica==""){
  @$cod_area_tematica = $_POST['cod_area_tematica'];
}

/*
switch($content){
  case "view/admin/consulta-avaliacao-trabalhos-listagem.php":
    #$FWS_Sub_Titulo = "Consulta Trabalhos de um Determinado Tema";
    $FWS_Busca         = true;
    $FWS_Form_Detalhes = true;
  break;
  case "view/admin/consulta-avaliacao-trabalhos-detalhes-avaliados.php":
    #$FWS_Sub_Titulo = "Consulta Trabalhos de um Determinado Tema";
    $FWS_Busca         = true;
    $FWS_Form_Detalhes = true;
  break;
  case "view/admin/consulta-avaliacao-trabalhos-detalhes-nao-avaliados.php":
    #$FWS_Sub_Titulo = "Consulta Trabalhos de um Determinado Tema";
    $FWS_Busca         = true;
    $FWS_Form_Detalhes = true;
  break;
  case "view/admin/consulta-avaliacao-trabalhos-apresentacao.php":
    #$FWS_Sub_Titulo = "Consulta Trabalhos de um Determinado Tema";
    $FWS_Busca         = true;
    $FWS_Form_Detalhes = false;
  break;
  case "view/admin/consulta-avaliacao-trabalhos-orientacao.php":
    #$FWS_Sub_Titulo = "Consulta Trabalhos de um Determinado Tema";
    $FWS_Busca         = true;
    $FWS_Form_Detalhes = false;
  break;
  default:
    $FWS_Sub_Titulo = "";
  break;
}

If($FWS_Sub_Titulo!=""){
  echo "<span style class='subtitulo'> <B> - ".$FWS_Sub_Titulo."</B></span>";
}

If($FWS_Busca==true){
  require_once(dirname(__FILE__)."/../../view/admin/consulta-avaliacao-trabalhos-busca.php");
}

If($FWS_Form_Detalhes==true){
  If(@$_POST['detalhes']==1){
    $checked_detalhes_1 = "checked";
  }Else{
    $checked_detalhes_0 = "checked";
  }

  #echo $_POST['detalhes']."->".$checked_detalhes_0;

  echo "
        <form action='index.php' method='post'>
          <b>Alternar lista de trabalhos </b>:
          <!--input type='hidden' name='content' value='".$content."'-->
          <input type='hidden' name='content' value='view/admin/consulta-avaliacao-trabalhos-listagem.php'>
          <input type='hidden' name='cod_area_tematica' value='".$cod_area_tematica."'>
          &nbsp;&nbsp;&nbsp;<input type='radio' name='detalhes' value='1' ".$checked_detalhes_1."> Trabalhos Avaliados
          &nbsp;&nbsp;&nbsp;<input type='radio' name='detalhes' value='0' ".$checked_detalhes_0."> Trabalhos a serem Avaliados
          &nbsp;&nbsp;&nbsp;<input type='submit' value='  OK  '>
        </form>
        <span style class='texto'>

       ";
}
*/
?>

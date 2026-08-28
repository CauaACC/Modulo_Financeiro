<?php
require_once(dirname(__FILE__)."/../../view/admin/re-avaliacao-trabalhos-seguranca.php");

$cod_area_tematica = $fws_['cod_area_tematica']!=''? $fws_['cod_area_tematica'] : $_GET['cod_area_tematica'];
$cod_trabalho      = $fws_['cod_trabalho']!=''?      $fws_['cod_trabalho']      : $_GET['cod_trabalho'];
$detalhes          = $fws_['detalhes']!=''?          $fws_['detalhes']          : $_GET['detalhes'];
?>
<h3>Área Administrativa</h3>  
<div class="panel-group">
<div class="panel panel-default">
    <div class="panel-heading"><h4>Gerenciar Avaliação de Trabalhos</h4></div>
    <div class="panel-body">
        
<!--
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a class='titulo' href='index.php?content=view/admin/re-avaliacao-trabalhos-orientacao.php'>
<B>Orientações para Re-Avaliação</B>
</a>
-->
<?php

switch($content){
  case "view/admin/re-avaliacao-trabalhos-listagem.php":
    #$FWS_Sub_Titulo = "Consulta Trabalhos de um Determinado Tema";
    $FWS_Busca         = true;
    $FWS_Form_Detalhes = true;
  break;
  case "view/admin/re-avaliacao-trabalhos-detalhes-avaliados.php":
    #$FWS_Sub_Titulo = "Consulta Trabalhos de um Determinado Tema";
    $FWS_Busca         = true;
    $FWS_Form_Detalhes = true;
  break;
  case "view/admin/re-avaliacao-trabalhos-detalhes-nao-avaliados.php":
    #$FWS_Sub_Titulo = "Consulta Trabalhos de um Determinado Tema";
    $FWS_Busca         = true;
    $FWS_Form_Detalhes = true;
  break;
  case "view/admin/re-avaliacao-trabalhos-apresentacao.php":
    #$FWS_Sub_Titulo = "Consulta Trabalhos de um Determinado Tema";
    $FWS_Busca         = true;
    $FWS_Form_Detalhes = false;
  break;
  case "view/admin/re-avaliacao-trabalhos-orientacao.php":
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
  require_once(dirname(__FILE__)."/../../view/admin/re-avaliacao-trabalhos-busca.php");
}

If($FWS_Form_Detalhes==true){
  If(@$detalhes==1){
    $checked_detalhes_1 = "checked";
  }Else{
    $checked_detalhes_0 = "checked";
  }
  echo "<br>
        <form action='index.php?content=".$content."' method='post'>
          <b>Alternar lista de trabalhos </b>:
          <!--input type='hidden' name='content' value='".$content."'-->
          <input type='hidden' name='content' value='view/admin/re-avaliacao-trabalhos-listagem.php'>
          <input type='hidden' name='cod_area_tematica' value='".$cod_area_tematica."'>
          &nbsp;&nbsp;&nbsp;<input type='radio' name='detalhes' value='1' ".$checked_detalhes_1."> Trabalhos Avaliados
          &nbsp;&nbsp;&nbsp;<input type='radio' name='detalhes' value='0' ".$checked_detalhes_0."> Trabalhos a serem Avaliados
          &nbsp;&nbsp;&nbsp;<input type='submit' value='  OK  '>
        </form>
        <span style class='texto'>
        <hr size='1' width='100%'>
       ";
}
?>

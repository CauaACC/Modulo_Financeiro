<?php
require_once(dirname(__FILE__)."/../../view/admin/trabalhos-cadastro-seguranca.php");
?>
<h3>Área Administrativa</h3>  
<div class="panel-group">
<div class="panel panel-default">
    <div class="panel-heading"><h4>Gerenciar Avaliação de Trabalhos</h4></div>
    <div class="panel-body">
<?php
@$cod_area_tematica = $_SESSION['cod_area_tematica'];
@$detalhes          = $_SESSION['detalhes'];
?>
<a class='titulo' href='index.php?content=view/admin/trabalhos-cadastro-listagem.php'>
<B>Alteração de Dados dos Trabalhos</B>
</a>
<!--
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a class='titulo' href='index.php?content=view/admin/re-avaliacao-trabalhos-orientacao.php'>
<B>Orientações para Re-Avaliação</B>
</a>
-->
<?php

switch($content){
  case "view/admin/trabalhos-cadastro-listagem.php":
    #$FWS_Sub_Titulo = "Consulta Trabalhos de um Determinado Tema";
    $FWS_Busca         = true;
    $FWS_Form_Detalhes = true;
  break;
  case "view/admin/trabalhos-cadastro-detalhes.php":
    #$FWS_Sub_Titulo = "Consulta Trabalhos de um Determinado Tema";
    $FWS_Busca         = true;
    $FWS_Form_Detalhes = true;
  break;
  default:
    $FWS_Sub_Titulo = "";
  break;
}

If($FWS_Sub_Titulo!=""){
  echo "<span style class='subtitulo'> <B> - ".$FWS_Sub_Titulo."</B></span>";
}

If($FWS_Busca==true){
  require_once(dirname(__FILE__)."/../../view/admin/trabalhos-cadastro-busca.php");
}

If($FWS_Form_Detalhes==true){
  If(@$detalhes==1){
    $checked_detalhes_1 = "checked";
  }Else{
    $checked_detalhes_0 = "checked";
  }

  #echo $_POST['detalhes']."->".$checked_detalhes_0;

  echo "
        <form action='index.php' method='post'>
          <b>Alternar lista de trabalhos </b>:
          <!--input type='hidden' name='content' value='".$content."'-->
          <input type='hidden' name='content' value='view/admin/trabalhos-cadastro-listagem.php'>
          <input type='hidden' name='cod_area_tematica' value='".$cod_area_tematica."'>
          &nbsp;&nbsp;&nbsp;<input type='radio' name='detalhes' value='1' ".$checked_detalhes_1."> Trabalhos Avaliados
          &nbsp;&nbsp;&nbsp;<input type='radio' name='detalhes' value='0' ".$checked_detalhes_0."> Trabalhos a serem Avaliados
          &nbsp;&nbsp;&nbsp;<input type='submit' value='  OK  '>
        </form>
        <span style class='texto'>

       ";
}
?>

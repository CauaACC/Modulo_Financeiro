<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/comunicado-avaliacao-trabalhos-seguranca.php");

?>
<h3>Área Administrativa</h3>  
<div class="panel-group">
<div class="panel panel-default">
    <div class="panel-heading"><h4>Gerenciar Comunicado de Avaliações dos Trabalhos</h4></div>
    <div class="panel-body">

<a class='titulo' href='index.php?content=view/admin/comunicado-avaliacao-trabalhos-listagem.php'>
<B>Sistema Comunicado de Avaliações dos Trabalhos </B>
</a>
<hr size='1' width='100%'>
<?php

switch($content){
  case "view/admin/comunicado-avaliacao-trabalhos-listagem.php":
    $FWS_Sub_Titulo    = "";
    $FWS_Busca         = false;
    $FWS_Form_Detalhes = false;
  break;
  case "view/admin/comunicado-avaliacao-trabalhos-detalhes-avaliados.php":
    $FWS_Sub_Titulo    = "";
    $FWS_Busca         = false;
    $FWS_Form_Detalhes = false;
  break;
  case "view/admin/comunicado-avaliacao-trabalhos-detalhes-nao-avaliados.php":
    $FWS_Sub_Titulo    = "";
    $FWS_Busca         = false;
    $FWS_Form_Detalhes = false;
  break;
  case "view/admin/comunicado-avaliacao-trabalhos-apresentacao.php":
    $FWS_Sub_Titulo    = "";
    $FWS_Busca         = false;
    $FWS_Form_Detalhes = false;
  break;
  case "view/admin/comunicado-avaliacao-trabalhos-orientacao.php":
    $FWS_Sub_Titulo    = "";
    $FWS_Busca         = false;
    $FWS_Form_Detalhes = false;
  break;
  default:
    $FWS_Sub_Titulo    = "";
  break;
}

If($FWS_Sub_Titulo!=""){
  echo "<span style class='subtitulo'> <B> - ".$FWS_Sub_Titulo."</B></span>";
}

If($FWS_Busca==true){
  require_once(dirname(__FILE__)."/../../view/admin/comunicado-avaliacao-trabalhos-busca.php");
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
          <input type='hidden' name='content' value='view/admin/comunicado-avaliacao-trabalhos-listagem.php'>

          <br>&nbsp;&nbsp;&nbsp;<input type='radio' name='detalhes' value='1' ".$checked_detalhes_1."> Trabalhos Avaliados  - não comunicados
          <br>&nbsp;&nbsp;&nbsp;<input type='radio' name='detalhes' value='0' ".$checked_detalhes_0."> Trabalhos Reprovados - não comunicados
          <!--
          <br>&nbsp;&nbsp;&nbsp;<input type='radio' name='detalhes' value='0' ".$checked_detalhes_0."> Trabalhos Reprovados - não comunicados
          <br>&nbsp;&nbsp;&nbsp;<input type='radio' name='detalhes' value='0' ".$checked_detalhes_0."> Trabalhos Reprovados - não comunicados
          -->
          &nbsp;&nbsp;&nbsp;<input type='submit' value='  OK  '>
        </form>
        <span style class='texto'>

       ";
}

echo "<br><a class='linkg' href='index.php?content=view/admin/comunicado-avaliacao-trabalhos-listagem-reprovado-reprovado.php'>Informar Reprovados Definitivo</a><br>";

echo "<br><a class='linkg' href='index.php?content=view/admin/comunicado-avaliacao-trabalhos-listagem-reprovado.php'>Informar Reprovados na 1º Avaliação</a><br>";

echo "<br><a class='linkg' href='index.php?content=view/admin/comunicado-avaliacao-trabalhos-listagem-aprovado.php'>Informar Aprovados</a><br>";

echo "<br><a class='linkg' href='index.php?content=view/admin/comunicado-avaliacao-trabalhos-listagem-realocado-tema-15-aprovado.php'>Informar Aprovados Realocados Tema 15</a><br>";

echo "<br><a class='linkg' href='index.php?content=view/admin/comunicado-ensalamento-trabalhos-aprovado-listagem-aprovado.php'>Informar Ensalamento</a><br>";
?>
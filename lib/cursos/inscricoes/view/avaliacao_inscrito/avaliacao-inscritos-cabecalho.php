<?php
require_once(dirname(__FILE__)."/../../view/avaliacao_inscrito/avaliacao-inscritos-seguranca.php");

($fws_['cod_area_tematica']=='')? $fws_['cod_area_tematica'] = addslashesopctec($_GET['cod_area_tematica']) : "";
($fws_['cod_trabalho']=='')     ? $fws_['cod_trabalho']      = addslashesopctec($_GET['cod_trabalho'])      : "";
($fws_['detalhes']=='')         ? $fws_['detalhes']          = addslashesopctec($_GET['detalhes'])          : "";
($fws_['detalhes']=='')         ? $fws_['detalhes']          = 2                          : "";


?>
|&nbsp;&nbsp;
<a class='titulo' href='index.php?content=view/avaliacao_inscrito/avaliacao-inscritos-apresentacao.php'>
<B>Apresentação</B>
</a>
&nbsp;&nbsp;|&nbsp;&nbsp;
<a class='titulo' href='index.php?content=view/avaliacao_inscrito/avaliacao-inscritos-orientacao.php'>
<B>Orientações para Avaliação</B>
</a>
&nbsp;&nbsp;|&nbsp;&nbsp;
<a class='titulo' href='index.php?content=view/avaliacao_inscrito/avaliacao-inscritos-listagem-por-avaliador.php'>
<B>Avaliação de Trabalhos</B>
</a>
&nbsp;&nbsp;|
<?php
switch($content){
  case "view/avaliacao_inscrito/avaliacao-inscritos-listagem-por-avaliador.php":
    #$FWS_Sub_Titulo = "Consulta Trabalhos de um Determinado Tema";
    $FWS_Busca         = true;
    $FWS_Form_Detalhes = true;
  break;
  case "view/avaliacao_inscrito/avaliacao-inscritos-detalhes-avaliados.php":
    #$FWS_Sub_Titulo = "Consulta Trabalhos de um Determinado Tema";
    $FWS_Busca         = true;
    $FWS_Form_Detalhes = true;
  break;
  case "view/avaliacao_inscrito/avaliacao-inscritos-detalhes-nao-avaliados.php":
    #$FWS_Sub_Titulo = "Consulta Trabalhos de um Determinado Tema";
    $FWS_Busca         = true;
    $FWS_Form_Detalhes = true;
  break;
  case "view/avaliacao_inscrito/avaliacao-inscritos-apresentacao.php":
    #$FWS_Sub_Titulo = "Consulta Trabalhos de um Determinado Tema";
    $FWS_Busca         = true;
    $FWS_Form_Detalhes = false;
  break;
  case "view/avaliacao_inscrito/avaliacao-inscritos-orientacao.php":
    #$FWS_Sub_Titulo = "Orientações para leitura e análise dos resumos";
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

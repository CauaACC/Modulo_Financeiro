<?php
require_once(dirname(__FILE__)."/../../view/avaliacao/avaliacao-trabalhos-seguranca.php");

($fws_['cod_area_tematica']=='')? $fws_['cod_area_tematica'] = addslashes($_GET['cod_area_tematica']) : "";
($fws_['cod_trabalho']=='')     ? $fws_['cod_trabalho']      = addslashes($_GET['cod_trabalho'])      : "";
($fws_['detalhes']=='')         ? $fws_['detalhes']          = addslashes($_GET['detalhes'])          : "";
/** @todo Avaliação trabalho definir regra para listagem inicial 0 ou 2 */
($fws_['detalhes']=='')         ? $fws_['detalhes']          = 0                          : "";


?>
|&nbsp;&nbsp;
<a class='titulo' href='index.php?content=view/avaliacao/avaliacao-trabalhos-apresentacao.php'>
<B>Apresentação</B>
</a>
&nbsp;&nbsp;|&nbsp;&nbsp;
<a class='titulo' href='index.php?content=view/avaliacao/avaliacao-trabalhos-orientacao.php'>
<B>Orientações para Avaliação</B>
</a>
&nbsp;&nbsp;|&nbsp;&nbsp;
<?php
/** @todo Avaliação trabalho definir regra */
if (in_array(COD_EVENTO, [84])) {
  $regraAvaliacao = 'avaliacao-trabalhos-listagem-avulsa';
}
switch($regraAvaliacao){
  case "avaliacao-trabalhos-listagem-avulsa":
    $urlRegraAvaliacao = "index.php?content=view/avaliacao/avaliacao-trabalhos-listagem-avulsa.php";
  break;
  case "avaliacao-trabalhos-listagem-por-areatematica":
    $urlRegraAvaliacao = "index.php?content=view/avaliacao/avaliacao-trabalhos-listagem-por-areatematica.php";
  break;
  default:
    $urlRegraAvaliacao = "index.php?content=view/avaliacao/avaliacao-trabalhos-listagem-por-avaliador.php";
  break;
}
?>
<a class='titulo' href='<?php echo $urlRegraAvaliacao ?>'>
<B>Avaliação de Trabalhos</B>
</a>
&nbsp;&nbsp;|
<?php
switch($content){
  case "view/avaliacao/avaliacao-trabalhos-listagem-avulsa.php":
  case "view/avaliacao/avaliacao-trabalhos-listagem-por-areatematica":
  case "view/avaliacao/avaliacao-trabalhos-listagem-por-avaliador.php":
    #$FWS_Sub_Titulo = "Consulta Trabalhos de um Determinado Tema";
    $FWS_Busca         = true;
    $FWS_Form_Detalhes = true;
  break;
  case "view/avaliacao/avaliacao-trabalhos-detalhes-avaliados.php":
    #$FWS_Sub_Titulo = "Consulta Trabalhos de um Determinado Tema";
    $FWS_Busca         = true;
    $FWS_Form_Detalhes = true;
  break;
  case "view/avaliacao/avaliacao-trabalhos-detalhes-nao-avaliados.php":
    #$FWS_Sub_Titulo = "Consulta Trabalhos de um Determinado Tema";
    $FWS_Busca         = true;
    $FWS_Form_Detalhes = true;
  break;
  case "view/avaliacao/avaliacao-trabalhos-apresentacao.php":
    #$FWS_Sub_Titulo = "Consulta Trabalhos de um Determinado Tema";
    $FWS_Busca         = true;
    $FWS_Form_Detalhes = false;
  break;
  case "view/avaliacao/avaliacao-trabalhos-orientacao.php":
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

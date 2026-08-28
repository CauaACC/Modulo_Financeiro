<?php
require_once(dirname(__FILE__) . "/../../include/config.php");
require_once(dirname(__FILE__) . "/../../view/admin/inscritos-seguranca.php");
#-------------------------------------------
$debug = false;

$fws_['data_alteracao']  = Date("YmdHi");
$fws_['data_inclusao']   = Date("YmdHi");
$fws_['status']          = "ATIVADA";
$fws_['session_id']      = $_SESSION['_cod_usuario'];
$fws_['http_user_agent'] = $_SERVER["HTTP_USER_AGENT"];
$fws_['ip_cliente']      = $_SERVER["REMOTE_ADDR"];
$fws_['dat_incl'] = date('Y-m-d H:i:s');
$fws_['res_incl'] = $_SESSION['_cod_usuario'];
$fws_['ip_incl']  = $_SERVER['REMOTE_ADDR'];
$fws_['versao']    = 1;

#echo $acao."-<BR>";
#-------------------------------------------
if ($fws_['info_almoco']) {
  $almoco = json_encode(array_map('htmlentities', $fws_['info_almoco']));
}
if ($fws_['cpf']) {
  $fws_['cpf'] = limparCpfCnpj($fws_['cpf']);
}
if ($fws_['cpf_recibo']) {
  $fws_['cpf_recibo'] = limparCpfCnpj($fws_['cpf_recibo']);
}

$fws_['qr_code'] = hash('sha256', $fws_['cpf'] . $fws_['cod_evento'] . $fws_['dat_incl']);

try {
    $objInscrito = new Inscrito();
    $objInscrito->setAll($fws_);
    $objInscrito->salvar($debug);
} catch (OKException $exc) {
    $MSG     = $exc->getMessage();
    $_SESSION['MSG'] = $MSG;
    $CONTENT = "view/admin/inscritos-cadastro-ok.php";
    $URL     = WB_URL_VIEW . "?content=" . $CONTENT . "&cod_inscrito=" . $objInscrito->getCodInscrito();
} catch (Exception $exc) {
    $MSG     = $exc->getMessage();
    $_SESSION['MSG'] = $MSG;
    $CONTENT = "view/admin/inscritos-cadastro-incluir.php";
    $URL     = WB_URL_VIEW . "?content=" . $CONTENT . "&cod_evento=".$objEv->getCodEvento()."&cod_inscrito=" . $fws_['cod_inscrito'];
}

#--------------------------------------------------------------
$MSG_Imprimir = $MSG;

$loc   = "/--/";
$subst = "<br>";
$MSG_Imprimir = preg_replace($loc, $subst, $MSG_Imprimir);

$loc   = "/-/";
$subst = " ";
$MSG_Imprimir = preg_replace($loc, $subst, $MSG_Imprimir);
#--------------------------------------------------------------
if ($debug == true) {
  echo $MSG_Imprimir;
  echo "<br><a href='{$URL}'>{$URL}</a><br>";
} else {
  echo "<br>Aguarde um momento - " . date("d/m/Y - H:i:s");
  echo "<br>Gravando seus dados no sistema<BR>";
  echo "<br>Este procedimento pode demorar algun tempo caso a lista de trabalhos a serem avaliados ainda seja grande<BR>";
  echo "<br><a href='{$URL}'>{$URL}</a><br>";
  echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $URL . "\">";
}

<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/inscritos-seguranca.php");
#-------------------------------------------
$debug = false;

@$fws_['data_alteracao']  = Date("YmdHi");
@$fws_['data_inclusao']   = Date("YmdHi");
#@$fws_['status']         = "ATIVADO";
@$fws_['session_id']      = $_SESSION['_cod_usuario'];
@$fws_['http_user_agent'] = $_SERVER["HTTP_USER_AGENT"];
@$fws_['ip_cliente']      = $_SERVER["REMOTE_ADDR"]; 
$fws_['dat_alter'] = date('Y-m-d H:i:s');
$fws_['res_alter'] = $_SESSION['_cod_usuario'];
$fws_['ip_alter']  = $_SERVER['REMOTE_ADDR'];

if($fws_['cpf']){
    $fws_['cpf'] = limparCpfCnpj($fws_['cpf']);
}
if($fws_['cpf_recibo']){
    $fws_['cpf_recibo'] = limparCpfCnpj($fws_['cpf_recibo']);
}
if(!$fws_['institucional']){
    $fws_['institucional'] = 0;
}
#-------------------------------------------
if (!$Conexao_db = FWA_Conexao("OpcTec")) { //Conectando-se ao banco de dados
    $MSG = "Erro-de-Conexao";
    $_SESSION['MSG'] = $MSG;
    $CONTENT = "view/admin/inscritos-cadastro-ok.php";
    $URL = WB_URL_VIEW."?content=".$CONTENT."&cod_inscrito=".$cod_inscrito;
}else{
    $var_sql = "
        INSERT INTO tab_inscritos_atualizados
        SELECT *
        FROM tab_inscritos
        WHERE (cod_inscrito=".$fws_['cod_inscrito'].")and(cod_evento =".$fws_['cod_evento'].")
        ";
    if($RS = FWA_Executa_SQL($debug, $Conexao_db, $var_sql)){
        try{
            $objInscrito = new Inscrito();
            $objInscrito->setCodInscrito($fws_['cod_inscrito']);
            $objInscrito->getInscritoById($debug);
            $objInscrito->setAll($fws_);
            $objInscrito->salvar($debug);
        } catch (OKException $exc) {
            $MSG = "Operação-Executada-com-Sucesso";
            $_SESSION['MSG'] = $MSG;
            $CONTENT = "view/admin/inscritos-cadastro-ok.php";
            $URL = WB_URL_VIEW."?content=".$CONTENT."&cod_inscrito=".$fws_['cod_inscrito'];
        } catch (Exception $exc) {
            $MSG = "Erro-de-Execucao-ERRO-SQL-Update";
            $_SESSION['MSG'] = $MSG;
            $CONTENT = "view/admin/inscritos-cadastro-ok.php";
            $URL = WB_URL_VIEW."?content=".$CONTENT."&cod_inscrito=".$fws_['cod_inscrito'];
        }
    }else{
        $MSG = "Erro-de-Execucao-ERRO-SQL-Backup";
        $_SESSION['MSG'] = $MSG;
        $CONTENT = "view/admin/inscritos-cadastro-ok.php";
        $URL = WB_URL_VIEW."?content=".$CONTENT."&cod_inscrito=".$fws_['cod_inscrito'];
    }
}
#--------------------------------------------------------------
$MSG_Imprimir = $MSG;

$loc   = "/--/";
$subst = "<br>";
$MSG_Imprimir = preg_replace($loc,$subst,$MSG_Imprimir);

$loc   = "/-/";
$subst = " ";
$MSG_Imprimir = preg_replace($loc,$subst,$MSG_Imprimir);
#--------------------------------------------------------------
if($debug==true){
  echo $MSG_Imprimir;
  echo "<br><a href='{$URL}'>{$URL}</a><br>";
}else{
  echo "<br>Aguarde um momento - ".date("d/m/Y - H:i:s");
  echo "<br>Gravando seus dados no sistema<BR>";
  echo "<br>Este procedimento pode demorar algun tempo caso a lista de trabalhos a serem avaliados ainda seja grande<BR>";
  echo "<br><a href='{$URL}'>{$URL}</a><br>";
  echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=".$URL."\">";
}

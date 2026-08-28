<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/area-tematica-seguranca.php");
#-------------------------------------------
$FWS_Debug = false;
$FWS_ = processPostArray($FWS_Debug);
#-------------------------------------------
#$acao     = "INSERIR";
$FWS_Data_Ini  = "200604010001"; #aaaammddhhii
$FWS_Data_Fim  = "200606232359"; #aaaammddhhii
$FWS_Data_Hoje = Date('YmdHi');

@$fws_['data_alteracao']  = Date("YmdHi");
@$fws_['data_inclusao']   = Date("YmdHi");
@$fws_['status']          = "ATIVADO";
@$fws_['session_id']      = session_id();
@$fws_['http_user_agent'] = $_SERVER["HTTP_USER_AGENT"];
@$fws_['ip_cliente']      = $_SERVER["REMOTE_ADDR"]; 

$fws_['data_ini'] = $fws_['data_ini_dia']."/".$fws_['data_ini_mes']."/".$fws_['data_ini_ano'];
$fws_['data_fim'] = $fws_['data_fim_dia']."/".$fws_['data_fim_mes']."/".$fws_['data_fim_ano'];

$fws_['horario_ini'] = $fws_['horario_ini_hora'].":".$fws_['horario_ini_minuto'];
$fws_['horario_fim'] = $fws_['horario_fim_hora'].":".$fws_['horario_fim_minuto'];

If (Strlen($fws_['data_ini'])<10){
  $fws_['data_ini'] = " ";
}

If (Strlen($fws_['data_fim'])<10){
  $fws_['data_fim'] = " ";
}

If (Strlen($fws_['horario_ini'])<5){
  $fws_['horario_ini'] = " ";
}

If (Strlen($fws_['horario_fim'])<5){
  $fws_['horario_fim'] = " ";
}
//I: ---------------------------------------
$Var_Sql = ""
.""
.$fws_['cod_evento']        ." ,'"
.$fws_['status']            ."','"
.$fws_['titulo']            ."','"
.$fws_['nome_ministrante']  ."','"
.$fws_['e_mail_ministrante']."','"
.$fws_['fone_ministrante']  ."','"
.$fws_['local']             ."','"
.$fws_['carga_horaria']     ."','"
.$fws_['numero_vagas']      ."','"
.$fws_['horario_ini']       ."','"
.$fws_['horario_fim']       ."','"
.$fws_['data_ini']          ."','"
.$fws_['data_fim']          ."','"
.$fws_['palavras_chave']    ."','"
.$fws_['publico_alvo']      ."','"
.$fws_['objetivo']          ."','"
.$fws_['pre_requisito']     ."','"
.$fws_['ementa']            ."','"
.$fws_['descricao']         ."','"
.$fws_['observacao']        ."','"
.$fws_['n_max_trab_por_avaliador']."','"
.@$fws_['http_user_agent']."'"
."";
//-------------------------------------------
#ipcliente

//I: ---------------------------------------
$Var_Campos = ""
."cod_evento        ,"
."status            ,"
."titulo            ,"
."nome_ministrante  ,"
."e_mail_ministrante,"
."fone_ministrante  ,"
."local             ,"
."carga_horaria     ,"
."numero_vagas      ,"
."horario_ini       ,"
."horario_fim       ,"
."data_ini          ,"
."data_fim          ,"
."palavras_chave    ,"
."publico_alvo      ,"
."objetivo          ,"
."pre_requisito     ,"
."ementa            ,"
."descricao         ,"
."observacao        ,"
."n_max_trab_por_avaliador,"
."http_user_agent    "
."";

#-------------------------------------------
#echo $Var_Sql."<br>".$Var_Campos."<br>";

If (!$Conexao_db = FWA_Conexao("OpcTec")) { //Conectando-se ao banco de dados

  $MSG     = "Erro-de-Conexao";
  $_SESSION['MSG'] = $MSG;
  $CONTENT = "view/admin/area-tematica-cadastro-ok.php";
  $URL     = "index.php?content=".$CONTENT."&cod_area_tematica=".$fws_['cod_area_tematica'];

}Else{

     $consql  = "
                 Insert Into tab_opcoes_areas_tematicas (".$Var_Campos.")
                 Values (".$Var_Sql.")
                ";

  #echo $consql;

  If (!$Conexao_db->Execute($consql)) { //Executando o Insert

    $MSG     = "Erro-de-Execucao-ERRO-SQL";
    $_SESSION['MSG'] = $MSG;
    $CONTENT = "view/admin/area-tematica-cadastro-ok.php";
    $URL = "index.php?content=".$CONTENT."&cod_area_tematica=".$fws_['cod_area_tematica'];

  }Else{

    $consql = "SELECT cod_evento, cod_area_tematica FROM tab_opcoes_areas_tematicas WHERE (cod_evento=".$fws_['cod_evento'].")and(titulo='".$fws_['titulo']."')and(e_mail_ministrante='".$fws_['e_mail_ministrante']."') ORDER BY cod_area_tematica DESC";

    $RS = $Conexao_db->Execute($consql);
    $fws_['cod_evento']  = $RS->fields[0];
    $fws_['cod_area_tematica'] = $RS->fields[1];

    $MSG     = "Operação-Executada-com-Sucesso";
    $_SESSION['MSG'] = $MSG;
    $CONTENT = "view/admin/area-tematica-cadastro-ok.php";
    $URL = "index.php?content=".$CONTENT."&cod_area_tematica=".$fws_['cod_area_tematica'];

  }
}
FWA_Mostrar_Mensagem($MSG);

$URL = "http://".FWA_Dominio()."/".FWA_URL_Prefixo()."/".$URL;
echo "<br><a href='{$URL}'>{$URL}</a><br>";
echo  "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=".$URL."\"></HTML>";

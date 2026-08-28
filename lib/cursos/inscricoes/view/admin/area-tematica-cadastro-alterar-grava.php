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

@$FWS_['data_alteracao']  = Date("YmdHi");
@$FWS_['data_inclusao']   = Date("YmdHi");
@$FWS_['status']          = "ATIVADO";
@$FWS_['session_id']      = session_id();
@$FWS_['http_user_agent'] = $_SERVER["HTTP_USER_AGENT"];
@$FWS_['ip_cliente']      = $_SERVER["REMOTE_ADDR"]; 

$FWS_['data_ini'] = $FWS_['data_ini_dia']."/".$FWS_['data_ini_mes']."/".$FWS_['data_ini_ano'];
$FWS_['data_fim'] = $FWS_['data_fim_dia']."/".$FWS_['data_fim_mes']."/".$FWS_['data_fim_ano'];

$FWS_['horario_ini'] = $FWS_['horario_ini_hora'].":".$FWS_['horario_ini_minuto'];
$FWS_['horario_fim'] = $FWS_['horario_fim_hora'].":".$FWS_['horario_fim_minuto'];

If (Strlen($FWS_['data_ini'])<10){
  $FWS_['data_ini'] = " ";
}

If (Strlen($FWS_['data_fim'])<10){
  $FWS_['data_fim'] = " ";
}

If (Strlen($FWS_['horario_ini'])<5){
  $FWS_['horario_ini'] = " ";
}

If (Strlen($FWS_['horario_fim'])<5){
  $FWS_['horario_fim'] = " ";
}
#-------------------------------------------

#echo $Var_Sql."<br>".$Var_Campos."<br>";

If (!$Conexao_db = FWA_Conexao("OpcTec")) { //Conectando-se ao banco de dados

  $MSG     = "Erro-de-Conexao";
  $CONTENT = "view/admin/area-tematica-cadastro-ok.php";
  $_SESSION['MSG'] = $MSG;
  $URL = "index.php?content=".$CONTENT."&cod_area_tematica=".$cod_area_tematica;

}Else{

  $consql  = "
              UPDATE tab_opcoes_areas_tematicas SET
                status                  ='".$FWS_['status']                  ."',
                titulo                  ='".$FWS_['titulo']                  ."',
                nome_ministrante        ='".$FWS_['nome_ministrante']        ."',
                e_mail_ministrante      ='".$FWS_['e_mail_ministrante']      ."',
                fone_ministrante        ='".$FWS_['fone_ministrante']        ."',
                local                   ='".$FWS_['local']                   ."',
                carga_horaria           ='".$FWS_['carga_horaria']           ."',
                numero_vagas            ='".$FWS_['numero_vagas']            ."',
                horario_ini             ='".$FWS_['horario_ini']             ."',
                horario_fim             ='".$FWS_['horario_fim']             ."',
                data_ini                ='".$FWS_['data_ini']                ."',
                data_fim                ='".$FWS_['data_fim']                ."',
                palavras_chave          ='".$FWS_['palavras_chave']          ."',
                publico_alvo            ='".$FWS_['publico_alvo']            ."',
                objetivo                ='".$FWS_['objetivo']                ."',
                pre_requisito           ='".$FWS_['pre_requisito']           ."',
                ementa                  ='".$FWS_['ementa']                  ."',
                descricao               ='".$FWS_['descricao']               ."',
                observacao              ='".$FWS_['observacao']              ."',
                n_max_trab_por_avaliador='".$FWS_['n_max_trab_por_avaliador']."',
                http_user_agent         ='".$FWS_['http_user_agent']."'
              WHERE (cod_area_tematica=".$FWS_['cod_area_tematica'].")and(cod_evento =".$FWS_['cod_evento'].")
             ";

  #echo $consql;

  If (!$Conexao_db->Execute($consql)) { //Executando o Insert

    $MSG     = "Erro-de-Execucao-ERRO-SQL";
    $CONTENT = "view/admin/area-tematica-cadastro-ok.php";
    $_SESSION['MSG'] = $MSG;
    $URL = "index.php?content=".$CONTENT."&cod_area_tematica=".$FWS_['cod_area_tematica'];

  }Else{

    $MSG     = "Operação-Executada-com-Sucesso";
    $_SESSION['MSG'] = $MSG;
    $CONTENT = "view/admin/area-tematica-cadastro-ok.php";
    $URL = "index.php?content=".$CONTENT."&cod_area_tematica=".$FWS_['cod_area_tematica'];

  }
}

$loc   = "/-/";
$subst = " ";
echo preg_replace($loc,$subst,$MSG)." ,aguarde um momento - ".date("d/m/Y - H:i:s");

$URL = "http://".FWA_Dominio()."/".FWA_URL_Prefixo()."/".$URL;
echo  "<br><a href='{$URL}'>{$URL}</a><br>";
echo  "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=".$URL."\"></HTML>";

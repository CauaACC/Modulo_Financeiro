<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/inscritos-seguranca.php");
#-------------------------------------------
$FWS_Debug = false;
$FWS_ = processPostArray($FWS_Debug);
#-------------------------------------------
@$FWS_['cod_evento']      = COD_EVENTO;
@$FWS_['data_alteracao']  = Date("YmdHi");
@$FWS_['data_inclusao']   = Date("YmdHi");
@$FWS_['status']          = "ATIVADA";
@$FWS_['session_id']      = $_SESSION['_cod_usuario'];
@$FWS_['http_user_agent'] = $_SERVER["HTTP_USER_AGENT"];
@$FWS_['ip_cliente']      = $_SERVER["REMOTE_ADDR"]; 

#echo $acao."-<BR>";
#-------------------------------------------

$Var_Sql = ""
.""
.$FWS_['cod_evento'               ].","
.$FWS_['cod_inscrito'             ].","
.$FWS_['cod_area_tematica'        ].",'"
.$FWS_['autor_apresentador_nome'  ]."','"
.$FWS_['autor_apresentador_e_mail']."','"
.$FWS_['nome_co_autor_1'          ]."','"
.$FWS_['nome_co_autor_2'          ]."','"
.$FWS_['nome_co_autor_3'          ]."','"
.$FWS_['nome_co_autor_4'          ]."','"
.$FWS_['nome_co_autor_5'          ]."','"
.$FWS_['nome_co_autor_6'          ]."','"
.$FWS_['instituicao_trabalho'     ]."','"
.$FWS_['titulo_trabalho'          ]."','"
.$FWS_['palavras_chave'           ]."','"
.$FWS_['resumo_trabalho'          ]."','"
.$FWS_['data_inclusao'            ]."','"
.$FWS_['status'                   ]."','"
.$FWS_['session_id'               ]."','"
.$FWS_['http_user_agent'          ]."','"
.$FWS_['ip_cliente'               ]."'"
."";
//-------------------------------------------

//I: ---------------------------------------
$Var_Campos = ""
."cod_evento                ,"
."cod_inscrito              ,"
."cod_area_tematica         ,"
."autor_apresentador_nome   ,"
."autor_apresentador_e_mail ,"
."nome_co_autor_1           ,"
."nome_co_autor_2           ,"
."nome_co_autor_3           ,"
."nome_co_autor_4           ,"
."nome_co_autor_5           ,"
."nome_co_autor_6           ,"
."instituicao_trabalho      ,"
."titulo_trabalho           ,"
."palavras_chave            ,"
."resumo_trabalho           ,"
."data_inclusao            ,"
."status                    ,"
."session_id                ,"
."http_user_agent           ,"
."ip_cliente                ";

#-------------------------------------------
#echo $Var_Sql."<br>".$Var_Campos."<br>";

If (!$Conexao_db = FWA_Conexao("OpcTec")) { //Conectando-se ao banco de dados

  $MSG     = "Erro-de-Conexao";
  $_SESSION['MSG']=$MSG;
  $CONTENT = "view/admin/inscritos-cadastro-ok.php";
  $URL     = WB_URL_VIEW."?content=".$CONTENT."&cod_inscrito=".$cod_inscrito;

}Else{

  $var_sql= "
             Insert Into tab_trabalhos (".$Var_Campos.")
             Values (".$Var_Sql.")
            ";

  If($RS=FWA_Executa_SQL($FWS_Debug,$Conexao_db,$var_sql)){
      $MSG     = "Operação-Executada-com-Sucesso--Inscrito-incluído-com-sucesso";
      $_SESSION['MSG']=$MSG;
      $CONTENT = "view/admin/inscritos-cadastro-ok.php";
      $URL     = WB_URL_VIEW."?content=".$CONTENT."&cod_inscrito=".$FWS_['cod_inscrito'];
  }Else{
    $MSG     = "Erro-de-Execucao-ERRO-SQL-Insert";
    $_SESSION['MSG']=$MSG;
    $CONTENT = "view/admin/inscritos-cadastro-ok.php";
    $URL     = WB_URL_VIEW."?content=".$CONTENT."&cod_inscrito=".$FWS_['cod_inscrito'];
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
If($FWS_Debug==true){
  echo $MSG_Imprimir;
  echo "<br><a href='{$URL}'>{$URL}</a><br>";
}Else{
  echo "<br>Aguarde um momento - ".date("d/m/Y - H:i:s");
  echo "<br>Gravando seus dados no sistema<BR>";
  echo "<br>Este procedimento demora poucos segundos.<br>";
  echo "<br><a href='{$URL}'>{$URL}</a><br>";
  echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=".$URL."\">";
}


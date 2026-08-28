<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/inscritos-seguranca.php");
#-------------------------------------------
$FWS_Debug = false;
$FWS_ = processPostArray($FWS_Debug);
#-------------------------------------------
@$FWS_['data_alteracao']  = Date("YmdHi");
@$FWS_['data_inclusao']   = Date("YmdHi");
#@$FWS_['status']         = "ATIVADA";
@$FWS_['session_id']      = $_SESSION['_cod_usuario'];
@$FWS_['http_user_agent'] = $_SERVER["HTTP_USER_AGENT"];
@$FWS_['http_user_agent_alteracao'] = $_SERVER["HTTP_USER_AGENT"];
@$FWS_['ip_cliente']      = $_SERVER["REMOTE_ADDR"]; 
@$FWS_['ip_alteracao']      = $_SERVER["REMOTE_ADDR"]; 

#echo $acao."-<BR>";
#-------------------------------------------

If (!$Conexao_db = FWA_Conexao("OpcTec")) { //Conectando-se ao banco de dados

  $MSG     = "Erro-de-Conexao";
  $_SESSION['MSG']=$MSG;
  $CONTENT = "view/admin/inscritos-cadastro-ok.php";
  $URL     = WB_URL_VIEW."?content=".$CONTENT."&cod_inscrito=".$cod_inscrito;

}Else{

  $var_sql = "
               INSERT INTO tab_trabalhos_atualizados
               SELECT *
               FROM tab_trabalhos
               WHERE (cod_trabalho=".$FWS_['cod_trabalho'].")and(cod_evento =".$FWS_['cod_evento'].")
             ";
             
  If($RS=FWA_Executa_SQL($FWS_Debug,$Conexao_db,$var_sql)){
    $FWS_Execucao = true;
    $var_sql = "
                 INSERT INTO tab_trabalhos_avaliacao_atualizados
                 SELECT *
                 FROM tab_trabalhos_avaliacao
                 WHERE (cod_trabalho=".$FWS_['cod_trabalho'].")and(cod_evento =".$FWS_['cod_evento'].")
               ";

    If($RS=FWA_Executa_SQL($FWS_Debug,$Conexao_db,$var_sql)){
      $FWS_Execucao = true;
    }Else{
      $FWS_Execucao=false;
      $MSG     = "Erro-de-Execucao-ERRO-SQL-Backup-Avaliacao";
      $_SESSION['MSG']=$MSG;
      $CONTENT = "view/admin/inscritos-cadastro-ok.php";
      $URL = WB_URL_VIEW."?content=".$CONTENT."&cod_trabalho=".$FWS_['cod_trabalho']."&cod_inscrito=".$FWS_['cod_inscrito'];
    }      
  }Else{
    $FWS_Execucao=false;
    $MSG     = "Erro-de-Execucao-ERRO-SQL-Backup-Trabalho";
    $_SESSION['MSG']=$MSG;
    $CONTENT = "view/admin/inscritos-cadastro-ok.php";
    $URL = WB_URL_VIEW."?content=".$CONTENT."&cod_trabalho=".$FWS_['cod_trabalho']."&cod_inscrito=".$FWS_['cod_inscrito'];
  }  
  
  If($FWS_Execucao==true){
    $var_sql = "
              UPDATE tab_trabalhos SET
                cod_inscrito              ='".$FWS_['cod_inscrito'              ]."',
                cod_area_tematica         ='".$FWS_['cod_area_tematica'         ]."',
                autor_apresentador_nome   ='".$FWS_['autor_apresentador_nome'   ]."',
                autor_apresentador_e_mail ='".$FWS_['autor_apresentador_e_mail' ]."',
                nome_co_autor_1           ='".$FWS_['nome_co_autor_1'           ]."',
                nome_co_autor_2           ='".$FWS_['nome_co_autor_2'           ]."',
                nome_co_autor_3           ='".$FWS_['nome_co_autor_3'           ]."',
                nome_co_autor_4           ='".$FWS_['nome_co_autor_4'           ]."',
                nome_co_autor_5           ='".$FWS_['nome_co_autor_5'           ]."',
                nome_co_autor_6           ='".$FWS_['nome_co_autor_6'           ]."',
                instituicao_trabalho      ='".$FWS_['instituicao_trabalho'      ]."',
                titulo_trabalho           ='".$FWS_['titulo_trabalho'           ]."',
                palavras_chave            ='".$FWS_['palavras_chave'            ]."',
                resumo_trabalho           ='".$FWS_['resumo_trabalho'           ]."',
                data_alteracao            ='".$FWS_['data_alteracao'            ]."',
                status                    ='".$FWS_['status'                    ]."',
                session_id                ='".$FWS_['session_id'                ]."',
                http_user_agent           ='".$FWS_['http_user_agent'           ]."',
                ip_cliente                ='".$FWS_['ip_cliente'                ]."',
                tipo_documento            ='".$FWS_['tipo_documento'            ]."'
              WHERE (cod_trabalho=".$FWS_['cod_trabalho'].")and(cod_evento =".$FWS_['cod_evento'].")
             ";

    If($RS=FWA_Executa_SQL($FWS_Debug,$Conexao_db,$var_sql)){
      $FWS_Execucao=true;
      $var_sql = "
                UPDATE tab_trabalhos_avaliacao SET
                  cod_inscrito              ='".$FWS_['cod_inscrito'              ]."',
                  cod_area_tematica         ='".$FWS_['cod_area_tematica'         ]."',
                  data_alteracao            ='".$FWS_['data_alteracao'            ]."',
                  status                    ='".$FWS_['status'                    ]."',
                  session_id                ='".$FWS_['session_id'                ]."',
                  http_user_agent_alteracao ='".$FWS_['http_user_agent_alteracao'           ]."',
                  ip_alteracao                ='".$FWS_['ip_alteracao'                ]."'
                WHERE (cod_trabalho=".$FWS_['cod_trabalho'].")and(cod_evento =".$FWS_['cod_evento'].")
               ";

      If($RS=FWA_Executa_SQL($FWS_Debug,$Conexao_db,$var_sql)){
        $FWS_Execucao=true;
        $MSG     = "Operação-Executada-com-Sucesso";
        $_SESSION['MSG']=$MSG;
        $CONTENT = "view/admin/inscritos-cadastro-ok.php";
        $URL = WB_URL_VIEW."?content=".$CONTENT."&cod_trabalho=".$FWS_['cod_trabalho']."&cod_inscrito=".$FWS_['cod_inscrito'];
      }Else{
        $FWS_Execucao=false;
        $MSG     = "Erro-de-Execucao-ERRO-SQL-Update-Avaliacao";
        $_SESSION['MSG']=$MSG;
        $CONTENT = "view/admin/inscritos-cadastro-ok.php";
        $URL = WB_URL_VIEW."?content=".$CONTENT."&cod_trabalho=".$FWS_['cod_trabalho']."&cod_inscrito=".$FWS_['cod_inscrito'];
      }
      $var_sql = "
                UPDATE tab_historico_avaliacao SET
                  cod_inscrito              ='".$FWS_['cod_inscrito'              ]."',
                  cod_area_tematica         ='".$FWS_['cod_area_tematica'         ]."',
                  data_alteracao            ='".$FWS_['data_alteracao'            ]."',
                  status                    ='".$FWS_['status'                    ]."',
                  session_id                ='".$FWS_['session_id'                ]."',
                  http_user_agent_alteracao ='".$FWS_['http_user_agent_alteracao'           ]."',
                  ip_alteracao                ='".$FWS_['ip_alteracao'                ]."'
                WHERE (cod_trabalho=".$FWS_['cod_trabalho'].")and(cod_evento =".$FWS_['cod_evento'].")
               ";

      If($RS=FWA_Executa_SQL($FWS_Debug,$Conexao_db,$var_sql)){
        $FWS_Execucao=true;
        $MSG     = "Operação-Executada-com-Sucesso";
        $_SESSION['MSG']=$MSG;
        $CONTENT = "view/admin/inscritos-cadastro-ok.php";
        $URL = WB_URL_VIEW."?content=".$CONTENT."&cod_trabalho=".$FWS_['cod_trabalho']."&cod_inscrito=".$FWS_['cod_inscrito'];
      }Else{
        $FWS_Execucao=false;
        $MSG     = "Erro-de-Execucao-ERRO-SQL-Update-Avaliacao";
        $_SESSION['MSG']=$MSG;
        $CONTENT = "view/admin/inscritos-cadastro-ok.php";
        $URL = WB_URL_VIEW."?content=".$CONTENT."&cod_trabalho=".$FWS_['cod_trabalho']."&cod_inscrito=".$FWS_['cod_inscrito'];
      }
    }Else{
      $FWS_Execucao=false;
      $MSG     = "Erro-de-Execucao-ERRO-SQL-Update-Trabalho";
      $_SESSION['MSG']=$MSG;
      $CONTENT = "view/admin/inscritos-cadastro-ok.php";
      $URL = WB_URL_VIEW."?content=".$CONTENT."&cod_trabalho=".$FWS_['cod_trabalho']."&cod_inscrito=".$FWS_['cod_inscrito'];
    }
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
  echo "<br>Gravando seus dados no sistema<br>";
  echo "<br>Este procedimento demora poucos segundos.<br>";
  echo "<br><a href='{$URL}'>{$URL}</a><br>";
  echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=".$URL."\">";
}
?>

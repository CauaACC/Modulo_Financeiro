<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/re-avaliacao-trabalhos-seguranca.php");
  
  $COD_INSCRITO      = $_POST['cod_inscrito'];
  $cod_trabalho      = $_POST['cod_trabalho'];
  $cod_area_tematica = $_POST['cod_area_tematica'];
  $cod_avaliador     = $_SESSION['_cod_usuario'];
  $avaliador         = $_POST['avaliacao'];
  $OBSERVACAO        = $_POST['observacao'];

  If($OBSERVACAO==""){ $OBSERVACAO= NULL;}
  

  $acao              = $_POST['acao'];

  $SESSION_ID        = $_SESSION['_login'];
  $DATA_INCLUSAO     = Date('YmdHi');
  $DATA_ALTERACAO    = Date('YmdHi');
  
  $FWS_Debug=false;
  
  @$STATUS          = "ATIVADO";
  @$HTTP_USER_AGENT = $_SERVER["HTTP_USER_AGENT"];
  @$IP_CLIENTE      = $_SERVER["REMOTE_ADDR"];   

#-------------------------------------------
  If (!$Conexao_db = FWA_Conexao("OpcTec")) { #Conectando-se ao banco de dados

     $MSG          = "Operação-Não-Executada,-Erro-de-Conexão";
     $_SESSION['MSG']=$MSG;
     $CONTENT      = "view/admin/erro.php";
     $URL          = WB_URL_VIEW."?content=".$CONTENT;
     $FWA_Execucao = false;

  }Else{

    If($FWS_Debug==1){ echo  $acao."<br><br>"; }
    If($acao=="Avaliação") {
      
      $var_sql = "SELECT cod_trabalho
                  FROM tab_trabalhos_avaliacao
                  WHERE (cod_trabalho = ".$cod_trabalho.")
                    and (cod_evento   = ".COD_EVENTO.")
                 ";
      
      $RS=FWA_Executa_SQL($FWS_Debug,$Conexao_db,$var_sql); //Consulta login existe   
      
      If((!$RS->EOF)AND($RS->fields[0]==$cod_trabalho)){
  
        $var_sql = "
                    UPDATE tab_trabalhos_avaliacao SET
                       cod_avaliador  = ".$cod_avaliador." 
                      ,avaliacao      = '".$avaliador."'
                      ,observacao     = '".$OBSERVACAO."' 
                      ,status         = '".$STATUS."'
                      ,data_alteracao = '".$DATA_ALTERACAO."'
                      ,session_id     = '".$SESSION_ID."' 
                      ,http_user_agent_alteracao= '".$HTTP_USER_AGENT."' 
                      ,ip_alteracao     = '".$IP_CLIENTE."'
                    WHERE (cod_area_tematica = ".$cod_area_tematica.")
                       and(cod_evento        = ".COD_EVENTO.")
                       and(cod_trabalho      = ".$cod_trabalho.")
             ";      
      
      }Else{
      
        $var_sql = "
                    Insert Into
                    tab_trabalhos_avaliacao
                    (cod_evento
                    ,cod_area_tematica
                    ,cod_trabalho
                    ,cod_inscrito
                    ,cod_avaliador
                    ,avaliacao
                    ,observacao
                    ,status          
                    ,data_inclusao  
                    ,session_id      
                    ,http_user_agent_inclusao 
                    ,ip_inclusao      
                    )Values(
                     ".COD_EVENTO."
                    ,".$cod_area_tematica."
                    ,".$cod_trabalho."
                    ,".$COD_INSCRITO."
                    ,".$cod_avaliador."
                    ,'".$avaliador."'
                    ,'".$OBSERVACAO."'
                    ,'ATIVADA'
                    ,'".$DATA_INCLUSAO."'
                    ,'".$SESSION_ID."'
                    ,'".$_SERVER["HTTP_USER_AGENT"]."'
                    ,'".$_SERVER["REMOTE_ADDR"]."'    
                    )
                   ";
      }
      
      If($FWS_Debug==true){ echo "<br>".$var_sql."<br>"; }
      If(!$Conexao_db->Execute($var_sql)){
        $MSG          = "Operação-Não-Executada,--Ocorreu-um-Erro_durante-a-".$acao."--Talvez-o-trabalho-ja-tenha-sido-avaliado,--aperte-a-tecla-F5-para-atualizar-a-pagina-de-trabalhos-a-serem-avaliados.--";
        $_SESSION['MSG']=$MSG;
        $CONTENT      = "view/admin/re-avaliacao-trabalhos-listagem.php";
        $URL          = WB_URL_VIEW."?content=".$CONTENT."&cod_area_tematica=".$cod_area_tematica;
        $FWA_Execucao = false;
      }Else{
        $MSG          = "Operação-de-".$acao."-Executada-Codigo-trabalho:".$cod_trabalho."--";
        $_SESSION['MSG']=$MSG;
        $CONTENT      = "view/admin/re-avaliacao-trabalhos-listagem.php";
        $URL          = WB_URL_VIEW."?content=".$CONTENT."&cod_area_tematica=".$cod_area_tematica;
        $FWA_Execucao = true;           
      }
    }Else{
      $MSG          = "Operação-Não-Executada,-Acao-Diferente-Ativar--";
      $_SESSION['MSG']=$MSG;
      $CONTENT      = "view/admin/re-avaliacao-trabalhos-listagem.php";
      $URL          = WB_URL_VIEW."?content=".$CONTENT."&cod_area_tematica=".$cod_area_tematica;
      $FWA_Execucao = false;
    }
  }
#--------------------------------------------------------------

FWA_Mostrar_Mensagem($MSG);
#--------------------------------------------------------------
If($FWS_Debug==true){
  echo $MSG_Imprimir;
  echo "<br><a href='{$URL}'>{$URL}</a><br>";
}Else{
  echo "<br>Aguarde um momento - ".date("d/m/Y - H:i:s");
  echo "<br>Gravando seus dados no sistema<BR>";
  echo "<br>Este procedimento pode demorar algun tempo caso a lista de trabalhos a serem avaliados ainda seja grande<BR>";
  echo "<br><a href='{$URL}'>{$URL}</a><br>";
  echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=".$URL."\">";
}


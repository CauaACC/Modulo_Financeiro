<?php
session_start();
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/trabalhos-cadastro-seguranca.php");

$cod_area_tematica_atual    = $_POST['cod_area_tematica_atual'];
$cod_trabalho               = $_POST['cod_trabalho'];
$cod_area_tematica_nova     = $_POST['cod_area_tematica_nova'];
$autor_apresentador_nome    = $_POST['autor_apresentador_nome'];
$autor_apresentador_e_mail  = $_POST['autor_apresentador_e_mail'];
$nome_co_autor_1            = $_POST['nome_co_autor_1'];
$nome_co_autor_2            = $_POST['nome_co_autor_2'];
$nome_co_autor_3            = $_POST['nome_co_autor_3'];
$nome_co_autor_4            = $_POST['nome_co_autor_4'];
$nome_co_autor_5            = $_POST['nome_co_autor_5'];
$instituicao_trabalho       = $_POST['instituicao_trabalho'];
$titulo_trabalho            = $_POST['titulo_trabalho'];
$palavras_chave             = $_POST['palavras_chave'];
$resumo_trabalho            = $_POST['resumo_trabalho'];

  $acao              = $_POST['acao'];

  $SESSION_ID        = $_SESSION['_login'];
  $DATA_INCLUSAO     = Date('YmdHi');
  $DATA_ALTERACAO    = Date('YmdHi');

  $FWS_Debug=true;

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
    If($acao=="Alteracao") {

       $var_sql = "
                    INSERT INTO tab_trabalhos_excluidos
                    SELECT *
                    FROM tab_trabalhos_avaliacao
                    WHERE (cod_trabalho = ".$cod_trabalho.")
                      and (cod_evento   = ".COD_EVENTO.")
                  ";
       #echo $var_sql."- <BR>";

       $RS_Backup=FWA_Executa_SQL($FWS_Debug,$Conexao_db,$var_sql); //Consulta login existe

       $var_sql = "
                    SELECT cod_trabalho
                    FROM tab_trabalhos_excluido
                    WHERE (cod_trabalho = ".$cod_trabalho.")
                      and (cod_evento   = ".COD_EVENTO.")
                  ";

       #echo $var_sql."- <BR>";

       $RS_Select=FWA_Executa_SQL($FWS_Debug,$Conexao_db,$var_sql); //Consulta login existe

       If (!$RS_select->EOF) {
         $cod_trabalho = $RS_select->fields[0];

         $var_sql = "UPDATE tab_trabalhos SET
                        cod_area_tematica         = ".$cod_area_tematica_nova."
                       ,autor_apresentador_nome   = '".$autor_apresentador_nome."'
                       ,autor_apresentador_e_mail = '".$autor_apresentador_e_mail."'
                       ,nome_co_autor_1           = '".$nome_co_autor_1."'
                       ,nome_co_autor_2           = '".$nome_co_autor_2."'
                       ,nome_co_autor_3           = '".$nome_co_autor_3."'
                       ,nome_co_autor_4           = '".$nome_co_autor_4."'
                       ,nome_co_autor_5           = '".$nome_co_autor_5."'
                       ,instituicao_trabalho      = '".$instituicao_trabalho."'
                       ,titulo_trabalho           = '".$titulo_trabalho."'
                       ,palavras_chave            = '".$palavras_chave."'
                       ,resumo_trabalho           = '".$resumo_trabalho."'
                       ,status                    = '".$STATUS."'
                       ,data_alteracao            = '".$DATA_ALTERACAO."'
                       ,session_id                = '".$SESSION_ID."'
                       ,http_user_agent           = '".$HTTP_USER_AGENT."'
                       ,ip_cliente                = '".$IP_CLIENTE."'
                    WHERE (cod_area_tematica = ".$cod_area_tematica.")
                       and(cod_evento        = ".COD_EVENTO.")
                       and(cod_trabalho      = ".$cod_trabalho.")

         ";

         FWA_Executa_SQL($FWS_Debug,$Conexao_db,$var_sql); //Consulta login e
         #echo $var_sql."- <BR>";

         $MSG          = "Operação-Executada";
         $_SESSION['MSG']=$MSG;
         $CONTENT      = "view/admin/trabalhos-cadastro-listagem.php.php";
         $URL          = "index.php?content=".$CONTENT."&cod_trabalho=".$cod_trabalho;
         $FWA_Execucao = true;

       }Else{

         $MSG          = "Operação-Não-Executada,-Ocorreu-um-Erro_ao-Excluir";
         $_SESSION['MSG']=$MSG;
         $CONTENT      = "view/admin/trabalhos-cadastro-listagem.php.php";
         $URL          = "index.php?content=".$CONTENT."&cod_trabalho=".$cod_trabalho;
         $FWA_Execucao = true;

       }

    }Else{
      $MSG          = "Operação-Não-Executada,-Acao-Diferente-Ativar--";
      $_SESSION['MSG']=$MSG;
      $CONTENT      = "view/admin/trabalhos-cadastro-listagem.php";
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

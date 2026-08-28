<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/ligacao-usuarios-grupos-seguranca.php");

$MSG = "";
$FWS_Debug=false;
@$FWS_['data_alteracao']  = Date("YmdHi");
@$FWS_['data_inclusao']   = Date("YmdHi");
@$FWS_['status']          = "ATIVADO";
@$FWS_['session_id']      = "-";#session_id();
@$FWS_['http_user_agent'] = $_SERVER["HTTP_USER_AGENT"];
@$FWS_['ip_cliente']      = $_SERVER["REMOTE_ADDR"];

echo "<table border='1'><tr><td>";

      If (!$Conexao_db = FWA_Conexao("OpcTec")) { //Conectando-se ao banco de dados
        $MSG         .="Erro-de-Conexao";
        $FWS_Execucao = false;
      }Else{
      
        $consql[0] = "Insert Into sis_sistemas (cod_sistema,nome_sistema,cod_evento)
                   Values(4,'Sistema de Consulta Andamento das Avaliação de Trabalhos',1)";
        $consql[1] = "Insert Into sis_grupos   (cod_grupo,nome_grupo,cod_evento)
                   Values(5,'Grupo de Consulta ao Andamento das Avaliações de Trabalhos',1)";
        $consql[2] = "Insert Into sis_direitos_grupos_sistemas (
                   cod_grupo,cod_sistema,cod_direito,cod_evento)
                   Values(5,4,5,1)";
        
        For($i=0;$i<count($consql);$i++){

          If (FWA_Executa_SQL($FWS_Debug,$Conexao_db,$consql[$i])) {//Se login inserido
            $MSG .="--SQL-Executador com sucesso->".$i;
            $FWS_Execucao = true;
          }Else{
            $MSG         .="Erro-de-Inserção->".$i;
            $FWS_Execucao = false;
          }
          echo "<br>".$consql[$i]."<br><br>";
        }
      }
      echo "<br>".$MSG."<br>";

    echo "</td></tr></table>";

?>
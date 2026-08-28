<?php
require_once(dirname(__FILE__)."/../../include/config.php");
#require_once(dirname(__FILE__)."/../../view/admin/inscricoes-cadastro-seguranca.php");

$MSG = "";
$FWS_Debug=false;
@$FWS_['data_alteracao']  = Date("YmdHi");
@$FWS_['data_inclusao']   = Date("YmdHi");
@$FWS_['status']          = "ATIVADO";
@$FWS_['session_id']      = "/-/";#session_id();
@$FWS_['http_user_agent'] = $_SERVER["HTTP_USER_AGENT"];
@$FWS_['ip_cliente']      = $_SERVER["REMOTE_ADDR"];


/*
#-----------------------------------------------------------------------------------------------
If($FWS_Liberar==1){
  $FWS_Grava_Arquivo = false;
}Else{
  $FWS_Grava_Arquivo = true;
}
#-----------------------------------------------------------------------------------------------
If((file_exists($FWS_Url_Arquivo))and($FWS_Grava_Arquivo == true)){
}Else{
*/
#--------------------------------------------------------------------------------------------------
/*
Parametros para abrir um arquivo
  r   -> Abre o arquivo com permissão apenas para leitura.
  r+  -> Abre o arquivo com permissão para escrita e leitura, posicionando o ponteiro no início do mesmo.
  w   -> Abre o arquivo com permissão apenas para escrita.    Se o arquivo existir, todo o conteúdo é apagado. Se não existir, o PHP tenta criá-lo. O ponteiro é posicionado no início do arquivo
  w+  -> Abre o arquivo com permissão para escrita e leitura. Se o arquivo existir, todo o conteúdo é apagado. Se não existir, o PHP tenta criá-lo. O ponteiro é posicionado no início do arquivo
  a   -> Abre o arquivo com permissão apenas para escrita.    Se o arquivo não existir, o PHP tenta criá-lo. O ponteiro é posicionado no final do arquivo
  a+  -> Abre o arquivo com permissão para escrita e leitura. Se o arquivo não existir, o PHP tenta criá-lo. O ponteiro é posicionado no final do arquivo.
*/


$FWS_Nome_Arquivo_Atual = $_SERVER["DOCUMENT_ROOT"]."/congresso/inscricoes/upload/pagamentos/Tit185086.dat";
$FWS_Nome_Arquivo_Novo  = $_SERVER["DOCUMENT_ROOT"]."/congresso/inscricoes/upload/pagamentos/Tit185086-".Date('Y')."-".Date('m')."-".Date('d').".dat";

$FWS_Url_Arquivo = $FWS_Nome_Arquivo_Atual;
#echo $FWS_Url_Arquivo."<br>";

if (file_exists($FWS_Url_Arquivo)) {

  $identificador = FOPEN($FWS_Url_Arquivo,"r");

  echo "<table border='1'>";
  While(!feof($identificador)){
    $MSG            = "";
    $FWS['campos']  = "";
    $FWS['valores'] = "";
    $i=$i+1;
    $line=fgets($identificador);
    #echo $line."<br>";
    If($line!=""){
      $line_Array = split(";",$line); //transforma uma string em um array definidos pelo ","
      $FWS_Colunas = Count($line_Array);
      
      $FWS_['numero_boleto'] = $line_Array[7];

      $FWS_['cod_inscrito']    = substr($FWS_['numero_boleto'],-8,-1);
      $FWS_['valor']           = $line_Array[9];
      $FWS_['forma_pagamento'] = "Boleto Bancário";
      

      settype($FWS_['cod_inscrito'], "integer");

      echo "<tr><td>".$i."-</td><td>".$FWS_['cod_inscrito']."</td><td>".$FWS_['valor']."</td>";

      $FWS['campos']  = "cod_inscrito, valor, forma_pagamento";
      $FWS['valores'] = $FWS_['cod_inscrito'].", ".$FWS_['valor'].", '".$FWS_['forma_pagamento']."'";

      $consql  = "SELECT cod_pagamento, cod_inscrito, valor, data_inclusao FROM tab_pagamentos WHERE (cod_inscrito=".$FWS_['cod_inscrito'].")and(campo_7='".$FWS_['numero_boleto']."')";

      For($ii=0;$ii<$FWS_Colunas;$ii++){
        If($line_Array[$ii]==""){ $line_Array[$ii]="-"; }
        $FWS['campos']  = $FWS['campos'].", campo_".$ii;
        $FWS['valores'] = $FWS['valores'].",'".$line_Array[$ii]."'";

        #echo "<td>".$line_Array[$ii]."</td>";
      }

      #-------------------------------------------

        If (!$Conexao_db = FWA_Conexao("OpcTec")) { //Conectando-se ao banco de dados
          $MSG         .="Erro-de-Conexao";
          $FWS_Execucao = false;
        }Else{

          If ($RS=FWA_Executa_SQL($FWS_Debug,$Conexao_db,$consql)) { //Consulta convidado possui login
            If($FWS_Debug==1){ echo "<br>".$RS->fields[0]."<br>"; }
            If($RS->fields[3]!=""){//Se o pagamento ja foi incluido
              $FWS_['data_inclusao_anterior']=$RS->fields[0];
              $MSG .="OK-Pagamento-Incluido-Anteriormente";
            }Else{//Se não o pagamento ja foi incluido, incluir
              $consql  = "Insert Into tab_pagamentos(
              ".$FWS['campos']."
                ,cod_evento
                ,data_inclusao
                ,status
                ,session_id
                ,http_user_agent
                ,ip_cliente
              ) Values (
              ".$FWS['valores']."
              ,1
              ,'".$FWS_['data_inclusao']."'
              ,'".$FWS_['status']."'
              ,'".$FWS_['session_id']."'
              ,'".$FWS_['http_user_agent']."'
              ,'".$FWS_['ip_cliente']."'
              )";

              If (FWA_Executa_SQL($FWS_Debug,$Conexao_db,$consql)) {//Se login inserido
                $MSG .="--OK-Pagamento-cadastrado.";
                $FWS_Execucao = true;
              }Else{
                $MSG         .="Erro-de-Inserção";
                $FWS_Execucao = false;
              }
            }
          }Else{
            $MSG         .="ERRO-Consulta-se-pagameno-ja-incluido";
            $FWS_Execucao = false;
          }
        }
      echo "<td>".$MSG."</td>";
      echo "</tr>";
    }
  }
  echo "<table>";
  FCLOSE($identificador);
  rename($FWS_Url_Arquivo_Atual,$FWS_Url_Arquivo_Novo);
}Else{
  echo "O arquivo ".$FWS_Url_Arquivo_Atual." não existe";
}
?>
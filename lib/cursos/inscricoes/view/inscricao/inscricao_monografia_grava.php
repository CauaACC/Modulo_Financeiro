<?php
require_once(dirname(__FILE__)."/../../include/config.php");

$STATUS         = "ATIVADA";
$SESSION_ID     = session_id();
$DATA_INSCRICAO = Date('Ymd H:i');
$MSG            = " ";

#--------------------------------------------------------------------------------------
#require_once("include/functions/FWA_Conexao.php");
require_once(dirname(__FILE__)."/../../include/config.php");

$data_ini = "200601180001";
$data_fim = "200612302359";

$FWA_Debug=false;

If (($data_ini<=Date("YmdHi"))and(Date("YmdHi")<=$data_fim)){
#If(FWA_Status()){

  If (!$Conexao_db = FWA_Conexao("OpcTec")) { //Conectando-se ao banco de dados

    $MSG     = $MSG."--Erro-de-Conexao";
    $FWA_Execucao = false;

  }Else{
    #-----------------------------------------------------------------------------------------
    If (($_SESSION['cod_inscrito']!="")and($_SESSION['cpf']!="")and($_SESSION['cod_premiacao_cientifica']!="")){#Testa se o campo cpf foi preenchido
      $Var_Campos = "
                      i.nome_inscrito
                      ,i.cod_inscrito
                      ,i.rg
                      ,i.cpf
                      ,i.e_mail
                      ,i.status
                     ";


      #Definindo comando SQL
      $var_sql = "
                 SELECT ".$Var_Campos."
                 FROM tab_inscritos as i
                 WHERE
                 (cod_evento        = ".COD_EVENTO.")
                 and (cod_inscrito  = ".$_SESSION['cod_inscrito'].")
                 and (cpf         = '".$_SESSION['cpf']."')
                 ORDER BY cod_inscrito DESC
                ";

      #echo $var_sql;

      #Fim da definição do comando SQL

      #Executando a consulta no banco SQL
      $RS = $Conexao_db->Execute($var_sql);

      #Verificando se houve retorno de algum registro
      If (!$RS->EOF) {#Se cpf e cod_inscrito esta inscrito
        #Adicionando nas variaveis, os valores dos campos retirados dos registros
        $nome_inscrito = $RS->fields[0];
        $cod_inscrito  = $RS->fields[1];
        $rg            = $RS->fields[2];
        $cpf           = $RS->fields[3];
        $e_mail        = $RS->fields[4];
        $status        = $RS->fields[5];


          #III: ---------------------------------------
          $Var_Sql_Dados_Monografia = ""
          .""
          .COD_EVENTO.","
          .$cod_inscrito.","
          .$_SESSION['cod_premiacao_cientifica'].",'"
          .$_SESSION['autor_nome'              ]."','"
          .$_SESSION['autor_e_mail'            ]."','"
          .$_SESSION['autor_pseudonimo'        ]."','"
          .$_SESSION['instituicao_monografia'  ]."','"
          .$_SESSION['titulo_monografia'       ]."','"
          .$_SESSION['arquivo_upload'          ]."','"
          .$DATA_INSCRICAO                      ."','"
          .$STATUS                              ."','"
          .$SESSION_ID                          ."','"
          .$_SERVER["HTTP_USER_AGENT"]          ."','"
          .$_SERVER["REMOTE_ADDR"]              ."'"
          ."";
          #-------------------------------------------
          #III: --------------------------------------
          $Var_Campos_Dados_Monografia = ""
          ."cod_evento              ,"
          ."cod_inscrito            ,"
          ."cod_premiacao_cientifica,"
          ."autor_nome              ,"
          ."autor_e_mail            ,"
          ."autor_pseudonimo        ,"
          ."instituicao_monografia  ,"
          ."titulo_monografia       ,"
          ."arquivo_upload          ,"
          ."data_inclusao           ,"
          ."status                  ,"
          ."session_id              ,"
          ."http_user_agent         ,"
          ."ip_cliente               "
          ."";

          #-------------------------------------------
          $consql  = "
                      Insert Into tab_monografias (".$Var_Campos_Dados_Monografia.")
                      Values (".$Var_Sql_Dados_Monografia.")
                     ";

          If($FWA_Debug==1){ echo $consql;}

          If (!$Conexao_db->Execute($consql)) { //Executando o Insert
            $MSG     =$MSG."--ERRO-SQL-monografia";
          }Else{
            $MSG     = $MSG."--OK--monografia";

            session_unregister(cod_premiacao_cientifica);
            session_unregister(autor_nomeo);
            session_unregister(autor_e_mail);
            session_unregister(autor_pseudonimo);
            session_unregister(instituicao_monografia);
            session_unregister(titulo_monografia);
          }

      }Else{
        $MSG = $MSG."--não-forão-encontrados-inscritos-com-CPF=".$_SESSION['cpf']."-e-COD_INSCRITO=".$_SESSION['cod_inscrito']."";
      }
    }Else{#Testa se o campo cpf foi preenchidoe
      $MSG = $MSG."--CPF-e-COD_INSCRITO-não-forão-preenchidos-inscritos";
      $CONTENT="view/inscricao/inscricao_trabalhos_inicio.php";
    }

  }

  #echo $MSG;
  $_SESSION['MSG']=$MSG;
  If(@$FWA_Execucao==1){
    $URL = "http://".FWA_Dominio()."/".FWA_URL_Prefixo()."/index.php?content=view/inscricao/inscricao_trabalhos_inicio.php";
    echo "<br>Aguarde um momento - ".date("d/m/Y - H:i:s");
    echo "<br>Gravando seus dados no sistema<BR>";
  }Else{
    $URL = "http://".FWA_Dominio()."/".FWA_URL_Prefixo()."/index.php?content=view/inscricao/inscricao_trabalhos_inicio.php";
    echo "<br>Mensagens de:<BR>";
  }

  #--------------------------------------------------------------
}Else{#Fora do periodo de inscrição
    $MSG = "--Inscrições-Encerradas!--Periodo-de-Inscrição:-".$data_ini."-até-".$data_fim."--Data-de-hoje:-".Date("YmdHi");
    $URL = "http://".FWA_Dominio()."/".FWA_URL_Prefixo()."/index.php?content=ERRO.php";
}
#--------------------------------------------------------------

FWA_Mostrar_Mensagem($MSG);
$_SESSION['MSG']=$MSG;
#--------------------------------------------------------------
If($FWA_Debug==1){
  echo $MSG_Imprimir;
  echo "<br><a href='{$URL}'>{$URL}</a><br>";
}Else{
  echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=".$URL."\">";
}

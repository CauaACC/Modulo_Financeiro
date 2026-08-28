<?php
require_once(dirname(__FILE__)."/../../include/config.php");
#require_once(dirname(__FILE__)."/../../include/inscricao_request_variaveis.php");
#Fazendo conexão com o banco
$Conexao_db = FWA_Conexao("OpcTec");
$debug = false;

$STATUS = "ATIVADA";
$SESSION_ID = session_id();

If ($OP_SUBMISSAO_TRABALHO) {

    #-----------------------------------------------------------------------------------------
    If (($_SESSION['cod_inscrito'] != "")and ( $_SESSION['cpf'] != "")and ( $_SESSION['cod_area_tematica'] != "")) {#Testa se o campo cpf foi preenchido
        $var_sql = "
                 SELECT  i.nome_inscrito
                      ,i.cod_inscrito
                      ,i.rg
                      ,i.cpf
                      ,i.e_mail
                      ,i.status
                      ,(SELECT Count(t.cod_trabalho) FROM tab_trabalhos as t, tab_opcoes_areas_tematicas as oat
                       WHERE
                           (t.cod_evento        = " . COD_EVENTO . ")
                       and (t.cod_inscrito      = " . $_SESSION['cod_inscrito'] . ")
                       and (t.cod_area_tematica = oat.cod_area_tematica)) as total
                 FROM tab_inscritos as i
                 WHERE
                 (cod_evento        = " . COD_EVENTO . ")
                 and (cod_inscrito  = " . $_SESSION['cod_inscrito'] . ")
                 and (cpf         = '" . $_SESSION['cpf'] . "')               
                 ORDER BY cod_inscrito DESC
                ";

        $Conexao_db->debug = $debug;
        $RS = $Conexao_db->Execute($var_sql);

        #Verificando se houve retorno de algum registro
        If (!$RS->EOF) {#Se cpf Esta inscrito e cod_inscrito esta inscrito
            #Adicionando nas variaveis, os valores dos campos retirados dos registros
            $nome_inscrito = $RS->fields[0];
            $cod_inscrito = $RS->fields[1];
            $rg = $RS->fields[2];
            $cpf = $RS->fields[3];
            $e_mail = $RS->fields[4];
            $status = $RS->fields[5];
            $total = $RS->fields[6];

            If ($total < 3) {

                #III: ---------------------------------------
                $Var_Sql_Dados_Trabalho = ""
                        . ""
                        . COD_EVENTO . ","
                        . $cod_inscrito . ","
                        . $_SESSION['cod_area_tematica'] . ",'"
                        . $_SESSION['autor_apresentador_nome'] . "','"
                        . $_SESSION['autor_apresentador_e_mail'] . "','"
                        . $_SESSION['nome_co_autor_1'] . "','"
                        . $_SESSION['nome_co_autor_2'] . "','"
                        . $_SESSION['nome_co_autor_3'] . "','"
                        . $_SESSION['nome_co_autor_4'] . "','"
                        . $_SESSION['nome_co_autor_5'] . "','"
                        . $_SESSION['nome_co_autor_6'] . "','"
                        . $_SESSION['instituicao_trabalho'] . "','"
                        . $_SESSION['titulo_trabalho'] . "','"
                        . $_SESSION['palavras_chave'] . "','"
                        . $_SESSION['resumo_trabalho'] . "','"
                        . $FWS_DATA_HOJE . "','"
                        . "'ATIVADA','"
                        . $SESSION_ID . "','"
                        . $_SERVER["HTTP_USER_AGENT"] . "','"
                        . $_SERVER["REMOTE_ADDR"] . "'"
                        . "";
                #-------------------------------------------
                #III: --------------------------------------
                $Var_Campos_Dados_Trabalho = ""
                        . "cod_evento               ,"
                        . "cod_inscrito             ,"
                        . "cod_area_tematica        ,"
                        . "autor_apresentador_nome  ,"
                        . "autor_apresentador_e_mail,"
                        . "nome_co_autor_1          ,"
                        . "nome_co_autor_2          ,"
                        . "nome_co_autor_3          ,"
                        . "nome_co_autor_4          ,"
                        . "nome_co_autor_5          ,"
                        . "nome_co_autor_6          ,"
                        . "instituicao_trabalho     ,"
                        . "titulo_trabalho          ,"
                        . "palavras_chave           ,"
                        . "resumo_trabalho          ,"
                        . "data_inclusao            ,"
                        . "status                   ,"
                        . "session_id               ,"
                        . "http_user_agent          ,"
                        . "ip_cliente                "
                        . "";

                #-------------------------------------------
                $consql = "
                      Insert Into tab_trabalhos (" . $Var_Campos_Dados_Trabalho . ")
                      Values (" . $Var_Sql_Dados_Trabalho . ")
                     ";

                $Conexao_db->debug = $debug;
                
                If (!$Conexao_db->Execute($consql)) { //Executando o Insert
                    $MSG = $MSG . "--ERRO-SQL-Trabalho";
                    $FWA_Execucao = false;
                } Else {
                    $MSG = $MSG . "--OK--Trabalho";
                    $FWA_Execucao = true;
                }
            } Else {
                $MSG = $MSG . "--Ultrapassou-o-limite-de-3-trabalhos-inscritos";
                $FWA_Execucao = true;
            }
        } Else {
            $MSG = $MSG . "--Não-forão-encontrados-inscritos-com-CPF=" . $_SESSION['cpf'] . "-e-COD_INSCRITO=" . $_SESSION['cod_inscrito'] . "";
            $FWA_Execucao = false;
        }
    } Else {#Testa se o campo cpf foi preenchidoe 
        $MSG = $MSG . "--CPF-e-COD_INSCRITO-não-forão-preenchidos-inscritos";
        $FWA_Execucao = false;
    }

    #echo $MSG;
    $_SESSION['MSG']=$MSG;
    If (@$FWA_Execucao == 1) {
        $URL = "http://" . FWA_Dominio() . "/" . FWA_URL_Prefixo() . "/index.php?content=view/inscricao/inscricao_congresso_ok.php";
        echo "<br>Aguarde um momento - " . date("d/m/Y - H:i:s");
        echo "<br>Gravando seus dados no sistema<BR>";
    } Else {
        $URL = "http://" . FWA_Dominio() . "/" . FWA_URL_Prefixo() . "/index.php?content=view/inscricao/inscricao_congresso_ok.php";
        echo "<br>Mensagens de:<BR>";
    }

    #--------------------------------------------------------------
} Else {#Fora do periodo de inscrição
    $MSG = "--Inscrições-Encerradas!--Periodo-de-Inscrição:-" . $data_ini . "-até-" . $data_fim . "--Data-de-hoje:-" . Date("YmdHi");
    $URL = "http://" . FWA_Dominio() . "/" . FWA_URL_Prefixo() . "/index.php?content=ERRO.php";
}
#--------------------------------------------------------------    

FWA_Mostrar_Mensagem($MSG);
$_SESSION['MSG']=$MSG;
#--------------------------------------------------------------
If ($debug == 1) {
    echo $MSG_Imprimir;
    echo "<br><a href='{$URL}'>{$URL}</a><br>";
} Else {
    echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $URL . "\">";
}

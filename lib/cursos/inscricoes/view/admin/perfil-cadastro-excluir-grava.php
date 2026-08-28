<?php
require_once(dirname(__FILE__)."/../../include/config.php");
$cod_perfil = $_GET['cod_perfil'];
$acao       = 'excluir';
$debug      = false;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
#-------------------------------------------
If (!$Conexao_db = FWA_Conexao("OpcTec")) { #Conectando-se ao banco de dados
    $MSG = "Operação-Não-Executada,-Erro-de-Conexão";
    $_SESSION['MSG'] = $MSG;
    $CONTENT = "view/admin/perfil-cadastro-ok.php";
    $URL = "index.php?content=" . $CONTENT . "&cod_perfil=" . $cod_perfil;
} Else {

    If ($acao == "excluir") {
        $var_sql = "
                    INSERT INTO tab_opcoes_perfis_excluidos
                    SELECT *
                    FROM tab_opcoes_perfis
                    WHERE (cod_perfil = " . $cod_perfil . ")
                  ";
        $RS = $Conexao_db->Execute($var_sql);
        $var_sql = "
                    INSERT INTO tab_opcoes_taxas_excluidos
                    SELECT *
                    FROM tab_opcoes_taxas
                    WHERE (cod_perfil = " . $cod_perfil . ")
                  ";
        $RS = $Conexao_db->Execute($var_sql);

        $var_sql = "
                    SELECT cod_perfil
                    FROM tab_opcoes_perfis_excluidos
                    WHERE (cod_perfil = " . $cod_perfil . ")
                  ";

        #echo $var_sql."- <BR>";

        $RS_select = $Conexao_db->Execute($var_sql);

        If (!$RS_select->EOF) {
            $cod_perfil = $RS_select->fields[0];

            $var_sql = "DELETE FROM tab_opcoes_perfis WHERE (cod_perfil = " . $cod_perfil . ")";
            $RS = $Conexao_db->Execute($var_sql);
            $var_sql = "DELETE FROM tab_opcoes_taxas WHERE (cod_perfil = " . $cod_perfil . ")";
            $RS = $Conexao_db->Execute($var_sql);

            $MSG = "Operação-Executada";
            $_SESSION['MSG'] = $MSG;
            $CONTENT = "view/admin/perfil-cadastro-ok.php";
            $URL = "index.php?content=" . $CONTENT; 

            #echo $var_sql."- <BR>";
        } Else {
            $MSG = "Operação-Não-Executada,-Ocorreu-um-Erro_ao-Excluir";
            $_SESSION['MSG'] = $MSG;
            $CONTENT = "view/admin/perfil-cadastro-ok.php";
            $URL = "index.php?content=" . $CONTENT;
        }
    } Else {
        $MSG = "Operação-Não-Executada,-Acao-Diferente-Excluir";
        $_SESSION['MSG'] = $MSG;
        $CONTENT = "view/admin/perfil-cadastro-ok.php";
        $URL = "index.php?content=" . $CONTENT;
    }
}

FWA_Mostrar_Mensagem($MSG);
if($debug!=true){
    echo  "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"1; URL=".$URL."\"></HTML>";
}
$URL = "http://".FWA_Dominio()."/".FWA_URL_Prefixo()."/".$URL;
echo  "<br><a href='{$URL}'>{$URL}</a><br>";

<?php
require_once(dirname(__FILE__)."/../../include/config.php");

$cod_trabalho = $_GET['cod_trabalho'];
$acao = $_GET['acao'];

If (!$Conexao_db = FWA_Conexao("OpcTec")) { #Conectando-se ao banco de dados
    $MSG = "Operação-Não-Executada,-Erro-de-Conexão";
    $_SESSION['MSG']=$MSG;
    $CONTENT = "view/inscricao/inscricao_congresso_ok.php";
    $URL = "index.php?content=" . $CONTENT . "&cod_trabalho=" . $cod_trabalho;
} Else {

    If ($acao == "Excluir") {
        $var_sql = "
                    INSERT INTO tab_trabalhos_excluidos
                    SELECT *
                    FROM tab_trabalhos
                    WHERE (cod_trabalho = " . $cod_trabalho . ")
                  ";
        $RS = $Conexao_db->Execute($var_sql);

        $var_sql = "
                    SELECT cod_trabalho
                    FROM tab_trabalhos_excluidos
                    WHERE (cod_trabalho = " . $cod_trabalho . ")
                  ";
        $RS_select = $Conexao_db->Execute($var_sql);

        If (!$RS_select->EOF) {
            $cod_trabalho = $RS_select->fields[0];

            $var_sql = "DELETE FROM tab_trabalhos WHERE (cod_trabalho = " . $cod_trabalho . ")";
            $RS = $Conexao_db->Execute($var_sql);

            $MSG = "Operação-Executada-com-Sucesso";
            $CONTENT = "view/inscricao/inscricao_congresso_ok.php";
            $URL = "index.php?content=" . $CONTENT . "&cod_trabalho=" . $cod_trabalho;
        } Else {
            $MSG = "Operação-Não-Executada,-Erro-ao-tentar-Excluir";
            $CONTENT = "view/inscricao/inscricao_congresso_ok.php";
            $URL = "index.php?content=" . $CONTENT . "&cod_trabalho=" . $cod_trabalho;
        }
    } Else {
        $MSG = "Operação-Não-Executada,-Acao-não-Informada-Corretamente";
        $CONTENT = "view/inscricao/inscricao_congresso_ok.php";
        $URL = "index.php?content=" . $CONTENT . "&cod_trabalho=" . $cod_trabalho;
    }
}

FWA_Mostrar_Mensagem($MSG);
$_SESSION['MSG']=$MSG;
$URL = "http://" . FWA_Dominio() . "/" . FWA_URL_Prefixo() . "/" . $URL;
echo "<br><a href='{$URL}'>{$URL}</a><br>";
echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $URL . "\"></HTML>";

<?php
require_once(dirname(__FILE__)."/../../include/config.php");

$cod_trabalho = $_GET['cod_trabalho'];
$acao = $_GET['acao'];

If (!$Conexao_db = FWA_Conexao("OpcTec")) { #Conectando-se ao banco de dados
    $MSG = "Operação-Não-Executada,-Erro-de-Conexão";
    $CONTENT = "view/inscricao/inscricao_congresso_ok.php";
    $URL = WB_URL_VIEW . "?content=" . $CONTENT . "&cod_trabalho=" . $cod_trabalho;
} Else {

    If ($acao == "Avaliar") {
        $obj = new GerenciarAvaliacaoAC();
        $obj->inscritoEnviarAvaliacao($fws_, $debug);
        exit();
    } Else {
        $MSG = "Operação-Não-Executada,-Acao-não-Informada-Corretamente";
        $CONTENT = "view/inscricao/inscricao_congresso_ok.php";
        $URL = WB_URL_VIEW . "?content=" . $CONTENT . "&cod_trabalho=" . $cod_trabalho;
    }
}
FWA_Mostrar_Mensagem($MSG);
$_SESSION['MSG']=$MSG;
echo "<br><a href='{$URL}'>{$URL}</a><br>";
echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $URL . "\"></HTML>";
?>
<?php
require_once(dirname(__FILE__)."/../../include/config.php");

$cod_oficina = $_GET['cod_oficina'];
$acao = $_GET['acao'];

$objInscrito = new Inscrito();
$objInscrito->setCodInscrito($_SESSION['_cod_inscrito']);
$objInscrito->getInscritoById();
if($objInscrito->getPossuiBoletoGerado() and !$OP_PERMITIR_ALTERAR_OFICINA){
    $MSG = "Após ter gerado um boleto bancário, a alteração de itens que afetam o valor de sua inscrição fica bloqueada.";
    $_SESSION['MSG']=$MSG;
    $CONTENT = "view/inscricao/inscricao_congresso_ok.php";
    $URL = "index.php?content=" . $CONTENT . "&cod_oficina=" . $cod_oficina;
}else{
    If (!$Conexao_db = FWA_Conexao("OpcTec")) { #Conectando-se ao banco de dados
        $MSG = "Operação-Não-Executada,-Erro-de-Conexão";
        $_SESSION['MSG']=$MSG;
        $CONTENT = "view/inscricao/inscricao_congresso_ok.php";
        $URL = "index.php?content=" . $CONTENT . "&cod_oficina=" . $cod_oficina;
    } Else {

        If ($acao == "Excluir") {
            $var_sql = "
                        INSERT INTO tab_lig_inscritos_oficinas_atualizados
                        SELECT *
                        FROM tab_lig_inscritos_oficinas
                        WHERE cod_oficina = " . $cod_oficina . " and cod_inscrito = ".$_SESSION['_cod_inscrito']." and cod_evento=".COD_EVENTO." 
                      ";
            $RS = $Conexao_db->Execute($var_sql);

            $var_sql = "
                        SELECT cod_oficina
                        FROM tab_lig_inscritos_oficinas_atualizados
                        WHERE cod_oficina = " . $cod_oficina . " and cod_inscrito = ".$_SESSION['_cod_inscrito']." and cod_evento=".COD_EVENTO." 
                      ";
            $RS_select = $Conexao_db->Execute($var_sql);

            If (!$RS_select->EOF) {
                $cod_oficina = $RS_select->fields[0];

                $var_sql = "DELETE FROM tab_lig_inscritos_oficinas WHERE "
                    . "cod_oficina = " . $cod_oficina . " and cod_inscrito = ".$_SESSION['_cod_inscrito']." and cod_evento=".COD_EVENTO." ";
                $RS = $Conexao_db->Execute($var_sql);

                $MSG = "Operação-Executada-com-Sucesso";
                $_SESSION['MSG']=$MSG;
                $CONTENT = "view/inscricao/inscricao_congresso_ok.php";
                $URL = "index.php?content=" . $CONTENT . "&cod_oficina=" . $cod_oficina;
            } Else {
                $MSG = "Operação-Não-Executada,-Erro-ao-tentar-Excluir";
                $_SESSION['MSG']=$MSG;
                $CONTENT = "view/inscricao/inscricao_congresso_ok.php";
                $URL = "index.php?content=" . $CONTENT . "&cod_oficina=" . $cod_oficina;
            }
        } Else {
            $MSG = "Operação-Não-Executada,-Acao-não-Informada-Corretamente";
            $_SESSION['MSG']=$MSG;
            $CONTENT = "view/inscricao/inscricao_congresso_ok.php";
            $URL = "index.php?content=" . $CONTENT . "&cod_oficina=" . $cod_oficina;
        }
    }
}
FWA_Mostrar_Mensagem($MSG);

$URL = "http://" . FWA_Dominio() . "/" . FWA_URL_Prefixo() . "/" . $URL;
echo "<br><a href='{$URL}'>Excluindo ...</a><br>";
echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $URL . "\"></HTML>";


<?php
Function FWA_Conexao($FWA_Banco = false, $debug=false) {
    ConexaoDB::getInstance();
    $conex = ConexaoDB::getConexaoDB();
    if ($debug) {
        echo "Teste instancia conexao->" . print_r($conex) . "<-<hr>";
    }
    return $conex;
}

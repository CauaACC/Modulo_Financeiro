<?php

Function FWA_Select_Inscrito($cod_evento=false) {

#Definindo comando SQL
    $var_sql = "
           SELECT cod_inscrito, nome_inscrito
           FROM tab_inscritos
           WHERE
           (cod_evento  = " . COD_EVENTO . ")
           ORDER BY nome_inscrito
          ";

#Fim da definição do comando SQL

    $Conexao_db = FWA_Conexao("OpcTec");

#Executando a consulta no banco SQL
    If ($RS_Ins = FWA_Executa_SQL($FWS_Debug, $Conexao_db, $var_sql)) {
        #Verificando se houve retorno de algum registro
        If (!$RS_Ins->EOF) {

            $FWA_Select_Inscrito.= "<select name='cod_inscrito'>
    <option value='-1'>Selecione Um Inscrito</option>
    {$OPTION_Inscrito}
    ";

            #Efetuando loop para o visualização dos registros retornados pela consulta SQL
            While (!$RS_Ins->EOF) {

                $COD_INSCRITO = $RS_Ins->fields[0];
                $NOME_INSCRITO = $RS_Ins->fields[1];

                #Abreviar nome_inscritos com nº de caracteres maiores q 50
                $NOME_INSCRITO = substr($NOME_INSCRITO, 0, 50);
                If (strlen($NOME_INSCRITO) == 50) {
                    $NOME_INSCRITO = $NOME_INSCRITO . "...";
                }

                $FWA_Select_Inscrito.= "<option value='" . $COD_INSCRITO . "'>" . FWA_Zero_Esquerda($COD_INSCRITO, 4) . "-" . $NOME_INSCRITO . "</option>";

                #Movendo para o proximo registro
                $RS_Ins->MoveNext();
            }
            $FWA_Select_Inscrito.= "</select>";
        } Else {
            $FWA_Select_Inscrito.= "<br>Não há Inscrito Cadastrado!<br><br>";
        }
    } Else {
        $FWA_Select_Inscrito.= "<br>Erro na Consulta<br>";
    }
}

?>
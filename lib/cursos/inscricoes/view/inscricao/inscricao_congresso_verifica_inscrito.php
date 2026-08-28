<?php
require_once(dirname(__FILE__)."/../../include/config.php");
#Fazendo conexão com o banco
$Conexao_db = FWA_Conexao("OpcTec");
$fwsDebug = false;
#-----------------------------------------------------------------------------------------

If ($fws_['cpf'] != "") {#Testa se o campo cpf foi preenchido
    $var_sql = "
    SELECT i.nome_inscrito, i.cod_inscrito, i.cpf, i.rg, i.e_mail
    FROM tab_inscritos as i
    WHERE
    (i.cod_evento  = " . COD_EVENTO . ")
    and (i.cpf         = '" . $fws_['cpf'] . "')
    and (i.status      = 'ATIVADA')
    ORDER BY i.cod_inscrito DESC
    ";

    #echo $var_sql;
    #Fim da definição do comando SQL
    #Executando a consulta no banco SQL
    $RS = $Conexao_db->Execute($var_sql);

    #Verificando se houve retorno de algum registro
    If (!$RS->EOF) {#Se cpf Esta inscrito
        #Definindo cabeçalho da tabela dos resultados da consula
        #Efetuando loop para o visualização dos registros retornados pela consulta SQL
        While (!$RS->EOF) {

            #Adicionando nas variaveis, os valores dos campos retirados dos registros
            $nome_inscrito = $RS->fields[0];
            $cod_inscrito = $RS->fields[1];
            $cpf = $RS->fields[2];
            $rg = $RS->fields[3];
            $e_mail = $RS->fields[4];
            $MSG = "Este cpf já está inscrito no evento!";
            echo "
            <br>Ola:                 {$nome_inscrito}
            <br>
            <br>CPF:                 {$cpf}
            <br>
            <br>".$MSG.".
            ";

            $URL = WB_URL_VIEW."?content=view/inscricao/inscricao_login.php&cpf=".$cpf."&MSG=" . $MSG;
            echo "<br><a href='{$URL}'>Redirecionando... para pagina de login.</a><br>";
            #Movendo para o proximo registro
            $RS->MoveNext();
        }
        echo "</blockquote>";
    } Else {#Se cpf Esta inscrito
        $MSG = "Redirecionando para formulário de cadastro";
        echo "
        <br>
        <br>CPF={$fws_['cpf']}, não encontrado!
        <br>
        <br>".$MSG.".
        ";
        $cpf           = $fws_['cpf'];

        $URL = WB_URL_VIEW."?content=view/inscricao/inscricao_congresso_cadastro_dados_pessoais.php&cpf=".$fws_['cpf']."&MSG=" . $MSG;
        echo "<br><a href='{$URL}'>Redirecionando... para o formulário de inscrição</a><br>";
    }
} Else {#Testa se o campo cpf foi preenchido
    echo "
    <br>
    <br>
    <br>O campo CPF não foi preenchidos.
    <br>
    ";
    $URL = WB_URL_VIEW."?content=view/inscricao/inscricao_congresso_verifica_inicio.php&MSG=" . $MSG;
    echo "<br><a href='{$URL}'>Redirecionando... para o formulário de verificação de inscrito</a><br>";
}
if(!$fwsDebug){
    echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $URL . "\">";
}
?>

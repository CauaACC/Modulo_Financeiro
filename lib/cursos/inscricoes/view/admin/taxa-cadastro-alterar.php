<?php
require_once(dirname(__FILE__) . "/../../include/config.php");
require_once(dirname(__FILE__) . "/../../view/admin/taxa-cabecalho.php");
require_once(dirname(__FILE__) . "/../../view/admin/taxa-cadastro-busca.php");
#Fazendo conexão com o banco

$cod_taxa = $fwsGet_['cod_taxa'];

#echo $cod_taxa  ."<br>";

If (isset($cod_taxa)) {

    $Conexao_db = FWA_Conexao("OpcTec");
    #Definindo comando SQL
    $var_sql = "
                 SELECT *
                 FROM tab_opcoes_taxas
                 WHERE
                 (cod_taxa = " . $cod_taxa . ")and
                 (cod_evento  = " . COD_EVENTO . ")
                 ORDER BY cod_taxa DESC
                ";

    #echo $var_sql;
    #Fim da definição do comando SQL
    #Executando a consulta no banco SQL
    $RS = $Conexao_db->Execute($var_sql);

    #Verificando se houve retorno de algum registro
    If (!$RS->EOF) {
        #echo "<br>Definindo cabeçalho da tabela dos resultados da consula<br>";
        #Efetuando loop para o visualização dos registros retornados pela consulta SQL
        While (!$RS->EOF) {

            #echo "<br>Adicionando nas variaveis, os valores dos campos retirados dos registros<br>";
            $status = $RS->fields['status'];
            $titulo = $RS->fields['titulo'];
            $tx_unica_a_vista_valor = $RS->fields['tx_unica_a_vista_valor'];
            $periodo_inicial = $RS->fields['periodo_inicial'];
            $periodo_final = $RS->fields['periodo_final'];
            $max_data_vencimento = $RS->fields['max_data_vencimento'];
            $dias_vencimento = $RS->fields['dias_vencimento'];
            $OBSERVACAO = $RS->fields['observacao'];
            $cod_taxa = $RS->fields['cod_taxa'];
            $cod_perfil = $RS->fields['cod_perfil'];

            $objPerfil = new Perfil();
            $objPerfil->setCodPerfil($cod_perfil);
            $objPerfil->getPerfilById();

            echo "<h4>Perfil: " . $objPerfil->getTitulo() . "</h4>";

            echo "
            <hr size='1' width='100%'>
            <FONT SIZE='2' FACE='VERDANA'> <B> Ficha de Cadastro de Taxas</B></FONT><br>
            <FONT SIZE='1' FACE='VERDANA'> <B> * Campos Obrigatórios</B></FONT>
            <FONT SIZE='1' FACE='VERDANA'>

            <FORM method='POST' name='form' action='view/admin/taxa-cadastro-alterar-grava.php' onsubmit=\"return criticar()\">
              <input type='hidden' id='codTaxa' name='cod_taxa' value='{$cod_taxa}'>
              <input type='hidden' id='codEvento' name='COD_EVENTO'  value='{$COD_EVENTO}'>

            <FONT SIZE='2'><b>Cógido da Taxa: {$cod_taxa}</b></font><br><br>

            <fieldset width='570'><legend> Dados da Taxa </legend>
            ";

              include_once("taxa-cadastro-campos.php");

            echo "</fieldset>";
            echo "
            <div border='0' width='565' align='center'>
                <br><br>
                <input type='submit' value='Salvar' style='font-size:14px'>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <input type='reset' value='Limpar' style='font-size:14px'>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <button class='button' style='font-size:14px' id='btnDuplicar' onkeypress='javascript: event.preventDefault();'>Duplicar</button>
            </div>";
            echo "</FORM>";

            #Movendo para o proximo registro
            $RS->MoveNext();
        }
    } Else {
        echo "Codigo não encontrado.";
    }
} Else {
    echo "É necessário codigo da Taxa.";
}
?>
</div></div></div></div>





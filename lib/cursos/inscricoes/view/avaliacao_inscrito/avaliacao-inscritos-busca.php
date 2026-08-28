<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/avaliacao_inscrito/avaliacao-inscritos-seguranca.php");
?>
<br>
<!--<hr size='1' width='100%'>-->
<!--Listar trabalhos disponíveis para avaliação<hr>-->
<form action='index.php?content=view/avaliacao_inscrito/avaliacao-inscritos-listagem-por-avaliador.php' method='post'>
<?php
#Definindo comando SQL
/*listar areas tematicas por usuário*/
$var_sql = "
     SELECT toa.cod_area_tematica, toa.ordem, toa.titulo
     FROM tab_opcoes_areas_tematicas toa, tab_lig_convidados_areas_tematicas tlca, tab_convidados tc, sis_usuarios su
     WHERE
     (su.cod_usuario    = ".$_SESSION['_cod_usuario'].")and
     (su.cod_evento     = ".COD_EVENTO.")and
     (su.cod_evento     = tlca.cod_evento)and
     (su.cod_convidado  = tlca.cod_convidado)and
     (tlca.cod_convidado = tc.cod_convidado)and
     (tlca.cod_area_tematica = toa.cod_area_tematica)and 
     (tlca.cod_evento = toa.cod_evento)
     ORDER BY toa.ordem
    ";
/*listar areas tematicas*/
$var_sql = "
     SELECT toa.cod_area_tematica, toa.ordem, toa.titulo
     FROM tab_opcoes_areas_tematicas toa
     WHERE toa.cod_evento=".COD_EVENTO."
     ORDER BY toa.ordem
    ";
$listar = false;
//echo $var_sql;
#Fim da definição do comando SQL
$Conexao_db = FWA_Conexao("OpcTec");
#Executando a consulta no banco SQL
$RS = $Conexao_db->Execute($var_sql);

#Verificando se houve retorno de algum registro
if (!$RS->EOF and $listar==1) {
    #echo "<br>Definindo cabeçalho da tabela dos resultados da consula<br>";
    ?>
    Área Temática:<br><br>

    <!--form action='index.php?content=view/avaliacao_premiacao/avaliacao-inscritos-listagem.php' method='post'-->
    <select name='cod_area_tematica' class='form-control'>
        <option value='-1'>Todas</option>
        <?php
        #Efetuando loop para o visualização dos registros retornados pela consulta SQL
        while (!$RS->EOF) {

            $COD_AREA_TEMATICA = $RS->fields[0];
            $ORDEM = $RS->fields[1];
            $TITULO_OPCAO = $RS->fields[2];

            #Abreviar titulos com nº de caracteres maiores q 50
            //$TITULO_OPCAO=substr($TITULO_OPCAO,0,50);
            //If(strlen($TITULO_OPCAO)==50){
            //      $TITULO_OPCAO=$TITULO_OPCAO."...";
            //}

            $COD_AREA_TEMATICA_Z = FWA_Zero_Esquerda($COD_AREA_TEMATICA, 2);
            $ORDEM_Z = FWA_Zero_Esquerda($ORDEM, 2);

            if ($RS->RecordCount() <= 1) {
                $fws_['cod_area_tematica'] = $COD_AREA_TEMATICA;
            }

            echo "<option ";
            echo verifItemSelected($fws_['cod_area_tematica'], $COD_AREA_TEMATICA);
            echo " value='{$COD_AREA_TEMATICA}'>{$TITULO_OPCAO}</option>";

    #Movendo para o proximo registro
            $RS->MoveNext();
        }
    echo "</select><br>";
}

if ($FWS_Form_Detalhes == true) {
    if ($fws_['detalhes'] == 2) {
        $checked_detalhes_2 = "checked";
    } else if ($fws_['detalhes'] == 1) {
        $checked_detalhes_1 = "checked";
    } else {
        $checked_detalhes_0 = "checked";
    }
    echo "&nbsp;&nbsp;&nbsp;<input type='radio' name='detalhes' value='1' " . $checked_detalhes_1 . "> Listar minhas avaliações concluídas";
    echo "<br>&nbsp;&nbsp;&nbsp;<input type='radio' name='detalhes' value='2' " . $checked_detalhes_2 . "> Listar minhas avaliações NÃO concluídas"; 
//    echo "<br>&nbsp;&nbsp;&nbsp;<input type='radio' name='detalhes' value='0' " . $checked_detalhes_0 . "> Listar disponíveis para avaliação";

}
?>
        <br><br>&nbsp;&nbsp;
    <input type='submit' value='Clique aqui para listar trabalhos' class='btn btn-xs btn-primary'><br><br>
</form>
<hr size='1' width='100%'>

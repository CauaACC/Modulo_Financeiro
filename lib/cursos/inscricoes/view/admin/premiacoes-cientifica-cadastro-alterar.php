<?php
require_once(dirname(__FILE__)."/../../include/config.php");

require_once(dirname(__FILE__)."/../../view/admin/premiacoes-cientifica-cabecalho.php");

require_once(dirname(__FILE__)."/../../view/admin/premiacoes-cientifica-cadastro-busca.php");


#Fazendo conexão com o banco

$COD_PREMIACAO_CIENTIFICA = $_GET['COD_PREMIACAO_CIENTIFICA'];

#echo $COD_PREMIACAO_CIENTIFICA  ."<br>";

If(isset($COD_PREMIACAO_CIENTIFICA)){

  $Conexao_db = FWA_Conexao("OpcTec");

  $Var_Campos = "
                  ordem,
                  status,
                  titulo,
                  nome_ministrante,
                  e_mail_ministrante,
                  fone_ministrante,
                  local,
                  carga_horaria,
                  numero_vagas,
                  horario_ini,
                  horario_fim,
                  data_ini,
                  data_fim,
                  palavras_chave,
                  publico_alvo,
                  objetivo,
                  pre_requisito,
                  ementa,
                  descricao,
                  observacao
                ";


     #Definindo comando SQL
      $var_sql = "
                 SELECT ".$Var_Campos."
                 FROM tab_opcoes_premiacoes_cientifica
                 WHERE
                 (cod_premiacao_cientifica = ".$COD_PREMIACAO_CIENTIFICA.")and
                 (cod_evento  = ".COD_EVENTO.")
                 ORDER BY cod_premiacao_cientifica DESC
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
      $ORDEM              = $RS->fields[0];
      $STATUS             = $RS->fields[1];
      $TITULO             = $RS->fields[2];
      $NOME_MINISTRANTE   = $RS->fields[3];
      $E_MAIL_MINISTRANTE = $RS->fields[4];
      $FONE_MINISTRANTE   = $RS->fields[5];
      $LOCAL              = $RS->fields[6];
      $CARGA_HORARIA      = $RS->fields[7];
      $NUMERO_VAGAS       = $RS->fields[8];
      $HORARIO_INI        = $RS->fields[9];;
      $HORARIO_FIM        = $RS->fields[10];
      $DATA_INI           = $RS->fields[11];
      $DATA_FIM           = $RS->fields[12];
      $PALAVRAS_CHAVE     = $RS->fields[13];
      $PUBLICO_ALVO       = $RS->fields[14];
      $OBJETIVO           = $RS->fields[15];
      $PRE_REQUISITO      = $RS->fields[16];
      $EMENTA             = $RS->fields[17];
      $DESCRICAO          = $RS->fields[18];
      $OBSERVACAO         = $RS->fields[19];

      $DATA_INI_DIA = substr($DATA_INI, 0, 2);
      $DATA_INI_MES = substr($DATA_INI, 3, 2);
      $DATA_INI_ANO = substr($DATA_INI, 6, 4);

      $DATA_FIM_DIA = substr($DATA_FIM, 0, 2);
      $DATA_FIM_MES = substr($DATA_FIM, 3, 2);
      $DATA_FIM_ANO = substr($DATA_FIM, 6, 4);

      $HORARIO_INI_HORA   = substr($HORARIO_INI, 0, 2);
      $HORARIO_INI_MINUTO = substr($HORARIO_INI, 3, 2);

      $HORARIO_FIM_HORA   = substr($HORARIO_FIM, 0, 2);
      $HORARIO_FIM_MINUTO = substr($HORARIO_FIM, 3, 2);



      #echo $STATUS             ."<br>";
      #echo $TITULO             ."<br>";
      #echo $NOME_MINISTRANTE   ."<br>";
      #echo $E_MAIL_MINISTRANTE ."<br>";
      #echo $FONE_MINISTRANTE   ."<br>";
      #echo $LOCAL              ."<br>";
      #echo $CARGA_HORARIA      ."<br>";
      #echo $NUMERO_VAGAS       ."<br>";
      #echo $HORARIO_INI        ."<br>";
      #echo $HORARIO_FIM        ."<br>";
      #echo $DATA_INI           ."<br>";
      #echo $DATA_FIM           ."<br>";
      #echo $PALAVRAS_CHAVE     ."<br>";
      #echo $PUBLICO_ALVO       ."<br>";
      #echo $OBJETIVO           ."<br>";
      #echo $PRE_REQUISITO      ."<br>";
      #echo $EMENTA             ."<br>";
      #echo $DESCRICAO          ."<br>";
      #echo $OBSERVACAO         ."<br>";


      echo "
      <hr size='1' width='100%'>
      <FONT SIZE='2' FACE='VERDANA'> <B> Ficha de Cadastro de Premiação Cientifica</B></FONT><br>
      <FONT SIZE='1' FACE='VERDANA'> <B> * Campos Obrigatórios</B></FONT>
      <FONT SIZE='1' FACE='VERDANA'>

      <FORM method='POST' name='form' action='view/admin/premiacoes-cientifica-cadastro-alterar-grava.php' onsubmit=\"return criticar()\">
        <input type='hidden' name='COD_PREMIACAO_CIENTIFICA' value='{$COD_PREMIACAO_CIENTIFICA}'>
        <input type='hidden' name='COD_EVENTO'  value='{$COD_EVENTO}'>

      <FONT SIZE='2'><b>Cógido da Premiação Cientifica: {$COD_PREMIACAO_CIENTIFICA}</b></font><br><br>
      <table border='0' width='565'>
      <tr><td colspan='2' align='center'>
            <input type='submit' value='Alterar'> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='reset' value='Limpar'>
      </td></tr>
      </table>
      <fieldset width='570'><legend> Dados da Premiação Cientifica </legend>
      ";

      include_once("premiacoes-cientifica-cadastro-campos.php");

      echo "
      </fieldset>
      <table border='0' width='565'>
      <tr><td colspan='2' align='center'>
            <br><br>
            <input type='submit' value='Alterar'> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='reset' value='Limpar'>
      </td></tr>
      </table>
      </FORM>
      ";

      #Movendo para o proximo registro
      $RS->MoveNext();
    }


  }Else{
    echo "Codigo não encontrado.";
  }

}Else{
  echo "É Necessário Codigo da Premiação Cientifica.";
}

?>
</div></div></div></div></div>
<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/area-tematica-cabecalho.php");
require_once(dirname(__FILE__)."/../../view/admin/area-tematica-cadastro-busca.php");
#Fazendo conexão com o banco

$cod_area_tematica = $_GET['cod_area_tematica'];
#echo $cod_area_tematica  ."<br>";
If(isset($cod_area_tematica)){

  $Conexao_db = FWA_Conexao("OpcTec");

  $Var_Campos = "
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
                  observacao,
                  n_max_trab_por_avaliador
                ";


     #Definindo comando SQL
      $var_sql = "
                 SELECT ".$Var_Campos."
                 FROM tab_opcoes_areas_tematicas
                 WHERE
                 (cod_area_tematica = ".$cod_area_tematica.")and
                 (cod_evento  = ".COD_EVENTO.")
                 ORDER BY cod_area_tematica DESC
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
      $STATUS             = $RS->fields[0];
      $TITULO             = $RS->fields[1];
      $NOME_MINISTRANTE   = $RS->fields[2];
      $E_MAIL_MINISTRANTE = $RS->fields[3];
      $FONE_MINISTRANTE   = $RS->fields[4];
      $LOCAL              = $RS->fields[5];
      $CARGA_HORARIA      = $RS->fields[6];
      $NUMERO_VAGAS       = $RS->fields[7];
      $HORARIO_INI        = $RS->fields[8];
      $HORARIO_FIM        = $RS->fields[9];;
      $DATA_INI           = $RS->fields[10];
      $DATA_FIM           = $RS->fields[11];
      $PALAVRAS_CHAVE     = $RS->fields[12];
      $PUBLICO_ALVO       = $RS->fields[13];
      $OBJETIVO           = $RS->fields[14];
      $PRE_REQUISITO      = $RS->fields[15];
      $EMENTA             = $RS->fields[16];
      $DESCRICAO          = $RS->fields[17];
      $OBSERVACAO         = $RS->fields[18];
      $N_MAX_TRAB_POR_AVALIADOR = $RS->fields[19];

      If (Strlen($DATA_INI)==10){
        $DATA_INI_DIA = substr($DATA_INI, 0, 2);
        $DATA_INI_MES = substr($DATA_INI, 3, 2);
        $DATA_INI_ANO = substr($DATA_INI, 6, 4);
      }
      
      If (Strlen($DATA_FIM)==10){
        $DATA_FIM_DIA = substr($DATA_FIM, 0, 2);
        $DATA_FIM_MES = substr($DATA_FIM, 3, 2);
        $DATA_FIM_ANO = substr($DATA_FIM, 6, 4);
      }
      
      If (Strlen($HORARIO_INI)==5){
        $HORARIO_INI_HORA   = substr($HORARIO_INI, 0, 2);
        $HORARIO_INI_MINUTO = substr($HORARIO_INI, 3, 2);
      }
      
      If (Strlen($HORARIO_FIM)==5){
        $HORARIO_FIM_HORA   = substr($HORARIO_FIM, 0, 2);
        $HORARIO_FIM_MINUTO = substr($HORARIO_FIM, 3, 2);
      }


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
      #echo $N_MAX_TRAB_POR_AVALIADOR."<br>";


      echo "
      <hr size='1' width='100%'>
      <FONT SIZE='2' FACE='VERDANA'> <B> Ficha de Cadastro de Areas Tematicas</B></FONT><br>
      <FONT SIZE='1' FACE='VERDANA'> <B> * Campos Obrigatórios</B></FONT>
      <FONT SIZE='1' FACE='VERDANA'>

      <FORM method='POST' name='form' action='view/admin/area-tematica-cadastro-alterar-grava.php' onsubmit=\"return criticar()\">
        <input type='hidden' name='cod_area_tematica' value='{$cod_area_tematica}'>
        <input type='hidden' name='COD_EVENTO'  value='{$COD_EVENTO}'>

      <FONT SIZE='2'><b>Cógido da Area Tematica: {$cod_area_tematica}</b></font><br><br>

      <fieldset width='570'><legend> Dados da Area Tematica </legend>
      ";

      include_once("area-tematica-cadastro-campos.php");

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
  echo "Informe o código da Área Temática.";
}
?>
<hr size='1' width='100%'>
<a class='link' href='javascript:history.go(-1)'><b>voltar<b></a>

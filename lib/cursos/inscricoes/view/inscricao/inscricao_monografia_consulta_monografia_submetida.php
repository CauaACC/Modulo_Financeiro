<?php
#Includes
require_once(dirname(__FILE__)."/../../include/config.php");

#Fazendo conexão com o banco
$Conexao_db = FWA_Conexao("OpcTec");

#require_once(dirname(__FILE__)."/../../include/inscricao_request_variaveis.php");

If ($_SESSION['cpf']!=""){#Testa se o campo cod_inscrito foi preenchido

echo "<fieldset width='500'><legend> Monografia Submetida </legend>";

#-----------------------------------------------------------------------------------------
    $Var_Campos = "
                    m.cod_monografia
                   ,m.cod_premiacao_cientifica
                   ,m.titulo_monografia
                   ,m.autor_nome
                   ,m.autor_pseudonimo
                   ,m.arquivo_upload
                   ,op.titulo
                  ";


     #Definindo comando SQL
      $var_sql = "
                 SELECT ".$Var_Campos."
                 FROM tab_monografias as m, tab_opcoes_premiacoes_cientifica as op, tab_inscritos as ti
                 WHERE
                     (m.cod_evento        = ".COD_EVENTO.")
                 and (m.cod_inscrito      = ti.cod_inscrito)
                 and (ti.cpf              = '".$_SESSION['cpf']."')
                 and (ti.status           = 'ATIVADA')
                 and (m.cod_premiacao_cientifica     = op.cod_premiacao_cientifica)
                 and (m.status            = 'ATIVADA')
                 ORDER BY cod_monografia DESC
                ";


    #echo $var_sql;

    #Fim da definição do comando SQL

    #Executando a consulta no banco SQL
    $RS = $Conexao_db->Execute($var_sql);

    #Verificando se houve retorno de algum registro
    If (!$RS->EOF) {#Se nao ha trabahos submetidos
      #Definindo cabeçalho da tabela dos resultados da consula

      #Efetuando loop para o visualização dos registros retornados pela consulta SQL
      echo "<table border='0'>";
      While (!$RS->EOF) {

      #Adicionando nas variaveis, os valores dos campos retirados dos registros
        $cod_monografia    = $RS->fields[0];
        $cod_premiacao_cientifica     = $RS->fields[1];
        $titulo_monografia = $RS->fields[2];
        $autor_nome        = $RS->fields[3];
        $autor_pseudonimo  = $RS->fields[4];
        $arquivo_upload    = $RS->fields[5];
        $titulo_premiacao  = $RS->fields[6];

        $Var_caminho_arquivo = "upload/monografia/".COD_EVENTO."-".$_SESSION['cod_inscrito'].".doc";

echo <<<corpo
          <tr valign='top'><td colspan='2'>&nbsp;</td></tr>
          <tr valign='top'><td width='80'>Titulo     : </td><td> <b>{$titulo_monografia }</b></td></tr>
          <tr valign='top'><td            >Nome autor : </td><td> <b>{$autor_nome       }</b></td></tr>
          <tr valign='top'><td            >PseudÃ´nimo : </td><td> <b>{$autor_pseudonimo }</b></td></tr>
          <tr valign='top'><td            >Premiação  : </td><td> <b>{$titulo_premiacao }</b></td></tr>
          <tr valign='top'><td            >Arquivo    : </td><td> <a href='{$Var_caminho_arquivo}' target='blank' class='linkv'><b>{$arquivo_upload}</b></a></td></tr>
corpo;

        #Movendo para o proximo registro
        $RS->MoveNext();
      }
      echo "<tr valign='top'><td colspan='2'>&nbsp;</td></tr></table>";
    }Else{
echo <<<corpo
      <br>
      <blockquote>

      Você não possui nenhuma monografia submetida!


      <br>
      <FORM NAME='exit' METHOD='POST' ACTION='index.php'>
         <INPUT TYPE='hidden' VALUE='inscricao_monografia_seleciona_premiacao_cientifica.php' NAME='content'>
         <INPUT TYPE='SUBMIT' VALUE='Para Enviar Monografia, clique aqui'>
      </FORM>

      <br>
      <font color='red'>
      <br>
      Obs:
      <br>O prazo para envio de trabalhos e monografias se encerrou dia 18/04.

      </blockquote>
corpo;

    }

#-----------------------------------------------------------------------------------------
echo "</fieldset>";
}
?>

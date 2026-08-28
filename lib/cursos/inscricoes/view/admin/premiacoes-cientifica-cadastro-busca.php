<?php
require_once(dirname(__FILE__)."/../../include/config.php");

echo "<hr size='1' width='100%'>";

#Definindo comando SQL
      $var_sql = "
                 SELECT cod_premiacao_cientifica, titulo, ordem
                 FROM tab_opcoes_premiacoes_cientifica
                 WHERE
                 (cod_evento  = ".COD_EVENTO.")
                 ORDER BY ordem, titulo
                ";

  #echo $var_sql;

  #Fim da definição do comando SQL

  $Conexao_db = FWA_Conexao("OpcTec");

  #Executando a consulta no banco SQL
  $RS = $Conexao_db->Execute($var_sql);

  #Verificando se houve retorno de algum registro
  If (!$RS->EOF) {
    #echo "<br>Definindo cabeçalho da tabela dos resultados da consula<br>";

    echo "
    &nbsp;Selecione uma Premiação Cientifica no campo abaixo:
    <form action='index.php' method='get'>
      <input type='hidden' name='content' value='view/admin/premiacoes-cientifica-cadastro-ok.php'>
      &nbsp;<select name='COD_PREMIACAO_CIENTIFICA'>
               <option value='-1'>Selecione Uma Premiação Cientifica</option>
    ";

    #Efetuando loop para o visualização dos registros retornados pela consulta SQL
    While (!$RS->EOF) {

      $COD_PREMIACAO_CIENTIFICA = $RS->fields[0];
      $TITULO_OPCAO= $RS->fields[1];
      $ORDEM       = $RS->fields[2];
      
      #Abreviar titulos com nº de caracteres maiores q 50
      $TITULO_OPCAO=substr($TITULO_OPCAO,0,50);
      If(strlen($TITULO_OPCAO)==50){
        $TITULO_OPCAO=$TITULO_OPCAO."...";
      }

      echo "<option value='{$COD_PREMIACAO_CIENTIFICA}'>{$ORDEM}-{$TITULO_OPCAO}</option>";

      #Movendo para o proximo registro
      $RS->MoveNext();
    }
    echo "
      </select>
    <input type='submit' value='Buscar'>
    ";
  }Else{

    echo "<br>&nbsp;&nbsp;&nbsp;&nbsp;Não há nenhuma Premiação Cientifica cadastrada!<br><br>";

  }
?>
</form>
&nbsp;&nbsp;
<a href="<?php echo WB_URL_VIEW?>?content=view/admin/evento-cadastro-ok.php" class="button btn btn-primary btn-xs">
    <span class="glyphicon glyphicon-list"></span> Listar Eventos</a>
&nbsp;&nbsp;
<a href="<?php echo WB_URL_VIEW?>?content=view/admin/evento-cadastro-alterar.php&cod_evento=<?php echo $cod_evento ?>" class="button btn btn-primary btn-xs">
    <span class="glyphicon glyphicon-eye-open"></span> Editar Evento</a>
&nbsp;&nbsp;
<a href="<?php echo WB_URL_VIEW?>?content=view/admin/evento-configurar-alterar.php&cod_evento=<?php echo $cod_evento ?>" class="button btn btn-primary btn-xs">
    <span class="glyphicon glyphicon-eye-open"></span> Configurar Evento</a>
&nbsp;&nbsp;
<a href="<?php echo WB_URL_VIEW?>?content=view/admin/perfil-cadastro-ok.php" class="button btn btn-primary btn-xs">
    <span class="glyphicon glyphicon-eye-open"></span> Grupos de Participantes</a>
&nbsp;&nbsp;
<a href="<?php echo WB_URL_VIEW?>?content=view/admin/oficina-cadastro-ok.php" class="button btn btn-primary btn-xs">
    <span class="glyphicon glyphicon-eye-open"></span> Listar de Atividades</a>
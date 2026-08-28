<?php
require_once(dirname(__FILE__)."/../../include/config.php");

echo "<hr size='1' width='100%'>";

$objUE = new UsuarioEvento();
$objUE->setCodEvento(COD_EVENTO);
$objUE->carregarListaUsuariosByIdEvento($debug);

  #Verificando se houve retorno de algum registro
    $listaUsuarios = $objUE->getListaUsuarios();
    if (isset($listaUsuarios) && $listaUsuarios !== '') {
    #echo "<br>Definindo cabeçalho da tabela dos resultados da consula<br>";

    echo "<span style class=\"textop\">
    &nbsp;Selecione um login no campo abaixo:
    <form action='index.php' method='post'>
      <input type='hidden' name='content' value='view/admin/login-cadastro-ok.php'>
      &nbsp;<select name='cod_usuario'>
               <option value='-1'>Selecione Um Login</option>
    ";

    #Efetuando loop para o visualização dos registros retornados pela consulta SQL
    foreach ($listaUsuarios as $objL) {
            
      #Abreviar nome_convidados com nº de caracteres maiores q 50
      $LOGIN=substr($objL->getLogin(),0,50);
      If(strlen($LOGIN)==50){
        $LOGIN=$LOGIN."...";
      }

      echo "<option value='{$objL->getCodUsuario()}'>{$LOGIN}</option>";

      #Movendo para o proximo registro
    }
    echo "
      </select>
    <input type='submit' value='Buscar'>
    ";
  }Else{

    echo "<br>&nbsp;&nbsp;&nbsp;&nbsp;Não há nenhum Login cadastrado!<br><br>";

  }
?>

</form>

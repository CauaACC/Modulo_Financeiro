<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/login-cabecalho.php");
require_once(dirname(__FILE__)."/../../view/admin/login-cadastro-busca.php");

$FWS_Debug = false;

#Fazendo conexão com o banco

$COD_USUARIO = $_GET['COD_USUARIO'];

#echo $COD_USUARIO  ."<br>";

If(isset($COD_USUARIO)){

  $Conexao_db = FWA_Conexao("OpcTec");

  $var_sql = "
             SELECT 
                  su.cod_usuario,
                  su.cod_convidado,
                  su.nome_usuario,
                  su.cod_evento,
                  su.login,
                  su.senha,
                  tc.nome_convidado
             FROM sis_usuarios su 
             left join tab_convidados tc ON (tc.cod_convidado=su.cod_usuario or tc.cod_usuario=su.cod_usuario)
             WHERE
             (su.cod_usuario = ".$COD_USUARIO.")and(su.cod_evento  = ".COD_EVENTO.")
               ";

    $RS=FWA_Executa_SQL($FWS_Debug,$Conexao_db,$var_sql); //Consulta login existe   

    #Verificando se houve retorno de algum registro
    If (!$RS->EOF) {
      #Definindo cabeçalho da tabela dos resultados da consula

      #Efetuando loop para o visualização dos registros retornados pela consulta SQL
      While (!$RS->EOF) {

      #Adicionando nas variaveis, os valores dos campos retirados dos registros
      #Adicionando nas variaveis, os valores dos campos retirados dos registros
      $cod_usuario    = $RS->fields[0];
      $cod_convidado  = $RS->fields[1];
      $nome_usuario   = $RS->fields[2];
//      COD_EVENTO     = $RS->fields[3];
      $login          = $RS->fields[4];
      $senha          = $RS->fields[5];
      $nome_convidado = $RS->fields[6];

        #Definindo tabela com resultado

?>
      <hr size='1' width='100%'>
      <span style class='texto'> <B> Ficha de Cadastro de Login</B><br>
      <span style class='textop'> <B> * Campos Obrigatórios</B></span>

      <FORM method='POST' name='form' action='view/admin/login-cadastro-alterar-grava.php' onsubmit="return criticar()">

      <fieldset width='570'><legend> Dados do Login </legend>
        <span style class='texto'>&nbsp;&nbsp;<b>Cógido do Login: <?php echo $COD_USUARIO?></b><br><br></span>

        <?php
        include_once("login-cadastro-campos.php");
        ?>


      </fieldset>
      <table border='0' width='565'>
      <tr><td colspan='2' align='left'>
            <br><br>
            <input type='submit' value='Alterar'> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='reset' value='Limpar'>
      </td></tr>
      </table>
      </FORM>
      <?php
      #Movendo para o proximo registro
      $RS->MoveNext();
    }


  }Else{
    echo "Codigo não encontrado.";
  }

}Else{
  echo "É Necessário Codigo do Login.";
}

?>
<hr size='1' width='100%'>
<a class='link' href='javascript:history.go(-1)'><b>voltar<b></a>
</body>
</html>
</div></div></div</div></div>
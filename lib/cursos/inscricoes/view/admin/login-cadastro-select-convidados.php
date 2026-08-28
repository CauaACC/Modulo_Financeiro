<?php
require_once(dirname(__FILE__)."/../../include/config.php");

$FWS_Debug = false;

$Conexao_db = FWA_Conexao("OpcTec");

$var_sql = "
            SELECT tc.cod_convidado, tc.nome_convidado, tc.e_mail, tc.cpf
             FROM tab_convidados tc
             WHERE
             (tc.cod_evento    = ".COD_EVENTO.")
          ";

$RS=FWA_Executa_SQL($FWS_Debug,$Conexao_db,$var_sql); //Consulta login existe   

#Verificando se houve retorno de algum registro
If (!$RS->EOF) {

  echo "<br>&nbsp;&nbsp;
  Selecione Convidado:
    <select name='COD_CONVIDADO'>
     ".$OPTIONS_Atuacao;

  #Efetuando loop para o visualização dos registros retornados pela consulta SQL
  While (!$RS->EOF) {

    $COD_CONVIDADO = $RS->fields[0];
    $NOME_CONVIDADO= $RS->fields[1];
    $E_MAIL        = $RS->fields[2];
    $CPF           = $RS->fields[3];
    
    #Abreviar titulos com nº de caracteres maiores q 50
    $NOME_CONVIDADO=substr($NOME_CONVIDADO,0,50);
    If(strlen($NOME_CONVIDADO)==50){
      $NOME_CONVIDADO=$NOME_CONVIDADO."...";
    }

    echo "<option value='".$COD_CONVIDADO."' ".  verifItemSelected($COD_CONVIDADO, $fws_['cod_convidado'])." >".$NOME_CONVIDADO."</option>";

    #Movendo para o proximo registro
    $RS->MoveNext();
  }
  echo "</select>&nbsp;&nbsp;&nbsp;";
  
  echo  "<a href='index.php?content=view/admin/convidados-cadastro-incluir.php'>Cadastrar convidado</a>";
  
  echo "<br><br>";
}Else{
  echo "<br>&nbsp;&nbsp;&nbsp;&nbsp;Não há nenhuma Convidado cadastrada!<br><br>";
}
?>

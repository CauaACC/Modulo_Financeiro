<?php
require_once(dirname(__FILE__)."/../../include/config.php");

require_once(dirname(__FILE__)."/../../view/admin/convidados-cabecalho.php");

require_once(dirname(__FILE__)."/../../view/admin/convidados-cadastro-busca.php");


#Fazendo conexão com o banco

$COD_CONVIDADO = $_GET['COD_CONVIDADO'];

#echo $COD_CONVIDADO  ."<br>";

If(isset($COD_CONVIDADO)){

  $Conexao_db = FWA_Conexao("OpcTec");

  $Var_Campos = "
                tc.cod_convidado,
                tc.cod_atuacao_convidado,
                tc.nome_convidado,
                tc.cpf,
                tc.rg,
                tc.e_mail,
                tc.data_nascimento,
                tc.sexo,
                tc.profissao_atuacao,
                tc.grau_escolaridade,
                tc.instituicao,
                tc.endereco,
                tc.numero,
                tc.complemento,
                tc.bairro,
                tc.cep,
                tc.cidade,
                tc.estado,
                tc.uf,
                tc.fone_residencial,
                tc.fone_comercial,
                tc.fone_celular,
                tc.desconto,
                tc.data_inclusao,
                tc.data_alteracao,
                tc.http_user_agent,
                tc.ip_cliente,
                tc.status,
                tc.session_id,
                (SELECT toa.titulo
                  FROM tab_opcoes_atuacoes_convidados toa
                  WHERE
                  (toa.cod_evento            = ".COD_EVENTO.")AND
                  (toa.cod_atuacao_convidado = tc.cod_atuacao_convidado)
                )
                ";


     #Definindo comando SQL
      $var_sql = "
                 SELECT ".$Var_Campos."
                 FROM tab_convidados tc 
                 inner join sis_ligacao_usuarios_eventos lue ON (lue.cod_usuario=tc.cod_convidado OR lue.cod_usuario=tc.cod_usuario)
                 WHERE
                 tc.cod_convidado = ".$COD_CONVIDADO." and lue.cod_evento=".COD_EVENTO."
                 ORDER BY tc.cod_convidado DESC
                ";

//    echo $var_sql;

    #Fim da definição do comando SQL

    #Executando a consulta no banco SQL
    $RS = $Conexao_db->Execute($var_sql);

    #Verificando se houve retorno de algum registro
    If (!$RS->EOF) {
      #Definindo cabeçalho da tabela dos resultados da consula

      #Efetuando loop para o visualização dos registros retornados pela consulta SQL
      While (!$RS->EOF) {

      #Adicionando nas variaveis, os valores dos campos retirados dos registros
        $cod_convidado         = $RS->fields[0];
        $cod_atuacao_convidado = $RS->fields[1];
        $nome_convidado        = $RS->fields[2];
        $cpf                   = $RS->fields[3];
        $rg                    = $RS->fields[4];
        $e_mail                = $RS->fields[5];
        $data_nascimento       = $RS->fields[6];
        $sexo                  = $RS->fields[7];
        $profissao_atuacao     = $RS->fields[8];
        $grau_escolaridade     = $RS->fields[9];;
        $instituicao           = $RS->fields[10];
        $endereco              = $RS->fields[11];
        $numero                = $RS->fields[12];
        $complemento           = $RS->fields[13];
        $bairro                = $RS->fields[14];
        $cep                   = $RS->fields[15];
        $cidade                = $RS->fields[16];
        $estado                = $RS->fields[17];
        $uf                    = $RS->fields[18];
        $fone_residencial      = $RS->fields[19];
        $fone_comercial        = $RS->fields[20];
        $fone_celular          = $RS->fields[21];
        $desconto              = $RS->fields[22];
        $data_inscricao        = $RS->fields[23];
        $data_alteracao        = $RS->fields[24];
        $http_user_agent       = $RS->fields[25];
        $ip_cliente            = $RS->fields[26];
        $status                = $RS->fields[27];
        $session_id            = $RS->fields[28];
        $nome_atuacao_convidado= $RS->fields[29];

        #Definindo tabela com resultado

      echo "
      <hr size='1' width='100%'>
      <span style class='texto'> <B> Ficha de Cadastro de Convidado</B>><br>
      <span style class='textop'> <B> * Campos Obrigatórios</B></span>

      <table border='0' width='565'>
      <tr><td colspan='2' align='left'>
      <FORM method='POST' name='form' action='view/admin/convidados-cadastro-alterar-grava.php' onsubmit=\"return criticar()\">
        <input type='hidden' name='COD_CONVIDADO' value='{$COD_CONVIDADO}'>
        <input type='hidden' name='COD_EVENTO'    value='{$COD_EVENTO}'>
        <input type='submit' value='Alterar'> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='reset' value='Limpar'>
        <br>&nbsp;
      </td></tr>
      </table>
      <fieldset width='570'><legend> Dados do Convidado </legend>
        <span style class='texto'>&nbsp;&nbsp;<b>Cógido do Convidado: {$COD_CONVIDADO}</b><br><br></span>
      ";

      include_once("convidados-cadastro-campos.php");

      echo "
      </fieldset>
      <table border='0' width='565'>
      <tr><td colspan='2' align='left'>
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
  echo "Ã Necessário Codigo do Convidado.";
}

?>
<hr size='1' width='100%'>
<a class='link' href='javascript:history.go(-1)'><b>voltar<b></a>
</body>
</html>
</div></div></div</div></div>
<?php
require_once(dirname(__FILE__)."/../../include/config.php");

require_once(dirname(__FILE__)."/../../view/admin/consulta-dados-congresso-cabecalho.php");

#require_once(dirname(__FILE__)."/../../view/admin/consulta-inscritos-busca.php");

$ORDER_BY = $_SESSION['orderby'];
If(@$ORDER_BY==""){
$ORDER_BY = "nome_inscrito";
}

$MSG   = $_SESSION['MSG'];

$MSG_Imprimir = $MSG;
$loc   = "/-/";
$subst = " ";
$MSG_Imprimir = preg_replace($loc,$subst,$MSG_Imprimir);

echo $MSG_Imprimir;



echo "<h4>
	  Lista dos Inscritos na<br>
      I CAMINHADA/CORRIDA DE 5 KM Â GENTE QUE FAZ SAÃDE
	  </h4><hr size='1' width='100%'>
     ";
  $Conexao_db = FWA_Conexao("OpcTec");

     #Definindo comando SQL
    $var_sql = "
                 SELECT
                        ti.nome_inscrito
                       ,ti.rg
                       ,ti.data_nascimento
					   ,ti.sexo
					   ,ti.categoria
                       ,ti.endereco
                       ,ti.numero
                       ,ti.complemento
                       ,ti.bairro
                       ,ti.cep
                       ,ti.cidade
                       ,ti.estado
                       ,ti.e_mail
                       ,ti.fone_residencial
                       ,ti.fone_comercial
                       ,ti.fone_celular
                       ,ti.p1
					   ,ti.p2
					   ,ti.p3
					   ,ti.p4
					   ,ti.p5
					   ,ti.p6
					   ,ti.p7
                       ,ti.data_inscricao
                       ,ti.status
                       ,ti.http_user_agent
                       ,ti.ip_cliente
                 FROM
                        tab_inscritos_corrida AS ti
                 WHERE
					  (ti.cod_evento = 1)
               ";

    If($FWS_Debug==1){ echo $var_sql."<br><br>";}

    #Fim da definição do comando SQL

    #Executando a consulta no banco SQL
    $RS = $Conexao_db->Execute($var_sql);

    #Verificando se houve retorno de algum registro
    If (!$RS->EOF) {
      #Definindo cabeçalho da tabela dos resultados da consula

      echo "<table border='0' width='670' CELLSPACING='2' CELLPADDING='0'>";

      $bgcolor = "F7F7F7";
      #Efetuando loop para o visualização dos registros retornados pela consulta SQL
      While (!$RS->EOF) {
		  @$i = $i+1;
		  #Adicionando nas variaveis, os valores dos campos retirados dos registros
		  $nome_inscrito   = $RS->fields[0];
		  $rg              = $RS->fields[1];
		  $data_nascimento = $RS->fields[2];
		  $sexo            = $RS->fields[3];
		  $categoria       = $RS->fields[4];
		  $endereco        = $RS->fields[5];
		  $numero          = $RS->fields[6];
		  $complemento     = $RS->fields[7];
		  $bairro          = $RS->fields[8];
		  $cep             = $RS->fields[9];
		  $cidade          = $RS->fields[10];
		  $estado          = $RS->fields[11];
		  $e_mail          = $RS->fields[12];
		  $fone_residencial= $RS->fields[13];
		  $fone_comercial  = $RS->fields[14];
		  $fone_celular    = $RS->fields[15];
		  $p1              = $RS->fields[16];
		  $p2              = $RS->fields[17];
		  $p3              = $RS->fields[18];
		  $p4              = $RS->fields[19];
		  $p5              = $RS->fields[20];
		  $p6              = $RS->fields[21];
		  $p7              = $RS->fields[22];
		  $data_inscricao  = $RS->fields[23];
		  $status          = $RS->fields[24];
		  $http_user_agent = $RS->fields[25];
		  $ip_cliente      = $RS->fields[26];

		  #Definindo tabela com resultado

		  If($bgcolor=="F7F7F7"){
		    $bgcolor = "FFFFFF";
		  }Else{
		    $bgcolor="F7F7F7";
		  }

echo <<<corpo
             <tr bgcolor=#{$bgcolor}>
               <td>
				<br>Nome_inscrito   	 - {$nome_inscrito}
				<br>
				<br>RG              	 - {$rg}
				<br>Data_nascimento 	 - {$data_nascimento}
				<br>Sexo            	 - {$sexo}
				<br>Categoria       	 - {$categoria}
				<br>Endereco        	 - {$endereco}
				<br>Numero          	 - {$numero}
				<br>Complemento     	 - {$complemento}
				<br>Bairro          	 - {$bairro}
				<br>Cep             	 - {$cep}
				<br>Cidade          	 - {$cidade}
				<br>Estado          	 - {$estado}
				<br>E_mail          	 - {$e_mail}
				<br>Fone_residencial	 - {$fone_residencial}
				<br>Fone_comercial  	 - {$fone_comercial}
				<br>Fone_celular    	 - {$fone_celular}
				<br>
				<br>P1              	 - {$p1}
				<br>P2              	 - {$p2}
				<br>P3              	 - {$p3}
				<br>P4              	 - {$p4}
				<br>P5              	 - {$p5}
				<br>P6              	 - {$p6}
				<br>P7              	 - {$p7}
				<br>
				<br>Data_inscricao  	 - {$data_inscricao}
				<br>Status          	 - {$status}
				<hr size='1' width='100%'>
               </td>
             </tr>
corpo;

        #Movendo para o proximo registro
        $RS->MoveNext();
      }

echo "<tr><td>Total de Inscritos: ".$i."</td></tr></table>";

    }Else{

        echo "
              <br><br>A busca não retornou nenhum registro, o sistema não possui nenhum inscrito.<br><br>
             ";
    }
?>
</body>
</html>


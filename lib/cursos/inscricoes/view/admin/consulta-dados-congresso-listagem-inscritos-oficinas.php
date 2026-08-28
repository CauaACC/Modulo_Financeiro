<?php
require_once(dirname(__FILE__)."/../../include/config.php");

require_once(dirname(__FILE__)."/../../view/admin/consulta-dados-congresso-cabecalho.php");

#require_once(dirname(__FILE__)."/../../view/admin/consulta-dados-congresso-busca.php");
   
If((isset($_SESSION['cod_oficina']))and($_SESSION['cod_oficina']!="")){

    $Conexao_db = FWA_Conexao("SRedeUnida");

    ## Consulta Trabalhos por Área Temática ##
    
    #Definindo comando SQL
    /*                  
                 ,(SELECT Count(tlio.cod_oficina) as Total_Inscritos_Oficinas
                   FROM tab_lig_inscritos_oficinas tlio, tab_inscritos ti, tab_opcoes_oficinas to
                   WHERE (tlio.cod_evento   = ".COD_EVENTO.")and
                         (tlio.cod_oficina  = ".$_SESSION['cod_oficina'].")and
                         (tlio.cod_oficina  = toa.cod_oficina)and
                         (tlio.cod_inscrito = ti.cod_inscrito)and
                         (ti.status         = 'ATIVADA')
                   GROUP BY tlio.cod_oficina)                  
    */

    
    $var_sql = "
                 SELECT 
                  toa.titulo
                 ,toa.numero_vagas
                 ,(SELECT Count(cod_oficina)
                  FROM
                    (SELECT  ti.cpf, toa.cod_oficina
                     FROM tab_lig_inscritos_oficinas tlio, tab_inscritos ti, tab_opcoes_oficinas to 
                     WHERE (tlio.cod_evento  = ".COD_EVENTO.")
                       and (tlio.cod_oficina = ".$_SESSION['cod_oficina'].")
                       and (tlio.cod_oficina = toa.cod_oficina)
                       and (tlio.cod_inscrito = ti.cod_inscrito)
                       and(ti.status = 'ATIVADA')
                     GROUP BY ti.cpf, toa.cod_oficina
                    )
                  GROUP BY cod_oficina) as Total_Inscritos_Of_por_CPF
                 ,ti.cod_inscrito
                 ,ti.nome_inscrito
                 ,ti.cpf
                 ,ti.rg
                 ,ti.e_mail
                 ,ti.data_nascimento
                 ,ti.sexo
                 ,ti.endereco
                 ,ti.numero
                 ,ti.complemento
                 ,ti.bairro
                 ,ti.cep
                 ,ti.cidade
                 ,ti.estado
                 ,ti.fone_residencial
                 ,ti.fone_comercial
                 ,ti.fone_celular
                 ,ti.profissao_atuacao
                 ,ti.instituicao
                 ,ti.desconto
                 FROM tab_lig_inscritos_oficinas tlio, tab_inscritos ti, tab_opcoes_oficinas to
                 WHERE (tlio.cod_evento   = ".COD_EVENTO.")and
                       (tlio.cod_oficina  = ".$_SESSION['cod_oficina'].")and
                       (tlio.cod_oficina  = toa.cod_oficina)and                   
                       (tlio.cod_inscrito = ti.cod_inscrito)and
                       (ti.status         = 'ATIVADA')
                 ORDER BY ti.nome_inscrito, ti.cod_inscrito
               ";#GROUP BY toa.titulo, toa.cod_area_tematica

    #echo $var_sql;

    #Fim da definição do comando SQL

    #Executando a consulta no banco SQL
    $RS = $Conexao_db->Execute($var_sql);

    #Verificando se houve retorno de algum registro
      
    If (!$RS->EOF) {
      $Total_Vagas_Disponiveis = $RS->fields[1]-$RS->fields[2];

      #Definindo cabeçalho da tabela dos resultados da consula
      echo "
      <table border='0' width='100%'>
      <tr><td colspan='3' class='subtitulo'>Oficina:<br><br><b>{$RS->fields[0]}</b><br>&nbsp;</td></tr>
      <tr><td colspan='3' >
              | Total de Vagas:             {$RS->fields[1]}   
        &nbsp;| Total de inscritos por CPF: {$RS->fields[2]}
        &nbsp;| Total de vagas disponíveis: {$Total_Vagas_Disponiveis}
        &nbsp;|
      </td></tr>
      <tr><td colspan='3' >Segue abaixo lista de inscritos:   
      </td></tr>
      ";
      
      #Efetuando loop para o visualização dos registros retornados pela consulta SQL
      While (!$RS->EOF) {

      #Adicionando nas variaveis, os valores dos campos retirados dos registros

        $titulo                     = $RS->fields[0];
        $numero_vagas               = $RS->fields[1];
        $Total_Inscritos_Of_por_CPF = $RS->fields[2];
        $cod_inscrito               = $RS->fields[3];
        $nome_inscrito              = $RS->fields[4];
        $cpf                        = $RS->fields[5];
        $rg                         = $RS->fields[6];
        $e_mail                     = $RS->fields[7];
        $data_nascimento            = $RS->fields[8];
        $sexo                       = $RS->fields[9];
        $endereco                   = $RS->fields[10];
        $numero                     = $RS->fields[11];
        $complemento                = $RS->fields[12];
        $bairro                     = $RS->fields[13];
        $cep                        = $RS->fields[14];
        $cidade                     = $RS->fields[15];
        $estado                     = $RS->fields[16];
        $fone_residencial           = $RS->fields[17];
        $fone_comercial             = $RS->fields[18];
        $fone_celular               = $RS->fields[19];
        $profissao_atuacao          = $RS->fields[20];
        $instituicao                = $RS->fields[21];
        $desconto                   = $RS->fields[22];
        
        #Definindo tabela com resultado
        
echo <<<corpo
      <tr><td colspan='3'><hr size='1' width='100%'>
      <TABLE WIDTH='100%' BORDER='0' CELLSPACING='0' CELLPADDING='4'>
         
         <TR>
           <TD VALIGN='top' colspan='2'>
             <a class='link' href='index.php?content=view/admin/inscritos-cadastro-ok.php&acao=Visualizar&cod_inscrito={$cod_inscrito}&cod_evento={COD_EVENTO}'>
             <b>{$cod_inscrito}</b> - <b>{$nome_inscrito}</a>
           </TD>
         </TR>
         <TR><TD VALIGN='top' colspan='2'>E-Mail: <b>{$e_mail}            </TD></TR>
corpo;

if(@$_SESSION['detalhes']==1){
echo <<<detalhes
         <TR><TD VALIGN='top' width='30'>CPF:      </TD><TD width='100%'>                  <b>{$cpf}               </TD></TR>
         <TR><TD VALIGN='top' >RG:       </TD><TD>                  <b>{$rg}                </TD></TR>
         <TR><TD VALIGN='top' >E-Mail:   </TD><TD>                  <b>{$e_mail}            </TD></TR>
         <TR><TD VALIGN='top' COLSPAN='2'>Data de Nascimento (dd/mm/aaaa): &nbsp;&nbsp;  <b>{$data_nascimento}</TD></TR>
         <TR><TD VALIGN='top' COLSPAN='2'>Sexo:                     <b>{$sexo}              </TD></TR>
         <TR><TD VALIGN='top' >Rua:      </TD><TD>                  <b>{$endereco}          </TD></TR>
         <TR><TD VALIGN='top' >Número:   </TD><TD>                  <b>{$numero}            </TD></TR>
         <TR><TD VALIGN='top' COLSPAN='2'>Complemento:              <b>{$complemento}       </TD></TR>
         <TR><TD VALIGN='top' >Bairro:   </TD><TD>                  <b>{$bairro}</b> &nbsp;&nbsp;&nbsp; Cep:     <b>{$cep}</b>    </TD></TR>
         <TR><TD VALIGN='top' >Cidade:   </TD><TD>                  <b>{$cidade}</b> &nbsp;&nbsp;&nbsp; Estado:  <b>{$estado}</b> </TD></TR>
         <TR><TD VALIGN='top' COLSPAN='2'>Telefone Res:             <b>{$fone_residencial}  </TD></TR>
         <TR><TD VALIGN='top' COLSPAN='2'>Telefone Com:             <b>{$fone_comercial}    </TD></TR>
         <TR><TD VALIGN='top' COLSPAN='2'>Telefone Cel:             <b>{$fone_celular}      </TD></TR>
         <TR><TD VALIGN='top' COLSPAN='2'>Profissão/Atuação:        <b>{$profissao_atuacao} </TD></TR>
         <TR><TD VALIGN='top' COLSPAN='2'>Instituição/Entidade:     <b>{$instituicao}       </TD></TR>
         <TR><TD VALIGN='top' COLSPAN='2'>Inscrição com Desconto:   <b>{$desconto}</TD></TR>
detalhes;
}
echo "    </TABLE></td></tr>";


        #Movendo para o proximo registro
        $RS->MoveNext();
      }
      echo "</table><br>";
    }Else{
       echo "O código da oficina não existe.";   
    }    
}Else{
   echo "O código da oficina não foi informado.";   
}    
?>
<hr size='1' width='100%'>
<a class='link' href='index.php?content=view/admin/consulta-dados-congresso-listagem.php'><b>voltar<b></a>
</body>
</html>

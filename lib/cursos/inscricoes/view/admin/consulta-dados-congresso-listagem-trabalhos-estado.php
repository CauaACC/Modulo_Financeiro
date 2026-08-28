<?php
require_once(dirname(__FILE__)."/../../include/config.php");

require_once(dirname(__FILE__)."/../../view/admin/consulta-dados-congresso-cabecalho.php");

#require_once(dirname(__FILE__)."/../../view/admin/consulta-dados-congresso-busca.php");

$estado = $_SESSION['estado'];

If(isset($estado)){

    $Conexao_db = FWA_Conexao("OpcTec");

    ## Consulta Trabalhos por Estado ##
    
    #Definindo comando SQL
    $var_sql = "
                 SELECT 
                  ti.estado
                 , (SELECT Count(tt.cod_trabalho) as Total_Trabalhos_Enviados
                   FROM tab_trabalhos tt, tab_inscritos ti 
                   WHERE (tt.cod_evento   = ".COD_EVENTO.")and
                         (tt.cod_inscrito = ti.cod_inscrito)and
                         (ti.estado       = '".$estado."')and
                         (ti.status           ='ATIVADA')
                         )
                 ,ti.cod_inscrito
                 ,ti.nome_inscrito
                 ,tt.autor_apresentador_nome
                 ,tt.autor_apresentador_e_mail 
                 ,tt.nome_co_autor_1            
                 ,tt.nome_co_autor_2            
                 ,tt.nome_co_autor_3            
                 ,tt.nome_co_autor_4            
                 ,tt.nome_co_autor_5            
                 ,tt.instituicao_trabalho       
                 ,tt.titulo_trabalho            
                 ,tt.palavras_chave             
                 ,tt.resumo_trabalho            
                 FROM tab_trabalhos tt, tab_inscritos ti, tab_opcoes_areas_tematicas toa
                 WHERE (tt.cod_evento        = ".COD_EVENTO.")and
                       (tt.cod_area_tematica = toa.cod_area_tematica)and                   
                       (tt.cod_inscrito      = ti.cod_inscrito)and
                       (ti.estado            = '".$estado."')and
                       (ti.status            = 'ATIVADA')
                 ORDER BY ti.cod_inscrito
               ";

    #echo $var_sql;

    #Fim da definição do comando SQL

    #Executando a consulta no banco SQL
    $RS = $Conexao_db->Execute($var_sql);

    #Verificando se houve retorno de algum registro
    If (!$RS->EOF) {
      #Definindo cabeçalho da tabela dos resultados da consula
      echo "
      <table border='0' width='100%'>
      <tr><td colspan='3' class='subtitulo'>Estado:<br><br><b>{$RS->fields[0]}</b><br>&nbsp;</td></tr>
      <tr><td colspan='3' >Total de trabalhos inscritos neste estado:  {$RS->fields[1]}   </td></tr>
      <tr><td colspan='3' >Segue abaixo lista de trabalhos inscritos:    </td></tr>
      ";
      #Efetuando loop para o visualização dos registros retornados pela consulta SQL
      While (!$RS->EOF) {

      #Adicionando nas variaveis, os valores dos campos retirados dos registros
        $titulo                     = $RS->fields[0];
        $Total_Trabalhos_Enviados   = $RS->fields[1];
        $cod_inscrito               = $RS->fields[2];
        $nome_inscrito              = $RS->fields[3];
        $autor_apresentador_nome    = $RS->fields[4];
        $autor_apresentador_e_mail  = $RS->fields[5];
        $nome_co_autor_1            = $RS->fields[6];
        $nome_co_autor_2            = $RS->fields[7];
        $nome_co_autor_3            = $RS->fields[8];
        $nome_co_autor_4            = $RS->fields[9];
        $nome_co_autor_5            = $RS->fields[10];
        $instituicao_trabalho       = $RS->fields[11];
        $titulo_trabalho            = $RS->fields[12];
        $palavras_chave             = $RS->fields[13];
        $resumo_trabalho            = $RS->fields[14];
        

        #Definindo tabela com resultado
        
echo <<<corpo
      <tr><td colspan='3'><hr size='1' width='100%'>
      <TABLE WIDTH='100%' BORDER='0' CELLSPACING='0' CELLPADDING='4'>
        <TR><TD VALIGN='top'>
              <a class='link' href='index.php?content=view/admin/inscritos-cadastro-ok.php&acao=Visualizar&cod_inscrito={$cod_inscrito}&cod_evento={COD_EVENTO}'>
              Cógido do Inscrito: <b>{$cod_inscrito}</b> - <b>{$nome_inscrito}</b></a> 
        </TD></TR>
corpo;

if(@$_SESSION['detalhes']==1){
echo <<<detalhes
        <TR><TD VALIGN='top'>
              Nome do Apresentador: <b>{$autor_apresentador_nome}</b>
        </TD></TR>
        <TR><TD VALIGN='top'>
              E-mail do Apresentador: <b>{$autor_apresentador_e_mail}</b>
        </TD></TR>
        <TR><TD VALIGN='top'>
              Nomes dos Co-Autores:
          </TD>  
        </TR>
        <TR><TD VALIGN='top'>1º - Co-autor: <b>{$nome_co_autor_1}</b></TD></TR>
        <TR><TD VALIGN='top'>2º - Co-autor: <b>{$nome_co_autor_2}</b></TD></TR>
        <TR><TD VALIGN='top'>3º - Co-autor: <b>{$nome_co_autor_3}</b></TD></TR>
        <TR><TD VALIGN='top'>4º - Co-autor: <b>{$nome_co_autor_4}</b></TD></TR>
        <TR><TD VALIGN='top'>5º - Co-autor: <b>{$nome_co_autor_5}</b></TD></TR>

        <TR><TD VALIGN='top'>
              Instituição:<br><b>{$instituicao_trabalho}</b>
        </TD></TR>
        <TR><TD VALIGN='top'>
              Titulo do Trabalho:<br><b>{$titulo_trabalho}</b>
        </TD></TR>
        <TR><TD VALIGN='top'>
              Palavras-chave:<br><b>{$palavras_chave}</b>
        </TD></TR>
        <TR><TD VALIGN='top'>
              Resumo do Trabalho:
              <p ALIGN='justify'>
              <b>{$resumo_trabalho}</b>
        </TD></TR>
detalhes;
}
echo "    </TABLE></td></tr>";

        #Movendo para o proximo registro
        $RS->MoveNext();
      }
      echo "</table><br>";
    }Else{
       echo "O código da área temática não existe.";   
    }    

}Else{
   echo "O código da área temática não foi informado.";   
}    
?>
<hr size='1' width='100%'>
<a class='link' href='index.php?content=view/admin/consulta-dados-congresso-listagem.php'><b>voltar<b></a>
</body>
</html>

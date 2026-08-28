<?php
require_once(dirname(__FILE__)."/../../include/config.php");

require_once(dirname(__FILE__)."/../../view/admin/consulta-dados-congresso-cabecalho.php");

#require_once(dirname(__FILE__)."/../../view/admin/consulta-dados-congresso-busca.php");

$cod_premiacao_cientifica  = $_SESSION['cod_premiacao_cientifica'];

If(isset($cod_premiacao_cientifica )){

    $Conexao_db = FWA_Conexao("OpcTec");

    ## Consulta Trabalhos por Área Temática ##
    
    #Definindo comando SQL
    $var_sql = "
                 SELECT 
                  top.titulo
                 , (SELECT Count(tm.cod_monografia) as Total_Monografias_Enviadas
                   FROM tab_monografias tm, tab_inscritos ti 
                   WHERE (tm.cod_evento               = ".COD_EVENTO.")and
                         (tm.cod_premiacao_cientifica = ".$cod_premiacao_cientifica.")and
                         (tm.cod_inscrito             = ti.cod_inscrito)and
                         (tm.status                   = 'ATIVADA')and
                         (ti.status                   = 'ATIVADA'))
                 ,ti.cod_inscrito             
                 ,ti.nome_inscrito            
                 ,tm.autor_nome               
                 ,tm.autor_e_mail             
                 ,tm.autor_pseudonimo         
                 ,tm.instituicao_monografia   
                 ,tm.titulo_monografia        
                 ,tm.arquivo_upload
                 FROM tab_monografias tm, tab_inscritos ti,tab_opcoes_premiacoes_cientifica top 
                 WHERE (tm.cod_evento               = ".COD_EVENTO.")and
                       (tm.cod_premiacao_cientifica = ".$cod_premiacao_cientifica.")and
                       (tm.cod_premiacao_cientifica = top.cod_premiacao_cientifica)and                   
                       (tm.cod_inscrito             = ti.cod_inscrito)and
                       (tm.status                   = 'ATIVADA')and
                       (ti.status                   = 'ATIVADA')
                 ORDER BY ti.cod_inscrito
               ";#GROUP BY toa.titulo, toa.cod_premiacao_cientifica

    #echo $var_sql;

    #Fim da definição do comando SQL

    #Executando a consulta no banco SQL
    $RS = $Conexao_db->Execute($var_sql);

    #Verificando se houve retorno de algum registro
    If (!$RS->EOF) {
      #Definindo cabeçalho da tabela dos resultados da consula
      echo "
      <table border='0' width='100%'>
      <tr><td colspan='3' class='subtitulo'>Premiação Científica:<br><br><b>{$RS->fields[0]}</b><br>&nbsp;</td></tr>
      <tr><td colspan='3' >Total de inscritos nesta premiação científica:   {$RS->fields[1]}   </td></tr>
      <tr><td colspan='3' >Segue abaixo lista de monografias:    </td></tr>
      ";
      #Efetuando loop para o visualização dos registros retornados pela consulta SQL
      While (!$RS->EOF) {

      #Adicionando nas variaveis, os valores dos campos retirados dos registros

        $titulo                   = $RS->fields[0];
        $Total_Inscritos_Oficinas = $RS->fields[1];
        $cod_inscrito             = $RS->fields[2];
        $nome_inscrito            = $RS->fields[3];
        $autor_nome               = $RS->fields[4];
        $autor_e_mail             = $RS->fields[5];
        $autor_pseudonimo         = $RS->fields[6];
        $instituicao_monografia   = $RS->fields[7];
        $titulo_monografia        = $RS->fields[8];
        $arquivo_upload           = $RS->fields[9];
        
        $Var_caminho_arquivo = "upload/monografia/".COD_EVENTO."-".$cod_inscrito.".doc";
        
        #Definindo tabela com resultado
        
echo <<<corpo
      <tr><td colspan='3'><hr size='1' width='100%'>
      <TABLE WIDTH='100%' BORDER='0' CELLSPACING='0' CELLPADDING='4'>
        <TR><TD VALIGN='top'>
              <a class='link' href='index.php?content=view/admin/inscritos-cadastro-ok.php&acao=Visualizar&cod_inscrito={$cod_inscrito}&cod_evento={COD_EVENTO}'>
              Código de Inscrito: <b>{$cod_inscrito}</b> - <b>{$nome_inscrito}</b></a>
        </TD></TR>
corpo;

if(@$_SESSION['detalhes']==1){
echo <<<detalhes
        <TR><TD VALIGN='top'>
              Nome do Autor: <b>{$autor_nome}</b>
        </TD></TR>
        <TR><TD VALIGN='top'>
              E-mail do Autor: <b>{$autor_e_mail}</b>
        </TD></TR>
        <TR><TD VALIGN='top'>
              PseudÃ´nimo do Autor: <b>{$autor_pseudonimo}</b>
        </TD></TR>
        <TR><TD VALIGN='top'>
              Instituição: <b>{$instituicao_monografia}</b>
        </TD></TR>
        <TR><TD VALIGN='top' class='textop'>
              Titulo da Monografia: <b>{$titulo_monografia}</b>
        </TD></TR>
        <TR><TD VALIGN='top' class='textop'>
              Nome do Arquivo da Monografia Anexada: <b>{$arquivo_upload}</b>
        </TD></TR>
        <TR><TD VALIGN='top' class='textop'>        
         <a href='{$Var_caminho_arquivo}' target='blank' class='linkv'><b>Clique aqui para abrir arquivo da monografia</b></a>
        </TD></TR>         
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

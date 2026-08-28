<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/re-avaliacao-trabalhos-cabecalho.php");
#require_once(dirname(__FILE__)."/../../view/admin/re-avaliacao-trabalhos-busca.php");

If((isset($cod_area_tematica))and(isset($cod_trabalho))){

    $Conexao_db = FWA_Conexao("OpcTec");
    ## Consulta Trabalhos por Área Temática ##
    
    #Definindo comando SQL
    $var_sql = "
                 SELECT 
                  toa.titulo
                 ,ti.cod_inscrito
                 ,ti.nome_inscrito
                 ,tt.cod_trabalho
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
                       (tt.cod_area_tematica = ".$cod_area_tematica.")and                   
                       (tt.cod_trabalho      = ".$cod_trabalho.")and                   
                       (tt.cod_area_tematica = toa.cod_area_tematica)and                   
                       (tt.cod_inscrito      = ti.cod_inscrito)and
                       (ti.status            = 'ATIVADA')
                 ORDER BY ti.cod_inscrito
               ";#GROUP BY toa.titulo, toa.cod_area_tematica

    #echo "<br>".$var_sql."<br>";

    #Fim da definição do comando SQL

    #Executando a consulta no banco SQL
    $RS = $Conexao_db->Execute($var_sql);

    #Verificando se houve retorno de algum registro
    If (!$RS->EOF) {
      #Definindo cabeçalho da tabela dos resultados da consula
      echo "
      <table border='0' width='100%' colspan='2' cellspacing='3'>
      <tr><td colspan='3' class='subtitulo'>Área Temática:<br><br><b>{$RS->fields[0]}</b><br>&nbsp;</td></tr>
      <tr><td colspan='3'>
      Segue abaixo lista de titulo e resumo de trabalho a ser avaliado:
      <hr size='1' width='100%'>
      </td></tr>
      ";
      #Efetuando loop para o visualização dos registros retornados pela consulta SQL
      While (!$RS->EOF) {

      #Adicionando nas variaveis, os valores dos campos retirados dos registros
        $titulo                     = $RS->fields[0];
        $cod_inscrito               = $RS->fields[1];
        $nome_inscrito              = $RS->fields[2];
        $cod_trabalho               = $RS->fields[3];
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
        <tr><td colspan='3'>
          <b>Titulo:</b>
          <br>
          {$titulo_trabalho}
          <br>
          <p align='justify'>
          <b>Resumo:</b>
          <br>
          {$resumo_trabalho}
          <BR><BR>
         
          <SCRIPT LANGUAGE="JavaScript1.2" src="resources/js/avaliacao-trabalhos.js"></SCRIPT>
          
          <FORM method='POST' name='form' action='view/admin/re-avaliacao-trabalhos-grava.php' onsubmit='return criticar()'>
          
          <b>Avalição:</b>&nbsp;&nbsp;&nbsp;
          <input type='radio' name='avaliacao' value='APROVADO'> Aprovado
          &nbsp;&nbsp;&nbsp;
          <input type='radio' name='avaliacao' value='REPROVADO'> Reprovado
          <BR><BR>
          <b>Justificativa (Exigida somente em caso de reprovação):</b>
          <br>
          <textarea name='observacao' cols='100' rows='10'></textarea>
          <BR><BR>          
          <input type='hidden' value='{$COD_EVENTO}'        name='cod_evento'>
          <input type='hidden' value='{$cod_area_tematica}' name='cod_area_tematica'>
          <input type='hidden' value='{$cod_inscrito}'      name='cod_inscrito'>          
          <input type='hidden' value='{$cod_trabalho}'      name='cod_trabalho'>          
          <input type='hidden' value='Avaliação'            name='acao'>
          <input type='submit' value='Clique aqui para Confirmar Avaliação'>
          </form>

        </td></tr>

corpo;

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
<a class='link' href='javascript:history.go(-1)'><b>voltar<b></a>
</body>
</html>
</div></div></div></div></div></div>

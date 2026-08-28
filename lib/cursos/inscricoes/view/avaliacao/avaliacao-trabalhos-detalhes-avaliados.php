<?php
require_once(dirname(__FILE__)."/../../include/config.php");
?>
<h3>Área do Avaliador</h3>  
<div class="panel-group">
    <div class="panel panel-default">
        <div class="panel-heading">
            <?php require_once(dirname(__FILE__)."/../../view/avaliacao/avaliacao-trabalhos-cabecalho.php"); ?>
        </div>
        <div class="panel-body">
            <h3>Avaliação de Trabalhos</h3>
            <hr>
<?php
if($fws_['cod_area_tematica']==''){
    $fws_['cod_area_tematica']=$_GET['cod_area_tematica'];
}
if($fws_['cod_trabalho']==''){
    $fws_['cod_trabalho']=$_GET['cod_trabalho'];
}

If((isset($fws_['cod_area_tematica']))and(isset($fws_['cod_trabalho']))){

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
                 ,tt.nome_co_autor_6            
                 ,tt.instituicao_trabalho       
                 ,tt.titulo_trabalho            
                 ,tt.palavras_chave             
                 ,tt.resumo_trabalho
                 ,ta.avaliacao
                 ,ta.observacao
                 ,tt.arquivo_upload
                 ,tt.arquivo_gerado
                 ,tt.formato_texto
                 ,ta.criterio_1
                 ,ta.criterio_2
                 ,ta.criterio_3
                 ,ta.criterio_4
                 ,ta.criterio_5
                 ,ta.criterio_6
                 ,ta.indicar_premiacao
                 ,ta.alterar_area_tematica
                 FROM tab_trabalhos tt, tab_inscritos ti, tab_opcoes_areas_tematicas toa, tab_trabalhos_avaliacao ta
                 WHERE (tt.cod_evento        = ".COD_EVENTO."       )and
                       (tt.cod_trabalho      = ".$fws_['cod_trabalho']."     )and                   
                       (tt.cod_area_tematica = toa.cod_area_tematica  )and                   
                       (tt.cod_inscrito      = ti.cod_inscrito       )and
                       (tt.cod_evento        = ta.cod_evento         )and
                       (tt.cod_area_tematica = ta.cod_area_tematica  )and                   
                       (tt.cod_trabalho      = ta.cod_trabalho       )and                                   
                       (tt.cod_inscrito      = ta.cod_inscrito       )and
                       (ti.status           = 'ATIVADA')
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
      <!--<tr><td colspan='3' class='subtitulo'>Área Temática:<br><br><b>{$RS->fields[0]}</b><br>&nbsp;</td></tr>-->
      <!--<tr><td colspan='3'>
      Segue abaixo titulo e resumo de trabalho <b>AVALIADO</b>:
      <hr size='1' width='100%'>
      </td></tr>-->
      ";
      #Efetuando loop para o visualização dos registros retornados pela consulta SQL
      While (!$RS->EOF) {

      #Adicionando nas variaveis, os valores dos campos retirados dos registros
        $titulo_area_tematica       = $RS->fields[0];
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
        $nome_co_autor_6            = $RS->fields[11];
        $instituicao_trabalho       = $RS->fields[12];
        $titulo_trabalho            = $RS->fields[13];
        $palavras_chave             = $RS->fields[14];
        $resumo_trabalho            = $RS->fields[15];
        $avaliacao                  = $RS->fields[16];
        $observacao                 = $RS->fields[17];
        $arquivo_upload             = $RS->fields[18];
        $arquivo_gerado             = $RS->fields[19];
        $formato_texto              = $RS->fields[20];
        $criterio_1                 = $RS->fields[21];
        $criterio_2                 = $RS->fields[22];
        $criterio_3                 = $RS->fields[23];
        $criterio_4                 = $RS->fields[24];
        $criterio_5                 = $RS->fields[25];
        $criterio_6                 = $RS->fields[26];
        $indicar_premiacao          = $RS->fields[27];
        $alterar_area_tematica       = $RS->fields[28];
        
        $nota = $criterio_1+$criterio_2+$criterio_3+$criterio_4+$criterio_5+$criterio_6;
        ($indicar_premiacao=='' or $indicar_premiacao==0)? $indicar_premiacao='NÃO' : $indicar_premiacao='SIM';
        ($alterar_area_tematica=='' or $alterar_area_tematica==0)? $alterar_area_tematica='NÃO' : $alterar_area_tematica='SIM';
        #Definindo tabela com resultado

        echo "
        <tr><td colspan='3'>
        <b>Titulo:</b><h4 style='color:#337ab7;'>{$titulo_trabalho}</h4><br>
        <p align='justify'>
        <b>Palavras-chave:</b>
        <br>{$palavras_chave}<br>
        ";          
        if ($resumo_trabalho != ''){
            echo "<br>
                <b>Resumo:</b>
                <br>{$resumo_trabalho}
            ";
        }
        if ($arquivo_gerado != ''){
            echo "
                <br><a href=\"javascript: visualiza('{$arquivo_gerado}');\"  title='Visualizar Resumo'>
                    <img src='resources/image/doc.gif' border='0'>
                    Visualizar Arquivo Resumo</a>
                <br>
            ";
        }
        echo "<br><br>
        <b>Eixo Temático</b>:
        <br>{$titulo_area_tematica}<br>";
        
        echo "<hr><h4>Decisão de Avaliação: {$avaliacao}</h4>";
       
        echo "
        <b>Nota:</b>&nbsp;&nbsp;&nbsp;{$nota}
        <br><b>Sugerir Alteração de Eixo Temático:</b>&nbsp;&nbsp;&nbsp;{$alterar_area_tematica}
        <br><b>Indicar para Prêmio Inova Saúde Paraná:</b>&nbsp;&nbsp;&nbsp;{$indicar_premiacao}
        <br><br>
        <b>Justificativa:</b>
        <br>
        {$observacao}
        <br><br>  
        </td></tr>
";

        #Movendo para o proximo registro
        $RS->MoveNext();
      }
      echo "</table><br>";
    }Else{
       echo "Nenhum trabalho foi avaliado até o momento.";   
    }    

}Else{
   echo "O código da área temática não foi informado.";   
}    
?>
<hr size='1' width='100%'>
<!--a class='link' href='javascript:history.go(-1)'><b>voltar<b></a-->
<a class='link' href='index.php?content=view/avaliacao/avaliacao-trabalhos-listagem-por-avaliador.php&cod_area_tematica=<?php echo $fws_['cod_area_tematica']?>&detalhes=<?php echo $fws_['detalhes']?>'><b>voltar<b></a>
</body>
</html>
</div>
</div>
<form name="getdoc" method="post" action="<?php echo WB_URL_COMMAND?>" target="_blank">
    <input type="hidden" name="c" value="GerenciarUpload">
    <input type="hidden" name="m" value="abrirWord">
    <input type="hidden" value="" name="cod">
</form>
<SCRIPT LANGUAGE="JavaScript1.2" src="resources/js/avaliacao-trabalhos.js"></SCRIPT>
<script language="JavaScript" type="text/javascript">
    function visualiza(cod){
        document.getdoc.cod.value=cod;
        document.getdoc.submit();
    }
</script>
</div></div></div>
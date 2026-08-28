<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/consulta-dados-congresso-cabecalho.php");
#require_once(dirname(__FILE__)."/../../view/admin/consulta-dados-congresso-busca.php");
echo "<div class=\"panel-body\">";
$Conexao_db = FWA_Conexao("OpcTec");

#------------------------------------------------------
## Consulta Trabalhos por Área Temática/Aprovado ##
#Definindo comando SQL
$var_sql = "
                 SELECT Count(tt.cod_trabalho) as Total_Trabalhos_Enviados, toa.titulo, toa.cod_area_tematica, toa.ordem, tt.formato_texto
                 FROM tab_trabalhos tt, tab_inscritos ti, tab_opcoes_areas_tematicas toa, tab_trabalhos_avaliacao ta 
                 WHERE (tt.cod_evento  =" . COD_EVENTO . ")and
                       (tt.cod_area_tematica=toa.cod_area_tematica)and
                       (tt.cod_inscrito=ti.cod_inscrito)and
                       (ti.status='ATIVADA')and
                       (tt.status='ATIVADA')and 
                       tt.cod_trabalho=ta.cod_trabalho
                       AND ta.avaliacao='APROVADO'  AND ta.status='ATIVADA'
                 GROUP BY toa.titulo, toa.cod_area_tematica, toa.ordem, tt.formato_texto
                 ORDER BY toa.titulo, tt.formato_texto
               ";

#echo $var_sql;
#Fim da definição do comando SQL
#Executando a consulta no banco SQL
$RS = $Conexao_db->Execute($var_sql);

#Verificando se houve retorno de algum registro
If (!$RS->EOF) {
    #Definindo cabeçalho da tabela dos resultados da consula

    #Efetuando loop para o visualização dos registros retornados pela consulta SQL
    $varFormatoTexto = "['Pesquisa Original', 'Relato de Experiência'],";
    $cod_area_ant = '';
    While (!$RS->EOF) {
        #Adicionando nas variaveis, os valores dos campos retirados dos registros
        $total_trabalhos = $RS->fields[0];
        $titulo_area     = $RS->fields[1];
        $cod_area        = $RS->fields[2];
        $ordem           = $RS->fields[3];
        $formato_texto   = $RS->fields[4];
        #Definindo tabela com resultado
          
        if($formato_texto=='Pesquisa Original'){
            $total_pequisa_original = $total_trabalhos;
        }
        if($formato_texto=='Relato de Experiência'){
            $total_relato_experiencia = $total_trabalhos;
        }
        if($cod_area_ant!=$cod_area){
            $varGraficoTrabalhosAprovadosFT .= "['" . $cod_area . "'," . $total_pequisa_original;
            $listaAreas .= $cod_area . " - " . $titulo_area . "<br>";
            $listaTabela .= "<tr><td>".$cod_area . "</td><td>" . $titulo_area . "</td><td></td><td>".$total_pequisa_original;
        }else{
            $varGraficoTrabalhosAprovadosFT .=",".$total_relato_experiencia."],";
            $listaTabela .= "</td><td></td><td>".$total_relato_experiencia."</td>";
            
            $total = $total_pequisa_original+$total_relato_experiencia;
            $listaTabela .= "</td><td></td><td>".$total."</td></tr>";
            $total_geral = $total_geral + $total;
        }   
        $cod_area_ant = $cod_area;
        #Movendo para o proximo registro
        $RS->MoveNext();
    }
}
?>
<html>
  <head>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
      google.charts.load('current', {'packages':['bar']});
      google.charts.setOnLoadCallback(drawChart);

      function drawChart() {
        var data = google.visualization.arrayToDataTable([
            ['Area Tematica','Pesquisa Original', 'Relato de Experiencia'],
            <?php echo substr($varGraficoTrabalhosAprovadosFT,0,-1); ?>
            ]);

        var options = {
          chart: {
            title: 'Trabalhos',
            subtitle: 'Agrupados por área temática e formato de texto',
          }
        };

        var chart = new google.charts.Bar(document.getElementById('by_formato_texto'));

        chart.draw(data);

      }
    </script>
  </head>
  <body>
    <div id="by_formato_texto" style="width: 800px; height: 500px;"></div>
    <?php
//    echo $listaAreas;
    ?>
    <hr>
    <table class='table table-hover'>
        <tr><td>Cod.</td><td>Área</td><td></td><td>Pesquisa<br>Original</td><td></td><td>Relato de<br>Experiência</td><td></td><td>Total</td></tr>
        <?php
        echo $listaTabela;
        ?>
        <tr><td colspan="7">Total Geral</td><td><?php echo $total_geral?></td></tr>
    </table>
  </body>
   <?php // print_r(substr($varGraficoTrabalhosAprovadosFT,0,-1)); ?>
</html>
<?php
#------------------------------------------------------
?></DIV>
</div></div></div>


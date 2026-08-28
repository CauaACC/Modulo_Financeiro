<?php
require_once(dirname(__FILE__) . "/../../include/config.php");
require_once(dirname(__FILE__) . "/../../view/admin/consulta-dados-congresso-graficos-cabecalho.php");
#require_once(dirname(__FILE__)."/../../view/admin/consulta-dados-congresso-busca.php");
echo "<div class=\"panel-body\">";
$Conexao_db = FWA_Conexao("OpcTec");
$codEvento = COD_EVENTO;

// Cards de totais no topo — padrao igual ao Painel de Avaliacao. Reusa DashboardAvaliacaoDAO.
$_totaisTrab = (new DashboardAvaliacaoDAO())->getTotaisTrabalhos($codEvento);
?>
<div class="row" style="margin-bottom:15px;">
    <div class="col-sm-3">
        <div class="panel panel-default"><div class="panel-body text-center">
            <h4 style="color:#337ab7;"><?php echo $_totaisTrab['total_trabalhos']; ?></h4>
            <small>Total de trabalhos</small>
        </div></div>
    </div>
    <div class="col-sm-3">
        <div class="panel panel-default"><div class="panel-body text-center">
            <h4 style="color:#5cb85c;"><?php echo $_totaisTrab['trabalhos_aprovados']; ?></h4>
            <small>Aprovados</small>
        </div></div>
    </div>
    <div class="col-sm-3">
        <div class="panel panel-default"><div class="panel-body text-center">
            <h4 style="color:#d9534f;"><?php echo $_totaisTrab['trabalhos_reprovados']; ?></h4>
            <small>Reprovados</small>
        </div></div>
    </div>
    <div class="col-sm-3">
        <div class="panel panel-default"><div class="panel-body text-center">
            <h4 style="color:#f0ad4e;"><?php echo $_totaisTrab['trabalhos_com_presenca']; ?></h4>
            <small>Com presença registrada</small>
        </div></div>
    </div>
</div>
<?php
#------------------------------------------------------
echo "<b>Detalhes das Avaliações de Trabalhos:</b><br>";
echo "Total - Situação na Avaliação<br>";
try {
    $objTrabalho = new Trabalho();
    $objTrabalho->carregarDadosTrabalhos(false, $debug);

    $objTrabalhoAvalicao = new HistoricoAvaliacao();
    $objTrabalhoAvalicao->carregarListaEstatisticaAvaliacaoByIdEvento($debug);
    foreach ($objTrabalhoAvalicao->getListaEstatisticaAvaliacaoByIdEvento() as $objLAT) {
        if ($objLAT->getAvaliacao() != 'Cancelada') {
            echo numberPad($objLAT->getTotal(), 3) . " &nbsp; - "
                . "<a href='index.php?content=view/admin/consulta-dados-congresso-listagem-trabalhos.php&avaliacao=" . strtolower($objLAT->getAvaliacao()) . "'>"
                . "" . $objLAT->getDesAvaliacao() . "</a><br>";
            $total_ja_distribuido = $total_ja_distribuido + $objLAT->getTotal();
        }
    }
    $TotalSemAvaliador = $objTrabalho->getTotalTrabalhos() - $total_ja_distribuido;
    echo numberPad($TotalSemAvaliador, 3) . " &nbsp; - <a href='index.php?content=view/admin/consulta-dados-congresso-listagem-trabalhos.php&avaliacao=submetido'>Sem Avaliador</a><br>";
    echo "<br>" . numberPad($objTrabalho->getTotalTrabalhos(), 3) . " &nbsp; - Total Trabalhos Enviados<br>";
} catch (Exception $exc) {
    $exc->getMessage() . "<br>";
}
echo "<hr><br>";
#------------------------------------------------------
## Consulta Trabalhos por Área Temática/Aprovado ##
#Definindo comando SQL

#Definindo cabeçalho da tabela dos resultados da consula
echo "
  <table border='0' width='100%' collspacing=2>
  <tr><td colspan='4'><b>Trabalhos por área temática:</b>    </td></tr>
  <tr>
    <td width='10px' style='text-align:rigth'>Total</td>";
if ($naoMostrar) {
    echo "<td width='20px' style='text-align:rigth'>Cód.Eixo</td>";
}
echo "<td width='90%'>Nome</td>
 </tr>
  ";

$var_sql = "
                 SELECT ifnull(Count(tt.cod_trabalho),0) as Total_Trabalhos_Enviados, toa.titulo
                 , toa.cod_area_tematica, toa.ordem
                 FROM tab_trabalhos tt, tab_inscritos ti, tab_opcoes_areas_tematicas toa
                 WHERE (tt.cod_evento  =" . $codEvento . ")and
                       (tt.cod_area_tematica=toa.cod_area_tematica)and
                       (tt.cod_inscrito=ti.cod_inscrito)and
                       (ti.status='ATIVADA')and
                       (tt.status='ATIVADA')
                 GROUP BY toa.titulo, toa.cod_area_tematica, toa.ordem
                 ORDER BY toa.ordem
               ";

#echo $var_sql;
#Fim da definição do comando SQL
#Executando a consulta no banco SQL
$RS = $Conexao_db->Execute($var_sql);

#Verificando se houve retorno de algum registro
$totalAreaTematica = 0;
if (!$RS->EOF) {
    #Efetuando loop para o visualização dos registros retornados pela consulta SQL
    while (!$RS->EOF) {

        #Adicionando nas variaveis, os valores dos campos retirados dos registros
        $Total_Trabalhos_Enviados = $RS->fields[0];
        $Titulo = $RS->fields[1];
        $cod_area_tematica = $RS->fields[2];
        $ordem = $RS->fields[3];
        #Definindo tabela com resultado
        $totalAreaTematica = $totalAreaTematica + $Total_Trabalhos_Enviados;
        echo "<tr valign='top'>";
        echo "<td width='10px' style='text-align:left'>" .  numberPad($Total_Trabalhos_Enviados, 3) . "</td>";
        if ($naoMostrar) {
            echo "<td width='20px' style='text-align:right'>" . $cod_area_tematica . " - </td>";
        }
        echo "<td width='90%'>  -  <a class='link' href='index.php?content=view/admin/consulta-dados-congresso-listagem-trabalhos.php&cod_area_tematica=" . $cod_area_tematica . "'>" . $Titulo . "</a> </td>";
        echo "</tr>";

        #Movendo para o proximo registro
        $RS->MoveNext();
    }
    echo "<tr><td colspan='4'>" . numberPad($totalAreaTematica, 3) . " - Total</td></tr>";
} else {
    echo "<tr><td colspan='4'>0 - Total de Trabalhos</td></tr>";
}
echo "</table>";
echo "<hr><br>";
#------------------------------------------------------
## Consulta Trabalhos por Área Temática/Aprovado ##
#Definindo comando SQL

#Definindo cabeçalho da tabela dos resultados da consula
echo "
  <table border='0' width='100%' collspacing=2>
  <tr><td colspan='4'><b>Trabalhos APROVADOS por área temática:</b>    </td></tr>
  <tr>
    <td width='10px' style='text-align:rigth'>Total</td>";
if ($naoMostrar) {
    echo "<td width='20px' style='text-align:rigth'>Cód.Eixo</td>";
}
echo "<td width='90%'>Nome</td>
 </tr>
  ";

$var_sql = "
                 SELECT ifnull(Count(tt.cod_trabalho),0) as Total_Trabalhos_Enviados, toa.titulo, toa.cod_area_tematica, toa.ordem
                 FROM tab_trabalhos tt, tab_inscritos ti, tab_opcoes_areas_tematicas toa, tab_trabalhos_avaliacao ta 
                 WHERE (tt.cod_evento  =" . $codEvento . ")and
                       (tt.cod_area_tematica=toa.cod_area_tematica)and
                       (tt.cod_inscrito=ti.cod_inscrito)and
                       (ti.status='ATIVADA')and
                       (tt.status='ATIVADA')and 
                       tt.cod_trabalho=ta.cod_trabalho
                       AND ta.avaliacao='APROVADO'  AND ta.status='ATIVADA'
                 GROUP BY toa.titulo, toa.cod_area_tematica, toa.ordem
                 ORDER BY toa.ordem
               ";

#echo $var_sql;
#Fim da definição do comando SQL
#Executando a consulta no banco SQL
$RS = $Conexao_db->Execute($var_sql);

#Verificando se houve retorno de algum registro
$totalAreaTematica = 0;
if (!$RS->EOF) {
    #Efetuando loop para o visualização dos registros retornados pela consulta SQL
    while (!$RS->EOF) {

        #Adicionando nas variaveis, os valores dos campos retirados dos registros
        $Total_Trabalhos_Enviados = $RS->fields[0];
        $Titulo = $RS->fields[1];
        $cod_area_tematica = $RS->fields[2];
        $ordem = $RS->fields[3];
        #Definindo tabela com resultado
        $totalAreaTematica = $totalAreaTematica + $Total_Trabalhos_Enviados;
        echo "<tr valign='top'>";
        echo "<td width='10px' style='text-align:left'>" .  numberPad($Total_Trabalhos_Enviados, 3) . "</td>";
        if ($naoMostrar) {
            echo "<td width='20px' style='text-align:right'>" . $cod_area_tematica . " - </td>";
        }
        echo "<td width='90%'>  -  <a class='link' href='index.php?content=view/admin/consulta-dados-congresso-listagem-trabalhos.php&cod_area_tematica=" . $cod_area_tematica . "'>" . $Titulo . "</a> </td>";
        echo "</tr>";

        #Movendo para o proximo registro
        $RS->MoveNext();
    }
    echo "<tr><td colspan='4'>" . numberPad($totalAreaTematica, 3) . " - Total</td></tr>";
} else {
    echo "<tr><td colspan='4'>0 - Total</td></tr>";
}
echo "</table>";
echo "<hr><br>";

#------------------------------------------------------
## Consulta Trabalhos por Área Temática/Reprovado ##
#    #Definindo cabeçalho da tabela dos resultados da consula
echo "
  <table border='0' width='100%' collspacing=2>
  <tr><td colspan='4'><b>Trabalhos REPROVADOS por área temática:</b>    </td></tr>
  <tr>
    <td width='10px' style='text-align:rigth'>Total</td>";
if ($naoMostrar) {
    echo "<td width='20px' style='text-align:rigth'>Cód.Eixo</td>";
}
echo "<td width='90%'>Nome</td>
 </tr>
  ";
#Definindo comando SQL
$var_sql = "
                 SELECT Count(tt.cod_trabalho) as Total_Trabalhos_Enviados, toa.titulo, toa.cod_area_tematica, toa.ordem
                 FROM tab_trabalhos tt, tab_inscritos ti, tab_opcoes_areas_tematicas toa, tab_trabalhos_avaliacao ta 
                 WHERE (tt.cod_evento  =" . $codEvento . ")and
                       (tt.cod_area_tematica=toa.cod_area_tematica)and
                       (tt.cod_inscrito=ti.cod_inscrito)and
                       (ti.status='ATIVADA')and
                       (tt.status='ATIVADA')and 
                       tt.cod_trabalho=ta.cod_trabalho
                       AND ta.avaliacao='REPROVADO'  AND ta.status='ATIVADA'
                 GROUP BY toa.titulo, toa.cod_area_tematica, toa.ordem
                 ORDER BY toa.ordem
               ";

#echo $var_sql;
#Fim da definição do comando SQL
#Executando a consulta no banco SQL
$RS = $Conexao_db->Execute($var_sql);

#Verificando se houve retorno de algum registro
$totalAreaTematica = 0;
if (!$RS->EOF) {
    #Efetuando loop para o visualização dos registros retornados pela consulta SQL
    while (!$RS->EOF) {

        #Adicionando nas variaveis, os valores dos campos retirados dos registros
        $Total_Trabalhos_Enviados = $RS->fields[0];
        $Titulo = $RS->fields[1];
        $cod_area_tematica = $RS->fields[2];
        $ordem = $RS->fields[3];
        #Definindo tabela com resultado
        $totalAreaTematica = $totalAreaTematica + $Total_Trabalhos_Enviados;
        echo "<tr valign='top'>";
        echo "<td width='10px' style='text-align:left'>" .  numberPad($Total_Trabalhos_Enviados, 3) . "</td>";
        if ($naoMostrar) {
            echo "<td width='20px' style='text-align:right'>" . $cod_area_tematica . " - </td>";
        }
        echo "<td width='90%'>  -  <a class='link' href='index.php?content=view/admin/consulta-dados-congresso-listagem-trabalhos.php&cod_area_tematica=" . $cod_area_tematica . "'>" . $Titulo . "</a> </td>";
        echo "</tr>";

        #Movendo para o proximo registro
        $RS->MoveNext();
    }
    echo "<tr><td colspan='4'>" . numberPad($totalAreaTematica, 3) . " - Total</td></tr>";
    //    echo "<tr><td colspan='4'><br><b><a href='index.php?content=view/admin/consulta-dados-congresso-listagem-trabalhos.php&avaliacao=reprovado'>Listagem de trabalhos Reprovados</a></b></td></tr>";
} else {
    echo "<tr><td colspan='4'>0 - Total</td></tr>";
}
echo "</table>";
#------------------------------------------------------
$var_sql = "SELECT * FROM vw_qtd_apresentacoes_enviadas_por_evento WHERE cod_evento   = " . $codEvento . "";
$RS = $Conexao_db->Execute($var_sql);
if (!$RS->EOF) {
    while (!$RS->EOF) {
        $qtdArquivosApresentacaoEnviados = $RS->fields[0];
        $RS->MoveNext();
    }
    echo "<br><b>Quantidade de arquivos enviados para apresentação: </b>";
    echo $qtdArquivosApresentacaoEnviados;
}
$var_sql = "SELECT COUNT(cod_trabalho) qtd_trabalhos_presentes FROM tab_trabalhos WHERE flg_presenca IS NOT NULL and cod_evento   = " . $codEvento . "";
$RS = $Conexao_db->Execute($var_sql);
if (!$RS->EOF) {
    while (!$RS->EOF) {
        $qtdTrabalhosPresentes = $RS->fields[0];
        $RS->MoveNext();
    }
    echo "<br><b>Quantidade de trabalhos com presença registrada: </b>";
    echo $qtdTrabalhosPresentes;
}
#------------------------------------------------------
echo "<hr>";
#------------------------------------------------------
## Consulta Trabalhos por Dia ##
$var_sql = "SELECT CAST(data_inclusao AS DATE) AS dia, COUNT(0) as total_trabalhos_dia 
FROM tab_trabalhos WHERE STATUS='ATIVADA' and cod_evento=" . $codEvento . "
GROUP BY CAST(data_inclusao AS DATE),cod_evento
ORDER BY data_inclusao ";
$RS = $Conexao_db->Execute($var_sql);
if (!$RS->EOF) {
    echo "<table><tr><td colspan='10'><b>Trabalhos enviados por dia:</b></td></tr>";
    $ii == 0;
    $varSomaTotal = 0;
    $varSomaTotalDia = 0;
    while (!$RS->EOF) {
        $total_trabalhos_dia = (int) $RS->fields[1];
        $dia = formatDateView($RS->fields[0]);
        #Definindo tabela com resultado
        $varSomaTotalDia = (int) $varSomaTotalDia + $total_trabalhos_dia;
        $varGraficoTrabalhosPorDia .= "['" . $dia . "'," . $total_trabalhos_dia . "],";
        #Movendo para o proximo registro
        $RS->MoveNext();
    }
    echo "</table><br>";
}
//echo $varGraficoTrabalhosPorDia;
?>
<html>

<head>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
        google.charts.load("current", {
            packages: ["corechart"]
        });
        google.charts.setOnLoadCallback(drawChart);

        function drawChart() {
            var data = google.visualization.arrayToDataTable([
                ['Task', 'Total Registros <?php echo $varSomaTotalDia ?>'],
                <?php echo  $varGraficoTrabalhosPorDia ?>
            ]);

            var options = {
                title: 'Inscritos por Dia',
                pieHole: 0.4,
            };

            var chart = new google.visualization.ColumnChart(document.getElementById('graficoTrabPorDia'));
            chart.draw(data, options);
        }
    </script>
</head>

<body>
    <div id="graficoTrabPorDia" style="width: 900px; height: 500px;"></div>
</body>

</html>
<?php
#------------------------------------------------------
#------------------------------------------------------
## Consulta Trabalhos por Área Temática/Aprovado ##
#Definindo comando SQL
$var_sql = "
                 SELECT Count(tt.cod_trabalho) as Total_Trabalhos_Enviados, toa.titulo, toa.cod_area_tematica, toa.ordem, tt.formato_texto
                 FROM tab_trabalhos tt, tab_inscritos ti, tab_opcoes_areas_tematicas toa, tab_trabalhos_avaliacao ta 
                 WHERE (tt.cod_evento  =" . $codEvento . ")and
                       (tt.cod_area_tematica=toa.cod_area_tematica)and
                       (tt.cod_inscrito=ti.cod_inscrito)and
                       (ti.status='ATIVADA')and
                       (tt.status='ATIVADA')and 
                       tt.cod_trabalho=ta.cod_trabalho
                       AND ta.avaliacao='APROVADO'  AND ta.status='ATIVADA'
                 GROUP BY toa.cod_area_tematica, tt.formato_texto
                 ORDER BY toa.ordem, toa.titulo, tt.formato_texto
               ";

#echo $var_sql;
#Fim da definição do comando SQL
#Executando a consulta no banco SQL
$RS = $Conexao_db->Execute($var_sql);

#Verificando se houve retorno de algum registro

#Definindo cabeçalho da tabela dos resultados da consula
echo '<hr><b>Trabalhos aprovados e agrupados por área temática e formato de texto:</b><br>';

#Efetuando loop para o visualização dos registros retornados pela consulta SQL
$varFormatoTexto = "['Pesquisa Original', 'Relato de Experiência'],";
if (!$RS->EOF) {
    $cod_area_ant = '';
    $total_geral = 0;
    $varGraficoTrabalhosAprovadosFT = 0;
    while (!$RS->EOF) {
        #Adicionando nas variaveis, os valores dos campos retirados dos registros
        $total_trabalhos = $RS->fields[0];
        $titulo_area     = $RS->fields[1];
        $cod_area        = $RS->fields[2];
        $ordem           = $RS->fields[3];
        $formato_texto   = $RS->fields[4];
        #Definindo tabela com resultado

        // if($formato_texto=='Pesquisa Original'){
        //     $total_pequisa_original = $total_trabalhos;
        // }
        // if($formato_texto=='Relato de Experiência'){
        //     $total_relato_experiencia = $total_trabalhos;
        // }
        // if($cod_area_ant!=$cod_area){
        //     $varGraficoTrabalhosAprovadosFT .= "['" . $cod_area . "'," . $total_pequisa_original;
        //     $listaAreas .= $cod_area . " - " . $titulo_area . "<br>";
        //     $listaTabela .= "<tr><td>".$cod_area . "</td><td>" . $titulo_area . "</td><td></td><td>".$total_pequisa_original;
        // }else{
        // $varGraficoTrabalhosAprovadosFT .=",".$total_relato_experiencia."],";
        // $listaTabela .= "</td><td></td><td>".$total_relato_experiencia."</td>";

        // $total = $total_pequisa_original+$total_relato_experiencia;
        // $listaTabela .= "</td><td></td><td>".$total."</td></tr>";
        // $total_geral = $total_geral + $total;
        // }
        $listaTabela .= "<tr><td>" . $titulo_area . "</td><td>" . $formato_texto . "</td><td>" . $total_trabalhos . "</td></tr>";
        $cod_area_ant = $cod_area;
        #Movendo para o proximo registro
        $RS->MoveNext();
    }
?>
        <table class='table' style="font-size: 11px;">
            <tr>
                <th>Área</th>
                <th>Formato do Texto</th>
                <th>Total</th>
            </tr>
            <?php
            echo $listaTabela;
            ?>
        </table>
<?php
} else {
    echo "<br>0 - Trabalhos aprovados por area e formato de texto<br><hr>";
}
#------------------------------------------------------
#------------------------------------------------------
### Consulta Trabalhos por Instituição ##
echo "<br><b>Detalhes de Trabalhos por Instituição:</b><br>";
echo "De acordo Instituição informada no trabalho<br>";
try {
    $objG = new Grafico();
    $objG->carregarListaTotalTrabalhosPorInstituicao($debug);
    $varSomaTotal = 0;
    foreach ($objG->getListaTotalTrabalhosPorInstituicao() as $objL) {
        $varGraficoTrabalhosPorInstituicao .= "['" . $objL->getInstituicaoTrabalho() . "'," . $objL->getTotal() . "],";
        $varSomaTotal = (int) $varSomaTotal + (int) $objL->getTotal();
    }
} catch (Exception $exc) {
    $exc->getMessage() . "<br>";
}
?>
<html>

<head>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
        google.charts.load("current", {
            packages: ["corechart"]
        });
        google.charts.setOnLoadCallback(drawChart);

        function drawChart() {
            var data = google.visualization.arrayToDataTable([
                ['Task', 'Total Registros <?php echo $varSomaTotal ?>'],
                <?php echo  $varGraficoTrabalhosPorInstituicao ?>
            ]);

            var options = {
                title: 'Trabalhos por Instituição',
                pieHole: 0.4,
            };

            var chart = new google.visualization.ColumnChart(document.getElementById('graficoTrabalhosPorInstituicao'));
            chart.draw(data, options);
        }
    </script>
</head>

<body>
    <div id="graficoTrabalhosPorInstituicao" style="width: 900px; height: 500px;"></div>
</body>

</html>
<?php
#------------------------------------------------------
## Consulta Trabalhos Enviados por Estado ##
#    #Definindo cabeçalho da tabela dos resultados da consula
echo "<table border=0><tr><td colspan='24'><b>Trabalhos enviados por estado:</b></td></tr>";
#Definindo comando SQL
$var_sql = "
                 SELECT Count(tt.cod_trabalho) as Total_Trabalhos_Enviados, ti.estado
                   FROM tab_trabalhos tt, tab_inscritos ti
                   WHERE (tt.cod_evento  =" . $codEvento . ")and
                        (tt.cod_inscrito=ti.cod_inscrito)and
                        (ti.status='ATIVADA')and
                        (tt.status='ATIVADA')
                   Group by ti.estado  order by Count(tt.cod_trabalho) desc, ti.estado
               ";

#echo $var_sql;
#Fim da definição do comando SQL
#Executando a consulta no banco SQL
$RS = $Conexao_db->Execute($var_sql);

#Verificando se houve retorno de algum registro
if (!$RS->EOF) {
    #Efetuando loop para o visualização dos registros retornados pela consulta SQL
    echo "<br><br><table>";
    $ii = 0;
    while (!$RS->EOF) {

        #Adicionando nas variaveis, os valores dos campos retirados dos registros
        $Total_Trabalhos_Enviados = $RS->fields[0];
        $Estado = $RS->fields[1];
        if ($Estado == '-1' or $Estado == '') {
            $Estado = "Não informou";
        }
        #Definindo tabela com resultado
        if ($ii == 10)
            $ii = 0;

        if ($ii == 0)
            echo "<tr valign='top'>";

        echo "
              <td>
              | <a class='link' href='index.php?content=view/admin/consulta-dados-congresso-listagem-trabalhos.php&estado=" . $Estado . "'>
              " . $Total_Trabalhos_Enviados . "
              ->
              " . $Estado . "</a>
              </td>
             ";

        $varGraficoTrabalhosEnviados .= "['" . $Estado . "'," . $Total_Trabalhos_Enviados . "],";
        $ii = $ii + 1;
        if ($ii == 10)
            echo "</tr>";

        #Movendo para o proximo registro
        $RS->MoveNext();
    }
    echo "</table><br>";
}
?>
<html>

<head>
    <script type="text/javascript">
        google.charts.load("current", {
            packages: ["corechart"]
        });
        google.charts.setOnLoadCallback(drawChart);

        function drawChart() {
            var data = google.visualization.arrayToDataTable([
                ['Task', 'Trabalhos por Estado'],
                <?php echo  $varGraficoTrabalhosEnviados ?>
            ]);

            var options = {
                title: 'Trabalhos enviados por Estado',
                pieHole: 0.4,
            };

            var chart = new google.visualization.ColumnChart(document.getElementById('donutchart'));
            chart.draw(data, options);
        }
    </script>
</head>

<body>
    <div id="donutchart" style="width: 900px; height: 500px;"></div>
</body>

</html>
<?php
#------------------------------------------------------
?></DIV>
</div>
</div>
</div>
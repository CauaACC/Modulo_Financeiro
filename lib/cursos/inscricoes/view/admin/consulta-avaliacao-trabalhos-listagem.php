<?php

require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/consulta-avaliacao-trabalhos-cabecalho.php");
require_once(dirname(__FILE__)."/../../view/admin/consulta-avaliacao-trabalhos-busca.php");
#----------------------------------------------------------------------
#Conectar no Banco
$debug = false;
if ($_SERVER['REMOTE_ADDR'] == '189.90.70.566') {
    $debug = true;
}
$fws_['cod_area_tematica'] = $fws_['cod_area_tematica'] == '' ? $fwsGet_['cod_area_tematica'] : $fws_['cod_area_tematica'];
$COD_CONVIDADO = $fws_['cod_convidado'] == '' ? $_GET['cod_convidado'] : $fws_['cod_convidado'];

$Conexao_db = FWA_Conexao("OpcTec");
#----------------------------------------------------------------------
#Mensagens de ERRO
$MSG = $_SESSION['MSG'];
If ($MSG) {
//  echo "<hr size='1' width='100%'>";
    echo FWA_Mostrar_Mensagem($MSG);
}
#----------------------------------------------------------------------
#com avaliador por área temática
$whereComAvaliadorPorAreaTematica = "";
if(COD_EVENTO==70){
    $whereComAvaliadorPorAreaTematica = " and lia.cod_area_tematica=toat.cod_area_tematica ";
}

#Lista Trabalhos a serem avaliados da Area Tematica a qual o convidado esta associado
## Consulta Trabalhos por Área Temática ##
$whereAreaTematica = "";
$whereAreaTematicaTba = "";
if ($fws_['cod_area_tematica']) {
    $whereAreaTematica .= " and toat.cod_area_tematica = " . $fws_['cod_area_tematica'];
    $whereAreaTematica .= " and lia.cod_area_tematica = " . $fws_['cod_area_tematica'];
    $whereAreaTematicaTba = " and tba.cod_area_tematica = " . $fws_['cod_area_tematica'];
}

#Definindo comando SQL
$var_sql = "
SELECT 
    tc.cod_convidado,
    tc.nome_convidado,
    su.login,
    ( 
        SELECT COUNT(tba.cod_avaliador) 
        FROM tab_trabalhos_avaliacao tba, tab_inscritos ti
        WHERE tba.cod_inscrito = ti.cod_inscrito 
          AND ti.status = 'ATIVADA' 
          ".$whereAreaTematicaTba."
          and tba.cod_area_tematica=toat.cod_area_tematica
          AND (tba.avaliacao = 'APROVADO' OR tba.avaliacao = 'REPROVADO') 
          AND tba.cod_avaliador = su.cod_usuario 
        GROUP BY tba.cod_avaliador 
    ) AS Avaliados_pelo_Avaliador,
    ( 
        SELECT COUNT(tba.cod_avaliador) 
        FROM tab_trabalhos_avaliacao tba, tab_inscritos ti 
        WHERE tba.cod_inscrito = ti.cod_inscrito 
          AND ti.status = 'ATIVADA' 
          ".$whereAreaTematicaTba."
          and tba.cod_area_tematica=toat.cod_area_tematica
          AND tba.avaliacao = 'APROVADO' 
          AND tba.cod_avaliador = su.cod_usuario 
        GROUP BY tba.cod_avaliador 
    ) AS Aprovados_pelo_Avaliador,
    ( 
        SELECT COUNT(tba.cod_avaliador) 
        FROM tab_trabalhos_avaliacao tba, tab_inscritos ti 
        WHERE tba.cod_inscrito = ti.cod_inscrito 
          AND ti.status = 'ATIVADA' 
          ".$whereAreaTematicaTba."
          and tba.cod_area_tematica=toat.cod_area_tematica
          AND tba.avaliacao = 'REPROVADO' 
          AND tba.cod_avaliador = su.cod_usuario 
        GROUP BY tba.cod_avaliador 
    ) AS Reprovados_pelo_Avaliador,
	    toat.cod_area_tematica,
    toat.titulo,
    toat.n_max_trab_por_avaliador
    ,
    ( 
        SELECT COUNT(tb.cod_trabalho) 
        FROM tab_trabalhos tb
        INNER JOIN tab_inscritos ti ON tb.cod_inscrito = ti.cod_inscrito 
        WHERE 
	      ti.status = 'ATIVADA' 
          AND tb.status = 'ATIVADA' 
          and tb.cod_area_tematica=toat.cod_area_tematica
          AND ti.cod_evento=toat.cod_evento
        GROUP BY tb.cod_evento
    ) AS total_inscritos,
    ( 
        SELECT COUNT(tba.cod_trabalho) 
        FROM tab_trabalhos_avaliacao tba
        INNER JOIN tab_inscritos ti ON tba.cod_inscrito = ti.cod_inscrito 
        WHERE 
              ti.status = 'ATIVADA' 
              ".$whereAreaTematicaTba."
              and tba.cod_area_tematica=toat.cod_area_tematica
          AND (tba.avaliacao = 'APROVADO' OR tba.avaliacao = 'REPROVADO') 
          AND tba.cod_evento=toat.cod_evento
        GROUP BY tba.cod_evento
    ) AS total_avaliados,
    ( 
        SELECT COUNT(tba.cod_trabalho) 
        FROM tab_trabalhos_avaliacao tba, tab_inscritos ti 
        WHERE tba.cod_inscrito = ti.cod_inscrito 
          AND ti.status = 'ATIVADA' 
          ".$whereAreaTematicaTba."
          and tba.cod_area_tematica=toat.cod_area_tematica
          AND tba.avaliacao = 'APROVADO' 
          AND tba.cod_evento=toat.cod_evento
        GROUP BY tba.cod_evento
    ) AS total_aprovados,
    ( 
        SELECT COUNT(tba.cod_trabalho) 
        FROM tab_trabalhos_avaliacao tba, tab_inscritos ti 
        WHERE tba.cod_inscrito = ti.cod_inscrito 
          AND ti.status = 'ATIVADA' 
          ".$whereAreaTematicaTba."
          and tba.cod_area_tematica=toat.cod_area_tematica
          AND tba.avaliacao = 'REPROVADO' 
          AND tba.cod_evento=toat.cod_evento
        GROUP BY tba.cod_evento
    ) AS total_reprovados,
    ( 
        SELECT COUNT(tb.cod_trabalho) 
        FROM tab_trabalhos tb
        INNER JOIN tab_inscritos ti ON tb.cod_inscrito = ti.cod_inscrito 
        WHERE 
          tb.status = 'DESATIVADA' 
          and tb.cod_area_tematica=toat.cod_area_tematica
          AND ti.cod_evento=toat.cod_evento
        GROUP BY tb.cod_evento
    ) AS total_desativados
FROM tab_opcoes_areas_tematicas toat 
INNER JOIN sis_ligacao_usuarios_eventos lue ON toat.cod_evento = lue.cod_evento 
INNER JOIN sis_usuarios su ON su.cod_usuario = lue.cod_usuario 
INNER JOIN tab_convidados tc ON su.cod_usuario = tc.cod_usuario 
left JOIN tab_lig_convidados_areas_tematicas lia on lia.cod_usuario=su.cod_usuario
WHERE toat.cod_evento=" . COD_EVENTO . "
    " .$whereComAvaliadorPorAreaTematica. "
    " . $whereAreaTematica . "
ORDER BY toat.titulo, tc.nome_convidado 
";

$RS = FWA_Executa_SQL($debug, $Conexao_db, $var_sql); //Consulta login existe   
#Verificando se houve retorno de algum registro
If (!$RS->EOF) {
    echo "<table border='0' width='100%' colspan='0' cellspacing='0'>";
    $faltaAvaliar = 0;
    While (!$RS->EOF) {
        $cod_convidado = $RS->fields[0];
        $nome_convidado = $RS->fields[1];
        $e_mail = $RS->fields[2];
        $total_avaliados_pelo_avaliador = $RS->fields[3];
        $total_aprovados_pelo_avaliador = $RS->fields[4];
        $total_reprovados_pelo_avaliador = $RS->fields[5];
        $cod_area_tematica = $RS->fields[6];
        $titulo_area_tematica = $RS->fields[7];
        $n_max_trab_por_avaliador = $RS->fields[8];
        $total_inscritos = $RS->fields[9];
        $total_avaliados = $RS->fields[10];
        $total_aprovados = $RS->fields[11];
        $total_reprovados = $RS->fields[12];
        $total_desativados = $RS->fields[13];

        If ($total_avaliados_pelo_avaliador == "") {
            $total_avaliados_pelo_avaliador = 0;
        }
        If ($total_aprovados_pelo_avaliador == "") {
            $total_aprovados_pelo_avaliador = 0;
        }
        If ($total_reprovados_pelo_avaliador == "") {
            $total_reprovados_pelo_avaliador = 0;
        }

        If ($total_inscritos == "") {
            $total_inscritos = 0;
        }
        If ($total_avaliados == "") {
            $total_avaliados = 0;
        }
        If ($total_aprovados == "") {
            $total_aprovados = 0;
        }
        If ($total_reprovados == "") {
            $total_reprovados = 0;
        }
        If ($total_desativados == "") {
            $total_desativados = 0;
        }

        If (@$titulo_area_tematica_ant != $titulo_area_tematica) {

            $total_geral_inscritos = $total_geral_inscritos + $total_inscritos;
            $total_geral_avaliados = $total_geral_avaliados + $total_avaliados;
            $total_geral_aprovados = $total_geral_aprovados + $total_aprovados;
            $total_geral_reprovados = $total_geral_reprovados + $total_reprovados;
            $total_geral_desativados = $total_geral_desativados + $total_desativados;

            $faltaAvaliar = $total_inscritos - $total_avaliados;
            $txtFaltaAvaliar = "";
            if($faltaAvaliar>0){
                $txtFaltaAvaliar = "<font color=red>Falta avaliar: ".$faltaAvaliar."</font>";
            }else{
                $txtFaltaAvaliar = "<font color=green>Falta avaliar: 0 </font>";
            }
            echo "
          <tr>
            <td colspan='3' class='subtitulo'>
              <br><br><hr size='1' width='100%'>
              <h4><a name='" . $cod_area_tematica . "'>
                Área Temática:
                <a class='linkg' href='index.php?content=view/admin/area-tematica-cadastro-alterar.php&cod_area_tematica=" . $cod_area_tematica . "'
                    title='Editar Área Temática'><img src='resources/image/fig-alterar.gif' alt='Editar' border='0'></a>
                -   
                <a class='linkg' href='index.php?content=view/admin/consulta-avaliacao-trabalhos-listagem.php&cod_area_tematica=" . $cod_area_tematica . "'
                    title='Editar Área Temática'>" . $titulo_area_tematica . "</a>
              </h4>
              
              <a class='linkg' href='index.php?content=view/admin/re-avaliacao-trabalhos-listagem.php&cod_area_tematica=" . $cod_area_tematica . "'
              title='Avaliar Trabalhos como Administrador'><input type='button' value='Avaliar como Administrador' class='btn btn-xs btn-primary'></a>
              <br>
              
              | Total Trabahos -> Inscritos: " . $total_inscritos . "
              | A Serem Avaliados por cada Avaliador: " . $n_max_trab_por_avaliador . "
              | 

              <br>
              | Total Trabahos ->
                " . $txtFaltaAvaliar . "
              | Avaliados: " . $total_avaliados . "
              | Aprovados: " . $total_aprovados . "
              | Reprovados:" . $total_reprovados . "
              | Desativados:" . $total_desativados . "
              |
              
            </td>
          </tr>
          <tr><td colspan='3'>
                <br>Avaliadores: 
                <a class='linkg' href='index.php?content=view/admin/ligacao-convidados-areas-tematicas-listagem.php&cod_area_tematica=" . $cod_area_tematica . "&COD_CONVIDADO=" . $_GET['COD_CONVIDADO'] . "'
                title='Adicionar Avaliador'><input type='button' value='Adicionar Avaliador' class='btn btn-xs btn-primary'></a>
                <br>&nbsp;
          </td></tr>
          ";
        }

//        if ($nome_convidado != '') {
            echo "
            <tr valign='top'>
            <td width='5%'>";

            echo "<a href='index.php?content=view/admin/convidados-cadastro-ok-listagem-areas-tematicas.php&COD_CONVIDADO=" . $cod_convidado . "'>
                    <img src='resources/image/fig-alterar.gif' alt='Editar' title='Editar Convidado' border='0'></a>";
            echo "<a href='view/admin/ligacao-convidados-areas-tematicas-excluir-grava.php?acao=Excluir&cod_convidado=" . $cod_convidado . "&cod_evento=" . COD_EVENTO . "&cod_area_tematica=" . $cod_area_tematica . "'>
                    <img src='resources/image/fig-excluir.gif' alt='Excluir' title='Excluir Avaliador' border='0'></a>";
            ?>
            </td>
            <td colspan='2' width='95%' >
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-4 info-col">
                        <!-- Verificação do código convidado -->
                        <?php if ($cod_convidado == $_GET['COD_CONVIDADO']): ?>
                            <span class="info-title"><?php echo $nome_convidado; ?>:</span>
                        <?php else: ?>
                            <span class="info-title"><?php echo $nome_convidado; ?>:</span>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-2 info-col">
                        <span class="info-title">Total Trabalhos:</span>
                    </div>
                    <div class="col-md-2 info-col">
                        <span class="info-title">Avaliados:</span> <?php echo $total_avaliados_pelo_avaliador; ?>
                    </div>
                    <div class="col-md-2 info-col">
                        <span class="info-title">Aprovados:</span> <?php echo $total_aprovados_pelo_avaliador; ?>
                    </div>
                    <div class="col-md-2 info-col">
                        <span class="info-title">Reprovados:</span> <?php echo $total_reprovados_pelo_avaliador; ?>
                    </div>
                </div>
            </div>
            </td></tr>
            <?php
//        }
        $ii++;
        $titulo_area_tematica_ant = $titulo_area_tematica;
        #Movendo para o proximo registro
        $RS->MoveNext();
        /*imprimir quando o proximo for diferente*/
        if ($titulo_area_tematica_ant != $RS->fields[7]) {
            if ($titulo_area_tematica_ant != '') {
                echo "<td colspan='2' width='95%' >Total de Avaliadores Cadastrados: " . $ii . "</td></tr>";
                $ii = "";
            }
        }

    }
    echo "</table><br>";
}

echo "
<hr size='1' width='100%'>
<span style class='textog'>Totais Geral</span>
              <br>
              | Total Geral de Trabahos ->
                Inscritos: " . $total_geral_inscritos . "
              |
              <br>
              | Total Geral de Trabahos ->
                Avaliados: " . $total_geral_avaliados . "
              | Aprovados: " . $total_geral_aprovados . "
              | Reprovados:" . $total_geral_reprovados . "
              | Desativados:" . $total_geral_desativados . "
              |

<hr size='1' width='100%'>

<a class='link' href='javascript:history.go(-1)'><b>voltar<b></a>
</body>
</html>
";
?>
</div></div></div></div></div>
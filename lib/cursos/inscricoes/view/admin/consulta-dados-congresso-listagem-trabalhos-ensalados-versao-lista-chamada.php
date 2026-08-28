<?php
require_once(dirname(__FILE__)."/../../include/config.php");
//require_once(dirname(__FILE__)."/../../view/admin/consulta-dados-congresso-cabecalho.php");
#require_once(dirname(__FILE__)."/../../view/admin/comunicado-avaliacao-trabalhos-busca.php");
#----------------------------------------------------------------------
#Conectar no Banco
$FWS_Debug = false;
$Conexao_db = FWA_Conexao("OpcTec");
#----------------------------------------------------------------------
#Mensagens de ERRO
/*mesclando com o modelo*/
ob_start();
header('Content-Type: text/html; charset=UTF-8');
echo '<div style="PAGE-BREAK-AFTER: always;margin: 10px;">';




$MSG   = $_SESSION['MSG'];

$MSG_Imprimir = $MSG;
$loc   = "/--/";
$subst = "<br>";
$MSG_Imprimir = preg_replace($loc,$subst,$MSG_Imprimir);
$loc   = "/-/";
$subst = " ";
$MSG_Imprimir = preg_replace($loc,$subst,$MSG_Imprimir);

if($MSG_Imprimir){
    echo $MSG_Imprimir."<hr size='1' width='100%'>";
}
$whereSala = "";
if($fwsGet_['nome_sala']){
    $whereSala = " and es.nome_sala = '".$fwsGet_['nome_sala']."'";
}
#----------------------------------------------------------------------
#Lista Trabalhos a serem avaliados da Area Tematica a qual o convidado esta associado

    ## Consulta Trabalhos por Área Temática ##

    #Definindo comando SQL
    $var_sql = "
    SELECT t.cod_evento, es.cod_sessao, i.cod_inscrito, t.cod_trabalho, t.cod_area_tematica
    , es.nome_bloco, es.nome_sala,es.data, es.hora_ini, es.turno
    , UPPER(i.nome_inscrito) AS nome_inscrito, UPPER(t.titulo_trabalho) AS titulo_trabalho
    , UPPER(t.autor_apresentador_nome) AS  autor_apresentador_nome
    , UPPER(t.nome_co_autor_1) AS nome_co_autor_1
    , UPPER(t.nome_co_autor_2) AS nome_co_autor_2
    , UPPER(t.nome_co_autor_3) AS nome_co_autor_3
    , UPPER(t.nome_co_autor_4) AS nome_co_autor_4
    , UPPER(t.nome_co_autor_5) AS nome_co_autor_5
    , UPPER(t.nome_co_autor_6) AS nome_co_autor_6
    , toa.titulo
    FROM vw_ensalamento_sessao es
    LEFT JOIN tab_ensalamento_trabalho_sessao ts ON es.cod_sessao=ts.cod_sessao
    LEFT JOIN tab_trabalhos t ON t.cod_trabalho=ts.cod_trabalho
    LEFT JOIN tab_opcoes_areas_tematicas toa ON t.cod_area_tematica=toa.cod_area_tematica
    LEFT JOIN tab_inscritos i ON i.cod_inscrito=t.cod_inscrito
    WHERE es.cod_evento=".COD_EVENTO."
        ".$whereSala."
    ORDER BY es.cod_evento, es.cod_sessao";

    #echo $var_sql."<br>";

    $RS=FWA_Executa_SQL($FWS_Debug,$Conexao_db,$var_sql); //Consulta login existe
        ?>
        <style>
            .h3, h3 {
                margin-left: 0px;
            }
            .h4, h4,.h3, h3  {
                margin: 0px;
            }
            .tableTit{
                border-spacing: 1px;
                font-size: 11px;
                font-family: arial;
            }
            .tdCel{
                padding: 5px;
            }

        </style>
        <?php
    #Verificando se houve retorno de algum registro
    if (!$RS->EOF) {
        echo "<table border='0' width='100%' class='tableTit'>";

        while (!$RS->EOF) {

        $cod_inscrito    = $RS->fields[cod_inscrito];
        $cod_sessao      = $RS->fields[cod_sessao];
        $nome_inscrito   = textoMaiusculoMinusculo($RS->fields[nome_inscrito]);
        $enderecamento_sala = "Sala: ".$RS->fields[nome_sala]." ".$RS->fields[nome_bloco]." ".formatDateView($RS->fields[data])." ".$RS->fields[hora_ini];
        $sala            = $RS->fields[nome_sala];
        $bloco           = $RS->fields[nome_bloco];
        $turno           = $RS->fields[turno];
        $data            = formatDateView($RS->fields[data]);
        $hora            = $RS->fields[hora_ini];
        $hora_ini        = substr($hora,0, 5);
        $hora_ini        = str_replace(":","h",$hora_ini);
        $email_enviado   = "SIM";
        $cod_trabalho    = $RS->fields[cod_trabalho];
        $titulo_trabalho = $RS->fields[titulo_trabalho];
        $autor_apresentador_nome  = textoMaiusculoMinusculo($RS->fields[autor_apresentador_nome]);
        $nome_co_autor_1 = textoMaiusculoMinusculo($RS->fields[nome_co_autor_1]);
        $nome_co_autor_2 = textoMaiusculoMinusculo($RS->fields[nome_co_autor_2]);
        $nome_co_autor_3 = textoMaiusculoMinusculo($RS->fields[nome_co_autor_3]);
        $nome_co_autor_4 = textoMaiusculoMinusculo($RS->fields[nome_co_autor_4]);
        $nome_co_autor_5 = textoMaiusculoMinusculo($RS->fields[nome_co_autor_5]);
        $titulo_area_tematica = $RS->fields[titulo];

        if(@$imprimiu_uma_vez!=1){
            if(!$_GET['chamada']){
            echo "
            <tr><td colspan='3'>
            <B>Segue abaixo lista do Programa e Ensalamento:</B>
            <hr size='1' width='100%'>
            <a href='#possiveisProblemas>
            clique aqui para consultar possíveis problemas</a>
            </td></tr>
            ";
            }
        }

        $imprimiu_uma_vez=1;
        /*
        if(($avaliacao=="APROVADO")
        or($avaliacao=="APROVADO-REPROVADO")
        or($avaliacao=="APROVADO-CA-REPROVADO")
        or($avaliacao=="APROVADO-REPROVADO-REPROVADO")
        or($avaliacao=="REALOCADO-TEMA-15-APROVADO")
        or($avaliacao=="REALOCADO-TEMA-15-APROVADO-REPROVADO")
        ){
        */

        if ($bgcolor == 'e6e6e6') { $bgcolor = "FFFFFF"; } else { $bgcolor = "e6e6e6"; }
        
                
                if($turno!=$turno_ant){
                    echo "<tr valign='top' bgcolor='#".$bgcolor."'><td colspan='9' class='tdCel'><hr></td></tr>";
                }
                if($bloco!=$bloco_ant){
                    echo "
                    <tr valign='top' bgcolor='#".$bgcolor."'>
                    <td colspan='9' class='tdCel'>
                        <h3>Bloco: ".$bloco."</h3>
                    </td></tr>";
                }                
                if($turno!=$turno_ant){
                    echo "
                    <tr valign='top' bgcolor='#".$bgcolor."'>
                    <td colspan='9' class='tdCel'>
                        <h3>Sala: ".$sala." - ".$data." - Turno: ".$turno."</h3>
                    </td></tr>";
                }
                
                if($titulo_area_tematica!=$titulo_area_tematica_ant){                
                echo "
                <tr valign='top' bgcolor='#".$bgcolor."'>
                <td colspan='9' class='tdCel'>
                    <a name='A".$RS->fields[cod_area_tematica]."'></a>
                    <h3>Eixo: ".$titulo_area_tematica."</h3>
                </td></tr>
                
                <tr valign='top' bgcolor='#" . $bgcolor . "'>
                <td colspan='9'><hr size='1' width='100%'></td></tr>
                <tr valign='top' bgcolor='#" . $bgcolor . "' style='padding:8px;font-weight:700'>
                    <td class='tdCel'>Seq.</td>
                    <td class='tdCel'>Horário</td>
                    <td class='tdCel'>Código<br>Trabalho</td>
                    <td class='tdCel' width='400px'>Titulo do Trabalho</td>
                    <td class='tdCel' width='300px'>Autores</td>
                    <td class='tdCel' width='300px'>Assinatura do Apresentador</td>
                </tr>";
                $i=0;
            }
            $bloco_ant = $bloco;
            $sala_ant = $sala;
            $turno_ant = $turno;
            $titulo_area_tematica_ant = $titulo_area_tematica;

            
            @$i = $i+1;
            if($cod_trabalho){
            $cod_trabalho = FWA_Zero_Esquerda($cod_trabalho,4);
            @$totalTrabalhos = $totalTrabalhos+1;
            }
            /*marcar trabalhos*/
            $fontcolor = "000000";
            if($cod_inscrito == $cod_inscrito_ant and $cod_inscrito!=''){
                $totalTrabSeqMesmoInscrito++;
                $linkTrabSeqMesmoInscrito .= ": <a href='#S".($cod_sessao-3)."'>".$cod_trabalho."</a>";
                $fontcolor = "FF0000";
            }
            $cod_inscrito_ant = $cod_inscrito;
            echo "<tr valign='top' bgcolor='#".$bgcolor."' style='padding:8px;' id='".$cod_sessao."'>";
            echo "<td class='tdCel'><font color='#".$fontcolor."'>".$cod_sessao."</font>";
            echo "<a name='S".$cod_sessao."'></a>";
            echo "</td>"; 
            echo "<td class='tdCel'><font color='#".$fontcolor."'>".$hora_ini."</font></td>";
            if($cod_trabalho){
                echo "<td class='tdCel'>";
                echo "<font color='#".$fontcolor."'> <a name='T".$cod_trabalho."'></a>".$cod_trabalho."</font>&nbsp;&nbsp;&nbsp;";
                if(!$_GET['chamada']){
                    echo '<a onclick="removerTrabalhoSessao(\''.$cod_trabalho.'\',\''.$cod_sessao.'\');" href="javascript:void(0);"/><img src="'.WB_URL_IMG.'/fig-excluir.gif" title="Remover trabalho da sessão"></a> ';
                }
                if($cod_trabalho_ant){
                    if(!$_GET['chamada']){
                    echo '<a onclick="moverSessaoAcima(\''.$cod_trabalho.'\',\''.$cod_sessao.'\',\''.$cod_trabalho_ant.'\',\''.$cod_sessao_ant.'\');" href="javascript:void(0);"/><img src="'.WB_URL_IMG.'/arrow_top1.gif" title="Mover trabalho para sessão acima."></a> ';
                    }
                }
                
                echo "</td>";
            }else{
                echo "<td class='tdCel'>";
                if(!$_GET['chamada']){
                    echo '<a onclick="adicionarTrabalhoSessao(\''.$cod_sessao.'\');" href="javascript:void(0);"'
                    . ' />Add.Trab.Sessão</a><br>';
                }
                echo "</td>";
            }
            echo "<td class='tdCel'><font color='#".$fontcolor."'>".$titulo_trabalho."</font></td>";
            echo "<td class='tdCel'><font color='#".$fontcolor."'>";

            if ($nome_inscrito != $autor_apresentador_nome and $autor_apresentador_nome!="") {
                echo "<b>" . $nome_inscrito . " | " . $autor_apresentador_nome . "</b>";
            }else {
                if($nome_inscrito){
                echo "<b>" . $nome_inscrito . " | </b>";
                }
            }

            if (($nome_co_autor_1 != "")and( $nome_co_autor_1 != " ")) {
                echo "" . $nome_co_autor_1;
            }
            if (($nome_co_autor_2 != "")and( $nome_co_autor_2 != " ")) {
                echo "; " . $nome_co_autor_2;
            }
            if (($nome_co_autor_3 != "")and( $nome_co_autor_3 != " ")) {
                echo "; " . $nome_co_autor_3;
            }
            if (($nome_co_autor_4 != "")and( $nome_co_autor_4 != " ")) {
                echo "; " . $nome_co_autor_4;
            }
            if (($nome_co_autor_5 != "")and( $nome_co_autor_5 != " ")) {
                echo "; " . $nome_co_autor_5;
            }
            if (($nome_co_autor_6 != "")and( $nome_co_autor_6 != " ")) {
                echo "; " . $nome_co_autor_6;
            }

            echo "</font></td>";
            echo "<td style='border-bottom:1pt solid black;'></td></tr>";


            #}#if(($avaliacao=="APROVADO")or($avaliacao=="APROVADO-REPROVADO")or($avaliacao=="APROVADO-CA-REPROVADO")or($avaliacao=="APROVADO-REPROVADO-REPROVADO")or($avaliacao=="REALOCADO-TEMA-15-APROVADO")or($avaliacao=="REALOCADO-TEMA-15-APROVADO-REPROVADO")){
            #Movendo para o proximo registro
            $cod_inscrito_ant = $cod_inscrito;
            $cod_trabalho_ant = $cod_trabalho;
            $cod_sessao_ant   = $cod_sessao;
            $RS->MoveNext();
        }
        echo "</table><hr size='1' width='100%'>";
    }

echo " <hr size='1' width='100%'>";

$iii = $i-$ii;

echo "
<hr size='1' width='100%'>
<h4>Detalhes:</h4>
Total trab. ensalados: ".$totalTrabalhos."<br>
<a name='possiveisProblemas'></a>
";
if($totalTrabSeqMesmoInscrito){
    echo "Total trab. sequentes do mesmo inscrito: ".$totalTrabSeqMesmoInscrito."<br>";
}
if($linkTrabSeqMesmoInscrito){
    echo "Trabalhos Verificar ".$linkTrabSeqMesmoInscrito."<br>";
}
echo "<hr size='1' width='100%'>";

$RS=FWA_Executa_SQL($FWS_Debug,$Conexao_db,"SELECT * FROM vw_ensalamento_aprovados_nao_ensalados WHERE cod_evento=" . COD_EVENTO);
if (!$RS->EOF) {
    echo "Trabalhos não ensalados:<br>";
    while (!$RS->EOF) {
        echo "Cod.Inscrito: ".$RS->fields[cod_inscrito]. " - ".$RS->fields[nome_inscrito]."<br>";
        echo "Cod.Trabalho: ".$RS->fields[cod_trabalho]. " - ".$RS->fields[titulo_trabalho]. "<br>";
        echo "Temática: <a href='#A{$RS->fields[cod_area_tematica]}'>{$RS->fields[titulo_area_tematica]}</a><br>";
        echo '<a onclick="adicionarSessaoTrabalho(\''.$RS->fields[cod_trabalho].'\');" href="javascript:void(0);"'
        . ' />Add.Trab.Sessão<br>';        
        $RS->MoveNext();
    }
}
echo "<hr size='1' width='100%'>";
$sqlMesmoHorario = "SELECT es.* FROM vw_ensalamento_no_mesmo_horario emh 
INNER JOIN vw_ensalamento es ON es.cod_inscrito = emh.cod_inscrito 
WHERE emh.cod_evento=" . COD_EVENTO;
$RS=FWA_Executa_SQL($FWS_Debug,$Conexao_db,$sqlMesmoHorario);
if (!$RS->EOF) {
    echo "Inscritos ensalados no mesmo horário em salas diferentes:<br>";
    while (!$RS->EOF) {
        echo "Data/Hora inicio:".formatDateView($RS->fields[cod_inscrito])."/".$RS->fields[hora_ini]." - Cod.Inscrito: ".$RS->fields[cod_inscrito]. " - ".$RS->fields[nome_inscrito]."<br>";
        echo "Cod.Trabalho: <a href='#T{$RS->fields[cod_trabalho]}'>{$RS->fields[titulo_trabalho]}<br>";
        echo "Temática: <a href='#A{$RS->fields[cod_area_tematica]}'>{$RS->fields[titulo_area_tematica]}</a><br>";     
        $RS->MoveNext();
    }
}
echo "</div>";
$content = ob_get_clean();
/*gerando pdf*/
require_once(dirname(__FILE__) . '/../../lib/html2pdf/html2pdf.class.php');
$html2pdf = new HTML2PDF('L', 'A4', 'pt');
$html2pdf->setTestTdInOnePage(false);
//$html2pdf->WriteHTML($content, isset($_GET['vuehtml']));
$html2pdf->WriteHTML($content, 1);

if (!file_exists(WB_PATH_LOG_LISTA_CHAMADA)) {
    mkdir(WB_PATH_LOG_LISTA_CHAMADA, 0755);
}
$fws_['nomeArquivoListaChamada'] = "lista-chamada-sala-".$nome_sala.'.pdf';
$html2pdf->Output(WB_PATH_LOG_LISTA_CHAMADA. '/' . $fws_['nomeArquivoListaChamada'], 'F'); // grava o arquivo em disco na primeira vez que ? visualizado
$html2pdf->Output($fws_['nomeArquivoListaChamada']); // grava o arquivo em disco na primeira vez que ? visualizado
exit();
?>

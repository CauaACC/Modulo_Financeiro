<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/consulta-dados-congresso-cabecalho.php");
#require_once(dirname(__FILE__)."/../../view/admin/comunicado-avaliacao-trabalhos-busca.php");
#----------------------------------------------------------------------
#Conectar no Banco
$FWS_Debug = false;
$Conexao_db = FWA_Conexao("OpcTec");
#----------------------------------------------------------------------
#Mensagens de ERRO

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
#----------------------------------------------------------------------
#Lista Trabalhos a serem avaliados da Area Tematica a qual o convidado esta associado

    ## Consulta Trabalhos por Área Temática ##

    #Definindo comando SQL
    $var_sql = "
    SELECT t.cod_evento, es.cod_sessao, i.cod_inscrito, t.cod_trabalho
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
    INNER JOIN tab_ensalamento_trabalho_sessao ts ON es.cod_sessao=ts.cod_sessao
    INNER JOIN tab_trabalhos t ON t.cod_trabalho=ts.cod_trabalho
    LEFT JOIN tab_opcoes_areas_tematicas toa ON t.cod_area_tematica=toa.cod_area_tematica
    INNER JOIN tab_inscritos i ON i.cod_inscrito=t.cod_inscrito
    WHERE t.cod_evento=".COD_EVENTO."
    ORDER BY t.cod_evento, es.cod_sessao";

    #echo $var_sql."<br>";

    $RS=FWA_Executa_SQL($FWS_Debug,$Conexao_db,$var_sql); //Consulta login existe
        ?>
        <style>
            .h3, h3 {
                margin-left: 0px;
            }
            .h4, h4,.h3, h3  {
                margin-bottom: 7px;
            }
            .tableTit{
                border-spacing: 5px;
            }
            .tdCel{
                padding: 7px;
            }
        </style>
        <?php
    #Verificando se houve retorno de algum registro
    if (!$RS->EOF) {
        echo "<table border='0' width='100%' class='tableTit'>";

        while (!$RS->EOF) {

        $cod_inscrito    = $RS->fields[cod_inscrito];
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
            echo "
            <tr><td colspan='3'><hr size='1' width='100%'>
            <B>Segue abaixo lista do Programa e Ensalamento:</B>
            <hr size='1' width='100%'>
            </td></tr>
            ";
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
        
                
                if($turno!=$$turno_ant){
                    echo "<tr valign='top' bgcolor='#".$bgcolor."'><td colspan='9' class='tdCel'><hr></td></tr>";
                    echo "
                    <tr valign='top' bgcolor='#".$bgcolor."'>
                    <td colspan='9' class='tdCel'>
                        <h3>Sala: ".$sala." - ".$data." - Turno: ".$turno."</h4>
                    </td></tr>";
                }
                
                if($titulo_area_tematica!=$titulo_area_tematica_ant){                
                echo "
                <tr valign='top' bgcolor='#".$bgcolor."'>
                <td colspan='9' class='tdCel'>
                    <h3>Eixo: ".$titulo_area_tematica."</h3>
                </td></tr>
                
                <tr valign='top' bgcolor='#" . $bgcolor . "'>
                <td colspan='9'><hr size='1' width='100%'></td></tr>
                <tr valign='top' bgcolor='#" . $bgcolor . "' style='padding:8px;font-weight:700'>
                    <td class='tdCel'>Seq.</td>
                    <td class='tdCel'>Horário</td>
                    <td class='tdCel'>Cod.Trabalho</td>
                    <td class='tdCel'>Titulo do Trabalho</td>
                    <td class='tdCel'>Autores</td>
                </tr>";
                $i=0;
            }
            $sala_ant = $sala;
            $$turno_ant = $turno;
            $titulo_area_tematica_ant = $titulo_area_tematica;

            
            @$i = $i+1;
            $cod_trabalho = FWA_Zero_Esquerda($cod_trabalho,4);
            echo "
            <tr valign='top' bgcolor='#".$bgcolor."' style='padding:8px;'>
              <td class='tdCel'><font color='#".$fontcolor."'>".$i."</font></td>
              <td class='tdCel'><font color='#".$fontcolor."'>".$hora_ini."</font></td>
              <td class='tdCel'><font color='#".$fontcolor."'>".$cod_trabalho."</font></td>
              <td class='tdCel'><font color='#".$fontcolor."'>".$titulo_trabalho."</font></td>
              <td class='tdCel'><font color='#".$fontcolor."'>
            ";

            if ($nome_inscrito != $autor_apresentador_nome and $autor_apresentador_nome!="") {
                echo "<b>" . $nome_inscrito . " | " . $autor_apresentador_nome . "</b>";
            }else {
                echo "<b>" . $nome_inscrito . " | </b>";
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

            echo ";</td></tr>";


            #}#if(($avaliacao=="APROVADO")or($avaliacao=="APROVADO-REPROVADO")or($avaliacao=="APROVADO-CA-REPROVADO")or($avaliacao=="APROVADO-REPROVADO-REPROVADO")or($avaliacao=="REALOCADO-TEMA-15-APROVADO")or($avaliacao=="REALOCADO-TEMA-15-APROVADO-REPROVADO")){
            #Movendo para o proximo registro
            $RS->MoveNext();
        }
        echo "</table><hr size='1' width='100%'>";
    }

echo " <hr size='1' width='100%'>";

$iii = $i-$ii;

echo "
<hr size='1' width='100%'>

Total Trabalhos Ensalados= ".$i."<br>
Total Trabalhos E-mail não Enviado= ".$ii."<br>
Total Trabalhos Ensalados - Repetidos= ".$iii."<br>


<hr size='1' width='100%'>
<a class='link' href='javascript:history.go(-1)'><b>voltar<b></a>
</body>
</html>
";
?>
versao-2
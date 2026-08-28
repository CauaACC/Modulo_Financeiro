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

If($MSG_Imprimir){
  echo $MSG_Imprimir."<hr size='1' width='100%'>";
}
#----------------------------------------------------------------------
#Lista Trabalhos a serem avaliados da Area Tematica a qual o convidado esta associado

    ## Consulta Trabalhos por Área Temática ##

    #Definindo comando SQL
    $var_sql = "
                 SELECT ti.cod_inscrito
                      , ti.nome_inscrito
                      , ti.e_mail
                      , tt.autor_apresentador_e_mail
                      , tt.autor_apresentador_nome
                      , tt.nome_co_autor_1
                      , tt.nome_co_autor_2
                      , tt.nome_co_autor_3
                      , tt.nome_co_autor_4
                      , tt.nome_co_autor_5
                      , tt.cod_trabalho
                      , tt.titulo_trabalho
                      , tt.resumo_trabalho
                      , tt.instituicao_trabalho
                      , tt.palavras_chave
                      , toa.cod_area_tematica
                      , toa.ordem
                      , toa.titulo
                      , ta.avaliacao
                      , ta.observacao
                      , ta.e_mail_enviado
                      
                 FROM tab_opcoes_areas_tematicas toa, tab_trabalhos tt
                 , tab_inscritos ti, tab_trabalhos_avaliacao ta
                 WHERE (tt.cod_evento       =".COD_EVENTO.")
                    and(tt.cod_trabalho     =ta.cod_trabalho)
                    and(tt.cod_area_tematica=toa.cod_area_tematica)
                    and(tt.cod_inscrito     =ti.cod_inscrito)
                    and(ti.status           = 'ATIVADA')
                    and(tt.status           ='ATIVADA')
                    and(ta.status           ='ATIVADA')
                 ORDER BY toa.ordem,tt.cod_trabalho
               ";

    #echo $var_sql."<br>";
    $RS=$Conexao_db->Execute($var_sql);
//    $RS=FWA_Executa_SQL($FWS_Debug,$Conexao_db,$var_sql); //Consulta login existe

    #Verificando se houve retorno de algum registro
    If (!$RS->EOF) {
      echo "<table border='0' width='100%' colspan='0' cellspacing='0'>";

      While (!$RS->EOF) {

        $cod_inscrito              = $RS->fields[0];
        $nome_inscrito             = trim($RS->fields[1]);
        $e_mail                    = trim($RS->fields[2]);
        $autor_apresentador_e_mail = trim($RS->fields[3]);
        $autor_apresentador_nome   = trim($RS->fields[4]);
        $nome_co_autor_1           = trim($RS->fields[5]);
        $nome_co_autor_2           = trim($RS->fields[6]);
        $nome_co_autor_3           = trim($RS->fields[7]);
        $nome_co_autor_4           = trim($RS->fields[8]);
        $nome_co_autor_5           = trim($RS->fields[9]);
        $cod_trabalho              = $RS->fields[10];
        $titulo_trabalho           = $RS->fields[11];
        $resumo_trabalho           = $RS->fields[12];
        $instituicao_trabalho      = trim($RS->fields[13]);
        $palavras_chave            = $RS->fields[14];
        $cod_area_tematica         = $RS->fields[15];
        $ordem                     = $RS->fields[16];
        $titulo                    = $RS->fields[17];
        $avaliacao                 = $RS->fields[18];
        $observacao                = $RS->fields[19];
        $e_mail_enviado            = $RS->fields[20];
        $valor                     = $RS->fields[21];

//        $resumo_trabalho = ucfirst(mb_strtolower($resumo_trabalho));
        $e_mail          = strtolower($e_mail);

        If(@$imprimiu_uma_vez!=1){
          echo "
          <tr><td colspan='3'><hr size='1' width='100%'>
          <B>Segue abaixo lista APROVADOS:</B>
          <hr size='1' width='100%'>
          </td></tr>
          ";
        }

        $imprimiu_uma_vez=1;

        If(($avaliacao=="APROVADO")
        or($avaliacao=="APROVADO-REPROVADO")
        or($avaliacao=="APROVADO-CA-REPROVADO")
        or($avaliacao=="APROVADO-REPROVADO-REPROVADO")
        or($avaliacao=="REALOCADO-TEMA-15-APROVADO")
        or($avaliacao=="REALOCADO-TEMA-15-APROVADO-REPROVADO")
        ){


            If ($bgcolor == 'e6e6e6') {
                $bgcolor = "FFFFFF";
            } Else {
                $bgcolor = "e6e6e6";
            }

            @$i = $i + 1;
            @$ii = $ii + 1;

            echo "
            <tr valign='top' bgcolor='#" . $bgcolor . "'>
            <td>";

            If ($cod_area_tematica != $cod_area_tematica_impresso) {
                echo "
                <hr size='1' width='100%'>
                  <b>EIXO</b>: " . maiusculo($titulo) . "<br>
                ";
                $cod_area_tematica_impresso = $cod_area_tematica;
                $ii = 1;
            }

            //echo FWA_Zero_Esquerda($ordem, 2) . "." . FWA_Zero_Esquerda($ii, 2) . "(" . FWA_Zero_Esquerda($cod_trabalho, 4) . ")<br>";
            $titulo_trabalho = str_replace('\"', '"', $titulo_trabalho);
            echo "<br>" . maiusculo($titulo_trabalho) . "<br><br>";

            /*
              If($nome_inscrito!=$autor_apresentador_nome){
              echo "<b>Atenção: nome do inscrito esta diferente do nome do apresentador.</b><br>";
              echo "Nome Inscrito: ".$nome_inscrito."<br><br>";
              }
             */
            
            If ($nome_inscrito != $autor_apresentador_nome) {
                if ($autor_apresentador_nome != '') {
                    $autores = maiusculo($nome_inscrito . ", " . $autor_apresentador_nome);
                } else {
                    $autores = maiusculo($nome_inscrito);
                }
            } Else {
                $autores = $autor_apresentador_nome;
            }

            If (($nome_co_autor_1 != "")and($nome_co_autor_1 != "")) {
                $autores .=" | " . $nome_co_autor_1;
            }
            If (($nome_co_autor_1 != "")and($nome_co_autor_2 != "")) {
                $autores .= ", " . $nome_co_autor_2;
            }
            If (($nome_co_autor_1 != "")and($nome_co_autor_3 != "")) {
                $autores .= ", " . $nome_co_autor_3;
            }
            If (($nome_co_autor_1 != "")and($nome_co_autor_4 != "")) {
                $autores .= ", " . $nome_co_autor_4;
            }
            If (($nome_co_autor_1 != "")and($nome_co_autor_5 != " ")) {
                $autores .= ", " . $nome_co_autor_5;
            }

            if(substr($autores,-1)==','){
                $autores = substr($autores,0,-1).'. ';
            }else{
                $autores .= '. ';
            }
            
            echo "<b>Autores: </b>".$autores." ";
            
            echo "<b>Instituição: </b> ".$instituicao_trabalho."<br><br>";
            
            echo "<b>Palavras-chave: </b>" . $palavras_chave . "<br><br>";

            echo "
            <p align='justify'>
            " . negritarResumo($resumo_trabalho) . "<br>
            ";

            echo "<hr size='1' width='100%'>&nbsp</td></tr>";

        }#If(($avaliacao=="APROVADO")or($avaliacao=="APROVADO-REPROVADO")or($avaliacao=="APROVADO-CA-REPROVADO")or($avaliacao=="APROVADO-REPROVADO-REPROVADO")or($avaliacao=="REALOCADO-TEMA-15-APROVADO")or($avaliacao=="REALOCADO-TEMA-15-APROVADO-REPROVADO")){

        #Movendo para o proximo registro

        $RS->MoveNext();
      }
      echo "</table><hr size='1' width='100%'>";
    }

echo " <hr size='1' width='100%'>";


echo "
<hr size='1' width='100%'>

Total Registros= ".$i."

<hr size='1' width='100%'>
<a class='link' href='javascript:history.go(-1)'><b>voltar<b></a>
</body>
</html>
";
?>

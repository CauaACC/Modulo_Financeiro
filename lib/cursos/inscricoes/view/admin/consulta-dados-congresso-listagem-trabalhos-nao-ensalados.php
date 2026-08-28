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
                SELECT toa.ordem
                      ,toa.titulo
                      ,teo.numero_sala
                      ,teo.bloco
                      ,teo.dia
                      ,teo.hora
                      ,teo.obs
                      ,tet.cod_trabalho
                      ,tt.titulo_trabalho
                      ,ti.nome_inscrito
                      ,tt.autor_apresentador_nome
                      ,tt.nome_co_autor_1
                      ,tt.nome_co_autor_2
                      ,tt.nome_co_autor_3
                      ,tt.nome_co_autor_4
                      ,tt.nome_co_autor_5
                FROM tab_ensalamento_opcoes teo
                   , tab_ensalamento_trabalhos tet
                   , tab_opcoes_areas_tematicas toa
                   , tab_inscritos ti
                   , tab_trabalhos tt
                WHERE (teo.cod_ensalamento =tet.cod_ensalamento)
                   and(teo.ordem=tet.ordem)
                   and(tet.ordem=toa.ordem)
                   and(tet.cod_trabalho=tt.cod_trabalho)
                   and(ti.cod_inscrito=tt.cod_inscrito)
                   and(tt.cod_evento       =1)
                   and(ti.status           <>'EXCLUÍDA')
                ORDER BY toa.ordem,toa.titulo,teo.dia,teo.hora,teo.numero_sala,tt.cod_trabalho, tt.titulo_trabalho
               ";

    #echo $var_sql."<br>";

    $RS=FWA_Executa_SQL($FWS_Debug,$Conexao_db,$var_sql); //Consulta login existe

    #Verificando se houve retorno de algum registro
    If (!$RS->EOF) {
      echo "<table border='0' width='100%' colspan='0' cellspacing='0'>";

      While (!$RS->EOF) {

        $ordem                   = $RS->fields[0];
        $titulo                  = $RS->fields[1];
        $numero_sala             = $RS->fields[2];
        $bloco                   = $RS->fields[3];
        $dia                     = $RS->fields[4];
        $hora                    = $RS->fields[5];
        $obs                     = $RS->fields[6];
        $cod_trabalho            = $RS->fields[7];
        $titulo_trabalho         = $RS->fields[8];
        $nome_inscrito           = $RS->fields[9];
        $autor_apresentador_nome = $RS->fields[10];
        $nome_co_autor_1         = $RS->fields[11];
        $nome_co_autor_2         = $RS->fields[12];
        $nome_co_autor_3         = $RS->fields[13];
        $nome_co_autor_4         = $RS->fields[14];
        $nome_co_autor_5         = $RS->fields[15];

        If(@$imprimiu_uma_vez!=1){
          echo "
          <tr><td colspan='3'><hr size='1' width='100%'>
          <B>Segue abaixo lista do Programa e Ensalamento:</B>
          <hr size='1' width='100%'>
          </td></tr>
          ";
        }

        $imprimiu_uma_vez=1;
/*
        If(($avaliacao=="APROVADO")
         or($avaliacao=="APROVADO-REPROVADO")
         or($avaliacao=="APROVADO-CA-REPROVADO")
         or($avaliacao=="APROVADO-REPROVADO-REPROVADO")
         or($avaliacao=="REALOCADO-TEMA-15-APROVADO")
         or($avaliacao=="REALOCADO-TEMA-15-APROVADO-REPROVADO")
        ){
*/

          @$i = $i+1;

          If($bgcolor=='e6e6e6'){
            $bgcolor="FFFFFF";
          }Else{
            $bgcolor="e6e6e6";
          }
          
          If($obs=='vermelho'){
            $fontcolor=="FF0000";
            @$ii = $ii+1;

          }Else{
            $fontcolor=="000000";
          }


          If($ordem!=$ordem_impresso){

            echo "
              <tr valign='top' bgcolor='#".$bgcolor."'>
              <td colspan='3'>
              <hr size='1' width='100%'>
              <font size='2' color='#".$fontcolor."'><b>
                ".$ordem." - ".$titulo."<br>
              </b></font>
              ";
              
            $ordem_impresso = $ordem;
          }
          
          If($numero_sala!=$numero_sala_impresso){
          
            echo "
              <tr valign='top' bgcolor='#".$bgcolor."'>
              <td colspan='3'>
              <hr size='1' width='100%'>
              <b>Sala: ".$numero_sala." - ".$bloco." - ".$dia." - ".$hora."</b> 
              <hr size='1' width='100%'></td></tr>
              ";
            $numero_sala_impresso = $numero_sala;
          }

          echo "
            <tr valign='top' bgcolor='#".$bgcolor."'>
              <td>".$cod_trabalho."</td>
              <td>".$titulo_trabalho."</td>
              <td>
          ";

          If($nome_inscrito!=$autor_apresentador_nome){
            echo $nome_inscrito."; ".$autor_apresentador_nome;
          }Else{
            echo $autor_apresentador_nome;
          }

          If(($nome_co_autor_1!="")and($nome_co_autor_1!=" ")){
            echo "; ".$nome_co_autor_1;
          }
          If(($nome_co_autor_1!="")and($nome_co_autor_2!=" ")){
            echo "; ".$nome_co_autor_2;
          }
          If(($nome_co_autor_1!="")and($nome_co_autor_3!=" ")){
            echo "; ".$nome_co_autor_3;
          }
          If(($nome_co_autor_1!="")and($nome_co_autor_4!=" ")){
            echo "; ".$nome_co_autor_4;
          }
          If(($nome_co_autor_1!="")and($nome_co_autor_5!=" ")){
            echo "; ".$nome_co_autor_5;
          }

          echo ";</td></tr>";


        #}#If(($avaliacao=="APROVADO")or($avaliacao=="APROVADO-REPROVADO")or($avaliacao=="APROVADO-CA-REPROVADO")or($avaliacao=="APROVADO-REPROVADO-REPROVADO")or($avaliacao=="REALOCADO-TEMA-15-APROVADO")or($avaliacao=="REALOCADO-TEMA-15-APROVADO-REPROVADO")){

        #Movendo para o proximo registro

        $RS->MoveNext();
      }
      echo "</table><hr size='1' width='100%'>";
    }

echo " <hr size='1' width='100%'>";


echo "
<hr size='1' width='100%'>

Total Trabalhos Ensalados= ".$i."
Total Trabalhos Repetidos= ".$ii."

<hr size='1' width='100%'>
<a class='link' href='javascript:history.go(-1)'><b>voltar<b></a>
</body>
</html>
";
?>

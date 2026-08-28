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
                      ,ti.nome_inscrito
                      ,te.enderecamento_sala
                      ,te.e_mail_enviado
                      ,tt.cod_trabalho
                      ,tt.titulo_trabalho
                      ,ti.nome_inscrito
                      ,tt.autor_apresentador_nome
                      ,tt.nome_co_autor_1
                      ,tt.nome_co_autor_2
                      ,tt.nome_co_autor_3
                      ,tt.nome_co_autor_4
                      ,tt.nome_co_autor_5
                      ,toa.titulo
                FROM tab_ensalamento_novo te
                   , tab_inscritos ti
                   , tab_trabalhos tt
                   , tab_opcoes_areas_tematicas toa
                WHERE (te.cod_trabalho = tt.cod_trabalho)
                   and(tt.cod_area_tematica = toa.cod_area_tematica)    
                   and(tt.cod_inscrito = ti.cod_inscrito)
                   and(tt.cod_evento   =1)
                   and(ti.status       = 'ATIVADA')
                ORDER BY te.enderecamento_sala
               ";

    #echo $var_sql."<br>";

    $RS=FWA_Executa_SQL($FWS_Debug,$Conexao_db,$var_sql); //Consulta login existe

    #Verificando se houve retorno de algum registro
    If (!$RS->EOF) {
      echo "<table border='0' width='100%' colspan='0' cellspacing='2'>";

      While (!$RS->EOF) {

        $cod_inscrito             = $RS->fields[0];
        $nome_inscrito            = $RS->fields[1];
        $enderecamento_sala       = $RS->fields[2];
        $email_enviado            = $RS->fields[3];
        $cod_trabalho             = $RS->fields[4];
        $titulo_trabalho          = $RS->fields[5];
        $nome_inscrito            = $RS->fields[6];
        $autor_apresentador_nome  = $RS->fields[7];
        $nome_co_autor_1          = $RS->fields[8];
        $nome_co_autor_2          = $RS->fields[9];
        $nome_co_autor_3          = $RS->fields[10];
        $nome_co_autor_4          = $RS->fields[11];
        $nome_co_autor_5          = $RS->fields[12];
        $titulo                   = $RS->fields[13];

        If($email_enviado!='SIM'){
          $email_enviado="ERRO";
        }

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
          If($bgcolor=='e6e6e6'){
            $bgcolor="FFFFFF";
          }Else{
            $bgcolor="e6e6e6";
          }

          If($email_enviado=='ERRO'){
            $fontcolor="FF0000";
            @$ii = $ii+1;
          }Else{
            $fontcolor="000000";
          }


          If($enderecamento_sala!=$enderecamento_sala_impresso){

            echo "
              <tr valign='top' bgcolor='#".$bgcolor."'>
              <td colspan='4'>
              <hr size='1' width='100%'>
              <b>".$enderecamento_sala."</b>
              <hr size='1' width='100%'></td></tr>
              <tr valign='top' bgcolor='#".$bgcolor."'>
                <td>Enviou E-mail</td>
                <td>Cód.Trabalho</td>
                <td>Titulo</td>
                <td>Autores</td>
          </tr>
        <!--tr valign='top' bgcolor='#".$bgcolor."'><td colspan='3'><hr size='1' width='100%'></td></tr-->
              ";
            $enderecamento_sala_impresso = $enderecamento_sala;
          }

          @$i = $i+1;
          $cod_trabalho = FWA_Zero_Esquerda($cod_trabalho,4);
          echo "
            <tr valign='top' bgcolor='#".$bgcolor."'>
              <td><font color='#".$fontcolor."'>".$email_enviado."</font></td>
              <td><font color='#".$fontcolor."'>".$cod_trabalho."</font></td>
              <td><font color='#".$fontcolor."'>".$titulo_trabalho."</font></td>
              <td><font color='#".$fontcolor."'>
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
versao-1
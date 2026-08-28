<?php
require_once(dirname(__FILE__)."/../../include/config.php");

require_once(dirname(__FILE__)."/../../view/admin/trabalhos-cadastro-cabecalho.php");

#require_once(dirname(__FILE__)."/../../view/admin/trabalhos-cadastro-busca.php");

#----------------------------------------------------------------------
#Conectar no Banco

$FWS_Debug = false;

$Conexao_db = FWA_Conexao("OpcTec");
@$cod_area_tematica = $_SESSION['cod_area_tematica'];
@$detalhes          = $_SESSION['detalhes'];

If($cod_area_tematica!=""){
#----------------------------------------------------------------------
#Localizar area tematica a qual o avaliador esta associado

## Consulta Trabalhos por Área Temática ##
#Definindo comando SQL
$var_sql = "
           SELECT
                  toa.cod_area_tematica
                 ,toa.titulo
                 ,toa.n_max_trab_por_avaliador
                 ,(
                   SELECT Count(tb.cod_area_tematica) as total_inscritos
                   FROM tab_trabalhos tb, tab_inscritos ti
                   WHERE (tb.cod_area_tematica=toa.cod_area_tematica)
                      and(tb.cod_inscrito     =ti.cod_inscrito)
                      and(ti.status            <>'EXCLUÍDA')
                   GROUP BY tb.cod_area_tematica
                  ) as total_inscritos
                 ,(
                   SELECT Count(tba.cod_area_tematica) as total_avaliados
                   FROM tab_trabalhos_avaliacao tba, tab_inscritos ti
                   WHERE (tba.cod_area_tematica=toa.cod_area_tematica)
                      and(tba.cod_inscrito     =ti.cod_inscrito)
                      and(ti.status            <>'EXCLUÍDA')
                      and((tba.avaliacao='APROVADO')or(tba.avaliacao='REPROVADO'))
                   GROUP BY tba.cod_area_tematica
                  ) as total_avaliados
                 ,(
                   SELECT Count(tba.cod_area_tematica) as total_aprovados
                   FROM tab_trabalhos_avaliacao tba, tab_inscritos ti
                   WHERE (tba.cod_area_tematica=toa.cod_area_tematica)
                      and(tba.cod_inscrito     =ti.cod_inscrito)
                      and(ti.status            <>'EXCLUÍDA')
                      and(tba.avaliacao        ='APROVADO')
                   GROUP BY tba.cod_area_tematica
                  ) as total_aprovados
                 ,(
                   SELECT Count(tba.cod_area_tematica) as total_reprovados
                   FROM tab_trabalhos_avaliacao tba, tab_inscritos ti
                   WHERE (tba.cod_area_tematica=toa.cod_area_tematica)
                      and(tba.cod_inscrito     =ti.cod_inscrito)
                      and(tba.avaliacao        ='REPROVADO')
                   GROUP BY tba.cod_area_tematica
                  ) as total_reprovados
           FROM tab_opcoes_areas_tematicas toa
           WHERE
              (toa.cod_evento        = ".COD_EVENTO.")
           AND(toa.cod_area_tematica = ".$cod_area_tematica.")
           ";

#echo $var_sql."<br>";

$RS=FWA_Executa_SQL($FWS_Debug,$Conexao_db,$var_sql); //Consulta login existe

If (!$RS->EOF) {
  While (!$RS->EOF) {
    #Adicionando nas variaveis, os valores dos campos retirados dos registros
    $cod_area_tematica          = $RS->fields[0];
    $titulo_area_tematica       = $RS->fields[1];
    $n_max_trab_por_avaliador   = $RS->fields[2];
    $total_inscritos  = $RS->fields[3];
    $total_avaliados  = $RS->fields[4];
    $total_aprovados  = $RS->fields[5];
    $total_reprovados = $RS->fields[6];

    #echo $n_max_trab_por_avaliador."-";
    #echo $n_trab_aval_pelo_avaliador."<br>";

    #Movendo para o proximo registro
    $RS->MoveNext();
  }
}

echo "
      <table border='0' width='100%' colspacing='2' cellspacing='3'>
      <tr><td colspan='3' class='subtitulo'>
      Área Temática:<br><br><b>".$cod_area_tematica." - ".$titulo_area_tematica."</b>
      <br>
              <br>
              | Total Trabahos ->
                Inscritos: ".$total_inscritos."
              | A Serem Avaliados por cada Avaliador: ".$n_max_trab_por_avaliador."
              |
              <br>
              | Total Trabahos ->
                Avaliados: ".$total_avaliados."
              | Aprovados: ".$total_aprovados."
              | Reprovados:".$total_reprovados."
              |
      </td></tr>
      </table>
      ";


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
  echo "<table width='100%'><tr><td><hr size='1' width='100%'>".$MSG_Imprimir."<hr size='1' width='100%'></td></tr></table>";
}
#----------------------------------------------------------------------
#Lista Trabalhos a serem avaliados da Area Tematica a qual o convidado esta associado
If(isset($cod_area_tematica)){

    ## Consulta Trabalhos por Área Temática ##

    If(@$detalhes==1){
      $FWS_Detalhes_1 = "and(tt.cod_trabalho     in (SELECT ta.cod_trabalho FROM tab_trabalhos_avaliacao ta WHERE (ta.cod_area_tematica=".$cod_area_tematica.")))";
      $FWS_Detalhes_2 = "AVALIADOS";
      $FWS_Detalhes_3 = "view/admin/trabalhos-cadastro-detalhes.php";
      #$FWS_Detalhes_4 = "";
    }Else{
      $FWS_Detalhes_1 = "and(tt.cod_trabalho not in (select ta.cod_trabalho from tab_trabalhos_avaliacao ta))";
      $FWS_Detalhes_2 = "A SEREM AVALIADOS";
      $FWS_Detalhes_3 = "view/admin/trabalhos-cadastro-detalhes.php";
      #$FWS_Detalhes_4 = " Limit 0-50 ";

    }
/*
               , (SELECT Count(tt.cod_trabalho) as Total_Trabalhos_Enviados
                 FROM tab_trabalhos tt, tab_inscritos ti
                 WHERE (tt.cod_evento       = ".COD_EVENTO.")
                    and(tt.cod_area_tematica=".$cod_area_tematica.")
                    and(tt.cod_area_tematica=toa.cod_area_tematica)
                    and(tt.cod_inscrito     = ti.cod_inscrito)
                    and(ti.status          <> 'EXCLUÍDA')
                    ".$FWS_Detalhes_1."
                  )
*/

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
               ,(SELECT ta.avaliacao  FROM tab_trabalhos_avaliacao ta WHERE (ta.cod_trabalho=tt.cod_trabalho))
               FROM tab_trabalhos tt, tab_inscritos ti, tab_opcoes_areas_tematicas toa
               WHERE
                    (tt.cod_evento       =".COD_EVENTO.")
                 and(tt.cod_area_tematica=".$cod_area_tematica.")
                 and(tt.cod_area_tematica=toa.cod_area_tematica)
                 and(tt.cod_inscrito     =ti.cod_inscrito)
                 and(ti.status          <> 'EXCLUÍDA')
                 ".$FWS_Detalhes_1."
               ORDER BY toa.titulo ASC
                ".$FWS_Detalhes_4."
               ";

    #echo $var_sql;

    #Executando a consulta no banco SQL
    $RS=FWA_Executa_SQL($FWS_Debug,$Conexao_db,$var_sql); //Consulta login existe

    #Verificando se houve retorno de algum registro
    If (!$RS->EOF) {

      $var_sql = "
      SELECT Count(tt.cod_trabalho) as Total_Trabalhos_Enviados
      FROM tab_trabalhos tt, tab_inscritos ti
      WHERE (tt.cod_evento       = ".COD_EVENTO.")and
            (tt.cod_area_tematica=".$cod_area_tematica.")and
            (tt.cod_inscrito     = ti.cod_inscrito)and
            (ti.status          <> 'EXCLUÍDA')
            ".$FWS_Detalhes_1."
       ";

      #echo $var_sql."<br>";
      #$RSTotal = $Conexao_db->Execute($var_sql);
      $RSTotal=FWA_Executa_SQL($FWS_Debug,$Conexao_db,$var_sql); //Consulta login existe

      #Verificando se houve retorno de algum registro
      If (!$RSTotal->EOF) {
         $total_trabalhos_enviados = $RSTotal->fields[0];
      }

      #Definindo cabeçalho da tabela dos resultados da consula
      echo "
      <table border='0' width='100%' colspan='2' cellspacing='3'>
      <tr><td colspan='3'>
      Segue abaixo lista de titulos dos: ".$total_trabalhos_enviados." trabalhos <b>".$FWS_Detalhes_2."</b>:
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
        $avaliacao                  = $RS->fields[15];

        $var_sql = "
        SELECT ta.observacao  
        FROM tab_trabalhos tt, tab_inscritos ti, tab_trabalhos_avaliacao ta 
        WHERE (ta.cod_trabalho     = tt.cod_trabalho)
           and(tt.cod_trabalho     = ".$cod_trabalho.")
           and(tt.cod_evento       = ".COD_EVENTO.")
           and(tt.cod_area_tematica= ".$cod_area_tematica.")
           and(tt.cod_inscrito     = ti.cod_inscrito)
           and(ti.status          <> 'EXCLUÍDA')
         ";

        $RSObs=FWA_Executa_SQL($FWS_Debug,$Conexao_db,$var_sql); //Consulta login existe
        #echo $var_sql."<br>";
        #$RSObs = $Conexao_db->Execute($var_sql);

        #Verificando se houve retorno de algum registro
        If (!$RSObs->EOF) {
           $observacao = $RSObs->fields[0];
        }Else{
           $observacao = "/-/";
        }

        If($avaliacao!=""){
          if($avaliacao=="REPROVADO"){
            $color = "#ff0000";
          }Else{
            $color = "#000000";
          }
          $avaliacao = "<b><font color='".$color."'>".$avaliacao."</font></b> - ";
        }

        #Definindo tabela com resultado

        echo "
        <tr><td colspan='3'>
        <ul><li>
          <a class='link' href='index.php?content=".$FWS_Detalhes_3."&cod_area_tematica=".$cod_area_tematica."&cod_trabalho=".$cod_trabalho."'>".$avaliacao.$titulo_trabalho."</a>
          <br><b>Obs</b>:<br>".$observacao."
        </li></ul>
        </td></tr>
        ";

        #Movendo para o proximo registro
        $RS->MoveNext();
      }
      echo "</table><br>";
    }Else{
       echo "<table width='100%'><tr><td>
             <hr size='1' width='100%'><br><br>
             Nenhum trabalho foi avaliado NESTA ÁREA TEMÁTICA por você até o momento.
             <br><br></td></tr></table>";
    }

  }Else{
     echo "<table><tr><td>O código da área temática não foi informado.</td></tr></table>";
  }
}Else{
   echo "<table><tr><td>Selecione uma Eixo Temático no select acima.</td></tr></table>";
}

?>
<hr size='1' width='100%'>
<a class='link' href='javascript:history.go(-1)'><b>voltar<b></a>
</body>
</html>

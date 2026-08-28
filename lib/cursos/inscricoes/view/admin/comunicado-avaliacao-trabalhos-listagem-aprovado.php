<?php
require_once(dirname(__FILE__)."/../../include/config.php");

require_once(dirname(__FILE__)."/../../view/admin/comunicado-avaliacao-trabalhos-cabecalho.php");

#require_once(dirname(__FILE__)."/../../view/admin/comunicado-avaliacao-trabalhos-busca.php");

#----------------------------------------------------------------------
#Conectar no Banco

$FWS_Debug = false;

$Conexao_db = FWA_Conexao("OpcTec");

#----------------------------------------------------------------------
#Paginação

@$ultimo_registro   = $_GET['ultimo_registro'];
@$primeiro_registro = $_GET['primeiro_registro'];
@$atual_pagina      = $_GET['atual_pagina'];
@$acao              = $_GET['acao'];

$registros_por_pagina= 30;

If($acao=="avancar"){
  $registro_ini = $ultimo_registro;
  $registro_fim = $ultimo_registro+$registros_por_pagina+1;
  $atual_pagina = $atual_pagina+1;
}

If($acao=="voltar"){
  $registro_ini = $primeiro_registro-$registros_por_pagina-1;
  $registro_fim = $primeiro_registro;
  $atual_pagina = $atual_pagina-1;
}

If($acao=="atualizar"){
  $registro_ini = $primeiro_registro;
  $registro_fim = $primeiro_registro+$registros_por_pagina+1;
  $atual_pagina = $atual_pagina;
}


If($acao==""){
  $registro_ini = 0;
  $registro_fim = $registros_por_pagina+1;
  $atual_pagina = 1;
}

/*
echo $registro_ini."<br>";
echo $registro_fim."<br>";
echo $avancar."<br>";
echo $registros_por_pagina."<br>";
*/
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
                     , tt.cod_trabalho
                     , tt.titulo_trabalho
                     , toa.cod_area_tematica
                     , toa.titulo
                     , (SELECT distinct(ta.avaliacao)      FROM tab_trabalhos_avaliacao ta WHERE (ta.cod_trabalho=tt.cod_trabalho)) AS avaliacao
                     , (SELECT distinct(ta.e_mail_enviado) FROM tab_trabalhos_avaliacao ta WHERE (ta.cod_trabalho=tt.cod_trabalho)) AS e_mail_enviado
                     ,(
                       SELECT Count(ta.cod_evento) as total_geral_avaliados
                       FROM tab_trabalhos_avaliacao ta, tab_inscritos ti, tab_opcoes_areas_tematicas toa
                       WHERE (ta.cod_evento       =1)
                          and(ta.cod_area_tematica=toa.cod_area_tematica)
                          and(ta.cod_inscrito     =ti.cod_inscrito)
                          and(ti.status           <>'EXCLUÍDA')
                          and(
                              (ta.avaliacao='REALOCADO-TEMA-15-APROVADO')or
                              (ta.avaliacao='REALOCADO-TEMA-15-APROVADO-REPROVADO')or
                              (ta.avaliacao='APROVADO-CA-REPROVADO')or
                              (ta.avaliacao='APROVADO-REPROVADO-REPROVADO')or
                              (ta.avaliacao='APROVADO-REPROVADO')or
                              (ta.avaliacao='APROVADO')or
                              (ta.avaliacao='REPROVADO')or
                              (ta.avaliacao='REPROVADO-REPROVADO')
                             )
                       GROUP BY ta.cod_evento
                      ) as total_geral_avaliados
                     ,(
                       SELECT Count(ta.cod_evento) as total_geral_aprovados
                       FROM tab_trabalhos_avaliacao ta, tab_inscritos ti, tab_opcoes_areas_tematicas toa
                       WHERE (ta.cod_evento       =1)
                          and(ta.cod_area_tematica=toa.cod_area_tematica)
                          and(ta.cod_inscrito     =ti.cod_inscrito)
                          and(ti.status           <>'EXCLUÍDA')
                          and(
                              (ta.avaliacao='REALOCADO-TEMA-15-APROVADO')or
                              (ta.avaliacao='REALOCADO-TEMA-15-APROVADO-REPROVADO')or
                              (ta.avaliacao='APROVADO-CA-REPROVADO')or
                              (ta.avaliacao='APROVADO-REPROVADO-REPROVADO')or
                              (ta.avaliacao='APROVADO-REPROVADO')or
                              (ta.avaliacao='APROVADO')
                             )
                       GROUP BY ta.cod_evento
                      ) as total_geral_aprovados
                     ,(
                       SELECT Count(ta.cod_evento) as total_geral_reprovados
                       FROM tab_trabalhos_avaliacao ta, tab_inscritos ti, tab_opcoes_areas_tematicas toa
                       WHERE (ta.cod_evento       =1)
                          and(ta.cod_area_tematica=toa.cod_area_tematica)
                          and(ta.cod_inscrito     =ti.cod_inscrito)
                          and(ti.status           <>'EXCLUÍDA')
                          and(
                              (ta.avaliacao='REPROVADO')or
                              (ta.avaliacao='REPROVADO-REPROVADO')
                             )
                       GROUP BY ta.cod_evento
                      ) as total_geral_reprovados
                     ,(
                       SELECT Count(ta.cod_evento) as total_geral_aprovados_comunicado_sim
                       FROM tab_trabalhos_avaliacao ta, tab_inscritos ti, tab_opcoes_areas_tematicas toa
                       WHERE (ta.cod_evento       =1)
                          and(ta.cod_area_tematica=toa.cod_area_tematica)
                          and(ta.cod_inscrito     =ti.cod_inscrito)
                          and(ti.status           <>'EXCLUÍDA')
                          and(
                              (ta.avaliacao='REALOCADO-TEMA-15-APROVADO')or
                              (ta.avaliacao='REALOCADO-TEMA-15-APROVADO-REPROVADO')or
                              (ta.avaliacao='APROVADO-CA-REPROVADO')or
                              (ta.avaliacao='APROVADO-REPROVADO-REPROVADO')or
                              (ta.avaliacao='APROVADO-REPROVADO')or
                              (ta.avaliacao='APROVADO')
                             )
                          and(ta.e_mail_enviado   ='SIM')
                       GROUP BY ta.cod_evento
                      ) as total_geral_aprovados_comunicado_sim
                     ,(
                       SELECT Count(ta.cod_evento) as total_geral_reprovados_comunicado_sim
                       FROM tab_trabalhos_avaliacao ta, tab_inscritos ti, tab_opcoes_areas_tematicas toa
                       WHERE (ta.cod_evento       =1)
                          and(ta.cod_area_tematica=toa.cod_area_tematica)
                          and(ta.cod_inscrito     =ti.cod_inscrito)
                          and(ti.status           <>'EXCLUÍDA')
                          and(
                              (ta.avaliacao='REPROVADO')or
                              (ta.avaliacao='REPROVADO-REPROVADO')
                             )
                          and(ta.e_mail_enviado   ='SIM')
                       GROUP BY ta.cod_evento
                      ) as total_geral_reprovados_comunicado_sim
                     ,(
                       SELECT Count(tt.cod_evento) as total_geral_trabalhos
                       FROM tab_opcoes_areas_tematicas toa, tab_trabalhos tt, tab_inscritos ti
                       WHERE (tt.cod_evento       =1)
                          and(tt.cod_area_tematica=toa.cod_area_tematica)
                          and(tt.cod_inscrito     =ti.cod_inscrito)
                          and(ti.status           <>'EXCLUÍDA')
                       GROUP BY tt.cod_evento
                      ) as total_geral_trabalhos
                 FROM tab_opcoes_areas_tematicas toa, tab_trabalhos tt, tab_inscritos ti
                 WHERE (tt.cod_evento       =1)
                    and(tt.cod_area_tematica=toa.cod_area_tematica)
                    and(tt.cod_inscrito     =ti.cod_inscrito)
                    and(ti.status           <>'EXCLUÍDA')
                 ORDER BY tt.cod_trabalho, ti.cod_inscrito
               ";

    #echo $var_sql."<br>";

    $RS=FWA_Executa_SQL($FWS_Debug,$Conexao_db,$var_sql); //Consulta login existe

    #Verificando se houve retorno de algum registro
    If (!$RS->EOF) {
      echo "<table border='0' width='100%' colspan='0' cellspacing='0'>
      <form action='index.php' method='POST'>
      <input type='hidden' name='content' value='view/admin/comunicado-avaliacao-trabalhos-grava-aprovado.php'>
      ";
      While (!$RS->EOF) {

        $cod_inscrito              = $RS->fields[0];
        $nome_inscrito             = $RS->fields[1];
        $e_mail                    = $RS->fields[2];
        $autor_apresentador_e_mail = $RS->fields[3];
        $autor_apresentador_nome   = $RS->fields[4];
        $cod_trabalho              = $RS->fields[5];
        $titulo_trabalho           = $RS->fields[6];
        $cod_area_tematica         = $RS->fields[7];
        $titulo                    = $RS->fields[8];

        $avaliacao                 = $RS->fields[9];
        $e_mail_enviado            = $RS->fields[10];

        $total_geral_avaliados     = $RS->fields[11];
        $total_geral_aprovados     = $RS->fields[12];
        $total_geral_reprovados    = $RS->fields[13];

        $total_geral_aprovados_comunicado_sim= $RS->fields[14];
        $total_geral_reprovados_comunicado_sim= $RS->fields[15];

        $total_geral_trabalhos     = $RS->fields[16];

        If($total_geral_avaliados               ==""){$total_geral_avaliados               =0;}
        If($total_geral_aprovados               ==""){$total_geral_aprovados               =0;}
        If($total_geral_reprovados              ==""){$total_geral_reprovados              =0;}
        If($total_geral_aprovados_comunicado_sim==""){$total_geral_aprovados_comunicado_sim=0;}
        If($total_geral_reprovados_comunicado_sim==""){$total_geral_reprovados_comunicado_sim=0;}
        If($total_geral_trabalhos               ==""){$total_geral_trabalhos               =0;}

        If(@$imprimiu_uma_vez!=1){

          $total_aprovados_falta_envio_comunidado = $total_geral_aprovados - $total_geral_aprovados_comunicado_sim;
          $total_reprovados_falta_envio_comunidado = $total_geral_reprovados - $total_geral_reprovados_comunicado_sim;
          
          $total_paginas = $total_aprovados_falta_envio_comunidado/$registros_por_pagina;
          #settype($total_paginas, "integer");

          echo "<input type='hidden' name='total_comunidado' value='".$total_aprovados_falta_envio_comunidado."'>";

          echo "
          <tr>
            <td colspan='3' class='subtitulo'>
              <hr size='1' width='100%'>
              | Total Trabahos ->
                Inscritos: ".$total_geral_trabalhos."
              |
              <br>
              | Total Trabahos ->
                Avaliados: ".$total_geral_avaliados."
              | Aprovados: ".$total_geral_aprovados."
              | Reprovados:".$total_geral_reprovados."
              <BR>
              | Falta Envio de Comunicados->
                Aprovados:".$total_aprovados_falta_envio_comunidado."
              | Reprovados:".$total_reprovados_falta_envio_comunidado."
              |
              <br>
              | Total que ja foi enviado comunicado de APROVAÃÃO -> ".$total_geral_aprovados_comunicado_sim."

            </td>
          </tr>
          <tr><td colspan='3'><hr size='1' width='100%'>
          <B>Segue abaixo lista APROVADOS que NÃO FOI ENVIADO COMUNICADO:</B>

          <hr size='1' width='100%'>

          | Nº de Registros:".$i."

          | Página Nº:".$atual_pagina."

          -> Total Páginas:".$total_paginas."<hr size='1' width='100%'>

          <br>
          <input type='submit' name='botao' value='  Enviar Comunicado de Aprovação  '>
          &nbsp;&nbsp;&nbsp;&nbsp;
          <input type='reset' value='  Limpar  ' name='B2'>
          <hr size='1' width='100%'>
          </td></tr>
          ";
        }

        $imprimiu_uma_vez=1;

        If((
              ($avaliacao=="APROVADO")
            or($avaliacao=="APROVADO-CA-REPROVADO")
            or($avaliacao=="APROVADO-REPROVADO")
            or($avaliacao=="APROVADO-REPROVADO-REPROVADO")
            or($avaliacao=="REALOCADO-TEMA-15-APROVADO")
            or($avaliacao=="REALOCADO-TEMA-15-APROVADO-REPROVADO")
          )and($e_mail_enviado!="SIM")){
        
          $registro_atual=$registro_atual+1;

          If(($registro_ini<$registro_atual)and($registro_atual<=$registro_fim)){

            If($bgcolor=='e6e6e6'){
              $bgcolor="FFFFFF";
            }Else{
              $bgcolor="e6e6e6";
            }

            @$i = $i+1;

            echo "
            <tr valign='top' bgcolor='#".$bgcolor."'>
            <td>";


            If($e_mail!=$autor_apresentador_e_mail){
              echo "<input type='checkbox' name='cod_trabalho_".$i."' value='".$cod_trabalho."'>";
            }Else{
              echo "<input type='checkbox' name='cod_trabalho_".$i."' value='".$cod_trabalho."'>";
            }


            echo "&nbsp;</td><td>";

            If($e_mail!=$autor_apresentador_e_mail){
              echo "<b>Atenção</b>: O email do inscrito é diferente do email do apresentador<br>&nbsp;";
              echo $autor_apresentador_e_mail."<br>";
            }

            echo "
              Cód.Inscrito: ".$cod_inscrito."<br>
              Nome Inscrito: ".$nome_inscrito."<br>
              E-mail Inscrito: ".$e_mail."<br>
              Autor Apresentador E-mail: ".$autor_apresentador_e_mail."<BR>
              Autor Apresentador Nome: ".$autor_apresentador_nome."<BR>
              Cód.Trabalho: ".$cod_trabalho."<br>
              Titulo do Trabalho: ".$titulo_trabalho."<br>&nbsp;
              Avaliação: ".$avaliacao."<br>&nbsp;
              E-mail Enviado: ".$e_mail_enviado."<br>&nbsp;
            ";

            echo "&nbsp</td></tr>";


            If($i==1){
              $primeiro_registro=$registro_atual;
            }
            If($i==$registros_por_pagina){
              #$RS->MoveLast();
              $ultimo_registro=$registro_atual;
            }
            #echo $i."-".$primeiro_registro."-".$registro_atual."-".$ultimo_registro."<br>";
          }#If(($registro_ini<$registro_atual)and($registro_atual<=$registro_fim)){
        }

        #Movendo para o proximo registro

        $RS->MoveNext();
      }
      echo "</table><hr size='1' width='100%'>";
    }

echo "
<input type='hidden' name='acao' value='Enviar-Comunicado'>

<input type='submit' name='botao' value='  Enviar Comunicado de Aprovação  '>
&nbsp;&nbsp;&nbsp;&nbsp;
<input type='reset' value='  Limpar  ' name='B2'>
</form>
";
echo " <hr size='1' width='100%'>";

echo "| Nº de Registros:".$i;

echo "| Página Nº:".$atual_pagina;

If($total_paginas>=1)
  echo "-> Total Páginas:".$total_paginas;

If(($atual_pagina!=1)and($atual_pagina!=0)){
  echo " | <a class='linkg' href='?content=".$_SESSION['content']."&primeiro_registro=".$primeiro_registro."&ultimo_registro=".$ultimo_registro."&atual_pagina=".$atual_pagina."&registros=".$registros."&acao=voltar'><- voltar</a> | ";
}
If($atual_pagina<=$total_paginas){
  echo " | <a class='linkg' href='?content=".$_SESSION['content']."&primeiro_registro=".$primeiro_registro."&ultimo_registro=".$ultimo_registro."&atual_pagina=".$atual_pagina."&registros=".$registros."&acao=avancar'>avançar -></a> | ";
}

echo "
<hr size='1' width='100%'>

<a class='link' href='javascript:history.go(-1)'><b>voltar<b></a>
</body>
</html>
";
?>
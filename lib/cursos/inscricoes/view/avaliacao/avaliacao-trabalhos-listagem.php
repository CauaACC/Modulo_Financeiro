<?php
require_once(dirname(__FILE__)."/../../include/config.php");
if($_SERVER['REMOTE_ADDR']=='177.100.49.198'){
    $debug=true;
}
?>
<h3>Área do Avaliador</h3>  
<div class="panel-group">
    <div class="panel panel-default">
        <div class="panel-heading">
            <?php require_once(dirname(__FILE__)."/../../view/avaliacao/avaliacao-trabalhos-cabecalho.php"); ?>
        </div>
        <div class="panel-body">
            <h3>Avaliar Trabalhos</h3>
            1<hr>
<?php
If($FWS_Busca==true){
  require_once(dirname(__FILE__)."/../../view/avaliacao/avaliacao-trabalhos-busca.php");
}
echo "2<hr>";
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

#----------------------------------------------------------------------
#Localizar area tematica a qual o avaliador esta associado
## Consulta Trabalhos por Área Temática ##
#echo $fws_['cod_area_tematica'];
#Definindo comando SQL
if($fws_['cod_area_tematica']='-1'){
    $fws_['cod_area_tematica'] = '';
}
//If(isset($fws_['cod_area_tematica'])){
    
    if($fws_['cod_area_tematica'] and $fws_['cod_area_tematica']!='-1' and ($fws_['detalhes']!=1 and $fws_['detalhes']!=2)){
           
        if ($fws_['cod_area_tematica']!='') {
            $var_sql = "
            SELECT toa.cod_area_tematica, toa.titulo, toa.n_max_trab_por_avaliador
            ,(SELECT Count(ta.cod_avaliador) as n_trab_aval_pelo_avaliador FROM tab_trabalhos_avaliacao ta WHERE (ta.cod_avaliador=su.cod_usuario)AND(ta.cod_area_tematica=".$fws_['cod_area_tematica']."))
            FROM sis_usuarios su, tab_lig_convidados_areas_tematicas tlca, tab_opcoes_areas_tematicas toa
            WHERE
              (su.cod_evento    = ".COD_EVENTO.")
            AND(su.cod_usuario   = ".$_SESSION['_cod_usuario'].")
            AND(su.cod_convidado = tlca.cod_convidado)
            AND(tlca.cod_area_tematica = ".$fws_['cod_area_tematica'].")
            AND(tlca.cod_area_tematica = toa.cod_area_tematica)
            AND(tlca.cod_evento = su.cod_evento)
            ";
        }else{
            $var_sql = "
            SELECT toa.cod_area_tematica, toa.titulo, toa.n_max_trab_por_avaliador
            ,(SELECT Count(ta.cod_avaliador) as n_trab_aval_pelo_avaliador 
                FROM tab_trabalhos_avaliacao ta 
                WHERE (ta.cod_avaliador=su.cod_usuario))
            FROM sis_usuarios su, tab_lig_convidados_areas_tematicas tlca, tab_opcoes_areas_tematicas toa
            WHERE
              (su.cod_evento    = ".COD_EVENTO.")
            AND(su.cod_usuario   = ".$_SESSION['_cod_usuario'].")
            AND(su.cod_convidado = tlca.cod_convidado)
            AND(tlca.cod_area_tematica = ".$fws_['cod_area_tematica'].")
            AND(tlca.cod_area_tematica = toa.cod_area_tematica)
            AND(tlca.cod_evento = su.cod_evento)
            ";
        }
        
        echo $debug? "1-.".$var_sql."<hr>" : "";
        
        $RS=FWA_Executa_SQL($FWS_Debug,$Conexao_db,$var_sql); //Consulta login existe

        If (!$RS->EOF) {
          While (!$RS->EOF) {
            #Adicionando nas variaveis, os valores dos campos retirados dos registros
            $cod_area_tematica          = $RS->fields[0];
            $titulo_area_tematica       = $RS->fields[1];
            $n_max_trab_por_avaliador   = $RS->fields[2];
            $n_trab_aval_pelo_avaliador = $RS->fields[3];

            #echo $n_max_trab_por_avaliador."-";
            #echo $n_trab_aval_pelo_avaliador."<br>";

            #Movendo para o proximo registro
            $RS->MoveNext();
          }
        }

        echo "<table border='0' width='100%' colspacing='2' cellspacing='3'>
              <tr><td colspan='3' class='subtitulo'>
              Área Temática:<br><b>".$titulo_area_tematica."</b>
              <br><br>Máximo de trabalhos a serem avaliados por avaliador nesta Área Temátca: ".$n_max_trab_por_avaliador."
              </td></tr>
              </table>
              ";
    }
  
    #----------------------------------------------------------------------
    #Mensagens de ERRO
    if($fws_['msg']){
        FWA_Mostrar_Mensagem($fws_['msg']);
    }
    #----------------------------------------------------------------------
    /*Apenas para a tela de selecionar trabalhos para avaliação
    por área temática do avaliador sem distribuição previa*/
    If(($n_trab_aval_pelo_avaliador<$n_max_trab_por_avaliador)or($fws_['detalhes']!=0)){
        echo $debug? __LINE__."<br>" : "";
    #Lista Trabalhos a serem avaliados da Area Tematica a qual o convidado esta associado
        ## Consulta Trabalhos por Área Temática ##

        If($fws_['detalhes']==2){
            echo $debug? __LINE__."<br>" : "";
          $FWS_Detalhes_1 = "and(tt.cod_trabalho not in (select ta.cod_trabalho from tab_trabalhos_avaliacao ta))";
          $FWS_Detalhes_1 .= "and(tt.cod_trabalho in(select cod_trabalho from tab_historico_avaliacao ta 
                                                            WHERE ta.cod_avaliador=".$_SESSION['_cod_usuario']." 
                                                            AND (ta.avaliacao='DEVOLVIDO' OR ta.avaliacao='SELECIONADO') and ta.status='ATIVADA' ))";
          $whereAreaTematica = "";
          if($fws_['cod_area_tematica'] and $fws_['cod_area_tematica']!='-1'){
            $whereAreaTematica = "and(tt.cod_area_tematica=".$fws_['cod_area_tematica'].")";
          }
          
          $FWS_Detalhes_2 = "AVALIAÇÕES NÃO CONCLUÍDAS";
          $FWS_Detalhes_3 = "view/avaliacao/avaliacao-trabalhos-detalhes-nao-avaliados.php";
          $txtBotaoAcessarTrabalho = "Concluir avaliação";
//          $FWS_Messagem_Nao_Encontrado = "Nenhuma trabalho disponível para AVALIAÇÃO até o momento.";
          $FWS_Messagem_Nao_Encontrado = "Nenhuma trabalho encontrado de acordo com o filtro acima.";
          #$FWS_Detalhes_4 = " Limit 0-50 ";

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
                     ,tt.nome_co_autor_6
                     ,tt.instituicao_trabalho
                     ,tt.titulo_trabalho
                     ,tt.palavras_chave
                     ,tt.resumo_trabalho
                     ,tt.flg_enviado_avaliacao
                     ,''
                     FROM tab_trabalhos tt, tab_inscritos ti, tab_opcoes_areas_tematicas toa
                     WHERE
                          (tt.cod_evento       =".COD_EVENTO.")
                       ".$whereAreaTematica."
                       and(tt.flg_enviado_avaliacao='1' or tt.flg_enviado_avaliacao='0')
                       and(tt.status='ATIVADA')
                       and(tt.cod_area_tematica=toa.cod_area_tematica)
                       and(tt.cod_inscrito     =ti.cod_inscrito)
                       and(ti.status          = 'ATIVADA')
                       ".$FWS_Detalhes_1."
                     ORDER BY toa.titulo ASC
                      ".$FWS_Detalhes_4."
                     ";

        }Else If($fws_['detalhes']==1){
            echo $debug? __LINE__."<br>" : "";
          $FWS_Detalhes_1 = "and(tt.cod_trabalho     in (SELECT ta.cod_trabalho FROM tab_trabalhos_avaliacao ta WHERE (ta.cod_avaliador=".$_SESSION['_cod_usuario'].")AND(ta.cod_area_tematica=".$fws_['cod_area_tematica'].")))";
          
          $fromTabAvaliacao = ", tab_trabalhos_avaliacao tta ";
          $whereTabAvaliacao = " and tta.cod_trabalho=tt.cod_trabalho";
          
          $whereAreaTematica = "";
          if($fws_['cod_area_tematica'] and $fws_['cod_area_tematica']!='-1'){
            $whereAreaTematica = "and(tt.cod_area_tematica=".$fws_['cod_area_tematica'].")";
          }
          
          
          $FWS_Detalhes_2 = "AVALIADOS POR VOCÊ";
          $FWS_Detalhes_3 = "view/avaliacao/avaliacao-trabalhos-detalhes-avaliados.php";
          $txtBotaoAcessarTrabalho = "Consultar";
          
//          $FWS_Messagem_Nao_Encontrado = "Nenhuma trabalho foi AVALIADO por você até o momento.";
          $FWS_Messagem_Nao_Encontrado = "Nenhuma trabalho encontrado de acordo com o filtro acima.";
          #$FWS_Detalhes_4 = "";

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
                     ,tt.nome_co_autor_6
                     ,tt.instituicao_trabalho
                     ,tt.titulo_trabalho
                     ,tt.palavras_chave
                     ,tt.resumo_trabalho
                     ,tt.flg_enviado_avaliacao
                     ,tta.avaliacao
                     FROM tab_trabalhos tt, tab_inscritos ti, tab_opcoes_areas_tematicas toa
                     ".$fromTabAvaliacao."
                     WHERE
                          (tt.cod_evento       =".COD_EVENTO.")
                       and(tt.status='ATIVADA')
                       ".$whereAreaTematica."
                       ".$whereTabAvaliacao."
                       and(tt.cod_area_tematica=toa.cod_area_tematica)
                       and(tt.flg_enviado_avaliacao     =1)
                       and(tt.cod_inscrito     =ti.cod_inscrito)
                       and(ti.status          = 'ATIVADA')
                       and(tt.cod_trabalho     in (SELECT ta.cod_trabalho
                                                   FROM tab_trabalhos_avaliacao ta
                                                   WHERE (ta.cod_avaliador=".$_SESSION['_cod_usuario'].")
                                                  )
                          )
                     ORDER BY toa.titulo ASC
                      ".$FWS_Detalhes_4."
                     ";

        }Else{
            echo $debug? __LINE__."<br>" : "";
            /*para ser avaliado não pode estar na tabela de trabalhos_avaliacao e nem na histórico para não ter avaliador*/
          $FWS_Detalhes_1  = "and(tt.cod_trabalho not in (select ta.cod_trabalho from tab_trabalhos_avaliacao ta))";
          $FWS_Detalhes_1 .= "AND(tt.cod_trabalho NOT IN (SELECT ta.cod_trabalho FROM tab_historico_avaliacao ta WHERE ta.cod_avaliador<>'' and ta.status='ATIVADA'))";
          $FWS_Detalhes_2 = "SEM AVALIADOR";
          $FWS_Detalhes_3 = "view/avaliacao/avaliacao-trabalhos-detalhes-nao-avaliados.php";
          $txtBotaoAcessarTrabalho = "Avaliar";
          
//          $FWS_Messagem_Nao_Encontrado = "Nenhuma trabalho disponível para AVALIAÇÃO até o momento.";
          $FWS_Messagem_Nao_Encontrado = "Nenhuma trabalho encontrado de acordo com o filtro acima.";
          #$FWS_Detalhes_4 = " Limit 0-50 ";

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
                     ,tt.nome_co_autor_6
                     ,tt.instituicao_trabalho
                     ,tt.titulo_trabalho
                     ,tt.palavras_chave
                     ,tt.resumo_trabalho
                     ,tt.flg_enviado_avaliacao
                     ,''
                     FROM tab_trabalhos tt, tab_inscritos ti, tab_opcoes_areas_tematicas toa
                     WHERE
                          (tt.cod_evento       =".COD_EVENTO.")
                       and(tt.cod_area_tematica=".$fws_['cod_area_tematica'].")
                       and(tt.flg_enviado_avaliacao='1')
                       and(tt.status='ATIVADA')
                       and(tt.cod_area_tematica=toa.cod_area_tematica)
                       and(tt.cod_inscrito     =ti.cod_inscrito)
                       and(ti.status          = 'ATIVADA')
                       ".$FWS_Detalhes_1."
                     ORDER BY toa.titulo ASC
                      ".$FWS_Detalhes_4."
                     ";
        }
        echo $debug? __LINE__."<br>" : "";
        echo $debug? $var_sql."<hr>" : "";
        #Fim da definição do comando SQL
        #Executando a consulta no banco SQL
        $RS = $Conexao_db->Execute($var_sql);

        #Verificando se houve retorno de algum registro
        If (!$RS->EOF) {

          $var_sql = "
          SELECT Count(tt.cod_trabalho) as Total_Trabalhos_Enviados
          FROM tab_trabalhos tt, tab_inscritos ti
          WHERE (tt.cod_evento       = ".COD_EVENTO.")and
                (tt.cod_area_tematica=".$fws_['cod_area_tematica'].")and
                (tt.cod_inscrito     = ti.cod_inscrito)and
                (ti.status          <> 'EXCLUÍDA')
                ".$FWS_Detalhes_1."
           ";

          #$RSTotal=FWA_Executa_SQL($FWS_Debug,$Conexao_db,$var_sql); //Consulta login existe
          echo $debug? __LINE__."<br>" : "";
          echo $debug? $var_sql."<hr>" : "";
          #echo $var_sql."<br>";
          $RSTotal = $Conexao_db->Execute($var_sql);

          #Verificando se houve retorno de algum registro
          If (!$RSTotal->EOF) {
             $total_trabalhos_enviados = $RSTotal->fields[0];
          }

          #Definindo cabeçalho da tabela dos resultados da consula
          echo "
          <table border='0' width='100%' colspan='2' cellspacing='3'>
          <tr><td colspan='3'>
          <!--Segue abaixo lista de titulos dos: ".$total_trabalhos_enviados." trabalhos <b>".$FWS_Detalhes_2."</b>:-->
          Segue abaixo lista de titulos dos trabalhos <b>".$FWS_Detalhes_2."</b>:
          <br>&nbsp;
          </td></tr>
          ";
          $titulo_ant = "";
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
            $nome_co_autor_6            = $RS->fields[11];
            $instituicao_trabalho       = $RS->fields[12];
            $titulo_trabalho            = $RS->fields[13];
            $palavras_chave             = $RS->fields[14];
            $resumo_trabalho            = $RS->fields[15];
            $flg_enviado_avaliacao      = $RS->fields[16];
            $avaliacao                  = $RS->fields[17];

            $total_paginas = $total_trabalhos_enviados/$registros_por_pagina;
            #Definindo tabela com resultado
            $registro_atual=$registro_atual+1;

            If(($registro_ini<$registro_atual)and($registro_atual<=$registro_fim)){

              @$i = $i+1;
              
              if($flg_enviado_avaliacao ==0){
                  $txtPBotaoAcessarTrabalho = "Aguardando retorno do autor";
              }else{
                  $txtPBotaoAcessarTrabalho = $txtBotaoAcessarTrabalho;
              }
              
              if($titulo != $titulo_ant){
                echo "<tr><td colspan='3'>{$titulo}</td></tr>";
              }
              $titulo_ant = $titulo;
              
              if($avaliacao=='APROVADO'){
                  $txtAvaliacao = "<font color=green>Apr.</font> ";
              }  else if($avaliacao=='REPROVADO'){
                  $txtAvaliacao = "<font color=red>Rep.</font> ";
              }else{
                  $txtAvaliacao = "";
              }
              echo "
              <tr><td colspan='3'>
              <ul><li>
                <a title='".$txtPBotaoAcessarTrabalho."' class='link' href='index.php?content=".$FWS_Detalhes_3."&cod_area_tematica=".$fws_['cod_area_tematica']."&cod_area_tematica=".$fws_['cod_area_tematica']."&cod_trabalho=".$cod_trabalho."&detalhes=".$fws_['detalhes']."'>
                <input type='button' value='".$txtPBotaoAcessarTrabalho."' class='btn btn-xs btn-primary'></a> - ".$txtAvaliacao.$titulo_trabalho."        
              </li></ul>
              </td></tr>
              ";

              If($i==1){
                $primeiro_registro=$registro_atual;
              }
              If($i==$registros_por_pagina){
                #$RS->MoveLast();
                $ultimo_registro=$registro_atual;
              }
            }
            #Movendo para o proximo registro
            $RS->MoveNext();
          }
          echo "</table><br>";

    #----------------------------------------------------------------------
    #Paginação
        If($total_paginas>1){
            echo " <hr size='1' width='100%'>";
            echo "Página Nº:".$atual_pagina;
            echo "-> Total Páginas:".$total_paginas;

            If(($atual_pagina!=1)and($atual_pagina!=0)){
              echo " | <a class='linkg' href='?content=".$_GET['content']."&primeiro_registro=".$primeiro_registro."&ultimo_registro=".$ultimo_registro."&atual_pagina=".$atual_pagina."&cod_area_tematica=".$fws_['cod_area_tematica']."&detalhes=".$fws_['detalhes']."&acao=voltar'><- voltar</a> | ";
            }
            If($atual_pagina<=$total_paginas){
              echo " | <a class='linkg' href='?content=".$_GET['content']."&primeiro_registro=".$primeiro_registro."&ultimo_registro=".$ultimo_registro."&atual_pagina=".$atual_pagina."&cod_area_tematica=".$fws_['cod_area_tematica']."&detalhes=".$fws_['detalhes']."&acao=avancar'>avançar -></a> | ";
            }
            echo "<hr size='1' width='100%'>";
        }
    #----------------------------------------------------------------------
        }Else{
           echo "<table width='100%'><tr><td><br>".$FWS_Messagem_Nao_Encontrado.".<br><br></td></tr></table>";
        }
    }Else{
        echo "<table width='100%'><tr><td>";
        echo "<hr size='1' width='100%'>";
        echo $debug? __LINE__."<br>" : "";
        /*para mostrar a mensagem somente quando selecionar a area tematica
         * e quando estiver na perspectiva de seleção de trabalhos para avaliação
         */
        if($fws_['cod_area_tematica']!='-1' and $fws_['cod_area_tematica']){
            echo "Você Atingiu o número Máximo de trabalhos avaliados NESTA ÁREA TEMÁTICA.";
            echo "<br><br>";
            echo "Para avaliar OUTRA área temática selecione a busca acima";
        }else{
            echo "Selecione uma ÁREA TEMÁTICA";
        }
        echo "<br><br></td></tr></table>";
    }
//}
//Else{
//   echo "<table><tr><td>Selecione uma área temática.</td></tr></table>";
//}
?>
<hr size='1' width='100%'>
<a class='link' href='javascript:history.go(-1)'><b>voltar<b></a>
</body>
</html>
        </div>
    </div>
</div>
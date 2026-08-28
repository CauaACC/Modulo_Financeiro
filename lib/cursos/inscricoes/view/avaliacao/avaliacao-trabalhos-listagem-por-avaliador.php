<?php

use Psr\Log\NullLogger;

require_once(dirname(__FILE__)."/../../include/config.php");
$debug = false;
if(($_SERVER['REMOTE_ADDR']=='177.82.0.288')or($_SERVER['REMOTE_ADDR']=='189.90.70.566')){
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
<?php
If($FWS_Busca==true){
  require_once(dirname(__FILE__)."/../../view/avaliacao/avaliacao-trabalhos-busca.php");
}
#----------------------------------------------------------------------
#Conectar no Banco
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
## Consulta Trabalhos por Área Temática ou não##
//if($fws_['cod_area_tematica']='-1'){
//    $fws_['cod_area_tematica'] = '';
//}
   echo $debug? __LINE__." - Área Temática informada: ->".$fws_['cod_area_tematica']."<-<br>" : "";
    #----------------------------------------------------------------------
    #Mensagens de ERRO
    if($fws_['msg']){
        FWA_Mostrar_Mensagem($fws_['msg']);
    }
    #----------------------------------------------------------------------
    echo $debug? __LINE__."<br>" : "";


    #Lista Trabalhos a serem avaliados da Area Tematica a qual o convidado esta associado

    ## Minhas avaliações NÃO concluídas ##
    if ($fws_['detalhes'] == 2) {
        echo $debug? __LINE__."-avaliações NÃO concluídas-<br>" : "";

        $txtDetalhes_2              = "AVALIAÇÕES NÃO CONCLUÍDAS";
        $txtDetalhes_3              = "view/avaliacao/avaliacao-trabalhos-detalhes-nao-avaliados.php";
        $txtBotaoAcessarTrabalho     = "Avaliar";
        $FWS_Messagem_Nao_Encontrado = "Nenhuma trabalho encontrado de acordo com o filtro acima";

        /**@todo EXCLUINDO os que já estão em avaliação */
        $whereDetalhes_1 = "and(tt.cod_trabalho not in (select ta.cod_trabalho from tab_trabalhos_avaliacao ta "
                . "                                     WHERE ta.status='ATIVADA' and ta.avaliacao<>'Cancelada' and ta.cod_evento=".COD_EVENTO."))";
        /**@todo INCLUINOD SÓ os que não estão avaliação para o avaliador logado */
        if(COD_EVENTO==84){
            /**Pega todos, não só os indicados para avaliação */
        }else{
            $whereDetalhes_1 .= "and(tt.cod_trabalho in(select tha.cod_trabalho from tab_historico_avaliacao tha 
            WHERE tha.cod_avaliador=".$_SESSION['_cod_usuario']." 
            AND (tha.avaliacao='DEVOLVIDO' OR tha.avaliacao='SELECIONADO')
            AND tha.status='ATIVADA' AND tha.cod_evento=".COD_EVENTO.")
            )";            
        }



        $whereAreaTematica = "";
        if($fws_['cod_area_tematica']!='' and $fws_['cod_area_tematica']!='-1'){
          $whereAreaTematica = "and(tt.cod_area_tematica=".$fws_['cod_area_tematica'].")
           )
          ";
        }

        // if($fws_['flg_enviado_avaliacao']!='' and $fws_['flg_enviado_avaliacao']!='-1'){
            $whereEnvioAvaliacao = " and(tt.flg_enviado_avaliacao='1' or tt.flg_enviado_avaliacao='0') ";
        // }
        if(COD_EVENTO==103){
            $whereEnvioAvaliacao = "";
            $whereAreaTematica = "";
        }

        #$sqlAposOrderBy = " Limit 0-50 ";

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
                 FROM tab_trabalhos tt
                 inner join tab_inscritos ti on tt.cod_inscrito=ti.cod_inscrito
                 left join tab_opcoes_areas_tematicas toa on tt.cod_area_tematica=toa.cod_area_tematica
                 WHERE
                      (tt.cod_evento       =".COD_EVENTO.")
                   ".$whereEnvioAvaliacao."
                   and(tt.status='ATIVADA')
                   ".$whereAreaTematica."
                   
                   and(ti.status          = 'ATIVADA')
                   ".$whereDetalhes_1."
                 ORDER BY toa.titulo ASC
                  ".$sqlAposOrderBy."
                 ";

  } else if ($fws_['detalhes'] == 1) {
      echo $debug? __LINE__."<br>" : "";

      $txtDetalhes_2              = "AVALIADOS POR VOCÊ";
      $txtDetalhes_3              = "view/avaliacao/avaliacao-trabalhos-detalhes-avaliados.php";
      #$sqlAposOrderBy             = "";
      $txtBotaoAcessarTrabalho     = "Consultar";
      $FWS_Messagem_Nao_Encontrado = "Nenhum trabalho encontrado de acordo com o filtro acima";


      $whereDetalhes_1 = "and(tt.cod_trabalho     in (SELECT ta.cod_trabalho FROM tab_trabalhos_avaliacao ta "
                                                        . " WHERE ta.cod_avaliador=".$_SESSION['_cod_usuario']
                                                        . " AND ta.cod_area_tematica=".$fws_['cod_area_tematica']
                                                        . " AND ta.status='ATIVADA' and ta.cod_evento = ".COD_EVENTO
                                                    . ")"
                          . ")";

      $fromTabAvaliacao = ", tab_trabalhos_avaliacao tta ";
      $whereTabAvaliacao = " and tta.cod_trabalho=tt.cod_trabalho";

      $whereAreaTematica = "";
      if($fws_['cod_area_tematica']!='' and $fws_['cod_area_tematica']!='-1'){
        $whereAreaTematica = "and(tt.cod_area_tematica=".$fws_['cod_area_tematica'].")";
      }

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
                                               WHERE ta.cod_avaliador=".$_SESSION['_cod_usuario']." and ta.status='ATIVADA' AND ta.cod_evento = ".COD_EVENTO."
                                              )
                      )
                   and tta.status='ATIVADA'
                 ORDER BY toa.titulo ASC
                  ".$sqlAposOrderBy."
                 ";

    } else {

        echo $debug? __LINE__."<br>" : "";
        $usarPaginacao = false;
        $txtDetalhes_2 = "SEM AVALIADOR";
        $txtDetalhes_3 = "view/avaliacao/avaliacao-trabalhos-detalhes-nao-avaliados.php";
        // $txtBotaoAcessarTrabalho = "Avaliar";
        $txtBotaoAcessarTrabalho = "Selecionar para avaliar";

        /* @todo para ser avaliado não pode estar na tabela de trabalhos_avaliacao e nem na histórico para não ter avaliador e com flg_enviado_avaliacao=1 em tab_trabalhos*/
        $whereDetalhes_1  = "and(tt.cod_trabalho not in (select ta.cod_trabalho from tab_trabalhos_avaliacao ta WHERE ta.status='ATIVADA' and ta.cod_evento=".COD_EVENTO." and ta.avaliacao<>'Cancelada')) ";
        $whereDetalhes_1 .= "AND(tt.cod_trabalho NOT IN (SELECT tha.cod_trabalho FROM tab_historico_avaliacao tha "
                                                        . "WHERE tha.cod_avaliador<>'' and tha.status='ATIVADA'
                                                         and tha.cod_evento=".COD_EVENTO
                                                        . ")"
                             . ")";

//          $FWS_Messagem_Nao_Encontrado = "Nenhuma trabalho disponível para AVALIAÇÃO até o momento.";
        $FWS_Messagem_Nao_Encontrado = "Nenhuma trabalho encontrado de acordo com o filtro acima";
        #$sqlAposOrderBy = " Limit 0-50 ";
        
        $whereAreaTematica = "";
        if($fws_['cod_area_tematica']!='' and $fws_['cod_area_tematica']!='-1'){
            $whereAreaTematica = "(tt.cod_area_tematica=".$fws_['cod_area_tematica'].")and";
        }
        
        // $var_sql = "
        //  SELECT
        //     toa.titulo
        //    ,ti.cod_inscrito
        //    ,ti.nome_inscrito
        //    ,tt.cod_trabalho
        //    ,tt.autor_apresentador_nome
        //    ,tt.autor_apresentador_e_mail
        //    ,tt.nome_co_autor_1
        //    ,tt.nome_co_autor_2
        //    ,tt.nome_co_autor_3
        //    ,tt.nome_co_autor_4
        //    ,tt.nome_co_autor_5
        //    ,tt.nome_co_autor_6
        //    ,tt.instituicao_trabalho
        //    ,tt.titulo_trabalho
        //    ,tt.palavras_chave
        //    ,tt.resumo_trabalho
        //    ,tt.flg_enviado_avaliacao
        //    ,''
        //  FROM tab_trabalhos tt, tab_inscritos ti, tab_opcoes_areas_tematicas toa
        //  WHERE
        //     (tt.cod_evento       =".COD_EVENTO.")
        //     and(tt.flg_enviado_avaliacao='1')
        //     and(tt.status='ATIVADA')
        //     ".$whereAreaTematica."
        //     and(tt.cod_area_tematica=toa.cod_area_tematica)
        //     and(tt.cod_inscrito     =ti.cod_inscrito)
        //     and(ti.status          = 'ATIVADA')
        //     ".$whereDetalhes_1."
        //  ORDER BY toa.titulo ASC
        //     ".$sqlAposOrderBy."
        //  ";

        #Definindo comando SQL
        $FWS_Detalhes_1  = "and(tt.cod_trabalho not in (select ta.cod_trabalho from tab_trabalhos_avaliacao ta where ta.status='ATIVADA' and ta.avaliacao<>'Cancelada' and ta.cod_evento = ".COD_EVENTO.")) ";
        $FWS_Detalhes_1 .= "AND(tt.cod_trabalho NOT IN (SELECT ta.cod_trabalho FROM tab_historico_avaliacao ta WHERE ta.cod_avaliador<>'' and ta.status='ATIVADA' and ta.cod_evento = ".COD_EVENTO."))";
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
        FROM tab_trabalhos tt
        inner join tab_inscritos ti on tt.cod_inscrito=ti.cod_inscrito 
        left join tab_opcoes_areas_tematicas toa on tt.cod_area_tematica=toa.cod_area_tematica 
        WHERE
            (tt.cod_evento       =".COD_EVENTO.")
            ".$whereEnviadoAvaliacao."
            and(tt.status='ATIVADA')
            and(ti.status='ATIVADA')
            ".$FWS_Detalhes_1."
            ORDER BY toa.titulo ASC
            ".$FWS_Detalhes_4."
            ";
    }

    echo $debug? __LINE__."<br>" : "";
    echo $debug? "<pre>".$var_sql."</pre><hr>" : "";
    
    $RS = $Conexao_db->Execute($var_sql);

    if ( !$RS->EOF ) {

        $whereAreaTematica = "";
//        if($fws_['cod_area_tematica']!='' and $fws_['cod_area_tematica']!='-1'){
//            $whereAreaTematica = "(tt.cod_area_tematica=".$fws_['cod_area_tematica'].")and";
//        }
        if ($usarPaginacao){ 
            $var_sql = "
                SELECT Count(tt.cod_trabalho) as Total_Trabalhos_Enviados
                FROM tab_trabalhos tt, tab_inscritos ti
                WHERE (tt.cod_evento       = ".COD_EVENTO.")and
                ".$whereAreaTematica."
                (tt.cod_inscrito     = ti.cod_inscrito)and
                (ti.status          <> 'EXCLUÍDA')
                ".$whereDetalhes_1."
            ";
            echo $debug? __LINE__."<br>" : "";
            echo $debug? $var_sql."<hr>" : "";
            $RSTotal = $Conexao_db->Execute($var_sql);

            if (!$RSTotal->EOF) {
                $total_trabalhos_enviados = $RSTotal->fields[0];
            }
        }

        echo "
        <table border='0' width='100%' colspan='2' cellspacing='3'>
        <tr><td colspan='3'>
        <!--Segue abaixo lista de titulos dos: ".$total_trabalhos_enviados." trabalhos <b>".$txtDetalhes_2."</b>:-->
        Trabalhos <b>".$txtDetalhes_2."</b>:
        <br>&nbsp;
        </td></tr>
        ";
        $titulo_ant = "";
        $ii=0;
        while (!$RS->EOF) {
            $ii++;
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

            // if (($registro_ini < $registro_atual)and ( $registro_atual <= $registro_fim)) {
            if (!$usarPaginacao || (($registro_ini < $registro_atual) and ($registro_atual <= $registro_fim))) {
                @$i = $i+1;

                if ($flg_enviado_avaliacao == 0) {
                    $txtPBotaoAcessarTrabalho = "Aguardando retorno do autor";
                } else {
                    $txtPBotaoAcessarTrabalho = $txtBotaoAcessarTrabalho;
                }

                if ($titulo != $titulo_ant) {
                    echo "<tr><td colspan='3'>{$titulo}<br>&nbsp;</td></tr>";
                }
                $titulo_ant = $titulo;

                if ($avaliacao == 'APROVADO') {
                    $txtAvaliacao = "<font color=green>Apr.</font> ";
                } else if ($avaliacao == 'REPROVADO') {
                    $txtAvaliacao = "<font color=red>Rep.</font> ";
                } else {
                    $txtAvaliacao = "";
                }
                echo "<tr><td colspan='3'>";
                echo "<ul><li>";

                    echo "<a title='".$txtPBotaoAcessarTrabalho."' class='link' href='index.php?content=".$txtDetalhes_3."&cod_area_tematica=".$fws_['cod_area_tematica']."&cod_area_tematica=".$fws_['cod_area_tematica']."&cod_trabalho=".$cod_trabalho."&detalhes=".$fws_['detalhes']."'>
                        <input type='button' value='".$txtPBotaoAcessarTrabalho."' class='btn btn-xs btn-primary'></a>";
                    if($_SERVER['REMOTE_ADDR']=='177.82.0.288'){ 
                        echo "<a title='Declinar avaliação' class='link' href='index.php?content=GerenciarAvaliacaoAC&acao=recusar&cod_trabalho=".$cod_trabalho."'>
                        <input type='button' value='Declinar avaliação' class='btn btn-xs btn-danger'></a>";
                    }
                    echo " - ".$txtAvaliacao.$titulo_trabalho;
                    echo "</li></ul>";

                echo "</td></tr>";
                if ($i == 1) {
                    $primeiro_registro=$registro_atual;
                }
                if ($i == $registros_por_pagina) {
                    $ultimo_registro=$registro_atual;
                }
            }
            #Movendo para o proximo registro
            $RS->MoveNext();
        }
        if (!$total_trabalhos_enviados) {
           $total_trabalhos_enviados = $ii;
        }
        echo "</table><br>";
        echo "Total de trabalhos: ".$total_trabalhos_enviados;
#----------------------------------------------------------------------
        #Paginação
        if ($usarPaginacao){ 
            if ($total_paginas > 1) {
                echo " <hr size='1' width='100%'>";
                echo "Página Nº:".$atual_pagina;
                echo "-> Total Páginas:".$total_paginas;

                if (($atual_pagina != 1)and ( $atual_pagina != 0)) {
                    echo " | <a class='linkg' href='?content=".$_GET['content']."&primeiro_registro=".$primeiro_registro."&ultimo_registro=".$ultimo_registro."&atual_pagina=".$atual_pagina."&cod_area_tematica=".$fws_['cod_area_tematica']."&detalhes=".$fws_['detalhes']."&acao=voltar'><- voltar</a> | ";
                }
                if ($atual_pagina <= $total_paginas) {
                    echo " | <a class='linkg' href='?content=".$_GET['content']."&primeiro_registro=".$primeiro_registro."&ultimo_registro=".$ultimo_registro."&atual_pagina=".$atual_pagina."&cod_area_tematica=".$fws_['cod_area_tematica']."&detalhes=".$fws_['detalhes']."&acao=avancar'>avançar -></a> | ";
                }
                echo "<hr size='1' width='100%'>";
            }
        }
#----------------------------------------------------------------------
    } else {
        echo "<table width='100%'><tr><td><br>".$FWS_Messagem_Nao_Encontrado.".<br><br></td></tr></table>";
    }
    echo $debug? __LINE__."<br>" : "";
?>
<hr size='1' width='100%'>
<a class='link' href='javascript:history.go(-1)'><b>voltar<b></a>
</body>
</html>
        </div>
    </div>
</div>
<?php
require_once(dirname(__FILE__)."/../../include/config.php");
#require_once(dirname(__FILE__)."/../../include/inscricao_request_variaveis.php");
#Fazendo conexão com o banco
$Conexao_db = FWA_Conexao("OpcTec");

$debug = false;
?>
<h3>Área do Inscrito</h3>  
<div class="panel-group">
<!--    Atenção:
    <br> - é permitida a seleção de apenas um atividade/evento;
    <br> - para efeito de seleção, consideramos apenas o ultimo atividade/evento clicado;-->
    <?php
    try {
        require_once 'inscricao_congresso_cabecalho.php';
?>
<div class="panel panel-default">
    <div class="panel-heading"><h4>
      <?php if ($objEvConf->getEventoParaleloDescricao()) { ?>
          <?php echo $objEvConf->getEventoParaleloDescricao() ?>
      <?php }else{ ?>
          Selecione Simpósios e/ou Evento Paralelo
      <?php } ?>
    </h4></div>
    <div class="panel-body">
      <?php
      if($objEvConf->getCodEvento()==70){
        echo "Atenção: Adicione uma atividade por vez.";
      }
      ?>
        <!--
        <br>
        <a href="index.php?content=view/inscricao/inscricao_congresso_cadastro_dados_pessoais.php&cod_oficina="
           class="btn btn-sx btn-primary" role="button">Avançar sem selecionar Oficina</a>
        <br><br>
        -->
        
    <FORM method='POST' name='form'  action='<?php echo WB_URL_COMMAND?>' onsubmit='return criticar()'>
    <input type="hidden" name="c" value="GerenciarInscricao">
    <input type="hidden" name="m" value="salvarAtividadeParalela">
    <input type="hidden" name="cod_inscrito" value="<?php echo $cod_inscrito?>">
    
    <button class='btn btn-xs btn-primary' type="submit">Avançar</button>
    <button class='btn btn-xs btn-primary' type="button" id='btn-limpar'>Limpar</button>
    <a href="index.php?content=view/inscricao/inscricao_congresso_ok.php"
        class="btn btn-xs btn-primary" role="button">Voltar</a>    
        <br><br>
        <?php
        if($fws_['msg']){
            FWA_Mostrar_Mensagem($fws_['msg']);
        }
        ?>
    <table border='0' width='100%' CELLSPACING='6' CELLPADDING='6' style="font-size: 13px;">
    <?php

      $var_sql = "
         SELECT too.ordem,
          too.status,
          too.titulo,
          too.nome_ministrante,
          too.e_mail_ministrante,
          too.fone_ministrante,
          too.local,
          too.carga_horaria,
          too.numero_vagas,
          too.horario_ini,
          too.horario_fim,
          too.data_ini,
          too.data_fim,
          too.palavras_chave,
          too.publico_alvo,
          too.objetivo,
          too.pre_requisito,
          too.ementa,
          too.descricao,
          too.observacao,
          too.cod_oficina,
          (SELECT Count(tlio.cod_oficina) as Total_Inscritos_Oficinas
           FROM tab_lig_inscritos_oficinas tlio, tab_inscritos ti
           WHERE (tlio.cod_evento = ".COD_EVENTO.")and (tlio.cod_oficina = too.cod_oficina) and (tlio.cod_inscrito=ti.cod_inscrito)and(ti.status='ATIVADA') GROUP BY tlio.cod_oficina)
          ,numero_oficial, too.tipo
          ,(SELECT Count(tlio.cod_oficina) as ja_inscrito_atividade
           FROM tab_lig_inscritos_oficinas tlio, tab_inscritos ti
           WHERE (tlio.cod_evento = ".COD_EVENTO.")and (tlio.cod_oficina = too.cod_oficina) and (tlio.cod_inscrito=ti.cod_inscrito)and(ti.status='ATIVADA' and ti.cod_inscrito=".$cod_inscrito.") GROUP BY tlio.cod_oficina)
          ,(SELECT COUNT(tlio.cod_inscrito) as total_inscricoes_atividade_tipo
          FROM tab_lig_inscritos_oficinas tlio
          JOIN tab_opcoes_oficinas toof ON tlio.cod_oficina = toof.cod_oficina
          WHERE tlio.cod_evento = ".COD_EVENTO."
            AND tlio.cod_inscrito = ".$cod_inscrito." and toof.tipo=too.tipo
          GROUP BY toof.tipo)
         FROM tab_opcoes_oficinas too
         WHERE
         (too.cod_evento  = ".COD_EVENTO.")
         and (too.status  = 'ATIVADA')
         and (too.tipo  <> 'Minicurso' or (too.cod_oficina in (".$objEvConf->getEventoParaleloTipo().")))
         ORDER BY ORDEM
        ";
      //echo $var_sql;

    $RS = $Conexao_db->Execute($var_sql);

    If (!$RS->EOF) {
        
    $dataHoraAnt = "";
    if($objEvConf->getCodEvento()==76){
      $total_atividade_permitido[strtolower(removerAcentos('Minicurso'))] = 1;
      $total_atividade_permitido[strtolower(removerAcentos('Visita Técnica'))] = 0;
    }else{
      $total_atividade_permitido[strtolower(removerAcentos('Minicurso'))] = 0;
      $total_atividade_permitido[strtolower(removerAcentos('Visita Técnica'))] = 0;
    }
      While (!$RS->EOF) {
          

        $ORDEM              = $RS->fields[0];
        $STATUS             = $RS->fields[1];
        $TITULO             = $RS->fields[2];
        $NOME_MINISTRANTE   = $RS->fields[3];
        $E_MAIL_MINISTRANTE = $RS->fields[4];
        $FONE_MINISTRANTE   = $RS->fields[5];
        $LOCAL              = $RS->fields[6];
        $CARGA_HORARIA      = $RS->fields[7];
        $NUMERO_VAGAS       = $RS->fields[8];
        $HORARIO_INI        = $RS->fields[9];;
        $HORARIO_FIM        = $RS->fields[10];
        $DATA_INI           = $RS->fields[11];
        $DATA_FIM           = $RS->fields[12];
        $PALAVRAS_CHAVE     = $RS->fields[13];
        $PUBLICO_ALVO       = $RS->fields[14];
        $OBJETIVO           = $RS->fields[15];
        $PRE_REQUISITO      = $RS->fields[16];
        $EMENTA             = $RS->fields[17];
        $DESCRICAO          = $RS->fields[18];
        $OBSERVACAO         = $RS->fields[19];
        $COD_OFICINA        = $RS->fields[20];
        $TOTAL_INSCRITOS    = $RS->fields[21];
        $NUMERO_OFICIAL     = $RS->fields[22];
        $TIPO               = $RS->fields[23];
        $ja_inscrito_atividade = $RS->fields[24];
        $total_inscricoes_atividade_tipo = $RS->fields[25];

        #Definindo tabela com resultado
        /*
        If($_SESSION['cod_oficina']==$COD_OFICINA){
          $FWS_Checked="checked";
        }Else{
          $FWS_Checked="";
        }
        */

        @$i = 1;
        #--------------------------------------
        #Contar Vagas Ocupadas em um curso,
        #Removendo inscrições com um unico cpf
        $var_sql_b = "
                      SELECT tlio.cod_oficina
                      FROM tab_lig_inscritos_oficinas tlio, tab_inscritos ti
                      WHERE
                      (tlio.cod_evento = ".COD_EVENTO.")and
                      (tlio.cod_oficina = ".$COD_OFICINA.") and (tlio.cod_inscrito=ti.cod_inscrito) and (ti.status='ATIVADA') GROUP BY tlio.cod_oficina, ti.cpf
                   ";

        $RS_b = $Conexao_db->Execute($var_sql_b);
        $ii = 0;
        
        While (!$RS_b->EOF) {
            
           $ii = $ii + 1;
           $RS_b->MoveNext();
        }
        
        $vagas_net_ocupadas = $ii;
        $TOTAL_INSCRITOS = $vagas_net_ocupadas;

        $VAGAS_DISPONIVEIS = $NUMERO_VAGAS-$TOTAL_INSCRITOS;
        #--------------------------------------

        #ECHO $TOTAL_INSCRITOS."<BR>";
        #ECHO $NUMERO_VAGAS."<BR>";
        #ECHO $VAGAS_DISPONIVEIS."<BR><br>";

        If($NUMERO_VAGAS<=0){
            
          $STATUS_VAGAS     = "<br>(Nenhuma vaga foi ofertada para este atividade/evento)";
          $SELECIONA_OFICINA="";
        }Else{
            
          If($VAGAS_DISPONIVEIS<=0){
              
             $STATUS_VAGAS     = "<br><font color=red>(Todas as vagas para este atividade/evento foram preenchidas)</font>";
             $SELECIONA_OFICINA="";
          }Else{
            if($ja_inscrito_atividade>=1){
              $STATUS_VAGAS     = "<br><font color=green>(Já selecionada)</font>";
              $SELECIONA_OFICINA="";
            }else if($total_inscricoes_atividade_tipo>=$total_atividade_permitido[strtolower(removerAcentos($TIPO))]){
              $STATUS_VAGAS     = "<br><font color=red>Sua inscrição atingiu o máximo de ".$TIPO." permitido: ".$total_atividade_permitido[removerAcentos($TIPO)]."</font>";
              $SELECIONA_OFICINA="";
            }else {
              $SELECIONA_OFICINA = "<INPUT TYPE='radio' NAME='cod_oficina' VALUE='".$COD_OFICINA."' ".@$FWS_Checked.">";
              $STATUS_VAGAS      = "";
            } 
          }
        }

        If (@$bgcolorant=="#F4F4F4"){
           $bgcolor="#FFFFFF";
        }Else{
           $bgcolor="#F4F4F4";
        }

        $bgcolorant=$bgcolor;
        if($dataHoraAnt != $DATA_INI.$HORARIO_INI){
            echo "<tr valign='middle' bgcolor='{$bgcolor}'>"
            . "<td width='100%' colspan='2' style='padding: 4px 4px 6px 4px;'>"
            . "<h4>".formatDateView($DATA_INI)." - "
            . "{$HORARIO_INI}</h4>"
            . "</td></tr>";
        }
        $dataHoraAnt = $DATA_INI.$HORARIO_INI;
         
        echo "<tr valign='middle' bgcolor='{$bgcolor}'>"
        . "<td width='5%'  style='padding: 4px 4px 6px 4px;text-align:center;'>{$SELECIONA_OFICINA}</td>"
        . "<td width='90%' style='padding: 4px 4px 6px 4px;'>";
        if($TITULO != "Não desejo participar de atividade/evento"){
            echo "{$TIPO} - ";
        }
        echo "{$TITULO}"
        . "{$STATUS_VAGAS}"
        . "</td></tr>";

        #Movendo para o proximo registro
        $RS->MoveNext();
      }
    }else{
        echo "<tr><td>Nenhuma atividade/evento paralelo cadastrado</tr></td>";
    }
?>
    </td>
  </tr>
</table>
<!--<div class='btn-group'>-->
    <br><br>
    <INPUT TYPE='hidden' VALUE='Inserir' NAME='acao'>
    <button class='btn btn-xs btn-primary' type="submit">Avançar</button>
    <button class='btn btn-xs btn-primary' type="button" id='btn-limpar'>Limpar</button>
    <a href="index.php?content=view/inscricao/inscricao_congresso_ok.php"
       class="btn btn-xs btn-primary" role="button">Voltar</a>

<!--    <a href="index.php?content=view/inscricao/inscricao_trabalhos_inicio.php"
      class="btn btn-sx btn-primary" role="button">Voltar</a>-->
<!--</div>-->
</form>
       <?php
       } catch (Exception $exc) {
           $_SESSION['MSG']=$exc->getMessage();
           echo "<a href='" . $url . "'>" . FWA_Mostrar_Mensagem($exc->getMessage()) . "</a>";
           if (!$debug) {
               echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $url . "&codRetorno=" . $codRetorno . "\"></HTML>";
           }
       }
       ?>
        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-body">
            <br>
        </div>
    </div>
</div>

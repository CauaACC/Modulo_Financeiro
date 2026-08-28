<?php
require_once(dirname(__FILE__)."/../../include/config.php");
#require_once(dirname(__FILE__)."/../../include/inscricao_request_variaveis.php");
#Fazendo conexão com o banco
$Conexao_db = FWA_Conexao("OpcTec");

$debug = false;

require_once 'inscricao_congresso_tit_area_inscrito.php';
?>
<div class="panel-group">
<?php
try {
    if($fws_['cpf']==''){
        $URL = "http://" . FWA_Dominio() . "/" . FWA_URL_Prefixo() . "";
        $MSG = "Para se inscrever é necessário informar CPF.";
        FWA_Mostrar_Mensagem($MSG);
        $_SESSION['MSG']=$MSG;
        echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $URL . "\">";
        exit();
    }
//        require_once 'inscricao_congresso_cabecalho.php';
?>
<div class="panel panel-default">
    <?php
    //if ($fws_['op_selecionar_oficina']) and ($objInscrito->getObjPerfil()->getVerificarSelecionarAtividade())) {
    try {
    $objPerfil = new Perfil();
    if($fws_['cod_perfil']==''){
        echo "<span style='margin:15px;font-size:16px;color:red'>Para se inscrever é necessário selecionar uma opção, clique em voltar</span>";
    }
    $objPerfil->setCodPerfil($fws_['cod_perfil']);
    $objPerfil->carregarPerfilById();
    if(CarregarOptionsAC::descTipoInscricao($fws_, $debug)!='Outros'){
        echo "<h3>Perfil Selecionado: ". CarregarOptionsAC::descTipoInscricao($fws_, $debug)."</h3>";
    }
    echo "<h3>".$objPerfil->getAbrevTipo().": ".$objPerfil->getTitulo()."</h3>";
    echo "<div style='font-size:17px;margin-left:40px'>".$objPerfil->getInvestimento()."</div>";

    } catch (Exception $exc) {
        echo $exc->getMessage();
    }
    ?>
    <?php if (COD_EVENTO==23) { ?>
    <div class="panel-heading"><h4>Selecionar Atividade</h4>
        <span style='font-size:14px;color:red;font-weight: bold;'>
        Atenção:</span>
        <br> - é permitida mais de um minicurso;
        <br> - associados em dia, terão 20% de desconto no valor da matricula de cada minicursos
        <br> - ASSOCIE-SE durante a matricula, valor da Associação: R$ 65,00
        <br>
        <br>Se você ainda não é um associado, não perca a oportunidade de apoiar uma instituição com 35 anos de história de apoio à formação de recursos humanos para saúde e construção do SUS. 
        <br>
        <br>Além de valores diferenciados para a Escola de Inverno, em outubro de 2023 será realizado o 2º Simpósio Paranaense de Saúde Pública e Coletiva que também ofertará condições diferenciadas aos membros da instituição, assim como em outras atividades promovidas.
        <br>&nbsp;
    </div>
    <?php }else if (COD_EVENTO==4) { ?>
    <div class="panel-heading"><h4>Selecionar Atividade</h4>
        Atenção:
        <br> - é permitida a seleção de apenas um minicurso;
        <br> - para efeito de seleção, consideramos apenas o ultimo minicurso clicado;
        <br>&nbsp;
    </div>
    <?php }else if (COD_EVENTO==5) { ?>
    <div class="panel-heading"><h4>Selecionar Atividades</h4>
        Atenção:
        <br> - é permitida a seleção de apenas um dos dias de Plenária;
        <br> - ou você poderá escolher se inscrever para ambos os dias.;
        <br>&nbsp;
    </div>
    <?php }else if ($objEvConf->getMsgOficinaAtivar()) { ?>
    <div class="panel-heading">
        <div class="panel-heading">
        <?php echo $objEvConf->getMsgOficinaTexto() ?>              
        <h4>Selecionar <?php echo $objEvConf->getAtividadeTipo() ?></h4>
        <?php if ($objPerfil->getVerificarSelecionarAtividade()) { ?>
        <h4>permite selecionar até <?php echo $objPerfil->getQtdAtividadePermite()?> opções abaixo:</h4>
      
        <?php }else { ?>
        <h4>Este perfil NÃO permite seleção de <?php echo $objEvConf->getAtividadeTipo()? $objEvConf->getAtividadeTipo() : "opções"; ?>:</h4>
        <?php } ?>
    </div>
    <?php }else { ?>
        <?php if(defined('OP_AL') && OP_AL){ ?>
        <div class="panel-heading">
            <?php if ($objPerfil->getVerificarSelecionarAtividade()) { ?>
            <h4>permite selecionar até <?php echo $objPerfil->getQtdAtividadePermite()?> opções:</h4>
            <h4>Selecionar Opções</h4>
            <?php }else { ?>
            <h4>Este perfil NÃO permite seleção de <?php echo $objEvConf->getAtividadeTipo()? $objEvConf->getAtividadeTipo() : "opções"; ?>:</h4>
            <?php } ?>
        </div>
        <?php } ?>
    <?php } ?>

    <div class="panel-body">   
        <form method="POST" name="form"
        <?php
        // $ips_permitidos = ['189.90.70.56-','200.203.142.25-', '201.66.95.230-', '177.82.0.28-','45.230.27.11-'];
        // if (in_array($_SERVER['REMOTE_ADDR'], $ips_permitidos)) {
        if ($objPerfil->getCodEvento()==70) {
        ?>
            action="index.php?content=view/inscricao/inscricao_congresso_atividade_paralela_seleciona.php"
        <?php }else{ ?>
            action="index.php?content=view/inscricao/inscricao_congresso_cadastro_dados_pessoais.php"
        <?php } ?>
        onsubmit="return criticar()">
        <input type="hidden" name="e_mail"     value="<?php echo $fws_['e_mail']?>">
        <input type="hidden" name="cpf"        value="<?php echo $fws_['cpf']?>">
        <input type="hidden" name="cod_perfil" value="<?php echo $fws_['cod_perfil']?>">
        <input type="hidden" name="n_ativ_selecionados" value="0">
        <input type="hidden" name="tipo_inscricao" value="<?php echo $fws_['tipo_inscricao']?>">
    <div style="margin:20px 15px 15px; padding-top:15px; border-top:1px solid #eee; display:flex; gap:8px; flex-wrap:wrap;">
        <?php if($fws_['cod_perfil']!=''){ ?>
        <button class='btn btn-primary modalBtnSalvar' type="submit" onclick="validarFormulario(event)">Avançar</button>
        <?php } ?>
        <button class='btn btn-default modalBtnCancelar' type="button" id='btn-limpar'>Limpar</button>
        <?php if ($_SESSION['_cod_inscrito']){ ?>
            <a href="index.php?content=view/inscricao/inscricao_congresso_ok.php"
              class="btn btn-default modalBtnCancelar" role="button">Voltar</a>
        <?php }else{ ?>
            <a href="index.php?content=view/inscricao/inscricao_congresso_perfil_seleciona.php"
              class="btn btn-default modalBtnCancelar" role="button">Voltar</a>
        <?php } ?>
    </div>
    <?php if ($objPerfil->getVerificarSelecionarAtividade()) { ?>
    <table border='0' width='100%' CELLSPACING='6' CELLPADDING='6' style="font-size: 13px;">
    <?php

    $innerJoin = "";
    if(in_array($objEv->getCodEvento(), array(35,52,72,79,90,105,119))){
        $orderBy = ' data_inicio, ordem, cod_oficina, titulo ';
    }else if($objEv->getCodEvento()==46){
        $orderBy = ' data_inicio, ordem, cod_oficina, titulo ';
        $innerJoin = " inner join tab_lig_oficinas_perfis liop on liop.cod_oficina=too.cod_oficina and liop.cod_perfil=".$objPerfil->getCodPerfil()." ";
    }else if($objEv->getCodEvento()==88){
        $orderBy = ' data_inicio, ordem, cod_oficina, titulo ';
        $innerJoin = " inner join tab_lig_oficinas_perfis liop on liop.cod_oficina=too.cod_oficina and liop.cod_perfil=".$objPerfil->getCodPerfil()." ";
    }else{
        $orderBy = ' too.ordem, too.cod_oficina, too.titulo ';
    }
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
          too.abrev_tipo,
          (SELECT Count(tlio.cod_oficina) as Total_Inscritos_Oficinas
             FROM tab_lig_inscritos_oficinas tlio, tab_inscritos ti
           WHERE (tlio.cod_evento = ".COD_EVENTO.")and (tlio.cod_oficina = too.cod_oficina) and (tlio.cod_inscrito=ti.cod_inscrito)and(ti.status='ATIVADA') GROUP BY tlio.cod_oficina)
          ,too.numero_oficial, too.status_inscricao, too.inscrito_apto
          ,too.investimento
          ,too.data_inicio
          ,too.data_fim_inscricao
          ,too.local
    ,too.numero_vagas - COALESCE(
        (SELECT Count(tlio.cod_oficina)
         FROM tab_lig_inscritos_oficinas tlio, tab_inscritos ti
         WHERE tlio.cod_evento = ".COD_EVENTO."
         AND tlio.cod_oficina = too.cod_oficina
         AND tlio.cod_inscrito=ti.cod_inscrito
         AND ti.status='ATIVADA'
         GROUP BY tlio.cod_oficina), 
    0) AS VAGAS_DISPONIVEIS
    ,too.vagas_universal_01
    ,too.vagas_universal_02
         FROM tab_opcoes_oficinas too
         $innerJoin
         WHERE
         (too.cod_evento  = ".COD_EVENTO.")
         and (too.status  = 'ATIVADA')
         and (too.receber_inscricao  = 1)
         and (too.status_inscricao  = 'ATIVADA')
         ORDER BY ".$orderBy;
      
    // echo $var_sql;
    $RS = $Conexao_db->Execute($var_sql);
    $aoMenosUma = false;
    if(COD_EVENTO==70){
        $aoMenosUma = true;
    }
    
    If (!$RS->EOF) {
        $dataHoraAnt = "";
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
        $ABREV_TIPO         = $RS->fields[21];
        $TOTAL_INSCRITOS    = $RS->fields[22];
        $NUMERO_OFICIAL     = $RS->fields[23];
        $STATUS_INSCRICAO   = $RS->fields[24];
        $inscrito_apto = $RS->fields['inscrito_apto'];
        $INVESTIMENTO       = $RS->fields[26];
        $DATA_INI           = $RS->fields[27];
        $data_fim_inscricao = $RS->fields['data_fim_inscricao'];
        $VAGAS_DISPONIVEIS = $RS->fields['VAGAS_DISPONIVEIS'];
        $LOCAL = $RS->fields['LOCAL'];
//        $DATA_INI           = $RS->fields[11];
        #Definindo tabela com resultado
        /*
        If($_SESSION['cod_oficina']==$COD_OFICINA){
          $FWS_Checked="checked";
        }Else{
          $FWS_Checked="";
        }
        */
        $atividadeDataFimLiberado = 0;
        //echo formatDateTimeSemSeparador($data_fim_inscricao).'==\'\' or '.Date('YmdHmi').'<='.formatDateTimeSemSeparador($data_fim_inscricao);
        //if( $VAGAS_DISPONIVEIS > 0 or $objEvConf->getOcultarOpcaoSemVaga()){
        if((formatDateTimeSemSeparador($data_fim_inscricao)=='' or Date('YmdHmi')<=formatDateTimeSemSeparador($data_fim_inscricao))or($atividadeDataFimLiberado == 1)){
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
            $inscrito_apto_liberado = true;
            #Verificando se possui lista de aptos
            if ($inscrito_apto == true) {
                /*@todo - verificar se cpf esta na lista de aptos para a atividade*/
                try {
                    $objOf = new Oficina();
                    $objOf->setCodOficina($COD_OFICINA);
                    $objOf->setCpf($fws_['cpf']);
                    if ($objOf->verificandoInscritoApto() == true) {
                        $inscrito_apto_liberado = true;
                    } else {
                        $inscrito_apto_liberado = false;
                    }
                } catch (Exception $exc) {
                    $inscrito_apto_liberado = false;
                    //echo $exc->getTraceAsString();
                }
            }
            $vagas_net_ocupadas = $ii;
            $TOTAL_INSCRITOS = $vagas_net_ocupadas;

            $VAGAS_DISPONIVEIS = $NUMERO_VAGAS-$TOTAL_INSCRITOS;
            #--------------------------------------
            if($_SERVER['REMOTE_ADDR']=='189.90.70.2299'){
                ECHO $TOTAL_INSCRITOS."<BR>";
                ECHO $NUMERO_VAGAS."<BR>";
                ECHO $VAGAS_DISPONIVEIS."<BR><br>";
            }
            /*tratando vagar por perfil*/
            if($objPerfil->getCodPerfil()=='372'){
                if(in_array($COD_OFICINA, array(994,986))){
                    $NUMERO_VAGAS=0;
                }
            }
            if($objPerfil->getCodPerfil()=='373'){
                if(in_array($COD_OFICINA, array(988,999,981,982,985,1013,1001))){
                    $NUMERO_VAGAS=0;
                }
            }
            /*fim tratando vagar por perfil*/

            If($NUMERO_VAGAS<=0){
              $STATUS_VAGAS     = "<br>(Nenhuma vaga foi ofertada)";
              $SELECIONA_OFICINA="";
            }Else{
                if($inscrito_apto_liberado){
                    If($VAGAS_DISPONIVEIS<=0){
                       $STATUS_VAGAS     = "<br><font color=red>(Todas as vagas preenchidas)</font>";
                       $SELECIONA_OFICINA="";
                    }Else{
                        if ($STATUS_INSCRICAO=='ENCERRADA') {
                          $SELECIONA_OFICINA = "";
                          $STATUS_VAGAS      = "<br><font color=red>Inscrição encerrada</font>";
                        } else {
                          $SELECIONA_OFICINA = "<INPUT TYPE='checkbox' class='selectAtividade' NAME='cod_oficina[]'"
                                  . " VALUE='".$COD_OFICINA."' ".@$FWS_Checked." data-selected='".$DATA_INI."-".$HORARIO_INI."'" 
                                  . " \">";
                          $STATUS_VAGAS      = "";
                          $aoMenosUma = true;
                        }
                    }
                }else{
                    $SELECIONA_OFICINA = "";
                    $STATUS_VAGAS      = "<br><font color=red>Você não consta na lista de aptos desta opção</font>";
                }
            }

            If (@$bgcolorant=="#F4F4F4"){
               $bgcolor="#FFFFFF";
            }Else{
               $bgcolor="#F4F4F4";
            }

            $bgcolorant=$bgcolor;
            if ($EMENTA != ""){
                $EMENTA = ""
                        . "<br><span style='font-size:14px'>"
                        ."<a href=\"javascript:void(0);\" data-toggle=\"collapse\" data-target=\"#idOf".$COD_OFICINA."\">Ementa +</a>"
                        ."<div id=\"idOf".$COD_OFICINA."\" class=\"collapse\" style=\"background-color: rgb(255, 255, 255);\">"
                        . "".$EMENTA."<br>&nbsp;"
                        . "</div>"
                        . "</span>";
            }else{
                $EMENTA = "";
            }

            if($dataHoraAnt != $DATA_INI.$HORARIO_INI){
                echo "<tr valign='middle'>"
                . "<td width='100%' colspan='2' style='padding: 4px 4px 6px 4px;'>"
                . "<h4>";
                echo formatDateView($DATA_INI);
                echo $HORARIO_INI? " - ".$HORARIO_INI : '';
                echo "</h4>";
                echo "</td></tr>";
            }
            $dataHoraAnt = $DATA_INI.$HORARIO_INI;

            echo "<tr valign='middle' bgcolor='{$bgcolor}'>"
            . "<td width='5%'  style='padding: 4px 4px 6px 4px;text-align:center;vertical-align:top;'>{$SELECIONA_OFICINA}</td>"
            . "<td width='90%' style='padding: 4px 4px 6px 4px;'><span style='font-size:15px;'>";
            if($TITULO != "Não desejo participar de plenária"){
                if($NUMERO_OFICIAL){
                    echo $ABREV_TIPO." - ".$NUMERO_OFICIAL." - ";
                }else if($ORDEM){
                    echo $ABREV_TIPO." - ".$ORDEM." - ";
                }else{
                    echo $ABREV_TIPO? $ABREV_TIPO." - " : "";
                }
            }
            echo ""
            . "<b>{$TITULO}</b>"
            . "{$STATUS_VAGAS}";

            echo $NOME_MINISTRANTE? '<br>'.$NOME_MINISTRANTE : '';
            echo $CARGA_HORARIA? '<br>Carga horária: '.$CARGA_HORARIA.'h' : '';
            echo $EMENTA? $EMENTA : '';
            echo $INVESTIMENTO? '<br>'.$INVESTIMENTO : '';
            echo $LOCAL? '<br>'.$LOCAL : '';

            echo "</span></td></tr>";

        }
//        }///ocultaropcaosemvaga
        #Movendo para o proximo registro
        $RS->MoveNext();
        }
    }
    }
?>
    </td>
  </tr>
</table>
<!--<div class='btn-group'>-->
    <?php if ($objPerfil->getVerificarSelecionarAtividade()) { ?>
    <INPUT TYPE='hidden' VALUE='Inserir' NAME='acao'>
    <div style="margin:20px 15px 15px; padding-top:15px; border-top:1px solid #eee; display:flex; gap:8px; flex-wrap:wrap;">
        <?php if($fws_['cod_perfil']!='' and $aoMenosUma==true){ ?>
        <button class='btn btn-primary modalBtnSalvar' type="submit" onclick="validarFormulario(event)">Avançar</button>
        <?php } ?>
        <button class='btn btn-default modalBtnCancelar' type="button" id='btn-limpar'>Limpar</button>
        <?php if ($_SESSION['_cod_inscrito']){ ?>
            <a href="index.php?content=view/inscricao/inscricao_congresso_ok.php"
              class="btn btn-default modalBtnCancelar" role="button">Voltar</a>
        <?php }else{ ?>
            <a href="index.php?content=view/inscricao/inscricao_01.php"
              class="btn btn-default modalBtnCancelar" role="button">Voltar</a>
        <?php } ?>
    </div>
    <?php } ?>

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
<?php
$fws_['tipoOpcaoAtividade']   = 'Oficina';
$fws_['permitirMesmoHorario'] = false;
if(COD_EVENTO==28){
    $fws_['permitirMesmoHorario'] = true;
}
$fws_['numMaxAtividade'] = $objPerfil->getQtdAtividadePermite();
?>
<script>
$(document).ready(function(){
    $(".selectAtividade").click(function(){
        var dtInicioSelected = $(this).data('selected');
        var codSelected      = $(this).val();
        var element          = $(this);
        somar = document.form.n_ativ_selecionados.value;
        somar = eval(somar);
        totalPermitido = <?php echo $objPerfil->getQtdAtividadePermite()?>;
        
        if($(this).is(":checked")){
            sair = false;
            $(".selectAtividade").each(function(){
                if($(this).is(":checked")){
                    console.log(dtInicioSelected+'=='+$(this).data('selected')+' && '+codSelected+'!='+$(this).val())
                    <?php if($fws_['permitirMesmoHorario']==false) { ?>
                    if(somar>=totalPermitido){
                        alert('Já existe a quantidade permitida de atividade selecionada.');
                        element.prop( "checked", false );
                        $(this).focus();
                        sair = true;                        
                        return false
                    }
                    if(dtInicioSelected==$(this).data('selected') && codSelected!=$(this).val()){
                        alert('Já existe uma atividade seleciona para este dia.');
                        element.prop( "checked", false );
                        $(this).focus();
                        sair = true;
                        return false;
                    }
                    <?php } ?>
                }
                
            });
            if(sair){
                return false;
            }
            somar = eval(somar+1);
        } else {
            somar = eval(somar-1);
        }
//        alert(somar)
        document.form.n_ativ_selecionados.value = somar
        <?php if ($objPerfil->getQtdAtividadePermite()!=0){ ?>
        if (somar ==<?php echo $objPerfil->getQtdAtividadePermite(); ?>){
            alert("Você atingiu o número máximo de <?php echo $objPerfil->getQtdAtividadePermite() ?> atividades selecionadas!");
//            if (confirm("Clique em OK para avançar.")) {
//                document.form.submit();
//            }            
        }
        <?php } ?>
    });
});

function validarFormulario(event) {
    var opcoesSelecionadas = document.getElementsByName("cod_oficina[]");
    var peloMenosUmaSelecionada = false;

    for (var i = 0; i < opcoesSelecionadas.length; i++) {
        if (opcoesSelecionadas[i].checked) {
            peloMenosUmaSelecionada = true;
            break;
        }
    }
    <?php if ($objPerfil->getVerificarSelecionarAtividade()) { ?>
        <?php if ($objPerfil->getCodEvento()==70) { ?>
            if (!peloMenosUmaSelecionada) {
                if (confirm("Deseja continuar sem selecionar atividade?")) {
                } else {
                    event.preventDefault(); // Impede o envio do formulário
                }    
            }
        <?php }else{ ?>
            if (!peloMenosUmaSelecionada) {
                event.preventDefault(); // Impede o envio do formulário
                alert("Selecione pelo menos uma opção de atividade.");
            }
        <?php } ?>
    <?php } ?>
}
</script>

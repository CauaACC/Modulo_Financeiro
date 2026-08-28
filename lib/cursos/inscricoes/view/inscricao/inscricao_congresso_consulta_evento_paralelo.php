<?php

use Spipu\Html2Pdf\Tag\Html\Span;

if($objInscrito->getPossuiBoletoGerado() and $OP_BLOQUEAR_ALTERACAO_SE_BOLETO_GERADO){
    $OP_PERMITIR_ADICIONAR_EVENTO_PARALELO = true;
    $OP_PERMITIR_ALTERAR_EVENTO_PARALELO   = true;
}

/**
 * Calculando dias a permitir alteração após data de inscrição
 * ps: não esta verificando a data de seleção da atividade
 */
if ($OP_PERMITIR_TESTAR_PERMISSAO_POR_DATA_INSCRICAO){
    $dataComparar1 = date('Ymd', strtotime(substr($objInscrito->getDatIncl(),0,10)));
    $dataComparar2 = date('Ymd', strtotime('+'.$OP_PERMITIR_TESTAR_PERMISSAO_POR_DATA_INSCRICAO.'  days', strtotime($dataComparar1)));
    $dataHoje = Date("Ymd");
    if (!($dataComparar1 <= $dataHoje and $dataHoje <= $dataComparar2)) {
        $OP_PERMITIR_ALTERAR_EVENTO_PARALELO = false;
    }
}
    
/*Permitir a edição de minicurso durante o logar como*/
//@$SIS_RECEPCAO_PARTICIPANTES = 1;
//@$cod_msg = false;
//@$redirect = false;
//If (FWA_Autenticacao($_SESSION['_cod_usuario'], @$SIS_RECEPCAO_PARTICIPANTES, 5, $cod_msg, $redirect)) {
//    $OP_PERMITIR_ALTERAR_EVENTO_PARALELO   = true;
//    $OP_PERMITIR_ADICIONAR_EVENTO_PARALELO = true;
//}

try {
    $ii_p = 0;
    $objInscrito->carregarListaOficinas($debug);
    $valor_atividade = 0;
    $eventosParalelos = $objEvConf->getEventoParaleloTipo();
    foreach ($objInscrito->getListaOficinas() as $objL) {
        /*verificando se é um evento paralelo*/
        $isEvParalelo = false;
        if($eventosParalelos!=''){
            $eventosParalelosArray = explode(',', $eventosParalelos); 
            if(in_array($objL->getTipo(), $eventosParalelosArray) or in_array($objL->getCodOficina(), $eventosParalelosArray)){
                $isEvParalelo = true;
            }
        }
        /*Lista apenas atividades ativas e se é evento paralelo*/
        if(($objL->getStatus()=='ATIVADA')and($objL->getStatusInscricao()=='ATIVADA')and($isEvParalelo==true)){
            echo ($ii_p=='0'? "<br>" : "");
            $ii_p++;
            echo "<div class='row' style='margin-botton:3px'>";
            if($OP_PERMITIR_ALTERAR_EVENTO_PARALELO){
                echo "<div class='col-sm-1'>
                <a href='index.php?content=view/inscricao/inscricao_congresso_atividade_parelela_excluir.php&acao=Excluir&cod_oficina=".$objL->getCodOficina()."'
                class='btn btn-xs btn-primary' role='button' title='Excluir para alterar'>Excluir</a>
                </div >";
            }
            echo "<div class='col-sm-11'>";
            $txtTituloAtividade = formatarTitulo($objL->getTipo(), $objL->getTitulo());
            echo $txtTituloAtividade . "<br>";
            // echo $objL->getDataIni()? "<span class='fs-3'>".formatDateView($objL->getDataIni())." - ".$objL->getHorarioIni()."</span>" : "";
            if($OP_EVENTO_ONLINE_LINK_MINICURSO_LIBERADO){
                echo $objL->getLinkAula()!=''? " <a href='".$objL->getLinkAula()."' target='_blank'>Acessar Sala</a>" : "";
            }
            echo "</div>";
            echo "</div>";
            
            $objL->getValorAtividade()? $valor_atividade += $valor_atividade+$objL->getValorAtividade() : "";
        }
    }
    /*Permitir adicionar evento paralelo caso não tenha selecionado nenhum*/
    /**@todo colocar a quantidade permitida na configuação */
    if($objEvConf->getCodEvento()==70){
        $OP_PERMITIR_MAX_EVENTO_PARALELO = 8;
    }
    if($ii_p>=$OP_PERMITIR_MAX_EVENTO_PARALELO){
        $msgBloqueio = "Para alterar as opções acima, exclua uma das alternativas";
        $OP_PERMITIR_ADICIONAR_EVENTO_PARALELO = false;
    }

    if($valor_atividade>0){
        echo "<span style='font-size:10px;font-weight:bold;margin-left:60px;'>Investimento: R$ ".$valor_atividade.",00</span>";
    }
} catch (Exception $exc) {
    // echo $exc->getMessage();
}

if($fws['op_selecionar_evento_paralelo_botao_personalizado']){
    if($OP_PERMITIR_ADICIONAR_EVENTO_PARALELO){
        ?>
        <br>
        <!--<span style='font-size:20px;font-weight:bold;'>4º Encontro dos Profissionais de Educação Física</span><br>-->
        <a href="index.php?content=view/inscricao/inscricao_congresso_seleciona_atividade_parelela.php"
           class="btn btn-xs btn-primary" role="button">Adicione Atividade</a>
        <?php
    }else{
        echo "<br>";
        if($msgBloqueio){
            echo $msgBloqueio;
        }else{
            // echo "Opção não disponível";
        }
    }
}else{
    if($OP_PROCESSO_INSCRICAO AND $OP_PERMITIR_ADICIONAR_EVENTO_PARALELO){
        ?>
        <br><br>
        <a href="index.php?content=view/inscricao/inscricao_congresso_seleciona_atividade_parelela.php"
           class="btn btn-xs btn-primary" role="button">Selecionar Atividade</a>
        <?php
    }else{
        // echo "Opção não disponível";
    }
}

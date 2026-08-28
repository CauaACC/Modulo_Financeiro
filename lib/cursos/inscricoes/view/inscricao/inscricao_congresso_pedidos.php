<?php
/**
 * Calculando dias a permitir alteração após data de inscrição
 * ps: não esta verificando a data de seleção da atividade
 */
if ($OP_PERMITIR_TESTAR_PERMISSAO_POR_DATA_INSCRICAO){
    $dataComparar1 = date('Ymd', strtotime(substr($objInscrito->getDatIncl(),0,10)));
    $dataComparar2 = date('Ymd', strtotime('+'.$OP_PERMITIR_TESTAR_PERMISSAO_POR_DATA_INSCRICAO.'  days', strtotime($dataComparar1)));
    $dataHoje = Date("Ymd");
    if ($dataComparar1 <= $dataHoje and $dataHoje <= $dataComparar2) {
        $OP_PERMITIR_ALTERAR_OFICINA = true;
    }else{
        $OP_PERMITIR_ALTERAR_OFICINA = false;
    }
}

/*Permitir a edição de minicurso durante o logar como*/
//@$SIS_RECEPCAO_PARTICIPANTES = 1;
//@$cod_msg = false;
//@$redirect = false;
//If (FWA_Autenticacao($_SESSION['_cod_usuario'], @$SIS_RECEPCAO_PARTICIPANTES, 5, $cod_msg, $redirect)) {
//    $OP_PERMITIR_ALTERAR_OFICINA = true;
//}
if ($OP_PERMITIR_ALTERAR_OFICINA){ 
    echo "<b>Atenção:</b> A possibilidade de excluir e alterar o minicurso escolhido estará disponível
    por até 1 dia após a gravação de sua inscrição.<br>";
}

try {
    $ii = 0;
    $objInscrito->carregarListaOficinas($debug);
    $valor_atividade = 0;
    foreach ($objInscrito->getListaOficinas() as $objL) {
        /*Lista apenas atividades ativas*/
        //if(($objL->getStatus()=='ATIVADA')and($objL->getTipo()=='Minicurso')){
        if(($objL->getStatus()=='ATIVADA')){
            echo "<div class='row' style='margin-top:15px;'>";
            $ii++;
//                            if(!$objInscrito->getPossuiBoletoGerado() and $OP_PERMITIR_ALTERAR_OFICINA){
            if($OP_PERMITIR_ALTERAR_OFICINA){
            if($fws_['op_excluir_oficina_apos_inscricao']){
                echo "<div class='col-sm-1'>";
                echo "<a href='index.php?content=view/inscricao/inscricao_congresso_oficina_excluir.php&acao=Excluir&cod_oficina=".$objL->getCodOficina()."'
                      class='btn btn-xs btn-primary' role='button' title='Excluir para alterar'>Excluir</a>";
                echo "</div>";
//                            }
            }}
            echo "<div class='col-sm-11'>";
            if($objL->getOrdem()!=0){
                echo "M-".lpad($objL->getOrdem(),2,0)." - ";
            }
            echo $objL->getTitulo()."<br>";
            echo $objL->getDataIni()? $objL->getDataIni()." - ".$objL->getHorarioIni() : "";
            if($OP_EVENTO_ONLINE_LINK_MINICURSO_LIBERADO){
                echo (($objL->getLinkAula()!='' and $objL->getLinkAulaPublicar()=='1')? "  <a href='".$objL->getLinkAula()."' "
                        . "target='_blank' style='margin-left:35px;font-size:18px'>"
                        . " <i class='fa fa-group'></i> Acessar Sala</a><br>" : "");
            }
            echo "</div>";
            echo "</div>";
            $objL->getValorAtividade()? $valor_atividade += $valor_atividade+$objL->getValorAtividade() : "";
        }
    }
    if($valor_atividade>0){
        echo "<span style='font-site:10px;font-weight:bold;margin-left:60px;'>Investimento minicurso: R$ ".$valor_atividade.",00</span>";
    }
} catch (Exception $exc) {
    echo $exc->getMessage();
}
//if(($objPerfil->getQtdAtividadePermite()>$ii and !$objInscrito->getPossuiBoletoGerado() and $OP_PROCESSO_INSCRICAO) or ($objPerfil->getQtdAtividadePermite()>$ii and $OP_PERMITIR_ALTERAR_OFICINA)){
if(($objPerfil->getQtdAtividadePermite()>$ii  and $OP_PROCESSO_INSCRICAO) or ($objPerfil->getQtdAtividadePermite()>$ii and $OP_PERMITIR_ALTERAR_OFICINA)){
if($fws_['op_selecionar_oficina_apos_inscricao']){
    ?>
    <br><br>
    <a href="index.php?content=view/inscricao/inscricao_congresso_seleciona_oficina.php"
       class="btn btn-xs btn-primary" role="button">Selecionar Atividade</a>
    <?php
}}
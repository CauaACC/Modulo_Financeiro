<?php
/**
 * Calculando dias a permitir alteração após data de inscrição
 * ps: não esta verificando a data de seleção da atividade
 */
if ($OP_PERMITIR_TESTAR_PERMISSAO_POR_DATA_INSCRICAO){
    $dataComparar1 = date('Ymd', strtotime(substr($objInscrito->getDatIncl(),0,10)));
    $dataComparar2 = date('Ymd', strtotime('+'.$OP_PERMITIR_TESTAR_PERMISSAO_POR_DATA_INSCRICAO.' days', strtotime($dataComparar1)));
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
if($objEvConf->getAtividadeAlterar()){
    if ($OP_PERMITIR_ALTERAR_OFICINA){ 
        $fws_['op_excluir_oficina_apos_inscricao'] = 1;
        $fws_['op_selecionar_oficina_apos_inscricao'] = 1;
        
        if($objEvConf->getCodEvento()==88){
            echo "<b>Atenção:</b> A possibilidade de excluir para alterar a opção escolhida estará disponível até 18/04/2025.<br>";
        }else{
            echo "<b>Atenção:</b> A possibilidade de excluir/alterar a opção escolhida estará disponível
            por até ".$OP_PERMITIR_TESTAR_PERMISSAO_POR_DATA_INSCRICAO." dia após a gravação de sua inscrição.<br>";
        }
    }
}
try {
    $ii = 0;
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
        /*Lista apenas atividades ativas e se não é evento paralelo*/
        if(($objL->getStatus()=='ATIVADA')and($objL->getStatusInscricao()=='ATIVADA')and($isEvParalelo==false)){
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
            if($objL->getTipo()=='Agenda' and (in_array($objL->getCodEvento(), array(35,52,72,105,119)))){
                $fws_['data_inicio_agenda'] = $objL->getDataInicio();
                echo "<span style='font-size:18px;color:green;'>";
                //echo "Agendado para: ".$objL->getTitulo()."<br>";
                $fws_['desc_atividade'] = formatDateHoraTrans($objL->getDataInicio(), $formatIn='YYYYMM-DD', $formatOut='DD/MM/YYYY')." - ".$objL->getTitulo()."<br>";
                echo "Agendado para: ".$fws_['desc_atividade'];
                echo "</span>";
            }else{
                if($objL->getOrdem()!=0){
                    if($objL->getAbrevTipo()){
                        echo $objL->getAbrevTipo()." - ".lpad($objL->getOrdem(),2,0)." - ";
                    }else{
                        echo "M-".lpad($objL->getOrdem(),2,0)." - ";
                    }
                }
                echo $objL->getCodOficina()." - ".$objL->getTitulo()."<br>";
            }
            $dataAtiv = $objL->getDataInicio() ?: $objL->getDataIni();
            echo isValidDate($dataAtiv)? formatDateView($dataAtiv)." - ".$objL->getHorarioIni() : "";
            echo $objL->getInvestimento()!='' ? "<br>".$objL->getInvestimento() : "";
            echo "<span style='margin-left:35px'>".$objL->getLinkAulaComplemento()."</span>";
            if($OP_EVENTO_ONLINE_LINK_MINICURSO_LIBERADO){
                if ($objL->getLinkAula()!='' and $objL->getLinkAulaPublicar()=='1'){
                    echo "  <br><a href='".$objL->getLinkAula()."' "
                        . "target='_blank' style='margin-left:35px;font-size:18px;color:green'>"
                        . " <i class='fa fa-group'></i> Clique aqui para Acessar Sala</a>";
                }    
            }
            if(($objEvConf->getAtividadeConteudoLiberar()==1) and ($objInscrito->getTotalPagamentos()>=$objEvConf->getAtividadeConteudoQtdPagamento())){
                $objConteudo = new GerenciarArquivoAtividadeAC();
                ?>    
                <b>Lista de Arquivos:</b>
                <div style="margin-left:35px;width: inherit;">
                    <table class="tablesorter" border="0" style="width: inherit;">
                        <thead>
                        <th width="80%">Descrição do link</th>
                        <th width="20%"><span style="padding-left: 15px;">Data inclusão</span></th>
                        </thead> 
                        <tbody class="inputs">         
                            <?php
                            $objConteudo->carregarTabelaConteudoPublico($fws_, $debug);
                            ?>
                        </tbody>
                    </table>
                </div>
                <form name="getdocumento" method="post" action="<?php echo WB_URL_COMMAND?>" target="_blank">
                    <input type="hidden" name="c" value="GerenciarArquivoAtividade">
                    <input type="hidden" name="m" value="abrirArquivoAtividade">
                    <input type="hidden" value="" name="cod">
                </form>
                <script type="text/javascript">    
                    function visualizarAtividade(cod){
                        document.getdocumento.cod.value=cod;
                        document.getdocumento.submit();
                    }
                </script>
            <?php
            }
            echo "</div>";
            echo "</div>";
            $objL->getValorAtividade()? $valor_atividade = $valor_atividade+$objL->getValorAtividade() : "";
        }
    }
//    if($valor_atividade>0){
//        echo "<span style='font-site:10px;font-weight:bold;margin-left:60px;'>"
//        . "Investimento minicurso: R$ ".formatMoneyView($valor_atividade.'00')."</span>";
//    }
} catch (Exception $exc) {
    echo $exc->getMessage();
}
//if(($objPerfil->getQtdAtividadePermite()>$ii and !$objInscrito->getPossuiBoletoGerado() and $OP_PROCESSO_INSCRICAO) or ($objPerfil->getQtdAtividadePermite()>$ii and $OP_PERMITIR_ALTERAR_OFICINA)){
if(($objPerfil->getQtdAtividadePermite()>$ii  and $OP_PROCESSO_INSCRICAO) or ($objPerfil->getQtdAtividadePermite()>$ii and $OP_PERMITIR_ALTERAR_OFICINA)){
if($fws_['op_selecionar_oficina_apos_inscricao']){
    ?>
    <br><br>
    <a href="index.php?content=view/inscricao/inscricao_congresso_seleciona_oficina.php"
       class="btn btn-xs btn-primary" role="button">Selecionar Opção</a>
    <?php
}}
<!--<div class="form-group col-sm-12" style="padding-left: 0px;">
    <label for="participou">Já participou de algum evento promovido pelo INESCO?</label>
    &nbsp;&nbsp;&nbsp;            
    <input type='radio' name='ja_participou_outro_evento' value='SIM' <?php echo verifItemChecked($objInscrito->getJaParticipouOutroEvento(), 'SIM') ?>  required autofocus>
    Sim
    &nbsp;&nbsp;&nbsp;
    <input type='radio' name='ja_participou_outro_evento' value='NAO' <?php echo verifItemChecked($objInscrito->getJaParticipouOutroEvento(), 'NAO') ?>  required autofocus>
    Não 
</div>-->
<?php
$objAssociado = new Associado();
$objAssociado->setCpf($objInscrito->getCpf());
$objAssociado->setCodEvento($objInscrito->getCodEvento());
$objAssociado->carregarAssociadoByCpf();

/*Localizar taxas por período*/
$objPerfil = $objInscrito->getObjPerfil();
if($objPerfil->getValoresPorPeriodo()){
    $condicoes['data_hoje'] = Date('Y-m-d H:i:s');  
}else{
    $condicoes = false;  
}
$objPerfil->carregarTaxaAtiva($condicoes, $debug);
$objTaxaPerfil = $objPerfil->getObjTaxa();

/*inicio calculo valor do pedido*/
$valorPerfil = $objTaxaPerfil->getTxUnicaAVistaValor();
$valorAssociacao = 65.00;

$valorTotalAssociado    = $valorPerfil-($valorPerfil*0.30);
$valorTotalAssociando   = $valorPerfil-($valorPerfil*0.30)+$valorAssociacao;
$valorTotalNaoAssociado = $valorPerfil;

if($objAssociado->getStatus()=='ATIVADA'){
    $valorTotalPedido = $valorTotalAssociado;
//        echo "<br><span style='font-size:17px'>para ASSOCIADO em dia: R$ ".formatMoneyView($valorTotalAssociado.'00');
}else{
    if($fws_['que_ser_associado']=='SIM'){
        $valorTotalPedido = $valorTotalAssociando;
    }else{
        $valorTotalPedido = $valorTotalNaoAssociado;
    }
//        echo "<br><span style='font-size:17px'>para ASSOCIAR-SE agora: R$ ".formatMoneyView($valorTotalAssociando.'00');
//        echo "<br><span style='font-size:17px'>para NÃO ASSOCIAR-SE: R$ ".formatMoneyView($valorTotalNaoAssociado.'00');
}

?>
<?php if ($objAssociado->getStatus()=='ATIVADA') { ?>
    <div class="form-group col-sm-12" style="padding-left: 0px;">
        <label for="que_ser_associado"><span style='font-size:16px'>
                Valor da inscrição conforme itens selecionados:</span></label>
    </div>
    <div class="form-group col-sm-12" style="padding-left: 0px;">
        <?php echo "<br><span style='font-size:17px'>para ASSOCIADO em dia: R$ ".formatMoneyView($valorTotalAssociado.'00'); ?>
    </div>
<?php }else { ?>
    <div class="form-group col-sm-12" style="padding-left: 0px;">
        <label for="que_ser_associado"><span style='font-size:16px'>
                Opções de inscrição conforme itens selecionados:</span></label>
    </div>
    <div class="form-group col-sm-12" style="padding-left: 0px;">
        Ao optar por se ASSOCIAR ao INESCO 
        <br>Acrescentamos a valor de R$ 65,00 a sua inscrição como parcela única para ano de 2023.
        <br>Mas aplicamos o desconto de 30% no valor da inscrição do perfil selecionado
        <br>
        <br><br>Quer se ASSOCIAR e obter o desconto de 30% no valor da inscrição?
        <b><br>Valor total (evento + associação): R$ <?php echo formatMoneyView($valorTotalAssociando.'00'); ?></b>
        <br><input type='radio' name='que_ser_associado' value='SIM' <?php echo verifItemChecked($objInscrito->getQueSerAssociado(), 'SIM') ?>  required autofocus>
        SIM, quero me associar e ganhar os desconto!
        &nbsp;&nbsp;&nbsp;
    </div>
    <div class="form-group col-sm-12" style="padding-left: 0px;">
        <br><b>Valor total (evento sem associação: R$ <?php echo formatMoneyView($valorTotalNaoAssociado.'00'); ?></b>
        <br><input type='radio' name='que_ser_associado' value='NAO' <?php echo verifItemChecked($objInscrito->getQueSerAssociado(), 'NAO') ?> >
        Não, não quero me associar.
    </div>
<?php } ?>
<!--
<div class="form-group col-sm-12" style="padding-left: 0px;">
    <label for="que_ser_associado">
        Além de valores diferenciados para a Escola de Inverno, em outubro de 2023 será realizado o 2º Simpósio Paranaense de Saúde Pública e Coletiva 
        que também ofertará condições diferenciadas aos membros da instituição, assim como em outras atividades promovidas.
        Quero me ASSOCIAR ao INESCO/2023: valor da associação R$ 65,00?
        <br>Associodos obterão desconto de 20% no valor da inscrição de cada curso?</label>
    <br>
    <input type='radio' name='que_ser_associado' value='SIM' <?php echo verifItemChecked($objInscrito->getQueSerAssociado(), 'SIM') ?>  required autofocus>
    SIM, quero me associar e ganhar os desconto!
    &nbsp;&nbsp;&nbsp;
    <input type='radio' name='que_ser_associado' value='NAO' <?php echo verifItemChecked($objInscrito->getQueSerAssociado(), 'NAO') ?> >
    Não, não quero me associar.
</div>
-->
    
    
<div class="form-group col-sm-12" style="padding-left: 0px;">
    <div class="form-group col-sm-5" style="padding-left: 0px;padding-top: 10px;">
        <label for="tipo_inscricao">Perfil da Inscrição:</label>
        <select class='form-control' name='tipo_inscricao' id='tipo_inscricao' placeholder="Perfil da Inscrição" required autofocus>
        <option class='form_campos' value='' selected>Selecione um Perfil</option>
        <?php
        if ($fws_['tipo_inscricao'] == '') {
            $fws_['tipo_inscricao'] = $objInscrito->getTipoInscricao();
        }
        CarregarOptionsAC::tipoInscricaoLinha($fws_, $debug, false);
        ?>
        </select>
    </div>
</div>

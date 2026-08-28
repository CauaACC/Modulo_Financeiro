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
        
$totalOficias = $fws_['cod_oficina'];
if(count($totalOficias)>0){
//    $valorAssociacao = 65.00;
    $valorTotalAtividade = $objInscrito->getValorTotalAtividade();

    /** 
    Profissionais código do perfil = 178 
    Pacote para profissionais
    (Congresso + 3 workshops) 

    Alunos de graduação, pós graduação e associados = 175 - 176 - 177
    Pacote para alunos de graduação, pós graduação e associados ABFA 
    (Congresso + 3 workshops) – já com desconto de 50 %
    */

    $valorTotalProfissional  = $valorTotalAtividade+$valorPerfil;
    $valorTotalAlunoPoseGrad = $valorTotalAtividade+$valorPerfil;
}
if(count($totalOficias)>=3){
    $valorTotalProfissional  = $valorTotalProfissional-50.00;
    $valorTotalAlunoPoseGrad = $valorTotalAlunoPoseGrad*0.50;
}
$codPerfil = $objPerfil->getCodPerfil();
$perfisPermitidos = [175, 176, 177];
if (in_array($codPerfil, $perfisPermitidos)) {
    $valorTotal = $valorTotalAlunoPoseGrad;
} else if (in_array($codPerfil, [192])) {
    $valorTotal = $valorPerfil;
} else {
    $valorTotal = $valorTotalProfissional;
}
?>
<div class="form-group col-sm-12" style="padding-left: 0px;">
    <label for="que_ser_associado"><span style='font-size:16px'>
    Valor da inscrição conforme itens selecionados:</span></label>
</div>
<div class="form-group col-sm-12" style="padding-left: 0px;">
    O valor da inscrição leva em consideração o perfil selecionado
    <br>e a quantidade de atividades selecionadas
    <br>
    <span style='font-size:16px'>
        <b><br>Valor Total da Inscrição: R$ <?php echo formatMoneyView($valorTotal.'00'); ?></b>
    </span>    
    <br>
    <hr>
</div>
<!--<div class="form-group col-sm-12" style="padding-left: 0px;">
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
</div>-->

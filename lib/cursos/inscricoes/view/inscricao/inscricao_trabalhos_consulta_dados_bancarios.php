<?php
require_once(dirname(__FILE__)."/../../include/config.php");
#require_once(dirname(__FILE__)."/../../include/inscricao_request_variaveis.php");
// dd($objInscrito);
?>
<span style="color:red;"><h3>Atenção:</h3></span>
<a href='https://hutec.org.br/wp-content/uploads/2025/10/COMUNICADO_PRO-REDE_assinado.pdf' target='_blank'>COMUNICADO DE ESCLARECIMENTO AOS CANDIDATOS</a> 
<br>
<a href='https://hutec.org.br/wp-content/uploads/2025/10/EDITAL_027.2025_Cancelamento_Pro-Rede_assinado.pdf' target='_blank'>Edital 027/2025 – COMUNICA O CANCELAMENTO DO EDITAL DE PROCESSO SELETIVO 016/2025 – PRÓ-REDE</a> 
<br>
<?php if ($objEvConf->getFormDadosBancarios() == true) { ?>
    <?php
    $objInscrito->carregarUltimoPagamento($debug);
    $objUTPagamento = $objInscrito->getObjPagamento();

    if($objUTPagamento->getFormaPagamento()=='BOLETO'){
    ?>
    <br>Para pagamentos efetuados por BOLETO BANCÁRIO, o candidato deverá acessar o login da inscrição e informar a chave Pix ou o número da agência e conta corrente para recebimento da devolução.
    <br><span style="color:green"> devolução da taxa de inscrição será efetuada até o dia 14 de novembro de 2025</span>.
    <br><span style="color:red;font-size: 14px;"> Atenção: confirme seus dados bancários descritos abaixo:</span>
    <br>
    <div class="form-group col-sm-12" style="margin-bottom:10px;margin-top:10px;padding-left: 0px;">
        <div class="form-group col-sm-12" style="padding-left: 0px;">
            <label for="rg">Dados Bancários:</label>
        </div>
        <?php if ($objEvConf->getFormDadosBancariosSomentePix() == true) { ?>
            <div class="form-group col-sm-3" style="padding-left: 0px;" >
                <label for="pix">Pix:</label>
                <input type="text" id="db_pix" name="db_pix" value="<?php echo $objInscrito->getDbPix() ?>" class="form-control" placeholder="Pix" <?php echo $OP_PERMITIR_ALTERAR_REQUIRED ?> autofocus SIZE='60' maxlength='255' onBlur='trim(this);' style="border:0; background:transparent; box-shadow:none; padding:0; height:auto;" >
            </div>

        <?php } ?>    
        <?php if ($objEvConf->getFormDadosBancariosSomentePix() == 0) { ?>
            <div class="form-group col-sm-3" style="padding-left: 0px;">
                <label for="banco">Banco:</label>
                <?php echo $objInscrito->getDbBanco()? $objInscrito->getDbBanco() : '<font color="red">Não informado</font>'; ?>
            </div>
            <div class="form-group col-sm-3" style="padding-left: 0px;">
                <label for="agencia">Agência - DV:</label>
                <?php echo $objInscrito->getDbAgencia()? $objInscrito->getDbAgencia() : '<font color="red">Não informado</font>'; ?>
            </div>
            <div class="form-group col-sm-3" style="padding-left: 0px;">
                <label for="conta_corrente">Conta Corrente - DV:</label>
                <?php echo $objInscrito->getDbContaCorrente()? $objInscrito->getDbContaCorrente() : '<font color="red">Não informado</font>'; ?>
            </div>
            <div class="form-group col-sm-3" style="padding-left: 0px;" >
                <label for="pix">Pix:</label>
                <?php echo $objInscrito->getDbPix()? $objInscrito->getDbPix() : '<font color="red">Não informado</font>'; ?>
            </div>
        <?php } ?>
        <div class="form-group col-sm-12" style="padding-left: 0px;">
            <a href='index.php?content=view/inscricao/inscricao_congresso_cadastro_dados_outros.php'>
            <button class="btn btn-xs btn-primary" role='button'>Clique aqui para Informar Dados Bancários</button></a>
        </div> 
    <?php }else{ ?>
        <div class="form-group col-sm-12" style="padding-left: 0px;">
            <?php
            if ($objInscrito->getTotalPagamentos() > 0){
                echo '
                Os candidatos que efetuaram o pagamento da taxa de inscrição terão direito à devolução dos
                valores pagos pela mesma plataforma de pagamento (PagSeguro), sendo que, para
                pagamentos realizados via CARTÃO DE CRÉDITO ou PIX, o estorno será processado
                automaticamente no cartão ou conta vinculada à respectiva transação. 
                <span style="color:green">Não será necessário o envio de qualquer solicitação para tal procedimento.</span>
                <br><br><span style="color:green"> A devolução da taxa de inscrição será efetuada até o dia 14 de novembro de 2025</span>.';
            }else{
                echo '
                Nenhum pagamento detectado para estorno.
                ';
            }
            ?>
        </div>
    <?php } ?>
    <br>
    <br>em caso de dúvidas, entre em contato com a HUTec
    <br>pelo WhatsApp 43 99104-2659  ou pelo e-mail: prorede@hutec.com.br.
    <br>
<?php } ?>
<table border='0' width='570'  cellspacing="10" cellpadding="10" class="dataTable">
    <tr><td colspan="2"><span style="font-size:15px;font-weight: bold;">Configuração de Formas de Pagamento</span></td></tr>
    <tr>
        <td>Plano de Contas para Receita de Inscrições:</td>
        <td>
            <select name="cod_plano_contas_receita" id="cod_plano_contas_receita">
                <option value="">Nenhum (não gerar lançamento automático)</option>
                <?php
                try {
                    $objPC = new PlanoContas();
                    $objPC->carregarListaPlanoContas(false, $debug);
                    $listaPC = $objPC->getListaPlanoContas();
                    foreach ($listaPC as $objPCItem) {
                        if ($objPCItem->getAceitaLancamento() == 1) {
                            $selected = ($objEvConf->getCodPlanoContasReceita() == $objPCItem->getCodPlanoContas()) ? 'selected' : '';
                            echo "<option value='" . $objPCItem->getCodPlanoContas() . "' " . $selected . ">"
                                . $objPCItem->getCodigoContabil() . " - " . $objPCItem->getNomePlanoContas()
                                . " (" . $objPCItem->getTipoConta() . ")"
                                . "</option>";
                        }
                    }
                } catch (Exception $e) {}
                ?>
            </select>
        </td>
    </tr>
    <tr>
        <td>Gateway de Pagamento:  </td>
        <td>
            <select name="gateway_pagamento" id="gateway_pagamento">
            <option></option>
            <?php
            $fws_['gateway_pagamento'] = $objEvConf->getGatewayPagamento();
            CarregarOptionsAC::gatewayPagamento($fws_, $debug);
            ?>
        </td>
    </tr>
    <tr class="pagSeguro">
        <td colspan="2">
            Chave PagSeguro:
            <input type='text' name='pag_pagseguro_chave' value='<?php echo $objEvConf->getPagSeguroChave() ?>' maxlength='255' size='60' onBlur="trim(this);" >
        </td>
    </tr>
    <tr class="pagSeguro">
        <td colspan="2">
            Serial Máquininha:
            <input type='text' name='pag_seguro_serial_maquina' value='<?php echo $objEvConf->getPagSeguroSerialMaquina() ?>' maxlength='255' size='60' onBlur="trim(this);" >
        </td>
    </tr>
    <tr class="itauShopLine">
        <td colspan="2">
            Código da Empresa ItauShopline:
            <input type='text' name='itau_shopline_cod_empresa' value='<?php echo $objEvConf->getItauShoplineCodEmpresa() ?>' maxlength='255' size='60' onBlur="trim(this);" >
        </td>
    </tr>
    <tr class="itauShopLine">
        <td colspan="2">
            Chave ItauShopline:
            <input type='text' name='itau_shopline_chave' value='<?php echo $objEvConf->getItauShoplineChave() ?>' maxlength='255' size='60' onBlur="trim(this);" >
        </td>
    </tr>
    <tr class="itauBolecode">
        <td colspan="2">
            Client ID Itaú BoleCode:
            <input type='text' name='itau_bolecode_client_id' value='<?php echo $objEvConf->getItauBolecodeClientId() ?>' maxlength='255' size='60' onBlur="trim(this);" >
        </td>
    </tr>
    <tr class="itauBolecode">
        <td colspan="2">
            Client Secret Itaú BoleCode:
            <input type='text' name='itau_bolecode_client_secret' value='<?php echo $objEvConf->getItauBolecodeClientSecret() ?>' maxlength='255' size='60' onBlur="trim(this);" >
        </td>
    </tr>
    <tr class="itauBolecode">
        <td colspan="2">
            Chave PIX (BoleCode):
            <input type='text' name='itau_bolecode_chave_pix' value='<?php echo $objEvConf->getItauBolecodeChavePix() ?>' maxlength='255' size='60' onBlur="trim(this);" >
        </td>
    </tr>
    <tr class="itauBolecode">
        <td colspan="2">
            ID Beneficiário (agência+00+conta+dígito):
            <input type='text' name='itau_bolecode_id_beneficiario' value='<?php echo $objEvConf->getItauBolecodeIdBeneficiario() ?>' maxlength='50' size='60' onBlur="trim(this);" >
        </td>
    </tr>
    <tr class="itauBolecode">
        <td colspan="2">
            Código Carteira:
            <input type='text' name='itau_bolecode_codigo_carteira' value='<?php echo $objEvConf->getItauBolecodeCodigoCarteira() ?>' maxlength='10' size='10' onBlur="trim(this);" >
        </td>
    </tr>
    <tr class="itauBolecode">
        <td colspan="2">
            <label>
                <input type="checkbox" name="itau_bolecode_exibir_pix" value="1" <?php echo $objEvConf->getItauBolecodeExibirPix() == 1 ? 'checked' : '' ?>>
                Exibir QR Code PIX no boleto (al&eacute;m da linha digit&aacute;vel)
            </label>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <label>
                <input type="hidden" name="aviso_pagamento_confirmado_habilitado" value="0">
                <input type="checkbox" name="aviso_pagamento_confirmado_habilitado" value="1" <?php echo $objEvConf->getAvisoPagamentoConfirmadoHabilitado() == 1 ? 'checked' : '' ?>>
                Avisar o inscrito por e-mail quando o pagamento for confirmado
            </label>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <label>
                <input type="hidden" name="aviso_vencimento_habilitado" value="0">
                <input type="checkbox" name="aviso_vencimento_habilitado" value="1" <?php echo $objEvConf->getAvisoVencimentoHabilitado() == 1 ? 'checked' : '' ?>>
                Avisar o inscrito por e-mail quando o pagamento estiver perto de vencer (5 dias e 1 dia antes)
            </label>
        </td>
    </tr>
    <?php if ($objEvConf->getGatewayPagamento()!=''){ ?>
    <tr class="opcoesPagamento">
        <td colspan="2">
            Opções de pagamento:
            <?php if (in_array($objEvConf->getGatewayPagamento(), ['PAGSEGURO'])) { ?>
            <br>
            <label>
                <input  type="checkbox" <?php echo verifItemChecked('pix', json_decode($objEvConf->getOpcaoPagamento()), false) ?> name="opcao_pagamento[]" value="pix">
                PIX
            </label>
            <?php } ?>
            <?php if (in_array($objEvConf->getGatewayPagamento(), ['PAGSEGURO'])) { ?>
            <br>
            <label>
                <input  type="checkbox" <?php echo verifItemChecked('cartaoCredito', json_decode($objEvConf->getOpcaoPagamento()), false) ?> name="opcao_pagamento[]" value="cartaoCredito">
                Cartão de Crédito
            </label>
            <br>
            <?php } ?>
            <?php if ($objEvConf->getGatewayPagamento()=='NENHUM'){ ?>
            <label>
                <input  type="checkbox" <?php echo verifItemChecked('cartaoDebito', json_decode($objEvConf->getOpcaoPagamento()), false) ?> name="opcao_pagamento[]" value="cartaoDebito">
                Cartão de Débito
            </label>
            <?php } ?>
            <?php if (in_array($objEvConf->getGatewayPagamento(), ['PAGSEGURO', 'ITAU-SHOPLINE', 'ITAU-BOLECODE'])) { ?>
            <br>
            <label>
                <input  type="checkbox"  <?php echo verifItemChecked('boleto',json_decode($objEvConf->getOpcaoPagamento()), false)?> name="opcao_pagamento[]" value="boleto">
                Boleto Bancário
            </label>
            <?php } ?>
            <?php if ($objEvConf->getGatewayPagamento()=='NENHUM'){ ?>
            <br>
            <label>
                <input  type="checkbox" <?php echo verifItemChecked('paypal', json_decode($objEvConf->getOpcaoPagamento()), false) ?> name="opcao_pagamento[]" value="paypal">
                PayPal
            </label>
            <?php } ?>
            <?php if ($objEvConf->getGatewayPagamento()=='NENHUM'){ ?>
            <br>
            <label>
                <input  type="checkbox" <?php echo verifItemChecked('transferencia', json_decode($objEvConf->getOpcaoPagamento()), false) ?> name="opcao_pagamento[]" value="transferencia">
                Transferência Bancária
            </label>
            <?php } ?>
            <?php if ($objEvConf->getGatewayPagamento()=='NENHUM'){ ?>
            <br>
            <label>
                <input  type="checkbox" <?php echo verifItemChecked('deposito', json_decode($objEvConf->getOpcaoPagamento()), false) ?> name="opcao_pagamento[]" value="deposito">
                Deposito em Conta
            </label>
            <?php } ?>
            <?php if ($objEvConf->getGatewayPagamento()=='NENHUM'){ ?>
            <br>
            <label>
                <input  type="checkbox" <?php echo verifItemChecked('dinheiro', json_decode($objEvConf->getOpcaoPagamento()), false) ?> name="opcao_pagamento[]" value="dinheiro">
                Dinheiro (Pagamento na entrega)
            </label>
            <?php } ?>
            <br>
        </td>
    </tr>
    <?php } ?>
    <!--
    <tr>
        <td>Status boleto:        </td>
        <td>
            <input type='text' name='boleto_situacao'                  value='<?php echo $objEvConf->getBoletoSituacao() ?>'      maxlength='255'   size='60'  onBlur="trim(this);" >
        </td>
    </tr>
    <tr><td>Data inicio boleto:   </td><td> <input type='text' name='boleto_dat_ini'                   value='<?php echo $objEvConf->getBoletoDatIni() ?>'        maxlength='255'   size='60'  onBlur="trim(this);" ></td></tr>
    <tr><td>Data fim boleto:      </td><td> <input type='text' name='boleto_dat_fim'                   value='<?php echo $objEvConf->getBoletoDatFim() ?>'        maxlength='255'   size='60'  onBlur="trim(this);" ></td></tr>
    <tr><td>Dias para vencimento: </td><td> <input type='text' name='boleto_dias_pagamento'            value='<?php echo $objEvConf->getBoletoDiasPagamento() ?>' maxlength='255'   size='60'  onBlur="trim(this);" ></td></tr>
    <tr><td>Mensagem no boleto:   </td><td> <input type='text' name='boleto_msg_btn'                   value='<?php echo $objEvConf->getBoletoMsgBtn() ?>'        maxlength='255'   size='60'  onBlur="trim(this);" ></td></tr>
    <tr><td colspan=2>Observação: </td></tr>
    <tr><td colspan=2>
        <textarea rows=5 cols=70 name='descricao' size='60' onBlur='trim(this);' onkeypress='javascript: return isAlfa(window.event.keyCode);' class='textob'><?php echo $objEvConf->getDescricao() ?></textarea>
    </td></tr>
    -->

</table>

<?php
// URL de retorno padrão: baseada no prefixo do evento ou gestor
$appboleto_escape_url_default = '';
if ($objEv->getUrlPrefixo()) {
    $appboleto_escape_url_default = 'https://opctec.net.br/' . $objEv->getUrlPrefixo() . '/inscricoes/';
} elseif ($objEv->getGestor()) {
    $appboleto_escape_url_default = 'https://opctec.net.br/' . strtolower($objEv->getGestor()) . '/' . $objEv->getCodEvento() . '/inscricoes/';
}
?>

<?php if (@FWA_AutenticacaoGeral($_SESSION['_cod_usuario'], enumSistema::SIS_GERENCIAR_ADM_GERAL, 5, false, false)
      && in_array($objEvConf->getGatewayPagamento(), ['PAGSEGURO'])) { ?>
<table border='0' width='570' cellspacing="10" cellpadding="10" class="dataTable" style="margin-top: 15px;">
    <tr>
        <td colspan="2">
            <span style="font-size:15px;font-weight: bold;">Sincronizar com Gateway de Pagamento (PagSeguro)</span>
            <br><small style="color:#666;">Evento: <?php echo (int) COD_EVENTO ?> | Gestor: <?php echo strtoupper(WB_GRUPO) ?></small>
        </td>
    </tr>
    <tr>
        <td>URL de Retorno (Área do Visitante):</td>
        <td>
            <input type='text' id='appboleto_escape_url' maxlength='255' size='60'
                   placeholder='https://opctec.net.br/evento/inscricoes/'
                   value='<?php echo htmlspecialchars($appboleto_escape_url_default) ?>'>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <button type="button" id="btn-sync-appboleto" style="padding: 6px 20px; cursor: pointer; background-color: #337ab7; color: white; border: 1px solid #2e6da4; border-radius: 4px;">
                Sincronizar com Gateway
            </button>
            <span id="appboleto-sync-status" style="margin-left: 10px;"></span>
        </td>
    </tr>
</table>

<!-- Modal confirmacao sincronizar gateway -->
<div class="modal fade" id="modalConfirmarSync" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background:#337ab7; color:#fff;">
                <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" style="color:#fff;">&times;</button>
                <h4 class="modal-title"><i class="fa fa-refresh"></i> Sincronizar Gateway</h4>
            </div>
            <div class="modal-body">
                <p>Deseja sincronizar o evento <strong><?php echo (int) COD_EVENTO ?></strong> com o gateway de pagamento?</p>
                <p class="text-muted" style="font-size:12px;">Esta a&ccedil;&atilde;o ir&aacute; atualizar as configura&ccedil;&otilde;es de pagamento no gateway.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="executarSyncGateway()"><i class="fa fa-refresh"></i> Sincronizar</button>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('btn-sync-appboleto').addEventListener('click', function() {
    try {
        var modalEl = document.getElementById('modalConfirmarSync');
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        } else if (typeof $ !== 'undefined') {
            $('#modalConfirmarSync').modal('show');
        }
    } catch(e) {
        if (typeof $ !== 'undefined') {
            $('#modalConfirmarSync').modal('show');
        }
    }
});

function executarSyncGateway() {
    try {
        var modalEl = document.getElementById('modalConfirmarSync');
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            bootstrap.Modal.getInstance(modalEl).hide();
        } else if (typeof $ !== 'undefined') {
            $('#modalConfirmarSync').modal('hide');
        }
    } catch(e) {}

    var btn = document.getElementById('btn-sync-appboleto');
    var statusEl = document.getElementById('appboleto-sync-status');
    var escapeUrl = document.getElementById('appboleto_escape_url').value;

    btn.disabled = true;
    btn.textContent = 'Sincronizando...';
    statusEl.innerHTML = '';

    var formData = new FormData();
    formData.append('cod_evento', '<?php echo (int) COD_EVENTO ?>');
    formData.append('evento_type', '<?php echo strtoupper(WB_GRUPO) ?>');
    formData.append('escape_url', escapeUrl);

    var apiUrl = '<?php echo WB_URL_DOMINIO ?>/<?php echo WB_GRUPO ?>/admin/api/appboleto/sync';

    fetch(apiUrl, {
        method: 'POST',
        body: formData
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
        if (data.statusMessage) {
            statusEl.innerHTML = '<span style="color: green; font-weight: bold;">' + data.message + '</span>';
        } else {
            statusEl.innerHTML = '<span style="color: red; font-weight: bold;">' + data.message + '</span>';
        }
    })
    .catch(function(error) {
        statusEl.innerHTML = '<span style="color: red; font-weight: bold;">Erro de conexão: ' + error.message + '</span>';
    })
    .finally(function() {
        btn.disabled = false;
        btn.textContent = 'Sincronizar com Gateway';
    });
}
</script>
<?php } ?>

<script>
// Toggle campos por gateway selecionado
(function() {
    var sel = document.getElementById('gateway_pagamento');
    if (!sel) return;
    function toggleCampos() {
        var val = sel.value;
        var trs;
        // PagSeguro
        trs = document.querySelectorAll('.pagSeguro');
        for (var i = 0; i < trs.length; i++) trs[i].style.display = (val === 'PAGSEGURO') ? '' : 'none';
        // Itau ShopLine
        trs = document.querySelectorAll('.itauShopLine');
        for (var i = 0; i < trs.length; i++) trs[i].style.display = (val === 'ITAU-SHOPLINE') ? '' : 'none';
        // Itau BoleCode
        trs = document.querySelectorAll('.itauBolecode');
        for (var i = 0; i < trs.length; i++) trs[i].style.display = (val === 'ITAU-BOLECODE') ? '' : 'none';
        // Opcoes pagamento
        trs = document.querySelectorAll('.opcoesPagamento');
        for (var i = 0; i < trs.length; i++) trs[i].style.display = (val !== '') ? '' : 'none';
    }
    sel.addEventListener('change', toggleCampos);
    toggleCampos();
})();
</script>
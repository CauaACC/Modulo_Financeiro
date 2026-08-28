<?php
/**
 * Form de pagamento Itaú BoleCode (boleto híbrido PIX + boleto bancário)
 *
 * Include na área do inscrito (inscricao_botao_boleto.php)
 * Fluxo:
 * 1. Extrai dados do inscrito
 * 2. Verifica se já existe boleto BoleCode registrado (tab_boletos)
 * 3. Se não existe, chama a API do Itaú para registrar
 * 4. Salva retorno no banco (tab_boletos)
 * 5. Exibe tela de pagamento (QR Code PIX + link boleto)
 */

$mostrarBolecodeItau = true;
$debug = false;

if (!$mostrarBolecodeItau) {
    ?>
    <br>Pagamento não disponível.
    <span style="font-size:17px;color:red;">
        <br>Sistema de pagamento em manutenção.
    </span>
    <?php
    return;
}

echo $debug ? __LINE__ . '-' : '';

// --- Extrair dados do inscrito ---
$codInscrito    = $objInscrito->getCodInscrito();
$nomeSacado     = $objInscrito->getNomeInscrito();
$cpfCnpj        = $objInscrito->getCpf();
$enderecoSacado = $objInscrito->getEndereco();
if ($objInscrito->getNumero()) {
    $enderecoSacado .= ', ' . $objInscrito->getNumero();
}
$bairroSacado  = $objInscrito->getBairro();
$cepSacado     = $objInscrito->getCep();
$cidadeSacado  = $objInscrito->getCidade();
$estadoSacado  = $objInscrito->getEstado();

$titulo_pagamento = $objLI->getDescricao();
$evento_titulo    = $objEv->getTitulo();
$valor            = $objLI->getValorTotal();
$pedido           = $objLI->getId();
$dataVencimento   = $objLI->getDatVencimento();
$dataVencimento   = formatDateHoraTrans($dataVencimento, 'YYYY-MM-DD', 'DD/MM/YYYY');

// Dados institucionais (pessoa jurídica)
if ($objInscrito->getInstitucional() == 1) {
    $nomeSacado     = $objInscrito->getNomeRecibo();
    $cpfCnpj        = limparCpfCnpj($objInscrito->getCpfRecibo());
    $enderecoSacado = $objInscrito->getEnderecoRecibo();
    $enderecoSacado = $objInscrito->getNumeroRecibo() ? $enderecoSacado . ', ' . $objInscrito->getNumeroRecibo() : $enderecoSacado;
    $bairroSacado   = $objInscrito->getBairroRecibo();
    $cepSacado      = $objInscrito->getCepRecibo();
    $cidadeSacado   = $objInscrito->getCidadeRecibo();
    $estadoSacado   = $objInscrito->getEstadoRecibo();
}

// Limpar CPF/CEP
$cpfCnpj   = preg_replace('/\D/', '', $cpfCnpj);
$cepSacado = preg_replace('/\D/', '', $cepSacado);
$cepSacado = str_pad($cepSacado, 8, '0');

// Tipo documento
$tipoDocumento = (strlen($cpfCnpj) <= 11) ? '01' : '02';

// Instruções
$instrucao1 = $objLI->getDescricao();
$instrucao2 = 'Após o vencimento multa de 2% + juros 1% ao mês conforme contrato';
$instrucao3 = 'Não receber após 30 dias do vencimento';

// Montar $fws_ para o controller
$fws_['codInscrito']    = $codInscrito;
$fws_['cod_inscrito']   = $codInscrito;
$fws_['pedido']         = $pedido;
$fws_['sacado']         = $nomeSacado;
$fws_['cpfCnpj']        = $cpfCnpj;
$fws_['tipoDocumento']  = $tipoDocumento;
$fws_['endereco']       = $enderecoSacado;
$fws_['bairro']         = $bairroSacado;
$fws_['cep']            = $cepSacado;
$fws_['cidade']         = $cidadeSacado;
$fws_['estado']         = $estadoSacado;
$fws_['dataVencimento'] = $dataVencimento;
$fws_['valorDocumento'] = $valor;
$fws_['valorBoleto']    = $valor;
$fws_['instrucoes1']    = $instrucao1;
$fws_['instrucoes2']    = $instrucao2;
$fws_['instrucoes3']    = $instrucao3;
$fws_['demonstrativo1'] = $objLI->getDescricao();

// --- Verificar se ja tem pagamento registrado (leve, so consulta banco) ---
$pedidoJaPago = false;
try {
    $objPagVerif = new Pagamento();
    $objPagVerif->setCodEvento(COD_EVENTO);
    $objPagVerif->setPedidoId($pedido);
    $objPagVerif->getPagamentoByPedidoId();
    if ($objPagVerif->getCodPagamento() != '') {
        $pedidoJaPago = true;
    }
} catch (Exception $e) {}

if ($pedidoJaPago) {
?>
<div class="alert alert-success" style="margin:10px 0;">
    <strong>Pagamento confirmado!</strong> Valor: R$ <?php echo number_format((float)$objPagVerif->getValor(), 2, ',', '.') ?>
</div>
<?php
} else {
?>
<!-- Botao que abre modal -->
<?php
$exibirPixConfig = $objEvConf->getItauBolecodeExibirPix() == 1;
$txtBotaoBolecode = $exibirPixConfig ? 'Pagar via PIX ou Boleto' : 'Pagar via Boleto';
?>
<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalBolecodeForm" data-bs-toggle="modal" data-bs-target="#modalBolecodeForm" style="margin:10px 0;">
    <?php echo $txtBotaoBolecode ?>
</button>

<!-- Modal BoleCode -->
<div class="modal fade" id="modalBolecodeForm" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog" role="document" style="max-width:550px;">
    <div class="modal-content" style="border-radius:16px; overflow:hidden; border:none; box-shadow:0 8px 30px rgba(0,0,0,0.12);">
      <div class="modal-header" style="background:linear-gradient(135deg, #065f46, #047857); color:#fff; display:flex; justify-content:space-between; align-items:center; padding:20px 24px; border-radius:0;">
        <h4 class="modal-title" style="margin:0; font-size:16px; font-weight:600;"><i class="fa fa-credit-card" style="margin-right:8px; opacity:0.8;"></i>Pagamento via <?php echo $exibirPixConfig ? 'PIX ou Boleto' : 'Boleto' ?></h4>
        <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Fechar" style="color:rgba(255,255,255,0.8); font-size:20px; background:rgba(255,255,255,0.15); border:none; width:32px; height:32px; border-radius:8px; display:flex; align-items:center; justify-content:center;"><span>&times;</span></button>
      </div>
      <div class="modal-body" style="padding:20px 24px 24px;">
        <!-- Info fixa -->
        <div style="display:flex; gap:12px; margin-bottom:18px;">
            <div style="flex:1; padding:14px; border-radius:10px; background:#f8f9fb; border:1px solid #eef0f4;">
                <small style="color:#999; font-size:11px; font-weight:500; text-transform:uppercase; letter-spacing:0.3px;">Valor</small>
                <strong style="display:block; margin-top:4px; font-size:18px; font-weight:700; color:#065f46;">R$ <?php echo number_format((float)$valor, 2, ',', '.') ?></strong>
            </div>
            <div style="flex:1; padding:14px; border-radius:10px; background:#f8f9fb; border:1px solid #eef0f4;">
                <small style="color:#999; font-size:11px; font-weight:500; text-transform:uppercase; letter-spacing:0.3px;">Vencimento</small>
                <strong style="display:block; margin-top:4px; font-size:16px; font-weight:700;"><?php echo $dataVencimento ?></strong>
            </div>
        </div>
        <!-- Conteudo dinamico (carregado via AJAX) -->
        <div id="bolecodeConteudo">
            <div style="text-align:center; padding:30px;">
                <i class="fa fa-spinner fa-spin fa-2x" style="color:#047857;"></i>
                <p style="margin-top:10px; color:#666;">Preparando pagamento...</p>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Mensagem copiado -->
<div id="msgCopiadoBolecode" style="display:none; position:fixed; top:20px; left:50%; transform:translateX(-50%); background:#28a745; color:#fff; padding:10px 25px; border-radius:6px; z-index:10060; font-weight:bold;">
    Copiado!
</div>

<script>
// Quando o modal abre, carrega dados via AJAX
$('#modalBolecodeForm').on('shown.bs.modal', function() {
    var conteudo = $('#bolecodeConteudo');
    // Se ja tem conteudo carregado (nao e spinner), nao recarrega
    if (conteudo.find('.bolecode-carregado').length > 0) return;

    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {
            'c': 'GerenciarBoletoItauBolecode',
            'm': 'registrarOuCarregar',
            'nomeBanco': 'ITAU-BOLECODE',
            'pedido_id': '<?php echo $pedido ?>',
            'cod_inscrito': '<?php echo $codInscrito ?>',
            'sacado': '<?php echo addslashes($nomeSacado) ?>',
            'cpfCnpj': '<?php echo $cpfCnpj ?>',
            'endereco': '<?php echo addslashes($enderecoSacado) ?>',
            'bairro': '<?php echo addslashes($bairroSacado) ?>',
            'cep': '<?php echo $cepSacado ?>',
            'cidade': '<?php echo addslashes($cidadeSacado) ?>',
            'estado': '<?php echo $estadoSacado ?>',
            'valorBoleto': '<?php echo $valor ?>',
            'dataVencimento': '<?php echo $dataVencimento ?>',
            'instrucoes1': '<?php echo addslashes($instrucao1) ?>',
            'demonstrativo1': '<?php echo addslashes($objLI->getDescricao()) ?>',
            '_submit_check': '1'
        },
        dataType: "json",
        success: function(data) {
            if (!data.statusMessage) {
                conteudo.html('<div class="alert alert-danger">' + (data.mensagem || 'Erro ao processar.') + '</div>');
                return;
            }
            var html = '<div class="bolecode-carregado">';
            // QR Code
            if (data.qrcode_base64) {
                html += '<div style="text-align:center; padding:20px; border:2px dashed #a7f3d0; border-radius:12px; margin-bottom:16px; background:#f0fdf4;">';
                html += '<div style="display:inline-flex; align-items:center; gap:6px; font-weight:600; font-size:13px; margin-bottom:12px; padding:4px 12px; border-radius:20px; background:rgba(4,120,87,0.08); color:#065f46;"><i class="fa fa-qrcode"></i> Pague instantaneamente via PIX</div>';
                html += '<img src="data:image/png;base64,' + data.qrcode_base64 + '" alt="QR Code PIX" style="max-width:220px; border-radius:8px;">';
                html += '</div>';
            }
            // PIX copia e cola
            if (data.qrcode_texto) {
                html += '<div onclick="copiarTextoBolecode(\'' + data.qrcode_texto.replace(/'/g, "\\'") + '\')" style="background:#ecfdf5; padding:14px; border-radius:10px; margin-bottom:14px; cursor:pointer; word-break:break-all; font-size:12px;">';
                html += '<div style="color:#065f46; font-weight:600; font-size:12px; display:flex; align-items:center; gap:6px; margin-bottom:6px;"><i class="fa fa-copy"></i> PIX Copia e Cola (clique para copiar)</div>';
                html += '<span style="color:#666; line-height:1.4;">' + data.qrcode_texto + '</span>';
                html += '</div>';
            }
            // Linha digitavel
            if (data.linha_digitavel) {
                html += '<div onclick="copiarTextoBolecode(\'' + data.linha_digitavel + '\')" style="background:#fffbf0; border:1px solid #f0e6cc; padding:14px; border-radius:10px; margin-bottom:14px; text-align:center; font-family:Consolas,monospace; font-size:12px; cursor:pointer; letter-spacing:0.5px; color:#8a7340;" title="Clique para copiar">';
                html += '<small style="color:#b09860; display:block; margin-bottom:6px; font-family:Arial,sans-serif; font-weight:500; font-size:11px; text-transform:uppercase; letter-spacing:0.3px;">Linha digitavel (clique para copiar)</small>';
                html += data.linha_digitavel;
                html += '</div>';
            }
            // Mensagem sobre prazo de confirmacao
            var msgPrazo = data.exibir_pix ? 'A confirmacao do pagamento, via PIX ou Boleto, pode levar ate 2 dias uteis.' : 'A confirmacao do pagamento via Boleto pode levar ate 2 dias uteis.';
            html += '<div style="background:#f0f7ff; border:1px solid #d4e6f7; color:#3a6d9c; padding:10px 14px; border-radius:8px; font-size:12px; margin-bottom:14px; display:flex; align-items:center; gap:8px;"><i class="fa fa-info-circle" style="font-size:14px;"></i> ' + msgPrazo + '</div>';
            // Verificar pagamento (oculto ate resolver consulta API)
            html += '<div id="divVerificarPagamento" style="margin-bottom:10px; display:none;">';
            html += '<button type="button" class="btn btn-success btn-block" style="width:100%; padding:13px; border-radius:10px; font-weight:600;" onclick="verificarPagamentoBolecode(\'' + data.cod_boleto + '\')">Verificar Pagamento</button>';
            html += '</div>';
            html += '<div id="resultadoVerificacao" style="display:none; margin-bottom:10px;"></div>';
            // Boleto completo
            html += '<form action="<?php echo WB_URL_COMMAND ?>" method="post" target="_blank">';
            html += '<input type="hidden" name="c" value="GerenciarBoleto">';
            html += '<input type="hidden" name="m" value="pdfBoleto">';
            html += '<input type="hidden" name="nomeBanco" value="ITAU-BOLECODE">';
            html += '<input type="hidden" name="cod_evento" value="<?php echo COD_EVENTO ?>">';
            html += '<input type="hidden" name="cod_inscrito" value="<?php echo $codInscrito ?>">';
            html += '<input type="hidden" name="cod_boleto" value="' + data.cod_boleto + '">';
            html += '<button type="submit" class="btn btn-block" style="width:100%; padding:13px; border:none; border-radius:10px; background:#065f46; color:#fff; font-size:14px; font-weight:600; cursor:pointer;">Visualizar Boleto Completo</button>';
            html += '</form>';
            html += '</div>';
            conteudo.html(html);
        },
        error: function() {
            conteudo.html('<div class="alert alert-danger">Erro de comunicacao. Tente novamente.</div>');
        }
    });
});

function verificarPagamentoBolecode(codBoleto) {
    var btn = $('#divVerificarPagamento button');
    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Verificando...');
    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {
            'c': 'GerenciarBoletoItauBolecode',
            'm': 'verificarPagamento',
            'nomeBanco': 'ITAU-BOLECODE',
            'cod_boleto': codBoleto,
            '_submit_check': '1'
        },
        dataType: "json",
        success: function(data) {
            var div = $('#resultadoVerificacao');
            div.show();
            if (data.pago) {
                div.html('<div class="alert alert-success"><strong>Pagamento confirmado!</strong><br>Valor: R$ ' + data.valor_pago + '<br>Data: ' + data.data_pagamento + '</div>');
                btn.html('Pagamento Confirmado').removeClass('btn-success').addClass('btn-default').prop('disabled', true);
                setTimeout(function() { location.reload(); }, 3000);
            } else {
                div.html('<div class="alert alert-warning">Pagamento ainda nao identificado.<br><small>A confirmação do pagamento via PIX ou boleto pode levar até 2 dias úteis</small></div>');
                btn.prop('disabled', false).html('Verificar Pagamento');
            }
        },
        error: function() {
            $('#resultadoVerificacao').show().html('<div class="alert alert-danger">Erro ao verificar. Tente novamente.</div>');
            btn.prop('disabled', false).html('Verificar Pagamento');
        }
    });
}

function copiarTextoBolecode(texto) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(texto);
    } else {
        var el = document.createElement('textarea');
        el.value = texto;
        document.body.appendChild(el);
        el.select();
        document.execCommand('copy');
        document.body.removeChild(el);
    }
    var msg = document.getElementById('msgCopiadoBolecode');
    msg.style.display = 'block';
    setTimeout(function() { msg.style.display = 'none'; }, 2000);
}
</script>
<?php
}
echo "<hr>";
?>

<?php
require_once(dirname(__FILE__) . "/../../include/config.php");
$debug = false;

require_once 'inscricao_congresso_tit_area_inscrito.php';

// Carregar dados do inscrito
$objInscrito = null;
if ($_SESSION['_cod_inscrito'] != '') {
    $objInscrito = new Inscrito();
    $objInscrito->setCodInscrito($_SESSION['_cod_inscrito']);
    $objInscrito->setCodEvento(COD_EVENTO);
    $objInscrito->getInscritoById($debug);
    $objInscrito->carregarOjbPerfil($debug);
}

// Verificar se é isento
$isIsento = false;
if ($objInscrito) {
    try {
        $objIsento = new Isento();
        $objIsento->setCpf($objInscrito->getCpf());
        $objIsento->setCodEvento(COD_EVENTO);
        $objIsento->carregarIsentoByCpf($debug);
        if ($objIsento->getCodInscrito() != '') {
            $isIsento = true;
        }
    } catch (Exception $e) {
        // Não é isento
    }
}

// Identificar pedido da compra atual (via URL ou sessão)
$pedidoIdAtual = isset($fwsGet_['pedido_id']) ? $fwsGet_['pedido_id'] : '';
if (empty($pedidoIdAtual) && !empty($_SESSION['_ultimo_pedido_id'])) {
    $pedidoIdAtual = $_SESSION['_ultimo_pedido_id'];
}

// Carregar ingressos — filtrar pelo pedido atual quando disponível
$listaIngressos = [];
$totalIngressos = 0;
$valorTotalIngressos = 0;
if ($objInscrito) {
    try {
        $objIngresso = new Ingresso();
        $objIngresso->setEventoId(COD_EVENTO);
        if (!empty($pedidoIdAtual)) {
            // Carregar apenas ingressos deste pedido
            $objIngresso->setPedidoId($pedidoIdAtual);
            $objIngresso->carregarListaIngressosByPedidoId($debug);
        } else {
            // Fallback: carregar todos do inscrito (fluxo gratuito sem pedido)
            $objIngresso->setInscritoId($objInscrito->getCodInscrito());
            $objIngresso->carregarListaIngressosByInscritoId($debug);
        }
        $listaIngressos = $objIngresso->getListaIngressos();
        if (is_array($listaIngressos)) {
            $totalIngressos = count($listaIngressos);
            foreach ($listaIngressos as $ing) {
                $valorTotalIngressos += floatval($ing->getValor());
            }
        }
    } catch (Exception $e) {
        // Sem ingressos — fluxo normal
    }
}

// Carregar pedido atual para link de pagamento
$listaPedidos = [];
$temPedidoPendente = false;
if ($objInscrito && !$isIsento && !$OP_INCRICAO_GRATUITA && $valorTotalIngressos > 0) {
    try {
        $objPedido = new Pedido();
        $objPedido->setInscritoId($objInscrito->getCodInscrito());
        $objPedido->setEventoId($objInscrito->getCodEvento());
        $objPedido->carregarListaPedidosByInscritoId($debug);
        $listaPedidos = $objPedido->getListaPedidos();
        if (is_array($listaPedidos) && !empty($pedidoIdAtual)) {
            // Filtrar apenas o pedido da compra atual
            $listaPedidos = array_filter($listaPedidos, function($ped) use ($pedidoIdAtual) {
                return $ped->getId() == $pedidoIdAtual;
            });
        }
        if (is_array($listaPedidos)) {
            foreach ($listaPedidos as $ped) {
                if ($ped->getStatus() != 'DESATIVADA' && empty($ped->getCodPagamento()) && $ped->getStatus() != 'Pago via acerto') {
                    $temPedidoPendente = true;
                    break;
                }
            }
        }
    } catch (Exception $e) {
        // Sem pedidos
    }
}
?>
<style>
.sympla-progress { display:flex; justify-content:center; margin-bottom:25px; }
.sympla-progress .step { text-align:center; padding:8px 20px; position:relative; flex:1; max-width:200px; }
.sympla-progress .step .num { display:inline-block; width:32px; height:32px; line-height:32px; border-radius:50%; color:#fff; font-weight:bold; margin-bottom:4px; }
.sympla-progress .step.done .num { background:#2D937E; }
.sympla-progress .step .label-step { display:block; font-size:12px; color:#666; }
.sympla-progress .step.done .label-step { color:#2D937E; }

.sympla-confirmacao {
    text-align: center;
    padding: 40px 20px;
}
.sympla-confirmacao .check-icon {
    display: inline-block;
    width: 80px;
    height: 80px;
    line-height: 80px;
    border-radius: 50%;
    background: #2D937E;
    color: #fff;
    font-size: 40px;
    margin-bottom: 20px;
}
.sympla-confirmacao h2 { color: #2D937E; margin-bottom: 20px; }
.sympla-confirmacao .info-item { font-size: 15px; color: #555; margin-bottom: 8px; }
.sympla-confirmacao .info-label { font-weight: bold; color: #333; }
.sympla-aviso-pagamento {
    background: #fff3cd;
    border: 1px solid #ffc107;
    border-radius: 6px;
    padding: 15px 20px;
    margin: 20px auto;
    max-width: 700px;
    text-align: center;
    color: #856404;
}

/* Tabela de ingressos */
.ingressos-table { width:100%; max-width:700px; margin:20px auto; border-collapse:collapse; text-align:left; }
.ingressos-table th, .ingressos-table td { padding:10px 14px; border-bottom:1px solid #eee; }
.ingressos-table th { font-size:13px; color:#888; font-weight:normal; border-bottom:2px solid #ddd; }
.ingressos-table td { font-size:14px; color:#333; }
.ingressos-table .ing-total td { font-weight:bold; border-top:2px solid #2D937E; }

/* Identificação de portador */
.ingresso-form-inline { background:#f9f9f9; padding:12px; border:1px solid #e0e0e0; border-radius:4px; margin:5px 0; }
.ingresso-form-inline .form-group { margin-bottom:8px; text-align:left; }
.ingresso-form-inline label { font-size:12px; color:#555; font-weight:bold; display:block; margin-bottom:3px; }
.ingresso-form-inline input { width:100%; padding:6px 10px; border:1px solid #ccc; border-radius:4px; font-size:13px; }
.btn-identificar { background:#2D937E; color:#fff; border:none; padding:6px 16px; border-radius:4px; font-size:13px; cursor:pointer; }
.btn-identificar:hover { background:#247a68; }
.btn-cancelar-id { background:#999; color:#fff; border:none; padding:6px 16px; border-radius:4px; font-size:13px; cursor:pointer; margin-left:5px; }
.btn-cancelar-id:hover { background:#777; }
.portador-identificado { color:#2D937E; font-size:12px; }
.portador-info-mini { font-size:11px; color:#888; }

/* Pagamento */
.pagamento-section { max-width:700px; margin:25px auto; text-align:left; }
.pagamento-section h4 { color:#333; margin-bottom:15px; text-align:center; }
.pagamento-opcoes { text-align:center; margin:10px 0 15px; }
.pagamento-opcoes label { margin:0 10px; font-size:14px; cursor:pointer; }
</style>

<div class="panel-group">
    <div class="panel panel-default">
        <div class="panel-body">
            <!-- Barra de progresso completa -->
            <div class="sympla-progress">
                <div class="step done">
                    <span class="num">&#10003;</span>
                    <span class="label-step">Selecionar Ingresso</span>
                </div>
                <div class="step done">
                    <span class="num">&#10003;</span>
                    <span class="label-step">Dados Pessoais</span>
                </div>
                <div class="step done">
                    <span class="num">&#10003;</span>
                    <span class="label-step">Confirmação</span>
                </div>
            </div>

            <?php
            if ($fws_['msg']) {
                FWA_Mostrar_Mensagem($fws_['msg']);
            }
            ?>

            <div class="sympla-confirmacao">
                <div class="check-icon">&#10003;</div>
                <h2>Inscrição Realizada!</h2>

                <?php if ($objInscrito) { ?>
                    <div class="info-item">
                        <span class="info-label">Nome:</span>
                        <?php echo $objInscrito->getNomeInscrito(); ?>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Código da Inscrição:</span>
                        <?php echo $objInscrito->getCodInscrito(); ?>
                    </div>
                    <?php if ($objInscrito->getObjPerfil()) { ?>
                        <div class="info-item">
                            <span class="info-label">Categoria:</span>
                            <?php echo $objInscrito->getObjPerfil()->getTitulo(); ?>
                        </div>
                    <?php } ?>
                    <div class="info-item">
                        <span class="info-label">E-mail:</span>
                        <?php echo $objInscrito->getEMail(); ?>
                    </div>

                    <?php if ($totalIngressos > 0) { ?>
                        <!-- Lista de ingressos com identificação de portador -->
                        <h4 style="color:#333;margin-top:25px;margin-bottom:10px;">Ingressos</h4>
                        <table class="ingressos-table" id="tblIngressos">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Portador</th>
                                    <th>Categoria</th>
                                    <th style="text-align:right;">Valor</th>
                                    <th style="text-align:center;">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $num = 1;
                                foreach ($listaIngressos as $ing) {
                                    $ingressoId = $ing->getId();
                                    $nomePortador = $ing->getNome();
                                    $emailPortador = $ing->getEmail();
                                    $telPortador = $ing->getTelefone();
                                    $identificado = intval($ing->getPortadorIdentificado());
                                ?>
                                    <tr id="ing-row-<?php echo $ingressoId; ?>">
                                        <td><?php echo $num; ?></td>
                                        <td id="ing-portador-<?php echo $ingressoId; ?>">
                                            <?php echo htmlspecialchars($nomePortador); ?>
                                            <?php if ($identificado == 1 && $emailPortador) { ?>
                                                <br><span class="portador-info-mini"><?php echo htmlspecialchars($emailPortador); ?></span>
                                                <?php if ($telPortador) { ?>
                                                    <br><span class="portador-info-mini"><?php echo htmlspecialchars($telPortador); ?></span>
                                                <?php } ?>
                                                <br><span class="portador-identificado">&#10003; Identificado</span>
                                            <?php } ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($ing->getCategoria()); ?></td>
                                        <td style="text-align:right;">
                                            <?php if (floatval($ing->getValor()) <= 0) { ?>
                                                Gratuito
                                            <?php } else { ?>
                                                R$ <?php echo number_format(floatval($ing->getValor()), 2, ',', '.'); ?>
                                            <?php } ?>
                                        </td>
                                        <td style="text-align:center;">
                                            <?php if ($identificado == 1) { ?>
                                                <span class="portador-identificado">&#128274;</span>
                                            <?php } else { ?>
                                                <button type="button" class="btn-identificar" onclick="abrirFormIdentificar(<?php echo $ingressoId; ?>)">
                                                    Identificar
                                                </button>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                    <!-- Linha oculta para formulário inline -->
                                    <tr id="ing-form-row-<?php echo $ingressoId; ?>" style="display:none;">
                                        <td colspan="5">
                                            <div class="ingresso-form-inline">
                                                <div class="form-group">
                                                    <label>Nome do Portador *</label>
                                                    <input type="text" id="inp-nome-<?php echo $ingressoId; ?>"
                                                           value="<?php echo ($nomePortador != 'A identificar') ? htmlspecialchars($nomePortador) : ''; ?>"
                                                           placeholder="Nome completo do portador">
                                                </div>
                                                <div class="form-group">
                                                    <label>E-mail do Portador *</label>
                                                    <input type="email" id="inp-email-<?php echo $ingressoId; ?>"
                                                           value="<?php echo htmlspecialchars($emailPortador); ?>"
                                                           placeholder="email@exemplo.com">
                                                </div>
                                                <div class="form-group">
                                                    <label>Telefone do Portador *</label>
                                                    <input type="text" id="inp-tel-<?php echo $ingressoId; ?>"
                                                           value="<?php echo htmlspecialchars($telPortador); ?>"
                                                           placeholder="(00) 00000-0000">
                                                </div>
                                                <div style="text-align:right;margin-top:10px;">
                                                    <button type="button" class="btn-cancelar-id" onclick="fecharFormIdentificar(<?php echo $ingressoId; ?>)">Cancelar</button>
                                                    <button type="button" class="btn-identificar" id="btn-confirmar-<?php echo $ingressoId; ?>" onclick="confirmarIdentificacao(<?php echo $ingressoId; ?>)">Confirmar</button>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php $num++; } ?>
                                <?php if ($valorTotalIngressos > 0) { ?>
                                    <tr class="ing-total">
                                        <td></td>
                                        <td>Total</td>
                                        <td></td>
                                        <td style="text-align:right;color:#2D937E;">R$ <?php echo number_format($valorTotalIngressos, 2, ',', '.'); ?></td>
                                        <td></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    <?php } ?>

                    <?php
                    // ============================================================
                    // LINK DE PAGAMENTO DIRETO
                    // ============================================================
                    if ($temPedidoPendente && is_array($listaPedidos)) {
                        foreach ($listaPedidos as $objLI) {
                            if ($objLI->getStatus() == 'DESATIVADA') continue;
                            if (!empty($objLI->getCodPagamento())) continue;
                            if ($objLI->getStatus() == 'Pago via acerto') continue;
                    ?>
                        <div class="pagamento-section">
                            <h4>Efetuar Pagamento</h4>
                            <div class="sympla-aviso-pagamento">
                                <strong><?php echo htmlspecialchars($objLI->getDescricao()); ?></strong>
                                <br>Valor: R$ <?php echo formatMoneyView($objLI->getValorTotal()); ?>
                                <?php if (isDateStringValid($objLI->getDatVencimento())) { ?>
                                    <br>Vencimento: <?php echo formatDateView($objLI->getDatVencimento()); ?>
                                <?php } ?>
                            </div>

                            <?php if ($_SERVER['HTTP_HOST'] == 'eventos.test') { ?>
                                <form method="POST" action="http://appboleto.test/api/login/evento" target="_blank">
                            <?php } else { ?>
                                <form method="POST" action="https://pg.opctec.net/api/login/evento" target="_blank">
                            <?php } ?>
                                <div class="pagamento-opcoes">
                                    Selecione uma forma de pagamento:
                                    <?php if ($objEvConf->getGatewayPagamento() != '') { ?>
                                        <?php if (verifItemArray('pix', json_decode($objEvConf->getOpcaoPagamento()))) { ?>
                                            <br><label><input type="radio" name="tipo_pagamento" value="pix" required> PIX</label>
                                        <?php } ?>
                                        <?php if (verifItemArray('boleto', json_decode($objEvConf->getOpcaoPagamento()))) { ?>
                                            <br><label><input type="radio" name="tipo_pagamento" value="Boleto" required> Boleto</label>
                                        <?php } ?>
                                        <?php if (verifItemArray('cartaoCredito', json_decode($objEvConf->getOpcaoPagamento()))) { ?>
                                            <br><label><input type="radio" name="tipo_pagamento" value="cartao" required> Cartão</label>
                                        <?php } ?>
                                    <?php } else { ?>
                                        <br>
                                        <label><input type="radio" name="tipo_pagamento" value="pix" required> PIX</label>
                                        &nbsp;|&nbsp;
                                        <label><input type="radio" name="tipo_pagamento" value="cartao" required> Cartão</label>
                                        &nbsp;|&nbsp;
                                        <label><input type="radio" name="tipo_pagamento" value="Boleto" required> Boleto</label>
                                    <?php } ?>
                                </div>
                                <input type="hidden" name="nome" value="<?php echo $objInscrito->getNomeInscrito(); ?>">
                                <input type="hidden" name="email" value="<?php echo $objInscrito->getEMail(); ?>">
                                <input type="hidden" name="cod_inscrito" value="<?php echo $objInscrito->getCodInscrito(); ?>">
                                <input type="hidden" name="pedido_id" value="<?php echo $objLI->getId(); ?>">
                                <input type="hidden" name="evento_id" value="<?php echo $objInscrito->getCodEvento(); ?>">
                                <input type="hidden" name="rota_retorno" value="hutec">
                                <div style="text-align:center;margin-top:10px;">
                                    <button class="btn btn-lg btn-primary" type="submit" style="background:#2D937E;border-color:#2D937E;">
                                        Efetuar Pagamento
                                    </button>
                                </div>
                            </form>
                        </div>
                    <?php
                        } // end foreach pedidos
                    } elseif (!$isIsento && !$OP_INCRICAO_GRATUITA && $totalIngressos == 0) { ?>
                        <div class="sympla-aviso-pagamento">
                            <strong>Atenção:</strong> Sua inscrição foi registrada. Acesse a área do inscrito para efetuar o pagamento e garantir sua participação.
                        </div>
                    <?php } ?>
                <?php } ?>

                <div style="margin-top:30px;">
                    <a href="<?php echo WB_URL_VIEW; ?>?content=view/inscricao/inscricao_congresso_ok.php" class="btn btn-lg btn-primary" style="background:#2D937E;border-color:#2D937E;">
                        Ir para Área do Inscrito
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($totalIngressos > 0) { ?>
<script>
function abrirFormIdentificar(ingressoId) {
    // Fechar qualquer form aberto
    $('tr[id^="ing-form-row-"]').hide();
    // Abrir o form do ingresso clicado
    $('#ing-form-row-' + ingressoId).show();
    $('#inp-nome-' + ingressoId).focus();
}

function fecharFormIdentificar(ingressoId) {
    $('#ing-form-row-' + ingressoId).hide();
}

function confirmarIdentificacao(ingressoId) {
    var nome     = $.trim($('#inp-nome-' + ingressoId).val());
    var email    = $.trim($('#inp-email-' + ingressoId).val());
    var telefone = $.trim($('#inp-tel-' + ingressoId).val());

    if (!nome) { alert('Informe o nome do portador.'); return; }
    if (!email) { alert('Informe o e-mail do portador.'); return; }
    if (!telefone) { alert('Informe o telefone do portador.'); return; }

    var $btn = $('#btn-confirmar-' + ingressoId);
    $btn.prop('disabled', true).text('Salvando...');

    $.ajax({
        type: 'POST',
        url: '<?php echo WB_URL_COMMAND; ?>',
        data: {
            'c': 'GerenciarIngresso',
            'm': 'identificarPortador',
            'ingresso_id': ingressoId,
            'nome_portador': nome,
            'email_portador': email,
            'telefone_portador': telefone,
            '_submit_check': '1'
        },
        dataType: 'json',
        cache: false,
        success: function(data) {
            if (data.statusMessage == true) {
                // Atualizar a célula do portador
                var html = data.nome;
                html += '<br><span class="portador-info-mini">' + data.email + '</span>';
                html += '<br><span class="portador-info-mini">' + data.telefone + '</span>';
                html += '<br><span class="portador-identificado">&#10003; Identificado</span>';
                $('#ing-portador-' + ingressoId).html(html);

                // Trocar botão de ação pelo cadeado
                $('#ing-row-' + ingressoId + ' td:last').html('<span class="portador-identificado">&#128274;</span>');

                // Fechar o form
                $('#ing-form-row-' + ingressoId).hide();
            } else {
                alert(data.message || 'Erro ao identificar portador.');
                $btn.prop('disabled', false).text('Confirmar');
            }
        },
        error: function() {
            alert('Erro na comunicação com o servidor.');
            $btn.prop('disabled', false).text('Confirmar');
        }
    });
}
</script>
<?php } ?>

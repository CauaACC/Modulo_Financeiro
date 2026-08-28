<?php
require_once(dirname(__FILE__) . "/../../include/config.php");
$debug = false;

require_once 'inscricao_congresso_tit_area_inscrito.php';
?>
<style>
.sympla-progress { display:flex; justify-content:center; margin-bottom:25px; }
.sympla-progress .step { text-align:center; padding:8px 20px; position:relative; flex:1; max-width:200px; }
.sympla-progress .step .num { display:inline-block; width:32px; height:32px; line-height:32px; border-radius:50%; color:#fff; font-weight:bold; margin-bottom:4px; }
.sympla-progress .step.active .num { background:#2D937E; }
.sympla-progress .step.inactive .num { background:#ccc; }
.sympla-progress .step .label-step { display:block; font-size:12px; color:#666; }
.sympla-progress .step.active .label-step { color:#2D937E; font-weight:bold; }

.sympla-card {
    border: 1px solid #e0e0e0;
    border-left: 4px solid #2D937E;
    border-radius: 6px;
    padding: 20px;
    margin-bottom: 15px;
    background: #fff;
    transition: box-shadow 0.2s;
}
.sympla-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
.sympla-card.esgotado {
    border-left-color: #ccc;
    opacity: 0.6;
}
.sympla-card .ticket-titulo { font-size: 18px; font-weight: bold; color: #333; margin-bottom: 5px; }
.sympla-card .ticket-lote { font-size: 13px; color: #888; margin-bottom: 5px; }
.sympla-card .ticket-preco { font-size: 22px; font-weight: bold; color: #2D937E; }
.sympla-card .ticket-preco-gratuito { font-size: 22px; font-weight: bold; color: #2D937E; }
.sympla-card .ticket-vagas { font-size: 12px; color: #e67e22; margin-top: 4px; }
.sympla-card .ticket-desc { font-size: 13px; color: #666; margin-top: 8px; line-height: 1.5; }
.sympla-card .ticket-investimento { font-size: 13px; color: #555; margin-top: 8px; line-height: 1.5; }
.sympla-card .btn-selecionar {
    background: #2D937E;
    color: #fff;
    border: none;
    padding: 10px 24px;
    border-radius: 4px;
    font-size: 15px;
    cursor: pointer;
    transition: background 0.2s;
}
.sympla-card .btn-selecionar:hover { background: #247a68; }
.sympla-card .btn-selecionar:disabled { background: #ccc; cursor: not-allowed; }
.sympla-msg-esgotado { color: #e74c3c; font-weight: bold; font-size: 14px; }

/* Controles de quantidade */
.qty-control { display:inline-flex; align-items:center; gap:8px; }
.qty-btn {
    width:34px; height:34px; border-radius:50%; border:1px solid #2D937E;
    background:#fff; color:#2D937E; font-size:18px; font-weight:bold;
    cursor:pointer; display:inline-flex; align-items:center; justify-content:center;
    transition: background 0.2s, color 0.2s;
    line-height:1; padding:0;
}
.qty-btn:hover { background:#2D937E; color:#fff; }
.qty-btn:disabled { border-color:#ccc; color:#ccc; cursor:not-allowed; background:#fff; }
.qty-display { font-size:18px; font-weight:bold; min-width:30px; text-align:center; color:#333; }

/* Barra de resumo sticky no rodapé */
.sympla-footer-bar {
    position:sticky; bottom:0; background:#fff; border-top:2px solid #2D937E;
    padding:15px 20px; z-index:100; display:flex; align-items:center; justify-content:space-between;
    box-shadow: 0 -2px 8px rgba(0,0,0,0.1);
}
.sympla-footer-bar .footer-resumo { font-size:16px; color:#333; }
.sympla-footer-bar .footer-resumo .footer-total { font-weight:bold; color:#2D937E; font-size:18px; }
.sympla-footer-bar .btn-continuar {
    background:#2D937E; color:#fff; border:none; padding:12px 30px;
    border-radius:4px; font-size:16px; cursor:pointer; transition:background 0.2s;
}
.sympla-footer-bar .btn-continuar:hover { background:#247a68; }
.sympla-footer-bar .btn-continuar:disabled { background:#ccc; cursor:not-allowed; }
</style>

<div class="panel-group">
    <div class="panel panel-default">
        <div class="panel-body">
            <!-- Barra de progresso -->
            <div class="sympla-progress">
                <div class="step active">
                    <span class="num">1</span>
                    <span class="label-step">Selecionar Ingresso</span>
                </div>
                <div class="step inactive">
                    <span class="num">2</span>
                    <span class="label-step">Dados Pessoais</span>
                </div>
                <div class="step inactive">
                    <span class="num">3</span>
                    <span class="label-step">Confirmação</span>
                </div>
            </div>

            <?php
            if ($fws_['msg']) {
                FWA_Mostrar_Mensagem($fws_['msg']);
            }

            // Mensagem customizada do evento
            $msg_tela_durante = $objEvConf->getMsgTelaDurante();
            if ($msg_tela_durante) {
                echo "<div style='text-align:center;margin-bottom:15px;'><h4 style='margin-bottom:0px;'>" . $msg_tela_durante . "</h4></div>";
            }

            // Mensagem de perfil (se ativada)
            if ($objEvConf->getMsgPerfilAtivar() == 1) {
                $msgPerfil = $objEvConf->getMsgPerfilTexto();
                if ($msgPerfil) {
                    echo "<div style='text-align:center;margin-bottom:15px;'>" . $msgPerfil . "</div>";
                }
            }
            ?>

            <h3 style="text-align:center;margin-bottom:20px;color:#333;">Escolha seus ingressos</h3>

            <?php
            try {
                $obj = new Perfil();
                $obj->setCodEvento(COD_EVENTO);
                try {
                    $obj->carregarListaPerfilInscricao($debug);
                } catch (Exception $exc) {
                    throw new Exception("Nenhuma categoria de inscrição disponível no momento.");
                }

                // Array para dados JS dos perfis
                $perfisJS = [];

                foreach ($obj->getListaPerfil() as $objL) {
                    if ($objL->getReceberInscricao() != 1) continue;

                    // Verificar vagas limitadas
                    $vagas_disponiveis = true;
                    $vagas_restantes = null;
                    $max_qty = 10; // default
                    if ($objL->getVerificarVagasLimitadas() == 1) {
                        $vagasOcupadas = $objL->getTotalInscritosPerfil1() + $objL->getTotalInscritosPerfil2();
                        $vagas_restantes = $objL->getVagas() - $vagasOcupadas;
                        if ($vagas_restantes <= 0) {
                            $vagas_disponiveis = false;
                            $vagas_restantes = 0;
                        } else {
                            $max_qty = min($vagas_restantes, 10);
                        }
                    }

                    // Carregar taxa/preço ativo
                    $taxaAtiva = null;
                    $valorExibir = '';
                    $loteExibir = '';
                    try {
                        $objTaxa = new Taxa();
                        $objTaxa->setCodPerfil($objL->getCodPerfil());
                        $objTaxa->setCodEvento(COD_EVENTO);
                        $cond = $objL->getValoresPorPeriodo() ? ['data_hoje' => date('Y-m-d H:i:s')] : false;
                        $objTaxa->carregarTaxaAtiva($cond);
                        $listaTaxas = $objTaxa->getListaTaxas();
                        if (is_array($listaTaxas) && count($listaTaxas) > 0) {
                            $taxaAtiva = $listaTaxas[0];
                            $valorExibir = $taxaAtiva->getTxUnicaAVistaValor();
                            $loteExibir = $taxaAtiva->getTitulo();
                        }
                    } catch (Exception $e) {
                        // Sem taxa ativa - pode ser gratuito
                    }

                    // Verificar se é gratuito
                    $isGratuito = ($OP_INCRICAO_GRATUITA == 1) || (empty($valorExibir) || $valorExibir == 0 || $valorExibir == '0.00' || $valorExibir == '0,00');

                    $cardClass = $vagas_disponiveis ? 'sympla-card' : 'sympla-card esgotado';
                    $codPerfil = $objL->getCodPerfil();
                    $valorFloat = floatval($valorExibir);

                    // Guardar dados para JS
                    if ($vagas_disponiveis) {
                        $perfisJS[] = [
                            'cod_perfil' => $codPerfil,
                            'titulo' => $objL->getTitulo(),
                            'valor_unitario' => $valorFloat,
                            'max_qty' => $max_qty,
                            'gratuito' => $isGratuito
                        ];
                    }
                    ?>
                    <div class="<?php echo $cardClass; ?>" data-cod-perfil="<?php echo $codPerfil; ?>">
                        <div class="row">
                            <div class="col-sm-5">
                                <div class="ticket-titulo"><?php echo $objL->getTitulo(); ?></div>
                                <?php if ($loteExibir) { ?>
                                    <div class="ticket-lote"><?php echo $loteExibir; ?></div>
                                <?php } ?>
                                <?php if ($objL->getDescricao()) { ?>
                                    <div class="ticket-desc"><?php echo $objL->getDescricao(); ?></div>
                                <?php } ?>
                                <?php if ($objL->getInvestimento()) { ?>
                                    <div class="ticket-investimento"><?php echo $objL->getInvestimento(); ?></div>
                                <?php } ?>
                            </div>
                            <div class="col-sm-3 text-center" style="display:flex;align-items:center;justify-content:center;">
                                <?php if (!$vagas_disponiveis) { ?>
                                    <div>
                                        <span class="sympla-msg-esgotado">Esgotado</span>
                                    </div>
                                <?php } elseif ($isGratuito) { ?>
                                    <div>
                                        <div class="ticket-preco-gratuito">Gratuito</div>
                                        <?php if ($vagas_restantes !== null) { ?>
                                            <div class="ticket-vagas"><?php echo $vagas_restantes; ?> vaga<?php echo $vagas_restantes != 1 ? 's' : ''; ?> restante<?php echo $vagas_restantes != 1 ? 's' : ''; ?></div>
                                        <?php } ?>
                                    </div>
                                <?php } else { ?>
                                    <div>
                                        <div class="ticket-preco">R$ <?php echo number_format($valorFloat, 2, ',', '.'); ?></div>
                                        <?php if ($vagas_restantes !== null) { ?>
                                            <div class="ticket-vagas"><?php echo $vagas_restantes; ?> vaga<?php echo $vagas_restantes != 1 ? 's' : ''; ?> restante<?php echo $vagas_restantes != 1 ? 's' : ''; ?></div>
                                        <?php } ?>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="col-sm-4 text-center" style="display:flex;align-items:center;justify-content:center;">
                                <?php if ($vagas_disponiveis) { ?>
                                    <div class="qty-control" data-cod-perfil="<?php echo $codPerfil; ?>">
                                        <button type="button" class="qty-btn qty-minus" data-perfil="<?php echo $codPerfil; ?>" disabled>&minus;</button>
                                        <span class="qty-display" id="qty-<?php echo $codPerfil; ?>">0</span>
                                        <button type="button" class="qty-btn qty-plus" data-perfil="<?php echo $codPerfil; ?>">&plus;</button>
                                    </div>
                                <?php } else { ?>
                                    <button class="btn-selecionar" disabled>Esgotado</button>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                    <?php
                }

            } catch (Exception $exc) {
                echo "<div style='text-align:center;padding:20px;'>";
                FWA_Mostrar_Mensagem($exc->getMessage());
                echo "</div>";
            }
            ?>

            <div style="text-align:center;margin-top:20px;margin-bottom:80px;">
                <a href="<?php echo WB_URL_VIEW; ?>?content=view/inscricao/inscricao_01.php" class="btn btn-default">Voltar</a>
            </div>
        </div>
    </div>
</div>

<!-- Barra de resumo sticky -->
<div class="sympla-footer-bar" id="footerBar" style="display:none;">
    <div class="footer-resumo">
        <span id="footerQtd">0</span> ingresso(s)
        <span style="margin:0 10px;">—</span>
        Total: <span class="footer-total" id="footerTotal">R$ 0,00</span>
    </div>
    <form method="POST" action="<?php echo WB_URL_VIEW; ?>?content=view/inscricao/ticket_dados.php" id="formTickets">
        <input type="hidden" name="tickets_json" id="tickets_json" value="">
        <button type="submit" class="btn-continuar" id="btnContinuar" disabled>Continuar</button>
    </form>
</div>

<script type="text/javascript">
$(document).ready(function() {
    // Dados dos perfis disponíveis
    var perfis = <?php echo json_encode($perfisJS ?? []); ?>;
    // Estado: quantidade por cod_perfil
    var quantidades = {};
    var perfilMap = {};
    $.each(perfis, function(i, p) {
        quantidades[p.cod_perfil] = 0;
        perfilMap[p.cod_perfil] = p;
    });

    function atualizarResumo() {
        var totalQtd = 0;
        var totalValor = 0;
        $.each(quantidades, function(cod, qty) {
            if (qty > 0 && perfilMap[cod]) {
                totalQtd += qty;
                totalValor += qty * perfilMap[cod].valor_unitario;
            }
        });
        $('#footerQtd').text(totalQtd);
        $('#footerTotal').text('R$ ' + totalValor.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.'));

        if (totalQtd > 0) {
            $('#footerBar').show();
            $('#btnContinuar').prop('disabled', false);
        } else {
            $('#footerBar').hide();
            $('#btnContinuar').prop('disabled', true);
        }

        // Montar JSON dos tickets
        var tickets = [];
        $.each(quantidades, function(cod, qty) {
            if (qty > 0 && perfilMap[cod]) {
                tickets.push({
                    cod_perfil: cod,
                    quantidade: qty,
                    titulo: perfilMap[cod].titulo,
                    valor_unitario: perfilMap[cod].valor_unitario
                });
            }
        });
        $('#tickets_json').val(JSON.stringify(tickets));
    }

    // Botão +
    $(document).on('click', '.qty-plus', function() {
        var cod = $(this).data('perfil');
        if (!perfilMap[cod]) return;
        var max = perfilMap[cod].max_qty;
        if (quantidades[cod] < max) {
            quantidades[cod]++;
            $('#qty-' + cod).text(quantidades[cod]);
            // Atualizar estado dos botões
            $(this).closest('.qty-control').find('.qty-minus').prop('disabled', false);
            if (quantidades[cod] >= max) {
                $(this).prop('disabled', true);
            }
            atualizarResumo();
        }
    });

    // Botão -
    $(document).on('click', '.qty-minus', function() {
        var cod = $(this).data('perfil');
        if (quantidades[cod] > 0) {
            quantidades[cod]--;
            $('#qty-' + cod).text(quantidades[cod]);
            // Atualizar estado dos botões
            $(this).closest('.qty-control').find('.qty-plus').prop('disabled', false);
            if (quantidades[cod] <= 0) {
                $(this).prop('disabled', true);
            }
            atualizarResumo();
        }
    });
});
</script>

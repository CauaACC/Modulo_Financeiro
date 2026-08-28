<?php
require_once(dirname(__FILE__)."/../../view/admin/consulta-dados-congresso-seguranca.php");
$objSRV = new GerenciarInscritoStatusAC();
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
.hist-card {
    background: #fff; border: 1px solid #e0e0e0; border-radius: 8px;
    margin-bottom: 12px; overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08); transition: box-shadow .2s;
}
.hist-card:hover { box-shadow: 0 2px 8px rgba(0,0,0,0.13); }
.hist-card-header {
    display: flex; align-items: center; gap: 10px;
    padding: 10px 16px; background: #f8f9fa; border-bottom: 1px solid #e9ecef;
}
.hist-card-cod { font-weight: 700; color: #888; }
.hist-card-info { flex: 1; display: flex; flex-direction: column; }
.hist-card-nome { font-weight: 600; color: #333; }
.hist-card-perfil { font-size: 12px; color: #777; }
.hist-timeline { padding: 12px 16px 8px; position: relative; }
.hist-timeline-item {
    display: flex; align-items: center; gap: 8px;
    padding: 6px 0; position: relative; padding-left: 20px;
    border-left: 2px solid #2D937E; margin-left: 6px;
}
.hist-timeline-item:last-child { border-left-color: transparent; }
.hist-timeline-item::before {
    content: ''; position: absolute; left: -5px; top: 50%; transform: translateY(-50%);
    width: 10px; height: 10px; border-radius: 50%;
    background: #2D937E; border: 2px solid #fff; box-shadow: 0 0 0 1px #2D937E;
}
.hist-data { color: #999; font-size: 13px; white-space: nowrap; min-width: 120px; }
.hist-status-de { color: #888; min-width: 120px; }
.hist-arrow { color: #2D937E; font-weight: bold; }
.hist-status-para { font-weight: 600; color: #333; min-width: 120px; }
.hist-timeline-ultimo { background: #f0faf7; border-radius: 4px; padding-top: 8px; padding-bottom: 8px; margin-right: -8px; padding-right: 8px; }
.hist-timeline-ultimo::before { background: #1a7a64; width: 12px; height: 12px; left: -6px; box-shadow: 0 0 0 2px #1a7a64; }
.hist-timeline-ultimo .hist-status-para { color: #1a7a64; }
.hist-filter-label { font-weight: 600; color: #555; font-size: 12px; margin-bottom: 4px; display: block; }
.hist-filter-box {
    padding: 6px 10px; background: #f8f9fa; border: 1px solid #e0e0e0;
    border-radius: 6px; min-height: 32px; display: flex; align-items: center;
}
.hist-filter-box input[type="text"] {
    border: none; background: transparent; outline: none;
    width: 100%; font-size: 13px; color: #333;
}
.hist-filter-box input[type="text"]::placeholder { color: #aaa; }
.hist-checkbox-group {
    display: flex; flex-wrap: wrap; gap: 6px 12px;
    padding: 6px 10px; background: #f8f9fa; border: 1px solid #e0e0e0;
    border-radius: 6px; min-height: 32px; align-items: center;
}
.hist-checkbox-group label {
    display: inline-flex; align-items: center; gap: 4px;
    font-weight: 400; font-size: 13px; color: #444;
    cursor: pointer; margin: 0; white-space: nowrap;
}
.hist-checkbox-group input[type="checkbox"] { accent-color: #2D937E; cursor: pointer; }
.hist-btn-limpar {
    display: inline-flex; align-items: center; gap: 4px;
    background: #2D937E; color: #fff; border: none; border-radius: 4px;
    padding: 5px 12px; font-size: 12px; cursor: pointer; transition: background .2s;
}
.hist-btn-limpar:hover { background: #247a68; text-decoration: none; color: #fff; }
.hist-resultados-bar {
    display: flex; align-items: center; justify-content: space-between;
    padding: 8px 0; margin-top: 4px; margin-bottom: 10px;
    border-top: 1px solid #e0e0e0; border-bottom: 1px solid #e0e0e0;
}
.hist-resultados-bar .hist-contador {
    font-size: 13px; color: #666;
}
.hist-resultados-bar .hist-contador strong { color: #2D937E; }
</style>

<script type="text/javascript">
    $(document).ready(function() {
        montarCheckboxPerfil();
        montarCheckboxSituacao();
        atualizarZebra();

        $('#codInscrito, #nomeInscrito').on('input', filtrarTabela);
        $('#checkboxPerfil, #checkboxSituacao').on('change', 'input', filtrarTabela);
        $('#formInscritoStatus').on('submit', function(e) { e.preventDefault(); });
        $('#btnLimpar').click(function() { limparCampos(); filtrarTabela(); });
    });

    function montarCheckboxPerfil() {
        var $container = $('#checkboxPerfil');
        $('#codPerfil_source option').each(function() {
            var val = $.trim($(this).text());
            if (!val) return;
            $container.append(
                '<label><input type="checkbox" name="filtro_perfil" value="' + val.toLowerCase() + '"> ' + val + '</label>'
            );
        });
    }

    function montarCheckboxSituacao() {
        var situacoes = {};
        $('#itens .hist-card').each(function() {
            var status = $(this).data('status-atual');
            if (status) situacoes[status] = true;
        });
        var $container = $('#checkboxSituacao');
        var keys = Object.keys(situacoes).sort();
        for (var i = 0; i < keys.length; i++) {
            var label = keys[i].charAt(0).toUpperCase() + keys[i].slice(1);
            $container.append(
                '<label><input type="checkbox" name="filtro_situacao" value="' + keys[i] + '"> ' + label + '</label>'
            );
        }
    }

    function filtrarTabela() {
        var codInscrito  = $.trim($('#codInscrito').val()).toLowerCase();
        var nomeInscrito = $.trim($('#nomeInscrito').val()).toLowerCase();
        var perfisSel = [];
        $('input[name="filtro_perfil"]:checked').each(function() {
            perfisSel.push($(this).val());
        });
        var situacoesSel = [];
        $('input[name="filtro_situacao"]:checked').each(function() {
            situacoesSel.push($(this).val());
        });

        $('#itens .hist-card').each(function() {
            var $card = $(this);
            var show = true;

            if (codInscrito && $card.data('cod').toString().indexOf(codInscrito) === -1) show = false;
            if (nomeInscrito && $card.data('nome').indexOf(nomeInscrito) === -1) show = false;
            if (perfisSel.length > 0) {
                var perfCard = $card.data('perfil');
                var match = false;
                for (var p = 0; p < perfisSel.length; p++) {
                    if (perfCard.indexOf(perfisSel[p]) !== -1) { match = true; break; }
                }
                if (!match) show = false;
            }
            if (situacoesSel.length > 0) {
                var statusAtual = ($card.data('status-atual') || '').toString().toLowerCase();
                var matchSit = false;
                for (var s = 0; s < situacoesSel.length; s++) {
                    if (statusAtual === situacoesSel[s]) { matchSit = true; break; }
                }
                if (!matchSit) show = false;
            }
            $card.toggle(show);
        });
        atualizarZebra();
    }

    function atualizarZebra() {
        var total = $('#itens .hist-card').length;
        var visiveis = 0;
        $('#itens .hist-card:visible').each(function(i) {
            $(this).css('background', i % 2 === 0 ? '#fff' : '#f9fafb');
            visiveis++;
        });
        $('#contadorTotal').text(total);
        $('#contadorVisiveis').text(visiveis);
    }

    function limparCampos() {
        $('#codInscrito').val('');
        $('#nomeInscrito').val('');
        $('input[name="filtro_perfil"]').prop('checked', false);
        $('input[name="filtro_situacao"]').prop('checked', false);
    }
</script>
<?php
require_once(dirname(__FILE__)."/../../view/admin/titulo_area_admin.php");
require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_administracao.php");
require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_inscricoes.php");
?>
<div class="panel-group">
<div class="panel panel-default">
    <div class="panel-heading"><h4>Hist&oacute;rico de Processo dos Inscritos:</h4></div>
    <div class="panel-body">
        <ul class="nav nav-tabs" role="tablist" style="margin-bottom:12px;">
            <li class="active"><a href="#tab-historico" data-toggle="tab" role="tab"><i class="fa fa-list"></i> Hist&oacute;rico de Status</a></li>
            <li><a href="#tab-estatisticas" data-toggle="tab" role="tab"><i class="fa fa-bar-chart"></i> Estat&iacute;sticas de Registro de Atividade</a></li>
        </ul>
        <div class="tab-content">
            <div id="tab-historico" class="tab-pane fade in active" role="tabpanel">
                <?php
                    try {
                        ?>
                        <form id="formInscritoStatus" name="formInscritoStatus">
                            <div class="row" style="margin-bottom: 8px;">
                                <div class="col-sm-2">
                                    <label class="hist-filter-label">Cód. Inscrito:</label>
                                    <div class="hist-filter-box">
                                        <input type="text" id="codInscrito" name="codInscrito" placeholder="código">
                                    </div>
                                </div>
                                <div class="col-sm-5">
                                    <label class="hist-filter-label">Nome Inscrito:</label>
                                    <div class="hist-filter-box">
                                        <input type="text" id="nomeInscrito" name="nomeInscrito" placeholder="nome do inscrito">
                                    </div>
                                </div>
                                <div class="col-sm-5" style="display:flex; align-items:flex-end; justify-content:flex-end;">
                                    <button type="button" class="hist-btn-limpar" id="btnLimpar"><i class="fa fa-times"></i> Limpar filtros</button>
                                </div>
                            </div>
                            <div class="row" style="margin-bottom: 0;">
                                <div class="col-sm-6">
                                    <label class="hist-filter-label">Perfil:</label>
                                    <div class="hist-checkbox-group" id="checkboxPerfil">
                                        <?php
                                        $fws_['codEvento'] = $objEv->getCodEvento();
                                        $fws_['semRestricao']=true;
                                        ?>
                                        <select id="codPerfil_source" style="display:none" multiple>
                                            <?php CarregarOptionsAC::perfis($fws_, $debug); ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <label class="hist-filter-label">Situação Atual:</label>
                                    <div class="hist-checkbox-group" id="checkboxSituacao">
                                    </div>
                                </div>
                            </div>
                        </form>
                    <?php } catch (Exception $exc) { ?>
                        <div class="col-sm-12"> <?php echo $exc->getMessage(); ?></div>
                    <?php } ?>
                    <div class="col-sm-12" style="padding-left: 0px;">
                        <div class="hist-resultados-bar">
                            <span class="hist-contador">Exibindo <strong id="contadorVisiveis">0</strong> de <strong id="contadorTotal">0</strong> inscritos</span>
                        </div>
                        <div id="itens">
                            <?php
                            $objSRV->carregarTabelaInscritosStatus($fws_, $debug);
                            ?>
                        </div>
                    </div>
            </div>
            <div id="tab-estatisticas" class="tab-pane fade" role="tabpanel">
                <?php
                $fws_['codEvento'] = $objEv->getCodEvento();
                $objSRV->carregarPainelRegistroAtividadeValidado($fws_, $debug);
                ?>
            </div>
        </div>
    </div>
</div>
</div>

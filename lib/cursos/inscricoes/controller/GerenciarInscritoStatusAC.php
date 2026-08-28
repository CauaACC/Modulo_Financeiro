<?php

class GerenciarInscritoStatusAC {

    function __construct() {
        /* Exigia SO o 999, que nao e superset: admin do evento com direito de
           inscrito levava Fatal error numa tela que o menu mostra pra ele.
           Gate no construtor vale para todos os metodos da classe. */
        FWA_exigirPermissao(array(
            enumSistema::SIS_GERENCIAR_INSCRITO,
            enumSistema::SIS_GERENCIAR_ADM_GERAL,
        ));
    }

    public function carregarTabelaInscritosStatus($fws_, $debug = false) {
        $obj = new Inscrito();
        try {
            $condicoes['codInscrito']       = $fws_['codInscrito'] ?? '';
            $condicoes['nomeInscrito']      = $fws_['nomeInscrito'] ?? '';
            $condicoes['codPerfil']         = $fws_['codPerfil'] ?? '';
            $lista = $obj->getListaInscritosStatusByIdEvento($condicoes, $debug);

            // Agrupar por cod_inscrito
            $agrupado = [];
            foreach ($lista as $objL) {
                $cod = $objL->getCodInscrito();
                if (!isset($agrupado[$cod])) {
                    $agrupado[$cod] = [
                        'cod'    => $cod,
                        'nome'   => textoMaiusculoMinusculo($objL->getNomeInscrito()),
                        'perfil' => $objL->getNomePerfil(),
                        'transicoes' => []
                    ];
                }
                $agrupado[$cod]['transicoes'][] = [
                    'de'       => $objL->getStatusAntiga(),
                    'para'     => $objL->getStatusNova(),
                    'dataView' => formatDateView($objL->getDataAlteracao())
                ];
            }

            // Gerar cards
            foreach ($agrupado as $insc) {
                $codEsc   = htmlspecialchars($insc['cod']);
                $nomeEsc  = htmlspecialchars($insc['nome']);
                $perfilEsc = htmlspecialchars($insc['perfil']);
                $nomeLower  = htmlspecialchars(mb_strtolower($insc['nome'], 'UTF-8'));
                $perfilLower = htmlspecialchars(mb_strtolower($insc['perfil'], 'UTF-8'));
                // Situação atual = status_nova da última transição
                $ultimaTransicao = end($insc['transicoes']);
                $statusAtual = htmlspecialchars($ultimaTransicao['para']);
                $statusAtualLower = htmlspecialchars(mb_strtolower($ultimaTransicao['para'], 'UTF-8'));
                ?>
                <div class="hist-card" data-cod="<?php echo $codEsc ?>" data-nome="<?php echo $nomeLower ?>" data-perfil="<?php echo $perfilLower ?>" data-status-atual="<?php echo $statusAtualLower ?>">
                    <div class="hist-card-header">
                        <div class="hist-card-info">
                            <span class="hist-card-nome"><span class="hist-card-cod">#<?php echo $codEsc ?></span> <?php echo $nomeEsc ?></span>
                            <span class="hist-card-perfil"><?php echo $perfilEsc ?></span>
                        </div>
                    </div>
                    <div class="hist-timeline">
                        <?php
                        $totalTransicoes = count($insc['transicoes']);
                        foreach ($insc['transicoes'] as $idx => $t) {
                            $isUltimo = ($idx === $totalTransicoes - 1);
                        ?>
                        <div class="hist-timeline-item<?php echo $isUltimo ? ' hist-timeline-ultimo' : '' ?>">
                            <span class="hist-data"><?php echo htmlspecialchars($t['dataView']) ?></span>
                            <span class="hist-status-de"><?php echo htmlspecialchars($t['de']) ?></span>
                            <span class="hist-arrow"><i class="fa fa-long-arrow-right"></i></span>
                            <span class="hist-status-para"><?php echo htmlspecialchars($t['para']) ?></span>

                        </div>
                        <?php } ?>
                    </div>
                </div>
                <?php
            }
        } catch (Exception $e) {
            echo '<div class="alert alert-warning" style="margin:10px 0">';
            if ($e->getMessage()) {
                echo htmlspecialchars($e->getMessage());
            } else {
                echo "Lista vazia.";
            }
            echo '</div>';
        }
    }

    public function carregarPainelRegistroAtividadeValidado($fws_, $debug = false) {
        try {
            $codEvento = isset($fws_['codEvento']) ? intval($fws_['codEvento']) : COD_EVENTO;
            $objRA = new RegistroAtividade();
            $objRA->setCodEvento($codEvento);
            $linhas = $objRA->getPessoasValidadasPorMesPerfil($debug);
            $linhasParticipando = $objRA->getParticipandoPorPerfil($debug);

            $objIns = new Inscrito();
            $objIns->setCodEvento($codEvento);
            $linhasCadastrados = $objIns->getCadastradosPorMesPerfil($debug);

            if (empty($linhas)) {
                echo '<div class="panel panel-default" style="margin-top:8px"><div class="panel-body"><em>Nenhum registro de atividade validado encontrado para este evento.</em></div></div>';
                return;
            }

            $perfisMap = [];
            $perfisCadastradosMap = [];
            $anosMap = [];
            $matrizPessoas = [];
            $matrizMinutos = [];
            $matrizCadastrados = [];
            foreach ($linhas as $r) {
                $perfil = $r['perfil'];
                $am     = $r['ano_mes'];
                $ano    = substr($am, 0, 4);
                $perfisMap[$perfil] = true;
                $anosMap[$ano] = true;
                $matrizPessoas[$perfil][$am] = intval($r['pessoas']);
                $matrizMinutos[$perfil][$am] = intval($r['minutos']);
            }
            foreach ($linhasCadastrados as $r) {
                $perfil = $r['perfil'];
                $am     = $r['ano_mes'];
                $perfisCadastradosMap[$perfil] = true;
                if (!empty($am)) {
                    $anosMap[substr($am, 0, 4)] = true;
                    $matrizCadastrados[$perfil][$am] = intval($r['pessoas']);
                }
            }
            ksort($perfisCadastradosMap);
            $anosMap[date('Y')] = true; // garante o ano atual como opcao mesmo sem dados
            ksort($anosMap);
            ksort($perfisMap);
            $listaAnos = array_keys($anosMap);
            $anoDefault = in_array(date('Y'), $listaAnos) ? date('Y') : end($listaAnos);
            ?>
            <div class="panel panel-default" style="margin-top:8px">
                <div class="panel-heading">
                    <h4>Estat&iacute;sticas de Atividade Validada</h4>
                    <small class="text-muted">M&eacute;tricas calculadas sobre registros com <strong>avalia&ccedil;&atilde;o = Validado</strong> no evento atual.</small>
                </div>
                <div class="panel-body">
                    <div style="margin-bottom:16px;display:flex;align-items:center;gap:10px">
                        <label for="selectAnoEstat" style="margin:0;font-weight:600">Ano:</label>
                        <select id="selectAnoEstat" class="form-control" style="width:auto;display:inline-block">
                            <?php foreach ($listaAnos as $a): ?>
                                <option value="<?php echo $a; ?>" <?php echo ($a == $anoDefault ? 'selected' : ''); ?>><?php echo $a; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <h4 style="margin-top:0">Pessoas que registraram atividade validada no m&ecirc;s</h4>
                    <small class="text-muted">Cada c&eacute;lula = pessoas distintas com pelo menos 1 registro validado naquele cargo/m&ecirc;s. <em>Sem total horizontal porque a mesma pessoa apareceria contada em meses diferentes.</em></small>
                    <div style="margin:12px 0">
                        <canvas id="chartRegAtivPessoas" style="max-height:340px"></canvas>
                    </div>
                    <div style="overflow-x:auto;margin-bottom:8px">
                        <table id="tabRegAtivPessoas" class="table table-bordered table-condensed" style="font-size:13px;margin-bottom:0"></table>
                    </div>

                    <?php
                    $totalParticipando = 0;
                    $mapParticipando = [];
                    foreach ($linhasParticipando as $rp) {
                        $mapParticipando[$rp['perfil']] = intval($rp['pessoas']);
                        $totalParticipando += intval($rp['pessoas']);
                    }
                    ?>
                    <div style="overflow-x:auto;margin-bottom:24px">
                        <table class="table table-bordered table-condensed" style="font-size:13px;margin-bottom:0;max-width:520px">
                            <thead>
                                <tr style="background:#f8f9fa">
                                    <th colspan="2">
                                        <i class="fa fa-user-check"></i> Atualmente PARTICIPANDO
                                        <small class="text-muted" style="font-weight:400">(snapshot de hoje, <?php echo date('d/m/Y'); ?> &mdash; status_processo = PARTICIPANDO)</small>
                                    </th>
                                </tr>
                                <tr style="background:#f8f9fa">
                                    <th>Perfil</th>
                                    <th style="text-align:right">Pessoas</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($mapParticipando)): ?>
                                    <tr><td colspan="2" class="text-muted"><em>Nenhum inscrito com status_processo = PARTICIPANDO no momento.</em></td></tr>
                                <?php else: ?>
                                    <?php foreach ($mapParticipando as $perfilNome => $qtd): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($perfilNome, ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td style="text-align:right"><?php echo $qtd; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <tr style="background:#f0faf7;font-weight:700">
                                        <td>Total Geral</td>
                                        <td style="text-align:right"><?php echo $totalParticipando; ?></td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <h4>Minutos de atividade validada no m&ecirc;s</h4>
                    <small class="text-muted">Soma da carga hor&aacute;ria registrada (em minutos) com avalia&ccedil;&atilde;o validada.</small>
                    <div style="margin:12px 0">
                        <canvas id="chartRegAtivMinutos" style="max-height:340px"></canvas>
                    </div>
                    <div style="overflow-x:auto">
                        <table id="tabRegAtivMinutos" class="table table-bordered table-condensed" style="font-size:13px;margin-bottom:0"></table>
                    </div>

                    <h4 style="margin-top:24px">Inscritos cadastrados no m&ecirc;s</h4>
                    <small class="text-muted">Cada c&eacute;lula = inscritos cujo <strong>dat_incl</strong> (data de cadastro em <code>tab_inscritos</code>) cai naquele m&ecirc;s/ano, independente do status.</small>
                    <div style="margin:12px 0">
                        <canvas id="chartRegAtivCadastrados" style="max-height:340px"></canvas>
                    </div>
                    <div style="overflow-x:auto">
                        <table id="tabRegAtivCadastrados" class="table table-bordered table-condensed" style="font-size:13px;margin-bottom:0"></table>
                    </div>
                </div>
            </div>
            <script>
            (function(){
                const matrizPessoas = <?php echo json_encode($matrizPessoas, JSON_UNESCAPED_UNICODE); ?>;
                const matrizMinutos = <?php echo json_encode($matrizMinutos, JSON_UNESCAPED_UNICODE); ?>;
                const matrizCadastrados = <?php echo json_encode($matrizCadastrados, JSON_UNESCAPED_UNICODE); ?>;
                const perfis = <?php echo json_encode(array_keys($perfisMap), JSON_UNESCAPED_UNICODE); ?>;
                const perfisCadastrados = <?php echo json_encode(array_keys($perfisCadastradosMap), JSON_UNESCAPED_UNICODE); ?>;
                const mesesAbrev = ['jan','fev','mar','abr','mai','jun','jul','ago','set','out','nov','dez'];
                let chartPessoas = null;
                let chartMinutos = null;
                let chartCadastrados = null;
                let inicializado = false;

                function gerarMesesAno(ano) {
                    const meses = [];
                    for (let i = 1; i <= 12; i++) {
                        const mm = String(i).padStart(2,'0');
                        meses.push({ am: ano + '-' + mm, label: mesesAbrev[i-1] + '/' + ano });
                    }
                    return meses;
                }

                // Tabela de pessoas: SEM coluna "Total" (mesma pessoa apareceria em varios meses).
                // Mantem apenas a linha "Total Geral (mes)" porque cada pessoa tem 1 perfil.
                function renderTabelaPessoas(ano) {
                    const meses = gerarMesesAno(ano);
                    const tab = document.getElementById('tabRegAtivPessoas');
                    let html = '<thead><tr style="background:#f8f9fa"><th>Perfil</th>';
                    meses.forEach(m => { html += "<th style='text-align:right'>" + m.label + "</th>"; });
                    html += "</tr></thead><tbody>";
                    let totMes = meses.map(() => 0);
                    perfis.forEach(p => {
                        html += '<tr><td>' + p + '</td>';
                        meses.forEach((m, idx) => {
                            const v = (matrizPessoas[p] && matrizPessoas[p][m.am]) ? matrizPessoas[p][m.am] : 0;
                            totMes[idx] += v;
                            html += "<td style='text-align:right'>" + (v || '-') + "</td>";
                        });
                        html += "</tr>";
                    });
                    html += '<tr style="background:#f0faf7;font-weight:700"><td>Total Geral (m&ecirc;s)</td>';
                    totMes.forEach(v => { html += "<td style='text-align:right'>" + (v || '-') + "</td>"; });
                    html += '</tr></tbody>';
                    tab.innerHTML = html;
                }

                // Tabela de cadastrados: cada inscrito tem 1 mes de cadastro -> COM coluna "Total ano" e linha "Total Geral (mes)"
                function renderTabelaCadastrados(ano) {
                    const meses = gerarMesesAno(ano);
                    const tab = document.getElementById('tabRegAtivCadastrados');
                    let html = '<thead><tr style="background:#f8f9fa"><th>Perfil</th>';
                    meses.forEach(m => { html += "<th style='text-align:right'>" + m.label + "</th>"; });
                    html += "</tr></thead><tbody>";
                    perfisCadastrados.forEach(p => {
                        html += '<tr><td>' + p + '</td>';
                        meses.forEach((m) => {
                            const v = (matrizCadastrados[p] && matrizCadastrados[p][m.am]) ? matrizCadastrados[p][m.am] : 0;
                            html += "<td style='text-align:right'>" + (v || '-') + "</td>";
                        });
                        html += "</tr>";
                    });
                    html += '</tbody>';
                    tab.innerHTML = html;
                }

                // Tabela de minutos: COM total horizontal (somar minutos do mesmo perfil em meses diferentes faz sentido)
                // E total vertical (somar minutos de perfis diferentes no mesmo mes faz sentido).
                function renderTabelaMinutos(ano) {
                    const meses = gerarMesesAno(ano);
                    const tab = document.getElementById('tabRegAtivMinutos');
                    let html = '<thead><tr style="background:#f8f9fa"><th>Perfil</th>';
                    meses.forEach(m => { html += "<th style='text-align:right'>" + m.label + "</th>"; });
                    html += "<th style='text-align:right'>Total ano</th></tr></thead><tbody>";
                    let totMes = meses.map(() => 0);
                    let totalGeral = 0;
                    perfis.forEach(p => {
                        let totalPerfil = 0;
                        html += '<tr><td>' + p + '</td>';
                        meses.forEach((m, idx) => {
                            const v = (matrizMinutos[p] && matrizMinutos[p][m.am]) ? matrizMinutos[p][m.am] : 0;
                            totalPerfil += v;
                            totMes[idx] += v;
                            html += "<td style='text-align:right'>" + (v ? v.toLocaleString('pt-BR') : '-') + "</td>";
                        });
                        html += "<td style='text-align:right;font-weight:600'>" + (totalPerfil ? totalPerfil.toLocaleString('pt-BR') : '-') + "</td></tr>";
                        totalGeral += totalPerfil;
                    });
                    html += '<tr style="background:#f0faf7;font-weight:700"><td>Total Geral (m&ecirc;s)</td>';
                    totMes.forEach(v => { html += "<td style='text-align:right'>" + (v ? v.toLocaleString('pt-BR') : '-') + "</td>"; });
                    html += "<td style='text-align:right'>" + (totalGeral ? totalGeral.toLocaleString('pt-BR') : '-') + "</td>";
                    html += '</tr></tbody>';
                    tab.innerHTML = html;
                }

                function gerarDatasets(matriz, meses) {
                    return perfis.map(p => ({
                        label: p,
                        data: meses.map(m => (matriz[p] && matriz[p][m.am]) ? matriz[p][m.am] : 0)
                    }));
                }

                function renderCharts(ano) {
                    const meses = gerarMesesAno(ano);
                    const labels = meses.map(m => m.label);

                    const dsPessoas = gerarDatasets(matrizPessoas, meses);
                    if (chartPessoas) {
                        chartPessoas.data.labels = labels;
                        chartPessoas.data.datasets = dsPessoas;
                        chartPessoas.update();
                    } else {
                        const ctxP = document.getElementById('chartRegAtivPessoas');
                        if (ctxP && typeof Chart !== 'undefined') {
                            chartPessoas = new Chart(ctxP, {
                                type: 'bar',
                                data: { labels: labels, datasets: dsPessoas },
                                options: {
                                    responsive: true,
                                    plugins: { legend: { position: 'top' } },
                                    scales: { y: { beginAtZero: true, title: { display: true, text: 'Pessoas distintas' } } }
                                }
                            });
                        }
                    }

                    const dsMinutos = gerarDatasets(matrizMinutos, meses);
                    if (chartMinutos) {
                        chartMinutos.data.labels = labels;
                        chartMinutos.data.datasets = dsMinutos;
                        chartMinutos.update();
                    } else {
                        const ctxM = document.getElementById('chartRegAtivMinutos');
                        if (ctxM && typeof Chart !== 'undefined') {
                            chartMinutos = new Chart(ctxM, {
                                type: 'bar',
                                data: { labels: labels, datasets: dsMinutos },
                                options: {
                                    responsive: true,
                                    plugins: { legend: { position: 'top' } },
                                    scales: { y: { beginAtZero: true, title: { display: true, text: 'Minutos validados' } } }
                                }
                            });
                        }
                    }

                    const dsCadastrados = gerarDatasets(matrizCadastrados, meses);
                    if (chartCadastrados) {
                        chartCadastrados.data.labels = labels;
                        chartCadastrados.data.datasets = dsCadastrados;
                        chartCadastrados.update();
                    } else {
                        const ctxC = document.getElementById('chartRegAtivCadastrados');
                        if (ctxC && typeof Chart !== 'undefined') {
                            chartCadastrados = new Chart(ctxC, {
                                type: 'bar',
                                data: { labels: labels, datasets: dsCadastrados },
                                options: {
                                    responsive: true,
                                    plugins: { legend: { position: 'top' } },
                                    scales: { y: { beginAtZero: true, title: { display: true, text: 'Inscritos cadastrados' } } }
                                }
                            });
                        }
                    }
                }

                function renderAno(ano) {
                    renderTabelaPessoas(ano);
                    renderTabelaMinutos(ano);
                    renderTabelaCadastrados(ano);
                    renderCharts(ano);
                }

                function init() {
                    if (inicializado) return;
                    const sel = document.getElementById('selectAnoEstat');
                    sel.addEventListener('change', () => renderAno(sel.value));
                    renderAno(sel.value);
                    inicializado = true;
                }

                // canvas em aba oculta tem tamanho 0 -> inicializar quando a aba aparecer
                if (window.jQuery) {
                    jQuery(function($){
                        $('a[href="#tab-estatisticas"]').on('shown.bs.tab', init);
                        if ($('#tab-estatisticas').hasClass('active')) init();
                    });
                } else {
                    document.addEventListener('DOMContentLoaded', init);
                }
            })();
            </script>
            <?php
        } catch (Exception $e) {
            echo '<div class="alert alert-warning" style="margin:10px 0">';
            echo $e->getMessage() ? htmlspecialchars($e->getMessage()) : 'N&atilde;o foi poss&iacute;vel carregar o painel.';
            echo '</div>';
        }
    }
}
?>
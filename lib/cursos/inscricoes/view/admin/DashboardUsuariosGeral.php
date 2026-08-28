<?php
// Dashboard geral de usuarios (cross-event). Acesso restrito ao usuario 668.
// Callers: area_admin_menu_login.php (item "Dashboard Geral", so visivel pro 668).

require_once(dirname(__FILE__) . "/../../include/config.php");

DashboardUsuariosGeralAC::guard();

$diasPadrao = isset($fwsGet_['dias']) && (int) $fwsGet_['dias'] > 0 ? (int) $fwsGet_['dias'] : 30;
$diasPorEvento = isset($fwsGet_['diasEvento']) && (int) $fwsGet_['diasEvento'] > 0 ? (int) $fwsGet_['diasEvento'] : 7;
$filtroEmailLogin = isset($fwsGet_['emailLogin']) ? trim(addslashesopctec($fwsGet_['emailLogin'])) : '';
$deTentativas  = isset($fwsGet_['tentDe'])  ? trim($fwsGet_['tentDe'])  : '';
$ateTentativas = isset($fwsGet_['tentAte']) ? trim($fwsGet_['tentAte']) : '';

$dados = DashboardUsuariosGeralAC::carregarDadosDashboard($diasPadrao, $diasPorEvento, $filtroEmailLogin, $deTentativas, $ateTentativas);

// Helpers de formatacao para Google Charts.
function formatarLinhasGraficoDia($lista) {
    $linhas = '';
    foreach ($lista as $row) {
        $dia = isset($row['dia']) ? formatDateView($row['dia']) : '';
        $total = (int) ($row['total'] ?? 0);
        $linhas .= "['" . $dia . "'," . $total . "],";
    }
    return $linhas;
}
function formatarLinhasGraficoCategoria($lista, $colCategoria, $colValor = 'total') {
    $linhas = '';
    foreach ($lista as $row) {
        $cat = isset($row[$colCategoria]) ? addslashes((string) $row[$colCategoria]) : '';
        $total = (int) ($row[$colValor] ?? 0);
        $linhas .= "['" . $cat . "'," . $total . "],";
    }
    return $linhas;
}

$linhasInscritosDia     = formatarLinhasGraficoDia($dados['inscritosPorDia']);
// Tentativas por dia com 3 series (total, sucesso, falha) para o grafico de linhas.
$linhasTentDetalhe = '';
foreach ($dados['tentativasDetalhePorDia'] as $row) {
    $dia = isset($row['dia']) ? formatDateView($row['dia']) : '';
    $linhasTentDetalhe .= "['" . $dia . "'," . (int) ($row['total'] ?? 0) . "," . (int) ($row['sucesso'] ?? 0) . "," . (int) ($row['falha'] ?? 0) . "],";
}
$listaTentativasPorLogin = $dados['tentativasPorLogin'];
$linhasLoginsSucessoDia = formatarLinhasGraficoDia($dados['loginsSucessoPorDia']);
$linhasLoginsFalhaDia   = formatarLinhasGraficoDia($dados['loginsFalhaPorDia']);
$linhasPorEvento        = formatarLinhasGraficoCategoria($dados['inscritosPorEvento'], 'titulo');
$linhasPorEstado        = formatarLinhasGraficoCategoria($dados['inscritosPorEstado'], 'estado');
$linhasPorCidadePR      = formatarLinhasGraficoCategoria($dados['inscritosPorCidadePR'], 'cidade');
?>
<?php
// Menus superiores (abas de modulos + sub-abas de usuarios) - mesma ordem do GerenciarGestaoAcessos
require_once(dirname(__FILE__) . "/../../view/admin/area_admin_menu_administracao.php");
require_once(dirname(__FILE__) . "/../../view/admin/area_admin_menu_usuarios.php");
?>

<h3>Dashboard Geral — Cross-Event</h3>
<p class="text-muted">Visão consolidada de todos os eventos. Acesso restrito.</p>

<!-- Usuarios ativos agora (atualizado por AJAX a cada 30s, sem recarregar a pagina) -->
<div class="panel panel-default" id="ativosAgoraBloco" style="margin-bottom:20px;">
    <?php DashboardUsuariosGeralAC::renderBlocoAtivos(); ?>
</div>

<script>
// Atualiza SO o bloco "Usuarios ativos agora" a cada 30s -- nao recarrega a pagina
// toda nem repinta os graficos. Se a sessao expirar, NAO injeta a tela de login no
// bloco: para o polling e mostra o overlay de sessao expirada.
(function () {
    var URL = '<?php echo WB_URL_COMMAND ?>&c=DashboardUsuariosGeral&m=carregarAtivosAgora';
    var handle = setInterval(function () {
        if (document.hidden) { return; }   // aba em segundo plano nao atualiza
        var x = new XMLHttpRequest();
        x.open('POST', URL + '&t=' + Date.now(), true);
        x.onreadystatechange = function () {
            if (x.readyState !== 4) { return; }
            var t = x.responseText || '';
            var expirou = (x.status === 401) || t.indexOf('sessaoExpirada') >= 0;
            var pareceBloco = t.indexOf('panel-heading') >= 0 && t.toLowerCase().indexOf('<html') < 0;
            if (x.status === 200 && pareceBloco && !expirou) {
                document.getElementById('ativosAgoraBloco').innerHTML = t;   // troca so o bloco
            } else if (expirou) {
                clearInterval(handle);   // para de pingar
                if (typeof window.sessaoExpiradaMostrar === 'function') {
                    window.sessaoExpiradaMostrar('admin');   // overlay, sem injetar login
                }
            }
            // qualquer outra resposta inesperada: ignora (mantem o bloco atual)
        };
        x.send();
    }, 30000);
})();
</script>

<form method="GET" action="index.php" style="margin-bottom:15px;">
    <input type="hidden" name="content" value="view/admin/DashboardUsuariosGeral.php">
    <label>Período dos gráficos diários:
        <select name="dias" onchange="this.form.submit()">
            <option value="7"   <?php echo $diasPadrao == 7   ? 'selected' : ''; ?>>7 dias</option>
            <option value="30"  <?php echo $diasPadrao == 30  ? 'selected' : ''; ?>>30 dias</option>
            <option value="90"  <?php echo $diasPadrao == 90  ? 'selected' : ''; ?>>90 dias</option>
            <option value="365" <?php echo $diasPadrao == 365 ? 'selected' : ''; ?>>1 ano</option>
        </select>
    </label>
    &nbsp;
    <label>Período para "por evento":
        <select name="diasEvento" onchange="this.form.submit()">
            <option value="7"  <?php echo $diasPorEvento == 7  ? 'selected' : ''; ?>>7 dias</option>
            <option value="14" <?php echo $diasPorEvento == 14 ? 'selected' : ''; ?>>14 dias</option>
            <option value="30" <?php echo $diasPorEvento == 30 ? 'selected' : ''; ?>>30 dias</option>
        </select>
    </label>
</form>

<div class="row" style="margin-bottom:15px;">
    <div class="col-sm-3">
        <div class="panel panel-default">
            <div class="panel-body text-center">
                <h4 style="color:#337ab7;"><?php echo $dados['totais']['inscritos_periodo']; ?></h4>
                <small>Inscritos nos últimos <?php echo $diasPorEvento; ?> dias</small>
            </div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="panel panel-default">
            <div class="panel-body text-center">
                <h4 style="color:#5cb85c;"><?php echo $dados['totais']['logins_sucesso']; ?></h4>
                <small>Logins com sucesso (<?php echo $diasPorEvento; ?> dias)</small>
            </div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="panel panel-default">
            <div class="panel-body text-center">
                <h4 style="color:#d9534f;"><?php echo $dados['totais']['logins_falha']; ?></h4>
                <small>Falhas de login (<?php echo $diasPorEvento; ?> dias)</small>
            </div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="panel panel-default">
            <div class="panel-body text-center">
                <h4 style="color:#f0ad4e;"><?php echo $dados['totais']['eventos_ativos']; ?></h4>
                <small>Eventos com inscrições</small>
            </div>
        </div>
    </div>
</div>

<ul class="nav nav-tabs" role="tablist">
    <li role="presentation" class="active"><a href="#tab-usuarios" aria-controls="tab-usuarios" role="tab" data-toggle="tab" data-bs-toggle="tab">Usuários</a></li>
    <li role="presentation"><a href="#tab-trabalhos" aria-controls="tab-trabalhos" role="tab" data-toggle="tab" data-bs-toggle="tab">Trabalhos</a></li>
    <li role="presentation"><a href="#tab-arquivos"  aria-controls="tab-arquivos"  role="tab" data-toggle="tab" data-bs-toggle="tab">Arquivos</a></li>
</ul>

<div class="tab-content" style="padding-top:15px;">

    <div role="tabpanel" class="tab-pane active" id="tab-usuarios">
        <div class="row">
            <div class="col-sm-12">
                <div class="panel panel-default"><div class="panel-body">
                    <div id="graficoTentativasPorDia" style="width:100%; height:280px;"></div>
                </div></div>
            </div>
        </div>

        <!-- Filtro SO da listagem abaixo (nao mexe no periodo do grafico acima, que
             tem form/campos proprios). Carrega dias/diasEvento em campos ocultos so
             pra nao perder a selecao dos graficos ao filtrar a lista. -->
        <form method="GET" action="index.php" style="margin-bottom:10px;">
            <input type="hidden" name="content" value="view/admin/DashboardUsuariosGeral.php">
            <input type="hidden" name="dias" value="<?php echo (int) $diasPadrao; ?>">
            <input type="hidden" name="diasEvento" value="<?php echo (int) $diasPorEvento; ?>">
            <label>Tentativas de login — E-mail:
                <input type="text" name="emailLogin" value="<?php echo htmlspecialchars($filtroEmailLogin); ?>" placeholder="filtrar por e-mail" class="form-control input-sm" style="display:inline-block; width:220px;">
            </label>
            &nbsp;
            <label>De:
                <input type="date" name="tentDe" value="<?php echo htmlspecialchars($deTentativas); ?>" class="form-control input-sm" style="display:inline-block; width:150px;">
            </label>
            &nbsp;
            <label>Até:
                <input type="date" name="tentAte" value="<?php echo htmlspecialchars($ateTentativas); ?>" class="form-control input-sm" style="display:inline-block; width:150px;">
            </label>
            &nbsp;
            <button type="submit" class="btn btn-sm btn-default"><i class="fa fa-search"></i> Filtrar</button>
            <?php if ($deTentativas !== '' || $ateTentativas !== ''): ?>
                <small class="text-muted">(De/Até substitui o período de <?php echo $diasPadrao; ?> dias só nesta lista)</small>
            <?php endif; ?>
        </form>

        <div class="row">
            <div class="col-sm-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <?php if ($deTentativas !== '' || $ateTentativas !== ''): ?>
                            Tentativas de login por e-mail (<?php echo $deTentativas !== '' ? formatDateView($deTentativas, false) : 'início'; ?> até <?php echo $ateTentativas !== '' ? formatDateView($ateTentativas, false) : 'hoje'; ?>)
                        <?php else: ?>
                            Tentativas de login por e-mail (últimos <?php echo $diasPadrao; ?> dias)
                        <?php endif; ?>
                        <?php echo $filtroEmailLogin !== '' ? ' — filtro: "' . htmlspecialchars($filtroEmailLogin) . '"' : ''; ?>
                        <?php if (count($listaTentativasPorLogin) >= 500): ?>
                            <span class="text-muted" style="font-weight:normal;"> (mostrando os 500 e-mails com mais tentativas)</span>
                        <?php endif; ?>
                    </div>
                    <div class="panel-body" style="padding:0;">
                        <?php if (empty($listaTentativasPorLogin)): ?>
                            <div class="text-muted text-center" style="padding:20px;">Nenhuma tentativa encontrada no período<?php echo $filtroEmailLogin !== '' ? ' para esse e-mail' : ''; ?>.</div>
                        <?php else: ?>
                            <table class="tbl-fin" id="tblTentativasPorLogin" style="width:100%;">
                                <thead>
                                    <tr>
                                        <th data-col="0" style="cursor:pointer;">E-mail <span class="sort-arrow">&uarr;&darr;</span></th>
                                        <th data-col="1" style="cursor:pointer; width:110px;">Tentativas <span class="sort-arrow">&uarr;&darr;</span></th>
                                        <th data-col="2" style="cursor:pointer; width:110px;">Com sucesso <span class="sort-arrow">&uarr;&darr;</span></th>
                                        <th data-col="3" style="cursor:pointer; width:110px;">Sem sucesso <span class="sort-arrow">&uarr;&darr;</span></th>
                                        <th data-col="4" style="cursor:pointer; width:150px;">Última tentativa <span class="sort-arrow">&uarr;&darr;</span></th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($listaTentativasPorLogin as $linha): ?>
                                    <tr>
                                        <td><a href="javascript:void(0)" onclick="abrirModalLoginEmail('<?php echo htmlspecialchars((string) $linha['login'], ENT_QUOTES); ?>')" title="Ver/gerenciar login deste e-mail"><?php echo htmlspecialchars((string) $linha['login']); ?></a></td>
                                        <td><?php echo (int) $linha['total']; ?></td>
                                        <td style="color:#2d6a4f;"><?php echo (int) $linha['sucesso']; ?></td>
                                        <td style="color:<?php echo ((int) $linha['falha'] > 0) ? '#c0392b' : '#999'; ?>;"><?php echo (int) $linha['falha']; ?></td>
                                        <td><?php echo htmlspecialchars(formatDateView($linha['ultima_tentativa'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de acoes de login por e-mail (compartilhado com Login de Inscritos). -->
        <?php require_once(dirname(__FILE__) . "/modal-acoes-login-email.php"); ?>

        <div class="row">
            <div class="col-sm-6">
                <div class="panel panel-default"><div class="panel-body">
                    <div id="graficoInscritosPorDia" style="width:100%; height:280px;"></div>
                </div></div>
            </div>
            <div class="col-sm-6">
                <div class="panel panel-default"><div class="panel-body">
                    <div id="graficoLoginsSucessoPorDia" style="width:100%; height:280px;"></div>
                </div></div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6">
                <div class="panel panel-default"><div class="panel-body">
                    <div id="graficoLoginsFalhaPorDia" style="width:100%; height:280px;"></div>
                </div></div>
            </div>
            <div class="col-sm-6">
                <div class="panel panel-default"><div class="panel-body">
                    <div id="graficoInscritosPorEvento" style="width:100%; height:280px;"></div>
                </div></div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6">
                <div class="panel panel-default"><div class="panel-body">
                    <div id="graficoInscritosPorEstado" style="width:100%; height:280px;"></div>
                </div></div>
            </div>
            <div class="col-sm-6">
                <div class="panel panel-default"><div class="panel-body">
                    <div id="graficoInscritosPorCidadePR" style="width:100%; height:280px;"></div>
                </div></div>
            </div>
        </div>
    </div>

    <div role="tabpanel" class="tab-pane" id="tab-trabalhos">
        <div class="alert alert-info">
            <strong>Em breve.</strong> Gráficos cross-event de trabalhos (submetidos, aprovados, por área temática, com anexo, etc.)
        </div>
    </div>

    <div role="tabpanel" class="tab-pane" id="tab-arquivos">
        <div class="alert alert-info">
            <strong>Em breve.</strong> Gráficos cross-event de arquivos enviados (apresentação, avulso, contrato, etc.)
        </div>
    </div>

</div>

<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">
google.charts.load("current", {packages: ["corechart", "bar"]});
google.charts.setOnLoadCallback(function() {
    desenharLinhaMulti('graficoTentativasPorDia',   'Tentativas de login por dia — total × sucesso × falha', [<?php echo $linhasTentDetalhe; ?>]);
    desenharColuna('graficoInscritosPorDia',        'Inscritos por Dia',            [<?php echo $linhasInscritosDia; ?>]);
    desenharColuna('graficoLoginsSucessoPorDia',    'Logins com sucesso por dia',   [<?php echo $linhasLoginsSucessoDia; ?>], '#5cb85c');
    desenharColuna('graficoLoginsFalhaPorDia',      'Falhas de login por dia',      [<?php echo $linhasLoginsFalhaDia; ?>], '#d9534f');
    desenharBarra ('graficoInscritosPorEvento',     'Inscritos por evento (últimos <?php echo $diasPorEvento; ?> dias)', [<?php echo $linhasPorEvento; ?>]);
    desenharColuna('graficoInscritosPorEstado',     'Inscritos por Estado',         [<?php echo $linhasPorEstado; ?>]);
    desenharColuna('graficoInscritosPorCidadePR',   'Inscritos por Cidade do Paraná (top 20)', [<?php echo $linhasPorCidadePR; ?>]);
});

function desenharColuna(idDiv, titulo, linhas, cor) {
    if (!linhas.length) {
        document.getElementById(idDiv).innerHTML = '<div class="text-muted text-center" style="padding:40px;">Sem dados no período.</div>';
        return;
    }
    var data = google.visualization.arrayToDataTable([['Categoria', 'Total']].concat(linhas));
    var options = {title: titulo, legend: {position: 'none'}};
    if (cor) options.colors = [cor];
    var chart = new google.visualization.ColumnChart(document.getElementById(idDiv));
    chart.draw(data, options);
}

function desenharBarra(idDiv, titulo, linhas) {
    if (!linhas.length) {
        document.getElementById(idDiv).innerHTML = '<div class="text-muted text-center" style="padding:40px;">Sem dados no período.</div>';
        return;
    }
    var data = google.visualization.arrayToDataTable([['Categoria', 'Total']].concat(linhas));
    var options = {title: titulo, legend: {position: 'none'}, chartArea: {left: 180}};
    var chart = new google.visualization.BarChart(document.getElementById(idDiv));
    chart.draw(data, options);
}

// Linhas sobrepostas: total, com sucesso e sem sucesso na mesma escala.
function desenharLinhaMulti(idDiv, titulo, linhas) {
    if (!linhas.length) {
        document.getElementById(idDiv).innerHTML = '<div class="text-muted text-center" style="padding:40px;">Sem dados no período.</div>';
        return;
    }
    var data = google.visualization.arrayToDataTable(
        [['Dia', 'Tentativas', 'Com sucesso', 'Sem sucesso']].concat(linhas)
    );
    var options = {
        title: titulo,
        legend: {position: 'top'},
        colors: ['#5bc0de', '#5cb85c', '#d9534f'],
        pointSize: 3,
        height: 280
    };
    var chart = new google.visualization.LineChart(document.getElementById(idDiv));
    chart.draw(data, options);
}
</script>

<?php // (menus ja incluidos no topo do arquivo) ?>

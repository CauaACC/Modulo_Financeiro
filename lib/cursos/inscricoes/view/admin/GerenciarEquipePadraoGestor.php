<?php
/**
 * GESTOR -> Equipe padrao. Tela FORA do evento.
 *
 * Define de qual evento a equipe e' herdada quando um projeto novo e' criado
 * naquele gestor. Antes disso o molde era sempre o ultimo evento ATIVADO --
 * o ultimo criado, real ou de teste.
 *
 * Acesso: ADM_GERAL, verificado de forma GERAL (cod_evento='TODOS'), porque a
 * tela nao pertence a nenhum evento.
 */
require_once(dirname(__FILE__) . "/../../include/config.php");

if (!FWA_AutenticacaoGeral($_SESSION['_cod_usuario'], enumSistema::SIS_GERENCIAR_ADM_GERAL, 5, false, false)) {
    $URL = WB_URL_VIEW . "?content=view/admin/login.php";
    $_SESSION['MSG'] = "Não há usuário logado, ou usuário logado não tem permissão de acesso!";
    echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $URL . "\"></HTML>";
    exit;
}

$tituloPagina = 'Equipe padrão por Gestor';
$submenu = 'area_admin_menu_gestor.php';
require_once(dirname(__FILE__) . "/../../view/admin/cabecalho-geral.php");

$objEq = new EquipePadraoGestor();
$listaGestores = $objEq->listarGestores();

/* Gestor aberto: o que veio na URL ou o do contexto atual (WB_GRUPO). */
$gestorSel = isset($fwsGet_['cod_gestor']) ? (int) $fwsGet_['cod_gestor'] : 0;
if (!$gestorSel) {
    foreach ($listaGestores as $g) {
        if (strtoupper($g['nome_gestor']) === strtoupper(WB_GRUPO)) {
            $gestorSel = $g['cod_gestor'];
            break;
        }
    }
}
$gestorAtual = null;
foreach ($listaGestores as $g) {
    if ($g['cod_gestor'] === $gestorSel) { $gestorAtual = $g; break; }
}

$eventos = $gestorAtual ? $objEq->listarEventosDoGestor($gestorAtual['nome_gestor']) : array();
$equipe  = ($gestorAtual && $gestorAtual['cod_evento_modelo'])
         ? $objEq->listarEquipeDoEvento($gestorAtual['cod_evento_modelo'])
         : array();
?>

<style>
    .eqp-cx { max-width: 900px; }
    .eqp-aviso { background:#fdf6e3; border-left:3px solid #c98307; padding:11px 14px; margin-bottom:16px;
                 font-size:13px; color:#5a4a20; line-height:1.5; border-radius:0 4px 4px 0; }
    .eqp-linha { margin-bottom:14px; }
    .eqp-linha label { display:block; font-size:12.5px; color:#555; margin-bottom:4px; font-weight:bold; }
    .eqp-linha select, .eqp-linha input[type=text] { font-size:13px; padding:5px 8px; max-width:100%; }
    .eqp-atual { font-size:13px; color:#333; background:#f4f7f4; border:1px solid #dbe5db;
                 padding:9px 12px; border-radius:4px; margin-bottom:16px; }
    .eqp-atual b { color:#2c6b2f; }
    .eqp-tab { width:100%; border-collapse:collapse; font-size:13px; border:1px solid #e3e3e3; }
    .eqp-tab th { background:#fafafa; text-align:left; padding:7px 10px; font-size:12px; color:#666; border-bottom:1px solid #e3e3e3; }
    .eqp-tab td { padding:6px 10px; border-bottom:1px solid #f2f2f2; }
    .eqp-tab tr:last-child td { border-bottom:none; }
    .eqp-vazio { padding:14px; color:#888; font-size:13px; }
    .eqp-msg { margin-top:12px; font-size:13px; padding:9px 12px; border-radius:4px; display:none; }
    .eqp-msg.ok { background:#eef7ee; border:1px solid #cfe3cf; color:#2c6b2f; }
    .eqp-msg.erro { background:#fdeeea; border:1px solid #f3cfc4; color:#a33; }
</style>

<div class="eqp-cx">

    <div class="eqp-aviso">
        Quando um projeto novo &eacute; criado, ele nasce com a <b>mesma equipe</b> de um evento
        anterior deste gestor &mdash; assim as pessoas j&aacute; enxergam o projeto na lista sem
        precisar ser adicionadas uma a uma. Aqui voc&ecirc; escolhe <b>qual evento</b> serve de
        modelo. Sem escolha, o sistema usa o &uacute;ltimo evento ativo, seja ele qual for.
    </div>

    <?php if (!$gestorAtual) { ?>
        <div class="eqp-vazio">Nenhum gestor ativo encontrado.</div>
    <?php } else { ?>

    <div class="eqp-linha">
        <label for="eqpGestor">Gestor</label>
        <select id="eqpGestor" onchange="eqpTrocarGestor(this.value)">
            <?php foreach ($listaGestores as $g) { ?>
            <option value="<?= $g['cod_gestor'] ?>" <?= $g['cod_gestor'] === $gestorSel ? 'selected' : '' ?>>
                <?= htmlspecialchars($g['nome_gestor']) ?>
            </option>
            <?php } ?>
        </select>
    </div>

    <div class="eqp-atual">
        <?php if ($gestorAtual['cod_evento_modelo']) { ?>
            Modelo atual: <b><?= htmlspecialchars($gestorAtual['titulo_modelo']) ?></b>
            (c&oacute;digo <?= (int) $gestorAtual['cod_evento_modelo'] ?>)
        <?php } else { ?>
            <b>Nenhum modelo definido.</b> Projetos novos herdam a equipe do &uacute;ltimo evento ativo deste gestor.
        <?php } ?>
    </div>

    <div class="eqp-linha">
        <label for="eqpFiltro">Filtrar evento</label>
        <input type="text" id="eqpFiltro" placeholder="digite parte do t&iacute;tulo ou o c&oacute;digo"
               oninput="eqpFiltrar()" onkeydown="if(event.key==='Enter'){event.preventDefault();}">
    </div>

    <div class="eqp-linha">
        <label for="eqpEvento">Evento modelo (<span id="eqpContador"><?= count($eventos) ?></span> dispon&iacute;veis)</label>
        <select id="eqpEvento" size="8" style="width:100%;" onchange="eqpVerEquipe(this.value)">
            <option value="">— nenhum (usar o &uacute;ltimo evento ativo) —</option>
            <?php foreach ($eventos as $ev) { ?>
            <option value="<?= $ev['cod_evento'] ?>" <?= ((int) $gestorAtual['cod_evento_modelo'] === $ev['cod_evento']) ? 'selected' : '' ?>>
                <?= $ev['cod_evento'] ?> — <?= htmlspecialchars($ev['titulo']) ?>
            </option>
            <?php } ?>
        </select>
    </div>

    <div class="eqp-linha">
        <label>Quem tem acesso a este evento hoje</label>
        <div id="eqpEquipe">
            <?php if (!$equipe) { ?>
                <div class="eqp-vazio">Escolha um evento acima para ver a equipe.</div>
            <?php } else { ?>
            <table class="eqp-tab">
                <tr><th>Pessoa</th><th>E-mail</th><th>Permiss&otilde;es</th></tr>
                <?php foreach ($equipe as $p) { ?>
                <tr>
                    <td><?= htmlspecialchars($p['nome'] ? $p['nome'] : '(sem nome)') ?></td>
                    <td><?= htmlspecialchars($p['login']) ?></td>
                    <td><?= (int) $p['qtd_grupos'] ?></td>
                </tr>
                <?php } ?>
            </table>
            <?php } ?>
        </div>
    </div>

    <button type="button" class="btn btn-primary btn-sm" onclick="eqpSalvar()">Salvar evento modelo</button>
    <div class="eqp-msg" id="eqpMsg"></div>

    <?php } ?>
</div>

<script>
var EQP_GESTOR = <?= (int) $gestorSel ?>;

function eqpTrocarGestor(cod) {
    window.location.href = '<?= WB_URL_VIEW ?>?content=view/admin/GerenciarEquipePadraoGestor.php&cod_gestor=' + cod;
}

/* Filtro por digitacao: com mais de 100 eventos o select solto e' inutilizavel.
 * Mesmo padrao do "Trocar evento" e do importar configuracao. */
function eqpFiltrar() {
    var termo = document.getElementById('eqpFiltro').value.toLowerCase();
    var sel = document.getElementById('eqpEvento');
    var visiveis = 0;
    for (var i = 0; i < sel.options.length; i++) {
        var op = sel.options[i];
        if (op.value === '') { continue; }
        var casa = op.text.toLowerCase().indexOf(termo) !== -1;
        op.hidden = !casa;
        if (casa) { visiveis++; }
    }
    document.getElementById('eqpContador').textContent = visiveis;
}

function eqpVerEquipe(codEvento) {
    var alvo = document.getElementById('eqpEquipe');
    if (!codEvento) {
        alvo.innerHTML = '<div class="eqp-vazio">Sem modelo: a equipe vem do &uacute;ltimo evento ativo.</div>';
        return;
    }
    alvo.innerHTML = '<div class="eqp-vazio">Carregando&hellip;</div>';
    var req = new XMLHttpRequest();
    req.open('POST', '<?= WB_URL_COMMAND ?>&t=' + Math.random(), true);
    req.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    req.onreadystatechange = function () {
        if (req.readyState !== 4) { return; }
        var d = null;
        try { d = JSON.parse(req.responseText); } catch (e) { d = null; }
        if (!d || d.statusMessage !== '1') {
            alvo.innerHTML = '<div class="eqp-vazio">N&atilde;o foi poss&iacute;vel carregar a equipe.</div>';
            return;
        }
        if (!d.equipe.length) {
            alvo.innerHTML = '<div class="eqp-vazio">Este evento n&atilde;o tem ningu&eacute;m com acesso ativo.</div>';
            return;
        }
        var html = '<table class="eqp-tab"><tr><th>Pessoa</th><th>E-mail</th><th>Permiss&otilde;es</th></tr>';
        for (var i = 0; i < d.equipe.length; i++) { html += '<tr><td></td><td></td><td></td></tr>'; }
        html += '</table>';
        alvo.innerHTML = html;
        var linhas = alvo.querySelectorAll('tr');
        for (var k = 0; k < d.equipe.length; k++) {
            var celulas = linhas[k + 1].querySelectorAll('td');
            celulas[0].textContent = d.equipe[k].nome ? d.equipe[k].nome : '(sem nome)';
            celulas[1].textContent = d.equipe[k].login;
            celulas[2].textContent = d.equipe[k].qtd_grupos;
        }
    };
    req.send('codEvento=' + encodeURIComponent(codEvento)
           + '&c=GerenciarEquipePadraoGestor&m=listarEquipe&_submit_check=1');
}

function eqpSalvar() {
    var cod = document.getElementById('eqpEvento').value;
    var msg = document.getElementById('eqpMsg');
    var req = new XMLHttpRequest();
    req.open('POST', '<?= WB_URL_COMMAND ?>&t=' + Math.random(), true);
    req.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    req.onreadystatechange = function () {
        if (req.readyState !== 4) { return; }
        var d = null;
        try { d = JSON.parse(req.responseText); } catch (e) { d = null; }
        msg.style.display = 'block';
        if (d && d.statusMessage === '1') {
            msg.className = 'eqp-msg ok';
            msg.textContent = d.message;
        } else {
            msg.className = 'eqp-msg erro';
            msg.textContent = (d && d.message) ? d.message : 'Não foi possível salvar.';
        }
    };
    req.send('codGestor=' + encodeURIComponent(EQP_GESTOR)
           + '&codEventoModelo=' + encodeURIComponent(cod)
           + '&c=GerenciarEquipePadraoGestor&m=salvarModelo&_submit_check=1');
}
</script>

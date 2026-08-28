<?php
/**
 * Modal do "duplicar projeto": mostra QUEM tem acesso ao projeto atual e deixa
 * escolher, pessoa por pessoa, quem vai junto para a copia.
 *
 * Incluida pelas DUAS telas que tem o botao (Editar Evento e aba Projeto), para
 * a pergunta ser a mesma nos dois caminhos.
 *
 * CSS e JS proprios, com XMLHttpRequest em vez de jQuery: nem toda tela que usa
 * isso carrega jQuery, e o bootstrap.js nem sempre esta presente.
 *
 * Identifica pelo e-mail (login): nome_usuario e' NULL em boa parte da base, e
 * o mesmo e-mail aparece em usuarios diferentes -- por isso o codigo tambem.
 */
?>
<style>
.dupm-fundo { display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,.45); align-items:center; justify-content:center; }
.dupm-fundo.aberto { display:flex; }
.dupm-cx { background:#fff; width:600px; max-width:94vw; max-height:88vh; border-radius:6px;
           box-shadow:0 8px 30px rgba(0,0,0,.3); display:flex; flex-direction:column;
           font-family:Arial,Helvetica,sans-serif; }
.dupm-cab { padding:16px 20px 12px; border-bottom:1px solid #e6e6e6; }
.dupm-cab h4 { margin:0; font-size:17px; color:#333; }
.dupm-cab p { margin:6px 0 0; font-size:12.5px; color:#777; line-height:1.45; }
.dupm-corpo { padding:12px 20px; overflow-y:auto; flex:1; }
.dupm-acoes { display:flex; gap:6px; margin-bottom:10px; flex-wrap:wrap; align-items:center; }
.dupm-acoes button { font-size:11.5px; padding:4px 9px; border:1px solid #ccc; background:#f6f6f6;
                     border-radius:3px; cursor:pointer; color:#333; }
.dupm-acoes button:hover { background:#eaeaea; }
.dupm-acoes .dupm-cont { margin-left:auto; font-size:12px; color:#666; }
.dupm-lista { border:1px solid #e0e0e0; border-radius:4px; max-height:46vh; overflow-y:auto; }
.dupm-p { display:flex; align-items:center; gap:9px; padding:7px 10px; border-bottom:1px solid #f0f0f0; cursor:pointer; }
.dupm-p:last-child { border-bottom:none; }
.dupm-p:hover { background:#f7fbff; }
.dupm-p input { flex:none; }
.dupm-p .quem { flex:1; min-width:0; }
.dupm-p .quem b { display:block; font-size:13px; color:#333; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.dupm-p .quem i { display:block; font-style:normal; font-size:11.5px; color:#888; }
.dupm-tag { font-size:10px; padding:2px 6px; border-radius:9px; white-space:nowrap; }
.dupm-tag.adm { background:#eef3fb; color:#2c4e86; border:1px solid #cfdcf0; }
.dupm-tag.ope { background:#f3f3f3; color:#777; border:1px solid #e2e2e2; }
.dupm-nota { font-size:12.5px; color:#555; background:#f3f8ef; border-left:3px solid #7aa869;
             padding:9px 11px; border-radius:0 4px 4px 0; margin-top:12px; }
.dupm-vazio { padding:14px; font-size:13px; color:#777; text-align:center; }
.dupm-rod { padding:12px 20px; border-top:1px solid #e6e6e6; display:flex; justify-content:flex-end; gap:8px; }
.dupm-rod button { padding:7px 16px; border-radius:4px; font-size:13px; cursor:pointer; }
.dupm-cancelar { background:#f0f0f0; color:#333; border:1px solid #ccc; }
.dupm-ok { background:#e0a020; color:#fff; border:1px solid #c98307; font-weight:bold; }
</style>

<div class="dupm-fundo" id="dupmFundo">
  <div class="dupm-cx" role="dialog" aria-modal="true" aria-labelledby="dupmTitulo">
    <div class="dupm-cab">
      <h4 id="dupmTitulo">Criar um novo projeto a partir deste</h4>
      <p>Vem junto: tipo, m&oacute;dulos, nota fiscal, textos, e-mails, grupos de participante,
         atividades e taxas. <b>N&atilde;o vem</b>: inscritos, pedidos nem pagamentos.</p>
    </div>

    <div class="dupm-corpo">
      <p style="margin:0 0 9px; font-size:13.5px; color:#333;">
        <b>Quer trazer tamb&eacute;m algu&eacute;m deste projeto?</b>
      </p>
      <div class="dupm-acoes">
        <button type="button" id="dupmTodos">Marcar todos</button>
        <button type="button" id="dupmSoAdm">S&oacute; a coordena&ccedil;&atilde;o</button>
        <button type="button" id="dupmNenhum">Desmarcar todos</button>
        <span class="dupm-cont" id="dupmContador"></span>
      </div>
      <div class="dupm-lista" id="dupmLista">
        <div class="dupm-vazio">Carregando as pessoas deste projeto&hellip;</div>
      </div>
      <p class="dupm-nota"><b>A equipe padr&atilde;o do gestor j&aacute; entra sozinha</b> no projeto novo &mdash;
         quem est&aacute; nela pode aparecer marcado aqui e vai de qualquer jeito. Esta lista serve para
         trazer <b>tamb&eacute;m</b> quem &eacute; s&oacute; deste projeto. Voc&ecirc; sempre ter&aacute; acesso,
         e quem estiver com acesso desativado aqui n&atilde;o aparece.</p>
    </div>

    <div class="dupm-rod">
      <button type="button" class="dupm-cancelar" id="dupmCancelar">Cancelar</button>
      <button type="button" class="dupm-ok" id="dupmConfirmar">Duplicar projeto</button>
    </div>
  </div>
</div>

<script>
var dupmAoConfirmar = null;
var dupmCarregou    = false;

function dupmContar() {
    var marcados = document.querySelectorAll('.dupm-p input:checked').length;
    var total    = document.querySelectorAll('.dupm-p input').length;
    var el = document.getElementById('dupmContador');
    el.textContent = total ? (marcados + ' de ' + total + ' selecionadas') : '';
}

function dupmMarcar(criterio) {
    var linhas = document.querySelectorAll('.dupm-p');
    for (var i = 0; i < linhas.length; i++) {
        var cx = linhas[i].querySelector('input');
        if (criterio === 'todos')  { cx.checked = true; }
        if (criterio === 'nenhum') { cx.checked = false; }
        if (criterio === 'adm')    { cx.checked = linhas[i].getAttribute('data-adm') === '1'; }
    }
    dupmContar();
}

function dupmCarregarLista(codEvento) {
    if (dupmCarregou) { return; }
    var alvo = document.getElementById('dupmLista');
    var req = new XMLHttpRequest();
    req.open('POST', '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), true);
    req.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    req.onreadystatechange = function () {
        if (req.readyState !== 4) { return; }
        var dados = null;
        try { dados = JSON.parse(req.responseText); } catch (e) { dados = null; }

        if (!dados || dados.statusMessage !== '1') {
            alvo.innerHTML = '<div class="dupm-vazio">N&atilde;o foi poss&iacute;vel carregar a lista.'
                           + ' Voc&ecirc; ainda pode duplicar sem trazer ningu&eacute;m.</div>';
            dupmContar();
            return;
        }
        var lista = dados.usuarios || [];
        if (!lista.length) {
            alvo.innerHTML = '<div class="dupm-vazio">Este projeto n&atilde;o tem outras pessoas com acesso ativo.</div>';
            dupmContar();
            dupmCarregou = true;
            return;
        }

        /* Monta a estrutura de uma vez; nome e e-mail entram depois por
         * textContent, para dado do banco nunca virar HTML. */
        var html = '';
        for (var i = 0; i < lista.length; i++) {
            var eAdm = lista[i].eh_admin === 1;
            html += '<label class="dupm-p" data-adm="' + (eAdm ? '1' : '0') + '">'
                 +    '<input type="checkbox" value="' + parseInt(lista[i].cod_usuario, 10) + '"' + (eAdm ? ' checked' : '') + '>'
                 +    '<span class="quem"><b></b><i></i></span>'
                 +    '<span class="dupm-tag ' + (eAdm ? 'adm' : 'ope') + '">' + (eAdm ? 'coordena&ccedil;&atilde;o' : 'operacional') + '</span>'
                 +  '</label>';
        }
        alvo.innerHTML = html;

        var linhas = alvo.querySelectorAll('.dupm-p');
        for (var k = 0; k < lista.length; k++) {
            var u = lista[k];
            var temNome = u.nome && String(u.nome).trim() !== '';
            linhas[k].querySelector('.quem b').textContent = temNome ? u.nome : u.login;
            linhas[k].querySelector('.quem i').textContent =
                (temNome ? u.login + ' - ' : '') + 'código ' + u.cod_usuario
                + ' - ' + u.qtd_grupos + ' permissões';
            linhas[k].querySelector('input').addEventListener('change', dupmContar);
        }
        dupmContar();
        dupmCarregou = true;
    };
    req.send('codEvento=' + encodeURIComponent(codEvento)
           + '&c=GerenciarEvento&m=listarUsuariosImportacao&_submit_check=1');
}

function dupmAbrir(codEvento, aoConfirmar) {
    dupmAoConfirmar = aoConfirmar;
    document.getElementById('dupmFundo').classList.add('aberto');
    dupmCarregarLista(codEvento);
}
function dupmFechar() {
    document.getElementById('dupmFundo').classList.remove('aberto');
}

document.addEventListener('DOMContentLoaded', function () {
    var fundo = document.getElementById('dupmFundo');
    if (!fundo) { return; }
    document.getElementById('dupmCancelar').addEventListener('click', dupmFechar);
    document.getElementById('dupmTodos').addEventListener('click', function () { dupmMarcar('todos'); });
    document.getElementById('dupmSoAdm').addEventListener('click', function () { dupmMarcar('adm'); });
    document.getElementById('dupmNenhum').addEventListener('click', function () { dupmMarcar('nenhum'); });
    fundo.addEventListener('click', function (e) { if (e.target === fundo) { dupmFechar(); } });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && fundo.classList.contains('aberto')) { dupmFechar(); }
    });
    document.getElementById('dupmConfirmar').addEventListener('click', function () {
        var marcados = document.querySelectorAll('.dupm-p input:checked');
        var codigos = [];
        for (var i = 0; i < marcados.length; i++) { codigos.push(marcados[i].value); }
        dupmFechar();
        if (typeof dupmAoConfirmar === 'function') { dupmAoConfirmar(codigos.join(',')); }
    });
});
</script>

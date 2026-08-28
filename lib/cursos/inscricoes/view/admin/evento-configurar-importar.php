<?php
/**
 * Bloco "Importar configuracao de outro projeto" — botao + modal, autocontido.
 *
 * Existe como include separado porque a acao precisa aparecer em DOIS pontos, e
 * os dois sao a mesma situacao vista de angulos diferentes:
 *
 *   1. evento-configurar-alterar.php, ramo "Codigo nao encontrado" — a tela em
 *      que se cai pelo menu quando o projeto ainda nao tem configuracao. E onde
 *      mora o botao "Incluir", e portanto o primeiro lugar em que alguem se
 *      pergunta "preciso preencher isso tudo na mao?".
 *   2. evento-configurar-campos-projeto.php (aba Projeto do formulario de
 *      INCLUIR), para quem ja clicou em Incluir e so entao pensou em copiar.
 *
 * Nunca sao renderizados juntos: o ramo (1) nao inclui o formulario, e o (2) so
 * existe dentro dele.
 *
 * REGRA: so aparece enquanto o projeto NAO tem configuracao. E isso que torna o
 * importar uma acao de uma vez so — depois dela a configuracao existe, o bloco
 * some, e nao ha caminho para importar de novo (que era como se acumulava perfil
 * repetido e como as taxas amplificavam).
 *
 * Sem jQuery de proposito: no ramo (1) a pagina nao carrega o jquery.js que o
 * formulario de configuracao traz, entao $.ajax nao existe la.
 */

/* Projetos do MESMO gestor. Filtrar aqui e' conveniencia; quem decide de
 * verdade e' o controller, porque quem manda o POST nao usa este <select>. */
$impOrigens = array();
try {
    $impObjLista = new Evento();
    $impObjLista->setGestor(WB_GRUPO);
    $impOrigens = $impObjLista->carregarListaEventosByGestor();
} catch (Exception $impExc) {
    $impOrigens = array();
}
?>
<style>
    .imp-ajuda       { font-size:11px; color:#888; display:block; margin-top:4px; }
    .imp-alerta      { font-size:11px; background:#fff8e1; border:1px solid #ffe082; color:#7a5c00;
                       padding:6px 10px; border-radius:5px; margin-top:8px; display:none; }
    .imp-modal-fundo { display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:10000; }
    /* flex-column + corpo rolavel: sem isto o modal inteiro rola e os botoes
       Cancelar/Importar caem abaixo do corte da tela em monitor baixo. */
    .imp-modal       { position:fixed; top:50%; left:50%; transform:translate(-50%,-50%);
                       width:min(640px, 92vw); max-height:88vh; background:#fff;
                       border-radius:8px; box-shadow:0 6px 28px rgba(0,0,0,.28); z-index:10001;
                       display:none; font-family:Helvetica,sans-serif;
                       flex-direction:column; overflow:hidden; }
    .imp-modal.aberto{ display:flex; }
    .imp-modal-topo  { padding:16px 20px; border-bottom:1px solid #eee; font-size:17px;
                       font-weight:bold; flex:0 0 auto; }
    .imp-modal-corpo { padding:18px 20px; font-size:13px; color:#444; line-height:1.5;
                       flex:1 1 auto; overflow-y:auto; }
    .imp-modal-pe    { padding:14px 20px; border-top:1px solid #eee; text-align:right;
                       flex:0 0 auto; background:#fff; }
    .imp-modal-x     { float:right; cursor:pointer; color:#999; font-weight:normal; font-size:20px; line-height:1; }
    .imp-modal select{ width:100%; margin:4px 0; padding:6px; }
    .imp-modal input[type=text] { width:100%; margin:4px 0 12px; padding:6px; box-sizing:border-box; }
    /* A tela de configuracao aplica `label { margin-left:15px }` global; dentro
       do modal isso desalinha o rotulo em relacao ao campo. */
    .imp-modal label { margin-left:0; display:block; margin-top:10px; }
    .imp-modal-aviso { background:#fff8e1; border:1px solid #ffe082; color:#7a5c00;
                       padding:10px 12px; border-radius:5px; margin-top:12px; font-size:12px; }
</style>

<button type="button" class="btn btn-primary btn-sm" id="impBtnAbrir">
    <i class="fa fa-download"></i> Importar de outro projeto
</button>
<span class="imp-ajuda">
    Copie a configuração de um projeto pronto e ajuste só o que mudar.
</span>

<div class="imp-modal-fundo" id="impFundo"></div>
<div class="imp-modal" id="impModal" role="dialog" aria-labelledby="impTitulo">
    <div class="imp-modal-topo" id="impTitulo">
        Importar configuração de outro projeto
        <span class="imp-modal-x" id="impFechar" title="Fechar">&times;</span>
    </div>
    <div class="imp-modal-corpo">
        <p>Este projeto ficará com a configuração do projeto que você escolher.
           Vêm junto os <b>perfis</b>, as <b>atividades</b>, as <b>taxas</b> e os
           <b>modelos de e-mail</b>.</p>

        <?php
        /* Filtro + lista, o mesmo par do "Trocar evento" (titulo_area_admin.php).
         * Com mais de cem projetos, um <select> simples obriga a rolar atras do
         * nome; aqui digitar filtra na hora.
         *
         * O que NAO foi copiado de la sao os atalhos (Enter e duplo-clique
         * confirmam). La eles so navegam; aqui gravariam a configuracao do
         * projeto. Escolher a origem e confirmar sao passos separados. */
        ?>
        <label for="impFiltro"><b>Filtrar:</b></label>
        <input type="text" id="impFiltro" class="form-control"
               placeholder="Digite parte do nome ou o código..." autocomplete="off">

        <label for="impOrigem"><b>Projeto de origem</b></label>
        <select id="impOrigem" size="8" style="height:auto;">
            <option value="">— escolha o projeto de origem —</option>
            <?php foreach ($impOrigens as $impEv) {
                if ($impEv->getCodEvento() == COD_EVENTO) { continue; } ?>
                <option value="<?php echo (int) $impEv->getCodEvento() ?>">
                    <?php echo $impEv->getCodEvento().' — '.htmlspecialchars($impEv->getTitulo()) ?>
                </option>
            <?php } ?>
        </select>
        <span class="imp-ajuda" id="impContador"></span>

        <div class="imp-modal-aviso">
            <b>Você só pode importar uma vez.</b> Depois disto a opção some.
            <ul style="margin:8px 0 0; padding-left:18px;">
                <li>Perfis e atividades vêm com &quot;Cópia de&quot; no nome — renomeie depois.</li>
                <li>As datas de realização são as <b>deste</b> projeto, não as do outro.</li>
                <li>Inscritos, pedidos e pagamentos <b>não</b> vêm.</li>
            </ul>
        </div>

        <div class="imp-alerta" id="impMsg"></div>
    </div>
    <div class="imp-modal-pe">
        <button type="button" class="btn btn-default btn-sm" id="impCancelar">Cancelar</button>
        <button type="button" class="btn btn-primary btn-sm" id="impConfirmar">
            <i class="fa fa-download"></i> Importar
        </button>
    </div>
</div>

<script type="text/javascript">
(function () {
    function el(id) { return document.getElementById(id); }

    function abrir(mostrar) {
        if (el('impFundo')) { el('impFundo').style.display = mostrar ? 'block' : 'none'; }
        /* Classe, e nao style.display: o modal precisa de display:flex quando
         * aberto (rodape fixo), e 'block' quebraria o layout. */
        if (el('impModal')) {
            el('impModal').className = mostrar ? 'imp-modal aberto' : 'imp-modal';
        }
        if (mostrar && el('impFiltro')) {
            el('impFiltro').value = '';
            filtrar();
            el('impFiltro').focus();
        }
    }

    /**
     * Esconde as opcoes que nao casam com o texto digitado, igual ao "Trocar
     * evento". Usa `hidden` em vez de display:none porque e' o que os
     * navegadores respeitam dentro de <select>.
     *
     * O placeholder (value="") nunca some: sem ele, um filtro que nao acha nada
     * deixaria a lista vazia e sem explicacao.
     */
    function filtrar() {
        var select = el('impOrigem');
        var termo  = (el('impFiltro').value || '').toLowerCase().trim();
        if (!select) { return; }

        var visiveis = 0, primeiro = null;
        for (var i = 0; i < select.options.length; i++) {
            var op = select.options[i];
            if (op.value === '') { op.hidden = false; continue; }
            var casa = op.text.toLowerCase().indexOf(termo) > -1;
            op.hidden = !casa;
            if (casa) {
                visiveis++;
                if (!primeiro) { primeiro = op; }
            }
        }

        /* Com filtro escrito, o primeiro resultado ja fica selecionado para
         * Enter bastar. SEM filtro, nao: aqui a escolha errada sobrescreve a
         * configuracao de um projeto, entao ninguem deve conseguir clicar
         * "Importar" e levar um projeto qualquer que estava no topo da lista.
         * (No "Trocar evento" o pre-selecionado e' inofensivo — so navega.) */
        if (termo !== '' && primeiro) { primeiro.selected = true; }
        else { select.value = ''; }

        var contador = el('impContador');
        if (contador) {
            contador.innerHTML = termo === ''
                ? visiveis + (visiveis === 1 ? ' projeto na lista.' : ' projetos na lista.')
                : (visiveis === 0 ? 'Nenhum projeto com esse nome.'
                                  : visiveis + (visiveis === 1 ? ' projeto encontrado.'
                                                               : ' projetos encontrados.'));
        }
    }

    /**
     * Ao terminar vai para a tela de configuracao do projeto. Nao adianta
     * recarregar a tela atual: as duas de onde este bloco e' chamado sao telas
     * de "ainda nao existe configuracao", e a configuracao passou a existir.
     */
    function importar() {
        var box    = el('impMsg');
        var select = el('impOrigem');
        var botao  = el('impConfirmar');
        var origem = select ? select.value : '';

        if (origem === '') {
            box.innerHTML = 'Escolha um projeto na lista.';
            box.style.display = 'block';
            return;
        }

        var nome = select.options[select.selectedIndex].text.trim();
        if (!confirm('Copiar a configuração de:\n\n' + nome + '\n\n'
                   + 'Você só pode importar uma vez.')) {
            return;
        }

        botao.disabled = true;
        box.innerHTML = 'Importando…';
        box.style.display = 'block';

        var req = new XMLHttpRequest();
        req.open('POST', '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), true);
        req.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        req.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        req.onreadystatechange = function () {
            if (req.readyState !== 4) { return; }
            var data = null;
            try { data = JSON.parse(req.responseText); } catch (e) { data = null; }

            if (data && data.statusMessage == '1') {
                // A resposta diz QUANTOS perfis, atividades, taxas e templates
                // entraram. E a unica chance de o usuario ver esse numero.
                box.innerHTML = (data.message || 'Configuração importada.')
                    + '<br>Abrindo a configuração do projeto…';
                setTimeout(function () {
                    document.location.href = 'index.php?content=view/admin/'
                        + 'evento-configurar-alterar.php&cod_evento=<?php echo COD_EVENTO ?>';
                }, 4000);
            } else {
                botao.disabled = false;
                box.innerHTML = (data && data.message) ? data.message
                                                       : 'Não foi possível importar.';
            }
        };
        req.send('codEventoOrigem=' + encodeURIComponent(origem)
               + '&c=GerenciarEvento&m=importarConfiguracao&_submit_check=1');
    }

    function ligar() {
        if (el('impBtnAbrir')) {
            el('impBtnAbrir').addEventListener('click', function () { abrir(true); });
        }
        if (el('impConfirmar')) {
            el('impConfirmar').addEventListener('click', importar);
        }
        if (el('impFiltro')) {
            /* `input` e nao `keyup`: pega tambem colar, autofill e teclado de
             * celular, que nao emitem keyup. */
            el('impFiltro').addEventListener('input', filtrar);

            /* Enter aqui NAO importa e NAO submete nada.
             *
             * Nao importa porque escolher a origem e' meia-decisao: o usuario
             * ainda precisa olhar o que selecionou. No "Trocar evento" o Enter
             * so navega; aqui ele gravaria.
             *
             * E precisa de preventDefault porque na tela de INCLUIR este modal
             * vive dentro do <form> da configuracao — sem isso o Enter dispara
             * o "Cadastrar" e grava a configuracao em branco. */
            el('impFiltro').addEventListener('keydown', function (e) {
                if (e.key === 'Enter') { e.preventDefault(); }
            });
        }
        /* Sem atalho de duplo-clique na lista, de proposito: o usuario deu dois
         * cliques so para selecionar e a importacao aconteceu. Selecionar e
         * confirmar sao passos separados — o unico gatilho e o botao Importar. */
        var fechar = ['impFechar', 'impCancelar', 'impFundo'];
        for (var i = 0; i < fechar.length; i++) {
            if (el(fechar[i])) {
                el(fechar[i]).addEventListener('click', function () { abrir(false); });
            }
        }
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') { abrir(false); }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', ligar);
    } else {
        ligar();
    }
})();
</script>

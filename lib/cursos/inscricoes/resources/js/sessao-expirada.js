/**
 * Sessao expirada — aviso e ida ao login, em qualquer tela.
 *
 * Problema que resolve: com a sessao ja' morta, uma acao AJAX voltava um erro
 * generico e a tela seguia como se nada tivesse acontecido. Na consulta do
 * PagSeguro chegava a dizer "Nenhuma transacao encontrada", afirmando ausencia
 * de dados quando o caso era falta de login. Como sao 186 telas chamando o
 * CommandAC, o tratamento vive aqui, num handler global, e nao tela por tela.
 *
 * O servidor marca o caso (CommandAC devolve sessaoExpirada + area); aqui so se
 * reage. Falta de PERMISSAO nao passa por aqui de proposito: mandar para o login
 * quem esta logado e' laco, o login devolveria a mesma tela proibida.
 *
 * O visual segue o modal que ja existia em
 * view/inscricao/inscricao_trabalhos_consulta_trabalhos_submetidos_prorede2_129.php.
 * Naquela tela o controle e' proprio (contador + keep-alive), entao este script
 * sai de cena quando o modal dela esta presente.
 */
(function () {
    'use strict';

    if (typeof jQuery === 'undefined') { return; }

    var $ = jQuery;
    var SEGUNDOS_ATE_REDIRECIONAR = 5;
    var jaDisparou = false;

    /** A tela do 129 tem controle proprio de sessao — nao concorrer com ele. */
    function telaTemControleProprio() {
        return $('#sessaoExpirouModal').length > 0
            || typeof window.sessaoExpirouFlag !== 'undefined';
    }

    /**
     * Login de destino. Prefere a area informada pelo servidor; sem ela, o
     * proprio endereco diz de onde veio (a sessao ja nao existe para consultar).
     */
    function urlDoLogin(area) {
        if (!area) {
            area = (window.location.href.indexOf('view/admin/') >= 0) ? 'admin' : 'inscrito';
        }
        var pagina = (area === 'inscrito')
            ? 'view/inscricao/inscricao_login.php'
            : 'view/admin/login.php';
        return window.location.pathname + '?content=' + pagina;
    }

    /** Le o corpo da resposta tolerando o que nao for JSON (ex.: HTML de login). */
    function corpoDaResposta(jqXHR) {
        if (!jqXHR) { return null; }
        if (jqXHR.responseJSON) { return jqXHR.responseJSON; }
        var txt = jqXHR.responseText;
        if (!txt || typeof txt !== 'string' || txt.indexOf('sessaoExpirada') < 0) { return null; }
        try { return JSON.parse(txt); } catch (e) { return null; }
    }

    function avisarEIrParaLogin(area) {
        if (jaDisparou || telaTemControleProprio()) { return; }
        jaDisparou = true;

        var destino = urlDoLogin(area);

        $('<div id="avisoSessaoExpirada">'
            + '<div class="caixa">'
            +   '<h3>&#9888; Sua sess&atilde;o expirou</h3>'
            +   '<p>Por inatividade, sua sess&atilde;o foi encerrada. '
            +      'A &uacute;ltima a&ccedil;&atilde;o <strong>n&atilde;o foi conclu&iacute;da</strong>.</p>'
            +   '<p class="contagem">Redirecionando para o login em '
            +      '<strong><span id="avisoSessaoSegundos">' + SEGUNDOS_ATE_REDIRECIONAR + '</span></strong>s...</p>'
            +   '<a href="' + destino + '" class="btn-entrar">Entrar agora</a>'
            + '</div>'
        + '</div>').appendTo('body');

        var restante = SEGUNDOS_ATE_REDIRECIONAR;
        var handle = setInterval(function () {
            restante -= 1;
            $('#avisoSessaoSegundos').text(restante > 0 ? restante : 0);
            if (restante <= 0) {
                clearInterval(handle);
                window.location.href = destino;
            }
        }, 1000);
    }

    function tratar(jqXHR) {
        var corpo = corpoDaResposta(jqXHR);
        if (corpo && corpo.sessaoExpirada === true) {
            avisarEIrParaLogin(corpo.area);
        }
    }

    // ajaxSuccess porque o CommandAC responde 200 com o erro no corpo (mudar para
    // 401 jogaria as respostas para o ramo de erro e mudaria o comportamento das
    // dezenas de telas que tratam tudo em success). ajaxError cobre 401/403 que
    // venham de outros pontos.
    $(document).ajaxSuccess(function (evt, jqXHR) { tratar(jqXHR); });
    $(document).ajaxError(function (evt, jqXHR) {
        if (jqXHR && (jqXHR.status === 401 || jqXHR.status === 403)) {
            avisarEIrParaLogin(null);
            return;
        }
        tratar(jqXHR);
    });

    // Estilo junto do script: sao poucas regras, so deste aviso, e assim uma
    // unica linha no bootstrap-loader liga tudo.
    $(function () {
        $('<style>'
            + '#avisoSessaoExpirada{position:fixed;top:0;left:0;width:100vw;height:100vh;'
            +   'background:rgba(0,0,0,0.75);z-index:99999;display:flex;'
            +   'justify-content:center;align-items:center;}'
            + '#avisoSessaoExpirada .caixa{background:#fff;padding:30px 40px;border-radius:10px;'
            +   'max-width:500px;text-align:center;box-shadow:0 5px 20px rgba(0,0,0,0.3);'
            +   'font-family:Arial,Helvetica,sans-serif;}'
            + '#avisoSessaoExpirada h3{color:#d9534f;margin-top:0;}'
            + '#avisoSessaoExpirada p{color:#555;font-size:14px;line-height:1.5;}'
            + '#avisoSessaoExpirada .contagem{color:#888;font-size:13px;}'
            + '#avisoSessaoExpirada .btn-entrar{display:inline-block;background:#0073aa;color:#fff;'
            +   'padding:12px 24px;border-radius:5px;font-size:16px;text-decoration:none;margin-top:15px;}'
            + '#avisoSessaoExpirada .btn-entrar:hover{background:#005a87;color:#fff;}'
        + '</style>').appendTo('head');
    });

    // Exposto para o heartbeat de presenca disparar o MESMO aviso de forma
    // proativa (sem esperar uma acao do usuario) quando detecta a sessao morta.
    // Mantem os guards internos (jaDisparou / telaTemControleProprio -> 129 intacto).
    window.sessaoExpiradaMostrar = avisarEIrParaLogin;
})();

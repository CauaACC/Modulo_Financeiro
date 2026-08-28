<?php
/**
 * Campos "espelho": editar na aba Projeto OU na aba de origem, tanto faz.
 *
 * O problema. Dezesseis configuracoes foram movidas para a aba Projeto e nas
 * abas de origem viraram texto ("Sim / Configurado na aba Projeto"). Quem estava
 * acostumado a liga-las onde sempre esteveram passou a ter de caçar a aba nova.
 *
 * Por que nao basta repetir o campo. As 10 abas vivem dentro de UM UNICO
 * <form>. Dois inputs com o mesmo `name` no mesmo POST fazem o ULTIMO vencer —
 * o usuario mudaria numa aba, o valor de outra sobrescreveria, e a gravacao
 * sairia errada sem nenhum aviso. Foi por isso que os campos foram MOVIDOS, e
 * nao copiados.
 *
 * A saida. Continua existindo UM SO campo que vai no POST — o da aba Projeto,
 * chamado aqui de CANONICO. O que se renderiza na aba de origem e' um ESPELHO:
 * mesma aparencia, `name` proprio (`esp_<campo>`, que os radios exigem para
 * agrupar) e `data-espelho` apontando para o canonico. O JS mantem os dois
 * iguais nos dois sentidos, e antes do submit desabilita os espelhos para que
 * nenhum deles chegue ao servidor.
 *
 * O espelho tambem copia o estado DESABILITADO do canonico: a aba Projeto
 * desabilita o que nao se aplica ao tipo do projeto, justamente para o campo
 * ficar fora do POST e preservar o valor gravado. Um espelho ainda clicavel ali
 * daria a impressao de que a mudanca foi salva quando nao foi.
 */

/** Par Sim/Nao espelhado. $valor e' o que esta gravado hoje. */
function espelhoSimNao($campo, $valor) {
    $sim = verifItemChecked($valor, '1', false);
    $nao = verifItemChecked($valor, array('', 0, false), false);
    echo "<span data-espelho-de='".htmlspecialchars($campo)."'>"
       . "<input type='radio' name='esp_".htmlspecialchars($campo)."' value='1' data-espelho='".htmlspecialchars($campo)."' {$sim} /> Sim"
       . "&nbsp;&nbsp;"
       . "<input type='radio' name='esp_".htmlspecialchars($campo)."' value='0' data-espelho='".htmlspecialchars($campo)."' {$nao} /> Não"
       . "</span>"
       . "<span style='font-size:11px;color:#888;display:block;margin-top:2px;'>"
       . "Também está na aba <b>Projeto</b>, junto das outras chaves do projeto."
       . "</span>";
}

/** <select> espelhado. $opcoes = array(valor => rotulo). */
function espelhoSelect($campo, $valor, $opcoes) {
    echo "<select name='esp_".htmlspecialchars($campo)."' data-espelho='".htmlspecialchars($campo)."'>";
    foreach ($opcoes as $v => $rotulo) {
        $sel = ((string) $valor === (string) $v) ? "selected" : "";
        echo "<option value='".htmlspecialchars($v)."' {$sel}>".htmlspecialchars($rotulo)."</option>";
    }
    echo "</select>"
       . "<span style='font-size:11px;color:#888;display:block;margin-top:2px;'>"
       . "Também está na aba <b>Projeto</b>, junto das outras chaves do projeto."
       . "</span>";
}
?>
<script type="text/javascript">
(function () {
    function canonicos(campo) {
        return document.querySelectorAll("[name='" + campo + "']");
    }
    function espelhos(campo) {
        return document.querySelectorAll("[data-espelho='" + campo + "']");
    }
    function todosOsEspelhos() {
        return document.querySelectorAll("[data-espelho]");
    }

    function valorDe(lista) {
        for (var i = 0; i < lista.length; i++) {
            var e = lista[i];
            if (e.type === 'radio') { if (e.checked) { return e.value; } }
            else { return e.value; }
        }
        return null;
    }

    function aplicarValor(lista, valor) {
        for (var i = 0; i < lista.length; i++) {
            var e = lista[i];
            if (e.type === 'radio') { e.checked = (e.value === valor); }
            else { e.value = valor; }
        }
    }

    /** Espelho -> canonico. Dispara `change` no canonico para que as regras da
     *  aba Projeto (avisos, campos que somem) reajam como se o clique tivesse
     *  sido feito la. */
    function aoMudarEspelho(campo) {
        var valor = valorDe(espelhos(campo));
        if (valor === null) { return; }
        var alvo = canonicos(campo);
        aplicarValor(alvo, valor);
        for (var i = 0; i < alvo.length; i++) {
            alvo[i].dispatchEvent(new Event('change', { bubbles: true }));
        }
    }

    /** Canonico -> espelho, inclusive o estado desabilitado. */
    function sincronizarDoCanonico(campo) {
        var lista = canonicos(campo);
        if (!lista.length) { return; }
        var valor = valorDe(lista);
        var esp = espelhos(campo);
        if (valor !== null) { aplicarValor(esp, valor); }

        var bloqueado = lista[0].disabled;
        for (var i = 0; i < esp.length; i++) {
            esp[i].disabled = bloqueado;
        }
        // Quando o tipo do projeto nao comporta a opcao, a aba Projeto esconde a
        // linha inteira; aqui basta esmaecer para o motivo continuar visivel.
        var caixas = document.querySelectorAll("[data-espelho-de='" + campo + "']");
        for (var k = 0; k < caixas.length; k++) {
            caixas[k].style.opacity = bloqueado ? '.45' : '';
            caixas[k].title = bloqueado
                ? 'Esta opção não se aplica ao tipo escolhido na aba Projeto.' : '';
        }
    }

    function ligar() {
        var vistos = {};
        var lista = todosOsEspelhos();

        for (var i = 0; i < lista.length; i++) {
            var campo = lista[i].getAttribute('data-espelho');
            vistos[campo] = true;
            lista[i].addEventListener('change', (function (c) {
                return function () { aoMudarEspelho(c); };
            })(campo));
        }

        for (var campo in vistos) {
            if (!vistos.hasOwnProperty(campo)) { continue; }
            var canon = canonicos(campo);
            for (var j = 0; j < canon.length; j++) {
                canon[j].addEventListener('change', (function (c) {
                    return function () { sincronizarDoCanonico(c); };
                })(campo));
            }
            sincronizarDoCanonico(campo);
        }

        /* A aba Projeto habilita e desabilita campos ao trocar o tipo do
         * projeto, sem disparar `change`. Um observer mantem os espelhos
         * coerentes sem precisar acoplar os dois arquivos. */
        if (window.MutationObserver) {
            var observador = new MutationObserver(function () {
                for (var campo in vistos) {
                    if (vistos.hasOwnProperty(campo)) { sincronizarDoCanonico(campo); }
                }
            });
            for (var campo2 in vistos) {
                if (!vistos.hasOwnProperty(campo2)) { continue; }
                var alvos = canonicos(campo2);
                for (var m = 0; m < alvos.length; m++) {
                    observador.observe(alvos[m], { attributes: true, attributeFilter: ['disabled'] });
                }
            }
        }

        /* O que garante a gravacao correta: nenhum espelho vai no POST. Sem
         * isto o servidor receberia esp_* alem do campo real. */
        var forms = document.getElementsByTagName('form');
        for (var f = 0; f < forms.length; f++) {
            forms[f].addEventListener('submit', function () {
                var todos = todosOsEspelhos();
                for (var t = 0; t < todos.length; t++) { todos[t].disabled = true; }
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', ligar);
    } else {
        ligar();
    }
})();
</script>

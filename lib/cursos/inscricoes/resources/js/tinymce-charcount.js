/**
 * Limite de caracteres para editores TinyMCE.
 * Conta caracteres do texto puro (sem tags HTML) via editor.getContent({format:'text'}).
 *
 * Uso:
 *   1. Carregar este script depois do tinymce.min.js
 *   2. Na chamada tinymce.init({...}):
 *        setup: function(editor) { tinyMceCharCount.attach(editor, 5000); }
 *   3. Antes de salvar (em funcoes tipo salvarPerfil(), salvarProjeto()):
 *        if (!tinyMceCharCount.podeSalvar()) return;
 *   4. Para forms com submit nativo, o hook automatico ja bloqueia o submit
 *      quando o form contem <textarea class="editor_padrao">.
 */
(function () {
    var EDITORS = []; // {editor, max, overlimit}

    function getCount(editor) {
        try {
            return (editor.getContent({format: 'text'}) || '').length;
        } catch (e) {
            return 0;
        }
    }

    function getCounterEl(editor) {
        var $ta = $('#' + editor.id);
        if (!$ta.length) return null;
        var $wrap = $ta.next('.mce-tinymce');
        var $c;
        if ($wrap.length) {
            $c = $wrap.next('.tinymce-char-counter');
            if (!$c.length) {
                $c = $('<div class="tinymce-char-counter" data-for="' + editor.id + '" style="text-align:right;font-size:12px;color:#777;margin-top:3px;padding-right:4px;"></div>');
                $wrap.after($c);
            }
        } else {
            $c = $ta.next('.tinymce-char-counter');
            if (!$c.length) {
                $c = $('<div class="tinymce-char-counter" data-for="' + editor.id + '" style="text-align:right;font-size:12px;color:#777;margin-top:3px;padding-right:4px;"></div>');
                $ta.after($c);
            }
        }
        return $c;
    }

    function atualizar(entry) {
        var count = getCount(entry.editor);
        var $c = getCounterEl(entry.editor);
        if (!$c) return;
        $c.text(count + ' / ' + entry.max + ' caracteres');
        if (count > entry.max) {
            $c.css({color: '#a94442', fontWeight: 'bold'});
            entry.overlimit = true;
        } else {
            $c.css({color: '#777', fontWeight: 'normal'});
            entry.overlimit = false;
        }
    }

    function showAlert(mensagem) {
        var $alert = $('#tinymce-charcount-alert');
        if (!$alert.length) {
            $alert = $('<div id="tinymce-charcount-alert" class="alert alert-danger alert-dismissible" role="alert" style="position:fixed;top:20px;left:50%;transform:translateX(-50%);z-index:99999;max-width:640px;box-shadow:0 4px 12px rgba(0,0,0,.15);"><button type="button" class="close" data-dismiss="alert" aria-label="Fechar"><span aria-hidden="true">&times;</span></button><span class="msg"></span></div>');
            $('body').append($alert);
            $alert.on('click', '.close', function () { $alert.hide(); });
        }
        $alert.find('.msg').html(mensagem);
        $alert.show();
        clearTimeout($alert.data('hideTimer'));
        $alert.data('hideTimer', setTimeout(function () { $alert.fadeOut(); }, 8000));
    }

    window.tinyMceCharCount = {
        attach: function (editor, max) {
            var entry = {editor: editor, max: max || 5000, overlimit: false};
            EDITORS.push(entry);
            editor.on('init keyup change blur SetContent', function () { atualizar(entry); });
        },
        podeSalvar: function () {
            if (window.tinymce) {
                try { tinymce.triggerSave(); } catch (e) {}
            }
            EDITORS.forEach(atualizar);
            var violadores = EDITORS.filter(function (e) { return e.overlimit; });
            if (violadores.length === 0) return true;
            var max = violadores[0].max;
            var nomes = violadores.map(function (e) {
                var $ta = $('#' + e.editor.id);
                var name = $ta.attr('name') || e.editor.id;
                return '<b>' + name + '</b>';
            }).join(', ');
            showAlert('Conte&uacute;do excede o limite de <b>' + max + '</b> caracteres no(s) campo(s): ' + nomes + '.<br>Reduza o texto antes de salvar.');
            return false;
        }
    };

    // Auto-hook em forms com textarea.editor_padrao (submit nativo)
    $(document).on('submit', 'form', function (e) {
        var $form = $(this);
        if ($form.find('textarea.editor_padrao').length === 0) return;
        if (!window.tinyMceCharCount.podeSalvar()) {
            e.preventDefault();
            return false;
        }
    });
})();

<!-- Modal Gerenciar Email Template (reutilizável — compatível BS3 e BS5) -->
<style>.tox-tinymce-aux, .mce-window, .mce-floatpanel { z-index: 100100 !important; } .mce-modal-block { z-index: 100050 !important; }</style>
<script>
// Fix: Bootstrap modal intercepta foco e impede digitar no TinyMCE (link, code, etc.)
$(document).on('focusin', function(e) {
    if ($(e.target).closest('.mce-window, .mce-floatpanel, .tox-tinymce-aux, .tox-dialog').length) {
        e.stopImmediatePropagation();
    }
});
</script>
<div class="modal fade" id="modalEmailTemplate" role="dialog" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header modalCabecalho">
        <h4 class="modal-title" id="modalEmailTemplateTitle"><i class="fa fa-envelope"></i> Novo Template</h4>
        <button type="button" class="close modalBtnFechar" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="mdt_codEmailTemplate">
        <input type="hidden" id="mdt_updateEmailTemplate" value="0">
        <input type="hidden" id="mdt_corpoTemp">
        <div class="row">
          <div class="col-sm-9">
            <div class="form-group">
              <label for="mdt_assunto">Assunto:</label>
              <input type="text" id="mdt_assunto" class="form-control">
            </div>
          </div>
          <div class="col-sm-3">
            <div class="form-group">
              <label for="mdt_status">Status:</label>
              <select id="mdt_status" class="form-control">
                <option value="ATIVADA">ATIVADO</option>
                <option value="DESATIVADA">DESATIVADO</option>
              </select>
            </div>
          </div>
        </div>
        <div class="form-group">
          <label for="mdt_corpo">Corpo da mensagem:</label>
          <textarea id="mdt_corpo" class="form-control" rows="8"></textarea>
        </div>
        <div class="form-group">
          <label for="mdt_descricao">Descri&ccedil;&atilde;o / objetivo desse e-mail: <small class="text-muted">(opcional)</small></label>
          <textarea id="mdt_descricao" class="form-control" rows="2"></textarea>
        </div>
        <div class="row">
          <div class="col-sm-6">
            <div class="form-group">
              <label for="mdt_tipoDestinatario">Perfil dos Destinat&aacute;rios: <small class="text-muted">(opcional)</small></label>
              <input type="text" id="mdt_tipoDestinatario" class="form-control">
            </div>
          </div>
          <div class="col-sm-6">
            <div class="form-group">
              <label for="mdt_modeloChave">Identificador Abreviado: <small class="text-muted">(opcional)</small></label>
              <input type="text" id="mdt_modeloChave" class="form-control">
            </div>
          </div>
        </div>
        <div id="mdt_mensagem"></div>
      </div>
      <div class="modal-footer modalRodape">
        <button type="button" class="btn btn-default modalBtnCancelar" data-dismiss="modal">Fechar</button>
        <button type="button" class="btn modalBtnSalvar" id="btnMdtSalvar" onclick="mdtSalvar(false)"><i class="fa fa-save"></i> Salvar</button>
        <button type="button" class="btn btn-info" id="btnMdtSalvarComoNovo" onclick="mdtSalvar(true)" style="display:none;">Salvar como Novo</button>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
// --- Modal Email Template: variáveis e config TinyMCE ---
var _mdtVariaveis = [
    {text: 'Nome Completo',     value: '[NOME_INSCRITO]'},
    {text: 'Pré-nome',          value: '[PRE_NOME]'},
    {text: 'Login',             value: '[LOGIN]'},
    {text: 'Credencial',        value: '[CREDENCIAL]'},
    {text: 'Nome do Projeto',   value: '[NOME_PROJETO]'},
    {text: 'Link Pagamento',    value: '[LINK_PAGAMENTO]'},
    {text: 'Link Área Inscrito',value: '[LINK_AREA_INSCRITO]'}
];
var _mdtTinyConfig = {
    plugins: "code textcolor link table",
    menubar: false,
    statusbar: true,
    height: 350,
    resize: "both",
    force_br_newlines: true,
    force_p_newlines: false,
    forced_root_block: '',
    toolbar: "code | undo redo | bold italic underline | alignleft aligncenter alignright | bullist numlist | forecolor backcolor fontsizeselect | link | table | variaveis",
    textcolor_map: ["FF0000","Red","008B45","Green","0000FF","Blue"],
    fontsize_formats: "10pt 12pt 14pt 18pt 24pt 36pt",
    setup: function(editor) {
        editor.addButton('variaveis', {
            type: 'menubutton',
            text: 'Variáveis',
            icon: false,
            menu: _mdtVariaveis.map(function(v) {
                return {
                    text: v.text + '  ' + v.value,
                    onclick: function() { editor.insertContent(v.value); }
                };
            })
        });
    }
};

// Callback opcional — cada página define o seu
var _mdtOnSalvo = null;

// Helper: show/hide modal compatível BS3 e BS5
function _mdtShowModal() {
    $('#modalEmailTemplate').modal('show');
}
function _mdtHideModal() {
    $('#modalEmailTemplate').modal('hide');
}

// --- Abrir modal para NOVO template ---
function mdtNovoTemplate() {
    _mdtLimparCampos();
    $('#modalEmailTemplateTitle').text('Novo Template');
    $('#mdt_updateEmailTemplate').val(0);
    $('#btnMdtSalvarComoNovo').hide();
    _mdtShowModal();
}

// --- Abrir modal para EDITAR template existente ---
function mdtEditarTemplate(codEmailTemplate) {
    if (!codEmailTemplate) {
        alert('Código do template não informado.');
        return;
    }
    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>',
        data: {
            'codEmailTemplate': codEmailTemplate,
            'c': 'GerenciarEmailTemplate',
            'm': 'editarEmailTemplate',
            '_submit_check': '1'
        },
        dataType: "json",
        success: function(data) {
            if (data.statusMessage) {
                $('#mdt_codEmailTemplate').val(data.codEmailTemplate);
                $('#mdt_updateEmailTemplate').val(1);
                $('#mdt_assunto').val(data.assunto);
                $('#mdt_status').val(data.status);
                $('#mdt_corpoTemp').val(data.corpo || '');
                $('#mdt_descricao').val(data.descricao || '');
                $('#mdt_tipoDestinatario').val(data.tipoDestinatario || '');
                $('#mdt_modeloChave').val(data.modeloChave || '');
                $('#modalEmailTemplateTitle').text('Editar Template');
                $('#btnMdtSalvarComoNovo').show();
                _mdtShowModal();
            } else {
                alert('Template não encontrado.');
            }
        },
        error: function() {
            alert('Erro ao carregar template.');
        }
    });
}

// --- Salvar template (novo ou atualização) ---
function mdtSalvar(comoNovo) {
    var editorModal = tinymce.get('mdt_corpo');
    if (editorModal) tinymce.triggerSave();
    var corpo = editorModal ? editorModal.getContent() : $('#mdt_corpo').val();

    var dados = {
        'assunto':           $('#mdt_assunto').val(),
        'corpo':             corpo,
        'status':            $('#mdt_status').val(),
        'descricao':         $('#mdt_descricao').val(),
        'tipoDestinatario':  $('#mdt_tipoDestinatario').val(),
        'modeloChave':       $('#mdt_modeloChave').val(),
        'c': 'GerenciarEmailTemplate',
        'm': 'salvarEmailTemplate',
        '_submit_check': '1'
    };

    if (comoNovo) {
        dados['updateEmailTemplate'] = 0;
        dados['codEmailTemplate'] = '';
    } else {
        dados['updateEmailTemplate'] = $('#mdt_updateEmailTemplate').val();
        dados['codEmailTemplate'] = $('#mdt_codEmailTemplate').val();
    }

    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>',
        data: dados,
        dataType: "json",
        success: function(data) {
            if (data.statusMessage) {
                $('#mdt_mensagem').html('<div class="alert alert-success">Template salvo com sucesso!</div>');
                var resultado = {
                    codEmailTemplate: data.codEmailTemplate,
                    assunto: dados.assunto,
                    corpo: dados.corpo,
                    status: dados.status
                };
                setTimeout(function() {
                    _mdtHideModal();
                    if (typeof _mdtOnSalvo === 'function') {
                        _mdtOnSalvo(resultado);
                    }
                }, 1500);
            } else {
                $('#mdt_mensagem').html('<div class="alert alert-danger">' + (data.message || 'Erro ao salvar.') + '</div>');
            }
        },
        error: function() {
            $('#mdt_mensagem').html('<div class="alert alert-danger">Erro de comunicação.</div>');
        }
    });
}

// --- Limpar campos do modal ---
function _mdtLimparCampos() {
    $('#mdt_codEmailTemplate').val('');
    $('#mdt_assunto').val('');
    $('#mdt_status').val('ATIVADA');
    $('#mdt_corpoTemp').val('');
    $('#mdt_descricao').val('');
    $('#mdt_tipoDestinatario').val('');
    $('#mdt_modeloChave').val('');
    $('#mdt_mensagem').html('');
}

// --- TinyMCE: init ao abrir, destroy ao fechar ---
// Evento 'shown.bs.modal' funciona tanto no BS3 quanto no BS5
$('#modalEmailTemplate').on('shown.bs.modal', function() {
    var corpoTemp = $('#mdt_corpoTemp').val();
    if (!tinymce.get('mdt_corpo')) {
        tinymce.init($.extend({}, _mdtTinyConfig, {
            selector: '#mdt_corpo',
            init_instance_callback: function(editor) {
                editor.setContent(corpoTemp || '');
            }
        }));
    } else {
        tinymce.get('mdt_corpo').setContent(corpoTemp || '');
    }
});
$('#modalEmailTemplate').on('hidden.bs.modal', function() {
    var editor = tinymce.get('mdt_corpo');
    if (editor) editor.remove();
    $('#mdt_mensagem').html('');
});
</script>

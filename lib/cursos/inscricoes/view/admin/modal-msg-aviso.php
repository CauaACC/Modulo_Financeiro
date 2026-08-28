<!-- Modal Gerenciar Avisos (mesmo padrão do modal-email-template) -->
<style>.tox-tinymce-aux, .mce-window, .mce-floatpanel { z-index: 100100 !important; } .mce-modal-block { z-index: 100050 !important; }</style>
<script>
// Fix: Bootstrap modal intercepta foco e impede digitar no TinyMCE (link, code, etc.)
$(document).on('focusin', function(e) {
    if ($(e.target).closest('.mce-window, .mce-floatpanel, .tox-tinymce-aux, .tox-dialog').length) {
        e.stopImmediatePropagation();
    }
});
</script>
<div class="modal fade" id="modalMsgAviso" role="dialog" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header modalCabecalho">
        <h4 class="modal-title" id="modalMsgAvisoTitle"><i class="fa fa-bullhorn"></i> Novo Aviso</h4>
        <button type="button" class="close modalBtnFechar" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="mav_codMsgAviso">
        <input type="hidden" id="mav_updateMsgAviso" value="0">
        <input type="hidden" id="mav_corpoTemp">
        <div class="row">
          <div class="col-sm-9">
            <div class="form-group">
              <label for="mav_assunto">Assunto:</label>
              <input type="text" id="mav_assunto" class="form-control">
            </div>
          </div>
          <div class="col-sm-3">
            <div class="form-group">
              <label for="mav_status">Status:</label>
              <select id="mav_status" class="form-control">
                <option value="ATIVADA">ATIVADA</option>
                <option value="DESATIVADA">DESATIVADA</option>
              </select>
            </div>
          </div>
        </div>
        <div class="form-group">
          <label for="mav_corpo">Corpo do Aviso:</label>
          <textarea id="mav_corpo" class="form-control" rows="8"></textarea>
        </div>
        <div class="row">
          <div class="col-sm-4">
            <div class="form-group">
              <label for="mav_dataPublicacaoIni">Data de publicação:</label>
              <input type="datetime-local" id="mav_dataPublicacaoIni" class="form-control">
            </div>
          </div>
          <div class="col-sm-4">
            <div class="form-group">
              <label for="mav_dataPublicacaoFim">Data final da publicação:</label>
              <input type="datetime-local" id="mav_dataPublicacaoFim" class="form-control">
            </div>
          </div>
          <div class="col-sm-2">
            <div class="form-group">
              <label for="mav_codPerfil">Perfil:</label>
              <select class="form-control" id="mav_codPerfil">
                <option value=""></option>
                <?php
                $fws_['codEvento'] = $objEv->getCodEvento();
                $fws_['semRestricao'] = true;
                CarregarOptionsAC::perfis($fws_, $debug);
                ?>
              </select>
            </div>
          </div>
          <div class="col-sm-2">
            <div class="form-group">
              <label for="mav_exibirPara">Pagamento:</label>
              <select class="form-control" id="mav_exibirPara">
                <option value="TODOS">Todos</option>
                <option value="PAGO">Pagos</option>
                <option value="NAO_PAGO">N&atilde;o pagos</option>
              </select>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-sm-4">
            <div class="form-group">
              <label for="mav_filtroStatus">Status inscri&ccedil;&atilde;o:</label>
              <select class="form-control" id="mav_filtroStatus">
                <option value="">Todos</option>
                <option value="ATIVADA">Ativada</option>
                <option value="DESATIVADA">Desativada</option>
              </select>
            </div>
          </div>
          <div class="col-sm-4">
            <div class="form-group">
              <label for="mav_filtroStatusProcesso">Status processo:</label>
              <select class="form-control" id="mav_filtroStatusProcesso">
                <option value="">Todos</option>
                <option value="INSCRITO">Inscrito</option>
                <option value="PARTICIPANDO">Participando</option>
                <option value="DESISTENTE">Desistente</option>
                <option value="CANCELADA">Cancelada</option>
              </select>
            </div>
          </div>
          <div class="col-sm-4">
            <div class="form-group">
              <label for="mav_fixado">Fixar aviso:</label>
              <select class="form-control" id="mav_fixado">
                <option value="0">N&atilde;o</option>
                <option value="1">Sim (sempre vis&iacute;vel mesmo ap&oacute;s lido)</option>
              </select>
            </div>
          </div>
        </div>
        <div id="mav_mensagem"></div>
      </div>
      <div class="modal-footer modalRodape">
        <button type="button" class="btn btn-default modalBtnCancelar" data-dismiss="modal">Fechar</button>
        <button type="button" class="btn modalBtnSalvar" id="btnMavSalvar" onclick="mavSalvar(false)"><i class="fa fa-save"></i> Salvar</button>
        <button type="button" class="btn btn-info" id="btnMavSalvarComoNovo" onclick="mavSalvar(true)" style="display:none;">Salvar como Novo</button>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
// --- Modal Msg Aviso: config TinyMCE ---
var _mavTinyConfig = {
    plugins: "code textcolor link table",
    menubar: false,
    statusbar: true,
    height: 350,
    resize: "both",
    force_br_newlines: true,
    force_p_newlines: false,
    forced_root_block: '',
    toolbar: "code | undo redo | bold italic underline | alignleft aligncenter alignright | bullist numlist | forecolor backcolor fontsizeselect | link | table",
    textcolor_map: ["FF0000","Red","008B45","Green","0000FF","Blue"],
    fontsize_formats: "10pt 12pt 14pt 18pt 24pt 36pt"
};

// Callback opcional — cada página define o seu
var _mavOnSalvo = null;

// --- Abrir modal para NOVO aviso ---
function mavNovoAviso() {
    _mavLimparCampos();
    $('#modalMsgAvisoTitle').text('Novo Aviso');
    $('#mav_updateMsgAviso').val(0);
    $('#btnMavSalvarComoNovo').hide();
    $('#modalMsgAviso').modal('show');
}

// --- Abrir modal para EDITAR aviso existente ---
function mavEditarAviso(codMsgAviso) {
    if (!codMsgAviso) {
        alert('Código do aviso não informado.');
        return;
    }
    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>',
        data: {
            'codMsgAviso': codMsgAviso,
            'c': 'GerenciarMsgAviso',
            'm': 'editarMsgAvisos',
            '_submit_check': '1'
        },
        dataType: "json",
        success: function(data) {
            if (data.statusMessage) {
                $('#mav_codMsgAviso').val(data.codMsgAviso);
                $('#mav_updateMsgAviso').val(1);
                $('#mav_assunto').val(data.assunto);
                $('#mav_status').val(data.status);
                $('#mav_corpoTemp').val(data.corpo || '');
                $('#mav_dataPublicacaoIni').val(data.dataPublicacaoIni || '');
                $('#mav_dataPublicacaoFim').val(data.dataPublicacaoFim || '');
                $('#mav_codPerfil').val(data.codPerfil || '');
                $('#mav_exibirPara').val(data.exibirPara || 'TODOS');
                $('#mav_filtroStatus').val(data.filtroStatus || '');
                $('#mav_filtroStatusProcesso').val(data.filtroStatusProcesso || '');
                $('#mav_fixado').val(data.fixado || '0');
                $('#modalMsgAvisoTitle').text('Editar Aviso');
                $('#btnMavSalvarComoNovo').show();
                $('#modalMsgAviso').modal('show');
            } else {
                alert('Aviso não encontrado.');
            }
        },
        error: function() {
            alert('Erro ao carregar aviso.');
        }
    });
}

// --- Salvar aviso (novo ou atualização) ---
function mavSalvar(comoNovo) {
    var editorModal = tinymce.get('mav_corpo');
    if (editorModal) tinymce.triggerSave();
    var corpo = editorModal ? editorModal.getContent() : $('#mav_corpo').val();

    var dados = {
        'assunto':           $('#mav_assunto').val(),
        'corpo':             corpo,
        'status':            $('#mav_status').val(),
        'dataPublicacaoIni': $('#mav_dataPublicacaoIni').val(),
        'dataPublicacaoFim': $('#mav_dataPublicacaoFim').val(),
        'codPerfil':         $('#mav_codPerfil').val(),
        'exibirPara':        $('#mav_exibirPara').val(),
        'filtroStatus':      $('#mav_filtroStatus').val(),
        'filtroStatusProcesso': $('#mav_filtroStatusProcesso').val(),
        'fixado':            $('#mav_fixado').val(),
        'c': 'GerenciarMsgAviso',
        'm': 'salvarMsgAvisos',
        '_submit_check': '1'
    };

    if (comoNovo) {
        dados['updateMsgAvisos'] = 0;
        dados['codMsgAviso'] = '';
    } else {
        dados['updateMsgAvisos'] = $('#mav_updateMsgAviso').val();
        dados['codMsgAviso'] = $('#mav_codMsgAviso').val();
    }

    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>',
        data: dados,
        dataType: "json",
        success: function(data) {
            if (data.statusMessage) {
                $('#mav_mensagem').html('<div class="alert alert-success">Aviso salvo com sucesso!</div>');
                setTimeout(function() {
                    $('#modalMsgAviso').modal('hide');
                    if (typeof _mavOnSalvo === 'function') {
                        _mavOnSalvo();
                    }
                }, 1500);
            } else {
                $('#mav_mensagem').html('<div class="alert alert-danger">' + (data.message || 'Erro ao salvar.') + '</div>');
            }
        },
        error: function() {
            $('#mav_mensagem').html('<div class="alert alert-danger">Erro de comunicação.</div>');
        }
    });
}

// --- Limpar campos do modal ---
function _mavLimparCampos() {
    $('#mav_codMsgAviso').val('');
    $('#mav_assunto').val('');
    $('#mav_status').val('ATIVADA');
    $('#mav_corpoTemp').val('');
    $('#mav_dataPublicacaoIni').val('');
    $('#mav_dataPublicacaoFim').val('');
    $('#mav_codPerfil').val('');
    $('#mav_exibirPara').val('TODOS');
    $('#mav_filtroStatus').val('');
    $('#mav_filtroStatusProcesso').val('');
    $('#mav_fixado').val('0');
    $('#mav_mensagem').html('');
}

// --- TinyMCE: init ao abrir, destroy ao fechar ---
$('#modalMsgAviso').on('shown.bs.modal', function() {
    var corpoTemp = $('#mav_corpoTemp').val();
    if (!tinymce.get('mav_corpo')) {
        tinymce.init($.extend({}, _mavTinyConfig, {
            selector: '#mav_corpo',
            init_instance_callback: function(editor) {
                editor.setContent(corpoTemp || '');
            }
        }));
    } else {
        tinymce.get('mav_corpo').setContent(corpoTemp || '');
    }
});
$('#modalMsgAviso').on('hidden.bs.modal', function() {
    var editor = tinymce.get('mav_corpo');
    if (editor) editor.remove();
    $('#mav_mensagem').html('');
});
</script>

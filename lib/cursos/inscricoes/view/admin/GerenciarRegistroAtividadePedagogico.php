<?php
require_once(dirname(__FILE__)."/../../view/admin/pedagogico-seguranca.php");

try {
    $objSRV = new GerenciarRegistroAtividadePedagogicoAC();
    $codInscritoResolvido = $objSRV->getCodInscritoResolvido();
    $objInscrito = new Inscrito();
    $objInscrito->setCodInscrito($codInscritoResolvido);
    $objInscrito->setCodEvento(COD_EVENTO);
    $objInscrito->getInscritoById();
} catch (Exception $e) {
    echo '<div class="alert alert-danger" style="margin-top:15px;">' . $e->getMessage() . '</div>';
    require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_administracao.php");
    require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_pedagogico.php");
    return;
}
?>
<script type="text/javascript">
$(document).ready(function() {
    carregarTabelaRegistroAtividades();

    $('#btnSalvar').on('click', function() {
        salvarRegistroAtividade();
    });
    $('#btnLimpar').on('click', function() {
        limparCampos();
    });
});

function carregarTabelaRegistroAtividades() {
    $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), {
        'c': 'GerenciarRegistroAtividadePedagogico',
        'm': 'carregarTabelaRegistroAtividades',
        '_submit_check': '1'
    }, function(html) {
        $("#itens tbody").html(html);
        bindEventosTabela();
    });
}

function bindEventosTabela() {
    $('#itens').off('click', '.editar').on('click', '.editar', function() {
        editarRegistroAtividade($(this).closest('tr').attr('id'));
    });
    $('#itens').off('click', '.excluir').on('click', '.excluir', function() {
        var cod = $(this).closest('tr').attr('id');
        $('#modalConfirmarExclusao').data('cod', cod);
        $('#modalConfirmarExclusao').modal('show');
    });
}

function salvarRegistroAtividade() {
    var valido = true;
    $('#formRegistroAtividade .form-group').removeClass('has-error');
    $('#formRegistroAtividade .msg-campo').remove();

    if (!$('#dia').val()) {
        $('#dia').closest('.form-group').addClass('has-error')
            .append('<span class="msg-campo help-block" style="color:#a94442; font-size:11px; margin-top:2px;">Campo obrigat&oacute;rio</span>');
        valido = false;
    }
    if (!$('#horasTrabalhadas').val()) {
        $('#horasTrabalhadas').closest('.form-group').addClass('has-error')
            .append('<span class="msg-campo help-block" style="color:#a94442; font-size:11px; margin-top:2px;">Campo obrigat&oacute;rio</span>');
        valido = false;
    }
    if (!$.trim($('#descricao').val())) {
        $('#descricao').closest('.form-group').addClass('has-error')
            .append('<span class="msg-campo help-block" style="color:#a94442; font-size:11px; margin-top:2px;">Campo obrigat&oacute;rio</span>');
        valido = false;
    }
    if (!valido) return;

    var formData = new FormData();
    formData.append('c', 'GerenciarRegistroAtividadePedagogico');
    formData.append('m', 'salvarRegistroAtividade');
    formData.append('codRegAtividade', $('#codRegAtividade').val());
    formData.append('novoCodRegAtividade', $('#novoCodRegAtividade').val());
    formData.append('dia', $('#dia').val());
    formData.append('diaSemana', $('#diaSemana').val());
    formData.append('horasTrabalhadas', $('#horasTrabalhadas').val());
    formData.append('descricao', $.trim($('#descricao').val()));
    formData.append('justificativa', $.trim($('#justificativa').val()));
    formData.append('_submit_check', '1');

    // Arquivos (evento 115)
    var fileInput = document.getElementById('arquivosRegistro');
    if (fileInput && fileInput.files.length > 0) {
        for (var i = 0; i < fileInput.files.length; i++) {
            formData.append('arquivosRegistro[]', fileInput.files[i]);
        }
    }

    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: formData,
        processData: false,
        contentType: false,
        dataType: "json",
        cache: false,
        beforeSend: function() { startLoading(); },
        success: function(data) {
            processJson(data);
            if (data.statusMessage) {
                limparCampos();
                carregarTabelaRegistroAtividades();
            }
        },
        complete: function() { stopLoading(); }
    });
}

function editarRegistroAtividade(codRegAtividade) {
    if (!codRegAtividade) return;
    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {
            'codRegAtividade': codRegAtividade,
            'c': 'GerenciarRegistroAtividadePedagogico',
            'm': 'editarRegistroAtividade',
            '_submit_check': '1'
        },
        dataType: "json",
        cache: false,
        beforeSend: function() { startLoading(); },
        success: function(data) {
            $('#codRegAtividade').val(data.codRegAtividade);
            $('#dia').val(data.dia);
            $('#diaSemana').val(data.diaSemana);
            $('#descricao').val(data.descricao);
            $('#justificativa').val(data.justificativa);
            $('#horasTrabalhadas').val(data.horasTrabalhadas);
            $('#novoCodRegAtividade').val(1);

            // Arquivos (evento 115)
            if ($('#arquivosAtuais').length) {
                carregarArquivosRegistro(data.codRegAtividade);
                $('#arquivosRegistro').val('');
            }

            $('html,body').animate({ scrollTop: $("#registroAtividades").offset().top }, 'slow');
        },
        complete: function() { stopLoading(); }
    });
}

function confirmarExclusao() {
    var cod = $('#modalConfirmarExclusao').data('cod');
    $('#modalConfirmarExclusao').modal('hide');
    if (!cod) return;
    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {
            'codRegAtividade': cod,
            'c': 'GerenciarRegistroAtividadePedagogico',
            'm': 'excluirRegistroAtividade',
            '_submit_check': '1'
        },
        dataType: "json",
        cache: false,
        beforeSend: function() { startLoading(); },
        success: function(data) {
            processJson(data);
            if (data.statusMessage) {
                carregarTabelaRegistroAtividades();
            }
        },
        complete: function() { stopLoading(); }
    });
}

function carregarArquivosRegistro(codRegAtividade) {
    $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), {
        'c': 'GerenciarRegistroAtividadePedagogico',
        'm': 'carregarArquivosRegistro',
        'codRegAtividade': codRegAtividade,
        '_submit_check': '1'
    }, function(html) {
        if (html) {
            $('#arquivosAtuais').html(html).show();
        } else {
            $('#arquivosAtuais').hide().html('');
        }
    });
}

function excluirArquivoIndividual(codArquivo, btn) {
    if (!confirm('Remover este arquivo?')) return;
    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {
            'c': 'GerenciarRegistroAtividadePedagogico',
            'm': 'excluirArquivoRegistro',
            'codArquivo': codArquivo,
            '_submit_check': '1'
        },
        dataType: "json",
        success: function(data) {
            if (data.statusMessage) {
                $(btn).closest('.arquivo-item').fadeOut(200, function() { $(this).remove(); });
            } else {
                alert(data.message);
            }
        }
    });
}

function limparCampos() {
    $('#codRegAtividade').val('');
    $('#dia').val('');
    $('#diaSemana').val('');
    $('#descricao').val('');
    $('#justificativa').val('');
    $('#horasTrabalhadas').val('');
    $('#novoCodRegAtividade').val(0);
    $('#formRegistroAtividade .form-group').removeClass('has-error');
    $('#formRegistroAtividade .msg-campo').remove();
    if ($('#arquivosRegistro').length) {
        $('#arquivosRegistro').val('');
        $('#arquivosAtuais').hide().html('');
    }
}
</script>
<?php
require_once(dirname(__FILE__)."/../../view/admin/titulo_area_admin.php");
require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_administracao.php");
require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_pedagogico.php");
?>

<div id="sucesso" class="hidden"></div>
<div id="erro" class="hidden"></div>

<div id="registroAtividades" style="background:#fff; border:1px solid #e8e8e8; border-radius:12px; overflow:hidden; margin-top:10px;">
    <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 20px; border-bottom:1px solid #eee;">
        <h4 style="margin:0; font-size:15px; font-weight:600; color:#333;">Registro de Atividades</h4>
    </div>
    <div style="padding:15px 20px;">
        <?php
        try {
            if ($objInscrito->getStatusProcesso() != 'PARTICIPANDO' && $objInscrito->getStatusProcesso() != 'INSCRITO') {
                throw new Exception('Registro de Atividade bloqueado. Situa&ccedil;&atilde;o no processo: ' . $objInscrito->getStatusProcesso());
            }
            $hoje = new DateTime();
            $objLiberacao = new LiberacaoRegistro();
            $objLiberacao->setCodInscrito($objInscrito->getCodInscrito());
            $objLiberacao->setCodEvento(COD_EVENTO);
            $liberacao = $objLiberacao->buscarLiberacaoAtiva();
            if ($liberacao) {
                $dataMin = $liberacao['data_inicio'];
            } else {
                $dataMin = $hoje->modify('first day of this month')->format('Y-m-d');
                $dataMin = $hoje->modify('-25 days')->format('Y-m-d');
            }
            $dataMax = (new DateTime())->modify('last day of this month')->format('Y-m-d');
        ?>
            <form id="formRegistroAtividade" name="formRegistroAtividade">
                <div class="row">
                    <div class="form-group col-sm-4">
                        <label for="dia">Data da atividade:</label>
                        <input type="date" id="dia" name="dia" class="form-control"
                               min="<?php echo $dataMin ?>" max="<?php echo $dataMax ?>">
                        <script>
                            document.getElementById("dia").addEventListener("change", function() {
                                var d = new Date(this.value);
                                var dias = ["Domingo","Segunda-feira","Ter\u00e7a-feira","Quarta-feira","Quinta-feira","Sexta-feira","S\u00e1bado"];
                                document.getElementById("diaSemana").value = dias[d.getUTCDay()];
                            });
                        </script>
                    </div>
                    <div class="form-group col-sm-4">
                        <label for="diaSemana">Dia da semana:</label>
                        <select class="form-control" name="diaSemana" id="diaSemana">
                            <option value="">Dia da semana</option>
                            <?php CarregarOptionsAC::diaSemana($fws_, $debug); ?>
                        </select>
                    </div>
                    <div class="form-group col-sm-4">
                        <label for="horasTrabalhadas">Horas trabalhadas:</label>
                        <select id="horasTrabalhadas" name="horasTrabalhadas" class="form-control">
                            <option value="">Selecione</option>
                            <?php
                                $limiteHoras = (COD_EVENTO == 115) ? 480 : 360;
                                for ($m = 15; $m <= $limiteHoras; $m += 15) {
                                $h = floor($m / 60);
                                $min = $m % 60;
                                $label = $h > 0 ? "{$h}h" : "";
                                $label .= $min > 0 ? " {$min}min" : "";
                                $sel = ($m == $limiteHoras) ? ' selected' : '';
                                echo "<option value=\"{$m}\"{$sel}>{$label}</option>";
                            } ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="descricao">Descri&ccedil;&atilde;o das atividades:</label>
                    <textarea id="descricao" name="descricao" class="form-control" style="height:120px;" placeholder="Descreva as atividades realizadas"></textarea>
                </div>
                <div class="form-group">
                    <label for="justificativa">Justificativas:</label>
                    <textarea id="justificativa" name="justificativa" class="form-control" placeholder="Justificativas (opcional)"></textarea>
                </div>

                <?php if (COD_EVENTO == 115) { ?>
                <div class="form-group">
                    <label for="arquivosRegistro">Anexar arquivos:</label>
                    <input type="file" id="arquivosRegistro" name="arquivosRegistro[]" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" multiple>
                    <span class="help-block" style="font-size:11px; color:#999;">At&eacute; 5 arquivos. Formatos: PDF, JPG, PNG, DOC, DOCX. M&aacute;ximo 10MB cada.</span>
                    <div id="arquivosAtuais" style="display:none; margin-top:8px;"></div>
                </div>
                <?php } ?>

                <input type="hidden" name="novoCodRegAtividade" id="novoCodRegAtividade" value="0">
                <input type="hidden" name="codRegAtividade" id="codRegAtividade" value="">

                <div style="margin-top:10px;">
                    <button class="btn btn-sm modalBtnSalvar" type="button" id="btnSalvar">Salvar Registro</button>
                    <button class="btn btn-sm modalBtnCancelar" type="button" id="btnLimpar">Limpar</button>
                </div>
            </form>
        <?php } catch (Exception $exc) { ?>
            <div class="alert alert-warning"><?php echo $exc->getMessage(); ?></div>
        <?php } ?>

        <div style="margin-top:20px;">
            <h5 style="font-size:14px; font-weight:600; color:#555;">Atividades Registradas</h5>
            <table class="tbl-fin" id="itens">
                <tbody>
                    <?php $objSRV->carregarTabelaRegistroAtividades($fws_, $debug); ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Confirmar Exclusao -->
<div class="modal fade" id="modalConfirmarExclusao" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog modal-sm" role="document">
    <div class="modal-content">
      <div class="modal-header modalCabecalhoExclusao">
        <h4 class="modal-title"><i class="fa fa-exclamation-triangle"></i>Confirmar Exclus&atilde;o</h4>
        <button type="button" class="close modalBtnFechar" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <div class="modal-body">
        <p>Deseja realmente excluir este registro de atividade?</p>
        <p style="color:#a94442; font-size:12px;"><i class="fa fa-exclamation-triangle"></i> Esta a&ccedil;&atilde;o n&atilde;o pode ser desfeita.</p>
      </div>
      <div class="modal-footer modalRodape">
        <button type="button" class="btn btn-default modalBtnCancelar" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-danger modalBtnExcluir" onclick="confirmarExclusao()">Excluir</button>
      </div>
    </div>
  </div>
</div>

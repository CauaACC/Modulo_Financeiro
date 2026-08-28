<?php
require_once(dirname(__FILE__) . "/../../view/admin/consulta-dados-congresso-seguranca.php");
$objSRV = new GerenciarCalendarioBaseAC();
?>
<script type="text/javascript">
$(document).ready(function() {

    carregarAnosDisponiveis(function(){
        carregarTabelaCalendarioBase();
    });

    $('#filtroAno').on('change', function(){
        carregarTabelaCalendarioBase();
    });

    // Delegated events — clique em qualquer dia redireciona pra GerenciarFeriado com a data preenchida
    $('#itens').on('click', '.cb-dia', function(e) {
        e.preventDefault();
        var data = $(this).attr('data-data');
        if (!data) return;
        window.location.href = 'index.php?content=view/admin/GerenciarFeriado.php&dia_calendario=' + encodeURIComponent(data);
    });

    $('#btnSalvarCalendario').click(function() {
        salvarCalendarioBase();
    });
});

function carregarAnosDisponiveis(callback) {
    $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), {
        'c': 'GerenciarCalendarioBase',
        'm': 'carregarAnosDisponiveis',
        '_submit_check': '1'
    }, function(data) {
        try {
            var resp = (typeof data === 'string') ? JSON.parse(data) : data;
            var anoAtual = (new Date()).getFullYear();
            var sel = $('#filtroAno');
            sel.find('option:not([value=""])').remove();
            (resp.anos || []).forEach(function(ano){
                sel.append('<option value="' + ano + '">' + ano + '</option>');
            });
            if (resp.anos && resp.anos.indexOf(anoAtual) >= 0) {
                sel.val(anoAtual);
            } else if (resp.anos && resp.anos.length > 0) {
                sel.val(resp.anos[0]);
            }
        } catch (e) {}
        if (callback) callback();
    });
}

function abrirModalCalendario() {
    limparCampos();
    $('#modalCalendarioTitulo').text('Novo Registro');
    $('#modalCalendario').modal('show');
}

function carregarTabelaCalendarioBase() {
    var ano = $('#filtroAno').val() || '';
    $('#contadorDias').text('');
    $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), {
        'c': 'GerenciarCalendarioBase',
        'm': 'carregarTabelaCalendarioBase',
        'ano': ano,
        '_submit_check': '1'
    }, function(html) {
        $('#itens').html(html);
        var totalDias = $('#itens .cb-dia').length;
        if (totalDias > 0) {
            $('#contadorDias').text('— ' + totalDias + ' dia(s) listado(s)');
        }
    });
}

function salvarCalendarioBase() {
    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {
            'c': 'GerenciarCalendarioBase',
            'm': 'salvarCalendarioBase',
            'id': $('#id').val(),
            'updateCalendarioBase': $('#updateCalendarioBase').val(),
            'dia_calendario': $.trim($('#dia_calendario').val()),
            'mes': $.trim($('#mes').val()),
            'tipo_dia': $.trim($('#tipo_dia').val()),
            'Feriado': $.trim($('#Feriado').val()),
            'Ponto': $.trim($('#Ponto').val()),
            '_submit_check': '1'
        },
        dataType: "json",
        async: false,
        cache: false,
        beforeSend: function() { startLoading(); },
        success: function(data) {
            processJson(data);
            if (data.statusMessage) {
                limparCampos();
                carregarTabelaCalendarioBase();
                $('#modalCalendario').modal('hide');
            }
        },
        complete: function() { stopLoading(); }
    });
}

function editarCalendarioBase(cod) {
    if (!cod) return;
    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {'id': cod, 'c': 'GerenciarCalendarioBase', 'm': 'editarCalendarioBase', '_submit_check': '1'},
        dataType: "json",
        beforeSend: function() { startLoading(); },
        success: function(data) {
            if (data.statusMessage) {
                $('#updateCalendarioBase').val(1);
                $('#id').val(data.id);
                $('#dia_calendario').val(data.dia_calendario);
                $('#mes').val(data.mes);
                $('#tipo_dia').val(data.tipo_dia);
                $('#Feriado').val(data.Feriado);
                $('#Ponto').val(data.Ponto);
                $('#modalCalendarioTitulo').text('Editar Registro');
                $('#modalCalendario').modal('show');
            } else {
                processJson(data);
            }
        },
        complete: function() { stopLoading(); }
    });
}

function excluirCalendarioBase(cod) {
    $('#modalConfirmarExclusao').data('cod', cod);
    $('#modalConfirmarExclusao').modal('show');
}

function confirmarExclusao() {
    var cod = $('#modalConfirmarExclusao').data('cod');
    $('#modalConfirmarExclusao').modal('hide');
    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {'id': cod, 'c': 'GerenciarCalendarioBase', 'm': 'excluirCalendarioBase', '_submit_check': '1'},
        dataType: "json",
        async: false,
        cache: false,
        beforeSend: function() { startLoading(); },
        success: function(data) {
            processJson(data);
            if (data.statusMessage) {
                carregarTabelaCalendarioBase();
            }
        },
        complete: function() { stopLoading(); }
    });
}

function limparCampos() {
    $('#id').val('');
    $('#dia_calendario').val('');
    $('#mes').val('');
    $('#tipo_dia').val('');
    $('#Feriado').val('');
    $('#Ponto').val('');
    $('#updateCalendarioBase').val(0);
    $('#modalCalendarioMsg').html('');
    $('#modalCalendario .form-group').removeClass('has-error');
    $('#modalCalendario .msg-campo').remove();
}

function gerarCalendario() {
    var ano = $('#anoGerar').val();
    if (!ano) return;
    $('#btnGerarConfirmar').prop('disabled', true).text('Gerando...');
    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {
            'c': 'GerenciarCalendarioBase',
            'm': 'gerarCalendario',
            'ano': ano,
            '_submit_check': '1'
        },
        dataType: "json",
        cache: false,
        success: function(data) {
            processJson(data);
            if (data.statusMessage) {
                $('#modalGerarCalendario').modal('hide');
                // Recarrega anos disponiveis, seleciona o ano que acabou de ser gerado e atualiza a grade
                carregarAnosDisponiveis(function(){
                    $('#filtroAno').val(ano);
                    carregarTabelaCalendarioBase();
                });
            }
        },
        complete: function() {
            $('#btnGerarConfirmar').prop('disabled', false).text('Gerar');
        }
    });
}
</script>
<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/titulo_area_admin.php");
require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_administracao.php");
require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_outros.php");
?>

<div id="sucesso" class="hidden"></div>
<div id="erro" class="hidden"></div>

<div style="background:#fff; border:1px solid #e8e8e8; border-radius:12px; overflow:hidden; margin-top:10px; margin-bottom:300px;">
    <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 20px; border-bottom:1px solid #eee;">
        <h4 style="margin:0; font-size:15px; font-weight:600; color:#333;">Calend&aacute;rio Base</h4>
        <div>
            <button class="btn btn-sm abriModal" type="button" onclick="$('#modalGerarCalendario').modal('show');" style="margin-right:5px;">
                <i class="fa fa-magic"></i> Gerar Calend&aacute;rio
            </button>
            <button class="btn btn-sm abriModal" type="button" onclick="abrirModalCalendario()">
                <i class="fa fa-plus"></i> Novo Registro
            </button>
        </div>
    </div>
    <div style="padding:12px 18px;">
        <div id="carregando" class="shown hidden">
            <div class="imagem">
                <div class="frase">Carregando...</div>
                <img src="<?php echo WB_PATH ?>/resources/image/loading-9.gif" style="border: 0;">
            </div>
        </div>
        <div style="display:flex; align-items:center; gap:10px;">
            <label for="filtroAno" style="margin:0; font-size:14px; font-weight:600; color:#555;">Ano:</label>
            <select id="filtroAno" class="form-control" style="width:auto; display:inline-block;">
                <option value="">— Todos —</option>
            </select>
            <span id="contadorDias" style="font-size:13px; color:#777;"></span>
        </div>
    </div>
    <div id="itens"></div>
</div>

<!-- Modal Calendario Base -->
<div class="modal fade" id="modalCalendario" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header modalCabecalho">
        <h4 class="modal-title" id="modalCalendarioTitulo"><i class="fa fa-calendar"></i>Novo Registro</h4>
        <button type="button" class="close modalBtnFechar" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <form id="formCalendarioBase" name="formCalendarioBase" action="<?php echo WB_URL_COMMAND ?>&dummy=" method="post" novalidate>
      <div class="modal-body">
        <div id="modalCalendarioMsg"></div>
        <input type="hidden" value="1" name="_submit_check">
        <input type="hidden" value="GerenciarCalendarioBase" name="c">
        <input type="hidden" value="salvarCalendarioBase" name="m">
        <input type="hidden" name="updateCalendarioBase" id="updateCalendarioBase" value="0">
        <input type="hidden" name="id" id="id" value="">
        <div class="form-group">
            <label for="dia_calendario">Dia:</label>
            <input type="date" id="dia_calendario" name="dia_calendario" class="form-control">
        </div>
        <div class="form-group">
            <label for="mes">M&ecirc;s:</label>
            <input type="text" id="mes" name="mes" class="form-control" placeholder="M&ecirc;s">
        </div>
        <div class="form-group">
            <label for="tipo_dia">Tipo de Dia:</label>
            <input type="text" id="tipo_dia" name="tipo_dia" class="form-control" placeholder="Tipo de Dia">
        </div>
        <div class="form-group">
            <label for="Feriado">Feriado:</label>
            <input type="text" id="Feriado" name="Feriado" class="form-control" placeholder="Feriado">
        </div>
        <div class="form-group">
            <label for="Ponto">Ponto:</label>
            <input type="text" id="Ponto" name="Ponto" class="form-control" placeholder="Ponto">
        </div>
      </div>
      <div class="modal-footer modalRodape">
        <button type="button" class="btn btn-default modalBtnCancelar" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary modalBtnSalvar" id="btnSalvarCalendario">Salvar</button>
      </div>
      </form>
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
        <p>Deseja realmente excluir este registro?</p>
        <p style="color:#a94442; font-size:12px;"><i class="fa fa-exclamation-triangle"></i> Esta a&ccedil;&atilde;o n&atilde;o pode ser desfeita.</p>
      </div>
      <div class="modal-footer modalRodape">
        <button type="button" class="btn btn-default modalBtnCancelar" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-danger modalBtnExcluir" onclick="confirmarExclusao()">Excluir</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Gerar Calendario -->
<div class="modal fade" id="modalGerarCalendario" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog modal-sm" role="document">
    <div class="modal-content">
      <div class="modal-header modalCabecalho">
        <h4 class="modal-title"><i class="fa fa-magic"></i>Gerar Calend&aacute;rio</h4>
        <button type="button" class="close modalBtnFechar" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <div class="modal-body">
        <div class="form-group">
            <label for="anoGerar">Ano:</label>
            <select id="anoGerar" class="form-control">
                <?php
                $anoAtual = date('Y');
                for ($a = $anoAtual - 1; $a <= $anoAtual + 2; $a++) {
                    $sel = ($a == $anoAtual) ? ' selected' : '';
                    echo "<option value=\"$a\"$sel>$a</option>";
                }
                ?>
            </select>
        </div>
        <p style="color:#856404; font-size:12px; background:#fff3cd; padding:10px; border-radius:4px;">
            <i class="fa fa-info-circle"></i> Todos os dias do ano ser&atilde;o gerados com S&aacute;bados, Domingos e Dias &Uacute;teis.
            <br>Se j&aacute; existirem registros para o ano, ser&atilde;o substitu&iacute;dos.
        </p>
      </div>
      <div class="modal-footer modalRodape">
        <button type="button" class="btn btn-default modalBtnCancelar" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary modalBtnSalvar" id="btnGerarConfirmar" onclick="gerarCalendario()">Gerar</button>
      </div>
    </div>
  </div>
</div>

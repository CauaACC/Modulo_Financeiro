<?php
require_once(dirname(__FILE__) . "/../../view/admin/seguranca-adm-geral.php");
$objSRV = new GerenciarModulosAC();
?>
<script type="text/javascript">
$(document).ready(function() {

    // Toggle da seção de copiar
    $('.section-toggle').click(function() {
        var target = $(this).data('target');
        $(target).slideToggle(300);
        $(this).find('i.fa').toggleClass('fa-angle-right fa-angle-down');
    });

    $('#btnAdicionarModulo').click(function() {
        adicionarModulo();
    });

    $('#btnCopiarModulos').click(function() {
        copiarModulosEvento();
    });
});

function carregarTabelaModulos() {
    var codEvento = $('#selectEvento').val();
    if (!codEvento) {
        $("tbody.modulos").html('<tr><td colspan="4">Selecione um evento.</td></tr>');
        return;
    }

    $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), {
        'c': 'GerenciarModulos',
        'm': 'carregarTabelaModulos',
        'cod_evento': codEvento,
        '_submit_check': '1'
    }, function(html) {
        $("tbody.modulos").html(html);
    });
}

function adicionarModulo() {
    var codEvento  = $('#selectEvento').val();
    var codGrupo   = $('#selectGrupo').val();
    var codSistema = $('#selectSistema').val();
    var codDireito = $('#selectDireito').val();

    var errosModulo = [];
    if (!codEvento)  errosModulo.push('Evento');
    if (!codGrupo)   errosModulo.push('Grupo');
    if (!codSistema) errosModulo.push('Sistema/M\u00f3dulo');
    if (!codDireito) errosModulo.push('Direito');
    if (errosModulo.length > 0) {
        $('#mensagem').html('<div class="alert alert-warning alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>Preencha: ' + errosModulo.join(', ') + '.</div>');
        return;
    }

    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {
            'cod_evento': codEvento,
            'cod_grupo': codGrupo,
            'cod_sistema': codSistema,
            'cod_direito': codDireito,
            'c': 'GerenciarModulos',
            'm': 'adicionarModulo',
            '_submit_check': '1'
        },
        dataType: "json",
        async: false,
        cache: false,
        beforeSend: function() { startLoading(); },
        success: function(data) {
            processJson(data);
            if (data.statusMessage) {
                carregarTabelaModulos();
            }
        },
        complete: function() { stopLoading(); }
    });
}

var _excluirModulo = { codGrupo: null, codSistema: null, codDireito: null };

function excluirModulo(codGrupo, codSistema, codDireito) {
    _excluirModulo = { codGrupo: codGrupo, codSistema: codSistema, codDireito: codDireito };
    $('#modalExcluirModulo').modal('show');
}

function confirmarExcluirModulo() {
    if (!_excluirModulo.codGrupo) return;
    $('#modalExcluirModulo').modal('hide');

    var codEvento = $('#selectEvento').val();

    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {
            'cod_evento': codEvento,
            'cod_grupo': _excluirModulo.codGrupo,
            'cod_sistema': _excluirModulo.codSistema,
            'cod_direito': _excluirModulo.codDireito,
            'c': 'GerenciarModulos',
            'm': 'excluirModulo',
            '_submit_check': '1'
        },
        dataType: "json",
        async: false,
        cache: false,
        beforeSend: function() { startLoading(); },
        success: function(data) {
            processJson(data);
            if (data.statusMessage) {
                carregarTabelaModulos();
            }
        },
        complete: function() { stopLoading(); _excluirModulo = {}; }
    });
}

function copiarModulosEvento() {
    var codEventoDestino = $('#selectEvento').val();
    var codEventoOrigem  = $('#selectEventoOrigem').val();

    if (!codEventoOrigem) {
        $('#mensagem').html('<div class="alert alert-warning alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>Selecione o evento de origem.</div>');
        return;
    }
    if (!codEventoDestino) {
        $('#mensagem').html('<div class="alert alert-warning alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>Selecione o evento de destino (evento principal).</div>');
        return;
    }
    if (codEventoOrigem == codEventoDestino) {
        $('#mensagem').html('<div class="alert alert-warning alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>Evento de origem e destino n&atilde;o podem ser iguais.</div>');
        return;
    }

    $('#modalCopiarModulos').modal('show');
    return;
}

function confirmarCopiarModulos() {
    $('#modalCopiarModulos').modal('hide');
    var codEventoDestino = $('#selectEvento').val();
    var codEventoOrigem  = $('#selectEventoOrigem').val();

    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {
            'cod_evento_origem': codEventoOrigem,
            'cod_evento_destino': codEventoDestino,
            'c': 'GerenciarModulos',
            'm': 'copiarModulosEvento',
            '_submit_check': '1'
        },
        dataType: "json",
        async: false,
        cache: false,
        beforeSend: function() { startLoading(); },
        success: function(data) {
            processJson(data);
            if (data.statusMessage) {
                carregarTabelaModulos();
            }
        },
        complete: function() { stopLoading(); }
    });
}
</script>

<script type="text/javascript" src="resources/js/default.js"></script>
<?php
require_once(dirname(__FILE__) . "/../../include/config.php");
require_once(dirname(__FILE__) . "/../../view/admin/titulo_area_admin.php");
require_once(dirname(__FILE__) . "/../../view/admin/area_admin_menu_administracao.php");
require_once(dirname(__FILE__) . "/../../view/admin/area_admin_menu_usuarios.php");
?>

<div class="panel-group">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4>Gerenciar M&oacute;dulos do Evento</h4>
        </div>
        <div class="panel-body">

            <!-- SELECT: Evento -->
            <div class="row" style="margin-bottom:10px;">
                <div class="col-sm-6">
                    <label for="selectEvento">Evento:</label>
                    <select id="selectEvento" name="cod_evento" class="form-control" onchange="carregarTabelaModulos()">
                        <?php $objSRV->carregarSelectEventos($fws_, $debug); ?>
                    </select>
                </div>
            </div>

            <hr>

            <!-- FORM: Adicionar Modulo -->
            <b>Adicionar M&oacute;dulo:</b>
            <div class="row" style="margin-bottom:10px;">
                <div class="col-sm-3">
                    <label for="selectGrupo">Grupo:</label>
                    <select id="selectGrupo" name="cod_grupo" class="form-control">
                        <?php $objSRV->carregarSelectGrupos($fws_, $debug); ?>
                    </select>
                </div>
                <div class="col-sm-4">
                    <label for="selectSistema">Sistema/M&oacute;dulo:</label>
                    <select id="selectSistema" name="cod_sistema" class="form-control">
                        <?php $objSRV->carregarSelectSistemas($fws_, $debug); ?>
                    </select>
                </div>
                <div class="col-sm-3">
                    <label for="selectDireito">Direito:</label>
                    <select id="selectDireito" name="cod_direito" class="form-control">
                        <option value="">Selecione um direito</option>
                        <option value="1">Leitura</option>
                        <option value="2">Adicionar</option>
                        <option value="3">Alterar</option>
                        <option value="4">Excluir</option>
                        <option value="5">Administrar</option>
                    </select>
                </div>
                <div class="col-sm-2">
                    <label>&nbsp;</label><br>
                    <button class="btn btn-primary btn-sm" type="button" id="btnAdicionarModulo">
                        <i class="fa fa-plus"></i> Adicionar
                    </button>
                </div>
            </div>

            <div id="carregando" class="shown hidden">
                <div class="imagem">
                    <div class="frase">Carregando...</div>
                    <img src="<?php echo WB_PATH ?>/resources/image/loading-9.gif" style="border: 0;">
                </div>
            </div>
            <div id="mensagem"></div>
            <div id="sucesso" class="hidden"></div>
            <div id="erro" class="hidden"></div>

            <hr>

            <!-- TABELA: Modulos do Evento -->
            <b>M&oacute;dulos configurados:</b>
            <table class="table table-striped table-bordered" style="margin-top:10px;">
                <thead>
                    <tr>
                        <th width="80px">A&ccedil;&otilde;es</th>
                        <th>Grupo</th>
                        <th>M&oacute;dulo/Sistema</th>
                        <th>Direito</th>
                    </tr>
                </thead>
                <tbody class="modulos">
                    <?php $objSRV->carregarTabelaModulos($fws_, $debug); ?>
                </tbody>
            </table>

            <hr>

            <!-- COPIAR DE OUTRO EVENTO -->
            <div class="section-toggle" data-target="#secCopiar" style="cursor:pointer;padding:8px 0;font-weight:bold;color:#337ab7;">
                <i class="fa fa-angle-right"></i> Copiar m&oacute;dulos de outro evento
            </div>
            <div id="secCopiar" style="display:none;">
                <div class="row" style="margin-top:10px;">
                    <div class="col-sm-5">
                        <label for="selectEventoOrigem">Evento de Origem:</label>
                        <select id="selectEventoOrigem" name="cod_evento_origem" class="form-control">
                            <?php $objSRV->carregarSelectEventos($fws_, $debug); ?>
                        </select>
                    </div>
                    <div class="col-sm-4">
                        <label>&nbsp;</label><br>
                        <button class="btn btn-warning btn-sm" type="button" id="btnCopiarModulos">
                            <i class="fa fa-copy"></i> Copiar para evento selecionado
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Modal: Confirmar Exclusao Modulo -->
<div class="modal fade" id="modalExcluirModulo" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#d9534f; color:#fff;">
                <button type="button" class="close" data-dismiss="modal" style="color:#fff;"><span>&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-exclamation-triangle"></i> Confirmar Exclus&atilde;o</h4>
            </div>
            <div class="modal-body">
                <p>Deseja realmente excluir este m&oacute;dulo?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" onclick="confirmarExcluirModulo()"><i class="fa fa-trash"></i> Excluir</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Confirmar Copia de Modulos -->
<div class="modal fade" id="modalCopiarModulos" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#f0ad4e; color:#fff;">
                <button type="button" class="close" data-dismiss="modal" style="color:#fff;"><span>&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-copy"></i> Confirmar C&oacute;pia</h4>
            </div>
            <div class="modal-body">
                <p>Copiar todos os m&oacute;dulos do evento de origem para o evento selecionado?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning" onclick="confirmarCopiarModulos()"><i class="fa fa-copy"></i> Copiar</button>
            </div>
        </div>
    </div>
</div>

<style>
#sucesso,
#erro {
    min-height: 40px;
    visibility: hidden;
}
#sucesso.shown,
#erro.shown {
    visibility: visible;
}
.section-toggle:hover {
    text-decoration: underline;
}
</style>

<?php
require_once(dirname(__FILE__)."/../../view/admin/consulta-dados-congresso-seguranca.php");
$objSRV = new GerenciarInscritoPerfilAC();
if (empty($fws_['cod_inscrito'])) {
    $fws_['cod_inscrito'] = $fwsGet['cod_inscrito'];
}
if (empty($fws_['nome_inscrito'])) {
    $fws_['nome_inscrito'] = $fwsGet['nome_inscrito'];
}
if (empty($fws_['cpf'])) {
    $fws_['cpf'] = $fwsGet['cpf'];
}
?>
<style>
    .zebra-stripe:nth-child(odd) {
        background-color: #f2f2f2;
    } 
</style>
<script type="text/javascript">
    $(document).ready(function() {
        $('#btnSalvar').click(function() {
            salvarInscritoPerfil();
        });
        $('#btnSalvarComoNovo').click(function() {
            $('#novoCodInscritoPerfil').val(0);
            $('#salvarInscritoPerfil').val('Adicionar');
            salvarInscritoPerfil();
        });
        $('#btnLimpar').click(function() {
            limparCampos();
        });
            $('.editar').click(function() {
                var id = $(this).parents("tr").attr("id");
                editarInscritoPerfil(id);
            });
            $('.excluir').click(function() {
                var id = $(this).parents("tr").attr("id");
                excluirInscritoPerfil(id);
            });

            $("table.tablesorter").tablesorter({widgets: ['zebra']})
                      
//        carregarTabelaInscritoPerfils();
        $('table').tablesorter();
    });
    //funçães globais
    function carregarTabelaInscritoPerfil() {
//        alert($('#ordenar').val())
        $.post('<?php echo  WB_URL_COMMAND ?>&t=' + Math.random()
                , {'c': 'GerenciarInscritoPerfil', 'm': 'carregarTabelaInscritoPerfil', 'ordenar': $('#ordenar').val(), '_submit_check': '1'}
        , function(html) {
            $("table tbody.inputs tr").remove();
            $("table").trigger("update");
            // append the "ajax'd" data to the table body 
            $("table tbody.inputs").append(html);
            // let the plugin know that we made a update 
            $("table").trigger("update");
            //            $("table").tablesorter({
            //                sortList: [[1,2]]                    
            //            });            
            //eventos para manipulação de registros
            $('.editar').click(function() {
                var id = $(this).parents("tr").attr("id");
                editarInscritoPerfil(id);
            });
            $('.excluir').click(function() {
                var id = $(this).parents("tr").attr("id");
                excluirInscritoPerfil(id);
            });
        }
        );
    }
    function salvarInscritoPerfil() {
        $('#formInscritoPerfil').ajaxSubmit({
            target: '#mensagem'
                    , dataType: 'json'
                    , async: false
                    , cache: false
                    , beforeSend: function() {
                startLoading();
            }
            , timeout: 4000
                    , error: function(ajax) {
                showResponse(ajax);
            }
            , success: function(data) {
                processJson(data);
                if (data.statusMessage) {
                    carregarTabelaInscritoPerfils();
                    limparCampos();
                }
            }
            , complete: function() {
                stopLoading();
            }
        });
    }
    function editarInscritoPerfil(codInscritoPerfil) {
        if (codInscritoPerfil) {
            $.ajax({
                type: "POST",
                url: '<?php echo  WB_URL_COMMAND ?>&t=' + Math.random(),
                data: ({'codInscritoPerfil': codInscritoPerfil, 'c': 'GerenciarInscritoPerfil', 'm': 'editarInscritoPerfil', '_submit_check': '1'}),
                dataType: "json",
                async: false,
                cache: false,
                beforeSend: function() {
                    startLoading();
                },
                success: function(data) {
                    //Setando valores para alteração
                    $('#codInscritoPerfil').val(data.codInscritoPerfil);
                    $('#nomeInscritoPerfil').val(data.nomeInscritoPerfil);
                    $('#cpf').val(data.cpf);
                    $('#email').val(data.email);
                    $('#organizacao').val(data.organizacao);
                    $('#departamento').val(data.departamento);
                    $('#cpfCnpjAbonador').val(data.cpfCnpjAbonador);
                    $('#novoCodInscritoPerfil').val(1);
                    $('#salvarInscritoPerfil').val('Salvar Alterações');
                    $('#cpf').focus();
                    $('html,body').scrollTop(0);
                }
                , complete: function() {
                    stopLoading();
                }
            });
        } else {
            alert('Código do isento não informado!');
        }
    }
    function excluirInscritoPerfil(codInscritoPerfil) {
        if (confirm('Deseja realmente excluir InscritoPerfil?')) {
            $.ajax({
                type: "POST",
                url: '<?php echo  WB_URL_COMMAND ?>&t=' + Math.random(),
                data: ({'codInscritoPerfil': codInscritoPerfil, 'c': 'GerenciarInscritoPerfil', 'm': 'excluirInscritoPerfil', '_submit_check': '1'}),
                dataType: "json",
                async: false,
                cache: false,
                beforeSend: function() {
                    startLoading();
                },
                success: function(data) {
                    processJson(data);
                    if (data.statusMessage) {
                        carregarTabelaInscritoPerfils();
                    }
                    limparCampos();
                }
                , complete: function() {
                    stopLoading();
                }
            });
        }
    }
    function limparCampos() {
        //Limpando campos
        $('#codInscritoPerfil').val('');
        $('#nomeInscritoPerfil').val('');
        $('#cpf').val('');
        $('#email').val('');
        $('#organizacao').val('');
        $('#departamento').val('');
        $('#cpfCnpjAbonador').val('');
        $('#getMessage').html('');
        $('#novoCodInscritoPerfil').val(0);
        $('#salvarInscritoPerfil').val('Adicionar');

    }
function associarInscritoPerfilAdministrativo(codInscrito,codPerfil){
    var id_input_perfil= '#associar-perfil-'+codPerfil+'-'+codInscrito;
    var acao = $(id_input_perfil).prop("checked");
    
    if(codInscrito=='' || codPerfil==''){
        alert('Inscrito ou perfil não informado.');
        $(id_input_perfil).attr("checked",'false');
        return false;
    }    

    if(acao==true){
        msgAcao='Deseja associar o inscrito: '+codInscrito+' ao perfil administrativo: '+codPerfil+'?'
    }else{
        msgAcao='Deseja excluir associação entre o inscrito: '+codInscrito+' e o perfil administrativo: '+codPerfil+'?'
    }
    
    if(confirm(msgAcao)){
        $.ajax({
            type: "POST",
            url: '<?php echo  WB_URL_COMMAND?>&t='+Math.random(),
            data: ({'cod_inscrito':codInscrito,'cod_perfil':codPerfil, 'acao':acao ,'c':'GerenciarInscritoPerfil', 'm':'associarInscritoPerfilAdminstrativo'}),
            dataType: "json",
            async: false,
            cache: false,
            success: function(data){
                elementMsg = '#statusPresenca-'+codPerfil
                processJsonElement(data,elementMsg);
                // Mostra/remove o lapis de editar as horas na hora que marca/desmarca,
                // para ja dar pra configurar a carga de quem acabou de ser liberado.
                var pencilId = 'pencilPerfil-'+codPerfil+'-'+codInscrito;
                if(acao == true){
                    if($('#'+pencilId).length === 0){
                        $(id_input_perfil).closest('div').append(
                            " <a id='"+pencilId+"' href='javascript:void(0)'"
                          + " title='Editar carga horária desta pessoa neste perfil'"
                          + " onclick=\"editarCargaPerfil('"+codInscrito+"','"+codPerfil+"')\""
                          + " style='text-decoration:none; color:#2e7d32;'><i class='fa fa-pencil'></i></a>");
                    }
                }else{
                    $('#'+pencilId).remove();
                }
            }
            ,timeout: 4000
            ,beforeSend: function() {
                loading('div#carregando-'+codInscrito,'show');
            }
            ,error: function(ajax) {
                //showResponse(ajax);
            }
            ,complete: function() {        
                loading('div#carregando-'+codInscrito,'hidden');
            }
        });
    }else{
        if(acao==true)
            $(id_input_perfil).prop('checked', false);
        else
            $(id_input_perfil).prop('checked', true);
    }
}
// ---------------- Cadastro simples de inscrito ----------------
$(document).ready(function () {

    var $modalAdd = $('#modalAddInscrito');

    function fecharAdd() { $modalAdd.hide(); }
    // Abrir o modal SEMPRE volta pro modo "adicionar" (limpo e destravado).
    $('#addAbrir').on('click', function () { resetModalAddParaNovo(); $modalAdd.show(); $('#add_nome').focus(); });
    $('#addFechar, #addCancelar').on('click', fecharAdd);
    $modalAdd.on('click', function (e) { if (e.target === this) fecharAdd(); });

    // Carga horaria depende da atividade: ela e gravada na ligacao
    // inscrito<->oficina, que e de onde o certificado le. Os campos ficam
    // SEMPRE visiveis (so desabilitados) para nao esconder que existem.
    $('#add_oficina').on('change', function () {
        var tem = $(this).val() !== '';
        $('.ch-dep').prop('disabled', !tem);
        $('#add_avisoCh').toggle(!tem);
        if (!tem) { $('.ch-dep').val(''); }
    });

    $('#addSalvar').on('click', function () {
        // Modo edicao: salva SO a carga horaria daquela pessoa naquele perfil.
        if ($('#add_modo').val() === 'edit') { salvarCargaPerfilEdit(); return; }

        var nome = $.trim($('#add_nome').val());
        if (nome === '') { alert('Informe o nome da pessoa.'); $('#add_nome').focus(); return; }
        if ($('#add_perfil').val() === '') { alert('Escolha o perfil.'); $('#add_perfil').focus(); return; }

        var dados = {
            'c': 'GerenciarInscritoPerfil', 'm': 'adicionarInscritoSimples', '_submit_check': '1',
            'nome_inscrito': nome,
            'e_mail':        $.trim($('#add_email').val()),
            'cpf':           $.trim($('#add_cpf').val()),
            'cod_perfil':    $('#add_perfil').val(),
            'presente':      $('#add_presente').is(':checked') ? '1' : '',
            'perfil_carga_horaria':         $.trim($('#add_perfil_ch').val()),
            'perfil_carga_horaria_teorica': $.trim($('#add_perfil_ch_teorica').val()),
            'perfil_carga_horaria_pratica': $.trim($('#add_perfil_ch_pratica').val()),
            'cod_oficina':   $('#add_oficina').val(),
            'tipo_relacao':  $('#add_tipo_relacao').val(),
            'carga_horaria':          $.trim($('#add_ch').val()),
            'carga_horaria_teorica':  $.trim($('#add_ch_teorica').val()),
            'carga_horaria_pratica':  $.trim($('#add_ch_pratica').val())
        };

        startLoading();
        $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), dados, function (r) {
            processJson && processJson(r);
            if (r && r.statusMessage) {
                fecharAdd();
                setTimeout(function () { location.reload(); }, 900);
            }
        }, 'json').always(function () { stopLoading(); });
    });
});

// ---------------- Editar carga horaria da pessoa NAQUELE perfil ----------------
// Reusa o modal "Adicionar inscrito" em modo edicao. A carga da RELACAO
// (tab_lig_inscritos_perfis) prevalece: quando a ligacao tem qualquer carga
// propria, o modal mostra a da relacao no lugar do padrao do perfil.

function resetModalAddParaNovo() {
    $('#add_modo').val('add');
    $('#add_cod_inscrito_edit').val('');
    $('#add_cod_perfil_edit').val('');
    $('#add_nome, #add_email, #add_cpf').val('').prop('readonly', false);
    $('#add_presente').prop('checked', true).prop('disabled', false);
    $('#add_perfil').val('').prop('disabled', false);
    $('#add_perfil_ch, #add_perfil_ch_teorica, #add_perfil_ch_pratica').val('');
    $('#add_oficina').val('');
    $('#add_tipo_relacao, #add_ch, #add_ch_teorica, #add_ch_pratica').val('').prop('disabled', true);
    $('#add_avisoCh').show();
    $('#add_blocoAtividade').show();
    $('#modalAddTitulo').html('<i class="fa fa-user-plus"></i> Adicionar inscrito');
    $('#addSalvar').html('<i class="fa fa-save"></i> Cadastrar');
}

function editarCargaPerfil(codInscrito, codPerfil) {
    startLoading();
    $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        {'c': 'GerenciarInscritoPerfil', 'm': 'carregarCargaPerfil', '_submit_check': '1',
         'cod_inscrito': codInscrito, 'cod_perfil': codPerfil},
        function (d) {
            if (!d) { alert('Não foi possível carregar os dados desta pessoa/perfil.'); return; }

            $('#add_modo').val('edit');
            $('#add_cod_inscrito_edit').val(codInscrito);
            $('#add_cod_perfil_edit').val(codPerfil);

            // Dados da pessoa: so leitura no modo edicao.
            $('#add_nome').val(d.nome_inscrito || '').prop('readonly', true);
            $('#add_email').val(d.e_mail || '').prop('readonly', true);
            $('#add_cpf').val(d.cpf || '').prop('readonly', true);
            $('#add_presente').prop('checked', d.presente == 1).prop('disabled', true);

            // Perfil travado no perfil que esta sendo editado.
            $('#add_perfil').val(codPerfil).prop('disabled', true);

            // A RELACAO vence o padrao: se a ligacao tem qualquer carga, usa a dela.
            var temLig = (d.lig_ch || '') !== '' || (d.lig_ch_teorica || '') !== '' || (d.lig_ch_pratica || '') !== '';
            if (temLig) {
                $('#add_perfil_ch').val(d.lig_ch || '');
                $('#add_perfil_ch_teorica').val(d.lig_ch_teorica || '');
                $('#add_perfil_ch_pratica').val(d.lig_ch_pratica || '');
            } else {
                // Sem carga na relacao: mostra o padrao do perfil como base.
                $('#add_perfil_ch').val(d.perfil_ch_padrao || '');
                $('#add_perfil_ch_teorica').val('');
                $('#add_perfil_ch_pratica').val('');
            }

            // Bloco de atividade (ligacao com oficina) nao se aplica na edicao do perfil.
            $('#add_blocoAtividade').hide();

            $('#modalAddTitulo').html('<i class="fa fa-pencil"></i> Editar carga horária — ' + (d.perfil_titulo || 'perfil'));
            $('#addSalvar').html('<i class="fa fa-save"></i> Salvar alterações');
            $('#modalAddInscrito').show();
        }, 'json').always(function () { stopLoading(); });
}

function salvarCargaPerfilEdit() {
    var dados = {
        'c': 'GerenciarInscritoPerfil', 'm': 'salvarCargaPerfil', '_submit_check': '1',
        'cod_inscrito': $('#add_cod_inscrito_edit').val(),
        'cod_perfil':   $('#add_cod_perfil_edit').val(),
        'perfil_carga_horaria':         $.trim($('#add_perfil_ch').val()),
        'perfil_carga_horaria_teorica': $.trim($('#add_perfil_ch_teorica').val()),
        'perfil_carga_horaria_pratica': $.trim($('#add_perfil_ch_pratica').val())
    };
    startLoading();
    $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), dados, function (r) {
        processJson && processJson(r);
        if (r && r.statusMessage) {
            $('#modalAddInscrito').hide();
            setTimeout(function () { location.reload(); }, 900);
        }
    }, 'json').always(function () { stopLoading(); });
}

// Alterna presenca/ausencia do inscrito no evento (tab_inscritos.flg_presenca_inscrito).
// Usa o mesmo metodo das listagens de inscritos.
function alternarPresencaInscrito(codInscrito){
    var $chk    = $('#presencaInscrito-'+codInscrito);
    var $status = $('#statusPresencaInscrito-'+codInscrito);
    var desejado = $chk.prop('checked'); // estado apos o clique
    $.ajax({
        type: "POST"
        ,url: '<?php echo  WB_URL_COMMAND?>&t='+Math.random()
        ,data: ({'cod_inscrito':codInscrito, 'c':'ListarInscritos', 'm':'alternarPresencaInscrito', '_submit_check':'1'})
        ,dataType: "json"
        ,cache: false
        ,beforeSend: function() {
            $chk.prop('disabled', true);
        }
        ,success: function(data){
            if(data && data.statusMessage == 1){
                // Rotulo Liberado/Bloqueado (coluna mestre de liberacao geral);
                // o mecanismo continua sendo a presenca no evento.
                $status.text(desejado ? 'Liberado' : 'Bloqueado')
                       .css('color', desejado ? 'green' : 'red');
            }else{
                // Nao gravou: volta o checkbox para o estado anterior.
                $chk.prop('checked', !desejado);
                alert('Não foi possível alterar a presença do inscrito '+codInscrito+'.');
            }
        }
        ,error: function(){
            $chk.prop('checked', !desejado);
            alert('Erro de comunicação ao alterar a presença.');
        }
        ,complete: function() {
            $chk.prop('disabled', false);
        }
    });
}

// Certificado GERAL de participacao: interruptor de liberacao (marcado = libera),
// irmao dos checkboxes de perfil. Desmarcar grava o bloqueio (flg=1). Desmarcado
// fica em vermelho para a excecao saltar aos olhos. Presenca (coluna mestre)
// continua liberando/bloqueando TODOS. O certificado especial do papel nao e afetado.
function alternarBloqueioCertificadoGeral(codInscrito){
    var $chk = $('#bloqGeral-'+codInscrito);
    var $lbl = $('#lblGeral-'+codInscrito);
    var receber  = $chk.prop('checked'); // estado apos o clique: marcado = libera
    var bloquear = !receber;
    $.ajax({
        type: "POST"
        ,url: '<?php echo  WB_URL_COMMAND?>&t='+Math.random()
        ,data: ({'cod_inscrito':codInscrito, 'bloquear': bloquear ? '1' : '', 'c':'GerenciarInscritoPerfil', 'm':'alternarBloqueioCertificadoGeral', '_submit_check':'1'})
        ,dataType: "json"
        ,cache: false
        ,beforeSend: function() {
            $chk.prop('disabled', true);
        }
        ,success: function(data){
            if(data && data.statusMessage == 1){
                $lbl.css('color', bloquear ? '#b3261e' : '');
            }else{
                // Nao gravou: volta o checkbox para o estado anterior.
                $chk.prop('checked', !receber);
                alert('Não foi possível alterar o certificado geral do inscrito '+codInscrito+'.');
            }
        }
        ,error: function(){
            $chk.prop('checked', !receber);
            alert('Erro de comunicação ao alterar o certificado geral.');
        }
        ,complete: function() {
            $chk.prop('disabled', false);
        }
    });
}
</script>
<!--<script type="text/javascript" src="/<?php echo  WB_PATH ?>/view/js/util/validacoes.js"></script>-->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
<script type="text/javascript" src="resources/js/jquery/jquery.form.js"></script>
<script type="text/javascript" src="resources/js/jquery/jquery.price_format.1.7.min.js"></script>
<script type="text/javascript" src="resources/js/default.js"></script>
<link   rel="stylesheet"      href="resources/js/jquery.tablesorter/themes/blue/style.css" type="text/css" id="" media="print, projection, screen" />
<script type="text/javascript" src="resources/js/jquery.tablesorter/jquery.tablesorter.js"></script>
<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/titulo_area_admin.php");
require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_administracao.php");
require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_configuracao.php");
require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_certificados.php");
?>
<div class="panel-group">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4 style="margin-bottom:4px;">Libera&ccedil;&atilde;o de certificado por pessoa</h4>
            <small class="text-muted">Marque o papel ao lado do nome para <b>liberar o certificado administrativo</b> daquela pessoa (ex.: Ministrante). Busque pelo nome, CPF ou c&oacute;digo.</small>
        </div>
        <div class="panel-body">
            <div class="form-group col-sm-12" style="padding-left: 0px;">
                <?php /* Card do formulario de busca -- separa visualmente da lista.
                         overflow:hidden contem as colunas Bootstrap flutuantes. */ ?>
                <div style="background:#f2f7f3; border:1px solid #dfe4df; border-radius:8px; padding:14px 16px 4px; margin-bottom:4px; overflow:hidden;">
                <form id="filtroInscritoPerfil" name="filtroInscritoPerfil" action="<?php echo WB_URL_VIEW ?>?content=view/admin/GerenciarInscritoPerfil.php&dummy=" method="post">
                        <div class="form-group col-sm-2" style="padding-left: 0px;">
                            <label for="cod_inscrito">Código do Inscrito:</label>
                            <input type="text" name="cod_inscrito" id="cod_inscrito" value="<?php echo isset($fws_['cod_inscrito']) ? $fws_['cod_inscrito'] : ''; ?>" class="form-control">
                        </div>
                        <div class="form-group col-sm-2" style="padding-left: 0px;">
                            <label for="cpf">CPF:</label>
                            <input type="text" name="cpf" id="cpf" value="<?php echo isset($fws_['cpf']) ? $fws_['cpf'] : ''; ?>" class="form-control">
                        </div>
                        <div class="form-group col-sm-8" style="padding-left: 0px;">
                            <label for="nome_inscrito">Nome do Inscrito:</label>
                            <input type="text" name="nome_inscrito" id="nome_inscrito" value="<?php echo isset($fws_['nome_inscrito']) ? $fws_['nome_inscrito'] : ''; ?>" class="form-control">
                        </div>
                        <div class="form-group col-sm-12" style="padding-left: 0px;">
                            <label for="cod_perfil">Selecione Perfil de Cadastro: </label>
                            <select name="cod_perfil" id="cod_perfil" class="form-control" placeholder="Selecione um Perfil">
                                <option></option>
                                <?php 
                                try {
                                    CarregarOptionsAC::perfisCadAdmin($fws_, $debug); 
                                } catch (Exception $exc) {
                                    echo $exc->getTraceAsString();
                                }
                                ?>
                            </select>
                        </div>
                        <?php /* Filtros de estado da listagem (aplicados no controller). */ ?>
                        <div class="form-group col-sm-4" style="padding-left: 0px;">
                            <label for="filtro_liberado">Certificado especial:</label>
                            <select name="filtro_liberado" id="filtro_liberado" class="form-control">
                                <option value="">Todos</option>
                                <option value="sim" <?php echo (@$fws_['filtro_liberado']==='sim')?'selected':'' ?>>J&aacute; liberado (tem papel)</option>
                                <option value="nao" <?php echo (@$fws_['filtro_liberado']==='nao')?'selected':'' ?>>N&atilde;o liberado</option>
                            </select>
                        </div>
                        <div class="form-group col-sm-4" style="padding-left: 0px;">
                            <label for="filtro_pagamento">Pagamento:</label>
                            <select name="filtro_pagamento" id="filtro_pagamento" class="form-control">
                                <option value="">Todos</option>
                                <option value="pago" <?php echo (@$fws_['filtro_pagamento']==='pago')?'selected':'' ?>>Pago ou isento</option>
                                <option value="sem" <?php echo (@$fws_['filtro_pagamento']==='sem')?'selected':'' ?>>Sem pagamento</option>
                            </select>
                        </div>
                        <div class="form-group col-sm-4" style="padding-left: 0px;">
                            <label for="filtro_geral">Certificado geral:</label>
                            <select name="filtro_geral" id="filtro_geral" class="form-control">
                                <option value="">Todos</option>
                                <option value="recebe" <?php echo (@$fws_['filtro_geral']==='recebe')?'selected':'' ?>>Recebe</option>
                                <option value="bloqueado" <?php echo (@$fws_['filtro_geral']==='bloqueado')?'selected':'' ?>>Bloqueado</option>
                            </select>
                        </div>
                        <div class="form-group col-sm-12" style="padding-left: 0px;">
                            <label for="nome">Ordenar listagem por:</label>
                            <input onclick="submit()" type='radio' name='ordenar' id='ordenar' value='cod_inscrito' <?php echo verifItemChecked($fws_['ordenar'], 'cod_inscrito') ?>> Cód.Inscrito
                            &nbsp;&nbsp;
                            <input onclick="submit()" type='radio' name='ordenar' id='ordenar' value='nome_inscrito' <?php echo verifItemChecked($fws_['ordenar'], 'nome_inscrito') ?>> Nome do Inscrito
                            &nbsp;&nbsp;
                            <input onclick="submit()" type='radio' name='ordenar' id='ordenar' value='cpf' <?php echo verifItemChecked($fws_['ordenar'], 'cpf') ?>> Cpf
                        </div>                        
                        <div class="form-group col-sm-12" style="padding-left: 0px;">
                            <button type="submit" class="btn btn-sm btn-primary" style="margin-bottom:0;"><i class="fa fa-filter"></i> Filtrar</button>
                            <a href="<?php echo WB_URL_VIEW ?>?content=view/admin/GerenciarInscritoPerfil.php&dummy=" class="btn btn-sm btn-default" style="margin-bottom:0; margin-left:6px;">Limpar</a>
                        </div>
                </form>
                </div><!-- /card do formulario de busca -->
                <div class="clearfix"></div>

                <?php
                // A coluna de bloqueio do certificado GERAL so faz sentido em evento
                // que emite o geral de participacao (certificado_congressista=1).
                $mostrarColunaGeral = false;
                try {
                    $objConfCol = EventoConfiguracao::getInstancePopulado(COD_EVENTO);
                    $mostrarColunaGeral = ($objConfCol->getCertificadoCongressista() == 1);
                } catch (Exception $e) {
                    $mostrarColunaGeral = false;
                }
                $fws_['mostrar_coluna_geral'] = $mostrarColunaGeral;
                ?>

                <?php /* Barra da lista: titulo a esquerda + acao "Adicionar" a direita.
                         O clear:both e obrigatorio -- o form de filtro acima usa colunas
                         Bootstrap flutuantes sem clearfix. */ ?>
                <div style="clear:both; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px; border-top:1px solid #e3ebf3; padding-top:14px; margin-top:6px;">
                    <b style="font-size:14px;">Lista de inscritos / perfis cadastrados</b>
                    <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                        <small class="text-muted">para quem participou mas n&atilde;o se inscreveu pelo site</small>
                        <button type="button" id="addAbrir" class="btn btn-primary btn-sm">
                            <i class="fa fa-user-plus"></i> Adicionar inscrito
                        </button>
                    </div>
                </div>

                <div style="overflow-x:auto; margin-top:10px;">
                <table class="tablesorter" id="itens" border="0" style="width:100%;">
                    <thead>
                    <!-- <th width="10px"></th> -->
                    <th width="250px">Nome</th>
                    <th width="120px">Pagamento / Isen&ccedil;&atilde;o</th>
                    <th width="130px">Libera&ccedil;&atilde;o geral<br><small style="font-weight:normal; color:#888;">presen&ccedil;a &middot; libera/bloqueia <b>todos</b></small></th>
                    <?php if ($mostrarColunaGeral) { ?>
                    <th width="150px">Certificado<br><small style="font-weight:normal; color:#888;">s&oacute; o de participa&ccedil;&atilde;o</small></th>
                    <?php } ?>
                    <th width="250px">Certificados especiais<br><small style="font-weight:normal; color:#888;">cada papel/perfil</small></th>
                    </thead>
                    <tbody class="inputs">
                        <?php
                        $objSRV->carregarTabelaInscritoPerfils($fws_, $debug);
                        ?>
                    </tbody>
                </table>
                </div><!-- /overflow-x da tabela -->
            </div>
        </div>
    </div>
</div><!-- /panel-group -- estava SEM fechar: o fechamento do .miolo (index.php:61)
         acabava fechando este panel-group e deixava o .container.miolo aberto, jogando
         o rodape para dentro dele (largura fixa) -- origem da faixa branca a direita. -->

<!-- ===================== Modal: adicionar inscrito ===================== -->
<div id="modalAddInscrito" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:1050;">
    <div style="max-width:760px; margin:4% auto; background:#fff; border-radius:10px; overflow:hidden; box-shadow:0 10px 40px rgba(0,0,0,.3);">
        <div style="display:flex; justify-content:space-between; align-items:center; padding:12px 18px; background:#2e7d32; color:#fff;">
            <b id="modalAddTitulo"><i class="fa fa-user-plus"></i> Adicionar inscrito</b>
            <span style="cursor:pointer; font-size:22px; line-height:1;" id="addFechar">&times;</span>
        </div>

        <div style="padding:16px 18px; max-height:72vh; overflow:auto;">
            <?php /* Estado do modal: 'add' (novo inscrito) ou 'edit' (so a carga da
                     relacao-perfil de alguem ja cadastrado). */ ?>
            <input type="hidden" id="add_modo" value="add">
            <input type="hidden" id="add_cod_inscrito_edit" value="">
            <input type="hidden" id="add_cod_perfil_edit" value="">
            <div class="row">
                <div class="form-group col-sm-12">
                    <label for="add_nome">Nome <span style="color:#c00;">*</span></label>
                    <input type="text" id="add_nome" class="form-control input-sm" maxlength="255" placeholder="nome completo">
                </div>
            </div>
            <div class="row">
                <div class="form-group col-sm-7">
                    <label for="add_email">E-mail <small class="text-muted">(opcional)</small></label>
                    <input type="text" id="add_email" class="form-control input-sm" maxlength="255">
                </div>
                <div class="form-group col-sm-5">
                    <label for="add_cpf">CPF <small class="text-muted">(opcional)</small></label>
                    <input type="text" id="add_cpf" class="form-control input-sm" maxlength="20">
                </div>
            </div>
            <div class="row">
                <div class="form-group col-sm-12">
                    <label style="font-weight:normal;">
                        <input type="checkbox" id="add_presente" checked>
                        Marcar como <b>presente</b>
                        <small class="text-muted">(vale para o cadastro e para o perfil abaixo)</small>
                    </label>
                </div>
            </div>

            <?php /* Bloco do PERFIL -- mesmo esquema do bloco de atividade logo
                     abaixo: o vinculo, a presenca e a carga horaria da pessoa
                     NAQUELE papel (tab_lig_inscritos_perfis). */ ?>
            <div style="border-top:1px solid #e3ebf3; margin-top:2px; padding-top:12px;">
                <div style="font-weight:600; font-size:13px; color:#2e7d32; margin-bottom:2px;">
                    Perfil <span style="color:#c00;">*</span>
                </div>
                <small class="text-muted" style="display:block; margin-bottom:10px;">
                    Carga hor&aacute;ria da pessoa <b>neste perfil</b>.
                    Deixe vazio para usar a carga hor&aacute;ria padr&atilde;o do pr&oacute;prio perfil.
                </small>
                <div class="row">
                    <div class="form-group col-sm-12">
                        <select id="add_perfil" class="form-control input-sm">
                            <option value="">Selecione o perfil&hellip;</option>
                            <?php
                            // Atencao: perfis() le 'codEvento' (camelCase) e oficinas() le
                            // 'cod_evento'. Sem passar explicito, listariam de TODOS os eventos.
                            // semRestricao=1: sem ele, perfis() so mostra quem "recebe
                            // inscricao aberta" e some com Ministrante, Coordenador etc. --
                            // justamente os que aparecem nesta listagem.
                            $fwsAdd = array('codEvento' => COD_EVENTO, 'cod_evento' => COD_EVENTO,
                                            'cod_perfil' => '', 'semRestricao' => true);
                            try { CarregarOptionsAC::perfis($fwsAdd, $debug); } catch (Exception $e) {}
                            ?>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-sm-4">
                        <label for="add_perfil_ch">Carga hor&aacute;ria total <small class="text-muted">(ex.: 40h)</small></label>
                        <input type="text" id="add_perfil_ch" class="form-control input-sm" maxlength="10">
                    </div>
                    <div class="form-group col-sm-4">
                        <label for="add_perfil_ch_teorica">Te&oacute;rica <small class="text-muted">(ex.: 24h)</small></label>
                        <input type="text" id="add_perfil_ch_teorica" class="form-control input-sm" maxlength="10">
                    </div>
                    <div class="form-group col-sm-4">
                        <label for="add_perfil_ch_pratica">Pr&aacute;tica <small class="text-muted">(ex.: 16h)</small></label>
                        <input type="text" id="add_perfil_ch_pratica" class="form-control input-sm" maxlength="10">
                    </div>
                </div>
            </div>

            <div id="add_blocoAtividade" style="border-top:1px solid #e3ebf3; margin-top:6px; padding-top:12px;">
                <div style="font-weight:600; font-size:13px; color:#2e7d32; margin-bottom:2px;">
                    Participa&ccedil;&atilde;o numa atividade <small class="text-muted" style="font-weight:normal;">(opcional)</small>
                </div>
                <small class="text-muted" style="display:block; margin-bottom:10px;">
                    Carga hor&aacute;ria da pessoa <b>nesta atividade</b>.
                    Preencha s&oacute; se for diferente da carga hor&aacute;ria da pr&oacute;pria atividade.
                </small>
                <div class="row">
                    <div class="form-group col-sm-7">
                        <label for="add_oficina">Atividade</label>
                        <select id="add_oficina" class="form-control input-sm">
                            <option value="">Sem atividade</option>
                            <?php try { CarregarOptionsAC::oficinas($fwsAdd, $debug); } catch (Exception $e) {} ?>
                        </select>
                    </div>
                    <div class="form-group col-sm-5">
                        <label for="add_tipo_relacao">Tipo de rela&ccedil;&atilde;o</label>
                        <select id="add_tipo_relacao" class="form-control input-sm ch-dep" disabled>
                            <option value="">Selecione&hellip;</option>
                            <?php try { echo CarregarOptionsAC::tipoRelacao('', false); } catch (Exception $e) {} ?>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-sm-4">
                        <label for="add_ch">Carga hor&aacute;ria total <small class="text-muted">(ex.: 16h)</small></label>
                        <input type="text" id="add_ch" class="form-control input-sm ch-dep" maxlength="10" disabled>
                    </div>
                    <div class="form-group col-sm-4">
                        <label for="add_ch_teorica">Te&oacute;rica <small class="text-muted">(ex.: 10h)</small></label>
                        <input type="text" id="add_ch_teorica" class="form-control input-sm ch-dep" maxlength="10" disabled>
                    </div>
                    <div class="form-group col-sm-4">
                        <label for="add_ch_pratica">Pr&aacute;tica <small class="text-muted">(ex.: 6h)</small></label>
                        <input type="text" id="add_ch_pratica" class="form-control input-sm ch-dep" maxlength="10" disabled>
                    </div>
                </div>
                <small id="add_avisoCh" class="text-muted"><i class="fa fa-info-circle"></i>
                    Escolha a atividade para liberar os campos de carga hor&aacute;ria.</small>
            </div>
        </div>

        <div style="padding:12px 18px; border-top:1px solid #eee; text-align:right;">
            <button type="button" class="btn btn-default btn-sm" id="addCancelar">Cancelar</button>
            <button type="button" class="btn btn-primary btn-sm" id="addSalvar"><i class="fa fa-save"></i> Cadastrar</button>
        </div>
    </div>
</div>

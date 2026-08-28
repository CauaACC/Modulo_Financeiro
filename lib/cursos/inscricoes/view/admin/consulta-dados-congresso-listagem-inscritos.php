<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/consulta-dados-congresso-cabecalho.php");
#require_once(dirname(__FILE__)."/../../view/admin/consulta-dados-congresso-busca.php");
echo "<div class=\"panel-body\">";

$fws_['situacao'] = $fws_['situacao'] == '' ? 'ATIVADA' : $fws_['situacao'];
$fws_['status_processo'] = $fws_['status_processo'] == '' ? '' : $fws_['status_processo'];
if($fws_['perspectiva'] == ''){
    if($objEvConf->getAtividadeTipo()=='Agenda'){
         $fws_['perspectiva'] = "listagemComAtividade";
         if($fws_['ordenar'] == ''){
            $fws_['orenar']      = "data_agendamento";
         }
    }
}
$fws_['ordenar'] = $fws_['ordenar'] == '' ? 'cod_inscrito' : $fws_['ordenar'];
$fws_['perspectiva'] = $fws_['perspectiva'] == '' ? 'simplificada' : $fws_['perspectiva'];
if($fws_['data_pagamento_ini'] or $fws_['data_pagamento_fim']){
    $fws_['ordenar'] = "data_pagamento";
}
if($fws_['data_pagamento_incl_ini'] or $fws_['data_pagamento_incl_fim']){
    $fws_['ordenar'] = "data_pagamento";
}
$fws_['consultaPresenca']    = $fws_['consultaPresenca']    == '' ? ''             : $fws_['consultaPresenca'];
$fws_['comunicado']  = $fws_['comunicado']  == '' ? '0'            : $fws_['comunicado'];
$fws_['acaoEmLote']  = $fws_['acaoEmLote']  == '' ? ''             : $fws_['acaoEmLote'];

if ($fws_['tipo_inscricao'] == ''){
    $fws_['tipo_inscricao'] = $fwsGet_['tipo_inscricao'];
    if($fws_['tipo_inscricao'])
        $fws_['listar'] = 1;
}
if ($fws_['pagamentos'] == ''){
    $fws_['pagamentos'] = $fwsGet_['pagamentos'];
    if($fws_['pagamentos']){
        $fws_['perspectiva'] = 'detalhada';
        $fws_['listar'] = 1;
        $fws_['cod_evento'] = COD_EVENTO;
    }
}
if ($fws_['cod_perfil'] == ''){
    $fws_['cod_perfil'] = $fwsGet_['cod_perfil'];
    if($fws_['cod_perfil'])
        $fws_['listar'] = 1;
}
if ($fws_['cod_area_tematica'] == ''){
    $fws_['cod_area_tematica'] = $fwsGet_['cod_area_tematica'];
    if($fws_['cod_area_tematica'])
        $fws_['listar'] = 1;
}
if ($fws_['cod_oficina'] == ''){
    $fws_['cod_oficina'] = $fwsGet_['cod_oficina'];
    if($fws_['cod_oficina'])
        $fws_['listar'] = 1;
}
if ($fws_['estado'] == ''){
    $fws_['estado'] = $fwsGet_['estado'];
    if($fws_['estado'])
        $fws_['listar'] = 1;
}
if ($fws_['pagamentos'] == ''){
    $fws_['pagamentos'] = $fwsGet_['pagamentos'];
    if($fws_['pagamentos']){
        $fws_['listar'] = 1;
    }
}
if ($fws_['forma_pagamento'] == ''){
    $fws_['forma_pagamento'] = $fwsGet_['forma_pagamento'];
    if($fws_['forma_pagamento']){
        $fws_['perspectiva'] = 'detalhada';
        $fws_['listar'] = 1;
        $fws_['cod_evento'] = COD_EVENTO;
    }
}
if ($fws_['patrocinador_voucher'] == ''){
    $fws_['patrocinador_voucher'] = $fwsGet_['patrocinador_voucher'];
    if($fws_['patrocinador_voucher']){
        $fws_['listar'] = 1;
        $fws_['cod_evento'] = COD_EVENTO;
    }
}
if ($fws_['presenca_inscrito'] == ''){
    $fws_['presenca_inscrito'] = $fwsGet_['presenca_inscrito'];
    if($fws_['presenca_inscrito'])
        $fws_['listar'] = 1;
}
if ($fws_['fone_contato'] == ''){
    $fws_['fone_contato'] = $fwsGet_['fone_contato'];
    if($fws_['fone_contato'])
        $fws_['listar'] = 1;
}
if ($fws_['nome_inscrito'] == ''){
    $fws_['nome_inscrito'] = $fwsGet_['nome_inscrito'];
    if ($fws_['nome_inscrito'])
        $fws_['listar'] = 1;
}
if ($fws_['cod_inscrito'] == ''){
    $fws_['cod_inscrito'] = $fwsGet_['cod_inscrito'];
    if ($fws_['cod_inscrito'])
        $fws_['listar'] = 1;
}
if ($fws_['cod_trabalho'] == ''){
    $fws_['cod_trabalho'] = $fwsGet_['cod_trabalho'];
    if ($fws_['cod_trabalho'])
        $fws_['listar'] = 1;
}
if ($fws_['pedido_id'] == ''){
    $fws_['pedido_id'] = $fwsGet_['pedido_id'];
    if ($fws_['pedido_id'])
        $fws_['listar'] = 1;
}
if ($fws_['transacao_id'] == ''){
    $fws_['transacao_id'] = $fwsGet_['transacao_id'];
    if ($fws_['transacao_id'])
        $fws_['listar'] = 1;
}
if ($fws_['id_publico'] == ''){
    $fws_['id_publico'] = $fwsGet_['id_publico'];
    if ($fws_['id_publico'])
        $fws_['listar'] = 1;
}
if ($fws_['e_mail'] == ''){
    $fws_['e_mail'] = $fwsGet_['e_mail'];
    if ($fws_['e_mail'])
        $fws_['listar'] = 1;
}
if ($fws_['cpf'] == ''){
    $fws_['cpf'] = $fwsGet_['cpf'];
    if ($fws_['cpf'])
        $fws_['listar'] = 1;
}
if ($fws_['cod_evento'] == ''){
    $fws_['cod_evento'] = $fwsGet_['cod_evento'];
}
/** Combinações */
if ($fws_['xml_pagamentos_carregados']=='nao-carregados'){
    $fws_['xml_pagamentos']=104;
}
if ($fws_['xml_pagamentos']==104){
    $fws_['perspectiva'] = 'listagemPagamento';
}
/** se ativar busca por trabalhos avaliados, é necessário ativar quantidade de trabalhos enviados */
if ($fws_['avalicao_trabalhos']!=''){
    $fws_['trabalhos'] = '1or';
}
/** @todo Avaliação de Inscrito */
if (in_array($objEvConf->getCodEvento(),[80,88,107,115])){ 
    // if($fws_['avaliacao_inscrito'] != ''){
    //     $fws_['perspectiva']='listagemAvaliacao';
    // }
    if(($fws_['ordenar'] == 'nota_informada')or($fws_['ordenar'] == 'nota_confirmada')){
        $fws_['perspectiva']='listagemAvaliacao';
    }
    // Avaliador puro (sem permissão de gerenciar inscrito): perspectiva fixa em listagemAvaliacao
    if (FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_AVALIACAO_INSCRITO, 5, false, false)
        && !(FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_GERENCIAR_INSCRITO, 5, false, false))){
        $fws_['perspectiva']='listagemAvaliacao';
    }
    // $fws_['perspectiva']='';
}
$objEvento = new Evento();
$objEvento->setCodUsuario($_SESSION['_cod_usuario']);
$objEvento->carregarListaEventosByIdLogin($debug);
$totalEventosUsuarioLogado = count($objEvento->getListaEventos());
//$totalEventosUsuarioLogado = 0;
?>
<script type="text/javascript" src="resources/js/tinymce_4.3.0/js/tinymce/tinymce.min.js"></script>
<script language="JavaScript" type="text/javascript">
$(document).ready(function () {
    $('#data_inscricao_ini').datetimepicker({
        format:'d/m/Y H:i',
        step: 1,
        // minDate: 0,
        timepicker:true,
        allowInputToggle: true
    });
    $('#data_inscricao_fim').datetimepicker({
        format:'d/m/Y H:i',
        step: 1,
        // minDate: 0,
        timepicker:true,
        allowInputToggle: true

    });

    $("table.tablesorter").tablesorter({widgets: ['zebra']})
//    .tablesorterPager({container: $("#pager")}); 
   
   //optDatePicker
//    $('#data_pagamento_ini').datepicker();
//    $('#data_pagamento_fim').datepicker();
    
    $("input[type='text']").keypress(function(e){
        if(e.which == 13) {
            submitConsulta();
        }
    });

    // Atualiza tags de filtros ativos em tempo real quando usuário altera campos
    $('#formListaInscritos').find('input,select,textarea').not('#filtro_salvo_aplicado,#filtroSalvoSelect,[name=content],[name=c],[name=m],[name=media],[name=listar],[name=paginarMais],#inputNomeFiltroSalvar').change(function(){
        limparFiltroSalvoAplicado();
        atualizarTagsFiltrosAtivos();
    });

    // Gera tags no carregamento da página (baseado no estado atual do form)
    atualizarTagsFiltrosAtivos();

    $("#estado").change(function(){
        carregarCidades(this.value);
    });
    $('#exportarXml').click(function(){
        $('#formListaInscritos').attr('action', '<?php echo WB_URL_COMMAND?>');
        var codEvento =  $('#cod_evento').val();
        if(codEvento!='-1'){
            $('#formListaInscritos').attr('action', '<?php echo WB_URL_DOMINIO . '/' . WB_GRUPO ?>/'+codEvento+'/inscricoes/index-action.php?content=controller/CommandAC.php');
        }else{
            $('#formListaInscritos').attr('action', '<?php echo WB_URL_DOMINIO . '/' . WB_GRUPO.'/'.COD_EVENTO ?>/inscricoes/index-action.php?content=controller/CommandAC.php');
        }

        $('#c').val('ListarInscritos');
        $('#m').val('carregarXml');
        $("input[name=media]").val(['xml']);
        $('#formListaInscritos').submit();
    });
    $('#exportarExcel').click(function(){
        //$('#formListaInscritos').attr('action', '<?php echo WB_URL_COMMAND?>');
        var codEvento =  $('#cod_evento').val();
        if(codEvento!='-1'){
            $('#formListaInscritos').attr('action', '<?php echo WB_URL_DOMINIO . '/' . WB_GRUPO ?>/'+codEvento+'/inscricoes/index-action.php?content=controller/CommandAC.php');
        }else{
            $('#formListaInscritos').attr('action', '<?php echo WB_URL_DOMINIO . '/' . WB_GRUPO.'/'.COD_EVENTO ?>/inscricoes/index-action.php?content=controller/CommandAC.php');
        }
        $('#c').val('ListarInscritos');
        $('#m').val('carregarXls');
        $("input[name=media]").val(['xls']);
        $('#formListaInscritos').submit();
    });
    <?php if ($objEvConf->getCodEvento()==882) { ?>
        $('#exportarRegistrosXls').click(function(){
            //$('#formListaInscritos').attr('action', '<?php echo WB_URL_COMMAND?>');
            var codEvento =  $('#cod_evento').val();
                $('#formListaInscritos').attr('action', '<?php echo WB_URL_DOMINIO . '/' . WB_GRUPO.'/'.COD_EVENTO ?>/inscricoes/index-action.php?content=controller/CommandAC.php');
            $('#c').val('ListarInscritos');
            $('#m').val('carregarRegistrosXls');
            $("input[name=media]").val(['xls']);
            $('#formListaInscritos').submit();
        });
    <?php } ?>
    <?php if ($objEvConf->getRecebePagamentoMensalidade()==1) { ?>
    $('#exportarMensalidadesXls').click(function(){
        //$('#formListaInscritos').attr('action', '<?php echo WB_URL_COMMAND?>');
        var codEvento =  $('#cod_evento').val();
            $('#formListaInscritos').attr('action', '<?php echo WB_URL_DOMINIO . '/' . WB_GRUPO.'/'.COD_EVENTO ?>/inscricoes/index-action.php?content=controller/CommandAC.php');
        $('#c').val('ListarInscritos');
        $('#m').val('carregarMensalidadesXls');
        $("input[name=media]").val(['xls']);
        $('#formListaInscritos').submit();
    });
    $('#exportarAcertosXls').click(function(){
        //$('#formListaInscritos').attr('action', '<?php echo WB_URL_COMMAND?>');
        var codEvento =  $('#cod_evento').val();
            $('#formListaInscritos').attr('action', '<?php echo WB_URL_DOMINIO . '/' . WB_GRUPO.'/'.COD_EVENTO ?>/inscricoes/index-action.php?content=controller/CommandAC.php');
        $('#c').val('ListarInscritos');
        $('#m').val('carregarAcertosXls');
        $("input[name=media]").val(['xls']);
        $('#formListaInscritos').submit();
    });
    <?PHP } ?>
    $('#exportarHtml').click(function(){
        //$('#formListaInscritos').attr('action', '<?php echo WB_URL_COMMAND?>');
        var codEvento =  $('#cod_evento').val();
        if(codEvento!='-1'){
            $('#formListaInscritos').attr('action', '<?php echo WB_URL_DOMINIO . '/' . WB_GRUPO ?>/'+codEvento+'/inscricoes/index-action.php?content=controller/CommandAC.php');
        }else{
            $('#formListaInscritos').attr('action', '<?php echo WB_URL_DOMINIO . '/' . WB_GRUPO.'/'.COD_EVENTO ?>/inscricoes/index-action.php?content=controller/CommandAC.php');
        }
        $('#c').val('ListarInscritos');
        $('#m').val('carregarHtml');
        $("input[name=media]").val(['xls']);
        $('#formListaInscritos').submit();
    });
    $('#exportarEditalHtml').click(function(){
        //$('#formListaInscritos').attr('action', '<?php echo WB_URL_COMMAND?>');
        var codEvento =  $('#cod_evento').val();
        if(codEvento!='-1'){
            $('#formListaInscritos').attr('action', '<?php echo WB_URL_DOMINIO . '/' . WB_GRUPO ?>/'+codEvento+'/inscricoes/index-action.php?content=controller/CommandAC.php');
        }else{
            $('#formListaInscritos').attr('action', '<?php echo WB_URL_DOMINIO . '/' . WB_GRUPO.'/'.COD_EVENTO ?>/inscricoes/index-action.php?content=controller/CommandAC.php');
        }
        $('#c').val('ListarInscritos');
        $('#m').val('carregarEditalHtml');
        $("input[name=media]").val(['xls']);
        $('#formListaInscritos').submit();
    });
    $('#excluirInscritoOficina').click(function(){


    });
    $('#listarTela').click(function(){
         submitConsulta();
    });

    $(".btn-enviar-certificados").click(function(event){
        event.preventDefault();
        var form = $(this).closest("form");
        enviarFormulario(form);
    });
    $(".btn-lembrar-mensalidade").click(function(event){
        event.preventDefault();
        var form = $(this).closest("form");
        enviarFormulario(form);
    });

    $(".btn-enviar-senha-admin").click(function(event){
        event.preventDefault();
        var form = $(this).closest("form");
        enviarFormulario(form);
    });
    $(".btn-lembrar-pagamento").click(function(event){
        event.preventDefault();
        var form = $(this).closest("form");
        enviarFormulario(form);
    });
    $(".btn-enviar-link-acesso").click(function(event){
        event.preventDefault();
        var form = $(this).closest("form");
        enviarFormulario(form);
    });
    $(".btn-lembrar-envio-trabalho").click(function(event){
        event.preventDefault();
        var form = $(this).closest("form");
        enviarFormulario(form);
    });

    // Chama a função verificarAcao quando a ação em lote é alterada
    $('#acaoEmLote').change(verificarAcao);
    verificarAcao();
    $('#selectTaxas').change(function() {
        if ($(this).val() == '-1') {
            $('#camposExtras').show();
        } else {
            $('#camposExtras').hide();
        }
    });    
    $('#comunicado').change(function() {
        if($('#comunicado').val()!=''){
            carregarEmailTemplate($('#comunicado').val());
        }
    });    
});
// Função para verificar os checkboxes e mostrar a div correspondente
function carregarEmailTemplate(codEmailTemplate)
{
   $.ajax({
       type: "POST",
       url: '<?php echo  WB_URL_COMMAND ?>',
       data: ({'codEmailTemplate': codEmailTemplate, 'c': 'GerenciarEmailTemplate', 'm': 'carregarEmailTemplate', '_submit_check': '1'}),
       dataType: "json",
       async: false,
       cache: false
       , success: function (data) {
           _atualizarPreviewTemplate(data.assunto, data.corpo);
       }
       , error: function () {
           alert("Nao foi possivel obter informações:\n" + req.statusText);
       }
   });
}
function verificarAcao() {
    // Esconde as divs primeiro
    $('#divSelectComunicados, #divSelectTaxas, #divAlterarVencimento, #divStatusProcesso, #divAcaoSemFormulario').hide();

    // Mostra so o painel da ação escolhida no select
    var valorAcao = $('#acaoEmLote').val();

    if (!valorAcao) {
        $('#tituloAcaoLote').hide();
        return;
    }

    var cartao = null;
    if(valorAcao === 'enviarComunicados' || valorAcao === 'enviarComunicadoWhats') {
        cartao = $('#divSelectComunicados');
    } else if(valorAcao === 'gerarMensalidade') {
        cartao = $('#divSelectTaxas');
    } else if(valorAcao === 'alterarVencimento') {
        cartao = $('#divAlterarVencimento');
    } else if(valorAcao === 'alterarStatusProcesso') {
        cartao = $('#divStatusProcesso');
    } else {
        // definir presenca, gerar certificado: nao tem formulario, mas precisa dar
        // retorno de que existe uma acao armada
        cartao = $('#divAcaoSemFormulario');
    }

    // o titulo vira o CABECALHO do cartao -- por isso e movido para dentro dele
    $('#tituloAcaoLoteTexto').text($('#acaoEmLote option:selected').text().trim());
    cartao.prepend($('#tituloAcaoLote'));
    $('#tituloAcaoLote').show();
    cartao.show();
}
function acessarAvaliacaoInscrito(codInscrito) {
    window.open('<?php echo WB_URL_DOMINIO.'/'.WB_GRUPO.'/'.COD_EVENTO ?>/inscricoes/index.php?content=view/admin/GerenciarAvaliacaoInscrito.php&codInscrito='+codInscrito, '_blank');
}
function submitConsulta(){
    var codEvento =  $('#cod_evento').val();
    if(codEvento!='-1'){
        $('#formListaInscritos').attr('action', '<?php echo WB_URL_DOMINIO.'/'.WB_GRUPO ?>/'+codEvento+'/inscricoes/index.php?content=view/admin/consulta-dados-congresso-listagem-inscritos.php');
    }else{
        $('#formListaInscritos').attr('action', '<?php echo WB_URL_DOMINIO.'/'.WB_GRUPO.'/'.COD_EVENTO ?>/inscricoes/index.php?content=view/admin/consulta-dados-congresso-listagem-inscritos.php');
    }
    $('#c').val('');
    $('#m').val('');
    $("input[name=media]").val(['tela']);
    $('#formListaInscritos').submit();
}
function carregarCidades(cod, selecionado)
{
   $.ajax({
       type: "POST",
       url: '<?php echo  WB_URL_COMMAND ?>',
       data: ({'uf': cod, 'c': 'CarregarOptions', 'm': 'cidades', '_submit_check': '1'}),
       dataType: "html",
       async: false,
       cache: false
       , success: function (data) {
           $("#cidade").html(data);
       }
       , error: function () {
           alert("Nao foi possivel obter informações:\n" + req.statusText);
       }
   });
}
// --- TinyMCE para email_template_body (textarea de envio) ---
// Botão editar template → abre modal compartilhado
function abrirModalEditarTemplate() {
    var codTemplate = $('#comunicado').val();
    if (!codTemplate) {
        alert('Selecione um template primeiro.');
        return;
    }
    mdtEditarTemplate(codTemplate);
}

// Atualiza preview visual do template selecionado
// Abre o e-mail completo em modo LEITURA -- serve para quem pode enviar mas nao
// pode editar (a previa curta nao basta para conferir o que vai ser disparado).
// Usa a mesma rota da previa (carregarEmailTemplate), liberada para leitura.
function verTemplateCompleto() {
    var cod = $('#comunicado').val();
    if (!cod || cod === 'novoModelo') {
        alert('Selecione um template para visualizar.');
        return;
    }
    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>',
        data: {'codEmailTemplate': cod, 'c': 'GerenciarEmailTemplate',
               'm': 'carregarEmailTemplate', '_submit_check': '1'},
        dataType: "json",
        cache: false,
        success: function(data) {
            if (!data || !data.statusMessage) {
                alert('Não foi possível carregar o conteúdo deste template.');
                return;
            }
            $('#verTemplateAssunto').text(data.assunto || '');
            $('#verTemplateDescricao').text(data.descricao || '');
            // iframe com sandbox: o CSS do e-mail nao vaza para a tela e nada
            // do corpo executa aqui dentro
            $('#verTemplateCorpo').attr('srcdoc',
                '<div style="font-family:Arial,Helvetica,sans-serif;font-size:14px;color:#333;">'
                + (data.corpo || '<i>(sem conteúdo)</i>') + '</div>');
            $('#modalVerTemplate').modal('show');
        },
        error: function() {
            alert('Erro ao carregar o template.');
        }
    });
}

function _atualizarPreviewTemplate(assunto, corpo) {
    if (assunto) {
        var corpoTexto = $('<div>').html(corpo).text();
        var resumo = corpoTexto.length > 200 ? corpoTexto.substring(0, 200) + '...' : corpoTexto;
        $('#previewAssunto').text(assunto);
        $('#previewCorpo').text(resumo);
        $('#previewTemplate').show();
    } else {
        $('#previewTemplate').hide();
    }
}

// Recarrega o select de templates via AJAX e seleciona o template indicado
function _recarregarSelectTemplates(codSelecionar) {
    $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), {
        'c': 'GerenciarEmailTemplate',
        'm': 'carregarOptionsEmailTemplate',
        '_submit_check': '1'
    }, function(html) {
        $('#comunicado').html('<option value="">Selecione um modelo</option>' + html);
        if (codSelecionar) {
            $('#comunicado').val(codSelecionar);
        }
    });
}

// Callback: ao salvar template no modal, atualiza select e preview
$(document).ready(function() {
    _mdtOnSalvo = function(resultado) {
        _recarregarSelectTemplates(resultado.codEmailTemplate);
        _atualizarPreviewTemplate(resultado.assunto, resultado.corpo);
    };
});

function limparFiltroSalvoAplicado(){
    document.getElementById('filtro_salvo_aplicado').value = '';
    document.getElementById('filtroSalvoSelect').selectedIndex = 0;
}
function limparCampos(){
    limparFiltroSalvoAplicado();
    //Limpando campos
    $('#cod_evento_vinculado').val('');
    $('#nome_inscrito').val('');
    $('#cod_inscrito').val('');
    $('#cod_trabalho').val('');
    $('#e_mail').val('');
    $('#cpf').val('');
    $('#fone_contato').val('');
    $('#cod_perfil').each(function(){ for(var i=0;i<this.options.length;i++) this.options[i].selected=false; });
    $('#tipo_inscricao').val('');
    $('#cod_oficina').val('');
    $('#cod_area_tematica').val('');  
    $('#cod_macrorregiao').val('');  
    $('#estado').val('');  
    $('#cidade').val('');  
    $('#data_pagamento_ini').val('');  
    $('#data_pagamento_fim').val('');  
    $('#data_pagamento_incl_ini').val('');  
    $('#data_pagamento_incl_fim').val('');  
    $('#data_inscricao_ini').val('');  
    $('#data_inscricao_fim').val('');  
    $('#data_vencimento_ini').val('');  
    $('#data_vencimento_fim').val('');  

    $('#datVencimento').val('');  
    $('#tipoPedido').val('');  
    $('#prefixoPedido').val('');  
    $('#valorPedido').val('');  
    $('#pedidos').val('');  
    $('#pedidosQtdAte').val('');  
    $('#transacao_id').val('');  
    $('#pedido_id').val('');  
    $('#id_publico').val('');  
    
    $("input[name=situacao]").val(['ATIVADA']);
    $("input[name=status_processo]").val(['']);
    $("input[name=presenca_inscrito]").val(['']);
    $("input[name=solicitaram_recurso]").val(['']);

    $("input[name=registro_atividade_status]").val(['']);
    $("input[name=periodo_apuracao_reg_ativ_ini]").val(['']);
    $("input[name=periodo_apuracao_reg_ativ_fim]").val(['']);

    $("input[name=avaliacao_inscrito]").val(['']);
    $("input[name=intervalo_registros]").val(['']);
    $("input[name=limite_registros]").val(['']);
    $("input[name=outros_filtros_inscrito]").val(['']);
    $("input[name=almoco]").val(['']);
    $("input[name=pagamentos]").val(['']);
    $("input[name=pagamentosQtdAte]").val(['']);
    $("input[name=pagamentosQtdMaior]").val(['']);
    $("input[name=pagamentosQtdMenor]").val(['']);
    $("input[name=xml_pagamentos]").val(['']);
    $("input[name=xml_pagamentos_carregados]").val(['']);
    $("input[name=tipo_perfil]").val(['']);
    $("input[name=tipo_pedido]").val(['']);
    $("input[name=forma_pagamento]").val(['']);
    $("input[name=trabalhos]").val(['']);
    // $("input[name=contratos]").val(['']);
    $("input[name=contrato_anexo]").val(['']);
    $("input[name=docs_contratacao]").val(['']);
    $("input[name='doc_contratacao_falta[]']").each(function(){ this.checked = false; });
    $("input[name=classificacao_ate]").val('');
    $("input[name=rpa_anexo]").val(['']);
    $("input[name=comp_transf_anexo]").val(['']);
    $("input[name=termo_desist_anexo]").val(['']);
    $("input[name=oficinas]").val(['']);
    $("input[name=perspectiva]").val(['simplificada']);
    $("input[name=media]").val(['tela']);
    $("input[name=ordenar]").val(['cod_inscrito']);
    $('#formListaInscritos').attr('action', '<?php echo WB_URL_VIEW?>?content=view/admin/consulta-dados-congresso-listagem-inscritos.php');
    $('#c').val('');
    $('#m').val('');
    atualizarTagsFiltrosAtivos();
}
function gerarfnEtiqueta(codInscrito){
//        $('#tipoEtiqueta').val(tipoEtiqueta);
    $('#codInscrito').val(codInscrito);
    $('#gerarEtiqueta').submit();
}  

function alternarPresencaOficina(codInscrito,codOficina){
    //if(confirm('Deseja definir presença para o inscrito: '+codInscrito+' na atividade: '+codOficina+'?')){
        var id_presenca= 'presencaOficina-'+codInscrito+'-'+codOficina;
        $.ajax({
            type: "POST",
            url: '<?php echo  WB_URL_COMMAND?>&t='+Math.random(),
            data: ({'cod_inscrito':codInscrito,'cod_oficina':codOficina, 'c':'ListarInscritos', 'm':'alternarPresencaOficina'}),
            dataType: "json",
            async: false,
            cache: false,
            success: function(data){
                $('#'+id_presenca).attr("checked", data.flgApresentado); 
                elementMsg = '#statusPresencaOficina-'+codInscrito+'-'+codOficina
                processJsonElement(data,elementMsg);
            }
            ,timeout: 4000
            ,beforeSend: function() {
                $('presencaOficina-'+codInscrito+'-'+codOficina).hide();
                loading('div#carregando-'+codInscrito,'show');
            }
            ,error: function(ajax) {
                //showResponse(ajax);
            }
            ,complete: function() {        
                $('#presencaOficina-'+codInscrito+'-'+codOficina).show();
                loading('div#carregando-'+codInscrito,'hidden');
            }
        });
    //}
}
function excluirInscritoOficina(codInscrito,codOficina){
    if(confirm('Deseja excluir inscrito: '+codInscrito+' da oficina: '+codOficina+'?')){
        var id_presenca= 'presencaAtividade-'+codOficina;
        $.ajax({
            type: "POST",
            url: '<?php echo  WB_URL_COMMAND?>&t='+Math.random(),
            data: ({'cod_inscrito':codInscrito,'cod_oficina':codOficina, 'c':'ListarInscritos', 'm':'excluirInscritoOficina'}),
            dataType: "json",
            async: false,
            cache: false,
            success: function(data){
                if(data.statusMessage==1){
//                    $('#'+codInscrito).hide();
                    $('#btnExcluirAtiv-'+codInscrito+'-'+codOficina).hide();
                }
                elementMsg = '#msgInscrito-'+codInscrito
                processJsonElement(data,elementMsg);
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
    }
}

function excluirIsento(codInscrito){
    codIsento = $('#codInscritoIsento-'+codInscrito).val();
    if(confirm('Deseja excluir inscrito: '+codInscrito+' da lista de isentos?')){
        $.ajax({
            type: "POST",
            url: '<?php echo  WB_URL_COMMAND?>&t='+Math.random(),
            data: ({'codInscrito':codInscrito,'codIsento':codIsento, 'c':'GerenciarIsento', 'm':'excluirIsento'}),
            dataType: "json",
            cache: false,
            success: function(data){
                elementMsg = '#msgInscrito-'+codInscrito
                processJsonElement(data,elementMsg);                
                if(data.statusMessage==1){
                    $('#txtInscritoIsento-'+codInscrito).html('');
                    $('#btnExcluirIsento-'+codInscrito).removeClass('show').addClass('hide');
                    $('#codInscritoIsento-'+codInscrito).val('');
                    $('#btnAdicionarIsento-'+codInscrito).removeClass('hide').addClass('show');
                }else{
                    $('#btnExcluirIsento-'+codInscrito).removeClass('hide').addClass('show')
                    $('#btnAdicionarIsento-'+codInscrito).removeClass('show').addClass('hide');
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
    }
}
function adicionarIsento(codInscrito,nomeInscrito, cpf, eMail){
    if(confirm('Deseja adicionar inscrito: '+nomeInscrito+' à lista de isentos?')){
        var departamento = prompt("Informe departamento que esta abonando a inscrição.");
        var organizacao = prompt("Informe organização que esta abonando a inscrição.");
        var cpf_cnpj_abonador = prompt("Informe o cpf ou cnpj do órgão que esta abonando a inscrição.");
        $.ajax({
            type: "POST",
            url: '<?php echo  WB_URL_COMMAND?>&t='+Math.random(),
            data: ({'nomeIsento':nomeInscrito,'codInscrito':codInscrito,'departamento':departamento,'organizacao':organizacao,'cpfCnpjAbonador':cpf_cnpj_abonador,'cpf':cpf,'email':eMail, 'c':'GerenciarIsento', 'm':'salvarIsento'}),
            dataType: "json",
            async: false,
            cache: false,
            success: function(data){
                elementMsg = '#msgInscrito-'+codInscrito
                processJsonElement(data,elementMsg);
                if(data.statusMessage==1){
                    $('#btnExcluirIsento-'+codInscrito).removeClass('hide').addClass('show');
                    $('#txtInscritoIsento-'+codInscrito).html('Cód.Isento: '+data.codIsento+'');
                    $('#codInscritoIsento-'+codInscrito).val(data.codIsento);
                    $('#btnAdicionarIsento-'+codInscrito).removeClass('show').addClass('hide');
                }else{
                    $('#btnExcluirIsento-'+codInscrito).removeClass('show').addClass('hide');
                    $('#btnAdicionarIsento-'+codInscrito).removeClass('hide').addClass('show');
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
    }
}
function excluirInscritoVoucher(codInscrito){
    var codVoucher=$('#codInscritoVoucher-'+codInscrito).val();
    if(confirm('Deseja excluir voucher '+codVoucher+' do inscrito: '+codInscrito+'?')){
        $.ajax({
            type: "POST",
            url: '<?php echo  WB_URL_COMMAND?>&t='+Math.random(),
            data: ({'codInscrito':codInscrito,'codVoucher':codVoucher, 'c':'GerenciarInscritoVoucher', 'm':'excluirInscritoVoucher'}),
            dataType: "json",
            cache: false,
            success: function(data){
                elementMsg = '#msgInscrito-'+codInscrito
                processJsonElement(data,elementMsg);                
                if(data.statusMessage==1){
                    $('#txtInscritoVoucher-'+codInscrito).html('');
                    $('#btnExcluirInscritoVoucher-'+codInscrito).removeClass('show').addClass('hide');
                    $('#codInscritoVoucher-'+codInscrito).val('');
                    $('#btnAdicionarInscritoVoucher-'+codInscrito).removeClass('hide').addClass('show');
                }else{
                    $('#btnExcluirInscritoVoucher-'+codInscrito).removeClass('hide').addClass('show')
                    $('#btnAdicionarInscritoVoucher-'+codInscrito).removeClass('show').addClass('hide');
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
    }
}
function adicionarInscritoVoucher(codInscrito){
    var codVoucher = prompt("Informe o código do voucher.");
    if(codVoucher!=null){   
//        if(confirm('Voucher digitado: ' + codVoucher)){ }else{ return false } 
        $.ajax({
            type: "POST",
            url: '<?php echo  WB_URL_COMMAND?>&t='+Math.random(),
            data: ({'codInscrito':codInscrito,'codVoucher':codVoucher, 'c':'GerenciarInscritoVoucher', 'm':'adicionarInscritoVoucher'}),
            dataType: "json",
            async: false,
            cache: false,
            success: function(data){
                elementMsg = '#msgInscrito-'+codInscrito
                processJsonElement(data,elementMsg);
                if(data.statusMessage==1){
                    $('#btnExcluirInscritoVoucher-'+codInscrito).removeClass('hide').addClass('show');
                    $('#txtInscritoVoucher-'+codInscrito).html('Cód.Voucher: '+codVoucher+'');
                    $('#codInscritoVoucher-'+codInscrito).val(codVoucher);
                    $('#btnAdicionarInscritoVoucher-'+codInscrito).removeClass('show').addClass('hide');
                }else{
                    $('#btnExcluirInscritoVoucher-'+codInscrito).removeClass('show').addClass('hide');
                    $('#btnAdicionarInscritoVoucher-'+codInscrito).removeClass('hide').addClass('show');
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
    }
}
function alternarPresencaInscrito(codInscrito){
    //if(confirm('Deseja definir presença para o inscrito: '+codInscrito+'?')){
        var id_presenca= 'presencaInscrito-'+codInscrito;
        $.ajax({
            type: "POST"
            ,url: '<?php echo  WB_URL_COMMAND?>&t='+Math.random()
            ,data: ({'cod_inscrito':codInscrito, 'c':'ListarInscritos', 'm':'alternarPresencaInscrito'})
            ,dataType: "json"
            ,async: false
            ,cache: false
            ,timeout: 4000
            ,beforeSend: function() {
                $('presencaInscrito-'+codInscrito).hide();
                loading('div#carregando-'+codInscrito,'show');
            }
            ,error: function(ajax) {
                //showResponse(ajax);
            }                
            ,success: function(data){
                if(data.flgPresencaInscrito!=""){
                    $('#'+id_presenca).attr("checked", 1); 
                }else{
                    $('#'+id_presenca).attr("checked", 0); 
                }
                elementMsg = '#statusPresencaInscrito-'+codInscrito
                processJsonElementLeve(data,elementMsg);
            }
            ,complete: function() {        
                $('#presenca-'+codInscrito).show();
                loading('div#carregando-'+codInscrito,'hidden');
            }
        });
    //}
}
function alterarStatusProcesso(codInscrito){
    var selectEl = $('#statusProcesso-'+codInscrito);
    var statusNovo = selectEl.val();
    var statusTexto = selectEl.find('option:selected').text();
    var statusAnterior = selectEl.attr('data-status-anterior');
    if(!confirm('Deseja alterar o status do inscrito '+codInscrito+' para '+statusTexto+'?')){
        selectEl.val(statusAnterior);
        return;
    }
    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND?>&t='+Math.random(),
        data: {'cod_inscrito':codInscrito, 'statusProcessoNovo':statusNovo, 'c':'ListarInscritos', 'm':'alterarStatusProcesso'},
        dataType: "json",
        cache: false,
        timeout: 4000,
        beforeSend: function() {
            loading('div#carregando-'+codInscrito,'show');
        },
        success: function(data){
            var elementMsg = '#msgStatusProcesso-'+codInscrito;
            processJsonElement(data, elementMsg);
            if(data.statusMessage == '1'){
                selectEl.attr('data-status-anterior', statusNovo);
            } else {
                selectEl.val(statusAnterior);
            }
        },
        error: function(ajax) {
            selectEl.val(statusAnterior);
        },
        complete: function() {
            loading('div#carregando-'+codInscrito,'hidden');
        }
    });
}
function alterarStatusInscricao(codInscrito, acao, valorPago){
    if(!confirm('Deseja '+acao+' a inscrição '+codInscrito+'?')){
        return;
    }
    // Inscricao PAGA: o aviso ao financeiro e OPCIONAL e quem decide e quem
    // esta cancelando. Recusar aqui NAO cancela a operacao — so nao notifica.
    valorPago = parseFloat(valorPago || 0);
    var notificarFinanceiro = 0;
    if(acao !== 'Ativar' && valorPago > 0){
        notificarFinanceiro = confirm('Esta inscrição tem pagamento de R$ '
            + valorPago.toFixed(2).replace('.', ',')
            + '.\n\nDeseja notificar o financeiro do gestor sobre o cancelamento'
            + ' (estorno/multa)?\n\n'
            + 'OK = notificar por e-mail\n'
            + 'Cancelar = não notificar (a inscrição será ' + acao.toLowerCase() + 'da assim mesmo)') ? 1 : 0;
    }
    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND?>&t='+Math.random(),
        data: {'cod_inscrito':codInscrito, 'acao':acao, 'notificar_financeiro':notificarFinanceiro,
               'c':'ListarInscritos', 'm':'alterarStatusInscricao'},
        dataType: "json",
        cache: false,
        timeout: 4000,
        beforeSend: function() {
            loading('div#carregando-'+codInscrito,'show');
        },
        success: function(data){
            var elementMsg = '#msgInscrito-'+codInscrito;
            processJsonElement(data, elementMsg);
            if(data.statusMessage == '1' && data.novoStatus){
                var spanStatus = $('#statusInscricao-'+codInscrito);
                var cor = (data.novoStatus == 'ativada') ? 'green' : 'red';
                spanStatus.css('color', cor);
                spanStatus.text(codInscrito + ' - ' + data.novoStatus);
            }
        },
        error: function(ajax) {
        },
        complete: function() {
            loading('div#carregando-'+codInscrito,'hidden');
        }
    });
}
function associarInscritoPerfilAdministrativo(codInscrito,codPerfil){
    var id_input_perfil= 'associar-perfil-'+codPerfil+'-'+codInscrito;
//    var acao = $(id_input_perfil).prop("checked");
    var checkBox = document.getElementById(id_input_perfil);
    if (checkBox) {
        var acao = checkBox.checked;
        if (acao) {
            console.log("Checkbox marcado");
        } else {
            console.log("Checkbox desmarcado");
        }
    } else {
        console.error("Elemento com o ID '" + id_input_perfil + "' não encontrado.");
    }
        
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
function enviarFormulario(form) {
    var formData = form.serialize();
    var codInscrito = form.find("[name='codInscrito']").val();

    $.ajax({
        type: "POST",
        url: form.attr("action"),
        data: formData,
        dataType: "json",
        async: false,
        cache: false,
        success: function(data){
            var elementMsg = '#msgInscrito-' + codInscrito;
            processJsonElement(data, elementMsg);
        },
        timeout: 4000,
        beforeSend: function() {
            loading('div#carregando-' + codInscrito, 'show');
        },
        error: function(ajax) {
            // Tratamento de erro
        },
        complete: function() {
            loading('div#carregando-' + codInscrito, 'hidden');
        }
    });
}
function atualizarTagsFiltrosAtivos(){
    var form = document.getElementById('formListaInscritos');
    // Helpers para ler valores do form
    function val(name){
        var el = form.querySelector('[name="'+name+'"]');
        // Tentar também com [] para campos array (ex: cod_perfil[])
        if(!el) el = form.querySelector('[name="'+name+'[]"]');
        if(!el) return '';
        if(el.tagName=='SELECT' && el.multiple){
            // Para select multiple, retornar valores selecionados
            var sel = [];
            for(var i=0; i<el.options.length; i++){
                if(el.options[i].selected && el.options[i].value) sel.push(el.options[i].value);
            }
            return sel.length ? sel.join(',') : '';
        }
        if(el.tagName=='SELECT') return el.value||'';
        return el.value||'';
    }
    function radioVal(name){
        var els = form.querySelectorAll('[name="'+name+'"]');
        for(var i=0;i<els.length;i++){ if(els[i].checked) return els[i].value; }
        return '';
    }
    function checkedVals(name){
        var r=[], els=form.querySelectorAll('[name="'+name+'"]');
        for(var i=0;i<els.length;i++){ if(els[i].checked) r.push(els[i].value); }
        return r;
    }
    function esc(s){ var d=document.createElement('span'); d.textContent=s; return d.innerHTML; }

    var tags = [];
    // Busca Rápida
    if(val('nome_inscrito')) tags.push({label:'Nome: '+esc(val('nome_inscrito')), campos:'nome_inscrito'});
    if(val('cpf')) tags.push({label:'CPF: '+esc(val('cpf')), campos:'cpf'});
    if(val('e_mail')) tags.push({label:'E-mail: '+esc(val('e_mail')), campos:'e_mail'});
    if(val('cod_inscrito')) tags.push({label:'Cód.Inscrito: '+val('cod_inscrito'), campos:'cod_inscrito'});
    if(val('cod_trabalho')) tags.push({label:'Cód.Trabalho: '+val('cod_trabalho'), campos:'cod_trabalho'});
    if(val('fone_contato')) tags.push({label:'Fone: '+val('fone_contato'), campos:'fone_contato'});
    if(val('id_publico')) tags.push({label:'Id Público: '+val('id_publico'), campos:'id_publico'});
    if(val('pedido_id')) tags.push({label:'Pedido: '+val('pedido_id'), campos:'pedido_id'});
    if(val('transacao_id')) tags.push({label:'Transação: '+val('transacao_id'), campos:'transacao_id'});
    // Perfil e Localização
    if(radioVal('tipo_perfil')) tags.push({label:'Tipo Perfil: '+radioVal('tipo_perfil'), campos:'tipo_perfil'});
    if(val('cod_perfil')){
        var qtdPerfis = val('cod_perfil').split(',').length;
        tags.push({label:'Perfil: '+qtdPerfis+' selecionado(s)', campos:'cod_perfil'});
    }
    if(val('tipo_inscricao')) tags.push({label:'Categoria selecionada', campos:'tipo_inscricao'});
    if(val('cod_oficina')) tags.push({label:'Atividade selecionada', campos:'cod_oficina'});
    if(val('cod_area_tematica')) tags.push({label:'Área Temática selecionada', campos:'cod_area_tematica'});
    if(val('cod_macrorregiao')) tags.push({label:'Macrorregião selecionada', campos:'cod_macrorregiao'});
    if(val('patrocinador_voucher')) tags.push({label:'Voucher selecionado', campos:'patrocinador_voucher'});
    if(val('estado')) tags.push({label:'Estado: '+val('estado'), campos:'estado,cidade'});
    if(val('cidade')) tags.push({label:'Cidade: '+esc(val('cidade')), campos:'cidade'});
    // Situação
    var sit = radioVal('situacao');
    if(sit && sit!='ATIVADA') tags.push({label:'Situação: '+sit, campos:'situacao'});
    if(radioVal('status_processo')) tags.push({label:'Status Processo: '+radioVal('status_processo'), campos:'status_processo'});
    if(radioVal('presenca_inscrito')) tags.push({label:'Presença: '+radioVal('presenca_inscrito'), campos:'presenca_inscrito'});
    if(radioVal('intervalo_registros')) tags.push({label:'Intervalo: '+radioVal('intervalo_registros'), campos:'intervalo_registros'});
    if(val('limite_registros')) tags.push({label:'Qtd.Registros: '+val('limite_registros'), campos:'limite_registros'});
    if(val('data_inscricao_ini')||val('data_inscricao_fim')) tags.push({label:'Dt.Cadastro: '+val('data_inscricao_ini')+' - '+val('data_inscricao_fim'), campos:'data_inscricao_ini,data_inscricao_fim'});
    if(val('data_pagamento_ini')||val('data_pagamento_fim')) tags.push({label:'Dt.Pagamento: '+val('data_pagamento_ini')+' - '+val('data_pagamento_fim'), campos:'data_pagamento_ini,data_pagamento_fim'});
    // Financeiro
    if(radioVal('pagamentos')) tags.push({label:'Pagamentos: '+radioVal('pagamentos'), campos:'pagamentos'});
    if(radioVal('almoco')) tags.push({label:'Refeição: '+radioVal('almoco'), campos:'almoco'});
    // Documentos e Avaliação
    if(radioVal('trabalhos')) tags.push({label:'Trabalhos: '+radioVal('trabalhos'), campos:'trabalhos'});
    var ca=radioVal('contrato_anexo');
    if(ca) tags.push({label:'Contrato: '+(ca=='1'?'Anexado':'Não anexado'), campos:'contrato_anexo'});
    var rpa=radioVal('rpa_anexo');
    if(rpa) tags.push({label:'RPA: '+(rpa=='1'?'Anexado':'Não anexado'), campos:'rpa_anexo'});
    if(radioVal('avaliacao_inscrito')) tags.push({label:'Avaliação: '+radioVal('avaliacao_inscrito'), campos:'avaliacao_inscrito'});
    if(radioVal('docs_contratacao')) tags.push({label:'Docs Contratação: '+radioVal('docs_contratacao'), campos:'docs_contratacao'});
    var docFalta = checkedVals('doc_contratacao_falta[]');
    if(docFalta.length) tags.push({label:'Falta Doc: '+docFalta.join(', '), campos:'doc_contratacao_falta[]'});
    if(val('classificacao_ate')) tags.push({label:'Classificação final até: '+val('classificacao_ate')+'º', campos:'classificacao_ate'});
    if(radioVal('solicitaram_recurso')) tags.push({label:'Recurso: '+radioVal('solicitaram_recurso'), campos:'solicitaram_recurso'});
    // Visualização
    var persp=radioVal('perspectiva');
    if(persp && persp!='simplificada') tags.push({label:'Perspectiva: '+persp, campos:'perspectiva'});
    var ord=radioVal('ordenar');
    if(ord && ord!='cod_inscrito') tags.push({label:'Ordenar: '+ord, campos:'ordenar'});

    // Montar HTML das tags
    var divTags = document.getElementById('divFiltrosAtivosTags');
    // Preservar os botões (Salvar definição de filtro + inline)
    var btnSalvar = document.getElementById('btnAbrirSalvarFiltro');
    var spanInline = document.getElementById('spanSalvarFiltroInline');
    // Limpar apenas as tags (spans após o <br>)
    var existentes = divTags.querySelectorAll('.filtro-ativo-tag, .filtro-salvo-tag');
    for(var i=0;i<existentes.length;i++) existentes[i].parentNode.removeChild(existentes[i]);

    // Tag do filtro salvo aplicado
    var filtroSalvoEl = document.getElementById('filtro_salvo_aplicado');
    var selFiltro = document.getElementById('filtroSalvoSelect');
    if(filtroSalvoEl && filtroSalvoEl.value && selFiltro){
        var opt = selFiltro.querySelector('option[value="'+filtroSalvoEl.value+'"]');
        if(opt){
            var spanFS = document.createElement('span');
            spanFS.className = 'filtro-salvo-tag';
            spanFS.style.cssText = 'display:inline-block; background:#b8e6c8; border-radius:3px; padding:2px 8px; margin:2px 3px; white-space:nowrap; font-weight:bold;';
            spanFS.title = 'Filtro salvo aplicado';
            spanFS.innerHTML = '&#9733; '+esc(opt.textContent.trim());
            divTags.appendChild(spanFS);
        }
    }

    for(var i=0;i<tags.length;i++){
        var span = document.createElement('span');
        span.className = 'filtro-ativo-tag';
        span.title = 'Clique para remover';
        span.style.cssText = 'display:inline-block; background:#dce6f7; border-radius:3px; padding:2px 8px; margin:2px 3px; white-space:nowrap; cursor:pointer;';
        span.setAttribute('data-campos', tags[i].campos);
        span.innerHTML = tags[i].label + ' <span style="color:#c00; font-weight:bold; margin-left:4px;">&times;</span>';
        (function(campos, el){
            el.onclick = function(){ removerFiltroAtivo(campos, el); };
        })(tags[i].campos, span);
        divTags.appendChild(span);
    }

    // Mostrar/esconder a div e ajustar separador visual
    var divMeus = document.getElementById('divMeusFiltros');
    if(tags.length > 0 || (filtroSalvoEl && filtroSalvoEl.value)){
        divTags.style.display = '';
        btnSalvar.style.display = '';
        if(divMeus){ divMeus.style.borderTop='1px solid #b8cce4'; divMeus.style.paddingTop='6px'; }
    } else {
        divTags.style.display = 'none';
        if(divMeus){ divMeus.style.borderTop='none'; divMeus.style.paddingTop='0'; }
    }
    return true;
}
function removerFiltroAtivo(camposStr, tagEl){
    limparFiltroSalvoAplicado();
    var campos = camposStr.split(',');
    for(var i=0; i<campos.length; i++){
        var nome = campos[i].trim();
        var elCampos = document.querySelectorAll('#formListaInscritos [name="'+nome+'"]');
        // Tentar também com [] para campos array (ex: cod_perfil[])
        if(elCampos.length==0) elCampos = document.querySelectorAll('#formListaInscritos [name="'+nome+'[]"]');
        if(elCampos.length==0) continue;
        var tipo = elCampos[0].type;
        if(tipo=='radio'){
            // Marca o radio com valor vazio (padrão "Todos"), se existir
            var encontrou = false;
            for(var j=0; j<elCampos.length; j++){
                if(elCampos[j].value==''){
                    elCampos[j].checked = true;
                    encontrou = true;
                } else {
                    elCampos[j].checked = false;
                }
            }
        } else if(tipo=='checkbox'){
            for(var j=0; j<elCampos.length; j++){
                elCampos[j].checked = false;
            }
        } else {
            // input text, select, hidden, textarea
            for(var j=0; j<elCampos.length; j++){
                if(elCampos[j].tagName=='SELECT'){
                    if(elCampos[j].multiple){
                        // Desmarcar todas as opções do select multiple
                        for(var k=0; k<elCampos[j].options.length; k++){
                            elCampos[j].options[k].selected = false;
                        }
                    } else {
                        elCampos[j].selectedIndex = 0;
                    }
                } else {
                    elCampos[j].value = '';
                }
            }
        }
    }
    // Recalcula as tags a partir do estado atual do form
    atualizarTagsFiltrosAtivos();
}
function abrirSalvarFiltro(){
    document.getElementById('btnAbrirSalvarFiltro').style.display='none';
    document.getElementById('spanSalvarFiltroInline').style.display='inline';
    var inp = document.getElementById('inputNomeFiltroSalvar');
    inp.value='';
    inp.focus();
}
function fecharSalvarFiltro(){
    document.getElementById('spanSalvarFiltroInline').style.display='none';
    document.getElementById('btnAbrirSalvarFiltro').style.display='';
}
function confirmarSalvarFiltro(){
    var inp = document.getElementById('inputNomeFiltroSalvar');
    var nomFiltro = inp.value.trim();
    if(!nomFiltro){ inp.style.borderColor='#c00'; inp.focus(); return; }
    inp.style.borderColor='#999';
    // Verifica se já existe filtro com mesmo nome
    var existente = null;
    $('#filtroSalvoSelect option').each(function(){
        if($(this).text().trim().toLowerCase() == nomFiltro.toLowerCase() && $(this).val()!=''){
            existente = $(this);
            return false;
        }
    });
    if(existente){
        if(!confirm('Já existe um filtro com o nome "'+nomFiltro+'". Deseja substituí-lo?')) return;
        var codAntigo = existente.val();
        $.ajax({
            type:"POST", url:'<?php echo WB_URL_COMMAND?>&t='+Math.random(),
            data:{c:'GerenciarFiltroSalvo', m:'excluir', cod_filtro_salvo:codAntigo},
            dataType:"json", cache:false, async:false
        });
        existente.remove();
    }
    // Serialização vanilla JS — captura todos os tipos de input incluindo type="number"
    var campos = [];
    var form = document.getElementById('formListaInscritos');
    var els = form.elements;
    for(var i=0; i<els.length; i++){
        var el = els[i];
        if(!el.name || el.disabled) continue;
        if(el.type=='checkbox' || el.type=='radio'){
            if(!el.checked) continue;
        }
        if(el.tagName=='SELECT' && el.multiple){
            for(var j=0; j<el.options.length; j++){
                if(el.options[j].selected) campos.push({name:el.name, value:el.options[j].value});
            }
        } else {
            campos.push({name:el.name, value:el.value});
        }
    }
    var datFiltro = JSON.stringify(campos);
    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND?>&t='+Math.random(),
        data: {c:'GerenciarFiltroSalvo', m:'salvar', nom_filtro:nomFiltro, dat_filtro:datFiltro, cod_evento:'<?php echo COD_EVENTO?>'},
        dataType: "json",
        cache: false,
        success: function(data){
            if(data.statusMessage=='1'){
                $('#filtroSalvoSelect').append('<option value="'+data.codFiltroSalvo+'">'+$('<span>').text(data.nomFiltro).html()+'</option>');
                $('#filtroSalvoSelect').val(data.codFiltroSalvo);
                fecharSalvarFiltro();
                // Feedback visual discreto
                var btn = document.getElementById('btnAbrirSalvarFiltro');
                var textoOriginal = btn.innerHTML;
                btn.innerHTML = 'Filtro salvo!';
                btn.className = btn.className.replace('btn-success','btn-default');
                btn.disabled = true;
                setTimeout(function(){ btn.innerHTML = textoOriginal; btn.className = btn.className.replace('btn-default','btn-success'); btn.disabled = false; }, 2000);
            } else {
                alert(data.message || 'Erro ao salvar filtro.');
            }
        },
        error: function(){
            alert('Erro ao salvar filtro.');
        }
    });
}
function carregarFiltroSalvo(){
    var codFiltroSalvo = $('#filtroSalvoSelect').val();
    if(!codFiltroSalvo){
        alert('Selecione um filtro para aplicar.');
        return;
    }
    var resposta = null;
    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND?>&t='+Math.random(),
        data: {c:'GerenciarFiltroSalvo', m:'carregar', cod_filtro_salvo:codFiltroSalvo},
        dataType: "json",
        async: false,
        cache: false,
        success: function(data){ resposta = data; },
        error: function(){ alert('Erro ao carregar filtro.'); }
    });
    if(!resposta || resposta.statusMessage!='1' || !resposta.datFiltro){
        if(resposta && resposta.message) alert(resposta.message);
        return;
    }
    var campos = resposta.datFiltro;
    if(typeof campos === 'string'){
        try { campos = JSON.parse(campos); } catch(e){ alert('Filtro salvo com formato inválido.'); return; }
    }
    if(!campos || !campos.length){ alert('Filtro salvo vazio.'); return; }
    // Limpa todos os campos do formulário
    limparCampos();
    // Popula os campos com os valores do filtro salvo (vanilla JS — compatível com qualquer jQuery)
    var form = document.getElementById('formListaInscritos');
    for(var i=0; i<campos.length; i++){
        var nome = campos[i].name;
        var valor = campos[i].value;
        if(!nome) continue;
        if(nome=='content' || nome=='c' || nome=='m' || nome=='media' || nome=='listar' || nome=='paginarMais' || nome=='filtro_salvo_aplicado') continue;
        try {
            var els = form.querySelectorAll('[name="'+nome+'"]');
            if(els.length==0) continue;
            var tipo = els[0].type;
            if(tipo=='radio'){
                for(var j=0; j<els.length; j++){ els[j].checked = (els[j].value == valor); }
            } else if(tipo=='checkbox'){
                for(var j=0; j<els.length; j++){ if(els[j].value == valor) els[j].checked = true; }
            } else if(els[0].tagName==='SELECT' && els[0].multiple){
                for(var j=0; j<els[0].options.length; j++){
                    if(els[0].options[j].value == valor) els[0].options[j].selected = true;
                }
            } else {
                els[0].value = valor;
            }
        } catch(e){}
    }
    // Marca qual filtro salvo foi aplicado (persiste no POST)
    document.getElementById('filtro_salvo_aplicado').value = codFiltroSalvo;
    atualizarTagsFiltrosAtivos();
    // Exibe modal de confirmação
    $('#modalConfirmarFiltro').modal('show');
}
function excluirFiltroSalvo(){
    var codFiltroSalvo = $('#filtroSalvoSelect').val();
    if(!codFiltroSalvo){
        alert('Selecione um filtro para excluir.');
        return;
    }
    var nomFiltro = $('#filtroSalvoSelect option:selected').text();
    if(!confirm('Deseja excluir o filtro "'+nomFiltro+'"?')) return;
    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND?>&t='+Math.random(),
        data: {c:'GerenciarFiltroSalvo', m:'excluir', cod_filtro_salvo:codFiltroSalvo},
        dataType: "json",
        cache: false,
        success: function(data){
            if(data.statusMessage=='1'){
                $('#filtroSalvoSelect option[value="'+codFiltroSalvo+'"]').remove();
                $('#filtroSalvoSelect').val('');
                alert('Filtro excluído com sucesso!');
            } else {
                alert(data.message || 'Erro ao excluir filtro.');
            }
        },
        error: function(){
            alert('Erro ao excluir filtro.');
        }
    });
}
</script>
        <link type="text/css" href="resources/js/jquery-ui-1.7.3.custom/css/smoothness/jquery-ui-1.7.3.custom.css" rel="stylesheet" />
        <!-- jQuery 1.3.2 removido: usa jQuery 1.12.4 do head com polyfills abaixo -->
        <script>
        (function($){
            // $.browser — removido no jQuery 1.9, necessário para jQuery UI 1.7.3
            if (!$.browser) {
                var ua = navigator.userAgent.toLowerCase();
                $.browser = {
                    msie: /msie|trident/.test(ua),
                    mozilla: /mozilla/.test(ua) && !/compatible|webkit/.test(ua),
                    webkit: /webkit/.test(ua),
                    opera: /opera/.test(ua),
                    version: (ua.match(/.+(?:rv|it|ra|ie)[\/: ]([\d.]+)/) || [0, '0'])[1]
                };
            }
            // $.curCSS — removido no jQuery 1.8
            if (!$.curCSS) {
                $.curCSS = function(elem, name) {
                    return $.css(elem, name);
                };
            }
        })(jQuery);
        </script>
        <script type="text/javascript" src="resources/js/jquery-ui-1.7.3.custom/js/jquery-ui-1.7.3.custom.min.js"></script>
        <script type="text/javascript" src="resources/js/jquery-ui-1.7.3.custom/development-bundle/ui/i18n/ui.datepicker-pt-BR.js"></script>
        <script type="text/javascript" src="resources/js/jquery-ui-1.7.3.custom/development-bundle/ui/ui.dialog.js"></script>
        <script type="text/javascript" src="resources/js/jquery-ui-timepicker-addon.js"></script>
        <script type="text/javascript" src="resources/js/jquery/jquery.price_format.1.7.min.js"></script>
        <script type="text/javascript" src="resources/js/jquery/jquery.form.js"></script>   
        
<link   rel="stylesheet"      href="resources/js/jquery.tablesorter/themes/blue/style.css" type="text/css" id="" media="print, projection, screen" />
<script type="text/javascript" src="resources/js/jquery.tablesorter/jquery.tablesorter.js"></script>
<!-- 
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.20/jquery.datetimepicker.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.20/jquery.datetimepicker.full.min.js"></script>
 -->

<fieldset>
    <ul class="nav nav-tabs">
        <li class="active"><a href="#"><b>Localizar Cadastrados</b></a></li>
        <?php if (FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_LISTAR_TRABALHOS, 5, $cod_msg, $redirect=FALSE)) { ?>
        <?php if ($objEvConf->getTrabSubmissao()==1 and $objEvConf->getCodEvento() != 107) { ?>
        <li><a href="<?php echo WB_URL_VIEW?>?content=view/admin/consulta-dados-congresso-listagem-trabalhos.php">Localizar <?php echo $objEvConf->getTrabAnexoTipo()!=''? $objEvConf->getTrabAnexoTipo() : 'Trabalhos' ?> </a></li>
        <?php } ?>
        <?php } ?>
        <?php if (in_array($objEvConf->getCodEvento(), [82])) { ?>
            <li><a href="<?php echo WB_URL_VIEW?>?content=view/admin/GerenciarInscritoStatus.php"><b>Inscrito Processo</b></a></li>
        <?php } ?>
        <?php if (COD_EVENTO == 1) { ?>
        <li><a href="<?php echo WB_URL_VIEW?>?content=view/admin/consulta-dados-congresso-listagem-trabalhos-premiacao.php">Definir Premiações</a></li>
        <?php } ?>
    </ul>
    <br>
    <form method="post" id="formListaInscritos" action="<?php echo WB_URL_VIEW?>?content=view/admin/consulta-dados-congresso-listagem-inscritos.php#rsltdoListaInscritos">
<!--        <div class="form-group col-sm-12" style="padding-left: 0px;">
            <input type="hidden" value="1" name="listar"> 
            <input type='hidden' name='content' value='<?php echo  $content ?>'>
            <input type='hidden' id='c' name='c' value=''>
            <input type='hidden' id='m' name='m' value=''>
            <input type='hidden' id='media' name='media' value='tela'>
            <input type="button" id="listarTela" value="Listar na Tela" class="btn btn-xs btn-primary"> 
            &nbsp;&nbsp;&nbsp;&nbsp;
            <input type="button" id="exportarExcel" value="Exportar para Excel" class="btn btn-xs btn-primary"> 
            &nbsp;&nbsp;&nbsp;&nbsp;
            <input type="button" value="Limpar Campos" onclick="javascript:limparCampos();" class="btn btn-sm modalBtnCancelar"> 
        </div>-->
        <input type="button" value="Limpar Campos" onclick="javascript:limparCampos();" class="btn btn-xs btn-primary">
        <a href="javascript:void(0)" id="toggleTodosAccordion" style="float:right; font-size:12px; color:#888; cursor:pointer;"><i class="fa fa-compress"></i> Recolher todos</a>
        <div class="panel-group" id="filtrosAccordion">

        <!-- Seção 1: Busca Rápida -->
        <div class="panel panel-default">
            <div class="panel-heading" data-toggle="collapse" data-target="#filtro-busca-rapida">
                <h4 class="panel-title" style="margin:0">Busca Rápida</h4>
                <i class="fa fa-chevron-down"></i>
            </div>
            <div id="filtro-busca-rapida" class="panel-collapse collapse in">
                <div class="panel-body">

        <div class="form-group col-sm-12" style="padding-left: 0px;">
            <label for="cod_evento">Selecione Projeto:
            </label>
            <select name="cod_evento" id="cod_evento" class="form-control" placeholder="Selecione um Eventos/Cursos">
                <option value="-1"></option>
                <?php
                    try {
                        CarregarOptionsAC::listarEventos($fws_, $debug);
                    } catch (Exception $exc) {
                        /*Lista não encontrada ou vazia*/
                    }
                ?>
            </select>
        </div>
        <div class="form-group col-sm-9" style="padding-left: 0px;">
            <!--<label for="nome_inscrito">Nome Inscrito:</label>-->
            <input type="text" id="nome_inscrito" name="nome_inscrito" value="<?php echo $fws_['nome_inscrito'] ?>"
                   class="form-control" placeholder="Nome do Inscrito"
                   SIZE='60' maxlength='255' onBlur='trim(this);' onkeypress='javascript: return isAlfa(getKey(event));'>
        </div>
        <div class="form-group col-sm-3" style="padding-left: 0px;">
            <!--<label for="cpf">Cpf:</label>-->
            <input type="text" id="fone_contato" name="fone_contato" value="<?php echo $fws_['fone_contato'] ?>"
                   class="form-control" placeholder="Fone do Inscrito"
                   SIZE='60' maxlength='15' onBlur='trim(this);' onkeypress='javascript: return isNum(getKey(event));'>
        </div>
    
        <div class="form-group col-sm-3" style="padding-left: 0px;">
            <!--<label for="cod_inscrito">Cód. Inscrito:</label>-->
            <input type="text" id="cod_inscrito" name="cod_inscrito" value="<?php echo $fws_['cod_inscrito'] ?>"
                   class="form-control" placeholder="Cód. do Inscrito"
                   SIZE='60' maxlength='255' onBlur='trim(this);' onkeypress='javascript: return isNum(getKey(event));'>
        </div>
        <div class="form-group col-sm-3" style="padding-left: 0px;">
            <!--<label for="cod_inscrito">Cód. Inscrito:</label>-->
            <input type="text" id="cod_trabalho" name="cod_trabalho" value="<?php echo $fws_['cod_trabalho'] ?>"
                   class="form-control" placeholder="Cód. do Trabalho"
                   SIZE='60' maxlength='255' onBlur='trim(this);' onkeypress='javascript: return isNum(getKey(event));'>
        </div>
        <div class="form-group col-sm-3" style="padding-left: 0px;">
            <input type="text" id="id_publico" name="id_publico" value="<?php echo $fws_['id_publico'] ?>"
                   class="form-control" placeholder="Id Público"
                   SIZE='60' maxlength='255' onBlur='trim(this);' onkeypress='javascript: return isNum(getKey(event));'>
        </div>           
        <div class="form-group col-sm-3" style="padding-left: 0px;">
            <input type="text" id="pedido_id" name="pedido_id" value="<?php echo $fws_['pedido_id'] ?>"
                   class="form-control" placeholder="Cód. do Pedido"
                   SIZE='60' maxlength='255' onBlur='trim(this);' onkeypress='javascript: return isNum(getKey(event));'>
        </div>
        <div class="form-group col-sm-4" style="padding-left: 0px;">
            <input type="text" id="transacao_id" name="transacao_id" value="<?php echo $fws_['transacao_id'] ?>"
                   class="form-control" placeholder="Transação Id"
                   SIZE='60' maxlength='255' onBlur='trim(this);' onkeypress='javascript: return isNum(getKey(event));'>
        </div>
        <div class="form-group col-sm-3" style="padding-left: 0px;">
            <!--<label for="e_mail">E-mail:</label>-->
            <input type="text" id="e_mail" name="e_mail" value="<?php echo $fws_['e_mail'] ?>"
                   class="form-control" placeholder="E-mail do Inscrito"
                   SIZE='60' maxlength='255' onBlur='trim(this);'>
        </div>
        <div class="form-group col-sm-3" style="padding-left: 0px;">
            <!--<label for="cpf">Cpf:</label>-->
            <input type="text" id="cpf" name="cpf" value="<?php echo $fws_['cpf'] ?>"
                   class="form-control" placeholder="Cpf do Inscrito"
                   SIZE='60' maxlength='15' onBlur='trim(this);' onkeypress='javascript: return isNum(getKey(event));'>
        </div>

                </div><!-- /.panel-body -->
            </div><!-- /.panel-collapse -->
        </div><!-- /.panel Busca Rápida -->

        <!-- Seção 2: Perfil -->
        <?php
        $abrir_perfil = ($fws_['tipo_perfil'] || !empty($fws_['cod_perfil']) || $fws_['tipo_inscricao'] || $fws_['cod_oficina'] || $fws_['cod_area_tematica'] || $fws_['patrocinador_voucher']);
        ?>
        <div class="panel panel-default">
            <div class="panel-heading <?php echo $abrir_perfil ? '' : 'collapsed' ?>" data-toggle="collapse" data-target="#filtro-perfil">
                <h4 class="panel-title" style="margin:0">Perfil</h4>
                <i class="fa fa-chevron-down"></i>
            </div>
            <div id="filtro-perfil" class="panel-collapse collapse<?php echo $abrir_perfil ? ' in' : '' ?>">
                <div class="panel-body">

        <div class="form-group col-sm-12" style="padding-left: 0px;">
            <label for="cod_perfil">Tipo do Perfil do Cadastro: </label>
            &nbsp;<input type='radio' name='tipo_perfil' value=''          <?php echo verifItemChecked($fws_['tipo_perfil'], '') ?> > Todos
            &nbsp;<input type='radio' name='tipo_perfil' value='INSCRITO'  <?php echo verifItemChecked($fws_['tipo_perfil'], 'INSCRITO') ?> > Inscritos
            &nbsp;<input type='radio' name='tipo_perfil' value='OUTROS'    <?php echo verifItemChecked($fws_['tipo_perfil'], 'OUTROS') ?>> Outros
            &nbsp;<input type='radio' name='tipo_perfil' value='AMBOS'     <?php echo verifItemChecked($fws_['tipo_perfil'], 'AMBOS') ?>> Ambos
        </div>
        <div class="form-group col-sm-12" style="padding-left: 0px;">
            <label for="cod_perfil">Selecione Perfil de Cadastro: </label>
            <select name="cod_perfil[]" id="cod_perfil" class="form-control" placeholder="Selecione um Perfil" multiple>
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
        <div class="form-group col-sm-12" style="padding-left: 0px;">
            <label for="tipo_inscricao">Selecione Categoria: </label>
            <select name="tipo_inscricao" id="tipo_inscricao" class="form-control" placeholder="Selecione um Categoria">
                <option></option>
                <?php CarregarOptionsAC::tipoInscricaoLinha($fws_, $debug); ?>
            </select>
        </div>
        <?php if ($fws_['op_selecionar_oficina']) {?>
        <div class="form-group col-sm-9" style="padding-left: 0px;">
            <?php 
            $tipoAtividade = "Atividades";
            if($objEvConf->getAtividadeTipo()!=''){
                $tipoAtividade = $objEvConf->getAtividadeTipo();
            }
            ?>
            <label for="cod_oficina">Selecione <?php echo $tipoAtividade ?>: </label>
            <select name="cod_oficina" id="cod_oficina" class="form-control" placeholder="Selecione uma <?php echo $tipoAtividade ?>">
                <option></option>
                <?php CarregarOptionsAC::oficinas($fws_, $debug); ?>
            </select>
        </div>
        <?php }?>
        <?php if ($fws_['op_selecionar_area_tematica']) {?>
        <div class="form-group col-sm-9" style="padding-left: 0px;">
            <label for="cod_area_tematica">Selecione Área Temática: </label>
            <select name="cod_area_tematica" id="cod_area_tematica" class="form-control">
                <option></option>
                <?php CarregarOptionsAC::areasTematicas($fws_, $debug); ?>
            </select>
        </div>
        <?php }?>
        <?php if ($objEvConf->getEmitirVoucher()==1) { ?>
        <div class="form-group col-sm-12 cnslt-padrao" style="padding-left: 0px;">
            <label for="patrocinador_voucher">Selecione Patrocinadores de Vouchers: </label> (Gerados, Utilizados, Patrocinador)
            <select name="patrocinador_voucher" id="formato_texto" class="form-control">
                <option></option>
                <?php CarregarOptionsAC::patrocinadoresVouchers($fws_, $debug); ?>
            </select>
        </div>
        <?php } ?>

                </div><!-- /.panel-body -->
            </div><!-- /.panel-collapse -->
        </div><!-- /.panel Perfil -->

        <!-- Seção 2b: Localização -->
        <?php
        $abrir_localizacao = ($fws_['cod_macrorregiao'] || $fws_['estado'] || $fws_['cidade']);
        ?>
        <div class="panel panel-default">
            <div class="panel-heading <?php echo $abrir_localizacao ? '' : 'collapsed' ?>" data-toggle="collapse" data-target="#filtro-localizacao">
                <h4 class="panel-title" style="margin:0">Localização</h4>
                <i class="fa fa-chevron-down"></i>
            </div>
            <div id="filtro-localizacao" class="panel-collapse collapse<?php echo $abrir_localizacao ? ' in' : '' ?>">
                <div class="panel-body">

        <?php if ($objEvConf->getPossuiMacrorregiao()==1) {?>
        <div class="form-group col-sm-3" style="padding-left: 0px;">
            <label for="cod_macrorregiao">Selecione Macrorregião: </label>
            <select name="cod_macrorregiao" id="cod_macrorregiao" class="form-control">
                <option></option>
                <?php CarregarOptionsAC::macrorregioes($fws_, $debug); ?>
            </select>
        </div>
        <?php }?>
        <div class="form-group col-sm-3" style="padding-left: 0px;">
            <label for="estado">Selecione Estado: </label>
            <select name="estado" id="estado" class="form-control">
                <option></option>
                <?php CarregarOptionsAC::estados($fws_, $debug); ?>
            </select>
        </div>
        <div class="form-group col-sm-9" style="padding-left: 0px;">
            <label for="cidade">Selecione Cidade: </label>
            <select name="cidade" id="cidade" class="form-control">
                <option></option>
                <?php CarregarOptionsAC::cidades($fws_, $debug); ?>
            </select>
        </div>

                </div><!-- /.panel-body -->
            </div><!-- /.panel-collapse -->
        </div><!-- /.panel Localização -->

        <!-- Seção 3: Situação -->
        <?php
        $abrir_situacao = ($fws_['situacao'] && $fws_['situacao'] != 'ATIVADA') || $fws_['status_processo'] || $fws_['presenca_inscrito'] || $fws_['intervalo_registros'] || $fws_['data_inscricao_ini'] || $fws_['data_inscricao_fim'];
        ?>
        <div class="panel panel-default">
            <div class="panel-heading" data-toggle="collapse" data-target="#filtro-situacao">
                <h4 class="panel-title" style="margin:0">Situação</h4>
                <i class="fa fa-chevron-down"></i>
            </div>
            <div id="filtro-situacao" class="panel-collapse collapse in">
                <div class="panel-body">
                    <?php CarregarComponentesAC::campoSituacaoInscricao($fws_); ?>
                    <?php CarregarComponentesAC::campoDataCadastro($fws_); ?>
                </div><!-- /.panel-body -->
            </div><!-- /.panel-collapse -->
        </div><!-- /.panel Situação -->

        <!-- Seção 3b: Registro de Atividade (apenas evento 82) -->
        <?php if (COD_EVENTO==82) { ?>
        <?php
        $abrir_registro_atividade = ($fws_['registro_atividade_status'] || $fws_['periodo_apuracao_reg_ativ_ini'] || $fws_['periodo_apuracao_reg_ativ_fim']);
        ?>
        <div class="panel panel-default">
            <div class="panel-heading <?php echo $abrir_registro_atividade ? '' : 'collapsed' ?>" data-toggle="collapse" data-target="#filtro-registro-atividade">
                <h4 class="panel-title" style="margin:0">Registro de Atividade</h4>
                <i class="fa fa-chevron-down"></i>
            </div>
            <div id="filtro-registro-atividade" class="panel-collapse collapse<?php echo $abrir_registro_atividade ? ' in' : '' ?>">
                <div class="panel-body">
                    <?php CarregarComponentesAC::campoRegistroAtividade($fws_); ?>
                </div><!-- /.panel-body -->
            </div><!-- /.panel-collapse -->
        </div><!-- /.panel Registro de Atividade -->
        <?php } ?>

        <!-- Seção 4: Financeiro -->
        <?php if ($objEvConf->getRecebePagamento()==1) {?>
        <?php
        $abrir_financeiro = ($fws_['pagamentos'] || $fws_['almoco'] || $fws_['tipo_pedido'] || $fws_['forma_pagamento'] || $fws_['data_pagamento_ini'] || $fws_['data_pagamento_fim'] || $fws_['data_pagamento_incl_ini'] || $fws_['data_pagamento_incl_fim'] || $fws_['data_vencimento_ini'] || $fws_['data_vencimento_fim']);
        ?>
        <div class="panel panel-default">
            <div class="panel-heading <?php echo $abrir_financeiro ? '' : 'collapsed' ?>" data-toggle="collapse" data-target="#filtro-financeiro">
                <h4 class="panel-title" style="margin:0">Financeiro</h4>
                <i class="fa fa-chevron-down"></i>
            </div>
            <div id="filtro-financeiro" class="panel-collapse collapse<?php echo $abrir_financeiro ? ' in' : '' ?>">
                <div class="panel-body">
                    <?php CarregarComponentesAC::campoQuantidadePedidos($fws_, $debug); ?>
                    <br>
                    <?php CarregarComponentesAC::campoListaPagamentos($fws_, $debug); ?>
                    <br>
                    <?php if($OP_SELECIONAR_ALMOCO) { ?>
                    <label for="ordenar">Refeições: </label>
                    &nbsp;<input type='radio' name='almoco' value='' <?php echo verifItemChecked($fws_['almoco'], '') ?> > Todos
                    &nbsp;<input type='radio' name='almoco' value='SIM' <?php echo verifItemChecked($fws_['almoco'], 'SIM') ?> > Com refeição
                    &nbsp;<input type='radio' name='almoco' value='NAO' <?php echo verifItemChecked($fws_['almoco'], 'NAO') ?> > Sem refeição
                    <?php } ?>
                    <?php CarregarComponentesAC::campoDatasPagamento($fws_); ?>
                </div><!-- /.panel-body -->
            </div><!-- /.panel-collapse -->
        </div><!-- /.panel Financeiro -->
        <?php } ?>

        <!-- Seção 5: Documentos -->
        <?php
        $exibir_documentos = (
            $objEvConf->getTrabSubmissao() ||
            $objEvConf->getContratoAnexar() ||
            in_array($objEvConf->getTrabAnexoTipo(), ["Contrato de Prestação de Serviço", "Documento", "Documentos"]) ||
            $objEvConf->getAvulsoAnexar() ||
            $objEvConf->getBoletoAnexar() ||
            ($objEvConf->getTrabAvaliacao() && $objEvConf->getTrabAvaliacaoPor()=='ARQUIVO') ||
            in_array($objEvConf->getCodEvento(), [80, 88, 115])
        );
        ?>
        <?php if ($exibir_documentos) { ?>
        <?php
        $abrir_documentos = ($fws_['trabalhos'] || $fws_['contrato_anexo'] || $fws_['rpa_anexo'] || $fws_['comp_transf_anexo'] || $fws_['termo_desist_anexo'] || $fws_['flg_assinado'] || $fws_['boletos'] || $fws_['docs_contratacao'] || !empty($fws_['doc_contratacao_falta']));
        ?>
        <div class="panel panel-default">
            <div class="panel-heading <?php echo $abrir_documentos ? '' : 'collapsed' ?>" data-toggle="collapse" data-target="#filtro-documentos">
                <h4 class="panel-title" style="margin:0">Documentos</h4>
                <i class="fa fa-chevron-down"></i>
            </div>
            <div id="filtro-documentos" class="panel-collapse collapse<?php echo $abrir_documentos ? ' in' : '' ?>">
                <div class="panel-body">
                    <?php CarregarComponentesAC::campoContratos($objEvConf, $fws_) ?>
                </div><!-- /.panel-body -->
            </div><!-- /.panel-collapse -->
        </div><!-- /.panel Documentos -->
        <?php } ?>

        <!-- Seção 6: Avaliação -->
        <?php
        $exibir_avaliacao = (
            in_array($objEvConf->getCodEvento(), [80, 88, 115]) ||
            $objEvConf->getTrabAvaliacaoPermitirRecurso() ||
            ($objEvConf->getTrabAvaliacao() && $objEvConf->getTrabAvaliacaoPor()=='CANDIDATO') ||
            $objEvConf->getAtividade()
        );
        ?>
        <?php if ($exibir_avaliacao) { ?>
        <?php
        $abrir_avaliacao = ($fws_['avaliacao_inscrito'] || $fws_['solicitaram_recurso'] || $fws_['oficinas'] || $fws_['avaliacao_trabalhos'] || $fws_['classificacao_ate']);
        ?>
        <div class="panel panel-default">
            <div class="panel-heading <?php echo $abrir_avaliacao ? '' : 'collapsed' ?>" data-toggle="collapse" data-target="#filtro-avaliacao">
                <h4 class="panel-title" style="margin:0">Avaliação</h4>
                <i class="fa fa-chevron-down"></i>
            </div>
            <div id="filtro-avaliacao" class="panel-collapse collapse<?php echo $abrir_avaliacao ? ' in' : '' ?>">
                <div class="panel-body">
                    <?php CarregarComponentesAC::camposAvaliacao($objEvConf, $fws_) ?>
                    <?php CarregarComponentesAC::camposAvitivades($objEvConf, $fws_) ?>
                </div><!-- /.panel-body -->
            </div><!-- /.panel-collapse -->
        </div><!-- /.panel Avaliação -->
        <?php } ?>

        <!-- Seção 7: Visualização -->
        <?php
        // perspectiva e ordenar SEMPRE recebem valor padrao no topo desta view, entao
        // nao da para deduzir "o usuario escolheu" -- este bloco nasce recolhido.
        $abrir_visualizacao = false;
        ?>
        <div class="panel panel-default">
            <div class="panel-heading <?php echo $abrir_visualizacao ? '' : 'collapsed' ?>" data-toggle="collapse" data-target="#filtro-visualizacao">
                <h4 class="panel-title" style="margin:0">Visualização</h4>
                <i class="fa fa-chevron-down"></i>
            </div>
            <div id="filtro-visualizacao" class="panel-collapse collapse<?php echo $abrir_visualizacao ? ' in' : '' ?>">
                <div class="panel-body">
                    <div class="form-group col-sm-12" style="padding-left: 0px;">
                        <?php CarregarComponentesAC::campoVisualizacao($fws_, $objEvConf); ?>
                    </div>
                    <?php if (FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_AVALIACAO_INSCRITO, 5, false, false)){?>
                        <?php if ($objEvConf->getTrabAvaliacao()) { ?>
                          <!-- /**filtrar avaliação inscrito */   -->
                        <?php } ?>
                    <?php } ?>
                </div><!-- /.panel-body -->
            </div><!-- /.panel-collapse -->
        </div><!-- /.panel Visualização -->

        </div><!-- /.panel-group filtrosAccordion -->

        <!-- Ações em Lote -->
        <?php // era 999 cravado aqui tambem: sem isto, liberar SIS_ACOES_EM_LOTE nao
              // adiantava nada -- este if externo derrubava o bloco inteiro antes de
              // chegar na verificacao do componente. Mesma lista do gate do controller.
        if (FWA_temPermissao(ListarInscritosAcoesAC::permissoesAcaoEmLote())) {?>
        <div class="form-group col-sm-12 cnslt-padrao" style="padding-left: 0px; margin-top: 10px;">

        <?php CarregarComponentesAC::campoAcaoEmLote($fws_, $objEvConf, $debug); ?>

            <div id="divSelectComunicados" style="display: none;margin-top: 10px;margin-bottom: 20px;" class="aba-filtro">
                <br>
                <label for="comunicado">Selecionar template de e-mail: </label>
                <select id="comunicado" name="comunicado">
                    <option value="">Selecione um modelo</option>
                    <?php CarregarOptionsAC::listarOpcoesEmail($fws_,$debug); ?>
                </select>
                <!-- leitura do conteudo completo: a previa abaixo corta em 200 caracteres e
                     em texto puro. Quem nao edita precisa conseguir ler o e-mail inteiro
                     antes de disparar para a lista toda. -->
                <button type="button" class="btn btn-sm" onclick="verTemplateCompleto()" style="margin-left: 10px;">
                    <i class="fa fa-eye"></i> Ver e-mail
                </button>
                <?php // escolher e ver e' leitura (liberada para Acao em Lote); mexer no texto
                      // do template continua ADM_GERAL. Sem este if, quem so envia clicava em
                      // Editar e recebia "Template nao encontrado" -- que na verdade era falta
                      // de permissao.
                if (FWA_temPermissao(GerenciarEmailTemplateAC::permissoesEdicao())) { ?>
                <button type="button" class="btn btn-sm modalBtnSalvar" onclick="abrirModalEditarTemplate()" style="margin-left: 10px;">
                    <i class="fa fa-pencil"></i> Editar
                </button>
                <button type="button" class="btn btn-sm modalBtnSalvar" onclick="mdtNovoTemplate()" style="margin-left: 4px;">
                    <i class="fa fa-plus"></i> Novo Template
                </button>
                <a class='link' style="margin-left: 10px;"
                href='index.php?content=view/admin/GerenciarEmailTemplate.php' target="_blank">Gerenciar Templates</a>
                <?php } ?>

                <!-- Preview do template selecionado -->
                <div id="previewTemplate" style="display:none; margin-top:10px; padding:10px; background:#f9f9f9; border:1px solid #ddd; border-radius:4px; font-size:13px;">
                    <b>Assunto:</b> <span id="previewAssunto"></span>
                    <br><b>Mensagem:</b> <span id="previewCorpo" style="color:#555;"></span>
                </div>

                <div style="margin-top:10px;">
                <input type="text" name="email_bcc" id="email_bcc" class="form-control" placeholder="Com cópia oculta para (BCC)" value="<?php echo $fws_['email_bcc'] ?>">
                </div>
                <br>
                <?php // volta marcado apos o envio: sem isto quem acabou de disparar nao ve
                      // se rodou em modo teste ou real -- e o token do formulario e que
                      // impede a repeticao, nao o campo em branco ?>
                <input type="radio" id="acaoEmailEnviar" name="acaoEmail" value="enviarEmail" <?php echo verifItemChecked($fws_['acaoEmail'], 'enviarEmail') ?>>
                <label for="acaoEmailEnviar">Enviar e-mail</label>&nbsp;&nbsp;
                <input type="radio" id="acaoEmailTestar" name="acaoEmail" value="testarEmail" <?php echo verifItemChecked($fws_['acaoEmail'], 'testarEmail') ?>>
                <label for="acaoEmailTestar">Testar e visualizar e-mail</label><br>
            </div>
            <br>

            <label for="negar-filtro">Negar filtro: </label>
            &nbsp;<input type='radio' name='negar_filtro' value='negar_filtro' <?php echo verifItemChecked($fws_['negar_filtro'], '1') ?> > Negar Filtro

        <?php } ?>

        <!-- Resumo dos filtros selecionados (tags geradas por JS atualizarTagsFiltrosAtivos) -->
        <?php

        // Carregar filtros salvos do usuário para o evento atual
        $listaFiltrosSalvos = [];
        try {
            $objFiltroSalvo = new FiltroSalvo();
            $objFiltroSalvo->setCodUsuario($_SESSION['_cod_usuario']);
            $objFiltroSalvo->setCodEvento(COD_EVENTO);
            $objFiltroSalvo->listar();
            $listaFiltrosSalvos = $objFiltroSalvo->getListaFiltrosSalvos();
            if (!$listaFiltrosSalvos) $listaFiltrosSalvos = [];
        } catch (Exception $e) {
            $listaFiltrosSalvos = [];
        }
        ?>
        <div id="resumoFiltrosAtivos" style="background:#f0f4ff; border:1px solid #b8cce4; border-radius:4px; padding:6px 12px; margin:8px 0; font-size:12px;">
            <div id="divFiltrosAtivosTags" style="margin-bottom:6px; display:none;">
                <b>Filtros ativos:</b>
                <button type="button" class="btn btn-sm modalBtnSalvar" id="btnAbrirSalvarFiltro" onclick="abrirSalvarFiltro()" style="margin-left:6px;">Salvar filtro</button>
                <span id="spanSalvarFiltroInline" style="display:none; margin-left:4px;">
                    <input type="text" id="inputNomeFiltroSalvar" maxlength="100" placeholder="Nome do filtro" onkeydown="if(event.keyCode==13){event.preventDefault();confirmarSalvarFiltro();}else if(event.keyCode==27){fecharSalvarFiltro();}" style="font-size:11px; padding:2px 6px; border:1px solid #999; border-radius:3px; width:180px; height:24px;">
                    <button type="button" class="btn btn-sm modalBtnSalvar" onclick="confirmarSalvarFiltro()">OK</button>
                    <button type="button" class="btn btn-sm modalBtnCancelar" onclick="fecharSalvarFiltro()">Cancelar</button>
                </span>
                <br>
                <!-- Tags geradas dinamicamente por atualizarTagsFiltrosAtivos() -->
            </div>
            <div style="display:flex; align-items:center; gap:6px; flex-wrap:wrap;" id="divMeusFiltros">
                <b>Meus filtros:</b>
                <select id="filtroSalvoSelect" class="form-control input-sm" style="width:220px; display:inline-block; height:28px; font-size:12px; padding:2px 6px;">
                    <option value="">-- Selecionar --</option>
                    <?php foreach ($listaFiltrosSalvos as $fs) { ?>
                        <option value="<?php echo $fs->getCodFiltroSalvo() ?>"<?php echo ($fws_['filtro_salvo_aplicado'] == $fs->getCodFiltroSalvo()) ? ' selected' : '' ?>><?php echo htmlspecialchars($fs->getNomFiltro()) ?></option>
                    <?php } ?>
                </select>
                <button type="button" class="btn btn-sm modalBtnSalvar" onclick="carregarFiltroSalvo()">Usar Filtro</button>
                <button type="button" class="btn btn-sm modalBtnExcluir btn-danger" onclick="excluirFiltroSalvo()">Excluir</button>
            </div>
        </div>

        <!-- Botões de execução (sempre visível) -->
        <div class="form-group col-sm-12" style="padding-left: 0px; margin-top: 10px;">
            <input type="hidden" id="paginarMais" name="paginarMais" value="" >
            <input type="hidden" value="1" name="listar">
            <input type='hidden' name='content' value='<?php echo  $content ?>'>
            <input type='hidden' id='c' name='c' value=''>
            <input type='hidden' id='m' name='m' value=''>
            <input type='hidden' id='media' name='media' value='tela'>
            <input type='hidden' id='filtro_salvo_aplicado' name='filtro_salvo_aplicado' value='<?php echo htmlspecialchars($fws_['filtro_salvo_aplicado']) ?>'>
            <input type="button" id="listarTela" value="Listar na Tela" class="btn btn-sm modalBtnSalvar">
            &nbsp;
            <input type="button" id="exportarExcel" value="Exportar para Excel" class="btn btn-sm modalBtnSalvar">
            &nbsp;
            <input type="button" id="exportarXml" value="Exportar para Xml" class="btn btn-sm modalBtnSalvar">
            &nbsp;
            <input type="button" id="exportarHtml" value="Exportar para Html" class="btn btn-sm modalBtnSalvar">
            &nbsp;
            <?php if ($_SESSION['_cod_usuario']==668) { ?>
                <?php if ($objEvConf->getExportarEdital()==1 or (in_array($objEvConf->getCodEvento(), [80, 88]))) { ?>
                    <input type="button" id="exportarEditalHtml" value="Exportar para Edital" class="btn btn-sm modalBtnSalvar">
                    &nbsp;
                <?php } ?>
            <?php } ?>
            <?php if ($objEvConf->getRecebePagamentoMensalidade()==1) { ?>
                <input type="button" id="exportarMensalidadesXls" value="Exportar Mensalidades para Xls" class="btn btn-sm modalBtnSalvar">
                &nbsp;
            <?php } ?>
            <?php if ($objEvConf->getExportarAcertos()==1 or $objEvConf->getCodEvento()==45) { ?>
                <input type="button" id="exportarAcertosXls" value="Exportar Acertos para Xls" class="btn btn-sm modalBtnSalvar">
                &nbsp;
            <?php } ?>
            <input type="button" value="Limpar Campos" onclick="javascript:limparCampos();" class="btn btn-sm modalBtnCancelar">
        </div>
    </form>

    <form name="gerarEtiqueta" id="gerarEtiqueta" method="post" action="<?php echo WB_URL_ACTION?>?content=view/recepcao/etiquetas/etiqueta.php" target="_blank">
            <!--form name="gerarEtiqueta" id="gerarEtiqueta" method="post" action="httpS://<?php echo FWA_Dominio()?>//inscricoes/lib/html2pdf/exemples/etiqueta.php?html" target="_blank"-->
        <input type="hidden" value="" name="tipoCertificado" id="tipoCertificado">
        <input type="hidden" value="" name="codInscrito"     id="codInscrito">
    </form>
</fieldset>
<hr style="width: 97%; color: black; height: 1px; background-color:black;" class="col-sm-12" />
<a name="rsltdoListaInscritos"></a>
<?php
try {
    $objLT = new ListarInscritosAC();
    ?>
<table class="tablesorter" width="550px" border="0">
    <thead>
        <?php $objLT->cabecalhoTabelaListaInscritos($fws_, $objEv, $objEvConf); ?>
    </thead>
    <tbody>    
    <?php
    if ($fws_['listar'] != 1) {
        throw new Exception("Para listar trabalhos, utilize o formulário acima e clique em <Aplicar Filtrar>");
    }
    $fws_['objEv'] = $objEv;
    $fws_['objEvConf'] = $objEvConf;
    $objLT->carregarListaInscritos($fws_, $debug);
    
} catch (Exception $exc) {
    echo "<tr><td colspan=8>" . $exc->getMessage() . "<br>&nbsp;"
            . "<a href='".WB_URL_VIEW."?content=view/admin/inscritos-cadastro-ok.php'>"
            . "<img src='resources/image/fig-incluir.gif' border='0' width='20px'> Cadastrar Inscrito</a>"
            . "</td></tr>";
}
echo "</tbody></table>";
echo "<br><br>";
require_once(dirname(__FILE__)."/../../view/admin/consulta-dados-congresso-rodape.php");
?>
</div></div></div>

<style>
  .aba-filtro {
    display: none;
    margin-top: 10px;
    margin-bottom: 20px;
    border: 1px solid #ccc;
    padding: 10px;
    border-radius: 5px;
  }
  #filtrosAccordion {
    margin-bottom: 10px;
    min-height: auto;
  }
  #filtrosAccordion > .panel {
    border-radius: 0;
    margin-bottom: 0;
  }
  #filtrosAccordion > .panel + .panel {
    margin-top: -1px;
  }
  #filtrosAccordion > .panel:first-child {
    border-top-left-radius: 0.375rem;
    border-top-right-radius: 0.375rem;
  }
  #filtrosAccordion > .panel:last-child {
    border-bottom-left-radius: 0.375rem;
    border-bottom-right-radius: 0.375rem;
  }
  #filtrosAccordion .panel-heading {
    cursor: pointer;
    user-select: none;
    padding: 6px 12px;
    display: flex;
    align-items: center;
    justify-content: space-between;
  }
  #filtrosAccordion .panel-heading .fa-chevron-down {
    transition: transform 0.3s ease;
    font-size: 12px;
    opacity: 0.5;
  }
  #filtrosAccordion .panel-heading.collapsed .fa-chevron-down {
    transform: rotate(-90deg);
  }
</style>

<?php include(dirname(__FILE__) . "/modal-email-template.php"); ?>

<!-- Modal de LEITURA do template (quem envia mas nao edita) -->
<div class="modal fade" id="modalVerTemplate" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document" style="max-width:820px;">
        <div class="modal-content">
            <div class="modal-header modalCabecalho">
                <h4 class="modal-title"><i class="fa fa-envelope-o"></i> Conteúdo do E-mail</h4>
                <button type="button" class="close modalBtnFechar" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body" style="max-height:520px; overflow-y:auto;">
                <p style="margin-bottom:6px;"><b>Assunto:</b> <span id="verTemplateAssunto"></span></p>
                <p style="margin-bottom:10px; color:#888; font-size:12px;"><span id="verTemplateDescricao"></span></p>
                <iframe id="verTemplateCorpo" sandbox=""
                        style="width:100%; height:420px; border:1px solid #e0e0e0; border-radius:4px; background:#fff;"></iframe>
                <p style="margin-top:8px; color:#888; font-size:12px;">
                    Visualização somente leitura. As tags (nome, evento, valores) são substituídas no envio.
                </p>
            </div>
            <div class="modal-footer modalRodape">
                <button type="button" class="btn btn-default modalBtnCancelar" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmação para aplicar filtro salvo -->
<div class="modal fade" id="modalConfirmarFiltro" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-sm" role="document">
    <div class="modal-content">
      <div class="modal-header modalCabecalho">
        <h4 class="modal-title"><i class="fa fa-filter"></i>Filtro Carregado</h4>
        <button type="button" class="close modalBtnFechar" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <div class="modal-body">
        <p>Deseja efetuar a busca e listar na tela?</p>
      </div>
      <div class="modal-footer modalRodape">
        <button type="button" class="btn btn-default modalBtnCancelar" data-dismiss="modal">N&atilde;o</button>
        <button type="button" class="btn btn-primary modalBtnSalvar" id="btnConfirmarFiltroSim">Sim</button>
      </div>
    </div>
  </div>
</div>
<script>
document.getElementById('btnConfirmarFiltroSim').addEventListener('click', function(){
    $('#modalConfirmarFiltro').modal('hide');
    document.getElementById('listarTela').click();
});
</script>

<script>
(function($){
$(document).ready(function(){
    var codEvento = '<?php echo COD_EVENTO; ?>';
    var storageKey = 'filtrosAccordion_' + codEvento;
    var paineis = [
        'filtro-busca-rapida',
        'filtro-perfil',
        'filtro-localizacao',
        'filtro-situacao',
        'filtro-registro-atividade',
        'filtro-financeiro',
        'filtro-documentos',
        'filtro-avaliacao',
        'filtro-visualizacao'
    ];

    // Detecta se o painel está aberto (compatível com BS3 .in e BS5 .show)
    function painelAberto(el){
        return $(el).hasClass('in') || $(el).hasClass('show');
    }

    function getEstadoSalvo(){
        try {
            var dados = localStorage.getItem(storageKey);
            return dados ? JSON.parse(dados) : null;
        } catch(e){ return null; }
    }

    function salvarEstado(){
        var estado = {};
        paineis.forEach(function(id){
            var el = document.getElementById(id);
            if(el){
                estado[id] = painelAberto(el);
            }
        });
        try { localStorage.setItem(storageKey, JSON.stringify(estado)); } catch(e){}
    }

    function atualizarChevron(headingEl, aberto){
        if(aberto){
            $(headingEl).removeClass('collapsed');
        } else {
            $(headingEl).addClass('collapsed');
        }
    }

    function atualizarBotaoToggle(){
        var todosAbertos = true;
        paineis.forEach(function(id){
            var el = document.getElementById(id);
            if(el && !painelAberto(el)){
                todosAbertos = false;
            }
        });
        var $btn = $('#toggleTodosAccordion');
        if(todosAbertos){
            $btn.html('<i class="fa fa-compress"></i> Recolher todos');
        } else {
            $btn.html('<i class="fa fa-expand"></i> Expandir todos');
        }
    }

    // Paineis que o PHP abriu por terem filtro preenchido -- esses ficam abertos
    // mesmo que o usuario tenha recolhido numa busca anterior (esconder filtro ativo confunde).
    var comFiltroAtivo = <?php echo json_encode(array_values(array_filter(array(
        !empty($abrir_perfil)              ? 'filtro-perfil'              : null,
        !empty($abrir_localizacao)         ? 'filtro-localizacao'         : null,
        !empty($abrir_registro_atividade)  ? 'filtro-registro-atividade'  : null,
        !empty($abrir_financeiro)          ? 'filtro-financeiro'          : null,
        !empty($abrir_documentos)          ? 'filtro-documentos'          : null,
        !empty($abrir_avaliacao)           ? 'filtro-avaliacao'           : null,
    )))); ?>;

    // Restaurar estado salvo. Sem valor salvo, mantem o que o PHP renderizou
    // (Busca Rapida e Situacao abertos; o resto so se tiver filtro).
    var estadoSalvo = getEstadoSalvo();
    if(estadoSalvo){
        paineis.forEach(function(id){
            var el = document.getElementById(id);
            if(!el) return;
            var heading = $(el).prev('.panel-heading');
            if(comFiltroAtivo.indexOf(id) !== -1){
                if(!painelAberto(el)){ $(el).addClass('in'); }
                atualizarChevron(heading, true);
                return;
            }
            if(estadoSalvo[id] === false){
                $(el).removeClass('show in');
                atualizarChevron(heading, false);
            } else if(estadoSalvo[id] === true){
                if(!painelAberto(el)){ $(el).addClass('in'); }
                atualizarChevron(heading, true);
            }
        });
        atualizarBotaoToggle();
    }

    // Salvar estado ao clicar no heading (aguarda BS3 concluir a transição)
    $('#filtrosAccordion').on('click', '.panel-heading', function(){
        setTimeout(function(){
            salvarEstado();
            atualizarBotaoToggle();
        }, 400);
    });

    // Toggle all — manipula classes diretamente (compatível BS3 e BS5)
    $('#toggleTodosAccordion').on('click', function(){
        var todosAbertos = true;
        paineis.forEach(function(id){
            var el = document.getElementById(id);
            if(el && !painelAberto(el)){
                todosAbertos = false;
            }
        });
        paineis.forEach(function(id){
            var el = document.getElementById(id);
            if(!el) return;
            var heading = $(el).prev('.panel-heading');
            if(todosAbertos){
                $(el).removeClass('in show');
                $(el).css('height', '');
                atualizarChevron(heading, false);
            } else {
                $(el).addClass('in show');
                $(el).css('height', '');
                atualizarChevron(heading, true);
            }
        });
        salvarEstado();
        atualizarBotaoToggle();
    });

    atualizarBotaoToggle();
});
})(jQuery);
</script>


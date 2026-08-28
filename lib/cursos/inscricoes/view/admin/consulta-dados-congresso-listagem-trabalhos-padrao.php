<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/consulta-dados-congresso-listagem-trabalho-cabecalho.php");

#require_once(dirname(__FILE__)."/../../view/admin/consulta-dados-congresso-busca.php");
echo "<div class=\"panel-body\">";

$fws_['ordenar']     = $fws_['ordenar']     == '' ? 'titulo_trabalho' : $fws_['ordenar'];
$fws_['situacao']    = $fws_['situacao']    == '' ? 'ATIVADA'      : $fws_['situacao'];
//$fws_['perspectiva'] = $fws_['perspectiva'] == '' ? 'simplificada' : $fws_['perspectiva'];
/*para evento com avaliação melhor já deixar a perspectiva no com padrão */
if($objEvConf->getTrabAvaliacao()==1){
    if (FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_GERENCIAR_AVALIACAO_TRABALHOS, 5, false, false)) {
        $fws_['listar'] = 1;
        $fws_['perspectiva'] = 'avaliacao';
    }
    if (FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_LISTAR_TRABALHOS, 5, false, false)) {
        $fws_['listar'] = 1;
        $fws_['perspectiva'] = 'detalhada';
    }
}

if (FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_LISTAR_TRABALHOS, 5, false, false)===false) {
    require_once(dirname(__FILE__)."/modulo-indisponivel.php");
    return;
}
// $fws_['perspectiva'] = $fws_['perspectiva'] == '' ? 'detalhada' : $fws_['perspectiva'];
$fws_['consultaPresenca']    = $fws_['consultaPresenca']    == '' ? ''             : $fws_['consultaPresenca'];
$fws_['consultaAnexoArqApresentacao'] = $fws_['consultaAnexoArqApresentacao']==''? '' : $fws_['consultaAnexoArqApresentacao'];
$fws_['consultaAnexoArqVideo']        = $fws_['consultaAnexoArqVideo']==''? '' : $fws_['consultaAnexoArqVideo'];
$fws_['consultaPremiados']    = $fws_['consultaPremiados']    == '' ? ''             : $fws_['consultaPremiados'];
$fws_['comunicado']  = $fws_['comunicado']  == '' ? '0'            : $fws_['comunicado'];
$fws_['acaoEmLote']  = $fws_['acaoEmLote']  == '' ? ''             : $fws_['acaoEmLote'];

if ($fws_['cod_area_tematica'] == ''){
    $fws_['cod_area_tematica'] = $fwsGet_['cod_area_tematica'];
    if($fws_['cod_area_tematica'])
        $fws_['listar'] = 1;
}
if ($fws_['nome_sala'] == ''){
    $fws_['nome_sala'] = $fwsGet_['nome_sala'];
    if($fws_['nome_sala'])
        $fws_['listar'] = 1;
}
if ($fws_['cod_inscrito'] == ''){
    $fws_['cod_inscrito'] = $fwsGet_['cod_inscrito'];
    if($fws_['cod_inscrito'])
        $fws_['listar'] = 1;
}
if ($fws_['estado'] == ''){
    $fws_['estado'] = $fwsGet_['estado'];
    if($fwsGet_['estado'])
        $fws_['listar'] = 1;
}
if ($fws_['pagamentos'] == ''){
    $fws_['pagamentos'] = $fwsGet_['pagamentos'];
    if($fwsGet_['pagamentos'])
        $fws_['listar'] = 1;
}
if ($fws_['avaliacao'] == ''){
    $fws_['avaliacao'] = $fwsGet_['avaliacao'];
    if($fwsGet_['avaliacao'])
        $fws_['listar'] = 1;
}
if(!$fws_['content']){
   $fws_['content'] = 'view/admin/consulta-dados-congresso-listagem-trabalhos.php'; 
}
?>
<form name="getdoc" method="post" action="<?php echo WB_URL_COMMAND?>" target="_blank">
    <input type="hidden" name="c" value="GerenciarUpload">
    <input type="hidden" name="m" value="abrirWord">
    <input type="hidden" value="" name="cod">
</form>
<form name="getAprovado" method="post" action="<?php echo WB_URL_COMMAND?>" target="_blank">
    <input type="hidden" name="c" value="GerenciarUpload">
    <input type="hidden" name="m" value="abrirPdf">
    <input type="hidden" value="" name="cod">
</form>
<form name="getApresentacaoVideo" method="post" action="<?php echo WB_URL_COMMAND?>" target="_blank">
    <input type="hidden" name="c" value="GerenciarUpload">
    <input type="hidden" name="m" value="abrirVideo">
    <input type="hidden" value="" name="cod">
</form>
<SCRIPT LANGUAGE="JavaScript" src="resources/js/avaliacao-trabalhos.js"></SCRIPT>
<script language="JavaScript" type="text/javascript">
$(document).ready(function () {
    $("table.tablesorter").tablesorter({widgets: ['zebra']})
//    .tablesorterPager({container: $("#pager")}); 
   
    $("input[type='text']").keypress(function(e){
        if(e.which == 13) {
            $('#formListaTrabalhos').submit();
        }
    });   
    $("#estado").change(function () {
        carregarCidades(this.value);
    });
    $('#exportarExcel').click(function(){
        $('#formListaTrabalhos').attr('action', '<?php echo WB_URL_COMMAND?>');
        $('#c').val('ListarTrabalhos');
        $('#m').val('carregarXls');
        $("input[name=media]").val(['xls']);
        $('#formListaTrabalhos').submit();
    });
    $('#exportarZip').click(function(){
        $('#formListaTrabalhos').attr('action', '<?php echo WB_URL_COMMAND?>');
        $('#c').val('ListarTrabalhos');
        $('#m').val('carregarZip');
        $("input[name=media]").val(['xls']);
        $('#formListaTrabalhos').submit();
    });
    $('#listarTela').click(function(){
        $('#formListaTrabalhos').attr('action', '<?php echo WB_URL_VIEW?>?content=<?php echo $fws_['content']?>');
        $('#c').val('');
        $('#m').val('');
        $("input[name=media]").val(['tela']);
        $('#formListaTrabalhos').submit();
    });
    $("input[name=perspectiva]").click(function(){
        if ($(this).val() == 'avaliacaoPremiacao' ){
             $("input[name=premiacao]").val(['SIM']);
             $("input[name=avaliacao]").val(['aprovado-indicado-premiacao']);
             $("input[name=ordenar]").val(['area_tematica']);
        }
    });
    $("#dataInclusaoAnexo").datetimepicker();
//    $('.presenca').live("click",function(){
//        var element = $(this);
//        inscritoConferido(element);
//    });
    // Chama a função verificarAcao quando um checkbox é alterado
    $('input[name="acaoEmLote"]').change(verificarAcao);
    verificarAcao();
    // $('#selectTaxas').change(function() {
    //     if ($(this).val() == '-1') {
    //         $('#camposExtras').show();
    //     } else {
    //         $('#camposExtras').hide();
    //     }
    // });    
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
           $("#email_subject").val(data.assunto);
           $("#email_template_body").val(data.corpo);
       }
       , error: function () {
           alert("Nao foi possivel obter informações:\n" + req.statusText);
       }
   });
}
function verificarAcao() {
    // Esconde as divs primeiro
    // $('#divSelectComunicados, #divSelectTaxas').hide();

    // Verifica cada checkbox marcado com o nome acaoLote
    $('input[name="acaoEmLote"]:checked').each(function() {
        var valorAcao = $(this).val();

        if(valorAcao === 'enviarComunicados') {
            $('#divSelectComunicados').show();
        } else if(valorAcao === 'gerarMensalidade') {
            $('#divSelectTaxas').show();
        }
    });
}
function alternarPresenca(codInscrito,codTrabalho){
    if(confirm('Deseja definir presença para o inscrito: '+codInscrito+' no trabalho: '+codTrabalho+'?')){
        var id_presenca= 'presenca-'+codTrabalho;
        $.ajax({
            type: "POST",
            url: '<?php echo  WB_URL_COMMAND?>&t='+Math.random(),
            data: ({'cod_inscrito':codInscrito,'cod_trabalho':codTrabalho, 'c':'GerenciarTrabalho', 'm':'alternarPresenca'}),
            dataType: "json",
            async: false,
            cache: false,
            success: function(data){
                $('#'+id_presenca).attr("checked", data.flgApresentado); 
                elementMsg = '#statusPresenca-'+codTrabalho
                processJsonElement(data,elementMsg);
            }
            ,timeout: 4000
            ,beforeSend: function() {
                $('presenca-'+codTrabalho).hide();
                loading('div#carregando-'+codTrabalho,'show');
            }
            ,error: function(ajax) {
                //showResponse(ajax);
            }
            ,complete: function() {        
                $('#presenca-'+codTrabalho).show();
                loading('div#carregando-'+codTrabalho,'hidden');
            }
        });
    }
}
function alternarFlgAssinado(codInscrito,codTrabalho){
    if(confirm('Deseja definir status da assinatura no documento: '+codTrabalho+'?')){
        var id_assinado= 'flgAssinado-'+codTrabalho;
        $.ajax({
            type: "POST",
            url: '<?php echo  WB_URL_COMMAND?>&t='+Math.random(),
            data: ({'cod_inscrito':codInscrito,'cod_trabalho':codTrabalho, 'c':'GerenciarTrabalho', 'm':'alternarFlgAssinado'}),
            dataType: "json",
            async: false,
            cache: false,
            success: function(data){
                $('#'+id_assinado).attr("checked", data.flgApresentado); 
                elementMsg = '#statusFlgAssinado-'+codTrabalho
                processJsonElement(data,elementMsg);
            }
            ,timeout: 4000
            ,beforeSend: function() {
                $('flgAssinado-'+codTrabalho).hide();
                loading('div#carregando-'+codTrabalho,'show');
            }
            ,error: function(ajax) {
                //showResponse(ajax);
            }
            ,complete: function() {        
                $('#flgAssinado-'+codTrabalho).show();
                loading('div#carregando-'+codTrabalho,'hidden');
            }
        });
    }
}
function definirPremiacao(codInscrito,codTrabalho){
    if(confirm('Deseja definir premiação para o trabalho: '+codTrabalho+' do inscrito: '+codInscrito+'?')){
        var id_premiacao = 'txt_premiacao_'+codTrabalho;
        var txt_premiacao = $('#'+id_premiacao).val();
//        alert(id_premiacao+'<->'+txt_premiacao);
        $.ajax({
            type: "POST",
            url: '<?php echo  WB_URL_COMMAND?>&t='+Math.random(),
            data: ({'cod_inscrito':codInscrito,'cod_trabalho':codTrabalho, 'c':'GerenciarAvaliacao', 'm':'definirPremiacao','txt_premiacao':txt_premiacao}),
            dataType: "json",
            async: false,
            cache: false,
            success: function(data){
//                processJson(data);
                elementTo = '#'+codTrabalho
                elementMsg = '#msgTrabPrem-'+codTrabalho
                elementMsgUT = '#msgUT-'+codTrabalho
                processJsonElement(data,elementMsg);
//                if (data.statusMessage==1){
//                    $(elementMsgUT).html(data.message+'<br>'+data.dataAlteracao);
//                }
            }
            ,timeout: 4000
            ,beforeSend: function() {
                loading('div#carregando'+codTrabalho,'show');
            }
            ,error: function(ajax) {
                //showResponse(ajax);
            }
            ,complete: function() {        
                loading('div#carregando'+codTrabalho,'hidden');
            }
        });
    }
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

function visualiza(cod){
    document.getdoc.cod.value=cod;
    document.getdoc.submit();
}
function getApresentacao(cod){
    getAprovado(cod);
}
function visualiza(cod){
    document.getdoc.cod.value=cod;
    document.getdoc.submit();
}
function getAprovado(cod){
    document.getAprovado.cod.value=cod;
    document.getAprovado.submit();
}
function getApresentacaoVideo(cod){
    document.getApresentacaoVideo.cod.value=cod;
    document.getApresentacaoVideo.submit();
}
    
function limparCampos(){
    //Limpando campos
    $('#nome_inscrito').val('');
    $('#cod_inscrito').val('');
    $('#titulo_trabalho').val('');
    $('#titulo_texto_trabalho').val('');
    $('#co_autor').val('');
    $('#e_mail').val('');
    $('#cpf').val('');
    $('#estado').val('');  
    $('#cidade').val('');  
    $('#cod_trabalho').val('');  
    $('#cod_avaliador').val('');   
    $('#cod_area_tematica').val('');  
    $('#formato_texto').val('');  
    $('#nome_sala').val('');  
    $('#patrocinador_voucher').val('');  
    $('#cod_avaliador').val('');  
    $("input[name=situacao]").val(['ATIVADA']);
    $("input[name=situacao_inscrito]").val(['ATIVADA']);
    $("input[name=presenca_inscrito]").val(['']);
    $("input[name=outros_filtros_inscrito]").val(['']);
    $("input[name=trabalhos]").val(['']);
    $("input[name=pagamentos]").val(['']);
    $("input[name=avaliacao]").val(['']);
    $("input[name=perspectiva]").val(['simplificada']);
    $("input[name=premiacao]").val(['']);
    $("input[name=premiado]").val(['']);
    $("input[name=flg_assinado]").val(['']);
    $('#tipo_documento').val(''); 
    $("input[name=ordenar]").val(['cod_inscrito']);
    $("input[name=comunicado]").val(['0']);
    $("input[name=consultaPresenca]").val(['']);
    $("input[name=consultaAnexoArqApresentacao]").val(['']);
    $("input[name=consultaAnexoArqVideo]").val(['']);
    $("input[name=consultaPremiados]").val(['']);
    $("input[name=media]").val(['tela']);
    $('#formListaTrabalhos').attr('action', '<?php echo WB_URL_VIEW?>?content=<?php echo $fws_['content']?>');
    $('#c').val('');
    $('#m').val('');
}        
function enviarParaAvaliacao(codInscrito,codTrabalho){
    if(confirm('Deseja enviar o trabalho '+codTrabalho+' para avaliação?' )){
        $.ajax({
            type: "POST",
            url: '<?php echo  WB_URL_COMMAND?>&t='+Math.random(),
            data: ({'cod_inscrito':codInscrito,'cod_trabalho':codTrabalho, 'c':'GerenciarAvaliacao', 'm':'adminEnviarParaAvaliacao'}),
            dataType: "json",
            async: false,
            cache: false,
            success: function(data){
//                processJson(data);
                elementTo = '#'+codTrabalho
                elementMsg = '#msgTrabAval-'+codTrabalho
                elementMsgUT = '#msgUT-'+codTrabalho
                processJsonElement(data,elementMsg);
                if (data.statusMessage==1){
                    $(elementMsgUT).html(data.avaliacao+'<br>'+data.idInclusao+'<br>'+data.dataInclusao);
                }
            }
            ,timeout: 4000
            ,beforeSend: function() {
                loading('div#carregando'+codTrabalho,'show');
            }
            ,error: function(ajax) {
//                showResponse(ajax);
            }
            ,complete: function() {        
                loading('div#carregando'+codTrabalho,'hidden');
            }
        });
    }
}
function indicarAvaliador(codInscrito,codTrabalho){
    if(confirm('Deseja enviar o trabalho '+codTrabalho+' para avaliação?' )){
        $.ajax({
            type: "POST",
            url: '<?php echo  WB_URL_COMMAND?>&t='+Math.random(),
            data: ({'cod_inscrito':codInscrito,'cod_trabalho':codTrabalho, 'c':'GerenciarAvaliacao', 'm':'adminIndicarAvaliador'}),
            dataType: "json",
            async: false,
            cache: false,
            success: function(data){
//                processJson(data);
                elementTo = '#'+codTrabalho
                elementMsg = '#msgTrabAval-'+codTrabalho
                elementMsgUT = '#msgUT-'+codTrabalho
                processJsonElement(data,elementMsg);
                if (data.statusMessage==1){
                    $(elementMsgUT).html(data.avaliacao+'<br>'+data.idInclusao+'<br>'+data.dataInclusao);
                }
            }
            ,timeout: 4000
            ,beforeSend: function() {
                loading('div#carregando'+codTrabalho,'show');
            }
            ,error: function(ajax) {
//                showResponse(ajax);
            }
            ,complete: function() {        
                loading('div#carregando'+codTrabalho,'hidden');
            }
        });
    }
}
function retornarParaInscrito(codInscrito,codTrabalho,sitAvaliacao){
//    if(sitAvaliacao=='Não submetido' || sitAvaliacao=='Devolvido'  || sitAvaliacao==''){
//        alert('Trabalho já encontra-se com o autor.')
//        return false;
//    } 
    if(confirm('Deseja retornar o trabalho '+codTrabalho+' para o autor?')){
        $.ajax({
            type: "POST",
            url: '<?php echo  WB_URL_COMMAND?>&t='+Math.random(),
            data: ({'cod_inscrito':codInscrito,'cod_trabalho':codTrabalho, 'c':'GerenciarAvaliacao', 'm':'adminRetornarParaInscrito'}),
            dataType: "json",
            async: false,
            cache: false,
            success: function(data){
//                processJson(data);
                elementTo = '#'+codTrabalho
                elementMsg = '#msgTrabAval-'+codTrabalho
                elementMsgUT = '#msgUT-'+codTrabalho
                processJsonElement(data,elementMsg);
                if (data.statusMessage==1){
                    $(elementMsgUT).html(data.avaliacao+'<br>'+data.idInclusao+'<br>'+data.dataInclusao);
                }
            }
            ,timeout: 4000
            ,beforeSend: function() {
                loading('div#carregando'+codTrabalho,'show');
            }
            ,error: function(ajax) {
//                showResponse(ajax);
            }
            ,complete: function() {        
                loading('div#carregando'+codTrabalho,'hidden');
            }
        });
    }
}
/*Avaliação Padrão*/
function indicarAvaliador(codInscrito,codTrabalho,codAvaliador){
    if(confirm('Deseja indicar o avaliador: '+codAvaliador+' para avaliar o trabalho: '+codTrabalho+'?')){
        $.ajax({
            type: "POST",
            url: '<?php echo  WB_URL_COMMAND?>&t='+Math.random(),
            data: ({'cod_inscrito':codInscrito,'cod_trabalho':codTrabalho,'cod_avaliador':codAvaliador, 'c':'GerenciarAvaliacao', 'm':'adminIndicarAvaliador'}),
            dataType: "json",
            async: false,
            cache: false,
            success: function(data){
//                processJson(data);
                elementTo = '#'+codTrabalho
                elementMsg = '#msgTrabAval-'+codTrabalho
                elementMsgUT = '#msgUT-'+codTrabalho
                processJsonElement(data,elementMsg);
                if (data.statusMessage==1){
                    $(elementMsgUT).html(data.avaliacao+'<br>'+data.idInclusao+'<br>'+data.dataInclusao);
                }
            }
            ,timeout: 4000
            ,beforeSend: function() {
                loading('div#carregando'+codTrabalho,'show');
            }
            ,error: function(ajax) {
                //showResponse(ajax);
            }
            ,complete: function() {        
                loading('div#carregando'+codTrabalho,'hidden');
            }
        });
    }
}
function liberarDeAvaliador(codInscrito,codTrabalho,codAvaliador){
    if(codAvaliador==''){
        alert('Trabalho não esta vinculado a nenhum avaliador.')
        return false;
    }    
    if(confirm('Deseja liberar trabalho: '+codTrabalho+' do avaliador: '+codAvaliador+'?')){
        $.ajax({
            type: "POST",
            url: '<?php echo  WB_URL_COMMAND?>&t='+Math.random(),
            data: ({'cod_inscrito':codInscrito,'cod_trabalho':codTrabalho,'cod_avaliador':codAvaliador, 'c':'GerenciarAvaliacao', 'm':'adminLiberarDeAvaliador'}),
            dataType: "json",
            async: false,
            cache: false,
            success: function(data){
//                processJson(data);
                elementTo = '#'+codTrabalho
                elementMsg = '#msgTrabAval-'+codTrabalho
                elementMsgUT = '#msgUT-'+codTrabalho
                processJsonElement(data,elementMsg);
                if (data.statusMessage==1){
                    $(elementMsgUT).html(data.avaliacao+'<br>'+data.idInclusao+'<br>'+data.dataInclusao);
                }
            }
            ,timeout: 4000
            ,beforeSend: function() {
                loading('div#carregando'+codTrabalho,'show');
            }
            ,error: function(ajax) {
                //showResponse(ajax);
            }
            ,complete: function() {        
                loading('div#carregando'+codTrabalho,'hidden');
            }
        });
    }
}
function cobrarAvaliador(codInscrito,codTrabalho,codAvaliador){
    if(codAvaliador==''){
        alert('Trabalho não esta vinculado a nenhum avaliador.')
        return false;
    }    
    if(confirm('Deseja cobrar avaliação do trabalho: '+codTrabalho+' que esta com avaliador: '+codAvaliador+'?')){
        $.ajax({
            type: "POST",
            url: '<?php echo  WB_URL_COMMAND?>&t='+Math.random(),
            data: ({'codInscrito':codInscrito,'codTrabalho':codTrabalho,'codAvaliador':codAvaliador, 'c':'GerenciarAvaliacao', 'm':'cobrarAvaliador'}),
            dataType: "json",
            async: false,
            cache: false,
            success: function(data){
//                processJson(data);
                elementTo = '#'+codTrabalho
                elementMsg = '#msgTrabAval-'+codTrabalho
                elementMsgUT = '#msgUT-'+codTrabalho
                processJsonElement(data,elementMsg);
//                if (data.statusMessage==1){
//                    $(elementMsgUT).html(data.message);
//                }
            }
            ,timeout: 4000
            ,beforeSend: function() {
                loading('div#carregando'+codTrabalho,'show');
            }
            ,error: function(ajax) {
                //showResponse(ajax);
            }
            ,complete: function() {        
                loading('div#carregando'+codTrabalho,'hidden');
            }
        });
    }
}

/*Avaliação Premiacao*/
//function indicarAvaliadorPremiacao(codInscrito,codTrabalho){
//    if(confirm('Deseja enviar o trabalho '+codTrabalho+' para avaliação da premiação.?' )){
//        $.ajax({
//            type: "POST",
//            url: '<?php echo  WB_URL_COMMAND?>&t='+Math.random(),
//            data: ({'cod_inscrito':codInscrito,'cod_trabalho':codTrabalho, 'c':'GerenciarAvaliacaoPremiacao', 'm':'adminIndicarAvaliador'}),
//            dataType: "json",
//            async: false,
//            cache: false,
//            success: function(data){
////                processJson(data);
//                elementTo = '#'+codTrabalho
//                elementMsg = '#msgTrabAval-'+codTrabalho
//                elementMsgUT = '#msgUT-'+codTrabalho
//                processJsonElement(data,elementMsg);
//                if (data.statusMessage==1){
//                    htmlAnterior = $(elementMsgUT).text();
//                    $(elementMsgUT).html(data.avaliacao+'<br>'+data.idInclusao+'<br>'+data.dataInclusao+'<br>'+htmlAnterior);
//                }
//            }
//            ,timeout: 4000
//            ,beforeSend: function() {
//                loading('div#carregando'+codTrabalho,'show');
//            }
//            ,error: function(ajax) {
////                showResponse(ajax);
//            }
//            ,complete: function() {        
//                loading('div#carregando'+codTrabalho,'hidden');
//            }
//        });
//    }
//}
function liberarDeAvaliadorPremiacao(codInscrito,codTrabalho,codAvaliador){
    if(codAvaliador==''){
        alert('Premiação: Trabalho não esta vinculado a nenhum avaliador.')
        return false;
    }    
    if(confirm('Premiação: Deseja liberar trabalho: '+codTrabalho+' do avaliador: '+codAvaliador+'?')){
        $.ajax({
            type: "POST",
            url: '<?php echo  WB_URL_COMMAND?>&t='+Math.random(),
            data: ({'cod_inscrito':codInscrito,'cod_trabalho':codTrabalho,'cod_avaliador':codAvaliador, 'c':'GerenciarAvaliacaoPremiacao', 'm':'adminLiberarDeAvaliador'}),
            dataType: "json",
            async: false,
            cache: false,
            success: function(data){
//                processJson(data);
                elementTo = '#'+codTrabalho
                elementMsg = '#msgTrabAval-'+codTrabalho
                elementMsgUT = '#msgUT-'+codTrabalho+'-'+codAvaliador
//                processJsonElement(data,elementMsg);
                if (data.statusMessage==1){
                    $(elementMsgUT).html(data.message);
                }
            }
            ,timeout: 4000
            ,beforeSend: function() {
                loading('div#carregando'+codTrabalho,'show');
            }
            ,error: function(ajax) {
                //showResponse(ajax);
            }
            ,complete: function() {        
                loading('div#carregando'+codTrabalho,'hidden');
            }
        });
    }
}
function cobrarAvaliadorPremiacao(codInscrito,codTrabalho,codAvaliador){
    if(codAvaliador==''){
        alert('Premiação: Trabalho não esta vinculado a nenhum avaliador.')
        return false;
    }    
    if(confirm('Premiação:Deseja cobrar avaliação do trabalho: '+codTrabalho+' que esta com avaliador: '+codAvaliador+'?')){
        $.ajax({
            type: "POST",
            url: '<?php echo  WB_URL_COMMAND?>&t='+Math.random(),
            data: ({'codInscrito':codInscrito,'codTrabalho':codTrabalho,'codAvaliador':codAvaliador, 'c':'GerenciarAvaliacaoPremiacao', 'm':'cobrarAvaliador'}),
            dataType: "json",
            async: false,
            cache: false,
            success: function(data){
//                processJson(data);
                elementTo = '#'+codTrabalho
                elementMsg = '#msgTrabAval-'+codTrabalho
                elementMsgUT = '#msgUT-'+codTrabalho
                processJsonElement(data,elementMsg);
//                if (data.statusMessage==1){
//                    $(elementMsgUT).html(data.message);
//                }
            }
            ,timeout: 4000
            ,beforeSend: function() {
                loading('div#carregando'+codTrabalho,'show');
            }
            ,error: function(ajax) {
                //showResponse(ajax);
            }
            ,complete: function() {        
                loading('div#carregando'+codTrabalho,'hidden');
            }
        });
    }
}

function reprovarDireto(codInscrito,codTrabalho){
    if(confirm('Deseja REPROVAR o trabalho: '+codTrabalho+' do inscrito: '+codInscrito+'?')){
        $.ajax({
            type: "POST",
            url: '<?php echo  WB_URL_COMMAND?>&t='+Math.random(),
            data: ({'cod_inscrito':codInscrito,'cod_trabalho':codTrabalho,'c':'GerenciarAvaliacao', 'm':'adminReprovarDireto'}),
            dataType: "json",
            async: false,
            cache: false,
            success: function(data){
//                processJson(data);
                elementTo = '#'+codTrabalho
                elementMsg = '#msgTrabAval-'+codTrabalho
                elementMsgUT = '#msgUT-'+codTrabalho
                processJsonElement(data,elementMsg);
                if (data.statusMessage==1){
                    $(elementMsgUT).html(data.avaliacao+'<br>'+data.idInclusao+'<br>'+data.dataInclusao);
                }
            }
            ,timeout: 4000
            ,beforeSend: function() {
                loading('div#carregando'+codTrabalho,'show');
            }
            ,error: function(ajax) {
                //showResponse(ajax);
            }
            ,complete: function() {        
                loading('div#carregando'+codTrabalho,'hidden');
            }
        });
    }
}
function aprovarDireto(codInscrito,codTrabalho){
    if(confirm('Deseja APROVAR o trabalho: '+codTrabalho+' do inscrito: '+codInscrito+'?')){
        $.ajax({
            type: "POST",
            url: '<?php echo  WB_URL_COMMAND?>&t='+Math.random(),
            data: ({'cod_inscrito':codInscrito,'cod_trabalho':codTrabalho, 'c':'GerenciarAvaliacao', 'm':'adminAprovarDireto'}),
            dataType: "json",
            async: false,
            cache: false,
            success: function(data){
//                processJson(data);
                elementTo = '#'+codTrabalho
                elementMsg = '#msgTrabAval-'+codTrabalho
                elementMsgUT = '#msgUT-'+codTrabalho
                processJsonElement(data,elementMsg);
                if (data.statusMessage==1){
                    $(elementMsgUT).html(data.avaliacao+'<br>'+data.idInclusao+'<br>'+data.dataInclusao);
                }
            }
            ,timeout: 4000
            ,beforeSend: function() {
                loading('div#carregando'+codTrabalho,'show');
            }
            ,error: function(ajax) {
                //showResponse(ajax);
            }
            ,complete: function() {        
                loading('div#carregando'+codTrabalho,'hidden');
            }
        });
    }
}
function alterarIndicadoComissao(codInscrito,codTrabalho){
    if(confirm('Deseja ALTERAR indicação de premiação do trabalho: '+codTrabalho+' do inscrito: '+codInscrito+'?')){
        $.ajax({
            type: "POST",
            url: '<?php echo  WB_URL_COMMAND?>&t='+Math.random(),
            data: ({'cod_inscrito':codInscrito,'cod_trabalho':codTrabalho, 'c':'GerenciarAvaliacao', 'm':'alterarIndicadoComissao'}),
            dataType: "json",
            async: false,
            cache: false,
            success: function(data){
//                processJson(data);
                elementTo = '#'+codTrabalho
                elementMsg = '#msgTrabAval-'+codTrabalho
                elementMsgUT = '#msgUT-'+codTrabalho+'-'+codAvaliador
//                processJsonElement(data,elementMsg);
                if (data.statusMessage==1){
                    $(elementMsgUT).html(data.message);
                }
            }
            ,timeout: 4000
            ,beforeSend: function() {
                loading('div#carregando'+codTrabalho,'show');
            }
            ,error: function(ajax) {
                //showResponse(ajax);
            }
            ,complete: function() {        
                loading('div#carregando'+codTrabalho,'hidden');
            }
        });
    }
}

</script>
<fildset>
    <?php
    require_once(dirname(__FILE__)."/../../view/admin/consulta-dados-congresso-listagem-abas.php");    ?>
    <br>
    <?php if ($fws_['perspectiva']!='avaliacao'){ ?>
    <form method="post" id="formListaTrabalhos" action="<?php echo WB_URL_VIEW?>?content=<?php echo $fws_['content']?>#rsltdoListaTrabalhos">
        <input type="button" value="Limpar Campos" onclick="javascript:limparCampos();" class="btn btn-xs btn-primary"> 
        <div class="form-group col-sm-12 cnslt-padrao" style="padding-left: 0px;">
            <label for="perpectiva">Visualização: </label>
            &nbsp;<input type='radio' id='perspectiva' name='perspectiva' value='simplificada' <?php echo verifItemChecked($fws_['perspectiva'], 'simplificada') ?> > Simplificada
            &nbsp;<input type='radio' id='perspectiva' name='perspectiva' value='detalhada'    <?php echo verifItemChecked($fws_['perspectiva'], 'detalhada') ?> > Detalhada

            <?php if( $objEvConf->getTrabAvaliacao() == 1 ){ ?>
                &nbsp;<input type='radio' id='perspectiva' name='perspectiva' value='avaliacao'    <?php echo verifItemChecked($fws_['perspectiva'], 'avaliacao') ?> > Avaliação
            <?php } ?>

            <?php if ($objEvConf->getEmitirAnais() == 1) {?>
                &nbsp;<input type='radio' id='perspectiva' name='perspectiva' value='avaliacaoPremiacao' <?php echo verifItemChecked($fws_['perspectiva'], 'avaliacaoPremiacao') ?> > Avaliação para Premiação
                &nbsp;<input type='radio' id='perspectiva' name='perspectiva' value='anais'        <?php echo verifItemChecked($fws_['perspectiva'], 'anais') ?> > Anais
                &nbsp;<input type='radio' id='perspectiva' name='perspectiva' value='ensalamento'  <?php echo verifItemChecked($fws_['perspectiva'], 'ensalamento') ?> > Ensalamento
            <?php } ?>
            
            <!-- &nbsp;<input type='radio' id='perspectiva' name='perspectiva' value='listarArquivos'  <?php echo verifItemChecked($fws_['perspectiva'], 'listarArquivos') ?> > Listar Arquivos -->

        </div>
        <div class="form-group col-sm-12 cnslt-padrao" style="padding-left: 0px;">
            <a href="<?php echo WB_URL_VIEW?>?content=view/admin/consulta-dados-congresso-graficos-trabalhos.php">Painel de Bordo de <?php echo $objEvConf->getTrabAnexoTipo()!=''? $objEvConf->getTrabAnexoTipo() : 'Trabalhos' ?></a>
        </div>
        <div class="form-group col-sm-12 cnslt-padrao" style="padding-left: 0px;">
            <!--<label for="nome_inscrito">Nome Inscrito:</label>-->
            <input type="text" id="nome_inscrito" name="nome_inscrito" value="<?php echo $fws_['nome_inscrito'] ?>"
                   class="form-control" placeholder="Nome do Inscrito"
                   SIZE='60' maxlength='255' onBlur='trim(this);'>
        </div>
        <div class="form-group col-sm-3 cnslt-padrao" style="padding-left: 0px;">
            <!--<label for="cod_inscrito">Cód. Inscrito:</label>-->
            <input type="text" id="cod_inscrito" name="cod_inscrito" value="<?php echo $fws_['cod_inscrito'] ?>"
                   class="form-control" placeholder="Cód. do Inscrito"
                   SIZE='60' maxlength='255' onBlur='trim(this);' onkeypress='javascript: return isNum(getKey(event));'>
        </div>
        <div class="form-group col-sm-3 cnslt-padrao" style="padding-left: 0px;">
            <!--<label for="cod_trabalho">Cód. Trabalho:</label>-->
            <input type="text" id="cod_trabalho" name="cod_trabalho" value="<?php echo $fws_['cod_trabalho'] ?>"
                   class="form-control" placeholder="Cód. do Trabalho"
                   SIZE='60' maxlength='255' onBlur='trim(this);' onkeypress='javascript: return isNum(getKey(event));'>
        </div>
        <div class="form-group col-sm-3 cnslt-padrao" style="padding-left: 0px;">
            <!--<label for="e_mail">E-mail:</label>-->
            <input type="text" id="e_mail" name="e_mail" value="<?php echo $fws_['e_mail'] ?>"
                   class="form-control" placeholder="E-mail do Inscrito"
                   SIZE='60' maxlength='255' onBlur='trim(this);'>
        </div>
        <div class="form-group col-sm-3 cnslt-padrao" style="padding-left: 0px;">
            <!--<label for="cpf">Cpf:</label>-->
            <input type="text" id="cpf" name="cpf" value="<?php echo $fws_['cpf'] ?>"
                   class="form-control" placeholder="Cpf do Inscrito"
                   SIZE='60' maxlength='15' onBlur='trim(this);' onkeypress='javascript: return isNum(getKey(event));'>
        </div>
        <div class="form-group col-sm-8 cnslt-padrao" style="padding-left: 0px;">
            <!--<label for="titulo_trabalho">Titulo Trabalho:</label>-->
            <input type="text" id="titulo_trabalho" name="titulo_trabalho" value="<?php echo $fws_['titulo_trabalho'] ?>"
                   class="form-control" placeholder="Titulo do Trabalho"
                   SIZE='60' maxlength='255' onBlur='trim(this);' onkeypress='javascript: return isAlfa(getKey(event));'>
        </div>
        <div class="form-group col-sm-8 cnslt-padrao" style="padding-left: 0px;">
            <input type="text" id="titulo_texto_trabalho" name="titulo_texto_trabalho" value="<?php echo $fws_['titulo_texto_trabalho'] ?>"
                   class="form-control" placeholder="Titulo, Texto e Palavras-Chave do Trabalho"
                   SIZE='60' maxlength='255' onBlur='trim(this);' onkeypress='javascript: return isAlfa(getKey(event));'>
        </div>
        <div class="form-group col-sm-4 cnslt-padrao" style="padding-left: 0px;">
            <!--<label for="titulo_trabalho">Titulo Trabalho:</label>-->
            <input type="text" id="co_autor" name="co_autor" value="<?php echo $fws_['co_autor'] ?>"
                   class="form-control" placeholder="Co-Autor"
                   SIZE='60' maxlength='255' onBlur='trim(this);' onkeypress='javascript: return isAlfa(getKey(event));'>
        </div>
        <div class="form-group col-sm-3 cnslt-padrao" style="padding-left: 0px;">
            <label for="estado">Selecione Estado: </label>
            <select name="estado" id="estado" class="form-control">
                <option></option>
                <?php CarregarOptionsAC::estados($fws_, $debug); ?>
            </select>
        </div>
        <div class="form-group col-sm-9 cnslt-padrao" style="padding-left: 0px;">
            <label for="cidade">Selecione Cidade: </label>
            <select name="cidade" id="cidade" class="form-control">
                <option></option>
                <?php CarregarOptionsAC::cidades($fws_, $debug); ?>
            </select>
        </div>        
        <?php if ($objEvConf->getTrabEixoTematico()==1) { ?>
        <div class="form-group col-sm-12 cnslt-padrao" style="padding-left: 0px;">
            <label for="cod_area_tematica">Selecione Eixo Temático: </label>
            <select name="cod_area_tematica" class="form-control">
                <option></option>
                <?php 
                try {
                    CarregarOptionsAC::areasTematicas($fws_, $debug);
                } catch (Exception $exc) {
                    //echo $exc->getTraceAsString();
                }
                ?>
            </select>
        </div>
        <?php } ?>
        <div class="form-group col-sm-12 cnslt-padrao" style="padding-left: 0px;">
            <label for="formato_texto">Selecione Formato do Texto: </label>
            <select name="formato_texto" id="formato_texto" class="form-control">
                <option></option>
                <?php CarregarOptionsAC::formatoTexto($fws_, $debug); ?>
            </select>
        </div>
        <div class="form-group col-sm-12 cnslt-padrao" style="padding-left: 0px;">
            <label for="patrocinador_voucher">Selecione Patrocinadores de Vouchers: </label> (Gerados, Utilizados, Patrocinador)
            <select name="patrocinador_voucher" id="formato_texto" class="form-control">
                <option></option>
                <?php CarregarOptionsAC::patrocinadoresVouchers($fws_, $debug); ?>
            </select>
        </div>
        <?php if (COD_EVENTO == 2) { ?>
        <div class="form-group col-sm-12" style="padding-left: 0px;">
            <div class="form-group col-sm-3" style="padding-left: 0px;">
                <label for="premiacao"><b>Prêmio Inova Saúde Paraná</b>: </label> 
            </div>    
            <div class="form-group col-sm-1" style="padding-left: 0px;">
                <input type='radio' name='premiacao' value='' <?php echo verifItemChecked($fws_['premiacao'], '') ?> >
                Todos
            </div>
            <div class="form-group col-sm-3" style="padding-left: 0px;">
                <input type='radio' name='premiacao' value='NAO' <?php echo verifItemChecked($fws_['premiacao'], 'NAO') ?> >
                Não escolheu participar
            </div>
            <div class="form-group col-sm-3" style="padding-left: 0px;">
                <input type='radio' name='premiacao' value='SIM' <?php echo verifItemChecked($fws_['premiacao'], 'SIM') ?> >
                Sim, escolheu participar
            </div>
            <div class="form-group col-sm-2" style="padding-left: 0px;"></div>
        </div>
        <?php } ?>
        <?php if (COD_EVENTO == 1) { ?>
        <div class="form-group col-sm-12" style="padding-left: 0px;">
            <label for="premiacao"><b>Prêmio Jovens Melhoristas</b>? </label> 
        </div>
        <div class="form-group col-sm-12" style="padding-left: 0px;">
            <div class="form-group col-sm-3" style="padding-left: 0px;">
                <input type='radio' name='premiacao' value='NAO'         <?php echo verifItemChecked($fws_['premiacao'], 'NAO') ?> >  Não escolheu participar.
                <!--&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='radio' name='premiacao' value='1-PREMIACAO' <?php echo verifItemChecked($fws_['premiacao'], '1-PREMIACAO') ?> >-->
            </div>
            <div class="form-group col-sm-3" style="padding-left: 0px;">
                <input type='radio' name='premiacao' value='2-PREMIACAO' <?php echo verifItemChecked($fws_['premiacao'], '2-PREMIACAO') ?> >
                escolheu categoria Iniciação Científica.
            </div>
            <div class="form-group col-sm-3" style="padding-left: 0px;">
                <input type='radio' name='premiacao' value='3-PREMIACAO' <?php echo verifItemChecked($fws_['premiacao'], '3-PREMIACAO') ?> >
                escolheu categoria Dissertação de Mestrado.
            </div>
            <div class="form-group col-sm-3" style="padding-left: 0px;">
                <input type='radio' name='premiacao' value='4-PREMIACAO' <?php echo verifItemChecked($fws_['premiacao'], '4-PREMIACAO') ?> >
                escolheu categoria Tese de Doutorado.
            </div>
            <div class="form-group col-sm-12" style="padding-left: 0px;">
                <input type='radio' name='premiado' value='1' <?php echo verifItemChecked($fws_['premiado'], '1') ?> >
                Listar somente trabalhos premiados em qualque categoria.
                &nbsp;&nbsp;&nbsp;&nbsp;
                <input type='radio' name='premiado' value='' <?php echo verifItemChecked($fws_['premiado'], '') ?> >
                Listar Todos.
            </div>
        </div>
        <?php } ?>
        <?php if ($fws_['perspectiva'] == 'avaliacao') { ?>
        <div class="form-group col-sm-12 cnslt-padrao" style="padding-left: 0px;">
            <label for="cod_avaliador">Selecione por Avaliador da Primeira Fase de Avaliaçao: </label>( Designados - Pendentes - Nome Avalador - Login )
            <select name="cod_avaliador" id="cod_avaliador" class="form-control">
                <option></option>
                <?php CarregarOptionsAC::avaliadores($fws_, $debug); ?>
            </select>
        </div>
        <?php } ?>
        <?php if ($fws_['perspectiva'] == 'avaliacaoPremiacao') { ?>
        <div class="form-group col-sm-12 cnslt-padrao" style="padding-left: 0px;">
            <label for="cod_avaliador">Selecione por Avaliador da Premiação: </label>( Designados - Pendentes - Nome Avalador - Login )
            <select name="cod_avaliador" id="cod_avaliador" class="form-control">
                <option></option>
                <?php CarregarOptionsAC::avaliadoresPremiacao($fws_, $debug); ?>
            </select>
        </div>
        <?php } ?>
        <div class="form-group col-sm-12" style="padding-left: 0px;">
            <label for="avaliacao" >Situação na Primeira Fase de Avaliaçao: </label>
        </div>
        <div class="form-group col-sm-12" style="padding-left: 0px;">
            <div class="col-sm-2">
                &nbsp;&nbsp;<input type='radio' name='avaliacao' value=''
                <?php echo verifItemChecked($fws_['avaliacao'], '') ?> > Todos
            </div>
            <div class="col-sm-3">
                &nbsp;&nbsp;<input type='radio' name='avaliacao' value='aprovado'
                <?php echo verifItemChecked($fws_['avaliacao'], 'aprovado') ?> > Aprovado
            </div>
            <div class="col-sm-3">
                &nbsp;&nbsp;<input type='radio' name='avaliacao' value='reprovado'
                <?php echo verifItemChecked($fws_['avaliacao'], 'reprovado') ?> > Reprovado
            </div>
            <div class="col-sm-3">
                &nbsp;&nbsp;<input type='radio' name='avaliacao' value='devolvido'
                <?php echo verifItemChecked($fws_['avaliacao'], 'devolvido') ?> > Devolvido
            </div>
                
            <div class="col-sm-3">
                &nbsp;&nbsp;<input type='radio' name='avaliacao' value='selecionado'
                <?php echo verifItemChecked($fws_['avaliacao'], 'selecionado') ?> > Sendo Avaliado
            </div>
            <div class="col-sm-3">
                &nbsp;&nbsp;<input type='radio' name='avaliacao' value='nao_enviou_avaliacao'
                <?php echo verifItemChecked($fws_['avaliacao'], 'nao_enviou_avaliacao') ?> > Não Submetido
            </div>
            <div class="col-sm-2">            
                &nbsp;&nbsp;<input type='radio' name='avaliacao' value='re_submetido'
                <?php echo verifItemChecked($fws_['avaliacao'], 're_submetido') ?> > Re-Submetido
            </div>
            <div class="col-sm-4">
                &nbsp;&nbsp;<input type='radio' name='avaliacao' value='submetido'
                <?php echo verifItemChecked($fws_['avaliacao'], 'submetido') ?> > Submetido Sem Avaliador
            </div>
<!--
            <div class="col-sm-2">
                &nbsp;&nbsp;<input type='radio' name='avaliacao' value='cancelado'
                <?php echo verifItemChecked($fws_['avaliacao'], 'cancelado') ?> > Cancelado
            </div>
-->
            <div class="col-sm-4">
                &nbsp;&nbsp;<input type='radio' name='avaliacao' value='aprovado-indicado-premiacao'
                <?php echo verifItemChecked($fws_['avaliacao'], 'aprovado-indicado-premiacao') ?> > Aprov.e.Indicado.Premiação
            </div>
            <div class="col-sm-4">
                &nbsp;&nbsp;<input type='radio' name='avaliacao' value='aprovado-nao-indicado-premiacao'
                <?php echo verifItemChecked($fws_['avaliacao'], 'aprovado-nao-indicado-premiacao') ?> > Aprov.e.Não.Indicado.Premiação
            </div>
            
        </div>
        <?php if ($fws_['perspectiva']=='ensalamento') { ?>
        <div class="form-group col-sm-4 cnslt-padrao" style="padding-left: 0px;">
            <label for="formato_texto">Selecione sala: </label>
            <select name="nome_sala" id="nome_sala" class="form-control">
                <option></option>
                <?php CarregarOptionsAC::salas($fws_, $debug); ?>
            </select>
        </div>
        <?php } ?>
        <div class="form-group col-sm-12" style="padding-left: 0px;">
            <label for="situacao_inscrito">Situação Inscrito: </label>
            &nbsp;<input type='radio' name='situacao_inscrito' value='ATIVADA'     <?php echo verifItemChecked($fws_['situacao_inscrito'], 'ATIVADA') ?> > Ativada
            &nbsp;<input type='radio' name='situacao_inscrito' value='DESATIVADA'  <?php echo verifItemChecked($fws_['situacao_inscrito'], 'DESATIVADA') ?> > Desativada
            <!--&nbsp;<input type='radio' name='situacao_inscrito' value='CANCELADA'   <?php echo verifItemChecked($fws_['situacao_inscrito'], 'CANCELADA') ?> > Cancelada-->
            &nbsp;<input type='radio' name='situacao_inscrito' value='EXCLUIDA'    <?php echo verifItemChecked($fws_['situacao_inscrito'], 'EXCLUIDA') ?> > Excluída
            &nbsp;&nbsp;&nbsp;
             <label for="presenca_inscrito">Situação da Presença: </label>
            &nbsp;<input type='radio' name='presenca_inscrito' value=''    <?php echo verifItemChecked($fws_['presenca_inscrito'], '') ?> > Todos
            &nbsp;<input type='radio' name='presenca_inscrito' value='SIM'    <?php echo verifItemChecked($fws_['presenca_inscrito'], 'SIM') ?> > Presença Confirmada
            &nbsp;<input type='radio' name='presenca_inscrito' value='NAO'    <?php echo verifItemChecked($fws_['presenca_inscrito'], 'NAO') ?> > Presença Não Confirmada
            <br>
             <label for="outros_filtros_inscrito">Outras: </label>
            &nbsp;<input type='radio' name='outros_filtros_inscrito' value=''    <?php echo verifItemChecked($fws_['outros_filtros_inscrito'], '') ?> > Todos
            &nbsp;<input type='radio' name='outros_filtros_inscrito' value='avaliou_evento'    <?php echo verifItemChecked($fws_['outros_filtros_inscrito'], 'avaliou_evento') ?> > Avaliou Evento
            <br>
            <?php if (FWA_Autenticacao($_SESSION['_cod_usuario'], 999, 5, false, false)) { ?>
            <label for="flg_assinado">Assinados: </label>
            &nbsp;<input type='radio' name='flg_assinado' value=''    <?php echo verifItemChecked($fws_['flg_assinado'], '') ?> > Todos
            &nbsp;<input type='radio' name='flg_assinado' value='1'    <?php echo verifItemChecked($fws_['flg_assinado'], 'avaliou_evento') ?> > Assinados
            &nbsp;<input type='radio' name='flg_assinado' value='2'    <?php echo verifItemChecked($fws_['flg_assinado'], 'avaliou_evento') ?> > Não Assinados
            <br>  
            <div class="form-group col-sm-4" style="padding-left: 0px;">
                <label for="formato_texto">Selecione tipo documento: </label>
                <select name="tipo_documento" id="tipo_documento" class="form-control">
                    <option></option>
                    <?php CarregarOptionsAC::tipoDocumento($fws_, $debug); ?>
                </select>
            </div>            
            <?php } ?>
        </div>
        <?php /** @todo Avaliação de Inscrito  */ ?>
        <?php if (in_array($objEvConf->getCodEvento(),[80,88,107,115])){   //TRABALHO_ANEXA_RESUMO ?>
        <div class="form-group col-sm-12" style="padding-left: 0px;">
            <label for="verificarArqAnexo">Anexaram Arquivo: </label>
            &nbsp;&nbsp;<input type='radio' name='verificarArqAnexo' value=''             <?php echo verifItemChecked($fws_['verificarArqAnexo'], '') ?> > Todos
            &nbsp;&nbsp;<input type='radio' name='verificarArqAnexo' value='anexaram'     <?php echo verifItemChecked($fws_['verificarArqAnexo'], 'anexaram') ?> > Somente quem anexou
            &nbsp;&nbsp;<input type='radio' name='verificarArqAnexo' value='nao_anexaram' <?php echo verifItemChecked($fws_['verificarArqAnexo'], 'nao_anexaram') ?> > Somente quem NÃO anexou
            <br>
            &nbsp;&nbsp;
            <label for="dataInclusaoAnexo">Data de Inclusão do Anexo a Partir de:
                <input type="text" id="dataInclusaoAnexo" name="dataInclusaoAnexo" value="<?php echo $fws_['dataInclusaoAnexo']?>" style="width: 200px;float: right" 
                   class="form-control" ></label>
        </div>
        <?php } ?>        
        <div class="form-group col-sm-12" style="padding-left: 0px;">
            <label for="consultaPresenca">Apresentaram Trabalho: </label>
            &nbsp;&nbsp;<input type='radio' name='consultaPresenca' value=''                 <?php echo verifItemChecked($fws_['consultaPresenca'], '') ?> > Todos
            &nbsp;&nbsp;<input type='radio' name='consultaPresenca' value='compareceram'     <?php echo verifItemChecked($fws_['consultaPresenca'], 'compareceram') ?> > Somente quem apresentou
            &nbsp;&nbsp;<input type='radio' name='consultaPresenca' value='naoCompareceram'  <?php echo verifItemChecked($fws_['consultaPresenca'], 'naoCompareceram') ?> > Somente quem NÃO apresentou
        </div>
        <?php if ($objEvConf->getApresentacaoAnexar()==1){ ?>
        <div class="form-group col-sm-12" style="padding-left: 0px;">
            <label for="consultaAnexoArqApresentacao">Anexaram Apresentação do Trabalho: </label>
            &nbsp;&nbsp;<input type='radio' name='consultaAnexoArqApresentacao' value=''             <?php echo verifItemChecked($fws_['consultaAnexoArqApresentacao'], '') ?> > Todos
            &nbsp;&nbsp;<input type='radio' name='consultaAnexoArqApresentacao' value='anexaram'     <?php echo verifItemChecked($fws_['consultaAnexoArqApresentacao'], 'anexaram') ?> > Somente quem anexou
            &nbsp;&nbsp;<input type='radio' name='consultaAnexoArqApresentacao' value='nao_anexaram' <?php echo verifItemChecked($fws_['consultaAnexoArqApresentacao'], 'nao_anexaram') ?> > Somente quem NÃO anexou
            <br>
            &nbsp;&nbsp;
            <label for="dataInclusaoAnexo">Data de Inclusão do Anexo a Partir de:
                <input type="text" id="dataInclusaoAnexo" name="dataInclusaoAnexo" value="<?php echo $fws_['dataInclusaoAnexo']?>" style="width: 200px;float: right" 
                   class="form-control" ></label>
        </div>
        <?php } ?>
        <?php if ($objEvConf->getApresentacaoAnexarVideo()==1){ ?>
        <div class="form-group col-sm-12" style="padding-left: 0px;">
            <label for="consultaAnexoArqVideo">Anexaram Vídeo do Trabalho: </label>
            &nbsp;&nbsp;<input type='radio' name='consultaAnexoArqVideo' value=''             <?php echo verifItemChecked($fws_['consultaAnexoArqVideo'], '') ?> > Todos
            &nbsp;&nbsp;<input type='radio' name='consultaAnexoArqVideo' value='anexaram'     <?php echo verifItemChecked($fws_['consultaAnexoArqVideo'], 'anexaram') ?> > Somente quem anexou
            &nbsp;&nbsp;<input type='radio' name='consultaAnexoArqVideo' value='nao_anexaram' <?php echo verifItemChecked($fws_['consultaAnexoArqVideo'], 'nao_anexaram') ?> > Somente quem NÃO anexou
        </div>
        <?php } ?>
        <?php if ($objEvConf->getTrabPremiacao()==1){ ?>
        <div class="form-group col-sm-12" style="padding-left: 0px;">
            <label for="consultaPremiados">Trabalhos Premiados: </label>
            &nbsp;&nbsp;<input type='radio' name='consultaPremiados' value=''           <?php echo verifItemChecked($fws_['consultaPremiados'], '') ?> > Todos
            &nbsp;&nbsp;<input type='radio' name='consultaPremiados' value='indicado_pela_comissao' <?php echo verifItemChecked($fws_['consultaPremiados'], 'indicado_pela_comissao') ?> > Somente Indicados pela Comissão
            &nbsp;&nbsp;<input type='radio' name='consultaPremiados' value='premiados'  <?php echo verifItemChecked($fws_['consultaPremiados'], 'premiados') ?> > Somente Premiados
        </div>
        <?php } ?>
        <div class="form-group col-sm-12 cnslt-padrao" style="padding-left: 0px;">
            <label for="situacao">Situação Trabalho: </label>
            &nbsp;&nbsp;<input type='radio' name='situacao' value='ATIVADA'       <?php echo verifItemChecked($fws_['situacao'], 'ATIVADA') ?> > Ativada
            &nbsp;&nbsp;<input type='radio' name='situacao' value='DESATIVADA'    <?php echo verifItemChecked($fws_['situacao'], 'DESATIVADA') ?> > Desativada
            <!--&nbsp;&nbsp;<input type='radio' name='situacao' value='CANCELADA' <?php echo verifItemChecked($fws_['situacao'], 'CANCELADA') ?> > Cancelada-->
            &nbsp;&nbsp;<input type='radio' name='situacao' value='EXCLUIDA'      <?php echo verifItemChecked($fws_['situacao'], 'EXCLUIDA') ?> > Excluída
        </div>   
        <div class="form-group col-sm-12 cnslt-padrao" style="padding-left: 0px;">
            <label for="pagamentos">Qtd.Pagamentos: </label>
            &nbsp;<input type='radio' name='pagamentos' value=''    <?php echo verifItemChecked($fws_['pagamentos'], '') ?> > Todos 
            &nbsp;<input type='radio' name='pagamentos' value='-1'    <?php echo verifItemChecked($fws_['pagamentos'], '-1') ?> > Não pagos 
            &nbsp;<input type='radio' name='pagamentos' value='98'    <?php echo verifItemChecked($fws_['pagamentos'], '98') ?> > Isentos Indicados
            &nbsp;<input type='radio' name='pagamentos' value='100'    <?php echo verifItemChecked($fws_['pagamentos'], '100') ?> > Isentos por Vouchers
            &nbsp;<input type='radio' name='pagamentos' value='ISENTO-PERFIL'    <?php echo verifItemChecked($fws_['pagamentos'], 'ISENTO-PERFIL') ?> > Isentos por Perfil
            &nbsp;<input type='radio' name='pagamentos' value='99'    <?php echo verifItemChecked($fws_['pagamentos'], '99') ?> > Pagos 
            &nbsp;<input type='radio' name='pagamentos' value='1'    <?php echo verifItemChecked($fws_['pagamentos'], '1') ?> > 1
            &nbsp;<input type='radio' name='pagamentos' value='2'    <?php echo verifItemChecked($fws_['pagamentos'], '2') ?> > 2
            &nbsp;&nbsp;&nbsp;&nbsp;
        </div>
        <div class="form-group col-sm-12 cnslt-padrao" style="padding-left: 0px;">
            <label for="pagamentos">Qtd.Trabalhos: </label>
            &nbsp;&nbsp;<input type='radio' name='trabalhos' value=''    <?php echo verifItemChecked($fws_['trabalhos'], '') ?> > Todos 
            &nbsp;&nbsp;<input type='radio' name='trabalhos' value='-1'  <?php echo verifItemChecked($fws_['trabalhos'], '-1') ?> > Nenhum 
            &nbsp;&nbsp;<input type='radio' name='trabalhos' value='1'   <?php echo verifItemChecked($fws_['trabalhos'], '1') ?> > Uma ou mais
            <!--&nbsp;&nbsp;<input type='radio' name='trabalhos' value='2'   <?php echo verifItemChecked($fws_['trabalhos'], '2') ?> > Duas ou mais-->
        </div>     
        <div class="form-group col-sm-12 cnslt-padrao" style="padding-left: 0px;">
            <label for="ordenar">Ordenar por: </label>
            &nbsp;&nbsp;<input type='radio' name='ordenar' value='titulo_trabalho'   <?php echo verifItemChecked($fws_['ordenar'], 'titulo_trabalho') ?> > Titulo
            &nbsp;&nbsp;<input type='radio' name='ordenar' value='cod_trabalho'      <?php echo verifItemChecked($fws_['ordenar'], 'cod_trabalho') ?> > Cód.Trabalho
            &nbsp;&nbsp;<input type='radio' name='ordenar' value='cod_inscrito'      <?php echo verifItemChecked($fws_['ordenar'], 'cod_inscrito') ?> > Cód.Inscrito
            &nbsp;&nbsp;<input type='radio' name='ordenar' value='inscrito'          <?php echo verifItemChecked($fws_['ordenar'], 'inscrito') ?> >Nome do Inscrito
            <?php if ($objEvConf->getTrabEixoTematico()==1) { ?>
            &nbsp;&nbsp;<input type='radio' name='ordenar' value='area_tematica' <?php echo verifItemChecked($fws_['ordenar'], 'area_tematica') ?> >Área Temática
            <?php } ?>
            <?php if ($objEvConf->getApresentacaoAnexar()==1) { ?>
            &nbsp;&nbsp;<input type='radio' name='ordenar' value='data_inclusao_arq_apresentacao' <?php echo verifItemChecked($fws_['ordenar'], 'data_inclusao_arq_apresentacao') ?> >Data do Anexo Decres.
            <?php } ?>
            &nbsp;&nbsp;<input type='radio' name='ordenar' value='pontos_total' <?php echo verifItemChecked($fws_['ordenar'], 'pontos_total') ?> >Pontos Informados
            <?php if ($objEvConf->getTrabPremiacao()==1) { ?>
            &nbsp;&nbsp;<input type='radio' name='ordenar' value='nota_premiacao' <?php echo verifItemChecked($fws_['ordenar'], 'nota_premiacao') ?> >Nota da Premiação
            <?php } ?>
        </div>
        <?php if ($fws_['perspectiva'] != 'avaliacao') { ?>
        <?php if (FWA_Autenticacao($_SESSION['_cod_usuario'], 999, 5, false, false)) { ?>
        <div class="form-group col-sm-12 cnslt-padrao" style="padding-left: 0px;">
            <label for="comunicado">Ação em Lote: </label>
            &nbsp;<input type='radio' name='acaoEmLote' value=''          checked> Nenhuma ação
            &nbsp;<input type='radio' name='acaoEmLote' value='enviarComunicados'> Enviar comunicados
            &nbsp;<input type='radio' name='acaoEmLote' value='definirPresenca'  > Definir presença
            &nbsp;<input type='radio' name='acaoEmLote' value='compactarVideos'         <?php echo verifItemChecked($fws_['acaoEmLote'], 'compactarVideos') ?> > compactar vídeos
            &nbsp;<input type='radio' name='acaoEmLote' value='compactarApresentacoes'  <?php echo verifItemChecked($fws_['acaoEmLote'], 'compactarApresentacoes') ?> > compactar apresentacoes
            <br>
            <div id="divSelectComunicados" style="display: none;margin-top: 10px;margin-bottom: 20px;" class="aba">
                <label for="comunicado">Enviar e-mails: </label>
                <select id="comunicado" name="comunicado">
                    <option value="0">Selecione um modelo</option>
                    <?php CarregarOptionsAC::listarOpcoesEmail($fws_,$debug); ?>
                </select>      
                <?php 
                if($fws_['email_template_body']==''){
$fws_['email_template_body'] = "Assunto: XXXXX
Caros Colaboradores e Parceiros,

Disponibilizamos este comunicado para pontuar sobre alguns procedimento administrativos referente ao Projeto de Extensão de Formação Continuada.
Para acesso completo ao comunicado, [clique aqui](Inserir Link Aqui) ou no link a seguir: [Inserir Link Aqui].

Agradecemos sua atenção e comprometimento contínuo com o projeto.

Atenciosamente
";
                }
                if($fws_['email_subject']==''){
                    $fws_['email_subject'] = "Comunicado";
                }
                ?>
                <a class='link' style="margin-left: 10px;"
                href='index.php?content=view/admin/GerenciarEmailTemplate.php'>Gerenciar Email Templates</a>
                <br>
                <input type="text" name="email_subject" id="email_subject" class="form-control" placeholder="Assunto" value="<?php echo $fws_['email_subject'] ?>">
                <br>                
                <textarea name="email_template_body" id="email_template_body" cols="40" rows="10" class="form-control" placeholder="Texto da mensagem"><?php echo $fws_['email_template_body'] ?></textarea>
                <br>
                <input type="text" name="email_bcc" id="email_bcc" class="form-control" placeholder="Com cópia oculpa para" value="<?php echo $fws_['email_bcc'] ?>">
                <br>                 
                <input type="radio" id="acaoEmail" name="acaoEmail" value="enviarEmail">
                <label for="gerar">Enviar e-mail</label><br>
                  <input type="radio" id="acaoEmail" name="acaoEmail" value="testarEmail">
                <label for="testar">Testar e visualizar e-mail</label><br>  
            </div>
            <br>            
        <?php } ?>
        <?php } ?>    
        <div class="form-group col-sm-12 cnslt-padrao" style="padding-left: 0px;">
            <input type="hidden" value="1" name="listar">
            <input type='hidden' name='content' value='<?php echo  $content ?>'>
            <input type='hidden' id='c' name='c' value=''>
            <input type='hidden' id='m' name='m' value=''>
            <input type='hidden' id='cod_evento' name='cod_evento' value='<?php echo $objEv->getCodEvento() ?>'>
            <input type='hidden' id='media' name='media' value='tela'>
            <input type="button" id="listarTela" value="Listar na Tela" class="btn btn-xs btn-primary"> 
            &nbsp;&nbsp;&nbsp;&nbsp;
            <input type="button" id="exportarExcel" value="Exportar para Excel" class="btn btn-xs btn-primary"> 
            <?php
            $codigosEventos = [61, 70, 81, 116];
            if (in_array($objEv->getCodEvento(), $codigosEventos) || $objEvConf->getTrabDownloadZip() == 1) {
            ?>
            &nbsp;&nbsp;&nbsp;&nbsp;
            <input type="button" id="exportarZip" value="Exportar Zip com Lista em Html" class="btn btn-xs btn-primary">
            <?php } ?>
            &nbsp;&nbsp;&nbsp;&nbsp;
            <input type="button" value="Limpar Campos" onclick="javascript:limparCampos();" class="btn btn-xs btn-primary"> 
        </div>
    </form>
    <?php } ?>
</fildset>
<a name="rsltdoListaTrabalhos"></a>
<!--<input type="button" value="Marcar Presença para todos" onclick="javascript:marcarPresencaTodos();" class="btn btn-xs btn-primary">--> 
<hr style="width: 97%; color: black; height: 1px; background-color:black;" class="col-sm-12" />
<table class="tablesorter" width="550px" border="0">
    <thead>
    <?php if($fws_['perspectiva']=='anais'){?>
    <td colspan="3">Anais</td>
    <?php }else if($fws_['perspectiva']=='ensalamento'){?>
    <td colspan="3">Ensalemento</td>
    <?php }else if($fws_['perspectiva']=='listarArquivos'){?>
    <td colspan="3">Listar Arquivos</td>
    <?php }else{?>    
        <?php if ($fws_['perspectiva']!='avaliacao'){ ?>
            <th>Cód.<br>Inscrito</th>
            <th>Cód.<br>Trabalho</th>
        <?php } ?>
        <th>Titulo</th>
        <?php //if ($fws_['acaoEmLote']=='') {?>
            <th width="150px">Avaliacao / Ultima tramitação</th>
            <th>Ações Avaliação</th>
        <?php //} ?>
        <th>Situação</th>
        <?php if ($fws_['perspectiva']!='avaliacao' and $fws_['acaoEmLote']==''){ ?>
            <th>Pagamentos</th>
        <?php } ?>
    <?php }?>
    </thead>
    <tbody>    
<?php
try {
    if ($fws_['listar'] != 1) {
        throw new Exception("Para listar trabalhos, utilize o formulário acima e clique em <Aplicar Filtrar>.");
    }
    $objLT = new ListarTrabalhosAC();
//    if($ipLiberado){
//        $debug=true;
//    }
    if($fws_['perspectiva']=='anais'){
        $fws_['perspectiva']  = 'detalhada';
        $objLT->carregarListaTrabalhosAnais($fws_, $debug);
    }else
    if($fws_['perspectiva']=='ensalamento'){
        $objLT->carregarListaTrabalhosEnsalamento($fws_, $debug);
    }else
    if($fws_['perspectiva']=='listarArquivos'){
        $objLT->carregarListaTrabalhosListarArquivos($fws_, $debug);
    }else{
        $objLT->carregarListaTrabalhos($fws_, $debug);
    }
    
} catch (Exception $exc) {
    echo "<tr><td colspan=15>" . $exc->getMessage() . "<br>&nbsp;</td></tr>";
}
echo "</tbody></table>";
echo "<br><br>";
require_once(dirname(__FILE__)."/../../view/admin/consulta-dados-congresso-rodape.php");
?>
</div></div></div>
<script language="JavaScript" type="text/javascript">
    $(document).ready(function () {
//        $("#btnExport").click(function (e) {
//          e.preventDefault();
//          var table_div = document.getElementById('divTabela');   
//          // esse "\ufeff" é importante para manter os acentos         
//          var blobData = new Blob(['\ufeff'+table_div.outerHTML], { type: 'application/vnd.ms-excel' });
//          var url = window.URL.createObjectURL(blobData);
//          var a = document.createElement('a');
//          a.href = url;
//          a.download = 'Meu arquivo Excel'
//                a.click();
//            });
//        });  
//        
//        $('#modalIndicarAvaliador').dialog({
//            autoOpen: false,
//            width: 650,
//            position: [180,180],
//            resizable : false,
//            modal: true,
//            buttons: {
//                "Fechar": function() {
//                    ("#associa").hide();
//                    $("#associa").html('');
//                    $(this).dialog("close");
//                },
////                "Cadastrar Avaliador": function() {
////                    startLoading();
////                    $("#associa").hide();
////                    //document.location.href = '<?php echo  WB_URL_VIEW ?>?pagina=view/admin/GerenciarInscrito.php'
////                }
//            },
//            close: function() {
//                startLoading();
//                $("#associa").hide();
//                $("#associa").html('');
//                $(this).dialog("close");
//                //document.location.href = '<?php echo  WB_URL_VIEW ?>?pagina=view/admin/GerenciarInscrito.php';
//            }
//        });
    });
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
    function indicarAvaliador(codInscrito,codTrabalho){
        startLoading();
        $("#associa").html('');
        $("#associa").load('<?php echo  WB_URL_MODAL ?>?content=view/admin/GerenciarTrabalhoAvaliador.php'
                            , {'codInscrito':codInscrito, 'codTrabalho':codTrabalho}
                          )
//        $("#associa").show();
//        $('#modalIndicarAvaliador').dialog("open");
        stopLoading();
    }
    function indicarAvaliadorPremiacao(codInscrito,codTrabalho){
        startLoading();
        $("#associa").html('');
        $("#associa").load('<?php echo  WB_URL_MODAL ?>?content=view/admin/GerenciarTrabalhoAvaliadorPremiacao.php'
                            , {'codInscrito':codInscrito, 'codTrabalho':codTrabalho}
                          )
//        $("#associa").show();
//        $('#modalIndicarAvaliador').dialog("open");
        stopLoading();
    }
</script>
<!--<div id="dialog-form" title="Indicar Avaliador">
    <div id="associa"></div>
</div>-->
<!-- Button trigger modal -->
<!--<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalIndicarAvaliador" onclick="indicarAvaliador()">
  Launch demo modal
</button>-->

<!-- Modal -->
<div class="modal fade" id="modalIndicarAvaliador" tabindex="-1" role="dialog" aria-labelledby="modalIndicarAvaliadorLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="modalIndicarAvaliadorLabel">
                    Indicar Avaliador
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>       
                </h4>
            </div>
            <div class="modal-body">
                <div id="associa"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-xs btn-primary" data-dismiss="modal">Fechar</button>
                <!--<button type="button" class="btn btn-xs btn-primary">Save changes</button>-->
            </div>
        </div>
    </div>
</div> 
<!----------------------------- JQuery UI (jQuery e jQuery UI já carregados pelo index.php) ----------------------------->
<script type="text/javascript" src="resources/js/jquery/jquery.price_format.1.7.min.js"></script>
<link   rel="stylesheet"      href="resources/js/jquery.tablesorter/themes/blue/style.css" type="text/css" id="" media="print, projection, screen" />
<script type="text/javascript" src="resources/js/jquery.tablesorter/jquery.tablesorter.js"></script>
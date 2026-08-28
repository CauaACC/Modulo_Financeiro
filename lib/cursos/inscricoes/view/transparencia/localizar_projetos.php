<?php
require_once(dirname(__FILE__)."/../../include/config.php");
//echo '->'.$fws_['listar'].'<-'.__LINE__;
if ($fws_['numero_projeto'] == ''){
    $fws_['numero_projeto'] = $fwsGet_['numero_projeto'];
    //echo '->'.$fws_['listar'].'<-'.__LINE__;
    if($fwsGet_['numero_projeto'])
        $fws_['listar'] = 1;
}
//echo '->'.$fws_['listar'].'<-'.__LINE__;
// $fws_['listar'] = 1;
?>
<!-- jQuery já carregado no index.php -->
<link   rel="stylesheet"      href="resources/js/jquery.tablesorter/themes/blue/style.css" type="text/css" id="" media="print, projection, screen" />
<script type="text/javascript" src="resources/js/jquery.tablesorter/jquery.tablesorter.js"></script>
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
<script language="JavaScript" type="text/javascript">
var _sortCol = -1;
var _sortAsc = true;

function sortTable(tableId, colIndex, asc) {
    var tbody = $(tableId + ' tbody');
    var rows = tbody.find('tr').toArray();
    rows.sort(function(a, b) {
        var aVal = $(a).find('td').eq(colIndex).text().trim().toLowerCase();
        var bVal = $(b).find('td').eq(colIndex).text().trim().toLowerCase();
        var aNum = parseFloat(aVal.replace(/[^\d.,-]/g, '').replace(',', '.'));
        var bNum = parseFloat(bVal.replace(/[^\d.,-]/g, '').replace(',', '.'));
        if (!isNaN(aNum) && !isNaN(bNum)) return asc ? aNum - bNum : bNum - aNum;
        var aDate = aVal.match(/(\d{2})\/(\d{2})\/(\d{4})/);
        var bDate = bVal.match(/(\d{2})\/(\d{2})\/(\d{4})/);
        if (aDate && bDate) {
            var aD = new Date(aDate[3], aDate[2]-1, aDate[1]);
            var bD = new Date(bDate[3], bDate[2]-1, bDate[1]);
            return asc ? aD - bD : bD - aD;
        }
        return asc ? aVal.localeCompare(bVal) : bVal.localeCompare(aVal);
    });
    $.each(rows, function(i, row) { tbody.append(row); });
}

$(document).ready(function () {
    $(document).on('click', '.tbl-fin thead th[data-col]', function() {
        var col = parseInt($(this).data('col'));
        var tableId = '#' + $(this).closest('table').attr('id');
        if (_sortCol === col) { _sortAsc = !_sortAsc; } else { _sortCol = col; _sortAsc = true; }
        sortTable(tableId, col, _sortAsc);
        $(this).closest('thead').find('th').css('font-weight', '600');
        $(this).closest('thead').find('.sort-arrow').html('\u2191\u2193').css('color', '#ccc');
        $(this).css('font-weight', '700');
        $(this).find('.sort-arrow').html(_sortAsc ? '\u2191' : '\u2193').css('color', '#333');
    });

    $('#listarTela').click(function(){
        $('#formListaTransparencia').attr('action', '<?php echo WB_URL_VIEW?>?content=view/transparencia/localizar_projetos.php');
        $('#c').val('');
        $('#m').val('');
        $("input[name=media]").val(['tela']);
        $('#formListaTransparencia').submit();
    });
    // dentro do formulário com ID 'formListaTransparencia'
    $('#formListaTransparencia input, #formListaTransparencia select').keypress(function (e) {
        // Verifica se a tecla pressionada é a tecla Enter (código 13)
        if (e.which == 13) {
            // Previne o comportamento padrão do Enter (que pode ser submeter o form ou dar um erro)
            e.preventDefault();
            
            // Simula o clique no botão "Pesquisar" (que tem o ID 'listarTela')
            $('#listarTela').trigger('click');
        }
    });    
});
function carregarCidades(cod, selecionado)
{
   $.ajax({
       type: "POST",
       url: '<?php echo  WB_URL_COMMAND ?>',
       data: ({'uf': cod, 'c': 'carregarOptions', 'm': 'cidades', '_submit_check': '1'}),
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
    $('#tipo_busca').val('Projeto');
    $('#termo_busca').val('');
    $('#data_de').val('');
    $('#data_ate').val('');
    $('#tipo_pagamento').val('');
    $('#ordenar').val('vencimento_desc');
}        
</script>    
<?php require_once(dirname(__FILE__)."/menu-transparencia.php"); ?>

<div style="background:#fff; border:1px solid #e8e8e8; border-radius:12px; overflow:hidden; margin-top:10px;">
    <div style="display:flex; align-items:center; padding:12px 18px; border-bottom:1px solid #eee;">
        <h4 style="margin:0; font-size:16px;"><i class="fa fa-search"></i> Localizar Projetos</h4>
    </div>
    <div style="padding:12px 18px;">

    <form method="post" id="formListaTransparencia" action="<?php echo WB_URL_VIEW?>?content=view/transparencia/localizar_projetos.php">
        <div class="row" style="margin-bottom:8px;">
            <div class="col-sm-2">
                <label class="label-filtro">E-Protocolo:</label>
                <input type="text" id="e_protocolo" name="e_protocolo" value="<?php echo $fws_['e_protocolo'] ?>" class="form-control input-sm" placeholder="E-Protocolo">
            </div>
            <div class="col-sm-2">
                <label class="label-filtro">N&ordm; Projeto:</label>
                <input type="text" id="numero_projeto" name="numero_projeto" value="<?php echo $fws_['numero_projeto'] ?>" class="form-control input-sm" placeholder="N&ordm; do Projeto">
            </div>
            <div class="col-sm-3">
                <label class="label-filtro">T&iacute;tulo do Projeto:</label>
                <input type="text" id="proposta" name="proposta" value="<?php echo $fws_['proposta'] ?>" class="form-control input-sm" placeholder="T&iacute;tulo do Projeto">
            </div>
            <div class="col-sm-3">
                <label class="label-filtro">Coordenador:</label>
                <input type="text" id="coordenador" name="coordenador" value="<?php echo $fws_['coordenador'] ?>" class="form-control input-sm" placeholder="Coordenador">
            </div>
        </div>
        <div class="row" style="margin-bottom:8px;">
            <div class="col-sm-2">
                <label class="label-filtro">Per&iacute;odo De:</label>
                <input type="date" id="data_de_projeto" name="data_de_projeto" value="<?php echo $fws_['data_de_projeto'] ?>" class="form-control input-sm">
            </div>
            <div class="col-sm-2">
                <label class="label-filtro">At&eacute;:</label>
                <input type="date" id="data_ate_projeto" name="data_ate_projeto" value="<?php echo $fws_['data_ate_projeto'] ?>" class="form-control input-sm">
            </div>
        </div>
        <div style="margin-top:10px;">
            <input type="hidden" value="1" name="listar">
            <input type="hidden" name="content" value="<?php echo $content ?>">
            <input type="hidden" id="c" name="c" value="">
            <input type="hidden" id="m" name="m" value="">
            <input type="hidden" id="media" name="media" value="tela">
            <button type="button" id="listarTela" class="btn btn-sm modalBtnSalvar"><i class="fa fa-search"></i> Pesquisar</button>
            <button type="button" onclick="limparCampos();" class="btn btn-sm modalBtnCancelar">Limpar</button>
        </div>
    </form>
    </div>
</div>

<!-- Listagem -->
<div style="background:#fff; border:1px solid #e8e8e8; border-radius:12px; overflow:hidden; margin-top:10px;">
    <div style="display:flex; align-items:center; padding:12px 18px; border-bottom:1px solid #eee;">
        <h4 style="margin:0; font-size:16px;"><i class="fa fa-list"></i> Listagem de Projetos</h4>
    </div>
    <?php $objLT = new GerenciarTransparenciaAC(); ?>
    <table class="tbl-fin" id="tblProjetos">
        <thead>
            <tr>
                <th data-col="0" style="cursor:pointer;">C&oacute;d.Projeto <span class="sort-arrow">&uarr;&darr;</span></th>
                <th data-col="1" style="cursor:pointer;">E-protocolo <span class="sort-arrow">&uarr;&darr;</span></th>
                <th data-col="2" style="cursor:pointer;">Projeto <span class="sort-arrow">&uarr;&darr;</span></th>
                <th data-col="3" style="cursor:pointer;">Coordenador <span class="sort-arrow">&uarr;&darr;</span></th>
                <th data-col="4" style="cursor:pointer;">Data In&iacute;cio <span class="sort-arrow">&uarr;&darr;</span></th>
                <th data-col="5" style="cursor:pointer;">Data Fim <span class="sort-arrow">&uarr;&darr;</span></th>
            </tr>
        </thead>
        <tbody>
<?php
try {
    if ($fws_['listar'] != 1) {
        throw new Exception("Para listar Projetos, utilize o formul&aacute;rio acima e clique em Pesquisar.");
    }
    $objLT->carregarListaProjetos($fws_, $debug);
} catch (Exception $exc) {
    echo "<tr><td colspan=\"6\" class=\"text-center\" style=\"padding:30px 10px; color:#999; font-style:italic;\">" . $exc->getMessage() . "</td></tr>";
}
?>
        </tbody>
    </table>
    <div style="border-top:1px solid #e0e0e0; padding:8px 10px; text-align:right; color:#999; font-size:12px;" id="tblProjetosRodape"></div>
</div>
<div style="height:300px;"></div>

<?php
require_once(dirname(__FILE__)."/../../include/config.php");

$fws_['situacao'] = $fws_['situacao'] == '' ? 'ATIVADA' : $fws_['situacao'];
$fws_['ordenar'] = $fws_['ordenar'] == '' ? 'area_tematica' : $fws_['ordenar'];
$fws_['perspectiva'] = $fws_['perspectiva'] == '' ? 'simplificada' : $fws_['perspectiva'];

if ($fws_['cod_perfil'] == ''){
    $fws_['cod_perfil'] = $fwsGet_['cod_perfil'];
    if($fwsGet_['cod_perfil'])
        $fws_['listar'] = 1;
}
if ($fws_['cod_area_tematica'] == ''){
    $fws_['cod_area_tematica'] = $fwsGet_['cod_area_tematica'];
    if($fwsGet_['cod_area_tematica'])
        $fws_['listar'] = 1;
}
if ($fws_['cod_oficina'] == ''){
    $fws_['cod_oficina'] = $fwsGet_['cod_oficina'];
    if($fwsGet_['cod_oficina'])
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
if ($fws_['consultaPremiados'] == ''){
    $fws_['consultaPremiados'] = $fwsGet_['consultaPremiados'];
    if($fwsGet_['consultaPremiados'])
        $fws_['listar'] = 1;
}

$fws_['situacao']   = 'ATIVADA';
$fws_['avaliacao']  = 'aprovado';
$fws_['consultaAnexoArqApresentacao']  = 'anexaram';
//$fws_['pagamentos']  = '99';
$fws_['perspectiva']  = 'detalhada';
if($fws_['consultaPremiados']=='indicado_pela_comissao'){
    $fws_['consultaAnexoArqVideo']='anexaram';
}
/**
 * Removendo verificações para evento 61
 */
if($objEv->getCodEvento()==61){
    $fws_['consultaAnexoArqApresentacao'] = NULL;
    // $fws_['consultaPremiados'] = NULL;
    $fws_['consultaAnexoArqVideo'] = NULL;
}
if($objEv->getCodEvento()==70){
    $fws_['consultaAnexoArqApresentacao'] = NULL;
    $fws_['consultaPremiados'] = NULL;
    $fws_['consultaAnexoArqVideo'] = NULL;
}
$fws_['listar'] = 1;

?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
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
$(document).ready(function () {
    $("table.tablesorter").tablesorter({widgets: ['zebra']})
//    .tablesorterPager({container: $("#pager")}); 
   
    $("#estado").change(function () {
        carregarCidades(this.value);
    });

    $('#listarTela').click(function(){
        $('#formListaTrabalhos').attr('action', '<?php echo WB_URL_VIEW?>?content=view/anais/anais_pesquisa.php');
        $('#c').val('');
        $('#m').val('');
        $("input[name=media]").val(['tela']);
        $('#formListaTrabalhos').submit();
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
    $('#nome_inscrito').val('');
    $('#titulo_trabalho').val('');
    $('#formato_texto').val('');
    $('#titulo_trabalho').val('');
    $('#titulo_texto_trabalho').val('');
    $('#co_autor').val('');  
    $('#palavras_chave').val('');  
    $('#cidade').val('');  
    $('#cod_area_tematica').val('');  
    $("input[name=perspectiva]").val(['simplificada']);
    $("input[name=premiacao]").val(['']);
    $("input[name=premiado]").val(['']);
    $("input[name=ordenar]").val(['area_tematica']);
    $('#formListaTrabalhos').attr('action', '<?php echo WB_URL_VIEW?>?content=view/admin/consulta-dados-congresso-listagem-trabalhos.php');
    $('#c').val('');
    $('#m').val('');
}        
</script>    
<h3>Área Pública</h3>  
<div class="panel-group">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4>
                Exposição de Trabalhos - <?php echo $objEv->getTitulo()?>
            </h4>
        </div>
<div class="panel-body">
<fildset>
<!--    <ul class="nav nav-tabs">
        <li><a href="<?php echo WB_URL_VIEW?>?content=view/anais/anais_pesquisa.php">Anais do <?php echo ABREV_EVENTO." - ".NOME_EVENTO?></a></li>
    </ul>
    <br>-->
    <form method="post" id="formListaTrabalhos" action="<?php echo WB_URL_VIEW?>?content=view/admin/consulta-dados-congresso-listagem-trabalhos.php">
        <div class="form-group col-sm-6 cnslt-padrao" style="padding-left: 0px;">
            <!--<label for="nome_inscrito">Nome Inscrito:</label>-->
            <input type="text" id="nome_inscrito" name="nome_inscrito" value="<?php echo $fws_['nome_inscrito'] ?>"
                   class="form-control" placeholder="Nome do Inscrito"
                   SIZE='60' maxlength='255' onBlur='trim(this);'>
        </div>
        <div class="form-group col-sm-6 cnslt-padrao" style="padding-left: 0px;">
            <!--<label for="cod_autor">Nome de um co-autor:</label>-->
            <input type="text" id="co_autor" name="co_autor" value="<?php echo $fws_['co_autor'] ?>"
                   class="form-control" placeholder="Nome de um co-autor"
                   SIZE='60' maxlength='255' onBlur='trim(this);' onkeypress='javascript: return isAlfa(getKey(event));'>
        </div>
        <div class="form-group col-sm-6 cnslt-padrao" style="padding-left: 0px;">
            <!--<label for="titulo_trabalho">Titulo Trabalho:</label>-->
            <input type="text" id="titulo_trabalho" name="titulo_trabalho" value="<?php echo $fws_['titulo_trabalho'] ?>"
                   class="form-control" placeholder="Titulo do Trabalho"
                   SIZE='60' maxlength='255' onBlur='trim(this);' onkeypress='javascript: return isAlfa(getKey(event));'>
        </div>
        <div class="form-group col-sm-6 cnslt-padrao" style="padding-left: 0px;">
            <!--<label for="cod_autor">Palavras chave:</label>-->
            <input type="text" id="palavras_chave" name="palavras_chave" value="<?php echo $fws_['palavras_chave'] ?>"
                   class="form-control" placeholder="Palavras chave"
                   SIZE='60' maxlength='255' onBlur='trim(this);' onkeypress='javascript: return isAlfa(getKey(event));'>
        </div>
        <div class="form-group col-sm-12 cnslt-padrao" style="padding-left: 0px;">
            <input type="text" id="titulo_texto_trabalho" name="titulo_texto_trabalho" value="<?php echo $fws_['titulo_texto_trabalho'] ?>"
                   class="form-control" placeholder="Titulo, Texto e Palavras-Chave do Trabalho"
                   SIZE='60' maxlength='255' onBlur='trim(this);' onkeypress='javascript: return isAlfa(getKey(event));'>
        </div>
        <div class="form-group col-sm-12 cnslt-padrao" style="padding-left: 0px;">
            <label for="cod_area_tematica">Selecione Eixo Temático: </label>
            <select name="cod_area_tematica" id="cod_area_tematica" class="form-control">
                <option></option>
                <?php CarregarOptionsAC::areasTematicas($fws_, $debug); ?>
            </select>
        </div>
        <?php /**        
        <div class="form-group col-sm-3 cnslt-padrao" style="padding-left: 0px;">
            <label for="estado">Selecione Estado: </label>
            <select name="estado" id="estado" class="form-control">
                <option></option>
                <?php //CarregarOptionsAC::estados($fws_, $debug); ?>
            </select>
        </div> */?>
        <?php /** 
        <div class="form-group col-sm-9 cnslt-padrao" style="padding-left: 0px;">
            <label for="cidade">Selecione Cidade: </label>
            <select name="cidade" id="cidade" class="form-control">
                <option></option>
                <?php //CarregarOptionsAC::cidades($fws_, $debug); ?>
            </select>
        </div> */ ?>
        <?php /**
        <div class="form-group col-sm-12" style="padding-left: 0px;">
            <label for="premiacao"><b>Prêmidos - Jovens Melhoristas</b>? </label> 
            &nbsp;&nbsp;&nbsp;&nbsp;
            <input type='radio' name='premiado' value='1' <?php echo verifItemChecked($fws_['premiado'], '1') ?> >
            Sim.
            &nbsp;&nbsp;&nbsp;&nbsp;
            <input type='radio' name='premiado' value='' <?php echo verifItemChecked($fws_['premiado'], '') ?> >
            Não.
        </div> */?>
        <div class="form-group col-sm-12 cnslt-padrao" style="padding-left: 0px;">
            <label for="ordenar">com relação ao Tipo de Trabalho: </label>
            &nbsp;&nbsp;<input type='radio' name='formato_texto' value='' <?php echo verifItemChecked($fws_['formato_texto'], '') ?> > Todos
            <?php if($objEv->getCodEvento()==61) { ?>
                &nbsp;&nbsp;<input type='radio' name='formato_texto' value='Pesquisas Científicas' <?php echo verifItemChecked($fws_['formato_texto'], 'Pesquisas Científicas') ?> > Pesquisa
            &nbsp;&nbsp;<input type='radio' name='formato_texto' value='Relatos de Experiência'   <?php echo verifItemChecked($fws_['formato_texto'], 'Relatos de Experiência')   ?> > Relato de Experiência

                <?php }else{ ?>
            &nbsp;&nbsp;<input type='radio' name='formato_texto' value='pesquisa' <?php echo verifItemChecked($fws_['formato_texto'], 'pesquisa') ?> > Pesquisa
            &nbsp;&nbsp;<input type='radio' name='formato_texto' value='relato'   <?php echo verifItemChecked($fws_['formato_texto'], 'relato')   ?> > Relato de Experiência
            <?php } ?>
        </div>
        <?php /**
        <div class="form-group col-sm-12 cnslt-padrao" style="padding-left: 0px;">
            <label for="ordenar">com relação ao Premio: </label>
            &nbsp;&nbsp;<input type='radio' name='consultaPremiados' value=''           <?php echo verifItemChecked($fws_['consultaPremiados'], '') ?> > Todos
            <?php if($objEv->getCodEvento()==61){ ?>
            &nbsp;&nbsp;<input type='radio' name='consultaPremiados' value='indicado_pelo_avaliador' <?php echo verifItemChecked($fws_['consultaPremiados'], 'indicado_pelo_avaliador') ?> > Indicado ao prêmio por avaliador
            <?php }else{ ?>
            &nbsp;&nbsp;<input type='radio' name='consultaPremiados' value='indicado_pela_comissao' <?php echo verifItemChecked($fws_['consultaPremiados'], 'indicado_pela_comissao') ?> > Concorrendo ao prêmio
            <!--&nbsp;&nbsp;<input type='radio' name='consultaPremiados' value='premiados'  <?php // echo verifItemChecked($fws_['consultaPremiados'], 'premiados') ?> > Somente Premiados-->
            <?php } ?>
        </div>
         */?>
        <div class="form-group col-sm-12 cnslt-padrao" style="padding-left: 0px;">
            <?php /**
            <label for="perpectiva">Visualização: </label>
            &nbsp;<input type='radio' name='perspectiva' value='simplificada' <?php echo verifItemChecked($fws_['perspectiva'], 'simplificada') ?> > Simplicada
            &nbsp;<input type='radio' name='perspectiva' value='detalhada'    <?php echo verifItemChecked($fws_['perspectiva'], 'detalhada') ?> > Detalhada
            &nbsp;<input type='radio' name='perspectiva' value='comcodigo'    <?php echo verifItemChecked($fws_['perspectiva'], 'comcodigo') ?> > Com código
            &nbsp;&nbsp;&nbsp;
            */
            ?>
            <label for="ordenar">Ordenar por: </label>
            &nbsp;&nbsp;<input type='radio' name='ordenar' value='area_tematica' <?php echo verifItemChecked($fws_['ordenar'], 'area_tematica') ?> >Eixo Temático
            &nbsp;&nbsp;<input type='radio' name='ordenar' value='titulo_trabalho'   <?php echo verifItemChecked($fws_['ordenar'], 'titulo_trabalho') ?> > Titulo
            <!--&nbsp;&nbsp;<input type='radio' name='ordenar' value='cod_trabalho'      <?php echo verifItemChecked($fws_['ordenar'], 'cod_trabalho') ?> > Cód.Trabalho-->
            &nbsp;&nbsp;<input type='radio' name='ordenar' value='inscrito'          <?php echo verifItemChecked($fws_['ordenar'], 'inscrito') ?> >Nome do Inscrito
            <!--&nbsp;&nbsp;<input type='radio' name='ordenar' value='data_inclusao_arq_apresentacao' <?php echo verifItemChecked($fws_['ordenar'], 'data_inclusao_arq_apresentacao') ?> >Data do Anexo Decres.-->
        </div>
        <div class="form-group col-sm-12 cnslt-padrao" style="padding-left: 0px;">
            <input type="hidden" value="1" name="listar">
            <input type='hidden' name='content' value='<?php echo  $content ?>'>
            <input type='hidden' id='c' name='c' value=''>
            <input type='hidden' id='m' name='m' value=''>
            <input type='hidden' id='media' name='media' value='tela'>
            <input type="button" id="listarTela" value="Pesquisar" class="btn btn-sx btn-primary"> 
            &nbsp;&nbsp;&nbsp;&nbsp;
            <input type="button" value="Limpar Campos" onclick="javascript:limparCampos();" class="btn btn-sx btn-primary"> 
        </div>
    </form>
</fildset>
<hr style="width: 97%; color: black; height: 1px; background-color:black;" class="col-sm-12" />    
<br><h3>Sumário de acordo com o filtro acima:</h3>
<table class="tablesorter" style="font-size: 13px;" width="550px" border="0" colspan="4" rolspan="3">
    <thead>
    <th></th>
    <th>Titulo</th>
    <th></th>
    </thead>
    <tbody>    
<?php
try {
    if ($fws_['listar'] != 1) {
        throw new Exception("Para listar trabalhos, utilize o formulário acima e clique em <Aplicar Filtrar>.");
    }
    $objLT = new ListarTrabalhosAC();
    $objLT->carregarListaTrabalhosAnaisNovo($fws_, $debug);
    
} catch (Exception $exc) {
    echo "<tr><td></td><td>" . $exc->getMessage() . "<br>&nbsp;</td><td></td></tr>";
}
echo "</tbody></table>";
echo "<br><br>";
?></DIV>
</div></div></div>

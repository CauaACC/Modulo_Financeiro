<?php
// LEGADO: este arquivo não é mais utilizado. O módulo correto é GerenciarCargaPessoaUpload.php
// TODO: remover este arquivo e o controller GerenciarInscritoUploadAC.php
exit;

require_once(dirname(__FILE__) . "/../../lib/util/lov.php");

!$fws_['codEvento'] ? $fws_['codEvento'] = $_GET['codEvento'] : "";

$objSRV = new GerenciarInscritoUploadAC();
$objEv = $objSRV->getEvento($fws_);

?>
<script type="text/javascript">
$(document).ready(function() {
     $('table').tablesorter({
        sortList: [[1,2]]                    
     });
    $('#sucesso').click(function(){
        $('div#sucesso').html('');
        loading('div#sucesso','hidden');
    });
    
    $('#itens tr:odd').css('background','#F4F4F4');  
    $('#itens tr:even').css('background','#E9E9E9');    

    $('#acaoVoltar').click(function(){
        document.location.href='/index.php?contents=<?=WB_PATH?>/index.php&pagina=view/admin/ListarEventos.php';
    });
    $('#salvarInscritoUpload').click(function(){
        salvarInscritoUpload();
    }); 
    $('.tipoCodPerfil').click(function(){
        if($(this).val() == 'perfilInscricao'){
            $(".perfilInscricao").removeClass('hidden');
            $(".perfilColaboracao").addClass('hidden');
            $(".listagemCursos").addClass('hidden');
        }else if($(this).val() == 'perfilColaboracao'){
            $(".perfilInscricao").addClass('hidden');
            $(".perfilColaboracao").removeClass('hidden');
            $(".listagemCursos").addClass('hidden');
        }else if($(this).val() == 'listagemCursos'){
            $(".perfilInscricao").addClass('hidden');
            $(".perfilColaboracao").addClass('hidden');
            $(".listagemCursos").removeClass('hidden');
        }else{
            $(".perfilInscricao").addClass('hidden');
            $(".perfilColaboracao").addClass('hidden');
            $(".listagemCursos").addClass('hidden');
        }
    }); 
    
    $('#limpar').click(function(){
        processJsonLimpar();
        limparCampos();
        document.location.href='#';
    });
    //eventos para manipula��o de registros
    $(document).on("click", '.editar', function(){
        var id = $(this).parents("tr").attr("id");
        editarInscritoUpload(id);
        $('html,body').scrollTop(0);
    });
    $(document).on("click", '.excluir', function(){
        var id = $(this).parents("tr").attr("id");
        excluirInscritoUpload(id);
    });
    $(document).on("click", '.abrirArquivo', function(){
        var id = $(this).parents("tr").attr("id");
        abrirArquivoInscritoUpload(id);
    });
    $(document).on("click", 'a.downloadArquivo', function () {
        var id = $(this).parents("tr").attr("id");
        urlAction = '<?= WB_URL_ACTION_FULL ?>&c=GerenciarInscritoUpload&m=abrirArquivoDownload&_submit_check=1';
        $('#codEventoArquivo').val(id);
        $('#downloadArquivo').attr("action",urlAction);
//            $('#downloadArquivo').attr("target",'_blank');
        $('#downloadArquivo').submit();
    });
    $(document).on("click", '.botaoCarga', function(){ // 17/08/2016 (SQ)
        $('html,body').scrollTop(0);
        var id = $(this).parents("tr").attr("id");
        var arquivo = $(this).parents("tr").attr("data-arquivo");
        carregarInscrito(id, arquivo);

    });
    $(document).on("click", '.botaoAssociar', function(){
        $('html,body').scrollTop(0);
        var id = $(this).parents("tr").attr("id");
        var arquivo = $(this).parents("tr").attr("data-arquivo");
        associarInscrito(id, arquivo);
    }); 
    
});

function salvarInscritoUpload(){
    $('#formInscritoUpload').ajaxSubmit({
         target: '#mensagem'
        ,dataType: 'json'
        ,async: false
        ,cache: false
        ,beforeSend: function() {
            startLoading();
        }
        ,timeout: 4000
        ,error: function(ajax) {
            showResponse(ajax);
        }
        ,success: function(data) {
            processJson(data);
            if(data.statusMessage){ 
                carregarTabelaInscritoUpload();
                limparCampos();
            }

        }
        ,complete: function() {        
            stopLoading();
        }
    });
}

function editarInscritoUpload(codEventoArquivo){
    if(codEventoArquivo){
         //alert('entrou:'+codAcao);
         $.ajax({
             type: "POST",
             url: '<?=WB_URL_ACTION_FULL?>&t='+Math.random(),
             data: ({'codEvento': $('#codEvento').val(),'codEventoArquivo': codEventoArquivo,'c':'GerenciarInscritoUpload', 'm':'editarInscritoUpload','_submit_check':'1'}),
             dataType: "json",
             async: false,
             cache: false,
             beforeSend: function() {
                startLoading();
             },
             success: function(data){
                 //Setando valores para altera��o
                 //$('#codEvento').val(data.codEvento);
                 $('#codEventoArquivo').val(data.codEventoArquivo);
//                 $('#descricao').val(data.descricao); //RQ 16-08-16
                 $('#arquivo').val(data.arquivo);
                 $('#novoCodEventoArquivo').val(1);
             }
            ,complete: function(){
                stopLoading();
                //alert('C�digo n�o econtrado!');
//                return false;
            }
         });
    }else{
        alert('C�digo do arquivo n�o foi informado!');
    }        
}
function excluirInscritoUpload(codEventoArquivo){
    if(codEventoArquivo){
         //alert('entrou:'+codAcao);
         $.ajax({
             type: "POST",
             url: '/index-xml.php?contents=<?=WB_PATH?>/index-action.php&pagina=controller/CommandAC.php&t='+Math.random(),
             data: ({'codEvento': $('#codEvento').val(),'codEventoArquivo': codEventoArquivo, 'c':'GerenciarInscritoUpload', 'm':'excluirInscritoUpload','_submit_check':'1'}),
             dataType: "json",
             async: false,
             cache: false,
             beforeSend: function() {
                startLoading();
             },
             success: function(data){
                processJsonSemFade(data);
                if(data.statusMessage){ 
                    carregarTabelaInscritoUpload();
                    limparCampos();
                }
                
             }
            ,complete: function(){
                stopLoading();
                //alert('C�digo n�o econtrado!');
//                return false;
            }
         });
    }else{
        alert('C�digo do arquivo n�o foi informado!');
    }        
}  
function abrirArquivoInscritoUpload(codEventoArquivo){
    if(codEventoArquivo){
         //alert('entrou:'+codAcao);
         $.ajax({
             type: "POST",
             url: '/index-xml.php?contents=<?=WB_PATH?>/index-action.php&pagina=controller/CommandAC.php&t='+Math.random(),
             data: ({'codEvento': $('#codEvento').val(),'codEventoArquivo': codEventoArquivo, 'c':'GerenciarInscritoUpload', 'm':'abrirArquivoInscritoUpload','_submit_check':'1'}),
             dataType: "json",
             async: false,
             cache: false,
             beforeSend: function() {
                startLoading();
             },
             success: function(data){
                processJsonSemFade(data);
                if(data.statusMessage){ 
                    limparCampos();
                }
                
             }
            ,complete: function(){
                stopLoading();
                //alert('C�digo n�o econtrado!');
//                return false;
            }
         });
    }else{
        alert('C�digo do arquivo n�o foi informado!');
    }        
}  

function carregarInscrito(codEventoArquivo, arquivo){
    if(codEventoArquivo){
         $.ajax({
             type: "POST",
             url: '/index-xml.php?contents=<?=WB_PATH?>/index-action.php&pagina=controller/CommandAC.php&t='+Math.random(),
             data: ({'codEvento': $('#codEvento').val(),'codEventoArquivo': codEventoArquivo,'arquivo': arquivo,'c':'GerenciarInscritoUpload', 'm':'carregarArquivoInscritos','_submit_check':'1'}),
             dataType: "json",
             async: false,
             cache: false, 
             beforeSend: function() {
                startLoading();
             },
             success: function(data){
                processJsonSemFade(data);
                if(data.statusMessage){ 
                    carregarTabelaInscritoUpload();
                    limparCampos();
                }
             }
            ,complete: function(data){
                stopLoading();
            }
         });
    }else{
        alert('C�digo do arquivo n�o foi informado!');
    }        
}  
function associarInscrito(codEventoArquivo, arquivo){
    if(codEventoArquivo){
         $.ajax({
             type: "POST",
             url: '/index-xml.php?contents=<?=WB_PATH?>/index-action.php&pagina=controller/CommandAC.php&t='+Math.random(),
             data: ({'codEvento': $('#codEvento').val(),'codEventoArquivo': codEventoArquivo,'arquivo': arquivo,'c':'GerenciarInscritoUpload', 'm':'associarArquivoInscritos','_submit_check':'1'}),
             dataType: "json",
             async: false,
             cache: false, 
             beforeSend: function() {
                startLoading();
             },
             success: function(data){
                processJsonSemFade(data);
                if(data.statusMessage){ 
                    carregarTabelaInscritoUpload();
                    limparCampos();
                }
             }
            ,complete: function(data){
                stopLoading();
            }
         });
    }else{
        alert('C�digo do arquivo n�o foi informado!');
    }        
}  

function carregarTabelaInscritoUpload(){

    $.post('/index-xml.php?contents=<?=WB_PATH?>/index-action.php&pagina=controller/CommandAC.php&t='+Math.random()
    ,{'codEvento': $('#codEvento').val(), 'c':'GerenciarInscritoUpload', 'm':'carregarTabelaInscritoUpload','_submit_check':'1'} 
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
           
        }
    );
}
function limparCampos(){
    //Limpando campos
    $('#codEventoArquivo').val('');
    $('#codFormaPagamento').val('');
    $('#codCurso').val('');
    $('#codPerfilColaborador').val('');
    $('#arquivo').val('');
    $('#novoCodEventoArquivo').val(0);
}
</script>
<link   rel="stylesheet"      href="<?=WB_PATH?>/view/js/jquery.tablesorter/themes/blue/style.css" type="text/css" id="" media="print, projection, screen" />
<script type="text/javascript" src="<?=WB_PATH?>/view/js/jquery.tablesorter/jquery.tablesorter.js"></script>
<script type="text/javascript" src="<?=WB_PATH?>/view/js/jquery/jquery.form.js"></script>
<script type="text/javascript" src="/<?=WB_PATH?>/view/js/default.js"></script>
<fieldset>
<form id="downloadArquivo" name="downloadArquivo" action="#" method="post" target="_blank">
    <input type="hidden" name="codEvento" value="<?=$objEv->getCodEvento()?>">   
    <input type="hidden" id="codEventoArquivo" name="codEventoArquivo" value="">   
</form>
<legend> <?=TITULO_SECAO_ADMIN?> </legend>
<form  id="formInscritoUpload" name="formInscritoUpload" action="/index-xml.php?contents=<?=WB_PATH?>/index-action.php&pagina=controller/CommandAC.php&dummy=" method="post">
    <table cellspacing="2" cellpadding="2" id="itens" width="555px" border="0">
        <?  include 'cabecalhoEvento.php';?>
        <tr>
            <td class="labelTitulo" colspan="3">
                Carga de Inscritos:
            </td>
        </tr>
        <tr>
            <td class="label" colspan="3">
                <b>Obs:</b>
                <br>Padr�o do arquivo para upload dever� estar em formato csv contendo<br>
                <b>[nome; cpf; email;telefone; codigo da grande �rea; �rea tematica]</b> nesta ordem, nome,email,cpf s�o obrigat�rios, demais campos, caso n�o haja informa��o, mantenha o n�mero de colunas mesmo que em branco.
            </td>
        </tr>
        <tr>
            <td class="labelTitulo" colspan="3">
                Selecione uma das op��es abaixo de perfis ou atividades:
            </td>
        </tr>
        <tr>
            <td class="label" colspan="3" style="font-size: 13px">
                <input type="radio" name="tipoCodPerfil" class="tipoCodPerfil" value="perfilColaboracao"> Mostrar Perfis de Colaboradores:.
                <br>
                <input type="radio" name="tipoCodPerfil" class="tipoCodPerfil" value="perfilInscricao"> Mostrar Perfis de Inscri��o.
                <br>
                <input type="radio" name="tipoCodPerfil" class="tipoCodPerfil" value="listagemCursos"> Mostrar Atividades.
            </td>
        </tr>
        <tr class="perfilColaboracao hidden">
            <td  class="label" width="140px">Por perfil colaborador: </td>
            <td colspan="2">
                <select id="codPerfilColaborador" name="codPerfilColaborador">
                    <option value="">Listar todos</option>
                    <?
                    GerenciarInscritoPerfilColaboradorAC::carregarSelectOptionPerfilColaborador($objEv, $fws_, $debug);
                    ?>
                </select>
            </td>        
        </tr>
        <tr class="perfilInscricao hidden">
            <td  class="label" width="140px">Por perfil de inscri��o: </td>
            <td colspan="2">
                <?
                try {
                    $listaFormasPagamento = $objEv->getListaFormasPagamento();
                    ?>
                    <select id="codFormaPagamento" name="codFormaPagamento">
                        <option value="">Listar todos</option>
                        <?
                        foreach ($listaFormasPagamento as $key => $value) {
                            if($value->getSituacao()=='Ativado'){
                                $titulo = $value->getTitulo();
                                $codigo = $value->getCodFormaPagamento();
                                if (strlen($titulo) > 55) {
                                    $titulo = substr($titulo, 0, 55) . "...";
                                }
                                ?>
                                <option  <?= verifItemSelected($fws_['codFormaPagamento'], $codigo, true) ?> value="<?= $codigo ?>"><?= $titulo ?></option>
                                <?
                            }
                        }
                        ?>
                    </select>
               <?
               } catch (Exception $exc) {
                    echo $exc->getMessage();
               }
               ?>
            </td>        
        </tr>
        <tr class="listagemCursos hidden">
            <td  class="label" width="140px">Associar a Atividade: </td>
            <td colspan="2">
                <?
                try {
                    $listaCursos = $objEv->getListaCursos();
                    ?>
                    <select id="CodCurso" name="CodCurso">
                        <option value="">Listar todos</option>
                        <?
                        foreach ($listaCursos as $key => $value) {
                            $titulo = $value->getTitulo();
                            $codigo = $value->getCodCurso();
                            if (strlen($titulo) > 55) {
                                $titulo = substr($titulo, 0, 55) . "...";
                            }
                            ?>
                            <option  <?= verifItemSelected($fws_['CodCurso'], $codigo, true) ?> value="<?= $codigo ?>">
                                    <?= $value->getCodCurso() ?> - 
                                    <?php echo $value->getModalidade()?  $value->getModalidade().' - ' : ''; ?>
                                    <?php echo $value->getOrdem()?  $value->getOrdem().' - ' : ''; ?>
                                    <?= $titulo ?></option>
                            <?
                        }
                        ?>
                    </select>
               <?
               } catch (Exception $exc) {
                    echo $exc->getMessage();
               }
               ?>
            </td>        
        </tr>
        <tr>
            <td class="label" colspan="3">
                &nbsp;
            </td>
        </tr>
        <tr>
            <td class="label" >Arquivo: </td>
            <td class="label" colspan="2"><input size="55" type="file" name="arquivo" id="arquivo" value=""/>
             <br>Arquivo com extens�o CSV.
            </td>
        </tr> 
        <tr>
            <td colspan="3" align="center">
                <div id="carregando" class="shown hidden">
                    <div class="imagem">
                        <div class="frase">Carregando...</div>
                        <img src="<?=WB_PATH?>/view/image/loading-9.gif" border="0">
                        
                    </div>
                </div>
                <div id="mensagem"></div>
                <div id="sucesso"    class="hidden"></div>
                <div id="erro"       class="hidden"></div>                
                <input type="hidden" value="1"               name="_submit_check">
                <input type="hidden" value="GerenciarInscritoUpload" name="c">
                <input type="hidden" value="salvarInscritoUpload"   name="m">
                
                <input type="hidden" name="novoCodEventoArquivo" id="novoCodEventoArquivo" value="0"/>
                <input type="hidden" name="codEventoArquivo"     id="codEventoArquivo"     value="0"/>
                <input type="hidden" name="codEvento"     id="codEvento"     value="<?=$objEv->getCodEvento()?>"/>

                <input id="salvarInscritoUpload"  class="button_text" type="button" name="salvarInscritoUpload" value="Salvar" />
                &nbsp;&nbsp;&nbsp;                
                <input id="sairSemSalvar" class="button_text" type="button" name="exit"         value="Sair sem salvar" />
                &nbsp;&nbsp;&nbsp;                
                <input id="limpar" class="button_text" type="button" name="limpar"         value="Limpar" />
                    
            </td>
        </tr>
        <tr>
            <td class="labelTitulo" colspan="3">
                Lista de Arquivos Carregados:
            </td>
        </tr>        
    </table>            
</form>
<table class="tablesorter" border="0">
    <thead>
    <th width="35px"></th>
    <th width="6px">C�d.</th>
    <th width="6px">Tipo Perfil</th>
    <th width="350px">Arquivo gravado</th>
    <th width="30px">Situa��o</th>    
    </thead> 
    <tbody class="inputs">         
        <?=$objSRV->carregarTabelaInscritoUpload($fws_, $debug)?>
     </tbody>
</table>
</fieldset>
<br><?  include 'cabecalhoGerenciadores.php';?>
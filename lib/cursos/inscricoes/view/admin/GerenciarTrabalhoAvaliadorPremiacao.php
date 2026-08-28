<?php
require_once(dirname(__FILE__)."/../../view/admin/trabalhos-cadastro-seguranca.php");
$objSRV = new GerenciarTrabalhoAvaliadorPremiacaoAC();
$objTrabalho = $objSRV->carregarObjTrabalho($fws_, false);
?>
<script type="text/javascript">
    $(document).ready(function () {
        $('#itens tr:odd').css('background', '#F4F4F4');
        $('#itens tr:even').css('background', '#E9E9E9');
//
//        $('#acaoVoltar').click(function () {
//            document.location.href = '/index.php?contents=<?php echo  WB_PATH ?>/index.php&pagina=view/admin/ListarEventos.php';
//        });
        $('#adminIndicarAvaliadorPremiacao').click(function () {
            adminIndicarAvaliadorPremiacao();
        });
//        $('#sairSemSalvar').click(function () {
//            sairSemSalvar();
//        });
        $('.excluirAvaliadorTrabalho').on("click", function () {
            var id = $(this).parents("tr").attr("id");
            excluirAvaliadorTrabalho(id);
        });
//        stopLoading();
    });

    //funções globais
    function carregarTabelaAvaliadorTrabalho() {
        $.post('<?php echo  WB_URL_ACTION ?>?pagina=controller/CommandAC.php&t=' + Math.random()
                , {'codTrabalho': $('#codTrabalho').val(), 'c': 'GerenciarTrabalhoAvaliadorPremiacao', 'm': 'carregarTabelaAvaliadorTrabalho', '_submit_check': '1'}
        , function (html) {
            $("table tbody.inputs tr").remove();
            $("table").trigger("update");
            // append the "ajax'd" data to the table body 
            $("table tbody.inputs").append(html);
            // let the plugin know that we made a update 
            $("table").trigger("update");
            //            $("table").tablesorter({
            //                sortList: [[1,2]] 
            //            }); 
        });
    }
    function adminIndicarAvaliadorPremiacao() {
//        $('#formAvaliadorTrabalho').ajaxSubmit({
        var codTrabalho = $('#codTrabalho').val();
        var codInscrito = $('#codInscrito').val();
        var codAvaliador = $('#codAvaliador').val();
        
        if(codTrabalho==''){
            alert('Código do trabalho não informado.')
            return false;
        }
        if(codInscrito==''){
            alert('Código do inscrito não informado.')
            return false;
        }
        if(codAvaliador==''){
            alert('Código da avaliacao não informado.')
            return false;
        }
            
        $.ajax({
            type: "POST",
            url: '<?php echo  WB_URL_COMMAND?>&t='+Math.random(),
            data: ({'codTrabalho':codTrabalho,'codInscrito':codInscrito,'codAvaliador': codAvaliador,'c': 'GerenciarAvaliacaoPremiacao', 'm': 'adminIndicarAvaliador', '_submit_check': '1'}),
            target: '#mensagem',
            dataType: 'json',
            async: false,
            cache: false,
            beforeSend: function () {
                startLoading();
            },
            timeout: 4000,
            error: function (txtErr) {
                showResponse(txtErr);
            },
            success: function (data) {
                processJson(data);
                if (data.statusMessage == '1') {
//                    { "message":"Histórico da avaliação do trabalho gravado com sucesso.",
//                        "statusMessage":"1", "avaliacao":"SELECIONADO",
//                        "idInclusao":"admin", "dataInclusao":"27/04/2018 14:39" }
            //      processJson(data);
                    //elementTo = '#'+codTrabalho
                    elementMsg   = '#msgTrabAval-<?php echo  $fws_['codTrabalho'] ?>'
                    elementMsgUT = '#msgUT-<?php echo  $fws_['codTrabalho'] ?>'
            //        //processJsonElement(data,elementMsg);
//                    $(elementMsgUT).html(data.message);
                    htmlAnterior = $(elementMsgUT).html();
                    $(elementMsgUT).html(data.message+'<br><br>'+htmlAnterior);
                    $(elementMsg).html('');
                    stopLoading();
//                    $("#associa").hide();
                    $("#associa").html(data.message);
//                    $("#modalIndicarAvaliador").modal('hide');
//                    carregarTabelaAvaliadorTrabalho();
                }
            },
            complete: function () {
                limparCampos();
                stopLoading();
            }
        });
    }
    
//    function excluirAvaliadorTrabalho(codCurso) {
//        $.ajax({
//            type: "POST",
//            url: '<?php echo  WB_URL_ACTION ?>?pagina=controller/CommandAC.php&t=' + Math.random(),
//            data: ({'codTrabalho': $('#codTrabalho').val(), 'c': 'GerenciarTrabalhoAvaliadorPremiacao', 'm': 'excluirAvaliadorTrabalho', '_submit_check': '1'}),
//            dataType: "json",
//            async: false,
//            cache: false,
//            beforeSend: function () {
//                startLoading();
//            },
//            success: function (data) {
//                carregarTabelaAvaliadorTrabalho();
//            },
//            error: function (txtErr) {
//                showResponse(txtErr);
//            },
//            complete: function () {
//                stopLoading();
//                limparCampos();
//            }
//        });
//    }
    
    function limparCampos() {
        $('#codAvaliador').val('');
    }
    function sairSemSalvar() {
        $("#associa").hide();
        $("#associa").html('');
        $(this).dialog("modalIndicarAvaliadorLabel");
    }

</script>
<fieldset>
    <legend style="font-size: 13px;">Indicar Avaliador de Premiação para o Trabalho</legend>
    <form  id="formAvaliadorTrabalho" name="formAvaliadorTrabalho" action="" method="post">
        <div class="form-group col-sm-12 cnslt-padrao" style="padding-left: 0px;">
            <input type="hidden" id="codTrabalho" name="codTrabalho" value="<?php echo  $objTrabalho->getCodTrabalho() ?>">
            <input type="hidden" id="codInscrito" name="codInscrito" value="<?php echo  $objTrabalho->getCodInscrito() ?>">
            <h4><?php echo  $objTrabalho->getTituloTrabalho() ?></h4>
        </div>
        <div class="form-group col-sm-12 cnslt-padrao" style="padding-left: 0px;">
            <label for="codAvaliador" style="font-size: 13px;">Selecione um Avaliador: </label> ( Designados - Pendentes - Nome Avalador - Login )
            <select id="codAvaliador" name="codAvaliador"  class="form-control" required>
                <option ></option>
                <?php CarregarOptionsAC::avaliadoresPremiacao($fws_, $debug); ?>
            </select>
        </div>
        <div class="form-group col-sm-12 cnslt-padrao" style="padding-left: 0px;">
            <div id="carregando" class="shown hidden">
                <div class="imagem">
                    <div class="frase">Carregando...</div>
                    <img src="<?php echo  WB_URL_IMG ?>/loading-9.gif" border="0">

                </div>
            </div>
            <div id="mensagem"></div>
            <div id="sucesso" style="color: green;font-size: 12px;"     class="hidden"></div>
            <div id="erro"    style="color: red;font-size: 12px;"    class="hidden"></div>                
            <br>
            <input id="adminIndicarAvaliadorPremiacao" class="btn btn-xs btn-primary" type="button" name="adminIndicarAvaliadorPremiacao" value="Indicar Avaliador de Premiação para o Trabalho" />
            &nbsp;&nbsp;&nbsp;                
            <!--<input id="sairSemSalvar"       class="button_text" type="button" name="exit"                value="Sair sem salvar" />-->
            <button type="button" class="btn btn-xs btn-primary" data-dismiss="modal">Sair sem salvar</button>
            <br>    &nbsp;
        </div>
    </form>
<!--    <h3>Avaliadores Indicados para o trabalho:</h3>
    <table class="tablesorter" border="0">
        <thead>
        <th width="6px"></th>
        <th width="6px">Cod. Avaliador</th>
        <th width="250px">Nome do Avaliador</th>
        </thead> 
        <tbody class="inputs">
            <?php //= $objSRV->carregarTabelaAvaliadorByIdTrabalho($fws_,true,false) ?>
        </tbody>
    </table>-->
</fieldset>
<br><?php
//include 'cabecalhoGerenciadores.php'; ?>
<script type="text/javascript" src="resources/js/jquery/jquery.form.js"></script>
<link   rel="stylesheet"      href="resources/js/jquery.tablesorter/themes/blue/style.css" type="text/css" id="" media="print, projection, screen" />
<link   rel="stylesheet"      href="resources/css/formulario.css" type="text/css" id="" media="print, projection, screen" />
<script type="text/javascript" src="resources/js/jquery.tablesorter/jquery.tablesorter.js"></script>
<script type="text/javascript" src="resources/js/jquery/jquery.form.js"></script>
<script type="text/javascript" src="resources/js/default.js"></script>
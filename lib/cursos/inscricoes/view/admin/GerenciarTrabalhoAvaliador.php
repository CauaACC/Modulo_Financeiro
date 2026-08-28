<?php
require_once(dirname(__FILE__)."/../../view/admin/trabalhos-cadastro-seguranca.php");
$objSRV = new GerenciarTrabalhoAvaliadorAC();

// Esta tela so funciona com codTrabalho (e uma tela de detalhe, nao de listagem).
// Chegar aqui sem codTrabalho costuma ser porque o usuario clicou no botao
// "Avaliadores" do menu, que aponta direto pra ca. O fluxo correto e: abrir
// "Avaliacao de Trabalhos" -> clicar num trabalho -> gerenciar avaliadores.
if (empty($fws_['codTrabalho'])) {
    require_once(dirname(__FILE__)."/../../include/config.php");
    require_once(dirname(__FILE__)."/../../view/admin/titulo_area_admin.php");
    require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_administracao.php");
    require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_avaliacao.php");
    ?>
    <div class="panel-group">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4>Avaliadores do Trabalho</h4>
            </div>
            <div class="panel-body">
                <div class="alert alert-warning">
                    <strong>Selecione um trabalho.</strong><br>
                    Esta tela gerencia os avaliadores de um trabalho espec&iacute;fico.
                    Para chegar at&eacute; ela, acesse primeiro
                    <a href="index.php?content=view/admin/consulta-avaliacao-trabalhos-listagem.php">Avalia&ccedil;&atilde;o de Trabalhos</a>
                    e clique no trabalho desejado.
                </div>
                <a href="javascript:history.back();" class="btn btn-default modalBtnCancelar" role="button">&larr; Voltar</a>
            </div>
        </div>
    </div>
    <?php
    return;
}

try {
    $objTrabalho = $objSRV->carregarObjTrabalho($fws_, false);
} catch (Exception $e) {
    ?>
    <div class="alert alert-danger">
        <strong>Trabalho não encontrado.</strong><br>
        <?php echo htmlspecialchars($e->getMessage()); ?>
    </div>
    <?php
    return;
}
?>
<script type="text/javascript">
    $(document).ready(function () {
//        $('#itens tr:odd').css('background', '#F4F4F4');
//        $('#itens tr:even').css('background', '#E9E9E9');
//
//        $('#acaoVoltar').click(function () {
//            document.location.href = '/index.php?contents=<?php echo  WB_PATH ?>/index.php&pagina=view/admin/ListarEventos.php';
//        });
        $('#adminIndicarAvaliador').click(function () {
            adminIndicarAvaliador();
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
                , {'codTrabalho': $('#codTrabalho').val(), 'c': 'GerenciarTrabalhoAvaliador', 'm': 'carregarTabelaAvaliadorTrabalho', '_submit_check': '1'}
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
    function adminIndicarAvaliador() {
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
            data: ({'codTrabalho':codTrabalho,'codInscrito':codInscrito,'codAvaliador': codAvaliador,'c': 'GerenciarAvaliacao', 'm': 'adminIndicarAvaliador', '_submit_check': '1'}),
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
                    $(elementMsgUT).html(data.message);
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
//            data: ({'codTrabalho': $('#codTrabalho').val(), 'c': 'GerenciarTrabalhoAvaliador', 'm': 'excluirAvaliadorTrabalho', '_submit_check': '1'}),
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
    <legend style="font-size: 13px;">Indicar Avaliador para o Trabalho</legend>
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
                <?php CarregarOptionsAC::avaliadores($fws_, $debug); ?>
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
            <input id="adminIndicarAvaliador" class="btn btn-xs btn-primary" type="button" name="adminIndicarAvaliador" value="Indicar Avaliador para o Trabalho" />
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

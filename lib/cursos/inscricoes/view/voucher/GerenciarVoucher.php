<?php
//ControleAcesso::ifUserInRole('gerenciarEventos,coordenarEventos','url');
@$SIS_RECEPCAO_PARTICIPANTES =11;
@$cod_msg = false;
@$redirect = true;
if(!FWA_Autenticacao($_SESSION['_cod_usuario'], @$SIS_RECEPCAO_PARTICIPANTES, 5, $cod_msg, $redirect)){
    exit();
}

$objSRV = new GerenciarPresencaAC();
?>
<script type="text/javascript">
    $(document).ready(function() {
        $('table').tablesorter({
            sortList: [[1, 2]]
        });

        $('#itens tr:odd').css('background', '#F4F4F4');
        $('#itens tr:even').css('background', '#E9E9E9');
        
        $("input").keypress(function(e){
            if(e.which == 13) {
              localizarInscrito();
            }
        });
        $('#btnLocalizar').click(function() {
             localizarInscrito();
        });
        $('#btnLimpar').click(function() {
            limparCampos();
        });
    });
    //funçães globais
    function carregarTabelaInscritos() {
        $.post('<?php echo  WB_URL_COMMAND ?>&t=' + Math.random()
                , { 'c': 'GerenciarPresenca', 'm': 'carregarTabelaPresencas', '_submit_check': '1'}
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
                editarPresenca(id);
            });
            $('.excluir').click(function() {
                var id = $(this).parents("tr").attr("id");
                excluirPresenca(id);
            });
        }
        );
    }
    
   function localizarInscrito() {
        $('#formPresenca').ajaxSubmit({
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
                    carregarTabelaInscritos();
                    limparCampos();
                }
            }
            , complete: function() {
                stopLoading();
            }
        });
    }
    function limparCampos() {
        //Limpando campos
        $('#cod_inscrito').val('');
        $('#nome_inscrito').val('');
        $('#cpf').val('');
        $('#e_mail').val('');
    }
    function gerarfnEtiqueta(codInscrito){
//        $('#tipoEtiqueta').val(tipoEtiqueta);
        $('#codInscrito').val(codInscrito);
        $('#gerarEtiqueta').submit();
    }    
</script>
<!--<script type="text/javascript" src="/<?php echo  WB_PATH ?>/view/js/util/validacoes.js"></script>-->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
<link   rel="stylesheet"      href="resources/js/jquery.tablesorter/themes/blue/style.css" type="text/css" id="" media="print, projection, screen" />
<script type="text/javascript" src="resources/js/jquery.tablesorter/jquery.tablesorter.js"></script>
<script type="text/javascript" src="resources/js/jquery/jquery.form.js"></script>
<script type="text/javascript" src="resources/js/jquery/jquery.price_format.1.7.min.js"></script>
<script type="text/javascript" src="resources/js/default.js"></script>4
<?php
require_once(dirname(__FILE__)."/../../include/config.php");
?>
<h3>Área Administrativa</h3>

<form name="gerarEtiqueta" id="gerarEtiqueta" method="post" action="<?php echo WB_URL_ACTION?>?content=view/recepcao/etiquetas/etiqueta.php" target="_blank">
<!--form name="gerarEtiqueta" id="gerarEtiqueta" method="post" action="http://www.opctec.com.br/inesco/5congresso/inscricoes/lib/html2pdf/exemples/etiqueta.php?html" target="_blank"-->
    <input type="hidden" value="" name="tipoCertificado" id="tipoCertificado">
    <input type="hidden" value="" name="codInscrito"     id="codInscrito">
</form>
 
<div class="panel-group">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4>Recepção de Participantes:</h4>
        </div>
        <div class="panel-body">
            <label>Localizar Inscrito por:</label>
            <?php
            try {
//                $objEv->controlarAcessoProposta('mensagem');
                ?>
                <form  id="formPresenca" name="formPresenca" action="<?php echo  WB_URL_COMMAND ?>&dummy=" method="post">
                    
                    <div class="form-group col-md-12" style="padding-left: 0px;">
                        
                        <div class="form-group col-md-4" style="padding-left: 0px;">
                            <label for="cpf">Cpf:</label>
                            <input type="cpf" id="cpf" name="cpf" value=""  
                                   class="form-control" placeholder="Informe cpf"  autofocus onBlur='trim(this);'>
                        </div>
                        <div class="form-group col-md-4" style="padding-left: 0px;">
                            <label for="e_mail">E-mail:</label>
                            <input type="text" id="e_mail" name="e_mail" value="" 
                                   class="form-control" placeholder="Informar e-mail"  autofocus onBlur='trim(this);'>
                        </div>
                        <div class="form-group col-md-4" style="padding-left: 0px;">
                            <label for="cod_inscrito">Cód.Inscrito:</label>
                            <input type="text" id="cod_inscrito" name="cod_inscrito" value="" 
                                   class="form-control" placeholder="Informar Cód. Inscrito"  autofocus onBlur='trim(this);'>
                        </div>
                    </div>
                    <div class="form-group col-md-12" style="padding-left: 0px;">
                        <div class="form-group col-md-12" style="padding-left: 0px;">
                            <label for="nome_inscrito">Nome:</label>
                            <input type="text" id="nome_inscrito" name="nome_inscrito" value=""
                                   class="form-control" placeholder="Nome do Inscrito"  onBlur='trim(this);'>
                        </div>

                        <div class="form-group col-md-12" style="padding-left: 0px;margin-top: 10px;">                  
                            <div id="carregando" class="shown hidden">
                                <div class="imagem">
                                    <div class="frase">Carregando...</div>
                                    <img src="<?php echo  WB_PATH ?>/resources/image/loading-9.gif" border="0">

                                </div>
                            </div>
                            <div id="mensagem"></div>
                            <div id="sucesso"    class="hidden"></div>
                            <div id="erro"       class="hidden"></div>                
                            <input type="hidden" value="1"               name="_submit_check">
                            <input type="hidden" value="GerenciarPresenca" name="c">
                            <input type="hidden" value="localizarInscrito"    name="m">
       
                            <button class="btn btn-md btn-primary" type="button" id="btnLocalizar" >Localizar</button>
                            &nbsp;&nbsp;&nbsp;                
                            <button class="btn btn-mds btn-primary" type="button" id="btnLimpar" >Limpar</button>
                        </div>
                    </div>  
                </form>
            <?php } catch (Exception $exc) { ?>
                <div class="form-group col-md-12" style="padding-left: 0px;"> <?php echo $exc->getMessage(); ?></div>        
            <?php } ?>
            <div class="form-group col-md-10 inputs" style="padding-left: 0px;">
            </div>
        </div>
    </div>    

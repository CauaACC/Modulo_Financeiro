<?php
require_once(dirname(__FILE__)."/../../view/admin/consulta-dados-congresso-seguranca.php");
$objSRV = new GerenciarComunicadoAC();
?>
<script type="text/javascript">
    $(document).ready(function() {
        $('#btnSalvar').click(function() {
            salvarComunicado();
        });
        $('#btnSalvarComoNovo').click(function() {
            $('#novoCodComunicado').val(0);
            $('#salvarComunicado').val('Adicionar');
            salvarComunicado();
        });
        $('#btnFechar').click(function() {
            limparCampos();
            $('#formComunicado').addClass('hide');
            $('html,body').scrollTop(0);
        });
        $('#btnAdicionarNovo').click(function() {
            $('#formComunicado').removeClass('hide');
            limparCampos();
            $('html,body').scrollTop(0);
        });
        // Delegated events para a tabela
        $('#tblComunicados').on('click', '.editar', function() {
            var id = $(this).closest("tr").attr("id");
            editarComunicado(id);
        });
        $('#tblComunicados').on('click', '.excluir', function() {
            var id = $(this).closest("tr").attr("id");
            _comunicadoAcaoPendente = {acao: 'excluir', id: id};
            $('#modalConfirmarComunicado .modal-header').removeClass('modalCabecalho').addClass('modalCabecalhoExclusao');
            $('#modalConfirmarComunicado .modal-title').html('<i class="fa fa-exclamation-triangle"></i> Confirmar Exclus\u00e3o');
            $('#modalConfirmarComunicado .modal-body p').html('Deseja realmente excluir o Comunicado C\u00f3d.: <strong>' + id + '</strong>?');
            $('#btnConfirmarComunicadoAcao').removeClass('btn-success modalBtnSalvar').addClass('btn-danger modalBtnExcluir').html('<i class="fa fa-trash"></i> Excluir');
            $('#modalConfirmarComunicado').modal('show');
        });
        $('#tblComunicados').on('click', '.desativar', function() {
            var id = $(this).closest("tr").attr("id");
            _comunicadoAcaoPendente = {acao: 'desativar', id: id};
            $('#modalConfirmarComunicado .modal-header').removeClass('modalCabecalhoExclusao').addClass('modalCabecalho');
            $('#modalConfirmarComunicado .modal-title').html('<i class="fa fa-ban"></i> Confirmar Desativa\u00e7\u00e3o');
            $('#modalConfirmarComunicado .modal-body p').html('Deseja realmente desativar o Comunicado C\u00f3d.: <strong>' + id + '</strong>?');
            $('#btnConfirmarComunicadoAcao').removeClass('btn-danger modalBtnExcluir').addClass('btn-success modalBtnSalvar').html('<i class="fa fa-check"></i> Confirmar');
            $('#modalConfirmarComunicado').modal('show');
        });
        $('#btnConfirmarComunicadoAcao').click(function() {
            $('#modalConfirmarComunicado').modal('hide');
            if (_comunicadoAcaoPendente.acao === 'excluir') excluirComunicado(_comunicadoAcaoPendente.id);
            else if (_comunicadoAcaoPendente.acao === 'desativar') desativarComunicado(_comunicadoAcaoPendente.id);
            else if (_comunicadoAcaoPendente.acao === 'enviarEmail') enviarEmailAvisoPadrao(_comunicadoAcaoPendente.id);
        });
        $('.enviarEmailAvisoPadrao').click(function() {
            var id = $(this).attr("data-cod_inscrito");
            _comunicadoAcaoPendente = {acao: 'enviarEmail', id: id};
            $('#modalConfirmarComunicado .modal-header').removeClass('modalCabecalhoExclusao').addClass('modalCabecalho');
            $('#modalConfirmarComunicado .modal-title').html('<i class="fa fa-envelope"></i> Enviar E-mail');
            $('#modalConfirmarComunicado .modal-body p').html('Deseja enviar e-mail de aviso de boleto com texto padr\u00e3o?');
            $('#btnConfirmarComunicadoAcao').removeClass('btn-danger modalBtnExcluir').addClass('btn-success modalBtnSalvar').html('<i class="fa fa-check"></i> Enviar');
            $('#modalConfirmarComunicado').modal('show');
        });
        $("#estado").change(function () {
            carregarCidades(this.value);
        });
        $('#dataDesconto').change(function(){
            setInstrucao1();
        });
        $('#valorBoleto').change(function(){
            setInstrucao1();
        });
        $('#valorDesconto').change(function(){
            setInstrucao1();
        });
        
    });
    function setInstrucao1(){
//        var dataDesconto = $('#dataDesconto').datepicker({ dateFormat: 'dd/mm/yyyy' }).val()
        var dataDesconto = $('#dataDesconto').val();
        var valorComDesconto = eval($('#valorBoleto').val()-$('#valorDesconto').val());
        var instrucoes1 = '- Para pagamento até dia '+dataDesconto+', receber R$ '+valorComDesconto+',00';
        if($('#valorDesconto').val()!='' || $('#dataDesconto').val()!=''){
            if (confirm('Confirma a alteração da instrução 1 para:'+instrucoes1)){ 
                $('#instrucoes1').val(instrucoes1);
            }
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
    var _comunicadoAcaoPendente = {};
    function carregarTabelaBoletosAvulsos() {
        startLoading();
        $.post('<?php echo  WB_URL_COMMAND ?>&t=' + Math.random()
        , {'c': 'GerenciarComunicado', 'm': 'carregarTabelaBoletosAvulsos', 'codInscrito':$('#codInscrito').val(), '_submit_check': '1'}
        , function(html) {
            $("#tblComunicados tbody").html(html);
        }
        );
        stopLoading();
    }
    function salvarComunicado() {
        $('#formComunicado').ajaxSubmit({
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
                    carregarTabelaBoletosAvulsos();
                    limparCampos();
                    $('#formComunicado').addClass('hide');
                    $('html,body').scrollTop(0);
                }
            }
            , complete: function() {
                stopLoading();
            }
        });
    }
    function editarComunicado(codComunicado) {
        if (codComunicado) {
            $.ajax({
                type: "POST",
                url: '<?php echo  WB_URL_COMMAND ?>&t=' + Math.random(),
                data: ({'codComunicado': codComunicado, 'codInscrito': $('#codInscrito').val(), 'c': 'GerenciarComunicado', 'm': 'editarComunicado', '_submit_check': '1'}),
                dataType: "json",
                async: false,
                cache: false,
                beforeSend: function() {
                    startLoading();
                },
                success: function(data) {
                    processJson(data);
                    if (data.statusMessage) {
                        $('#codComunicado').val(data.codComunicado);
                        $('#codInscrito').val(data.codInscrito);
                        $('#sacado').val(data.sacado);
                        $('#cpfCnpj').val(data.cpfCnpj);
                        $('#dataVencimento').val(data.dataVencimento);
                        $('#valorBoleto').val(data.valorBoleto);
                        $('#dataDesconto').val(data.dataDesconto);
                        $('#valorDesconto').val(data.valorDesconto);
                        $('#demonstrativo1').val(data.demonstrativo1);
                        $('#demonstrativo2').val(data.demonstrativo2);
                        $('#demonstrativo3').val(data.demonstrativo3);
                        $('#instrucoes1').val(data.instrucoes1);
                        $('#instrucoes2').val(data.instrucoes2);
                        $('#instrucoes3').val(data.instrucoes3);
                        $('#foneContato').val(data.foneContato);
                        $('#novoCodComunicado').val(1);
                        $('#formComunicado').removeClass('hide');
                        $('#salvarComunicado').val('Salvar Alterações');
                        $('#dataVencimento').focus();
                        $('html,body').scrollTop(0);
                    }else{
                        alert('Problema ao carregar dados');
                    }
                }
                , complete: function() {
                    stopLoading();
                }
            });
        } else {
            alert('Código do Boleto Avulso não informado!');
        }
    }
    function excluirComunicado(codComunicado) {
        $.ajax({
            type: "POST",
            url: '<?php echo  WB_URL_COMMAND ?>&t=' + Math.random(),
            data: ({'codComunicado': codComunicado,'codInscrito': $('#codInscrito').val(),  'c': 'GerenciarComunicado', 'm': 'excluirComunicado', '_submit_check': '1'}),
            dataType: "json",
            beforeSend: function() { startLoading(); },
            success: function(data) {
                processJson(data);
                if (data.statusMessage) {
                    carregarTabelaBoletosAvulsos();
                    limparCampos();
                }
            },
            complete: function() { stopLoading(); }
        });
    }
    function desativarComunicado(codComunicado) {
        $.ajax({
            type: "POST",
            url: '<?php echo  WB_URL_COMMAND ?>&t=' + Math.random(),
            data: ({'codComunicado': codComunicado,'codInscrito': $('#codInscrito').val(),  'c': 'GerenciarComunicado', 'm': 'desativarComunicado', '_submit_check': '1'}),
            dataType: "json",
            beforeSend: function() { startLoading(); },
            success: function(data) {
                processJson(data);
                if (data.statusMessage) {
                    carregarTabelaBoletosAvulsos();
                    limparCampos();
                }
            },
            complete: function() { stopLoading(); }
        });
    }
    function enviarEmailAvisoPadrao(codInscrito) {
        $.ajax({
            type: "POST",
            url: '<?php echo  WB_URL_COMMAND ?>&t=' + Math.random(),
            data: ({'codInscrito': codInscrito,  'c': 'GerenciarComunicado', 'm': 'enviarEmailAvisoPadrao', '_submit_check': '1'}),
            dataType: "json",
            beforeSend: function() { startLoading(); },
            success: function(data) { processJson(data); },
            complete: function() { stopLoading(); }
        });
    }
    function limparCampos() {
        //Limpando campos
        $('#codComunicado').val('');
//        $('#sacado').val('');
//        $('#cpfCnpj').val('');
        $('#dataVencimento').val('');
        $('#valorBoleto').val('');
        $('#dataDesconto').val('');
        $('#valorDesconto').val('');
        $('#demonstrativo2').val('');
        $('#demonstrativo3').val('');
        $('#novoCodComunicado').val(0);
        $('#salvarComunicado').val('Adicionar');

    }
</script>
<script type="text/javascript" src="resources/js/jquery/jquery.form.js"></script>
<script type="text/javascript" src="resources/js/jquery/jquery.price_format.1.7.min.js"></script>
<script type="text/javascript" src="resources/js/default.js"></script>
<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/titulo_area_admin.php");
require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_administracao.php");
require_once(dirname(__FILE__)."/../../view/admin/area_admin_menu_outros.php");
?>
<div style="background:#fff; border:1px solid #e8e8e8; border-radius:12px; overflow:hidden; margin-top:10px;">
    <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 20px; border-bottom:1px solid #eee;">
        <h4 style="margin:0; font-size:15px; font-weight:600; color:#333;">
                Gerenciar Comunicados:
            </h4>
            <div id="carregando" class="hidden">
                <div class="spinner"></div>
                <div class="imagem">
                    <div class="frase">Carregando...</div>
<!--                    <img src="<?php echo  WB_URL_IMG ?>/loading-9.gif" border="0">-->
            </div>
            </div>
            <div id="mensagem"></div>
            <div id="sucesso" class="hidden"></div>
            <div id="erro"    class="hidden"></div>
    </div>
    <div style="padding:15px 20px;">
            <?php
            try {
                ?>
            <form  id="formComunicado" name="formComunicado" class="hide" action="<?php echo  WB_URL_COMMAND ?>&dummy=" method="post">
                    <div class="form-group col-sm-12" style="padding-left: 0px;">
                        <div class="row">
                        <div class="form-group col-sm-12" style="padding-left: 0px;">
                            <label for="sacado">Nome do Sacado:</label>
                            <input type="text" id="sacado" name="sacado" value="<?php echo $objInscrito->getNomeInscrito()?>"
                                   class="form-control" placeholder="Sacado">
                        </div>
                        <div class="form-group col-sm-4" style="padding-left: 0px;">
                            <label for="cpf_cnpj">Cpf/Cnpj:</label>
                            <input type="text" id="cpfCnpj" name="cpfCnpj" value="<?php echo  $objInscrito->getCpf() ?>"
                                class="form-control" placeholder="Cpf/Cnpj" required autofocus
                                 SIZE='60' maxlength='18'>
                        </div>
                        <div class="form-group col-sm-3" style="padding-left: 0px;">
                            <label for="fone_contato">Fone Contato:</label>
                            <input type="tel" id="fone_contato" name="fone_contato" value="<?php echo $objInscrito->getFoneContato() ?>"
                                   class="form-control" SIZE='60' maxlength='255' onBlur='trim(this);'>
                        </div>
                        </div>
                        <div class="form-group col-sm-4" style="padding-left: 0px;">
                            <label for="dataVencimento">Data de vencimento:</label>
                            <input type="date" id="dataVencimento" name="dataVencimento" value="" style="line-height: 15px;"
                                   class="form-control" placeholder="Data de vencimento" >
                        </div>
                        <div class="form-group col-sm-6" style="padding-left: 0px;">
                            <label for="valorBoleto">Valor: (XXXX.00)</label>
                            <input type="text" id="valorBoleto" name="valorBoleto" value=""
                                   class="form-control" placeholder="Não acrescente centavos" >
                        </div>
                        <div class="form-group col-sm-4" style="padding-left: 0px;">
                            <label for="dataDesconto">Aplicar desconto até:</label>
                            <input type="date" id="dataDesconto" name="dataDesconto" value="" style="line-height: 15px;"
                                   class="form-control" placeholder="Aplicar desconto até" >
                        </div>
                        <div class="form-group col-sm-6" style="padding-left: 0px;">
                            <label for="valorDesconto">Valor Desconto: (XXXX.00)</label>
                            <input type="text" id="valorDesconto" name="valorDesconto" value=""
                                   class="form-control" placeholder="Não acrescente centavos" >
                        </div>
                        
                        <div class="form-group col-sm-6" style="padding-left: 0px;">
                            <label for="endereco">Rua:</label>
                            <input type="text" id="endereco" name="endereco" value="<?php echo $objInscrito->getEndereco() ?>"
                                   class="form-control" placeholder="" <?php echo $OP_PERMITIR_ALTERAR_REQUIRED ?> autofocus
                                    SIZE='60' maxlength='255' onBlur='trim(this);'>
                        </div>
                        <div class="form-group col-sm-2" style="padding-left: 0px;">
                            <label for="numero">Número:</label>
                            <input type="text" id="numero" name="numero" value="<?php echo $objInscrito->getNumero() ?>"
                                   class="form-control" placeholder="Número" <?php echo $OP_PERMITIR_ALTERAR_REQUIRED ?> autofocus
                                    SIZE='60' maxlength='255' onBlur='trim(this);'>
                        </div>
                        <div class="form-group col-sm-4" style="padding-left: 0px;">
                            <label for="complemento">Complemento:</label>
                            <input type="text" id="complemento" name="complemento" value="<?php echo $objInscrito->getComplemento() ?>" 
                                   class="form-control" 
                                    SIZE='60' maxlength='255' onBlur='trim(this);'>
                        </div>



                        <div class="form-group col-sm-2" style="padding-left: 0px;">
                            <label for="cep">Cep:</label>
                            <input type="text" id="cep" name="cep" value="<?php echo $objInscrito->getCep() ?>" 
                                   class="form-control" placeholder="Cep" <?php echo $OP_PERMITIR_ALTERAR_REQUIRED ?> autofocus
                                    SIZE='60' maxlength='9' onBlur='trim(this);' onkeypress='javascript: return isNumInt(getKey(event));'>
                        </div>
                        <div class="form-group col-sm-2" style="padding-left: 0px;">
                            <label for="bairro">Bairro:</label>
                            <input type="text" id="bairro" name="bairro" value="<?php echo $objInscrito->getBairro() ?>"
                                   class="form-control" placeholder="Bairro" <?php echo $OP_PERMITIR_ALTERAR_REQUIRED ?> autofocus
                                    SIZE='60' maxlength='255' onBlur='trim(this);'>
                        </div>

                        <div class="form-group col-sm-12" style="padding-left: 0px;">
                            <label for="instrucoes1">Instrução 1:</label>
                            <input type="text" id="instrucoes1" name="instrucoes1" value=""
                                   class="form-control" placeholder="Descrição linha 1" required autofocus>
                        </div>
                        <div class="form-group col-sm-12" style="padding-left: 0px;">
                            <label for="instrucoes2">Instrução 2:</label>
                            <input type="text" id="instrucoes2" name="instrucoes2" value="- Não receber após o vencimento"
                                   class="form-control" placeholder="Descrição linha 2" required autofocus>
                        </div>
                        <div class="form-group col-sm-12" style="padding-left: 0px;">
                            <label for="instrucoes3">Instrução 3:</label>
                            <input type="text" id="instrucoes3" name="instrucoes3" value="- Emitido pelo sistema de inscrição"
                                   class="form-control" placeholder="Descrição linha 3" required autofocus>
                        </div>
                        <div class="form-group col-sm-12" style="padding-left: 0px;">
                            <label for="demonstrativo1">Descrição Linha 1:</label>
                            <input type="text" id="demonstrativo1" name="demonstrativo1" value="<?php echo ABREV_EVENTO?> - inscrição número: <?php echo $objInscrito->getCodInscrito()?> | Categoria: <?php echo $objInscrito->getNomePerfil()?>"
                                   class="form-control" placeholder="Descrição linha 1" required autofocus>
                        </div>
                        <div class="form-group col-sm-12" style="padding-left: 0px;">
                            <label for="demonstrativo2">Descrição Linha 2:</label>
                            <input type="text" id="demonstrativo2" name="demonstrativo2" value=""
                                   class="form-control" placeholder="Descrição linha 2" required autofocus>
                        </div>
                        <div class="form-group col-sm-12" style="padding-left: 0px;">
                            <label for="demonstrativo3">Descrição Linha 3:</label>
                            <input type="text" id="demonstrativo3" name="demonstrativo3" value=""
                                   class="form-control" placeholder="Descrição linha 3" required autofocus>
                        </div>
                        <div class="form-group col-sm-12" style="padding-left: 0px;">                  
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
                            <input type="hidden" value="GerenciarComunicado" name="c">
                            <input type="hidden" value="salvarComunicado"    name="m">

                            <input type="hidden" name="novoCodComunicado" id="novoCodComunicado" value="0"/>
                            <input type="hidden" name="codComunicado"     id="codComunicado"     value=""/>                
                            <button class="btn btn-xs btn-primary" type="button" id="btnSalvar" >Salvar</button>
                            &nbsp;&nbsp;&nbsp;                
                            <button class="btn btn-xs btn-primary" type="button" id="btnSalvarComoNovo" >Salvar como novo registro</button>
                            &nbsp;&nbsp;&nbsp;
                            <button class="btn btn-xs btn-primary" type="button" id="btnFechar" >Fechar </button>
                        </div>
                    </div>  
                </form>
            <button class="btn btn-xs btn-primary" type="button" id="btnAdicionarNovo" >Novo Boleto </button>
            <?php } catch (Exception $exc) { ?>
                <div class="form-group col-sm-12" style="padding-left: 0px;"> <?php echo $exc->getMessage(); ?></div>        
            <?php } ?>
            <div class="form-group col-sm-12" style="padding-left: 0px;">
                <h5 style="font-size:14px; font-weight:600; color:#555; margin:15px 0 8px;">Lista de Comunicados Cadastrados</h5>
                <table class="tbl-fin" id="tblComunicados">
                    <thead>
                    <tr>
                        <th style="width:80px;">A&ccedil;&otilde;es</th>
                        <th>Descri&ccedil;&atilde;o</th>
                        <th>Emiss&atilde;o</th>
                        <th>Vencimento</th>
                        <th>Vlr. Doc.</th>
                        <th>Dat. Desc.</th>
                        <th>Vlr. Desc.</th>
                        <th>Vlr. Pago</th>
                        <th>Pagamento</th>
                        <th>Registro</th>
                        <th>Tipo</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    </div>

<!-- Modal Confirmar Acao Comunicado -->
<div class="modal fade" id="modalConfirmarComunicado" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header modalCabecalho">
                <h4 class="modal-title"><i class="fa fa-question-circle"></i> Confirmar</h4>
                <button type="button" class="close modalBtnFechar" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <p></p>
            </div>
            <div class="modal-footer modalRodape">
                <button type="button" class="btn btn-default modalBtnCancelar" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn modalBtnSalvar" id="btnConfirmarComunicadoAcao"><i class="fa fa-check"></i> Confirmar</button>
            </div>
        </div>
    </div>
</div>

<?php
if (!isset($STATUS)) {
    $STATUS = "ATIVADA";
}
?>
<SCRIPT LANGUAGE="JavaScript1.2" src="resources/js/perfil-cadastro.js"></SCRIPT>
<input type='hidden' name='COD_EVENTO' value='<?php echo $COD_EVENTO ?>'>
<style>
    /* Estilos CSS para as abas */
    .tab-content {
        display: none;
    }

.dataTable tbody tr:nth-child(even) {
    background-color: #FFF;
}
.dataTable tbody tr:nth-child(odd) {
    background-color: #CCC;
}
.dataTable tbody td{
    padding: 5px;
    font-family: Helvetica,sans-serif;
    font-size: 13px;
}
.txtAba{
    font-family: Helvetica,sans-serif;
    font-size: 13px;
}
.resizable {
    resize: both; /* Isso permite redimensionar horizontal e verticalmente */
    overflow: auto; /* Adicione barras de rolagem se o conteúdo exceder o tamanho da textarea */
}
label {
    margin-left: 15px;
    font-weight: normal;
}
.dataTable tr td:first-child {
    width: 20%; /* Defina a largura desejada da primeira célula */
  }
.dataTable  {
    width: 100%; /* Defina a largura desejada da primeira célula */
  }
</style>

<!-- Abas de navegação -->
<div id="tabs">
    <ul class="nav nav-tabs" role="tablist">
        <li class="txtAba"><a href="#tab-gerais">Gerais</a></li>
        <li class="txtAba"><a href="#tab-investimento">Investimento</a></li>
        <li class="txtAba"><a href="#tab-atividades">Atividades</a></li>
        <li class="txtAba"><a href="#tab-descricao">Descrição</a></li>
        <li class="txtAba"><a href="#tab-certificado">Certificado</a></li>
        <li class="txtAba"><a href="#tab-comprovante">Comprovante</a></li>
    </ul>

    <div id="tab-gerais" class="tab-content" >
        <table border='0' width='100%'  cellspacing="10" cellpadding="10">
            <tr><td>
                <div class="col-sm-12" style="padding-left: 10px;padding-bottom: 3px;">
                    <span style="font-size: 13px" for="status">Nome para o Grupo de Participante: </span>
                    <input style="font-size: 13px" type='text' name='titulo' value='<?php echo $objPerfil->getTitulo() ?>' size='60' maxlength='255' onBlur="trim(this);">
                </div>

                <div class="col-sm-12" style="padding-left: 10px;padding-bottom: 3px; font-size: 13px;">
                    <input type="radio" name="receber_inscricao" <?php echo verifItemChecked($objPerfil->getReceberInscricao(), '1') ?> value='1'> Sim
                    &nbsp;&nbsp;
                    <input type="radio" name="receber_inscricao" <?php echo verifItemChecked($objPerfil->getReceberInscricao(), array('', 0, false, NULL)) ?> value='0'> Não
                    &nbsp;&nbsp;&nbsp;
                    <span style="font-size: 12px" for="receber_inscricao" style="padding-left: 10px;padding-bottom: 3px;">Mostrar este grupo no site para receber inscrições: </span>
                </div>

                <div class="col-sm-12" style="padding-left: 10px;padding-bottom: 3px;">
                    <span style="font-size: 13px" for="status">Período de inscrição: </span>
                    <span style="font-size: 12px">Inicio: </span>
                    <input style="font-size: 12px" type='datetime-local' name='periodo_inscricao_inicial' value='<?php echo $objPerfil->getPeriodoInscricaoInicial() ?>' maxlength='50' onBlur="trim(this);">
                    <span style="font-size: 12px">Fim: </span>
                    <input style="font-size: 12px" type='datetime-local' name='periodo_inscricao_final' value='<?php echo $objPerfil->getPeriodoInscricaoFinal() ?>' maxlength='50' onBlur="trim(this);">

                </div>

                <div class="col-sm-12" style="padding-left: 10px;padding-bottom: 3px; font-size: 13px;">
                    <input type="radio" name="verificar_vagas_limitadas" <?php echo verifItemChecked($objPerfil->getVerificarVagasLimitadas(), '1') ?> value='1'> Sim
                    &nbsp;&nbsp;
                    <input type="radio" name="verificar_vagas_limitadas" <?php echo verifItemChecked($objPerfil->getVerificarVagasLimitadas(), array('', 0, false)) ?> value='0'> Não
                    &nbsp;&nbsp;&nbsp;
                    <span style="font-size: 12px" for="verificar_vagas_limitadas" style="padding-left: 10px;padding-bottom: 3px;">Vagas limitadas: </span>
                </div>

                <div class="col-sm-12" style="padding-left: 10px;padding-bottom: 3px; font-size: 13px;">
                    <span style="font-size: 13px" for="vagas">Caso sim defina número de vagas: </span>
                    <input type='text' name='vagas' value='<?php echo $objPerfil->getVagas() ?>' size='10' maxlength='10' onBlur="trim(this);">
                </div>

                <div class="col-sm-12" style="padding-left: 10px;padding-bottom: 3px; font-size: 13px;">
                    <input type="radio" name="verificar_inscrito_apto" <?php echo verifItemChecked($objPerfil->getVerificarInscritoApto(), '1') ?> value='1'> Sim
                    &nbsp;&nbsp;
                    <input type="radio" name="verificar_inscrito_apto" <?php echo verifItemChecked($objPerfil->getVerificarInscritoApto(), array('', 0, false)) ?> value='0'> Não
                    &nbsp;&nbsp;&nbsp;
                    <span style="font-size: 12px" for="verificar_inscrito_apto" style="padding-left: 10px;padding-bottom: 3px;">Este grupo terá uma lista prévia de pessoas habilitadas/indicados? </span>
                </div>

                <div class="col-sm-12" style="padding-left: 10px;padding-bottom: 3px; font-size: 13px;">
                    <input type="radio" name="verificar_envio_trabalho" <?php echo verifItemChecked($objPerfil->getVerificarEnvioTrabalho(), '1') ?> value='1'> Sim
                    &nbsp;&nbsp;
                    <input type="radio" name="verificar_envio_trabalho" <?php echo verifItemChecked($objPerfil->getVerificarEnvioTrabalho(), array('', 0, false)) ?> value='0'> Não
                    &nbsp;&nbsp;&nbsp;
                    <span style="font-size: 12px" for="verificar_envio_trabalho" style="padding-left: 10px;padding-bottom: 3px;">Este grupo pode enviar de trabalho? </span>
                </div>

                <div class="col-sm-12" style="padding-left: 10px;padding-bottom: 3px; font-size: 13px;">
                    <input type="radio" name="verificar_selecionar_atividade" <?php echo verifItemChecked($objPerfil->getVerificarSelecionarAtividade(), '1') ?> value='1'> Sim
                    &nbsp;&nbsp;
                    <input type="radio" name="verificar_selecionar_atividade" <?php echo verifItemChecked($objPerfil->getVerificarSelecionarAtividade(), array('', 0, false)) ?> value='0'> Não
                    &nbsp;&nbsp;&nbsp;
                    <span style="font-size: 12px" for="verificar_selecionar_atividade" style="padding-left: 10px;padding-bottom: 3px;">Este grupo pode selecionar atividade? </span>
                </div>
                <div class="col-sm-12" style="padding-top: 12px; padding-left: 10px;padding-bottom: 3px;">
                    <span style="font-size: 13px" for="status"> Quantas atividades pode selecionar? </span>
                    <input type='text' name='qtd_atividade_permite' value='<?php echo $objPerfil->getQtdAtividadePermite() ?>' size='10' maxlength='10' onBlur="trim(this);">
                </div>

                <div class="col-sm-12" style="padding-left: 10px;padding-top: 5px;padding-bottom: 5px;">
                    <span style="font-size: 13px" for="status">Posição na Listagem de inscrição: </span>
                    <input type='text' name='ordem' value='<?php echo $objPerfil->getOrdem() ?>' size='10' maxlength='10' onBlur="trim(this);">
                </div>
            
                <div class="col-sm-12" style="padding-left: 10px;padding-bottom: 3px; padding-bottom: 12px;">
                    <span style="font-size: 13px" for="status">Status: </span>
                    <select name='status'>
                        <option <?php echo verifItemSelected($STATUS, 'ATIVADA') ?> value='ATIVADA '>ATIVADA </option>
                        <option <?php echo verifItemSelected($STATUS, 'DESATIVA') ?> value='DESATIVA '>DESATIVA </option>
                        <option value='DESATIVA'>DESATIVA</option>
                    </select>
                    &nbsp;&nbsp;&nbsp;
                </div>

        </td></tr></table>
    </div>

    <!-- Investimento -->
    <div id="tab-investimento" class="tab-content" >
        <table border='0' width='100%'  cellspacing="10" cellpadding="10" class="dataTable">
                <div class="col-sm-12" style="padding-left: 10px;padding-bottom: 3px; font-size: 13px;">
                    <input type="radio" name="perfil_isento" <?php echo verifItemChecked($objPerfil->getPerfilIsento(), '1') ?> value='1'> Sim
                    &nbsp;&nbsp;
                    <input type="radio" name="perfil_isento" <?php echo verifItemChecked($objPerfil->getPerfilIsento(), array('', 0, false, NULL)) ?> value='0'> Não
                    &nbsp;&nbsp;&nbsp;
                    <span style="font-size: 12px" for="perfil_isento" style="padding-left: 10px;padding-bottom: 3px;">Perfil Isento: </span>
                </div>
                <div class="col-sm-12" style="padding-left: 10px;padding-bottom: 3px; font-size: 13px;">
                    <input type="radio" name="valores_por_periodo" <?php echo verifItemChecked($objPerfil->getValoresPorPeriodo(), '1') ?> value='1'> Sim
                    &nbsp;&nbsp;
                    <input type="radio" name="valores_por_periodo" <?php echo verifItemChecked($objPerfil->getValoresPorPeriodo(), array('', 0, false, NULL)) ?> value='0'> Não
                    &nbsp;&nbsp;&nbsp;
                    <span style="font-size: 12px" for="valores_por_periodo" style="padding-left: 10px;padding-bottom: 3px;">Valor da inscrição se altera conforme período(lote): </span>
                </div>

                <div class="col-sm-12" style="padding-left: 10px;padding-bottom: 3px; font-size: 13px;">
                    <input type="radio" name="liberar_comprovante_inscricao" <?php echo verifItemChecked($objPerfil->getLiberarComprovanteInscricao(), '1') ?> value='1'> Sim
                    &nbsp;&nbsp;
                    <input type="radio" name="liberar_comprovante_inscricao" <?php echo verifItemChecked($objPerfil->getLiberarComprovanteInscricao(), array('', 0, false)) ?> value='0'> Não
                    &nbsp;&nbsp;&nbsp;
                    <span style="font-size: 12px" for="liberar_comprovante_inscricao" style="padding-left: 10px;padding-bottom: 3px;">Liberar emissão de comprovante inscrição: </span>
                </div>    
         
                <div class="col-sm-12" style="padding-left: 10px;padding-bottom: 3px; font-size: 13px;">
                    <span style="font-size: 12px" for="liberar_comprovante_inscricao" style="padding-left: 10px;padding-bottom: 3px;">Investimento: </span>
                    <div class="form-group col-sm-12" style="padding-left: 0px;">
                        <textarea class=editor_padrao rows=5 cols=70 name='investimento' onBlur='trim(this);' class='textob form_control'><?php echo $objPerfil->getInvestimento() ?></textarea>
                    </div>                    
                </div>                
                <div class="col-sm-12" style="padding-left: 10px;padding-bottom: 3px; font-size: 13px;">
                    <span style="font-size: 12px" for="liberar_comprovante_inscricao" style="padding-left: 10px;padding-bottom: 3px;">Complemento botão: </span>
                    <div class="form-group col-sm-12" style="padding-left: 0px;">
                        <textarea class=form_control rows=5 cols=70 name='complemento' onBlur='trim(this);' class='textob form_control'><?php echo $objPerfil->getComplemento() ?></textarea>
                    </div>                   
                </div>                
                      
        </td></tr></table>
    </div>

    <!-- Certificado -->
    <div id="tab-certificado" class="tab-content" >
        <table border='0' width='100%'  cellspacing="10" cellpadding="10">
            <tr><td>
                <div class="col-sm-12" style="padding-left: 10px;padding-bottom: 3px; font-size: 13px;">
                    <input type="radio" name="liberar_certificado" <?php echo verifItemChecked($objPerfil->getLiberarCertificado(), '1') ?> value='1'> Sim
                    &nbsp;&nbsp;
                    <input type="radio" name="liberar_certificado" <?php echo verifItemChecked($objPerfil->getLiberarCertificado(), array('', 0, false)) ?> value='0'> Não
                    &nbsp;&nbsp;&nbsp;
                    <span style="font-size: 12px" for="liberar_certificado" style="padding-left: 10px;padding-bottom: 3px;">Liberar emissão de certificados: </span>
                </div>

                <div class="col-sm-12" style="padding: 10px;">
                    <span style="font-size: 13px" for="status">Qual será a função ou papel indicada no certificado para este grupo?
                    <br>(Ex: “Participante”, “Organizador”, “Avaliador”, “Ministrante”): </span><br>
                    <input style="font-size: 13px" type='text' name='titulo_certificado' value='<?php echo $objPerfil->getTituloCertificado() ?>' size='60' maxlength='255' onBlur="trim(this);">
                </div>                

                <div class="col-sm-12" style="padding-left: 10px;padding-bottom: 3px;">
                    <span style="font-size: 13px" for="carga_horaria_certificado">Carga horária para certificado: </span>
                    <input style="font-size: 13px" type='text' name='carga_horaria_certificado' value='<?php echo $objPerfil->getCargaHorariaCertificado() ?>' size='10' maxlength='10' onBlur="trim(this);">
                </div>                
            </td>
        </td></tr></table>
    </div>

    <!-- Comprovante exigido na inscricao (dados pessoais) -->
    <div id="tab-comprovante" class="tab-content" >
        <table border='0' width='100%'  cellspacing="10" cellpadding="10">
            <tr><td>
                <div class="col-sm-12" style="padding-left: 10px;padding-bottom: 3px; font-size: 13px;">
                    <input type="radio" name="comprovante_exigir" <?php echo verifItemChecked($objPerfil->getComprovanteExigir(), '1') ?> value='1'> Sim
                    &nbsp;&nbsp;
                    <input type="radio" name="comprovante_exigir" <?php echo verifItemChecked($objPerfil->getComprovanteExigir(), array('', 0, false, NULL)) ?> value='0'> Não
                    &nbsp;&nbsp;&nbsp;
                    <span style="font-size: 12px" for="comprovante_exigir">Exigir comprovante (arquivo) deste grupo na tela de dados pessoais da inscrição: </span>
                </div>

                <div class="col-sm-12" style="padding-left: 10px;padding-top: 8px;padding-bottom: 3px;">
                    <span style="font-size: 13px" for="comprovante_titulo">Nome do comprovante (ex.: "Atestado de matrícula"): </span><br>
                    <input style="font-size: 13px" type='text' name='comprovante_titulo' value='<?php echo htmlspecialchars($objPerfil->getComprovanteTitulo(), ENT_QUOTES, 'UTF-8') ?>' size='60' maxlength='255' onBlur="trim(this);">
                </div>

                <div class="col-sm-12" style="padding-left: 10px;padding-top: 8px;padding-bottom: 3px;">
                    <span style="font-size: 13px" for="comprovante_extensoes">Extensões aceitas, separadas por vírgula (vazio = pdf,jpg,jpeg,png): </span><br>
                    <input style="font-size: 13px" type='text' name='comprovante_extensoes' value='<?php echo htmlspecialchars($objPerfil->getComprovanteExtensoes(), ENT_QUOTES, 'UTF-8') ?>' size='40' maxlength='100' onBlur="trim(this);">
                </div>

                <div class="col-sm-12" style="padding-left: 10px;padding-top: 8px;padding-bottom: 3px; font-size: 13px;">
                    <span style="font-size: 13px" for="comprovante_instrucao">Orientação ao inscrito (o que serve como comprovante): </span>
                    <div class="form-group col-sm-12" style="padding-left: 0px;">
                        <textarea class=editor_padrao rows=5 cols=70 name='comprovante_instrucao' onBlur='trim(this);' class='textob form_control'><?php echo $objPerfil->getComprovanteInstrucao() ?></textarea>
                    </div>
                </div>
            </td></tr></table>
    </div>

    <!-- Descricao -->
    <div id="tab-descricao" class="tab-content" >
            
        <table border='0' width='100%'  cellspacing="10" cellpadding="10" class="dataTable">
          
            <tr>
                <td style="font-size: 15px; padding-top: 12px;" colspan=2>&nbsp; Descrição: </td>
            </tr>
            <tr>
                <td colspan=2>&nbsp;
                    <div class="form-group col-sm-12" style="padding-left: 0px;">
                        <textarea class=editor_padrao rows=5 cols=70 name='descricao' onBlur='trim(this);' class='textob form_control'><?php echo $objPerfil->getDescricao() ?></textarea>
                    </div>
                </td>
            </tr>

            <!--   <tr><td colspan=2>&nbsp; Publico Alvo: </td></tr>
        <tr><td colspan=2>&nbsp; <textarea rows=5 cols=70               name='publico_alvo'       size='100' onBlur='trim(this);'
            class='textob'><?php echo $objPerfil->getPublicoAlvo() ?></textarea></td></tr>

        <tr><td colspan=2>&nbsp; Pré-Requisito: </td></tr>
        <tr><td colspan=2>&nbsp; <textarea rows=5 cols=70               name='pre_requisito'      size='100' onBlur='trim(this);' onkeypress='javascript: return isAlfa(window.event.keyCode);'
            class='textob'><?php echo $objPerfil->getPreRequisito() ?></textarea></td></tr>-->


            <!--<tr><td colspan=2>&nbsp; Observação: </td></tr>
        <tr><td colspan=2>&nbsp; <textarea rows=5 cols=70               name='observacao'         size='100' onBlur='trim(this);' onkeypress='javascript: return isAlfa(window.event.keyCode);'
            class='textob'><?php echo $objPerfil->getObservacao() ?></textarea></td></tr>-->

        </table>

    </div>  

<!-- Script JavaScript para ativar as abas com jQuery UI -->
<script type="text/javascript" src="resources/js/tinymce_4.3.0/js/tinymce/tinymce.min.js"></script>
<script type="text/javascript" src="resources/js/tinymce-charcount.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        tinymce.init({
            selector: "textarea.editor_padrao",
            plugins: "code textcolor link",
            height: 200,
            max_height: 200,
            menubar: false,
            statusbar: true,
            block_formats: 'Header 1=h1;Header 2=h2;Header 3=h3',
            force_br_newlines: true,
            forced_root_block: '',
            toolbar: "code | undo redo | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist | forecolor backcolor fontsizeselect | link ",
            setup: function(editor) { tinyMceCharCount.attach(editor, 5000); },
            init_instance_callback: function(editor) {
                $('.mce-tinymce').show('fast');
                $(editor.getContainer()).find(".mce-path").css("display", "none");
            },
            textcolor_map: [
                "FF0000", "Red",
                "00FF00", "Green",
                "0000FF", "Blue"
            ],
            fontsize_formats: "8pt 10pt 12pt 14pt 18pt 24pt 36pt",
        });
        $("#tabs").tabs();
        // Inicializa as abas pai
        $("#tabs").tabs({
            activate: function(event, ui) {
                var indexPai = ui.newTab.index();
                localStorage.setItem('lastActiveTabIndexPai', indexPai);
            }
        });      
        // Restaura a aba pai ativa
        var lastActiveTabIndexPai = localStorage.getItem('lastActiveTabIndexPai');
        if (lastActiveTabIndexPai !== null) {
            $("#tabs").tabs("option", "active", parseInt(lastActiveTabIndexPai));
        }
        restoreActiveTabs();
    });
    // Função para restaurar as abas pai ativas
    function restoreActiveTabs() {
        var lastActiveTabs = localStorage.getItem('lastActiveTabs');
        if (lastActiveTabs !== null) {
            var activeTabs = JSON.parse(lastActiveTabs);
        }
    }
</script>
<!----------------------------- JQuery UI ----------------------------->
<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css">
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery-ui-timepicker-addon/1.6.3/jquery-ui-timepicker-addon.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery.form/4.3.0/jquery.form.min.js"></script>

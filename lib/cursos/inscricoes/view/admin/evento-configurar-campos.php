<script>
//$(document).ready(function() {
//    //linhas pares, iniciando em zero
//    $('.1dataTable tbody tr:even').css('background-color', '#CCC'); 
//    //linhas ímpares iniciando em zero
//    $('.1dataTable tbody tr:odd').css('background-color', '#FFF'); 
//});
</script>
<style>
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
}
.dataTable tr td:first-child {
    width: 20%; /* Defina a largura desejada da primeira célula */
  }
.dataTable  {
    width: 100%; /* Defina a largura desejada da primeira célula */
  }
<?php //echo $objEvConf->getRealizacaoIni() ?>
</style>
<input type='hidden' name='COD_EVENTO' value='<?php echo $objEv->getCodEvento()?>'>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <style>
        /* Estilos CSS para as abas */
        .tab-content {
            display: none;
        }
    </style>
</head>
<body>
    <!-- Abas de navegação -->
    <div id="tabs">
        <ul>
            <li class="txtAba"><a href="#projeto">Projeto</a></li>
            <li class="txtAba"><a href="#configuracao">Inscrição</a></li>
            <li class="txtAba"><a href="#mensagens">Mensagens</a></li>
            <li class="txtAba"><a href="#atividade">Atividade</a></li>
            <li class="txtAba"><a href="#trabalho">Trabalho</a></li>
            <li class="txtAba"><a href="#documentos">Documentos</a></li>
            <li class="txtAba"><a href="#formularios">Formulário</a></li>
            <li class="txtAba"><a href="#pagamento">Pagamento</a></li>
        </ul>

        <!-- Conteúdo das abas -->
        <?php
        /* Campos que aparecem na aba Projeto E na aba de origem. Precisa vir
           ANTES das abas, porque elas chamam espelhoSimNao()/espelhoSelect(). */
        include_once('view/admin/evento-configurar-espelho.php');
        ?>
        <div id="projeto" class="tab-content">
            <!-- O que DEFINE o evento: modulos ligados, dinheiro, entregas.
                 Os campos daqui foram MOVIDOS das outras abas, nunca copiados:
                 as abas dividem um unico <form>, e nome repetido faria o
                 ultimo input vencer no POST. -->
            <?php include('view/admin/evento-configurar-campos-projeto.php') ?>
        </div>

        <div id="configuracao" class="tab-content">
            <!-- Conteúdo da aba "Configuração do Evento" -->
            <?php include('view/admin/evento-configurar-campos-gerais.php') ?>
        </div>

        <div id="trabalho" class="tab-content">
            <!-- Conteúdo da aba "Trabalhos" -->
            <?php include('view/admin/evento-configurar-campos-trabalho.php') ?>
        </div>

        <div id="atividade" class="tab-content">
            <!-- Conteúdo da aba "Atividades" -->
            <?php include('view/admin/evento-configurar-campos-atividade.php') ?>
        </div>

        <div id="documentos" class="tab-content">
            <!-- Conteúdo da aba "Documentos" -->
            <?php include('view/admin/evento-configurar-campos-documentos.php');?>
        </div>

        <div id="mensagens" class="tab-content">
            <!-- Conteúdo da aba "Mensagens" -->
            <?php include('view/admin/evento-configurar-campos-mensagens.php'); ?>
        </div>

        <div id="formularios" class="tab-content">
            <!-- Conteúdo da aba "Formulário Inscrição" -->
            <?php include('view/admin/evento-configurar-campos-formularios.php'); ?>
        </div>
        
        <div id="pagamento" class="tab-content">
            <!-- Conteúdo da aba "Pagamentos" -->
            <?php include('view/admin/evento-configurar-campos-pagamento.php'); ?>
        </div>    
    </div>

    <!-- Script JavaScript para ativar as abas com jQuery UI -->
    <script>
    $(function() {
        $("#tabs").tabs();
        $("#tabsTrabalho").tabs();
    });
    </script>
</body>
</html>

<script type="text/javascript" src="resources/js/tinymce_4.3.0/js/tinymce/tinymce.min.js"></script>
<script type="text/javascript" src="resources/js/tinymce-charcount.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        $('form').submit(function(){
            tinymce.triggerSave();
        });
        tinymce.init({
            selector: "textarea.editor_padrao",
            plugins: "code textcolor link table",
            width: 800,
            height: 800,
            max_height: 1200,
            resize: "both",
            menubar: false,
            statusbar: true,
            block_formats: 'Header 1=h1;Header 2=h2;Header 3=h3',
            force_br_newlines: true,
            force_p_newlines: false,
            forced_root_block: '',
            apply_source_formatting: true,
            convert_newlines_to_brs: true,
            remove_linebreaks: false,            
            toolbar: "code | undo redo | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist | forecolor backcolor fontsizeselect | link | table ",
            setup: function (editor) { tinyMceCharCount.attach(editor, 5000); },
            init_instance_callback: function (editor) {
                $('.mce-tinymce').show('fast');
                $(editor.getContainer()).find(".mce-path").css("display", "none");
            },
            textcolor_map: [
                "FF0000", "Red",
                "008B45", "Green",
                "0000FF", "Blue"
            ],
            fontsize_formats: "10pt 12pt 14pt 18pt 24pt 36pt",
        });
             
    // Inicializa as abas pai
    $("#tabs").tabs({
        activate: function(event, ui) {
            var indexPai = ui.newTab.index();
            localStorage.setItem('lastActiveTabIndexPai', indexPai);
        }
    });

    // Inicializa as abas filhas dentro de "Trabalho"
    $("#tabsTrabalho").tabs({
        activate: function(event, ui) {
            var indexFilha = ui.newTab.index();
            localStorage.setItem('lastActiveTabIndexFilha', indexFilha);
        }
    });

    // Restaura a aba pai ativa
    var lastActiveTabIndexPai = localStorage.getItem('lastActiveTabIndexPai');
    if (lastActiveTabIndexPai !== null) {
        /* Indice gravado por uma versao com mais abas abriria a tela sem aba
           ativa. Limita ao total atual. */
        var totalAbasPai = $("#tabs > ul > li").length;
        var indicePai = parseInt(lastActiveTabIndexPai);
        if (isNaN(indicePai) || indicePai < 0) { indicePai = 0; }
        if (indicePai > totalAbasPai - 1) { indicePai = totalAbasPai - 1; }
        $("#tabs").tabs("option", "active", indicePai);
    }

    // Restaura a aba filha ativa dentro de "Trabalho"
    var lastActiveTabIndexFilha = localStorage.getItem('lastActiveTabIndexFilha');
    if (lastActiveTabIndexFilha !== null && lastActiveTabIndexPai == $("#tabs > ul > li > a[href='#trabalho']").parent().index()) {
        $("#tabsTrabalho").tabs("option", "active", parseInt(lastActiveTabIndexFilha));
    }

    $("#tabsMsgTrabalhoTexto").tabs({
        activate: function(event, ui) {
            var indexPai = ui.newTab.index();
            localStorage.setItem('lastActiveTabIndexPai', indexPai);
        }
    });
    $("#tabsTrabalhoConfig").tabs({
        activate: function(event, ui) {
            //var indexPai = ui.newTab.index();
            //localStorage.setItem('lastActiveTabIndexPai', indexPai);
            var activeTabs = $("#tabsTrabalhoConfig").tabs("option", "active");
            localStorage.setItem('lastActiveTabs', JSON.stringify(activeTabs));
        }
    });
    $("#tabsMsgComplementoFormTexto").tabs({
        activate: function(event, ui) {
            var indexPai = ui.newTab.index();
            localStorage.setItem('lastActiveTabIndexPai', indexPai);
        }
    });
    
    $("#tabsMsgTrabalhoRecursoTexto").tabs({
        activate: function(event, ui) {
            var indexPai = ui.newTab.index();
            localStorage.setItem('lastActiveTabIndexPai', indexPai);
        }
    });
    $("#tabsMsgTrabalhoAposAnexarTexto").tabs({
        activate: function(event, ui) {
            var indexPai = ui.newTab.index();
            localStorage.setItem('lastActiveTabIndexPai', indexPai);
        }
    });
    
    $("#tabsMsgTela").tabs({
        activate: function(event, ui) {
            var indexPai = ui.newTab.index();
            localStorage.setItem('lastActiveTabIndexPai', indexPai);
        }
    });
        $("#tabsMsgTelaAntes").tabs({
            activate: function(event, ui) {
                var indexPai = ui.newTab.index();
                localStorage.setItem('lastActiveTabIndexPai', indexPai);
            }
        });
        $("#tabsMsgTelaDurante").tabs({
            activate: function(event, ui) {
                var indexPai = ui.newTab.index();
                localStorage.setItem('lastActiveTabIndexPai', indexPai);
            }
        });
        $("#tabsMsgTelaDuranteRealizacao").tabs({
            activate: function(event, ui) {
                var indexPai = ui.newTab.index();
                localStorage.setItem('lastActiveTabIndexPai', indexPai);
            }
        });
        $("#tabsMsgTelaDepoisEncerrado").tabs({
            activate: function(event, ui) {
                var indexPai = ui.newTab.index();
                localStorage.setItem('lastActiveTabIndexPai', indexPai);
            }
        });
        $("#tabsMsgTelaSelecionarPerfil").tabs({
            activate: function(event, ui) {
                var indexPai = ui.newTab.index();
                localStorage.setItem('lastActiveTabIndexPai', indexPai);
            }
        });

        let reminderTime = 30 * 60 * 1000; // 10 minutos
        function showReminder() {
            alert("Salve seu progresso para não perder suas informações.");
        }
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(showReminder, reminderTime);
        });
        
        $('#data_ini').datetimepicker();
        $('#data_fim').datetimepicker();
        restoreActiveTabs();
    });
    // Função para restaurar as abas pai ativas
    function restoreActiveTabs() {
        var lastActiveTabs = localStorage.getItem('lastActiveTabs');
        if (lastActiveTabs !== null) {
            var activeTabs = JSON.parse(lastActiveTabs);
            $("#tabsTrabalhoConfig").tabs("option", "active", activeTabs);
        }
    }
</script>
<!----------------------------- JQuery UI ----------------------------->
<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery-ui-timepicker-addon/1.6.3/jquery-ui-timepicker-addon.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery.form/4.3.0/jquery.form.min.js"></script>

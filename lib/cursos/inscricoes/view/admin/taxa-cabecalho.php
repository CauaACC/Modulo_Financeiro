<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/taxa-seguranca.php");
?>
<script type="text/javascript">
    $(document).ready(function () {
        $('#btnDuplicar').click(function (event) {
            event.preventDefault();
            $('#c').val('GerenciarTaxa');
            $('#m').val('duplicar');
            duplicarTaxa();
        });
        $('#periodo_final').change(function(event) {
            var elemento = document.getElementById("periodo_final");
            var valor = elemento.value.substr(0, 10);
            $('#max_data_vencimento').val(valor);
        });
    });
    function duplicarTaxa(event) {
        $.ajax({
            type: "POST"
            , url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random()
            , data: ({'codEvento': $('#codEvento').val(), 'codTaxa': $('#codTaxa').val(), 'c': 'GerenciarTaxa', 'm': 'duplicar', '_submit_check': '1'})
            , dataType: "json"
            , async: false
            , cache: false
            , beforeSend: function () {
                startLoading();
            }
            , timeout: 4000
            , error: function (ajax) {
                showResponse(ajax);
            }
            , success: function (data) {
                processJson(data);
                if (data.statusMessage) {
                    document.location.href = '<?php echo WB_URL_VIEW ?>?content=view/admin/perfil-cadastro-listar.php&cod_perfil=' + data.codPerfil
                    //location.reload(); 
                }
            }
            , complete: function () {
                stopLoading();
            }
        });
    }
</script>
<h3>Área Administrativa</h3>  
<div class="panel-group">
<div class="panel panel-default">
    <div class="panel-heading"><h4>Gerenciar Taxas</h4></div>
    <div class="panel-body">

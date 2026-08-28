<div class="page-header">

    <div class="page-context">
        Área Administrativa
    </div>

    <div class="page-title">
        <a href="#" data-toggle="modal" data-target="#modalTrocarEvento" title="Trocar evento" style="margin-left:8px;">
            <i class="fa fa-exchange" style="font-size:16px;"></i>
        </a>      
        <?php
        if ($COD_EVENTO!='' and $COD_EVENTO!='COD_EVENTO'){
            echo $objEv->getTitulo();
        }else{
            $objEvAlt = new Evento();
            $objEvAlt->setCodEvento($cod_evento);
            $objEvAlt->getEventoById();
            echo $objEvAlt->getTitulo();
        }
        ?>
    </div>

</div>

<div id="modalTrocarEvento" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="lblTrocarEvento">
  <div class="modal-dialog" role="document" style="width:95%;max-width:700px;">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title" id="lblTrocarEvento">Selecionar Evento</h4>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label for="filtroTrocarEvento"><b>Filtrar:</b></label>
          <input type="text" id="filtroTrocarEvento" class="form-control" placeholder="Digite para filtrar..." autocomplete="off">
        </div>
        <div class="form-group">
          <label for="selectTrocarEvento"><b>Selecione o evento:</b></label>
          <select id="selectTrocarEvento" class="form-control" size="12" style="height:auto;">
            <option value="-1">-- Selecione --</option>
            <?php
            try {
                $fws_['statusEvento'] = 'ATIVADO';
                CarregarOptionsAC::listarEventos($fws_, $debug);
            } catch (Exception $exc) {
                /* Lista não encontrada ou vazia */
            }
            ?>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" onclick="trocarEvento()">Trocar</button>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
function trocarEvento() {
    var codEvento = $('#selectTrocarEvento').val();
    if (!codEvento || codEvento == '-1') return;

    var urlParams = new URLSearchParams(window.location.search);
    var content = urlParams.get('content') || '';
    var baseUrl = '<?php echo WB_URL_DOMINIO . "/" . WB_GRUPO ?>';
    var novaUrl = baseUrl + '/' + codEvento + '/inscricoes/index.php';
    if (content) {
        novaUrl += '?content=' + encodeURIComponent(content);
    }
    window.location.href = novaUrl;
}

$(function() {
    // Filtro ao digitar
    $('#filtroTrocarEvento').on('keyup', function(e) {
        if (e.key === 'Enter') {
            trocarEvento();
            return;
        }
        var termo = $(this).val().toLowerCase();
        $('#selectTrocarEvento option').each(function() {
            var texto = $(this).text().toLowerCase();
            var val = $(this).val();
            if (val == '-1') return; // manter "Selecione"
            $(this).toggle(texto.indexOf(termo) > -1);
        });
        // Selecionar primeiro visível
        var primeiroVisivel = $('#selectTrocarEvento option:visible:not([value="-1"])').first();
        if (primeiroVisivel.length) {
            primeiroVisivel.prop('selected', true);
        }
    });

    // Duplo-clique no select troca direto
    $('#selectTrocarEvento').on('dblclick', function() {
        trocarEvento();
    });

    // Focar no filtro ao abrir o modal
    $('#modalTrocarEvento').on('shown.bs.modal', function() {
        $('#filtroTrocarEvento').val('').trigger('keyup').focus();
    });
});
</script>

<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/oficina-cabecalho.php");
?>
<?php
/*
<fieldset>
    <!--
    <ul class="nav nav-tabs">
        <li class="active"><a href="#"><b>Localizar Cadastrados</b></a></li>
        <?php if ($objEvConf->getTrabSubmissao()==1) { ?>
        <li><a href="<?php echo WB_URL_VIEW?>?content=view/admin/consulta-dados-congresso-listagem-trabalhos.php">Localizar Trabalhos </a></li>
        <?php } ?>
        <?php if (COD_EVENTO == 1) { ?>
        <li><a href="<?php echo WB_URL_VIEW?>?content=view/admin/consulta-dados-congresso-listagem-trabalhos-premiacao.php">Definir Premiações</a></li>
        <?php } ?>
    </ul>
        -->
    <br>
    <form method="post" id="formListaInscritos" action="<?php echo WB_URL_VIEW?>?content=view/admin/consulta-dados-congresso-listagem-inscritos.php#rsltdoListaInscritos">
    </form>
</fieldset>
*/
?>
<hr size='1' width='100%'>
<div style="margin-bottom:10px; display:flex; gap:6px; flex-wrap:wrap; align-items:center;">
    <a href="index.php?content=view/admin/oficina-cadastro-incluir.php" class="btn btn-primary btn-xs">Incluir Atividade</a>
    <?php if (FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_GERENCIAR_EVENTO, 5, false, false)) { ?>
    <a href="index.php?content=view/admin/GerenciarGerarSessoes.php" class="btn btn-default btn-xs">Gerar Sess&otilde;es</a>
    <button type="button" class="btn btn-danger btn-xs" style="margin-left:auto;" data-toggle="modal" data-target="#modalExcluirAtividades">Excluir Todas</button>
    <?php } ?>
</div>

<!-- Modal confirmação excluir atividades -->
<div class="modal fade" id="modalExcluirAtividades" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#d9534f; color:#fff;">
                <button type="button" class="close" data-dismiss="modal" style="color:#fff;opacity:1;">&times;</button>
                <h4 class="modal-title">Excluir Todas as Atividades</h4>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    Todas as atividades/sess&otilde;es deste evento ser&atilde;o exclu&iacute;das da listagem.
                    <br>Os registros n&atilde;o s&atilde;o apagados do banco, apenas marcados como exclu&iacute;dos.
                </div>
                <p>Deseja continuar?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" onclick="excluirTodasAtividades()">Excluir Todas</button>
            </div>
        </div>
    </div>
</div>
<script>
function excluirTodasAtividades() {
    var btn = $('#modalExcluirAtividades .btn-danger');
    btn.prop('disabled', true).text('Processando...');
    $.ajax({
        type: 'POST',
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {
            'c': 'GerenciarGerarSessoes',
            'm': 'excluirTodas',
            'cod_evento': '<?php echo COD_EVENTO ?>',
            '_submit_check': '1'
        },
        dataType: 'json',
        success: function(resp) {
            $('#modalExcluirAtividades').modal('hide');
            btn.prop('disabled', false).text('Excluir Todas');
            if (resp.statusMessage == '1') {
                alert(resp.message);
                location.reload();
            } else {
                alert('Erro: ' + resp.message);
            }
        },
        error: function() {
            $('#modalExcluirAtividades').modal('hide');
            btn.prop('disabled', false).text('Excluir Todas');
            alert('Erro ao excluir.');
        }
    });
}
</script>

  <table class="table table-hover table-condensed tablesorter">
    <thead>
      <tr>
        <th>Ação</th>
        <th>Titulo da Atividade</th>
        <th>Ordem</th>
        <th>Status</th>
        <th>Recebendo<br>Inscrição</th>
        <th>Vagas<br>Limitadas</th>
        <th>Total<br>Inscritos/Vagas</th>
        <th>Valor<br>Individual</th>
        <th>Liberar<br>Certificado</th>
      </tr>
    </thead>
    <tbody>
<?php
try {

    $objOficina = new Oficina();
    if(in_array($objEv->getCodEvento(), array(35,52,72))){
        $orderBy = ' data_inicio, ordem, cod_oficina, titulo ';
        $agruparData = true;
    }else{
        $orderBy = ' ordem, cod_oficina, titulo ';
        $agruparData = false;
    }
    $objOficina->carregarListaOficinaSemCache($orderBy,false);
    $totalAtividade = 0;
    foreach ($objOficina->getListaOficina() as $objL) {
        $totalAtividade = $totalAtividade+1;
        $classText = 'txtPadrao';
        if($agruparData){
            if($dataHoraAnt != $objL->getDataInicio().$objL->getHorarioIni()){
                echo "<tr valign='middle' bgcolor='{$bgcolor}'> "
                . "<td width='100%' colspan='9' style='padding: 4px 4px 6px 4px;'>"
                . "<h5>".formatDateView($objL->getDataInicio())." - "
                . "{$objL->getHorarioIni()}</h5>"
                . "</td></tr>";
            }
            $dataHoraAnt = $objL->getDataInicio().$objL->getHorarioIni();
        }
        ?>
        <tr data-id="<?php echo $totalAtividade ?>"> 
        <td width="160px" >
            <a title="Visualizar Atividade <?php echo numberPad($objL->getCodOficina(),2) ?>"        href="<?php echo WB_URL_VIEW?>?content=view/admin/oficina-cadastro-ok.php&COD_OFICINA=<?php echo $objL->getCodOficina()?>" class="button btn btn-default btn-xs" target="_blank">
            <span class="glyphicon glyphicon-eye-open"></span></a>
            <a title="Alterar Atividade <?php echo numberPad($objL->getCodOficina(),2) ?>" href="<?php echo WB_URL_VIEW?>?content=view/admin/oficina-cadastro-alterar.php&COD_OFICINA=<?php echo $objL->getCodOficina()?>" class="button btn btn-default btn-xs" target="_blank">
            <span class="glyphicon glyphicon-folder-open"></span></a>
            
            <!-- <a title="Excluir Atividade <?php echo numberPad($objL->getCodOficina(),2) ?>" href="<?php echo WB_URL_VIEW?>?content=view/admin/oficina-cadastro-excluir-grava.php&COD_OFICINA=<?php echo $objL->getCodOficina()?>" class="button btn btn-default btn-xs">
            <span class="glyphicon glyphicon-trash"></span></a> -->

            <button type="button"
                class="btn btn-xs btn-excluir-atividade"
                title="Excluir atividade"
                data-id="<?php echo $objL->getCodOficina() ?>">
                <span class="glyphicon glyphicon-trash"></span>
            </button>            
            
            <a title="Listar inscritos nesta atividade" href="<?php echo WB_URL_VIEW?>?content=view/admin/consulta-dados-congresso-listagem-inscritos.php&cod_oficina=<?php echo $objL->getCodOficina() ?>&cod_evento=<?php echo $objL->getCodEvento() ?>" class="button btn btn-default btn-xs" target="_blank">
                
            <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 640 512"><!--! Font Awesome Free 6.4.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. --><path d="M144 0a80 80 0 1 1 0 160A80 80 0 1 1 144 0zM512 0a80 80 0 1 1 0 160A80 80 0 1 1 512 0zM0 298.7C0 239.8 47.8 192 106.7 192h42.7c15.9 0 31 3.5 44.6 9.7c-1.3 7.2-1.9 14.7-1.9 22.3c0 38.2 16.8 72.5 43.3 96c-.2 0-.4 0-.7 0H21.3C9.6 320 0 310.4 0 298.7zM405.3 320c-.2 0-.4 0-.7 0c26.6-23.5 43.3-57.8 43.3-96c0-7.6-.7-15-1.9-22.3c13.6-6.3 28.7-9.7 44.6-9.7h42.7C592.2 192 640 239.8 640 298.7c0 11.8-9.6 21.3-21.3 21.3H405.3zM224 224a96 96 0 1 1 192 0 96 96 0 1 1 -192 0zM128 485.3C128 411.7 187.7 352 261.3 352H378.7C452.3 352 512 411.7 512 485.3c0 14.7-11.9 26.7-26.7 26.7H154.7c-14.7 0-26.7-11.9-26.7-26.7z"/></svg></a>
            
        </td>
        <td width="450px">
            <button type="button"
            class="btn btn-xs btn-editar-atividade"
            title="Editar atividade"
            data-toggle="modal" data-target="#modal_atividade"
            data-id="<?php echo $objL->getCodOficina() ?>"
            data-titulo="<?php echo $objL->getTitulo() ?>"
            data-tipo="<?php echo $objL->getTipo() ?>"
            data-ordem="<?php echo $objL->getOrdem() ?>"
            data-status="<?php echo $objL->getStatus() ?>"
            data-receber_inscricao="<?php echo $objL->getReceberInscricao() ?>"
            data-numero_vagas="<?php echo $objL->getNumeroVagas() ?>"
            data-valor="<?php echo $objL->getValorAtividade() ?>"
            data-investimento="<?php echo htmlspecialchars((string)$objL->getInvestimento(), ENT_QUOTES, 'UTF-8') ?>"
            data-liberar_certificado="<?php echo $objL->getLiberarCertificado() ?>"
            data-nome_ministrante="<?php echo htmlspecialchars((string)$objL->getNomeMinistrante(), ENT_QUOTES, 'UTF-8') ?>"
            data-titulo_certificado="<?php echo htmlspecialchars((string)$objL->getTituloCertificado(), ENT_QUOTES, 'UTF-8') ?>"
            data-data_inicio_inscricao="<?php echo $objL->getDataInicioInscricao() ? date('Y-m-d\TH:i', strtotime($objL->getDataInicioInscricao())) : '' ?>"
            data-data_fim_inscricao="<?php echo $objL->getDataFimInscricao() ? date('Y-m-d\TH:i', strtotime($objL->getDataFimInscricao())) : '' ?>"
            data-data_curso="<?php echo $objL->getDataCurso() ?>"
            data-data_inicio="<?php echo $objL->getDataInicio() ?>"
            data-horario_ini="<?php echo $objL->getHorarioIni() ?>"
            data-data_fim="<?php echo $objL->getDataFim() ?>"
            data-horario_fim="<?php echo $objL->getHorarioFim() ?>">
            <span class="glyphicon glyphicon-pencil"></span>
            </button>

            <a class='link' title="Listar inscritos nesta atividade" href='<?php echo WB_URL_VIEW?>?content=view/admin/consulta-dados-congresso-listagem-inscritos.php&cod_oficina=<?php echo $objL->getCodOficina() ?>&cod_evento=<?php echo $objL->getCodEvento() ?>'>
                <span style="font-size:14px;"><?php echo $objL->getTitulo() ?></span></a>
        </td>
        <td ><?php echo numberPad($objL->getOrdem(),2) ?></td>
        <td>
            <?php $statusVal = trim($objL->getStatus()); ?>
            <button type="button" class="btn btn-xs btn-toggle-oficina <?php echo ($statusVal=='ATIVADA')?'btn-success':'btn-default'; ?>"
                data-cod_oficina="<?php echo $objL->getCodOficina(); ?>"
                data-campo="status"
                title="Clique para alternar">
                <?php echo ($statusVal=='ATIVADA')?'Ativada':'Desativada'; ?>
            </button>
        </td>
        <td style="text-align:center;">
            <?php
            $esgotada = false;
            $encerrada = false;
            $recebeInscricao = ($objL->getReceberInscricao() == 1);
            if ($recebeInscricao) {
                if ($objL->getNumeroVagas() <= $objL->getTotalInscritos() and $objL->getNumeroVagas() != '' and $objL->getNumeroVagas() != 0) {
                    $esgotada = true;
                } elseif ((formatDateTimeSemSeparador($objL->getDataFimInscricao()) < Date("YmdHis")) and ($objL->getDataFimInscricao() != '')) {
                    $encerrada = true;
                }
            }
            if ($esgotada) {
                echo '<span style="color:red; font-weight:bold; font-size:11px;">Esgotada</span>';
            } elseif ($encerrada) {
                echo '<span style="color:red; font-weight:bold; font-size:11px;">Encerrada</span>';
            } else {
            ?>
            <button type="button" class="btn btn-xs btn-toggle-oficina <?php echo $recebeInscricao?'btn-success':'btn-default'; ?>"
                data-cod_oficina="<?php echo $objL->getCodOficina(); ?>"
                data-campo="receber_inscricao"
                title="Clique para alternar">
                <?php echo $recebeInscricao?'Sim':'Não'; ?>
            </button>
            <?php } ?>
        </td>
        <td>
            <?php
            if(intval($objL->getNumeroVagas())!=''){
                echo 'Sim';
            }else{
                echo 'Não';
            }
            ?>
        </td>
        <td >
            <a class='link' title="Listar inscritos nesta atividade" href='<?php echo WB_URL_VIEW?>?content=view/admin/consulta-dados-congresso-listagem-inscritos.php&cod_oficina=<?php echo $objL->getCodOficina() ?>&cod_evento=<?php echo $objL->getCodEvento() ?>'>
            <?php
            if($objL->getTotalInscritos()==$objL->getNumeroVagas()){
                echo "<font color='red'>".numberPad($objL->getTotalInscritos(),3) ?> / <?php echo numberPad($objL->getNumeroVagas(),3)."</font>";
            }else{
                echo numberPad($objL->getTotalInscritos(),3) ?> / <?php echo numberPad($objL->getNumeroVagas(),3);
            }
            ?>
            </a>
        </td>
        <td >
            <?php
                if(intval($objL->getValorAtividade())!=''){
                    echo 'R$ '.$objL->getValorAtividade(); 
                }else{
                    echo 'Não';
                }
            ?>
        </td>
        <td>
            <?php $val = $objL->getLiberarCertificado(); ?>
            <button type="button" class="btn btn-xs btn-toggle-oficina <?php echo ($val==1)?'btn-success':'btn-default'; ?>"
                data-cod_oficina="<?php echo $objL->getCodOficina(); ?>"
                data-campo="liberar_certificado"
                title="Clique para alternar">
                <?php echo ($val==1)?'Sim':'Não'; ?>
            </button>
            <?php echo $objL->getCargaHoraria()? "<br>ch: ".$objL->getCargaHoraria()."h" : ""; ?>
        </td>
        </tr>
        <?php
    }
    echo "<tr><td colspan=\"5\">Total Atividade: " . $totalAtividade . "</td></tr>";
} catch (Exception $exc) {
    echo "<tr><td colspan=\"5\">" . $exc->getMessage() . "</td></tr>";
}
echo "</table>";
?>
<script>
$(document).ready(function () {
    // $("#sortable-table tbody").sortable({
//         handle: '.order-handle',
//         update: function (event, ui) {
//             // Após a reordenação, obtenha a nova ordem e envie para o servidor
//             var newOrder = $(this).sortable('toArray', { attribute: 'data-id' });
//             console.log(newOrder)
//             // Envie a nova ordem para o servidor (usando AJAX e PHP)
//             $.ajax({
//                 type: "POST",
//                 url: "update_order.php", // Substitua pelo caminho real do seu script PHP
//                 data: { order: newOrder },
//                 success: function (response) {
//                     // Trate a resposta do servidor conforme necessário
//                 }
//             });
//         }
    // });

    $('#modal_atividade').on('show.bs.modal', function (event) {       
        const button = $(event.relatedTarget); 
        const modal = $(this);
        
        // INPUTS (Text e Hidden)
        modal.find('#id').val(button.data('id')); 
        modal.find('#codOficina').val(button.data('id')); 
        modal.find('#titulo').val(button.data('titulo'));
        modal.find('#tipo').val(button.data('tipo'));
        modal.find('#ordem').val(button.data('ordem'));
        modal.find('#numero_vagas').val(button.data('numero_vagas'));
        modal.find('#valor_atividade').val(button.data('valor'));
        modal.find('#investimento').val(button.data('investimento') || '');
        investimentoSugerido = '';
        
        // SELECTS (Dropdowns)
        modal.find('#status').val(button.data('status'));
        modal.find('#receber_inscricao').val(button.data('receber_inscricao'));
        modal.find('#vagas_limitadas').val(button.data('vagas_limitadas'));
        modal.find('#liberar_certificado').val(button.data('liberar_certificado'));
        modal.find('#nome_ministrante').val(button.data('nome_ministrante') || '');
        modal.find('#titulo_certificado').val(button.data('titulo_certificado') || '');
        toggleTituloCertificado();

        // Adicionar valores dos novos campos datetime-local e date
        modal.find('#data_inicio_inscricao').val(button.data('data_inicio_inscricao'));
        modal.find('#data_fim_inscricao').val(button.data('data_fim_inscricao'));
        modal.find('#data_curso').val(button.data('data_curso'));
        modal.find('#data_inicio').val(button.data('data_inicio'));
        modal.find('#horario_ini').val(button.data('horario_ini'));
        modal.find('#data_fim').val(button.data('data_fim'));
        modal.find('#horario_fim').val(button.data('horario_fim'));
    });

    // $('.modal_atividade').draggable({
    //     handle: ".modal-header"
    // });

    $('#btnSalvar').click(function() {
        salvarOficina();
    });

    $('#btnSalvarComoNovo').click(function() {
        $('#novoCodOficina').val(0);
        $('#salvarOficina').val('Adicionar');
        salvarOficina();
    });

    $('.btn-excluir-atividade').click(function() {
        var id = $(this).data('id');
        excluirOficina(id);
    });

    // Toggle campos oficina
    $('.btn-toggle-oficina').click(function() {
        var $btn = $(this);
        var codOficina = $btn.data('cod_oficina');
        var campo = $btn.data('campo');
        $btn.prop('disabled', true);
        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {'codOficina': codOficina, 'campo': campo, 'c': 'GerenciarOficina', 'm': 'toggleCampo', '_submit_check': '1'},
            dataType: "json",
            cache: false,
            success: function(data) {
                if (data.statusMessage == '1') {
                    $btn.text(data.novoLabel);
                    if (campo == 'status') {
                        $btn.toggleClass('btn-success', data.novoValor == 'ATIVADA');
                        $btn.toggleClass('btn-default', data.novoValor != 'ATIVADA');
                    } else {
                        $btn.toggleClass('btn-success', data.novoValor == '1');
                        $btn.toggleClass('btn-default', data.novoValor != '1');
                    }
                } else {
                    alert(data.message);
                }
            },
            error: function() {
                alert('Erro ao alternar campo.');
            },
            complete: function() {
                $btn.prop('disabled', false);
            }
        });
    });
});   

// Mostra o campo "Titulo para certificado" apenas quando Liberar certificado = Sim
function toggleTituloCertificado() {
    $('#row_titulo_certificado').toggle($('#liberar_certificado').val() == '1');
}
$(document).on('change', '#liberar_certificado', toggleTituloCertificado);
// Sugere o texto de investimento a partir do valor individual, sem sobrescrever texto proprio do usuario
var investimentoSugerido = '';
$(document).on('input change', '#valor_atividade', function() {
    var v = parseFloat(String($(this).val()).replace(',', '.'));
    var $inv = $('#investimento');
    var atual = $.trim($inv.val());
    if (atual !== '' && atual !== investimentoSugerido) { return; }
    if (!isFinite(v) || v <= 0) {
        if (atual === investimentoSugerido) { $inv.val(''); investimentoSugerido = ''; }
        return;
    }
    investimentoSugerido = 'Investimento: R$ ' + v.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    $inv.val(investimentoSugerido);
});
function salvarOficina() {
    $('#formOficina').ajaxSubmit({
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
                // carregarTabelaOficinas();
                $('#modal_atividade').modal('hide');
                window.location.reload();                
            }
        }
        , complete: function() {
            stopLoading();
        }
    });
} 
function excluirOficina(codOficina) {
    if (confirm('Deseja realmente excluir Oficina?')) {
        $.ajax({
            type: "POST",
            url: '<?php echo  WB_URL_COMMAND ?>&t=' + Math.random(),
            data: ({'codOficina': codOficina, 'c': 'GerenciarOficina', 'm': 'excluirOficina', '_submit_check': '1'}),
            dataType: "json",
            async: false,
            cache: false,
            beforeSend: function() {
                startLoading();
            },
            success: function(data) {
                processJson(data);
                if (data.statusMessage) {
                    // carregarTabelaOficinas();
                    window.location.reload();     
                }
            }
            , complete: function() {
                stopLoading();
            }
        });
    }
}
</script>

<!-- MODAL -->
<div class="modal fade" id="modal_atividade" tabindex="-1" role="dialog" aria-labelledby="modal_atividade_titulo">
  <div class="modal-dialog" role="document">
    <div class="modal-content">

      <!-- <form id="form_atividade" method="post" action="GerenciarAtividadeAC.php"> -->
      <form  id="formOficina" name="formOficina" action="<?php echo  WB_URL_COMMAND ?>&dummy=" method="post">
        <input type="hidden" name="acao" value="atualizar_atividade_lista">
        <input type="hidden" name="id" id="id">

        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
            <span aria-hidden="true">&times;</span>
          </button>
          <h4 class="modal-title" id="modal_atividade_titulo">Editar atividade</h4>
        </div>

        <div class="modal-body">
          <div class="form-group">
            <label for="titulo">Título da atividade</label>
            <input type="text" class="form-control" name="titulo" id="titulo" required>
          </div>

          <div class="form-group">
            <label for="nome_ministrante">Nome do ministrante</label>
            <input type="text" class="form-control" name="nome_ministrante" id="nome_ministrante" maxlength="255">
          </div>

          <div class="row">
            <div class="col-sm-4">
              <div class="form-group">
                <label for="ordem">Ordem</label>
                <input type="text" class="form-control" name="ordem" id="ordem">
              </div>
            </div>

            <div class="col-sm-4">
              <div class="form-group">
                <label for="status">Status</label>
                <select class="form-control" name="status" id="status">
                  <option value="ATIVADA">Ativa</option>
                  <option value="DESATIVADA">Desativa</option>
                </select>
              </div>
            </div>

            <div class="col-sm-4">
              <div class="form-group">
                <label for="receber_inscricao">Receber inscrição</label>
                <select class="form-control" name="receber_inscricao" id="receber_inscricao">
                  <option value="1">Sim</option>
                  <option value="0">Não</option>
                </select>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-sm-6">
              <div class="form-group">
                <label for="tipo">Modalidade da Opção:</label>

                <select class='form-control' name="tipo" id="tipo">
                    <option></option>
                    <?php
                    $tipo_atividade = $objOficina->getTipo();
                    CarregarOptionsAC::tipoAtividades($tipo_atividade, $debug);
                    ?>
                </select>

              </div>
            </div>

            <div class="col-sm-6">
              <div class="form-group">
                <label for="numero_vagas">Qtd. vagas</label>
                <input type="text" class="form-control" name="numero_vagas" id="numero_vagas">
              </div>
            </div>
          </div>

            <!-- <div class="row">
                <div class="col-sm-6">
                  <div class="form-group">
                    <label for="data_curso">Dt. Atividade:</label>
                    <input class='form-control' type='date' name='data_curso' value='' 
                        size='60' maxlength='255' onBlur='trim(this);' onkeypress='javascript: return isAlfa(window.event.keyCode);'>
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="form-group">
                <label for="data_fim">Dt.Fim Atividade:</label>
                <input class='form-control' type='date' name='data_fim' value='' 
                    size='60' maxlength='255' onBlur='trim(this);' onkeypress='javascript: return isAlfa(window.event.keyCode);'>
                  </div>
                </div>
            </div> -->

            <div class="row">
                <div class="col-sm-6">
                <div class="form-group">
                    <label for="valor_atividade">Valor individual</label> (ex: 999.00)
                    <input type="text" class="form-control" name="valor_atividade" id="valor_atividade">
                </div>
                </div>

                <div class="col-sm-6">
                <div class="form-group">
                    <label for="liberar_certificado">Liberar certificado</label>
                    <select class="form-control" name="liberar_certificado" id="liberar_certificado">
                    <option value="1">Sim</option>
                    <option value="0">Não</option>
                    </select>
                </div>
                </div>
          </div>
          <div class="row" id="row_titulo_certificado" style="display:none;">
                <div class="col-sm-12">
                <div class="form-group">
                    <label for="titulo_certificado">Título para certificado</label>
                    <small class="text-muted">(vazio = usa o título da atividade)</small>
                    <input type="text" class="form-control" name="titulo_certificado" id="titulo_certificado" maxlength="255">
                </div>
                </div>
          </div>
          <div class="row">
                <div class="col-sm-12">
                <div class="form-group">
                    <label for="investimento">Texto de investimento</label>
                    <small class="text-muted">(aparece para o inscrito na seleção/confirmação; vazio = não mostra)</small>
                    <textarea class="form-control" name="investimento" id="investimento" rows="2"></textarea>
                </div>
                </div>
          </div>
          <div class="row">
                <div class="form-group col-sm-6">
                    <label for="data_inicio_inscricao">Data Inicio Inscrição:</label>
                    
                    <input class='form-control' type='datetime-local' name='data_inicio_inscricao' id='data_inicio_inscricao' value='' 
                        size='60' maxlength='255' onBlur='trim(this);' onkeypress='javascript: return isAlfa(window.event.keyCode);'>
                </div>
                <div class="form-group col-sm-6">
                    <label for="data_fim_inscricao">Data Fim Inscrição:</label>
                    
                    <input class='form-control' type='datetime-local' name='data_fim_inscricao' id='data_fim_inscricao' value='' 
                        size='60' maxlength='255' onBlur='trim(this);' onkeypress='javascript: return isAlfa(window.event.keyCode);'>
                    
                </div>
            </div>
                      <div class="row">
                <?php if ($objOficina->getTipo()!='Agenda') { ?>
                <div class="form-group col-sm-4">
                    <label for="data_curso">Dt. Atividade:</label>
                    <input class='form-control' type='date' name='data_curso' id='data_curso' value='' 
                        size='60' maxlength='255' onBlur='trim(this);' onkeypress='javascript: return isAlfa(window.event.keyCode);'>
                </div>
                <?php } ?>
                <div class="form-group col-sm-4">
                    <label for="data_inicio">Dt.Inicio Atividade:</label>
                    <input class='form-control' type='date' name='data_inicio' id='data_inicio' value='' 
                        size='60' maxlength='255' onBlur='trim(this);' onkeypress='javascript: return isAlfa(window.event.keyCode);'>
                        <!-- Campo de Hora -->
                        <label for="horario_ini">Hora Inicio:</label>
                        <input class='form-control' type="text" id="horario_ini" name="horario_ini" pattern="\d{2}h\d{2}" maxlength="5" value='<?php echo $objOficina->getHorarioIni()?>'> (ex: 09h00)
                </div>
                <?php if ($objOficina->getTipo()!='Agenda') { ?>
                <div class="form-group col-sm-4">
                    <label for="data_fim">Dt.Fim Atividade:</label>
                    <input class='form-control' type='date' name='data_fim' id='data_fim' value='' 
                        size='60' maxlength='255' onBlur='trim(this);' onkeypress='javascript: return isAlfa(window.event.keyCode);'>
                        <!-- Campo de Hora -->
                        <label for="horario_fim">Hora Fim:</label>
                        <input class='form-control' type="text" id="horario_fim" name="horario_fim" pattern="\d{2}h\d{2}"  maxlength="5" value='<?php echo $objOficina->getHorarioFim()?>'>  (ex: 09h00)
                </div>
                <?php } ?>
            </div>  
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
            <!-- <button type="submit" class="btn btn-primary">Salvar</button> -->
            <button class="btn  btn-primary" type="button" id="btnSalvar" >Salvar</button>
            <!--<button class="btn btn-xs btn-primary" type="button" id="btnSalvarComoNovo" >Salvar como novo registro</button>-->

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
            <input type="hidden" value="GerenciarOficina" name="c">
            <input type="hidden" value="salvarOficina"    name="m">
            
            <br>&nbsp;
            <!-- <input type="hidden" name="novoCodOficina" id="novoCodOficina" value="0"/> -->
            <input type="hidden" name="codOficina"     id="codOficina"     value=""/>                
            
        </div>        
      </form>

    </div>
  </div>
</div>

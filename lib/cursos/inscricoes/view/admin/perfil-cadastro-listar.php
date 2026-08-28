<?php
require_once(dirname(__FILE__)."/../../include/config.php");

echo "<hr size='1' width='100%'>";
?>
  <table class="table table-hover table-condensed tablesorter">
    <thead>
      <tr>
        <th>Ação</th>
        <th>Nome do Grupo</th>
        <th>Ordem</th>
        <th>Status</th>
        <th>Recebendo<br>Inscrições</th>
        <th>Vagas<br>Limitadas</th>
        <th>Total<br>Inscritos/Vagas</th>
        <th>Insento</th>
        <th>Envio de<br>Trabalhos</th>
        <th>Permite<br>Atividades</th>
        <th>Liberar<br>Certificado</th>
      </tr>
    </thead>
    <tbody>
<?php
try {

    $objPerfil = new Perfil();
    $objPerfil->carregarListaPerfil(' order by ordem, cod_perfil desc',$debug);

    foreach ($objPerfil->getListaPerfil() as $objL) {
        if($dataHoraAnt != $objL->getDataIni().$objL->getHorarioIni()){
            echo "<tr valign='middle' bgcolor='{$bgcolor}'>"
            . "<td width='100%' colspan='7' style='padding: 4px 4px 6px 4px;'>"
            . "<h4>{$objL->getDataIni()} - "
            . "{$objL->getHorarioIni()}</h4>"
            . "</td></tr>";
        }
        $dataHoraAnt = $objL->getDataIni().$objL->getHorarioIni();
        ?>     
        <tr id='<?php echo $objL->getCodPerfil()?>'>
        <td width="50px" >
<!--            <a title="Visualizar Perfil <?php echo $objL->getCodPerfil()?>"        href="<?php echo WB_URL_VIEW?>?content=view/admin/perfil-cadastro-ok.php&cod_perfil=<?php echo $objL->getCodPerfil()?>" class="button btn btn-default btn-xs">
            <span class="glyphicon glyphicon-eye-open"></span></a>-->

            <button type="button"
                class="btn btn-xs btn-editar-grupo"
                title="Editar grupo"
                data-toggle="modal" data-target="#modal_grupo"
                data-id="<?php echo $objL->getCodPerfil() ?>"
                data-titulo="<?php echo $objL->getTitulo() ?>"
                data-ordem="<?php echo $objL->getOrdem() ?>"
                data-status="<?php echo trim($objL->getStatus()) ?>"
                data-perfil_isento="<?php echo trim($objL->getPerfilIsento()) ?>"
                data-verificar_envio_trabalho="<?php echo trim($objL->getVerificarEnvioTrabalho()) ?>"
                data-verificar_selecionar_atividade="<?php echo trim($objL->getVerificarSelecionarAtividade()) ?>"
                data-qtd_atividade_permite="<?php echo trim($objL->getQtdAtividadePermite()) ?>"
                data-verificar_vagas_limitadas="<?php echo trim($objL->getVerificarVagasLimitadas()) ?>"
                data-receber_inscricao="<?php echo $objL->getReceberInscricao() ?>"
                data-vagas="<?php echo $objL->getVagas() ?>"
                data-liberar_certificado="<?php echo $objL->getLiberarCertificado() ?>"
                data-investimento=""
                data-investimento-id="inv_<?php echo $objL->getCodPerfil() ?>">
                <span class="glyphicon glyphicon-pencil"></span>
            </button>
            <div id="inv_<?php echo $objL->getCodPerfil() ?>" style="display:none;">
            <?php echo $objL->getInvestimento(); ?>
            </div>
            <a title="Alterar Perfil <?php echo $objL->getCodPerfil()?>" href="<?php echo WB_URL_VIEW?>?content=view/admin/perfil-cadastro-alterar.php&cod_perfil=<?php echo $objL->getCodPerfil()?>" class="button btn btn-default btn-xs">
            <span class="glyphicon glyphicon-folder-open"></span></a>
            
            <!-- <a title="Excluir Perfil <?php echo numberPad($objL->getCodPerfil(),2) ?>" href="<?php echo WB_URL_VIEW?>?content=view/admin/perfil-cadastro-excluir-grava.php&cod_perfil=<?php echo $objL->getCodPerfil()?>" class="button btn btn-default btn-xs">
            <span class="glyphicon glyphicon-trash"></span></a> -->

            <button type="button"
                class="btn btn-xs btn-excluir-grupo"
                title="Excluir grupo"
                data-id="<?php echo $objL->getCodPerfil() ?>">
                <span class="glyphicon glyphicon-trash"></span>
            </button>     

        </td>
        <td>
            <a style='font-size:16px;' class='link' title="Listar inscritos nesta perfil" href='<?php echo WB_URL_VIEW?>?content=view/admin/consulta-dados-congresso-listagem-inscritos.php&cod_perfil=<?php echo $objL->getCodPerfil() ?>&cod_evento=<?php echo $objL->getCodEvento() ?>'>
            <?php echo $objL->getTitulo() ?></a>
            <?php
            if($objL->getInvestimento()!=''){
                echo "Investimento:<br>";
                echo $objL->getInvestimento();
            }
            ?>
            <br>Taxas/Períodos ou Lotes:
            <div style='margin-top:0px;margin-bottom: 5px;'>
            <?php
            try {
                $orderby = 'periodo_inicial, periodo_final';
                $objL->carregarListaTaxas('', $orderby, $debug);
                foreach ($objL->getListaTaxas() as $objLT) {
                    if($objLT->getStatus() == 'ATIVADA') { 
                    ?>
                    <div class='mb-1'>
                        <button type="button"
                            class="btn btn-xs btn-abrir-modal-url"
                            data-toggle="modal"
                            data-target="#modal_taxa"
                            data-url="<?php echo WB_URL_MODAL; ?>?content=view/admin/taxa-cadastro-campos.php&cod_taxa=<?php echo $objL->getCodPerfil(); ?>"
                            data-id="<?php echo $objLT->getCodTaxa() ?>"
                            data-cod_taxa="<?php echo $objLT->getCodTaxa() ?>"
                            data-cod_perfil="<?php echo $objLT->getCodPerfil() ?>"
                            data-status="<?php echo $objLT->getStatus() ?>"
                            data-tx_unica_a_vista_valor="<?php echo $objLT->getTxUnicaAVistaValor() ?>"
                            data-max_data_vencimento="<?php echo $objLT->getMaxDataVencimento() ?>"
                            data-periodo_inicial="<?php echo $objLT->getPeriodoInicial() ?>"
                            data-periodo_final="<?php echo $objLT->getPeriodoFinal() ?>">
                            <span class="glyphicon glyphicon-pencil"></span>
                        </button>

                    <!-- <br><a title="Alterar Taxa" href="<?php echo WB_URL_VIEW?>?content=view/admin/taxa-cadastro-alterar.php&cod_taxa=<?php echo $objLT->getCodTaxa()?>"
                    class="button btn btn-default btn-xs">
                    <span class="glyphicon glyphicon-folder-open"></span></a> -->

                    <?php 
                    echo " R$ ".$objLT->getTxUnicaAVistaValor();
                    echo " - Venc.: ".formatDateHoraTrans($objLT->getMaxDataVencimento(),'YYYY-MM-DD','DD/MM/YYYY')." - status: ".$objLT->getDesStatus();
                    echo " <br>Período: ".formatDateView($objLT->getPeriodoInicial())." - ".formatDateView($objLT->getPeriodoFinal());
                     ?>
                     </div>
                    <?php    
                    }
                }
            } catch (Exception $exc) {
                //echo $exc->getMessage();
            }
            ?>
            </div>
            <a style="margin-bottom:10px;" title="Adcionar mais uma taxa" href="<?php echo WB_URL_VIEW?>?content=view/admin/taxa-cadastro-incluir.php&cod_perfil=<?php echo $objL->getCodPerfil()?>">adc. + Taxa</a>

        </td>
        <td ><?php echo numberPad($objL->getOrdem(),2) ?></td>
        <td >
            <?php $statusVal = trim($objL->getStatus()); ?>
            <button type="button" class="btn btn-xs btn-toggle-campo <?php echo ($statusVal=='ATIVADA')?'btn-success':'btn-default'; ?>"
                data-cod_perfil="<?php echo $objL->getCodPerfil(); ?>"
                data-campo="status"
                title="Clique para alternar">
                <?php echo ($statusVal=='ATIVADA')?'Ativada':'Desativada'; ?>
            </button>
        </td>
        <td >
            <?php $val = $objL->getReceberInscricao(); ?>
            <button type="button" class="btn btn-xs btn-toggle-campo <?php echo ($val==1)?'btn-success':'btn-default'; ?>"
                data-cod_perfil="<?php echo $objL->getCodPerfil(); ?>"
                data-campo="receber_inscricao"
                title="Clique para alternar">
                <?php echo ($val==1)?'Sim':'Não'; ?>
            </button>
            <?php
            if($objL->getVerificarInscritoApto()==true){
                ?>
                <br>
                <a style="margin-bottom:10px;" title="Gerenciar Inscritos Aptos"
               href="<?php echo WB_URL_VIEW?>?content=view/admin/GerenciarInscritoApto.php">Somente Aptos Ativado</a>
                <?php
            }
            ?>
        </td>
        <td >
            <?php $val = $objL->getVerificarVagasLimitadas(); ?>
            <button type="button" class="btn btn-xs btn-toggle-campo <?php echo ($val==1)?'btn-success':'btn-default'; ?>"
                data-cod_perfil="<?php echo $objL->getCodPerfil(); ?>"
                data-campo="verificar_vagas_limitadas"
                title="Clique para alternar">
                <?php echo ($val==1)?'Sim':'Não'; ?>
            </button>
        </td>
        <td width="50px"><?php echo numberPad($objL->getTotalInscritos(),3) .' / '.numberPad($objL->getVagas(),3); ?> </td>
        <td >
            <?php $val = $objL->getPerfilIsento(); ?>
            <button type="button" class="btn btn-xs btn-toggle-campo <?php echo ($val==1)?'btn-success':'btn-default'; ?>"
                data-cod_perfil="<?php echo $objL->getCodPerfil(); ?>"
                data-campo="perfil_isento"
                title="Clique para alternar">
                <?php echo ($val==1)?'Sim':'Não'; ?>
            </button>
        </td>
        <td >
            <?php $val = $objL->getVerificarEnvioTrabalho(); ?>
            <button type="button" class="btn btn-xs btn-toggle-campo <?php echo ($val==1)?'btn-success':'btn-default'; ?>"
                data-cod_perfil="<?php echo $objL->getCodPerfil(); ?>"
                data-campo="verificar_envio_trabalho"
                title="Clique para alternar">
                <?php echo ($val==1)?'Sim':'Não'; ?>
            </button>
        </td>
        <td >
            <?php $val = $objL->getVerificarSelecionarAtividade(); ?>
            <button type="button" class="btn btn-xs btn-toggle-campo <?php echo ($val==1)?'btn-success':'btn-default'; ?>"
                data-cod_perfil="<?php echo $objL->getCodPerfil(); ?>"
                data-campo="verificar_selecionar_atividade"
                title="Clique para alternar">
                <?php echo ($val==1)?'Sim':'Não'; ?>
            </button>
        </td>
        <td >
            <?php $val = $objL->getLiberarCertificado(); ?>
            <button type="button" class="btn btn-xs btn-toggle-campo <?php echo ($val==1)?'btn-success':'btn-default'; ?>"
                data-cod_perfil="<?php echo $objL->getCodPerfil(); ?>"
                data-campo="liberar_certificado"
                title="Clique para alternar">
                <?php echo ($val==1)?'Sim':'Não'; ?>
            </button>
            <?php echo $objL->getCargaHorariaCertificado()? "<br>ch: ".$objL->getCargaHorariaCertificado() : ""; ?>
        </td>
        </tr>
        <?php
    }
} catch (Exception $exc) {
    echo "<tr><td colspan=\"5\">" . $exc->getMessage() . "</td></tr>";
}
echo "</table>";
?>
<script>
$(document).ready(function () {
    $('#modal_taxa').on('show.bs.modal', function (e) {
        var $btn = $(e.relatedTarget);        // botão clicado
        var url  = $btn.data('url');          // URL do conteúdo
        var $md  = $(this);
        $('#mensagem').html('');
        $md.find('.modal-body').html('Carregando...');

        $.get(url, function (data) {
            $md.find('.modal-body').html(data);
            // agora o HTML do form já está dentro do modal
            $md.find('#cod_perfil').val($btn.data('cod_perfil'));
            $md.find('#cod_taxa').val($btn.data('cod_taxa'));
            $md.find('#status').val($btn.data('status'));
            $md.find('#tx_unica_a_vista_valor').val($btn.data('tx_unica_a_vista_valor'));
            $md.find('#max_data_vencimento').val($btn.data('max_data_vencimento'));
            $md.find('#periodo_inicial').val($btn.data('periodo_inicial'));
            $md.find('#periodo_final').val($btn.data('periodo_final'));
        });
    });
    $('#modal_taxa').on('shown.bs.modal', function () {
    var $md = $(this);

    // só tenta se existir
    if ($.fn.draggable) {
        $md.find('.modal-dialog').draggable({
        handle: '.modal-header',
        containment: 'window',
        scroll: false,
        cancel: 'input,textarea,select,button,a,label'
        });
    }
    });

    $('#modal_grupo').on('show.bs.modal', function (event) {       
        const button = $(event.relatedTarget); 
        const modal = $(this);
        
        // INPUTS (Text e Hidden)
        modal.find('#id').val(button.data('id')); 
        modal.find('#codPerfil').val(button.data('id')); 
        modal.find('#titulo').val(button.data('titulo'));
        modal.find('#tipo').val(button.data('tipo'));
        modal.find('#perfil_isento').val(button.data('perfil_isento'));
        modal.find('#verificar_envio_trabalho').val(button.data('verificar_envio_trabalho'));
        modal.find('#verificar_selecionar_atividade').val(button.data('verificar_selecionar_atividade'));
        modal.find('#qtd_atividade_permite').val(button.data('qtd_atividade_permite'));
        modal.find('#verificar_vagas_limitadas').val(button.data('verificar_vagas_limitadas'));
        modal.find('#ordem').val(button.data('ordem'));
        modal.find('#vagas').val(button.data('vagas'));
        modal.find('#valor').val(button.data('valor'));
        
        // SELECTS (Dropdowns)
        modal.find('#status').val(button.data('status'));
        modal.find('#receber_inscricao').val(button.data('receber_inscricao'));
        modal.find('#vagas_limitadas').val(button.data('vagas_limitadas'));
        modal.find('#liberar_certificado').val(button.data('liberar_certificado'));
        modal.find('#qtd_atividade_permite').val(button.data('qtd_atividade_permite'));

        var idInv = button.data('investimento-id');
        var html = $('#' + idInv).html();
        // Se TinyMCE estiver ativo nesse textarea
        if (window.tinymce && tinymce.get('investimento')) {
            tinymce.get('investimento').setContent(html);
        } else {
            // fallback (caso ainda não tenha inicializado)
            $('#investimento').val(html);
        }
    });

    // $('.modal_grupo').draggable({
    //     handle: ".modal-header"
    // });

    $('#btnSalvar').click(function() {
        salvarPerfil();
    });
    $('#btnSalvarTaxa').click(function() {
        salvarTaxa();
    });

    $('#btnSalvarComoNovo').click(function() {
        $('#novoCodPerfil').val(0);
        $('#salvarPerfil').val('Adicionar');
        salvarPerfil();
    });

    $('.btn-excluir-grupo').click(function() {
        var id = $(this).data('id');
        excluirPerfil(id);
    });

    $('.btn-toggle-campo').click(function() {
        var $btn = $(this);
        var codPerfil = $btn.data('cod_perfil');
        var campo = $btn.data('campo');
        $btn.prop('disabled', true);
        $.ajax({
            type: "POST",
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: {'codPerfil': codPerfil, 'campo': campo, 'c': 'GerenciarPerfil', 'm': 'toggleCampo', '_submit_check': '1'},
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

function salvarPerfil() {
    if (window.tinymce) tinymce.triggerSave();
    if (window.tinyMceCharCount && !tinyMceCharCount.podeSalvar()) return;
    $('#formPerfil').ajaxSubmit({
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
                // carregarTabelaPerfils();
                $('#modal_grupo').modal('hide');
                window.location.reload();                
            }
        }
        , complete: function() {
            stopLoading();
        }
    });
} 
function excluirPerfil(codPerfil) {
    if (confirm('Deseja realmente excluir Perfil?')) {
        $.ajax({
            type: "POST",
            url: '<?php echo  WB_URL_COMMAND ?>&t=' + Math.random(),
            data: ({'codPerfil': codPerfil, 'c': 'GerenciarPerfil', 'm': 'excluirPerfil', '_submit_check': '1'}),
            dataType: "json",
            async: false,
            cache: false,
            beforeSend: function() {
                startLoading();
            },
            success: function(data) {
                processJson(data);
                if (data.statusMessage) {
                    // carregarTabelaPerfils();
                    window.location.reload();     
                }
            }
            , complete: function() {
                stopLoading();
            }
        });
    }
}
function salvarTaxa() {
    $('#formTaxa').ajaxSubmit({
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
                // carregarTabelaPerfils();
                $('#modal_taxa').modal('hide');
                window.location.reload();                
            }
        }
        , complete: function() {
            stopLoading();
        }
    });
} 
</script>
<style>
    #modal_taxa .modal-header { cursor: move; }
</style>

<!--<style>
.table tbody {
    cursor: move;
}

.order-handle {
    cursor: move;
}   
</style>-->

<!-- MODAL -->
              
<div class="modal fade" id="modal_taxa" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <!-- <form id="form_grupo" method="post" action="GerenciargrupoAC.php"> -->
            <form  id="formTaxa" name="formTaxa" action="<?php echo  WB_URL_COMMAND ?>&dummy=" method="post">
                <input type="hidden" name="acao" value="atualizar_taxa_lista">
                <input type="hidden" name="id" id="id">
                <input type="hidden" name="cod_taxa" id="cod_taxa">

                <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="modal_grupo_titulo">Editar Taxa</h4>
                </div>

                <div class="modal-body"></div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <!-- <button type="submit" class="btn btn-primary">Salvar</button> -->
                    <button class="btn  btn-primary" type="button" id="btnSalvarTaxa" >Salvar</button>
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
                    <input type="hidden" value="GerenciarTaxa" name="c">
                    <input type="hidden" value="salvarTaxa"    name="m">
                    
                    <br>&nbsp;
                    <input type="hidden" name="codTaxa"     id="codTaxa"     value=""/>                
                    
                </div>        
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modal_grupo" tabindex="-1" role="dialog" aria-labelledby="modal_grupo_titulo">
  <div class="modal-dialog" role="document">
    <div class="modal-content">

      <!-- <form id="form_grupo" method="post" action="GerenciargrupoAC.php"> -->
      <form  id="formPerfil" name="formPerfil" action="<?php echo  WB_URL_COMMAND ?>&dummy=" method="post">
        <input type="hidden" name="acao" value="atualizar_grupo_lista">
        <input type="hidden" name="id" id="id">

        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
            <span aria-hidden="true">&times;</span>
          </button>
          <h4 class="modal-title" id="modal_grupo_titulo">Editar grupo</h4>
        </div>

        <div class="modal-body">
          <div class="form-group">
            <label for="titulo">Título da grupo</label>
            <input type="text" class="form-control" name="titulo" id="titulo" required>
          </div>

          <div class="row">
            <div class="col-sm-2">
              <div class="form-group">
                <label for="ordem">Ordem</label>
                <input type="text" class="form-control" name="ordem" id="ordem">
              </div>
            </div>

            <div class="col-sm-2">
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

            <div class="col-sm-4">
              <div class="form-group">
                <label for="perfil_isento">Perfil Isento</label>
                <select class="form-control" name="perfil_isento" id="perfil_isento">
                  <option value="1">Sim</option>
                  <option value="0">Não</option>
                </select>
              </div>
            </div>

            <div class="col-sm-4">
              <div class="form-group">
                <label for="verificar_vagas_limitadas">Limite de Vagas</label>
                <select class="form-control" name="verificar_vagas_limitadas" id="verificar_vagas_limitadas">
                  <option value="1">Sim</option>
                  <option value="0">Não</option>
                </select>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group">
                <label for="vagas">Qtd. vagas</label>
                <input type="text" class="form-control" name="vagas" id="vagas">
              </div>
            </div>

            <div class="col-sm-3">
              <div class="form-group">
                <label for="verificar_envio_trabalho">Enviar Trabalho</label>
                <select class="form-control" name="verificar_envio_trabalho" id="verificar_envio_trabalho">
                  <option value="1">Sim</option>
                  <option value="0">Não</option>
                </select>
              </div>
            </div>

            <div class="col-sm-4">
              <div class="form-group">
                <label for="verificar_selecionar_atividade">Selecionar Atividade</label>
                <select class="form-control" name="verificar_selecionar_atividade" id="verificar_selecionar_atividade">
                  <option value="1">Sim</option>
                  <option value="0">Não</option>
                </select>
              </div>
            </div>

            <div class="col-sm-3">
              <div class="form-group">
                <label for="qtd_atividade_permite">Qtd. Atividades</label>
                <input type="text" class="form-control" name="qtd_atividade_permite" id="qtd_atividade_permite">
              </div>
            </div>


            <!--
                <div class="col-sm-6">
                  <div class="form-group">
                    <label for="data_curso">Dt. grupo:</label>
                    <input class='form-control' type='date' name='data_curso' value='' 
                        size='60' maxlength='255' onBlur='trim(this);' onkeypress='javascript: return isAlfa(window.event.keyCode);'>
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="form-group">
                <label for="data_fim">Dt.Fim grupo:</label>
                <input class='form-control' type='date' name='data_fim' value='' 
                    size='60' maxlength='255' onBlur='trim(this);' onkeypress='javascript: return isAlfa(window.event.keyCode);'>
                  </div>
                </div>
                -->

            </div>
            <div class="row">
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
            <div class="row">
                <div class="col-sm-12" style="padding-left: 10px;padding-bottom: 3px; font-size: 13px;">
                    <span style="font-size: 12px" for="liberar_comprovante_inscricao" style="padding-left: 10px;padding-bottom: 3px;">Investimento: </span>
                    <div class="form-group col-sm-12" style="padding-left: 0px;">
                        <textarea class=editor_padrao rows=5 cols=70 name='investimento' id='investimento' onBlur='trim(this);' class='textob form_control'></textarea>
                    </div>                    
                </div>                  
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
            <input type="hidden" value="GerenciarPerfil" name="c">
            <input type="hidden" value="salvarPerfil"    name="m">
            
            <br>&nbsp;
            <!-- <input type="hidden" name="novoCodPerfil" id="novoCodPerfil" value="0"/> -->
            <input type="hidden" name="codPerfil"     id="codPerfil"     value=""/>                
            
        </div>        
      </form>

    </div>
  </div>
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

    });

</script>
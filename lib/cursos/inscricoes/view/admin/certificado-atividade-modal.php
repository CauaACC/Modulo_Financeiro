<?php
/*
 * Modal de edicao do certificado de UMA atividade.
 *
 * Vive num arquivo so porque duas telas usam o mesmo formulario: o Painel de
 * Certificados (bloco "3. Certificado por atividade") e a tela "Por atividade".
 * Enquanto eram duas copias, elas divergiram: a do Painel ficou sem o campo
 * "Local onde foi ministrado" e, como o gravarAtividade grava tudo o que recebe
 * (campo ausente vira ''), salvar pelo Painel APAGAVA o local preenchido na
 * outra tela.
 *
 * Quem inclui precisa ter jQuery e os helpers startLoading/stopLoading/processJson.
 * Abre por qualquer botao .btn-edit-atv (data-oficina, data-titulo) ou chamando
 * abrirModalAtividade(cod, titulo).
 */
?>
<div id="modalAtvCert" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:1050;">
    <div style="max-width:640px; margin:5% auto; background:#fff; border-radius:10px; overflow:hidden; box-shadow:0 10px 40px rgba(0,0,0,.3);">
        <div style="display:flex; justify-content:space-between; align-items:center; padding:12px 18px; background:#2e7d32; color:#fff;">
            <b>Certificado da atividade &mdash; <span id="atvTitulo"></span></b>
            <span style="cursor:pointer; font-size:20px;" id="atvFechar">&times;</span>
        </div>
        <div style="padding:16px 18px; max-height:70vh; overflow:auto;">
            <input type="hidden" id="atvCod">
            <div class="form-group" style="background:#f7f9fc; border:1px solid #e3e9f0; border-radius:6px; padding:10px 12px;">
                <label style="margin-bottom:2px;">Quem participou desta atividade recebe um certificado da atividade?</label>
                <div style="color:#777; font-size:12px; margin-bottom:6px;">
                    &Eacute; um certificado <b>s&oacute; desta atividade</b>, separado do certificado geral do evento.
                    Os campos abaixo valem para ele.
                </div>
                <div>
                    <label style="font-weight:normal; margin-right:16px;"><input type="radio" name="atv_libera" value="1"> Sim, emite</label>
                    <label style="font-weight:normal;"><input type="radio" name="atv_libera" value="0"> N&atilde;o emite</label>
                </div>
            </div>

            <div style="color:#777; font-size:12px; margin:2px 0 10px;">
                <i class="fa fa-info-circle"></i> Campo em branco <b>n&atilde;o sai vazio</b> no certificado:
                ele usa o que est&aacute; em cinza, herdado do evento ou da pr&oacute;pria atividade.
            </div>

            <div class="form-group"><label>T&iacute;tulo do certificado</label><input type="text" id="atv_titulo_cert" class="form-control" maxlength="255"></div>
            <div class="form-group"><label>Texto de qualifica&ccedil;&atilde;o</label><input type="text" id="atv_qualif" class="form-control" maxlength="255"></div>
            <div class="form-group"><label>Carga hor&aacute;ria (em horas)</label><input type="text" id="atv_ch" class="form-control" maxlength="50"></div>
            <div class="form-group"><label>Carga hor&aacute;ria por extenso</label><input type="text" id="atv_ch_ext" class="form-control" maxlength="255"></div>
            <div class="form-group"><label>Per&iacute;odo do curso</label><input type="text" id="atv_periodo" class="form-control" maxlength="255"></div>
            <div class="form-group"><label>Data de emiss&atilde;o</label><input type="text" id="atv_data_emissao" class="form-control" maxlength="255"></div>
            <div class="form-group"><label>Local onde foi ministrado</label><input type="text" id="atv_local" class="form-control" maxlength="255"></div>
        </div>
        <div style="padding:12px 18px; border-top:1px solid #eee; text-align:right;">
            <button type="button" class="btn btn-default" id="atvCancelar">Cancelar</button>
            <button type="button" class="btn btn-primary" id="atvSalvar"><i class="fa fa-save"></i> Salvar</button>
        </div>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function () {
    var $modal = $('#modalAtvCert');
    function fecharModal() { $modal.hide(); }
    $('#atvFechar, #atvCancelar').on('click', fecharModal);
    $modal.on('click', function (e) { if (e.target === this) fecharModal(); });

    // Campo vazio com valor herdado conhecido: preenche como EXEMPLO editavel
    // (o usuario altera e salva), em vez de so um placeholder fantasma que da
    // a impressao de preenchido sem gravar nada. Sem valor conhecido, so
    // explica a regra no placeholder.
    function herdado(seletor, valor, regra) {
        var v = $.trim(valor || '');
        var $campo = $(seletor);
        if ($.trim($campo.val()) === '' && v !== '') {
            $campo.val(v);
        }
        $campo.attr('placeholder', v !== '' ? ('em branco, sai: ' + v) : (regra || 'opcional'));
    }

    // Global de proposito: a tela "Por atividade" abre o modal pelo seletor de
    // "adicionar", nao so pelo botao da lista.
    // sugerirSim: usado so pelo fluxo "Adicionar texto para uma atividade"
    // (atividade ainda sem customizacao) -- marca "Sim, emite" de saida, ja
    // que preencher os dados aqui e o proprio objetivo de estar nesta tela.
    // Nao mexe se a atividade ja tinha "Sim" salvo (edicao normal).
    window.abrirModalAtividade = function (cod, titulo, sugerirSim) {
        $('#atvTitulo').text(titulo || ('#' + cod));
        $('#atvCod').val(cod);
        startLoading && startLoading();
        $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            {'c': 'PainelCertificados', 'm': 'carregarAtividade', '_submit_check': '1', 'codOficina': cod},
            function (d) {
                var libera = parseInt(d.liberar_certificado, 10) === 1 ? 1 : 0;
                if (sugerirSim && !libera) { libera = 1; }
                $('input[name=atv_libera][value="' + libera + '"]').prop('checked', true);
                $('#atv_titulo_cert').val(d.titulo_certificado || '');
                $('#atv_qualif').val(d.qualificacao_certificado || '');
                $('#atv_ch').val(d.carga_horaria || '');
                $('#atv_ch_ext').val(d.carga_horaria_extenso || '');
                $('#atv_periodo').val(d.periodo_certificado || '');
                $('#atv_data_emissao').val(d.data_emissao_certificado || '');
                $('#atv_local').val(d.local || '');

                // Placeholder = o que o certificado REALMENTE imprime se o campo
                // ficar em branco (vem do servidor, mesma heranca do gerador).
                var h = d.heranca || {};
                herdado('#atv_titulo_cert',  h.titulo,       'em branco, usa o título da atividade');
                herdado('#atv_ch',           h.cargaHoraria, 'em branco, usa a carga horária da participação de cada inscrito');
                herdado('#atv_periodo',      h.periodo,      'em branco, usa o período cadastrado no evento (hoje sem preencher)');
                herdado('#atv_data_emissao', h.dataEmissao,  'em branco, usa a data configurada em Textos & Regras');
                herdado('#atv_local',        h.local,        'em branco, usa a cidade do evento (hoje sem preencher)');
                $('#atv_qualif').attr('placeholder', 'opcional — em branco, esta linha não sai no certificado');
                $('#atv_ch_ext').attr('placeholder', 'em branco, usa a carga horária acima');

                $modal.show();
            }, 'json'
        ).always(function () { stopLoading && stopLoading(); });
    };

    // Delegado: serve para a lista do Painel e para a da tela "Por atividade".
    $(document).on('click', '.btn-edit-atv', function () {
        abrirModalAtividade($(this).data('oficina'), $(this).data('titulo'));
    });

    $('#atvSalvar').on('click', function () {
        var dados = {
            'c': 'PainelCertificados', 'm': 'gravarAtividade', '_submit_check': '1',
            'codOficina': $('#atvCod').val(),
            'liberar_certificado': $('input[name=atv_libera]:checked').val() || '0',
            'titulo_certificado': $('#atv_titulo_cert').val(),
            'qualificacao_certificado': $('#atv_qualif').val(),
            'carga_horaria': $('#atv_ch').val(),
            'carga_horaria_extenso': $('#atv_ch_ext').val(),
            'periodo_certificado': $('#atv_periodo').val(),
            'data_emissao_certificado': $('#atv_data_emissao').val(),
            'local': $('#atv_local').val()
        };
        startLoading && startLoading();
        $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), dados,
            function (d) {
                processJson && processJson(d);
                if (d && d.statusMessage) { fecharModal(); setTimeout(function () { location.reload(); }, 600); }
            }, 'json'
        ).always(function () { stopLoading && stopLoading(); });
    });
});
</script>

<?php

class GerenciarUploadDocumentoAC {

    function __construct(){
        $this->codEvento = COD_EVENTO;
        $this->varDiretorio = WB_PATH_LOG_TRABALHO;
    }

    public static function gerarBlocoUpload($objInscrito, $tipoDocumento='Documento', $slug_tipo_documento='documento', $obrigatorio=true, $debug = false)
    {
        $objTrabalho = new Trabalho();
        $objTrabalho->setCodInscrito($objInscrito->getCodInscrito());
        $objTrabalho->setCodEvento(COD_EVENTO);
        $objTrabalho->setTipoDocumento($tipoDocumento);
        $objTrabalho->setSlugTipoDocumento($slug_tipo_documento);
        $objTrabalho->setStatus('ATIVADA');
        $objTrabalho->getByDocumentoUltimo(); 
        $fws_['slug_tipo_documento'] = $slug_tipo_documento;
        ob_start();
        ?>
        <?php
        $jaEnviado = ($objTrabalho->getVersao() != '') ? 'true' : 'false';
        $objEvConfBloco = EventoConfiguracao::getInstancePopulado(COD_EVENTO);
        $periodoEncerradoBloco = $objEvConfBloco->isPeriodoEnvioTrabalhoEncerrado();
        ?>
        <div class="list-group-item item-liberado">
            <strong>📎 Enviar <?php echo htmlspecialchars($tipoDocumento); ?></strong>
            <?php if ($obrigatorio) { echo '<span style="color: red;">(Obrigatório)</span>'; } ?>
            <ul>
                <li>
                    <div class="upload-documento"
                         data-documento="<?php echo $fws_['slug_tipo_documento']; ?>"
                         data-obrigatorio="<?php echo $obrigatorio ? 'true' : 'false'; ?>"
                         data-enviado="<?php echo $jaEnviado; ?>"
                         data-rotulo="<?php echo htmlspecialchars($tipoDocumento, ENT_QUOTES); ?>">
                        <?php
                        $gGerenciarUpload = new GerenciarUploadDocumentoAC();
                        $gGerenciarUpload->formEnviarDocumento($objInscrito, $objTrabalho, $fws_, $debug = false);
                        ?>
                    </div>
                    <?php if (!empty($objTrabalho->getArquivoGerado()) && !$periodoEncerradoBloco): ?>
                        <div class="botaoAbrir" data-cod-area="<?php echo $fws_['slug_tipo_documento']; ?>">
                            <button type="button"  class="btnLink" onclick="abrirArquivoGerado('<?php echo $objTrabalho->getArquivoGerado(); ?>', '<?php echo $objTrabalho->getCodEvento(); ?>')">
                                📄 Para conferir documento enviado, <b>Clique aqui para visualizar documento</b>.
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="botaoAbrir" data-cod-area="<?php echo $fws_['slug_tipo_documento']; ?>"></div>
                    <?php endif; ?>
                </li>
            </ul>
        </div>
        <?php
        return ob_get_clean();
    }

    public function formEnviarDocumento($objInscrito, $objTrabalho, $fws_, $debug = false){
        $objEvConf = EventoConfiguracao::getInstancePopulado(COD_EVENTO);
        $periodoEnvioArqEncerrado = $objEvConf->isPeriodoEnvioTrabalhoEncerrado();
        if(!$fws_['tipo_documento']){
            $fws_['tipo_documento'] = $objTrabalho->getTipoDocumento();
        }
        $naoMostrar = 1;
        ?>
        <?php if ($naoMostrar) {?>
        <form class="formUpload" enctype="multipart/form-data">
            <input type="hidden" name="c" value="GerenciarUploadDocumento">
            <input type="hidden" name="m" value="salvarTrabalho">
          <input type="hidden" name="slug_tipo_documento" value="<?php echo $fws_['slug_tipo_documento']?>">
          <input type="hidden" name="tipo_documento" value="<?php echo $fws_['tipo_documento']?>">
          <input type="hidden" name="codInscrito" value="<?php echo $objInscrito->getCodInscrito() ?>">
          <?php 
          if ($objTrabalho->getCodTrabalho() != '') {
            echo '<input type="hidden" name="codTrabalho" id="codTrabalho-'.$objInscrito->getCodInscrito().'" value="' . $objTrabalho->getCodTrabalho() . '">';
          } else {
            echo '<input type="hidden" name="codTrabalho" id="codTrabalho-'.$objInscrito->getCodInscrito().'" value="">';
          }
          ?>
          <?php if ($periodoEnvioArqEncerrado !== true) { ?>
            <input type="file" name="arquivo" required>
            <button type="submit">Clique para Enviar Arquivo</button>
            <progress class="barra-progresso" value="0" max="100" style="width: 100%; display:none;"></progress>
            <div class="mensagem-upload"></div>
          <?php }else{ ?>
            <?php if ($objTrabalho->getVersao() != ''){ ?>
                <!-- <span style="color: green;"><b>O período de envio encerrado.</b></span> -->
                <?php }else{ ?>
                <span style="color: red;"><b>O período de envio encerrado.</b></span>
                <?php } ?>
                <?php } ?>
        <?php } ?>
        </form>
        <div class="historico-envios">
        <?php
        if ($objTrabalho->getVersao() != ''){
            echo '<span style="color: green;">';
            echo '<b>Arquivo enviado com sucesso!</b> Última versão: ' . $objTrabalho->getVersao();
            $data = $objTrabalho->getDataAlteracao()? $objTrabalho->getDataAlteracao() : $objTrabalho->getDataInclusao();
                $data = formatDateView($data);
            echo '<br>Data do envio: ' . $data;
            echo '</span>';
        }else{
            echo '<span style="color: red;">';
            echo 'Nenhum arquivo enviado.';
            echo '</span>';
        } 
    }

    public static function gerarBlocoCriterios($objInscrito, $tipoDocumento='Documento', $slug_tipo_documento='documento', $debug = false)
    {
        $objEvConfCalc = EventoConfiguracao::getInstancePopulado(COD_EVENTO);
        $periodoEnvioArqEncerrado = $objEvConfCalc->isPeriodoEnvioTrabalhoEncerrado();
        $dataFimCalc = $objEvConfCalc->getTrabSubmissaoDatFim();
        // força o slug_tipo_documento e tipo_documento
        $fws_['slug_tipo_documento'] = 'criterios_pontuacao';
        $fws_['tipo_documento'] = 'criterios_pontuacao';
        $tipo_documento = 'criterios_pontuacao';

        $objTrabalho = new Trabalho();
        $objTrabalho->setCodInscrito($objInscrito->getCodInscrito());
        $objTrabalho->setCodEvento(COD_EVENTO);
        $objTrabalho->setTipoDocumento($tipoDocumento);
        $objTrabalho->setSlugTipoDocumento($slug_tipo_documento);
        $objTrabalho->setStatus('ATIVADA');
        $objTrabalho->getByDocumentoUltimo();

        ob_start();
        $mostrar = true;
        // Se o periodo encerrou: nao renderiza botao da calculadora, so o aviso.
        if ($periodoEnvioArqEncerrado) {
            $msgFim = $dataFimCalc ? formatDateView($dataFimCalc) : '';
            ?>
            <div class="list-group-item item-liberado">
                <div style="padding:10px 12px;border:1px solid #b30000;background:#fff3f3;color:#b30000;border-radius:4px;">
                    <strong>⚠ Calculadora indisponível — período de envio encerrado<?php echo $msgFim ? ' em ' . htmlspecialchars($msgFim) : ''; ?>.</strong>
                </div>
            </div>
            <?php
            return ob_get_clean();
        }
        ?>
        <div class="list-group-item item-liberado">
            <strong>📎 <?php //echo htmlspecialchars($tipoDocumento); ?></strong>
            <ul>
                <li>
                    <?php if($mostrar) {?>
                    <div class="upload-documento" data-documento="<?php echo $fws_['slug_tipo_documento']; ?>">
                        <?php
                        $cid = 'collapse_criterios_' . $objInscrito->getCodInscrito();
                        $labelBtnCollapse = (defined('COD_EVENTO') && COD_EVENTO == 129)
                            ? '📊 Abrir calculadora de pontuação (Anexo II) — clique para expandir'
                            : 'Efetuar autoavaliação, apenas para conferência (clique para abrir/fechar)';
                        // Botão sem data-toggle nativo: a abertura é controlada via JS
                        // após validar se os documentos obrigatórios foram enviados.
                        echo "<button type='button' class='btn btn-default btn btn-abrir-calculadora'
                                data-target-collapse='#{$cid}'
                                aria-expanded='false'
                                style='margin-bottom:10px;'>"
                                . htmlspecialchars($labelBtnCollapse) .
                                "</button>";
                        echo "<div class='alert alert-warning hidden' id='{$cid}_obrigatorios_msg' role='alert' style='margin:8px 0;'></div>";
                        echo "<div id='{$cid}' class='collapse criterios-collapse'>";
                        $gGerenciarUpload = new GerenciarUploadDocumentoAC();
                        $gGerenciarUpload->formEnviarDocumentoCriterio($objInscrito, $objTrabalho, $fws_, $debug = false);
                        echo "</div>";
                        ?>
                    </div>
                    <?php if (!empty($objTrabalho->getArquivoGerado())): ?>
                        <div class="botaoAbrir" data-cod-area="<?php echo $fws_['slug_tipo_documento']; ?>">
                            <button type="button"  class="btnLink" style="cursor: pointer;font-size:unset;" onclick="abrirArquivoGerado('<?php echo $objTrabalho->getArquivoGerado(); ?>', '<?php echo $objTrabalho->getCodEvento(); ?>')">
                                📄 Abrir autoavaliação enviada (última versão)
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="botaoAbrir" data-cod-area="<?php echo $fws_['slug_tipo_documento']; ?>"></div>
                    <?php endif; ?>
                    <?php }else { ?>
                        <div class="upload-documento" data-documento="<?php echo $fws_['slug_tipo_documento']; ?>">
                            <em>O formulário de envio da pontuação será liberado após o envio dos documentos obrigatórios.</em>
                        </div>
                    <?php } ?>

                </li>
            </ul>
        </div>
        <?php
        return ob_get_clean();
    }

// path: view/inscricao/form_enviar_documento_criterio.php
public function formEnviarDocumentoCriterio($objInscrito, $objTrabalho, $fws_, $debug = false){ 
  // força o slug_tipo_documento e tipo_documento
  $fws_['slug_tipo_documento'] = 'criterios_pontuacao';
  $fws_['tipo_documento'] = 'criterios_pontuacao';
  $naoMostrar = 1;
  ?>
  <?php if ($naoMostrar) {?>
  <form class="formUpload1" enctype="multipart/form-data" data-tipo="criterios_pontuacao">
    <input type="hidden" name="slug_tipo_documento" value="<?php echo $fws_['slug_tipo_documento']?>">
    <input type="hidden" name="tipo_documento" value="<?php echo $fws_['tipo_documento']?>">
    <input type="hidden" name="c" value="GerenciarUploadDocumento">
    <input type="hidden" name="m" value="salvarTrabalho">
    <input type="hidden" name="codInscrito" value="<?php echo $objInscrito->getCodInscrito() ?>">
    <?php 
      $codTrab = $objTrabalho->getCodTrabalho() ?: '';
      echo '<input type="hidden" name="codTrabalho" id="codTrabalho-'.$objInscrito->getCodInscrito().'" value="'.$codTrab.'">';
      ?>
      <!-- Renderiza o formulário/tabela dos critérios -->
    <?php
    // Evento 129 (edital 003/2026 - PES): usa anexo limpo sem HTML/DOCTYPE
    // e independe do cod_perfil (o 129 tem perfil proprio, nao 614).
    if (COD_EVENTO == 129) {
        require_once(dirname(__FILE__)."/../view/inscricao/inscricao_trabalhos_consulta_prorede2_anexo_2_129.php");
    } elseif ($objInscrito->getCodPerfil() == 614) {
        require_once(dirname(__FILE__)."/../view/inscricao/inscricao_trabalhos_consulta_prorede2_anexo_2.php");
    }
    ?>
    <?php if ($objInscrito->getCodPerfil() == 566) { ?>
    <?php require_once(dirname(__FILE__)."/../view/inscricao/inscricao_trabalhos_consulta_prorede_anexo_2.php"); ?>
    <?php } ?>
    <?php if ($objInscrito->getCodPerfil() == 565) { ?>
        <?php require_once(dirname(__FILE__)."/../view/inscricao/inscricao_trabalhos_consulta_prorede_anexo_3.php"); ?>
    <?php } ?>
    <?php if ($objInscrito->getCodPerfil() == 568) { ?>
        <?php require_once(dirname(__FILE__)."/../view/inscricao/inscricao_trabalhos_consulta_prorede_anexo_4.php"); ?>
    <?php } ?>
    <?php if ($objInscrito->getCodPerfil() == 569) { ?>
        <?php require_once(dirname(__FILE__)."/../view/inscricao/inscricao_trabalhos_consulta_prorede_anexo_5.php"); ?>
    <?php } ?>
    <?php if ($objInscrito->getCodPerfil() == 570) { ?>
        <?php require_once(dirname(__FILE__)."/../view/inscricao/inscricao_trabalhos_consulta_prorede_anexo_6.php"); ?>
    <?php } ?>


    <!-- O arquivo é gerado via JS; manter input apenas para compatibilidade (não required) -->
    <input type="file" name="arquivo" style="display:none">
    <?php if ($periodoEnvioArqEncerrado !== true) { ?>
        <?php $labelBtnGerar = (defined('COD_EVENTO') && COD_EVENTO == 129)
                ? '💾 Salvar minha pontuação calculada'
                : 'Gerar arquivo de autoavaliação (apenas conferência)'; ?>
        <button type="submit"><?php echo htmlspecialchars($labelBtnGerar); ?></button>
    <?php }else{ ?>
        <span style="color: red;"><b>O período de envio encerrado.</b></span>
    <?php } ?>
    <progress class="barra-progresso" value="0" max="100" style="width: 100%; display:none;"></progress>
    <div class="mensagem-upload"></div>
  </form>
  <?php } ?>

  <div class="historico-envios">
  <?php
    if ($objTrabalho->getVersao() != ''){
      echo '<span style="color: green;">';
      echo '<b>Pontuação enviada!</b> Última versão: ' . $objTrabalho->getVersao();
      $data = $objTrabalho->getDataAlteracao()? $objTrabalho->getDataAlteracao() : $objTrabalho->getDataInclusao();
      $data = formatDateView($data);
      echo '<br>Data do envio: ' . $data;
      echo '</span>';
    } else {
      echo '<span style="color: red;">Nenhuma pontuação salva   .</span>';
    } 
  ?>

  <script>
  // ======= Coleta a tabela de critérios (#tbl) e gera o JSON =======
  function coletarCriterios(selectorTbl){
    const tbl = document.querySelector(selectorTbl || '#tbl');
    const res = { itens:[], total:0 };
    if(!tbl) return res;

    tbl.querySelectorAll('tbody tr[data-id]').forEach(tr => {
      const id   = tr.dataset.id || '';
      const per  = parseFloat(tr.dataset.per || '0') || 0;
      const max  = parseFloat(tr.dataset.max || '0') || 0;
      const qEl  = tr.querySelector('input[type="number"]');
      const pEl  = tr.querySelector('input[readonly]');
      const qtd  = qEl ? parseFloat(qEl.value || '0') : 0;
      // aceita "12,34"
      const pts  = pEl ? (parseFloat(String(pEl.value||'0').replace('.','').replace(',','.'))||0) : (qtd*per);
      const desc = (tr.cells[0]?.innerText || '').trim();

      res.itens.push({ id, descricao: desc, pontos_por_item: per, max_pontos: max, quantidade: qtd, pontos_calculados: pts });
      res.total += pts;
    });
    return res;
  }

  function montarArquivoCriterios(form){
    const { itens, total } = coletarCriterios('#tbl');
    const payload = {
      tipo: 'criterios_pontuacao',
      slug_tipo_documento: form.find('input[name="slug_tipo_documento"]').val() || null,
      codInscrito: form.find('input[name="codInscrito"]').val() || null,
      codTrabalho: form.find('input[name="codTrabalho"]').val() || null,
      gerado_em: new Date().toISOString(),
      tabela: { total, itens }
    };
    const blob = new Blob([JSON.stringify(payload, null, 2)], { type: 'application/json' });
    return new File([blob], 'criterios_pontuacao.json', { type: 'application/json' });
  }
    /* INSERT 1: função utilitária (pode ficar antes do handler) */
    function montarPayloadCriterios() {
    const N =
        (typeof qtdCriterios !== 'undefined' && +qtdCriterios) ||
        document.querySelectorAll('[id^="criterio_"]').length ||
        0;

    const respostas = {};
    const pesos = {};
    const pontos = {};
    let total = 0;

    for (let i = 1; i <= N; i++) {
        const crit = document.getElementById('criterio_' + i);
        const pes  = document.getElementById('peso_' + i);
        const pts  = document.getElementById('pontos_' + i);

        respostas['criterio_' + i] = crit ? String(crit.value || '0') : '0';
        pesos['peso_' + i]         = pes  ? String(pes.value  || '0') : '0';

        const p = pts ? parseFloat(String(pts.value || '0').replace('.', '').replace(',', '.')) || 0 : 0;
        pontos['pontos_' + i] = String(p);
        total += p;
    }

    const totalEl = document.getElementById('pontos_total');
    if (totalEl && totalEl.value !== '') {
        const v = parseFloat(String(totalEl.value).replace('.', '').replace(',', '.'));
        if (!isNaN(v)) total = v;
    }
    return { respostas, pesos, pontos, total };
    }
  // ======= Validação dos documentos obrigatórios antes da calculadora =======
  // Varre todos os blocos .upload-documento[data-obrigatorio="true"] e devolve
  // os rótulos amigáveis dos que ainda não foram enviados (data-enviado="false").
  function obrigatoriosPendentes(){
    var pendentes = [];
    $('.upload-documento[data-obrigatorio="true"]').each(function(){
      var $b = $(this);
      if ($b.attr('data-enviado') !== 'true'){
        pendentes.push($b.attr('data-rotulo') || $b.attr('data-documento'));
      }
    });
    return pendentes;
  }

  // Reverifica no servidor o estado real de cada bloco (banco + arquivo no
  // disco) e atualiza data-enviado dos elementos. Cobre cenarios onde:
  //   - registro foi apagado em outra aba/sessao;
  //   - arquivo foi removido do disco mas registro ficou no banco.
  function reverificarBancoESincronizar(callbackOk, onFail){
    var slugs = [];
    $('.upload-documento[data-documento]').each(function(){
      var s = $(this).attr('data-documento');
      // bloco da propria calculadora nao tem documento real — ignora
      if (s && s !== 'criterios_pontuacao') slugs.push(s);
    });
    if (slugs.length === 0){ callbackOk(); return; }
    $.ajax({
      url: '<?php echo WB_URL_COMMAND ?>',
      type: 'POST',
      dataType: 'json',
      cache: false,
      timeout: 10000,
      data: {
        c: 'GerenciarUploadDocumento',
        m: 'verificarStatusDocumentos',
        slugs: JSON.stringify(slugs)
      },
      success: function(data){
        if (!data || data.ok != 1){
          if (typeof onFail === 'function') onFail(data && data.msg ? data.msg : 'Erro ao verificar status no servidor.');
          return;
        }
        $.each(data.documentos || {}, function(slug, info){
          var ok = !!(info && info.enviado && info.arquivo_existe);
          $('.upload-documento[data-documento="' + slug + '"]').attr('data-enviado', ok ? 'true' : 'false');
        });
        callbackOk();
      },
      error: function(){
        if (typeof onFail === 'function') onFail('Não foi possível verificar o status dos documentos. Tente novamente.');
      }
    });
  }

  function montarMsgPendentes(pendentes){
    var html = '<strong>⚠ Antes de abrir a calculadora, envie os documentos obrigatórios pendentes:</strong><ul style="margin:8px 0 0 20px;">';
    pendentes.forEach(function(rot){
      html += '<li>' + $('<div>').text(rot).html() + '</li>';
    });
    html += '</ul><div style="margin-top:6px;">Cada documento tem seu próprio botão <em>Enviar Arquivo</em>. Envie um de cada vez e <strong>aguarde a confirmação verde de “Documento enviado com sucesso!” antes de enviar o próximo</strong>.</div>';
    return html;
  }

  // ======= Reaproveita seu AJAX já existente =======
  $(function(){
    // Click no botão "Abrir calculadora": antes de qualquer coisa, verifica
    // se a sessao ainda esta valida. Depois valida obrigatorios. So entao
    // abre o collapse (evita preencher a planilha e descobrir no submit
    // que a sessao caiu, perdendo os dados digitados).
    $('.btn-abrir-calculadora').on('click', function(e){
      var $btn = $(this);
      var alvo = $btn.data('target-collapse');
      var $msg = $(alvo + '_obrigatorios_msg');

      var labelOriginalCalc = $btn.text();
      function travarBtn(){
        $btn.prop('disabled', true).text('Verificando sessão...');
      }
      function liberarBtn(){
        $btn.prop('disabled', false).text(labelOriginalCalc);
      }

      function continuarAposSessaoOk(){
        var pendentes = obrigatoriosPendentes();
        if (pendentes.length > 0){
          $msg.html(montarMsgPendentes(pendentes)).removeClass('hidden').show();
          $(alvo).collapse('hide');
          try { $msg[0].scrollIntoView({behavior:'smooth', block:'center'}); } catch(_){}
          return;
        }
        $msg.addClass('hidden').hide().html('');
        $(alvo).collapse('toggle');
      }

      function aposSincronizar(){
        liberarBtn();
        continuarAposSessaoOk();
      }

      function sincronizarBancoESeguir(){
        // Mantem botao travado durante a verificacao no servidor
        $btn.text('Verificando documentos...');
        reverificarBancoESincronizar(aposSincronizar, function(msgErro){
          liberarBtn();
          $msg.html('<strong>Não foi possível verificar o status dos documentos.</strong><br>' + (msgErro || '') + '<br>Tente novamente.').removeClass('hidden').show();
        });
      }

      if (typeof verificarSessaoAntesDeEnviar === 'function'){
        travarBtn();
        verificarSessaoAntesDeEnviar(function(){
          // sessao OK -> agora sincroniza estado real com banco/disco
          sincronizarBancoESeguir();
        }, function(){
          liberarBtn();
          $msg.html('<strong>Sessão expirada.</strong> Faça login novamente para usar a calculadora.').removeClass('hidden').show();
        });
      } else {
        // Fluxo fallback (telas sem helper de sessao): so revalida banco e segue
        travarBtn();
        sincronizarBancoESeguir();
      }
    });

    $('.formUpload1').on('submit', function(e){
      e.preventDefault();
      var form = $(this);
      var container = form.closest('.upload-documento');
      var formData = new FormData(form[0]);
      var progressBar = container.find('.barra-progresso');
      var mensagem = container.find('.mensagem-upload');
      var historico = container.find('.historico-envios');
      var btnSubmit = form.find('button[type=submit], input[type=submit]');

      // Revalida obrigatórios também no submit da calculadora.
      var pendentes = obrigatoriosPendentes();
      if (pendentes.length > 0){
        mensagem.html('<div class="alert alert-warning" style="margin:8px 0;">' + montarMsgPendentes(pendentes) + '</div>');
        try { mensagem[0].scrollIntoView({behavior:'smooth', block:'center'}); } catch(_){}
        return;
      }

      // Verifica sessao antes de enviar (evita "salvei e perdi tudo" quando expira).
      var labelOriginal = btnSubmit.first().text() || btnSubmit.first().val();
      btnSubmit.prop('disabled', true);
      btnSubmit.each(function(){
        if (this.tagName === 'BUTTON') this.textContent = 'Verificando sessão...';
        else this.value = 'Verificando sessão...';
      });
      mensagem.html('');

      function restaurarBotaoCalc(){
        btnSubmit.prop('disabled', false);
        btnSubmit.each(function(){
          if (this.tagName === 'BUTTON') this.textContent = labelOriginal;
          else this.value = labelOriginal;
        });
      }

      // Helper opcional definido na view do 129. Se nao existir (outras telas),
      // pula a verificacao e segue o fluxo antigo.
      if (typeof verificarSessaoAntesDeEnviar === 'function'){
        verificarSessaoAntesDeEnviar(function(){
          restaurarBotaoCalc();
          enviarCalculadora();
        }, function(){
          restaurarBotaoCalc();
          mensagem.html('<span style="color: red;"><b>Sessão expirada.</b> Faça login novamente para salvar a pontuação.</span>');
        });
      } else {
        restaurarBotaoCalc();
        enviarCalculadora();
      }

    function enviarCalculadora(){
    const c = montarPayloadCriterios();
    formData.set('criterios_respostas', JSON.stringify(c.respostas));
    formData.set('criterios_pesos',     JSON.stringify(c.pesos));
    formData.set('criterios_pontos',    JSON.stringify(c.pontos));
    formData.set('pontos_total',        String(c.total));

      progressBar.show().val(0);
      mensagem.html("");

      // Gera e injeta o arquivo JSON
      try {
        const jsonFile = montarArquivoCriterios(form);
        formData.set('arquivo', jsonFile, jsonFile.name);
        formData.set('tipo_documento', 'criterios_pontuacao');
      } catch (err) {
        mensagem.html('<span style="color: red;">Erro ao gerar JSON: '+(err?.message||err)+'</span>');
        progressBar.val(0);
        return;
      }

      $.ajax({
        xhr: function(){
          var xhr = new window.XMLHttpRequest();
          xhr.upload.addEventListener("progress", function(evt){
            if (evt.lengthComputable) {
              progressBar.val(Math.round((evt.loaded / evt.total) * 100));
            }
          }, false);
          return xhr;
        },
        url: '<?php echo WB_URL_COMMAND ?>',
        type: 'POST',
        dataType: 'json',
        data: formData,
        processData: false,
        contentType: false,
        success: function(data){
          progressBar.val(100);
          if(data.statusMessage==1){
            historico.html('<span style="color: green;">' + data.message + ' <br>Última versão: ' + data.versao + ' - Data: ' + data.data + '</span>');
            $('#codTrabalho-'+data.codInscrito).val(data.codTrabalho);
            var botaoAbrir = '<button type="button" class="btnLink" onclick="abrirArquivoGerado(\''+data.arquivoGerado+'\', \''+data.codEvento+'\')">📄 Abrir Arquivo Enviado</button>';
            $('.botaoAbrir[data-cod-area="' + data.slug_tipo_documento + '"]').html(botaoAbrir);
          }else{
            mensagem.html('<span style="color: red;">' + data.message + '</span>');
            progressBar.val(0);
          }
        },
        error: function(){
          mensagem.html('<span style="color: red;">Erro no envio, tente novamente ou entre em contato.</span>');
          progressBar.val(0);
        }
      });
    } // fim enviarCalculadora
    });
  });

  function abrirArquivoGerado(nomeArquivo, codEvento) {
    $('#arquivoParaAbrir').val(nomeArquivo);
    $('#codEventoParaAbrir').val(codEvento);
    $('#formAbrirArquivo').submit();
  }
  </script>
  <?php
}

    public function salvarTrabalho($fws_, $debug = false){
        try {
            // Bloqueio server-side: rejeita POST quando o periodo ja se encerrou
            // (mesma data usada na UI). Sem este check, um POST direto burla a tela.
            $objEvConfSv = EventoConfiguracao::getInstancePopulado(COD_EVENTO);
            if ($objEvConfSv->isPeriodoEnvioTrabalhoEncerrado()) {
                $dataFimSv = $objEvConfSv->getTrabSubmissaoDatFim();
                $msgFimSv = $dataFimSv ? formatDateView($dataFimSv) : '';
                throw new Exception('Período de envio encerrado' . ($msgFimSv ? ' em ' . $msgFimSv : '') . '. Não é possível anexar ou substituir documentos.');
            }

            $objI = new Inscrito();
            $objI->setCodInscrito($_SESSION['_cod_inscrito']);

            if(!$objI->inscritoExiste($debug)){
                throw new Exception('Código de inscrito não cadastrado.');
            }
            $obj = new Trabalho();
            /*
            if ($fws_['cod_trabalho']==''){
               throw new Exception("Código do trabalho não informado.");
            }*/
            //dd($fws_);
            $obj->setAll($fws_);
            $obj->setCodInscrito($_SESSION['_cod_inscrito']);
            // $obj->setSlugTipoDocumento($fws_['slug_tipo_documento']);
            // if($obj->getSlugTipoDocumento() == ''){
            //     throw new Exception("Área temática não informada.");
            // }
            // $capitulo = str_pad($fws_['capitulo'], 2, '0', STR_PAD_LEFT);
            // $obj->setTipoDocumento($fws_['tipo_documento']);            
            if($obj->getStatus()==''){
                $obj->setStatus('ATIVADA'); 
            }
            /**versão esta fixada em 1 pois não é para versionar nesse caso $obj->setVersaoArquivo(1); */
            if($fws_['codTrabalho']!=''){
                $obj->setCodTrabalho($fws_['codTrabalho']);
                //$obj->getByCapituloUltimo($debug);
                $obj->getByDocumentoUltimo($debug);
                // echo $obj->getVersao(); echo '<br>';
                // $novaVersao = $obj->getVersao()+1;
                // $obj->setVersao($novaVersao);
                $obj->setVersaoArquivo(1);
                // echo __LINE__.'<br>';
            }else{
                $obj->carregarCodNovoTrabalho();
                // $obj->setVersao($obj->getVersao()+1);
                $obj->setVersaoArquivo(1);
                $obj->setNovo('1');
                // echo __LINE__.'<br>';
            }
            // dd($obj);
            if($fws_['tipoDocumento']!='criterios_pontuacao'){
                /*verificar se arquivo foi submetido*/
                if($_FILES['arquivo']['name']==''){
                     throw new Exception("Arquivo não informado");
                }
                if ($_FILES['arquivo']['size'] > 1024*1024*1024*70){
                    throw new Exception("Não é permitido arquivos maiores que 30MB.");
                }
                if ($_FILES['arquivo']['name'] == '') {
                     throw new Exception("Arquivo não selecionado.");
                }
                if ($_FILES['arquivo']['tmp_name'] == '') {
                     throw new Exception("Arquivo temporário não encontrado.");
                }
                $extensao = validarTipoArquivoUpload(
                    $_FILES['arquivo']['tmp_name'],
                    $_FILES['arquivo']['name'],
                    array('doc','docx','pdf','gif','jpg','jpeg','jfif','png','json')
                );

                $fws_['arquivo_gerado']  = numberPad($obj->getCodInscrito(), 6). "_" . numberPad($obj->getCodTrabalho(), 6)  ."_" . $obj->getVersaoArquivo() . ".".$extensao;
                if (!file_exists($this->varDiretorio)){
                    mkdir($this->varDiretorio, 0700);
                    echo "Criou diretório";
                }
                $fws_['arquivo_destino'] = $this->varDiretorio.$fws_['arquivo_gerado'];
                if (!move_uploaded_file($_FILES['arquivo']['tmp_name'], $fws_['arquivo_destino'])) {
                     throw new Exception("Ocorreu um erro durante upload do arquivo.");
                }
                $obj->setArquivoUpload($_FILES['arquivo']['name']);
                $obj->setArquivoGerado($fws_['arquivo_gerado']);
            }
            $obj->salvarTarefa($debug);
        } catch (OKException $exc) {
            $ok = 1;
            $msg = $exc->getMessage();
            $_SESSION['MSG'] = $msg;
            $url = WB_URL_VIEW . "?content=view/inscricao/inscricao_congresso_ok.php";
            $data = $obj->getDataAlteracao()? $obj->getDataInclusao() : $obj->getDataInclusao();
            $data = formatDateView($data);
        } catch (Exception $exc) {
            $ok = 0;
            $msg = '';
            if($fws_['tipo_documento']!='criterios_pontuacao'){
                $msg = $exc->getMessage();
            }
            $_SESSION['MSG'] = $msg;
            $url = WB_URL_VIEW . "?content=view/inscricao/inscricao_congresso_ok.php";
            // Log do erro de upload para consulta pelo suporte.
            // Usa executarLog() do sistema, que grava em logn/eventos/geral/YYYYMMDD-upload_errors.txt
            // e ja anexa data/hora, IP e user-agent no formato padrao.
            $logMsg = sprintf(
                "evento=%s cod_inscrito=%s slug=%s tipo=%s arquivo=%s size=%s msg=\"%s\"",
                defined('COD_EVENTO') ? COD_EVENTO : '-',
                isset($_SESSION['_cod_inscrito']) ? $_SESSION['_cod_inscrito'] : '-',
                isset($fws_['slug_tipo_documento']) ? $fws_['slug_tipo_documento'] : '-',
                isset($fws_['tipo_documento']) ? $fws_['tipo_documento'] : '-',
                isset($_FILES['arquivo']['name']) ? $_FILES['arquivo']['name'] : '-',
                isset($_FILES['arquivo']['size']) ? $_FILES['arquivo']['size'] : '-',
                str_replace(array("\r","\n","\""), array(' ',' ','\''), $exc->getMessage())
            );
            executarLog($logMsg, 'upload_errors', true);
        }
        $data = $obj->getDataAlteracao()? $obj->getDataAlteracao() : $obj->getDataInclusao();
        echo '{
            "statusMessage":"' . $ok . '",
            "message":      "' . $msg . '",
            "versao":       "' . $obj->getVersao() . '",
            "data":"'. formatDateView($data) . '",
            "codInscrito":"' . $obj->getCodInscrito() . '",
            "codTrabalho":"' . $obj->getCodTrabalho() . '",
            "slug_tipo_documento":"' . $obj->getSlugTipoDocumento() . '",
            "tipoDocumento":"' . $obj->getTipoDocumento() . '",
            "codEvento":"' . $obj->getCodEvento() . '",
            "arquivoGerado":"' . $obj->getArquivoGerado() . '"
            }';
    }
    public function abrirDocumentoTela($fws_, $debug = false) {
        if ($_SESSION['_cod_usuario'] or $_SESSION['_cod_inscrito']){
            $id=$fws_['cod'];
            $filepath = $this->varDiretorio;
            if($fws_['codEvento']){
                // echo $fws_['codEvento'];
                // echo __LINE__.'<br>';   
                $codEvento = str_pad($fws_['codEvento'],'3','0',STR_PAD_LEFT);
                $filepath = WB_PATH_LOG_TRABALHO.'../../'.$codEvento.'/trabalhos/';
                // echo $filepath.'<br>';  
                // echo $id;
                // exit();
            }
            $arquivo = $id;
            if (file_exists($filepath.$arquivo)) {
                /* INSERT 2) Dentro de abrirDocumentoTela(), após montar $filepath e $arquivo, ANTES de enviar headers do arquivo */
                $ext = strtolower(pathinfo($arquivo, PATHINFO_EXTENSION));
                if ($ext === 'json') {
                    $outName = preg_replace('/\.json$/i', '.pdf', $arquivo);
                    $this->gerarPdfCriteriosFromJson($filepath.$arquivo, $outName, $debug);
                    return; // garante que não cai no fluxo de download padrão
                }

                /* … mantém seu fluxo atual para outras extensões … */
                header("Content-Description: File Transfer");
                header("Cache-Control: private"); // work-around for IE
                header("Content-Type: application/msword");
                header("Content-Disposition: attachment; filename=\"".$arquivo."\"");
                header("Content-Length: " . @filesize($filepath.$arquivo));
                header("Pragma: public");
                readfile($filepath.$arquivo) or die();
            } else {
                echo "<script language=\"Javascript\"> alert('Arquivo não existe!'); window.close();</script>";
            }
        }else{
            echo "<script language=\"Javascript\"> alert('Acesso negado!'); window.close();</script>";
        }
    }

    /* INSERT 1) Coloque este método na mesma classe */
    public function gerarPdfCriteriosFromJson(string $jsonFullPath, string $outputName = 'criterios_pontuacao.pdf', bool $debug = false): void
    {
        if (!is_file($jsonFullPath)) {
            throw new Exception('Arquivo JSON não encontrado.');
        }

        $raw  = file_get_contents($jsonFullPath);
        $data = json_decode($raw, true) ?: [];

        $tabela = $data['tabela'] ?? ['itens' => [], 'total' => 0];
        $itens  = is_array($tabela['itens'] ?? null) ? $tabela['itens'] : [];
        $total  = (float)($tabela['total'] ?? 0);

        // HTML simples em tabela
        $html = '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="utf-8">
        <style>
        body{font-family:Arial,Helvetica,sans-serif;color:#222;margin:16px;}
        h2{margin:0 0 6px;}
        table{width:100%;border-collapse:collapse;table-layout:fixed;}
        th,td{border:1px solid #ddd;padding:8px;vertical-align:top;font-size:12px;}
        th{background:#f5f5f5;text-align:center;}
        tfoot td{font-weight:700;}
        .muted{color:#666;font-size:11px;}
        td.c{text-align:center}
        </style></head><body>';

        $html .= '<h2>Critérios de Pontuação</h2>';
        $html .= '<div class="muted">Gerado em: '.htmlspecialchars($data['gerado_em'] ?? '').'</div>';

        $html .= '<table><thead><tr>
        <th style="width:8%">Item</th>
        <th style="width:42%">Descrição</th>
        <th style="width:10%">Pts/item</th>
        <th style="width:12%">Máx. pontos</th>
        <th style="width:12%">Quantidade</th>
        <th style="width:16%">Pontos</th>
        </tr></thead><tbody>';

        foreach ($itens as $row) {
            $html .= '<tr>'.
            '<td class="c">'.htmlspecialchars($row['id'] ?? '').'</td>'.
            '<td>'.htmlspecialchars($row['descricao'] ?? '').'</td>'.
            '<td class="c">'.number_format((float)($row['pontos_por_item'] ?? 0), 2, ',', '.').'</td>'.
            '<td class="c">'.number_format((float)($row['max_pontos'] ?? 0), 2, ',', '.').'</td>'.
            '<td class="c">'.number_format((float)($row['quantidade'] ?? 0), 2, ',', '.').'</td>'.
            '<td class="c">'.number_format((float)($row['pontos_calculados'] ?? 0), 2, ',', '.').'</td>'.
            '</tr>';
        }

        $html .= '</tbody><tfoot><tr>
        <td colspan="5" style="text-align:right;">TOTAL</td>
        <td class="c">'.number_format((float)$total, 2, ',', '.').'</td>
        </tr></tfoot></table></body></html>';

        // Gera PDF com mPDF (usa seu autoload já existente)
        require_once dirname(__FILE__) . '/../lib/vendor/autoload.php';
        $mpdf = new \Mpdf\Mpdf(['format'=>'A4','orientation'=>'P']);
        $mpdf->WriteHTML($html);
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="'.$outputName.'"');
        echo $mpdf->Output($outputName, \Mpdf\Output\Destination::STRING_RETURN);
        exit;
    }

    /**
     * Endpoint AJAX: dado um array de slugs em fws_['slugs'] (JSON), consulta
     * o banco (status='ATIVADA' por slug, ultima versao) e ainda checa
     * file_exists() do arquivo gerado no disco. Usado para sincronizar o
     * estado real dos blocos antes de abrir a calculadora — pega casos de
     * arquivo apagado ou alteracoes feitas em outra aba/sessao.
     *
     * Retorno JSON:
     *   { ok: 1, documentos: { <slug>: {enviado, arquivo_existe, versao, arquivo_gerado} } }
     */
    public function verificarStatusDocumentos($fws_, $debug = false) {
        header('Content-Type: application/json; charset=UTF-8');
        try {
            $codInscrito = isset($_SESSION['_cod_inscrito']) ? (int) $_SESSION['_cod_inscrito'] : 0;
            if ($codInscrito <= 0) {
                echo json_encode(['ok' => 0, 'msg' => 'Sessão inválida.']);
                return;
            }

            $slugsRaw = isset($fws_['slugs']) ? $fws_['slugs'] : '';
            $slugs = is_string($slugsRaw) ? json_decode($slugsRaw, true) : (is_array($slugsRaw) ? $slugsRaw : []);
            if (!is_array($slugs)) $slugs = [];

            $diretorio = defined('WB_PATH_LOG_TRABALHO') ? WB_PATH_LOG_TRABALHO : '';
            $resultado = [];

            $objEvConfSt = EventoConfiguracao::getInstancePopulado(COD_EVENTO);
            $periodoEncerradoSt = $objEvConfSt->isPeriodoEnvioTrabalhoEncerrado();
            $dataFimSt = $objEvConfSt->getTrabSubmissaoDatFim();

            foreach ($slugs as $slug) {
                $slug = preg_replace('/[^a-zA-Z0-9_\-]/', '', (string)$slug);
                if ($slug === '') continue;

                $obj = new Trabalho();
                $obj->setCodInscrito($codInscrito);
                $obj->setCodEvento(COD_EVENTO);
                $obj->setSlugTipoDocumento($slug);
                $obj->setStatus('ATIVADA');
                $obj->getByDocumentoUltimo($debug);

                $temVersao = ($obj->getVersao() != '');
                $arquivoGerado = $obj->getArquivoGerado();
                $arquivoExiste = false;
                if ($temVersao && $arquivoGerado && $diretorio) {
                    $arquivoExiste = is_file($diretorio . $arquivoGerado);
                }
                $resultado[$slug] = [
                    'enviado'        => $temVersao,
                    'arquivo_existe' => $arquivoExiste,
                    'versao'         => $obj->getVersao(),
                    'arquivo_gerado' => $arquivoGerado,
                ];
            }

            echo json_encode([
                'ok' => 1,
                'documentos' => $resultado,
                'periodo_encerrado' => $periodoEncerradoSt,
                'data_fim' => $dataFimSt,
            ]);
        } catch (Exception $e) {
            echo json_encode(['ok' => 0, 'msg' => 'Erro ao verificar status: ' . $e->getMessage()]);
        }
    }

}

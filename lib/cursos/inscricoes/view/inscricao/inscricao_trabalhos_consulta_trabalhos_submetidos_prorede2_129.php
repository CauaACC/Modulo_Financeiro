<?php
require_once(dirname(__FILE__)."/../../include/config.php");
#require_once(dirname(__FILE__)."/../../include/inscricao_request_variaveis.php");

if ($objPerfil->getVerificarEnvioTrabalho()==1){
?>
 <style>
    .item-documento {
      border-left: 5px solid #28a745;
    }
    .item-liberado {
      /* border-left: 5px solid #28a745;       */
      margin: 10px 0;
      padding: 10px;
      background: #f5fff5;
    }
    .item-bloco {
      border-left: 5px solid #6c757d;
    }
    .item-bloqueado {
      border-left: 5px solid #6c757d;
      opacity: 0.7;
      margin: 10px 0;
      padding: 10px;
      background: #f8f9fa;
    }
    .list-group-item strong:first-child {
      color: #000000;
      font-size: 1.1em;
    }
    .btn-video {
      display: inline-block;
      margin-top: 10px;
      background: #3a5a7d;
      color: #fff;
      padding: 6px 12px;
      border-radius: 10px;
      text-decoration: none;
      font-size: 14px;
      cursor: pointer;

    }

    /* Modal */
    .modal {
      display: none;
      position: fixed;
      z-index: 9999;
      top: 0; left: 0;
      width: 100vw;
      height: 100vh;
      background: rgba(0,0,0,0.8);
      justify-content: center;
      align-items: center;
    }
    .modal-content {
      background: #000;
      position: relative;
      width: 90%;
      max-width: 960px;
      aspect-ratio: 16 / 9;
    }
    .modal iframe {
      width: 100%;
      height: 100%;
      border: none;
    }
    .close-modal {
      position: absolute;
      top: 10px;
      right: 15px;
      font-size: 20px;
      background: #ff3333;
      color: #fff;
      border: none;
      padding: 5px 10px;
      border-radius: 5px;
      cursor: pointer;
    }
    .btnLink {
        all: unset;
        cursor: pointer;
        color: green;
    }
    /* Badge de sessao - pill destacado abaixo do navbar */
    #sessaoBadge {
      position: fixed; top: 65px; right: 15px;
      z-index: 9998;
      padding: 10px 18px;
      border-radius: 24px;
      font-size: 14px; font-weight: 700;
      background: #28a745; color: #fff;
      box-shadow: 0 3px 12px rgba(0,0,0,0.3);
      transition: background 0.3s, font-size 0.2s;
      letter-spacing: 0.3px;
      border: 2px solid rgba(255,255,255,0.4);
    }
    #sessaoBadge.warn {
      background: #f0ad4e;
      font-size: 15px;
      padding: 12px 20px;
    }
    #sessaoBadge.danger {
      background: #d9534f;
      font-size: 16px;
      padding: 14px 22px;
      animation: sessaoPulse 1s infinite;
    }
    @keyframes sessaoPulse { 50% { opacity: 0.55; transform: scale(1.03); } }

    /* Modal de sessao expirada */
    #sessaoExpirouModal {
      display: none;
      position: fixed; top:0; left:0; width:100vw; height:100vh;
      background: rgba(0,0,0,0.75);
      z-index: 9999;
      justify-content: center; align-items: center;
    }
    #sessaoExpirouModal.ativo { display: flex; }
    #sessaoExpirouModal .caixa {
      background: #fff; padding: 30px 40px;
      border-radius: 10px; max-width: 500px;
      text-align: center; box-shadow: 0 5px 20px rgba(0,0,0,0.3);
    }
    #sessaoExpirouModal h3 { color: #d9534f; margin-top: 0; }
    #sessaoExpirouModal .btn-refazer {
      background: #0073aa; color: #fff;
      padding: 12px 24px; border: none;
      border-radius: 5px; font-size: 16px;
      cursor: pointer; margin-top: 15px;
    }
    #sessaoExpirouModal .btn-refazer:hover { background: #005a87; }
  </style>
<script>
// -----------------------------------------------------------------
// Controle de sessao: contador cliente + ping keep-alive + modal
//
// Problema original: candidato fica muito tempo na tela, sessao PHP
// expira, upload falha silenciosamente.
//
// Estrategia:
//  1. Ping a cada 30s mantem a sessao PHP viva em background.
//  2. Cliente tem um limite SEPARADO de tempo na tela (1h).
//     Quando zera, forca logout no servidor e bloqueia a tela.
//  3. Badge mostra o tempo do limite cliente (contador regressivo).
// -----------------------------------------------------------------
var PING_URL         = '<?php echo WB_URL_COMMAND ?>&c=PingSessao&m=verificar';
var LOGOUT_URL       = '<?php echo WB_URL_COMMAND ?>&c=PingSessao&m=logoutForcado';
var PING_INTERVALO_MS = 30000;   // ping a cada 30s -> keep-alive
var LIMITE_TELA_MS    = 60 * 60 * 1000; // 1h - tempo max na tela
var LIMITE_AMARELO_MS = 10 * 60 * 1000; // ultimos 10min: amarelo
var LIMITE_VERMELHO_MS = 5 * 60 * 1000; // ultimos 5min: vermelho pulsante

var tempoInicioTela = Date.now();
var sessaoExpirouFlag = false;
var pingIntervalHandle = null;
var tickIntervalHandle = null;

function fmtMMSS(ms){
  var s = Math.max(0, Math.floor(ms / 1000));
  var m = Math.floor(s / 60);
  var r = s % 60;
  return (m<10?'0':'') + m + ':' + (r<10?'0':'') + r;
}

function atualizarBadge(){
  var $b = $('#sessaoBadge');
  if(!$b.length) return;
  var restanteMs = LIMITE_TELA_MS - (Date.now() - tempoInicioTela);

  if(restanteMs <= 0){
    $b.removeClass('warn').addClass('danger').text('Sessão expirada');
    return;
  }
  if(restanteMs <= LIMITE_VERMELHO_MS){
    $b.removeClass('warn').addClass('danger')
      .text('⚠ Sessão expira em ' + fmtMMSS(restanteMs));
  }else if(restanteMs <= LIMITE_AMARELO_MS){
    $b.removeClass('danger').addClass('warn')
      .text('⚠ Sessão expira em ' + fmtMMSS(restanteMs));
  }else{
    $b.removeClass('warn danger')
      .text('Sessão ativa — ' + fmtMMSS(restanteMs));
  }
}

function encerrarSessaoCliente(){
  if(sessaoExpirouFlag) return;
  sessaoExpirouFlag = true;
  if(pingIntervalHandle){ clearInterval(pingIntervalHandle); pingIntervalHandle=null; }
  if(tickIntervalHandle){ clearInterval(tickIntervalHandle); tickIntervalHandle=null; }
  // forca logout no servidor para nao deixar sessao zombie
  $.ajax({ url: LOGOUT_URL, type:'POST', cache:false, timeout:8000 });
  mostrarModalSessaoExpirada();
}

function mostrarModalSessaoExpirada(){
  sessaoExpirouFlag = true;
  $('#sessaoExpirouModal').addClass('ativo');
}

// Verifica a sessao no servidor ANTES de qualquer envio (upload de doc ou
// salvar calculo). Evita o caso "enviei e sumiu" quando a sessao cai entre
// dois pings de keep-alive.
function verificarSessaoAntesDeEnviar(callbackOk, onFail){
  $.ajax({
    url: PING_URL,
    type: 'GET',
    dataType: 'json',
    cache: false,
    timeout: 8000,
    success: function(data){
      if (data && data.logado){
        callbackOk();
      } else {
        mostrarModalSessaoExpirada();
        if (typeof onFail === 'function') onFail('expirada');
      }
    },
    error: function(){
      // Sem resposta = considera caido por seguranca.
      mostrarModalSessaoExpirada();
      if (typeof onFail === 'function') onFail('sem-resposta');
    }
  });
}

function pingarSessao(){
  // se ja passou do limite cliente, nem pinga mais
  if(Date.now() - tempoInicioTela >= LIMITE_TELA_MS){
    encerrarSessaoCliente();
    return;
  }
  $.ajax({
    url: PING_URL,
    type: 'GET',
    dataType: 'json',
    cache: false,
    timeout: 10000,
    success: function(data){
      if(!data.logado){
        // servidor ja considera expirada (bem improvavel com keep-alive)
        mostrarModalSessaoExpirada();
      }
    },
    error: function(xhr){
      if(xhr.status === 401 || xhr.status === 0){
        mostrarModalSessaoExpirada();
      }
    }
  });
}

function tickContador(){
  atualizarBadge();
  if(Date.now() - tempoInicioTela >= LIMITE_TELA_MS){
    encerrarSessaoCliente();
  }
}

$(function(){
  // inicia ping, badge e contador
  atualizarBadge();
  pingarSessao();
  pingIntervalHandle = setInterval(pingarSessao, PING_INTERVALO_MS);
  tickIntervalHandle = setInterval(tickContador, 1000);

  // ---- upload de documentos ----
  $('.formUpload').on('submit', function(e){
    e.preventDefault();

    var form = $(this);
    var container = form.closest('.upload-documento');
    var formData = new FormData(form[0]);
    var progressBar = container.find('.barra-progresso');
    var mensagem = container.find('.mensagem-upload');
    var historico = container.find('.historico-envios');
    var btnSubmit = form.find('button[type=submit], input[type=submit]');

    // Trava o botao enquanto verifica a sessao.
    var labelOriginal = btnSubmit.first().text() || btnSubmit.first().val();
    btnSubmit.prop('disabled', true);
    btnSubmit.each(function(){
      if (this.tagName === 'BUTTON') this.textContent = 'Verificando sessão...';
      else this.value = 'Verificando sessão...';
    });
    mensagem.html('');

    function restaurarBotao(){
      btnSubmit.prop('disabled', false);
      btnSubmit.each(function(){
        if (this.tagName === 'BUTTON') this.textContent = labelOriginal;
        else this.value = labelOriginal;
      });
    }

    verificarSessaoAntesDeEnviar(function(){
      restaurarBotao();
      enviarUploadDocumento();
    }, function(){
      restaurarBotao();
      mensagem.html('<span style="color: red;"><b>Sessão expirada.</b> Faça login novamente para enviar o arquivo.</span>');
    });

    function enviarUploadDocumento(){
    progressBar.show().val(0);
    mensagem.html("");

    $.ajax({
      xhr: function(){
        var xhr = new window.XMLHttpRequest();
        xhr.upload.addEventListener("progress", function(evt){
          if (evt.lengthComputable) {
            var percent = Math.round((evt.loaded / evt.total) * 100);
            progressBar.val(percent);
          }
        }, false);
        return xhr;
      },
      url: '<?php echo WB_URL_COMMAND ?>', // ou "index.php"
      type: 'POST',
      dataType: 'json',
      data: formData,
      processData: false,
      contentType: false,
      success: function(data){
        progressBar.val(100);
        if(data && data.statusMessage==1){
          historico.html('<span style="color: green;"><b>Documento enviado com sucesso!</b>' + data.message + ' <br>Última versão: ' + data.versao + ' - Data: ' + data.data + '</span>');

          $('#codTrabalho-'+data.codInscrito).val(data.codTrabalho);
          var botaoAbrir = '<button type="button" class="btnLink" onclick="abrirArquivoGerado(\''+data.arquivoGerado+'\')">📄 Para conferir documento enviado, <b>Clique aqui para visualizar documento</b></button>';
          $('.botaoAbrir[data-cod-area="' + data.slug_tipo_documento + '"]').html(botaoAbrir);

          // Marca o bloco como enviado para a validação dos obrigatórios reconhecer.
          $('.upload-documento[data-documento="' + data.slug_tipo_documento + '"]').attr('data-enviado', 'true');

        }else{
          mensagem.html('<span style="color: red;">' + (data && data.message ? data.message : 'Erro desconhecido') + '</span>');
          progressBar.val(0);
        }
      },
      error: function(xhr, textStatus){
        progressBar.val(0);
        // Qualquer erro: revalida a sessao via ping antes de decidir a mensagem.
        // Sessao caida pode se manifestar como 401, parsererror, 500, timeout etc,
        // entao nao da pra depender do status/textStatus — sempre pergunta ao servidor.
        $.ajax({
          url: PING_URL, type:'GET', dataType:'json', cache:false, timeout:8000,
          success: function(data){
            if(!data.logado){
              mostrarModalSessaoExpirada();
              mensagem.html('<span style="color: red;"><b>Sessão expirada.</b> O envio NÃO foi concluído. Faça login novamente.</span>');
            }else{
              mensagem.html('<span style="color: red;">Erro no envio. Tente novamente ou entre em contato.</span>');
            }
          },
          error: function(){
            mostrarModalSessaoExpirada();
            mensagem.html('<span style="color: red;"><b>Sessão expirada.</b> O envio NÃO foi concluído. Faça login novamente.</span>');
          }
        });
      }
    });
    } // fim de enviarUploadDocumento
  });
});
  function abrirArquivoGerado(nomeArquivo, codEvento) {
    $('#arquivoParaAbrir').val(nomeArquivo);
    $('#codEventoParaAbrir').val(codEvento);
    $('#formAbrirArquivo').submit();
  }  
</script>  
</head>
<body>
<!-- Badge de sessao (atualizado via JS) -->
<div id="sessaoBadge">Sessão ativa</div>

<!-- Modal de sessao expirada -->
<div id="sessaoExpirouModal">
  <div class="caixa">
    <h3>⚠ Sua sessão expirou</h3>
    <p>Por segurança, sua sessão foi encerrada após 1 hora.</p>
    <p>Os dados cadastrais e os documentos já enviados <strong>continuam salvos</strong>. Para continuar o envio de documentos, faça login novamente.</p>
    <a href="<?php echo WB_URL_VIEW ?>?content=view/inscricao/inscricao_01.php" class="btn-refazer" style="display:inline-block;text-decoration:none;">
      Refazer login e continuar
    </a>
  </div>
</div>

<form id="formAbrirArquivo" method="POST" action="<?php echo WB_URL_COMMAND ?>" target="_blank">
  <input type="hidden" name="c" value="GerenciarUploadDocumento">
  <input type="hidden" name="m" value="abrirDocumentoTela">
  <input type="hidden" name="cod" id="arquivoParaAbrir">
  <input type="hidden" name="codEvento" id="codEventoParaAbrir">
</form>

<div class="list-group">
    <?php
    echo "<div class='text-danger' style='font-weight:700;margin:6px 0 4px;'>Atenção</div>";
echo "<div style='font-family: Arial, Helvetica, sans-serif; font-size: 14px; line-height: 1.55; color: #222;'>
• A senha de acesso foi enviada para o e-mail informado no cadastro.
<br>• Se a sessão expirar, basta fazer login novamente — seus dados e arquivos já enviados continuam salvos.
<br>• Não é permitido enviar documentos ou fazer correções após o último dia de inscrição.
<br><br>

<strong>Antes de iniciar</strong>
<br>• Prepare todos os arquivos em seu computador, em <b>PDF</b>, com nomes claros (ex.: <i>CPF_NomeCompleto.pdf</i>).
<br>• Separe os documentos que serão enviados para facilitar o envio.
<br><br>

<strong>Passo a passo para enviar cada documento</strong>
<br>1) Clique em <em>Procurar…</em> e selecione o arquivo.
<br>2) Confira se o nome do arquivo apareceu ao lado.
<br>3) Clique em <em>Enviar Arquivo</em>.
<br>4) Aguarde a mensagem de sucesso. Se após clicar em <em>Enviar Arquivo</em> continuar aparecendo <em>Nenhum arquivo enviado</em>, o envio <u>não</u> foi concluído.
<br>5) Após o sucesso, clique em <em>Abrir/Visualizar arquivo enviado</em> para conferir.
<br>6) Repita o procedimento para os demais documentos.
<br><br>

<strong>Conferência</strong>
<br>• Sempre visualize os documentos enviados para certificar-se de que o envio foi feito corretamente.
</div><br>";


$objEvConfView = EventoConfiguracao::getInstancePopulado(COD_EVENTO);
$dataFimEnvio  = $objEvConfView->getTrabSubmissaoDatFim();
$periodoEnvioEncerradoView = $objEvConfView->isPeriodoEnvioTrabalhoEncerrado();
$dataFimFmt    = $dataFimEnvio ? formatDateView($dataFimEnvio) : '';

if ($periodoEnvioEncerradoView) {
    echo '<div style="padding:14px 16px;border:2px solid #b30000;background:#fff3f3;color:#b30000;border-radius:6px;margin:10px 0;font-size:15px;">
      <strong>⛔ Período de envio encerrado' . ($dataFimFmt ? ' em ' . htmlspecialchars($dataFimFmt) : '') . '.</strong>
      <br>Não é mais possível anexar, substituir nem visualizar documentos. A lista abaixo mostra apenas o status do que você enviou enquanto o prazo estava aberto.
    </div>';
} else {
    echo '<div style="padding:12px 14px;border:1px solid #f0ad4e;background:#fff8e5;border-radius:6px;color:#333;margin:10px 0;">
      <strong>📌 Período de envio de documentos: até ' . htmlspecialchars($dataFimFmt ?: '— prazo não configurado') . '.</strong>
      <span>Após essa data não será possível anexar ou substituir documentos.</span>
    </div>';
}


echo "<fieldset><legend>1 - Documentos Obrigatórios (Eliminatórios)</legend>";
echo "<div style='color:#b30000;font-weight:700;background:#fff3f3;border:1px solid #b30000;border-radius:4px;padding:10px 12px;margin:6px 0 10px;'>⚠ <strong>Atenção:</strong> A <strong>não anexação</strong> de qualquer documento exigido nesses campos, dentro do prazo previsto, <strong>implicará no indeferimento da inscrição</strong>.</div>";

echo GerenciarUploadDocumentoAC::gerarBlocoUpload($objInscrito, 'Documento oficial com foto (RG/CNH/Passaporte etc.) ', 'documento_com_foto',$obrigatorio=true);
echo GerenciarUploadDocumentoAC::gerarBlocoUpload($objInscrito, 'CPF', 'cpf',$obrigatorio=true);
echo GerenciarUploadDocumentoAC::gerarBlocoUpload($objInscrito, 'Título Eleitoral', 'titulo_eleitor',$obrigatorio=true);

echo "<br>Comprovação de qualificação mínima - (Edital 003/2026 – item 9.2, inciso VII, alíneas a/b/c): <br>a) Diploma de Ensino Médio (preferencialmente com magistério) OU <br>b) Comprovante de matrícula (graduandos em Pedagogia, Educação Física, Enfermagem, Fisioterapia, Fonoaudiologia, Nutrição, Psicologia, Terapia Ocupacional, Técnico de Enfermagem) OU <br>c) Diploma de Graduação em Licenciatura (com ou sem Pós-Graduação em Educação Especial).";
echo GerenciarUploadDocumentoAC::gerarBlocoUpload($objInscrito,'Comprovação de qualificação mínima','comprovacao_qualificacao',$obrigatorio=true);

echo GerenciarUploadDocumentoAC::gerarBlocoUpload($objInscrito, 'Comprovante de endereço atualizado (no nome do candidato)', 'comprovante_endereco',$obrigatorio=true);
echo GerenciarUploadDocumentoAC::gerarBlocoUpload($objInscrito, 'Declaração do titular do endereço com firma reconhecida (obrigatório se o comprovante de endereço estiver em nome de outra pessoa)', 'declaracao_titular_endereco', $obrigatorio=false);

echo "</fieldset>";

echo "<br><fieldset><legend>2 - Currículo e Pontuação (Anexo II)</legend>";
echo "Para comprovar sua pontuação, crie <strong>um único arquivo em PDF</strong> contendo seu <strong>currículo</strong> e <strong>todos os comprovantes</strong> (certificados, títulos, declarações etc.) referentes aos itens do <strong>Anexo II</strong> do Edital, <strong>na mesma ordem do Anexo II</strong>.
<br><br>
A <strong>pontuação final</strong> será calculada pela <strong>Comissão Avaliadora</strong>, com base exclusivamente nos documentos enviados neste arquivo.
<br><br>";
echo GerenciarUploadDocumentoAC::gerarBlocoUpload($objInscrito, 'Currículo + Comprovantes de Pontuação (PDF)', 'curriculo_comprovantes',$obrigatorio=false);

// CTPS: só se for comprovar tempo de atividade (pontuação)
echo GerenciarUploadDocumentoAC::gerarBlocoUpload(
    $objInscrito,
    'CTPS ou Comprovação de tempo de atividade profissional (se for utilizar para pontuação)',
    'ctps_tempo_atividade', $obrigatorio=false);
echo "</fieldset>";

echo "<br><fieldset><legend>3 - Cálculo de Pontuação (simulação)</legend>";
echo "<div style='background:#fff8e5;border:1px solid #f0ad4e;padding:12px 14px;margin:8px 0 14px;font-size:14px;line-height:1.6;color:#333;border-radius:4px;'>";
echo "<div style='font-weight:700;color:#8a5a00;margin-bottom:6px;'>⚠ Esta calculadora <u>não vale como nota</u>.</div>";
echo "Ela apenas <strong>soma os pontos que você mesmo marcar</strong> nos critérios do Anexo II — é só uma estimativa pessoal.";
echo "<br>Não há nenhuma verificação automática: a calculadora <strong>não confere</strong> se você tem os comprovantes nem se eles são válidos.";
echo "<br><br>";
echo "<strong>Quem define sua pontuação oficial é a Comissão Avaliadora</strong>, analisando apenas o arquivo que você enviou no item 2 (<em>Currículo + Comprovantes em PDF</em>).";
echo "</div>";
echo GerenciarUploadDocumentoAC::gerarBlocoCriterios($objInscrito, 'criterios_pontuacao', 'criterios_pontuacao');
echo "</fieldset>";


?>
</div>

    <form name="getdocumento" method="post" action="<?php echo WB_URL_COMMAND?>" target="_blank">
        <input type="hidden" name="c" value="GerenciarUpload">
        <input type="hidden" name="m" value="abrirDocumento">
        <input type="hidden" value="" name="cod">
    </form>
    <form name="getavulso" method="post" action="<?php echo WB_URL_COMMAND?>" target="_blank">
        <input type="hidden" name="c" value="GerenciarUpload">
        <input type="hidden" name="m" value="abrirAvulso">
        <input type="hidden" value="" name="cod">
    </form>
    <form name="getcontrato" method="post" action="<?php echo WB_URL_COMMAND?>" target="_blank">
        <input type="hidden" name="c" value="GerenciarUpload">
        <input type="hidden" name="m" value="abrirContrato">
        <input type="hidden" value="" name="cod">
    </form>
    <form name="getvideo" method="post" action="<?php echo WB_URL_COMMAND?>" target="_blank">
        <input type="hidden" name="c" value="GerenciarUpload">
        <input type="hidden" name="m" value="abrirVideo">
        <input type="hidden" value="" name="cod">
    </form>
    <form name="getdoc" method="post" action="<?php echo WB_URL_COMMAND?>" target="_blank">
        <input type="hidden" name="c" value="GerenciarUpload">
        <input type="hidden" name="m" value="abrirWord">
        <input type="hidden" value="" name="cod">
    </form>
    <SCRIPT LANGUAGE="JavaScript1.2" src="resources/js/avaliacao-trabalhos.js"></SCRIPT>
    <script language="JavaScript" type="text/javascript">
        function visualiza(cod){
            document.getdoc.cod.value=cod;
            document.getdoc.submit();
        }
        function visualizarDocumento(cod){
            document.getdocumento.cod.value=cod;
            document.getdocumento.submit();
        }
        function visualizarAvulso(cod){
            document.getavulso.cod.value=cod;
            document.getavulso.submit();
        }
        function visualizarContrato(cod){
            document.getcontrato.cod.value=cod;
            document.getcontrato.submit();
        }
        function visualizarVideo(cod){
            document.getvideo.cod.value=cod;
            document.getvideo.submit();
        }
        function ocultarResumo(cod_resumo){
            loading('#resumo-'+cod_resumo,'hidden')
            loading('#btnVisualizarResumo-'+cod_resumo,'show')
            loading('#btnOcultarResumo-'+cod_resumo,'hidden')
        }
        function visualizarResumo(cod_resumo){
            loading('#resumo-'+cod_resumo,'show')
            loading('#btnVisualizarResumo-'+cod_resumo,'hidden')
            loading('#btnOcultarResumo-'+cod_resumo,'show')
        }
        function criticarAnexoApresentacao(cod_trabalho){
            if($('#arquivo-'+cod_trabalho).val()==''){
                alert('É necessário anexar arquivo da apresentação.');
                return false;
            }
        }
        function criticarAnexoVideo(cod_trabalho){
            if($('#arquivo-video-'+cod_trabalho).val()==''){
                alert('É necessário anexar arquivo de video.');
                return false;
            }
        }
    </script>
<?php }?>


<?php
/**
 * Documentos Complementares (Contratação)
 *
 * Exibe área de upload de documentos obrigatórios para contratação.
 * Controle de exibição: ocultar para quem já está contratado/participando.
 *
 * Variáveis esperadas: $objInscrito
 */

// Proteção: não mostrar para quem já está contratado/participando
$statusOcultarDocContratacao = ['PARTICIPANDO', 'DESISTENTE'];
if (in_array($objInscrito->getStatusProcesso(), $statusOcultarDocContratacao)) {
    return;
}
?>

<style>
    .item-documento {
      border-left: 5px solid #28a745;
    }
    .item-liberado {
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
    .btnLink {
        all: unset;
        cursor: pointer;
        color: green;
    }
</style>

<p>Os candidatos que não apresentarem no ato da contratação os documentos obrigatórios exigidos no item 15.6 serão excluídos do Processo Seletivo, sendo convocados os candidatos imediatamente subsequentes conforme lista classificatória definitiva do Processo Seletivo.</p>

<?php
echo GerenciarUploadDocumentoAC::gerarBlocoUpload(
    $objInscrito,
    'CNH – Carteira Nacional de Habilitação (se habilitado)',
    'cnh',
    $obrigatorio = false
);

echo GerenciarUploadDocumentoAC::gerarBlocoUpload(
    $objInscrito,
    'Carteira de Trabalho (CTPS – física ou digital)',
    'ctps',
    $obrigatorio = true
);

echo GerenciarUploadDocumentoAC::gerarBlocoUpload(
    $objInscrito,
    'Certidão de Nascimento ou Casamento',
    'certidao_nascimento',
    $obrigatorio = true
);

echo GerenciarUploadDocumentoAC::gerarBlocoUpload(
    $objInscrito,
    'Foto recente 3x4 (para cracha)',
    'foto_cracha',
    $obrigatorio = true
);

if ($objInscrito->getSexo() == 'MASCULINO') {
    echo GerenciarUploadDocumentoAC::gerarBlocoUpload(
        $objInscrito,
        'Certificado de Reservista, para homens entre 18 e 45 anos)',
        'certificado_reservista',
        $obrigatorio = true
    );
}

echo GerenciarUploadDocumentoAC::gerarBlocoUpload(
    $objInscrito,
    'Certidão de Antecedentes Criminais',
    'certidao_antecedentes',
    $obrigatorio = true
);
?>

<form id="formAbrirArquivo" method="POST" action="<?php echo WB_URL_COMMAND ?>" target="_blank">
  <input type="hidden" name="c" value="GerenciarUploadDocumento">
  <input type="hidden" name="m" value="abrirDocumentoTela">
  <input type="hidden" name="cod" id="arquivoParaAbrir">
  <input type="hidden" name="codEvento" id="codEventoParaAbrir">
</form>

<script>
(function($){
  $('#docsContratacao .formUpload').off('submit').on('submit', function(e){
    e.preventDefault();

    var form = $(this);
    var container = form.closest('.upload-documento');
    var formData = new FormData(form[0]);
    var fileInput = container.find('input[type="file"]')[0];
    var progressBar = container.find('.barra-progresso');
    var mensagem = container.find('.mensagem-upload');
    var historico = container.find('.historico-envios');

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
      url: '<?php echo WB_URL_COMMAND ?>',
      type: 'POST',
      dataType: 'json',
      data: formData,
      processData: false,
      contentType: false,
      success: function(data){
        progressBar.val(100);
        if(data.statusMessage==1){
          historico.html('<span style="color: green;"><b>Documento enviado com sucesso!</b>' + data.message + ' <br>Última versão: ' + data.versao + ' - Data: ' + data.data + '</span>');
          container.find('input[name="codTrabalho"]').val(data.codTrabalho);
          var botaoAbrir = '<button type="button" class="btnLink" onclick="abrirArquivoGerado(\''+data.arquivoGerado+'\')">📄 Para conferir documento enviado, <b>Clique aqui para visualizar documento</b></button>';
          container.find('.botaoAbrir').html(botaoAbrir);
          progressBar.hide().val(0);
          fileInput.value = "";
        }else{
          mensagem.html('<span style="color: red;">' + data.message + '</span>');
          progressBar.hide().val(0);
        }
      },
      error: function(){
        mensagem.html('<span style="color: red;">Erro no envio, tente novamente ou entre em contato.</span>');
        progressBar.hide().val(0);
      }
    });
  });
})(jQuery);

function abrirArquivoGerado(nomeArquivo, codEvento) {
    jQuery('#arquivoParaAbrir').val(nomeArquivo);
    jQuery('#codEventoParaAbrir').val(codEvento);
    jQuery('#formAbrirArquivo').submit();
}
</script>

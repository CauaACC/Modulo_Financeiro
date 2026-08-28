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
  </style>
<script>
$(function(){
  $('.formUpload').on('submit', function(e){
    e.preventDefault();

    var form = $(this);
    var container = form.closest('.upload-documento');
    var formData = new FormData(form[0]);
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
      url: '<?php echo WB_URL_COMMAND ?>', // ou "index.php"
      type: 'POST',
      dataType: 'json',
      data: formData,
      processData: false,
      contentType: false,
      success: function(data){
        progressBar.val(100);
        if(data.statusMessage==1){
          historico.html('<span style="color: green;"><b>Documento enviado com sucesso!</b>' + data.message + ' <br>Última versão: ' + data.versao + ' - Data: ' + data.data + '</span>');

          $('#codTrabalho-'+data.codInscrito).val(data.codTrabalho);
          var botaoAbrir = '<button type="button" class="btnLink" onclick="abrirArquivoGerado(\''+data.arquivoGerado+'\')">📄 Para conferir documento enviado, <b>Clique aqui para visualizar documento</b></button>';
          $('.botaoAbrir[data-cod-area="' + data.slug_tipo_documento + '"]').html(botaoAbrir);

        }else{
          mensagem.html('<span style="color: red;">' + data.message);
          progressBar.val(0);
        }
      },
      error: function(){
        mensagem.html('<span style="color: red;">Erro no envio, tente novamente ou entre em contato.</span>');
        progressBar.val(0);
      }
    });
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
<br>• Por segurança, o sistema pode desconectar automaticamente após algum tempo sem uso. Se isso acontecer, faça login novamente para continuar o envio dos arquivos.
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


echo '<div style="padding:12px 14px;border:1px solid #f0ad4e;background:#fff8e5;border-radius:6px;color:#333;margin:10px 0;">
  <strong>📌 Período de envio de documentos encerrado em 19/12/2025.</strong>
  <span>Não é possível anexar ou substituir documentos após essa data.</span>
</div>';


    /*
    <h4>
      <a href='https://hutec.org.br/wp-content/uploads/2025/10/EDITAL_024_2025_resultado_homologacao_das_inscricoes_assinado-unif-num-v01.pdf' target="_blank">
        Resultado da primeira etapa: Clique aqui para Acessar PUBLICAÇÃO DA HOMOLOGAÇÃO DAS INSCRIÇÕES PRELIMINARES</a>
      <br><br>
        Aos candidatos que tiveram suas inscrições preliminares INDEFERIDAS, Formulário para Solicitação de Recurso disponível a partir de 11/10/2025 das 08h até 13/10/2025 18h</h4>
      <br>
    */
echo "<fieldset><legend>1 - Documentos Obrigatórios (Eliminatórios)</legend>";
echo "A <strong>não anexação</strong> de qualquer documento exigido nesses campos, dentro do prazo previsto, <strong>implicará no indeferimento da inscrição</strong><br>";

echo GerenciarUploadDocumentoAC::gerarBlocoUpload($objInscrito, 'Documento oficial com foto (RG/CNH/Passaporte etc.) ', 'documento_com_foto',$obrigatorio=true);
echo GerenciarUploadDocumentoAC::gerarBlocoUpload($objInscrito, 'CPF', 'cpf',$obrigatorio=true);
echo GerenciarUploadDocumentoAC::gerarBlocoUpload($objInscrito, 'Título Eleitoral', 'titulo_eleitor',$obrigatorio=true);

echo "<br>Comprovação de qualificação mínima - (Edital 032/2025 – item 9.2, inciso VII, alíneas a/b/c): <br>a) Diploma de Ensino Médio (preferencialmente com magistério) OU <br>b) Comprovante de matrícula (graduandos) OU <br>c) Diploma de Licenciatura.";
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

echo "<br><fieldset><legend>3 - Cálculo de Pontuação (autoavaliação, apenas para conferência) — Anexo II. A pontuação final será atribuída pela Comissão mediante avaliação de comprovantes enviados no currículo.</legend>";
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


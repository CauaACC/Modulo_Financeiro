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
    // echo "<div class='text-danger' style='font-weight:700;margin:6px 0 4px;'>Atenção</div>";
    // echo "<div>Para enviar cada documento:
    // <br> 1) clique em <em>Procurar…</em> e selecione o arquivo;
    // <br> 2) confirme que o nome apareceu ao lado;
    // <br> 3) clique em <em>Enviar Arquivo</em>;
    // <br> 4) aguarde a mensagem de sucesso. Se aparecer <em>Nenhum arquivo enviado</em>, o envio <u>não</u> foi concluído.
    // <br> 5) após mensagem de envio com sucesso, clique em <em>Abrir Arquivo Enviado</em> para visualizar o documento.
    // <br> 6) após confirmação de envio, repita o procedimento para demais documentos.
    // </div><br>";

    /*
    <h4>
      <a href='https://hutec.org.br/wp-content/uploads/2025/10/EDITAL_024_2025_resultado_homologacao_das_inscricoes_assinado-unif-num-v01.pdf' target="_blank">
        Resultado da primeira etapa: Clique aqui para Acessar PUBLICAÇÃO DA HOMOLOGAÇÃO DAS INSCRIÇÕES PRELIMINARES</a>
      <br><br>
        Aos candidatos que tiveram suas inscrições preliminares INDEFERIDAS, Formulário para Solicitação de Recurso disponível a partir de 11/10/2025 das 08h até 13/10/2025 18h</h4>
      <br>
    */

echo "<fieldset><legend>Documentos Comuns (Obrigatórios)</legend>";
echo GerenciarUploadDocumentoAC::gerarBlocoUpload($objInscrito, 'Documento com Foto', 'documento_com_foto');
echo GerenciarUploadDocumentoAC::gerarBlocoUpload($objInscrito, 'CPF', 'cpf');
echo GerenciarUploadDocumentoAC::gerarBlocoUpload($objInscrito, 'Título de Eleitor', 'titulo_eleitor');
echo GerenciarUploadDocumentoAC::gerarBlocoUpload($objInscrito, 'Comprovante de Endereço', 'comprovante_endereco');
echo "</fieldset>";

if ($objInscrito->getCodPerfil() == 566) {
    echo "<fieldset><legend>Profissional Especializado de Suporte (PES 1) Nível 1</legend>";
    echo GerenciarUploadDocumentoAC::gerarBlocoUpload($objInscrito, 'Diploma de Ensino Médio, preferencialmente com Habilitação em Magistério', 'diploma_medio_magisterio');
    echo GerenciarUploadDocumentoAC::gerarBlocoUpload($objInscrito, 'Declaração de Matrícula em Curso Superior', 'declaracao_matricula_superior');
    echo GerenciarUploadDocumentoAC::gerarBlocoUpload($objInscrito, 'Diploma de Licenciatura', 'diploma_licenciatura');

    echo GerenciarUploadDocumentoAC::gerarBlocoCriterios($objInscrito, 'criterios_pontuacao', 'criterios_pontuacao');
    // require_once(dirname(__FILE__)."/inscricao_trabalhos_consulta_prorede_anexo_2.php");
    echo "</fieldset>";
}

if ($objInscrito->getCodPerfil() == 565) {
    echo "<fieldset><legend>Profissional Especializado de Suporte (PES 2) Nível 2</legend>";
    echo GerenciarUploadDocumentoAC::gerarBlocoUpload($objInscrito, 'Diploma de Técnico em Enfermagem', 'diploma_tecnico_enfermagem');
    echo GerenciarUploadDocumentoAC::gerarBlocoUpload($objInscrito, 'Registro no Conselho de Classe (COREN)', 'registro_coren');
    echo GerenciarUploadDocumentoAC::gerarBlocoUpload($objInscrito, 'Certidão de Regularidade no Conselho', 'certidao_regularidade_conselho');

    echo GerenciarUploadDocumentoAC::gerarBlocoCriterios($objInscrito, 'criterios_pontuacao', 'criterios_pontuacao');
    // require_once(dirname(__FILE__)."/inscricao_trabalhos_consulta_prorede_anexo_3.php");
    echo "</fieldset>";
}

if ($objInscrito->getCodPerfil() == 568) {
    echo "<fieldset><legend>Profissional Supervisor de PES Nível 1 (Psicologia)</legend>";
    echo GerenciarUploadDocumentoAC::gerarBlocoUpload($objInscrito, 'Diploma de Graduação em Psicologia', 'diploma_psicologia');
    echo GerenciarUploadDocumentoAC::gerarBlocoUpload($objInscrito, 'Registro no Conselho Regional de Psicologia (CRP)', 'registro_crp');
    echo GerenciarUploadDocumentoAC::gerarBlocoUpload($objInscrito, 'Certidão de Regularidade no CRP', 'certidao_regularidade_crp');

    echo GerenciarUploadDocumentoAC::gerarBlocoCriterios($objInscrito, 'criterios_pontuacao', 'criterios_pontuacao');
    // require_once(dirname(__FILE__)."/inscricao_trabalhos_consulta_prorede_anexo_4.php");
    echo "</fieldset>";
}

if ($objInscrito->getCodPerfil() == 569) {
    echo "<fieldset><legend>Profissional Supervisor de PES Nível 2 (Enfermagem)</legend>";
    echo GerenciarUploadDocumentoAC::gerarBlocoUpload($objInscrito, 'Diploma de Graduação em Enfermagem', 'diploma_enfermagem');
    echo GerenciarUploadDocumentoAC::gerarBlocoUpload($objInscrito, 'Registro no COREN', 'registro_coren');
    echo GerenciarUploadDocumentoAC::gerarBlocoUpload($objInscrito, 'Certidão de Regularidade no COREN', 'certidao_regularidade_coren');

    echo GerenciarUploadDocumentoAC::gerarBlocoCriterios($objInscrito, 'criterios_pontuacao', 'criterios_pontuacao');
    // require_once(dirname(__FILE__)."/inscricao_trabalhos_consulta_prorede_anexo_5.php");
    echo "</fieldset>";
}

if ($objInscrito->getCodPerfil() == 570) {
    echo "<fieldset><legend>Profissional Referência em Avaliação Psicológica</legend>";
    echo GerenciarUploadDocumentoAC::gerarBlocoUpload($objInscrito, 'Diploma de Graduação em Psicologia', 'diploma_psicologia');
    echo GerenciarUploadDocumentoAC::gerarBlocoUpload($objInscrito, 'Registro no Conselho Regional de Psicologia (CRP)', 'registro_crp');
    echo GerenciarUploadDocumentoAC::gerarBlocoUpload($objInscrito, 'Certidão de Regularidade no CRP', 'certidao_regularidade_crp');

    echo GerenciarUploadDocumentoAC::gerarBlocoCriterios($objInscrito, 'criterios_pontuacao', 'criterios_pontuacao');
    // require_once(dirname(__FILE__)."/inscricao_trabalhos_consulta_prorede_anexo_6.php");
    echo "</fieldset>";
}
echo "<br><fieldset><legend>Currículo</legend>";
echo "<div style='font-weight:700;color:#c9302c;margin-top:6px;margin-bottom:4px;'>Atenção</div>";
echo "Crie um único arquivo em PDF com seu currículo e <strong>toda a documentação comprobatória</strong> (certificados, títulos, declarações etc.) que valide a pontuação informada.<br>
 <strong>A apresentação desses comprovantes é imprescindível para a definição da sua classificação e para a habilitação da inscrição.</strong>";

echo GerenciarUploadDocumentoAC::gerarBlocoUpload($objInscrito, 'Currículo', 'curriculo');
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


<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Capacitação</title>
  <style>
    .item-capitulo {
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
  </style>
<script>
$(function(){
  $('.formUpload').on('submit', function(e){
    e.preventDefault();

    var form = $(this);
    var container = form.closest('.upload-capitulo');
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
        if(data.statusMessage){
          // mensagem.html("Upload realizado com sucesso!");
          historico.html(data.message + ' <br>Última versão: ' + data.versao+ ' - Data: ' + data.data);
          $('#codTrabalho-'+data.codInscrito).val(data.codTrabalho);
          var botaoAbrir = '<button type="button" onclick="abrirArquivoGerado(\''+data.arquivoGerado+'\')">📄 Abrir Arquivo Enviado</button>';
          $('.botaoAbrir[data-cod-area="' + data.codAreaTematica + '"]').html(botaoAbrir);

        }else{
          mensagem.html("Erro no upload: " + data.message);
        }
      },
      error: function(){
        mensagem.html("Erro no envio, tente novamente ou entre em contato.");
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
  <input type="hidden" name="c" value="GerenciarUploadCapitulo">
  <input type="hidden" name="m" value="abrirDocumentoTela">
  <input type="hidden" name="cod" id="arquivoParaAbrir">
  <input type="hidden" name="codEvento" id="codEventoParaAbrir">
</form>

<div class="list-group">

  <div class="list-group-item item-liberado item-capitulo">
    <strong>Capítulo 01</strong><br>
  </div>  
  <div class="list-group-item item-liberado">
    <strong>25/03/2025 19h‑21h– (Síncrona)</strong><br>
    <i class="fa fa-group"></i> Abertura e Capítulo 1<br>
    <button class="btn-video" onclick="openVideo('cap1')">🎬 Assistir vídeo</button>
  </div>
  <div class="list-group-item item-liberado">
    <strong>17–24/03/2025 – (Assíncrona)</strong>
    <ul>
      <li><i class="fa fa-file"></i> 
      <a href="/hutec/78/inscricoes/resources/public/00078/capitulo-1-apresentacao-EduPrev-FINAL.pdf" target="_blank">Epigenética e experiências adversas</a></li>
      <li><i class="fa fa-file"></i> 
      <a href="/hutec/78/inscricoes/resources/public/00078/genetica-e-epigenetica-cor.pdf" target="_blank">Slides</a></li>
      <li><i class="fa fa-file"></i> 
      <a href="/hutec/78/inscricoes/resources/public/00078/problemaepigenetica.pdf" target="_blank">Problemas</a></li>
    </ul>
  </div>
  <?php //if ($_SERVER['REMOTE_ADDR'] === '177.82.0.28') { ?>
  <div class="list-group-item item-liberado">
    <strong>Enviar Tarefa Capítulo 01</strong>
    <ul>
        <li>
        <div class="upload-capitulo" data-capitulo="1">
        <?php
        $fws_['numCapitulo'] = '1';
        $objTrabalho = new Trabalho();
        $objTrabalho->setCodInscrito($objInscrito->getCodInscrito());
        $objTrabalho->setCodEvento(COD_EVENTO);
        $objTrabalho->setCodAreaTematica('101');
        $objTrabalho->setTipoDocumento('Capitulo-01');
        $objTrabalho->setStatus('ATIVADA');
        $objTrabalho->getByCapituloUltimo();        
        $gerenciarUploadCapitulo = new GerenciarUploadCapituloAC();
        $gerenciarUploadCapitulo->formEnviarCapitulo($objInscrito, $objTrabalho, $fws_, $debug = false);
        ?>
        </div>
        <?php
          if (!empty($objTrabalho->getArquivoGerado())) {
          ?>
            <div class="botaoAbrir" data-cod-area="<?php echo $objTrabalho->getCodAreaTematica()?>">
              <button type="button" onclick="abrirArquivoGerado('<?php echo $objTrabalho->getArquivoGerado() ?>', '<?php echo $objTrabalho->getCodEvento() ?>')">
                📄 Abrir Arquivo Enviado
              </button>
            </div>
          <?php
          }else{
            ?><div class="botaoAbrir" data-cod-area="<?php echo $objTrabalho->getCodAreaTematica()?>">
            <?php
          }
        ?>
      </li>
    </ul>
  </div>
  <?php //} ?>

  <div class="list-group-item item-liberado item-capitulo">
    <strong>Capítulo 02</strong><br>
  </div> 
  <div class="list-group-item item-liberado">    
    <strong>01/04/2025 – (Síncrona)</strong><br>
    <i class="fa fa-group"></i> <i class="fa fa-lock"></i> Capítulo 02 - Capacitação em Transtornos Mentais<br>
    <button class="btn-video" onclick="openVideo('cap2')">🎬 Assistir vídeo</button>
  </div>
  <div class="list-group-item item-liberado">
    <strong>26–31/03/2025 – (Assíncrona)</strong><br>
    <i class="fa fa-book"></i> 
    <a href="/hutec/78/inscricoes/resources/public/00078/Capitulo-2-Estresse-Problemas.pdf" target="_blank">Transtorno de estresse pós‑traumático: Medo vs Ansiedade</a>
  </div>
  <?php // if ($_SERVER['REMOTE_ADDR'] === '177.82.0.28') { ?>
  <div class="list-group-item item-liberado">
    <strong>Enviar Tarefa Capítulo 02</strong>
    <ul>
        <li>
        <div class="upload-capitulo" data-capitulo="2">
        <?php
        $fws_['numCapitulo'] = '2';
        $objTrabalho = new Trabalho();
        $objTrabalho->setCodInscrito($objInscrito->getCodInscrito());
        $objTrabalho->setCodEvento(COD_EVENTO);
        $objTrabalho->setCodAreaTematica('102');
        $objTrabalho->setTipoDocumento('Capitulo-02');
        $objTrabalho->setStatus('ATIVADA');
        $objTrabalho->getByCapituloUltimo();        
        $gerenciarUploadCapitulo = new GerenciarUploadCapituloAC();
        $gerenciarUploadCapitulo->formEnviarCapitulo($objInscrito, $objTrabalho, $fws_, $debug = false);
        ?>
        </div>
        <?php
          if (!empty($objTrabalho->getArquivoGerado())) {
          ?>
            <div class="botaoAbrir" data-cod-area="<?php echo $objTrabalho->getCodAreaTematica()?>">
              <button type="button" onclick="abrirArquivoGerado('<?php echo $objTrabalho->getArquivoGerado() ?>', '<?php echo $objTrabalho->getCodEvento() ?>')">
                Abrir Arquivo Enviado
              </button>
            </div>
          <?php
          }else{
            ?><div class="botaoAbrir" data-cod-area="<?php echo $objTrabalho->getCodAreaTematica()?>">
            <?php
          }
        ?>
      </li>
    </ul>
  </div>
  <?php //} ?>


  <div class="list-group-item item-liberado item-capitulo">
    <strong>Capítulo 03</strong><br>
  </div> 
  <div class="list-group-item item-liberado">    
    <strong>08/04/2025 · Terça-feira · 18h45 – 21h45 (Síncrona)</strong><br>
    <i class="fa fa-group"></i>
    Capítulo 03 - Transtorno de Ansiedade
    <br>
    <?php
    //verificar se libera aula pela data 08/04/2025 
    if (date('Ymd') >= '20250408+1') {
      ?>
      <button class="btn-video" onclick="openVideo('cap3')">🎬 Assistir vídeo</button>
      <?php
    }else if (date('Ymd') >= '20250408') {
      ?>
      <a href="https://meet.google.com/zxr-xngx-gar" target="_blank"><button class="btn-video">🎬 Google Meet: https://meet.google.com/zxr-xngx-gar</button></a>
      <?php
    } else {
      echo '<i class="fa fa-lock"></i> Aula Virtual - (Aguardando liberação)';
    }
    ?>    
  </div>    
  <div class="list-group-item item-liberado">
    <strong>02-07/04/2025 – (Assíncrona)</strong><br>
    <i class="fa fa-book"></i> 
    <a href="/hutec/78/inscricoes/resources/public/00078/cap-3-ansiedade-problema.pdf" target="_blank">Transtorno de Ansiedade - Problema</a>
  </div>  
  <?php // if ($_SERVER['REMOTE_ADDR'] === '177.82.0.28') { ?>
  <div class="list-group-item item-liberado">
    <strong>Enviar Tarefa Capítulo 03</strong>
    <ul>
        <li>
        <div class="upload-capitulo" data-capitulo="3">
        <?php
        $fws_['numCapitulo'] = '3';
        $objTrabalho = new Trabalho();
        $objTrabalho->setCodInscrito($objInscrito->getCodInscrito());
        $objTrabalho->setCodEvento(COD_EVENTO);
        $objTrabalho->setCodAreaTematica('103');
        $objTrabalho->setTipoDocumento('Capitulo-03');
        $objTrabalho->setStatus('ATIVADA');
        $objTrabalho->getByCapituloUltimo();        
        $gerenciarUploadCapitulo = new GerenciarUploadCapituloAC();
        $gerenciarUploadCapitulo->formEnviarCapitulo($objInscrito, $objTrabalho, $fws_, $debug = false);
        ?>
        </div>
        <?php
          if (!empty($objTrabalho->getArquivoGerado())) {
          ?>
            <div class="botaoAbrir" data-cod-area="<?php echo $objTrabalho->getCodAreaTematica()?>">
              <button type="button" onclick="abrirArquivoGerado('<?php echo $objTrabalho->getArquivoGerado() ?>', '<?php echo $objTrabalho->getCodEvento() ?>')">
                📄 Abrir Arquivo Enviado
              </button>
            </div>
          <?php
          }else{
            ?><div class="botaoAbrir" data-cod-area="<?php echo $objTrabalho->getCodAreaTematica()?>">
            <?php
          }
        ?>
      </li>
    </ul>
  </div>
  <?php //} ?>



  <div class="list-group-item item-liberado item-capitulo">
    <strong>Capítulo 04</strong><br>
  </div> 
  <div class="list-group-item item-liberado">    
    <strong>15/04/2025 · Terça-feira · 19h – 21h (Síncrona)</strong><br>
    <i class="fa fa-group"></i>
    Capítulo 04 - Detecção Precoce do Transtorno Obsessivo Compulsivo e Transtornos Relacionados
    <br>
    <?php
    //verificar se libera aula pela data 08/04/2025 
    if (date('Ymd') >= '20250415+1') {
      ?>
      <button class="btn-video" onclick="openVideo('cap4')">🎬 Assistir vídeo</button>
      <?php
    }else if (date('Ymd') >= '20250415') {
      ?>
      <a href="https://meet.google.com/qvd-xdqo-tge" target="_blank"><button class="btn-video">🎬 Google Meet: https://meet.google.com/qvd-xdqo-tge</button></a>
      <?php
    } else {
      echo '<i class="fa fa-lock"></i> Aula Virtual - (Aguardando liberação)';
    }
    ?>
    <br><i class="fa fa-book"></i> 
    <a href="/hutec/78/inscricoes/resources/public/00078/Capitulo_4_slides-aula-2025-04-15.pdf" target="_blank">Transtorno Obsessivo-Compulsivo - Slides da Aula 15/04/2025</a>
  </div>
  <div class="list-group-item item-liberado">
    <strong>09-15/04/2025 – (Assíncrona)</strong><br>
    <i class="fa fa-book"></i> 
    <a href="/hutec/78/inscricoes/resources/public/00078/cap-4-Obsessivo-Compulsivo-Problema.pdf" target="_blank">Transtorno Obsessivo-Compulsivo - Problema</a>
  </div>  
  <?php // if ($_SERVER['REMOTE_ADDR'] === '177.82.0.28') { ?>
  <div class="list-group-item item-liberado">
    <strong>Enviar Tarefa Capítulo 04</strong>
    <ul>
        <li>
        <div class="upload-capitulo" data-capitulo="4">
        <?php
        $fws_['numCapitulo'] = '4';
        $objTrabalho = new Trabalho();
        $objTrabalho->setCodInscrito($objInscrito->getCodInscrito());
        $objTrabalho->setCodEvento(COD_EVENTO);
        $objTrabalho->setCodAreaTematica('104');
        $objTrabalho->setTipoDocumento('Capitulo-04');
        $objTrabalho->setStatus('ATIVADA');
        $objTrabalho->getByCapituloUltimo();        
        $gerenciarUploadCapitulo = new GerenciarUploadCapituloAC();
        $gerenciarUploadCapitulo->formEnviarCapitulo($objInscrito, $objTrabalho, $fws_, $debug = false);
        ?>
        </div>
        <?php
          if (!empty($objTrabalho->getArquivoGerado())) {
          ?>
            <div class="botaoAbrir" data-cod-area="<?php echo $objTrabalho->getCodAreaTematica()?>">
              <button type="button" onclick="abrirArquivoGerado('<?php echo $objTrabalho->getArquivoGerado() ?>', '<?php echo $objTrabalho->getCodEvento() ?>')">
                📄 Abrir Arquivo Enviado
              </button>
            </div>
          <?php
          }else{
            ?><div class="botaoAbrir" data-cod-area="<?php echo $objTrabalho->getCodAreaTematica()?>">
            <?php
          }
        ?>
      </li>
    </ul>
  </div>
  <?php //} ?>

    <!-- Próxima Aula -->
<?php
$txtCapitulo = 'Capítulo 05';
$txtCapituloTit = 'Transtorno Depressivos: Compreendendo e Cuidando';
$txtData = '29/04/2025';
$txtDataSincrona = 'Terça-feira · 19h – 21h (Síncrona)';
$txtDataPeriodo  = '16–28/04/2025';
$dtLiberacaoCtAssincrono = '20250416';
$dtLiberacaoCtSincrono   = '20250429';
$txtClassBloqueado = 'item-bloqueado';
if (date('Ymd') < $dtLiberacaoCtAssincrono) {
  $txtClassBloqueado = 'item-bloqueado';
} else {
  $txtClassBloqueado = 'item-liberado';
}
?>     
  <div class="list-group-item item-liberado item-capitulo">
    <strong><?php echo $txtCapitulo ?></strong><br>
  </div> 
  <div class="list-group-item <?php echo $txtClassBloqueado ?>">    
    <strong><?php echo $txtData. ' - ' .$txtDataSincrona ?></strong><br>
    <i class="fa fa-group"></i> <i class="fa fa-lock"></i>
    <?php echo $txtCapitulo ?> - <?php echo $txtCapituloTit ?>
    <br><?php echo $txtDataSincrona ?>
    <?php
    if (date('Ymd') >= $dtLiberacaoCtSincrono+1) {
      ?>
      <button class="btn-video" onclick="openVideo('cap5')">🎬 Assistir vídeo</button>
      <?php
    }else if (date('Ymd') >= $dtLiberacaoCtSincrono) {
      ?>
      <a href="https://meet.google.com/dmv-jyvp-bnk" target="_blank">
        <button class="btn-video">🎬 Google Meet: https://meet.google.com/dmv-jyvp-bnk</button></a>
      <?php
    } else {
      echo '<i class="fa fa-lock"></i> Aula Virtual - (Aguardando liberação)';
    }
    ?>    
  </div>  
  <div class="list-group-item <?php echo $txtClassBloqueado ?>">    
    <strong><?php echo $txtDataPeriodo ?> – (Assíncrona)</strong><br>
    <i class="fa fa-lock"></i>
    <?php if (date('Ymd') < $dtLiberacaoCtAssincrono) { ?>
        <?php echo $txtCapituloTit ?> - Aguardando liberação de conteúdo
    <?php }else{ ?>
        <a href="/hutec/78/inscricoes/resources/public/00078/cap-5-Depressivo-Problema-v02.pdf" target="_blank"><?php echo $txtCapituloTit ?> - Problema</a>
    <?php } ?>
  </div>
  <div class="list-group-item item-liberado">
    <strong>Enviar Tarefa Capítulo 05</strong>
    <ul>
        <li>
        <div class="upload-capitulo" data-capitulo="5">
        <?php
        $fws_['numCapitulo'] = '5';
        $objTrabalho = new Trabalho();
        $objTrabalho->setCodInscrito($objInscrito->getCodInscrito());
        $objTrabalho->setCodEvento(COD_EVENTO);
        $objTrabalho->setCodAreaTematica('105');
        $objTrabalho->setTipoDocumento('Capitulo-05');
        $objTrabalho->setStatus('ATIVADA');
        $objTrabalho->getByCapituloUltimo();        
        $gerenciarUploadCapitulo = new GerenciarUploadCapituloAC();
        $gerenciarUploadCapitulo->formEnviarCapitulo($objInscrito, $objTrabalho, $fws_, $debug = false);
        ?>
        </div>
        <?php
          if (!empty($objTrabalho->getArquivoGerado())) {
          ?>
            <div class="botaoAbrir" data-cod-area="<?php echo $objTrabalho->getCodAreaTematica()?>">
              <button type="button" onclick="abrirArquivoGerado('<?php echo $objTrabalho->getArquivoGerado() ?>', '<?php echo $objTrabalho->getCodEvento() ?>')">
                📄 Abrir Arquivo Enviado
              </button>
            </div>
          <?php
          }else{
            ?><div class="botaoAbrir" data-cod-area="<?php echo $objTrabalho->getCodAreaTematica()?>">
            <?php
          }
        ?>
      </li>
    </ul>
  </div>

    <!-- Próxima Aula -->
    <?php
$txtCapitulo = 'Capítulo 06';
$txtCapituloTit = 'Transtorno Bipolar';
$txtData = '06/05/2025';
$txtDataSincrona = 'Terça-feira · 19h – 21h (Síncrona)';
$txtDataPeriodo  = '30/04 a 05/05/2025';
$dtLiberacaoCtAssincrono = '20250430';
$dtLiberacaoCtSincrono   = '20250506';
$txtClassBloqueado = 'item-bloqueado';
if (date('Ymd') < $dtLiberacaoCtAssincrono) {
  $txtClassBloqueado = 'item-bloqueado';
} else {
  $txtClassBloqueado = 'item-liberado';
}
?>     
  <div class="list-group-item item-liberado item-capitulo">
    <strong><?php echo $txtCapitulo ?></strong><br>
  </div> 
  <div class="list-group-item <?php echo $txtClassBloqueado ?>">    
    <strong><?php echo $txtData. ' - ' .$txtDataSincrona ?></strong><br>
    <i class="fa fa-group"></i> <i class="fa fa-lock"></i>
    <?php echo $txtCapitulo ?> - <?php echo $txtCapituloTit ?>
    <br><?php echo $txtDataSincrona ?>
    <?php
    if (date('Ymd') >= $dtLiberacaoCtSincrono+1) {
      ?>
      <button class="btn-video" onclick="openVideo('cap6')">🎬 Assistir vídeo</button>
      <?php
    }else if (date('Ymd') >= $dtLiberacaoCtSincrono) {
      ?>
      <a href="https://meet.google.com/xrq-qaqq-bfx" target="_blank">
        <button class="btn-video">🎬 Google Meet: https://meet.google.com/xrq-qaqq-bfx</button></a>
      <?php
    } else {
      echo '<i class="fa fa-lock"></i> Aula Virtual - (Aguardando liberação)';
    }
    ?>    
  </div>  
  <!-- aula assincrona -->
  <div class="list-group-item <?php echo $txtClassBloqueado ?>">    
    <strong><?php echo $txtDataPeriodo ?> – (Assíncrona)</strong><br>
    <i class="fa fa-lock"></i>
    <?php if (date('Ymd') < $dtLiberacaoCtAssincrono) { ?>
        <?php echo $txtCapituloTit ?> - Aguardando liberação de conteúdo
    <?php }else{ ?>
        <a href="/hutec/78/inscricoes/resources/public/00078/cap-6-Bipolar-Problema.pdf" target="_blank"><?php echo $txtCapituloTit ?> - Problema</a>
    <?php } ?>
  </div>
  <!-- Enviar tarefa -->
  <div class="list-group-item item-liberado">
    <strong>Enviar Tarefa Capítulo 06</strong>
    <ul>
        <li>
        <div class="upload-capitulo" data-capitulo="6">
        <?php
        $fws_['numCapitulo'] = '6';
        $objTrabalho = new Trabalho();
        $objTrabalho->setCodInscrito($objInscrito->getCodInscrito());
        $objTrabalho->setCodEvento(COD_EVENTO);
        $objTrabalho->setCodAreaTematica('106');
        $objTrabalho->setTipoDocumento('Capitulo-06');
        $objTrabalho->setStatus('ATIVADA');
        $objTrabalho->getByCapituloUltimo();        
        $gerenciarUploadCapitulo = new GerenciarUploadCapituloAC();
        $gerenciarUploadCapitulo->formEnviarCapitulo($objInscrito, $objTrabalho, $fws_, $debug = false);
        ?>
        </div>
        <?php
          if (!empty($objTrabalho->getArquivoGerado())) {
          ?>
            <div class="botaoAbrir" data-cod-area="<?php echo $objTrabalho->getCodAreaTematica()?>">
              <button type="button" onclick="abrirArquivoGerado('<?php echo $objTrabalho->getArquivoGerado() ?>', '<?php echo $objTrabalho->getCodEvento() ?>')">
                📄 Abrir Arquivo Enviado
              </button>
            </div>
          <?php
          }else{
            ?><div class="botaoAbrir" data-cod-area="<?php echo $objTrabalho->getCodAreaTematica()?>">
            <?php
          }
        ?>
      </li>
    </ul>
  </div>
<!-- Próxima Aula -->
<?php
$ordemAtividade = 7;
$codAreaTematica = '107';
$txtCapitulo = 'Capítulo 07';
$idCapitulo = 'Capitulo-07';
$txtCapituloTit = 'Espectro da Esquizofrenia';
$txtData = '13/05/2025';
$txtDataSincrona = 'Terça-feira · 19h – 21h (Síncrona)';
$txtDataPeriodo  = '07 a 12/05/2025';
$dtLiberacaoCtAssincrono = '20250507';
$dtLiberacaoCtSincrono   = '20250513';
$txtClassBloqueado = 'item-bloqueado';
if (date('Ymd') < $dtLiberacaoCtAssincrono) {
  $txtClassBloqueado = 'item-bloqueado';
} else {
  $txtClassBloqueado = 'item-liberado';
}
$urlMeet = 'https://meet.google.com/seg-ttdq-tsu';
$urlConteudoAssincrono = '/hutec/78/inscricoes/resources/public/00078/cap-7-Esquizofrenia-Problema.pdf';
?>     
  <div class="list-group-item item-liberado item-capitulo">
    <strong><?php echo $txtCapitulo ?></strong><br>
  </div> 
  <div class="list-group-item <?php echo $txtClassBloqueado ?>">    
    <strong><?php echo $txtData. ' - ' .$txtDataSincrona ?></strong><br>
    <i class="fa fa-group"></i> <i class="fa fa-lock"></i>
    <?php echo $txtCapitulo ?> - <?php echo $txtCapituloTit ?>
    <br><?php echo $txtDataSincrona ?>
    <?php
    if (date('Ymd') >= $dtLiberacaoCtSincrono+1) {
      ?>
      <button class="btn-video" onclick="openVideo('cap<?php echo $ordemAtividade?>')">🎬 Assistir vídeo</button>
      <?php
    }else if (date('Ymd') >= $dtLiberacaoCtSincrono and $urlMeet!='') {
      ?>
      <a href="<?php echo $urlMeet ?>" target="_blank">
        <button class="btn-video">🎬 Google Meet: <?php echo $urlMeet ?></button></a>
      <?php
    } else {
      echo '<i class="fa fa-lock"></i> Aula Virtual - (Aguardando liberação)';
    }
    ?>  
    <br><i class="fa fa-file"></i> 
    <a href="/hutec/78/inscricoes/resources/public/00078/Capitulo_7_slides.pdf" target="_blank">Slides</a>
  </div>  
  <div class="list-group-item <?php echo $txtClassBloqueado ?>">    
    <strong><?php echo $txtDataPeriodo ?> – (Assíncrona)</strong><br>
    <i class="fa fa-lock"></i>
    <?php if (date('Ymd') < $dtLiberacaoCtAssincrono) { ?>
        <?php echo $txtCapituloTit ?> - Aguardando liberação de conteúdo
    <?php }else{ ?>
        <a href="<?php echo $urlConteudoAssincrono ?>" target="_blank"><?php echo $txtCapituloTit ?> - Problema</a>
    <?php } ?>
  </div>
  <!-- Enviar tarefa -->
  <div class="list-group-item item-liberado">
    <strong>Enviar Tarefa Capítulo <?php echo $ordemAtividade?></strong>
    <ul>
        <li>
        <div class="upload-capitulo" data-capitulo="<?php echo $ordemAtividade?>">
        <?php
        $fws_['numCapitulo'] = $ordemAtividade;
        $objTrabalho = new Trabalho();
        $objTrabalho->setCodInscrito($objInscrito->getCodInscrito());
        $objTrabalho->setCodEvento(COD_EVENTO);
        $objTrabalho->setCodAreaTematica($codAreaTematica);
        $objTrabalho->setTipoDocumento($idCapitulo);
        $objTrabalho->setStatus('ATIVADA');
        $objTrabalho->getByCapituloUltimo();        
        $gerenciarUploadCapitulo = new GerenciarUploadCapituloAC();
        $gerenciarUploadCapitulo->formEnviarCapitulo($objInscrito, $objTrabalho, $fws_, $debug = false);
        ?>
        </div>
        <?php
          if (!empty($objTrabalho->getArquivoGerado())) {
          ?>
            <div class="botaoAbrir" data-cod-area="<?php echo $objTrabalho->getCodAreaTematica()?>">
              <button type="button" onclick="abrirArquivoGerado('<?php echo $objTrabalho->getArquivoGerado() ?>', '<?php echo $objTrabalho->getCodEvento() ?>')">
                📄 Abrir Arquivo Enviado
              </button>
            </div>
          <?php
          }else{
            ?><div class="botaoAbrir" data-cod-area="<?php echo $objTrabalho->getCodAreaTematica()?>">
            <?php
          }
        ?>
      </li>
    </ul>
  </div>
<!-- Próxima Aula -->
<?php
$ordemAtividade = 8;
$codAreaTematica = '108';
$txtCapitulo = 'Capítulo 08';
$idCapitulo = 'Capitulo-08';
$txtCapituloTit = 'Transtornos Alimentares';
$txtData = '20/05/2025';
$txtDataSincrona = 'Terça-feira · 19h – 21h (Síncrona)';
$txtDataPeriodo  = '14 a 19/05/2025';
$dtLiberacaoCtAssincrono = '20250513';
$dtLiberacaoCtSincrono   = '20250520';
$txtClassBloqueado = 'item-bloqueado';
if (date('Ymd') < $dtLiberacaoCtAssincrono) {
  $txtClassBloqueado = 'item-bloqueado';
} else {
  $txtClassBloqueado = 'item-liberado';
}
$urlMeet = 'https://meet.google.com/cbf-getj-gqj';
$urlVideo = 'Cap-08';
$urlConteudoAssincrono = '/hutec/78/inscricoes/resources/public/00078/cap-8-Alimentar-Problemas.pdf';
?>     
  <div class="list-group-item item-liberado item-capitulo">
    <strong><?php echo $txtCapitulo ?></strong><br>
  </div> 
  <div class="list-group-item <?php echo $txtClassBloqueado ?>">    
    <strong><?php echo $txtData. ' - ' .$txtDataSincrona ?></strong><br>
    <i class="fa fa-group"></i> <i class="fa fa-lock"></i>
    <?php echo $txtCapitulo ?> - <?php echo $txtCapituloTit ?>
    <br><?php echo $txtDataSincrona ?>
    <?php
    if (date('Ymd') >= $dtLiberacaoCtSincrono+1 and $urlVideo!='') {
      ?>
      <button class="btn-video" onclick="openVideo('cap<?php echo $ordemAtividade?>')">🎬 Assistir vídeo</button>
      <?php
    }else if ((($dtLiberacaoCtSincrono-1 <= date('Ymd')) and (date('Ymd') <=$dtLiberacaoCtSincrono+1)) and $urlMeet!='') {
      ?>
      <a href="<?php echo $urlMeet ?>" target="_blank">
        <button class="btn-video">🎬 Google Meet: <?php echo $urlMeet ?></button></a>
      <?php
    } else {
      echo '<i class="fa fa-lock"></i> Aula Virtual - (Aguardando liberação)';
    }
    ?>   
    <br><i class="fa fa-file"></i> 
    <a href="/hutec/78/inscricoes/resources/public/00078/Capitulo_8_slides.pdf" target="_blank">Slides</a> 
  </div>  
  <div class="list-group-item <?php echo $txtClassBloqueado ?>">    
    <strong><?php echo $txtDataPeriodo ?> – (Assíncrona)</strong><br>
    <i class="fa fa-lock"></i>
    <?php if (date('Ymd') < $dtLiberacaoCtAssincrono) { ?>
        <?php echo $txtCapituloTit ?> - Aguardando liberação de conteúdo
    <?php }else{ ?>
        <a href="<?php echo $urlConteudoAssincrono ?>" target="_blank"><?php echo $txtCapituloTit ?> - Problema</a>
    <?php } ?>
  </div>
  <!-- Enviar tarefa -->
  <div class="list-group-item item-liberado">
    <strong>Enviar Tarefa Capítulo <?php echo $ordemAtividade?></strong>
    <ul>
        <li>
        <div class="upload-capitulo" data-capitulo="<?php echo $ordemAtividade?>">
        <?php
        $fws_['numCapitulo'] = $ordemAtividade;
        $objTrabalho = new Trabalho();
        $objTrabalho->setCodInscrito($objInscrito->getCodInscrito());
        $objTrabalho->setCodEvento(COD_EVENTO);
        $objTrabalho->setCodAreaTematica($codAreaTematica);
        $objTrabalho->setTipoDocumento($idCapitulo);
        $objTrabalho->setStatus('ATIVADA');
        $objTrabalho->getByCapituloUltimo();        
        $gerenciarUploadCapitulo = new GerenciarUploadCapituloAC();
        $gerenciarUploadCapitulo->formEnviarCapitulo($objInscrito, $objTrabalho, $fws_, $debug = false);
        ?>
        </div>
        <?php
          if (!empty($objTrabalho->getArquivoGerado())) {
          ?>
            <div class="botaoAbrir" data-cod-area="<?php echo $objTrabalho->getCodAreaTematica()?>">
              <button type="button" onclick="abrirArquivoGerado('<?php echo $objTrabalho->getArquivoGerado() ?>', '<?php echo $objTrabalho->getCodEvento() ?>')">
                📄 Visualizar Arquivo Enviado
              </button>
            </div>
          <?php
          }
        ?>
      </li>
    </ul>
  </div>
<!-- Próxima Aula -->
<?php
$ordemAtividade = 9;
$codAreaTematica = '109';
$txtCapitulo = 'Capítulo 09';
$idCapitulo = 'Capitulo-09';
$txtCapituloTit = 'Transtornos Relacionados ao uso de Substâncias e Comportamentos';
$txtData = '27/05/2025';
$txtDataSincrona = 'Terça-feira · 19h – 21h (Síncrona)';
$txtDataPeriodo  = '21 a 26/05/2025';
$dtLiberacaoCtAssincrono = '20250520';
$dtLiberacaoCtSincrono   = '20250526';
$txtClassBloqueado = 'item-bloqueado';
if (date('Ymd') < $dtLiberacaoCtAssincrono) {
  $txtClassBloqueado = 'item-bloqueado';
} else {
  $txtClassBloqueado = 'item-liberado';
}
$urlMeet = 'https://meet.google.com/zzp-ryby-xwq';
$urlVideo = 'Cap-09';
$urlConteudoAssincrono = '/hutec/78/inscricoes/resources/public/00078/cap-9-Substancias-Problema.pdf';
?>     
  <div class="list-group-item item-liberado item-capitulo">
    <strong><?php echo $txtCapitulo ?></strong><br>
  </div> 
  <div class="list-group-item <?php echo $txtClassBloqueado ?>">    
    <strong><?php echo $txtData. ' - ' .$txtDataSincrona ?></strong><br>
    <i class="fa fa-group"></i> <i class="fa fa-lock"></i>
    <?php echo $txtCapitulo ?> - <?php echo $txtCapituloTit ?>
    <br><?php echo $txtDataSincrona ?>
    <?php
    if (date('Ymd') >= $dtLiberacaoCtSincrono+1 and $urlVideo != '') {
      ?>
      <button class="btn-video" onclick="openVideo('cap<?php echo $ordemAtividade?>')">🎬 Assistir vídeo</button>
      <?php
    }else if ((($dtLiberacaoCtSincrono-1 <= date('Ymd')) and (date('Ymd') <=$dtLiberacaoCtSincrono+1)) and $urlMeet!='') {
      ?>
      <a href="<?php echo $urlMeet ?>" target="_blank">
        <button class="btn-video">🎬 Google Meet: <?php echo $urlMeet ?></button></a>
      <?php
    } else {
      echo '<i class="fa fa-lock"></i> Aula Virtual - (Aguardando liberação)';
    }
    ?>    
  </div>  
  <div class="list-group-item <?php echo $txtClassBloqueado ?>">    
    <strong><?php echo $txtDataPeriodo ?> – (Assíncrona)</strong><br>
    <i class="fa fa-lock"></i>
    <?php if (date('Ymd') < $dtLiberacaoCtAssincrono) { ?>
        <?php echo $txtCapituloTit ?> - Aguardando liberação de conteúdo
    <?php }else{ ?>
        <a href="<?php echo $urlConteudoAssincrono ?>" target="_blank"><?php echo $txtCapituloTit ?> - Problema</a>
    <?php } ?>
  </div>
  <!-- Enviar tarefa -->
  <div class="list-group-item item-liberado">
    <strong>Enviar Tarefa Capítulo <?php echo $ordemAtividade?></strong>
    <ul>
        <li>
        <div class="upload-capitulo" data-capitulo="<?php echo $ordemAtividade?>">
        <?php
        $fws_['numCapitulo'] = $ordemAtividade;
        $objTrabalho = new Trabalho();
        $objTrabalho->setCodInscrito($objInscrito->getCodInscrito());
        $objTrabalho->setCodEvento(COD_EVENTO);
        $objTrabalho->setCodAreaTematica($codAreaTematica);
        $objTrabalho->setTipoDocumento($idCapitulo);
        $objTrabalho->setStatus('ATIVADA');
        $objTrabalho->getByCapituloUltimo();        
        $gerenciarUploadCapitulo = new GerenciarUploadCapituloAC();
        $gerenciarUploadCapitulo->formEnviarCapitulo($objInscrito, $objTrabalho, $fws_, $debug = false);
        ?>
        </div>
        <?php
          if (!empty($objTrabalho->getArquivoGerado())) {
          ?>
            <div class="botaoAbrir" data-cod-area="<?php echo $objTrabalho->getCodAreaTematica()?>">
              <button type="button" onclick="abrirArquivoGerado('<?php echo $objTrabalho->getArquivoGerado() ?>', '<?php echo $objTrabalho->getCodEvento() ?>')">
                📄 Visualizar Arquivo Enviado
              </button>
            </div>
          <?php
          }
        ?>
      </li>
    </ul>
  </div>

<!-- Próxima Aula Cap 10-->
<?php
$ordemAtividade = 10;
$codAreaTematica = '110';
$txtCapitulo = 'Capítulo 10';
$idCapitulo = 'Capitulo-10';
$txtCapituloTit = 'Suicídio na Infância, Adolescência e Adulto Jovem';
$txtData = '03/06/2025';
$txtDataSincrona = 'Terça-feira · 19h – 21h (Síncrona)';
$txtDataPeriodo  = '28/05 a 02/06/2025';
$dtLiberacaoCtAssincrono = '20250527';
$dtLiberacaoCtSincrono   = '20250602';
$txtClassBloqueado = 'item-bloqueado';
if (date('Ymd') < $dtLiberacaoCtAssincrono) {
  $txtClassBloqueado = 'item-bloqueado';
} else {
  $txtClassBloqueado = 'item-liberado';
}
#$urlMeet = 'https://meet.google.com/vku-fpae-mus';
$urlMeet = '';
$urlVideo = '2c4c4779-da50-4cf3-b786-6797435ff6a1';
$urlConteudoAssincrono = '/hutec/78/inscricoes/resources/public/00078/cap-10-Suicidio-Problema.pdf';
?>     
  <div class="list-group-item item-liberado item-capitulo">
    <strong><?php echo $txtCapitulo ?></strong><br>
  </div> 
  <div class="list-group-item <?php echo $txtClassBloqueado ?>">    
    <strong><?php echo $txtData. ' - ' .$txtDataSincrona ?></strong><br>
    <i class="fa fa-group"></i> <i class="fa fa-lock"></i>
    <?php echo $txtCapitulo ?> - <?php echo $txtCapituloTit ?>
    <br><?php echo $txtDataSincrona ?>
    <?php
    if (date('Ymd') >= $dtLiberacaoCtSincrono+1 and $urlVideo != '') {
      ?>
      <button class="btn-video" onclick="openVideo('cap<?php echo $ordemAtividade?>')">🎬 Assistir vídeo</button>
      <?php
    }else if (date('Ymd') >= $dtLiberacaoCtSincrono and $urlMeet!='') {
      ?>
      <a href="<?php echo $urlMeet ?>" target="_blank">
        <button class="btn-video">🎬 Google Meet: <?php echo $urlMeet ?></button></a>
      <?php
    } else {
      echo '<i class="fa fa-lock"></i> Aula Virtual - (Aguardando liberação)';
    }
    ?>  
    <!-- slides aqui -->  
  </div>  
  <div class="list-group-item <?php echo $txtClassBloqueado ?>">    
    <strong><?php echo $txtDataPeriodo ?> – (Assíncrona)</strong><br>
    <i class="fa fa-lock"></i>
    <?php if (date('Ymd') < $dtLiberacaoCtAssincrono) { ?>
        <?php echo $txtCapituloTit ?> - Aguardando liberação de conteúdo
    <?php }else{ ?>
        <a href="<?php echo $urlConteudoAssincrono ?>" target="_blank"><?php echo $txtCapituloTit ?> - Problema</a>
    <?php } ?>
  </div>
  <!-- Enviar tarefa -->
  <?php if (date('Ymd') > $dtLiberacaoCtAssincrono) { ?>
  <div class="list-group-item item-liberado">
    <strong>Enviar Tarefa Capítulo <?php echo $ordemAtividade?></strong>
    <ul>
        <li>
        <div class="upload-capitulo" data-capitulo="<?php echo $ordemAtividade?>">
        <?php
        $fws_['numCapitulo'] = $ordemAtividade;
        $objTrabalho = new Trabalho();
        $objTrabalho->setCodInscrito($objInscrito->getCodInscrito());
        $objTrabalho->setCodEvento(COD_EVENTO);
        $objTrabalho->setCodAreaTematica($codAreaTematica);
        $objTrabalho->setTipoDocumento($idCapitulo);
        $objTrabalho->setStatus('ATIVADA');
        $objTrabalho->getByCapituloUltimo();        
        $gerenciarUploadCapitulo = new GerenciarUploadCapituloAC();
        $gerenciarUploadCapitulo->formEnviarCapitulo($objInscrito, $objTrabalho, $fws_, $debug = false);
        ?>
        </div>
        <?php
          if (!empty($objTrabalho->getArquivoGerado())) {
          ?>
            <div class="botaoAbrir" data-cod-area="<?php echo $objTrabalho->getCodAreaTematica()?>">
              <button type="button" onclick="abrirArquivoGerado('<?php echo $objTrabalho->getArquivoGerado() ?>', '<?php echo $objTrabalho->getCodEvento() ?>')">
                📄 Visualizar Arquivo Enviado
              </button>
            </div>
          <?php
          }
        ?>
      </li>
    </ul>
  </div>  
  <?php } ?>

<!-- Próxima Aula Cap 11 -->
<?php
$ordemAtividade = 11;
$codAreaTematica = '111';
$txtCapitulo = 'Capítulo 11';
$idCapitulo = 'Capitulo-11';
$txtCapituloTit = 'Transtornos do Neurodesenvolvimento';
$txtData = '10/06/2025';
$txtDataSincrona = 'Terça-feira · 19h – 21h (Síncrona)';
$txtDataPeriodo  = '04/06 a 09/06/2025';
$dtLiberacaoCtAssincrono = '20250609';
$dtLiberacaoCtSincrono   = '20250605';
$txtClassBloqueado = 'item-bloqueado';
if (date('Ymd') < $dtLiberacaoCtAssincrono) {
  $txtClassBloqueado = 'item-bloqueado';
} else {
  $txtClassBloqueado = 'item-liberado';
}
$urlMeet = '';
$urlVideo = '03156644-8141-4dd0-aa79-4c39fd274d00';
$urlConteudoAssincrono = '/hutec/78/inscricoes/resources/public/00078/cap-11-Transtorno-do-Desenvolvimento-Problema.pdf';
?>     
  <div class="list-group-item item-liberado item-capitulo">
    <strong><?php echo $txtCapitulo ?></strong><br>
  </div> 
  <div class="list-group-item <?php echo $txtClassBloqueado ?>">    
    <strong><?php echo $txtData. ' - ' .$txtDataSincrona ?></strong><br>
    <i class="fa fa-group"></i> <i class="fa fa-lock"></i>
    <?php echo $txtCapitulo ?> - <?php echo $txtCapituloTit ?>
    <br><?php echo $txtDataSincrona ?>
    <?php
    if (date('Ymd') >= $dtLiberacaoCtSincrono+1 and $urlVideo != '') {
      ?>
      <button class="btn-video" onclick="openVideo('cap<?php echo $ordemAtividade?>')">🎬 Assistir vídeo</button>
      <?php
    }else if (date('Ymd') >= $dtLiberacaoCtSincrono and $urlMeet!='') {
      ?>
      <a href="<?php echo $urlMeet ?>" target="_blank">
        <button class="btn-video">🎬 Google Meet: <?php echo $urlMeet ?></button></a>
      <?php
    } else {
      echo '<i class="fa fa-lock"></i> Aula Virtual - (Aguardando liberação)';
    }
    ?>  
    <!-- slides aqui -->  
  </div>  
  <div class="list-group-item <?php echo $txtClassBloqueado ?>">    
    <strong><?php echo $txtDataPeriodo ?> – (Assíncrona)</strong><br>
    <i class="fa fa-lock"></i>
    <?php if (date('Ymd') < $dtLiberacaoCtAssincrono) { ?>
        <?php echo $txtCapituloTit ?> - Aguardando liberação de conteúdo
    <?php }else{ ?>
        <a href="<?php echo $urlConteudoAssincrono ?>" target="_blank"><?php echo $txtCapituloTit ?> - Problema</a>
    <?php } ?>
  </div>
  <!-- Enviar tarefa -->
  <?php if (date('Ymd') > $dtLiberacaoCtAssincrono) { ?>
  <div class="list-group-item item-liberado">
    <strong>Enviar Tarefa Capítulo <?php echo $ordemAtividade?></strong>
    <ul>
        <li>
        <div class="upload-capitulo" data-capitulo="<?php echo $ordemAtividade?>">
        <?php
        $fws_['numCapitulo'] = $ordemAtividade;
        $objTrabalho = new Trabalho();
        $objTrabalho->setCodInscrito($objInscrito->getCodInscrito());
        $objTrabalho->setCodEvento(COD_EVENTO);
        $objTrabalho->setCodAreaTematica($codAreaTematica);
        $objTrabalho->setTipoDocumento($idCapitulo);
        $objTrabalho->setStatus('ATIVADA');
        $objTrabalho->getByCapituloUltimo();        
        $gerenciarUploadCapitulo = new GerenciarUploadCapituloAC();
        $gerenciarUploadCapitulo->formEnviarCapitulo($objInscrito, $objTrabalho, $fws_, $debug = false);
        ?>
        </div>
        <?php
          if (!empty($objTrabalho->getArquivoGerado())) {
          ?>
            <div class="botaoAbrir" data-cod-area="<?php echo $objTrabalho->getCodAreaTematica()?>">
              <button type="button" onclick="abrirArquivoGerado('<?php echo $objTrabalho->getArquivoGerado() ?>', '<?php echo $objTrabalho->getCodEvento() ?>')">
                📄 Visualizar Arquivo Enviado
              </button>
            </div>
          <?php
          }
        ?>
      </li>
    </ul>
  </div>  
  <?php } ?>

<!-- Próxima Aula Cap 12 -->
<?php
$ordemAtividade = 12;
$codAreaTematica = '112';
$txtCapitulo = 'Capítulo 12';
$idCapitulo = 'Capitulo-12';
$txtCapituloTit = 'Guia de Automonitoramento';
$txtData = '17/06/2025';
$txtDataSincrona = 'Terça-feira · 19h – 21h (Síncrona)';
$txtDataPeriodo  = '11/06 a 16/06/2025';
$dtLiberacaoCtAssincrono = '20250616';
$dtLiberacaoCtSincrono   = '20250612';
$txtClassBloqueado = 'item-bloqueado';
if (date('Ymd') < $dtLiberacaoCtAssincrono) {
  $txtClassBloqueado = 'item-bloqueado';
} else {
  $txtClassBloqueado = 'item-liberado';
}
$urlMeet = '';
$urlVideo = '';
$urlConteudoAssincrono = '/hutec/78/inscricoes/resources/public/00078/cap-12-Automonitoramento-B-Problema.pdf';
?>     
  <div class="list-group-item item-liberado item-capitulo">
    <strong><?php echo $txtCapitulo ?></strong><br>
  </div> 
  <div class="list-group-item <?php echo $txtClassBloqueado ?>">    
    <strong><?php echo $txtData. ' - ' .$txtDataSincrona ?></strong><br>
    <i class="fa fa-group"></i> <i class="fa fa-lock"></i>
    <?php echo $txtCapitulo ?> - <?php echo $txtCapituloTit ?>
    <br><?php echo $txtDataSincrona ?>
    <?php
    if (date('Ymd') >= $dtLiberacaoCtSincrono+1 and $urlVideo != '') {
      ?>
      <button class="btn-video" onclick="openVideo('cap<?php echo $ordemAtividade?>')">🎬 Assistir vídeo</button>
      <?php
    }else if (date('Ymd') >= $dtLiberacaoCtSincrono and $urlMeet!='') {
      ?>
      <a href="<?php echo $urlMeet ?>" target="_blank">
        <button class="btn-video">🎬 Google Meet: <?php echo $urlMeet ?></button></a>
      <?php
    } else {
      echo '<i class="fa fa-lock"></i> Aula Virtual - (Aguardando liberação)';
    }
    ?>  
    <!-- slides aqui -->  
  </div>  
  <div class="list-group-item <?php echo $txtClassBloqueado ?>">    
    <strong><?php echo $txtDataPeriodo ?> – (Assíncrona)</strong><br>
    <i class="fa fa-lock"></i>
    <?php if (date('Ymd') < $dtLiberacaoCtAssincrono) { ?>
        <?php echo $txtCapituloTit ?> - Aguardando liberação de conteúdo
    <?php }else{ ?>
        <a href="<?php echo $urlConteudoAssincrono ?>" target="_blank"><?php echo $txtCapituloTit ?> - Problema</a>
    <?php } ?>
  </div>
  <!-- Enviar tarefa -->
  <?php if (date('Ymd') > $dtLiberacaoCtAssincrono) { ?>
  <div class="list-group-item item-liberado">
    <strong>Enviar Tarefa Capítulo <?php echo $ordemAtividade?></strong>
    <ul>
        <li>
        <div class="upload-capitulo" data-capitulo="<?php echo $ordemAtividade?>">
        <?php
        $fws_['numCapitulo'] = $ordemAtividade;
        $objTrabalho = new Trabalho();
        $objTrabalho->setCodInscrito($objInscrito->getCodInscrito());
        $objTrabalho->setCodEvento(COD_EVENTO);
        $objTrabalho->setCodAreaTematica($codAreaTematica);
        $objTrabalho->setTipoDocumento($idCapitulo);
        $objTrabalho->setStatus('ATIVADA');
        $objTrabalho->getByCapituloUltimo();        
        $gerenciarUploadCapitulo = new GerenciarUploadCapituloAC();
        $gerenciarUploadCapitulo->formEnviarCapitulo($objInscrito, $objTrabalho, $fws_, $debug = false);
        ?>
        </div>
        <?php
          if (!empty($objTrabalho->getArquivoGerado())) {
          ?>
            <div class="botaoAbrir" data-cod-area="<?php echo $objTrabalho->getCodAreaTematica()?>">
              <button type="button" onclick="abrirArquivoGerado('<?php echo $objTrabalho->getArquivoGerado() ?>', '<?php echo $objTrabalho->getCodEvento() ?>')">
                📄 Visualizar Arquivo Enviado
              </button>
            </div>
          <?php
          }
        ?>
      </li>
    </ul>
  </div>  
  <?php } ?>
  
<!-- Próxima Aula Cap 13 -->
<?php
$ordemAtividade = 13;
$codAreaTematica = '113';
$txtCapitulo = 'Capítulo 13';
$idCapitulo = 'Capitulo-13';
$txtCapituloTit = 'Avaliar a Saúde Mental e Física e por meio de Inteligência Artificial';
$txtData = '24/06/2025';
$txtDataSincrona = 'Terça-feira · 19h – 21h (Síncrona)';
$txtDataPeriodo  = '18/06 a 23/06/2025';
$dtLiberacaoCtAssincrono = '20250623';
$dtLiberacaoCtSincrono   = '20250618';
$txtClassBloqueado = 'item-bloqueado';
if (date('Ymd') < $dtLiberacaoCtAssincrono) {
  $txtClassBloqueado = 'item-bloqueado';
} else {
  $txtClassBloqueado = 'item-liberado';
}
$urlMeet = '';
$urlVideo = 'd9ebb062-1791-4ce3-a2eb-e60c0465156b';

$urlConteudoAssincrono = '/hutec/78/inscricoes/resources/public/00078/Capitulo-13-IA formatado.pdf#';
?>     
  <div class="list-group-item item-liberado item-capitulo">
    <strong><?php echo $txtCapitulo ?></strong><br>
  </div> 
  <div class="list-group-item <?php echo $txtClassBloqueado ?>">    
    <strong><?php echo $txtData. ' - ' .$txtDataSincrona ?></strong><br>
    <i class="fa fa-group"></i> <i class="fa fa-lock"></i>
    <?php echo $txtCapitulo ?> - <?php echo $txtCapituloTit ?>
    <br><?php echo $txtDataSincrona ?>
    <?php
    if (date('Ymd') >= $dtLiberacaoCtSincrono+1 and $urlVideo != '') {
      ?>
      <button class="btn-video" onclick="openVideo('cap<?php echo $ordemAtividade?>')">🎬 Assistir vídeo</button>
      <?php
    }else if (date('Ymd') >= $dtLiberacaoCtSincrono and $urlMeet!='') {
      ?>
      <a href="<?php echo $urlMeet ?>" target="_blank">
        <button class="btn-video">🎬 Google Meet: <?php echo $urlMeet ?></button></a>
      <?php
    } else {
      echo '<i class="fa fa-lock"></i> Aula Virtual - (Aguardando liberação)';
    }
    ?>  
    <!-- slides aqui -->  
  </div>  
  <div class="list-group-item <?php echo $txtClassBloqueado ?>">    
    <strong><?php echo $txtDataPeriodo ?> – (Assíncrona)</strong><br>
    <i class="fa fa-lock"></i>
    <?php if (date('Ymd') < $dtLiberacaoCtAssincrono) { ?>
        <?php echo $txtCapituloTit ?> - Aguardando liberação de conteúdo
    <?php }else{ ?>
        <a href="<?php echo $urlConteudoAssincrono ?>" target="_blank"><?php echo $txtCapituloTit ?> - Problema</a>
    <?php } ?>
  </div>
  <!-- Enviar tarefa -->
  <?php if (date('Ymd') > $dtLiberacaoCtAssincrono) { ?>
  <div class="list-group-item item-liberado">
    <strong>Enviar Tarefa Capítulo <?php echo $ordemAtividade?></strong>
    <ul>
        <li>
        <div class="upload-capitulo" data-capitulo="<?php echo $ordemAtividade?>">
        <?php
        $fws_['numCapitulo'] = $ordemAtividade;
        $objTrabalho = new Trabalho();
        $objTrabalho->setCodInscrito($objInscrito->getCodInscrito());
        $objTrabalho->setCodEvento(COD_EVENTO);
        $objTrabalho->setCodAreaTematica($codAreaTematica);
        $objTrabalho->setTipoDocumento($idCapitulo);
        $objTrabalho->setStatus('ATIVADA');
        $objTrabalho->getByCapituloUltimo();        
        $gerenciarUploadCapitulo = new GerenciarUploadCapituloAC();
        $gerenciarUploadCapitulo->formEnviarCapitulo($objInscrito, $objTrabalho, $fws_, $debug = false);
        ?>
        </div>
        <?php
          if (!empty($objTrabalho->getArquivoGerado())) {
          ?>
            <div class="botaoAbrir" data-cod-area="<?php echo $objTrabalho->getCodAreaTematica()?>">
              <button type="button" onclick="abrirArquivoGerado('<?php echo $objTrabalho->getArquivoGerado() ?>', '<?php echo $objTrabalho->getCodEvento() ?>')">
                📄 Visualizar Arquivo Enviado
              </button>
            </div>
          <?php
          }
        ?>
      </li>
    </ul>
  </div>  
  <?php } ?>
<!-- Próxima Aula Fechamento -->
<?php
$ordemAtividade = 14;
$codAreaTematica = '114';
$txtCapitulo = 'Fechamento e Avaliação';
$idCapitulo = 'Capitulo-14';
$txtCapituloTit = 'Fechamento e Avaliação';
$txtData = '01/07/2025';
$txtDataSincrona = 'Terça-feira · 19h – 21h (Síncrona)';
$txtDataPeriodo  = '01/07/2025';
$dtLiberacaoCtAssincrono = '20250701';
$dtLiberacaoCtSincrono   = '20250630';
$txtClassBloqueado = 'item-bloqueado';
if (date('Ymd') < $dtLiberacaoCtAssincrono) {
  $txtClassBloqueado = 'item-bloqueado';
} else {
  $txtClassBloqueado = 'item-liberado';
}
$urlMeet = 'https://meet.google.com/bno-vbmu-bcm';
$urlVideo = '';
$urlConteudoAssincrono = 'https://forms.gle/dvUPFNkCEjuiNE8h7';
?>     
  <div class="list-group-item item-liberado item-capitulo">
    <strong><?php echo $txtCapitulo ?></strong><br>
  </div> 
  <div class="list-group-item <?php echo $txtClassBloqueado ?>">    
    <strong><?php echo $txtData. ' - ' .$txtDataSincrona ?></strong><br>
    <i class="fa fa-group"></i> <i class="fa fa-lock"></i>
    <?php echo $txtCapitulo ?> - <?php echo $txtCapituloTit ?>
    <br><?php echo $txtDataSincrona ?>
    <?php
    if (date('Ymd') >= $dtLiberacaoCtSincrono+1 and $urlVideo != '') {
      ?>
      <button class="btn-video" onclick="openVideo('cap<?php echo $ordemAtividade?>')">🎬 Assistir vídeo</button>
      <?php
    }else if (date('Ymd') >= $dtLiberacaoCtSincrono and $urlMeet!='') {
      ?>
      <a href="<?php echo $urlMeet ?>" target="_blank">
        <button class="btn-video">🎬 Google Meet: <?php echo $urlMeet ?></button></a>
      <?php
    } else {
      echo '<i class="fa fa-lock"></i> Aula Virtual - (Aguardando liberação)';
    }
    ?>  
    <!-- slides aqui -->  
  </div>  
  <div class="list-group-item <?php echo $txtClassBloqueado ?>">    
    <strong><?php echo $txtDataPeriodo ?> – (Assíncrona)</strong><br>
    <i class="fa fa-lock"></i>
    <?php if (date('Ymd') < $dtLiberacaoCtAssincrono) { ?>
        <?php echo $txtCapituloTit ?> - Aguardando liberação de conteúdo
    <?php }else{ ?>
        <a href="<?php echo $urlConteudoAssincrono ?>" target="_blank">Formulário de Avaliação</a>
    <?php } ?>
  </div>
  <!-- Enviar tarefa -->
  <?php if (date('Ymd') > $dtLiberacaoCtAssincrono) { ?>
  <div class="list-group-item item-liberado">
    <strong>Enviar Tarefa Capítulo <?php echo $ordemAtividade?></strong>
    <ul>
        <li>
        <div class="upload-capitulo" data-capitulo="<?php echo $ordemAtividade?>">
        <?php
        $fws_['numCapitulo'] = $ordemAtividade;
        $objTrabalho = new Trabalho();
        $objTrabalho->setCodInscrito($objInscrito->getCodInscrito());
        $objTrabalho->setCodEvento(COD_EVENTO);
        $objTrabalho->setCodAreaTematica($codAreaTematica);
        $objTrabalho->setTipoDocumento($idCapitulo);
        $objTrabalho->setStatus('ATIVADA');
        $objTrabalho->getByCapituloUltimo();        
        $gerenciarUploadCapitulo = new GerenciarUploadCapituloAC();
        $gerenciarUploadCapitulo->formEnviarCapitulo($objInscrito, $objTrabalho, $fws_, $debug = false);
        ?>
        </div>
        <?php
          if (!empty($objTrabalho->getArquivoGerado())) {
          ?>
            <div class="botaoAbrir" data-cod-area="<?php echo $objTrabalho->getCodAreaTematica()?>">
              <button type="button" onclick="abrirArquivoGerado('<?php echo $objTrabalho->getArquivoGerado() ?>', '<?php echo $objTrabalho->getCodEvento() ?>')">
                📄 Visualizar Arquivo Enviado
              </button>
            </div>
          <?php
          }
        ?>
      </li>
    </ul>
  </div>  
  <?php } ?>  
</div>


<!-- Modal -->
<div class="modal" id="videoModal">
  <div class="modal-content">
    <button class="close-modal" onclick="closeVideo()">×</button>
    <iframe id="videoFrame"
            title="Vídeo do capítulo"
            sandbox="allow-same-origin allow-scripts allow-popups"
            allowfullscreen>
    </iframe>
  </div>
</div>

<script>
  function openVideo(capitulo) {
    const frame = document.getElementById('videoFrame');
    const modal = document.getElementById('videoModal');

    const links = {
      cap1: "https://inscritec-videos.wz5mth.easypanel.host/videos/embed/fc13df84-ff12-40d6-9e90-5ba42ce0579e?warningTitle=0&peertubeLink=0",
      cap2: "https://inscritec-videos.wz5mth.easypanel.host/videos/embed/4a6a5574-e348-41c9-b22f-41d0d4c378f8?warningTitle=0&peertubeLink=0",
      cap3: "https://inscritec-videos.wz5mth.easypanel.host/videos/embed/bf267154-4d22-4405-9e8f-7a67c67c57ef?warningTitle=0&peertubeLink=0",
      cap4: "https://inscritec-videos.wz5mth.easypanel.host/videos/embed/9caeef60-d15e-44b2-ae9b-4cb1fc8b376d?warningTitle=0&peertubeLink=0",
      cap5: "https://inscritec-videos.wz5mth.easypanel.host/videos/embed/bc18bc4d-2bd1-4f00-9fbb-dff54868d7af?warningTitle=0&peertubeLink=0",
      cap6: "https://inscritec-videos.wz5mth.easypanel.host/videos/embed/7c1123f8-3f2f-4f4b-a247-70a9decd5cc3?warningTitle=0&peertubeLink=0",
      cap7: "https://inscritec-videos.wz5mth.easypanel.host/videos/embed/4ee1554e-aa44-4466-9cc5-74cd5d949603?warningTitle=0&peertubeLink=0",
      cap8: "https://inscritec-videos.wz5mth.easypanel.host/videos/embed/4a966115-1b7b-4f6b-bce2-209a5bdaccda?warningTitle=0&peertubeLink=0",
      cap9: "https://inscritec-videos.wz5mth.easypanel.host/videos/embed/476047e1-83b8-4ba4-9170-48787563b8dc?warningTitle=0&peertubeLink=0",
      cap10:"https://inscritec-videos.wz5mth.easypanel.host/videos/embed/2c4c4779-da50-4cf3-b786-6797435ff6a1?warningTitle=0&peertubeLink=0",      
      cap11:"https://inscritec-videos.wz5mth.easypanel.host/videos/embed/03156644-8141-4dd0-aa79-4c39fd274d00?warningTitle=0&peertubeLink=0",
      cap12:"https://inscritec-videos.wz5mth.easypanel.host/videos/embed/?warningTitle=0&peertubeLink=0",
      cap13:"https://inscritec-videos.wz5mth.easypanel.host/videos/embed/d9ebb062-1791-4ce3-a2eb-e60c0465156b?warningTitle=0&peertubeLink=0"
    };
    frame.src = links[capitulo];
    modal.style.display = 'flex';
  }

  function closeVideo() {
    const modal = document.getElementById('videoModal');
    const frame = document.getElementById('videoFrame');
    frame.src = "";
    modal.style.display = 'none';
  }

  document.getElementById('videoModal').addEventListener('click', function(e) {
    // Verifica se o clique foi exatamente no fundo da modal, e não dentro do conteúdo
    if (e.target.id === 'videoModal') {
      // Apenas ignora o clique. Não chama closeVideo().
      // Assim não fecha o modal por engano
    }
  });

</script>

</body>
</html>

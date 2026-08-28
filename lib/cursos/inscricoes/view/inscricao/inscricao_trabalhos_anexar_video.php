<?php
if($apresentacaoAnexada == true){
$objAnexarVideo = new ArquivoVideo();
$objAnexarVideo->setCodTrabalho($cod_trabalho);
$txtBotaoAnexarVideo = "Enviar";
/*
<span style="font-size: 14px;color: #000">
    <br><a href="http://mostraparanaensesaude.org.br/normas-para-apresentacao-de-trabalho/" target="_blank">
        Normas e orientações para o preparo do vídeo.</a>
<span>
*/
?>
<span style="font-size: 19px;font-weight: bold;color:#000">
    <br>Arquivo do vídeo:
</span>
<a href='<?php
echo "https://api.whatsapp.com/send?phone=5543996967790"
. "&text=Olá, meu nome é ". textoMaiusculoMinusculo($nome_apelido).". "
. "Estou com dificuldades para anexar o video de meu trabalho: ".$cod_trabalho.""
. " ";
?>'>(Clique para contato e ajuda no envio de vídeo)</a>
<?php
try {
    $objAnexarVideo->carregarListaArquivoVideoBy('cod_trabalho');
    $txtBotaoAnexarVideo = "";
    foreach ($objAnexarVideo->getListaArquivoVideo() as $objListaAnexo) {
        if (file_exists(WB_PATH_LOG_VIDEO.$objAnexarVideo->getArquivoGerado())){
            echo "<br>Arquivo anexado: <a href=\"javascript:void(0);\" onclick=\"visualizarVideo('".$objAnexarVideo->getArquivoGerado()."');\">".$objAnexarVideo->getArquivoGerado()."</a>";
            $txtBotaoAnexarVideo = "Substituir";
        }else{
            echo "<br><font color='red'>Arquivo ".$objAnexarVideo->getArquivoGerado()." não encontrado, entre em contato com a administração.</font>";
        }
    }
    if(count($objAnexarVideo->getListaArquivoVideo())==0){
        echo "<br><font color='red'>Arquivo ".$objAnexarVideo->getArquivoGerado()." não encontrado, entre em contato com a administração.</font>";
    }
} catch (Exception $exc) { }
    if($OP_PERMITIR_ANEXAR_VIDEO){
        $trocarFormulario=true;
        if($trocarFormulario==false and ($_SERVER['REMOTE_ADDR']!='189.76.62.18')and($_SERVER['REMOTE_ADDR']!='200.203.142.25')){
            ?>
            <br>
            <span style="font-size:12px;">
                (Extensões permitidas: .mp4, tamanho máximo 300MB e entre 4 a 5 minutos)
            </span>
            <font color="#000">
            <br>
            <form name="form-<?php echo $cod_trabalho?>" method="POST" action="<?php echo WB_URL_COMMAND?>"  onsubmit="return criticarAnexoVideo();" enctype="multipart/form-data"  >
                <input type="hidden" name="c" value="GerenciarInscricao">
                <input type="hidden" name="m" value="salvarAnexoVideo">
                <input type="hidden" name="cod_trabalho" value="<?php echo $cod_trabalho?>">
                <input type="file" name="arquivo" id="arquivo-video-<?php echo $cod_trabalho?>" value=""
                       placeholder="Inserir arquivo do Video"  class="btn-xs" required="">             
                <input type="submit" class="btn btn-xs btn-primary" 
                role="button" value="<?php echo $txtBotaoAnexarVideo; ?> arquivo referente Vídeo do trabalho: <?php echo $cod_trabalho?>">
            </form>
            </font>
        <?php
        }else{


           $tagName = 'arquivo-video-'.$cod_trabalho;
            ?>
                        <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Upload de Arquivo com Barra de Progresso</title>
                <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
            </head>
            <body>

            <form  class="uploadForm"  id="uploadForm-<?php echo $cod_trabalho?>" name="form-<?php echo $cod_trabalho?>" method="POST" action="<?php echo WB_URL_COMMAND?>"  onsubmit="return criticarAnexoVideo();"  enctype="multipart/form-data">
                <input type="hidden" name="c" value="GerenciarInscricao">
                <input type="hidden" name="m" value="salvarAnexoVideo">
                <input type="hidden" name="cod_trabalho" value="<?php echo $cod_trabalho?>">
                <input type="file" name="arquivo" id="arquivo-video-<?php echo $cod_trabalho?>" value=""
                       placeholder="Inserir arquivo do Video"  class="btn-xs" required="">             
                <input type="submit" class="btn btn-xs btn-primary" 
                role="button" value="<?php echo $txtBotaoAnexarVideo; ?> arquivo referente Vídeo do trabalho: <?php echo $cod_trabalho?>">
            </form>

            <div class="progressBar" id="progressBar-<?php echo $cod_trabalho?>">
                <div class="progress" id="progress-<?php echo $cod_trabalho?>"></div>
                <div id="percentage-<?php echo $cod_trabalho?>" class="percentage">
                    <span id="time-elapsed-<?php echo $cod_trabalho?>" class="time-elapsed"></span>
                    <span id="time-remaining-<?php echo $cod_trabalho?>" class="time-remaining"></span>
                    <span id="progress-text-<?php echo $cod_trabalho?>" class="progress-text"><?php echo $txtBotaoAnexarVideo=="Substituir"? "100% carregado" : "0%" ?></span>
                 </div>
            </div>                    
            <?php if ($imprimirScript==1) {?>
            <script>
            $(document).ready(function() {
                $('.uploadForm').on('submit', function(e) {
                    e.preventDefault();

                    var actionURL = $(this).attr('action');
                    var formData = new FormData(this);
                    var cod_trabalho = $(this).find('input[name="cod_trabalho"]').val();
                    var startTime = new Date().getTime(); // Obter o tempo de início
                    var estimatedTime = 0; // Tempo estimado total (inicialmente 0)
                    
                    $.ajax({
                        xhr: function() {
                            var xhr = new window.XMLHttpRequest();
                            xhr.upload.addEventListener("progress", function(evt) {
                                if (evt.lengthComputable) {
                                    var percentComplete = ((evt.loaded / evt.total) * 100);
                                    $('#progress-' + cod_trabalho).width(percentComplete + '%');
                                    
                                    // Calcular o tempo decorrido em minutos
                                    var currentTime = new Date().getTime();
                                    var elapsedTimeInSeconds = (currentTime - startTime) / 1000;
                                    var elapsedTimeInMinutes = elapsedTimeInSeconds / 60; // Tempo em minutos

                                    // Calcular o tempo restante estimado em minutos
                                    estimatedTime = (100 - percentComplete) * (elapsedTimeInMinutes / percentComplete);

                                    // Exibir a porcentagem de progresso e as medidas de tempo
//                                    $('#percentage-' + cod_trabalho).text(Math.round(percentComplete) + '%');
                                    $('#time-elapsed-' + cod_trabalho).text('Tempo decorrido: ' + elapsedTimeInMinutes.toFixed(1) + ' minutos');
                                    $('#time-remaining-' + cod_trabalho).text('Tempo restante: ' + estimatedTime.toFixed(1) + ' minutos');
                                    $('#progress-text-' + cod_trabalho).text(percentComplete.toFixed(1) + '% ');
                                }
                            }, false);
                            return xhr;
                        },
                        url: actionURL,
                        data: formData,
                        contentType: false,
                        cache: false,
                        processData: false,
                        timeout: 1200000, // Tempo limite em milissegundos (20 minutos)                        
                        type: 'POST',
                        success: function(data) {
                            // alert(data);
                            // Recarregar a página após o envio bem-sucedido
                            location.reload();
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            console.log("Erro: " + textStatus, errorThrown);
                            console.log("Status HTTP: " + jqXHR.status);
                            console.log("Resposta do servidor: " + jqXHR.responseText);
                        }
                    });
                });
            });
            </script>
            <style>
                /* Estilo para cada barra de progresso */
                .progressBar {
                    width: 100%;
                    background-color: #f3f3f3;
                display: column; 
                align-items: center; 
                justify-content: left; 
                position: relative;
                }

                .progress {
                    width: 0%;
                    height: 20px;
                    background-color: #4CAF50;
                    margin-bottom: 0px !important; 
                }

                .percentage {
                    font-weight: bold;
                    text-align: left;
                }

                .time-elapsed,
                .time-remaining,
                .progress-text {
                    display: block;
                }
            </style>
            <?php
                $imprimirScrip=0;
            }
        }
    }//}#catch>
}else{
    echo "<br>Envio de vídeo bloqueado, período encerrado";
}
if($OP_ANEXAR_APRESENTACAO_VIDEO_ENCERRADO){
    ?>
    <font color="#000">
    <br>
    Período de Envio da Apresentação em Vídeo Encerrado.
    </font>
    <?php  
}
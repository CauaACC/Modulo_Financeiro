<?php
class GerenciarUploadAC{
    
    function __construct(){
       $this->codEvento = COD_EVENTO;
       $this->varDiretorio = WB_PATH_LOG_TRABALHO;
       $this->varDiretorioApresentacao = WB_PATH_LOG_APRESENTACAO;
       $this->varDiretorioAvulso = WB_PATH_LOG_AVULSO;
    }
    public function getCodEvento(){
        return str_pad($this->codEvento,'3','0',STR_PAD_LEFT);
    }
    function artigoExiste($id) {
        $filepath = $this->varDiretorio;
        $arquivo = $id . ".doc";
        if (file_exists($filepath . $arquivo)) {
            return true;
        } else {
            return false;
        }
    }
    public function uploadArquivo($fws_, $debug = false) {
        if (move_uploaded_file($fws_['arquivo_origem'], $fws_['arquivo_destino'])) {
            return true;
        } else {
            return false;
        }
    }
    public function abrirWord($fws_, $debug = false) {
        $id=$fws_['cod'];

        $filepath = $this->varDiretorio;
        $arquivo = $id;
//        echo $filepath.$arquivo."<br>";
//        exit();
        if (file_exists($filepath.$arquivo)) {
            header("Content-Description: File Transfer");
            header("Cache-Control: private"); // work-around for IE
            header("Content-Type: application/msword");
            header("Content-Type: application/force-download");
            header("Content-Disposition: attachment; filename=\"".$arquivo."\"");
            header("Content-Length: " . @filesize($filepath.$arquivo));
            header("Pragma: public");
            readfile($filepath.$arquivo) or die();
        } else {
            echo "<script language=\"Javascript\"> alert('Arquivo não existe!'); window.close();</script>";
        }
    }
    public function abrirDocumentoTela($fws_, $debug = false) {
        if (FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_AVALIACAO_INSCRITO, 5, false, false)){
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
    public function abrirDocumentoUnificadoTela($fws_, $debug = false) {
        if (FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_AVALIACAO_INSCRITO, 5, false, false)){
            $id=$fws_['cod'];
            $filepath = $this->varDiretorio;
            if($fws_['codEvento']){
                // echo $fws_['codEvento'];
                // echo __LINE__.'<br>';   
                $codEvento = str_pad($fws_['codEvento'],'3','0',STR_PAD_LEFT);
                $filepath = WB_PATH_LOG_TRABALHO;
                // echo $filepath.'<br>';  
                // echo $id;
                // exit();
            }
            $arquivo = $id;
            if (file_exists($filepath.$arquivo)) {
                // header("Content-Description: File Transfer");
                // header("Cache-Control: private"); // work-around for IE
                // // header("Content-Type: application/msword");
                // header("Content-Disposition: attachment; filename=\"".$arquivo."\"");
                // header("Content-Length: " . @filesize($filepath.$arquivo));
                // header("Pragma: public");
                // readfile($filepath.$arquivo) or die();

                $arquivoFull = $filepath.$arquivo;
                // Detecta MIME real
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime  = finfo_file($finfo, $arquivoFull);
                finfo_close($finfo);

                // Tipos que podem abrir no navegador
                $inlineMimes = [
                    'application/pdf',
                    'image/jpeg',
                    'image/png'
                ];

                $disposition = in_array($mime, $inlineMimes)
                    ? 'inline'
                    : 'attachment';

                header("Content-Description: File Transfer");
                header("Content-Type: $mime");
                header("Content-Disposition: $disposition; filename=\"$arquivo\"");
                header("Content-Length: " . filesize($arquivoFull));
                header("Cache-Control: private");
                header("Pragma: public");

                readfile($arquivoFull);                
            } else {
                echo "<script language=\"Javascript\"> alert('Arquivo não existe!'); window.close();</script>";
            }
        }else{
            echo "<script language=\"Javascript\"> alert('Acesso negado!'); window.close();</script>";
        }
    }
    public function abrirDocumento($fws_, $debug = false) {
        $id=$fws_['cod'];

        $filepath = WB_PATH_LOG_APRESENTACAO;
        $arquivo = $id;
//        echo $filepath.$arquivo."<br>";
//        exit();
        if (file_exists($filepath.$arquivo)) {
            header("Content-Description: File Transfer");
            header("Cache-Control: private"); // work-around for IE
            header("Content-Type: application/force-download");
            header("Content-Disposition: attachment; filename=\"".$arquivo."\"");
            header("Content-Length: " . @filesize($filepath.$arquivo));
            header("Pragma: public");
            readfile($filepath.$arquivo) or die();
        } else {
            echo "<script language=\"Javascript\"> alert('Arquivo não existe!'); window.close();</script>";
        }
    }
    public function abrirAvulso($fws_, $debug = false) {
        $id=$fws_['cod'];

        $filepath = WB_PATH_LOG_AVULSO;
        $arquivo = $id;
//        echo $filepath.$arquivo."<br>";
//        exit();
        if (file_exists($filepath.$arquivo)) {
            header("Content-Description: File Transfer");
            header("Cache-Control: private"); // work-around for IE
            header("Content-Type: application/force-download");
            header("Content-Disposition: attachment; filename=\"".$arquivo."\"");
            header("Content-Length: " . @filesize($filepath.$arquivo));
            header("Pragma: public");
            readfile($filepath.$arquivo) or die();
        } else {
            echo "<script language=\"Javascript\"> alert('Arquivo não existe!'); window.close();</script>";
        }
    }
    public function visualizarDoc($fws_, $debug = false) {
        $id=$fws_['cod'];

        $filepath = WB_PATH_LOG_TRABALHO;
        $arquivo = $id;
        $fullFilePath = $filepath.$arquivo;
//        echo $fullFilePath."<br>";
//        exit();
        if (file_exists($fullFilePath)) {
            detectarHeaderArquivo($fullFilePath);              
            header("Content-Description: File Transfer");
            header("Cache-Control: private"); // work-around for IE
            // header("Content-Type: application/force-download");
            header("Content-Disposition: attachment; filename=\"".$arquivo."\"");
            header("Content-Length: " . @filesize($fullFilePath));
            header("Pragma: public");
            readfile($fullFilePath) or die();
        } else {
            echo "<script language=\"Javascript\"> alert('Arquivo não existe!'); window.close();</script>";
        }
    }    
    public function abrirContrato($fws_, $debug = false) {
        $id=$fws_['cod'];
   
        $filepath = WB_PATH_LOG_CONTRATO;
        $arquivo = $id;
        $fullFilePath = $filepath.$arquivo;
//        echo $fullFilePath."<br>";
//        exit();
        if (file_exists($fullFilePath)) {
            detectarHeaderArquivo($fullFilePath);                  
            header("Content-Description: File Transfer");
            header("Cache-Control: private"); // work-around for IE
            // header("Content-Type: application/force-download");
            header("Content-Disposition: attachment; filename=\"".$arquivo."\"");
            header("Content-Length: " . @filesize($fullFilePath));
            header("Pragma: public");
            readfile($fullFilePath) or die();
        } else {
            echo "<script language=\"Javascript\"> alert('Arquivo não existe!'); window.close();</script>";
        }
    }
    public function abrirBoleto($fws_, $debug = false) {
        $id=$fws_['cod'];

        $filepath = WB_PATH_LOG_BOLETO;
        $arquivo = $id;
//        echo $filepath.$arquivo."<br>";
//        exit();
        if (file_exists($filepath.$arquivo)) {
            header("Content-Description: File Transfer");
            header("Cache-Control: private"); // work-around for IE
            header("Content-Type: application/force-download");
            header("Content-Disposition: attachment; filename=\"".$arquivo."\"");
            header("Content-Length: " . @filesize($filepath.$arquivo));
            header("Pragma: public");
            readfile($filepath.$arquivo) or die();
        } else {
            echo "<script language=\"Javascript\"> alert('Arquivo não existe!'); window.close();</script>";
        }
    }
    public function abrirVideo($fws_, $debug = false) {
        $id=$fws_['cod'];

        $filepath = WB_PATH_LOG_VIDEO;
        $arquivo = $id;
//        echo WB_PATH_LIB.'-';      
//        echo $filepath.$arquivo."<br>";
//        exit();
        if (file_exists($filepath.$arquivo)) {
//            header("Content-Description: File Transfer");
//            header("Cache-Control: private"); // work-around for IE
//            header("Content-Type: application/force-download");
//            header("Content-Disposition: attachment; filename=\"".$arquivo."\"");
//            header("Content-Length: " . @filesize($filepath.$arquivo));
//            header("Pragma: public");
//            readfile($filepath.$arquivo) or die();
            ?>
           <!DOCTYPE html>
<html>
<head>
    <title>Video</title>
    <link href="https://vjs.zencdn.net/7.15.4/video-js.css" rel="stylesheet">
    <script src="https://vjs.zencdn.net/7.15.4/video.min.js"></script>
</head>
<body>
    <video id="video" class="video-js vjs-default-skin" controls preload="auto" width="800" data-setup='{}'>
      <source src="lib/stream/videoStreamPlay.php?codEvento=<?php echo COD_EVENTO; ?>&video=<?php echo $arquivo; ?>&dummy=<?php echo rand(5, 15); ?>" type='video/mp4' />
      <p class="vjs-no-js">To view this video please enable JavaScript, and consider upgrading to a web browser that <a href="https://videojs.com/html5-video-support/" target="_blank">supports HTML5 video</a></p>
    </video>
    <script>
        var player = videojs('video');
    </script>
</body>
</html>

            <?php
        } else {
            echo "<script language=\"Javascript\"> alert('Arquivo não existe!'); window.close();</script>";
        }
    }
    public function abrirPdf($fws_, $debug = false) {
        $id=$fws_['cod'];
        
        $filepath = WB_PATH_LOG_APRESENTACAO;
//        $arquivo = $id.".pdf";
        $arquivo = $id;
//        echo $filepath.$arquivo."<br>";
//        exit();
        if (file_exists($filepath.$arquivo)) {
            $extensao = pathinfo($filepath . $arquivo, PATHINFO_EXTENSION);

            switch (strtolower($extensao)) {
                case 'pdf':
                    header('Content-Type: application/pdf');
                    break;
                case 'jpg':
                case 'jpeg':
                    header('Content-Type: image/jpeg');
                    break;
                case 'png':
                    header('Content-Type: image/png');
                    break;
                case 'gif':
                    header('Content-Type: image/gif');
                    break;
                case 'doc':
                case 'docx':
                    header('Content-Type: application/msword');
                    break;
                case 'ppt':
                    header('Content-Type: application/vnd.ms-powerpoint');
                    break;
                case 'pptx':
                    header('Content-Type: application/vnd.openxmlformats-officedocument.presentationml.presentation');
                    break;                    
                // Adicione mais casos conforme necessário para outras extensões
                default:
                    // header('Content-Type: application/octet-stream');
                    break;
            }            
            header("Content-Description: File Transfer");
            header("Cache-Control: private"); // work-around for IE
            // header("Content-Type: application/pdf");
            // header("Content-Type: application/force-download");
            header("Content-Disposition: attachment; filename=\"".$arquivo."\"");
            header("Content-Length: " . @filesize($filepath.$arquivo));
            header("Pragma: public");
            header("Expires: 0");
            readfile($filepath.$arquivo) or die();
        } else {
            echo "<script language=\"Javascript\"> alert('Arquivo não existe!'); window.close();</script>";
        }
    }
    public function abrirApresentacao($fws_, $debug = false) {
        $id=$fws_['cod'];

        $filepath = WB_PATH_LOG_APRESENTACAO;
        $arquivo = $id;
//        echo $filepath.$arquivo."<br>";
//        exit();
        if (file_exists($filepath.$arquivo)) {
            header("Content-Description: File Transfer");
            header("Cache-Control: private"); // work-around for IE
            header("Content-Type: application/pdf");
            header("Content-Type: application/force-download");
            header("Content-Disposition: attachment; filename=\"".$arquivo."\"");
            header("Content-Length: " . @filesize($filepath.$arquivo));
            header("Pragma: public");
            header("Expires: 0");
            readfile($filepath.$arquivo) or die();
        } else {
            echo "<script language=\"Javascript\"> alert('Arquivo não existe!'); window.close();</script>";
        }
    }
    public function compactar($fileName,$arrayFiles){
//        $diretorio = WB_PATH_LOG_VIDEO;
        $diretorio = 'C:/Users/home/Documents/projetos/apps/inesco/log/eventos/'.$this->getCodEvento().'/video/';
        $textoArquivo = "CD ".dirname(__LINE__)."\n\r";
        $textoArquivo .= "MKDIR ".$fileName."\n\r";
        foreach ($arrayFiles as $file) {
            $textoArquivo .= "COPY ".$file." ".$fileName."\n\r";
        }     
        $textoArquivo .= "\n\r";
        
        $fileLog = $diretorio. "/" . $fileName.'.bat';
        file_put_contents($fileLog, $textoArquivo);
        echo "Arquivo Gerado: ";
        echo $fileName.'.bat';
    }
    public function montarListaPdfPremiados($arrayIndice,$fileName, $arrayFileVideo,$arrayTitulo,$arrayInscrito){
//        $diretorio = WB_PATH_LOG_VIDEO;
        $diretorio = 'C:/Users/home/Documents/projetos/apps/inesco/log/eventos/'.$this->getCodEvento().'/apresentacao/';
            echo __LINE__.'<br>';
        echo "CD ".$diretorio."<br>";
        echo "MKDIR premiados<br>";
        foreach ($arrayFiles as $file) {
            echo "COPY ".$file." premiados<br>";
        }     
    }
    public function montarListagemVideo($arrayIndice, $fileName, $arrayFileVideo,$arrayTitulo,$arrayInscrito){
        $diretorio = 'C:/Users/home/Documents/projetos/apps/inesco/log/eventos/'.$this->getCodEvento().'/video/';
        $textoArquivo ="<html>
<head>
<!DOCTYPE html>
<html>
<head>
    <title>Videos</title>
    <link href=\"css/video-js.css\" rel=\"stylesheet\">
    <script src=\"js/video.js\"></script>
    <link href=\"css/estilo.css\" rel=\"stylesheet\">
</head>
<body>";
        foreach ($arrayIndice as $i) {
            $textoArquivo .= "<h3>";
            $textoArquivo .= textoMaiusculoMinusculo($arrayTitulo[$i])."<br>";
            $textoArquivo .= textoMaiusculoMinusculo($arrayInscrito[$i])."";
            $textoArquivo .= "</h3>";
            $textoArquivo .= '<video id="video" class="video-js vjs-default-skin" controls preload="auto" width="800px"><source src="'.$fileName.'/'.$arrayFileVideo[$i].'" type="video/mp4" /></video>';
            $textoArquivo .= '';

        }  
        $fileLog = $diretorio. "/" . $fileName.'.html';
        file_put_contents($fileLog, $textoArquivo);
        echo "Arquivo Gerado: ";
        echo $fileName.'.html';
    }
}    

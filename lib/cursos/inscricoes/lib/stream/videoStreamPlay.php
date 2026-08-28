<?php
$codEvento = addslashesopctec($_GET['codEvento']);
$video     = addslashesopctec($_GET['video']);
if($codEvento == '' or $video == ''){
    echo "Não foi possível encontrar o video, volte ao sistema e inicie o processo novamente";
    exit();
}
include 'VideoStream.php';
$path = dirname(__FILE__).'/../../../../../../log/eventos/'.str_pad($codEvento, '3', "0", STR_PAD_LEFT).'/video/'.$video;
//echo $path;
$stream = new VideoStream($path);
$stream->start();

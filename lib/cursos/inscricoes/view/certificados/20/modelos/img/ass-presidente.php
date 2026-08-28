<?php
$arq = "../../../../../../../../../log/eventos/010/assinaturas/assinatura-presidente.jpg";
if(file_exists($arq)){
//require_once(dirname(__FILE__).'\..\..\..\..\..\lib\canvas-master\canvas.php');
////$img = new Canvas('assinatura.jpg');
//Canvas::carrega($arq)->grava();
//exit();
    
    
//if (extension_loaded('gd')) {
//echo "<br>GD support is  loaded ";
//}else{
//echo "<br>GD support is NOT loaded ";
//}
//if(function_exists('gd_info')){
//echo "<br>GD function support is available ";
//}else{
//echo "<br>GD function support is NOT available ";
//}
//exit();

// Obter dados do arquivo de imagem
$dados = getimagesize($arq);
//print_r($dados);
//exit();

// Determinar se o tipo de imagem e' suportado
//$tipo = $dados[2];
//if ($tipo) {
//    switch ($tipo) {
//    case 1:
//        $img = imagecreatefromgif($arq);
//        break;
//    case 2:
//        $img = imagecreatefromjpeg($arq);
//        break;
//    case 3:
//        $img = imagecreatefrompng($arq);
//        break;
//    case 4:
//        $img = imagecreatefromwbmp($arq);
//        break;
//    case 5:
//        $img = imagecreatefromxpm($arq);
//        break;
//    default:
//        $conteudo = file_get_contents($arq);
//        $img = imagecreatefromstring($conteudo);
//        break;
//    }
//}
$qualidade = 100; // valor entre 0-100
header('Content-type: '.$dados['mime']);
header("Content-length: ".filesize($arq));
//header("Content-Disposition: attachment; filename=" . basename($arq));
readfile($arq);
exit();

// Output the image
// Abrindo uma imagem PNG
//$img = imagecreatefromjpeg($arq);
//Obtendo o tamanho do arquivo de imagem

//error_reporting(E_ALL ^ E_NOTICE);
//ini_set("display_errors", "On");
//imagegd();
//exit();

//header("Content-type: image/jpeg");
//Load
//$thumb  = imagecreatetruecolor($dados[0],$dados[1]);
//$source = imagecreatefromjpeg($filename);
//Resize
//imagecopyresized($thumb,$source,0,0,0,0,$dados[0],$dados[1],$dados[0],$dados[1]);
//Output
//imagejpeg($thumb);
//if(file_exists($destPathImage)){
//    header("Content-type: image/png");
//    header("Expires: 0");
//    $size = filesize($destPathImage);    
//    header("Content-length: $size");
//    header('filename="assinatura-presidente.png"');
//    readfile($destPathImage);
//}else{
//    echo "Assinatura não encontrada, já estamos contactando o administrador do sistema";
//}
}else{
    echo "Assinatura não encontrada, já estamos contactando o administrador do sistema";
}
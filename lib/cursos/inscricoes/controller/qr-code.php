<?php
require_once("../../../../../../php/Image/QRCode.php");

$options = array(
    "image_type" => "png",
    "module_size" => 3,
    "error_correct" => "H",
    "version" => 4,
    "output_type" => "display"
);

$stringData = "Zaqueu Costeski Crosati\r\nOPCTEC - Técnologia\r\nSite: http://www.opctec.com.br";

$qr = new Image_QRCode();
$qrcode = $qr->makeCode($stringData, $options);
?>
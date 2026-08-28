<?php
$_GET['figura'] = true;
#require_once(dirname(__FILE__)."/../../include/config.php");
$id = addslashes($_GET['id']);
$string_qr_code = $_GET['string_qr_code'];
if($id == '' && $string_qr_code == ''){
    echo "Id não informado";
    exit();
}
require_once(dirname(__FILE__) . "/../../lib/Image_QRCode/Image_QRCode-0.1.3/Image/QRCode.php");

	       $options = array(
	        "image_type" => "png",
	        "module_size" => 5,
	        "error_correct" => "M",
	        "version" => 5,
	        "output_type" => "display"
	       );
$stringData = $string_qr_code ?? "Validação de conteúdo: https://www.opctec.net.br/opctec/atividades/certificados/validar/?id=".$id;

/*
$stringData = $string_qr_code ?? "Validação de conteúdo: https://www.opctec.net.br/". WB_GRUPO."/".WB_PROJETO."/certificados/validar?id=".$id;
*/
$qr = new Image_QRCode();
$qr->makeCode($stringData, $options);

//$arr = array(
//            "output_type" => "return"
//        );
//$gd = $qr->makeCode("http://www.example.com/php/", $arr);
//$this->assertInternalType("resource", $gd);

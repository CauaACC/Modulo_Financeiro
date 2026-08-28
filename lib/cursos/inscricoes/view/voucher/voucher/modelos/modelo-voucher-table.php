<style type="text/css">
body {
    font-family: 'Helvetica', sans-serif;
    /* width: 9.3cm;
    height: 3.3cm;  */
    /* margin: 0.5cm; */
    
}
page[size="voucher"] {  
    /* font-family: 'Helvetica', sans-serif; */
    width: 9.3cm;
    height: 3.3cm; 
    margin: 0.5cm;
}
table
{
    padding: 0;
    /*border: solid 1mm LawnGreen;*/
    font-size: 12pt;
    background: #FFFFFF;
    text-align: center;
    vertical-align: middle;
}

td
{
    width: 360px;
    padding: 0mm;
    border: solid 0mm black;
}

.codVoucher {
/*-webkit-transform: rotate(-90deg); 
-moz-transform: rotate(-90deg);	
rotate: 90;*/
font-size: 27px;
position: absolute;
float: left;
width: 150px;
height: 30px;
margin-top: 100px;
margin-left: 200px;
border: solid 0px black;
}
.txtVoucher {
/*-webkit-transform: rotate(-90deg); 
-moz-transform: rotate(-90deg);	*/
font-size: 12px;
position: absolute;
float: left;
width: 145px;
height: 20px;
margin-top: 130px;
margin-left: 200px;
border: solid 0px black;
}
.desVoucher {
/*-webkit-transform: rotate(-90deg); 
-moz-transform: rotate(-90deg);	*/
font-size: 13px;
position: absolute;
float: left;
width: 160px;
height: 20px;
margin-top: 160px;
margin-left: 195px;
border: solid 0px black;
color: #00afdb;
font-weight: bold;
}
.seqVoucher {
font-size: 11.5px;
line-height: normal;
position: absolute;
float: left;
width: 145px;
height: 10px;
border: solid 0px black;
z-index: 0;
margin-top: 100px;
/* margin-left: 70px; */
}
.validadeVoucher {
font-size: 11px;
line-height: normal;
position: absolute;
float: left;
width: 145px;
height: 10px;
border: solid 0px black;
z-index: 0;
margin-top: 135px;
/* margin-left: 70px; */
}
.dataValidadeVoucher {
font-size: 11px;
line-height: normal;
position: absolute;
float: left;
width: 145px;
height: 10px;
border: solid 0px black;
z-index: 0;
margin-top: 150px;
/* margin-left: 70px; */
}
.urlSite {
font-size: 9px;
line-height: normal;
position: absolute;
float: left;
width: 180px;
height: 10px;
border: solid 0px black;
z-index: 0;
margin-top: 178px;
margin-left: 10px;
}
.qrCodeVoucher {
position: absolute;
margin-top: 117px;
margin-left: -155px;
width: 60px;
height: 60px;
z-index: 1;
}
.siglaEvento {
font-size:16px;
font-weight: bold;
color: #333;
position: absolute;
float: left;
margin-top: 30px;
margin-left: 195px;
width: 160px;
text-align: left;
z-index: 1;
}

</style>
   <?php
    if($_GET['intervalo']!=''){
        $intervalo = $_GET['intervalo'];
    }
    if($_GET['cod_voucher_evento']!=''){
        $fws_['codVoucherEvento'] = $_GET['cod_voucher_evento'];
    }

    switch ($intervalo) {
//    case 1:
//        $intervalo = '1,50';
//            break;
//    case 2:
//        $intervalo = '51,100';
//            break;
//    case 3:
//        $intervalo = '101,150';
//            break;
//    case 4:
//        $intervalo = '151,200';
//            break;
    default:
        $intervalo = '';
        break;
}
//$objVoucher = new Voucher();
//$objVoucher->setCodVoucherEvento($fws_['codVoucherEvento']);
//$objVoucher->carregarListaVouchersByCodVoucherEVento($debug,false);
//$objVoucher->carregarListaVouchersByCodEvento();

$logoFile = "etiqueta_10x56mm_".str_pad($objEv->getCodEvento(), 3, '0', STR_PAD_LEFT).".jpg";
$logoEtiqueta = dirname(__FILE__) . "/../../../../resources/image/".$logoFile;

$logoFileGestor = "etiqueta_10x56mm_".$objEv->getGestor().".jpg";
$logoEtiquetaGestor = dirname(__FILE__) . "/../../../../resources/image/".$logoFileGestor;

if(file_exists($logoEtiqueta)){
    $logoEtiqueta = "resources/image/".$logoFile;
}else if(file_exists($logoEtiquetaGestor)){
    $logoEtiqueta = "resources/image/".$logoFileGestor;
}else{
    $logoEtiqueta = "resources/image/etiqueta_10x56mm_9999.jpg";
}

// Gerar QR Code com URL de inscrição do evento
$qrCodeBase64 = '';
$urlInscricao = defined('WB_URL_SITE') ? WB_URL_SITE . '/index.php' : URL_OFICIAL;
if ($urlInscricao != '') {
    require_once(dirname(__FILE__) . '/../../../../lib/Image_QRCode/Image_QRCode-0.1.3/Image/QRCode.php');
    $qrOptions = array(
        "image_type" => "png",
        "module_size" => 3,
        "error_correct" => "M",
        "version" => 3,
        "output_type" => "return"
    );
    $qr = new \Image_QRCode();
    $gd = $qr->makeCode($urlInscricao, $qrOptions);
    ob_start();
    imagepng($gd);
    $qrImageData = ob_get_clean();
    imagedestroy($gd);
    $qrCodeBase64 = 'data:image/png;base64,' . base64_encode($qrImageData);
}
?>
<page size="voucher" layout="portrait">
    <table cellspacing="0" cellpadding="0">
        <?php
        $i=0;
        foreach ($objVoucher->getListaVouchers() as $objL) {
            if($i==0){
                echo "<tr>";
            }
            $descricao = $objL->getDescricao();
            // if($objL->getDescricao()==''){
            //     $descricao = "VALE 01 INSCRIÇÃO<br>CATEGORIA INSCRITO";
            // }
//            $descricao = $objL->getDescricao();
//            if($objL->getDescricao()==''){
//                $descricao = "VALE 01 INSCRIÇÃO<br>CATEGORIA PREMIUM";
//            }
        ?>
        <!--<tr>-->
            <td class="div" style="border:1px dashed;">
                <div style="position: relative;border:0px;min-height:195px;">
                <img src="<?php echo $logoEtiqueta ?>" style="width:200px;margin:0px;align:left;float:left;border:0;">
                <div class="siglaEvento"><?php echo defined('ABREV_EVENTO') ? ABREV_EVENTO : '' ?></div>
                <div class="txtVoucher"><?php echo htmlentities('Informe o código acima no final de sua inscrição') ?></div>
                <div class="seqVoucher"><?php echo $objL->getPatrocinador()."<br>".lpad($objL->getSequenciaInterna(),4,0); ?></div>
                <div class="codVoucher"><?php echo $objL->getCodVoucher(); ?></div>
                <div class="desVoucher"><?php echo $descricao; ?></div>
                <div class="validadeVoucher"><?php echo htmlentities('Válido até:') ?></div>
                <div class="dataValidadeVoucher"><?php echo htmlentities(formatDateView($objL->getDataVencimento())) ?></div>
                <?php if ($qrCodeBase64 != '') { ?>
                <img class="qrCodeVoucher" src="<?php echo $qrCodeBase64 ?>" alt="QR Code Inscrição">
                <?php } ?>
                <div class="urlSite"><?php echo URL_OFICIAL ?></div>
                
                </div>
            </td>
        <?php
        $i=$i+1;
        if($i==2){
            echo "</tr>";
            $i=0;
        }
    }
    if(!(count($objVoucher->getListaVouchers())%2==0)){
        echo "</tr>";
    }
    ?>
    </table>
</page>
</body>
</html>

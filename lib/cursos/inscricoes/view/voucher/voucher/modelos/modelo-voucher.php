<?php
if (mb_detect_encoding($fws_['texto_recibo'] . 'x', 'UTF-8, ISO-8859-1') == 'UTF-8') {
    $fws_['texto_voucher'] = utf8_decode($fws_['texto_voucher']);
}
?>
<style>
body {
  background: rgb(204,204,204); 
}
page {
  background: white;
  display: block;
  margin: 0 auto;
  margin-bottom: 0.5cm;
  box-shadow: 0 0 0.5cm rgba(0,0,0,0.5);
}
page[size="voucher"] {  
  width: 9.3cm;
  height: 3.3cm; 
}
page[size="etiqueta"] {  
  width: 9.3cm;
  height: 3.3cm; 
}
page[size="A4"] {  
  width: 21cm;
  height: 29.7cm; 
}
page[size="A4"][layout="portrait"] {
  width: 29.7cm;
  height: 21cm;  
}
page[size="A3"] {
  width: 29.7cm;
  height: 42cm;
}
page[size="A3"][layout="portrait"] {
  width: 42cm;
  height: 29.7cm;  
}
page[size="A5"] {
  width: 14.8cm;
  height: 21cm;
}
page[size="A5"][layout="portrait"] {
  width: 21cm;
  height: 14.8cm;  
}
@media print {
  body, page {
    margin: 0;
    box-shadow: 0;
  }
}
.codVoucher {
-webkit-transform: rotate(-90deg); 
-moz-transform: rotate(-90deg);	
font-size: 30px;
position: absolute;
float: left;
margin-top: 70px;
margin-left: 259px;
}
.txtVoucher {
-webkit-transform: rotate(-90deg); 
-moz-transform: rotate(-90deg);	
font-size: 15px;
position: absolute;
float: left;
margin-top: 70px;
margin-left: 230px;
}
.validadeVoucher {
font-size: 12px;
line-height: normal;
position: absolute;
float: left;
margin-top: 146px;
margin-left: 175px;
}
</style>
<!--<page size="voucher" layout="portrait">-->

<!--<div style="PAGE-BREAK-AFTER: always;line-height: 5px;margin: 15px;">-->
<!--<div style="position: absolute; width: 600px;height: 40px;  margin-top: 5px; margin-left: 5px; text-align-last: center">-->
<div style="position: relative; width: 842px;height: 1191px;  margin-top: 5px; margin-left: 5px; padding-bottom: 15px; text-align-last: center">
    <!--<img  style="position:absolute; margin-left:25px; margin-top:550px;" border="1" width="1020"-->
    <?php
    $objV = new Voucher();
    $intervalo = '0,10';
    $intervalo = '11,20';
    $intervalo = '21,30';
    $intervalo = '31,40';
    $intervalo = '41,50';
    $intervalo = '51,60';
    $intervalo = '61,70';
    $intervalo = '71,80';
    $intervalo = '81,90';
    $intervalo = '91,100';
    $intervalo = '101,110';
    $intervalo = '111,120';
    $intervalo = '121,130';
    $intervalo = '131,140';
    $intervalo = '141,150';
    $intervalo = '151,160';
    $intervalo = '161,170';
    $intervalo = '171,180';
    $intervalo = '181,190';
    $intervalo = '191,200';
    $intervalo = '201,210';
    $intervalo = '211,220';
    $intervalo = '221,230';
    $intervalo = '231,240';
    $intervalo = '241,250';
    $intervalo = '251,260';
    $intervalo = '261,270';
    $intervalo = '271,280';
    $intervalo = '281,290';
    $intervalo = '291,300';
    $intervalo = '435,444';

    if($_GET['intervalo']!=''){
        $intervalo = $_GET['intervalo'];
    }
    $objV->carregarListaVouchers(false,$intervalo);
    foreach ($objV->getListaVouchers() as $objL) {
        ?>
        <div style="margin:0px;float:left;border:1;">
            <div class="txtVoucher"><?php echo htmlentities('Código do Voucher') ?></div>
            <div class="codVoucher"><?php echo $objL->getCodVoucher(); ?></div>
            <div class="validadeVoucher"><?php echo htmlentities('Válido até: 10/06/2019') ?></div>
            <img src="view/voucher/voucher/modelos/img/etiqueta_10x56mm.jpg"
                 style="width:365px;margin:0px;align:left;border:0;">
        </div>
        <?php
        $i++;
    }
    ?>
</div>
<!--</div>-->
<!--</page>-->    
<?php
//echo $i;
?>
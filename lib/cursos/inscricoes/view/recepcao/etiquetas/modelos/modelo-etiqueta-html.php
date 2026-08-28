<?php
if (mb_detect_encoding($fws_['texto_recibo'] . 'x', 'UTF-8, ISO-8859-1') == 'UTF-8') {
    $fws_['texto_etiqueta'] = utf8_decode($fws_['texto_etiqueta']);
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
page[size="etiqueta"] {  
  width: 9.5cm;
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
</style>
<page size="etiqueta" layout="portrait">
 <?php echo $fws_['texto_etiqueta'];?>
</page>
<?php
if (mb_detect_encoding($fws_['texto_recibo'] . 'x', 'UTF-8, ISO-8859-1') == 'UTF-8') {
    $fws_['texto_etiqueta'] = utf8_decode($fws_['texto_etiqueta']);
}
?>
<div style="PAGE-BREAK-AFTER: always;line-height: 5px;margin: 5px 5px 5px 15px;">
<div style="position: absolute; width: 290px;height: 40px;  margin: 5px 5px 5px 15px; text-align-last: center">
    <?php echo $fws_['texto_etiqueta'];?>
</div>
</div>
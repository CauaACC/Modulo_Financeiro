<?php
$_GET['figura']=true;
try {
    FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_CONTROLE_VOUCHER, 5, false, true);
    $objCrt = new GerenciarVoucherAC();
    $objCrt->gerarVoucher($fws_);
} catch (Exception $exc) {
    echo $exc->getMessage();
}

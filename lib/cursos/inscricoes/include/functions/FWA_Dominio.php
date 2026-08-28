<?php

Function FWA_Dominio() {
    return $_SERVER['SERVER_NAME'];

    Switch ($_SERVER['HTTP_HOST']) {
        Case "www.opctec.com.br":
        Case "opctec.com.br":
//            $FWA_Dominio = "www.opctec.com.br";
//            return $FWA_Dominio;
//            break;
        Case "www.opctec.net.br":
        Case "opctec.net.br":
            $FWA_Dominio = "opctec.net.br";
            return $FWA_Dominio;
            break;
        Default:
            return $_SERVER['SERVER_NAME'];
            break;
    }
}


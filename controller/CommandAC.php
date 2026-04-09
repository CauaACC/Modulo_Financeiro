<?php
require_once(dirname(__FILE__) . "/../include/config.php");

try {
    if (!$fws_['c']) {
        $fws_['c'] = addslashesopctec($fwsGet_['c']);
        $fws_['m'] = addslashesopctec($fwsGet_['m']);
    }

    if ($fws_['c'] == '' or $fws_['m'] == '') {
        throw new Exception("Uma tentativa de acesso direto foi detectada...");
    }

    class Command {
        public function __construct($fws_) {
            $classe = $fws_['c'];
            $metodo = $fws_['m'];
            require_once dirname(__FILE__) . '/' . $classe . 'AC.php';
            $classe .= 'AC';
            $obj = new $classe;
            $obj->$metodo($fws_);
        }
    }
    $command = new Command($fws_);

} catch (Exception $exc) {
    echo $exc->getMessage() . "<hr>";
}

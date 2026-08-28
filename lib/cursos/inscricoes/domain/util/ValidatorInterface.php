<?php

interface ValidatorInterface {

    public function validarObjSalvar($debug = false);

    public function validarObjExcluir($debug = false);

    public function validarObj($debug = false);
}

?>
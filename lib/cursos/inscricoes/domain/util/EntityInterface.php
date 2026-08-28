<?php

interface EntityInterface {

    public function salvar($debug = false);

    public function excluir($debug = false);

    public function carregaObjById($debug = false);

    public function verificaExisteById($debug = false);
}

?>
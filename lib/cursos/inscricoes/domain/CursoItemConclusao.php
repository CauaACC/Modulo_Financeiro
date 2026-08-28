<?php

class CursoItemConclusao extends CursoItemConclusaoVO {

    private $objDAO;

    function __construct() {
        $this->objDAO = new CursoItemConclusaoDAO();
    }

    public function registrar($debug = false) {
        $this->validatorRegistrar();
        if (!$this->getDataConclusao()) $this->setDataConclusao(date('Y-m-d H:i:s'));
        // upsert: se já existe, ignora (UNIQUE no banco protege)
        return $this->objDAO->insertOrIgnore($this, $debug);
    }

    public function jaConcluiu($codCursoItem, $codInscrito, $debug = false) {
        return $this->objDAO->jaConcluiu($codCursoItem, $codInscrito, $debug);
    }

    public function carregarConclusoesDoInscrito($codInscrito, $codEvento = null, $debug = false) {
        return $this->objDAO->carregarConclusoesDoInscrito($codInscrito, $codEvento, $debug);
    }

    public function contarConclusoesPorItem($codCursoItem, $debug = false) {
        return $this->objDAO->contarConclusoesPorItem($codCursoItem, $debug);
    }
}

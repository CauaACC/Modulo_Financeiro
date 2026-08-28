<?php

class CursoItemTarefa extends CursoItemTarefaVO {

    private $objDAO;

    function __construct() {
        $this->objDAO = new CursoItemTarefaDAO();
    }

    public function getPorItemInscrito($debug = false) {
        return $this->objDAO->getPorItemInscrito($this, $debug);
    }

    public function salvar($debug = false) {
        $this->validatorSalvar();
        $this->setDatEnvio(date('Y-m-d H:i:s'));
        $this->setIp($_SERVER['REMOTE_ADDR']);
        $existente = $this->objDAO->getPorItemInscrito($this, $debug);
        if ($existente) {
            $this->setCodTarefa($existente['cod_tarefa']);
            $this->setVersao((int) $existente['versao'] + 1);
            return $this->objDAO->update($this, $debug);
        }
        $this->setVersao(1);
        return $this->objDAO->insert($this, $debug);
    }
}

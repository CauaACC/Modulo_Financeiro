<?php

class CursoItemComentario extends CursoItemComentarioVO {

    private $objDAO;

    function __construct() {
        $this->objDAO = new CursoItemComentarioDAO();
    }

    public function salvar($debug = false) {
        $this->validatorSalvar();
        if (!$this->getDatIncl()) $this->setDatIncl(date('Y-m-d H:i:s'));
        if (!$this->getStatus()) $this->setStatus('ATIVADO');
        if (!$this->getCodComentario()) {
            return $this->objDAO->insert($this, $debug);
        }
        return $this->objDAO->update($this, $debug);
    }

    public function carregarPorItem($codCursoItem, $debug = false) {
        return $this->objDAO->carregarPorItem($codCursoItem, $debug);
    }

    public function carregarById($codComentario, $debug = false) {
        return $this->objDAO->getById($codComentario, $debug);
    }

    public function excluir($debug = false) {
        if (!$this->getCodComentario()) throw new Exception("Comentário não informado.");
        return $this->objDAO->softDelete($this, $debug);
    }
}

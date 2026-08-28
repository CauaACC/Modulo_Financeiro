<?php

class FiltroSalvo extends FiltroSalvoVO {
    private $objDAO;

    function __construct() {
        parent::__construct();
        $this->objDAO = new FiltroSalvoDAO();
    }

    public function salvar($debug = false) {
        if (!$this->getNomFiltro()) {
            throw new Exception("Nome do filtro é obrigatório.");
        }
        if (!$this->getCodUsuario()) {
            throw new Exception("Usuário não identificado.");
        }
        if (!$this->getCodEvento()) {
            throw new Exception("Evento não identificado.");
        }
        if ($this->objDAO->insert($this, $debug)) {
            throw new OKException("Filtro salvo com sucesso.");
        } else {
            throw new Exception("Não foi possível salvar o filtro.");
        }
    }

    public function carregar($debug = false) {
        if (!$this->getCodFiltroSalvo()) {
            throw new Exception("Código do filtro não informado.");
        }
        if (!$this->objDAO->getById($this, $debug)) {
            throw new Exception("Filtro não encontrado.");
        }
    }

    public function listar($debug = false) {
        $this->objDAO->getListByUsuarioEvento($this, $debug);
    }

    public function excluir($debug = false) {
        if (!$this->getCodFiltroSalvo()) {
            throw new Exception("Código do filtro não informado.");
        }
        if ($this->objDAO->delete($this, $debug)) {
            throw new OKException("Filtro excluído com sucesso.");
        } else {
            throw new Exception("Não foi possível excluir o filtro.");
        }
    }
}

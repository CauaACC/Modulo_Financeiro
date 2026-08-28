<?php

class LogAcesso extends LogAcessoVO {

    private $objDAO;

    function __construct() {
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new LogAcessoDAO();
    }

    public function LogAcessoExiste() {
        if (!$this->getCodLogAcesso()) {
            throw new Exception('Código da Código do Log de Acesso inválido ou não informado.');
        }
        if (!$this->objDAO->getByIdExiste($this)) {
            throw new Exception("Nenhum Log de Acesso foi encontrada para o código informado: " . $this->getCodLogAcesso() . ".");
            //throw new Exception("Id informado para deleção não existe");
        }
    }

    public function novo($debug = false) {
        
    }

    public function salvar($debug = false) {
        
        $this->validatorObj();
        if (is_null($this->getCodLogAcesso()) or $this->getCodLogAcesso() == "") {
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Log de Acesso salva com sucesso.");
            } else {
                throw new Exception("Novo Log de Acesso não foi salva.");
            }
        } else {
            $this->LogAcessoExiste($debug);

            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Log de Acesso foi atualizada com sucesso.");
            } else {
                throw new Exception("Log de Acesso atual não foi atualizada.");
            }
        }
    }

    public function excluir($debug = false) {
        $this->validatorEcluir();
        if ($this->objDAO->delete($this, $debug)) {
            throw new OKException("Log de Acesso cód:" . $this->getCodLogAcesso() . " foi excluída com sucesso.");
        } else {
            throw new Exception("Log de Acesso cód:" . $this->getCodLogAcesso() . " não foi excluída.");
        }
    }

    public function getLogAcessoById($debug = false) {
        $this->objDAO->getById($this, $debug);
    }

    public function getListaLogAcessoByIdEvento($debug = false) {
        if (!$this->getCodEvento()) {
            throw new Exception("Id do evento não informado.");
        }
        $this->objDAO->carregarListaLogAcessosByIdEvento($this, $debug);
        if ($this->getListaLogAcessos()) {
            return $this->getListaLogAcessos();
        } else {
            throw new Exception("Evento não possui Log de Acesso cadastrado.");
        }
    }
    public function carregarListaLogAcessoByLogin($debug = false) {
        $this->objDAO->carregarListaLogAcessoByLogin($this, $debug);
        if ($this->getListaLogAcesso()) {
        } else {
            throw new Exception("Login não possui Log de Acesso cadastrado.");
        }
    }
    public function carregarUltimoAcessoByLogin($debug = false) {
        $this->objDAO->carregarUltimoAcessoByLogin($this, $debug);
    }

    public function getEvento() {
        $obj = new InscEvento();
        $obj->setCodEvento($this->getCodEvento());
        $obj->getEventoById();
        return $obj;
    }

}

?>
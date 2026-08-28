<?php

class InscritoOficina extends InscritoOficinaVO {

    private $objDAO;

    function __construct() {
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new InscritoOficinaDAO();
    }

    public function inscritoOficinaExiste($debug = false) {
        if (!$this->getCodInscrito()) {
            throw new Exception('Código do Inscrito inválido ou não informado: '.$this->getCodInscrito());
        }
        if (!$this->getCodOficina()) {
            throw new Exception('Código do Opção inválido ou não informado: '.$this->getCodOficina());
        }
        return $this->objDAO->getByIdExiste($this, $debug);
    }
    public function getInscritoOficinaExisteByDtHr($debug = false) {
        if (!$this->getCodInscrito()) {
            throw new Exception('Código do Inscrito inválido ou não informado: '.$this->getCodInscrito());
        }
        if (!$this->getCodOficina()) {
            throw new Exception('Código do Opção inválido ou não informado: '.$this->getCodOficina());
        }
        return $this->objDAO->getInscritoOficinaExisteByDtHr($this, $debug);
    }
    public function getByCodInscritoCodOficina($debug = false) {
        if (!$this->getCodInscrito()) {
            throw new Exception('Código do Inscrito inválido ou não informado: '.$this->getCodInscrito());
        }
        if (!$this->getCodOficina()) {
            throw new Exception('Código da Opção inválido ou não informado: '.$this->getCodOficina());
        }
        $this->objDAO->getByCodInscritoCodOficina($this, $debug);
        return true;
    }
    public function getByCodOficina($debug = false) {
        if (!$this->getCodOficina()) {
            throw new Exception('Código da Opção inválido ou não informado: '.$this->getCodOficina());
        }
        $this->objDAO->getByCodOficina($this, $debug);
        return true;
    }
    public function carregarListaOficinasByIdInscrito($debug = false) {
        $this->objDAO->carregarListaOficinasByIdInscrito($this, $debug);
        if ($this->getListaOficinas()) {
            return true;
        } else {
            throw new Exception("Inscrito não possui opção associada.");
        }
    }
    public function carregarListaOficinasLiberadasByIdEvento($debug = false) {
        $this->objDAO->carregarListaOficinasLiberadasByIdEvento($this, $debug);
        if ($this->getListaOficinas()) {
            return true;
        } else {
            throw new Exception("Evento não possui opção associada.");
        }
    }
    public function carregarOficinaByIdInscritoOficina($debug = false) {
        $this->objDAO->carregarOficinaByIdInscritoOficina($this, $debug);
        if ($this->getTitulo()) {
            return true;
        } else {
            throw new Exception("Inscrito não possui está opção associada.");
        }
    }
    public function carregarListaMinistrantesOficinasByIdInscrito($debug = false) {
        $this->objDAO->carregarListaMinistrantesOficinasByIdInscrito($this, $debug);
        if ($this->getListaMinistrantesOficinas()) {
            return true;
        } else {
            throw new Exception("Inscrito não possui opção associada.");
        }
    }
    public function salvar($debug = false) {

        $this->validatorObj();
        if (is_null($this->getCodInscritoOficina()) or $this->getCodInscritoOficina() == "") {
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Opção associada ao inscrito sucesso.");
            } else {
                throw new Exception("Opção não foi associada ao inscrito.");
            }
        } else {
            $this->InscritoOficinaExiste($debug);

            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Opção associada ao inscrito atualizado com sucesso.");
            } else {
                throw new Exception("Opção associada ao inscrito não foi atualizado.");
            }
        }
    }
    
    public function salvarPresenca($debug = false) {
        if ($this->objDAO->update($this, $debug)) {
            throw new OKException("Presença alterada com sucesso.");
        } else {
            throw new Exception("Presença NÃO alterada com sucesso.");
        }
    }
    
    public function excluir($debug = false) {
        $this->validatorEcluir();
        $this->InscritoOficinaExiste($debug);
        if ($this->objDAO->delete($this, $debug)) {
            throw new OKException("Inscrito cód. " . $this->getCodInscrito() . " foi excluído com sucesso da opção cód:" . $this->getCodOficina() . ".");
        } else {
            throw new Exception("Inscrito cód. " . $this->getCodInscrito() . " NÃO foi excluído da opção cód:" . $this->getCodOficina() . ".");
        }
    }

    public function getInscritoOficinaById($debug = false) {
        $this->objDAO->getById($this, $debug);
    }

    public function getEvento() {
        $obj = new InscEvento();
        $obj->setCodEvento($this->getCodEvento());
        $obj->getEventoById();
        return $obj;
    }

}

?>
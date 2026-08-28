<?php

class Isento extends IsentoVO {

    private $objDAO;

    function __construct() {
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new IsentoDAO();
    }

    public function IsentoExiste() {
        if (!$this->getCodIsento()) {
            throw new Exception('Código da Isento inválido ou não informado.');
        }
        if (!$this->objDAO->getByIdExiste($this)) {
            throw new Exception("Nenhuma Isento foi encontrada para o código informado: " . $this->getCodIsento() . ".");
            //throw new Exception("Id informado para deleção não existe");
        }
    }

    public function novo($debug = false) {
        
    }

    public function salvar($debug = false) {
        
        $this->validatorObj();
        if (is_null($this->getCodIsento()) or $this->getCodIsento() == "") {
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Isento salva com sucesso.");
            } else {
                throw new Exception("Novo Isento não foi salvo.");
            }
        } else {
            $this->IsentoExiste($debug);

            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Isento foi atualizado com sucesso.");
            } else {
                throw new Exception("Isento atual não foi atualizado.");
            }
        }
    }
    public function salvarIsentoCodigo($debug = false) {
        
        $this->validatorObj();
        if (is_null($this->getCodIsento()) or $this->getCodIsento() == "") {
            if ($this->objDAO->insertCodigo($this, $debug)) {
                throw new OKException("Isento código salva com sucesso.");
            } else {
                throw new Exception("Novo Isento código não foi salvo.");
            }
        } else {
            $this->IsentoExiste($debug);

            if ($this->objDAO->updateCodigo($this, $debug)) {
                throw new OKException("Isento código foi atualizado com sucesso.");
            } else {
                throw new Exception("Isento código atual não foi atualizado.");
            }
        }
    }

    public function excluir($debug = false) {
        if($this->validatorEcluir()){
            $excluidoIsento          = $this->objDAO->delete($this, $debug);
            $excluidoIsentoCodIsento = $this->objDAO->deleteCodigoIsento($this, $debug);
            if ($excluidoIsento or $excluidoIsentoCodIsento) {
                throw new OKException("Isento cód:" . $this->getCodIsento() . " foi excluído com sucesso.");
            } else {
                throw new Exception("Isento cód:" . $this->getCodIsento() . " não foi excluído.");
            }
        }
    }

    public function getIsentoById($debug = false) {
        $this->objDAO->getById($this, $debug);
    }
    public function getIsentoByCpf($debug = false) {
        $this->objDAO->getByCpf($this, $debug);
    }
    public function getIsentoByCpfOld($debug = false) {
        $this->objDAO->getByCpfOld($this, $debug);
    }
    public function getByCpfPorPerfil($debug = false) {
        $this->objDAO->getByCpfPorPerfil($this, $debug);
    }

    public function carregarListaIsento($condicoes=false, $debug = false) {
        $this->objDAO->carregarListaIsentoByIdEvento($this, $condicoes, $debug);
        if ($this->getListaIsento()) {
        } else {
            throw new Exception("Evento não possui Isento cadastrado.");
        }
    }
    public function carregarListaPerfil($condicoes=false, $debug = false) {
        $obj= new InscritoPerfil();
        $obj->setCodInscrito($this->getCodInscrito());
        $obj->carregarListaPerfilByIdInscrito($debug);
        $this->setListaPerfil($obj->getListaPerfil());
        if ($this->getListaPerfil()) {
            
        } else {
            throw new Exception("Isento não possui perfis cadastrados.");
        }
    }

    public function getEvento() {
        $obj->setCodEvento($this->getCodEvento());
        $obj->getEventoById();
        $obj = new Evento();
        return $obj;
    }

}

?>
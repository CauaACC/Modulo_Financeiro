<?php

class Comunicado extends ComunicadoVO {

    private $objDAO;

    function __construct() {
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new ComunicadoDAO();
    }

    public function ComunicadoExiste() {
        if (!$this->getCodComunicado()) {
            throw new Exception('Código do Comunicado inválido ou não informado.');
        }
        if (!$this->objDAO->getByIdExiste($this)) {
            throw new Exception("Nenhuma Comunicado foi encontrada para o código informado: " . $this->getCodComunicado() . ".");
            //throw new Exception("Id informado para deleção não existe");
        }
    }

    public function novo($debug = false) {
        
    }

    public function salvar($debug = false) {
        
        $this->validatorObj();
        if (is_null($this->getCodComunicado()) or $this->getCodComunicado() == "") {
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Comunicado salvo com sucesso.");
            } else {
                throw new Exception("Novo Comunicado não foi salva.");
            }
        } else {
            $this->ComunicadoExiste($debug);

            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Comunicado foi atualizado com sucesso.");
            } else {
                throw new Exception("Comunicado atual não foi atualizada.");
            }
        }
    }

    public function excluir($debug = false) {
        $this->validatorEcluir();
        if ($this->objDAO->delete($this, $debug)) {
            throw new OKException("Comunicado cód:" . $this->getCodComunicado() . " foi excluída com sucesso.");
        } else {
            throw new Exception("Comunicado cód:" . $this->getCodComunicado() . " não foi excluída.");
        }
    }

    public function getComunicadoById($debug = false) {
        return $this->objDAO->getById($this, $debug);
    }

    public function carregarListaComunicados($debug = false,$limit=false) {
        if ($this->objDAO->getAll($this, $debug,$limit)) {
            return true;
        } else {
            throw new Exception("Evento não possui Comunicado cadastrado.");
        }
    }
    public function carregarListaComunicadosByTipoComunicado($debug = false,$limit=false) {
        if ($this->objDAO->getListaComunicadoByByTipoComunicado($this, $debug)) {
            return true;
        } else {
            throw new Exception("Evento não possui Comunicado cadastrado.");
        }
    }

    public function getEvento() {
        $obj = new InscEvento();
        $obj->setCodEvento($this->getCodEvento());
        $obj->getEventoById();
        return $obj;
    }

}

?>
<?php

class ComunicadoModelo extends ComunicadoModeloVO {

    private $objDAO;

    function __construct() {
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new ComunicadoModeloDAO();
    }

    public function ComunicadoModeloExiste() {
        if (!$this->getCodComunicadoModelo()) {
            throw new Exception('Código do Comunicado Modelo inválido ou não informado.');
        }
        if (!$this->objDAO->getByIdExiste($this)) {
            throw new Exception("Nenhuma Comunicado Modelo foi encontrada para o código informado: " . $this->getCodComunicadoModelo() . ".");
            //throw new Exception("Id informado para deleção não existe");
        }
    }

    public function novo($debug = false) {
        
    }

    public function salvar($debug = false) {
        
        $this->validatorObj();
        if (is_null($this->getCodComunicadoModelo()) or $this->getCodComunicadoModelo() == "") {
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Comunicado Modelo salvo com sucesso.");
            } else {
                throw new Exception("Novo Comunicado Modelo não foi salva.");
            }
        } else {
            $this->ComunicadoModeloExiste($debug);

            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Comunicado Modelo foi atualizado com sucesso.");
            } else {
                throw new Exception("Comunicado Modelo atual não foi atualizada.");
            }
        }
    }

    public function excluir($debug = false) {
        $this->validatorEcluir();
        if ($this->objDAO->delete($this, $debug)) {
            throw new OKException("Comunicado Modelo cód:" . $this->getCodComunicadoModelo() . " foi excluída com sucesso.");
        } else {
            throw new Exception("Comunicado Modelo cód:" . $this->getCodComunicadoModelo() . " não foi excluída.");
        }
    }

    public function getComunicadoModeloById($debug = false) {
        return $this->objDAO->getById($this, $debug);
    }

    public function carregarListaComunicadoModelos($debug = false,$limit=false) {
        if ($this->objDAO->getAll($this, $debug,$limit)) {
            return true;
        } else {
            throw new Exception("Evento não possui Comunicado Modelo cadastrado.");
        }
    }
    public function carregarListaComunicadoModelosByTipo($debug = false,$limit=false) {
        if ($this->objDAO->getListaComunicadoModeloByByTipoModelo($this, $debug)) {
            return true;
        } else {
            throw new Exception("Evento não possui ComunicadoModelo cadastrado.");
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
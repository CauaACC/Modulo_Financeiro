<?php

class AreaTematica extends AreaTematicaVO {

    private $objDAO;

    function __construct() {
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new AreaTematicaDAO();
    }

    public function AreaTematicaExiste() {
        if (!$this->getCodAreaTematica()) {
            throw new Exception('Código da Área Temática inválido ou não informado.');
        }
        if (!$this->objDAO->getByIdExiste($this)) {
            throw new Exception("Nenhuma Área Temática foi encontrada para o código informado: " . $this->getCodAreaTematica() . ".");
            //throw new Exception("Id informado para deleção não existe");
        }
    }

    public function novo($debug = false) {
        
    }

    public function salvar($debug = false) {
        
        $this->validatorObj();
        if (is_null($this->getCodAreaTematica()) or $this->getCodAreaTematica() == "") {
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Área Temática salva com sucesso.");
            } else {
                throw new Exception("Novo Área Temática não foi salva.");
            }
        } else {
            $this->AreaTematicaExiste($debug);

            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Área Temática foi atualizada com sucesso.");
            } else {
                throw new Exception("Área Temática atual não foi atualizada.");
            }
        }
    }

    public function excluir($debug = false) {
        $this->validatorEcluir();
        if ($this->objDAO->delete($this, $debug)) {
            throw new OKException("Área Temática cód:" . $this->getCodAreaTematica() . " foi excluída com sucesso.");
        } else {
            throw new Exception("Área Temática cód:" . $this->getCodAreaTematica() . " não foi excluída.");
        }
    }

    public function getAreaTematicaById($debug = false) {
        $this->objDAO->getById($this, $debug);
    }

    public function carregarListaAreaTematicaByIdEvento ($debug = false) {
        return $this->getListaAreaTematicaByIdEvento($debug);
    }
    public function carregarListaAreasTematicasByIdEvento ($debug = false) {
        return $this->getListaAreaTematicaByIdEvento($debug);
    }

    public function getListaAreaTematicaByIdEvento($debug = false) {
        if (!$this->getCodEvento()) {
            throw new Exception("Id do evento não informado.");
        }
        $this->objDAO->carregarListaAreaTematicasByIdEvento($this, $debug);
        if ($this->getListaAreasTematicas()) {
            return $this->getListaAreasTematicas();
        } else {
            throw new Exception("Evento não possui Área Temática cadastrada.");
        }
    }

    public function carregarListaAreaTematica($orderBy=false, $debug = false) {
        $this->objDAO->carregarListaAreaTematicaByIdEvento($this, $debug);
        if ($this->getListaAreasTematicas()) {
        } else {
            throw new Exception("Evento não possui Área Temática cadastrada.");
        }
    }

    public function carregarListaAreasTematicasByIdUsuario($orderBy=false, $debug = false) {
        $this->objDAO->carregarListaAreasTematicasByIdUsuario($this, $debug);
        if ($this->getListaAreasTematicas()) {
        } else {
            throw new Exception("Evento não possui Área Temática cadastrada.");
        }
    }

    public function getEvento() {
        $obj = new Evento();
        $obj->setCodEvento($this->getCodEvento());
        $obj->getEventoById();
        return $obj;
    }

}

?>
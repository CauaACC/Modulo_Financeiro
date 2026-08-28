<?php

class InscritoPagamento extends InscritoPagamentoVO {

    private $objDAO;

    function __construct() {
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new InscritoPagamentoDAO();
    }

    public function inscritoPagamentoExiste($debug = false) {
        if (!$this->getCodInscrito()) {
            throw new Exception('Código do Inscrito inválido ou não informado.');
        }
        if (!$this->getCodOficina()) {
            throw new Exception('Código da Atividade inválido ou não informado.');
        }
        return $this->objDAO->getByIdExiste($this, $debug);
    }
    public function carregarListaOficinasByIdInscrito($debug = false) {
        $this->objDAO->carregarListaOficinasByIdInscrito($this, $debug);
        if ($this->getListaOficinas()) {
            return true;
        } else {
            throw new Exception("Inscrito não possui Atividade cadastrada.");
        }
    }
    public function salvar($debug = false) {

        $this->validatorObj();
        if (is_null($this->getCodInscritoPagamento()) or $this->getCodInscritoPagamento() == "") {
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Atividade associada ao inscrito sucesso.");
            } else {
                throw new Exception("Atividade não foi associada ao inscrito.");
            }
        } else {
            $this->InscritoPagamentoExiste($debug);

            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Atividade associada ao inscrito atualizada com sucesso.");
            } else {
                throw new Exception("Atividade associada ao inscrito não foi atualizada.");
            }
        }
    }

    public function excluir($debug = false) {
        $this->validatorEcluir();
        if ($this->objDAO->delete($this, $debug)) {
            throw new OKException("Atividade cód:" . $this->getCodAtividade() . " foi excluída com sucesso.");
        } else {
            throw new Exception("Atividade cód:" . $this->getCodAtividade() . " não foi excluída.");
        }
    }

    public function getInscritoPagamentoById($debug = false) {
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
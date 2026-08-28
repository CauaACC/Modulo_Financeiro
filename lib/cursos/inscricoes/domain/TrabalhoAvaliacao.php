<?php

class TrabalhoAvaliacao extends TrabalhoAvaliacaoVO {

    private $objDAO;

    function __construct() {
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new TrabalhoAvaliacaoDAO();
    }

    public function trabalhoAvaliacaoExiste($debug = false) {
        if (!$this->getCodInscrito()) {
            throw new Exception('Código do Inscrito inválido ou não informado.');
        }
        if (!$this->getCodTrabalho()) {
            throw new Exception('Código da Trabalho inválido ou não informado.');
        }
        return $this->objDAO->getByIdExiste($this, $debug);
    }
    public function carregarListaAvaliacoesByIdTrabalho($debug = false) {
        $this->objDAO->carregarListaAvaliacoesByIdTrabalho($this, $debug);
        if ($this->getListaAvaliacoes()) {
            return true;
        } else {
            throw new Exception("Trabalho não possui avaliações cadastrada.");
        }
    }
    public function carregarListaEstatisticaAvaliacaoByIdEvento($debug = false) {
        $this->objDAO->carregarListaEstatisticaAvaliacaoByIdEvento($this, $debug);
        if ($this->getListaEstatisticaAvaliacaoByIdEvento()) {
            return true;
        } else {
            throw new Exception("Não há informações.");
        }
    }
    public function salvar($debug = false) {

        $this->validatorObj();
        if (is_null($this->getCodAvaliacao()) or $this->getCodAvaliacao() == "") {
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Avaliação do trabalho executada com sucesso.");
            } else {
                throw new Exception("Avaliação do trabalho NÃO executada.");
            }
        } else {
            $this->TrabalhoAvaliacaoExiste($debug);

            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Avaliação do trabalho atualizada com sucesso.");
            } else {
                throw new Exception("Avaliação do trabalho não foi atualizada.");
            }
        }
    }
    public function salvarAvaliacao($debug = false) {

        $this->validatorObj();
        if($this->TrabalhoAvaliacaoExiste($debug)){
            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Avaliação do trabalho atualizada com sucesso.");
            } else {
                throw new Exception("Avaliação do trabalho não foi atualizada.");
            }
        }else{
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Avaliação do trabalho executada com sucesso.");
            } else {
                throw new Exception("Avaliação do trabalho NÃO executada.");
            }
        }
    }

    public function excluir($debug = false) {
        $this->validatorEcluir();
        if ($this->objDAO->delete($this, $debug)) {
            throw new OKException("Avaliação cód:" . $this->getCodTrabalho() . " foi excluída com sucesso.");
        } else {
            throw new Exception("Avaliação cód:" . $this->getCodTrabalho() . " não foi excluída.");
        }
    }

    public function getTrabalhoAvaliacaoById($debug = false) {
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
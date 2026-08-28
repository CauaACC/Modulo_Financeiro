<?php

class HistoricoAvaliacaoPremiacao extends HistoricoAvaliacaoPremiacaoVO {

    private $objDAO;

    function __construct() {
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new HistoricoAvaliacaoPremiacaoDAO();
    }

    public function historicoAvaliacaoExiste($debug = false) {
        if (!$this->getCodInscrito()) {
            throw new Exception('Código do Inscrito inválido ou não informado.');
        }
        if (!$this->getCodTrabalho()) {
            throw new Exception('Código da Trabalho inválido ou não informado.');
        }
        if (!$this->getCodAvaliacao()) {
            throw new Exception('Código do Histórico da avaliação inválido ou não informado.');
        }
        return $this->objDAO->getByIdExiste($this, $debug);
    }
    public function carregarListaAvaliacoesByIdTrabalho($debug = false) {
        $this->objDAO->carregarListaAvaliacoesByIdTrabalho($this, $debug);
        if ($this->getListaAvaliacoes()) {
            return true;
        } else {
            throw new Exception("Trabalho não possui avaliações cadastradas.");
        }
    }
    public function carregarListaUltimasAvaliacaoPremiacaoByIdTrabalho($debug = false) {
        $this->objDAO->carregarListaUltimasAvaliacaoPremiacaoByIdTrabalho($this, $debug);
        if ($this->getListaUltimasAvaliacaoPremiacao()) {
            return true;
        } else {
            throw new Exception("Trabalho não possui avaliações cadastradas.");
        }
    }
    public function carregarUltimaAvaliacaoByIdTrabalho($debug = false) {
        return $this->objDAO->getUltimaAvaliacaoByIdTrabalho($this, $debug);
    }
    public function salvar($debug = false) {

        $this->validatorObj();
        if (is_null($this->getCodAvaliacao()) or $this->getCodAvaliacao() == "") {
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Histórico da avaliação do trabalho gravado com sucesso.");
            } else {
                throw new Exception("Histórico da avaliação do trabalho NÃO gravado.");
            }
        } else {
            $this->HistoricoAvaliacaoExiste($debug);

            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Histórico da avaliação do trabalho atualizada com sucesso.");
            } else {
                throw new Exception("Histórico da avaliação do trabalho não foi atualizada.");
            }
        }
    }

    public function excluir($debug = false) {
        $this->validatorEcluir();
        if ($this->objDAO->delete($this, $debug)) {
            throw new OKException("Histórico da avaliação cód:" . $this->getCodAvaliacao() . " foi excluída com sucesso.");
        } else {
            throw new Exception("Histórico da avaliação cód:" . $this->getCodTrabalho() . " não foi excluída.");
        }
    }

    public function desativarAvaliacoesAvaliador($debug = false) {
        $this->validarLiberarAvaliador();
        if ($this->objDAO->desativarAvaliacoesAvaliador($this, $debug)) {
            throw new OKException("Trabalho liberado para outro avaliador com sucesso.");
        } else {
            throw new Exception("Liberação para outro avaliador não foi executada.");
        }
    }

    public function getHistoricoAvaliacaoById($debug = false) {
        $this->objDAO->getById($this, $debug);
    }
    public function getHistoricoAvaliacaoByIdAvaliador($debug = false) {
        return $this->objDAO->getByIdAvaliador($this, $debug);
    }

    public function getEvento() {
        $obj = new InscEvento();
        $obj->setCodEvento($this->getCodEvento());
        $obj->getEventoById();
        return $obj;
    }

    public function carregarObjAvaliador() {
        $obj = new Avaliador();
        $obj->setCodAvaliador($this->getCodAvaliador());
        $obj->getAvaliadorByIdPremiacao();
        $this->setObjAvaliador($obj);
    }

}

?>
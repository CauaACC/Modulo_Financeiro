<?php

class AvaliacaoPES extends AvaliacaoPESVO {

    private $objDAO;

    function __construct() {
        if ((!isset($_SESSION['_cod_inscrito']) || empty($_SESSION['_cod_inscrito'])) and (!isset($_SESSION['_cod_usuario']) || empty($_SESSION['_cod_usuario']))) {
            throw new Exception("Usuario nao esta logado. Por favor, faca login e tente novamente.");
        }
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new AvaliacaoPESDAO();
    }

    public function AvaliacaoPESExiste() {
        if (!$this->getCodAvaliacao()) {
            throw new Exception('Codigo da avaliacao invalido ou nao informado.');
        }
        if (!$this->objDAO->getByIdExiste($this)) {
            throw new Exception("Nenhuma Avaliacao foi encontrada para o codigo informado: " . $this->getCodAvaliacao() . ".");
        }
    }

    public function salvar($debug = false) {
        $this->validatorObj();
        if (is_null($this->getCodAvaliacao()) or $this->getCodAvaliacao() == "") {
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Avaliacao do PES salva com sucesso.");
            } else {
                throw new Exception("Avaliacao do PES nao foi salva.");
            }
        } else {
            $this->AvaliacaoPESExiste($debug);
            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Avaliacao do PES foi atualizada com sucesso.");
            } else {
                throw new Exception("Avaliacao do PES nao foi atualizada.");
            }
        }
    }

    public function excluir($debug = false) {
        $this->validatorExcluir();
        if ($this->objDAO->delete($this, $debug)) {
            throw new OKException("Avaliacao cod:" . $this->getCodAvaliacao() . " foi excluida com sucesso.");
        } else {
            throw new Exception("Avaliacao cod:" . $this->getCodAvaliacao() . " nao foi excluida.");
        }
    }

    public function getAvaliacaoPESById($debug = false) {
        $this->objDAO->getById($this, $debug);
    }

    public function getListaAvaliacoesByCodAgente($debug = false) {
        if (!$this->getCodAgente()) {
            throw new Exception("Codigo do agente nao informado.");
        }
        $this->objDAO->carregarListaAvaliacoesByCodAgente($this, $debug);
        if ($this->getListaAvaliacoes()) {
            return $this->getListaAvaliacoes();
        } else {
            throw new Exception("");
        }
    }

    public function getAgentesComStatusPorSupervisor($codSupervisor, $debug = false) {
        return $this->objDAO->carregarAgentesComStatusPorSupervisor($this->getCodEvento(), $codSupervisor, $debug);
    }

    public function getEstatisticasGerais($debug = false) {
        return $this->objDAO->carregarEstatisticasGeraisPES($this->getCodEvento(), $debug);
    }

    public function getCodGrupoSupervisor($debug = false) {
        return $this->objDAO->getCodGrupoSupervisor($this->getCodEvento(), $debug);
    }

    public function getListaSupervisores($codGrupoSupervisor, $debug = false) {
        return $this->objDAO->carregarListaSupervisores($this->getCodEvento(), $codGrupoSupervisor, $debug);
    }

    public function getPainelComparativo($debug = false) {
        return $this->objDAO->carregarPainelComparativo($this->getCodEvento(), $debug);
    }

}

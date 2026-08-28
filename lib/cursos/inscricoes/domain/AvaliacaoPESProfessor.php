<?php

class AvaliacaoPESProfessor extends AvaliacaoPESProfessorVO {

    private $objDAO;

    function __construct() {
        if ((!isset($_SESSION['_cod_inscrito']) || empty($_SESSION['_cod_inscrito'])) and (!isset($_SESSION['_cod_usuario']) || empty($_SESSION['_cod_usuario']))) {
            throw new Exception("Usuario nao esta logado. Por favor, faca login e tente novamente.");
        }
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new AvaliacaoPESProfessorDAO();
    }

    public function avaliacaoExiste() {
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
                throw new OKException("Avaliacao salva com sucesso.");
            } else {
                throw new Exception("Avaliacao nao foi salva.");
            }
        } else {
            $this->avaliacaoExiste($debug);
            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Avaliacao foi atualizada com sucesso.");
            } else {
                throw new Exception("Avaliacao nao foi atualizada.");
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

    public function getAvaliacaoById($debug = false) {
        $this->objDAO->getById($this, $debug);
    }

    public function getAgentesComAvaliacoesPorSupervisor($codSupervisor, $ciclo, $debug = false) {
        return $this->objDAO->carregarAgentesComAvaliacoesPorSupervisor($this->getCodEvento(), $codSupervisor, $ciclo, $debug);
    }

    public function getAvaliacoesPorAgente($codAgente, $ciclo, $debug = false) {
        return $this->objDAO->carregarAvaliacoesPorAgente($this->getCodEvento(), $codAgente, $ciclo, $debug);
    }

    public function getPainelComparativo($ciclo, $debug = false) {
        return $this->objDAO->carregarPainelComparativo($this->getCodEvento(), $ciclo, $debug);
    }

}

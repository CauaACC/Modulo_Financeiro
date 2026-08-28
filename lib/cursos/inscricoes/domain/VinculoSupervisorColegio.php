<?php

class VinculoSupervisorColegio extends VinculoSupervisorColegioVO {

    private $objDAO;

    function __construct() {
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new VinculoSupervisorColegioDAO();
    }

    public function vinculoExiste() {
        if (!$this->getCodVinculo()) {
            throw new Exception('Codigo do vinculo invalido ou nao informado.');
        }
        if (!$this->objDAO->getByIdExiste($this)) {
            throw new Exception("Nenhum vinculo foi encontrado para o codigo informado: " . $this->getCodVinculo() . ".");
        }
    }

    public function verificarDuplicidade($debug = false) {
        if ($this->objDAO->verificarDuplicidade($this, $debug)) {
            throw new Exception("Este col&eacute;gio j&aacute; est&aacute; vinculado a este supervisor.");
        }
    }

    public function salvar($debug = false) {
        $this->validatorObj();
        if (is_null($this->getCodVinculo()) or $this->getCodVinculo() == "") {
            $this->verificarDuplicidade($debug);
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Col&eacute;gio vinculado ao supervisor com sucesso.");
            } else {
                throw new Exception("V&iacute;nculo n&atilde;o foi salvo.");
            }
        }
    }

    public function excluir($debug = false) {
        $this->validatorExcluir();
        if ($this->objDAO->delete($this, $debug)) {
            throw new OKException("Col&eacute;gio desvinculado do supervisor com sucesso.");
        } else {
            throw new Exception("V&iacute;nculo n&atilde;o foi removido.");
        }
    }

    public function getListaVinculosBySupervisor($debug = false) {
        if (!$this->getCodSupervisor()) {
            throw new Exception("Codigo do supervisor nao informado.");
        }
        $this->objDAO->carregarListaVinculosBySupervisor($this, $debug);
        if ($this->getListaVinculos()) {
            return $this->getListaVinculos();
        } else {
            return array();
        }
    }

    public static function listarCodColegiosDeSupervisor($codSupervisor, $debug = false) {
        $dao = new VinculoSupervisorColegioDAO();
        return $dao->carregarCodColegiosBySupervisor(COD_EVENTO, $codSupervisor, $debug);
    }

    public static function listarContagensPorColegio($debug = false) {
        $dao = new VinculoSupervisorColegioDAO();
        return $dao->carregarContagensPorColegio(COD_EVENTO, $debug);
    }

}

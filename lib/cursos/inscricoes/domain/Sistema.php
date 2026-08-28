<?php

class Sistema extends SistemaVO {

    private $objDAO;

    function __construct() {
        $this->objDAO = new SistemaDAO();
    }

    public function salvar($debug = false) {
        $this->validatorObj();
        if (is_null($this->getCodSistema()) or $this->getCodSistema() === "") {
            $proxCod = $this->objDAO->getProximoCodSistema($debug);
            $this->setCodSistema($proxCod);
            if ($this->getEnumSistema() == '') {
                $this->setEnumSistema('SIS_' . Grupo::nomeParaConstante($this->getNomeSistema()));
            }
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Sistema cadastrado. Enum: " . $this->getEnumSistema() . " = " . $this->getCodSistema());
            } else {
                throw new Exception("Sistema n&atilde;o foi salvo.");
            }
        } else {
            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Sistema atualizado com sucesso.");
            } else {
                throw new Exception("Sistema n&atilde;o foi atualizado.");
            }
        }
    }

    public function excluir($debug = false) {
        $this->validatorExcluir();
        $codSistema = intval($this->getCodSistema());

        $totalDireitos = $this->objDAO->contarVinculosDireitos($codSistema, $debug);
        if ($totalDireitos > 0) {
            throw new Exception("N&atilde;o &eacute; poss&iacute;vel excluir: sistema est&aacute; vinculado a " . $totalDireitos . " registro(s) de direitos.");
        }

        if ($this->objDAO->delete($this, $debug)) {
            throw new OKException("Sistema exclu&iacute;do com sucesso.");
        } else {
            throw new Exception("Sistema n&atilde;o foi exclu&iacute;do.");
        }
    }

    public function getSistemaById($debug = false) {
        return $this->objDAO->getById($this, $debug);
    }

    public function getListaSistemas($debug = false) {
        return $this->objDAO->getListaSistemas($debug);
    }

    public function carregarSelectSistemas($debug = false) {
        return $this->objDAO->getSelectSistemas($debug);
    }

    public function criarGrupoCorrespondente($debug = false) {
        $enumGrupo = str_replace('SIS_', 'GR_', $this->getEnumSistema());
        $objGrupo = new Grupo();
        if (!$objGrupo->existeGrupoNoEvento($this->getCodSistema(), 0, $debug)) {
            $objGrupo->inserirGrupoNoEvento(
                $this->getCodSistema(),
                $this->getNomeSistema(),
                0,
                $enumGrupo,
                0,
                '',
                $debug
            );
        }
    }
}

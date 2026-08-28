<?php

class Grupo extends GrupoVO {

    private $objDAO;

    function __construct() {
        $this->objDAO = new GrupoDAO();
    }

    public function salvar($debug = false) {
        $this->validatorObj();
        if (is_null($this->getCodGrupo()) or $this->getCodGrupo() === "") {
            $proxCod = $this->objDAO->getProximoCodGrupo($debug);
            $this->setCodGrupo($proxCod);
            if ($this->getEnumGrupo() == '') {
                $this->setEnumGrupo('GR_' . self::nomeParaConstante($this->getNomeGrupo()));
            }
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Grupo cadastrado. Enum: " . $this->getEnumGrupo() . " = " . $this->getCodGrupo());
            } else {
                throw new Exception("Grupo n&atilde;o foi salvo.");
            }
        } else {
            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Grupo atualizado com sucesso.");
            } else {
                throw new Exception("Grupo n&atilde;o foi atualizado.");
            }
        }
    }

    public function excluir($debug = false) {
        $this->validatorExcluir();
        $codGrupo = intval($this->getCodGrupo());

        $totalDireitos = $this->objDAO->contarVinculosDireitos($codGrupo, $debug);
        if ($totalDireitos > 0) {
            throw new Exception("N&atilde;o &eacute; poss&iacute;vel excluir: grupo est&aacute; vinculado a " . $totalDireitos . " registro(s) de direitos.");
        }

        $totalUsuarios = $this->objDAO->contarVinculosUsuarios($codGrupo, $debug);
        if ($totalUsuarios > 0) {
            throw new Exception("N&atilde;o &eacute; poss&iacute;vel excluir: grupo est&aacute; vinculado a " . $totalUsuarios . " usu&aacute;rio(s).");
        }

        if ($this->objDAO->delete($this, $debug)) {
            throw new OKException("Grupo exclu&iacute;do com sucesso.");
        } else {
            throw new Exception("Grupo n&atilde;o foi exclu&iacute;do.");
        }
    }

    public function getGrupoById($debug = false) {
        return $this->objDAO->getById($this, $debug);
    }

    public function getListaGrupos($debug = false) {
        return $this->objDAO->getListaGrupos($debug);
    }

    public function carregarSelectGrupos($debug = false) {
        return $this->objDAO->getSelectGrupos($debug);
    }

    public function carregarGruposByEvento($codEvento, $debug = false) {
        return $this->objDAO->getGruposByEvento($codEvento, $debug);
    }

    public function existeGrupoNoEvento($codGrupo, $codEvento, $debug = false) {
        return $this->objDAO->existeGrupoNoEvento($codGrupo, $codEvento, $debug);
    }

    public function inserirGrupoNoEvento($codGrupo, $nomeGrupo, $codEvento, $enumGrupo, $ordem, $categoria, $debug = false) {
        return $this->objDAO->inserirGrupoNoEvento($codGrupo, $nomeGrupo, $codEvento, $enumGrupo, $ordem, $categoria, $debug);
    }

    public static function nomeParaConstante($nome) {
        $mapa = array(
            'á'=>'a','à'=>'a','ã'=>'a','â'=>'a','ä'=>'a',
            'é'=>'e','è'=>'e','ê'=>'e','ë'=>'e',
            'í'=>'i','ì'=>'i','î'=>'i','ï'=>'i',
            'ó'=>'o','ò'=>'o','õ'=>'o','ô'=>'o','ö'=>'o',
            'ú'=>'u','ù'=>'u','û'=>'u','ü'=>'u',
            'ç'=>'c','ñ'=>'n',
            'Á'=>'A','À'=>'A','Ã'=>'A','Â'=>'A','Ä'=>'A',
            'É'=>'E','È'=>'E','Ê'=>'E','Ë'=>'E',
            'Í'=>'I','Ì'=>'I','Î'=>'I','Ï'=>'I',
            'Ó'=>'O','Ò'=>'O','Õ'=>'O','Ô'=>'O','Ö'=>'O',
            'Ú'=>'U','Ù'=>'U','Û'=>'U','Ü'=>'U',
            'Ç'=>'C','Ñ'=>'N'
        );
        $nome = strtr($nome, $mapa);
        $nome = strtoupper($nome);
        $nome = preg_replace('/[^A-Z0-9]+/', '_', $nome);
        $nome = trim(preg_replace('/_+/', '_', $nome), '_');
        return $nome;
    }
}

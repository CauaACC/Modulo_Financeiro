<?php

class GrupoSistema extends GrupoSistemaVO {
    

    private $objDAO;

    function __construct() {
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new GrupoSistemaDAO();
    }

    function carregarListaGruposByIdEvento($debug = false){
        return $this->objDAO->getListaGruposByIdEvento($this, $debug);
    }

    function carregarListaGruposByIdLogin($debug = false){
        return $this->objDAO->getListaGruposByIdLogin($this, $debug);
    }
    
    function carregarListaGruposByIdUsuario($debug = false){
        return $this->objDAO->getListaGruposByIdUsuario($this, $debug);
    }

    function carregarListaModulosByEvento($codEvento, $debug = false){
        return $this->objDAO->getListaModulosByEvento($codEvento, $debug);
    }

    function existeAssociacao($codEvento, $codGrupo, $codSistema, $codDireito, $debug = false){
        return $this->objDAO->existeAssociacao($codEvento, $codGrupo, $codSistema, $codDireito, $debug);
    }

    function carregarModulosByEvento($codEvento, $debug = false){
        return $this->objDAO->getModulosByEvento($codEvento, $debug);
    }

    function inserir($debug = false){
        return $this->objDAO->insert($this, $debug);
    }

    function excluir($debug = false){
        return $this->objDAO->delete($this, $debug);
    }

    function carregarSelectEventos($debug = false){
        return $this->objDAO->getSelectEventos($debug);
    }

    function carregarSelectGrupos($debug = false){
        $objGrupo = new Grupo();
        return $objGrupo->carregarSelectGrupos($debug);
    }

    function carregarSelectSistemas($debug = false){
        $objSistema = new Sistema();
        return $objSistema->carregarSelectSistemas($debug);
    }

    function carregarGruposByEvento($codEvento, $debug = false){
        $objGrupo = new Grupo();
        return $objGrupo->carregarGruposByEvento($codEvento, $debug);
    }

    function existeGrupoNoEvento($codGrupo, $codEvento, $debug = false){
        $objGrupo = new Grupo();
        return $objGrupo->existeGrupoNoEvento($codGrupo, $codEvento, $debug);
    }

    function inserirGrupoNoEvento($codGrupo, $nomeGrupo, $codEvento, $enumGrupo, $ordem, $categoria, $debug = false){
        $objGrupo = new Grupo();
        return $objGrupo->inserirGrupoNoEvento($codGrupo, $nomeGrupo, $codEvento, $enumGrupo, $ordem, $categoria, $debug);
    }
}

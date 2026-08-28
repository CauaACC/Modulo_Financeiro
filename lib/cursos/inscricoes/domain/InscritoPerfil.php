<?php

class InscritoPerfil extends InscritoPerfilVO {

    private $objDAO;

    function __construct() {
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new InscritoPerfilDAO();
    }

    public function inscritoPerfilExiste($debug = false) {
        if (!$this->getCodInscrito()) {
            throw new Exception('Código do Inscrito inválido ou não informado.');
        }
        if (!$this->getCodPerfil()) {
            throw new Exception('Código do Perfil inválido ou não informado.');
        }
        return $this->objDAO->getByIdExiste($this, $debug);
    }
    public function carregarListaPerfilByIdInscrito($debug = false) {
        if (!$this->getCodInscrito()) {
            throw new Exception('Código do Inscrito inválido ou não informado.');
        }
        $this->objDAO->getListaPerfilByIdInscrito($this, $debug);
        if ($this->getListaPerfil()) {
            return true;
        } else {
            throw new Exception("Inscrito não possui Perfil cadastrado.");
        }
    }
    public function carregarListaInscritoPerfil($condicoes, $debug = false) {
        $this->objDAO->carregarListaInscritoPerfil($this, $condicoes, $debug);
        if ($this->getListaInscritoPerfil()) {
            return true;
        } else {
            throw new Exception("Evento não possui inscritos cadastrados.");
        }
    }
    public function carregarListaPerfil($condicoes=false, $debug = false) {
        $obj= new InscritoPerfil();
        $obj->setCodInscrito($this->getCodInscrito());
        $obj->carregarListaPerfilByIdInscrito($debug);
        $this->setListaPerfil($obj->getListaPerfil());
        if ($this->getListaPerfil()) {
            
        } else {
            throw new Exception("Isento não possui perfis cadastrados.");
        }
    }    
    public function salvar($debug = false) {

        $this->validatorObj();
        if (is_null($this->getCodInscritoPerfil()) or $this->getCodInscritoPerfil() == "") {
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Perfil associado ao inscrito sucesso.");
            } else {
                throw new Exception("Perfil não foi associado ao inscrito.");
            }
        } else {
            $this->InscritoPerfilExiste($debug);

            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Perfil associado ao inscrito atualizado com sucesso.");
            } else {
                throw new Exception("Perfil associado ao inscrito não foi atualizado.");
            }
        }
    }

    /**
     * Atualiza SOMENTE a carga horaria da ligacao inscrito<->perfil (UPDATE
     * direcionado). Usado pelo modal de edicao da "Liberacao por pessoa".
     * Requer cod_evento/cod_inscrito/cod_perfil setados.
     */
    public function atualizarCarga($debug = false) {
        $this->validatorObj();
        if ($this->objDAO->atualizarCarga($this, $debug)) {
            throw new OKException("Carga horária do perfil atualizada com sucesso.");
        } else {
            throw new Exception("Não foi possível atualizar a carga horária do perfil.");
        }
    }

    public function excluir($debug = false) {
        $this->validatorEcluir();
        if ($this->objDAO->delete($this, $debug)) {
            throw new OKException("Perfil cód:" . $this->getCodPerfil() . " foi excluído com sucesso.");
        } else {
            throw new Exception("Perfil cód:" . $this->getCodPerfil() . " não foi excluído.");
        }
    }

    public function getInscritoPerfilById($debug = false) {
        $this->objDAO->getById($this, $debug);
    }
    
    public function carregarInscritoPerfilById($debug = false) {
        return $this->objDAO->getById($this, $debug);
    }

    public function getEvento() {
        $obj = new InscEvento();
        $obj->setCodEvento($this->getCodEvento());
        $obj->getEventoById();
        return $obj;
    }

}

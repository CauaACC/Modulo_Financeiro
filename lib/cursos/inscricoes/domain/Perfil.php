<?php

class Perfil extends PerfilVO {

    private $objDAO;

    function __construct() {
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new PerfilDAO();
    }

    public function carregarTaxaAtiva($condicoes = false, $debug = false){
        
        $objTaxa = new Taxa();
        $objTaxa->setCodPerfil($this->getCodPerfil());
        $objTaxa->carregarTaxaAtiva($condicoes , $debug);
        $this->setObjTaxa($objTaxa);
    }

    public function carregarTaxaMensalidade($condicoes = false, $debug = false){
        
        $objTaxa = new Taxa();
        $objTaxa->setCodPerfil($this->getCodPerfil());
        $objTaxa->carregarTaxaMensalidade($condicoes , $debug);
        $this->setObjTaxa($objTaxa);
    }
    
    public function carregarListaTaxas($condicoes = false, $orderby=false, $debug = false){
        $objTaxa = new Taxa();
        $objTaxa->setCodPerfil($this->getCodPerfil());
        $objTaxa->carregarListaTaxasBy($condicoes , $orderby, $debug);
        $this->setListaTaxas($objTaxa->getListaTaxas());
    }
    
    public function PerfilExiste() {
        if (!$this->getCodPerfil()) {
            throw new Exception('Código do Perfil inválido ou não informado.');
        }
        if (!$this->objDAO->getByIdExiste($this)) {
            throw new Exception("Nenhum Perfil foi encontrado para o código informado: " . $this->getCodPerfil() . ".");
            //throw new Exception("Id informado para deleção não existe");
        }
    }

    public function novo($debug = false) {
        
    }

    public function salvar($debug = false) {
        
        $this->validatorObj();
        if (is_null($this->getCodPerfil()) or $this->getCodPerfil() == "") {
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Perfil salvo com sucesso.");
            } else {
                throw new Exception("Novo Perfil não foi salvo.");
            }
        } else {
            $this->PerfilExiste($debug);

            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Perfil foi atualizado com sucesso.");
            } else {
                throw new Exception("Perfil atual não foi atualizado.");
            }
        }
    }

    public function excluir($debug = false) {
        $this->validatorExcluir();
        if ($this->objDAO->delete($this, $debug)) {
            throw new OKException("Perfil cód:" . $this->getCodPerfil() . " foi excluído com sucesso.");
        } else {
            throw new Exception("Perfil cód:" . $this->getCodPerfil() . " não foi excluído.");
        }
    }

    public function getPerfilById($debug = false) {
        $this->objDAO->getById($this, $debug);
    }

    public function carregarListaPerfil($orderBy = '',$debug = false) {
        $this->objDAO->carregarListaPerfilByIdEvento($this, $orderBy, $debug);
        if ($this->getListaPerfil()) {
        } else {
            throw new Exception("Evento não possui Perfis cadastrado.");
        }
    }
    
    public function toggleCampo($campo, $debug = false) {
        return $this->objDAO->toggleCampo($this, $campo, $debug);
    }

    public function carregarListaPerfilOptions($debug = false) {
        $this->objDAO->carregarListaPerfilOptions($this, $debug);
    }

    public function carregarListaPerfilInscricao($debug = false) {
        $this->objDAO->carregarListaPerfilInscricaoByIdEvento($this, $debug);
        if ($this->getListaPerfil()) {
        } else {
            throw new Exception("Evento não possui Perfis de inscrição liberados.");
        }
    }

    public function getEvento() {
        $obj = new Evento();
        $obj->setCodEvento($this->getCodEvento());
        $obj->getEventoById();
        return $obj;
    }

    public function duplicar($debug = false) {
        $this->validatorExcluir();
        $codPerfilAnt = $this->getCodPerfil();
        if ($this->objDAO->duplicar($this, $debug)) {
            throw new OKException("Perfil cód: ".$codPerfilAnt." duplicada para ". $this->getCodPerfil() . " foi duplicada com sucesso.");
        } else {
            throw new Exception("Perfil cód:" . $codPerfilAnt . " não foi duplicada.");
        }
    }
    
    public function importarPerfis($debug = false) {
        if ($this->objDAO->importarPerfis($this, $debug)) {
            throw new OKException("Perfis cód: ".$this->getCodEventoAnt()." importados para ". $this->getCodEvento() . " com sucesso.");
        } else {
            throw new Exception("Perfis cód:" . $this->getCodEventoAnt() . " não foi importados.");
        }
    }
}

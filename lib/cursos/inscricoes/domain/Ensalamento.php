<?php

class Ensalamento extends EnsalamentoVO {
    

    private $objDAO;

    function __construct() {
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new EnsalamentoDAO();
    }
    
    public function ensalmentoExiste() {
        if ($this->objDAO->getById($this)){
            return true;
        }else{
            return false;
        }
    }
   
    function adicionarTrabalhoSessao(){
        if(!$this->ensalmentoExiste()){
            $this->validarAdicionar();
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Cód.Trabalho:" . $this->getCodTrabalho() . " adicionado a sessão: ".$this->getCodSessao().".");
            } else {
                throw new Exception("Cód.Trabalho:" . $this->getCodTrabalho() . " NÃO adicionado a sessão: ".$this->getCodSessao().".");
            }
        }else{
            throw new OKException("Ensalamento já existe.");
        }
    }
    
    function removerTrabalhoSessao(){
        if($this->ensalmentoExiste()){
            $this->validarRemover();
            if ($this->objDAO->delete($this, $debug)) {
                throw new OKException("Cód.Trabalho:" . $this->getCodTrabalho() . " removido da sessão: ".$this->getCodSessao().".");
            } else {
                throw new Exception("Cód.Trabalho:" . $this->getCodTrabalho() . " NÃO removido da sessão: ".$this->getCodSessao().".");
            }
        }else{
            throw new OKException("Ensalamento não existe.");
        }
    }
    public function carregarListaSalas($debug = false) {
        $this->objDAO->carregarListaSalasByIdEvento($this, $debug);
        if ($this->getListaSalas()) {
        } else {
            throw new Exception("Evento não possui salas liberadas.");
        }
    }
}
?>
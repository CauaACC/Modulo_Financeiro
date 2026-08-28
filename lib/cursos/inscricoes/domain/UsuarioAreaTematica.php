<?php

class UsuarioAreaTematica extends UsuarioAreaTematicaVO {
    

    private $objDAO;

    function __construct() {
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new UsuarioAreaTematicaDAO();
    }
   
    function adicionarUsuarioAreaTematica(){
        $this->validarAdicionar();
        if ($this->objDAO->insert($this, $debug)) {
            throw new OKException("Usuário:" . $this->getCodUsuario() . " adicionado no eixo: ".$this->getCodAreaTematica().".");
        } else {
            throw new Exception("Usuário:" . $this->getCodUsuario() . " NÃO adicionado no eixo: ".$this->getCodAreaTematica().".");
        }
    }
    
    function removerUsuarioAreaTematica(){
        $this->validarRemover();
        if ($this->objDAO->delete($this, $debug)) {
            throw new OKException("Usuário:" . $this->getCodUsuario() . " removido do eixo: ".$this->getCodAreaTematica().".");
        } else {
            throw new Exception("Usuário:" . $this->getCodUsuario() . " NÃO removido do eixo: ".$this->getCodAreaTematica().".");
        }        
    }

}
?>
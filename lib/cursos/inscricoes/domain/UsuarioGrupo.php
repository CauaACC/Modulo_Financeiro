<?php

class UsuarioGrupo extends UsuarioGrupoVO {
    

    private $objDAO;

    function __construct() {
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new UsuarioGrupoDAO();
    }
   
    function adicionarUsuarioGrupo(){
        $this->validarAdicionar();
        if ($this->objDAO->insert($this, $debug)) {
            throw new OKException("Usuário:" . $this->getCodUsuario() . " adicionado no grupo: ".$this->getCodGrupo().".");
        } else {
            throw new Exception("Usuário:" . $this->getCodUsuario() . " NÃO adicionado no grupo: ".$this->getCodGrupo().".");
        }
    }
    
    function removerUsuarioGrupo(){
        $this->validarRemover();
        if ($this->objDAO->delete($this, $debug)) {
            throw new OKException("Usuário:" . $this->getCodUsuario() . " removido do grupo: ".$this->getCodGrupo().".");
        } else {
            throw new Exception("Usuário:" . $this->getCodUsuario() . " NÃO removido do grupo: ".$this->getCodGrupo().".");
        }        
    }

}
?>
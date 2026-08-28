<?php

class UsuarioEvento extends UsuarioEventoVO {
    

    private $objDAO;

    function __construct() {
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new UsuarioEventoDAO();
    }

    function getUsuarioEventoById($debug = false){
        return $this->objDAO->getById($this, $debug);
    }
    
    function carregarListaUsuariosByIdEvento($debug = false){
        return $this->objDAO->getListaUsuariosByIdEvento($this, $debug);
    }

    function carregarListaEventosByIdLogin($debug = false){
        return $this->objDAO->getListaEventosByIdLogin($this, $debug);
    }
    
    function adicionarUsuarioEvento(){
        $this->validarAdicionar();
        if ($this->objDAO->insert($this, $debug)) {
            return new Result("Usuário:" . $this->getCodUsuario() . " adicionado no evento: ".$this->getCodEvento().".");
        } else {
            throw new Exception("Usuário:" . $this->getCodUsuario() . " NÃO adicionado no evento: ".$this->getCodEvento().".");
        }
    }
    
    function removerUsuarioEvento(){
        $this->validarRemover();
        if ($this->objDAO->delete($this, $debug)) {
            throw new OKException("Usuário:" . $this->getCodUsuario() . " removido do evento: ".$this->getCodEvento().".");
        } else {
            throw new Exception("Usuário:" . $this->getCodUsuario() . " NÃO removido do evento: ".$this->getCodEvento().".");
        }        
    }

    /**
     * @param array $escolhidos cod_usuario que a tela manteve marcados
     */
    /** Quem pode ser trazido do projeto de origem (para a tela conferir). */
    public function listarCandidatosImportacao($codOrigem, $debug = false) {
        return $this->objDAO->listarCandidatosImportacao($codOrigem, $debug);
    }

    public function importarUsuariosEvento($debug = false, $escolhidos = array()) {
        $this->setUsuariosSelecionados(is_array($escolhidos) ? $escolhidos : array());
        if ($this->objDAO->importarUsuariosEvento($this, $debug)) {
            throw new OKException("Usuários do evento: ".$this->getCodEventoAnt()." importados para ". $this->getCodEvento() . " com sucesso.");
        } else {
            throw new Exception("Usuários do evento: ".$this->getCodEventoAnt()." não foi importados.");
        }
    }
}

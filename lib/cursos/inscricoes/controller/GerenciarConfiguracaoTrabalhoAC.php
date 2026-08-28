<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of GerenciarConfiguracaoTrabalhoAC
 *
 * @author home
 */
class GerenciarConfiguracaoTrabalhoAC {

    function __construct() {
        $this->codEvento = COD_EVENTO;
        $this->carregarConfiguracao();
    }
    
    public static function getInstance()
    {
//        // Verifica se o objeto jã foi inicializado
        if (self::$_instance == null) {
//            // Cria uma nova instãncia da prãpria classe
            self::$_instance = new self();
        }      
        // Informa o objeto ãnico da classe Jogo
        return self::$_instance;
    }

    public function verficarPermissao($permissao = false){
        if(!$permissao){
            return false;
        }
        return $this->getOp.$permissao.'()';
    }
    
    public function carregarConfiguracao(){
       if($this->codEvento==6){
            $this->opPermiteAnexarApresentacao = false;
            $this->opPermiteAnexarVideo        = false;
       }
    }
}

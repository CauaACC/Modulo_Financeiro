<?php

/**
 * @param type $retorno  URL, STRING or MSG, EXCEPTION ou BOLLEAN
 * @throws Exception
 */
class ControleAcesso {

    public $verificaPerfil;
    private $perfilView;
    /**
     * classe instanciada em verificaPerfil.php disponível tanto em index.php quanto em index-action.php
     * @param type este é substuido por logado como em alguns casos, carregado em config.php
     * @param type $fwsLogado[permissoes] recebe $_SESSION['priv'], carregado em usuarioPerfil.php
     */
    private function __construct() {

    }

    /**
     * Metodos referente a tratamento de direito do usuário logado
     */
    public function setUserAndRole($fwsLogado = false, $fwsLogadoPrivilegios = false) {
        $verificaPerfil = verificaPerfil::getInstance();
        $verificaPerfil->setUserAndRole($fwsLogado, $fwsLogadoPrivilegios);
    }

    public function ifUserInRole($permissoesExigidas, $retorno) {
        $verificaPerfil = verificaPerfil::getInstance();
        return $verificaPerfil->ifUserInRole($permissoesExigidas,$retorno);
    }

    /**
     * verifica se possui todas as permissões declareadas
     * @param type $retorno URL, STRING or MSG, EXCEPTION ou BOLLEAN
     * @param type $retorno URL, STRING or MSG, EXCEPTION ou BOLLEAN
     */
    public function ifAllGranted($permissoesExigidas, $retorno) {
        $verificaPerfil = verificaPerfil::getInstance();
        $verificaPerfil->usuarioLogado($permissoesExigidas,$retorno);
    }

    /**
     * verifica se NÃO possui qualquer uma das permissões declaradas
     * @param type $retorno URL, STRING or MSG, EXCEPTION ou BOLLEAN
     */
    public function ifNotGranted($permissoesExigidas, $retorno) {
        $verificaPerfil = verificaPerfil::getInstance();
        $verificaPerfil->usuarioLogado($permissoesExigidas,$retorno);
    }

    /**
     * verifica se possui qualquer um dos perfis declarados
     * @param type $retorno URL, STRING or MSG, EXCEPTION ou BOLLEAN
     */
    public function ifAnyGranted($permissoesExigidas, $retorno) {
        $verificaPerfil = verificaPerfil::getInstance();
        $verificaPerfil->ifAnyGranted($permissoesExigidas,$retorno);
    }

    /**
     * @param type $retorno  URL, STRING or MSG, EXCEPTION ou BOLLEAN
     * @throws Exception
     */
    public function usuarioLogado($retorno = 'URL') {
        /* Verificar se esta Logado */
        $verificaPerfil = verificaPerfil::getInstance();
        $verificaPerfil->usuarioLogado($retorno);
    }
    
    public function retorno($retorno = 'URL') {
        /* Verificar se esta Logado */
        $verificaPerfil = verificaPerfil::getInstance();
        $verificaPerfil->retorno(false,$retorno);
    }
    
    public function alertControleAcesso(){
        echo "alertControleAcesso(data);";
    }
    
}
?>
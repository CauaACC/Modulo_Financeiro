<?php

class MenuAcesso extends MenuAcessoVO {
    private $objDAO;

    function __construct() {
        parent::__construct();
        $this->objDAO = new MenuAcessoDAO();
    }

    /**
     * So aceita caminho de view/controller do proprio sistema.
     * O que vem do cliente nunca entra no banco sem passar por aqui.
     */
    public static function chaveValida($chave) {
        if (!$chave || strlen($chave) > 150) {
            return false;
        }
        if (strpos($chave, '..') !== false) {
            return false;
        }
        if (!preg_match('#^(view|controller)/[A-Za-z0-9_\-/]+\.php$#', $chave)) {
            return false;
        }
        return file_exists(dirname(__FILE__) . '/../' . $chave);
    }

    public function registrar($debug = false) {
        if (!$this->getCodUsuario() || !$this->getCodEvento()) {
            return false;   // silencioso: e so contador, nunca atrapalha a navegacao
        }
        if (!self::chaveValida($this->getDesView())) {
            return false;
        }
        return $this->objDAO->registrar($this, $debug);
    }

    public function listarMaisUsados($limite = 4, $debug = false) {
        if (!$this->getCodUsuario() || !$this->getCodEvento()) {
            return array();
        }
        return $this->objDAO->getMaisUsados($this, $limite, $debug);
    }
}

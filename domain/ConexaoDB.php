<?php

class ConexaoDB {
    private static $_instance = null;
    private static $_conex = null;

    private function __construct($userDb = false) {
        $conex = new ADODBConnection($userDb, false);
        self::$_conex = $conex->getConnection();
    }

    public static function getInstance() {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public static function getConexaoDB() {
        return self::$_conex;
    }
}

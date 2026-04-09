<?php

class OKException extends Exception {};
class AvisoException extends Exception {};

class DAO {
    private $message;
    public $conex = null;

    public function __construct($userDb = false, $debug = false) {
        ConexaoDB::getInstance();
        $this->conex = ConexaoDB::getConexaoDB();
    }

    public function getMessage() {
        return $this->message;
    }

    public function setMessage($message) {
        $this->message = $message;
    }

    public function enviarMensagem($msgErro) {
        if ($this->conex) {
            $this->conex->RollbackTrans();
        }
        throw new Exception($msgErro);
    }
}

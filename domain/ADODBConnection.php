<?php
/**
 * Conexão ADODB simplificada.
 */
require_once(dirname(__FILE__) . "/../lib/adodb-5.22.4/adodb.inc.php");
require_once(dirname(__FILE__) . "/../lib/adodb-5.22.4/tohtml.inc.php");
require_once(dirname(__FILE__) . "/../lib/adodb-5.22.4/adodb-exceptions.inc.php");

class ADODBConnection {

    public $host;
    public $user;
    public $pass;
    public $db;
    public $type;
    public $charset;
    public $con;

    public function __construct($user = false, $persistent = false) {
        $this->host    = "localhost:3306";
        $this->user    = "root";
        $this->pass    = "";
        $this->db      = "modulo_financeiro";
        $this->type    = "mysqli";
        $this->charset = "utf8";
    }

    public function getConnection() {
        $this->con = ADONewConnection($this->type);
        $this->con->Connect($this->host, $this->user, $this->pass, $this->db);
        $this->con->Execute("set names 'utf8'");
        return $this->con;
    }
}
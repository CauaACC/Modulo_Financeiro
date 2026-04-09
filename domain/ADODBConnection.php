<?php
/**
 * Conexão ADODB simplificada.
 * Ajuste as credenciais conforme seu ambiente.
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
        switch ($_SERVER["SERVER_NAME"] ?? 'localhost') {
            case "localhost":
            case "eventos.test":
                $this->host = "localhost:3306";
                $this->user = "root";
                $this->pass = "";
                $this->db   = "opcte984_opctec";
                $this->type = "mysqli";
                break;
            case "opctec.net.br":
            case "www.opctec.net.br":
                $this->host = "localhost";
                $this->user = "opcte984_opctec";
                $this->pass = ""; // Preencher em produção
                $this->db   = "opcte984_opctec";
                $this->type = "mysqli";
                break;
            default:
                $this->host = "localhost:3306";
                $this->user = "root";
                $this->pass = "";
                $this->db   = "opcte984_opctec";
                $this->type = "mysqli";
                break;
        }
        $this->charset = "utf8";
    }

    public function getConnection() {
        $this->con = ADONewConnection($this->type);
        $this->con->Connect($this->host, $this->user, $this->pass, $this->db);
        $this->con->Execute("set names 'utf8'");
        return $this->con;
    }
}

<?php
// require_once(dirname(__FILE__)."/../lib/adodb-5.20.14/adodb-error.inc.php");
// #require_once(dirname(__FILE__)."/../lib/adodb-5.20.14/adodb-errorhandler.inc.php");
// require_once(dirname(__FILE__)."/../lib/adodb-5.20.14/adodb.inc.php");
// require_once(dirname(__FILE__)."/../lib/adodb-5.20.14/tohtml.inc.php");
// require_once(dirname(__FILE__)."/../lib/adodb-5.20.14/toexport.inc.php");
// require_once(dirname(__FILE__)."/../lib/adodb-5.20.14/adodb-exceptions.inc.php");
class ConexaoDB {
    //public $dbName="web";
//    public $dbName="WProexEventos";
    private static $_instance = null;
    private static $_conex = null;

//    public function ConexaoDAO(){
    private function __construct($userDb=false)
    {
        $userDb = NULL;
        $conex = new ADODBConnection($userDb,false);
        self::$_conex=$conex->getConnection();
        //return $this->conex;
        //$this->conex->BeginTrans();
        //$this->conex->CommitTrans();
        //$this->conex->RollbackTrans();
    }

    public static function getInstance()
    {
//        // Verifica se o objeto jã foi inicializado
        if (self::$_instance == null) {
//              if($_SERVER["REMOTE_ADDR"]=='189.90.70.51' or $_SERVER["REMOTE_ADDR"]=='189.90.70.56'){
//                  echo "Cria uma nova instancia<hr>";
//              }
//            // Cria uma nova instãncia da prãpria classe
            self::$_instance = new self();
        }
//          if($_SERVER["REMOTE_ADDR"]=='189.90.70.51' or $_SERVER["REMOTE_ADDR"]=='189.90.70.56'){
//              echo print_r(self::$_instance)."<hr>";
//          }         
        // Informa o objeto ãnico da classe Jogo
        return self::$_instance;
    }
    public static function getConexaoDB(){
        return self::$_conex;
    }
}

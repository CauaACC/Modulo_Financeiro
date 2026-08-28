<?php 
require_once(dirname(__FILE__) . '/../../inscricoes/include/config.php');
//Class DbConnect
class DbConnect
{
    //Variable to store database link
    private $con;
 
    //Class constructor
    function __construct()
    {
 
    }
 
    //This method will connect to the database
    function connect()
    {
        //Including the config.php file to get the database constants
        include_once dirname(__FILE__) . '/Config.php';
 
        //connecting to mysql database
        $this->con = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
 
        //Checking if any error occured while connecting
        if (mysqli_connect_errno()) {
            echo "Falha na conexão";
            $text = "Failed to connect to MySQL: " . mysqli_connect_error();
            executarLogApp($text, 'log');
        }
        executarLogApp('Conectado com sucesso', 'log');
        //finally returning the connection link 
        return $this->con;
    }
 
}
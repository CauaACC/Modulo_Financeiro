<?php

class DbOperation
{
    //Database connection link
    private $con;

    //Class constructor
    function __construct()
    {
        //Getting the DbConnect.php file
        require_once dirname(__FILE__) . '/DbConnect.php';

        //Creating a DbConnect object to connect to the database
        $db = new DbConnect();

        //Initializing our connection link of this class
        //by calling the method connect of DbConnect class
        $this->con = $db->connect();
    }

    //storing token in database 
    public function registerDeviceOld($email,$token){
//        if(!$this->isEmailExist($email)){
            $stmt = $this->con->prepare("INSERT INTO devices (email, token) VALUES (?,?) ");
            $stmt->bind_param("ss",$email,$token);
            if($stmt->execute())
                return 0; //return 0 means success
            return 1; //return 1 means failure
//        }else{
//            return 2; //returning 2 means email already exist
//        }
    }
    public function registerDevice($fws_){
            $sql = "INSERT INTO devices (cod_evento,email,token,platform,access_key,http_user_agent,ip)"
                    . " VALUES (".COD_EVENTO
                    . ",'".$fws_['email']."'"
                    . ",'".$fws_['token']."'"
                    . ",'".$fws_['platform']."'"
                    . ",'".$fws_['accessKey']."'"
                    . ",'".$_SERVER['HTTP_USER_AGENT']."'"
                    . ",'".$_SERVER["REMOTE_ADDR"]."')";
            executarLogApp('Executando sql:'.$sql, 'sql-log');
            $stmt = $this->con->prepare($sql);
            if($stmt->execute()){
                executarLogApp('Executando sql: sucess', 'log');
                return 0; //return 0 means success
            }else{
                executarLogApp('Executando sql: failure', 'log');
                return 1; //return 1 means failure
            }
    }

    //the method will check if email already exist 
    private function isEmailexist($email){
        $stmt = $this->con->prepare("SELECT id FROM devices WHERE email = ?");
        $stmt->bind_param("s",$email);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    //getting all tokens to send push to all devices
    public function getAllTokens(){
        $stmt = $this->con->prepare("SELECT token FROM devices");
        $stmt->execute(); 
        $result = $stmt->store_result();
        $tokens = array(); 
        while($token = $result->fetch()){
            array_push($tokens, $token['token']);
        }
        return $tokens; 
    }

    //getting a specified token to send push to selected device
    public function getTokenByEmail($email){
        $stmt = $this->con->prepare("SELECT token FROM devices WHERE email = ?");
        $stmt->bind_param("s",$email);
        $stmt->execute(); 
        $result = $stmt->store_result()->fetch();
        return array($result['token']);        
    }

    //getting all the registered devices from database 
    public function getAllDevices(){
        $stmt = $this->con->prepare("SELECT * FROM devices");
        $stmt->execute();
        $result = $stmt->store_result();
        return $result; 
    }
}
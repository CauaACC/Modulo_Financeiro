<?php
class OKException extends Exception {};
class Result extends Exception {};
class AvisoException extends Exception {};

class DAO{
    // irã receber uma conexão
    private $message;
    public $conex = null;
    public function getConexao(){
        return $this->conex;
    }
    public function  __construct($userDb=false,$debug=false) {
            ConexaoDB::getInstance();
            $this->conex=ConexaoDB::getConexaoDB();
            if($debug){
                echo "Teste instancia conexao->".print_r($this->conex)."<-<hr>";
            }            
    }
    // constructor
    public function getMessage() {
        return $this->message;
    }
    public function setMessage($message) {
        $this->message = $message;
    }
    public function enviarMensagem($msgErro)
    {
        $avisodeTela = $msgErro->msg;

        $msgErro="\nUsuário Logado:
            Login Logado: ".$_SESSION['_login']."
            Browser: ".$_SERVER['HTTP_USER_AGENT']."
            IP:".$_SERVER['REMOTE_ADDR']."\nMensagem de Log:\n ".$msgErro."\n";
        $abrevEvento = defined('ABREV_EVENTO') ? ABREV_EVENTO : 'unknown';
        $msgSubject="[".$abrevEvento."][".$_SERVER["SERVER_NAME"]."]".get_class($this);
        $msgTo="eventos@opctec.com.br";
        $txtLog = $msgSubject."\nAviso de Tela:".$avisodeTela.$msgErro."\n";
        executarLog($txtLog,"DAO",true);        
        if($_SERVER['HTTP_HOST'] != 'eventos.test'){ 
            $objEnviarErro = new enviarErro($msgTo,$msgSubject,$msgErro);
        }else{
            //echo "<hr>msgErro:<br><pre>".$msgErro."</pre><hr>";
        }
//        echo "<br>Ocorreu um problema durante a execução.";
//        echo "<br>O administrador do sistema foi comunicado do problema.";
//        echo "<br>";
        
        $this->conex->RollbackTrans();

        if(stristr($avisodeTela, "Duplicate")){
            $valor = '';
            $chave = '';
            if (preg_match("/Duplicate entry '([^']+)' for key '([^']+)'/i", $avisodeTela, $matches)) {
                $valor = $matches[1];
                $chave = $matches[2];
            }
            $msg = "Registro já cadastrado.";
            if ($valor) {
                $msg .= " Valor duplicado: <b>" . htmlspecialchars($valor) . "</b>";
            }
            throw new Exception($msg);
        }
        if ($_SERVER['HTTP_HOST'] == 'eventos.test') {
            throw new Exception("Erro SQL (local): " . $avisodeTela);
        }
        throw new Exception("Não foi possível executar o comando desejado.");
    }
}

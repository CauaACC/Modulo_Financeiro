<?php
/*
 * Local onde se define os sub-grupos de chapas
 */
require_once 'usuarioPerfil.php';

class VerificaPerfil {

    private static $_instance = null;
    public $msgPermissao = 'Usuário não possui permissão para executar esta aplicação';
    /**
     * classe instanciada em verificaPerfil.php disponível tanto em index.php quanto em index-action.php
     * @param type este é substuido por logado como em alguns casos, carregado em config.php
     * @param type $fwsLogado[permissoes] recebe $_SESSION['priv'], carregado em usuarioPerfil.php
     */
    public function __construct() {
    }
    
    public function getInstance() {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    public function setUserAndRole($fwsLogado = false, $fwsLogadoPrivilegios = false) {
        $this->fwsLogado = $fwsLogado;
        $this->fwsLogado['permissoes'] = $fwsLogadoPrivilegios;
    }
    
    /**
     * verifica se possui uma das permissões declaradas
     * @param type $permissoesExigidas testarSistema, desenvolvedorSistema,
     * administrarSistema,gerenciarEventos, coordenarEventos, avaliarEventos,
     * consultarEventos, consultarLogarComoURL
     * @param type $retorno URL, STRING or MSG, EXCEPTION ou BOLLEAN
     */
    public function ifUserInRole($permissoesExigidas, $retorno) {
        //echo $_SERVER['REMOTE_ADDR']=='189.90.70.56'? __LINE__."-".__FILE__."<br>" : "";
        $this->usuarioLogado($retorno);
        $acessar = $this->verificaPermissao($permissoesExigidas);
        return $this->retorno($acessar, $retorno);
    }

    /**
     * verifica se possui todas as permissões declareadas
     * @param type $retorno URL, STRING or MSG, EXCEPTION ou BOLLEAN
     * @param type $retorno URL, STRING or MSG, EXCEPTION ou BOLLEAN
     */
    public function ifAllGranted($permissoesExigidas, $retorno) {
        $this->usuarioLogado();
    }

    /**
     * verifica se NÃO possui qualquer uma das permissões declaradas
     * @param type $retorno URL, STRING or MSG, EXCEPTION ou BOLLEAN
     */
    public function ifNotGranted($permissoesExigidas, $retorno) {
        $this->usuarioLogado();
    }

    /**
     * verifica se possui qualquer um dos perfis declarados
     * @param type $retorno URL, STRING or MSG, EXCEPTION ou BOLLEAN
     */
    public function ifAnyGranted($permissoesExigidas, $retorno) {
        $this->usuarioLogado();
        $acessar = $this->verificaPermissao($permissoesExigidas);
        $this->retorno($acessar, $retorno);
    }

    /**
     * Verifica se $this->fwsLogado['permissoes'] possui as permissoes exigidas
     * para exibição ou utilização do recurso solicitado
     * @param type $permissoes
     */
    private function verificaPermissao($permissoesExigidas = false, $debug = false) {
        
        $arrayPermissoesExigidas = explode(',', $permissoesExigidas);
        $arrayPermissaoLogado = $this->fwsLogado['permissoes'];
        
        if ($debug) {
            echo "permissões Logado:<br>";
            print_r($arrayPermissaoLogado);
            echo "<hr>";
            echo "permissões Exigidas:<br>";
            print_r($arrayPermissoesExigidas);
            echo "<hr>";
        }
        /**
         * Percorrendo array de permissões solicitadas $arrayPermissoesExigidas e
         * jogando seus valores como indices do array de permissões do usuario 
         * logado $arrayPermissaoLogado, caso o usuario logado possua ao menos uma 
         * das permissões o sistema retorna true
         */
        foreach ($arrayPermissoesExigidas as $value) {
            if ($arrayPermissaoLogado[$value]==1) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param type $retorno  URL, STRING or MSG, EXCEPTION ou BOLLEAN
     * @throws Exception
     */
    public function usuarioLogado($retorno = 'URL') {
        /* Verificar se esta Logado */
        $this->retorno($this->fwsLogado["Logged"], $retorno);
    }

    public function retorno($acessar = false, $retorno = 'URL') {
        $retorno = strtoupper($retorno);
//        echo "->".$acessar."<-<hr>";
        if ($acessar != 1) {
//            try {
//                throw new Exception('teste');
//            } catch (Exception $exc) {
//                showException($exc);
//                exit();
//            }
            
            if ($retorno == 'URL') {
                ?>
                <script>
                    window.location = '<?php echo WB_URL_VIEW ?>&pagina=acessoNegado.php';
                </script>
                <?php
                echo $this->msgPermissao.".<br>";
                exit();
            } else if ($retorno == 'STRING' or $retorno == 'MSG') {
                echo $this->msgPermissao;
                exit();
            } else if ($retorno == 'ALERT') {
                ?>
                <script>
                    alert('<?php echo $this->msgPermissao?>');
                </script>
                <?php
                exit();
            } else if ($retorno == 'JSON') {
                echo '{"controleAcessoStatus":"0","controleAcessoMsg":"'.$this->msgPermissao.'"}';
                exit();
            } else if ($retorno == 'EXCEPTION') {
                throw new Exception($this->msgPermissao);
            } else if ($retorno == 'BOOLEAN') {
                return false;
                exit();
            } else {
                echo 'forma de retorno não especificado<br>';
                return false;
                exit();
            }
        } else {
            if ($retorno == 'BOOLEAN') {
                return true;
            }
        }
    }
}

/*
 * Libera o funcionamento do sistema para quem esta definido como administrador,
 * mesmo que estiver em modo de manutenção.
 */
if($administrarSistema){
    $FWS_MANUTENCAO=false;
//    error_reporting(E_ALL ^ E_NOTICE);
//    ini_set('display_errors','On');
}
/*
 * Testa modo de manutenção
 * Não esta redirecionando para o arquivo com mensagem de manutenção, fazer funcionar.
 */
if($FWS_MANUTENCAO){
    $conteudo = "manutencao.php";
}
$menuMigra=false;

function controleAcesso($acessar=false,$retorno='URL'){
//    echo "priv:";
//    print_r($_SESSION['priv']);echo "<hr>privteste:";
//    print_r($_SESSION['privteste']);
    if($acessar!=1){
        ControleAcesso::retorno($retorno);
    }else{
        if($retorno=='BOOLEAN'){
            return true;
        }
    }
}
/*
 * @parameter privilegio, (STRING, EXCEPTION, BOOLEAN)
 */
function controleAcessoPrivilegio($privilegio, $retorno = 'URL') {
    $acessar = $_SESSION['priv'][$privilegio];
    return controleAcesso($acessar, $retorno);
}

/**
 * 
 * @param type $conteudo conteudo da variavel $_GET[pagina] ou $_POST[pagina] 
 * WB_PATH_SECURITY constante definida em config.php
 * função chamada me index.php e index-action.php
 */
function controleAcessoPorDiretorio($conteudo){
    $conteudo = explode("/", $conteudo);
    $arrayDir = explode(",", WB_PATH_SECURITY);
    $findme   = $conteudo[0]."/".$conteudo[1];
    if (in_array($findme, $arrayDir)) {
        controleAcesso::usuarioLogado();
    }
}

ControleAcesso::setUserAndRole($fwsLogado,$_SESSION['priv']);
?>
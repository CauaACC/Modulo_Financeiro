<?php
class Perspectiva {

    private static $_instance = null;
    private $perfilView;

    /**
     * classe instanciada em verificaPerfil.php disponível tanto em index.php quanto em index-action.php
     * @param type este é substuido por logado como em alguns casos, carregado em config.php
     * @param type $fwsLogado[permissoes] recebe $_SESSION['priv'], carregado em usuarioPerfil.php
     */
    protected function __construct() {

    }

    public function getInstance() {
        if (self::$_instance == null) {
            self::$_instance = unserialize($_SESSION['objPerspectiva']);
        }
        if (self::$_instance == null) {
            self::$_instance = new self();    
        }
        return self::$_instance;
    }
    
    function ifInPerfilView($perspectivaExigida=false) {
        
        $arrayPerspectivaExigida = explode(',', $perspectivaExigida);
        
        if (!$perspectivaExigida) {
            echo 'perpectiva exigida não foi informada.<br>';
            exit();
        }
        if (count($arrayPerspectivaExigida) > 0) {
            foreach ($arrayPerspectivaExigida as $value) {
                if (self::getInstance()->getPerfilView() == $value) { 
                    return true;
                }
            }
        } else {
            if (self::getInstance()->getPerfilView() == $perspectivaExigida) {
                return true;
            }
        }
        return false;
    }
    
    function ifInPerfilViewList($perspectivasExigidas = false, $debug = false) {
        if(stristr($perspectivasExigidas, self::getInstance()->getPerfilView())){
            return true;
        }
        echo 'perpectiva exigida não foi informada.<br>';
        exit();
        return false;
    }
    
    function getPerfilView() {
        return self::getInstance()->perfilView;
    } 
    
    function setPerfilView($perfilView) {
        $objPerspectiva = self::getInstance();
        $objPerspectiva->perfilView = $perfilView;
        $_SESSION['objPerspectiva'] = serialize($objPerspectiva);
    }
    
    /**
     * Verifica o dominio do arquivo de origem
     * barra normal / se servidor for  linux
     * duas contra barras para se servidor for windows
     * @param __FILE__ arquivo de origem
     * @return String com a perspectiva
     */
    function setPerfilViewByLocalFile($arquivoOrigem, $debug = false) {
        echo($debug ? $arquivoOrigem . "<hr>" : "");
        switch (true) {
            case (stristr($arquivoOrigem, "/view/admin/") or stristr($arquivoOrigem, "\\view\\admin\\")):
                echo($debug ? __LINE__ . " admin<hr>" : "");
                self::getInstance()->setPerfilView('perspectivaAdministrador');
                break;
            case (stristr($arquivoOrigem, "/view/owner/") or stristr($arquivoOrigem, "\\view\\owner\\")):
                echo($debug ? __LINE__ . " owner<hr>" : "");
                self::getInstance()->setPerfilView('perspectivaCoordenador');
                break;
            default:
                echo($debug ? "->" . stristr($arquivoOrigem, "\\view\\admin\\") . "<-<hr>" : "");
                echo($debug ? __LINE__ . " default<hr>" : "");
                break;
        }
    }

}
?>
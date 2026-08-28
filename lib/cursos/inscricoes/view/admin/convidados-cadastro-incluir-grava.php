<?php
require_once(dirname(__FILE__)."/../../include/config.php");
$debug=false;
echo $debug? __line__.'-' : '';
require_once(dirname(__FILE__)."/../../view/admin/convidados-seguranca.php");
echo $debug? __line__.'-' : '';
try {
    $objConvidado = new Convidado();
    echo $debug? __line__.'-' : '';
    if($fws_['cpf']!=''){
        echo $debug? __line__.'-' : '';
        $objConvidado->setCpf($fws_['cpf']);
        $objConvidado->getConvidadoByCpf($debug);
        echo $debug? __line__.'-' : '';
    }else{
        echo $debug? __line__.'-' : '';
        $objConvidado->setEMail($fws_['e_mail']);
        $objConvidado->getConvidadoByEmail($debug);
        echo $debug? __line__.'-' : '';
    }
    if($objConvidado->getCodConvidado()==''){
        echo $debug? __line__.'-' : '';
        try {
            if ($fws_['novoCodConvidado'] == 0) {
                $fws_['codConvidado'] = NULL;
            }
            $isForm = false;
            $fws_['cpf'] = formatCpfInsert($fws_['cpf']);
            $objConvidado->setAll($fws_, $isForm);
            echo $debug? __line__.'-' : '';
            $objConvidado->salvar($debug);
            echo $debug? __line__.'-' : '';
        } catch (OkException $e) {
            echo $debug? __line__.'-' : '';
            $_SESSION['MSG'] = $e->getMessage();
            //$CONTENT = "view/admin/convidados-cadastro-ok.php";
            $CONTENT = "view/admin/convidados-cadastro-ok-listagem-grupos.php";
        } catch (Exception $e){
            $_SESSION['MSG'] = $e->getMessage();
            echo $debug? __line__.'-' : '';
            $CONTENT = "view/admin/convidados-cadastro-incluir.php";
        }
        $URL = WB_URL_VIEW."?content=".$CONTENT."&COD_CONVIDADO=".$objConvidado->getCodConvidado();
    }else{
        echo $debug? __line__.'-' : '';
        $MSG     = "Cpf e/ou E-mail já cadastrado em outro usuário";
        $_SESSION['MSG'] = $MSG;
        $CONTENT = "view/admin/convidados-cadastro-ok.php";
        $URL = WB_URL_VIEW."?content=".$CONTENT."&COD_CONVIDADO=".$objConvidado->getCodConvidado();
    }
} catch (Exception $exc) {
    $_SESSION['MSG'] = $exc->getMessage();
    echo $debug? $_SESSION['MSG'] : '';
    $CONTENT = "view/admin/convidados-cadastro-ok-listagem-grupos.php";
    $URL = WB_URL_VIEW."?content=".$CONTENT."&COD_CONVIDADO=".$objConvidado->getCodConvidado();
}

echo $debug? __line__.'-' : '';
$URL = $URL."&cod_usuario=".$fws_['cod_usuario'];
echo "<br><a href='{$URL}'>{$URL}</a><br>";
if(!$debug){
    echo  "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"1; URL=".$URL."\"></HTML>";
}
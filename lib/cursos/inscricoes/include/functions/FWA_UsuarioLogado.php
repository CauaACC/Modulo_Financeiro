<?php
Function FWA_UsuarioLogado($cod_usuario, $redirect = false){#Testa se o usuario esta logado ou nao.
  #echo $cod_usuario."<br>";
  $URL = WB_URL_VIEW . "?content=view/admin/login.php";
  If (@$cod_usuario!=""){
    #echo "Usuário está logado!<br>";
    return true;
    #exit;
    #break;
  }Else{
//    #echo "Usuário não está logado!<br>";
//    return false;
//    #exit;
//    #break;
    If ((isset($redirect) == true)and ( empty($redirect) == true)) {# se estiver difinido redireciona para tela de login
    } Else {
        echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $URL . "\"></HTML>";
        exit();
    }
    return false;
  }
}

<?php
Function FWA_BarraLogin(){   
  If(@$_SESSION['_nome_usuario']!=""){
    echo "|&nbsp;&nbsp;<a class='link' href='http://".FWA_Dominio().FWA_URL_Prefixo()."/view/admin/logout_acao.php'><b>Logout</b></a>";
    echo "&nbsp;&nbsp;|&nbsp;&nbsp;";
    echo "<b>Usuário Logado: ".$_SESSION['_cod_usuario']." - ".$_SESSION['_nome_usuario']."</b>";
  }Else{
    If(@$_SESSION['_login']!=""){
        echo "|&nbsp;&nbsp;<a class='link' href='http://".FWA_Dominio().FWA_URL_Prefixo()."/view/admin/logout_acao.php'><b>Logout</b></a>";
        echo "&nbsp;&nbsp;|&nbsp;&nbsp;";
      echo "<b>Usuário Logado: ".$_SESSION['_login']."</b>";
    }Else{
        echo "|&nbsp;&nbsp;<a class='link' href='http://".FWA_Dominio().FWA_URL_Prefixo()."/index.php?content=view/admin/login.php'><b>Login</b></a>";
        echo "&nbsp;&nbsp;|&nbsp;&nbsp;";
        echo "<b>Não há Usuário Logado: </b>";
    }
  }
}
?>

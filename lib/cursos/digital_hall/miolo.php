<?php
    include_once('view/inscricoes_congresso_hall_digital.php');
    exit();
    If(FWA_Arquivo_Existe($content,0)){
      include_once($content);
    }Else{
        include_once('view/anais/anais_pesquisa.php');
//      echo "<h3>Atenção</h3><p>Arquivo não encontrado.
//          <p>Provavelmente você está tentando acessar um link quebrado.
//          <p>Use o menu ao lado para encontrar a informação de seu interesse,
//          ou entre em contato conosco utilizando o email no rodape desta página.\n";
    }
    
    If(substr($content,0,6)=="view/admin/"){
      echo "</td></tr></table>";
    }    
  ?>
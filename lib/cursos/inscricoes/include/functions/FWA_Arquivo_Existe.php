<?php

Function FWA_Arquivo_Existe($FWF_Arquivo, $FWF_MSG) {

    $FWF_Arquivo = addslashes($FWF_Arquivo);
    $FWF_MSG = addslashes($FWF_MSG);
    If (FILE_EXISTS($FWF_Arquivo) != 1) {
        /*
          Não esta funcinando direito,
          estou trabalhando apenas com a mensagem logica.
          ($FWF_MSG!=1)
         */
        If ($FWF_MSG == 1) {
            $FWA_Arquivo_Existe = "<p>O arquivo não foi encontrado.
          <p>Provavelmente você está tentando acessar um link quebrado.
          <p>Utilize o menu para encontrar a informação de seu interesse, 
          ou entre em contato conosco.\n";
        } Else {
            $FWA_Arquivo_Existe = false;
        }
    } Else {
        #If($FWF_MSG==1){
        #  $FWA_Arquivo_Existe = "<p>O arquivo ".$FWF_Arquivo." Existe.\n";
        #}Else{
        $FWA_Arquivo_Existe = true;
        #}
    }
    return $FWA_Arquivo_Existe;
}

?>
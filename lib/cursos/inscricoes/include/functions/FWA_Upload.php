<?php
Function FWA_Upload($Var_Nome_Final_Arquivo, $Var_Diretorio, $Var_Permissao, $Var_Imprimir ,$Var_caminho_arquivo, $Var_arquivo, $Var_arquivo_name, $Var_arquivo_size, $Var_arquivo_type){
  
  #@$FWA_Debug=true;
  echo $arquivo;

  $arquivo      = $Var_arquivo;
  $arquivo_name = $Var_arquivo_name;
  $arquivo_size = $Var_arquivo_size;
  $arquivo_type = $Var_arquivo_type;


  #Variaveis criadas pelo proprio php de acordo com o nome do campo definido no formulário
  /*
    echo "Nome do Arquivo Temporario : ".$arquivo."            <br>";
    echo "Nome do Arquivo            : ".$arquivo_name."       <br>";
    echo "Tamanho do Arquivo         : ".$arquivo_size."- bytes<br>";
    echo "Tipo do Arquivo            : ".$arquivo_type."       <br>";
  */
  #Variaveis definidas pelo usuário
  #  echo $Var_Nome_Final_Arquivo."<br>";
  #  echo $Var_Diretorio."<br>";
  #  echo $Var_Permissao."<br>";
  #  echo $Var_Imprimir."<br>";

  If ((@!empty($arquivo))and($arquivo!="")){

      #Abrindo o arquivo temporario criado no servidor
      $IdLer     = FOPEN($arquivo,"r");

      #Lendo o arquivo aberto e atribuindo seu valor a variavel conteudo
      $conteudo  = FREAD($IdLer,$arquivo_size);

      #Imprimindo o conteudo do arquivo aberto
      If(@$Var_Imprimir==true){
        echo $conteudo;
      }

      #Define nome q o arquivo recebera ao ser recebido pelo servidor,
      #Comente esta variavel para que o arquivo seja gravado com seu nome original.
      If(@$Var_Nome_Final_Arquivo){
        $arquivo_name = $Var_Nome_Final_Arquivo;
      }

      /*
      Parametros para abrir um arquivo
        r   -> Abre o arquivo com permissão apenas para leitura.
        r+  -> Abre o arquivo com permissão para escrita e leitura, posicionando o ponteiro no início do mesmo.
        w   -> Abre o arquivo com permissão apenas para escrita.    Se o arquivo existir, todo o conteúdo é apagado. Se não existir, o PHP tenta criá-lo. O ponteiro é posicionado no início do arquivo
        w+  -> Abre o arquivo com permissão para escrita e leitura. Se o arquivo existir, todo o conteúdo é apagado. Se não existir, o PHP tenta criá-lo. O ponteiro é posicionado no início do arquivo
        a   -> Abre o arquivo com permissão apenas para escrita.    Se o arquivo não existir, o PHP tenta criá-lo. O ponteiro é posicionado no final do arquivo
        a+  -> Abre o arquivo com permissão para escrita e leitura. Se o arquivo não existir, o PHP tenta criá-lo. O ponteiro é posicionado no final do arquivo.
      */

      #Criando um novo arquivo ou abrindo um existente, de acordo com o parametro definido pelo usuário, de arcordo com a tabela acima.
      $IdGravar = FOPEN($Var_Diretorio."/".$arquivo_name,$Var_Permissao);

      #Fazendo a Gravação do arquivo propriamente dita.
      FWRITE($IdGravar,$conteudo);

      #Fecha os arquivos abertos
        FCLOSE($IdGravar); #Fecha arquivo definitivo gravado
        FCLOSE($IdLer);    #Fecha arquivo temporario de leitura

      #Agora preciso ver se o arquivo existe.
      #echo $Var_Diretorio."/".$arquivo_name;

      #echo file_exists($Var_Diretorio."/".$arquivo_name);

      If (file_exists($Var_Diretorio."/".$arquivo_name)){
        If(@$FWA_Debug==1){ echo "funcionou"; }
        return true;
      }Else{
        If(@$FWA_Debug==1){ echo "não funcionou"; }
        return false;
      }
  }Else{
      If(@$FWA_Debug==1){ echo "não funcionou, variavel arquivo não foi definida"; }
      return false;
  }
}
?>

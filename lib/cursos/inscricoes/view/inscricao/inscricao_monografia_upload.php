<?php
/*
#Variaveis criadas pelo proprio php de acordo com o nome do campo definido no formulário

#  echo "Nome do Arquivo Temporario : ".$arquivo."            <br>";
#  echo "Nome do Arquivo            : ".$arquivo_name."       <br>";
#  echo "Tamanho do Arquivo         : ".$arquivo_size."- bytes<br>";
#  echo "Tipo do Arquivo            : ".$arquivo_type."       <br>";

#Variaveis definidas pelo usuário
#  echo $Var_Nome_Final_Arquivo."<br>";
#  echo $Var_Diretorio."<br>";
#  echo $Var_Permissao."<br>";
#  echo $Var_Imprimir."<br>";
*/
/*
Parametros para abrir um arquivo
  r   -> Abre o arquivo com permissão apenas para leitura.
  r+  -> Abre o arquivo com permissão para escrita e leitura, posicionando o ponteiro no início do mesmo.
  w   -> Abre o arquivo com permissão apenas para escrita.    Se o arquivo existir, todo o conteúdo é apagado. Se não existir, o PHP tenta criá-lo. O ponteiro é posicionado no início do arquivo
  w+  -> Abre o arquivo com permissão para escrita e leitura. Se o arquivo existir, todo o conteúdo é apagado. Se não existir, o PHP tenta criá-lo. O ponteiro é posicionado no início do arquivo
  a   -> Abre o arquivo com permissão apenas para escrita.    Se o arquivo não existir, o PHP tenta criá-lo. O ponteiro é posicionado no final do arquivo
  a+  -> Abre o arquivo com permissão para escrita e leitura. Se o arquivo não existir, o PHP tenta criá-lo. O ponteiro é posicionado no final do arquivo.
*/

$data_recebimento = Date("Y-m-d");
$Var_Nome_Final_Arquivo = COD_EVENTO."-".$_SESSION['cod_inscrito'].".doc";
$Var_Diretorio          = "upload/monografia";
$Var_Permissao          = "w";
$Var_Imprimir           = false;
$Var_caminho_arquivo    = $Var_Diretorio."/".$Var_Nome_Final_Arquivo;

$Var_Tipo_Termitido     = "application/msword";

#echo $arquivo_type."<br>";
echo "<fieldset><legend> Arquivo Monografia </legend><blockquote>";
If ($arquivo_type==$Var_Tipo_Termitido){

#----------------------------------------------------------------------------
#Efetuando o upload do arquivo e testando se o mesmo foi efetuado com sucesso;
#----------------------------------------------------------------------------
  If(FWA_Upload($Var_Nome_Final_Arquivo
               ,$Var_Diretorio
               ,$Var_Permissao
               ,$Var_Imprimir
               ,$Var_caminho_arquivo
               ,$arquivo
               ,$arquivo_name
               ,$arquivo_size
               ,$arquivo_type
               )==1){

    echo "<a href='".$Var_caminho_arquivo."' target='blank' class='linkv'>Para abrir arquivo anexado, clique aqui</a><br>";
  }Else{
    echo "Atenção, ocorreu um erro no envio de seu arquivo<br>";
  }
#----------------------------------------------------------------------------
}Else{
  echo "Atenção, ocorreu um erro no envio de seu arquivo<br>";
  echo "Atenção, anexar apenas arquivos com extensão \".DOC\" (Microsoft Word)<br>";
}
if($_SESSION['content']=="view/inscricao/inscricao_monografia_confirmar.php"){
  echo "<br>Para trocar o arquivo anexo, clique em Editar Monografia<br><br>";
}
echo "</blockquote></fieldset>";
?>



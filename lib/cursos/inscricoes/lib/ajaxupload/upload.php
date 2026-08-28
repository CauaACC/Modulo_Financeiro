<?php
/**
 * $_FILES["file"]["error"]         # Error
 * $_FILES["file"]["name"]          # Nome do arquivo
 * $_FILES["file"]["type"]          # Tipo do arquivo
 * $_FILES["file"]["size"]          # Tamanho (em bytes)
 * $_FILES["file"]["tmp_name"]      # Caminho onde o arquivo foi armazenado temporariamente
 */

/**
 * Restrições do arquivo:
 * Não pode ser vazio
 * Não pode ser de uma extensão diferente da informada
 * Não pode ultrapassar o tamanho máximo
 */

$valido = 1;
$validoNome = true;
$validoExtensao = true;
$validoTamanho = true;
$result = 0;
$caminho = $_POST["caminho"];
$pasta = $_POST["pasta"];
$tagName = $_POST["tag_name"];
$nomeArquivo = $_POST["nome_arquivo"];
$listaExtensoes = $_POST["lista_extensoes"];
$maxFileSize = $_POST["MAX_FILE_SIZE"];

// Se existe um arquivo
if(isset($_FILES[$tagName."_file"])) {
    #echo "<script language='javascript'>alert('Arquivo:".$_FILES[$tagName."_file"]."');</script>";
    $mensagem = "<ul style='text-align: justify'>";
    /*
     * Validar o Nome, se ele foi carregado
     */
    if($_FILES[$tagName."_file"]['name'] == "") {
        $validoNome = false;
        $valido = 0;
        $mensagem .= "<li>Nenhum arquivo foi selecionado 2.</li>";
    } else {
        /*
         * Validar a extensão, se ela está dentro da lista de extensões permitidas
         */
        $extensaoArquivo = array_reverse(explode(".", $_FILES[$tagName."_file"]['name']));
        $extensaoArquivo = strtolower($extensaoArquivo[0]);        
        $verificaExtensao = false;

        $extensao = explode(";", $listaExtensoes);
        foreach ($extensao as $ext) {
            if($extensaoArquivo == $ext) {
                $verificaExtensao = true;
            }
        }
        if(!$verificaExtensao) {
            $validoExtensao = false;
            $valido = 0;
            $mensagem .= "<li>Arquivo com extensão ".strtoupper($extensaoArquivo).", não permitida.</li>";
        }    
        /*
         * Validar o tamanho, se ele está dentro do tamanho permitido
         */        
        if($_FILES[$tagName."_file"]["size"] > $maxFileSize) {
            $validoTamanho = false;
            $valido = 0;
            $mensagem .= "<li>Arquivo com tamanho ".number_format(($_FILES[$tagName."_file"]["size"]/1024), 2)." KB, maior que o permitido.</li>";
        }
    }
    $mensagem .= "</ul>";

    /**
     * Armazenamento do arquivo em si
     */
    if($validoNome && $validoExtensao && $validoTamanho) {
        $arquivo = $nomeArquivo.".".$extensaoArquivo;
        $targetPath = $caminho.$pasta.$arquivo;
        #echo "<script language='javascript'>alert('Arquivo 1:".$targetPath."');</script>";
        if(move_uploaded_file($_FILES[$tagName."_file"]['tmp_name'], $targetPath)) {
            $result = 1;
            #echo "<script language='javascript'>alert('Arquivo 2:".$targetPath."');</script>";
            /*
             * ATENÇÃO:
             * Este arquivo não é utilizado para realizar o upload de arquivos. Ele foi adicionado
             * Neste projeto para salvar no Banco de Dados o nome do arquivo aqui salvo
             * 
             */
            include_once("system/ic/business/salvar_arquivo.php");
            #echo "<script language='javascript'>alert('Arquivo 4:".$targetPath."');</script>";
            /**
             * Se inseriu, verificar se existia o mesmo aquivo (com o mesmo nome) mas
             * com uma extensão diferente da inserida (mas permitida)
             */
            $extensao = explode(";", $listaExtensoes);
            foreach ($extensao as $ext) {
                $arquivoRemover = $caminho.$pasta.$nomeArquivo.".".$ext;
                if(file_exists($arquivoRemover) && ($ext != $extensaoArquivo)) {
                    try {
                        unlink($arquivoRemover);
                    } catch (Exception $e) { }
                }                
            }
        } else {
            #echo "<script language='javascript'>alert('Arquivo 5:".$targetPath."');</script>";
            try {
                #echo "<script language='javascript'>alert('Arquivo 6:".$targetPath."');</script>";
                unlink($targetPath);
            } catch (Exception $e) {} #echo "<script language='javascript'>alert('Arquivo 7:".$targetPath."');</script>";
        }
        #echo "<script language='javascript'>alert('Arquivo 8:".$targetPath."');</script>";
        sleep(1);
    }
} else {
    $mensagem = "<ul><li>Nenhum arquivo foi selecionado 1.</li></ul>";
}

/**
 * Verifica se o arquivo já foi carregado anteriormente, caso não tenha sido possível
 * a adição do arquivo.
 */
$existe = 0;
if($result == 0) {
    $extensao = explode(";", $listaExtensoes);
    foreach ($extensao as $ext) {
        $arquivoVerifica = $caminho.$pasta.$nomeArquivo.".".$ext;
        if(file_exists($arquivoVerifica)) {
            $existe = 1;
            $arquivoExistente = $nomeArquivo.".".$ext;
        }
    }    
}
#echo "<script language='javascript'>alert('Arquivo 9:".$targetPath."');</script>";
?>
<!--
Parada do Processamento e Controle de resultados.
-->
<script language="javascript" type="text/javascript">
    window.top.window.stopUpload("<?=$result?>", "<?=$valido?>", "<?=$mensagem?>", 
        "<?=$tagName?>", "<?=$caminho.$pasta?>", "<?=$nomeArquivo.".".$extensaoArquivo?>",
        "<?=$existe?>", "<?=$arquivoExistente?>");
</script>
<?php
include_once("system/ic/admin/display_errors.php");

/**
 * Description of UploadFile
 *
 * @author ilhilario
 */
class UploadFile {

    /**
     * Cria um diretório no caminho especificado com a permissão "0777"
     * 
     * @param String $caminho Caminho que será criado o diretório. Começar e terminar 
     * com "/"
     * @param String $nomeDiretorio Nome do diretório
     * @return Boolean
     */
    public function criarDiretorio($caminho, $nomeDiretorio) {
        try {
            if(!file_exists($caminho.$nomeDiretorio)) {                
                if(mkdir($caminho.$nomeDiretorio, 0777)) {
                    return true;
                } else {
                    echo("Não foi possível criar o diretório no caminho especificado.
                    Favor entrar em contato com a administrador.");
                    return false;
                }
            }
        } catch (Exception $e) {
            echo($e->getCode().$e->getMessage());
        }
    }

    /**
     *
     * @param <type> $caminho
     * @param <type> $nomeDiretorio
     * @return <type> 
     */
    public function deleteDiretorio ($caminho, $nomeDiretorio) {
        try {
            if(file_exists($caminho.$nomeDiretorio)) {
                if(rmdir($caminho.$nomeDiretorio)) {
                    return true;
                } else {
                    echo("Não foi possível deletar o diretório no caminho especificado.
                    Favor entrar em contato com a administrador.");
                    return false;
                }
            }
        } catch (Exception $e) {
            echo($e->getMessage());
        }
    }    

    /**
     *
     * @param <type> $caminho
     * @param <type> $nomeArquivo
     * @return <type> 
     */
    public function deletarArquivo($caminho, $nomeArquivo) {
        try {
            return unlink($caminho.$nomeArquivo);
        } catch (Exception $e) {
            return $e;
        }
    }

    /**
     *
     * @param <type> $arquivo
     * @return <type> 
     */
    public function getFileExtension($arquivo) {
        $extensao = array_reverse(explode(".", $arquivo));
        $ext = strtolower($extensao[0]);
        return $ext;
    }

}
?>

<?php

class CriptografiaHelper {

    private static $cipher = 'aes-256-cbc';

    private static function getChave() {
        if (!defined('WB_CRIPTOGRAFIA_CHAVE')) {
            throw new Exception('Chave de criptografia nao configurada (WB_CRIPTOGRAFIA_CHAVE).');
        }
        return hash('sha256', WB_CRIPTOGRAFIA_CHAVE, true);
    }

    public static function criptografar($texto) {
        if ($texto === null || $texto === '') {
            return $texto;
        }
        $chave = self::getChave();
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length(self::$cipher));
        $criptografado = openssl_encrypt($texto, self::$cipher, $chave, OPENSSL_RAW_DATA, $iv);
        return base64_encode($iv . $criptografado);
    }

    public static function descriptografar($textoCriptografado) {
        if ($textoCriptografado === null || $textoCriptografado === '') {
            return $textoCriptografado;
        }
        $chave = self::getChave();
        $dados = base64_decode($textoCriptografado);
        if ($dados === false) {
            return $textoCriptografado;
        }
        $ivLength = openssl_cipher_iv_length(self::$cipher);
        if (strlen($dados) < $ivLength) {
            return $textoCriptografado;
        }
        $iv = substr($dados, 0, $ivLength);
        $criptografado = substr($dados, $ivLength);
        $resultado = openssl_decrypt($criptografado, self::$cipher, $chave, OPENSSL_RAW_DATA, $iv);
        if ($resultado === false) {
            return $textoCriptografado;
        }
        return $resultado;
    }

    public static function criptografarArquivo($caminhoOrigem, $caminhoDestino) {
        $conteudo = file_get_contents($caminhoOrigem);
        if ($conteudo === false) {
            throw new Exception('Erro ao ler arquivo para criptografar.');
        }
        $criptografado = self::criptografar($conteudo);
        if (file_put_contents($caminhoDestino, $criptografado) === false) {
            throw new Exception('Erro ao salvar arquivo criptografado.');
        }
        return true;
    }

    public static function descriptografarArquivo($caminhoCriptografado) {
        $conteudo = file_get_contents($caminhoCriptografado);
        if ($conteudo === false) {
            throw new Exception('Erro ao ler arquivo criptografado.');
        }
        return self::descriptografar($conteudo);
    }

}

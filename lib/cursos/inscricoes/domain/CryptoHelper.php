<?php

class CryptoHelper {

    private static $cipher = 'aes-256-cbc';

    private static function getKey() {
        if (!defined('WB_CRYPTO_KEY') || empty(WB_CRYPTO_KEY)) {
            throw new Exception('Chave de criptografia nao configurada (WB_CRYPTO_KEY).');
        }
        return hash('sha256', WB_CRYPTO_KEY, true);
    }

    public static function encrypt($plainText) {
        if ($plainText === null || $plainText === '') {
            return $plainText;
        }
        $key = self::getKey();
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length(self::$cipher));
        $encrypted = openssl_encrypt($plainText, self::$cipher, $key, 0, $iv);
        return base64_encode($iv . '::' . $encrypted);
    }

    public static function decrypt($encryptedText) {
        if ($encryptedText === null || $encryptedText === '') {
            return $encryptedText;
        }
        $key = self::getKey();
        $data = base64_decode($encryptedText);
        if ($data === false || strpos($data, '::') === false) {
            // Dado nao criptografado (legado), retorna como esta
            return $encryptedText;
        }
        list($iv, $encrypted) = explode('::', $data, 2);
        $decrypted = openssl_decrypt($encrypted, self::$cipher, $key, 0, $iv);
        if ($decrypted === false) {
            // Falha na descriptografia, retorna original
            return $encryptedText;
        }
        return $decrypted;
    }

    public static function gerarIniciais($nomeCompleto) {
        if (!$nomeCompleto) return '';
        $preposicoes = array('de', 'da', 'do', 'das', 'dos', 'e');
        $partes = explode(' ', trim($nomeCompleto));
        $iniciais = '';
        foreach ($partes as $parte) {
            $parte = trim($parte);
            if ($parte === '' || in_array(mb_strtolower($parte, 'UTF-8'), $preposicoes)) {
                continue;
            }
            $iniciais .= mb_strtoupper(mb_substr($parte, 0, 1, 'UTF-8'), 'UTF-8') . '.';
        }
        return $iniciais;
    }

}

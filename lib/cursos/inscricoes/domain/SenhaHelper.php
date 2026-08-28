<?php

class SenhaHelper {

    public static function hash($plainText) {
        return password_hash($plainText, PASSWORD_BCRYPT);
    }

    public static function verify($plainText, $hash) {
        return password_verify($plainText, $hash);
    }

    public static function isBcrypt($value) {
        return is_string($value) && strlen($value) >= 60 && strpos($value, '$2y$') === 0;
    }

    public static function gerarToken() {
        return bin2hex(random_bytes(32));
    }

    public static function gerarSenhaAleatoria($tamanho = 10) {
        $letras = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ';
        $numeros = '23456789';
        $especiais = '!@#$%&*';

        // Garantir pelo menos 1 de cada tipo
        $senha = '';
        $senha .= $letras[random_int(0, strlen($letras) - 1)];
        $senha .= strtoupper($letras[random_int(0, strlen($letras) - 1)]);
        $senha .= $numeros[random_int(0, strlen($numeros) - 1)];
        $senha .= $especiais[random_int(0, strlen($especiais) - 1)];

        // Preencher o restante
        $todos = $letras . $numeros . $especiais;
        for ($i = 4; $i < $tamanho; $i++) {
            $senha .= $todos[random_int(0, strlen($todos) - 1)];
        }

        // Embaralhar
        $arr = str_split($senha);
        shuffle($arr);
        return implode('', $arr);
    }

}

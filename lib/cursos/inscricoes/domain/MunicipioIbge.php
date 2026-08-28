<?php

/**
 * Resolve o codigo IBGE do municipio a partir de nome + UF.
 *
 * O cadastro do inscrito guarda cidade como texto livre e tab_opcoes_cidades
 * nao tem codigo IBGE, mas a NFS-e exige o codigo. Em vez de pedir o codigo ao
 * usuario (que ninguem sabe de cabeca), consultamos a Focus e guardamos em
 * cache de arquivo — municipio nao muda de codigo.
 */
class MunicipioIbge {

    private static $memoria = array();

    private static function getArquivoCache() {
        $dir = defined('WB_PATH_LOG_CACHE') ? WB_PATH_LOG_CACHE : sys_get_temp_dir() . '/';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        return rtrim($dir, '/\\') . '/municipios-ibge.json';
    }

    private static function carregarCache() {
        if (!empty(self::$memoria)) {
            return self::$memoria;
        }
        $arquivo = self::getArquivoCache();
        if (file_exists($arquivo)) {
            $conteudo = @file_get_contents($arquivo);
            $dados = json_decode($conteudo, true);
            if (is_array($dados)) {
                self::$memoria = $dados;
            }
        }
        return self::$memoria;
    }

    private static function gravarCache($chave, $codigo) {
        self::$memoria[$chave] = $codigo;
        @file_put_contents(self::getArquivoCache(),
            json_encode(self::$memoria, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }

    private static function montarChave($nome, $uf) {
        return strtoupper(trim($uf)) . '|' . self::normalizar($nome);
    }

    /**
     * Compara sem acento, sem caixa e sem pontuacao: "Sao Jose dos Pinhais",
     * "SÃO JOSÉ DOS PINHAIS" e "sao jose dos pinhais" sao o mesmo municipio.
     */
    private static function normalizar($texto) {
        $texto = trim((string) $texto);
        $de = array('á','à','ã','â','ä','é','è','ê','ë','í','ì','î','ï','ó','ò','õ','ô','ö','ú','ù','û','ü','ç','ñ');
        $para = array('a','a','a','a','a','e','e','e','e','i','i','i','i','o','o','o','o','o','u','u','u','u','c','n');
        $texto = str_replace($de, $para, mb_strtolower($texto, 'UTF-8'));
        $texto = str_replace(array('Á','À','Ã','Â','É','Ê','Í','Ó','Õ','Ô','Ú','Ç'),
                             array('a','a','a','a','e','e','i','o','o','o','u','c'), $texto);
        $texto = preg_replace('/[^a-z0-9 ]/', '', $texto);
        $texto = preg_replace('/\s+/', ' ', $texto);
        return trim($texto);
    }

    /**
     * @return string codigo IBGE de 7 digitos, ou '' se nao resolver.
     *         Nao lanca: municipio nao resolvido nao pode derrubar a emissao,
     *         quem chama decide se o campo e obrigatorio.
     */
    public static function resolver($nomeCidade, $uf, FocusNFeClient $client = null) {
        $nomeCidade = trim((string) $nomeCidade);
        $uf = strtoupper(trim((string) $uf));

        if ($nomeCidade == '' || strlen($uf) != 2) {
            return '';
        }

        $chave = self::montarChave($nomeCidade, $uf);
        $cache = self::carregarCache();
        if (isset($cache[$chave])) {
            return $cache[$chave];
        }

        if ($client === null) {
            try {
                $client = new FocusNFeClient();
            } catch (Exception $e) {
                return '';
            }
        }

        try {
            $codigo = self::consultarNaFocus($client, $nomeCidade, $uf);
        } catch (Exception $e) {
            return '';
        }

        if ($codigo != '') {
            self::gravarCache($chave, $codigo);
        }
        return $codigo;
    }

    private static function consultarNaFocus(FocusNFeClient $client, $nomeCidade, $uf) {
        $resposta = $client->consultarMunicipios($uf, $nomeCidade);
        if ($resposta['http'] >= 400 || !is_array($resposta['corpo'])) {
            return '';
        }

        $alvo = self::normalizar($nomeCidade);

        // A busca por nome e "contem": "Londrina" traz tambem "Nova Londrina".
        // So aceitamos correspondencia exata para nao emitir nota no municipio errado.
        foreach ($resposta['corpo'] as $mun) {
            if (!isset($mun['nome_municipio']) || !isset($mun['codigo_municipio'])) {
                continue;
            }
            if (self::normalizar($mun['nome_municipio']) === $alvo) {
                return (string) $mun['codigo_municipio'];
            }
        }

        return '';
    }

}

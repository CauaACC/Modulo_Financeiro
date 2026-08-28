<?php

/**
 * Normaliza endereco vindo de marketplace para o formato que a nota exige.
 *
 * Existe porque a planilha da Shopee entrega o endereco de um jeito que a
 * NF-e nao aceita: a UF vem por EXTENSO ("Sergipe") e o logradouro vem colado
 * com numero e complemento num campo so.
 */
class NormalizadorEndereco {

    /**
     * Nome do estado -> sigla. A nota exige sigla; a planilha manda o nome.
     */
    private static $ufs = array(
        'ACRE' => 'AC', 'ALAGOAS' => 'AL', 'AMAPA' => 'AP', 'AMAZONAS' => 'AM',
        'BAHIA' => 'BA', 'CEARA' => 'CE', 'DISTRITO FEDERAL' => 'DF',
        'ESPIRITO SANTO' => 'ES', 'GOIAS' => 'GO', 'MARANHAO' => 'MA',
        'MATO GROSSO' => 'MT', 'MATO GROSSO DO SUL' => 'MS', 'MINAS GERAIS' => 'MG',
        'PARA' => 'PA', 'PARAIBA' => 'PB', 'PARANA' => 'PR', 'PERNAMBUCO' => 'PE',
        'PIAUI' => 'PI', 'RIO DE JANEIRO' => 'RJ', 'RIO GRANDE DO NORTE' => 'RN',
        'RIO GRANDE DO SUL' => 'RS', 'RONDONIA' => 'RO', 'RORAIMA' => 'RR',
        'SANTA CATARINA' => 'SC', 'SAO PAULO' => 'SP', 'SERGIPE' => 'SE',
        'TOCANTINS' => 'TO'
    );

    /**
     * Aceita sigla ("SP") ou nome ("Sao Paulo" / "São Paulo").
     * @return string sigla, ou '' quando nao reconhecer
     */
    public static function resolverUf($valor) {
        $valor = trim((string) $valor);
        if ($valor == '') {
            return '';
        }

        // Ja e sigla?
        if (strlen($valor) == 2 && in_array(strtoupper($valor), self::$ufs, true)) {
            return strtoupper($valor);
        }

        $chave = self::semAcento($valor);
        return isset(self::$ufs[$chave]) ? self::$ufs[$chave] : '';
    }

    /**
     * Quebra o endereco de entrega em logradouro / numero / complemento.
     *
     * A Shopee monta o campo assim, separado por virgula:
     *   "Travessa Serigy, 49, Aracaju, Sergipe, 49085400"
     *   "Av Cd de Mte Cristo, 323, Casa, Suzano, Sao Paulo, 08670020"
     *
     * O fim da string repete cidade/UF/CEP, que JA vem em colunas proprias da
     * planilha — entao esses pedacos sao DESCARTADOS aqui em vez de
     * reinterpretados. Confiar na coluna e melhor do que adivinhar na string.
     *
     * O numero de partes varia (com e sem complemento), por isso o corte e
     * feito de tras para frente: tira CEP, UF e cidade do fim; o que sobra e
     * logradouro, numero e, se houver, complemento.
     *
     * @return array logradouro, numero, complemento, incerto (bool)
     */
    public static function quebrarLogradouro($enderecoCompleto, $cidade = '', $uf = '') {
        $vazio = array('logradouro' => '', 'numero' => '', 'complemento' => '', 'incerto' => true);

        $texto = trim((string) $enderecoCompleto);
        if ($texto == '') {
            return $vazio;
        }

        $partes = array_map('trim', explode(',', $texto));
        $partes = array_values(array_filter($partes, function ($p) { return $p !== ''; }));
        if (empty($partes)) {
            return $vazio;
        }

        // Tira do FIM o que ja temos em coluna propria: CEP, UF e cidade.
        $descartar = array();
        if (trim((string) $cidade) != '') { $descartar[] = self::semAcento($cidade); }
        if (trim((string) $uf) != '')     { $descartar[] = self::semAcento($uf); }

        while (count($partes) > 1) {
            $ultimo = $partes[count($partes) - 1];
            $soDigitos = preg_replace('/[^0-9]/', '', $ultimo);

            $ehCep = (strlen($soDigitos) == 8 && strlen(preg_replace('/[^0-9\-]/', '', $ultimo)) == strlen($ultimo));
            $ehLocal = in_array(self::semAcento($ultimo), $descartar, true)
                    || self::resolverUf($ultimo) != '';

            if ($ehCep || $ehLocal) {
                array_pop($partes);
                continue;
            }
            break;
        }

        $logradouro = array_shift($partes);
        $numero = '';
        $complemento = '';

        if (!empty($partes)) {
            $candidato = array_shift($partes);
            // Numero costuma ser o 2o pedaco, mas ha endereco sem numero —
            // nesse caso o pedaco e complemento, nao numero.
            if (preg_match('/^[0-9]+[A-Za-z]?$/', $candidato) || strtoupper($candidato) == 'S/N') {
                $numero = $candidato;
            } else {
                $complemento = $candidato;
            }
        }

        if (!empty($partes)) {
            $resto = implode(', ', $partes);
            $complemento = $complemento != '' ? $complemento . ', ' . $resto : $resto;
        }

        // "Incerto" sinaliza para a tela pedir conferencia humana: sem numero
        // a nota costuma ser rejeitada, e endereco de uma parte so nao foi
        // realmente quebrado.
        $incerto = ($numero == '');

        return array(
            'logradouro'  => $logradouro,
            'numero'      => $numero,
            'complemento' => $complemento,
            'incerto'     => $incerto
        );
    }

    /**
     * Telefone da planilha vem MASCARADO ("******54") — nao serve para nada e
     * nao e obrigatorio na nota. Devolve vazio nesse caso em vez de gravar
     * lixo que depois parece dado real.
     */
    public static function limparTelefone($telefone) {
        $telefone = trim((string) $telefone);
        if ($telefone == '' || strpos($telefone, '*') !== false) {
            return '';
        }
        return preg_replace('/[^0-9]/', '', $telefone);
    }

    public static function limparCep($cep) {
        return preg_replace('/[^0-9]/', '', (string) $cep);
    }

    /**
     * Tira acento e devolve em maiuscula, SEM depender de mbstring: a troca e
     * feita nos dois casos (minuscula e maiuscula) e, depois dela, so sobra
     * ASCII — ai o strtoupper simples ja basta. Evita quebrar em ambiente que
     * nao tenha a extensao carregada.
     */
    public static function semAcento($texto) {
        $texto = trim((string) $texto);
        $de = array(
            'á','à','ã','â','ä','é','è','ê','ë','í','ì','î','ï','ó','ò','õ','ô','ö','ú','ù','û','ü','ç','ñ',
            'Á','À','Ã','Â','Ä','É','È','Ê','Ë','Í','Ì','Î','Ï','Ó','Ò','Õ','Ô','Ö','Ú','Ù','Û','Ü','Ç','Ñ'
        );
        $para = array(
            'a','a','a','a','a','e','e','e','e','i','i','i','i','o','o','o','o','o','u','u','u','u','c','n',
            'A','A','A','A','A','E','E','E','E','I','I','I','I','O','O','O','O','O','U','U','U','U','C','N'
        );
        return strtoupper(str_replace($de, $para, $texto));
    }

}

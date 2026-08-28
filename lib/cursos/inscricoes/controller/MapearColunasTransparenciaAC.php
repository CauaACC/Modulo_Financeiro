<?php
/**
 * Mapeamento de colunas para carga de dados de transparência.
 *
 * Recebe o array bruto (explode do CSV) e retorna com as chaves nomeadas
 * que o domain (Transparencia) espera: numero_projeto, valor_bruto, etc.
 *
 * Chamado via método estático:
 *   MapearColunasTransparenciaAC::identificar($linha1, $arrayColunas, $tipoArquivo)
 */
class MapearColunasTransparenciaAC {

    /**
     * Ponto de entrada: identifica tipo e delega para o mapeamento correto.
     */
    public static function identificar($linha1, $arrayColunas, $tipoArquivo) {
        if ($tipoArquivo == 'CARGA_TRANSPARENCIA_PROJETOS') {
            return self::mapearProjetos($linha1, $arrayColunas);
        }
        if ($tipoArquivo == 'CARGA_TRANSPARENCIA_PAGAMENTOS') {
            return self::mapearPagamentos($linha1, $arrayColunas);
        }
        throw new Exception("Tipo de arquivo desconhecido: " . $tipoArquivo);
    }

    /**
     * PROJETOS: mapeamento por cabeçalho (detecta colunas pelo nome).
     * Suporta múltiplos formatos de planilha, com ou sem coluna vazia inicial.
     *
     * Formato A (coluna vazia inicial):
     *   ;Data inicio de projeto;Data fim do projeto;Eprotocolo;;Vínculo;Coordenador;Proposta do Projeto;nº Projeto
     *
     * Formato B (sem coluna vazia):
     *   Data inicio;Data fim;Eprotocolo;Tipo;Vínculo;Coordenador;Proposta;nº Projeto
     *
     * Fallback: posição fixa 0-7 (com detecção de offset se coluna 0 vazia)
     */
    private static function mapearProjetos($linha1, $arrayColunas) {
        $linha1 = trim($linha1);
        $linha1 = rtrim($linha1, ';');
        // Remover BOM UTF-8
        $linha1 = preg_replace('/^\xEF\xBB\xBF/', '', $linha1);
        // Converter para UTF-8 se necessário (arquivo pode ser Windows-1252/ISO-8859-1)
        if (!mb_check_encoding($linha1, 'UTF-8')) {
            $linha1 = mb_convert_encoding($linha1, 'UTF-8', 'Windows-1252');
        }
        $linha1Lower = mb_strtolower($linha1, 'UTF-8');
        $headerCols = explode(';', $linha1Lower);

        // Verificar se o cabeçalho contém nomes de colunas reconhecíveis
        $temCabecalho = false;
        foreach ($headerCols as $col) {
            $col = trim($col);
            if ($col !== '' && (
                strpos($col, 'data') !== false
                || strpos($col, 'projeto') !== false
                || strpos($col, 'coordenador') !== false
                || strpos($col, 'proposta') !== false
                || strpos($col, 'vinculo') !== false
                || strpos($col, 'vínculo') !== false
                || strpos($col, 'protocolo') !== false
            )) {
                $temCabecalho = true;
                break;
            }
        }

        if ($temCabecalho) {
            $arrayColunas['data_inicio']    = self::buscarPorNome($headerCols, $arrayColunas, 0, ['data inicio', 'data_inicio', 'dt inicio']);
            $arrayColunas['data_fim']       = self::buscarPorNome($headerCols, $arrayColunas, 0, ['data fim', 'data_fim', 'dt fim']);
            $arrayColunas['e_protocolo']    = self::buscarPorNome($headerCols, $arrayColunas, 0, ['eprotocolo', 'e-protocolo', 'e_protocolo', 'protocolo']);
            $arrayColunas['vinculo']        = self::buscarPorNome($headerCols, $arrayColunas, 0, ['vinculo', 'vínculo']);
            $arrayColunas['coordenador']    = self::buscarPorNome($headerCols, $arrayColunas, 0, ['coordenador']);
            $arrayColunas['proposta']       = self::buscarPorNome($headerCols, $arrayColunas, 0, ['proposta']);
            $arrayColunas['numero_projeto'] = self::buscarPorNome($headerCols, $arrayColunas, 0, ['n projeto', 'nº projeto', 'no projeto', 'numero projeto', 'numero_projeto', 'nr projeto', 'num projeto']);

            // tipo_pagamento: pode não ter nome no cabeçalho (coluna vazia entre eprotocolo e vínculo)
            $arrayColunas['tipo_pagamento'] = self::buscarPorNome($headerCols, $arrayColunas, 0, ['tipo pagamento', 'tipo_pagamento', 'tipo de pagamento']);
            if (empty(trim($arrayColunas['tipo_pagamento'] ?? ''))) {
                // Procurar coluna sem nome entre eprotocolo e vínculo
                $posProtocolo = self::encontrarPosicao($headerCols, ['eprotocolo', 'e-protocolo', 'protocolo']);
                $posVinculo   = self::encontrarPosicao($headerCols, ['vinculo', 'vínculo']);
                if ($posProtocolo !== false && $posVinculo !== false && ($posVinculo - $posProtocolo) > 1) {
                    $posTipo = $posProtocolo + 1;
                    $arrayColunas['tipo_pagamento'] = isset($arrayColunas[$posTipo]) ? $arrayColunas[$posTipo] : '';
                }
            }

            return $arrayColunas;
        }

        // Fallback: posição fixa (formato sem cabeçalho reconhecível)
        $offset = (trim($arrayColunas[0] ?? '') === '') ? 1 : 0;
        $arrayColunas['data_inicio']    = isset($arrayColunas[$offset + 0]) ? $arrayColunas[$offset + 0] : '';
        $arrayColunas['data_fim']       = isset($arrayColunas[$offset + 1]) ? $arrayColunas[$offset + 1] : '';
        $arrayColunas['e_protocolo']    = isset($arrayColunas[$offset + 2]) ? $arrayColunas[$offset + 2] : '';
        $arrayColunas['tipo_pagamento'] = isset($arrayColunas[$offset + 3]) ? $arrayColunas[$offset + 3] : '';
        $arrayColunas['vinculo']        = isset($arrayColunas[$offset + 4]) ? $arrayColunas[$offset + 4] : '';
        $arrayColunas['coordenador']    = isset($arrayColunas[$offset + 5]) ? $arrayColunas[$offset + 5] : '';
        $arrayColunas['proposta']       = isset($arrayColunas[$offset + 6]) ? $arrayColunas[$offset + 6] : '';
        $arrayColunas['numero_projeto'] = isset($arrayColunas[$offset + 7]) ? $arrayColunas[$offset + 7] : '';
        return $arrayColunas;
    }

    /**
     * PAGAMENTOS: identifica grupo pelo prefixo do cabeçalho e mapeia.
     */
    private static function mapearPagamentos($linha1, $arrayColunas) {
        $linha1 = limparString($linha1);
        $linha1 = trim($linha1);
        $linha1 = rtrim($linha1, ';');
        // Converter para UTF-8 se necessário
        if (!mb_check_encoding($linha1, 'UTF-8')) {
            $linha1 = mb_convert_encoding($linha1, 'UTF-8', 'Windows-1252');
        }

        $linha1Lower = mb_strtolower($linha1, 'UTF-8');
        $headerCols  = explode(';', $linha1Lower);

        // Detectar tipo pelo cabeçalho
        $headerNorm = self::normalizar($linha1Lower);
        $isRpa = (strpos($headerNorm, 'valor') !== false && strpos($headerNorm, 'vencimento') !== false);
        $isBolsa = self::comecaCom($linha1Lower, 'nome;vinculo;') || self::comecaCom(self::normalizar($linha1Lower), 'nome;vinculo;');

        if ($isRpa) {
            $arrayColunas = self::mapearRpaPorCabecalho($headerCols, $arrayColunas);
        } elseif ($isBolsa) {
            $arrayColunas = self::mapearBolsa($headerCols, $arrayColunas);
        } else {
            throw new Exception("Padrão de colunas não reconhecido, arquivo inválido.(" . $linha1 . ")");
        }

        // Limpar fornecedor quando é o próprio nome do cabeçalho
        $fornecedorLower = mb_strtolower(trim($arrayColunas['fornecedor'] ?? ''), 'UTF-8');
        $fornecedorNorm = self::normalizar($fornecedorLower);
        if (in_array($fornecedorNorm, ['nome', 'fichario', 'fichário'])) {
            $arrayColunas['fornecedor'] = '';
        }

        return $arrayColunas;
    }

    /**
     * RPA genérico: mapeia por nome do cabeçalho, independente de colunas vazias extras.
     * Funciona para todos os formatos RPA (com ID, sem ID, ID duplo).
     */
    private static function mapearRpaPorCabecalho($headerCols, $arrayColunas) {
        // Buscar cada campo pelo nome no cabeçalho
        $valorBruto   = self::buscarPorNome($headerCols, $arrayColunas, 0, ['valor bruto rpa', 'valor rpa', 'valor bruto']);
        $vencimento   = self::buscarPorNome($headerCols, $arrayColunas, 0, ['vencimento']);
        $valorLiquido = self::buscarPorNome($headerCols, $arrayColunas, 0, ['valor liquido', 'valor líquido', 'vlr liquido']);
        $codigo       = self::buscarPorNome($headerCols, $arrayColunas, 0, ['codigo', 'código']);
        $servico      = self::buscarPorNome($headerCols, $arrayColunas, 0, ['servico', 'serviço', 'servico/descricao']);
        $cnpj         = self::buscarPorNome($headerCols, $arrayColunas, 0, ['cnpj', 'cpf/cnpj', 'cpf']);
        $tipoRpa      = self::buscarPorNome($headerCols, $arrayColunas, 0, ['tipo de rpa', 'tipos de rpa', 'tipo rpa']);

        // Projeto: buscar por nome, extrair numero_projeto do formato "NR/ANO -Nome"
        $projetoRaw   = self::buscarPorNome($headerCols, $arrayColunas, 0, ['projeto']);
        $txtProjeto   = explode('-', $projetoRaw, 2);
        $numeroProjeto = limparString($txtProjeto[0]);
        $nomeProjeto   = limparString(isset($txtProjeto[1]) ? $txtProjeto[1] : '');

        // Fornecedor: buscar fichário/fichario
        $fornecedor   = self::buscarPorNome($headerCols, $arrayColunas, 0, ['fichario', 'fichário', 'fornecedor']);

        $arrayColunas['numero_projeto'] = $numeroProjeto;
        $arrayColunas['projeto']        = $nomeProjeto;
        $arrayColunas['fornecedor']     = limparString($fornecedor);
        $arrayColunas['servico']        = $servico;
        $arrayColunas['data_pagamento'] = $vencimento;
        $arrayColunas['valor_bruto']    = $valorBruto;
        $arrayColunas['valor_liquido']  = $valorLiquido;
        $arrayColunas['codigo']         = limparString($codigo);
        $arrayColunas['id_projeto']     = self::buscarExato($headerCols, $arrayColunas, ['id']);
        $arrayColunas['cnpj']           = $cnpj;
        $arrayColunas['tipo_pagamento'] = $tipoRpa;
        $arrayColunas['vinculo']        = self::buscarPorNome($headerCols, $arrayColunas, 0, ['vinculo', 'vínculo']);

        return $arrayColunas;
    }

    /**
     * GRUPO D: Bolsa/PREDUC.
     * Detecta presença de Eprotocolo para ajustar posições.
     */
    private static function mapearBolsa($headerCols, $arrayColunas) {
        $temEprotocolo = isset($headerCols[4]) && (strpos($headerCols[4], 'eprotocolo') !== false);

        if ($temEprotocolo) {
            // Com Eprotocolo: Nome(0);Vinculo(1);NrProjeto(2);Projeto(3);Eprotocolo(4);DataInicio(5);DataFim(6);Coordenador(7);Atividade(8);Pagamentos(9);tipo(10);bruto(11);liquido(12)
            $arrayColunas['fornecedor']     = limparString($arrayColunas[0]);
            $arrayColunas['vinculo']        = $arrayColunas[1];
            $arrayColunas['numero_projeto'] = $arrayColunas[2];
            $arrayColunas['projeto']        = $arrayColunas[3];
            $arrayColunas['e_protoolo']     = $arrayColunas[4];
            $arrayColunas['data_inicio']    = $arrayColunas[5];
            $arrayColunas['data_fim']       = $arrayColunas[6];
            $arrayColunas['coordenador']    = $arrayColunas[7];
            $arrayColunas['servico']        = $arrayColunas[8];
            $arrayColunas['data_pagamento'] = $arrayColunas[9];
            $arrayColunas['tipo_pagamento'] = isset($arrayColunas[10]) ? $arrayColunas[10] : '';
            $arrayColunas['valor_bruto']    = isset($arrayColunas[11]) ? $arrayColunas[11] : '';
            $arrayColunas['valor_liquido']  = isset($arrayColunas[12]) ? $arrayColunas[12] : '';
        } else {
            // Sem Eprotocolo: Nome(0);Vinculo(1);NrProjeto(2);Projeto(3);DataInicio(4);DataFim(5);Coordenador(6);Atividade(7);Pagamentos(8);tipo(9);bruto(10);liquido(11)
            $arrayColunas['fornecedor']     = limparString($arrayColunas[0]);
            $arrayColunas['vinculo']        = $arrayColunas[1];
            $arrayColunas['numero_projeto'] = $arrayColunas[2];
            $arrayColunas['projeto']        = $arrayColunas[3];
            $arrayColunas['e_protoolo']     = '';
            $arrayColunas['data_inicio']    = $arrayColunas[4];
            $arrayColunas['data_fim']       = $arrayColunas[5];
            $arrayColunas['coordenador']    = $arrayColunas[6];
            $arrayColunas['servico']        = $arrayColunas[7];
            $arrayColunas['data_pagamento'] = $arrayColunas[8];
            $arrayColunas['tipo_pagamento'] = isset($arrayColunas[9])  ? $arrayColunas[9]  : '';
            $arrayColunas['valor_bruto']    = isset($arrayColunas[10]) ? $arrayColunas[10] : '';
            $arrayColunas['valor_liquido']  = isset($arrayColunas[11]) ? $arrayColunas[11] : '';
        }

        return $arrayColunas;
    }

    // ===================== Utilitários =====================

    private static function comecaCom($haystack, $needle) {
        return substr($haystack, 0, strlen($needle)) === $needle;
    }

    /**
     * Normaliza string para comparação: remove acentos, degree sign, ordinal, etc.
     * Permite que "n° projeto" e "nº projeto" sejam comparados corretamente.
     */
    private static function normalizar($str) {
        return str_replace(
            ['á','é','í','ó','ú','ã','õ','â','ê','ô','ç','à','ü','ñ', 'º','°','ª'],
            ['a','e','i','o','u','a','o','a','e','o','c','a','u','n', '' ,'' ,''],
            $str
        );
    }

    /**
     * Retorna a posição (índice) de uma coluna no cabeçalho pelo nome.
     * Retorna false se não encontrar.
     */
    private static function encontrarPosicao($headerCols, $termos) {
        for ($i = 0; $i < count($headerCols); $i++) {
            $colNameNorm = self::normalizar(trim($headerCols[$i]));
            foreach ($termos as $termo) {
                if (strpos($colNameNorm, self::normalizar($termo)) !== false) {
                    return $i;
                }
            }
        }
        return false;
    }

    /**
     * Busca valor de uma coluna por nome EXATO no cabeçalho.
     * Útil para nomes curtos como "id" que poderiam dar match parcial com strpos.
     */
    private static function buscarExato($headerCols, $arrayColunas, $termos) {
        for ($i = 0; $i < count($headerCols); $i++) {
            $colNameNorm = self::normalizar(trim($headerCols[$i]));
            foreach ($termos as $termo) {
                if ($colNameNorm === self::normalizar($termo)) {
                    return isset($arrayColunas[$i]) ? $arrayColunas[$i] : '';
                }
            }
        }
        return '';
    }

    /**
     * Busca valor de uma coluna pelo nome no cabeçalho, a partir de $posInicio.
     * Percorre $headerCols procurando um nome que contenha um dos $termos.
     * Retorna o valor correspondente em $arrayColunas, ou '' se não encontrar.
     */
    private static function buscarPorNome($headerCols, $arrayColunas, $posInicio, $termos) {
        for ($i = $posInicio; $i < count($headerCols); $i++) {
            $colNameNorm = self::normalizar(trim($headerCols[$i]));
            foreach ($termos as $termo) {
                if (strpos($colNameNorm, self::normalizar($termo)) !== false) {
                    return isset($arrayColunas[$i]) ? $arrayColunas[$i] : '';
                }
            }
        }
        return '';
    }
}

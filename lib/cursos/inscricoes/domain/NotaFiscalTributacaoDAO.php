<?php

/**
 * Codigos de Tributacao Nacional do ISS (cTribNac) da LC 116/2003.
 *
 * O CATALOGO fica em ARQUIVO (resources/dados/ctribnac.json), nao no banco:
 * e tabela nacional estatica, nao dado do sistema. Assim ela viaja junto com o
 * codigo pelo FTP, fica versionada no git e nao depende de rodar migration em
 * producao — que e exatamente onde as coisas se perdem.
 *
 * No banco fica so o que e nosso e muda: a frequencia de uso por emissor
 * (fin_nota_fiscal_tributacao_uso), que alimenta os favoritos.
 */
class NotaFiscalTributacaoDAO extends DAO {

    private static $catalogo = null;

    public function __construct() {
        $this->tableName = "fin_nota_fiscal_tributacao_uso";
        parent::__construct();
    }

    private function getCaminhoCatalogo() {
        return dirname(__FILE__) . '/../resources/dados/ctribnac.json';
    }

    /**
     * Carrega uma vez por requisicao. 335 itens: cabe na memoria sem dor.
     */
    private function getCatalogo() {
        if (self::$catalogo !== null) {
            return self::$catalogo;
        }
        self::$catalogo = array();

        $arquivo = $this->getCaminhoCatalogo();
        if (!file_exists($arquivo)) {
            return self::$catalogo;
        }
        $dados = json_decode(@file_get_contents($arquivo), true);
        if (is_array($dados) && isset($dados['codigos']) && is_array($dados['codigos'])) {
            foreach ($dados['codigos'] as $item) {
                if (isset($item['codigo'])) {
                    self::$catalogo[$item['codigo']] = isset($item['descricao']) ? $item['descricao'] : '';
                }
            }
        }
        return self::$catalogo;
    }

    public function getDescricao($codigo) {
        $codigo = trim((string) $codigo);
        if ($codigo === '') {
            return '';
        }
        $catalogo = $this->getCatalogo();
        return isset($catalogo[$codigo]) ? $catalogo[$codigo] : '';
    }

    /**
     * Busca por codigo OU por texto do servico — quem emite raramente sabe o
     * numero de cabeca, mas sabe o nome do que vende.
     *
     * Os mais usados por ESTE emissor vem primeiro: depois de duas semanas, os
     * tres ou quatro do dia a dia estao sempre no topo.
     */
    public function buscar($termo, $codEmissor, $limite = 20, $debug = false) {
        $termo = $this->normalizar($termo);
        $catalogo = $this->getCatalogo();
        $uso = $this->getUsoPorEmissor($codEmissor, $debug);

        $achados = array();
        foreach ($catalogo as $codigo => $descricao) {
            if ($termo !== '') {
                $alvo = $this->normalizar($codigo . ' ' . $descricao);
                if (strpos($alvo, $termo) === false) {
                    continue;
                }
            }
            $achados[] = array(
                'codigo'    => $codigo,
                'descricao' => $descricao,
                'qtdUso'    => isset($uso[$codigo]) ? (int) $uso[$codigo] : 0
            );
        }

        // Mais usados primeiro; empate resolve pelo codigo, para a ordem ser estavel.
        usort($achados, function ($a, $b) {
            if ($a['qtdUso'] === $b['qtdUso']) {
                return strcmp($a['codigo'], $b['codigo']);
            }
            return $b['qtdUso'] - $a['qtdUso'];
        });

        return array_slice($achados, 0, (int) $limite);
    }

    /**
     * Compara sem acento e sem caixa: "informatica" acha "Informática".
     */
    private function normalizar($texto) {
        $texto = trim((string) $texto);
        if ($texto === '') {
            return '';
        }
        $de   = array('á','à','ã','â','ä','é','è','ê','ë','í','ì','î','ï','ó','ò','õ','ô','ö','ú','ù','û','ü','ç','ñ');
        $para = array('a','a','a','a','a','e','e','e','e','i','i','i','i','o','o','o','o','o','u','u','u','u','c','n');
        $texto = str_replace($de, $para, mb_strtolower($texto, 'UTF-8'));
        return $texto;
    }

    private function getUsoPorEmissor($codEmissor, $debug = false) {
        $uso = array();
        if (!$codEmissor) {
            return $uso;
        }
        try {
            $sql = sprintf("SELECT codigo, qtd_uso FROM " . $this->tableName . "
                    WHERE cod_emissor = %d", (int) $codEmissor);

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if ($rs && !$rs->EOF) {
                foreach ($rs as $row) {
                    $uso[$row['codigo']] = $row['qtd_uso'];
                }
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
        }
        return $uso;
    }

    /**
     * Conta o uso do codigo por este emissor. Chamado na EMISSAO, nao na previa:
     * so olhar nao pode reordenar a lista.
     */
    public function registrarUso($codEmissor, $codigo, $debug = false) {
        try {
            if (!$codEmissor || trim((string) $codigo) == '') {
                return false;
            }
            $sql = sprintf("INSERT INTO " . $this->tableName . "
                    (cod_emissor, codigo, qtd_uso, data_ultimo_uso)
                    VALUES (%d, '%s', 1, '%s')
                    ON DUPLICATE KEY UPDATE qtd_uso = qtd_uso + 1, data_ultimo_uso = '%s'",
                (int) $codEmissor, addslashes((string) $codigo),
                date('Y-m-d H:i:s'), date('Y-m-d H:i:s'));

            $this->conex->debug = $debug;
            $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

}

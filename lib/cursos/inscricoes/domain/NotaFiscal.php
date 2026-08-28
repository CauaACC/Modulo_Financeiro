<?php

/**
 * Nota fiscal emitida. Os dados gravados sao um SNAPSHOT do momento da emissao:
 * se o pedido mudar depois, a nota nao muda — nota emitida so se cancela ou substitui.
 */
class NotaFiscal extends NotaFiscalVO {

    private $objDAO;

    function __construct() {
        $this->objDAO = new NotaFiscalDAO();
    }

    public function getObjDAO() {
        return $this->objDAO;
    }

    public function getNotaFiscalById($debug = false) {
        return $this->objDAO->getById($this, $debug);
    }

    public function getNotaFiscalByReferencia($debug = false) {
        return $this->objDAO->getByReferencia($this, $debug);
    }

    public function getNotaVigenteByOrigem($debug = false) {
        return $this->objDAO->getVigenteByOrigem($this, $debug);
    }

    public function listarNotasByOrigem($debug = false) {
        return $this->objDAO->listarByOrigem($this, $debug);
    }

    public function listarNotasByEvento($filtroStatus = '', $debug = false) {
        return $this->objDAO->listarByEvento($this, $filtroStatus, $debug);
    }

    /** Consulta fiscal com filtros (tela Financeiro > Notas Fiscais). */
    public function listarComFiltros(array $filtros, $debug = false) {
        return $this->objDAO->listarComFiltros($filtros, $debug);
    }

    public function resumoPorStatus(array $filtros, $debug = false) {
        return $this->objDAO->resumoPorStatus($filtros, $debug);
    }

    /**
     * Referencia idempotente: a Focus usa a ref como chave unica, entao duplo
     * clique ou retry nao gera duas notas para o mesmo pedido. O sufixo de
     * tentativa so muda quando a anterior falhou (nota em erro pode ser refeita).
     */
    public static function montarReferencia($origem, $origemId, $tentativa = 1) {
        $prefixo = strtoupper(substr((string) $origem, 0, 3));
        $ref = $prefixo . '-' . preg_replace('/[^A-Za-z0-9]/', '', (string) $origemId);
        if ((int) $tentativa > 1) {
            $ref .= '-R' . (int) $tentativa;
        }
        return $ref;
    }

    public function gravar($debug = false) {
        $this->validatorEmitir();
        if (!$this->objDAO->insert($this, $debug)) {
            throw new Exception("Nao foi possivel registrar a nota fiscal.");
        }
        return true;
    }

    public function atualizarRetorno($debug = false) {
        return $this->objDAO->atualizarRetorno($this, $debug);
    }

    public function atualizarArquivosLocais($debug = false) {
        return $this->objDAO->atualizarArquivosLocais($this, $debug);
    }

    public function registrarCancelamento($debug = false) {
        return $this->objDAO->registrarCancelamento($this, $debug);
    }

    public function registrarEnvioEmail($debug = false) {
        return $this->objDAO->registrarEnvioEmail($this, $debug);
    }

    /**
     * So faz sentido enviar nota que a prefeitura autorizou. Rejeitada nao
     * existe fiscalmente; cancelada nao vale mais nada para o tomador.
     */
    public function podeEnviarPorEmail() {
        return $this->getStatus() == 'AUTORIZADA' && !$this->getDataEnvioEmail();
    }

    /**
     * Aplica ao objeto o que a Focus devolveu, traduzindo o status dela
     * para o nosso. Um unico ponto de traducao evita divergencia entre
     * emissao e consulta.
     */
    public function aplicarRespostaFocus($resposta) {
        $corpo = isset($resposta['corpo']) && is_array($resposta['corpo']) ? $resposta['corpo'] : array();

        $this->setResponseJson(json_encode($corpo, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        $statusFocus = isset($corpo['status']) ? $corpo['status'] : '';

        switch ($statusFocus) {
            case 'autorizado':
                $this->setStatus('AUTORIZADA');
                $this->setMensagemErro(null);
                break;
            case 'cancelado':
                $this->setStatus('CANCELADA');
                break;
            case 'processando_autorizacao':
                $this->setStatus('PROCESSANDO');
                break;
            case 'erro_autorizacao':
            case 'denegado':
                $this->setStatus('ERRO');
                break;
            default:
                // Sem status reconhecido: se veio erro HTTP, e erro; senao mantem.
                if (!empty($resposta['erro']) || (isset($resposta['http']) && $resposta['http'] >= 400)) {
                    $this->setStatus('ERRO');
                }
                break;
        }

        if (!empty($corpo['numero'])) {
            $this->setNumero($corpo['numero']);
        }
        if (!empty($corpo['codigo_verificacao'])) {
            $this->setCodigoVerificacao($corpo['codigo_verificacao']);
        }
        // Nomes confirmados na PRIMEIRA nota autorizada de verdade (29/07):
        //   url_danfse              -> PDF da nota (link completo, S3)
        //   caminho_xml_nota_fiscal -> XML (caminho relativo a API)
        //   url                     -> consulta publica no portal da prefeitura,
        //                              NAO e o PDF. Guardar isso como url_pdf
        //                              fazia o botao PDF abrir a pagina errada.
        if (!empty($corpo['url_danfse'])) {
            $this->setUrlPdf($corpo['url_danfse']);
        } elseif (!empty($corpo['caminho_danfse'])) {
            $this->setUrlPdf($corpo['caminho_danfse']);
        }
        if (!empty($corpo['caminho_xml_nota_fiscal'])) {
            $this->setUrlXml($corpo['caminho_xml_nota_fiscal']);
        } elseif (!empty($corpo['url_xml'])) {
            $this->setUrlXml($corpo['url_xml']);
        }
        if (!empty($corpo['url'])) {
            $this->setUrlConsultaPublica($corpo['url']);
        }
        if (!empty($corpo['data_emissao'])) {
            $data = date('Y-m-d H:i:s', strtotime($corpo['data_emissao']));
            $this->setDataEmissao($data);
        }

        // Campos que so a NF-e devolve. Ficam depois dos comuns e so gravam
        // quando vem preenchidos — nota de servico nao encosta neles.
        if (!empty($corpo['chave_nfe'])) {
            $this->setChaveNfe($corpo['chave_nfe']);
        }
        if (!empty($corpo['protocolo'])) {
            $this->setProtocoloAutorizacao($corpo['protocolo']);
        }
        if (!empty($corpo['serie'])) {
            $this->setSerieNfe($corpo['serie']);
        }
        // O DANFE da NF-e vem em campo proprio (sem o "S" de NFS-e).
        if (!empty($corpo['caminho_danfe'])) {
            $this->setUrlPdf($corpo['caminho_danfe']);
        }

        $erro = $this->extrairMensagemErro($corpo, $resposta);
        if ($erro != '') {
            $this->setMensagemErro($erro);
        }

        return $this;
    }

    /**
     * A rejeicao pode vir em campos diferentes conforme o tipo de falha
     * (validacao da Focus x rejeicao da prefeitura). Sem juntar tudo aqui,
     * o gestor ve "erro" na tela e nada mais.
     */
    private function extrairMensagemErro($corpo, $resposta) {
        $partes = array();

        if (!empty($corpo['mensagem'])) {
            $partes[] = $corpo['mensagem'];
        }
        if (!empty($corpo['erros']) && is_array($corpo['erros'])) {
            foreach ($corpo['erros'] as $err) {
                if (is_array($err)) {
                    $texto = isset($err['mensagem']) ? $err['mensagem'] : json_encode($err, JSON_UNESCAPED_UNICODE);
                    if (isset($err['campo']) && $err['campo'] != '') {
                        $texto = $err['campo'] . ': ' . $texto;
                    }
                    $partes[] = $texto;
                } else {
                    $partes[] = (string) $err;
                }
            }
        }
        if (!empty($corpo['status_sefaz']) && !empty($corpo['mensagem_sefaz'])) {
            $partes[] = $corpo['status_sefaz'] . ' - ' . $corpo['mensagem_sefaz'];
        }
        if (empty($partes) && !empty($resposta['erro'])) {
            $partes[] = $resposta['erro'];
        }

        return implode(' | ', array_unique($partes));
    }

    public function ehNfe() {
        return $this->getTipo() == 'NFE';
    }

    public function getDescricaoStatus() {
        switch ($this->getStatus()) {
            case 'PROCESSANDO': return $this->ehNfe() ? 'Processando na SEFAZ' : 'Processando na prefeitura';
            case 'AUTORIZADA':  return 'Autorizada';
            case 'CANCELADA':   return 'Cancelada';
            case 'ERRO':        return 'Rejeitada';
            default:            return (string) $this->getStatus();
        }
    }

}

<?php

class HistoricoAvaliacaoInscrito extends HistoricoAvaliacaoInscritoVO {

    /**
     * Janela de expiracao da reserva SELECIONADO. Reservas mais antigas
     * que isso sao consideradas expiradas (libera pra outro pegar / nao
     * mostra como "ativa" no warning). Hardcoded por enquanto; futuramente
     * mover para configuracao por etapa.
     */
    const RESERVA_EXPIRA_HORAS = 24;

    private $objDAO;

    function __construct() {
        $this->codEvento = COD_EVENTO;
        $this->objDAO    = new HistoricoAvaliacaoInscritoDAO();
    }

    public function salvar($debug = false) {
        $this->validatorObj();
        if (is_null($this->getCodAvaliacaoHistorico()) || $this->getCodAvaliacaoHistorico() == "") {
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Histórico salvo com sucesso.");
            }
            throw new Exception("Falha ao salvar o histórico.");
        }
        if ($this->objDAO->update($this, $debug)) {
            throw new OKException("Histórico atualizado.");
        }
        throw new Exception("Falha ao atualizar o histórico.");
    }

    /**
     * Marca SELECIONADO pra uma combinacao (cod_evento, cod_inscrito, cod_etapa, cod_avaliador).
     * Retorna true se foi de fato gravado (nao havia ainda) ou false se ja existia.
     * Nao lanca excecao em caso de duplicidade. Reserva nao-exclusiva: varios
     * avaliadores podem ter SELECIONADO ativo no mesmo (inscrito, etapa).
     */
    public function marcarSelecionado($debug = false) {
        return $this->objDAO->marcarSelecionado($this, $debug);
    }

    /**
     * Retorna LISTA de avaliadores que tem reserva SELECIONADO ativa para
     * (cod_evento, cod_inscrito, cod_etapa), filtrando reservas mais antigas
     * que RESERVA_EXPIRA_HORAS (consideradas expiradas).
     * Util pra mostrar warning "X tambem esta avaliando este inscrito".
     */
    public function getAvaliadoresSelecionados($debug = false) {
        return $this->objDAO->getAvaliadoresSelecionados($this, self::RESERVA_EXPIRA_HORAS, $debug);
    }

    /**
     * Libera (DESATIVA) a reserva SELECIONADO do avaliador atual para
     * (cod_evento, cod_inscrito, cod_etapa). Outros avaliadores nao sao
     * afetados.
     */
    public function liberarReserva($debug = false) {
        return $this->objDAO->liberarReserva($this, $debug);
    }

    /**
     * Registra a decisao do avaliador no historico (HOMOLOGADO ou NAO HOMOLOGADO).
     * Recebe o classificacao_status do registro consolidado ('H'/'I') e
     * traduz pra texto. Idempotente: nao duplica decisao do mesmo avaliador.
     */
    public function registrarDecisao($classificacaoStatus, $debug = false) {
        $cs = strtoupper(trim((string)$classificacaoStatus));
        if ($cs === 'H') {
            $this->setAvaliacao(self::ACAO_HOMOLOGADO);
        } elseif ($cs === 'I') {
            $this->setAvaliacao(self::ACAO_NAO_HOMOLOGADO);
        } else {
            return false; // status nao reconhecido, nao grava
        }
        return $this->objDAO->registrarDecisao($this, $debug);
    }
}

<?php

class HistoricoAvaliacaoInscritoVO extends PadraoVO {

    /**
     * Estados possiveis em `avaliacao` (texto por extenso, padrao da legada
     * tab_historico_avaliacao). Para inscritos usamos vocabulario de
     * homologacao em vez de aprovacao/reprovacao de trabalho:
     */
    const ACAO_SELECIONADO     = 'SELECIONADO';
    const ACAO_HOMOLOGADO      = 'HOMOLOGADO';
    const ACAO_NAO_HOMOLOGADO  = 'NAO HOMOLOGADO';
    const ACAO_DEVOLVIDO       = 'DEVOLVIDO';

    public function validatorObj() {
        $message = "";
        $return  = true;
        if (is_null($this->getCodEvento()) || $this->getCodEvento() == "") {
            $message .= "Código do evento não foi informado.<br>";
            $return = false;
        }
        if (is_null($this->getCodInscrito()) || $this->getCodInscrito() == "") {
            $message .= "Código do inscrito não foi informado.<br>";
            $return = false;
        }
        if (is_null($this->getCodAvaliador()) || $this->getCodAvaliador() == "") {
            $message .= "Código do avaliador não foi informado.<br>";
            $return = false;
        }
        if (is_null($this->getAvaliacao()) || $this->getAvaliacao() == "") {
            $message .= "Ação (avaliacao) não foi informada.<br>";
            $return = false;
        }
        if (!$return) {
            throw new Exception($message);
        }
        return true;
    }

    public function validatorExcluir() {
        if (is_null($this->getCodAvaliacaoHistorico()) || $this->getCodAvaliacaoHistorico() == "") {
            throw new Exception("Código do histórico não foi informado.");
        }
        return true;
    }
}

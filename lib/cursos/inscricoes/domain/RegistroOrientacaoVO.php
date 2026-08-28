<?php

class RegistroOrientacaoVO extends PadraoVO {

    public function validatorObj() {
        $message = "";
        $return = true;
        if (is_null($this->getCodEvento()) or $this->getCodEvento() == "") {
            $message .= "Codigo do evento nao foi informado.<br>";
            $return = false;
        }
        if (is_null($this->getDataRegistro()) or $this->getDataRegistro() == "") {
            $message .= "Informe a Data do Registro.<br>";
            $return = false;
        }
        if (is_null($this->getCodSupervisor()) or $this->getCodSupervisor() == "") {
            $message .= "Codigo do supervisor nao foi informado.<br>";
            $return = false;
        }
        if ($return == false) {
            throw new Exception($message);
        }
        return true;
    }

    public function validatorItemPes($item) {
        $message = "";
        if (empty($item['cod_agente'])) {
            $message .= "Selecione o PES.<br>";
        }
        $temAtividade = !empty($item['atividades_pes']);
        $temDescricao = !empty(trim($item['descricao_pes'] ?? ''));
        $temOrientacao = !empty(trim($item['orientacoes_pes'] ?? ''));
        if (!$temAtividade && !$temDescricao && !$temOrientacao) {
            $message .= "Preencha ao menos uma atividade, descricao ou orientacao para o PES.<br>";
        }
        if ($message) {
            throw new Exception($message);
        }
        return true;
    }

    public function validatorItemCrianca($item) {
        $message = "";
        if (empty($item['cod_aluno'])) {
            $message .= "Selecione a crianca.<br>";
        }
        $temAtividade = !empty($item['atividades_crianca']);
        $temDescricao = !empty(trim($item['descricao_crianca'] ?? ''));
        $temOrientacao = !empty(trim($item['orientacoes_crianca'] ?? ''));
        if (!$temAtividade && !$temDescricao && !$temOrientacao) {
            $message .= "Preencha ao menos uma atividade, descricao ou orientacao para a intervencao.<br>";
        }
        if ($message) {
            throw new Exception($message);
        }
        return true;
    }

    public function validatorExcluir() {
        $message = "";
        $return = true;
        if (is_null($this->getCodEvento()) or $this->getCodEvento() == "") {
            $message .= "Codigo do evento nao informado.<br>";
            $return = false;
        }
        if (is_null($this->getCodRegistro()) or $this->getCodRegistro() == "") {
            $message .= "Codigo do Registro nao informado.<br>";
            $return = false;
        }
        if ($return == false) {
            throw new Exception($message);
        }
        return true;
    }

}

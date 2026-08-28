<?php

class RegistroPedagogicoVO extends PadraoVO {

    public function validatorPresenca() {
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
        if (is_null($this->getCodAluno()) or $this->getCodAluno() == "") {
            $message .= "Codigo do aluno nao foi informado.<br>";
            $return = false;
        }
        $presenca = $this->getPresenca();
        if (!in_array($presenca, array('PRESENTE', 'FALTA', 'FALTA_JUSTIFICADA'))) {
            $message .= "Valor de presenca invalido.<br>";
            $return = false;
        }
        if ($return == false) {
            throw new Exception($message);
        }
        return true;
    }

    public function validatorObjRascunho() {
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
        if (is_null($this->getCodAluno()) or $this->getCodAluno() == "") {
            $message .= "Codigo do aluno nao foi informado.<br>";
            $return = false;
        }
        if ($return == false) {
            throw new Exception($message);
        }
        return true;
    }

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
        if (is_null($this->getCondicaoInicio()) or $this->getCondicaoInicio() == "") {
            $message .= "Informe a Condicao Inicial para a Aula.<br>";
            $return = false;
        }
        if (is_null($this->getParticipacao()) or $this->getParticipacao() == "") {
            $message .= "Informe a Participacao nas atividades.<br>";
            $return = false;
        }
        if (is_null($this->getGrauApoio()) or $this->getGrauApoio() == "") {
            $message .= "Informe o Grau de apoio necessario.<br>";
            $return = false;
        }
        if (is_null($this->getCondicaoFinal()) or $this->getCondicaoFinal() == "") {
            $message .= "Informe a Condicao ao Final da Aula.<br>";
            $return = false;
        }
        if (is_null($this->getEvolucaoPeriodo()) or $this->getEvolucaoPeriodo() == "") {
            $message .= "Informe a Evolucao no periodo.<br>";
            $return = false;
        }
        if ($return == false) {
            throw new Exception($message);
            return false;
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
            $message .= "Codigo do Registro Pedagogico nao informado.<br>";
            $return = false;
        }
        if ($return == false) {
            throw new Exception($message);
            return false;
        }
        return true;
    }

}

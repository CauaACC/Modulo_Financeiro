<?php

class CursoVO extends PadraoVO {

    public function validatorSalvar() {
        $message = "";
        $return = true;
        if (is_null($this->getCodEvento()) or $this->getCodEvento() == "") {
            $message .= "Código do evento não foi informado.<br>";
            $return = false;
        }
        if (is_null($this->getTitulo()) or trim($this->getTitulo()) == "") {
            $message .= "Título do curso é obrigatório.<br>";
            $return = false;
        }
        if ($this->getHoraInicio() && $this->getHoraFim()
            && strtotime($this->getHoraInicio()) >= strtotime($this->getHoraFim())) {
            $message .= "Hora de início deve ser menor que a hora de fim.<br>";
            $return = false;
        }
        if ($this->getDtLiberacao() && $this->getDtEncerramento()
            && $this->getDtLiberacao() > $this->getDtEncerramento()) {
            $message .= "Data de liberação deve ser anterior à data de encerramento.<br>";
            $return = false;
        }
        if ($return == false) {
            throw new Exception($message);
        }
        return true;
    }

    public function validatorExcluir() {
        if (!$this->getCodCurso()) {
            throw new Exception("Código do curso não informado.");
        }
        return true;
    }

    public function estaDentroDaJanela($agora = null) {
        if ($agora === null) $agora = time();
        $hoje = date('Y-m-d', $agora);
        $horaAtual = date('H:i:s', $agora);
        if ($this->getDtLiberacao() && $this->getDtLiberacao() > $hoje) return false;
        if ($this->getDtEncerramento() && $this->getDtEncerramento() < $hoje) return false;
        if ($this->getHoraInicio() && $horaAtual < $this->getHoraInicio()) return false;
        if ($this->getHoraFim() && $horaAtual > $this->getHoraFim()) return false;
        return true;
    }
}

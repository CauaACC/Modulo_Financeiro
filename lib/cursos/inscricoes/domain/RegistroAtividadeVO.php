<?php

class RegistroAtividadeVO extends PadraoVO {

    public function validatorObj() {
        $message = "";
        $return = true;
        if (is_null($this->getCodEvento()) or $this->getCodEvento() == "") {
            $message .= "Código do evento não foi informado.<br>";
            $return = false;
        }
        if (is_null($this->getDia()) or $this->getDia() == "") {
            $message .= "Informe o Dia.<br>";
            $return = false;
        }
        if (is_null($this->getDiaSemana()) or $this->getDiaSemana() == "") {
            $message .= "Informe o Dia da Semana.<br>";
            $return = false;
        }
        // if (is_null($this->getCumprimentoHorario()) or $this->getCumprimentoHorario() == "") {
        //     $message .= "Informe se o cumprimento do horário foi parcial ou integral.<br>";
        //     $return = false;
        // }
        if (is_null($this->getHorasTrabalhadas()) or $this->getHorasTrabalhadas() == "") {
            $message .= "Informe as Horas Trabalhadas.<br>";
            $return = false;
        }
        if (is_null($this->getDescricao()) or $this->getDescricao() == "") {
            $message .= "Informe Descrição da atividade.<br>";
            $return = false;
        }        
        if ($return == false) {
            throw new Exception($message);
            return false;
        }
        return true;
    }
    public function validatorAvaliacaoObj() {
        $message = "";
        $return = true;
        if (is_null($this->getCodEvento()) or $this->getCodEvento() == "") {
            $message .= "Código do evento não foi informado.<br>";
            $return = false;
        }
        if ($this->getAvaliacaoStatus()=='Reprovar'){ 
            if (is_null($this->getParecerAvaliador()) or $this->getParecerAvaliador() == "") {
                $message .= "Informe o ParecerAvaliador.<br>";
                $return = false;
            }
        }
        if (is_null($this->getAvaliacaoStatus()) or $this->getAvaliacaoStatus() == "") {
            $message .= "Informe sua decisão de avaliação.<br>";
            $return = false;
        }
        if ($return == false) {
            throw new Exception($message);
            return false;
        }
        return true;
    }

    public function validatorEcluir() {
        $message = "";
        $return = true;
        if (is_null($this->getCodEvento()) or $this->getCodEvento() == "") {
            $message .= "Código do evento não informado.<br>";
            $return = false;
        }
        if (is_null($this->getCodRegAtividade()) or $this->getCodRegAtividade() == "") {
            $message .= "Código do Registro Atividade não informado.<br>";
            $return = false;
        }
        if ($return == false) {
            throw new Exception($message);
            return false;
        }
        return true;
    }

}


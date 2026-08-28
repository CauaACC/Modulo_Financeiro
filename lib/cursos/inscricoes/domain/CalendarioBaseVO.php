<?php
class CalendarioBaseVO extends PadraoVO
{
    protected $id;
    protected $dia_calendario;
    protected $mes;
    protected $tipo_dia;
    protected $Feriado;
    protected $Ponto;

    public function getId() { return $this->id; }
    public function setId($value) { $this->id = $value; }

    public function getDiaCalendario() { return $this->dia_calendario; }
    public function setDiaCalendario($value) { $this->dia_calendario = $value; }

    public function getMes() { return $this->mes; }
    public function setMes($value) { $this->mes = $value; }

    public function getTipoDia() { return $this->tipo_dia; }
    public function setTipoDia($value) { $this->tipo_dia = $value; }

    public function getferiado() { return $this->Feriado; }
    public function setferiado($value) { $this->Feriado = $value; }

    public function getponto() { return $this->Ponto; }
    public function setponto($value) { $this->Ponto = $value; }

    public function validatorInsert() {
        $message = "";
        $return  = true;

        if($this->isNullOrEmpty($this->getDiaCalendario())){
            $message .= "Dia do calendário não informado.<br>";
            $return = false;
        }
        if($this->isNullOrEmpty($this->getMes())){
            $message .= "Mes não informado.<br>";
            $return = false;
        }
        if($this->isNullOrEmpty($this->getTipoDia())){
            $message .= "TipoDia não informado.<br>";
            $return = false;
        }
        if($this->isNullOrEmpty($this->getferiado())){
            $message .= "feriado não informado.<br>";
            $return = false;
        }
        if($this->isNullOrEmpty($this->getponto())){
            $message .= "ponto não informado.<br>";
            $return = false;
        }

        if ($return == false) {
            throw new Exception($message);
            return false;
        }
        return true;
    }

    public function validatorUpdate() {
        $message = "";
        $return  = true;

        if($this->isNullOrEmpty($this->getId())){
            $message .= "Id não informado.<br>";
            $return = false;
        }
        if($this->isNullOrEmpty($this->getDiaCalendario())){
            $message .= "Dia do calendário não informado.<br>";
            $return = false;
        }
        if($this->isNullOrEmpty($this->getMes())){
            $message .= "Mes não informado.<br>";
            $return = false;
        }
        if($this->isNullOrEmpty($this->getTipoDia())){
            $message .= "TipoDia não informado.<br>";
            $return = false;
        }
        if($this->isNullOrEmpty($this->getferiado())){
            $message .= "feriado não informado.<br>";
            $return = false;
        }
        if($this->isNullOrEmpty($this->getponto())){
            $message .= "ponto não informado.<br>";
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
        $return  = true;

        if (is_null($this->getEventoId()) || $this->getEventoId() == "") {
            $message .= "Cód.Evento não foi informado.<br>";
            $return = false;
        }
        if ($this->isNullOrEmpty($this->getId())) {
            $message .= "Código não informado.<br>";
            $return = false;
        }

        if ($return == false) {
            throw new Exception($message);
            return false;
        }
        return true;
    }
}
<?php
class FeriadoVO extends PadraoVO
{
    protected $id;
    protected $evento_id;
    protected $cidade_id;
    protected $dia_calendario;
    protected $tipo_dia;
    protected $descricao;

    public function getId() { return $this->id; }
    public function setId($value) { $this->id = $value; }

    public function getEventoId() { return $this->evento_id; }
    public function setEventoId($value) { $this->evento_id = $value; }

    public function getCidadeId() { return $this->cidade_id; }
    public function setCidadeId($value) { $this->cidade_id = $value; }

    public function getDiaCalendario() { return $this->dia_calendario; }
    public function setDiaCalendario($value) { $this->dia_calendario = $value; }

    public function getTipoDia() { return $this->tipo_dia; }
    public function setTipoDia($value) { $this->tipo_dia = $value; }

    public function getDescricao() { return $this->descricao; }
    public function setDescricao($value) { $this->descricao = $value; }

    public function validatorInsert() {
        $message = "";
        $return  = true;

        if($this->isNullOrEmpty($this->getEventoId())){
            $message .= "EventoId não informado.<br>";
            $return = false;
        }
        // if($this->isNullOrEmpty($this->getCidadeId())){
        //     $message .= "CidadeId não informado.<br>";
        //     $return = false;
        // }
        if($this->isNullOrEmpty($this->getDiaCalendario())){
            $message .= "Dia do calendário não informado.<br>";
            $return = false;
        }
        if($this->isNullOrEmpty($this->getDescricao())){
            $message .= "Descricao não informado.<br>";
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
        if($this->isNullOrEmpty($this->getEventoId())){
            $message .= "EventoId não informado.<br>";
            $return = false;
        }
        // if($this->isNullOrEmpty($this->getCidadeId())){
        //     $message .= "CidadeId não informado.<br>";
        //     $return = false;
        // }
        if($this->isNullOrEmpty($this->getDiaCalendario())){
            $message .= "Dia do calendário não informado.<br>";
            $return = false;
        }
        if($this->isNullOrEmpty($this->getDescricao())){
            $message .= "Descricao não informado.<br>";
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
    public function getAllJson($status = true)
    {
        $data = $this->getAllForArray(null, false); // obtém todos os campos públicos como array
    
        $data['statusMessage'] = $status;
        $data['message'] = $this->getMessage(); // opcional, se você usa mensagens no VO
    
        return $data;
    }
    
}
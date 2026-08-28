<?php
class AreaTematicaVO extends PadraoVO
{   
    public function getDesOrdem(){
        return lpad($this->getOrdem(),2,0);
    }
    public function setDatAreaTematica($datAreaTematica) {
        $this->datAreaTematica = formatDateInsert($datAreaTematica);
    }
    public function validatorObj(){
        $message = "";
        $return  = true;
        if(is_null($this->getCodEvento())or$this->getCodEvento()==""){
            $message .= "Código do evento não foi informado.<br>";
            $return = false;
        }
        if(is_null($this->getTipo())or$this->getTipo()==""){
            $message .= "Informe se o item trata-se de receita ou despesa.<br>";
            $return = false;
        }
        if(is_null($this->getVlrTotal())or$this->getVlrTotal()==""){
            $message .= "Informe valor total.<br>";
            $return = false;
        }
        if(is_null($this->getDesAreaTematica())or$this->getDesAreaTematica()==""){
            $message .= "Informe uma descrição.<br>";
            $return = false;
        }
        if(is_null($this->getCodTipoReceitaDespesa())or$this->getCodTipoReceitaDespesa()==""){
            $message .= "Informe o nome da ". $this->getTipo(). ".<br>";
            $return = false;
        }
        if($return==false){
            throw new Exception($message);
            return false;
        }
        if(is_null($this->getTipoReceitaDespesa())or$this->getTipoReceitaDespesa()==""){
            $message .= "Descrição da Receita/Despesa não foi informado.<br>";
            $return = false;
        }
        return true;
    }
    public function validatorEcluir(){
        $message = "";
        $return  = true;
        if(is_null($this->getCodEvento())or$this->getCodEvento()==""){
            $message .= "Código do evento não foi informado.<br>";
            $return = false;
        }
        if(is_null($this->getCodAreaTematica()) or $this->getCodAreaTematica()==""){
            $message .= "Código da ocorrência não foi informado.<br>";
            $return = false;
        }
        if($return==false){
            throw new Exception($message);
            return false;
        }
        return true;
    }    
}?>
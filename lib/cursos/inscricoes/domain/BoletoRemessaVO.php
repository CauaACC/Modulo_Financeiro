<?php
class BoletoRemessaVO extends PadraoVO
{   
    public function setDatBoletoRemessa($datBoletoRemessa) {
        $this->datBoletoRemessa = formatDateInsert($datBoletoRemessa);
    }
    public function validatorObj(){
        $message = "";
        $return  = true;
        if(is_null($this->getCodEvento())or$this->getCodEvento()==""){
            $message .= "Código do evento não foi informado.<br>";
            $return = false;
        }
        if(is_null($this->getNomeArquivo())or$this->getNomeArquivo()==""){
            $message .= "Nome do Arquivo não foi informado.<br>";
            $return = false;
        }
        if($return==false){
            throw new Exception($message);
            return false;
        }
        return true;
    }
    public function validatorExcluir(){
        $message = "";
        $return  = true;
        if(is_null($this->getCodEvento())or$this->getCodEvento()==""){
            $message .= "Código do evento não foi informado.<br>";
            $return = false;
        }
        if(is_null($this->getCodBoletoRemessa()) or $this->getCodBoletoRemessa()==""){
            $message .= "Código do arquivo de remessa não foi informado.<br>";
            $return = false;
        }
        if($return==false){
            throw new Exception($message);
            return false;
        }
        return true;
    }    
}?>
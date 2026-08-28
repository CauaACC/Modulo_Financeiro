<?php
class OficinaVO extends PadraoVO
{   
    // public function getIsEventoParalelo(){
    //     $tipo = $this->getTipo();
    //     $codAtividade = $this->getCodOficina();
    //     $eventosParalelos = $this->getObjEventoConfiguracao()->getEventoParaleloTipo();
    //     $isEvParalelo = false;
    //     if($eventosParalelos!=''){
    //         $eventosParalelosArray = explode(',', $eventosParalelos); 
    //         if(in_array($tipo, $eventosParalelosArray) or in_array($codAtividade, $eventosParalelosArray)){
    //             $isEvParalelo = true;
    //         }
    //     }
    //     return $isEvParalelo;
    // }
    public function getTipoRelacao() {
        if ($this->tipoRelacao=='') {
            $this->tipoRelacao = 'Participante';
        }
        return $this->tipoRelacao;
    }
    public function getTituloAgenda(){
        return "Agendado para: ".formatDateHoraTrans($this->getDataInicio(), $formatIn='YYYYMM-DD', $formatOut='DD/MM/YYYY').' - '.$this->titulo;
    }
    public function getDataAgenda(){
        return formatDateHoraTrans($this->getDataInicio(), $formatIn='YYYYMM-DD', $formatOut='DD/MM/YYYY').' - '.$this->titulo;
    }
    public function getDesOrdem(){
        return lpad($this->getOrdem(),2,0);
    }
    public function setDatOficina($datOficina) {
        $this->datOficina = formatDateInsert($datOficina);
    }
    public function getDesReceberInscricao(){
        if($this->getReceberInscricao()=='1'){
            return "Sim";
        }else{
            return "Não";
        }
    }
    public function getDesStatus(){
        if($this->getStatus()=='ATIVADA'){
            return "A";
        }else{
            return "D";
        }
    }
    public function getDesLiberarCertificado(){
        if($this->getLiberarCertificado()==1){
            return "Liberado";
        }else{
            return "";
        }
    }
    public function validatorObj(){
        $message = "";
        $return  = true;
        if(is_null($this->getCodEvento())or$this->getCodEvento()==""){
            $message .= "Código do evento não foi informado.<br>";
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
    public function validatorExcluir(){
        $message = "";
        $return  = true;
        if(is_null($this->getCodEvento())or$this->getCodEvento()==""){
            $message .= "Código do evento não foi informado.<br>";
            $return = false;
        }
        if(is_null($this->getCodOficina()) or $this->getCodOficina()==""){
            $message .= "Código da atividade não foi informado.<br>";
            $return = false;
        }
        if($return==false){
            throw new Exception($message);
            return false;
        }
        return true;
    }    
}
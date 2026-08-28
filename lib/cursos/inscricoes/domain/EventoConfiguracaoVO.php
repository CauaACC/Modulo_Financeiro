<?php
class EventoConfiguracaoVO extends PadraoVO
{   
    public function getMensDataUltima()
    {
        $dataPrimeira = $this->getMensDataPrimeira(); // formato 'Y-m-d'
        $qtde = (int) $this->getMensQtd();

        if (!$dataPrimeira || $qtde <= 0) {
            return null; // Ou ''
        }

        // Soma (qtde - 1) meses
        $dataUltima = date('Y-m-d', strtotime("+" . ($qtde - 1) . " months", strtotime($dataPrimeira)));
        return $dataUltima;
    }

    public function getTratandoConfPorCodigo(){
        /**usar esse tipo de função quando for necessário inserir configuração ainda não tratada */
        $codigosDeEventosPermitidos = [37, 38, 46, 44, 42, 50]; 
        if (in_array($this->getCodEvento(), $codigosDeEventosPermitidos)) {
            return true;
        }
        return false;
    }

    public function getTxtBtnEditarTrabalho(){
        if($this->txtBtnEditarTrabalho!=''){
           return $this->txtBtnEditarTrabalho; 
        }
        if($this->getCodEvento()==37 or $this->getCodEvento()==38){
//            return "Clique aqui para Solicitar Recurso";
        }
        if($this->getCodEvento()==46){
            return "Clique aqui para Preencher ou Editar Gabarito e ou Reanexar Documento Unificado";
        }
        return "Editar ".$this->getDescTrabAnexoTipo();
    }
    public function getTxtBtnEnviarTrabalho(){
        if($this->txtBtnEnviarTrabalho!=''){
           return $this->txtBtnEnviarTrabalho; 
        }
        if($this->getCodEvento()==37 or $this->getCodEvento()==38){
//            return "Clique aqui para Solicitar Recurso";
        }
        if($this->getCodEvento()==46){
            return "Clique aqui para Preencher ou Editar Gabarito e ou Reanexar Documento Unificado";
        }
        return "Adicionar ".$this->getDescTrabAnexoTipo();
    }
    public function getTxtBtnSalvarTrabalho(){
        if($this->txtBtnSalvarTrabalho!=''){
           return $this->txtBtnSalvarTrabalho; 
        }
        if($this->getCodEvento()==37 or $this->getCodEvento()==38){
            return "Clique aqui para Salvar sua Solicitação de Recurso";
        }
        return "Salvar ".$this->getDescTrabAnexoTipo();
    }
    public function getDescTrabAnexoTipo(){
        if($this->getCodEvento()==37 or $this->getCodEvento()==38){
            return "Documento Unificado";
        }
        if($this->getCodEvento()==46){
            return "Documento Unificado";
        }
        if($this->getTrabAnexoTipo()!=''){
           return $this->getTrabAnexoTipo(); 
        }
        return "Trabalho.";
    }
    public function testarPeriodoRealicacao($debug){
        return testaPeriodo($this->getRealizacaoIni(), $this->getRealizacaoFim(), $debug);
    }
    public function testarPeriodoInscricao($debug){
        return testaPeriodo($this->getDataInscricaoIni(), $this->getDataInscricaoFim(), $debug);
    }

    /**
     * Retorna true se o periodo para envio/edicao de trabalhos/documentos
     * ja se encerrou. Compara data atual com `trab_submissao_dat_fim`.
     * Se a coluna nao estiver configurada, retorna true (bloqueia por seguranca,
     * exigindo configuracao explicita do prazo no admin do evento).
     */
    public function isPeriodoEnvioTrabalhoEncerrado(){
        $dataFim = $this->getTrabSubmissaoDatFim();
        if (empty($dataFim) || $dataFim === '0000-00-00 00:00:00') {
            return true;
        }
        return date('Y-m-d H:i:s') > $dataFim;
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
        return true;
    }
    
    public function validatorEcluir(){
        $message = "";
        $return  = true;
        if(is_null($this->getCodEventoConfiguracao())or$this->getCodEventoConfiguracao()==""){
            $message .= "Código do evento não foi informado.<br>";
            $return = false;
        }
        if($return==false){
            throw new Exception($message);
            return false;
        }
        return true;
    }
}
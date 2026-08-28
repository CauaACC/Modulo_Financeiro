<?php

class Trabalho extends TrabalhoVO {

    private $objDAO;

    function __construct() {
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new TrabalhoDAO();
        $objEvConf = $this->getObjEventoConfiguracao(COD_EVENTO);
        $this->setObjEvConf($objEvConf);
        if(COD_EVENTO == 37 or COD_EVENTO==38){
            $this->txtMsgDocumento = "Solicitação de Recurso";
        }else{
            $this->txtMsgDocumento = $objEvConf->getTrabAnexoTipo();
        }
    }

    public function carregarObjInscrito() {
        $obj = new Inscrito();
        $obj->setCodInscrito($this->getCodInscrito());
        $obj->getInscritoById();
        $this->setObjInscrito($obj);
    }
    public function carregarListaUltimasAvaliacaoPremiacaoByIdTrabalho() {
        $obj = new HistoricoAvaliacaoPremiacao();
        $obj->setCodTrabalho($this->getCodTrabalho());
        $obj->setCodInscrito($this->getCodInscrito());
        $obj->carregarListaUltimasAvaliacaoPremiacaoByIdTrabalho();
    }
    public function carregarCodNovoTrabalho(){
        $this->setCodTrabalho($this->objDAO->getLastId());
    }
    public function TrabalhoExiste() {
        if (!$this->getCodTrabalho()) {
            throw new Exception('Cód. '.$this->txtMsgDocumento.' inválido ou não informado.');
        }
        if (!$this->objDAO->getByIdExiste($this)) {
            throw new Exception("Nenhum ".$this->txtMsgDocumento." foi encontrado para o código informado: " . $this->getCodTrabalho() . ".");
            //throw new Exception("Id informado para deleção não existe");
        }
    }

    public function novo($debug = false) {
        
    }

    public function salvar($debug = false) {
        /** @todo acrescentar aqui um teste para saber se esta na tela de editação ou na tela de inscrito */
        if($this->getTipoDocumento()=='COMPROVANTE_VINCULO' or $this->getTipoDocumento()=='COMPROVANTE_PERFIL'){ /*comprovantes anexados nos dados pessoais (122 / config por perfil)*/
            echo $debug? __LINE__.'-' : '';
            $this->validatorObjComprovanteVinculo();
        }else if($_SESSION['_administrativo']){
            echo $debug? __LINE__.'-' : '';
            $this->validatorObjAdmin();
        }else{
            echo $debug? __LINE__.'-' : '';
            $this->validatorObj();
        }
        echo $debug? __LINE__.'-' : '';
        if($debug){
            exit();
        }
        if (is_null($this->getCodTrabalho()) or $this->getCodTrabalho() == "" or $this->getNovo()==1) {
            echo $debug? __LINE__.'-' : '';
            if ($this->objDAO->insert($this, $debug)) {
                echo $debug? __LINE__.'-' : '';
                throw new OKException($this->txtMsgDocumento." salvo com sucesso.");
            } else {
                echo $debug? __LINE__.'-' : '';
                throw new Exception("Novo ".$this->txtMsgDocumento." não foi salvo.");
            }
        } else {
            $this->TrabalhoExiste($debug);

            if ($this->objDAO->update($this, $debug)) {
                echo $debug? __LINE__.'-' : '';
                throw new OKException($this->txtMsgDocumento." foi atualizado com sucesso.");
            } else {
                echo $debug? __LINE__.'-' : '';
                throw new Exception($this->txtMsgDocumento." atual não foi atualizado.");
            }
        }
    }
    public function salvarTarefa($debug = false) {
        /** @todo acrescentar aqui um teste para saber se esta na tela de editação ou na tela de inscrito */
        if($_SESSION['_administrativo']){
            echo $debug? __LINE__.'-' : '';
            $this->validatorObjAdmin();
        }else{
            echo $debug? __LINE__.'-' : '';
            $this->validatorObjTarefa();
        }
        echo $debug? __LINE__.'-' : '';
        if($debug){
            exit();
        }
        if (is_null($this->getCodTrabalho()) or $this->getCodTrabalho() == "" or $this->getNovo()==1) {
            echo $debug? __LINE__.'-' : '';
            if ($this->objDAO->insert($this, $debug)) {
                echo $debug? __LINE__.'-' : '';
                throw new OKException($this->txtMsgDocumento." salvo com sucesso.");
            } else {
                echo $debug? __LINE__.'-' : '';
                throw new Exception("Novo ".$this->txtMsgDocumento." não foi salvo.");
            }
        } else {
            $this->TrabalhoExiste($debug);

            if ($this->objDAO->update($this, $debug)) {
                echo $debug? __LINE__.'-' : '';
                throw new OKException($this->txtMsgDocumento." foi atualizado com sucesso.");
            } else {
                echo $debug? __LINE__.'-' : '';
                throw new Exception($this->txtMsgDocumento." atual não foi atualizado.");
            }
        }
    }
    public function enviarAvaliacao($debug = false) {
        $this->validatorObj();
        $this->TrabalhoExiste($debug);
        if ($this->objDAO->update($this, $debug)) {
            throw new OKException("Tramitação do ".$this->txtMsgDocumento." de avaliação executada com sucesso.");
        } else {
            throw new Exception("Tramitação do ".$this->txtMsgDocumento." de avaliação NÃO executada com sucesso.");
        }
    }
    public function salvarAvaliacao($debug = false) {
        $this->validatorEnvioAvaliacao();
        $this->TrabalhoExiste($debug);
        if ($this->objDAO->update($this, $debug)) {
            throw new OKException("Tramitação do ".$this->txtMsgDocumento." de avaliação executada com sucesso.");
        } else {
            throw new Exception("Tramitação do ".$this->txtMsgDocumento." de avaliação NÃO executada com sucesso.");
        }
    }
    public function salvarPremiacao($debug = false) {
        $this->validatorAlterarFlg();
        $this->TrabalhoExiste($debug);
        if ($this->objDAO->update($this, $debug)) {
            throw new OKException("Premiação definida com sucesso.");
        } else {
            throw new Exception("Premiação NÃO definida com sucesso.");
        }
    }
    public function salvarPresenca($debug = false) {
        $this->validatorAlterarFlg();
        $this->TrabalhoExiste($debug);
        if ($this->objDAO->update($this, $debug)) {
            throw new OKException("Presença alterada com sucesso.");
        } else {
            throw new Exception("Presença NÃO alterada com sucesso.");
        }
    }
    public function salvarAssinado($debug = false) {
        $this->validatorAlterarFlg();
        $this->TrabalhoExiste($debug);
        if ($this->objDAO->update($this, 1)) {
            throw new OKException("Assinado alterada com sucesso.");
        } else {
            throw new Exception("Assinado NÃO alterada com sucesso.");
        }
    }
    public function excluir($debug = false) {
        $this->validatorEcluir();
        if ($this->objDAO->delete($this, $debug)) {
            throw new OKException($this->txtMsgDocumento." cód:" . $this->getCodTrabalho() . " foi excluído com sucesso.");
        } else {
            throw new Exception($this->txtMsgDocumento." cód:" . $this->getCodTrabalho() . " não foi excluído.");
        }
    }

    public function getTrabalhoById($debug = false) {
        if($this->getCodTrabalho()==''){
            throw new Exception('Código do '.$this->txtMsgDocumento.' não foi informado.');
        }
        return $this->objDAO->getById($this, $debug);
    }

    public function getByCapituloUltimo($debug = false) {
        if($this->getCodAreaTematica()==''){
            throw new Exception('Capítulo não foi informado.');
        }
        return $this->objDAO->getByCapituloUltimo($this, $debug);
    }

    public function getByDocumentoUltimo($debug = false) {
        if($this->getTipoDocumento()==''){
            throw new Exception('Tipo de documento não foi informado.');
        }
        return $this->objDAO->getByDocumentoUltimo($this, $debug);
    }

    public function carregarTrabalhoByIdTrabalho($debug = false) {
        if($this->getCodTrabalho()==''){
            throw new Exception("Código de ".$this->txtMsgDocumento." não informado.");
        }
        if(!$this->objDAO->getByIdTrabalho($this, $debug)){
            throw new Exception($this->txtMsgDocumento." não encontrado.");
        }
    }
   
    public function getListaTrabalhosByIdEvento($debug = false) {
        if (!$this->getCodEvento()) {
            throw new Exception("Id do evento não informado.");
        }
        $this->objDAO->carregarListaTrabalhosByIdEvento($this, $debug);
        if ($this->getListaTrabalhos()) {
            return $this->getListaTrabalhos();
        } else {
            throw new Exception("Evento não possui ".$this->txtMsgDocumento." cadastrada.");
        }
    }
    
    public function carregarListaTrabalhos($condicoes = false , $debug=false){
        return $this->objDAO->carregarListaTrabalhos($this, $condicoes, $debug);
    }
    
    public function carregarListaTipoDocumentos($condicoes = false , $debug=false){
        return $this->objDAO->carregarListaTipoDocumentos($this, $condicoes, $debug);
    }
    
    public function carregarListaTrabalhosByIdInscrito($condicoes = false , $debug=false){
        if (!$this->getCodInscrito()) {
            throw new Exception("Id do inscrito não informado.");
        }
        $condicoes['cod_inscrito'] = $this->getCodInscrito();
        return $this->objDAO->carregarListaTrabalhos($this, $condicoes, $debug);
    }
    public function carregarListaTrabalhosByIdInscritoRecurso($condicoes = false , $debug=false){
        if (!$this->getCodInscrito()) {
            throw new Exception("Id do inscrito não informado.");
        }
        $condicoes['cod_inscrito'] = $this->getCodInscrito();
        $condicoes['tipoDocumento'] = 'RECURSO';
        return $this->objDAO->carregarListaTrabalhos($this, $condicoes, $debug);
    }

    public function carregarListaTrabalhosAceitosByIdInscrito($condicoes = false , $debug=false){
        if (!$this->getCodInscrito()) {
            throw new Exception("Id do inscrito não informado.");
        }
        $condicoes['cod_inscrito'] = $this->getCodInscrito();
        return $this->objDAO->carregarListaTrabalhosAceitosByIdInscrito($this, $condicoes, $debug);
    }

    public function getEvento() {
        $obj = new Evento();
        $obj->setCodEvento($this->getCodEvento());
        $obj->getEventoById();
        return $obj;
    }

    public function carregarDadosTrabalhos($condicoes = false , $debug=false){
        return $this->objDAO->carregarDadosTrabalhos($this, $condicoes, $debug);
    }

    public function getSlugsDocumentosEntregues($slugs, $debug = false) {
        if (!$this->getCodInscrito()) {
            throw new Exception("Id do inscrito não informado.");
        }
        return $this->objDAO->getSlugsDocumentosEntregues($this, $slugs, $debug);
    }

    public function marcarLegibilidade($debug = false) {
        if (!$this->getCodTrabalho()) {
            throw new Exception("Código do documento não informado.");
        }
        if (!$this->objDAO->marcarLegibilidade($this, $debug)) {
            throw new Exception("Não foi possível atualizar a legibilidade do documento.");
        }
        return true;
    }
}

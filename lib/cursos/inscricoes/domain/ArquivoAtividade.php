<?php

class ArquivoAtividade extends ArquivoAtividadeVO {

    private $objDAO;

    function __construct() {
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new ArquivoAtividadeDAO();   
    }

    public function getLastId(){
        return $this->objDAO->getLastId($this);
    }
    public function arquivoatividadeExiste() {
        if (!$this->getCodArquivoAtividade()) {
            throw new Exception('Código da ArquivoAtividade inválido ou não informado.');
        }
        if (!$this->objDAO->getById($this)) {
            throw new Exception("Nenhuma ArquivoAtividade  foi encontrada para o código informado: " . $this->getCodArquivoAtividade() . ".");
            //throw new Exception("Id informado para deleção não existe");
        }
    }

    public function novo($debug = false) {
        
    }

    public function substituirArquivoAtividade($debug = false) {
        $this->validatorObj();
        $this->objDAO->desativar($this, $debug);
        $this->setCodArquivoAtividade(NULL);
        if ($this->objDAO->insert($this, $debug)) {
            throw new OKException("ArquivoAtividade salvo com sucesso.");
        } else {
            throw new Exception("Novo ArquivoAtividade não foi salvo.");
        }
    }
    public function salvar($debug = false) {
        
        $this->validatorObj();
        if (is_null($this->getCodArquivoAtividade()) or $this->getCodArquivoAtividade() == "") {
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("ArquivoAtividade salvo com sucesso.");
            } else {
                throw new Exception("Novo ArquivoAtividade não foi salvo.");
            }
        } else {
            $this->arquivoatividadeExiste($debug);

            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("ArquivoAtividade foi atualizada com sucesso.");
            } else {
                throw new Exception("ArquivoAtividade atual não foi atualizada.");
            }
        }
    }
    
    public function atualizar($debug=false) {
        if ($this->getCodArquivoAtividade() and $this->getCodArquivoAtividade() != "") {
            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("ArquivoAtividade processado com sucesso.");
            } else {
                throw new Exception("ArquivoAtividade não foi processo com sucesso.");
            }
        }
    }
    
    public function excluir($debug = false) {
        $this->validatorEcluir();
        if ($this->objDAO->delete($this, $debug)) {
            throw new OKException("ArquivoAtividade  cód:" . $this->getCodArquivoAtividade() . " foi excluído com sucesso.");
        } else {
            throw new Exception("ArquivoAtividade  cód:" . $this->getCodArquivoAtividade() . " não foi excluído.");
        }
    }
    
    public function desativar($debug = false) {
        $this->validatorEcluir();
        if ($this->objDAO->desativar($this, $debug)) {
            throw new OKException("ArquivoAtividade cód:" . $this->getCodArquivoAtividade() . " foi excluído com sucesso.");
        } else {
            throw new Exception("ArquivoAtividade cód:" . $this->getCodArquivoAtividade() . " não foi excluído.");
        }
    }

    public function carregarArquivoAtividadeById($debug = false) {
        return $this->objDAO->getById($this, $debug);
    }

    public function carregarListaArquivoAtividadeByIdEvento($debug = false) {
        $this->objDAO->carregarListaArquivoAtividadeByIdEvento($this, $debug);
        if ($this->getListaArquivoAtividade()) {
        } else {
            throw new Exception("Evento não possui ArquivoAtividade cadastrada.");
        }
    }

    function carregarArquivoAtividadeByIdOficinaAssCoordenador($debug = false){
        return $this->objDAO->carregarArquivoAtividadeOficinaAssCoordenadorBy($this, $condicoes, $debug);
    }
    function carregarArquivoAtividadeByIdOficinaPrograma($debug = false){
        return $this->objDAO->carregarArquivoAtividadeOficinaProgramaBy($this, $condicoes, $debug);
    }
    function carregarListaArquivoAtividadeBy($condicoes, $debug = false){
        $this->objDAO->carregarListaArquivoAtividadeBy($this, $condicoes, $debug);
        if ($this->getListaArquivoAtividade()) {
        } else {
            throw new Exception("Anexo não cadastrado.");
        }        
    }
    public function getEvento() {
        $obj = new Evento();
        $obj->setCodEvento($this->getCodEvento());
        $obj->getEventoById();
        return $obj;
    }
    public function getEventoConfiguracao() {
        $obj = new EventoConfiguracao();
        $obj->setCodEvento($this->getCodEvento());
        $obj->getEventoConfiguracaoById();
        return $obj;
    }
}


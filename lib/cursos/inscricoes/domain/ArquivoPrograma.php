<?php

class ArquivoPrograma extends ArquivoProgramaVO {

    private $objDAO;

    function __construct() {
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new ArquivoProgramaDAO();   
    }

    public function arquivoProgramaExiste() {
        if (!$this->getCodArquivoPrograma()) {
            throw new Exception('Código da Arquivo Programa inválido ou não informado.');
        }
        if (!$this->objDAO->getByIdExiste($this)) {
            throw new Exception("Nenhuma Arquivo Programa foi encontrada para o código informado: " . $this->getCodArquivoPrograma() . ".");
            //throw new Exception("Id informado para deleção não existe");
        }
    }

    public function novo($debug = false) {
        
    }

    public function substituirArquivo($debug = false) {
        $this->validatorObj();
        $this->objDAO->desativar($this, $debug);
        $this->setCodArquivoPrograma(NULL);
        if ($this->objDAO->insert($this, $debug)) {
            throw new OKException("Arquivo Programa salvo com sucesso.");
        } else {
            throw new Exception("Novo Arquivo Programa não foi salvo.");
        }
    }
    public function salvar($debug = false) {
        
        $this->validatorObj();
        if (is_null($this->getCodArquivoPrograma()) or $this->getCodArquivoPrograma() == "") {
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Arquivo Programa salvo com sucesso.");
            } else {
                throw new Exception("Novo Arquivo Programa não foi salvo.");
            }
        } else {
            $this->arquivoapresentacaoExiste($debug);

            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Arquivo Programa foi atualizada com sucesso.");
            } else {
                throw new Exception("Arquivo Programa atual não foi atualizada.");
            }
        }
    }

    public function excluir($debug = false) {
        $this->validatorEcluir();
        if ($this->objDAO->delete($this, $debug)) {
            throw new OKException("Arquivo Programa cód:" . $this->getCodArquivoPrograma() . " foi excluído com sucesso.");
        } else {
            throw new Exception("Arquivo Programa cód:" . $this->getCodArquivoPrograma() . " não foi excluído.");
        }
    }
    
    public function desativar($debug = false) {
        $this->validatorEcluir();
        if ($this->objDAO->desativar($this, $debug)) {
            throw new OKException("Arquivo Programa cód:" . $this->getCodArquivoPrograma() . " foi excluído com sucesso.");
        } else {
            throw new Exception("Arquivo Programa cód:" . $this->getCodArquivoPrograma() . " não foi excluído.");
        }
    }

    public function carregarArquivoProgramaById($debug = false) {
        return $this->objDAO->getByIdExiste($this, $debug);
    }

    public function carregarListaArquivoProgramaByIdEvento($debug = false) {
        $this->objDAO->carregarListaArquivoProgramaByIdEvento($this, $debug);
        if ($this->getListaArquivoPrograma()) {
        } else {
            throw new Exception("Evento não possui Arquivo Programa cadastrada.");
        }
    }

    function carregarListaArquivoProgramaBy($condicoes, $debug = false){
        $this->objDAO->carregarListaArquivoProgramaBy($this, $condicoes, $debug);
        if ($this->getListaArquivoPrograma()) {
        } else {
            throw new Exception("Anexo não cadastrado.");
        }        
    }
    public function getEvento() {
        $obj = new InscEvento();
        $obj->setCodEvento($this->getCodEvento());
        $obj->getEventoById();
        return $obj;
    }

}


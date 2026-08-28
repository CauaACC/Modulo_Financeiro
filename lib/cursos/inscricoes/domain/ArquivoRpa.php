<?php

class ArquivoRpa extends ArquivoRpaVO {

    private $objDAO;

    function __construct() {
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new ArquivoRpaDAO();   
    }

    public function arquivoRpaExiste() {
        if (!$this->getCodArquivoRpa()) {
            throw new Exception('Código da Arquivo Rpa inválido ou não informado.');
        }
        if (!$this->objDAO->getByIdExiste($this)) {
            throw new Exception("Nenhuma Arquivo Rpa foi encontrada para o código informado: " . $this->getCodArquivoRpa() . ".");
            //throw new Exception("Id informado para deleção não existe");
        }
    }

    public function novo($debug = false) {
        
    }

    public function substituirArquivo($debug = false) {
        $this->validatorObj();
        $this->objDAO->desativar($this, $debug);
        $this->setCodArquivoRpa(NULL);
        if ($this->objDAO->insert($this, $debug)) {
            throw new OKException("Arquivo Rpa salvo com sucesso.");
        } else {
            throw new Exception("Novo Arquivo Rpa não foi salvo.");
        }
    }
    public function salvar($debug = false) {
        
        $this->validatorObj();
        if (is_null($this->getCodArquivoRpa()) or $this->getCodArquivoRpa() == "") {
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Arquivo Rpa salvo com sucesso.");
            } else {
                throw new Exception("Novo Arquivo Rpa não foi salvo.");
            }
        } else {
            $this->arquivoRpaExiste($debug);

            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Arquivo Rpa foi atualizada com sucesso.");
            } else {
                throw new Exception("Arquivo Rpa atual não foi atualizada.");
            }
        }
    }

    public function excluir($debug = false) {
        $this->validatorEcluir();
        if ($this->objDAO->delete($this, $debug)) {
            throw new OKException("Arquivo Rpa cód:" . $this->getCodArquivoRpa() . " foi excluído com sucesso.");
        } else {
            throw new Exception("Arquivo Rpa cód:" . $this->getCodArquivoRpa() . " não foi excluído.");
        }
    }
    
    public function desativar($debug = false) {
        $this->validatorEcluir();
        if ($this->objDAO->desativar($this, $debug)) {
            throw new OKException("Arquivo Rpa cód:" . $this->getCodArquivoRpa() . " foi excluído com sucesso.");
        } else {
            throw new Exception("Arquivo Rpa cód:" . $this->getCodArquivoRpa() . " não foi excluído.");
        }
    }

    public function carregarArquivoRpaById($debug = false) {
        return $this->objDAO->getByIdExiste($this, $debug);
    }

    public function carregarListaArquivoRpaByIdEvento($debug = false) {
        $this->objDAO->carregarListaArquivoRpaByIdEvento($this, $debug);
        if ($this->getListaArquivoRpa()) {
        } else {
            throw new Exception("Evento não possui Arquivo Rpa cadastrada.");
        }
    }

    function carregarListaArquivoRpaBy($condicoes, $debug = false){
        $this->objDAO->carregarListaArquivoRpaBy($this, $condicoes, $debug);
        if ($this->getListaArquivoRpa()) {
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

?>
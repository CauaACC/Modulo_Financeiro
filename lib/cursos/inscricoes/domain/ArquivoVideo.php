<?php

class ArquivoVideo extends ArquivoVideoVO {

    private $objDAO;

    function __construct() {
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new ArquivoVideoDAO();   
    }

    public function arquivoApresentacaoExiste() {
        if (!$this->getCodArquivoVideo()) {
            throw new Exception('Código da Arquivo Video inválido ou não informado.');
        }
        if (!$this->objDAO->getByIdExiste($this)) {
            throw new Exception("Nenhuma Arquivo Video foi encontrada para o código informado: " . $this->getCodArquivoVideo() . ".");
            //throw new Exception("Id informado para deleção não existe");
        }
    }

    public function novo($debug = false) {
        
    }

    public function substituirArquivo($debug = false) {
        $this->validatorObj();
        $this->objDAO->desativar($this, $debug);
        $this->setCodArquivoVideo(NULL);
        if ($this->objDAO->insert($this, $debug)) {
            throw new OKException("Arquivo Video salvo com sucesso.");
        } else {
            throw new Exception("Novo Arquivo Video não foi salvo.");
        }
    }
    public function salvar($debug = false) {
        
        $this->validatorObj();
        if (is_null($this->getCodArquivoVideo()) or $this->getCodArquivoVideo() == "") {
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Arquivo Video salvo com sucesso.");
            } else {
                throw new Exception("Novo Arquivo Video não foi salvo.");
            }
        } else {
            $this->arquivoapresentacaoExiste($debug);

            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Arquivo Video foi atualizada com sucesso.");
            } else {
                throw new Exception("Arquivo Video atual não foi atualizada.");
            }
        }
    }

    public function excluir($debug = false) {
        $this->validatorEcluir();
        if ($this->objDAO->delete($this, $debug)) {
            throw new OKException("Arquivo Video cód:" . $this->getCodArquivoVideo() . " foi excluído com sucesso.");
        } else {
            throw new Exception("Arquivo Video cód:" . $this->getCodArquivoVideo() . " não foi excluído.");
        }
    }
    
    public function desativar($debug = false) {
        $this->validatorEcluir();
        if ($this->objDAO->desativar($this, $debug)) {
            throw new OKException("Arquivo Video cód:" . $this->getCodArquivoVideo() . " foi excluído com sucesso.");
        } else {
            throw new Exception("Arquivo Video cód:" . $this->getCodArquivoVideo() . " não foi excluído.");
        }
    }

    public function carregarArquivoVideoById($debug = false) {
        return $this->objDAO->getByIdExiste($this, $debug);
    }

    public function carregarListaArquivoVideoByIdEvento($debug = false) {
        $this->objDAO->carregarListaArquivoVideoByIdEvento($this, $debug);
        if ($this->getListaArquivoVideo()) {
        } else {
            throw new Exception("Evento não possui Arquivo Video cadastrada.");
        }
    }

    function carregarListaArquivoVideoBy($condicoes, $debug = false){
        $this->objDAO->carregarListaArquivoVideoBy($this, $condicoes, $debug);
        if ($this->getListaArquivoVideo()) {
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
<?php

class ArquivoApresentacao extends ArquivoApresentacaoVO {

    private $objDAO;

    function __construct() {
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new ArquivoApresentacaoDAO();   
    }

    public function arquivoApresentacaoExiste() {
        if (!$this->getCodArquivoApresentacao()) {
            throw new Exception('Código da Arquivo Apresentacao inválido ou não informado.');
        }
        if (!$this->objDAO->getByIdExiste($this)) {
            throw new Exception("Nenhuma Arquivo Apresentacao foi encontrada para o código informado: " . $this->getCodArquivoApresentacao() . ".");
            //throw new Exception("Id informado para deleção não existe");
        }
    }

    public function novo($debug = false) {
        
    }

    public function substituirArquivo($debug = false) {
        $this->validatorObj();
        $this->objDAO->desativar($this, $debug);
        $this->setCodArquivoApresentacao(NULL);
        if ($this->objDAO->insert($this, $debug)) {
            throw new OKException("Arquivo Apresentacao salvo com sucesso.");
        } else {
            throw new Exception("Novo Arquivo Apresentacao não foi salvo.");
        }
    }
    public function salvar($debug = false) {
        
        $this->validatorObj();
        if (is_null($this->getCodArquivoApresentacao()) or $this->getCodArquivoApresentacao() == "") {
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Arquivo Apresentacao salvo com sucesso.");
            } else {
                throw new Exception("Novo Arquivo Apresentacao não foi salvo.");
            }
        } else {
            $this->arquivoapresentacaoExiste($debug);

            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Arquivo Apresentacao foi atualizada com sucesso.");
            } else {
                throw new Exception("Arquivo Apresentacao atual não foi atualizada.");
            }
        }
    }

    public function excluir($debug = false) {
        $this->validatorEcluir();
        if ($this->objDAO->delete($this, $debug)) {
            throw new OKException("Arquivo Apresentacao cód:" . $this->getCodArquivoApresentacao() . " foi excluído com sucesso.");
        } else {
            throw new Exception("Arquivo Apresentacao cód:" . $this->getCodArquivoApresentacao() . " não foi excluído.");
        }
    }
    
    public function desativar($debug = false) {
        $this->validatorEcluir();
        if ($this->objDAO->desativar($this, $debug)) {
            throw new OKException("Arquivo Apresentacao cód:" . $this->getCodArquivoApresentacao() . " foi excluído com sucesso.");
        } else {
            throw new Exception("Arquivo Apresentacao cód:" . $this->getCodArquivoApresentacao() . " não foi excluído.");
        }
    }

    public function carregarArquivoApresentacaoById($debug = false) {
        return $this->objDAO->getByIdExiste($this, $debug);
    }

    public function carregarListaArquivoApresentacaoByIdEvento($debug = false) {
        $this->objDAO->carregarListaArquivoApresentacaoByIdEvento($this, $debug);
        if ($this->getListaArquivoApresentacao()) {
        } else {
            throw new Exception("Evento não possui Arquivo Apresentacao cadastrada.");
        }
    }

    function carregarListaArquivoApresentacaoBy($condicoes, $debug = false){
        $this->objDAO->carregarListaArquivoApresentacaoBy($this, $condicoes, $debug);
        if ($this->getListaArquivoApresentacao()) {
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
<?php

class ArquivoAvulso extends ArquivoAvulsoVO {

    private $objDAO;

    function __construct() {
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new ArquivoAvulsoDAO();   
    }

    public function arquivoAvulsoExiste() {
        if (!$this->getCodArquivoAvulso()) {
            throw new Exception('Código da Arquivo Avulso inválido ou não informado.');
        }
        if (!$this->objDAO->getByIdExiste($this)) {
            throw new Exception("Nenhuma Arquivo Avulso foi encontrada para o código informado: " . $this->getCodArquivoAvulso() . ".");
            //throw new Exception("Id informado para deleção não existe");
        }
    }

    public function novo($debug = false) {
        
    }

    public function substituirArquivo($debug = false) {
        $this->validatorObj();
        $this->objDAO->desativar($this, $debug);
        $this->setCodArquivoAvulso(NULL);
        if ($this->objDAO->insert($this, $debug)) {
            throw new OKException("Arquivo Avulso salvo com sucesso.");
        } else {
            throw new Exception("Novo Arquivo Avulso não foi salvo.");
        }
    }
    public function salvar($debug = false) {
        
        $this->validatorObj();
        if (is_null($this->getCodArquivoAvulso()) or $this->getCodArquivoAvulso() == "") {
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Arquivo Avulso salvo com sucesso.");
            } else {
                throw new Exception("Novo Arquivo Avulso não foi salvo.");
            }
        } else {
            $this->arquivoAvulsoExiste($debug);

            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Arquivo Avulso foi atualizada com sucesso.");
            } else {
                throw new Exception("Arquivo Avulso atual não foi atualizada.");
            }
        }
    }

    public function excluir($debug = false) {
        $this->validatorEcluir();
        if ($this->objDAO->delete($this, $debug)) {
            throw new OKException("Arquivo Avulso cód:" . $this->getCodArquivoAvulso() . " foi excluído com sucesso.");
        } else {
            throw new Exception("Arquivo Avulso cód:" . $this->getCodArquivoAvulso() . " não foi excluído.");
        }
    }
    
    public function desativar($debug = false) {
        $this->validatorEcluir();
        if ($this->objDAO->desativar($this, $debug)) {
            throw new OKException("Arquivo Avulso cód:" . $this->getCodArquivoAvulso() . " foi excluído com sucesso.");
        } else {
            throw new Exception("Arquivo Avulso cód:" . $this->getCodArquivoAvulso() . " não foi excluído.");
        }
    }

    public function carregarArquivoAvulsoById($debug = false) {
        return $this->objDAO->getByIdExiste($this, $debug);
    }

    public function carregarListaArquivoAvulsoByIdEvento($debug = false) {
        $this->objDAO->carregarListaArquivoAvulsoByIdEvento($this, $debug);
        if ($this->getListaArquivoAvulso()) {
        } else {
            throw new Exception("Evento não possui Arquivo Avulso cadastrada.");
        }
    }

    function carregarListaArquivoAvulsoBy($condicoes, $debug = false){
        $this->objDAO->carregarListaArquivoAvulsoBy($this, $condicoes, $debug);
        if ($this->getListaArquivoAvulso()) {
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
<?php

class ArquivoBoleto extends ArquivoBoletoVO {

    private $objDAO;

    function __construct() {
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new ArquivoBoletoDAO();   
    }

    public function arquivoBoletoExiste() {
        if (!$this->getCodArquivoBoleto()) {
            throw new Exception('Código da Arquivo Boleto inválido ou não informado.');
        }
        if (!$this->objDAO->getByIdExiste($this)) {
            throw new Exception("Nenhuma Arquivo Boleto foi encontrada para o código informado: " . $this->getCodArquivoBoleto() . ".");
            //throw new Exception("Id informado para deleção não existe");
        }
    }

    public function novo($debug = false) {
        
    }

    public function substituirArquivo($debug = false) {
        $this->validatorObj();
        $this->objDAO->desativar($this, $debug);
        $this->setCodArquivoBoleto(NULL);
        if ($this->objDAO->insert($this, $debug)) {
            throw new OKException("Arquivo Boleto salvo com sucesso.");
        } else {
            throw new Exception("Novo Arquivo Boleto não foi salvo.");
        }
    }
    public function salvar($debug = false) {
        
        $this->validatorObj();
        if (is_null($this->getCodArquivoBoleto()) or $this->getCodArquivoBoleto() == "") {
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Arquivo Boleto salvo com sucesso.");
            } else {
                throw new Exception("Novo Arquivo Boleto não foi salvo.");
            }
        } else {
            $this->arquivoBoletoExiste($debug);

            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Arquivo Boleto foi atualizada com sucesso.");
            } else {
                throw new Exception("Arquivo Boleto atual não foi atualizada.");
            }
        }
    }

    public function excluir($debug = false) {
        $this->validatorEcluir();
        if ($this->objDAO->delete($this, $debug)) {
            throw new OKException("Arquivo Boleto cód:" . $this->getCodArquivoBoleto() . " foi excluído com sucesso.");
        } else {
            throw new Exception("Arquivo Boleto cód:" . $this->getCodArquivoBoleto() . " não foi excluído.");
        }
    }
    
    public function desativar($debug = false) {
        $this->validatorEcluir();
        if ($this->objDAO->desativar($this, $debug)) {
            throw new OKException("Arquivo Boleto cód:" . $this->getCodArquivoBoleto() . " foi excluído com sucesso.");
        } else {
            throw new Exception("Arquivo Boleto cód:" . $this->getCodArquivoBoleto() . " não foi excluído.");
        }
    }

    public function carregarArquivoBoletoById($debug = false) {
        return $this->objDAO->getByIdExiste($this, $debug);
    }

    public function carregarListaArquivoBoletoByIdEvento($debug = false) {
        $this->objDAO->carregarListaArquivoBoletoByIdEvento($this, $debug);
        if ($this->getListaArquivoBoleto()) {
        } else {
            throw new Exception("Evento não possui Arquivo Boleto cadastrada.");
        }
    }

    function carregarListaArquivoBoletoBy($condicoes, $debug = false){
        $this->objDAO->carregarListaArquivoBoletoBy($this, $condicoes, $debug);
        if ($this->getListaArquivoBoleto()) {
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
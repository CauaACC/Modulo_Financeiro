<?php

class ArquivoContrato extends ArquivoContratoVO {

    private $objDAO;

    function __construct() {
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new ArquivoContratoDAO();   
    }

    public function arquivoContratoExiste() {
        if (!$this->getCodArquivoContrato()) {
            throw new Exception('Código da Arquivo Contrato inválido ou não informado.');
        }
        if (!$this->objDAO->getByIdExiste($this)) {
            throw new Exception("Nenhuma Arquivo Contrato foi encontrada para o código informado: " . $this->getCodArquivoContrato() . ".");
            //throw new Exception("Id informado para deleção não existe");
        }
    }

    public function novo($debug = false) {
        
    }

    public function substituirArquivo($debug = false) {
        $this->validatorObj();
        $this->objDAO->desativar($this, $debug);
        $this->setCodArquivoContrato(NULL);
        if ($this->objDAO->insert($this, $debug)) {
            throw new OKException("Arquivo Contrato salvo com sucesso.");
        } else {
            throw new Exception("Novo Arquivo Contrato não foi salvo.");
        }
    }
    public function salvar($debug = false) {
        
        $this->validatorObj();
        if (is_null($this->getCodArquivoContrato()) or $this->getCodArquivoContrato() == "") {
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Arquivo Contrato salvo com sucesso.");
            } else {
                throw new Exception("Novo Arquivo Contrato não foi salvo.");
            }
        } else {
            $this->arquivoContratoExiste($debug);

            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Arquivo Contrato foi atualizada com sucesso.");
            } else {
                throw new Exception("Arquivo Contrato atual não foi atualizada.");
            }
        }
    }

    public function excluir($debug = false) {
        $this->validatorEcluir();
        if ($this->objDAO->delete($this, $debug)) {
            throw new OKException("Arquivo Contrato cód:" . $this->getCodArquivoContrato() . " foi excluído com sucesso.");
        } else {
            throw new Exception("Arquivo Contrato cód:" . $this->getCodArquivoContrato() . " não foi excluído.");
        }
    }
    
    public function desativar($debug = false) {
        $this->validatorEcluir();
        if ($this->objDAO->desativar($this, $debug)) {
            throw new OKException("Arquivo Contrato cód:" . $this->getCodArquivoContrato() . " foi excluído com sucesso.");
        } else {
            throw new Exception("Arquivo Contrato cód:" . $this->getCodArquivoContrato() . " não foi excluído.");
        }
    }

    public function carregarArquivoContratoById($debug = false) {
        return $this->objDAO->getByIdExiste($this, $debug);
    }

    public function carregarListaArquivoContratoByIdEvento($debug = false) {
        $this->objDAO->carregarListaArquivoContratoByIdEvento($this, $debug);
        if ($this->getListaArquivoContrato()) {
        } else {
            throw new Exception("Evento não possui Arquivo Contrato cadastrada.");
        }
    }

    function carregarListaArquivoContratoBy($condicoes, $debug = false){
        if($condicoes=='cod_trabalho' and $this->getCodTrabalho()==''){
            throw new Exception("Código do Trabalho inválido ou não informado.");
        }
        if($condicoes=='cod_inscrito' and $this->getCodInscrito()==''){
            throw new Exception("Código do Inscrito inválido ou não informado.");
        }
        $this->objDAO->carregarListaArquivoContratoBy($this, $condicoes, $debug);
        if ($this->getListaArquivoContrato()) {
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

}

?>
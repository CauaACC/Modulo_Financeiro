<?php

class PrevisaoOrcamentaria extends PrevisaoOrcamentariaVO {

    private $objDAO;

    function __construct() {
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new PrevisaoOrcamentariaDAO();
    }

    public function PrevisaoOrcamentariaExiste() {
        if (!$this->getCodPrevisaoOrcamentaria()) {
            throw new Exception('Código da Previsão Orcamentária inválido ou não informado.');
        }
        if (!$this->objDAO->getByIdExiste($this)) {
            throw new Exception("Nenhuma Previsão Orcamentária foi encontrada para o código informado: " . $this->getCodPrevisaoOrcamentaria() . ".");
            //throw new Exception("Id informado para deleção não existe");
        }
    }

    public function novo($debug = false) {
        
    }

    public function salvar($debug = false) {
        /* Carrega os atributos do InscTipoReceita Despesa para Setar a tabela 
         * de PrevisaoOrcamentaria.
         */
        $obj = new TipoReceitaDespesa();
        $obj->setCodTipoReceitaDespesa($this->getCodTipoReceitaDespesa());
        $obj->carregarObjTipoReceitaDespesaById();
        $this->setNomTipoReceitaDespesa($obj->getNomeTipoReceitaDespesa());
        $this->setDesTipoReceitaDespesa($obj->getDesTipoReceitaDespesa());
        
        $string = str_replace(".","",$this->getVlrUnitario());
        $string = str_replace(",",".",$string);
        $this->setVlrUnitario($string);
        $string = str_replace(".","",$this->getVlrTotal());
        $string = str_replace(",",".",$string);
        $this->setVlrTotal($string);
        
        
        $this->validatorObj();
        if (is_null($this->getCodPrevisaoOrcamentaria()) or $this->getCodPrevisaoOrcamentaria() == "") {
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Previsão Orcamentária salva com sucesso.");
            } else {
                throw new Exception("Novo Previsão Orcamentária não foi salva.");
            }
        } else {
            $this->PrevisaoOrcamentariaExiste($debug);

            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Previsão Orcamentária foi atualizada com sucesso.");
            } else {
                throw new Exception("Previsão Orcamentária atual não foi atualizada.");
            }
        }
    }

    public function excluir($debug = false) {
        $this->validatorEcluir();
        if ($this->objDAO->delete($this, $debug)) {
            throw new OKException("Previsão Orcamentária cód:" . $this->getCodPrevisaoOrcamentaria() . " foi excluída com sucesso.");
        } else {
            throw new Exception("Previsão Orcamentária cód:" . $this->getCodPrevisaoOrcamentaria() . " não foi excluída.");
        }
    }

    public function getPrevisaoOrcamentariaById($debug = false) {
        $this->objDAO->getById($this, $debug);
    }

    public function getListaPrevisaoOrcamentariasByIdEvento($debug = false) {
        if (!$this->getCodEvento()) {
            throw new Exception("Id do evento não informado.");
        }
        $this->objDAO->carregarListaPrevisaoOrcamentariasByIdEvento($this, $debug);
        if ($this->getListaPrevisaoOrcamentarias()) {
            return $this->getListaPrevisaoOrcamentarias();
        } else {
            throw new Exception("Evento não possui Previsão Orcamentária cadastrada.");
        }
    }

}

?>
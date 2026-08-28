<?php
class TipoReceitaDespesa extends TipoReceitaDespesaVO{
    
    private $objDAO;
    
    function __construct() {
        $this->objDAO = new TipoReceitaDespesaDAO();        
    }
    public function TipoReceitaDespesaExiste() {
        if (!$this->getCodTipoReceitaDespesa()) {
            throw new Exception('Código do Tipo Receita Despesa inválido ou não informado.');
        }
        if (!$this->objDAO->getByIdExiste($this)) {
            throw new Exception("Nenhuma Tipo Receita Despesa foi encontrada para o código informado: " . $this->getCodTipoReceitaDespesa() . ".");
        }
    }    
    public function novo($debug=false) {

    }    
    public function salvar($debug=false) {
        $this->validarSalvarObj();
        if (is_null($this->getCodTipoReceitaDespesa()) or $this->getCodTipoReceitaDespesa() == "") {
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Tipo Receita Despesa salvo com sucesso.");
            } else {
                throw new Exception("Novo Tipo Receita Despesa não foi salvo.");
            }
        } else {
            $this->TipoReceitaDespesaExiste($debug);

            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Tipo Receita Despesa foi atualizado com sucesso.");
            } else {
                throw new Exception("Tipo Receita Despesa atual não foi atualizada.");
            }
        }
    }
    public function excluir($debug=false) {
        $this->validarEcluirObj();
        if($this->objDAO->delete($this, $debug)){
            throw new OKException("Tipo Receita Despesa cód:".$this->getCodTipoReceitaDespesa()." foi excluída com sucesso.");
        } else {
            throw new Exception("Tipo Receita Despesa cód:".$this->getCodTipoReceitaDespesa()." não foi excluída.");
        }
    }    
    public function carregarObjTipoReceitaDespesaById($debug=false){
        $this->objDAO->getById($this,$debug);
    } 
    public function carregarListaTipoReceitaDespesas($debug=false){
        $this->objDAO->carregarListaTipoReceitaDespesas($this,$debug);
        if(!$this->getListaTipoReceitaDespesas()){
            throw new Exception("Evento não possui Tipo Receita Despesas cadastrados.");
        }        
    }
    
    public function carregarListaReceitaDespesaByTipo($debug=false)
    {
        $this->objDAO->getAll($this,$debug);
    }
  
}
?>
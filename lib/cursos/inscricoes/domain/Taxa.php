<?php

class Taxa extends TaxaVO {

    private $objDAO;

    function __construct() {
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new TaxaDAO();
    }

    public function TaxaExiste() {
        if (!$this->getCodTaxa()) {
            throw new Exception('Código da Taxa inválido ou não informado.');
        }
        if (!$this->objDAO->getByIdExiste($this)) {
            throw new Exception("Nenhuma Taxa foi encontrada para o código informado: " . $this->getCodTaxa() . ".");
            //throw new Exception("Id informado para deleção não existe");
        }
    }

    public function novo($debug = false) {
        
    }

    public function salvar($debug = false) {
        
        $this->validatorObj();
        if (is_null($this->getCodTaxa()) or $this->getCodTaxa() == "") {
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Taxa salva com sucesso.");
            } else {
                throw new Exception("Novo Taxa não foi salva.");
            }
        } else {
            $this->taxaExiste($debug);

            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Taxa foi atualizada com sucesso.");
            } else {
                throw new Exception("Taxa atual não foi atualizada.");
            }
        }
    }

    public function excluir($debug = false) {
        $this->validatorEcluir();
        if ($this->objDAO->delete($this, $debug)) {
            throw new OKException("Taxa cód:" . $this->getCodTaxa() . " foi excluída com sucesso.");
        } else {
            throw new Exception("Taxa cód:" . $this->getCodTaxa() . " não foi excluída.");
        }
    }

    public function getTaxaById($debug = false) {
        return $this->objDAO->getById($this, $debug);
    }

    public function getListaTaxasByIdEvento($debug = false) {
        if (!$this->getCodEvento()) {
            throw new Exception("Id do evento não informado.");
        }
        $this->objDAO->carregarListaTaxassByIdEvento($this, $debug);
        if ($this->getListaTaxass()) {
            return $this->getListaTaxass();
        } else {
            throw new Exception("Evento não possui Taxa cadastrada.");
        }
    }
    public function carregarListaTaxasByIdEvento($debug = false) {
        $this->objDAO->carregarListaTaxasByIdEvento($this, $debug);
        if ($this->getListaTaxas()) {
        } else {
            throw new Exception("Evento não possui Taxa cadastrada.");
        }
    }

    function carregarListaTaxasBy($condicoes, $orderby=false, $debug = false){
        $this->objDAO->carregarListaTaxasBy($this, $condicoes, $orderby, $debug);
        if ($this->getListaTaxas()) {
        } else {
            throw new Exception("Consulta não retornou registros de Taxa. (cod.".__LINE__.")");
        }        
    }
    function carregarTaxaAtiva($condicoes, $debug = false){
        $this->objDAO->carregarListaTaxasBy($this, $condicoes, $debug);
        if ($this->getListaTaxas()) {
        } else {
            throw new Exception("Consulta não retornou registros de Taxa. (cod.".__LINE__.")");
        }        
    }
    function carregarListaTaxas($condicoes = false, $debug = false){
        $this->objDAO->carregarListaTaxas($this, $condicoes, $debug);
        if ($this->getListaTaxas()) {
        } else {
            throw new Exception("Consulta não retornou registros de Taxa. (cod.".__LINE__.")");
        }        
    }
    function carregarTaxaMensalidade($condicoes = false, $debug = false){
        $this->objDAO->carregarListaTaxasByMensalidade($this, $condicoes, $debug);
        if ($this->getListaTaxas()) {
        } else {
            throw new Exception("Consulta não retornou registros de Taxa. (cod.".__LINE__.")");
        }        
    }
    public function getEvento() {
        $obj = new Evento();
        $obj->setCodEvento($this->getCodEvento());
        $obj->getEventoById();
        return $obj;
    }
        
    public function duplicar($debug = false) {
        $this->validatorEcluir();
        $codTaxaAnt = $this->getCodTaxa();
        if ($this->objDAO->duplicar($this, $debug)) {
            throw new OKException("Taxa cód: ".$codTaxaAnt." duplicada para ". $this->getCodTaxa() . " foi duplicada com sucesso.");
        } else {
            throw new Exception("Taxa cód:" . $codTaxaAnt . " não foi duplicada.");
        }
    }

    
    public function ImportarTaxas($debug = false) {
        if ($this->objDAO->importarTaxas($this, $debug)) {
            throw new OKException("Taxas do projeto: ".$this->getCodEventoAnt()." importados para ". $this->getCodEvento() . " com sucesso.");
        } else {
            throw new Exception("Taxas do projeto: ".$this->getCodEventoAnt()." não foi importados.");
        }
    }    
}

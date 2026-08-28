<?php

class BoletoAvulso extends BoletoAvulsoVO {

    private $objDAO;

    function __construct() {
        
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new BoletoAvulsoDAO();
    }

    public function carregarInscrito(){
        $obj = new Inscrito();
        $obj->setCodInscrito($this->getCodInscrito());
        $obj->getInscritoById();
        $this->setObjInscrito($obj);
    }
    public function BoletoAvulsoExiste() {
        if (!$this->getCodBoleto()) {
            throw new Exception('Código do Boleto inválido ou não informado.');
        }
        if (!$this->objDAO->getByIdExiste($this)) {
            throw new Exception("Nenhuma Boleto foi encontrada para o código informado: " . $this->getCodBoleto() . ".");
            //throw new Exception("Id informado para deleção não existe");
        }
    }

    public function novo($debug = false) {
        
    }

    public function gerarBoleto($debug = false){

        /**gerando nosso número */
        $nossoNumero = $objSisprime->gerarNumeroDocumento($fws_, $debug);
        $objBoleto->setNossoNumero($nossoNumero);

        /** pode ser $cod_boleto gerado + digito verificador*/
        //$objBoleto->setNumeroDocumento();
        
        $this->validatorObj();
        $this->setValorDocumento($this->getValorBoleto());
//        $this->setValorBoleto($this->getValorBoleto());
        $this->setDataVencimento(formatDateView($this->getDataVencimento()));
        $this->setDataDesconto(formatDateView($this->getDataDesconto()));
        $this->setValorDesconto($this->getValorDesconto());
        $this->setDataEmissao(date('d/m/Y'));
        $this->setDataProcessamento(date('d/m/Y'));
        $this->setDataDocumento(date('d/m/Y'));
        $this->setTipoDocumento(2);
        $this->setUf($this->getEstado());
        if(strlen($this->getCpfCnpj())<14){
            $this->setTipoDocumento(1);
        }
        $this->setExtra(1);
        
        $this->setEndereco1($this->getEndereco() . ", ". $this->getNumero(). ", ". $this->getComplemento()." ". $this->getBairro()); //"Av. Madre Leonia Milito 2000";
        $this->setEndereco2($this->getCidade(). "-". $this->getEstado(). " CEP: ". $this->getCep()); //"Cidade - Estado -  CEP: 00000-000";
    
        $this->setDataVencimento(formatDateView($this->getDataVencimento()));
        if (is_null($this->getCodBoleto()) or $this->getCodBoleto() == "") {
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Boleto salvo com sucesso.");
            } else {
                throw new Exception("Novo Boleto não foi salvo.");
            }
        } else {
            $this->BoletoAvulsoExiste($debug);

            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Boleto foi atualizado com sucesso.");
            } else {
                throw new Exception("Boleto atual não foi atualizado.");
            }
        }        
    }

    public function salvar($debug = false) {
        
        $this->validatorObj();
        $this->setValorDocumento($this->getValorBoleto());
//        $this->setValorBoleto($this->getValorBoleto());
        $this->setDataVencimento(formatDateView($this->getDataVencimento()));
        $this->setDataDesconto(formatDateView($this->getDataDesconto()));
        $this->setValorDesconto($this->getValorDesconto());
        $this->setDataEmissao(date('d/m/Y'));
        $this->setDataProcessamento(date('d/m/Y'));
        $this->setDataDocumento(date('d/m/Y'));
        $this->setTipoDocumento(2);
        $this->setUf($this->getEstado());
        if(strlen($this->getCpfCnpj())<14){
            $this->setTipoDocumento(1);
        }
        $this->setExtra(1);
        
        $this->setEndereco1($this->getEndereco() . ", ". $this->getNumero(). ", ". $this->getComplemento()." ". $this->getBairro()); //"Av. Madre Leonia Milito 2000";
        $this->setEndereco2($this->getCidade(). "-". $this->getEstado(). " CEP: ". $this->getCep()); //"Cidade - Estado -  CEP: 00000-000";
    
        $this->setDataVencimento(formatDateView($this->getDataVencimento()));
        if (is_null($this->getCodBoleto()) or $this->getCodBoleto() == "") {
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Boleto salvo com sucesso.");
            } else {
                throw new Exception("Novo Boleto não foi salvo.");
            }
        } else {
            $this->BoletoAvulsoExiste($debug);

            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Boleto foi atualizado com sucesso.");
            } else {
                throw new Exception("Boleto atual não foi atualizado.");
            }
        }
    }

    public function excluir($debug = false) {
        $this->validatorEcluir();
        if ($this->objDAO->delete($this, $debug)) {
            throw new OKException("Boleto  cód:" . $this->getCodBoleto() . " foi excluído com sucesso.");
        } else {
            throw new Exception("Boleto cód:" . $this->getCodBoleto() . " não foi excluído.");
        }
    }
    
    public function desativar($debug = false) {
        $this->validatorDesativar();
        if ($this->objDAO->desativar($this, $debug)) {
            throw new OKException("Boleto  cód:" . $this->getCodBoleto() . " foi desativado com sucesso.");
        } else {
            throw new Exception("Boleto cód:" . $this->getCodBoleto() . " não foi desativado.");
        }
    }

    public function getBoletoAvulsoByIdPedido($debug = false) {
        $this->objDAO->getByIdPedido($this, $debug);
    }

    public function getBoletoAvulsoById($debug = false) {
        $this->objDAO->getById($this, $debug);
    }

    public function getAll($debug = false) {
        $this->objDAO->getAll($this, $debug);
    }

    public function getListaBoletosAvulsosByIdInscrito($condicao=false,$debug=false) {
        if (!$this->getCodInscrito()) {
            throw new Exception("Id do inscrito não informado.");
        }
        $this->objDAO->carregarListaBoletosAvulsosByIdInscrito($this, $condicao, $debug);
        if ($this->getListaBoletosAvulsos()) {
            return $this->getListaBoletosAvulsos();
        } else {
            throw new Exception("Inscrito não possui Boletos cadastrado.");
        }
    }

}

?>
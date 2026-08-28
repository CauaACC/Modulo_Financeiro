<?php

class Boleto extends BoletoVO {

    private $objDAO;

    function __construct() {
        if($this->codEvento!='4'){
            $this->codEvento = COD_EVENTO;
        }
        $this->objDAO = new BoletoDAO();
    }
    
    public function BoletoExiste() {
//        if (!$this->getCodBoleto()) {
//            throw new Exception('Código da Boleto inválido ou não informado.');
//        }
        if (!$this->objDAO->getByIdExiste($this)) {
            //throw new Exception("Nenhuma Boleto foi encontrada para o código informado: " . $this->getCodBoleto() . ".");
            //throw new Exception("Id informado para deleção não existe");
        }
    }

    public function novo($debug = false) {
        
    }

    public function salvar($debug = false) {
        
        $this->validatorObj();
        if (is_null($this->getCodBoleto()) or $this->getCodBoleto() == "") {
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Boleto salvo com sucesso.");
            } else {
                throw new Exception("Novo Boleto não foi salvo.");
            }
        } else {
            //$this->BoletoExiste($debug);
            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Boleto foi atualizado com sucesso.");
            } else {
                throw new Exception("Boleto atual não foi atualizado.");
            }
        }
    }

    public function salvarAssociacao($debug = false) {
        
        $this->validatorObj();
        if (is_null($this->getCodBoleto()) or $this->getCodBoleto() == "") {
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Boleto salvo com sucesso.");
            } else {
                throw new Exception("Novo Boleto não foi salvo.");
            }
        } else {
            //$this->BoletoExiste($debug);
            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Boleto foi atualizado com sucesso.");
            } else {
                throw new Exception("Boleto atual não foi atualizado.");
            }
        }
    }
    public function salvarRemessa($debug = false) {
        
//        $this->validatorObj();
//        if (is_null($this->getCodBoleto()) or $this->getCodBoleto() == "") {
//            if ($this->objDAO->insert($this, $debug)) {
//                throw new OKException("Boleto salvo com sucesso.");
//            } else {
//                throw new Exception("Novo Boleto não foi salvo.");
//            }
//        } else {
            $this->BoletoExiste($debug);
            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Boleto foi atualizado com sucesso.");
            } else {
                throw new Exception("Boleto atual não foi atualizado.");
            }
//        }
            return false;
    }
    public function salvarCargaBoleto($debug = false) {
        
        $this->validatorObj();
        if (is_null($this->getCodBoleto()) or $this->getCodBoleto() == "") {
             throw new Exception("Código do boleto não informado.");
        } else {
            $this->BoletoExiste($debug);
            if ($this->objDAO->update($this, $debug)) {
                if($this->getIdOcorrencia()!='02'){
                    try {
                        $objPagamento = new Pagamento();
                        $objPagamento->setCodEvento($this->getCodEvento());
                        $objPagamento->setPedidoId($this->getPedidoId());
                        $objPagamento->getPagamentoByPedidoId($debug);
                        
                        $objPagamento->setCodBoleto($this->getCodBoleto());
                        $objPagamento->setCodInscrito($this->getCodInscrito());
                        $objPagamento->setFormaPagamento('BOLETO BANCÁRIO');
                        $objPagamento->setValor($this->getValorPago());
                        $objPagamento->setDataPagamento(formatDateInsert($this->getDataPagamento()));
                        $objPagamento->setStatus('ATIVADA');
                        $objPagamento->salvarCargaPgSisprime($debug);
                    } catch (OKException $exc) {
                        throw new OKException("Boleto e Pagamento atualizado com sucesso.");
                    }
                }else{
                    throw new OKException("Boleto registrado com sucesso.");
                }
            } else {
                throw new Exception("Boleto atual não foi atualizado.");
            }
        }
    }

    public function excluir($debug = false) {
        $this->validatorEcluir();
        if ($this->objDAO->delete($this, $debug)) {
            throw new OKException("Boleto cód:" . $this->getCodBoleto() . " foi excluída com sucesso.");
        } else {
            throw new Exception("Boleto cód:" . $this->getCodBoleto() . " não foi excluída.");
        }
    }

    public function getBoletoById($debug = false) {
        if($this->getCodEvento()==''){
            throw new Exception("codEvento não encontrado.");
        }
        if($this->getCodBoleto()==''){
            throw new Exception("codBoleto não encontrado.");
        }    
        return $this->objDAO->getById($this, $debug);
    }

    public function getBoletoByNumeroDocumento($debug = false) {
        return $this->objDAO->getBoletoByNumeroDocumento($this, $debug);
    }

    public function getBoletoByPedidoId($debug = false) {
        return $this->objDAO->getBoletoByPedido($this, $debug);
    }

    public function getNextSequenciaBoleto($debug = false) {
        return $this->objDAO->getNextSequenciaBoleto($this, $debug);
    }

    public function carregarUltimoBoletoByIdInscrito($debug = false) {
        return $this->objDAO->getUltimoBoletoByIdInscrito($this, $debug);
    }

    public function carregarUltimoBoletoByIdInscritoValor($debug = false) {
        return $this->objDAO->getUltimoBoletoByIdInscritoValor($this, $debug);
    }

    public function carregarListaBoletos($debug = false) {
        $this->objDAO->carregarListaBoletosByIdEvento($this, $debug);
        if ($this->getListaBoletos()) {
        } else {
            throw new Exception("Evento não possui Boleto cadastrada.");
        }
    }
    public function carregarListaBoletosByCodInscrito($debug = false) {
        return $this->objDAO->carregarListaBoletosByCodInscrito($this, $debug);
    }

    public function getEvento() {
        $obj = new InscEvento();
        $obj->setCodEvento($this->getCodEvento());
        $obj->getEventoById();
        return $obj;
    }

}

?>
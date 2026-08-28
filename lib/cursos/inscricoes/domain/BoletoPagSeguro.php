<?php

class BoletoPagSeguro extends BoletoPagSeguroVO {

    private $objDAO;

    function __construct() {
        if($this->codEvento!='4'){
            $this->codEvento = COD_EVENTO;
        }
        $this->objDAO = new BoletoPagSeguroDAO();
    }
    
    public function BoletoPagSeguroExiste() {
//        if (!$this->getTransacaoId()) {
//            throw new Exception('Código da BoletoPagSeguro inválido ou não informado.');
//        }
        if (!$this->objDAO->getByIdExiste($this)) {
            //throw new Exception("Nenhuma BoletoPagSeguro foi encontrada para o código informado: " . $this->getTransacaoId() . ".");
            //throw new Exception("Id informado para deleção não existe");
        }
    }

    public function novo($debug = false) {
        
    }

    public function salvar($debug = false) {
        $this->validatorObj();
//        if (is_null($this->getTransacaoId()) or $this->getTransacaoId() == "") {
        if (!$this->objDAO->getByIdExiste($this)){
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("BoletoPagSeguro salvo com sucesso.");
            } else {
                throw new Exception("Novo BoletoPagSeguro não foi salvo.");
            }
        } else {
            //$this->BoletoPagSeguroExiste($debug);
            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("BoletoPagSeguro foi atualizado com sucesso.");
            } else {
                throw new Exception("BoletoPagSeguro atual não foi atualizado.");
            }
        }
    }

    public function salvarAssociacao($debug = false) {
        
        $this->validatorObj();
        if (is_null($this->getTransacaoId()) or $this->getTransacaoId() == "") {
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("BoletoPagSeguro salvo com sucesso.");
            } else {
                throw new Exception("Novo BoletoPagSeguro não foi salvo.");
            }
        } else {
            //$this->BoletoPagSeguroExiste($debug);
            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("BoletoPagSeguro foi atualizado com sucesso.");
            } else {
                throw new Exception("BoletoPagSeguro atual não foi atualizado.");
            }
        }
    }
    public function salvarRemessa($debug = false) {
        
//        $this->validatorObj();
//        if (is_null($this->getTransacaoId()) or $this->getTransacaoId() == "") {
//            if ($this->objDAO->insert($this, $debug)) {
//                throw new OKException("BoletoPagSeguro salvo com sucesso.");
//            } else {
//                throw new Exception("Novo BoletoPagSeguro não foi salvo.");
//            }
//        } else {
            $this->BoletoPagSeguroExiste($debug);
            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("BoletoPagSeguro foi atualizado com sucesso.");
            } else {
                throw new Exception("BoletoPagSeguro atual não foi atualizado.");
            }
//        }
            return false;
    }
    public function salvarCargaBoletoPagSeguro($debug = false) {
        
        $this->validatorObj();
        if (is_null($this->getTransacaoId()) or $this->getTransacaoId() == "") {
             throw new Exception("Código do boleto não informado.");
        } else {
            $this->BoletoPagSeguroExiste($debug);
            if ($this->objDAO->update($this, $debug)) {
                if($this->getIdOcorrencia()!='02'){
                    try {
                        $objPagamento = new Pagamento();
                        $objPagamento->setCodEvento($this->getCodEvento());
                        $objPagamento->setTransacaoId($this->getTransacaoId());
                        $objPagamento->setCodInscrito($this->getCodInscrito());
                        $objPagamento->setFormaPagamento('BOLETO BANCÁRIO');
                        $objPagamento->setValor(str_replace(".", "", $this->getValorPago()));
                        $objPagamento->setDataPagamento(formatDateInsert($this->getDataPagamento()));
                        $objPagamento->setStatus('ATIVADA');
                        $objPagamento->salvarCargaPagamento($debug);
                    } catch (OKException $exc) {
                        throw new OKException("BoletoPagSeguro e Pagamento atualizado com sucesso.");
                    }
                }else{
                    throw new OKException("BoletoPagSeguro registrado com sucesso.");
                }
            } else {
                throw new Exception("BoletoPagSeguro atual não foi atualizado.");
            }
        }
    }

    public function excluir($debug = false) {
        $this->validatorEcluir();
        if ($this->objDAO->delete($this, $debug)) {
            throw new OKException("BoletoPagSeguro cód:" . $this->getTransacaoId() . " foi excluída com sucesso.");
        } else {
            throw new Exception("BoletoPagSeguro cód:" . $this->getTransacaoId() . " não foi excluída.");
        }
    }
    
    public function excluirByCodArquivo($debug = false) {
        $this->validatorEcluir();
        if ($this->objDAO->deleteByCodArquivo($this, $debug)) {
            throw new OKException("BoletoPagSeguro cód:" . $this->getTransacaoId() . " foi excluída com sucesso.");
        } else {
            throw new Exception("BoletoPagSeguro cód:" . $this->getTransacaoId() . " não foi excluída.");
        }
    }

    public function getBoletoPagSeguroById($debug = false) {
        if($this->getCodEvento()==''){
            throw new Exception("codEvento não encontrado.");
        }
        if($this->getTransacaoId()==''){
            throw new Exception("codBoletoPagSeguro não encontrado.");
        }    
        return $this->objDAO->getById($this, $debug);
    }

    public function getBoletoPagSeguroByTransacaoId($debug = false) {
        return $this->objDAO->getBoletoPagSeguroByTransacaoId($this, $debug);
    }

    public function carregarListaBoletoPagSeguros($debug = false) {
        $this->objDAO->carregarListaBoletoPagSegurosByIdEvento($this, $debug);
        if ($this->getListaBoletoPagSeguros()) {
        } else {
            throw new Exception("Evento não possui BoletoPagSeguro cadastrada.");
        }
    }

    public function getEvento() {
        $obj = new InscEvento();
        $obj->setCodEvento($this->getCodEvento());
        $obj->getEventoById();
        return $obj;
    }

}

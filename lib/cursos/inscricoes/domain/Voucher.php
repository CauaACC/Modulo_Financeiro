<?php

class Voucher extends VoucherVO {

    private $objDAO;

    function __construct() {
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new VoucherDAO();
    }

    public function getLastSequenciaBySigla($debug=false) {
        return $this->objDAO->getLastSequenciaBySigla($this, $debug);
    }
    public function VoucherExiste() {
        if (!$this->getCodVoucher()) {
            throw new Exception('Código do Voucher inválido ou não informado.');
        }
        if (!$this->objDAO->getByIdExiste($this)) {
            throw new Exception("Nenhuma Voucher foi encontrada para o código informado: " . $this->getCodVoucher() . ".");
            //throw new Exception("Id informado para deleção não existe");
        }
    }

    public function novo($debug = false) {
        
    }

    public function salvar($debug = false) {
        
        $this->validatorObj();
        if (is_null($this->getId()) or $this->getId() == "") {
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Voucher salvo com sucesso.");
            } else {
                throw new Exception("Novo Voucher não foi salva.");
            }
        } else {
            $this->VoucherExiste($debug);

            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Voucher foi atualizado com sucesso.");
            } else {
                throw new Exception("Voucher atual não foi atualizada.");
            }
        }
    }

    public function excluir($debug = false) {
        $this->validatorEcluir();
        if ($this->objDAO->delete($this, $debug)) {
            throw new OKException("Voucher cód:" . $this->getCodVoucher() . " foi excluída com sucesso.");
        } else {
            throw new Exception("Voucher cód:" . $this->getCodVoucher() . " não foi excluída.");
        }
    }

    public function getVoucherById($debug = false) {
        return $this->objDAO->getById($this, $debug);
    }

    public function testeQtdMaximaBySiglaCategoria($debug = false) {
        return $this->objDAO->testeQtdMaximaBySiglaCategoria($this, $debug);
    }

    public function carregarListaVouchers($debug = 1,$limit=false) {

        if ($this->objDAO->getAll($this, $debug,$limit)) {
            return true;
        } else {
            if($limit==false){
                throw new Exception("Limite de emissão não foi definido.");
            }
            throw new Exception("Evento não possui Voucher cadastrado.".__LINE__);
        }
    }
    public function carregarListaVouchersByCodEvento($debug = false,$limit=false) {
        if ($this->objDAO->getListaVouchersByCodEvento($this, $debug,$limit)) {
            return true;
        } else {
            throw new Exception("Evento não possui Voucher cadastrado.".__LINE__);
        }
    }
    public function carregarListaVouchersByCodVoucherEvento($debug = false,$limit=false) {
        if ($this->objDAO->getListaVouchersByCodVoucherEvento($this, $debug,$limit)) {
            return true;
        } else {
            throw new Exception("Evento não possui Voucher cadastrado.".__LINE__);
        }
    }
    public function carregarListaPatrocinadoresVoucherByIdEvento($debug = false,$limit=false) {
        if ($this->objDAO->getListaPatrocinadoresVoucherByIdEvento($this, $debug)) {
            return true;
        } else {
            throw new Exception("Evento não possui Voucher cadastrado.".__LINE__);
        }
    }

    public function getEvento() {
        $obj = new Evento();
        $obj->setCodEvento($this->getCodEvento());
        $obj->getEventoById();
        return $obj;
    }

}


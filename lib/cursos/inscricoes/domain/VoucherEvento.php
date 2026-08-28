<?php

class VoucherEvento extends VoucherEventoVO {

    private $objDAO;

    function __construct() {
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new VoucherEventoDAO();
    }
    
    public function criarListaVouchers($debug = false) {
        
        for ($i = 1; $i <= $this->getQuantidade(); $i++) {
            $objVoucher = new Voucher();
            $objVoucher->setCodVoucherEvento($this->getCodVoucherEvento());
            $objVoucher->setNome($this->getNome());
            $objVoucher->setTipoVoucher($this->getTipoVoucher());
            $objVoucher->setValor($this->getValor());
            $objVoucher->setValorInscricao($this->getValorInscricao());
            $objVoucher->setPorcentagem($this->getPorcentagem());
            $objVoucher->setMaxUtilizacao($this->getMaxUtilizacao());
            $objVoucher->setOfertaId($this->getOfertaId());
            $objVoucher->setPatrocinador($this->getPatrocinador());
            $objVoucher->setSigla($this->getSigla());
            $objVoucher->setDescricao($this->getDescricao());
            $objVoucher->setCategoria($this->getCategoria());
            $objVoucher->setDataVencimento($this->getDataValidade());
//            $objVoucher->setCodVoucher(md5(rand(0000000,9999999)));
            $objVoucher->setCodVoucher(strtoupper(substr(md5(rand(0000000,9999999)), 1, 7)));
//            $objVoucher->setCodVoucher(strtoupper(substr(md5(date("YmdHis")), 1, 7)));
//            $objVoucher->setSequenciaInterna($i);
            $objVoucher->setSequenciaInterna($objVoucher->getLastSequenciaBySigla());
            try {
                $objVoucher->salvar($debug);
            } catch (OKException $exc) { }
        } 
    }
    
    public function carregarListaVouchersByCodVoucherEvento($debug = false) {
        try {
            $objVoucher = new Voucher();
            $objVoucher->setCodVoucherEvento($this->getCodVoucherEvento());
            $objVoucher->carregarListaVouchersByCodVoucherEvento($debug);
            $this->setListaVouchers($objVoucher->getListaVouchers());
            return true;
        } catch (Exception $exc) {
            return false;
        }

    }

    public function VoucherEventoExiste() {
        if (!$this->getCodVoucherEvento()) {
            throw new Exception('Código do grupo de vouchers inválido ou não informado.');
        }
        if (!$this->objDAO->getByIdExiste($this)) {
            throw new Exception("Nenhuma grupo de vouchers foi encontrada para o código informado: " . $this->getCodVoucherEvento() . ".");
            //throw new Exception("Id informado para deleção não existe");
        }
    }

    public function novo($debug = false) {
        
    }

    public function salvar($debug = false) {
        
        $this->validatorObj();
        if (is_null($this->getCodVoucherEvento()) or $this->getCodVoucherEvento() == "") {
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Grupo de vouchers salvo com sucesso.");
            } else {
                throw new Exception("Novo grupo de vouchers não foi salva.");
            }
        } else {
            $this->VoucherEventoExiste($debug);

            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Grupo de vouchers foi atualizado com sucesso.");
            } else {
                throw new Exception("Grupo de vouchers atual não foi atualizada.");
            }
        }
    }

    public function excluir($debug = false) {
        $this->validatorEcluir();
        if ($this->objDAO->delete($this, $debug)) {
            throw new OKException("Grupo de vouchers cód:" . $this->getCodVoucherEvento() . " foi excluída com sucesso.");
        } else {
            throw new Exception("Grupo de vouchers cód:" . $this->getCodVoucherEvento() . " não foi excluída.");
        }
    }
    public function desativar($debug = false) {
        $this->validatorEcluir();
        if ($this->objDAO->desativar($this, $debug)) {
            throw new OKException("Grupo de vouchers cód:" . $this->getCodVoucherEvento() . " foi excluída com sucesso.");
        } else {
            throw new Exception("Grupo de vouchers cód:" . $this->getCodVoucherEvento() . " não foi excluída.");
        }
    }

    public function getVoucherEventoById($debug = false) {
        return $this->objDAO->getById($this, $debug);
    }

    public function getVoucherById($debug = false) {
        return $this->objDAO->getById($this, $debug);
    }

    public function carregarListaVoucherEventos($debug = false,$limit=false) {
        if ($this->objDAO->getAll($this, $debug,$limit)) {
            return true;
        } else {
            throw new Exception("Evento não possui grupo de vouchers cadastrado.");
        }
    }

    public function getEvento() {
        $obj = new InscEvento();
        $obj->setCodEvento($this->getCodEvento());
        $obj->getEventoById();
        return $obj;
    }

}


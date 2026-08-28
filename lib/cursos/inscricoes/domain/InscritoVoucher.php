<?php

class InscritoVoucher extends InscritoVoucherVO {

    private $objDAO;

    function __construct() {
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new InscritoVoucherDAO();
    }

    /**
     * @return type true para utilizado e false para não utilizado
     */
    public function inscritoPossuiVoucher($debug){
        $this->objDAO->inscritoPossuiVoucher($this,$debug);
        if($this->getVersao()==''){
            return false;
        }else{
            return true;
        }
    }

    /**
     * @return type true para utilizado e false para não utilizado
     */
    public function voucherUtilizado($debug){
        $this->objDAO->voucherUtilizado($this,$debug);
        if($this->getVersao()==''){
            return false;
        }else{
            return true;
        }
    }
    
    /**
     * @return type true para utilizado e false para não utilizado
     */
    public function voucherExiste($debug = false){
        $obj = new Voucher();
        $obj->setCodVoucher($this->getCodVoucher());
        $obj->getVoucherById($debug);
        if($obj->getStatus()=='DESATIVADA'){
            return false;
        }
        if($obj->getId()!=''){
            return true;
        }else{
            return false;
        }
    }
    /**
     * @return type true se pode ser utilizado e false se não pode ser utilizado
     * @todo Esse método precisa de revisão para ser usado apenas quando for necessário
     * teste de quantidade de utilização por Sigla ou Categoria
     */
    public function testeQtdMaximaBySiglaCategoria($debug = false){
        return true;
        $obj = new Voucher();
        $obj->setCodVoucher($this->getCodVoucher());
        $obj->testeQtdMaximaBySiglaCategoria($debug);
        if(is_null($obj->getMaxUtilizacao()) or $obj->getMaxUtilizacao()==""){
            return true;
        }
        if($obj->getTotalUtilizado() < $obj->getMaxUtilizacao()){
            return true;
        }else{
            return false;
        }
    }
    
    public function carregarObjVoucher($debug = false){
        $obj = new Voucher();
        $obj->getVoucherById($debug);
        $this->setObjVoucher($obj);
    }

    public function inscritoVoucherExiste($debug = false) {
        if (!$this->getCodInscrito()) {
            throw new Exception('Código do Inscrito inválido ou não informado.');
        }
        if (!$this->getCodVoucher()) {
            throw new Exception('Código do Voucher inválido ou não informado.');
        }
        return $this->objDAO->getByIdExiste($this, $debug);
    }
    public function carregarListaVoucherByIdInscrito($debug = false) {
        if (!$this->getCodInscrito()) {
            throw new Exception('Código do Inscrito inválido ou não informado.');
        }
        $this->objDAO->getListaVoucherByIdInscrito($this, $debug);
        if ($this->getListaVoucher()) {
            return true;
        } else {
            throw new Exception("Inscrito não possui Voucher cadastrado.");
        }
    }
    public function salvar($debug = false) {

        $this->validatorObj();
        if(!$this->InscritoVoucherExiste($debug)){
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Voucher associado ao inscrito sucesso.");
            } else {
                throw new Exception("Voucher não foi associado ao inscrito.");
            }
        } else {
            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Voucher associado ao inscrito atualizado com sucesso.");
            } else {
                throw new Exception("Voucher associado ao inscrito não foi atualizado.");
            }
        }
    }
    public function adicionarVoucher($debug = false) {

        $this->validatorObj();
        if(!$this->InscritoVoucherExiste($debug)){
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Voucher associado ao inscrito sucesso.");
            } else {
                throw new Exception("Voucher não foi associado ao inscrito.");
            }
        } else {
            throw new OKException("Voucher associado ao inscrito atualizado com sucesso.");
        }
    }

    public function excluir($debug = false) {
        $this->validatorEcluir();
        if ($this->objDAO->delete($this, $debug)) {
            throw new OKException("Voucher cód:" . $this->getCodVoucher() . " foi excluído com sucesso.");
        } else {
            throw new Exception("Voucher cód:" . $this->getCodVoucher() . " não foi excluído.");
        }
    }

    public function getInscritoVoucherById($debug = false) {
        $this->objDAO->getById($this, $debug);
    }

    public function getEvento() {
        $obj = new InscEvento();
        $obj->setCodEvento($this->getCodEvento());
        $obj->getEventoById();
        return $obj;
    }

}

?>
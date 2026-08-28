<?php

class CertificadoEvento extends CertificadoEventoVO {

    private $objDAO;

    function __construct() {
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new CertificadoEventoDAO();
    }
    
    public function criarListaCertificados($debug = false) {
        
        for ($i = 1; $i <= $this->getQuantidade(); $i++) {
            $objCertificado = new CertificadoEvento();
            $objCertificado->setCodCertificadoEvento($this->getCodCertificadoEvento());
            $objCertificado->setNome($this->getNome());
            $objCertificado->setTipoCertificado($this->getTipoCertificado());
            $objCertificado->setValor($this->getValor());
            $objCertificado->setPorcentagem($this->getPorcentagem());
            $objCertificado->setMaxUtilizacao($this->getMaxUtilizacao());
            $objCertificado->setOfertaId($this->getOfertaId());
            $objCertificado->setPatrocinador($this->getPatrocinador());
            $objCertificado->setSigla($this->getSigla());
            $objCertificado->setDescricao($this->getDescricao());
            $objCertificado->setCategoria($this->getCategoria());
            $objCertificado->setDataVencimento($this->getDataValidade());
//            $objCertificado->setCodCertificado(md5(rand(0000000,9999999)));
            $objCertificado->setCodCertificado(strtoupper(substr(md5(rand(0000000,9999999)), 1, 7)));
//            $objCertificado->setCodCertificado(strtoupper(substr(md5(date("YmdHis")), 1, 7)));
//            $objCertificado->setSequenciaInterna($i);
            $objCertificado->setSequenciaInterna($objCertificado->getLastSequenciaBySigla());
            try {
                $objCertificado->salvar($debug);
            } catch (OKException $exc) { }
        } 
    }
    
    public function carregarListaCertificadosByCodCertificadoEvento($debug = false) {
        try {
            $objCertificado = new Certificado();
            $objCertificado->setCodCertificadoEvento($this->getCodCertificadoEvento());
            $objCertificado->carregarListaCertificadosByCodCertificadoEvento($debug);
            $this->setListaCertificados($objCertificado->getListaCertificados());
            return true;
        } catch (Exception $exc) {
            return false;
        }

    }

    public function CertificadoEventoExiste() {
        if (!$this->getCodCertificadoEvento()) {
            throw new Exception('Código do grupo de certificados inválido ou não informado.');
        }
        if (!$this->objDAO->getByIdExiste($this)) {
            throw new Exception("Nenhuma grupo de certificados foi encontrada para o código informado: " . $this->getCodCertificadoEvento() . ".");
            //throw new Exception("Id informado para deleção não existe");
        }
    }

    public function novo($debug = false) {
        
    }

    public function salvar($debug = false) {
        
        $this->validatorObj();
        if (is_null($this->getCodCertificadoEvento()) or $this->getCodCertificadoEvento() == "") {
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Grupo de certificados salvo com sucesso.");
            } else {
                throw new Exception("Novo grupo de certificados não foi salva.");
            }
        } else {
            $this->CertificadoEventoExiste($debug);

            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Grupo de certificados foi atualizado com sucesso.");
            } else {
                throw new Exception("Grupo de certificados atual não foi atualizada.");
            }
        }
    }

    public function excluir($debug = false) {
        $this->validatorEcluir();
        if ($this->objDAO->delete($this, $debug)) {
            throw new OKException("Grupo de certificados cód:" . $this->getCodCertificadoEvento() . " foi excluída com sucesso.");
        } else {
            throw new Exception("Grupo de certificados cód:" . $this->getCodCertificadoEvento() . " não foi excluída.");
        }
    }
    public function desativar($debug = false) {
        $this->validatorEcluir();
        if ($this->objDAO->desativar($this, $debug)) {
            throw new OKException("Grupo de certificados cód:" . $this->getCodCertificadoEvento() . " foi excluída com sucesso.");
        } else {
            throw new Exception("Grupo de certificados cód:" . $this->getCodCertificadoEvento() . " não foi excluída.");
        }
    }

    public function getCertificadoEventoById($debug = false) {
        return $this->objDAO->getById($this, $debug);
    }

    public function carregarListaCertificadoEventos($debug = false,$limit=false) {
        if ($this->objDAO->getAll($this, $debug,$limit)) {
            return true;
        } else {
            throw new Exception("Evento não possui grupo de certificados cadastrado.");
        }
    }

    public function getEvento() {
        $obj = new InscEvento();
        $obj->setCodEvento($this->getCodEvento());
        $obj->getEventoById();
        return $obj;
    }

}


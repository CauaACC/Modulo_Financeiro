<?php

class Ingresso extends IngressoVO
{

    private $objDAO;

    function __construct()
    {
        $this->eventoId = COD_EVENTO;
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new IngressoDAO();
    }

    public function IngressoExiste($debug = false)
    {
        if (!$this->getId()) {
            throw new Exception('Código do Ingresso inválido ou não informado: ' . $this->getId());
        }
        return $this->objDAO->getByIdExiste($this, $debug);
    }

    public function carregarListaIngressosByInscritoId($debug = false)
    {
        $this->objDAO->carregarListaIngressosByInscritoId($this, $debug);
        if ($this->getListaIngressos()) {
            return true;
        } else {
            throw new Exception("Inscrito não possui ingressos associados.");
        }
    }

    public function carregarListaIngressosByPedidoId($debug = false)
    {
        $this->objDAO->carregarListaIngressosByPedidoId($this, $debug);
        if ($this->getListaIngressos()) {
            return true;
        } else {
            throw new Exception("Inscrito não possui ingressos associados.");
        }
    }

    public function salvar($debug = false)
    {

        $this->validatorObj();
        if (is_null($this->getId()) or $this->getId() == "") {
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Ingresso gravado com sucesso.");
            } else {
                throw new Exception("Ingresso não foi gravado com sucesso.");
            }
        } else {
            $this->IngressoExiste($debug);

            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Ingresso atualizado com sucesso.");
            } else {
                throw new Exception("Ingresso não foi atualizado.");
            }
        }
    }

    public function excluir($debug = false)
    {
        $this->validatorExcluir();
        $this->IngressoExiste($debug);
        if ($this->objDAO->delete($this, $debug)) {
            throw new OKException("Ingresso cód. " . $this->getId() . " foi excluído com sucesso");
        } else {
            throw new Exception("Ingresso cód. " . $this->getId() . " NÃO foi excluído");
        }
    }

    public function getIngressoById($debug = false)
    {
        $this->objDAO->getById($this, $debug);
    }

    public function getEvento()
    {
        $obj = new InscEvento();
        $obj->setCodEvento($this->getEventoId());
        $obj->getEventoById();
        return $obj;
    }

    public function getAll($debug = false)
    {
        return $this->objDAO->getAll($this, $debug);
    }

    public function getInscritoByQrCode($debug = false) {
        return $this->objDAO->getByQrCode($this, $debug);
    }
}
<?php

class Interessado extends InteressadoVO
{

    private $objDAO;


    function __construct()
    {
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new InteressadoDAO();
    }

    public function InteressadosExiste($debug = false)
    {
        if (!$this->getCodInteressado()) {
            throw new Exception('Código do Aviso inválido ou não informado: ' . $this->getCodInteressado());
        }
        return $this->objDAO->getByIdExiste($this, $debug);
    }

    public function getTotalInteressadosForAreaInscrito($debug = false)
    {
        return $this->objDAO->carregarTotalInteressadosForAreaInscrito($this, $debug);
    }

    public function carregarListaInteressadosByIdEvento($debug = false)
    {
        $this->objDAO->carregarListaInteressadosByIdEvento($this, $debug);
        if ($this->getListaInteressados()) {
            return true;
        } else {
            throw new Exception("Evento não possui Avisos associados.");
        }
    }

    public function carregarListaInteressadosForAreaInscrito($debug = false)
    {
        $this->objDAO->carregarListaInteressadosForAreaInscrito($this, $debug);
        if ($this->getListaInteressados()) {
            return true;
        } else {
            throw new Exception("Nenhum aviso encontrado.");
        }
    }

    public function salvar($debug = false)
    {
        try {
            $this->validatorObj();

            if (is_null($this->getCodInteressado()) or $this->getCodInteressado() == "") {
                if ($this->objDAO->insert($this, $debug)) {
                    throw new OKException("Aviso gravado com sucesso.");
                } else {
                    throw new Exception("O aviso não pode ser gravado.");
                }
            } else {
                $this->InteressadosExiste($debug);

                if ($this->objDAO->update($this, $debug)) {
                    throw new OKException("Aviso atualizado com sucesso.");
                } else {
                    throw new Exception("Avsiso não foi atualizado.");
                }
            }
        } catch (Exception $e) {
            throw $e; // Lança a exceção novamente após tratamento
        }
    }

    public function insertLigacaoInscritoAviso($debug = false)
    {
        try {
            // $this->validatorObjInsert();
            $this->objDAO->insertLigacaoInscritoAviso($this, $debug);
        } catch (Exception $e) {
        }
    }

    public function excluir($debug = false)
    {
        $this->validatorExcluir();
        $this->InteressadosExiste($debug);
        if ($this->objDAO->delete($this, $debug)) {
            throw new OKException("Interessados cód. " . $this->getCodInteressado() . " foi excluído com sucesso");
        } else {
            throw new Exception("Interessados cód. " . $this->getCodInteressado() . " NÃO foi excluído");
        }
    }

    public function getInteressadoById($debug = false)
    {
        $this->objDAO->getById($this, $debug);
    }

    public function getEvento()
    {
        $obj = new Evento();
        $obj->setCodEvento($this->getCodEvento());
        $obj->getEventoById();
        return $obj;
    }

}
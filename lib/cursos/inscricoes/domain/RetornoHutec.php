<?php

class RetornoHutec extends RetornoHutecVO
{

    private $objDAO;

    function __construct()
    {
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new RetornoHutecDAO();
    }

    public function retornoHutecExiste($debug = false)
    {
        if (!$this->getCodRetornoHutec()) {
            throw new Exception('Código do retornohutec inválido ou não informado: ' . $this->getCodRetornoHutec());
        }
        return $this->objDAO->getByIdExiste($this, $debug);
    }
    public function retornoHutecExisteCarga($debug = false)
    {
        return $this->objDAO->getByIdExisteCarga($this, $debug);
    }

    public function carregarListaRetornoHutecByIdEvento($debug = false)
    {
        $this->objDAO->carregarListaRetornoHutecByIdEvento($this, $debug);
        if ($this->getListaRetornoHutec()) {
            return true;
        } else {
            throw new Exception("Evento não possui retornohutec associados.");
        }
    }

    public function salvar($debug = false)
    {
        try {
            $this->validatorObj();

            if (is_null($this->getCodRetornoHutec()) or $this->getCodRetornoHutec() == "") {
                if ($this->objDAO->insert($this, $debug)) {
                    throw new OKException("RetornoHutec gravado com sucesso.");
                } else {
                    throw new Exception("RetornoHutec não foi gravado com sucesso.");
                }
            } else {
                $this->retornoHutecExiste($debug);

                if ($this->objDAO->update($this, $debug)) {
                    throw new OKException("RetornoHutec atualizado com sucesso.");
                } else {
                    throw new Exception("RetornoHutec não foi atualizado.");
                }
            }
        } catch (Exception $e) {
            throw $e; // Lança a exceção novamente após tratamento
        }
    }
    public function salvarCarga($debug = false)
    {
        try {
            $this->validatorObj();

            if (is_null($this->getCodRetornoHutec()) or $this->getCodRetornoHutec() == "") {
                if ($this->objDAO->insert($this, $debug)) {
                    throw new OKException("RetornoHutec gravado com sucesso.");
                } else {
                    throw new Exception("RetornoHutec não foi gravado com sucesso.");
                }
            } else {
                $this->retornoHutecExiste($debug);

                if ($this->objDAO->update($this, $debug)) {
                    throw new OKException("RetornoHutec atualizado com sucesso.");
                } else {
                    throw new Exception("RetornoHutec não foi atualizado.");
                }
            }
        } catch (Exception $e) {
            throw $e; // Lança a exceção novamente após tratamento
        }
    }


    public function excluir($debug = false)
    {
        $this->validatorExcluir();
        $this->retornoHutecExiste($debug);
        if ($this->objDAO->delete($this, $debug)) {
            throw new OKException("RetornoHutec cód. " . $this->getCodRetornoHutec() . " foi excluído com sucesso");
        } else {
            throw new Exception("RetornoHutec cód. " . $this->getCodRetornoHutec() . " NÃO foi excluído");
        }
    }

    public function excluirCargaArquivo($debug = false)
    {
        $this->validatorExcluirCarga();
        $this->retornoHutecExisteCarga($debug);
        if ($this->objDAO->deleteCarga($this, $debug)) {
            throw new OKException("RetornoHutec cód. " . $this->getCodRetornoHutec() . " foi excluído com sucesso");
        } else {
            throw new Exception("RetornoHutec cód. " . $this->getCodRetornoHutec() . " NÃO foi excluído");
        }
    }

    public function getRetornoHutecById($debug = false)
    {
        $this->objDAO->getById($this, $debug);
    }

    public function getCriticaDivergentes($debug = false)
    {
        return $this->objDAO->getCriticaDivergentes($this, $debug);
    }

    public function getCriticaSoNoHutec($debug = false)
    {
        return $this->objDAO->getCriticaSoNoHutec($this, $debug);
    }

    public function getCriticaSoNoNosso($debug = false)
    {
        return $this->objDAO->getCriticaSoNoNosso($this, $debug);
    }

    public function getEvento()
    {
        $obj = new Evento();
        $obj->setCodEvento($this->getCodEvento());
        $obj->getEventoById();
        return $obj;
    }

}
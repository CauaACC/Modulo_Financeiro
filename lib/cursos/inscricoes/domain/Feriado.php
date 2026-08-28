<?php
class Feriado extends FeriadoVO
{
    private $objDAO;

    function __construct()
    {
        $this->evento_id = COD_EVENTO;
        $this->objDAO = new FeriadoDAO();
    }

    public function FeriadoExiste($debug = false)
    {
        if (!$this->getId()) {
            throw new Exception('Código inválido ou não informado: ' . $this->getId());
        }
        return $this->objDAO->getByIdExiste($this, $debug);
    }

    public function carregarListaFeriadoByIdEvento($debug = false)
    {
        $this->objDAO->carregarListaFeriadoByIdEvento($this, $debug);
        if ($this->{"getListaFeriados"}()) {
            return true;
        } else {
            throw new Exception("Evento não possui registros associados.");
        }
    }

    public function salvar($debug = false)
    {
        try {
            if (is_null($this->getId()) || $this->getId() == "") {
                $this->validatorInsert();
                if ($this->objDAO->insert($this, $debug)) {
                    throw new OKException("Feriado gravado com sucesso.");
                } else {
                    throw new Exception("Feriado não foi gravado com sucesso.");
                }
            } else {
                $this->validatorUpdate();
                $this->FeriadoExiste($debug);
                if ($this->objDAO->update($this, $debug)) {
                    throw new OKException("Feriado atualizado com sucesso.");
                } else {
                    throw new Exception("Feriado não foi atualizado.");
                }
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function excluir($debug = false)
    {
        $this->validatorExcluir();
        $this->FeriadoExiste($debug);
        if ($this->objDAO->delete($this, $debug)) {
            throw new OKException("Feriado cód. " . $this->getId() . " foi excluído com sucesso");
        } else {
            throw new Exception("Feriado cód. " . $this->getId() . " NÃO foi excluído");
        }
    }

    public function getFeriadoById($debug = false)
    {
        $this->objDAO->getById($this, $debug);
    }
}
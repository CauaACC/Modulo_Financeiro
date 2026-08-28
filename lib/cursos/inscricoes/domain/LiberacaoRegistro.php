<?php

class LiberacaoRegistro extends LiberacaoRegistroVO
{

    private $objDAO;

    function __construct()
    {
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new LiberacaoRegistroDAO();
    }

    public function liberacaoExiste($debug = false)
    {
        if (!$this->getCodLiberacao()) {
            throw new Exception('Código da Liberação inválido ou não informado: ' . $this->getCodLiberacao());
        }
        return $this->objDAO->getByIdExiste($this, $debug);
    }

    public function getLiberacaoById($debug = false)
    {
        $this->objDAO->getById($this, $debug);
    }

    public function carregarListaByEvento($debug = false)
    {
        $this->objDAO->listarByEvento($this, $debug);
        if ($this->getListaLiberacoes()) {
            return true;
        } else {
            throw new Exception("Nenhuma liberação encontrada.");
        }
    }

    public function buscarInscritos($debug = false)
    {
        return $this->objDAO->buscarInscritos($this, $debug);
    }

    public function buscarLiberacaoAtiva($debug = false)
    {
        return $this->objDAO->buscarLiberacaoAtiva($this, $debug);
    }

    public function salvar($debug = false)
    {
        try {
            $this->validatorObj();

            if (is_null($this->getCodLiberacao()) or $this->getCodLiberacao() == "") {
                if ($this->objDAO->insert($this, $debug)) {
                    throw new OKException("Liberação gravada com sucesso.");
                } else {
                    throw new Exception("A liberação não pode ser gravada.");
                }
            } else {
                $this->liberacaoExiste($debug);

                if ($this->objDAO->update($this, $debug)) {
                    throw new OKException("Liberação atualizada com sucesso.");
                } else {
                    throw new Exception("Liberação não foi atualizada.");
                }
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function excluir($debug = false)
    {
        $this->validatorExcluir();
        $this->liberacaoExiste($debug);
        if ($this->objDAO->delete($this, $debug)) {
            throw new OKException("Liberação cód. " . $this->getCodLiberacao() . " foi excluída com sucesso");
        } else {
            throw new Exception("Liberação cód. " . $this->getCodLiberacao() . " NÃO foi excluída");
        }
    }

}

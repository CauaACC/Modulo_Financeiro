<?php

class BloqueioAvaliacao extends BloqueioAvaliacaoVO
{

    private $objDAO;

    function __construct()
    {
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new BloqueioAvaliacaoDAO();
    }

    public function bloqueioExiste($debug = false)
    {
        if (!$this->getCodBloqueio()) {
            throw new Exception('Código do Bloqueio inválido ou não informado.');
        }
        return $this->objDAO->getByIdExiste($this, $debug);
    }

    public function getBloqueioById($debug = false)
    {
        $this->objDAO->getById($this, $debug);
    }

    public function carregarListaByEvento($debug = false)
    {
        return $this->objDAO->listarByEvento($this, $debug);
    }

    public function buscarPorMesAno($mes, $ano, $debug = false)
    {
        return $this->objDAO->buscarPorMesAno($this->getCodEvento(), $mes, $ano, $debug);
    }

    public static function isMesBloqueado($mes, $ano, $debug = false)
    {
        $obj = new self();
        $registro = $obj->buscarPorMesAno($mes, $ano, $debug);
        if ($registro && $registro['status'] === 'BLOQUEADO') {
            return true;
        }
        return false;
    }

    public function salvar($debug = false)
    {
        $this->validatorObj();

        if (is_null($this->getCodBloqueio()) or $this->getCodBloqueio() == "") {
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Bloqueio de avaliação salvo com sucesso.");
            } else {
                throw new Exception("O bloqueio de avaliação não pode ser salvo.");
            }
        } else {
            $this->bloqueioExiste($debug);

            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Bloqueio de avaliação atualizado com sucesso.");
            } else {
                throw new Exception("Bloqueio de avaliação não foi atualizado.");
            }
        }
    }

    public function excluir($debug = false)
    {
        $this->validatorExcluir();
        $this->bloqueioExiste($debug);
        if ($this->objDAO->delete($this, $debug)) {
            throw new OKException("Bloqueio cód. " . $this->getCodBloqueio() . " foi excluído com sucesso.");
        } else {
            throw new Exception("Bloqueio cód. " . $this->getCodBloqueio() . " NÃO foi excluído.");
        }
    }

    public function alternarStatus($mes, $ano, $novoStatus, $observacao = '', $debug = false)
    {
        $this->setMes($mes);
        $this->setAno($ano);
        $this->validatorObj();

        $registro = $this->buscarPorMesAno($mes, $ano, $debug);
        if ($registro) {
            $this->setCodBloqueio($registro['cod_bloqueio']);
            $this->getBloqueioById($debug);
            $this->setStatus($novoStatus);
            if ($observacao) {
                $this->setObservacao($observacao);
            }
            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Avaliação do mês " . $mes . "/" . $ano . " foi " . ($novoStatus === 'BLOQUEADO' ? 'bloqueada' : 'liberada') . " com sucesso.");
            } else {
                throw new Exception("Não foi possível alterar o status.");
            }
        } else {
            $this->setCodBloqueio(null);
            $this->setStatus($novoStatus);
            if ($observacao) {
                $this->setObservacao($observacao);
            }
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Avaliação do mês " . $mes . "/" . $ano . " foi " . ($novoStatus === 'BLOQUEADO' ? 'bloqueada' : 'liberada') . " com sucesso.");
            } else {
                throw new Exception("Não foi possível salvar o status.");
            }
        }
    }
}

<?php

/**
 * Entidade do Editor de Programa (verso do certificado).
 * Instancia o DAO e expoe os metodos de negocio. Nenhum SQL aqui.
 */
class VersoPrograma extends VersoProgramaVO
{
    private $objDAO;

    function __construct()
    {
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new VersoProgramaDAO();
    }

    /**
     * Carrega o programa salvo para o escopo (evento + oficina).
     * cod_oficina vazio/0 = programa geral do evento.
     * Retorna true se encontrou, false caso contrario (objeto fica "novo").
     */
    public function carregarPorEscopo($debug = false)
    {
        return $this->objDAO->getByEventoOficina($this, $debug);
    }

    public function carregarPorCod($debug = false)
    {
        return $this->objDAO->getByCod($this, $debug);
    }

    public function existe($debug = false)
    {
        return $this->objDAO->existePorEscopo($this, $debug);
    }

    /** Escopos do evento que ja tem programa cadastrado. */
    public function listarEscopos($debug = false)
    {
        return $this->objDAO->listarEscopos($this, $debug);
    }

    /**
     * Salva (insert ou update) as opcoes do programa. Decide pelo escopo:
     * se ja existe um registro para (evento, oficina), atualiza; senao, insere.
     */
    public function salvar($debug = false)
    {
        $this->validatorObj();

        $codExistente = $this->objDAO->getCodByEscopo($this, $debug);
        if ($codExistente) {
            $this->setCodVersoPrograma($codExistente);
            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Programa salvo com sucesso.");
            }
            throw new Exception("Nao foi possivel salvar o programa.");
        } else {
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Programa salvo com sucesso.");
            }
            throw new Exception("Nao foi possivel salvar o programa.");
        }
    }

    public function excluir($debug = false)
    {
        $this->validatorExcluir();
        if ($this->objDAO->delete($this, $debug)) {
            throw new OKException("Programa excluido com sucesso.");
        }
        throw new Exception("Nao foi possivel excluir o programa.");
    }
}

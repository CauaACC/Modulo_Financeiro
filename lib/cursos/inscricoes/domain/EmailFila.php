<?php

class EmailFila extends EmailFilaVO
{
    private $objDAO;

    function __construct()
    {
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new EmailFilaDAO();
    }

    public function salvar($debug = false)
    {
        $this->validatorObj();

        if (is_null($this->getCodEmailFila()) or $this->getCodEmailFila() == "") {
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("E-mail enfileirado com sucesso.");
            } else {
                throw new Exception("Erro ao enfileirar e-mail.");
            }
        } else {
            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("E-mail atualizado com sucesso.");
            } else {
                throw new Exception("Erro ao atualizar e-mail.");
            }
        }
    }

    public function cancelar($debug = false)
    {
        if ($this->isNullOrEmpty($this->getCodEmailFila())) {
            throw new Exception("Código do e-mail não informado.");
        }
        if ($this->objDAO->cancelar($this, $debug)) {
            throw new OKException("E-mail cancelado com sucesso.");
        } else {
            throw new Exception("Não foi possível cancelar. Verifique se o e-mail ainda está pendente.");
        }
    }

    public function cancelarPorTemplate($debug = false)
    {
        if ($this->isNullOrEmpty($this->getCodEmailTemplate())) {
            throw new Exception("Código do template não informado.");
        }
        $total = $this->objDAO->cancelarPorTemplate($this, $debug);
        throw new OKException($total . " e-mail(s) cancelado(s).");
    }

    public function getById($debug = false)
    {
        return $this->objDAO->getById($this, $debug);
    }

    public function carregarLista($debug = false)
    {
        return $this->objDAO->carregarLista($this, $debug);
    }

    public function carregarResumo($debug = false)
    {
        return $this->objDAO->carregarResumo($this, $debug);
    }

    public function enfileirarEmLote($listaEmails, $debug = false)
    {
        if (empty($listaEmails)) {
            throw new Exception("Nenhum e-mail para enfileirar.");
        }
        $total = $this->objDAO->insertEmLote($this, $listaEmails, $debug);
        throw new OKException($total . " e-mail(s) enfileirado(s) com sucesso.");
    }
}

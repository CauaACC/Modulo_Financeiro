<?php

class NotaFiscalSolicitacao extends NotaFiscalSolicitacaoVO {

    private $objDAO;

    function __construct() {
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new NotaFiscalSolicitacaoDAO();
    }

    public function getObjDAO() {
        return $this->objDAO;
    }

    public function solicitacaoExiste() {
        if (!$this->getCodSolicitacao()) {
            throw new Exception('Codigo da solicitacao invalido ou nao informado.');
        }
        if (!$this->objDAO->getByIdExiste($this)) {
            throw new Exception("Solicitacao nao encontrada para o codigo: " . $this->getCodSolicitacao() . ".");
        }
    }

    public function getSolicitacaoById($debug = false) {
        return $this->objDAO->getById($this, $debug);
    }

    public function getSolicitacaoAtivaByPagamento($debug = false) {
        if (!$this->getCodPagamento()) {
            throw new Exception("Codigo do pagamento nao informado.");
        }
        return $this->objDAO->getAtivaByPagamento($this, $debug);
    }

    public function solicitar($debug = false) {
        $this->validatorSolicitar();
        if ($this->objDAO->existeAtivaPorPagamento($this, $debug)) {
            throw new Exception("Ja existe uma solicitacao ativa de Nota Fiscal para este pagamento.");
        }
        if ($this->objDAO->insert($this, $debug)) {
            throw new OKException("Solicitacao de Nota Fiscal registrada com sucesso.");
        } else {
            throw new Exception("Solicitacao nao foi salva.");
        }
    }

    public function anexar($debug = false) {
        $this->validatorAnexar();
        $this->solicitacaoExiste();
        if ($this->objDAO->anexarArquivo($this, $debug)) {
            throw new OKException("Nota Fiscal anexada com sucesso.");
        } else {
            throw new Exception("Nota Fiscal nao foi anexada.");
        }
    }

    public function substituir(NotaFiscalSolicitacao $objNova, $debug = false) {
        $this->validatorSubstituir();
        $this->solicitacaoExiste();
        if (!$this->objDAO->desativar($this, $debug)) {
            throw new Exception("Nao foi possivel desativar a solicitacao atual.");
        }
        $objNova->setCodEvento($this->getCodEvento());
        $objNova->setCodPagamento($this->getCodPagamento());
        $objNova->setCodInscrito($this->getCodInscrito());
        $objNova->setTomadorCpfCnpj($this->getTomadorCpfCnpj());
        $objNova->setTomadorNome($this->getTomadorNome());
        $objNova->setTomadorEndereco($this->getTomadorEndereco());
        $objNova->setTomadorCep($this->getTomadorCep());
        $objNova->setTomadorCidade($this->getTomadorCidade());
        $objNova->setTomadorUf($this->getTomadorUf());
        $objNova->setTomadorEmail($this->getTomadorEmail());
        $objNova->setObservacaoInscrito($this->getObservacaoInscrito());
        if (!$this->objDAO->insertComArquivo($objNova, $debug)) {
            throw new Exception("Nao foi possivel gravar a nova solicitacao substituta.");
        }
        throw new OKException("Nota Fiscal substituida com sucesso.");
    }

    public function carregarHistoricoByPagamento($debug = false) {
        return $this->objDAO->listarHistoricoByPagamento($this, $debug);
    }

    /**
     * Solicitacao ainda nao atendida por nota emitida. E o que a emissao
     * procura para saber se ha um pedido do inscrito a fechar.
     */
    public function getSolicitacaoPendenteByPagamento($debug = false) {
        if (!$this->getCodPagamento()) {
            throw new Exception("Codigo do pagamento nao informado.");
        }
        return $this->objDAO->getAtivaSemNotaByPagamento($this, $debug);
    }

    public function vincularNotaFiscal($debug = false) {
        if (!$this->getCodSolicitacao() || !$this->getCodNotaFiscal()) {
            throw new Exception("Solicitacao ou nota fiscal nao informada para o vinculo.");
        }
        return $this->objDAO->vincularNotaFiscal($this, $debug);
    }

    public function desvincularNotaFiscal($arquivoDaNota = '', $debug = false) {
        if (!$this->getCodNotaFiscal()) {
            return false;
        }
        return $this->objDAO->desvincularNotaFiscal($this, $arquivoDaNota, $debug);
    }

}

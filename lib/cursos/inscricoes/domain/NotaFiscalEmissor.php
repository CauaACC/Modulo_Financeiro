<?php

/**
 * Empresa emissora de nota fiscal, vinculada a um gestor (sis_gestor).
 * Guarda os dados fiscais que o padrao nacional da NFS-e exige do prestador.
 */
class NotaFiscalEmissor extends NotaFiscalEmissorVO {

    private $objDAO;

    function __construct() {
        $this->objDAO = new NotaFiscalEmissorDAO();
    }

    public function getObjDAO() {
        return $this->objDAO;
    }

    public function getEmissorById($debug = false) {
        return $this->objDAO->getById($this, $debug);
    }

    public function getEmissorAtivoByGestor($debug = false) {
        if (!$this->getCodGestor()) {
            throw new Exception("Codigo do gestor nao informado.");
        }
        return $this->objDAO->getAtivoByGestor($this, $debug);
    }

    public function getEmissorAtivoByEvento($codEvento, $debug = false) {
        if (!$codEvento) {
            throw new Exception("Codigo do evento nao informado.");
        }
        return $this->objDAO->getAtivoByEvento($this, $codEvento, $debug);
    }

    public function listarEmissores($codsGestor = null, $debug = false) {
        return $this->objDAO->listar($codsGestor, $debug);
    }

    public function gravar($debug = false) {
        $this->validatorGravar();
        $this->setCnpj(preg_replace('/[^0-9]/', '', (string) $this->getCnpj()));
        $this->setCep(preg_replace('/[^0-9]/', '', (string) $this->getCep()));

        if ($this->getStatus() != 'DESATIVADA' && $this->objDAO->existeDuplicado($this, $debug)) {
            throw new Exception("Ja existe um emissor ativo com este CNPJ para o mesmo gestor. "
                . "Edite o cadastro existente em vez de criar outro.");
        }

        if ($this->getCodEmissor()) {
            if (!$this->objDAO->update($this, $debug)) {
                throw new Exception("Emissor nao foi alterado.");
            }
            throw new OKException("Emissor alterado com sucesso.");
        }

        if (!$this->objDAO->insert($this, $debug)) {
            throw new Exception("Emissor nao foi cadastrado.");
        }
        throw new OKException("Emissor cadastrado com sucesso.");
    }

    /**
     * Inscricao municipal so entra no payload quando preenchida: em municipios
     * onde a prefeitura nao registrou a IM no ambiente nacional, mandar o campo
     * derruba a emissao.
     */
    /**
     * Reserva o proximo numero de DPS. Serie vazia significa que este emissor
     * nao controla numeracao (a Focus/prefeitura resolve) — nesse caso nao ha
     * numero a reservar.
     */
    public function reservarNumeroDps($ambiente = 'producao', $debug = false) {
        if (trim((string) $this->getSerieDps()) == '') {
            return 0;
        }
        if (!$this->getCodEmissor()) {
            throw new Exception("Emissor nao identificado para reservar numero de DPS.");
        }
        return $this->objDAO->reservarNumeroDps($this, $ambiente, $debug);
    }

    /**
     * Proximo numero previsto no ambiente informado — usado pela previa, que
     * mostra sem reservar.
     */
    public function getProximoNumeroDpsDoAmbiente($ambiente) {
        return ($ambiente === 'producao')
             ? $this->getProximoNumeroDps()
             : $this->getProximoNumeroDpsHomologacao();
    }

    // ------------------------------------------------------------------
    // NF-e de mercadoria (modelo 55)
    //
    // Mesmo emissor, mesmo CNPJ e mesmo certificado da NFS-e — muda so o
    // conjunto fiscal. A numeracao e do EMITENTE (CNPJ + serie), por isso o
    // contador mora aqui e nao no canal de venda.
    // ------------------------------------------------------------------

    /**
     * Serie vazia significa que este emissor nao emite NF-e — e o que separa
     * um emissor so de servico (o MEI da OPCTEC) de um de mercadoria.
     */
    public function emiteNfe() {
        return trim((string) $this->getSerieNfe()) !== '';
    }

    public function reservarNumeroNfe($ambiente = 'producao', $debug = false) {
        if (!$this->emiteNfe()) {
            throw new Exception("Este emissor nao tem serie de NF-e configurada.");
        }
        if (!$this->getCodEmissor()) {
            throw new Exception("Emissor nao identificado para reservar numero de NF-e.");
        }
        return $this->objDAO->reservarNumeroNfe($this, $ambiente, $debug);
    }

    /**
     * Proximo numero previsto no ambiente informado — usado pela previa, que
     * mostra sem reservar.
     */
    public function getProximoNumeroNfeDoAmbiente($ambiente) {
        return ($ambiente === 'producao')
             ? $this->getProximoNumeroNfe()
             : $this->getProximoNumeroNfeHomologacao();
    }

    /**
     * CFOP depende de para onde a mercadoria vai: dentro da UF do emitente ou
     * para fora. E a unica regra fiscal da venda que o sistema decide sozinho —
     * o resto e cadastro (do emissor ou do produto).
     */
    public function getCfopParaUf($ufDestino) {
        $ufDestino = strtoupper(trim((string) $ufDestino));
        $ufEmissor = strtoupper(trim((string) $this->getUf()));

        if ($ufDestino != '' && $ufEmissor != '' && $ufDestino === $ufEmissor) {
            return $this->getCfopDentroEstado();
        }
        return $this->getCfopForaEstado();
    }

    public function temInscricaoMunicipal() {
        $im = trim((string) $this->getInscricaoMunicipal());
        return $im !== '';
    }

    public function getDescricaoAmigavel() {
        $razao = trim((string) $this->getRazaoSocial());
        $cnpj = (string) $this->getCnpj();
        if (strlen($cnpj) == 14) {
            $cnpj = substr($cnpj, 0, 2) . '.' . substr($cnpj, 2, 3) . '.' . substr($cnpj, 5, 3)
                  . '/' . substr($cnpj, 8, 4) . '-' . substr($cnpj, 12, 2);
        }
        return $razao . ($cnpj ? ' (' . $cnpj . ')' : '');
    }

}

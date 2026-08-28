<?php

class Etapa extends EtapaVO {

    /** Coluna `situacao` (renomeada de `ativa`) — 4 estados: */
    const SITUACAO_INATIVA      = 0;
    const SITUACAO_EM_ANDAMENTO = 1;
    const SITUACAO_CONCLUIDA    = 2;
    const SITUACAO_AGUARDANDO   = 3;

    /** Mapa numero -> rotulo amigavel. */
    public static $rotuloSituacao = [
        self::SITUACAO_INATIVA      => 'Inativa',
        self::SITUACAO_EM_ANDAMENTO => 'Em andamento',
        self::SITUACAO_CONCLUIDA    => 'Concluída',
        self::SITUACAO_AGUARDANDO   => 'Aguardando início',
    ];

    private $objDAO;

    function __construct() {
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new EtapaDAO();
    }

    public function isEmAndamento() { return (int)$this->getSituacao() === self::SITUACAO_EM_ANDAMENTO; }
    public function isConcluida()   { return (int)$this->getSituacao() === self::SITUACAO_CONCLUIDA; }
    public function isAguardando()  { return (int)$this->getSituacao() === self::SITUACAO_AGUARDANDO; }
    public function isInativa()     { return (int)$this->getSituacao() === self::SITUACAO_INATIVA; }

    public function getRotuloSituacao() {
        $v = (int)$this->getSituacao();
        return self::$rotuloSituacao[$v] ?? '?';
    }

    public function EtapaExiste() {
        if (!$this->getCodEtapa()) {
            throw new Exception('Código da Etapa inválido ou não informado.');
        }
        if (!$this->objDAO->getByIdExiste($this)) {
            throw new Exception("Nenhuma Etapa foi encontrada para o código informado: " . $this->getCodEtapa() . ".");
            //throw new Exception("Id informado para deleção não existe");
        }
    }

    public function salvar($debug = false) {
        
        $this->validatorObj();
        if (is_null($this->getCodEtapa()) or $this->getCodEtapa() == "") {
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Etapa salva com sucesso.");
            } else {
                throw new Exception("Nova Etapa não foi salva.");
            }
        } else {
            $this->EtapaExiste($debug);

            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Etapa foi atualizado com sucesso.");
            } else {
                throw new Exception("Etapa atual não foi atualizado.");
            }
        }
    }

    public function excluir($debug = false) {
        if($this->validatorExcluir()){
            $excluidoEtapa          = $this->objDAO->delete($this, $debug);
            if ($excluidoEtapa) {
                throw new OKException("Etapa cód:" . $this->getCodEtapa() . " foi excluída com sucesso.");
            } else {
                throw new Exception("Etapa cód:" . $this->getCodEtapa() . " não foi excluída.");
            }
        }
    }

    public function getEtapaById($debug = false) {
        $this->objDAO->getById($this, $debug);
    }

    /**
     * Busca a etapa pela ordem_etapa do evento atual.
     * @param int $ordemEtapa  Ordem da etapa (1, 2, ...)
     * @param bool $somenteAtivas Filtra situacao=1 (default true).
     * @return int|null cod_etapa ou null.
     */
    public function getEtapaByOrdem($ordemEtapa, $somenteAtivas = true, $debug = false) {
        return $this->objDAO->getEtapaByOrdem($this, $ordemEtapa, $somenteAtivas, $debug);
    }

    public function carregarListaEtapa($condicoes=false, $debug = false) {
        $this->objDAO->carregarListaEtapaByIdEvento($this, $condicoes, $debug);
        if ($this->getListaEtapa()) {
        } else {
            throw new Exception("Evento não possui Etapa cadastrada.");
        }
    }

    public function getEvento() {
        $obj->setCodEvento($this->getCodEvento());
        $obj->getEventoById();
        $obj = new Evento();
        return $obj;
    }
}

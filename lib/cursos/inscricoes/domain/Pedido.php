<?php

class Pedido extends PedidoVO {

    private $objDAO;

    public function  testarDatVencimentoItauShopline($fws_){
        if(($this->getDatVencimento() == '0000-00-00') or ($this->getDatVencimento() == '')){
            //$objLI->setDatVencimento(Date('Y-m-d'));
            $dataVencimento = somarDiasNaData(Date('Y-m-d'),3);
            if(in_array($this->getEventoId(), array(35,52,72))){
                $dataVencimento = subtrairDiasDaData($fws_['data_inicio_agenda'],10);
            }
            $this->setDatVencimento($dataVencimento);
            try{
                $this->salvar();
            } catch (OKException $ex) { }
        }
    }
    public function  testarDatVencimentoProrrogar($fws_, $debug=false){
        if(in_array($this->getEventoId(), array(45))){
            $vencimento = $this->getDatVencimento(); // Data no formato YYYY-MM-DD
            $dataAtual = date('Y-m-d'); // Data atual no formato YYYY-MM-DD

            if ($vencimento < $dataAtual) {
                $dataVencimento = somarDiasNaData($dataAtual,1);
                // Somar um dia na data atual
                $this->setDatVencimento($dataVencimento);
            }
            try{
                $this->salvar();
            } catch (OKException $ex) { }
        }
    }

    /**
     * Vencimento escalonado ancorado na data da visita (eventos AGENDA / BoleCode).
     * Etapas: visita-10 -> (vencido) visita-5 -> (vencido) visita-3 (prazo final).
     * Apos visita-3 retorna 'ENCERRADO' (a view bloqueia a emissao).
     * Ao trocar de etapa, invalida o boleto BoleCode registrado para forcar
     * re-registro com a nova data (nao altera o boleto existente no Itau).
     *
     * @param array $fws_ precisa conter 'data_inicio_agenda' (data da visita)
     * @return string '' (evento fora de escopo) | data aplicada (Y-m-d)
     *                | 'ENCERRADO' | 'SEM_VISITA' | 'PAGO'
     * @todo remover hardcode do evento 119 quando houver config por evento de inscricao
     */
    public function atualizarVencimentoAgendaEscalonado($fws_, $debug = false) {
        if (!in_array($this->getEventoId(), array(119))) {
            return '';
        }
        /* Pedido ja pago nao tem vencimento a recalcular */
        if ($this->getCodPagamento() != '' || $this->getDataPagamento() != '') {
            return 'PAGO';
        }
        $dataVisita = $this->normalizarDataYmd(isset($fws_['data_inicio_agenda']) ? $fws_['data_inicio_agenda'] : '');
        if ($dataVisita == '') {
            return 'SEM_VISITA';
        }
        $hoje = date('Y-m-d');
        $v10  = subtrairDiasDaData($dataVisita, 10);
        $v5   = subtrairDiasDaData($dataVisita, 5);
        $v3   = subtrairDiasDaData($dataVisita, 3);

        if ($hoje <= $v10) {
            $alvo = $v10;
        } elseif ($hoje <= $v5) {
            $alvo = $v5;
        } elseif ($hoje <= $v3) {
            $alvo = $v3;
        } else {
            return 'ENCERRADO';
        }

        /* Trocou de etapa: grava nova data e invalida o boleto registrado */
        if ($this->normalizarDataYmd($this->getDatVencimento()) != $alvo) {
            $this->setDatVencimento($alvo);
            try { $this->salvar($debug); } catch (OKException $ex) { }
            $this->invalidarBoletoRegistrado($debug);
        }
        return $alvo;
    }

    /**
     * Remove o boleto BoleCode registrado para este pedido, forcando que o
     * proximo acesso gere um novo boleto (com a data de vencimento atualizada).
     */
    private function invalidarBoletoRegistrado($debug = false) {
        try {
            $objBoleto = new Boleto();
            $objBoleto->setCodEvento($this->getEventoId());
            $objBoleto->setPedidoId($this->getId());
            if ($objBoleto->getBoletoByPedidoId($debug) && $objBoleto->getCodBoleto() != '') {
                try { $objBoleto->excluir($debug); } catch (OKException $ok) { }
            }
        } catch (Exception $ex) {
            /* sem boleto registrado ainda: nada a invalidar */
        }
    }

    /**
     * Normaliza datas em formatos variados (Y-m-d, Y-m-d H:i:s, Ymd, d/m/Y)
     * para Y-m-d. Retorna '' se nao reconhecer / data invalida (ex: 0000-00-00).
     */
    private function normalizarDataYmd($raw) {
        $raw = trim((string) $raw);
        if ($raw == '' || strpos($raw, '0000-00-00') === 0) {
            return '';
        }
        if (preg_match('#^(\d{2})/(\d{2})/(\d{4})#', $raw, $m)) {
            return $m[3] . '-' . $m[2] . '-' . $m[1];
        }
        if (preg_match('#^(\d{4})-(\d{2})-(\d{2})#', $raw, $m)) {
            return $m[1] . '-' . $m[2] . '-' . $m[3];
        }
        if (preg_match('#^(\d{4})(\d{2})(\d{2})$#', $raw, $m)) {
            return $m[1] . '-' . $m[2] . '-' . $m[3];
        }
        return '';
    }

    public function setDescricao($descricao){
        $descricao = substr($descricao,0,99);
        $this->descricao = $descricao;
    }
    function __construct() {
        $this->codEvento = COD_EVENTO;
        $this->eventoId = COD_EVENTO;
        $this->objDAO = new PedidoDAO();
    }

    public function PedidoExiste($debug = false) {
        if (!$this->getInscritoId()) {
            throw new Exception('Código do Inscrito inválido ou não informado: '.$this->getInscritoId());
        }
        return $this->objDAO->getByIdExiste($this, $debug);
    }

    public function carregarListaPedidosByInscritoId($debug = false) {
        $this->objDAO->carregarListaPedidosByInscritoId($this, $debug);
        if ($this->getListaPedidos()) {
            return true;
        } else {
            throw new Exception("Inscrito não possui pedidos.");
        }
    }
    /**
     * Existe pedido de mensalidade (nao desativado) do inscrito no evento?
     * Usa evento_id/inscrito_id do objeto. Em erro retorna true (nao gerar de novo).
     */
    public function existePedidoMensalidade($debug = false) {
        return $this->objDAO->existePedidoMensalidade($this->getEventoId(), $this->getInscritoId(), $debug);
    }

    /**
     * Previa da troca do dia de vencimento (nao grava). Usa evento_id/inscrito_id do objeto.
     */
    public function listarPrevisaoNovoDiaVencimento($novoDia, $debug = false) {
        return $this->objDAO->listarPrevisaoNovoDiaVencimento($this->getEventoId(), $this->getInscritoId(), $novoDia, $debug);
    }

    /**
     * Troca o dia de vencimento dos pedidos em aberto e futuros. Retorna a quantidade alterada.
     */
    public function alterarDiaVencimentoEmLote($novoDia, $debug = false) {
        return $this->objDAO->alterarDiaVencimentoEmLote($this->getEventoId(), $this->getInscritoId(), $novoDia, $debug);
    }

    public function salvar($debug = false) {

        $this->validatorObj();
        if (is_null($this->getId()) or $this->getId() == "") {
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Pedido gravado com sucesso.");
            } else {
                throw new Exception("Pedido não foi gravado com sucesso.");
            }
        } else {
            $this->PedidoExiste($debug);

            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Pedido atualizado com sucesso.");
            } else {
                throw new Exception("Pedido não foi atualizado.");
            }
        }
    }
    
    public function excluir($debug = false) {
        $this->validatorEcluir();
        $this->PedidoExiste($debug);
        if ($this->objDAO->delete($this, $debug)) {
            throw new OKException("Inscrito cód. " . $this->getInscritoId() . " foi excluído com sucesso");
        } else {
            throw new Exception("Inscrito cód. " . $this->getInscritoId() . " NÃO foi excluído");
        }
    }

    public function getPedidoById($debug = false) {
        $this->objDAO->getById($this, $debug);
    }

    public function getByInscritoId($debug = false) {
        $this->objDAO->getByInscritoId($this, $debug);
    }

    public function getEvento() {
        $obj = new InscEvento();
        $obj->setCodEvento($this->getEventoId());
        $obj->getEventoById();
        return $obj;
    }

}

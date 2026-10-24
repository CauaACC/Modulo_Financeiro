<?php

class Pagamento extends PagamentoVO {

    private $objDAO;

    function __construct() {
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new PagamentoDAO();
    }

    public function PagamentoExiste() {
//        if (!$this->getCodPagamento()) {
//            throw new Exception('Código da Pagamento inválido ou não informado.');
//        }
        if (!$this->objDAO->getByIdExiste($this)) {
            //throw new Exception("Nenhuma Pagamento foi encontrada para o código informado: " . $this->getCodPagamento() . ".");
            //throw new Exception("Id informado para deleção não existe");
        }
    }

    public function getPagamentoByPedidoId() {
        $this->objDAO->getPagamentoByPedidoId($this);
    }

    public function getPagamentoByUltimo($debug = false) {
        $this->objDAO->getPagamentoByUltimo($this);
    }

    public function novo($debug = false) {
        
    }

    public function salvar($debug = false) {

        $this->validatorObj();
        if (is_null($this->getCodPagamento()) or $this->getCodPagamento() == "") {
            if ($this->objDAO->insert($this, $debug)) {
                $this->criarLancamentoAutomatico($debug);
                GerenciarEmailAC::enviarConfirmacaoPagamento($this, $debug);
                throw new OKException("Pagamento salvo com sucesso.");
            } else {
                throw new Exception("Novo Pagamento não foi salvo.");
            }
        } else {
            //$this->PagamentoExiste($debug);
//            if ($this->objDAO->update($this, $debug)) {
//                throw new OKException("Pagamento foi atualizado com sucesso.");
//            } else {
                throw new Exception("Pagamento atual não foi atualizado.");
//            }
        }
    }
    public function salvarCargaPagamento($debug = false) {

        $this->validatorObjCarga();
        if (is_null($this->getCodPagamento()) or $this->getCodPagamento() == "") {
            if ($this->objDAO->insert($this, $debug)) {
                $this->criarLancamentoAutomatico($debug);
                GerenciarEmailAC::enviarConfirmacaoPagamento($this, $debug);
                throw new OKException("Pagamento salvo com sucesso.");
            } else {
                throw new Exception("Novo Pagamento não foi salvo.");
            }
        } else {
            if($this->getAtualizarValor()=='SIM'){
                //$this->PagamentoExiste($debug);
                if ($this->objDAO->update($this, $debug)) {
                    throw new OKException("Pagamento foi atualizado com sucesso.");
                } else {
                    throw new Exception("Pagamento atual não foi atualizado.");
                }
            }else{
                throw new OKException("Pagamento não foi atualizado.");
            }  
        }
    }
    public function salvarCargaPgSisPrime($debug = false) {
        
        // $this->validatorObjCarga();
        if (is_null($this->getCodPagamento()) or $this->getCodPagamento() == "") {
            if ($this->objDAO->insert($this, $debug)) {
                GerenciarEmailAC::enviarConfirmacaoPagamento($this, $debug);
                throw new OKException("Pagamento salvo com sucesso.");
            } else {
                throw new Exception("Novo Pagamento não foi salvo.");
            }
        } else {
            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Pagamento foi atualizado com sucesso.");
            } else {
                throw new Exception("Pagamento atual não foi atualizado.");
            }
        }
    }

    public function excluir($debug = false) {
        $this->validatorEcluir();
        if ($this->objDAO->delete($this, $debug)) {
            try {
                $lancamento = new Lancamentos();
                $lancamento->setCodEvento($this->getCodEvento());
                $lancamento->setCodPagamento($this->getCodPagamento());
                $lancamento->excluirByCodPagamento($debug);
            } catch (Exception $e) {
                // Falha na exclusão do lançamento não bloqueia a exclusão do pagamento
            }
            throw new OKException("Pagamento cód:" . $this->getCodPagamento() . " foi excluída com sucesso.");
        } else {
            throw new Exception("Pagamento cód:" . $this->getCodPagamento() . " não foi excluída.");
        }
    }
    
    /**
     * Registra o ESTORNO do pagamento: snapshot no espelho de auditoria,
     * status ESTORNADO e contra-lancamento financeiro (debito) espelhando a
     * receita da venda. A devolucao do dinheiro em si e feita FORA do sistema
     * (painel do gateway/banco) — aqui fica apenas o registro financeiro.
     */
    public function estornar($debug = false) {
        if ($this->getCodEvento() == '' or $this->getCodPagamento() == '') {
            throw new Exception("Cód. do evento/pagamento não informado para o estorno.");
        }
        if (!$this->getPagamentoById($debug)) {
            throw new Exception("Pagamento cód:" . $this->getCodPagamento()
                . " não encontrado no evento " . $this->getCodEvento() . ".");
        }
        $statusAtual = trim((string)$this->getStatus());
        if (in_array($statusAtual, array('ESTORNADO', 'CANCELADO', 'DEVOLVIDO'))) {
            throw new Exception("Pagamento cód:" . $this->getCodPagamento()
                . " já está com status " . $statusAtual . ".");
        }

        // ---- Devolucao do dinheiro via API PagBank ----------------------
        // Com transacao vinculada e token configurado, o estorno REAL sai
        // daqui; se a API falhar, NADA e registrado. Sem transacao (tesouraria,
        // empenho, manual) ou sem token (ambiente local), fica so o registro
        // financeiro e a devolucao segue manual.
        $transacaoId = trim((string)$this->getTransacaoId());
        $estornoViaApi = false;
        if ($transacaoId != '' && PagSeguroClient::estornoHabilitado()) {
            $valorCentavos = intval(round(floatval($this->getValor()) * 100));
            $cliente = new PagSeguroClient();
            $resultado = $cliente->estornarCharge(
                $transacaoId,
                $valorCentavos,
                'estorno-' . $this->getCodEvento() . '-' . $this->getCodPagamento()
            );
            if (!$resultado['ok']) {
                throw new Exception("Estorno NÃO realizado — o PagBank recusou a devolução: "
                    . $resultado['erro'] . " (nada foi registrado no sistema).");
            }
            $estornoViaApi = true;
        }

        $arquivadas = $this->objDAO->arquivarParaAtualizados(
            $this->getCodPagamento(), $this->getCodEvento(), $debug);
        if ($arquivadas == 0) {
            throw new Exception("Registro de pagamento não encontrado no evento atual"
                . " (cod_evento=" . $this->getCodEvento() . "). Verifique se você está no evento correto.");
        }

        if (!$this->objDAO->atualizarStatus(
                $this->getCodPagamento(), $this->getCodEvento(), 'ESTORNADO', $debug)) {
            throw new Exception("Status do pagamento cód:" . $this->getCodPagamento()
                . " não foi atualizado.");
        }

        $this->criarLancamentoEstorno($debug);

        if ($estornoViaApi) {
            throw new OKException("Pagamento cód:" . $this->getCodPagamento()
                . " ESTORNADO — devolução solicitada ao PagBank com sucesso.");
        }
        throw new OKException("Pagamento cód:" . $this->getCodPagamento()
            . " registrado como ESTORNADO (registro contábil apenas — devolução manual"
            . ($transacaoId == '' ? ", sem transação vinculada" : ", API PagBank não configurada") . ").");
    }

    /**
     * Contra-lancamento do estorno: debito no mesmo plano de contas da receita
     * (espelho do criarLancamentoAutomatico da venda). Falha aqui NAO bloqueia
     * o estorno — mesmo criterio da exclusao de pagamento.
     *
     * ATENCAO (verificado em 2026-08-20): a config de evento nao tem a coluna
     * `cod_plano_contas_receita` — o getter magico devolve NULL e este metodo
     * sai sem criar nada, exatamente como ja acontece com o lancamento da
     * VENDA. Enquanto essa origem nao for definida, o estorno grava status +
     * snapshot, mas NAO gera lancamento financeiro.
     */
    private function criarLancamentoEstorno($debug = false) {
        try {
            $config = EventoConfiguracao::getInstancePopulado($this->getCodEvento());
            $codPlanoContasReceita = $config->getCodPlanoContasReceita();
            if (empty($codPlanoContasReceita)) {
                return;
            }

            $lancamento = new Lancamentos();
            $lancamento->setCodEvento($this->getCodEvento());
            $lancamento->setCodPagamento($this->getCodPagamento());
            $lancamento->setTipoLancamento('ESTORNO');
            $lancamento->setValor($this->getValor());
            $lancamento->setOrigemLancamento('INTEGRACAO');
            $lancamento->setDebito($codPlanoContasReceita);
            $lancamento->setCodPlanoContas($codPlanoContasReceita);
            $lancamento->setFlgLancamento('D');
            $lancamento->setConciliado(1);
            $lancamento->setDataCompetencia(date('Y-m-d'));
            $lancamento->setDataFinanceira(date('Y-m-d'));
            $lancamento->setHistorico('Estorno de pagamento - Cód. pagamento: ' . $this->getCodPagamento()
                . ' | Inscrito: ' . $this->getCodInscrito()
                . ' | Forma: ' . $this->getFormaPagamento());
            $lancamento->salvar($debug);
        } catch (OKException $e) {
            // lancamento de estorno criado
        } catch (Exception $e) {
            error_log('[ESTORNO_PAGAMENTO] Falha no lancamento financeiro: ' . $e->getMessage());
        }
    }

    public function carregarPagamentoById($debug = false){
        return $this->getPagamentoById($debug);
    }
    
    public function getPedidoByIdPagamento($debug = false){
        if ($this->getPedidoId()!=''){
            $obj = new Pedido();
            $obj->setPedidoId($this->getPedidoId());
            $obj->getPedidoById($debug);
            return $obj;
        }return false;
    }
    
    public function getInscritoByIdPagamento($debug = false){
        if ($this->getCodInscrito()!=''){
            $obj = new Inscrito();
            $obj->setCodInscrito($this->getCodInscrito());
            $obj->getInscritoById($debug);
            return $obj;
        }return false;
    }
    
    public function getPagamentoById($debug = false) {
        if($this->getCodEvento()==''){
            throw new Exception("Cod Evento não informado.");
        }
        if($this->getCodPagamento()==''){
            throw new Exception("Cod. Pagamento não informado.");
        }    
        return $this->objDAO->getById($this, $debug);
    }
    
    public function getPagamentoByTransacaoId($debug = false) {
        if($this->getCodEvento()==''){
            throw new Exception("Cod Evento não informado.");
        }
        if($this->getTransacaoId()==''){
            throw new Exception("Cod. Pagamento não informado.");
        }    
        return $this->objDAO->getByTransacaoId($this, $debug);
    }

    public function carregarUltimoPagamentoByIdInscrito($debug = false) {
        return $this->objDAO->getUltimoPagamentoByIdInscrito($this, $debug);
    }
    
    public function carregarListaPagamentos10UltimosByIdEvento($debug = false) {
        return $this->objDAO->carregarListaPagamentos10UltimosByIdEvento($this, $debug);
    }

    public function carregarListaPagamentos($debug = false) {
        $this->objDAO->carregarListaPagamentosByIdEvento($this, $debug);
        if ($this->getListaPagamentos()) {
        } else {
            throw new Exception("Evento não possui Pagamento cadastrada.");
        }
    }
    public function carregarListaPagamentosByCodInscrito($debug = false) {
        return $this->objDAO->carregarListaPagamentosByCodInscrito($this, $debug);
    }
    public function carregarListaPagamentosByIdInscrito($debug = false) {
        return $this->objDAO->carregarListaPagamentosByCodInscrito($this, $debug);
    }

    public function getEvento() {
        $obj = new InscEvento();
        $obj->setCodEvento($this->getCodEvento());
        $obj->getEventoById();
        return $obj;
    }

    public function carregarListaMensalidades($debug = false) {
        $this->objDAO->carregarListaMensalidades($this, $debug);
    }

    public function carregarListaAcertos($debug = false) {
        $this->objDAO->carregarListaAcertos($this, $debug);
    }

    private function criarLancamentoAutomatico($debug = false) {
        try {
            error_log('[LANCAMENTO_AUTO] Inicio - codEvento=' . $this->getCodEvento() . ' codPagamento=' . $this->getCodPagamento());

            $config = EventoConfiguracao::getInstancePopulado(COD_EVENTO);
            $codPlanoContasReceita = $config->getCodPlanoContasReceita();

            error_log('[LANCAMENTO_AUTO] codPlanoContasReceita=' . var_export($codPlanoContasReceita, true));

            if (empty($codPlanoContasReceita)) {
                error_log('[LANCAMENTO_AUTO] SAINDO - codPlanoContasReceita vazio');
                return;
            }

            $lancamento = new Lancamentos();
            $lancamento->setCodEvento($this->getCodEvento());
            $lancamento->setCodPagamento($this->getCodPagamento());

            if ($lancamento->existeLancamentoByCodPagamento($debug)) {
                error_log('[LANCAMENTO_AUTO] SAINDO - lancamento ja existe para este pagamento');
                return;
            }

            $lancamento->setTipoLancamento('VENDA');
            $lancamento->setValor($this->getValor());
            $lancamento->setOrigemLancamento('INTEGRACAO');
            $lancamento->setCredito($codPlanoContasReceita);
            $lancamento->setCodPlanoContas($codPlanoContasReceita);
            $lancamento->setFlgLancamento('C');
            $lancamento->setConciliado(1);
            $lancamento->setDataCompetencia(date('Y-m-d'));
            $lancamento->setDataFinanceira(date('Y-m-d'));

            $historico = 'Lançamento automático - Transação: ' . $this->getTransacaoId()
                . ' | Inscrito: ' . $this->getCodInscrito()
                . ' | Forma: ' . $this->getFormaPagamento();
            $lancamento->setHistorico($historico);

            error_log('[LANCAMENTO_AUTO] Chamando salvar() - valor=' . $this->getValor() . ' planoContas=' . $codPlanoContasReceita);

            $lancamento->salvar($debug);
        } catch (OKException $e) {
            error_log('[LANCAMENTO_AUTO] SUCESSO - ' . $e->getMessage());
        } catch (Exception $e) {
            error_log('[LANCAMENTO_AUTO] ERRO - ' . $e->getMessage());
        }
    }

}

<?php

class Lancamentos extends LancamentosVO {

    private $objDAO;

    function __construct() {
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new LancamentosDAO();
    }
    public function getEvento() {
        $obj = new Evento();
        $obj->setCodEvento($this->getCodEvento());
        $obj->getEventoById();
        return $obj;
    }

    public function LancamentosExiste() {
        if (!$this->getCodLancamentos()) {
            throw new Exception('Código do Centro de Custo inválido ou não informado.');
        }
        if (!$this->objDAO->getByIdExiste($this)) {
            throw new Exception("Nenhum Centro de Custo foi encontrada para o código informado: " . $this->getCodLancamentos() . ".");
            //throw new Exception("Id informado para deleção não existe");
        }
    }

    public function salvar($debug = false) {
        
        $this->validatorSalvar();
        if (is_null($this->getCodLancamentos()) or $this->getCodLancamentos() == "") {
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Centro de Custo salva com sucesso.");
            } else {
                throw new Exception("Novo Centro de Custo não foi salva.");
            }
        } else {
            $this->LancamentosExiste($debug);

            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Centro de Custo foi atualizado com sucesso.");
            } else {
                throw new Exception("Centro de Custo atual não foi atualizado.");
            }
        }
    }

    public function excluir($debug = false) {
        if($this->validatorExcluir()){
            $excluidoLancamentos          = $this->objDAO->delete($this, $debug);
            if ($excluidoLancamentos) {
                throw new OKException("Centro de Custo cód:" . $this->getCodLancamentos() . " foi excluída com sucesso.");
            } else {
                throw new Exception("Centro de Custo cód:" . $this->getCodLancamentos() . " não foi excluída.");
            }
        }
    }

    public function getLancamentosById($debug = false) {
        $this->objDAO->getById($this, $debug);
    }

    public function existeLancamentoByCodPagamento($debug = false) {
        return $this->objDAO->existeLancamentoByCodPagamento($this, $debug);
    }

    public function excluirByCodPagamento($debug = false) {
        return $this->objDAO->deleteByCodPagamento($this, $debug);
    }

    /** Listagem da tela, com filtros. Sem resultado nao e erro: devolve lista vazia. */
    public function carregarListaFiltrada($filtros = array(), $debug = false) {
        return $this->objDAO->carregarListaFiltrada($this, $filtros, $debug);
    }

    public function carregarListaLancamentos($condicoes=false, $debug = false) {
        $this->objDAO->carregarListaLancamentosByIdEvento($this, $condicoes, $debug);
        if ($this->getListaLancamentos()) {
        } else {
            throw new Exception("Evento não possui Centro de Custo cadastrado.");
        }
    }
}

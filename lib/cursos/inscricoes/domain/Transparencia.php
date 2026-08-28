<?php

class Transparencia extends TransparenciaVO {

    private $objDAO;

    function __construct() {
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new TransparenciaDAO();
        $objEvConf = $this->getObjEventoConfiguracao(COD_EVENTO);
        $this->setObjEvConf($objEvConf);
        // if(COD_EVENTO == 37 or COD_EVENTO==38){
        //     $this->txtMsgDocumento = "Solicitação de Recurso";
        // }else{
        //     $this->txtMsgDocumento = $objEvConf->getTrabAnexoTipo();
        // }
    }

    public function novo($debug = false) {
        
    }

    public function ProjetoExiste() {
        if (!$this->getNumeroProjeto()) {
            throw new Exception('Número do Projeto inválido ou não informado.');
        }
        if (!$this->objDAO->getByIdExisteProjeto($this)) {
            throw new Exception("Nenhum Projeto foi encontrado para o número informado: " . $this->getNumeroProjeto() . ".");
            //throw new Exception("Id informado para deleção não existe");
        }
    }

    public function PagamentoExiste() {
        if (
            !$this->getNumeroProjeto()  ||
            !$this->getValorBruto()     ||
            !$this->getValorLiquido()   ||
            !$this->getVencimento()     ||
            !$this->getServico()        ||
            !$this->getFornecedor()     ||
            !$this->getCnpj()           ||
            !$this->getTipoRpa()
        ) {
            throw new Exception('Pagamento inválido ou faltando informação.');
        }
        if (!$this->objDAO->getByIdExistePagamento($this)) {
            throw new Exception("Nenhum Pagamento foi encontrado para a chave informada: " . $this->getPagamento() . ".");
            //throw new Exception("Id informado para deleção não existe");
        }
    }

    public function salvar($debug = false) {
        /** @todo acrescentar aqui um teste para saber se esta na tela de editação ou na tela de inscrito */
        if($_SESSION['_administrativo']){
            echo $debug? __LINE__.'-' : '';
            $this->validatorObjAdmin();
        }else{
            echo $debug? __LINE__.'-' : '';
            $this->validatorObj();
        }
        echo $debug? __LINE__.'-' : '';
        if($debug){
            exit();
        }
        if (is_null($this->getCodTransparencia()) or $this->getCodTransparencia() == "" or $this->getNovo()==1) {
            echo $debug? __LINE__.'-' : '';
            if ($this->objDAO->insert($this, $debug)) {
                echo $debug? __LINE__.'-' : '';
                throw new OKException($this->txtMsgDocumento." salvo com sucesso.");
            } else {
                echo $debug? __LINE__.'-' : '';
                throw new Exception("Novo ".$this->txtMsgDocumento." não foi salvo.");
            }
        } else {
            $this->TransparenciaExiste($debug);

            if ($this->objDAO->update($this, $debug)) {
                echo $debug? __LINE__.'-' : '';
                throw new OKException($this->txtMsgDocumento." foi atualizado com sucesso.");
            } else {
                echo $debug? __LINE__.'-' : '';
                throw new Exception($this->txtMsgDocumento." atual não foi atualizado.");
            }
        }
    }

    public function salvarProjetos($debug = false) {
        /** @todo acrescentar aqui um teste para saber se esta na tela de editação ou na tela de inscrito */
        // if($_SESSION['_administrativo']){
        //     echo $debug? __LINE__.'-' : '';
        //     $this->validatorObjAdmin();
        // }else{
        //     echo $debug? __LINE__.'-' : '';
        //     $this->validatorObjTarefa();
        // }
        if (
            !$this->getNumeroProjeto()  ||
            !$this->getEProtocolo()  ||
            !$this->getCoordenador()  ||
            $this->getDataInicio()=='Data inicio de projeto' ||
            $this->getCoordenador()=='Coordenador'
        ) {
            throw new Exception('linha inválido ou faltando informação.');
        }        
        echo $debug? __LINE__.'-' : '';
        if ($this->objDAO->getByIdExisteProjeto($this, $debug)) {
            throw new Exception("linha já cadastrada:".$this->getNumeroProjeto()." - ".$this->getCoordenador());
        }
        $this->setNovo(1);
        if (is_null($this->getId()) or $this->getId() == "" or $this->getNovo()==1) {
            echo $debug? __LINE__.'-' : '';
            if ($this->objDAO->insertProjeto($this, $debug)) {
                echo $debug? __LINE__.'-' : '';
                throw new OKException($this->txtMsgDocumento." salvo com sucesso.".$this->getNumeroProjeto()." - ".$this->getCoordenador());
            } else {
                echo $debug? __LINE__.'-' : '';
                throw new Exception("Novo ".$this->txtMsgDocumento." não foi salvo.".$this->getNumeroProjeto()." - ".$this->getCoordenador());
            }
        } else {
        //     $this->TransparenciaExiste($debug);

            if ($this->objDAO->updateProjeto($this, $debug)) {
                echo $debug? __LINE__.'-' : '';
                throw new OKException($this->txtMsgDocumento." foi atualizado com sucesso.");
            } else {
                echo $debug? __LINE__.'-' : '';
                throw new Exception($this->txtMsgDocumento." atual não foi atualizado.");
            }
        }
    }

    /**
     * Salva o projeto. Se já existir (mesmo numero_projeto), atualiza em vez de rejeitar.
     */
    public function salvarOuAtualizarProjetos($debug = false) {
        if (
            !$this->getNumeroProjeto()  ||
            !$this->getEProtocolo()  ||
            !$this->getCoordenador()  ||
            $this->getDataInicio()=='Data inicio de projeto' ||
            $this->getCoordenador()=='Coordenador'
        ) {
            throw new Exception('linha inválido ou faltando informação.');
        }

        $existente = $this->objDAO->getProjetoByNumero($this->getNumeroProjeto(), $debug);
        if ($existente) {
            $this->setId($existente['id']);
            if ($this->objDAO->updateProjeto($this, $debug)) {
                throw new OKException("Atualizado: ".$this->getNumeroProjeto()." - ".$this->getCoordenador());
            } else {
                throw new Exception("Não foi possível atualizar: ".$this->getNumeroProjeto()." - ".$this->getCoordenador());
            }
        } else {
            $this->setNovo(1);
            if ($this->objDAO->insertProjeto($this, $debug)) {
                throw new OKException("Salvo: ".$this->getNumeroProjeto()." - ".$this->getCoordenador());
            } else {
                throw new Exception("Não foi possível salvar: ".$this->getNumeroProjeto()." - ".$this->getCoordenador());
            }
        }
    }

    public function salvarPagamentos($debug = false) {
        // if (
        //     !$this->getNumeroProjeto()  ||
        //     !$this->getValorBruto()     ||
        //     !$this->getValorLiquido()   ||
        //     !$this->getVencimento()     ||
        //     !$this->getServico()        ||
        //     !$this->getFornecedor()     
        // ) {
        if (
            $this->getNumeroProjeto() === ''  || $this->getNumeroProjeto() === null ||
            $this->getValorBruto()    === ''  || $this->getValorBruto()    === null ||
            $this->getValorLiquido()  === ''  || $this->getValorLiquido()  === null ||
            $this->getVencimento()    === ''  || $this->getVencimento()    === null ||
            $this->getServico()       === ''  || $this->getServico()       === null ||
            $this->getFornecedor()    === ''  || $this->getFornecedor()    === null 
        ) {
            throw new Exception('Linha inválida ou faltando informação.');
        }      
        $txtPagamento = $this->getFornecedor()." - ".$this->getCnpj()." - ".$this->getValorLiquido()." - ".formatDateView($this->getVencimento());
        /** @todo acrescentar aqui um teste para verificar se o registro de pagamento já foi salvo antesazxxz */
        echo $debug? __LINE__.'-' : '';
        if ($this->objDAO->getByIdExistePagamento($this, $debug)) {
            throw new Exception("Linha já cadastrada: ".$txtPagamento);
        }
        $this->setNovo(1);
        if (is_null($this->getId()) or $this->getId() == "" or $this->getNovo()==1) {
            echo $debug? __LINE__.'-' : '';
            if ($this->objDAO->insertPagamento($this, $debug)) {
                echo $debug? __LINE__.'-' : '';
                throw new OKException($this->txtMsgDocumento." salvo com sucesso: ".$txtPagamento);
            } else {
                echo $debug? __LINE__.'-' : '';
                throw new Exception("Novo ".$this->txtMsgDocumento." não foi salvo: ".$txtPagamento);
            }
        } else {
        //     $this->TransparenciaExiste($debug);

            if ($this->objDAO->updatePagamento($this, $debug)) {
                echo $debug? __LINE__.'-' : '';
                throw new OKException($this->txtMsgDocumento." foi atualizado com sucesso.");
            } else {
                echo $debug? __LINE__.'-' : '';
                throw new Exception($this->txtMsgDocumento." atual não foi atualizado.");
            }
        }
    }

    public function excluir($debug = false) {
        $this->validatorEcluir();
        if ($this->objDAO->delete($this, $debug)) {
            throw new OKException($this->txtMsgDocumento." cód:" . $this->getCodTransparencia() . " foi excluído com sucesso.");
        } else {
            throw new Exception($this->txtMsgDocumento." cód:" . $this->getCodTransparencia() . " não foi excluído.");
        }
    }

    public function getTransparenciaById($debug = false) {
        if($this->getCodTransparencia()==''){
            throw new Exception('Código do '.$this->txtMsgDocumento.' não foi informado.');
        }
        return $this->objDAO->getById($this, $debug);
    }
   
    public function getListaTransparenciasByIdEvento($debug = false) {
        if (!$this->getCodEvento()) {
            throw new Exception("Id do evento não informado.");
        }
        $this->objDAO->carregarListaTransparenciasByIdEvento($this, $debug);
        if ($this->getListaTransparencias()) {
            return $this->getListaTransparencias();
        } else {
            throw new Exception("Evento não possui ".$this->txtMsgDocumento." cadastrada.");
        }
    }
    
    public function carregarListaTransparencias($condicoes = false , $debug=false){
        return $this->objDAO->carregarListaTransparencias($this, $condicoes, $debug);
    }
 
    public function getEvento() {
        $obj = new Evento();
        $obj->setCodEvento($this->getCodEvento());
        $obj->getEventoById();
        return $obj;
    }

    public function carregarDadosTransparencias($condicoes = false , $debug=false){
        return $this->objDAO->carregarDadosTransparencias($this, $condicoes, $debug);
    }

    public function deletePagamentosByProjeto($numeroProjeto, $gestor = '', $debug = false) {
        return $this->objDAO->deletePagamentosByProjeto($numeroProjeto, $gestor, $debug);
    }

    public function getProjetoByNumero($numeroProjeto, $debug = false) {
        return $this->objDAO->getProjetoByNumero($numeroProjeto, $debug);
    }

    public function getPagamentoByChave($numeroProjeto, $valorBruto, $valorLiquido, $vencimento, $servico, $fornecedor, $gestor = '', $debug = false) {
        return $this->objDAO->getPagamentoByChave($numeroProjeto, $valorBruto, $valorLiquido, $vencimento, $servico, $fornecedor, $gestor, $debug);
    }

    /**
     * Verifica se o pagamento existe usando o próprio objeto (mesmo caminho que salvarPagamentos).
     * Retorna os dados do registro existente ou false.
     */
    public function buscarPagamentoExistente($debug = false) {
        return $this->objDAO->getByIdExistePagamento($this, $debug, true);
    }

    public function listarProjetosPorArquivo($codArquivo, $debug = false) {
        return $this->objDAO->listarProjetosPorArquivo($codArquivo, $debug);
    }

    public function listarPagamentosPorArquivo($codArquivo, $debug = false) {
        return $this->objDAO->listarPagamentosPorArquivo($codArquivo, $debug);
    }

    /**
     * Salva o pagamento. Se já existir (mesma chave composta), atualiza em vez de rejeitar.
     */
    public function salvarOuAtualizarPagamentos($debug = false) {
        if (
            $this->getNumeroProjeto() === ''  || $this->getNumeroProjeto() === null ||
            $this->getValorBruto()    === ''  || $this->getValorBruto()    === null ||
            $this->getValorLiquido()  === ''  || $this->getValorLiquido()  === null ||
            $this->getVencimento()    === ''  || $this->getVencimento()    === null ||
            $this->getServico()       === ''  || $this->getServico()       === null ||
            $this->getFornecedor()    === ''  || $this->getFornecedor()    === null
        ) {
            throw new Exception('Linha inválida ou faltando informação.');
        }
        $txtPagamento = $this->getFornecedor()." - ".$this->getCnpj()." - ".$this->getValorLiquido()." - ".formatDateView($this->getVencimento());

        $existente = $this->objDAO->getPagamentoByChave(
            $this->getNumeroProjeto(),
            $this->getValorBruto(),
            $this->getValorLiquido(),
            $this->getVencimento(),
            $this->getServico(),
            $this->getFornecedor(),
            $this->getGestor(),
            $debug
        );
        if ($existente) {
            $this->setId($existente['id']);
            if ($this->objDAO->updatePagamento($this, $debug)) {
                throw new OKException("Atualizado: ".$txtPagamento);
            } else {
                throw new Exception("Não foi possível atualizar: ".$txtPagamento);
            }
        } else {
            $this->setNovo(1);
            if ($this->objDAO->insertPagamento($this, $debug)) {
                throw new OKException("Salvo: ".$txtPagamento);
            } else {
                throw new Exception("Não foi possível salvar: ".$txtPagamento);
            }
        }
    }

    public function deleteProjetosByArquivo($codArquivo, $debug = false) {
        return $this->objDAO->deleteProjetosByArquivo($codArquivo, $debug);
    }

    public function deletePagamentosByArquivo($codArquivo, $debug = false) {
        return $this->objDAO->deletePagamentosByArquivo($codArquivo, $debug);
    }

    // ==================== GESTAO DE REGISTROS ====================

    public function listarProjetosFiltrados($filtros = array(), $debug = false) {
        return $this->objDAO->listarProjetosFiltrados($this->getGestor(), $filtros, $debug);
    }

    public function listarPagamentosFiltrados($filtros = array(), $debug = false) {
        return $this->objDAO->listarPagamentosFiltrados($this->getGestor(), $filtros, $debug);
    }

    public function getProjetoById($id, $debug = false) {
        return $this->objDAO->getProjetoById($id, $this->getGestor(), $debug);
    }

    public function getPagamentoById($id, $debug = false) {
        return $this->objDAO->getPagamentoById($id, $this->getGestor(), $debug);
    }

    public function atualizarStatusPublicacao($tabela, $id, $novoStatus, $debug = false) {
        return $this->objDAO->atualizarStatusPublicacao($tabela, $id, $novoStatus, $this->getGestor(), $debug);
    }

    public function atualizarStatusPublicacaoEmLote($tabela, $ids, $novoStatus, $debug = false) {
        return $this->objDAO->atualizarStatusPublicacaoEmLote($tabela, $ids, $novoStatus, $this->getGestor(), $debug);
    }

    public function atualizarStatusPublicacaoPorArquivo($tabela, $codArquivo, $novoStatus, $debug = false) {
        return $this->objDAO->atualizarStatusPublicacaoPorArquivo($tabela, $codArquivo, $novoStatus, $this->getGestor(), $debug);
    }

    public function carregarDadosDashboard($ano = '', $mes = '', $debug = false) {
        return $this->objDAO->carregarDadosDashboard($this->getGestor(), $ano, $mes, $debug);
    }

    public function contarRegistrosPorStatus($tabela, $debug = false) {
        return $this->objDAO->contarRegistrosPorStatus($tabela, $this->getGestor(), $debug);
    }

    public function listarArquivosCarga($debug = false) {
        return $this->objDAO->listarArquivosCarga($this->getGestor(), $debug);
    }

}

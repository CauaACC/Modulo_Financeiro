<?php

class AvaliacaoInscrito extends AvaliacaoInscritoVO {

    private $objDAO;

    function __construct() {
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new AvaliacaoInscritoDAO();
    }
        
    public function cerregarAvaliacaoInscritoTotal($debug=false) {
        $this->objDAO->carregarAvaliacaoInscritoTotal($this, $debug);
    }
    
    public function carregarListaAvaliacoesByIdTrabalho($debug = false) {
        $this->objDAO->carregarListaAvaliacoesByIdTrabalho($this, $debug);
        if ($this->getListaAvaliacoes()) {
            return true;
        } else {
            throw new Exception("Trabalho não possui avaliações cadastradas.");
        }
    }
    
    public function AvaliacaoInscritoExiste($debug=false) {
       if (!$this->getCodAvaliacao()) {
           throw new Exception('Código da Avaliação Inscrito inválido ou não informado.');
       }
       if (!$this->objDAO->getByIdExiste($this,$debug)) {
           throw new Exception("Nenhuma Avaliação Inscrito foi encontrada para o código informado: " . $this->getCodAvaliacao() . ".");
           //throw new Exception("Id informado para deleção não existe");
       }
    }
    public function solicitouRecurso($debug=false) {
        if (!$this->getCodInscrito()) {
            throw new Exception('Código da Inscrição inválido ou não informado.');
        }
        $objTrabalho = new Trabalho();
        $objTrabalho->setCodInscrito($this->getCodInscrito());
        $objTrabalho->carregarListaTrabalhosByIdInscritoRecurso();
        if ($objTrabalho->getListaTrabalhos()) {
            // throw new Exception("Nenhum Trabalho de Recurso foi encontrado para o inscrito informado: " . $this->getCodInscrito() . ".");
            //throw new Exception("Id informado para deleção não existe");
            foreach ($objTrabalho->getListaTrabalhos() as $objT){
                if($objT->getTipoDocumento()=='RECURSO'){
                    $this->setCodRecurso($objT->getCodTrabalho());
                    return true;
                }
            }
        }
    }
    
    public function carregarListaAvaliacaoInscritoByIdInscrito($debug = false) {
        $this->objDAO->carregarListaAvaliacaoInscritoByIdInscrito($this, $debug);
        if ($this->getListaAvaliacaoInscrito()) {
            return true;
        } else {
            throw new Exception("Inscrito não possui avaliações cadastradas.");
        }
    }

    public function carregarListaAvaliacaoInscritoByIdInscritoProRedeFase1($debug = false) {
        $this->objDAO->carregarListaAvaliacaoInscritoByIdInscritoProRedeFase1($this, $debug);
        if ($this->getListaAvaliacaoInscrito()) {
            return true;
        } else {
            throw new Exception("Inscrito não possui avaliações cadastradas.");
        }
    }

    /**
     * Carrega o registro da fase 1 (cod_etapa=1) da tab_trabalhos_avaliacao_inscricao.
     * Usado pra mostrar HOMOLOGADA / NÃO HOMOLOGADA na área do inscrito.
     * Retorna true se existe registro, false se ainda não foi avaliado.
     */
    public function carregarAvaliacaoFase1ByIdInscrito($debug = false) {
        return $this->objDAO->carregarAvaliacaoFase1ByIdInscrito($this, $debug);
    }

    /**
     * Carrega o registro da fase 2 (ordem_etapa=2) da avaliacao de documentos
     * feita pela Comissao Avaliadora. Usado pra mostrar DEFERIDO/INDEFERIDO + nota.
     */
    public function carregarAvaliacaoFase2ByIdInscrito($debug = false) {
        return $this->objDAO->carregarAvaliacaoFase2ByIdInscrito($this, $debug);
    }

    /**
     * Salva (atualiza) o texto do recurso na etapa 1 do inscrito.
     * Pré-requisitos: setCodInscrito(), setRecursoTexto().
     * (cod_evento já vem do __construct via COD_EVENTO)
     */
    public function salvarRecursoFase1ByIdInscrito($debug = false) {
        return $this->objDAO->salvarRecursoFase1ByIdInscrito($this, $debug);
    }
    public function salvarRecursoFase2ByIdInscrito($debug = false) {
        return $this->objDAO->salvarRecursoFase2ByIdInscrito($this, $debug);
    }
    public function carregarListaAvaliacaoInscritoByIdInscritoProRedeFase2($debug = false) {
        $this->objDAO->carregarListaAvaliacaoInscritoByIdInscritoProRedeFase2($this, $debug);
        if ($this->getListaAvaliacaoInscrito()) {
            return true;
        } else {
            throw new Exception("Inscrito não possui avaliações cadastradas.");
        }
    }

    public function getAvaliacaoRecursoProRedeFase1($debug = false) {
        $this->objDAO->getAvaliacaoRecursoProRedeFase1($this, $debug);
    }

    public function carregarListaUltimasAvaliacaoPremiacaoByIdTrabalho($debug = false) {
        $this->objDAO->carregarListaUltimasAvaliacaoPremiacaoByIdTrabalho($this, $debug);
        if ($this->getListaUltimasAvaliacaoPremiacao()) {
            return true;
        } else {
            throw new Exception("Trabalho não possui avaliações cadastradas.");
        }
    }
    
    public function carregarUltimaAvaliacaoByIdTrabalho($debug = false) {
        return $this->objDAO->getUltimaAvaliacaoByIdTrabalho($this, $debug);
    }
    
    public function salvar($debug = false) {
        $this->validatorObj();
        if (is_null($this->getCodAvaliacao()) or $this->getCodAvaliacao() == "") {
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Avaliação Inscrito salva com sucesso.");
            } else {
                throw new Exception("Novo Avaliação Inscrito não foi salvo.");
            }
        } else {
            $this->AvaliacaoInscritoExiste($debug);
            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Avaliação Inscrito foi atualizado com sucesso.");
            } else {
                throw new Exception("Avaliação Inscrito atual não foi atualizado.");
            }
        }
    }

    public function excluir($debug = false) {
        if($this->validatorExcluir()){
        if ($this->objDAO->delete($this, $debug)) {
                throw new OKException("Avaliação Inscrito cód:" . $this->getCodAvaliacao() . " foi excluído com sucesso.");
        } else {
                throw new Exception("Avaliação Inscrito cód:" . $this->getCodAvaliacao() . " não foi excluído.");
        }
        }
    }

    public function getAvaliacaoInscritoById($debug = false) {
        $this->objDAO->getById($this, $debug);
    }

    public function getAvaliacaoInscritoByIdInscrito($debug = false) {
        $this->objDAO->getByIdInscrito($this, $debug);
    }

    public function getListaRecursosSolicitadosByIdInscrito($debug = false) {
        $this->objDAO->getListaRecursosSolicitadosByIdInscrito($this, $debug);
    }

    public function carregarListaDocumentosByIdInscrito($debug = false) {
        $this->objDAO->carregarListaDocumentosByIdInscrito($this, $debug);
    }

    public function carregarListaAvaliacaoInscrito($condicoes=false, $debug = false) {
        $this->objDAO->carregarListaAvaliacaoInscritoByIdEvento($this, $condicoes, $debug);
        if ($this->getListaAvaliacaoInscrito()) {
        } else {
            throw new Exception("Evento não possui Avaliação Inscrito cadastrado.");
        }
    }
    
    public function getListaAvaliacaoInscritosByIdEvento($condicoes = false, $debug = false) {
        if (!$this->getCodEvento()) {
            throw new Exception("Id do evento não informado.");
        }
        $this->objDAO->carregarListaAvaliacaoInscritoByIdEvento($this, $condicoes, $debug);
        if ($this->getListaAvaliacaoInscritos()) {
            return $this->getListaAvaliacaoInscritos();
        } else {
            throw new Exception("Evento não possui Avaliação Inscrito cadastrada.");
        }
    }    
    
    public function carregarListaAvaliacaoInscritosByIdEvento($debug = false) {
        $this->objDAO->carregarListaAvaliacaoInscritoByIdEvento($this, $debug);
        if ($this->getListaAvaliacaoInscritos()) {
        } else {
            throw new Exception("Evento não possui Avaliação Inscrito cadastrada.");
        }
    }

    /**
     * Lista os inscritos elegiveis para a Fase 2 (habilitados na Fase 1 final).
     * Faz LEFT JOIN com a Fase 2 para indicar pendentes/avaliados.
     */
    public function carregarListaInscritosElegiveisFase2($condicoes = false, $debug = false) {
        $this->objDAO->carregarListaInscritosElegiveisFase2($this, $condicoes, $debug);
        return $this->getListaAvaliacaoInscritos();
    }
    
    public function carregarListaPerfil($condicoes=false, $debug = false) {
        $obj= new InscritoPerfil();
        $obj->setCodInscrito($this->getCodInscrito());
        $obj->carregarListaPerfilByIdInscrito($debug);
        $this->setListaPerfil($obj->getListaPerfil());
        if ($this->getListaPerfil()) {

        } else {
            throw new Exception("Avaliação Inscrito não possui perfis cadastrados.");
        }
    }
    
    public function desativarAvaliacoesAvaliador($debug = false) {
        $this->validarLiberarAvaliador();
        if ($this->objDAO->desativarAvaliacoesAvaliador($this, $debug)) {
            throw new OKException("Trabalho liberado para outro avaliador com sucesso.");
        } else {
            throw new Exception("Liberação para outro avaliador não foi executada.");
        }
    }

    public function getHistoricoAvaliacaoById($debug = false) {
        $this->objDAO->getById($this, $debug);
    }
    
    public function getHistoricoAvaliacaoByIdAvaliador($debug = false) {
        return $this->objDAO->getByIdAvaliador($this, $debug);
    }

    public function getEvento() {
        $obj = new InscEvento();
        $obj->setCodEvento($this->getCodEvento());
        $obj->getEventoById();
        return $obj;
    }

    public function carregarObjAvaliador() {
        $obj = new Avaliador();
        $obj->setCodAvaliador($this->getCodAvaliador());
        $obj->getAvaliadorByIdPremiacao();
        $this->setObjAvaliador($obj);
    }

}

<?php

class Projeto extends ProjetoVO
{

    private $objDAO;

    function __construct()
    {
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new ProjetoDAO();
    }

    function cerregarProjetoTotal($debug = false)
    {
        $this->objDAO->carregarProjetoTotal($this, $debug);
    }

    public function ProjetoExiste()
    {
        if (!$this->getCodProjeto()) {
            throw new Exception('Código da Novo Projeto inválido ou não informado.');
        }
        if (!$this->objDAO->getByIdExiste($this)) {
            throw new Exception("Nenhuma Novo Projeto foi encontrada para o código informado: " . $this->getCodProjeto() . ".");
            //throw new Exception("Id informado para deleção não existe");
        }
    }

    public function novo($debug = false)
    {

    }

    public function salvar($debug = false)
    {
        $this->validatorObj();
        if (is_null($this->getCodProjeto()) or $this->getCodProjeto() == "") {
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Novo Projeto salva com sucesso.");
            } else {
                throw new Exception("Novo Novo Projeto não foi salvo.");
            }
        } else {
            $this->ProjetoExiste($debug);
            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Novo Projeto foi atualizado com sucesso.");
            } else {
                throw new Exception("Novo Projeto atual não foi atualizado.");
            }
        }
    }

    public function excluir($debug = false)
    {
        if ($this->validatorEcluir()) {
            if ($this->objDAO->delete($this, $debug)) {
                throw new OKException("Novo Projeto cód:" . $this->getCodProjeto() . " foi excluído com sucesso.");
            } else {
                throw new Exception("Novo Projeto cód:" . $this->getCodProjeto() . " não foi excluído.");
            }
        }
    }

    public function getProjetoById($debug = false)
    {
        $this->objDAO->getById($this, $debug);
    }

    public function getProjetoByCpf($debug = false)
    {
        $this->objDAO->getByCpf($this, $debug);
    }

    public function carregarListaProjeto($condicoes = false, $debug = false)
    {
        $this->objDAO->carregarListaProjetoByIdEvento($this, $condicoes, $debug);
        if ($this->getListaProjeto()) {
        } else {
            throw new Exception("Evento não possui Projeto cadastrado.");
        }
    }

    public function getListaProjetosByIdEvento($debug = false)
    {
        if (!$this->getCodEvento()) {
            throw new Exception("Id do evento não informado.");
        }
        $this->objDAO->carregarListaProjetoByIdEvento($this, $debug);
        if ($this->getListaProjetos()) {
            return $this->getListaProjetos();
        } else {
            throw new Exception("Evento não possui Projeto cadastrada.");
        }
    }

    public function carregarListaProjetosByIdEvento($debug = false)
    {
        $this->objDAO->carregarListaProjetoByIdEvento($this, $debug);
        if ($this->getListaProjetos()) {
        } else {
            throw new Exception("Evento não possui Projeto cadastrada.");
        }
    }

    public function carregarListaPerfil($condicoes = false, $debug = false)
    {
        $obj = new InscritoPerfil();
        $obj->setCodInscrito($this->getCodInscrito());
        $obj->carregarListaPerfilByIdInscrito($debug);
        $this->setListaPerfil($obj->getListaPerfil());
        if ($this->getListaPerfil()) {

        } else {
            throw new Exception("Novo Projeto não possui perfis cadastrados.");
        }
    }

    public function getEvento()
    {
        $obj->setCodEvento($this->getCodEvento());
        $obj->getEventoById();
        $obj = new Evento();
        return $obj;
    }
}
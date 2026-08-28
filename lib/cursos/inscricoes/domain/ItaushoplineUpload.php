<?php

class ItaushoplineUpload extends ItaushoplineUploadVO {

    private $objDAO;

    function __construct() {
        $this->objDAO = new ItaushoplineUploadDAO();
    }

    public function ItaushoplineArquivoExiste() {
        $this->validatorObj();
        if (!$this->objDAO->getByIdExiste($this)) {
            throw new Exception("Nenhum Arquivo foi encontrado para o c�digo informado.");
        }
    }

    public function novo($debug = false) {
        
    }

    public function carregarObjItaushoplineArquivoById($debug = false) {
        $this->objDAO->getById($this, $debug);
    }

    public function salvar($debug = false) {
        $this->validarSalvar();
        if ($this->objDAO->getByIdExiste($this, $debug)) {
            throw new OKException("Arquivo j� foi cadastrado.");
        } else {
//O Arquivo s� pode ser adicionado uma unida vez.
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Arquivo salvo com sucesso.");
            } else {
                throw new Exception("Novo Arquivo não foi salvo.");
            }
        }
    }

    public function gerarSequenciaArquivo($debug = false) {
        if (!$this->objDAO->getLastId($this)) {
            throw new Exception("Erro na gera��o da sequ�ncia do arquivo!");
        }
    }

    public function atualizar($debug = false) {
        if ($this->objDAO->getByIdExiste($this, $debug)) {
            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Arquivo foi atualizado com sucesso.");
            } else {
                throw new Exception("Arquivo não foi atualizado.");
            }
        }
    }

    public function excluir($debug = false) {
        $this->validatorExcluir();
        if ($this->objDAO->delete($this, $debug)) {
            throw new OKException("Arquivo c�d:" . $this->getCodItaushoplineArquivo() . " foi exclu�do com sucesso.");
        } else {
            throw new Exception("Arquivo c�d:" . $this->getCodItaushoplineArquivo() . " não foi exclu�do.");
        }
    }

    public function carregarListaItaushoplineArquivosByIdEvento($debug = false) {
        $this->validaCarregarLista();
        $this->objDAO->carregarListaItaushoplineArquivosByIdEvento($this, $debug);
        if (!$this->getListaItaushoplineArquivos()) {
            throw new Exception("Evento não possui arquivos de carga cadastrados.");
        }
    }

    public function carregarObjEvento($debug = false) {
        $obj = new InscEvento();
        $obj->setCodEvento($this->getCodEvento());
        $obj->getEventoById($debug);
        $this->setObjEvento($obj);
    }

    public function carregarNomeTipoPerfil() {
        $objC = new InscCurso();
        $objC->setCodEvento($this->getCodEvento());
        $objC->setCodCurso($this->getCodPerfil());
        $objC->carregarObjCursoById();
        $this->setNomeTipoPerfil(strtoupper($objC->getModalidade()) . ' - ' .$objC->getTitulo());
    }
}

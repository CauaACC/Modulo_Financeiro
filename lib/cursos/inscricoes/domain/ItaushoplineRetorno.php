<?php

class ItaushoplineRetorno extends ItaushoplineRetornoVO {

    private $objDAO;

    function __construct() {
        $this->objDAO = new ItaushoplineRetornoDAO();
    }

    public function ItaushoplineArquivoExiste() {
        $this->validatorObj();
        if (!$this->objDAO->getByIdExiste($this)) {
            throw new Exception("Nenhum Arquivo foi encontrado para o código informado.");
        }
    }

    public function novo($debug = false) {
        
    }

    public function carregarObjItaushoplineArquivoById($debug = false) {
        $this->objDAO->getById($this, $debug);
    }

    public function salvarCargaRetorno($sqlInsert, $debug = false) {
        if ($this->objDAO->insertCargaRetorno($sqlInsert, $debug)) {
            throw new OKException("Arquivo salvo com sucesso.");
        } else {
            throw new Exception("Novo Arquivo não foi salvo.");
        }
    }
    /**
     * Modelo para salvar lista de objetos, sem precisar gerar sql no controller
     * $objOffice->setStatus('EXECUTADO');
     * $return[] = clone $objOffice;
     * $objOffice->setListaRetorno($return);
     * $objOffice->salvarListaRetorno($debug);
     */
    public function salvarListaRetorno($debug=false) {
        if (count($this->getListaRetorno())>=1) {
            if ($this->objDAO->salvarListaRetorno($this, $debug)) {
                throw new OKException("Arquivo de retorno carregado com sucesso.");
            } else {
                throw new Exception("Ocorreu um erro ao carregar arquivo de retorno.");
            }
        }else{
            throw new Exception("Arquivo de retorno vazio.");
        }
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

    public function excluir($debug = false) {
        $this->validatorExcluir();
        if ($this->objDAO->delete($this, $debug)) {
            throw new OKException("Arquivo c�d:" . $this->getCodArquivo() . " foi excluído com sucesso.");
        } else {
            throw new Exception("Arquivo c�d:" . $this->getCodArquivo() . " não foi excluído.");
        }
    }
    public function excluirListaRetorno($debug = false) {
        //$this->validatorExcluir();
        if ($this->objDAO->deleteListaRetorno($this, $debug)) {
            throw new OKException("Arquivo c�d:" . $this->getCodArquivoUpload() . " foi excluído com sucesso.");
        } else {
            throw new Exception("Arquivo c�d:" . $this->getCodArquivoUpload() . " não foi excluído.");
        }
    }

    public function carregarListaPagamentosItaushoplineByIdInscrito($debug = false) {
        if($this->getCodEvento()==''){
            throw new Exception("Código do evento não informado.");
        }
        if($this->getCodInscrito()==''){
            throw new Exception("Código do inscrito não informado.");
        }

        $this->objDAO->carregarListaPagamentosItaushoplineByIdInscrito($this, $debug);
        if (!$this->getListaPagamentosItaushopline()) {
            throw new Exception("Inscrito não possui registro de pagamentos.");
        }
    }
    public function carregarListaItaushoplineArquivosByIdEvento($debug = false) {
        $this->validaCarregarLista();
        $this->objDAO->carregarListaItaushoplineArquivosByIdEvento($this, $debug);
        if (!$this->getListaItaushoplineArquivos()) {
            throw new Exception("Evento não possui arquivos de carga cadastrados.");
        }
    }
    
    public function carregarListaEventosArquivosByIdArquivo($debug = false) {
        if($this->getCodArquivoUpload()==''){
            throw new Exception("Código do arquivo de retorno não informado.");
        }
        $this->objDAO->carregarListaEventosArquivosByIdArquivo($this, $debug);
        if (!$this->getListaEventosArquivos()) {
            throw new Exception("Arquivo não possui boletos carregados.");
        }
    }
    public function carregarListaEventosArquivosByIdEvento($debug = false) {
        if($this->getCodEvento()==''){
            throw new Exception("Código do evento não informado.");
        }
        $this->objDAO->carregarListaEventosArquivosByIdEvento($this, $debug);
        if (!$this->getListaEventosArquivos()) {
            throw new Exception("Evento não possui boletos carregados.");
        }
    }

    public function carregarObjEvento($debug = false) {
        $obj = new InscEvento();
        $obj->setCodEvento($this->getCodEvento());
        $obj->getEventoById($debug);
        $this->setObjEvento($obj);
    }

}

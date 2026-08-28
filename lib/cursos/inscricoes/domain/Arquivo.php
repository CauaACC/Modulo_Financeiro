<?php

class Arquivo extends ArquivoVO {

    private $objDAO;

    function __construct() {
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new ArquivoDAO();   
    }

    public function getLastId(){
        return $this->objDAO->getLastId($this);
    }

    public function arquivoExiste() {
        if (!$this->getCodArquivo()) {
            throw new Exception('Código da Arquivo inválido ou não informado.');
        }
        if (!$this->objDAO->getById($this)) {
            throw new Exception("Nenhuma Arquivo  foi encontrada para o código informado: " . $this->getCodArquivo() . ".");
            //throw new Exception("Id informado para deleção não existe");
        }
    }

    public function arquivoByNomeExiste() {
        if (!$this->getArquivoUpload()) {
            throw new Exception('Nome da Arquivo inválido ou não informado.');
        }
        if ($this->objDAO->getByNomeExiste($this)) {
            throw new Exception("Arquivo já cadastrado: " . $this->getArquivoUpload() . ".");
            //throw new Exception("Id informado para deleção não existe");
        }
    }

    public function novo($debug = false) {
        
    }

    public function substituirArquivo($debug = false) {
        $this->validatorObj();
        $this->objDAO->desativar($this, $debug);
        $this->setCodArquivo(NULL);
        if ($this->objDAO->insert($this, $debug)) {
            throw new OKException("Arquivo salvo com sucesso.");
        } else {
            throw new Exception("Novo Arquivo não foi salvo.");
        }
    }
    public function salvar($debug = false) {
        
        $this->validatorObj();
        if (is_null($this->getCodArquivo()) or $this->getCodArquivo() == "") {
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Arquivo salvo com sucesso.");
            } else {
                throw new Exception("Novo Arquivo não foi salvo.");
            }
        } else {
            $this->arquivoExiste($debug);

            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Arquivo foi atualizada com sucesso.");
            } else {
                throw new Exception("Arquivo atual não foi atualizada.");
            }
        }
    }
    
    public function atualizar($debug=false) {
        if ($this->getCodArquivo() and $this->getCodArquivo() != "") {
            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Arquivo processado com sucesso.");
            } else {
                throw new Exception("Arquivo não foi processo com sucesso.");
            }
        }
    }
    
    public function excluir($debug = false) {
        $this->validatorEcluir();
        if ($this->objDAO->delete($this, $debug)) {
            throw new OKException("Arquivo  cód:" . $this->getCodArquivo() . " foi excluído com sucesso.");
        } else {
            throw new Exception("Arquivo  cód:" . $this->getCodArquivo() . " não foi excluído.");
        }
    }
    
    public function desativar($debug = false) {
        $this->validatorEcluir();
        if ($this->objDAO->desativar($this, $debug)) {
            throw new OKException("Arquivo cód:" . $this->getCodArquivo() . " foi excluído com sucesso.");
        } else {
            throw new Exception("Arquivo cód:" . $this->getCodArquivo() . " não foi excluído.");
        }
    }

    public function carregarArquivoById($debug = false) {
        return $this->objDAO->getById($this, $debug);
    }

    public function carregarArquivoByCodArquivo($debug = false) {
        return $this->objDAO->getByCodArquivo($this, $debug);
    }

    /**Este médoto precisa receber um array de tipo de arquivos externos para carregar a lista corretamente */
    public function carregarListaArquivoByIdEvento($debug = false) {
        $this->objDAO->carregarListaArquivoByIdEvento($this, $debug);
        if ($this->getListaArquivo()) {
        } else {
            throw new Exception("Evento não possui Arquivo cadastrada.");
        }
    }
    /**Este médoto precisa receber um array de tipo de arquivos externos para carregar a lista corretamente */
    public function carregarListaArquivoCargaProjetosPgByIdEvento($debug = false) {
        $this->objDAO->carregarListaArquivoCargaProjetosPgByIdEvento($this, $debug);
        if ($this->getListaArquivo()) {
        } else {
            throw new Exception("Evento não possui Arquivo cadastrada.");
        }
    }

    public function carregarListaCargaTransparenciaFiltrada($filtros=array(), $debug = false) {
        $this->objDAO->carregarListaCargaTransparenciaFiltrada($this, $filtros, $debug);
        if ($this->getListaArquivo()) {
        } else {
            throw new Exception("Nenhum arquivo encontrado para os filtros informados.");
        }
    }
    function carregarArquivoByIdOficinaAssCoordenador($debug = false){
        return $this->objDAO->carregarArquivoOficinaAssCoordenadorBy($this, $condicoes, $debug);
    }
    function carregarArquivoByIdOficinaPrograma($debug = false){
        return $this->objDAO->carregarArquivoOficinaProgramaBy($this, $condicoes, $debug);
    }
    // function carregarListaArquivoBy($condicoes, $debug = false){
    //     $this->objDAO->carregarListaArquivoBy($this, $condicoes, $debug);
    //     if ($this->getListaArquivo()) {
    //     } else {
    //         throw new Exception("Anexo não cadastrado.");
    //     }        
    // }
    public function getEvento() {
        $obj = new Evento();
        $obj->setCodEvento($this->getCodEvento());
        $obj->getEventoById();
        return $obj;
    }
    public function getEventoConfiguracao() {
        $obj = new EventoConfiguracao();
        $obj->setCodEvento($this->getCodEvento());
        $obj->getEventoConfiguracaoById();
        return $obj;
    }
}


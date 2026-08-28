<?php

class Oficina extends OficinaVO {

    private $objDAO;

    function __construct() {
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new OficinaDAO();
    }
    public function verificandoInscritoApto($debug=false) {
        if (!$this->getCodOficina()) {
            throw new Exception('Código da Oficina inválido ou não informado.');
        }
        if (!$this->objDAO->verificandoInscritoApto($this,$debug)) {
            return false;
        }
        return true;
    }
    public function getInscritoAptoCount($debug=false) {
        return $this->objDAO->getInscritoAptoCount($this,$debug);
    }
    function verifAssinaturaCoordenador($debug = false){
        $arquivo = "coordenador-".numberPad($this->getCodOficina(),7).".jpg";
        $filepath = WB_PATH_LOG_CERTIFICADOS."../assinaturas/";
        echo $debug? $filepath .$arquivo.'<br>' : '';
        if(file_exists($filepath .$arquivo)){
            return true;
        }
        return false;
    }
    function verifProgramaCertificado(){
        $filepath = WB_PATH_LOG_CERTIFICADOS.'verso/';
        $arquivo = 'programa-'.numberPad($this->getCodOficina(), 7).'.pdf';
        if(file_exists($filepath .$arquivo)){
            return true;
        }
        return false;
    }
    public function OficinaExiste($debug=false) {
        if (!$this->getCodOficina()) {
            throw new Exception('Código da Oficina inválido ou não informado.');
        }
        if (!$this->objDAO->getByIdExiste($this,$debug)) {
            throw new Exception("Nenhuma Oficina foi encontrada para o código informado: " . $this->getCodOficina() . ".");
            //throw new Exception("Id informado para deleção não existe");
        }
        return true;
    }

    public function novo($debug = false) {
        
    }

    public function salvar($debug = false) {
        
        $this->validatorObj();
        if (is_null($this->getCodOficina()) or $this->getCodOficina() == "") {
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Oficina salva com sucesso.");
            } else {
                throw new Exception("Novo Oficina não foi salva.");
            }
        } else {
            $this->OficinaExiste($debug);

            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Oficina foi atualizada com sucesso.");
            } else {
                throw new Exception("Oficina atual não foi atualizada.");
            }
        }
    }

    public function excluir($debug = false) {
        $this->validatorExcluir();
        if ($this->objDAO->delete($this, $debug)) {
            throw new OKException("Oficina cód:" . $this->getCodOficina() . " foi excluída com sucesso.");
        } else {
            throw new Exception("Oficina cód:" . $this->getCodOficina() . " não foi excluída.");
        }
    }

    public function toggleCampo($campo, $debug = false) {
        return $this->objDAO->toggleCampo($this, $campo, $debug);
    }

    /** Grava SOMENTE os campos de certificado da atividade (UPDATE direcionado). */
    public function gravarCertificado($debug = false) {
        if (!$this->getCodEvento() || !$this->getCodOficina()) {
            throw new Exception("Evento e atividade são obrigatórios.");
        }
        if ($this->objDAO->updateCertificado($this, $debug)) {
            throw new OKException("Certificado da atividade salvo com sucesso.");
        }
        throw new Exception("Não foi possível salvar o certificado da atividade.");
    }

    public function getOficinaById($debug = false) {
        return $this->objDAO->getById($this, $debug);
    }

    public function carregarOficinasInscritiveisAgenda($debug = false) {
        return $this->objDAO->carregarOficinasInscritiveisAgenda($this, $debug);
    }

    // public function carregarListaOficinas($orderBy = '', $debug = false) {
    //     return $this->carregarListaOficina($orderBy,$debug);
    // }
    public function carregarListaOficina($orderBy = '', $debug = false) {
        $this->objDAO->carregarListaOficinaByIdEvento($this, $orderBy, $debug);
        if ($this->getListaOficina()) {
        } else {
            throw new Exception("Evento não possui Oficina cadastrada.");
        }
    }
    public function carregarListaOficinaSemCache($orderBy = '', $debug = false) {
        $this->objDAO->carregarListaOficinaSemCacheByIdEvento($this, $orderBy, $debug);
        if ($this->getListaOficina()) {
        } else {
            throw new Exception("Evento não possui Oficina cadastrada.");
        }
    }

    public function getEvento() {
        $obj = new InscEvento();
        $obj->setCodEvento($this->getCodEvento());
        $obj->getEventoById();
        return $obj;
    }
    
    public function duplicar($debug = false) {
        $this->validatorExcluir();
        $codOficinaAnt = $this->getCodOficina();
        if ($this->objDAO->duplicar($this, $debug)) {
            throw new OKException("Oficina cód: ".$codOficinaAnt." duplicada para ". $this->getCodOficina() . " foi duplicada com sucesso.");
        } else {
            throw new Exception("Oficina cód:" . $codOficinaAnt . " não foi duplicada.");
        }
    }
    
    public function ImportarOficinas($debug = false) {
        if ($this->objDAO->importarOficinas($this, $debug)) {
            throw new OKException("Atividades do projeto: ".$this->getCodEventoAnt()." importados para ". $this->getCodEvento() . " com sucesso.");
        } else {
            throw new Exception("Atividades do projeto: ".$this->getCodEventoAnt()." não foi importados.");
        }
    }
}

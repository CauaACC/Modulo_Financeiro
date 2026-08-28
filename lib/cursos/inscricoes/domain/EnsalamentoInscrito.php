<?php

class EnsalamentoInscrito extends EnsalamentoInscritoVO {

    private $objDAO;

    function __construct() {
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new EnsalamentoInscritoDAO();
    }

    /**
     * Upsert: 1 ensalamento por inscrito/evento (UNIQUE cod_evento+cod_inscrito).
     * Se ja existe, atualiza sala/bloco/dia/hora/obs; senao insere.
     */
    public function salvar($debug = false){
        $this->validarSalvar();

        // Preserva os valores novos: getByInscrito faz setAll da linha existente (para nao
        // perder dat_incl/res_incl/versao no update), o que sobrescreveria estes campos.
        $novoSala  = $this->getSala();
        $novoBloco = $this->getBloco();
        $novoDia   = $this->getDia();
        $novoHora  = $this->getHora();
        $novoObs   = $this->getObs();

        if($this->objDAO->getByInscrito($this, $debug)){
            $this->setSala($novoSala);
            $this->setBloco($novoBloco);
            $this->setDia($novoDia);
            $this->setHora($novoHora);
            $this->setObs($novoObs);
            if($this->objDAO->update($this, $debug)){
                throw new OKException("Ensalamento atualizado para o inscrito " . $this->getCodInscrito() . ".");
            }
            throw new Exception("Não foi possível atualizar o ensalamento do inscrito " . $this->getCodInscrito() . ".");
        }else{
            if($this->objDAO->insert($this, $debug)){
                return true;
            }
            throw new Exception("Não foi possível gravar o ensalamento do inscrito " . $this->getCodInscrito() . ".");
        }
    }

    public function carregarListaByEvento($debug = false){
        $this->objDAO->carregarListaByEvento($this, $debug);
        return $this->getListaEnsalamentoInscrito();
    }
}
?>

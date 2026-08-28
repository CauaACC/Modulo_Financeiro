<?php

class Pessoa extends PessoaVO implements EntityInterface {

    private $objDAO;

    public function Pessoa() {
        $this->objDAO = new PessoaDAO();
    }

    public function carregaObjById($debug = false) {
        $this->objDAO->getById($this, $debug);
        return true;
    }

    public function carregarObjById($debug = false) {
        $this->objDAO->getById($this, $debug);
        return true;
    }

    public function carregarObjByCpf($debug = false) {
        $this->objDAO->getByCpf($this, $debug);
        return true;
    }

    public function carregarObjByEmail($debug = false) {
        $this->objDAO->getByEmail($this, $debug);
        return true;
    }

    public function carregarObjSalaByIdPessoa($debug = false) {
        $obj = new Sala();
        $obj->setCodPessoa($this->getCodPessoa());
        $obj->carregarObjSalaByIdPessoa($debug);
        $this->setObjSala($obj);
    }

    public function carregarObjApresentadorById($debug = false) {
        $obj = new Apresentador();
        if($this->getCodPessoa()){
            $obj->setCodParticipante($this->getCodPessoa());
            $obj->carregarObjById($debug);
        }
        $this->setObjApresentador($obj);
    }
    public function carregarObjOuvinteById($debug = false) {
        $obj = new Ouvinte();
        if($this->getCodPessoa()){
            $obj->setCodParticipante($this->getCodPessoa());
            $obj->carregarObjById($debug);
        }
        $this->setObjOuvinte($obj);
    }
    public function carregarObjTecnicoById($debug = false) {
        $obj = new Tecnico();
        if($this->getCodPessoa()){
            $obj->setCodTecnico($this->getCodPessoa());
            $obj->carregarObjById($debug);
        }
        $this->setObjTecnico($obj);
    }

    public function carregaObjInstituicaoById($debug = false) {
        $obj = new Instituicao();
        $obj->setCodInstituicao($this->getCodInstituicao());
        $obj->carregaObjById($debug);
        $this->setObjInstituicao($obj);
        return true;
    }

    public function carregarListaPessoa($debug = false) {
        $this->objDAO->getAll($this, $debug);
    }

    public function excluir($debug = false) {
        $this->verificaExisteById($debug);
        if ($this->objDAO->delete($this, $debug)) {
            throw new OKException("Solicitação foi removida com sucesso.");
        } else {
            throw new Exception("Solicitação atual não foi removida.");
        }
    }

    public function desativar($debug = false) {
        $this->verificaExisteById($debug);
        if ($this->objDAO->desativar($this, $debug)) {
            throw new OKException("Solicitação foi desativada com sucesso.");
        } else {
            throw new Exception("Solicitação atual não foi desativada.");
        }
    }

    public function salvar($debug = false) {
        if ($this->getCodPessoa() == '') {
            echo($debug ? "INSERINDO REGISTRO" : "");
            if ($this->getSenha() == '') {
                $this->setSenha(rand(10000, 99999));
            }
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Solicitação salva com sucesso.");
            } else {
                throw new Exception("Nova solicitação não foi salva.");
            }
        } else {
            $this->verificaExisteById($debug);
            echo($debug ? "ATUALIZANDO REGISTRO" : "");
            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Solicitação foi atualizada com sucesso.");
            } else {
                throw new Exception("Solicitação atual não foi atualizada.");
            }
        }
    }

    public function verificaExisteById($debug = false) {
        $this->validarObj($debug);
        if (!$this->objDAO->getExisteById($this)) {
            throw new Exception("Nenhum registro foi encontrada para o código informado.");
        }
    }

    public function getListaPessoaNomeEmailCpf($debug = false) {
        $this->validarObjBuscar($debug);
        $this->objDAO->carregarListaPessoaNomeEmailCpf($this, $debug);
    }

    public function getListaPessoaRefeicaoFiltro($fws_, $debug = false) {
        $this->objDAO->carregarListaPessoaRefeicaoFiltro($this, $fws_, $debug);
    }

    public function getListaPessoaRefeicaoGroupBy($fws_, $debug = false) {
        $this->objDAO->carregarListaPessoaRefeicaoGroupBy($this, $fws_, $debug);
    }

    public function getListaEstatistica($debug = false) {
        $this->objDAO->carregarListaEstatistica($this, $debug);
    }

    public function getListaEnsalamentoVisualizarImpressao($fws_, $debug = false) {
        $this->objDAO->carregarListaEnsalamentoVisualizarImpressao($this, $fws_, $debug);
    }

    public function getListaAlunosAprovados($fws_, $debug = false) {
        $this->objDAO->carregarListaAlunosAprovados($this, $fws_, $debug);
    }

    public function getListaFuncaoByIdPessoa($fws_, $debug = false) {
        $this->objDAO->carregarListaFuncao($this, $debug);
    }

}

?>
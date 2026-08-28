<?php

class PessoaVO extends PadraoVO implements ValidatorInterface {

    private $codPessoa;
    private $nome;
    private $email;
    private $cpf;
    private $rg;
    private $senha;
    private $dat_ini;
    private $dat_alter;

    public function getCodPessoa() {
        return $this->codPessoa;
    }

    public function setCodPessoa($codPessoa) {
        $this->codPessoa = $codPessoa;
    }

    public function getNome() {
        return $this->nome;
    }

    public function setNome($nome) {
        $this->nome = $nome;
    }

    public function getEmail() {
        return $this->email;
    }

    public function setEmail($email) {
        $this->email = $email;
    }

    public function getCpf() {
        return $this->cpf;
    }

    public function setCpf($cpf) {
        $cpf = str_replace("-", "", $cpf);
        $cpf = str_replace(".", "", $cpf);
        $this->cpf = $cpf;
    }

    public function getRg() {
        return $this->rg;
    }

    public function setRg($rg) {
        $this->rg = $rg;
    }

    public function getSenha() {
        return $this->senha;
    }

    public function setSenha($senha) {
        $this->senha = $senha;
    }

    public function getDat_ini() {
        return $this->dat_ini;
    }

    public function setDat_ini($dat_ini) {
        $this->dat_ini = $dat_ini;
    }

    public function getDat_alter() {
        return $this->dat_alter;
    }

    public function setDat_alter($dat_alter) {
        $this->dat_alter = $dat_alter;
    }

            
    public function validarObj($debug = false) {
        $message = "";
        $return = true;
        if ((is_null($this->getNome()) or $this->getNome() == "") and ( is_null($this->getNome()) or $this->getNome() == "")) {
            $message .= "Nome não foi informado.<br>";
            $return = false;
        }
        if ((is_null($this->getCpf()) or $this->getCpf() == "") and ( is_null($this->getCpf()) or $this->getCpf() == "")) {
            $message .= "CPF não foi informado.<br>";
            $return = false;
        }
        if ($return == false) {
            throw new Exception($message);
            return false;
        }
        return true;
    }

    public function validarObjSalvar($debug = false) {
        $this->validarObj($debug);
    }
    
    public function validarObjBuscar($debug = false) {
        $message = "";
        $return = true;
        if ((is_null($this->getNome()) or $this->getNome() == "") 
                and (is_null($this->getCpf()) or $this->getCpf() == "") 
                and (is_null($this->getEmail()) or $this->getEmail() == "")
                and (is_null($this->getCodPessoa()) or $this->getCodPessoa() == "")
           ) {
            $message .= "Pelo menos um campo deve ser preenchido.";
            $return = false;
        }
        if ($return == false) {
            throw new Exception($message);
            return false;
        }
        return true;
    }

    public function validarObjExcluir($debug = false) {
        
    }

    public function verificarCodParticipante() {
        if ($this->getCodParticipante() != NULL) {
            return true;
        }
        else
            return false;
    }

    public function verificarCodOuvinte() {
        if ($this->getCodOuvinte() != NULL) {
            return true;
        }
        else
            return false;
    }

    public function verificarCodProfessor() {
        if ($this->getCodProfessor() != NULL) {
            return true;
        }
        else
            return false;
    }

    public function verificarCodTecnico() {
        if ($this->getCodTecnico() != NULL) {
            return true;
        }
        else
            return false;
    }

    public function verificarCpfPessoaProjeto() {
        if ($this->getCpfPessoaProjeto() != NULL) {
            return true;
        }
        else
            return false;
    }
}

?>
<?php

class AvaliacaoPESProfessorVO extends PadraoVO {

    public function validatorObj() {
        $msg = "";
        if (!$this->getCodAgente()) $msg .= "PES nao informado.\n";
        if (!$this->getNomeProfessor()) $msg .= "Nome do professor nao informado.\n";
        if (!$this->getCiclo()) $msg .= "Ciclo nao informado.\n";
        if (!$this->getDataAvaliacao()) $msg .= "Data da avaliacao nao informada.\n";
        if ($msg) throw new Exception($msg);
    }

    public function validatorExcluir() {
        if (!$this->getCodAvaliacao()) {
            throw new Exception("Codigo da avaliacao nao informado.");
        }
    }
}

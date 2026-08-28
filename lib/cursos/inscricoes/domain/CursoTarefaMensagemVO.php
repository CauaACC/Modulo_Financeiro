<?php

class CursoTarefaMensagemVO extends PadraoVO {

    public function validatorSalvar() {
        $message = "";
        if (!$this->getCodEvento())    $message .= "Evento não informado.<br>";
        if (!$this->getCodCursoItem()) $message .= "Tarefa não informada.<br>";
        if (!$this->getCodInscrito())  $message .= "Aluno não informado.<br>";
        if (!in_array($this->getAutorTipo(), array('ALUNO', 'PROFESSOR'))) {
            $message .= "Tipo de autor inválido.<br>";
        }
        if (!trim((string) $this->getTexto())) $message .= "Mensagem vazia.<br>";
        if ($this->getAutorTipo() === 'ALUNO' && !$this->getCodAutorInscrito()) {
            $message .= "Autor aluno sem identificação.<br>";
        }
        if ($this->getAutorTipo() === 'PROFESSOR' && !$this->getCodAutorUsuario()) {
            $message .= "Autor professor sem identificação.<br>";
        }
        if ($message) throw new Exception($message);
        return true;
    }
}

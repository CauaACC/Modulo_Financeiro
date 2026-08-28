<?php

class CursoTarefaMensagem extends CursoTarefaMensagemVO {

    private $objDAO;

    function __construct() {
        $this->objDAO = new CursoTarefaMensagemDAO();
    }

    public function enviar($debug = false) {
        $this->validatorSalvar();
        if (!$this->getDatIncl()) $this->setDatIncl(date('Y-m-d H:i:s'));
        if (!$this->getStatus())  $this->setStatus('ATIVADO');
        return $this->objDAO->insert($this, $debug);
    }

    /** Thread completa de um aluno numa tarefa (ordem cronológica). */
    public function carregarThread($codCursoItem, $codInscrito, $debug = false) {
        return $this->objDAO->carregarThread($codCursoItem, $codInscrito, $debug);
    }

    /** $paraQuem = 'ALUNO' (não lidas pelo aluno) | 'PROFESSOR' (não lidas pelo professor). */
    public function contarNaoLidas($codCursoItem, $codInscrito, $paraQuem, $debug = false) {
        return $this->objDAO->contarNaoLidas($codCursoItem, $codInscrito, $paraQuem, $debug);
    }

    public function marcarLidas($codCursoItem, $codInscrito, $paraQuem, $debug = false) {
        return $this->objDAO->marcarLidas($codCursoItem, $codInscrito, $paraQuem, $debug);
    }

    /** Caixa do professor: alunos com thread nessa tarefa + contagem de não lidas. */
    public function alunosComThread($codCursoItem, $debug = false) {
        return $this->objDAO->alunosComThread($codCursoItem, $debug);
    }
}

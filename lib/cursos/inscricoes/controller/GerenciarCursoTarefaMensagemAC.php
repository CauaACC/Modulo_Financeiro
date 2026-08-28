<?php

/**
 * Mensagens privadas aluno<->professor presas a uma TAREFA do Curso EAD.
 * Rota: CommandAC  ->  c=GerenciarCursoTarefaMensagem & m=(enviar|listarThread|listarAlunosComThread)
 *
 * Autorizacao:
 *  - ALUNO   (_cod_inscrito): so a PROPRIA thread (a thread e' sempre a dele).
 *  - PROFESSOR (FWA_ehProfessorEAD): qualquer thread; informa de qual aluno via codInscrito.
 */
class GerenciarCursoTarefaMensagemAC {

    function __construct() { }

    private function getCodInscritoLogado() {
        return !empty($_SESSION['_cod_inscrito']) ? (int) $_SESSION['_cod_inscrito'] : 0;
    }

    /**
     * Item de tarefa tem de ser DESTE evento -- o professor de um evento nao
     * abre a conversa de um item de outro. (Para o aluno o gate e' mais estreito
     * ainda: FWA_exigirCursoItemDoInscrito.)
     */
    private function exigirItemDoEvento($codCursoItem, $debug = false) {
        $codCursoItem = (int) $codCursoItem;
        if (!$codCursoItem) throw new Exception("Tarefa não informada.");
        $item = new CursoItem();
        $item->setCodCursoItem($codCursoItem);
        if (!$item->getCursoItemById($debug)) {
            throw new Exception("Tarefa não encontrada.");
        }
        return $codCursoItem;
    }

    /** Aluno manda pro professor, ou professor responde pro aluno. */
    public function enviar($fws_, $debug = false) {
        $ok = false;
        $message = '';
        try {
            $texto = isset($fws_['texto']) ? trim((string) $fws_['texto']) : '';
            if ($texto === '') throw new Exception("Mensagem vazia.");

            $codInscritoLogado = $this->getCodInscritoLogado();
            // Quem nao e' aluno tem de ser professor ANTES de qualquer consulta
            // ao item -- senao a mensagem de erro ja diria se o item existe.
            if (!$codInscritoLogado && !FWA_ehProfessorEAD()) throw new Exception("Não autenticado.");
            $codCursoItem = $codInscritoLogado
                ? FWA_exigirCursoItemDoInscrito($fws_['codCursoItem'], $debug)
                : $this->exigirItemDoEvento($fws_['codCursoItem'], $debug);

            $obj = new CursoTarefaMensagem();
            $obj->setCodEvento(COD_EVENTO);
            $obj->setCodCursoItem($codCursoItem);
            $obj->setTexto($texto);
            $obj->setIp($_SERVER['REMOTE_ADDR']);

            if ($codInscritoLogado) {
                // Aluno: a thread e' a dele.
                $obj->setCodInscrito($codInscritoLogado);
                $obj->setAutorTipo('ALUNO');
                $obj->setCodAutorInscrito($codInscritoLogado);
            } elseif (FWA_ehProfessorEAD()) {
                // Professor: precisa dizer de qual aluno e' a conversa.
                $codInscritoAlvo = isset($fws_['codInscrito']) ? (int) $fws_['codInscrito'] : 0;
                if (!$codInscritoAlvo) throw new Exception("Aluno da conversa não informado.");
                $obj->setCodInscrito($codInscritoAlvo);
                $obj->setAutorTipo('PROFESSOR');
                $obj->setCodAutorUsuario((int) $_SESSION['_cod_usuario']);
            } else {
                throw new Exception("Não autenticado.");
            }

            if (!$obj->enviar($debug)) throw new Exception("Não foi possível enviar a mensagem.");
            $ok = true;
            $message = "Mensagem enviada.";
        } catch (Exception $e) {
            $ok = false;
            $message = $e->getMessage();
        }
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(array('statusMessage' => $ok, 'message' => tratarJson($message)));
    }

    /** Devolve a thread e ja marca como lidas as mensagens do outro lado. */
    public function listarThread($fws_, $debug = false) {
        $ok = false;
        $mensagens = array();
        $message = '';
        try {
            $obj = new CursoTarefaMensagem();

            $codInscritoLogado = $this->getCodInscritoLogado();
            if (!$codInscritoLogado && !FWA_ehProfessorEAD()) throw new Exception("Não autenticado.");
            $codCursoItem = $codInscritoLogado
                ? FWA_exigirCursoItemDoInscrito($fws_['codCursoItem'], $debug)
                : $this->exigirItemDoEvento($fws_['codCursoItem'], $debug);

            if ($codInscritoLogado) {
                $codInscrito = $codInscritoLogado;   // aluno: sempre a propria thread
                $paraQuem = 'ALUNO';
            } elseif (FWA_ehProfessorEAD()) {
                $codInscrito = isset($fws_['codInscrito']) ? (int) $fws_['codInscrito'] : 0;
                if (!$codInscrito) throw new Exception("Aluno da conversa não informado.");
                $paraQuem = 'PROFESSOR';
            } else {
                throw new Exception("Não autenticado.");
            }

            $mensagens = $obj->carregarThread($codCursoItem, $codInscrito, $debug);
            $obj->marcarLidas($codCursoItem, $codInscrito, $paraQuem, $debug);
            $ok = true;
        } catch (Exception $e) {
            $ok = false;
            $message = $e->getMessage();
        }
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(array('statusMessage' => $ok, 'mensagens' => $mensagens, 'message' => tratarJson($message)));
    }

    /** Caixa do professor: alunos que tem thread nessa tarefa (+ nao lidas pelo professor). */
    public function listarAlunosComThread($fws_, $debug = false) {
        $ok = false;
        $alunos = array();
        $message = '';
        try {
            if (!FWA_ehProfessorEAD()) throw new Exception("Sem permissão.");
            $codCursoItem = $this->exigirItemDoEvento($fws_['codCursoItem'], $debug);
            $obj = new CursoTarefaMensagem();
            $alunos = $obj->alunosComThread($codCursoItem, $debug);
            $ok = true;
        } catch (Exception $e) {
            $ok = false;
            $message = $e->getMessage();
        }
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(array('statusMessage' => $ok, 'alunos' => $alunos, 'message' => tratarJson($message)));
    }
}

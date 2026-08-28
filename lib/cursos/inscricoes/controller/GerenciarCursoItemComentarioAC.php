<?php

class GerenciarCursoItemComentarioAC {

    function __construct() { }

    private function getAutor() {
        $autor = array('cod_inscrito' => null, 'cod_usuario' => null, 'nome' => '', 'tipo' => '');
        if (!empty($_SESSION['_cod_inscrito'])) {
            $autor['cod_inscrito'] = (int) $_SESSION['_cod_inscrito'];
            $autor['nome'] = !empty($_SESSION['_nome_inscrito']) ? $_SESSION['_nome_inscrito'] : 'Inscrito';
            $autor['tipo'] = 'ALUNO';
        } elseif (!empty($_SESSION['_cod_usuario'])) {
            $autor['cod_usuario'] = (int) $_SESSION['_cod_usuario'];
            $autor['nome'] = !empty($_SESSION['_nome_usuario']) ? $_SESSION['_nome_usuario'] : 'Admin';
            $autor['tipo'] = 'ADMIN';
        }
        return $autor;
    }

    /**
     * O item pedido esta ao alcance de quem esta chamando?
     *  - ALUNO: so item de curso a que ele tem acesso, neste evento.
     *  - ADMIN: precisa de papel do EAD (coordenador/professor/ADM_GERAL) e o
     *    item tem de ser deste evento -- antes, qualquer usuario logado lia e
     *    escrevia comentario de item de qualquer evento.
     * Devolve o codigo validado.
     */
    private function exigirEscopoItem($autor, $codCursoItem, $debug = false) {
        $codCursoItem = (int) $codCursoItem;
        if (!$codCursoItem) throw new Exception("Item não informado.");

        if (!empty($autor['cod_inscrito'])) {
            return FWA_exigirCursoItemDoInscrito($codCursoItem, $debug);
        }

        FWA_exigirPermissao(FWA_permissoesAcompanharEAD());
        $item = new CursoItem();
        $item->setCodCursoItem($codCursoItem);
        if (!$item->getCursoItemById($debug)) {
            throw new Exception("Item do curso não encontrado.");
        }
        return $codCursoItem;
    }

    public function comentar($fws_, $debug = false) {
        $ok = false;
        $message = '';
        try {
            $autor = $this->getAutor();
            if (!$autor['cod_inscrito'] && !$autor['cod_usuario']) {
                throw new Exception("Não autenticado.");
            }
            $codCursoItem = $this->exigirEscopoItem($autor, $fws_['codCursoItem'], $debug);

            $obj = new CursoItemComentario();
            $obj->setCodCursoItem($codCursoItem);
            $obj->setCodPai(!empty($fws_['codPai']) ? (int) $fws_['codPai'] : null);
            $obj->setCodInscrito($autor['cod_inscrito']);
            $obj->setCodUsuario($autor['cod_usuario']);
            $obj->setTexto(trim((string) $fws_['texto']));
            $obj->salvar($debug);
            $ok = true;
            $message = "Comentário enviado.";
        } catch (Exception $e) {
            $ok = false;
            $message = $e->getMessage();
        }
        echo json_encode(array(
            'statusMessage' => $ok,
            'message'       => tratarJson($message),
        ));
    }

    public function listar($fws_, $debug = false) {
        // Area de assistir: exige inscrito logado (ou admin com papel do EAD,
        // p/ preview/moderacao) E que o item esteja ao alcance dele.
        try {
            $autor = $this->getAutor();
            if (!$autor['cod_inscrito'] && !$autor['cod_usuario']) {
                throw new Exception("Não autenticado.");
            }
            $codCursoItem = $this->exigirEscopoItem($autor, $fws_['codCursoItem'], $debug);
            $obj = new CursoItemComentario();
            $linhas = $obj->carregarPorItem($codCursoItem, $debug);
            echo json_encode(array('statusMessage' => true, 'comentarios' => $linhas));
        } catch (Exception $e) {
            echo json_encode(array('statusMessage' => false, 'message' => tratarJson($e->getMessage())));
        }
    }

    public function excluir($fws_, $debug = false) {
        $ok = false;
        $message = '';
        try {
            $autor = $this->getAutor();
            if (!$autor['cod_inscrito'] && !$autor['cod_usuario']) {
                throw new Exception("Não autenticado.");
            }
            $codComentario = (int) $fws_['codComentario'];
            if (!$codComentario) throw new Exception("Comentário não informado.");

            $obj = new CursoItemComentario();
            $dono = $obj->carregarById($codComentario, $debug);
            if (!$dono) throw new Exception("Comentário não encontrado.");

            // Moderacao e' montagem do curso: coordenador do EAD ou ADM_GERAL
            // removem qualquer comentario; o inscrito, so o proprio.
            $ehAdmin = !empty($autor['cod_usuario'])
                && FWA_temPermissao(FWA_permissoesCoordenarEAD());
            $ehDono  = !empty($autor['cod_inscrito'])
                && (int) $dono['cod_inscrito'] === (int) $autor['cod_inscrito'];
            if (!$ehAdmin && !$ehDono) {
                throw new Exception("Sem permissão para remover este comentário.");
            }

            $obj->setCodComentario($codComentario);
            $obj->excluir($debug);
            $ok = true;
            $message = "Comentário removido.";
        } catch (Exception $e) {
            $ok = false;
            $message = $e->getMessage();
        }
        echo json_encode(array('statusMessage' => $ok, 'message' => tratarJson($message)));
    }
}

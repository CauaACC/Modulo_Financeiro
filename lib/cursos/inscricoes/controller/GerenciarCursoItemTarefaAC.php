<?php

class GerenciarCursoItemTarefaAC {

    private $varDiretorio;

    function __construct() {
        $this->varDiretorio = defined('WB_PATH_LOG_TRABALHO') ? WB_PATH_LOG_TRABALHO : (WB_ROOT . 'logn/eventos/trabalhos/');
    }

    private function getCodInscritoLogado() {
        return !empty($_SESSION['_cod_inscrito']) ? (int) $_SESSION['_cod_inscrito'] : 0;
    }

    public function salvarTarefa($fws_, $debug = false) {
        $ok = false;
        $message = '';
        $arquivoSalvo = '';
        try {
            $codInscrito = $this->getCodInscritoLogado();
            if (!$codInscrito) throw new Exception("Inscrito não autenticado.");
            // Antes de tocar no upload: item tem de ser de um curso deste
            // inscrito, neste evento. Senao o arquivo iria para o disco mesmo
            // com a gravacao recusada depois.
            $codCursoItem = FWA_exigirCursoItemDoInscrito($fws_['codCursoItem'], $debug);

            if (empty($_FILES['arquivo']) || $_FILES['arquivo']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception("Erro no upload do arquivo.");
            }
            $tmp = $_FILES['arquivo']['tmp_name'];
            $nomeOriginal = $_FILES['arquivo']['name'];
            $tamanho = (int) $_FILES['arquivo']['size'];

            $ext = strtolower(pathinfo($nomeOriginal, PATHINFO_EXTENSION));
            $permitidas = array('pdf','doc','docx','xls','xlsx','jpg','jpeg','png','txt','rtf','odt');
            if (!in_array($ext, $permitidas)) {
                throw new Exception("Extensão não permitida. Aceitas: " . implode(', ', $permitidas));
            }

            // Diretório de destino
            $dir = rtrim($this->varDiretorio, '/') . '/curso_tarefa/' . COD_EVENTO . '/';
            if (!is_dir($dir)) @mkdir($dir, 0775, true);

            $nomeSalvo = 'tarefa_' . $codCursoItem . '_' . $codInscrito . '_' . time() . '.' . $ext;
            $destino = $dir . $nomeSalvo;
            if (!move_uploaded_file($tmp, $destino)) {
                throw new Exception("Falha ao mover arquivo.");
            }

            // Persistir
            $obj = new CursoItemTarefa();
            $obj->setCodCursoItem($codCursoItem);
            $obj->setCodInscrito($codInscrito);
            $obj->setArquivoOriginal($nomeOriginal);
            $obj->setArquivoSalvo($nomeSalvo);
            $obj->setTamanhoBytes($tamanho);
            $obj->setStatus('ENVIADO');
            $obj->salvar($debug);
            $arquivoSalvo = $nomeSalvo;

            // Marca conclusão automática
            $concl = new CursoItemConclusao();
            $concl->setCodCursoItem($codCursoItem);
            $concl->setCodInscrito($codInscrito);
            $concl->setOrigem('AUTOMATICA');
            $concl->setTempoEfetivo(0);
            $concl->registrar($debug);

            $ok = true;
            $message = "Tarefa enviada com sucesso (versão " . $obj->getVersao() . ").";
        } catch (Exception $e) {
            $ok = false;
            $message = $e->getMessage();
        }
        echo json_encode(array(
            'statusMessage' => $ok,
            'message'       => tratarJson($message),
            'arquivo'       => $arquivoSalvo,
        ));
    }

    public function carregarMinhaTarefa($fws_, $debug = false) {
        $ok = false;
        $tarefa = null;
        try {
            $codInscrito = $this->getCodInscritoLogado();
            if (!$codInscrito) throw new Exception("Inscrito não autenticado.");
            $codCursoItem = FWA_exigirCursoItemDoInscrito($fws_['codCursoItem'], $debug);
            $obj = new CursoItemTarefa();
            $obj->setCodCursoItem($codCursoItem);
            $obj->setCodInscrito($codInscrito);
            $tarefa = $obj->getPorItemInscrito($debug);
            $ok = true;
        } catch (Exception $e) {
            $ok = false;
        }
        echo json_encode(array('statusMessage' => $ok, 'tarefa' => $tarefa));
    }
}

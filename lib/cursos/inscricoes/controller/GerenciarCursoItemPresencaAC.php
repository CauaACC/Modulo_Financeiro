<?php

class GerenciarCursoItemPresencaAC {

    function __construct() { }

    private function getCodInscritoLogado() {
        if (!empty($_SESSION['_cod_inscrito'])) {
            return (int) $_SESSION['_cod_inscrito'];
        }
        return 0;
    }

    public function registrarEvento($fws_, $debug = false) {
        $ok = false;
        $message = '';
        try {
            $codInscrito = $this->getCodInscritoLogado();
            if (!$codInscrito) {
                throw new Exception("Inscrito não autenticado.");
            }

            // O inscrito so registra presenca em item de curso a que ele tem
            // acesso, dentro do evento em que esta inscrito.
            $codCursoItem = FWA_exigirCursoItemDoInscrito($fws_['codCursoItem'], $debug);
            $tipo = (string) $fws_['tipoEvento'];
            $codSessao = isset($fws_['codSessao']) ? (string) $fws_['codSessao'] : '';
            $tempoAcumulado = isset($fws_['tempoAcumulado']) ? (int) $fws_['tempoAcumulado'] : 0;

            $item = new CursoItem();
            $item->setCodCursoItem($codCursoItem);
            // getCursoItemById devolve false quando o item nao existe NESTE
            // evento -- testar o retorno, e nao getCodCursoItem(), que continua
            // preenchido porque foi setado acima.
            if (!$item->getCursoItemById($debug)) {
                throw new Exception("Item do curso não encontrado.");
            }

            // Janela horária — server-side
            if (in_array($tipo, array('INICIO','PING','CONFIRMACAO_OK','RETOMADA'))
                && !$item->estaDentroDaJanela()) {
                $tipo = 'FORA_HORARIO';
            }

            $obj = new CursoItemPresenca();
            $obj->setCodCursoItem($codCursoItem);
            $obj->setCodInscrito($codInscrito);
            $obj->setCodSessao($codSessao);
            $obj->setTipo($tipo);
            $obj->setTempoAcumulado($tempoAcumulado);
            $obj->registrar($debug);

            // Conclusão automática: ao receber FIM com tempo acumulado >= 60s
            $concluiuAgora = false;
            if ($tipo === 'FIM' && $tempoAcumulado >= 60) {
                $concl = new CursoItemConclusao();
                $concl->setCodCursoItem($codCursoItem);
                $concl->setCodInscrito($codInscrito);
                $concl->setOrigem('AUTOMATICA');
                $concl->setTempoEfetivo($tempoAcumulado);
                $concl->registrar($debug);
                $concluiuAgora = true;
            }

            $ok = true;
            $message = "Evento registrado.";
        } catch (Exception $e) {
            $ok = false;
            $message = $e->getMessage();
        }
        echo json_encode(array(
            'statusMessage'  => $ok,
            'message'        => tratarJson($message),
            'tipoFinal'      => isset($tipo) ? $tipo : null,
            'concluiuAgora'  => isset($concluiuAgora) ? $concluiuAgora : false,
        ));
    }

    public function marcarConcluidoManual($fws_, $debug = false) {
        $ok = false;
        $message = '';
        try {
            $codInscrito = $this->getCodInscritoLogado();
            if (!$codInscrito) throw new Exception("Inscrito não autenticado.");
            $codCursoItem = FWA_exigirCursoItemDoInscrito($fws_['codCursoItem'], $debug);

            $concl = new CursoItemConclusao();
            $concl->setCodCursoItem($codCursoItem);
            $concl->setCodInscrito($codInscrito);
            $concl->setOrigem('MANUAL');
            $concl->setTempoEfetivo(0);
            $concl->registrar($debug);
            $ok = true;
            $message = "Item marcado como concluído.";
        } catch (Exception $e) {
            $ok = false;
            $message = $e->getMessage();
        }
        echo json_encode(array(
            'statusMessage' => $ok,
            'message'       => tratarJson($message),
        ));
    }

    public function relatorioPorItem($fws_, $debug = false) {
        // Leitura de acompanhamento: coordenador, professor ou ADM_GERAL.
        if (!FWA_temPermissao(FWA_permissoesAcompanharEAD())) {
            echo json_encode(array('statusMessage' => false, 'message' => 'Sem permissão.'));
            return;
        }
        $codCursoItem = (int) $fws_['codCursoItem'];
        $obj = new CursoItemPresenca();
        $linhas = $obj->carregarRelatorioPorItem($codCursoItem, $debug);
        echo json_encode(array('statusMessage' => true, 'linhas' => $linhas));
    }
}

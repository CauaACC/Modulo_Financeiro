<?php

/**
 * GESTOR -> Equipe padrao.
 *
 * Define de qual evento a equipe e' herdada quando um projeto novo e' criado
 * naquele gestor (EventoDAO::herdarEquipeDoGestor).
 *
 * Ate 2026-08-20 o molde era sempre "o evento de codigo mais alto que esteja
 * ATIVADO" -- o ultimo criado, real ou de teste. Com 103 eventos ativos so no
 * HUTEC, a equipe de todo projeto novo dependia de qual foi o ultimo evento
 * que alguem criou. Aqui isso passa a ser escolhido e visivel.
 */
class GerenciarEquipePadraoGestorAC {

    function __construct() {
        /* Tela FORA do evento: verificacao GERAL, como as demais do modulo Gestor. */
        if (!FWA_AutenticacaoGeral($_SESSION['_cod_usuario'], enumSistema::SIS_GERENCIAR_ADM_GERAL, 5, false, false)) {
            throw new Exception("Usuário sem permissão para definir a equipe padrão do gestor.");
        }
    }

    /**
     * Grava o evento modelo do gestor. cod_evento vazio volta ao criterio
     * antigo (ultimo evento ativo), que e' o comportamento de quem nunca
     * definiu nada.
     */
    public function salvarModelo($fws_, $debug = false) {
        $statusExcec = 0;
        $mensagem = 'Não foi possível salvar o evento modelo.';
        try {
            $codGestor = (int) $fws_['codGestor'];
            if ($codGestor <= 0) {
                throw new Exception("Gestor não informado.");
            }
            $codEvento = isset($fws_['codEventoModelo']) ? trim($fws_['codEventoModelo']) : '';
            $codEvento = ($codEvento === '' ? null : (int) $codEvento);

            $obj = new EquipePadraoGestor();
            $obj->salvarModelo($codGestor, $codEvento, $debug);

            $mensagem = ($codEvento === null)
                ? 'Sem evento modelo: projetos novos voltam a herdar a equipe do último evento ativo.'
                : 'Evento modelo salvo. Projetos novos deste gestor nascem com a equipe dele.';
            $statusExcec = 1;
        } catch (Throwable $exc) {
            $statusExcec = 0;
            $mensagem = $exc->getMessage();
        }
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(array(
            'statusMessage' => (string) $statusExcec,
            'message'       => $mensagem
        ));
        exit();
    }

    /** Quem esta no evento informado -- para conferir antes de salvar. */
    public function listarEquipe($fws_, $debug = false) {
        $statusExcec = 0;
        $mensagem = '';
        $lista = array();
        try {
            $codEvento = (int) $fws_['codEvento'];
            if ($codEvento <= 0) {
                throw new Exception("Evento não informado.");
            }
            $obj = new EquipePadraoGestor();
            $lista = $obj->listarEquipeDoEvento($codEvento, $debug);
            $statusExcec = 1;
        } catch (Throwable $exc) {
            $statusExcec = 0;
            $mensagem = $exc->getMessage();
        }
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(array(
            'statusMessage' => (string) $statusExcec,
            'message'       => $mensagem,
            'equipe'        => $lista
        ));
        exit();
    }
}

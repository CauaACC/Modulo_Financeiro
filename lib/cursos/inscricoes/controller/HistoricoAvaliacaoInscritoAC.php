<?php

/**
 * HistoricoAvaliacaoInscritoAC
 *
 * Controller das acoes de log/historico da avaliacao de inscritos
 * (tab_trabalhos_historico_avaliacao_inscricao).
 *
 * Acoes principais:
 *   - iniciarAvaliacao()    : marca SELECIONADO ao avaliador abrir a tela
 *   - liberarReserva()      : avaliador clica pra "liberar" sua reserva
 *   - listarOutrosAvaliadoresAtivos() : lista quem mais esta com reserva
 *
 * Seguranca: __construct() ja inclui consulta-avaliacao-inscrito-seguranca.php,
 * que faz FWA_Autenticacao (avaliador OU gestor) e da exit() se nao houver
 * permissao. Todos os metodos tambem FORCAM cod_avaliador = sessao,
 * ignorando qualquer cod_avaliador que venha do form/POST.
 */
class HistoricoAvaliacaoInscritoAC
{

    public function __construct()
    {
        require_once(dirname(__FILE__) . "/../view/admin/consulta-avaliacao-inscrito-seguranca.php");
    }

    /**
     * Marca SELECIONADO para o avaliador logado em
     * (cod_evento atual, cod_inscrito, cod_etapa). Idempotente.
     * Retorna a entidade carregada (com a lista de outros avaliadores).
     */
    public function iniciarAvaliacao($fws_, $debug = false)
    {
        $codAvaliadorSessao = isset($_SESSION['_cod_usuario']) ? (int)$_SESSION['_cod_usuario'] : 0;
        if ($codAvaliadorSessao <= 0) {
            throw new Exception("Sessão inválida.");
        }

        $obj = new HistoricoAvaliacaoInscrito();
        $obj->setCodInscrito((int)($fws_['codInscrito'] ?? 0));
        $obj->setCodEtapa((int)($fws_['codEtapa'] ?? 0));
        $obj->setCodAvaliador($codAvaliadorSessao); // SEMPRE da sessao

        if ((int)$obj->getCodInscrito() <= 0 || (int)$obj->getCodEtapa() <= 0) {
            return $obj; // dados insuficientes; silenciosamente nao marca
        }

        try {
            $obj->marcarSelecionado($debug);
        } catch (Exception $e) {
            // erro de log nao deve travar a tela de avaliacao
        }
        return $obj;
    }

    /**
     * Lista outros avaliadores com reserva SELECIONADO ativa para o mesmo
     * (inscrito, etapa). Exclui o avaliador logado. Filtra reservas mais
     * antigas que RESERVA_EXPIRA_HORAS.
     */
    public function listarOutrosAvaliadoresAtivos($fws_, $debug = false)
    {
        $codAvaliadorSessao = isset($_SESSION['_cod_usuario']) ? (int)$_SESSION['_cod_usuario'] : 0;
        $obj = new HistoricoAvaliacaoInscrito();
        $obj->setCodInscrito((int)($fws_['codInscrito'] ?? 0));
        $obj->setCodEtapa((int)($fws_['codEtapa'] ?? 0));

        $todos = $obj->getAvaliadoresSelecionados($debug);
        $outros = [];
        foreach ($todos as $av) {
            if ((int)$av['cod_avaliador'] !== $codAvaliadorSessao) {
                $outros[] = $av;
            }
        }
        return $outros;
    }

    /**
     * Registra decisao do avaliador logado no historico (HOMOLOGADO/NAO HOMOLOGADO).
     * Chamado pelos controllers de salvar avaliacao (AvaliacaoInscritoAC,
     * AvaliacaoFase2AC) apos persistir o registro consolidado.
     * Idempotente. Nunca interrompe a operacao principal.
     *
     * @param int    $codInscrito
     * @param int    $codEtapa
     * @param string $classificacaoStatus  'H' ou 'I'
     */
    public function registrarDecisao($codInscrito, $codEtapa, $classificacaoStatus, $debug = false)
    {
        try {
            $codAvaliadorSessao = isset($_SESSION['_cod_usuario']) ? (int)$_SESSION['_cod_usuario'] : 0;
            if ($codAvaliadorSessao <= 0) return;
            if ((int)$codInscrito <= 0 || (int)$codEtapa <= 0) return;

            $obj = new HistoricoAvaliacaoInscrito();
            $obj->setCodInscrito((int)$codInscrito);
            $obj->setCodEtapa((int)$codEtapa);
            $obj->setCodAvaliador($codAvaliadorSessao); // SEMPRE da sessao
            $obj->registrarDecisao($classificacaoStatus, $debug);
        } catch (Exception $e) {
            // log nunca interrompe save
        }
    }

    /**
     * Libera a reserva SELECIONADO do avaliador logado.
     * Chamada via AJAX (m=liberarReserva).
     */
    public function liberarReserva($fws_, $debug = false)
    {
        $ok = false;
        $msg = '';
        try {
            $codAvaliadorSessao = isset($_SESSION['_cod_usuario']) ? (int)$_SESSION['_cod_usuario'] : 0;
            if ($codAvaliadorSessao <= 0) {
                throw new Exception("Sessão inválida.");
            }
            $obj = new HistoricoAvaliacaoInscrito();
            $obj->setCodInscrito((int)($fws_['codInscrito'] ?? 0));
            $obj->setCodEtapa((int)($fws_['codEtapa'] ?? 0));
            $obj->setCodAvaliador($codAvaliadorSessao); // SEMPRE da sessao

            if ((int)$obj->getCodInscrito() <= 0 || (int)$obj->getCodEtapa() <= 0) {
                throw new Exception("Inscrito ou etapa inválidos.");
            }
            $obj->liberarReserva($debug);
            $ok = true;
            $msg = 'Reserva liberada. Outro avaliador pode pegar agora.';
        } catch (Exception $e) {
            $msg = $e->getMessage();
        }

        echo '{"statusMessage":"' . ($ok ? '1' : '') . '","message":"' . tratarJson($msg) . '"}';
    }
}

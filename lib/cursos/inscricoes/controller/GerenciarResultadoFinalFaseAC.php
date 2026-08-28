<?php

/**
 * GerenciarResultadoFinalFaseAC
 *
 * Controller da tela "Resultado Final" — operacao admin de Consolidar Fase
 * (preenche pontos_total_final, classificacao_status_final e classificacao_final
 * em tab_trabalhos_avaliacao_inscricao para a etapa indicada).
 *
 * Acesso restrito a SIS_GERENCIAR_ADM_GERAL — checado no construtor para
 * proteger qualquer entrypoint (UI direta ou CommandAC AJAX).
 *
 * Endpoints AJAX:
 *   - validarRecursosPendentes  ->  pre-check do botao Consolidar
 *   - consolidar                ->  executa a operacao
 */
class GerenciarResultadoFinalFaseAC
{
    public function __construct()
    {
        // Bloqueia chamada por usuario nao autenticado / sem permissao.
        if (!isset($_SESSION['_cod_usuario'])
            || !FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_GERENCIAR_ADM_GERAL, 5, false, false)) {
            // Resposta amigavel para AJAX e para acesso direto.
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])
                && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                echo json_encode(array(
                    'statusMessage' => '0',
                    'message'       => 'Acesso negado. Esta operacao exige perfil de Administrador Geral.',
                ));
                exit();
            }
            echo '<div style="padding:30px; font-family:Arial; color:#a94442;">Acesso negado. Esta operacao exige perfil de Administrador Geral.</div>';
            exit();
        }
    }

    /**
     * AJAX — devolve a contagem (e a lista) de recursos pendentes de analise
     * na etapa. Usado pelo botao Consolidar para mostrar feedback antes de
     * abrir o modal de confirmacao.
     */
    public function validarRecursosPendentes($fws_, $debug = false)
    {
        $ok = false;
        $msg = '';
        $pendentes = array();
        try {
            $ordemEtapa = isset($fws_['ordemEtapa']) ? (int) $fws_['ordemEtapa'] : 0;
            if ($ordemEtapa <= 0) {
                throw new Exception('Ordem da etapa nao informada.');
            }
            $obj = new ConsolidacaoFase();
            $obj->setOrdemEtapa($ordemEtapa);
            $lista = $obj->carregarListaRecursosPendentes($debug);

            foreach ($lista as $p) {
                $pendentes[] = array(
                    'cod_inscrito' => $p->getCodInscrito(),
                    'id_publico'   => $p->getIdPublico(),
                    'nome'         => $p->getNomeInscrito(),
                );
            }
            $ok = true;
        } catch (Exception $e) {
            $msg = $e->getMessage();
        }
        echo json_encode(array(
            'statusMessage'    => $ok ? '1' : '0',
            'message'          => $msg,
            'totalPendentes'   => count($pendentes),
            'pendentes'        => $pendentes,
        ));
    }

    /**
     * AJAX — executa a consolidacao. Bloqueia se houver recursos pendentes.
     * Sempre idempotente: pode rodar varias vezes; cada execucao gera nova
     * linha de auditoria em sis_log_consolidacao_etapa.
     */
    public function consolidar($fws_, $debug = false)
    {
        $ok = false;
        $msg = '';
        $totais = array();
        try {
            $ordemEtapa = isset($fws_['ordemEtapa']) ? (int) $fws_['ordemEtapa'] : 0;
            if ($ordemEtapa <= 0) {
                throw new Exception('Ordem da etapa nao informada.');
            }
            $obj = new ConsolidacaoFase();
            $obj->setOrdemEtapa($ordemEtapa);
            $totais = $obj->consolidar($debug);
            $ok = true;
            $msg = sprintf(
                'Consolidacao concluida com sucesso. %d inscrito(s) — %d deferido(s) / %d indeferido(s).',
                $totais['total_inscritos'],
                $totais['total_deferidos'],
                $totais['total_indeferidos']
            );
        } catch (Exception $e) {
            $msg = $e->getMessage();
        }
        echo json_encode(array(
            'statusMessage' => $ok ? '1' : '0',
            'message'       => $msg,
            'totais'        => $totais,
        ));
    }
}

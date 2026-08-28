<?php

/**
 * AvaliacaoFasesAC
 *
 * Controller da tela de entrada da Avaliacao (AvaliacaoFases.php).
 *
 * Lista todas as fases (linhas em tab_avaliacao_etapa do evento) ordenadas
 * por ordem_etapa e indica o status de cada uma (Em andamento / Concluida /
 * Aguardando inicio / Inativa).
 *
 * Mapeamento ordem_etapa -> tela de avaliacao e' hardcoded (por enquanto).
 * Quando crescer, migra para uma coluna na tabela.
 */
class AvaliacaoFasesAC
{

    /**
     * Mapa ordem_etapa => tela de avaliacao da fase.
     * Ate adicionarmos uma coluna de configuracao em tab_avaliacao_etapa,
     * adicionar aqui qualquer fase nova.
     */
    private static $mapaOrdemTela = array(
        1 => 'view/admin/GerenciarAvaliacaoInscrito.php',
        2 => 'view/admin/GerenciarAvaliacaoFase2.php',
        // 3 => 'view/admin/GerenciarAvaliacaoRecursoFase2.php',  // futuro
    );

    /**
     * Carrega todas as etapas do evento atual ordenadas por ordem_etapa.
     */
    public function carregarFases($debug = false)
    {
        $obj = new Etapa();
        try {
            $obj->carregarListaEtapa(false, $debug);
            $lista = $obj->getListaEtapa();
        } catch (Exception $e) {
            $lista = array();
        }
        if (empty($lista)) {
            return array();
        }
        usort($lista, function($a, $b) {
            return (int)$a->getOrdemEtapa() - (int)$b->getOrdemEtapa();
        });
        return $lista;
    }

    /**
     * Retorna a maior ordem_etapa que esta ativa (situacao=1) no evento.
     * Usado para classificar as demais como "concluida" / "aguardando".
     */
    public function getOrdemAtivaCorrente($lista)
    {
        $maxAtiva = null;
        foreach ($lista as $etapa) {
            if ((int)$etapa->getSituacao() === Etapa::SITUACAO_EM_ANDAMENTO) {
                $ord = (int)$etapa->getOrdemEtapa();
                if ($maxAtiva === null || $ord > $maxAtiva) {
                    $maxAtiva = $ord;
                }
            }
        }
        return $maxAtiva;
    }

    /**
     * Retorna info de status da fase: ['label', 'cor', 'corBg', 'btnAtivo', 'consultavel']
     *   - btnAtivo:    botao "Avaliar" (so EM_ANDAMENTO)
     *   - consultavel: liberado pra gestor consultar read-only (CONCLUIDA tambem)
     */
    public function getStatusFase($etapa, $ordemAtivaCorrente = null)
    {
        $valor = (int)$etapa->getSituacao();
        switch ($valor) {
            case Etapa::SITUACAO_EM_ANDAMENTO:
                return array(
                    'label'       => 'Em andamento',
                    'cor'         => '#2d6a4f',
                    'corBg'       => '#e6f4ea',
                    'btnAtivo'    => true,
                    'consultavel' => true,
                );
            case Etapa::SITUACAO_CONCLUIDA:
                return array(
                    'label'       => 'Conclu&iacute;da',
                    'cor'         => '#555',
                    'corBg'       => '#f1f1f1',
                    'btnAtivo'    => false,
                    'consultavel' => true,
                );
            case Etapa::SITUACAO_AGUARDANDO:
                return array(
                    'label'       => 'Aguardando in&iacute;cio',
                    'cor'         => '#8a6d3b',
                    'corBg'       => '#fff4e5',
                    'btnAtivo'    => false,
                    'consultavel' => false,
                );
            case Etapa::SITUACAO_INATIVA:
            default:
                return array(
                    'label'       => 'Inativa',
                    'cor'         => '#888',
                    'corBg'       => '#f1f1f1',
                    'btnAtivo'    => false,
                    'consultavel' => false,
                );
        }
    }

    /**
     * Retorna o caminho da tela que avalia a fase de ordem indicada,
     * ou null se nao mapeado ainda.
     */
    public function getTelaDaFase($ordemEtapa)
    {
        $ord = (int)$ordemEtapa;
        return isset(self::$mapaOrdemTela[$ord]) ? self::$mapaOrdemTela[$ord] : null;
    }
}

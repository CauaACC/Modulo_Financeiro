<?php

/**
 * Controller do dashboard do evento (filtrado por COD_EVENTO).
 *
 * Sem gate 668 — qualquer admin com acesso ao evento pode visualizar.
 * As abas Trabalhos, Avaliacoes e Documentos so aparecem se as flags
 * correspondentes em $objEvConf estiverem ativas.
 */
class DashboardAvaliacaoAC {

    /**
     * Determina quais abas devem ser exibidas com base na configuracao do evento.
     * @param EventoConfiguracao $objEvConf
     * @return array com flags booleanas: inscritos, trabalhos, avaliacoes, documentos
     */
    public static function definirAbasAtivas($objEvConf) {
        FWA_exigirPermissao([enumSistema::SIS_GERENCIAR_ADM_GERAL, enumSistema::SIS_GERENCIAR_AVALIACAO_TRABALHOS, enumSistema::SIS_AVALIACAO_TRABALHO]);
        $avaliacaoPorCandidato = $objEvConf->getTrabAvaliacaoPor() == 'CANDIDATO';
        return [
            // Aba "Inscritos" removida — ja existe no Dashboard Geral cross-event.
            // Este dashboard e focado em avaliacao de trabalhos/inscritos do evento.
            //
            // "Trabalhos" so faz sentido para eventos com avaliacao por arquivo
            // (TrabAvaliacaoPor != CANDIDATO). Em eventos por candidato, a tab_trabalhos
            // armazena documentos do inscrito (RG, CPF, etc) e nao trabalhos cientificos,
            // entao metricas de "trabalhos por area tematica", "por formato" etc. nao
            // se aplicam.
            'trabalhos'   => ((int) $objEvConf->getTrabSubmissao()) === 1
                          && !$avaliacaoPorCandidato,
            'avaliacoes'  => ((int) $objEvConf->getTrabAvaliacao()) === 1,
            'documentos'  => ((int) $objEvConf->getApresentacaoAnexar()) === 1
                          || ((int) $objEvConf->getApresentacaoAnexarVideo()) === 1,
        ];
    }

    public static function carregarDadosInscritos($codEvento, $dias = 30, $debug = false) {
        FWA_exigirPermissao([enumSistema::SIS_GERENCIAR_ADM_GERAL, enumSistema::SIS_GERENCIAR_AVALIACAO_TRABALHOS, enumSistema::SIS_AVALIACAO_TRABALHO]);
        $dao = new DashboardAvaliacaoDAO();
        return [
            'porDia'             => $dao->getInscritosPorDia($codEvento, $dias, $debug),
            'porEstado'          => $dao->getInscritosPorEstado($codEvento, $debug),
            'porCidadePR'        => $dao->getInscritosPorCidadePR($codEvento, 20, $debug),
            'porPerfil'          => $dao->getInscritosPorPerfil($codEvento, $debug),
            'porInstituicao'     => $dao->getInscritosPorInstituicao($codEvento, 15, $debug),
            'loginsSucessoPorDia'=> $dao->getLoginsPorDia($codEvento, $dias, 1, $debug),
            'loginsFalhaPorDia'  => $dao->getLoginsPorDia($codEvento, $dias, 0, $debug),
            'totais'             => $dao->getTotaisInscritos($codEvento, $dias, $debug),
        ];
    }

    public static function carregarDadosTrabalhos($codEvento, $debug = false) {
        FWA_exigirPermissao([enumSistema::SIS_GERENCIAR_ADM_GERAL, enumSistema::SIS_GERENCIAR_AVALIACAO_TRABALHOS, enumSistema::SIS_AVALIACAO_TRABALHO]);
        $dao = new DashboardAvaliacaoDAO();
        return [
            'porDia'          => $dao->getTrabalhosPorDia($codEvento, $debug),
            'porAreaTematica' => $dao->getTrabalhosPorAreaTematica($codEvento, $debug),
            'porAreaAprovados'=> $dao->getTrabalhosPorAreaTematicaPorStatus($codEvento, 'APROVADO', $debug),
            'porAreaReprovados'=> $dao->getTrabalhosPorAreaTematicaPorStatus($codEvento, 'REPROVADO', $debug),
            'porFormato'      => $dao->getTrabalhosPorFormato($codEvento, $debug),
            'porInstituicao'  => $dao->getTrabalhosPorInstituicao($codEvento, 15, $debug),
            'porEstado'       => $dao->getTrabalhosPorEstado($codEvento, $debug),
            'aprovadosAreaFormato' => $dao->getTrabalhosAprovadosPorAreaFormato($codEvento, $debug),
            'totais'          => $dao->getTotaisTrabalhos($codEvento, $debug),
        ];
    }

    public static function carregarAtividadeRecente($codEvento, $limit = 10, $debug = false) {
        FWA_exigirPermissao([enumSistema::SIS_GERENCIAR_ADM_GERAL, enumSistema::SIS_GERENCIAR_AVALIACAO_TRABALHOS, enumSistema::SIS_AVALIACAO_TRABALHO]);
        $dao = new DashboardAvaliacaoDAO();
        return [
            'ultimasInscricoes' => $dao->getUltimasInscricoes($codEvento, $limit, $debug),
            'ultimosTrabalhos'  => $dao->getUltimosTrabalhos($codEvento, $limit, $debug),
            'ultimosArquivos'   => $dao->getUltimosArquivos($codEvento, $limit, $debug),
            'ultimaAtividade'   => $dao->getUltimaAtividade($codEvento, $debug),
        ];
    }

    public static function carregarDadosAvaliacoes($codEvento, $debug = false) {
        FWA_exigirPermissao([enumSistema::SIS_GERENCIAR_ADM_GERAL, enumSistema::SIS_GERENCIAR_AVALIACAO_TRABALHOS, enumSistema::SIS_AVALIACAO_TRABALHO]);
        $dao = new DashboardAvaliacaoDAO();
        return [
            'trabalhosPorStatus'      => $dao->getTrabalhosPorStatusAvaliacao($codEvento, $debug),
            'trabalhosSemAvaliador'   => $dao->getTotalTrabalhosSemAvaliador($codEvento, $debug),
            'topAvaliadores'          => $dao->getTopAvaliadores($codEvento, 10, $debug),
            'topAvaliadoresInscritos' => $dao->getTopAvaliadoresInscritos($codEvento, 10, $debug),
            'avaliadoresEmAndamento'  => $dao->getAvaliadoresEmAndamento($codEvento, $debug),
            'progressoFases'          => $dao->getProgressoFases($codEvento, $debug),
            'estatisticasPontuacaoF2' => $dao->getEstatisticasPontuacaoFase2($codEvento, $debug),
            'histogramaPontuacaoF2'   => $dao->getHistogramaPontuacaoFase2($codEvento, $debug),
            'avaliacoesPorDia'        => $dao->getAvaliacoesInscritoPorDia($codEvento, 30, $debug),
            'totaisAvalInscrito'      => $dao->getTotaisAvaliacaoInscrito($codEvento, $debug),
            'avaliadoresFaseMomento'  => $dao->getAvaliadoresPorFaseMomento($codEvento, 20, $debug),
            'evolucaoAvaliadores'     => $dao->getEvolucaoAvaliadoresPorDia($codEvento, 30, 10, $debug),
        ];
    }
}

<?php

/**
 * ConsolidacaoFaseVO
 *
 * VO vazio — PadraoVO ja fornece set/get magico para qualquer campo.
 *
 * Esta entidade nao mapeia 1:1 com uma tabela; representa o resultado
 * da operacao de "Consolidar Fase" sobre tab_trabalhos_avaliacao_inscricao
 * (preenche pontos_total_final, classificacao_status_final, classificacao_final).
 *
 * Campos tipicamente usados:
 *   - codEvento, ordemEtapa, codEtapa
 *   - totalInscritos, totalDeferidos, totalIndeferidos, totalRecursosPendentes
 *   - dataConsolidacao, idConsolidacao, ipConsolidacao
 *   - listaConsolidada (array de ConsolidacaoFase com 1 item por inscrito)
 *   - listaRecursosPendentes (array de ConsolidacaoFase com 1 item por recurso pendente)
 */
class ConsolidacaoFaseVO extends PadraoVO
{
}

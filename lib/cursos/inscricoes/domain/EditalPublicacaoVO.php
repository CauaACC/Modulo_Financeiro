<?php

/**
 * EditalPublicacaoVO
 *
 * Linha consolidada de uma publicacao de edital (resultado de uma etapa
 * de avaliacao). Nao mapeia 1:1 com uma tabela do banco — eh um agregado
 * montado pelo DAO juntando tab_inscritos + tab_trabalhos_avaliacao_inscricao
 * + tab_avaliacao_etapa.
 *
 * Campos:
 *   - cod_inscrito, id_publico, nome_inscrito, data_nascimento, idade
 *   - pontos                 (pontos da etapa — pode ser confirmado ou pos-recurso)
 *   - classificacao_status   (H/I — habilitado/inabilitado da etapa)
 *   - avaliacao_json         (JSON da pontuacao da etapa, usado pro desempate)
 *
 * Campos calculados (preenchidos pelo helper EditalPublicacao::processarLista):
 *   - situacao               (DEFERIDO/INDEFERIDO — derivado da classificacao_status)
 *   - nivel_titulacao        (rank derivado do avaliacao_json — pro desempate)
 *   - meses_experiencia      (soma das categorias 3.x do avaliacao_json — pro desempate)
 *   - classificacao          (numero final na ordem — apenas DEFERIDOs)
 *   - seq                    (numero sequencial geral, comeca em 1)
 *
 * Pontos eh exibido como "129,00" no DOCX e "-" para INDEFERIDOs (regra do edital).
 */
class EditalPublicacaoVO extends PadraoVO
{
}

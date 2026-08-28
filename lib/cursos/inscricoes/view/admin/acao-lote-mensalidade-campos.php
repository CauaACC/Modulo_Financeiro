<?php
/**
 * Painel "Parâmetros de geração em lote" + preview.
 * Posicionado dentro de #divSelectTaxas, abaixo dos campos existentes.
 *
 * Reaproveita os campos do bloco pai:
 *   - #prefixoPedido   → prefixo da descrição (default "Mensalidade" quando vazio + qtd>1)
 *   - #referencia      → referência/mês (aceita tags {MES}, {MES_NUM}, {ANO}, {ANO2})
 *   - #datVencimento   → data do 1º vencimento (iterações somam +i meses)
 *
 * Visibilidade, chips e preview controlados por resources/js/acao-lote-mensalidade.js
 * (pareado com acao-lote-mensalidade-dica.php acima).
 */
?>
<div id="divLoteMensalidade" style="display:none; margin-top:10px; padding:10px; border:1px dashed #aaa; background:#fafafa;">
    <b>Parâmetros de geração em lote (para Mensalidade):</b><br>

    <label for="qtdParcelasGerar">Qtd. de parcelas a gerar:</label>
    <input type="number" id="qtdParcelasGerar" name="qtdParcelasGerar"
           min="1" max="60"
           value="<?php echo isset($fws_['qtdParcelasGerar']) && $fws_['qtdParcelasGerar'] !== '' ? intval($fws_['qtdParcelasGerar']) : '1' ?>"
           style="width:80px;">
    &nbsp;&nbsp;
    <label for="parcelaInicial">Nº da parcela inicial:</label>
    <input type="number" id="parcelaInicial" name="parcelaInicial"
           min="1" max="99"
           value="<?php echo isset($fws_['parcelaInicial']) && $fws_['parcelaInicial'] !== '' ? intval($fws_['parcelaInicial']) : '1' ?>"
           style="width:80px;">
    &nbsp;&nbsp;
    <label for="totalParcelas">Total de parcelas (denominador):</label>
    <input type="number" id="totalParcelas" name="totalParcelas"
           min="0" max="99"
           value="<?php echo isset($fws_['totalParcelas']) ? htmlspecialchars($fws_['totalParcelas'], ENT_QUOTES) : '' ?>"
           placeholder="opcional"
           style="width:90px;">
    <br>

    <div id="previewLoteMensalidade" style="margin-top:8px;padding:8px;background:#fff;border:1px solid #ddd;font-size:12px;color:#333;">
        <span style="color:#999;">Prévia aparecerá aqui ao preencher os campos...</span>
    </div>
</div>

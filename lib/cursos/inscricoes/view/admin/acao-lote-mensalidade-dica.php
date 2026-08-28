<?php
/**
 * Dica + chips de inserção de tags para a geração de mensalidades em lote.
 * Posicionada logo após o select "Tipo da Despesa/Pedido".
 * Visibilidade controlada por resources/js/acao-lote-mensalidade.js
 * (aparece só quando: acaoEmLote=gerarMensalidade + tipoPedido=mensalidade).
 */
?>
<div id="divLoteMensalidadeDica" style="display:none; margin-top:6px; margin-bottom:6px;">
    <div>
        <span style="color:#666;font-size:12px;">Inserir tag no "Mês de referência":</span>
        <button type="button" class="btn btn-default btn-xs btn-inserir-tag-referencia" data-tag="{MES}"     style="font-size:11px;padding:2px 8px;">{MES}</button>
        <button type="button" class="btn btn-default btn-xs btn-inserir-tag-referencia" data-tag="{MES_NUM}" style="font-size:11px;padding:2px 8px;">{MES_NUM}</button>
        <button type="button" class="btn btn-default btn-xs btn-inserir-tag-referencia" data-tag="{ANO}"     style="font-size:11px;padding:2px 8px;">{ANO}</button>
        <button type="button" class="btn btn-default btn-xs btn-inserir-tag-referencia" data-tag="{ANO2}"    style="font-size:11px;padding:2px 8px;">{ANO2}</button>
        <button type="button" id="btnToggleDicaLote"
                style="font-size:11px;padding:2px 8px;margin-left:8px;border:1px solid #999;border-radius:50%;background:#fff;cursor:pointer;"
                title="Mostrar ajuda sobre tags e defaults">?</button>
        <span style="color:#999;font-size:11px;margin-left:10px;">
            {MES}=Maio · {MES_NUM}=05 · {ANO}=2026 · {ANO2}=26
        </span>
    </div>
    <div id="textoDicaLoteMensalidade"
         style="display:none; margin-top:6px; padding:8px 10px; background:#fffbe6; border:1px solid #e8d98a; border-radius:3px; color:#555; font-size:12px;">
        As tags do campo <b>"Mês de referência"</b> variam por parcela conforme o vencimento:
        <br>&nbsp;&nbsp;• <code>{MES}/{ANO}</code> → <i>Maio/2026</i>, <i>Junho/2026</i>, <i>Julho/2026</i>, ...
        <br>&nbsp;&nbsp;• <code>Ref. {MES_NUM}/{ANO2}</code> → <i>Ref. 05/26</i>, <i>Ref. 06/26</i>, ...
        <br>
        Você pode editar livremente tanto o <b>"Mês de referência"</b> quanto o <b>"Prefixo"</b> — os valores preenchidos são apenas sugestões.
    </div>
</div>

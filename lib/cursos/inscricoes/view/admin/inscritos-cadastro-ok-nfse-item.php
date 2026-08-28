<?php
/**
 * Bloco de Nota Fiscal de Servico dentro da linha do PEDIDO (aba Pedidos).
 *
 * Emitir acontece onde esta o fato — o pedido. A visao de conjunto (todas as
 * notas, pendencias, cancelamentos) e assunto de tela propria, no financeiro.
 *
 * Espera $__codPedidoNfse definido pelo laco dos pedidos.
 */

if (!isset($__codPedidoNfse) || $__codPedidoNfse == '') {
    return;
}

// So quem pode emitir enxerga o bloco.
if (!isset($__podeEmitirNfse)) {
    $__podeEmitirNfse = false;
    try {
        FWA_exigirPermissao(array(enumSistema::SIS_CONTROLE_FINANCEIRO, enumSistema::SIS_GERENCIAR_ADM_GERAL));
        $__podeEmitirNfse = true;
    } catch (Exception $e) {
        $__podeEmitirNfse = false;
    }
}
if (!$__podeEmitirNfse) {
    return;
}

// Gestor que nao usa emissao automatica nao ve o botao: continua so com o
// anexo manual, que segue funcionando exatamente como antes. Sem isto o botao
// apareceria para todo mundo e so daria erro ao clicar.
if (!isset($__temEmissorNfse)) {
    $__temEmissorNfse = false;
    try {
        $objEmissorNfse = new NotaFiscalEmissor();
        $__temEmissorNfse = $objEmissorNfse->getEmissorAtivoByEvento(COD_EVENTO);
    } catch (Exception $e) {
        $__temEmissorNfse = false;
    }
}
if (!$__temEmissorNfse) {
    return;
}

/*
 * O botao "Emitir NFS-e" NAO e escrito aqui: o JS o injeta junto das demais
 * acoes do pedido (Alterar, Excluir, PDF, XML, enviar, cancelar), para tudo
 * ficar numa linha so. Renderizar no PHP faria o botao aparecer habilitado e
 * so depois desabilitar, porque o estado dele depende das notas que so o JS
 * conhece.
 *
 * Chamado DUAS vezes por pedido, via $__nfseParte:
 *   'barra' -> ancora dos botoes, dentro da barra de acoes do cartao
 *   'lista' -> ancora das notas emitidas, abaixo das acoes
 * Separar os dois evita CSS acrobatico para misturar botao e lista no flex.
 */
$__parteNfse = isset($__nfseParte) ? $__nfseParte : 'lista';

if ($__parteNfse == 'barra') {
    ?>
    <span class="nfse-barra-alvo" data-cod-pedido="<?php echo (int) $__codPedidoNfse; ?>"></span>
    <?php
} else {
    ?>
    <div class="nfse-lista" data-cod-pedido="<?php echo (int) $__codPedidoNfse; ?>"></div>
    <?php
}

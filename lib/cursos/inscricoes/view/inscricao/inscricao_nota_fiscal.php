<?php
// Botao de Nota Fiscal para UM pagamento especifico
// Esperado do escopo pai: $objPagamento, $objEvConf
// Nao renderiza se a feature estiver desabilitada no evento
if (!isset($objEvConf) || $objEvConf->getSolicitacaoNfHabilitada() != 1) {
    return;
}

$objSolicNf = new NotaFiscalSolicitacao();
$objSolicNf->setCodEvento($objPagamento->getCodEvento());
$objSolicNf->setCodPagamento($objPagamento->getCodPagamento());
$temSolicAtiva = $objSolicNf->getSolicitacaoAtivaByPagamento();

if (!$temSolicAtiva) {
    echo " &nbsp;|&nbsp; "
       . "<a href='javascript:void(0);' class='js-btn-solicitar-nf' "
       . "data-cod-pagamento='" . $objPagamento->getCodPagamento() . "'>Solicitar Nota Fiscal</a>";
} elseif (!$objSolicNf->getArquivoNf()) {
    echo " &nbsp;|&nbsp; <span style='color:#8a6d3b;'>NF solicitada em "
       . htmlspecialchars($objSolicNf->getDataInclusao()) . " - aguardando emiss&atilde;o</span>";
} else {
    $urlBaixar = WB_URL . '/' . WB_GRUPO . '/' . WB_PROJETO . '/api/nota-fiscal/baixar?cod_solicitacao=' . $objSolicNf->getCodSolicitacao();
    echo " &nbsp;|&nbsp; <a href='" . $urlBaixar . "' target='_blank'>Baixar Nota Fiscal</a>";
}

<?php
// Secao de Nota Fiscal para UM pagamento especifico - admin
// Esperado do escopo pai: $cod_evento, $cod_pagamento, $objEvConf
if (!isset($objEvConf) || $objEvConf->getSolicitacaoNfHabilitada() != 1) {
    return;
}

$objSolic = new NotaFiscalSolicitacao();
$objSolic->setCodEvento($cod_evento);
$objSolic->setCodPagamento($cod_pagamento);
$temSolicAtiva = $objSolic->getSolicitacaoAtivaByPagamento();

$objHist = new NotaFiscalSolicitacao();
$objHist->setCodEvento($cod_evento);
$objHist->setCodPagamento($cod_pagamento);
$historico = $objHist->carregarHistoricoByPagamento();
$temHistorico = (count($historico) > 1) || ($temSolicAtiva && count($historico) >= 1 && $historico[0]->getStatus() == 'DESATIVADA');
?>
<div class="nf-item" style="margin-top:10px; padding:8px; border-left:3px solid #5bc0de; background:#f6fbff;">
    <b>Nota Fiscal:</b>
    <?php if (!$temSolicAtiva): ?>
        <span style="color:#666;">Nenhuma solicita&ccedil;&atilde;o ativa.</span>
    <?php else: ?>
        <div style="margin-top:6px;">
            <b>Tomador:</b> <?php echo htmlspecialchars($objSolic->getTomadorNome()) ?>
            &nbsp;|&nbsp; CPF/CNPJ: <?php echo htmlspecialchars($objSolic->getTomadorCpfCnpj()) ?>
            <br>
            <?php if ($objSolic->getTomadorEndereco()): ?>
                Endere&ccedil;o: <?php echo htmlspecialchars($objSolic->getTomadorEndereco() . ' - ' . $objSolic->getTomadorCidade() . '/' . $objSolic->getTomadorUf() . ' - CEP ' . $objSolic->getTomadorCep()) ?><br>
            <?php endif; ?>
            <?php if ($objSolic->getTomadorEmail()): ?>
                E-mail: <?php echo htmlspecialchars($objSolic->getTomadorEmail()) ?><br>
            <?php endif; ?>
            <?php if ($objSolic->getObservacaoInscrito()): ?>
                Observa&ccedil;&atilde;o: <?php echo nl2br(htmlspecialchars($objSolic->getObservacaoInscrito())) ?><br>
            <?php endif; ?>
            Solicitada em: <?php echo htmlspecialchars($objSolic->getDataInclusao()) ?>
        </div>

        <?php if (!$objSolic->getArquivoNf()): ?>
            <div style="margin-top:8px;">
                <button type="button" class="btn btn-primary btn-sm js-btn-anexar-nf"
                        data-cod-solicitacao="<?php echo $objSolic->getCodSolicitacao() ?>">
                    Anexar NF
                </button>
                <span style="color:#8a6d3b;"> &nbsp; Aguardando anexo da Nota Fiscal</span>
            </div>
        <?php else: ?>
            <div style="margin-top:8px;">
                Anexada em <b><?php echo htmlspecialchars($objSolic->getDataAnexo()) ?></b> &nbsp;
                <a href="<?php echo WB_URL . "/" . WB_GRUPO . "/" . WB_PROJETO ?>/api/nota-fiscal/baixar?cod_solicitacao=<?php echo $objSolic->getCodSolicitacao() ?>"
                   target="_blank" class="btn btn-success btn-sm">Baixar NF</a>
                <button type="button" class="btn btn-warning btn-sm js-btn-substituir-nf"
                        data-cod-solicitacao="<?php echo $objSolic->getCodSolicitacao() ?>">
                    Substituir
                </button>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <?php if ($temHistorico): ?>
        <div style="margin-top:8px;">
            <a href="#" class="js-btn-hist-nf" data-target="#histNf<?php echo $cod_pagamento ?>" style="font-size:12px;">Ver hist&oacute;rico</a>
            <div id="histNf<?php echo $cod_pagamento ?>" style="display:none; margin-top:5px;">
                <table class="table table-bordered" style="font-size:11px; background:#fff;">
                    <thead>
                        <tr><th>#</th><th>Solicitada</th><th>Anexada</th><th>Status</th><th>Motivo</th><th>Arquivo</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($historico as $h): ?>
                        <tr>
                            <td><?php echo $h->getCodSolicitacao() ?></td>
                            <td><?php echo htmlspecialchars($h->getDataInclusao()) ?></td>
                            <td><?php echo htmlspecialchars($h->getDataAnexo()) ?></td>
                            <td><?php echo htmlspecialchars($h->getStatus()) ?></td>
                            <td><?php echo htmlspecialchars($h->getMotivoDesativacao()) ?></td>
                            <td>
                                <?php if ($h->getArquivoNf()): ?>
                                    <a href="<?php echo WB_URL . "/" . WB_GRUPO . "/" . WB_PROJETO ?>/api/nota-fiscal/baixar?cod_solicitacao=<?php echo $h->getCodSolicitacao() ?>" target="_blank">Baixar</a>
                                <?php else: ?>&mdash;<?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

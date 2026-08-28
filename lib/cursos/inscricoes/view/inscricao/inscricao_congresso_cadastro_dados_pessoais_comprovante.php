<?php
/**
 * Comprovante exigido pelo PERFIL na tela de dados pessoais (generaliza o hack do evento 122).
 * Configuracao em tab_opcoes_perfis: comprovante_exigir / comprovante_titulo /
 * comprovante_instrucao / comprovante_extensoes.
 * Espera $objPerfilComprovante (Perfil carregado) e $objInscrito definidos pela view pai.
 * O arquivo vai no mesmo POST (input name="arquivo") e e gravado em tab_trabalhos
 * com tipo_documento='COMPROVANTE_PERFIL' por GerenciarInscricaoAC::gravarComprovantePerfil().
 */
$comprovanteTitulo    = trim($objPerfilComprovante->getComprovanteTitulo()) != '' ? $objPerfilComprovante->getComprovanteTitulo() : 'Comprovante';
$comprovanteInstrucao = $objPerfilComprovante->getComprovanteInstrucao();
$comprovanteExtensoes = GerenciarInscricaoAC::extensoesComprovantePerfil($objPerfilComprovante);
$comprovanteAccept    = '.' . implode(',.', $comprovanteExtensoes);
$comprovanteAtual     = null;
if ($objInscrito->getCodInscrito()) {
    $objTrabComprovante = new Trabalho();
    $objTrabComprovante->setCodEvento(COD_EVENTO);
    $objTrabComprovante->setCodInscrito($objInscrito->getCodInscrito());
    $objTrabComprovante->setTipoDocumento(GerenciarInscricaoAC::TIPO_DOC_COMPROVANTE_PERFIL);
    $objTrabComprovante->setSlugTipoDocumento(GerenciarInscricaoAC::SLUG_DOC_COMPROVANTE_PERFIL);
    if ($objTrabComprovante->getByDocumentoUltimo($debug) && $objTrabComprovante->getArquivoGerado() != '' && $objTrabComprovante->getStatus() == 'ATIVADA') {
        $comprovanteAtual = $objTrabComprovante;
    }
}
?>
            <div class="form-group col-sm-12" style="padding-left: 0px;" id="bloco-comprovante-perfil">
                <label for="arquivo"><?php echo htmlspecialchars($comprovanteTitulo, ENT_QUOTES, 'UTF-8') ?>: <span class="text-danger">*</span></label>
                <?php if (trim($comprovanteInstrucao) != '') { ?>
                    <div class="text-muted" style="margin-bottom: 6px;"><?php echo $comprovanteInstrucao ?></div>
                <?php } ?>
                <?php if ($comprovanteAtual) {
                    $dataEnvio = $comprovanteAtual->getDataAlteracao() ? $comprovanteAtual->getDataAlteracao() : $comprovanteAtual->getDataInclusao();
                ?>
                    <div class="alert alert-success" style="padding: 8px; margin-bottom: 5px;">
                        <i class="fa fa-check-circle"></i> Comprovante enviado em <?php echo formatDateView($dataEnvio) ?>
                        (versão <?php echo (int) $comprovanteAtual->getVersao() ?>)
                        — <?php echo htmlspecialchars($comprovanteAtual->getArquivoUpload(), ENT_QUOTES, 'UTF-8') ?>
                    </div>
                    <input type="file" id="arquivo" name="arquivo" class="form-control" accept="<?php echo $comprovanteAccept ?>">
                    <small class="text-muted">Selecione um novo arquivo apenas se desejar substituir o atual. Formatos: <?php echo strtoupper(implode(', ', $comprovanteExtensoes)) ?> (máx. 20MB)</small>
                <?php } else { ?>
                    <input type="file" id="arquivo" name="arquivo" class="form-control" required accept="<?php echo $comprovanteAccept ?>">
                    <small class="text-muted">Formatos aceitos: <?php echo strtoupper(implode(', ', $comprovanteExtensoes)) ?> (máximo 20MB)</small>
                <?php } ?>
            </div>
            <div class="form-group col-sm-12" style="padding-left: 0px; margin-top: 10px;">
                <hr>
            </div>

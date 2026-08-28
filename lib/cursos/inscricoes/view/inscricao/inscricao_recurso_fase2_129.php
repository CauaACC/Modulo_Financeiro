<?php
/**
 * Solicitação/Edição de Recurso da 2ª Fase (Avaliação de Documentos) — evento 129.
 * Aceita tanto INDEFERIDO quanto DEFERIDO (este último pode contestar a pontuação,
 * por exemplo pedir aumento de nota).
 *
 * Grava em tab_trabalhos_avaliacao_inscricao.recurso_texto (linha com ordem_etapa=2)
 * via GerenciarRecursoInscritoAC::salvarRecursoFase2.
 */
require_once(dirname(__FILE__)."/../../include/config.php");
$Conexao_db = FWA_Conexao("OpcTec");
$debug = false;

$urlVoltar = WB_URL_VIEW . "?content=view/inscricao/inscricao_congresso_ok.php";

$objAval = new AvaliacaoInscrito();
$objAval->setCodInscrito($_SESSION['_cod_inscrito']);
$objAval->carregarAvaliacaoFase2ByIdInscrito($debug);

$textoAtual = (string) $objAval->getRecursoTexto();
$jaEnviou   = ($textoAtual !== '');
$statusF2   = $objAval->getClassificacaoStatus();
$pontosF2   = (float) ($objAval->getPontosTotalConfirmado() ?: 0);
$pontosTxt  = number_format($pontosF2, 2, ',', '.');
$maxCarac   = 5000;

$labelTopo = ($statusF2 === 'H')
    ? "Sua situação na 2ª Fase: <strong>DEFERIDO</strong> &middot; Pontuação: <strong>{$pontosTxt}</strong>"
    : "Sua situação na 2ª Fase: <strong>INDEFERIDO</strong>" . ($pontosF2 > 0 ? " &middot; Pontuação: <strong>{$pontosTxt}</strong>" : "");
?>
<h3>Área do Inscrito</h3>
<div class="panel-group">
    <?php require_once 'inscricao_congresso_cabecalho.php'; ?>

    <form name="form" method="POST" action="<?php echo WB_URL_COMMAND ?>">
        <input type="hidden" name="c" value="GerenciarRecursoInscrito">
        <input type="hidden" name="m" value="salvarRecursoFase2">

        <div class="panel panel-default">
            <div class="panel-heading"><h4>Apresentação de Recurso – Avaliação de Documentos (2ª Fase)</h4></div>
            <div class="panel-body">
                <?php if ($fws_['msg']) FWA_Mostrar_Mensagem($fws_['msg']); ?>

                <div style="margin:6px 0 14px;padding:8px 12px;background:#f0f7ff;border-left:4px solid #1565c0;border-radius:4px;color:#0d3a6b;font-size:14px;">
                    <?php echo $labelTopo; ?>
                </div>

                <br>
                <label>Descreva abaixo os argumentos com os quais contesta a decisão da Comissão Avaliadora sobre sua pontuação.</label>
                (máximo <?php echo $maxCarac ?> caracteres, com espaços)
                <br><font id="DigitadoRecurso" color="red">0</font> caracteres digitados &nbsp; ⇒ &nbsp;
                restam <font id="RestanteRecurso" color="red"><?php echo $maxCarac ?></font>
                <textarea id="recurso_texto" name="recurso_texto" rows="15" cols="80"
                          maxlength="<?php echo $maxCarac ?>"
                          onkeyup="MaximoCaracter(this, <?php echo $maxCarac ?>,'DigitadoRecurso','RestanteRecurso')"
                          onkeypress="MaximoCaracter(this, <?php echo $maxCarac ?>,'DigitadoRecurso','RestanteRecurso')"
                          class="form-control"
                          placeholder="Digite suas argumentações para o recurso aqui"
                          required autofocus><?php echo htmlspecialchars($textoAtual) ?></textarea>
                <br>

                <br><label>Deseja realmente solicitar recurso?</label>
                <br>
                <input type="radio" name="confirma_recurso" value="1" <?= $jaEnviou ? 'checked' : '' ?>>
                    Sim, Confirmo essa solicitação de recurso
                <br>
                <input type="radio" name="confirma_recurso" value="0" <?= $jaEnviou ? '' : 'checked' ?>>
                    Não, não quero solicitar recurso
                <br><br>

                <button class="btn btn btn-primary" type="submit">
                    <?= $jaEnviou ? 'Atualizar Pedido de Recurso' : 'Clique aqui para salvar Pedido de Recurso' ?>
                </button>
                <button class="btn btn-xs btn-primary" type="reset">Limpar</button>
                <a href="<?= $urlVoltar ?>" class="btn btn-xs btn-primary" role="button" style="color:#fff">Voltar</a>
            </div>
        </div>
    </form>
</div>
<script src="resources/js/default.js"></script>

<?php
/**
 * Solicitação/Edição de Recurso da 1ª Fase (Homologação Preliminar) — evento 129.
 * Espelha inscricao_trabalhos_cadastro_dados_trabalho_recurso.php (form normal POST),
 * mas grava em tab_trabalhos_avaliacao_inscricao.recurso_texto via
 * GerenciarRecursoInscritoAC::salvarRecursoFase1.
 */
require_once(dirname(__FILE__)."/../../include/config.php");
$Conexao_db = FWA_Conexao("OpcTec");
$debug = false;

$urlVoltar = WB_URL_VIEW . "?content=view/inscricao/inscricao_congresso_ok.php";

$objAval = new AvaliacaoInscrito();
$objAval->setCodInscrito($_SESSION['_cod_inscrito']);
$objAval->carregarAvaliacaoFase1ByIdInscrito($debug);

$textoAtual = (string) $objAval->getRecursoTexto();
$jaEnviou   = ($textoAtual !== '');
$maxCarac   = 5000;
?>
<h3>Área do Inscrito</h3>
<div class="panel-group">
    <?php require_once 'inscricao_congresso_cabecalho.php'; ?>

    <form name="form" method="POST" action="<?php echo WB_URL_COMMAND ?>">
        <input type="hidden" name="c" value="GerenciarRecursoInscrito">
        <input type="hidden" name="m" value="salvarRecursoFase1">

        <div class="panel panel-default">
            <div class="panel-heading"><h4>Apresentação de Recurso – Homologação Preliminar</h4></div>
            <div class="panel-body">
                <?php if ($fws_['msg']) FWA_Mostrar_Mensagem($fws_['msg']); ?>

                <br>
                <label>Descreva abaixo os argumentos com os quais contesta a decisão sobre sua inscrição.</label>
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

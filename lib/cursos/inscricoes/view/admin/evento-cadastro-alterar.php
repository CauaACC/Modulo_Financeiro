<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/evento-cabecalho.php");
?>
<div class="panel-group">
<div class="panel panel-default">
    <div class="panel-heading"><h4>Edição de Projeto/Evento</h4></div>
    <div class="panel-body">
<?php
require_once(dirname(__FILE__)."/../../view/admin/evento-cadastro-busca.php");

$cod_evento_alt = $fwsGet_['cod_evento'];

If(isset($cod_evento_alt)){

  $Conexao_db = FWA_Conexao("OpcTec");
  $var_sql = "
             SELECT *
             FROM tab_eventos
             WHERE
             (cod_evento = ".$cod_evento_alt.")
             ORDER BY cod_evento DESC
            ";

  $RS = $Conexao_db->Execute($var_sql);

  If (!$RS->EOF) {

    While (!$RS->EOF) {

      $objEvAlt = new Evento();
      $objEvAlt->setCodEvento($cod_evento_alt);
      $objEvAlt->getEventoById();
      $tituloEvento = $cod_evento_alt . ' - ' . $objEvAlt->getTitulo();
      ?>
      <form method="POST" name="form" action="view/admin/evento-cadastro-alterar-grava.php" onsubmit="return criticar()">
        <input type="hidden" name="cod_evento" id="codEvento" value="<?php echo $cod_evento_alt ?>">

      <?php include_once("evento-cadastro-campos.php"); ?>

      <div style="margin-top:14px; padding-top:12px; border-top:1px solid #e0e0e0; display:flex; gap:8px; align-items:center;">
        <button type="submit" class="btn btn-primary btn-sm">Salvar Altera&ccedil;&otilde;es</button>
        <button type="reset" class="btn btn-default btn-sm">Limpar</button>
        <?php /* Verificacao GERAL (cod_evento='TODOS'), nao por evento: a versao
                 antiga perguntava se a pessoa estava no grupo Administrador Geral
                 DAQUELE evento -- e num evento que nasceu sem equipe isso e' falso
                 para todo mundo, inclusive para o dono do sistema. Era o caso do
                 evento 119 (Museu 2026), onde ninguem conseguia duplicar. */ ?>
        <?php if (FWA_AutenticacaoGeral($_SESSION['_cod_usuario'], enumSistema::SIS_GERENCIAR_ADM_GERAL, 5, false, false)) { ?>
        <button type="button" class="btn btn-warning btn-sm" id="btnDuplicar" style="margin-left:auto;">Duplicar Evento</button>
        <?php include_once("evento-duplicar-modal.php"); ?>
        <?php } ?>
      </div>
      </form>
      <?php

      $RS->MoveNext();
    }

  }Else{
    echo "Codigo não encontrado.";
  }

}Else{
  echo "É Necessário Codigo da Evento.";
}

?></div></div></div></div>

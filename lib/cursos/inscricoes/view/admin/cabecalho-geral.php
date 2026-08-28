<?php
/**
 * Cabeçalho genérico para módulos que não dependem de evento carregado.
 *
 * Variáveis opcionais (definir ANTES do include):
 *   $tituloPagina   - texto do heading do panel (default: "Administração")
 *   $permSistema    - enumSistema para verificação de acesso (default: SIS_GERENCIAR_EVENTO)
 *   $permDireito    - enumDireito para verificação de acesso (default: DIR_ADMINISTRAR)
 *   $submenu        - nome do arquivo de submenu a incluir (ex: 'area_admin_menu_projetos.php')
 */
require_once(dirname(__FILE__)."/../../include/config.php");

if (!isset($tituloPagina))  $tituloPagina  = 'Administração';
if (!isset($permSistema))   $permSistema   = enumSistema::SIS_GERENCIAR_EVENTO;
if (!isset($permDireito))   $permDireito   = enumDireito::DIR_ADMINISTRAR;

if (COD_EVENTO == '0' && @$_SESSION['_administrativo']) {
    // Sem evento selecionado: verificar permissao em qualquer evento via 'TODOS'
    FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_INCLUIR_EVENTO, $permDireito, false, true, 'TODOS');
} else {
    FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_INCLUIR_EVENTO, $permDireito, false, true);
}
?>
<div class="page-header">
    <div class="page-context">Área Administrativa</div>
</div>
<?php
require_once(dirname(__FILE__)."/area_admin_menu_administracao.php");
if (isset($submenu)) require_once(dirname(__FILE__)."/".$submenu);
?>
<div class="panel-group">
<div class="panel panel-default">
    <div class="panel-heading"><h4><?php echo htmlspecialchars($tituloPagina) ?></h4></div>
    <div class="panel-body">

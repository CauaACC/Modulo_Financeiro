<?php
require_once(dirname(__FILE__)."/../../include/config.php");
$tituloPagina = 'Filtrar Projetos/Eventos:';
$submenu = 'area_admin_menu_projetos.php';
require_once(dirname(__FILE__)."/../../view/admin/cabecalho-geral.php");

@$cod_evento = $fwsGet_['$cod_evento'];
if (@$cod_evento != '') {
    // exibir detalhes de um evento específico
} else {
    require_once(dirname(__FILE__)."/../../view/admin/evento-cadastro-listar.php");
}

If (isset($cod_evento)) {
    $obj = new Evento();
    $obj->setCodEvento($cod_evento);
    $obj->getEventoById();
    If ($obj->getStatus() == 'ATIVADO') {
        echo "
        <br>
        <a href='index.php?content=view/admin/evento-cadastro-alterar.php&cod_evento={$cod_evento}' class='btn btn-primary btn-xs'>Editar</a>
        ";

        FWA_Mostrar_Mensagem($_SESSION['MSG']);

        echo "
        <table border='0' width='570'>
        <tr><td colspan='2'>&nbsp;<b> Código do Evento: {$obj->getCodEvento()}</td></tr>
        <tr><td colspan='2'>&nbsp;<b> Status:  {$obj->getStatus()}</b></td></tr>
        <tr><td width='110'>&nbsp;</td><td width='460'>&nbsp;</td></tr>
        <tr><td colspan='2'>&nbsp;<b> Titulo da Evento: </b> {$obj->getTitulo()}</td></tr>
        <tr><td colspan=2>&nbsp;<b> Observação: </td></tr>
        <tr><td colspan=2>{$obj->getObservacao()}</td></tr>
        </table>
        <br>
        ";
    } Else {
        FWA_Mostrar_Mensagem("Evento Número: " . $cod_evento . ", não encontrado!");
    }
}

?>
</div></div></div></div>

<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/evento-cabecalho.php");

@$cod_evento   = $fwsGet_['$cod_evento'];
if(@$cod_evento!=''){
//    require_once(dirname(__FILE__)."/../../view/admin/perfil-cadastro-busca.php");
} else {
    require_once(dirname(__FILE__)."/../../view/admin/evento-cadastro-listar.php");    
}
//@$cod_evento = $_GET['cod_evento'];
//@$cod_evento = COD_EVENTO;
If (isset($cod_evento)) {
    $obj = new Evento();
    $obj->setCodEvento($cod_evento);
    #Verificando se houve retorno de algum registro
    $obj->getEventoById();
    If ($obj->getStatus()=='ATIVADO') {
        echo " <br>
        &nbsp;&nbsp;&nbsp;&nbsp;
        <a href='index.php?content=view/admin/evento-configurar-alterar.php&cod_evento={$cod_evento}'><img src='resources/image/txt-alterar.gif' border='0'></a>
        &nbsp;&nbsp;&nbsp;&nbsp;
        ";

        FWA_Mostrar_Mensagem($_SESSION['MSG']);

        echo "
        <table border='0' width='570'>
        <tr><td colspan='2'>&nbsp;<b> Código do Evento: {$obj->getCodEvento()}</td></tr>
        <tr><td colspan='2'>&nbsp;<b> Status:  {$obj->getStatus()}</b></td></tr>

        <tr><td width='110'>&nbsp;</td><td width='460'>&nbsp;</td></tr>

        <tr><td colspan='2'>&nbsp;<b> Titulo da Evento: </b> {$obj->getTitulo() }</td></tr>

        <tr><td colspan=2>&nbsp;<b> Observação: </td></tr>
        <tr><td colspan=2>{$obj->getObservacao()}</td></tr>
        </table>
        <br>
        ";
    } Else {

        echo "
        <hr size=1 width=100%>
        &nbsp;&nbsp;&nbsp;&nbsp;

        <hr size=1 width=100%>
        <table border=0 width=570>
          <tr><td>
            &nbsp;&nbsp;&nbsp;&nbsp;
            " . FWA_Mostrar_Mensagem("Evento Número: " . $cod_evento . ", não encontrado!") . " 
          </td></tr>
        </table>
        <br>
        ";
    }
} Else {

//    echo "
//    <hr size=1 width=100%>
//    &nbsp;&nbsp;&nbsp;&nbsp;
//    <a href='index.php?content=view/admin/evento-configurar-incluir.php'><img src='resources/image/txt-incluir.gif' border='0'></a>
//    &nbsp;&nbsp;&nbsp;&nbsp;
//    <hr size=1 width=100%>
//    <table border=0 width=570>
//      <tr><td>
//        &nbsp;&nbsp;&nbsp;&nbsp;
//        Clique no Botão Incluir para Inserir um Evento!
//      </td></tr>
//    </table>
//    <br>
//    ";
    echo "Código do evento não informado.";


}


?>
</div></div></div></div>
</body>
</html>

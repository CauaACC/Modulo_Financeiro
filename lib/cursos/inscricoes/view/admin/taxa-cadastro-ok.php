<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/taxa-cabecalho.php");
require_once(dirname(__FILE__)."/../../view/admin/taxa-cadastro-busca.php");

@$cod_taxa = $_GET['cod_taxa'];
If (isset($cod_taxa)) {
    $obj = new Taxa();
    $obj->setCodTaxa($cod_taxa);
    #Verificando se houve retorno de algum registro
    If ($obj->getTaxaById($debug)) {
        echo " <br>
        &nbsp;&nbsp;&nbsp;&nbsp;
        <a href='index.php?content=view/admin/taxa-cadastro-incluir.php'><img src='resources/image/txt-incluir.gif' border='0'></a>
        &nbsp;&nbsp;&nbsp;&nbsp;
        <a href='index.php?content=view/admin/taxa-cadastro-alterar.php&cod_taxa={$cod_taxa}'><img src='resources/image/txt-alterar.gif' border='0'></a>
        &nbsp;&nbsp;&nbsp;&nbsp;
        <a href='view/admin/taxa-cadastro-excluir-grava.php?cod_taxa={$cod_taxa}&COD_EVENTO=".COD_EVENTO."&acao=Excluir'><img src='resources/image//txt-excluir.gif' border='0'></a>
        <hr size='1' width='100%'>
        ";

        FWA_Mostrar_Mensagem($_SESSION['MSG']);

        echo "
        <table border='0' width='570'>
        <tr><td colspan='2'>&nbsp;<b> Código da TAXA: {$obj->getCodTaxa()}</td></tr>
        <tr><td colspan='2'>&nbsp;<b> Status:  {$obj->getStatus()}</b></td></tr>

        <tr><td width='110'>&nbsp;</td><td width='460'>&nbsp;</td></tr>

        <tr><td colspan='2'>&nbsp;<b> Titulo da Taxa: </b> {$obj->getTitulo() }</td></tr>

        <tr><td colspan=2>&nbsp;<b> Valor: </td></tr>
        <tr><td colspan=2>{$obj->getTxUnicaAVista()}</td></tr>

        <tr><td colspan=2>&nbsp;<b> Observação: </td></tr>
        <tr><td colspan=2>{$obj->getObservacao()}</td></tr>
        </table>
        <br>
        ";
    } Else {

        echo "
        <hr size=1 width=100%>
        &nbsp;&nbsp;&nbsp;&nbsp;
        <a href='index.php?content=view/admin/taxa-cadastro-incluir.php'><img src='resources/image/txt-incluir.gif' border='0'></a>
        &nbsp;&nbsp;&nbsp;&nbsp;

        <hr size=1 width=100%>
        <table border=0 width=570>
          <tr><td>
            &nbsp;&nbsp;&nbsp;&nbsp;
            " . FWA_Mostrar_Mensagem("A TAXA Número: " . $cod_taxa . ", não Existe ou foi Excluída!") . " 
          </td></tr>
        </table>
        <br>
        ";
    }
} Else {

    echo "
    <hr size=1 width=100%>
    &nbsp;&nbsp;&nbsp;&nbsp;
    <a href='index.php?content=view/admin/taxa-cadastro-incluir.php'><img src='resources/image/txt-incluir.gif' border='0'></a>
    &nbsp;&nbsp;&nbsp;&nbsp;
    <hr size=1 width=100%>
    <table border=0 width=570>
      <tr><td>
        &nbsp;&nbsp;&nbsp;&nbsp;
        Clique no Botão Incluir para Inserir uma TAXA!
      </td></tr>
    </table>
    <br>
    ";
}
?>
</div></div></div></div>
</body>
</html>

<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/inscritos-cabecalho.php");
#require_once(dirname(__FILE__)."/../../view/admin/inscritos-cadastro-busca.php");
#--------------------------------------------------------------------
$debug = false;
$cod_trabalho   = $_GET['cod_trabalho'];
$MSG             = $_SESSION['MSG'];

$MSG_Imprimir = $MSG;
$loc   = "/--/";
$subst = "<br>";
$MSG_Imprimir = preg_replace($loc,$subst,$MSG_Imprimir);
$loc   = "/-/";
$subst = " ";
$MSG_Imprimir = preg_replace($loc,$subst,$MSG_Imprimir);

If($MSG_Imprimir){
  echo "<hr size='1' width='100%'>";
  echo $MSG_Imprimir;
  #echo "<hr size='1' width='100%'>";
}

#--------------------------------------------------------------------
If(isset($cod_trabalho)){
    try {

        $objTrabalho = new Trabalho();
        $objTrabalho->setCodTrabalho($cod_trabalho);
        $objTrabalho->getTrabalhoById();
        $obj = $objTrabalho;
      
        #Adicionando nas variaveis, os valores dos campos retirados dos registros
        $cod_inscrito               = $objTrabalho->getCodInscrito();
        $nome_inscrito              = $objTrabalho->getNomeInscrito();
        $cod_trabalho               = $objTrabalho->getCodTrabalho();
        $autor_apresentador_nome    = $objTrabalho->getAutorApresentadorNome();
        $autor_apresentador_e_mail  = $objTrabalho->getAutorApresentadorEMail();
        $nome_co_autor_1            = $objTrabalho->getNomeCoAutor1();
        $nome_co_autor_2            = $objTrabalho->getNomeCoAutor2();
        $nome_co_autor_3            = $objTrabalho->getNomeCoAutor3();
        $nome_co_autor_4            = $objTrabalho->getNomeCoAutor4();
        $nome_co_autor_5            = $objTrabalho->getNomeCoAutor5();
        $nome_co_autor_6            = $objTrabalho->getNomeCoAutor6();
        $instituicao_trabalho       = $objTrabalho->getInstituicaoTrabalho();
        $titulo_trabalho            = $objTrabalho->getTituloTrabalho();
        $palavras_chave             = $objTrabalho->getPalavrasChave();
        $resumo_trabalho            = $objTrabalho->getResumoTrabalho();
        $status                     = $objTrabalho->getStatus();
        $cod_area_tematica          = $objTrabalho->getCodAreaTematica();
        $titulo_area_tematica       = $objTrabalho->getTituloAreaTematica();
        $flg_enviado_avaliacao      = $objTrabalho->getFlgEnviadoAvaliacao();
        $arquivo_gerado             = $objTrabalho->getArquivoGerado();
        $arquivo_upload             = $objTrabalho->getArquivoUpload();
        $formato_texto              = $objTrabalho->getFormatoTexto();
        $tipo_documento              = $objTrabalho->getTipoDocumento();
                                                   
        #Definindo tabela com resultado
        if ($cod_inscrito != '') {
          try {
            $objI = new Inscrito();
            $objI->setCodInscrito($cod_inscrito);
            $objI->getInscritoById();
            echo "<h4>".$objI->getCodInscrito()." - ".$objI->getNomeInscrito()."</h4>";
          } catch (Exception $e) {
            // Captura a exceção e executa o código aqui
            echo "Não foi possível carregar inscrito: " . $e->getMessage();
          }
        }
        echo "
        <hr size='1' width='100%'>
        <span style class='texto'> <B> Ficha de Alteração de Trabalho</B><br>
        <span style class='textop'> <B> * Campos Obrigatórios</B></span>

        <table border='0' width='565'>
        <tr><td colspan='2' align='left'>
        
        <SCRIPT LANGUAGE=\"JavaScript1.2\" SRC=\"scripts/inscritos-trabalho-cadastro.js\"></SCRIPT>

        <!--<FORM method='POST' name='form' action='view/admin/inscritos-trabalho-cadastro-alterar-grava.php' onsubmit=\"return criticar()\">-->
        <FORM method='POST' name='form' action='view/admin/inscritos-trabalho-cadastro-grava.php' onsubmit=\"return criticarTrabalho9CBMP();\"  enctype=\"multipart/form-data\">
          <input type='hidden' name='cod_trabalho' value='{$cod_trabalho}'>
          <input type='hidden' name='COD_EVENTO'    value='{$COD_EVENTO}'>
          <input type='hidden' name='cod_inscrito'    value='{$cod_inscrito}'>
          <input type='hidden' name='trabalho_tipo' value='{$objEvConf->getTrabAnexoTipo()}'>
          <input type='submit' value='Alterar'> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='reset' value='Limpar'>
          <br>&nbsp;
        </td></tr>
        </table>
        <fieldset width='570'><legend> Dados do trabalho </legend>
          <span style class='texto'>&nbsp;&nbsp;<b>Cógido do Trabalho: {$cod_trabalho}</b><br><br></span>
        ";

        require_once("inscritos-trabalho-cadastro-campos.php");

        echo "
        </fieldset>
        <table border='0' width='565'>
        <tr><td colspan='2' align='left'>
              <br><br>
              <input type='submit' value='Alterar'> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='reset' value='Limpar'>
        </td></tr>
        </table>
        </FORM>
        ";
        
    } catch (Exception $exc) {
        echo $exc->getMessage();
    }


}Else{
  echo "É Necessário Codigo do Trabalho.";
}

?>

<form name="getdoc" method="post" action="<?php echo WB_URL_COMMAND?>" target="_blank">
    <input type="hidden" name="c" value="GerenciarUpload">
    <input type="hidden" name="m" value="abrirWord">
    <input type="hidden" value="" name="cod">
</form>
<form name="visualizarDoc" method="post" action="<?php echo WB_URL_COMMAND?>" target="_blank">
    <input type="hidden" name="c" value="GerenciarUpload">
    <input type="hidden" name="m" value="visualizarDoc">
    <input type="hidden" value="" name="cod">
</form>
<script language="JavaScript" type="text/javascript">
    function getdoc(cod){
        document.getdoc.cod.value=cod;
        document.getdoc.submit();
    }
    function visualiza(cod){
        document.visualizarDoc.cod.value=cod;
        document.visualizarDoc.submit();
    }
</script>
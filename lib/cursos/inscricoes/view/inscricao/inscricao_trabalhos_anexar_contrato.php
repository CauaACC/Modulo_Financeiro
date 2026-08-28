<?php
$objAnexContrato = new ArquivoContrato();
$objAnexContrato->setCodTrabalho($cod_trabalho);
$objAnexContrato->setCodInscrito($cod_inscrito);
$txtBotaoAnexo = "Enviar";
?>
<span style="font-size: 18px;font-weight: bold;color:#000">
    <br>Arquivo para o Contrato:
</span>
<?php
try {
    if(!$cod_trabalho){
        throw new Exception("Não foi possível localizar contratos.(cód.12)");
    }
    $objAnexContrato->carregarListaArquivoContratoBy('cod_inscrito');
    $ContratoAnexada = false;
    foreach ($objAnexContrato->getListaArquivoContrato() as $objListaAnexo) {
        if (file_exists(WB_PATH_LOG_CONTRATO.$objAnexContrato->getArquivoGerado())){
            $ContratoAnexada = true;
            echo "<br>";
            retornarIconeExtensao($objAnexContrato->getArquivoGerado());        
            echo "<a href=\"javascript:void(0);\" 
            onclick=\"visualizarContrato('".$objAnexContrato->getArquivoGerado()."');\"  
            title='Visualizar Contrato ".$objAnexContrato->getArquivoGerado()."' 
            style='color:green;'>
            <b>Visualizar Arquivo Contrato</b>
            - ".formatDateView($objAnexContrato->getDataInclusao())."</a><br>";
            $txtBotaoAnexo = "Substituir";
        }else{
            echo "<br><font color='red'>Arquivo ".$objAnexContrato->getArquivoGerado()." não encontrado, entre em contato com a administração.</font>";
        }
    }
    if($OP_PERMITIR_ANEXAR_CONTRATO){
        ?>
        <br>
        <span style="font-size:12px;">
            (Extensões permitidas: pdf tamanho máximo 25MB)
        </span>    
        <font color="#000">
        <br>
        <form name="form-<?php echo $cod_trabalho?>" method="POST" action="<?php echo WB_URL_COMMAND?>"  onsubmit="return criticarAnexoContrato();" enctype="multipart/form-data"  >
            <input type="hidden" name="c" value="GerenciarInscricao">
            <input type="hidden" name="m" value="salvarAnexoContrato">
            <input type="hidden" name="cod_trabalho" value="<?php echo $cod_trabalho?>">
            <input type="file" name="arquivo" id="arquivo-<?php echo $cod_trabalho?>" value=""
                placeholder="Inserir arquivo da apresentação"  class="btn-xs" required="">             
            <input type="submit" class="btn btn-xs btn-primary" 
            role="button" value="<?php echo $txtBotaoAnexo; ?> arquivo referente ao Contrato: <?php echo $cod_trabalho?>">
        </form>
        </font>
    <?php
    }//}#catch
} catch (Exception $exc) { 
    echo $exc->getMessage();
} 
?>
<?php
// echo "Arquivo anexado: ";
//     //echo "<br>Arquivo anexado: ".$arquivo_upload;
// //                echo "<br>Arquivo Gravado: ".$arquivo_gerado;
//     //if($_SERVER['REMOTE_ADDR']=='189.90.70.56'){
//     echo "<a href=\"javascript: visualiza('{$arquivo_gerado}');\"  title='Visualizar Contrato ".$objAnexContrato->getArquivoGerado()."' style='color:green;'>";
//     echo (substr($arquivo_gerado, -4)=='.pdf')? "<img src='resources/image/pdf.gif' border='0'>" : ""; 
//     echo (substr($arquivo_gerado, -4)=='.doc')? "<img src='resources/image/doc.gif' border='0'>" : ""; 
//     echo "Visualizar Arquivo ".$objEvConf->getDescTrabAnexoTipo()."</a>";
//     echo "<br><br>";
?>
<?php if($OP_ANEXAR_CONTRATO_ENCERRADO){ ?>
    <font color="#000">
    <br>
    Período de Envio de Contrato Encerrado: <?php echo formatDateView($objEvConf->getContratoAnexarDatFim()) ?>.
    </font>
<?php } ?>
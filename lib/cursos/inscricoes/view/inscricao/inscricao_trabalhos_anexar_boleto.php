<?php
$objAnexBoleto = new ArquivoBoleto();
$objAnexBoleto->setCodTrabalho($cod_trabalho);
$txtBotaoAnexo = "Enviar";
?>
<span style="font-size: 18px;font-weight: bold;color:#000">
    <br>Arquivo para o Boleto:
</span>
<?php
try {
    $objAnexBoleto->carregarListaArquivoBoletoBy('cod_trabalho');
    $BoletoAnexada = false;
    foreach ($objAnexBoleto->getListaArquivoBoleto() as $objListaAnexo) {
        if (file_exists(WB_PATH_LOG_BOLETO.$objAnexBoleto->getArquivoGerado())){
            $BoletoAnexada = true;
            echo "<br>";
            retornarIconeExtensao($objAnexBoleto->getArquivoGerado());        
            echo "<a href=\"javascript:void(0);\" 
            onclick=\"visualizarBoleto('".$objAnexBoleto->getArquivoGerado()."');\"  
            title='Visualizar Boleto ".$objAnexBoleto->getArquivoGerado()."' 
            style='color:green;'>
            <b>Visualizar Arquivo Boleto</b></a><br>";
            $txtBotaoAnexo = "Substituir";
        }else{
            echo "<br><font color='red'>Arquivo ".$objAnexBoleto->getArquivoGerado()." não encontrado, entre em contato com a administração.</font>";
        }
    }
} catch (Exception $exc) { }
if($OP_PERMITIR_ANEXAR_BOLETO){
    ?>
    <br>
    <span style="font-size:12px;">
        (Extensões permitidas: pdf tamanho máximo 25MB)
    </span>    
    <font color="#000">
    <br>
    <form name="form-<?php echo $cod_trabalho?>" method="POST" action="<?php echo WB_URL_COMMAND?>"  onsubmit="return criticarAnexoBoleto();" enctype="multipart/form-data"  >
        <input type="hidden" name="c" value="GerenciarInscricao">
        <input type="hidden" name="m" value="salvarAnexoBoleto">
        <input type="hidden" name="cod_trabalho" value="<?php echo $cod_trabalho?>">
        <input type="file" name="arquivo" id="arquivo-<?php echo $cod_trabalho?>" value=""
               placeholder="Inserir arquivo da apresentação"  class="btn-xs" required="">             
        <input type="submit" class="btn btn-xs btn-primary" 
        role="button" value="<?php echo $txtBotaoAnexo; ?> arquivo referente ao Boleto: <?php echo $cod_trabalho?>">
    </form>
    </font>
<?php
}//}#catch ?>
<?php
// echo "Arquivo anexado: ";
//     //echo "<br>Arquivo anexado: ".$arquivo_upload;
// //                echo "<br>Arquivo Gravado: ".$arquivo_gerado;
//     //if($_SERVER['REMOTE_ADDR']=='189.90.70.56'){
//     echo "<a href=\"javascript: visualiza('{$arquivo_gerado}');\"  title='Visualizar Boleto ".$objAnexBoleto->getArquivoGerado()."' style='color:green;'>";
//     retornarIconeExtensao($arquivo_gerado);
//     echo "Visualizar Arquivo ".$objEvConf->getDescTrabAnexoTipo()."</a>";
//     echo "<br><br>";
?>
<?php if($OP_ANEXAR_BOLETO_ENCERRADO){ ?>
    <font color="#000">
    <br>
    Período de Envio de Boleto Encerrado: <?php echo formatDateView($objEvConf->getBoletoAnexarDatFim()) ?>.
    </font>
<?php } ?>
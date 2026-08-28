<?php
$objAnexRpa = new ArquivoRpa();
$objAnexRpa->setCodTrabalho($cod_trabalho);
$objAnexRpa->setCodInscrito($cod_inscrito);
$txtBotaoAnexo = "Enviar";
?>
<span style="font-size: 18px;font-weight: bold;color:#000">
    <br>Arquivo para o Rpa:
</span>
<?php
try {
    if(!$cod_trabalho){
        throw new Exception("Não foi possível localizar rpas.(cód.12)");
    }
    $objAnexRpa->carregarListaArquivoRpaBy('cod_inscrito');
    $RpaAnexada = false;
    foreach ($objAnexRpa->getListaArquivoRpa() as $objListaAnexo) {
        if (file_exists(WB_PATH_LOG_RPA.$objAnexRpa->getArquivoGerado())){
            $RpaAnexada = true;
            echo "<br>";
            retornarIconeExtensao($objAnexRpa->getArquivoGerado());       
            echo "<a href=\"javascript:void(0);\" 
            onclick=\"visualizarRpa('".$objAnexRpa->getArquivoGerado()."');\"  
            title='Visualizar Rpa ".$objAnexRpa->getArquivoGerado()."' 
            style='color:green;'>
            <b>Visualizar Arquivo Rpa</b>
            - ".formatDateView($objAnexRpa->getDataInclusao())."</a><br>";
            $txtBotaoAnexo = "Substituir";
        }else{
            echo "<br><font color='red'>Arquivo ".$objAnexRpa->getArquivoGerado()." não encontrado, entre em contato com a administração.</font>";
        }
    }
    if($OP_PERMITIR_ANEXAR_RPA){
        ?>
        <br>
        <span style="font-size:12px;">
            (Extensões permitidas: pdf tamanho máximo 25MB)
        </span>    
        <font color="#000">
        <br>
        <form name="form-<?php echo $cod_trabalho?>" method="POST" action="<?php echo WB_URL_COMMAND?>"  onsubmit="return criticarAnexoRpa();" enctype="multipart/form-data"  >
            <input type="hidden" name="c" value="GerenciarInscricao">
            <input type="hidden" name="m" value="salvarAnexoRpa">
            <input type="hidden" name="cod_trabalho" value="<?php echo $cod_trabalho?>">
            <input type="file" name="arquivo" id="arquivo-<?php echo $cod_trabalho?>" value=""
                placeholder="Inserir arquivo da apresentação"  class="btn-xs" required="">             
            <input type="submit" class="btn btn-xs btn-primary" 
            role="button" value="<?php echo $txtBotaoAnexo; ?> arquivo referente ao Rpa: <?php echo $cod_trabalho?>">
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
//     echo "<a href=\"javascript: visualiza('{$arquivo_gerado}');\"  title='Visualizar Rpa ".$objAnexRpa->getArquivoGerado()."' style='color:green;'>";
//     retornarIconeExtensao($arquivo_gerado); 
//     echo "Visualizar Arquivo ".$objEvConf->getDescTrabAnexoTipo()."</a>";
//     echo "<br><br>";
?>
<?php if($OP_ANEXAR_RPA_ENCERRADO){ ?>
    <font color="#000">
    <br>
    Período de Envio de Rpa Encerrado: <?php echo formatDateView($objEvConf->getRpaAnexarDatFim()) ?>.
    </font>
<?php } ?>
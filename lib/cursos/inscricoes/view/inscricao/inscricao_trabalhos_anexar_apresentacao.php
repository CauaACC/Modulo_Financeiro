<?php
$objAnexApresetacao = new ArquivoApresentacao();
$objAnexApresetacao->setCodTrabalho($cod_trabalho);
$txtBotaoAnexo = "Enviar";
?>
<span style="font-size: 14px;color: #000">
<?php if ($objEvConf->getApresentacaoUrlNormas()!='') { ?>
    <br><a href="<?php echo $objEvConf->getApresentacaoUrlNormas() ?>" target="_blank">
    Normas e orientações para o preparo de trabalhos para a exposição em PLATAFORMA DIGITAL.</a>
<?php } ?>
<?php if ($objEvConf->getApresentacaoUrlTemplate()!='') { ?>
    <br><a href="<?php echo $objEvConf->getApresentacaoUrlTemplate() ?>" target="_blank">
    Utilize o “template” (<b>clique aqui para download</b>).</a>
<?php } ?>
<span>
<span style="font-size: 18px;font-weight: bold;color:#000">
    <br>Arquivo para o Pôster Digital:
    <!--Adicione seu arquivo para apresentação:-->
</span>
<br>
<span style="font-size:12px;">
    (Extensões permitidas: pdf tamanho máximo 25MB)
</span>
<?php
try {
    $objAnexApresetacao->carregarListaArquivoApresentacaoBy('cod_trabalho');
    $apresentacaoAnexada = false;
    foreach ($objAnexApresetacao->getListaArquivoApresentacao() as $objListaAnexo) {
        if (file_exists(WB_PATH_LOG_APRESENTACAO.$objAnexApresetacao->getArquivoGerado())){
            $apresentacaoAnexada = true;
            echo "<br>Arquivo anexado: <a href=\"javascript:void(0);\" onclick=\"visualizarDocumento('".$objAnexApresetacao->getArquivoGerado()."');\">".$objAnexApresetacao->getArquivoGerado()."</a>";
            $txtBotaoAnexo = "Substituir";
        }else{
            echo "<br><font color='red'>Arquivo ".$objAnexApresetacao->getArquivoGerado()." não encontrado, entre em contato com a administração.</font>";
        }
    }
} catch (Exception $exc) { }
if($OP_PERMITIR_ANEXAR_APRESENTACAO){
    ?>
    <font color="#000">
    <br>
    <form name="form-<?php echo $cod_trabalho?>" method="POST" action="<?php echo WB_URL_COMMAND?>"  onsubmit="return criticarAnexoApresentacao();" enctype="multipart/form-data"  >
        <input type="hidden" name="c" value="GerenciarInscricao">
        <input type="hidden" name="m" value="salvarAnexoApresentacao">
        <input type="hidden" name="cod_trabalho" value="<?php echo $cod_trabalho?>">
        <input type="file" name="arquivo" id="arquivo-<?php echo $cod_trabalho?>" value=""
               placeholder="Inserir arquivo da apresentação"  class="btn-xs" required="">             
        <input type="submit" class="btn btn-xs btn-primary" 
        role="button" value="<?php echo $txtBotaoAnexo; ?> arquivo referente ao Pôster Digital do Trabalho: <?php echo $cod_trabalho?>">
    </form>
    </font>
<?php
}//}#catch ?>
<?php if($OP_ANEXAR_APRESENTACAO_ENCERRADO){ ?>
    <font color="#000">
    <br>
    Período de Envio de Pôster Digital Encerrado: <?php echo formatDateView($objEvConf->getApresentacaoAnexarDatFim()) ?>.
    </font>
<?php } ?>
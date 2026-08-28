<?php
$objAnexAvulso = new ArquivoAvulso();
$objAnexAvulso->setCodTrabalho($cod_trabalho);
$txtBotaoAnexo = "Enviar";
?>
<br>
<span style="font-size: 14px;color: #000">
<?php if ($objEvConf->getAvulsoUrlNormas()!='') { ?>
    <br><a href="<?php echo $objEvConf->getAvulsoUrlNormas() ?>" target="_blank">
    Normas e orientações para o preparo.</a>
<?php } ?>
<?php if ($objEvConf->getAvulsoUrlTemplate()!='') { ?>
    <br><a href="<?php echo $objEvConf->getAvulsoUrlTemplate() ?>" target="_blank">
    <?php if ($objEvConf->getCodEvento()==103) { ?>
        Escolha UM dos dois modelos para a impressão do banner (<b>clique aqui para download.</b>).</a>
    <?php } else { ?>
        Utilize o “template” (<b>clique aqui para download.</b>).</a>
    <?php } ?>
<?php } ?>
<span>
<?php if ($objEvConf->getAvulsoAnexarArquivo()!='') { ?>
    <span style="font-size: 18px;font-weight: bold;color:#000">
        <br><?php 
        if (trim($objEvConf->getAvulsoNome())!=''){
            echo $objEvConf->getAvulsoNome();
        }else{
            echo "Arquivo Avulso (cadastrar descrição na configuração)";
        }
        ?>:
        <!--Adicione seu arquivo para apresentação:-->
    </span>
    <br>
    <span style="font-size:12px;">
        <!-- (Extensões permitidas: pdf tamanho máximo 25MB) -->
    </span>
    <?php
    try {
        $objAnexAvulso->carregarListaArquivoAvulsoBy('cod_trabalho');
        $AvulsoAnexada = false;
        foreach ($objAnexAvulso->getListaArquivoAvulso() as $objListaAnexo) {
            if (file_exists(WB_PATH_LOG_AVULSO.$objAnexAvulso->getArquivoGerado())){
                $AvulsoAnexada = true;
                echo "<br>Arquivo anexado: <a href=\"javascript:void(0);\" onclick=\"visualizarAvulso('".$objAnexAvulso->getArquivoGerado()."');\">".$objAnexAvulso->getArquivoGerado()."</a>";
                $txtBotaoAnexo = "Substituir";
            }else{
                echo "<br><font color='red'>Arquivo ".$objAnexAvulso->getArquivoGerado()." não encontrado, entre em contato com a administração.</font>";
            }
        }
    } catch (Exception $exc) { }
    if($OP_PERMITIR_ANEXAR_AVULSO){
        ?>
        <font color="#000">
        <br>
        <form name="form-<?php echo $cod_trabalho?>" method="POST" action="<?php echo WB_URL_COMMAND?>"  onsubmit="return criticarAnexoAvulso();" enctype="multipart/form-data"  >
            <input type="hidden" name="c" value="GerenciarInscricao">
            <input type="hidden" name="m" value="salvarAnexoAvulso">
            <input type="hidden" name="cod_trabalho" value="<?php echo $cod_trabalho?>">
            <input type="file" name="arquivo" id="arquivo-<?php echo $cod_trabalho?>" value=""
                placeholder="Inserir arquivo"  class="btn-xs" required="">             
            <input type="submit" class="btn btn-xs btn-primary" 
            role="button" value="<?php echo $txtBotaoAnexo; ?> <?php echo $objEvConf->getAvulsoNome() ?>: <?php echo $cod_trabalho?>">
        </form>
        </font>
    <?php
    }//}#catch ?>
    <?php if($OP_ANEXAR_AVULSO_ENCERRADO){ ?>
        <!-- <font color="#000">
        <br>
        Período de Envio de Pôster Digital Encerrado: <?php echo formatDateView($objEvConf->getAvulsoAnexarDatFim()) ?>.
        </font> -->
    <?php } ?>
<?php } ?>
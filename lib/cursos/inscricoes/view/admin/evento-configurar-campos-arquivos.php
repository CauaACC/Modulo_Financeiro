<table border='0' width='570'  cellspacing="10" cellpadding="10" class="dataTable">
    <tr><td colspan="2"><h4>Gerenciar Midias</h4></td></tr>
    <tr>
        <td colspan="2">
            <a href='?content=view/admin/GerenciarMidiaUpload.php'>Upload de Arquivos</a>
            <br>
        </td>
    </tr>
    <tr><td colspan="2"><h4>Assinatura do Presidente/Gestor:</h4></td></tr>
    <tr>
        <td colspan="2">
            Utilizada em documentos e certificados
            <br>
            <?php
            $filepath = WB_PATH_LOG_CERTIFICADOS."../assinaturas/";
            $arq = $filepath."/assinatura-presidente.jpg";
            if(file_exists($arq)){
                ?>
                <img style="width:250px;border:1px dashed;" z-index="98" src="https://opctec.net.br/<?php echo WB_GRUPO?>/<?php echo WB_PROJETO?>/inscricoes/view/certificados/modelos/img/ass-presidente.php">  
                <?php
            }else{
                echo "<font color='red'>Assinatura do presidente não carregada</font>";
            }
            ?>
            </td></tr>
    <tr><td colspan="2"><h4>Logo:</h4></td></tr>  
    <tr><td colspan="2">
                <?php  if(file_exists('resources/image/logo-60X235pxN-'.COD_EVENTO.'.png')){ ?>
                    <img src="<?php echo WB_URL_SITE; ?>/resources/image/<?php echo '/logo-60X235pxN-'.COD_EVENTO.'.png'; ?>" class="img-responsive" style="height: 72px;border:1px dashed;" >
                <?php }else if(file_exists('resources/image/logo-60X235pxN-'.$objEv->getGestor().'.png')){ ?>
                    <img src="<?php echo WB_URL_SITE; ?>/resources/image/<?php echo '/logo-60X235pxN-'.$objEv->getGestor().'.png'; ?>" class="img-responsive" style="height: 72px;border:1px dashed;" >
                <?php }else if(file_exists('resources/image/logo-60X235pxN-'. strtoupper($organizacao->getSigla()).'.png')){ ?>
                    <img src="<?php echo WB_URL_SITE; ?>/resources/image/<?php echo '/logo-60X235pxN-'.strtoupper($organizacao->getSigla()).'.png'; ?>" class="img-responsive" style="height: 72px;border:1px dashed;" >
                <?php }else{ ?>
                    <img src="<?php echo WB_URL_SITE; ?>/resources/image/<?php echo '/logo-OPCTEC.png'; ?>" class="img-responsive" style="height: 72px;border:1px dashed;" >
                <?php }  ?>
            
            <?php if ($objEvConf->getEmitirVoucher()==1) { ?>
            <h4>Arte para emissão de vouchers:</h4>                    
            Esta arte não deve sobrepor a área inferior direita, a partir da palavra PATROCINIO até a palavra OURO.
            <br><a href='?content=view/admin/GerenciarVoucherEvento.php'>Gerenciar Vouchers</a>
            <br><br>
                <?php $logoEtiqueta = "resources/image/etiqueta_10x56mm_".str_pad($objEv->getCodEvento(), 3, '0', STR_PAD_LEFT).".jpg"; ?>
                <?php  if(file_exists($logoEtiqueta)){ ?>
                    
        <style type="text/css">
            <!--
            table.voucher
            {
                padding: 0;
                /*border: solid 1mm LawnGreen;*/
                font-size: 12pt;
                background: #FFFFFF;
                text-align: center;
                vertical-align: middle;
            }

            td.voucher
            {
                padding: 0mm;
                border: solid 0mm black;
            }

            td.div voucher
            {
                width: 110px;
                /*height: 100px;*/
                text-align: left;
                padding: 0;
                position: relative;
            }

            td.div voucher
            {
                margin: auto;
                /*background: yellow;*/
                border: solid 0px black;
                /*color: red;*/
                width: 100px;
                height: 65px;
                text-align: center;
            }
            .codVoucher {
                /*-webkit-transform: rotate(-90deg); 
                -moz-transform: rotate(-90deg);	
                rotate: 90;*/
                font-size: 27px;
                position: absolute;
                float: left;
                width: 150px;
                height: 30px;
                margin-top: 100px;
                margin-left: 200px;
                border: solid 0px black;
            }
            .txtVoucher {
                /*-webkit-transform: rotate(-90deg); 
                -moz-transform: rotate(-90deg);	*/
                font-size: 12px;
                position: absolute;
                float: left;
                width: 145px;
                height: 20px;
                margin-top: 130px;
                margin-left: 200px;
                border: solid 0px black;
            }
            .desVoucher {
                /*-webkit-transform: rotate(-90deg); 
                -moz-transform: rotate(-90deg);	*/
                font-size: 13px;
                position: absolute;
                float: left;
                width: 160px;
                height: 20px;
                margin-top: 160px;
                margin-left: 200px;
                border: solid 0px black;
                color: #00afdb;
                font-weight: bold;
            }
            .seqVoucher {
                font-size: 11.5px;
                line-height: normal;
                position: absolute;
                float: left;
                width: 145px;
                height: 10px;
                border: solid 0px black;
                z-index: 0;
                margin-top: 100px;
                margin-left: 70px;
            }
            .validadeVoucher {
                font-size: 11px;
                line-height: normal;
                position: absolute;
                float: left;
                width: 145px;
                height: 10px;
                border: solid 0px black;
                z-index: 0;
                margin-top: 135px;
                margin-left: 70px;
            }
            .dataValidadeVoucher {
                font-size: 11px;
                line-height: normal;
                position: absolute;
                float: left;
                width: 145px;
                height: 10px;
                border: solid 0px black;
                z-index: 0;
                margin-top: 150px;
                margin-left: 70px;
            }
            .urlSite {
                font-size: 9px;
                line-height: normal;
                position: absolute;
                float: left;
                width: 145px;
                height: 10px;
                border: solid 0px black;
                z-index: 0;
                margin-top: 178px;
                margin-left: 10px;
            }
            -->
        </style>
                <table class="voucher" cellspacing="0" cellpadding="0"><tr>
                <td class="div voucher" style="padding: 0px;border:1px dashed;">
                <div style="position: relative;">
                <img src="<?php echo WB_URL_SITE.'/'.$logoEtiqueta?>" style="width:360px;margin:0px;align:left;float:left;">
                <div class="txtVoucher" border='1'><?php echo htmlentities('Informe o código acima no final de sua inscrição') ?></div>
                <div class="seqVoucher"><?php echo "PATROCINIO<br>001"; ?></div>
                <div class="codVoucher"><?php echo "AB99999"; ?></div>
                <div class="desVoucher"><?php echo "VALE 01 INSCRIÇÃO<br>CATEGORIA OURO"; ?></div>
                <div class="validadeVoucher"><?php echo htmlentities('Válido até:') ?></div>
                <div class="dataValidadeVoucher"><?php echo htmlentities(formatDateView(Date('Y-m-d'))) ?></div>
                <div class="urlSite"><?php echo URL_HOME_MENU ?></div>
                </div>                    
                </td></tr></table>
                
                <?php }else{ ?>
                    <font color='red'>arquivo não carregado</font>
                <?php } ?>
            <?php } ?>


                    
            <h4>Programa do evento:</h4>
            <br>
            <div class="form-group col-sm-12" style="padding-left: 0px;">
            <div class="form-group col-sm-12" style="padding-left: 0px;">
                Programa/Verso do Certificado: (PDF)
                <br>
                <?php
                    $objOficina = new Oficina();
                ?>
                <?php if ($objOficina->verifProgramaCertificado()) {?>
                    <button onclick="javascript:excluirArqPrograma('<?php echo $objOficina->getCodOficina(); ?>');">Excluir</button>
                    &nbsp;&nbsp;|&nbsp;&nbsp;
                    <button id="btnVisualizarPrograma" title="Visualizar Programa"
                    href="javascript:void(0);" onclick="javascript:visualizarPrograma('<?php echo  $objOficina->getCodOficina() ?>');">
                    <font color='green'>Visualizar Programa Anexado</font></button> 
                <?php }else{ ?>
                    <font color=red>Programa não anexado.</font>
                    <br>

                <?php } ?>
                <br><br>
            </div>
        </div>

        </td>
    </tr>
</table>
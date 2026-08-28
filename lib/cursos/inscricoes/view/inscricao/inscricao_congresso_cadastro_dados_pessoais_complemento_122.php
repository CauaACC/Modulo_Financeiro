            <div class="form-group col-sm-12" style="padding-left: 0px;">
                <label for="instituicao">Unidade Maple Bear: <span class="text-danger">*</span></label>
                <input type="text" id="instituicao" name="instituicao" value="<?php echo $objInscrito->getInstituicao() ?>"
                       class="form-control" placeholder="Informe a Unidade Maple Bear" required autofocus
                       SIZE='60' maxlength='255' onBlur='trim(this);'>
            </div>
            <div class="form-group col-sm-12" style="padding-left: 0px;">
                <label for="arquivo">Comprovante de Vínculo com a Maple Bear: <span class="text-danger">*</span></label>
                <?php
                $comprovanteAtual = null;
                if ($objInscrito->getCodInscrito()) {
                    $objTrabCompl = new Trabalho();
                    $objTrabCompl->setCodInscrito($objInscrito->getCodInscrito());
                    $objTrabCompl->setCodEvento(COD_EVENTO);
                    $objTrabCompl->carregarListaTrabalhosByIdInscrito($debug);
                    if ($objTrabCompl->getListaTrabalhos()) {
                        foreach ($objTrabCompl->getListaTrabalhos() as $trabItem) {
                            if ($trabItem->getTipoDocumento() == 'COMPROVANTE_VINCULO') {
                                $comprovanteAtual = $trabItem;
                                break;
                            }
                        }
                    }
                }
                if ($comprovanteAtual) { ?>
                    <div class="alert alert-success" style="padding: 8px; margin-bottom: 5px;">
                        <i class="fa fa-check-circle"></i> Comprovante enviado
                    </div>
                    <input type="file" id="arquivo" name="arquivo" class="form-control"
                           accept=".pdf,.jpg,.jpeg,.png">
                    <small class="text-muted">Selecione um novo arquivo apenas se desejar substituir o atual. Formatos: PDF, JPG, PNG (máx 10MB)</small>
                <?php } else { ?>
                    <input type="file" id="arquivo" name="arquivo" class="form-control" required
                           accept=".pdf,.jpg,.jpeg,.png">
                    <small class="text-muted">Formatos aceitos: PDF, JPG, PNG (máximo 10MB)</small>
                <?php } ?>
            </div>
            <div class="form-group col-sm-12" style="padding-left: 0px; margin-top: 10px;">
                <hr>
            </div>
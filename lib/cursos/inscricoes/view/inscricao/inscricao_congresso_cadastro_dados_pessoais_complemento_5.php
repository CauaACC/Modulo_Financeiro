            <div class="form-group col-sm-6" style="padding-left: 0px;">
                <label for="que_ser_associado">Número do CRESS (caso seja estudante ou outro, informe 000 como resposta):</label>
               <input type="text" id="numero_cress" name="numero_cress" value="<?php echo $objInscrito->getNumeroCress() ?>"
                       class="form-control" placeholder="Número Cress" <?php echo $OP_PERMITIR_ALTERAR_REQUIRED ?> autofocus required
                        SIZE='60' maxlength='255' onBlur='trim(this);'>
            </div>
            <div class="form-group col-sm-6" style="padding-left: 0px;">
                <label for="participou">Selecione sua instância</label>
                <select class="form-control" name="regional" id="regional" placeholder="reginal" <?php echo $OP_PERMITIR_ALTERAR_REQUIRED ?> autofocus required>
                    <option class='form_campos' value='-1' selected>Selecione uma instância</option>
                    <?php
                    $fws_['regional'] = @$objInscrito->getRegional();
                    CarregarOptionsAC::regionais($fws_, $debug);
                    ?>
                </select>
            </div>
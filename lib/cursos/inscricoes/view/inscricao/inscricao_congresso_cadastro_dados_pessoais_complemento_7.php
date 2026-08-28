
<div class="form-group col-sm-12" style="padding-left: 0px;">
    <label for="participou">Já participou de algum evento promovido pelo INESCO?</label>
    &nbsp;&nbsp;&nbsp;            
    <input type='radio' name='ja_participou_outro_evento' value='SIM' <?php echo verifItemChecked($objInscrito->getJaParticipouOutroEvento(), 'SIM') ?>  required autofocus>
    Sim
    &nbsp;&nbsp;&nbsp;
    <input type='radio' name='ja_participou_outro_evento' value='NAO' <?php echo verifItemChecked($objInscrito->getJaParticipouOutroEvento(), 'NAO') ?>  required autofocus>
    Não 
</div>
<div class="form-group col-sm-12" style="padding-left: 0px;">

    <div class="form-group col-sm-5" style="padding-left: 0px;">
        <label for="tipo_inscricao">Perfil da Inscrição:</label>
        <select class='form-control' name='tipo_inscricao' id='tipo_inscricao' placeholder="Perfil da Inscrição" required autofocus>
        <option class='form_campos' value='' selected>Selecione um Perfil</option>
        <?php
        if ($fws_['tipo_inscricao'] == '') {
            $fws_['tipo_inscricao'] = $objInscrito->getTipoInscricao();
        }
        CarregarOptionsAC::tipoInscricaoLinha($fws_, $debug, false);
        ?>
        </select>
    </div>
</div>

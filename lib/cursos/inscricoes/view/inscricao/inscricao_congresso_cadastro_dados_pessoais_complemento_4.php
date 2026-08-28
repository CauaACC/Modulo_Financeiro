            <div class="form-group col-sm-12" style="padding-left: 0px;">
                <label for="que_ser_associado">Aceito me associar ao INESCO gratuitamente para participar do evento?</label>
                &nbsp;&nbsp;&nbsp;
                <input type='radio' name='que_ser_associado' value='SIM' <?php echo verifItemChecked($objInscrito->getQueSerAssociado(), 'SIM') ?>  required autofocus>
                Sim
<!--                &nbsp;&nbsp;&nbsp;
                <input type='radio' name='que_ser_associado' value='NAO' <?php echo verifItemChecked($objInscrito->getQueSerAssociado(), 'NAO') ?> >
                Não -->
            </div>
            <div class="form-group col-sm-12" style="padding-left: 0px;">
                <label for="participou">Já participou de algum evento promovido pelo INESCO?</label>
                &nbsp;&nbsp;&nbsp;            
                <input type='radio' name='ja_participou_outro_evento' value='SIM' <?php echo verifItemChecked($objInscrito->getJaParticipouOutroEvento(), 'SIM') ?>  required autofocus>
                Sim
                &nbsp;&nbsp;&nbsp;
                <input type='radio' name='ja_participou_outro_evento' value='NAO' <?php echo verifItemChecked($objInscrito->getJaParticipouOutroEvento(), 'NAO') ?>  required autofocus>
                Não 
            </div>
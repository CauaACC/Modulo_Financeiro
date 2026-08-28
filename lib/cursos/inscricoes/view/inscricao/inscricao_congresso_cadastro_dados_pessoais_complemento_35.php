<?php if ($objEvConf->getDadosPessoaisTermoCompromisso()==1) {?>
<div class="form-group col-sm-12" style="margin-bottom:15px;margin-top:15px;padding-left: 0px;">
    <span style="text-size:18px;color:red;font-weight: bold;">
        Atenção, para visitar o museu é necessário aceitar o termo de adesão<br>
        <?php if ($objEvConf->getDadosPessoaisTermoCompromissoUrl()!='') {?>
        <a href="<?php echo $objEvConf->getDadosPessoaisTermoCompromissoUrl() ?>" target="_termoAceite">
            Clique aqui para ler o <br>TERMO DE ADESÃO PARA VISITAÇÃO AO MUSEU DIDÁTICO PROFESSOR CARLOS DA COSTA BRANCO
        </a>
        <?php }else{ ?>
            Url do termo não informada
        <?php } ?>
        </span>
        <span style="text-size:15px;font-weight: bold;">
        <br><br>
        <input type='radio' name='aceitou_termos' value='SIM' <?php echo verifItemChecked($objInscrito->getAceitouTermos(), 'SIM') ?>  required autofocus>
        <span style="color:green">SIM, LI, CONCORDO e ACEITO O TERMO DE ADESÃO PARA VISITAÇÃO.</span>
        <br><br>
        <input type='radio' name='aceitou_termos' id='nao_aceitou_termos' value='NAO' <?php echo verifItemChecked($objInscrito->getAceitouTermos(), 'NAO') ?>  required autofocus>
        NÃO, não aceito o termo de adesão. </span>
    </span>
</div>   
<div class="form-group col-sm-12" style="padding-bottom:15px;padding-top:15px;padding-left: 0px;">
    <hr width="100%" border="1">    
</div>
<?php } ?>    
<!--
div class="form-group col-sm-12" style="padding-left: 0px;">
    <div class="form-group col-sm-12" style="padding-left: 0px;padding-top: 10px;">
        <hr>
        <span style="text-size:15px;font-weight: bold;">
        A quantidade mínima de visitantes é de 10 pessoas incluindo guias e professores.
        <br>O custo por visitante é de R$ 5,00.
        <br>Selecione a Quantidade de Ingressos a serem adquiridos:
        </span>
        <select name='qtd_ingressos' id='qtd_ingressos' required autofocus>
            <option value=''></option>
            <option value='20'>de 01 a 20</option>
            <option value='30'>de 21 a 30</option>
            <option value='40'>de 31 a 40</option>
            <option value='50'>de 41 a 50</option>
        </select>
        <br>
        <br>
        <span style="text-size:17px;font-weight: bold;">
            Valor Calculado de Acordo com a Quantidade de Visitantes: R$ <span id="valorIngressos"></span>
        </span>
        <br>
        <hr>
    </div>
</div>
-->


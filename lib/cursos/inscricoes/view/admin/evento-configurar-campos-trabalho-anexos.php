<div id="tabsTrabalhoConfig">
    <ul>
        <li class="txtAba"><a href="#trabConfigApresentacao">Configurar Apresentação</a></li>
        <li class="txtAba"><a href="#trabConfigVideo">Configurar Vídeo</a></li>
        <li class="txtAba"><a href="#trabConfigContrato">Configurar Contrato</a></li>
        <li class="txtAba"><a href="#trabConfigRpa">Configurar Rpa</a></li>
        <li class="txtAba"><a href="#trabConfigBoletos"> Configurar Boletos</a></li>
        <li class="txtAba"><a href="#trabConfigDocAvulso">Configurar Doc Avulsos</a></li>
    </ul>

    <!-- Conteúdo das abas -->
    <div id="trabConfigApresentacao" class="tab-content">
        <table border='0' width='540' cellspacing="10" cellpadding="10" class="dataTable">
            <tr>
                <td colspan="2"><br><b>Configurar Apresentação:</b></td>
            </tr>
            <tr>
                <td colspan="2">Anexar apresentação:<br>
                    <input type="radio" name="apresentacao_anexar" value="1" <?= verifItemChecked($objEvConf->getApresentacaoAnexar(), '1', false) ?> /> Sim
                    &nbsp;&nbsp;
                    <input type="radio" name="apresentacao_anexar" value="0" <?= verifItemChecked($objEvConf->getApresentacaoAnexar(), array('', 0, false), false) ?> /> Não
                </td>
            </tr>
            <tr>
                <td colspan="2">Quem pode anexar?</td>
            </tr>
            <tr>
                <td> <input type="radio" name="apresentacao_anexar_quem" value="0" <?= verifItemChecked($objEvConf->getTrabFormatoTexto(), array('', 0, false), false) ?> /> Permitir Todos.
                    &nbsp;&nbsp;
                    <br> <input type="radio" name="apresentacao_anexar_quem" value="pago" <?= verifItemChecked($objEvConf->getTrabFormatoTexto(), 'pago', false) ?> /> Permitir anexar se inscrito pago.
                    &nbsp;&nbsp;
                    <br> <input type="radio" name="apresentacao_anexar_quem" value="aprovado" <?= verifItemChecked($objEvConf->getTrabFormatoTexto(), 'aprovado', false) ?> /> Permitir anexar se inscrito aprovado.
                    &nbsp;&nbsp;
                    <br> <input type="radio" name="apresentacao_anexar_quem" value="pago_aprovado" <?= verifItemChecked($objEvConf->getTrabFormatoTexto(), 'pago_aprovado', false) ?> /> Permitir anexar se inscrito pago e aprovado.
            </tr>
            </td>
            <tr>
                <td colspan="2">Quem pode visualizar?</td>
            </tr>
            <tr>
                <td> <input type="radio" name="apresentacao_visualizar_quem" value="0" <?= verifItemChecked($objEvConf->getTrabFormatoTexto(), array('', 0, false), false) ?> /> Permitir Todos.
                    &nbsp;&nbsp;
                    <br> <input type="radio" name="apresentacao_visualizar_quem" value="pago" <?= verifItemChecked($objEvConf->getTrabFormatoTexto(), 'pago', false) ?> /> Permitir visualizar se inscrito pago.
                    &nbsp;&nbsp;
                    <br> <input type="radio" name="apresentacao_visualizar_quem" value="aprovado" <?= verifItemChecked($objEvConf->getTrabFormatoTexto(), 'aprovado', false) ?> /> Permitir visualizar se inscrito aprovado.
                    &nbsp;&nbsp;
                    <br> <input type="radio" name="apresentacao_visualizar_quem" value="pago_aprovado" <?= verifItemChecked($objEvConf->getTrabFormatoTexto(), 'pago_aprovado', false) ?> /> Permitir visualizar se inscrito pago e aprovado.
            </tr>
            </td>
            <tr>
                <td colspan="2">
                    Período de Anexar Apresentação<br>
                    Inicio <input type='datetime-local' name='apresentacao_anexar_dat_ini' value='<?php echo $objEvConf->getApresentacaoAnexarDatIni() ?>' onBlur="trim(this);">
                    Fim <input type='datetime-local' name='apresentacao_anexar_dat_fim' value='<?php echo $objEvConf->getApresentacaoAnexarDatFim() ?>' onBlur="trim(this);">
                </td>
            </tr>
            <tr>
                <td colspan="2">Url normas para elaboração: <br> <input type='text' name='apresentacao_url_normas' value='<?php echo $objEvConf->getApresentacaoUrlNormas() ?>' maxlength='255' size='60' onBlur="trim(this);"></td>
            </tr>
            <tr>
                <td colspan="2">Url download do template: <br> <input type='text' name='apresentacao_url_template' value='<?php echo $objEvConf->getApresentacaoUrlTemplate() ?>' maxlength='255' size='60' onBlur="trim(this);"></td>
            </tr>
        </table>
    </div>
    <div id="trabConfigVideo" class="tab-content">
        <table border='0' width='570' cellspacing="10" cellpadding="10" class="dataTable">
            <tr>
                <td colspan="2"><br><b>Configurar Vídeo:</b></td>
            </tr>
            <tr>
                <td colspan="2">Anexar apresentação em video:<br>
                    <input type="radio" name="apresentacao_anexar_video" value="1" <?= verifItemChecked($objEvConf->getApresentacaoAnexarVideo(), '1', false) ?> /> Sim
                    &nbsp;&nbsp;
                    <input type="radio" name="apresentacao_anexar_video" value="0" <?= verifItemChecked($objEvConf->getApresentacaoAnexarVideo(), array('', 0, false), false) ?> /> Não
                </td>
            </tr>
            <tr>
                <td colspan="2">Quem pode anexar?</td>
            </tr>
            <tr>
                <td> <input type="radio" name="apresentacao_anexar_video_quem" value="0" <?= verifItemChecked($objEvConf->getTrabFormatoTexto(), array('', 0, false), false) ?> /> Permitir Todos.
                    &nbsp;&nbsp;
                    <br> <input type="radio" name="apresentacao_anexar_video_quem" value="pago" <?= verifItemChecked($objEvConf->getTrabFormatoTexto(), 'pago', false) ?> /> Permitir anexar se inscrito pago.
                    &nbsp;&nbsp;
                    <br> <input type="radio" name="apresentacao_anexar_video_quem" value="aprovado" <?= verifItemChecked($objEvConf->getTrabFormatoTexto(), 'aprovado', false) ?> /> Permitir anexar se inscrito aprovado.
                    &nbsp;&nbsp;
                    <br> <input type="radio" name="apresentacao_anexar_video_quem" value="pago_aprovado" <?= verifItemChecked($objEvConf->getTrabFormatoTexto(), 'pago_aprovado', false) ?> /> Permitir anexar se inscrito pago e aprovado.
            </tr>
            </td>
            <tr>
                <td colspan="2">Quem pode visualizar?</td>
            </tr>
            <tr>
                <td> <input type="radio" name="apresentacao_visualizar_video_quem" value="0" <?= verifItemChecked($objEvConf->getTrabFormatoTexto(), array('', 0, false), false) ?> /> Permitir Todos.
                    &nbsp;&nbsp;
                    <br> <input type="radio" name="apresentacao_visualizar_video_quem" value="pago" <?= verifItemChecked($objEvConf->getTrabFormatoTexto(), 'pago', false) ?> /> Permitir visualizar se inscrito pago.
                    &nbsp;&nbsp;
                    <br> <input type="radio" name="apresentacao_visualizar_video_quem" value="aprovado" <?= verifItemChecked($objEvConf->getTrabFormatoTexto(), 'aprovado', false) ?> /> Permitir visualizar se inscrito aprovado.
                    &nbsp;&nbsp;
                    <br> <input type="radio" name="apresentacao_visualizar_video_quem" value="pago_aprovado" <?= verifItemChecked($objEvConf->getTrabFormatoTexto(), 'pago_aprovado', false) ?> /> Permitir visualizar se inscrito pago e aprovado.
            </tr>
            </td>
            <tr>
                <td colspan="2">
                    Período de Anexar Apresentação e Video<br>
                    Inicio <input type='datetime-local' name='apresentacao_anexar_video_dat_ini' value='<?php echo $objEvConf->getApresentacaoAnexarVideoDatIni() ?>' onBlur="trim(this);">
                    Fim <input type='datetime-local' name='apresentacao_anexar_video_dat_fim' value='<?php echo $objEvConf->getApresentacaoAnexarVideoDatFim() ?>' onBlur="trim(this);">
                </td>
            </tr>
        </table>
    </div>
    <div id="trabConfigContrato" class="tab-content">
        <table border='0' width='570' cellspacing="10" cellpadding="10" class="dataTable">
            <tr>
                <td colspan="2"><br><b>Configurar Contrato:</b></td>
            </tr>
            <tr>
                <td colspan="2">Anexar contrato:<br>
                    <input type="radio" name="contrato_anexar" value="1" <?= verifItemChecked($objEvConf->getContratoAnexar(), '1', false) ?> /> Sim
                    &nbsp;&nbsp;
                    <input type="radio" name="contrato_anexar" value="0" <?= verifItemChecked($objEvConf->getContratoAnexar(), array('', 0, false), false) ?> /> Não
                </td>
            </tr>
            <tr>
                <td colspan="2">Quem pode anexar?</td>
            </tr>
            <tr>
                <td> <input type="radio" name="contrato_anexar_quem" value="0" <?= verifItemChecked($objEvConf->getContratoAnexarQuem(), array('', 0, false), false) ?> /> Permitir Todos.
                    &nbsp;&nbsp;
                    <br> <input type="radio" name="contrato_anexar_quem" value="pago" <?= verifItemChecked($objEvConf->getContratoAnexarQuem(), 'pago', false) ?> /> Permitir anexar se inscrito pago.
                    &nbsp;&nbsp;
                    <br> <input type="radio" name="contrato_anexar_quem" value="aprovado" <?= verifItemChecked($objEvConf->getContratoAnexarQuem(), 'aprovado', false) ?> /> Permitir anexar se inscrito aprovado.
                    &nbsp;&nbsp;
                    <br> <input type="radio" name="contrato_anexar_quem" value="pago_aprovado" <?= verifItemChecked($objEvConf->getContratoAnexarQuem(), 'pago_aprovado', false) ?> /> Permitir anexar se inscrito pago e aprovado.
            </tr>
            </td>
            <tr>
                <td colspan="2">Quem pode visualizar?</td>
            </tr>
            <tr>
                <td> <input type="radio" name="contrato_visualizar_quem" value="0" <?= verifItemChecked($objEvConf->getContratoVisualizarQuem(), array('', 0, false), false) ?> /> Permitir Todos.
                    &nbsp;&nbsp;
                    <br> <input type="radio" name="contrato_visualizar_quem" value="pago" <?= verifItemChecked($objEvConf->getContratoVisualizarQuem(), 'pago', false) ?> /> Permitir visualizar se inscrito pago.
                    &nbsp;&nbsp;
                    <br> <input type="radio" name="contrato_visualizar_quem" value="aprovado" <?= verifItemChecked($objEvConf->getContratoVisualizarQuem(), 'aprovado', false) ?> /> Permitir visualizar se inscrito aprovado.
                    &nbsp;&nbsp;
                    <br> <input type="radio" name="contrato_visualizar_quem" value="pago_aprovado" <?= verifItemChecked($objEvConf->getContratoVisualizarQuem(), 'pago_aprovado', false) ?> /> Permitir visualizar se inscrito pago e aprovado.
            </tr>
            </td>
            <tr>
                <td colspan="2">
                    Período de Anexar Contrato<br>
                    Inicio <input type='datetime-local' name='contrato_anexar_dat_ini' value='<?php echo $objEvConf->getContratoAnexarDatIni() ?>' onBlur="trim(this);">
                    Fim <input type='datetime-local' name='contrato_anexar_dat_fim' value='<?php echo $objEvConf->getContratoAnexarDatFim() ?>' onBlur="trim(this);">
                </td>
            </tr>
        </table>
    </div>
    <div id="trabConfigRpa" class="tab-content">
        <table border='0' width='570' cellspacing="10" cellpadding="10" class="dataTable">
            <tr>
                <td colspan="2"><br><b>Configurar Rpa:</b></td>
            </tr>
            <tr>
                <td colspan="2">Anexar Rpa:<br>
                    <input type="radio" name="rpa_anexar" value="1" <?= verifItemChecked($objEvConf->getRpaAnexar(), '1', false) ?> /> Sim
                    &nbsp;&nbsp;
                    <input type="radio" name="rpa_anexar" value="0" <?= verifItemChecked($objEvConf->getRpaAnexar(), array('', 0, false), false) ?> /> Não
                </td>
            </tr>
            <tr>
                <td colspan="2">Quem pode anexar?</td>
            </tr>
            <tr>
                <td> <input type="radio" name="rpa_anexar_quem" value="0" <?= verifItemChecked($objEvConf->getRpaAnexarQuem(), array('', 0, false), false) ?> /> Permitir Todos.
                    &nbsp;&nbsp;
                    <br> <input type="radio" name="rpa_anexar_quem" value="pago" <?= verifItemChecked($objEvConf->getRpaAnexarQuem(), 'pago', false) ?> /> Permitir anexar se inscrito pago.
                    &nbsp;&nbsp;
                    <br> <input type="radio" name="rpa_anexar_quem" value="aprovado" <?= verifItemChecked($objEvConf->getRpaAnexarQuem(), 'aprovado', false) ?> /> Permitir anexar se inscrito aprovado.
                    &nbsp;&nbsp;
                    <br> <input type="radio" name="rpa_anexar_quem" value="pago_aprovado" <?= verifItemChecked($objEvConf->getRpaAnexarQuem(), 'pago_aprovado', false) ?> /> Permitir anexar se inscrito pago e aprovado.
            </tr>
            </td>
            <tr>
                <td colspan="2">Quem pode visualizar?</td>
            </tr>
            <tr>
                <td> <input type="radio" name="rpa_visualizar_quem" value="0" <?= verifItemChecked($objEvConf->getRpaVisualizarQuem(), array('', 0, false), false) ?> /> Permitir Todos.
                    &nbsp;&nbsp;
                    <br> <input type="radio" name="rpa_visualizar_quem" value="pago" <?= verifItemChecked($objEvConf->getRpaVisualizarQuem(), 'pago', false) ?> /> Permitir visualizar se inscrito pago.
                    &nbsp;&nbsp;
                    <br> <input type="radio" name="rpa_visualizar_quem" value="aprovado" <?= verifItemChecked($objEvConf->getRpaVisualizarQuem(), 'aprovado', false) ?> /> Permitir visualizar se inscrito aprovado.
                    &nbsp;&nbsp;
                    <br> <input type="radio" name="rpa_visualizar_quem" value="pago_aprovado" <?= verifItemChecked($objEvConf->getRpaVisualizarQuem(), 'pago_aprovado', false) ?> /> Permitir visualizar se inscrito pago e aprovado.
            </tr>
            </td>
            <tr>
                <td colspan="2">
                    Período de Anexar rpa<br>
                    Inicio <input type='datetime-local' name='rpa_anexar_dat_ini' value='<?php echo $objEvConf->getRpaAnexarDatIni() ?>' onBlur="trim(this);">
                    Fim <input type='datetime-local' name='rpa_anexar_dat_fim' value='<?php echo $objEvConf->getRpaAnexarDatFim() ?>' onBlur="trim(this);">
                </td>
            </tr>
        </table>
    </div>
    <div id="trabConfigBoletos" class="tab-content">
        <table border='0' width='570' cellspacing="10" cellpadding="10" class="dataTable">
            <tr>
                <td colspan="2"><br><b>Configurar Boletos:</b></td>
            </tr>
            <tr>
                <td colspan="2">Anexar boletos:<br>
                    <input type="radio" name="boleto_anexar" value="1" <?= verifItemChecked($objEvConf->getBoletoAnexar(), '1', false) ?> /> Sim
                    &nbsp;&nbsp;
                    <input type="radio" name="boleto_anexar" value="0" <?= verifItemChecked($objEvConf->getBoletoAnexar(), array('', 0, false), false) ?> /> Não
                </td>
            </tr>
            <tr>
                <td colspan="2">Quem pode anexar?</td>
            </tr>
            <tr>
                <td> <input type="radio" name="boleto_anexar_quem" value="0" <?= verifItemChecked($objEvConf->getBoletoAnexarQuem(), array('', 0, false), false) ?> /> Permitir Todos.
                    &nbsp;&nbsp;
                    <br> <input type="radio" name="boleto_anexar_quem" value="pago" <?= verifItemChecked($objEvConf->getBoletoAnexarQuem(), 'pago', false) ?> /> Permitir anexar se inscrito pago.
                    &nbsp;&nbsp;
                    <br> <input type="radio" name="boleto_anexar_quem" value="aprovado" <?= verifItemChecked($objEvConf->getBoletoAnexarQuem(), 'aprovado', false) ?> /> Permitir anexar se inscrito aprovado.
                    &nbsp;&nbsp;
                    <br> <input type="radio" name="boleto_anexar_quem" value="pago_aprovado" <?= verifItemChecked($objEvConf->getBoletoAnexarQuem(), 'pago_aprovado', false) ?> /> Permitir anexar se inscrito pago e aprovado.
            </tr>
            </td>
            <tr>
                <td colspan="2">Quem pode visualizar?</td>
            </tr>
            <tr>
                <td> <input type="radio" name="boleto_visualizar_quem" value="0" <?= verifItemChecked($objEvConf->getBoletoVisualizarQuem(), array('', 0, false), false) ?> /> Permitir Todos.
                    &nbsp;&nbsp;
                    <br> <input type="radio" name="boleto_visualizar_quem" value="pago" <?= verifItemChecked($objEvConf->getBoletoVisualizarQuem(), 'pago', false) ?> /> Permitir visualizar se inscrito pago.
                    &nbsp;&nbsp;
                    <br> <input type="radio" name="boleto_visualizar_quem" value="aprovado" <?= verifItemChecked($objEvConf->getBoletoVisualizarQuem(), 'aprovado', false) ?> /> Permitir visualizar se inscrito aprovado.
                    &nbsp;&nbsp;
                    <br> <input type="radio" name="boleto_visualizar_quem" value="pago_aprovado" <?= verifItemChecked($objEvConf->getBoletoVisualizarQuem(), 'pago_aprovado', false) ?> /> Permitir visualizar se inscrito pago e aprovado.
            </tr>
            </td>
            <tr>
                <td colspan="2">
                    Período de Anexar Boleto<br>
                    Inicio <input type='datetime-local' name='boleto_anexar_dat_ini' value='<?php echo $objEvConf->getBoletoAnexarDatIni() ?>' onBlur="trim(this);">
                    Fim <input type='datetime-local' name='boleto_anexar_dat_fim' value='<?php echo $objEvConf->getBoletoAnexarDatFim() ?>' onBlur="trim(this);">
                </td>
            </tr>
        </table>
    </div>
    <div id="trabConfigDocAvulso" class="tab-content">
        <table border='0' width='570' cellspacing="10" cellpadding="10" class="dataTable">
            <tr>
                <td colspan="2"><br><b>Configurar Doc Avulsos:</b></td>
            </tr>
            <tr>
                <td colspan="2">Anexar avulsos:<br>
                    <input type="radio" name="avulso_anexar" value="1" <?= verifItemChecked($objEvConf->getAvulsoAnexar(), '1', false) ?> /> Sim
                    &nbsp;&nbsp;
                    <input type="radio" name="avulso_anexar" value="0" <?= verifItemChecked($objEvConf->getAvulsoAnexar(), array('', 0, false), false) ?> /> Não
                </td>
            </tr>      
            <tr>
                <td colspan="2">Avulso descrição nome: <br> <input type='text' name='avulso_nome' value='<?php echo $objEvConf->getAvulsoNome() ?>' maxlength='255' size='60' onBlur="trim(this);"></td>
            </tr>      
            <tr>
                <td colspan="2">Quem pode anexar?</td>
            </tr>
            <tr>
                <td> <input type="radio" name="avulso_anexar_quem" value="0" <?= verifItemChecked($objEvConf->getAvulsoAnexarQuem(), array('', 0, false), false) ?> /> Permitir Todos.
                    &nbsp;&nbsp;
                    <br> <input type="radio" name="avulso_anexar_quem" value="pago" <?= verifItemChecked($objEvConf->getAvulsoAnexarQuem(), 'pago', false) ?> /> Permitir anexar se inscrito pago.
                    &nbsp;&nbsp;
                    <br> <input type="radio" name="avulso_anexar_quem" value="aprovado" <?= verifItemChecked($objEvConf->getAvulsoAnexarQuem(), 'aprovado', false) ?> /> Permitir anexar se inscrito aprovado.
                    &nbsp;&nbsp;
                    <br> <input type="radio" name="avulso_anexar_quem" value="pago_aprovado" <?= verifItemChecked($objEvConf->getAvulsoAnexarQuem(), 'pago_aprovado', false) ?> /> Permitir anexar se inscrito pago e aprovado.
            </tr>
            </td>
            <tr>
                <td colspan="2">Quem pode visualizar?</td>
            </tr>
            <tr>
                <td> <input type="radio" name="avulso_visualizar_quem" value="0" <?= verifItemChecked($objEvConf->getAvulsoVisualizarQuem(), array('', 0, false), false) ?> /> Permitir Todos.
                    &nbsp;&nbsp;
                    <br> <input type="radio" name="avulso_visualizar_quem" value="pago" <?= verifItemChecked($objEvConf->getAvulsoVisualizarQuem(), 'pago', false) ?> /> Permitir visualizar se inscrito pago.
                    &nbsp;&nbsp;
                    <br> <input type="radio" name="avulso_visualizar_quem" value="aprovado" <?= verifItemChecked($objEvConf->getAvulsoVisualizarQuem(), 'aprovado', false) ?> /> Permitir visualizar se inscrito aprovado.
                    &nbsp;&nbsp;
                    <br> <input type="radio" name="avulso_visualizar_quem" value="pago_aprovado" <?= verifItemChecked($objEvConf->getAvulsoVisualizarQuem(), 'pago_aprovado', false) ?> /> Permitir visualizar se inscrito pago e aprovado.
            </tr>
            </td>
            <tr>
                <td colspan="2">
                    Período de Anexar Avulso<br>
                    Inicio <input type='datetime-local' name='avulso_anexar_dat_ini' value='<?php echo $objEvConf->getAvulsoAnexarDatIni() ?>' onBlur="trim(this);">
                    Fim <input type='datetime-local' name='avulso_anexar_dat_fim' value='<?php echo $objEvConf->getAvulsoAnexarDatFim() ?>' onBlur="trim(this);">
                </td>
            </tr>
            <tr>
                <td colspan="2">Url normas para elaboração: <br> <input type='text' name='avulso_url_normas' value='<?php echo $objEvConf->getAvulsoUrlNormas() ?>' maxlength='255' size='60' onBlur="trim(this);"></td>
            </tr>
            <tr>
                <td colspan="2">Url download do template: <br> <input type='text' name='avulso_url_template' value='<?php echo $objEvConf->getAvulsoUrlTemplate() ?>' maxlength='255' size='60' onBlur="trim(this);"></td>
            </tr>
            <tr>
                <td colspan="2">Extensões permitidas: <br> <input type='text' name='avulso_arquivo_extensao' value='<?php echo $objEvConf->getAvulsoArquivoExtensao() ?>' maxlength='255' size='60' onBlur="trim(this);"></td>
            </tr>
        </table>
    </div>
</div>
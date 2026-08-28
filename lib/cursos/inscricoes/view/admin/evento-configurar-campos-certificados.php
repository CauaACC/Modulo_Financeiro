<table border='0' width='570'  cellspacing="10" cellpadding="10" class="dataTable">
    <tr><td colspan="2"><span style="font-size:15px;font-weight: bold;">Configuração de Certificado</span></td></tr>        
    <tr><td>Liberar certificado:        </td><td> 
            <?php espelhoSimNao('emitir_certificado', $objEvConf->getEmitirCertificado()) ?>
        </td></tr>
    <tr><td>Data liberação certificado: </td><td> 
            <input type='datetime-local' name='certificado_dat_ini' value='<?php echo $objEvConf->getCertificadoDatIni() ?>' onBlur="trim(this);" >
        </td></tr>        
    <tr><td>Data Emissão: </td><td> 
            <input type='date' name='certificado_data_emissao' value='<?php echo substr($objEvConf->getCertificadoDataEmissao(),0,10) ?>' onBlur="trim(this);" >
        </td></tr>        
    <tr><td colspan="2">Liberar Avaliação do Evento antes da emissão do certificado:
            <input type="radio" name="liberar_avaliar_evento" value="1" <?= verifItemChecked($objEvConf->getLiberarAvaliarEvento(), '1', false) ?> /> Sim 
            &nbsp;&nbsp;
            <input type="radio" name="liberar_avaliar_evento" value="0" <?= verifItemChecked($objEvConf->getLiberarAvaliarEvento(), array('', 0, false), false) ?> /> Não
        </td></tr>    
    <tr><td>Url do Form. de Avaliação</td><td> <input type='text'       name='url_avaliar_evento'       value='<?php echo $objEvConf->getUrlAvaliarEvento() ?>'    maxlength='255'   size='60'  onBlur="trim(this);" ></td></tr>    
    <tr><td>Certificado com selo na frente:        </td><td> 
            <input type="radio" name="certificado_selo" value="1" <?= verifItemChecked($objEvConf->getCertificadoSelo(), '1', false) ?> /> Sim 
            &nbsp;&nbsp;
            <input type="radio" name="certificado_selo" value="0" <?= verifItemChecked($objEvConf->getCertificadoSelo(), array('', 0, false), false) ?> /> Não
        </td></tr>
    <tr><td>Certificado com duas assinaturas:        </td><td> 
            <input type="radio" name="certificado_assinatura_dupla" value="1" <?= verifItemChecked($objEvConf->getCertificadoAssinaturaDupla(), '1', false) ?> /> Sim 
            &nbsp;&nbsp;
            <input type="radio" name="certificado_assinatura_dupla" value="0" <?= verifItemChecked($objEvConf->getCertificadoAssinaturaDupla(), array('', 0, false), false) ?> /> Não
        </td></tr>       
    <tr><td>Liberar Certificado Congressista/Participante a todos:        </td><td> 
            <input type="radio" name="certificado_congressista" value="1" <?= verifItemChecked($objEvConf->getCertificadoCongressista(), '1', false) ?> /> Sim 
            &nbsp;&nbsp;
            <input type="radio" name="certificado_congressista" value="0" <?= verifItemChecked($objEvConf->getCertificadoCongressista(), array('', 0, false), false) ?> /> Não
        </td></tr>   
        <tr><td>Texto p/Substituir qualidade de "MEMBRO EFETIVO"</td><td> <input type='text'       name='certificado_congressista_qualidade'       value='<?php echo $objEvConf->getCertificadoCongressistaQualidade() ?>'    maxlength='255'   size='70'  onBlur="trim(this);" ></td></tr>    
        <tr><td>Texto p/Substituir "Carga Horária: 15h"</td><td> <input type='text'       name='certificado_congressista_carga_horaria'       value='<?php echo $objEvConf->getCertificadoCongressistaCargaHoraria() ?>'    maxlength='255'   size='70'  onBlur="trim(this);" ></td></tr>                
    <tr><td>Liberar Certificado Publicação de Trabalhos para Aprovados mas não Apresentados:        </td><td> 
            <input type="radio" name="certificado_trabalho_nao_apresentado" value="1" <?= verifItemChecked($objEvConf->getCertificadoTrabalhoNaoApresentado(), '1', false) ?> /> Sim 
            &nbsp;&nbsp;
            <input type="radio" name="certificado_trabalho_nao_apresentado" value="0" <?= verifItemChecked($objEvConf->getCertificadoTrabalhoNaoApresentado(), array('', 0, false), false) ?> /> Não
        </td></tr>       
    <tr><td colspan="2"><span style="font-size:15px;font-weight: bold;">Configuração do Texto do Certificado</span></td></tr>          
    <tr><td>Texto p/Substituir "CONFERIDO A"</td><td> <input type='text'       name='certificado_texto_inicial'       value='<?php echo $objEvConf->getCertificadoTextoInicial() ?>'    maxlength='255'   size='70'  onBlur="trim(this);" ></td></tr>    
    <tr><td>Exibir no texto " na qualidade de ...":        </td><td> 
            <input type="radio" name="certificado_exibir_qualidade" value="1" <?= verifItemChecked($objEvConf->getCertificadoExibirQualidade(), '1', false) ?> /> Sim 
            &nbsp;&nbsp;
            <input type="radio" name="certificado_exibir_qualidade" value="0" <?= verifItemChecked($objEvConf->getCertificadoExibirQualidade(), array('', 0, false), false) ?> /> Não
        </td></tr>
    <tr><td>Texto p/Substituir "na qualidade de"</td><td> <input type='text'       name='certificado_texto_qualidade'       value='<?php echo $objEvConf->getCertificadoTextoQualidade() ?>'    maxlength='255'   size='70'  onBlur="trim(this);" ></td></tr>  

    <tr><td>Texto p/Substituir "CONFERIDO ao TRABALHO"</td><td> <input type='text'       name='certificado_texto_inicial_trabalho'       value='<?php echo $objEvConf->getCertificadoTextoInicialTrabalho() ?>'    maxlength='255'   size='70'  onBlur="trim(this);" ></td></tr>      
    <tr><td>Texto No ou Na</td><td> <input type='text'       name='certificado_texto_no'       value='<?php echo $objEvConf->getCertificadoTextoNo() ?>'    maxlength='255'   size='70'  onBlur="trim(this);" ></td></tr>      
    <tr><td>Texto Do ou Da</td><td> <input type='text'       name='certificado_texto_do'       value='<?php echo $objEvConf->getCertificadoTextoDo() ?>'    maxlength='255'   size='70'  onBlur="trim(this);" ></td></tr>      
    <tr><td>Texto p/Substituir "Realizado"</td><td> <input type='text'       name='certificado_texto_reallizado'       value='<?php echo $objEvConf->getCertificadoTextoReallizado() ?>'    maxlength='255'   size='70'  onBlur="trim(this);" ></td></tr>
    <tr><td>Promovido por (aparece no certificado se preenchido)</td><td> <input type='text'       name='certificado_promovido_por'       value='<?php echo $objEvConf->getCertificadoPromovidoPor() ?>'    maxlength='255'   size='70'  onBlur="trim(this);" ></td></tr>
</table>
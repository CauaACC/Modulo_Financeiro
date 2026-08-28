<SCRIPT LANGUAGE="JavaScript1.2" src="resources/js/oficina-cadastro.js"></SCRIPT>

<?php
//$objOficina= new Oficina();
//if($COD_OFICINA){
//    $objOficina->setCodOficina($COD_OFICINA);
//    $objOficina->getOficinaById($debug);
//}
#require_once(dirname(__FILE__)."/../../include/config.php");
$FWA_Option_Dia    = FWA_Option_Dia();
$FWA_Option_Mes    = FWA_Option_Mes();
$FWA_Option_Ano    = FWA_Option_Ano(3);
$FWA_Option_Hora   = FWA_Option_Hora();
$FWA_Option_Minuto = FWA_Option_Minuto();


#$STATUS="DESATIVADO";

If(isset($STATUS)){
  $OPTION_STATUS = "<option selected value='{$STATUS}'>{$STATUS}</option>";
}Else{
  $OPTION_STATUS = "<option selected value='-1'>Alterar Status</option>";
}

function mostrarAjuda($value){
    ?>
    <a href="javascript:void(0);alert('<?php echo $value ?>')" title="<?php echo $value ?>" >??</a>
    <?php
}
?>

<input type='hidden' name='COD_EVENTO' value='<?php echo $COD_EVENTO?>'>
<style>
label { font-weight: normal; }
</style>
        <div class="form-group col-sm-12" style="padding-left: 0px;">
            <div class="form-group col-sm-2" style="padding-left: 0px;">
                <label for="status">Status:</label>
                <?php if ($camposEditar==false){ echo $objOficina->getStatus(); }else{ ?>
                <select class='form-control' name='status'>
                    <option <?php echo verifItemSelected($objOficina->getStatus(), 'ATIVADA')  ?> value='ATIVADA'>ATIVADA</option>
                    <option <?php echo verifItemSelected($objOficina->getStatus(), 'DESATIVA') ?> value='DESATIVA'>DESATIVA</option>
                    <option <?php echo verifItemSelected($objOficina->getStatus(), 'EXCLUÍDA') ?> value='EXCLUÍDA'>EXCLUÍDA</option>
                </select>
                <?php } ?>
            </div>
            <div class="form-group col-sm-2" style="padding-left: 0px;">
                <label for="status_inscricao">Status Inscrição:</label>
                <?php if ($camposEditar==false){ echo $objOficina->getStatusInscricao(); }else{ ?>
                <select class='form-control' name='status_inscricao'>
                    <option <?php echo verifItemSelected($objOficina->getStatusInscricao(), 'ATIVADA')  ?> value='ATIVADA'>ATIVADA</option>
                    <option <?php echo verifItemSelected($objOficina->getStatusInscricao(), 'SUSPENSA') ?> value='SUSPENSA'>SUSPENSA</option>
                    <option <?php echo verifItemSelected($objOficina->getStatusInscricao(), '') ?> value='SUSPENSA'>SUSPENSA</option>
                </select>
                <?php } ?>
            </div>
            <div class="form-group col-sm-3" style="padding-left: 0px;">
                <label for="receber_inscricao">Receber Inscrição:</label>
                <?php if ($camposEditar==false){ echo $objOficina->getReceberInscricao(); }else{ ?>
                <select class='form-control' name='receber_inscricao'>
                    <option <?php echo verifItemSelected($objOficina->getReceberInscricao(), '1')  ?> value='1'>SIM</option>
                    <option <?php echo verifItemSelected($objOficina->getReceberInscricao(), '0') ?> value='0'>NÃO</option>
                    <option <?php echo verifItemSelected($objOficina->getReceberInscricao(), '') ?> value='0'>NÃO</option>
                </select>
                <?php } ?>
            </div>
            <div class="form-group col-sm-3" style="padding-left: 0px;">
                <label for="liberar_certificado">Liberar Certificado:</label>
                <?php if ($camposEditar==false){ echo $objOficina->getLiberarCertificado(); }else{ ?>
                <select class='form-control' name='liberar_certificado'>
                    <option <?php echo verifItemSelected($objOficina->getLiberarCertificado(), '1')  ?> value='1'>SIM</option>
                    <option <?php echo verifItemSelected($objOficina->getLiberarCertificado(), '0') ?>  value='0'>NÃO</option>
                    <option <?php echo verifItemSelected($objOficina->getLiberarCertificado(), '') ?> value=''>NÃO</option>
                </select>
                <?php } ?>
            </div>
        </div>
        <div class="form-group col-sm-12" style="padding-left: 0px;">            
            <div class="form-group col-sm-3" style="padding-left: 0px;">
                <label for="tipo">Modalidade da Opção:</label>
                <?php if ($camposEditar==false){ echo $objOficina->getTipo(); }else{ ?>
                <select class='form-control' name="tipo" id="tipo">
                    <option></option>
                    <?php
                    $tipo_atividade = $objOficina->getTipo();
                    CarregarOptionsAC::tipoAtividades($tipo_atividade, $debug);
                    ?>
                </select>
                <?php } ?>
            </div>   
            <div class="form-group col-sm-2" style="padding-left: 0px;">
                <label for="numero_oficial">Número Oficial:</label>
                <?php if ($camposEditar==false){ echo $objOficina->getNumeroOficial(); }else{ ?>
                <input class='form-control' type='text' name='numero_oficial' value='<?php echo $objOficina->getNumeroOficial()?>'
                  size='60' maxlength='255' onBlur='trim(this);'>
                <?php } ?>
            </div>
            <div class="form-group col-sm-2" style="padding-left: 0px;">
                <label for="ordem">Ordem: </label>
                <?php if ($camposEditar==false){ echo $objOficina->getOrdem(); }else{ ?>
                <input class='form-control' type='text' name='ordem' value='<?php echo $objOficina->getOrdem()?>'
                  size='60' maxlength='255'onBlur='trim(this);'>
                <?php } ?>
            </div>
            <div class="form-group col-sm-3" style="padding-left: 0px;">
                <label for="numero_vagas">Número de Vagas:</label>
                <?php if ($camposEditar==false){ echo $objOficina->getNumeroVagas(); }else{ ?>
                <input class='form-control' type='text' name='numero_vagas' value='<?php echo $objOficina->getNumeroVagas()?>'
                  size='60' maxlength='255' onBlur='trim(this);'>
                <?php } ?>
            </div>
        </div>
        <div class="form-group col-sm-12" style="padding-left: 0px;">
            <div class="form-group col-sm-12" style="padding-left: 0px;">
                <label for="titulo">Titulo:</label>
                <?php if ($camposEditar==false){ echo $objOficina->getTitulo(); }else{ ?>
                <input class='form-control' type='text' name='titulo' value='<?php echo $objOficina->getTitulo()?>' 
                  required  size='60' maxlength='255' onBlur='trim(this);'>
                <?php } ?>
            </div>
        </div>
        <?php if ($objOficina->getTipo()!='Agenda' and $objOficina->getTipo()!='Horario') { ?>
        <div class="form-group col-sm-12" style="padding-left: 0px;">
            <div class="form-group col-sm-12" style="padding-left: 0px;">
                <label for="titulo_certificado">Titulo para Certificado:</label>
                <?php if ($camposEditar==false){ echo $objOficina->getTituloCertificado(); }else{ ?>
                <input class='form-control' type='text' name='titulo_certificado' value='<?php echo $objOficina->getTituloCertificado()?>' 
                   size='60' maxlength='255' onBlur='trim(this);'>
                <?php } ?>
            </div>
        </div>
        <?php } ?>
        <?php if ($objOficina->getTipo()!='Agenda' and $objOficina->getTipo()!='Horario') { ?>
        <div class="form-group col-sm-12" style="padding-left: 0px;">
            <div class="form-group col-sm-12" style="padding-left: 0px;">
                <label for="qualificacao_certificado">Texto Qualificação para Certificado: <?php mostrarAjuda("ex: O Certificado [TEXTO QUALIFICACAO], por sua participação") ?></label>
                <?php if ($camposEditar==false){ echo $objOficina->getQualificacaoCertificado(); }else{ ?>
                <input class='form-control' type='text' name='qualificacao_certificado' value='<?php echo $objOficina->getQualificacaoCertificado()?>' 
                  size='60' maxlength='255' onBlur='trim(this);'>
                <?php } ?>
            </div>
        </div>
        <?php } ?>
        <?php if ($objOficina->getTipo()!='Agenda' and $objOficina->getTipo()!='Horario') { ?>
        <div class="form-group col-sm-12" style="padding-left: 0px;">
            <div class="form-group col-sm-6" style="padding-left: 0px;">
                <label for="periodo_certificado">Período do Curso para Certificado: <?php mostrarAjuda("ex: 19 de Setembro a 20 de Outubro de 2022") ?></label>
                <?php if ($camposEditar==false){ echo $objOficina->getPeriodoCertificado(); }else{ ?>
                <input class='form-control' type='text' name='periodo_certificado' value='<?php echo $objOficina->getPeriodoCertificado()?>' 
                   size='60' maxlength='255' onBlur='trim(this);'>
                <?php } ?>
            </div>
            <div class="form-group col-sm-4" style="padding-left: 0px;">
                <label for="data_emissao_certificado">Data de Emissão para Certificado: <?php mostrarAjuda("ex: Londrina (PR), 20 de Outubro de 2022") ?></label>
                <?php if ($camposEditar==false){ echo $objOficina->getDataEmissaoCertificado(); }else{ ?>
                <input class='form-control' type='text' name='data_emissao_certificado' value='<?php echo $objOficina->getDataEmissaoCertificado()?>'
                  size='60' maxlength='255' onBlur='trim(this);'>
                <?php } ?>
            </div>
        </div>
        <?php } ?>
        <?php if ($objOficina->getTipo()!='Agenda' and $objOficina->getTipo()!='Horario') { ?>
        <div class="form-group col-sm-12" style="padding-left: 0px;">
            <div class="form-group col-sm-6" style="padding-left: 0px;">
                <label for="nome_ministrante">Nome Coordenador:</label>
                <?php if ($camposEditar==false){ echo $objOficina->getNomeMinistrante(); }else{ ?>
                <input class='form-control' type='text' name='nome_ministrante' value='<?php echo $objOficina->getNomeMinistrante()?>' 
                   size='60' maxlength='255' onBlur='trim(this);' onkeypress='javascript: return isAlfa(window.event.keyCode);'>
                <?php } ?>
            </div>
            <div class="form-group col-sm-4" style="padding-left: 0px;">
                <label for="e_mail_ministrante">E-mail Coodenador:</label>
                <?php if ($camposEditar==false){ echo $objOficina->getEMailMinistrante(); }else{ ?>
                <input class='form-control' type='text' name='e_email_ministrante' value='<?php echo $objOficina->getEMailMinistrante()?>'
                  size='60' maxlength='255' onBlur='trim(this);'>
                <?php } ?>
            </div>
            <div class="form-group col-sm-2" style="padding-left: 0px;">
                <label for="fone_ministrante">Contato Coodenador:</label>
                <?php if ($camposEditar==false){ echo $objOficina->getFonteMinistrante(); }else{ ?>
                <input class='form-control' type='text' name='fonte_ministrante' value='<?php echo $objOficina->getFonteMinistrante()?>'
                  size='60' maxlength='255' onBlur='trim(this);'>
                <?php } ?>
            </div>
        </div>
        <?php } ?>
        <div class="form-group col-sm-12" style="padding-left: 0px;">
            <?php if ($objOficina->getTipo()!='Agenda' and $objOficina->getTipo()!='Horario') { ?>
            <div class="form-group col-sm-6" style="padding-left: 0px;">
                <label for="local">Local onde será Ministrado:</label>
                <?php if ($camposEditar==false){ echo $objOficina->getLocal(); }else{ ?>
                <input class='form-control' type='text' name='local' value='<?php echo $objOficina->getLocal()?>' 
                   size='60' maxlength='255' onBlur='trim(this);' onkeypress='javascript: return isAlfa(window.event.keyCode);'>
                <?php } ?>
            </div>
            <?php } ?>
            <?php if ($objOficina->getTipo()!='Agenda' and $objOficina->getTipo()!='Horario') { ?>
            <div class="form-group col-sm-2" style="padding-left: 0px;">
                <label for="carga_horaria">Carga Horária: (em horas):</label>
                <?php if ($camposEditar==false){ echo $objOficina->getCargaHoraria(); }else{ ?>
                <input class='form-control' type='text' name='carga_horaria' value='<?php echo $objOficina->getCargaHoraria()?>'
                  size='60' maxlength='255' onBlur='trim(this);'>
                <?php } ?>
            </div>
            <div class="form-group col-sm-2" style="padding-left: 0px;">
                <label for="carga_horaria_extenso">CH. Extenso: (em horas):</label>
                <?php if ($camposEditar==false){ echo $objOficina->getCargaHorariaExtenso(); }else{ ?>
                <input class='form-control' type='text' name='carga_horaria_extenso' value='<?php echo $objOficina->getCargaHorariaExtenso()?>'
                  size='60' maxlength='255' onBlur='trim(this);'>
                <?php } ?>
            </div>
            <?php } ?>
        </div>
<!--
        <div class="form-group col-sm-12" style="padding-left: 0px;">
            <div class="form-group col-sm-6" style="padding-left: 0px;">
                <label for="periodo_inscricao_data_ini">Período de Inscrição Inicio</label>
                <input class='form-control' type='datetime-local' name='periodo_inscricao_data_ini' value='<?php echo $objOficina->getPeriodoInscricaoDataIni()?>' 
                    size='60' maxlength='255' onBlur='trim(this);' onkeypress='javascript: return isAlfa(window.event.keyCode);'>
            </div>
            <div class="form-group col-sm-6" style="padding-left: 0px;">
                <label for="periodo_inscricao_data_fim">Período de Inscrição Fim</label>
                <input class='form-control' type='datetime-local' name='periodo_inscricao_data_fim' value='<?php echo $objOficina->getPeriodoInscricaoDataFim()?>' 
                    size='60' maxlength='255' onBlur='trim(this);' onkeypress='javascript: return isAlfa(window.event.keyCode);'>
            </div>
        </div>
-->
        <div class="form-group col-sm-12" style="padding-left: 0px;">
            <div class="form-group col-sm-6" style="padding-left: 0px;">
                <label for="data_inicio_inscricao">Data Inicio Inscrição:</label>
                <?php if ($camposEditar==false){ echo $objOficina->getDataInicioInscricao(); }else{ ?>
                <input class='form-control' type='datetime-local' name='data_inicio_inscricao' id='data_inicio_inscricao' value='<?php echo formatDateHoraTrans($objOficina->getDataInicioInscricao(),'YYYY-MM-DD HH:II:SS','YYYY-MM-DDTHH:II') ?>' 
                    size='60' maxlength='255' onBlur='trim(this);' onkeypress='javascript: return isAlfa(window.event.keyCode);'>
                <?php } ?>
            </div>
            <div class="form-group col-sm-6" style="padding-left: 0px;">
                <label for="data_fim_inscricao">Data Fim Inscrição:</label>
                <?php if ($camposEditar==false){ echo $objOficina->getDataFimInscricao(); }else{ ?>
                <input class='form-control' type='datetime-local' name='data_fim_inscricao' id='data_fim_inscricao' value='<?php echo formatDateHoraTrans($objOficina->getDataFimInscricao(),'YYYY-MM-DD HH:II:SS','YYYY-MM-DDTHH:II')?>' 
                    size='60' maxlength='255' onBlur='trim(this);' onkeypress='javascript: return isAlfa(window.event.keyCode);'>
                <?php } ?>
            </div>
        </div>
<!--        
        <div class="form-group col-sm-12" style="padding-left: 0px;">
            <div class="form-group col-sm-6" style="padding-left: 0px;">
                <label for="data_inicio_curso">Dt de Inicio Curso</label>
                <input class='form-control' type='datetime-local' name='data_inicio_curso' value='<?php echo $objOficina->getDataInicioCurso()?>' 
                    size='60' maxlength='255' onBlur='trim(this);' onkeypress='javascript: return isAlfa(window.event.keyCode);'>
            </div>
            <div class="form-group col-sm-6" style="padding-left: 0px;">
                <label for="data_fim_curso">Dt de Fim Curso</label>
                <input class='form-control' type='datetime-local' name='data_fim_curso' value='<?php echo $objOficina->getDataFimCurso()?>' 
                    size='60' maxlength='255' onBlur='trim(this);' onkeypress='javascript: return isAlfa(window.event.keyCode);'>
            </div>
        </div>
-->
        <div class="form-group col-sm-12" style="padding-left: 0px;">
            <?php if ($objOficina->getTipo()!='Agenda') { ?>
            <div class="form-group col-sm-4" style="padding-left: 0px;">
                <label for="data_curso">Dt. Atividade:</label>
                <?php if ($camposEditar==false){ echo $objOficina->getDataCurso(); }else{ ?>
                <input class='form-control' type='date' name='data_curso' value='<?php echo formatDateInsert($objOficina->getDataCurso())?>' 
                    size='60' maxlength='255' onBlur='trim(this);' onkeypress='javascript: return isAlfa(window.event.keyCode);'>
                <?php } ?>
            </div>
            <?php } ?>
            <div class="form-group col-sm-4" style="padding-left: 0px;">
                <label for="data_inicio">Dt.Inicio Atividade:</label>
                <?php if ($camposEditar==false){ echo $objOficina->getDataInicio(); }else{ ?>
                <input class='form-control' type='date' name='data_inicio' value='<?php echo $objOficina->getDataInicio()?>' 
                    size='60' maxlength='255' onBlur='trim(this);' onkeypress='javascript: return isAlfa(window.event.keyCode);'>
                    <!-- Campo de Hora -->
                    <label for="horario_ini">Hora Inicio:</label>
                    <input type="text" id="horario_ini" name="horario_ini" pattern="\d{2}h\d{2}" maxlength="5" value='<?php echo $objOficina->getHorarioIni()?>'> (ex: 09h00)
                <?php } ?>
            </div>
            <?php if ($objOficina->getTipo()!='Agenda') { ?>
            <div class="form-group col-sm-4" style="padding-left: 0px;">
                <label for="data_fim">Dt.Fim Atividade:</label>
                <?php if ($camposEditar==false){ echo $objOficina->getDataFim(); }else{ ?>
                <input class='form-control' type='date' name='data_fim' value='<?php echo $objOficina->getDataFim()?>' 
                    size='60' maxlength='255' onBlur='trim(this);' onkeypress='javascript: return isAlfa(window.event.keyCode);'>
                    <!-- Campo de Hora -->
                    <label for="horario_fim">Hora Fim:</label>
                    <input type="text" id="horario_fim" name="horario_fim" pattern="\d{2}h\d{2}"  maxlength="5" value='<?php echo $objOficina->getHorarioFim()?>'>  (ex: 09h00)
                <?php } ?>
            </div>
            <?php } ?>
        </div>
        <?php if ($objOficina->getTipo()!='Agenda') { ?>
        <div class="form-group col-sm-12" style="padding-left: 0px;">
            <div class="form-group col-sm-12" style="padding-left: 0px;">
                <label for="nome_instituicao">Nome da Instituicao:</label>
                <?php if ($camposEditar==false){ echo $objOficina->getNomeInstituicao(); }else{ ?>
                <input class='form-control' type='text' name='nome_instituicao' value='<?php echo $objOficina->getNomeInstituicao()?>' 
                    size='60' maxlength='255' onBlur='trim(this);' onkeypress='javascript: return isAlfa(window.event.keyCode);'>
                <?php } ?>
            </div>
        </div>
        <?php } ?>
        <?php if($objEvConf->getAtividadeValorVariavel()) { ?>
        <div class="form-group col-sm-12" style="padding-left: 0px;">
            <div class="form-group col-sm-12" style="padding-left: 0px;">
                <label for="valor_atividade">Valor Inscrição na Atividade:</label> (ex: 999.00)
                <?php if ($camposEditar==false){ echo $objOficina->getValorAtividade(); }else{ ?>
                R$ <input class='form-control' type='text' name='valor_atividade' value='<?php echo $objOficina->getValorAtividade()?>' 
                    size='60' maxlength='255' onBlur='trim(this);'>
                <?php } ?>
            </div>
        </div>
        <?php } ?>

        <div class="form-group col-sm-2" style="padding-left: 0px;">
            <label for="link_aula">Link Aula Publicar:</label>
            <?php if ($camposEditar==false){ echo $objOficina->getLinkAulaPublicar(); }else{ ?>
            <select class='form-control' name='link_aula_publicar'>
                <option <?php echo verifItemSelected($objOficina->getLinkAulaPublicar(), '1')  ?> value='1'>SIM</option>
                <option <?php echo verifItemSelected($objOficina->getLinkAulaPublicar(), '0') ?> value='0'>NÃO</option>
                <option <?php echo verifItemSelected($objOficina->getLinkAulaPublicar(), '') ?> value='0'>NÃO</option>
            </select>
            <?php } ?>
        </div>

        <div class="form-group col-sm-10" style="padding-left: 0px;">
            <label for="link_aula">Link Aula:</label>
            <?php if ($camposEditar==false){ echo $objOficina->getLinkAula(); }else{ ?>
                <input class='form-control' type='text' name='link_aula' value='<?php echo $objOficina->getLinkAula()?>' 
                  size='60' maxlength='255' onBlur='trim(this);' onkeypress='javascript: return isAlfa(window.event.keyCode);'>
            <?php } ?>
        </div>
        <div class="form-group col-sm-12" style="padding-left: 0px;">
            <div class="form-group col-sm-12" style="padding-left: 0px;">
                <label for="link_aula_complemento">Link Aula Complemento:</label>
                <?php if ($camposEditar==false){ echo $objOficina->getLinkAulaComplemento(); }else{ ?>
                <textarea class=editor_padrao rows=5 cols=70 name='link_aula_complemento'       
                size='100' onBlur='trim(this);' onkeypress='javascript: return isAlfa(window.event.keyCode);' class='textob form_control'><?php echo $objOficina->getLinkAulaComplemento()?></textarea>
                <?php } ?>
            </div>
        </div>
        <div class="form-group col-sm-12" style="padding-left: 0px;">
            <div class="form-group col-sm-12" style="padding-left: 0px;">
                <label for="descricao">Descrição:</label>
                <?php if ($camposEditar==false){ echo $objOficina->getDescricao(); }else{ ?>
                <textarea class=editor_padrao rows=5 cols=70 name='descricao'       
                size='100' onBlur='trim(this);' onkeypress='javascript: return isAlfa(window.event.keyCode);' class='textob form_control'><?php echo $objOficina->getDescricao()?></textarea>
                <?php } ?>
            </div>
        </div>

<?php if ($objOficina->getTipo()!='Agenda' and $objOficina->getTipo()!='Horario') { ?>
<?php if ($camposEditar==true){ ?>
        <div class="form-group col-sm-12" style="padding-left: 0px;">
            <div class="form-group col-sm-12" style="padding-left: 0px;">
                <label for="palavras_chave">Palavras Chave:</label>
                <?php if ($camposEditar==false){ echo $objOficina->getPalavrasChave(); }else{ ?>
                <input class='form-control' type='text' name='palavras_chave' value='<?php echo $objOficina->getPalavrasChave()?>' 
                  size='60' maxlength='255' onBlur='trim(this);' onkeypress='javascript: return isAlfa(window.event.keyCode);'>
                <?php } ?>
            </div>
        </div>
        <div class="form-group col-sm-12" style="padding-left: 0px;">
            <div class="form-group col-sm-12" style="padding-left: 0px;">
                <label for="ementa">Ementa:</label>
                <?php if ($camposEditar==false){ echo $objOficina->getEmenta(); }else{ ?>
                <textarea class=editor_padrao rows=5 cols=70 name='ementa'       
                size='100' onBlur='trim(this);' onkeypress='javascript: return isAlfa(window.event.keyCode);' class='textob form_control'><?php echo $objOficina->getEmenta()?></textarea>
                <?php } ?>
            </div>
        </div>
        <div class="form-group col-sm-12" style="padding-left: 0px;">
            <div class="form-group col-sm-12" style="padding-left: 0px;">
                <label for="publico_alvo">Público Alvo:</label>
                <?php if ($camposEditar==false){ echo $objOficina->getPublicoAlvo(); }else{ ?>
                <textarea class=editor_padrao rows=5 cols=70 name='publico_alvo'       
                size='100' onBlur='trim(this);' onkeypress='javascript: return isAlfa(window.event.keyCode);' class='textob form_control'><?php echo $objOficina->getPublicoAlvo()?></textarea>
                <?php } ?>
            </div>
        </div>
        <div class="form-group col-sm-12" style="padding-left: 0px;">
            <div class="form-group col-sm-12" style="padding-left: 0px;">
                <label for="objetivo">Objetivo:</label>
                <?php if ($camposEditar==false){ echo $objOficina->getObjetivo(); }else{ ?>
                <textarea class=editor_padrao rows=5 cols=70 name='objetivo'       
                size='100' onBlur='trim(this);' onkeypress='javascript: return isAlfa(window.event.keyCode);' class='textob form_control'><?php echo $objOficina->getObjetivo()?></textarea>
                <?php } ?>
            </div>
        </div>
        <div class="form-group col-sm-12" style="padding-left: 0px;">
            <div class="form-group col-sm-12" style="padding-left: 0px;">
                <label for="pre_requisito">Pré-Requisitos:</label>
                <?php if ($camposEditar==false){ echo $objOficina->getPreRequisito(); }else{ ?>
                <textarea class=editor_padrao rows=5 cols=70 name='pre_requisito'       
                size='100' onBlur='trim(this);' onkeypress='javascript: return isAlfa(window.event.keyCode);' class='textob form_control'><?php echo $objOficina->getPreRequisito()?></textarea>
                <?php } ?>
            </div>
        </div>
        <div class="form-group col-sm-12" style="padding-left: 0px;">
            <div class="form-group col-sm-12" style="padding-left: 0px;">
                <label for="observacao">Observacao:</label>
                <?php if ($camposEditar==false){ echo $objOficina->getObservacao(); }else{ ?>
                <textarea class=editor_padrao rows=5 cols=70 name='observacao'       
                size='100' onBlur='trim(this);' onkeypress='javascript: return isAlfa(window.event.keyCode);' class='textob form_control'><?php echo $objOficina->getObservacao()?></textarea>
                <?php } ?>
            </div>
        </div>
        <div class="form-group col-sm-12" style="padding-left: 0px;">
            <div class="form-group col-sm-12" style="padding-left: 0px;">
                <label for="mais_informacao">Mais Informações:</label>
                <?php if ($camposEditar==false){ echo $objOficina->getMaisInformacao(); }else{ ?>
                <textarea class=editor_padrao rows=5 cols=70 name='mais_informacao'       
                size='100' onBlur='trim(this);' onkeypress='javascript: return isAlfa(window.event.keyCode);' class='textob form_control'><?php echo $objOficina->getMaisInformacao()?></textarea>
                <?php } ?>
            </div>
        </div>
<?php } ?>
<?php } ?>

<script type="text/javascript" src="resources/js/tinymce_4.3.0/js/tinymce/tinymce.min.js"></script>
<script type="text/javascript" src="resources/js/tinymce-charcount.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        <?php if ($camposEditar==true){ ?>
        tinymce.init({
            selector: "textarea.editor_padrao"
            , plugins: "code textcolor link table"
            , height: 400
            , max_height: 400
            ,resize: "both"
            ,menubar: false
            ,statusbar: true
            ,block_formats: 'Header 1=h1;Header 2=h2;Header 3=h3'
            ,force_br_newlines: true
            ,force_p_newlines: false
            ,forced_root_block: ''
            ,apply_source_formatting: true
            ,convert_newlines_to_brs: true
            ,remove_linebreaks: false
            , icons: 'fa'
  , content_css: '//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css'

            , toolbar: "customIcon | code | undo redo | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist | forecolor backcolor fontsizeselect | link | table | icon"
            , setup: function (editor) { tinyMceCharCount.attach(editor, 5000); }
            , init_instance_callback: function (editor) {
                $('.mce-tinymce').show('fast');
                $(editor.getContainer()).find(".mce-path").css("display", "none");
            },
            textcolor_map: [
                "FF0000", "Red",
                "008B45", "Green",
                "0000FF", "Blue"
            ],
            fontsize_formats: "10pt 12pt 14pt 18pt 24pt 36pt",
        });
        <?php } ?>
    });
</script>


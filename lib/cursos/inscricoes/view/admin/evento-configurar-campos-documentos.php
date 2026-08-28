<table border='0' width='570'  cellspacing="10" cellpadding="10" class="dataTable">
    <tr><td colspan="2"><span style="font-size:15px;font-weight: bold;">Configuração de Documentos</span></td></tr>
           <tr><td>Comprovante de inscrição:   </td><td> 
        <?php espelhoSimNao('emitir_comprovante', $objEvConf->getEmitirComprovante()) ?>
    </td></tr>
    <tr><td>Comprovante de pagamento / recibo:   </td><td>
            <?php espelhoSimNao('emitir_recibo', $objEvConf->getEmitirRecibo()) ?>
        </td></tr>
    <tr><td>Solicita&ccedil;&atilde;o de Nota Fiscal:</td><td>
            <?php espelhoSimNao('solicitacao_nf_habilitada', $objEvConf->getSolicitacaoNfHabilitada()) ?>
        </td></tr>
    <tr><td>E-mail do gestor financeiro (NF):</td><td>
            <input type='text' name='email_gestor_nf' value='<?php echo $objEvConf->getEmailGestorNf() ?>' maxlength='255' size='60' onBlur="trim(this);" placeholder="Destinatário das notificações de solicitação de NF">
        </td></tr>
    <tr><td colspan="2">Permitir Inscrição/Recibo em nome de terceiro ou empresa:
            <input type="radio" name="emitir_recibo_terceiro" value="1" <?= verifItemChecked($objEvConf->getEmitirReciboTerceiro(), '1', false) ?> /> Sim 
            &nbsp;&nbsp;
            <input type="radio" name="emitir_recibo_terceiro" value="0" <?= verifItemChecked($objEvConf->getEmitirReciboTerceiro(), array('', 0, false), false) ?> /> Não
        </td></tr>
    <tr><td>Carta de aceite:          </td><td> 
            <input type="radio" name="emitir_carta_aceite" value="1" <?= verifItemChecked($objEvConf->getEmitirCartaAceite(), '1', false) ?> /> Sim 
            &nbsp;&nbsp;
            <input type="radio" name="emitir_carta_aceite" value="0" <?= verifItemChecked($objEvConf->getEmitirCartaAceite(), array('', 0, false), false) ?> /> Não
        </td></tr>
    <tr><td>Declaração de presença:   </td><td> 
            <?php espelhoSimNao('emitir_declaracao_presenca', $objEvConf->getEmitirDeclaracaoPresenca()) ?>
        </td></tr>
    <tr><td>Emitir listas de chamada: </td><td> 
            <input type="radio" name="emitir_lista_chamada" value="1" <?= verifItemChecked($objEvConf->getEmitirListaChamada(), '1', false) ?> /> Sim 
            &nbsp;&nbsp;
            <input type="radio" name="emitir_lista_chamada" value="0" <?= verifItemChecked($objEvConf->getEmitirListaChamada(), array('', 0, false), false) ?> /> Não
        </td></tr>
    <tr><td>Emitir crachas:           </td><td> 
            <input type="radio" name="emitir_cracha" value="1" <?= verifItemChecked($objEvConf->getEmitirCracha(), '1', false) ?> /> Sim 
            &nbsp;&nbsp;
            <input type="radio" name="emitir_cracha" value="0" <?= verifItemChecked($objEvConf->getEmitirCracha(), array('', 0, false), false) ?> /> Não
        </td></tr>
    <tr><td>Emissão de anais:         </td><td> 
            <input type="radio" name="emitir_anais" value="1" <?= verifItemChecked($objEvConf->getEmitirAnais(), '1', false) ?> /> Sim 
            &nbsp;&nbsp;
            <input type="radio" name="emitir_anais" value="0" <?= verifItemChecked($objEvConf->getEmitirAnais(), array('', 0, false), false) ?> /> Não
        </td></tr>
    <tr><td>Exportar de edital:         </td><td> 
            <input type="radio" name="exportar_edital" value="1" <?= verifItemChecked($objEvConf->getExportarEdital(), '1', false) ?> /> Sim 
            &nbsp;&nbsp;
            <input type="radio" name="exportar_edital" value="0" <?= verifItemChecked($objEvConf->getExportarEdital(), array('', 0, false), false) ?> /> Não
        </td></tr>
    <tr><td>Exportar acerto:         </td><td>
            <input type="radio" name="exportar_acerto" value="1" <?= verifItemChecked($objEvConf->getExportarAcerto(), '1', false) ?> /> Sim
            &nbsp;&nbsp;
            <input type="radio" name="exportar_acerto" value="0" <?= verifItemChecked($objEvConf->getExportarAcerto(), array('', 0, false), false) ?> /> Não
        </td></tr>
    <tr><td>Modelos de contrato (download pelo inscrito):   </td><td>
            <?php espelhoSimNao('habilitar_modelos_contrato', $objEvConf->getHabilitarModelosContrato()) ?>
            <br><small style="color:#888; font-size:11px;">
                Permite o inscrito baixar contratos/documentos preenchidos com seus dados cadastrais.<br>
                Ap&oacute;s ativar, cadastre os modelos DOCX em <strong>Outros &raquo; Modelos de Contrato</strong>.<br>
                Use placeholders no template: <code>${NOME}</code> <code>${CPF}</code> <code>${RG}</code> <code>${NASCIMENTO}</code>
                <code>${ESTADO_CIVIL}</code> <code>${PROFISSAO}</code> <code>${CARGO_ATUAL}</code> <code>${GRAU_ESCOLARIDADE}</code>
                <code>${ENDERECO}</code> <code>${NUMERO}</code>
                <code>${BAIRRO}</code> <code>${CIDADE}</code> <code>${ESTADO}</code> <code>${CEP}</code>
                <code>${TELEFONE}</code> <code>${EMAIL}</code> <code>${INSTITUICAO}</code> <code>${DATA_LOCAL}</code>
            </small>
        </td></tr>

</table>
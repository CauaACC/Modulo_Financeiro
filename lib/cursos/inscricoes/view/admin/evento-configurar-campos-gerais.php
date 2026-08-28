<table border='0' width='570'  cellspacing="10" cellpadding="10" class="dataTable">
    <tr><td colspan="2"><b>Configurações Gerais</b></td></tr>
   <tr>
    <td colspan="2">
        <b>Período de Realização</b><br>
        Data Inicio <input type='datetime-local' name='realizacao_ini' value='<?php echo $objEvConf->getRealizacaoIni() ?>' onBlur="trim(this);" >
        Data Fim <input type='datetime-local' name='realizacao_fim' value='<?php echo $objEvConf->getRealizacaoFim() ?>'  onBlur="trim(this);" >
    </td>
    </tr>
    <tr>
        <td colspan="2">
            <b>Período de Inscrição</b><br>
            Inicio <input type='datetime-local' name='data_inscricao_ini' value='<?php echo $objEvConf->getDataInscricaoIni() ?>' onBlur="trim(this);" >
            Fim    <input type='datetime-local' name='data_inscricao_fim' value='<?php echo $objEvConf->getDataInscricaoFim() ?>'  onBlur="trim(this);" >
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <b>Configurar Simulação</b><br>
            
            Ativar Simulação:
            &nbsp;&nbsp;
            <input type="radio" name="simular_data" value="1" <?= verifItemChecked($objEvConf->getSimularData(), '1', false) ?> /> Sim 
            &nbsp;&nbsp;
            <input type="radio" name="simular_data" value="0" <?= verifItemChecked($objEvConf->getSimularData(), array('', 0, false), false) ?> /> Não

            <br><br>
            Data de hoje:  <input type='datetime-local' name='data_simulacao' value='<?php echo $objEvConf->getDataSimulacao() ?>' onBlur="trim(this);" >
            <br>
            Ips liberado pra teste: <input type='text' name='ips_simulacao' value='<?php echo $objEvConf->getIpsSimulacao() ?>'  onBlur="trim(this);" >
            <br>Seu ip atual: <?php echo $_SERVER['REMOTE_ADDR'] ?>
        </td>
    </tr>
    <!--<tr><td>Permitir Login         </td>
        <td> 
            <input type="radio" name="insc_login" value="1" <?= verifItemChecked($objEvConf->getInscLogin(), '1', false) ?> /> Sim 
            &nbsp;&nbsp;
            <input type="radio" name="insc_login" value="0" <?= verifItemChecked($objEvConf->getInscLogin(), array('', 0, false), false) ?> /> Não
        </td></tr>-->
    <tr><td>Permitir Login         </td>
        <td> 
            <input type="radio" name="liberar_ja_estou_inscrito" value="1" <?= verifItemChecked($objEvConf->getLiberarJaEstouInscrito(), '1', false) ?> /> Sim 
            &nbsp;&nbsp;
            <input type="radio" name="liberar_ja_estou_inscrito" value="0" <?= verifItemChecked($objEvConf->getLiberarJaEstouInscrito(), array('', 0, false), false) ?> /> Não
        </td></tr>
    <!--<tr><td>Liberar Inscrição      </td><td>
            <input type="radio" name="insc_situacao" value="1" <?= verifItemChecked($objEvConf->getInscSituacao(), '1', false) ?> /> Sim 
            &nbsp;&nbsp;
            <input type="radio" name="insc_situacao" value="0" <?= verifItemChecked($objEvConf->getInscSituacao(), array('', 0, false), false) ?> /> Não
        </td></tr>-->
    <tr><td>Liberar Inscrição      </td><td>
            <input type="radio" name="processo_inscricao" value="1" <?= verifItemChecked($objEvConf->getProcessoInscricao(), '1', false) ?> /> Sim 
            &nbsp;&nbsp;
            <input type="radio" name="processo_inscricao" value="0" <?= verifItemChecked($objEvConf->getProcessoInscricao(), array('', 0, false), false) ?> /> Não
        </td></tr>
    <tr><td>Processo de Inscrição Suspenso      </td><td>
            <input type="radio" name="processo_inscricao_suspenso" value="1" <?= verifItemChecked($objEvConf->getProcessoInscricaoSuspenso(), '1', false) ?> /> Sim
            &nbsp;&nbsp;
            <input type="radio" name="processo_inscricao_suspenso" value="0" <?= verifItemChecked($objEvConf->getProcessoInscricaoSuspenso(), array('', 0, false), false) ?> /> Não
        </td></tr>
    <?php // Fluxo de Inscricao MOVIDO para a aba "Projeto" (define a natureza
          // do evento). Nao recolocar aqui: as abas dividem um <form> so e o
          // name repetido faria o ultimo input vencer no POST. ?>
    <tr><td>Fluxo de Inscrição</td>
        <td><?php espelhoSelect('tipo_fluxo_inscricao',
                                $objEvConf->getTipoFluxoInscricao() != '' ? $objEvConf->getTipoFluxoInscricao() : 'PADRAO',
                                array('PADRAO'  => 'Padrão (CPF → Perfil → Dados)',
                                      'TICKETS' => 'Ingressos (Selecionar → Dados+CPF → Confirmação)',
                                      'AGENDA'  => 'Agenda (Atividade/Dia → Perfil/Quantidade → Dados Pessoais)')) ?>
        </td></tr>
    <tr><td>Permitir Inscrição Duplicada: </td><td>
            <input type="radio" name="insc_duplicada" value="1" <?= verifItemChecked($objEvConf->getInscDuplicada(), '1', false) ?> /> Sim 
            &nbsp;&nbsp;
            <input type="radio" name="insc_duplicada" value="0" <?= verifItemChecked($objEvConf->getInscDuplicada(), array('', 0, false), false) ?> /> Não
        </td></tr>
    <tr><td>Possui controle de inscritos aptos por perfil:   </td><td>
            <input type="radio" name="verificar_apto_por_perfil" value="1" <?= verifItemChecked($objEvConf->getVerificarAptoPorPerfil(), '1', false) ?> /> Sim 
            &nbsp;&nbsp;
            <input type="radio" name="verificar_apto_por_perfil" value="0" <?= verifItemChecked($objEvConf->getVerificarAptoPorPerfil(), array('', 0, false), false) ?> /> Não
        </td></tr>        
    <?php // Recebe inscricoes pagas: MOVIDO para a aba "Projeto". ?>
    <tr><td>Recebe inscrições pagas:   </td><td>
            <?php espelhoSimNao('recebe_pagamento', $objEvConf->getRecebePagamento()) ?>
        </td></tr>
    <tr><td>Data limite para pagamento:</td><td>
            <input type="date" name="data_limite_pagamento" value="<?= $objEvConf->getDataLimitePagamento() ?>" style="width:200px;">
            <br><small style="color:#888;">Opcional. Preencha apenas se o prazo para pagamento for diferente do per&iacute;odo de inscri&ccedil;&atilde;o. Se vazio, o bot&atilde;o de pagamento ser&aacute; bloqueado ap&oacute;s o fim do per&iacute;odo de inscri&ccedil;&atilde;o.</small>
        </td></tr>
    <tr><td>Emissão de vouchers:      </td><td> 
            <input type="radio" name="emitir_voucher" value="1" <?= verifItemChecked($objEvConf->getEmitirVoucher(), '1', false) ?> /> Sim 
            &nbsp;&nbsp;
            <input type="radio" name="emitir_voucher" value="0" <?= verifItemChecked($objEvConf->getEmitirVoucher(), array('', 0, false), false) ?> /> Não
        </td></tr>
    <tr><td colspan="2"> <span style="font-size:15px;font-weight: bold;">Mensalidade</span> </td></tr>        
    <tr><td>Possui mensalidade:   </td><td>
            <input type="radio" name="recebe_pagamento_mensalidade" value="1" <?= verifItemChecked($objEvConf->getRecebePagamentoMensalidade(), '1', false) ?> /> Sim 
            &nbsp;&nbsp;
            <input type="radio" name="recebe_pagamento_mensalidade" value="0" <?= verifItemChecked($objEvConf->getRecebePagamentoMensalidade(), array('', 0, false), false) ?> /> Não
        </td></tr>
    <tr>
    <?php // Mensalidade Ativa: MOVIDO para a aba "Projeto". ?>
    <tr><td>Mensalidade Ativa: (cobrada por esse sistema)   </td><td>
            <?php espelhoSimNao('mens_ativa', $objEvConf->getMensAtiva()) ?>
        </td></tr>
    <tr>
        <td>Valor da mensalidade:</td>
        <td>
            <input type="text" name="mens_valor" value="<?= $objEvConf->getMensValor() ?>" size="10" maxlength="10" onblur="trim(this);" />
        </td>
    </tr>
    <tr>
        <td>Quantidade de mensalidades:</td>
        <td>
            <input type="number" name="mens_qtd" value="<?= $objEvConf->getMensQtd() ?>" min="1" max="60" size="5" onblur="trim(this);" />
        </td>
    </tr>
    <tr>
        <td>Data da 1ª mensalidade:</td>
        <td>
            <input type="date" name="mens_data_primeira" value="<?= $objEvConf->getMensDataPrimeira() ?>" onblur="trim(this);" />
        </td>
    </tr>
    <tr>
        <td>Dia do vencimento:</td>
        <td>
            <input type="number" name="mens_dia_venc" value="<?= $objEvConf->getMensDiaVenc() ?>" min="1" max="28" size="3" onblur="trim(this);" />
            <small>(1 a 28)</small>
        </td>
    </tr>
    <tr>
        <td>Observação (mensalidade):</td>
        <td>
            <input type="text" name="mens_obs" value="<?= $objEvConf->getMensObs() ?>" maxlength="255" size="50" onblur="trim(this);" />
        </td>
    </tr>
    <tr><td>Gerar mensalidades automaticamente na inscrição:</td><td>
            <input type="radio" name="mens_gerar_auto" value="1" <?= verifItemChecked($objEvConf->getMensGerarAuto(), '1', false) ?> /> Sim
            &nbsp;&nbsp;
            <input type="radio" name="mens_gerar_auto" value="0" <?= verifItemChecked($objEvConf->getMensGerarAuto(), array('', 0, false), false) ?> /> Não
            <br><small>Requer: Possui mensalidade = Sim, Mensalidade Ativa = Sim, quantidade e valor (da config ou da taxa "Mensalidade" do perfil).</small>
        </td></tr>
    <tr><td>Cronograma da mensalidade automática:</td><td>
            <input type="radio" name="mens_deslocar_cronograma" value="1" <?= verifItemChecked($objEvConf->getMensDeslocarCronograma(), array('', 1, false), false) ?> /> Deslocado: 1ª parcela no mês seguinte à inscrição (padrão)
            <br>
            <input type="radio" name="mens_deslocar_cronograma" value="0" <?= verifItemChecked($objEvConf->getMensDeslocarCronograma(), '0', false) ?> /> Fixo do evento: todas as parcelas desde a Data da 1ª mensalidade, mesmo vencidas
        </td></tr>

    <tr><td colspan="2"> <span style="font-size:15px;font-weight: bold;">Recepção e Acesso dos Participantes</span> </td></tr>
    <tr><td colspan="2">
            <b>Período de Recepção (entraga de material)</b><br>
            Inicio <input type='datetime-local' name='recepcao_ini' value='<?php echo $objEvConf->getRecepcaoIni() ?>' onBlur="trim(this);" >
            Fim    <input type='datetime-local' name='recepcao_fim' value='<?php echo $objEvConf->getRecepcaoFim() ?>'  onBlur="trim(this);" >
        </td>
    </tr>
    <tr><td>Evento online          </td><td>
            <input type="radio" name="evento_online" value="1" <?= verifItemChecked($objEvConf->getEventoOnline(), '1', false) ?> /> Sim 
            &nbsp;&nbsp;
            <input type="radio" name="evento_online" value="0" <?= verifItemChecked($objEvConf->getEventoOnline(), array('', 0, false), false) ?> /> Não
        </td></tr>
    <?php // Hall Virtual Liberar: MOVIDO para a aba "Projeto". ?>
    <tr><td>Hall Virtual Liberar   </td><td>
            <?php espelhoSimNao('hall_virtual_ativar', $objEvConf->getHallVirtualAtivar()) ?>
        </td></tr>
    <tr><td>Possui controle de participação:   </td><td>
            <input type="radio" name="controle_participacao" value="1" <?= verifItemChecked($objEvConf->getControleParticipacao(), '1', false) ?> /> Sim 
            &nbsp;&nbsp;
            <input type="radio" name="controle_participacao" value="0" <?= verifItemChecked($objEvConf->getControleParticipacao(), array('', 0, false), false) ?> /> Não
        </td></tr>
    <tr>
        <td colspan="2">
            <b>Período de Evento Online</b><br>
            Inicio <input type='datetime-local' name='online_dat_ini' value='<?php echo $objEvConf->getOnlineDatIni() ?>' onBlur="trim(this);" >
            Fim    <input type='datetime-local' name='online_dat_fim' value='<?php echo $objEvConf->getOnlineDatFim() ?>'  onBlur="trim(this);" >
        </td>
    </tr>
    <tr><td>URL Hall Virtual Externo</td><td> <input type='text'           name='hall_virtual_url'                     value='<?php echo $objEvConf->getHallVirtualUrl() ?>'                   maxlength='255'   size='60'  onBlur="trim(this);" ></td></tr>
    
    <tr><td colspan="2"> <span style="font-size:15px;font-weight: bold;">Serviço de Mensagem</span> </td></tr>
    <tr><td>Ativar Botões para Enviar Aviso Mensalidade:         </td><td> 
            <input type="radio" name="aviso_mensalidade" value="1" <?= verifItemChecked($objEvConf->getAvisoMensalidade(), '1', false) ?> /> Sim 
            &nbsp;&nbsp;
            <input type="radio" name="aviso_mensalidade" value="0" <?= verifItemChecked($objEvConf->getAvisoMensalidade(), array('', 0, false), false) ?> /> Não
        </td></tr>
    <tr><td>Ativar Botões para Enviar Aviso Pagamento:         </td><td> 
            <input type="radio" name="aviso_pagamento" value="1" <?= verifItemChecked($objEvConf->getAvisoPagamento(), '1', false) ?> /> Sim 
            &nbsp;&nbsp;
            <input type="radio" name="aviso_pagamento" value="0" <?= verifItemChecked($objEvConf->getAvisoPagamento(), array('', 0, false), false) ?> /> Não
        </td></tr>
    <tr><td>Ativar Botões para Enviar Aviso Sala Online:         </td><td>
            <input type="radio" name="aviso_sala_online" value="1" <?= verifItemChecked($objEvConf->getAvisoSalaOnline(), '1', false) ?> /> Sim
            &nbsp;&nbsp;
            <input type="radio" name="aviso_sala_online" value="0" <?= verifItemChecked($objEvConf->getAvisoSalaOnline(), array('', 0, false), false) ?> /> Não
        </td></tr>

    <tr><td colspan="2"> <span style="font-size:15px;font-weight: bold;">Aplicativo Mobile (PWA)</span> </td></tr>
    <?php // Habilitar PWA: MOVIDO para a aba "Projeto". ?>
    <tr><td>Habilitar PWA (instalar como app no celular):   </td><td>
            <?php espelhoSimNao('pwa_habilitado', $objEvConf->getPwaHabilitado()) ?>
            <br><small>Requer ícones em resources/public/{cod_evento}/pwa-icon-192.png e pwa-icon-512.png</small>
        </td></tr>
</table>

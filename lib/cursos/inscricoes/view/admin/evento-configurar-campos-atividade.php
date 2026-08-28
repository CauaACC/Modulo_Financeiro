<table border='0' width='570'  cellspacing="10" cellpadding="10" class="dataTable">
    <tr><td colspan="2"><span style="font-size:15px;font-weight: bold;">Configuração de Registros de Atividades</span></td></tr>
       <tr><td>Possui Registro Diário de Atividades</td><td>
        <?php espelhoSimNao('registro_diario', $objEvConf->getRegistroDiario()) ?>
    </td></tr>
    <tr><td colspan="2"><span style="font-size:15px;font-weight: bold;">Configuração de Cursos EAD (Hall Digital)</span></td></tr>
       <tr><td>Habilitar módulo de Cursos EAD</td><td>
        <?php espelhoSimNao('curso_habilitado', $objEvConf->getCursoHabilitado()) ?>
    </td></tr>
    <tr><td colspan="2"><span style="font-size:15px;font-weight: bold;">Configuração de Opções/Atividades</span></td></tr>
       <tr><td>Possui Atividades</td><td>
        <input type="radio" name="atividade" value="1" <?= verifItemChecked($objEvConf->getAtividade(), '1', false) ?> /> Sim 
        &nbsp;&nbsp;
        <input type="radio" name="atividade" value="0" <?= verifItemChecked($objEvConf->getAtividade(), array('', 0, false), false) ?> /> Não
    </td></tr>
    <tr><td colspan="2">Data fim da oferta: <input type='datetime-local'           name='atividade_dat_fim'                value='<?php echo $objEvConf->getAtividadeDatFim() ?>'               maxlength='255'   size='60'  onBlur="trim(this);" ></td></tr>
    <tr><td colspan="2">Tipo de Opção/Atividades Predominante a ser Ofertada:
            <select id="atividade_tipo" name="atividade_tipo">
                <option></option>
                <option <?= verifItemSelected($objEvConf->getAtividadeTipo(), 'Agenda', false) ?>>Agenda</option>
                <option <?= verifItemSelected($objEvConf->getAtividadeTipo(), 'Área', false) ?>>Área</option>
                <option <?= verifItemSelected($objEvConf->getAtividadeTipo(), 'Atividade', false) ?>>Atividade</option>
                <option <?= verifItemSelected($objEvConf->getAtividadeTipo(), 'Aula', false) ?>>Aula</option>
                <option <?= verifItemSelected($objEvConf->getAtividadeTipo(), 'Clinica', false) ?>>Clinica</option>
                <option <?= verifItemSelected($objEvConf->getAtividadeTipo(), 'Curso', false) ?>>Curso</option>
                <option <?= verifItemSelected($objEvConf->getAtividadeTipo(), 'Grupo de trabalho', false) ?>>Grupo de trabalho</option>
                <option <?= verifItemSelected($objEvConf->getAtividadeTipo(), 'Horário', false) ?>>Horário</option>
                <option <?= verifItemSelected($objEvConf->getAtividadeTipo(), 'Mesa Redonda', false) ?>>Mesa Redonda</option>
                <option <?= verifItemSelected($objEvConf->getAtividadeTipo(), 'Mini-curso', false) ?>>Mini-curso</option>
                <option <?= verifItemSelected($objEvConf->getAtividadeTipo(), 'Oficina', false) ?>>Oficina</option>
                <option <?= verifItemSelected($objEvConf->getAtividadeTipo(), 'Palestra', false) ?>>Palestra</option>
                <option <?= verifItemSelected($objEvConf->getAtividadeTipo(), 'Produto', false) ?>>Produto</option>
                <option <?= verifItemSelected($objEvConf->getAtividadeTipo(), 'Programa', false) ?>>Programa</option>
                <option <?= verifItemSelected($objEvConf->getAtividadeTipo(), 'Regiões', false) ?>>Regiões</option>
                <option <?= verifItemSelected($objEvConf->getAtividadeTipo(), 'Simpósio', false) ?>>Simpósio</option>
                <option <?= verifItemSelected($objEvConf->getAtividadeTipo(), 'Turma', false) ?>>Turma</option>
                <option <?= verifItemSelected($objEvConf->getAtividadeTipo(), 'Workshop', false) ?>>Workshop</option>
            </select>
        </td></tr>
    <tr><td>Permitir troca         </td><td> 
            <input type="radio" name="atividade_alterar" value="1" <?= verifItemChecked($objEvConf->getAtividadeAlterar(), '1', false) ?> /> Sim 
            &nbsp;&nbsp;
            <input type="radio" name="atividade_alterar" value="0" <?= verifItemChecked($objEvConf->getAtividadeAlterar(), array('', 0, false), false) ?> /> Não
        </td></tr>
<!--    <tr><td>Até qual data é permitir troca         </td><td> 
         <input type='date' name='atividade_permitir_alterar_data' value='<?php echo formatDateHoraTrans($objEvConf->getAtividadePermitirAlterarData(), 'YYYY-MM-DD HH:II:SS', 'YYYY-MM-DD') ?>' onBlur="trim(this);" >
        </td></tr>-->
    <tr>
        <td colspan="2">Até quantos dias após a inscrição é permitido a troca: 
            <input type="type" name="atividade_permitir_alterar_dias" value="<?php echo $objEvConf->getAtividadePermitirAlterarDias() ?>" maxlength="2" size="10"  onBlur="trim(this);">
        </td></tr>
    <tr><td colspan="2">Possuem valores variáveis para cada atividade
            <input type="radio" name="atividade_valor_variavel" value="1" <?= verifItemChecked($objEvConf->getAtividadeValorVariavel(), '1', false) ?> /> Sim 
            &nbsp;&nbsp;
            <input type="radio" name="atividade_valor_variavel" value="0" <?= verifItemChecked($objEvConf->getAtividadeValorVariavel(), array('', 0, false), false) ?> /> Não
        </td></tr>
    <tr><td colspan="2">Liberar Link de Videos Atividades:
            <input type="radio" name="atividade_link_online_liberar" value="1" <?= verifItemChecked($objEvConf->getAtividadeLinkOnlineLiberar(), '1', false) ?> /> Sim 
            &nbsp;&nbsp;
            <input type="radio" name="atividade_link_online_liberar" value="0" <?= verifItemChecked($objEvConf->getAtividadeLinkOnlineLiberar(), array('', 0, false), false) ?> /> Não
        </td></tr>
    <tr><td colspan="2">Liberar Conteúdo em Arquivos: 
            <input type="radio" name="atividade_conteudo_liberar" value="1" <?= verifItemChecked($objEvConf->getAtividadeConteudoLiberar(), '1', false) ?> /> Sim 
            &nbsp;&nbsp;
            <input type="radio" name="atividade_conteudo_liberar" value="0" <?= verifItemChecked($objEvConf->getAtividadeConteudoLiberar(), array('', 0, false), false) ?> /> Não
        </td></tr>
    <tr>
        <td colspan="2">Qtd de Pagamento para Liberar Conteúdo em Arquivos: 
            <input type="type" name="atividade_conteudo_qtd_pagamento" value="<?php echo $objEvConf->getAtividadeConteudoQtdPagamento() ?>" maxlength="2" size="10"  onBlur="trim(this);">
        </td></tr>
    <!--<tr><td>Valor                  </td><td> <input type='text'           name='atividade_valor'                  value='<?php echo $objEvConf->getAtividadeValor() ?>'                maxlength='255'   size='60'  onBlur="trim(this);" ></td></tr>-->
    <!--
    <tr><td colspan="2"> <span style="font-size:15px;font-weight: bold;">Cobrar Almoço</span></td></tr>
    <tr><td>Ativar                </td><td> 
        <input type="radio" name="almoco" value="1" <?= verifItemChecked($objEvConf->getAlmoco(), '1', false) ?> /> Sim 
        &nbsp;&nbsp;
        <input type="radio" name="almoco" value="0" <?= verifItemChecked($objEvConf->getAlmoco(), array('', 0, false), false) ?> /> Não
    </td></tr>
    <tr><td>Valor                </td><td> <input type='text'           name='almoco_valor'                     value='<?php echo $objEvConf->getAlmocoValor() ?>'                   maxlength='255'   size='60'  onBlur="trim(this);" ></td></tr>

    <tr><td colspan="2"> <span style="font-size:15px;font-weight: bold;">Cobrar Camiseta</span></td></tr>
    <tr><td>Ativar                </td><td> 
        <input type="radio" name="camiseta" value="1" <?= verifItemChecked($objEvConf->getCamiseta(), '1', false) ?> /> Sim 
        &nbsp;&nbsp;
        <input type="radio" name="camiseta" value="0" <?= verifItemChecked($objEvConf->getCamiseta(), array('', 0, false), false) ?> /> Não
    </td></tr>
    <tr><td>Valor                  </td><td> <input type='text'           name='camiseta_valor'                     value='<?php echo $objEvConf->getCamisetaValor() ?>'                   maxlength='255'   size='60'  onBlur="trim(this);" ></td></tr>

    <tr><td colspan="2"> <span style="font-size:15px;font-weight: bold;">Cobrar Confraternição</span></td></tr>
    <tr><td>Ativar                </td><td> 
            <input type="radio" name="confraternizacao" value="1" <?= verifItemChecked($objEvConf->getConfraternizacao(), '1', false) ?> /> Sim 
            &nbsp;&nbsp;
            <input type="radio" name="confraternizacao" value="0" <?= verifItemChecked($objEvConf->getConfraternizacao(), array('', 0, false), false) ?> /> Não
    </td></tr>
    <tr><td>Valor                 </td><td> <input type='text'           name='confraternizacao_valor'                     value='<?php echo $objEvConf->getConfraternizacaoValor() ?>'                   maxlength='255'   size='60'  onBlur="trim(this);" ></td></tr>
    -->
    <tr><td colspan="2"><span style="font-size:15px;font-weight: bold;">Configuração de Evento Paralelo</span></td></tr>
    <tr><td>Ativar Inscrição em Evento Paralelo:         </td><td> 
            <input type="radio" name="evento_paralelo" value="1" <?= verifItemChecked($objEvConf->getEventoParalelo(), '1', false) ?> /> Sim 
            &nbsp;&nbsp;
            <input type="radio" name="evento_paralelo" value="0" <?= verifItemChecked($objEvConf->getEventoParalelo(), array('', 0, false), false) ?> /> Não
        </td></tr>
    <tr><td>Quantidade  </td><td> <input type='text'  name='evento_paralelo_qtd' value='<?php echo $objEvConf->getEventoParaleloQtd() ?>' maxlength='255'   size='15'  onBlur="trim(this);" ></td></tr>
    <tr><td>Descrição  </td><td> <input type='text'  name='evento_paralelo_descricao' value='<?php echo $objEvConf->getEventoParaleloDescricao() ?>' maxlength='255'   size='50'  onBlur="trim(this);" ></td></tr>
    <tr><td>Tipos Permitidos ou Código (separados por virgula)  </td><td> <input type='text'  name='evento_paralelo_tipo' value='<?php echo $objEvConf->getEventoParaleloTipo() ?>' maxlength='255'   size='50'  onBlur="trim(this);" ></td></tr>
    <tr><td>Ativar Botão Adicionar:         </td><td> 
            <input type="radio" name="evento_paralelo_adicionar" value="1" <?= verifItemChecked($objEvConf->getEventoParaleloAdicionar(), '1', false) ?> /> Sim 
            &nbsp;&nbsp;
            <input type="radio" name="evento_paralelo_adicionar" value="0" <?= verifItemChecked($objEvConf->getEventoParaleloAdicionar(), array('', 0, false), false) ?> /> Não
        </td></tr>
    <tr><td>Ativar Botão Alterar:         </td><td> 
            <input type="radio" name="evento_paralelo_alterar" value="1" <?= verifItemChecked($objEvConf->getEventoParaleloAlterar(), '1', false) ?> /> Sim 
            &nbsp;&nbsp;
            <input type="radio" name="evento_paralelo_alterar" value="0" <?= verifItemChecked($objEvConf->getEventoParaleloAlterar(), array('', 0, false), false) ?> /> Não
        </td></tr>
    <!-- <tr><td>Ativar Botão Excluir:         </td><td> 
            <input type="radio" name="evento_paralelo_excluir" value="1" <?= verifItemChecked($objEvConf->getEventoParaleloExcluir(), '1', false) ?> /> Sim 
            &nbsp;&nbsp;
            <input type="radio" name="evento_paralelo_excluir" value="0" <?= verifItemChecked($objEvConf->getEventoParaleloExcluir(), array('', 0, false), false) ?> /> Não
        </td></tr> -->
    <!-- <tr><td>Ativar Botão Personalizado:         </td><td> 
            <input type="radio" name="evento_paralelo_btn_personalizado" value="1" <?= verifItemChecked($objEvConf->getEventoParaleloBtnPersonalizado(), '1', false) ?> /> Sim 
            &nbsp;&nbsp;
            <input type="radio" name="evento_paralelo_btn_personalizado" value="0" <?= verifItemChecked($objEvConf->getEventoParaleloBtnPersonalizado(), array('', 0, false), false) ?> /> Não
        </td></tr> -->
    <tr><td colspan="2"></td></tr>        
    <tr><td>Ativar Mensagem em Selecionar Opções/Atividades:         </td><td> 
            <input type="radio" name="msg_oficina_ativar" value="1" <?= verifItemChecked($objEvConf->getMsgOficinaAtivar(), '1', false) ?> /> Sim 
            &nbsp;&nbsp;
            <input type="radio" name="msg_oficina_ativar" value="0" <?= verifItemChecked($objEvConf->getMsgOficinaAtivar(), array('', 0, false), false) ?> /> Não
        </td></tr>
    <tr><td colspan="2">
        <div class="form-group col-sm-12" style="padding-left: 0px;">
            <div class="form-group col-sm-12" style="padding-left: 0px;">
                <label for="descricao">Mensagem em Selecionar Opções/Atividades:</label>
                <textarea class=editor_padrao rows=5 cols=70 name='msg_oficina_texto'       
                onBlur='trim(this);' class='textob form_control'><?php echo $objEvConf->getMsgOficinaTexto()?></textarea>
            </div>
        </div>
        </td></tr>
</table>
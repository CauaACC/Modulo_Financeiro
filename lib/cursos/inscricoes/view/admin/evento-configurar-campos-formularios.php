<table border='0' width='570' cellspacing="10" cellpadding="10" class="dataTable">
    <tr>
        <td colspan="2"><span style="font-size:15px;font-weight: bold;">Configuração de Formulários</span></td>
    </tr>
    <tr>
        <td colspan="2">Permitir editar dados pessoais até <input type='date' name='ed_dados_dat_fim' value='<?php echo formatDateHoraTrans($objEvConf->getEdDadosDatFim(), 'YYYY-MM-DD HH:II:SS', 'YYYY-MM-DD') ?>' maxlength='255' size='60' onBlur="trim(this);"></td>
    </tr>
    <tr>
        <td colspan="2">Label do campo nome do inscrito (tratar cadastrado como?):<br>
            <select id="dados_pessoais_nome" name="dados_pessoais_nome">
                <option value="CANDIDATO" <?= verifItemSelected($objEvConf->getDadosPessoaisNome(), 'CANDIDATO', false) ?>>CANDIDATO</option>
                <option value="CLIENTE" <?= verifItemSelected($objEvConf->getDadosPessoaisNome(), 'CLIENTE', false) ?>>CLIENTE</option>
                <option value="INSCRITO" <?= verifItemSelected($objEvConf->getDadosPessoaisNome(), 'INSCRITO', false) ?>>INSCRITO</option>
                <option value="PARTICIPANTE" <?= verifItemSelected($objEvConf->getDadosPessoaisNome(), 'PARTICIPANTE', false) ?>>PARTICIPANTE</option>
                <option value="PAGADOR" <?= verifItemSelected($objEvConf->getDadosPessoaisNome(), 'PAGADOR', false) ?>>PAGADOR</option>
                <option value="REQUERENTE" <?= verifItemSelected($objEvConf->getDadosPessoaisNome(), 'REQUERENTE', false) ?>>REQUERENTE</option>
        </td>
    </tr>

    <tr>
        <td colspan="2"><span style="font-size:15px;font-weight: bold;">Campos para o Formulário</span>
            <br>Escolha abaixo os campos para informações a serem solicitadas no formulário de inscrição
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <input type="radio" name="dados_pessoais_profissao" value="1" <?= verifItemChecked($objEvConf->getDadosPessoaisProfissao(), '1', false) ?> /> Sim
            &nbsp;&nbsp;
            <input type="radio" name="dados_pessoais_profissao" value="0" <?= verifItemChecked($objEvConf->getDadosPessoaisProfissao(), array('', 0, false), false) ?> /> Não
            <label>Profissão</label>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <input type="radio" name="form_tipo_inscricao" value="1" <?= verifItemChecked($objEvConf->getFormTipoInscricao(), '1', false) ?> /> Sim
            &nbsp;&nbsp;
            <input type="radio" name="form_tipo_inscricao" value="0" <?= verifItemChecked($objEvConf->getFormTipoInscricao(), array('', 0, false), false) ?> /> Não
            <label>Tipo Inscrição/Categoria:</label>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <input type="radio" name="estrangeiro_sem_cpf" value="1" <?= verifItemChecked($objEvConf->getEstrangeiroSemCpf(), '1', false) ?> /> Sim
            &nbsp;&nbsp;
            <input type="radio" name="estrangeiro_sem_cpf" value="0" <?= verifItemChecked($objEvConf->getEstrangeiroSemCpf(), array('', 0, false), false) ?> /> Não
            <label>Aceitar estrangeiros sem CPF</label>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <input type="radio" name="form_nome_cracha" value="1" <?= verifItemChecked($objEvConf->getFormNomeCracha(), '1', false) ?> /> Sim
            &nbsp;&nbsp;
            <input type="radio" name="form_nome_cracha" value="0" <?= verifItemChecked($objEvConf->getFormNomeCracha(), array('', 0, false), false) ?> /> Não
            <label>Nome Cracha</label>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <input type="radio" name="form_melhor_email" value="1" <?= verifItemChecked($objEvConf->getFormMelhorEmail(), '1', false) ?> /> Sim
            &nbsp;&nbsp;
            <input type="radio" name="form_melhor_email" value="0" <?= verifItemChecked($objEvConf->getFormMelhorEmail(), array('', 0, false), false) ?> /> Não
            <label>Melhor E-mail</label>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <input type="radio" name="dados_pessoais_tipo_instituicao" value="1" <?= verifItemChecked($objEvConf->getDadosPessoaisTipoInstituicao(), '1', false) ?> /> Sim
            &nbsp;&nbsp;
            <input type="radio" name="dados_pessoais_tipo_instituicao" value="0" <?= verifItemChecked($objEvConf->getDadosPessoaisTipoInstituicao(), array('', 0, false), false) ?> /> Não
            <label>Tipo instituição</label>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <input type="radio" name="dados_pessoais_instituicao" value="1" <?= verifItemChecked($objEvConf->getDadosPessoaisInstituicao(), '1', false) ?> /> Sim
            &nbsp;&nbsp;
            <input type="radio" name="dados_pessoais_instituicao" value="0" <?= verifItemChecked($objEvConf->getDadosPessoaisInstituicao(), array('', 0, false), false) ?> /> Não
            <label>Nome instituição</label>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <input type="radio" name="form_data_nascimento" value="1" <?= verifItemChecked($objEvConf->getFormDataNascimento(), '1', false) ?> /> Sim
            &nbsp;&nbsp;
            <input type="radio" name="form_data_nascimento" value="0" <?= verifItemChecked($objEvConf->getFormDataNascimento(), array('', 0, false), false) ?> /> Não
            <label>Data de nascimento</label>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <input type="radio" name="form_cidade_nascimento" value="1" <?= verifItemChecked($objEvConf->getFormCidadeNascimento(), '1', false) ?> /> Sim
            &nbsp;&nbsp;
            <input type="radio" name="form_cidade_nascimento" value="0" <?= verifItemChecked($objEvConf->getFormCidadeNascimento(), array('', 0, false), false) ?> /> Não
            <label>Cidade Nascimento</label>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <input type="radio" name="form_estado_nascimento" value="1" <?= verifItemChecked($objEvConf->getFormEstadoNascimento(), '1', false) ?> /> Sim
            &nbsp;&nbsp;
            <input type="radio" name="form_estado_nascimento" value="0" <?= verifItemChecked($objEvConf->getFormEstadoNascimento(), array('', 0, false), false) ?> /> Não
            <label>Estado Nascimento</label>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <input type="radio" name="form_sexo" value="1" <?= verifItemChecked($objEvConf->getFormSexo(), '1', false) ?> /> Sim
            &nbsp;&nbsp;
            <input type="radio" name="form_sexo" value="0" <?= verifItemChecked($objEvConf->getFormSexo(), array('', 0, false), false) ?> /> Não
            <label>Sexo</label>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <input type="radio" name="form_raca" value="1" <?= verifItemChecked($objEvConf->getFormRaca(), '1', false) ?> /> Sim
            &nbsp;&nbsp;
            <input type="radio" name="form_raca" value="0" <?= verifItemChecked($objEvConf->getFormRaca(), array('', 0, false), false) ?> /> Não
            <label>Raça</label>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <input type="radio" name="form_estado_civil" value="1" <?= verifItemChecked($objEvConf->getFormEstadoCivil(), '1', false) ?> /> Sim
            &nbsp;&nbsp;
            <input type="radio" name="form_estado_civil" value="0" <?= verifItemChecked($objEvConf->getFormEstadoCivil(), array('', 0, false), false) ?> /> Não
            <label>Estado Civíl</label>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <input type="radio" name="form_rg" value="1" <?= verifItemChecked($objEvConf->getFormRg(), '1', false) ?> /> Sim
            &nbsp;&nbsp;
            <input type="radio" name="form_rg" value="0" <?= verifItemChecked($objEvConf->getFormRg(), array('', 0, false), false) ?> /> Não
            <label>RG</label>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <input type="radio" name="form_rg_data_emissao" value="1" <?= verifItemChecked($objEvConf->getFormRgDataEmissao(), '1', false) ?> /> Sim
            &nbsp;&nbsp;
            <input type="radio" name="form_rg_data_emissao" value="0" <?= verifItemChecked($objEvConf->getFormRgDataEmissao(), array('', 0, false), false) ?> /> Não
            <label>RG Data Emissão</label>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <input type="radio" name="form_rg_cidade_emissao" value="1" <?= verifItemChecked($objEvConf->getFormRgCidadeEmissao(), '1', false) ?> /> Sim
            &nbsp;&nbsp;
            <input type="radio" name="form_rg_cidade_emissao" value="0" <?= verifItemChecked($objEvConf->getFormRgCidadeEmissao(), array('', 0, false), false) ?> /> Não
            <label>RG Cidade Emissão</label>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <input type="radio" name="form_rg_estado_emissao" value="1" <?= verifItemChecked($objEvConf->getFormRgEstadoEmissao(), '1', false) ?> /> Sim
            &nbsp;&nbsp;
            <input type="radio" name="form_rg_estado_emissao" value="0" <?= verifItemChecked($objEvConf->getFormRgEstadoEmissao(), array('', 0, false), false) ?> /> Não
            <label>RG Estado Emissão</label>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <input type="radio" name="form_rg_orgao_expedidor" value="1" <?= verifItemChecked($objEvConf->getFormRgOrgaoExpedidor(), '1', false) ?> /> Sim
            &nbsp;&nbsp;
            <input type="radio" name="form_rg_orgao_expedidor" value="0" <?= verifItemChecked($objEvConf->getFormRgOrgaoExpedidor(), array('', 0, false), false) ?> /> Não
            <label>RG Órgão expoedidor</label>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <input type="radio" name="form_dados_bancarios" value="1" <?= verifItemChecked($objEvConf->getFormDadosBancarios(), '1', false) ?> /> Sim
            &nbsp;&nbsp;
            <input type="radio" name="form_dados_bancarios" value="0" <?= verifItemChecked($objEvConf->getFormDadosBancarios(), array('', 0, false), false) ?> /> Não
            <label>Dados Bancários</label>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <input type="radio" name="form_dados_bancarios_somente_pix" value="1" <?= verifItemChecked($objEvConf->getFormDadosBancariosSomentePix(), '1', false) ?> /> Sim
            &nbsp;&nbsp;
            <input type="radio" name="form_dados_bancarios_somente_pix" value="0" <?= verifItemChecked($objEvConf->getFormDadosBancariosSomentePix(), array('', 0, false), false) ?> /> Não
            <label>Somente PIX</label>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <input type="radio" name="form_pis" value="1" <?= verifItemChecked($objEvConf->getFormPis(), '1', false) ?> /> Sim
            &nbsp;&nbsp;
            <input type="radio" name="form_pis" value="0" <?= verifItemChecked($objEvConf->getFormPis(), array('', 0, false), false) ?> /> Não
            <label>Número do PIS</label>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <input type="radio" name="form_nis" value="1" <?= verifItemChecked($objEvConf->getFormNis(), '1', false) ?> /> Sim
            &nbsp;&nbsp;
            <input type="radio" name="form_nis" value="0" <?= verifItemChecked($objEvConf->getFormNis(), array('', 0, false), false) ?> /> Não
            <label>Número do NIS</label>
        </td>
    <tr>
        <td colspan="2">
            <input type="radio" name="form_nome_mae" value="1" <?= verifItemChecked($objEvConf->getFormNomeMae(), '1', false) ?> /> Sim
            &nbsp;&nbsp;
            <input type="radio" name="form_nome_mae" value="0" <?= verifItemChecked($objEvConf->getFormNomeMae(), array('', 0, false), false) ?> /> Não
            <label>Nome da Mãe</label>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <input type="radio" name="form_nome_pai" value="1" <?= verifItemChecked($objEvConf->getFormNomePai(), '1', false) ?> /> Sim
            &nbsp;&nbsp;
            <input type="radio" name="form_nome_pai" value="0" <?= verifItemChecked($objEvConf->getFormNomePai(), array('', 0, false), false) ?> /> Não
            <label>Nome do Pai</label>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <input type="radio" name="form_dependentes" value="1" <?= verifItemChecked($objEvConf->getFormDependentes(), '1', false) ?> /> Sim
            &nbsp;&nbsp;
            <input type="radio" name="form_dependentes" value="0" <?= verifItemChecked($objEvConf->getFormDependentes(), array('', 0, false), false) ?> /> Não
            <label>Cadastro de Dependente</label>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <input type="radio" name="form_grau_escolaridade" value="1" <?= verifItemChecked($objEvConf->getFormGrauEscolaridade(), '1', false) ?> /> Sim
            &nbsp;&nbsp;
            <input type="radio" name="form_grau_escolaridade" value="0" <?= verifItemChecked($objEvConf->getFormGrauEscolaridade(), array('', 0, false), false) ?> /> Não
            <label>Grau de Escolaridade</label>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <input type="radio" name="form_graduacao" value="1" <?= verifItemChecked($objEvConf->getFormGraduacao(), '1', false) ?> /> Sim
            &nbsp;&nbsp;
            <input type="radio" name="form_graduacao" value="0" <?= verifItemChecked($objEvConf->getFormGraduacao(), array('', 0, false), false) ?> /> Não
            <label>Graduação</label>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <input type="radio" name="form_graduacao_ano" value="1" <?= verifItemChecked($objEvConf->getFormGraduacaoAno(), '1', false) ?> /> Sim
            &nbsp;&nbsp;
            <input type="radio" name="form_graduacao_ano" value="0" <?= verifItemChecked($objEvConf->getFormGraduacaoAno(), array('', 0, false), false) ?> /> Não
            <label>Ano de Conclusão da Graduação</label>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <input type="radio" name="form_graduacao_ies" value="1" <?= verifItemChecked($objEvConf->getFormGraduacaoIes(), '1', false) ?> /> Sim
            &nbsp;&nbsp;
            <input type="radio" name="form_graduacao_ies" value="0" <?= verifItemChecked($objEvConf->getFormGraduacaoIes(), array('', 0, false), false) ?> /> Não
            <label>Instituição de Ensino da Graduação</label>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <input type="radio" name="form_pos_graduacao" value="1" <?= verifItemChecked($objEvConf->getFormPosGraduacao(), '1', false) ?> /> Sim
            &nbsp;&nbsp;
            <input type="radio" name="form_pos_graduacao" value="0" <?= verifItemChecked($objEvConf->getFormPosGraduacao(), array('', 0, false), false) ?> /> Não
            <label>Pós-Graduação</label>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <input type="radio" name="form_pos_graduacao_ano" value="1" <?= verifItemChecked($objEvConf->getFormPosGraduacaoAno(), '1', false) ?> /> Sim
            &nbsp;&nbsp;
            <input type="radio" name="form_pos_graduacao_ano" value="0" <?= verifItemChecked($objEvConf->getFormPosGraduacaoAno(), array('', 0, false), false) ?> /> Não
            <label>Ano de Conclusão da Pós-Graduação</label>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <input type="radio" name="form_pos_graduacao_ies" value="1" <?= verifItemChecked($objEvConf->getFormPosGraduacaoIes(), '1', false) ?> /> Sim
            &nbsp;&nbsp;
            <input type="radio" name="form_pos_graduacao_ies" value="0" <?= verifItemChecked($objEvConf->getFormPosGraduacaoIes(), array('', 0, false), false) ?> /> Não
            <label>Instituição de Ensino da Pós-Graduação</label>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <input type="radio" name="form_empresa_atual" value="1" <?= verifItemChecked($objEvConf->getFormEmpresaAtual(), '1', false) ?> /> Sim
            &nbsp;&nbsp;
            <input type="radio" name="form_empresa_atual" value="0" <?= verifItemChecked($objEvConf->getFormEmpresaAtual(), array('', 0, false), false) ?> /> Não
            <label>Empresa Atual</label>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <input type="radio" name="form_cargo_atual" value="1" <?= verifItemChecked($objEvConf->getFormCargoAtual(), '1', false) ?> /> Sim
            &nbsp;&nbsp;
            <input type="radio" name="form_cargo_atual" value="0" <?= verifItemChecked($objEvConf->getFormCargoAtual(), array('', 0, false), false) ?> /> Não
            <label>Cargo Atual</label>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <input type="radio" name="form_pretensao_carreira" value="1" <?= verifItemChecked($objEvConf->getFormPretensaoCarreira(), '1', false) ?> /> Sim
            &nbsp;&nbsp;
            <input type="radio" name="form_pretensao_carreira" value="0" <?= verifItemChecked($objEvConf->getFormPretensaoCarreira(), array('', 0, false), false) ?> /> Não
            <label>Pretensão de Carreira</label>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <input type="radio" name="form_como_ficou_sabendo" value="1" <?= verifItemChecked($objEvConf->getFormComoFicouSabendo(), '1', false) ?> /> Sim
            &nbsp;&nbsp;
            <input type="radio" name="form_como_ficou_sabendo" value="0" <?= verifItemChecked($objEvConf->getFormComoFicouSabendo(), array('', 0, false), false) ?> /> Não
            <label>Como ficou sabendo do evento</label>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <input type="radio" name="form_qual_motivo_inscreveu" value="1" <?= verifItemChecked($objEvConf->getFormQualMotivoInscreveu(), '1', false) ?> /> Sim
            &nbsp;&nbsp;
            <input type="radio" name="form_qual_motivo_inscreveu" value="0" <?= verifItemChecked($objEvConf->getFormQualMotivoInscreveu(), array('', 0, false), false) ?> /> Não
            <label>Qual o motivo da inscrição</label>
        </td>
    </tr>

    <tr>
        <td colspan="2"><span style="font-size:15px;font-weight: bold;">Complemento no Formulário</span></td>
    </tr>
    <tr>
        <td colspan="2">Adicionar Complemento no Formulário de Inscrição:
            <input type="radio" name="dados_pessoais_complemento" value="1" <?= verifItemChecked($objEvConf->getDadosPessoaisComplemento(), '1', false) ?> /> Sim
            &nbsp;&nbsp;
            <input type="radio" name="dados_pessoais_complemento" value="0" <?= verifItemChecked($objEvConf->getDadosPessoaisComplemento(), array('', 0, false), false) ?> /> Não
        </td>
    </tr>
    <tr>
        <td colspan="2">Complemento para formulário de dados pessoais:
            <br>
            <textarea class=editor_padrao rows=5 cols=70 name='dados_pessoais_complemento_texto' onBlur='trim(this);' class='textob form_control'><?php echo $objEvConf->getDadosPessoaisComplementoTexto() ?></textarea>
            <!--<textarea rows=5 cols=70 name='dados_pessoais_complemento_texto' size='60' onBlur='trim(this);' onkeypress='javascript: return isAlfa(window.event.keyCode);' class='textob'><?php echo $objEvConf->getDadosPessoaisComplementoTexto() ?></textarea>-->
        </td>
    </tr>
    <tr>
        <td colspan="2"><span style="font-size:15px;font-weight: bold;">Termos</span></td>
    </tr>
    <tr>
        <td colspan="2">Exigir aceite de termo de compromisso:
            <input type="radio" name="dados_pessoais_termo_compromisso" value="1" <?= verifItemChecked($objEvConf->getDadosPessoaisTermoCompromisso(), '1', false) ?> /> Sim
            &nbsp;&nbsp;
            <input type="radio" name="dados_pessoais_termo_compromisso" value="0" <?= verifItemChecked($objEvConf->getDadosPessoaisTermoCompromisso(), array('', 0, false), false) ?> /> Não
        </td>
    </tr>
    <tr>
        <td colspan="2">Url do termo de compromisso:
            <br> <input type='text' name='dados_pessoais_termo_compromisso_url' value='<?php echo $objEvConf->getDadosPessoaisTermoCompromissoUrl() ?>' maxlength='255' size='60' onBlur="trim(this);">
        </td>
    </tr>
    <tr>
        <td colspan="2">Texto explicativo para termo de compromisso:
            <br>
            <textarea class=editor_padrao rows=5 cols=70 name='dados_pessoais_termo_compromisso_texto' onBlur='trim(this);' class='textob form_control'><?php echo $objEvConf->getDadosPessoaisTermoCompromissoTexto() ?></textarea>
            <!--<textarea rows=5 cols=70 name='dados_pessoais_termo_compromisso_texto' size='60' onBlur='trim(this);' onkeypress='javascript: return isAlfa(window.event.keyCode);' class='textob'><?php echo $objEvConf->getDadosPessoaisTermoCompromissoTexto() ?></textarea>-->
        </td>
    </tr>
    <tr>
        <td colspan="2">Label do bot&atilde;o de salvar inscri&ccedil;&atilde;o (deixe vazio para usar o padr&atilde;o "Salvar Inscri&ccedil;&atilde;o"):<br>
            <input type="text" name="form_btn_salvar_label" value="<?php echo htmlspecialchars($objEvConf->getFormBtnSalvarLabel()) ?>" class="form-control" placeholder="Salvar Inscri&ccedil;&atilde;o" maxlength="100" style="max-width:400px;">
        </td>
    </tr>
</table>
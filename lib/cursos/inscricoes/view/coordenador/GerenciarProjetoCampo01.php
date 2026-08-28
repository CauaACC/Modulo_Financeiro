<div class="wz-field">
    <label class="wz-field-label" for="titulo">
        T&iacute;tulo do Evento <span class="wz-required">*</span>
    </label>
    <input type="text" id="titulo" name="titulo" value="<?php echo $fws_['titulo'] ?>" maxlength="255" class="form-control" placeholder="Ex: III Congresso Nacional de..." onBlur="trim(this);">
</div>

<div class="wz-row">
    <div class="wz-field wz-col-8">
        <label class="wz-field-label" for="nomeOrganizador">
            Nome do Coordenador <span class="wz-required">*</span>
        </label>
        <input type="text" id="nomeOrganizador" name="nomeOrganizador" value="<?php echo $fws_['nomeOrganizador'] ?>" maxlength="255" class="form-control" placeholder="Nome completo" onBlur="trim(this);">
    </div>
    <div class="wz-field wz-col-4">
        <label class="wz-field-label" for="foneOrganizador">
            Telefone / WhatsApp <span class="wz-required">*</span>
        </label>
        <input type="text" id="foneOrganizador" name="foneOrganizador" value="<?php echo $fws_['foneOrganizador'] ?>" maxlength="255" class="form-control" placeholder="(00) 00000-0000" onBlur="trim(this);">
    </div>
</div>

<div class="wz-field">
    <label class="wz-field-label" for="emailContato">
        E-mail do Coordenador <span class="wz-required">*</span>
    </label>
    <input type="text" id="emailContato" name="emailContato" value="<?php echo $fws_['emailContato'] ?>" maxlength="255" class="form-control" placeholder="email@exemplo.com" onBlur="trim(this);">
</div>

<div class="wz-field">
    <label class="wz-field-label" for="descricaoProjeto">Resumo do Projeto</label>
    <span class="wz-field-hint">Descri&ccedil;&atilde;o geral do projeto, evento ou curso (opcional).</span>
    <textarea id="descricaoProjeto" name="descricaoProjeto" class="form-control" placeholder="Breve descri&ccedil;&atilde;o do projeto..." rows="5"></textarea>
</div>

<div class="wz-row">
    <div class="wz-field">
        <label class="wz-field-label" for="publicoAlvo">
            P&uacute;blico-Alvo <span class="wz-required">*</span>
        </label>
        <textarea id="publicoAlvo" name="publicoAlvo" class="form-control" placeholder="A quem se destina o evento" rows="4"></textarea>
    </div>
    <div class="wz-field">
        <label class="wz-field-label" for="objetivo">
            Objetivo <span class="wz-required">*</span>
        </label>
        <textarea id="objetivo" name="objetivo" class="form-control" placeholder="Objetivos gerais e espec&iacute;ficos" rows="4"></textarea>
    </div>
</div>

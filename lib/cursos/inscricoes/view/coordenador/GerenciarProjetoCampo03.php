<div class="wz-row">
    <div class="wz-field">
        <fieldset class="wz-fieldset">
            <legend>Formato <span class="wz-required">*</span></legend>
            <div class="wz-option">
                <input type="radio" name="tipoProjeto" id="eventoPresencial" value="Presencial">
                <label for="eventoPresencial">Evento Presencial</label>
            </div>
            <div class="wz-option">
                <input type="radio" name="tipoProjeto" id="eventoRemotoOnline" value="Remoto/Online">
                <label for="eventoRemotoOnline">Evento Remoto/Online</label>
            </div>
            <div class="wz-option">
                <input type="radio" name="tipoProjeto" id="EventoHibrido" value="H&iacute;brido">
                <label for="EventoHibrido">Evento H&iacute;brido</label>
            </div>
        </fieldset>
    </div>
    <div class="wz-field">
        <fieldset class="wz-fieldset">
            <legend>Servi&ccedil;os <span class="wz-required">*</span></legend>
            <div class="wz-option">
                <input type="checkbox" name="trabEnvioTrabalhos" id="trabEnvioTrabalhos" value="1">
                <label for="trabEnvioTrabalhos">Envio de Trabalhos</label>
            </div>
            <div class="wz-option">
                <input type="checkbox" name="trabEscolherAtividades" id="trabEscolherAtividades" value="1">
                <label for="trabEscolherAtividades">Escolher Atividade</label>
            </div>
            <div class="wz-option">
                <input type="checkbox" name="trabEmissaoCertificados" id="trabEmissaoCertificados" value="1">
                <label for="trabEmissaoCertificados">Emiss&atilde;o de Certificados</label>
            </div>
            <div class="wz-option">
                <input type="checkbox" name="trabReceberPagamentos" id="trabReceberPagamentos" value="1">
                <label for="trabReceberPagamentos">Receber Pagamentos</label>
            </div>
        </fieldset>
    </div>
</div>

<div class="wz-field">
    <label class="wz-field-label" for="descLocalRealizacao">Local de Realiza&ccedil;&atilde;o</label>
    <span class="wz-field-hint">Endere&ccedil;o completo onde o evento ser&aacute; realizado (opcional).</span>
    <textarea id="descLocalRealizacao" name="descLocalRealizacao" class="form-control" placeholder="Endere&ccedil;o do local..." rows="2"></textarea>
</div>

<div class="wz-field">
    <label class="wz-field-label" for="descricaoValoresInscricao">
        Valores de Inscri&ccedil;&atilde;o <span class="wz-required">*</span>
    </label>
    <span class="wz-field-hint">Perfis ou categorias de inscritos, valores e per&iacute;odos de lotes.</span>
    <textarea id="descricaoValoresInscricao" name="descricaoValoresInscricao" class="form-control" placeholder="Descreva categorias, valores e lotes de inscri&ccedil;&atilde;o..." rows="3"></textarea>
</div>

<div class="wz-field">
    <label class="wz-field-label" for="descricaoTrabalhosArtigos">
        Envio de Trabalhos / Artigos <span class="wz-required">*</span>
    </label>
    <span class="wz-field-hint">Caracter&iacute;sticas do envio de trabalhos ou URL com informa&ccedil;&otilde;es.</span>
    <textarea id="descricaoTrabalhosArtigos" name="descricaoTrabalhosArtigos" class="form-control" placeholder="Descreva as regras para envio de trabalhos..." rows="3"></textarea>
</div>

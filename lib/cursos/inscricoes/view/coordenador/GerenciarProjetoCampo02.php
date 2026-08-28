<div class="wz-section-title">Per&iacute;odo de Realiza&ccedil;&atilde;o do Evento</div>
<div class="wz-row">
    <div class="wz-field">
        <label class="wz-field-label" for="dataInicioEvento">Data de In&iacute;cio</label>
        <input class="form-control" type="text" id="dataInicioEvento" name="dataInicioEvento" value="<?php echo $fws_['dataInicioEvento'] ?>">
    </div>
    <div class="wz-field">
        <label class="wz-field-label" for="dataFimEvento">Data de Fim</label>
        <input class="form-control" type="text" id="dataFimEvento" name="dataFimEvento" value="<?php echo $fws_['dataFimEvento'] ?>">
    </div>
</div>

<div class="wz-row">
    <div class="wz-field">
        <label class="wz-field-label" for="Categoria">
            Categoria <span class="wz-required">*</span>
        </label>
        <select id="Categoria" name="categoria" class="form-control">
            <!-- Acad&ecirc;micos -->
            <option value="">Selecione uma op&ccedil;&atilde;o</option>
            <option>Assembl&eacute;ia</option>
            <option>Ato P&uacute;blico</option>
            <option>Col&oacute;quio</option>
            <option>Comunica&ccedil;&atilde;o Oral</option>
            <option>Congresso</option>
            <option>Confer&ecirc;ncia</option>
            <option>Curso</option>
            <option>Mesa-Redonda</option>
            <option>Minicurso</option>
            <option>M&oacute;dulo</option>
            <option>Painel</option>
            <option>Palestra</option>
            <option>Programa</option>
            <option>Retiro</option>
            <option>Semin&aacute;rio</option>
            <option>Webinar</option>
            <option>Workshop</option>
            <option>Hackathon</option>
            <option>Grupo de Estudo</option>
            <option>Sa&iacute;da de Campo</option>
            <option>Pitch</option>
            <!-- Empresariais -->
            <option>Reuni&atilde;o de Neg&oacute;cios</option>
            <option>Apresenta&ccedil;&atilde;o Corporativa</option>
            <option>Networking</option>
            <option>Feira de Neg&oacute;cios</option>
            <option>Reuni&otilde;es de Brainstorming</option>
            <!-- Sociais -->
            <option>Festa</option>
            <option>Churrasco</option>
            <option>Casamento</option>
            <option>Reuni&atilde;o de Fam&iacute;lia</option>
            <option>Picnic</option>
            <option>Passeio</option>
            <option>Acampamento</option>
            <option>Jantar Tem&aacute;tico</option>
            <option>Happy Hour</option>
            <!-- Entretenimento -->
            <option>Concerto</option>
            <option>Show</option>
            <option>Festival</option>
            <option>Feira/Exposi&ccedil;&atilde;o</option>
            <option>Desfile</option>
            <option>Festival Gastron&ocirc;mico</option>
            <option>Teatro</option>
            <option>Oficina de Artes</option>
            <option>Exposi&ccedil;&atilde;o de Arte</option>
            <option>Festival de Cinema</option>
            <option>Mostra de Cinema</option>
            <option>Agenda de Visita&ccedil;&atilde;o</option>
            <option>Taxa de Visita&ccedil;&atilde;o</option>
            <option>Ensaio</option>
            <!-- Esportivos -->
            <option>Jogo</option>
            <option>Torneio</option>
            <option>Campeonato</option>
            <option>Competi&ccedil;&atilde;o</option>
            <option>Maratona</option>
            <option>Corrida</option>
            <option>Exibi&ccedil;&atilde;o Esportiva</option>
            <!-- Chamamentos -->
            <option>Edital de Chamamento</option>
            <option>Edital</option>
            <option>Chamada P&uacute;blica</option>
            <option>Distribui&ccedil;&atilde;o de Bolsa</option>
            <option>Convoca&ccedil;&atilde;o</option>
            <option>Licita&ccedil;&atilde;o</option>
            <option>Solicita&ccedil;&atilde;o de Propostas</option>
            <option>Consulta P&uacute;blica</option>
            <!-- Religioso -->
            <option>Missas</option>
            <option>Retiro Espiritual</option>
            <option>Reuni&atilde;o de Ora&ccedil;&atilde;o</option>
            <option>Encontro Religioso</option>
            <option>C&iacute;rculo de Estudos B&iacute;blicos</option>
            <!-- Outros -->
            <option>NDA</option>
        </select>
    </div>
    <div class="wz-field">
        <label class="wz-field-label" for="expectativa">
            P&uacute;blico Esperado <span class="wz-required">*</span>
        </label>
        <select id="expectativa" name="expectativa" class="form-control">
            <option value="">Selecione uma op&ccedil;&atilde;o</option>
            <option value="1-300">At&eacute; 300 participantes</option>
            <option value="301-1000">De 301 a 1000 participantes</option>
            <option value="1000+">Acima de 1000 participantes</option>
        </select>
    </div>
    <div class="wz-field">
        <label class="wz-field-label" for="sigla">
            Sigla <span class="wz-required">*</span>
        </label>
        <input type="text" id="sigla" name="sigla" value="" class="form-control" placeholder="Ex: HUTEC">
    </div>
</div>

<div class="wz-row">
    <div class="wz-field">
        <label class="wz-field-label" for="cargaHoraria">
            Carga Hor&aacute;ria <span class="wz-required">*</span>
        </label>
        <input id="cargaHoraria" name="cargaHoraria" class="form-control" placeholder="Ex: 13h">
    </div>
    <div class="wz-field">
        <label class="wz-field-label" for="quantidadeVagasMinima">
            Vagas (m&iacute;nimo) <span class="wz-required">*</span>
        </label>
        <input id="quantidadeVagasMinima" name="quantidadeVagasMinima" class="form-control" placeholder="M&iacute;nimo vi&aacute;vel">
    </div>
    <div class="wz-field">
        <label class="wz-field-label" for="quantidadeVagas">
            Vagas (m&aacute;ximo) <span class="wz-required">*</span>
        </label>
        <input id="quantidadeVagas" name="quantidadeVagas" class="form-control" placeholder="Capacidade m&aacute;xima">
    </div>
</div>

<div class="wz-section-title">Per&iacute;odo de Inscri&ccedil;&otilde;es</div>
<div class="wz-row">
    <div class="wz-field">
        <label class="wz-field-label" for="dataInicioInscricoes">Data de In&iacute;cio</label>
        <input class="form-control" type="text" id="dataInicioInscricoes" name="dataInicioInscricoes" value="<?php echo $fws_['dataInicioInscricoes'] ?>">
    </div>
    <div class="wz-field">
        <label class="wz-field-label" for="dataFimInscricoes">Data de Fim</label>
        <input class="form-control" type="text" id="dataFimInscricoes" name="dataFimInscricoes" value="<?php echo $fws_['dataFimInscricoes'] ?>">
    </div>
</div>

<div class="wz-section-title">Per&iacute;odo de Envio de Trabalhos</div>
<div class="wz-row">
    <div class="wz-field">
        <label class="wz-field-label" for="dataInicioEnvioTrabalhos">Data de In&iacute;cio</label>
        <input class="form-control" type="text" id="dataInicioEnvioTrabalhos" name="dataInicioEnvioTrabalhos" value="<?php echo isset($fws_['dataInicioEnvioTrabalhos']) ? $fws_['dataInicioEnvioTrabalhos'] : '' ?>">
    </div>
    <div class="wz-field">
        <label class="wz-field-label" for="dataFimEnvioTrabalhos">Data de Fim</label>
        <input class="form-control" type="text" id="dataFimEnvioTrabalhos" name="dataFimEnvioTrabalhos" value="<?php echo isset($fws_['dataFimEnvioTrabalhos']) ? $fws_['dataFimEnvioTrabalhos'] : '' ?>">
    </div>
</div>

<div class="wz-field">
    <label class="wz-field-label" for="descricao_categoria">Especifica&ccedil;&atilde;o da Categoria</label>
    <span class="wz-field-hint">Detalhes adicionais sobre a categoria do evento (opcional).</span>
    <input id="descricao_categoria" name="descricao_categoria" class="form-control" placeholder="Detalhes adicionais...">
</div>

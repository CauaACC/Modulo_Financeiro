<div class="form-group col-sm-12" style="padding-left: 0px;">
    <label for="tituloEvento">Título do Evento:</label>
    <span style="color: red;">*</span>
    <input type="text" id="tituloEvento" name="tituloEvento" value=""
           class="form-control" placeholder="Título do Evento"
    >
</div>
<div class="form-group col-sm-6" style="padding-left: 0px;">
    <label for="emailCoordenador">E-mail do Coordenador:</label>
    <span style="color: red;">*</span>
    <input type="text" id="emailCoordenador" name="emailCoordenador" value=""
           class="form-control" placeholder="E-mail do Coordenador"
    >
</div>

<div class="form-group col-sm-3 style=padding-left: 0px">
    <label for="pais">País:</label>
    <span style="color: red;">*</span>
    <select id="pais" name="pais" class="form-control" onchange="habilitarCampos(); atualizarDDD();" required>
        <option value="">Selecione uma opção</option>
        <option value="África do Sul">África do Sul | South Africa - Ningizimu Afrika</option>
        <option value="Angola">Angola | Angola</option>
        <option value="Argentina">Argentina | Argentina</option>
        <option value="Bolívia">Bolívia | Bolivia - Buliwya - Wuliwya - Volívia</option>
        <option value="Brasil">Brasil</option>
        <option value="Chile">Chile | Chile</option>
        <option value="Colômbia">Colômbia | Colombia</option>
        <option value="Equador">Equador | Ikwayur - Ecuador - Ekuatur</option>
        <option value="Estados Unidos">Estados Unidos | United States</option>
        <option value="México">México | México</option>
        <option value="Moçambique">Moçambique | Moçambique - Mozambiki</option>
        <option value="Paraguai">Paraguai | Paraguái</option>
        <option value="Peru">Peru | Perú - Piruw</option>
        <option value="Portugal">Portugal | Portugal</option>
        <option value="Uruguai">Uruguai | Uruguay</option>
    </select>
</div>

<div class="form-group col-sm-3" style="padding-left: 0px">
    <label for="telefone">Telefone/WhatsApp:</label>
    <span style="color: red;">*</span>
    <input type="text" id="telefone" name="telefone" value="" class="form-control" placeholder="Telefone" disabled>
</div>

<div class="form-group col-sm-6" style="padding-left: 0px;">
    <label for="nomeCoordenador">Nome do Coordenador:</label>
    <span style="color: red;">*</span>
    <input type="text" id="nomeCoordenador" name="nomeCoordenador" value=""
           class="form-control" placeholder="Nome do Coordenador"
    >
</div>

<div class="form-group col-sm-3" style="padding-left: 0px;">
    <label for="sigla">Sigla/Abreviação do Evento:</label>
    <span style="color: red;">*</span>
    <input type="text" id="sigla" name="sigla" value=""
           class="form-control" placeholder="Sigla/Abreviação do Evento"
    >
</div>

<div class="form-group col-sm-3" style="padding-left: 0px">
    <label for="expectativa">Quantidade de público esperado</label>
    <span style="color: red;">*</span>
    <select id="expectativa" name="expectativa" class="form-control">
        <option value="">Selecione uma opção</option>
        <option value="1-300">até 300 participantes</option>
        <option value="301-1.000">de 301 até 1000 participantes</option>
        <option value="1000+">Acima de 1000 de participantes</option>
    </select>
</div>

<div class="form-group col-sm-3" style=padding-left: 0px">
    <label for="tipo">Tipo</label>
<span style="color: red;">*</span>
    <select id="tipo" name="tipo" class="form-control">
        <option value="">Selecione uma opção</option>
        <option value="Acadêmico">Acadêmico - Seminário/Jornada</option>
        <option value="Científico">Científico - Congresso/Simpósio</option>
        <option value="corporativo">corporativo - Empresarial</option>
        <option value="Curso">Workshop/Palestra</option>
        <option value="Entretenimento">Show/Festa</option>
        <option value="Religioso">Retiro/Encontro/Beneficente</option>
        <option value="Esportivo">Jogos/Torneios</option>
        <option value="Exibição">Feira/Exposição</option>
        <option value="Networking">Encontro/Meetup</option>
        <option value="Outro">Outro</option>
    </select>
</div>

<div class="form-group col-sm-3" style=padding-left: 0px">
    <label for="categoria">E a categoria</label>
<span style="color: red;">*</span>
    <select id="categoria" name="categoria" class="form-control">
        <option value="">Selecione uma opção</option>
        <option value="Agricultura,Pesca,Veterinária">Agricultura,Pesca,Veterinária</option>
        <option value="Artes e humanidades">Artes e humanidades</option>
        <option value="Ciências sociais e jornalismo">Ciências sociais e jornalismo</option>
        <option value="Direito">Direito</option>
        <option value="Educação">Educação</option>
        <option value="Empreendedorismo e inovação">Empreendedorismo e inovação</option>
        <option value="Engenharias">Engenharias</option>
        <option value="Gastronomia">Gastronomia</option>
        <option value="Medicina">Medicina</option>
        <option value="Negócios e administração">Negócios e administração</option>
        <option value="Saúde e bem-estar">Saúde e bem-estar</option>
        <option value="Desenvolvimento pessoal">Desenvolvimento pessoal</option>
        <option value="Religioso e Espiritualidade">Religioso e Espiritualidade</option>
        <option value="Outro">Outro</option>
    </select>
</div>

<div class="form-group col-sm-12" style="padding-left: 0px; margin-top: 10px ">
    <label for="dataEvento">Data de Início/Fim do Evento:</label>
    <span style="color: red;">*</span>

    <input type="text" id="dataInicioEvento" name="dataInicioEvento"
           value="<?php echo $fws_['dataInicioEvento'] ?>">

    <input type="text" id="dataFimEvento" name="dataFimEvento"
           value="<?php echo $fws_['dataFimEvento'] ?>">
</div>

<div class="form-group col-sm-12" style="padding-left: 0px; margin-top: 5px">
    <label for="dataInscricoes">Data de Início/Fim das Inscrições:</label>
    <span style="color: red;">*</span>

    <input type="text" id="dataInicioInscricoes" name="dataInicioInscricoes"
           value="<?php echo $fws_['dataInicioInscricoes'] ?>"
    >

    <input type="text" id="dataFimInscricoes" name="dataFimInscricoes"
           value="<?php echo $fws_['dataFimInscricoes'] ?>"
    >
</div>
<?php
// Format dates for datetime-local input
$dataIniVal = $objEvAlt->getDataIni();
$dataFimVal = $objEvAlt->getDataFim();
$dataIniF = ($dataIniVal && substr($dataIniVal, 0, 4) !== '0000') ? substr(str_replace(' ', 'T', $dataIniVal), 0, 16) : '';
$dataFimF = ($dataFimVal && substr($dataFimVal, 0, 4) !== '0000') ? substr(str_replace(' ', 'T', $dataFimVal), 0, 16) : '';
?>
<input type="hidden" name="COD_EVENTO" value="<?= $objEvAlt->getCodEvento() ?>">

<style>
.ev-section {
    background: #fff; border: 1px solid #e0e0e0; border-radius: 6px;
    margin-bottom: 14px; overflow: hidden;
}
.ev-section-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 8px 14px; background: #f5f6f8; border-bottom: 1px solid #e0e0e0;
    cursor: pointer; user-select: none;
    font-weight: 600; font-size: 13px; color: #495057;
}
.ev-section-header:hover { background: #ebedf0; }
.ev-section-header .fa { transition: transform 0.2s; }
.ev-section-header.ev-open .fa { transform: rotate(90deg); }
.ev-section-body { padding: 12px 14px; }
.ev-section-body.ev-collapsible { display: none; }
.ev-field { margin-bottom: 10px; }
.ev-field label { display: block; font-size: 12px; color: #666; margin-bottom: 2px; font-weight: 500; }
.ev-field input, .ev-field select, .ev-field textarea {
    width: 100%; padding: 6px 8px; border: 1px solid #ccc; border-radius: 4px;
    font-size: 13px; color: #333; background: #fff; box-sizing: border-box;
}
.ev-field input:focus, .ev-field select:focus, .ev-field textarea:focus {
    border-color: #2D937E; outline: none; box-shadow: 0 0 0 2px rgba(45,147,126,0.15);
}
.ev-field textarea { resize: vertical; }
.ev-row { display: flex; gap: 12px; flex-wrap: wrap; }
.ev-row > .ev-field { flex: 1; min-width: 0; }
.ev-row > .ev-field.ev-w2 { flex: 2; }
.ev-row > .ev-field.ev-w3 { flex: 3; }
.ev-row > .ev-field.ev-w4 { flex: 4; }
.ev-row > .ev-field.ev-small { flex: 0 0 120px; }
</style>

<!-- ======================== DADOS PRINCIPAIS ======================== -->
<div class="ev-section">
    <div class="ev-section-body">
        <div class="ev-row">
            <div class="ev-field ev-w4">
                <label>T&iacute;tulo</label>
                <input type="text" name="titulo" value="<?= $objEvAlt->getTitulo() ?>" maxlength="255">
            </div>
            <div class="ev-field">
                <label>Gestor</label>
                <select name="gestor">
                    <option value="">Selecione</option>
                    <?php
                    $fws_['gestor'] = $objEvAlt->getGestor();
                    CarregarOptionsAC::listarGestoresByCodUsuario($fws_, $debug);
                    ?>
                </select>
            </div>
        </div>
        <div class="ev-row">
            <div class="ev-field">
                <label>Status</label>
                <select name="STATUS">
                    <option value="-1">Alterar Status</option>
                    <option <?= verifItemSelected($objEvAlt->getStatus(), 'ATIVADO') ?> value="ATIVADO">Ativado</option>
                    <option <?= verifItemSelected($objEvAlt->getStatus(), 'DESATIVO') ?> value="DESATIVO">Desativado</option>
                </select>
            </div>
            <div class="ev-field">
                <label>Visibilidade</label>
                <select name="visibilidade">
                    <option <?= verifItemSelected($objEvAlt->getVisibilidade(), 'OCULTO') ?> value="OCULTO">Oculto</option>
                    <option <?= verifItemSelected($objEvAlt->getVisibilidade(), 'PUBLICO') ?> value="PUBLICO">P&uacute;blico</option>
                    <option <?= verifItemSelected($objEvAlt->getVisibilidade(), 'PRIVADO') ?> value="PRIVADO">Privado</option>
                </select>
            </div>
            <div class="ev-field ev-w2">
                <label>Finalidade do Projeto</label>
                <select name="finalidade">
                    <option <?= verifItemSelected($objEvAlt->getFinalidade(), 'Inscrição') ?> value="Inscrição">Inscri&ccedil;&atilde;o Simples</option>
                    <option <?= verifItemSelected($objEvAlt->getFinalidade(), 'Curso') ?> value="Curso">Curso</option>
                    <option <?= verifItemSelected($objEvAlt->getFinalidade(), 'Evento') ?> value="Evento">Evento</option>
                    <option <?= verifItemSelected($objEvAlt->getFinalidade(), 'Processo de Seleção') ?> value="Processo de Seleção">Processo de Sele&ccedil;&atilde;o</option>
                    <option <?= verifItemSelected($objEvAlt->getFinalidade(), 'Agenda') ?> value="Agenda">Reserva de Agenda</option>
                    <option <?= verifItemSelected($objEvAlt->getFinalidade(), 'Ingressos') ?> value="Ingressos">Vender Ingressos</option>
                    <option <?= verifItemSelected($objEvAlt->getFinalidade(), 'Produto Físico') ?> value="Produto Físico">Vender Produto F&iacute;sico</option>
                    <option <?= verifItemSelected($objEvAlt->getFinalidade(), 'Captar Dados Gerais') ?> value="Captar Dados Gerais">Captar Dados - Gerais</option>
                    <option <?= verifItemSelected($objEvAlt->getFinalidade(), 'Captar Dados NIS') ?> value="Captar Dados NIS">Captar Dados - NIS</option>
                    <option <?= verifItemSelected($objEvAlt->getFinalidade(), 'Captar Dados Socioeconomico') ?> value="Captar Dados Socioeconomico">Captar Dados - Socioecon&ocirc;mico</option>
                    <option <?= verifItemSelected($objEvAlt->getFinalidade(), 'Pedidos Genéricos') ?> value="Pedidos Genéricos">Pedidos Gen&eacute;ricos</option>
                    <option <?= verifItemSelected($objEvAlt->getFinalidade(), 'Documentação') ?> value="Documentação">Documenta&ccedil;&atilde;o Avulsa</option>
                    <option <?= verifItemSelected($objEvAlt->getFinalidade(), 'Outros') ?> value="Outros">Outros</option>
                </select>
            </div>
        </div>
        <div class="ev-row">
            <div class="ev-field">
                <label>Identifica&ccedil;&atilde;o do Projeto</label>
                <input type="text" name="projeto" value="<?= $objEvAlt->getProjeto() ?>" maxlength="20" placeholder="Ex: 032/2025">
            </div>
            <div class="ev-field">
                <label>Status do Projeto</label>
                <input type="text" name="status_projeto" value="<?= $objEvAlt->getStatusProjeto() ?>" maxlength="15">
            </div>
            <div class="ev-field">
                <label>Status no Processo</label>
                <input type="text" name="status_processo" value="<?= $objEvAlt->getStatusProcesso() ?>" maxlength="15">
            </div>
        </div>
        <div class="ev-row">
            <div class="ev-field ev-small">
                <label>Sigla</label>
                <input type="text" name="sigla" value="<?= $objEvAlt->getSigla() ?>" maxlength="15">
            </div>
            <div class="ev-field ev-w2">
                <label>Abrevia&ccedil;&atilde;o</label>
                <input type="text" name="abrev_evento" value="<?= $objEvAlt->getAbrevEvento() ?>" maxlength="50">
            </div>
            <div class="ev-field ev-small">
                <label>Ano Ref.</label>
                <input type="text" name="ano_evento" value="<?= $objEvAlt->getAnoEvento() ?>" maxlength="4">
            </div>
            <div class="ev-field">
                <label>Data de In&iacute;cio</label>
                <input type="datetime-local" name="data_ini" value="<?= $dataIniF ?>">
            </div>
            <div class="ev-field">
                <label>Data de Fim</label>
                <input type="datetime-local" name="data_fim" value="<?= $dataFimF ?>">
            </div>
        </div>
    </div>
</div>

<!-- ======================== CONTATO ======================== -->
<div class="ev-section">
    <div class="ev-section-header ev-toggle" data-target="#secContato">
        Contato <i class="fa fa-angle-right"></i>
    </div>
    <div class="ev-section-body ev-collapsible" id="secContato">
        <div class="ev-row">
            <div class="ev-field">
                <label>Nome Contato (p&uacute;blico)</label>
                <input type="text" name="nome_contato" value="<?= $objEvAlt->getNomeContato() ?>" maxlength="200">
            </div>
            <div class="ev-field">
                <label>Fone Contato (p&uacute;blico)</label>
                <input type="text" name="fone_contato" value="<?= $objEvAlt->getFoneContato() ?>" maxlength="150">
            </div>
            <div class="ev-field">
                <label>Email Contato (p&uacute;blico)</label>
                <input type="text" name="email_contato" value="<?= $objEvAlt->getEmailContato() ?>" maxlength="150">
            </div>
        </div>
        <div class="ev-row">
            <div class="ev-field">
                <label>Nome Contato (privado)</label>
                <input type="text" name="nome_contato_privado" value="<?= $objEvAlt->getNomeContatoPrivado() ?>" maxlength="200">
            </div>
            <div class="ev-field">
                <label>Fone Organizador (privado)</label>
                <input type="text" name="fone_organizador" value="<?= $objEvAlt->getFoneOrganizador() ?>" maxlength="200">
            </div>
            <div class="ev-field">
                <label>Email Organizador (privado)</label>
                <input type="text" name="email_organizador" value="<?= $objEvAlt->getEmailOrganizador() ?>" maxlength="200">
            </div>
        </div>
    </div>
</div>

<!-- ======================== CONFIGURAÇÃO DE E-MAIL ======================== -->
<div class="ev-section">
    <div class="ev-section-header ev-toggle" data-target="#secEmail">
        Configura&ccedil;&atilde;o de E-mail <i class="fa fa-angle-right"></i>
    </div>
    <div class="ev-section-body ev-collapsible" id="secEmail">
        <div class="ev-row">
            <div class="ev-field">
                <label>Abrev. E-mail</label>
                <input type="text" name="abrev_evento_email" value="<?= $objEvAlt->getAbrevEventoEmail() ?>" maxlength="150">
            </div>
            <div class="ev-field">
                <label>Email para ReplyTo</label>
                <input type="text" name="email_replay_to" value="<?= $objEvAlt->getEmailReplay_to() ?>" maxlength="150">
            </div>
        </div>
        <div class="ev-row">
            <div class="ev-field">
                <label>Email para Receber Inscri&ccedil;&otilde;es (BCC)</label>
                <input type="text" name="email_bcc" value="<?= $objEvAlt->getEmailBcc() ?>" maxlength="150">
            </div>
            <div class="ev-field">
                <label>Nome Contato no E-mail</label>
                <input type="text" name="email_contato_att" value="<?= $objEvAlt->getEmailContatoAtt() ?>" maxlength="150">
            </div>
        </div>
        <div class="ev-row">
            <div class="ev-field">
                <label>URL de Rodap&eacute; do E-mail</label>
                <input type="text" name="link_contato_att" value="<?= $objEvAlt->getLinkContatoAtt() ?>" maxlength="255">
            </div>
        </div>
    </div>
</div>

<!-- ======================== DOCUMENTOS ======================== -->
<div class="ev-section">
    <div class="ev-section-header ev-toggle" data-target="#secDocumentos">
        Configura&ccedil;&atilde;o para Documentos <i class="fa fa-angle-right"></i>
    </div>
    <div class="ev-section-body ev-collapsible" id="secDocumentos">
        <div class="ev-row">
            <div class="ev-field">
                <label>Coordenador do Evento</label>
                <input type="text" name="presidente" value="<?= $objEvAlt->getPresidente() ?>" maxlength="255">
            </div>
            <div class="ev-field">
                <label>T&iacute;tulo da Coordena&ccedil;&atilde;o</label>
                <input type="text" name="presidente_titulo" value="<?= $objEvAlt->getPresidenteTitulo() ?>" maxlength="150">
            </div>
        </div>
        <div class="ev-row">
            <div class="ev-field">
                <label>Per&iacute;odo de Realiza&ccedil;&atilde;o</label>
                <input type="text" name="periodo" value="<?= $objEvAlt->getPeriodo() ?>" maxlength="150">
            </div>
            <div class="ev-field">
                <label>Cidade-UF de Realiza&ccedil;&atilde;o</label>
                <input type="text" name="cidade_uf" value="<?= $objEvAlt->getCidadeUf() ?>" maxlength="150">
            </div>
        </div>
        <div class="ev-row">
            <div class="ev-field ev-w2">
                <label>Local de Realiza&ccedil;&atilde;o</label>
                <input type="text" name="desc_local_realizacao" value="<?= $objEvAlt->getDescLocalRealizacao() ?>" maxlength="200">
            </div>
            <div class="ev-field">
                <label>Data de Emiss&atilde;o</label>
                <input type="date" name="data_emissao_doc" value="<?= $objEvAlt->getDataEmissaoDoc() ?>">
            </div>
            <div class="ev-field">
                <label>CNPJ Organizador</label>
                <input type="text" name="cnpj_organizador" value="<?= $objEvAlt->getCnpjOrganizador() ?>" maxlength="200">
            </div>
        </div>
        <div class="ev-row">
            <div class="ev-field">
                <label>Nome do Organizador</label>
                <input type="text" name="nome_organizador" value="<?= $objEvAlt->getNomeOrganizador() ?>" maxlength="200">
            </div>
            <div class="ev-field">
                <label>Cidade-UF Organizador</label>
                <input type="text" name="cidade_organizador" value="<?= $objEvAlt->getCidadeOrganizador() ?>" maxlength="200">
            </div>
        </div>
        <div class="ev-row">
            <div class="ev-field">
                <label>Endere&ccedil;o Organizador</label>
                <textarea name="endereco_organizador" rows="3"><?= $objEvAlt->getEnderecoOrganizador() ?></textarea>
            </div>
        </div>
    </div>
</div>

<!-- ======================== URLs ======================== -->
<div class="ev-section">
    <div class="ev-section-header ev-toggle" data-target="#secUrl">
        Configura&ccedil;&atilde;o de URL <i class="fa fa-angle-right"></i>
    </div>
    <div class="ev-section-body ev-collapsible" id="secUrl">
        <div class="ev-row">
            <div class="ev-field">
                <label>Prefixo URL</label>
                <input type="text" name="url_prefixo" value="<?= $objEvAlt->getUrlPrefixo() ?>" maxlength="150">
            </div>
            <div class="ev-field">
                <label>URL Oficial</label>
                <input type="text" name="url_oficial" value="<?= $objEvAlt->getUrlOficial() ?>" maxlength="255">
            </div>
        </div>
        <div class="ev-row">
            <div class="ev-field">
                <label>URL da Logo</label>
                <input type="text" name="url_logo" value="<?= $objEvAlt->getUrlLogo() ?>" maxlength="255">
            </div>
            <div class="ev-field">
                <label>URL Logo de Documentos</label>
                <input type="text" name="url_logo_documentos" value="<?= $objEvAlt->getUrlLogoDocumentos() ?>" maxlength="255">
            </div>
        </div>
        <div class="ev-row">
            <div class="ev-field">
                <label>URL do Boleto</label>
                <input type="text" name="url_boleto" value="<?= $objEvAlt->getUrlBoleto() ?>" maxlength="255">
            </div>
            <div class="ev-field">
                <label>URL do CSS</label>
                <input type="text" name="url_css" value="<?= $objEvAlt->getUrlCss() ?>" maxlength="255">
            </div>
        </div>
        <div class="ev-row">
            <div class="ev-field">
                <label>URL do Hall Virtual</label>
                <input type="text" name="url_hall" value="<?= $objEvAlt->getUrlHall() ?>" maxlength="255">
            </div>
            <div class="ev-field"><!-- spacer --></div>
        </div>
    </div>
</div>

<!-- ======================== OUTRAS INFORMAÇÕES ======================== -->
<div class="ev-section">
    <div class="ev-section-header ev-toggle" data-target="#secOutras">
        Outras Informa&ccedil;&otilde;es <i class="fa fa-angle-right"></i>
    </div>
    <div class="ev-section-body ev-collapsible" id="secOutras">
        <div class="ev-row">
            <div class="ev-field">
                <label>ISSN</label>
                <input type="text" name="issn" value="<?= $objEvAlt->getIssn() ?>" maxlength="15">
            </div>
            <div class="ev-field">
                <label>Benefici&aacute;rios</label>
                <input type="text" name="beneficiario" value="<?= $objEvAlt->getBeneficiario() ?>" maxlength="15">
            </div>
            <div class="ev-field">
                <label>Categoria</label>
                <input type="text" name="categoria" value="<?= $objEvAlt->getcategoria() ?>" maxlength="50">
            </div>
            <div class="ev-field ev-small">
                <label>Max Oficinas</label>
                <input type="text" name="max_oficinas" value="<?= $objEvAlt->getMaxOficinas() ?>" maxlength="3">
            </div>
            <div class="ev-field ev-small">
                <label>Dura&ccedil;&atilde;o</label>
                <input type="text" name="duracao" value="<?= $objEvAlt->getDuracao() ?>" maxlength="10">
            </div>
        </div>
        <div class="ev-row">
            <div class="ev-field">
                <label>Outros Coordenadores</label>
                <input type="text" name="coordenadores" value="<?= $objEvAlt->getCoordenadores() ?>" maxlength="255">
            </div>
            <div class="ev-field">
                <label>Atribui&ccedil;&atilde;o</label>
                <input type="text" name="atribuicao" value="<?= $objEvAlt->getAtribuicao() ?>" maxlength="255">
            </div>
        </div>
        <div class="ev-row">
            <div class="ev-field">
                <label>Descri&ccedil;&atilde;o do Evento</label>
                <input type="text" name="desc_evento" value="<?= $objEvAlt->getDescEvento() ?>" maxlength="255">
            </div>
        </div>
        <div class="ev-row">
            <div class="ev-field">
                <label>Observa&ccedil;&atilde;o</label>
                <textarea name="descricao" rows="4"><?= $objEvAlt->getDescricao() ?></textarea>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function () {
    // Toggle das seções colapsáveis
    $('.ev-toggle').click(function() {
        var target = $(this).attr('data-target');
        $(target).slideToggle(200);
        $(this).toggleClass('ev-open');
    });

    $('#btnDuplicar').click(function(event) {
        event.preventDefault();
        /* A modal substitui o confirm(): mostra quem tem acesso a este projeto
         * e deixa escolher quem vai junto. Devolve os codigos por callback. */
        if (typeof dupmAbrir === 'function') {
            dupmAbrir($('#codEvento').val(), function (usuarios) {
                duplicarEvento(usuarios);
            });
        } else if (confirm('Tem certeza que deseja duplicar este evento?')) {
            duplicarEvento('');
        }
    });
});

function duplicarEvento(usuariosImportar) {
    var requestData = {
        'codEvento': $('#codEvento').val(),
        'c': 'GerenciarEvento',
        'm': 'duplicarEvento',
        'usuariosImportar': usuariosImportar || '',
        '_submit_check': '1'
    };

    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: requestData,
        dataType: "json",
        cache: false,
        beforeSend: function() {
            startLoading();
        },
        /* Eram 4s: a copia (evento + configuracao + templates + perfis +
         * atividades + taxas) passa disso com facilidade, e o botao
         * acusava falha de um duplicar que tinha dado certo. */
        timeout: 60000,
        error: function(ajax) {
            showResponse(ajax);
        },
        success: function(data) {
            processJson(data);
            if (data.statusMessage) {
                document.location.href='<?php echo WB_URL_DOMINIO.'/'.WB_GRUPO ?>/'+data.codEvento+'/inscricoes/index.php?content=view/admin/evento-cadastro-alterar.php&cod_evento='+data.codEvento
            }
        },
        complete: function() {
            stopLoading();
        }
    });
}
</script>

<?php
/**
 * Aba "Projeto" — o que DEFINE o evento, num lugar so.
 *
 * Motivo de existir: tab_eventos_configuracao tem 273 colunas espalhadas por 10
 * abas, e as poucas chaves que decidem *o que o projeto e* (o que vende, que
 * nota emite, quais modulos ligam) estavam misturadas com centenas de ajustes
 * de detalhe. Para saber a cara de um evento era preciso abrir cinco abas.
 *
 * REGRA DESTA ABA: so entra o que muda a NATUREZA do projeto. Parametro de
 * detalhe (datas, textos, credenciais) fica na aba do assunto.
 *
 * IMPORTANTE: as abas vivem dentro de UM UNICO <form>. Repetir aqui um campo
 * que exista em outra aba faria dois inputs com o mesmo name no mesmo POST — o
 * ultimo venceria e o usuario gravaria sem querer. Por isso os campos abaixo
 * foram MOVIDOS das abas de origem, nao copiados. O que continua sendo editado
 * em outra aba aparece aqui apenas como LEITURA.
 */

/**
 * Este projeto ja tem linha em tab_eventos_configuracao?
 *
 * getById() marca 'populado' quando encontra o registro, entao isPopulado() e a
 * resposta direta. E o que decide se o "importar" aparece: ele so existe
 * enquanto NAO ha configuracao, o que o torna uma acao de uma vez so.
 */
$prjTemConfiguracao = $objEvConf->isPopulado();

?>
<style>
    .prj-sec      { font-size:15px; font-weight:bold; }
    .prj-sec-ajuda{ font-size:11px; color:#888; font-weight:normal; display:block; margin-top:2px; }
    .prj-ajuda    { font-size:11px; color:#888; display:block; margin-top:2px; }
    .prj-alerta   { font-size:11px; background:#fff8e1; border:1px solid #ffe082; color:#7a5c00;
                    padding:6px 10px; border-radius:5px; margin-top:6px; display:none; }
    .prj-off      { opacity:.45; }
    .prj-leitura  { color:#555; }

</style>

<table border='0' width='700' cellspacing="10" cellpadding="10" class="dataTable">

    <!-- ------------------------------------------------------------------ -->
    <tr><td colspan="2">
        <span class="prj-sec">1. O que este projeto é</span>
        <span class="prj-sec-ajuda">Esta escolha comanda o resto da tela: cada tipo tem um jeito próprio de vender, cobrar e emitir nota.</span>
    </td></tr>

    <?php
    // Duplicar mora aqui porque e nesta aba que se responde "que projeto e
    // este" — e quem ja tem um projeto do jeito certo quase nunca quer
    // configurar outro do zero, quer o mesmo de novo. O botao ja existia, mas
    // so dentro da edicao do evento, onde ninguem esta quando quer CRIAR um.
    // Reusa GerenciarEvento::duplicarEvento, que copia evento + as 273 configs
    // + templates de e-mail.
    ?>
    <?php
    /* Restrito ao mesmo gate do botao "Duplicar Evento" da tela Editar Evento
     * (sistema 999). O caminho de producao continua sendo aquele botao; este
     * aqui fica visivel so para quem tem 999, para teste. Decidido 2026-08-20,
     * depois de o duplicar falhar em producao. */
    /* Verificacao GERAL (cod_evento='TODOS'), nao por evento -- ver comentario
     * igual em evento-cadastro-alterar.php: num evento sem equipe, a checagem
     * por evento esconde o botao de todo mundo. */
    if (FWA_AutenticacaoGeral($_SESSION['_cod_usuario'], enumSistema::SIS_GERENCIAR_ADM_GERAL, 5, false, false)) {
    ?>
    <tr><td>Repetir este projeto:</td><td>
            <button type="button" class="btn btn-warning btn-sm" id="prjBtnDuplicar">
                <i class="fa fa-copy"></i> Criar um novo projeto a partir deste
            </button>
            <span class="prj-ajuda">
                Cria um projeto novo já com <b>toda esta configuração</b> copiada — tipo, módulos, nota fiscal,
                textos e templates de e-mail. Você só ajusta nome, datas e valores.
                <b>Não copia</b> inscritos, pedidos nem pagamentos.
            </span>
            <div class="prj-alerta" id="prjMsgDuplicar"></div>
        </td></tr>
    <?php include_once("evento-duplicar-modal.php"); ?>
    <?php } ?>

    <?php if (!$prjTemConfiguracao) { ?>
    <?php
    // Importar so aparece ENQUANTO o projeto nao tem configuracao — ou seja, na
    // tela de INCLUIR. Duas razoes, e a segunda e a que manda:
    //
    // 1. E onde serve: projeto recem-criado nao tem nada a perder, e partir de
    //    um projeto que ja deu certo poupa as 273 configuracoes.
    // 2. E o controle de "importa uma vez so". Depois da primeira importacao a
    //    configuracao passa a existir, o bloco some, e nao ha como importar de
    //    novo — que era o caminho para acumular perfil repetido e para as taxas
    //    amplificarem (o SQL delas casa o perfil por "Copia de X", e com dois
    //    perfis de mesmo titulo o JOIN multiplica).
    //
    // Se um dia precisar reimportar num projeto ja configurado, isso volta a ser
    // uma decisao consciente, nao um botao sempre a mao.
    ?>
    <tr><td>Importar configuração:</td><td>
            <?php include_once("evento-configurar-importar.php") ?>
        </td></tr>
    <?php } ?>

    <tr><td>Tipo de projeto:</td><td>
            <select name='tipo_projeto' id='prjTipoProjeto'>
                <option value=''              <?= $objEvConf->getTipoProjeto() == ''              ? 'selected' : '' ?>>— não classificado —</option>
                <option value='CURSO'         <?= $objEvConf->getTipoProjeto() == 'CURSO'         ? 'selected' : '' ?>>Curso</option>
                <option value='CONGRESSO'     <?= $objEvConf->getTipoProjeto() == 'CONGRESSO'     ? 'selected' : '' ?>>Congresso / evento científico</option>
                <option value='EDITAL'        <?= $objEvConf->getTipoProjeto() == 'EDITAL'        ? 'selected' : '' ?>>Edital / seleção</option>
                <option value='AGENDA_VISITA' <?= $objEvConf->getTipoProjeto() == 'AGENDA_VISITA' ? 'selected' : '' ?>>Agenda com reserva de horário (visitação)</option>
                <option value='TEATRO'        <?= $objEvConf->getTipoProjeto() == 'TEATRO'        ? 'selected' : '' ?>>Teatro / bilheteria</option>
                <option value='SERVICO'       <?= $objEvConf->getTipoProjeto() == 'SERVICO'       ? 'selected' : '' ?>>Prestação de serviço (sem mercadoria)</option>
                <option value='LOJA'          <?= $objEvConf->getTipoProjeto() == 'LOJA'          ? 'selected' : '' ?>>Loja (venda de mercadoria)</option>
            </select>
            <div class="prj-alerta" id="prjPerfilTipo"></div>
            <span class="prj-ajuda">Projetos antigos ficam &quot;não classificado&quot; até alguém que conheça o evento escolher — nada é preenchido automaticamente.</span>
        </td></tr>

    <tr id='prjLinhaCanal'><td>Como a loja vende:</td><td>
            <select name='canal_venda_tipo' id='prjCanal'>
                <option value=''            <?= $objEvConf->getCanalVendaTipo() == ''            ? 'selected' : '' ?>>—</option>
                <option value='MARKETPLACE' <?= $objEvConf->getCanalVendaTipo() == 'MARKETPLACE' ? 'selected' : '' ?>>Marketplace (Shopee, Amazon…) — entrega no endereço</option>
                <option value='LOJA_FISICA' <?= $objEvConf->getCanalVendaTipo() == 'LOJA_FISICA' ? 'selected' : '' ?>>Balcão — cliente leva na hora</option>
            </select>
            <span class="prj-ajuda">Um canal por marketplace. A numeração da nota, porém, é do CNPJ — canais do mesmo emissor dividem a série.</span>
        </td></tr>

    <tr id='prjLinhaFluxo'><td>Fluxo de inscrição:</td><td>
            <select name="tipo_fluxo_inscricao" id="prjFluxo" class="form-control" style="width:auto;display:inline-block;">
                <option value="PADRAO" <?= ($objEvConf->getTipoFluxoInscricao() == '' || $objEvConf->getTipoFluxoInscricao() == 'PADRAO') ? 'selected' : '' ?>>
                    Padrão (CPF &rarr; Perfil &rarr; Dados)
                </option>
                <option value="TICKETS" <?= ($objEvConf->getTipoFluxoInscricao() == 'TICKETS') ? 'selected' : '' ?>>
                    Ingressos (Selecionar &rarr; Dados+CPF &rarr; Confirmação)
                </option>
                <option value="AGENDA" <?= ($objEvConf->getTipoFluxoInscricao() == 'AGENDA') ? 'selected' : '' ?>>
                    Agenda (Atividade/Dia &rarr; Perfil/Quantidade &rarr; Dados Pessoais)
                </option>
            </select>
            <span class="prj-ajuda">Caminho que o inscrito percorre. Trocar com evento em andamento afeta quem já se inscreveu.</span>
            <div class="prj-alerta" id="prjAlertaFluxo">
                Projeto de venda não usa fluxo de inscrição — o cliente vem do pedido, não de um formulário. Este campo fica sem efeito.
            </div>
        </td></tr>

    <tr id='prjLinhaLoja'><td>Identificador da loja no canal:</td><td>
            <input type='text' name='canal_venda_identificador' id='prjLoja' value='<?php echo $objEvConf->getCanalVendaIdentificador() ?>' maxlength='60' size='30' onBlur="trim(this);">
            <span class="prj-ajuda">Ex.: <i>shop_id</i> da Shopee. O token de acesso <b>não</b> fica aqui — ele expira e é guardado criptografado.</span>
        </td></tr>

    <!-- ------------------------------------------------------------------ -->
    <tr><td colspan="2">
        <span class="prj-sec">2. Nota fiscal</span>
        <span class="prj-sec-ajuda">O que o projeto vende decide o documento: serviço emite NFS-e; mercadoria emite NF-e ou NFC-e. Quem emite é o <b>emissor do gestor</b>, cadastrado em Vendas &rarr; Emissores de NF.</span>
    </td></tr>

    <tr><td>Documento fiscal emitido:</td><td>
            <select name='documento_fiscal_tipo' id='prjDoc'>
                <option value=''     <?= $objEvConf->getDocumentoFiscalTipo() == ''     ? 'selected' : '' ?>>Não emite nota fiscal</option>
                <option value='NFSE' <?= $objEvConf->getDocumentoFiscalTipo() == 'NFSE' ? 'selected' : '' ?>>NFS-e — nota de serviço (inscrição, ingresso, mensalidade)</option>
                <option value='55'   <?= $objEvConf->getDocumentoFiscalTipo() == '55'   ? 'selected' : '' ?>>NF-e (55) — mercadoria com entrega</option>
                <option value='65'   <?= $objEvConf->getDocumentoFiscalTipo() == '65'   ? 'selected' : '' ?>>NFC-e (65) — mercadoria presencial</option>
            </select>
            <div class="prj-alerta" id="prjAlertaDoc"></div>
        </td></tr>

    <tr data-campo="solicitacao_nf_habilitada"><td>Inscrito pode solicitar a nota:</td><td>
            <input type="radio" name="solicitacao_nf_habilitada" value="1" <?= verifItemChecked($objEvConf->getSolicitacaoNfHabilitada(), '1', false) ?> /> Sim
            &nbsp;&nbsp;
            <input type="radio" name="solicitacao_nf_habilitada" value="0" <?= verifItemChecked($objEvConf->getSolicitacaoNfHabilitada(), array('', 0, false), false) ?> /> Não
            <span class="prj-ajuda">Abre um pedido de nota na área do inscrito. Serve como plano B quando a emissão automática não se aplica.</span>
        </td></tr>

    <!-- ------------------------------------------------------------------ -->
    <tr><td colspan="2">
        <span class="prj-sec">3. Dinheiro</span>
        <span class="prj-sec-ajuda">Desligar a cobrança some com boleto, conciliação e cobrança em todo o projeto.</span>
    </td></tr>

    <tr data-campo="recebe_pagamento"><td>Recebe pagamento:</td><td>
            <input type="radio" name="recebe_pagamento" value="1" <?= verifItemChecked($objEvConf->getRecebePagamento(), '1', false) ?> /> Sim
            &nbsp;&nbsp;
            <input type="radio" name="recebe_pagamento" value="0" <?= verifItemChecked($objEvConf->getRecebePagamento(), array('', 0, false), false) ?> /> Não
            <span class="prj-ajuda">Não = projeto gratuito.</span>
        </td></tr>

    <tr data-campo="mens_ativa"><td>Cobra mensalidade:</td><td>
            <input type="radio" name="mens_ativa" value="1" <?= verifItemChecked($objEvConf->getMensAtiva(), '1', false) ?> /> Sim
            &nbsp;&nbsp;
            <input type="radio" name="mens_ativa" value="0" <?= verifItemChecked($objEvConf->getMensAtiva(), array('', 0, false), false) ?> /> Não
            <span class="prj-ajuda">Cobrança recorrente por este sistema. Valores e vencimentos ficam na aba <b>Inscrição</b>.</span>
        </td></tr>

    <tr><td>Gateway de pagamento:</td><td>
            <span class="prj-leitura"><b><?php echo $objEvConf->getGatewayPagamento() != '' ? htmlspecialchars($objEvConf->getGatewayPagamento()) : '—' ?></b></span>
            <span class="prj-ajuda">Somente leitura aqui — editado na aba <b>Pagamento</b>, junto das credenciais dele.</span>
        </td></tr>

    <!-- ------------------------------------------------------------------ -->
    <tr><td colspan="2">
        <span class="prj-sec">4. Módulos ativos</span>
        <span class="prj-sec-ajuda">Cada chave faz um menu inteiro aparecer ou sumir para a equipe.</span>
        <div class="prj-alerta" id="prjAlertaModulos">
            Projeto de venda normalmente não usa nenhum destes — eles são do mundo de evento (trabalhos, cursos, certificados).
        </div>
    </td></tr>

    <tr data-campo="trab_submissao"><td>Submissão de trabalhos:</td><td>
            <input type="radio" name="trab_submissao" value="1" <?= verifItemChecked($objEvConf->getTrabSubmissao(), '1', false) ?> /> Sim
            &nbsp;&nbsp;
            <input type="radio" name="trab_submissao" value="0" <?= verifItemChecked($objEvConf->getTrabSubmissao(), array('', 0, false), false) ?> /> Não
            <span class="prj-ajuda">Inscrito envia trabalho. Prazos e anexos na aba <b>Trabalho</b>.</span>
        </td></tr>

    <tr data-campo="trab_avaliacao"><td>Avaliação de trabalhos:</td><td>
            <input type="radio" name="trab_avaliacao" value="1" <?= verifItemChecked($objEvConf->getTrabAvaliacao(), '1', false) ?> /> Sim
            &nbsp;&nbsp;
            <input type="radio" name="trab_avaliacao" value="0" <?= verifItemChecked($objEvConf->getTrabAvaliacao(), array('', 0, false), false) ?> /> Não
            <div class="prj-alerta" id="prjAlertaAval">
                Avaliação sem submissão não tem o que avaliar — ligue também a submissão de trabalhos.
            </div>
        </td></tr>

    <tr data-campo="trab_premiacao"><td>Premiação de trabalhos:</td><td>
            <input type="radio" name="trab_premiacao" value="1" <?= verifItemChecked($objEvConf->getTrabPremiacao(), '1', false) ?> /> Sim
            &nbsp;&nbsp;
            <input type="radio" name="trab_premiacao" value="0" <?= verifItemChecked($objEvConf->getTrabPremiacao(), array('', 0, false), false) ?> /> Não
            <div class="prj-alerta" id="prjAlertaPrem">
                Premiação depende da avaliação dos trabalhos.
            </div>
        </td></tr>

    <tr data-campo="curso_habilitado"><td>Cursos EAD:</td><td>
            <input type="radio" name="curso_habilitado" value="1" <?= verifItemChecked($objEvConf->getCursoHabilitado(), '1', false) ?> /> Sim
            &nbsp;&nbsp;
            <input type="radio" name="curso_habilitado" value="0" <?= verifItemChecked($objEvConf->getCursoHabilitado(), array('', 0, false), false) ?> /> Não
            <span class="prj-ajuda">Conteúdo, tarefas e presença. Abre o menu <b>Cursos EAD</b>.</span>
        </td></tr>

    <tr data-campo="registro_diario"><td>Registro de Atividade:</td><td>
            <input type="radio" name="registro_diario" value="1" <?= verifItemChecked($objEvConf->getRegistroDiario(), '1', false) ?> /> Sim
            &nbsp;&nbsp;
            <input type="radio" name="registro_diario" value="0" <?= verifItemChecked($objEvConf->getRegistroDiario(), array('', 0, false), false) ?> /> Não
            <span class="prj-ajuda">
                O participante lança o <b>trabalho que ele fez</b> — dia, horas trabalhadas e descrição — e um coordenador valida.
                É diário de bordo com aprovação.
                <b>Não confundir</b> com inscrição em oficinas/atividades do evento: aquilo é escolha de atividade pelo inscrito e se configura na aba <b>Atividade</b>.
            </span>
        </td></tr>

    <tr data-campo="habilitar_modelos_contrato"><td>Modelos de contrato:</td><td>
            <input type="radio" name="habilitar_modelos_contrato" value="1" <?= verifItemChecked($objEvConf->getHabilitarModelosContrato(), '1', false) ?> /> Sim
            &nbsp;&nbsp;
            <input type="radio" name="habilitar_modelos_contrato" value="0" <?= verifItemChecked($objEvConf->getHabilitarModelosContrato(), array('', 0, false), false) ?> /> Não
        </td></tr>

    <tr data-campo="hall_virtual_ativar"><td>Hall virtual:</td><td>
            <input type="radio" name="hall_virtual_ativar" value="1" <?= verifItemChecked($objEvConf->getHallVirtualAtivar(), '1', false) ?> /> Sim
            &nbsp;&nbsp;
            <input type="radio" name="hall_virtual_ativar" value="0" <?= verifItemChecked($objEvConf->getHallVirtualAtivar(), array('', 0, false), false) ?> /> Não
            <span class="prj-ajuda">Ambiente online do evento. URL e período na aba <b>Inscrição</b>.</span>
        </td></tr>

    <tr data-campo="pwa_habilitado"><td>Aplicativo no celular (PWA):</td><td>
            <input type="radio" name="pwa_habilitado" value="1" <?= verifItemChecked($objEvConf->getPwaHabilitado(), '1', false) ?> /> Sim
            &nbsp;&nbsp;
            <input type="radio" name="pwa_habilitado" value="0" <?= verifItemChecked($objEvConf->getPwaHabilitado(), array('', 0, false), false) ?> /> Não
            <span class="prj-ajuda">Requer os ícones em <i>resources/public/{cod_evento}/</i>.</span>
        </td></tr>

    <!-- ------------------------------------------------------------------ -->
    <tr><td colspan="2">
        <span class="prj-sec">5. O que o inscrito recebe</span>
        <span class="prj-sec-ajuda">Só liga ou desliga a entrega. Datas, textos e modelos ficam nas abas <b>Certificados</b> e <b>Documentos</b>.</span>
    </td></tr>

    <tr data-campo="emitir_certificado"><td>Certificado:</td><td>
            <input type="radio" name="emitir_certificado" value="1" <?= verifItemChecked($objEvConf->getEmitirCertificado(), '1', false) ?> /> Sim
            &nbsp;&nbsp;
            <input type="radio" name="emitir_certificado" value="0" <?= verifItemChecked($objEvConf->getEmitirCertificado(), array('', 0, false), false) ?> /> Não
        </td></tr>

    <tr data-campo="emitir_comprovante"><td>Comprovante de inscrição:</td><td>
            <input type="radio" name="emitir_comprovante" value="1" <?= verifItemChecked($objEvConf->getEmitirComprovante(), '1', false) ?> /> Sim
            &nbsp;&nbsp;
            <input type="radio" name="emitir_comprovante" value="0" <?= verifItemChecked($objEvConf->getEmitirComprovante(), array('', 0, false), false) ?> /> Não
        </td></tr>

    <tr data-campo="emitir_recibo"><td>Recibo:</td><td>
            <input type="radio" name="emitir_recibo" value="1" <?= verifItemChecked($objEvConf->getEmitirRecibo(), '1', false) ?> /> Sim
            &nbsp;&nbsp;
            <input type="radio" name="emitir_recibo" value="0" <?= verifItemChecked($objEvConf->getEmitirRecibo(), array('', 0, false), false) ?> /> Não
            <span class="prj-ajuda">Recibo é documento interno — não substitui nota fiscal.</span>
        </td></tr>

    <tr data-campo="emitir_declaracao_presenca"><td>Declaração de presença:</td><td>
            <input type="radio" name="emitir_declaracao_presenca" value="1" <?= verifItemChecked($objEvConf->getEmitirDeclaracaoPresenca(), '1', false) ?> /> Sim
            &nbsp;&nbsp;
            <input type="radio" name="emitir_declaracao_presenca" value="0" <?= verifItemChecked($objEvConf->getEmitirDeclaracaoPresenca(), array('', 0, false), false) ?> /> Não
        </td></tr>

</table>

<script type="text/javascript">
/**
 * Coerencia entre as escolhas desta aba.
 *
 * Regra adotada: AVISAR e desabilitar o que e impossivel, sem apagar valor
 * gravado. Zerar campo por conta propria numa tela de configuracao e pior que
 * deixar uma combinacao esquisita visivel — o usuario perde dado sem perceber.
 */
(function () {
    /**
     * O que cada tipo de projeto significa na pratica. Fonte unica das regras
     * desta aba — mudar o comportamento de um tipo se faz aqui, num lugar so.
     *
     *   venda    : vende mercadoria (usa modulo Vendas, nao usa inscricao)
     *   doc      : documento fiscal compativel
     *   fluxo    : fluxo de inscricao tipico (so sugestao, nao forca)
     *   modulos  : modulos de evento fazem sentido?
     *   resumo   : o texto que explica o tipo para quem configura
     */
    /**
     * naoAplica: campos que este tipo NAO tem como usar, com o porque.
     *
     * Criterio conservador — so entra aqui o que e ESTRUTURALMENTE impossivel
     * (nao existe inscrito, nao existe evento), nunca o que e apenas incomum.
     * Marcar como incompativel algo que o cliente um dia queira usar e pior do
     * que deixar passar: vira suporte, nao seguranca.
     */
    var PRJ_TIPOS = {
        'CURSO': {
            venda: false, doc: ['NFSE'], fluxo: 'PADRAO', modulos: true,
            resumo: 'Inscrição em curso. Emite <b>NFS-e</b> (serviço). Costuma usar Cursos EAD, presença e certificado.',
            naoAplica: {}
        },
        'CONGRESSO': {
            venda: false, doc: ['NFSE'], fluxo: 'PADRAO', modulos: true,
            resumo: 'Inscrição em evento científico. Emite <b>NFS-e</b>. Costuma usar submissão e avaliação de trabalhos, e certificado.',
            naoAplica: {}
        },
        'EDITAL': {
            venda: false, doc: ['NFSE', ''], fluxo: 'PADRAO', modulos: true,
            resumo: 'Seleção por edital. Em geral <b>não cobra</b> e não emite nota; usa submissão, avaliação e recurso.',
            naoAplica: {
                'mens_ativa': 'edital não tem cobrança recorrente'
            }
        },
        'AGENDA_VISITA': {
            venda: false, doc: ['NFSE', ''], fluxo: 'AGENDA', modulos: true,
            resumo: 'Reserva de horário de visita. Fluxo <b>Agenda</b>: o visitante escolhe dia e horário antes dos dados pessoais.',
            naoAplica: {
                'trab_submissao': 'visitação não recebe trabalhos',
                'trab_avaliacao': 'visitação não recebe trabalhos',
                'trab_premiacao': 'visitação não recebe trabalhos',
                'mens_ativa': 'visita é avulsa, não recorrente'
            }
        },
        'TEATRO': {
            venda: false, doc: ['NFSE'], fluxo: 'TICKETS', modulos: false,
            resumo: 'Bilheteria. Fluxo <b>Ingressos</b>. Ingresso é <b>serviço</b>, então emite NFS-e — não precisa de Inscrição Estadual nem CSC.',
            naoAplica: {
                'trab_submissao': 'bilheteria não recebe trabalhos',
                'trab_avaliacao': 'bilheteria não recebe trabalhos',
                'trab_premiacao': 'bilheteria não recebe trabalhos',
                'curso_habilitado': 'bilheteria não tem conteúdo de curso',
                'registro_diario': 'não há trabalho diário a registrar',
                'emitir_certificado': 'quem compra ingresso não recebe certificado',
                'emitir_declaracao_presenca': 'a entrada é o próprio ingresso',
                'mens_ativa': 'ingresso é avulso, não recorrente'
            }
        },
        // Prestacao de servico: fatura por pedido e emite NFS-e, sem evento e
        // sem mercadoria. E o caso do proprio MEI da OPCTEC. Nao e "venda" no
        // sentido do modulo Vendas (que e de mercadoria), mas tambem nao tem
        // inscricao — por isso fica com fluxo nulo e sem os modulos de evento.
        'SERVICO': {
            venda: false, doc: ['NFSE'], fluxo: null, modulos: false,
            resumo: 'Prestação de serviço (consultoria, suporte, manutenção). Fatura por pedido e emite <b>NFS-e</b>. Não tem inscrição, trabalho nem certificado.',
            naoAplica: {
                'trab_submissao': 'prestação de serviço não recebe trabalhos',
                'trab_avaliacao': 'prestação de serviço não recebe trabalhos',
                'trab_premiacao': 'prestação de serviço não recebe trabalhos',
                'curso_habilitado': 'não há conteúdo de curso',
                'registro_diario': 'não há participante lançando horas',
                'hall_virtual_ativar': 'não há evento online',
                'pwa_habilitado': 'o app é da área do inscrito, que aqui não existe',
                'emitir_certificado': 'cliente de serviço não recebe certificado',
                'emitir_comprovante': 'não há inscrição a comprovar',
                'emitir_declaracao_presenca': 'não há presença a declarar'
            }
        },
        'LOJA': {
            venda: true, doc: ['55', '65'], fluxo: null, modulos: false,
            resumo: 'Venda de mercadoria. Aparece no módulo <b>Vendas</b>, não usa inscrição, e emite <b>NF-e (55)</b> ou <b>NFC-e (65)</b>.',
            naoAplica: {
                'trab_submissao': 'loja não tem inscrito nem trabalho',
                'trab_avaliacao': 'loja não tem inscrito nem trabalho',
                'trab_premiacao': 'loja não tem inscrito nem trabalho',
                'curso_habilitado': 'loja não tem conteúdo de curso',
                'registro_diario': 'loja não tem participante lançando horas',
                'hall_virtual_ativar': 'não há evento online',
                'pwa_habilitado': 'o app é da área do inscrito, que a loja não tem',
                'emitir_certificado': 'cliente de loja não recebe certificado',
                'emitir_comprovante': 'não há inscrição a comprovar',
                'emitir_declaracao_presenca': 'não há presença a declarar',
                'solicitacao_nf_habilitada': 'o cliente não tem login para pedir a nota',
                'mens_ativa': 'venda de mercadoria não é cobrança recorrente'
            }
        }
    };

    /**
     * Campo escondido continua no DOM mas NAO pode ir no POST: se fosse, o
     * radio "Nao" iria junto e desligaria em silencio uma config que o usuario
     * nunca viu. Desabilitado, o campo simplesmente nao e enviado e o setAll
     * preserva o que esta no banco.
     */
    function prjHabilitarLinha(linha, habilitar) {
        var campos = linha.querySelectorAll('input, select, textarea');
        for (var i = 0; i < campos.length; i++) {
            campos[i].disabled = !habilitar;
        }
    }

    function prjNomeTipo(select) {
        if (!select || select.selectedIndex < 0) { return 'este tipo'; }
        return select.options[select.selectedIndex].text.replace(/—.*$/, '').trim();
    }

    function prjAplicar() {
        var tipoProjeto = document.getElementById('prjTipoProjeto');
        var canal = document.getElementById('prjCanal');
        var doc   = document.getElementById('prjDoc');
        if (!tipoProjeto || !doc) { return; }

        var perfil = PRJ_TIPOS[tipoProjeto.value] || null;

        // Explica o tipo escolhido.
        var boxTipo = document.getElementById('prjPerfilTipo');
        boxTipo.innerHTML = perfil ? perfil.resumo : '';
        boxTipo.style.display = perfil ? 'block' : 'none';

        // "Como a loja vende" so existe para LOJA.
        var ehLoja = tipoProjeto.value === 'LOJA';
        var linhaCanal = document.getElementById('prjLinhaCanal');
        if (linhaCanal) { linhaCanal.style.display = ehLoja ? '' : 'none'; }

        var ehVenda      = perfil ? perfil.venda : (canal && canal.value !== '');
        var ehPresencial = ehLoja && canal && canal.value === 'LOJA_FISICA';
        var tipo = { value: ehVenda ? (canal ? canal.value : 'MARKETPLACE') : '' };

        // 1) Loja nao usa fluxo de inscricao. Nos demais, avisa quando o fluxo
        //    escolhido nao e o tipico do tipo — sem trocar por conta propria.
        var linhaFluxo = document.getElementById('prjLinhaFluxo');
        var fluxo      = document.getElementById('prjFluxo');
        var alertaFluxo = document.getElementById('prjAlertaFluxo');
        if (fluxo)      { fluxo.disabled = ehVenda; }
        if (linhaFluxo) { linhaFluxo.className = ehVenda ? 'prj-off' : ''; }

        if (ehVenda) {
            alertaFluxo.innerHTML = 'Loja não usa fluxo de inscrição — o cliente vem do pedido, não de um formulário.';
            alertaFluxo.style.display = 'block';
        } else if (perfil && perfil.fluxo && fluxo && fluxo.value !== perfil.fluxo) {
            var nomes = {'PADRAO': 'Padrão', 'TICKETS': 'Ingressos', 'AGENDA': 'Agenda'};
            alertaFluxo.innerHTML = 'Este tipo normalmente usa o fluxo <b>' + (nomes[perfil.fluxo] || perfil.fluxo)
                                  + '</b>. Só mude se souber por quê.';
            alertaFluxo.style.display = 'block';
        } else {
            alertaFluxo.style.display = 'none';
        }

        // 2) Identificador da loja so faz sentido em marketplace.
        var linhaLoja = document.getElementById('prjLinhaLoja');
        if (linhaLoja) { linhaLoja.style.display = (ehLoja && canal && canal.value === 'MARKETPLACE') ? '' : 'none'; }

        // 3) Documento fiscal x tipo de projeto.
        //    Servico (NFS-e) para curso/congresso/edital/agenda/teatro;
        //    mercadoria (55/65) para loja. NFC-e so presencial.
        var msg = '';
        for (var i = 0; i < doc.options.length; i++) {
            var v = doc.options[i].value;
            var bloqueado = false;
            if (perfil) {
                // Vazio ("nao emite") so e oferecido a quem pode nao emitir.
                bloqueado = (perfil.doc.indexOf(v) === -1) && v !== '';
                if (v === '' && perfil.doc.indexOf('') === -1) { bloqueado = false; }
                if (v === '65' && !ehPresencial)               { bloqueado = true; }
            }
            doc.options[i].disabled = bloqueado;
        }

        if (doc.value !== '' && perfil && perfil.doc.indexOf(doc.value) === -1) {
            msg = perfil.venda
                ? 'Este tipo vende <b>mercadoria</b>: o documento é NF-e (55) ou NFC-e (65).'
                : 'Este tipo vende <b>serviço</b>: o documento é NFS-e.';
        } else if (doc.value === '65' && !ehPresencial) {
            msg = 'NFC-e é para venda presencial. Para entrega no endereço use NF-e (55).';
        } else if (doc.value === '65') {
            msg = 'NFC-e exige <b>Inscrição Estadual e CSC</b> no emissor. Sem os dois, use NF-e (55).';
        } else if (doc.value === '55') {
            msg = 'Confirme que o emissor tem <b>Inscrição Estadual</b> e série de NF-e configuradas.';
        } else if (doc.value === 'NFSE') {
            msg = 'Precisa de um emissor com <b>certificado A1</b> e código de tributação em Vendas &rarr; Emissores de NF.';
        }
        var alertaDoc = document.getElementById('prjAlertaDoc');
        alertaDoc.innerHTML = msg;
        alertaDoc.style.display = msg ? 'block' : 'none';

        // 4) Opcoes que NAO fazem sentido para o tipo escolhido.
        //
        //    Regra: se nao se aplica E esta desligada, SOME da tela — e o que
        //    torna a aba util, mostrando so o que decide o projeto. Mas se
        //    estiver LIGADA, aparece esmaecida com o motivo, porque esconder
        //    uma config ativa faria o usuario perder de vista justamente a
        //    inconsistencia que ele precisa corrigir (tipico de projeto antigo
        //    reclassificado).
        var naoAplica = (perfil && perfil.naoAplica) ? perfil.naoAplica : {};
        var escondidos = 0;
        var linhas = document.querySelectorAll('#projeto tr[data-campo]');

        for (var j = 0; j < linhas.length; j++) {
            var linha  = linhas[j];
            var campo  = linha.getAttribute('data-campo');
            var motivo = naoAplica[campo];

            var nota = linha.querySelector('.prj-nao-aplica');
            if (!nota) {
                nota = document.createElement('span');
                nota.className = 'prj-nao-aplica prj-ajuda';
                var celula = linha.getElementsByTagName('td')[1];
                if (celula) { celula.appendChild(nota); }
            }

            if (!motivo) {
                linha.style.display = '';
                linha.classList.remove('prj-off');
                nota.innerHTML = '';
                nota.style.display = 'none';
                prjHabilitarLinha(linha, true);
                continue;
            }

            var ligado = !!linha.querySelector("input[type='radio'][value='1']:checked");
            if (ligado) {
                // Config ativa que o tipo nao comporta: mostra e explica.
                linha.style.display = '';
                linha.classList.add('prj-off');
                nota.innerHTML = '<b>Ligado, mas não se aplica a este tipo</b> — ' + motivo
                               + '. Desligue, ou revise o tipo do projeto.';
                nota.style.display = 'block';
                prjHabilitarLinha(linha, true);
            } else {
                linha.style.display = 'none';
                escondidos++;
                prjHabilitarLinha(linha, false);
            }
        }

        var boxMod = document.getElementById('prjAlertaModulos');
        if (escondidos > 0) {
            boxMod.innerHTML = escondidos + ' opção(ões) foram ocultadas por não fazerem sentido em '
                             + '<b>' + prjNomeTipo(tipoProjeto) + '</b>. Trocar o tipo traz de volta.';
            boxMod.style.display = 'block';
        } else {
            boxMod.style.display = 'none';
        }

        // 5) Dependencias dentro dos trabalhos.
        var sub  = document.querySelector("input[name='trab_submissao']:checked");
        var aval = document.querySelector("input[name='trab_avaliacao']:checked");
        var prem = document.querySelector("input[name='trab_premiacao']:checked");
        document.getElementById('prjAlertaAval').style.display =
            (aval && aval.value === '1' && sub && sub.value !== '1') ? 'block' : 'none';
        document.getElementById('prjAlertaPrem').style.display =
            (prem && prem.value === '1' && aval && aval.value !== '1') ? 'block' : 'none';
    }

    // O <select> desabilitado nao e enviado no POST, e o setAll deixaria o
    // valor antigo no banco — o que aqui e o comportamento desejado (nao
    // apagamos o fluxo de um projeto que virou canal de venda).
    /**
     * Duplicar reusa o GerenciarEvento::duplicarEvento que ja existia. Dois
     * cuidados: o botao e type=button para nao submeter o form de configuracao
     * junto, e a confirmacao diz o que NAO vem, porque a duvida real de quem
     * clica e se vai arrastar inscrito e pagamento do evento antigo.
     */
    function prjDuplicar() {
        var codEvento = '<?php echo COD_EVENTO ?>';
        /* A modal explica o que vem junto e deixa escolher quem vai. */
        if (typeof dupmAbrir === 'function') {
            dupmAbrir(codEvento, function (usuarios) { prjDuplicarConfirmado(usuarios); });
        } else {
            prjDuplicarConfirmado('');
        }
    }

    function prjDuplicarConfirmado(usuariosImportar) {
        var box = document.getElementById('prjMsgDuplicar');
        var codEvento = '<?php echo COD_EVENTO ?>';

        box.innerHTML = 'Duplicando...';
        box.style.display = 'block';

        $.ajax({
            type: 'POST',
            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
            data: { 'codEvento': codEvento, 'c': 'GerenciarEvento',
                    'm': 'duplicarEvento', 'usuariosImportar': usuariosImportar || '',
                    '_submit_check': '1' },
            dataType: 'json',
            cache: false,
            /* Sem timeout a tela ficava em "Duplicando..." para sempre quando a
             * resposta nao voltava -- o sintoma relatado em 2026-08-20. Copiar
             * evento + configuracao + templates + perfis + atividades + taxas
             * leva alguns segundos; 60s cobre com folga sem prender ninguem. */
            timeout: 60000,
            success: function (data) {
                if (data && data.statusMessage == '1') {
                    var texto = (data.message || 'Projeto duplicado.');
                    if (data.codEvento) {
                        texto += ' <a href="index.php?content=view/admin/evento-cadastro-alterar.php&cod_evento='
                               + data.codEvento + '">Abrir o projeto novo</a> para renomear e ajustar as datas.';
                    } else {
                        texto += ' Procure por <b>&quot;Cópia de…&quot;</b> na lista de eventos.';
                    }
                    box.innerHTML = texto;
                } else {
                    box.innerHTML = (data && data.message) ? data.message : 'Não foi possível duplicar.';
                }
            },
            error: function (xhr, tipo) {
                if (tipo === 'timeout') {
                    box.innerHTML = 'A duplicação passou de 60 segundos e o navegador desistiu de esperar. '
                                  + 'Confira na lista se o projeto <b>&quot;Cópia de…&quot;</b> foi criado antes de tentar de novo.';
                } else {
                    box.innerHTML = 'Falha ao duplicar (' + (xhr.status || 'sem resposta') + '). '
                                  + 'Se repetir, avise o suporte com o horário da tentativa.';
                }
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        prjAplicar();
        var btnDup = document.getElementById('prjBtnDuplicar');
        if (btnDup) { btnDup.addEventListener('click', prjDuplicar); }
        /* O importar tem JS proprio, dentro de evento-configurar-importar.php —
         * ele precisa funcionar tambem na tela onde esta pagina nem existe. */
        var gatilhos = ['prjTipoProjeto', 'prjCanal', 'prjDoc', 'prjFluxo'];
        for (var i = 0; i < gatilhos.length; i++) {
            var el = document.getElementById(gatilhos[i]);
            if (el) { el.addEventListener('change', prjAplicar); }
        }
        var radios = document.querySelectorAll(
            "input[name='trab_submissao'], input[name='trab_avaliacao'], input[name='trab_premiacao']");
        for (var k = 0; k < radios.length; k++) {
            radios[k].addEventListener('change', prjAplicar);
        }
    });
})();
</script>

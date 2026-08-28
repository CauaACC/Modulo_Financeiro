<?php
/**
 * Renderiza botão "Minhas Outras Agendas (X)" no topo da área do inscrito + modal escondido
 * com a lista. Cada item da lista mostra "DD/MM/YYYY - Tipo - HH:MM às HH:MM" e botão "Acessar"
 * que troca o cod_inscrito ativo via carregarParticipacaoEvento() (já definido na área).
 *
 * Inclusão controlada por inscricao_congresso_ok.php (só renderiza quando tipo_fluxo='AGENDA').
 */
try {
    $listaAgendas = $objInscrito->carregarListaAgendasMesmoCpfMesmoEvento($debug);
    if (!is_array($listaAgendas) || count($listaAgendas) == 0) {
        return;
    }
    // Dedup por cod_inscrito: se o inscrito tem mais de uma oficina, o LEFT JOIN traz linhas duplicadas
    $jaVistos = array();
    $agendasUnicas = array();
    foreach ($listaAgendas as $a) {
        $c = (int) $a->getCodInscrito();
        if (isset($jaVistos[$c])) { continue; }
        $jaVistos[$c] = true;
        $agendasUnicas[] = $a;
    }
    $listaAgendas = $agendasUnicas;
    // Usa direto da sessão — $objInscrito->getCodInscrito() pode estar sobrescrito pelos LEFT JOINs do getById
    $codInscritoAtual = (int) ($_SESSION['_cod_inscrito'] ?? $objInscrito->getCodInscrito());
    $qtdTotal = count($listaAgendas);
    // Se só tem a inscrição atual (e nenhuma outra), não exibe botão/modal
    $qtdOutras = 0;
    foreach ($listaAgendas as $a) {
        if ((int) $a->getCodInscrito() !== $codInscritoAtual) { $qtdOutras++; }
    }
    if ($qtdOutras == 0) { return; }
    $nomeMeses = array('','JAN','FEV','MAR','ABR','MAI','JUN','JUL','AGO','SET','OUT','NOV','DEZ');
    ?>
    <style>
        /* Botão Minhas Agendas — usa padrão btn-xs do projeto pra casar com "Editar Dados" / "Pagar via Boleto" */
        #btnMinhasAgendas { margin-left:6px; }
        #btnMinhasAgendas .badge-qtd { display:inline-block; background:#fff; color:#2D937E; border-radius:9px;
                                        padding:0 6px; font-size:11px; font-weight:bold; margin-left:4px; }
        .minhas-agendas-modal-item { display:flex; align-items:center; gap:12px; padding:10px 12px; margin-bottom:8px;
                                      border:1px solid #e0e0e0; border-left:3px solid #2D937E; border-radius:5px; background:#fff; }
        .minhas-agendas-modal-item .agdesc { flex:1; }
        .minhas-agendas-modal-item .agdesc .quando { font-size:14px; font-weight:bold; color:#2D937E; }
        .minhas-agendas-modal-item .agdesc .grupo  { font-size:12px; color:#666; margin-top:2px; }
        .minhas-agendas-modal-item .agdesc .pago     { display:inline-block; font-size:11px; padding:2px 8px; border-radius:10px; background:#e6f4ef; color:#1f6f5b; font-weight:bold; margin-top:4px; }
        .minhas-agendas-modal-item .agdesc .pendente { display:inline-block; font-size:11px; padding:2px 8px; border-radius:10px; background:#fdf3e0; color:#8b6914; font-weight:bold; margin-top:4px; }
        .minhas-agendas-modal-item.atual { background:#f6fbfa; border-color:#2D937E; }
        .minhas-agendas-modal-item .badge-atual { display:inline-block; font-size:11px; padding:2px 8px; border-radius:10px; background:#2D937E; color:#fff; font-weight:bold; margin-left:6px; vertical-align:middle; }
        .minhas-agendas-modal-item .acoes { display:flex; flex-direction:column; gap:6px; align-items:flex-end; min-width:160px; }
        .minhas-agendas-modal-item .btn-cancelar { color:#c0392b; background:#fff; border:1px solid #f5c6c6; padding:5px 10px; font-size:12px; border-radius:4px; cursor:pointer; transition:background 0.15s, color 0.15s; }
        .minhas-agendas-modal-item .btn-cancelar i { margin-right:4px; }
        .minhas-agendas-modal-item .btn-cancelar:hover { background:#fdecea; color:#a93226; border-color:#e6a4a4; }
    </style>

    <button type="button" class="btn btn-primary" id="btnMinhasAgendas">
        Minhas agendas <span class="badge-qtd"><?php echo $qtdTotal; ?></span>
    </button>

    <!-- Modal - padrão do projeto (.modalCabecalho / .modalBtnFechar / .modalRodape / .modalBtnCancelar) -->
    <div class="modal fade" id="modalMinhasAgendas"
         data-backdrop="static" data-bs-backdrop="static" data-keyboard="false" data-bs-keyboard="false"
         tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header modalCabecalho">
                    <h4 class="modal-title"><i class="fa fa-calendar"></i> Minhas agendas neste evento</h4>
                    <button type="button" class="modalBtnFechar" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Fechar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <?php foreach ($listaAgendas as $objAg) {
                        $dataIni = $objAg->getDataInicio();
                        $dataFmt = '';
                        if (isDateStringValid($dataIni)) {
                            $ts = strtotime($dataIni);
                            $dataFmt = date('d/m/Y', $ts);
                        }
                        $tipo = trim($objAg->getAbrevTipo());
                        $hIni = $objAg->getHorarioIni();
                        $hFim = $objAg->getHorarioFim();
                        $horario = $hIni . ($hFim ? ' às ' . $hFim : '');
                        $quando = $dataFmt;
                        if ($tipo) { $quando .= ($quando ? ' - ' : '') . $tipo; }
                        if ($horario) { $quando .= ($quando ? ' - ' : '') . $horario; }
                        if (!$quando) { $quando = 'Sem dia escolhido'; }
                        $tituloPerfil  = $objAg->getTituloPerfil();
                        $codInscritoOutra = (int) $objAg->getCodInscrito();
                        $codEventoAtual   = (int) $objAg->getCodEvento();
                        $qtdPag = (int) $objAg->getQtdPagamentosEfetuados();
                        $isPago = $qtdPag > 0;
                        $isAtual = ($codInscritoOutra === $codInscritoAtual);
                        ?>
                        <div class="minhas-agendas-modal-item<?php echo $isAtual ? ' atual' : ''; ?>">
                            <div class="agdesc">
                                <div class="quando">
                                    <?php echo htmlspecialchars($quando); ?>
                                    <?php if ($isAtual) { ?><span class="badge-atual">Selecionada</span><?php } ?>
                                </div>
                                <?php if ($tituloPerfil) { ?>
                                    <div class="grupo">&#128101; <?php echo htmlspecialchars($tituloPerfil); ?></div>
                                <?php } ?>
                                <?php if ($isPago) { ?>
                                    <div class="pago">&#10003; Pago</div>
                                <?php } else { ?>
                                    <div class="pendente">Aguardando pagamento</div>
                                <?php } ?>
                            </div>
                            <div class="acoes" data-cod-inscrito="<?php echo $codInscritoOutra; ?>">
                                <?php if (!$isAtual) { ?>
                                <button type="button" class="btn btn-xs btn-primary"
                                    onclick="acessarMinhaAgenda(<?php echo $codEventoAtual; ?>, <?php echo $codInscritoOutra; ?>, '<?php echo addslashes($objAg->getCpf()); ?>');">
                                    Acessar
                                </button>
                                <?php } ?>
                                <?php if ($isPago) { ?>
                                    <?php $idPub = htmlspecialchars($objAg->getIdPublico()); ?>
                                    <!-- <a href="https://api.whatsapp.com/send?phone=5543996967790&text=Cancelar%20essa%20Inscri%C3%A7%C3%A3o:(<?php echo $idPub; ?>)"
                                       target="_blank" class="btn-cancelar"
                                       title="Solicitar cancelamento via WhatsApp">
                                        <i class="fa fa-whatsapp"></i> Solicitar cancelamento
                                    </a> -->
                                <?php } else { ?>
                                    <button type="button" class="btn-cancelar btn-cancelar-agenda"
                                            data-cod-inscrito="<?php echo $codInscritoOutra; ?>" title="Excluir esta agenda">
                                        <i class="fa fa-trash"></i> Excluir
                                    </button>
                                <?php } ?>
                            </div>
                        </div>
                    <?php } ?>
                </div>
                <div class="modal-footer modalRodape" style="display:flex;justify-content:space-between;align-items:center;">
                    <a href="index.php?content=view/inscricao/agenda_passo1_dia.php&novo=1" class="btn btn-xs btn-primary">
                        <i class="fa fa-plus"></i> Novo agendamento
                    </a>
                    <button type="button" class="btn btn-xs btn-default" data-dismiss="modal" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Helpers de modal compatíveis BS3 + BS5 + fallback (padrão do projeto, ver feedback_modal_bootstrap_compat)
        if (typeof window.mostrarModalCompat !== 'function') {
            window.mostrarModalCompat = function(id){
                var el = document.getElementById(id);
                if (!el) return;
                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    bootstrap.Modal.getOrCreateInstance(el).show(); return;
                }
                if (typeof $ !== 'undefined' && $.fn && typeof $.fn.modal === 'function') {
                    $('#' + id).modal('show'); return;
                }
                el.style.display = 'block';
                el.classList.add('show','in');
                el.removeAttribute('aria-hidden');
                el.setAttribute('aria-modal', 'true');
                document.body.classList.add('modal-open');
                if (!document.querySelector('.modal-backdrop[data-for="' + id + '"]')) {
                    var bd = document.createElement('div');
                    bd.className = 'modal-backdrop fade show in';
                    bd.setAttribute('data-for', id);
                    document.body.appendChild(bd);
                }
            };
        }
        if (typeof window.ocultarModalCompat !== 'function') {
            window.ocultarModalCompat = function(id){
                var el = document.getElementById(id);
                if (!el) return;
                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    var inst = bootstrap.Modal.getInstance(el);
                    if (inst) { inst.hide(); return; }
                }
                if (typeof $ !== 'undefined' && $.fn && typeof $.fn.modal === 'function') {
                    $('#' + id).modal('hide'); return;
                }
                el.style.display = 'none';
                el.classList.remove('show','in');
                document.body.classList.remove('modal-open');
                var bd = document.querySelector('.modal-backdrop[data-for="' + id + '"]');
                if (bd) bd.remove();
            };
        }
        $(document).ready(function(){
            $('#btnMinhasAgendas').on('click', function(){
                mostrarModalCompat('modalMinhasAgendas');
            });
            // Fechar via [data-dismiss] / [data-bs-dismiss]
            $(document).on('click', '#modalMinhasAgendas [data-dismiss="modal"], #modalMinhasAgendas [data-bs-dismiss="modal"]', function(){
                ocultarModalCompat('modalMinhasAgendas');
            });
            // Excluir agenda — confirmação inline (substitui o conteúdo do botão)
            $(document).on('click', '#modalMinhasAgendas .btn-cancelar-agenda', function(){
                var $btn = $(this);
                if ($btn.data('confirmando')) { return; }
                $btn.data('confirmando', true);
                $btn.data('htmlOriginal', $btn.html());
                $btn.html('Excluir esta agenda? <a href="javascript:void(0)" class="btn-cancelar-sim" style="color:#c0392b;font-weight:bold;text-decoration:underline;margin-left:6px;">Sim</a> <a href="javascript:void(0)" class="btn-cancelar-nao" style="color:#666;text-decoration:underline;margin-left:4px;">não</a>');
            });
            $(document).on('click', '#modalMinhasAgendas .btn-cancelar-nao', function(e){
                e.stopPropagation();
                var $btn = $(this).closest('.btn-cancelar-agenda');
                $btn.html($btn.data('htmlOriginal'));
                $btn.removeData('confirmando');
            });
            $(document).on('click', '#modalMinhasAgendas .btn-cancelar-sim', function(e){
                e.stopPropagation();
                var $btn = $(this).closest('.btn-cancelar-agenda');
                var codInsc = $btn.data('cod-inscrito');
                $btn.text('Cancelando...');
                $.ajax({
                    type: 'POST',
                    url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
                    data: { 'cod_inscrito': codInsc, 'c': 'GerenciarInscricao', 'm': 'cancelarMinhaAgenda', '_submit_check': '1' },
                    dataType: 'json', cache: false,
                    success: function(data){
                        if (data && data.ok) {
                            window.location.reload();
                        } else {
                            $btn.html('<span style="color:#c0392b;">' + ((data && data.message) || 'Erro') + '</span>');
                        }
                    },
                    error: function(){
                        $btn.html('<span style="color:#c0392b;">Erro ao cancelar</span>');
                    }
                });
            });
        });
        // acessarMinhaAgenda — escolhe o caminho certo conforme o tipo de sessão:
        // - Admin em modo logarComo: redireciona pra URL ?acao=logarComo (mesma rota que trouxe o admin)
        // - Inscrito normal: usa AJAX carregarParticipacaoEvento (já existente em Demais Projetos)
        window.acessarMinhaAgenda = function(codEvento, codInscrito, cpf) {
            if (!codInscrito) { return; }
            <?php $ehLogarComo = ($_SESSION['_logadoComo'] ?? '') === 'inscrito' || ($_SESSION['_administrativo'] ?? '') == '1'; ?>
            var ehLogarComo = <?php echo $ehLogarComo ? 'true' : 'false'; ?>;
            if (ehLogarComo) {
                var url = 'index.php?content=view/inscricao/inscricao_congresso_ok.php'
                        + '&acao=logarComo'
                        + '&cpf=' + encodeURIComponent(cpf)
                        + '&cod_inscrito=' + encodeURIComponent(codInscrito)
                        + '&cod_evento=' + encodeURIComponent(codEvento);
                window.location.href = url;
                return;
            }
            // Inscrito normal — fluxo AJAX padrão
            if (typeof window.carregarParticipacaoEvento === 'function') {
                window.carregarParticipacaoEvento(codEvento, codInscrito);
                return;
            }
            $.ajax({
                type: 'POST',
                url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
                data: { 'cod_evento': codEvento, 'cod_inscrito': codInscrito,
                        'c': 'GerenciarLogin', 'm': 'carregarParticipacao', '_submit_check': '1' },
                dataType: 'json', cache: false,
                beforeSend: function(){ if (typeof startLoading === 'function') { startLoading(); } },
                success: function(data){
                    if (typeof processJsonSemFade === 'function') { processJsonSemFade(data); }
                    if (data && data.statusMessage == 1) { window.open(data.url, '_self'); }
                },
                complete: function(){ if (typeof stopLoading === 'function') { stopLoading(); } }
            });
        };
    </script>
    <?php
} catch (Throwable $exc) {
    // silencioso — bloco opcional, não pode quebrar a área do inscrito
}

<?php
// Tela de consulta de pagamentos excluidos. Consulta e' aberta a quem tem
// SIS_CONTROLE_FINANCEIRO (mesmo gate da consulta PagSeguro); restaurar e'
// restrito ao usuario 668 -- ver GerenciarPagamentoExcluidoAC.
// Fonte: tab_pagamentos_atualizados (snapshot gravado antes do DELETE em tab_pagamentos).

require_once(dirname(__FILE__)."/../../include/config.php");

// Verificar permissao de financeiro
@$cod_msg = false;
if (!FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_CONTROLE_FINANCEIRO, 5, $cod_msg, false)) {
    $URL = WB_URL_VIEW . "?content=view/admin/login.php";
    $_SESSION['MSG'] = "Não há usuário logado, ou usuário logado não tem permissão de acesso!";
    echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $URL . "\"></HTML>";
    exit;
}

$podeRestaurar = GerenciarPagamentoExcluidoAC::autorizado();

// Acao de restauracao via POST (POST-to-self). GerenciarPagamentoExcluidoAC::restaurar
// tem o proprio guard() -- um usuario nao autorizado forcando o POST leva redirect.
// Esta view e' incluida por index.php (roteamento via $content) DEPOIS que o
// cabecalho/nav ja comecou a ser escrito -- por isso header('Location') aqui nao
// funciona (headers ja enviados), mesmo padrao do bloco de auth acima: manda por
// META REFRESH + flash na sessao. O filtro ativo vem por hidden field (o form nao
// tem querystring), senao a tela pos-restauracao perde o filtro e lista tudo.
if($podeRestaurar && isset($_POST['acao']) && $_POST['acao']==='restaurar'){
    $resultado = GerenciarPagamentoExcluidoAC::restaurar(
        $_POST['cod_pagamento'] ?? 0,
        $_POST['cod_evento'] ?? 0
    );
    $_SESSION['MSG']      = $resultado['msg'];
    $_SESSION['MSG_TIPO'] = $resultado['ok'] ? 'sucesso' : 'erro';

    $paramsRedirect = array('content' => 'view/admin/GerenciarPagamentoExcluido.php');
    if(!empty($_POST['filtro_cod_evento']))   $paramsRedirect['cod_evento']   = (int)$_POST['filtro_cod_evento'];
    if(!empty($_POST['filtro_cod_inscrito'])) $paramsRedirect['cod_inscrito'] = (int)$_POST['filtro_cod_inscrito'];
    $URL = WB_URL_VIEW . '?' . http_build_query($paramsRedirect);
    echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $URL . "\"></HTML>";
    exit();
}

$msgSucesso = '';
$msgErro    = '';
if(!empty($_SESSION['MSG'])){
    if(($_SESSION['MSG_TIPO'] ?? '') === 'erro'){
        $msgErro = $_SESSION['MSG'];
    }else{
        $msgSucesso = $_SESSION['MSG'];
    }
    unset($_SESSION['MSG'], $_SESSION['MSG_TIPO']);
}

$tituloPagina = 'Pagamentos Excluídos — Consulta';
$submenu = 'area_admin_menu_projetos.php';
require_once(dirname(__FILE__)."/../../view/admin/cabecalho-geral.php");

// Filtros via GET
$filtroEvento   = isset($_GET['cod_evento'])   && (int)$_GET['cod_evento']>0   ? (int)$_GET['cod_evento']   : null;
$filtroInscrito = isset($_GET['cod_inscrito']) && (int)$_GET['cod_inscrito']>0 ? (int)$_GET['cod_inscrito'] : null;

$objPagDAO = new PagamentoDAO();
$listaExcluidos = $objPagDAO->listarExcluidos($filtroEvento, $filtroInscrito);

// Helper local: transforma a transacao em link para a consulta PagSeguro
function FWA_linkTransacaoConsulta($transacao_id) {
    $tid = trim((string)$transacao_id);
    if ($tid === '') return '-';
    $url = 'index.php?content=view/admin/GerenciarConsultaPagSeguro.php&transacao_auto=' . urlencode($tid);
    return "<a href='{$url}' target='_blank' title='Consultar esta transação no PagSeguro' style='word-break:break-all;'>".htmlspecialchars($tid)."</a>";
}
?>

<h3>Pagamentos Excluídos — Consulta</h3>
<p class="text-muted">
    Lista pagamentos cujo registro foi removido da tabela <code>tab_pagamentos</code> mas tem snapshot
    preservado em <code>tab_pagamentos_atualizados</code>.
    <?php echo $podeRestaurar
        ? 'A restauração está disponível apenas para o seu usuário.'
        : 'Somente consulta — a restauração é restrita ao administrador.'; ?>
</p>

<?php if($msgSucesso): ?>
    <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-label="Fechar"><span aria-hidden="true">&times;</span></button>
        <strong>Sucesso:</strong> <?php echo htmlspecialchars($msgSucesso); ?>
    </div>
<?php endif; ?>

<?php if($msgErro): ?>
    <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-label="Fechar"><span aria-hidden="true">&times;</span></button>
        <strong>Erro:</strong> <?php echo htmlspecialchars($msgErro); ?>
    </div>
<?php endif; ?>

<form method="GET" action="index.php" class="form-inline" style="margin-bottom:15px;">
    <input type="hidden" name="content" value="view/admin/GerenciarPagamentoExcluido.php">
    <div class="form-group">
        <label for="filtroCodInscrito">Cód. Inscrito:</label>
        <input type="number" id="filtroCodInscrito" name="cod_inscrito" class="form-control"
               value="<?php echo $filtroInscrito ?: ''; ?>" placeholder="ex.: 44762" min="1">
    </div>
    &nbsp;
    <div class="form-group">
        <label for="filtroCodEvento">Cód. Evento:</label>
        <input type="number" id="filtroCodEvento" name="cod_evento" class="form-control"
               value="<?php echo $filtroEvento ?: ''; ?>" placeholder="ex.: 122" min="1">
    </div>
    &nbsp;
    <button type="submit" class="btn btn-primary">Filtrar</button>
    <a href="index.php?content=view/admin/GerenciarPagamentoExcluido.php" class="btn btn-default">Limpar</a>
</form>

<div class="panel panel-default">
    <div class="panel-heading">
        <strong>Total: <?php echo count($listaExcluidos); ?> pagamento(s) excluído(s)</strong>
    </div>
    <div class="panel-body" style="padding:0;">
        <?php if(empty($listaExcluidos)): ?>
            <div class="text-muted text-center" style="padding:20px;">
                Nenhum pagamento excluído encontrado<?php echo ($filtroEvento || $filtroInscrito) ? ' com os filtros aplicados' : ''; ?>.
            </div>
        <?php else: ?>
            <table class="table table-striped table-hover" style="margin-bottom:0;font-size:12px;">
                <thead>
                    <tr>
                        <th>Cód. Pgto</th>
                        <th>Evento</th>
                        <th>Inscrito</th>
                        <th>Forma / Status</th>
                        <th style="text-align:right;">Valor</th>
                        <th>Transação ID</th>
                        <th>Pedido</th>
                        <th>Datas</th>
                        <?php if($podeRestaurar): ?><th style="text-align:center;">Ação</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                <?php foreach($listaExcluidos as $row): ?>
                    <tr>
                        <td><strong><?php echo (int)$row['cod_pagamento']; ?></strong></td>
                        <td>
                            <small class="text-muted">#<?php echo (int)$row['cod_evento']; ?></small><br>
                            <small><?php echo htmlspecialchars($row['titulo_evento'] ?? ''); ?></small>
                        </td>
                        <td>
                            <small class="text-muted">#<?php echo (int)$row['cod_inscrito']; ?></small><br>
                            <small><?php echo htmlspecialchars($row['nome_inscrito'] ?? ''); ?></small>
                        </td>
                        <td>
                            <small><?php echo htmlspecialchars($row['forma_pagamento'] ?? ''); ?></small><br>
                            <small class="text-muted"><?php echo htmlspecialchars($row['status'] ?? ''); ?></small>
                        </td>
                        <td style="text-align:right;white-space:nowrap;">
                            R$ <?php echo number_format((float)($row['valor'] ?? 0), 2, ',', '.'); ?>
                        </td>
                        <td style="max-width:180px;"><?php echo FWA_linkTransacaoConsulta($row['transacao_id'] ?? ''); ?></td>
                        <td>
                            <?php if(!empty($row['pedido_id'])): ?>
                                <small class="text-muted">#<?php echo (int)$row['pedido_id']; ?></small><br>
                                <small><?php echo htmlspecialchars($row['pedido_titulo'] ?? ''); ?></small>
                            <?php else: ?>
                                <small class="text-muted">—</small>
                            <?php endif; ?>
                        </td>
                        <td style="white-space:nowrap;">
                            <small>
                                <?php if(!empty($row['data_pagamento'])): ?>
                                    Pgto: <?php echo formatDateView($row['data_pagamento']); ?><br>
                                <?php endif; ?>
                                Incl.: <?php echo !empty($row['data_inclusao']) ? formatDateView($row['data_inclusao']) : '—'; ?><br>
                                Alt.: <?php echo !empty($row['data_alteracao']) ? formatDateView($row['data_alteracao']) : '—'; ?>
                            </small>
                        </td>
                        <?php if($podeRestaurar): ?>
                        <td style="text-align:center;white-space:nowrap;">
                            <button type="button" class="btn btn-success btn-xs"
                                    onclick="abrirModalRestaurarPagamento(<?php echo (int)$row['cod_pagamento']; ?>, <?php echo (int)$row['cod_evento']; ?>, '<?php echo number_format((float)($row['valor'] ?? 0), 2, ',', '.'); ?>');">
                                <i class="glyphicon glyphicon-refresh"></i> Restaurar
                            </button>
                        </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php if($podeRestaurar): ?>
<!-- Modal de confirmacao de restauracao -->
<div class="modal fade" id="modalRestaurarPagamento" tabindex="-1" role="dialog" aria-labelledby="modalRestaurarPagamentoLabel" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="formRestaurarPagamento" method="post" action="index.php?content=view/admin/GerenciarPagamentoExcluido.php">
                <div class="modal-header" style="background-color:#5cb85c;color:#fff;">
                    <h4 class="modal-title" id="modalRestaurarPagamentoLabel">
                        <i class="glyphicon glyphicon-refresh"></i> Confirmar restauração de pagamento
                    </h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar" style="color:#fff;opacity:.9;"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <p>Reinserir o pagamento na tabela <code>tab_pagamentos</code> a partir do snapshot mais recente em <code>tab_pagamentos_atualizados</code>.</p>
                    <p>
                        <strong>Cód. pagamento:</strong> <span id="modalRestaurarPagamentoId"></span><br>
                        <strong>Cód. evento:</strong> <span id="modalRestaurarPagamentoEvento"></span><br>
                        <strong>Valor:</strong> R$ <span id="modalRestaurarPagamentoValor"></span>
                    </p>
                    <div class="alert alert-info">
                        A restauração não interfere em outros pagamentos ou pedidos.
                        Verifique no módulo do inscrito se há divergências antes de aceitar a operação.
                    </div>
                    <input type="hidden" name="acao"          value="restaurar">
                    <input type="hidden" name="cod_pagamento" id="modalRestaurarPagamentoCodPagamento" value="">
                    <input type="hidden" name="cod_evento"    id="modalRestaurarPagamentoCodEvento"    value="">
                    <input type="hidden" name="filtro_cod_evento"   value="<?php echo $filtroEvento ?: ''; ?>">
                    <input type="hidden" name="filtro_cod_inscrito" value="<?php echo $filtroInscrito ?: ''; ?>">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success" id="btnConfirmarRestaurarPagamento">
                        <i class="glyphicon glyphicon-refresh"></i> Confirmar restauração
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
function abrirModalRestaurarPagamento(codPagamento, codEvento, valor){
    $('#modalRestaurarPagamentoCodPagamento').val(codPagamento);
    $('#modalRestaurarPagamentoCodEvento').val(codEvento);
    $('#modalRestaurarPagamentoId').text(codPagamento);
    $('#modalRestaurarPagamentoEvento').text(codEvento);
    $('#modalRestaurarPagamentoValor').text(valor || '0,00');
    $('#modalRestaurarPagamento').modal('show');
}

$(document).ready(function(){
    $('#formRestaurarPagamento').on('submit', function(){
        $('#btnConfirmarRestaurarPagamento').prop('disabled', true).html('Restaurando...');
        return true;
    });
});
</script>
<?php endif; ?>

</div><!-- panel-body (aberto em cabecalho-geral.php) -->
</div><!-- panel -->
</div><!-- panel-group -->

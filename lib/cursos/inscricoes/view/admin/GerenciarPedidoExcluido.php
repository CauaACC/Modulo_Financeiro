<?php
// Tela de recuperacao de pedidos excluidos. Acesso restrito ao usuario 668.
// Mostra snapshot mais recente em pedidos_atualizados cujo id ja nao existe em pedidos.

require_once(dirname(__FILE__) . "/../../include/config.php");

// Guard primeiro: nao-668 redireciona antes de qualquer render do shell admin
GerenciarPedidoExcluidoAC::guard();

// Acao de restauracao via POST (POST-to-self). Esta view e' incluida por
// index.php (roteamento via $content) DEPOIS que o cabecalho/nav ja comecou a
// ser escrito -- por isso header('Location') aqui nao funciona (headers ja
// enviados). Manda por META REFRESH + flash na sessao, como o bloco de auth do
// GerenciarPagamentoExcluido.php ja fazia. O filtro ativo vem por hidden field
// (o form nao tem querystring), senao a tela pos-restauracao perde o filtro.
if(isset($_POST['acao']) && $_POST['acao']==='restaurar'){
    $resultado = GerenciarPedidoExcluidoAC::restaurar(
        $_POST['cod_pedido'] ?? 0,
        $_POST['cod_evento'] ?? 0
    );
    $_SESSION['MSG']      = $resultado['msg'];
    $_SESSION['MSG_TIPO'] = $resultado['ok'] ? 'sucesso' : 'erro';

    $paramsRedirect = array('content' => 'view/admin/GerenciarPedidoExcluido.php');
    if(!empty($_POST['filtro_cod_evento']))   $paramsRedirect['cod_evento']   = (int)$_POST['filtro_cod_evento'];
    if(!empty($_POST['filtro_cod_inscrito'])) $paramsRedirect['cod_inscrito'] = (int)$_POST['filtro_cod_inscrito'];
    $URL = WB_URL_VIEW . '?' . http_build_query($paramsRedirect);
    echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $URL . "\"></HTML>";
    exit();
}

require_once(dirname(__FILE__) . "/../../view/admin/inscritos-cabecalho.php");

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

// Filtros via GET
$filtroEvento   = isset($fwsGet_['cod_evento'])   && (int)$fwsGet_['cod_evento']>0   ? (int)$fwsGet_['cod_evento']   : null;
$filtroInscrito = isset($fwsGet_['cod_inscrito']) && (int)$fwsGet_['cod_inscrito']>0 ? (int)$fwsGet_['cod_inscrito'] : null;

$listaExcluidos = GerenciarPedidoExcluidoAC::listar($filtroEvento, $filtroInscrito);
?>

<h3>Pedidos Excluídos — Recuperação</h3>
<p class="text-muted">
    Lista pedidos cujo registro foi removido da tabela <code>pedidos</code> mas tem snapshot preservado em <code>pedidos_atualizados</code>.
    Acesso restrito ao usuário administrador.
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
    <input type="hidden" name="content" value="view/admin/GerenciarPedidoExcluido.php">
    <div class="form-group">
        <label for="filtroCodInscrito">Cód. Inscrito:</label>
        <input type="number" id="filtroCodInscrito" name="cod_inscrito" class="form-control"
               value="<?php echo $filtroInscrito ?: ''; ?>" placeholder="ex.: 44014" min="1">
    </div>
    &nbsp;
    <div class="form-group">
        <label for="filtroCodEvento">Cód. Evento:</label>
        <input type="number" id="filtroCodEvento" name="cod_evento" class="form-control"
               value="<?php echo $filtroEvento ?: ''; ?>" placeholder="ex.: 116" min="1">
    </div>
    &nbsp;
    <button type="submit" class="btn btn-primary">Filtrar</button>
    <a href="index.php?content=view/admin/GerenciarPedidoExcluido.php" class="btn btn-default">Limpar</a>
</form>

<div class="panel panel-default">
    <div class="panel-heading">
        <strong>Total: <?php echo count($listaExcluidos); ?> pedido(s) excluído(s)</strong>
    </div>
    <div class="panel-body" style="padding:0;">
        <?php if(empty($listaExcluidos)): ?>
            <div class="text-muted text-center" style="padding:20px;">
                Nenhum pedido excluído encontrado<?php echo ($filtroEvento || $filtroInscrito) ? ' com os filtros aplicados' : ''; ?>.
            </div>
        <?php else: ?>
            <table class="table table-striped table-hover" style="margin-bottom:0;">
                <thead>
                    <tr>
                        <th>Cód. Pedido</th>
                        <th>Evento</th>
                        <th>Inscrito</th>
                        <th>Descrição</th>
                        <th style="text-align:right;">Valor</th>
                        <th>Tipo Ação</th>
                        <th>Excluído por</th>
                        <th>Data</th>
                        <th>Motivo</th>
                        <th style="text-align:center;">Ação</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach($listaExcluidos as $row): ?>
                    <tr>
                        <td><strong><?php echo (int)$row['id']; ?></strong></td>
                        <td>
                            <small class="text-muted">#<?php echo (int)$row['evento_id']; ?></small><br>
                            <small><?php echo htmlspecialchars($row['titulo_evento'] ?? ''); ?></small>
                        </td>
                        <td>
                            <small class="text-muted">#<?php echo (int)$row['inscrito_id']; ?></small><br>
                            <small><?php echo htmlspecialchars($row['nome_inscrito'] ?? ''); ?></small>
                        </td>
                        <td><small><?php echo htmlspecialchars($row['descricao'] ?? $row['descricao_completa'] ?? ''); ?></small></td>
                        <td style="text-align:right;">R$ <?php echo number_format((float)($row['valor_total'] ?? 0), 2, ',', '.'); ?></td>
                        <td>
                            <?php $tipo = $row['tipo_acao'] ?? null; ?>
                            <?php if($tipo === 'EXCLUSAO'): ?>
                                <span class="label label-danger">EXCLUSÃO</span>
                            <?php elseif($tipo === 'ALTERACAO'): ?>
                                <span class="label label-info">ALTERAÇÃO</span>
                            <?php else: ?>
                                <span class="label label-default" title="Snapshot anterior à auditoria">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if(!empty($row['login_usuario_acao'])): ?>
                                <small><?php echo htmlspecialchars($row['login_usuario_acao']); ?></small>
                                <small class="text-muted">(#<?php echo (int)$row['usuario_acao']; ?>)</small>
                            <?php elseif(!empty($row['usuario_acao'])): ?>
                                <small class="text-muted">#<?php echo (int)$row['usuario_acao']; ?></small>
                            <?php else: ?>
                                <small class="text-muted">—</small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <small><?php echo $row['data_acao'] ? formatDateView($row['data_acao']) : '—'; ?></small>
                        </td>
                        <td><small><?php echo nl2br(htmlspecialchars($row['motivo_acao'] ?? '')); ?></small></td>
                        <td style="text-align:center;white-space:nowrap;">
                            <button type="button" class="btn btn-success btn-xs"
                                    onclick="abrirModalRestaurarPedido(<?php echo (int)$row['id']; ?>, <?php echo (int)$row['evento_id']; ?>, '<?php echo addslashes($row['descricao'] ?? ''); ?>', '<?php echo number_format((float)($row['valor_total'] ?? 0), 2, ',', '.'); ?>');">
                                <i class="glyphicon glyphicon-refresh"></i> Restaurar
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<!-- Modal de confirmacao de restauracao -->
<div class="modal fade" id="modalRestaurarPedido" tabindex="-1" role="dialog" aria-labelledby="modalRestaurarPedidoLabel" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="formRestaurarPedido" method="post" action="index.php?content=view/admin/GerenciarPedidoExcluido.php">
                <div class="modal-header" style="background-color:#5cb85c;color:#fff;">
                    <h4 class="modal-title" id="modalRestaurarPedidoLabel">
                        <i class="glyphicon glyphicon-refresh"></i> Confirmar restauração de pedido
                    </h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar" style="color:#fff;opacity:.9;"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <p>Reinserir o pedido na tabela <code>pedidos</code> a partir do snapshot mais recente em <code>pedidos_atualizados</code>.</p>
                    <p>
                        <strong>Cód. pedido:</strong> <span id="modalRestaurarPedidoId"></span><br>
                        <strong>Cód. evento:</strong> <span id="modalRestaurarPedidoEvento"></span><br>
                        <strong>Descrição:</strong> <span id="modalRestaurarPedidoDescricao"></span><br>
                        <strong>Valor:</strong> R$ <span id="modalRestaurarPedidoValor"></span>
                    </p>
                    <div class="alert alert-info">
                        A restauração não interfere em pagamentos já registrados.
                        Verifique no módulo do inscrito se há divergências antes de aceitar a operação.
                    </div>
                    <input type="hidden" name="acao"        value="restaurar">
                    <input type="hidden" name="cod_pedido"  id="modalRestaurarPedidoCodPedido"  value="">
                    <input type="hidden" name="cod_evento"  id="modalRestaurarPedidoCodEvento"  value="">
                    <input type="hidden" name="filtro_cod_evento"   value="<?php echo $filtroEvento ?: ''; ?>">
                    <input type="hidden" name="filtro_cod_inscrito" value="<?php echo $filtroInscrito ?: ''; ?>">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success" id="btnConfirmarRestaurarPedido">
                        <i class="glyphicon glyphicon-refresh"></i> Confirmar restauração
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
function abrirModalRestaurarPedido(codPedido, codEvento, descricao, valor){
    $('#modalRestaurarPedidoCodPedido').val(codPedido);
    $('#modalRestaurarPedidoCodEvento').val(codEvento);
    $('#modalRestaurarPedidoId').text(codPedido);
    $('#modalRestaurarPedidoEvento').text(codEvento);
    $('#modalRestaurarPedidoDescricao').text(descricao || '(sem descrição)');
    $('#modalRestaurarPedidoValor').text(valor || '0,00');
    $('#modalRestaurarPedido').modal('show');
}

$(document).ready(function(){
    $('#formRestaurarPedido').on('submit', function(){
        $('#btnConfirmarRestaurarPedido').prop('disabled', true).html('Restaurando...');
        return true;
    });
});
</script>

</div><!-- panel-body (aberto em inscritos-cabecalho.php) -->
</div><!-- panel -->
</div><!-- panel-group -->

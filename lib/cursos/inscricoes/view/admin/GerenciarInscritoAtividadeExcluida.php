<?php
// Atividades excluidas do inscrito — consulta e recuperacao.
// Fonte: tab_lig_inscritos_oficinas_excluidos (copia gravada antes do DELETE).
// Acesso: mesmo gate das telas do inscrito (SIS_GERENCIAR_INSCRITO, direito 5).

require_once(dirname(__FILE__) . "/../../include/config.php");

$objAtivExcAC = new GerenciarInscritoAtividadeAC();
// Guard antes do cabecalho: quem nao tem direito nao ve nem o shell admin.
$objAtivExcAC->guard();

require_once(dirname(__FILE__) . "/../../view/admin/inscritos-cabecalho.php");

// Restauracao via POST-to-self
$msgSucesso = '';
$msgErro    = '';
if(isset($_POST['acao']) && $_POST['acao']==='restaurar'){
    $resultado = $objAtivExcAC->restaurar(isset($_POST['id_exclusao']) ? $_POST['id_exclusao'] : 0);
    if($resultado['ok']){
        $msgSucesso = $resultado['msg'];
    }else{
        $msgErro = $resultado['msg'];
    }
}

// Filtros via GET
$filtroEvento   = isset($_GET['cod_evento'])   && (int)$_GET['cod_evento']>0   ? (int)$_GET['cod_evento']   : null;
$filtroInscrito = isset($_GET['cod_inscrito']) && (int)$_GET['cod_inscrito']>0 ? (int)$_GET['cod_inscrito'] : null;

$listaExcluidas = $objAtivExcAC->listar($filtroEvento, $filtroInscrito);

$urlVoltaInscrito = $filtroInscrito
    ? 'index.php?content=view/admin/inscritos-cadastro-ok.php&cod_inscrito=' . (int)$filtroInscrito
    : '';

// Filtrada por um inscrito, as colunas Inscrito e Evento repetem a mesma
// informacao em toda linha e empurram Motivo e Acao para fora da tela. Nesse
// caso elas viram um subtitulo, e a tabela cabe sem rolagem lateral.
$mostrarContexto = !$filtroInscrito;
$nomeInscritoFiltro = '';
$tituloEventoFiltro = '';
if(!$mostrarContexto && !empty($listaExcluidas)){
    $nomeInscritoFiltro = (string)$listaExcluidas[0]['nome_inscrito'];
    $tituloEventoFiltro = (string)$listaExcluidas[0]['titulo_evento'];
}
?>

<style>
    /* Sem larguras minimas o titulo da atividade e o motivo quebram palavra por
       palavra e a linha vira uma coluna de texto. */
    #tblAtivExcluidas th, #tblAtivExcluidas td { vertical-align: top; }
    #tblAtivExcluidas .col-atividade { min-width: 180px; }
    #tblAtivExcluidas .col-motivo    { max-width: 260px; word-break: break-word; }
    #tblAtivExcluidas .col-inscrito,
    #tblAtivExcluidas .col-evento    { min-width: 130px; }
    #tblAtivExcluidas .col-data      { white-space: nowrap; }
    #tblAtivExcluidas .col-relacao   { white-space: nowrap; }
</style>

<h3>Atividades Excluídas — Recuperação</h3>
<p class="text-muted">
    Atividades que foram removidas do cadastro do inscrito. O registro continua guardado e pode
    voltar exatamente como estava — inclusive presença, frequência e carga horária.
    Cada linha é <strong>uma participação</strong>: o mesmo inscrito pode ter mais de uma relação
    na mesma atividade (Participante, Participante 2, Ministrante...).
</p>

<?php if($msgSucesso): ?>
    <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-label="Fechar"><span aria-hidden="true">&times;</span></button>
        <strong>Sucesso:</strong> <?php echo htmlspecialchars($msgSucesso, ENT_QUOTES, 'UTF-8'); ?>
    </div>
<?php endif; ?>

<?php if($msgErro): ?>
    <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-label="Fechar"><span aria-hidden="true">&times;</span></button>
        <strong>Erro:</strong> <?php echo htmlspecialchars($msgErro, ENT_QUOTES, 'UTF-8'); ?>
    </div>
<?php endif; ?>

<form method="GET" action="index.php" class="form-inline" style="margin-bottom:15px;">
    <input type="hidden" name="content" value="view/admin/GerenciarInscritoAtividadeExcluida.php">
    <div class="form-group">
        <label for="filtroCodInscrito">Cód. Inscrito:</label>
        <input type="number" id="filtroCodInscrito" name="cod_inscrito" class="form-control"
               value="<?php echo $filtroInscrito ?: ''; ?>" placeholder="ex.: 12143" min="1">
    </div>
    &nbsp;
    <div class="form-group">
        <label for="filtroCodEvento">Cód. Evento:</label>
        <input type="number" id="filtroCodEvento" name="cod_evento" class="form-control"
               value="<?php echo $filtroEvento ?: ''; ?>" placeholder="ex.: 10" min="1">
    </div>
    &nbsp;
    <button type="submit" class="btn btn-primary">Filtrar</button>
    <a href="index.php?content=view/admin/GerenciarInscritoAtividadeExcluida.php" class="btn btn-default">Limpar</a>
    <?php if($urlVoltaInscrito): ?>
        &nbsp;<a href="<?php echo $urlVoltaInscrito; ?>" class="btn btn-default">
            <i class="glyphicon glyphicon-arrow-left"></i> Voltar ao inscrito
        </a>
    <?php endif; ?>
</form>

<div class="panel panel-default">
    <div class="panel-heading">
        <strong>Total: <?php echo count($listaExcluidas); ?> registro(s)</strong>
        <?php if($nomeInscritoFiltro!=''): ?>
            <span class="text-muted">
                &nbsp;— inscrito <strong>#<?php echo (int)$filtroInscrito; ?></strong>
                <?php echo htmlspecialchars($nomeInscritoFiltro, ENT_QUOTES, 'UTF-8'); ?>
                <?php if($tituloEventoFiltro!=''): ?>
                    · evento <?php echo htmlspecialchars($tituloEventoFiltro, ENT_QUOTES, 'UTF-8'); ?>
                <?php endif; ?>
            </span>
        <?php endif; ?>
        <small class="text-muted">&nbsp;— clique no cabeçalho para ordenar</small>
    </div>
    <div class="panel-body" style="padding:0;">
        <?php if(empty($listaExcluidas)): ?>
            <div class="text-muted text-center" style="padding:20px;">
                Nenhuma atividade excluída encontrada<?php echo ($filtroEvento || $filtroInscrito) ? ' com os filtros aplicados' : ''; ?>.
            </div>
        <?php else: ?>
            <div style="overflow-x:auto;">
            <table class="table table-striped table-hover" id="tblAtivExcluidas" style="margin-bottom:0;table-layout:auto;">
                <thead>
                    <tr style="cursor:pointer;">
                        <th>Cód. atividade</th>
                        <th>Atividade</th>
                        <th>Relação</th>
                        <th>Presença</th>
                        <th>C.H.</th>
                        <?php if($mostrarContexto): ?>
                            <th>Inscrito</th>
                            <th>Evento</th>
                        <?php endif; ?>
                        <th>Excluída por</th>
                        <th>Data da exclusão</th>
                        <th>Motivo</th>
                        <th style="text-align:center;">Ação</th>
                    </tr>
                </thead>
                <tbody class="inputs">
                <?php foreach($listaExcluidas as $row): ?>
                    <?php
                        $jaRestaurada = ((int)$row['ja_restaurada'] === 1);
                        $tituloAtiv   = $row['titulo_oficina'] !== null && $row['titulo_oficina'] !== ''
                                      ? $row['titulo_oficina']
                                      : '(atividade não localizada no cadastro)';
                        $tipoRelacao  = ($row['tipo_relacao']!==null && $row['tipo_relacao']!=='')
                                      ? $row['tipo_relacao'] : '(sem relação)';
                        $dataExc      = $row['data_exclusao'] ? date('d/m/Y H:i', strtotime($row['data_exclusao'])) : '';
                    ?>
                    <tr>
                        <td class="col-codigo"><?php echo (int)$row['cod_oficina']; ?></td>
                        <td class="col-atividade">
                            <?php echo htmlspecialchars($tituloAtiv, ENT_QUOTES, 'UTF-8'); ?>
                            <?php if($row['tipo_oficina']): ?>
                                <br><small class="text-muted"><?php echo htmlspecialchars($row['tipo_oficina'], ENT_QUOTES, 'UTF-8'); ?></small>
                            <?php endif; ?>
                        </td>
                        <td class="col-relacao"><?php echo htmlspecialchars($tipoRelacao, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo ((int)$row['flg_presenca']===1) ? 'Presente' : 'Ausente'; ?></td>
                        <td class="col-num"><?php echo htmlspecialchars((string)$row['carga_horaria'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <?php if($mostrarContexto): ?>
                        <td class="col-inscrito">
                            <small class="text-muted">#<?php echo (int)$row['cod_inscrito']; ?></small>
                            <?php echo htmlspecialchars((string)$row['nome_inscrito'], ENT_QUOTES, 'UTF-8'); ?>
                        </td>
                        <td class="col-evento">
                            <small class="text-muted">#<?php echo (int)$row['cod_evento']; ?></small>
                            <small><?php echo htmlspecialchars((string)$row['titulo_evento'], ENT_QUOTES, 'UTF-8'); ?></small>
                        </td>
                        <?php endif; ?>
                        <td>
                            <?php if(!empty($row['login_usuario_exclusao'])): ?>
                                <small><?php echo htmlspecialchars($row['login_usuario_exclusao'], ENT_QUOTES, 'UTF-8'); ?></small>
                                <small class="text-muted">(#<?php echo (int)$row['usuario_exclusao']; ?>)</small>
                            <?php elseif(!empty($row['usuario_exclusao'])): ?>
                                <small class="text-muted">#<?php echo (int)$row['usuario_exclusao']; ?></small>
                            <?php else: ?>
                                <small class="text-muted">—</small>
                            <?php endif; ?>
                        </td>
                        <td class="col-data" data-ord="<?php echo htmlspecialchars((string)$row['data_exclusao'], ENT_QUOTES, 'UTF-8'); ?>">
                            <?php echo $dataExc ?: '—'; ?>
                        </td>
                        <td class="col-motivo"><small><?php echo nl2br(htmlspecialchars((string)$row['motivo_exclusao'], ENT_QUOTES, 'UTF-8')); ?></small></td>
                        <td style="text-align:center;white-space:nowrap;">
                            <?php if($jaRestaurada): ?>
                                <span class="label label-default" title="A atividade já consta no cadastro do inscrito">No cadastro</span>
                            <?php else: ?>
                                <button type="button" class="btn btn-success btn-xs btn-restaurar-ativ"
                                        data-id-exclusao="<?php echo (int)$row['id_exclusao']; ?>"
                                        data-atividade="<?php echo htmlspecialchars($tituloAtiv, ENT_QUOTES, 'UTF-8'); ?>"
                                        data-cod-oficina="<?php echo (int)$row['cod_oficina']; ?>"
                                        data-relacao="<?php echo htmlspecialchars($tipoRelacao, ENT_QUOTES, 'UTF-8'); ?>"
                                        data-inscrito="<?php echo htmlspecialchars((string)$row['nome_inscrito'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="glyphicon glyphicon-refresh"></i> Restaurar
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php overlayProcessando(); ?>

<!-- Modal de confirmacao da restauracao -->
<div class="modal fade" id="modalRestaurarAtividade" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="formRestaurarAtividade" method="post" data-processando="Restaurando a atividade..."
                  action="index.php?content=view/admin/GerenciarInscritoAtividadeExcluida.php<?php
                echo $filtroInscrito ? '&cod_inscrito='.(int)$filtroInscrito : '';
                echo $filtroEvento   ? '&cod_evento='.(int)$filtroEvento     : ''; ?>">
                <div class="modal-header" style="background-color:#5cb85c;color:#fff;">
                    <h4 class="modal-title">
                        <i class="glyphicon glyphicon-refresh"></i> Confirmar recuperação da atividade
                    </h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar" style="color:#fff;opacity:.9;"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <p>A participação volta para o cadastro do inscrito com os mesmos dados que tinha quando foi excluída.</p>
                    <p>
                        <strong>Atividade:</strong> <span id="modalRestAtivTitulo"></span><br>
                        <strong>Cód. atividade:</strong> <span id="modalRestAtivCodigo"></span><br>
                        <strong>Relação:</strong> <span id="modalRestAtivRelacao"></span><br>
                        <strong>Inscrito:</strong> <span id="modalRestAtivInscrito"></span>
                    </p>
                    <div class="alert alert-info">
                        Só esta participação é restaurada. As outras relações do inscrito nesta atividade não são afetadas.
                    </div>
                    <input type="hidden" name="acao" value="restaurar">
                    <input type="hidden" name="id_exclusao" id="modalRestAtivId" value="">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success" id="btnConfirmarRestaurarAtividade">
                        <i class="glyphicon glyphicon-refresh"></i> Confirmar recuperação
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function(){
    $(document).on('click', '.btn-restaurar-ativ', function(){
        $('#modalRestAtivId').val($(this).data('id-exclusao'));
        $('#modalRestAtivTitulo').text($(this).data('atividade'));
        $('#modalRestAtivCodigo').text($(this).data('cod-oficina'));
        $('#modalRestAtivRelacao').text($(this).data('relacao'));
        $('#modalRestAtivInscrito').text($(this).data('inscrito') || '—');
        $('#modalRestaurarAtividade').modal('show');
    });

    $('#formRestaurarAtividade').on('submit', function(){
        $('#btnConfirmarRestaurarAtividade').prop('disabled', true).html('Restaurando...');
        return true;
    });

    // Ordenacao pelo cabecalho: handler delegado, mexe so no tbody.
    $('#tblAtivExcluidas thead').on('click', 'th', function(){
        ordenarTabelaAtivExc($('#tblAtivExcluidas'), $(this).index());
    });
});

// Numero em pt-BR e data dd/mm/aaaa nao ordenam como texto.
function valorOrdenavelAtivExc(texto){
    var t = $.trim(texto);
    var data = t.match(/^(\d{2})\/(\d{2})\/(\d{4})/);
    if (data) { return data[3] + data[2] + data[1]; }
    if (/^-?[\d.]+,\d+$/.test(t)) { return parseFloat(t.replace(/\./g,'').replace(',','.')); }
    if (/^-?\d+$/.test(t)) { return parseFloat(t); }
    return t.toLowerCase();
}

function ordenarTabelaAtivExc($tabela, coluna){
    var $tbody = $tabela.find('tbody.inputs');
    var asc = $tabela.data('ordem-col') === coluna ? !$tabela.data('ordem-asc') : true;
    $tabela.data('ordem-col', coluna).data('ordem-asc', asc);

    var linhas = $tbody.find('tr').get();
    if (linhas.length < 2) { return; }
    linhas.sort(function(a, b){
        var $a = $(a).children('td').eq(coluna);
        var $b = $(b).children('td').eq(coluna);
        var va = $a.attr('data-ord') !== undefined ? $a.attr('data-ord') : valorOrdenavelAtivExc($a.text());
        var vb = $b.attr('data-ord') !== undefined ? $b.attr('data-ord') : valorOrdenavelAtivExc($b.text());
        if (va < vb) { return asc ? -1 : 1; }
        if (va > vb) { return asc ? 1 : -1; }
        return 0;
    });
    $.each(linhas, function(i, linha){ $tbody.append(linha); });
}
</script>

<div style="margin-bottom:28px;"></div>

</div><!-- panel-body (aberto em inscritos-cabecalho.php) -->
</div><!-- panel -->
</div><!-- panel-group -->

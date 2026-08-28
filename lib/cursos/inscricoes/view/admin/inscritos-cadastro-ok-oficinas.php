<?php
/*
 * Atividades do inscrito.
 *
 * Antes: os mesmos 4 icones .gif (incluir / alterar / excluir / listar) se
 * repetiam em CADA atividade, e os dados vinham num <blockquote> de 7 linhas
 * sem destaque nenhum — com 8 atividades era impossivel bater o olho e ver
 * quem estava presente.
 *
 * Agora: um cartao por atividade, presenca em badge, borda esquerda verde
 * quando presente. "Incluir" sai do laco e vira acao do bloco, uma vez so.
 * Nenhum campo foi removido.
 */
if($objEvConf->getAtividade()==1){
        $var_sql ="
        SELECT too.cod_oficina, too.titulo, too.ordem, too.tipo, tl.tipo_relacao, tl.carga_horaria, tl.carga_horaria_pratica
        , tl.carga_horaria_teorica, tl.cod_inscrito, tl.cod_evento, tl.cod_oficina, tl.tipo_relacao
        , tl.flg_presenca, too.liberar_certificado, too.data_inicio, tl.frequencia
         FROM tab_lig_inscritos_oficinas tl, tab_opcoes_oficinas too
         WHERE
         (tl.cod_evento   = ".$cod_evento.")AND
         (tl.cod_inscrito = ".$cod_inscrito.")AND
         (tl.cod_oficina  = too.cod_oficina)
         ORDER BY too.titulo";

         echo "<fieldset><legend> Atividades</legend>";

         // Atividades ja excluidas deste inscrito: so aparece quando ha o que recuperar.
         $qtdAtivExcluida = (new InscritoOficinaDAO())->contarExcluidos($cod_evento, $cod_inscrito);
         if ($qtdAtivExcluida > 0) {
             $urlAtivExcluida = 'index.php?content=view/admin/GerenciarInscritoAtividadeExcluida.php'
                              . '&cod_inscrito=' . (int)$cod_inscrito . '&cod_evento=' . (int)$cod_evento;
             echo "<div style='text-align:right;margin-bottom:8px;'>"
                . "<a href='".$urlAtivExcluida."' class='btn btn-default btn-xs' title='Ver as atividades excluídas deste inscrito e recuperar se necessário'>"
                . "<i class='glyphicon glyphicon-trash'></i> Atividades excluídas (".(int)$qtdAtivExcluida.")"
                . "</a></div>";
         }

         // Acao do bloco (nao do item): incluir aparece uma vez.
         echo "<div class='card-barra-topo'>";
         echo   "<a class='btn btn-sm btn-primary' href='index.php?content=view/admin/inscritos-oficina-cadastro-incluir.php"
              . "&cod_evento=".$cod_evento."&cod_inscrito=".$cod_inscrito."'>"
              . "<i class='glyphicon glyphicon-plus'></i> Incluir atividade</a>";
         echo "</div>";

         If ($RS_O=FWA_Executa_SQL($debug,$Conexao_db,$var_sql)) {
           If (!$RS_O->EOF) {
             $totAtividades = 0;
             $totPresencas  = 0;
             $totCargaHoraria = 0;
             While (!$RS_O->EOF) {
                #Adicionando nas variaveis, os valores dos campos retirados dos registros
                $cod_oficina = $RS_O->fields['cod_oficina'];
                $titulo      = $RS_O->fields['titulo'];
                $ordem       = $RS_O->fields['ordem'];
                $tipo        = $RS_O->fields['tipo'];
                $tipo_relacao= $RS_O->fields['tipo_relacao'];
                $flg_presenca= $RS_O->fields['flg_presenca'];
                $carga_horaria= $RS_O->fields['carga_horaria'];
                $carga_horaria_pratica= $RS_O->fields['carga_horaria_pratica'];
                $carga_horaria_teorica= $RS_O->fields['carga_horaria_teorica'];
                $liberar_certificado = $RS_O->fields['liberar_certificado'];
                $data_inicio = $RS_O->fields['data_inicio'];
                $frequencia  = $RS_O->fields['frequencia'];
                // O tipo_relacao faz parte da chave (cod_evento+cod_inscrito+cod_oficina+tipo_relacao):
                // o mesmo inscrito aparece na mesma atividade como 'Participante',
                // 'Participante 2', 'Ministrante'... Nos links vai o valor CRU do banco --
                // mandar o texto de exibicao faria a alteracao/exclusao nao achar a linha.
                $tipo_relacao_real = $tipo_relacao;
                $tipo_relacao      = $tipo_relacao!=''? $tipo_relacao : 'participantes';
                $presente = ($flg_presenca == 1);
                $desc_flg_presenca = $presente? 'Presente' : 'Ausente';
                $desc_liberar_certificado = $liberar_certificado==1? 'Sim' : 'Não';

                if($tipo=='Agenda'){
                    $titulo = formatDateView($data_inicio) . ' - '.$titulo;
                }

                $totAtividades++;
                if ($presente) { $totPresencas++; }
                if (is_numeric($carga_horaria)) { $totCargaHoraria += (float) $carga_horaria; }

                echo "<div class='card-item" . ($presente ? " card-ok" : "") . "'>";

                // --- cabecalho: o que e a atividade e se a pessoa esteve la ---
                echo "<div class='card-cab'>";
                echo   "<span class='card-titulo'>" . htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') . "</span>";
                echo   "<span class='card-badge " . ($presente ? "card-badge-ok" : "card-badge-neutro") . "'>"
                     . mb_strtoupper($desc_flg_presenca, 'UTF-8') . "</span>";
                if ($desc_liberar_certificado == 'Sim') {
                    echo "<span class='card-badge card-badge-info'>EMITE CERTIFICADO</span>";
                }
                echo "</div>";

                // --- metadados: mesmos campos da lista antiga, com rotulo ---
                echo montarMeta(array(
                    'Cód. atividade' => $cod_oficina,
                    'Ordem'          => $ordem,
                    'Tipo'           => $tipo,
                    'Função'         => ucfirst($tipo_relacao),
                ));
                echo montarMeta(array(
                    'Frequência'          => $frequencia,
                    'Carga horária'       => $carga_horaria,
                    'C.H. prática'        => $carga_horaria_pratica,
                    'C.H. teórica'        => $carga_horaria_teorica,
                    'Possui certificado'  => $desc_liberar_certificado,
                ));

                // --- acoes: todas juntas, na ordem do fluxo de trabalho ---
                echo "<div class='card-acoes'>";
                echo   "<a class='btn btn-sm btn-primary' href='index.php?content=view/admin/inscritos-oficina-cadastro-alterar.php"
                     . "&cod_inscrito=".$cod_inscrito."&cod_oficina=".$cod_oficina."&tipo_relacao=".urlencode($tipo_relacao_real)."'"
                     . " title='Alterar a participacao deste inscrito na atividade'>Alterar participação</a>";
                echo   "<a class='btn btn-sm btn-primary' href='index.php?content=view/admin/oficina-cadastro-alterar.php"
                     . "&COD_OFICINA=".$cod_oficina."' title='Editar o cadastro da propria atividade'>Editar atividade</a>";
                echo   "<a class='btn btn-sm btn-primary' href='index.php?content=view/admin/consulta-dados-congresso-listagem-inscritos.php"
                     . "&cod_oficina=".$cod_oficina."' title='Listar todos os inscritos desta atividade'>Listar inscritos</a>";
                // Exclusao passa por modal: pergunta, pede motivo e remove SO esta
                // participacao (o link GET antigo levava todas as relacoes junto).
                echo   "<a class='btn btn-sm btn-link card-excluir btn-excluir-atividade' href='#'"
                     . " data-cod-inscrito='".(int)$cod_inscrito."'"
                     . " data-cod-oficina='".(int)$cod_oficina."'"
                     . " data-tipo-relacao='".htmlspecialchars($tipo_relacao_real, ENT_QUOTES, 'UTF-8')."'"
                     . " data-titulo='".htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8')."'"
                     . " data-relacao-desc='".htmlspecialchars(ucfirst($tipo_relacao), ENT_QUOTES, 'UTF-8')."'"
                     . " title='Remover o inscrito desta atividade'>Excluir</a>";
                echo "</div>";

                echo "</div>";  // .card-item
                #Movendo para o proximo registro
                $RS_O->MoveNext();
             }

             echo "<div class='card-total'>";
             echo   "<span>Atividades <b>".$totAtividades."</b></span>";
             echo   "<span>Presenças <b>".$totPresencas."</b></span>";
             if ($totCargaHoraria > 0) {
                 echo "<span>Carga horária total <b>".$totCargaHoraria."</b></span>";
             }
             echo "</div>";
           }Else{
             echo montarVazio('Nenhuma atividade vinculada a este inscrito.');
           }
           echo "</fieldset>";
         }

         // Modal de exclusao: um por pagina, mesmo que a busca traga mais de um inscrito.
         if (!isset($GLOBALS['__modalExcluirAtividadeRenderizado'])) {
             $GLOBALS['__modalExcluirAtividadeRenderizado'] = true;
             overlayProcessando();
             ?>
<div class="modal fade" id="modalExcluirAtividade" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="formExcluirAtividade" method="post" data-processando="Excluindo a atividade..."
                  action="<?php echo WB_URL_COMMAND; ?>&c=GerenciarInscritoAtividade&m=excluir">
                <div class="modal-header" style="background-color:#d9534f;color:#fff;">
                    <h4 class="modal-title">
                        <i class="glyphicon glyphicon-warning-sign"></i> Confirmar exclusão da atividade
                    </h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar" style="color:#fff;opacity:.9;"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <strong>Atenção:</strong> remove <u>somente esta participação</u> do inscrito na atividade.
                        Outras relações do mesmo inscrito nesta atividade continuam como estão.
                        O registro fica preservado e pode ser recuperado em <em>Atividades excluídas</em>.
                    </div>
                    <p>
                        <strong>Atividade:</strong> <span id="modalExcluirAtividadeTitulo"></span><br>
                        <strong>Cód. atividade:</strong> <span id="modalExcluirAtividadeCodigo"></span><br>
                        <strong>Relação:</strong> <span id="modalExcluirAtividadeRelacao"></span>
                    </p>
                    <div class="form-group">
                        <label for="modalExcluirAtividadeMotivo"><strong>Motivo da exclusão</strong> <span style="color:#d9534f;">*</span></label>
                        <textarea id="modalExcluirAtividadeMotivo" name="motivo_exclusao" class="form-control" rows="3" maxlength="500" placeholder="Descreva o motivo (mín. 5 caracteres)" required></textarea>
                        <small class="text-muted">Fica registrado junto com quem excluiu e quando.</small>
                    </div>
                    <div id="modalExcluirAtividadeErro" class="alert alert-danger" style="display:none;"></div>
                    <input type="hidden" id="modalExcluirAtividadeCodInscrito" name="cod_inscrito"  value="">
                    <input type="hidden" id="modalExcluirAtividadeCodOficina"  name="cod_oficina"   value="">
                    <input type="hidden" id="modalExcluirAtividadeTipoRelacao" name="tipo_relacao"  value="">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger" id="btnConfirmarExcluirAtividade">
                        <i class="glyphicon glyphicon-trash"></i> Confirmar exclusão
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function() {
    $(document).on('click', '.btn-excluir-atividade', function(e) {
        e.preventDefault();
        var $b = $(this);
        $('#modalExcluirAtividadeCodInscrito').val($b.data('cod-inscrito'));
        $('#modalExcluirAtividadeCodOficina').val($b.data('cod-oficina'));
        $('#modalExcluirAtividadeTipoRelacao').val($b.data('tipo-relacao'));
        $('#modalExcluirAtividadeTitulo').text($b.data('titulo'));
        $('#modalExcluirAtividadeCodigo').text($b.data('cod-oficina'));
        $('#modalExcluirAtividadeRelacao').text($b.data('relacao-desc'));
        $('#modalExcluirAtividadeMotivo').val('');
        $('#modalExcluirAtividadeErro').hide().text('');
        $('#btnConfirmarExcluirAtividade').prop('disabled', false).html("<i class='glyphicon glyphicon-trash'></i> Confirmar exclusão");
        $('#modalExcluirAtividade').modal('show');
    });

    $('#formExcluirAtividade').on('submit', function(e) {
        var motivo = $.trim($('#modalExcluirAtividadeMotivo').val());
        if (motivo.length < 5) {
            e.preventDefault();
            $('#modalExcluirAtividadeErro').text('Informe um motivo com pelo menos 5 caracteres.').show();
            $('#modalExcluirAtividadeMotivo').focus();
            return false;
        }
        $('#btnConfirmarExcluirAtividade').prop('disabled', true).html('Excluindo...');
        return true;
    });
});
</script>
             <?php
         }
}

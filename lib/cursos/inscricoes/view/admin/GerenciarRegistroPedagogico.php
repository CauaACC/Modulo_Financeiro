<?php
// Se já foi autenticado como agente pedagógico (vindo da área do inscrito), pula a checagem admin
if (!isset($exibirRegistroPedagogico) || !$exibirRegistroPedagogico) {
    require_once(dirname(__FILE__)."/../../view/admin/pedagogico-seguranca.php");
}
$objSRV = new GerenciarRegistroPedagogicoAC();

// codAgente preenchido automaticamente
$codAgente = isset($objInscrito) ? $objInscrito->getCodInscrito() : (isset($fws_['codAgente']) ? $fws_['codAgente'] : '');

// Verificar se usuario logado é PES (agente pedagógico)
$isPES = false;
if (isset($objInscrito) && $codAgente) {
    $isPES = VinculoAgenteAluno::isAgentePedagogico(
        $codAgente,
        $objInscrito->getStatusProcesso(),
        $objInscrito->getCodPerfil()
    );
}

// Gera iniciais a partir do nome completo (ex: "João Silva" -> "J.S.")
function obterIniciaisNome($nome) {
    if (!$nome) return '';
    $partes = preg_split('/\s+/', trim($nome));
    $iniciais = '';
    foreach ($partes as $p) {
        if (mb_strlen($p) > 2) { // ignora preposições (de, da, do, etc.)
            $iniciais .= mb_strtoupper(mb_substr($p, 0, 1)) . '.';
        }
    }
    return $iniciais ? $iniciais : mb_strtoupper(mb_substr($nome, 0, 1)) . '.';
}

// Carregar alunos vinculados ao agente logado via domain
$alunosVinculados = array();
if ($codAgente) {
    try {
        $objVinculo = new VinculoAgenteAluno();
        $objVinculo->setCodAgente($codAgente);
        $listaVinculos = $objVinculo->getListaVinculosByAgente();
        foreach ($listaVinculos as $v) {
            // PES vê iniciais, Admin vê nome completo
            if ($isPES && $v->getNomeCompleto()) {
                $nomeExibicao = obterIniciaisNome($v->getNomeCompleto());
            } else {
                $nomeExibicao = $v->getNomeCompleto() ? $v->getNomeCompleto() : $v->getCodigoExibicao();
            }
            $alunosVinculados[] = array(
                'cod'   => $v->getCodAluno(),
                'nome'  => $nomeExibicao,
                'codigo'=> $v->getCodigoExibicao(),
                'turma' => ($v->getSerieTurma() ? $v->getSerieTurma() : $v->getNivel()) . ' - ' . $v->getTurno(),
                'idade' => $v->getNomeColegio() ? $v->getNomeColegio() : ''
            );
        }
    } catch (Exception $e) {
        // Sem alunos vinculados
    }
}
?>

<style>
/* ===== Registro Pedagógico - Layout Mobile-First ===== */
.rp-container {
    max-width: 600px;
    margin: 0 auto;
    padding: 0 8px;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
}

/* Header bar */
.rp-header {
    display: flex;
    align-items: center;
    padding: 12px 0;
    margin-bottom: 8px;
    border-bottom: 2px solid #2196F3;
}
.rp-header .rp-back-btn {
    background: none;
    border: none;
    font-size: 22px;
    color: #2196F3;
    cursor: pointer;
    padding: 4px 12px 4px 0;
    line-height: 1;
}
.rp-header .rp-back-btn:hover {
    color: #1565C0;
}
.rp-header .rp-title {
    font-size: 17px;
    font-weight: 600;
    color: #333;
    margin: 0;
    flex: 1;
}
.rp-header .rp-subtitle {
    font-size: 13px;
    color: #777;
    margin: 0;
}

/* Search input */
.rp-search {
    position: relative;
    margin-bottom: 12px;
}
.rp-search input {
    width: 100%;
    padding: 10px 12px 10px 36px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 15px;
    outline: none;
    box-sizing: border-box;
    transition: border-color 0.2s;
}
.rp-search input:focus {
    border-color: #2196F3;
}
.rp-search .rp-search-icon {
    position: absolute;
    left: 11px;
    top: 50%;
    transform: translateY(-50%);
    color: #999;
    font-size: 15px;
}

/* Aluno card */
.rp-aluno-card {
    background: #fff;
    border: 1px solid #e8e8e8;
    border-radius: 10px;
    padding: 14px 16px;
    margin-bottom: 10px;
    cursor: pointer;
    transition: box-shadow 0.2s, border-color 0.2s;
    display: flex;
    align-items: center;
}
.rp-aluno-card:hover {
    border-color: #2196F3;
    box-shadow: 0 2px 8px rgba(33,150,243,0.15);
}
.rp-aluno-card .rp-avatar {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: #2196F3;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    font-weight: 600;
    flex-shrink: 0;
    margin-right: 14px;
}
.rp-aluno-card .rp-aluno-info {
    flex: 1;
}
.rp-aluno-card .rp-aluno-nome {
    font-size: 15px;
    font-weight: 600;
    color: #333;
    margin: 0 0 3px 0;
}
.rp-aluno-card .rp-aluno-detalhe {
    font-size: 13px;
    color: #888;
    margin: 0;
}
.rp-aluno-card .rp-chevron {
    color: #ccc;
    font-size: 18px;
    margin-left: 8px;
}

/* Botão flutuante / destaque */
.rp-btn-novo {
    display: block;
    width: 100%;
    padding: 12px;
    background: #2196F3;
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    margin-bottom: 16px;
    transition: background 0.2s;
}
.rp-btn-novo:hover {
    background: #1976D2;
}

/* Registro card (listagem em linha) */
.rp-registro-card {
    background: #fff;
    border: 1px solid #e8e8e8;
    border-radius: 10px;
    padding: 10px 16px;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 12px;
}
.rp-registro-card .rp-reg-data {
    font-size: 14px;
    font-weight: 600;
    color: #2196F3;
    flex: 1;
    margin: 0;
}
.rp-registro-card .rp-reg-actions {
    display: flex;
    gap: 6px;
    flex-shrink: 0;
}
.rp-registro-card .rp-reg-actions button {
    padding: 6px 10px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    border: 1px solid #ddd;
    transition: background 0.2s;
    background: #fff;
}
.rp-registro-card .rp-reg-actions .rp-btn-visualizar {
    color: #4CAF50;
    border-color: #4CAF50;
}
.rp-registro-card .rp-reg-actions .rp-btn-visualizar:hover {
    background: #E8F5E9;
}
.rp-registro-card .rp-reg-actions .rp-btn-editar {
    color: #2196F3;
    border-color: #2196F3;
}
.rp-registro-card .rp-reg-actions .rp-btn-editar:hover {
    background: #E3F2FD;
}
.rp-registro-card .rp-reg-actions .rp-btn-excluir {
    color: #e53935;
    border-color: #e53935;
}
.rp-registro-card .rp-reg-actions .rp-btn-excluir:hover {
    background: #FFEBEE;
}

/* Area de presenca do dia */
.rp-presenca-hoje {
    background: #fff;
    border: 2px solid #2196F3;
    border-radius: 10px;
    padding: 14px 16px;
    margin-bottom: 16px;
}
.rp-presenca-hoje .rp-presenca-titulo {
    font-size: 14px;
    font-weight: 600;
    color: #2196F3;
    margin: 0 0 10px 0;
}
.rp-presenca-hoje .rp-presenca-btns {
    display: flex;
    gap: 8px;
}
.rp-presenca-hoje .rp-presenca-btns button {
    flex: 1;
    padding: 10px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    border: 2px solid;
    transition: background 0.2s, color 0.2s;
}
.rp-btn-presente {
    color: #4CAF50;
    border-color: #4CAF50 !important;
    background: #fff;
}
.rp-btn-presente:hover {
    background: #E8F5E9 !important;
}
.rp-btn-presente.ativo {
    background: #4CAF50 !important;
    color: #fff !important;
}
.rp-btn-faltou {
    color: #e53935;
    border-color: #e53935 !important;
    background: #fff;
}
.rp-btn-faltou:hover {
    background: #FFEBEE !important;
}
.rp-btn-faltou.ativo {
    background: #e53935 !important;
    color: #fff !important;
}
.rp-btn-comportamento {
    display: block;
    width: 100%;
    padding: 10px;
    margin-top: 10px;
    background: #2196F3;
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s;
}
.rp-btn-comportamento:hover {
    background: #1976D2;
}
.rp-btn-comportamento:disabled {
    background: #ccc;
    cursor: not-allowed;
}

/* Badge de status nos registros */
.rp-badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 10px;
    font-size: 11px;
    font-weight: 600;
    margin-left: 8px;
}
.rp-badge-completo { background: #E8F5E9; color: #2E7D32; }
.rp-badge-pendente { background: #FFF3E0; color: #E65100; }
.rp-badge-falta { background: #FFEBEE; color: #C62828; }

/* Formulário - seções */
.rp-form-section {
    background: #fff;
    border: 1px solid #e8e8e8;
    border-radius: 10px;
    padding: 14px 16px;
    margin-bottom: 12px;
}
.rp-form-section legend {
    font-size: 14px;
    font-weight: 600;
    color: #2196F3;
    border: none;
    margin-bottom: 10px;
    padding: 0;
    width: auto;
}
.rp-form-section .form-control {
    border-radius: 6px;
    font-size: 14px;
}
.rp-radio-group label,
.rp-check-group label {
    display: block;
    padding: 8px 10px;
    margin: 0 0 4px 0;
    background: #f8f9fa;
    border-radius: 6px;
    font-size: 14px;
    cursor: pointer;
    transition: background 0.15s;
    font-weight: normal;
}
.rp-radio-group label:hover,
.rp-check-group label:hover {
    background: #e3f2fd;
}
.rp-radio-group input,
.rp-check-group input {
    margin-right: 8px;
}

/* Botões ação do form */
.rp-form-actions {
    display: flex;
    gap: 10px;
    margin: 16px 0 20px;
}
.rp-form-actions button {
    flex: 1;
    padding: 12px;
    border-radius: 8px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    border: none;
    transition: background 0.2s;
}
.rp-form-actions .rp-btn-salvar {
    background: #4CAF50;
    color: #fff;
}
.rp-form-actions .rp-btn-salvar:hover {
    background: #388E3C;
}
.rp-form-actions .rp-btn-rascunho {
    background: #fff3cd;
    color: #856404;
    border: 1px solid #ffc107;
}
.rp-form-actions .rp-btn-rascunho:hover {
    background: #ffe69c;
}
.rp-form-actions .rp-btn-cancelar {
    background: #f5f5f5;
    color: #666;
    border: 1px solid #ddd;
}
.rp-form-actions .rp-btn-cancelar:hover {
    background: #eee;
}

/* Empty state */
.rp-empty {
    text-align: center;
    padding: 32px 16px;
    color: #999;
}
.rp-empty .rp-empty-icon {
    font-size: 40px;
    margin-bottom: 8px;
}
.rp-empty p {
    margin: 0;
    font-size: 14px;
}

/* Loading e mensagens */
.rp-loading {
    text-align: center;
    padding: 20px;
    color: #999;
}

/* Ocultar tela */
.rp-screen-hidden {
    display: none;
}

/* Sem resultados na busca */
.rp-no-results {
    display: none;
    text-align: center;
    padding: 20px;
    color: #999;
    font-size: 14px;
}
</style>

<div class="rp-container">

    <!-- ===== TELA 1: Lista de Alunos ===== -->
    <div id="telaAlunos">
        <div class="rp-header">
            <div>
                <h4 class="rp-title">Monitoramento Comportamental</h4>
                <p class="rp-subtitle">Selecione um aluno para registrar</p>
            </div>
        </div>

        <div class="rp-search">
            <span class="rp-search-icon"><i class="fa fa-search"></i></span>
            <input type="text" id="rpBuscaAluno" placeholder="Buscar aluno...">
        </div>

        <div id="rpListaAlunos">
            <?php foreach ($alunosVinculados as $aluno): ?>
                <?php $iniciais = mb_substr($aluno['nome'], 0, 1); ?>
                <div class="rp-aluno-card" data-cod="<?php echo $aluno['cod']; ?>" data-nome="<?php echo htmlspecialchars($aluno['nome']); ?>">
                    <div class="rp-avatar"><?php echo $iniciais; ?></div>
                    <div class="rp-aluno-info">
                        <p class="rp-aluno-nome"><?php echo htmlspecialchars($aluno['nome']); ?></p>
                        <p class="rp-aluno-detalhe"><?php echo htmlspecialchars($aluno['turma']); ?> &middot; <?php echo htmlspecialchars($aluno['idade']); ?></p>
                    </div>
                    <span class="rp-chevron"><i class="fa fa-chevron-right"></i></span>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="rp-no-results" id="rpSemResultados">
            Nenhum aluno encontrado.
        </div>
    </div>

    <!-- ===== TELA 2: Registros do Aluno ===== -->
    <div id="telaRegistros" class="rp-screen-hidden">
        <div class="rp-header">
            <!-- <button type="button" class="rp-back-btn" id="btnVoltarAlunos">&larr;</button> -->
            <div>
                <h4 class="rp-title">Lista de Alunos</h4>
            </div>
        </div>
        <div class="rp-header" style="border-bottom:none; margin-bottom:0;">
            <button type="button" class="rp-back-btn" id="btnVoltarAlunos">&larr;</button>
            <div>
                <h4 class="rp-title" id="tituloRegistros">Registros</h4>
                <p class="rp-subtitle">Hist&oacute;rico de acompanhamento</p>
            </div>
        </div>

        <!-- Area de presenca do dia de hoje -->
        <div class="rp-presenca-hoje" id="rpPresencaHoje">
            <p class="rp-presenca-titulo"><i class="fa fa-calendar"></i> Hoje - <span id="rpPresencaData"></span></p>
            <div class="rp-presenca-btns">
                <button type="button" class="rp-btn-presente" id="btnPresente"><i class="fa fa-check"></i> Presente</button>
                <button type="button" class="rp-btn-faltou" id="btnFaltou"><i class="fa fa-times"></i> Faltou</button>
            </div>
            <button type="button" class="rp-btn-comportamento" id="btnComportamento" disabled>
                <i class="fa fa-clipboard"></i> Registrar comportamento
            </button>
        </div>

        <div id="rpListaRegistros">
            <div class="rp-loading" id="rpLoadingRegistros">
                <img src="<?php echo WB_PATH ?>/resources/image/loading-9.gif" style="width:32px;"> Carregando...
            </div>
        </div>

        <div class="rp-empty rp-screen-hidden" id="rpSemRegistros">
            <div class="rp-empty-icon"><i class="fa fa-clipboard"></i></div>
            <p>Nenhum registro anterior encontrado.</p>
            <p>Marque a presen&ccedil;a do aluno acima para come&ccedil;ar.</p>
        </div>
    </div>

    <!-- ===== TELA 3: Formulário ===== -->
    <div id="telaFormulario" class="rp-screen-hidden">
        <div class="rp-header">
            <button type="button" class="rp-back-btn" id="btnVoltarRegistros">&larr;</button>
            <div>
                <h4 class="rp-title" id="tituloFormulario">Novo Registro Comportamental</h4>
                <p class="rp-subtitle" id="subtituloFormulario">Preencha os dados do acompanhamento</p>
            </div>
        </div>

        <form id="formRegistroPedagogico" name="formRegistroPedagogico" action="<?php echo WB_URL_COMMAND ?>&dummy=" method="post">

            <!-- Identificação -->
            <div class="rp-form-section">
                <legend>Data do Registro</legend>
                <input type="date" id="dataRegistro" name="dataRegistro" value="" class="form-control" required>
                <div id="rpAvisoDataDuplicada" style="display:none; margin-top:8px; padding:8px 12px; background:#fff3cd; border:1px solid #ffc107; border-radius:6px; color:#856404; font-size:13px;">
                    <i class="fa fa-exclamation-triangle"></i> J&aacute; existe um registro para este aluno nesta data. Ao salvar, ser&aacute; perguntado se deseja substituir.
                </div>
            </div>

            <!-- Condição Inicial -->
            <div class="rp-form-section">
                <legend>Condição Inicial para a Aula</legend>
                <div class="rp-radio-group">
                    <label><input type="radio" name="condicaoInicio" value="adequada" required> Adequada para iniciar</label>
                    <label><input type="radio" name="condicaoInicio" value="necessitou_apoio"> Necessitou apoio inicial</label>
                    <label><input type="radio" name="condicaoInicio" value="dificuldade_inicio"> Apresentou dificuldade para iniciar</label>
                </div>
            </div>

            <!-- Durante a Aula -->
            <div class="rp-form-section">
                <legend>Participação nas Atividades</legend>
                <div class="rp-radio-group">
                    <label><input type="radio" name="participacao" value="espontanea" required> Espontânea</label>
                    <label><input type="radio" name="participacao" value="com_apoio"> Com apoio</label>
                    <label><input type="radio" name="participacao" value="resistente"> Resistiu / Recusou</label>
                </div>
            </div>

            <div class="rp-form-section">
                <legend>Grau de Apoio Necessário</legend>
                <div class="rp-radio-group">
                    <label><input type="radio" name="grauApoio" value="autonomo" required> Autônomo</label>
                    <label><input type="radio" name="grauApoio" value="pontual"> Apoio pontual</label>
                    <label><input type="radio" name="grauApoio" value="frequente"> Apoio frequente</label>
                    <label><input type="radio" name="grauApoio" value="intensivo"> Apoio intensivo</label>
                </div>
            </div>

            <div class="rp-form-section">
                <legend>Situações Observadas</legend>
                <div class="rp-check-group">
                    <label><input type="checkbox" class="situacao-check" value="agitacao"> Agitação</label>
                    <label><input type="checkbox" class="situacao-check" value="choro"> Choro</label>
                    <label><input type="checkbox" class="situacao-check" value="recusa"> Recusa de atividade</label>
                    <label><input type="checkbox" class="situacao-check" value="agressividade"> Agressividade</label>
                    <label><input type="checkbox" class="situacao-check" value="isolamento"> Isolamento</label>
                    <label><input type="checkbox" class="situacao-check" value="nenhuma"> Nenhuma situação relevante</label>
                </div>
            </div>

            <!-- Condição Final -->
            <div class="rp-form-section">
                <legend>Condição ao Final da Aula</legend>
                <div class="rp-radio-group">
                    <label><input type="radio" name="condicaoFinal" value="adequada" required> Adequada</label>
                    <label><input type="radio" name="condicaoFinal" value="melhorou_parcialmente"> Melhorou parcialmente</label>
                    <label><input type="radio" name="condicaoFinal" value="permaneceu_dificuldade"> Permaneceu com dificuldade</label>
                </div>
            </div>

            <div class="rp-form-section">
                <legend>Evolução no Período</legend>
                <div class="rp-radio-group">
                    <label><input type="radio" name="evolucaoPeriodo" value="melhorou" required> Melhorou</label>
                    <label><input type="radio" name="evolucaoPeriodo" value="manteve"> Manteve</label>
                    <label><input type="radio" name="evolucaoPeriodo" value="piorou"> Piorou</label>
                </div>
            </div>

            <!-- Informações Pedagógicas -->
            <div class="rp-form-section">
                <legend>Principais Interesses da Criança (Opcional)</legend>
                <textarea id="interesses" name="interesses"
                          class="form-control"
                          rows="2"
                          maxlength="2000"
                          style="min-height:100px; resize: vertical;"
                          placeholder="Ex: desenhar, montar quebra-cabeça, brincar com massinha..."></textarea>
            </div>

            <div class="rp-form-section">
                <legend>Brincadeiras Favoritas (Opcional)</legend>
                <textarea id="brincadeirasFavoritas" name="brincadeirasFavoritas"
                          class="form-control"
                          rows="2"
                          maxlength="2000"
                          style="min-height:100px; resize: vertical;"
                          placeholder="Ex: pega-pega, faz de conta, jogos de encaixe..."></textarea>
            </div>

            <div class="rp-form-section">
                <legend>Comportamento Alimentar (Opcional)</legend>
                <textarea id="comportamentoAlimentar" name="comportamentoAlimentar"
                          class="form-control"
                          rows="2"
                          maxlength="2000"
                          style="min-height:100px; resize: vertical;"
                          placeholder="Ex: aceitou bem o lanche, recusou frutas, comeu de forma independente..."></textarea>
            </div>

            <!-- Observação -->
            <div class="rp-form-section">
                <legend>Observação Geral (Opcional)</legend>
                <p style="font-size: 12px; color: #888; margin: 0 0 8px 0;">
                    Registrar apenas fatos objetivos observados durante o período.
                </p>
                <textarea id="observacao" name="observacao"
                          class="form-control"
                          rows="3"
                          maxlength="2000"
                          style="min-height:120px; resize: vertical;"
                          placeholder="Ex: apresentou dificuldade para permanecer sentado durante atividade coletiva."></textarea>
                <p style="font-size: 11px; color: #aaa; margin: 4px 0 0 0;">Máximo 2000 caracteres. Arraste a alça inferior do campo para redimensionar.</p>
            </div>

            <!-- Hidden fields (antes do _submit_check para serem processados pelo setAll) -->
            <input type="hidden" name="novoCodRegistro" id="novoCodRegistro" value="0" />
            <input type="hidden" name="codRegistro" id="codRegistro" value="" />
            <input type="hidden" name="codAluno" id="codAluno" value="1" />
            <input type="hidden" name="codAgente" id="codAgente" value="<?php echo htmlspecialchars($codAgente); ?>" />
            <input type="hidden" name="situacoes" id="situacoesJson" value="" />
            <input type="hidden" name="presenca" id="presencaHidden" value="PRESENTE" />
            <input type="hidden" name="rascunho" id="rascunho" value="0" />
            <input type="hidden" value="1" name="_submit_check">
            <input type="hidden" value="GerenciarRegistroPedagogico" name="c">
            <input type="hidden" value="salvarRegistroPedagogico" name="m">

            <div id="carregando" class="shown hidden">
                <div class="imagem">
                    <div class="frase">Carregando...</div>
                    <img src="<?php echo WB_PATH ?>/resources/image/loading-9.gif" border="0">
                </div>
            </div>
            <div id="mensagem"></div>
            <div id="sucesso" class="hidden"></div>
            <div id="erro" class="hidden"></div>

            <!-- Botões -->
            <div class="rp-form-actions">
                <button class="rp-btn-salvar" type="button" id="btnSalvar">Salvar</button>
                <button class="rp-btn-rascunho" type="button" id="btnSalvarRascunho" title="Salvar o que estiver preenchido para completar depois">Salvar rascunho</button>
                <button class="rp-btn-cancelar" type="button" id="btnCancelar">Cancelar</button>
            </div>
        </form>
    </div>

</div>

<!-- Tabela oculta para manter compatibilidade com carregarTabelaRegistroPedagogicos -->
<div style="display:none;">
    <table id="itens">
        <tbody class="inputs">
            <?php $objSRV->carregarTabelaRegistroPedagogicos($fws_, $debug); ?>
        </tbody>
    </table>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
<script type="text/javascript" src="resources/js/jquery/jquery.form.js"></script>
<script type="text/javascript" src="resources/js/jquery/jquery.price_format.1.7.min.js"></script>
<script type="text/javascript" src="resources/js/default.js"></script>

<script type="text/javascript">
var $j = jQuery.noConflict();

// ===== Estado da aplicação =====
var rpAlunoAtual = { cod: '', nome: '' };
var rpDatasRegistros = []; // datas (yyyy-mm-dd) dos registros já cadastrados
var rpRegistroEditandoId = null; // id do registro em edição (para não alertar sobre ele mesmo)
var _rpIdDuplicata = null; // id do registro duplicado encontrado

function rpDataHoje() {
    var d = new Date();
    var mm = ('0' + (d.getMonth() + 1)).slice(-2);
    var dd = ('0' + d.getDate()).slice(-2);
    return d.getFullYear() + '-' + mm + '-' + dd;
}

// Converte dd/mm/yyyy para yyyy-mm-dd
function rpConverterData(dataBR) {
    var p = dataBR.split('/');
    return p.length === 3 ? p[2] + '-' + p[1] + '-' + p[0] : '';
}

// Retorna o id do registro duplicado ou null
function rpBuscarDuplicata() {
    var dataSelecionada = $j('#dataRegistro').val();
    if (!dataSelecionada) return null;
    for (var i = 0; i < rpDatasRegistros.length; i++) {
        if (rpDatasRegistros[i].data === dataSelecionada && rpDatasRegistros[i].id !== rpRegistroEditandoId) {
            return rpDatasRegistros[i].id;
        }
    }
    return null;
}

function rpVerificarDataDuplicada() {
    var aviso = $j('#rpAvisoDataDuplicada');
    if (rpBuscarDuplicata()) {
        aviso.show();
    } else {
        aviso.hide();
    }
}

// ===== Navegação entre telas =====
function rpMostrarTela(telaId) {
    $j('#telaAlunos, #telaRegistros, #telaFormulario').addClass('rp-screen-hidden');
    $j('#' + telaId).removeClass('rp-screen-hidden');
    // Scroll ao topo
    window.scrollTo(0, 0);
}

// ===== Tela 1: Seleção de aluno =====
$j(document).ready(function() {

    // Filtro de busca
    $j('#rpBuscaAluno').on('input', function() {
        var termo = $j(this).val().toLowerCase();
        var encontrou = false;
        $j('#rpListaAlunos .rp-aluno-card').each(function() {
            var nome = $j(this).data('nome').toLowerCase();
            if (nome.indexOf(termo) !== -1) {
                $j(this).show();
                encontrou = true;
            } else {
                $j(this).hide();
            }
        });
        $j('#rpSemResultados').toggle(!encontrou);
    });

    // Click no card do aluno
    $j('#rpListaAlunos').on('click', '.rp-aluno-card', function() {
        rpAlunoAtual.cod = $j(this).data('cod');
        rpAlunoAtual.nome = $j(this).data('nome');
        $j('#codAluno').val(rpAlunoAtual.cod);
        $j('#tituloRegistros').text(rpAlunoAtual.nome);
        rpMostrarTela('telaRegistros');
        carregarRegistrosCards();
    });

    // ===== Tela 2: Botões =====
    $j('#btnVoltarAlunos').click(function() {
        rpMostrarTela('telaAlunos');
    });

    // Formatar data de hoje na area de presenca
    var hoje = rpDataHoje();
    var hojeParts = hoje.split('-');
    $j('#rpPresencaData').text(hojeParts[2] + '/' + hojeParts[1] + '/' + hojeParts[0]);

    // Botao Presente
    $j('#btnPresente').click(function() {
        rpMarcarPresenca('PRESENTE');
    });

    // Botao Faltou
    $j('#btnFaltou').click(function() {
        rpMarcarPresenca('FALTA');
    });

    // Botao Registrar Comportamento
    $j('#btnComportamento').click(function() {
        if ($j(this).prop('disabled')) return;
        var regHoje = rpGetRegistroHoje();
        if (regHoje && regHoje.id) {
            // Editar registro existente
            editarRegistroPedagogico(regHoje.id);
        } else {
            // Novo registro (fallback)
            limparCampos();
            rpRegistroEditandoId = null;
            $j('#dataRegistro').val(rpDataHoje());
            $j('#tituloFormulario').text('Novo Registro Comportamental');
            $j('#subtituloFormulario').text(rpAlunoAtual.nome);
            rpVerificarDataDuplicada();
            rpMostrarTela('telaFormulario');
        }
    });

    // Verificar duplicata ao mudar a data
    $j('#dataRegistro').on('change', function() {
        rpVerificarDataDuplicada();
    });

    // ===== Tela 3: Botões =====
    $j('#btnVoltarRegistros').click(function() {
        rpMostrarTela('telaRegistros');
    });

    $j('#btnSalvar').click(function() {
        $j('#rascunho').val('0');
        salvarRegistroPedagogico();
    });

    $j('#btnSalvarRascunho').click(function() {
        $j('#rascunho').val('1');
        salvarRegistroPedagogico();
    });

    $j('#btnCancelar').click(function() {
        rpMostrarTela('telaRegistros');
    });
});

// ===== Carregar registros em formato card =====
function carregarRegistrosCards() {
    $j('#rpLoadingRegistros').show();
    $j('#rpSemRegistros').addClass('rp-screen-hidden');
    // Limpa cards anteriores (mantém loading)
    $j('#rpListaRegistros .rp-registro-card').remove();

    $j.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random(), {
        'c': 'GerenciarRegistroPedagogico',
        'm': 'carregarTabelaRegistroPedagogicos',
        'codAluno': $j('#codAluno').val(),
        '_submit_check': '1'
    }, function(html) {
        $j('#rpLoadingRegistros').hide();

        // Também atualiza tabela oculta para compatibilidade
        $j("table#itens tbody.inputs tr").remove();
        $j("table#itens tbody.inputs").append(html);

        // Lê os dados da tabela e gera cards (apenas tr com id = registro real)
        var rows = $j("table#itens tbody.inputs tr[id]");
        rpDatasRegistros = []; // reseta lista de datas
        if (rows.length === 0) {
            $j('#rpSemRegistros').removeClass('rp-screen-hidden');
            rpAtualizarPresencaHoje();
            return;
        }

        var labels = {
            'adequada': 'Adequada',
            'necessitou_apoio': 'Necessitou apoio',
            'dificuldade_inicio': 'Dificuldade início',
            'espontanea': 'Espontânea',
            'com_apoio': 'Com apoio',
            'resistente': 'Resistiu/Recusou',
            'autonomo': 'Autônomo',
            'pontual': 'Apoio pontual',
            'frequente': 'Apoio frequente',
            'intensivo': 'Apoio intensivo',
            'melhorou_parcialmente': 'Melhorou parcial',
            'permaneceu_dificuldade': 'Com dificuldade',
            'melhorou': 'Melhorou',
            'manteve': 'Manteve',
            'piorou': 'Piorou'
        };

        rows.each(function() {
            var tr = $j(this);
            var id = tr.attr('id');
            var tds = tr.find('td');
            var data = $j(tds[1]).text().trim();
            var presenca = tr.data('presenca') || '';
            var condicaoInicio = $j(tds[2]).text().trim();
            var condicaoFinal = $j(tds[5]).text().trim();

            // Armazena data no formato yyyy-mm-dd para verificacao de duplicata
            rpDatasRegistros.push({ id: id, data: rpConverterData(data), presenca: presenca, condicaoInicio: condicaoInicio });

            // Nao mostrar card para registro de hoje (ja tem a area de presenca)
            var dataISO = rpConverterData(data);
            if (dataISO === rpDataHoje()) {
                rpAtualizarPresencaHoje();
                return; // skip, nao gera card
            }

            var status = rpCalcularStatus(presenca, condicaoInicio, condicaoFinal);
            var badge = presenca ? ' <span class="rp-badge ' + status.css + '">' + status.label + '</span>' : '';

            var card = '<div class="rp-registro-card" data-id="' + id + '">';
            card += '<div class="rp-reg-data"><i class="fa fa-calendar"></i> ' + data + badge + '</div>';
            card += '<div class="rp-reg-actions">';
            if (presenca === 'FALTA' || presenca === 'FALTA_JUSTIFICADA') {
                card += '<button type="button" class="rp-btn-excluir" data-id="' + id + '"><i class="fa fa-trash"></i> Excluir</button>';
            } else {
                card += '<button type="button" class="rp-btn-visualizar" data-id="' + id + '"><i class="fa fa-eye"></i> Visualizar</button>';
                card += '<button type="button" class="rp-btn-editar" data-id="' + id + '"><i class="fa fa-pencil"></i> Editar</button>';
                card += '<button type="button" class="rp-btn-excluir" data-id="' + id + '"><i class="fa fa-trash"></i> Excluir</button>';
            }
            card += '</div>';
            card += '</div>';

            $j('#rpListaRegistros').append(card);
        });

        rpAtualizarPresencaHoje();

        // Bind visualizar/editar/excluir nos cards
        $j('#rpListaRegistros').off('click', '.rp-btn-visualizar').on('click', '.rp-btn-visualizar', function(e) {
            e.stopPropagation();
            var id = $j(this).data('id');
            visualizarRegistroPedagogico(id);
        });
        $j('#rpListaRegistros').off('click', '.rp-btn-editar').on('click', '.rp-btn-editar', function(e) {
            e.stopPropagation();
            var id = $j(this).data('id');
            editarRegistroPedagogico(id);
        });
        $j('#rpListaRegistros').off('click', '.rp-btn-excluir').on('click', '.rp-btn-excluir', function(e) {
            e.stopPropagation();
            var id = $j(this).data('id');
            excluirRegistroPedagogico(id);
        });
    });
}

// Compat: manter a função original para chamadas internas
function carregarTabelaRegistroPedagogicos() {
    carregarRegistrosCards();
}

// ===== Salvar =====
function salvarRegistroPedagogico() {
    // Verifica se já existe registro para esta data
    var idDuplicata = rpBuscarDuplicata();
    if (idDuplicata) {
        _rpIdDuplicata = idDuplicata;
        $j('#modalConfirmarDuplicata').modal('show');
        return;
    }

    rpExecutarSalvar();
}

// ===== Visualizar (somente leitura) =====
function visualizarRegistroPedagogico(codRegistro) {
    if (!codRegistro) return;
    // Carrega os dados no formulário em modo leitura
    rpRegistroEditandoId = String(codRegistro);
    carregarFormulario(codRegistro, function() {
        $j('#tituloFormulario').text('Visualizar Registro Comportamental');
        $j('#subtituloFormulario').text(rpAlunoAtual.nome);
        // Desabilita todos os campos
        $j('#formRegistroPedagogico input, #formRegistroPedagogico textarea, #formRegistroPedagogico select').prop('disabled', true);
        $j('.rp-form-actions .rp-btn-salvar').hide();
        $j('.rp-form-actions .rp-btn-rascunho').hide();
        $j('.rp-form-actions .rp-btn-cancelar').text('Voltar');
        rpMostrarTela('telaFormulario');
    });
}

// ===== Carregar dados no formulário (compartilhado entre visualizar e editar) =====
function carregarFormulario(codRegistro, callback) {
    $j.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {
            'codRegistro': codRegistro,
            'c': 'GerenciarRegistroPedagogico',
            'm': 'editarRegistroPedagogico',
            '_submit_check': '1'
        },
        dataType: "json",
        async: false,
        cache: false,
        beforeSend: function() {
            startLoading();
        },
        success: function(data) {
            limparCampos();
            // Reabilita campos (caso venha de uma visualização anterior)
            $j('#formRegistroPedagogico input, #formRegistroPedagogico textarea, #formRegistroPedagogico select').prop('disabled', false);
            $j('.rp-form-actions .rp-btn-salvar').show();
            $j('.rp-form-actions .rp-btn-rascunho').show();
            $j('.rp-form-actions .rp-btn-cancelar').text('Cancelar');

            $j('#codRegistro').val(data.codRegistro);
            $j('#codAluno').val(data.codAluno);
            $j('#codAgente').val(data.codAgente);
            $j('#dataRegistro').val(data.dataRegistro);

            $j('input[name="condicaoInicio"][value="' + data.condicaoInicio + '"]').prop('checked', true);
            $j('input[name="participacao"][value="' + data.participacao + '"]').prop('checked', true);
            $j('input[name="grauApoio"][value="' + data.grauApoio + '"]').prop('checked', true);
            $j('input[name="condicaoFinal"][value="' + data.condicaoFinal + '"]').prop('checked', true);
            $j('input[name="evolucaoPeriodo"][value="' + data.evolucaoPeriodo + '"]').prop('checked', true);

            $j('.situacao-check').prop('checked', false);
            if (data.situacoes) {
                var situacoes = JSON.parse(data.situacoes);
                situacoes.forEach(function(val) {
                    $j('.situacao-check[value="' + val + '"]').prop('checked', true);
                });
            }

            $j('#interesses').val(data.interesses);
            $j('#brincadeirasFavoritas').val(data.brincadeirasFavoritas);
            $j('#comportamentoAlimentar').val(data.comportamentoAlimentar);
            $j('#observacao').val(data.observacao);
            $j('#novoCodRegistro').val(1);

            if (callback) callback();
        },
        complete: function() {
            stopLoading();
        }
    });
}

// ===== Editar =====
function editarRegistroPedagogico(codRegistro) {
    if (!codRegistro) return;
    rpRegistroEditandoId = String(codRegistro);
    carregarFormulario(codRegistro, function() {
        $j('#btnSalvar').text('Salvar');
        $j('#tituloFormulario').text('Editar Registro Comportamental');
        $j('#subtituloFormulario').text(rpAlunoAtual.nome);
        rpVerificarDataDuplicada();
        rpMostrarTela('telaFormulario');
    });
}

// ===== Excluir =====
var _codRegistroExcluir = null;

function excluirRegistroPedagogico(codRegistro) {
    _codRegistroExcluir = codRegistro;
    $j('#modalExcluirRegistro').modal('show');
}

function confirmarExclusaoRegistro() {
    if (!_codRegistroExcluir) return;
    $j('#modalExcluirRegistro').modal('hide');
    $j.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {
            'codRegistro': _codRegistroExcluir,
            'c': 'GerenciarRegistroPedagogico',
            'm': 'excluirRegistroPedagogico',
            '_submit_check': '1'
        },
        dataType: "json",
        async: false,
        cache: false,
        beforeSend: function() {
            startLoading();
        },
        success: function(data) {
            processJson(data);
            if (data.statusMessage) {
                carregarRegistrosCards();
            }
        },
        complete: function() {
            stopLoading();
            _codRegistroExcluir = null;
        }
    });
}

// ===== Limpar campos =====
function limparCampos() {
    $j('#codRegistro').val('');
    $j('#dataRegistro').val('');
    $j('#rpAvisoDataDuplicada').hide();
    $j('input[name="condicaoInicio"]').prop('checked', false);
    $j('input[name="participacao"]').prop('checked', false);
    $j('input[name="grauApoio"]').prop('checked', false);
    $j('.situacao-check').prop('checked', false);
    $j('input[name="condicaoFinal"]').prop('checked', false);
    $j('input[name="evolucaoPeriodo"]').prop('checked', false);
    $j('#interesses').val('');
    $j('#brincadeirasFavoritas').val('');
    $j('#comportamentoAlimentar').val('');
    $j('#observacao').val('');
    $j('#situacoesJson').val('');
    $j('#novoCodRegistro').val(0);
    $j('#rascunho').val('0');
    $j('#btnSalvar').text('Salvar');
}

// Confirmar substituição de registro duplicado
function confirmarSubstituirDuplicata() {
    $j('#modalConfirmarDuplicata').modal('hide');
    // Substituir: usa o codRegistro existente para fazer UPDATE
    $j('#codRegistro').val(_rpIdDuplicata);
    $j('#novoCodRegistro').val(1);
    _rpIdDuplicata = null;
    rpExecutarSalvar();
}

// ===== Presenca =====
function rpGetRegistroHoje() {
    var hoje = rpDataHoje();
    for (var i = 0; i < rpDatasRegistros.length; i++) {
        if (rpDatasRegistros[i].data === hoje) {
            return rpDatasRegistros[i];
        }
    }
    return null;
}

function rpAtualizarPresencaHoje() {
    var regHoje = rpGetRegistroHoje();
    $j('#btnPresente').removeClass('ativo');
    $j('#btnFaltou').removeClass('ativo');
    $j('#btnComportamento').prop('disabled', true);

    if (regHoje && regHoje.presenca === 'PRESENTE') {
        $j('#btnPresente').addClass('ativo');
        $j('#btnComportamento').prop('disabled', false);
        // Verificar se comportamento ja foi preenchido
        if (regHoje.condicaoInicio) {
            $j('#btnComportamento').html('<i class="fa fa-pencil"></i> Editar comportamento');
        } else {
            $j('#btnComportamento').html('<i class="fa fa-clipboard"></i> Registrar comportamento');
        }
    } else if (regHoje && regHoje.presenca === 'FALTA') {
        $j('#btnFaltou').addClass('ativo');
        $j('#btnComportamento').prop('disabled', true).html('<i class="fa fa-clipboard"></i> Registrar comportamento');
    } else {
        $j('#btnComportamento').html('<i class="fa fa-clipboard"></i> Registrar comportamento');
    }
}

function rpMarcarPresenca(presenca) {
    var regHoje = rpGetRegistroHoje();
    // Se ja tem registro hoje com mesma presenca, nao faz nada
    if (regHoje && regHoje.presenca === presenca) return;

    $j.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
        data: {
            'c': 'GerenciarRegistroPedagogico',
            'm': 'marcarPresenca',
            'codAluno': $j('#codAluno').val(),
            'codAgente': $j('#codAgente').val(),
            'dataRegistro': rpDataHoje(),
            'presenca': presenca,
            '_submit_check': '1'
        },
        dataType: "json",
        cache: false,
        beforeSend: function() {
            startLoading();
        },
        success: function(data) {
            if (data.statusMessage) {
                carregarRegistrosCards();
            } else {
                processJson(data);
            }
        },
        complete: function() {
            stopLoading();
        }
    });
}

function rpCalcularStatus(presenca, condicaoInicio, condicaoFinal) {
    if (presenca === 'FALTA' || presenca === 'FALTA_JUSTIFICADA') {
        return {label: 'Faltou', css: 'rp-badge-falta'};
    }
    if (!condicaoInicio) {
        return {label: 'Entrada pendente', css: 'rp-badge-pendente'};
    }
    if (!condicaoFinal) {
        return {label: 'Sa\u00edda pendente', css: 'rp-badge-pendente'};
    }
    return {label: 'Completo', css: 'rp-badge-completo'};
}

function rpExecutarSalvar() {
    var situacoesArray = [];
    $j('.situacao-check:checked').each(function() {
        situacoesArray.push($j(this).val());
    });
    $j('#situacoesJson').val(JSON.stringify(situacoesArray));

    $j('#formRegistroPedagogico').ajaxSubmit({
        target: '#mensagem',
        dataType: 'json',
        async: false,
        cache: false,
        beforeSend: function() {
            startLoading();
        },
        timeout: 4000,
        error: function(ajax) {
            showResponse(ajax);
        },
        success: function(data) {
            processJson(data);
            if (data.statusMessage) {
                limparCampos();
                rpMostrarTela('telaRegistros');
                carregarRegistrosCards();
            }
        },
        complete: function() {
            stopLoading();
        }
    });
}
</script>

<!-- Modal Confirmar Substituicao de Registro Duplicado -->
<div class="modal fade" id="modalConfirmarDuplicata" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#f0ad4e; color:#fff;">
                <button type="button" class="close" data-dismiss="modal" style="color:#fff;"><span>&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-exclamation-triangle"></i> Registro Duplicado</h4>
            </div>
            <div class="modal-body">
                <p>J&aacute; existe um registro para este aluno nesta data.</p>
                <p>Deseja substituir o registro existente?</p>
                <p class="text-muted"><small>N&atilde;o &eacute; permitido manter dois registros para o mesmo dia.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning" onclick="confirmarSubstituirDuplicata()"><i class="fa fa-refresh"></i> Substituir</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Confirmar Exclusao Registro Pedagogico -->
<div class="modal fade" id="modalExcluirRegistro" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#d9534f; color:#fff;">
                <button type="button" class="close" data-dismiss="modal" style="color:#fff;"><span>&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-exclamation-triangle"></i> Confirmar Exclus&atilde;o</h4>
            </div>
            <div class="modal-body">
                <p>Deseja realmente excluir este registro?</p>
                <p class="text-danger"><small><i class="fa fa-warning"></i> Esta a&ccedil;&atilde;o n&atilde;o pode ser desfeita.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" onclick="confirmarExclusaoRegistro()"><i class="fa fa-trash"></i> Excluir</button>
            </div>
        </div>
    </div>
</div>

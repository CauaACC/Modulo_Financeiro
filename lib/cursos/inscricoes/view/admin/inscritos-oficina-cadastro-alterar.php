<?php
/*
 * Alterar a participacao do inscrito numa atividade.
 *
 * Antes: a consulta nao filtrava o tipo_relacao no evento 10 e o laco desenhava
 * UMA ficha por relacao do inscrito na atividade -- como o formulario vinha de
 * um require_once, so a primeira saia com campos e as outras ficavam so com os
 * botoes "Alterar/Limpar" soltos na tela. Ainda por cima, a ficha carregada era
 * sempre a da PRIMEIRA relacao, nao a que o usuario clicou.
 *
 * Agora: uma participacao, a que foi clicada (cod_oficina + tipo_relacao), num
 * formulario unico. Nenhum campo foi removido; o que mudou e' a hierarquia --
 * quem e' o inscrito e qual participacao esta sendo editada aparece no topo.
 */
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/inscritos-cabecalho.php");
#--------------------------------------------------------------------
$debug = false;
@$cod_inscrito = $fwsGet_['cod_inscrito'];
@$cod_oficina  = $fwsGet_['cod_oficina'];
@$tipo_relacao = isset($fwsGet_['tipo_relacao']) ? $fwsGet_['tipo_relacao'] : '';

$MSG = isset($_SESSION['MSG']) ? $_SESSION['MSG'] : '';
$MSG_Imprimir = preg_replace('/--/', '<br>', $MSG);
$MSG_Imprimir = preg_replace('/-/',  ' ',    $MSG_Imprimir);

if($MSG_Imprimir){
    echo "<div class='alert alert-info alert-dismissible'>"
       . "<button type='button' class='close' data-dismiss='alert' aria-label='Fechar'><span aria-hidden='true'>&times;</span></button>"
       . $MSG_Imprimir . "</div>";
}

$urlFicha = 'index.php?content=view/admin/inscritos-cadastro-ok.php&cod_inscrito='.(int)$cod_inscrito;

if(!isset($cod_oficina) || (int)$cod_oficina<=0){
    echo "<div class='alert alert-warning'>É necessário informar o código da atividade.</div>";
    echo "<a href='".$urlFicha."' class='btn btn-default'>Voltar ao inscrito</a>";
    echo "</div></div></div>";
    return;
}

$Conexao_db = FWA_Conexao("OpcTec");

// A participacao e' identificada pelo tipo_relacao junto com a atividade: o
// mesmo inscrito pode ser 'Participante', 'Participante 2', 'Ministrante'...
// Sem esse filtro a tela abria a relacao errada e repetia a ficha.
if($tipo_relacao===''){
    $whereRelacao = " AND (tli.tipo_relacao IS NULL OR tli.tipo_relacao='') ";
}else{
    $whereRelacao = " AND (tli.tipo_relacao = '".addslashesopctec($tipo_relacao)."') ";
}

$var_Sql = "
             SELECT
               ti.cod_inscrito
              ,ti.nome_inscrito
              ,tli.cod_oficina
              ,too.titulo
              ,too.tipo
              ,too.data_inicio
              ,tli.tipo_relacao
              ,tli.flg_presenca
              ,tli.frequencia
              ,tli.carga_horaria
              ,tli.carga_horaria_pratica
              ,tli.carga_horaria_teorica
              FROM tab_inscritos ti, tab_lig_inscritos_oficinas tli, tab_opcoes_oficinas too
              WHERE
              (tli.cod_oficina  = ".(int)$cod_oficina.")AND
              (tli.cod_evento   = ".COD_EVENTO.")AND
              (tli.cod_inscrito = ".(int)$cod_inscrito.")AND
              (tli.cod_inscrito = ti.cod_inscrito)AND
              (tli.cod_oficina  = too.cod_oficina)
              $whereRelacao
              LIMIT 1
             ";

$RS_Of = FWA_Executa_SQL($debug,$Conexao_db,$var_Sql);

if(!$RS_Of){
    echo "<div class='alert alert-danger'>Não foi possível consultar a participação.</div>";
    echo "<a href='".$urlFicha."' class='btn btn-default'>Voltar ao inscrito</a>";
    echo "</div></div></div>";
    return;
}
if($RS_Of->EOF){
    echo "<div class='alert alert-warning'>Participação não encontrada — pode ter sido excluída ou alterada em outra janela.</div>";
    echo "<a href='".$urlFicha."' class='btn btn-default'>Voltar ao inscrito</a>";
    echo "</div></div></div>";
    return;
}

$nome_inscrito          = $RS_Of->fields['nome_inscrito'];
$cod_oficina            = $RS_Of->fields['cod_oficina'];
$titulo_oficina         = $RS_Of->fields['titulo'];
$tipo_oficina           = $RS_Of->fields['tipo'];
$data_inicio_oficina    = $RS_Of->fields['data_inicio'];
$tipo_relacao           = $RS_Of->fields['tipo_relacao'];
$flg_presenca           = $RS_Of->fields['flg_presenca'];
$frequencia             = $RS_Of->fields['frequencia'];
$carga_horaria          = $RS_Of->fields['carga_horaria'];
$carga_horaria_pratica  = $RS_Of->fields['carga_horaria_pratica'];
$carga_horaria_teorica  = $RS_Of->fields['carga_horaria_teorica'];

// Outras participacoes do mesmo inscrito NESTA atividade: o usuario precisa
// saber que existem, senao acha que esta editando "a" inscricao dele.
$outrasRelacoes = array();
$sqlOutras = "SELECT tipo_relacao FROM tab_lig_inscritos_oficinas
               WHERE cod_evento=".COD_EVENTO." AND cod_inscrito=".(int)$cod_inscrito."
                 AND cod_oficina=".(int)$cod_oficina;
if($RS_Outras = FWA_Executa_SQL($debug,$Conexao_db,$sqlOutras)){
    while(!$RS_Outras->EOF){
        $r = $RS_Outras->fields['tipo_relacao'];
        if($r !== $tipo_relacao){
            $outrasRelacoes[] = ($r!==null && $r!=='') ? $r : '(sem relação)';
        }
        $RS_Outras->MoveNext();
    }
}

// O select de tipo de relacao tem uma lista fixa; valores como 'Participante 3'
// (criados na carga) nao estao nela. Sem esta opcao extra, salvar trocaria a
// relacao sem o usuario pedir -- e a relacao faz parte da chave do registro.
ob_start();
CarregarOptionsAC::tipoRelacao($tipo_relacao, false);
$optionsRelacao = ob_get_clean();
if(strpos($optionsRelacao, 'selected') === false && $tipo_relacao!==null && $tipo_relacao!==''){
    $optionsRelacao = "<option selected='selected'>".htmlspecialchars($tipo_relacao, ENT_QUOTES, 'UTF-8')."</option>"
                    . $optionsRelacao;
}

ob_start();
CarregarOptionsAC::presenca($flg_presenca, false);
$optionsPresenca = ob_get_clean();

// A funcao do select de atividade e' compartilhada com outras telas e nao leva
// classe; aqui ela entra no visual do formulario sem precisar ser alterada.
$selectOficina = FWA_Select_Oficina($cod_oficina, $titulo_oficina, COD_EVENTO);
$selectOficina = str_replace("<select name='COD_OFICINA'>", "<select name='COD_OFICINA' class='form-control'>", $selectOficina);

$tituloExibicao = $titulo_oficina;
if($tipo_oficina=='Agenda' && $data_inicio_oficina!=''){
    $tituloExibicao = formatDateView($data_inicio_oficina).' - '.$titulo_oficina;
}
$relacaoExibicao = ($tipo_relacao!==null && $tipo_relacao!=='') ? $tipo_relacao : '(sem relação)';
?>

<style>
    .part-cab { border-left:4px solid #4A90E2; background:#f7f9fc; padding:12px 14px; margin-bottom:16px; }
    .part-cab .part-titulo { font-size:16px; font-weight:bold; }
    .part-cab .part-meta { color:#666; font-size:13px; margin-top:4px; }
    .part-cab .part-badge { display:inline-block; background:#4A90E2; color:#fff; border-radius:3px;
                            padding:1px 8px; font-size:12px; margin-left:6px; }
    .form-participacao .form-group { margin-bottom:14px; }
    .form-participacao .ajuda { color:#888; font-size:12px; }
    .part-acoes { margin-top:18px; padding-top:14px; border-top:1px solid #eee; }
</style>

<h3 style="margin-top:0;">Alterar participação na atividade</h3>

<div class="part-cab">
    <div class="part-titulo">
        <?php echo htmlspecialchars($tituloExibicao, ENT_QUOTES, 'UTF-8'); ?>
        <span class="part-badge"><?php echo htmlspecialchars($relacaoExibicao, ENT_QUOTES, 'UTF-8'); ?></span>
    </div>
    <div class="part-meta">
        Inscrito <strong><?php echo (int)$cod_inscrito; ?></strong> —
        <?php echo htmlspecialchars((string)$nome_inscrito, ENT_QUOTES, 'UTF-8'); ?>
        &nbsp;|&nbsp; Cód. atividade <strong><?php echo (int)$cod_oficina; ?></strong>
        <?php if($tipo_oficina!=''): ?>&nbsp;|&nbsp; <?php echo htmlspecialchars($tipo_oficina, ENT_QUOTES, 'UTF-8'); ?><?php endif; ?>
    </div>
</div>

<?php if(!empty($outrasRelacoes)): ?>
    <div class="alert alert-info">
        Este inscrito também está nesta atividade como
        <strong><?php echo htmlspecialchars(implode(', ', $outrasRelacoes), ENT_QUOTES, 'UTF-8'); ?></strong>.
        A alteração abaixo vale <u>somente</u> para a participação
        <strong><?php echo htmlspecialchars($relacaoExibicao, ENT_QUOTES, 'UTF-8'); ?></strong>.
    </div>
<?php endif; ?>

<?php overlayProcessando(); ?>

<form method="POST" name="form" class="form-participacao"
      data-processando="Salvando a participação..."
      action="view/admin/inscritos-oficina-cadastro-alterar-grava.php">

    <input type="hidden" name="cod_inscrito"          value="<?php echo (int)$cod_inscrito; ?>">
    <input type="hidden" name="cod_evento"            value="<?php echo COD_EVENTO; ?>">
    <input type="hidden" name="cod_oficina_anterior"  value="<?php echo (int)$cod_oficina; ?>">
    <input type="hidden" name="tipo_relacao_anterior" value="<?php echo htmlspecialchars((string)$tipo_relacao, ENT_QUOTES, 'UTF-8'); ?>">

    <div class="row">
        <div class="col-md-8">
            <div class="form-group">
                <label>Atividade</label>
                <?php echo $selectOficina; ?>
                <div class="ajuda">Trocar a atividade move esta participação para a atividade escolhida.</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="tipo_relacao">Tipo de relação com a atividade</label>
                <select id="tipo_relacao" name="tipo_relacao" class="form-control">
                    <?php echo $optionsRelacao; ?>
                </select>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                <label for="flg_presenca">Presença</label>
                <select id="flg_presenca" name="flg_presenca" class="form-control">
                    <option value=""></option>
                    <?php echo $optionsPresenca; ?>
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="frequencia">Frequência</label>
                <input class="form-control" type="text" id="frequencia" name="frequencia" maxlength="50"
                       value="<?php echo htmlspecialchars((string)$frequencia, ENT_QUOTES, 'UTF-8'); ?>"
                       onBlur="trim(this);">
                <div class="ajuda">Só o número. Ex.: 98 (sem o %)</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-group">
                <label for="carga_horaria">Carga horária</label>
                <input class="form-control" type="text" id="carga_horaria" name="carga_horaria" maxlength="50"
                       value="<?php echo htmlspecialchars((string)$carga_horaria, ENT_QUOTES, 'UTF-8'); ?>"
                       onBlur="trim(this);">
                <div class="ajuda">Ex.: 16h</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-group">
                <label for="carga_horaria_pratica">C.H. prática</label>
                <input class="form-control" type="text" id="carga_horaria_pratica" name="carga_horaria_pratica" maxlength="50"
                       value="<?php echo htmlspecialchars((string)$carga_horaria_pratica, ENT_QUOTES, 'UTF-8'); ?>"
                       onBlur="trim(this);">
                <div class="ajuda">Ex.: 16h</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-group">
                <label for="carga_horaria_teorica">C.H. teórica</label>
                <input class="form-control" type="text" id="carga_horaria_teorica" name="carga_horaria_teorica" maxlength="50"
                       value="<?php echo htmlspecialchars((string)$carga_horaria_teorica, ENT_QUOTES, 'UTF-8'); ?>"
                       onBlur="trim(this);">
                <div class="ajuda">Ex.: 16h</div>
            </div>
        </div>
    </div>

    <div class="part-acoes">
        <button type="submit" class="btn btn-primary" id="btnSalvarParticipacao">
            <i class="glyphicon glyphicon-ok"></i> Salvar alterações
        </button>
        &nbsp;
        <a href="<?php echo $urlFicha; ?>" class="btn btn-default">Cancelar</a>
    </div>
</form>

<script type="text/javascript">
$(document).ready(function(){
    // Duplo clique em "Salvar" dispararia dois UPDATEs; o botao trava no envio.
    $('form.form-participacao').on('submit', function(){
        $('#btnSalvarParticipacao').prop('disabled', true).html('Salvando...');
        return true;
    });
});
</script>

<div style="margin-bottom:28px;"></div>

</div><!-- panel-body (aberto em inscritos-cabecalho.php) -->
</div><!-- panel -->
</div><!-- panel-group -->

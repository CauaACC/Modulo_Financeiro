<?php

class GerenciarAvaliacaoInscritoAC
{
    /**
     * Garante checagem de auth ao instanciar o controller. O seguranca.php faz
     * FWA_Autenticacao e da exit() se nao houver permissao — protegendo todos
     * os metodos publicos contra invocacao via AJAX sem sessao valida.
     */
    function __construct() {
        require_once(dirname(__FILE__) . "/../view/admin/consulta-avaliacao-inscrito-seguranca.php");
    }

    public function verificarContratos($fws_, $objAI, $debug = false)
    {
        try {
            $objInscrito = new Inscrito(); 
            $objInscrito->setCodInscrito($objAI->getCodInscrito()); 
            $objInscrito->getInscritoById($debug);
            $objInscrito->carregarListaEventosByCpfInscrito($debug);
            echo "<ul>";
            $listouAlgumEventoInscrito = 0;
            foreach ($objInscrito->getListaEventos() as $objL) {
                if ($objL->getVisibilidade() == 'PUBLICO' and $objL->getCpf() == $objInscrito->getCpf()) {
                    /**@todo aqui devemos inserir uma configuração
                     * nos eventos que se relacionam internamente */
                    if (in_array($objL->getCodEvento(), [42,44,50,73])) {
                        echo "<h4>Projetos anteriores:</h4>";
                        $listouAlgumEventoInscrito = 1;
                        $objInscritoL = new Trabalho(); 
                        $objInscritoL->setCodInscrito($objL->getCodInscrito()); 
                        $objInscritoL->setCodEvento($objL->getCodEvento()); 
                        $objInscritoL->carregarListaTrabalhosByIdInscrito();
                        $listaTrabalhos = $objInscritoL->getListaTrabalhos();
                        foreach ($listaTrabalhos as $objT) {
                            $tituloDocumento = 'Contrato - '. $objL->getTitulo();
                            if($objT->getTipoDocumento()){
                                $tituloDocumento = $objT->getTipoDocumento();
                            }
                            if ($objT->getPeriodoReferencia()) {
                                $tituloDocumento .= ' - ' . $objT->getPeriodoReferencia();
                            }
                            echo '<li style="font-size: 12px;line-height: 0.8;margin-top:10px;">';
                                echo '<a href="javascript:void(0);" '
                                     . 'onclick="visualizarDocAvaliado(\'' . $objT->getArquivoGerado() . '\','.$objT->getCodEvento().');">'
                                 .$tituloDocumento.'</a>';
                                //echo $tituloDocumento;
                            echo "</li>";
                        } 
                    }
                }
            }
            echo "</ul>";
            if ($listouAlgumEventoInscrito == 0) {
                echo "<div class='alert alert-warning'>Nenhum projeto encontrado para este candidato.</div>";
            }
        } catch (Exception $exc) {
            echo "<div class='alert alert-danger'>" . $exc->getMessage() . "</div>";
        }   
    }

    public function carregarCriterios($fws_, $objAI, $debug = false)
    {
 /*
        echo '<div class="form-group col-sm-12" style="padding-left: 0px;padding-top:10px">';
        echo $objEvConf->getTrabComplementoFormText();
        echo '</div>';
        */
        
        // Dados fornecidos
        $respostas = json_decode($objAI->getCriteriosRespostas(), true);
        $peso   = json_decode($objAI->getCriteriosPesos(), true);
        $pontos = json_decode($objAI->getCriteriosPontos(), true);
        $total = $objAI->getPontosTotal();
                                    
                                    // Criar a tabela HTML
        // Criar a tabela HTML
        echo '<label>';
        echo 'Gabarito de Respostas de: ';
        echo $objAI->getCodInscrito().' - ';
        echo $objAI->getNomeInscrito();
        if ($objAI->getInstitucional() == 1) {
            echo '<br><b>Inscrição Pessoa Jurídica</b>';
        }else{
            echo "<br><b>Inscrição Pessoa Física</b>";
        }
        echo '</label>'; 

        echo '<table border="1" style="border-collapse: separate; border-spacing: 5px; width: 50%; margin: 0 auto;">';
        echo '<thead>';
        echo '<tr style="background-color: #004080; color: #fff;">'; // Cabeçalho com fundo azul escuro
        echo '<th style="text-align: center;">Código do Critério</th>';
        echo '<th style="text-align: center;">Resposta</th>';
        echo '<th style="text-align: center;">Peso</th>';
        echo '<th style="text-align: center;">Pontos Calculados</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        // Iterar pelos arrays e montar as linhas
        $zebra = false; // Para alternar entre cores
        foreach ($respostas as $key => $resposta) {
            // Obter índice baseando-se na chave
            $indice = str_replace('criterio_', '', $key);

            // Verificar se os campos estão preenchidos
            $respostaPreenchida = !empty($resposta);
            $pesoPreenchido = !empty($peso['peso_' . $indice]);
            $pontosPreenchido = !empty($pontos['pontos_' . $indice]);

            // Alternar cor de fundo (zebra)
            $backgroundColor = $zebra ? '#f2f2f2' : '#ffffff';
            $zebra = !$zebra;

            // Exibir linha
            echo '<tr style="background-color: ' . $backgroundColor . ';">';
            echo '<td style="text-align: center;">Critério ' . $indice . '</td>';
            echo '<td style="text-align: center; ' . ($respostaPreenchida ? 'font-weight: bold; color: #004080;' : 'color: #ccc;') . '">' . htmlspecialchars($resposta) . '</td>';
            echo '<td style="text-align: center; ' . ($pesoPreenchido ? 'font-weight: bold; color: #004080;' : 'color: #ccc;') . '">' . htmlspecialchars($peso['peso_' . $indice]) . '</td>';
            echo '<td style="text-align: center; ' . ($pontosPreenchido ? 'font-weight: bold; color: #004080;' : 'color: #ccc;') . '">' . htmlspecialchars($pontos['pontos_' . $indice]) . '</td>';
            echo '</tr>';
        }

        echo '</tbody>';

        // Exibir o total abaixo da tabela
        echo '<tfoot>';
        echo '<tr style="background-color: #004080; color: #fff;">';
        echo '<td colspan="3" style="text-align: right; font-weight: bold;">Total informado pelo candidato(a):</td>';
        echo '<td style="text-align: center; font-weight: bold;">' . number_format($total, 2, ',', '.') . '</td>';
        echo '</tr>';
        echo '</tfoot>';

        echo '</table>';
    }

    public function carregarAvaliacaoInscrito($fws_, $debug = false)
    {
        $obj = new AvaliacaoInscrito();
        $obj->setCodAvaliacao($fws_['codAvaliacao']);
        $obj->getAvaliacaoInscritoById($debug);
        return $obj;
    }

    public function carregarAvaliacaoInscritoByIdInscrito($fws_, $debug = false)
    {
        $obj = new AvaliacaoInscrito();
        if ($fws_['codInscrito'] != '') {
            $obj->setCodInscrito($fws_['codInscrito']);
            if (!empty($fws_['ordemEtapa'])) {
                $obj->setOrdemEtapa($fws_['ordemEtapa']);
            }
            $obj->getAvaliacaoInscritoByIdInscrito($debug);
        }
        return $obj;
    }

    public function carregarRecursosSolicitadosByIdInscrito($fws_, $debug = false){
        $obj = new AvaliacaoInscrito();
        if ($fws_['codInscrito'] != '') {
            $obj->setCodInscrito($fws_['codInscrito']);
            $obj->setCodEvento(COD_EVENTO);
            $obj->getListaRecursosSolicitadosByIdInscrito($debug);
        }
        return $obj;
    }

    public function carregarTabelaRecursos($fws_, $debug = false, $objAI = null){

        // Para o evento 129 (Pró-REDE 2025) Fase 1, o recurso é texto único gravado em
        // tab_trabalhos_avaliacao_inscricao.recurso_texto (não em tab_trabalhos.recurso_trabalho).
        // Mostra a caixa readonly com o texto do candidato.
        if (defined('COD_EVENTO') && COD_EVENTO == 129) {
            // Se $objAI não veio (chamadas legadas), recarrega a Fase 1 direto do DAO.
            if (!$objAI && !empty($fws_['codInscrito'])) {
                $objAI = new AvaliacaoInscrito();
                $objAI->setCodInscrito($fws_['codInscrito']);
                $objAI->carregarAvaliacaoFase1ByIdInscrito($debug);
            }
            $texto = $objAI ? (string)$objAI->getRecursoTexto() : '';
            ?>
            <textarea readonly rows="8" class="form-control" style="background-color: #f2f2f2; cursor: not-allowed; white-space: pre-wrap;"><?php echo htmlspecialchars($texto) ?></textarea>
            <?php
            if ($texto === '') {
                echo '<div class="alert alert-warning" style="margin-top:5px;">Nenhum texto de recurso foi registrado por este candidato.</div>';
            }
            return;
        }

        echo '<table border="1" style="border-collapse: collapse; width: 100%; background-color: #f9f9f9; border: 1px solid #ddd;">';
        echo '<thead>';
        echo '<tr style="background-color: #f1f1f1; color: #333;">';
        echo '<th style="padding: 10px; text-align: left;">Cod Recurso</th>';
        echo '<th style="padding: 10px; text-align: left;">Recurso</th>';
        echo '<th style="padding: 10px; text-align: left;">Arquivo</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        $obj = $this->carregarRecursosSolicitadosByIdInscrito($fws_, $debug);
        foreach ($obj->getListaRecursosSolicitados() as $objL) {
            echo '<tr style="background-color: #fff; border-bottom: 1px solid #ddd;">';
            echo '<td style="padding: 8px;">' . htmlspecialchars($objL->getCodTrabalho()) . '</td>';
            echo '<td style="padding: 8px;">' . nl2br(htmlspecialchars($objL->getRecursoTrabalho())) . '</td>';
            echo '<td style="padding: 8px;">';
            if (!empty($objL->getArquivoGerado())) {
                ?>
                <a id="btnVisualizarDocumentoAvaliado-<?php echo $objL->getArquivoGerado() ?>" title="Visualizar Documento" href="javascript:void(0);" style="color: #4CAF50; font-weight: bold; display: inline-block;" onclick="javascript:visualizarDocAvaliado('<?php echo $objL->getArquivoGerado() ?>');"> Visualizar Documento
                    <img src='<?php echo  WB_URL_IMG ?>/pdf.gif' alt='Visualizar Documento' width="20px">
                </a>
                <?php
            } else {
                echo 'Sem anexo';
            }
            echo '</td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
    }

    //ESSE É O DO ZAQUEU
    public function gerarJsonAvaliacao($obj, $debug = false)
    {
        $json = array();
        $json['avaliacaoJson'] = $obj->getAvaliacaoJson();
        $json['codAvaliacao'] = $obj->getCodAvaliacao();
        $json['codInscrito']  = $obj->getCodInscrito();
        $json['nomeInscrito'] = $obj->getNomeInscrito();
        $json['sitPontos']    = $obj->getSitPontos();
        $json['pontosTotalConfirmado'] = $obj->getPontosTotalConfirmado();
        $json['pontosTotalAposRecurso'] = $obj->getPontosTotalAposRecurso();
        $json['classificacao'] = $obj->getClassificacao();
        $json['classificacaoStatus'] = $obj->getClassificacaoStatus();
        $json['recursoTexto'] = tratarJson($obj->getRecursoTexto());
        $json['recursoParecer'] = tratarJson($obj->getRecursoParecer());
        $json['parecerAvaliador'] = tratarJson($obj->getParecerAvaliador());
        $json['recursoStatus'] = $obj->getRecursoStatus();
        $json['jaInscrito']   = $obj->getJaInscrito();
        $json['arquivoGerado']   = $obj->getArquivoGerado();
        $json['statusMessage']   =  $obj->getNomeInscrito() != '' ? '1' : '0';
        $json['message']   =  $obj->getNomeInscrito() != '' ? 'Carregado' : 'Não carregado';
        return json_encode($json);
    }

    public function editarAvaliacaoInscrito($fws_, $debug = false)
    {
        $obj = $this->carregarAvaliacaoInscrito($fws_, $debug);
        echo $this->gerarJsonAvaliacao($obj, $debug);
        exit();
    }

    public function editarAvaliacaoInscritoByIdInscrito($fws_, $debug = false)
    {
        $obj = $this->carregarAvaliacaoInscritoByIdInscrito($fws_, $debug);
        echo $this->gerarJsonAvaliacao($obj, $debug);
        exit();
    }

    public function salvarAvaliacaoInscrito($fws_, $debug = false)
    {
        $ok = false; // Inicialize $ok aqui
        $e = null;   // Inicialize $e aqui

        try {
            $objAvaliacao = $this->carregarAvaliacaoInscritoByIdInscrito($fws_);
            if ($objAvaliacao->getCodAvaliacao() != '' && !$permitirAlteracaoAvaliacao) {
                echo '{"statusMessage":"","message":"Esta avaliação já foi realizada e não pode ser alterada.","codAvaliacao":"'.$objAvaliacao->getCodAvaliacao().'"}';
                return;
            }
            if ($objAvaliacao->getCodAvaliacao() != '') {
                $fws_['codAvaliacao'] = $objAvaliacao->getCodAvaliacao();
            } else {
                if ($fws_['novoCodAvaliacao'] == 0)
                    $fws_['codAvaliacao'] = NULL;
            }
            $isForm = true;
            $obj = $this->carregarAvaliacaoInscrito($fws_);
            $obj->setAll($fws_, $isForm);
            $obj->setAvaliacaoJson($this->buildAvaliacaoPesJsonForDb($fws_));
            $obj->salvar($debug);

            // Registrar decisao no historico (HOMOLOGADO/NAO HOMOLOGADO).
            // Usa cod_etapa do registro recem-salvo. Se ainda nao tiver cod_etapa
            // (eventos legados pre-fases), tenta resolver via ordem 1.
            $codEtapaHist = (int)$obj->getCodEtapa();
            if ($codEtapaHist <= 0) {
                $objEtTmp = new Etapa();
                $codEtapaHist = (int)$objEtTmp->getEtapaByOrdem(1, false);
            }
            if ($codEtapaHist > 0) {
                $objHistAC = new HistoricoAvaliacaoInscritoAC();
                $objHistAC->registrarDecisao(
                    $obj->getCodInscrito(),
                    $codEtapaHist,
                    $obj->getClassificacaoStatus(),
                    $debug
                );
            }
            $ok = true; // Defina $ok como true em caso de execução bem-sucedida
        } catch (OkException $e) {
            $ok = true;
        } catch (Exception $ex) {
            // Atualize $e com a exceção capturada
            $e = $ex;
            // $ok já está definido como false por padrão
        }

        echo '{
        "statusMessage":"' . $ok . '",
        "message":      "' . ($e ? $e->getMessage() : '') . '",
        "codAvaliacao":"' . ($obj ? $obj->getCodAvaliacao() : '') . '"
        }';
    }

    public function excluirAvaliacaoInscrito($fws_, $debug = false)
    {
        try {
            $obj = new AvaliacaoInscrito();
            $obj->setCodAvaliacao($fws_['codAvaliacao']);

            $obj->excluir();
        } catch (OKException $e) {
            $ok = true;
        } catch (Exception $e) {
            $ok = false;
        }

        echo '{
        "statusMessage":"' . $ok . '",
        "message":      "' . $e->getMessage() . '",
        "codAvaliacao":"' . $fws_['codAvaliacao'] . '"
        }';
    }

    public function carregarCabecalhoTrabelaAvaliacaoInscrito($fws_, $debug = false)
    {
        ?>
        <th width="40"  style="white-space:normal; overflow:visible; text-overflow:clip; text-align:center;">#</th>
        <th width="85"  style="white-space:normal; overflow:visible; text-overflow:clip;">A&ccedil;&otilde;es</th>
        <th width="110" data-col="2" style="white-space:normal; overflow:visible; text-overflow:clip; cursor:pointer;">C&oacute;digo Inscrito <span class="sort-arrow">&#8597;</span></th>
        <th             data-col="3" style="white-space:normal; overflow:visible; text-overflow:clip; cursor:pointer;">Nome <span class="sort-arrow">&#8597;</span></th>
        <th width="100" data-col="4" style="white-space:normal; overflow:visible; text-overflow:clip; cursor:pointer;">Pontos Informados <span class="sort-arrow">&#8597;</span></th>
        <th width="100" data-col="5" style="white-space:normal; overflow:visible; text-overflow:clip; cursor:pointer;">Pontos Total Confirmados <span class="sort-arrow">&#8597;</span></th>
        <th width="110" data-col="6" style="white-space:normal; overflow:visible; text-overflow:clip; cursor:pointer;">Classif. Status <span class="sort-arrow">&#8597;</span></th>
        <th width="100" data-col="7" style="white-space:normal; overflow:visible; text-overflow:clip; cursor:pointer;">Solicitou Recurso <span class="sort-arrow">&#8597;</span></th>
        <th width="100" data-col="8" style="white-space:normal; overflow:visible; text-overflow:clip; cursor:pointer;">Pontos Ap&oacute;s Recurso <span class="sort-arrow">&#8597;</span></th>
        <th width="130" data-col="9" style="white-space:normal; overflow:visible; text-overflow:clip; cursor:pointer;">Classif. Status Ap&oacute;s Recurso <span class="sort-arrow">&#8597;</span></th>
        <?php
    }


    public function carregarTabelaAvaliacaoInscrito($fws_, $debug = false)
    {
        $obj = new AvaliacaoInscrito();
        $filtro = isset($fws_['filtroAvaliacao']) ? $fws_['filtroAvaliacao'] : 'todos';
        $condicoes = array('filtro' => $filtro);
        if (!empty($fws_['ordemEtapa'])) {
            $condicoes['ordemEtapa'] = (int)$fws_['ordemEtapa'];
        }
        try {
            $lista = $obj->getListaAvaliacaoInscritosByIdEvento($condicoes, $debug);
            $ii = 0;
            $totalJaInscrito = 0;
            $codInscritoAnt = 0;
            foreach ($lista as $objL) {
                if ($codInscritoAnt == $objL->getCodInscrito()) {
                    continue;
                }
                $codInscritoAnt = $objL->getCodInscrito();
                $ii++;
                ?>
                <tr id="<?php echo $objL->getCodAvaliacao() ?>">
                    <td style="text-align:center; color:#888; font-size:12px;"><?php echo $ii ?></td>
                    <td>
                        <button class="btn btn-sm btn-success btn-acessar-avaliacao-inscrito" style="margin-right: 5px;" type="button" onclick="acessarAvaliacaoInscrito(<?php echo  $objL->getCodInscrito() ?>)">
                            Consultar
                        </button>
                        </td>
                        <td>                        
                        <?php
                        if (FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_LOGAR_COMO, 5, false, false)) {
                            if ($objL->getJaInscrito()) {
                                $totalJaInscrito++;
                                ?>
                                <a title='Logar Como' target="_blank" href='index.php?content=view/inscricao/inscricao_congresso_ok.php&acao=logarComo&cpf=<?php echo $objL->getCpf() ?>&cod_inscrito=<?php echo $objL->getCodInscrito() ?>&cod_evento=<?php echo COD_EVENTO ?>'>
                                    <img src='<?php echo WB_URL_IMG ?>/icone_preview.gif' border='0'></a>
                                <?php
                            }
                        }
                        ?>
                        <?php echo $objL->getCodInscrito() ?></td>
                    <td>
                        <?php echo textoMaiusculoMinusculo($objL->getNomeInscrito()) ?>
                        <?php CarregarBotoesAC::btnMostrarParecerAvaliacao($fws_, $objL, $debug); ?>                          
                    </td>
                    <td><?php echo number_format($objL->getSitPontos(), 2) ?></td>
                    <td><?php echo $objL->getPontosTotalConfirmado() ?></td>
                    <!-- <td><?php echo $objL->getDescClassificacao() ?></td> -->
                    <td>
                        <?php
                        if ($objL->getClassificacaoStatus() == 'H') {
                            $corClassificacao = 'green';
                        }else if ($objL->getClassificacaoStatus() == 'I') {
                            $corClassificacao = 'red';
                        }else{
                            $corClassificacao = 'orange';
                        }
                        ?>
                        <span style="color: <?php echo $corClassificacao; ?>;"><?php echo $objL->getDescClassificacaoStatus(); ?></span>
                </td>
                <td><?php
                     if ($objL->getTotalRecursos()>0) {
                        echo "Sim: ". $objL->getTotalRecursos();
                     }
                    ?>
                </td>
                <td><?php echo $objL->getPontosTotalAposRecurso() ?></td>

                <td>
                    <?php
                    if ($objL->getClassificacaoStatusAposRecurso() == 'H') {
                        $corClassificacaoAposRecurso = 'green';
                    }else if ($objL->getClassificacaoStatusAposRecurso() == 'I') {
                        $corClassificacaoAposRecurso = 'red';
                    }else{
                        $corClassificacaoAposRecurso = 'orange';
                    }
                    ?>
                    <span style="color: <?php echo $corClassificacaoAposRecurso; ?>;">
                        <?php echo $objL->getDescClassificacaoStatusAposRecurso(); ?></span>
                </td>                
                </tr>
            <?php
            }
            ?>
            <tr class="no-sort">
                <td colspan="10" style="text-align:right; color:#999; font-size:12px; padding:8px 12px; border-top:1px solid #eee;">
                    Total: <strong><?php echo $ii ?></strong> registro<?php echo $ii === 1 ? '' : 's' ?>
                </td>
            </tr>
<?php
        } catch (Exception $e) {
            echo "<tr><td></td><td colspan=3>";
            if ($e->getMessage()) {
                echo $e->getMessage();
            } else {
                echo "Lista vazia.";
            }
            echo "</td></tr>";
        }
    }

public static function formularioAvaliacaoProRede2($fws_, $objEv, $objAI, $debug) {

    $avaliacaoJson = $objAI->getAvaliacaoJson(); // ajuste se o getter tiver outro nome
    $avaliacao = [];

    if ($avaliacaoJson) {
        $avaliacao = json_decode($avaliacaoJson, true);
    }

    $itens = $avaliacao['itens'] ?? [];
    $total = $avaliacao['total'] ?? '0.00';

    function qtdItem($itens, $k){
        return $itens[$k]['qtd'] ?? 0;
    }

    function pontItem($itens, $k){
        return $itens[$k]['pontuacao'] ?? '0.00';
    }

    ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<title>Anexo II – Avaliação PES</title>

<style>
body { font-family: Arial, sans-serif; font-size: 12px; }
h2 { font-size: 15px; margin-bottom: 5px; }
h3 { font-size: 13px; margin-top: 20px; margin-bottom: 5px; }
table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
th, td { border: 1px solid #000; padding: 4px 6px; vertical-align: top; }
th { background: #eee; font-size: 11px; text-align: center; }
.pergunta { font-size: 10.5px; color: #444; line-height: 1.2; }
input[type=number] { width: 55px; font-size: 11px; text-align: center; }
.pontos { text-align: center; font-weight: bold; font-size: 11px; }
.total { background: #003366; color: #fff; font-size: 12px; font-weight: bold; }
</style>

<script>
function showAviso(msg, ms = 2500){
    const el = document.getElementById('toastAviso');
    if (!el) return;

    el.textContent = msg;
    el.style.display = 'block';

    clearTimeout(el._t);
    el._t = setTimeout(() => { el.style.display = 'none'; }, ms);
}

function getQtdByName(name){
    const el = document.querySelector(`input[name="${name}"]`);
    return { el, qtd: parseFloat(el?.value) || 0 };
}

function zeraLinha(el){
    if (!el) return;
    el.value = 0;

    const pontosTd = el.closest('tr')?.querySelector('.pontos');
    if (pontosTd) pontosTd.innerText = '0';

    // zera também hidden da pontuação desse item
    setPontuacaoLinhaHidden(el, 0);
}

function labelItem(name){
    const m = String(name || '').match(/^qtd\[(.+)\]$/);
    return m ? m[1] : String(name || '');
}

/* ===== NOVO: seta hidden pontuacao[item] ===== */
function setPontuacaoLinhaHidden(qtdInputEl, totalLinha){
    const m = (qtdInputEl?.name || '').match(/^qtd\[(.+)\]$/);
    if (!m) return;

    const item = m[1];
    const hidden = document.getElementById('pontuacao_' + item.replaceAll('.', '_'));
    if (hidden) hidden.value = Number(totalLinha).toFixed(2);
}

function trataConflitos(changedEl){
    const groups = [
        ['qtd[1.2.1]', 'qtd[1.2.2]'],
        ['qtd[1.4.1]', 'qtd[1.4.2]'],
    ];

    for (const [aName, bName] of groups) {
        const a = getQtdByName(aName);
        const b = getQtdByName(bName);

        if (a.qtd > 0 && b.qtd > 0) {
            const changedName = changedEl?.getAttribute('name');

            if (changedName === aName) {
                zeraLinha(b.el);
                showAviso(`Atenção Conflito: mantido ${labelItem(aName)}; zerado ${labelItem(bName)}.`);
            } else if (changedName === bName) {
                zeraLinha(a.el);
                showAviso(`Atenção Conflito: mantido ${labelItem(bName)}; zerado ${labelItem(aName)}.`);
            } else {
                zeraLinha(a.el);
                showAviso(`Atenção Conflito: mantido ${labelItem(bName)}; zerado ${labelItem(aName)}.`);
            }
        }
    }
}

function calcLinha(el){
    trataConflitos(el);

    let qtd   = parseFloat(el.value) || 0;
    let ponto = parseFloat(el.dataset.ponto);
    let max   = parseFloat(el.dataset.max);

    let total = qtd * ponto;
    if (total > max) total = max;

    el.closest('tr').querySelector('.pontos').innerText = total.toFixed(2);

    // grava o valor calculado (capado) no hidden do item
    setPontuacaoLinhaHidden(el, total);

    calcTotal();
}

function calcTotal(){
    let soma = 0;
    document.querySelectorAll('.pontos').forEach(td=>{
        soma += parseFloat(td.innerText) || 0;
    });

    const totalCalculado = soma.toFixed(2);

    document.getElementById('total_comissao').innerText = totalCalculado;
    document.getElementById('pontosTotalConfirmado').value = totalCalculado;
}
</script>
</head>

<body>

<div id="toastAviso" style="
  position: fixed;
  left: 50%;
  top: 18%;
  transform: translateX(-50%);
  max-width: 520px;
  width: calc(100% - 24px);
  padding: 10px 12px;
  border: 1px solid #ff0000;
  background: #fff;
  color: #ff0000;
  font-size: 12px;
  box-shadow: 0 10px 26px rgba(0,0,0,.20);
  border-radius: 10px;
  display: none;
  z-index: 9999;
  text-align: center;
"></div>

<input type="hidden" id="pontuacao_1_1" name="pontuacao[1.1]" value="<?php echo pontItem($itens,'1.1'); ?>">
<input type="hidden" id="pontuacao_1_2_1" name="pontuacao[1.2.1]" value="<?php echo pontItem($itens,'1.2.1'); ?>">
<input type="hidden" id="pontuacao_1_2_2" name="pontuacao[1.2.2]" value="<?php echo pontItem($itens,'1.2.2'); ?>">
<input type="hidden" id="pontuacao_1_3" name="pontuacao[1.3]" value="<?php echo pontItem($itens,'1.3'); ?>">
<input type="hidden" id="pontuacao_1_4_1" name="pontuacao[1.4.1]" value="<?php echo pontItem($itens,'1.4.1'); ?>">
<input type="hidden" id="pontuacao_1_4_2" name="pontuacao[1.4.2]" value="<?php echo pontItem($itens,'1.4.2'); ?>">
<input type="hidden" id="pontuacao_1_5" name="pontuacao[1.5]" value="<?php echo pontItem($itens,'1.5'); ?>">
<input type="hidden" id="pontuacao_1_6" name="pontuacao[1.6]" value="<?php echo pontItem($itens,'1.6'); ?>">
<input type="hidden" id="pontuacao_1_7_1" name="pontuacao[1.7.1]" value="<?php echo pontItem($itens,'1.7.1'); ?>">
<input type="hidden" id="pontuacao_1_7_2" name="pontuacao[1.7.2]" value="<?php echo pontItem($itens,'1.7.2'); ?>">
<input type="hidden" id="pontuacao_1_8_1" name="pontuacao[1.8.1]" value="<?php echo pontItem($itens,'1.8.1'); ?>">
<input type="hidden" id="pontuacao_1_8_2" name="pontuacao[1.8.2]" value="<?php echo pontItem($itens,'1.8.2'); ?>">
<input type="hidden" id="pontuacao_1_9_1" name="pontuacao[1.9.1]" value="<?php echo pontItem($itens,'1.9.1'); ?>">
<input type="hidden" id="pontuacao_1_9_2" name="pontuacao[1.9.2]" value="<?php echo pontItem($itens,'1.9.2'); ?>">

<input type="hidden" id="pontuacao_2_1" name="pontuacao[2.1]" value="<?php echo pontItem($itens,'2.1'); ?>">
<input type="hidden" id="pontuacao_2_2" name="pontuacao[2.2]" value="<?php echo pontItem($itens,'2.2'); ?>">

<input type="hidden" id="pontuacao_3_1" name="pontuacao[3.1]" value="<?php echo pontItem($itens,'3.1'); ?>">
<input type="hidden" id="pontuacao_3_2" name="pontuacao[3.2]" value="<?php echo pontItem($itens,'3.2'); ?>">
<input type="hidden" id="pontuacao_3_3" name="pontuacao[3.3]" value="<?php echo pontItem($itens,'3.3'); ?>">
<input type="hidden" id="pontuacao_3_4" name="pontuacao[3.4]" value="<?php echo pontItem($itens,'3.4'); ?>">

<h2>ANEXO II – Profissional Especializado de Suporte (PES)</h2>
<h3>1. FORMAÇÃO</h3>

<table>
<colgroup>
    <col style="width:70px">
    <col style="width:auto">
    <col style="width:90px">
    <col style="width:70px">
    <col style="width:70px">
    <col style="width:90px">
</colgroup>

<tr>
<th>Item</th><th>Descrição</th><th>Pontos</th><th>Máx.</th><th>Qtd.</th><th>Pontuação</th>
</tr>

<tr>
<td>1.1</td>
<td>Ensino Médio com magistério</td>
<td align="center">10,0</td>
<td align="center">10</td>
<td align="center"><input name="qtd[1.1]" type="number" min="0" max="1" data-ponto="10" data-max="10" oninput="calcLinha(this)" value="<?php echo qtdItem($itens,'1.1'); ?>"></td>
<td class="pontos"><?php echo pontItem($itens,'1.1'); ?></td>
</tr>

<tr>
<td>1.2.1</td>
<td>Cursando curso técnico de enfermagem</td>
<td align="center">3,0</td>
<td align="center">3</td>
<td align="center"><input name="qtd[1.2.1]" type="number" min="0" max="1" data-ponto="3" data-max="3" oninput="calcLinha(this)" value="<?php echo qtdItem($itens,'1.2.1'); ?>"></td>
<td class="pontos"><?php echo pontItem($itens,'1.2.1'); ?></td>
</tr>

<tr>
<td>1.2.2</td>
<td>Curso técnico de enfermagem (não pontuar com 1.2.1)</td>
<td align="center">5,0</td>
<td align="center">5</td>
<td align="center"><input name="qtd[1.2.2]" type="number" min="0" max="1" data-ponto="5" data-max="5" oninput="calcLinha(this)" value="<?php echo qtdItem($itens,'1.2.2'); ?>"></td>
<td class="pontos"><?php echo pontItem($itens,'1.2.2'); ?></td>
</tr>

<tr>
<td>1.3</td>
<td>Cursando graduação (Pedagogia, EF, Enfermagem, etc.)</td>
<td align="center">5,0</td>
<td align="center">5</td>
<td align="center"><input name="qtd[1.3]" type="number" min="0" max="1" data-ponto="5" data-max="5" oninput="calcLinha(this)" value="<?php echo qtdItem($itens,'1.3'); ?>"></td>
<td class="pontos"><?php echo pontItem($itens,'1.3'); ?></td>
</tr>

<tr>
<td>1.4.1</td>
<td>Graduação completa (áreas listadas)</td>
<td align="center">15,0</td>
<td align="center">15</td>
<td align="center"><input name="qtd[1.4.1]" type="number" min="0" max="1" data-ponto="15" data-max="15" oninput="calcLinha(this)" value="<?php echo qtdItem($itens,'1.4.1'); ?>"></td>
<td class="pontos"><?php echo pontItem($itens,'1.4.1'); ?></td>
</tr>

<tr>
<td>1.4.2</td>
<td>Graduação com licenciatura (não pontuar com 1.4.1)</td>
<td align="center">15,0</td>
<td align="center">15</td>
<td align="center"><input name="qtd[1.4.2]" type="number" min="0" max="1" data-ponto="15" data-max="15" oninput="calcLinha(this)" value="<?php echo qtdItem($itens,'1.4.2'); ?>"></td>
<td class="pontos"><?php echo pontItem($itens,'1.4.2'); ?></td>
</tr>

<tr>
<td>1.5</td>
<td>Curso complementar até 20h</td>
<td align="center">2,0</td>
<td align="center">6</td>
<td align="center"><input name="qtd[1.5]" type="number" min="0" max="3" data-ponto="2" data-max="6" oninput="calcLinha(this)" value="<?php echo qtdItem($itens,'1.5'); ?>"></td>
<td class="pontos"><?php echo pontItem($itens,'1.5'); ?></td>
</tr>

<tr>
<td>1.6</td>
<td>Curso complementar &gt; 20h</td>
<td align="center">4,0</td>
<td align="center">12</td>
<td align="center"><input name="qtd[1.6]" type="number" min="0" max="3" data-ponto="4" data-max="12" oninput="calcLinha(this)" value="<?php echo qtdItem($itens,'1.6'); ?>"></td>
<td class="pontos"><?php echo pontItem($itens,'1.6'); ?></td>
</tr>

<tr>
<td>1.7.1</td>
<td>Cursando especialização</td>
<td align="center">6,0</td>
<td align="center">6</td>
<td align="center"><input name="qtd[1.7.1]" type="number" min="0" max="1" data-ponto="6" data-max="6" oninput="calcLinha(this)" value="<?php echo qtdItem($itens,'1.7.1'); ?>"></td>
<td class="pontos"><?php echo pontItem($itens,'1.7.1'); ?></td>
</tr>

<tr>
<td>1.7.2</td>
<td>Especialização concluída</td>
<td align="center">10,0</td>
<td align="center">50</td>
<td align="center"><input name="qtd[1.7.2]" type="number" min="0" max="5" data-ponto="10" data-max="50" oninput="calcLinha(this)" value="<?php echo qtdItem($itens,'1.7.2'); ?>"></td>
<td class="pontos"><?php echo pontItem($itens,'1.7.2'); ?></td>
</tr>

<tr>
<td>1.8.1</td>
<td>Cursando mestrado</td>
<td align="center">15,0</td>
<td align="center">15</td>
<td align="center"><input name="qtd[1.8.1]" type="number" min="0" max="1" data-ponto="15" data-max="15" oninput="calcLinha(this)" value="<?php echo qtdItem($itens,'1.8.1'); ?>"></td>
<td class="pontos"><?php echo pontItem($itens,'1.8.1'); ?></td>
</tr>

<tr>
<td>1.8.2</td>
<td>Mestrado concluído</td>
<td align="center">20,0</td>
<td align="center">20</td>
<td align="center"><input name="qtd[1.8.2]" type="number" min="0" max="1" data-ponto="20" data-max="20" oninput="calcLinha(this)" value="<?php echo qtdItem($itens,'1.8.2'); ?>"></td>
<td class="pontos"><?php echo pontItem($itens,'1.8.2'); ?></td>
</tr>

<tr>
<td>1.9.1</td>
<td>Cursando doutorado</td>
<td align="center">25,0</td>
<td align="center">25</td>
<td align="center"><input name="qtd[1.9.1]" type="number" min="0" max="1" data-ponto="25" data-max="25" oninput="calcLinha(this)" value="<?php echo qtdItem($itens,'1.9.1'); ?>"></td>
<td class="pontos"><?php echo pontItem($itens,'1.9.1'); ?></td>
</tr>

<tr>
<td>1.9.2</td>
<td>Doutorado concluído</td>
<td align="center">30,0</td>
<td align="center">30</td>
<td align="center"><input name="qtd[1.9.2]" type="number" min="0" max="1" data-ponto="30" data-max="30" oninput="calcLinha(this)" value="<?php echo qtdItem($itens,'1.9.2'); ?>"></td>
<td class="pontos"><?php echo pontItem($itens,'1.9.2'); ?></td>
</tr>
</table>

<h3>2. FORMAÇÃO ACADÊMICA</h3>

<table>
<colgroup>
    <col style="width:70px">
    <col style="width:auto">
    <col style="width:90px">
    <col style="width:70px">
    <col style="width:70px">
    <col style="width:90px">
</colgroup>

<tr><th>Item</th><th>Descrição</th><th>Pontos</th><th>Máx.</th><th>Qtd.</th><th>Pontuação</th></tr>

<tr>
<td>2.1</td>
<td>Projetos de ensino/pesquisa/extensão</td>
<td align="center">0,5/mês</td>
<td align="center">30</td>
<td align="center"><input name="qtd[2.1]" type="number" min="0" data-ponto="0.5" data-max="30" oninput="calcLinha(this)" value="<?php echo qtdItem($itens,'2.1'); ?>"></td>
<td class="pontos"><?php echo pontItem($itens,'2.1'); ?></td>
</tr>

<tr>
<td>2.2</td>
<td>Grupos de estudo</td>
<td align="center">0,1/hora</td>
<td align="center">15</td>
<td align="center"><input name="qtd[2.2]" type="number" min="0" data-ponto="0.1" data-max="15" oninput="calcLinha(this)" value="<?php echo qtdItem($itens,'2.2'); ?>"></td>
<td class="pontos"><?php echo pontItem($itens,'2.2'); ?></td>
</tr>
</table>

<h3>3. EXPERIÊNCIA PROFISSIONAL</h3>

<table>
<colgroup>
    <col style="width:70px">
    <col style="width:auto">
    <col style="width:90px">
    <col style="width:70px">
    <col style="width:70px">
    <col style="width:90px">
</colgroup>

<tr><th>Item</th><th>Descrição</th><th>Pontos</th><th>Máx.</th><th>Qtd.</th><th>Pontuação</th></tr>

<tr>
<td>3.1</td>
<td>Exercício profissional no ensino (não docente)</td>
<td align="center">0,5/mês</td>
<td align="center">30</td>
<td align="center"><input name="qtd[3.1]" type="number" min="0" data-ponto="0.5" data-max="30" oninput="calcLinha(this)" value="<?php echo qtdItem($itens,'3.1'); ?>"></td>
<td class="pontos"><?php echo pontItem($itens,'3.1'); ?></td>
</tr>

<tr>
<td>3.2</td>
<td>Experiência em educação especial (não docente)</td>
<td align="center">0,8/mês</td>
<td align="center">48</td>
<td align="center"><input name="qtd[3.2]" type="number" min="0" data-ponto="0.8" data-max="48" oninput="calcLinha(this)" value="<?php echo qtdItem($itens,'3.2'); ?>"></td>
<td class="pontos"><?php echo pontItem($itens,'3.2'); ?></td>
</tr>

<tr>
<td>3.3</td>
<td>Atividades como docente</td>
<td align="center">1,0/mês</td>
<td align="center">60</td>
<td align="center"><input name="qtd[3.3]" type="number" min="0" data-ponto="1" data-max="60" oninput="calcLinha(this)" value="<?php echo qtdItem($itens,'3.3'); ?>"></td>
<td class="pontos"><?php echo pontItem($itens,'3.3'); ?></td>
</tr>

<tr>
<td>3.4</td>
<td>Atuação como AT (clínica infantil)</td>
<td align="center">1,0/mês</td>
<td align="center">60</td>
<td align="center"><input name="qtd[3.4]" type="number" min="0" data-ponto="1" data-max="60" oninput="calcLinha(this)" value="<?php echo qtdItem($itens,'3.4'); ?>"></td>
<td class="pontos"><?php echo pontItem($itens,'3.4'); ?></td>
</tr>

<tr class="total">
<td colspan="5" align="right">PONTUAÇÃO TOTAL DA COMISSÃO</td>
<td id="total_comissao"> <?php echo $objAI->getPontosTotalConfirmado() ?></td>
</tr>
</table>

</body>
</html>
    <?php
}

    public function carregarDocumentos($fws_, $objEv, $objAI, $debug = false)
    {
        ?>
        <?php if (in_array($objEv->getCodEvento(), [115, 129])) { ?>
            <?php
            $objAI->setCodEvento($objEv->getCodEvento());
            $objAI->carregarListaDocumentosByIdInscrito($debug);
            $listaDocs = $objAI->getListaDocumentos();
            if (empty($listaDocs)) {
                echo '<span style="color:red;font-weight:bold;">Nenhum documento encontrado.</span>';
            } else {
                ?>
                <style>
                    .doc-legivel-row { display:flex; flex-wrap:wrap; align-items:center; gap:10px; padding:6px 8px; border-bottom:1px dashed #e0e0e0; }
                    .doc-legivel-row .doc-link { flex:1 1 220px; min-width:220px; }
                    .doc-legivel-row .doc-leg-check { display:flex; gap:6px; align-items:center; font-size:12px; color:#a94442; }
                    .doc-legivel-row .doc-leg-check label { font-weight:600; margin:0; cursor:pointer; }
                    .doc-legivel-row .doc-leg-obs { flex:1 1 220px; min-width:180px; display:none; }
                    .doc-legivel-row .doc-leg-obs input { width:100%; padding:3px 6px; font-size:12px; border:1px solid #ccc; border-radius:3px; }
                    .doc-legivel-row .doc-leg-status { font-size:11px; color:#888; min-width:90px; text-align:right; }
                </style>
                <?php
                foreach ($listaDocs as $doc) {
                    $codTrab = (int) $doc->getCodTrabalho();
                    $legAtual = $doc->getDocumentoLegivel();
                    $isIleg = ($legAtual === '0' || $legAtual === 0);
                    $obsAtual = (string) $doc->getDocumentoLegivelObs();
                    ?>
                    <div class="doc-legivel-row" data-cod-trabalho="<?php echo $codTrab ?>">
                        <div class="doc-link">
                            <a class="show" id="btnVisualizarDocumento-<?php echo $doc->getArquivoGerado() ?>" title="Visualizar Documento" href="javascript:void(0);" style="color:green;font-weight: bold;" onclick="javascript:visualizarDocUnificado('<?php echo $doc->getArquivoGerado() ?>');">
                                <?php echo $doc->getTipoDocumento() ?> <img src='<?php echo  WB_URL_IMG ?>/pdf.gif' alt='Visualizar Documento' border='0' width="15px">
                            </a>
                        </div>
                        <div class="doc-leg-check">
                            <label><input type="checkbox" id="chkIleg_<?php echo $codTrab ?>" <?php echo $isIleg ? 'checked' : '' ?> onchange="toggleDocIlegivel(<?php echo $codTrab ?>, this.checked);"> Ilegível</label>
                        </div>
                        <div class="doc-leg-obs" style="<?php echo $isIleg ? 'display:flex;' : '' ?>">
                            <input type="text" maxlength="255" placeholder="O que está ilegível (opcional)" value="<?php echo htmlspecialchars($obsAtual, ENT_QUOTES) ?>" onblur="salvarDocLegivelObs(<?php echo $codTrab ?>, this.value);">
                        </div>
                        <div class="doc-leg-status" id="docLegStatus_<?php echo $codTrab ?>"></div>
                    </div>
                <?php
                }
                ?>
                <script>
                if (typeof window.toggleDocIlegivel !== 'function') {
                    window._salvarDocLegivelAjax = function(codTrab, ilegivel) {
                        var $row = $('.doc-legivel-row[data-cod-trabalho="'+codTrab+'"]');
                        var obs = ilegivel ? ($row.find('.doc-leg-obs input').val() || '') : '';
                        var $st = $('#docLegStatus_'+codTrab);
                        $st.text('salvando...').css('color','#888');
                        $.ajax({
                            type: 'POST',
                            url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
                            data: {
                                c: 'GerenciarAvaliacaoFase2',
                                m: 'marcarDocumentoLegivel',
                                _submit_check: '1',
                                codTrabalho: codTrab,
                                legivel: ilegivel ? '0' : '',
                                documentoLegivelObs: obs
                            },
                            dataType: 'json', cache: false,
                            success: function(data){
                                if (data && data.statusMessage == '1') {
                                    $st.text('✓ salvo').css('color','#2d6a4f');
                                } else {
                                    $st.text('erro: ' + (data && data.message ? data.message : '')).css('color','#a94442');
                                }
                            },
                            error: function(){ $st.text('erro de rede').css('color','#a94442'); }
                        });
                    };
                    window.toggleDocIlegivel = function(codTrab, ilegivel) {
                        var $row = $('.doc-legivel-row[data-cod-trabalho="'+codTrab+'"]');
                        var $obs = $row.find('.doc-leg-obs');
                        if (ilegivel) {
                            $obs.css('display','flex');
                            $obs.find('input').focus();
                        } else {
                            $obs.css('display','none');
                            $obs.find('input').val('');
                        }
                        window._salvarDocLegivelAjax(codTrab, ilegivel);
                    };
                    window.salvarDocLegivelObs = function(codTrab, obs) {
                        var $row = $('.doc-legivel-row[data-cod-trabalho="'+codTrab+'"]');
                        var ilegivel = $row.find('input[type="checkbox"]').prop('checked');
                        if (!ilegivel) { return; }
                        window._salvarDocLegivelAjax(codTrab, true);
                    };
                }
                </script>
                <?php
            }
            ?>
        <?php }else if ($objAI->getArquivoGerado()) { ?>
            <input type="hidden" name="arquivoGerado" id="arquivoGerado" value="<?php echo $objAI->getArquivoGerado() ?>" />
            <a class="show" id="btnVisualizarDocumento-<?php echo $objAI->getArquivoGerado() ?>" title="Visualizar Documento" href="javascript:void(0);" style="color:green;font-weight: bold;" onclick="javascript:visualizarDoc();">
                Visualizar Documento <img src='<?php echo  WB_URL_IMG ?>/pdf.gif' alt='Visualizar Documento' border='0' width="15px"></a>
        <?php }else{
                echo "Nenhum documento anexado.";
            } ?>
        <?php
    }

    public function descTipoDocumento($tipo)
    {
        $desc = '';
        $docsObrigatorios = [
            'Documento de identificação com foto'                 => $objLAI->getTemDocFoto(),
            'CPF'                                                 => $objLAI->getTemCpf(),
            'Título Eleitoral'                                    => $objLAI->getTemTitulo(),
            'Comprovação da qualificação mínima exigida'          => $objLAI->getTemQualificacao(),
            'Comprovante de endereço ou declaração'               => $objLAI->getTemEnderecoOuDeclaracao(),
            // 'CTPS ou outro comprovante de experiência profissional' => $objLAI->getTemCtpsOuComp(),
            // 'Currículo (descritivo e documental)'                 => $objLAI->getTemCurriculo(),
            // 'Documentos para pontuação nos critérios (Anexo II)'  => $objLAI->getTemCriterios(),
        ];
        switch ($tipo) {
            default:
                $desc = 'Documento Desconhecido';
                break;
        }
        return $desc;
    }

/**
 * Melhor pra gravar no banco: JSON.
 * - Consultável/portável (MySQL JSON / Postgres jsonb), fácil debugar
 * - serialize() é PHP-only e vira “caixa preta”
 * - array não grava sozinho (vira serialize/json de qualquer jeito)
 *
 * Esta versão:
 * - Usa as pontuações já enviadas pelo JS: $_POST['pontuacao'][item]
 * - Não salva floats “sujos”: ponto/max/pontuacao/total ficam como STRING decimal (ex: "0.1", "0.8", "2.00")
 * - Aplica conflitos e zera a pontuação do item perdedor
 */
function buildAvaliacaoPesJsonForDb(array $post): string
{
    $qtd = $post['qtd'] ?? [];
    $pont = $post['pontuacao'] ?? [];

    if (!is_array($qtd)) $qtd = [];
    if (!is_array($pont)) $pont = [];

    $nf = static function (mixed $v): float {
        $s = str_replace(',', '.', (string)$v);
        $n = (float)$s;
        return is_finite($n) ? $n : 0.0;
    };

    $fmt = static function (float $n, int $dec = 2): string {
        return number_format($n, $dec, '.', '');
    };

    // Fonte da verdade (evita depender de data-* do cliente)
    $rules = [
        '1.1'   => ['ponto' => '10',  'max' => '10'],
        '1.2.1' => ['ponto' => '3',   'max' => '3'],
        '1.2.2' => ['ponto' => '5',   'max' => '5'],
        '1.3'   => ['ponto' => '5',   'max' => '5'],
        '1.4.1' => ['ponto' => '15',  'max' => '15'],
        '1.4.2' => ['ponto' => '15',  'max' => '15'],
        '1.5'   => ['ponto' => '2',   'max' => '6'],
        '1.6'   => ['ponto' => '4',   'max' => '12'],
        '1.7.1' => ['ponto' => '6',   'max' => '6'],
        '1.7.2' => ['ponto' => '10',  'max' => '50'],
        '1.8.1' => ['ponto' => '15',  'max' => '15'],
        '1.8.2' => ['ponto' => '20',  'max' => '20'],
        '1.9.1' => ['ponto' => '25',  'max' => '25'],
        '1.9.2' => ['ponto' => '30',  'max' => '30'],
        '2.1'   => ['ponto' => '0.5', 'max' => '30'],
        '2.2'   => ['ponto' => '0.1', 'max' => '15'],
        '3.1'   => ['ponto' => '0.5', 'max' => '30'],
        '3.2'   => ['ponto' => '0.8', 'max' => '48'],
        '3.3'   => ['ponto' => '1',   'max' => '60'],
        '3.4'   => ['ponto' => '1',   'max' => '60'],
    ];

    // Conflitos (prioriza o 2º); zera qtd + pontuacao do perdedor
    if ($nf($qtd['1.2.1'] ?? 0) > 0 && $nf($qtd['1.2.2'] ?? 0) > 0) { $qtd['1.2.1'] = 0; $pont['1.2.1'] = 0; }
    if ($nf($qtd['1.4.1'] ?? 0) > 0 && $nf($qtd['1.4.2'] ?? 0) > 0) { $qtd['1.4.1'] = 0; $pont['1.4.1'] = 0; }

    $itens = [];
    $total = 0.0;

    foreach ($rules as $item => $r) {
        $q = $nf($qtd[$item] ?? 0);

        // pontuação vem do JS (já capada); servidor só normaliza/garante limites
        $ponto = $nf($r['ponto']);
        $max   = $nf($r['max']);

        $pontuacao = $nf($pont[$item] ?? 0);

        // “cinto e suspensório”: garante 0..max
        if ($pontuacao > $max) $pontuacao = $max;
        if ($pontuacao < 0) $pontuacao = 0;

        $pontuacao = round($pontuacao, 2);

        $itens[$item] = [
            'qtd' => $q,                 // qtd pode ficar numérica
            'ponto' => (string)$r['ponto'], // string limpa: "0.1"
            'max' => (string)$r['max'],     // string limpa: "48"
            'pontuacao' => $fmt($pontuacao, 2), // string com 2 casas: "2.00"
        ];

        $total += $pontuacao;
    }

    $payload = [
        'versao' => 1,
        'total' => $fmt(round($total, 2), 2), // string "2.00"
        'itens' => $itens,
        'updated_at' => gmdate('c'),
    ];

    $json = json_encode($payload, JSON_UNESCAPED_UNICODE);
    if ($json === false) {
        throw new RuntimeException('Falha ao gerar JSON: ' . json_last_error_msg());
    }

    return $json;
}


/*
USO:
$jsonParaBanco = buildAvaliacaoPesJsonForDb($_POST);
// $pdo->prepare('UPDATE tabela SET avaliacao_json = :j WHERE id = :id')->execute([':j'=>$jsonParaBanco, ':id'=>$id]);
*/

}

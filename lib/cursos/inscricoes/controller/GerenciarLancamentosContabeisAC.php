<?php

class GerenciarLancamentosContabeisAC {

    private $conex;

    function __construct() {
        FWA_exigirPermissao([enumSistema::SIS_GERENCIAR_ADM_GERAL, enumSistema::SIS_CONTROLE_FINANCEIRO]);
        ConexaoDB::getInstance();
        $this->conex = ConexaoDB::getConexaoDB();
    }

    /**
     * Data ate a qual o periodo esta fechado neste evento (ou null).
     */
    private function dataFechamento() {
        $sql = sprintf("SELECT data_fechamento FROM fin_periodo_fechamento WHERE cod_evento = %d", COD_EVENTO);
        $rs = $this->conex->Execute($sql);
        return ($rs && !$rs->EOF && $rs->fields['data_fechamento']) ? $rs->fields['data_fechamento'] : null;
    }

    /**
     * Barra a operacao se a data cai em periodo fechado. Lanca Exception, que
     * o salvar/excluir devolve como erro para a tela.
     */
    private function exigirPeriodoAberto($dataCompetencia) {
        $fech = $this->dataFechamento();
        if (!$fech || !$dataCompetencia) {
            return;
        }
        if (substr($dataCompetencia, 0, 10) <= $fech) {
            throw new Exception('Periodo fechado ate ' . date('d/m/Y', strtotime($fech))
                . '. Lancamentos ate essa data nao podem ser criados, alterados nem excluidos.'
                . ' Reabra o periodo se precisar corrigir.');
        }
    }

    /**
     * Grava/atualiza a data de fechamento do evento (avancar ou reabrir).
     */
    public function definirFechamento($fws_, $debug = false) {
        $ok = false;
        $msg = '';
        try {
            $data = trim($fws_['dataFechamento']);
            $usuario = isset($_SESSION['_cod_usuario']) ? $_SESSION['_cod_usuario'] : '';
            if ($data === '') {
                // reabrir: zera a data
                $sql = sprintf("UPDATE fin_periodo_fechamento SET data_fechamento = NULL,
                                data_registro = %s, id_registro = %s WHERE cod_evento = %d",
                    $this->conex->qstr(date('Y-m-d H:i:s')), $this->conex->qstr($usuario), COD_EVENTO);
                $this->conex->Execute($sql);
                $msg = 'Periodo reaberto.';
            } else {
                // insere ou atualiza a linha do evento
                $existe = $this->conex->GetOne(sprintf(
                    "SELECT COUNT(*) FROM fin_periodo_fechamento WHERE cod_evento = %d", COD_EVENTO));
                if ($existe > 0) {
                    $sql = sprintf("UPDATE fin_periodo_fechamento SET data_fechamento = %s,
                                    data_registro = %s, id_registro = %s WHERE cod_evento = %d",
                        $this->conex->qstr($data), $this->conex->qstr(date('Y-m-d H:i:s')),
                        $this->conex->qstr($usuario), COD_EVENTO);
                } else {
                    $sql = sprintf("INSERT INTO fin_periodo_fechamento
                                    (cod_evento, data_fechamento, data_registro, id_registro)
                                    VALUES (%d, %s, %s, %s)",
                        COD_EVENTO, $this->conex->qstr($data), $this->conex->qstr(date('Y-m-d H:i:s')),
                        $this->conex->qstr($usuario));
                }
                $this->conex->debug = $debug;
                $this->conex->Execute($sql);
                $msg = 'Periodo fechado ate ' . date('d/m/Y', strtotime($data)) . '.';
            }
            $ok = true;
        } catch (Exception $e) {
            $msg = $e->getMessage();
        }
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(array('statusMessage' => $ok, 'message' => $msg,
                               'dataFechamento' => $this->dataFechamento()));
        exit();
    }

    /**
     * Devolve a data de fechamento vigente (para a tela mostrar/preencher).
     */
    public function situacaoFechamento($fws_, $debug = false) {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(array('statusMessage' => true, 'dataFechamento' => $this->dataFechamento()));
        exit();
    }

    public function carregarLancamentosContabeis($fws_, $debug = false) {
        $obj = new LancamentosContabeis();
        $obj->setcodLancamentosContabeis($fws_['codLancamentosContabeis']);
        $obj->getLancamentosContabeisById($debug);
        return $obj;
    }

    public function editarLancamentosContabeis($fws_, $debug = false) {
        $obj = $this->carregarLancamentosContabeis($fws_,$debug);

        // Centro de custo, fonte, favorecido e documento faltavam no retorno: a
        // edicao reabria com esses campos em branco e, ao salvar, os apagava.
        // Monta com json_encode (nao com concatenacao) para escapar aspas,
        // apostrofos e quebras de linha do texto livre -- senao o JSON.parse do
        // navegador falha e a tela nao preenche NENHUM campo.
        // O banco devolve ora latin1, ora utf8 conforme o SET NAMES; json_encode
        // exige utf8 valido.
        $u = function ($s) {
            if ($s === null || $s === '') { return ''; }
            return mb_detect_encoding($s, 'UTF-8', true) ? $s : utf8_encode($s);
        };
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(array(
            'statusMessage'           => true,
            'message'                 => $u($obj->getMessage()),
            'codPlanoContas'          => $obj->getCodPlanoContas(),
            'flgLancamento'           => $obj->getFlgLancamento(),
            'codLancamentosContabeis' => $obj->getCodLancamentosContabeis(),
            'historico'               => $u($obj->getHistorico()),
            'origemLancamento'        => $obj->getOrigemLancamento(),
            'valor'                   => $obj->getValor(),
            'dataCompetencia'         => $obj->getDataCompetencia(),
            'dataFinanceira'          => $obj->getDataFinanceira(),
            'tipoLancamento'          => $obj->getTipoLancamento(),
            'conciliado'              => $obj->getConciliado(),
            'arquivoComprovante'      => $u($obj->getArquivoComprovante()),
            'codCentroCusto'          => $obj->getCodCentroCusto(),
            'codFontesRecurso'        => $obj->getCodFontesRecurso(),
            'codInscrito'             => $obj->getCodInscrito(),
            'favorecido'              => $u($obj->getFavorecido()),
            'numDocumento'            => $u($obj->getNumDocumento()),
        ));
        exit();
    }

    public function salvarLancamentosContabeis($fws_, $debug = false) {
        try {
            // if ($fws_['novocodLancamentosContabeis'] == 0) {
            //     $fws_['codLancamentosContabeis'] = NULL;
            // }
            $isForm = true;
            $obj = $this->carregarLancamentosContabeis($fws_);

            // Periodo fechado: nao aceita lancamento com data ATE o fechamento.
            // Vale para a data nova (do form) e para a data atual do lancamento
            // (impede tirar um lancamento de dentro do periodo fechado).
            $this->exigirPeriodoAberto($fws_['dataCompetencia']);
            if ($obj->getDataCompetencia()) {
                $this->exigirPeriodoAberto($obj->getDataCompetencia());
            }

            $obj->setAll($fws_, $isForm);

            // Comprovante: grava na pasta padrao do evento (WB_PATH_LOG_RPA) com o
            // helper de upload do sistema. So se veio arquivo -- e opcional, e ao
            // editar sem reenviar mantem o que ja estava.
            if (!empty($_FILES['comprovante']['name'])) {
                $obj->setArquivoComprovante($this->salvarComprovante($obj));
            }

            $obj->salvar($debug);
        } catch (OkException $e) {
            $ok = true;
        } catch (Exception $e) {
            $ok = false;
        }

        echo '{
        "statusMessage":"' . $ok . '",
        "message":      "' . tratarJson($e->getMessage()) . '",
        "codLancamentosContabeis":"' . $obj->getCodLancamentosContabeis() . '"
        }';
    }

    /**
     * Grava o comprovante enviado na pasta padrao do evento e devolve o nome do
     * arquivo (o que fica no registro). Reusa validarArquivo/WB_PATH_LOG_RPA do
     * sistema -- mesmo mecanismo dos demais anexos.
     */
    private function salvarComprovante($obj) {
        $tamanho = 1024 * 1024 * 20; // 20 MB
        $tiposPermitidos = array('pdf', 'jpg', 'jpeg', 'png');
        // validarArquivo espera o campo 'arquivo'; aqui o input e 'comprovante'
        validarArquivo(array('arquivo' => $_FILES['comprovante']), $tamanho, $tiposPermitidos);

        $ext = strtolower(pathinfo($_FILES['comprovante']['name'], PATHINFO_EXTENSION));
        // resolve o path por realpath antes do mkdir, senao o ".." vira pasta
        $pasta = realpath(dirname(WB_PATH_LOG_RPA)) . DIRECTORY_SEPARATOR . basename(WB_PATH_LOG_RPA);
        if (!is_dir($pasta)) {
            mkdir($pasta, 0755, true);
        }
        // nome estavel por lancamento; ao reenviar, sobrescreve o anterior
        $codLanc = $obj->getCodLancamentosContabeis() ? $obj->getCodLancamentosContabeis() : ('novo' . time());
        $nome = 'comprovante_' . COD_EVENTO . '_' . $codLanc . '.' . $ext;
        $destino = $pasta . DIRECTORY_SEPARATOR . $nome;

        // move_uploaded_file valida o input 'arquivo'; aqui o campo e 'comprovante'
        if (!move_uploaded_file($_FILES['comprovante']['tmp_name'], $destino)) {
            throw new Exception("Nao foi possivel gravar o comprovante.");
        }
        return $nome;
    }

    /**
     * Envia o arquivo do comprovante para download/visualizacao.
     */
    public function baixarComprovante($fws_, $debug = false) {
        // o link e GET (abre em nova aba); nesse caso os parametros vem em $_GET,
        // nao em $fws_ (que o Command so preenche com c/m).
        $cod = isset($fws_['codLancamentosContabeis']) && $fws_['codLancamentosContabeis'] !== ''
             ? $fws_['codLancamentosContabeis']
             : (isset($_GET['codLancamentosContabeis']) ? $_GET['codLancamentosContabeis'] : 0);
        $obj = new LancamentosContabeis();
        $obj->setcodLancamentosContabeis(intval($cod));
        $obj->getLancamentosContabeisById($debug);
        $nome = $obj->getArquivoComprovante();
        if (!$nome) {
            exit('Sem comprovante.');
        }
        $pasta = realpath(dirname(WB_PATH_LOG_RPA)) . DIRECTORY_SEPARATOR . basename(WB_PATH_LOG_RPA);
        $caminho = $pasta . DIRECTORY_SEPARATOR . basename($nome);
        if (!is_file($caminho)) {
            exit('Arquivo do comprovante nao encontrado.');
        }
        $mime = 'application/octet-stream';
        $ext = strtolower(pathinfo($caminho, PATHINFO_EXTENSION));
        if ($ext == 'pdf')  { $mime = 'application/pdf'; }
        if (in_array($ext, array('jpg', 'jpeg'))) { $mime = 'image/jpeg'; }
        if ($ext == 'png')  { $mime = 'image/png'; }
        header('Content-Type: ' . $mime);
        header('Content-Disposition: inline; filename="' . basename($nome) . '"');
        header('Content-Length: ' . filesize($caminho));
        readfile($caminho);
        exit();
    }

    public function excluirLancamentosContabeis($fws_, $debug = false) {
        try {
            $obj = new LancamentosContabeis();
            $obj->setcodLancamentosContabeis($fws_['codLancamentosContabeis']);
            $obj->getLancamentosContabeisById($debug);
            // nao deixa apagar lancamento de periodo ja fechado
            $this->exigirPeriodoAberto($obj->getDataCompetencia());
            $obj->excluir();
        } catch (OKException $e) {
            $ok = true;
        } catch (Exception $e) {
            $ok = false;
        }

        echo '{
        "statusMessage":"' . $ok . '",
        "message":      "' . tratarJson( $e->getMessage()) . '",
        "codLancamentosContabeis":"' . $fws_['codLancamentosContabeis'] . '"
        }';
    }

    /**
     * Filtros da listagem. Ficam aqui (e nao no DAO) so para nao espalhar a
     * leitura do $fws_ pelas camadas de baixo.
     */
    private function filtrosDaTela($fws_) {
        $campos = array('dataInicio', 'dataFim', 'codPlanoContas', 'tipoLancamento',
                        'origemLancamento', 'conciliado', 'busca',
                        'filtroInscrito', 'filtroPagamento', 'filtroPedido');
        $filtros = array();
        foreach ($campos as $c) {
            if (isset($fws_[$c]) && trim($fws_[$c]) !== '') {
                $filtros[$c] = trim($fws_[$c]);
            }
        }
        return $filtros;
    }

    /**
     * Rotulos de tela. As constantes sao MAIUSCULAS_COM_UNDERSCORE, que numa
     * coluna estreita quebram letra a letra -- aqui viram texto normal.
     */
    private function rotuloTipo($tipo) {
        $mapa = array(
            'VENDA'                => 'Venda',
            'DESPESA_PAGA'         => 'Despesa paga',
            'PAGAMENTO_FORNECEDOR' => 'Pgto. fornecedor',
        );
        return isset($mapa[$tipo]) ? $mapa[$tipo] : ucfirst(strtolower(str_replace('_', ' ', $tipo)));
    }

    private function rotuloOrigem($origem) {
        $mapa = array(
            'MANUAL'               => 'Manual',
            'PAGAMENTO_AUTOMATICO' => 'Automático',
            'FINANCEIRO'           => 'Financeiro',
            'INTEGRACAO'           => 'Integração',
            'ESTORNO'              => 'Estorno',
        );
        return isset($mapa[$origem]) ? $mapa[$origem] : ucfirst(strtolower(str_replace('_', ' ', $origem)));
    }

    /**
     * Nome da conta em caixa normal: o catalogo grava tudo em maiuscula.
     * So mexe se a string for UTF-8 valida -- o banco devolve ora latin1, ora
     * utf8, e mb_convert_case em latin1 corrompe o acento.
     */
    private function nomeAmigavel($nome) {
        if (!$nome || !mb_detect_encoding($nome, 'UTF-8', true)) {
            return $nome;
        }
        return (mb_strtoupper($nome, 'UTF-8') === $nome) ? mb_convert_case($nome, MB_CASE_TITLE, 'UTF-8') : $nome;
    }

    /** O evento ja tem catalogo contabil? Usado so para orientar a tela vazia. */
    private function eventoTemPlanoContas($debug = false) {
        try {
            $objPC = new PlanoContas();
            $objPC->carregarListaPlanoContas(false, $debug);
            return (bool) $objPC->getListaPlanoContas();
        } catch (Exception $e) {
            return false;
        }
    }

    public function carregarTabelaLancamentosContabeis($fws_, $debug = false) {
        $obj = new LancamentosContabeis();
        try {
            $filtros = $this->filtrosDaTela($fws_);
            $lista = $obj->carregarListaFiltrada($filtros, $debug);

            $ii = 0;
            $totalDebito = 0;
            $totalCredito = 0;

            foreach ($lista as $r) {
                $ii++;
                $valor = floatval($r['valor']);
                $ehDebito = ($r['flg_lancamento'] == 'D');
                if ($ehDebito) { $totalDebito += $valor; } else { $totalCredito += $valor; }

                $tipo = $r['tipo_lancamento'];
                $dotTipo = 'dot-blue';
                if ($tipo == 'VENDA') $dotTipo = 'dot-green';
                if ($tipo == 'DESPESA_PAGA') $dotTipo = 'dot-red';
                if ($tipo == 'PAGAMENTO_FORNECEDOR') $dotTipo = 'dot-orange';

                // conciliado gravado ora como 1, ora como 'SIM'
                $ehConciliado = in_array((string)$r['conciliado'], array('1', 'SIM'));

                $dtComp = $r['data_competencia'] ? date('d/m/Y', strtotime($r['data_competencia'])) : '';
                $dtFin  = $r['data_financeira'] ? date('d/m/Y', strtotime($r['data_financeira'])) : '';
                ?>
                <tr id="<?php echo $r['cod_lancamentos_contabeis'] ?>">
                    <td>
                        <span class="editar btn-acao" title="Editar"><i class="fa fa-pencil"></i></span>
                        <span class="excluir btn-acao btn-acao-excluir" title="Excluir"><i class="fa fa-trash"></i></span>
                    </td>
                    <td class="col-data" title="Competência<?php echo $dtFin && $dtFin != $dtComp ? ' / financeira' : ''; ?>">
                        <?php echo $dtComp; ?>
                        <?php if ($dtFin && $dtFin != $dtComp) { ?>
                            <span class="data-secundaria">fin. <?php echo $dtFin; ?></span>
                        <?php } ?>
                    </td>
                    <td class="col-conta">
                        <?php if ($r['codigo_contabil'] || $r['nome_plano_contas']) { ?>
                            <span class="conta-codigo"><?php echo $r['codigo_contabil']; ?></span>
                            <span class="conta-nome"><?php echo $this->nomeAmigavel($r['nome_plano_contas']); ?></span>
                        <?php } else { ?>
                            <span style="color:#bbb;">sem conta</span>
                        <?php } ?>
                    </td>
                    <td class="col-badge">
                        <span class="badge-tipo"><span class="dot <?php echo $dotTipo; ?>"></span><?php echo $this->rotuloTipo($tipo); ?></span>
                    </td>
                    <td class="col-num">
                        R$ <?php echo number_format($valor, 2, ',', '.'); ?>
                        <span style="font-size:11px; color:#999;"><?php echo $ehDebito ? 'D' : 'C'; ?></span>
                    </td>
                    <td class="col-nome">
                        <?php echo $r['favorecido']; ?>
                    </td>
                    <td class="col-codigo">
                        <?php echo $r['num_documento']; ?>
                    </td>
                    <td class="col-badge">
                        <?php echo $this->rotuloOrigem($r['origem_lancamento']); ?>
                    </td>
                    <td class="col-hist">
                        <?php echo $r['historico']; ?>
                        <?php if ($r['arquivo_comprovante']) { ?>
                            <a href="<?php echo WB_URL_COMMAND ?>&c=GerenciarLancamentosContabeis&m=baixarComprovante&codLancamentosContabeis=<?php echo $r['cod_lancamentos_contabeis'] ?>&_submit_check=1"
                               target="_blank" title="Ver o comprovante anexado" style="margin-left:6px; color:#2980b9;">
                                <i class="fa fa-paperclip"></i>
                            </a>
                        <?php } ?>
                    </td>
                    <td class="col-badge">
                        <span class="badge-tipo"><span class="dot <?php echo $ehConciliado ? 'dot-green' : 'dot-red'; ?>"></span><?php echo $ehConciliado ? 'SIM' : 'NÃO'; ?></span>
                    </td>
                </tr>
                <?php
            }

            if ($ii == 0) {
                echo "<tr><td colspan='10' style='padding:14px; color:#888;'>Nenhum lan&ccedil;amento encontrado"
                   . (count($filtros) ? " com os filtros informados." : ".");
                // Sem filtro e sem nada na tela pode ser evento ainda sem catalogo
                // contabil: leva direto para a tela que popula tudo de uma vez.
                if (!count($filtros) && !$this->eventoTemPlanoContas($debug)) {
                    echo ' O evento ainda n&atilde;o tem plano de contas.'
                       . ' <a href="index.php?content=view/admin/ConfigurarContabilidade.php" '
                       . 'style="margin-left:6px; font-weight:600;">Configurar agora</a>';
                }
                echo "</td></tr>";
            }
            ?>
                </tbody><tfoot>
                <?php
                $saldo = $totalCredito - $totalDebito;
                $ladoSaldo = $saldo >= 0 ? 'C' : 'D';
                $corSaldo = $saldo >= 0 ? '#27ae60' : '#e74c3c';
                ?>
                <tr style="border-top:2px solid #333; font-weight:700;">
                    <td colspan="4" style="padding:10px;">TOTAIS<?php echo count($filtros) ? ' (filtrados)' : ''; ?></td>
                    <td class="col-num" style="padding:10px;">
                        D R$ <?php echo number_format($totalDebito, 2, ',', '.'); ?><br>
                        C R$ <?php echo number_format($totalCredito, 2, ',', '.'); ?><br>
                        <span style="color:<?php echo $corSaldo; ?>;">S R$ <?php echo number_format(abs($saldo), 2, ',', '.'); ?> (<?php echo $ladoSaldo; ?>)</span>
                    </td>
                    <td colspan="5"></td>
                </tr>
                <tr><td colspan="10" style="text-align:right; color:#999; font-size:12px; padding:8px 10px; border-top:1px solid #eee;">Total: <?php echo $ii ?> registro<?php echo $ii != 1 ? 's' : '' ?></td></tr>
                </tfoot><tbody class="inputs" style="display:none;">
            <?php
        } catch (Exception $e) {
            echo "<tr><td></td><td colspan=7>";
            if ($e->getMessage()) {
                echo $e->getMessage();
                // Evento sem catalogo contabil: leva direto para a tela que
                // popula tudo de uma vez, em vez de deixar o usuario cadastrar
                // item a item nas quatro telas.
                if (strpos($e->getMessage(), 'Centro de Custo') !== false) {
                    echo ' <a href="index.php?content=view/admin/ConfigurarContabilidade.php" '
                       . 'style="margin-left:6px; font-weight:600;">Configurar agora</a>';
                }
            } else {
                echo "Lista vazia.";
            }
            echo "</td></tr>";
        }
    }    
}

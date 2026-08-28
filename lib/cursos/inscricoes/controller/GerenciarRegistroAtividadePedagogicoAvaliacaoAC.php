<?php
/**
 * Avaliacao do Registro de Atividade (modulo Pedagogico).
 *
 * Quem usa: COORDENADOR (SIS_PEDAGOGICO).
 * Diferenca do evento 82 (membro NRE): aqui o avaliador loga como ADMIN
 * (_cod_usuario), nao como inscrito (_cod_inscrito). Por isso cod_avaliador
 * fica NULL e a identificacao vai em avaliacaoResponsavel (nome do coordenador)
 * + id_alteracao (gravado pelo DAO).
 */
class GerenciarRegistroAtividadePedagogicoAvaliacaoAC {

    function __construct() {
        $codUsuario = isset($_SESSION['_cod_usuario']) ? $_SESSION['_cod_usuario'] : null;
        if (!$codUsuario) {
            throw new Exception("Usuario nao esta logado.");
        }
        if (!FWA_Autenticacao($codUsuario, enumSistema::SIS_PEDAGOGICO, 5, false, false)) {
            throw new Exception("Acesso restrito ao coordenador pedagogico.");
        }
    }

    private function getNomeCoordenadorLogado() {
        try {
            $conex = FWA_Conexao('OpcTec');
            $codUsuario = intval($_SESSION['_cod_usuario']);
            $sql = "SELECT COALESCE(tc.nome_convidado, su.nome_usuario, su.login) AS nome
                    FROM sis_usuarios su
                    LEFT JOIN tab_convidados tc ON tc.cod_usuario = su.cod_usuario
                    WHERE su.cod_usuario = {$codUsuario}";
            $rs = $conex->Execute($sql);
            if ($rs && !$rs->EOF) {
                foreach ($rs as $row) { return $row['nome']; }
            }
        } catch (Exception $e) {}
        return '';
    }

    public function carregarRegistroAtividade($fws_, $debug = false) {
        $obj = new RegistroAtividade();
        $obj->setCodRegAtividade($fws_['codRegAtividade']);
        $obj->getRegistroAtividadeById($debug);
        return $obj;
    }

    // Lista anexos do registro para o coordenador visualizar na avaliacao.
    // So link de download; sem botao de excluir (coordenador nao remove anexo do gerente).
    public function carregarArquivosRegistroAvaliacao($fws_, $debug = false) {
        $codRegAtividade = isset($fws_['codRegAtividade']) ? intval($fws_['codRegAtividade']) : 0;
        if (!$codRegAtividade) {
            echo '<div style="font-size:12px; color:#888; font-style:italic;">Registro nao informado.</div>';
            exit();
        }
        $daoArquivo = new RegistroAtividadeArquivoDAO();
        $arquivos = $daoArquivo->listarPorRegistro($codRegAtividade, COD_EVENTO);
        if (empty($arquivos)) {
            echo '<div style="font-size:12px; color:#888; font-style:italic;">Nenhum arquivo anexado.</div>';
            exit();
        }
        $html = '';
        foreach ($arquivos as $arq) {
            $nome = htmlspecialchars(mb_strimwidth($arq['arquivo_nome'], 0, 40, '...'));
            $nomeCompleto = htmlspecialchars($arq['arquivo_nome']);
            $html .= '<div class="arquivo-item" data-cod="' . $arq['cod_arquivo'] . '" style="display:flex; align-items:center; gap:8px; padding:4px 8px; background:#f5f5f5; border-radius:4px; margin-bottom:4px; font-size:12px;">';
            // Download via metodo deste controller (coordenador nao tem inscrito, nao passa pelo construtor do GerenciarRegistroAtividadePedagogicoAC).
            $html .= '<a href="' . WB_URL_COMMAND . '&c=GerenciarRegistroAtividadePedagogicoAvaliacao&m=downloadArquivoAvaliacao&codArquivo=' . $arq['cod_arquivo'] . '&_submit_check=1" target="_blank" title="' . $nomeCompleto . '"><i class="fa fa-paperclip"></i> ' . $nome . '</a>';
            $html .= '</div>';
        }
        echo $html;
        exit();
    }

    // Download do anexo pelo coordenador na tela de avaliacao.
    // Por que duplicar? O controller do gerente resolve _cod_inscrito no construtor e
    // coordenador nao tem inscrito no evento — caia em "usuario nao possui inscricao".
    public function downloadArquivoAvaliacao($fws_, $debug = false) {
        try {
            // codArquivo pode vir por GET (link href) ou POST. $fws_ so traz POST no framework,
            // entao fallback pra $_GET.
            $codArquivo = !empty($fws_['codArquivo']) ? $fws_['codArquivo'] : (isset($_GET['codArquivo']) ? $_GET['codArquivo'] : '');
            $daoArquivo = new RegistroAtividadeArquivoDAO();
            $arquivo = $daoArquivo->buscarPorCodigo($codArquivo, COD_EVENTO);
            if (!$arquivo) {
                throw new Exception("Arquivo nao encontrado.");
            }
            $dir = WB_PATH_LOG . 'registro_atividades/';
            $caminhoArquivo = $dir . $arquivo['arquivo_path'];
            if (!file_exists($caminhoArquivo)) {
                throw new Exception("Arquivo nao encontrado no servidor.");
            }
            $nomeOriginal = $arquivo['arquivo_nome'] ? $arquivo['arquivo_nome'] : $arquivo['arquivo_path'];
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $nomeOriginal . '"');
            if (substr($arquivo['arquivo_path'], -4) === '.enc') {
                $conteudo = CriptografiaHelper::descriptografarArquivo($caminhoArquivo);
                header('Content-Length: ' . strlen($conteudo));
                echo $conteudo;
            } else {
                header('Content-Length: ' . filesize($caminhoArquivo));
                readfile($caminhoArquivo);
            }
            exit();
        } catch (Exception $e) {
            echo $e->getMessage();
            exit();
        }
    }

    public function editarRegistroAtividadeAvaliacao($fws_, $debug = false) {
        $obj = $this->carregarRegistroAtividade($fws_, $debug);
        $horasTrabalhadas = floor($obj->getHorasTrabalhadas() / 60) . ' horas e ' . ($obj->getHorasTrabalhadas() % 60) . ' minutos';
        $txtDescricao = str_replace("\\'", "'", $obj->getDescricao());
        $txtDescricao = str_replace("'", '"', $txtDescricao);

        echo json_encode(array(
            'statusMessage' => true,
            'codRegAtividade' => $obj->getCodRegAtividade(),
            'codInscrito' => $obj->getCodInscrito(),
            'nomeInscrito' => $obj->getNomeInscrito(),
            'dia' => formatDateView($obj->getDia()),
            'diaSemana' => $obj->getDiaSemana(),
            'horasTrabalhadas' => $horasTrabalhadas,
            'descricao' => $txtDescricao,
            'justificativa' => $obj->getJustificativa(),
            'parecerAvaliador' => $obj->getParecerAvaliador(),
            'avaliacaoStatus' => $obj->getAvaliacaoStatus(),
            'avaliacaoResponsavel' => $obj->getAvaliacaoResponsavel(),
            'avaliacaoData' => $obj->getAvaliacaoData()
        ));
        exit();
    }

    public function salvarRegistroAtividadeAvaliacao($fws_, $debug = false) {
        $ok = false; $msg = '';
        try {
            if (empty($fws_['codRegAtividade'])) {
                throw new Exception("Registro nao informado.");
            }
            if (empty($fws_['avaliacaoStatus'])) {
                throw new Exception("Informe a decisao (Validado ou Nao Validado).");
            }
            if ($fws_['avaliacaoStatus'] === 'Não Validado' && empty(trim($fws_['parecerAvaliador']))) {
                throw new Exception("Parecer e obrigatorio para 'Nao Validado'.");
            }

            $obj = $this->carregarRegistroAtividade($fws_, $debug);

            if ($obj->getDia()) {
                $partes = explode('-', $obj->getDia());
                if (count($partes) >= 2 && class_exists('BloqueioAvaliacao')
                    && BloqueioAvaliacao::isMesBloqueado(intval($partes[1]), intval($partes[0]))) {
                    throw new Exception("Avaliacao bloqueada para o mes " . $partes[1] . "/" . $partes[0] . ".");
                }
            }

            // cod_avaliador fica NULL (campo e FK de tab_inscritos; coordenador nao e inscrito).
            // Identificacao via avaliacaoResponsavel (nome) + id_alteracao (gravado pelo DAO).
            $obj->setCodAvaliador(null);
            $obj->setParecerAvaliador(isset($fws_['parecerAvaliador']) ? $fws_['parecerAvaliador'] : '');
            $obj->setAvaliacaoStatus($fws_['avaliacaoStatus']);
            $obj->setAvaliacaoResponsavel($this->getNomeCoordenadorLogado());
            $obj->setAvaliacaoData(date('Y-m-d H:i:s'));
            $obj->salvarAvaliacao($debug);
            $ok = true;
            $msg = 'Avaliacao registrada.';
        } catch (OkException $e) {
            $ok = true; $msg = $e->getMessage();
        } catch (Exception $e) {
            $msg = $e->getMessage();
        }
        echo json_encode(array(
            'statusMessage' => $ok,
            'message' => $msg,
            'codRegAtividade' => isset($fws_['codRegAtividade']) ? $fws_['codRegAtividade'] : ''
        ));
        exit();
    }

    public function exportarPlanilha($fws_, $debug = false) {
        try {
            $codInscrito = isset($fws_['codInscritoFiltro']) ? intval($fws_['codInscritoFiltro']) : 0;
            $dataInicio = isset($fws_['dataInicio']) ? trim($fws_['dataInicio']) : '';
            $dataFim = isset($fws_['dataFim']) ? trim($fws_['dataFim']) : '';
            if (empty($dataInicio) || empty($dataFim)) {
                throw new Exception("Informe a data inicial e a data final.");
            }

            $obj = new RegistroAtividade();
            $linhas = $obj->getLinhasParaExportacao($codInscrito, $dataInicio, $dataFim, $debug);

            // Nome do arquivo: usa nome do gerente quando filtrado, senao "todos"
            $rotulo = 'todos';
            if ($codInscrito > 0 && !empty($linhas)) {
                $rotulo = isset($linhas[0]['nome_inscrito']) ? $linhas[0]['nome_inscrito'] : 'gerente';
            } elseif ($codInscrito > 0) {
                $rotulo = 'gerente';
            }
            $slug = preg_replace('/[^A-Za-z0-9]+/', '_', $rotulo);
            $slug = trim($slug, '_');
            if (empty($slug)) { $slug = 'gerente'; }
            $nomeArquivo = sprintf('registros_atividade_%s_%s_a_%s.xlsx', $slug, $dataInicio, $dataFim);

            $autoload = dirname(__FILE__) . '/../lib/spreadsheet/vendor/autoload.php';
            if (!file_exists($autoload)) {
                throw new Exception("Biblioteca PhpSpreadsheet nao encontrada no servidor.");
            }
            require_once($autoload);

            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Registros');

            $cabecalhos = array('Data', 'Dia da semana', 'Gerente', 'Horas', 'Descricao', 'Status', 'Avaliado por');
            foreach ($cabecalhos as $i => $titulo) {
                $col = chr(ord('A') + $i);
                $sheet->setCellValue($col . '1', $titulo);
            }

            $larguras = array('A' => 12, 'B' => 16, 'C' => 32, 'D' => 12, 'E' => 70, 'F' => 18, 'G' => 30);
            foreach ($larguras as $col => $w) {
                $sheet->getColumnDimension($col)->setWidth($w);
            }

            $sheet->getStyle('A1:G1')->applyFromArray(array(
                'font' => array('bold' => true, 'color' => array('rgb' => 'FFFFFF')),
                'fill' => array(
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => array('rgb' => '2E5C8A')
                ),
                'alignment' => array('vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER),
            ));
            $sheet->getRowDimension(1)->setRowHeight(22);
            $sheet->freezePane('A2');

            $linhaAtual = 2;
            foreach ($linhas as $linha) {
                $statusAval = !empty($linha['avaliacao_status']) ? $linha['avaliacao_status'] : 'Aguardando';
                $sheet->setCellValue('A' . $linhaAtual, formatDateView($linha['dia']));
                $sheet->setCellValue('B' . $linhaAtual, $linha['dia_semana']);
                $sheet->setCellValue('C' . $linhaAtual, $linha['nome_inscrito']);
                $sheet->setCellValue('D' . $linhaAtual, converterMinutosParaHoras($linha['horas_trabalhadas']));
                $sheet->setCellValue('E' . $linhaAtual, $linha['descricao']);
                $sheet->setCellValue('F' . $linhaAtual, $statusAval);
                $sheet->setCellValue('G' . $linhaAtual, $linha['avaliacao_responsavel']);

                // Pinta fundo conforme status pra dar feedback visual rapido
                $corFundo = null;
                if ($statusAval === 'Validado') {
                    $corFundo = 'EAF6E5'; // verde claro
                } elseif ($statusAval === 'Não Validado') {
                    $corFundo = 'FDECEA'; // vermelho claro
                } elseif ($statusAval === 'Aguardando') {
                    $corFundo = 'FFF8E1'; // amarelo claro
                }
                if ($corFundo) {
                    $sheet->getStyle('A' . $linhaAtual . ':G' . $linhaAtual)->applyFromArray(array(
                        'fill' => array(
                            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                            'startColor' => array('rgb' => $corFundo)
                        ),
                    ));
                }
                $linhaAtual++;
            }

            $ultimaLinha = max($linhaAtual - 1, 2);
            $sheet->getStyle('A2:G' . $ultimaLinha)->getAlignment()
                ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP)
                ->setWrapText(true);

            while (ob_get_level() > 0) { ob_end_clean(); }
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $nomeArquivo . '"');
            header('Cache-Control: no-cache, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);
            exit();
        } catch (Exception $e) {
            while (ob_get_level() > 0) { ob_end_clean(); }
            header('Content-Type: text/html; charset=UTF-8');
            echo '<div style="font-family:Arial; padding:30px; max-width:600px; margin:30px auto; border:1px solid #ddd; border-radius:8px;">';
            echo '<h3 style="color:#a94442;">Nao foi possivel exportar</h3>';
            echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
            echo '<p><a href="javascript:window.close();">Fechar</a></p>';
            echo '</div>';
            exit();
        }
    }

    public function carregarListaGerentes($fws_, $debug = false) {
        try {
            $obj = new RegistroAtividade();
            $lista = $obj->getGerentesComAtividade($debug);
            echo json_encode(array('statusMessage' => true, 'lista' => $lista));
        } catch (Exception $e) {
            echo json_encode(array('statusMessage' => false, 'message' => $e->getMessage()));
        }
        exit();
    }

    // Retorna o HTML do tbody com as atividades filtradas.
    // Agrupa por mes/ano + gerente (igual ao padrao do evento 82).
    public function carregarTabelaRegistroAtividadesAvaliacao($fws_, $debug = false) {
        try {
            $filtros = array(
                'cod_inscrito_gerente' => isset($fws_['filtroGerente']) ? $fws_['filtroGerente'] : '',
                'avaliacao_status' => isset($fws_['filtroStatus']) ? $fws_['filtroStatus'] : '',
                'ano' => isset($fws_['filtroAno']) ? $fws_['filtroAno'] : '',
                'mes' => isset($fws_['filtroMes']) ? $fws_['filtroMes'] : ''
            );

            $obj = new RegistroAtividade();
            $lista = $obj->getListaByEvento($filtros, $debug);

            if (empty($lista)) {
                echo '<tr><td colspan="7" style="text-align:center; padding:30px; color:#999;"><em>Nenhuma atividade encontrada com os filtros selecionados.</em></td></tr>';
                exit();
            }

            $dadosAgrupados = array();
            foreach ($lista as $objL) {
                if ($objL->getCodInscrito() == '') continue;
                $parts = explode('-', $objL->getDia());
                $ano = $parts[0];
                $mes = $parts[1];
                $anoMes = $ano . '-' . $mes;
                $codInscrito = $objL->getCodInscrito();
                if (!isset($dadosAgrupados[$anoMes])) $dadosAgrupados[$anoMes] = array();
                if (!isset($dadosAgrupados[$anoMes][$codInscrito])) {
                    $dadosAgrupados[$anoMes][$codInscrito] = array(
                        'nomeInscrito' => $objL->getNomeInscrito(),
                        'atividades' => array(),
                        'totalHoras' => 0
                    );
                }
                $dadosAgrupados[$anoMes][$codInscrito]['atividades'][] = $objL;
                if (strtolower($objL->getAvaliacaoStatus()) === 'validado') {
                    $dadosAgrupados[$anoMes][$codInscrito]['totalHoras'] += (float) $objL->getHorasTrabalhadas();
                }
            }

            ksort($dadosAgrupados);
            // Exibe meses mais recentes primeiro
            $dadosAgrupados = array_reverse($dadosAgrupados, true);

            foreach ($dadosAgrupados as $anoMes => $inscritos) {
                $p = explode('-', $anoMes);
                $mesNome = getMonthName($p[1]);
                $classesFiltro = "mes-{$p[1]} ano-{$p[0]}";
                echo "<tr class='grupo-mes {$classesFiltro}'><td colspan='7'><h5 style='margin:10px 0;color:#4A90E2;'>Relat&oacute;rio: {$mesNome}/{$p[0]}</h5></td></tr>";
                echo "<tr class='grupo-mes-header {$classesFiltro}' style='background:#f0f0f0; font-weight:bold;'>
                        <th style='width:140px;'>A&ccedil;&otilde;es</th>
                        <th style='width:100px;'>Dia</th>
                        <th style='width:120px;'>Dia da semana</th>
                        <th style='width:100px;'>Horas</th>
                        <th>Descri&ccedil;&atilde;o</th>
                        <th style='width:180px;'>Status Valida&ccedil;&atilde;o</th>
                        <th style='width:140px;'>Avaliado por</th>
                      </tr>";
                foreach ($inscritos as $codInscrito => $dados) {
                    echo "<tr class='grupo-colaborador {$classesFiltro}'><td colspan='7' style='font-size:14px; font-weight:bold; background:#fafafa; padding:8px;'>Colaborador: " . htmlspecialchars(maiusculo($dados['nomeInscrito'])) . "</td></tr>";
                    foreach ($dados['atividades'] as $objL) {
                        $avaliacaoStatus = $objL->getAvaliacaoStatus();
                        if ($avaliacaoStatus === 'Validado') {
                            $classeStatus = 'status-validado';
                            $corText = '#3c763d';
                            $txtBotao = 'Revalidar';
                        } elseif ($avaliacaoStatus === 'Não Validado') {
                            $classeStatus = 'status-nao-validado';
                            $corText = '#a94442';
                            $txtBotao = 'Revalidar';
                        } else {
                            $classeStatus = 'status-aguardando';
                            $corText = '#8a6d3b';
                            $txtBotao = 'Avaliar';
                        }

                        $mesBloqueado = false;
                        if ($objL->getDia() && class_exists('BloqueioAvaliacao')) {
                            $pdia = explode('-', $objL->getDia());
                            if (count($pdia) >= 2) {
                                $mesBloqueado = BloqueioAvaliacao::isMesBloqueado(intval($pdia[1]), intval($pdia[0]));
                            }
                        }

                        echo "<tr id='" . $objL->getCodRegAtividade() . "' class='ativ-row {$classeStatus} {$classesFiltro}'>";
                        echo "<td>";
                        if ($mesBloqueado) {
                            echo "<button type='button' class='btn btn-default btn-xs' disabled>M&ecirc;s fechado</button>";
                        } else {
                            echo "<button type='button' class='btn btn-primary btn-xs avaliarAtividade'><i class='glyphicon glyphicon-check'></i> " . $txtBotao . "</button>";
                        }
                        echo "</td>";
                        echo "<td>" . formatDateView($objL->getDia()) . "</td>";
                        echo "<td>" . htmlspecialchars($objL->getDiaSemana()) . "</td>";
                        echo "<td>" . converterMinutosParaHoras($objL->getHorasTrabalhadas()) . "</td>";
                        echo "<td>" . htmlspecialchars($objL->getDescricao()) . "</td>";
                        echo "<td style='color:{$corText}; font-weight:600;'>";
                        echo $avaliacaoStatus ? htmlspecialchars($avaliacaoStatus) : 'Aguardando';
                        if ($objL->getAvaliacaoData()) {
                            echo "<br><small style='color:#888;font-weight:normal;'>" . formatDateView($objL->getAvaliacaoData()) . "</small>";
                        }
                        echo "</td>";
                        echo "<td style='font-size:12px;'>" . htmlspecialchars($objL->getAvaliacaoResponsavel()) . "</td>";
                        echo "</tr>";
                    }
                    echo "<tr class='total-colaborador {$classesFiltro}'><td colspan='7' style='text-align:right; background:#fafafa; padding:6px 10px; font-size:12px; border-top:1px solid #eee;'><strong>Total de horas validadas: " . converterMinutosParaHoras($dados['totalHoras']) . "</strong></td></tr>";
                }
            }
        } catch (Exception $e) {
            echo '<tr><td colspan="7">' . ($e->getMessage() ?: 'Erro ao carregar.') . '</td></tr>';
        }
    }
}

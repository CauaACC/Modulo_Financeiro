<?php

class GerenciarRegistroOrientacaoAC {

    private $codSupervisor = null;
    private $papelPedagogico = null;

    function __construct() {
        $cod_usuario = $_SESSION['_cod_usuario'];
        if (!$cod_usuario) {
            throw new Exception("Usuario nao esta logado.");
        }
        if (FWA_Autenticacao($cod_usuario, enumSistema::SIS_PEDAGOGICO, 5, false, false)) {
            $this->papelPedagogico = 'COORDENADOR';
        } elseif (FWA_Autenticacao($cod_usuario, enumSistema::SIS_PEDAGOGICO_GERENTE, 5, false, false)) {
            $this->papelPedagogico = 'GERENTE';
        } elseif (FWA_Autenticacao($cod_usuario, enumSistema::SIS_PEDAGOGICO_SUPERVISOR, 5, false, false)) {
            $this->papelPedagogico = 'SUPERVISOR';
            $this->codSupervisor = $cod_usuario;
        }
        if (!$this->papelPedagogico) {
            throw new Exception("Acesso restrito ao modulo pedagogico.");
        }
    }

    public function getCodSupervisor() {
        return $this->codSupervisor;
    }

    public function getPapelPedagogico() {
        return $this->papelPedagogico;
    }

    public function isAdmin() {
        return in_array($this->papelPedagogico, array('COORDENADOR', 'GERENTE'));
    }

    // Desfaz o addslashes aplicado em arrays de POST pelo config.php.
    // Why: config.php aplica utf8_decode + addslashes em itens de array do POST,
    //      o que corrompe acentos e multiplica barras a cada edição.
    private function normalizarTexto($valor) {
        if (!isset($valor)) return '';
        $valor = stripslashes($valor);
        if (function_exists('mb_detect_encoding') && mb_detect_encoding($valor, 'UTF-8', true) === false) {
            $valor = mb_convert_encoding($valor, 'UTF-8', 'ISO-8859-1');
        }
        return trim($valor);
    }

    private function montarRegistroSupervisor($fws_) {
        if ($this->isAdmin()) {
            throw new Exception("Coordenadores e gerentes nao podem registrar ou editar.");
        }
        if (empty($fws_['dataRegistro'])) {
            throw new Exception("Informe a data do registro.");
        }
        $obj = new RegistroOrientacao();
        $obj->setDataRegistro($fws_['dataRegistro']);
        $obj->setCodSupervisor($this->codSupervisor);
        return $obj;
    }

    // --- Dia (atividades_dia + descricao_dia) ---

    public function salvarDia($fws_, $debug = false) {
        $ok = false; $msg = '';
        try {
            $obj = $this->montarRegistroSupervisor($fws_);
            $atividadesDia = isset($fws_['atividades_dia']) && is_array($fws_['atividades_dia'])
                ? implode(',', $fws_['atividades_dia']) : '';
            $obj->setAtividadesDia($atividadesDia);
            $obj->setDescricaoDia(isset($fws_['descricaoDia']) ? $this->normalizarTexto($fws_['descricaoDia']) : '');
            $obj->salvarDia($debug);
        } catch (OKException $e) {
            $ok = true; $msg = $e->getMessage();
        } catch (Exception $e) {
            $msg = $e->getMessage();
        }
        echo json_encode(array('statusMessage' => $ok, 'message' => $msg));
        exit();
    }

    public function carregarDadosDia($fws_, $debug = false) {
        try {
            $obj = $this->montarRegistroSupervisor($fws_);
            $encontrado = $obj->carregarDia($debug);
            $itensPes = $encontrado ? $obj->getItensPesByRegistro($debug) : array();
            $itensCrianca = $encontrado ? $obj->getItensCriancaByRegistro($debug) : array();
            echo json_encode(array(
                'statusMessage' => true,
                'existe' => $encontrado,
                'codRegistro' => $encontrado ? $obj->getCodRegistro() : '',
                'dataRegistro' => $obj->getDataRegistro(),
                'atividadesDia' => $encontrado ? $obj->getAtividadesDia() : '',
                'descricaoDia' => $encontrado ? $obj->getDescricaoDia() : '',
                'itensPes' => $itensPes,
                'itensCrianca' => $itensCrianca
            ));
        } catch (Exception $e) {
            echo json_encode(array('statusMessage' => false, 'message' => $e->getMessage()));
        }
        exit();
    }

    // --- Item PES ---

    public function salvarItemPes($fws_, $debug = false) {
        $ok = false; $msg = '';
        try {
            $obj = $this->montarRegistroSupervisor($fws_);
            $atividades = isset($fws_['atividades_pes']) ? $fws_['atividades_pes'] : '';
            if (is_array($atividades)) $atividades = implode(',', $atividades);
            $item = array(
                'cod_agente' => isset($fws_['cod_agente']) ? $fws_['cod_agente'] : '',
                'atividades_pes' => $atividades,
                'descricao_pes' => isset($fws_['descricao_pes']) ? $this->normalizarTexto($fws_['descricao_pes']) : '',
                'orientacoes_pes' => isset($fws_['orientacoes_pes']) ? $this->normalizarTexto($fws_['orientacoes_pes']) : ''
            );
            $codItem = !empty($fws_['cod_registro_pes']) ? intval($fws_['cod_registro_pes']) : null;
            $obj->salvarItemPes($item, $codItem, $debug);
        } catch (OKException $e) {
            $ok = true; $msg = $e->getMessage();
        } catch (Exception $e) {
            $msg = $e->getMessage();
        }
        echo json_encode(array('statusMessage' => $ok, 'message' => $msg));
        exit();
    }

    public function excluirItemPes($fws_, $debug = false) {
        $ok = false; $msg = '';
        try {
            if ($this->isAdmin()) {
                throw new Exception("Coordenadores e gerentes nao podem excluir.");
            }
            $obj = new RegistroOrientacao();
            $obj->excluirItemPes(isset($fws_['cod_registro_pes']) ? $fws_['cod_registro_pes'] : '', $debug);
        } catch (OKException $e) {
            $ok = true; $msg = $e->getMessage();
        } catch (Exception $e) {
            $msg = $e->getMessage();
        }
        echo json_encode(array('statusMessage' => $ok, 'message' => $msg));
        exit();
    }

    public function carregarItemPes($fws_, $debug = false) {
        try {
            $obj = new RegistroOrientacao();
            $item = $obj->getItemPesById(isset($fws_['cod_registro_pes']) ? $fws_['cod_registro_pes'] : '', $debug);
            echo json_encode(array('statusMessage' => $item ? true : false, 'item' => $item));
        } catch (Exception $e) {
            echo json_encode(array('statusMessage' => false, 'message' => $e->getMessage()));
        }
        exit();
    }

    // --- Item Crianca ---

    public function salvarItemCrianca($fws_, $debug = false) {
        $ok = false; $msg = '';
        try {
            $obj = $this->montarRegistroSupervisor($fws_);
            $atividades = isset($fws_['atividades_crianca']) ? $fws_['atividades_crianca'] : '';
            if (is_array($atividades)) $atividades = implode(',', $atividades);
            $item = array(
                'cod_aluno' => isset($fws_['cod_aluno']) ? $fws_['cod_aluno'] : '',
                'atividades_crianca' => $atividades,
                'descricao_crianca' => isset($fws_['descricao_crianca']) ? $this->normalizarTexto($fws_['descricao_crianca']) : '',
                'orientacoes_crianca' => isset($fws_['orientacoes_crianca']) ? $this->normalizarTexto($fws_['orientacoes_crianca']) : ''
            );
            $codItem = !empty($fws_['cod_registro_crianca']) ? intval($fws_['cod_registro_crianca']) : null;
            $obj->salvarItemCrianca($item, $codItem, $debug);
        } catch (OKException $e) {
            $ok = true; $msg = $e->getMessage();
        } catch (Exception $e) {
            $msg = $e->getMessage();
        }
        echo json_encode(array('statusMessage' => $ok, 'message' => $msg));
        exit();
    }

    public function excluirItemCrianca($fws_, $debug = false) {
        $ok = false; $msg = '';
        try {
            if ($this->isAdmin()) {
                throw new Exception("Coordenadores e gerentes nao podem excluir.");
            }
            $obj = new RegistroOrientacao();
            $obj->excluirItemCrianca(isset($fws_['cod_registro_crianca']) ? $fws_['cod_registro_crianca'] : '', $debug);
        } catch (OKException $e) {
            $ok = true; $msg = $e->getMessage();
        } catch (Exception $e) {
            $msg = $e->getMessage();
        }
        echo json_encode(array('statusMessage' => $ok, 'message' => $msg));
        exit();
    }

    public function carregarItemCrianca($fws_, $debug = false) {
        try {
            $obj = new RegistroOrientacao();
            $item = $obj->getItemCriancaById(isset($fws_['cod_registro_crianca']) ? $fws_['cod_registro_crianca'] : '', $debug);
            echo json_encode(array('statusMessage' => $item ? true : false, 'item' => $item));
        } catch (Exception $e) {
            echo json_encode(array('statusMessage' => false, 'message' => $e->getMessage()));
        }
        exit();
    }

    // --- Exclusão do registro-dia ---
    // Nunca permite cascade. Só exclui se o dia não tiver filhos (PES ou crianças).
    // Se tiver filhos, orienta o usuário a excluir os itens primeiro ou solicitar ao administrador.

    public function excluirRegistro($fws_, $debug = false) {
        $ok = false; $msg = '';
        try {
            if ($this->isAdmin()) {
                throw new Exception("Coordenadores e gerentes nao excluem registros pela interface. Solicite ao suporte.");
            }
            $obj = new RegistroOrientacao();
            $obj->setCodRegistro($fws_['codRegistro']);
            $obj->excluirSemFilhos($debug);
        } catch (OKException $e) {
            $ok = true; $msg = $e->getMessage();
        } catch (Exception $e) {
            $msg = $e->getMessage();
        }
        echo json_encode(array('statusMessage' => $ok, 'message' => $msg));
        exit();
    }

    // --- Histórico (listagem por supervisor) ---

    public function carregarTabela($fws_, $debug = false) {
        try {
            $codSupervisorFiltro = $this->codSupervisor;
            if ($this->isAdmin() && !empty($fws_['codSupervisorFiltro'])) {
                $codSupervisorFiltro = $fws_['codSupervisorFiltro'];
            }
            if (empty($codSupervisorFiltro)) {
                echo '<tr><td colspan="5">Selecione um supervisor para visualizar os registros.</td></tr>';
                exit();
            }
            $obj = new RegistroOrientacao();
            $obj->setCodSupervisor($codSupervisorFiltro);
            $registros = $obj->getListaRegistrosBySupervisor($debug);
            if (empty($registros)) {
                echo '<tr><td colspan="5">Nenhum registro encontrado.</td></tr>';
                exit();
            }
            $isAdmin = $this->isAdmin();
            $mesAtual = '';
            $labelsAtividades = self::getLabelsAtividadesDia();
            foreach ($registros as $reg) {
                $parts = explode("-", $reg['data_registro']);
                $mesAno = $parts[0] . '-' . $parts[1];
                if ($mesAtual != $mesAno) {
                    $mesAtual = $mesAno;
                    $mesNome = getMonthName($parts[1]);
                    ?>
                    <tr class="ro-separador-mes"><td colspan="5" style="background:#fafbfc; font-size:12px; font-weight:600; color:#777; text-transform:uppercase; letter-spacing:0.3px; padding:8px 12px; border-top:1px solid #eee; border-bottom:1px solid #eee;"><?php echo $mesNome ?> / <?php echo $parts[0] ?></td></tr>
                    <?php
                }
                $atividadesMarcadas = array();
                if (!empty($reg['atividades_dia'])) {
                    $codigos = explode(',', $reg['atividades_dia']);
                    foreach ($codigos as $cod) {
                        $cod = trim($cod);
                        if (isset($labelsAtividades[$cod])) {
                            $atividadesMarcadas[] = $labelsAtividades[$cod];
                        }
                    }
                }
                $resumoAtividades = !empty($atividadesMarcadas) ? implode(', ', $atividadesMarcadas) : '-';
                $qtdPes = intval($reg['qtd_pes']);
                $qtdCrianca = intval($reg['qtd_crianca']);
                ?>
                <tr id="<?php echo $reg['cod_registro'] ?>" data-data="<?php echo $reg['data_registro'] ?>">
                    <td>
                    <?php if (!$isAdmin) { ?>
                        <span class="btn-acao abrir-dia" title="Abrir"><i class="fa fa-folder-open"></i></span>
                        <span class="btn-acao btn-acao-excluir excluir" title="Excluir (apenas se vazio)"><i class="fa fa-trash"></i></span>
                    <?php } else { ?>
                        <span class="btn-acao visualizar" title="Visualizar"><i class="fa fa-eye"></i></span>
                    <?php } ?>
                    </td>
                    <td><?php echo formatDateView($reg['data_registro']) ?></td>
                    <td style="font-size:12px;"><?php echo $resumoAtividades ?></td>
                    <td class="text-center"><?php echo $qtdPes > 0 ? $qtdPes : '-' ?></td>
                    <td class="text-center"><?php echo $qtdCrianca > 0 ? $qtdCrianca : '-' ?></td>
                </tr>
                <?php
            }
            echo "<tr class='tbl-fin-total'><td colspan='5' style='text-align:right; color:#999; font-size:12px; padding:8px 10px; border-top:1px solid #eee;'>Total de registros: " . count($registros) . "</td></tr>";
        } catch (Exception $e) {
            echo '<tr><td colspan="5">' . ($e->getMessage() ?: 'Nenhum registro encontrado.') . '</td></tr>';
        }
    }

    // Visualização agregada (admin / histórico)
    public function visualizarRegistro($fws_, $debug = false) {
        try {
            $obj = new RegistroOrientacao();
            $obj->setCodRegistro($fws_['codRegistro']);
            $obj->getRegistroOrientacaoById($debug);
            $itensPes = $obj->getItensPesByRegistro($debug);
            $itensCrianca = $obj->getItensCriancaByRegistro($debug);
            echo json_encode(array(
                'statusMessage' => true,
                'codRegistro' => $obj->getCodRegistro(),
                'dataRegistro' => $obj->getDataRegistro(),
                'nomeSupervisor' => $obj->getNomeSupervisor($debug),
                'atividadesDia' => $obj->getAtividadesDia(),
                'descricaoDia' => $obj->getDescricaoDia(),
                'itensPes' => $itensPes,
                'itensCrianca' => $itensCrianca
            ));
        } catch (Exception $e) {
            echo json_encode(array('statusMessage' => false, 'message' => $e->getMessage()));
        }
        exit();
    }

    public function carregarListaPes($fws_, $debug = false) {
        try {
            $codSup = $this->isAdmin() && !empty($fws_['codSupervisorFiltro'])
                ? $fws_['codSupervisorFiltro'] : $this->codSupervisor;
            $obj = new RegistroOrientacao();
            $lista = $obj->getListaPesDasEscolas($codSup, $debug);
            echo json_encode(array('statusMessage' => true, 'lista' => $lista));
        } catch (Exception $e) {
            echo json_encode(array('statusMessage' => false, 'message' => $e->getMessage()));
        }
        exit();
    }

    public function carregarListaCriancas($fws_, $debug = false) {
        try {
            $codSup = $this->isAdmin() && !empty($fws_['codSupervisorFiltro'])
                ? $fws_['codSupervisorFiltro'] : $this->codSupervisor;
            $obj = new RegistroOrientacao();
            $lista = $obj->getListaCriancasDasEscolas($codSup, $debug);
            echo json_encode(array('statusMessage' => true, 'lista' => $lista));
        } catch (Exception $e) {
            echo json_encode(array('statusMessage' => false, 'message' => $e->getMessage()));
        }
        exit();
    }

    public function exportarPlanilha($fws_, $debug = false) {
        try {
            if (!$this->isAdmin()) {
                throw new Exception("Apenas coordenadores e gerentes podem exportar.");
            }
            $codSupervisor = isset($fws_['codSupervisorFiltro']) ? intval($fws_['codSupervisorFiltro']) : 0;
            $dataInicio = isset($fws_['dataInicio']) ? trim($fws_['dataInicio']) : '';
            $dataFim = isset($fws_['dataFim']) ? trim($fws_['dataFim']) : '';
            if (empty($dataInicio) || empty($dataFim)) {
                throw new Exception("Informe a data inicial e a data final.");
            }

            $obj = new RegistroOrientacao();
            if ($codSupervisor > 0) {
                $obj->setCodSupervisor($codSupervisor);
                $nomeSupervisor = $obj->getNomeSupervisor($debug);
            } else {
                $nomeSupervisor = 'todos';
            }
            $linhas = $obj->getLinhasParaExportacao($codSupervisor, $dataInicio, $dataFim, $debug);

            $labelsDia = self::getLabelsAtividadesDia();
            $labelsPes = self::getLabelsAtividadesPes();

            $slug = preg_replace('/[^A-Za-z0-9]+/', '_', $nomeSupervisor);
            $slug = trim($slug, '_');
            if (empty($slug)) { $slug = 'supervisor'; }
            $nomeArquivo = sprintf('registros_%s_%s_a_%s.xlsx', $slug, $dataInicio, $dataFim);

            // PhpSpreadsheet vive em pasta isolada para nao afetar o composer principal.
            $autoload = dirname(__FILE__) . '/../lib/spreadsheet/vendor/autoload.php';
            if (!file_exists($autoload)) {
                throw new Exception("Biblioteca PhpSpreadsheet nao encontrada no servidor.");
            }
            require_once($autoload);

            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Registros');

            $cabecalhos = array('Data', 'Supervisor', 'Tipo', 'PES', 'Atividades', 'Descricao', 'Orientacoes ao PES');
            foreach ($cabecalhos as $i => $titulo) {
                $col = chr(ord('A') + $i);
                $sheet->setCellValue($col . '1', $titulo);
            }

            // Larguras (em "characters") aproximando proporcionalmente
            $larguras = array('A' => 12, 'B' => 32, 'C' => 7, 'D' => 32, 'E' => 50, 'F' => 60, 'G' => 60);
            foreach ($larguras as $col => $w) {
                $sheet->getColumnDimension($col)->setWidth($w);
            }

            // Estilo do cabecalho
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

            // Linhas de dados (a partir da 2)
            $linhaAtual = 2;
            foreach ($linhas as $linha) {
                $atividadesLegiveis = $this->traduzirAtividades(
                    $linha['atividades'],
                    $linha['tipo'] === 'DIA' ? $labelsDia : $labelsPes
                );
                $sheet->setCellValue('A' . $linhaAtual, formatDateView($linha['data_registro']));
                $sheet->setCellValue('B' . $linhaAtual, $linha['nome_supervisor']);
                $sheet->setCellValue('C' . $linhaAtual, $linha['tipo']);
                $sheet->setCellValue('D' . $linhaAtual, $linha['nome_pes']);
                $sheet->setCellValue('E' . $linhaAtual, $atividadesLegiveis);
                $sheet->setCellValue('F' . $linhaAtual, $linha['tipo'] === 'PES' ? $linha['descricao'] : '');
                $sheet->setCellValue('G' . $linhaAtual, $linha['orientacoes_pes']);

                if ($linha['tipo'] === 'DIA') {
                    // Linha DIA recebe fundo claro pra destacar do PES
                    $sheet->getStyle('A' . $linhaAtual . ':G' . $linhaAtual)->applyFromArray(array(
                        'fill' => array(
                            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                            'startColor' => array('rgb' => 'EFF3F8')
                        ),
                        'font' => array('italic' => true),
                    ));
                }
                $linhaAtual++;
            }

            // Alinhamento e wrap-text geral da area de dados
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

    // Converte string "COD1,COD2" em "Label1, Label2" usando o dicionario passado.
    // Codigos desconhecidos ficam como o proprio codigo.
    private function traduzirAtividades($atividadesCsv, $labels) {
        if (empty($atividadesCsv)) return '';
        $codigos = array_map('trim', explode(',', $atividadesCsv));
        $nomes = array();
        foreach ($codigos as $cod) {
            if ($cod === '') continue;
            $nome = isset($labels[$cod]) ? $labels[$cod] : $cod;
            $nomes[] = html_entity_decode($nome, ENT_QUOTES, 'UTF-8');
        }
        return implode(', ', $nomes);
    }

    public function carregarListaSupervisores($fws_, $debug = false) {
        try {
            $obj = new RegistroOrientacao();
            $lista = $obj->getListaSupervisores($debug);
            echo json_encode(array('statusMessage' => true, 'lista' => $lista));
        } catch (Exception $e) {
            echo json_encode(array('statusMessage' => false, 'message' => $e->getMessage()));
        }
        exit();
    }

    // --- Consulta consolidada (admin): por PES / por Aluno ---
    // Traz para o coordenador/gerente a mesma informacao que hoje so aparece
    // dia a dia, agrupada por PES ou por aluno.

    public function consultarPesComOrientacoes($fws_, $debug = false) {
        try {
            if (!$this->isAdmin()) {
                throw new Exception("Consulta disponivel apenas para coordenadores e gerentes.");
            }
            $obj = new RegistroOrientacao();
            $lista = $obj->getListaPesComOrientacoes($debug);
            echo json_encode(array('statusMessage' => true, 'lista' => $lista));
        } catch (Exception $e) {
            echo json_encode(array('statusMessage' => false, 'message' => $e->getMessage()));
        }
        exit();
    }

    public function consultarAlunosComOrientacoes($fws_, $debug = false) {
        try {
            if (!$this->isAdmin()) {
                throw new Exception("Consulta disponivel apenas para coordenadores e gerentes.");
            }
            $obj = new RegistroOrientacao();
            $lista = $obj->getListaAlunosComOrientacoes($debug);
            echo json_encode(array('statusMessage' => true, 'lista' => $lista));
        } catch (Exception $e) {
            echo json_encode(array('statusMessage' => false, 'message' => $e->getMessage()));
        }
        exit();
    }

    public function consultarOrientacoesPorPes($fws_, $debug = false) {
        try {
            if (!$this->isAdmin()) {
                throw new Exception("Consulta disponivel apenas para coordenadores e gerentes.");
            }
            $codPes = isset($fws_['codPes']) ? intval($fws_['codPes']) : 0;
            if ($codPes <= 0) {
                throw new Exception("Selecione um PES.");
            }
            $obj = new RegistroOrientacao();
            $lista = $obj->getOrientacoesRecebidas($codPes, $debug);
            echo json_encode(array('statusMessage' => true, 'lista' => $lista));
        } catch (Exception $e) {
            echo json_encode(array('statusMessage' => false, 'message' => $e->getMessage()));
        }
        exit();
    }

    public function consultarOrientacoesPorAluno($fws_, $debug = false) {
        try {
            if (!$this->isAdmin()) {
                throw new Exception("Consulta disponivel apenas para coordenadores e gerentes.");
            }
            // cod_aluno é UUID (string), não inteiro — não usar intval.
            $codAluno = isset($fws_['codAluno']) ? trim($fws_['codAluno']) : '';
            if ($codAluno === '') {
                throw new Exception("Selecione um aluno.");
            }
            $obj = new RegistroOrientacao();
            $lista = $obj->getOrientacoesPorAluno($codAluno, $debug);
            echo json_encode(array('statusMessage' => true, 'lista' => $lista));
        } catch (Exception $e) {
            echo json_encode(array('statusMessage' => false, 'message' => $e->getMessage()));
        }
        exit();
    }

    public static function getLabelsAtividadesDia() {
        return array(
            'REUNIAO_PROFESSOR_REGENTE' => 'Reuni&atilde;o/orienta&ccedil;&atilde;o com Professor(a) Regente',
            'REUNIAO_PROFESSOR_AEE' => 'Reuni&atilde;o/orienta&ccedil;&atilde;o com Professor(a) do AEE',
            'REUNIAO_COORDENACAO_DIRECAO' => 'Reuni&atilde;o/orienta&ccedil;&atilde;o com coordena&ccedil;&atilde;o e/ou dire&ccedil;&atilde;o da escola',
            'REUNIAO_PAIS_RESPONSAVEIS' => 'Reuni&atilde;o/orienta&ccedil;&atilde;o com pais/respons&aacute;veis pelas crian&ccedil;as',
            'REUNIAO_PROFISSIONAIS_CLINICO' => 'Reuni&atilde;o/orienta&ccedil;&atilde;o com profissionais de atendimento cl&iacute;nico externo',
            'REUNIAO_GERENCIA_PROREDE' => 'Reuni&atilde;o com ger&ecirc;ncia/coordena&ccedil;&atilde;o do Pr&oacute;-rede',
            'REPASSES_COMUNICACAO' => 'Repasses/comunica&ccedil;&atilde;o institucional',
            'ELABORACAO_MATERIAIS' => 'Elabora&ccedil;&atilde;o/prepara&ccedil;&atilde;o de materiais de interven&ccedil;&atilde;o',
            'OUTROS' => 'Outros'
        );
    }

    public static function getLabelsAtividadesPes() {
        return array(
            'OBSERVACAO_DIRETA' => 'Observa&ccedil;&atilde;o direta da atua&ccedil;&atilde;o do PES',
            'ORIENTACAO_ATUACAO' => 'Orienta&ccedil;&atilde;o sobre a atua&ccedil;&atilde;o do PES',
            'REMANEJAMENTO' => 'Remanejamento de PES',
            'PLANO_ACAO' => 'Elabora&ccedil;&atilde;o de plano de a&ccedil;&atilde;o de PES',
            'OUTROS' => 'Outros'
        );
    }

    public static function getLabelsAtividadesCrianca() {
        return array(
            'ACOMPANHAMENTO_PROVISORIO' => 'Acompanhamento provis&oacute;rio da crian&ccedil;a',
            'OBSERVACAO_DIRETA' => 'Observa&ccedil;&atilde;o direta da crian&ccedil;a',
            'MANEJO_COMPORTAMENTAL' => 'Manejo comportamental espec&iacute;fico',
            'MANEJO_CRISE' => 'Manejo em caso de crise',
            'OUTROS' => 'Outros'
        );
    }

}

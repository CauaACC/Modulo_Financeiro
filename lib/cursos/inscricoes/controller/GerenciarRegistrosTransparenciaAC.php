<?php

class GerenciarRegistrosTransparenciaAC
{
    function __construct()
    {
        $cod_usuario = $_SESSION['_cod_usuario'];
        if (!FWA_Autenticacao($cod_usuario, enumSistema::SIS_CONTROLE_FINANCEIRO, 5, false, false)
            && !FWA_Autenticacao($cod_usuario, enumSistema::SIS_CARGA_TRANSPARENCIA, 5, false, false)) {
            throw new Exception("Acesso restrito. Permissão necessária: Controle Financeiro ou Carga Transparência.");
        }
    }

    // ==================== TABELA DE PROJETOS ====================

    public function carregarTabelaProjetos($fws_, $debug = false)
    {
        try {
            $filtros = array();
            if (!empty($fws_['status_publicacao'])) $filtros['status_publicacao'] = $fws_['status_publicacao'];
            if (!empty($fws_['cod_arquivo'])) $filtros['cod_arquivo'] = intval($fws_['cod_arquivo']);
            if (!empty($fws_['busca'])) $filtros['busca'] = $fws_['busca'];

            $objTransparencia = new Transparencia();
            $objTransparencia->setGestor(WB_GRUPO);
            $lista = $objTransparencia->listarProjetosFiltrados($filtros, $debug);

            if (!$lista || count($lista) == 0) {
                echo '<div class="text-center" style="padding:30px 10px; color:#999; font-style:italic;">Nenhum projeto encontrado.</div>';
                return;
            }

            $ii = count($lista);
            ?>
            <table class="tbl-fin" id="tblRegistrosProjetos">
                <thead>
                    <tr>
                        <th style="width:30px;"><input type="checkbox" id="chkHeadProjeto" title="Selecionar todos"></th>
                        <th style="width:80px;">A&ccedil;&otilde;es</th>
                        <th data-col="2" style="cursor:pointer;">N&ordm; Projeto <span class="sort-arrow">&uarr;&darr;</span></th>
                        <th data-col="3" style="cursor:pointer;">Coordenador <span class="sort-arrow">&uarr;&darr;</span></th>
                        <th data-col="4" style="cursor:pointer;">E-Protocolo <span class="sort-arrow">&uarr;&darr;</span></th>
                        <th data-col="5" style="cursor:pointer;">Proposta <span class="sort-arrow">&uarr;&darr;</span></th>
                        <th data-col="6" style="cursor:pointer;">In&iacute;cio <span class="sort-arrow">&uarr;&darr;</span></th>
                        <th data-col="7" style="cursor:pointer;">Fim <span class="sort-arrow">&uarr;&darr;</span></th>
                        <th data-col="8" style="cursor:pointer;">Status <span class="sort-arrow">&uarr;&darr;</span></th>
                        <th data-col="9" style="cursor:pointer;">Arquivo <span class="sort-arrow">&uarr;&darr;</span></th>
                    </tr>
                </thead>
                <tbody>
            <?php
            foreach ($lista as $row) {
                $id = intval($row['id']);
                $statusPublicacao = $row['status_publicacao'];

                if ($statusPublicacao == 'PUBLICADO') {
                    $badgeStatus = '<span class="badge-tipo" style="cursor:pointer;" onclick="publicarRegistro(\'projeto\',' . $id . ',\'RASCUNHO\')" title="Clique para despublicar"><span class="dot dot-green"></span>Publicado <i class="fa fa-eye-slash" style="margin-left:4px; opacity:0.5;"></i></span>';
                } else {
                    $badgeStatus = '<span class="badge-tipo" style="cursor:pointer;" onclick="publicarRegistro(\'projeto\',' . $id . ',\'PUBLICADO\')" title="Clique para publicar"><span class="dot dot-orange"></span>Rascunho <i class="fa fa-eye" style="margin-left:4px; opacity:0.5;"></i></span>';
                }
                ?>
                <tr data-id="<?php echo $id ?>">
                    <td><input type="checkbox" class="chk-registro" value="<?php echo $id ?>"></td>
                    <td>
                        <span class="btn-acao" onclick="abrirEditarProjeto(<?php echo $id ?>)" title="Editar"><i class="fa fa-pencil"></i></span>
                    </td>
                    <td><?php echo htmlspecialchars($row['numero_projeto']) ?></td>
                    <td><?php echo htmlspecialchars(mb_convert_case($row['coordenador'], MB_CASE_TITLE, 'UTF-8')) ?></td>
                    <td><?php echo htmlspecialchars($row['e_protocolo']) ?></td>
                    <td><?php echo htmlspecialchars($row['proposta']) ?></td>
                    <td><?php echo !empty($row['data_inicio']) ? date('d/m/Y', strtotime($row['data_inicio'])) : '' ?></td>
                    <td><?php echo !empty($row['data_fim']) ? date('d/m/Y', strtotime($row['data_fim'])) : '' ?></td>
                    <td><?php echo $badgeStatus ?></td>
                    <td style="font-size:11px; color:#999;"><?php echo htmlspecialchars($row['arquivo_upload'] ?? '') ?></td>
                </tr>
                <?php
            }
            ?>
                </tbody>
                <tfoot><tr><td colspan="10" style="text-align:right; color:#999; font-size:12px; padding:8px 10px; border-top:1px solid #eee;">Total: <?php echo $ii ?> projeto<?php echo $ii != 1 ? 's' : '' ?></td></tr></tfoot>
            </table>
            <?php
        } catch (Exception $e) {
            echo '<div class="text-center text-muted" style="padding:30px 0;">' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    }

    // ==================== TABELA DE PAGAMENTOS ====================

    public function carregarTabelaPagamentos($fws_, $debug = false)
    {
        try {
            $filtros = array();
            if (!empty($fws_['status_publicacao'])) $filtros['status_publicacao'] = $fws_['status_publicacao'];
            if (!empty($fws_['cod_arquivo'])) $filtros['cod_arquivo'] = intval($fws_['cod_arquivo']);
            if (!empty($fws_['busca'])) $filtros['busca'] = $fws_['busca'];

            $objTransparencia = new Transparencia();
            $objTransparencia->setGestor(WB_GRUPO);
            $lista = $objTransparencia->listarPagamentosFiltrados($filtros, $debug);

            if (!$lista || count($lista) == 0) {
                echo '<div class="text-center" style="padding:30px 10px; color:#999; font-style:italic;">Nenhum pagamento encontrado.</div>';
                return;
            }

            $ii = count($lista);
            ?>
            <table class="tbl-fin" id="tblRegistrosPagamentos">
                <thead>
                    <tr>
                        <th style="width:30px;"><input type="checkbox" id="chkHeadPagamento" title="Selecionar todos"></th>
                        <th style="width:80px;">A&ccedil;&otilde;es</th>
                        <th data-col="2" style="cursor:pointer;">N&ordm; Projeto <span class="sort-arrow">&uarr;&darr;</span></th>
                        <th data-col="3" style="cursor:pointer;">Fornecedor <span class="sort-arrow">&uarr;&darr;</span></th>
                        <th data-col="4" style="cursor:pointer;">Valor Bruto <span class="sort-arrow">&uarr;&darr;</span></th>
                        <th data-col="5" style="cursor:pointer;">Valor L&iacute;quido <span class="sort-arrow">&uarr;&darr;</span></th>
                        <th data-col="6" style="cursor:pointer;">Vencimento <span class="sort-arrow">&uarr;&darr;</span></th>
                        <th data-col="7" style="cursor:pointer;">Servi&ccedil;o <span class="sort-arrow">&uarr;&darr;</span></th>
                        <th data-col="8" style="cursor:pointer;">CNPJ <span class="sort-arrow">&uarr;&darr;</span></th>
                        <th data-col="9" style="cursor:pointer;">Tipo RPA <span class="sort-arrow">&uarr;&darr;</span></th>
                        <th data-col="10" style="cursor:pointer;">Status <span class="sort-arrow">&uarr;&darr;</span></th>
                    </tr>
                </thead>
                <tbody>
            <?php
            foreach ($lista as $row) {
                $id = intval($row['id']);
                $statusPublicacao = $row['status_publicacao'];

                if ($statusPublicacao == 'PUBLICADO') {
                    $badgeStatus = '<span class="badge-tipo" style="cursor:pointer;" onclick="publicarRegistro(\'pagamento\',' . $id . ',\'RASCUNHO\')" title="Clique para despublicar"><span class="dot dot-green"></span>Publicado <i class="fa fa-eye-slash" style="margin-left:4px; opacity:0.5;"></i></span>';
                } else {
                    $badgeStatus = '<span class="badge-tipo" style="cursor:pointer;" onclick="publicarRegistro(\'pagamento\',' . $id . ',\'PUBLICADO\')" title="Clique para publicar"><span class="dot dot-orange"></span>Rascunho <i class="fa fa-eye" style="margin-left:4px; opacity:0.5;"></i></span>';
                }
                ?>
                <tr data-id="<?php echo $id ?>">
                    <td><input type="checkbox" class="chk-registro" value="<?php echo $id ?>"></td>
                    <td>
                        <span class="btn-acao" onclick="abrirEditarPagamento(<?php echo $id ?>)" title="Editar"><i class="fa fa-pencil"></i></span>
                    </td>
                    <td><?php echo htmlspecialchars($row['numero_projeto']) ?></td>
                    <td><?php echo htmlspecialchars(mb_convert_case($row['fornecedor'], MB_CASE_TITLE, 'UTF-8')) ?></td>
                    <td><?php echo htmlspecialchars($row['valor_bruto']) ?></td>
                    <td><?php echo htmlspecialchars($row['valor_liquido']) ?></td>
                    <td><?php echo !empty($row['vencimento']) ? date('d/m/Y', strtotime($row['vencimento'])) : '' ?></td>
                    <td><?php echo htmlspecialchars($row['servico']) ?></td>
                    <td><?php echo htmlspecialchars($row['cnpj']) ?></td>
                    <td><?php echo htmlspecialchars($row['tipo_rpa'] ?? '') ?></td>
                    <td><?php echo $badgeStatus ?></td>
                </tr>
                <?php
            }
            ?>
                </tbody>
                <tfoot><tr><td colspan="11" style="text-align:right; color:#999; font-size:12px; padding:8px 10px; border-top:1px solid #eee;">Total: <?php echo $ii ?> pagamento<?php echo $ii != 1 ? 's' : '' ?></td></tr></tfoot>
            </table>
            <?php
        } catch (Exception $e) {
            echo '<div class="text-center text-muted" style="padding:30px 0;">' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    }

    // ==================== EDITAR PROJETO ====================

    public function editarProjeto($fws_, $debug = false)
    {
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');

        try {
            $id = intval($fws_['id']);
            if (!$id) {
                throw new Exception('ID do projeto não informado.');
            }

            $objTransparencia = new Transparencia();
            $objTransparencia->setGestor(WB_GRUPO);
            $projeto = $objTransparencia->getProjetoById($id, $debug);

            if (!$projeto) {
                throw new Exception('Projeto não encontrado.');
            }

            echo json_encode(array(
                'statusMessage' => true,
                'message'       => '',
                'dados'         => $projeto
            ));
        } catch (Exception $e) {
            echo json_encode(array(
                'statusMessage' => false,
                'message'       => $e->getMessage()
            ));
        }
        exit();
    }

    // ==================== SALVAR PROJETO ====================

    public function salvarProjeto($fws_, $debug = false)
    {
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');

        try {
            $id = intval($fws_['id']);
            if (!$id) {
                throw new Exception('ID do projeto não informado.');
            }

            $objTransparencia = new Transparencia();
            $objTransparencia->setGestor(WB_GRUPO);
            $projeto = $objTransparencia->getProjetoById($id, $debug);

            if (!$projeto) {
                throw new Exception('Projeto não encontrado.');
            }

            // Atualizar campos via objeto Transparencia
            $objTransparencia->setId($id);
            $objTransparencia->setNumeroProjeto(isset($fws_['numero_projeto']) ? trim($fws_['numero_projeto']) : $projeto['numero_projeto']);
            $objTransparencia->setCoordenador(isset($fws_['coordenador']) ? trim($fws_['coordenador']) : $projeto['coordenador']);
            $objTransparencia->setEProtocolo(isset($fws_['e_protocolo']) ? trim($fws_['e_protocolo']) : $projeto['e_protocolo']);
            $objTransparencia->setProposta(isset($fws_['proposta']) ? trim($fws_['proposta']) : $projeto['proposta']);
            $objTransparencia->setDataInicio(isset($fws_['data_inicio']) ? trim($fws_['data_inicio']) : $projeto['data_inicio']);
            $objTransparencia->setDataFim(isset($fws_['data_fim']) ? trim($fws_['data_fim']) : $projeto['data_fim']);

            $ok = false;
            $ex = null;
            try {
                $objTransparencia->salvarOuAtualizarProjetos($debug);
            } catch (OKException $e) {
                $ok = true;
                $ex = $e;
            } catch (Exception $e) {
                $ok = false;
                $ex = $e;
            }

            echo json_encode(array(
                'statusMessage' => $ok,
                'message'       => $ex ? $ex->getMessage() : 'Projeto salvo com sucesso.'
            ));
        } catch (Exception $e) {
            echo json_encode(array(
                'statusMessage' => false,
                'message'       => $e->getMessage()
            ));
        }
        exit();
    }

    // ==================== EDITAR PAGAMENTO ====================

    public function editarPagamento($fws_, $debug = false)
    {
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');

        try {
            $id = intval($fws_['id']);
            if (!$id) {
                throw new Exception('ID do pagamento não informado.');
            }

            $objTransparencia = new Transparencia();
            $objTransparencia->setGestor(WB_GRUPO);
            $pagamento = $objTransparencia->getPagamentoById($id, $debug);

            if (!$pagamento) {
                throw new Exception('Pagamento não encontrado.');
            }

            echo json_encode(array(
                'statusMessage' => true,
                'message'       => '',
                'dados'         => $pagamento
            ));
        } catch (Exception $e) {
            echo json_encode(array(
                'statusMessage' => false,
                'message'       => $e->getMessage()
            ));
        }
        exit();
    }

    // ==================== SALVAR PAGAMENTO ====================

    public function salvarPagamento($fws_, $debug = false)
    {
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');

        try {
            $id = intval($fws_['id']);
            if (!$id) {
                throw new Exception('ID do pagamento não informado.');
            }

            $objTransparencia = new Transparencia();
            $objTransparencia->setGestor(WB_GRUPO);
            $pagamento = $objTransparencia->getPagamentoById($id, $debug);

            if (!$pagamento) {
                throw new Exception('Pagamento não encontrado.');
            }

            // Atualizar campos via objeto Transparencia
            $objTransparencia->setId($id);
            $objTransparencia->setNumeroProjeto(isset($fws_['numero_projeto']) ? trim($fws_['numero_projeto']) : $pagamento['numero_projeto']);
            $objTransparencia->setFornecedor(isset($fws_['fornecedor']) ? trim($fws_['fornecedor']) : $pagamento['fornecedor']);
            $objTransparencia->setValorBruto(isset($fws_['valor_bruto']) ? trim($fws_['valor_bruto']) : $pagamento['valor_bruto']);
            $objTransparencia->setValorLiquido(isset($fws_['valor_liquido']) ? trim($fws_['valor_liquido']) : $pagamento['valor_liquido']);
            $objTransparencia->setVencimento(isset($fws_['vencimento']) ? trim($fws_['vencimento']) : $pagamento['vencimento']);
            $objTransparencia->setServico(isset($fws_['servico']) ? trim($fws_['servico']) : $pagamento['servico']);
            $objTransparencia->setCnpj(isset($fws_['cnpj']) ? trim($fws_['cnpj']) : $pagamento['cnpj']);
            $objTransparencia->setTipoRpa(isset($fws_['tipo_rpa']) ? trim($fws_['tipo_rpa']) : $pagamento['tipo_rpa']);

            $ok = false;
            $ex = null;
            try {
                $objTransparencia->salvarOuAtualizarPagamentos($debug);
            } catch (OKException $e) {
                $ok = true;
                $ex = $e;
            } catch (Exception $e) {
                $ok = false;
                $ex = $e;
            }

            echo json_encode(array(
                'statusMessage' => $ok,
                'message'       => $ex ? $ex->getMessage() : 'Pagamento salvo com sucesso.'
            ));
        } catch (Exception $e) {
            echo json_encode(array(
                'statusMessage' => false,
                'message'       => $e->getMessage()
            ));
        }
        exit();
    }

    // ==================== PUBLICAR REGISTRO (individual) ====================

    public function publicarRegistro($fws_, $debug = false)
    {
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');

        try {
            $tipo = isset($fws_['tipo']) ? trim($fws_['tipo']) : '';
            $id   = intval($fws_['id']);

            if (!in_array($tipo, array('projetos', 'pagamentos'))) {
                throw new Exception('Tipo inválido. Informe "projetos" ou "pagamentos".');
            }
            if (!$id) {
                throw new Exception('ID do registro não informado.');
            }

            $novoStatus = isset($fws_['status']) ? trim($fws_['status']) : 'PUBLICADO';
            if (!in_array($novoStatus, array('PUBLICADO', 'RASCUNHO'))) {
                throw new Exception('Status inválido.');
            }

            $tabela = ($tipo == 'projetos') ? 'wp_projetos' : 'wp_pagamentos';

            $objTransparencia = new Transparencia();
            $objTransparencia->setGestor(WB_GRUPO);
            $resultado = $objTransparencia->atualizarStatusPublicacao($tabela, $id, $novoStatus, $debug);

            echo json_encode(array(
                'statusMessage' => $resultado ? true : false,
                'message'       => $resultado ? 'Status atualizado com sucesso.' : 'Não foi possível atualizar o status.'
            ));
        } catch (Exception $e) {
            echo json_encode(array(
                'statusMessage' => false,
                'message'       => $e->getMessage()
            ));
        }
        exit();
    }

    // ==================== PUBLICAR EM LOTE ====================

    public function publicarEmLote($fws_, $debug = false)
    {
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');

        try {
            $tipo = isset($fws_['tipo']) ? trim($fws_['tipo']) : '';
            $ids  = isset($fws_['ids']) ? $fws_['ids'] : array();

            // Aceitar singular ou plural
            if ($tipo == 'projeto') $tipo = 'projetos';
            if ($tipo == 'pagamento') $tipo = 'pagamentos';

            if (!in_array($tipo, array('projetos', 'pagamentos'))) {
                throw new Exception('Tipo inválido. Informe "projetos" ou "pagamentos".');
            }

            // Decodificar JSON se veio como string
            if (is_string($ids)) {
                $ids = json_decode($ids, true);
            }
            if (!is_array($ids) || count($ids) == 0) {
                throw new Exception('Nenhum registro selecionado.');
            }

            $novoStatus = isset($fws_['status']) ? trim($fws_['status']) : 'PUBLICADO';
            if (!in_array($novoStatus, array('PUBLICADO', 'RASCUNHO'))) {
                throw new Exception('Status inválido.');
            }

            // Sanitizar ids
            $ids = array_map('intval', $ids);
            $ids = array_filter($ids, function ($v) { return $v > 0; });

            $tabela = ($tipo == 'projetos') ? 'wp_projetos' : 'wp_pagamentos';

            $objTransparencia = new Transparencia();
            $objTransparencia->setGestor(WB_GRUPO);
            $resultado = $objTransparencia->atualizarStatusPublicacaoEmLote($tabela, $ids, $novoStatus, $debug);

            echo json_encode(array(
                'statusMessage' => $resultado ? true : false,
                'message'       => $resultado ? count($ids) . ' registro(s) atualizado(s) com sucesso.' : 'Não foi possível atualizar os registros.',
                'count'         => count($ids)
            ));
        } catch (Exception $e) {
            echo json_encode(array(
                'statusMessage' => false,
                'message'       => $e->getMessage()
            ));
        }
        exit();
    }

    // ==================== PUBLICAR POR ARQUIVO ====================

    public function publicarPorArquivo($fws_, $debug = false)
    {
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');

        try {
            $codArquivo = intval($fws_['codArquivo']);
            if (!$codArquivo) {
                throw new Exception('Código do arquivo não informado.');
            }

            $novoStatus = isset($fws_['status']) ? trim($fws_['status']) : 'PUBLICADO';
            if (!in_array($novoStatus, array('PUBLICADO', 'RASCUNHO'))) {
                throw new Exception('Status inválido.');
            }

            $objTransparencia = new Transparencia();
            $objTransparencia->setGestor(WB_GRUPO);

            $resProjetos   = $objTransparencia->atualizarStatusPublicacaoPorArquivo('wp_projetos', $codArquivo, $novoStatus, $debug);
            $resPagamentos = $objTransparencia->atualizarStatusPublicacaoPorArquivo('wp_pagamentos', $codArquivo, $novoStatus, $debug);

            echo json_encode(array(
                'statusMessage' => ($resProjetos || $resPagamentos) ? true : false,
                'message'       => 'Registros do arquivo atualizados com sucesso.'
            ));
        } catch (Exception $e) {
            echo json_encode(array(
                'statusMessage' => false,
                'message'       => $e->getMessage()
            ));
        }
        exit();
    }

    // ==================== REJEITAR REGISTRO (desativar) ====================

    public function rejeitarRegistro($fws_, $debug = false)
    {
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');

        try {
            $tipo = isset($fws_['tipo']) ? trim($fws_['tipo']) : '';

            // Aceitar singular ou plural
            if ($tipo == 'projeto') $tipo = 'projetos';
            if ($tipo == 'pagamento') $tipo = 'pagamentos';

            if (!in_array($tipo, array('projetos', 'pagamentos'))) {
                throw new Exception('Tipo inválido. Informe "projetos" ou "pagamentos".');
            }

            // Aceitar id individual ou ids em lote (JSON)
            $ids = array();
            if (!empty($fws_['ids'])) {
                $ids = is_string($fws_['ids']) ? json_decode($fws_['ids'], true) : $fws_['ids'];
            } elseif (!empty($fws_['id'])) {
                $ids = array(intval($fws_['id']));
            }
            $ids = array_map('intval', $ids);
            $ids = array_filter($ids, function ($v) { return $v > 0; });

            if (count($ids) == 0) {
                throw new Exception('Nenhum registro selecionado.');
            }

            $tabela = ($tipo == 'projetos') ? 'wp_projetos' : 'wp_pagamentos';

            $objTransparencia = new Transparencia();
            $objTransparencia->setGestor(WB_GRUPO);
            $resultado = $objTransparencia->atualizarStatusPublicacaoEmLote($tabela, $ids, 'DESATIVADA', $debug);

            echo json_encode(array(
                'statusMessage' => $resultado ? true : false,
                'message'       => $resultado ? count($ids) . ' registro(s) rejeitado(s) com sucesso.' : 'Não foi possível rejeitar os registros.'
            ));
        } catch (Exception $e) {
            echo json_encode(array(
                'statusMessage' => false,
                'message'       => $e->getMessage()
            ));
        }
        exit();
    }

    // ==================== DASHBOARD ====================

    public function carregarDadosDashboard($fws_, $debug = false)
    {
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        try {
            $ano = isset($fws_['ano']) ? trim($fws_['ano']) : '';
            $mes = isset($fws_['mes']) ? trim($fws_['mes']) : '';

            $obj = new Transparencia();
            $obj->setGestor(WB_GRUPO);
            $dados = $obj->carregarDadosDashboard($ano, $mes, $debug);

            if (!$dados) {
                echo json_encode(array('statusMessage' => false, 'message' => 'Erro ao carregar dados do dashboard.'));
                exit();
            }

            echo json_encode(array(
                'statusMessage' => true,
                'projetos' => $dados['projetos'],
                'pagamentos' => $dados['pagamentos'],
                'financeiro' => $dados['financeiro'],
                'topCoordenadores' => $dados['topCoordenadores'],
                'ultimasCargas' => $dados['ultimasCargas']
            ));
        } catch (Exception $e) {
            echo json_encode(array('statusMessage' => false, 'message' => $e->getMessage()));
        }
        exit();
    }

    // ==================== CONTADORES ====================

    public function carregarContadores($fws_, $debug = false)
    {
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');

        try {
            $objTransparencia = new Transparencia();
            $objTransparencia->setGestor(WB_GRUPO);

            $contProjetos   = $objTransparencia->contarRegistrosPorStatus('wp_projetos', $debug);
            $contPagamentos = $objTransparencia->contarRegistrosPorStatus('wp_pagamentos', $debug);

            echo json_encode(array(
                'statusMessage'        => true,
                'message'              => '',
                'projetos_rascunho'    => intval($contProjetos['RASCUNHO'] ?? 0),
                'projetos_publicado'   => intval($contProjetos['PUBLICADO'] ?? 0),
                'pagamentos_rascunho'  => intval($contPagamentos['RASCUNHO'] ?? 0),
                'pagamentos_publicado' => intval($contPagamentos['PUBLICADO'] ?? 0)
            ));
        } catch (Exception $e) {
            echo json_encode(array(
                'statusMessage' => false,
                'message'       => $e->getMessage()
            ));
        }
        exit();
    }

    // ==================== ARQUIVOS DE CARGA ====================

    public function carregarArquivosCarga($fws_, $debug = false)
    {
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');

        try {
            $objTransparencia = new Transparencia();
            $objTransparencia->setGestor(WB_GRUPO);
            $lista = $objTransparencia->listarArquivosCarga($debug);

            echo json_encode(array(
                'statusMessage' => true,
                'message'       => '',
                'arquivos'      => $lista ? $lista : array()
            ));
        } catch (Exception $e) {
            echo json_encode(array(
                'statusMessage' => false,
                'message'       => $e->getMessage()
            ));
        }
        exit();
    }
}

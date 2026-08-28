<?php
// error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE & ~E_WARNING);
// ini_set("display_errors", "On");
include(dirname(__FILE__) .'/../include/functions/LeitorArquivo.php');

class GerenciarCargaTransparenciaAC {

    function __construct() {
        // Nao gatear quando chamado pela rota /api (webhook receberCargaAPI se autentica por api_token).
        if (!defined('FWA_API_EXEC')) { FWA_exigirPermissao(enumSistema::SIS_GERENCIAR_ADM_GERAL); }
        $this->tiposPermitidos = array('csv', 'txt', 'xls');
        $this->tamanho = '30MB';
        $this->varDiretorio = WB_PATH_LOG_CARGA.'transparencia/';
        $this->prefixo = 'transparencia';
        // $this->arrayTipoArquivo = array(['PAGSEGURO','PAY-PAL','ITAUSHOPLINE','BOLETOSCAIXA','BOLETOUNIPRIME','BOLETOSISPRIME']);        
    }

    /**
     * Detecta a linha de cabeçalho real no CSV.
     * Procura por palavras-chave conhecidas dos cabeçalhos de projetos e pagamentos,
     * percorrendo TODAS as linhas do arquivo (não apenas as 5 primeiras).
     * Se nenhuma linha com palavras-chave for encontrada, retorna a primeira linha não-vazia.
     */
    private function detectarLinhaCabecalho($listaLinhas, $totalLinhas) {
        // Palavras-chave que identificam linhas de cabeçalho reais
        $palavrasChavePagamento = ['valor bruto', 'vencimento', 'valor liquido', 'valor líquido', 'servico', 'serviço', 'fichario', 'fichário', 'tipo de rpa', 'tipo rpa'];
        $palavrasChaveProjeto   = ['coordenador', 'e-protocolo', 'eprotocolo', 'proposta', 'data inicio', 'data início'];
        $palavrasChaveBolsa     = ['nome;vinculo', 'nome;vínculo'];

        $primeiraNaoVazia = null;
        $primeiraNaoVaziaIdx = null;

        for ($h = 0; $h < $totalLinhas; $h++) {
            $linhaLimpa = trim(str_replace(';', '', $listaLinhas[$h]));
            if ($linhaLimpa === '') continue;

            if ($primeiraNaoVazia === null) {
                $primeiraNaoVazia = $listaLinhas[$h];
                $primeiraNaoVaziaIdx = $h;
            }

            $linhaLower = mb_strtolower(trim($listaLinhas[$h]), 'UTF-8');
            // Normalizar caracteres especiais para comparação
            $linhaNorm = str_replace(
                ['á','é','í','ó','ú','ã','õ','â','ê','ô','ç','à','ü','ñ','º','°','ª'],
                ['a','e','i','o','u','a','o','a','e','o','c','a','u','n','','',''],
                $linhaLower
            );

            // Verificar palavras-chave de pagamento (precisa ter pelo menos 3 matches)
            $matchesPagamento = 0;
            foreach ($palavrasChavePagamento as $palavra) {
                if (strpos($linhaNorm, $palavra) !== false) {
                    $matchesPagamento++;
                }
            }
            if ($matchesPagamento >= 3) {
                return ['indice' => $h, 'linha' => $listaLinhas[$h]];
            }

            // Verificar palavras-chave de projeto (precisa ter pelo menos 2 matches)
            $matchesProjeto = 0;
            foreach ($palavrasChaveProjeto as $palavra) {
                if (strpos($linhaNorm, $palavra) !== false) {
                    $matchesProjeto++;
                }
            }
            if ($matchesProjeto >= 2) {
                return ['indice' => $h, 'linha' => $listaLinhas[$h]];
            }

            // Verificar cabeçalho de bolsa
            foreach ($palavrasChaveBolsa as $palavra) {
                if (strpos($linhaNorm, $palavra) !== false) {
                    return ['indice' => $h, 'linha' => $listaLinhas[$h]];
                }
            }
        }

        // Fallback: primeira linha não-vazia
        $idx = $primeiraNaoVaziaIdx !== null ? $primeiraNaoVaziaIdx : 0;
        return ['indice' => $idx, 'linha' => $primeiraNaoVazia !== null ? $primeiraNaoVazia : $listaLinhas[0]];
    }

    public function carregarArquivo($fws_, $debug = false) {
        $obj = new Arquivo();
        $obj->setCodArquivo($fws_['codArquivo']);
        // Transparencia usa gestor, nao cod_evento — carregarArquivoByCodArquivo busca so pelo cod_arquivo
        if (!$obj->carregarArquivoByCodArquivo($debug)) {
            // Fallback: tentar com cod_evento para compatibilidade com outros modulos
            $obj->setCodEvento(COD_EVENTO);
            $obj->carregarArquivoById($debug);
        }
        return $obj;
    }

    public function editarArquivo($fws_, $debug = false) {
        $obj = $this->carregarArquivo($fws_,$debug);

        echo '{
                  "statusMessage":"' . $ok . '",
                  "message":      "' . $obj->getMessage() . '",
                  "codArquivo":   "' . $obj->getCodArquivo() . '",
                  "tipoArquivo":  "' . $obj->getTipoArquivo() . '",
                  "arquivoGerado":"' . $obj->geArquivoGerado() . '",
                  "dataInclusao": "' . $obj->getDataInclusao() . '" 
                 }';
        exit();
    }

    public function salvarArquivo($fws_, $debug = false) {
        try {
            if ($fws_['novoCodArquivo'] == 0) {
                $fws_['codArquivo'] = NULL;
            }
            $isForm = true;
            $obj = $this->carregarArquivo($fws_);
            $obj->setAll($fws_, $isForm);
            $obj->salvar($debug);
        } catch (OkException $e) {
            $ok = true;
        } catch (Exception $e) {
            $ok = false;
        }

        echo '{
        "statusMessage":"' . $ok . '",
        "message":      "' . $e->getMessage() . '",
        "codArquivo":"' . $obj->getCodArquivo() . '"
        }';
    }

    public function excluirArquivo($fws_, $debug = false) {
        $ok = false;
        $mensagem = '';
        try {
            $obj = $this->carregarArquivo($fws_, $debug);

            // Excluir dados associados (projetos e pagamentos) antes de excluir o registro do arquivo
            $objT = new Transparencia();
            $objT->setGestor(WB_GRUPO);
            $tipoArquivo = $obj->getTipoArquivo();
            $codArquivo = $obj->getCodArquivo();
            if ($tipoArquivo == 'CARGA_TRANSPARENCIA_PROJETOS') {
                $objT->deleteProjetosByArquivo($codArquivo, $debug);
            } elseif ($tipoArquivo == 'CARGA_TRANSPARENCIA_PAGAMENTOS') {
                $objT->deletePagamentosByArquivo($codArquivo, $debug);
            }

            // Excluir arquivo físico
            $caminhoFisico = $this->varDiretorio . $obj->getArquivoGerado();
            if(file_exists($caminhoFisico)) {
                if(!@unlink($caminhoFisico)) {
                    $logDir = WB_PATH_LOG_CARGA;
                    if (!is_dir($logDir)) @mkdir($logDir, 0755, true);
                    $logMsg = date('Y-m-d H:i:s') . " - Falha ao remover arquivo cod_arquivo=" . $codArquivo . " nome=" . $obj->getArquivoGerado() . "\n";
                    @file_put_contents($logDir . 'erros-exclusao.log', $logMsg, FILE_APPEND);
                }
            }

            // Excluir registro do banco
            $obj->excluir();

        } catch (OKException $e) {
            $ok = true;
            $mensagem = $e->getMessage();
        } catch (Exception $e) {
            $ok = false;
            $mensagem = $e->getMessage();
        }

        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'statusMessage' => $ok ? '1' : '',
            'message' => $mensagem,
            'codArquivo' => $fws_['codArquivo']
        ]);
        exit();
    }

    public function carregarTabelaArquivos($fws_, $debug = false) {
        try {
            $filtros = array();
            if(!empty($fws_['tipoFiltro'])) $filtros['tipoFiltro'] = $fws_['tipoFiltro'];
            if(!empty($fws_['dataDe'])) $filtros['dataDe'] = $fws_['dataDe'];
            if(!empty($fws_['dataAte'])) $filtros['dataAte'] = $fws_['dataAte'];
            if(!empty($fws_['statusFiltro'])) $filtros['statusFiltro'] = $fws_['statusFiltro'];
            if(!empty($fws_['nomeArquivoFiltro'])) $filtros['nomeArquivoFiltro'] = $fws_['nomeArquivoFiltro'];
            $filtros['gestorFiltro'] = WB_GRUPO;

            $obj = new Arquivo();
            $lista = array();
            try {
                $obj->carregarListaCargaTransparenciaFiltrada($filtros, $debug);
                $lista = $obj->getListaArquivo();
            } catch (Exception $eList) {
                // Sem registros - lista vazia
            }

            $ii = empty($lista) ? 0 : count($lista);
            $tblId = 'tblArquivos' . uniqid();
            ?>
            <table class="tbl-fin" id="<?php echo $tblId ?>">
                <thead>
                    <tr>
                        <th style="width:90px;">A&ccedil;&otilde;es</th>
                        <th data-col="1" style="cursor:pointer;">Arquivo Enviado <span class="sort-arrow">&uarr;&darr;</span></th>
                        <th data-col="2" style="cursor:pointer; width:130px;">Situa&ccedil;&atilde;o <span class="sort-arrow">&uarr;&darr;</span></th>
                        <th data-col="3" style="cursor:pointer; width:100px;">Data <span class="sort-arrow">&uarr;&darr;</span></th>
                    </tr>
                </thead>
                <tbody>
            <?php if ($ii == 0) { ?>
                <tr><td colspan="4" class="text-center" style="padding:30px 10px; color:#999; font-style:italic;">Nenhum arquivo encontrado.</td></tr>
            <?php } ?>
            <?php
            if (!empty($lista)) foreach ($lista as $objL) {
                $codArq = $objL->getCodArquivo();
                $status = $objL->getStatusCarga();

                // Badge de situacao
                if ($status == 'CARREGADO') {
                    $badge = '<span class="badge-tipo"><span class="dot dot-green"></span>Carregado</span>';
                } elseif ($status == 'PARCIALMENTE_CARREGADO') {
                    $badge = '<span class="badge-tipo"><span class="dot dot-orange"></span>Parcial</span>';
                } elseif ($status == 'ASSOCIADO') {
                    $badge = '<span class="badge-tipo"><span class="dot dot-blue"></span>Associado</span>';
                } else {
                    $badge = '<span class="badge-tipo"><span class="dot dot-red"></span>Aguardando</span>';
                }

                // Botoes de acao
                $btns = '<span class="btn-acao btn-acao-excluir excluir" title="Excluir"><i class="fa fa-trash"></i></span>';
                if (!$status || $status == '' || $status == 'AGUARDANDO') {
                    $btns .= ' <span class="btn-acao botaoCarga" title="Carregar dados"><i class="fa fa-play"></i></span>';
                }
                if ($status == 'CARREGADO' || $status == 'PARCIALMENTE_CARREGADO') {
                    $btns .= ' <span class="btn-acao botaoReprocessar" title="Reprocessar"><i class="fa fa-refresh"></i></span>';
                    $btns .= ' <span class="btn-acao botaoVisualizar" title="Visualizar dados"><i class="fa fa-eye"></i></span>';
                }

                // Log
                $log = $objL->getMessageLog();
                $temLog = !empty($log);
                ?>
                <tr id="<?php echo $codArq ?>">
                    <td><?php echo $btns ?></td>
                    <td>
                        <?php echo htmlspecialchars($objL->getArquivoUpload()) ?>
                        <?php if ($temLog) { ?>
                            <br><a href="#" class="mostrarLog" style="font-size:11px; color:#999;">ver log</a>
                            <div class="messageLog" style="display:none; margin-top:5px; font-size:11px; color:#666; background:#f9f9f9; padding:8px; border-radius:4px;">
                                <?php echo $log ?>
                            </div>
                        <?php } ?>
                    </td>
                    <td><?php echo $badge ?></td>
                    <td style="font-size:12px; color:#888;"><?php echo formatDateView($objL->getDataInclusao()) ?></td>
                </tr>
                <?php
            }
            ?>
                </tbody>
                <tfoot><tr><td colspan="4" style="text-align:right; color:#999; font-size:12px; padding:8px 10px; border-top:1px solid #eee;">Total: <?php echo $ii ?> arquivo<?php echo $ii != 1 ? 's' : '' ?></td></tr></tfoot>
            </table>
            <?php
        } catch (Exception $e) {
            echo '<div class="text-center text-muted" style="padding:20px 0;">';
            echo $e->getMessage() ?: 'Lista vazia.';
            echo '</div>';
        }
    }
    
    public function salvarCargaTransparencia($fws_, $debug = false) {
        try {
            $objAr = new Arquivo();
            $objAr->setAll($fws_,$camelCase=1);
            $objAr->setGestor(WB_GRUPO);
            $objAr->setArquivoUpload($_FILES['arquivo']['name']);

            $reenvio = false;
            try {
                $objAr->arquivoByNomeExiste();
                // Não lançou exceção = arquivo NÃO existe, criar novo
            } catch (Exception $eExiste) {
                // Arquivo já existe — getByNomeExiste já carregou dados no $objAr
                $reenvio = true;
            }

            if ($reenvio) {
                // Substituir arquivo físico existente e resetar status
                $destino = $this->varDiretorio . $objAr->getArquivoGerado();
                if (!move_uploaded_file($_FILES['arquivo']['tmp_name'], $destino)) {
                    throw new Exception("Erro ao substituir arquivo físico.");
                }
                $objAr->setStatusCarga('');
                $objAr->setMessageLog('');
                $objAr->atualizar($debug);
            } else {
                $this->uploadArquivo($fws_, $objAr, $debug);
                $objAr->salvar($debug);
            }

        } catch (OkException $e) {
            $ok = true;
            $msg = $reenvio ? 'Arquivo reenviado com sucesso. Clique em Carregar para processar.' : $e->getMessage();
        } catch (Exception $e) {
            $ok = false;
            $msg = $e->getMessage();
        }
        echo json_encode([
            'statusMessage' => $ok ? '1' : '',
            'message'       => $msg,
            'codArquivo'    => $objAr->getCodArquivo()
        ]);
    }    
    
    public function uploadArquivo($fws_, $obj, $debug = false) {

        if($_FILES['arquivo']['name']==''){
            throw new Exception("Arquivo não informado");
        }
        if ($_FILES['arquivo']['size'] > convertSizeToBytes($this->tamanho)){
           throw new Exception("Não é permitido arquivos maiores que ".$this->tamanho);
        }
        if ($_FILES['arquivo']['tmp_name'] == '') {
            throw new Exception("Arquivo temporário não encontrado.");
        }
        $arrayMineTypeString = createMimeTypesArray($this->tiposPermitidos,true);
        $ext = strtolower(pathinfo($_FILES['arquivo']['name'], PATHINFO_EXTENSION));     
        if (!in_array($ext, $this->tiposPermitidos)) {
            throw new Exception("Extensão de arquivo enviado (".$ext.") não consta na lista de permitidos (".$arrayMineTypeString.")");
        }
        // $arrayMineTypeString = createMimeTypesArray($this->tiposPermitidos,true);
        // if (!in_array($_FILES['arquivo']['type'], $arrayMineTypeString['mimeTypes'])) {
        //     throw new Exception("Extensão de arquivo enviado (".$_FILES['arquivo']['type'].") não consta na lista de permitidos (".$arrayMineTypeString['typesString'].")");
        // }
        $extensao = pathinfo($_FILES['arquivo']['name'], PATHINFO_EXTENSION);
        $obj->setArquivoUpload($_FILES['arquivo']['name']);
        if($fws_['tipoArquivo']!='TRANSPARENCIA'){
            $this->prefixo = strtolower($fws_['tipoArquivo']);
        }
        $this->varArquivo = $this->prefixo.'-'.numberPad($obj->getCodEvento(), 4).'-'.numberPad($obj->getLastId(), 4).'.'.$extensao;
        $obj->setTipoArquivo($fws_['tipoArquivo']);
        $obj->setCodPerfil($fws_['codPerfil']);
        $obj->setArquivoGerado($this->varArquivo);
        
        if (!file_exists($this->varDiretorio)){
            if(mkdir($this->varDiretorio, 0755, true)){
                echo $debug? "Criou diretório<br>" : "";
            }else{
               throw new Exception("Ocorreu um erro ao criar diretório");
            }
        }
        if (move_uploaded_file($_FILES['arquivo']['tmp_name'], $this->varDiretorio.$this->varArquivo)) {
            echo $debug? "Arquivo carregado com sucesso!<br>" : "";
            return true;
        } else {
            throw new Exception("Ocorreu um erro durante upload do arquivo.");
        }
    }    
    
    public function carregarCargaDados($fws_, $debug = false) {
        if (ob_get_level()) ob_clean();
        set_time_limit(300);

        try {
            $objAr = $this->carregarArquivo($fws_,$debug);
            $objEv = $objAr->getEvento();
            $substituirFlag = isset($fws_['substituir']) && $fws_['substituir'] == '1';
            $acaoExistentes = isset($fws_['acao_existentes']) ? $fws_['acao_existentes'] : 'pular';
            if($objAr->getStatusCarga() != '' && !$substituirFlag){
                throw new Exception("O arquivo já foi processado.");
            }
            if($substituirFlag && $objAr->getStatusCarga() != ''){
                try {
                    $objAr->setStatusCarga('');
                    $objAr->setMessageLog('');
                    $objAr->atualizar();
                } catch (OKException $e) { /* reset ok */ }
            }

            $objConf = $objAr->getEventoConfiguracao();

            $caminhoArquivo = $this->varDiretorio . $objAr->getArquivoGerado();
            if (!file_exists($caminhoArquivo)) {
                error_log("Arquivo não encontrado: " . $caminhoArquivo);
                throw new Exception("Arquivo não encontrado no servidor. Verifique se o arquivo foi enviado corretamente.");
            }

            $file = new LeitorArquivo();
            $file->setNmArquivo($objAr->getArquivoGerado());
            $file->carregarArquivo($caminhoArquivo, $debug);

            $listaLinhas = $file->getListaLinhas();
            $totalLinhas = $file->getTotalLinhas();

            // Detectar cabeçalho real (pular linhas vazias/só separadores e metadados)
            $cabecalhoInfo = $this->detectarLinhaCabecalho($listaLinhas, $totalLinhas);
            $linhaCabecalho = $cabecalhoInfo['linha'];
            $indiceCabecalho = $cabecalhoInfo['indice'];

            // Começar a processar a partir da linha APÓS o cabeçalho
            $contadorInverso = $indiceCabecalho + 1;
            $totalRegistros = $totalLinhas - $contadorInverso;
            $totalLinhas = $totalRegistros;

            // Se modo substituir, coletar projetos do arquivo e deletar pagamentos existentes
            $substituir = isset($fws_['substituir']) && $fws_['substituir'] == '1';
            if ($substituir && $objAr->getTipoArquivo() == 'CARGA_TRANSPARENCIA_PAGAMENTOS') {
                $projetosParaDeletar = [];
                $tmpTotal = $totalRegistros;
                $tmpContador = $contadorInverso;
                while ($tmpTotal > 0) {
                    try {
                        $tmpLinha = $listaLinhas[$tmpContador];
                        $tmpCols = explode(';', $tmpLinha);
                        $tmpMapped = MapearColunasTransparenciaAC::identificar($linhaCabecalho, $tmpCols, $objAr->getTipoArquivo());
                        $numProjeto = trim(limparString($tmpMapped['numero_projeto']));
                        if ($numProjeto != '' && !in_array($numProjeto, $projetosParaDeletar)) {
                            $projetosParaDeletar[] = $numProjeto;
                        }
                    } catch (Exception $e) {
                        // Ignorar linhas inválidas (cabeçalho, vazias)
                    }
                    $tmpTotal--;
                    $tmpContador++;
                }
                $objT = new Transparencia();
                foreach ($projetosParaDeletar as $proj) {
                    $objT->deletePagamentosByProjeto($proj, $objAr->getGestor(), $debug);
                }
            }

            //Loop em cima das linhas retornadas do arquivo (após o cabeçalho)
            $arrayColunas = [];
            $carregado = 0;
            $naoCarregado = 0;
            $linhasProcessadas = 0;
            while($totalLinhas > 0){

                try{

                    $linha = $listaLinhas[$contadorInverso];
                    unset($arrayColunas);
                    $arrayColunas = explode(';', $linha);
                    $arrayColunas = MapearColunasTransparenciaAC::identificar($linhaCabecalho, $arrayColunas, $objAr->getTipoArquivo());
                    switch ($objAr->getTipoArquivo()) {
                        case 'CARGA_TRANSPARENCIA_PROJETOS':
                            $this->carregarDadosProjetos($arrayColunas, $objConf, $objAr, $objEv, $acaoExistentes);
                            break;
                        case 'CARGA_TRANSPARENCIA_PAGAMENTOS':
                            $this->carregarDadosPagamentos($arrayColunas, $objConf, $objAr, $objEv, $acaoExistentes);
                            break;
                        default:
                            throw new Exception("Tipo de arquivo (".$objAr->getTipoArquivo().") não reconhecido");
                        break;
                    }
                }catch (OKException $e){
                    $carregado ++;
                    $message .= "<br><span style='color:green;'>linha: ".$contadorInverso.":".$e->getMessage()."</span>";
                }catch (Exception $e){
                    if($e->getMessage()!=''){
                        $naoCarregado ++;
                        $message .= "<br><span style='color:red;'>linha: ".$contadorInverso.":".$e->getMessage()."</span>";
                    }
                }

                $totalLinhas--;
                $contadorInverso++;
                $linhasProcessadas++;
            }
            $messageLog = $message;
            $message = '';
            // Atualiza o status do arquivo carregado, indicando que a carga já foi feita.
            if ($linhasProcessadas == $totalRegistros){
                try {
                    $objAr->getByIdExiste();
                    if($carregado >= 1){
                        $statusArquivo = 'CARREGADO';
                    } else {
                        $statusArquivo = 'PARCIALMENTE_CARREGADO';
                    }
                    $objAr->setStatusCarga($statusArquivo);
                    $resumo = "<div style='margin-bottom:8px;'>"
                        . "<span class='label label-info'>Detectados: $totalRegistros</span> "
                        . "<span class='label label-success'>Carregados: $carregado</span> "
                        . "<span class='label label-danger'>Falharam: $naoCarregado</span> "
                        . "<span class='label label-warning'>Status: RASCUNHO</span>"
                        . "</div>";
                    $objAr->setMessageLog($resumo . $messageLog);
                    $objAr->atualizar();
                } catch (OKException $e) {
                    // atualizar OK
                } catch (Exception $e) {
                    // erro ao atualizar status
                }

                // Definir ok com base nos registros efetivamente carregados
                if ($carregado >= 1) {
                    $ok = true;
                    $message = "Carregados: $carregado (como rascunho). Não carregados: $naoCarregado. Total linhas: $totalRegistros. Acesse 'Gestão de Registros' para revisar e publicar.";
                } else {
                    $ok = false;
                    $message = "Nenhum registro foi carregado. Não carregados: $naoCarregado. Total linhas: $totalRegistros.";
                }
            }else{
                $ok = false;
                $message = 'Arquivo não carregado';
                $statusArquivo = 'Não carregado';
            }
        } catch (Exception $e) {
            $statusArquivo = isset($statusArquivo) ? $statusArquivo : '';
            $message = $e->getMessage();
            $ok = false;
        }
        $codArquivo = isset($objAr) ? $objAr->getCodArquivo() : '';
        echo json_encode([
            'statusMessage' => $ok ? '1' : '',
            'message'       => str_replace("\n", "", $message . ($statusArquivo ? '<br>' . $statusArquivo : '')),
            'codArquivo'    => $codArquivo
        ]);
    }
  
    // Mapeamento de colunas movido para MapearColunasTransparenciaAC::identificar()

    public function carregarDadosProjetos($arrayColunas, $objConf, $objAr, $objEv, $acaoExistentes = 'pular'){
        if($objAr->getTipoArquivo()=='CARGA_TRANSPARENCIA_PROJETOS'){
            $objT = new Transparencia();
            $objT->setCodEvento($objAr->getCodEvento());
            $objT->setCodArquivo($objAr->getCodArquivo());
            $objT->setGestor($objAr->getGestor());
            $objT->setDataInicio(formatDateInsert($arrayColunas['data_inicio']));
            $objT->setDataFim(formatDateInsert($arrayColunas['data_fim']));
            $objT->setEProtocolo($arrayColunas['e_protocolo']);
            $objT->setTipoPagamento($arrayColunas['tipo_pagamento']);
            $objT->setVinculo($arrayColunas['vinculo']);
            $objT->setCoordenador($arrayColunas['coordenador']);
            $objT->setProposta($arrayColunas['proposta']);
            $objT->setNumeroProjeto($arrayColunas['numero_projeto']);

            if ($acaoExistentes == 'substituir') {
                $objT->salvarOuAtualizarProjetos();
            } else {
                $objT->salvarProjetos();
            }
        }
    }
    public function carregarDadosPagamentos($arrayColunas, $objConf, $objAr, $objEv, $acaoExistentes = 'pular'){
        /*Definindo codigo da forma de pagamento a ser associado*/
        if($objAr->getTipoArquivo()=='CARGA_TRANSPARENCIA_PAGAMENTOS'){
            /*criar inscrito para a pagamento*/
            $objT = new Transparencia();
            $objT->setCodEvento($objAr->getCodEvento());
            $objT->setCodArquivo($objAr->getCodArquivo());
            $objT->setGestor($objAr->getGestor());
            $objT->setIdProjeto($arrayColunas['id_projeto']);
            $objT->setNumeroProjeto($arrayColunas['numero_projeto']);
            $objT->setValorBruto(limparString($arrayColunas['valor_bruto']));
            $objT->setVencimento(formatDateInsert($arrayColunas['data_pagamento']));
            $objT->setValorLiquido(limparString($arrayColunas['valor_liquido']));
            $objT->setCodigo(limparString($arrayColunas['codigo']));
            $objT->setServico(limparString($arrayColunas['servico']));
            $objT->setVinculo(limparString($arrayColunas['vinculo']));
            $objT->setProjeto(limparString($arrayColunas['projeto']));
            $objT->setFornecedor(limparString($arrayColunas['fornecedor']));
            $objT->setCnpj($arrayColunas['cnpj']);
            $objT->setTipoRpa(limparString($arrayColunas['tipo_pagamento']));

            if ($acaoExistentes == 'substituir') {
                $objT->salvarOuAtualizarPagamentos();
            } else {
                $objT->salvarPagamentos();
            }
        }
    }

    /**
     * Verifica conflitos antes de carregar: identifica projetos que já existem no banco.
     * Retorna JSON com lista de novos e existentes (dados atuais vs dados do arquivo).
     */
    public function verificarConflitos($fws_, $debug = false) {
        set_time_limit(300);
        ob_start(); // Capturar qualquer saída indesejada durante o processamento

        try {
            $objAr = $this->carregarArquivo($fws_, $debug);

            $caminhoArquivo = $this->varDiretorio . $objAr->getArquivoGerado();
            if (!file_exists($caminhoArquivo)) {
                error_log("Arquivo não encontrado: " . $caminhoArquivo);
                throw new Exception("Arquivo não encontrado no servidor. Verifique se o arquivo foi enviado corretamente.");
            }

            $file = new LeitorArquivo();
            $file->setNmArquivo($objAr->getArquivoGerado());
            $file->carregarArquivo($caminhoArquivo, $debug);

            $listaLinhas = $file->getListaLinhas();
            $totalLinhas = $file->getTotalLinhas();
            $cabecalhoInfo = $this->detectarLinhaCabecalho($listaLinhas, $totalLinhas);
            $linhaCabecalho = $cabecalhoInfo['linha'];
            $indiceCabecalho = $cabecalhoInfo['indice'];

            $objT = new Transparencia();
            $novos = 0;
            $conflitos = [];

            if ($objAr->getTipoArquivo() == 'CARGA_TRANSPARENCIA_PROJETOS') {
                $projetosVistos = [];
                for ($i = $indiceCabecalho + 1; $i < $totalLinhas; $i++) {
                    try {
                        $linha = $listaLinhas[$i];
                        $cols = explode(';', $linha);
                        $mapped = MapearColunasTransparenciaAC::identificar($linhaCabecalho, $cols, $objAr->getTipoArquivo());

                        $numProjeto = trim($mapped['numero_projeto'] ?? '');
                        $coordenador = trim($mapped['coordenador'] ?? '');
                        if ($numProjeto == '' || $coordenador == '' || $coordenador == 'Coordenador') continue;
                        if (in_array($numProjeto, $projetosVistos)) continue;
                        $projetosVistos[] = $numProjeto;

                        $dadosNovos = [
                            'numero_projeto' => $numProjeto,
                            'data_inicio'    => trim($mapped['data_inicio'] ?? ''),
                            'data_fim'       => trim($mapped['data_fim'] ?? ''),
                            'e_protocolo'    => trim($mapped['e_protocolo'] ?? ''),
                            'tipo_pagamento' => trim($mapped['tipo_pagamento'] ?? ''),
                            'vinculo'        => trim($mapped['vinculo'] ?? ''),
                            'coordenador'    => $coordenador,
                            'proposta'       => trim($mapped['proposta'] ?? ''),
                        ];

                        $existente = $objT->getProjetoByNumero($numProjeto, $debug);
                        if ($existente) {
                            $conflitos[] = [
                                'atual' => [
                                    'numero_projeto' => $existente['numero_projeto'] ?? '',
                                    'data_inicio'    => $existente['data_inicio'] ?? '',
                                    'data_fim'       => $existente['data_fim'] ?? '',
                                    'e_protocolo'    => $existente['e_protocolo'] ?? '',
                                    'tipo_pagamento' => $existente['tipo_pagamento'] ?? '',
                                    'vinculo'        => $existente['vinculo'] ?? '',
                                    'coordenador'    => $existente['coordenador'] ?? '',
                                    'proposta'       => $existente['proposta'] ?? '',
                                ],
                                'novo' => $dadosNovos,
                            ];
                        } else {
                            $novos++;
                        }
                    } catch (OKException $e) {
                    } catch (Exception $e) {
                    }
                }
            } elseif ($objAr->getTipoArquivo() == 'CARGA_TRANSPARENCIA_PAGAMENTOS') {
                for ($i = $indiceCabecalho + 1; $i < $totalLinhas; $i++) {
                    try {
                        $linha = $listaLinhas[$i];
                        $cols = explode(';', $linha);
                        $mapped = MapearColunasTransparenciaAC::identificar($linhaCabecalho, $cols, $objAr->getTipoArquivo());

                        // Montar objeto idêntico ao carregarDadosPagamentos
                        $objTmp = new Transparencia();
                        $objTmp->setCodEvento($objAr->getCodEvento());
                        $objTmp->setCodArquivo($objAr->getCodArquivo());
                        $objTmp->setGestor($objAr->getGestor());
                        $objTmp->setNumeroProjeto($mapped['numero_projeto']);
                        $objTmp->setValorBruto(limparString($mapped['valor_bruto']));
                        $objTmp->setVencimento(formatDateInsert($mapped['data_pagamento']));
                        $objTmp->setValorLiquido(limparString($mapped['valor_liquido']));
                        $objTmp->setServico(limparString($mapped['servico']));
                        $objTmp->setFornecedor(limparString($mapped['fornecedor']));
                        $objTmp->setCnpj($mapped['cnpj']);
                        $objTmp->setTipoRpa(limparString($mapped['tipo_pagamento']));

                        $numProjeto = $objTmp->getNumeroProjeto();
                        $fornecedor = $objTmp->getFornecedor();
                        $valorBruto = $objTmp->getValorBruto();

                        if ($numProjeto === '' || $numProjeto === null) continue;
                        if ($valorBruto === '' || $valorBruto === null) continue;
                        if ($fornecedor === '' || $fornecedor === null) continue;
                        // Pular linha de cabeçalho
                        $fornecedorLower = strtolower($fornecedor);
                        if ($fornecedorLower == 'fichario' || $fornecedorLower == 'fichário' || $fornecedorLower == 'nome') continue;

                        $dadosNovos = [
                            'numero_projeto' => $numProjeto,
                            'fornecedor'     => $fornecedor,
                            'valor_bruto'    => $valorBruto,
                            'valor_liquido'  => $objTmp->getValorLiquido(),
                            'vencimento'     => $mapped['data_pagamento'] ?? '',
                            'servico'        => $objTmp->getServico(),
                            'cnpj'           => $objTmp->getCnpj(),
                            'tipo_pagamento' => $objTmp->getTipoRpa(),
                        ];

                        $existente = $objTmp->buscarPagamentoExistente($debug);
                        if ($existente) {
                            $conflitos[] = [
                                'atual' => [
                                    'numero_projeto' => $existente['numero_projeto'] ?? '',
                                    'fornecedor'     => $existente['fornecedor'] ?? '',
                                    'valor_bruto'    => $existente['valor_bruto'] ?? '',
                                    'valor_liquido'  => $existente['valor_liquido'] ?? '',
                                    'vencimento'     => isset($existente['vencimento']) ? formatDateView($existente['vencimento']) : '',
                                    'servico'        => $existente['servico'] ?? '',
                                    'cnpj'           => $existente['cnpj'] ?? '',
                                    'tipo_pagamento' => $existente['tipo_rpa'] ?? '',
                                ],
                                'novo' => $dadosNovos,
                            ];
                        } else {
                            $novos++;
                        }
                    } catch (OKException $e) {
                    } catch (Exception $e) {
                    }
                }
            }

            $strayOutput = ob_get_clean(); // Descartar saída indesejada
            $result = [
                'statusMessage' => '1',
                'temConflitos'  => count($conflitos) > 0,
                'novos'         => $novos,
                'tipo'          => $objAr->getTipoArquivo(),
                'conflitos'     => $conflitos
            ];
            echo json_encode($result);
        } catch (OKException $e) {
            $strayOutput = ob_get_clean();
            echo json_encode([
                'statusMessage' => '0',
                'message'       => 'Erro ao carregar arquivo: ' . $e->getMessage() . ($strayOutput ? ' [output: ' . substr($strayOutput, 0, 200) . ']' : '')
            ]);
        } catch (Exception $e) {
            $strayOutput = ob_get_clean();
            echo json_encode([
                'statusMessage' => '0',
                'message'       => $e->getMessage() . ($strayOutput ? ' [output: ' . substr($strayOutput, 0, 200) . ']' : '')
            ]);
        }
    }

    /**
     * Retorna os dados (projetos ou pagamentos) carregados por um arquivo específico.
     */
    public function visualizarDadosCarga($fws_, $debug = false) {
        if (ob_get_level()) ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        try {
            $objAr = $this->carregarArquivo($fws_, $debug);
            $tipo = $objAr->getTipoArquivo();
            $objT = new Transparencia();

            if ($tipo == 'CARGA_TRANSPARENCIA_PROJETOS') {
                $dados = $objT->listarProjetosPorArquivo($objAr->getCodArquivo(), $debug);
                echo json_encode([
                    'statusMessage' => '1',
                    'tipo' => 'projetos',
                    'total' => count($dados),
                    'dados' => $dados
                ]);
            } elseif ($tipo == 'CARGA_TRANSPARENCIA_PAGAMENTOS') {
                $dados = $objT->listarPagamentosPorArquivo($objAr->getCodArquivo(), $debug);
                echo json_encode([
                    'statusMessage' => '1',
                    'tipo' => 'pagamentos',
                    'total' => count($dados),
                    'dados' => $dados
                ]);
            } else {
                echo json_encode(['statusMessage' => '0', 'message' => 'Tipo de arquivo desconhecido.']);
            }
        } catch (Exception $e) {
            echo json_encode(['statusMessage' => '0', 'message' => $e->getMessage()]);
        }
    }

    /**
     * Endpoint da API para receber arquivos CSV de carga em lote.
     * Recebe: api_token, tipoArquivo, arquivo (file upload)
     * Retorna: JSON com status, total carregado, erros
     */
    public function receberCargaAPI($fws_, $debug = false) {
        header('Content-Type: application/json; charset=utf-8');

        // Validar token de autenticação
        $tokenEsperado = '8fcd9a1736b8f4a5c9e2437dfc61298eb19df8e7e46d32ac6456c3fc921a7b4f';
        $tokenRecebido = isset($_POST['api_token']) ? $_POST['api_token'] : '';
        if ($tokenRecebido !== $tokenEsperado) {
            http_response_code(401);
            echo json_encode(['status' => 'erro', 'message' => 'Token inválido ou não informado.']);
            exit();
        }

        // Validar tipo do arquivo
        $tiposValidos = ['CARGA_TRANSPARENCIA_PROJETOS', 'CARGA_TRANSPARENCIA_PAGAMENTOS'];
        $tipoArquivo = isset($_POST['tipoArquivo']) ? $_POST['tipoArquivo'] : '';
        if (!in_array($tipoArquivo, $tiposValidos)) {
            http_response_code(400);
            echo json_encode(['status' => 'erro', 'message' => 'tipoArquivo inválido. Esperado: ' . implode(' ou ', $tiposValidos)]);
            exit();
        }

        // Validar arquivo enviado
        if (!isset($_FILES['arquivo']) || $_FILES['arquivo']['error'] !== UPLOAD_ERR_OK) {
            $erro = isset($_FILES['arquivo']) ? $_FILES['arquivo']['error'] : 'não enviado';
            http_response_code(400);
            echo json_encode(['status' => 'erro', 'message' => 'Arquivo não recebido ou com erro: ' . $erro]);
            exit();
        }

        try {
            // Montar $fws_ compatível com salvarCargaTransparencia / uploadArquivo
            $fws_['tipoArquivo'] = $tipoArquivo;
            $fws_['codArquivo'] = NULL;
            $fws_['novoCodArquivo'] = 0;
            $fws_['codPerfil'] = 0;

            // Criar registro do arquivo
            $objAr = new Arquivo();
            $objAr->setAll($fws_, $camelCase = 1);
            $objAr->setGestor(WB_GRUPO);
            $objAr->setArquivoUpload($_FILES['arquivo']['name']);

            // Verificar se arquivo com mesmo nome já existe
            $reprocessando = false;
            try {
                $objAr->arquivoByNomeExiste();
            } catch (Exception $e) {
                // Arquivo já existe — permitir reprocessamento
                // O DAO já carregou o registro existente em $objAr (setAll)
                $reprocessando = true;
                try {
                    $objAr->setStatusCarga('');
                    $objAr->setMessageLog('');
                    $objAr->atualizar();
                } catch (OKException $okE) {
                    // Reset OK
                } catch (Exception $e2) {
                    echo json_encode(['status' => 'erro', 'message' => 'Erro ao resetar arquivo para reprocessamento: ' . $e2->getMessage()]);
                    exit();
                }
            }

            if (!$reprocessando) {
                // Novo arquivo — upload e salvar registro
                $this->uploadArquivo($fws_, $objAr, $debug);
                $objAr->salvar($debug);
            }

        } catch (OkException $e) {
            // salvar lança OkException em sucesso
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'erro', 'message' => 'Erro ao salvar arquivo: ' . $e->getMessage()]);
            exit();
        }

        // Processar carga de dados
        try {
            $codArquivo = $objAr->getCodArquivo();
            $fws_['codArquivo'] = $codArquivo;

            // Recarregar objeto do arquivo salvo
            $objAr2 = $this->carregarArquivo($fws_, $debug);
            $objEv = $objAr2->getEvento();

            if ($objAr2->getStatusCarga() != '') {
                echo json_encode(['status' => 'ignorado', 'message' => 'Arquivo já foi processado anteriormente.', 'codArquivo' => $codArquivo]);
                exit();
            }

            $objConf = $objAr2->getEventoConfiguracao();

            $file = new LeitorArquivo();
            $file->setNmArquivo($objAr2->getArquivoGerado());
            $file->carregarArquivo($this->varDiretorio . $file->getNmArquivo(), $debug);

            $listaLinhas = $file->getListaLinhas();
            $totalLinhas = $file->getTotalLinhas();

            // Detectar cabeçalho real (pular linhas vazias e metadados)
            $cabecalhoInfoAPI = $this->detectarLinhaCabecalho($listaLinhas, $totalLinhas);
            $linhaCabecalhoAPI = $cabecalhoInfoAPI['linha'];
            $indiceCabecalhoAPI = $cabecalhoInfoAPI['indice'];

            $contadorInverso = $indiceCabecalhoAPI + 1;
            $totalRegistros = $totalLinhas - $contadorInverso;
            $totalLinhas = $totalRegistros;
            $arrayColunas = [];
            $carregado = 0;
            $naoCarregado = 0;
            $message = '';
            $linhasProcessadas = 0;

            while ($totalLinhas > 0) {
                try {
                    $linha = $listaLinhas[$contadorInverso];
                    unset($arrayColunas);
                    $arrayColunas = explode(';', $linha);
                    $arrayColunas = MapearColunasTransparenciaAC::identificar($linhaCabecalhoAPI, $arrayColunas, $objAr2->getTipoArquivo());

                    switch ($objAr2->getTipoArquivo()) {
                        case 'CARGA_TRANSPARENCIA_PROJETOS':
                            $this->carregarDadosProjetos($arrayColunas, $objConf, $objAr2, $objEv);
                            break;
                        case 'CARGA_TRANSPARENCIA_PAGAMENTOS':
                            $this->carregarDadosPagamentos($arrayColunas, $objConf, $objAr2, $objEv);
                            break;
                    }
                } catch (OKException $e) {
                    $carregado++;
                    $message .= "<br><span style='color:green;'>linha: ".$contadorInverso.":".$e->getMessage()."</span>";
                } catch (Exception $e) {
                    if ($e->getMessage() != '') {
                        $naoCarregado++;
                        $message .= "<br><span style='color:red;'>linha: ".$contadorInverso.":".$e->getMessage()."</span>";
                    }
                }
                $totalLinhas--;
                $contadorInverso++;
                $linhasProcessadas++;
            }

            // Atualizar status do arquivo
            if ($linhasProcessadas == $totalRegistros) {
                try {
                    $objAr2->getByIdExiste();
                    $statusArquivo = ($carregado >= 1) ? 'CARREGADO' : 'PARCIALMENTE_CARREGADO';
                    $objAr2->setStatusCarga($statusArquivo);
                    $resumo = "<b>Resumo: carregado=$carregado, naoCarregado=$naoCarregado, totalLinhas=$totalRegistros</b>";
                    $messageLog = $resumo . $message;
                    $objAr2->setMessageLog($messageLog);
                    $objAr2->atualizar();
                } catch (OKException $e) {
                    // OK
                } catch (Exception $e) {
                    // Erro ao atualizar status
                }
            }

            // Extrair lista de erros a partir de $message (cada erro é uma tag <span>)
            $erros = [];
            if (!empty($message)) {
                preg_match_all('/<span[^>]*color:red[^>]*>(.*?)<\/span>/', $message, $matches);
                $erros = $matches[1] ?? [];
            }

            echo json_encode([
                'status' => 'sucesso',
                'codArquivo' => $codArquivo,
                'totalLinhas' => $totalRegistros,
                'carregado' => $carregado,
                'naoCarregado' => $naoCarregado,
                'erros' => array_slice($erros, 0, 50)
            ]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'erro',
                'message' => 'Erro ao processar carga: ' . $e->getMessage(),
                'codArquivo' => isset($codArquivo) ? $codArquivo : null
            ]);
        }
        exit();
    }

}

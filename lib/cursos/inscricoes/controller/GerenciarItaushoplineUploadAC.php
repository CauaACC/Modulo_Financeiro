<?php
include(dirname(__FILE__) .'/../include/functions/LeitorArquivo.php');

class GerenciarItaushoplineUploadAC {

    function __construct() {
        FWA_exigirPermissao([enumSistema::SIS_GERENCIAR_ADM_GERAL, enumSistema::SIS_CONTROLE_FINANCEIRO]);
        $this->tiposPermitidos = array('csv', 'txt', 'ret', 'application/octet-stream');
        $this->tamanho = '30MB';
        $this->varDiretorio = WB_PATH_LOG_CARGA.'pagamentos/';
        $this->prefixo = 'itaushopline';
    }

    public function carregarArquivo($fws_, $debug = false) {
        $obj = new Arquivo();
        $obj->setCodArquivo($fws_['codArquivo']);
        $obj->carregarArquivoById($debug);
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
        try {
            $obj = $this->carregarArquivo($fws_, $debug);
            
            if(file_exists($this->varDiretorio.$obj->getArquivoGerado())) {
                if(!unlink($this->varDiretorio.$obj->getArquivoGerado())) {
                    throw new Exception("Não foi possível remover o arquivo fisicamente.");
                }
            }    
            $obj->excluir();
            
        } catch (OKException $e) {
            $ok = true;
        } catch (Exception $e) {
            $ok = false;
        }

        echo '{
        "statusMessage":"' . $ok . '",
        "message":      "' . $e->getMessage() . '",
        "codArquivo":"' . $fws_['codArquivo'] . '"
        }';
    }

    public function carregarTabelaArquivos($fws_, $debug = false) {
        try {
            $obj = new Arquivo();
            $obj->carregarListaArquivoByIdEvento($debug);
            $ii = 0;
            foreach ($obj->getListaArquivo() as $objL) {
                if ($objL->getTipoArquivo()=='ITAUSHOPLINE' || $objL->getTipoArquivo()=='ITAU-BOLECODE'){
                $ii++;
                $status = $objL->getStatusCarga();
                ?>
                <tr id="<?php echo $objL->getCodArquivo() ?>">
                    <td>
                        <span class="btn-acao btn-acao-excluir excluir" title="Excluir"><i class="fa fa-trash"></i></span>
                        <?php if (!$status || $status == '') { ?>
                            <button class="btn-mini-acao btn-mini-carregar botaoCargaRetorno"><i class="fa fa-download"></i> Carregar</button>
                        <?php } elseif ($status == 'CARREGADO') { ?>
                            <button class="btn-mini-acao btn-mini-associar botaoAssociar"><i class="fa fa-link"></i> Associar</button>
                        <?php } ?>
                    </td>
                    <td>
                        <?php if ($status == 'ASSOCIADO') { ?>
                            <span style="color:#28a745; font-weight:600;"><i class="fa fa-check-circle"></i> Associado</span>
                        <?php } elseif ($status == 'CARREGADO') { ?>
                            <span style="color:#e6a817; font-weight:600;"><i class="fa fa-clock-o"></i> Carregado</span>
                        <?php } else { ?>
                            <span style="color:#999;">Aguardando</span>
                        <?php } ?>
                    </td>
                    <td><?php echo $objL->getTipoArquivo() ?><br><small style="color:#999;"><?php echo $objL->getArquivoGerado() ?></small></td>
                    <td>
                        <?php echo $objL->getArquivoUpload() ?>
                        <?php if ($objL->getMessageLog()) { ?>
                            <br><a href="#" class="mostrarLog" style="font-size:11px; color:#666;"><i class="fa fa-file-text-o"></i> Mostrar Log</a>
                            <div class="messageLog" style="display:none; margin-top:8px; padding:10px; background:#f8f9fb; border:1px solid #eee; border-radius:6px; font-size:12px; max-height:300px; overflow-y:auto;">
                                <?php echo $objL->getMessageLog(); ?>
                            </div>
                        <?php } ?>
                    </td>
                    <td><?php echo formatDateView($objL->getDataInclusao()) ?></td>
                </tr>
                <?php
                }
            }
            echo "<tr class='tbl-fin-total'><td colspan='6' style='text-align:right; color:#999; font-size:12px; padding:8px 10px; border-top:1px solid #eee;'>Total de arquivos: {$ii}</td></tr>";
        } catch (Exception $e) {
            echo "<tr><td colspan='6'>";
            echo $e->getMessage() ?: "Nenhum arquivo carregado.";
            echo "</td></tr>";
        }
    }
    
    public function salvarPagamentoUpload($fws_, $debug = false) {
        try {
            $objAr = new Arquivo();
            $objAr->setAll($fws_,$camelCase=1);
            $this->uploadArquivo($fws_, $objAr, $debug);
            $objAr->salvar($debug);
            
        } catch (OkException $e) {
            $ok = true;
        } catch (Exception $e) {
            $ok = false;
        }
        echo '{
        "statusMessage":"' . $ok . '",
        "message":      "' . $e->getMessage() . '",
        "codArquivo":"' . $objAr->getCodArquivo() . '"
        }';
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
        
        $extensao = pathinfo($_FILES['arquivo']['name'], PATHINFO_EXTENSION);
        $arrayMineTypeString = createMimeTypesArray($this->tiposPermitidos,true);
        
        //if (!in_array($_FILES['arquivo']['type'], $arrayMineTypeString['mimeTypes'])) {
//        if (!in_array($extensao, $arrayMineTypeString['mimeTypes'])) {
//            throw new Exception("Extensão de arquivo somente tipo .(".$arrayMineTypeString['typesString'].")");
//        }
        // $obj->setArquivoUpload($_FILES['arquivo']['name']);
        // $this->varArquivo = 'itaushopline-'.numberPad($obj->getCodEvento(), 4).'-'.numberPad($obj->getLastId(), 4).'.'.$extensao;
        // $obj->setArquivoGerado($this->varArquivo);

        $extensao = pathinfo($_FILES['arquivo']['name'], PATHINFO_EXTENSION);
        $obj->setArquivoUpload($_FILES['arquivo']['name']);
        if($fws_['tipoArquivo']!='ITAUSHOPLINE'){
            $this->prefixo = strtolower($fws_['tipoArquivo']);
        }
        $this->varArquivo = $this->prefixo.'-'.numberPad($obj->getCodEvento(), 4).'-'.numberPad($obj->getLastId(), 4).'.'.$extensao;
        $obj->setTipoArquivo($fws_['tipoArquivo']);
        $obj->setCodPerfil('');
        $obj->setArquivoGerado($this->varArquivo);
        
        if (!file_exists($this->varDiretorio)){
            if(mkdir($this->varDiretorio, 0777, true)){
                echo $debug? "Criou diretório<br>" : "";
            }else{
               throw new Exception("Ocorreu um erro ao criar diretório");
            }
        }
        if (move_uploaded_file($_FILES['arquivo']['tmp_name'], $this->varDiretorio.$this->varArquivo)) {
            echo $debug? "Arquivo carregado com sucesso!<br>" : "";
            return true;
        } else {
            throw new Exception("Ocorreu um erro durante upload do arquivo: " . $_FILES["file"]["error"]);
        }
    }    

    public function carregarArquivoItaushoplines($fws_, $debug = false) {

        $objAr = $this->carregarArquivo($fws_,$debug);     
        $objEv = $objAr->getEvento();     
        if($objAr->getStatusCarga() != ''){
            throw new Exception("O arquivo já foi processado.");
        }

        $objConf = $objAr->getEventoConfiguracao();

        $objArquivo = new LeitorArquivo();
        $objArquivo->setNmArquivo($objAr->getArquivoGerado()); 
        $objArquivo->carregarArquivo($this->varDiretorio . $objArquivo->getNmArquivo(),$debug);
        
        $listaLinhas = $objArquivo->getListaLinhas();
        $totalLinhas = $objArquivo->getTotalLinhas();

        //Começar com contador inverso igual a 1 para não gravar a primeira linha onde deve constar o titulo das colunas
        $descartarPrimeiraLinha = true;
        $descartarUltimaLinha   = true;
        if($descartarUltimaLinha){
            $totalLinhas = $totalLinhas-1;
        }
        if($descartarPrimeiraLinha){
            $contadorInverso = 1; 
            $totalLinhas = $totalLinhas-1;
        }else{
            $contadorInverso = 0;
        }
        $totalRegistros = $totalLinhas;
        
        //Loop em cima das linhas retornadas do arquivo
        $objItaushoplineRetorno = new ItaushoplineRetorno();
        while ($totalLinhas > 0) {
            $objItaushoplineRetorno = new ItaushoplineRetorno();
            $linha = $listaLinhas[$contadorInverso];
            
            $objItaushoplineRetorno->setTipoRegistro   (intval(substr($linha, 0,1))); #'posição(01',01) pega(1)
            $objItaushoplineRetorno->setCodInscricao   (substr($linha, 1,2)); #'posição(02,03) pega(2), Tipo da inscrição 01 para Cpf e 02 para Cnpj',
            $objItaushoplineRetorno->setNumeroInscricao(substr($linha, 3,14)); #'posição(04,17) pega(14)',
            $objItaushoplineRetorno->setCbAgencia      (substr($linha, 17,4)); #'posição(18,23) pega(4)',
            $objItaushoplineRetorno->setCbConta        (substr($linha, 23,5)); #'posição(24,29) pega(5)',
            $objItaushoplineRetorno->setCbDac          (substr($linha, 28,1)); #'posição(29,29) pega(1)',
            $objItaushoplineRetorno->setUsoEmpresa     (substr($linha, 37,25)); #'posição(38-62) pega(25) identificação do titulo na empresa',
            $objItaushoplineRetorno->setNumeroCarteira (substr($linha, 82,3)); #'posição(83-85) pega(03) número da Carteira',
            $objItaushoplineRetorno->setNossoNumero    (substr($linha, 85,8)); #'posição(86-93) pega(08) identificação do titulo no banco',
            $objItaushoplineRetorno->setDacNossoNumero (substr($linha, 93,1)); #'posição(94-94) pega(01)',
            $objItaushoplineRetorno->setCodCarteira    (substr($linha, 107,1)); #'posição(108-108) pega(01)',
            $objItaushoplineRetorno->setCodOcorrencia  (substr($linha, 108,2)); #'posição(109-110) pega(02)',
            $objItaushoplineRetorno->setDataOcorrencia (substr($linha, 110,6)); #'posição(111-116) pega(06) DDMMAA',
            $objItaushoplineRetorno->setNDocumento     (substr($linha, 116,10)); #'posição(117-126) pega(10)',
            $objItaushoplineRetorno->setConfirmacaoNossoNumero(substr($linha, 126,8)); #'posição(127-134) pega(08)', cod_inscrito 
            $objItaushoplineRetorno->setVencimento     (substr($linha, 146,6)); #'posição(147-152) pega(06) DDMMAA',
            $objItaushoplineRetorno->setValorTitulo    (substr($linha, 152,13)); #'posição(153-165) pega(13)',
            $objItaushoplineRetorno->setCodigoBanco    (substr($linha, 165,3)); #'posição(166,168) pega(03)',
            $objItaushoplineRetorno->setAgCobradora    (substr($linha, 168,4)); #'posição(169,172) pega(03) Ag Cobradora, Ag de liquidação ou Baixa',
            $objItaushoplineRetorno->setAgCobradoraDac (substr($linha, 172,1)); #'posição(173,173) pega(03) Dac Ag Cobradora, Ag de liquidação ou Baixa',
            $objItaushoplineRetorno->setEspecie        (substr($linha, 173,2)); #'posição(174,175) pega(02)',
            $objItaushoplineRetorno->setTarifaCobranca (substr($linha, 175,13)); #'posição(176,188) pega(13)',
            $objItaushoplineRetorno->setValorIof       (substr($linha, 214,13)); #'posição(215,227) pega(13)',
            $objItaushoplineRetorno->setValorAbatimento(substr($linha, 227,13)); #'posição(228,240) pega(13)',
            $objItaushoplineRetorno->setDescontos      (substr($linha, 240,13)); #'posição(241,253) pega(13)',
            $objItaushoplineRetorno->setValorPrincipal (substr($linha, 253,13)); #'posição(254,266) pega(13)',
            $objItaushoplineRetorno->setJurosMoraMulta (substr($linha, 266,13)); #'posição(267,279) pega(13)',
            $objItaushoplineRetorno->setOutrosCreditos (substr($linha, 279,13)); #'posição(280,292) pega(13)',
            $objItaushoplineRetorno->setBoletoDda      (substr($linha, 292,1)); #'posição(293,293) pega(01)',
            $objItaushoplineRetorno->setDataCredito    (substr($linha, 295,6)); #'posição(296,301) pega(06) DDMMAA',
            $objItaushoplineRetorno->setInstrCancelada (substr($linha, 301,4)); #'posição(302,305) pega(04) códigoda Instrução Cancelada',
            $objItaushoplineRetorno->setNomePagador    (trim(substr($linha, 324,30))); #'posição(325,354) pega(30)',
            $objItaushoplineRetorno->setErros          (trim(substr($linha, 377,8))); #'posição(378,385) pega(08) Registros rejeitados ou alegação do pagador ou registro de mensagm informativa',
            $objItaushoplineRetorno->setCodLiquidacao  (substr($linha, 392,2)); #'posição(393,394) pega(02) Meio pelo qual o titulo foi liquidado',
            $objItaushoplineRetorno->setNumeroSequencial(substr($linha, 394,6)); #'posição(395,400) pega(06)',
            
            $objItaushoplineRetorno->setCodInscrito(intval(substr($linha, 128,8)));
            $objItaushoplineRetorno->setCodArquivo($fws_['codArquivo']);
            $objItaushoplineRetorno->setVersao('1');
            
            $return[] = clone $objItaushoplineRetorno;
           
            $totalLinhas--;
            $contadorInverso++;
        }
        
        $objItaushoplineRetorno->setListaRetorno($return);
        try {
            // Validar agência/conta do arquivo — contas legadas + configuração do evento
            $agCcArquivo = 'ag:' . $objItaushoplineRetorno->getCbAgencia() . '-cc:' . $objItaushoplineRetorno->getCbConta();
            $contasPermitidas = ['ag:4113-cc:03695']; // contas legadas

            // Adicionar conta da configuração do evento
            $idBeneficiario = $objConf->getItauBolecodeIdBeneficiario();
            if (!$idBeneficiario) {
                $idBeneficiario = $objConf->getItauShoplineCodEmpresa();
            }
            if ($idBeneficiario) {
                $agConfig = substr($idBeneficiario, 0, 4);
                $ccConfig = substr($idBeneficiario, 6, 5);
                $contasPermitidas[] = 'ag:' . $agConfig . '-cc:' . $ccConfig;
            }

            if (!in_array($agCcArquivo, $contasPermitidas)) {
                throw new Exception("Arquivo de retorno da agência/cc: " . $objItaushoplineRetorno->getCbAgencia() . "/" . $objItaushoplineRetorno->getCbConta() . ", não habilitada.");
            }
            $algoErradoOcorreu = $objItaushoplineRetorno->salvarListaRetorno($debug);
        } catch (OkException $e) {
            // Atualiza o status do arquivo carregado, indicando que a carga executada
            if ($contadorInverso == $totalRegistros or !$algoErradoOcorreu) {
                try {
                    $objAr->getByIdExiste();
                    $statusArquivo = 'CARREGADO';
                    $objAr->setStatusCarga($statusArquivo);   
                    $objAr->atualizar();
                } catch (OkException $e) {
                    $statusArquivo = $e->getMessage();
                    $ok = true;
                } catch (Exception $e) {
                    $statusArquivo = $e->getMessage();
                    $ok = false;
                }
                echo '{
                "statusMessage":"' . $ok . '",
                "message":      "' . str_replace("\n", "", 'Total de linhas no arquivo: ' . $totalRegistros . '<br>Registros processados: ' . $contadorInverso . '<br>' . $message . '<br>' . $statusArquivo) . '",
                "codArquivo":"' . $fws_['codArquivo'] . '"
                }';
                exit();
            }
        } catch (Exception $exc) {
            $message = "Arquivo não carregado:".$exc->getMessage();
        }
        echo '{
        "statusMessage":"0",
        "message":      "'.$message.'",
        "codArquivo":"' . $fws_['codArquivo'] . '"            
        }';
    }
    
    public function associarInscritoPagamento($fws_, $debug = false) {
        try{
            $objAr = $this->carregarArquivo($fws_,$debug);     
            if(!$objAr->getStatusCarga() == 'CARREGADO'){
                throw new Exception("O arquivo deve ser carregado antes de associado.");
            }
            if(!$objAr->getStatusCarga() == 'ASSOCIADO'){
                throw new Exception("O arquivo já foi associado.");
            }

            $objConf = $objAr->getEventoConfiguracao();
            
            $file = new LeitorArquivo();
            $file->setNmArquivo($objAr->getArquivoGerado()); 
            $file->carregarArquivo($this->varDiretorio . $file->getNmArquivo(),$debug);

            $listaLinhas = $file->getListaLinhas();
            $totalLinhas = $file->getTotalLinhas();
            
            //Começar com contador inverso igual a 1 para não gravar a primeira linha 
            //onde deve constar o titulo das colunas
            //$contadorInverso = 1;
            $contadorInverso = 0;
            $totalRegistros = $totalLinhas;

            //Loop em cima das linhas retornadas do arquivo
            $arrayColunas = [];
            $ctLiquidacoes = 0;
            $ctPedidosEncontrados = 0;
            $ctPagamentosGravados = 0;
            $ctNaoLiquidacao = 0;
            $ctErros = 0;
            while($totalLinhas > 0){
                $linha = $listaLinhas[$contadorInverso];
                unset($arrayColunas);
                $arrayColunas = explode(';', $linha);

                try{
                    if($objAr->getTipoArquivo()=='ITAUSHOPLINE' || $objAr->getTipoArquivo()=='ITAU-BOLECODE'){
                        // Nosso número: 8 bytes na posição 86-93
                        $nossoNumeroRaw = substr($linha, 85, 8);
                        $nossoNumeroInt = intval($nossoNumeroRaw);

                        // Registros com nosso número < 1600000 não são nossos — ignorar silenciosamente
                        if ($nossoNumeroInt < 1600000) {
                            throw new Exception("");
                        }

                        if(substr($linha, 108,2) != '06'){ //06=liquidação
                            $ctNaoLiquidacao++;
                            throw new Exception("Ocorrência " . substr($linha, 108,2) . " não é liquidação.");
                        }

                        $ctLiquidacoes++;

                        $objPedido = new Pedido();
                        $objPedido->setEventoId($objAr->getCodEvento());
                        if ($objAr->getTipoArquivo() == 'ITAU-BOLECODE') {
                            // Formato: pedido + sequencial(2) — remover últimos 2
                            $objPedido->setPedidoId(intval(substr($nossoNumeroRaw, 0, -2)));
                        } else {
                            $objPedido->setPedidoId($nossoNumeroInt); #nosso número
                        }
                        $objPedido->getPedidoById();

                        if($objPedido->getStatus()==''){
                            throw new Exception("Pedido ID não encontrado->".$objPedido->getPedidoId()."<-");
                        }
                        $ctPedidosEncontrados++;

                        // Verificar se pagamento já existe para este pedido
                        $objPagVerif = new Pagamento();
                        $objPagVerif->setCodEvento($objPedido->getEventoId());
                        $objPagVerif->setPedidoId($objPedido->getPedidoId());
                        $objPagVerif->getPagamentoByPedidoId();
                        if ($objPagVerif->getCodPagamento() != '') {
                            throw new OKException("Pagamento ja registrado anteriormente");
                        }

                        $objPagamento = new Pagamento();
                        $objPagamento->setCodEvento($objPedido->getEventoId());
                        $objPagamento->setPedidoId($objPedido->getPedidoId());
                        $objPagamento->setCodArquivo($objAr->getCodArquivo());
                        $objPagamento->setCodInscrito($objPedido->getInscritoId());

                        $numeroFormatado = intval(substr($linha, 152,13));
                        $money['i'] = substr($numeroFormatado, 0,-2);
                        $money['c'] = substr($numeroFormatado, -2);
                        $numeroFormatado = $money['i'].'.'.$money['c'];
                        $objPagamento->setValor($numeroFormatado);

                        $dataCredito = substr($linha, 295,6);
                        $data_formato_novo = DateTime::createFromFormat('dmy', $dataCredito);

                        if ($data_formato_novo !== false) {
                            $data_formato_novo->setTime(0, 0, 0);
                            $dataCredito = $data_formato_novo->format('Y-m-d H:i:s');
                        } else {
                            $dataCredito = null;
                        }

                        $objPagamento->setDataPagamento($dataCredito);
                        $objPagamento->setFormaPagamento('BOLETO');
                        $objPagamento->setStatus('ATIVADA');
                        $objPagamento->salvarCargaPagamento();
                        $ctPagamentosGravados++;
                    }

                }catch (OKException $e){
                    $ctPagamentosGravados++;
                    $_pidLog = isset($objPedido) ? $objPedido->getPedidoId() : intval(substr($linha, 85,8));
                    $_urlInscrito = 'index.php?content=view/admin/consulta-dados-congresso-listagem-inscritos.php&pedido_id=' . $_pidLog;
                    $message .= "<br><span style='color:#28a745;'>linha: ".$contadorInverso.": ".$e->getMessage().": Pedido Id: <a href='".$_urlInscrito."' target='_blank' style='color:#28a745; text-decoration:underline;'>".$_pidLog."</a></span>";
                }catch (Exception $e){
                    if($e->getMessage()!=''){
                        $ctErros++;
                        $message .= "<br>linha: ".$contadorInverso.": ".$e->getMessage().": Nosso Num: ".intval(substr($linha, 85,8));
                    }
                }
                // $objInscApto->limparObj(); Verificar se precisa

                $totalLinhas--;
                $contadorInverso++;
            }
            $resumo = '<b>Resumo:</b> Linhas: ' . $totalRegistros
                . ' | Liquidações: ' . $ctLiquidacoes
                . ' | Pedidos encontrados: ' . $ctPedidosEncontrados
                . ' | Pagamentos gravados: ' . $ctPagamentosGravados
                . ' | Outras ocorrências: ' . $ctNaoLiquidacao
                . ' | Erros: ' . $ctErros;
            $messageLog = $resumo . $message;
            $message = $resumo;
            // Atualiza o status do arquivo carregado, indicando que a carga já foi feita.
            if ($contadorInverso == $totalRegistros){            
                try {
                    $statusArquivo = 'ASSOCIADO';
                    $objAr->setStatusCarga($statusArquivo);   
                    $objAr->setMessageLog($messageLog); 
                    $objAr->atualizar();   
                } catch (OkException $e) {
                    $statusArquivo = $e->getMessage();
                    $ok = true;
                } catch (Exception $e) {
                    $statusArquivo = $e->getMessage();
                    $ok = false;
                }
            }else{
                $ok = false;
                $message = 'Arquivo não carregado';
                $statusArquivo = 'Não carregado';
            }
        } catch (Exception $e) {
            $statusArquivo = $e->getMessage();
            $ok = false;
        }            
        echo '{
        "statusMessage":"' . $ok . '",
        "message":      "' .str_replace("\n","",$message .'<br>'. $statusArquivo) . '",
        "codArquivo":"' . $objAr->getCodArquivo() . '"            
        }';
    }
}

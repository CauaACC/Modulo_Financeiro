<?php
require_once(dirname(__FILE__)."/../domain/ReferenciaTemplateHelper.php");
require_once(dirname(__FILE__)."/../domain/Inscrito.php");

class  GerenciarPedidoAC{

    /** Evita repetir o UPDATE do dia padrao do evento a cada inscrito do lote. */
    private static $diaVencEventoGravado = false;

    /**
     * Gera N mensalidades (pedidos) para um único inscrito, em loop.
     * Cada iteração delega para acaoGerarMensalidade() ajustando:
     *   - datVencimento (+ i meses a partir da data base)
     *   - referencia    (tags {MES}/{ANO}... substituídas pelo venc. da parcela)
     *   - prefixoPedido (anexa " N/total" ou " N" no final)
     *
     * Defaults quando qtd > 1 e campos vazios:
     *   - referencia  vazia  → "{MES}/{ANO}"
     *   - prefixo     vazio  → "Mensalidade"
     *
     * Força cod_taxa='-1' para que cada iteração use valor/vencimento próprios.
     */
    public static function acaoGerarMensalidadesLote($obj, $MSG, $fws_, $debug=false){
        try {
            $qtd            = isset($fws_['qtdParcelasGerar']) ? intval($fws_['qtdParcelasGerar']) : 1;
            $parcelaInicial = isset($fws_['parcelaInicial'])   ? intval($fws_['parcelaInicial'])   : 1;
            $totalParcelas  = isset($fws_['totalParcelas'])    ? intval($fws_['totalParcelas'])    : 0;
            $referenciaBase = isset($fws_['referencia'])       ? $fws_['referencia']               : '';
            $prefixoBase    = isset($fws_['prefixoPedido'])    ? $fws_['prefixoPedido']            : '';
            $datVencBase    = isset($fws_['datVencimento'])    ? $fws_['datVencimento']            : '';

            if ($qtd < 1)            $qtd = 1;
            if ($parcelaInicial < 1) $parcelaInicial = 1;

            // Defaults leves quando está gerando em lote
            if ($referenciaBase === '') $referenciaBase = '{MES}/{ANO}';
            if ($prefixoBase    === '') $prefixoBase    = 'Mensalidade';

            for ($i = 0; $i < $qtd; $i++) {
                $fwsIter = $fws_;
                $numParcela = $parcelaInicial + $i;

                // Vencimento desta iteração = base + i meses (preservando o dia original)
                if (!empty($datVencBase)) {
                    try {
                        $dt = new DateTime($datVencBase);
                        $diaOriginal = (int) $dt->format('d');
                        $dt->modify('first day of this month');
                        if ($i > 0) {
                            $dt->modify("+{$i} month");
                        }
                        $ultimoDia = (int) $dt->format('t');
                        $dia = min($diaOriginal, $ultimoDia);
                        $dt->setDate((int) $dt->format('Y'), (int) $dt->format('m'), $dia);
                        $fwsIter['datVencimento'] = $dt->format('Y-m-d');
                    } catch (Exception $e) {
                        // Mantém a data original em caso de parse inválido
                    }
                }

                // Referência: substitui tags usando o vencimento desta iteração
                if (!empty($fwsIter['datVencimento'])) {
                    $fwsIter['referencia'] = ReferenciaTemplateHelper::substituirTags($referenciaBase, $fwsIter['datVencimento']);
                } else {
                    $fwsIter['referencia'] = $referenciaBase;
                }

                // Sufixo da numeração no prefixo (entra na descrição)
                $sufixoNum = $totalParcelas > 0 ? $numParcela . '/' . $totalParcelas : (string) $numParcela;
                $fwsIter['prefixoPedido'] = trim($prefixoBase . ' ' . $sufixoNum);

                // Força modo manual: cada iteração usa valor/vencimento próprios,
                // não os da taxa (a taxa é fixa e sobrescreveria nossos cálculos).
                $fwsIter['cod_taxa'] = '-1';

                self::acaoGerarMensalidade($obj, $MSG, $fwsIter, $debug);
            }
        } catch (Exception $e) {
            echo "<br>Erro no lote de mensalidades: " . $e->getMessage();
        }
    }

    /**
     * Mensalidades automaticas da inscricao publica.
     *
     * Chamado no fim de GerenciarInscricaoAC::salvarInscricao. Reusa o motor da
     * Acao em Lote (acaoGerarMensalidadesLote) com os parametros da configuracao
     * do evento (mens_*) e, se existir, a taxa "Mensalidade" do perfil.
     *
     * Regras:
     *   - Opt-in por evento: mens_gerar_auto=1 (+ recebe_pagamento_mensalidade e mens_ativa).
     *   - Perfil isento nao recebe mensalidade; fluxo TICKETS nunca gera.
     *   - Valor: taxa "Mensalidade" do perfil > mens_valor da config.
     *   - Cronograma (mens_deslocar_cronograma, padrao ligado):
     *       ligado/NULL = 1a parcela no mes seguinte a inscricao;
     *       0 = todas as parcelas desde mens_data_primeira, mesmo vencidas.
     *   - Dia do vencimento: mens_dia_venc > dia de mens_data_primeira > 10.
     *   - Anti-duplicidade: nada e gerado se o inscrito ja tem pedido de mensalidade.
     *
     * NUNCA deixa excecao vazar nem HTML escapar (a inscricao ja esta gravada e o
     * motor da echo de progresso): tudo vai bufferizado para o inscricao-log.
     */
    public static function gerarMensalidadesAutomaticas($objInscrito, $objEvConf, $debug=false){
        $nivelBufferEntrada = ob_get_level();
        try {
            if ($objEvConf->getMensGerarAuto() != 1) return;
            if ($objEvConf->getRecebePagamentoMensalidade() != 1) return;
            if ($objEvConf->getMensAtiva() != 1) return;
            if ($objEvConf->getTipoFluxoInscricao() == 'TICKETS') return;

            $qtd = intval($objEvConf->getMensQtd());
            if ($qtd < 1) {
                executarLog('Mensalidade auto NAO gerada (mens_qtd vazio) - inscrito '.$objInscrito->getCodInscrito(), 'inscricao-log', true);
                return;
            }

            $objPerfil = $objInscrito->getObjPerfil();
            if (!$objPerfil) {
                $objInscrito->carregarOjbPerfil();
                $objPerfil = $objInscrito->getObjPerfil();
            }
            if ($objPerfil and $objPerfil->getPerfilIsento() == 1) return;

            /* Valor: taxa "Mensalidade" do perfil prevalece sobre mens_valor da config */
            $valor = floatval(str_replace(',', '.', (string)$objEvConf->getMensValor()));
            if ($objPerfil) {
                try {
                    $objPerfil->carregarTaxaMensalidade(false, $debug);
                    $valorPerfil = floatval(str_replace(',', '.', (string)$objPerfil->getObjTaxa()->getTxUnicaAVistaValor()));
                    if ($valorPerfil > 0) {
                        $valor = $valorPerfil;
                    }
                } catch (Exception $e) {
                    /* perfil sem taxa "Mensalidade": segue com o valor da config */
                }
            }
            if ($valor <= 0) {
                executarLog('Mensalidade auto NAO gerada (sem valor na config e no perfil) - inscrito '.$objInscrito->getCodInscrito(), 'inscricao-log', true);
                return;
            }

            /* Anti-duplicidade: reedicao do cadastro repassa por este fluxo */
            $objPedido = new Pedido();
            $objPedido->setInscritoId($objInscrito->getCodInscrito());
            $objPedido->setEventoId($objInscrito->getCodEvento());
            if ($objPedido->existePedidoMensalidade($debug)) return;

            $dataPrimeira = $objEvConf->getMensDataPrimeira();
            $deslocarCfg  = $objEvConf->getMensDeslocarCronograma();
            $deslocar     = ($deslocarCfg === '' or $deslocarCfg === null or intval($deslocarCfg) == 1);

            if (!$deslocar and !$dataPrimeira) {
                executarLog('Mensalidade auto NAO gerada (cronograma fixo sem mens_data_primeira) - inscrito '.$objInscrito->getCodInscrito(), 'inscricao-log', true);
                return;
            }

            $dia = intval($objEvConf->getMensDiaVenc());
            if (($dia < 1 or $dia > 28) and $dataPrimeira) {
                $dia = intval(date('d', strtotime($dataPrimeira)));
            }
            if ($dia < 1 or $dia > 31) {
                $dia = 10;
            }

            if ($deslocar) {
                $dt = new DateTime('first day of next month');
            } else {
                $dt = new DateTime($dataPrimeira);
                $dt->modify('first day of this month');
            }
            $dt->setDate((int)$dt->format('Y'), (int)$dt->format('m'), min($dia, (int)$dt->format('t')));

            $fwsMens = array(
                'qtdParcelasGerar' => $qtd,
                'parcelaInicial'   => 1,
                'totalParcelas'    => $qtd,
                'referencia'       => ReferenciaTemplateHelper::padraoDefault(),
                'prefixoPedido'    => 'Mensalidade',
                'datVencimento'    => $dt->format('Y-m-d'),
                'valorPedido'      => $valor,
                'tipoPedido'       => 'mensalidade',
                'cod_taxa'         => '-1',
                'acaoPedido'       => 'gerarPedido',
            );

            ob_start();
            self::acaoGerarMensalidadesLote($objInscrito, 'MSG', $fwsMens, $debug);
            $saida = trim(strip_tags(str_replace('<br>', ' | ', ob_get_clean())));
            executarLog('Mensalidade auto - inscrito '.$objInscrito->getCodInscrito().': '.$saida, 'inscricao-log', true);
        } catch (Exception $e) {
            /* fecha somente buffers abertos por este metodo */
            while (ob_get_level() > $nivelBufferEntrada) { @ob_end_clean(); }
            executarLog('Erro mensalidade auto - inscrito '.$objInscrito->getCodInscrito().': '.$e->getMessage(), 'inscricao-log', true);
        }
    }

    /**
     * Troca o DIA de vencimento das parcelas de um inscrito, preservando mes/ano.
     *
     * Roda uma vez por inscrito listado (mesmo contrato das outras acoes em lote).
     * Mexe so no que esta em aberto e ainda vai vencer — parcela paga ou vencida
     * fica como esta, e o que foi pulado aparece no retorno da tela.
     *
     * fws_ esperado: novoDiaVencimento (1..31) e acaoVencimento ('gravar'|'testar').
     */
    public static function acaoAlterarDiaVencimento($obj, $MSG, $fws_, $debug=false){
        try {
            $novoDia = isset($fws_['novoDiaVencimento']) ? intval($fws_['novoDiaVencimento']) : 0;
            if ($novoDia < 1 || $novoDia > 31) {
                throw new Exception('Informe o novo dia de vencimento (1 a 31).');
            }

            $objInscrito = new Inscrito();
            $objInscrito->setCodInscrito($obj->getCodInscrito());
            $objInscrito->getInscritoById();

            $objPedido = new Pedido();
            $objPedido->setInscritoId($objInscrito->getCodInscrito());
            $objPedido->setEventoId($objInscrito->getCodEvento());

            $previa = $objPedido->listarPrevisaoNovoDiaVencimento($novoDia, $debug);

            echo "<br><b>".$objInscrito->getCodInscrito()." - ".$objInscrito->getNomeInscrito()."</b>";
            if (count($previa) == 0) {
                echo "<br><span style='color:#a86a00'>Nenhuma parcela em aberto e a vencer — nada a alterar.</span><br>";
                return;
            }

            if ($fws_['acaoVencimento'] == 'gravar') {
                $qtd = $objPedido->alterarDiaVencimentoEmLote($novoDia, $debug);
                echo "<br>".$qtd." parcela(s) alterada(s) para o dia ".$novoDia.":";

                /* o dia e regra do contrato: opcionalmente vira o padrao do evento.
                   Uma vez so, ainda que o lote passe por varios inscritos. */
                if ($fws_['atualizarDiaVencEvento'] and !self::$diaVencEventoGravado) {
                    self::$diaVencEventoGravado = true;
                    $objConf = new EventoConfiguracao();
                    $objConf->setCodEvento($objInscrito->getCodEvento());
                    $objConf->setMensDiaVenc($novoDia);
                    if ($objConf->salvarDiaVencimentoMensalidade($debug)) {
                        echo "<br><i>Dia padrão de vencimento do evento atualizado para ".$novoDia.".</i>";
                    }
                }
            } else {
                echo "<br>TESTE — ".count($previa)." parcela(s) ficariam assim (nada foi gravado):";
            }

            echo "<br><span style='font-size:12px;color:#555'>";
            foreach ($previa as $linha) {
                echo "<br>&nbsp;&nbsp;".$linha['referencia']
                   . ": ".formatDateView($linha['vencimento_atual'])
                   . " &rarr; ".formatDateView($linha['vencimento_novo']);
            }
            echo "</span><br>";

        } catch (Exception $e) {
            echo "<br>".$e->getMessage();
        }
    }

     /**
     * @param type $obj Trabalho
     * @param type $flgPresenca (0 para não compareceu) ou (1 para compareceu)
     * @param type $debug
     * @throws Exception
     */
    public static function acaoGerarMensalidade($obj, $MSG, $fws_, $debug=false){
        try {
                $objInscrito = new Inscrito();
                $objInscrito->setCodInscrito($obj->getCodInscrito());
                $objInscrito->getInscritoById();

                $objInscrito->carregarOjbPerfil($debug);
                $objPerfil = $objInscrito->getObjPerfil();
                $objInscrito->carregarObjEvento();
                $objEv = $objInscrito->getObjEvento();
                
                /*Localizar taxas por período*/
                // if($objPerfil->getValoresPorPeriodo()){
                //     $condicoes['data_hoje'] = Date('Y-m-d H:i:s');  
                // }else{
                //     $condicoes = false;  
                // }
                // $objPerfil->carregarTaxaAtiva($condicoes, $debug);


                if($fws_['cod_taxa']!='' and $fws_['cod_taxa']!='-1'){
                    $objTaxa = new Taxa();
                    // $codTaxaResidente = 2802;
                    // $codTaxaPediatra = 2804;
                    $objTaxa->setCodTaxa($fws_['cod_taxa']);
                    $objTaxa->getTaxaById();

                    // /*@todo tratar quando não houver data de vencimento definida*/
                    $valorParcela   = $objTaxa->getTxUnicaAVistaValor();
                    $maxVencimento  = $objTaxa->getMaxDataVencimento();
                    $diasVencimento = $objTaxa->getDiasVencimento();
                    $tipo           = $objTaxa->getTipo();
                    $maxQtdParcela  = $objTaxa->getMaxQtdParcela();
                    $datVencimento = $objTaxa->getDataVencimento();
                }else{
                    // /*@todo tratar quando não houver data de vencimento definida*/
                    $valorParcela  = $fws_['valorPedido'];
                    $datVencimento = $fws_['datVencimento'];
                    $tipo = $fws_['tipoPedido'];
                    if($fws_['prefixoPedido']==""){
                        $fws_['prefixoPedido'] = ucfirst($tipo);
                    }
                }   
                if($tipo=='mensalidade'){
                    $status = 'aguardando pagamento';
                }else{
                    $status = 'ATIVADA';
                }

                // if ($objInscrito->getCodPerfil()==259){
                //     $valorParcela   = '100.00';
                //     // $diasVencimento = '3';
                //     // $maxVencimento  = '2024-03-10';
                //     $datVencimento = '2024-03-11';
                //     $tipo          = 'mensalidade';
                //     $maxQtdParcela = 1;
                //     $status        = 'aguardando pagamento';
                // }
                // if ($objInscrito->getCodPerfil()==258){
                //     $valorParcela   = '250.00';
                //     // $diasVencimento = '3';
                //     // $maxVencimento  = '2024-03-10';
                //     $datVencimento = '2024-03-11';
                //     $tipo          = 'mensalidade';
                //     $maxQtdParcela = 1;
                //     $status        = 'aguardando pagamento';
                // }
                // if ($objInscrito->getCodPerfil()==235){
                //     $valorParcela   = '530.00';
                //     // $diasVencimento = '3';
                //     // $maxVencimento  = '2024-03-10';
                //     $datVencimento = '2024-03-11';
                //     $tipo          = 'mensalidade';
                //     $maxQtdParcela = 5;
                //     $status        = 'aguardando pagamento';
                // }
                // /*definindo data de vencimento*/
                // $datVencimento = date('Y-m-d');
                if (!$diasVencimento){
                    $diasVencimento = 3;
                }
                if (!$datVencimento){
                     $hoje = date('Y-m-d');
                     $dataCalculada = date('Y-m-d', strtotime($hoje . ' + ' . $diasVencimento . ' days'));
                     $datVencimento = min($maxVencimento, $dataCalculada);     
                }    
                    
                /* Setando Pedido */
                $objPedido = new Pedido();
                $objPedido->setInscritoId($objInscrito->getCodInscrito());
                $objPedido->setEventoId($objInscrito->getCodEvento());
                $objPedido->setTipoProdutoId(2);
                $objPedido->setQuantidade(1);

                //$objPedido->setDescricao("Cod.Insc.: ".$objInscrito->getCodInscrito(). "-".ANO_EVENTO. " - ".ABREV_EVENTO." - ".$objPerfil->getTitulo());
    //            $objPedido->setDescricao("Insc: ".$objInscrito->getCodInscrito()." - ".$objEv->getProjeto()." - ".$objEv->getCodEvento()."-".$objEv->getAbrevEvento()." - ".$objPerfil->getTitulo());
                // 1° P.Mensalidade 007/2024 51-ATUALIZAÇÃO em PEDIATRIA-Pediatras                   
                $stringDescricao  = "";
                // $stringDescricao .= "1°Mensalidade ";
                if($fws_['prefixoPedido']!=''){
                    $stringDescricao .= $fws_['prefixoPedido']." ";
                }else{
                    $stringDescricao .= $objPerfil->getTitulo()." ";
                }
                $stringDescricao .= "E-".$objEv->getCodEvento()." ";
                $stringDescricao .= "I-".$objInscrito->getCodInscrito()." ";
                $stringDescricao .= $objEv->getProjeto()? $objEv->getProjeto()." " : "";
                $stringDescricao .= $objEv->getAbrevEvento()? $objEv->getAbrevEvento()." " : "";
                $stringDescricao .= $objInscrito->getNomeInscrito()." ";

                $objPedido->setDescricaoCompleta($stringDescricao);
                $objPedido->setDescricao(substr($stringDescricao,0,98));
                
                $objPedido->setDatVencimento($datVencimento);
                $objPedido->setValorTotal($valorParcela);
                $objPedido->setMaxQtdParcela($maxQtdParcela);
                $objPedido->setTipo($tipo);
                // Idempotente: se vier da rota de lote, as tags já foram substituídas e isto vira no-op.
                // Cobre o fluxo qtd=1 (que não passa pelo acaoGerarMensalidadesLote).
                $referenciaResolvida = ReferenciaTemplateHelper::substituirTags($fws_['referencia'], $datVencimento);
                $objPedido->setReferencia($referenciaResolvida);
                $objPedido->setStatus($status);

                $stringComp = "";
                $stringComp .= "<br>".$referenciaResolvida;
                $stringComp .= "<br>R$ ".$valorParcela;
                $stringComp .= "<br>Dt.Venc. ".formatDateView($datVencimento);                

                if($fws_['acaoPedido']=='gerarPedido'){
                    $stringComp = $stringDescricao.$stringComp;
                    $objPedido->salvar($debug);
                }else{
                    throw new OKException($stringDescricao);
                }
                
    
        }catch (OKException $e){
            echo "<br><br>linha: ".$ii.":".$e->getMessage().$stringComp;
        }catch (Exception $e){
            echo "<br><br>linha: ".$ii.":".$e->getMessage();
        }
    }

    public static function listarPedidosPagamentoOld($objInscrito, $objPerfil, $objEv, $objEvConf, &$fws_, $debug = false)
    {
        try {
            $objPedido = new Pedido();
            $objPedido->setInscritoId($objInscrito->getCodInscrito());
            $objPedido->setEventoId($objInscrito->getCodEvento());
            $objPedido->carregarListaPedidosByInscritoId();

            $valorTotalPedido = 0;
            if($objPerfil->getPerfilIsento()==1){
                throw new Exception("Isento");
            }
            foreach ($objPedido->getListaPedidos() as $objLIP) {
                if($objLIP->getDatVencimento() == '0000-00-00'){
                    $objLIP->setDatVencimento('');
                }

                if($objLIP->getStatus()!='DESATIVADA'){
                    echo "<span style='color:black;font-size:15px;'>";
    //                echo "<b>Descrição do Pedido ".$objLIP->getInscritoId()." - ". numberPad($objLIP->getId(),8).":</b>";

                    if($fws_['op_selecionar_ingresso']){
                        echo "<br>".$objLIP->getDescricao()." - R$ ".$objLIP->getValorTotal()."<br>";
                        $objIngresso = new Ingresso();
                        $objIngresso->setEventoId($objLIP->getEventoId());
                        $objIngresso->setPedidoId($objLIP->getId());
                        $objIngresso->carregarListaIngressosByPedidoId();
                        echo "<br>Pessoas cadastradas:<br>";
                        echo "<table with=150px><tr><td style='width: 155px;'>Nome</td><td>Rg</td></tr>";
                        foreach ($objIngresso->getListaIngressos() as $objLI) {
                            echo "<tr style='font-size:12px;'><td>".$objLI->getNome().'</td><td>'.$objLI->getRg().'</td></tr>';
                        }
                        echo "</table>";
                        echo ""
                        . "<br>Quantidade de Pessoas: ". count($objIngresso->getListaIngressos())
                        . "<br>Quantidade de Dias: ", $objInscrito->getIntervaloEstadia('dias');
                    }
                    /** Testando Descontos */
                    if($objLIP->getDescontoAplicado()!='SIM'){
                        $fws_['valor_cobrado']=$objLIP->getValorTotal();
                        // echo $fws_['valor_cobrado'].'<-';
                        $fws_ = CarregarOptionsAC::calcularDescontos($objInscrito, $objVoucher, $fws_, $debug);
                        if($fws_['APLICAR-DESCONTO'] == true){
                            try{
                                $objLIP->setValorTotal(intval($fws_['valor_cobrado']).'.00');
                                $objLIP->setDescontoAplicado('SIM');
                                $objLIP->setDescontoDescricao($fws_['desconto_descricao']);
                                $objLIP->salvar();
                            } catch (OKException $ex) { }                    
                        }
                    }
                    echo "<span style='font-size:18px;color:green'>";
                    $BLOQUEAR_INFORMAR_VOUCHER = false;
                    if($objLIP->getDescontoAplicado()=='SIM'){
                        /**@todo não bloquear informar voucher para inscritos do 7 Congresso. É preciso fazer alguma verificação, para saber se pode usar vouhcer em cima de uma inscrição que já recebeu desconto, ou definir uma hierarquia de descontos e vouchers para saber quem pode ser aplicado em quem */
                        if($objEvConf->getCodEvento()!=61){
                            $BLOQUEAR_INFORMAR_VOUCHER = true;
                        }
                        echo "<br>".$objLIP->getDescontoDescricao();
                    }    
                    echo "<br>Valor total do pedido: R$ ". formatMoneyView($objLIP->getValorTotal());
                    echo "<br></span>";
                    $valorTotalPedido = $valorTotalPedido+floatval($objLIP->getValorTotal());
                    // echo $valorTotalPedido.'-';
                }
            }
            if (intval($valorTotalPedido)!=0) {
                // if($objEv->getCodEvento()==70){
                //     echo "Liberação de recebimento dos pagamentos prevista para 17/07/2024.<br>Você será avisado por e-mail quando entrar em funcionamento.";
                // }
                /*@todo para o ACLS */
                if($objInscrito->getCodEvento()!=12){
                // if($objInscrito->getCodEvento()!=12 and $objEv->getCodEvento()!=70){    
            ?>
            <span style='color:black;font-size:15px;'>
                <?php
                if ($_SERVER['HTTP_HOST']=='eventos.test') {
                    $debugTeste=true;
                }
                ?>
                <?php
                if($objInscrito->getInscritoPossuiVoucher()=='' and $objEvConf->getEmitirVoucher()==1 and !$BLOQUEAR_INFORMAR_VOUCHER){
                    echo "<br><b><font color=\'#f56800\'>Você possui um voucher?</font></b>";
                    echo "<font color=\'000000\'>";
                    echo "<br>Informe o código de seu voucher aqui:";
                    echo "<br><input type='text' value='' name='codVoucher'  id='codVoucher'>"
                    . "<input type='hidden' value='".$objInscrito->getCodInscrito()."' id='codInscrito' name='codInscrito'> ";
                    echo "&nbsp;&nbsp;&nbsp;&nbsp;";
                    echo "<button class=\"btn btn-xs btn-primary\" id=\"btnCarregarVoucher\" type=\"submit\">Carregar Voucher</button>";
                    echo "<div id='msgRetornoVoucher' style='color:red'></div>";

                    echo "<br><br><a href=\'mailto:eventos@opctec.com.br?subject=[Boleto Bancário] - \'>Qualquer dúvida entre em contato.</a></font>";
                }
                ?>
                
                <?php /**@todo, tratar aqui o encerramento do recebimento das inscrições */?>
                <?php if ($objEvConf->getCodEvento()==86 and Date('YmdHi')>=202505231500){ ?>
                    <font color=red>Período de Pagamento Encerrado.</font>
                <?php }else if ($objEvConf->getCodEvento()==75){ ?>
                    <font color=red>Período de Pagamento Encerrado.</font>
                <?php }else if ($objPedido->getStatus()=='Pago via acerto'){ ?>
                    <font color=red>Pago via acerto.</font>                    
                <?php }else if ($objEvConf->getGatewayPagamento()=='SISPRIME'){ ?>
                    <?php
                    $objLIP->testarDatVencimentoItauShopline($fws_);
                    ?>
                    <?php include('sisprimeForm.php') ?>
                <?php }else if ($objEvConf->getGatewayPagamento()=='ITAU-SHOPLINE'){ ?>
                    <?php
                    $objLIP->testarDatVencimentoItauShopline($fws_);
                    ?>
                    <?php include('itaushoplineForm.php') ?>
                <?php }else if ($objPerfil->getComplemento()!=''){ ?>
                    <?php include('inscricao_botao_boleto_complemento.php') ?>

                <?php }else if ($objEvConf->getCodEvento()=='48'){ ?>
                    Inscrição Isenta
                <?php }else{ ?>
                    <?php if ($debugTeste==true) { ?>
                        <form method="POST" action="http://appboleto.test/api/login/evento" target="_blank">
                    <?php } else { ?>
                        <form method="POST" action="https://pg.opctec.net/api/login/evento" target="_blank">
                    <?php } ?>
                        Selecione uma forma de pagamento:
                        <?php if ($objEvConf->getGatewayPagamento()!=''){ ?>
                            <?php if (verifItemArray('pix', json_decode($objEvConf->getOpcaoPagamento()))) { ?>
                                <BR><input type="radio" name="tipo_pagamento" value="pix"    required="require"/> PIX
                            <?php } ?>
                            <?php if (verifItemArray('boleto', json_decode($objEvConf->getOpcaoPagamento()))) { ?>
                                <BR><input type="radio" name="tipo_pagamento" value="Boleto" required="require"/> Boleto
                            <?php } ?>
                            <?php if (verifItemArray('cartaoCredito', json_decode($objEvConf->getOpcaoPagamento()))) { ?>
                            <BR><input type="radio" name="tipo_pagamento" value="cartao" required="require"/> Cartão
                            <?php } ?>
                        <?php }else if ($objLIP->getEventoId()==23 or $objLIP->getEventoId()==24 or $objLIP->getEventoId()==25 or $objLIP->getEventoId()==21) {?>
                            <BR><input type="radio" name="tipo_pagamento" value="cartao" required="require"/> Cartão
                            <?php if ($debugTeste==true) { ?>
                            <BR><input type="radio" name="tipo_pagamento" value="pix"    required="require"/> PIX
                            <BR><input type="radio" name="tipo_pagamento" value="Boleto" required="require"/> Boleto
                            <BR><input type="radio" name="tipo_pagamento" value="cartao" required="require"/> Cartão
                            <?php } ?>
                        <?php }else if ($objLIP->getEventoId()==34){?>
                            <BR><input type="radio" name="tipo_pagamento" value="pix"    required="require"/> PIX
                            <BR><input type="radio" name="tipo_pagamento" value="cartao" required="require"/> Cartão
                        <?php }else{?>
                            <BR><input type="radio" name="tipo_pagamento" value="pix"    required="require"/> PIX
                            <BR><input type="radio" name="tipo_pagamento" value="cartao" required="require"/> Cartão
                            <BR><input type="radio" name="tipo_pagamento" value="Boleto" required="require"/> Boleto
                        <?php } ?>
                        <input type="hidden" name="nome"  value="<?php echo $nome_inscrito ?>">
                        <input type="hidden" name="email" value="<?php echo $objInscrito->getEMail()?>">
                        <input type="hidden" name="cod_inscrito" value="<?php echo $_SESSION['_cod_inscrito']?>">
                        <input type="hidden" name="pedido_id" value="<?php echo $objLIP->getId()?>">
                        <input type="hidden" name="evento_id" value="<?php echo $objInscrito->getCodEvento()?>">
                                <input type="hidden" name="rota_retorno" value="<?php echo 'hutec/'.$objInscrito->getCodEvento()?>">
                        <h4><button class="btn btn-primary" type="submit" >Clique aqui para efetuar pagamento</button></h4>
                    </form>
                <?php } ?>
            </span>
            <?php
            }else{
                    
                }
            }else{
                echo "Nenhum valor encontrado.";
            }
        } catch (Exception $exc) {
            if($objEv->getCodEvento()>=9){
                if($objPerfil->getPerfilIsento()==1){
                    if($objEvConf->getRecebePagamento()==1){
                        echo "Isento";
                    }
                }else{    
                    echo $exc->getMessage().'<br>';
                    echo 'Aguardando atualização...<br>';
                }
            }
        }
    }

    public static function listarPedidosPagamento($objInscrito, $objPerfil, $objEv, $objEvConf, &$fws_, $debug = false)
    {
        try {
            $objPedido = new Pedido();
            $objPedido->setInscritoId($objInscrito->getCodInscrito());
            $objPedido->setEventoId($objInscrito->getCodEvento());
            $objPedido->carregarListaPedidosByInscritoId();

            $valorTotalPedido = 0;
            if ($objPerfil->getPerfilIsento() == 1) {
                throw new Exception("Isento");
            }

            // --- CSS do layout (inline, sem conflitar) ---
            echo '<style>
            .table-pedidos { margin-bottom:10px; }
            .table-pedidos th, .table-pedidos td { vertical-align: middle !important; }
            .table-pedidos thead th { background:#f5f7f6; color:#555; font-weight:600;
                font-size:12px; text-transform:uppercase; letter-spacing:.03em; border-bottom:2px solid #e3e6e5; }
            .table-pedidos .col-acao{ width:1%; white-space:nowrap; }
            .table-pedidos .col-valor{ white-space:nowrap; }
            .table-pedidos .ped-ref{ font-weight:600; color:#333; }
            .table-pedidos .ped-desc{ display:block; color:#999; font-size:12px; font-weight:normal; }
            .table-pedidos tfoot td{ background:#f5f7f6; font-weight:600; }
            .sit{ display:inline-block; padding:3px 10px; border-radius:12px;
                font-size:12px; font-weight:600; white-space:nowrap; }
            .sit-pago{ background:#e6f4ea; color:#2e7d32; }
            .sit-aberto{ background:#fff4e0; color:#a86a00; }
            .sit-vencido{ background:#fdecea; color:#c0392b; }
            .btn-pagar-agora{ background:#2D937E; color:#fff; border:none; border-radius:4px;
                padding:6px 14px; font-weight:600; white-space:nowrap; }
            .btn-pagar-agora:hover, .btn-pagar-agora:focus{ background:#257866; color:#fff; }
            .btn-recibo{ color:#2D937E; background:none; border:none; padding:6px 8px;
                font-weight:600; white-space:nowrap; }
            .btn-recibo:hover{ color:#257866; text-decoration:underline; }
            .ped-acerto{ color:#777; font-size:12px; white-space:nowrap; }
            .table-pedidos .col-seq{ width:1%; color:#999; text-align:center; }
            .sel-tipo-pag{ display:inline-block; width:auto; height:32px; padding:2px 6px;
                border:1px solid #ccc; border-radius:4px; margin-right:4px; vertical-align:middle; }
            </style>';

            /* Formas de pagamento habilitadas no evento — mesma regra da listagem antiga
               (view/inscricao/inscricao_botao_boleto.php): config do evento e, sem config, o trio padrao */
            $formasPagamento = array();
            if ($objEvConf->getGatewayPagamento() != '') {
                $opcaoPagamento = json_decode($objEvConf->getOpcaoPagamento());
                if (verifItemArray('pix', $opcaoPagamento))           { $formasPagamento['pix']    = 'PIX'; }
                if (verifItemArray('cartaoCredito', $opcaoPagamento)) { $formasPagamento['cartao'] = 'Cartão'; }
                if (verifItemArray('boleto', $opcaoPagamento))        { $formasPagamento['Boleto'] = 'Boleto'; }
            }
            if (count($formasPagamento) == 0) {
                $formasPagamento = array('pix' => 'PIX', 'cartao' => 'Cartão', 'Boleto' => 'Boleto');
            }

            // --- Cabeçalho da tabela ---
            echo '<div class="table-responsive">
            <table class="table table-striped table-pedidos">
            <thead>
            <tr>
                <th class="col-seq">#</th>
                <th>Parcela / Referência</th>
                <th>Vencimento</th>
                <th class="text-right col-valor">Valor</th>
                <th>Situação</th>
                <th>Pago em</th>
                <th class="text-center col-acao">Ação</th>
            </tr>
            </thead>
            <tbody>';
            $totalPago   = 0;
            $totalAberto = 0;
            $hojeYmd     = date('Y-m-d');
            $seq         = 0;

            foreach ($objPedido->getListaPedidos() as $objLIP) {
                if ($objLIP->getDatVencimento() == '0000-00-00') {
                    $objLIP->setDatVencimento('');
                }
                if ($objLIP->getStatus() == 'DESATIVADA') {
                    continue;
                }

                // (opcional) manter sua lógica de descontos
                if ($objLIP->getDescontoAplicado() != 'SIM') {
                    $fws_['valor_cobrado'] = $objLIP->getValorTotal();
                    $fws_ = CarregarOptionsAC::calcularDescontos($objInscrito, $objVoucher, $fws_, $debug);
                    if ($fws_['APLICAR-DESCONTO'] == true) {
                        try {
                            $objLIP->setValorTotal(intval($fws_['valor_cobrado']).'.00');
                            $objLIP->setDescontoAplicado('SIM');
                            $objLIP->setDescontoDescricao($fws_['desconto_descricao']);
                            $objLIP->salvar();
                        } catch (OKException $ex) { }
                    }
                }

                // Montagem das colunas
                $ref  = trim($objLIP->getReferencia()) != '' ? $objLIP->getReferencia() : $objLIP->getDescricao();
                $ven  = $objLIP->getDatVencimento() ? formatDateView($objLIP->getDatVencimento()) : '—';
                /* number_format direto: formatMoneyView divide por 100 quando o valor vem sem ponto
                   e nao usa separador de milhar (1000.00 sairia "1000,00") */
                $val  = number_format(floatval($objLIP->getValorTotal()), 2, ',', '.');
                $dtp  = $objLIP->getDataPagamento() ? formatDateView($objLIP->getDataPagamento()) : '—';

                $isPago   = ($objLIP->getCodPagamento() != '' or $objLIP->getStatus() == 'Pago via acerto');
                $vencido  = (!$isPago and $objLIP->getDatVencimento() and $objLIP->getDatVencimento() < $hojeYmd);

                $seq++;
                echo '<tr>';
                echo '  <td class="col-seq">'.$seq.'</td>';
                /* so a referencia: a descricao e um texto interno (E-135 I-50502 ...) que nao ajuda o inscrito */
                echo '  <td><span class="ped-ref">'.$ref.'</span></td>';
                echo '  <td>'.$ven.'</td>';
                echo '  <td class="text-right col-valor">R$ '.$val.'</td>';

                if ($isPago) {
                    echo '  <td><span class="sit sit-pago">Pago</span></td>';
                    echo '  <td>'.$dtp.'</td>';
                    echo '  <td class="text-center">';
                    if ($objLIP->getStatus() == 'Pago via acerto'){
                        echo "<span class='ped-acerto'>Pago via acerto</span>";
                    }else{
                        echo " <a class='btn btn-recibo' href='javascript:void(0);' onclick=\"gerarReciboPagamento('".$objLIP->getCodPagamento()."');\">"
                           . "<i class='fa fa-file-text-o'></i> Ver recibo</a>";
                    }
                    echo '  </td>';
                    $totalPago += floatval($objLIP->getValorTotal());
                } else {
                    echo $vencido
                        ? '  <td><span class="sit sit-vencido">Vencido</span></td>'
                        : '  <td><span class="sit sit-aberto">Em aberto</span></td>';
                    echo '  <td>—</td>';

                    // Mesmo endpoint que você já usa
                    $action = ($_SERVER["HTTP_HOST"]=="eventos.test")
                            ? "http://appboleto.test/api/login/evento"
                            : "https://pg.opctec.net/api/login/evento";

                    /* Selecao da forma de pagamento por linha (equivale aos radios da listagem antiga).
                       Com uma unica forma habilitada nao ha o que escolher: vai em hidden. */
                    if (count($formasPagamento) > 1) {
                        $campoTipoPagamento = '<select name="tipo_pagamento" class="sel-tipo-pag" required="required">
                                <option value="">Forma</option>';
                        foreach ($formasPagamento as $valorTipo => $rotuloTipo) {
                            $campoTipoPagamento .= '<option value="'.$valorTipo.'">'.$rotuloTipo.'</option>';
                        }
                        $campoTipoPagamento .= '</select>';
                    } else {
                        $formasChaves = array_keys($formasPagamento);
                        $campoTipoPagamento = '<input type="hidden" name="tipo_pagamento" value="'.$formasChaves[0].'">';
                    }

                    echo '  <td class="text-center col-acao">
                            <form method="POST" action="'.$action.'" target="_blank" style="margin:0;white-space:nowrap;">
                                '.$campoTipoPagamento.'
                                <input type="hidden" name="nome"        value="'.$objInscrito->getNomeInscrito().'">
                                <input type="hidden" name="email"       value="'.$objInscrito->getEMail().'">
                                <input type="hidden" name="cod_inscrito"value="'.$_SESSION['_cod_inscrito'].'">
                                <input type="hidden" name="pedido_id"   value="'.$objLIP->getId().'">
                                <input type="hidden" name="evento_id"   value="'.$objInscrito->getCodEvento().'">
                                <input type="hidden" name="rota_retorno"value="hutec/'.$objInscrito->getCodEvento().'">
                                <button type="submit" class="btn-pagar-agora"><i class="fa fa-credit-card"></i> Pagar</button>
                            </form>
                            </td>';
                    $totalAberto += floatval($objLIP->getValorTotal());
                }
                echo '</tr>';

                $valorTotalPedido += floatval($objLIP->getValorTotal());
            }

            echo '</tbody>';
            echo '<tfoot>
                <tr>
                    <td colspan="3">Total</td>
                    <td class="text-right col-valor">R$ '.number_format($valorTotalPedido, 2, ',', '.').'</td>
                    <td colspan="3">
                        <span class="sit sit-pago">Pago R$ '.number_format($totalPago, 2, ',', '.').'</span>
                        <span class="sit sit-aberto">Em aberto R$ '.number_format($totalAberto, 2, ',', '.').'</span>
                    </td>
                </tr>
            </tfoot>';
            echo '</table></div>';
            ?>
            <form name="abrirReciboPagamento" id="abrirReciboPagamento" method="post" action="<?php echo WB_URL_ACTION?>?content=view/recibos/recibo.php" target="_blank">
                <input type="hidden" value="" name="cod_pagamento" id="cod_pagamento">
            </form>
            <script language="JavaScript" type="text/javascript">
                function gerarReciboPagamento(codPagamento){
                    $('#cod_pagamento').val(codPagamento);
                    $('#abrirReciboPagamento').submit();
                }
            </script> 
            <?php 
            if (intval($valorTotalPedido) == 0) {
                echo "Nenhum valor encontrado.";
            }
            return;
        } catch (Exception $exc) {
            if ($objEv->getCodEvento() >= 9) {
                if ($objPerfil->getPerfilIsento() == 1 && $objEvConf->getRecebePagamento() == 1) {
                    echo "Isento";
                } else {
                    echo $exc->getMessage().'<br>';
                    echo 'Aguardando atualização...<br>';
                }
            }
        }
    }

    /**
     * Excluir pedido (move para pedidos_atualizados com auditoria e remove de pedidos).
     * Chamado via WB_URL_COMMAND com c=GerenciarPedido & m=excluir.
     * NAO static — CommandAC instancia via $obj->excluir($fws_), e PHP 8.1+ emite
     * deprecation se chamar metodo static via instancia.
     */
    public function excluir($fws_, $debug = false) {
        // Log em arquivo de auditoria (alem do registro em pedidos_atualizados)
        $logFile = dirname(__FILE__) . '/../../../../logn/eventos/geral/excluir-pedido.log';
        $logDir  = dirname($logFile);
        if (!is_dir($logDir)) { @mkdir($logDir, 0775, true); }
        $logRow = function($msg) use ($logFile) {
            @file_put_contents(
                $logFile,
                '[' . date('Y-m-d H:i:s') . '] [' . ($_SERVER['REMOTE_ADDR'] ?? '?') . '] '
                . '[sess=' . session_id() . '] '
                . '[cod_usuario=' . ($_SESSION['_cod_usuario'] ?? 'null') . '] '
                . $msg . "\n",
                FILE_APPEND
            );
        };

        $logRow('=== ENTRADA excluir() ==='
              . ' cod_pedido=' . ($fws_['cod_pedido'] ?? 'null')
              . ' cod_inscrito=' . ($fws_['cod_inscrito'] ?? 'null')
              . ' motivo=' . substr($fws_['motivo_exclusao'] ?? '', 0, 80));

        $cod_usuario = $_SESSION['_cod_usuario'] ?? null;
        if (empty($cod_usuario)) {
            $logRow('FALHA AUTH: sem _cod_usuario na sessao');
            $this->renderErroExcluir(
                'Sessão expirada ou não há usuário logado. Faça login novamente.',
                $fws_
            );
            return;
        }

        // Verifica direito de acesso ao SIS_GERENCIAR_INSCRITO (=13) cod_direito=5
        $temDireito = false;
        if (function_exists('FWA_DireitoSobreSistema')) {
            $codSistemaInscrito = defined('enumSistema::SIS_GERENCIAR_INSCRITO')
                ? enumSistema::SIS_GERENCIAR_INSCRITO : '13';
            $codEventoConst = defined('COD_EVENTO') ? COD_EVENTO : false;
            $temDireito = (@FWA_DireitoSobreSistema($cod_usuario, $codSistemaInscrito, 5, $codEventoConst) === true)
                       || (@FWA_DireitoSobreSistema($cod_usuario, '13', 5, $codEventoConst) === true);
        }
        $logRow('AUTH temDireito=' . ($temDireito ? 'true' : 'false'));
        if (!$temDireito) {
            $this->renderErroExcluir(
                'Usuário sem permissão para excluir pedidos.',
                $fws_
            );
            return;
        }

        $cod_pedido   = (int)($fws_['cod_pedido']      ?? 0);
        $cod_inscrito = (int)($fws_['cod_inscrito']    ?? 0);
        $motivo       = trim($fws_['motivo_exclusao']  ?? '');

        // cod_evento via inscrito (fallback para COD_EVENTO)
        $objI = new Inscrito();
        $objI->setCodInscrito($cod_inscrito);
        $objI->getInscritoByIdSemEvento();
        $cod_evento = ($objI->getCodEvento() != '') ? (int)$objI->getCodEvento() : (int)COD_EVENTO;
        $logRow('cod_pedido=' . $cod_pedido . ' cod_inscrito=' . $cod_inscrito
              . ' cod_evento=' . $cod_evento);

        $URL = WB_URL_VIEW . "?content=view/admin/inscritos-cadastro-ok.php&cod_inscrito=" . $cod_inscrito;

        if (!$Conexao_db = FWA_Conexao("OpcTec")) {
            $MSG = "Erro-de-Conexao";
        } elseif ($cod_pedido <= 0) {
            $MSG = "Erro--Pedido-nao-informado";
        } elseif ($motivo === '' || mb_strlen($motivo) < 5) {
            $MSG = "Motivo-da-exclusao-obrigatorio--Minimo-5-caracteres";
        } else {
            // Lista colunas de pedidos para INSERT explícito (pedidos_atualizados ganhou
            // colunas extras de auditoria e o antigo INSERT...SELECT * deixou de bater)
            $colunas = array();
            $rsCols  = FWA_Executa_SQL($debug, $Conexao_db, "SHOW COLUMNS FROM pedidos");
            if ($rsCols) {
                foreach ($rsCols as $rowCol) {
                    $colunas[] = $rowCol['Field'];
                }
            }

            if (empty($colunas)) {
                $MSG = "Erro-de-Execucao-ERRO-SQL-Listagem-Colunas-Pedidos";
            } else {
                $listaCol     = '`'.implode('`,`', $colunas).'`';
                $usuario_acao = (int)($_SESSION['_cod_usuario'] ?? 0);
                $ip_cliente   = addslashes($_SERVER['REMOTE_ADDR'] ?? '');
                $motivoEsc    = addslashes($motivo);

                $sqlBackup = "
                   INSERT INTO pedidos_atualizados ($listaCol, tipo_acao, motivo_acao, usuario_acao, ip_cliente_acao, data_acao)
                   SELECT $listaCol, 'EXCLUSAO', '$motivoEsc', $usuario_acao, '$ip_cliente', NOW()
                   FROM pedidos
                   WHERE (id=$cod_pedido) and (evento_id=$cod_evento)
                ";

                if (FWA_Executa_SQL($debug, $Conexao_db, $sqlBackup)) {
                    $sqlDelete = "DELETE FROM pedidos WHERE (id=$cod_pedido) and (evento_id=$cod_evento)";
                    if (FWA_Executa_SQL($debug, $Conexao_db, $sqlDelete)) {
                        $MSG = "Operação-Executada-com-Sucesso";
                    } else {
                        $MSG = "Erro-de-Execucao-ERRO-SQL-Delete-Pedido";
                    }
                } else {
                    $MSG = "Erro-de-Execucao-ERRO-SQL-Backup-Pedido";
                }
            }
        }

        $_SESSION['MSG'] = $MSG;
        $logRow('FIM MSG=' . $MSG . ' URL=' . $URL);

        $MSG_Imprimir = preg_replace('/--/', '<br>', $MSG);
        $MSG_Imprimir = preg_replace('/-/',  ' ',    $MSG_Imprimir);

        if ($debug) {
            echo $MSG_Imprimir;
            echo "<br><a href='{$URL}'>{$URL}</a><br>";
        } else {
            echo "<br>Aguarde um momento - " . date("d/m/Y - H:i:s");
            echo "<br>Gravando seus dados no sistema<br>";
            echo "<br>Este procedimento demora poucos segundos.<br>";
            echo "<br><a href='{$URL}'>{$URL}</a><br>";
            echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $URL . "\">";
        }
    }

    /**
     * Helper: tela de erro estilizada (NAO redireciona pra login automaticamente).
     * Usado quando auth falha em excluir() — em vez de exit silencioso ou meta-refresh
     * que confundem com sessao expirada.
     */
    private function renderErroExcluir($mensagem, $fws_) {
        $codInscrito = (int)($fws_['cod_inscrito'] ?? 0);
        $voltar = WB_URL_VIEW . '?content=view/admin/inscritos-cadastro-ok.php'
                . ($codInscrito ? '&cod_inscrito=' . $codInscrito : '');
        echo '<!DOCTYPE html><html lang="pt-Br"><head><meta charset="UTF-8"><title>Aviso</title></head>'
           . '<body style="margin:0;padding:40px;background:#f5f5f5;font-family:Arial,Helvetica,sans-serif;">'
           . '<div style="max-width:600px;margin:60px auto;background:#fff;border:1px solid #e0e0e0;'
           .   'border-radius:8px;padding:28px;box-shadow:0 1px 4px rgba(0,0,0,0.06);">'
           . '<h3 style="margin:0 0 12px;color:#d9534f;">Não foi possível excluir o pedido</h3>'
           . '<p style="color:#333;font-size:14px;line-height:1.5;">' . htmlspecialchars($mensagem) . '</p>'
           . '<div style="margin-top:20px;text-align:right;">'
           . '<a href="' . htmlspecialchars($voltar) . '" '
           .   'style="display:inline-block;padding:8px 18px;background:#4A90E2;color:#fff;'
           .   'border-radius:4px;text-decoration:none;font-size:13px;">Voltar</a>'
           . '</div></div></body></html>';
    }

}
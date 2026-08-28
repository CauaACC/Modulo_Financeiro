<?php
require_once(dirname(__FILE__)."/../../include/config.php");
//if($_SESSION['_administrativo']!='1'){
//    $OP_EMISSAO_BOLETO_EM_MANUTENCAO = true;
//    $OP_EMISSAO_BOLETO = false;
//}
$debug = false;
if($_SERVER['REMOTE_ADDR']=='177.82.0.288'){
    $debug = true;
}
if($OP_EMISSAO_BOLETO_EXTRA){
    /*boleto extra*/
    $OP_OCULTAR_BOLETO_PADRAO = false;
    require_once('inscricao_botao_boleto_extra.php');
}
/* Evento AGENDA (119): garante a data da visita para o vencimento escalonado do boleto,
   pois nem todas as views que incluem este arquivo populam $fws_['data_inicio_agenda'] */
if (in_array($objInscrito->getCodEvento(), array(119)) && empty($fws_['data_inicio_agenda'])) {
    try {
        $objInscrito->carregarListaOficinas($debug);
        foreach ($objInscrito->getListaOficinas() as $objOf) {
            if ($objOf->getStatus()=='ATIVADA' and $objOf->getTipo()=='Agenda') {
                $fws_['data_inicio_agenda'] = $objOf->getDataInicio();
            }
        }
    } catch (Exception $e) { /* inscrito sem agenda */ }
}
try {
    if($objInscrito->getCpf()) {
        // Aceita CPF ou CNPJ (pessoa juridica usa CNPJ)
        if(!validarCPF($objInscrito->getCpf()) && !validarCNPJ($objInscrito->getCpf())) {
            throw new Exception("<span style='color:red'>CPF inválido, entre contato para correção</span>");
        }
    }
    /* Verifica prazo de pagamento (exceto eventos com mensalidade ativa e quem já pagou) */
    $jaTemPagamento = ($objInscrito->getCodVoucher() != '' || $objInscrito->getCodIsento() != '' || $objInscrito->getTotalPagamentos() > 0 || $objInscrito->getTotalPerfisIsentos() > 0 || $objInscrito->getValorTotalPedidos() == 0);
    if (!$jaTemPagamento && !($objEvConf->getRecebePagamentoMensalidade() == 1 && $objEvConf->getMensAtiva() == 1)) {
        $hoje = date('Y-m-d');
        $prazoBloqueado = false;

        // 1. Data limite de pagamento do evento (mais prioritária)
        if ($objEvConf->getDataLimitePagamento() && $hoje > $objEvConf->getDataLimitePagamento()) {
            $prazoBloqueado = true;
        }
        // 2. Sem data limite: usa fim do período de inscrição como fallback
        if (!$prazoBloqueado && !$objEvConf->getDataLimitePagamento()) {
            if ($objEvConf->getDataInscricaoFim() && $hoje > date('Y-m-d', strtotime($objEvConf->getDataInscricaoFim()))) {
                $prazoBloqueado = true;
            }
        }

        if ($prazoBloqueado) {
            throw new Exception('Per&iacute;odo de pagamento encerrado.');
        }
    }
    /**@todo continuar implementação de nova listagem de pagamentos de mensalidades na área do inscrito */
    if ($objEvConf->getCodEvento() == 45 and $_SERVER['REMOTE_ADDR']=='177.82.0.28') {
            echo $debug? __LINE__.'-' : '';
            GerenciarPedidoAC::listarPedidosPagamento($objInscrito, $objPerfil, $objEv, $objEvConf, $fws_, $debug);
    }

    if ($OP_INCRICAO_GRATUITA){
        throw new Exception('Inscrição gratuita!');
    }

    if ($objInscrito->getCodPerfil()) {

        $objInscrito->carregarOjbPerfil($debug);
        $objPerfil = $objInscrito->getObjPerfil();
        $fws_['tipo_inscricao'] = $objInscrito->getTipoInscricao();
        //echo "<h4>Perfil Selecionado: ". CarregarOptionsAC::descTipoInscricao($fws_, $debug)."</h4>";
        echo "<h4 class='corTitulo'>".$objPerfil->getAbrevTipo().": " . $objPerfil->getTitulo() . "</h4>";
        if(!$OP_OCULTAR_BOLETO_PADRAO){
               echo $objPerfil->getInvestimento();
        }
        echo "<input type='hidden' name='cod_perfil' value='" . $objPerfil->getCodPerfil() . "'>";
        echo "<hr>";

        $condicoes['data_hoje'] = Date('Y-m-d H:i:s');
        $tipoData = "data de hoje";
        
//        if (Date('YmdHis') <= 20170601220000) {
//        if($_SERVER['REMOTE_ADDR']=='179.154.10.72'){
//            /* localizando taxa pela data de inscrição */
//            $tipoData = "data da inscrição";
//            $condicoes['data_hoje'] = $objInscrito->getDatIncl();
//        } else {
            /* localizando taxa pela data de hoje */
            $tipoData = "data de hoje";
            $condicoes['data_hoje'] = Date('Y-m-d H:i:s');
//        }

        try {
            $objPerfil->carregarListaTaxas($condicoes, $debug);

            foreach ($objPerfil->getListaTaxas() as $objL) {
                $valor_inscricao = $objL->getTxUnicaAVistaValor();
                $fws_['data_desconto']       = formatDateView($objL->getDataDesconto());
                $fws_['max_data_vencimento'] = formatDateView($objL->getMaxDataVencimento());
                $fws_['dias_vencimento']     = $objL->getDiasVencimento();
                
                /*centralizar em um método do objeto taxa o calculo data de vencimento*/
                if($objL->getDiasVencimento()==''){
                    $fws_['data_vencimento'] = formatDateView($objL->getMaxDataVencimento());
                }else{
                    $fws_['data_vencimento'] = date("d/m/Y", time() + ($objL->getDiasVencimento() * 86400));
                }
                /* verificando se data de vencimento calculada não ultrapassa a máxima data de vencimento */
                if (formatDateCompare(formatDateView($objL->getMaxDataVencimento())) < formatDateCompare($fws_['data_vencimento'])) {
                    $fws_['data_vencimento'] = formatDateView($objL->getMaxDataVencimento());
                }
                if ($ipLiberado) {
//                    echo "Levando a " . $tipoData . " em consideração para localizar valor: " . formatDateView($condicoes['data_hoje'])
//                    . "<br>Vencimento: " . $fws_['data_vencimento']
//                    . " - R$ " . formatMoneyView($objL->getTxUnicaAVistaValor()) . "<br>";
//                    echo "Comparação data de vencimento:<br>";
//                    echo $objL->getMaxDataVencimento()."<->".formatDateCompare(formatDateView($objL->getMaxDataVencimento()))."<br>";
//                    echo $fws_['data_vencimento']."<->".formatDateCompare($fws_['data_vencimento'])."<br>";
                }    
            }

        } catch (Exception $exc) {
            //echo $exc->getMessage();
        }
    }
    
    $FWS_DESCONTO_VOUCHER = 0;
    /* zerando valor da inscrição em virtude do inscrito ser isento */
    if ($objIsento->getCodIsento() != '') {
        echo "<b>Sua inscrição foi abonada pelo sistema de isenção.</b><br>";
//            if($objInscrito->getCodEvento()==2){
//                echo "<b>Sua inscrição foi abonada.</b><br>";
//            }else{
//                echo "O valor de R$ " . $valor_inscricao . ", referente a Inscrição foi Abonado.<br>";
//            }
        $OP_EMISSAO_BOLETO = false;
        $valor_inscricao = 0;
        echo $debug? __LINE__.'-' : '';
        $FWS_Pagamento   = true;
    }
    if($objInscrito->getInscritoPossuiVoucher()){
        /*Caso possua voucher, bloquear emissão de boletos*/
        $objVoucher = new Voucher();
        $objVoucher->setCodVoucher($objInscrito->getCodVoucher());
        $objVoucher->setStatus('ATIVADA');
        $objVoucher->getVoucherById();
        if($objVoucher->getPorcentagem()==100){        
            $OP_EMISSAO_BOLETO = false;
            $valor_inscricao = 0;
            echo $debug? __LINE__.'-' : '';
            $FWS_Pagamento = true;
            echo "<b>Sua inscrição foi abonada pelo voucher: ".$objInscrito->objInscritoVoucher->getCodVoucher()."</b><br>";
        }else{
            /*@TODO necessário calcular o desconto*/
            if($objVoucher->getPorcentagem()!='' or $objVoucher->getValor()!='' or $objVoucher->getValorInscricao()!=''){
                $FWS_DESCONTO_VOUCHER = 1;
            }
        }
    }
    
//    if ($OP_GERAR_BOLETO) {
        
//        if ($objInscrito->getPossuiBoletoGerado()) {
//            echo "<font color=red>Atenção: Você já gerou um boleto bancário para esta inscrição.
//            <br>Portanto, a alteração de itens que afetam o valor de sua inscrição está bloqueada.<br></font>";
//        } else {
//            echo "<font color=red>Atenção: Após gerar boleto bancário, a alteração de itens que afetam o valor de sua inscrição estará bloqueada.<br></font>";
//        }
//    }
    $fws_['nome_inscrito'] = $objInscrito->getNomeInscrito();
    $fws_['cod_inscrito']  = $objInscrito->getCodInscrito();
    $fws_['cpf']           = $objInscrito->getCpf();
    $fws_['endereco']      = $objInscrito->getEndereco();
    $fws_['numero']        = $objInscrito->getNumero();
    $fws_['complemento']   = $objInscrito->getComplemento();
    $fws_['cidade']        = $objInscrito->getCidade();
    $fws_['estado']        = $objInscrito->getEstado();
    $fws_['cep']           = $objInscrito->getCep();
    $fws_['dat_incl']           = $objInscrito->getDatIncl();
    
    /*Verificando se encontrou algum pagamento*/
    if (!$FWS_Pagamento) {
        echo $debug? __LINE__.'-' : '';
    $var_sql = "SELECT tp.cod_evento, tp.cod_pagamento, tp.cod_inscrito, tp.valor, tp.forma_pagamento"
                . ", tp.campo_7, tp.data_inclusao, tp.data_alteracao, tp.observacao, tp.numero_empenho, tp.boleto_extra, tp.ocultar_boleto_inscricao"
                . ", tp.pedido_id "
                . "FROM tab_pagamentos tp "
                . "WHERE (tp.cod_inscrito=" . $fws_['cod_inscrito'] . ")and(tp.cod_evento=" . $objInscrito->getCodEvento() . " and tp.status='ATIVADA')";

    echo $debug? $var_sql.'-' : '';

    //echo "</blockquote><fieldset><legend> Pagamento da Inscrição </legend><blockquote>";
        If ($RS_P = FWA_Executa_SQL($debug, $Conexao_db, $var_sql)) {
            echo $debug? __LINE__.'-' : '';
            If (!$RS_P->EOF) {
                echo $debug? __LINE__.'-' : '';
                While (!$RS_P->EOF) {
                    echo $debug? __LINE__.'-' : '';
                    #Adicionando nas variaveis, os valores dos campos retirados dos registros
                    $cod_evento      = $RS_P->fields[0];
                    $cod_pagamento   = $RS_P->fields[1];
                    $valor           = $RS_P->fields[3];
                    $forma_pagamento = $RS_P->fields[4];
                    $numero_boleto   = $RS_P->fields[5];
                    $data_inclusao_pagamento  = $RS_P->fields[6];
                    $data_alteracao_pagamento = $RS_P->fields[7];
                    $observacao      = $RS_P->fields[8];
                    $numero_empenho  = $RS_P->fields[9];
                    $boleto_extra  = $RS_P->fields[10];
                    $ocultar_boleto_inscricao  = $RS_P->fields[11];
                    $pedido_id  = $RS_P->fields[12];

                    /*Definindo comportamento dos boletos de acordo com pagamentos cadastrados*/
                    if($ocultar_boleto_inscricao){
                        $OP_EMISSAO_BOLETO = false;
                    }
                    if($boleto_extra){
                        $OP_EMISSAO_BOLETO_EXTRA = true;
                    }

                    If ($valor != "") {
//                        $valor = substr($valor, 0, strlen($valor) - 2) . "," . substr($valor, -2);
//                        $valor_pago .= substr($valor, 0, strlen($valor) - 2) . "," . substr($valor, -2);
                        $valor = formatMoneyView($valor);
                        $valor_pago .= formatMoneyView($valor);
                    } Else {
                        $valor = "O Pagamento ainda não consta no sistema.";
                    }

                    $data_inclusao_pagamento = substr($data_inclusao_pagamento, 6, 2) . "/" . substr($data_inclusao_pagamento, 4, 2) . "/" . substr($data_inclusao_pagamento, 0, 4) . " - " . substr($data_inclusao_pagamento, 8, 2) . "h" . substr($data_inclusao_pagamento, 10, 2);
                    If ($data_alteracao_pagamento != "") {
                        $data_alteracao_pagamento = substr($data_alteracao_pagamento, 6, 2) . "/" . substr($data_alteracao_pagamento, 4, 2) . "/" . substr($data_alteracao_pagamento, 0, 4) . " - " . substr($data_alteracao_pagamento, 8, 2) . "h" . substr($data_alteracao_pagamento, 10, 2);
                    }

                    if($forma_pagamento=='EMPENHO'){
                        echo "<br>Pagamento a ser efetuado via <b>Empenho</b>";
                        //echo "<br>Data Inclusão: <b> {$data_inclusao_pagamento}  </b>";
                        if($numero_empenho!='' and $numero_empenho!=' '){
                            echo "<br>Número: <b> {$numero_empenho}</b>";
                        } 
                        echo "<br>No valor de:     <b> {$valor}</b>";
                        echo $debug? __LINE__.'-' : '';
                        $FWS_Pagamento = false;
                    }else{
                        if($pedido_id == ''){
                            echo "<br>Recebemos o seu Pagamento:";
                            if ($forma_pagamento!='')
                                echo "<br> <b> {$forma_pagamento} </b>";

                            //echo "<br>Data Inclusão: <b> {$data_inclusao_pagamento}  </b>";
                            echo "<br>Valor Pago:     <b> R$ ".$valor."</b>";
                        }    
                            /* Calculando valor de boleto a ser gerado */
                            $valorCobrado = $fws_['valor_cobrado'];
                            $valorDevido = floatval($valor_inscricao) + floatval($valor_atividade) + floatval($valor_almoco) - floatval($valor_pago);
                            echo $debug? __LINE__.'-' : '';
                        $FWS_Pagamento = true;
                    }

                    #Movendo para o proximo registro
                    $RS_P->MoveNext();

                }
            } Else {
                echo $debug? __LINE__.'-' : '';
                $FWS_Pagamento = false;
            }
        } Else {
            echo $debug? __LINE__.'-' : '';
            $FWS_Pagamento = false;
        }
    }
    echo $debug? __LINE__.'-' : '';
    echo $debug? '<br><b>FWS_Pagamento</b>->'.$FWS_Pagamento.'<-<br>' : '';
    /** @todo Tarefa para melhorar listagem de pagamento de mensalidades. Quando um evento inscrição e mensalidades, pode ser que mesmo tendo algo pago precise gerar a lista de pedidos */
    if($objEv->getCodEvento()==89 or ($objEvConf->getRecebePagamentoMensalidade()==1 and $objEvConf->getMensAtiva()==1)){
        $FWS_Pagamento = 1;
    }
    
    If ($FWS_Pagamento == false and $objEv->getCodEvento()!=68 and $objEv->getCodEvento()!=34 and $objEv->getCodEvento()!=51 and $objIsento->getCodIsento()!=1) {
        echo $debug? __LINE__.'-' : '';
        $fws_['valor_cobrado'] = floatval($valor_inscricao) + floatval($valor_atividade) + floatval($valor_almoco);
        
        /**
         * Calculando desconto por tipo de inscrição
         * */
        // echo $debug? '<br>Tipo-inscrição:'.$fws_['tipo_inscricao']."<-<br>" : '';
        // /** @todo o boleto padrão esta sendo ocultado logo mais abaixo */
        // if(!$OP_OCULTAR_BOLETO_PADRAO){
        //     echo $debug? __LINE__.'-' : '';
        //     $fws_ = CarregarOptionsAC::calcularDescontos($objInscrito, $fws_, $debug);
        // }
        $fws_['valor_cobrado'] = $fws_['valor_cobrado'] . ",00";

        $MSG_AMBOS = "
        <br>Valor total da inscrição: R$ " . $fws_['valor_cobrado'] . "
        <br>Até a data de Vencimento: " . $fws_['data_vencimento'] . "
        <br>
        ";

        $MSG_AMBOS = "
        <br>Valor total da inscrição: R$ " . $fws_['valor_cobrado'] . "
        <br>
        ";

        $fws_['msg_boleto'] = $fws_['msg_boleto'] . $MSG_AMBOS;
        $fws_['msg_site']   = $fws_['msg_site'] . $MSG_AMBOS;

        #echo $_SESSION['content'];
        If (($_SESSION['content'] == "view/inscricao/inscricao_congresso_ok.php")
        or ( $_SESSION['content'] == "view/inscricao/inscricao_trabalhos_inicio.php")) {
            //echo $MSG_AMBOS;
        }
        
//        echo "<font color='red'>
//            <!--<br>Atenção: O valor do boleto bancário esta atrelado apenas ao fato do inscrito ter optado ou não em participar uma oficina.-->
//            <!--<br><br><a href=\'mailto:eventos@opctec.com.br?subject=[Boleto Bancário] - \'>duvidas sobre boleto bancário clique aqui.</a>-->
//            </font>
//            <br>";
        
        If ($fws_['valor_cobrado'] != 0) {
            echo $debug? __LINE__.'-' : '';
            /** @todo Tarefa para melhorar listagem de pagamento de mensalidades. Quando um evento inscrição e mensalidades, pode ser que mesmo tendo algo pago precise gerar a lista de pedidos */
            if($objEv->getCodEvento()==45 or $objEv->getCodEvento()==68 or $objEv->getCodEvento()==89 or ($objEvConf->getRecebePagamentoMensalidade()==1 and $objEvConf->getMensAtiva()==1)){
                $OP_EMISSAO_BOLETO = true;
            }
            // if($objEv->getCodEvento()==75){
            //     $OP_EMISSAO_BOLETO = false;
            // }
            If ($OP_EMISSAO_BOLETO) {
                echo $debug? __LINE__.'-' : '';
                /* Gerar botão boleto aqui */
                $objBoleto = new GerenciarBoletoAC();
                $fws_['nomeBanco'] = $objEvConf->getGatewayPagamento(); //UNIPRIME ou SISPRIME
                    //GerenciarProcessoInscricaoAC::definicaoBancoBoleto($fws_, $debug);
                
                /* Gerar botão boleto aqui */
                $OP_OCULTAR_BOLETO_PADRAO = true;
                if(!$OP_OCULTAR_BOLETO_PADRAO){
                    If ($objInscrito->getInstitucional() == 1) {
                        If ($objInscrito->getNomeRecibo() != '' and $objInscrito->getCpfRecibo() != '') {
                            $fws_['nome_recibo'] = $objInscrito->getNomeRecibo();
                            $fws_['cpf_recibo'] = $objInscrito->getCpfRecibo();
                            $objBoleto->btnBoletoInstitucional($fws_, $debug);
                        }else{
                            echo "<b><font color='red'>Não é possível gerar boleto bancário, edite os dados pessoais e informe o cpnj da instituição.</font></b>";
                        }
                    } Else {
                        $objBoleto->btnBoleto($fws_, $debug);
                    }
                }
//                $valorComDesconto = $fws_['valor_cobrado'] - 100;
                /*
                echo "
                <!--
                <br><b><font color=\'#f56800\'>Instruções para pagamento do boleto com desconto:</font></b>
                <font color=\'000000\'>
                <br>Apesar de constar no boleto bancário o valor de R$ ".$fws_['valor_cobrado']." com vencimento para ".$fws_['data_vencimento'].".
                <br>Economize quitando sua inscrição o quanto antes.
                <br>Para pagamento via internet bank, basta informar o valor reduzido no aplicativo de pagamento do seu banco.
                <br>Para pagamento na boca do caixa bancário, lembre o atendente do caixa, que existe um desconto informado nas instruções do boleto.
                </font>
                -->
                <br><br><b><font color=\'#f56800\'>Atenção, para evitar transtornos:</font></b>
                <font color=\'000000\'>
                <br><b>AGUARDE UM DIA (24h) APÓS EMISSÃO DO BOLETO PARA EFETUAR PAGAMENTO DO MESMO.</b>
                <br>Essa medida se faz necessária em virtude da necessidade de registrarmos os boletos junto as instituições financeiras conforme normas estabelecidas pelo Banco Central.
                <br>O processo de registro do boleto pode variar de 2 a 12 horas após a data de emissão do documento.<br>
                </font>
                ";
                */
//                if($objInscrito->getCodEvento()!=18 and $objInscrito->getInscritoPossuiVoucher()==''){
                if($objInscrito->getInscritoPossuiVoucher()=='' and $objEvConf->getEmitirVoucher()==1){
                    echo "<br><b><font color=\'#f56800\'>Você possui um voucher?</font></b>";
                    echo "<font color=\'000000\'>";
                    echo "<br>Informe o código de seu voucher aqui:";
                    echo "<br><input type='text' value='' name='codVoucher'  id='codVoucher'>"
                    . "<input type='hidden' value='".$objInscrito->getCodInscrito()."' id='codInscrito' name='codInscrito'> ";
                    echo "&nbsp;&nbsp;&nbsp;&nbsp;";
                    echo "<button class=\"btn btn-xs btn-primary\" id=\"btnCarregarVoucher\" type=\"submit\">Carregar Voucher</button>";
                    echo "<div id='msgRetornoVoucher' style='color:red'></div>";
                    // echo "<br>Pagamento por voucher em manutenção.";

                    echo "<br><br><a href=\'mailto:eventos@opctec.com.br?subject=[Boleto Bancário] - \'>Qualquer dúvida entre em contato.</a></font>";
                }
            }else{
                if ($OP_EMISSAO_BOLETO_EM_MANUTENCAO) {
                    echo "<font color=\'#f56800\'>
                    <br>Atenção: Emissão do boleto em manutenção.
                    <br>Previsão de liberação: ".Date("d/m/Y")." 23h.
                    <br><br><a href='mailto:contato.opctec@gmail.com?subject=[Boleto Bancário] - '>duvidas sobre boleto bancário clique aqui.</a>
                    <br></font>";
                }
            }
        } else {
            //echo "<font color=\'#f56800\'>Fora do período de pagamento.</font>";
        }
        echo "</blockquote></fieldset>";
        /**@todo botão pagSeguro*/
//        if($objPedido->getId()!=''){

        /** @todo Tarefa para melhorar listagem de pagamento de mensalidades. Quando um evento inscrição e mensalidades, pode ser que mesmo tendo algo pago precise gerar a lista de pedidos */
        if($objEvConf->getCodEvento()==92 or $objEvConf->getCodEvento()==89 or ($objEvConf->getRecebePagamentoMensalidade()==1 and $objEvConf->getMensAtiva()==1)){
            echo $debug? __LINE__.'-' : '';
            GerenciarPedidoAC::listarPedidosPagamento($objInscrito, $objPerfil, $objEv, $objEvConf, $fws_, $debug);
        }else{ 
            echo $debug? __LINE__.'-' : '';
            try {
                $objPedido = new Pedido();
                $objPedido->setInscritoId($objInscrito->getCodInscrito());
                $objPedido->setEventoId($objInscrito->getCodEvento());
                $objPedido->carregarListaPedidosByInscritoId();

                $valorTotalPedido = 0;
                if($objPerfil->getPerfilIsento()==1){
                    throw new Exception("Isento");
                }
                foreach ($objPedido->getListaPedidos() as $objLI) {
                    if($objLI->getDatVencimento() == '0000-00-00'){
                        $objLI->setDatVencimento('');
                    }

                    if($objLI->getStatus()!='DESATIVADA'){
                        echo "<span style='color:black;font-size:15px;'>";
        //                echo "<b>Descrição do Pedido ".$objLI->getInscritoId()." - ". numberPad($objLI->getId(),8).":</b>";

                        if($fws_['op_selecionar_ingresso']){
                            echo "<br>".$objLI->getDescricao()." - R$ ".$objLI->getValorTotal()."<br>";
                            $objIngresso = new Ingresso();
                            $objIngresso->setEventoId($objPedido->getCodEvento());
                            $objIngresso->setPedidoId($objPedido->getId());
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
                        if($objPedido->getDescontoAplicado()!='SIM'){
                            $fws_['valor_cobrado']=$objPedido->getValorTotal();
                            // echo $fws_['valor_cobrado'].'<-';
                            $fws_ = CarregarOptionsAC::calcularDescontos($objInscrito, $objVoucher, $fws_, $debug);
                            if($fws_['APLICAR-DESCONTO'] == true){
                                try{
                                    $objPedido->setValorTotal(intval($fws_['valor_cobrado']).'.00');
                                    $objPedido->setDescontoAplicado('SIM');
                                    $objPedido->setDescontoDescricao($fws_['desconto_descricao']);
                                    $objPedido->salvar();
                                } catch (OKException $ex) { }                    
                            }
                        }
                        echo "<span style='font-size:18px;color:green'>";
                        $BLOQUEAR_INFORMAR_VOUCHER = false;
                        if($objPedido->getDescontoAplicado()=='SIM'){
                            /**@todo não bloquear informar voucher para inscritos do 7 Congresso. É preciso fazer alguma verificação, para saber se pode usar vouhcer em cima de uma inscrição que já recebeu desconto, ou definir uma hierarquia de descontos e vouchers para saber quem pode ser aplicado em quem */
                            if($objEvConf->getCodEvento()!=61){
                                $BLOQUEAR_INFORMAR_VOUCHER = true;
                            }
                            echo "<br>".$objPedido->getDescontoDescricao();
                        }    
                        echo "<br>Valor total do pedido: R$ ". formatMoneyView($objPedido->getValorTotal());
                        echo "<br></span>";
                        $valorTotalPedido = $valorTotalPedido+floatval($objPedido->getValorTotal());
                    }
                }
                if (intval($valorTotalPedido)!=0 && !$FWS_Pagamento) {
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
                    <?php if ($objEvConf->getCodEvento()==108){ ?>
                            <font color=red>Período de Pagamento Encerrado.</font>
                    <?php }else if ($objEvConf->getCodEvento()==86 and Date('YmdHi')>=202505231500){ ?>
                        <font color=red>Período de Pagamento Encerrado.</font>
                    <?php }else if ($objEvConf->getCodEvento()==75){ ?>
                        <font color=red>Período de Pagamento Encerrado.</font>
                    <?php }else if ($objPedido->getStatus()=='Pago via acerto'){ ?>
                        <font color=red>Pago via acerto.</font>
                    <?php }else if ($objEvConf->getGatewayPagamento()=='SISPRIME'){ ?>
                        <?php
                        $objLI->testarDatVencimentoItauShopline($fws_);
                        ?>
                        <?php include('sisprimeForm.php') ?>
                    <?php }else if ($objEvConf->getGatewayPagamento()=='ITAU-SHOPLINE'){ ?>
                        <?php
                        $objLI->testarDatVencimentoItauShopline($fws_);
                        ?>
                        <?php include('itaushoplineForm.php') ?>
                    <?php }else if ($objEvConf->getGatewayPagamento()=='ITAU-BOLECODE'){ ?>
                        <?php
                        $statusVencAgenda = $objLI->atualizarVencimentoAgendaEscalonado($fws_);
                        if ($statusVencAgenda === 'ENCERRADO') {
                            $linkWhats = defined('LINK_CONTATO_WHATS') ? LINK_CONTATO_WHATS : 'https://api.whatsapp.com/send?phone=5543996967790';
                            echo '<div class="alert alert-warning" style="margin:10px 0;">'
                               . 'O prazo para pagamento desta inscri&ccedil;&atilde;o j&aacute; encerrou.<br>'
                               . 'Para regularizar, fale com a organiza&ccedil;&atilde;o pelo '
                               . '<a href="'.$linkWhats.'" target="_blank" style="font-weight:bold;"><i class="fa fa-whatsapp"></i> chat via WhatsApp</a>.'
                               . '</div>';
                        } else {
                            $objLI->testarDatVencimentoItauShopline($fws_);
                            include('itauBolecodeForm.php');
                        }
                        ?>
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
                            <?php }else if ($objPedido->getEventoId()==23 or $objPedido->getEventoId()==24 or $objPedido->getEventoId()==25 or $objPedido->getEventoId()==21) {?>
                                <BR><input type="radio" name="tipo_pagamento" value="cartao" required="require"/> Cartão
                                <?php if ($debugTeste==true) { ?>
                                <BR><input type="radio" name="tipo_pagamento" value="pix"    required="require"/> PIX
                                <BR><input type="radio" name="tipo_pagamento" value="Boleto" required="require"/> Boleto
                                <BR><input type="radio" name="tipo_pagamento" value="cartao" required="require"/> Cartão
                                <?php } ?>
                            <?php }else if ($objPedido->getEventoId()==34){?>
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
                            <input type="hidden" name="pedido_id" value="<?php echo $objPedido->getId()?>">
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
        }#fim listar pedidos
        ?>
<!--@todo aguardo verificar procedimento-->
<!--        <span style='color:black;font-size:15px;'>
            <?php if ($_SERVER['HTTP_HOST']=='eventos.test') { ?>
                <form method="POST" action="http://appboleto.test/api/login/evento" target="_blank">
            <?php } else { ?>
                <form method="POST" action="https://pg.opctec.net/api/login/evento" target="_blank">
            <?php } ?>
                Selecione uma forma de pagamento:
                <BR><input type="radio" name="tipo_pagamento" value="pix"    required="require"/> PIX
                <BR><input type="radio" name="tipo_pagamento" value="cartao" required="require"/> Cartão
                <input type="hidden" name="nome" value="<?php echo $nome_inscrito ?>">
                <input type="hidden" name="email" value="<?php echo $objInscrito->getEMail()?>">
                <input type="hidden" name="cod_inscrito" value="<?php echo $_SESSION['_cod_inscrito']?>">
                <h4><button class="btn btn-primary" type="submit" >Clique aqui para efetuar pagamento</button></h4>
            </form>
        </span>-->
<!--@fim-->
        <?php
    }#Pagamento Efetuado 
    else{
       try {
        echo $debug? __LINE__.'-' : '';
            if($OP_EMISSAO_BOLETO==false){
                if($objEv->getCodEvento()==61){
                    throw new Exception('');
                }
            }
            /* Listagem nova de pedidos (tabela com vencimento, situacao e acao).
               Eventos com mensalidade caem sempre neste ramo (FWS_Pagamento=1 la em cima),
               por isso a chamada da listagem nova precisa estar aqui tambem.
               Acrescente o codigo do evento no array para migrar outros eventos. */
            if (in_array($objEv->getCodEvento(), array(135))) {
                GerenciarPedidoAC::listarPedidosPagamento($objInscrito, $objPerfil, $objEv, $objEvConf, $fws_, $debug);
            } else {
            $objPedido = new Pedido();
            $objPedido->setInscritoId($objInscrito->getCodInscrito());
            $objPedido->setEventoId($objInscrito->getCodEvento());
            $objPedido->carregarListaPedidosByInscritoId();

            foreach ($objPedido->getListaPedidos() as $objLI) {
                if($objLI->getStatus()=='DESATIVADA'){
                    continue;
                }
                if($objLI->getDatVencimento() == '0000-00-00'){
                    $objLI->setDatVencimento('');
                }
                /**Não há valor para efetuar pagamento */
                if($objLI->getValorTotal()=='0.00'){
                    continue;
                }
                if($objLI->getStatus()!='DESATIVADA'){
                echo "<span style='color:black;font-size:15px;'>";
                echo "<br><br><b>Descrição do Pedido ".$objLI->getInscritoId()." - ". numberPad($objLI->getId(),8).":</b>";
                //echo "Pedido Extra:";
                echo "<br><span style='font-size:15px;'>".$objLI->getDescricao()."</span>";
                if($objLI->getReferencia()){
                    echo "<br><b>".$objLI->getReferencia()."</b>";
                }
                echo "<br>Valor: R$ ". formatMoneyView($objLI->getValorTotal());
                echo $objLI->getDatVencimento()? " Vencimento: ".formatDateView($objLI->getDatVencimento()) : "";
                    
                // if(
                    // ($objLI->getTipo()=='extra' or $objLI->getTipo()=='mensalidade')and
                    //  ($objLI->getStatus()!='DESATIVADA' and $objLI->getCodPagamento()=='' and $objLI->getStatusPagamento()!='ATIVADA')){

                    // if($objLI->getStatus()=='aguardando pagamento' and $objLI->getCodPagamento()=='' and $objLI->getStatusPagamento()!='ATIVADA'){
                if ($objIsento->getCodIsento() != ''){
                    echo "<br>Isenção registrada";
                }else if (isset($objVoucher) && $objVoucher->getPorcentagem()==100){
                    echo "<br>Abonado pelo voucher";
                }else if ($objLI->getStatus()=='Pago via acerto'){
                    echo "<br>Pago via acerto";
                }else if($objLI->getCodPagamento()!=''){
                    echo "<br>Pagamento registrado";
                    echo $objLI->getDataPagamento()? ": dt.pag. ".formatDateView($objLI->getDataPagamento()) : "";
                }else{                         
                    ?>
                    <span style='color:black;font-size:15px;'>
                        <?php
                        if ($_SERVER['HTTP_HOST']=='eventos.test') {
                            $debugTeste=true;
                        }
                        ?>

                        <?php /**@todo, tratar aqui o encerramento do recebimento das inscrições */?>
                        <?php if ($objEvConf->getCodEvento()==75 or $objEvConf->getCodEvento()==108){ ?>
                            <font color=red>Período de Pagamento Encerrado.</font>
                            
                        <?php }else if ($objEvConf->getGatewayPagamento()=='SISPRIME'){ ?>
                            <?php
                            $objLI->testarDatVencimentoItauShopline($fws_);
                            ?>
                            <?php include('sisprimeForm.php') ?>
                        <?php }else if ($objEvConf->getGatewayPagamento()=='ITAU-SHOPLINE'){ ?>
                            <?php
                            $objLI->testarDatVencimentoItauShopline($fws_);
                            ?>
                            <?php include('itaushoplineForm.php') ?>
                        <?php }else if ($objEvConf->getGatewayPagamento()=='ITAU-BOLECODE'){ ?>
                            <?php
                            $objLI->testarDatVencimentoItauShopline($fws_);
                            ?>
                            <?php include('itauBolecodeForm.php') ?>

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
                                <?php }else if ($objPedido->getEventoId()==23 or $objPedido->getEventoId()==24 or $objPedido->getEventoId()==25 or $objPedido->getEventoId()==21) {?>
                                    <BR><input type="radio" name="tipo_pagamento" value="cartao" required="require"/> Cartão
                                <?php }else if ($objPedido->getEventoId()==34){?>
                                    <input type="radio" name="tipo_pagamento" value="pix"    required="require"/> PIX
                                    &nbsp|&nbsp
                                    <input type="radio" name="tipo_pagamento" value="cartao" required="require"/> Cartão
                                <?php }else{?>
                                    <BR>
                                    <input style="margin-left:10px;margin-right: 10px" type="radio" name="tipo_pagamento" value="pix"    required="require"/> PIX
                                    &nbsp|&nbsp
                                    <input style="margin-left:10px;margin-right: 10px" type="radio" name="tipo_pagamento" value="cartao" required="require"/> Cartão
                                    &nbsp|&nbsp
                                    <input style="margin-left:10px;margin-right: 10px" type="radio" name="tipo_pagamento" value="Boleto" required="require"/> Boleto
                                <?php } ?>
                                <input type="hidden" name="nome"  value="<?php echo $nome_inscrito ?>">
                                <input type="hidden" name="email" value="<?php echo $objInscrito->getEMail()?>">
                                <input type="hidden" name="cod_inscrito" value="<?php echo $_SESSION['_cod_inscrito']?>">
                                <input type="hidden" name="pedido_id" value="<?php echo $objLI->getId()?>">
                                <input type="hidden" name="evento_id" value="<?php echo $objInscrito->getCodEvento()?>">
                                <input type="hidden" name="rota_retorno" value="<?php echo 'hutec'?>">
                                <h4><button class="btn btn-primary" type="submit" >Clique aqui para efetuar pagamento</button></h4>
                            </form>
                        <?php } ?>
                </span>
                    <?php
                }
                //echo "<br>".$objLI->getDescricao()." - R$ ".$objLI->getValorTotal()."<br>";
                if($fws_['op_selecionar_ingresso']){
                    $objIngresso = new Ingresso();
                    $objIngresso->setEventoId($objPedido->getCodEvento());
                    $objIngresso->setPedidoId($objPedido->getId());
                    $objIngresso->carregarListaIngressosByPedidoId();
                    echo "<br>Pessoas cadastradas:<br>";
                    echo "<table with=150px><tr><td style='width: 155px;'>Nome</td><td>Rg</td></tr>";
                    foreach ($objIngresso->getListaIngressos() as $objLI) {
                        echo "<tr style='font-size:12px;'><td>".$objLI->getNome().'</td><td>'.$objLI->getRg().'</td></tr>';
                    }
                    echo "</table>";
                    echo ""
                    . "<br>Quantidade de Pessoas: ". count($objIngresso->getListaIngressos())
                    . "<br>Quantidade de Dias: ", $objInscrito->getIntervaloEstadia('dias')
                    . "<br>Valor total do pedido: R$ ". formatMoneyView($objPedido->getValorTotal())
                    . "</span>";
                }
                }
            }
            }/*fim do ramo da listagem antiga*/

        } catch (Exception $exc) {
            if($objEv->getCodEvento()>=9){
                echo "<br>".$exc->getMessage()."<br>";
            }
        }
    }
} catch (Exception $exc) {
    echo $exc->getMessage();
}
//boleto extra estava aqui
?>
<script language="JavaScript" type="text/javascript">
$(document).ready(function () {
    $('#btnCarregarVoucher').click(function(){
        adicionarInscritoVoucher($)
    });
});
function adicionarInscritoVoucher(){
    $('#msgRetornoVoucher').html('');
    if($('#codVoucher').val()==''){
        alert("É necessário informar um voucher!");
        return false;
    }
    codInscrito = $('#codInscrito').val();
    codVoucher  = $('#codVoucher').val();
    $.ajax({
        type: "POST",
        url: '<?php echo  WB_URL_COMMAND?>&t='+Math.random(),
        data: ({'codInscrito':codInscrito,'codVoucher':codVoucher, 'c':'GerenciarInscritoVoucher', 'm':'adicionarInscritoVoucher'}),
        dataType: "json",
        async: false,
        cache: false,
        success: function(data){
//            elementMsg = '#msgInscrito-'+codInscrito
            processJsonElement(data);
//            alert(data.message);
            if(data.statusMessage==1){
//                alert(data.message);
                location.reload(); 
            }else{
                $('#msgRetornoVoucher').html(data.message);
                $('#codVoucher').focus();
            }
        }
        ,timeout: 4000
        ,beforeSend: function() {
//                loading('div#carregando-'+codInscrito,'show');
        }
        ,error: function(ajax) {
            //showResponse(ajax);
        }
        ,complete: function() {        
//                loading('div#carregando-'+codInscrito,'hidden');
        }
    });
}
</script>

<?php

class GerenciarBoletoAvulsoAC {

    function __construct() {
        
    }

    public function carregarInscrito($fws_, $debug = false) {
        $obj = new Inscrito();
        $fws_['codInscrito'] = $fws_['codInscrito']? $fws_['codInscrito'] : $_GET['codInscrito'];
        $obj->setCodInscrito($fws_['codInscrito']);
        $obj->getInscritoById($debug);
        return $obj;
    }

    public function carregarBoletoAvulso($fws_, $debug = false) {
        $obj = new BoletoAvulso();
        $obj->setCodBoleto($fws_['codBoleto']);
        $obj->setCodInscrito($fws_['codInscrito']);
        $obj->getBoletoAvulsoById($debug);
        return $obj;
    }

    public function carregarBoletoAvulsoByIdPedido($fws_, $debug = false) {
        $obj = new BoletoAvulso();
        $obj->setPedido($fws_['pedido']);
        $obj->setCodInscrito($fws_['codInscrito']);
        $obj->getBoletoAvulsoByIdPedido($debug);
        return $obj;
    }

    public function editarBoletoAvulso($fws_, $debug = false) {
        $obj = $this->carregarBoletoAvulso($fws_,$debug);
        if($obj->getValorPago()!=''){
            echo '{
            "statusMessage":"0",
            "message":      "Boleto quitado não pode ser Editado",
            "codBoleto":"' . $obj->getCodBoleto() . '"
            }';
        exit();
        }
        /**
         * Escapando de testes quando estiver logado com gestor financeiro
         */
        if (!FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_CONTROLE_FINANCEIRO, 5, $cod_msg, false)){
            if($obj->getExtra()!='1'){
                echo '{
                "statusMessage":"0",
                "message":      "Boleto gerado na inscrição não pode ser Editado",
                "codBoleto":"' . $obj->getCodBoleto() . '"
                }';
            exit();
            }
        }

        echo '{
                  "statusMessage":"1",
                  "message":     "' . $obj->getMessage() . '",
                  "codBoleto":   "' . $obj->getCodBoleto() . '",
                  "codInscrito": "' . $obj->getCodInscrito() . '",
                  "sacado":      "' . $obj->getSacado() . '",
                  "cpfCnpj":     "' . $obj->getCpfCnpj() . '",
                  "dataVencimento":  "' . formatDateInsert($obj->getDataVencimento()) . '",
                  "valorBoleto":   "' . $obj->getValorBoleto() . '",
                  "dataDesconto":  "' . formatDateInsert($obj->getDataDesconto()) . '",
                  "valorDesconto": "' . $obj->getValorDesconto() . '",
                  "endereco":      "' . $obj->getEndereco() . '",
                  "numero":        "' . $obj->getNumero() . '",
                  "complemento":   "' . $obj->getComplemento() . '",
                  "cep":           "' . $obj->getCep() . '",
                  "cidade":        "' . $obj->getCidade() . '",
                  "estado":        "' . $obj->getEstado() . '",
                  "fone_contato": "' . $obj->getFoneContato() . '",
                  "bairro":        "' . $obj->getBairro() . '",
                  "instrucoes1":  "' . tratarJson($obj->getInstrucoes1()) . '",
                  "instrucoes2":  "' . tratarJson($obj->getInstrucoes2()) . '",
                  "instrucoes3":  "' . tratarJson($obj->getInstrucoes3()) . '",
                  "demonstrativo1":  "' . tratarJson($obj->getDemonstrativo1()) . '",
                  "demonstrativo2":  "' . tratarJson($obj->getDemonstrativo2()) . '",
                  "demonstrativo3":  "' . tratarJson($obj->getDemonstrativo3()) . '"
                 }';
        exit();
    }

    public function salvarBoletoSisprime($fws_, $debug = false) {
        try {
            // if ($fws_['novoCodBoleto'] == 0) {
            //     $fws_['codBoleto'] = NULL;
            // }       
            // $isForm = true;
            $obj = new BoletoAvulso();
            $obj->setCodInscrito($fws_['codInscrito']);
            $obj->setPedido($fws_['pedido']);
            // pedido_id e coluna NOT NULL em tab_boletos; setPedido() so alimenta a busca
            // por numero_documento. Sem este set, o INSERT do salvar() falha
            // com "Column 'pedido_id' cannot be null" em pedidos novos.
            $obj->setPedidoId($fws_['pedido']);
            $obj->getBoletoAvulsoByIdPedido($debug );
            if($obj->getCodBoleto()!=''){
                /*boleto já cadastrado*/
                // if(formatDateInsert($obj->getDataVencimento())!=$fws_['dataVencimento']){
                //     $boletoVencido = true;
                //     $fws_['codBoleto'] = NULL;
                //     $fws_['registrarNovamente']='1';
                //     $fws_['arquivoRemessa']='';
                //     $fws_['arquivoRemessaLinha']='';
                //     $obj->setNossoNumero(NULL);
                // }else{
                    throw new OkException("Boleto já cadastrado");
                // }
            }

            // $obj = $this->carregarBoletoAvulso($fws_); 
            if($obj->getValorPago()!=""){
                throw new Exception("Boleto Quitado não pode ser alterado.");
            }
//            if($obj->getExtra()==""){
//                throw new Exception("Boleto Gerado na inscrição não pode ser alterado, gere um novo boleto.");
//            }
            if(($obj->getFlgRegistrado()!="" or $obj->getFlgRemessa()!="")and($fws_['registrarNovamente']!='1')){
                throw new Exception("Boleto Registrado ou adicionado a Remessa"
                        . ", não pode ser alterado, gere um novo boleto.");
            }

            $obj->setAll($fws_, true, $debug);
            if($fws_['registrarNovamente']=='1'){
                $obj->setFlgRegistrado('');
                $obj->setFlgRemessa('');
            }
            $obj->salvar();
        } catch (OkException $e) {
            $ok = true;
            return $obj;
        } catch (Exception $e) {
            // Expoe a mensagem pro caller pra evitar fatal silencioso no btnBoletoAvulso(false).
            // O retorno false e mantido por compatibilidade com chamadas existentes.
            $this->erroSalvarBoletoSisprime = $e->getMessage();
            $ok = false;
            return false;
        }
    }
    public function salvarBoletoAvulso($fws_, $debug = false) {
        try {
            if ($fws_['novoCodBoleto'] == 0) {
                $fws_['codBoleto'] = NULL;
            }       
            $isForm = true;
            $obj = $this->carregarBoletoAvulso($fws_); 
            if($obj->getValorPago()!=""){
                throw new Exception("Boleto Quitado não pode ser alterado.");
            }
//            if($obj->getExtra()==""){
//                throw new Exception("Boleto Gerado na inscrição não pode ser alterado, gere um novo boleto.");
//            }
            if(($obj->getFlgRegistrado()!="" or $obj->getFlgRemessa()!="")and($fws_['registrarNovamente']!='1')){
                throw new Exception("Boleto Registrado ou adicionado a Remessa"
                        . ", não pode ser alterado, gere um novo boleto.");
            }
            $obj->setAll($fws_, true, $debug);
            if($fws_['registrarNovamente']=='1'){
                $obj->setFlgRegistrado('');
                $obj->setFlgRemessa('');
            }
            $obj->salvar();
        } catch (OkException $e) {
            $ok = true;
        } catch (Exception $e) {
            $ok = false;
        }

        echo '{
        "statusMessage":"' . $ok . '",
        "message":      "' . $e->getMessage() . '",
        "codBoleto":"' . $obj->getCodBoleto() . '"
        }';
    }

    public function excluirBoletoAvulso($fws_, $debug = false) {
        try {
            $obj = $this->carregarBoletoAvulso($fws_,$debug);
            if($obj->getValorPago()!=""){
                throw new Exception("Boleto Quitado não pode ser excluido.");
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
        "codBoleto":"' . $fws_['codBoleto'] . '",
        "codInscrito":"' . $fws_['codInscrito'] . '"
        }';
    }

    public function desativarBoletoAvulso($fws_, $debug = false) {
        try {
            $obj = $this->carregarBoletoAvulso($fws_,$debug);
            if($obj->getValorPago()!=""){
                throw new Exception("Boleto Quitado não pode ser desativado.");
            }
            $obj->desativar();
        } catch (OKException $e) {
            $ok = true;
        } catch (Exception $e) {
            $ok = false;
        }

        echo '{
        "statusMessage":"' . $ok . '",
        "message":      "' . $e->getMessage() . '",
        "codBoleto":"' . $fws_['codBoleto'] . '",
        "codInscrito":"' . $fws_['codInscrito'] . '"
        }';
    }
    
    public function enviarEmailAvisoPadrao($fws_, $debug = false) {
        $enviarEmail = new GerenciarEmailAC();
        $enviarEmail->enviarEmailAvisoPadraoBoleto($fws_,$debug);
    }

    public function carregarTabelaBoletosAvulsos($fws_, $debug = false) {
        $obj = new BoletoAvulso();
        $obj->setCodInscrito($fws_['codInscrito']);
        $lista = [];
        try {
            $lista = $obj->getListaBoletosAvulsosByIdInscrito($debug);
        } catch (Exception $e) {}
        $ii = 0;
        if (!empty($lista)) {
            $objBoleto = new GerenciarBoletoAC();
            foreach ($lista as $objL) {
                $ii++;
                ?>
                <tr id="<?php echo  $objL->getCodBoleto() ?>">
                    <td>
                        <div style="color:#999; font-size:11px; margin-bottom:4px;">#<?php echo  $objL->getCodBoleto() ?></div>
                        <?php
                        if ((($objL->getExtra()==1) and ($objL->getFlgRemessa()!= 1) and ($objL->getFlgPagamento() != 1))
                            or(FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_CONTROLE_FINANCEIRO, 5, $cod_msg, false))){
                        ?>
                        <span class="btn-acao editar" title="Editar"><i class="fa fa-pencil"></i></span>
                        <span class="btn-acao btn-acao-excluir excluir" title="Excluir"><i class="fa fa-trash"></i></span>
                        <?php }else{ ?>
                        <span style="font-size:11px; color:#999;">Bloqueado</span>
                        <?php } ?>
                        <span class="btn-acao desativar" title="Desativar"><i class="fa fa-ban"></i></span>
                    </td>

                    <td>
                    <?php
                        echo "Cpf/Cnpj: " . formatCpfCnpjView($objL->getCpfCnpj())."<br>";
                        echo "Sacado: " . $objL->getSacado()."<br>";
                        echo $objL->getDescricao();
                        echo "Nosso N&uacute;m.: ".$objL->getNossoNumero()." | ";
                        echo "N&uacute;m. do Doc.: ".$objL->getNumeroDocumento()."<br>";
                        $objBoleto->btnBoletoAvulso($objL, $debug);

                        echo "<hr>";
                    ?>
                    </td>
                    <td><?php echo  $objL->getDataEmissao() ?></td>
                    <td><?php echo  $objL->getDataVencimento() ?></td>
                    <td><?php echo  $objL->getValorDocumento()?></td>
                    <td><?php echo  $objL->getDataDesconto()?></td>
                    <td><?php echo  $objL->getValorDesconto()?></td>
                    <td>
                        <?php
                        if($objL->getValorPago()!=''){
                            echo substr($objL->getValorPago(), 0, strlen($objL->getValorPago())-5).".00";
                        }
                        ?>
                    </td>
                    <td><?php
                        if($objL->getDesFlgPagamento()=='Pago'){
                            echo $objL->getDesFlgPagamento();
                            echo "<br>".$objL->getDataPagamento();
                        }
                        ?></td>
                    <td><?php echo  $objL->getStatusProcessoRegistro()?></td>
                    <td><?php echo  $objL->getDesExtra()?></td>
                </tr>
                <?php
            }
        }
        if ($ii === 0) {
            echo '<tr><td colspan="11" class="text-center" style="padding:30px 10px; color:#999; font-style:italic;">Nenhum boleto encontrado.</td></tr>';
        }
    }
    public function carregarTabelaBoletosAvulsosAreaInscrito($fws_, $debug = false) {
        $obj = new BoletoAvulso();
        $obj->setCodInscrito($fws_['codInscrito']);
        try {
            $condicao['somenteBoletoExtra'] = '1';
            $condicao['somenteNaoQuitado'] = '1';
            $lista = $obj->getListaBoletosAvulsosByIdInscrito($condicao,$debug);
            $objBoletoAC = new GerenciarBoletoAC();
            if($lista){
                echo "<h4>Opções de pagamento com ajuste:</h4>";
                $OP_OCULTAR_BOLETO_PADRAO = true;
            }
            foreach ($lista as $objL) {
                /*Ocultando boletos extras que foram pagos porque isso já é 
                 * mostrado na área onde mostra o botão padrão do boleto*/
//                if($objL->getFlgPagamento() == ''){
                    $ii++;
                    echo "Cpf/Cnpj: " . formatCpfCnpjView($objL->getCpfCnpj())."<br>";
                    echo "Sacado: " . $objL->getSacado()."<br>";
                    echo "Cód.Boleto: " . $objL->getCodBoleto()."<br>";
                    echo $objL->getDescricao();
                    echo "Valor do Documento: ".$objL->getValorDocumento()." - Data de Vencimento: ".$objL->getDataVencimento()."<br>";
                    if($objL->getSitVencimentoDesconto()!='Vencido'){
                        echo "Valor do Desconto:  ".$objL->getValorDesconto()." - para pagamento até: ".$objL->getDataDesconto()."<br>";
                    }
                    if($objL->getFlgPagamento()){
                        echo "Status Pagamento: " . $objL->getDesFlgPagamento();
                    }else{
                        if($objL->getDesFlgPagamento()=="") {
                            $objBoletoAC->btnBoletoAvulso($objL, $debug);
                        }
                    }
                    echo "<hr>";
//                }
            }
        } catch (Exception $e) {
//            echo "<tr><td></td><td colspan=3>";
//            if ($e->getMessage()) {
//                echo $e->getMessage();
//            } else {
//                echo "Lista vazia.";
//            }
//            echo "</td></tr>";
        }
//        echo "</table>";
    }
    
    public function carregarBoleto($fws_, $debug) {
        try {
            $objBA = new BoletoAvulso();
            $bojBA->setCodBoleto($fws_['cod_boleto']);
            if (!$bojBA->getCodBoleto()) {
                throw new Exception('Código do Boleto inválido ou não informado.');
            }
            $bojBA->getBoletoAvulsoById();
        } catch (Exception $exc) {
            echo $exc->getMessage();
        }

        
    }

    /**
     * Listar todos os boletos do evento (sem filtro por inscrito)
     */
    public function carregarTabelaBoletosEvento($fws_, $debug = false)
    {
        $obj = new BoletoAvulso();
        $lista = [];
        try {
            $obj->getAll($debug);
            $lista = $obj->getListaBoletosAvulsos();
        } catch (Exception $e) {}
        $ii = 0;
        if (!empty($lista)) {
            foreach ($lista as $objL) {
                $ii++;
                $dotClass = 'dot-blue';
                $statusTxt = 'Aberto';
                if ($objL->getFlgPagamento() == 1) {
                    $dotClass = 'dot-green';
                    $statusTxt = 'Pago';
                }
                if ($objL->getStatus() == 'DESATIVADA') {
                    $dotClass = 'dot-orange';
                    $statusTxt = 'Desativado';
                }
                ?>
                <tr>
                    <td style="color:#999; font-size:12px;"><?php echo $objL->getCodBoleto() ?></td>
                    <td class="col-nome">
                        <?php echo $objL->getSacado() ?>
                        <div style="font-weight:normal; color:#999; font-size:11px;"><?php echo formatCpfCnpjView($objL->getCpfCnpj()) ?></div>
                    </td>
                    <td><?php echo $objL->getDescricao() ?></td>
                    <td><?php echo $objL->getDataVencimento() ?></td>
                    <td><?php echo $objL->getValorDocumento() ?></td>
                    <td><span class="badge-tipo"><span class="dot <?php echo $dotClass ?>"></span><?php echo $statusTxt ?></span></td>
                    <td>
                        <a href="index.php?content=view/admin/GerenciarBoletoAvulso.php&codInscrito=<?php echo $objL->getCodInscrito() ?>" class="btn-acao" title="Abrir ficha do inscrito"><i class="fa fa-external-link"></i></a>
                    </td>
                </tr>
                <?php
            }
        }
        if ($ii === 0) {
            echo '<tr><td colspan="7" class="text-center" style="padding:30px 10px; color:#999; font-style:italic;">Nenhum boleto encontrado neste evento.</td></tr>';
        }
    }
}
?>
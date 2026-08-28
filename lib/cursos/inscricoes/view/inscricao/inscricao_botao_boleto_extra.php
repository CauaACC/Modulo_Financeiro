<?php
//if($_SERVER['REMOTE_ADDR']=='189.90.70.56'){
    echo "</font><br><hr>";

    $fws_['codInscrito'] = $objInscrito->getCodInscrito();
    $obj = new BoletoAvulso();
    $obj->setCodInscrito($fws_['codInscrito']);
    try {
        $condicao['somenteBoletoExtra'] = '1';
        $condicao['somenteNaoQuitado'] = '1';
        $lista = $obj->getListaBoletosAvulsosByIdInscrito($condicao,$debug);
        $objBoleto = new GerenciarBoletoAC();
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
                        $objBoleto->btnBoletoAvulso($objL, $debug);
                    }
                }
                echo "<hr>";
//                }
        }
    } catch (Exception $e) {}
//}   
    /*Acispar*/
    /* Removido por não ser mais necessário */
    /*
    $ChapasConsultarEventos = "('00830652965'-'50577697900'-'00744636914'-'48295442953'-'01023221802'-'06188568978'-'02158951931')";
    if(stristr($ChapasConsultarEventos, "'".$objInscrito->getCpf()."'")){
    //if($objInscrito->getCpf()=='2926905903'){
        
        $fws_['data_vencimento'] = "15/07/2018"; #"d/m/Y"
        echo "<br><br>Segue abaixo, boleto avulso no valor R$ 300,00<br>";
        $fws_['valor_cobrado']='300.00';
        $objBoleto = new GerenciarBoletoAC();
        $fws_['nomeBanco'] = "UNIPRIME";
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
    */
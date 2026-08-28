<?php
    // error_reporting(E_ALL);
    // ini_set("display_errors", "On");
/**
 * @todo zaqueu 09-09-2023
 * é necessário gravar a data de vencimento no pedido
 * para não gerar uma nova data de vencimento
 * talvez até gravar os dados criptografados do boleto no pedido
 * afim de verificar se algo mudou no endereço cadastrado ou valor do pedido 
 * e com isso gerar um novo pedido com novo número
 * uma opção seria versionar o pedido e quando alguma alteração ocorrer no pedido, 
 * também alterar a versão do pedido e user o numero do pedido mais a versão do registro
 * para compor o número inscrição no itaushopline, vulgo nosso número.
 */
$mostrarBoletoSisprime=true;
$debug=false;
if($_SERVER['REMOTE_ADDR'] == '189.90.70.56'){
// if($_SERVER['REMOTE_ADDR'] == '127.0.0.1'){
    $mostrarBoletoSisprime=true;
     $debug=false;
}
if(date('YYYYMMDDHHMM')>=202411081800){
    $mostrarBoletoSisprime=true;
}
if (!$mostrarBoletoSisprime){ 
        ?>
        <br>Boleto Bancário não disponível.
        <span style="font-size:17px;color:red;">
        <br>Sistema de pagamento em manutenção.
        <br>Previsão de retorno 08/11/2024 18h00.
        <br>
    </span>        
    <?php
}

if ($mostrarBoletoSisprime){
    echo $debug? __line__.'-' : '';

    $codInscrito = $objInscrito->getCodInscrito();
    $nomeSacado  = $objInscrito->getNomeInscrito();
    $cpfCnpj     = $objInscrito->getCpf(); 

    $enderecoSacado  = $objInscrito->getEndereco();
    if($objInscrito->getNumero()){
        $enderecoSacado  = $enderecoSacado.', '.$objInscrito->getNumero();
    }
    $bairroSacado    = $objInscrito->getBairro();
    $cepSacado       = $objInscrito->getCep();
    $cidadeSacado    = $objInscrito->getCidade();
    $estadoSacado    = $objInscrito->getEstado();

    $titulo_pagamento = $objLI->getDescricao();
    $evento_titulo  = $objEv->getTitulo();
    $valor          = $objLI->getValorTotal();
    // $valor          = formatMoneyItauShopline($valor);
    // $pedido         = trim(str_pad($objLI->getId(), 8, "0", STR_PAD_LEFT));
    // $objLI->testarDatVencimentoItauShopline($fws_);
    $pedido = $objLI->getId();
    $dataVencimento = $objLI->getDatVencimento();
    $dataVencimento = formatDateHoraTrans($dataVencimento,'YYYY-MM-DD','DD/MM/YYYY');
    // dd($objLI);    

    if ($objInscrito->getInstitucional()==1){
        $nomeSacado = $objInscrito->getNomeRecibo();
        $cpfCnpj = limparCpfCnpj($objInscrito->getCpfRecibo());
        $enderecoSacado  = $objInscrito->getEnderecoRecibo();
        $enderecoSacado  = $objInscrito->getNumeroRecibo()? $enderecoSacado.', '.$objInscrito->getNumeroRecibo() : '';
        $bairroSacado    = $objInscrito->getBairroRecibo();
        $cepSacado       = $objInscrito->getCepRecibo();
        $cidadeSacado    = $objInscrito->getCidadeRecibo();
        $estadoSacado    = $objInscrito->getEstadoRecibo();
        $cepSacado = str_replace(array(".", "-"), "",$cepSacado);
        $cepSacado = trim(str_pad($cepSacado,8,"0"));
    }
    #01 para cpf | 02 para cnpj
    if(strlen($cpfCnpj) <= 11){
        $tipoDocumento = '01'; 
    }else{
        $tipoDocumento = '02';
    }    
    $cepSacado = str_replace(array(".", "-"), "",$cepSacado);
    $cepSacado = trim(str_pad($cepSacado,8,"0"));

    // $instrucao1 = 'Para pagamento até dia '.$dataVencimento.', receber R$ '.$valor;
    $instrucao1 = $objLI->getDescricao();
    $instrucao2 = 'Após o vencimento multa de 2% + juros 1% ao mês conforme contrato';
    $instrucao3 = 'Não receber após 30 dias do vencimento';

    $demostrativo1 = $objLI->getDescricao();
    $demostrativo2 = '';
    $demostrativo3 = '';

    $nossoNumero     = '';
    $numeroDocumento = '';

    echo $debug? __line__.'-' : '';

    // $obsAd1 = substr($obsAd1, 0, 80);
    // $obsAd2 = substr($obsAd2, 0, 80);
    // $obsAd3 = substr($obsAd3, 0, 80);
    // echo $debug? __line__.'-' : '';

    $fws_['codInscrito'] = $codInscrito;
    $fws_['pedido'] = $pedido;
    $fws_['sacado'] = $nomeSacado;
    $fws_['cpfCnpj'] = $cpfCnpj;
    $fws_['tipoDocumento'] = $tipoDocumento;
    $fws_['endereco'] = $enderecoSacado;
    $fws_['bairro'] = $bairroSacado;
    $fws_['cep'] = $cepSacado;
    $fws_['cidade'] = $cidadeSacado;
    $fws_['estado'] = $estadoSacado;
    $fws_['dataVencimento'] = $dataVencimento;
    $fws_['valorDocumento'] = $valor;
    $fws_['valorBoleto'] = $valor;

    $fws_['instrucoes1'] = $instrucao1;
    $fws_['instrucoes2'] = $instrucao2;
    $fws_['instrucoes3'] = $instrucao3;
    $fws_['demonstrativo1'] = $demostrativo1;
    // $fws_['demonstrativo2'] = '';
    // $fws_['demonstrativo3'] = '';
    

    // $dataAtual = date("Y-m-d"); // Obtém a data atual no formato YYYY-MM-DD
    // if ($objLI->getDatVencimento() < $dataAtual) {
    //     echo "<br>Ajustando data de vencimento para 15/11/2024.";
        // echo "<br>Juros 2% ao dia para pagamento após Vencimento.";
    //     $fws_['dataVencimento'] = '2024-11-15';
    //     // echo "<br><font color=red>Aguarde até 2h após geração do boleto para efetuar pagamento</font>";
    // }

    echo $debug? __line__.'-' : '';
    // dd($fws_);

    /** gerando número do documento */
    $objSisprime = new GerenciarBoletoSisprimeAC();
    $fws_['numeroDocumento'] = $objSisprime->gerarNumeroDocumento($fws_, $debug);

    /**gerando nosso número */
    /** é o $cod_boleto gerado + digito verificador, no momento gerando durante geração da remessa
    */

    // if($objL->getFlgPagamento()){
    //     echo "Status Pagamento: " . $objL->getDesFlgPagamento();
    // }else{
    //     if($objL->getDesFlgPagamento()=="") {
    //         echo "Não pago";
            // $objBoleto = new GerenciarBoletoAC();            
            // $objBoleto->btnBoletoAvulso($objL, $debug);
    //     }else{
    //         echo "Não pago";
    //     }
    // }
    $objBAv = new GerenciarBoletoAvulsoAC();
    $objBoletoGerado = $objBAv->salvarBoletoSisprime($fws_, $debug);

    if (!$objBoletoGerado || !is_object($objBoletoGerado)) {
        $msgErroSisprime = isset($objBAv->erroSalvarBoletoSisprime) ? $objBAv->erroSalvarBoletoSisprime : '';
        echo '<div class="alert alert-danger" style="margin:10px 0;">';
        echo '<strong>N&atilde;o foi poss&iacute;vel gerar o boleto.</strong>';
        if ($msgErroSisprime !== '') {
            echo '<br><small>Detalhe: ' . htmlspecialchars($msgErroSisprime) . '</small>';
        }
        echo '<br>Tente novamente em alguns instantes ou entre em contato com o suporte.';
        echo '</div>';
    } else {
        $objBoletoAC = new GerenciarBoletoAC();
        $objBoletoAC->btnBoletoAvulso($objBoletoGerado, $debug);
    }
    // echo "em manutenção";
    if($debug){
        echo "<br>Código Pedido: ". $pedido."<br>";
    }
    echo "<hr>";

    ?>
<?php }else{ ?>
    <!-- <br><font color=red>Não foi possível gerar boleto bancário.
    <br> O administrador do sistema já foi informado sobre o problema
    </font><br><br>     -->
<?php } ?> 

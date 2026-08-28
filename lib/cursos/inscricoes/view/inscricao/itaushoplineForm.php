<?php
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
$naoMostrarBoletoItau=TRUE;
if($_SERVER['REMOTE_ADDR'] == '189.90.70.56'){
        $naoMostrarBoletoItau=TRUE;
}
if (!$naoMostrarBoletoItau){ 
        echo "<br>Boleto Bancário não disponível.";
        exit();
}

$debug=false;
$urlRetorno = "";
//if ($_SERVER['HTTP_HOST']=='eventos.test') {
//    $urlShopLine = "https://ww2.itau.com.br/shop/emissao_teste.asp";
//}else{
    $urlShopLine = "https://shopline.itau.com.br/shopline/shopline.aspx";
//}
echo $debug? __line__.'-' : '';
$codEmp = $objEvConf->getItauShoplineCodEmpresa();
$chave = $objEvConf->getItauShoplineChave();
if ($codEmp!='' and $chave){
    $observacao = 3;
    echo $debug? __line__.'-' : '';
    $nomeSacado = $objInscrito->getNomeInscrito();
    $numeroInscricao = $objInscrito->getCpf(); #cpf ou cnpj tamanho 14 posições
    $obsAd1 = trim("Insc:".$objLI->getInscritoId(). " - " .$fws_['desc_atividade']);
    $obsAd2 = removerAcentos(trim($objEv->getTitulo()));
    //$obsAd3 = trim(substr($objLI->getDescricao(), 0, 57));
    $obsAd3 = removerAcentos(trim($objPerfil->getTitulo()));
   
//    $obsAd1 = trim(substr("Insc:".$objLI->getInscritoId(). " - " .$fws_['desc_atividade'], 0, 57));
//    $obsAd2 = removerAcentos(trim(substr($objEv->getTitulo(), 0, 57)));
//    //$obsAd3 = trim(substr($objLI->getDescricao(), 0, 57));
//    $obsAd3 = removerAcentos(trim(substr($objPerfil->getTitulo(), 0, 57)));
//   
    #01 para cpf | 02 para cnpj
    if(strlen($numeroInscricao) <= 11){
        $codigoInscricao = '01'; 
    }else{
        $codigoInscricao = '02';
    }
    echo $debug? __line__.'-' : '';
    $enderecoSacado  = $objInscrito->getEndereco();
    echo $debug? __line__.'-' : '';
    $enderecoSacado  = $objInscrito->getNumero()? $enderecoSacado.', '.$objInscrito->getNumero() : '';
    echo $debug? __line__.'-' : '';
    $bairroSacado    = $objInscrito->getBairro();
    echo $debug? __line__.'-' : '';
    $cepSacado       = $objInscrito->getCep();
    echo $debug? __line__.'-' : '';
    $cidadeSacado    = $objInscrito->getCidade();
    echo $debug? __line__.'-' : '';
    $estadoSacado    = $objInscrito->getEstado();
    echo $debug? __line__.'-' : '';
    $titulo_pagamento = $objLI->getDescricao();
    echo $debug? __line__.'-' : '';
    $evento_titulo  = $objEv->getTitulo();
    echo $debug? __line__.'-' : '';
    $valor          = $objLI->getValorTotal();
    $valor          = formatMoneyItauShopline($valor);
    echo $debug? __line__.'-' : '';
    $pedido         = trim(str_pad($objLI->getId(), 8, "0", STR_PAD_LEFT));
    echo $debug? __line__.'-' : '';
    $dataVencimento = $objLI->getDatVencimento();
    $dataVencimento = formatDateHoraTrans($dataVencimento,'YYYY-MM-DD','DDMMYYYY');
    echo $debug? __line__.'-' : '';
    $cepSacado = str_replace(array(".", "-"), "",$cepSacado);
    echo $debug? __line__.'-' : '';
    $cepSacado = trim(str_pad($cepSacado,8,"0"));
    echo $debug? __line__.'-' : '';
    
    if ($objInscrito->getInstitucional()==1){
        $nomeSacado = $objInscrito->getNomeRecibo();
        $numeroInscricao = limparCpfCnpj($objInscrito->getCpfRecibo()); #cpf ou cnpj tamanho 14 posições
        #01 para cpf | 02 para cnpj
        if(strlen($numeroInscricao) <= 11){
            $codigoInscricao = '01'; 
        }else{
            $codigoInscricao = '02';
        }
        $enderecoSacado  = $objInscrito->getEnderecoRecibo();
        $enderecoSacado  = $objInscrito->getNumeroRecibo()? $enderecoSacado.', '.$objInscrito->getNumeroRecibo() : '';
        $bairroSacado    = $objInscrito->getBairroRecibo();
        $cepSacado       = $objInscrito->getCepRecibo();
        $cidadeSacado    = $objInscrito->getCidadeRecibo();
        $estadoSacado    = $objInscrito->getEstadoRecibo();
        $cepSacado = str_replace(array(".", "-"), "",$cepSacado);
        $cepSacado = trim(str_pad($cepSacado,8,"0"));
    }

    if($debug){
        echo "<br>Código Pedido: ". $pedido."<br>";

        echo "01->".$codEmp
        ."<br>02->".$pedido
        ."<br>03->".$valor
        ."<br>04->".$observacao
        ."<br>05->".$chave
        ."<br>06->".$nomeSacado
        ."<br>07->".$codigoInscricao
        ."<br>08->".$numeroInscricao
        ."<br>09->".$enderecoSacado
        ."<br>10->".$bairroSacado
        ."<br>11->".$cepSacado
        ."<br>12->".$cidadeSacado
        ."<br>13->".$estadoSacado
        ."<br>14->".$dataVencimento
        ."<br>15->".$urlRetorno
        ."<br>16->".substr($obsAd1, 0, 50)
        ."<br>17->".substr($obsAd2, 0, 50)
        ."<br>18->".substr($obsAd3, 0, 50)
        ."<hr>19";
    }
    echo $debug? __line__.'-' : '';
    $obsAd1 = substr($obsAd1, 0, 50);
    $obsAd2 = substr($obsAd2, 0, 50);
    $obsAd3 = substr($obsAd3, 0, 50);
    echo $debug? __line__.'-' : '';
    require_once(WB_ROOT . 'lib/cursos/inscricoes' . '/lib/itaushopline/Itaucripto.php');    
    echo $debug? __line__.'-' : '';
    $cripto = new Itaucripto();
    echo $debug? __line__.'-' : '';
    $dados = $cripto->geraDados($codEmp,$pedido,$valor,$observacao,$chave,$nomeSacado,$codigoInscricao,$numeroInscricao,$enderecoSacado,$bairroSacado,$cepSacado,$cidadeSacado,$estadoSacado,$dataVencimento,$urlRetorno,$obsAd1,$obsAd2,$obsAd3);
    echo $debug? __line__.'-' : '';
    
    echo "<br>Data de Vencimento: ". formatDateHoraTrans($dataVencimento, $formatIn='DDMMYYYY', $formatOut='DD/MM/YYYY');
    ?>
    <FORM action="<?=$urlShopLine?>" method="post" name="form" onsubmit="carregabrw();" target="SHOPLINE">
    <?php
    /*
    <!--
    Entre com os Dados
    <TABLE width=100% border=1 align=center>
    <TR>
    <TD width=25% align=center><B>Pedido (99999999):</B></TD>
            <TD><INPUT type="text" maxlength=8 size=8 name="pedido" value="<?=$pedido?>"></TD>
    </TR>
    <TR>
    <TD width=25% align=center><B>Valor (99999999,99):</B></TD>
            <TD><INPUT type="text" maxlength=10 size=10 name="valor" value="<?=$valor?>"></TD>
    </TR>
    <TR>
            <TD width=25% align=right><B>Observa��o:</B></TD>
            <TD><INPUT type="text" maxlength=40 size=40 name="observacao" value="<?=$observacao?>"></TD>
    </TR>
    <TR>
            <TD width=25% align=right><B>Nome do Sacado:</B></TD>
            <TD><INPUT type="text" maxlength=30 size=30 name="nomeSacado" value="<?=$nomeSacado?>"></TD>
    </TR>
    <TR>
            <TD width=25% align=right><B>C�digo de Inscri��o (01 CPF, 02 CNPJ):</B></TD>
            <TD><INPUT type="text" maxlength=2 size=2 name="codigoInscricao" value="<?=$codigoInscricao?>"></TD>
    </TR>
    <TR>
            <TD width=25% align=right><B>N�mero de Inscri��o (CPF, CNPJ):</B></TD>
            <TD><INPUT type="text" maxlength=14 size=14 name="numeroInscricao" value="<?=$numeroInscricao?>"></TD>
    </TR>
    <TR>
            <TD width=25% align=right><B>Endere�o do Sacado:</B></TD>
            <TD><INPUT type="text" maxlength=40 size=40 name="enderecoSacado" value="<?=$enderecoSacado?>"></TD>
    </TR>
    <TR>
            <TD width=25% align=right><B>Bairro:</B></TD>
            <TD><INPUT type="text" maxlength=15 size=15 name="bairroSacado" value="<?=$bairroSacado?>"></TD>
    </TR>
    <TR>
            <TD width=25% align=right><B>CEP (99999999):</B></TD>
            <TD><INPUT type="text" maxlength=8 size=8 name="cepSacado" value="<?=$cepSacado?>"></TD>
    </TR>
    <TR>
            <TD width=25% align=right><B>Cidade:</B></TD>
            <TD><INPUT type="text" maxlength=15 size=15 name="cidadeSacado" value="<?=$cidadeSacado?>"></TD>
    </TR>
    <TR>
            <TD width=25% align=right><B>Estado:</B></TD>
            <TD><INPUT type="text" maxlength=2 size=2 name="estadoSacado" value="<?=$estadoSacado?>"></TD>
    </TR>
    <TR>
            <TD width=25% align=right><B>Data de Vencimento (ddmmaaaa):</B></TD>
            <TD><INPUT type="text" maxlength=8 size=8 name="dataVencimento" value="<?=$dataVencimento?>"></TD>
    </TR>
    <TR>
            <TD width=25% align=right><B>URL retorna:</B></TD>
            <TD><INPUT type="text" maxlength=60 size=60 name="urlRetorna" value="<?=$urlRetorna?>"></TD>
    </TR>
    <TR>
            <TD width=25% align=right><B>ObsAdicional1:</B></TD>
            <TD><INPUT type="text" maxlength=60 size=60 name="ObsAdicional1" value="<?=$obsAd1?>"></TD>
    </TR>
    <TR>
            <TD width=25% align=right><B>ObsAdicional2:</B></TD>
            <TD><INPUT type="text" maxlength=60 size=60 name="ObsAdicional2" value="<?=$obsAd2?>"></TD>
    </TR>
    <TR>
            <TD width=25% align=right><B>ObsAdicional3:</B></TD>
            <TD><INPUT type="text" maxlength=60 size=60 name="ObsAdicional3" value="<?=$obsAd3?>"></TD>
    </TR>
    </TABLE>
     * 
     */
    ?>
        <INPUT type="hidden" name="DC" value="<?=$dados?>">
        <INPUT type="submit" name="Shopline" value="Gerar Boleto - Itau Shopline">
    </FORM>
    <script CHARSET="iso-8859-1" language="JavaScript">
        function carregabrw()
        {
            newwindow=window.open('','SHOPLINE',"toolbar=yes,menubar=yes,resizable=yes,status=no,scrollbars=yes,width=675,height=485");
            if (window.focus) {newwindow.focus()}
        }
    </script>
<?php }else{ ?>
    <br><font color=red>Não foi possível gerar boleto bancário.
    <br> O administrador do sistema já foi informado sobre o problema
    </font><br><br>    
<?php } ?> 

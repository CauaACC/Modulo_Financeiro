<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/consulta-dados-congresso-seguranca.php");
#require_once(dirname(__FILE__)."/../../view/admin/consulta-dados-congresso-cabecalho.php");
#require_once(dirname(__FILE__)."/../../view/admin/consulta-dados-congresso-busca.php");
?>
<h3>Área Administrativa</h3>  
<div class="panel-group">
<div class="panel panel-default">
    <div class="panel-heading"><h4>Carga de boletos pagos</h4></div>
    <div class="panel-body">

<?php
$pathArquivosCarregados = WB_PATH_LOG_EXTBOLETO;
$nomeArquivo = $_FILES["ed_arquivo"];
$arquivo_temp  = $_FILES["ed_arquivo"]["tmp_name"];
$arquivo       = $_FILES["ed_arquivo"]["name"];
$arquivo_final = $pathArquivosCarregados.$_FILES["ed_arquivo"]["name"];

try {
    if($arquivo ==""){
        throw new Exception("Informe arquivo a ser carregado.");
    }
            
    if(file_exists($arquivo_final)){
        $tempName = explode('.', $_FILES["ed_arquivo"]["name"]);
        for ($ii = 0; file_exists($arquivo_final); $ii++) {
            $arquivo_final = $pathArquivosCarregados.$tempName[0]."_REC_".$ii.".".$tempName[1];
        }
    }
    if(file_exists($arquivo_final)){
        throw new Exception("Arquivo ".$arquivo_final." já foi carregado."); 
    }
    if(!move_uploaded_file($arquivo_temp, $arquivo_final)){	
        throw new Exception("Erro ao tentar gravar arquivo: $arquivo_final."); 
    }
    
    $i=0;
    $arq = fopen ($arquivo_final, "rb");
    $select = "";
    echo "<pre>";
    while(!feof($arq)){
        
        $linhaArquivo =  fgets($arq);
        if ($i < 1) {
            echo $linhaArquivo;
        } else {
            $nossoNumero   = intval(substr($linhaArquivo,1,17));
            $seuNumero     = intval(substr($linhaArquivo,19,11));
            $nomeSacado    = trim(substr($linhaArquivo,31,44));
            $dtVencimento  = trim(substr($linhaArquivo,76,10));
            $dtPagamento   = trim(substr($linhaArquivo,87,10));
            $vlTitulo      = trim(substr($linhaArquivo,98,18));
            $vlLiqRecebido = trim(substr($linhaArquivo,117,18));
            $vlDescAbat    = trim(substr($linhaArquivo,136,18));
            $vlEncargos    = trim(substr($linhaArquivo,155,18));
            $vlDebitoCred  = trim(substr($linhaArquivo,174,18));
            $historico     = trim(substr($linhaArquivo,193,40));
            
            if($historico == 'LIQUIDACAO BLOQUETO - COMPENSACAO'
            or $historico == 'LIQUIDACAO BLOQUETO - AGEN. CAIXA - DIN'
            or $historico == 'LIQUIDACAO BLOQUETOS EM AUTO ATENDIMENTO'
            or $historico == 'LIQ.BLOQ. - CORRESP.BANC.LOT. - DINHEIRO'
            or $historico == 'LIQ. BLOQUETOS EM CORRESP. BANCARIO EST'
            or $historico == 'LIQ. BLOQUETOS VIA INTERNET BANKING'){

                $codEvento = intval(substr($nossoNumero,3,7));
                $codBoleto = intval(substr($nossoNumero,11,7));

                try{
                    $msgExecucao = "Pagamento Não Cadastrado";
                    $objBoleto = new Boleto();
                    $objBoleto->setCodEvento($codEvento);
                    $objBoleto->setCodBoleto($codBoleto);
                    $objBoleto->getBoletoById($debug);
                    
                    if($objBoleto->getFlgPagamento()==1){
                        throw new OKException("Boleto já baixado");
                    }
                    $objBoleto->setDataPagamento(str_replace(".", "/", $dtPagamento));
                    $vlDebitoCred = formatMoneyInsert($vlDebitoCred);
                    if($objBoleto->getValorBoleto()!=$vlDebitoCred){
                        throw new Exception("Valor pago:(".$vlDebitoCred.") diferente do valor cadastrado:(".$objBoleto->getValorBoleto().")");
                    }
                    $objBoleto->setValorPago($vlDebitoCred);
                    $objBoleto->setFlgPagamento('1');
                    $objBoleto->salvarCargaBoleto($debug);
                    
                } catch (OKException $exc){
                    $msgExecucao = "ok: ".$exc->getMessage();
                    if($exc->getMessage()=='Boleto e Pagamento atualizado com sucesso.'){
                        $iii++;
                    }
                    $iv++;
                } catch (Exception $exc) {
                    $msgExecucao = "erro: ".$exc->getMessage();
                }
                
                $ii++;
                echo numberPad($ii,2)."|".numberPad($objBoleto->getCodInscrito(),4)."|".$msgExecucao."|".$nossoNumero."|".$seuNumero."|".$nomeSacado."|".$dtVencimento."|".$dtPagamento."|".$vlTitulo."|".$vlLiqRecebido."|".$vlDescAbat."|".$vlEncargos."|".$vlDebitoCred."|".$historico."\r";
            }else{
                if($vlDebitoCred!='5,90' and $vlDebitoCred!='0,00'){
                    $linhasNaoProcessadas .= $linhaArquivo;
                }
            }
        }
        $i++;
    }
    echo "</pre>";
    echo "<br>Total de linhas processadas:".$ii;
    echo "<br>Total de titulos baixados neste processamento:".$iii;
    echo "<br>Total de titulos baixados no geral:".$iv;
    
    echo "<hr>Linhas não conhecidas e não processadas<br><br><pre>".$linhasNaoProcessadas."</pre>";
    fclose($arq);

//    pg_query('BEGIN TRANSACTION');
//    if (pg_query($select)) {
//        pg_query('COMMIT TRANSACTION');
//        $certo = "Boletos do arquivo ".$arquivo." cadastrados com sucesso.";
//    } else {
//        pg_query('ROLLBACK TRANSACTION');
//        throw new Exception("Erro ao cadastrar boletos pagos no sistema.");
//    }

} catch (Exception $exc) {
    $erro = Date("d/m/Y H:i:s")." - ".$exc->getMessage();
}

?>

<table>
<?php if($erro or $certo){?>
    <tr><td><div class="erro"><br><?php echo $erro; ?></div><strong><br><?php echo $certo; ?></strong></td></tr>
<?php }?>
<tr>
    <td>
    <fieldset><legend>Arquivo Retorno</legend>
    <table>
    <form action="" name="f_arquivo" method="post" enctype="multipart/form-data">
    <tr>
            <td><input type="file" name="ed_arquivo"></td>
            <td align="center"><input type="submit" name="btn_envia" value="Enviar"></td>
    </tr>
    </form>
    </table>
    </fieldset>
    </td>
</tr>
</table>
<br>
<table>
    <tr><td><?php echo $nomeArquivo["name"]?></td></tr>
    <?php
    if($listaValor){
    foreach($listaValor as $numero => $valor){
        $valor = str_replace(".", ",", $valor)?>
        <tr>
            <td>Número do Boleto: <?php echo $numero?>&nbsp;&nbsp;&nbsp;&nbsp;</td>
            <td>Valor do Boleto: R$<?php echo $valor?></td>
        </tr>
    <?php
    }}
    ?>
</table>
<br>
<table>
    <tr><td><b>Lista de arquivos já carregados</b></td></tr>
    <?php
    $diretorio = dir($pathArquivosCarregados);
    if($diretorio){
    while($arquivo = $diretorio -> read()){
       if($arquivo!="." and $arquivo!=".."){
       ?>
        <tr>
            <td colspan="2">
                <?php echo $arquivo?>
            </td>
        </tr>
        <?php
       }
    }
    }else{
        echo "Nenhum arquivo encontrado.";
    }
    $diretorio -> close();
    ?>
</table>
</div>
</div>
</div>
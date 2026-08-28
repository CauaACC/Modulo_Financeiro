<?php

class GerenciarCargaBoletoAC
{
    public function __construct()
    {
        // Nao gatear quando chamado pela rota /api (a rota /cargaboletos/sisprime executa via router).
        if (!defined('FWA_API_EXEC')) { FWA_exigirPermissao([enumSistema::SIS_GERENCIAR_ADM_GERAL, enumSistema::SIS_CONTROLE_FINANCEIRO]); }
        // Inicialização da classe
    }

    public function carregarBoletos($fws_, $debug = false)
    {
        if (!file_exists(WB_PATH_LOG_RETBOLETO)) {
            mkdir(WB_PATH_LOG_RETBOLETO, 0755);
        }
        $pathArquivosCarregados = WB_PATH_LOG_RETBOLETO;
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
            echo 'Carregando arquivo:'.$_FILES["ed_arquivo"]["name"].'<br>';
            while(!feof($arq)){
                
                $linhaArquivo =  fgets($arq);
                if ($i < 1) {
                    echo $linhaArquivo;
                } else {
                    $idUltima= trim(substr($linhaArquivo,0,7));
                    $idOcorrencia= trim(substr($linhaArquivo,108,2));
                    $dtOcorrenciaBanco = substr($linhaArquivo,110,6);
                    $numeroDocumento= substr($linhaArquivo,116,10);
                    $pedido= (int)substr($numeroDocumento,0,strlen($numeroDocumento)-3);
                    $identificacaoTituloNoBanco= substr($linhaArquivo,126,20);
                    $nossoNumero= (int)substr($linhaArquivo,126,19);
                    $codBoleto= $nossoNumero;
                    $dtVencimento= substr($linhaArquivo,146,6);
                    $vlTitulo   = trim(substr($linhaArquivo,152,13));
                    $vlAbatimento = trim(substr($linhaArquivo,227,13));
                    $vlDesconto   = trim(substr($linhaArquivo,240,13));
                    $vlRecebido = trim(substr($linhaArquivo,253,13));
                    $vlJuros = trim(substr($linhaArquivo,266,13));
                    $dtPagamento= trim(substr($linhaArquivo,295,6));
                    $motivo= trim(substr($linhaArquivo,295,6));
                    // echo '<br>-'.$idUltima
                    // .'<br>Ocorrencia:'.$idOcorrencia
                    // .'<br>dtOcorrenciaBanco:'.$dtOcorrenciaBanco
                    // .'<br>numeroDocumento:'.$numeroDocumento
                    // .'<br>pedido:'.$pedido
                    // .'<br>identificacaoTituloNoBanco:'.$identificacaoTituloNoBanco
                    // .'<br>NossoNúmero:'.$nossoNumero
                    // .'<br>codBoleto:'.$codBoleto
                    // .'<br>dtVencimento:'.$dtVencimento
                    // .'<br>ValorTitulo:'.$vlTitulo
                    // .'<br>vlAbatimento:'.$vlAbatimento
                    // .'<br>vlDesconto:'.$vlDesconto
                    // .'<br>ValorRecebido:'.$vlRecebido
                    // .'<br>vlJuros:'.$vlJuros
                    // .'<br>DtPagamento:'.$dtPagamento
                    // .'<br>motivo:'.$motivo
                    // .'<br>';
                    
                    if($idOcorrencia == '06'
                    or $idOcorrencia == '02'
                    or $idOcorrencia == '09'
                    or $idOcorrencia == '17'){
        
                        $codBoleto = $nossoNumero;
        
                        try{
                            $msgExecucao = "Pagamento Não Cadastrado";
                            $objBoleto = new Boleto();
                            $objBoleto->setCodBoleto($codBoleto);
                            $objBoleto->getBoletoById($debug);
                            $objBoleto->setIdOcorrencia($idOcorrencia);
                            
                            if($objBoleto->getFlgPagamento()==1){
                                throw new OKException("Boleto já baixado");
                            }
                            if($idOcorrencia == '02'){
                                $objBoleto->setFlgRegistrado('1');
                            }else{
                                $pattern = "/([0-9]{2})([0-9]{2})([0-9]{2})/";
                                preg_match($pattern, $dtPagamento, $matchesarray);
                                $dtPagamento = $matchesarray[1] . "/" . $matchesarray[2] . "/20" . $matchesarray[3];
                                $objBoleto->setDataPagamento(str_replace(".", "/", $dtPagamento));
                                $vlRecebido = sprintf('%.2f', $vlRecebido / 100);
                                // $vlRecebido = formatMoneyInsert(ltrim($vlRecebido));
        //                        if($objBoleto->getValorBoleto()!=formatMoneyBoleto($vlRecebido)){
        //                            throw new Exception("Valor pago:(".$vlRecebido.") diferente do valor cadastrado:(".$objBoleto->getValorBoleto().")");
        //                        }
                                $objBoleto->setValorPago($vlRecebido);
                                $objBoleto->setFlgPagamento('1');
                                $objBoleto->setPedidoId($pedido);
                            }
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
                        echo numberPad($ii,2)
                        ."|ocorrencia:".$idOcorrencia
                        ."|nossoNumero:".$nossoNumero
                        ."|".$seuNumero
                        ."|".$nomeSacado
                        ."|".$dtVencimento
                        ."|".$dtPagamento
                        ."|".$vlTitulo
                        ."|".$vlLiqRecebido
                        ."|".$vlDescAbat
                        ."|".$vlEncargos
                        ."|".$vlRecebido
                        ."|".$msgExecucao."\r";
                    }else{
                        //if($vlRecebido!='5,90' and $vlRecebido!='0,00'){
                        // if($idOcorrencia == '03'){
                        // }
                        if($idUltima!='9201237')
                            $linhasNaoProcessadas .= $linhaArquivo;
                        //}
                        
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
            
            if($linhasNaoProcessadas){
                executarLogReg($linhasNaoProcessadas,'retorno/nao-processado-'.$tempName[0]."_REC_".$ii.".".$tempName[1],true);
            }
        
        } catch (Exception $exc) {
            $erro = Date("d/m/Y H:i:s")." - ".$exc->getMessage();
        }
        
    }

    public function processarBoleto($boletoId)
    {
        // Lógica para processar um boleto específico
    }

    public function listarArquivosCarregados($fws_,$debug=false)
    {
        $pathArquivosCarregados = WB_PATH_LOG_RETBOLETO;
        $diretorio = dir($pathArquivosCarregados);
        if($diretorio){
            
            while($arquivo = $diretorio -> read()){
               if($arquivo!="." and $arquivo!=".."){
                   $listaArquivo[date('Y/m/d H:i:s', filemtime($pathArquivosCarregados.$arquivo))] = $arquivo;
               }
            }
            krsort($listaArquivo,SORT_STRING);
            foreach ($listaArquivo as $arquivo) {
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
    }

    public function atualizarStatusBoleto($boletoId, $status)
    {
        // Lógica para atualizar o status de um boleto
    }
}
<?php
// error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE & ~E_WARNING);
// ini_set("display_errors", "On");
include(dirname(__FILE__) .'/../include/functions/LeitorArquivo.php');

class GerenciarPagamentoUploadAC {

    function __construct() {
        FWA_exigirPermissao([enumSistema::SIS_GERENCIAR_ADM_GERAL, enumSistema::SIS_CONTROLE_FINANCEIRO]);
        $this->tiposPermitidos = array('csv', 'txt', 'xls', 'ret');
        $this->tamanho = '30MB';
        $this->varDiretorio = WB_PATH_LOG_CARGA.'pagamentos/';
        $this->prefixo = 'pagseguro';
        // $this->arrayTipoArquivo = array(['PAGSEGURO','PAY-PAL','ITAUSHOPLINE','BOLETOSCAIXA','BOLETOUNIPRIME','BOLETOSISPRIME']);        
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
            $obj->setTipoArquivo('PAGSEGURO');
            switch (@$fws_['gatewayPagamento']) {
                case 'PAGSEGURO':
                    $obj->setTipoArquivo('PAGSEGURO');
                    break;
                default:
                    if($fws_['gatewayPagamento']==''){
                        $objEvConf = EventoConfiguracao::getInstancePopulado(COD_EVENTO);
                        if($objEvConf->getGatewayPagamento()!=''){
                            $fws_['gatewayPagamento'] = $objEvConf->getGatewayPagamento();
                        }
                    }
                    $obj->setTipoArquivo($fws_['gatewayPagamento']);
                    break;
            }
            $obj->carregarListaArquivoByIdEvento($debug);
            $ii = 0;
            foreach ($obj->getListaArquivo() as $objL) {
                $ii++;
                ?>  
                <tr id="<?php echo  $objL->getCodArquivo() ?>">
                    <td>
                        <?php //if ($objEv->controlarAcessoProposta('boolean')) { ?>
                        <!--<img class="editar"  src="<?php echo  WB_PATH ?>/resources/image/alterar.gif" alt="Editar Arquivo" border="0">-->
                        <img class="excluir" src="<?php echo  WB_PATH ?>/resources/image/fig-excluir.gif" alt="Excluir Arquivo" border="0">
                        
                        <?php //} ?>
                    </td>
                   
                    <td>
                        <?php
                        $classCarga = false;
                        $classAssoc = false;
                        if ($objL->getStatusCarga() =='ASSOCIADO') {
                            echo $objL->getStatusCarga();
                            $classCarga = false;
                            $classAssoc = false;
                        }else{
                            if ($objL->getStatusCarga() =='CARREGADO') {
                                echo $objL->getStatusCarga();
                                $classCarga = false;
                                $classAssoc = true;
                            }else{
                                echo 'AGUARDANDO';
                                $classCarga = true;
                                $classAssoc = false;
                            }
                        }
                        echo $classCarga? '<input class="button_text botaoCarga"  type="button" id="carregarInscrito" name="carregarInscrito" value="Carregar" />' : '';
                        echo '<br>';
                        echo $classAssoc? '<input class="button_text botaoAssociar" type="button" id="associarPagamento" name="associarPagamento" value="Associar" />' : '';
                        
                        ?>
                    </td>
                    <td><?php echo  $objL->getTipoArquivo()?></td>
                    <td><?php echo  $objL->getArquivoGerado() ?></td>
                    <td>
                        <?php echo  $objL->getArquivoUpload() ?>
                        <br><a href="#" class="mostrarLog">Mostrar Log</a>
                        <div class="messageLog" style="display:none;">
                            <?php
                            if($objL->getMessageLog()){
                                echo $objL->getMessageLog();
                            }else{ echo "vazio"; }
                            ?>
                        </div>
                    </td>
                    <td><?php echo  formatDateView($objL->getDataInclusao()) ?></td>
                </tr>
                <?php
            }
            ?>
                <br>Total de cadastrados: <?php echo  $ii ?>.
            <?php
        } catch (Exception $e) {
            echo "<tr><td></td><td colspan=3>";
            if ($e->getMessage()) {
                echo $e->getMessage();
            } else {
                echo "Lista vazia.";
            }
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
        if($fws_['tipoArquivo']!='PAGSEGURO'){
            $this->prefixo = strtolower($fws_['tipoArquivo']);
        }
        $this->varArquivo = $this->prefixo.'-'.numberPad($obj->getCodEvento(), 4).'-'.numberPad($obj->getLastId(), 4).'.'.$extensao;
        $obj->setTipoArquivo($fws_['tipoArquivo']);
        $obj->setCodPerfil($fws_['codPerfil']);
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
    
    public function carregarInscritoPagamento($fws_, $debug = false) {
        try {
            $objAr = $this->carregarArquivo($fws_,$debug);
            $objEv = $objAr->getEvento();
            if($objAr->getStatusCarga() != ''){
                throw new Exception("O arquivo já foi processado.");
            }

            $objConf = $objAr->getEventoConfiguracao();

            $file = new LeitorArquivo();
            $file->setNmArquivo($objAr->getArquivoGerado()); 
            $file->carregarArquivo($this->varDiretorio . $file->getNmArquivo(),$debug);

            $listaLinhas = $file->getListaLinhas();
            $totalLinhas = $file->getTotalLinhas();

    //        $file->imprimirTituloColunas();;
    //        $this->DAO();
    //        $this->conex->BeginTrans();
    //        $this->conex->CommitTrans();
    //        $this->conex->RollbackTrans();

            //Começar com contador inverso igual a 1 para não gravar a primeira linha 
            //onde deve constar o titulo das colunas
            //$contadorInverso = 1;
            $contadorInverso = 0;
            $totalRegistros = $totalLinhas;

            //Loop em cima das linhas retornadas do arquivo        
            $arrayColunas = [];
            while($totalLinhas > 0){

                try{

                    $linha = $listaLinhas[$contadorInverso];
                    unset($arrayColunas);  // Atribuir um array vazio para limpar o array original
                    if($objAr->getTipoArquivo()=='PAY-PAL'){
                        $linha = str_replace(['","', '"'], [';', ''], $linha);
                    }
                    $arrayColunas = explode(';', $linha);
                    $arrayColunas = $this->identificarArrayColunas($arrayColunas, $objConf, $objAr, $objEv);
                    // dd($arrayColunas);

                    $transacaoId  = $arrayColunas['transacaoId'];

                    switch ($objAr->getTipoArquivo()) {
                        case 'PAGSEGURO':
                            $transacaoId = $arrayColunas['transacaoId'];
                            $this->pagSeguroCarregarInscrito($arrayColunas, $objConf, $objAr, $objEv);
                        break;
                        case 'PAY-PAL':
                            $transacaoId = $arrayColunas[12];
                            GerenciarPagamentoUploadPayPalAC::carregarInscrito($arrayColunas, $objConf, $objAr, $objEv);
                        break;
                        default:
                            throw new Exception("Tipo de arquivo (".$objAr->getTipoArquivo().") não reconhecido");                        
                        break;
                    }
                }catch (OKException $e){
                    $message .= "<br>linha: ".$contadorInverso.":".$e->getMessage().": Transacao Id: ".$transacaoId." ";
                }catch (Exception $e){
                    if($e->getMessage()!=''){
                        $message .= "<br>linha: ".$contadorInverso.":".$e->getMessage().": Transacao Id: ".$transacaoId." ";
                    }
                }
                // $objInscApto->limparObj(); Verificar se precisa

                $totalLinhas--;
                $contadorInverso++;
            }
            $message = '';
            // Atualiza o status do arquivo carregado, indicando que a carga já foi feita.
            if ($contadorInverso == $totalRegistros){
                try {
                    $objAr->getByIdExiste();
                    $statusArquivo = 'CARREGADO';
                    $objAr->setStatusCarga($statusArquivo);   
                    $objAr->atualizar();
                } catch (OKException $e) {
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
        } catch (Exception $e) {echo 
            $statusArquivo = $e->getMessage();
            $ok = false;
        }
        echo '{
        "statusMessage":"' . $ok . '",
        "message":      "' .str_replace("\n","",$message .'<br>'. $statusArquivo) . '",
        "codArquivo":"' . $objAr->getCodArquivo() . '"            
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

    //        $file->imprimirTituloColunas();;
    //        $this->DAO();
    //        $this->conex->BeginTrans();
    //        $this->conex->CommitTrans();
    //        $this->conex->RollbackTrans();

            //Começar com contador inverso igual a 1 para não gravar a primeira linha 
            //onde deve constar o titulo das colunas
            //$contadorInverso = 1;
            $contadorInverso = 0;
            $totalRegistros = $totalLinhas;

            //Loop em cima das linhas retornadas do arquivo        
            $arrayColunas = [];
            while($totalLinhas > 0){
                try{

                    $linha = $listaLinhas[$contadorInverso];
                    unset($arrayColunas);  // Atribuir um array vazio para limpar o array original
                    if($objAr->getTipoArquivo()=='PAY-PAL'){
                        $linha = str_replace(['","', '"'], [';', ''], $linha);
                    }                
                    $arrayColunas = explode(';', $linha);
                    $arrayColunas = $this->identificarArrayColunas($arrayColunas, $objConf, $objAr, $objEv);

                    switch ($objAr->getTipoArquivo()) {
                        case 'PAGSEGURO':
                            $this->pagSeguroAssociarPagamento($arrayColunas, $objConf, $objAr);
                        break;
                        case 'PAY-PAL':
                            /**@todo essa valiavel $linha vem com aspas e separada por virgula, é preciso depois e transformar retirar aspas do primeiro e do ultimo elemento do array */
                            // $arrayColunas = explode('","', $linha);
                            GerenciarPagamentoUploadPayPalAC::associarPagamento($arrayColunas, $objConf, $objAr);
                        break;
                        default:
                            throw new Exception("Tipo de arquivo (".$objAr->getTipoArquivo().") não reconhecido");                        
                        break;
                    }
                }catch (OKException $e){
                    $message .= "<br>linha: ".$contadorInverso.":<font color=green>".$e->getMessage()."</font>: Transacao Id: ".$arrayColunas['transacaoId']." - valor: ".$arrayColunas['valorBruto'];
                }catch (Exception $e){
    //                echo __line__.'<br>';
                    if($e->getMessage()!=''){
                        $message .= "<br>linha: ".$contadorInverso.":".$e->getMessage().": Transacao Id: ".$arrayColunas['transacaoId']." ";
                    }
                }
                // $objInscApto->limparObj(); Verificar se precisa

                $totalLinhas--;
                $contadorInverso++;
            }
            $messageLog = $message;
            $message = '';
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

    public function identificarArrayColunas($arrayColunas, $objConf, $objAr, $objEv){
        if($objAr->getTipoArquivo()=='PAGSEGURO'){
            // dd($arrayColunas);
            // if (new DateTime() >= new DateTime('2025-10-7')) {
            //     $arrayColunas['transacaoId']=$arrayColunas[3];  #transação id
            //     $arrayColunas['serialMaquina']=$arrayColunas[16]; #serial maquina
            //     $arrayColunas['status']=$arrayColunas[12]; #status
            //     $arrayColunas['formaPagamento']=$arrayColunas[7];  #forma pagamento
            //     $arrayColunas['valorBruto']=$arrayColunas[9];  #valor bruto  
            //     $arrayColunas['dataPagamento']=formatDateInsert($arrayColunas[4]); #data_pagamento
            //     if($arrayColunas[19]!=''){
            //         $arrayColunas['nomeInscrito']=$arrayColunas[19]; #NomeInscrito
            //     }else{
            //         $arrayColunas['nomeInscrito']=$arrayColunas['transacaoId']; #
            //     }
            //     $arrayColunas['email']=$arrayColunas[20]; #email
            // }else
            if (new DateTime() >= new DateTime('2026-03-16')) {
                // CSV gerado pelo script pagSeguroBaixarTransacoesByAPI.py
                // Header: Nome Cliente[0];E-mail Cliente[1];Código da Transação[2];Data da Transação[3];
                //         Data prevista de liberação[4];Bandeira[5];Forma de Pagamento[6];Parcela[7];
                //         Valor Bruto[8];Valor Taxa[9];Valor Líquido[10];Status[11];Número do Cartão[12];
                //         Código NSU[13];Código de Autorização[14];Identificação da Maquininha[15];
                //         Código da Venda[16];Código Referência[17];Nome Comprador[18];E-mail Comprador[19];
                //         Código TX ID (PIX)[20]
                $arrayColunas['transacaoId']=$arrayColunas[2];  #Código da Transação
                $arrayColunas['serialMaquina']=$arrayColunas[15]; #Identificação da Maquininha
                $arrayColunas['status']=$arrayColunas[11]; #Status
                $arrayColunas['formaPagamento']=$arrayColunas[6];  #Forma de Pagamento
                $arrayColunas['valorBruto']=$arrayColunas[8];  #Valor Bruto
                $arrayColunas['dataPagamento']=formatDateInsert($arrayColunas[3]); #Data da Transação
                if($arrayColunas[18]!=''){
                    $arrayColunas['nomeInscrito']=$arrayColunas[18]; #Nome Comprador
                }else{
                    $arrayColunas['nomeInscrito']=$arrayColunas['transacaoId'];
                }
                $arrayColunas['email']=$arrayColunas[19]; #E-mail Comprador
            }else
            if (new DateTime() >= new DateTime('2026-01-27')) {
                $arrayColunas['transacaoId']=$arrayColunas[4];  #transação id
                $arrayColunas['serialMaquina']=$arrayColunas[18]; #serial maquina
                $arrayColunas['status']=$arrayColunas[14]; #status
                $arrayColunas['formaPagamento']=$arrayColunas[8];  #forma pagamento
                $arrayColunas['valorBruto']=$arrayColunas[10];  #valor bruto
                $arrayColunas['dataPagamento']=formatDateInsert($arrayColunas[5]); #data_pagamento
                if($arrayColunas[21]!=''){
                    $arrayColunas['nomeInscrito']=$arrayColunas[21]; #NomeInscrito
                }else{
                    $arrayColunas['nomeInscrito']=$arrayColunas['transacaoId']; #
                }
                $arrayColunas['email']=$arrayColunas[22]; #email
            }else
            if (new DateTime() >= new DateTime('2025-10-21')) {
                $arrayColunas['transacaoId']=$arrayColunas[4];  #transação id
                $arrayColunas['serialMaquina']=$arrayColunas[17]; #serial maquina
                $arrayColunas['status']=$arrayColunas[13]; #status
                $arrayColunas['formaPagamento']=$arrayColunas[8];  #forma pagamento
                $arrayColunas['valorBruto']=$arrayColunas[10];  #valor bruto  
                $arrayColunas['dataPagamento']=formatDateInsert($arrayColunas[5]); #data_pagamento
                if($arrayColunas[20]!=''){
                    $arrayColunas['nomeInscrito']=$arrayColunas[20]; #NomeInscrito
                }else{
                    $arrayColunas['nomeInscrito']=$arrayColunas['transacaoId']; #
                }
                $arrayColunas['email']=$arrayColunas[21]; #email
            }else
            if (new DateTime() >= new DateTime('2025-05-05')) {
                $arrayColunas['transacaoId']=$arrayColunas[2];  #transação id
                $arrayColunas['serialMaquina']=$arrayColunas[15]; #serial maquina
                $arrayColunas['status']=$arrayColunas[11]; #status
                $arrayColunas['formaPagamento']=$arrayColunas[6];  #forma pagamento
                $arrayColunas['valorBruto']=$arrayColunas[8];  #valor bruto  
                $arrayColunas['dataPagamento']=formatDateInsert($arrayColunas[3]); #data_pagamento
                if($arrayColunas[18]!=''){
                    $arrayColunas['nomeInscrito']=$arrayColunas[18]; #NomeInscrito
                }else{
                    $arrayColunas['nomeInscrito']=$arrayColunas['transacaoId']; #
                }
                $arrayColunas['email']=$arrayColunas[19]; #email
            }else
            if (new DateTime() >= new DateTime('2024-12-20')) {
                $arrayColunas['transacaoId']=$arrayColunas[1];
                $arrayColunas['serialMaquina']=$arrayColunas[6];
                $arrayColunas['status']=$arrayColunas[10];
                $arrayColunas['formaPagamento']=$arrayColunas[3];
                $arrayColunas['valorBruto']=$arrayColunas[7];
                $arrayColunas['dataPagamento']=formatDateInsert($arrayColunas[2]);
                $arrayColunas['nomeInscrito']=$arrayColunas[15]; 
                $arrayColunas['email']=$arrayColunas[16];
            }

            $moneyValue = str_replace(".", "", $arrayColunas['valorBruto']); // Remove pontos
            $moneyValue = str_replace(",", ".", $moneyValue);    // Substitui vírgula por ponto
            $moneyValue = (float)$moneyValue;                   // Converte para número
            $moneyValue = number_format($moneyValue, 2, '.', ''); // Formata com 2 casas decimais
            $arrayColunas['valorBruto'] = $moneyValue;
            

            /** Se arquivo antigo antes de dezembro de 2024, usar essa forma de identificar colunar */
            //                $objB = new BoletoPagSeguro();
            //                #542-214-112 - Serial Hospital
            //                #542-213-961 - Serial ORQUIDEAS
            //                /*Se diferente, não pertence*/
            //                echo $contadorInverso.'->'.$arrayColunas['serialMaquina'].' != '.$objConf->getPagSeguroSerialMaquina().'<br>';
            // if($arrayColunas['serialMaquina'] != $objConf->getPagSeguroSerialMaquina()){
            // Recupera o(s) serial(is) configurado(s), em um array
            $seriaisConfig = array_map('trim', explode(',', $objConf->getPagSeguroSerialMaquina()));
            $serialRequisicao = $arrayColunas['serialMaquina'];
            if (!in_array($serialRequisicao, $seriaisConfig, true)) {
                throw new Exception("Não pertente a lista de maquinas deste projeto: (".$arrayColunas['serialMaquina'].")-(".$objConf->getPagSeguroSerialMaquina().")");
            }
            if($arrayColunas['transacaoId']==''){
                throw new Exception("Transação id não informada");
            }
            if($arrayColunas['status']!='Aprovada'){
                throw new Exception("Pagamento não aprovado ou cancelado");
            }                
        //                $objB->setCodEvento($objAr->getCodEvento());
        //                $objB->setCodArquivo($objAr->getCodArquivo());
        //                $objB->setTransacaoId($arrayColunas['transacaoId']);
        //                $objB->getBoletoPagSeguroByTransacaoId();

        //                if(!$objB->getBoletoPagSeguroByTransacaoId()){
        //                    throw new Exception("Transacao Id:".$objB->getTransacaoId()." não encontrado");
        //                }     
        }
        // dd($arrayColunas);
        return $arrayColunas;
    }

    public function pagSeguroCarregarInscrito($arrayColunas, $objConf, $objAr, $objEv){
        /*Definindo codigo da forma de pagamento a ser associado*/
        if($objAr->getTipoArquivo()=='PAGSEGURO'){
            /*criar inscrito para a pagamento*/
            $objI = new Inscrito();
            $objI->setCodEvento($objAr->getCodEvento()); 
            $objI->setTransacaoId($arrayColunas['transacaoId']);
            
            if($objI->verificarTransacaoIdEstaInscrito()){
                throw new Exception("Transação ID já cadastrado");
            }
            
            $objI->setCodEvento($objAr->getCodEvento()); 
            $objI->setCodArquivo($objAr->getCodArquivo());
            $objI->setCodInscrito(NULL);
            $objI->setCodPerfil($objAr->getCodPerfil());
            $objI->setCpf($arrayColunas['transacaoId']);
            $objI->setNomeInscrito($arrayColunas['nomeInscrito']);
            $objI->setEmail($arrayColunas['email']);
            $objI->setOrigemCadastro('CARGA_PAGAMENTO');

            if($objEv->getGestor()=='HUTEC'){
                $objI->setNomeInscrito('Transação ID: '.$arrayColunas['transacaoId']);
    //                            if ($arrayColunas['email']==''){
    //                                $objI->setEmail('contato@hutec.org.br');
    //                            }
                $objI->setEmail('contato@hutec.org.br');
                $objI->setEMail('contato@hutec.org.br');
                $objI->setCpf('11111111111');
                $objI->setFoneContatoDdd('43');
                $objI->setFoneContato('9999-9999');
            }
            $objI->setStatus('ATIVADA');
            $objI->salvarCarga();
        }
    }
    
    public function pagSeguroAssociarPagamento($arrayColunas, $objConf, $objAr){

        /*Definindo codigo da forma de pagamento a ser associado*/
        if($objAr->getTipoArquivo()=='PAGSEGURO'){
            /*criar inscrito para a pagamento*/
            $objI = new Inscrito();

            #542-214-112 - Serial Hospital
            #542-213-961 - Serial ORQUIDEAS
            /*Se diferente, não pertence*/

            $objI->setCodEvento($objAr->getCodEvento()); 
            $objI->setCodArquivo($objAr->getCodArquivo());
            $objI->setTransacaoId($arrayColunas['transacaoId']);
            if($objI->verificarTransacaoIdEstaInscrito()){
                $objP = new Pagamento();
                $objP->setCodEvento($objI->getCodEvento());
                $objP->setTransacaoId($arrayColunas['transacaoId']);

                $objP->setCodArquivo($objI->getCodArquivo());
                $objP->setCodInscrito($objI->getCodInscrito());
                
                $moneyValue = $arrayColunas['valorBruto'];

                if($objP->getPagamentoByTransacaoId()){
                    // if($objP->getValor()!=$moneyValue or $objP->getDataPagamento()!=formatDateInsert($arrayColunas['dataPagamento'])){
                    //     // throw new Exception("<font color=red>Transação ID Necessário atualização</font>");
                    //     $objP->setAtualizarValor('SIM');
                    //     // echo formatDateInsert($arrayColunas['dataPagamento']);
                    //     // exit();
                    //     $objP->setDataPagamento(formatDateInsert($arrayColunas['dataPagamento']));
                    //     $objP->setFormaPagamento($arrayColunas['formaPagamento']);
                    //     $objP->setFormaPagamento($objP->getDescFormaPagamento());;
                    //     $objP->setValor($moneyValue);
                    // }else{
                        throw new Exception("Transação ID já cadastrado");
                    // }
                }else{
                    /**salvando normalmente */
                    $objP->setValor($moneyValue);
                    $objP->setDataPagamento($arrayColunas['dataPagamento']);
                    $objP->setFormaPagamento($arrayColunas['formaPagamento']);
                    $objP->setFormaPagamento($objP->getDescFormaPagamento());
                    $objP->setStatus('ATIVADA');
                }

                $objP->salvarCargaPagamento();
            }else{
                throw new Exception("Transação ID não cadastrado");
            }
        }
    }
}

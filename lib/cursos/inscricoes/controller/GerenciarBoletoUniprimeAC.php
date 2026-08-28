<?php

class GerenciarBoletoUniprimeAC {

    public function calculandoDescontoExtra($fws_, $debug=false){

        $porcemDescPorTipo = CarregarOptionsAC::getDescontoTipoInscricao($fws_, $debug);
        $objDesconto = CarregarOptionsAC::carregarObjDesconto($fws_,$debug);
        
        echo $debug? __LINE__.'-' : "";
        if($debug){
            echo 'valor_cobrado:'.$fws_['valor_cobrado'].' | '.$porcemDescPorTipo.' < '.$porcemDescPorDoacao.'<'.$objDesconto->getPorcentagemTotalDesconto()."<br>";
        }
        echo $debug? __LINE__.'-' : "";
        if ($objDesconto->getPorcentagemTotalDesconto()!=''){
            echo $debug? __LINE__.'-' : "";
            $porcemDescPorDoacao = $objDesconto->getPorcentagemTotalDesconto()/100;
        }else{
            $porcemDescPorDoacao = 0;
        }
        if ($porcemDescPorDoacao==1){
            echo $debug? __LINE__.'-' : "";
            $porcemDesconto = $porcemDescPorDoacao;
        }else if ($porcemDescPorTipo < $porcemDescPorDoacao){
            echo $debug? __LINE__.'-' : "";
            $porcemDesconto = $porcemDescPorDoacao;
        }else{
            echo $debug? __LINE__.'-' : "";
            $porcemDesconto = $porcemDescPorTipo;
        }
            
        if ($porcemDesconto!='' and $porcemDesconto!=0){
            echo $debug? __LINE__.'-' : "";
            $fws_['valor_cobrado_desconto'] = $porcemDesconto*$fws_['valor_cobrado'];
            
            if($porcemDesconto == 1){
                echo $debug? __LINE__.'-' : "";
                $fws_['valor_cobrado'] = 0;
//                echo "<span style='font-size:18px;color:red'>Aplicando Desconto:"
//                . "<br> Inscrição isenta</span>";

            }else{
                echo $debug? __LINE__.'-' : "";
                $fws_['valor_cobrado'] = $fws_['valor_cobrado'] - $fws_['valor_cobrado_desconto'];
//                echo "<span style='font-size:18px;color:red'>Aplicando Desconto:"
//                . "<br> Para pagamento até dia 10/05"
//                . "<br>Valor R$: ".$fws_['valor_cobrado'] . ",00</span>";
                $fws_['valor_cobrado'] = $fws_['valor_cobrado'].'.00';
            }
        }
        return $fws_;
    }
    
    public function configurarBoleto($dadosboleto,$objBoleto) {
// ---------------------- DADOS FIXOS DE CONFIGURA��O DO SEU BOLETO --------------- //
        
        $dadosboleto["instrucoes1"] = $objBoleto->getInstrucoes1();
        $dadosboleto["instrucoes2"] = $objBoleto->getInstrucoes2();
        $dadosboleto["instrucoes3"] = $objBoleto->getInstrucoes3();
        $dadosboleto["instrucoes4"] = $objBoleto->getInstrucoes4();;   

//        $fws_['cod_boleto'] = 1;
//        $fws_['cod_boleto'] = $fws_['cod_inscrito']."01";
//        $dadosboleto["nosso_numero"]        = numberPad($fws_['cod_boleto'], 8); //"00000001";     // Nosso numero sem o DV - REGRA: Máximo de 8 caracteres!

        // Composi��o Nosso Numero - CEF SIGCB
        $dadosboleto["nosso_numero1"] = "000"; // tamanho 3
        $dadosboleto["nosso_numero_const1"] = "1"; //constanto 1 , 1=registrada , 2=sem registro
        $dadosboleto["nosso_numero2"] = "000"; // tamanho 3
        $dadosboleto["nosso_numero_const2"] = "4"; //constanto 2 , 4=emitido pelo proprio cliente
        //$dadosboleto["nosso_numero3"] = "000000001"; // tamanho 9        
        $dadosboleto["nosso_numero3"] = "0".$objBoleto->getCodEvento().lpad($objBoleto->getCodBoleto(), 7,'0');

        $dadosboleto["inicio_nosso_numero"] = "14";           // 24 - Padrão da Caixa Economica Federal
        //$dadosboleto["nosso_numero"] = numberPad($objBoleto->getCodBoleto(), 9); //"00000001";     // Nosso numero sem o DV - REGRA: Máximo de 8 caracteres!
        $dadosboleto["nosso_numero"] = $dadosboleto["nosso_numero3"];

        // DADOS OPCIONAIS DE ACORDO COM O BANCO OU CLIENTE
        $dadosboleto["quantidade"]     = "";
        $dadosboleto["valor_unitario"] = "";
        $dadosboleto["aceite"]         = "N";		
        $dadosboleto["especie"]        = "R$";
        $dadosboleto["especie_doc"]    = "OUTROS";
        
        // DADOS DA SUA CONTA - Uniprime
        $dadosboleto["agencia"] = "0001"; // Num da agencia, sem digito
        $dadosboleto["agencia_dv"] = "9"; // Digito do Num da agencia
        /*Substituindo agência pela da uniprime*/
        $dadosboleto["agencia"] = "0001"; // Num da agencia, sem digito
        $dadosboleto["agencia_dv"] = "9"; // Digito do Num da agencia
        
        $dadosboleto["conta"] = "55308";// Num da conta, sem digito
        $dadosboleto["conta_dv"] = "5";   // Digito do Num da conta

        // DADOS PERSONALIZADOS - Uniprime
        $dadosboleto["conta_cedente"] = "55308"; // ContaCedente do Cliente, sem digito (Somente N�meros)
        $dadosboleto["conta_cedente_dv"] = "5"; // Digito da ContaCedente do Cliente
        $dadosboleto["carteira"] = "09";  // C�digo da Carteira: pode ser 06 ou 03

        /*$dadosboleto["identificacao"] = ABREV_EVENTO. " - " . NOME_EVENTO;*/
        $dadosboleto["identificacao"] = "INSTITUTO DE ESTUDOS EM SAUDE COLETIVA";
        $dadosboleto["cpf_cnpj"]      = "80.511.173/0001-03";
        $dadosboleto["cep"]           = "86010-150";
        $dadosboleto["endereco"]      = "Av. Rio de Janeiro, 1500 Sl.11 - ".$dadosboleto["cep"];
        $dadosboleto["cidade_uf"]     = "Londrina-Pr";
        $dadosboleto["cedente"]       = "INSTITUTO DE ESTUDOS EM SAUDE COLETIVA - ".formatCpfCnpjView($dadosboleto["cpf_cnpj"]);
        
        $dadosboleto["cpf_cnpj_beneficiario"]  = "80.511.173/0001-03";
        $dadosboleto["cedente_beneficiario"]   = "INSTITUTO DE ESTUDOS EM SAUDE COLETIVA - ".formatCpfCnpjView($dadosboleto["cpf_cnpj_beneficiario"]);
        $dadosboleto["endereco_beneficiario"]  = "AV. RIO DE JANEIRO, Nº 1500 Sl.11, 86010-150";
        $dadosboleto["cidade_uf_beneficiario"] = "LONDRINA-PR";
        return $dadosboleto;
    }
    public function btnBoleto($fws_, $debug = false) {
//        if($_SERVER['REMOTE_ADDR']!='189.90.70.566'){
//            echo "<strong>Geração de boleto temporariamente indisponível</strong><br><br>&nbsp;";
//        }else{
        $fws_['m'] = 'htmlBoleto';
        if($fws_['tipo_boleto']=='pdfBoleto'){
            $fws_['m'] = 'pdfBoleto';
        }
        ?>
        <form action="<?php echo WB_URL_COMMAND?>" method="post" name="pagamento" target="blank"  onsubmit="return criticarGerarBoleto()">
            <input type="hidden" name="c" value="GerenciarBoleto">
            <input type="hidden" name="m" value="<?php echo $fws_['m']?>">
            <input type="hidden" name="nomeBanco"  value="UNIPRIME">
            <input type="hidden" name="cod_evento" value="<?php echo COD_EVENTO?>">
            <input type="hidden" name="nome_inscrito" value="<?php echo $fws_['nome_inscrito']?>">
            <input type="hidden" name="cod_inscrito"  value="<?php echo $fws_['cod_inscrito']?>">
            <input type="hidden" name="cod_boleto"    value="<?php echo $fws_['cod_boleto']?>">
            <input type="hidden" name="cpf"           value="<?php echo $fws_['cpf']?>">
            <input type="hidden" name="endereco"      value="<?php echo $fws_['endereco']?>">
            <input type="hidden" name="numero"        value="<?php echo $fws_['numero']?>">
            <input type="hidden" name="complemento"   value="<?php echo $fws_['complemento']?>">
            <input type="hidden" name="cidade"        value="<?php echo $fws_['cidade']?>">
            <input type="hidden" name="estado"        value="<?php echo $fws_['estado']?>">
            <input type="hidden" name="cep"           value="<?php echo $fws_['cep']?>">
            <!--<input type="hidden" name="cod_boleto"    value="<?php echo $fws_['cod_boleto']?>">-->
            <input type="hidden" name="valor_cobrado" value="<?php echo $fws_['valor_cobrado']?>">
            <input type="hidden" name="data_vencimento" value="<?php echo $fws_['data_vencimento']?>">
            <input type="hidden" name="complementar" value="<?php echo $fws_['complementar']?>">
            <input type="hidden" name="msg_boleto"    value="<?php echo $fws_['msg_boleto']?>">
            <button class="btn btn-xs btn-primary" type="submit">Gerar Boleto Bancário</button>
        </form>
        <script language='javascript'>
            <!--
            function criticarGerarBoleto(){
                <?php if ($OP_MENSAGEM_BTN_BOLETO){ ?>
                if (confirm('Após gerar boleto bancário, não será permitido alterar opções que afetem o valor da inscrição, deseja continuar?')) {
                    return true;
                }
                return false;
                <?php } ?>
            }
        </script>
        <?php
//        }
    }
    public function btnBoletoInstitucional($fws_, $debug = false) {
        ?>
        <form action="<?php echo WB_URL_COMMAND?>" method="post" name="pagamento" target="blank"  onsubmit="return criticarGerarBoleto()">
            <input type="hidden" name="c" value="GerenciarBoleto">
            <input type="hidden" name="m" value="htmlBoletoInstitucional">
            <input type="hidden" name="nomeBanco"  value="UNIPRIME">
            <input type="hidden" name="cod_evento" value="<?php echo COD_EVENTO?>">
            <input type="hidden" name="nome_inscrito" value="<?php echo $fws_['nome_inscrito']?>">
            <input type="hidden" name="cod_inscrito"  value="<?php echo $fws_['cod_inscrito']?>">
            <input type="hidden" name="cod_boleto"    value="<?php echo $fws_['cod_boleto']?>">
            <input type="hidden" name="cpf"           value="<?php echo $fws_['cpf']?>">
            <input type="hidden" name="nome_recibo"   value="<?php echo $fws_['nome_recibo']?>">
            <input type="hidden" name="cpf_recibo"    value="<?php echo $fws_['cpf_recibo']?>">
            <input type="hidden" name="endereco"      value="<?php echo $fws_['endereco']?>">
            <input type="hidden" name="numero"        value="<?php echo $fws_['numero']?>">
            <input type="hidden" name="complemento"   value="<?php echo $fws_['complemento']?>">
            <input type="hidden" name="cidade"        value="<?php echo $fws_['cidade']?>">
            <input type="hidden" name="estado"        value="<?php echo $fws_['estado']?>">
            <input type="hidden" name="cep"           value="<?php echo $fws_['cep']?>">
            <!--<input type="hidden" name="cod_boleto"    value="<?php echo $fws_['cod_boleto']?>">-->
            <input type="hidden" name="valor_cobrado" value="<?php echo $fws_['valor_cobrado']?>">
            <input type="hidden" name="data_vencimento" value="<?php echo $fws_['data_vencimento']?>">
            <input type="hidden" name="institucional" value="1">
            <input type="hidden" name="complementar" value="<?php echo $fws_['complementar']?>">
            <input type="hidden" name="msg_boleto"    value="<?php echo $fws_['msg_boleto']?>">
            <button class="btn btn-xs btn-primary" type="submit">Gerar Boleto Bancário Institucional</button>
        </form>
        <script language='javascript'>
            <!--
            function criticarGerarBoleto(){
                <?php if ($OP_MENSAGEM_BTN_BOLETO){ ?>
                if (confirm('Após gerar boleto bancário, não será permitido alterar opções que afetem o valor da inscrição, deseja continuar?')) {
                    return true;
                }
                return false;
                <?php } ?>
            }
        </script>
        <?php
    }
    
    public function pdfBoleto($fws_, $debug = false) {
        ob_start();
        $this->htmlBoleto($fws_, $debug = false);
        $content = ob_get_clean();
        $fws_['nomeArquivoBoleto'] = $fws_['cod_inscrito']. '-' . $fws_['cod_boleto'] .'.pdf';

        /*gerando pdf*/
        require_once(dirname(__FILE__) . '/../lib/html2pdf/html2pdf.class.php');
        $html2pdf = new HTML2PDF('P', 'A4', 'pt');
        $html2pdf->setTestTdInOnePage(false);
        $html2pdf->WriteHTML($content, isset($_GET['vuehtml']));
//        $html2pdf->WriteHTML($content, 1);

        if (!file_exists(WB_PATH_LOG_BOLETO)) {
            mkdir(WB_PATH_LOG_BOLETO, 0755);
        }
        $html2pdf->Output(WB_PATH_LOG_BOLETO. '/' . $fws_['nomeArquivoBoleto'], 'F'); // grava o arquivo em disco na primeira vez que ? visualizado
        $html2pdf->Output($fws_['nomeArquivoBoleto']); // grava o arquivo em disco na primeira vez que ? visualizado
        exit();
    }

    public function htmlBoleto($fws_, $debug = false) {
        
        $dadosboleto["numero_documento"]    = "0".COD_EVENTO. numberPad($fws_['cod_inscrito'], 6)."01"; // Num do pedido ou do documento
        $objBoleto = new Boleto();
        $objBoleto->setCodInscrito($fws_['cod_inscrito']);
        $objBoleto->setCodBoleto($fws_['cod_boleto']);
        
        /*carregando boleto*/
        $boletoCarregado = false;
        $boletoAlterado  = false;
        if($objBoleto->getCodBoleto()==''){
            if($objBoleto->carregarUltimoBoletoByIdInscrito($debug)){
                /*ultimo boleto cadastrado*/
                $boletoCarregado = true;
            }
        }else{
            if($objBoleto->getBoletoById($debug)){
                /*boleto carregado pelo id*/
                $boletoCarregado = true;
            }
        }
 
        /*se boleto carregado testar vencimento e alteração de valor enviado*/
        if($boletoCarregado == true){
//            if($objBoleto->getNossoNumero() == ""){
//                $objBoleto->setNossoNumero("0".COD_EVENTO.lpad($objBoleto->getCodBoleto(), 7,'0'));
//                $boletoAlterado = true;
//            }
            if($objBoleto->getNumeroDocumento() == ""){
                $objBoleto->setNumeroDocumento($dadosboleto["numero_documento"] );
                $boletoAlterado = true;
            }

            /*Se a data de vencimento do ultimo boleto cadastrado for menor que hoje, gerar um novo boleto*/
//            if(IP_LIBERADO){
//                echo formatDateCompare($objBoleto->getDataVencimento()).'<'.date('Ymd').'<br>';
//                echo formatMoneyInsert($fws_['valor_cobrado'])."<->".formatMoneyInsert($objBoleto->getValorBoleto())."<br>";
//            }
            $fws['datComparacaoVencimentoBoleto'] = date('Ymd');
            $fws['datComparacaoDescontoBoleto'] = date('Ymd');
            
//            $fws['forcarCarregarUltimoBoleto'] = false;
            //if($debug==true){
//            if(Date('Ymd')<=20200515){
//                $fws_['data_vencimento'] = "15/05/2020";
//                $fws_['valor_cobrado'] = '200.00';
//            }else
//            if(Date('Ymd')<=20200710) {
//                $fws_['data_vencimento'] = "10/07/2020";
//                $fws_['valor_cobrado']   = "200.00";
//            }
//            if(Date('Ymd')<=20200707) {
//                $fws_['data_vencimento'] = "07/07/2020";
//                $fws_['valor_cobrado']   = "250.00";
//            }
//            if($_SESSION['_administrativo']=='1'){
//                echo formatDateCompare($objBoleto->getDataVencimento())."<".$fws['datComparacaoVencimentoBoleto']."<br>";
//                echo formatMoneyInsert($fws_['valor_cobrado'])." != ".formatMoneyInsert($objBoleto->getValorBoleto())."<br>";
//                echo "<br>Data Desconto:".$objBoleto->getDataDesconto();
//                echo "<br>Data Desconto:".$objBoleto->getValorDesconto()."<br>";
//                echo "<br>intitucional:".$fws_['institucional'];
//                echo "<br>cpf_recibo:".$fws_['cpf_recibo'];
//                echo "<br>cpfCnpj:".$objBoleto->getCpfCnpj();
////                exit();
//            }
            
            if((formatDateCompare($objBoleto->getDataVencimento()) < $fws['datComparacaoVencimentoBoleto'])
                or (formatMoneyInsert($fws_['valor_cobrado']) != formatMoneyInsert($objBoleto->getValorBoleto()))
                or ($objBoleto->getDataDesconto() == '' and $objBoleto->getValorDesconto()!='')
                or ($fws_['institucional'] == 1 and $fws_['cpf_recibo']!=$objBoleto->getCpfCnpj())
              ){                
                /*definir nova data de vencimento e novo numero do documento*/
                $objBoleto->setCodBoleto('');
                $objBoleto->setValorPago('');
                $objBoleto->setFlgPagamento('');
                $objBoleto->setFlgRemessa('');
                $objBoleto->setFlgRegistrado('');
                $objBoleto->setArquivoRemessa('');
                $objBoleto->setArquivoRemessaLinha('');
                $objBoleto->setResAlter('');
                $objBoleto->setDatAlter('');
                $objBoleto->setHttpUerAgentAlter('');
                $dadosboleto["numero_documento"] = numberPad($objBoleto->getNumeroDocumento()+1,10);
            }else{
                $dadosboleto["numero_documento"] = $objBoleto->getNumeroDocumento();
            }
        }
//        $objBoleto->setCodBoleto('');
        $gerarHtmlBoleto = true;
        if($objBoleto->getCodBoleto()==''){

            $objInscrito = new Inscrito();
            $objInscrito->setCodInscrito($fws_['cod_inscrito']);
            $objInscrito->getInscritoById();
            $objInscrito->carregarOjbPerfil($debug);
            
            $objPerfil = $objInscrito->getObjPerfil();  
            
            // DADOS DO SEU CLIENTE
            if($fws_['institucional'] == 1){
                  if( $objInscrito->getCpfRecibo()!=''){
                    $fws_["sacado"]      = $objInscrito->getNomeRecibo();
                    $fws_["cpf_cnpj"]    = $objInscrito->getCpfRecibo();
                    $fws_["endereco"]    = $objInscrito->getEnderecoRecibo();
                    $fws_["numero"]      = $objInscrito->getNumeroRecibo();
                    $fws_["complemento"] = $objInscrito->getComplementoRecibo();
                    $fws_["bairro"]      = $objInscrito->getBairroRecibo();
                    $fws_["cidade"]      = $objInscrito->getCidadeRecibo();
                    $fws_["estado"]      = $objInscrito->getEstadoRecibo();
                    $fws_["cep"]         = $objInscrito->getCepRecibo();
                    
                    $dadosboleto["sacado"]    = $fws_['sacado'];   
                    $dadosboleto["cpf_cnpj"]  = $fws_['cpf_cnpj'];   
                    $dadosboleto["bairro"]    = $fws_['bairro'];  
                }  
            }else{
               
                if( $objInscrito->getCpf()!=''){
                    $fws_["sacado"]      = $objInscrito->getNomeInscrito();
                    $fws_["cpf_cnpj"]    = $objInscrito->getCpf();
                    $fws_["endereco"]    = $objInscrito->getEndereco();
                    $fws_["numero"]      = $objInscrito->getNumero();
                    $fws_["complemento"] = $objInscrito->getComplemento();
                    $fws_["bairro"]      = $objInscrito->getBairro();
                    $fws_["cidade"]      = $objInscrito->getCidade();
                    $fws_["estado"]      = $objInscrito->getEstado();
                    $fws_["cep"]         = $objInscrito->getCep();
                    
                    $dadosboleto["sacado"]    = $fws_['sacado'];   
                    $dadosboleto["cpf_cnpj"]  = $fws_['cpf_cnpj'];                     
                    $dadosboleto["bairro"]    = $fws_['bairro'];                     
                }
            } 
            
            $dadosboleto["uf"]        = $fws_['estado'];
            $dadosboleto["cpf_cnpj"]=limparCpfCnpj($dadosboleto["cpf_cnpj"]);
            if(strlen($dadosboleto["cpf_cnpj"])<=11){
                $dadosboleto["tipo_documento"]  = '1';  
            }else{
                $dadosboleto["tipo_documento"]  = '2';  
            }
            $dadosboleto["endereco1"] = $fws_["endereco"] . ", ". $fws_["numero"]. ", ". $fws_["complemento"]; //"Av. Madre Leonia Milito 2000";
            $dadosboleto["endereco2"] = $fws_["cidade"]. "-". $fws_["estado"]. " CEP: ". $fws_["cep"]; //"Cidade - Estado -  CEP: 00000-000";
            
            /*Deixar nosso número nulo quando não estiver utilizando opção de atualização de boleto alterado
             * Ou seja, para toda alteração de valor, registra-se um novo boleto
             */
            $objBoleto->setNossoNumero('');
            
            // DADOS DO BOLETO PARA O SEU CLIENTE
            $taxa_boleto = 0;//2.95;
//            if(Date('Ymd')<=20200515){
//                $fws_['data_vencimento'] = "15/05/2020";
//                $fws_['valor_cobrado']   = "200.00";
//                $dadosboleto["valor_desconto"]     = '50.00';  // Valor do Boleto - REGRA: Com v�rgula e sempre com duas casas depois da virgula
//                $dadosboleto["data_desconto"]      = '15/03/2020';  // Valor do Boleto - REGRA: Com v�rgula e sempre com duas casas depois da virgula
//                $dadosboleto["valor_com_desconto"] = $this->calcularDesconto($fws_['valor_cobrado'] ,$dadosboleto["valor_desconto"]);
//                $dadosboleto["instrucoes1"] = "Para pagamento até dia ". $dadosboleto["data_desconto"] .", receber R$ ". number_format($dadosboleto["valor_com_desconto"], 2, ',', '.');
//            }else
//            if(Date('Ymd')<=20200710) {
//                $fws_['data_vencimento'] = "10/07/2020";
//                $fws_['valor_cobrado']   = "200.00";
//                $dadosboleto["valor_desconto"]     = "";
//                $dadosboleto["data_desconto"]      = "";
//                $dadosboleto["valor_com_desconto"] = "";
//                $dadosboleto["instrucoes1"]        = "";
//
//            }
//            if ($objPerfil->getCodPerfil()==61){
//                $fws_['data_vencimento'] = "12/07/2020";
//                $fws_['valor_cobrado']   = "150.00";
//                $dadosboleto["valor_desconto"]     = "";
//                $dadosboleto["data_desconto"]      = "";
//                $dadosboleto["valor_com_desconto"] = "";
//                $dadosboleto["instrucoes1"]        = "";
//            }
            /*Carregando desconto cadastrado na taxa*/
            if ($objPerfil->getCodPerfil()!=''){
                $objPerfil->carregarTaxaAtiva();
                $objTaxa = $objPerfil->getObjTaxa();
                if($objTaxa->getValorDesconto()!='' and $objTaxa->getDataDesconto()!=''){
                    $fws_['data_vencimento'] = formatDateView($objTaxa->getMaxDataVencimento());
                    $fws_['valor_cobrado']   = $objTaxa->getTxUnicaAVistaValor();
//                    if($_SERVER['REMOTE_ADDR']=='179.154.10.72'){
//                        $debug = 1;
//                        echo "<pre>";
//                        print_r($fws_);
//                        echo "</pre>";
//                        echo __LINE__."<br>";
//                        $fws_ = $this->calculandoDescontoExtra($fws_, $debug);
//                        echo "<pre>";
//                        print_r($fws_);
//                        echo "</pre>";
//                        echo __LINE__."<br>";
//                    }
                    $dadosboleto["valor_desconto"]     = $objTaxa->getValorDesconto();  // Valor do Boleto - REGRA: Com v�rgula e sempre com duas casas depois da virgula
                    $dadosboleto["data_desconto"]      = formatDateView($objTaxa->getDataDesconto());  // Valor do Boleto - REGRA: Com v�rgula e sempre com duas casas depois da virgula
                    $dadosboleto["valor_com_desconto"] = $this->calcularDesconto($fws_['valor_cobrado'] ,$dadosboleto["valor_desconto"]);
                    $dadosboleto["instrucoes1"] = "Para pagamento até dia ". $dadosboleto["data_desconto"] .", receber R$ ". number_format($dadosboleto["valor_com_desconto"], 2, ',', '.');               
                }
            }
//            if($_SERVER['REMOTE_ADDR']=='179.154.10.72'){
//                echo "<pre>";
//                print_r($dadosboleto);
//                echo "</pre>";
//                exit();
//            }
//            if(Date('Ymd')<=20200707) {
//                $fws_['data_vencimento'] = "07/07/2020";
//                $fws_['valor_cobrado']   = "250.00";
//                $dadosboleto["valor_desconto"]     = '50.00';  // Valor do Boleto - REGRA: Com v�rgula e sempre com duas casas depois da virgula
//                $dadosboleto["data_desconto"]      = '30/05/2020';  // Valor do Boleto - REGRA: Com v�rgula e sempre com duas casas depois da virgula
//                $dadosboleto["valor_com_desconto"] = $this->calcularDesconto($fws_['valor_cobrado'] ,$dadosboleto["valor_desconto"]);
//                $dadosboleto["instrucoes1"] = "- Para pagamento até dia ". $dadosboleto["data_desconto"] .", receber R$ ". number_format($dadosboleto["valor_com_desconto"], 2, ',', '.');
//
//            }
            $dadosboleto["instrucoes2"] = "Não receber após o vencimento<br>Para efetuar pagamento aguarde 6h após data de emissão";            
            $dadosboleto["instrucoes3"] = "Emitido pelo sistema de inscrição:<br> ".ABREV_EVENTO." - ".URL_BOLETO;
                
            $valor_cobrado = formatMoneyView($fws_['valor_cobrado']);  //"5,00"; // Valor - REGRA: Sem pontos na milhar e tanto faz com "." ou "," ou com 1 ou 2 ou sem casa decimal
            $valor_cobrado = str_replace(".", "",$valor_cobrado);            
            $valor_cobrado = str_replace(",", ".",$valor_cobrado);
//            $valor_cobrado = '1,00';
            $valor_boleto=$valor_cobrado+$taxa_boleto;
            $valor_boleto = formatMoneyBoleto($valor_boleto);
            
   //        $dadosboleto["numero_documento"]    = "01". numberPad($fws_['cod_inscrito'], 6)."01"; // Num do pedido ou do documento

            $dadosboleto["data_vencimento"]     = $fws_['data_vencimento']; //date("d/m/Y", time() + ($dias_de_prazo_para_pagamento * 86400));  // Prazo de X dias  OU  informe data: "13/04/2006"  OU  informe "" se Contra Apresentacao; Data de Vencimento do Boleto - REGRA: Formato DD/MM/AAAA
            $dadosboleto["data_documento"]      = date("d/m/Y");  // Data de emissão do Boleto
            $dadosboleto["data_emissao"]        = date("d/m/Y");  // Data de processamento do boleto (opcional)
            $dadosboleto["data_processamento"]  = date("d/m/Y");  // Data de processamento do boleto (opcional)
            $dadosboleto["valor_boleto"]        = $valor_boleto;  // Valor do Boleto - REGRA: Com v�rgula e sempre com duas casas depois da virgula
            $dadosboleto["valor_documento"]     = $valor_boleto;  // Valor do Boleto - REGRA: Com v�rgula e sempre com duas casas depois da virgula
           
            // INFORMACOES PARA O CLIENTE
            $dadosboleto["demonstrativo1"] = ABREV_EVENTO." - inscrição número: ".$objInscrito->getCodInscrito()." | Categoria: ".$objPerfil->getTitulo();
            if($fws_['boleto_complementar']==1){
                $dadosboleto["demonstrativo2"] = "Complemento de valor de inscrição";
                $dadosboleto["demonstrativo3"] = "Boleto complementar";
            }else{
//                if($objInscrito->getCodOficina()){
//                    $dadosboleto["demonstrativo2"] = "Minicurso: ".$objInscrito->getCodOficina();
//                }else{
                    $dadosboleto["demonstrativo2"] = "";
//                }
//                if($objInscrito->getInfoAlmoco()){
//                    $dadosboleto["demonstrativo3"] = "Refeições: ".$objInscrito->getInfoAlmoco();
//                }else{
                    $dadosboleto["demonstrativo3"] = "";
//                }
            }
            //$dadosboleto["demonstrativo3"] = $objPerfil->getInvestimento();
            
            $objBoleto->setAll($fws_);
            $objBoleto->setAll($dadosboleto);
            $boletoAlterado = true;
        }
        if($boletoAlterado == true){
            try {
                $objBoleto->salvar();
            } catch (OKException $exc) {
                $fws_['cod_boleto'] = $objBoleto->getCodBoleto();
                //echo $exc->getMessage();
                $gerarHtmlBoleto = true;
            } catch (Exception $exc) {
                echo $exc->getMessage();
                $gerarHtmlBoleto = false;
            }
        }
        if(!$gerarHtmlBoleto){
            exit();
        }
        $dadosboleto["data_vencimento"]     = $objBoleto->getDataVencimento();      // Data de Vencimento do Boleto - REGRA: Formato DD/MM/AAAA
        $dadosboleto["data_documento"]      = $objBoleto->getDataDocumento();       // Data de emiss�o do Boleto
        $dadosboleto["data_emissao"]        = $objBoleto->getDataEmissao();         // Data de processamento do boleto (opcional)
        $dadosboleto["data_processamento"]  = $objBoleto->getDataProcessamento();   // Data de processamento do boleto (opcional)
        $dadosboleto["valor_boleto"]        = formatMoneyBoleto($objBoleto->getValorBoleto());     // Valor do Boleto - REGRA: Com v�rgula e sempre com duas casas depois da virgula
        $dadosboleto["valor_documento"]     = formatMoneyBoleto($objBoleto->getValorDocumento());  // Valor do Boleto - REGRA: Com v�rgula e sempre com duas casas depois da virgula
        
        // DADOS DO SEU CLIENTE
        $dadosboleto["sacado"]    = $objBoleto->getSacado()." / ".formatCpfCnpjView($objBoleto->getCpfCnpj())." / ".formatCepView($objBoleto->getCep());
        $dadosboleto["cep"]       = $objBoleto->getCep();
        $dadosboleto["uf"]        = $objBoleto->getUf();
        $dadosboleto["cpf"]       = $objBoleto->getCpfCnpj();   
        $dadosboleto["endereco1"] = $objBoleto->getEndereco1(); //"Av. Madre Leonia Milito 2000";
        $dadosboleto["endereco2"] = $objBoleto->getEndereco2(); //"Cidade - Estado -  CEP: 00000-000";

        // INFORMACOES PARA O CLIENTE
        $dadosboleto["demonstrativo1"] = $objBoleto->getDemonstrativo1();
        $dadosboleto["demonstrativo2"] = $objBoleto->getDemonstrativo2();
        $dadosboleto["demonstrativo3"] = $objBoleto->getDemonstrativo3();

        // ---------------------- DADOS FIXOS DE CONFIGURA��O DO SEU BOLETO --------------- //
        $dadosboleto = $this->configurarBoleto($dadosboleto,$objBoleto);
        // NÃO ALTERAR!
        include("lib/boleto/include/funcoes_uniprime.php"); 
        include("lib/boleto/include/layout_uniprime.php");
        ?>
        <script>
            window.focus();
        </script>
        <?php
    }

    public function htmlBoletoAssociacao($fws_, $debug = false) {
        $COD_EVENTO = 7;
        $objBoleto = new Boleto();
        $objBoleto->setCodInscrito($fws_['cod_inscrito']);
        $objBoleto->setCodBoleto($fws_['cod_boleto']);
        $objBoleto->setCodEvento($COD_EVENTO);
        $dadosboleto["numero_documento"]    = "0".$objBoleto->getCodEvento(). numberPad($objBoleto->getCodInscrito(), 6)."01"; // Num do pedido ou do documento
        
        /*carregando boleto*/
        $boletoCarregado = false;
        $boletoAlterado  = false;
        if($objBoleto->getCodBoleto()==''){
            if($objBoleto->carregarUltimoBoletoByIdInscrito($debug)){
                /*ultimo boleto cadastrado*/
                $boletoCarregado = true;
            }
        }else{
            if($objBoleto->getBoletoById($debug)){
                /*boleto carregado pelo id*/
                $boletoCarregado = true;
            }
        }
 
        /*se boleto carregado testar vencimento e alteração de valor enviado*/
        if($boletoCarregado == true){
            if($objBoleto->getNossoNumero() == ""){
                $objBoleto->setNossoNumero("0".$objBoleto->getCodEvento().lpad($objBoleto->getCodBoleto(), 7,'0'));
                $boletoAlterado = true;
            }
            if($objBoleto->getNumeroDocumento() == ""){
                $objBoleto->setNumeroDocumento($dadosboleto["numero_documento"] );
                $boletoAlterado = true;
            }

            /*Se a data de vencimento do ultimo boleto cadastrado for menor que hoje, gerar um novo boleto*/
//            if(IP_LIBERADO){
//                echo formatDateCompare($objBoleto->getDataVencimento()).'<'.date('Ymd').'<br>';
//                echo formatMoneyInsert($fws_['valor_cobrado'])."<->".formatMoneyInsert($objBoleto->getValorBoleto())."<br>";
//            }
            $fws['datComparacaoVencimentoBoleto'] = date('Ymd');
            
//            $fws['forcarCarregarUltimoBoleto'] = false;
//            if($fws['forcarCarregarUltimoBoleto'] == true){
//                $dadosboleto["numero_documento"] = $objBoleto->getNumeroDocumento();
//            }else
            if((formatDateCompare($objBoleto->getDataVencimento()) < $fws['datComparacaoVencimentoBoleto'])
                or (formatMoneyInsert($fws_['valor_cobrado']) != formatMoneyInsert($objBoleto->getValorBoleto()))){
                $boletoAlterado = true;
                /*definir nova data de vencimento e novo numero do documento*/
//                if($_SERVER['REMOTE_ADDR']=='177.34.46.46'){
//                    echo formatDateCompare($objBoleto->getDataVencimento())."<".date('Ymd')."<br>";
//                    echo formatMoneyInsert($fws_['valor_cobrado'])." != ".formatMoneyInsert($objBoleto->getValorBoleto())."<br>";
//                    exit();
//                }
                $objBoleto->setCodBoleto('');
                $objBoleto->setNossoNumero('');
                $objBoleto->setValorPago('');
                $objBoleto->setFlgPagamento('');
                $objBoleto->setFlgRemessa('');
                $objBoleto->setFlgRegistrado('');
                $objBoleto->setArquivoRemessa('');
                $objBoleto->setArquivoRemessaLinha('');
                $objBoleto->setResAlter('');
                $objBoleto->setDatAlter('');
                $objBoleto->setHttpUerAgentAlter('');
                $dadosboleto["numero_documento"] = numberPad($objBoleto->getNumeroDocumento()+1,10);
            }else{
                $dadosboleto["numero_documento"] = $objBoleto->getNumeroDocumento();
            }
        }
//            
//        }else{
//            $objBoleto->getBoletoByNumeroDocumento($debug);
//        }
        
        $gerarHtmlBoleto = true;
        if($boletoCarregado==false){
            $objBoleto->setCodBoleto('');
            $fws_['cod_boleto']='';
        }
        if($boletoCarregado==false or $boletoAlterado==true){
            /*Deixar nosso número nulo quando não estiver utilizando opção de atualização de boleto alterado
             * Ou seja, para toda alteração de valor, registra-se um novo boleto
             */
            $objBoleto->setNossoNumero('');
            
            // DADOS DO BOLETO PARA O SEU CLIENTE
            $taxa_boleto = 0;//2.95;
            
            /*verificar se a data de vencimento informada é menor que a data atual*/
            if(formatDateCompare($fws_['data_vencimento'])<=date('Ymd')){
                $fws_['data_vencimento'] = date("d/m/Y", time() + (5 * 86400));
            }else{
                $data_venc = $fws_['data_vencimento']; //date("d/m/Y", time() + ($dias_de_prazo_para_pagamento * 86400));  // Prazo de X dias  OU  informe data: "13/04/2006"  OU  informe "" se Contra Apresentacao;   
            }
//            $fws_['data_vencimento'] = date("d/m/Y", time() + ($FWS_Dias_Prazo_Pagamento * 86400));
            /*Maxima data de vencimento*/
//            $fws_['data_vencimento'] = "31/05/2017";
//            $data_venc = "31/05/2017";
            

            $valor_cobrado = formatMoneyView($fws_['valor_cobrado']);  //"5,00"; // Valor - REGRA: Sem pontos na milhar e tanto faz com "." ou "," ou com 1 ou 2 ou sem casa decimal
            $valor_cobrado = str_replace(".", "",$valor_cobrado);            
            $valor_cobrado = str_replace(",", ".",$valor_cobrado);
//            $valor_cobrado = '1,00';
            $valor_boleto=$valor_cobrado+$taxa_boleto;
            $valor_boleto = formatMoneyBoleto($valor_boleto);
            
   //        $dadosboleto["numero_documento"]    = "01". numberPad($fws_['cod_inscrito'], 6)."01"; // Num do pedido ou do documento

            $dadosboleto["data_vencimento"]     = $data_venc;     // Data de Vencimento do Boleto - REGRA: Formato DD/MM/AAAA
            $dadosboleto["data_documento"]      = date("d/m/Y");  // Data de emissão do Boleto
            $dadosboleto["data_emissao"]        = date("d/m/Y");  // Data de processamento do boleto (opcional)
            $dadosboleto["data_processamento"]  = date("d/m/Y");  // Data de processamento do boleto (opcional)
            $dadosboleto["valor_boleto"]        = $valor_boleto;  // Valor do Boleto - REGRA: Com v�rgula e sempre com duas casas depois da virgula
            $dadosboleto["valor_documento"]     = $valor_boleto;  // Valor do Boleto - REGRA: Com v�rgula e sempre com duas casas depois da virgula
             
            // DADOS DO SEU CLIENTE
            $dadosboleto["sacado"]  = $fws_['nome_inscrito'];   
            $dadosboleto["uf"]      = $fws_['estado'];  
            $dadosboleto["cpf_cnpj"]= $fws_['cpf'];  
            $dadosboleto["bairro"]  = $fws_['bairro']; 
            
            $dadosboleto["cpf_cnpj"]=limparCpfCnpj($dadosboleto["cpf_cnpj"]);
            if(strlen($dadosboleto["cpf_cnpj"])<=11){
                $dadosboleto["tipo_documento"]  = '1';  
            }else{
                $dadosboleto["tipo_documento"]  = '2';  
            }
            $dadosboleto["endereco1"] = $fws_["endereco"] . ", ". $fws_["numero"]. ", ". $fws_["complemento"]; //"Av. Madre Leonia Milito 2000";
            $dadosboleto["endereco2"] = $fws_["cidade"]. "-". $fws_["estado"]. " CEP: ". $fws_["cep"]; //"Cidade - Estado -  CEP: 00000-000";

            $dadosboleto["bairro"] = $fws_['bairro'];
           
            // INFORMACOES PARA O CLIENTE
            $dadosboleto["demonstrativo1"] = "Associado - número: ".$fws_['cod_inscrito']." | Categoria: ".$fws_['categoria'];
            //$dadosboleto["demonstrativo3"] = $objPerfil->getInvestimento();
            $dadosboleto["instrucoes2"] = "- Não receber após o vencimento<br> - Para evitar transtornos, aguarde 12h após data de emissão para efetuar pagamento";
            $dadosboleto["instrucoes3"] = "- Emitido pelo sistema de inscrição de Associados Inesco";
            
            $objBoleto->setAll($fws_);
            $objBoleto->setAll($dadosboleto);
            $boletoAlterado = true;
        }
//        echo "<pre>";
//        print_r($dadosboleto);
//        echo "->".$objBoleto->getCodBoleto()."<-";
//        echo "</pre>";
//        exit();
        if($boletoAlterado == true){
            try {
                $objBoleto->salvar($debug);
            } catch (OKException $exc) {
                $fws_['cod_boleto'] = $objBoleto->getCodBoleto();
                echo $debug? $exc->getMessage() : "";
                $gerarHtmlBoleto = true;
            } catch (Exception $exc) {
                echo $exc->getMessage();
                $gerarHtmlBoleto = false;
            }
        }
        
//        echo "<pre>";
//        print_r($objBoleto);
//        echo "</pre>";
//        exit();
        
        if(!$gerarHtmlBoleto){
            exit();
        }
        
        $data_venc = $objBoleto->getDataVencimento();     // Data de Vencimento do Boleto - REGRA: Formato DD/MM/AAAA
        $dadosboleto["data_vencimento"]     = $objBoleto->getDataVencimento();      // Data de Vencimento do Boleto - REGRA: Formato DD/MM/AAAA
        $dadosboleto["data_documento"]      = $objBoleto->getDataDocumento();       // Data de emiss�o do Boleto
        $dadosboleto["data_emissao"]        = $objBoleto->getDataEmissao();         // Data de processamento do boleto (opcional)
        $dadosboleto["data_processamento"]  = $objBoleto->getDataProcessamento();   // Data de processamento do boleto (opcional)
        $dadosboleto["valor_boleto"]        = formatMoneyBoleto($objBoleto->getValorBoleto());     // Valor do Boleto - REGRA: Com v�rgula e sempre com duas casas depois da virgula
        $dadosboleto["valor_documento"]     = formatMoneyBoleto($objBoleto->getValorDocumento());  // Valor do Boleto - REGRA: Com v�rgula e sempre com duas casas depois da virgula
        
        // DADOS DO SEU CLIENTE
        $dadosboleto["sacado"]    = $objBoleto->getSacado()." / ".formatCpfCnpjView($objBoleto->getCpfCnpj())." / ".formatCepView($objBoleto->getCep());
        $dadosboleto["cep"]       = $objBoleto->getCep();
        $dadosboleto["uf"]        = $objBoleto->getUf();
        $dadosboleto["cpf"]       = $objBoleto->getCpfCnpj();   
        $dadosboleto["endereco1"] = $objBoleto->getEndereco1(); //"Av. Madre Leonia Milito 2000";
        $dadosboleto["endereco2"] = $objBoleto->getEndereco2(); //"Cidade - Estado -  CEP: 00000-000";

        // INFORMACOES PARA O CLIENTE
        $dadosboleto["demonstrativo1"] = $objBoleto->getDemonstrativo1();
        $dadosboleto["demonstrativo2"] = $objBoleto->getDemonstrativo2();
        $dadosboleto["demonstrativo3"] = $objBoleto->getDemonstrativo3();

        // ---------------------- DADOS FIXOS DE CONFIGURA��O DO SEU BOLETO --------------- //
        $dadosboleto = $this->configurarBoleto($dadosboleto,$objBoleto);
        // NÃO ALTERAR!
        include("lib/boleto/include/funcoes_uniprime.php"); 
        include("lib/boleto/include/layout_uniprime.php");
        ?>
        <script>
            window.focus();
        </script>
        <?php
    }
    public function htmlBoletoInstitucional($fws_, $debug = false) {
        $fws_['institucional'] = 1;
        $this->htmlBoleto($fws_,$debug);
        exit();
    }
    public function calcularDesconto($valorCheio,$valorDesconto,$debug=false){
        $valorComDesconto = $valorCheio - $valorDesconto;
        if($debug){
            echo $valorCheio."<br>";
            echo $valorDesconto."<br>";
            echo $valorComDesconto."<br>";
        }    
        return $valorComDesconto;
    }
}

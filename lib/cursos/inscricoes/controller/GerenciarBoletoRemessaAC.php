<?php

class GerenciarBoletoRemessaAC {

    public function __construct(){
        @$cod_usuario = $_SESSION['_cod_usuario'];
        @$cod_sistema = 1;
        @$cod_direito = 5;
        @$cod_msg     = false;  
        @FWA_Autenticacao($cod_usuario,$cod_sistema,$cod_direito,$cod_msg,true);
    }
    public function gerarArquivoRemessa($fws_, $debug = false) {
        $objEvConf = EventoConfiguracao::getInstancePopulado(COD_EVENTO);
        $gatewayPagamento = $objEvConf->getCodEvento()==45? 'SISPRIME' : $objEvConf->getGatewayPagamento();

        if ($gatewayPagamento == 'UNIPRIME') {
            $gerarRemessa = new GerenciarBoletoRemessaUniprimeAC();
        } elseif ($gatewayPagamento == 'SISPRIME') {
            $gerarRemessa = new GerenciarBoletoRemessaSisprimeAC();
        } else {
            // Tratar caso onde o gateway de pagamento não é reconhecido
            echo "Gateway de pagamento não reconhecido.";
        }
        $gerarRemessa->gerarArquivoRemessa($fws_,$debug);
    }
    public function gerarArquivoRemessaCaixa($fws_, $debug = false) {
       
        if($_SERVER['REMOTE_ADDR']=='189.90.70.56'){
            $this->num_seq_arquivo = 2; // ultima remessa enviada
            $this->v_cod_lote = '0';
            
            $this->situacao_arquivo = 'REMESSA-PRODUCAO';
            $this->nome_empresa = 'OPCTEC';
            $this->tipo_cpf_cnpj = '1'; //1 para cpf 2 para cnpj
            $this->cpf_cnpj = '01603004955';

            // Dados bancários
            $this->v_banco        = '104';
            $this->v_agencia      = '03076';
            $this->v_dv_agencia   = '7';
            $this->v_cod_convenio= '841993';
        }else{
            $this->num_seq_arquivo = 0; // ultima remessa enviada
            $this->v_cod_lote = '0';
            $this->situacao_arquivo = 'REMESSA-PRODUCAO';
            $this->nome_empresa = ABREV_EVENTO;
            $this->tipo_cpf_cnpj = '2'; //1 para cpf 2 para cnpj
            $this->cpf_cnpj = '03431530000191';

            // Dados bancários
            $this->v_banco        = '104';
            $this->v_agencia      = '03178';
            $this->v_dv_agencia   = '0';
//            $this->v_cod_convenio= '672746';            
            $this->v_cod_convenio= '847332';            
        }
        
        $this->num_seq_arquivo = $this->num_seq_arquivo + 1;
        $this->num_seq_lote = '1';
                
        $textoArquivo .= $this->headerArquivo($fws_, $debug);
        $textoArquivo .= $this->headerLote($fws_, $debug);
        
        $this->v_cod_lote = $this->v_cod_lote + 1;
        
        //inicio loop
        $objBoleto = new Boleto();
        $objBoleto->carregarListaBoletos($debug);
        foreach ($objBoleto->getListaBoletos() as $objL) {
            
            $fws_['nosso_numero']       = $objL->getCodBoleto();
            $fws_['numero_documento']   = $objL->getNumeroDocumento();
            $fws_['data_vencimento']    = retirarBarras($objL->getDataVencimento());
            $fws_['valor_boleto']       = $objL->getValorBoleto();
            $fws_['data_processamento'] = retirarBarras($objL->getDataProcessamento());
            $fws_['data_juro_mora']     = '0';//vencimento +1
            $fws_['data_vencto_multa']  = '';
            $fws_['pagador_tipo_documento']= $objL->getTipoDocumento(); //1->cpf ou 2->cnpj
            $fws_['pagador_cpf_cnpj']   = $objL->getCpfCnpj();
            $fws_['pagador_nome']       = removerAcentos($objL->getSacado());
            $fws_['pagador_endereco']   = removerAcentos($objL->getEndereco1());
            $fws_['pagador_bairro']     = removerAcentos($objL->getBairro());
            $fws_['pagador_cep']        = removerHifen($objL->getCep());//completo sem hifen
            $fws_['pagador_cidade']     = removerAcentos($objL->getCidade());
            $fws_['pagador_uf']         = $objL->getUf();

            $fws_['num_seq_registro']   = $fws_['num_seq_registro']+1;
            $textoArquivo .= $this->registroDetalheP($fws_, $debug);
            
            $fws_['num_seq_registro']   = $fws_['num_seq_registro']+1;
            $textoArquivo .= $this->registroDetalheQ($fws_, $debug);

            //$fws_['num_seq_registro']   = $fws_['num_seq_registro']+1;
            //$textoArquivo .= $this->registroDetalheR($fws_, $debug);
            $this->total_titulos_lote = $this->total_titulos_lote + 1;
            
            //$this->total_registro_lote = $this->total_registro_lote + 1;
//            $this->total_registro = $this->total_registro + 1;
            $this->valor_total_registro_lote = $fws_['valor_boleto']+$this->valor_total_registro_lote; //soma dos valores por lote
        
        }
        $this->total_registro_lote = $fws_['num_seq_registro'] + 2;
        $this->total_registro_arquivo = $this->total_registro_lote + 2;
        
        $textoArquivo .= $this->trailerLote($fws_, $debug);
        $textoArquivo .= $this->trailerArquivo($fws_, $debug);
        
        echo "<pre>";
        echo $textoArquivo;
        echo "</pre>";
        $nomeArquivoLog = "E". Date('d'). lpad($this->num_seq_arquivo, 5, 0) . ".REM";
        $fileLog = WB_PATH_LOG_REMESSA. "/" . $nomeArquivoLog;
        file_put_contents($fileLog, $textoArquivo);
//        file_put_contents($fileLog, $textoArquivo, FILE_APPEND);
    }

    public function headerArquivo($fws_, $debug = false){
        $linhaN = ''
        . $this->v_banco        //-- Código do banco
        . lpad($this->v_cod_lote, 4, '0')	//-- Código do lote
        . '0'                   //-- Tipo de registro
        . lpad(' ', 9, ' ')     //-- Preenchido com branco
        . $this->tipo_cpf_cnpj  //-- Tipo de inscrição do beneficiário
        . lpad($this->cpf_cnpj, 14, '0')	//-- Número de inscrição do beneficiário
        . lpad('0', 20, '0')    //-- Preenchido com zeros
        . lpad($this->v_agencia, 5, '0')//-- Agência
        . $this->v_dv_agencia   //-- Dígito verificador da agência
        . $this->v_cod_convenio //-- Código do beneficiário (convênio)
        . lpad('0', 7, '0')     //-- Preenchido com zeros
        . lpad('0', 1, '0')     //-- Preenchido com zeros
        . rpad($this->nome_empresa, 30, ' ')	//-- Nome da empresa
        . rpad('CAIXA ECONOMICA FEDERAL', 30, ' ')//-- Nome do banco
        . lpad(' ', 10, ' ')    //-- Preenchido com branco
        . '1'                   //-- Código da remessa
        . Date('dmY') //to_char(sysdate, 'DDMMYYYY') //-- Data de geração do arquivo
        . Date('His') //to_char(sysdate, 'HH24MISS') //-- Hora de geração do arquivo
        . lpad($this->num_seq_arquivo, 6, '0') //-- Número sequencial adotado (NSA)
        . '050' 		//-- Versão do leiaute
        . lpad('0', 5, '0') 	//-- Preenchido com zeros
        . lpad(' ', 20, ' ') 	//-- Preenchido com branco
        . rpad($this->situacao_arquivo, 20, ' ')//-- Situação do arquivo
        . lpad(' ', 4, ' ') 	//-- Preenchido com branco
        . lpad(' ', 25, ' ')	//-- Preenchido com branco
        .PHP_EOL;
        return $linhaN;
    }
    
    public function headerLote($fws_, $debug = false){
       
        $linhaN = ''
	. $this->v_banco 	//-- Código do banco
        . lpad($this->v_cod_lote, 4, '0')  //-- Código do lote
        . '1'                   //-- Tipo de registro
        . 'R' 			//-- Tipo de operação
        . '01' 			//-- Tipo de serviço
        . lpad('0', 2, '0') 	//-- Preenchido com zeros
        . '030' 		//-- Versão do leiaute
        . lpad(' ', 1, ' ') 	//-- Preenchido com branco
        . $this->tipo_cpf_cnpj 	//-- Tipo de inscrição do beneficiário
        . lpad($this->cpf_cnpj, 15, '0') //-- Número de inscrição do beneficiário
        . $this->v_cod_convenio //-- Código do beneficiário (convênio)
        . lpad('0', 14, '0') 	//-- Preenchido com zeros
        . lpad($this->v_agencia, 5, '0')//-- Agência
        . $this->v_dv_agencia 	//-- Dígito verificador da agência
        . $this->v_cod_convenio //-- Código do beneficiário (convênio)
        . lpad('0', 7, '0') 	//-- Código do modelo de boleto personalizado
        . lpad('0', 1, '0') 	//-- Preenchido com zero
        . rpad($this->nome_empresa, 30, ' ')//-- Nome da empresa
        . lpad(' ', 40, ' ') 	//-- Mensagem 1
        . lpad(' ', 40, ' ') 	//-- Mensagem 2
        . lpad($this->num_seq_lote, 8, '0')   //-- Número sequencial adotado (NSA)
        . Date('dmY') //to_char(sysdate, 'DDMMYYYY') //-- Data de geração do arquivo
        . lpad('0', 8, '0') 	//-- Preenchido com zeros
        . lpad(' ', 33, ' ')	//-- Preenchido com branco
        .PHP_EOL;
        return $linhaN;
    }
    
    public function registroDetalheP($fws_ , $debug = false){
        $linhaN = ''
        . $this->v_banco 	//-- Código do banco
        . lpad($this->v_cod_lote, 4, '0')	//-- Código do lote
        . '3' 			//-- Tipo de registro
        . lpad($fws_['num_seq_registro'], 5, '0')	//-- Número sequencial do registro no lote
        . 'P'			//-- Código do segmento do registro
        . lpad(' ', 1, ' ')	//-- Preenchido com branco
        . '01'			//-- Código de movimento remessa
        . lpad($this->v_agencia, 5, '0')//-- Agência
        . $this->v_dv_agencia 	//-- Dígito verificador da agência
        . $this->v_cod_convenio //-- Código do beneficiário (convênio)
        . lpad('0', 8, '0')	//-- Preenchido com zeros
        . lpad('0', 3, '0')	//-- Preenchido com zeros
        . '14'			//-- Modalidade da carteira: título registrado emissão pelo cedente
        . lpad($fws_['nosso_numero'], 15, '0')	//-- Identificação do título no banco (Nosso Número)
        . '1'			//-- Código de carteira
        . '1'			//-- Forma de cadastramento do título no banco: o título não será impresso pelo banco
        . '2'			//-- Tipo de documento
        . '2'			//-- Identificação da emissão do boleto
        . '0'			//-- Identificação de entrega do boleto
        . rpad($fws_['numero_documento'], 11, ' ')	//-- Número do documento de cobrança
        . lpad(' ', 4, ' ')	//-- Preenchido com branco
        . $fws_['data_vencimento'] //to_char($v_dat_vencto, 'DDMMYYYY') //-- Data de vencimento 
        . lpad(($fws_['valor_boleto']*100),15,'0') //lpad(($v_valor_boleto * 100), 15, '0') //-- Valor do título	
        . lpad('0', 5, '0')	//-- Preenchido com zeros
        . '0'                   //-- Preenchido com zero
        . '99'			//-- Espécie do título
        . 'N'			//-- Identificação do título Aceito
        . $fws_['data_processamento'] //to_char($v_data_processamento, 'DDMMYYYY') //-- Data de emissão do título
        . '3'			//-- Código do juros de mora // 1->valor diario, 2->taxa mensal, 3->isento
        . lpad($fws_['data_juro_mora'], 8, '0') //to_char($v_data_juro_mora, 'DDMMYYYY') //-- Data do juro de mora
        . lpad('0', 15, '0')	//-- Juros de mora 1% ao mês
        . '0'			//-- Código do desconto 1
        . lpad('0', 8, '0')	//-- Data do desconto 1
        . lpad('0', 15, '0')	//-- Valor a ser concedido
        . lpad('0', 15, '0')	//-- Valor de IOF
        . lpad('0', 15, '0')	//-- Valor do abatimento
        . rpad($fws_['numero_documento'], 25, ' ') //rpad($v_numero_documento, 25, ' ')//-- Identificação do título na empresa
        . '3'                   //-- Código para protesto: Não protestar
        . lpad('0', 2, '0')	//-- Número de dias para protesto
        . '1'			//-- Código para baixa/devolução: Baixar/Devolver
        . lpad('120', 3, '0')	//-- Número de dias para baixa/devolução
        . '09'			//-- Código da moeda: REAL
        . lpad('0', 10, '0')	//-- Preenchido com zeros
        . lpad(' ', 1, ' ')     //-- Preenchido com branco	
        .PHP_EOL;
        return $linhaN;
    }

    public function registroDetalheQ($fws_ , $debug = false){

        $linhaN = ''
        . $this->v_banco 	//-- Código do banco
        . lpad($this->v_cod_lote, 4, '0') //-- Código do lote
        . '3'                   //-- Tipo de registro
        . lpad($fws_['num_seq_registro'], 5, '0')	//-- Número sequencial do registro no lote
        . 'Q'			//-- Código do segmento do registro
        . ' '                   //-- Preenchido com branco
        . '01'			//-- Código de movimento remessa
        . $fws_['pagador_tipo_documento']//-- tipo de inscrição do pagador
        . lpad($fws_['pagador_cpf_cnpj'], 15, '0')    //-- Número de inscrição do pagador
        . rpad($fws_['pagador_nome'], 40, ' ')      //-- Nome do pagador
        . rpad(substr($fws_['pagador_endereco'],0,40), 40, ' ') //-- Endereço do pagador
        . rpad(substr($fws_['pagador_bairro'],0,15), 15, ' ')	//-- Bairro do pagador
        . rpad($fws_['pagador_cep'], 8, '0')        //-- Cep do pagador 
        . rpad(substr($fws_['pagador_cidade'],0,15), 15, ' ')	//-- Cidade do pagador
        . $fws_['pagador_uf']   //-- Unidade da federação
        . '0' 			//-- Tipo de inscrição do sacador/avalista
        . lpad('0', 15, '0')	//-- Número de inscrição do sacador/avalista
        . rpad(' ', 40, ' ')	//-- Nome do sacador/avalista
        . lpad(' ', 3, ' ')	//-- Preenchido com zeros
        . lpad(' ', 20, ' ')	//-- Preenchido com branco
        . lpad(' ', 8, ' ')	//-- Preenchido com branco
        .PHP_EOL;
        return $linhaN;
    }    
    
    public function registroDetalheR($fws_ , $debug = false){
        $linhaN = ''
        . $this->v_banco 	//-- Código do banco
        . lpad($this->v_cod_lote, 4, '0') 	//-- Código do lote
        . '3' 			//-- Tipo de registro
        . lpad($fws_['num_seq_registro'], 5, '0')	//-- Número sequencial do registro no lote
        . 'R'			//-- Código do segmento do registro
        . ' '                   //-- Preenchido com branco
        . '01'			//-- Código de movimento remessa
        . '0'			//-- Código do desconto 2
        . lpad('0', 8, '0')	//-- Data do desconto 2
        . lpad('0', 15, '0')	//-- Valor do desconto 2
        . '0'			//-- Código do desconto 3
        . lpad('0', 8, '0')	//-- Data do desconto 3
        . lpad('0', 15, '0')	//-- Valor do desconto 3
        . '0'			//-- Código da multa
        . lpad($fws_['data_vencto_multa'],8,'0') //to_char(($v_dat_vencto + 1), 'DDMMYYYY')	//		-- Data da multa	
        . lpad('200', 15, '0')	//-- Valor Percentual da multa 2% duas casas decimais
        . lpad(' ', 10, ' ')	//-- Preenchido com branco
        . lpad(' ', 40, ' ')	//-- Preenchido com branco
        . lpad(' ', 40, ' ')	//-- Preenchido com branco
        . lpad(' ', 50, ' ')	//-- Preenchido com branco
        . lpad(' ', 11, ' ')	//-- Preenchido com branco
        .PHP_EOL;
        return $linhaN;
    }
    
    public function trailerLote($fws_, $debug = false){
				 
        $linhaN = $this->v_banco//-- Código do banco
        . lpad($this->v_cod_lote, 4, '0') 	//-- Código do lote
        . '5' 			//-- Tipo de registro
        . lpad(' ', 9, ' ')	//-- Preenchido com branco
        . lpad($this->total_registro_lote, 6, '0') //-- Quantidade de registros no lote
        . lpad($this->total_titulos_lote, 6, '0')  //-- Quantidade de títulos em cobrança simples
        . lpad(($this->valor_total_registro_lote * 100), 17, '0') //-- Valor total dos títulos em carteira de cobrança simples
        . lpad('0', 6, '0')	//-- Preenchido com zeros
        . lpad('0', 17, '0')	//-- Preenchido com zeros
        . lpad('0', 6, '0')	//-- Preenchido com zeros
        . lpad('0', 17, '0')	//-- Preenchido com zeros
        . lpad(' ', 31, ' ')	//-- Preenchido com branco
        . lpad(' ', 117, ' ')	//-- Preenchido com branco
        .PHP_EOL;
        return $linhaN;
    }
    
    public function trailerArquivo($fws_, $debug = false){
        
//	v_nsl := v_nsl + 1;
        $linhaN = ''                
        . $this->v_banco        //-- Código do banco
        . '9999'                //-- Código do lote
        . '9'                   //-- Tipo de registro
        . lpad(' ', 9, ' ')     //-- Preenchido com branco
        . lpad(1, 6, '0')       //-- Quantidade de lotes
        . lpad($this->total_registro_arquivo, 6, '0') //-- Quantidade de registros
        . lpad(' ', 6, ' ')	//-- Preenchido com branco
        . lpad(' ', 205, ' ')	//-- Preenchido com branco
        .PHP_EOL;
        return $linhaN;
    }           

}

?>

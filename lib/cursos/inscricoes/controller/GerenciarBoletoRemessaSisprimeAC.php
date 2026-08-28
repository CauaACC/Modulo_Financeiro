<?php

class GerenciarBoletoRemessaSisprimeAC {

    public function gerarArquivoRemessa($fws_, $debug = false) {
   
        try {
            $objArquivoRemessa = new BoletoRemessa();
            $this->num_seq_arquivo      = $objArquivoRemessa->getLastId(); // ultima remessa enviada
            $this->num_seq_nome_arquivo = $objArquivoRemessa->getLastArquivoId(); // ultima remessa enviada
            $nomeArquivoLog = "CB" . Date('d') . Date('m') . lpad($this->num_seq_nome_arquivo, 2, 0) . ".REM";
            if (!file_exists(WB_PATH_LOG_REMESSA)) {
                mkdir(WB_PATH_LOG_REMESSA, 0755);
            }
            $fileLog = WB_PATH_LOG_REMESSA . "/" . $nomeArquivoLog;            

            $this->total_titulos_lote = 0; // ultima remessa enviada
            $this->situacao_arquivo = 'REMESSA-PRODUCAO';
            $this->nome_empresa = "FUNDACAO DE APOIO AO DESENVOLVIMENTO TECNOLOGICO DO HOSPITAL UNIV";
            $this->tipo_cpf_cnpj = '2'; //1 para cpf 2 para cnpj
            $this->cpf_cnpj = '03133607000138';
            $this->cod_banco = '084';
            $this->nome_banco = 'SISPRIME';

            // Dados bancários
//            $this->v_banco = '237';
//            $this->v_agencia = '7509';
//            $this->v_dv_agencia = '4';
//            $this->v_cod_convenio = '553085';
//            $this->v_dv_convenio = '7';
//            $this->carteira = '09';
            // Dados bancários
            $this->v_banco = '084';
            $this->v_agencia = '0001';
            $this->v_dv_agencia = '9';
            $this->v_cod_convenio = '55275';
            $this->v_dv_convenio = '5';
            $this->carteira = '09';

//        $this->num_seq_arquivo = $this->num_seq_arquivo + 1;
            $this->total_titulos_lote = $this->total_titulos_lote + 1;
            $textoArquivo .= $this->headerArquivo($fws_, $debug);

            //inicio loop
            $objBoleto = new Boleto();
            $objBoleto->carregarListaBoletos($debug);
            foreach ($objBoleto->getListaBoletos() as $objL) {
                /*Não registrar boletos já pagos e boletos que já foram enviados em remessas anteriores or boletos desativados*/
                if ($objL->getFlgRemessa() == 0 AND $objL->getFlgPagamento() != 1 and ($objL->getStatus()!='DESATIVADA' and $objL->getStatus()!='CANCELADO')){
                    
                    /*@todo acrescentar filtro para não incluir na remessa boleto com data de processamento maior que a data de vencimento*/
                    
                    $fws_['numero_documento'] = $objL->getNumeroDocumento();
                    
                    if ($objL->getNossoNumero() == '') {
                        $dadosboleto["nosso_numero"] = lpad($objL->getCodBoleto(), 7, '0');
                        $nnum = formata_numero($this->carteira, 2, 0) . formata_numero($dadosboleto["nosso_numero"], 10, 0);
                        $fws_['nosso_numero'] = substr($nnum, 2);
                        $fws_['dv_nosso_numero'] = digitoVerificador_nossonumero($nnum);
                        $objL->setNossoNumero($fws_['nosso_numero'] . "-" . $fws_['dv_nosso_numero']);
                    } else {
                        //                $fws_['nosso_numero']       = $objL->getCodBoleto();                
                        $arNossoNumero = explode('-', $objL->getNossoNumero());
                        if(count($arNossoNumero)==2){
                            $fws_['nosso_numero'] = $arNossoNumero[0];
                            $fws_['dv_nosso_numero'] = $arNossoNumero[1];
                        }else{
                            $dadosboleto["nosso_numero"] = lpad($objL->getCodBoleto(), 7, '0');
                            $nnum = formata_numero($this->carteira, 2, 0) . formata_numero($dadosboleto["nosso_numero"], 10, 0);
                            $fws_['nosso_numero'] = substr($nnum, 2);
                            $fws_['dv_nosso_numero'] = digitoVerificador_nossonumero($nnum);
                            $objL->setNossoNumero($fws_['nosso_numero'] . "-" . $fws_['dv_nosso_numero']);
                        }
                    }

//                    $data = explode("/", $objL->getDataVencimento());
//                    $dia = data[0]+1;
//                    $mes = data[1];
//                    $ano = data[2];
//                    $objL->setDataVencimento($dia.'/'.$mes.'/'.$ano);
 
                    $fws_['data_vencimento'] = formatDateRemessa($objL->getDataVencimento());
                    $fws_['valor_boleto'] = removerPontos($objL->getValorBoleto());
                    $fws_['valor_bonificacao'] = '0';

//                    if($fws_['valor_boleto']==30000){
//                        $fws_['valor_desconto'] = removerPontos($objL->getValorDesconto());
//                        if ($fws_['valor_desconto'] == "") {
//                            $fws_['valor_desconto'] = '15000';
//                        }
//
//                        $fws_['data_desconto'] = formatDateRemessa($objL->getDataDesconto());
//                        if ($fws_['data_desconto'] == "") {
//                            $fws_['data_desconto'] = formatDateRemessa('15/03/2020');
//                        }
//                    }
                    
                    $fws_['valor_desconto'] = removerPontos($objL->getValorDesconto());
                    $fws_['data_desconto']  = formatDateRemessa($objL->getDataDesconto());
                    if($fws_['valor_desconto']==""){
                        $fws_['valor_desconto'] = removerPontos($objL->getValorDesconto1());
                        $fws_['data_desconto']  = formatDateRemessa($objL->getDataDesconto1());
                    }
                    $fws_['valor_desconto_2'] = removerPontos($objL->getValorDesconto2());
                    $fws_['data_desconto_2']  = formatDateRemessa($objL->getDataDesconto2());
                    $fws_['valor_desconto_3'] = removerPontos($objL->getValorDesconto3());
                    $fws_['data_desconto_3']  = formatDateRemessa($objL->getDataDesconto3());
                    
                    $fws_['data_processamento'] = formatDateRemessa($objL->getDataProcessamento());
                    $fws_['data_emissao'] = formatDateRemessa($objL->getDataEmissao());
                    $fws_['data_juro_mora'] = '0'; //vencimento +1
                    $fws_['data_vencto_multa'] = '';
                                
                    /**@todo parametricar aplicação de juros */
                    $fws_['aplicar_multa']    = 2; // 0 para não aplicar 2 para aplicar
                    $fws_['percentual_multa'] = 200; // percentual de multa, para 1% informar 100
                    $fws_['valor_mora'] = 17; // valor_mora=((1/10)/30)*valor_boleto, valor mora, a ser aplicado diariamente
                    
                    $fws_['pagador_tipo_documento'] = $objL->getTipoDocumento(); //1->cpf ou 2->cnpj
                    if($fws_['pagador_tipo_documento']==2){
                        $fws_['pagador_cpf_cnpj'] = formatCnpjInsert($objL->getCpfCnpj());
                    }else{
                        $fws_['pagador_cpf_cnpj'] = formatCpfInsert($objL->getCpfCnpj());
                    }
                    
                    $fws_['pagador_nome'] = removerAcentos($objL->getSacado());
                    $fws_['pagador_endereco'] = removerAcentos($objL->getEndereco1());
                    $fws_['pagador_bairro'] = removerAcentos($objL->getBairro());
                    $fws_['pagador_cep'] = substr(removerHifen($objL->getCep()),0,8); //completo sem hifen
                    $fws_['pagador_cidade'] = removerAcentos($objL->getCidade());
                    $fws_['pagador_uf'] = $objL->getUf();

                    $this->total_titulos_lote = $this->total_titulos_lote + 1;

                    $textoArquivo .= $this->registroDetalhe($fws_, $debug);
                    /*Se houver registro mais de um desconto*/
                    if($fws_['valor_desconto_2']!=''){
                        $textoArquivo .= $this->registroMensagem($fws_, $debug);
                    }
                    
                    $objL->setFlgRemessa('1');
                    $objL->setArquivoRemessa($nomeArquivoLog);
                    $objL->setArquivoRemessaLinha($this->total_titulos_lote);
                    
                    try {
                        $objL->salvarRemessa($debug);
                    } catch (OKException $exc) {}
                }
            }
            
            $objArquivoRemessa->setQtdRegistros($this->total_titulos_lote);
            $this->total_titulos_lote = $this->total_titulos_lote + 1;
            $textoArquivo .= $this->trailerArquivo($fws_, $debug);

            if($this->total_titulos_lote<=2){
                throw new Exception("Não há boletos a serem registrados");
            }
            
            file_put_contents($fileLog, $textoArquivo);

            $objArquivoRemessa->setNomeArquivo($nomeArquivoLog);
            try{
                $objArquivoRemessa->salvar();
            } catch (OKException $exc) {
//                echo $exc->getMessage().":".$nomeArquivoLog;
                if(isset($fileLog) && file_exists($fileLog)){
                    header("Content-Type: application/force-download");
                    header("Content-Type: application/octet-stream;");
                    header("Pragma: no-cache");
                    header("Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0");
                    header("Expires: 0");
                    header("Content-Length: ".filesize($fileLog));
                    header("Content-Disposition: attachment; filename=".basename($nomeArquivoLog));
                    readfile($fileLog);
                    exit;
                }else{
                    echo "Arquivo de remessa não encontrado. (".$nomeArquivoLog.")";
                }
            }
            
        } catch (Exception $exc) {
            echo "<hr>";
            echo $exc->getMessage()."<hr>";
        }
    }

    public function headerArquivo($fws_, $debug = false){
        $linhaN = ''
        . '0'
        . '1'
        . 'REMESSA'
        . '01'
        . rpad('COBRANCA', 15, ' ')
        . lpad($this->cpf_cnpj, 20, '0')
        . rpad(substr($this->nome_empresa,0,30), 30, ' ')
        . $this->cod_banco
        . rpad($this->nome_banco,15,' ')
        . Date('dmy')
        . lpad(' ', 8, ' ')
        . 'MX'
        . lpad($this->num_seq_arquivo,7,'0')
        . lpad(' ', 277, ' ')
        . lpad($this->total_titulos_lote,6,'0')
        .PHP_EOL;
        return $linhaN;
    }
  
    public function registroDetalhe($fws_ , $debug = false){
        
        $linhaN = ''
        . '1' // Coluna 1 - Indicação do registro
        . lpad(' ',5,' ') // Colunas 2 a 6
        . lpad(' ',1,' ') // Coluna 7
        . lpad(' ',5,' ') // Colunas 8 a 12
        . lpad(' ',7,' ') // Colunas 13 a 19
        . lpad(' ',1,' ') // Coluna 20 - Total de 19 brancos até aqui
        . '0' // Coluna 21
        . '009' // Colunas 22 a 24
        . '0' // Coluna 25
        . lpad($this->v_agencia, 4, '0') // Colunas 26 a 29
        . lpad($this->v_cod_convenio, 7, '0') // Colunas 30 a 36
        . lpad($this->v_dv_convenio, 1, '0') // Coluna 37 - Dados da conta corrente até aqui
        . lpad(' ',25,' ') // Colunas 38 a 62 - Uso da empresa
        . lpad('0',3,'0') // Colunas 63 a 65 - Código do banco a ser compensado na câmara de compensação
        . $fws_['aplicar_multa'] // Coluna 66 - Multar
        . lpad($fws_['percentual_multa'], 4, '0') // Colunas 67 a 70 - Percentual de multa
        . lpad($fws_['nosso_numero'], 11,'0') // Colunas 71 a 81
        . lpad($fws_['dv_nosso_numero'], 1,'0') // Coluna 82
        . lpad($fws_['valor_bonificacao'], 10,'0') // Colunas 83 a 92
        . '2' // Coluna 93 - Cliente emite boleto
        . lpad(' ',1,' ') // Coluna 94
        . lpad(' ',10,' ') // Colunas 95 a 104
        . lpad(' ',1,' ') // Coluna 105
        . lpad('2',1,' ') // Coluna 106 - Não emitir aviso de débito automático
        . lpad(' ',2,' ') // Colunas 107 a 108
        . '01' // Colunas 109 a 110 - Operação: inclusão do título
        . lpad($fws_['numero_documento'], 10, '0') // Colunas 111 a 120
        . lpad($fws_['data_vencimento'], 6, '0') // Colunas 121 a 126
        . lpad($fws_['valor_boleto'], 13, '0') // Colunas 127 a 139
        . lpad('0', 3, '0') // Colunas 140 a 142
        . lpad('0', 5, '0') // Colunas 143 a 147
        . '99' // Colunas 148 a 149
        . 'N' // Coluna 150
        . lpad($fws_['data_emissao'],6, '0') // Colunas 151 a 156
        . '00' // Colunas 157 a 158
        . '00' // Colunas 159 a 160
        . lpad($fws_['valor_mora'], 13, '0') // Colunas 161 a 173 valor mora
        . lpad($fws_['data_desconto'], 6, '0') // Colunas 174 a 179
        . lpad($fws_['valor_desconto'], 13, '0') // Colunas 180 a 192
        . lpad('0', 13, '0') // Colunas 193 a 205 - IOF
        . lpad('0', 13, '0') // Colunas 206 a 218 - Abatimento
        . lpad(substr($fws_['pagador_tipo_documento'],0,2), 2, '0') // Colunas 219 a 220
        . lpad(substr($fws_['pagador_cpf_cnpj'],0,14), 14, '0') // Colunas 221 a 234
        . rpad(substr($fws_['pagador_nome'],0,40), 40, ' ') // Colunas 235 a 274
        . rpad(substr($fws_['pagador_endereco'],0,40), 40, ' ') // Colunas 275 a 314
        . rpad(' ', 12, ' ') // Colunas 315 a 326
        . rpad($fws_['pagador_cep'], 8, '0') // Colunas 327 a 334
        . rpad(substr($fws_['pagador_bairro'],0,20), 20, ' ') // Colunas 335 a 354
        . rpad(substr($fws_['pagador_cidade'],0,38), 38, ' ') // Colunas 355 a 392
        . $fws_['pagador_uf'] // Colunas 393 a 394 - Unidade da federação
        . lpad($this->total_titulos_lote, 6, '0') // Colunas 395 a 400    
        .PHP_EOL;
        return $linhaN;
    }    
    public function registroMensagem($fws_ , $debug = false){
        
        $linhaN = ''
        . '2'
        . lpad(' ',80,' ')
        . lpad(' ',80,' ')
        . lpad(' ',80,' ')
        . lpad(' ',80,' ')
        . lpad($fws_['data_desconto_2'], 6, '0')
        . lpad($fws_['valor_desconto_2'], 13, '0')
        . lpad($fws_['data_desconto_3'], 6, '0')
        . lpad($fws_['valor_desconto_3'], 13, '0')
        . lpad(' ',7,' ')
        . '009'
        . lpad($this->v_agencia, 5, '0')
        . lpad($this->v_cod_convenio, 7, '0')
        . lpad($this->v_dv_convenio, 1, '0')
        . lpad($fws_['nosso_numero'], 11,'0')
        . lpad($fws_['dv_nosso_numero'], 1,'0')
        . lpad($this->total_titulos_lote, 6, '0')
        .PHP_EOL;
        return $linhaN;
    }    
    
    public function trailerArquivo($fws_, $debug = false){
        
//	v_nsl := v_nsl + 1;
        $linhaN = ''                
        . '9'
        . lpad(' ', 393, ' ')
        . lpad($this->total_titulos_lote, 6, '0')
        .PHP_EOL;
        return $linhaN;
    }           

}

function digitoVerificador_nossonumero($numero) {
	$resto2 = modulo_11($numero, 7, 1);
     $digito = 11 - $resto2;
     if ($digito == 10) {
        $dv = "P";
     } elseif($digito == 11) {
     	$dv = 0;
	} else {
        $dv = $digito;
     	}
	 return $dv;
}


function digitoVerificador_barra($numero) {
	$resto2 = modulo_11($numero, 9, 1);
     if ($resto2 == 0 || $resto2 == 1 || $resto2 == 10) {
        $dv = 1;
     } else {
	 	$dv = 11 - $resto2;
     }
	 return $dv;
}


// FUN��ES
// Algumas foram retiradas do Projeto PhpBoleto e modificadas para atender as particularidades de cada banco

function formata_numero($numero,$loop,$insert,$tipo = "geral") {
	if ($tipo == "geral") {
		$numero = str_replace(",","",$numero);
		while(strlen($numero)<$loop){
			$numero = $insert . $numero;
		}
	}
	if ($tipo == "valor") {
		/*
		retira as virgulas
		formata o numero
		preenche com zeros
		*/
		$numero = str_replace(",","",$numero);
		while(strlen($numero)<$loop){
			$numero = $insert . $numero;
		}
	}
	if ($tipo == "convenio") {
		while(strlen($numero)<$loop){
			$numero = $numero . $insert;
		}
	}
	return $numero;
}


function fbarcode($valor){

$fino = 1 ;
$largo = 3 ;
$altura = 50 ;

  $barcodes[0] = "00110" ;
  $barcodes[1] = "10001" ;
  $barcodes[2] = "01001" ;
  $barcodes[3] = "11000" ;
  $barcodes[4] = "00101" ;
  $barcodes[5] = "10100" ;
  $barcodes[6] = "01100" ;
  $barcodes[7] = "00011" ;
  $barcodes[8] = "10010" ;
  $barcodes[9] = "01010" ;
  for($f1=9;$f1>=0;$f1--){ 
    for($f2=9;$f2>=0;$f2--){  
      $f = ($f1 * 10) + $f2 ;
      $texto = "" ;
      for($i=1;$i<6;$i++){ 
        $texto .=  substr($barcodes[$f1],($i-1),1) . substr($barcodes[$f2],($i-1),1);
      }
      $barcodes[$f] = $texto;
    }
  }


//Desenho da barra


//Guarda inicial
?><img src=<?php echo WB_URL_IMG_BOLETO?>/p.png width=<?php echo $fino?> height=<?php echo $altura?> border=0><img 
src=<?php echo WB_URL_IMG_BOLETO?>/b.png width=<?php echo $fino?> height=<?php echo $altura?> border=0><img 
src=<?php echo WB_URL_IMG_BOLETO?>/p.png width=<?php echo $fino?> height=<?php echo $altura?> border=0><img 
src=<?php echo WB_URL_IMG_BOLETO?>/b.png width=<?php echo $fino?> height=<?php echo $altura?> border=0><img 
<?php
$texto = $valor ;
if((strlen($texto) % 2) <> 0){
	$texto = "0" . $texto;
}

// Draw dos dados
while (strlen($texto) > 0) {
  $i = round(esquerda($texto,2));
  $texto = direita($texto,strlen($texto)-2);
  $f = $barcodes[$i];
  for($i=1;$i<11;$i+=2){
    if (substr($f,($i-1),1) == "0") {
      $f1 = $fino ;
    }else{
      $f1 = $largo ;
    }
?>
    src=<?php echo WB_URL_IMG_BOLETO?>/p.png width=<?php echo $f1?> height=<?php echo $altura?> border=0><img 
<?php
    if (substr($f,$i,1) == "0") {
      $f2 = $fino ;
    }else{
      $f2 = $largo ;
    }
?>
    src=<?php echo WB_URL_IMG_BOLETO?>/b.png width=<?php echo $f2?> height=<?php echo $altura?> border=0><img 
<?php
  }
}

// Draw guarda final
?>
src=<?php echo WB_URL_IMG_BOLETO?>/p.png width=<?php echo $largo?> height=<?php echo $altura?> border=0><img 
src=<?php echo WB_URL_IMG_BOLETO?>/b.png width=<?php echo $fino?> height=<?php echo $altura?> border=0><img 
src=<?php echo WB_URL_IMG_BOLETO?>/p.png width=<?php echo 1?> height=<?php echo $altura?> border=0> 
  <?php
} //Fim da fun��o

function esquerda($entra,$comp){
	return substr($entra,0,$comp);
}

function direita($entra,$comp){
	return substr($entra,strlen($entra)-$comp,$comp);
}

// function fator_vencimento($data) {
// 	$data = explode("/",$data);
// 	$ano = $data[2];
// 	$mes = $data[1];
// 	$dia = $data[0];
//     return(abs((_dateToDays("1997","10","07")) - (_dateToDays($ano, $mes, $dia))));
// }

function fator_vencimento($data) {
    $data = explode("/", $data);
    $ano = $data[2];
    $mes = $data[1];
    $dia = $data[0];

    // Data base para 22/02/2025
    $data_base = strtotime("2025-02-22");
    $data_vencimento = strtotime("$ano-$mes-$dia");

    if ($data_vencimento < $data_base) {
        // Caso a data seja antes de 22/02/2025, usa o cálculo antigo
        return abs((_dateToDays("1997","10","07")) - (_dateToDays($ano, $mes, $dia)));
    } else {
        // Caso a data seja após 22/02/2025, o fator começa em 1000
        $dias_apos_base = floor(($data_vencimento - $data_base) / (60 * 60 * 24));
        return 1000 + $dias_apos_base;
    }
}


function _dateToDays($year,$month,$day) {
    $century = substr($year, 0, 2);
    $year = substr($year, 2, 2);
    if ($month > 2) {
        $month -= 3;
    } else {
        $month += 9;
        if ($year) {
            $year--;
        } else {
            $year = 99;
            $century --;
        }
    }
    return ( floor((  146097 * $century)    /  4 ) +
            floor(( 1461 * $year)        /  4 ) +
            floor(( 153 * $month +  2) /  5 ) +
                $day +  1721119);
}

function modulo_10($num) { 
		$numtotal10 = 0;
        $fator = 2;

        // Separacao dos numeros
        for ($i = strlen($num); $i > 0; $i--) {
            // pega cada numero isoladamente
            $numeros[$i] = substr($num,$i-1,1);
            // Efetua multiplicacao do numero pelo (falor 10)
            // 2002-07-07 01:33:34 Macete para adequar ao Mod10 do Ita�
            $temp = $numeros[$i] * $fator; 
            $temp0=0;
            foreach (preg_split('//',$temp,-1,PREG_SPLIT_NO_EMPTY) as $k=>$v){ $temp0+=$v; }
            $parcial10[$i] = $temp0; //$numeros[$i] * $fator;
            // monta sequencia para soma dos digitos no (modulo 10)
            $numtotal10 += $parcial10[$i];
            if ($fator == 2) {
                $fator = 1;
            } else {
                $fator = 2; // intercala fator de multiplicacao (modulo 10)
            }
        }
		
        // v�rias linhas removidas, vide fun��o original
        // Calculo do modulo 10
        $resto = $numtotal10 % 10;
        $digito = 10 - $resto;
        if ($resto == 0) {
            $digito = 0;
        }
		
        return $digito;
		
}

function modulo_11($num, $base=9, $r=0)  {
    /**
     *   Autor:
     *           Pablo Costa <pablo@users.sourceforge.net>
     *
     *   Fun��o:
     *    Calculo do Modulo 11 para geracao do digito verificador 
     *    de boletos bancarios conforme documentos obtidos 
     *    da Febraban - www.febraban.org.br 
     *
     *   Entrada:
     *     $num: string num�rica para a qual se deseja calcularo digito verificador;
     *     $base: valor maximo de multiplicacao [2-$base]
     *     $r: quando especificado um devolve somente o resto
     *
     *   Sa�da:
     *     Retorna o Digito verificador.
     *
     *   Observa��es:
     *     - Script desenvolvido sem nenhum reaproveitamento de c�digo pr� existente.
     *     - Assume-se que a verifica��o do formato das vari�veis de entrada � feita antes da execu��o deste script.
     */                                        

    $soma = 0;
    $fator = 2;

    /* Separacao dos numeros */
    for ($i = strlen($num); $i > 0; $i--) {
        // pega cada numero isoladamente
        $numeros[$i] = substr($num,$i-1,1);
        // Efetua multiplicacao do numero pelo falor
        $parcial[$i] = $numeros[$i] * $fator;
        // Soma dos digitos
        $soma += $parcial[$i];
        if ($fator == $base) {
            // restaura fator de multiplicacao para 2 
            $fator = 1;
        }
        $fator++;
    }

    /* Calculo do modulo 11 */
    if ($r == 0) {
        $soma *= 10;
        $digito = $soma % 11;
        if ($digito == 10) {
            $digito = 0;
        }
        return $digito;
    } elseif ($r == 1){
        $resto = $soma % 11;
        return $resto;
    }
}

function monta_linha_digitavel($codigo) {

	// 01-03    -> C�digo do banco sem o digito
	// 04-04    -> C�digo da Moeda (9-Real)
	// 05-05    -> D�gito verificador do c�digo de barras
	// 06-09    -> Fator de vencimento
	// 10-19    -> Valor Nominal do T�tulo
	// 20-44    -> Campo Livre (Abaixo)
	
	// 20-23    -> C�digo da Agencia (sem d�gito)
	// 24-05    -> N�mero da Carteira
	// 26-36    -> Nosso N�mero (sem d�gito)
	// 37-43    -> Conta do Cedente (sem d�gito)
	// 44-44    -> Zero (Fixo)
        

        // 1. Campo - composto pelo c�digo do banco, c�digo da mo�da, as cinco primeiras posi��es
        // do campo livre e DV (modulo10) deste campo
        
        $p1 = substr($codigo, 0, 4);							// Numero do banco + Carteira
        $p2 = substr($codigo, 19, 5);						// 5 primeiras posi��es do campo livre
        $p3 = modulo_10("$p1$p2");						// Digito do campo 1
        $p4 = "$p1$p2$p3";								// Uni�o
        $campo1 = substr($p4, 0, 5).'.'.substr($p4, 5);

        // 2. Campo - composto pelas posi�oes 6 a 15 do campo livre
        // e livre e DV (modulo10) deste campo
        $p1 = substr($codigo, 24, 10);						//Posi��es de 6 a 15 do campo livre
        $p2 = modulo_10($p1);								//Digito do campo 2	
        $p3 = "$p1$p2";
        $campo2 = substr($p3, 0, 5).'.'.substr($p3, 5);

        // 3. Campo composto pelas posicoes 16 a 25 do campo livre
        // e livre e DV (modulo10) deste campo
        $p1 = substr($codigo, 34, 10);						//Posi��es de 16 a 25 do campo livre
        $p2 = modulo_10($p1);								//Digito do Campo 3
        $p3 = "$p1$p2";
        $campo3 = substr($p3, 0, 5).'.'.substr($p3, 5);

        // 4. Campo - digito verificador do codigo de barras
        $campo4 = substr($codigo, 4, 1);

        // 5. Campo composto pelo fator vencimento e valor nominal do documento, sem
        // indicacao de zeros a esquerda e sem edicao (sem ponto e virgula). Quando se
        // tratar de valor zerado, a representacao deve ser 000 (tres zeros).
		$p1 = substr($codigo, 5, 4);
		$p2 = substr($codigo, 9, 10);
		$campo5 = "$p1$p2";

        return "$campo1 $campo2 $campo3 $campo4 $campo5"; 
}

function geraCodigoBanco($numero) {
    $parte1 = substr($numero, 0, 3);
    $parte2 = modulo_11($parte1);
    return $parte1 . "-" . $parte2;
}


<?php

class GerenciarBoletoCaixaParaEmpresaAC {

    public function btnBoleto($fws_, $debug = false) {
        ?>
        <form action="<?php echo WB_URL_COMMAND?>" method="post" name="pagamento" target="blank"  onsubmit="return criticarGerarBoleto()">
            <input type="hidden" name="c" value="GerenciarBoleto">
            <input type="hidden" name="m" value="htmlBoleto">
            <input type="hidden" name="nomeBanco"  value="CAIXAParaEmpresa">
            <input type="hidden" name="cod_evento" value="<?php echo COD_EVENTO?>">
            <br>Nome Empresa:       <input type="text" size="50" name="nome_inscrito" value="<?php echo $fws_['nome_inscrito']?>">
            <br>Código da Empresa:  <input type="text" size="50" name='cod_inscrito'  value="<?php echo $fws_['cod_inscrito']?>">
            <br>Cpf/Cnpj:           <input type="text" size="50" name="cpf"           value="<?php echo $fws_['cpf']?>">
            <br>Endereço:           <input type="text" size="50" name="endereco"      value="<?php echo $fws_['endereco']?>">
            <br>Número:             <input type="text" size="50" name="numero"        value="<?php echo $fws_['numero']?>">
            <br>Complemento:        <input type="text" size="50" name="complemento"   value="<?php echo $fws_['complemento']?>">
            <br>Cidade:             <input type="text" size="50" name="cidade"        value="<?php echo $fws_['cidade']?>">
            <br>Estado:             <input type="text" size="50" name="estado"        value="<?php echo $fws_['estado']?>">
            <br>Cep:                <input type="text" size="50" name="cep"           value="<?php echo $fws_['cep']?>">
            <br><!--<input type="text" size="50" name="cod_boleto"    value="<?php echo $fws_['cod_boleto']?>">-->
            <br>Valor:              <input type="text" size="50" name="valor_cobrado" value="<?php echo $fws_['valor_cobrado']?>">
            <br>Data Vencimento:    <input type="text" size="50" name="data_vencimento" value="<?php echo $fws_['data_vencimento']?>">
            <br>Mensagem Corpo Boleto: <br><textarea cols="50" rows="5"  name="msg_boleto"><?php echo $fws_['msg_boleto']?></textarea>
            <button class="btn btn-xs btn-primary" type="submit">Gerar Boleto Bancário</button><br>
        </form>
        <script language='javascript'>
            <!--
            function criticarGerarBoleto(){
                if (confirm('Após gerar boleto bancário, não será permitido alterar opção de minicurso ou de refeição, deseja continuar?')) {
                    return true;
                }
                return false;
            }
        </script>
        <?php
    }
   
    public function htmlBoleto($fws_, $debug = false) {
        
        $dadosboleto["numero_documento"]    = "01". numberPad($fws_['cod_inscrito'], 6)."01"; // Num do pedido ou do documento
        $objBoleto = new Boleto();
        $objBoleto->setCodInscrito($fws_['cod_inscrito']);
        $objBoleto->setNumeroDocumento($dadosboleto["numero_documento"]);
        
//        if ($_SERVER['REMOTE_ADDR'] == '189.90.70.56') {
        if($objBoleto->carregarUltimoBoletoByIdInscrito($debug)){
            /*Se a data de vencimento do ultimo boleto cadastrado for menor que hoje, gerar um novo boleto*/
            if(formatDateCompare($objBoleto->getDataVencimento())<date('Ymd')){
                /*definir nova data de vencimento*/
                /*definir novo numero do documento*/
                $objBoleto->setCodBoleto('');
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
        if($objBoleto->getCodBoleto()==''){

            // DADOS DO BOLETO PARA O SEU CLIENTE
            $taxa_boleto = 0;//2.95;
            
            $data_venc = $fws_['data_vencimento']; //date("d/m/Y", time() + ($dias_de_prazo_para_pagamento * 86400));  // Prazo de X dias  OU  informe data: "13/04/2006"  OU  informe "" se Contra Apresentacao;
            $fws_['data_vencimento'] = date("d/m/Y", time() + ($FWS_Dias_Prazo_Pagamento * 86400));

            $valor_cobrado = formatMoneyView($fws_['valor_cobrado']);  //"5,00"; // Valor - REGRA: Sem pontos na milhar e tanto faz com "." ou "," ou com 1 ou 2 ou sem casa decimal
            $valor_cobrado = str_replace(",", ".",$valor_cobrado);
//            $valor_cobrado = '1,00';
            $valor_boleto=$valor_cobrado+$taxa_boleto;
            $valor_boleto = formatMoneyBoleto($valor_boleto);

   //        $dadosboleto["numero_documento"]    = "01". numberPad($fws_['cod_inscrito'], 6)."01"; // Num do pedido ou do documento

            $dadosboleto["data_vencimento"]     = $data_venc;     // Data de Vencimento do Boleto - REGRA: Formato DD/MM/AAAA
            $dadosboleto["data_documento"]      = date("d/m/Y");  // Data de emiss�o do Boleto
            $dadosboleto["data_emissao"]        = date("d/m/Y");  // Data de processamento do boleto (opcional)
            $dadosboleto["data_processamento"]  = date("d/m/Y");  // Data de processamento do boleto (opcional)
            $dadosboleto["valor_boleto"]        = $valor_boleto;  // Valor do Boleto - REGRA: Com v�rgula e sempre com duas casas depois da virgula
            $dadosboleto["valor_documento"]     = $valor_boleto;  // Valor do Boleto - REGRA: Com v�rgula e sempre com duas casas depois da virgula

            // DADOS DO SEU CLIENTE
            $dadosboleto["sacado"]    = $fws_['nome_inscrito'];   
            $dadosboleto["uf"]        = $fws_['estado'];  
            $dadosboleto["cpf_cnpj"]  = $fws_['cpf'];  
            if(strlen($fws_['cpf'])==11){
                $dadosboleto["tipo_documento"]  = '1';  
            }else{
                $dadosboleto["tipo_documento"]  = '2';  
            }
            $dadosboleto["endereco1"] = $fws_["endereco"] . ", ". $fws_["numero"]. ", ". $fws_["complemento"]; //"Av. Madre Leonia Milito 2000";
            $dadosboleto["endereco2"] = $fws_["cidade"]. "-". $fws_["estado"]. " CEP: ". $fws_["cep"]; //"Cidade - Estado -  CEP: 00000-000";

            $objInscrito = new Inscrito();
            $objInscrito->setCodInscrito($fws_['cod_inscrito']);
            $objInscrito->getInscritoById();
            $objInscrito->carregarOjbPerfil($debug);
            $objPerfil = $objInscrito->getObjPerfil();
            $dadosboleto["bairro"] = $objInscrito->getBairro();
           
            // INFORMACOES PARA O CLIENTE
            $dadosboleto["demonstrativo1"] = ABREV_EVENTO." - inscrição número: ".$objInscrito->getCodInscrito()." | Categoria: ".$objPerfil->getTitulo();
            if($objInscrito->getCodOficina() or $objInscrito->getCodOficina()==98){
                $dadosboleto["demonstrativo2"] = "Minicurso: ".$objInscrito->getCodOficina();
            }else{
                $dadosboleto["demonstrativo2"] = "Não optou por minicurso.";
            }
            if($objInscrito->getInfoAlmoco()){
                $dadosboleto["demonstrativo3"] = "Refeições: ".$objInscrito->getInfoAlmoco();
            }else{
                $dadosboleto["demonstrativo3"] = "Não optou por refeição.";
            }
            //$dadosboleto["demonstrativo3"] = $objPerfil->getInvestimento();

            // INSTRU��ES PARA O CAIXA
            //$dadosboleto["instrucoes1"] = "<b>BOLETO DE TESTE, NÃO RECEBER</b>";
            //$dadosboleto["instrucoes1"] = "- Sr. Caixa, não receber após o vencimento";
            $dadosboleto["instrucoes1"] = "- Não receber após o vencimento";
            $dadosboleto["instrucoes2"] = "- Emitido pelo sistema de inscrição do 9-CBMP - http://www.sbmp.com.br/9congresso";
            
            $objBoleto->setAll($fws_);
            $objBoleto->setAll($dadosboleto);
            try {
                $objBoleto->salvar();
            } catch (OKException $exc) {
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
        
        $data_venc = $objBoleto->getDataVencimento();     // Data de Vencimento do Boleto - REGRA: Formato DD/MM/AAAA
        $dadosboleto["data_vencimento"]     = $objBoleto->getDataVencimento();      // Data de Vencimento do Boleto - REGRA: Formato DD/MM/AAAA
        $dadosboleto["data_documento"]      = $objBoleto->getDataDocumento();       // Data de emiss�o do Boleto
        $dadosboleto["data_emissao"]        = $objBoleto->getDataEmissao();         // Data de processamento do boleto (opcional)
        $dadosboleto["data_processamento"]  = $objBoleto->getDataProcessamento();   // Data de processamento do boleto (opcional)
        $dadosboleto["valor_boleto"]        = formatMoneyBoleto($objBoleto->getValorBoleto());     // Valor do Boleto - REGRA: Com v�rgula e sempre com duas casas depois da virgula
        $dadosboleto["valor_documento"]     = formatMoneyBoleto($objBoleto->getValorDocumento());  // Valor do Boleto - REGRA: Com v�rgula e sempre com duas casas depois da virgula

        // DADOS DO SEU CLIENTE
        $dadosboleto["sacado"]    = $objBoleto->getSacado();   
        $dadosboleto["uf"]        = $objBoleto->getUf();
        $dadosboleto["cpf"]       = $objBoleto->getCpfCnpj();   
        $dadosboleto["endereco1"] = $objBoleto->getEndereco1(); //"Av. Madre Leonia Milito 2000";
        $dadosboleto["endereco2"] = $objBoleto->getEndereco2(); //"Cidade - Estado -  CEP: 00000-000";

        // INFORMACOES PARA O CLIENTE
        $dadosboleto["demonstrativo1"] = $objBoleto->getDemonstrativo1();
        $dadosboleto["demonstrativo2"] = $objBoleto->getDemonstrativo2();
        $dadosboleto["demonstrativo3"] = $objBoleto->getDemonstrativo3();

        // INSTRU��ES PARA O CAIXA
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
        $dadosboleto["nosso_numero3"] = "01".lpad($objBoleto->getCodBoleto(), 7,'0');

        $dadosboleto["inicio_nosso_numero"] = "14";           // 24 - Padrão da Caixa Economica Federal
        //$dadosboleto["nosso_numero"] = numberPad($objBoleto->getCodBoleto(), 9); //"00000001";     // Nosso numero sem o DV - REGRA: Máximo de 8 caracteres!
        $dadosboleto["nosso_numero"] = $dadosboleto["nosso_numero3"];

        // DADOS OPCIONAIS DE ACORDO COM O BANCO OU CLIENTE
        $dadosboleto["quantidade"]     = "";
        $dadosboleto["valor_unitario"] = "";
        $dadosboleto["aceite"]         = "N";		
        $dadosboleto["especie"]        = "R$";
        $dadosboleto["especie_doc"]    = "CH";
        // ---------------------- DADOS FIXOS DE CONFIGURA��O DO SEU BOLETO --------------- //
        if($_SERVER['REMOTE_ADDR']=='189.90.70.566'){
        // DADOS DA SUA CONTA - CEF - ZAKA
            $dadosboleto["agencia"]  = "3076"; // Num da agencia, sem digito
            $dadosboleto["conta"]    = "3288";   // Num da conta, sem digito
            $dadosboleto["conta_dv"] = "4";   // Digito do Num da conta
            $dadosboleto["conta_cedente"] = "841993"; // Código Cedente do Cliente, com 6 digitos (Somente N�meros)

            $dadosboleto["identificacao"] = "OPCTEC";
            $dadosboleto["cpf_cnpj"]      = "01603004955";
            $dadosboleto["endereco"]      = "Av. Madre Leonia Milito, 200 Gleba Palhano";
            $dadosboleto["cidade_uf"]     = "Londrina / Paraná";
            $dadosboleto["cedente"]       = "OPCTEC";
        }  else {
        // DADOS DA SUA CONTA - CEF - CBMP
            $dadosboleto["agencia"]  = "3178"; // Num da agencia, sem digito
            $dadosboleto["conta"]    = "262";   // Num da conta, sem digito
            $dadosboleto["conta_dv"] = "5";   // Digito do Num da conta
            //$dadosboleto["conta_cedente"] = "672746"; // Código Cedente do Cliente, com 6 digitos (Somente N�meros)
            $dadosboleto["conta_cedente"] = "847332"; // Código Cedente do Cliente, com 6 digitos (Somente N�meros)
            
            $dadosboleto["identificacao"] = "9 ºCBMP - Congresso Brasileiro de Melhoramento de Plantas";
            $dadosboleto["cpf_cnpj"]      = "03.341.530/0001-91";
            $dadosboleto["endereco"]      = "Av. Colombo, Nº 5790 Bloco J 45 - 1º - Sala 103 - UEM";
            $dadosboleto["cidade_uf"]     = "Maringá / Paraná";
            $dadosboleto["cedente"]       = "9ºCBMP Cong. Brasileiro de Melhoramento de Plantas";
        }
        // DADOS PERSONALIZADOS - CEF

        $dadosboleto["carteira"] = "CR";  // Código da Carteira: pode ser SR (Sem Registro) ou CR (Com Registro) - (Confirmar com gerente qual usar)

        // NÃO ALTERAR!
        include("lib/boleto/include/funcoes_cef_sigcb.php"); 
        include("lib/boleto/include/layout_cef.php");
        ?>
        <script>
            window.focus();
        </script>
        <?php
    }

}

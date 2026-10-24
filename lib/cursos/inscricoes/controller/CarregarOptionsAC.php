<?php

class CarregarOptionsAC {
    
    public static function macrorregioes($fws_, $debug = false) {
        ?>
        <option class='form_campos' value='1' <?php echo verifItemSelected($fws_['cod_macrorregiao'], '1') ?> >Norte</option>
        <option class='form_campos' value='2' <?php echo verifItemSelected($fws_['cod_macrorregiao'], '2') ?> >Centro-Oeste</option>
        <option class='form_campos' value='3' <?php echo verifItemSelected($fws_['cod_macrorregiao'], '3') ?> >Centro-Leste</option>
        <option class='form_campos' value='4' <?php echo verifItemSelected($fws_['cod_macrorregiao'], '4') ?> >Metropolitana Curitiba</option>
        <?php
    }
    public static function macrorregioesGeograficas($fws_, $debug = false) {
        ?>
        <option class='form_campos' value='Norte' <?php echo verifItemSelected($fws_['cod_macrorregiao'], 'Norte') ?> >Norte</option>
        <!-- <option class='form_campos' value='Nordeste' <?php echo verifItemSelected($fws_['cod_macrorregiao'], 'Nordeste') ?> >Nordeste</option> -->
        <option class='form_campos' value='Centro-Oeste' <?php echo verifItemSelected($fws_['cod_macrorregiao'], 'Centro-Oeste') ?> >Centro-Oeste</option>
        <option class='form_campos' value='Centro-Leste' <?php echo verifItemSelected($fws_['cod_macrorregiao'], 'Centro-Leste') ?> >Centro-Leste</option>
        <option class='form_campos' value='Sudeste' <?php echo verifItemSelected($fws_['cod_macrorregiao'], 'Sudeste') ?> >Sudeste</option>
        <option class='form_campos' value='Sul' <?php echo verifItemSelected($fws_['cod_macrorregiao'], 'Sul') ?> >Sul</option>
        <?php
    }

    public static function listarOpcoesEmail($fws_,$debug = false) {
        ?>
        <option value="novoModelo" <?php echo verifItemSelected($fws_['comunicado'], 'novoModelo') ?>>Novo Modelo</option>
        <!--
        <option value="certificadoLiberacao" <?php echo verifItemSelected($fws_['comunicado'], 'certificadoLiberacao') ?>>Certificado Liberação</option>
        <option value="emailIsentos" <?php echo verifItemSelected($fws_['comunicado'], 'emailIsentos') ?>>E-mail Isentos</option>
        <option value="emailHutecFormadores" <?php echo verifItemSelected($fws_['comunicado'], 'emailHutecFormadores') ?>>Comunicado Hutec Formadores</option>
        <option value="emailFormadoresConvocados" <?php echo verifItemSelected($fws_['comunicado'], 'emailFormadoresConvocados') ?>>Formadores Convocados</option>
        <option value="emailComunicadoDesligamento" <?php echo verifItemSelected($fws_['comunicado'], 'emailComunicadoDesligamento') ?>>Comunicado Desligamento - Paraná Educação</option>
        <option value="emailSolicitacaoAtualizacaoDados" <?php echo verifItemSelected($fws_['comunicado'], 'emailSolicitacaoAtualizacaoDados') ?>>Comunicado Atualização de Dados - Paraná Educação</option>
        -->
        <?php
        $objEmailTemplate = new GerenciarEmailTemplateAC();
        $objEmailTemplate->carregarOptionsEmailTemplate($fws_, $debug);
    }
    public static function regionais($fws_,$debug = false) {
        ?>
        <option class='form_campos' value='Sede Curitiba'         <?php echo verifItemSelected($fws_['regional'], 'Sede Curitiba') ?> >Sede Curitiba</option>
        <option class='form_campos' value='Seccional de Londrina' <?php echo verifItemSelected($fws_['regional'], 'Seccional de Londrina') ?> >Seccional de Londrina</option>
        <option class='form_campos' value='Seccional de Cascavel' <?php echo verifItemSelected($fws_['regional'], 'Seccional de Cascavel') ?> >Seccional de Cascavel</option>
        
        <option class='form_campos' value='Sede Curitiba - Nucress CORNÉLIO PROCÓPIO' <?php echo verifItemSelected($fws_['regional'], 'Sede Curitiba - Nucress CORNÉLIO PROCÓPIO') ?> >Sede Curitiba - Nucress CORNÉLIO PROCÓPIO</option>
        <option class='form_campos' value='Sede Curitiba - Nucress GUARAPUAVA'  <?php echo verifItemSelected($fws_['regional'], 'Sede Curitiba - Nucress GUARAPUAVA') ?>            >Sede Curitiba - Nucress GUARAPUAVA</option>
        <option class='form_campos' value='Sede Curitiba - Nucress IRATI'       <?php echo verifItemSelected($fws_['regional'], 'Sede Curitiba - Nucress IRATI') ?>                 >Sede Curitiba - Nucress IRATI</option>
        <option class='form_campos' value='Sede Curitiba - Nucress JACAREZINHO' <?php echo verifItemSelected($fws_['regional'], 'Sede Curitiba - Nucress JACAREZINHO') ?>           >Sede Curitiba - Nucress JACAREZINHO</option>
        <option class='form_campos' value='Sede Curitiba - Nucress LITORAL'     <?php echo verifItemSelected($fws_['regional'], 'Sede Curitiba - Nucress LITORAL') ?>               >Sede Curitiba - Nucress LITORAL</option>
        <option class='form_campos' value='Sede Curitiba - Nucress PONTA GROSSA'     <?php echo verifItemSelected($fws_['regional'], 'Sede Curitiba - Nucress PONTA GROSSA') ?>     >Sede Curitiba - Nucress PONTA GROSSA</option>
        <option class='form_campos' value='Sede Curitiba - Nucress TELÊMACO BORBA'   <?php echo verifItemSelected($fws_['regional'], 'Sede Curitiba - Nucress TELÊMACO BORBA') ?>   >Sede Curitiba - Nucress TELÊMACO BORBA</option>
        <option class='form_campos' value='Sede Curitiba - Nucress UNIÃO DA VITÓRIA' <?php echo verifItemSelected($fws_['regional'], 'Sede Curitiba - Nucress UNIÃO DA VITÓRIA') ?> >Sede Curitiba - Nucress UNIÃO DA VITÓRIA</option>
        
        <option class='form_campos' value='Seccional Londrina - Nucress APUCARANA' <?php echo verifItemSelected($fws_['regional'], 'Seccional Londrina - Nucress APUCARANA') ?> >Seccional Londrina - Nucress APUCARANA</option>
        <option class='form_campos' value='Seccional Londrina - Nucress CIANORTE'  <?php echo verifItemSelected($fws_['regional'], 'Seccional Londrina - Nucress CIANORTE')  ?> >Seccional Londrina - Nucress CIANORTE</option>
        <option class='form_campos' value='Seccional Londrina - Nucress IVAIPORÃ'  <?php echo verifItemSelected($fws_['regional'], 'Seccional Londrina - Nucress IVAIPORÃ')  ?> >Seccional Londrina - Nucress IVAIPORÃ</option>
        <option class='form_campos' value='Seccional Londrina - Nucress MARINGÁ'   <?php echo verifItemSelected($fws_['regional'], 'Seccional Londrina - Nucress MARINGÁ')   ?> >Seccional Londrina - Nucress MARINGÁ</option>
        <option class='form_campos' value='Seccional Londrina - Nucress PARANAVAÍ' <?php echo verifItemSelected($fws_['regional'], 'Seccional Londrina - Nucress PARANAVAÍ') ?> >Seccional Londrina - Nucress PARANAVAÍ</option>
        
        <option class='form_campos' value='Seccional Cascavel – Nucress CAMPO MOURÃO'      <?php echo verifItemSelected($fws_['regional'], 'Seccional Cascavel – Nucress CAMPO MOURÃO') ?>      >Seccional Cascavel – Nucress CAMPO MOURÃO</option>
        <option class='form_campos' value='Seccional Cascavel – Nucress FOZ DO IGUAÇU'     <?php echo verifItemSelected($fws_['regional'], 'Seccional Cascavel – Nucress FOZ DO IGUAÇU') ?>     >Seccional Cascavel – Nucress FOZ DO IGUAÇU</option>
        <option class='form_campos' value='Seccional Cascavel – Nucress FRANCISCO BELTRÃO' <?php echo verifItemSelected($fws_['regional'], 'Seccional Cascavel – Nucress FRANCISCO BELTRÃO') ?> >Seccional Cascavel – Nucress FRANCISCO BELTRÃO</option>
        <option class='form_campos' value='Seccional Cascavel – Nucress PATO BRANCO'       <?php echo verifItemSelected($fws_['regional'], 'Seccional Cascavel – Nucress PATO BRANCO')       ?> >Seccional Cascavel – Nucress PATO BRANCO</option>
        <option class='form_campos' value='Seccional Cascavel – Nucress TOLEDO'            <?php echo verifItemSelected($fws_['regional'], 'Seccional Cascavel – Nucress TOLEDO') ?>   >Seccional Cascavel – Nucress TOLEDO</option>
        <option class='form_campos' value='Seccional Cascavel – Nucress UMUARAMA'          <?php echo verifItemSelected($fws_['regional'], 'Seccional Cascavel – Nucress UMUARAMA') ?> >Seccional Cascavel – Nucress UMUARAMA</option>
        <?php
    }
    public static function raca($fws_,$debug = false) {
        $fws_['raca'] = snakeCaseParaCamelCase($fws_['raca']);
    ?>
    <option class='form_campos' value='Branca'               <?php echo verifItemSelected($fws_['raca'], 'Branca') ?> >Branca</option> 
    <option class='form_campos' value='Preta'               <?php echo verifItemSelected($fws_['raca'], 'Preta') ?> >Preta</option>
    <option class='form_campos' value='Indigena'               <?php echo verifItemSelected($fws_['raca'], 'Indigena') ?> >Indígena</option>
    <option class='form_campos' value='Parda'               <?php echo verifItemSelected($fws_['raca'], 'Parda') ?> >Parda</option>
    <option class='form_campos' value='Amarela'               <?php echo verifItemSelected($fws_['raca'], 'Amarela') ?> >Amarela</option>
    <?php    
    }
    public static function tipoInscricaoLinha($fws_, $debug = false) {
        ?>
        <option class='form_campos' value='ASSOCIADO'           <?php echo verifItemSelected($fws_['tipo_inscricao'], 'ASSOCIADO') ?> >Associado</option>
        <option class='form_campos' value='LIDER COMUNITÁRIO'   <?php echo verifItemSelected($fws_['tipo_inscricao'], 'LIDER COMUNITÁRIO') ?> >Lider Comunitário</option>
        <option class='form_campos' value='ESTUDANTE GRADUAÇÃO'     <?php echo verifItemSelected($fws_['tipo_inscricao'], 'ESTUDANTE GRADUAÇÃO') ?> >Estudante Graduação</option>
        <option class='form_campos' value='ESTUDANTE PÓS-GRADUAÇÃO' <?php echo verifItemSelected($fws_['tipo_inscricao'], 'ESTUDANTE PÓS-GRADUAÇÃO') ?> >Estudante Pós-Graduação</option>
        <option class='form_campos' value='PROFISSIONAL SAÚDE'  <?php echo verifItemSelected($fws_['tipo_inscricao'], 'PROFISSIONAL SAÚDE') ?> >Profissional da Saúde</option>
        <option class='form_campos' value='OUTROS'              <?php echo verifItemSelected($fws_['tipo_inscricao'], 'OUTROS') ?> >Outros</option>
        <?php
    }
    public static function tipoInscricao($fws_, $debug = false) {
        if ($fws_['tipo_inscricao']==''){
            $fws_['tipo_inscricao'] = 'OUTROS';
        } 
        ?>
        <br><input type='radio' required name='tipo_inscricao' value='LIDER COMUNITÁRIO'      
            <?php echo verifItemChecked($fws_['tipo_inscricao'], 'LIDER COMUNITÁRIO') ?> > Lider Comunitário
        <br><input type='radio' required name='tipo_inscricao' value='ESTUDANTE GRADUAÇÃO'    
            <?php echo verifItemChecked($fws_['tipo_inscricao'], 'ESTUDANTE GRADUAÇÃO') ?> > Estudante de Graduação
        <br><input type='radio' required name='tipo_inscricao' value='ESTUDANTE PÓS-GRADUAÇÃO' 
            <?php echo verifItemChecked($fws_['tipo_inscricao'], 'ESTUDANTE PÓS-GRADUAÇÃO') ?> > Estudante de Pós-Graduação
        <br><input type='radio' required name='tipo_inscricao' value='PROFISSIONAL SAÚDE'      
            <?php echo verifItemChecked($fws_['tipo_inscricao'], 'PROFISSIONAL SAÚDE') ?> > Profissional de Saúde
        <br><input type='radio' required name='tipo_inscricao' value='OUTROS'                  
            <?php echo verifItemChecked($fws_['tipo_inscricao'], 'OUTROS') ?>
                   > Outros
        <?php
    }

    public static function descTipoInscricao($fws_, $debug = false) {
        switch ($fws_['tipo_inscricao']) {
            case 'PROFISSIONAL-SAÚDE':
            case 'PROFISSIONAL SAÚDE':
                return "Profissional de Saúde";
                break;
            case 'ASSOCIADO':
                return "Associado";
                break;
            case 'LIDER COMUNITÁRIO':
                return "Lider Comunitário";
                break;
            case 'ESTUDANTE PÓS-GRADUAÇÃO':
                return "Estudante de Pós-Graduação";
                break;
            case 'ESTUDANTE GRADUAÇÃO':
                return "Estudante de Graduação";
                break;
            default:
                return "Outros";
                break;
        }
    }
    public static function  calcularDescontos($objInscrito, $objVoucher, $fws_, $debug = false) {
        if(COD_EVENTO < 61){
            return $fws_;
        }       
        /** carregando desconto por tipo de inscrição ou categoria */
        $porcemDescPorTipo = CarregarOptionsAC::getDescontoTipoInscricao($fws_, $debug);
        if($porcemDescPorTipo!=''){
            $valoresCalculado['porcentagem na categoria'] = $porcemDescPorTipo*$fws_['valor_cobrado'];
        }
        /** carregando desconto por cpf da inscrição */
        $objDesconto = new Desconto();
        $fws_['cpf'] = removerEspacos($fws_['cpf']);
        $fws_['cpf'] = removerHifen($fws_['cpf']);
        $fws_['cpf'] = removerPontos($fws_['cpf']);
        // $fws_['cpf'] = formatCpfCnpjView($fws_['cpf']);
        $objDesconto->setCpf($fws_['cpf']);
        $objDesconto->cerregarDescontoTotal($debug);     

        /** calculando por porcentagem cadastrada*/
        if($objDesconto->getPorcentagemTotalDesconto()!=''){
            $valoresCalculado['porcentagem no cpf'] = ($objDesconto->getPorcentagemTotalDesconto()/100)*$fws_['valor_cobrado'];
        }
        /** calculando por valor cadastrado*/
        if($objDesconto->getValorTotalDesconto()!=''){
            $valoresCalculado['valor desconto no cpf'] = $objDesconto->getValorTotalDesconto();
        }
        /** calculando por valor fixo minimo*/
        if($objDesconto->getValorFixoMinimo()!=''){
            $valoresCalculado['valor fixo no cpf'] = $fws_['valor_cobrado']-$objDesconto->getValorFixoMinimo();
        }

        /** desconto por voucher */
        if ($objInscrito->getInscritoPossuiVoucher()!=''){
            //dd($objVoucher);
            if(intval($objVoucher->getPorcentagem())!=0){
                // $valorCalculado = $objVoucher->getPorcentagem()/100;
                // echo $valorCalculado.'-';
                // $valorCobrado = $fws_['valor_cobrado'];
                // echo $valorCobrado.'-';
                // $valorCalculado = $valorCobrado*$valorCalculado; 
                // echo $valorCalculado.'-';
                $valoresCalculado['porcentagem de desconto no voucher'] = ($objVoucher->getPorcentagem()/100)*$fws_['valor_cobrado'];
                // $valoresCalculado['porcentagem de desconto no voucher'] = $valorCalculado;
                // echo $valoresCalculado['porcentagem de desconto no voucher'];
            }    
            if(intval($objVoucher->getValor())!=0){
                $valoresCalculado['valor do desconto no voucher'] = $objVoucher->getValor();
            }   
            if(intval($objVoucher->getValorInscricao())!=0){
                $valoresCalculado['valor da inscrição no voucher'] = $fws_['valor_cobrado']-$objVoucher->getValorInscricao();
            }   
        }

        /** definindo qual desconto aplicar */
        $maiorValorDesconto = max($valoresCalculado); // Encontra o Maior valor
        $indicesMaiorValor = array_keys($valoresCalculado, $maiorValorDesconto); // Encontra os índices do Maior valor
        $indiceMaiorValor = $indicesMaiorValor[0]; // Pega o primeiro índice (caso haja valores repetidos)
        
        if($debug){
            // print_r($indicesMaiorValor);
            // echo $indiceMaiorValor;
            echo "Listando todos os valores e seus índices:<br>";
            foreach ($valoresCalculado as $indice => $valor) {
                echo "Índice: $indice, Valor: $valor <br>";
            }            
            echo "O maior valor de desconto é ($maiorValorDesconto) e índice aplicado foi ($indiceMaiorValor).";
        }
        
        if ($maiorValorDesconto!='' and $maiorValorDesconto!=0){
            $fws_['APLICAR-DESCONTO'] = false;
            if($maiorValorDesconto >=$fws_['valor_cobrado']){
                $fws_['APLICAR-DESCONTO'] = true;
                $fws_['valor_cobrado'] = 0;
                $fws_['desconto_descricao'] = "Aplicando Desconto:";

            }else{
                $fws_['APLICAR-DESCONTO'] = true;
                $fws_['valor_cobrado'] = $fws_['valor_cobrado'] - $maiorValorDesconto;
                // echo "Aplicando Desconto ";
                if ($objInscrito->getCodEvento()==7) {
                    /*descrição da validade do desconto*/
                    $fws_['desconto_descricao'] = "Aplicando Desconto por: Categoria, para pagamento até dia 10/05/2022";
                }else if ($objInscrito->getCodEvento()==61) {
                    $fws_['desconto_descricao'] = "Aplicando Desconto por: ".maiusculo($indiceMaiorValor).", para pagamento até dia 19/06/2024";
                }else{
                    $fws_['desconto_descricao'] = "Aplicando Desconto por.: ".maiusculo($indiceMaiorValor);
                }
            }
        }
        return $fws_;
    }    
    // public static function calcularDescontoPorVoucher($objInscrito, $objVoucher, $fws_, $debug){
    //     if(intval($objVoucher->getPorcentagem())!=0 or intval($objVoucher->getValor())!=0 or intval($objVoucher->getValorInscricao())!=0){
    //         echo "<br><span style='font-size:18px;color:green'>Aplicando Desconto ";
    //         //echo $objVoucher->getPorcentagem().'<->'.$objVoucher->getValor().'<->'.$objVoucher->getValorInscricao().'<->';
    //         if(intval($objVoucher->getPorcentagem())!=0){
    //             echo "<br>Por voucher de: ". $objVoucher->getPorcentagem()."% de desconto";
    //             $valor = $fws_['valor_cobrado'];
    //             $valorACobrar = $valor - $valor * $objVoucher->getPorcentagem()/100.0;
    //             $fws_['valor_cobrado'] = formatMoneyInsert($valorACobrar);
    //         }else if(intval($objVoucher->getValor())!=0){
    //             echo "<br>Por voucher de: R$ ". $objVoucher->getValor()." de desconto";
    //             $valor = $fws_['valor_cobrado'];
    //             $valorACobrar = $valor - $objVoucher->getValor();
    //             $fws_['valor_cobrado'] = formatMoneyInsert($valorACobrar);
    //         }else if(intval($objVoucher->getValorInscricao())!=0){
    //             echo "<br>Por voucher de: R$ ". $objVoucher->getValorInscricao()." como valor da inscrição";
    //             $valorACobrar = $objVoucher->getValorInscricao();
    //             $fws_['valor_cobrado'] = formatMoneyInsert($valorACobrar);
    //         }
            
    //         echo "<br>Valor R$: ".intval($fws_['valor_cobrado']) . ",00</span>";
    //         try{
    //             $objPedido->setValorTotal(intval($fws_['valor_cobrado']).'.00');
    //             $objPedido->salvar();
    //         } catch (OKException $ex) {

    //         }
    //     }    
    // }

    public static function getDescontoTipoInscricao($fws_, $debug = false) {
        if (COD_EVENTO==61){
            /*verificar por data de inclusão*/
//            if('20220510' < formatDateCompare($fws_['dat_incl'])){
//                return 0;
//            }
            /*verificar por data limite para retornar descontp*/
            if('202606192359' < Date('YmdHi')){
                return 0;
            }
            switch ($fws_['tipo_inscricao']) {
                case 'PROFISSIONAL SAÚDE':
                    return 0;
                    break;
                case 'ASSOCIADO':
                    return '0.5';
                    break;
                case 'LIDER COMUNITÁRIO':
                    return '0.2';
                    break;
                case 'ESTUDANTE GRADUAÇÃO':
                    return '0.2';
                    break;
                default:
                    return 0;
                    break;
            }
        }
        if (COD_EVENTO==7){
            /*verificar por data de inclusão*/
//            if('20220510' < formatDateCompare($fws_['dat_incl'])){
//                return 0;
//            }
            /*verificar por data de geração*/
            if('202205102359' < Date('YmdHi')){
                return 0;
            }
            switch ($fws_['tipo_inscricao']) {
                case 'PROFISSIONAL SAÚDE':
                    return 0;
                    break;
                case 'ASSOCIADO':
                    return '0.5';
                    break;
                case 'LIDER COMUNITÁRIO':
                    return '0.2';
                    break;
                case 'ESTUDANTE PÓS-GRADUAÇÃO':
                    return '0.2';
                    break;
                case 'ESTUDANTE GRADUAÇÃO':
                    return '0.2';
                    break;
                default:
                    return 0;
                    break;
            }
        }
        return 0;
        /*verificar por data de inclusão*/
        if('20210510' < formatDateCompare($fws_['dat_incl'])){
            return 0;
        }
        /*verificar por data de geração*/
//        if('202105110010' < Date('YmdHi')){
//            return 0;
//        }
        switch ($fws_['tipo_inscricao']) {
            case 'PROFISSIONAL-SAÚDE':
                return 0;
                break;
            case 'ASSOCIADO':
                return '0.5';
                break;
            case 'LIDER COMUNITÁRIO':
                return '0.5';
                break;
            case 'ESTUDANTE PÓS-GRADUAÇÃO':
                return 0;
                break;
            case 'ESTUDANTE GRADUAÇÃO':
                return '0.5';
                break;
            default:
                return 0;
                break;
        }
    }
    
    public static function carregarObjDesconto($fws_, $debug = false) {
        $objDesconto = new Desconto();
        $fws_['cpf'] = removerEspacos($fws_['cpf']);
        $fws_['cpf'] = removerHifen($fws_['cpf']);
        $fws_['cpf'] = removerPontos($fws_['cpf']);
        $fws_['cpf'] = formatCpfCnpjView($fws_['cpf']);
        $objDesconto->setCpf($fws_['cpf']);
        $objDesconto->cerregarDescontoTotal($debug);
        return $objDesconto;
    }

    public static function profissoes($fws_,$debug = false) {
        if($fws_['profissao_atuacao']==''){
            $fws_['profissao_atuacao'] = 'OUTROS';
        }
        if (COD_EVENTO==4){
        ?>
        <option class='form_campos' value='PROFISSIONAL' <?php echo verifItemSelected($fws_['profissao_atuacao'], 'PROFISSIONAL') ?> >Profissional da Saúde</option>
        <option class='form_campos' value='ESTUDANTE PÓS-GRADUAÇÃO' <?php echo verifItemSelected($fws_['profissao_atuacao'], 'ESTUDANTE PÓS-GRADUAÇÃO') ?> >Estudante Pós-Graduação em Saúde</option>
        <option class='form_campos' value='ESTUDANTE GRADUAÇÃO'     <?php echo verifItemSelected($fws_['profissao_atuacao'], 'ESTUDANTE GRADUAÇÃO') ?> >Estudante Graduação em Saúde</option>
        <option class='form_campos' value='CONSELHEIRO'  <?php echo verifItemSelected($fws_['profissao_atuacao'], 'CONSELHEIRO') ?> >Conselheiro de Saúde</option>
        <option class='form_campos' value='OUTROS'       <?php echo verifItemSelected($fws_['profissao_atuacao'], 'OUTROS') ?> >Outros</option>
        <?php
        } else {
        ?>
        <option class='form_campos' value='Biologia'        <?php echo verifItemSelected($fws_['profissao_atuacao'], 'Biologia') ?> >Biologia</option>
        <option class='form_campos' value='Biomedicina'     <?php echo verifItemSelected($fws_['profissao_atuacao'], 'Biomedicina') ?> >Biomedicina</option>
        <option class='form_campos' value='Educação Física' <?php echo verifItemSelected($fws_['profissao_atuacao'], 'Educação Física') ?> >Educação Física</option>
        <option class='form_campos' value='Enfermagem'      <?php echo verifItemSelected($fws_['profissao_atuacao'], 'Enfermagem') ?> >Enfermagem</option>
        <option class='form_campos' value='Farmácia'        <?php echo verifItemSelected($fws_['profissao_atuacao'], 'Farmácia') ?> >Farmácia</option>
        <option class='form_campos' value='Fonoadiologia'   <?php echo verifItemSelected($fws_['profissao_atuacao'], 'Fonoadiologia') ?> >Fonoadiologia</option>
        <option class='form_campos' value='Fisioterapia'    <?php echo verifItemSelected($fws_['profissao_atuacao'], 'Fisioterapia') ?> >Fisioterapia</option>
        <option class='form_campos' value='Medicina'        <?php echo verifItemSelected($fws_['profissao_atuacao'], 'Medicina') ?> >Medicina</option>
        <option class='form_campos' value='Medicina Veterinária' <?php echo verifItemSelected($fws_['profissao_atuacao'], 'Medicina Veterinária') ?> >Medicina Veterinária</option>
        <option class='form_campos' value='Nutrição'        <?php echo verifItemSelected($fws_['profissao_atuacao'], 'Nutrição') ?> >Nutrição</option>
        <option class='form_campos' value='Odontologia'     <?php echo verifItemSelected($fws_['profissao_atuacao'], 'Odontologia') ?> >Odontologia</option>
        <option class='form_campos' value='Psicologia'      <?php echo verifItemSelected($fws_['profissao_atuacao'], 'Psicologia') ?> >Psicologia</option>
        <option class='form_campos' value='Serviço Social'  <?php echo verifItemSelected($fws_['profissao_atuacao'], 'Serviço Social') ?> >Serviço Social</option>
        <option class='form_campos' value='Terapia Ocupacional' <?php echo verifItemSelected($fws_['profissao_atuacao'], 'Terapia Ocupacional') ?> >Terapia Ocupacional</option>
        <option class='form_campos' value='OUTROS'          <?php echo verifItemSelected($fws_['profissao_atuacao'], 'OUTROS') ?> >Outros</option>
        <?php
        }
    }
    public static function tipoInstituicoes($fws_,$debug = false) {
        if($fws_['tipo_instituicao']==''){
           $fws_['tipo_instituicao'] = 'OUTROS'; 
        }
        ?>
        <option class='form_campos' value='SECRETARIA ESTADUAL SAÚDE'  <?php echo verifItemSelected($fws_['tipo_instituicao'], 'SECRETARIA ESTADUAL SAÚDE') ?> >Secretaria Estadual de Saúde</option>
        <option class='form_campos' value='SECRETARIA MUNICIPAL SAÚDE' <?php echo verifItemSelected($fws_['tipo_instituicao'], 'SECRETARIA MUNICIPAL SAÚDE') ?> >Secretaria Municipal de Saúde</option>
        <option class='form_campos' value='INSTITUIÇÃO DE ENSINO'      <?php echo verifItemSelected($fws_['tipo_instituicao'], 'INSTITUIÇÃO DE ENSINO') ?> >Instituição de Ensino</option>
        <option class='form_campos' value='OUTROS'                     <?php echo verifItemSelected($fws_['tipo_instituicao'], 'OUTROS') ?> >Outros</option>
        <?php
    }
    public static function sexo($fws_,$debug = false) {
        if($fws_['sexo']==''){
           $fws_['sexo'] = 'PREFIRO NAO DIZER'; 
        }
        ?>
        <option class='form_campos' value='FEMININO'  <?php echo verifItemSelected($fws_['sexo'], 'FEMININO') ?> >Feminino</option>
        <option class='form_campos' value='MASCULINO' <?php echo verifItemSelected($fws_['sexo'], 'MASCULINO') ?> >Masculino</option>
        <option class='form_campos' value='OUTROS'      <?php echo verifItemSelected($fws_['sexo'], 'OUTROS') ?> >Outros</option>
        <option class='form_campos' value='PREFIRO NAO DIZER'   <?php echo verifItemSelected($fws_['sexo'], 'PREFIRO NAO DIZER') ?> >Prefiro Não Dizer</option>
        <?php
    }
    
    public static function patrocinadoresVouchers($fws_, $debug = false) {
        try {
            $objVoucher = new Voucher();
            $objVoucher->carregarListaPatrocinadoresVoucherByIdEvento($debug);
            foreach ($objVoucher->getListaPatrocinadoresVoucherByIdEvento() as $objLP) {
                ?>
                <option 
                    <?php echo verifItemSelected($fws_['patrocinador_voucher'], $objLP->getPatrocinador()) ?>
                    value="<?php echo  $objLP->getPatrocinador() ?>"><?php echo numberPad($objLP->getTotalGerado(),3) ?> - <?php echo numberPad($objLP->getTotalUtilizado(),3) ?> - <?php echo  $objLP->getPatrocinador() ?></option>
                <?php
            }
            if($objLP->getCodEvento()==61){
                ?>
                <option value='SMS Curitiba - FEAS'>FEAS - 1</option>
                <option value='SMS Curitiba FEAS'>FEAS - 2</option>
                <?php
            }
        } catch (Exception $exc) {
            echo "<option value=''>".$exc->getMessage()."</option>";
        }
    }
    public static function listarGestoresByCodUsuario($fws_, $debug = false) {
        try {
            $obj = new Gestor();
            $obj->setCodUsuario($_SESSION['_cod_usuario']);
            $obj->carregarListaGestoresByCodUsuario($debug);
            foreach ($obj->getListaGestores() as $objL) {
                if($obj->getCountListaGestores()==1){
                    $fws_['gestor'] = $objL->getNomeGestor();
                }
                ?>
                <option 
                    <?php echo verifItemSelected($fws_['gestor'], $objL->getNomeGestor()) ?>
                    value="<?php echo $objL->getNomeGestor() ?>"><?php echo  $objL->getNomeGestor() ?></option>
                <?php
            }
        } catch (Exception $exc) {
            echo "<option value=''>".$exc->getMessage()."</option>";
        }
    }
    public static function avaliadores($fws_, $debug = false) {
        try {
            $objAvaliador = new Avaliador();
            $objAvaliador->carregarListaAvaliadoresByIdEvento($debug);
            foreach ($objAvaliador->getListaAvaliadores() as $objAval) {
                ?>
                <option 
                    <?php echo verifItemSelected($fws_['cod_avaliador'], $objAval->getCodUsuario()) ?>
                    value="<?php echo  $objAval->getCodUsuario() ?>"><?php echo numberPad($objAval->getTotalTrabDesignados(),2) ?> - <?php echo numberPad($objAval->getTotalTrabPendentes(),2) ?> - <?php echo  $objAval->getNomeConvidado() ?>  - <?php echo  $objAval->getLogin() ?></option>
                <?php
            }
        } catch (Exception $exc) {
            echo "<option value=''>".$exc->getMessage()."</option>";
        }
    }
    public static function avaliadoresPremiacao($fws_, $debug = false) {
        try {
            $objAvaliador = new Avaliador();
            $objAvaliador->carregarListaAvaliadoresPremiacaoByIdEvento($debug);
            foreach ($objAvaliador->getListaAvaliadores() as $objAval) {
                ?>
                <option 
                    <?php echo verifItemSelected($fws_['cod_avaliador'], $objAval->getCodUsuario()) ?>
                    value="<?php echo  $objAval->getCodUsuario() ?>"><?php echo numberPad($objAval->getTotalTrabDesignados(),2) ?> - <?php echo numberPad($objAval->getTotalTrabPendentes(),2) ?> - <?php echo  $objAval->getNomeConvidado() ?>  - <?php echo  $objAval->getLogin() ?></option>
                <?php
            }
        } catch (Exception $exc) {
            echo "<option value=''>".$exc->getMessage()."</option>";
        }
    }
    public static function gatewayPagamento($fws_, $debug = false) {
        ?>
        <option <?= verifItemSelected($fws_['gateway_pagamento'], 'ITAU-SHOPLINE', false) ?>>ITAU-SHOPLINE</option>
        <option <?= verifItemSelected($fws_['gateway_pagamento'], 'PAGSEGURO', false) ?> >PAGSEGURO</option>
        <option <?= verifItemSelected($fws_['gateway_pagamento'], 'PAY-PAL', false) ?>>PAY-PAL</option>
        <option <?= verifItemSelected($fws_['gateway_pagamento'], 'SISPRIME', false) ?>>SISPRIME</option>
        <option <?= verifItemSelected($fws_['gateway_pagamento'], 'ITAU-BOLECODE', false) ?>>ITAU-BOLECODE</option>
        </select>    
        <?php
    }
    public static function tipoArquivoPagamento($fws_, $debug = false) {
        $objEvConf = EventoConfiguracao::getInstancePopulado(COD_EVENTO);
        switch ($objEvConf->getGatewayPagamento()) {
            case 'PAGSEGURO':
                ?><option value='PAGSEGURO'>PAGSEGURO</option><?php
                break;
            case 'PAY-PAL':
                ?><option value='PAY-PAL'>PAY-PAL</option><?php
                break;
            case 'ITAU-SHOPLINE':
                ?><option value='ITAU-SHOPLINE'>ITAU-SHOPLINE</option><?php
                break;
            case 'ITAU-BOLECODE':
                ?><option value='ITAU-BOLECODE'>ITAU-BOLECODE</option><?php
                break;
            case 'UNIPRIME':
                ?><option value='UNIPRIME'>UNIPRIME</option><?php
                break;
            case 'SISPRIME':
                ?><option value='SISPRIME'>SISPRIME</option><?php
                break;
            case 'CAIXAECONOMICA':
                ?><option value='CAIXAECONOMICA'>CAIXAECONOMICA</option><?php
                break;
            default:
                # code...
                break;
        }
    }
    public static function perfis($fws_, $debug = false) {
        $objPerfil = new Perfil();
        if ($fws_['codEvento']!=''){
            $objPerfil->setCodEvento($fws_['codEvento']);
        }
        try {
            $objPerfil->carregarListaPerfil($debug);
            foreach ($objPerfil->getListaPerfil() as $objL) {
                if($objL->getReceberInscricao()==true or $fws_['semRestricao']==true){
                ?>
                <option 
                    <?php echo verifItemSelected($fws_['cod_perfil'], $objL->getCodPerfil()) ?>
                    value='<?php echo $objL->getCodPerfil()?>' ><?php echo $objL->getTitulo() ?></option>
                <?php
                }
            }
        } catch (Exception $exc) {
            echo "<option value=''>".$exc->getMessage()."</option>";
        }
    }
    public static function taxas($fws_, $debug = false) {
        try {
            $objTaxa = new Taxa();
            if ($fws_['cod_evento']!=''){
                $objTaxa->setCodEvento($fws_['cod_evento']);
            }
            if ($fws_['cod_perfil']!=''){
                $objTaxa->setCodPerfil($fws_['cod_perfil']);
            }
            $objTaxa->carregarListaTaxas($condicoes=false, $debug);
            foreach ($objTaxa->getListaTaxas() as $objL) {
                if($objL->getTxUnicaAVistaValor()!=''){
                    $txtTaxa  = "";
                    // $txtTaxa .= $objL->getCodPerfil()? $objL->getCodPerfil()." " : "";
                    $txtTaxa .= $objL->getTituloPerfil()? $objL->getTituloPerfil()." " : "";
                    $txtTaxa .= $objL->getTitulo()? $objL->getTitulo()." " : "";
                    $txtTaxa .= $objL->getTxUnicaAVistaValor()? " - R$ ".formatMoneyView($objL->getTxUnicaAVistaValor()) : "";
                    // $txtTaxa .= $objL->getTxUnicaAVistaValor()? " - ".$objL->getTxUnicaAVistaValor() : "";
                    $txtTaxa .= $objL->getMaxDataVencimento()? " - ".formatDateView($objL->getMaxDataVencimento) : "";
                    ?>
                    <option <?php echo verifItemSelected($fws_['cod_taxa'], $objL->getCodTaxa()) ?>
                        value='<?php echo $objL->getCodTaxa()?>' ><?php echo $txtTaxa ?></option>
                        <?php
                }
            }
        } catch (Exception $exc) {
            echo "<option value=''>".$exc->getMessage()."</option>";
        }
    }
    public static function perfisCadAdmin($fws_, $debug = false) {
        try {
            $objPerfil = new Perfil();
            $objPerfil->carregarListaPerfil($debug);
            foreach ($objPerfil->getListaPerfil() as $objL) {
                ?>
                <option 
                    <?php echo verifItemSelected($fws_['cod_perfil'], $objL->getCodPerfil()) ?>
                    value='<?php echo $objL->getCodPerfil()?>' ><?php echo $objL->getTitulo() ?></option>
                <?php
            }
        } catch (Exception $exc) {
            echo "<option value=''>".$exc->getMessage()."</option>";
        }
    }
    public static function listarEventos($fws_, $debug = false) {
        $objEvento = new Evento();
        if ($fws_['statusEvento'] != '') {
            $objEvento->setStatus($fws_['statusEvento']);
        }
        $objEvento->setCodUsuario($_SESSION['_cod_usuario']);
        $objEvento->carregarListaEventosByIdLogin($debug);
        $totalItens = count($objEvento->getListaEventos());
        if($fws_['cod_evento']==''){
            $fws_['cod_evento'] = COD_EVENTO;
        }
        foreach ($objEvento->getListaEventos() as $objL) {
            ?>
            <option 
                <?php echo verifItemSelected($fws_['cod_evento'], $objL->getCodEvento()) ?>
                <?php echo $totalItens==1? 'selected' : '' ?>
                value='<?php echo $objL->getCodEvento()?>' ><?php echo $objL->getProjeto() . " - " .$objL->getCodEvento() . " - " . $objL->getTitulo() ?></option>
            <?php
        }
    }
    public static function areasTematicas($fws_, $debug = false) {
        $objAreaTematica = new AreaTematica();
        if (($fws_['perspectiva'] == 'avaliacao')
            and (FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_GERENCIAR_AVALIACAO_TRABALHOS, 5, false, false))) {
            $objAreaTematica->setCodUsuario($_SESSION['_cod_usuario']);
            $objAreaTematica->carregarListaAreasTematicasByIdUsuario(false,$debug);
        }else{
            $objAreaTematica->carregarListaAreaTematica($debug);
        }    
        
        foreach ($objAreaTematica->getListaAreasTematicas() as $objL) {
            ?>
            <option 
                <?php echo verifItemSelected($fws_['cod_area_tematica'], $objL->getCodAreaTematica()) ?>
                value='<?php echo $objL->getCodAreaTematica()?>' ><?php echo $objL->getPrefixo()?> - <?php echo $objL->getTitulo() ?></option>
            <?php
        }
    }
    /*
    public static function tipoDocumento($fws_, $debug = false){
        ?>
        <option <?php echo verifItemSelected($fws_['tipo_documento'], 'COMPROVANTE DE TRANSFERÊNCIA') ?> value="COMPROVANTE DE TRANSFERÊNCIA">COMPROVANTE DE TRANSFERÊNCIA</option>
        <option <?php echo verifItemSelected($fws_['tipo_documento'], 'RPA') ?> value="RPA">RPA</option>
        <option <?php echo verifItemSelected($fws_['tipo_documento'], 'RPA e COMPROVANTE DE TRANSFERÊNCIA') ?> value="RPA e COMPROVANTE DE TRANSFERÊNCIA">RPA e COMPROVANTE DE TRANSFERÊNCIA</option>
        <option <?php echo verifItemSelected($fws_['tipo_documento'], 'CONTRATO') ?> value="CONTRATO">CONTRATO</option>
        <option <?php echo verifItemSelected($fws_['tipo_documento'], 'TERMO DE DESISTÊNCIA') ?> value="TERMO DE DESISTÊNCIA">TERMO DE DESISTÊNCIA</option>
        <option <?php echo verifItemSelected($fws_['tipo_documento'], 'TERMO DE DESLIGAMENTO') ?> value="TERMO DE DESLIGAMENTO">TERMO DE DESLIGAMENTO</option>
        <option <?php echo verifItemSelected($fws_['tipo_documento'], 'TERMO DE CANCELAMENTO') ?> value="TERMO DE CANCELAMENTO">TERMO DE CANCELAMENTO</option>
        <option <?php echo verifItemSelected($fws_['tipo_documento'], 'TERMO DE CONCESSÃO') ?> value="TERMO DE CONCESSÃO">TERMO DE CONCESSÃO</option>
        <option <?php echo verifItemSelected($fws_['tipo_documento'], 'PEDIDO DE TROCA DE DADOS BANCÁRIOS') ?> value="PEDIDO DE TROCA DE DADOS BANCÁRIOS">PEDIDO DE TROCA DE DADOS BANCÁRIOS</option>
        <option <?php echo verifItemSelected($fws_['tipo_documento'], 'INFORMATIVO DE RENDIMENTOS') ?> value="INFORMATIVO DE RENDIMENTOS">INFORMATIVO DE RENDIMENTOS</option>
        <?php
    }
    */

    public static function tipoDocumento($fws_, $debug = false) {
        if($_SERVER['REMOTE_ADDR']=='177.82.0.28'){
        try {
            $obj = new Trabalho();
            $obj->carregarListaTipoDocumentos($debug);
            foreach ($obj->getListaTipoDocumentos() as $objL) {
                ?>
                <option 
                    <?php echo verifItemSelected($fws_['tipo_documento'], $objL->getTipoDocumento()) ?>
                    value="<?php echo $objL->getTipoDocumento() ?>"><?php echo  $objL->getTipoDocumento() ?></option>
                <?php
            }
        } catch (Exception $exc) {
            echo "<option value=''>".$exc->getMessage()."</option>";
        }
        }
    }        

    public static function formatoApresentacao($fws_, $debug = false) {
        if(COD_EVENTO==70){
            ?> 
            <option <?php echo verifItemSelected($fws_['formato_apresentacao'], ' Comunicação Oral') ?> value=' Comunicação Oral'> Comunicação Oral</option>
            <option <?php echo verifItemSelected($fws_['formato_apresentacao'], 'Resumo Expandido') ?> value='Resumo Expandido'>Resumo Expandido</option>
            <?php
        }
        if(COD_EVENTO==116){
            ?> 
            <option <?php echo verifItemSelected($fws_['formato_apresentacao'], ' Artigo original (pôster)') ?> value=' Artigo original (pôster)'> Artigo original (pôster)</option>
            <option <?php echo verifItemSelected($fws_['formato_apresentacao'], 'Relato de caso (pôster)') ?> value='Relato de caso (pôster)'>Relato de caso (pôster)</option>
            <option <?php echo verifItemSelected($fws_['formato_apresentacao'], 'Relato de experiência (pôster)') ?> value='Relato de experiência (pôster)'>Relato de experiência (pôster)</option>
            <option <?php echo verifItemSelected($fws_['formato_apresentacao'], 'Tema Livre Oral (apresentação oral)') ?> value='Tema Livre Oral (apresentação oral)'>Tema Livre Oral (apresentação oral)</option>
            <?php
        }
    }    
    public static function formatoTexto($fws_, $debug = false) {
        if(COD_EVENTO==103){
            ?> 
            <option <?php echo verifItemSelected($fws_['formato_texto'], 'Pesquisa científica') ?> value='Pesquisa científica'>Pesquisa científica</option>
            <option <?php echo verifItemSelected($fws_['formato_texto'], 'Relato de experiência') ?> value='Relato de experiência'>Relato de experiência</option>
            <option <?php echo verifItemSelected($fws_['formato_texto'], 'ações extensionistas') ?> value='ações extensionistas'>ações extensionistas</option>
            
            <?php
        }else if(COD_EVENTO==70){
            ?> 
            <option <?php echo verifItemSelected($fws_['formato_texto'], 'Sistematização do trabalho profissional') ?> value='Sistematização do trabalho profissional'>Sistematização do trabalho profissional</option>
            <option <?php echo verifItemSelected($fws_['formato_texto'], 'Relato de experiência') ?> value='Relato de experiência'>Relato de experiência</option>
            <option <?php echo verifItemSelected($fws_['formato_texto'], 'Resultado de pesquisa') ?> value='Resultado de pesquisa'>Resultado de pesquisa</option>
            <option <?php echo verifItemSelected($fws_['formato_texto'], 'Reflexão teórica') ?> value='Reflexão teórica'>Reflexão teórica</option>
            
            <?php
        }else if(COD_EVENTO==61){
            ?> 
            <option <?php echo verifItemSelected($fws_['formato_texto'], 'Pesquisas Científicas') ?> value='Pesquisas Científicas'>Pesquisas Científicas</option>
            <option <?php echo verifItemSelected($fws_['formato_texto'], 'Relatos de Experiência') ?> value='Relatos de Experiência'>Relatos de Experiência</option>
            <?php
        }else if(in_array(COD_EVENTO, [53, 81])){
            ?> 
            <option <?php echo verifItemSelected($fws_['formato_texto'], 'Artigo original (pôster)') ?> value='Artigo original (pôster)'>Artigo original (pôster)</option>
            <option <?php echo verifItemSelected($fws_['formato_texto'], 'Relato de caso (pôster)') ?> value='Relato de caso (pôster)'>Relato de caso (pôster)</option>
            <option <?php echo verifItemSelected($fws_['formato_texto'], 'Relato de experiência (pôster)') ?> value='Relato de experiência (pôster)'>Relato de experiência (pôster)</option>
            <option <?php echo verifItemSelected($fws_['formato_texto'], 'Tema livre oral (apresentação oral)') ?> value='Tema livre oral (apresentação oral)'>Tema livre oral (apresentação oral)</option>
            <?php
        }else
        if(COD_EVENTO==33){
            ?>
            <option <?php echo verifItemSelected($fws_['formato_texto'], 'Projeto de Pesquisa') ?> value='Projeto de Pesquisa'>Projeto de Pesquisa</option>
            <option <?php echo verifItemSelected($fws_['formato_texto'], 'Projeto de Extensão') ?> value='Projeto de Extensão'>Projeto de Extensão</option>
            <option <?php echo verifItemSelected($fws_['formato_texto'], 'Prestação de Serviços') ?> value='Prestação de Serviços'>Prestação de Serviços</option>
            <option <?php echo verifItemSelected($fws_['formato_texto'], 'Estudos de Caso') ?> value='Estudos de Caso'>Estudos de Caso</option>
            <option <?php echo verifItemSelected($fws_['formato_texto'], 'Relatos de Experiências') ?> value='Relatos de Experiências'>Relatos de Experiências</option>
            
            projetos de pesquisa, extensão, prestação de serviços, estudos de caso e relatos de experiências
            <?php
        }
        else        
        if(COD_EVENTO==7){
            ?>
            <option 
                <?php echo verifItemSelected($fws_['formato_texto'], 'Trabalho de Pesquisa') ?>
                value='Trabalho de Pesquisa'>Trabalho de Pesquisa</option>
            <option 
                <?php echo verifItemSelected($fws_['formato_texto'], 'Relato de Experiência') ?>
                value='Relato de Experiência'>Relato de Experiência</option>
            <?php
        }
        else        
        if(COD_EVENTO==6){
            ?>
            <option 
                <?php echo verifItemSelected($fws_['formato_texto'], 'Trabalho Acadêmico') ?>
                value='Pesquisa Original'>Pesquisa</option>
            <option 
                <?php echo verifItemSelected($fws_['formato_texto'], 'Relato de Experiência') ?>
                value='Relato de Experiência'>Relato de Experiência</option>
            <?php
        }
        else if(COD_EVENTO==4){
            switch ($fws_['formato_texto']) {
                case 'Trabalhos Originais':
                case 'Trabalhos Revisões':
                case 'Estudos de Caso':
                    $fws_['formato_texto'] = "Trabalho Acadêmico";
                    break;
                default:
                    break;
            }
        ?>
            <option 
                <?php echo verifItemSelected($fws_['formato_texto'], 'Trabalho Acadêmico') ?>
                value='Trabalho Acadêmico'>Trabalho Acadêmico</option>
            <option 
                <?php echo verifItemSelected($fws_['formato_texto'], 'Relato de Experiência') ?>
                value='Relato de Experiência'>Relato de Experiência</option>
            <option 
                <?php echo verifItemSelected($fws_['formato_texto'], '-1') ?>
                value='-1'>Não informou</option>
        <?php
        }else{    
        ?>
            <option 
                <?php echo verifItemSelected($fws_['formato_texto'], 'Trabalhos Originais') ?>
                value='Trabalhos Originais'>Trabalhos Originais</option>
            <option 
                <?php echo verifItemSelected($fws_['formato_texto'], 'Trabalhos Revisões') ?>
                value='Trabalhos Revisões'>Trabalhos Revisões</option>
            <option 
                <?php echo verifItemSelected($fws_['formato_texto'], 'Estudos de Caso') ?>
                value='Estudos de Caso'>Estudos de Caso</option>
            <option 
                <?php echo verifItemSelected($fws_['formato_texto'], 'Relato de Experiência') ?>
                value='Relato de Experiência'>Relato de Experiência</option>
            <option 
                <?php echo verifItemSelected($fws_['formato_texto'], '-1') ?>
                value='-1'>Não informou</option>
        <?php
        }
    }
    public static function salas($fws_, $debug = false) {
        try {
            $objEnsalamento = new Ensalamento();
            $objEnsalamento->carregarListaSalas($debug);

            foreach ($objEnsalamento->getListaSalas() as $objL) {
                if($objL->getNomeSala()!=$nomeSalaAnt){
                ?>
                <option 
                    <?php echo verifItemSelected($fws_['nome_sala'], $objL->getNomeSala()) ?>
                    value='<?php echo $objL->getNomeSala()?>' ><?php echo $objL->getNomeSala() ?></option>
                <?php
                }
                $nomeSalaAnt = $objL->getNomeSala();
            }
            ?>
                <option  <?php echo verifItemSelected($fws_['nome_sala'], '-1') ?>
                    value='-1'></option>
            <?php
        } catch (Exception $exc) {
            ?>
                <option  <?php echo verifItemSelected($fws_['nome_sala'], '-1') ?>
                    value='-1'>Salas para ensalamento não cadastradas</option><?php
        }

    }
    public static function oficinas($fws_, $debug = false) {
        try {

            $objOficina = new Oficina();
            if($fws_['cod_evento']!=''){
                $objOficina->setCodEvento($fws_['cod_evento']);
            }
            $objEvConf = $objOficina->getObjEventoConfiguracao();
            if($objEvConf->getFinalidade() == 'Agenda'){
                $orderBy = ' data_inicio, ordem, cod_oficina, titulo ';
            }
            $objOficina->carregarListaOficina($orderBy);
            foreach ($objOficina->getListaOficina() as $objL) {
                if($objL->getStatus()!='ATIVADA'){
                    continue;
                }
                $titulo = $objL->getCodOficina()." - ".$objL->getTitulo();
                if($objL->getTipo()=='Agenda'){
                    $titulo = formatDateView($objL->getDataInicio())." - ".$objL->getTitulo();
                }else{
                    $titulo = $objL->getTitulo();
                    if($objL->getNumeroOficial()!=''){
                        $titulo = $objL->getNumeroOficial()." - ".$titulo;
                    }
                    if($objL->getTipo()!=''){
                        $titulo = $objL->getTipo()." - ".$titulo;
                    }
                } 
                ?>
                <option 
                    <?php echo verifItemSelected($fws_['cod_oficina'], $objL->getCodOficina()) ?>
                    value='<?php echo $objL->getCodOficina()?>' ><?php echo $titulo ?></option>
                <?php
            }
        } catch (Exception $exc) {
            echo ">option>".$exc->getMessage()."</option>";
        }
    }
    public static function paises($fws_, $debug = false){
        ?>
        <option value="África do Sul">África do Sul</option>
        <option value="Albânia">Albânia</option>
        <option value="Alemanha">Alemanha</option>
        <option value="Andorra">Andorra</option>
        <option value="Angola">Angola</option>
        <option value="Anguilla">Anguilla</option>
        <option value="Antigua">Antigua</option>
        <option value="Arábia Saudita">Arábia Saudita</option>
        <option value="Argentina">Argentina</option>
        <option value="Armênia">Armênia</option>
        <option value="Aruba">Aruba</option>
        <option value="Austrália">Austrália</option>
        <option value="Áustria">Áustria</option>
        <option value="Azerbaijão">Azerbaijão</option>
        <option value="Bahamas">Bahamas</option>
        <option value="Bahrein">Bahrein</option>
        <option value="Bangladesh">Bangladesh</option>
        <option value="Barbados">Barbados</option>
        <option value="Bélgica">Bélgica</option>
        <option value="Benin">Benin</option>
        <option value="Bermudas">Bermudas</option>
        <option value="Botsuana">Botsuana</option>
        <option value="Brasil" selected>Brasil</option>
        <option value="Brunei">Brunei</option>
        <option value="Bulgária">Bulgária</option>
        <option value="Burkina Fasso">Burkina Fasso</option>
        <option value="botão">botão</option>
        <option value="Cabo Verde">Cabo Verde</option>
        <option value="Camarões">Camarões</option>
        <option value="Camboja">Camboja</option>
        <option value="Canadá">Canadá</option>
        <option value="Cazaquistão">Cazaquistão</option>
        <option value="Chade">Chade</option>
        <option value="Chile">Chile</option>
        <option value="China">China</option>
        <option value="Cidade do Vaticano">Cidade do Vaticano</option>
        <option value="Colômbia">Colômbia</option>
        <option value="Congo">Congo</option>
        <option value="Coréia do Sul">Coréia do Sul</option>
        <option value="Costa do Marfim">Costa do Marfim</option>
        <option value="Costa Rica">Costa Rica</option>
        <option value="Croácia">Croácia</option>
        <option value="Dinamarca">Dinamarca</option>
        <option value="Djibuti">Djibuti</option>
        <option value="Dominica">Dominica</option>
        <option value="EUA">EUA</option>
        <option value="Egito">Egito</option>
        <option value="El Salvador">El Salvador</option>
        <option value="Emirados Árabes">Emirados Árabes</option>
        <option value="Equador">Equador</option>
        <option value="Eritréia">Eritréia</option>
        <option value="Escócia">Escócia</option>
        <option value="Eslováquia">Eslováquia</option>
        <option value="Eslovênia">Eslovênia</option>
        <option value="Espanha">Espanha</option>
        <option value="Estônia">Estônia</option>
        <option value="Etiópia">Etiópia</option>
        <option value="Fiji">Fiji</option>
        <option value="Filipinas">Filipinas</option>
        <option value="Finlândia">Finlândia</option>
        <option value="França">França</option>
        <option value="Gabão">Gabão</option>
        <option value="Gâmbia">Gâmbia</option>
        <option value="Gana">Gana</option>
        <option value="Geórgia">Geórgia</option>
        <option value="Gibraltar">Gibraltar</option>
        <option value="Granada">Granada</option>
        <option value="Grécia">Grécia</option>
        <option value="Guadalupe">Guadalupe</option>
        <option value="Guam">Guam</option>
        <option value="Guatemala">Guatemala</option>
        <option value="Guiana">Guiana</option>
        <option value="Guiana Francesa">Guiana Francesa</option>
        <option value="Guiné-bissau">Guiné-bissau</option>
        <option value="Haiti">Haiti</option>
        <option value="Holanda">Holanda</option>
        <option value="Honduras">Honduras</option>
        <option value="Hong Kong">Hong Kong</option>
        <option value="Hungria">Hungria</option>
        <option value="Iêmen">Iêmen</option>
        <option value="Ilhas Cayman">Ilhas Cayman</option>
        <option value="Ilhas Cook">Ilhas Cook</option>
        <option value="Ilhas Curaçao">Ilhas Curaçao</option>
        <option value="Ilhas Marshall">Ilhas Marshall</option>
        <option value="Ilhas Turks & Caicos">Ilhas Turks & Caicos</option>
        <option value="Ilhas Virgens (brit.)">Ilhas Virgens (brit.)</option>
        <option value="Ilhas Virgens(amer.)">Ilhas Virgens(amer.)</option>
        <option value="Ilhas Wallis e Futuna">Ilhas Wallis e Futuna</option>
        <option value="Índia">Índia</option>
        <option value="Indonésia">Indonésia</option>
        <option value="Inglaterra">Inglaterra</option>
        <option value="Irlanda">Irlanda</option>
        <option value="Islândia">Islândia</option>
        <option value="Israel">Israel</option>
        <option value="Itália">Itália</option>
        <option value="Jamaica">Jamaica</option>
        <option value="Japão">Japão</option>
        <option value="Jordânia">Jordânia</option>
        <option value="Kuwait">Kuwait</option>
        <option value="Latvia">Latvia</option>
        <option value="Líbano">Líbano</option>
        <option value="Liechtenstein">Liechtenstein</option>
        <option value="Lituânia">Lituânia</option>
        <option value="Luxemburgo">Luxemburgo</option>
        <option value="Macau">Macau</option>
        <option value="Macedônia">Macedônia</option>
        <option value="Madagascar">Madagascar</option>
        <option value="Malásia">Malásia</option>
        <option value="Malaui">Malaui</option>
        <option value="Mali">Mali</option>
        <option value="Malta">Malta</option>
        <option value="Marrocos">Marrocos</option>
        <option value="Martinica">Martinica</option>
        <option value="Mauritânia">Mauritânia</option>
        <option value="Mauritius">Mauritius</option>
        <option value="México">México</option>
        <option value="Moldova">Moldova</option>
        <option value="Mônaco">Mônaco</option>
        <option value="Montserrat">Montserrat</option>
        <option value="Nepal">Nepal</option>
        <option value="Nicarágua">Nicarágua</option>
        <option value="Niger">Niger</option>
        <option value="Nigéria">Nigéria</option>
        <option value="Noruega">Noruega</option>
        <option value="Nova Caledônia">Nova Caledônia</option>
        <option value="Nova Zelândia">Nova Zelândia</option>
        <option value="Omã">Omã</option>
        <option value="Palau">Palau</option>
        <option value="Panamá">Panamá</option>
        <option value="Papua-nova Guiné">Papua-nova Guiné</option>
        <option value="Paquistão">Paquistão</option>
        <option value="Paraguai">Paraguai</option>
        <option value="Peru">Peru</option>
        <option value="Polinésia Francesa">Polinésia Francesa</option>
        <option value="Polônia">Polônia</option>
        <option value="Porto Rico">Porto Rico</option>
        <option value="Portugal">Portugal</option>
        <option value="Qatar">Qatar</option>
        <option value="Quênia">Quênia</option>
        <option value="Rep. Dominicana">Rep. Dominicana</option>
        <option value="Rep. Tcheca">Rep. Tcheca</option>
        <option value="Reunion">Reunion</option>
        <option value="Romênia">Romênia</option>
        <option value="Ruanda">Ruanda</option>
        <option value="Rússia">Rússia</option>
        <option value="Saipan">Saipan</option>
        <option value="Samoa Americana">Samoa Americana</option>
        <option value="Senegal">Senegal</option>
        <option value="Serra Leone">Serra Leone</option>
        <option value="Seychelles">Seychelles</option>
        <option value="Singapura">Singapura</option>
        <option value="Síria">Síria</option>
        <option value="Sri Lanka">Sri Lanka</option>
        <option value="St. Kitts & Nevis">St. Kitts & Nevis</option>
        <option value="St. Lúcia">St. Lúcia</option>
        <option value="St. Vincent">St. Vincent</option>
        <option value="Sudão">Sudão</option>
        <option value="Suécia">Suécia</option>
        <option value="Suiça">Suiça</option>
        <option value="Suriname">Suriname</option>
        <option value="Tailândia">Tailândia</option>
        <option value="Taiwan">Taiwan</option>
        <option value="Tanzânia">Tanzânia</option>
        <option value="Togo">Togo</option>
        <option value="Trinidad & Tobago">Trinidad & Tobago</option>
        <option value="Tunísia">Tunísia</option>
        <option value="Turquia">Turquia</option>
        <option value="Ucrânia">Ucrânia</option>
        <option value="Uganda">Uganda</option>
        <option value="Uruguai">Uruguai</option>
        <option value="Venezuela">Venezuela</option>
        <option value="Vietnã">Vietnã</option>
        <option value="Zaire">Zaire</option>
        <option value="Zâmbia">Zâmbia</option>
        <option value="Zimbábue">Zimbábue</option>
        <?php
    }
    public static function estadosNascimento($fws_,$debug = false, $recibo = false ) {
        $fws_['estado'] = $fws_['estado_nascimento'];
        CarregarOptionsAC::estados($fws_, $debug, $recibo);
    }
    public static function rgEstadoEmissao($fws_,$debug = false, $recibo = false ) {
        $fws_['estado'] = $fws_['rg_estado_emissao'];
        CarregarOptionsAC::estados($fws_, $debug, $recibo);
    }
    public static function estados($fws_,$debug = false, $recibo = false ) {
        if($recibo){
            $fws_['estado'] = $fws_['estado_recibo'];
        }
        if($fws_['estado']=='Não informou'){
            $fws_['estado']='-1';
        }
        ?>
        <option class='form_campos' value='AC' <?php echo verifItemSelected($fws_['estado'], 'AC') ?> >AC</option>
        <option class='form_campos' value='AL' <?php echo verifItemSelected($fws_['estado'], 'AL') ?> >AL</option>
        <option class='form_campos' value='AP' <?php echo verifItemSelected($fws_['estado'], 'AP') ?> >AP</option>
        <option class='form_campos' value='AM' <?php echo verifItemSelected($fws_['estado'], 'AM') ?> >AM</option>
        <option class='form_campos' value='BA' <?php echo verifItemSelected($fws_['estado'], 'BA') ?> >BA</option>
        <option class='form_campos' value='CE' <?php echo verifItemSelected($fws_['estado'], 'CE') ?> >CE</option>
        <option class='form_campos' value='DF' <?php echo verifItemSelected($fws_['estado'], 'DF') ?> >DF</option>
        <option class='form_campos' value='ES' <?php echo verifItemSelected($fws_['estado'], 'ES') ?> >ES</option>
        <option class='form_campos' value='GO' <?php echo verifItemSelected($fws_['estado'], 'GO') ?> >GO</option>
        <option class='form_campos' value='MA' <?php echo verifItemSelected($fws_['estado'], 'MA') ?> >MA</option>
        <option class='form_campos' value='MT' <?php echo verifItemSelected($fws_['estado'], 'MT') ?> >MT</option>
        <option class='form_campos' value='MS' <?php echo verifItemSelected($fws_['estado'], 'MS') ?> >MS</option>
        <option class='form_campos' value='MG' <?php echo verifItemSelected($fws_['estado'], 'MG') ?> >MG</option>
        <option class='form_campos' value='PA' <?php echo verifItemSelected($fws_['estado'], 'PA') ?> >PA</option>
        <option class='form_campos' value='PB' <?php echo verifItemSelected($fws_['estado'], 'PB') ?> >PB</option>
        <option class='form_campos' value='PR' <?php echo verifItemSelected($fws_['estado'], 'PR') ?> >PR</option>
        <option class='form_campos' value='PE' <?php echo verifItemSelected($fws_['estado'], 'PE') ?> >PE</option>
        <option class='form_campos' value='PI' <?php echo verifItemSelected($fws_['estado'], 'PI') ?> >PI</option>
        <option class='form_campos' value='RJ' <?php echo verifItemSelected($fws_['estado'], 'RJ') ?> >RJ</option>
        <option class='form_campos' value='RN' <?php echo verifItemSelected($fws_['estado'], 'RN') ?> >RN</option>
        <option class='form_campos' value='RS' <?php echo verifItemSelected($fws_['estado'], 'RS') ?> >RS</option>
        <option class='form_campos' value='RO' <?php echo verifItemSelected($fws_['estado'], 'RO') ?> >RO</option>
        <option class='form_campos' value='RR' <?php echo verifItemSelected($fws_['estado'], 'RR') ?> >RR</option>
        <option class='form_campos' value='SC' <?php echo verifItemSelected($fws_['estado'], 'SC') ?> >SC</option>
        <option class='form_campos' value='SP' <?php echo verifItemSelected($fws_['estado'], 'SP') ?> >SP</option>
        <option class='form_campos' value='SE' <?php echo verifItemSelected($fws_['estado'], 'SE') ?> >SE</option>
        <option class='form_campos' value='TO' <?php echo verifItemSelected($fws_['estado'], 'TO') ?> >TO</option>
        <?php
    }
    public static function bancos($fws_,$debug = false) {
        if($fws_['db_banco']=='Não informou'){
            $fws_['db_banco']='-1';
        }
        ?>
        <option class='form_campos' value=""></option>
        <option class='form_campos' value="001" <?php echo verifItemSelected($fws_['db_banco'], '001') ?> >001 - Banco do Brasil</option>
        <option class='form_campos' value="237" <?php echo verifItemSelected($fws_['db_banco'], '237') ?> >237 - Bradesco</option>
        <option class='form_campos' value="341" <?php echo verifItemSelected($fws_['db_banco'], '341') ?> >341 - Itaú</option>
        <option class='form_campos' value="104" <?php echo verifItemSelected($fws_['db_banco'], '104') ?> >104 - Caixa Econômica Federal</option>
        <option class='form_campos' value="033" <?php echo verifItemSelected($fws_['db_banco'], '033') ?> >033 - Santander</option>
        <option class='form_campos' value="077" <?php echo verifItemSelected($fws_['db_banco'], '077') ?> >077 - Banco Inter</option>
        <option class='form_campos' value="260" <?php echo verifItemSelected($fws_['db_banco'], '260') ?> >260 - Nubank</option>
        <option class='form_campos' value="318" <?php echo verifItemSelected($fws_['db_banco'], '318') ?> >318 - Banco BMG</option>
        <option class='form_campos' value="229" <?php echo verifItemSelected($fws_['db_banco'], '229') ?> >229 - Banrisul</option>
        <option class='form_campos' value="347" <?php echo verifItemSelected($fws_['db_banco'], '347') ?> >347 - Banco do Estado do Paraná</option>
        <option class='form_campos' value="003" <?php echo verifItemSelected($fws_['db_banco'], '003') ?> >003 - Banco da Amazônia</option>
        <option class='form_campos' value="004" <?php echo verifItemSelected($fws_['db_banco'], '004') ?> >004 - Banco da Amazônia</option>
        <option class='form_campos' value="070" <?php echo verifItemSelected($fws_['db_banco'], '070') ?> >070 - Banco de Brasília</option>
        <option class='form_campos' value="025" <?php echo verifItemSelected($fws_['db_banco'], '025') ?> >025 - Banco Alfa</option>
        <option class='form_campos' value="218" <?php echo verifItemSelected($fws_['db_banco'], '218') ?> >218 - Banco Bonsucesso</option>
        <option class='form_campos' value="065" <?php echo verifItemSelected($fws_['db_banco'], '065') ?> >065 - Banco Lemon</option>
        <option class='form_campos' value="121" <?php echo verifItemSelected($fws_['db_banco'], '121') ?> >121 - Banco Gerador</option>
        <option class='form_campos' value="291" <?php echo verifItemSelected($fws_['db_banco'], '291') ?> >291 - Banco de Credito Nacional</option>
        <option class='form_campos' value="076" <?php echo verifItemSelected($fws_['db_banco'], '076') ?> >076 - Banco KDB</option>
        <!-- Adicione outros bancos conforme necessário -->
        <option class='form_campos' value="290" <?php echo verifItemSelected($fws_['db_banco'], '290') ?> >290 - PagSeguro</option>
        <option class='form_campos' value="756" <?php echo verifItemSelected($fws_['db_banco'], '756') ?> >756 - Sicoob</option>
        <option class='form_campos' value="748" <?php echo verifItemSelected($fws_['db_banco'], '748') ?> >748 - Sicred</option>
        <!-- Adicione outros bancos conforme necessário -->
<option class='form_campos' value="336" <?=verifItemSelected($fws_['db_banco'],'336')?> >336 – C6 Bank</option>
<option class='form_campos' value="335" <?=verifItemSelected($fws_['db_banco'],'335')?> >335 – Banco Digio</option>
<option class='form_campos' value="654" <?=verifItemSelected($fws_['db_banco'],'654')?> >654 – Banco Digimais</option>
<option class='form_campos' value="735" <?=verifItemSelected($fws_['db_banco'],'735')?> >735 – Banco Neon</option>
<option class='form_campos' value="121" <?=verifItemSelected($fws_['db_banco'],'121')?> >121 – Agibank</option>
<option class='form_campos' value="323" <?=verifItemSelected($fws_['db_banco'],'323')?> >323 – Mercado Pago</option>
<option class='form_campos' value="280" <?=verifItemSelected($fws_['db_banco'],'280')?> >280 – Meu Pag!</option>
<option class='form_campos' value="380" <?=verifItemSelected($fws_['db_banco'],'380')?> >380 – PicPay Bank</option>
<option class='form_campos' value="246" <?=verifItemSelected($fws_['db_banco'],'246')?> >246 – Banco ABC Brasil</option>
<option class='form_campos' value="623" <?=verifItemSelected($fws_['db_banco'],'623')?> >623 – Banco Pan</option>
<option class='form_campos' value="633" <?=verifItemSelected($fws_['db_banco'],'633')?> >633 – Banco Rendimento</option>
<option class='form_campos' value="655" <?=verifItemSelected($fws_['db_banco'],'655')?> >655 – Banco Votorantim (BV)</option>
<option class='form_campos' value="741" <?=verifItemSelected($fws_['db_banco'],'741')?> >741 – Banco Ribeirão Preto</option>
<option class='form_campos' value="707" <?=verifItemSelected($fws_['db_banco'],'707')?> >707 – Banco Daycoval</option>
<option class='form_campos' value="746" <?=verifItemSelected($fws_['db_banco'],'746')?> >746 – Banco Modal</option>

        <?php
    }
    public static function estadoCivil($fws_,$debug = false) {
        if($fws_['db_banco']=='Não informou'){
            $fws_['db_banco']='-1';
        }
        ?>
        <option class='form_campos' value="">Selecione um estado civil</option>
        <option class='form_campos' value="Casado" <?php echo verifItemSelected($fws_['estado_civil'], 'Casado') ?> >Casado(a)</option>
        <option class='form_campos' value="Divorciado" <?php echo verifItemSelected($fws_['estado_civil'], 'Divorciado') ?> >Divorciado(a)</option>
        <option class='form_campos' value="Solteiro" <?php echo verifItemSelected($fws_['estado_civil'], 'Solteiro') ?> >Solteiro(a)</option>
        <option class='form_campos' value="União Estável" <?php echo verifItemSelected($fws_['estado_civil'], 'União Estável') ?> >União Estável</option>
        <option class='form_campos' value="Separado" <?php echo verifItemSelected($fws_['estado_civil'], 'Separado') ?> >Separado(a)</option>
        <option class='form_campos' value="Viúvo" <?php echo verifItemSelected($fws_['estado_civil'], 'Viúvo') ?> >Viúvo(a)</option>
        <option class='form_campos' value="Amasiado" <?php echo verifItemSelected($fws_['estado_civil'], 'Amasiado') ?> >Amasiado(a)</option>
        <?php
    }
    public static function cidadesNascimento($fws_,$debug = false, $recibo = false ) {
        $fws_['cidade'] = $fws_['cidade_nascimento'];
        $fws_['uf'] = $fws_['estado_nascimento'];
        CarregarOptionsAC::cidades($fws_, $debug, $recibo);    
    }
    public static function rgCidadeEmissao($fws_,$debug = false, $recibo = false ) {
        $fws_['cidade'] = $fws_['rg_cidade_emissao'];
        $fws_['uf'] = $fws_['rg_estado_emissao'];
        CarregarOptionsAC::cidades($fws_, $debug, $recibo);
    }
    public static function cidades($fws_, $debug = false, $recibo = false) {
        if($recibo){
            $fws_['cidade'] = $fws_['cidade_recibo'];
            $fws_['estado'] = $fws_['estado_recibo'];
        }
        if($fws_['uf']==''){
            $fws_['uf'] = $fws_['estado'];
        }
        try {
            if($fws_['uf']==''){
                throw new Exception("Estado não informado", 1);
            }    
            $objCidade = new Cidade();
            $objCidade->setUf($fws_['uf']);
            $objCidade->carregarListaCidades($debug);
            ?><option value=''>Selecione uma cidade</option><?php
            foreach ($objCidade->getListaCidades() as $objL) {
                ?>
                <option 
                    <?php echo verifItemSelected($fws_['cidade'], $objL->getNome()) ?>
                    value='<?php echo $objL->getNome()?>' ><?php echo $objL->getNome() ?>
                </option>
                <?php
            }
            
        } catch (Exception $exc) {
                        ?><option value=''>Selecione um estado para carregar as cidades</option><?php
        }
    }
    public static function presenca($flg_presenca,$debug = false) {
        ?>
        <option class='form_campos' <?php echo verifItemSelected($flg_presenca, '1') ?>      value='1'>Presente</option>
        <option class='form_campos' <?php echo verifItemSelected($flg_presenca, ['0','']) ?> value='0'>Ausente</option>
        <?php
    }  
    public static function tipoRelacao($tipo_relacao,$debug = false) {
        ?>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, '') ?> >Participante</option>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Participante') ?> >Participante</option>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Aplicador de exame') ?> >Aplicador de exame</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Apresentador(a) de atividade cultural') ?> >Apresentador(a) de atividade cultural</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Apresentação cultural em evento') ?> >Apresentação cultural em evento</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Arguidor(a)') ?> >Arguidor(a)</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Assistente') ?> >Assistente</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Avaliador de trabalho') ?> >Avaliador de trabalho</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Avaliador de trabalhos científicos') ?> >Avaliador de trabalhos científicos</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Avaliador de trabalhos extensionistas e coordenador de sala') ?> >Avaliador de trabalhos extensionistas e coordenador de sala</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Avaliador externo cnpq') ?> >Avaliador externo cnpq</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Avaliador') ?> >Avaliador</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Cerimonialista') ?> >Cerimonialista</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Colaborador em minicurso') ?> >Colaborador em minicurso</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Colaborador(a)') ?> >Colaborador(a)</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Comissão Organizadora') ?> >Comissão Organizadora</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Comunicador') ?> >Comunicador</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Conferencista com o tema') ?> >Conferencista com o tema</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Conferencista') ?> >Conferencista</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Consultor ad hoc dos capítulos submetidos ao livro pós-congresso, com o título') ?> >Consultor ad hoc dos capítulos submetidos ao livro pós-congresso, com o título</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Consultor ad hoc dos capítulos submetidos ao livro') ?> >Consultor ad hoc dos capítulos submetidos ao livro</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Consultor ad hoc') ?> >Consultor ad hoc</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Coordenador administrativo') ?> >Coordenador administrativo</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Coordenador da comissão científica') ?> >Coordenador da comissão científica</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Coordenador da mesa redonda') ?> >Coordenador da mesa redonda</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Coordenador de avaliação') ?> >Coordenador de avaliação</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Coordenador de comissão organizadora') ?> >Coordenador de comissão organizadora</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Coordenador de conferência') ?> >Coordenador de conferência</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Coordenador de grupo de trabalho') ?> >Coordenador de grupo de trabalho</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Coordenador de minicurso') ?> >Coordenador de minicurso</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Coordenador de oficina') ?> >Coordenador de oficina</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Coordenador de roda de conversa') ?> >Coordenador de roda de conversa</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Coordenador de sala') ?> >Coordenador de sala</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Coordenador de sessão de comunicação oral, constituída pelos trabalhos') ?> >Coordenador de sessão de comunicação oral, constituída pelos trabalhos</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Coordenador de sessão de comunicação') ?> >Coordenador de sessão de comunicação</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Coordenador de sessão de comunicação oral') ?> >Coordenador de sessão de comunicação oral</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Coordenador de sessão') ?> >Coordenador de sessão</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Coordenador de tutoria') ?> >Coordenador de tutoria</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Coordenador de videoforum') ?> >Coordenador de videoforum</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Coordenador geral') ?> >Coordenador geral</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Coordenador pedagógico') ?> >Coordenador pedagógico</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Coordenador técnico pedagógico') ?> >Coordenador técnico pedagógico</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Coordenador(a) da comissão de avaliação de trabalhos científico') ?> >Coordenador(a) da comissão de avaliação de trabalhos científico</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Coordenador(a) de simpósio') ?> >Coordenador(a) de simpósio</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Coordenador(a) e mediador(a)') ?> >Coordenador(a) e mediador(a)</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Coordenador(a)') ?> >Coordenador(a)</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Curador') ?> >Curador</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Debatedor de gt') ?> >Debatedor de gt</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Debatedor de poster da sessão intitulada') ?> >Debatedor de poster da sessão intitulada</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Debatedor de pôster') ?> >Debatedor de pôster</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Debatedor do simpósio') ?> >Debatedor do simpósio</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Equipe técnica') ?> >Equipe técnica</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Fiscal de aplicação de exame') ?> >Fiscal de aplicação de exame</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Fotógrafo') ?> >Fotógrafo</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Integrante da comissão de apoio') ?> >Integrante da comissão de apoio</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Intérprete de libras') ?> >Intérprete de libras</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Intérprete') ?> >Intérprete</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Mediador(a)') ?> >Mediador(a)</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Mediadora de mesa') ?> >Mediadora de mesa</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Membro da comissão avaliadora') ?> >Membro da comissão avaliadora</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Membro da comissão científica') ?> >Membro da comissão científica</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Membro da comissão de apoio') ?> >Membro da comissão de apoio</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Membro da comissão executiva') ?> >Membro da comissão executiva</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Membro da comissão monitora') ?> >Membro da comissão monitora</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Membro da comissão organizadora') ?> >Membro da comissão organizadora</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Membro de mesa redonda') ?> >Membro de mesa redonda</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Ministrante de minicurso') ?> >Ministrante de minicurso</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Ministrante de oficina') ?> >Ministrante de oficina</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Ministrante/supervisor(a)') ?> >Ministrante/supervisor(a)</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Ministrante') ?> >Ministrante</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Moderador de comunicação oral') ?> >Moderador de comunicação oral</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Moderador de conferência') ?> >Moderador de conferência</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Moderador de mesa-redonda') ?> >Moderador de mesa-redonda</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Moderador de mini curso') ?> >Moderador de mini curso</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Moderador') ?> >Moderador</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Monitor') ?> >Monitor</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Organizador do livro') ?> >Organizador do livro</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Orientador(a)') ?> >Orientador(a)</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Painelista') ?> >Painelista</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Palestrante mesa redonda') ?> >Palestrante mesa redonda</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Palestrante') ?> >Palestrante</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Parecerista') ?> >Parecerista</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Presidente da comissão organizadora') ?> >Presidente da comissão organizadora</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Presidente de mesa') ?> >Presidente de mesa</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Presidente de palestra') ?> >Presidente de palestra</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Produtora de material bibliografico') ?> >Produtora de material bibliografico</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Regente de coral') ?> >Regente de coral</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Tradutor(a)') ?> >Tradutor(a)</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Tutor') ?> >Tutor</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Técnico') ?> >Técnico</options>
        <option class='form_campos' <?php echo verifItemSelected($tipo_relacao, 'Vice-coordenador(a)') ?> >Vice-coordenador(a)</options>
        <?php
    }
    
    public static function status($status,$debug = false) {
        ?>
        <option class='form_campos' <?php echo verifItemSelected($status, 'ATIVADA') ?> >ATIVADA</options>
        <option class='form_campos' <?php echo verifItemSelected($status, 'DESATIVADA') ?> >DESATIVADA</options>
        <?php
    }

    public static function localMidia($fws_,$debug = false) {
        $objMidiaUpload = new MidiaUpload();
        foreach ($objMidiaUpload->getListaAtributosMidia() as $location => $details) {
            ?>
            <option
                <?php echo verifItemSelected($fws_['localMidia'],  $location) ?>
                    value='<?php echo $location ?>' ><?php echo  $location . ' ('. $details['tipo'] . ') ('.$details['tamanho'].')';  ?>
            </option>
            <?php
        }
    }
    public static function numeroProjeto($fws_,$debug = false) {
        $objP = new TransparenciaDAO();
        foreach ($objP->carregarListaProjetosOptions() as $projeto => $details) {
            ?>
            <option
                <?php echo verifItemSelected($fws_['numero_projeto'],  $details['numeroProjeto']) ?>
                    value='<?php echo $details['numeroProjeto'] ?>' ><?php echo  $details['numeroProjeto'] ?>
            </option>
            <?php
        }
    }

    public static function qtdPedidos($fws_,$debug = false) {
        ?>
        <option class='form_campos' value="-1" <?php echo verifItemSelected($fws_['pedidos'], '-1') ?> > 0</option>
        <option class='form_campos' value="1" <?php echo verifItemSelected($fws_['pedidos'], '1') ?> > 1</option>
        <option class='form_campos' value="2" <?php echo verifItemSelected($fws_['pedidos'], '2') ?> > 2</option>
        <option class='form_campos' value="3" <?php echo verifItemSelected($fws_['pedidos'], '3') ?> > 3</option>
        <option class='form_campos' value="4" <?php echo verifItemSelected($fws_['pedidos'], '4') ?> > 4</option>
        <option class='form_campos' value="5" <?php echo verifItemSelected($fws_['pedidos'], '5') ?> > 5</option>
        <option class='form_campos' value="6" <?php echo verifItemSelected($fws_['pedidos'], '6') ?> > 6</option>
        <option class='form_campos' value="7" <?php echo verifItemSelected($fws_['pedidos'], '7') ?> > 7</option>
        <option class='form_campos' value="8" <?php echo verifItemSelected($fws_['pedidos'], '8') ?> > 8</option>
        <option class='form_campos' value="9" <?php echo verifItemSelected($fws_['pedidos'], '9') ?> > 9</option>
        <option class='form_campos' value="10" <?php echo verifItemSelected($fws_['pedidos'], '10') ?> > 10</option>
        <option class='form_campos' value="11" <?php echo verifItemSelected($fws_['pedidos'], '11') ?> > 11</option>
        <option class='form_campos' value="12" <?php echo verifItemSelected($fws_['pedidos'], '12') ?> > 12</option>
        <option class='form_campos' value="13" <?php echo verifItemSelected($fws_['pedidos'], '13') ?> > 13</option>
        <option class='form_campos' value="14" <?php echo verifItemSelected($fws_['pedidos'], '14') ?> > 14</option>
        <option class='form_campos' value="15" <?php echo verifItemSelected($fws_['pedidos'], '15') ?> > 15</option>
        <?php
    }
    public static function pedidosQtdAte($fws_,$debug = false) {
        ?>
        <option class='form_campos' value="-1" <?php echo verifItemSelected($fws_['pedidosQtdAte'], '-1') ?> > Até 0</option>
        <option class='form_campos' value="1" <?php echo verifItemSelected($fws_['pedidosQtdAte'], '1') ?> > Até 1</option>
        <option class='form_campos' value="2" <?php echo verifItemSelected($fws_['pedidosQtdAte'], '2') ?> > Até 2</option>
        <option class='form_campos' value="3" <?php echo verifItemSelected($fws_['pedidosQtdAte'], '3') ?> > Até 3</option>
        <option class='form_campos' value="4" <?php echo verifItemSelected($fws_['pedidosQtdAte'], '4') ?> > Até 4</option>
        <option class='form_campos' value="5" <?php echo verifItemSelected($fws_['pedidosQtdAte'], '5') ?> > Até 5</option>
        <option class='form_campos' value="6" <?php echo verifItemSelected($fws_['pedidosQtdAte'], '6') ?> > Até 6</option>
        <option class='form_campos' value="7" <?php echo verifItemSelected($fws_['pedidosQtdAte'], '7') ?> > Até 7</option>
        <option class='form_campos' value="8" <?php echo verifItemSelected($fws_['pedidosQtdAte'], '8') ?> > Até 8</option>
        <option class='form_campos' value="9" <?php echo verifItemSelected($fws_['pedidosQtdAte'], '9') ?> > Até 9</option>
        <option class='form_campos' value="10" <?php echo verifItemSelected($fws_['pedidosQtdAte'], '10') ?> > Até 10</option>
        <option class='form_campos' value="11" <?php echo verifItemSelected($fws_['pedidosQtdAte'], '11') ?> > Até 11</option>
        <option class='form_campos' value="12" <?php echo verifItemSelected($fws_['pedidosQtdAte'], '12') ?> > Até 12</option>
        <option class='form_campos' value="13" <?php echo verifItemSelected($fws_['pedidosQtdAte'], '13') ?> > Até 13</option>
        <option class='form_campos' value="14" <?php echo verifItemSelected($fws_['pedidosQtdAte'], '14') ?> > Até 14</option>
        <option class='form_campos' value="15" <?php echo verifItemSelected($fws_['pedidosQtdAte'], '15') ?> > Até 15</option>
        <?php
    }
    public static function pagamentosQtdAte($fws_,$debug = false) {
        ?>
        <option class='form_campos' value="-1" <?php echo verifItemSelected($fws_['pagamentosQtdAte'], '-1') ?> > Até 0</option>
        <option class='form_campos' value="1" <?php echo verifItemSelected($fws_['pagamentosQtdAte'], '1') ?> > Até 1</option>
        <option class='form_campos' value="2" <?php echo verifItemSelected($fws_['pagamentosQtdAte'], '2') ?> > Até 2</option>
        <option class='form_campos' value="3" <?php echo verifItemSelected($fws_['pagamentosQtdAte'], '3') ?> > Até 3</option>
        <option class='form_campos' value="4" <?php echo verifItemSelected($fws_['pagamentosQtdAte'], '4') ?> > Até 4</option>
        <option class='form_campos' value="5" <?php echo verifItemSelected($fws_['pagamentosQtdAte'], '5') ?> > Até 5</option>
        <option class='form_campos' value="6" <?php echo verifItemSelected($fws_['pagamentosQtdAte'], '6') ?> > Até 6</option>
        <option class='form_campos' value="7" <?php echo verifItemSelected($fws_['pagamentosQtdAte'], '7') ?> > Até 7</option>
        <option class='form_campos' value="8" <?php echo verifItemSelected($fws_['pagamentosQtdAte'], '8') ?> > Até 8</option>
        <option class='form_campos' value="9" <?php echo verifItemSelected($fws_['pagamentosQtdAte'], '9') ?> > Até 9</option>
        <option class='form_campos' value="10" <?php echo verifItemSelected($fws_['pagamentosQtdAte'], '10') ?> > Até 10</option>
        <option class='form_campos' value="11" <?php echo verifItemSelected($fws_['pagamentosQtdAte'], '11') ?> > Até 11</option>
        <option class='form_campos' value="12" <?php echo verifItemSelected($fws_['pagamentosQtdAte'], '12') ?> > Até 12</option>
        <option class='form_campos' value="13" <?php echo verifItemSelected($fws_['pagamentosQtdAte'], '13') ?> > Até 13</option>
        <option class='form_campos' value="14" <?php echo verifItemSelected($fws_['pagamentosQtdAte'], '14') ?> > Até 14</option>
        <option class='form_campos' value="15" <?php echo verifItemSelected($fws_['pagamentosQtdAte'], '15') ?> > Até 15</option>
        <?php
    }
    public static function pagamentosQtdMaior($fws_,$debug = false) {
        ?>
        <option class='form_campos' value="-1" <?php echo verifItemSelected($fws_['pagamentosQtdMaior'], '-1') ?> > 0</option>
        <option class='form_campos' value="1" <?php echo verifItemSelected($fws_['pagamentosQtdMaior'], '1') ?> > 1</option>
        <option class='form_campos' value="2" <?php echo verifItemSelected($fws_['pagamentosQtdMaior'], '2') ?> > 2</option>
        <option class='form_campos' value="3" <?php echo verifItemSelected($fws_['pagamentosQtdMaior'], '3') ?> > 3</option>
        <option class='form_campos' value="4" <?php echo verifItemSelected($fws_['pagamentosQtdMaior'], '4') ?> > 4</option>
        <option class='form_campos' value="5" <?php echo verifItemSelected($fws_['pagamentosQtdMaior'], '5') ?> > 5</option>
        <option class='form_campos' value="6" <?php echo verifItemSelected($fws_['pagamentosQtdMaior'], '6') ?> > 6</option>
        <option class='form_campos' value="7" <?php echo verifItemSelected($fws_['pagamentosQtdMaior'], '7') ?> > 7</option>
        <option class='form_campos' value="8" <?php echo verifItemSelected($fws_['pagamentosQtdMaior'], '8') ?> > 8</option>
        <option class='form_campos' value="9" <?php echo verifItemSelected($fws_['pagamentosQtdMaior'], '9') ?> > 9</option>
        <option class='form_campos' value="10" <?php echo verifItemSelected($fws_['pagamentosQtdMaior'], '10') ?> > 10</option>
        <option class='form_campos' value="11" <?php echo verifItemSelected($fws_['pagamentosQtdMaior'], '11') ?> > 11</option>
        <option class='form_campos' value="12" <?php echo verifItemSelected($fws_['pagamentosQtdMaior'], '12') ?> > 12</option>
        <option class='form_campos' value="13" <?php echo verifItemSelected($fws_['pagamentosQtdMaior'], '13') ?> > 13</option>
        <option class='form_campos' value="14" <?php echo verifItemSelected($fws_['pagamentosQtdMaior'], '14') ?> > 14</option>
        <option class='form_campos' value="15" <?php echo verifItemSelected($fws_['pagamentosQtdMaior'], '15') ?> > 15</option>
        <?php
    }
    public static function pagamentosQtdMenor($fws_,$debug = false) {
        ?>
        <option class='form_campos' value="-1" <?php echo verifItemSelected($fws_['pagamentosQtdMenor'], '-1') ?> > 0</option>
        <option class='form_campos' value="1" <?php echo verifItemSelected($fws_['pagamentosQtdMenor'], '1') ?> > 1</option>
        <option class='form_campos' value="2" <?php echo verifItemSelected($fws_['pagamentosQtdMenor'], '2') ?> > 2</option>
        <option class='form_campos' value="3" <?php echo verifItemSelected($fws_['pagamentosQtdMenor'], '3') ?> > 3</option>
        <option class='form_campos' value="4" <?php echo verifItemSelected($fws_['pagamentosQtdMenor'], '4') ?> > 4</option>
        <option class='form_campos' value="5" <?php echo verifItemSelected($fws_['pagamentosQtdMenor'], '5') ?> > 5</option>
        <option class='form_campos' value="6" <?php echo verifItemSelected($fws_['pagamentosQtdMenor'], '6') ?> > 6</option>
        <option class='form_campos' value="7" <?php echo verifItemSelected($fws_['pagamentosQtdMenor'], '7') ?> > 7</option>
        <option class='form_campos' value="8" <?php echo verifItemSelected($fws_['pagamentosQtdMenor'], '8') ?> > 8</option>
        <option class='form_campos' value="9" <?php echo verifItemSelected($fws_['pagamentosQtdMenor'], '9') ?> > 9</option>
        <option class='form_campos' value="10" <?php echo verifItemSelected($fws_['pagamentosQtdMenor'], '10') ?> > 10</option>
        <option class='form_campos' value="11" <?php echo verifItemSelected($fws_['pagamentosQtdMenor'], '11') ?> > 11</option>
        <option class='form_campos' value="12" <?php echo verifItemSelected($fws_['pagamentosQtdMenor'], '12') ?> > 12</option>
        <option class='form_campos' value="13" <?php echo verifItemSelected($fws_['pagamentosQtdMenor'], '13') ?> > 13</option>
        <option class='form_campos' value="14" <?php echo verifItemSelected($fws_['pagamentosQtdMenor'], '14') ?> > 14</option>
        <option class='form_campos' value="15" <?php echo verifItemSelected($fws_['pagamentosQtdMenor'], '15') ?> > 15</option>
        <?php
    }
    public static function qtdPagamentos($fws_,$debug = false) {
        ?>
        <option class='form_campos' value="-1" <?php echo verifItemSelected($fws_['pagamentos'], '-1') ?> > Não pagos</option>
        <option class='form_campos' value="102" <?php echo verifItemSelected($fws_['pagamentos'], '102') ?> > Pagos menos isentos</option>
        <option class='form_campos' value="99" <?php echo verifItemSelected($fws_['pagamentos'], '99') ?> > Pagos</option>
        <option class='form_campos' value="1" <?php echo verifItemSelected($fws_['pagamentos'], '1') ?> > Até 1</option>
        <option class='form_campos' value="2" <?php echo verifItemSelected($fws_['pagamentos'], '2') ?> > Até 2</option>
        <option class='form_campos' value="3" <?php echo verifItemSelected($fws_['pagamentos'], '3') ?> > Até 3</option>
        <option class='form_campos' value="4" <?php echo verifItemSelected($fws_['pagamentos'], '4') ?> > Até 4</option>
        <option class='form_campos' value="5" <?php echo verifItemSelected($fws_['pagamentos'], '5') ?> > Até 5</option>
        <option class='form_campos' value="6" <?php echo verifItemSelected($fws_['pagamentos'], '6') ?> > Até 6</option>
        <option class='form_campos' value="7" <?php echo verifItemSelected($fws_['pagamentos'], '7') ?> > Até 7</option>
        <option class='form_campos' value="8" <?php echo verifItemSelected($fws_['pagamentos'], '8') ?> > Até 8</option>
        <option class='form_campos' value="9" <?php echo verifItemSelected($fws_['pagamentos'], '9') ?> > Até 9</option>
        <option class='form_campos' value="10" <?php echo verifItemSelected($fws_['pagamentos'], '10') ?> > Até 10</option>
        <option class='form_campos' value="11" <?php echo verifItemSelected($fws_['pagamentos'], '11') ?> > Até 11</option>
        <option class='form_campos' value="12" <?php echo verifItemSelected($fws_['pagamentos'], '12') ?> > Até 12</option>
        <option class='form_campos' value="13" <?php echo verifItemSelected($fws_['pagamentos'], '13') ?> > Até 13</option>
        <option class='form_campos' value="14" <?php echo verifItemSelected($fws_['pagamentos'], '14') ?> > Até 14</option>
        <option class='form_campos' value="15" <?php echo verifItemSelected($fws_['pagamentos'], '15') ?> > Até 15</option>
        <?php
    }

    public static function grauEscolaridade($fws_, $debug=false){
        ?>
        <option value="" <?php echo verifItemSelected($fws_['grau_escolaridade'], '') ?>></option>
        
        <?php if (COD_EVENTO!='115'){ ?>
            <option value="Ensino Fundamental" <?php echo verifItemSelected($fws_['grau_escolaridade'], 'Ensino Fundamental') ?>>Ensino Fundamental</option>
            <option value="Ensino Fundamental Incompleto" <?php echo verifItemSelected($fws_['grau_escolaridade'], 'Ensino Fundamental Incompleto') ?>>Ensino Fundamental Incompleto</option>
        <?php } ?>
        <option value="Ensino Médio" <?php echo verifItemSelected($fws_['grau_escolaridade'], 'Ensino Médio') ?>>Ensino Médio</option>
        <option value="Ensino Médio Incompleto" <?php echo verifItemSelected($fws_['grau_escolaridade'], 'Ensino Médio Incompleto') ?>>Ensino Médio Incompleto</option>
        <option value="Ensino Superior" <?php echo verifItemSelected($fws_['grau_escolaridade'], 'Ensino Superior') ?>>Ensino Superior</option>
        <option value="Ensino Superior Incompleto" <?php echo verifItemSelected($fws_['grau_escolaridade'], 'Ensino Superior Incompleto') ?>>Ensino Superior Incompleto</option>
        <option value="Pós-graduação" <?php echo verifItemSelected($fws_['grau_escolaridade'], 'Pós-graduação') ?>>Pós-graduação</option>
        <option value="Pós-graduação Incompleta" <?php echo verifItemSelected($fws_['grau_escolaridade'], 'Pós-graduação Incompleta') ?>>Pós-graduação Incompleta</option>
        <option value="Mestrado" <?php echo verifItemSelected($fws_['grau_escolaridade'], 'Mestrado') ?>>Mestrado</option>
        <option value="Mestrado Incompleto" <?php echo verifItemSelected($fws_['grau_escolaridade'], 'Mestrado Incompleto') ?>>Mestrado Incompleto</option>
        <option value="Doutorado" <?php echo verifItemSelected($fws_['grau_escolaridade'], 'Doutorado') ?>>Doutorado</option>
        <option value="Doutorado Incompleto" <?php echo verifItemSelected($fws_['grau_escolaridade'], 'Doutorado Incompleto') ?>>Doutorado Incompleto</option>
        <?php
    }
    public static function tipoAtividades($tipo_atividade,$debug = false) {
        ?>
        <option <?php echo verifItemSelected($tipo_atividade, 'Abertura') ?> value='Abertura'>Abertura</option>
        <option <?php echo verifItemSelected($tipo_atividade, 'Área') ?> value='Área'>Área</option>
        <option <?php echo verifItemSelected($tipo_atividade, 'Agenda') ?> value='Agenda'>Agenda</option>
        <option <?php echo verifItemSelected($tipo_atividade, 'Ato Público') ?> value='Ato Público'>Ato Público</option>
        <option <?php echo verifItemSelected($tipo_atividade, 'Aula') ?> value='Aula'>Aula</option>
        <option <?php echo verifItemSelected($tipo_atividade, 'Comunicações Orais') ?> value='Comunicações Orais'>Comunicações Orais</option>
        <option <?php echo verifItemSelected($tipo_atividade, 'Conferência') ?> value='Conferência'>Conferência</option>
        <option <?php echo verifItemSelected($tipo_atividade, 'Conferência Abertura') ?> value='Conferência Abertura'>Conferência Abertura</option>
        <option <?php echo verifItemSelected($tipo_atividade, 'Conferência Encerramento') ?> value='Conferência Encerramento'>Conferência Encerramento</option>
        <option <?php echo verifItemSelected($tipo_atividade, 'Encerramento') ?> value='Encerramento'>Encerramento</option>
        <option <?php echo verifItemSelected($tipo_atividade, 'Conteúdo') ?> value='Conteúdo'>Conteúdo</option>
        <option <?php echo verifItemSelected($tipo_atividade, 'Divulgação') ?> value='Divulgação'>Divulgação</option>
        <option <?php echo verifItemSelected($tipo_atividade, 'Encontro') ?> value='Encontro'>Encontro</option>
        <option <?php echo verifItemSelected($tipo_atividade, 'Fórum') ?> value='Fórum'>Fórum</option>
        <option <?php echo verifItemSelected($tipo_atividade, 'Curso') ?> value='Curso'>Curso</option>
        <option <?php echo verifItemSelected($tipo_atividade, 'Mesa-Redonda') ?> value='Mesa-Redonda'>Mesa-Redonda</option>
        <option <?php echo verifItemSelected($tipo_atividade, 'Minicurso') ?> value='Minicurso'>Minicurso</option>
        <option <?php echo verifItemSelected($tipo_atividade, 'Módulo') ?> value='Módulo'>Módulo</option>
        <option <?php echo verifItemSelected($tipo_atividade, 'Oficina') ?> value='Oficina'>Oficina</option>
        <option <?php echo verifItemSelected($tipo_atividade, 'Outras Atividades') ?> value='Outras Atividades'>Outras Atividades</option>
        <option <?php echo verifItemSelected($tipo_atividade, 'Painéis') ?> value='Painéis'>Painéis</option>
        <option <?php echo verifItemSelected($tipo_atividade, 'Palestra') ?> value='Palestra'>Palestra</option>
        <option <?php echo verifItemSelected($tipo_atividade, 'Produto') ?> value='Produto'>Produto</option>
        <option <?php echo verifItemSelected($tipo_atividade, 'Roda de Conversa') ?> value='Roda de Conversa'>Roda de Conversa</option>
        <option <?php echo verifItemSelected($tipo_atividade, 'Serviço') ?> value='Serviço'>Serviço</option>
        <option <?php echo verifItemSelected($tipo_atividade, 'Simpósio') ?> value='Simpósio'>Simpósio</option>
        <option <?php echo verifItemSelected($tipo_atividade, 'Simpósio Satélite') ?> value='Simpósio Satélite'>Simpósio Satélite</option>
        <option <?php echo verifItemSelected($tipo_atividade, 'Tema') ?> value='Tema'>Tema</option>
        <option <?php echo verifItemSelected($tipo_atividade, 'Turma') ?> value='Turma'>Turma</option>
        <option <?php echo verifItemSelected($tipo_atividade, 'Tópico') ?> value='Tópico'>Tópico</option>
        <option <?php echo verifItemSelected($tipo_atividade, 'Visita Técnica') ?> value='Visita Técnica'>Visita Técnica</option>
        <option <?php echo verifItemSelected($tipo_atividade, 'Workshop') ?> value='Workshop'>Workshop</option>
        <option <?php echo verifItemSelected($tipo_atividade, 'NDA') ?> value='NDA'>NDA</option>
    <?php
    }         
    public static function diaSemana($fws_,$debug = false) {
        $diaSemana = $fws_['diaSemana'];
        ?>    
        <option value="Domingo" <?php echo verifItemSelected($diaSemana, 'Domingo') ?> >Domingo</option>
        <option value="Segunda-feira" <?php echo verifItemSelected($diaSemana, 'Segunda-feira') ?> >Segunda-feira</option>
        <option value="Terça-feira" <?php echo verifItemSelected($diaSemana, 'Terça-feira') ?> >Terça-feira</option>
        <option value="Quarta-feira" <?php echo verifItemSelected($diaSemana, 'Quarta-feira') ?> >Quarta-feira</option>
        <option value="Quinta-feira" <?php echo verifItemSelected($diaSemana, 'Quinta-feira') ?> >Quinta-feira</option>
        <option value="Sexta-feira" <?php echo verifItemSelected($diaSemana, 'Sexta-feira') ?> >Sexta-feira</option>
        <option value="Sábado" <?php echo verifItemSelected($diaSemana, 'Sábado') ?> >Sábado</option>
    <?php
    }
    public static function avaliacaoStatus($status, $debug = false) {
        ?>
        <option class='form_campos' <?php echo verifItemSelected($status, ' Aprovado') ?> >Validado</option>
        <option class='form_campos' <?php echo verifItemSelected($status, 'Reprovado') ?> >Não Validado</option>
        <option class='form_campos' <?php echo verifItemSelected($status, 'Necessário Revormular') ?> >Reformular</option>
        <?php
    }
    public static function planoContasDebito($fws_, $debug = false) {
        try {
            $objPlanoContas = new PlanoContas();
            $objPlanoContas->carregarListaPlanoContasDebito($debug);
            $lista = $objPlanoContas->getListaPlanoContas();
            if ($lista) {
                foreach ($lista as $objL) {
                    ?>
                    <option
                        <?php echo verifItemSelected($fws_['cod_plano_contas'], $objL->getCodPlanoContas()) ?>
                        value='<?php echo $objL->getCodPlanoContas()?>' ><?php echo trim($objL->getCodigoFinanceiro() . ' - ' . ($objL->getNomePlanoContas() ? $objL->getNomePlanoContas() : $objL->getDescricao()), ' -') ?></option>
                    <?php
                }
            }
        } catch (Exception $exc) {
            echo "<option value=''>".$exc->getMessage()."</option>";
        }
    }
    public static function planoContasCredito($fws_, $debug = false) {
        try {
            $objPlanoContas = new PlanoContas();
            $objPlanoContas->carregarListaPlanoContasCredito($debug);
            $lista = $objPlanoContas->getListaPlanoContas();
            if ($lista) {
                foreach ($lista as $objL) {
                    ?>
                    <option
                        <?php echo verifItemSelected($fws_['cod_plano_contas'], $objL->getCodPlanoContas()) ?>
                        value='<?php echo $objL->getCodPlanoContas()?>' ><?php echo trim($objL->getCodigoFinanceiro() . ' - ' . ($objL->getNomePlanoContas() ? $objL->getNomePlanoContas() : $objL->getDescricao()), ' -') ?></option>
                    <?php
                }
            }
        } catch (Exception $exc) {
            echo "<option value=''>".$exc->getMessage()."</option>";
        }
    }
    /**
     * Contas para FILTRO de listagem: todas as que aceitam lancamento, sem
     * separar por natureza (o filtro nao sabe se o usuario procura debito ou
     * credito).
     */
    public static function planoContasFiltro($fws_, $debug = false) {
        try {
            $objPlanoContas = new PlanoContas();
            $objPlanoContas->carregarListaPlanoContas(false, $debug);
            $lista = $objPlanoContas->getListaPlanoContas();
            if ($lista) {
                foreach ($lista as $objL) {
                    if (!$objL->getAceitaLancamento()) {
                        continue;
                    }
                    ?>
                    <option value='<?php echo $objL->getCodPlanoContas()?>' ><?php echo trim($objL->getCodigoFinanceiro() . ' - ' . ($objL->getNomePlanoContas() ? $objL->getNomePlanoContas() : $objL->getDescricao()), ' -') ?></option>
                    <?php
                }
            }
        } catch (Exception $exc) {
            // evento ainda sem catalogo: filtro fica so com o "Todas"
        }
    }
    public static function planoContasPai($fws_, $debug = false) {
        try {
            $objPlanoContas = new PlanoContas();
            $objPlanoContas->carregarListaPlanoContasPai($debug);
            $lista = $objPlanoContas->getListaPlanoContas();
            if ($lista) {
                foreach ($lista as $objL) {
                    ?>
                    <option
                        <?php echo verifItemSelected($fws_['cod_plano_contas'], $objL->getCodPlanoContas()) ?>
                        value='<?php echo $objL->getCodPlanoContas()?>' data-tipo='<?php echo $objL->getTipoConta()?>' ><?php echo trim($objL->getCodigoFinanceiro() . ' - ' . ($objL->getNomePlanoContas() ? $objL->getNomePlanoContas() : $objL->getDescricao()), ' -') ?></option>
                    <?php
                }
            }
        } catch (Exception $exc) {
            echo "<option value=''>".$exc->getMessage()."</option>";
        }
    }
    public static function rateiosPai($fws_, $debug = false) {
        try {
            $objRateios = new Rateios();
            $objRateios->carregarListaRateiosPai($debug);
            $lista = $objRateios->getListaRateios();
            if ($lista) {
                foreach ($lista as $objL) {
                    ?>
                    <option
                        <?php echo verifItemSelected($fws_['cod_rateio'], $objL->getCodRateio()) ?>
                        value='<?php echo $objL->getCodRateio()?>'>
                        <?php echo $objL->getCodigoFinanceiroRateio() .' '. $objL->getNomeRateio() ?>
                    </option>
                    <?php
                }
            }
        } catch (Exception $exc) {
            //echo "<option value=''>".$exc->getMessage()."</option>";
        }
    }
    /**
     * Fontes de recurso do evento (de qual conta saiu/entrou o dinheiro).
     */
    public static function fontesRecurso($fws_, $debug = false) {
        try {
            $objFonte = new FontesRecurso();
            $objFonte->carregarListaFontesRecurso(false, $debug);
            $lista = $objFonte->getListaFontesRecurso();
            if ($lista) {
                foreach ($lista as $objL) {
                    ?>
                    <option
                        <?php echo verifItemSelected($fws_['codFontesRecurso'], $objL->getCodFontesRecurso()) ?>
                        value='<?php echo $objL->getCodFontesRecurso() ?>'>
                        <?php echo $objL->getNomeFontesRecurso() ?>
                    </option>
                    <?php
                }
            }
        } catch (Exception $exc) {
        }
    }

    /**
     * Favorecido do lancamento: os inscritos do evento. Quem recebe (ministrante,
     * palestrante, fornecedor) esta cadastrado aqui, com o papel definido pelo
     * perfil -- cliente e fornecedor sao a mesma pessoa em momentos diferentes.
     */
    public static function inscritoFavorecido($fws_, $debug = false) {
        try {
            ConexaoDB::getInstance();
            $conex = ConexaoDB::getConexaoDB();
            $sql = sprintf("SELECT i.cod_inscrito, i.nome_inscrito, i.cpf, p.titulo AS perfil
                            FROM tab_inscritos i
                            LEFT JOIN tab_opcoes_perfis p ON p.cod_perfil = i.cod_perfil AND p.cod_evento = i.cod_evento
                            WHERE i.cod_evento = '%s' AND i.nome_inscrito IS NOT NULL AND i.nome_inscrito <> ''
                            ORDER BY i.nome_inscrito", COD_EVENTO);
            $conex->debug = $debug;
            $rs = $conex->Execute($sql);
            if ($rs) {
                foreach ($rs as $r) {
                    $rotulo = $r['nome_inscrito'];
                    if (trim($r['cpf'])) {
                        $rotulo .= ' - ' . $r['cpf'];
                    }
                    if (trim($r['perfil'])) {
                        $rotulo .= ' (' . strip_tags($r['perfil']) . ')';
                    }
                    ?>
                    <option
                        <?php echo verifItemSelected($fws_['codInscrito'], $r['cod_inscrito']) ?>
                        value='<?php echo $r['cod_inscrito'] ?>'><?php echo $rotulo ?></option>
                    <?php
                }
            }
        } catch (Exception $exc) {
        }
    }

    public static function centroCusto($fws_, $debug = false) {
        try {
            $objCentroCusto = new CentroCusto();
            $objCentroCusto->carregarListaCentroCustoPai($debug);
            $lista = $objCentroCusto->getListaCentroCusto();
            if ($lista) {
                foreach ($lista as $objL) {
                    ?>
                    <option
                        <?php echo verifItemSelected($fws_['codCentroCusto'], $objL->getCodCentroCusto()) ?>
                        value='<?php echo $objL->getCodCentroCusto()?>'>
                        <?php echo $objL->getNomeCentroCusto() ?>
                    </option>
                    <?php
                }
            }
        } catch (Exception $exc) {
            //echo "<option value=''>".$exc->getMessage()."</option>";
        }
    }

    public static function centroCustoPai($fws_, $debug = false) {
        try {
            $objCentroCusto = new CentroCusto();
            $objCentroCusto->carregarListaCentroCustoPai($debug);
            $lista = $objCentroCusto->getListaCentroCusto();
            if ($lista) {
                foreach ($lista as $objL) {
                    ?>
                    <option
                        <?php echo verifItemSelected($fws_['cod_centro_custo'], $objL->getCodCentroCusto()) ?>
                        value='<?php echo $objL->getCodCentroCusto()?>'>
                        <?php echo $objL->getCodigoFinanceiro() .' '. $objL->getDescricao() ?>
                    </option>
                    <?php
                }
            }
        } catch (Exception $exc) {
            echo "<option value=''>".$exc->getMessage()."</option>";
        }
    }
}
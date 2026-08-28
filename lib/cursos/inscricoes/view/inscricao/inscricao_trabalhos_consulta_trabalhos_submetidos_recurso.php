<?php        
/*@todo aqui é preciso criar campo para decidir se avaliação será por individuo ou por trabalho*/
if($objEvConf->getTrabAvaliacaoDivulgar()==true or in_array($objEv->getCodEvento(), [80])){
//    echo __LINE__.'-';
    if ($objEvConf->getTrabAvaliacaoPermitirRecurso()==1){
        // echo __LINE__.'-';
        if($objEvConf->getMsgTrabalhoRecursoAtivar()==true){
            // echo __LINE__.'-';
            echo $objEvConf->getMsgTrabalhoRecursoTexto();
        }
    }
    try {
        //if($naoMostrar){
        /**@todo Avaliação de Inscrito, nem todo projeto tem avaliação por inscrito, portanto
        * é preciso criar uma lógica para lidar com isso.
        */
            $objAvalInsc = new AvaliacaoInscrito();
            $objAvalInsc->setCodEvento($objInscrito->getCodEvento());
            $objAvalInsc->setCodInscrito($objInscrito->getCodInscrito());
            $objAvalInsc->carregarListaAvaliacaoInscritoByIdInscrito();
            foreach ($objAvalInsc->getListaAvaliacaoInscrito() as $objLAI){
                switch ($objLAI->getClassificacaoStatus()) {
                    case 'H':
                        $corClassificacaoStatus='green';
//                            $OP_SUBMISSAO_TRABALHO_ENCERRADA = true;
                        $permitirRecurso = true;
                        if(COD_EVENTO==107){
                            $permitirRecurso = false; // não permite recurso para habilitados
                        }
                        break;
                    case 'I':
                        $corClassificacaoStatus='red';
//                            $OP_SUBMISSAO_TRABALHO_ENCERRADA = true;
                        $permitirRecurso = true;                                
                        break;
                    default:
                        $corClassificacaoStatus='black';
                        break;
                }                        
                if($objEvConf->getCodEvento()==107){
                    echo "<br>";
                    echo "<br><b><span style='text-size:15px;margin-top:5px;'>"
                    . "Parecer Referente Requerimento de Isenção:</span></b>";
                    echo "<br>";
                    echo $objLAI->getParecerAvaliador();
                    echo "<span style='text-size:14px;color:".$corClassificacaoStatus."'>";
                    echo "<br>Requerimento: ".$objLAI->getDescClassificacaoStatus();
                    echo "</span>";                            
                }else{
                    echo "<br>";
                    echo "<br><b><span style='text-size:15px;margin-top:5px;'>"
                        . "Resultado da Fase de Avaliação Documental:</span></b>";
//                    echo "<br>Documentação: ".$objLAI->getDescSitArquivo();
                    if($objLAI->getClassificacaoStatus()=='H' or $objLAI->getClassificacaoStatus()=='I'){
                        if($objLAI->getPontosTotalConfirmado()!=''){
                        echo "<br><b>Pontuação na Avaliação:</b> " .$objLAI->getPontosTotalConfirmado()." pontos";
                        }
                    }
                    /** @todo nem todos editais nem colocação */
                    if ($objLAI->getClassificacaoStatus()=='H'){
                        echo "<br><b>Colocação:</b> ";
                        echo "Consultar os editais de classificação";
                        // if(is_int($objLAI->getClassificacao())){
                        //     echo $objLAI->getClassificacao();
                        // }else{
                        //     /*Situação por falta de documentação*/
                        //     echo $objLAI->getClassificacao();
                        // }
                    }

                    echo "<br><b>Situação da Classificação Inicial:</b> ";
                    echo "<span style='text-size:14px;color:".$corClassificacaoStatus."'>";
                    echo $objLAI->getDescClassificacaoStatus();
                    echo "</span>";
                    echo "<br><b>Parecer da Avaliação:</b> ";
                    echo $objLAI->getParecerAvaliador()? $objLAI->getParecerAvaliador() : "";
                    if($objEvConf->getTrabRecursoParecerDivulgar()==1){
                        if($objLAI->getRecursoParecer()!=''){
                            echo "<br><b>Parecer da Avaliação Sobre o Recurso:</b> ";
                            echo $objLAI->getRecursoParecer();
                        }
                    }
                }
                    if ($objLAI->getClassificacaoStatusFinal()!=''){ 
                        switch ($objLAI->getClassificacaoStatusFinal()) {
                            case 'H':
                                $corClassificacaoStatusFinal='green';
                                break;
                            case 'I':
                                $corClassificacaoStatusFinal='red';
                                break;
                            default:
                                $corClassificacaoStatusFinal='black';
                                break;
                        }                        
                        echo "<br><b>Situação Final:</b> ";
                        echo "<span style='text-size:14px;color:".$corClassificacaoStatusFinal."'>";
                        echo $objLAI->getDescClassificacaoStatusFinal();
                    }
                    echo "</span>";
                    if($objLAI->getPontosTotalFinal()!=''){
                        echo "<br><b>Pontuação Final:</b> " .$objLAI->getPontosTotalFinal()." pontos";
                    }                        
                    ?>
                    <?php
                    /** @todo verificar necessidade de exibir texto */
                    /*
                    <br><b><font color='red'>Atenção:</b></font>
                    <ul>
                        <li>A habilitação não garante a participação no projeto.</li>
                        <li>Para consultar sua classificação geral consulte os editais de publicação disponíveis no site:<br><a href='https://hutec.org.br/editais' target='_blank'>Hutec:  https://hutec.org.br/editais</a></li>
                    </ul>
                    */
                    ?>
                    <?php
                    // <br>Sendo assim, consta como habilitados os que tiveram notas mais altas e que pela ordem ficaram dentro da quantidade de vagas.";
                
            }
        //}
    } catch (Exception $exc) {
        if(!$objEvConf->getMsgTrabalhoPadraoOcultar()==true){
            echo $exc->getMessage();
        }
    }
    if(($objEvConf->getTrabAvaliacaoPermitirRecurso()==1 or in_array($objEv->getCodEvento(), [80]))
        and ($permitirRecurso==1)){ 
        if($_SERVER['REMOTE_ADDR']=='177.82.0.288'){
            // $debug=true;
            $OP_PERMITIR_SOLICITAR_RECURSO=true;
            $permitirRecurso = true;
        }else{
            $OP_PERMITIR_SOLICITAR_RECURSO=false;
            $permitirRecurso = false;
            /** @todo As vezes o pedido de recurso é necessário até para quem foi habilitado
             * mas as vezes quem foi habilitado não tem necessidade de pedir recurso, verificar necessidade de ajuste */
                $recursoDatIni = formatDateHoraTrans($objEvConf->getTrabRecursoDatIni(),'YYYY-MM-DD HH:II:SS', 'YYYYMMDDHHII');
            $recursoDatFim = formatDateHoraTrans($objEvConf->getTrabRecursoDatFim(),'YYYY-MM-DD HH:II:SS', 'YYYYMMDDHHII');
            if( Date('YmdHi')>=$recursoDatIni && Date('YmdHi')<= $recursoDatFim){
                /**encerrado */
                $OP_PERMITIR_SOLICITAR_RECURSO=true;
                $permitirRecurso = true;
                echo "<br><br><font color='red'>Apresentação de Recurso disponível até ".formatDateView($objEvConf->getTrabRecursoDatFim())."!</font>";                    
            }else if(Date('YmdHi')>=$recursoDatFim){
                /**disponível */
                echo "<br><br><font color='red'>Apresentação de Recurso Encerrada em ".formatDateView($objEvConf->getTrabRecursoDatFim())."</font>";     
            }else{
                $OP_PERMITIR_SOLICITAR_RECURSO=false;
                $permitirRecurso = false;
                echo "<br><br><font color='red'>Apresentação de Recurso disponível a partir de: ".formatDateView($objEvConf->getTrabRecursoDatIni())."!</font>";
            }
        }
    }
    // echo __LINE__.'-';
    /** @todo avaliação trabalho, tratar melhor a questão de permitir recursos */
    if ($objEvConf->getTrabAvaliacaoPermitirRecurso() == 1 or $permitirRecurso==1){ 
        // if($objLAI->getClassificacaoStatus()=='I') {
            //  echo __LINE__.'-';
            //if ($objInscrito->getTotalTrabalhos() < $FWS_Numero_Maximo_Trabalhos) {
            If ($OP_PERMITIR_SOLICITAR_RECURSO == true) {
                // echo __LINE__.'-';
                // echo "<h4>Obs:
                // <br>Caso.</h4>";
                echo "<hr style='border:1;width:100%'>";
                echo "<br><b><span style='text-size:15px;margin-top:5px;'>Neste momento, sua situação está:</span></b><br>";
                if($objInscrito->getCodIsento() or $objInscrito->getTotalPagamentos()>=1){
                    $recurso_trabalho_solicitar = 0;

                    $lista = [38594, 38647, 38654, 38619, 38586, 38587, 38526, 38531, 38082, 38501, 38681];
                    if (in_array($objInscrito->getCodInscrito(), $lista)) {
                        echo "<span style='text-size:15px;color:red'>Indeferida para fase de avaliação de documentos: pagamento da taxa de inscrição não detectado</span><br>";
                        $recurso_trabalho_solicitar = 1;
                    }else{
                        echo "<span style='text-size:15px;color:green'>Homologado para fase de avaliação de documentos</span><br>";
                    }
                }else{
                    echo "<span style='text-size:15px;color:red'>Indeferida para fase de avaliação de documentos: pagamento da taxa de inscrição não detectado</span><br>";
                    $recurso_trabalho_solicitar = 1;
                }

                $objAvalInsc = new AvaliacaoInscrito();
                $objAvalInsc->setCodEvento($objInscrito->getCodEvento());
                $objAvalInsc->setCodInscrito($objInscrito->getCodInscrito());
                if($objAvalInsc->solicitouRecurso()){
                    // Pedido de recurso já registrado
                    $recurso_solicitado = 1;
                    $cod_trabalho = $objAvalInsc->getCodRecurso();
                } 
                if ($recurso_solicitado == 1) {
                    echo "<span style='text-size:15px;color:green'>Pedido de Recurso já Registrado!</span><br>";
                }
                // } else {
                if ($recurso_trabalho_solicitar == 1) {
                    echo "
                    <br>
                    <FORM NAME='exit' METHOD='POST' ACTION='index.php?content=view/inscricao/inscricao_trabalhos_cadastro_dados_trabalho_recurso.php'>
                        <input type='hidden' name='cod_trabalho' value='{$cod_trabalho}'>
                        <button class=\"btn btn btn-primary \" type=\"submit\">
                            " . $objEvConf->getTxtBtnSolicitarRecurso() . ".</button>
                    </FORM>
                    ";
                }
                echo "<br><hr style='border:1;width:100%'>";
            }
            //}
        // }
    }
}
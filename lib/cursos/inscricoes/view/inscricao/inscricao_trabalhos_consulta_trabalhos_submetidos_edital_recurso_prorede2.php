<?php
require_once(dirname(__FILE__)."/../../include/config.php");
#require_once(dirname(__FILE__)."/../../include/inscricao_request_variaveis.php");

if ($objPerfil->getVerificarEnvioTrabalho()==1){
    #Fazendo conexão com o banco
    $Conexao_db = FWA_Conexao("OpcTec");
    
    /*$FWS_Numero_Maximo_Trabalhos esta definido no config.php*/
    if($FWS_Numero_Maximo_Trabalhos_Atrelado_ao_Perfil){
        switch ($objInscrito->getCodPerfil()) {
            case 1:
            case 3:
            case 5:
                $FWS_Numero_Maximo_Trabalhos = 2;
                break;
            case 2:
            case 4:
            case 6:
                $FWS_Numero_Maximo_Trabalhos = 1;
                break;
            default:
                $FWS_Numero_Maximo_Trabalhos = 1;
                break;
        }   
    }
    if ($objInscrito->getCodInscrito() == '4751' or $objInscrito->getCodInscrito() == '4597') {
        $FWS_Numero_Maximo_Trabalhos = 3;
    }
    if ($objPerfil->getVerificarEnvioTrabalho()) {
        $FWS_Numero_Maximo_Trabalhos = $objEvConf->getTrabSubmissaoQtd();
    }
    if($FWS_Numero_Maximo_Trabalhos == 0){
        echo "<br>Perfil de inscrição não permite envio de anexos";
    }else{
        /** Tratando as mensagem e avisos da área de trabalho */
        $mostrarMsgTrabahoTexto = true;
        if(($objInscrito->getTotalTrabalhos()>=1) and ($objEvConf->getMsgTrabalhoAposAnexarOcultar()==true)){
            $mostrarMsgTrabahoTexto = false;
        }
        if($objInscrito->getStatusProcesso()=='PARTICIPANDO'){
            $mostrarMsgTrabahoTexto = false;
        }
        if($objInscrito->getStatusProcesso()=='DESISTENTE'){ 
            $mostrarMsgTrabahoTexto = false;
        }
        if($objEvConf->getMsgTrabalhoAtivar()==true){
            if ($mostrarMsgTrabahoTexto==true){
                echo $objEvConf->getMsgTrabalhoTexto();
            }
        }else{    
            if(!$objEvConf->getMsgTrabalhoPadraoOcultar()==true){
                // echo "<br>Sua inscrição lhe permite enviar: " . $FWS_Numero_Maximo_Trabalhos . " ".strtolower($objEvConf->getDescTrabAnexoTipo());
                //                if(!in_array($objEv->getCodEvento(), array(37,38,46))){
                //                    echo "<br>Até o momento você enviou: " . $objInscrito->getTotalTrabalhos() . "  ". strtolower($objEvConf->getDescTrabAnexoTipo());
                //                }
            }
        }
        if(($objInscrito->getTotalTrabalhos()>=1) and ($objEvConf->getMsgTrabalhoAposAnexarAtivar()==true and $objInscrito->getStatusProcesso()=='INSCRITO')){
            // echo $objEvConf->getMsgTrabalhoAposAnexarTexto();
        }
        /*@todo aqui é preciso criar campo para decidir se avaliação será por individuo ou por trabalho*/
        if($objEvConf->getTrabAvaliacaoDivulgar()==true or in_array($objEv->getCodEvento(), [80,115])){
            // echo __LINE__.'-';
            if ($objEvConf->getTrabAvaliacaoPermitirRecurso()==1){
                //  echo __LINE__.'-';
                if($objEvConf->getMsgTrabalhoRecursoAtivar()==true){
                    //  echo __LINE__.'-';
                    echo $objEvConf->getMsgTrabalhoRecursoTexto();
                }
            }
            try {
                // echo __LINE__.'-';
                // if($naoMostrar){
                /**@todo Avaliação de Inscrito, nem todo projeto tem avaliação por inscrito, portanto
                * é preciso criar uma lógica para lidar com isso.
                */
                    $objAvalInsc = new AvaliacaoInscrito();
                    $objAvalInsc->setCodEvento($objInscrito->getCodEvento());
                    $objAvalInsc->setCodInscrito($objInscrito->getCodInscrito());
                    // echo __LINE__.'-';
                    $objAvalInsc->carregarListaAvaliacaoInscritoByIdInscritoProRedeFase1();
                    // echo __LINE__.'-';
                    foreach ($objAvalInsc->getListaAvaliacaoInscrito() as $objLAI){
                        // echo __LINE__.'-';
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
                        if($objEvConf->getCodEvento()==115){
                            // echo __LINE__.'-';
                            // Cor do texto do resultado preliminar
                                if ($objLAI->getStatusProcesso() == 'OK') {
                                    $permitirRecurso = false; 
                                    $corClassificacaoStatus = 'green';
                                } else {
                                    $permitirRecurso = true; 
                                    $corClassificacaoStatus = 'red';
                                }
                                $corClassificacaoStatus = '';

                                /**Resultado  Resultado e publicação da Classificação Preliminar */
                                echo "<br><b><span style='font-size:15px;margin-top:5px;'>";
                                echo "Resultado e publicação da Classificação Preliminar (avaliação de documentos enviados):";
                                echo "</span></b>";
                                echo "<br>";
                                echo "<span style='font-size:14px;color:{$corClassificacaoStatus};'>";
                                echo "Inscrição: Aguardando liberação de Classificação Preliminar";
                                // echo $objLAI->getStatusProcesso()=='OK' ? 'HOMOLOGADA' : 'NÃO HOMOLOGADA';
                                echo "</span>";

                                if($naoMostrar){
                                    // echo "<br>";
                                    echo "<br><b><span style='font-size:15px;margin-top:5px;'>";
                                    echo "Resultado de Avaliação Préliminar referente Capítulo 9, item 9.2 do Edital 032/2025 (envio de documentos obrigatórios):";
                                    echo "</span></b>";

                                    echo "<br>";
                                    echo "<span style='font-size:14px;color:{$corClassificacaoStatus};'>";
                                    echo "Inscrição: ";
                                    echo $objLAI->getStatusProcesso()=='OK' ? 'HOMOLOGADA' : 'NÃO HOMOLOGADA';
                                    echo "</span>";

                                    #----
                                    if (in_array($objInscrito->getCodInscrito(), [41501, 41125, 41040, 40992, 40990, 40978])){ 
                                        $objAvalInsc->getAvaliacaoRecursoProRedeFase1();
                                        echo "<br><b>Resultado da Avaliação do Recurso:</b> ";
                                        // echo $objLAI->getParecerAvaliador()? $objLAI->getParecerAvaliador() : "";
                                        // if($objEvConf->getTrabRecursoParecerDivulgar()==1){
                                            // if($objLAI->getRecursoParecer()!=''){
                                                // echo "<br><b>Parecer da Avaliação Sobre o Recurso:</b> ";
                                                // echo $objLAI->getRecursoParecer();
                                                echo "<br>".$objAvalInsc->getParecerAvaliador().'<br>';
                                            // }    
                                        // }
                                        switch ($objInscrito->getCodInscrito()) {
                                            case '41501':
                                                echo "<font color='green'>Inscrição: HOMOLOGADA</font>";
                                                break;
                                            case '40992':
                                                echo "<font color='green'>Inscrição: HOMOLOGADA</font>";
                                                break;
                                            case '41125':
                                                echo "<font color='red'>Inscrição: NÃO HOMOLOGADA</font>    ";
                                                break;
                                            case '41040':
                                                echo "<font color='red'>Inscrição: NÃO HOMOLOGADA</font>    ";
                                                break;
                                            case '40990':
                                                echo "<font color='red'>Inscrição: NÃO HOMOLOGADA</font>    ";
                                                break;
                                            case '40978':
                                                echo "<font color='red'>Inscrição: NÃO HOMOLOGADA</font>    ";
                                                break;
                                            default:
                                                
                                                break;
                                        }
                                    }
                                }
                                #----

                                // ============================
                                // LISTA DE DOCUMENTOS
                                // ============================

                                $docsObrigatorios = [
                                    'Documento de identificação com foto'                 => $objLAI->getTemDocFoto(),
                                    'CPF'                                                 => $objLAI->getTemCpf(),
                                    'Título Eleitoral'                                    => $objLAI->getTemTitulo(),
                                    'Comprovação da qualificação mínima exigida'          => $objLAI->getTemQualificacao(),
                                    'Comprovante de endereço ou declaração'               => $objLAI->getTemEnderecoOuDeclaracao(),
                                    // 'CTPS ou outro comprovante de experiência profissional' => $objLAI->getTemCtpsOuComp(),
                                    // 'Currículo (descritivo e documental)'                 => $objLAI->getTemCurriculo(),
                                    // 'Documentos para pontuação nos critérios (Anexo II)'  => $objLAI->getTemCriterios(),
                                ];

                                echo "<br><br>";
                                echo "<b><span style='font-size:14px;'>Situação dos documentos obrigatórios:</span></b>";
                                echo "<ul style='font-size:13px;'>";

                                foreach ($docsObrigatorios as $rotulo => $valor) {
                                    // considerando 0 / 1 ou false / true
                                    $tem   = ((int)$valor === 1);
                                    $cor   = $tem ? 'green' : 'red';
                                    $texto = $tem ? 'ENTREGUE' : 'NÃO ENTREGUE';

                                    echo "<li>{$rotulo}: <span style='color:{$cor};'>{$texto}</span></li>";
                                }

                                echo "</ul>";
                            
                        }else if($objEvConf->getCodEvento()==107){
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
                                // echo "<br><b>Situação Final:</b> ";
                                // echo "<span style='text-size:14px;color:".$corClassificacaoStatusFinal."'>";
                                // echo $objLAI->getDescClassificacaoStatusFinal();
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
                // }
            } catch (Exception $exc) {
                if(!$objEvConf->getMsgTrabalhoPadraoOcultar()==true){
                    echo $exc->getMessage();
                }
            }
            if(($objEvConf->getTrabAvaliacaoPermitirRecurso()==1 or in_array($objEv->getCodEvento(), [80,115]))
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
            // if (($objEvConf->getTrabAvaliacaoPermitirRecurso() == 1 or $permitirRecurso==1) and ($objLAI->getClassificacaoStatus()=='I')) {
            if (($objEvConf->getTrabAvaliacaoPermitirRecurso() == 1 or $permitirRecurso==1)) {
                // echo __LINE__.'-';
                //if ($objInscrito->getTotalTrabalhos() < $FWS_Numero_Maximo_Trabalhos) {
                    If ($OP_PERMITIR_SOLICITAR_RECURSO == true) {
                        // echo "<h4>Obs:
                        // <br>Caso.</h4>";
                        if ($recurso_trabalho_solicitar == 1) {
                            echo "<span style='text-size:15px;color:green'>Pedido de Recurso já Registrado!</span><br>";
                        } else {
                            echo "
                            <br><br>
                            <FORM NAME='exit' METHOD='POST' ACTION='index.php?content=view/inscricao/inscricao_trabalhos_cadastro_dados_trabalho_recurso.php'>
                                <input type='hidden' name='cod_trabalho' value='{$cod_trabalho}'>
                                <button class=\"btn btn btn-primary \" type=\"submit\">
                                 " . $objEvConf->getTxtBtnSolicitarRecurso() . ".</button>
                            </FORM><hr style='border:1;width:100%'>
                            ";
                        }
                    }
                //}
            }
        }
 
        If ($OP_SUBMISSAO_TRABALHO) {
            if($objEvConf->getTrabSubmissãoDataDataFim()!=''){
                if($objEv->getCodEvento()!=37 and $objEv->getCodEvento()!=38){
                    echo "<br><font color='red'>Envio de ".strtolower($objEvConf->getDescTrabAnexoTipo())." se encerrará dia: " . formatDateView($objEvConf->getTrabSubmissãoDataDataFim()) . "</font>";;
                }
            }
            if (!$OP_SUBMISSAO_TRABALHO_ENCERRADA){
                if($objEv->getCodEvento()!=37 and $objEv->getCodEvento()!=38 and $objEv->getCodEvento()!=46){
                    if($objEvConf->getTrabPermitirBtnEditar() and $objInscrito->getStatusProcesso()=='INSCRITO'){
                        echo "<br><font color='red'>";
                        if($objEvConf->getTrabPermitirBtnEditar() and $objEvConf->getTrabPermitirBtnExcluir()){
                            echo "Edite ou exclua";
                        }else{
                            echo "Edite";
                        }
                        echo " seu ".strtolower($objEvConf->getDescTrabAnexoTipo())." enquanto o mesmo não estiver em processo de avaliação</font>";
                    }
                }    
            }

        }
        if ( $OP_SUBMISSAO_TRABALHO_AGUARDANDO ){
            echo "<br><font color='red'>";
            echo "<br>Liberação para envio de ".strtolower($objEvConf->getDescTrabAnexoTipo())." prevista para dia ";
            echo formatDateView($FWS_Data_Ini_Submissao_Trabalho) . "";
//            echo "e finalizar dia " . formatDateView($FWS_Data_Max_Submissao_Trabalho);
            echo "</font>";
        }
        if ($OP_SUBMISSAO_TRABALHO_ENCERRADA){
            if($objEv->getCodEvento()!=37 and $objEv->getCodEvento()!=38 and $objEv->getCodEvento()!=50 and $objEv->getCodEvento()!=44 and $objEv->getCodEvento()!=42){
                if(!$objEvConf->getMsgTrabalhoPadraoOcultar()==true){
                    echo "<br><font color='red'>";
                    echo "<br>Envio de ".strtolower($objEvConf->getDescTrabAnexoTipo())." encerrado dia: ";
                    echo formatDateView($FWS_Data_Max_Submissao_Trabalho) . "";
                    //echo "e finalizar dia " . formatDateView($FWS_Data_Max_Submissao_Trabalho);
                    echo "</font>";
                }
            }
            if($objEvConf->getApresentacaoAnexar()==true){
                if($objEvConf->getApresentacaoAnexarDataFim()!=''){
                    echo "<br><font color='red'>";
                    echo "<br>Envio de poster para apresentação até dia: ".formatDateView($objEvConf->getApresentacaoAnexarDataFim());
                    echo "</font>";
                }
            }
        }


        if($objInscrito->getTotalTrabalhos() < $FWS_Numero_Maximo_Trabalhos){
//            echo '->'.$OP_SUBMISSAO_TRABALHO.' and '.$OP_SUBMISSAO_TRABALHO_ENCERRADA.'<-';
            If ($OP_SUBMISSAO_TRABALHO and $OP_SUBMISSAO_TRABALHO_ENCERRADA!=true) {
                if($objEvConf->getTrabPermitirBtnEnviar()==true){
                    echo "
                    <br><br>
                    <FORM NAME='exit' METHOD='POST' ACTION='index.php?content=view/inscricao/inscricao_trabalhos_cadastro_dados_trabalho.php'>
                        <button class=\"btn btn btn-primary \" type=\"submit\">
                        ".$objEvConf->getTxtBtnEnviarTrabalho().".</button>
                    </FORM><hr style='border:1;width:100%'>
                    ";
                }
            }
        }
        
        #-----------------------------------------------------------------------------------------
        #Definindo comando SQL
        $var_sql = "
                SELECT t.cod_trabalho
               ,t.cod_area_tematica
               ,t.titulo_trabalho
               ,t.autor_apresentador_nome
               ,oat.titulo
               ,t.flg_enviado_avaliacao
               ,t.arquivo_upload
               ,t.arquivo_gerado
               ,t.nome_co_autor_1
               ,t.nome_co_autor_2
               ,t.nome_co_autor_3
               ,t.nome_co_autor_4
               ,t.nome_co_autor_5
               ,t.nome_co_autor_6
               ,t.resumo_trabalho
               ,t.flg_indicado_comissao
               ,es.*
               ,t.pontos_total
               ,t.recurso_trabalho_solicitar
               ,t.nome_co_autor_7
               ,t.nome_co_autor_8
               ,t.tipo_documento
               ,t.periodo_referencia
               ,t.flg_permitir_alterar
               ,t.flg_permitir_alterar_data_fim
               ,t.arquivo_identificado_upload
               ,t.arquivo_identificado_gerado               
             FROM tab_trabalhos as t
             left join tab_opcoes_areas_tematicas as oat on (t.cod_area_tematica = oat.cod_area_tematica)
             inner join tab_inscritos as ti on (t.cod_inscrito      = ti.cod_inscrito)
             left join tab_ensalamento_trabalho_sessao ets on ets.cod_trabalho=t.cod_trabalho
             left join vw_ensalamento_sessao es on es.cod_sessao=ets.cod_sessao
             WHERE
                 (t.cod_evento  = " . COD_EVENTO . ")
             and (t.status      = 'ATIVADA')
             and (ti.cod_inscrito = '" . $_SESSION['_cod_inscrito'] . "')
             and (ti.status     = 'ATIVADA')
             and (t.tipo_documento = 'RECURSO')
             ORDER BY cod_trabalho DESC
            ";

        #Executando a consulta no banco SQL
        $RS = $Conexao_db->Execute($var_sql);

        #Verificando se houve retorno de algum registro
        If (!$RS->EOF) {#Se nao ha trabahos submetidos
            #Definindo cabeçalho da tabela dos resultados da consula
            #Efetuando loop para o visualização dos registros retornados pela consulta SQL
            echo "<table border='0'>";
            $ii=0;
            $imprimirScript=1;
            While (!$RS->EOF) {
                $liberarBotaoEditar = false;
                #Adicionando nas variaveis, os valores dos campos retirados dos registros
                $cod_trabalho = $RS->fields[0];
                $cod_area_tematica = $RS->fields[1];
                $titulo_trabalho = $RS->fields[2];
                $autor_apresentador_nome = $RS->fields[3];
                $titulo_area_tematica = textoMaiusculoMinusculo($RS->fields[4]);
                $flg_enviado_avaliacao = $RS->fields[5];
                $arquivo_upload = $RS->fields[6];
                $arquivo_gerado = $RS->fields[7];

                $arquivo_identificado_upload = $RS->fields['arquivo_identificado_upload'];
                $arquivo_identificado_gerado = $RS->fields['arquivo_identificado_gerado'];
                
                $nome_co_autor_1  = $RS->fields[8];
                $nome_co_autor_2  = $RS->fields[9];
                $nome_co_autor_3  = $RS->fields[10];
                $nome_co_autor_4  = $RS->fields[11];
                $nome_co_autor_5  = $RS->fields[12];
                $nome_co_autor_6  = $RS->fields[13];
                $resumo_trabalho  = $RS->fields[14];
                $nome_co_autor_7  = $RS->fields['nome_co_autor_7'];
                $nome_co_autor_8  = $RS->fields['nome_co_autor_8'];
                $flg_indicado_comissao  = $RS->fields['flg_indicado_comissao'];
                
                $nome_bloco = $RS->fields['nome_bloco'];
                $nome_sala  = $RS->fields['nome_sala'];
                $data       = $RS->fields['data'];
                $hora_ini   = formatTimeView($RS->fields['hora_ini']);
                $turno  = $RS->fields['turno'];
                $pontos_total  = $RS->fields['pontos_total'];
                $recurso_trabalho_solicitar  = $RS->fields['recurso_trabalho_solicitar'];
                $tipo_documento  = $RS->fields['tipo_documento'];
                $periodo_referencia  = $RS->fields['periodo_referencia'];
                $flg_permitir_alterar  = $RS->fields['flg_permitir_alterar'];
                $flg_permitir_alterar_data_fim  = $RS->fields['flg_permitir_alterar_data_fim'];
                
                $coautores = "";
                $coautores .= $nome_co_autor_1? $nome_co_autor_1."<br>" : "";
                $coautores .= $nome_co_autor_2? $nome_co_autor_2."<br>" : "";
                $coautores .= $nome_co_autor_3? $nome_co_autor_3."<br>" : "";
                $coautores .= $nome_co_autor_4? $nome_co_autor_4."<br>" : "";
                $coautores .= $nome_co_autor_5? $nome_co_autor_5."<br>" : "";
                $coautores .= $nome_co_autor_6? $nome_co_autor_6."<br>" : "";
                $coautores .= $nome_co_autor_7? $nome_co_autor_7."<br>" : "";
                $coautores .= $nome_co_autor_8? $nome_co_autor_8."<br>" : "";

                echo ($ii>=0? "<tr valign='top'><td colspan='2'>&nbsp;</td></tr>" : "");
                $ii++;
                echo "<tr valign='top'><td colspan=2><hr style='border:1px solid #acacac !important;'></td></tr>";
                if($tipo_documento!=''){
                    if($tipo_documento=='RECURSO'){
                        echo "<tr valign='top'><td colspan='2'><b>Cód. Recurso.: <b>{$cod_trabalho }</b></td></tr>";
                    }else{
                        echo "<tr valign='top'><td width='130' colspan='2'>Cód. Documento: <b>{$cod_trabalho }</b></td></tr>";
                    }
                }else{
                    echo "<tr valign='top'><td width='130' colspan='2'>Cód. ".$objEvConf->getDescTrabAnexoTipo().": <b>{$cod_trabalho }</b></td></tr>";
                }
                
                //echo $titulo_area_tematica? "<tr valign='top'><td>Eixo Temático: </td><td> <b>{$titulo_area_tematica }</b></td></tr>" : "";
                if($objEvConf->getCodEvento()==46){
//                    echo "<tr valign='top'><td colspan='2'>Data Hora Consulta: <b>".."</b></td></tr>";
                    echo "<tr valign='top'><td colspan='2'>Soma de pontos informados por você durante seu cadastro: <b>{$pontos_total} pts</b></td></tr>";

                }else if($objEvConf->getCodEvento()==69){
//                    echo "<tr valign='top'><td colspan='2'>Data Hora Consulta: <b>".."</b></td></tr>";
                    echo "<tr valign='top'><td colspan='2'>Calcule sua pontuação com base na tabela de critérios de pontuação divulgada no Anexo II do Edital de Chamamento 001/2024</td></tr>";

                }else if($objEvConf->getCodEvento()==37 or $objEvConf->getCodEvento()==38){
                    echo "<tr valign='top'><td colspan='2'>Total de Pontos Informados: <b>{$pontos_total} pts</b></td></tr>";
                    /** @todo Avaliação de Inscrito */
                }else if(in_array($objEvConf->getCodEvento(),[80,88])){
                    if($tipo_documento=='RECURSO'){
                        echo "<tr valign='top'><td colspan='2'><b></b></td></tr>";
                    }else{
                        echo "<tr valign='top'><td colspan='2'>Total de pontos informados pelo candidato (sujeito à conferência e validação): <b>{$pontos_total} pts</b></td></tr>";
                    }

                }else if($objEvConf->getTrabAnexoTipo()=='Termo de Concessão de Bolsa'){
//                    echo "<tr valign='top'><td colspan='2'>Total de Pontos Informados: <b>{$pontos_total} pts</b></td></tr>";

                }else if($objEvConf->getTrabAnexoTipo()=='Documentos' or $objEvConf->getTrabAnexoTipo()=='Documento'){
//                    echo "<tr valign='top'><td colspan='2'>Total de Pontos Informados: <b>{$pontos_total} pts</b></td></tr>";

                }else if($objEvConf->getTrabAnexoTipo()=='Trabalho' 
                      or $objEvConf->getTrabAnexoTipo()=='Resumo'
                      or $objEvConf->getTrabAnexoTipo()=='Musica'){

                        echo $titulo_trabalho? "<tr valign='top'><td colspan='2'>Titulo: <b>{$titulo_trabalho }</b></td></tr>" : "";
                        if($objEvConf->getCodEvento()==70){
                            echo "<tr valign='top'><td >Apresentador: </td><td> <b>{$autor_apresentador_nome}</b></td></tr>";
                        }else{
                            echo "<tr valign='top'><td >Apresentador: </td><td> <b>{$objInscrito->getNomeInscrito() }</b></td></tr>";
                        }
                        echo $coautores? "<tr valign='top'><td>Co-autores: </td><td> {$coautores }</td></tr>" : "";
                        
                }        
                echo "<tr valign='top'><td colspan='2'>";
                if(TRABALHO_ANEXA_RESUMO){
                    if($arquivo_gerado){
                        // echo "Arquivo anexado: ";
                        //echo "<br>Arquivo anexado: ".$arquivo_upload;
        //                echo "<br>Arquivo Gravado: ".$arquivo_gerado;
                        //if($_SERVER['REMOTE_ADDR']=='189.90.70.56'){
                        $pathArquivoGerado = WB_PATH_LOG_TRABALHO.$arquivo_gerado;
                        $existArquivoGerado=0;
                        if (file_exists($pathArquivoGerado)) {
                            $existArquivoGerado=1;
                        }
                        if($objEvConf->getTrabPermitirBtnVisualizar()==1 and $existArquivoGerado==0){
                            echo "<span style='color:red;font-size:14pt;'>Arquivo ".strtolower($objEvConf->getDescTrabAnexoTipo())." não encontrado - tente enviar novamente.</span><br>";
                        }
                        if($objEvConf->getTrabPermitirBtnVisualizar()==1 and ($existArquivoGerado==1)){
                            echo "<a href=\"javascript: visualiza('{$arquivo_gerado}');\"  title='Visualizar ".$objEvConf->getDescTrabAnexoTipo()."' style='color:green;'>";
                            retornarIconeExtensao($arquivo_gerado);
                            if($tipo_documento!=''){
                                echo "Visualizar Arquivo ".$tipo_documento."</a>";
                            }else{
                                echo "Visualizar Arquivo ".$objEvConf->getDescTrabAnexoTipo()."</a>";
                            }
                            echo $periodo_referencia!=''? " - ref. ".$periodo_referencia : "";
                            echo "<br><br>";
                        }else{ 
                            // echo "Visualização bloqueada";
                        }
                        //}
                    }else{
                        if ($objEvConf->getTrabAvaliacaoPermitirRecurso()==1){
                            //echo "<br><font color=red>Arquivo do ".strtolower($objEvConf->getDescTrabAnexoTipo())." não anexado.</font>";
                        }else{
                            echo "<br><font color=red>Anexe o arquivo do ".strtolower($objEvConf->getDescTrabAnexoTipo()).".</font>";
                        }
                    } 
                    if($objEvConf->getCodEvento()==70){
                        if($arquivo_identificado_gerado){
                            // echo "arquivo_identificado anexado: ";
                            //echo "<br>arquivo_identificado anexado: ".$arquivo_identificado_upload;
            //                echo "<br>arquivo_identificado Gravado: ".$arquivo_identificado_gerado;
                            //if($_SERVER['REMOTE_ADDR']=='189.90.70.56'){
                            if($objEvConf->getTrabPermitirBtnVisualizar()==1){
                                echo "<a href=\"javascript: visualiza('{$arquivo_identificado_gerado}');\"  title='Visualizar ".$objEvConf->getDescTrabAnexoTipo()."' style='color:green;'>";
                                echo (substr($arquivo_identificado_gerado, -4)=='.pdf')? "<img src='resources/image/pdf.gif' border='0'>" : ""; 
                                echo (substr($arquivo_identificado_gerado, -4)=='.doc')? "<img src='resources/image/doc.gif' border='0'>" : ""; 
                                if($tipo_documento!=''){
                                    echo "Visualizar Arquivo Identificado ".$tipo_documento."</a>";
                                }else{
                                    echo "Visualizar Arquivo Identificado ".$objEvConf->getDescTrabAnexoTipo()."</a>";
                                }
                                echo $periodo_referencia!=''? " - ref. ".$periodo_referencia : "";
                                echo "<br><br>";
                            }else{ 
                                // echo "Visualização bloqueada";
                            }
                            //}
                        }else{
                            if ($objEvConf->getTrabAvaliacaoPermitirRecurso()==1){
                                //echo "<br><font color=red>arquivo_identificado do ".strtolower($objEvConf->getDescTrabAnexoTipo())." não anexado.</font>";
                            }else{
                                echo "<br><font color=red>Anexe o arquivo_identificado do ".strtolower($objEvConf->getDescTrabAnexoTipo()).".</font>";
                            }
                        } 
                    } 
                }else{
                    if($resumo_trabalho){
                        ?>
                        <a class="hidden" id="btnOcultarResumo-<?php echo $cod_trabalho ?>" title="Ocultar Resumo"
                           href='javascript:void(0);' onclick="javascript:ocultarResumo(<?php echo  $cod_trabalho ?>);">
                            <b>Ocultar Resumo </b><img src='<?php echo  WB_URL_IMG ?>/bull3_breeze.gif' alt='Ocultar Resumo' border='0' width="15px"></a>     
                        <a class="show" id="btnVisualizarResumo-<?php echo $cod_trabalho ?>" title="Visualizar Resumo"
                           href="javascript:void(0);" onclick="javascript:visualizarResumo(<?php echo  $cod_trabalho ?>);">
                           <b>Visualizar Resumo </b><img src='<?php echo  WB_URL_IMG ?>/abre.gif' alt='Visualizar Resumo' border='0' width="15px"></a>  
                        
                        <p align='justify' class='hidden' id='resumo-<?php echo $cod_trabalho;?>'><?php echo $resumo_trabalho;?></p>
                        <?php
                    }
                }
                echo "</td></tr>";

                //if($_SERVER['REMOTE_ADDR']=='189.90.70.56'){
                    if ($objEvConf->getTrabPermitirBtnEditar()and $objInscrito->getStatusProcesso()=='INSCRITO'){
                            $liberarBotaoEditar  = $objEvConf->getTrabPermitirBtnEditar();
                    }
                    if ($objEvConf->getTrabPermitirBtnExcluir()and $objInscrito->getStatusProcesso()=='INSCRITO'){
                        $liberarBotaoExcluir = $objEvConf->getTrabPermitirBtnExcluir();
                    }
                    if ($OP_SUBMISSAO_TRABALHO_ENCERRADA==true){
                        $liberarBotaoExcluir = 0;
                        $liberarBotaoEditar  = 0;
                    }
                    echo "<tr><td colspan=2>";
                    //echo "<b>Detalhes do ultimo parecer:</b><br>";
                    try {
                        $objHistoricoAvaliacao = new HistoricoAvaliacao();
                        $objHistoricoAvaliacao->setCodTrabalho($cod_trabalho);
                        $objHistoricoAvaliacao->setCodInscrito($cod_inscrito);
                        $objHistoricoAvaliacao->carregarUltimaAvaliacaoByIdTrabalho($debug);
                        if($objHistoricoAvaliacao->getAvaliacao()!=''){
//                            
                            ($objHistoricoAvaliacao->getAvaliacao()=='APROVADO')?  $colorMsg = "green" : $colorMsg = "red";
                            if($objHistoricoAvaliacao->getAvaliacao()=='APROVADO' AND $OP_DIVULGAR_TRABALHO_APROVADO){
                                
                                echo "<font color=".$colorMsg.">Parabéns, seu resumo foi <b>".$objHistoricoAvaliacao->getAvaliacao()."</b></font></font>";
                                if($flg_indicado_comissao==1){
                                    echo " <font color=".$colorMsg.">e <b>INDICADO PARA PREMIAÇÃO</b></font>";
                                }
                                echo "<br>";
                                ?>
                        
                                <?php if($OP_DIVULGAR_ENSALAMENTO){ ?>
                                    <font color=<?php echo $colorMsg?>><br>
                                        <span style="font-size: 20px;font-weight: bold;">
                                            Local de apresentação:
                                        </span><br>
                                        <?php echo $nome_bloco?> Sala: <?php echo $nome_sala?>
                                        <br>Data: <?php echo  formatDateView($data)?> - Horário: <?php echo $hora_ini?> - Turno: <?php echo $turno?>
                                    </font>
                                <?php }else{ ?>
                                    
<!--                                    <font color=<?php echo $colorMsg?>>
                                    <br>Em breve divulgaremos neste local, mais informações sobre apresentação.
                                    </font>-->
                                    
                                   
<!--                                    <font color=<?php echo $colorMsg?>>
                                    <br>Local de apresentação:
                                    <br>EXPOSIÇÃO EM PLATAFORMA DIGITAL
                                    <br>14 e 16 /07/2022
                                    <br>
                                    </font>-->
                                    

                                <?php } ?>
                                        
                                        
                                <?php 
//                                if($flg_indicado_comissao==1){
//                                   $OP_ANEXAR_APRESENTACAO=false;  
//                                   $OP_PERMITIR_ANEXAR_APRESENTACAO = 1;
//                                   $OP_PERMITIR_ANEXAR_VIDEO = 1;
//                                }
                                if($OP_ANEXAR_APRESENTACAO){ 
                                    include('inscricao_trabalhos_anexar_apresentacao.php');
                                }#$OP_ANEXAR_APRESENTACAO 
                                $apresentacaoAnexada = 1;
                                if($objEvConf->getAvulsoAnexar()){ 
                                    if($flg_indicado_comissao==1){
                                        include('inscricao_trabalhos_anexar_avulso.php');
                                    }
                                }#$OP_ANEXAR_AVULSO 
                                ?>
                                <?php 
                                if($OP_ANEXAR_VIDEO){ 
                                    if($flg_indicado_comissao==1){
                                        include('inscricao_trabalhos_anexar_video.php');
                                    }
                                }#$OP_ANEXAR_VIDEO 
                                ?>
                       
                                <?php
//                                echo "<font color=".$colorMsg."><br><a href='http://congressosaudepublica.org.br/suplemento_cientifico.pdf' target='_blank'>Clique aqui para localizar o horário de sua apresentação </a>.</b></font>";
                                
                            }else if ($objHistoricoAvaliacao->getAvaliacao()=='REPROVADO' AND $objEvConf->getTrabAvaliacaoDivulgar()){
                                echo "<font color=".$colorMsg.">"
                                        . "Agradecemos pela submissão do seu ".strtolower($objEvConf->getDescTrabAnexoTipo())."."
                                        . "<br>Sentimos muito em anunciar que esse ".strtolower($objEvConf->getDescTrabAnexoTipo())." foi considerado 'NÃO APROVADO' para o evento."
                                    . "</font>";
//                                echo "<font color=green><b> - AGUARDANDO REVISÃO.</b></font>";
                            }else{
                                echo "<font color=green><b> - AGUARDANDO AVALIAÇÃO.</b></font>";
                            }
                            
                            if($objHistoricoAvaliacao->getAvaliacao()=='DEVOLVIDO'){
                                $liberarBotaoEditar  = 1;
                                $liberarBotaoExcluir = 0;
                                //echo "<br><font color=".$colorMsg."><b>Ajuste seu resumo e o envie novamente para Avaliação.</b></font>";
                            }
                            if($objHistoricoAvaliacao->getAvaliacao()=='SUBMETIDO'){
                                $liberarBotaoEditar = 0;
                            }
                            if($objHistoricoAvaliacao->getAvaliacao()=='RE-SUBMETIDO'){
                                $liberarBotaoEditar = 0;
                            }
                            if($objHistoricoAvaliacao->getAvaliacao()=='REPROVADO'){
                                $liberarBotaoEditar = 0;
                            }
                            if($objHistoricoAvaliacao->getAvaliacao()=='APROVADO'){
                                $liberarBotaoEditar = 0;
                            }
                            if($objHistoricoAvaliacao->getAvaliacao()=='SELECIONADO'){
                                $liberarBotaoEditar = 0;
                                //echo "<font color=".$colorMsg."><b> - EM AVALIAÇÃO.</b></font>";
                            }
                            if($objHistoricoAvaliacao->getObservacao()!=''){
                                //echo "<br><b>PARECER DO AVALIADOR:</b><br>".$objHistoricoAvaliacao->getObservacao();
                            }
                        }else{
                            if($flg_enviado_avaliacao){
                                echo "<font color=".$colorMsg."><b> - AGUARDANDO AVALIAÇÃO.</b></font>";
                            }
                            /* Se não possui avaliação ativada, então 
                             * não é permitido editar ou excluir após submissão
                             */
//                            $liberarBotaoEditar = 1;
                        }
                        if($objEvConf->getTrabAvaliacaoMostrarHistorico()){
                            $objHistoricoAvaliacao->carregarListaAvaliacoesByIdTrabalho($debug);
                            foreach ($objHistoricoAvaliacao->getListaAvaliacoes() as $objLHA) {
                                if ($objLHA->getAvaliacao()){
                                    echo $objLHA->getAvaliacao()."<br>";
                                    echo $objLHA->getObservacao()."<br>";
                                }
                            }
                        }
                    } catch (Exception $exc) {
                        echo $exc->getMessage();
                    }
                    echo "</td></tr>";
                //}
                //if($flg_enviado_avaliacao){
    //            if(!$liberarBotaoEditar){
    //                if($cod_trabalho=='32'){
    //                    echo "<tr valign='top'><td colspan='2'><font color=green>Parabéns, seu resumo foi Aprovado</font></td></tr>";
    //                }else{
    //                    echo "<tr valign='top'><td colspan='2'>";
    //                    //echo "<font color=red>Enviado para Avaliação</font>";
    //                        $objAvaliacao = new TrabalhoAvaliacao();
    //                        $objAvaliacao->setCodInscrito($objInscrito->getCodInscrito());
    //                        $objAvaliacao->setCodTrabalho($cod_trabalho);
    //                        $objAvaliacao->getTrabalhoAvaliacaoById($debug);
    //                        if($cod_inscrito=='1023'){
    //                            echo "<font color=red>Enviado para Avaliação</font>";
    //                        }else{
    //                            if($objAvaliacao->getAvaliacao()=='APROVADO'){
    //                                echo "<font color=green>Resumo Aprovado</font>";
    //                            }else if($objAvaliacao->getAvaliacao()=='REPROVADO'){
    //                                echo "<font color=red>Resumo Reprovado</font>";
    //                            }else{
    //                                echo "<font color=red>Enviado para Avaliação</font>";
    //                            }
    //                        }
    //                    //}
    //                    echo "</td></tr>";
    //                }
    //            }else{
                if ($objEvConf->getTrabAvaliacaoPermitirRecurso()==1){
                    if($recurso_trabalho_solicitar==1){
                        if (!$mostrouMsgRecurso) {
                            echo "<br><span style='text-size:15px;color:green'>Pedido de Recurso já Registrado!</span><br>";
                            $mostrouMsgRecurso = true;
                        }
                    }
                    If ($OP_PERMITIR_SOLICITAR_RECURSO == true and $mostrouBtnRecurso == 1) {
                        $mostrouBtnRecurso = 1;
                        echo "<br><a href='index.php?content=view/inscricao/inscricao_trabalhos_cadastro_dados_trabalho.php&cod_trabalho=".$cod_trabalho."'
                        class='btn btn btn-primary' role='button'>".$objEvConf->getTxtBtnSolicitarRecurso()." </a>
                        &nbsp;&nbsp;&nbsp;"; 
                    }
                    
                }
                /** @todo inserir campo no formulário de edição de arquivo para permitir que um inscrito especifico possa fazer alteraçoes em seus arquivos sem que isso afete a regra geral aplicada a outros inscritos*/
                // if($cod_trabalho==6733 or $cod_trabalho==6734){
                //     $liberarBotaoEditar = 1;
                // }
                //$flg_permitir_alterar = 1;
                // $recurso_trabalho_solicitar=1;
                if($flg_permitir_alterar==1){
                    $liberarBotaoEditar = true;
                }
                if($liberarBotaoEditar){
                    echo "<tr valign='top'><td colspan='2'>";
//                  if ($objEvConf->getTrabAvaliacaoPermitirRecurso()==1){
                    if($recurso_trabalho_solicitar==1){
                        echo "<span style='text-size:15px;color:green'>Recurso já Solicitado!</span><br>";
                    }
//                  }
                    if($liberarBotaoExcluir == 1){
                        echo " 
                        <a href='index.php?content=view/inscricao/inscricao_trabalhos_excluir.php&acao=Excluir&cod_trabalho=".$cod_trabalho."'
                        class='btn btn-xs btn-primary' role='button'>Excluir</a>
                        &nbsp;&nbsp;";
                    }
//                    if($OP_TRAMITACAO_TRABALHO){
                        if(TRABALHO_ANEXA_RESUMO){
                            echo " 
                            <a href='index.php?content=view/inscricao/inscricao_trabalhos_cadastro_dados_trabalho.php&cod_trabalho=".$cod_trabalho."'
                            class='btn btn btn-primary' role='button'>".$objEvConf->getTxtBtnEditarTrabalho()." ...</a>
                            &nbsp;&nbsp;&nbsp;";
                        }else{
                            echo " 
                            <a href='index.php?content=view/inscricao/inscricao_trabalhos_cadastro_dados_trabalho.php&cod_trabalho=".$cod_trabalho."'
                            class='btn btn btn-primary' role='button'>Editar</a>
                            &nbsp;&nbsp;&nbsp;";
                        }
        //                if($arquivo_gerado){
                            if($objEvConf->getTrabAvaliacao()){
                                if(!$OP_AVALIACAO_ENVIAR_AO_SALVAR){ 
                                echo "
                                <a href='index.php?content=view/inscricao/inscricao_trabalhos_avaliacao_enviar.php&acao=Avaliar&cod_trabalho=".$cod_trabalho."'
                                    class='btn btn-xs btn-primary' role='button'>Enviar para Avaliação</a>
                                ";
                                }
                            }
        //                }
                        echo "</td></tr>";
//                    }     
                    if($OP_TRAMITACAO_TRABALHO){ 
                        if(!$OP_AVALIACAO_ENVIAR_AO_SALVAR){ 
        //                echo "<br><font color=red>Anexe o arquivo do trabalho, para liberar o botão de envio para avaliação.</font>";
                            echo "<tr valign='top'><td colspan='2'>
                            <br><font color=red><b>Atenção:</b></font>
                            <br>Não esqueça de enviar seu ".strtolower($objEvConf->getDescTrabAnexoTipo())." para avaliação.
                            </td></tr>";
                        }else{
                            
                        }
                    }
                }   

                if($OP_ANEXAR_CONTRATO){
                    echo "<tr><td colspan=2>";
                    include('inscricao_trabalhos_anexar_contrato.php');
                    echo "</td></tr>"; 
                }
                // if($OP_ANEXAR_RPA){
                //     echo "<tr><td colspan=2>";
                //     include('inscricao_trabalhos_anexar_rpa.php');
                //     echo "</td></tr>"; 
                // }
                // if($OP_ANEXAR_BOLETO){
                //     echo "<tr><td colspan=2>";
                //     include('inscricao_trabalhos_anexar_boleto.php');
                //     echo "</td></tr>"; 
                // }    

                #Movendo para o proximo registro
                $RS->MoveNext();
            }
            echo "<tr valign='top'><td colspan='2'>&nbsp;</td></tr></table>";

        }
    }
    ?>
    <form name="getdocumento" method="post" action="<?php echo WB_URL_COMMAND?>" target="_blank">
        <input type="hidden" name="c" value="GerenciarUpload">
        <input type="hidden" name="m" value="abrirDocumento">
        <input type="hidden" value="" name="cod">
    </form>
    <form name="getavulso" method="post" action="<?php echo WB_URL_COMMAND?>" target="_blank">
        <input type="hidden" name="c" value="GerenciarUpload">
        <input type="hidden" name="m" value="abrirAvulso">
        <input type="hidden" value="" name="cod">
    </form>
    <form name="getcontrato" method="post" action="<?php echo WB_URL_COMMAND?>" target="_blank">
        <input type="hidden" name="c" value="GerenciarUpload">
        <input type="hidden" name="m" value="abrirContrato">
        <input type="hidden" value="" name="cod">
    </form>
    <form name="getvideo" method="post" action="<?php echo WB_URL_COMMAND?>" target="_blank">
        <input type="hidden" name="c" value="GerenciarUpload">
        <input type="hidden" name="m" value="abrirVideo">
        <input type="hidden" value="" name="cod">
    </form>
    <form name="getdoc" method="post" action="<?php echo WB_URL_COMMAND?>" target="_blank">
        <input type="hidden" name="c" value="GerenciarUpload">
        <input type="hidden" name="m" value="abrirWord">
        <input type="hidden" value="" name="cod">
    </form>
    <SCRIPT LANGUAGE="JavaScript1.2" src="resources/js/avaliacao-trabalhos.js"></SCRIPT>
    <script language="JavaScript" type="text/javascript">
        function visualiza(cod){
            document.getdoc.cod.value=cod;
            document.getdoc.submit();
        }
        function visualizarDocumento(cod){
            document.getdocumento.cod.value=cod;
            document.getdocumento.submit();
        }
        function visualizarAvulso(cod){
            document.getavulso.cod.value=cod;
            document.getavulso.submit();
        }
        function visualizarContrato(cod){
            document.getcontrato.cod.value=cod;
            document.getcontrato.submit();
        }
        function visualizarVideo(cod){
            document.getvideo.cod.value=cod;
            document.getvideo.submit();
        }
        function ocultarResumo(cod_resumo){
            loading('#resumo-'+cod_resumo,'hidden')
            loading('#btnVisualizarResumo-'+cod_resumo,'show')
            loading('#btnOcultarResumo-'+cod_resumo,'hidden')
        }
        function visualizarResumo(cod_resumo){
            loading('#resumo-'+cod_resumo,'show')
            loading('#btnVisualizarResumo-'+cod_resumo,'hidden')
            loading('#btnOcultarResumo-'+cod_resumo,'show')
        }
        function criticarAnexoApresentacao(cod_trabalho){
            if($('#arquivo-'+cod_trabalho).val()==''){
                alert('É necessário anexar arquivo da apresentação.');
                return false;
            }
        }
        function criticarAnexoVideo(cod_trabalho){
            if($('#arquivo-video-'+cod_trabalho).val()==''){
                alert('É necessário anexar arquivo de video.');
                return false;
            }
        }
    </script>
<?php }?>
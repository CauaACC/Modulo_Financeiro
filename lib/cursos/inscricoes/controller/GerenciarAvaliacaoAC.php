<?php

class GerenciarAvaliacaoAC {

    function __construct() {
        
    }
    public function adminIndicarAvaliadorOld($fws_, $debug = false) {
        FWA_exigirPermissao([enumSistema::SIS_GERENCIAR_ADM_GERAL, enumSistema::SIS_GERENCIAR_AVALIACAO_TRABALHOS, enumSistema::SIS_AVALIACAO_TRABALHO]);
        try {
            $objTrabalhoHistorico = new HistoricoAvaliacao();
            $objTrabalhoHistorico->setCodTrabalho($fws_['codTrabalho']);
            $objTrabalhoHistorico->setCodInscrito($fws_['codInscrito']);
            $objTrabalhoHistorico->setCodAvaliador($fws_['codAvaliador']);
            $objTrabalhoHistorico->getHistoricoAvaliacaoByIdAvaliador($debug);            
            $objTrabalhoHistorico->ativarAvaliacaoAvaliador($debug);   
        } catch (OKException $exc) {
            $ok = 1;
            echo '{
                "message":"' . $exc->getMessage() . '",
                "statusMessage":"' . $ok . '",
                "avaliacao":"'.$objTrabalhoHistorico->getAvaliacao().'",
                "idInclusao":"'.$objTrabalhoHistorico->getIdInclusao().'",
                "dataInclusao":"'.formatDateView($objTrabalhoHistorico->getDataInclusao()).'"
                }';
            exit();
        }catch (Exception $exc) {
            $ok = 0;
            echo '{
            "message":"' . $exc->getMessage() . '",
            "statusMessage":"' . $ok . '"
            }';
            exit();
        } 
    }
    public function adminIndicarAvaliador($fws_, $debug = false) {
        FWA_exigirPermissao([enumSistema::SIS_GERENCIAR_ADM_GERAL, enumSistema::SIS_GERENCIAR_AVALIACAO_TRABALHOS, enumSistema::SIS_AVALIACAO_TRABALHO]);
        try {
            try {
                $objTrabalho = new Trabalho();
                $objTrabalho->setCodInscrito($fws_['codInscrito']);
                $objTrabalho->setCodTrabalho($fws_['codTrabalho']);
                $objTrabalho->getTrabalhoById($debug);
                if ($objTrabalho->getStatus()!='ATIVADA') {
                    throw new Exception("Não foi possível carregar os dados do trabalho");
                }
//                if (($objTrabalho->getArquivoGerado() == '') and (TRABALHO_ANEXA_RESUMO)) {
//                    throw new Exception("É necessário anexar o arquivo do resumo antes de envia-lo para avaliação.");
//                }
                $objTrabalho->setFlgEnviadoAvaliacao('1');
                $objTrabalho->salvarAvaliacao($debug);
                
            } catch (OKException $exc) {      
//                try{
//                    /*Gravando tramitação*/
//                    $objTrabalhoHistorico = new HistoricoAvaliacao();
//                    $objTrabalhoHistorico->setCodTrabalho($obj->getCodTrabalho());
//                    $objTrabalhoHistorico->setCodInscrito($obj->getCodInscrito());
//                    $objTrabalhoHistorico->carregarUltimaAvaliacaoByIdTrabalho($debug);
//                    $objTrabalhoHistorico->setObservacao('');
//                    $objTrabalhoHistorico->setAvaliacao('SUBMETIDO');
//                    if ($objTrabalhoHistorico->getCodAvaliacao() != '') {
//                        $objTrabalhoHistorico->setAvaliacao('RE-SUBMETIDO');
//                    }
//                    $objTrabalhoHistorico->getCodAvaliacao('');
//                    $objTrabalhoHistorico->salvar();
//                }catch (OKException $exc) {
                    /*Gravando avaliador*/
//                    try {
                        $objTrabalhoHistorico = new HistoricoAvaliacao();
                        $objTrabalhoHistorico->setCodAreaTematica($objTrabalho->getCodAreaTematica());
                        $objTrabalhoHistorico->setCodTrabalho($objTrabalho->getCodTrabalho());
                        $objTrabalhoHistorico->setCodInscrito($objTrabalho->getCodInscrito());
                        $objTrabalhoHistorico->setCodAvaliador($fws_['codAvaliador']);
                        $objTrabalhoHistorico->getHistoricoAvaliacaoByIdAvaliador();
                        if($objTrabalhoHistorico->getAvaliacao()==''){
                            $objTrabalhoHistorico->setAvaliacao('SELECIONADO');
                            $objTrabalhoHistorico->salvar();
                        }else{
                            throw new Exception("Trabalho já esta em avaliação.");
                        }
//                    }
//                }
            }
        } catch (OKException $exc) {;
            $ok = 1;
            $objTrabalhoHistorico->carregarObjAvaliador();
            $objAvaliador = $objTrabalhoHistorico->getObjAvaliador();
            $objTrabalho->carregarObjInscrito();
            $objInscrito = $objTrabalho->getObjInscrito();
            
            if(GerenciarEmailAvaliacaoAC::comunicadoIndicacaoAvaliador($objTrabalho,$objTrabalhoHistorico,$objInscrito,$objAvaliador,'BOOLEAN',false)){
                $statusEnvioEmailAvaliador = "Comunicado Enviado";
            }else{
                $statusEnvioEmailAvaliador = "Comunicado NÃO Enviado";
            }
            
            if ($objTrabalhoHistorico->getAvaliacao() == 'SELECIONADO'){
                $message = "Em Avaliação<br>Tramitação:SELECIONADO<br>"
                        . "<br>E-mail avaliador: ".$objAvaliador->getEMail().""
                        . "<br>".$statusEnvioEmailAvaliador.""
                        . "<br>".formatDateView($objTrabalhoHistorico->getDataInclusao());
            }else{
                $message = $exc->getMessage();
            }
            echo '{
                "message":"' . tratarJson($message) . '",
                "statusMessage":"' . $ok . '",
                "avaliacao":"'.$objTrabalhoHistorico->getAvaliacao().'",
                "idInclusao":"'.$objTrabalhoHistorico->getIdInclusao().'",
                "dataInclusao":"'.formatDateView($objTrabalhoHistorico->getDataInclusao()).'"
                }';
            
            
            exit();
        }catch (Exception $exc) {
            $ok = 0;
            echo '{
            "message":"' . $exc->getMessage() . '",
            "statusMessage":"' . $ok . '"
            }';
            exit();
        } 
    }
    public function inscritoEnviarAvaliacao($fws_, $debug = false) {
        FWA_exigirInscrito();
        try {
            $fws_['cod_trabalho'] = $fws_['cod_trabalho'] == '' ? $_GET['cod_trabalho'] : $fws_['cod_trabalho'];

            $obj = new Trabalho();
            $obj->setCodInscrito($_SESSION['_cod_inscrito']);
            $obj->setCodTrabalho($fws_['cod_trabalho']);
            $obj->getTrabalhoById($debug);
            if (($obj->getArquivoGerado() == '') and (TRABALHO_ANEXA_RESUMO)) {
                throw new Exception("É necessário anexar o arquivo do resumo antes de envia-lo para avaliação.");
            }
            try {
                $objTrabalhoHistorico = new HistoricoAvaliacao();
                $objTrabalhoHistorico->setCodTrabalho($obj->getCodTrabalho());
                $objTrabalhoHistorico->setCodInscrito($obj->getCodInscrito());
                $objTrabalhoHistorico->setCodAreaTematica($obj->getCodAreaTematica());
                $objTrabalhoHistorico->carregarUltimaAvaliacaoByIdTrabalho($debug);
                $objTrabalhoHistorico->setAvaliacao('SUBMETIDO');
                if ($objTrabalhoHistorico->getCodAvaliacao() != '') {
                    $objTrabalhoHistorico->setAvaliacao('RE-SUBMETIDO');
                }
                $objTrabalhoHistorico->getCodAvaliacao('');
                $objTrabalhoHistorico->salvar();
            } catch (OKException $exc) {
                /*@todo enviar email para avaliador confirmando o retorno*/
            }

            $obj->setFlgEnviadoAvaliacao('1');
            $obj->enviarAvaliacao($debug);
        } catch (OKException $exc) {
            $msg = $exc->getMessage();
            $_SESSION['MSG']=$msg;
            $url = WB_URL_VIEW . "?content=view/inscricao/inscricao_congresso_ok.php";
        } catch (Exception $exc) {
            $msg = $exc->getMessage();
            $_SESSION['MSG']=$msg;
            $url = WB_URL_VIEW . "?content=view/inscricao/inscricao_congresso_ok.php"; 
        }
        echo "<br><a href='{$url}'>Redirecionando... {$msg}</a><br>";
        if (!$debug) {
            echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $url . "\">";
        }
    }
    public function adminEnviarParaAvaliacao($fws_, $debug = false) {
        FWA_exigirPermissao([enumSistema::SIS_GERENCIAR_ADM_GERAL, enumSistema::SIS_GERENCIAR_AVALIACAO_TRABALHOS, enumSistema::SIS_AVALIACAO_TRABALHO]);
        try {
            try {
                $obj = new Trabalho();
                $obj->setCodInscrito($fws_['cod_inscrito']);
                $obj->setCodTrabalho($fws_['cod_trabalho']);
                $obj->getTrabalhoById($debug);
                if ($obj->getStatus()!='ATIVADA') {
                    throw new Exception("Não foi possível carregar os dados do trabalho");
                }
                if (($obj->getArquivoGerado() == '') and (TRABALHO_ANEXA_RESUMO)) {
                    throw new Exception("É necessário anexar o arquivo do resumo antes de envia-lo para avaliação.");
                }
                $obj->setFlgEnviadoAvaliacao('1');
                $obj->salvarAvaliacao($debug);
                
            } catch (OKException $exc) {
                $objTrabalhoHistorico = new HistoricoAvaliacao();
                $objTrabalhoHistorico->setCodTrabalho($obj->getCodTrabalho());
                $objTrabalhoHistorico->setCodInscrito($obj->getCodInscrito());
                $objTrabalhoHistorico->carregarUltimaAvaliacaoByIdTrabalho($debug);
                $objTrabalhoHistorico->setObservacao('');
                $objTrabalhoHistorico->setAvaliacao('SUBMETIDO');
                if ($objTrabalhoHistorico->getCodAvaliacao() != '') {
                    $objTrabalhoHistorico->setAvaliacao('RE-SUBMETIDO');
                }
                $objTrabalhoHistorico->getCodAvaliacao('');
                $objTrabalhoHistorico->salvar();
            }
        } catch (OKException $exc) {
            $ok = 1;
            echo '{
                "message":"' . $exc->getMessage() . '",
                "statusMessage":"' . $ok . '",
                "avaliacao":"'.$objTrabalhoHistorico->getAvaliacao().'",
                "idInclusao":"'.$objTrabalhoHistorico->getIdInclusao().'",
                "dataInclusao":"'.formatDateView($objTrabalhoHistorico->getDataInclusao()).'"
                }';
            exit();
        }catch (Exception $exc) {
            $ok = 0;
            echo '{
            "message":"' . $exc->getMessage() . '",
            "statusMessage":"' . $ok . '"
            }';
            exit();
        } 
        
    }

    public function adminRetornarParaInscrito($fws_, $debug = false) {
        FWA_exigirPermissao([enumSistema::SIS_GERENCIAR_ADM_GERAL, enumSistema::SIS_GERENCIAR_AVALIACAO_TRABALHOS, enumSistema::SIS_AVALIACAO_TRABALHO]);
        try {
            try{
                $obj = new Trabalho();
                $obj->setCodInscrito($fws_['cod_inscrito']);
                $obj->setCodTrabalho($fws_['cod_trabalho']);
                $obj->getTrabalhoById();
                if ($obj->getStatus()!='ATIVADA') {
                    throw new Exception("Não foi possível carregar os dados do trabalho");
                }
                $obj->setFlgEnviadoAvaliacao('0');
                $obj->salvarAvaliacao($debug);
            } catch (OKException $exc) {
                $objTrabalhoHistorico = new HistoricoAvaliacao();
                $objTrabalhoHistorico->setCodTrabalho($obj->getCodTrabalho());
                $objTrabalhoHistorico->setCodInscrito($obj->getCodInscrito());
                $objTrabalhoHistorico->carregarUltimaAvaliacaoByIdTrabalho($debug);
                
                if($objTrabalhoHistorico->getCodAvaliacao()=='' 
                or $objTrabalhoHistorico->getAvaliacao()=='DEVOLVIDO' 
                or $objTrabalhoHistorico->getAvaliacao()=='Não submetido'){
                    $objTrabalhoHistorico->setAvaliacao('Não submetido');
                    throw new Exception('Trabalho nao foi enviado para avaliação.');
                }
                
                $objTrabalhoHistorico->setObservacao('');
                $objTrabalhoHistorico->setAvaliacao('DEVOLVIDO');
                $objTrabalhoHistorico->setCodAvaliacao('');
                $objTrabalhoHistorico->salvar();
            }
        } catch (OKException $exc) {
            $ok = 1;
            echo '{
                "message":"' . $exc->getMessage() . '",
                "statusMessage":"' . $ok . '",
                "avaliacao":"'.$objTrabalhoHistorico->getAvaliacao().'",
                "idInclusao":"'.$objTrabalhoHistorico->getIdInclusao().'",
                "dataInclusao":"'.formatDateView($objTrabalhoHistorico->getDataInclusao()).'"
                }';
            exit();
        }catch (Exception $exc) {
            $ok = 0;
            echo '{
            "message":"' . $exc->getMessage() . '",
            "statusMessage":"' . $ok . '"
            }';
            exit();
        } 
    }

    public function adminLiberarDeAvaliador($fws_, $debug = false) {
        FWA_exigirPermissao([enumSistema::SIS_GERENCIAR_ADM_GERAL, enumSistema::SIS_GERENCIAR_AVALIACAO_TRABALHOS, enumSistema::SIS_AVALIACAO_TRABALHO]);
            try{
                $objTrabalho = new Trabalho();
                $objTrabalho->setCodInscrito($fws_['cod_inscrito']);
                $objTrabalho->setCodTrabalho($fws_['cod_trabalho']);
                $objTrabalho->getTrabalhoById();
                if ($objTrabalho->getStatus()!='ATIVADA') {
                    throw new Exception("Não foi possível carregar os dados do trabalho");
                }
                $objTrabalho->setFlgEnviadoAvaliacao('NULL');
                $objTrabalho->salvarAvaliacao($debug);
            } catch (OKException $exc) {
                try {
                    $objTrabalhoAvaliacao = new TrabalhoAvaliacao();
                    $objTrabalhoAvaliacao->setCodTrabalho($objTrabalho->getCodTrabalho());
                    $objTrabalhoAvaliacao->setCodInscrito($objTrabalho->getCodInscrito());
                    $objTrabalhoAvaliacao->setCodAvaliador($objTrabalho->getCodAvaliador());
                    $objTrabalhoAvaliacao->setCodAreaTematica($objTrabalho->getCodAreaTematica());
                    $objTrabalhoAvaliacao->getTrabalhoAvaliacaoById($debug);
                    $objTrabalhoAvaliacao->setStatus('DESATIVADA');
                    if(is_null($objTrabalhoAvaliacao->getAvaliacao())or $objTrabalhoAvaliacao->getAvaliacao()==""){
                        $objTrabalhoAvaliacao->setAvaliacao('Cancelada');
                    }
                    $objTrabalhoAvaliacao->salvarAvaliacao();
                } catch (OKException $exc) {
                    try {
                        $objTrabalhoHistorico = new HistoricoAvaliacao();
                        $objTrabalhoHistorico->setCodTrabalho($fws_['cod_trabalho']);
                        $objTrabalhoHistorico->setCodInscrito($fws_['cod_inscrito']);
                        $objTrabalhoHistorico->setCodAvaliador($fws_['cod_avaliador']);
                        $objTrabalhoHistorico->getHistoricoAvaliacaoByIdAvaliador($debug);
                        $objTrabalhoHistorico->desativarAvaliacoesAvaliador($debug);            
                    } catch (OKException $exc) {
                        /*Enviando aviso de troca*/
                        $objTrabalho->carregarObjInscrito();
                        $objInscrito = $objTrabalho->getObjInscrito();
                        $objTrabalhoHistorico->carregarObjAvaliador();
                        $objAvaliador = $objTrabalhoHistorico->getObjAvaliador();
                        if(GerenciarEmailAvaliacaoAC::comunicadoTrocaAvaliador($objTrabalho,$objTrabalhoHistorico,$objInscrito,$objAvaliador,'BOOLEAN',false)){
                            $statusEnvioEmailAvaliador = "Comunicado Enviado";
                        }else{
                            $statusEnvioEmailAvaliador = "Comunicado NÃO Enviado";
                        }
                        $ok = 1;
                        echo '{
                            "message":"' . $exc->getMessage() . '",
                            "statusMessage":"' . $ok . '",
                            "avaliacao":"Não submetido",
                            "idInclusao":"",
                            "dataInclusao":"'.formatDateView($objTrabalhoHistorico->getDataInclusao()).'"
                            }';
                        exit();
                    }
                }
            }catch (Exception $exc) {
                $ok = 0;
                echo '{
                "message":"' . $exc->getMessage() . '",
                "statusMessage":"' . $ok . '"
                }';
                exit();
            }
    }    
    public function cobrarAvaliador($fws_, $debug = false) {
        FWA_exigirPermissao([enumSistema::SIS_GERENCIAR_ADM_GERAL, enumSistema::SIS_GERENCIAR_AVALIACAO_TRABALHOS, enumSistema::SIS_AVALIACAO_TRABALHO]);
        try {
            $objTrabalho = new Trabalho();
            $objTrabalho->setCodInscrito($fws_['codInscrito']);
            $objTrabalho->setCodTrabalho($fws_['codTrabalho']);
            $objTrabalho->getTrabalhoById($debug);
            if ($objTrabalho->getStatus()!='ATIVADA') {
                throw new Exception("Não foi possível carregar os dados do trabalho");
            }
            $objTrabalho->carregarObjInscrito();
            $objInscrito = $objTrabalho->getObjInscrito();
            
            $objTrabalhoHistorico = new HistoricoAvaliacao();
            $objTrabalhoHistorico->setCodTrabalho($fws_['codTrabalho']);
            $objTrabalhoHistorico->setCodInscrito($fws_['codInscrito']);
            $objTrabalhoHistorico->setCodAvaliador($fws_['codAvaliador']);
            $objTrabalhoHistorico->getHistoricoAvaliacaoByIdAvaliador($debug);
            $objTrabalhoHistorico->carregarObjAvaliador();
            $objAvaliador = $objTrabalhoHistorico->getObjAvaliador();
            
            if(GerenciarEmailAvaliacaoAC::cobrarAvaliador($objTrabalho,$objTrabalhoHistorico,$objInscrito,$objAvaliador,'BOOLEAN',false)){
                $statusEnvioEmailAvaliador = "<font color=green>Comunicado Re-Enviado</font>";
            }else{
                $statusEnvioEmailAvaliador = "<font color=red>Comunicado NÃO Re-Enviado</font>";
            }
            $ok = 1;
            echo '{
                "message":"' . $statusEnvioEmailAvaliador . '",
                "statusMessage":"' . $ok . '"
                }';
            exit();
        }catch (Exception $exc) {
            $ok = 0;
            echo '{
            "message":"' . $exc->getMessage() . '",
            "statusMessage":"' . $ok . '"
            }';
            exit();
        }
    }    
    public function adminReprovarDireto($fws_, $debug = false) {
        FWA_exigirPermissao([enumSistema::SIS_GERENCIAR_ADM_GERAL, enumSistema::SIS_GERENCIAR_AVALIACAO_TRABALHOS, enumSistema::SIS_AVALIACAO_TRABALHO]);
        try {
            try{
                $obj = new Trabalho();
                $obj->setCodInscrito($fws_['cod_inscrito']);
                $obj->setCodTrabalho($fws_['cod_trabalho']);
                $obj->getTrabalhoById();
                if ($obj->getStatus()!='ATIVADA') {
                    throw new Exception("Não foi possível carregar os dados do trabalho");
                }
                $obj->setFlgEnviadoAvaliacao('1');
                $obj->salvarAvaliacao($debug);
            } catch (OKException $exc) {
                try{
                    $objTrabalhoHistorico = new HistoricoAvaliacao();
                    $objTrabalhoHistorico->setCodTrabalho($obj->getCodTrabalho());
                    $objTrabalhoHistorico->setCodInscrito($obj->getCodInscrito());
                    $objTrabalhoHistorico->carregarUltimaAvaliacaoByIdTrabalho($debug);
                    $objTrabalhoHistorico->setObservacao('');
                    $objTrabalhoHistorico->setAvaliacao('REPROVADO');
                    $objTrabalhoHistorico->setCodAvaliador($_SESSION['_login']);
                    $objTrabalhoHistorico->setCodAvaliacao('');
                    $objTrabalhoHistorico->salvar();
                } catch (OKException $exc) {
                    $objTrabalhoAvaliacao = new TrabalhoAvaliacao();
                    $objTrabalhoAvaliacao->setCodTrabalho($obj->getCodTrabalho());
                    $objTrabalhoAvaliacao->setCodInscrito($obj->getCodInscrito());
                    $objTrabalhoAvaliacao->setCodAreaTematica($obj->getCodAreaTematica());
                    $objTrabalhoAvaliacao->getTrabalhoAvaliacaoById($debug);
                    $objTrabalhoAvaliacao->setObservacao('');
                    $objTrabalhoAvaliacao->setAvaliacao('REPROVADO');
                    $objTrabalhoAvaliacao->setCodAvaliador($_SESSION['_login']);
                    $objTrabalhoAvaliacao->salvarAvaliacao();
                }
            }
        } catch (OKException $exc) {
            $ok = 1;
            echo '{
                "message":"' . $exc->getMessage() . '",
                "statusMessage":"' . $ok . '",
                "avaliacao":"'.$objTrabalhoHistorico->getAvaliacao().'",
                "idInclusao":"'.$objTrabalhoHistorico->getIdInclusao().'",
                "dataInclusao":"'.formatDateView($objTrabalhoHistorico->getDataInclusao()).'"
                }';
            exit();
        }catch (Exception $exc) {
            $ok = 0;
            echo '{
            "message":"' . $exc->getMessage() . '",
            "statusMessage":"' . $ok . '"
            }';
            exit();
        } 
    }

    public function adminAprovarDireto($fws_, $debug = false) {
        FWA_exigirPermissao([enumSistema::SIS_GERENCIAR_ADM_GERAL, enumSistema::SIS_GERENCIAR_AVALIACAO_TRABALHOS, enumSistema::SIS_AVALIACAO_TRABALHO]);
        try {
            try{
                $obj = new Trabalho();
                $obj->setCodInscrito($fws_['cod_inscrito']);
                $obj->setCodTrabalho($fws_['cod_trabalho']);
                $obj->getTrabalhoById();
                if ($obj->getStatus()!='ATIVADA') {
                    throw new Exception("Não foi possível carregar os dados do trabalho");
                }
                $obj->setFlgEnviadoAvaliacao('1');
                $obj->salvarAvaliacao($debug);
            } catch (OKException $exc) {
                try {
                    $objTrabalhoHistorico = new HistoricoAvaliacao();
                    $objTrabalhoHistorico->setCodTrabalho($obj->getCodTrabalho());
                    $objTrabalhoHistorico->setCodInscrito($obj->getCodInscrito());
                    $objTrabalhoHistorico->carregarUltimaAvaliacaoByIdTrabalho($debug);
                    $objTrabalhoHistorico->setObservacao('');
                    $objTrabalhoHistorico->setAvaliacao('APROVADO');
                    $objTrabalhoHistorico->setCodAvaliador($_SESSION['_login']);
                    $objTrabalhoHistorico->setCodAvaliacao('');
                    $objTrabalhoHistorico->salvar();
                } catch (OKException $exc) {
                    $objTrabalhoAvaliacao = new TrabalhoAvaliacao();
                    $objTrabalhoAvaliacao->setCodTrabalho($obj->getCodTrabalho());
                    $objTrabalhoAvaliacao->setCodInscrito($obj->getCodInscrito());
                    $objTrabalhoAvaliacao->setCodAreaTematica($obj->getCodAreaTematica());
                    $objTrabalhoAvaliacao->getTrabalhoAvaliacaoById($debug);
                    $objTrabalhoAvaliacao->setObservacao('');
                    $objTrabalhoAvaliacao->setAvaliacao('APROVADO');
                    $objTrabalhoAvaliacao->setCodAvaliador($_SESSION['_login']);
                    $objTrabalhoAvaliacao->salvarAvaliacao();
                }
            }
        } catch (OKException $exc) {
            $ok = 1;
            echo '{
                "message":"' . $exc->getMessage() . '",
                "statusMessage":"' . $ok . '",
                "avaliacao":"'.$objTrabalhoHistorico->getAvaliacao().'",
                "idInclusao":"'.$objTrabalhoHistorico->getIdInclusao().'",
                "dataInclusao":"'.formatDateView($objTrabalhoHistorico->getDataInclusao()).'"
                }';
            exit();
        }catch (Exception $exc) {
            $ok = 0;
            echo '{
            "message":"' . $exc->getMessage() . '",
            "statusMessage":"' . $ok . '"
            }';
            exit();
        } 
    }
    public function alterarIndicacaoComisssao($fws_, $debug = false) {
        FWA_exigirPermissao([enumSistema::SIS_GERENCIAR_ADM_GERAL, enumSistema::SIS_GERENCIAR_AVALIACAO_TRABALHOS, enumSistema::SIS_AVALIACAO_TRABALHO]);
        try {
            $obj = new Trabalho();
            $obj->setCodInscrito($fws_['cod_inscrito']);
            $obj->setCodTrabalho($fws_['cod_trabalho']);
            $obj->getTrabalhoById();
            if ($obj->getStatus()!='ATIVADA') {
                throw new Exception("Não foi possível carregar os dados do trabalho");
            }

            $objTrabalhoAvaliacao = new TrabalhoAvaliacao();
            $objTrabalhoAvaliacao->setCodTrabalho($obj->getCodTrabalho());
            $objTrabalhoAvaliacao->setCodInscrito($obj->getCodInscrito());
            $objTrabalhoAvaliacao->setCodAreaTematica($obj->getCodAreaTematica());
            $objTrabalhoAvaliacao->getTrabalhoAvaliacaoById($debug);
            if($objTrabalhoAvaliacao->getIndicadoComissao()==1){
                $objTrabalhoAvaliacao->setIndicadoComissao('');
            }else{
                $objTrabalhoAvaliacao->setIndicadoComissao('1');
            }  
            $objTrabalhoAvaliacao->salvarAvaliacao($debug);

        } catch (OKException $exc) {
            $ok = 1;
            $txtIndicacaoPremiacao = $objTrabalhoHistorico->getIndicadoComissao()==1? 'Registrada' : 'Removida';
            echo '{
                "message":"Indicação pela Comissão foi '.$txtIndicacaoPremiacao.' com sucesso.",
                "statusMessage":"' . $ok . '",
                "indicadoComissao":"'.$objTrabalhoHistorico->getIndicadoComissao().'",
                "idInclusao":"'.$objTrabalhoHistorico->getIdInclusao().'",
                "dataInclusao":"'.formatDateView($objTrabalhoHistorico->getDataInclusao()).'"
                }';
            exit();
        }catch (Exception $exc) {
            $ok = 0;
            echo '{
            "message":"' . $exc->getMessage() . '",
            "statusMessage":"' . $ok . '"
            }';
            exit();
        } 
    }
    
    public function definirPremiacao($fws_, $debug = false) {
        FWA_exigirPermissao([enumSistema::SIS_GERENCIAR_ADM_GERAL, enumSistema::SIS_GERENCIAR_AVALIACAO_TRABALHOS, enumSistema::SIS_AVALIACAO_TRABALHO]);
        try {
            $obj = new Trabalho();
            $obj->setCodInscrito($fws_['cod_inscrito']);
            $obj->setCodTrabalho($fws_['cod_trabalho']);
            $obj->getTrabalhoById();
            if ($obj->getStatus()!='ATIVADA') {
                throw new Exception("Não foi possível carregar os dados do trabalho");
            }
            if ($obj->getFlgPremiado()=='1') {
                $obj->setFlgPremiado(NULL);
                $obj->setTxtPremiacao(NULL);
                $msg = "Premiação Removida";
            } else {
                $obj->setFlgPremiado('1');
                $obj->setTxtPremiacao($fws_['txt_premiacao']);
                $msg = "Premiação Consedida: <br>".$fws_['txt_premiacao'];
            }
            $obj->salvarPremiacao($debug);

        } catch (OKException $exc) {
            echo '{
                "message":"<b>'.$msg.'</b>",
                "statusMessage":"1"
                }';
            exit();
        }catch (Exception $exc) {
            echo '{
            "message":"' . $exc->getMessage() . '",
            "statusMessage":"0"
            }';
            exit();
        } 
    }
}
?>

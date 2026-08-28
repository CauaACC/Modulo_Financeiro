<?php

class GerenciarAvaliacaoPremiacaoAC extends GerenciarAvaliacaoPadraoAC{

    function __construct() {
        
    }
    public function adminIndicarAvaliador($fws_, $debug = false) {
        FWA_exigirPermissao([enumSistema::SIS_GERENCIAR_ADM_GERAL, enumSistema::SIS_GERENCIAR_AVALIACAO_TRABALHOS, enumSistema::SIS_AVALIACAO_TRABALHO, enumSistema::SIS_AVALIACAO_TRABALHO_PREMIADO]);
        try {
            try {
                if($fws_['cod_inscrito']!=''){
                    $fws_['codInscrito'] = $fws_['cod_inscrito'];
                }
                if($fws_['cod_trabalho']!=''){
                    $fws_['codTrabalho'] = $fws_['cod_trabalho'];
                }
                
                $objTrabalho = new Trabalho();
                $objTrabalho->setCodInscrito($fws_['codInscrito']);
                $objTrabalho->setCodTrabalho($fws_['codTrabalho']);
                $objTrabalho->getTrabalhoById($debug);
                if ($objTrabalho->getStatus()!='ATIVADA') {
                    throw new Exception("Não foi possível carregar os dados do trabalho");
                }
                if (($objTrabalho->getArquivoGerado() == '') and (TRABALHO_ANEXA_RESUMO)) {
                    throw new Exception("É necessário anexar o arquivo do resumo antes de envia-lo para avaliação.");
                }
                $objTrabalho->setFlgEnviadoAvaliacaoPremiacao('1');
                $objTrabalho->salvarAvaliacao($debug);
                
            } catch (OKException $exc) {      

                        $objTrabalhoHistorico = new HistoricoAvaliacaoPremiacao();
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
            }
        } catch (OKException $exc) {;
            $ok = 1;
            $objTrabalhoHistorico->carregarObjAvaliador();
            $objAvaliador = $objTrabalhoHistorico->getObjAvaliador();
            $objTrabalho->carregarObjInscrito();
            $objInscrito = $objTrabalho->getObjInscrito();
            
            if(GerenciarEmailAvaliacaoPremiacaoAC::comunicadoIndicacaoAvaliador($objTrabalho,$objTrabalhoHistorico,$objInscrito,$objAvaliador,'BOOLEAN',false)){
                $statusEnvioEmailAvaliador = "Comunicado Enviado";
            }else{
                $statusEnvioEmailAvaliador = "Comunicado NÃO Enviado";
            }
            
            if ($objTrabalhoHistorico->getAvaliacao() == 'SELECIONADO'){
                $message = "<br>Enviado para Avaliação:"
                        . "<br>E-mail avaliador: ".$objAvaliador->getEMail().""
                        . "<br>".$statusEnvioEmailAvaliador.""
//                        . "<a disable=\"\" href=\"javascript:void(0);\" "
//                        . "onclick=\"liberarDeAvaliadorPremiacao('".$objTrabalho->getCodInscrito()."','".$objTrabalho->getCodTrabalho()."','".$objAvaliador->getCodAvaliador()."')\" "
//                        . "class=\"btn btn-xs btn-primary\" role=\"button\" "
//                        . "title=\"Trocar de Avaliador Premiacao\">Trocar de Avaliador Premiacao</a>"
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

    public function adminLiberarDeAvaliador($fws_, $debug = false) {
        FWA_exigirPermissao([enumSistema::SIS_GERENCIAR_ADM_GERAL, enumSistema::SIS_GERENCIAR_AVALIACAO_TRABALHOS, enumSistema::SIS_AVALIACAO_TRABALHO, enumSistema::SIS_AVALIACAO_TRABALHO_PREMIADO]);
            try{
                $objTrabalho = new Trabalho();
                $objTrabalho->setCodInscrito($fws_['cod_inscrito']);
                $objTrabalho->setCodTrabalho($fws_['cod_trabalho']);
                $objTrabalho->getTrabalhoById();
                if ($objTrabalho->getStatus()!='ATIVADA') {
                    throw new Exception("Não foi possível carregar os dados do trabalho");
                }
                $objTrabalho->setFlgEnviadoAvaliacaoPremiacao('NULL');
                $objTrabalho->salvarAvaliacao($debug);
            } catch (OKException $exc) {
                try {
                    $objTrabalhoAvaliacao = new TrabalhoAvaliacaoPremiacao();
                    $objTrabalhoAvaliacao->setCodTrabalho($objTrabalho->getCodTrabalho());
                    $objTrabalhoAvaliacao->setCodInscrito($objTrabalho->getCodInscrito());
                    $objTrabalhoAvaliacao->setCodAvaliador($objTrabalho->getCodAvaliador());
                    $objTrabalhoAvaliacao->setCodAreaTematica($objTrabalho->getCodAreaTematica());
                    $objTrabalhoAvaliacao->getTrabalhoAvaliacaoById($debug);
                    $objTrabalhoAvaliacao->setStatus('DESATIVADA');
                    if(is_null($objTrabalhoAvaliacao->getAvaliacao())or $objTrabalhoAvaliacao->getAvaliacao()==""){
                        $objTrabalhoAvaliacao->setAvaliacao('Cancelada');
                    }
                    $objTrabalhoAvaliacao->salvarAvaliacaoLiberacao();
                } catch (OKException $exc) {
                    try {
                        $objTrabalhoHistorico = new HistoricoAvaliacaoPremiacao();
                        $objTrabalhoHistorico->setCodTrabalho($fws_['cod_trabalho']);
                        $objTrabalhoHistorico->setCodInscrito($fws_['cod_inscrito']);
                        $objTrabalhoHistorico->setCodAvaliador($fws_['cod_avaliador']);
                        $objTrabalhoHistorico->getHistoricoAvaliacaoByIdAvaliador($debug);
                        $objTrabalhoHistorico->desativarAvaliacoesAvaliador($debug);            
                    } catch (OKException $exc) {
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
        FWA_exigirPermissao([enumSistema::SIS_GERENCIAR_ADM_GERAL, enumSistema::SIS_GERENCIAR_AVALIACAO_TRABALHOS, enumSistema::SIS_AVALIACAO_TRABALHO, enumSistema::SIS_AVALIACAO_TRABALHO_PREMIADO]);
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
            
            $objTrabalhoHistorico = new HistoricoAvaliacaoPremiacao();
            $objTrabalhoHistorico->setCodTrabalho($fws_['codTrabalho']);
            $objTrabalhoHistorico->setCodInscrito($fws_['codInscrito']);
            $objTrabalhoHistorico->setCodAvaliador($fws_['codAvaliador']);
            $objTrabalhoHistorico->getHistoricoAvaliacaoByIdAvaliador($debug);
            $objTrabalhoHistorico->carregarObjAvaliador();
            $objAvaliador = $objTrabalhoHistorico->getObjAvaliador();
            
            if(GerenciarEmailAvaliacaoPremiacaoAC::cobrarAvaliador($objTrabalho,$objTrabalhoHistorico,$objInscrito,$objAvaliador,'BOOLEAN',false)){
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
    public static function carregarListaUltimasAvaliacaoPremiacaoByIdTrabalho($objL, $debug = true){
        FWA_exigirPermissao([enumSistema::SIS_GERENCIAR_ADM_GERAL, enumSistema::SIS_GERENCIAR_AVALIACAO_TRABALHOS, enumSistema::SIS_AVALIACAO_TRABALHO, enumSistema::SIS_AVALIACAO_TRABALHO_PREMIADO]);
        echo "<button type=\"button\" class=\"btn btn-xs btn-primary\" data-toggle=\"modal\" data-target=\"#modalIndicarAvaliador\""
        . " title=\"Indicar Avaliador Premiacao\" onclick=\"indicarAvaliadorPremiacao('".$objL->getCodInscrito()."','".$objL->getCodTrabalho()."')\">"
        . "Inidicar Avaliador Premiacao</button><br><hr><br>";
        try {
            $obj = new HistoricoAvaliacaoPremiacao();
            $obj->setCodTrabalho($objL->getCodTrabalho());
            $obj->setCodInscrito($objL->getCodInscrito());
            $obj->carregarListaUltimasAvaliacaoPremiacaoByIdTrabalho($debug);
            
            $lista = $obj->getListaUltimasAvaliacaoPremiacao();
            if ($lista) {
                $totalNota = 0;
                $totalAvaliacaoConcluida = 0;
                foreach ($lista as $objHP) {
                    $i++;
                    echo "<div id='msgUT-".$objHP->getCodTrabalho()."-".$objHP->getCodAvaliador()."'>";
                    if($objHP->getAvaliacao()=='APROVADO' OR $objHP->getAvaliacao()=='REPROVADO'){
                        echo "<b>AVALIAÇÃO CONCLUÍDA</b>";
                        $totalAvaliacaoConcluida ++;
                    }else{
                        echo "<b>".$objHP->getAvaliacao()."</b>";
                    }
                    echo "<br>".$objHP->getLogin();
                    /*Calculando atraso na avaliação*/
                    $dateInterval    = "";
                    $cobrarAvaliador = "";
                    if($objHP->getAvaliacao()=='SELECIONADO'){
                        $data_inicio = new DateTime($objHP->getDataInclusao());
                        $data_fim    = new DateTime(Date('Y-m-d H:i:s'));
                        $dateInterval = $data_inicio->diff($data_fim);
                        if ($dateInterval->days >= 7) {
                            echo "<br><font color=red>".$dateInterval->days." da ultima tramitação</font>";
                            $avaliacaoAtrasada++;
                            $cobrarAvaliador = 1;
                        }   
                        if ($cobrarAvaliador){
                            echo "<br><a disable href=\"javascript:void(0);\" onclick=\"cobrarAvaliadorPremiacao('".$objHP->getCodInscrito()."','".$objHP->getCodTrabalho()."','".$objHP->getCodAvaliador()."')\"
                            class='btn btn-xs btn-primary' role='button' title='Cobrar Avaliador Premiacao'>Cobrar Avaliador Premiacao</a>";
                        }
                        echo "<br><a disable href=\"javascript:void(0);\" onclick=\"liberarDeAvaliadorPremiacao('".$objHP->getCodInscrito()."','".$objHP->getCodTrabalho()."','".$objHP->getCodAvaliador()."')\"
                        class='btn btn-xs btn-primary' role='button' title='Trocar de Avaliador Premiacao'>Trocar de Avaliador Premiacao</a>";
                    }else{
                        echo "<br>".$i."° Nota: ".$objHP->getNota()." pontos";
                        $totalNota = $totalNota+$objHP->getNota();
                    }
                    echo "<br>Tramitação:".formatDateView($objHP->getDataInclusao());
                    echo "</div>";
                    echo "<br><hr>";
                }
                echo "<hr>Total Avaliações Designadas:".$i."<br>";
                if($totalAvaliacaoConcluida==2){
                    echo "<font color=green><b>"
                    . "Avaliações Concluídas:".$totalAvaliacaoConcluida."<br>"
                    . "Nota Final:".$totalNota." pontos<br>"
                    . "</b></font><br>&nbsp;";
                }else{
                    echo "<font color=red>"
                    . "Avaliações Concluídas:".$totalAvaliacaoConcluida."<br>"
                    . "Total Nota:".$totalNota." pontos<br>"
                    . "</font><br>&nbsp;";
                }
                
            } else {
//                throw new Exception("Nenhuma avaliação de premiação definida.");
            }
        } catch (Exception $e) {
            if ($e->getMessage()) {
                echo $e->getMessage();
            }
        }
        
    }
}
?>

<?php

class GerenciarTrabalhoAC {
    
    /**
     * 
     * @param type $obj Trabalho
     * @param type $flgPresenca (0 para não compareceu) ou (1 para compareceu)
     * @param type $debug
     * @throws Exception
     */
    public static function definirPresenca($obj,$flgPresenca, $debug=false){
        FWA_exigirPermissao([enumSistema::SIS_GERENCIAR_ADM_GERAL, enumSistema::SIS_GERENCIAR_AVALIACAO_TRABALHOS, enumSistema::SIS_AVALIACAO_TRABALHO]);
        
        try {
            if ($obj->getStatus()!='ATIVADA') {
                throw new Exception("Não foi possível carregar os dados do trabalho");
            }
            if ($flgPresenca=='presenca') {
                $obj->setFlgPresenca('1');
                $msg = "compareceu";
            } else {
                $obj->setFlgPresenca(NULL);
                $msg = "Não compareceu";
            }
            $obj->salvarPresenca($debug);
        }catch (OKException $e){
        }catch (Exception $e){
            $msg .= "<br>linha: ".$ii.":".$e->getMessage();
        }
        echo $msg."<hr>";        
        
    }
    
    public function alternarPresenca($fws_, $debug = false) {
        FWA_exigirPermissao([enumSistema::SIS_GERENCIAR_ADM_GERAL, enumSistema::SIS_GERENCIAR_AVALIACAO_TRABALHOS, enumSistema::SIS_AVALIACAO_TRABALHO]);
        try {
            $obj = new Trabalho();
            $obj->setCodInscrito($fws_['cod_inscrito']);
            $obj->setCodTrabalho($fws_['cod_trabalho']);
            $obj->getTrabalhoById();
            if ($obj->getStatus()!='ATIVADA') {
                throw new Exception("Não foi possível carregar os dados do trabalho");
            }
            $flgPresenca = $obj->getFlgPresenca();
            if ($obj->getFlgPresenca()=='1') {
                $obj->setFlgPresenca(NULL);
                $msg = "Não compareceu";
            } else {
                $obj->setFlgPresenca('1');
                $msg = "Compareceu";
            }
            $obj->salvarPresenca($debug);

        }catch (OKException $exc) {
            $flgPresenca = $obj->getFlgPresenca();
            $statusExcec = 1;
        }catch (Exception $exc) {
            $statusExcec = 0;
        } 
        echo '{
            "flgPresenca":"'.$flgPresenca.'",
            "message":"<b>'.$msg.'</b>",
            "statusMessage":"'.$statusExcec.'"
            }';
        exit();
    }
    public function alternarFlgAssinado($fws_, $debug = false) {
        FWA_exigirPermissao([enumSistema::SIS_GERENCIAR_ADM_GERAL, enumSistema::SIS_GERENCIAR_AVALIACAO_TRABALHOS, enumSistema::SIS_AVALIACAO_TRABALHO]);
        try {
            $obj = new Trabalho();
            $obj->setCodInscrito($fws_['cod_inscrito']);
            $obj->setCodTrabalho($fws_['cod_trabalho']);
            $obj->getTrabalhoById();
            if ($obj->getStatus()!='ATIVADA') {
                throw new Exception("Não foi possível carregar os dados do trabalho");
            }
            $flgAssinado = $obj->getFlgAssinado();
            if ($obj->getFlgAssinado()=='1') {
                $obj->setFlgAssinado(NULL);
                $msg = "Não assinado";
            } else {
                $obj->setFlgAssinado('1');
                $msg = "assinado";
            }
            $obj->salvarAssinado($debug);

        }catch (OKException $exc) {
            $flgAssinado = $obj->getFlgAssinado();
            $statusExcec = 1;
        }catch (Exception $exc) {
            $statusExcec = 0;
        } 
        echo '{
            "flgAssinado":"'.$flgAssinado.'",
            "message":"<b>'.$msg.'</b>",
            "statusMessage":"'.$statusExcec.'"
            }';
        exit();
    }
}


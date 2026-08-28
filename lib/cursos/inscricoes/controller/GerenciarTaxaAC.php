<?php

class GerenciarTaxaAC {

    function __construct() {
        FWA_exigirPermissao([enumSistema::SIS_GERENCIAR_ADM_GERAL, enumSistema::SIS_CONTROLE_FINANCEIRO]);
    }

    public function duplicar($fws_, $debug=false){
        try{
            $obj = new Taxa();
            $obj->setCodTaxa($fws_['codTaxa']);
            $obj->setCodEvento($fws_['codEvento']);
            $obj->getTaxaById();
            $obj->duplicar($debug);
        }catch (OKException $exc) {
            $statusExcec = 1;
        }catch (Exception $exc) {
            $statusExcec = 0;
        } 
        echo '{
            "codTaxa":"'.$obj->getCodTaxa().'",
            "codPerfil":"'.$obj->getCodPerfil().'",
            "codOficina":"'.$obj->getCodOficina().'",
            "message":"<b>'.$exc->getMessage().'</b>",
            "statusMessage":"'.$statusExcec.'"
            }';
        exit();
    }
    public function salvarTaxa($fws_, $debug = false) {
        try {
            // if ($fws_['novoCodTaxa'] == 0) {
            //     $fws_['codTaxa'] = NULL;
            // }
            $isForm = false;
            $obj = $this->carregarTaxa($fws_);
            $obj->setAll($fws_, $isForm);
            $obj->salvar($debug);
        } catch (OkException $e) {
            $ok = true;
        } catch (Exception $e) {
            $ok = false;
        }

        echo '{
        "statusMessage":"' . $ok . '",
        "message":      "' . tratarJson($e->getMessage()) . '",
        "codTaxa":"' . $obj->getCodTaxa() . '"
        }';
    }    

    public function carregarTaxa($fws_, $debug = false) {
        $obj = new Taxa();
        $obj->setCodTaxa($fws_['codTaxa']);
        $obj->getTaxaById($debug);
        return $obj;
    }       
}

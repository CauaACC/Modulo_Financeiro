<?php

class GerenciarEnsalamentoAC {

    function __construct() {
        FWA_exigirPermissao(enumSistema::SIS_GERENCIAR_ADM_GERAL);
    }
    
    public function alternarEnsalamento($fws_, $debug = false) {
        try {
            $objGU = new Ensalamento();
            $objGU->setCodSessao($fws_['cod_sessao']);
            $objGU->setCodTrabalho($fws_['cod_trabalho']);

            if($fws_['acao_ensalamento']=='remover'){
                $objGU->removerTrabalhoSessao();
            }else if ($fws_['acao_ensalamento']=='adicionar'){
                $objGU->adicionarTrabalhoSessao();
            }else{
                throw new Exception("ação não definida");
            }
        } catch (OKException$exc) {
            echo '{
            "message":"'.$exc->getMessage().'",
            "statusMessage":"1"
            }';
        } catch (Exception $exc) {
            echo '{
            "message":"'.$exc->getMessage().'",
            "statusMessage":"0"
            }';
        }
        exit();
    }
    
    public function moverSessaoAcima($fws_, $debug = false) {
        try {
            $objGU = new Ensalamento();
            $objGU->setCodSessao($fws_['cod_sessao']);
            $objGU->setCodTrabalho($fws_['cod_trabalho']);
            try { $objGU->removerTrabalhoSessao(); } catch (OKException $exc) { }
            
            $objGU->setCodSessao($fws_['cod_sessao_ant']);
            $objGU->setCodTrabalho($fws_['cod_trabalho_ant']);
            try { $objGU->removerTrabalhoSessao(); } catch (OKException $exc) { };
            
            $objGU->setCodSessao($fws_['cod_sessao_ant']);
            $objGU->setCodTrabalho($fws_['cod_trabalho']);
            try { $objGU->adicionarTrabalhoSessao(); } catch (OKException $exc) { }
            
            $objGU->setCodSessao($fws_['cod_sessao']);
            $objGU->setCodTrabalho($fws_['cod_trabalho_ant']);
            try { $objGU->adicionarTrabalhoSessao(); } catch (OKException $exc) { }
            
            throw new OkException("Trabalho movido para sessão anterior.");

        } catch (OKException$exc) {
            echo '{
            "message":"'.$exc->getMessage().'",
            "statusMessage":"1"
            }';
        } catch (Exception $exc) {
            echo '{
            "message":"'.$exc->getMessage().'",
            "statusMessage":"0"
            }';
        }
        exit();
    }
    
}

?>

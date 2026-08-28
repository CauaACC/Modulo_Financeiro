<?php

class GerenciarConvidadosAC {

    function __construct() {
        FWA_exigirPermissao(enumSistema::SIS_GERENCIAR_ADM_GERAL);
    }
    
    public function alternarUsuarioGrupo($fws_, $debug = false) {
        try {
            $objGU = new UsuarioGrupo();
            $objGU->setCodUsuario($fws_['cod_usuario']);
            $objGU->setCodGrupo($fws_['cod_grupo']);

            if($fws_['acao_grupo']=='remover'){
                $objGU->removerUsuarioGrupo();
            }else if ($fws_['acao_grupo']=='adicionar'){
                $objGU->adicionarUsuarioGrupo();
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
    
    public function alternarUsuarioAreaTematica($fws_, $debug = false) {
        try {
            $objGU = new UsuarioAreaTematica();
            $objGU->setCodUsuario($fws_['cod_usuario']);
            $objGU->setCodAreaTematica($fws_['cod_area_tematica']);

            if($fws_['acao_area_tematica']=='remover'){
                $objGU->removerUsuarioAreaTematica();
            }else if ($fws_['acao_area_tematica']=='adicionar'){
                $objGU->adicionarUsuarioAreaTematica();
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
    
}

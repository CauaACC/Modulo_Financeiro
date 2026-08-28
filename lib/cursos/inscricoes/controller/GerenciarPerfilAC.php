<?php

class GerenciarPerfilAC {
    
    /**
     * 
     * @param type $obj Perfil
     * @param type $flgPresenca (0 para não compareceu) ou (1 para compareceu)
     * @param type $debug
     * @throws Exception
     */
    public static function definirPresenca($obj,$flgPresenca, $debug=false){
        
        try {
            if ($obj->getStatus()!='ATIVADA') {
                throw new Exception("Não foi possível carregar os dados do oficina");
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
        try {
            $obj = new Perfil();
            $obj->setCodInscrito($fws_['cod_inscrito']);
            $obj->setCodPerfil($fws_['cod_oficina']);
            $obj->getPerfilById();
            if ($obj->getStatus()!='ATIVADA') {
                throw new Exception("Não foi possível carregar os dados do oficina");
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
    
    public function duplicar($fws_, $debug=false){

        try{
            $obj = new Perfil();
            $obj->setCodPerfil($fws_['codPerfil']);
            $obj->duplicar($debug);
        }catch (OKException $exc) {
            $statusExcec = 1;
        }catch (Exception $exc) {
            $statusExcec = 0;
        } 
        echo '{
            "codPerfil":"'.$obj->getCodPerfil().'",
            "message":"<b>'.$exc->getMessage().'</b>",
            "statusMessage":"'.$statusExcec.'"
            }';
        exit();
    }

    public function salvarPerfil($fws_, $debug = false) {
        try {
            // if ($fws_['novoCodPerfil'] == 0) {
            //     $fws_['codPerfil'] = NULL;
            // }
            $isForm = false;
            $obj = $this->carregarPerfil($fws_);
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
        "codPerfil":"' . $obj->getCodPerfil() . '"
        }';
    }    

    public function carregarPerfil($fws_, $debug = false) {
        $obj = new Perfil();
        $obj->setCodPerfil($fws_['codPerfil']);
        $obj->getPerfilById($debug);
        return $obj;
    }    

    public function toggleCampo($fws_, $debug = false) {
        try {
            if (!FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_CONTROLE_CONFIGURACAO, 5, false, false)) {
                echo '{"statusMessage":"0","message":"Acesso negado.","novoValor":"","novoLabel":""}';
                return;
            }

            $obj = new Perfil();
            $obj->setCodPerfil($fws_['codPerfil']);
            $obj->getPerfilById($debug);
            $campo = $fws_['campo'];

            if ($obj->toggleCampo($campo, $debug)) {
                $obj->getPerfilById($debug);

                if ($campo == 'status') {
                    $novoValor = $obj->getStatus();
                    $novoLabel = ($novoValor == 'ATIVADA') ? 'Ativada' : 'Desativada';
                } else {
                    $getter = 'get' . str_replace('_', '', ucwords($campo, '_'));
                    $novoValor = call_user_func(array($obj, $getter));
                    $novoLabel = ($novoValor == 1) ? 'Sim' : 'Não';
                }
                $ok = true;
                $msg = 'Campo atualizado com sucesso.';
            } else {
                $ok = false;
                $msg = 'Campo não permitido.';
                $novoValor = '';
                $novoLabel = '';
            }
        } catch (Exception $e) {
            $ok = false;
            $msg = $e->getMessage();
            $novoValor = '';
            $novoLabel = '';
        }

        echo '{
        "statusMessage":"' . ($ok ? '1' : '0') . '",
        "message":"' . tratarJson($msg) . '",
        "novoValor":"' . $novoValor . '",
        "novoLabel":"' . $novoLabel . '"
        }';
    }

    public function excluirPerfil($fws_, $debug = false) {
        try {
            $obj = new Perfil();
            $obj->setCodPerfil($fws_['codPerfil']);
            $obj->excluir();
        } catch (OKException $e) {
            $ok = true;
        } catch (Exception $e) {
            $ok = false;
        }

        echo '{
        "statusMessage":"' . $ok . '",
        "message":      "' . tratarJson( $e->getMessage()) . '",
        "codPerfil":"' . $fws_['codPerfil'] . '"
        }';
    }
}

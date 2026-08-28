<?php

class GerenciarOficinaAC {
    
    /**
     * 
     * @param type $obj Oficina
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
            $obj = new Oficina();
            $obj->setCodInscrito($fws_['cod_inscrito']);
            $obj->setCodOficina($fws_['cod_oficina']);
            $obj->getOficinaById();
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
            $obj = new Oficina();
            $obj->setCodOficina($fws_['codOficina']);
            $obj->duplicar($debug);
        }catch (OKException $exc) {
            $statusExcec = 1;
        }catch (Exception $exc) {
            $statusExcec = 0;
        } 
        echo '{
            "codOficina":"'.$obj->getCodOficina().'",
            "message":"<b>'.$exc->getMessage().'</b>",
            "statusMessage":"'.$statusExcec.'"
            }';
        exit();
    }
    public function salvarOficina($fws_, $debug = false) {
        try {
            // if ($fws_['novoCodOficina'] == 0) {
            //     $fws_['codOficina'] = NULL;
            // }
            $isForm = false;
            // data_ini (legado) e lida pela area do inscrito e pelo conflito de horario;
            // manter espelhada com data_inicio, igual a tela antiga oficina-cadastro-alterar-grava.php
            // (prefixado no array: o setAll() para no _submit_check, que vem por ultimo no form)
            if (isset($fws_['data_inicio'])) {
                $fws_ = array('data_ini' => $fws_['data_inicio']) + $fws_;
            }
            $obj = $this->carregarOficina($fws_);
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
        "codOficina":"' . $obj->getCodOficina() . '"
        }';
    }    

    public function carregarOficina($fws_, $debug = false) {
        $obj = new Oficina();
        $obj->setCodOficina($fws_['codOficina']);
        $obj->getOficinaById($debug);
        return $obj;
    }    

    public function toggleCampo($fws_, $debug = false) {
        try {
            if (!FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_CONTROLE_CONFIGURACAO, 5, false, false)) {
                echo '{"statusMessage":"0","message":"Acesso negado.","novoValor":"","novoLabel":""}';
                return;
            }

            $obj = new Oficina();
            $obj->setCodOficina($fws_['codOficina']);
            $obj->getOficinaById($debug);
            $campo = $fws_['campo'];

            if ($obj->toggleCampo($campo, $debug)) {
                $obj->getOficinaById($debug);

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

    public function excluirOficina($fws_, $debug = false) {
        try {
            $obj = new Oficina();
            $obj->setCodOficina($fws_['codOficina']);
            $obj->excluir();
        } catch (OKException $e) {
            $ok = true;
        } catch (Exception $e) {
            $ok = false;
        }

        echo '{
        "statusMessage":"' . $ok . '",
        "message":      "' . tratarJson( $e->getMessage()) . '",
        "codOficina":"' . $fws_['codOficina'] . '"
        }';
    }
}

<?php

class GerenciarInscritoVoucherAC {

    function __construct() {
        FWA_exigirPermissao(enumSistema::SIS_GERENCIAR_ADM_GERAL);
        
    }

    public function carregarInscritoVoucher($fws_, $debug = false) {
        $obj = new InscritoVoucher();
        $obj->setCodInscrito($fws_['codInscrito']);
        $obj->setCodVoucher($fws_['codVoucher']);
        $obj->getInscritoVoucherById($debug);
        return $obj;
    }

    public function adicionarInscritoVoucher($fws_, $debug = false) {
        try {
            $obj = new InscritoVoucher();
            $obj->setCodInscrito($fws_['codInscrito']);
            $obj->setCodVoucher($fws_['codVoucher']);
            if($obj->inscritoPossuiVoucher($debug)){
                throw new Exception('Inscrito já possui voucher cadastrado!');
            }
            if($obj->voucherUtilizado($debug)){
                throw new Exception('Voucher já utilizado em outra inscrição!');
            }
            if(!$obj->voucherExiste($debug)){
                throw new Exception('Voucher não encontrado, informar outro voucher!');
            }
            if(!$obj->testeQtdMaximaBySiglaCategoria($debug)){
                throw new Exception('Quantidade máxima por patrocinador atingida, entre em contato fornecedor do voucher ou informe outro voucher!');
            }
            //echo $obj->getCodInscrito() . '-'.$obj->getCodVoucher();
            $obj->salvar($debug);
        }catch (OKException $exc) {
            $msg = "Voucher adicionado: ".$fws_['codVoucher'];
            $statusExcec = 1;
        }catch (Exception $exc) {
            $msg = $exc->getMessage()." Voucher ".$fws_['codVoucher']." não adicionado.";
            $statusExcec = 0;
        } 
        echo '{
            "message":"<b>'.$msg.'</b>",
            "statusMessage":"'.$statusExcec.'"
            }';
        exit();
    }   

    public function excluirInscritoVoucher($fws_, $debug = false) {
        try {
            $obj = new InscritoVoucher();
            $obj->setCodInscrito($fws_['codInscrito']);
            $obj->setCodVoucher($fws_['codVoucher']);
            $obj->excluir();
        } catch (OKException $e) {
            $ok = true;
        } catch (Exception $e) {
            $ok = false;
        }

        echo '{
        "statusMessage":"' . $ok . '",
        "message":      "' . tratarJson( $e->getMessage()) . '",
        "codVoucher":"' . $fws_['codVoucher'] . '"
        }';
    }
}
?>
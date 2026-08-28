<?php

class GerenciarCentroCustoAC {

    function __construct() {
        FWA_exigirPermissao([enumSistema::SIS_GERENCIAR_ADM_GERAL, enumSistema::SIS_CONTROLE_FINANCEIRO]);
        
    }

    public function carregarCentroCusto($fws_, $debug = false) {
        $obj = new CentroCusto();
        if ($fws_['codCentroCusto']) {
            $obj->setCodCentroCusto($fws_['codCentroCusto']);
            $obj->getCentroCustoById($debug);
        }
        return $obj;
    }

    public function editarCentroCusto($fws_, $debug = false) {
        $obj = $this->carregarCentroCusto($fws_,$debug);

        echo '{
                  "statusMessage":"' . $ok . '",
                  "message":      "' . $obj->getMessage() . '",
                  "codCentroCusto": "' . $obj->getCodCentroCusto() . '",
                  "codCentroCustoPai": "' . $obj->getCodCentroCustoPai() . '",
                  "nomeCentroCusto":"' . $obj->getNomeCentroCusto() . '",
                  "codigoContabilCC":"' . $obj->getCodigoContabilCC() . '",
                  "descricao":"' . $obj->getDescricao() . '",
                  "status":"' . $obj->getStatus() . '"
                 }';
        exit();
    }

    public function salvarCentroCusto($fws_, $debug = false) {
        try {
            $isForm = true;
            $obj = $this->carregarCentroCusto($fws_);
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
        "codCentroCusto":"' . $obj->getCodCentroCusto() . '"
        }';
    }

    public function excluirCentroCusto($fws_, $debug = false) {
        try {
            $obj = new CentroCusto();
            $obj->setCodCentroCusto($fws_['codCentroCusto']);
            $obj->excluir();
        } catch (OKException $e) {
            $ok = true;
        } catch (Exception $e) {
            $ok = false;
        }

        echo '{
        "statusMessage":"' . $ok . '",
        "message":      "' . tratarJson( $e->getMessage()) . '",
        "codCentroCusto":"' . $fws_['codCentroCusto'] . '"
        }';
    }

    public function carregarTabelaCentroCustos($fws_, $debug = false) {
        $obj = new CentroCusto();
        $objEv = $obj->getEvento();
        try {
            $obj->carregarListaCentroCusto(false, $debug);
            $objEv->getGestor();
            $lista = $obj->getListaCentroCusto();
            foreach ($lista as $objL) {
                $ii++;
                ?>  
                <tr id="<?php echo  $objL->getCodCentroCusto() ?>">
                    <td>
                        <span class="editar btn-acao" title="Editar"><i class="fa fa-pencil"></i></span>
                        <?php if ($objL->getTotalFilho() <= 0) {?>
                        <span class="excluir btn-acao btn-acao-excluir" title="Excluir"><i class="fa fa-trash"></i></span>
                        <?php } ?>
                    </td>
                    <td>
                        <?php echo $objL->getCodigoContabilCC(); ?>
                    </td>
                    <td class="col-nome">
                        <?php echo $objL->getNomeCentroCusto(); ?>
                    </td>
                    <td>
                        <?php echo $objL->getDescricao(); ?>
                    </td>
                </tr>
                <?php
            }
            ?>
                </tbody><tfoot><tr><td colspan="4" style="text-align:right; color:#999; font-size:12px; padding:8px 10px; border-top:1px solid #eee;">Total: <?php echo $ii ?> registro<?php echo $ii != 1 ? 's' : '' ?></td></tr></tfoot><tbody class="inputs" style="display:none;">
            <?php
        } catch (Exception $e) {
            echo "<tr><td></td><td colspan=3>";
            if ($e->getMessage()) {
                echo $e->getMessage();
            } else {
                echo "Lista vazia.";
            }
            echo "</td></tr>";
        }
    }    
}

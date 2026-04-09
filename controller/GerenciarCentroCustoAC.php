<?php

class GerenciarCentroCustoAC {

    function __construct() {
        
    }

    public function carregarCentroCusto($fws_, $debug = false) {
        $obj = new CentroCusto();
        $obj->setCodCentroCusto($fws_['codCentroCusto']);
        $obj->getCentroCustoById($debug);
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
            // if ($fws_['novoCodCentroCusto'] == 0) {
            //     $fws_['codCentroCusto'] = NULL;
            // }
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
                        <?php //if ($objEv->controlarAcessoProposta('boolean')) { ?>
                        <img class="editar" src="<?php echo  WB_PATH ?>/resources/image/alterar.gif" alt="Editar CentroCusto" border="0">
                        <?php if ($objL->getTotalFilho() <= 0) {?>
                        <img class="excluir" src="<?php echo  WB_PATH ?>/resources/image/fig-excluir.gif" alt="Excluir Rateios" border="0">                        
                        <?php } ?>
                        <?php //} ?>
                    </td>
                    <td>
                        <?php echo $objL->getCodigoContabilCC(); ?>
                    </td>
                    <td>
                        <?php echo $objL->getNomeCentroCusto(); ?>
                    </td>
                    <td>
                        <?php echo $objL->getDescricao(); ?>
                    </td>
                </tr>
                <?php
            }
            ?>
                <br>Total de Centros de Custo cadastradas: <?php echo  $ii ?>.
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

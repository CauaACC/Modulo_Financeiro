<?php

class GerenciarRateiosAC {

    function __construct() {
        
    }

    public function carregarRateios($fws_, $debug = false) {
        $obj = new Rateios();
        $obj->setcodRateio($fws_['codRateio']);
        $obj->getRateiosById($debug);
        return $obj;
    }

    public function editarRateios($fws_, $debug = false) {
        $obj = $this->carregarRateios($fws_,$debug);

        echo '{
                  "statusMessage":"' . $ok . '",
                  "message":      "' . $obj->getMessage() . '",
                  "codRateio": "' . $obj->getCodRateio() . '",
                  "codRateioPai": "' . $obj->getCodRateioPai() . '",
                  "nomeRateio":"' . $obj->getNomeRateio() . '",
                  "codigoContabilRateio":"' . $obj->getCodigoContabilRateio() . '",
                  "percentual":"' . $obj->getPercentual() . '",
                  "descricao":"' . $obj->getDescricao() . '",
                  "status":"' . $obj->getStatus() . '"
                 }';
        exit();
    }

    public function salvarRateios($fws_, $debug = false) {
        try {
            // if ($fws_['novocodRateio'] == 0) {
            //     $fws_['codRateio'] = NULL;
            // }
            $isForm = true;
            $obj = $this->carregarRateios($fws_);
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
        "codRateio":"' . $obj->getCodRateio() . '"
        }';
    }

    public function excluirRateios($fws_, $debug = false) {
        try {
            $obj = new Rateios();
            $obj->setcodRateio($fws_['codRateio']);
            $obj->excluir();
        } catch (OKException $e) {
            $ok = true;
        } catch (Exception $e) {
            $ok = false;
        }

        echo '{
        "statusMessage":"' . $ok . '",
        "message":      "' . tratarJson( $e->getMessage()) . '",
        "codRateio":"' . $fws_['codRateio'] . '"
        }';
    }

    public function carregarTabelaRateioss($fws_, $debug = false) {
        $obj = new Rateios();
        $objEv = $obj->getEvento();
        try {
            $obj->carregarListaRateios(false, $debug);
            $objEv->getGestor();
            $lista = $obj->getListaRateios();
            foreach ($lista as $objL) {
                $ii++;
                ?>  
                <tr id="<?php echo  $objL->getCodRateio() ?>">
                    <td>
                        <?php //if ($objEv->controlarAcessoProposta('boolean')) { ?>
                        <img class="editar" src="<?php echo  WB_PATH ?>/resources/image/alterar.gif" alt="Editar Rateios" border="0">
                        <?php if ($objL->getTotalFilho() <= 0) {?>
                        <img class="excluir" src="<?php echo  WB_PATH ?>/resources/image/fig-excluir.gif" alt="Excluir Rateios" border="0">                        
                        <?php } ?>
                        <?php //} ?>
                    </td>
                    <td>
                        <?php echo $objL->getCodigoContabilRateio(); ?>
                    </td>
                    <td>
                        <?php echo $objL->getNomeRateio(); ?>
                    </td>
                    <td>
                        <?php echo $objL->getPercentual(); ?>
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

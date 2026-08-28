<?php

class GerenciarRateiosAC {

    function __construct() {
        FWA_exigirPermissao([enumSistema::SIS_GERENCIAR_ADM_GERAL, enumSistema::SIS_CONTROLE_FINANCEIRO]);
        
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
                  "codRateioPrincipal": "' . $obj->getCodRateioPrincipal() . '",
                  "codCentroCusto": "' . $obj->getCodCentroCusto() . '",
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
                $ehFilho = ($objL->getCodRateioPrincipal() != null && $objL->getCodRateioPrincipal() != '');
                $classeHierarquia = $ehFilho ? 'rateio-filho' : 'rateio-pai';
                ?>
                <tr id="<?php echo  $objL->getCodRateio() ?>" data-cod-rateio-principal="<?php echo $objL->getCodRateioPrincipal() ?>" class="<?php echo $classeHierarquia ?>">
                    <td>
                        <span class="editar btn-acao" title="Editar"><i class="fa fa-pencil"></i></span>
                        <?php if ($objL->getTotalFilho() <= 0) {?>
                        <span class="excluir btn-acao btn-acao-excluir" title="Excluir"><i class="fa fa-trash"></i></span>
                        <?php } ?>
                    </td>
                    <td class="col-codigo">
                        <?php if ($ehFilho) { ?><span class="tree-indent"></span><?php } ?>
                        <?php echo $objL->getCodigoContabilRateio(); ?>
                    </td>
                    <td class="col-nome">
                        <?php if ($ehFilho) { ?><span class="tree-connector">└</span><?php } ?>
                        <?php echo $objL->getNomeRateio(); ?>
                    </td>
                    <td>
                        <?php echo $objL->getNomeCentroCusto(); ?>
                    </td>
                    <td class="col-num">
                        <?php echo rtrim(rtrim(number_format($objL->getPercentual(), 4, ',', '.'), '0'), ','); ?>%
                    </td>
                    <td>
                        <?php echo $objL->getDescricao(); ?>
                    </td>
                </tr>
                <?php
            }
            ?>
                </tbody><tfoot><tr><td colspan="6" style="text-align:right; color:#999; font-size:12px; padding:8px 10px; border-top:1px solid #eee;">Total: <?php echo $ii ?> registro<?php echo $ii != 1 ? 's' : '' ?></td></tr></tfoot><tbody class="inputs" style="display:none;">
            <?php
        } catch (Exception $e) {
            echo "<tr><td></td><td colspan=5>";
            if ($e->getMessage()) {
                echo $e->getMessage();
            } else {
                echo "Lista vazia.";
            }
            echo "</td></tr>";
        }
    }    
}

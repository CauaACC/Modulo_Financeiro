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
        $ok = true;
        $message = '';
        try {
            $obj = $this->carregarRateios($fws_, $debug);
        } catch (Exception $e) {
            $ok = false;
            $message = $e->getMessage();
            $obj = new Rateios();
        }

        header('Content-Type: application/json; charset=UTF-8');
        echo '{
                  "statusMessage":"' . $ok . '",
                  "message":      "' . tratarJson($message) . '",
                  "codRateio": "' . $obj->getCodRateio() . '",
                  "codRateioPrincipal": "' . $obj->getCodRateioPrincipal() . '",
                  "codCentroCusto": "' . $obj->getCodCentroCusto() . '",
                  "nomeRateio":"' . tratarJson($obj->getNomeRateio()) . '",
                  "codigoFinanceiroRateio":"' . tratarJson($obj->getCodigoFinanceiroRateio()) . '",
                  "percentual":"' . $obj->getPercentual() . '",
                  "descricao":"' . tratarJson($obj->getDescricao()) . '",
                  "status":"' . $obj->getStatus() . '"
                 }';
        exit();
    }

    public function salvarRateios($fws_, $debug = false) {
        $ok = false;
        $message = '';
        $obj = new Rateios();
        try {
            $isForm = true;
            $obj = $this->carregarRateios($fws_);
            $obj->setAll($fws_, $isForm);
            $obj->salvar($debug);
        } catch (OKException $e) {
            $ok = true;
            $message = $e->getMessage();
        } catch (Exception $e) {
            $ok = false;
            $message = $e->getMessage();
        }

        header('Content-Type: application/json; charset=UTF-8');
        echo '{
        "statusMessage":"' . $ok . '",
        "message":      "' . tratarJson($message) . '",
        "codRateio":"' . $obj->getCodRateio() . '"
        }';
    }

    public function excluirRateios($fws_, $debug = false) {
        $ok = false;
        $message = '';
        try {
            $obj = new Rateios();
            $obj->setcodRateio($fws_['codRateio']);
            $obj->excluir();
        } catch (OKException $e) {
            $ok = true;
            $message = $e->getMessage();
        } catch (Exception $e) {
            $ok = false;
            $message = $e->getMessage();
        }

        header('Content-Type: application/json; charset=UTF-8');
        echo '{
        "statusMessage":"' . $ok . '",
        "message":      "' . tratarJson($message) . '",
        "codRateio":"' . $fws_['codRateio'] . '"
        }';
    }

    public function carregarTabelaRateioss($fws_, $debug = false) {
        $obj = new Rateios();
        $objEv = $obj->getEvento();
        $ii = 0;
        try {
            $obj->carregarListaRateios(false, $debug);
            $objEv->getGestor();
            $lista = $obj->getListaRateios();
            foreach ($lista as $objL) {
                $ii++;
                $ehFilho = ($objL->getCodRateioPrincipal() != null && $objL->getCodRateioPrincipal() != '');
                $classeHierarquia = $ehFilho ? 'rateio-filho' : 'rateio-pai';
                $codigo = $objL->getCodigoFinanceiroRateio();
                $nivel = $codigo ? substr_count($codigo, '.') : 0;
                ?>
                <tr id="<?php echo  $objL->getCodRateio() ?>" data-cod-rateio-principal="<?php echo $objL->getCodRateioPrincipal() ?>" class="<?php echo $classeHierarquia ?>">
                    <td>
                        <span class="editar btn-acao" title="Editar"><i class="fa fa-pencil"></i></span>
                        <?php if ($objL->getTotalFilho() <= 0) {?>
                        <span class="excluir btn-acao btn-acao-excluir" title="Excluir"><i class="fa fa-trash"></i></span>
                        <?php } ?>
                    </td>
                    <td class="col-codigo">
                        <?php for ($i = 0; $i < $nivel; $i++) { ?><span class="tree-indent"></span><?php } ?>
                        <?php echo $codigo; ?>
                    </td>
                    <td class="col-nome">
                        <?php for ($i = 0; $i < $nivel - 1; $i++) { ?><span class="tree-indent"></span><?php } ?>
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

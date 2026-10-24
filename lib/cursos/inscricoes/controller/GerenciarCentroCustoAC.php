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
        $ok = true;
        $message = '';
        try {
            $obj = $this->carregarCentroCusto($fws_, $debug);
        } catch (Exception $e) {
            $ok = false;
            $message = $e->getMessage();
            $obj = new CentroCusto();
        }

        header('Content-Type: application/json; charset=UTF-8');
        echo '{
                  "statusMessage":"' . $ok . '",
                  "message":      "' . tratarJson($message) . '",
                  "codCentroCusto": "' . $obj->getCodCentroCusto() . '",
                  "codCentroCustoPai": "' . $obj->getCodCentroCustoPai() . '",
                  "nomeCentroCusto":"' . tratarJson($obj->getNomeCentroCusto()) . '",
                  "codigoFinanceiro":"' . tratarJson($obj->getCodigoFinanceiro()) . '",
                  "descricao":"' . tratarJson($obj->getDescricao()) . '",
                  "status":"' . $obj->getStatus() . '"
                 }';
        exit();
    }

    public function salvarCentroCusto($fws_, $debug = false) {
        $ok = false;
        $message = '';
        $obj = new CentroCusto();
        try {
            $isForm = true;
            $obj = $this->carregarCentroCusto($fws_);
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
        "codCentroCusto":"' . $obj->getCodCentroCusto() . '"
        }';
    }

    public function excluirCentroCusto($fws_, $debug = false) {
        $ok = false;
        $message = '';
        try {
            $obj = new CentroCusto();
            $obj->setCodCentroCusto($fws_['codCentroCusto']);
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
        "codCentroCusto":"' . $fws_['codCentroCusto'] . '"
        }';
    }

    public function carregarTabelaCentroCustos($fws_, $debug = false) {
        $obj = new CentroCusto();
        $objEv = $obj->getEvento();
        $ii = 0;
        try {
            $obj->carregarListaCentroCusto(false, $debug);
            $objEv->getGestor();
            $lista = $obj->getListaCentroCusto();
            foreach ($lista as $objL) {
                $ii++;
                $ehFilho = ($objL->getCodCentroCustoPai() != null && $objL->getCodCentroCustoPai() != '');
                $classeHierarquia = $ehFilho ? 'centro-filho' : 'centro-pai';
                $codigo = $objL->getCodigoFinanceiro();
                $nivel = $codigo ? substr_count($codigo, '.') : 0;
                ?>
                <tr id="<?php echo  $objL->getCodCentroCusto() ?>" data-cod-centro-custo-pai="<?php echo $objL->getCodCentroCustoPai() ?>" class="<?php echo $classeHierarquia ?>">
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

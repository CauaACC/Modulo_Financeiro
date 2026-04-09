<?php

class GerenciarLancamentosContabeisAC {

    function __construct() {
        
    }

    public function carregarLancamentosContabeis($fws_, $debug = false) {
        $obj = new LancamentosContabeis();
        $obj->setcodLancamentosContabeis($fws_['codLancamentosContabeis']);
        $obj->getLancamentosContabeisById($debug);
        return $obj;
    }

    public function editarLancamentosContabeis($fws_, $debug = false) {
        $obj = $this->carregarLancamentosContabeis($fws_,$debug);

        echo '{
                  "statusMessage":"' . $ok . '",
                  "message":      "' . $obj->getMessage() . '",
                  "codPlanoContas": "' . $obj->getCodPlanoContas() . '",
                  "flgLancamento": "' . $obj->getFlgLancamento() . '",
                  "codLancamentosContabeis": "' . $obj->getCodLancamentosContabeis() . '",
                  "historico":"' . $obj->getHistorico() . '",
                  "origemLancamento":"' . $obj->getOrigemLancamento() . '",
                  "valor":"' . $obj->getValor() . '",
                  "dataCompetencia":"' . $obj->getDataCompetencia() . '",
                  "dataFinanceira":"' . $obj->getDataFinanceira() . '",
                  "tipoLancamento":"' . $obj->getTipoLancamento() . '",
                  "conciliado":"' . $obj->getConciliado() . '"
                 }';
        exit();
    }

    public function salvarLancamentosContabeis($fws_, $debug = false) {
        try {
            // if ($fws_['novocodLancamentosContabeis'] == 0) {
            //     $fws_['codLancamentosContabeis'] = NULL;
            // }
            $isForm = true;
            $obj = $this->carregarLancamentosContabeis($fws_);
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
        "codLancamentosContabeis":"' . $obj->getCodLancamentosContabeis() . '"
        }';
    }

    public function excluirLancamentosContabeis($fws_, $debug = false) {
        try {
            $obj = new LancamentosContabeis();
            $obj->setcodLancamentosContabeis($fws_['codLancamentosContabeis']);
            $obj->excluir();
        } catch (OKException $e) {
            $ok = true;
        } catch (Exception $e) {
            $ok = false;
        }

        echo '{
        "statusMessage":"' . $ok . '",
        "message":      "' . tratarJson( $e->getMessage()) . '",
        "codLancamentosContabeis":"' . $fws_['codLancamentosContabeis'] . '"
        }';
    }

    public function carregarTabelaLancamentosContabeis($fws_, $debug = false) {
        $obj = new LancamentosContabeis();
        $objEv = $obj->getEvento();
        try {
            $obj->carregarListaLancamentosContabeis(false, $debug);
            $objEv->getGestor();
            $lista = $obj->getListaLancamentosContabeis();
            foreach ($lista as $objL) {
                $ii++;
                ?>  
                <tr id="<?php echo  $objL->getCodLancamentosContabeis() ?>">
                    <td>
                        <?php //if ($objEv->controlarAcessoProposta('boolean')) { ?>
                        <img class="editar" 
                             src="<?php echo  WB_PATH ?>/resources/image/alterar.gif" alt="Editar LancamentosContabeis" border="0">
                        <img class="excluir" 
                             src="<?php echo  WB_PATH ?>/resources/image/fig-excluir.gif" alt="Excluir LancamentosContabeis" border="0">                        
                        <?php //} ?>
                    </td>
                    <td>
                        <?php echo $objL->getDataCompetencia(); ?>
                    </td>
                    <td>
                        <?php echo $objL->getDataFinanceira(); ?>
                    </td>
                    <td>
                        <?php echo $objL->getTipoLancamento(); ?>
                    </td>
                    <td>
                        <?php echo $objL->getValor(); ?>
                    </td>
                    <td>
                        <?php echo $objL->getOrigemLancamento(); ?>
                    </td>
                    <td>
                        <?php echo $objL->getHistorico(); ?>
                    </td>
                    <td>
                        <?php echo $objL->getDesConciliado(); ?>
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

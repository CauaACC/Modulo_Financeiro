<?php

class GerenciarPlanoContasAC {

    function __construct() {
        
    }

    public function carregarPlanoContas($fws_, $debug = false) {
        $obj = new PlanoContas();
        $obj->setCodPlanoContas($fws_['codPlanoContas']);
        $obj->getPlanoContasById($debug);
        return $obj;
    }

    public function editarPlanoContas($fws_, $debug = false) {
        $obj = $this->carregarPlanoContas($fws_,$debug);

        echo '{
                  "statusMessage":"' . $ok . '",
                  "message":      "' . $obj->getMessage() . '",
                  "codPlanoContas": "' . $obj->getCodPlanoContas() . '",
                  "codPlanoContasPai": "' . $obj->getCodPlanoContasPai() . '",
                  "codigoContabil": "' . $obj->getCodigoContabil() . '",
                  "naturezaSaldo": "' . $obj->getNaturezaSaldo() . '",
                  "tipoConta": "' . $obj->getTipoConta() . '",
                  "nomePlanoContas":"' . $obj->getNomePlanoContas() . '",
                  "descricao":"' . $obj->getDescricao() . '",
                  "aceitaLancamento":"' . $obj->getAceitaLancamento() . '",
                  "status":"' . $obj->getStatus() . '"
                 }';
        exit();
    }

    public function salvarPlanoContas($fws_, $debug = false) {
        try {
            // if ($fws_['novoCodPlanoContas'] == 0) {
            //     $fws_['codPlanoContas'] = NULL;
            // }
            $isForm = true;
            $obj = $this->carregarPlanoContas($fws_);
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
        "codPlanoContas":"' . $obj->getCodPlanoContas() . '"
        }';
    }

    public function excluirPlanoContas($fws_, $debug = false) {
        try {
            $obj = new PlanoContas();
            $obj->setCodPlanoContas($fws_['codPlanoContas']);
            $obj->excluir();
        } catch (OKException $e) {
            $ok = true;
        } catch (Exception $e) {
            $ok = false;
        }

        echo '{
        "statusMessage":"' . $ok . '",
        "message":      "' . tratarJson( $e->getMessage()) . '",
        "codPlanoContas":"' . $fws_['codPlanoContas'] . '"
        }';
    }

    public function carregarTabelaPlanoContass($fws_, $debug = false) {
        $obj = new PlanoContas();
        $objEv = $obj->getEvento();
        try {
            $obj->carregarListaPlanoContas(false, $debug);
            $objEv->getGestor();
            $lista = $obj->getListaPlanoContas();
            foreach ($lista as $objL) {
                $ii++;
                ?>  
                <tr id="<?php echo  $objL->getCodPlanoContas() ?>">
                    <td>
                        <?php //if ($objEv->controlarAcessoProposta('boolean')) { ?>
                        <img class="editar" src="<?php echo  WB_PATH ?>/resources/image/alterar.gif" alt="Editar PlanoContas" border="0">
                        <?php if ($objL->getTotalFilho() <= 0) {?>
                        <img class="excluir" src="<?php echo  WB_PATH ?>/resources/image/fig-excluir.gif" alt="Excluir PlanoContas" border="0">                        
                        <?php } ?>
                        <?php //} ?>
                    </td>
                    <td>
                        <?php echo $objL->getCodigoContabil(); ?>
                    </td>
                    <td>
                        <?php echo $objL->getNomePlanoContas(); ?>
                    </td>
                    <td>
                        <?php echo $objL->getDescricao(); ?>
                    </td>
                    <td>
                        <?php echo $objL->getTipoConta(); ?>
                    </td>
                    <td>
                        <?php echo $objL->getNaturezaSaldo(); ?>
                    </td>
                    <td>
                        <?php echo $objL->getDesAceitaLancamento(); ?>
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

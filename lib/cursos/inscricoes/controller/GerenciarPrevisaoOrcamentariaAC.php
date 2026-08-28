<?php

class GerenciarPrevisaoOrcamentariaAC {

    function __construct() {
        FWA_exigirPermissao([enumSistema::SIS_GERENCIAR_ADM_GERAL, enumSistema::SIS_CONTROLE_FINANCEIRO]);
        
    }

    public function carregarPrevisaoOrcamentaria($fws_, $debug = false) {
        $obj = new PrevisaoOrcamentaria();
        $obj->setCodPrevisaoOrcamentaria($fws_['codPrevisaoOrcamentaria']);
        $obj->getPrevisaoOrcamentariaById($debug);
        return $obj;
    }

    public function editarPrevisaoOrcamentaria($fws_, $debug = false) {
        $obj = $this->carregarPrevisaoOrcamentaria($fws_,$debug);

        echo '{
                  "statusMessage":"' . $ok . '",
                  "message":      "' . $obj->getMessage() . '",
                  "codPrevisaoOrcamentaria":"' . $obj->getCodPrevisaoOrcamentaria() . '",
                  "desPrevisaoOrcamentaria":"' . tratarJson($obj->getDesPrevisaoOrcamentaria()) . '",
                  "tipo":         "' . $obj->getTipo() . '",
                  "nomTipoReceitaDespesa":"'.$obj->getNomTipoReceitaDespesa() . '",
                  "desTipoReceitaDespesa":"'.$obj->getDesTipoReceitaDespesa() . '",
                  "codTipoReceitaDespesa":"'.$obj->getCodTipoReceitaDespesa() . '",
                  "quantidade":   "' . $obj->getQuantidade() . '",
                  "vlrUnitario":"' . formatMoneyView($obj->getVlrUnitario()) . '",
                  "vlrTotal":"' . formatMoneyView($obj->getVlrTotal()) . '"
                 }';

        exit();
    }

    public function salvarPrevisaoOrcamentaria($fws_, $debug = false) {
        try {
            if ($fws_['novoCodPrevisaoOrcamentaria'] == 0) {
                $fws_['codPrevisaoOrcamentaria'] = NULL;
            }
            $isForm = true;

            $obj = $this->carregarPrevisaoOrcamentaria($fws_);
            
            $objTRD = new TipoReceitaDespesa();
            $objTRD->setCodTipoReceitaDespesa($obj->getCodTipoReceitaDespesa());
            //echo "--<".$objTRD->getCodTipoReceitaDespesa().">--";
            $objTRD->carregarObjTipoReceitaDespesaById();
            //echo "**". $objTRD->getCodTipoReceitaDespesa()."**";
            $obj->setNomTipoReceitaDespesa($objTRD->getNomTipoReceitaDespesa());
            $obj->setDesTipoReceitaDespesa($objTRD->getDesTipoReceitaDespesa());

            $obj->setAll($fws_, $isForm);

            $obj->salvar($debug);
        } catch (OkException $e) {
            $ok = true;
        } catch (Exception $e) {
            $ok = false;
        }

        echo '{
        "statusMessage":"' . $ok . '",
        "message":      "' . $e->getMessage() . '",
        "codPrevisaoOrcamentaria":"' . $obj->getCodPrevisaoOrcamentaria() . '"
        }';
    }

    public function excluirPrevisaoOrcamentaria($fws_, $debug = false) {
        try {
            $obj = new PrevisaoOrcamentaria();
            $obj->setCodPrevisaoOrcamentaria($fws_['codPrevisaoOrcamentaria']);

            $obj->excluir();
        } catch (OKException $e) {
            $ok = true;
        } catch (Exception $e) {
            $ok = false;
        }

        echo '{
        "statusMessage":"' . $ok . '",
        "message":      "' . $e->getMessage() . '",
        "codPrevisaoOrcamentaria":"' . $fws_['codPrevisaoOrcamentaria'] . '"
        }';
    }

    public function carregarTabelaPrevisaoOrcamentarias($fws_, $debug = false) {
        $obj = new PrevisaoOrcamentaria();

        //$objEv = $obj->getEvento();
        try {
            $lista = $obj->getListaPrevisaoOrcamentariasByIdEvento($debug);
//            if(!$lista){
            foreach ($lista as $objL) {
                ?>  
                <tr id="<?php echo  $objL->getCodPrevisaoOrcamentaria() ?>">
                    <td>
                        <?php //if ($objEv->controlarAcessoProposta('boolean')) { ?>
                        <img class="editar" 
                             src="<?php echo  WB_PATH ?>/resources/image/alterar.gif" alt="Editar PrevisaoOrcamentaria" border="0">
                        <img class="excluir" 
                             src="<?php echo  WB_PATH ?>/resources/image/fig-excluir.gif" alt="Excluir PrevisaoOrcamentaria" border="0">                        
                        <?php //} ?>
                    </td>
                   
                    <td><?php echo  $objL->getTipo() ?></td>
                    <td><?php echo  $objL->getNomTipoReceitaDespesa()?></td>
                    <td><?php echo  $objL->getDesPrevisaoOrcamentaria() ?></td>
                    <td><?php echo  $objL->getQuantidade() ?></td>
                    <td style="text-align: right;">R$ <?php echo  formatMoneyView($objL->getVlrUnitario()) ?></td>

                    <?php
                    if ($objL->getTipo() == 'Despesa') {
                        $totalVlrTotalD = $totalVlrTotalD + $objL->getVlrTotal();
                        ?>
                        <td style="text-align: right;">R$ (<?php echo  formatMoneyView($objL->getVlrTotal()) ?>)</td>
                        <?php
                    } else {
                        $totalVlrTotalR = $totalVlrTotalR + $objL->getVlrTotal();
                        ?>
                        <td style="text-align: right;">R$ <?php echo  formatMoneyView($objL->getVlrTotal()) ?></td>
                    <?php } ?>
                </tr>
                <?php
            }
            ?>
            <tr >
                <td> </td>
                <td></td>
                <td></td>
                <td style="font-weight: bold;text-align: right;" colspan="3">Total:
                <?php 
                $totalVlrTotal = $totalVlrTotalR-$totalVlrTotalD;
                ?>
                </td>    
                <?php if ($totalVlrTotal > 0) { ?>
                    <td style="font-weight: bold;text-align: right;"> R$ <?php echo  formatMoneyView($totalVlrTotal.'00') ?></td>
                <?php } else { ?>
                    <td style="font-weight: bold;text-align: right;"> R$ (<?php echo  formatMoneyView($totalVlrTotal.'00') ?>)</td>
                <?php } ?>
            </tr>
            <?php
        } catch (Exception $e) {
            echo "<tr><td></td><td colspan=6>";
            if ($e->getMessage()) {
                echo $e->getMessage();
            } else {
                echo "Lista vazia.";
            }
            echo "</td></tr>";
        }
    }

    public function getListaTipoReceitaDespesa() {
        $obj = new TipoReceitaDespesa();
        $obj->carregarListaTipoReceitaDespesas();
        $lista = $obj->getListaTipoReceitaDespesas();
        return $lista;
    }

    public function getListaTipoReceitaOuDespesa($fws_, $debug = false) {
        
        $objI = new TipoReceitaDespesa();
        $objI->setTipo($fws_["tipo"]);
        ?>
            <option></option> 
            <?php
        try {
            $objI->carregarListaReceitaDespesaByTipo($debug);
            $lista = $objI->getListaTipoReceitaDespesa();
            foreach ($lista as $obj) {
                ?>
                <option value="<?php echo  $obj->getCodTipoReceitaDespesa() ?>"                        
                       ><?php echo  $obj->getNomeTipoReceitaDespesa() ?></option>
                <?php
            }
        } catch (Exception $exc) {?>
                <option><?php echo $exc->getMessage()?> </option> 
        <?php }
    }

}
?>
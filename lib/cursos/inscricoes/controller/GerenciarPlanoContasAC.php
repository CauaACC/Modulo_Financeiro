<?php

class GerenciarPlanoContasAC {

    function __construct() {
        FWA_exigirPermissao([enumSistema::SIS_GERENCIAR_ADM_GERAL, enumSistema::SIS_CONTROLE_FINANCEIRO]);
        
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
                $ehSub = ($objL->getCodPlanoContasPai() != null && $objL->getCodPlanoContasPai() != '');
                $classeHierarquia = $ehSub ? 'sub-conta' : 'conta-principal';
                ?>
                <tr id="<?php echo  $objL->getCodPlanoContas() ?>" class="<?php echo $classeHierarquia ?>">
                    <td>
                        <span class="editar btn-acao" title="Editar"><i class="fa fa-pencil"></i></span>
                        <?php if ($objL->getTotalFilho() <= 0) {?>
                        <span class="excluir btn-acao btn-acao-excluir" title="Excluir"><i class="fa fa-trash"></i></span>
                        <?php } ?>
                    </td>
                    <td class="col-codigo">
                        <?php if ($ehSub) { ?><span class="tree-indent"></span><?php } ?>
                        <?php echo $objL->getCodigoContabil(); ?>
                    </td>
                    <td class="col-nome">
                        <?php if ($ehSub) { ?><span class="tree-connector">└</span><?php } ?>
                        <?php echo $objL->getNomePlanoContas(); ?>
                    </td>
                    <td>
                        <?php echo $objL->getDescricao(); ?>
                    </td>
                    <td>
                        <?php
                        $tipo = $objL->getTipoConta();
                        $dotClass = 'dot-blue';
                        if ($tipo == 'RECEITA') $dotClass = 'dot-green';
                        if ($tipo == 'DESPESA') $dotClass = 'dot-red';
                        if ($tipo == 'PASSIVO') $dotClass = 'dot-orange';
                        if ($tipo == 'PATRIMONIO_LIQUIDO') $dotClass = 'dot-purple';
                        ?>
                        <span class="badge-tipo"><span class="dot <?php echo $dotClass; ?>"></span><?php echo $tipo; ?></span>
                    </td>
                    <td>
                        <?php echo $objL->getNaturezaSaldo(); ?>
                    </td>
                    <td>
                        <?php
                        $aceitaVal = $objL->getAceitaLancamento();
                        $aceitaTxt = ($aceitaVal == 1) ? 'SIM' : 'NÃO';
                        $dotAceita = ($aceitaVal == 1) ? 'dot-green' : 'dot-red';
                        ?>
                        <span class="badge-tipo"><span class="dot <?php echo $dotAceita; ?>"></span><?php echo $aceitaTxt; ?></span>
                    </td>
                </tr>
                <?php
            }
            ?>
                </tbody><tfoot><tr><td colspan="7" style="text-align:right; color:#999; font-size:12px; padding:8px 10px; border-top:1px solid #eee;">Total: <?php echo $ii ?> registro<?php echo $ii != 1 ? 's' : '' ?></td></tr></tfoot><tbody class="inputs" style="display:none;">
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
}

<?php

class GerenciarInscritoDependentesAC {

    function __construct() {
        FWA_exigirPermissao(enumSistema::SIS_GERENCIAR_ADM_GERAL);
        
    }

    public function carregarInscritoDependentes($fws_, $debug = false) {
        $obj = new InscritoDependentes();
        $obj->setCodDependente($fws_['codDependente']);
        $obj->getInscritoDependentesById($debug);
        return $obj;
    }

    public function editarInscritoDependentes($fws_, $debug = false) {
        $obj = $this->carregarInscritoDependentes($fws_,$debug);

        echo '{
                  "statusMessage":"' . $ok . '",
                  "message":      "' . $obj->getMessage() . '",
                  "codDependente": "' . $obj->getCodDependente() . '",
                  "nomeDependente":"' . $obj->getNomeDependente() . '",
                  "cpfDependente": "' . $obj->getCpfDependente() . '",
                  "dataNascimento": "' . $obj->getDataNascimento() . '",
                  "grauParentesco": "' . $obj->getGrauParentesco() . '",
                  "sexo":"' . $obj->getSexo() . '",
                  "possuiDeficiencia":"' . $obj->getPossuiDeficiencia() . '",
                  "descricaoDeficiencia":"' . $obj->getDescricaoDeficiencia() . '",
                  "dependenteIrpf":"' . $obj->getDependenteIrpf() . '",
                  "dependentePlanoSaude":"' . $obj->getDependentePlanoSaude() . '",
                  "dependenteSalarioFamilia":"' . $obj->getDependenteSalarioFamilia() . '",
                  "observacoes":"' . $obj->getObservacoes() . '",
                  "status":"' . $obj->getStatus() . '"
                 }';
        exit();
    }

    public function salvarInscritoDependentes($fws_, $debug = false) {
        try {
            // if ($fws_['novoCodInscritoDependentes'] == 0) {
            //     $fws_['codDependente'] = NULL;
            // }
            $isForm = true;
            $obj = $this->carregarInscritoDependentes($fws_);
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
        "codDependente":"' . $obj->getCodDependente() . '"
        }';
    }

    public function excluirInscritoDependentes($fws_, $debug = false) {
        try {
            $obj = new InscritoDependentes();
            $obj->setCodDependente($fws_['codDependente']);
            $obj->excluir();
        } catch (OKException $e) {
            $ok = true;
        } catch (Exception $e) {
            $ok = false;
        }

        echo '{
        "statusMessage":"' . $ok . '",
        "message":      "' . tratarJson( $e->getMessage()) . '",
        "codDependente":"' . $fws_['codDependente'] . '"
        }';
    }

    public function carregarTabelaInscritoDependentes($fws_, $debug = false) {
        $obj = new InscritoDependentes();
        $objEv = $obj->getEvento();
        try {
            $obj->carregarListaInscritoDependentes(false, $debug);
            $objEv->getGestor();
            $lista = $obj->getListaInscritoDependentes();
            foreach ($lista as $objL) {
                $ii++;
                ?>  
                <tr id="<?php echo  $objL->getCodDependente() ?>">
                    <td style="white-space:nowrap;">
                        <button class="btn btn-xs btn-info editar" type="button" title="Editar"><i class="fa fa-pencil"></i></button>
                        <button class="btn btn-xs btn-danger excluir" type="button" title="Excluir"><i class="fa fa-trash"></i></button>
                    </td>
                    <td>
                        <?php echo $objL->getNomeDependente(); ?>
                    </td>
                    <td>
                        <?php echo $objL->getCpfDependente(); ?>
                    </td>
                    <td>
                        <?php echo $objL->getDataNascimento(); ?>
                    </td>
                    <td>
                        <?php echo $objL->getGrauParentesco(); ?>
                    </td>
                    <td>
                        <?php echo $objL->getSexo(); ?>
                    </td>
                    <td>
                        <?php echo $objL->getDesPossuiDeficiencia(); ?>
                    </td>
                    <td>
                        <?php echo $objL->getDesDependenteIrpf(); ?>
                    </td>
                    <td>
                        <?php echo $objL->getDesDependentePlanoSaude(); ?>
                    </td>
                    <td>
                        <?php echo $objL->getDesDependenteSalarioFamilia(); ?>
                    </td>
                </tr>
                <?php
            }
            ?>
                <tr><td colspan="10" class="text-right"><b>Total de dependentes cadastrados: <?php echo  $ii ?></b></td></tr>
            <?php
        } catch (Exception $e) {
            echo "<tr><td colspan='10' class='text-center'>";
            if ($e->getMessage()) {
                echo $e->getMessage();
            } else {
                echo "Nenhum dependente cadastrado.";
            }
            echo "</td></tr>";
        }
    }    
}

<?php
/**
 * Carrega options de selects para o módulo financeiro.
 * Extraído do CarregarOptionsAC original (apenas métodos financeiros).
 */
class CarregarOptionsAC {

    public static function planoContasDebito($fws_, $debug = false) {
        try {
            $objPlanoContas = new PlanoContas();
            $objPlanoContas->carregarListaPlanoContasDebito($debug);
            $lista = $objPlanoContas->getListaPlanoContas();
            if ($lista) {
                foreach ($lista as $objL) {
                    ?>
                    <option
                        <?php echo verifItemSelected($fws_['cod_plano_contas'], $objL->getCodPlanoContas()) ?>
                        value='<?php echo $objL->getCodPlanoContas() ?>'><?php echo $objL->getCodigoContabil() . ' ' . $objL->getDescricao() ?></option>
                    <?php
                }
            }
        } catch (Exception $exc) {
            echo "<option value=''>" . $exc->getMessage() . "</option>";
        }
    }

    public static function planoContasCredito($fws_, $debug = false) {
        try {
            $objPlanoContas = new PlanoContas();
            $objPlanoContas->carregarListaPlanoContasCredito($debug);
            $lista = $objPlanoContas->getListaPlanoContas();
            if ($lista) {
                foreach ($lista as $objL) {
                    ?>
                    <option
                        <?php echo verifItemSelected($fws_['cod_plano_contas'], $objL->getCodPlanoContas()) ?>
                        value='<?php echo $objL->getCodPlanoContas() ?>'><?php echo $objL->getCodigoContabil() . ' ' . $objL->getDescricao() ?></option>
                    <?php
                }
            }
        } catch (Exception $exc) {
            echo "<option value=''>" . $exc->getMessage() . "</option>";
        }
    }

    public static function planoContasPai($fws_, $debug = false) {
        try {
            $objPlanoContas = new PlanoContas();
            $objPlanoContas->carregarListaPlanoContasPai($debug);
            $lista = $objPlanoContas->getListaPlanoContas();
            if ($lista) {
                foreach ($lista as $objL) {
                    ?>
                    <option
                        <?php echo verifItemSelected(@$fws_['cod_plano_contas_pai'], $objL->getCodPlanoContas()) ?>
                        value='<?php echo $objL->getCodPlanoContas() ?>'><?php echo $objL->getCodigoContabil() . ' - ' . $objL->getNomePlanoContas() ?></option>
                    <?php
                }
            }
        } catch (Exception $exc) {
            echo "<option value=''>" . $exc->getMessage() . "</option>";
        }
    }

    public static function centroCustoPai($fws_, $debug = false) {
        try {
            $objCC = new CentroCusto();
            $objCC->carregarListaCentroCustoPai($debug);
            $lista = $objCC->getListaCentroCusto();
            if ($lista) {
                foreach ($lista as $objL) {
                    ?>
                    <option
                        <?php echo verifItemSelected(@$fws_['cod_centro_custo_pai'], $objL->getCodCentroCusto()) ?>
                        value='<?php echo $objL->getCodCentroCusto() ?>'><?php echo $objL->getCodigoContabilCC() . ' - ' . $objL->getNomeCentroCusto() ?></option>
                    <?php
                }
            }
        } catch (Exception $exc) {
            echo "<option value=''>" . $exc->getMessage() . "</option>";
        }
    }

    public static function rateiosPai($fws_, $debug = false) {
        try {
            $objRateios = new Rateios();
            $objRateios->carregarListaRateiosPai($debug);
            $lista = $objRateios->getListaRateios();
            if ($lista) {
                foreach ($lista as $objL) {
                    ?>
                    <option
                        <?php echo verifItemSelected(@$fws_['cod_rateio_pai'], $objL->getCodRateio()) ?>
                        value='<?php echo $objL->getCodRateio() ?>'><?php echo $objL->getCodigoContabilRateio() . ' - ' . $objL->getNomeRateio() ?></option>
                    <?php
                }
            }
        } catch (Exception $exc) {
            echo "<option value=''>" . $exc->getMessage() . "</option>";
        }
    }
}

<?php

class GerenciarPrestacaoServicoAC {

    function __construct() {
        FWA_exigirPermissao([enumSistema::SIS_GERENCIAR_ADM_GERAL, enumSistema::SIS_CONTROLE_FINANCEIRO]);

    }

    public function carregarPrestacaoServico($fws_, $debug = false) {
        $obj = new PrestacaoServico();
        $obj->setCodPrestacaoServico($fws_['codPrestacaoServico']);
        $obj->getPrestacaoServicoById($debug);
        return $obj;
    }

    public function editarPrestacaoServico($fws_, $debug = false) {
        $obj = $this->carregarPrestacaoServico($fws_,$debug);

        echo '{
                  "statusMessage":"' . $ok . '",
                  "message":      "' . $obj->getMessage() . '",
                  "nomeIdentificador": "' . $obj->getNomeIdentificador() . '",
                  "codPrestacaoServico":"' . $obj->getCodPrestacaoServico() . '",
                  "valorPrestacaoServico":"' . $obj->getValorPrestacaoServico() . '",
                  "dataVencimento": "' . $obj->getDataVencimento() . '",
                  "nomePrestador": "' . $obj->getNomePrestador() . '",
                  "situacaoPago": "' . $obj->getSituacaoPago() . '",
                 }';
        exit();

    }

    public function salvarPrestacaoServico($fws_, $debug = false) {
        try {

            if ($fws_['novoCodPrestacaoServico'] == 0) {
                $fws_['codPrestacaoServico'] = NULL;
            }
            $isForm = true;
            $obj = $this->carregarPrestacaoServico($fws_);
            $obj->setAll($fws_, $isForm);

            $valor = formatMoneyInsert($obj->getValorPrestacaoServico());
            $obj->setValorPrestacaoServico($valor);
            $data = formatDateInsert($obj->getDataVencimento());
            $obj->setDataVencimento($data);

            $obj->salvar($debug);
        } catch (OkException $e) {
            $ok = true;
        } catch (Exception $e) {
            $ok = false;
        }

        echo '{
        "statusMessage":"' . $ok . '",
        "message":      "' . $e->getMessage() . '",
        "codPrestacaoServico":"' . $obj->getCodPrestacaoServico() . '"
        }';
    }

    public function excluirPrestacaoServico($fws_, $debug = false) {
        try {
            $obj = new PrestacaoServico();
            $obj->setCodPrestacaoServico($fws_['codPrestacaoServico']);

            $obj->excluir();
        } catch (OKException $e) {
            $ok = true;
        } catch (Exception $e) {
            $ok = false;
        }

        echo '{
        "statusMessage":"' . $ok . '",
        "message":      "' . $e->getMessage() . '",
        "codPrestacaoServico":"' . $fws_['codPrestacaoServico'] . '"
        }';
    }

    public function carregarTabelaPrestacaoServicos($fws_, $debug = false) {
        $obj = new PrestacaoServico();
        try {
            $lista = $obj->getListaPrestacaoServicosByIdEvento($debug);
            foreach ($lista as $objL) {
                $ii++;
                ?>
                <tr id="<?php echo  $objL->getCodPrestacaoServico() ?>">
                    <td>
                        <?php //if ($objEv->controlarAcessoProposta('boolean')) { ?>
                        <img class="editar"
                             src="<?php echo  WB_PATH ?>/resources/image/alterar.gif" alt="Editar PrestacaoServico" border="0">
                        <img class="excluir"
                             src="<?php echo  WB_PATH ?>/resources/image/fig-excluir.gif" alt="Excluir PrestacaoServico" border="0">
                        <?php //} ?>
                    </td>
                    <td><?php echo textoMaiusculoMinusculo($objL->getNomeIdentificador()) ?></td>
                    <td><?php echo formatMoneyInView($objL->getValorPrestacaoServico()); ?></td>
                    <td><?php echo formatDateView($objL->getDataVencimento(), false) ?></td>
                    <td><?php echo  $objL->getNomePrestador() ?></td>
                    <td><?php echo  $objL->getSituacaoPago() ?></td>
                    <td><?php
                        echo $objL->getJaInscrito();
                        if($objL->getJaInscrito()){
                            $totalJaInscrito++;
                            ?>
                            <a title='Logar Como' target="_blank" href='index.php?content=view/inscricao/inscricao_congresso_ok.php&acao=logarComo&cpf=<?php echo $objL->getCpf()?><?php echo $objL->getCodInscricao()?>&cod_evento=<?php echo COD_EVENTO?>'>
                                <img src='<?php echo WB_URL_IMG?>/icone_preview.gif' border='0'>
                            </a>
                            <?php
                        }
                        ?></td>
                    <td><?php echo  $objL->getDescPagamento() ?></td>
                </tr>
                <?php
            }
            ?>
            <br>Total de prestações serviços cadastrados: <?php echo  $ii ?>.
<!--            <br>Total de descontos já inscritos: --><?php //echo  $totalJaInscrito ?><!--.-->
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
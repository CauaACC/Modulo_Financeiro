<?php

class GerenciarDescontoAC {

    function __construct() {
        FWA_exigirPermissao(enumSistema::SIS_GERENCIAR_ADM_GERAL);
        
    }

    public function carregarDesconto($fws_, $debug = false) {
        $obj = new Desconto();
        $obj->setCodDesconto($fws_['codDesconto']);
        $obj->getDescontoById($debug);
        return $obj;
    }

    public function editarDesconto($fws_, $debug = false) {
        $obj = $this->carregarDesconto($fws_,$debug);

        echo '{
                  "statusMessage":"' . $ok . '",
                  "message":      "' . $obj->getMessage() . '",
                  "codDesconto": "' . $obj->getCodDesconto() . '",
                  "nomeDesconto":"' . $obj->getNomeDesconto() . '",
                  "cpf":          "' . $obj->getCpf() . '",
                  "email":        "' . $obj->getEmail() . '",
                  "valorFixo":        "' . $obj->getValorFixo() . '",
                  "valorDoado":       "' . $obj->getValorDoado() . '",
                  "valorDesconto":    "' . $obj->getValorDesconto() . '",
                  "porcentagemDesconto":"' . $obj->getPorcentagemDesconto() . '"
                 }';
        exit();
    }

    public function salvarDesconto($fws_, $debug = false) {
        try {
            if ($fws_['novoCodDesconto'] == 0) {
                $fws_['codDesconto'] = NULL;
            }
            $isForm = true;
            $obj = $this->carregarDesconto($fws_);
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
        "codDesconto":"' . $obj->getCodDesconto() . '"
        }';
    }

    public function excluirDesconto($fws_, $debug = false) {
        try {
            $obj = new Desconto();
            $obj->setCodDesconto($fws_['codDesconto']);

            $obj->excluir();
        } catch (OKException $e) {
            $ok = true;
        } catch (Exception $e) {
            $ok = false;
        }

        echo '{
        "statusMessage":"' . $ok . '",
        "message":      "' . $e->getMessage() . '",
        "codDesconto":"' . $fws_['codDesconto'] . '"
        }';
    }

    public function carregarTabelaDescontos($fws_, $debug = false) {
        $obj = new Desconto();
        try {
            $lista = $obj->getListaDescontosByIdEvento($debug);
            $ii = 0;
            $totalJaInscrito = 0;
            foreach ($lista as $objL) {
                $ii++;
                $nome = '';
                if($objL->getNomeInscrito()){
                    $nome = $objL->getNomeInscrito();
                }else if($objL->getNomeParticipante()){
                    $nome = $objL->getNomeParticipante();
                }else{
                    $nome = textoMaiusculoMinusculo($objL->getNomeDesconto());
                }
                ?>
                <tr id="<?php echo $objL->getCodDesconto() ?>">
                    <td>
                        <span class="btn-acao editar" title="Editar"><i class="fa fa-pencil"></i></span>
                        <span class="btn-acao btn-acao-excluir excluir" title="Excluir"><i class="fa fa-trash"></i></span>
                    </td>
                    <td class="col-nome"><?php echo htmlspecialchars($nome) ?><?php if($objL->getEmail()) { ?><br><small style="font-weight:normal; color:#888;"><?php echo htmlspecialchars($objL->getEmail()) ?></small><?php } ?></td>
                    <td><?php echo $objL->getCpf() ?></td>
                    <td><?php echo $objL->getValorDoado() ?></td>
                    <td><?php echo $objL->getValorFixo() ?></td>
                    <td><?php echo $objL->getValorDesconto() ?></td>
                    <td><?php echo $objL->getPorcentagemDesconto() ?></td>
                    <td><?php
                        if($objL->getJaInscrito()){
                            $totalJaInscrito++;
                            ?>
                            Sim
                            <a title="Logar Como" target="_blank" href="index.php?content=view/inscricao/inscricao_congresso_ok.php&acao=logarComo&cpf=<?php echo $objL->getCpf() ?>&cod_inscrito=<?php echo $objL->getCodInscricao() ?>&cod_evento=<?php echo COD_EVENTO ?>">
                            <i class="fa fa-external-link" style="margin-left:4px; color:#337ab7;"></i></a>
                            <?php
                        } else {
                            echo 'N&atilde;o';
                        }
                    ?></td>
                    <td><?php echo $objL->getDescPagamento() ?></td>
                </tr>
                <?php
            }
            echo "<tr class='tbl-fin-total'><td colspan='9' style='text-align:right; color:#999; font-size:12px; padding:8px 10px; border-top:1px solid #eee;'>Total: " . $ii . " desconto(s) &mdash; " . $totalJaInscrito . " j&aacute; inscrito(s)</td></tr>";
        } catch (Exception $e) {
            echo "<tr><td colspan='9'>";
            if ($e->getMessage()) {
                echo $e->getMessage();
            } else {
                echo "Nenhum desconto cadastrado.";
            }
            echo "</td></tr>";
        }
    }
}

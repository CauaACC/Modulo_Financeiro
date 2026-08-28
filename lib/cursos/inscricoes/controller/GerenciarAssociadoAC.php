<?php

class GerenciarAssociadoAC {

    function __construct() {
        FWA_exigirPermissao(enumSistema::SIS_GERENCIAR_ADM_GERAL);
        
    }

    public function carregarAssociado($fws_, $debug = false) {
        $obj = new Associado();
        $obj->setCodAssociado($fws_['codAssociado']);
        $obj->getAssociadoById($debug);
        return $obj;
    }

    public function editarAssociado($fws_, $debug = false) {
        $obj = $this->carregarAssociado($fws_,$debug);

        echo '{
                  "statusMessage":"' . $ok . '",
                  "message":      "' . $obj->getMessage() . '",
                  "codAssociado": "' . $obj->getCodAssociado() . '",
                  "nomeAssociado":"' . $obj->getNomeAssociado() . '",
                  "cpf":          "' . $obj->getCpf() . '",
                  "email":        "' . $obj->getEmail() . '"
                 }';
        exit();
    }

    public function salvarAssociado($fws_, $debug = false) {
        try {
            if ($fws_['novoCodAssociado'] == 0) {
                $fws_['codAssociado'] = NULL;
            }
            $isForm = true;
            $obj = $this->carregarAssociado($fws_);
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
        "codAssociado":"' . $obj->getCodAssociado() . '"
        }';
    }

    public function excluirAssociado($fws_, $debug = false) {
        try {
            $obj = new Associado();
            $obj->setCodAssociado($fws_['codAssociado']);

            $obj->excluir();
        } catch (OKException $e) {
            $ok = true;
        } catch (Exception $e) {
            $ok = false;
        }

        echo '{
        "statusMessage":"' . $ok . '",
        "message":      "' . $e->getMessage() . '",
        "codAssociado":"' . $fws_['codAssociado'] . '"
        }';
    }

    public function carregarTabelaAssociados($fws_, $debug = false) {
        $obj = new Associado();
        $lista = [];
        try {
            $lista = $obj->getListaAssociadosByIdEvento($debug);
        } catch (Exception $e) {}
        $ii = 0;
        $totalJaInscrito = 0;
        if (!empty($lista)) {
            foreach ($lista as $objL) {
                $ii++;
                ?>
                <tr id="<?php echo  $objL->getCodAssociado() ?>">
                    <td>
                        <span class="btn-acao editar" title="Editar"><i class="fa fa-pencil"></i></span>
                        <span class="btn-acao btn-acao-excluir excluir" title="Excluir"><i class="fa fa-trash"></i></span>
                    </td>

                    <td><?php echo  $objL->getNomeAssociado() ?></td>
                    <td><?php echo  $objL->getCpf()?></td>
                    <td><?php echo  $objL->getEmail() ?></td>
                    <td><?php
                        echo $objL->getJaInscrito();
                        if($objL->getJaInscrito()){
                            $totalJaInscrito++;
                            ?>
                            <a title='Logar Como' target="_blank" href='index.php?content=view/inscricao/inscricao_congresso_ok.php&acao=logarComo&cpf=<?php echo $objL->getCpf()?>&cod_inscrito=<?php echo $objL->getCodInscricao()?>&cod_evento=<?php echo COD_EVENTO?>'>
                            <img src='<?php echo WB_URL_IMG?>/icone_preview.gif' border='0'></a>
                            <?php
                        }
                        ?></td>
                </tr>
                <?php
            }
        }
        if ($ii === 0) {
            echo '<tr><td colspan="5" class="text-center" style="padding:30px 10px; color:#999; font-style:italic;">Nenhum associado encontrado.</td></tr>';
        }
    }
}
?>
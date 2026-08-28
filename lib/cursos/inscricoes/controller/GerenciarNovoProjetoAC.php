<?php

class GerenciarNovoProjetoAC {

    function __construct() {
        FWA_exigirPermissao(enumSistema::SIS_GERENCIAR_ADM_GERAL);
        
    }

    public function carregarNovoProjeto($fws_, $debug = false) {
        $obj = new NovoProjeto();
        $obj->setNovoProjetoId($fws_['novo_projeto_id']);
        $obj->getNovoProjetoById($debug);
        return $obj;
    }

    public function editarNovoProjeto($fws_, $debug = false) {
        $obj = $this->carregarNovoProjeto($fws_,$debug);

        echo '{
                  "statusMessage":"' . $ok . '",
                  "message":      "' . $obj->getMessage() . '",
                  "novo_projeto_id": "' . $obj->getNovoProjetoId() . '",
                  "nome":         "' . $obj->getNome() . '",
                  "uf":           "' . $obj->getUf() . '",
                  "novo_projeto_id": "' . $obj->getCodigoNovoProjeto() . '",
                  "url_api":      "' . $obj->getUrlApi() . '",
                  "observacoes":  "' . $obj->getObservacoes() . '"
                 }';
        exit();
    }

    public function salvarNovoProjeto($fws_, $debug = false) {
        try {
            if ($fws_['novoNovoProjetoId'] == 0) {
                $fws_['novo_projeto_id'] = NULL;
            }
            $isForm = true;
            $obj = $this->carregarNovoProjeto($fws_);
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
        "novo_projeto_id":"' . $obj->getNovoProjetoId() . '"
        }';
    }

    public function excluirNovoProjeto($fws_, $debug = false) {
        try {
            $obj = new NovoProjeto();
            $obj->setNovoProjetoId($fws_['novo_projeto_id']);

            $obj->excluir();
        } catch (OKException $e) {
            $ok = true;
        } catch (Exception $e) {
            $ok = false;
        }

        echo '{
        "statusMessage":"' . $ok . '",
        "message":      "' . $e->getMessage() . '",
        "novo_projeto_id":"' . $fws_['novo_projeto_id'] . '"
        }';
    }

    public function carregarTabelaNovoProjetos($fws_, $debug = false) {
        $obj = new NovoProjeto();
        try {
            $lista = $obj->carregarListaNovoProjetos($debug);
            foreach ($lista as $objL) {
                $ii++;
                ?>  
                <tr id="<?php echo  $objL->getNovoProjetoId() ?>">
                    <td>
                        <img class="editar" 
                             src="<?php echo  WB_PATH ?>/resources/image/alterar.gif" alt="Editar NovoProjeto" border="0">
                        <img class="excluir" 
                             src="<?php echo  WB_PATH ?>/resources/image/fig-excluir.gif" alt="Excluir NovoProjeto" border="0">                        
                    </td>
                   
                    <td><?php echo $objL->getNome() ?></td>
                    <td><?php echo  $objL->getUf()?></td>
                    <td><?php echo  $objL->getCodigoNovoProjeto() ?></td>
                    <td><?php echo  $objL->getUrlApi() ?></td>
                    <td><?php echo  $objL->getObservacoes() ?></td>
                </tr>
                <?php
            }
            ?>
                <br>Total de municípios cadastrados: <?php echo  $ii ?>.
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

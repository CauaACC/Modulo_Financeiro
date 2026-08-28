<?php

class GerenciarMunicipioAC {

    function __construct() {
        FWA_exigirPermissao(enumSistema::SIS_GERENCIAR_ADM_GERAL);
        
    }

    public function carregarMunicipio($fws_, $debug = false) {
        $obj = new Municipio();
        $obj->setMunicipioId($fws_['municipio_id']);
        $obj->getMunicipioById($debug);
        return $obj;
    }

    public function editarMunicipio($fws_, $debug = false) {
        $obj = $this->carregarMunicipio($fws_,$debug);

        echo '{
                  "statusMessage":"' . $ok . '",
                  "message":      "' . $obj->getMessage() . '",
                  "municipio_id": "' . $obj->getMunicipioId() . '",
                  "nome":         "' . $obj->getNome() . '",
                  "uf":           "' . $obj->getUf() . '",
                  "codigo_municipio": "' . $obj->getCodigoMunicipio() . '",
                  "url_api":      "' . $obj->getUrlApi() . '",
                  "observacoes":  "' . $obj->getObservacoes() . '"
                 }';
        exit();
    }

    public function salvarMunicipio($fws_, $debug = false) {
        try {
            if ($fws_['novoMunicipioId'] == 0) {
                $fws_['municipio_id'] = NULL;
            }
            $isForm = true;
            $obj = $this->carregarMunicipio($fws_);
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
        "municipio_id":"' . $obj->getMunicipioId() . '"
        }';
    }

    public function excluirMunicipio($fws_, $debug = false) {
        try {
            $obj = new Municipio();
            $obj->setMunicipioId($fws_['municipio_id']);

            $obj->excluir();
        } catch (OKException $e) {
            $ok = true;
        } catch (Exception $e) {
            $ok = false;
        }

        echo '{
        "statusMessage":"' . $ok . '",
        "message":      "' . $e->getMessage() . '",
        "municipio_id":"' . $fws_['municipio_id'] . '"
        }';
    }

    public function carregarTabelaMunicipios($fws_, $debug = false) {
        $obj = new Municipio();
        try {
            $lista = $obj->carregarListaMunicipios($debug);
            foreach ($lista as $objL) {
                $ii++;
                ?>  
                <tr id="<?php echo  $objL->getMunicipioId() ?>">
                    <td>
                        <img class="editar" 
                             src="<?php echo  WB_PATH ?>/resources/image/alterar.gif" alt="Editar Municipio" border="0">
                        <img class="excluir" 
                             src="<?php echo  WB_PATH ?>/resources/image/fig-excluir.gif" alt="Excluir Municipio" border="0">                        
                    </td>
                   
                    <td><?php echo $objL->getNome() ?></td>
                    <td><?php echo  $objL->getUf()?></td>
                    <td><?php echo  $objL->getCodigoMunicipio() ?></td>
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

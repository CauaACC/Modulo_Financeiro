<?php

class GerenciarAreaTematicaAC
{

    function __construct()
    {
        FWA_exigirPermissao([enumSistema::SIS_GERENCIAR_ADM_GERAL, enumSistema::SIS_GERENCIAR_AREAS_TEMATICAS]);
        $this->codEvento = COD_EVENTO;
        $objConf = new GerenciarConfiguracaoEventoAC();
        $objConf->carregarConfiguracao($this);
    }

    public function carregarObjAreaTematica($fws_, $debug = false)
    {
        $obj = new AreaTematica();
        if ($fws_['codAreaTematica']) {
            $obj->setCodAreaTematica($fws_['codAreaTematica']);
            $obj->getAreaTematicaById($debug);
        }
        return $obj;
    }

    public function salvarAreaTematica($fws_, $debug = false)
    {
        $ok = false;
        $e = null;

        try {
            if ($fws_['novoAreaTematica'] == 0) {
                $fws_['codAreaTematica'] = NULL;
            }
            $obj = $this->carregarObjAreaTematica($fws_);
            $obj->setAll($fws_, true);
            $obj->salvar();
            $ok = true;
        } catch (OkException $ex) {
            $ok = true;
        } catch (Exception $ex) {
        }

        echo '{
    "statusMessage":"' . $ok . '",
    "message":      "' . ($ex ? $ex->getMessage() : '') . '",
    "codAreaTematica":"' . ($obj ? $obj->getCodAreaTematica() : '') . '"
    }';
    }

    public function editarAreaTematica($fws_, $debug = false)
    {
        $obj = $this->carregarObjAreaTematica($fws_, $debug);

        echo '{
                  "statusMessage":"' . $ok . '",
                  "message":"' . $obj->getMessage() . '",
                  "codAreaTematica":"' . ($obj ? $obj->getCodAreaTematica() : '') . '",
                  "nome":"' . ($obj ? $obj->getNome() : '') . '"
                 }';
        exit();
    }

    public function excluirAreaTematica($fws_, $debug = false)
    {
        try {
            $obj = $this->carregarObjAreaTematica($fws_);
            $obj->excluir();
        } catch (OKException $e) {
            $ok = true;
        } catch (Exception $e) {
            $ok = false;
        }

        echo '{
            "statusMessage":"' . $ok . '",
            "message":"' . $e->getMessage() . '",
            "codAreaTematica":"' . $fws_['codAreaTematica'] . '",
            }';
        exit();
    }

    public function carregarTabelaAreaTematica($fws_, $debug = false)
    {
        try {
            $obj = new AreaTematica();
            $obj->carregarListaAreaTematicaByIdEvento($debug);
            $lista = $obj->getListaAreasTematicas($debug);
            $ii = 0;

            if (empty($lista)) {
                throw new Exception("Não possui registros cadastradas.");
            }
            foreach ($lista as $objL) {
                $ii++;
                ?>
                <tr id="<?php echo $objL->getCodAreaTematica() ?>">
                    <td>
                        <button class="editar" type="submit" title="Editar AreaTematica">
                            <i class="fa-regular fa-folder-open"></i>
                        </button>
                        <button class="excluir" title="Excluir AreaTematica">
                            <i class="fa-regular fa-trash-can"></i>
                        </button>
                    </td>
                    <td>
                        <?php 
                        echo  $objL->getPrefixo()? $objL->getPrefixo().' - ' : '';
                        echo  $objL->getTitulo();
                        ?>
                    </td>
                    <td style="padding-left: 20px;"><?php echo  $objL->getStatus()?></td>

                </tr>
                <?php
            }
            ?>
            <br>Total de registros: <?php echo $ii ?>.
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
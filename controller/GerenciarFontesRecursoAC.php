<?php

class GerenciarFontesRecursoAC {

    function __construct() {
        
    }

    public function carregarFontesRecurso($fws_, $debug = false) {
        $obj = new FontesRecurso();
        $obj->setCodFontesRecurso($fws_['codFontesRecurso']);
        $obj->getFontesRecursoById($debug);
        return $obj;
    }

    public function editarFontesRecurso($fws_, $debug = false) {
        $obj = $this->carregarFontesRecurso($fws_,$debug);

        echo '{
                  "statusMessage":"' . $ok . '",
                  "message":      "' . $obj->getMessage() . '",
                  "codFontesRecurso": "' . $obj->getCodFontesRecurso() . '",
                  "nomeFontesRecurso":"' . $obj->getNomeFontesRecurso() . '",
                  "tipoFontesRecurso":"' . $obj->getTipoFontesRecurso() . '",
                  "descricao":"' . $obj->getDescricao() . '",
                  "status":"' . $obj->getStatus() . '"
                 }';
        exit();
    }

    public function salvarFontesRecurso($fws_, $debug = false) {
        try {
            // if ($fws_['novoCodFontesRecurso'] == 0) {
            //     $fws_['codFontesRecurso'] = NULL;
            // }
            $isForm = true;
            $obj = $this->carregarFontesRecurso($fws_);
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
        "codFontesRecurso":"' . $obj->getCodFontesRecurso() . '"
        }';
    }

    public function excluirFontesRecurso($fws_, $debug = false) {
        try {
            $obj = new FontesRecurso();
            $obj->setCodFontesRecurso($fws_['codFontesRecurso']);
            $obj->excluir();
        } catch (OKException $e) {
            $ok = true;
        } catch (Exception $e) {
            $ok = false;
        }

        echo '{
        "statusMessage":"' . $ok . '",
        "message":      "' . tratarJson( $e->getMessage()) . '",
        "codFontesRecurso":"' . $fws_['codFontesRecurso'] . '"
        }';
    }

    public function carregarTabelaFontesRecursos($fws_, $debug = false) {
        $obj = new FontesRecurso();
        $objEv = $obj->getEvento();
        try {
            $obj->carregarListaFontesRecurso(false, $debug);
            $objEv->getGestor();
            $lista = $obj->getListaFontesRecurso();
            foreach ($lista as $objL) {
                $ii++;
                ?>  
                <tr id="<?php echo  $objL->getCodFontesRecurso() ?>">
                    <td>
                        <?php //if ($objEv->controlarAcessoProposta('boolean')) { ?>
                        <img class="editar" 
                             src="<?php echo  WB_PATH ?>/resources/image/alterar.gif" alt="Editar FontesRecurso" border="0">
                        <img class="excluir" 
                             src="<?php echo  WB_PATH ?>/resources/image/fig-excluir.gif" alt="Excluir FontesRecurso" border="0">                        
                        <?php //} ?>
                    </td>
                    <td>
                        <?php echo $objL->getNomeFontesRecurso(); ?>
                    </td>
                    <td>
                        <?php echo $objL->getTipoFontesRecurso(); ?>
                    </td>
                    <td>
                        <?php echo $objL->getDescricao(); ?>
                    </td>
                </tr>
                <?php
            }
            ?>
                <br>Total de Centros de Custo cadastradas: <?php echo  $ii ?>.
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

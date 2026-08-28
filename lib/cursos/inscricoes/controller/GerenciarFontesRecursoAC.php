<?php

class GerenciarFontesRecursoAC {

    function __construct() {
        FWA_exigirPermissao([enumSistema::SIS_GERENCIAR_ADM_GERAL, enumSistema::SIS_CONTROLE_FINANCEIRO]);
        
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
                  "saldoInicial":"' . $obj->getSaldoInicial() . '",
                  "dataSaldoInicial":"' . $obj->getDataSaldoInicial() . '",
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
            // saldo vem do form em pt-BR ("1.500,50"): normaliza para o decimal
            // que o banco entende, senao ele corta na virgula (vira 1500)
            if (isset($fws_['saldoInicial']) && $fws_['saldoInicial'] !== '') {
                $fws_['saldoInicial'] = str_replace(',', '.', str_replace('.', '', $fws_['saldoInicial']));
            }
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
                        <span class="editar btn-acao" title="Editar"><i class="fa fa-pencil"></i></span>
                        <span class="excluir btn-acao btn-acao-excluir" title="Excluir"><i class="fa fa-trash"></i></span>
                    </td>
                    <td class="col-nome">
                        <?php echo $objL->getNomeFontesRecurso(); ?>
                    </td>
                    <td>
                        <?php
                        $tipo = $objL->getTipoFontesRecurso();
                        $dotTipo = ($tipo == 'INTERNA') ? 'dot-blue' : 'dot-green';
                        ?>
                        <span class="badge-tipo"><span class="dot <?php echo $dotTipo; ?>"></span><?php echo $tipo; ?></span>
                    </td>
                    <td>
                        <?php echo $objL->getDescricao(); ?>
                    </td>
                </tr>
                <?php
            }
            ?>
                </tbody><tfoot><tr><td colspan="4" style="text-align:right; color:#999; font-size:12px; padding:8px 10px; border-top:1px solid #eee;">Total: <?php echo $ii ?> registro<?php echo $ii != 1 ? 's' : '' ?></td></tr></tfoot><tbody class="inputs" style="display:none;">
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

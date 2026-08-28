<?php

class GerenciarColegioAC {

    function __construct() {
        $cod_usuario = $_SESSION['_cod_usuario'];
        if (!FWA_Autenticacao($cod_usuario, enumSistema::SIS_PEDAGOGICO, 5, false, false)
            && !FWA_Autenticacao($cod_usuario, enumSistema::SIS_PEDAGOGICO_GERENTE, 5, false, false)
            && !FWA_Autenticacao($cod_usuario, enumSistema::SIS_PEDAGOGICO_SUPERVISOR, 5, false, false)) {
            throw new Exception("Acesso restrito.");
        }
        $this->_isAdminPedagogico = FWA_Autenticacao($cod_usuario, enumSistema::SIS_PEDAGOGICO, 5, false, false)
            || FWA_Autenticacao($cod_usuario, enumSistema::SIS_PEDAGOGICO_GERENTE, 5, false, false);
    }

    private $_isAdminPedagogico = false;

    public function carregarObjColegio($fws_, $debug = false) {
        $obj = new Colegio();
        if (isset($fws_['codColegio']) && $fws_['codColegio']) {
            $obj->setCodColegio($fws_['codColegio']);
            $obj->getColegioById($debug);
        }
        return $obj;
    }

    private function exigirAdmin() {
        if (!$this->_isAdminPedagogico) {
            throw new Exception("Acesso restrito a coordenadores e gerentes.");
        }
    }

    public function salvarColegio($fws_, $debug = false) {
        $ok = false;
        $obj = null;
        $ex = null;
        try {
            $this->exigirAdmin();
            if (isset($fws_['novoCodColegio']) && $fws_['novoCodColegio'] == 0) {
                $fws_['codColegio'] = NULL;
            }
            $obj = $this->carregarObjColegio($fws_);
            $obj->setAll($fws_, true);
            $obj->salvar($debug);
        } catch (OkException $e) {
            $ok = true;
            $ex = $e;
        } catch (Exception $e) {
            $ok = false;
            $ex = $e;
        }

        echo json_encode(array(
            'statusMessage' => $ok,
            'message'       => $ex ? $ex->getMessage() : '',
            'codColegio'    => $obj ? $obj->getCodColegio() : ''
        ));
    }

    public function editarColegio($fws_, $debug = false) {
        $this->exigirAdmin();
        $obj = $this->carregarObjColegio($fws_, $debug);
        echo json_encode(array(
            'statusMessage'     => $obj->getCodColegio() ? true : false,
            'codColegio'        => $obj->getCodColegio(),
            'nome'              => $obj->getNome(),
            'necessidadeManha'  => $obj->getNecessidadeManha(),
            'necessidadeTarde'  => $obj->getNecessidadeTarde(),
            'alocacaoManha'     => $obj->getAlocacaoManha(),
            'alocacaoTarde'     => $obj->getAlocacaoTarde()
        ));
        exit();
    }

    public function excluirColegio($fws_, $debug = false) {
        $ok = false;
        $ex = null;
        try {
            $this->exigirAdmin();
            $obj = new Colegio();
            $obj->setCodColegio($fws_['codColegio']);
            $obj->excluir();
        } catch (OKException $e) {
            $ok = true;
            $ex = $e;
        } catch (Exception $e) {
            $ok = false;
            $ex = $e;
        }

        echo json_encode(array(
            'statusMessage' => $ok,
            'message'       => $ex ? $ex->getMessage() : '',
            'codColegio'    => $fws_['codColegio']
        ));
    }

    public function carregarTabelaColegios($fws_, $debug = false) {
        try {
            $obj = new Colegio();
            $lista = $obj->getListaColegiosByIdEvento($debug);
            $ii = 0;
            $totalNecManha = 0; $totalNecTarde = 0;
            $totalAlocManha = 0; $totalAlocTarde = 0;
            foreach ($lista as $objL) {
                $ii++;
                $necManha = intval($objL->getNecessidadeManha());
                $necTarde = intval($objL->getNecessidadeTarde());
                $alocManha = intval($objL->getAlocacaoManha());
                $alocTarde = intval($objL->getAlocacaoTarde());
                $totalNecManha += $necManha;
                $totalNecTarde += $necTarde;
                $totalAlocManha += $alocManha;
                $totalAlocTarde += $alocTarde;
                ?>
                <tr id="<?php echo $objL->getCodColegio() ?>">
                    <td><?php echo htmlspecialchars($objL->getNome()) ?></td>
                    <td class="text-center"><?php echo $necManha ?></td>
                    <td class="text-center"><?php echo $necTarde ?></td>
                    <td class="text-center"><?php echo $alocManha ?></td>
                    <td class="text-center"><?php echo $alocTarde ?></td>
                    <td class="text-nowrap">
                        <button class="btn btn-xs btn-acao-editar editar" title="Editar">
                            <i class="fa fa-pencil"></i>
                        </button>
                        <button class="btn btn-xs btn-acao-excluir excluir" title="Excluir">
                            <i class="fa fa-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php
            }
            ?>
            <tr style="font-weight:bold;">
                <td>Total (<?php echo $ii ?> col&eacute;gios)</td>
                <td class="text-center"><?php echo $totalNecManha ?></td>
                <td class="text-center"><?php echo $totalNecTarde ?></td>
                <td class="text-center"><?php echo $totalAlocManha ?></td>
                <td class="text-center"><?php echo $totalAlocTarde ?></td>
                <td></td>
            </tr>
            <?php
        } catch (Exception $e) {
            echo "<tr><td colspan='6'>";
            if ($e->getMessage()) {
                echo $e->getMessage();
            } else {
                echo "Lista vazia.";
            }
            echo "</td></tr>";
        }
    }

    public function carregarListaColegios($fws_, $debug = false) {
        try {
            $obj = new Colegio();
            $lista = $obj->getListaColegiosByIdEvento($debug);
            $colegios = array();
            foreach ($lista as $objL) {
                $colegios[] = array(
                    'codColegio'       => $objL->getCodColegio(),
                    'nome'             => $objL->getNome(),
                    'necessidadeManha' => intval($objL->getNecessidadeManha()),
                    'necessidadeTarde' => intval($objL->getNecessidadeTarde()),
                    'alocacaoManha'    => intval($objL->getAlocacaoManha()),
                    'alocacaoTarde'    => intval($objL->getAlocacaoTarde())
                );
            }
            echo json_encode(array(
                'statusMessage' => true,
                'colegios'      => $colegios
            ));
        } catch (Exception $e) {
            echo json_encode(array(
                'statusMessage' => true,
                'colegios'      => array()
            ));
        }
    }

}

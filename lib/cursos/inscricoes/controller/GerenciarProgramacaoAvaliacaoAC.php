<?php

class GerenciarProgramacaoAvaliacaoAC {

    function __construct() {
        $cod_usuario = $_SESSION['_cod_usuario'];
        if (!FWA_Autenticacao($cod_usuario, enumSistema::SIS_PEDAGOGICO, 5, false, false)
            && !FWA_Autenticacao($cod_usuario, enumSistema::SIS_PEDAGOGICO_GERENTE, 5, false, false)) {
            throw new Exception("Acesso restrito a coordenadores e gerentes.");
        }
    }

    public function carregarObjProgramacao($fws_, $debug = false) {
        $obj = new ProgramacaoAvaliacao();
        if (isset($fws_['codProgramacao']) && $fws_['codProgramacao']) {
            $obj->setCodProgramacao($fws_['codProgramacao']);
            $obj->getProgramacaoAvaliacaoById($debug);
        }
        return $obj;
    }

    public function salvarProgramacao($fws_, $debug = false) {
        $ok = false;
        $obj = null;
        $ex = null;
        try {
            if (isset($fws_['novoCodProgramacao']) && $fws_['novoCodProgramacao'] == 0) {
                $fws_['codProgramacao'] = NULL;
            }
            $obj = $this->carregarObjProgramacao($fws_);
            $obj->setAll($fws_, true);
            $obj->salvar($debug);
        } catch (OKException $e) {
            $ok = true;
            $ex = $e;
        } catch (Exception $e) {
            $ok = false;
            $ex = $e;
        }

        echo json_encode(array(
            'statusMessage'  => $ok,
            'message'        => $ex ? $ex->getMessage() : '',
            'codProgramacao' => $obj ? $obj->getCodProgramacao() : ''
        ));
    }

    public function editarProgramacao($fws_, $debug = false) {
        $obj = $this->carregarObjProgramacao($fws_, $debug);
        echo json_encode(array(
            'statusMessage'  => $obj->getCodProgramacao() ? true : false,
            'codProgramacao' => $obj->getCodProgramacao(),
            'descricao'      => $obj->getDescricao(),
            'tipoAvaliacao'  => $obj->getTipoAvaliacao(),
            'dataLiberacao'  => $obj->getDataLiberacao(),
            'dataFechamento' => $obj->getDataFechamento()
        ));
        exit();
    }

    public function excluirProgramacao($fws_, $debug = false) {
        $ok = false;
        $ex = null;
        try {
            $obj = new ProgramacaoAvaliacao();
            $obj->setCodProgramacao($fws_['codProgramacao']);
            $obj->excluir();
        } catch (OKException $e) {
            $ok = true;
            $ex = $e;
        } catch (Exception $e) {
            $ok = false;
            $ex = $e;
        }

        echo json_encode(array(
            'statusMessage'  => $ok,
            'message'        => $ex ? $ex->getMessage() : '',
            'codProgramacao' => $fws_['codProgramacao']
        ));
    }

    public function carregarTabelaProgramacoes($fws_, $debug = false) {
        try {
            $obj = new ProgramacaoAvaliacao();
            $lista = $obj->getListaProgramacoes($debug);
            $ii = 0;
            $hoje = date('Y-m-d');
            foreach ($lista as $objP) {
                $ii++;
                $dataLib = $objP->getDataLiberacao() ? date('d/m/Y', strtotime($objP->getDataLiberacao())) : '-';
                $dataFech = $objP->getDataFechamento() ? date('d/m/Y', strtotime($objP->getDataFechamento())) : '-';

                $situacao = 'Aguardando';
                $labelClass = 'label-default';
                if ($hoje >= $objP->getDataLiberacao() && $hoje <= $objP->getDataFechamento()) {
                    $situacao = 'Aberta';
                    $labelClass = 'label-success';
                } elseif ($hoje > $objP->getDataFechamento()) {
                    $situacao = 'Encerrada';
                    $labelClass = 'label-danger';
                }
                ?>
                <tr id="<?php echo $objP->getCodProgramacao() ?>">
                    <td><?php echo htmlspecialchars($objP->getDescricao()) ?></td>
                    <td><?php echo htmlspecialchars($objP->getTipoAvaliacao()) ?></td>
                    <td><?php echo $dataLib ?></td>
                    <td><?php echo $dataFech ?></td>
                    <td><span class="label <?php echo $labelClass ?>"><?php echo $situacao ?></span></td>
                    <td class="text-nowrap">
                        <button class="btn btn-xs btn-acao-editar editar-prog" title="Editar">
                            <i class="fa fa-pencil"></i>
                        </button>
                        <button class="btn btn-xs btn-acao-excluir excluir-prog" title="Excluir">
                            <i class="fa fa-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php
            }
            ?>
            <tr><td colspan="6">Total de per&iacute;odos cadastrados: <?php echo $ii ?>.</td></tr>
            <?php
        } catch (Exception $e) {
            echo "<tr><td colspan='6'>";
            if ($e->getMessage()) {
                echo $e->getMessage();
            } else {
                echo "Nenhuma programacao cadastrada.";
            }
            echo "</td></tr>";
        }
    }

}

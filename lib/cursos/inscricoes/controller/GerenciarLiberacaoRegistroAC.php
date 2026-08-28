<?php

class GerenciarLiberacaoRegistroAC
{

    function __construct()
    {
        FWA_exigirPermissao([enumSistema::SIS_GERENCIAR_ADM_GERAL, enumSistema::SIS_GERENCIAR_AREAS_TEMATICAS]);
        $this->codEvento = COD_EVENTO;
        $objConf = new GerenciarConfiguracaoEventoAC();
        $objConf->carregarConfiguracao($this);
    }

    public function carregarObj($fws_, $debug = false)
    {
        $obj = new LiberacaoRegistro();
        if ($fws_['codLiberacao']) {
            $obj->setCodLiberacao($fws_['codLiberacao']);
            $obj->getLiberacaoById($debug);
        }
        return $obj;
    }

    public function salvar($fws_, $debug = false)
    {
        $ok = false;
        $ex = null;

        try {
            if ($fws_['updateLiberacao'] == 0) {
                $fws_['codLiberacao'] = NULL;
            }
            $obj = $this->carregarObj($fws_);
            $obj->setAll($fws_, true);
            $obj->salvar();
            $ok = true;
        } catch (OkException $ex) {
            $ok = true;
        } catch (Exception $ex) {
        }

        echo json_encode(array(
            'statusMessage' => $ok ? '1' : '0',
            'message' => $ex ? $ex->getMessage() : '',
            'codLiberacao' => $obj ? $obj->getCodLiberacao() : ''
        ));
    }

    public function editar($fws_, $debug = false)
    {
        $ok = 0;
        $obj = $this->carregarObj($fws_, $debug);
        if ($obj->getCodLiberacao()) {
            $ok = 1;
        }
        // Buscar nome do inscrito para exibir no Select2
        $nomeInscrito = '';
        if ($obj->getCodInscrito()) {
            $objInsc = new Inscrito();
            $objInsc->setCodInscrito($obj->getCodInscrito());
            $objInsc->setCodEvento(COD_EVENTO);
            $objInsc->getInscritoById();
            $nomeInscrito = $objInsc->getNomeInscrito();
        }

        echo json_encode(array(
            'statusMessage' => $ok,
            'message' => $obj->getMessage(),
            'codLiberacao' => $obj->getCodLiberacao(),
            'codInscrito' => $obj->getCodInscrito(),
            'nomeInscrito' => $nomeInscrito,
            'dataInicio' => $obj->getDataInicio(),
            'dataFim' => $obj->getDataFim(),
            'observacao' => $obj->getObservacao()
        ));
        exit();
    }

    public function excluir($fws_, $debug = false)
    {
        $ok = false;
        $ex = null;

        try {
            $obj = $this->carregarObj($fws_);
            $obj->excluir();
        } catch (OKException $ex) {
            $ok = true;
        } catch (Exception $ex) {
            $ok = false;
        }

        echo json_encode(array(
            'statusMessage' => $ok ? '1' : '0',
            'message' => $ex ? $ex->getMessage() : '',
            'codLiberacao' => $fws_['codLiberacao']
        ));
        exit();
    }

    public function buscarInscritos($fws_, $debug = false)
    {
        $obj = new LiberacaoRegistro();
        $obj->setTermoBusca($fws_['termo']);
        $results = $obj->buscarInscritos($debug);
        echo json_encode(array('results' => $results));
        exit();
    }

    public function carregarTabela($fws_, $debug = false)
    {
        try {
            $obj = new LiberacaoRegistro();
            $obj->carregarListaByEvento($debug);
            $lista = $obj->getListaLiberacoes($debug);
            $ii = 0;

            if (empty($lista)) {
                throw new Exception("Não possui liberações cadastradas.");
            }
            ?>
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width:90px;">Ações</th>
                        <th>Inscrito</th>
                        <th>Período</th>
                        <th>Observação</th>
                    </tr>
                </thead>
                <tbody>
            <?php
            foreach ($lista as $objL) {
                $ii++;
                ?>
                <tr data-id="<?php echo $objL->getCodLiberacao() ?>">
                    <td class="text-center text-nowrap">
                        <button class="btn btn-sm btn-outline-primary editar" title="Editar">
                            <i class="fa fa-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger excluir" title="Excluir">
                            <i class="fa fa-trash"></i>
                        </button>
                    </td>
                    <td>
                        <strong><?php echo htmlspecialchars($objL->getNomeInscrito()) ?></strong>
                        <div class="text-muted small">#<?php echo $objL->getCodInscrito() ?></div>
                    </td>
                    <td class="text-nowrap">
                        <?php echo formatDateView($objL->getDataInicio()) ?>
                        <?php if($objL->getDataFim()){ ?>
                            <br>até <?php echo formatDateView($objL->getDataFim()) ?>
                        <?php } ?>
                    </td>
                    <td><?php echo htmlspecialchars($objL->getObservacao()) ?></td>
                </tr>
                <?php
            }
            if ($ii === 0) throw new Exception("Não possui liberações cadastradas.");
            ?>
                </tbody>
            </table>
            <div class="text-muted small">Total: <?php echo $ii ?> liberação(ões)</div>
            <?php
        } catch (Exception $e) {
            ?>
            <div class="text-center text-muted py-4">
                <?php echo $e->getMessage() ?: 'Lista vazia.'; ?>
            </div>
            <?php
        }
    }
}

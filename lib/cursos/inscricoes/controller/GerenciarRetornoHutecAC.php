<?php

class GerenciarRetornoHutecAC
{

    public $valorTotal = 0;
    public $totalRegistros = 0;

    function __construct()
    {
        $this->codEvento = COD_EVENTO;
        $objConf = new GerenciarConfiguracaoEventoAC();
        $objConf->carregarConfiguracao($this);
    }

    public function carregarObjRetornoHutec($fws_, $debug = false)
    {
        $obj = new RetornoHutec();
        if ($fws_['codRetornoHutec']) {
            $obj->setCodRetornoHutec($fws_['codRetornoHutec']);
            $obj->getRetornoHutecById($debug);
        }
        return $obj;
    }

    public function salvarRetornoHutec($fws_, $debug = false)
    {
        $ok = false;
        $e = null;

        try {
            if ($fws_['novoRetornoHutec'] == 0) {
                $fws_['codRetornoHutec'] = NULL;
            }
            $obj = $this->carregarObjRetornoHutec($fws_);
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
    "codRetornoHutec":"' . ($obj ? $obj->getCodRetornoHutec() : '') . '"
    }';
    }

    public function editarRetornoHutec($fws_, $debug = false)
    {
        $obj = $this->carregarObjRetornoHutec($fws_, $debug);

        echo '{
                  "statusMessage":"' . $ok . '",
                  "message":"' . $obj->getMessage() . '",
                  "codRetornoHutec":"' . ($obj ? $obj->getCodRetornoHutec() : '') . '",
                  "nome":"' . ($obj ? $obj->getNome() : '') . '"
                 }';
        exit();
    }

    public function excluirRetornoHutec($fws_, $debug = false)
    {
        try {
            $obj = $this->carregarObjRetornoHutec($fws_);
            $obj->excluir();
        } catch (OKException $e) {
            $ok = true;
        } catch (Exception $e) {
            $ok = false;
        }

        echo '{
            "statusMessage":"' . $ok . '",
            "message":"' . $e->getMessage() . '",
            "codRetornoHutec":"' . $fws_['codRetornoHutec'] . '",
            }';
        exit();
    }

    /**
     * Reúne as 3 caixas da crítica de conciliação HUTEC × Sistema:
     *  - diferente: mesmo cod_pagamento, valor divergente
     *  - soHutec:   crédito no retorno HUTEC sem pagamento no sistema (a mais)
     *  - soNosso:   pagamento no sistema ausente no retorno (não lançado)
     */
    public function getCritica($debug = false)
    {
        $obj = new RetornoHutec();
        $obj->setCodEvento($this->codEvento);
        return array(
            'diferente' => $obj->getCriticaDivergentes($debug),
            'soHutec'   => $obj->getCriticaSoNoHutec($debug),
            'soNosso'   => $obj->getCriticaSoNoNosso($debug),
        );
    }

    public function carregarTabelaRetornoHutec($fws_, $debug = false)
    {
        try {
            $obj = new RetornoHutec();
            $obj->carregarListaRetornoHutecByIdEvento($debug);
            $ii = 0;

            // if (empty($lista)) {
            //     throw new Exception("Não possui registros cadastradas.");
            // }
            $codPagamentoAnt = '';
            $valorTotal  = 0;
            foreach ($obj->getListaRetornoHutec() as $objL) {
                $marcarLinha = '';
                $ii++;
                $valor = str_replace(',', '.', $objL->getValor());
                $valor = (float) $valor;
                $valorTotal += $valor;                
                if($objL->getCodPagamento()==$codPagamentoAnt){
                    $marcarLinha = 'bg-danger';
                }
                if($objL->getPagamento()!=''){
                    if($objL->getPagamento()!=$objL->getCodPagamento()){
                        $marcarLinha .= ' text-danger';
                    }
                }else{
                    $marcarLinha .= ' text-danger';
                }
                $situacao = 'VERIFICAR';
                if ($objL->getPagamento() != '' && $objL->getPagamento() == $objL->getCodPagamento()) {
                    $situacao = 'OK';
                }
                ?>
                <tr id="<?php echo $objL->getCodRetornoHutec() ?>" data-situacao="<?php echo $situacao ?>">
                    <td>
                        <?php echo $ii ?> - 
                        <!-- <button class="editar" type="submit" title="Editar RetornoHutec">
                            <i class="fa-regular fa-folder-open"></i>
                        </button>
                        <button class="excluir" title="Excluir RetornoHutec">
                            <i class="fa-regular fa-trash-can"></i>
                        </button> -->
                    </td>

                    <td><?php echo  $objL->getData()?></td>
                    <td class="text-center <?php echo $marcarLinha; ?>"><?php echo $objL->getCodPagamento() ?></td>
                    <td><?php echo $objL->getNome() ?></td>
                    <td><?php echo $objL->getDescricao() ?></td>
                    <td class="text-center"><?php echo $objL->getCodExtra() ?></td>
                    <td class="text-end"><?php echo number_format((float) str_replace(',', '.', $objL->getValor()), 2, ',', '.') ?></td>
                    <td class="text-center"><?php echo $objL->getTipoOperacao() ?></td>
                    <td class="text-center">
                        <?php echo $situacao === 'OK' ? 'OK' : '<span class="text-danger">VERIFICAR</span>'; ?>
                    </td>
                </tr>
                <?php
            }
            $this->valorTotal = $valorTotal;
            $this->totalRegistros = $ii;
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
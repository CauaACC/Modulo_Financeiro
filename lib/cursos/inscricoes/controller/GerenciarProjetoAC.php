<?php

class GerenciarProjetoAC
{

    function __construct()
    {
        FWA_exigirPermissao(enumSistema::SIS_GERENCIAR_ADM_GERAL);
    }

    public function carregarProjeto($fws_, $debug = false)
    {
        $obj = new Projeto();
        $obj->setCodProjeto($fws_['codProjeto']);
        $obj->getProjetoById($debug);
        return $obj;
    }

    public function editarProjeto($fws_, $debug = false)
    {
        global $ok;
        $obj = $this->carregarProjeto($fws_, $debug);
        echo '{
                "statusMessage": "' . $ok . '",
                "message": "' . $obj->getMessage() . '",
                "codProjeto": "' . $obj->getCodProjeto() . '",
                "nomeProjeto": "' . $obj->getNomeProjeto() . '",
                "codEvento": "' . $obj->getCodEvento() . '",
                "codProjeto": "' . $obj->getCodProjeto() . '",
                "emailCoordenador": "' . $obj->getEmailCoordenador() . '",
                "nomeCoordenador": "' . $obj->getNomeCoordenador() . '",
                "sigla": "' . $obj->getsigla() . '",
                "dataInicioEvento":"' . $obj->getDataInicioEvento() . '",
                "dataFimEvento":"' . $obj->getDataInicioEvento() . '",
                "dataInicioInscricoes":"' . $obj->getDataInicioInscricoes() . '",
                "dataFimInscricoes":"' . $obj->getDataFimInscricoes() . '",
                "tipoProjeto":"' . $obj->getTipoProjeto() . '",
                "trabEnvioTrabalhos":"' . $obj->getTrabEnvioTrabalhos() . '",
                "trabEscolherAtividades":"' . $obj->getTrabEscolherAtividades() . '",
                "trabEmissaoCertificados":"' . $obj->getTrabEmissaoCertificados() . '",
                "trabReceberPagamentos":"' . $obj->getTrabReceberPagamentos() . '",
                "descricaoProjeto":"' . $obj->getDescricaoProjeto() . '",
                "formasPagamento":"' . $obj->getFormasPagamento() . '",
                "descricaoValoresInscricao":"' . $obj->getDescricaoValoresInscricao() . '",
                "listaAtividades":"' . $obj->getListaAtividades() . '",
                "descricaoAtividades":"' . $obj->getDescricaoAtividades() . '",
                "infoTrabEnvioArtigo":"' . $obj->getInfoTrabEnvioArtigo() . '",
                "descricaoTrabalhosArtigos":"' . $obj->getDescricaoTrabalhosArtigos() . '",
                "publicoAlvo":"' . $obj->getPublicoAlvo() . '",
                "quantidadeVagas":"' . $obj->getQuantidadeVagas() . '",
                "cargaHoraria":"' . $obj->getCargaHoraria() . '",
                "investimento":"' . $obj->getInvestimento() . '",
                "programacao":"' . $obj->getProgramacao() . '",
                "outrasInformacoes":"' . $obj->getOutrasInformacoes() . '",
                "linkInscricoes":"' . $obj->getLinkInscricoes() . '"
                 }';
        exit();
    }

    public function salvarProjeto($fws_, $debug = false)
    {
        try {

            if ($fws_['update'] == 0) {
                $fws_['codProjeto'] = NULL;
            }
            $isForm = true;
            $obj = $this->carregarProjeto($fws_, $debug);
            $fws_['dataInicioEvento'] = formatDateHoraTrans($fws_['dataInicioEvento'], 'DD/MM/YYYY HH:II', 'YYYY-MM-DD HH:II');
            $fws_['dataFimEvento'] = formatDateHoraTrans($fws_['dataFimEvento'], 'DD/MM/YYYY HH:II', 'YYYY-MM-DD HH:II');
            $fws_['dataInicioInscricoes'] = formatDateHoraTrans($fws_['dataInicioInscricoes'], 'DD/MM/YYYY HH:II', 'YYYY-MM-DD HH:II');
            $fws_['dataFimInscricoes'] = formatDateHoraTrans($fws_['dataFimInscricoes'], 'DD/MM/YYYY HH:II', 'YYYY-MM-DD HH:II');
            $obj->setAll($fws_, $isForm);
            if (!$fws_['trabEnvioTrabalhos']) {
                $obj->setTrabEnvioTrabalhos(0);
            }
            if (!$fws_['trabEscolherAtividades']) {
                $obj->setTrabEscolherAtividades(0);
            }
            if (!$fws_['trabEmissaoCertificados']) {
                $obj->setTrabEmissaoCertificados(0);
            }
            if (!$fws_['trabReceberPagamentos']) {
                $obj->setTrabReceberPagamentos(0);
            }
            $obj->salvar();
        } catch (OkException $e) {
            $ok = true;
        } catch (Exception $e) {
            $ok = false;
        }

        echo '{
        "statusMessage":"' . $ok . '",
        "message":      "' . $e->getMessage() . '",
        "codProjeto":"' . $obj->getCodProjeto() . '"
        }';
    }

    public function excluirProjeto($fws_, $debug = false)
    {
        try {
            $obj = new Projeto();
            $obj->setCodProjeto($fws_['codProjeto']);

            $obj->excluir();
        } catch (OKException $e) {
            $ok = true;
        } catch (Exception $e) {
            $ok = false;
        }

        echo '{
        "statusMessage":"' . $ok . '",
        "message":      "' . $e->getMessage() . '",
        "codProjeto":"' . $fws_['codProjeto'] . '"
        }';
    }

    public function carregarTabelaProjetos($fws_, $debug = false)
    {
        $obj = new Projeto();
        try {
            $lista = $obj->getListaProjetosByIdEvento($debug);
            foreach ($lista as $objL) {
                $ii++;
                ?>
                <tr id="<?php echo $objL->getCodProjeto() ?>">
                    <td>
                        <span class="btn-acao editar" title="Editar"><i class="fa fa-pencil"></i></span>
                        <span class="btn-acao btn-acao-excluir excluir" title="Excluir"><i class="fa fa-trash"></i></span>
                    </td>
                    <td><?php echo textoMaiusculoMinusculo($objL->getTituloEvento()) ?></td>
                </tr>
                <?php
            }
            ?>
            <br>Total de descontos cadastrados: <?php echo $ii ?>.
            <br>Total de descontos já inscritos: <?php echo $totalJaInscrito ?>.
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

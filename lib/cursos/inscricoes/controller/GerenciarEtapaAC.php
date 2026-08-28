<?php

class GerenciarEtapaAC {

    /**
     * Garante checagem de auth ao instanciar o controller. O seguranca.php
     * faz FWA_Autenticacao e da exit() se nao houver permissao — protegendo
     * todos os metodos publicos contra invocacao via AJAX sem sessao valida.
     */
    function __construct() {
        require_once(dirname(__FILE__) . "/../view/admin/consulta-avaliacao-inscrito-seguranca.php");
    }

    public function carregarEtapa($fws_, $debug = false) {
        $obj = new Etapa();
        $obj->setCodEtapa($fws_['codEtapa']);
        $obj->getEtapaById($debug);
        return $obj;
    }

    public function editarEtapa($fws_, $debug = false) {
        $obj = $this->carregarEtapa($fws_,$debug);

        echo '{
                  "statusMessage":"' . $ok . '",
                  "message":      "' . $obj->getMessage() . '",
                  "codEtapa":  "' . $obj->getCodEtapa() . '",
                  "nomeEtapa": "' . $obj->getNomeEtapa() . '",
                  "descricao": "' . tratarJson($obj->getDescricao()) . '",
                  "ordemEtapa":"' . $obj->getOrdemEtapa() . '",
                  "situacao":  "' . (int)$obj->getSituacao() . '",
                  "analiseDatIni":"' . $obj->getAnaliseDatIni() . '",
                  "analiseDatFim":"' . $obj->getAnaliseDatFim() . '",
                  "divulgacaoPreliminarDat":"' . $obj->getDivulgacaoPreliminarDat() . '",
                  "resultadoDivulgado":"' . (int)$obj->getResultadoDivulgado() . '",
                  "recursoDatIni":"' . $obj->getRecursoDatIni() . '",
                  "recursoDatFim":"' . $obj->getRecursoDatFim() . '",
                  "analiseRecursoDatIni":"' . $obj->getAnaliseRecursoDatIni() . '",
                  "analiseRecursoDatFim":"' . $obj->getAnaliseRecursoDatFim() . '",
                  "divulgacaoRecursoDat":"' . $obj->getDivulgacaoRecursoDat() . '",
                  "resultadoRecursoDivulgado":"' . (int)$obj->getResultadoRecursoDivulgado() . '",
                  "divulgacaoFinalDat":"' . $obj->getDivulgacaoFinalDat() . '",
                  "resultadoFinalDivulgado":"' . (int)$obj->getResultadoFinalDivulgado() . '",
                  "status":    "' . $obj->getStatus() . '"
                 }';
        exit();
    }

    public function salvarEtapa($fws_, $debug = false) {
        try {
            $isForm = true;
            $obj = $this->carregarEtapa($fws_);
            $obj->setAll($fws_, $isForm);
            // Situacao (0=Inativa, 1=Em andamento, 2=Concluida, 3=Aguardando)
            $sit = isset($fws_['situacao']) ? (int)$fws_['situacao'] : 0;
            if ($sit < 0 || $sit > 3) { $sit = 0; }
            $obj->setSituacao($sit);
            // Datas do cronograma: input datetime-local manda 'YYYY-MM-DDTHH:MM',
            // converter pra 'YYYY-MM-DD HH:MM:SS' do banco. Vazio = NULL.
            $normDt = function($v) {
                $v = trim((string) $v);
                if ($v === '') return null;
                $v = str_replace('T', ' ', $v);
                if (strlen($v) === 16) $v .= ':00';
                return $v;
            };
            $obj->setAnaliseDatIni($normDt($fws_['analiseDatIni'] ?? ''));
            $obj->setAnaliseDatFim($normDt($fws_['analiseDatFim'] ?? ''));
            $obj->setDivulgacaoPreliminarDat($normDt($fws_['divulgacaoPreliminarDat'] ?? ''));
            $obj->setResultadoDivulgado(((string) ($fws_['resultadoDivulgado'] ?? '0')) === '1' ? 1 : 0);
            $obj->setRecursoDatIni($normDt($fws_['recursoDatIni'] ?? ''));
            $obj->setRecursoDatFim($normDt($fws_['recursoDatFim'] ?? ''));
            $obj->setAnaliseRecursoDatIni($normDt($fws_['analiseRecursoDatIni'] ?? ''));
            $obj->setAnaliseRecursoDatFim($normDt($fws_['analiseRecursoDatFim'] ?? ''));
            $obj->setDivulgacaoRecursoDat($normDt($fws_['divulgacaoRecursoDat'] ?? ''));
            $obj->setResultadoRecursoDivulgado(((string) ($fws_['resultadoRecursoDivulgado'] ?? '0')) === '1' ? 1 : 0);
            $obj->setDivulgacaoFinalDat($normDt($fws_['divulgacaoFinalDat'] ?? ''));
            $obj->setResultadoFinalDivulgado(((string) ($fws_['resultadoFinalDivulgado'] ?? '0')) === '1' ? 1 : 0);
            $obj->salvar($debug);
        } catch (OkException $e) {
            $ok = true;
        } catch (Exception $e) {
            $ok = false;
        }

        echo '{
        "statusMessage":"' . $ok . '",
        "message":      "' . tratarJson($e->getMessage()) . '",
        "codEtapa":"' . $obj->getCodEtapa() . '"
        }';
    }

    public function excluirEtapa($fws_, $debug = false) {
        try {
            $obj = new Etapa();
            $obj->setCodEtapa($fws_['codEtapa']);
            $obj->excluir();
        } catch (OKException $e) {
            $ok = true;
        } catch (Exception $e) {
            $ok = false;
        }

        echo '{
        "statusMessage":"' . $ok . '",
        "message":      "' . tratarJson( $e->getMessage()) . '",
        "codEtapa":"' . $fws_['codEtapa'] . '"
        }';
    }

    /**
     * Toggle rapido pelo icone da listagem.
     * Em Andamento (1) <-> Concluida (2). Demais estados sao ajustados via modal.
     */
    public function toggleAtivaEtapa($fws_, $debug = false) {
        try {
            $obj = $this->carregarEtapa($fws_, $debug);
            $atual = (int)$obj->getSituacao();
            $novo = ($atual === Etapa::SITUACAO_EM_ANDAMENTO)
                ? Etapa::SITUACAO_CONCLUIDA
                : Etapa::SITUACAO_EM_ANDAMENTO;
            $obj->setSituacao($novo);
            $obj->salvar($debug);
        } catch (OKException $e) {
            $ok = true;
        } catch (Exception $e) {
            $ok = false;
        }

        echo '{
        "statusMessage":"' . $ok . '",
        "message":      "' . tratarJson($e->getMessage()) . '",
        "codEtapa":"' . $fws_['codEtapa'] . '"
        }';
    }

    public function carregarTabelaEtapas($fws_, $debug = false) {
        $obj = new Etapa();
        $lista = [];
        try {
            $obj->carregarListaEtapa(false, $debug);
            $lista = $obj->getListaEtapa();
        } catch (Exception $e) {}
        $ii = 0;
        if (!empty($lista)) {
            // Ordenar por ordem_etapa pra visual ficar coerente
            usort($lista, function($a, $b) {
                return (int)$a->getOrdemEtapa() - (int)$b->getOrdemEtapa();
            });
            foreach ($lista as $objL) {
                $ii++;
                $sit = (int)$objL->getSituacao();
                $emAndamento = $sit === Etapa::SITUACAO_EM_ANDAMENTO;
                switch ($sit) {
                    case Etapa::SITUACAO_EM_ANDAMENTO:
                        $bg='#e6f4ea'; $cor='#2d6a4f'; $rotulo='Em andamento'; break;
                    case Etapa::SITUACAO_CONCLUIDA:
                        $bg='#f1f1f1'; $cor='#555';    $rotulo='Concluída';   break;
                    case Etapa::SITUACAO_AGUARDANDO:
                        $bg='#fff4e5'; $cor='#8a6d3b'; $rotulo='Aguardando';  break;
                    case Etapa::SITUACAO_INATIVA:
                    default:
                        $bg='#f1f1f1'; $cor='#888';    $rotulo='Inativa';     break;
                }
                $badgeAtiva = '<span style="display:inline-block; padding:3px 10px; border-radius:12px; background:'.$bg.'; color:'.$cor.'; font-size:11px; font-weight:600;">'.$rotulo.'</span>';
                $tituloToggle = $emAndamento ? 'Marcar como Concluída' : 'Mudar para Em andamento';
                $iconeToggle  = $emAndamento ? 'fa-toggle-on' : 'fa-toggle-off';
                ?>
                <tr id="<?php echo  $objL->getCodEtapa() ?>">
                    <td>
                        <span class="btn-acao editar" title="Editar"><i class="fa fa-pencil"></i></span>
                        <span class="btn-acao btn-toggle-ativa" title="<?php echo $tituloToggle ?>"><i class="fa <?php echo $iconeToggle ?>"></i></span>
                        <span class="btn-acao btn-acao-excluir excluir" title="Excluir"><i class="fa fa-trash"></i></span>
                    </td>
                    <td style="text-align:center; font-weight:600;"><?php echo $objL->getOrdemEtapa() ?: '&mdash;'; ?></td>
                    <td><?php echo htmlspecialchars($objL->getNomeEtapa()); ?></td>
                    <td><?php echo htmlspecialchars($objL->getDescricao()); ?></td>
                    <td><?php echo $badgeAtiva; ?></td>
                </tr>
                <?php
            }
        }
        if ($ii === 0) {
            echo '<tr><td colspan="5" class="text-center" style="padding:30px 10px; color:#999; font-style:italic;">Nenhuma etapa encontrada.</td></tr>';
        }
    }
}

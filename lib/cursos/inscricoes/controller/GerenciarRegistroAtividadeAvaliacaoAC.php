<?php
//error_reporting(E_ALL);
//ini_set("display_errors", "On");

class GerenciarRegistroAtividadeAvaliacaoAC {

    function __construct() {

    }

    /** @todo migrar perfis para configuração no banco (tab_opcoes_perfis) */
    public function temAcessoAvaliacaoRegistro($codPerfil) {
        return in_array($codPerfil, ['469', '477']);
    }

    public function carregarRegistroAtividade($fws_, $debug = false) {
        $obj = new RegistroAtividade();
        $obj->setCodRegAtividade($fws_['codRegAtividade']);
        $obj->getRegistroAtividadeById($debug);
        return $obj;
    }
    public function editarRegistroAtividadeAvaliacao($fws_, $debug = false) {
        $ok = true;
        $obj = $this->carregarRegistroAtividade($fws_,$debug);
        $horasTrabalhadas = floor($obj->getHorasTrabalhadas() / 60) . ' horas e ' . ($obj->getHorasTrabalhadas() % 60) . ' minutos';
        // $txtDescricao = str_replace("\'", '\"', $$obj->getDescricao());
        $txtDescricao = $obj->getDescricao();
        $txtDescricao = str_replace("\\'", "'", $txtDescricao); // remove o escape
        $txtDescricao = str_replace("'", '"', $txtDescricao);   // troca por aspas duplas
        echo '{
                  "dia":        "' . formatDateView($obj->getDia()) . '",
                  "diaSemana":        "' . $obj->getDiaSemana() . '",
                  "presenca":        "' . $obj->getPresenca() . '",
                  "motivo":        "' . $obj->getMotivo() . '",
                  "observacao":        "' . tratarJson($obj->getObservacao()) . '",
                  "descricao":        "' . tratarJson($txtDescricao) . '",
                  "justificativa":        "' . tratarJson($obj->getJustificativa()) . '",
                  "horasTrabalhadas":        "' . $horasTrabalhadas.'",
                  "codRegAtividade": "' . $obj->getCodRegAtividade() . '",
                  "codAvaliador": "' . $obj->getCodAvaliador() . '",
                  "resumoRegistroAtividade":        "' . $obj->getDia() . '-'. $obj->getDiaSemana() . '-'. $obj->getPresenca() . '",
                  "nomeInscrito":        "' . $obj->getNomeInscrito() . '",
                  "codInscrito":        "' . $obj->getCodInscrito() . '",
                  "parecerAvaliador":        "' . tratarJson($obj->getParecerAvaliador()) . '",
                  "avaliacaoResponsavel": "' . $obj->getAvaliacaoResponsavel() . '",
                  "avaliacaoStatus":        "' . $obj->getAvaliacaoStatus() . '",
                  "avaliacaoData":        "' . $obj->getAvaliacaoData() . '",
                  "statusMessage":"' . $ok . '",
                  "message":      "' . $obj->getMessage() . '"              
                 }';
        exit();
    }

    public function salvarRegistroAtividadeAvaliacao($fws_, $debug = false) {
        try {
            if ($fws_['novoCodRegAtividade'] == 0) {
                $fws_['codRegAtividade'] = NULL;
            }
            $isForm = true;
            $obj = $this->carregarRegistroAtividade($fws_);

            // Verificar se o mês da atividade está bloqueado para avaliação
            if ($obj->getDia()) {
                $partesDia = explode('-', $obj->getDia());
                if (count($partesDia) >= 2 && BloqueioAvaliacao::isMesBloqueado(intval($partesDia[1]), intval($partesDia[0]))) {
                    throw new Exception("Avaliação bloqueada para o mês " . $partesDia[1] . "/" . $partesDia[0] . ". Entre em contato com o administrador.");
                }
            }
            $obj->setCodAvaliador($_SESSION['_cod_inscrito']);
            $obj->setNomeInscrito($fws_['NomeInscrito']);
            $obj->setParecerAvaliador($fws_['parecerAvaliador']);
            $obj->setAvaliacaoStatus($fws_['avaliacaoStatus']);
            $obj->setAvaliacaoResponsavel($fws_['avaliacaoResponsavel']);
            $obj->setAvaliacaoData(date('Y-m-d H:i:s'));
            $obj->salvarAvaliacao($debug);
        } catch (OkException $e) {
            $ok = true;
        } catch (Exception $e) {
            $ok = false;
        }

        echo '{
        "statusMessage":"' . $ok . '",
        "message":      "' . $e->getMessage() . '",
        "codRegAtividade":"' . $obj->getcodRegAtividadeAvaliacao() . '"
        }';
    }

    // public function excluirRegistroAtividade($fws_, $debug = false) {
    //     try {
    //         $obj = new RegistroAtividade();
    //         $obj->setcodRegAtividade($fws_['codRegAtividade']);

    //         $obj->excluir();
    //     } catch (OKException $e) {
    //         $ok = true;
    //     } catch (Exception $e) {
    //         $ok = false;
    //     }

    //     echo '{
    //     "statusMessage":"' . $ok . '",
    //     "message":      "' . $e->getMessage() . '",
    //     "codRegAtividade":"' . $fws_['codRegAtividade'] . '"
    //     }';
    // }
    public function corStatus($objL, $tipoRetorno, $debug = false)  {
        if ($objL->getAvaliacaoStatus() == "Validado") {
            $corLinha = "green";
            $filtroCor = "Validado";
        } elseif ($objL->getAvaliacaoStatus() == "Não Validado") {
               $corLinha = "red";
               $filtroCor = "Não Validado";
        } else {
            $corLinha = "orange";
            $filtroCor = "aguardando";
        }
        if ($tipoRetorno == 'corLinha') {
            return $corLinha;
        } else {
            return $filtroCor;
        }
    }
    public function carregarTabelaRegistroAtividades($fws_, $debug = false) {
        $obj = new RegistroAtividade();
        try {
            $lista = $obj->getListaRegistroAtividadesByIdEvento($debug);
            $mesAtual = 0;
            // dd($lista);
            $ii=0;
            foreach ($lista as $objL) {
                $ii++;
                ?>
                <?php 
                          $parts = explode("-", $objL->getDia());
                          $mes = $parts[1];
                    ?>
                <?php
                $mesNome = getMonthName($mes);
                if ($mesAtual != $mes){ 
                    $mesAtual = $mes; 
                    ?>
                    <tr class="mes <?php echo $mes; ?>">
                        <td colspan="6"><?php echo 'Atividades de ' . $mesNome; ?></td></tr>
                <?php
                }
                ?>
                <tr id="<?php echo  $objL->getCodRegAtividade() ?>" class="inscrito <?php echo $mes; ?> <?php echo $this->corStatus($objL, 'filtroCor', $debug); ?>">
                    <td>
                        <?php //if ($objEv->controlarAcessoProposta('boolean')) { ?>
                        <button class="avaliarAtividade">Validar Atividade</button>
                        <?php //} ?>
                    </td>
                   
                    <td><?php echo  $objL->getNomeInscrito() ?></td>
                    <td><?php echo  $objL->getParecerAvaliador() ?></td>
                    <td><?php echo  $objL->getAvaliacaoResponsavel() ?></td>
                    <td style="color: <?php echo $this->corStatus($objL, 'corLinha', $debug); ?>">
                        <?php
                        if($objL->getAvaliacaoStatus()!=''){
                            echo $objL->getAvaliacaoStatus();
                        }else{
                            echo "Aguardando";
                        }
                        ?>
                    </td>
                    <td><?php echo  formatDateView($objL->getAvaliacaoData()) ?></td>
                </tr>
                <?php
            }
            ?>
                <br>Total de Registros cadastrados: <?php echo  $ii ?>.
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
    public function carregarTabelaRegistroAtividadesAvaliacao($fws_, $debug = false) {
        try {
            $obj = new RegistroAtividade();
            $obj->setCodAvaliador($_SESSION['_cod_inscrito']);
            // $lista = $obj->getListaRegistroAtividadesByIdEvento($debug);

            $lista = $obj->getListaRegistroAtividadesByNre($debug);
            $mesAtual = 0;
            $totalHorasPorMes = 0; // Variável para somar as horas realizadas por mês
            $ii = 0;
            $dadosAgrupados = [];

            foreach ($lista as $objL) {
                // if ($objL->getCodInscrito() != 31680 and $objL->getCodInscrito() != 31681) {
                //     continue;
                // }
                if ($objL->getCodInscrito() == '') {
                    continue;
                }
                $parts = explode("-", $objL->getDia());
                $ano = $parts[0];
                $mes = $parts[1];
                $anoMes = $ano . '-' . $mes;
                $codInscrito = $objL->getCodInscrito();
                $nomeInscrito = $objL->getNomeInscrito();

                if (!isset($dadosAgrupados[$anoMes])) {
                    $dadosAgrupados[$anoMes] = [];
                }
                if (!isset($dadosAgrupados[$anoMes][$codInscrito])) {
                    $dadosAgrupados[$anoMes][$codInscrito] = [
                        'atividades' => [],
                        'totalHoras' => 0
                    ];
                }

                $dadosAgrupados[$anoMes][$codInscrito]['nomeInscrito'] = $objL->getNomeInscrito();
                $dadosAgrupados[$anoMes][$codInscrito]['atividades'][] = $objL;
                if (strtolower($objL->getAvaliacaoStatus()) === 'validado') {
                    $dadosAgrupados[$anoMes][$codInscrito]['totalHoras'] += (float)$objL->getHorasTrabalhadas();
                }
            }
            
            // Renderiza a tabela
            foreach ($dadosAgrupados as $anoMes => $inscritos) {
                $partsAnoMes = explode('-', $anoMes);
                $ano = $partsAnoMes[0];
                $mes = $partsAnoMes[1];
                $mesNome = getMonthName($mes);
                $classesFiltro = "mes-{$mes} ano-{$ano}";
                echo "<tr class='{$classesFiltro}'><td colspan='7'><h4>Relatório Mês: {$mesNome}/{$ano}</h4></td></tr>";
                echo "<tr class='{$classesFiltro}' style='font-weight: bold;background-color: #ccc;'>
                        <th></th><th>Dia</th><th>Dia da semana</th><th>Horas Trabalhadas</th>
                        <th>Descrição</th><th>Status Validação</th><th>Sit.Pagamento</th>
                      </tr>";

                foreach ($inscritos as $codInscrito => $dados) {
                    echo "<tr class='{$classesFiltro}'><td colspan='7' style='font-size:16px; font-weight:bold;'><br>Colaborador: ".maiusculo($dados['nomeInscrito'])."</style></td></tr>";

                    foreach ($dados['atividades'] as $objL) {
                        $resultado = GerenciarRegistroAtividadeUteisAC::tratarDadosAvaliacao($objL);

                        // Acessando as variáveis retornadas
                        $classeStatus = $resultado['classeStatus'];
                        $colorText    = $resultado['colorText'];
                        $txtBotao     = $resultado['txtBotao'];
                        $txtStatusPagamento = $resultado['txtStatusPagamento'];
                        $avaliacaoLiberada  = $resultado['avaliacaoLiberada'];

                        // Renderiza a linha da tabela
                        echo "<tr id='{$objL->getCodRegAtividade()}' class='{$classeStatus} {$classesFiltro}'>";
                        echo "<td>";
                        if ($avaliacaoLiberada==true) {
                            echo "<button class='avaliarAtividade'>".$txtBotao."</button>";
                        }else{
                            echo "<button disabled>Avalição encerrado</button>";
                        }
                        echo "</td>";
                        echo "<td>" . formatDateView($objL->getDia()) . "</td>";
                        echo "<td>{$objL->getDiaSemana()}</td>";
                        echo "<td>" . converterMinutosParaHoras($objL->getHorasTrabalhadas()) . "</td>";
                        echo "<td>{$objL->getDescricao()}</td>";
                        echo "<td style='color: $colorText;'>";
                            echo ($objL->getAvaliacaoStatus() ?: 'Aguardando Validação');
                            echo ($objL->getAvaliacaoData()? '<br>'.formatDateView($objL->getAvaliacaoData()) : '');
                        echo "</td>";
                        echo "<td>".$txtStatusPagamento."</td>";
                        echo "</tr>";

                    }

                    echo "<tr class='total-inscrito {$classesFiltro}'><td colspan='7'><strong>Total de Horas Validadas: " . converterMinutosParaHoras($dados['totalHoras']) . "</strong></td></tr>";
                }
            }
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
?>
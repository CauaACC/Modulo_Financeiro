<?php
class GerenciarRegistroAtividadeAC {

    function __construct() {
        
    }

    public function carregarRegistroAtividade($fws_, $debug = false) {
        $obj = new RegistroAtividade();
        $obj->setCodRegAtividade($fws_['codRegAtividade']);
        $obj->getRegistroAtividadeById($debug);
        return $obj;
    }

    public function editarRegistroAtividade($fws_, $debug = false) {
        $ok = false;
        $obj = $this->carregarRegistroAtividade($fws_,$debug);
        $ok =  $obj->getStatus()? true : false;
        echo '{
                  "statusMessage":"' . $ok . '",
                  "message":      "' . $obj->getMessage() . '",
                  "codRegAtividade": "' . $obj->getCodRegAtividade() . '",
                  "dia":        "' . $obj->getDia() . '",
                  "diaSemana":        "' . $obj->getDiaSemana() . '",
                  "descricao":        "' . $obj->getDescricao() . '",
                  "justificativa":        "' . $obj->getjustificativa() . '",
                  "horasTrabalhadas":        "' . $obj->getHorasTrabalhadas() . '"
                 }';
        exit();
    }

    public function salvarRegistroAtividade($fws_, $debug = false) {
        try {
            if ($fws_['novoCodRegAtividade'] == 0) {
                $fws_['codRegAtividade'] = NULL;
            }
            $isForm = true;
            $obj = $this->carregarRegistroAtividade($fws_);
            $obj->setAll($fws_, $isForm);
            $obj->salvar($debug);
        } catch (OkException $e) {
            $ok = true;
        } catch (Exception $e) {
            $ok = false;
        }

        echo '{
        "statusMessage":"' . $ok . '",
        "message":      "' . $e->getMessage() . '",
        "codRegAtividade":"' . $obj->getCodRegAtividade() . '"
        }';
    }

    public function excluirRegistroAtividade($fws_, $debug = false) {
        try {
            $obj = new RegistroAtividade();
            $obj->setCodRegAtividade($fws_['codRegAtividade']);

            $obj->excluir();
        } catch (OKException $e) {
            $ok = true;
        } catch (Exception $e) {
            $ok = false;
        }

        echo '{
        "statusMessage":"' . $ok . '",
        "message":      "' . $e->getMessage() . '",
        "codRegAtividade":"' . $fws_['codRegAtividade'] . '"
        }';
    }

    public function carregarTabelaRegistroAtividades($fws_, $debug = false) {
        try {
            $obj = new RegistroAtividade();
            $obj->setCodInscrito($_SESSION['_cod_inscrito']);
            // $lista = $obj->getListaRegistroAtividadesByIdEvento($debug);

            $lista = $obj->getListaRegistroAtividadesByIdCodInscrito($debug);
            $mesAtual = 0;
            $totalHorasPorMes = 0; // Variável para somar as horas realizadas por mês
            $ii = 0;
            foreach ($lista as $objL) {
                $ii++;

                $parts = explode("-", $objL->getDia());
                $mes = $parts[1];

                $mesNome = getMonthName($mes);
                if ($mesAtual != $mes) { 
                    if ($mesAtual != 0) { // Exibe o total de horas antes de mudar de mês
                        echo "<tr><td colspan='7'><strong>Total de Horas realizadas para o Mês de $mesNome: " . $totalHorasPorMes . " horas</strong></td></tr>";
                    }
                    $mesAtual = $mes;
                    $totalHorasPorMes = 0; // Reinicia o total de horas para o novo mês
                    ?>
                    <tr>
                        <td colspan="7">
                            <?php //echo 'Atividades do Mês: ' . $mesNome; ?>
                            <hr>
                            <?php 
                            echo "<h4>Relatório Mês: ".$mesNome."</h4>";
                            ?>
                        </td>
                    </tr>
                    <tr style="font-weight: bold;background-color: #ccc;">
                        <th width="30px"></th>
                        <th width="25px">Dia</th>
                        <th width="25px">Dia da semana</th>
                        <th width="25px">Horas Trabalhadas</th>
                        <th width="350px">Descrição das atividades</th>
                        <th width="25px">Status Validação</th>
                        <th width="25px">Sit.Pagamento</th>
                    </tr>                     
                <?php
                }
                ?>
                <tr id="<?php echo $objL->getCodRegAtividade() ?>">
                    <td>
                    <?php
                    $resultado = GerenciarRegistroAtividadeUteisAC::tratarDadosAvaliacao($objL);

                    // Acessando as variáveis retornadas
                    $classeStatus = $resultado['classeStatus'];
                    $colorText    = $resultado['colorText'];
                    $txtBotao     = $resultado['txtBotao'];
                    $txtStatusPagamento = $resultado['txtStatusPagamento'];
                    $avaliacaoLiberada  = $resultado['avaliacaoLiberada'];
                    $liberarBtnEditar  = $resultado['liberarBtnEditar'];
                    ?>
                    <?php if($liberarBtnEditar == true){ ?>
                        <img class="editar" 
                             src="<?php echo WB_PATH ?>/resources/image/alterar.gif" alt="Editar Registro Atividade: <?php echo $objL->getCodRegAtividade() ?>" border="0">
                        <img class="excluir" 
                             src="<?php echo WB_PATH ?>/resources/image/fig-excluir.gif" alt="Excluir Registro Atividade: <?php echo $objL->getCodRegAtividade() ?>" border="0">        
                    <?php }else{
                        // echo '<img src="'.WB_PATH.'/resources/image/fig-visualizar.gif" alt="Visualizar Registro Atividade: '.$objL->getCodRegAtividade().'" border="0">';
                        echo "Registro avaliado.";
                    } ?>                
                    </td>
                    <?php 
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

                // Acumula as horas realizadas do mês atual
                $totalHorasPorMes += (float)$objL->getHorasTrabalhadas();
            }
            // Exibe o total de horas do último mês após o loop
            if ($mesAtual != 0) {
                $horas = floor($totalHorasPorMes / 60);
                $minutos = $totalHorasPorMes % 60;
                echo "<tr><td colspan='7'><strong>Total de Horas realizadas para o Mês de $mesNome: " . $horas . " horas e " . $minutos . " minutos</strong></td></tr>";
            }
            ?>
                <br>Total de Registro Atividades cadastrados: <?php echo  $ii ?>.
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

    public static function colunaRegistroAtividade($fws_, $objL, $objEvConf, $condicoes, $debug){
        try {
            $obj = new RegistroAtividade();
            $obj->setCodInscrito($objL->getCodInscrito());
            // $lista = $obj->getListaRegistroAtividadesByIdEvento($debug);

            if (!empty($fws_['periodo_apuracao_reg_ativ_ini']) && !empty($fws_['periodo_apuracao_reg_ativ_fim'])) {
                // echo $fws_['periodo_apuracao_reg_ativ_ini'].'-'.$fws_['periodo_apuracao_reg_ativ_fim'];
                $data = $condicoes['periodo_apuracao_reg_ativ_ini']; // ou 'fim'
                $ano = substr($data, 0, 4);
                $mes = substr($data, 5, 2);
            }
            $arrayDiasNaoLançados = $obj->getArrayDiasMesIdEvento($mes,$ano,$obj->getCodEvento(),$debug);

            $lista = $obj->getListaRegistroAtividadesByIdCodInscrito($condicoes, $debug);
            $mesAtual = 0;
            $totalHorasPorMes = 0; // Variável para somar as horas realizadas por mês
            $ii = 0;
            foreach ($lista as $objL) {
                $ii++;

                $parts = explode("-", $objL->getDia());
                $mes = $parts[1];
                /** @todo fazer o tratamento para mostrar feriados e dias não lançados
                 * -- 1. Pega os feriados do evento
                 */
                $mesNome = getMonthName($mes);
                if ($mesAtual != $mes) { 
                    if ($mesAtual != 0) { // Exibe o total de horas antes de mudar de mês
                        echo "<tr><td colspan='7'><strong>Total de Horas realizadas para o Mês de $mesNome: " . $totalHorasPorMes . " horas</strong></td></tr>";
                    }
                    $mesAtual = $mes;
                    $totalHorasPorMes = 0; // Reinicia o total de horas para o novo mês
                    ?>
                    <tr>
                        <td colspan="7">
                            <?php //echo 'Atividades do Mês: ' . $mesNome; ?>
                            <hr>
                            <?php 
                            echo "<h4>Relatório Mês: ".$mesNome."</h4>";
                            ?>
                        </td>
                    </tr>
                    <tr style="font-weight: bold;background-color: #ccc;">
                        <th width="30px"></th>
                        <th width="25px">Dia</th>
                        <th width="25px">Dia da semana</th>
                        <th width="25px">Horas Trabalhadas</th>
                        <th width="350px">Descrição das atividades</th>
                        <th width="25px">Status Validação</th>
                        <th width="25px">Sit.Pagamento</th>
                    </tr>                     
                <?php
                }
                ?>
                <tr id="<?php echo $objL->getCodRegAtividade() ?>">
                    <td>
                    <?php
                    $resultado = GerenciarRegistroAtividadeUteisAC::tratarDadosAvaliacao($objL);

                    // Acessando as variáveis retornadas
                    $classeStatus = $resultado['classeStatus'];
                    $colorText    = $resultado['colorText'];
                    $txtBotao     = $resultado['txtBotao'];
                    $txtStatusPagamento = $resultado['txtStatusPagamento'];
                    $avaliacaoLiberada  = $resultado['avaliacaoLiberada'];
                    $liberarBtnEditar  = $resultado['liberarBtnEditar'];

                    $liberarBtnEditar=false;
                    ?>
                    <?php if($liberarBtnEditar == true){ ?>
                        <img class="editar" 
                             src="<?php echo WB_PATH ?>/resources/image/alterar.gif" alt="Editar Registro Atividade: <?php echo $objL->getCodRegAtividade() ?>" border="0">
                        <img class="excluir" 
                             src="<?php echo WB_PATH ?>/resources/image/fig-excluir.gif" alt="Excluir Registro Atividade: <?php echo $objL->getCodRegAtividade() ?>" border="0">        
                    <?php }else{
                        // echo '<img src="'.WB_PATH.'/resources/image/fig-visualizar.gif" alt="Visualizar Registro Atividade: '.$objL->getCodRegAtividade().'" border="0">';
                        echo "Registro avaliado.";
                    }
                    ?>                
                    </td>
                    <?php 
                    // Remove da lista de dias não lançados (se existir)
                    $dia = $objL->getDia();
                    if(($key = array_search($dia, $arrayDiasNaoLançados)) !== false) {
                        unset($arrayDiasNaoLançados[$key]);
                    }                    
                    echo "<td>" . formatDateView($objL->getDia()) . "</td>";
                    echo "<td>{$objL->getDiaSemana()}</td>";
                    echo "<td>";
                        echo converterMinutosParaHoras($objL->getHorasTrabalhadas());
                        if($objL->getHorasTrabalhadas()<360){
                            echo "<br><font color='red'>(Menos de 6 horas)</font>";
                        }
                    echo "</td>";
                    echo "<td>{$objL->getDescricao()}";
                            echo '<br>Cadastrado em: '.formatDateView($objL->getDataInclusao());
                            echo ($objL->getAvaliacaoData()? '<br>Avaliado em: '.formatDateView($objL->getAvaliacaoData()) : '');
                    echo "</td>";
                    echo "<td >";
                        if ($objL->getAvaliacaoStatus() == 'Validado'){
                            echo "<span style='color: ".$colorText.";'>";
                            echo "<br>".$objL->getAvaliacaoStatus()."</span>";
                        } else {
                            echo "<br>Aguardando Validação";
                        }
                    echo "</td>";
                    echo "<td>".$txtStatusPagamento."</td>";
                echo "</tr>";

                // Acumula as horas realizadas do mês atual
                $totalHorasPorMes += (float)$objL->getHorasTrabalhadas();
            }
            // Exibe o total de horas do último mês após o loop
            if ($mesAtual != 0) {
                $horas = floor($totalHorasPorMes / 60);
                $minutos = $totalHorasPorMes % 60;
                echo "<tr><td colspan='7'>
                    <strong>Total de Horas realizadas para o Mês de $mesNome: " . $horas . " horas e " . $minutos . " minutos</strong><BR>";
                    // Após o loop, exibe os dias que não estavam em $lista
                    foreach ($arrayDiasNaoLançados as $diaFaltante) {
                        echo "Dia ".formatDateView($diaFaltante)." NÃO lançado<br>";
                    }                
                echo "</td></tr>";
            }
            ?>
                <br>Total de Registro Atividades cadastrados: <?php echo  $ii ?>.
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

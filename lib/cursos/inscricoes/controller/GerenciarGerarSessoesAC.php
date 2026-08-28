<?php

class GerenciarGerarSessoesAC {

    function __construct() {
        FWA_exigirPermissao(enumSistema::SIS_GERENCIAR_ADM_GERAL);}

    /**
     * Gera preview das sessões baseado nas regras definidas
     * Retorna JSON com array de sessões
     */
    public function gerarPreview($fws_, $debug = false) {
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        try {
            $sessoes = $this->montarSessoes($fws_);
            echo json_encode([
                'statusMessage' => '1',
                'total' => count($sessoes),
                'sessoes' => $sessoes
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'statusMessage' => '0',
                'message' => $e->getMessage()
            ]);
        }
        exit();
    }

    /**
     * Importa sessões no banco
     */
    public function importarSessoes($fws_, $debug = false) {
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        try {
            $sessoes = $this->montarSessoes($fws_);
            if (empty($sessoes)) {
                throw new Exception('Nenhuma sessão para importar.');
            }

            $codEvento = intval($fws_['cod_evento']);
            if (!$codEvento) {
                throw new Exception('Evento não informado.');
            }

            $Conexao_db = FWA_Conexao("OpcTec");
            $inseridos = 0;

            foreach ($sessoes as $s) {
                $titulo = addslashes($s['titulo']);
                $tipo = addslashes($fws_['tipo'] ?: 'Agenda');
                $vagas = intval($fws_['vagas'] ?: 1);
                $diasAntecedencia = intval($fws_['dias_antecedencia'] ?: 5);

                // data_fim_inscricao = N dias antes da sessão
                $dataObj = new DateTime($s['data_iso']);
                $dataObj->modify("-{$diasAntecedencia} days");
                $dataFimInsc = $dataObj->format('Y-m-d');

                $sql = "INSERT INTO tab_opcoes_oficinas
                    (cod_evento, ordem, receber_inscricao, status_inscricao, status, tipo, titulo, numero_vagas, data_ini, data_fim_inscricao, data_inicio, data_inclusao)
                    VALUES
                    ({$codEvento}, 1, 'SIM', 'ATIVADA', 'ATIVADA', '{$tipo}', '{$titulo}', {$vagas}, '{$s['data_iso']}', '{$dataFimInsc}', '{$s['data_iso']}', NOW())";

                $Conexao_db->Execute($sql);
                $inseridos++;
            }

            echo json_encode([
                'statusMessage' => '1',
                'message' => "{$inseridos} sessões importadas com sucesso.",
                'total' => $inseridos
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'statusMessage' => '0',
                'message' => $e->getMessage()
            ]);
        }
        exit();
    }

    /**
     * Exclui (soft delete) todas as sessões do evento — marca como EXCLUIDA
     */
    public function excluirTodas($fws_, $debug = false) {
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        try {
            $codEvento = intval($fws_['cod_evento']);
            if (!$codEvento) {
                throw new Exception('Evento não informado.');
            }

            $Conexao_db = FWA_Conexao("OpcTec");
            $sqlCount = "SELECT COUNT(*) as total FROM tab_opcoes_oficinas WHERE cod_evento = {$codEvento} AND status != 'EXCLUIDA'";
            $rs = $Conexao_db->Execute($sqlCount);
            $total = $rs->fields['total'];

            $sql = "UPDATE tab_opcoes_oficinas SET status = 'EXCLUIDA' WHERE cod_evento = {$codEvento} AND status != 'EXCLUIDA'";
            $Conexao_db->Execute($sql);

            echo json_encode([
                'statusMessage' => '1',
                'message' => "{$total} atividades/sessões foram excluídas."
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'statusMessage' => '0',
                'message' => $e->getMessage()
            ]);
        }
        exit();
    }

    /**
     * Monta array de sessões baseado nas regras
     */
    private function montarSessoes($fws_) {
        $dataIni = $fws_['data_ini'];
        $dataFim = $fws_['data_fim'];

        if (empty($dataIni) || empty($dataFim)) {
            throw new Exception('Período não informado.');
        }

        $inicio = new DateTime($dataIni);
        $fim = new DateTime($dataFim);

        if ($inicio > $fim) {
            throw new Exception('Data inicial maior que data final.');
        }

        // Turnos por dia da semana (0=domingo, 1=segunda, ..., 6=sábado)
        $turnosPorDia = [];
        for ($d = 0; $d <= 6; $d++) {
            $turnosPorDia[$d] = [];
            if (isset($fws_["dia_{$d}_manha"]) && $fws_["dia_{$d}_manha"] == '1') {
                $hIni = $fws_["dia_{$d}_manha_ini"] ?: '10:00';
                $hFim = $fws_["dia_{$d}_manha_fim"] ?: '12:00';
                $turnosPorDia[$d][] = ['nome' => 'Manhã', 'ini' => $hIni, 'fim' => $hFim];
            }
            if (isset($fws_["dia_{$d}_tarde"]) && $fws_["dia_{$d}_tarde"] == '1') {
                $hIni = $fws_["dia_{$d}_tarde_ini"] ?: '14:00';
                $hFim = $fws_["dia_{$d}_tarde_fim"] ?: '16:00';
                $turnosPorDia[$d][] = ['nome' => 'Tarde', 'ini' => $hIni, 'fim' => $hFim];
            }
            if (isset($fws_["dia_{$d}_noite"]) && $fws_["dia_{$d}_noite"] == '1') {
                $hIni = $fws_["dia_{$d}_noite_ini"] ?: '19:00';
                $hFim = $fws_["dia_{$d}_noite_fim"] ?: '21:00';
                $turnosPorDia[$d][] = ['nome' => 'Noite', 'ini' => $hIni, 'fim' => $hFim];
            }
        }

        // Feriados/bloqueios
        $bloqueios = [];
        if (!empty($fws_['bloqueios'])) {
            $linhas = preg_split('/[\r\n,;]+/', $fws_['bloqueios']);
            foreach ($linhas as $l) {
                $l = trim($l);
                if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})/', $l, $m)) {
                    $bloqueios[] = "{$m[3]}-{$m[2]}-{$m[1]}";
                } elseif (preg_match('/^(\d{4})-(\d{2})-(\d{2})/', $l)) {
                    $bloqueios[] = $l;
                }
            }
        }

        $diasSemana = [
            0 => 'Domingo', 1 => 'Segunda-feira', 2 => 'Terça-feira',
            3 => 'Quarta-feira', 4 => 'Quinta-feira', 5 => 'Sexta-feira', 6 => 'Sábado'
        ];

        $sessoes = [];
        $current = clone $inicio;
        while ($current <= $fim) {
            $dataIso = $current->format('Y-m-d');
            $diaSemana = intval($current->format('w')); // 0=domingo

            // Verificar bloqueio
            if (in_array($dataIso, $bloqueios)) {
                $current->modify('+1 day');
                continue;
            }

            $turnos = $turnosPorDia[$diaSemana] ?? [];
            foreach ($turnos as $turno) {
                $dataFormatada = $current->format('d/m/Y');
                $nomeDia = $diasSemana[$diaSemana];
                $titulo = "{$dataFormatada} - {$nomeDia} ({$turno['ini']} as  {$turno['fim']})";

                $sessoes[] = [
                    'data_iso' => $dataIso,
                    'data_formatada' => $dataFormatada,
                    'dia_semana' => $nomeDia,
                    'turno' => $turno['nome'],
                    'horario_ini' => $turno['ini'],
                    'horario_fim' => $turno['fim'],
                    'titulo' => $titulo
                ];
            }

            $current->modify('+1 day');
        }

        return $sessoes;
    }
}

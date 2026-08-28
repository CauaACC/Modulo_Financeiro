<?php

/**
 * Folha de ponto do Curso EAD.
 * Rota: CommandAC -> c=GerenciarCursoPonto & m=registrar (heartbeat do aluno na area do curso).
 * O relatorio pro coordenador fica no RelatorioCursoItem (aba Registro de Ponto).
 */
class GerenciarCursoPontoAC {

    function __construct() { }

    /** Heartbeat: o aluno esta com a area do curso aberta -> estende/abre bloco de ponto. */
    public function registrar($fws_, $debug = false) {
        header('Content-Type: application/json; charset=UTF-8');
        $ok = false;
        if (!empty($_SESSION['_cod_inscrito'])) {
            $dao = new PontoSessaoDAO();
            $ok = $dao->heartbeat(
                (int) COD_EVENTO,
                (int) $_SESSION['_cod_inscrito'],
                isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null,
                isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null,
                150, $debug
            );
        }
        echo json_encode(array('statusMessage' => $ok ? true : false));
    }

    // ===== Relatorio (coordenador) =====
    private function fmtDur($seg) {
        $seg = (int) $seg;
        if ($seg < 60) return $seg . 's';
        $h = intdiv($seg, 3600); $m = intdiv($seg % 3600, 60);
        if ($h > 0) return $h . 'h' . str_pad($m, 2, '0', STR_PAD_LEFT);
        return $m . 'min';
    }
    private function fmtHora($dt) { return $dt ? substr($dt, 11, 5) : '--:--'; }
    private function fmtData($d) {
        $ts = strtotime($d . ' 00:00:00');
        $sem = array('Dom','Seg','Ter','Qua','Qui','Sex','Sáb');
        return $sem[(int) date('w', $ts)] . ', ' . date('d/m/Y', $ts);
    }
    private function iconAtiv($natureza, $tipo) {
        if ($natureza === 'TAREFA') return '<i class="fa fa-upload" style="color:#2d6a4f;"></i> Enviou tarefa';
        if ($tipo === 'PDF')  return '<i class="fa fa-file-pdf-o" style="color:#b5533a;"></i> Abriu material (PDF)';
        if ($tipo === 'LINK') return '<i class="fa fa-external-link" style="color:#3a6db5;"></i> Abriu link';
        if ($tipo === 'FORMULARIO') return '<i class="fa fa-list-alt" style="color:#7a5cb5;"></i> Formulário';
        return '<i class="fa fa-play-circle" style="color:#2d6a4f;"></i> Assistindo';
    }

    /** Folha de ponto detalhada de um aluno: por dia -> acesso (de-as) + timeline do que fez. */
    public function carregarFolha($fws_, $debug = false) {
        if (!FWA_ehProfessorEAD()) { echo '<div style="padding:20px; color:#a00;">Sem permissão.</div>'; return; }
        $codInscrito = isset($fws_['codInscrito']) ? (int) $fws_['codInscrito'] : 0;
        if (!$codInscrito) { echo '<div style="padding:24px; text-align:center; color:#999;">Selecione um aluno acima.</div>'; return; }

        $dao = new PontoSessaoDAO();
        $sessoes = $dao->sessoesDoAluno(COD_EVENTO, $codInscrito, $debug);
        $ativs   = $dao->atividadesDoAluno(COD_EVENTO, $codInscrito, $debug);

        $dias = array();
        $novoDia = function () { return array('blocos' => array(), 'ativ' => array(), 'total' => 0, 'min' => null, 'max' => null); };
        foreach ($sessoes as $s) {
            $d = $s['dia'];
            if (!isset($dias[$d])) $dias[$d] = $novoDia();
            $dias[$d]['blocos'][] = $s;
            $dias[$d]['total'] += (int) $s['segundos'];
            if ($dias[$d]['min'] === null || $s['inicio'] < $dias[$d]['min']) $dias[$d]['min'] = $s['inicio'];
            if ($dias[$d]['max'] === null || $s['fim']   > $dias[$d]['max']) $dias[$d]['max'] = $s['fim'];
        }
        foreach ($ativs as $a) {
            $d = substr($a['inicio'], 0, 10);
            if (!isset($dias[$d])) $dias[$d] = $novoDia();
            $dias[$d]['ativ'][] = $a;
            if ($dias[$d]['min'] === null || $a['inicio'] < $dias[$d]['min']) $dias[$d]['min'] = $a['inicio'];
            if ($dias[$d]['max'] === null || $a['fim']   > $dias[$d]['max']) $dias[$d]['max'] = $a['fim'];
        }
        if (empty($dias)) { echo '<div style="padding:24px; text-align:center; color:#999;">Sem registro de ponto para este aluno ainda.</div>'; return; }
        krsort($dias);

        // ===== Compromisso x realizado (regra derivada: oficina + calendario, sem duplicar) =====
        $ofs = $dao->oficinasDoAluno(COD_EVENTO, $codInscrito, $debug);
        $cargaH = 0; $hIni = null; $hFim = null; $dIni = null; $dFim = null; $nomes = array();
        foreach ($ofs as $o) {
            $c = trim((string) $o['carga_horaria']);
            if ($c !== '' && preg_match('/([0-9]+([.,][0-9]+)?)/', $c, $m)) $cargaH += (float) str_replace(',', '.', $m[1]);
            $hi = trim((string) $o['horario_ini']); $hf = trim((string) $o['horario_fim']);
            if ($hi !== '' && ($hIni === null || $hi < $hIni)) $hIni = $hi;
            if ($hf !== '' && ($hFim === null || $hf > $hFim)) $hFim = $hf;
            if (!empty($o['data_ini']) && ($dIni === null || $o['data_ini'] < $dIni)) $dIni = $o['data_ini'];
            if (!empty($o['data_fim']) && ($dFim === null || $o['data_fim'] > $dFim)) $dFim = $o['data_fim'];
            if (!empty($o['titulo'])) $nomes[] = $o['titulo'];
        }
        if (($cargaH > 0) || ($dIni && $dFim)) {
            $realSeg = 0; $diasAtiv = 0;
            foreach ($dias as $d => $info2) {
                if ($dIni && $d < substr($dIni, 0, 10)) continue;
                if ($dFim && $d > substr($dFim, 0, 10)) continue;
                $realSeg += (int) $info2['total'];
                if (!empty($info2['ativ']) || !empty($info2['blocos'])) $diasAtiv++;
            }
            $diasUteis = ($dIni && $dFim) ? $dao->diasUteis($dIni, $dFim, $debug) : 0;
            $pctCarga = ($cargaH > 0) ? min(100, (int) round(100 * $realSeg / ($cargaH * 3600))) : 0;
            $pctDias  = ($diasUteis > 0) ? min(100, (int) round(100 * $diasAtiv / $diasUteis)) : 0;
            $bar = function ($pct) { return '<div style="height:8px; background:#e9ecef; border-radius:4px; overflow:hidden;"><div style="height:100%; width:' . (int) $pct . '%; background:linear-gradient(90deg,#2d6a4f,#4caf50);"></div></div>'; };

            echo '<div style="padding:14px 16px; background:#fff; border-bottom:2px solid #e0eae4;">';
            echo '<div style="font-size:11px; text-transform:uppercase; letter-spacing:.05em; color:#888; margin-bottom:8px;">Compromisso'
               . (count($nomes) ? ' — ' . htmlspecialchars(implode(', ', array_unique($nomes))) : '') . '</div>';
            echo '<div style="display:flex; flex-wrap:wrap; gap:24px; align-items:flex-end;">';
            if ($cargaH > 0) {
                $cargaTxt = rtrim(rtrim(number_format($cargaH, 1, ',', '.'), '0'), ',');
                echo '<div style="min-width:230px; flex:1;"><div style="font-size:12px; color:#4b5563; margin-bottom:3px;">'
                   . 'Carga horária: <strong>' . $this->fmtDur($realSeg) . '</strong> de ' . $cargaTxt . 'h &nbsp;(' . $pctCarga . '%)</div>' . $bar($pctCarga) . '</div>';
            }
            if ($diasUteis > 0) {
                echo '<div style="min-width:230px; flex:1;"><div style="font-size:12px; color:#4b5563; margin-bottom:3px;">'
                   . 'Frequência: <strong>' . $diasAtiv . '</strong> de ' . $diasUteis . ' dias úteis &nbsp;(' . $pctDias . '%)</div>' . $bar($pctDias) . '</div>';
            }
            $meta = array();
            if ($hIni && $hFim) $meta[] = 'Horário ' . substr($hIni, 0, 5) . '–' . substr($hFim, 0, 5);
            if ($dIni && $dFim) $meta[] = 'Período ' . date('d/m/Y', strtotime($dIni)) . ' a ' . date('d/m/Y', strtotime($dFim));
            if ($meta) echo '<div style="font-size:11px; color:#888;">' . htmlspecialchars(implode(' · ', $meta)) . '</div>';
            echo '</div></div>';
        } else {
            echo '<div style="padding:12px 16px; background:#fff8e6; border-bottom:1px solid #f0e2b8; font-size:12px; color:#856404;">'
               . '<i class="fa fa-info-circle"></i> Compromisso não definido — preencha carga horária / horário / período na <strong>oficina</strong> do aluno para comparar cumprido × faltante.</div>';
        }

        $totGeral = 0; foreach ($dias as $dd) $totGeral += $dd['total'];
        echo '<div style="padding:12px 16px; background:#f0f7f3; border-bottom:1px solid #e0eae4; font-size:13px;">'
           . '<strong>Total no período:</strong> ' . $this->fmtDur($totGeral)
           . ' &nbsp;·&nbsp; ' . count($dias) . ' dia(s) com atividade</div>';

        foreach ($dias as $d => $info) {
            echo '<div style="border-bottom:1px solid #eee; padding:12px 16px;">';
            echo '<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">';
            echo '<strong style="font-size:14px; color:#1f2937;">' . htmlspecialchars($this->fmtData($d)) . '</strong>';
            if ($info['total'] > 0) {
                echo '<span style="font-size:12px; color:#4b5563;">Logado <strong>' . $this->fmtDur($info['total']) . '</strong>'
                   . ' &nbsp;·&nbsp; das ' . $this->fmtHora($info['min']) . ' às ' . $this->fmtHora($info['max']) . '</span>';
            } else {
                echo '<span style="font-size:12px; color:#4b5563;">Atividade das ' . $this->fmtHora($info['min']) . ' às ' . $this->fmtHora($info['max']) . '</span>';
            }
            echo '</div>';

            // blocos de acesso (login) — so quando ha heartbeat gravado
            if (!empty($info['blocos'])) {
                echo '<div style="font-size:11px; color:#888; margin:2px 0 4px;">Acesso à área:</div>';
                echo '<div style="display:flex; flex-wrap:wrap; gap:6px; margin-bottom:8px;">';
                foreach ($info['blocos'] as $b) {
                    echo '<span style="font-size:11px; background:#eef2f7; border:1px solid #dde4ec; border-radius:12px; padding:2px 9px;">'
                       . $this->fmtHora($b['inicio']) . '–' . $this->fmtHora($b['fim'])
                       . ' <span style="color:#888;">(' . $this->fmtDur($b['segundos']) . ')</span></span>';
                }
                echo '</div>';
            }

            // timeline do que fez
            if (!empty($info['ativ'])) {
                usort($info['ativ'], function ($x, $y) { return strcmp($x['inicio'], $y['inicio']); });
                echo '<table class="tbl-fin" style="margin-top:2px;"><tbody>';
                foreach ($info['ativ'] as $a) {
                    $hora = $this->fmtHora($a['inicio']);
                    $fim  = ($a['natureza'] === 'TAREFA' || $a['inicio'] === $a['fim']) ? '' : '–' . $this->fmtHora($a['fim']);
                    $dur  = ((int) $a['segundos'] > 0) ? '<span style="color:#888; font-size:11px;">(' . $this->fmtDur($a['segundos']) . ')</span>' : '';
                    echo '<tr>'
                       . '<td style="width:110px; white-space:nowrap; font-variant-numeric:tabular-nums;">' . $hora . $fim . '</td>'
                       . '<td style="width:150px;">' . $this->iconAtiv($a['natureza'], $a['tipo_item']) . '</td>'
                       . '<td>' . htmlspecialchars($a['titulo']) . ' ' . $dur . '</td>'
                       . '</tr>';
                }
                echo '</tbody></table>';
            } else {
                echo '<div style="font-size:12px; color:#999; font-style:italic;">Esteve na área, sem atividade de conteúdo registrada.</div>';
            }
            echo '</div>';
        }
    }
}

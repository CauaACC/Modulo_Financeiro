<?php
class GerenciarCalendarioBaseAC
{
    function __construct()
    {
        /* Antes isto respondia JSON + exit() SEMPRE. Como a view HTML
           (GerenciarCalendarioBase.php) instancia este controller, quem nao tinha
           permissao recebia a pagina cortada no meio -- a "tela branca".
           Agora: JSON so quando a chamada e AJAX; no acesso normal usa o mesmo
           caminho do GerenciarFeriadoAC, que mostra a mensagem de acesso negado. */
        $ehAjax = (isset($_SERVER['HTTP_X_REQUESTED_WITH'])
                   && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
                  || (isset($_SERVER['HTTP_ACCEPT'])
                      && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

        /* Mesmo gate do Gerenciar Feriado: o calendario e insumo das sessoes de
           aula, quem monta e o COORDENADOR. */
        $permitidos = array(
            enumSistema::SIS_PEDAGOGICO,            // coordenador
            enumSistema::SIS_GERENCIAR_ADM_GERAL,
        );

        if ($ehAjax) {
            $pode = false;
            foreach ($permitidos as $p) {
                if (FWA_Autenticacao($_SESSION['_cod_usuario'], $p, 5, false, false)) {
                    $pode = true;
                    break;
                }
            }
            if (!$pode) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(403);
                echo json_encode([
                    'statusMessage' => false,
                    'message'       => 'Sem permissão de acesso ao Calendário Base.'
                ]);
                exit();
            }
        } else {
            FWA_exigirPermissao($permitidos);
        }
        $this->codEvento = COD_EVENTO;
        $objConf = new GerenciarConfiguracaoEventoAC();
        $objConf->carregarConfiguracao($this);
    }

    public function carregarObjCalendarioBase($fws_, $debug = false)
    {
        $obj = new CalendarioBase();
        if ($fws_['id']) {
            $obj->setId($fws_['id']);
            $obj->getCalendarioBaseById($debug);
        }
        return $obj;
    }

    public function salvarCalendarioBase($fws_, $debug = false)
    {
        $ok = false;
        $e = null;
        try {
            if ($fws_['updateCalendarioBase'] == 0) {
                $fws_['id'] = NULL;
            }
            $obj = $this->carregarObjCalendarioBase($fws_);
            $obj->setAll($fws_, true);
            $obj->salvar();
            $ok = true;
        } catch (OKException $ex) {
            $ok = true;
        } catch (Exception $ex) { }

        echo '{
            "statusMessage":"' . $ok . '",
            "message":"' . ($ex ? $ex->getMessage() : '') . '",
            "id":"' . ($obj ? $obj->getId() : '') . '"
        }';
    }

    public function editarCalendarioBase($fws_, $debug = false)
    {
        $ok = 0;
        $obj = $this->carregarObjCalendarioBase($fws_, $debug);
        if ($obj->getId()) {
            $ok = 1;
        }

        echo json_encode($obj->getAllJson($ok));
        exit();
    }

    public function excluirCalendarioBase($fws_, $debug = false)
    {
        $ok = false;
        try {
            $obj = $this->carregarObjCalendarioBase($fws_);
            $obj->excluir();
            $ok = true;
        } catch (OKException $e) {
            $ok = true;
        } catch (Exception $e) {
            $ok = false;
        }

        echo '{
            "statusMessage":"' . $ok . '",
            "message":"' . ($e ? $e->getMessage() : '') . '",
            "id":"' . $fws_['id'] . '"
        }';
        exit();
    }

    public function gerarCalendario($fws_, $debug = false)
    {
        $ok = false;
        try {
            $ano = intval($fws_['ano']);
            $obj = new CalendarioBase();
            $obj->gerarCalendario($ano, $debug);
            $ok = true;
        } catch (OKException $ex) {
            $ok = true;
        } catch (Exception $ex) { }

        echo json_encode(array(
            'statusMessage' => $ok,
            'message' => ($ex ? $ex->getMessage() : '')
        ));
        exit();
    }

    public function carregarTabelaCalendarioBase($fws_, $debug = false)
    {
        $obj = new CalendarioBase();
        $obj->setAno($fws_['ano'] ?? '');
        $lista = [];
        try {
            $obj->carregarListaCalendarioBaseByIdEvento($debug);
            $lista = $obj->{"getListaCalendarioBases"}();
        } catch (Exception $e) {}

        if (empty($lista)) {
            echo '<div style="padding:40px;text-align:center;color:#999;font-style:italic">Nenhum registro encontrado para o filtro selecionado.</div>';
            return;
        }

        // Agrupa por (ano, mes) e indexa dias por número do dia
        $porMes = [];
        foreach ($lista as $objL) {
            $ano = (int) $objL->getAno();
            $mes = (int) $objL->getMes();
            $diaNum = (int) substr($objL->getDiaCalendario(), 8, 2);
            $porMes[$ano][$mes][$diaNum] = $objL;
        }
        ksort($porMes);

        $nomeMeses = [
            1=>'Janeiro', 2=>'Fevereiro', 3=>'Março', 4=>'Abril', 5=>'Maio', 6=>'Junho',
            7=>'Julho', 8=>'Agosto', 9=>'Setembro', 10=>'Outubro', 11=>'Novembro', 12=>'Dezembro'
        ];
        $diasSemanaCab = ['D','S','T','Q','Q','S','S']; // dom seg ter qua qui sex sab
        $coresTipo = [
            'Util'              => ['bg'=>'#e8f5e9', 'fg'=>'#1b5e20', 'border'=>'#c8e6c9'],
            'Fim de Semana'     => ['bg'=>'#eceff1', 'fg'=>'#455a64', 'border'=>'#cfd8dc'],
            'Feriado Nacional'  => ['bg'=>'#ffebee', 'fg'=>'#b71c1c', 'border'=>'#ffcdd2'],
            'Ponto Facultativo' => ['bg'=>'#fff3e0', 'fg'=>'#e65100', 'border'=>'#ffe0b2'],
            'Recesso'           => ['bg'=>'#fff8e1', 'fg'=>'#f57f17', 'border'=>'#ffecb3'],
        ];
        $padraoCor = ['bg'=>'#fafafa', 'fg'=>'#999', 'border'=>'#eee'];

        // Legenda
        echo '<div style="display:flex;flex-wrap:wrap;gap:14px;padding:8px 14px 14px;font-size:12px">';
        foreach ($coresTipo as $tipoNome => $c) {
            echo '<span style="display:inline-flex;align-items:center;gap:6px">';
            echo '<span style="display:inline-block;width:14px;height:14px;background:'.$c['bg'].';border:1px solid '.$c['border'].';border-radius:3px"></span>';
            echo '<span style="color:#555">'.htmlspecialchars($tipoNome).'</span>';
            echo '</span>';
        }
        echo '</div>';

        echo '<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:14px;padding:0 14px 14px">';
        foreach ($porMes as $ano => $meses) {
            ksort($meses);
            foreach ($meses as $mes => $diasIndexados) {
                $diasNoMes = (int) date('t', strtotime(sprintf('%04d-%02d-01', $ano, $mes)));
                $diaSemanaInicio = (int) date('w', strtotime(sprintf('%04d-%02d-01', $ano, $mes))); // 0=dom

                echo '<div style="border:1px solid #e0e0e0;border-radius:8px;overflow:hidden;background:#fff">';
                echo '<div style="background:#37474f;color:#fff;padding:6px 10px;font-weight:600;font-size:13px">';
                echo htmlspecialchars($nomeMeses[$mes].' '.$ano);
                echo '</div>';
                echo '<div style="display:grid;grid-template-columns:repeat(7,1fr);gap:1px;background:#eee">';
                foreach ($diasSemanaCab as $i => $diaCab) {
                    $isFds = ($i === 0 || $i === 6);
                    echo '<div style="background:'.($isFds?'#e0e0e0':'#f5f5f5').';color:#666;font-size:10px;font-weight:700;text-align:center;padding:4px 0">'.$diaCab.'</div>';
                }
                // Espaços vazios antes do dia 1
                for ($i = 0; $i < $diaSemanaInicio; $i++) {
                    echo '<div style="background:#fff;min-height:30px"></div>';
                }
                for ($d = 1; $d <= $diasNoMes; $d++) {
                    if (isset($diasIndexados[$d])) {
                        $objL = $diasIndexados[$d];
                        $tipo = $objL->getTipoDia() ?: '';
                        $c = $coresTipo[$tipo] ?? $padraoCor;
                        $tooltip = htmlspecialchars($tipo);
                        if ($objL->getDescricao()) $tooltip .= ' — '.htmlspecialchars($objL->getDescricao());
                        echo '<div class="cb-dia editar" data-id="'.$objL->getId().'" data-data="'.htmlspecialchars($objL->getDiaCalendario()).'" title="'.$tooltip.' (clique para cadastrar feriado)" '
                            .'style="background:'.$c['bg'].';color:'.$c['fg'].';font-size:12px;font-weight:600;text-align:center;padding:6px 0;cursor:pointer;min-height:30px">';
                        echo $d;
                        echo '</div>';
                    } else {
                        // Sem registro pra esse dia (não deveria acontecer se calendário foi gerado)
                        echo '<div style="background:#fff;color:#bbb;font-size:12px;text-align:center;padding:6px 0;min-height:30px">'.$d.'</div>';
                    }
                }
                echo '</div></div>';
            }
        }
        echo '</div>';
    }

    public function carregarAnosDisponiveis($fws_, $debug = false)
    {
        $obj = new CalendarioBase();
        $anos = $obj->getAnosDisponiveis($debug);
        echo json_encode(['anos' => $anos]);
        exit();
    }
}

<?php
class GerenciarFeriadoAC
{
    function __construct()
    {
        /* Calendario e feriado sao insumo das sessoes de aula (GerenciarGerarSessoesAC):
           quem monta isso e o COORDENADOR, nao so o admin do sistema. */
        FWA_exigirPermissao(array(
            enumSistema::SIS_PEDAGOGICO,            // coordenador
            enumSistema::SIS_GERENCIAR_ADM_GERAL,
        ));
        $this->EventoId = COD_EVENTO;
        $objConf = new GerenciarConfiguracaoEventoAC();
        $objConf->carregarConfiguracao($this);
    }

    public function carregarObjFeriado($fws_, $debug = false)
    {
        $obj = new Feriado();
        if ($fws_['id']) {
            $obj->setId($fws_['id']);
            $obj->getFeriadoById($debug);
        }
        return $obj;
    }

    public function salvarFeriado($fws_, $debug = false)
    {
        $ok = false;
        $e = null;
        try {
            if ($fws_['updateId'] == 0) {
                $fws_['id'] = NULL;
            }
            $obj = $this->carregarObjFeriado($fws_);
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

    public function editarFeriado($fws_, $debug = false)
    {
        $ok = 0;
        $obj = $this->carregarObjFeriado($fws_, $debug);
        if ($obj->getId()) {
            $ok = 1;
        }

        // echo json_encode($obj->getAllJson($ok));
        echo '{
            "statusMessage":"' . $ok . '",
            "message":"' . ($ex ? $ex->getMessage() : '') . '",
            "id":"' . ($obj ? $obj->getId() : '') . '",
            "dia_calendario":"' . ($obj ? $obj->getDiaCalendario() : '') . '",
            "tipo_dia":"' . ($obj ? $obj->getTipoDia() : '') . '",
            "descricao":"' . ($obj ? $obj->getDescricao() : '') . '"
        }';        
        exit();
    }

    public function excluirFeriado($fws_, $debug = false)
    {
        $ok = false;
        try {
            $obj = $this->carregarObjFeriado($fws_);
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

    public function carregarTabelaFeriado($fws_, $debug = false)
    {
        $anoFiltro = isset($fws_['ano']) && $fws_['ano'] !== '' ? intval($fws_['ano']) : null;
        $obj = new Feriado();
        $lista = [];
        try {
            $obj->carregarListaFeriadoByIdEvento($debug);
            $lista = $obj->{"getListaFeriados"}();
        } catch (Exception $e) {}

        if (empty($lista)) {
            echo '<div style="padding:40px;text-align:center;color:#999;font-style:italic">Nenhum feriado encontrado.</div>';
            return;
        }

        // Filtra por ano e agrupa por ano -> mes
        $porAnoMes = [];
        foreach ($lista as $objL) {
            $diaCal = $objL->getDiaCalendario();
            $ano  = (int) substr($diaCal, 0, 4);
            $mes  = (int) substr($diaCal, 5, 2);
            if ($anoFiltro !== null && $ano !== $anoFiltro) continue;
            $porAnoMes[$ano][$mes][] = $objL;
        }
        ksort($porAnoMes);

        if (empty($porAnoMes)) {
            echo '<div style="padding:40px;text-align:center;color:#999;font-style:italic">Nenhum feriado encontrado para o ano selecionado.</div>';
            return;
        }

        $nomeMeses = [
            1=>'Janeiro', 2=>'Fevereiro', 3=>'Março', 4=>'Abril', 5=>'Maio', 6=>'Junho',
            7=>'Julho', 8=>'Agosto', 9=>'Setembro', 10=>'Outubro', 11=>'Novembro', 12=>'Dezembro'
        ];
        $coresTipo = [
            'Feriado Nacional'  => '#e57373',
            'Feriado Estadual'  => '#ba68c8',
            'Feriado Municipal' => '#81c784',
            'Feriado Evento'    => '#64b5f6',
            'Recesso'           => '#ffb74d',
            'Ponto Facultativo' => '#ffa726',
        ];

        echo '<div style="padding:0 14px 14px">';
        foreach ($porAnoMes as $ano => $meses) {
            ksort($meses);
            $totalAno = 0; foreach ($meses as $m) $totalAno += count($m);
            echo '<div style="margin-top:14px;border:1px solid #e0e0e0;border-radius:8px;overflow:hidden;background:#fff">';
            echo '<div style="background:#37474f;color:#fff;padding:8px 14px;font-weight:600;font-size:14px;display:flex;justify-content:space-between;align-items:center">';
            echo '<span><i class="fa fa-calendar"></i> '.$ano.'</span>';
            echo '<span style="font-size:11px;opacity:0.85;font-weight:400">'.$totalAno.' registro(s)</span>';
            echo '</div>';
            foreach ($meses as $mes => $itens) {
                echo '<div style="padding:6px 14px;background:#f5f7f9;font-size:12px;font-weight:600;color:#455a64;border-top:1px solid #eee">';
                echo $nomeMeses[$mes];
                echo '</div>';
                echo '<div>';
                foreach ($itens as $objL) {
                    $isGlobal = ($objL->getOrigem() === 'GLOBAL');
                    $tipo = $objL->getTipoDia() ?: '';
                    $corTipo = $coresTipo[$tipo] ?? '#607d8b';
                    $diaSem = '';
                    $ts = strtotime($objL->getDiaCalendario());
                    if ($ts !== false) {
                        $diasSem = ['Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sábado'];
                        $diaSem = $diasSem[(int) date('w', $ts)];
                    }
                    echo '<div id="'.$objL->getId().'" class="cb-feriado-row" '
                        .'style="display:flex;align-items:center;gap:10px;padding:8px 14px;border-top:1px solid #f0f0f0;'.($isGlobal?'background:#fafafa':'').'">';

                    // Bloco data
                    echo '<div style="min-width:90px">';
                    echo '<div style="font-size:14px;font-weight:700;color:#263238">'.formatDateView($objL->getDiaCalendario()).'</div>';
                    if ($diaSem) echo '<div style="font-size:11px;color:#90a4ae">'.$diaSem.'</div>';
                    echo '</div>';

                    // Tipo (badge)
                    echo '<div style="min-width:160px">';
                    if ($tipo) {
                        echo '<span style="display:inline-block;background:'.$corTipo.';color:#fff;font-size:11px;font-weight:600;padding:3px 9px;border-radius:11px">'.htmlspecialchars($tipo).'</span>';
                    }
                    echo '</div>';

                    // Descrição (com badge GLOBAL na frente quando aplicavel)
                    echo '<div style="flex:1;color:#37474f;font-size:13px">';
                    if ($isGlobal) {
                        echo '<span style="background:#37474f;color:#fff;font-size:9px;padding:2px 6px;border-radius:3px;margin-right:8px;letter-spacing:0.5px;vertical-align:middle">GLOBAL</span>';
                    }
                    echo htmlspecialchars($objL->getDescricao() ?: '—');
                    echo '</div>';

                    // Ações
                    echo '<div style="min-width:80px;text-align:right">';
                    if (!$isGlobal) {
                        echo '<span class="btn-acao editar" title="Editar" style="cursor:pointer;margin-right:6px"><i class="fa fa-pencil"></i></span>';
                        echo '<span class="btn-acao btn-acao-excluir excluir" title="Excluir" style="cursor:pointer"><i class="fa fa-trash"></i></span>';
                    } else {
                        echo '<span style="color:#aaa;font-size:11px;font-style:italic" title="Feriado nacional, gerenciado em Calendário Base"><i class="fa fa-lock"></i></span>';
                    }
                    echo '</div>';

                    echo '</div>';
                }
                echo '</div>';
            }
            echo '</div>';
        }
        echo '</div>';
    }

    public function carregarAnosDisponiveisFeriado($fws_, $debug = false)
    {
        $obj = new Feriado();
        $anos = [];
        try {
            $obj->carregarListaFeriadoByIdEvento($debug);
            $lista = $obj->{"getListaFeriados"}();
            foreach ($lista as $objL) {
                $anos[(int) substr($objL->getDiaCalendario(), 0, 4)] = true;
            }
        } catch (Exception $e) {}
        krsort($anos);
        echo json_encode(['anos' => array_keys($anos)]);
        exit();
    }
}

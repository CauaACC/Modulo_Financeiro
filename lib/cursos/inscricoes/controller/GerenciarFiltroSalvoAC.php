<?php

class GerenciarFiltroSalvoAC {

    function __construct() {
        FWA_exigirPermissao(enumSistema::SIS_GERENCIAR_ADM_GERAL);
    }

    public function salvar($fws_, $debug = false) {
        $ok = false;
        try {
            $obj = new FiltroSalvo();
            $obj->setCodUsuario($_SESSION['_cod_usuario']);
            $obj->setCodEvento($fws_['cod_evento'] ? $fws_['cod_evento'] : COD_EVENTO);
            $obj->setNomFiltro(stripslashes($fws_['nom_filtro']));
            $obj->setDatFiltro(stripslashes($fws_['dat_filtro']));
            $obj->setStatus('ATIVADA');
            $obj->salvar($debug);
        } catch (OKException $ex) {
            $ok = true;
        } catch (Exception $ex) {
        }

        echo json_encode([
            'statusMessage' => $ok ? '1' : '0',
            'message' => $ex ? $ex->getMessage() : '',
            'codFiltroSalvo' => $obj ? $obj->getCodFiltroSalvo() : '',
            'nomFiltro' => $obj ? $obj->getNomFiltro() : ''
        ]);
        exit();
    }

    public function listar($fws_, $debug = false) {
        try {
            $obj = new FiltroSalvo();
            $obj->setCodUsuario($_SESSION['_cod_usuario']);
            $obj->setCodEvento($fws_['cod_evento'] ? $fws_['cod_evento'] : COD_EVENTO);
            $obj->listar($debug);

            $lista = $obj->getListaFiltrosSalvos();
            $resultado = [];
            if ($lista) {
                foreach ($lista as $item) {
                    $resultado[] = [
                        'codFiltroSalvo' => $item->getCodFiltroSalvo(),
                        'nomFiltro' => $item->getNomFiltro()
                    ];
                }
            }
            echo json_encode($resultado);
        } catch (Exception $ex) {
            echo json_encode([]);
        }
        exit();
    }

    public function carregar($fws_, $debug = false) {
        $ok = false;
        try {
            $obj = new FiltroSalvo();
            $obj->setCodFiltroSalvo($fws_['cod_filtro_salvo']);
            $obj->carregar($debug);
            $ok = true;
        } catch (Exception $ex) {
        }

        $datFiltro = $obj ? $obj->getDatFiltro() : '';
        // Limpa barras residuais de dados antigos salvos com escaping
        while (is_string($datFiltro) && (strpos($datFiltro, '\\"') !== false || strpos($datFiltro, '\\\\') !== false)) {
            $datFiltro = stripslashes($datFiltro);
        }
        // Decodifica o JSON para array — json_encode na resposta vai serializar corretamente
        $datFiltroArray = json_decode($datFiltro, true);
        echo json_encode([
            'statusMessage' => $ok ? '1' : '0',
            'message' => $ex ? $ex->getMessage() : '',
            'datFiltro' => $datFiltroArray !== null ? $datFiltroArray : $datFiltro
        ]);
        exit();
    }

    public function excluir($fws_, $debug = false) {
        $ok = false;
        try {
            $obj = new FiltroSalvo();
            $obj->setCodFiltroSalvo($fws_['cod_filtro_salvo']);
            $obj->excluir($debug);
        } catch (OKException $ex) {
            $ok = true;
        } catch (Exception $ex) {
        }

        echo json_encode([
            'statusMessage' => $ok ? '1' : '0',
            'message' => $ex ? $ex->getMessage() : ''
        ]);
        exit();
    }
}

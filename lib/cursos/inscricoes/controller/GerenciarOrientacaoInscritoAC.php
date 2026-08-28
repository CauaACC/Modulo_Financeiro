<?php
/**
 * Controller usado pela area do inscrito (PES) para consultar as orientacoes
 * que recebeu dos supervisores e marca-las como lidas.
 *
 * Autenticacao: exige inscrito logado ($_SESSION['_logado'] + _cod_inscrito).
 * Escopo: apenas orientacoes onde cod_agente == _cod_inscrito do evento corrente.
 */
class GerenciarOrientacaoInscritoAC {

    private $codInscrito = null;

    function __construct() {
        if (empty($_SESSION['_logado']) || empty($_SESSION['_cod_inscrito'])) {
            throw new Exception("Sessao expirada. Faca login novamente.");
        }
        $this->codInscrito = intval($_SESSION['_cod_inscrito']);
    }

    public function carregarMinhasOrientacoes($fws_, $debug = false) {
        try {
            $obj = new RegistroOrientacao();
            $lista = $obj->getOrientacoesRecebidas($this->codInscrito, $debug);
            $naoLidas = $obj->contarOrientacoesNaoLidas($this->codInscrito, $debug);
            echo json_encode(array(
                'statusMessage' => true,
                'lista' => $lista,
                'naoLidas' => $naoLidas,
                'total' => count($lista)
            ));
        } catch (Exception $e) {
            echo json_encode(array('statusMessage' => false, 'message' => $e->getMessage()));
        }
        exit();
    }

    public function marcarLida($fws_, $debug = false) {
        $ok = false; $msg = '';
        try {
            $codRegistroPes = isset($fws_['cod_registro_pes']) ? intval($fws_['cod_registro_pes']) : 0;
            $obj = new RegistroOrientacao();
            $obj->marcarOrientacaoLida($codRegistroPes, $this->codInscrito, $debug);
        } catch (OKException $e) {
            $ok = true; $msg = $e->getMessage();
        } catch (Exception $e) {
            $msg = $e->getMessage();
        }
        echo json_encode(array('statusMessage' => $ok, 'message' => $msg));
        exit();
    }

    public function contarNaoLidas($fws_, $debug = false) {
        try {
            $obj = new RegistroOrientacao();
            $totalPes = $obj->contarOrientacoesNaoLidas($this->codInscrito, $debug);
            $totalCrianca = $obj->contarOrientacoesCriancaNaoLidas($this->codInscrito, $debug);
            echo json_encode(array(
                'statusMessage' => true,
                'naoLidas' => intval($totalPes) + intval($totalCrianca),
                'naoLidasPes' => intval($totalPes),
                'naoLidasCrianca' => intval($totalCrianca)
            ));
        } catch (Exception $e) {
            echo json_encode(array('statusMessage' => false, 'message' => $e->getMessage()));
        }
        exit();
    }

    public function carregarMinhasOrientacoesCrianca($fws_, $debug = false) {
        try {
            $obj = new RegistroOrientacao();
            $lista = $obj->getOrientacoesCriancaRecebidas($this->codInscrito, $debug);
            $naoLidas = $obj->contarOrientacoesCriancaNaoLidas($this->codInscrito, $debug);
            echo json_encode(array(
                'statusMessage' => true,
                'lista' => $lista,
                'naoLidas' => $naoLidas,
                'total' => count($lista)
            ));
        } catch (Exception $e) {
            echo json_encode(array('statusMessage' => false, 'message' => $e->getMessage()));
        }
        exit();
    }

    public function marcarLidaCrianca($fws_, $debug = false) {
        $ok = false; $msg = '';
        try {
            $codRegistroCrianca = isset($fws_['cod_registro_crianca']) ? intval($fws_['cod_registro_crianca']) : 0;
            $obj = new RegistroOrientacao();
            $obj->marcarOrientacaoCriancaLida($codRegistroCrianca, $this->codInscrito, $debug);
        } catch (OKException $e) {
            $ok = true; $msg = $e->getMessage();
        } catch (Exception $e) {
            $msg = $e->getMessage();
        }
        echo json_encode(array('statusMessage' => $ok, 'message' => $msg));
        exit();
    }
}

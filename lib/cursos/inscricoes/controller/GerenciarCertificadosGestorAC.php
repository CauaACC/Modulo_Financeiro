<?php

/**
 * Modulo GESTOR -> Certificados. Tela fora do evento: lista e filtra por gestor
 * o que as views de certificado devolvem.
 *
 *   aba Cobranca -> vw_certificados_cobranca (1 linha por evento)
 *   aba Emitidos -> vw_certificados_emitidos (1 linha por atividade)
 *
 * Somente leitura -- o lancamento da nota (fin_certificados_notafiscal) continua
 * fora daqui.
 *
 * Gate GERAL (cod_evento='TODOS'): a tela nao pertence a evento nenhum, entao o
 * FWA_exigirPermissao normal (que filtra pelo evento corrente) nao serve.
 */
class GerenciarCertificadosGestorAC {

    function __construct() {
        FWA_exigirPermissaoGeral(enumSistema::SIS_GERENCIAR_ADM_GERAL);
    }

    /**
     * json_encode exige UTF-8 (senao volta vazio nos acentos).
     * As colunas destas views sao latin1 no schema, mas a conexao roda em utf8 --
     * o proprio MySQL ja entrega UTF-8. Converter as cegas dava duplo-encode
     * ("QualificaÃ§Ã£o"), entao so converte o que ainda NAO e UTF-8 valido.
     */
    private function utf8($valor) {
        $v = (string) $valor;
        if ($v === '' || mb_detect_encoding($v, 'UTF-8', true) === 'UTF-8') {
            return $v;
        }
        return mb_convert_encoding($v, 'UTF-8', 'ISO-8859-1');
    }

    /**
     * datetime do banco -> dd/mm/aaaa (com HH:MM quando a hora nao e 00:00).
     * Vazio/zerado vira string vazia -- a tela mostra travessao.
     */
    private function dataView($valor) {
        $v = trim((string) $valor);
        if ($v === '' || strpos($v, '0000-00-00') === 0) {
            return '';
        }
        $ts = strtotime($v);
        if ($ts === false) {
            return '';
        }
        return date('H:i', $ts) === '00:00' ? date('d/m/Y', $ts) : date('d/m/Y H:i', $ts);
    }

    /** Gestores existentes, para o select do filtro. */
    public function listarGestores($fws_ = array(), $debug = false) {
        $objDAO = new CertificadoGestorDAO();
        $lista = array();
        foreach ($objDAO->listarGestores($debug) as $g) {
            $lista[] = $this->utf8($g);
        }
        echo json_encode(array('sucesso' => true, 'gestores' => $lista));
        exit();
    }

    /**
     * Aba Cobranca: emitidos x cobrados x a cobrar, por evento.
     * Filtros: gestor, busca (codigo ou titulo), somente_a_cobrar.
     */
    public function listarCobranca($fws_, $debug = false) {
        try {
            $gestor  = isset($fws_['gestor']) ? trim($fws_['gestor']) : '';
            $busca   = isset($fws_['busca'])  ? trim($fws_['busca'])  : '';
            $soACobrar = !empty($fws_['somente_a_cobrar']);

            $objDAO = new CertificadoGestorDAO();
            $linhas = $objDAO->listarCobranca($gestor, $busca, $soACobrar, $debug);

            // pessoas = base cobravel; atividade/geral/perfil = documentos (informativo)
            $totPessoas = 0; $totAtiv = 0; $totGeral = 0; $totPerfil = 0;
            $totCobrados = 0; $totACobrar = 0;
            $saida = array();
            foreach ($linhas as $l) {
                $totPessoas  += $l['pessoas'];
                $totAtiv     += $l['atividade'];
                $totGeral    += $l['geral'];
                $totPerfil   += $l['perfil'];
                $totCobrados += $l['cobrados'];
                $totACobrar  += $l['a_cobrar'];
                $saida[] = array(
                    'cod_evento' => $l['cod_evento'],
                    'gestor'     => $this->utf8($l['gestor']),
                    'titulo'     => $this->utf8($l['titulo']),
                    'status'     => $this->utf8($l['status']),
                    // dd/mm/aaaa, com hora quando ela nao e 00:00 (varios eventos usam
                    // 18:00 / 23:59 / 00:01 para controlar a virada do dia)
                    'liberacao'  => $this->dataView($l['liberacao']),
                    'pessoas'    => $l['pessoas'],
                    'atividade'  => $l['atividade'],
                    'geral'      => $l['geral'],
                    'perfil'     => $l['perfil'],
                    'cobrados'   => $l['cobrados'],
                    'a_cobrar'   => $l['a_cobrar'],
                );
            }

            echo json_encode(array(
                'sucesso'   => true,
                'registros' => $saida,
                'total'     => count($saida),
                'totais'    => array(
                    'pessoas'   => $totPessoas,
                    'atividade' => $totAtiv,
                    'geral'     => $totGeral,
                    'perfil'    => $totPerfil,
                    'cobrados'  => $totCobrados,
                    'a_cobrar'  => $totACobrar,
                ),
                'limitado'  => (count($saida) >= CertificadoGestorDAO::LIMITE),
            ));
            exit();
        } catch (Exception $e) {
            echo json_encode(array('sucesso' => false, 'mensagem' => $this->utf8($e->getMessage())));
            exit();
        }
    }

    /**
     * Drill-down da aba Cobranca: as PESSOAS do evento que tem direito a certificado,
     * com o mecanismo de cada uma. O total daqui bate com o qtd_pessoas da linha.
     */
    public function listarPessoasEvento($fws_, $debug = false) {
        try {
            $codEvento = isset($fws_['cod_evento']) ? (int) $fws_['cod_evento'] : 0;
            if ($codEvento <= 0) {
                echo json_encode(array('sucesso' => false, 'mensagem' => 'Evento não informado.'));
                exit();
            }

            $objDAO = new CertificadoGestorDAO();
            $linhas = $objDAO->listarPessoasEvento($codEvento, $debug);

            $nAtiv = 0; $nGeral = 0; $nPerfil = 0;
            $saida = array();
            foreach ($linhas as $l) {
                $nAtiv   += $l['por_atividade'];
                $nGeral  += $l['por_geral'];
                $nPerfil += $l['por_perfil'];
                $saida[] = array(
                    'cod_inscrito'  => $l['cod_inscrito'],
                    'nome'          => $this->utf8($l['nome']),
                    'cpf'           => $this->utf8($l['cpf']),
                    'email'         => $this->utf8($l['email']),
                    'por_atividade' => $l['por_atividade'],
                    'por_geral'     => $l['por_geral'],
                    'por_perfil'    => $l['por_perfil'],
                );
            }

            echo json_encode(array(
                'sucesso'    => true,
                'cod_evento' => $codEvento,
                'registros'  => $saida,
                'total'      => count($saida),
                'totais'     => array('atividade' => $nAtiv, 'geral' => $nGeral, 'perfil' => $nPerfil),
                'limitado'   => (count($saida) >= CertificadoGestorDAO::LIMITE),
            ));
            exit();
        } catch (Exception $e) {
            echo json_encode(array('sucesso' => false, 'mensagem' => $this->utf8($e->getMessage())));
            exit();
        }
    }

    /**
     * Aba Emitidos: uma linha por atividade que libera certificado.
     * Filtros: gestor, busca, cod_evento (drill-down vindo da aba Cobranca).
     */
    public function listarEmitidos($fws_, $debug = false) {
        try {
            $gestor    = isset($fws_['gestor'])     ? trim($fws_['gestor']) : '';
            $busca     = isset($fws_['busca'])      ? trim($fws_['busca'])  : '';
            $codEvento = isset($fws_['cod_evento']) ? (int) $fws_['cod_evento'] : 0;

            $objDAO = new CertificadoGestorDAO();
            $linhas = $objDAO->listarEmitidos($gestor, $busca, $codEvento, $debug);

            $total = 0;
            $saida = array();
            foreach ($linhas as $l) {
                $total += $l['total'];
                $saida[] = array(
                    'cod_evento'       => $l['cod_evento'],
                    'cod_oficina'      => $l['cod_oficina'],
                    'gestor'           => $this->utf8($l['gestor']),
                    'titulo_evento'    => $this->utf8($l['titulo_evento']),
                    'titulo_atividade' => $this->utf8($l['titulo_atividade']),
                    'total'            => $l['total'],
                );
            }

            echo json_encode(array(
                'sucesso'   => true,
                'registros' => $saida,
                'total'     => count($saida),
                'totais'    => array('certificados' => $total),
                'limitado'  => (count($saida) >= CertificadoGestorDAO::LIMITE),
            ));
            exit();
        } catch (Exception $e) {
            echo json_encode(array('sucesso' => false, 'mensagem' => $this->utf8($e->getMessage())));
            exit();
        }
    }
}

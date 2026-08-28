<?php

/**
 * POLITICA DE CANCELAMENTO DE INSCRICAO — regra unica, em classe separada
 * (decisao do usuario, 2026-08-20).
 *
 * Responde: o cancelamento pode ser feito DIRETO (autoatendimento bot/API)
 * ou precisa passar por analise humana?
 *
 * Impedimentos ao cancelamento direto:
 *  - EVENTO_ENCERRADO        evento/curso ja encerrado (curto-circuito: nem
 *                            encaminha ao financeiro; orientar secretaria)
 *  - EVENTO_INICIADO         evento/curso em andamento (multa/proporcional)
 *  - PRESENCA_REGISTRADA     inscrito ja tem presenca no evento
 *  - CERTIFICADO_LIBERADO    certificado ja liberado para o inscrito
 *  - MENSALIDADE_EM_ABERTO   curso cobrando mensalidade com parcela em aberto
 *  - PAGO_FORA_ARREPENDIMENTO inscricao paga e ja se passaram mais de
 *                            DIAS_ARREPENDIMENTO dias da inscricao (CDC art.49)
 *
 * Inscricao PAGA dentro do prazo de arrependimento NAO e impedimento: cancela
 * direto e o financeiro do gestor recebe o pedido de estorno por e-mail.
 *
 * Quem usa: GerenciarAtendimentoAC::cancelarInscricao (bot/API). O botao do
 * admin nao passa por aqui — decisao humana, so confirm + aviso.
 */
class PoliticaCancelamento
{
    const DIAS_ARREPENDIMENTO = 7;

    /**
     * @param Inscrito $objInscrito ja carregado (getInscritoById)
     * @return array [
     *   'pode_cancelar_direto'   => bool,
     *   'evento_encerrado'       => bool,
     *   'impedimentos'           => array de ['codigo'=>..., 'descricao'=>...],
     *   'pago'                   => bool,
     *   'valor_pago'             => float,
     *   'formas'                 => string,
     *   'dentro_arrependimento'  => bool,
     * ]
     */
    public static function avaliar(Inscrito $objInscrito, $debug = false)
    {
        $codEvento   = intval($objInscrito->getCodEvento());
        $codInscrito = intval($objInscrito->getCodInscrito());
        $impedimentos = array();

        // ---- pago? (voucher/isencao contam como "pago" para fins de analise)
        $resumo = (new PagamentoDAO())->resumoPagamentosAtivos($codEvento, $codInscrito, $debug);
        $pago = ($resumo['total'] > 0)
             || $objInscrito->getCodVoucher()
             || $objInscrito->getCodIsento();

        // ---- prazo de arrependimento (dias corridos desde a inscricao)
        $dentroArrependimento = false;
        $datIncl = trim((string)$objInscrito->getDatIncl());
        if ($datIncl != '') {
            $limite = date('YmdHis', strtotime($datIncl . ' + ' . self::DIAS_ARREPENDIMENTO . ' days'));
            $dentroArrependimento = (date('YmdHis') <= $limite);
        }

        // ---- config do evento (instancia propria; o singleton
        //      getInstancePopulado e unico por request e devolveria a config
        //      de OUTRO evento num cancelamento cross-evento)
        $objEvConf = self::carregarConfig($codEvento);

        // ---- periodo de realizacao do evento
        $eventoEncerrado = false;
        $eventoIniciado  = false;
        try {
            $realIni = $objEvConf->getRealizacaoIni();
            $realFim = $objEvConf->getRealizacaoFim();
            if (trim((string)$realIni) != '' || trim((string)$realFim) != '') {
                $situacao = testaNaoIniciadaOuJaEncerrada($realIni, $realFim);
                if ($situacao === 'Encerrada') {
                    $eventoEncerrado = true;
                } elseif ($situacao === null || $situacao === '') {
                    // em curso (nem "Aguardando Período" nem "Encerrada")
                    $eventoIniciado = true;
                }
            }
        } catch (Exception $e) {
            // sem config: nao da para afirmar nada sobre o periodo
        }

        if ($eventoEncerrado) {
            // Curto-circuito: cancelamento nao se aplica; orientar secretaria.
            return array(
                'pode_cancelar_direto'  => false,
                'evento_encerrado'      => true,
                'impedimentos'          => array(array(
                    'codigo'    => 'EVENTO_ENCERRADO',
                    'descricao' => 'Evento/curso ja encerrado',
                )),
                'pago'                  => (bool)$pago,
                'valor_pago'            => $resumo['valor'],
                'formas'                => $resumo['formas'],
                'dentro_arrependimento' => $dentroArrependimento,
            );
        }

        if ($eventoIniciado) {
            $impedimentos[] = array(
                'codigo'    => 'EVENTO_INICIADO',
                'descricao' => 'Evento/curso ja iniciado',
            );
        }

        // ---- presenca registrada
        if ($objInscrito->getFlgPresencaInscrito()) {
            $impedimentos[] = array(
                'codigo'    => 'PRESENCA_REGISTRADA',
                'descricao' => 'Presenca ja registrada no evento',
            );
        }

        // ---- certificado liberado (nao ha registro de emissao por inscrito;
        //      o criterio e o mesmo gate de liberacao usado no atendimento)
        if (self::certificadoLiberado($objInscrito, $pago, $objEvConf)) {
            $impedimentos[] = array(
                'codigo'    => 'CERTIFICADO_LIBERADO',
                'descricao' => 'Certificado ja liberado para o inscrito',
            );
        }

        // ---- curso cobrando mensalidade com parcela em aberto
        $temMensalidade = $objEvConf->getMensAtiva() || $objEvConf->getRecebePagamentoMensalidade();
        if ($temMensalidade) {
            $parcelasAbertas = (new PedidoDAO())->contarParcelasEmAberto($codEvento, $codInscrito, $debug);
            if ($parcelasAbertas > 0) {
                $impedimentos[] = array(
                    'codigo'    => 'MENSALIDADE_EM_ABERTO',
                    'descricao' => 'Curso com mensalidade: ' . $parcelasAbertas . ' parcela(s) em aberto',
                );
            }
        }

        // ---- pago fora do prazo de arrependimento
        if ($pago && !$dentroArrependimento) {
            $impedimentos[] = array(
                'codigo'    => 'PAGO_FORA_ARREPENDIMENTO',
                'descricao' => 'Inscricao paga ha mais de ' . self::DIAS_ARREPENDIMENTO
                             . ' dias da inscricao (estorno/multa a analisar)',
            );
        }

        return array(
            'pode_cancelar_direto'  => empty($impedimentos),
            'evento_encerrado'      => false,
            'impedimentos'          => $impedimentos,
            'pago'                  => (bool)$pago,
            'valor_pago'            => $resumo['valor'],
            'formas'                => $resumo['formas'],
            'dentro_arrependimento' => $dentroArrependimento,
        );
    }

    /** Config carregada em instancia PROPRIA (nunca o singleton por request). */
    private static function carregarConfig($codEvento)
    {
        $objEvConf = new EventoConfiguracao();
        $objEvConf->setCodEvento(intval($codEvento));
        try {
            $objEvConf->getEventoConfiguracaoById();
        } catch (Exception $e) {
            // sem linha de config: getters magicos devolvem NULL, que os
            // testes abaixo tratam como "recurso desligado"
        }
        return $objEvConf;
    }

    /**
     * Mesmo gate de GerenciarAtendimentoAC::verificarCertificado, reduzido:
     * evento emite + data de liberacao ja passou + pago + presenca.
     */
    private static function certificadoLiberado(Inscrito $objInscrito, $pago, $objEvConf)
    {
        try {
            if (!$objEvConf->getEmitirCertificado()) {
                return false;
            }
            $dataLiberar = $objEvConf->getDataLiberarCertificado();
            if ($dataLiberar) {
                if (formatDateTimeSemSeparador($dataLiberar) > date('YmdHi')) {
                    return false;
                }
            }
            if (!$pago) {
                return false;
            }
            return (bool)$objInscrito->getFlgPresencaInscrito();
        } catch (Exception $e) {
            return false;
        }
    }
}

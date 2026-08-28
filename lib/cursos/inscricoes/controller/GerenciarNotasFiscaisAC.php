<?php

/**
 * Consulta das notas fiscais emitidas (NFS-e e NF-e) — Financeiro > Notas Fiscais.
 *
 * Tela de LEITURA: quem emite continua fazendo isso pelo pedido do inscrito
 * (NFS-e) ou pela venda (NF-e). Aqui se responde o que a rotina fiscal pergunta:
 * o que saiu no periodo, o que foi rejeitado, o que ainda esta processando,
 * quanto foi emitido e qual nota corresponde a qual pedido.
 */
class GerenciarNotasFiscaisAC {

    function __construct() {
        FWA_exigirPermissao(array(enumSistema::SIS_CONTROLE_FINANCEIRO, enumSistema::SIS_GERENCIAR_ADM_GERAL));
    }

    // ------------------------------------------------------------------
    // Isolamento por gestor — mesmo criterio do resto do modulo fiscal:
    // o emissor pertence ao gestor, entao quem nao e ADM_GERAL so enxerga
    // as notas dos emissores dos seus gestores.
    // ------------------------------------------------------------------

    private function ehAdmGeral() {
        $codUsuario = isset($_SESSION['_cod_usuario']) ? $_SESSION['_cod_usuario'] : '';
        return (bool) @FWA_Autenticacao($codUsuario, enumSistema::SIS_GERENCIAR_ADM_GERAL, 5, false, false);
    }

    /** @return array|null null = ve todos (ADM_GERAL); array = so estes gestores. */
    private function getGestoresPermitidos($debug = false) {
        if ($this->ehAdmGeral()) {
            return null;
        }
        $codUsuario = isset($_SESSION['_cod_usuario']) ? $_SESSION['_cod_usuario'] : '';
        $obj = new NotaFiscalEmissor();
        $lista = $obj->getObjDAO()->listarGestoresAtivos($codUsuario, $debug);

        $cods = array();
        foreach ($lista as $g) {
            $cods[] = (int) $g['cod_gestor'];
        }
        return $cods;
    }

    /**
     * Filtros vindos da tela, ja normalizados.
     *
     * O periodo padrao (mes corrente) e' decidido pela VIEW, que ja abre com as
     * datas preenchidas. O controller nao inventa periodo: se o usuario limpou
     * as datas de proposito, ele quer o historico inteiro — reaplicar o mes aqui
     * fazia o botao "Limpar" nao limpar nada.
     */
    private function montarFiltros($fws_, $debug = false) {
        $dataIni = isset($fws_['dataIni']) ? trim((string) $fws_['dataIni']) : '';
        $dataFim = isset($fws_['dataFim']) ? trim((string) $fws_['dataFim']) : '';

        $filtros = array(
            'codEvento' => COD_EVENTO,
            'gestores'  => $this->getGestoresPermitidos($debug),
            'status'    => isset($fws_['status'])   ? trim((string) $fws_['status'])   : '',
            'tipo'      => isset($fws_['tipo'])     ? trim((string) $fws_['tipo'])     : '',
            'ambiente'  => isset($fws_['ambiente']) ? trim((string) $fws_['ambiente']) : '',
            'dataIni'   => $this->normalizarData($dataIni),
            'dataFim'   => $this->normalizarData($dataFim),
            'numero'    => isset($fws_['numero'])   ? trim((string) $fws_['numero'])   : '',
            'busca'     => isset($fws_['busca'])    ? trim((string) $fws_['busca'])    : '',
            'semEnvio'  => !empty($fws_['semEnvio'])
        );

        // Sempre no escopo do evento atual: a tela vive dentro do evento, como
        // o resto do admin. Consulta entre eventos e assunto de tela de gestor.
        return $filtros;
    }

    /** Aceita dd/mm/aaaa (padrao das telas) e aaaa-mm-dd (input date). */
    private function normalizarData($valor) {
        $valor = trim((string) $valor);
        if ($valor === '') {
            return '';
        }
        if (preg_match('#^(\d{2})/(\d{2})/(\d{4})$#', $valor, $m)) {
            return $m[3] . '-' . $m[2] . '-' . $m[1];
        }
        if (preg_match('#^\d{4}-\d{2}-\d{2}$#', $valor)) {
            return $valor;
        }
        return '';
    }

    private function classeDoStatus($status) {
        switch ($status) {
            case 'AUTORIZADA':  return 'dot-green';
            case 'PROCESSANDO': return 'dot-blue';
            case 'CANCELADA':   return 'dot-gray';
            case 'ERRO':        return 'dot-red';
            default:            return 'dot-gray';
        }
    }

    private function rotuloDoStatus($status, $tipo) {
        switch ($status) {
            case 'AUTORIZADA':  return 'Autorizada';
            case 'PROCESSANDO': return $tipo == 'NFE' ? 'Processando na SEFAZ' : 'Processando na prefeitura';
            case 'CANCELADA':   return 'Cancelada';
            case 'ERRO':        return 'Rejeitada';
            default:            return (string) $status;
        }
    }

    /**
     * Cards do topo. Mostram o recorte filtrado, nao o total geral — o numero
     * tem que bater com a lista logo abaixo.
     */
    public function carregarResumoNotas($fws_, $debug = false) {
        $obj = new NotaFiscal();
        $resumo = $obj->resumoPorStatus($this->montarFiltros($fws_, $debug), $debug);

        $ordem = array(
            'AUTORIZADA'  => array('Autorizadas',  '#1e7e34'),
            'PROCESSANDO' => array('Processando',  '#0b5ed7'),
            'ERRO'        => array('Rejeitadas',   '#8b1a12'),
            'CANCELADA'   => array('Canceladas',   '#666')
        );

        $totalQtd = 0;
        $totalValor = 0;
        foreach ($resumo as $st => $d) {
            $totalQtd += $d['qtd'];
            // Cancelada nao soma no total emitido: nota cancelada nao e receita.
            if ($st != 'CANCELADA') {
                $totalValor += $d['valor'];
            }
        }

        echo "<div style='display:flex; gap:10px; flex-wrap:wrap; margin-bottom:12px;'>";
        foreach ($ordem as $st => $cfg) {
            $qtd   = isset($resumo[$st]) ? $resumo[$st]['qtd'] : 0;
            $valor = isset($resumo[$st]) ? $resumo[$st]['valor'] : 0;
            echo "<div style='flex:1; min-width:150px; background:#fff; border:1px solid #e8e8e8; border-radius:10px; padding:10px 14px;'>"
               . "<div style='font-size:11px; color:#888; text-transform:uppercase;'>" . $cfg[0] . "</div>"
               . "<div style='font-size:20px; font-weight:600; color:" . $cfg[1] . ";'>" . $qtd . "</div>"
               . "<div style='font-size:12px; color:#666;'>R$ " . number_format($valor, 2, ',', '.') . "</div>"
               . "</div>";
        }
        echo "<div style='flex:1; min-width:150px; background:#f7f7f7; border:1px solid #e0e0e0; border-radius:10px; padding:10px 14px;'>"
           . "<div style='font-size:11px; color:#888; text-transform:uppercase;'>Total emitido</div>"
           . "<div style='font-size:20px; font-weight:600; color:#333;'>" . $totalQtd . "</div>"
           . "<div style='font-size:12px; color:#666;'>R$ " . number_format($totalValor, 2, ',', '.')
           . " <span style='color:#999;'>(sem canceladas)</span></div>"
           . "</div>";
        echo "</div>";
    }

    public function carregarTabelaNotas($fws_, $debug = false) {
        $obj = new NotaFiscal();
        $lista = $obj->listarComFiltros($this->montarFiltros($fws_, $debug), $debug);

        if (empty($lista)) {
            echo "<tr><td colspan='8' style='text-align:center; padding:24px; color:#999;'>"
               . "Nenhuma nota fiscal no periodo/filtro selecionado."
               . "</td></tr>";
            return;
        }

        foreach ($lista as $row) {
            $tipo    = (string) $row['tipo'];
            $status  = (string) $row['status'];
            $numero  = trim((string) $row['numero_exibicao']);
            $valor   = (float) $row['valor_exibicao'];
            $quando  = (string) $row['data_referencia'];
            $codNota = (int) $row['cod_nota_fiscal'];

            echo "<tr data-id='" . $codNota . "'>";

            // --- acoes
            echo "<td class='col-codigo'>";
            if (!empty($row['url_pdf'])) {
                echo "<a href='" . htmlspecialchars((string) $row['url_pdf']) . "' target='_blank' "
                   . "class='btn-acao' title='PDF da nota'><i class='fa fa-file-pdf-o'></i></a>";
            }
            if (!empty($row['url_xml'])) {
                echo "<a href='" . htmlspecialchars((string) $row['url_xml']) . "' target='_blank' "
                   . "class='btn-acao' title='XML'><i class='fa fa-code'></i></a>";
            }
            if (!empty($row['url_consulta_publica'])) {
                echo "<a href='" . htmlspecialchars((string) $row['url_consulta_publica']) . "' target='_blank' "
                   . "class='btn-acao' title='Consulta publica'><i class='fa fa-external-link'></i></a>";
            }
            echo "<a href='#' class='btn-acao nf-detalhe' title='Detalhes'><i class='fa fa-search-plus'></i></a>";
            echo "</td>";

            // --- numero / serie
            echo "<td class='col-codigo'>";
            echo $numero !== '' ? htmlspecialchars($numero) : "<span style='color:#bbb;'>—</span>";
            $serie = $tipo == 'NFE' ? (string) $row['serie_nfe'] : (string) $row['serie_dps'];
            if (trim($serie) != '') {
                echo "<div style='font-weight:normal; color:#999; font-size:11px;'>serie " . htmlspecialchars($serie) . "</div>";
            }
            echo "</td>";

            // --- tipo + ambiente
            echo "<td class='col-badge'><span class='badge-tipo'>" . ($tipo == 'NFE' ? 'NF-e' : 'NFS-e') . "</span>";
            if ((string) $row['ambiente'] != 'PRODUCAO') {
                echo "<div style='font-size:10px; color:#b8860b; margin-top:2px;'>homologacao</div>";
            }
            echo "</td>";

            // --- data
            echo "<td class='col-codigo' data-ord='" . htmlspecialchars($quando) . "'>"
               . ($quando != '' ? date('d/m/Y H:i', strtotime($quando)) : '') . "</td>";

            // --- tomador
            echo "<td class='col-nome'>" . htmlspecialchars((string) $row['tomador_nome']);
            $doc = preg_replace('/[^0-9]/', '', (string) $row['tomador_cpf_cnpj']);
            if ($doc != '') {
                echo "<div style='font-weight:normal; color:#999; font-size:11px;'>"
                   . htmlspecialchars($this->formatarDocumento($doc)) . "</div>";
            }
            echo "</td>";

            // --- valor
            echo "<td class='col-num' style='text-align:right;' data-ord='" . $valor . "'>"
               . "R$ " . number_format($valor, 2, ',', '.') . "</td>";

            // --- status
            echo "<td class='col-badge'><span class='badge-tipo'>"
               . "<span class='dot " . $this->classeDoStatus($status) . "'></span>"
               . $this->rotuloDoStatus($status, $tipo) . "</span>";
            if ($status == 'ERRO' && trim((string) $row['mensagem_erro']) != '') {
                echo "<div style='font-size:11px; color:#8b1a12; margin-top:2px;'>"
                   . htmlspecialchars(mb_substr((string) $row['mensagem_erro'], 0, 90)) . "</div>";
            }
            if ($status == 'AUTORIZADA' && empty($row['data_envio_email'])) {
                echo "<div style='font-size:11px; color:#b8860b; margin-top:2px;'>nao enviada por e-mail</div>";
            }
            echo "</td>";

            // --- origem
            echo "<td class='col-codigo'>" . htmlspecialchars((string) $row['origem']) . " "
               . htmlspecialchars((string) $row['origem_id']);
            if (trim((string) $row['emissor_razao_social']) != '') {
                echo "<div style='font-weight:normal; color:#999; font-size:11px;'>"
                   . htmlspecialchars((string) $row['emissor_razao_social']) . "</div>";
            }
            echo "</td>";

            echo "</tr>";
        }
    }

    private function formatarDocumento($doc) {
        if (strlen($doc) == 11) {
            return substr($doc, 0, 3) . '.' . substr($doc, 3, 3) . '.' . substr($doc, 6, 3) . '-' . substr($doc, 9, 2);
        }
        if (strlen($doc) == 14) {
            return substr($doc, 0, 2) . '.' . substr($doc, 2, 3) . '.' . substr($doc, 5, 3)
                 . '/' . substr($doc, 8, 4) . '-' . substr($doc, 12, 2);
        }
        return $doc;
    }

    /**
     * Detalhe de uma nota. Nao repete o que a linha ja mostra: traz o que so
     * interessa quando se investiga um caso (erro completo, chave, protocolo,
     * competencia, envio de e-mail, cancelamento).
     */
    public function carregarDetalheNota($fws_, $debug = false) {
        $codNota = isset($fws_['codNotaFiscal']) ? (int) $fws_['codNotaFiscal'] : 0;
        if (!$codNota) {
            echo "<div style='color:#999;'>Nota nao informada.</div>";
            return;
        }

        $obj = new NotaFiscal();
        $obj->setCodNotaFiscal($codNota);
        if (!$obj->getNotaFiscalById($debug)) {
            echo "<div style='color:#999;'>Nota nao encontrada.</div>";
            return;
        }

        // Isolamento por gestor tambem no detalhe: sem isso, bastaria trocar o
        // id na chamada para ver nota de outro gestor.
        $permitidos = $this->getGestoresPermitidos($debug);
        if ($permitidos !== null) {
            $objEmissor = new NotaFiscalEmissor();
            $objEmissor->setCodEmissor($obj->getCodEmissor());
            $objEmissor->getEmissorById($debug);
            if (!in_array((int) $objEmissor->getCodGestor(), $permitidos, true)) {
                echo "<div style='color:#8b1a12;'>Esta nota pertence a outro gestor.</div>";
                return;
            }
        }

        $linhas = array(
            'Referencia'      => $obj->getReferencia(),
            'Competencia'     => $obj->getDataCompetencia() ? date('d/m/Y', strtotime($obj->getDataCompetencia())) : '',
            'Codigo verif.'   => $obj->getCodigoVerificacao(),
            'Chave NF-e'      => $obj->getChaveNfe(),
            'Protocolo'       => $obj->getProtocoloAutorizacao(),
            'Natureza'        => $obj->getNaturezaOperacao(),
            'CFOP'            => $obj->getCfop(),
            'Servico'         => $obj->getDescricaoServico(),
            'Complemento'     => $obj->getInformacoesComplementares(),
            'E-mail enviado'  => $obj->getDataEnvioEmail()
                                    ? date('d/m/Y H:i', strtotime($obj->getDataEnvioEmail())) . ' para ' . $obj->getEmailEnvio()
                                    : 'nao enviado',
            'Cancelamento'    => $obj->getDataCancelamento()
                                    ? date('d/m/Y H:i', strtotime($obj->getDataCancelamento())) . ' — ' . $obj->getMotivoCancelamento()
                                    : '',
            'Erro'            => $obj->getMensagemErro()
        );

        echo "<table class='tbl-fin' style='width:100%;'>";
        foreach ($linhas as $rotulo => $valor) {
            $valor = trim((string) $valor);
            if ($valor === '') {
                continue;
            }
            echo "<tr><td style='width:150px; color:#888; vertical-align:top;'>" . htmlspecialchars($rotulo) . "</td>"
               . "<td>" . nl2br(htmlspecialchars($valor)) . "</td></tr>";
        }
        echo "</table>";
    }

}

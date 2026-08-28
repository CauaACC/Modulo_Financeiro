<?php
/**
 * Controller do fluxo de inscrição AGENDA (tipo_fluxo_inscricao='AGENDA').
 *
 * Sequência: passo 1 (escolher dia/oficina) -> passo 2 (escolher tamanho do grupo/perfil)
 *         -> passo 3 (dados pessoais) -> confirmação.
 *
 * Centraliza as consultas usadas pelas views agenda_passo1_dia.php e agenda_passo2_grupo.php:
 * - oficinas ATIVADAS do evento, com vagas disponíveis pré-calculadas
 * - perfis ATIVADOS do evento, com taxa ativa anexada
 * - cálculo de faixa de valores (mínimo/máximo) entre os perfis
 */
class GerenciarAgendaAC {

    /**
     * Retorna oficinas inscritíveis do evento. Espelha exatamente os filtros e a regra de
     * data_fim_inscricao do fluxo legado (inscricao_congresso_oficina_seleciona.php).
     * Mostra a oficina se data_fim_inscricao está vazia OU é futura. O cálculo de
     * vagas_disponiveis já vem do DAO via CPF distinct.
     */
    public static function carregarOficinasDoEvento($codEvento, $debug = false) {
        $objOf = new Oficina();
        $objOf->setCodEvento($codEvento);
        $lista = $objOf->carregarOficinasInscritiveisAgenda($debug);
        if (!is_array($lista)) { return array(); }

        // formatDateTimeSemSeparador retorna 14 chars (YYYYMMDDHHIISS) — comparar string com mesma largura
        $hoje = date('YmdHis');
        $resultado = array();
        foreach ($lista as $of) {
            $dataFim = $of->getDataFimInscricao();
            $dataFimFmt = formatDateTimeSemSeparador($dataFim);
            // Mesma regra do legado: mostra se data_fim_inscricao vazia OU futura
            if ($dataFimFmt != '' && $hoje > $dataFimFmt) { continue; }
            $resultado[] = $of;
        }
        return $resultado;
    }

    /**
     * Retorna perfis ATIVADOS do evento. Cada perfil ganha 'valorTaxa' (float) via setExtra().
     */
    public static function carregarPerfisDoEvento($codEvento, $debug = false) {
        $objP = new Perfil();
        $objP->setCodEvento($codEvento);
        try {
            $objP->carregarListaPerfilInscricao($debug);
        } catch (Exception $e) {
            return array();
        }
        $lista = $objP->getListaPerfil();
        if (!is_array($lista)) { return array(); }

        $resultado = array();
        foreach ($lista as $perfil) {
            if ($perfil->getReceberInscricao() != 1) { continue; }
            $valor = 0;
            try {
                $objTx = new Taxa();
                $objTx->setCodPerfil($perfil->getCodPerfil());
                $objTx->setCodEvento($codEvento);
                $cond = $perfil->getValoresPorPeriodo() ? array('data_hoje' => date('Y-m-d H:i:s')) : false;
                $objTx->carregarTaxaAtiva($cond);
                $listaTx = $objTx->getListaTaxas();
                if (is_array($listaTx) && count($listaTx) > 0) {
                    $valor = floatval($listaTx[0]->getTxUnicaAVistaValor());
                }
            } catch (Exception $e) {}
            $perfil->setValorTaxa($valor);
            $resultado[] = $perfil;
        }
        return $resultado;
    }

    /**
     * Carrega uma oficina específica pelo código. Retorna null se não achar.
     */
    public static function carregarOficinaPorCodigo($codOficina, $debug = false) {
        if (!$codOficina) { return null; }
        try {
            $objOf = new Oficina();
            $objOf->setCodOficina((int) $codOficina);
            $objOf->getOficinaById($debug);
            return $objOf;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Retorna [valorMinimo, valorMaximo] entre os perfis. Ignora valores 0/null.
     */
    public static function calcularFaixaValores($perfis) {
        $min = null; $max = null;
        if (!is_array($perfis)) { return array(null, null); }
        foreach ($perfis as $p) {
            $v = floatval($p->getValorTaxa());
            if ($v <= 0) { continue; }
            if ($min === null || $v < $min) { $min = $v; }
            if ($max === null || $v > $max) { $max = $v; }
        }
        return array($min, $max);
    }
}

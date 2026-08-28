<?php
class GerenciarRegistroAtividadeUteisAC {

    public static function tratarDadosAvaliacao($objL) {
        // Inicializa variáveis
        $status = strtolower($objL->getAvaliacaoStatus());
        $classeStatus = '';
        $colorText = '';
        $avaliacaoLiberada = true;
        $txtBotao = '';
        $txtStatusPagamento = "Aguardando Validação";
        $liberarBtnEditar = false;

        // Define a classe e a cor do texto com base no status
        if ($status === 'validado') {
            $classeStatus = "status-validado";
            $colorText = "green";
            $liberarBtnEditar = false;
        } elseif ($status === 'não validado' || $status === 'nao validado') {
            $classeStatus = "status-nao-validado";
            $colorText = "red";
            $liberarBtnEditar = false;
        } else {
            $classeStatus = "status-aguardando";
            $colorText = "orange";
            $liberarBtnEditar = true;
        }

        // Define o texto do botão
        if ($objL->getAvaliacaoData()) {
            $txtBotao = "Revalidar";
        } else {
            $txtBotao = "Validar";
        }

        // Atualiza o status do pagamento
        if ($status === 'validado') {
            $txtStatusPagamento = "Aguardando Fechamento Mês";
        }

        if ($objL->getPagamentoStatus() === 'Confirmado') {
            $txtStatusPagamento = "Pagamento confirmado";
            $avaliacaoLiberada = false;
            $liberarBtnEditar = false;
        }

        // Verifica se o mês da atividade está bloqueado para avaliação
        if ($avaliacaoLiberada && $objL->getDia()) {
            $partesDia = explode('-', $objL->getDia());
            if (count($partesDia) >= 2) {
                $mesDia = intval($partesDia[1]);
                $anoDia = intval($partesDia[0]);
                if (BloqueioAvaliacao::isMesBloqueado($mesDia, $anoDia)) {
                    $avaliacaoLiberada = false;
                    $liberarBtnEditar = false;
                    $txtBotao = "Mês Bloqueado";
                }
            }
        }

        // Retorna as variáveis em um array
        return [
            'classeStatus' => $classeStatus,
            'colorText' => $colorText,
            'avaliacaoLiberada' => $avaliacaoLiberada,
            'txtBotao' => $txtBotao,
            'txtStatusPagamento' => $txtStatusPagamento,
            'liberarBtnEditar' => $liberarBtnEditar,
        ];
    }
}
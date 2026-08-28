<?php
/**
 * Tarefas agendadas do INESCO. So deve ser instanciado pelo gatilho CLI em
 * lib/cursos/cron/executar-tarefas.php.
 *
 * IMPORTANTE: CommandAC.php (rota ?content=controller/CommandAC.php) despacha
 * QUALQUER c=Classe&m=metodo sem checagem de sessao propria — cada controller
 * e responsavel por se proteger. Como os metodos aqui nao recebem um objeto
 * de dominio (diferente de GerenciarEmailAC::enviarConfirmacaoPagamento(), que
 * quebra com erro fatal se chamado por ali passando um array), ficariam
 * executaveis por qualquer visitante sem essa guarda no construtor.
 */
class GerenciarCronAC {

    public function __construct() {
        if (php_sapi_name() !== 'cli') {
            throw new Exception('GerenciarCronAC so pode ser executado via CLI.');
        }
    }

    /**
     * Aviso de vencimento proximo (5 dias e 1 dia antes), pra todos os
     * eventos com aviso_vencimento_habilitado=1 — dentro do MESMO processo,
     * sem subprocesso, sem shell_exec. PedidoDAO::buscarPedidosVencendoEm()
     * ja faz o JOIN com tab_eventos_configuracao e filtra a flag, entao nao
     * precisa enumerar eventos antes: busca direto por pedidos, evento
     * nenhum. Nao envia nada diretamente — so ENFILEIRA em tab_email_fila
     * (GerenciarEmailAC::enviarAvisoVencimentoProximo() faz o enfileirar).
     * Idempotente: quem evita enfileirar o mesmo aviso de novo e a checagem
     * contra tab_pedidos_aviso_vencimento_enviado dentro da mesma query.
     *
     * Ordem deliberada (2026-08-26, a pedido do usuario): reserva o slot em
     * tab_pedidos_aviso_vencimento_enviado ANTES de enfileirar o e-mail, nao
     * depois — mesmo principio do WP-Cron (reagenda antes de rodar o
     * callback). Se a gravacao do controle falhar (corrida rara, erro
     * transiente), nem tenta enfileirar — evita duplicar em troca de, na
     * pior hipotese, perder um aviso isolado. A ordem inversa (enfileirar
     * primeiro) arriscava o oposto: enfileirar de novo se so a gravacao do
     * controle falhasse entre os dois passos.
     */
    public function executarAvisoVencimento($debug = false) {
        $objPedidoDAO   = new PedidoDAO();
        $totalEnfileirados = 0;
        $totalPendentes    = 0;

        foreach (array(5, 1) as $dias) {
            $pedidos = $objPedidoDAO->buscarPedidosVencendoEm($dias, null, $debug);
            foreach ($pedidos as $pedido) {
                $objPedido = new Pedido();
                $objPedido->setAll($pedido);

                if (!$objPedidoDAO->registrarAvisoVencimentoEnviado($pedido['id'], $dias, $debug)) {
                    $totalPendentes++;
                    continue;
                }
                if (GerenciarEmailAC::enviarAvisoVencimentoProximo($objPedido, $dias, $debug)) {
                    $totalEnfileirados++;
                } else {
                    $totalPendentes++;
                }
            }
        }

        executarLog('enfileirados=' . $totalEnfileirados . ' pendentes=' . $totalPendentes, 'GerenciarCronAC', true);
    }

    /**
     * Worker generico da fila de e-mail (tab_email_fila): envia qualquer
     * PENDENTE programado pra agora ou antes, nao importa quem colocou lá
     * (aviso de vencimento, confirmacao de pagamento, ou qualquer coisa
     * futura que enfileirar ali — inclusive um eventual trigger de banco).
     * So mexe em PENDENTE — nunca toca no que ja esta ENVIADO/ERRO/CANCELADO
     * de outros fluxos (enviarCertificados, enviarAvisoPagamento etc., que
     * gravam na fila DEPOIS de ja terem enviado, nunca como PENDENTE).
     *
     * A trava de ambiente de teste continua garantida da forma de sempre,
     * dentro de GerenciarEmailAC::enviarEmail() (chamado aqui dentro) — nao
     * precisa duplicar a checagem neste worker.
     */
    public function processarFilaEmail($debug = false, $limite = 50) {
        $objFilaDAO = new EmailFilaDAO();
        $pendentes  = $objFilaDAO->buscarPendentesParaEnvio($limite, $debug);

        $totalEnviados = 0;
        $totalErros    = 0;

        foreach ($pendentes as $item) {
            $enviou = GerenciarEmailAC::enviarEmail(
                $item['email_destinatario'], $item['assunto'], $item['corpo'], false, true
            );
            if ($enviou) {
                $objFilaDAO->marcarEnviado($item['cod_email_fila'], $debug);
                $totalEnviados++;
            } else {
                $objFilaDAO->marcarErro($item['cod_email_fila'], 'Falha/bloqueio em GerenciarEmailAC::enviarEmail', $debug);
                $totalErros++;
            }
        }

        executarLog('enviados=' . $totalEnviados . ' erros=' . $totalErros, 'GerenciarCronAC', true);
    }
}

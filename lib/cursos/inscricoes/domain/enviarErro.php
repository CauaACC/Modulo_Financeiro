<?php
/**
 * Aviso de erro para o suporte.
 *
 * Usa o canal centralizado GerenciarEmailAC::enviarEmail(), que roteia pelo
 * provedor configurado em WB_PROVIDER_EMAIL -- hoje Postmark.
 */
class enviarErro {

    /**
     * ATE 2026-08-20 este metodo se chamava enviarErro(), no estilo de
     * construtor do PHP 4. O PHP 8 deixou de tratar metodo homonimo da classe
     * como construtor: `new enviarErro(...)` passou a criar o objeto sem
     * executar nada, e os avisos de erro de banco pararam de chegar no
     * suporte em silencio. Renomeado para __construct.
     */
    public function __construct($to, $subject, $msg) {

        /* O assunto levava o CORPO do erro concatenado, nao o assunto -- toda
         * mensagem chegava com um bloco de log no titulo. */
        $assunto = ABREV_EVENTO . " - ERRO - " . $subject;

        try {
            /* Assinatura: (to, subject, body, bcc, formatoHtml).
             * O quarto argumento antigo era o flag de HTML e ia parar no BCC. */
            GerenciarEmailAC::enviarEmail($to, $assunto, $msg, false, false);
        } catch (Throwable $e) {
            /* Avisar o suporte nao pode atrapalhar quem esta na tela: se o
             * provedor recusar ou demorar, o erro ORIGINAL precisa seguir seu
             * caminho e virar mensagem para o usuario. Sem este catch, uma
             * falha no envio substituiria o erro real por outro. */
            executarLog("Falha ao enviar aviso de erro ao suporte: " . $e->getMessage(), "DAO", true);
        }
    }
}

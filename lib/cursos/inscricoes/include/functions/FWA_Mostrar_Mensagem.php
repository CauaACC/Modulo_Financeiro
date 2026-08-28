<?php
/*
 * LEGADO — em extincao.
 *
 * A logica destas duas funcoes mudou-se para a biblioteca do sistema
 * (include/functions/functions.php), como mostrarMensagem() e
 * mostrarMensagemLogin(). Aqui ficaram so as cascas, porque a chamada antiga
 * esta espalhada: 157 chamadas de FWA_Mostrar_Mensagem em 120 arquivos e 4 de
 * FWA_Mostrar_Login. Trocar tudo de uma vez colocaria 120 arquivos no mesmo
 * deploy sem nenhum ganho de comportamento.
 *
 * Ao mexer numa tela por outro motivo, aproveite e troque a chamada:
 *     FWA_Mostrar_Mensagem($MSG)  ->  mostrarMensagem($MSG)
 *     FWA_Mostrar_Login($MSG)     ->  mostrarMensagemLogin($MSG)
 *
 * Quando nao sobrar chamada, este arquivo pode ser apagado (e o require dele
 * sai de functions.php e de FWA_Functions.php).
 */

function FWA_Mostrar_Mensagem($MSG = false) {
    mostrarMensagem($MSG);
}

function FWA_Mostrar_Login($MSG = false) {
    mostrarMensagemLogin($MSG);
}

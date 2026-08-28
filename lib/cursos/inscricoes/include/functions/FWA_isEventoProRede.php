<?php
/**
 * Retorna true se o cod_evento pertence a um edital Pro-REDE.
 *
 * Evita hardcode espalhado de COD_EVENTO == 115 pelo sistema.
 * Ao abrir novo edital Pro-REDE, adicionar o cod_evento ao array abaixo.
 *
 * Eventos Pro-REDE ja cadastrados:
 *   115 - Edital 032/2025 (primeiro Pro-REDE)
 *   129 - Edital 003/2026 (ampliacao cadastro de reserva)
 */
function FWA_isEventoProRede($codEvento) {
    return in_array((int) $codEvento, [115, 129], true);
}

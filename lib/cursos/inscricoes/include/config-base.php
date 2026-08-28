<?php
// config-base.php — settings globais que valem para QUALQUER rota
// Carregado em config.php antes do switch que escolhe entre config-padrao / config-opctec / config-<evento>.
// Usa as constantes ja definidas em config.php (WB_ROOT) para portabilidade —
// nao usar paths hardcoded com '../../../' aqui.

// Constantes externas (chaves de criptografia, tokens de provedor de e-mail, etc.)
@include_once(WB_ROOT . '../logn/eventos/cache/cfg/env.php');

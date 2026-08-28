<?php
/**
 * Guard das telas do modulo Cursos EAD.
 *
 * Uso (antes do require):
 *   $_eadExigir = 'PROFESSOR';   // so acompanhar: relatorios, ponto, mensagens
 *   $_eadExigir = 'COORDENADOR'; // padrao: montar conteudo (cursos, itens, importacao)
 *   require_once(dirname(__FILE__)."/../../view/admin/curso-ead-seguranca.php");
 *
 * O menu apenas esconde o que a pessoa nao pode; a protecao de verdade e' esta
 * (aqui e nos construtores dos controllers). ADM_GERAL entra em qualquer um dos
 * dois papeis -- ele esta explicito nas listas de FWA_CursoEadEscopo.php.
 */
require_once(dirname(__FILE__)."/../../include/config.php");

$_eadPerms = (isset($_eadExigir) && $_eadExigir === 'PROFESSOR')
           ? FWA_permissoesAcompanharEAD()
           : FWA_permissoesCoordenarEAD();

if (!FWA_temPermissao($_eadPerms)) {
    $URL = WB_URL_VIEW . "?content=view/admin/login.php";
    $_SESSION['MSG'] = "Não há usuário logado, ou usuário logado não tem permissão de acesso!";
    echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $URL . "\"></HTML>";
    exit;
}

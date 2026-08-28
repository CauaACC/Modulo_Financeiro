<?php
/**
 * Centraliza "quem responde como PROFESSOR no modulo Cursos EAD".
 *
 * A lista de papeis vive em FWA_permissoesAcompanharEAD()
 * (include/functions/FWA_CursoEadEscopo.php): SIS_EAD_PROFESSOR,
 * SIS_EAD_COORDENADOR e ADM_GERAL. Uma fonte so -- mudou la, muda aqui e em
 * todos os controllers/views do modulo.
 */
function FWA_ehProfessorEAD() {
    if (empty($_SESSION['_cod_usuario'])) return false;
    return FWA_temPermissao(FWA_permissoesAcompanharEAD());
}

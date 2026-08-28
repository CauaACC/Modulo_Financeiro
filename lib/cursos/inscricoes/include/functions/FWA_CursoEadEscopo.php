<?php
/**
 * Modulo Cursos EAD: quem pode o que.
 *
 * Duas coisas diferentes moram aqui, de proposito:
 *
 * 1) PAPEIS (lado admin) -- as listas de permissao usadas pelos controllers,
 *    views e menus do EAD. Ficam em funcao livre, e nao em metodo de
 *    controller, para que FWA_ehProfessorEAD (carregado bem cedo em
 *    functions.php) nao dependa da ordem de carga das classes.
 *    ADM_GERAL entra explicitamente: neste sistema o 999 NAO e superset
 *    automatico -- quem e' admin geral passa porque tem a linha em
 *    sis_direitos_grupos_sistemas, e nao porque o codigo o trate como coringa.
 *
 * 2) ESCOPO (lado aluno) -- o inscrito so acessa os cursos aos quais esta
 *    associado, dentro do evento em que esta inscrito. As telas ja aplicavam
 *    isso na renderizacao; os endpoints AJAX nao aplicavam nada, entao um
 *    inscrito logado podia mandar presenca/tarefa/comentario para item de
 *    qualquer curso, de qualquer evento, chamando o CommandAC direto.
 */

/** Papeis que podem MONTAR o conteudo do EAD (cursos, itens, importacao). */
function FWA_permissoesCoordenarEAD() {
    return array(
        enumSistema::cod('SIS_EAD_COORDENADOR'),
        enumSistema::SIS_GERENCIAR_ADM_GERAL,
    );
}

/** Papeis que podem ACOMPANHAR (relatorios, presenca, ponto, mensagens). */
function FWA_permissoesAcompanharEAD() {
    return array(
        enumSistema::cod('SIS_EAD_PROFESSOR'),
        enumSistema::cod('SIS_EAD_COORDENADOR'),
        enumSistema::SIS_GERENCIAR_ADM_GERAL,
    );
}

/**
 * Mapa cod_curso_item => true dos itens que ESTE inscrito pode acessar NESTE
 * evento: itens ativos dos cursos do evento cujas regras de publicacao
 * (exibir_para, filtro_status, filtro_status_processo, cod_perfil e vinculo por
 * oficina) o alcancam. A regra e' a mesma da tela -- Curso::inscritoPodeVer --
 * justamente para tela e endpoint nunca discordarem.
 *
 * Guardado na sessao por 5 minutos porque os endpoints do aluno batem sem
 * parar (PING de presenca a cada 30s, ponto a cada 60s): com o mapa em maos a
 * checagem vira um isset(), sem ida ao banco. Conteudo publicado agora aparece
 * assim que o mapa for recarregado -- e quem chama com um item fora do mapa
 * forca essa recarga (ver FWA_exigirCursoItemDoInscrito).
 */
function FWA_itensCursoPermitidos($forcarRecarga = false, $debug = false) {
    $codInscrito = !empty($_SESSION['_cod_inscrito']) ? (int) $_SESSION['_cod_inscrito'] : 0;
    if (!$codInscrito) return array();

    $codEvento = (int) COD_EVENTO;
    $cache = isset($_SESSION['_ead_itens_ok'][$codEvento][$codInscrito])
           ? $_SESSION['_ead_itens_ok'][$codEvento][$codInscrito] : null;
    if (!$forcarRecarga && $cache && (time() - (int) $cache['ts']) < 300) {
        return $cache['itens'];
    }

    $itens = array();
    try {
        $objInscrito = new Inscrito();
        $objInscrito->setCodInscrito($codInscrito);
        try { $objInscrito->getInscritoById($debug); } catch (Exception $e) {}

        $inscricaoPaga = Curso::calcularInscricaoPaga($objInscrito);

        $daoOfi = new OficinaDAO();
        $oficinasInscrito = array();
        foreach ($daoOfi->getCodOficinasByInscrito($codEvento, $codInscrito, $debug) as $co) {
            $oficinasInscrito[$co] = true;
        }
        $cursosComOficina = $daoOfi->getCursosComOficina($codEvento, $debug);

        $objCurso = new Curso();
        $objCurso->carregarLista(true, $debug);
        $codCursos = array();
        foreach (($objCurso->getListaCurso() ?: array()) as $cu) {
            if ($cu->inscritoPodeVer($objInscrito, $inscricaoPaga, $oficinasInscrito, $cursosComOficina)) {
                $codCursos[] = (int) $cu->getCodCurso();
            }
        }

        if (!empty($codCursos)) {
            $daoItem = new CursoItemDAO();
            $itens = $daoItem->itensPermitidosParaInscrito($codEvento, $codCursos, $debug);
        }
    } catch (Exception $e) {
        // Sem mapa nao se libera nada: o gate abaixo recusa e o aluno ve a
        // mensagem, em vez de a falha virar acesso liberado.
        $itens = array();
    }

    $_SESSION['_ead_itens_ok'][$codEvento][$codInscrito] = array('ts' => time(), 'itens' => $itens);
    return $itens;
}

/**
 * Gate dos endpoints do lado aluno. Devolve o codigo validado; lanca Exception
 * quando o item nao pertence a um curso deste inscrito neste evento.
 *
 * Quem chama trata a Exception como ja trata as demais (JSON
 * {statusMessage:false, message:...}).
 */
function FWA_exigirCursoItemDoInscrito($codCursoItem, $debug = false) {
    $codCursoItem = (int) $codCursoItem;
    if (!$codCursoItem) {
        throw new Exception("Item não informado.");
    }
    $permitidos = FWA_itensCursoPermitidos(false, $debug);
    if (isset($permitidos[$codCursoItem])) {
        return $codCursoItem;
    }
    // Pode ser mapa velho (item publicado depois da ultima carga): confere uma
    // vez com dado fresco antes de recusar.
    $permitidos = FWA_itensCursoPermitidos(true, $debug);
    if (isset($permitidos[$codCursoItem])) {
        return $codCursoItem;
    }

    error_log(sprintf('[EAD escopo] recusado item=%d evento=%d inscrito=%s usuario=%s ip=%s',
        $codCursoItem,
        (int) COD_EVENTO,
        isset($_SESSION['_cod_inscrito']) ? $_SESSION['_cod_inscrito'] : '-',
        isset($_SESSION['_cod_usuario'])  ? $_SESSION['_cod_usuario']  : '-',
        isset($_SERVER['REMOTE_ADDR'])    ? $_SERVER['REMOTE_ADDR']    : '-'));

    throw new Exception("Este item não está disponível para você.");
}

/** Zera o mapa de escopo do inscrito corrente (usar apos mudar matricula/curso). */
function FWA_limparCacheEscopoEAD() {
    unset($_SESSION['_ead_itens_ok']);
}

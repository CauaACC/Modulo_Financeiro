<?php
class GestaoAcessosDAO extends DAO
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Tab 1: Eventos do Gestor (com módulos e total de usuários)
     */
    public function getEventosByGestor($gestor, $debug = false)
    {
        $sql = "SELECT e.cod_evento, e.titulo, e.status,
                GROUP_CONCAT(DISTINCT CONCAT(sg.nome_grupo, ' &raquo; ', ss.nome_sistema)
                    ORDER BY sg.nome_grupo, ss.nome_sistema SEPARATOR '||') as modulos,
                (SELECT COUNT(DISTINCT lue2.cod_usuario)
                    FROM sis_ligacao_usuarios_eventos lue2
                    WHERE lue2.cod_evento = e.cod_evento) as total_usuarios
            FROM tab_eventos e
            LEFT JOIN sis_direitos_grupos_sistemas dgs ON dgs.cod_evento = e.cod_evento
            LEFT JOIN sis_grupos sg ON sg.cod_grupo = dgs.cod_grupo AND sg.cod_grupo <> 999
            LEFT JOIN sis_sistemas ss ON ss.cod_sistema = dgs.cod_sistema
            WHERE e.gestor = '" . addslashes($gestor) . "'
            AND e.status = 'ATIVADO'
            GROUP BY e.cod_evento, e.titulo, e.status
            ORDER BY e.cod_evento DESC";

        $this->conex->debug = $debug;
        return $this->conex->Execute($sql);
    }

    /**
     * Tab 2: Usuários do Gestor (com eventos vinculados)
     */
    public function getUsuariosByGestor($gestor, $debug = false)
    {
        $sql = "SELECT su.cod_usuario, su.login,
                COALESCE(tc.nome_convidado, su.login) as nome,
                GROUP_CONCAT(DISTINCT CONCAT(e.cod_evento, ':', e.titulo)
                    ORDER BY e.titulo SEPARATOR '||') as eventos
            FROM sis_usuarios su
            INNER JOIN sis_ligacao_usuarios_eventos lue ON su.cod_usuario = lue.cod_usuario
            INNER JOIN tab_eventos e ON e.cod_evento = lue.cod_evento
            LEFT JOIN tab_convidados tc ON su.cod_usuario = tc.cod_usuario
            WHERE e.gestor = '" . addslashes($gestor) . "'
            AND e.status = 'ATIVADO'
            GROUP BY su.cod_usuario, su.login, nome
            ORDER BY su.login";

        $this->conex->debug = $debug;
        return $this->conex->Execute($sql);
    }

    /**
     * Tab 3: Usuários do Evento (com grupos e permissões)
     */
    public function getUsuariosByEvento($codEvento, $debug = false)
    {
        $codEvento = intval($codEvento);
        $sql = "SELECT su.cod_usuario, su.login,
                COALESCE(tc.nome_convidado, su.login) as nome,
                GROUP_CONCAT(DISTINCT CONCAT(lug.cod_grupo, ':', sg.nome_grupo)
                    ORDER BY sg.nome_grupo SEPARATOR '||') as grupos_det
            FROM sis_usuarios su
            INNER JOIN sis_ligacao_usuarios_eventos lue ON su.cod_usuario = lue.cod_usuario
            LEFT JOIN tab_convidados tc ON su.cod_usuario = tc.cod_usuario
            LEFT JOIN sis_ligacao_usuarios_grupos lug
                ON lug.cod_usuario = su.cod_usuario AND lug.cod_evento = lue.cod_evento
            LEFT JOIN sis_grupos sg
                ON sg.cod_grupo = lug.cod_grupo AND sg.cod_grupo <> 999
            WHERE lue.cod_evento = " . $codEvento . "
            GROUP BY su.cod_usuario, su.login, nome
            ORDER BY su.login";

        $this->conex->debug = $debug;
        return $this->conex->Execute($sql);
    }

    /**
     * Tab 4: Eventos do Usuário (com grupos)
     */
    public function getEventosByUsuario($codUsuario, $debug = false)
    {
        $codUsuario = intval($codUsuario);
        $sql = "SELECT e.cod_evento, e.titulo, e.gestor, e.status,
                GROUP_CONCAT(DISTINCT CONCAT(lug.cod_grupo, ':', sg.nome_grupo)
                    ORDER BY sg.nome_grupo SEPARATOR '||') as grupos_det
            FROM tab_eventos e
            INNER JOIN sis_ligacao_usuarios_eventos lue ON e.cod_evento = lue.cod_evento
            LEFT JOIN sis_ligacao_usuarios_grupos lug
                ON lug.cod_usuario = lue.cod_usuario AND lug.cod_evento = e.cod_evento
            LEFT JOIN sis_grupos sg
                ON sg.cod_grupo = lug.cod_grupo AND sg.cod_grupo <> 999
            WHERE lue.cod_usuario = " . $codUsuario . "
            GROUP BY e.cod_evento, e.titulo, e.gestor, e.status
            ORDER BY e.cod_evento DESC";

        $this->conex->debug = $debug;
        return $this->conex->Execute($sql);
    }

    /**
     * Remover usuário de um evento (grupos + vínculo)
     */
    public function removerUsuarioEvento($codUsuario, $codEvento, $debug = false)
    {
        $codUsuario = intval($codUsuario);
        $codEvento  = intval($codEvento);

        $this->conex->debug = $debug;

        // Remover de todos os grupos do evento
        $sql = "DELETE FROM sis_ligacao_usuarios_grupos WHERE cod_usuario = " . $codUsuario . " AND cod_evento = " . $codEvento;
        $this->conex->Execute($sql);

        // Remover do evento
        $sql = "DELETE FROM sis_ligacao_usuarios_eventos WHERE cod_usuario = " . $codUsuario . " AND cod_evento = " . $codEvento;
        $this->conex->Execute($sql);
    }

    /**
     * Adicionar usuário a um evento
     */
    public function adicionarUsuarioEvento($codUsuario, $codEvento, $debug = false)
    {
        $codUsuario = intval($codUsuario);
        $codEvento  = intval($codEvento);

        $this->conex->debug = $debug;

        // Verificar se já existe
        $sql = "SELECT COUNT(*) as total FROM sis_ligacao_usuarios_eventos
                WHERE cod_usuario = " . $codUsuario . " AND cod_evento = " . $codEvento;
        $RS = $this->conex->Execute($sql);
        if ($RS->fields['total'] > 0) {
            throw new Exception("Usu&aacute;rio j&aacute; est&aacute; vinculado a este evento.");
        }

        $sql = "INSERT INTO sis_ligacao_usuarios_eventos (cod_usuario, cod_evento) VALUES (" . $codUsuario . ", " . $codEvento . ")";
        $this->conex->Execute($sql);
    }

    /**
     * Grupos que o usuário possui em um evento
     */
    public function getGruposUsuarioEvento($codUsuario, $codEvento, $debug = false)
    {
        $codUsuario = intval($codUsuario);
        $codEvento  = intval($codEvento);

        $sql = "SELECT cod_grupo FROM sis_ligacao_usuarios_grupos
                WHERE cod_usuario = " . $codUsuario . " AND cod_evento = " . $codEvento;

        $this->conex->debug = $debug;
        return $this->conex->Execute($sql);
    }

    /**
     * Todos os grupos disponíveis (exceto 999)
     */
    public function getTodosGrupos($debug = false)
    {
        $sql = "SELECT cod_grupo, nome_grupo, COALESCE(categoria, 'Outros') as categoria FROM sis_grupos WHERE cod_grupo <> 999 ORDER BY categoria, ordem, nome_grupo";

        $this->conex->debug = $debug;
        return $this->conex->Execute($sql);
    }

    /**
     * Copiar grupos de um evento para outro para o mesmo usuário
     */
    public function copiarGruposEntreEventos($codUsuario, $codEventoOrigem, $codEventoDestino, $debug = false)
    {
        $codUsuario       = intval($codUsuario);
        $codEventoOrigem  = intval($codEventoOrigem);
        $codEventoDestino = intval($codEventoDestino);

        $this->conex->debug = $debug;

        // Buscar grupos do evento origem
        $sql = "SELECT cod_grupo FROM sis_ligacao_usuarios_grupos
                WHERE cod_usuario = " . $codUsuario . " AND cod_evento = " . $codEventoOrigem;
        $RS = $this->conex->Execute($sql);

        if ($RS->EOF) {
            throw new Exception("Usu&aacute;rio n&atilde;o possui grupos no evento de origem.");
        }

        $inseridos = 0;
        while (!$RS->EOF) {
            $codGrupo = intval($RS->fields['cod_grupo']);

            // Verificar se já existe no destino
            $sqlCheck = "SELECT COUNT(*) as total FROM sis_ligacao_usuarios_grupos
                         WHERE cod_usuario = " . $codUsuario . " AND cod_grupo = " . $codGrupo . " AND cod_evento = " . $codEventoDestino;
            $RSCheck = $this->conex->Execute($sqlCheck);

            if ($RSCheck->fields['total'] == 0) {
                $sqlInsert = "INSERT INTO sis_ligacao_usuarios_grupos (cod_usuario, cod_grupo, cod_evento)
                              VALUES (" . $codUsuario . ", " . $codGrupo . ", " . $codEventoDestino . ")";
                $this->conex->Execute($sqlInsert);
                $inseridos++;
            }

            $RS->MoveNext();
        }

        return $inseridos;
    }

    /**
     * Buscar dados de um usuário por ID
     */
    public function getUsuarioById($codUsuario, $debug = false)
    {
        $codUsuario = intval($codUsuario);
        $sql = "SELECT su.cod_usuario, COALESCE(tc.nome_convidado, su.nome_usuario) as nome, su.login, su.status, tc.fone_celular
                FROM sis_usuarios su
                LEFT JOIN tab_convidados tc ON su.cod_usuario = tc.cod_usuario
                WHERE su.cod_usuario = " . $codUsuario;

        $this->conex->debug = $debug;
        $RS = $this->conex->Execute($sql);

        if ($RS->EOF) {
            return false;
        }
        return $RS->fields;
    }

    /**
     * Atualizar dados de um usuário
     */
    public function atualizarUsuario($codUsuario, $nome, $login, $status, $celular = '', $debug = false)
    {
        $codUsuario = intval($codUsuario);

        $this->conex->debug = $debug;

        // Verificar se login já existe em outro usuário
        $sql = sprintf("SELECT COUNT(*) as total FROM sis_usuarios WHERE login = '%s' AND cod_usuario <> %d",
            addslashes($login), $codUsuario);
        $RS = $this->conex->Execute($sql);
        if ($RS->fields['total'] > 0) {
            throw new Exception("J&aacute; existe outro usu&aacute;rio com este login.");
        }

        $sql = sprintf("UPDATE sis_usuarios SET nome_usuario = '%s', login = '%s', status = '%s' WHERE cod_usuario = %d",
            addslashes($nome), addslashes($login), addslashes($status), $codUsuario);
        $this->conex->Execute($sql);

        // Atualizar tab_convidados (nome e celular)
        $sqlCheck = sprintf("SELECT COUNT(*) as total FROM tab_convidados WHERE cod_usuario = %d", $codUsuario);
        $RSCheck = $this->conex->Execute($sqlCheck);
        if ($RSCheck->fields['total'] > 0) {
            $sql = sprintf("UPDATE tab_convidados SET nome_convidado = '%s', fone_celular = '%s' WHERE cod_usuario = %d",
                addslashes($nome), addslashes($celular), $codUsuario);
        } else {
            $sql = sprintf("INSERT INTO tab_convidados (cod_usuario, nome_convidado, fone_celular) VALUES (%d, '%s', '%s')",
                $codUsuario, addslashes($nome), addslashes($celular));
        }
        $this->conex->Execute($sql);
    }

    /**
     * Select: gestores distintos
     */
    public function getGestoresDistintos($debug = false)
    {
        $sql = "SELECT DISTINCT gestor FROM tab_eventos WHERE gestor IS NOT NULL AND gestor <> '' ORDER BY gestor";

        $this->conex->debug = $debug;
        return $this->conex->Execute($sql);
    }

    /**
     * Select: eventos ativos
     */
    public function getEventosAtivos($debug = false)
    {
        $sql = "SELECT cod_evento, titulo FROM tab_eventos WHERE status = 'ATIVADO' ORDER BY cod_evento DESC";

        $this->conex->debug = $debug;
        return $this->conex->Execute($sql);
    }

    /**
     * Select: todos os usuários vinculados a algum evento
     */
    public function getTodosUsuarios($debug = false)
    {
        $sql = "SELECT su.cod_usuario, su.login,
                    COALESCE(tc.nome_convidado, su.login) as nome
                FROM sis_usuarios su
                LEFT JOIN tab_convidados tc ON su.cod_usuario = tc.cod_usuario
                WHERE su.status = 'ATIVADA'
                ORDER BY su.login";

        $this->conex->debug = $debug;
        return $this->conex->Execute($sql);
    }

    /**
     * Select: eventos disponíveis (não vinculados ao usuário)
     */
    public function getEventosDisponiveisUsuario($codUsuario, $debug = false)
    {
        $codUsuario = intval($codUsuario);
        $sql = "SELECT e.cod_evento, e.titulo
                FROM tab_eventos e
                WHERE e.status = 'ATIVADO'
                AND e.cod_evento NOT IN (
                    SELECT lue.cod_evento FROM sis_ligacao_usuarios_eventos lue
                    WHERE lue.cod_usuario = " . $codUsuario . "
                )
                ORDER BY e.cod_evento DESC";

        $this->conex->debug = $debug;
        return $this->conex->Execute($sql);
    }

    /**
     * Select: usuários de um evento específico
     */
    public function getUsuariosDoEvento($codEvento, $debug = false)
    {
        $codEvento = intval($codEvento);
        $sql = "SELECT su.cod_usuario, su.login
                FROM sis_usuarios su
                INNER JOIN sis_ligacao_usuarios_eventos lue ON su.cod_usuario = lue.cod_usuario
                WHERE lue.cod_evento = " . $codEvento . "
                ORDER BY su.login";

        $this->conex->debug = $debug;
        return $this->conex->Execute($sql);
    }

    public function getInscritoAssociado($codUsuario, $codEvento, $debug = false)
    {
        $sql = sprintf(
            "SELECT lue.cod_inscrito, ti.nome_inscrito, ti.e_mail, ti.cod_perfil
             FROM sis_ligacao_usuarios_eventos lue
             LEFT JOIN tab_inscritos ti ON ti.cod_inscrito = lue.cod_inscrito AND ti.cod_evento = lue.cod_evento
             WHERE lue.cod_usuario = %d AND lue.cod_evento = %d",
            intval($codUsuario), intval($codEvento)
        );
        $this->conex->debug = $debug;
        return $this->conex->Execute($sql);
    }

    public function buscarInscritoPorEmailOuCpf($email, $cpf, $codEvento, $debug = false)
    {
        $where = array();
        if ($email) $where[] = sprintf("ti.e_mail = '%s'", addslashes($email));
        if ($cpf)   $where[] = sprintf("ti.cpf = '%s'", addslashes($cpf));
        if (empty($where)) return null;

        $sql = sprintf(
            "SELECT ti.cod_inscrito, ti.nome_inscrito, ti.e_mail, ti.cpf, ti.cod_perfil, tp.perfil as nome_perfil
             FROM tab_inscritos ti
             LEFT JOIN tab_perfis tp ON tp.cod_perfil = ti.cod_perfil AND tp.cod_evento = ti.cod_evento
             WHERE (%s) AND ti.cod_evento = %d AND ti.status = 'ATIVADA' LIMIT 1",
            implode(' OR ', $where), intval($codEvento)
        );
        $this->conex->debug = $debug;
        return $this->conex->Execute($sql);
    }

    public function getDadosUsuarioParaInscricao($codUsuario, $debug = false)
    {
        $sql = sprintf(
            "SELECT su.cod_usuario, su.login as email, su.nome_usuario,
                    tc.nome_convidado, tc.cpf, tc.fone_celular
             FROM sis_usuarios su
             LEFT JOIN tab_convidados tc ON tc.cod_usuario = su.cod_usuario
             WHERE su.cod_usuario = %d",
            intval($codUsuario)
        );
        $this->conex->debug = $debug;
        return $this->conex->Execute($sql);
    }

    public function criarInscrito($codEvento, $nome, $email, $cpf, $codPerfil, $codUsuario, $debug = false)
    {
        $sql = sprintf(
            "INSERT INTO tab_inscritos (cod_evento, nome_inscrito, e_mail, cpf, cod_perfil, status, origem_cadastro, dat_incl, res_incl, ip_incl, versao)
             VALUES (%d, '%s', '%s', '%s', %d, 'ATIVADA', 'VINCULO_LOGIN', NOW(), %d, '%s', 1)",
            intval($codEvento),
            addslashes($nome),
            addslashes($email),
            addslashes($cpf),
            intval($codPerfil),
            intval($codUsuario),
            addslashes($_SERVER['REMOTE_ADDR'])
        );
        $this->conex->debug = $debug;
        $this->conex->Execute($sql);
        return $this->conex->Insert_ID();
    }

    public function atualizarCodInscrito($codUsuario, $codEvento, $codInscrito, $debug = false)
    {
        $sql = sprintf(
            "UPDATE sis_ligacao_usuarios_eventos SET cod_inscrito = %d WHERE cod_usuario = %d AND cod_evento = %d",
            intval($codInscrito), intval($codUsuario), intval($codEvento)
        );
        $this->conex->debug = $debug;
        return $this->conex->Execute($sql);
    }

    public function removerCodInscrito($codUsuario, $codEvento, $debug = false)
    {
        $sql = sprintf(
            "UPDATE sis_ligacao_usuarios_eventos SET cod_inscrito = NULL WHERE cod_usuario = %d AND cod_evento = %d",
            intval($codUsuario), intval($codEvento)
        );
        $this->conex->debug = $debug;
        return $this->conex->Execute($sql);
    }

    /**
     * Importa todos os usuários e seus grupos de um evento para outro
     * Retorna array com totais de usuarios e grupos importados
     */
    public function importarUsuariosDeEvento($codEventoOrigem, $codEventoDestino, $debug = false)
    {
        $codEventoOrigem = intval($codEventoOrigem);
        $codEventoDestino = intval($codEventoDestino);
        $totalUsuarios = 0;
        $totalGrupos = 0;

        // 1) Buscar usuarios do evento origem
        $sql = "SELECT DISTINCT cod_usuario FROM sis_ligacao_usuarios_eventos
                WHERE cod_evento = {$codEventoOrigem} AND status = 'ATIVADA'";
        $this->conex->debug = $debug;
        $rs = $this->conex->Execute($sql);

        if (!$rs || $rs->EOF) {
            return array('usuarios' => 0, 'grupos' => 0);
        }

        while (!$rs->EOF) {
            $codUsuario = intval($rs->fields['cod_usuario']);

            // 2) Ligar usuario ao evento destino (se ainda nao existe)
            $sqlVerifica = "SELECT COUNT(*) as total FROM sis_ligacao_usuarios_eventos
                           WHERE cod_usuario = {$codUsuario} AND cod_evento = {$codEventoDestino}";
            $rsV = $this->conex->Execute($sqlVerifica);
            if ($rsV->fields['total'] == 0) {
                $sqlInsert = "INSERT INTO sis_ligacao_usuarios_eventos (cod_evento, cod_usuario, status)
                             VALUES ({$codEventoDestino}, {$codUsuario}, 'ATIVADA')";
                $this->conex->Execute($sqlInsert);
                $totalUsuarios++;
            }

            // 3) Copiar grupos do usuario (do evento origem para destino, sem duplicar)
            $sqlGrupos = "INSERT INTO sis_ligacao_usuarios_grupos (cod_usuario, cod_grupo, cod_evento)
                         SELECT {$codUsuario}, cod_grupo, {$codEventoDestino}
                         FROM sis_ligacao_usuarios_grupos
                         WHERE cod_usuario = {$codUsuario} AND cod_evento = {$codEventoOrigem}
                           AND NOT EXISTS (
                             SELECT 1 FROM sis_ligacao_usuarios_grupos x
                             WHERE x.cod_usuario = {$codUsuario}
                               AND x.cod_grupo = sis_ligacao_usuarios_grupos.cod_grupo
                               AND x.cod_evento = {$codEventoDestino}
                           )";
            $this->conex->Execute($sqlGrupos);
            $totalGrupos += $this->conex->Affected_Rows();

            $rs->MoveNext();
        }

        // 4) Copiar direitos de grupos (permissoes do evento origem para destino, sem duplicar)
        $sqlDireitos = "INSERT INTO sis_direitos_grupos_sistemas (cod_grupo, cod_sistema, cod_direito, cod_evento)
                       SELECT cod_grupo, cod_sistema, cod_direito, {$codEventoDestino}
                       FROM sis_direitos_grupos_sistemas
                       WHERE cod_evento = {$codEventoOrigem}
                         AND NOT EXISTS (
                           SELECT 1 FROM sis_direitos_grupos_sistemas x
                           WHERE x.cod_grupo = sis_direitos_grupos_sistemas.cod_grupo
                             AND x.cod_sistema = sis_direitos_grupos_sistemas.cod_sistema
                             AND x.cod_evento = {$codEventoDestino}
                         )";
        $this->conex->Execute($sqlDireitos);

        return array('usuarios' => $totalUsuarios, 'grupos' => $totalGrupos);
    }
}

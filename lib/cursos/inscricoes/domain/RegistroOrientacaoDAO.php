<?php

class RegistroOrientacaoDAO extends DAO {

    public function __construct() {
        parent::__construct();
    }

    public function getById(RegistroOrientacao $obj, $debug = false) {
        try {
            $sql = sprintf("SELECT * FROM acad_registro_supervisor WHERE cod_evento='%s' AND cod_registro='%s'",
                $obj->getCodEvento(), $obj->getCodRegistro());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if ((!$rs) or ($rs->EOF)) {
                return false;
            } else {
                foreach ($rs as $rows) {}
                $obj->setAll($rows);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function getByIdExiste(RegistroOrientacao $obj, $debug = false) {
        try {
            $sql = sprintf("SELECT cod_registro FROM acad_registro_supervisor WHERE cod_evento='%s' AND cod_registro='%s'",
                $obj->getCodEvento(), $obj->getCodRegistro());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if ((!$rs) or ($rs->EOF)) {
                return false;
            } else {
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function getByDataExiste(RegistroOrientacao $obj, $debug = false) {
        try {
            $sql = sprintf("SELECT cod_registro FROM acad_registro_supervisor
                WHERE cod_evento='%s' AND cod_supervisor='%s' AND data_registro='%s' AND status='ATIVADA'",
                $obj->getCodEvento(), $obj->getCodSupervisor(), $obj->getDataRegistro());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if ((!$rs) or ($rs->EOF)) {
                return false;
            } else {
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function getByData(RegistroOrientacao $obj, $debug = false) {
        try {
            $sql = sprintf("SELECT * FROM acad_registro_supervisor
                WHERE cod_evento='%s' AND cod_supervisor='%s' AND data_registro='%s' AND status='ATIVADA'
                LIMIT 1",
                $obj->getCodEvento(), $obj->getCodSupervisor(), $obj->getDataRegistro());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if ((!$rs) or ($rs->EOF)) {
                return false;
            } else {
                foreach ($rs as $rows) {}
                $obj->setAll($rows);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function carregarListaBySupervisor(RegistroOrientacao $obj, $debug = false) {
        try {
            $sql = sprintf("SELECT r.*,
                    (SELECT COUNT(*) FROM acad_registro_supervisor_pes p WHERE p.cod_registro = r.cod_registro AND p.cod_evento = r.cod_evento AND p.status = 'ATIVADA') AS qtd_pes,
                    (SELECT COUNT(*) FROM acad_registro_supervisor_crianca c WHERE c.cod_registro = r.cod_registro AND c.cod_evento = r.cod_evento AND c.status = 'ATIVADA') AS qtd_crianca
                FROM acad_registro_supervisor r
                WHERE r.cod_evento='%s' AND r.cod_supervisor='%s' AND r.status='ATIVADA'
                ORDER BY r.data_registro DESC",
                $obj->getCodEvento(), $obj->getCodSupervisor());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            $registros = array();
            if ($rs && !$rs->EOF) {
                foreach ($rs as $row) {
                    $registros[] = $row;
                }
            }
            return $registros;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return array();
        }
    }

    public function insert(RegistroOrientacao $obj, $debug = false) {
        try {
            $obj->setDataInclusao(date('Y-m-d H:i:s'));
            $obj->setIdInclusao($_SESSION['_cod_usuario']);
            $obj->setSessionIdInclusao(isset($_SESSION['userLogado']['_cod_inscrito']) ? $_SESSION['userLogado']['_cod_inscrito'] : '');
            $obj->setIpInclusao($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentInclusao($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao(1);
            $obj->setStatus('ATIVADA');

            $sql = "SELECT * FROM acad_registro_supervisor WHERE cod_evento=" . $obj->getCodEvento() . " AND cod_registro=0";
            $rs = $this->conex->Execute($sql);

            $arrayColunas = $this->conex->MetaColumnNames("acad_registro_supervisor");
            $fws_ = $obj->getAllForArray($arrayColunas, false);

            $insertSQL = $this->conex->GetInsertSQL($rs, $fws_, true, true);
            $sql = sprintf($insertSQL);

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $obj->setCodRegistro($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function update(RegistroOrientacao $obj, $debug = false) {
        try {
            $obj->setDataAlteracao(date('Y-m-d H:i:s'));
            $obj->setIdAlteracao($_SESSION['_cod_usuario']);
            $obj->setSessionIdAlteracao(isset($_SESSION['userLogado']['_cod_inscrito']) ? $_SESSION['userLogado']['_cod_inscrito'] : '');
            $obj->setIpAlteracao($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentAlteracao($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao($obj->getVersao() + 1);
            $obj->setStatus('ATIVADA');

            $sql = "SELECT * FROM acad_registro_supervisor WHERE cod_evento=" . $obj->getCodEvento() . " AND cod_registro=" . $obj->getCodRegistro();
            $rs = $this->conex->Execute($sql);
            $arrayColunas = $this->conex->MetaColumnNames("acad_registro_supervisor");
            $fws_ = $obj->getAllForArray($arrayColunas, false);
            $updateSQL = $this->conex->GetUpdateSQL($rs, $fws_, true, false, true);

            $sql = sprintf($updateSQL);

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function delete(RegistroOrientacao $obj, $debug = false) {
        try {
            $sql = sprintf("DELETE FROM acad_registro_supervisor WHERE cod_evento='%s' AND cod_registro='%s'",
                $obj->getCodEvento(), $obj->getCodRegistro());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    // --- Itens PES (operações item-a-item) ---

    public function insertItemPes($codRegistro, $codEvento, $item, $debug = false) {
        try {
            $sql = "INSERT INTO acad_registro_supervisor_pes
                (cod_registro, cod_evento, cod_agente, atividades_pes, descricao_pes, orientacoes_pes, status, data_inclusao, id_inclusao)
                VALUES ("
                . intval($codRegistro) . ", "
                . intval($codEvento) . ", "
                . intval($item['cod_agente']) . ", "
                . $this->conex->qstr($item['atividades_pes']) . ", "
                . $this->conex->qstr($item['descricao_pes']) . ", "
                . $this->conex->qstr($item['orientacoes_pes']) . ", "
                . "'ATIVADA', "
                . $this->conex->qstr(date('Y-m-d H:i:s')) . ", "
                . intval($_SESSION['_cod_usuario'])
                . ")";
            $this->conex->debug = $debug;
            $this->conex->Execute($sql);
            return $this->conex->Insert_ID();
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function updateItemPes($codRegistroPes, $codEvento, $item, $debug = false) {
        try {
            $sql = "UPDATE acad_registro_supervisor_pes SET "
                . "cod_agente = " . intval($item['cod_agente']) . ", "
                . "atividades_pes = " . $this->conex->qstr($item['atividades_pes']) . ", "
                . "descricao_pes = " . $this->conex->qstr($item['descricao_pes']) . ", "
                . "orientacoes_pes = " . $this->conex->qstr($item['orientacoes_pes'])
                . " WHERE cod_registro_pes = " . intval($codRegistroPes)
                . " AND cod_evento = " . intval($codEvento);
            $this->conex->debug = $debug;
            $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function deleteItemPes($codRegistroPes, $codEvento, $debug = false) {
        try {
            $sql = sprintf("DELETE FROM acad_registro_supervisor_pes WHERE cod_registro_pes = %d AND cod_evento = %d",
                intval($codRegistroPes), intval($codEvento));
            $this->conex->debug = $debug;
            $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function getItemPesById($codRegistroPes, $codEvento, $debug = false) {
        try {
            $sql = sprintf("SELECT p.*, i.nome_inscrito AS nome_pes
                FROM acad_registro_supervisor_pes p
                LEFT JOIN tab_inscritos i ON i.cod_inscrito = p.cod_agente AND i.cod_evento = p.cod_evento
                WHERE p.cod_registro_pes = %d AND p.cod_evento = %d AND p.status = 'ATIVADA'",
                intval($codRegistroPes), intval($codEvento));
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if (!$rs || $rs->EOF) return null;
            foreach ($rs as $row) { return $row; }
            return null;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return null;
        }
    }

    // --- Itens PES (operações em lote - mantidas por compatibilidade) ---

    public function salvarItensPes($codRegistro, $codEvento, $itens) {
        if (empty($itens)) return;
        try {
            foreach ($itens as $item) {
                $sql = "INSERT INTO acad_registro_supervisor_pes
                    (cod_registro, cod_evento, cod_agente, atividades_pes, descricao_pes, orientacoes_pes, status, data_inclusao, id_inclusao)
                    VALUES ("
                    . intval($codRegistro) . ", "
                    . intval($codEvento) . ", "
                    . intval($item['cod_agente']) . ", "
                    . $this->conex->qstr($item['atividades_pes']) . ", "
                    . $this->conex->qstr($item['descricao_pes']) . ", "
                    . $this->conex->qstr($item['orientacoes_pes']) . ", "
                    . "'ATIVADA', "
                    . $this->conex->qstr(date('Y-m-d H:i:s')) . ", "
                    . intval($_SESSION['_cod_usuario'])
                    . ")";
                $this->conex->Execute($sql);
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
        }
    }

    public function excluirItensPes($codRegistro, $codEvento) {
        try {
            $sql = sprintf("DELETE FROM acad_registro_supervisor_pes WHERE cod_registro='%s' AND cod_evento='%s'",
                addslashes($codRegistro), addslashes($codEvento));
            $this->conex->Execute($sql);
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
        }
    }

    public function carregarItensPes($codRegistro, $codEvento, $debug = false) {
        try {
            $sql = sprintf("SELECT p.*, i.nome_inscrito AS nome_pes
                FROM acad_registro_supervisor_pes p
                LEFT JOIN tab_inscritos i ON i.cod_inscrito = p.cod_agente AND i.cod_evento = p.cod_evento
                WHERE p.cod_registro='%s' AND p.cod_evento='%s' AND p.status='ATIVADA'
                ORDER BY p.cod_registro_pes ASC",
                addslashes($codRegistro), addslashes($codEvento));

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            $itens = array();
            if ($rs && !$rs->EOF) {
                foreach ($rs as $row) {
                    $itens[] = $row;
                }
            }
            return $itens;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return array();
        }
    }

    // --- Itens Crianca (operações item-a-item) ---

    public function insertItemCrianca($codRegistro, $codEvento, $item, $debug = false) {
        try {
            $orientacoes = isset($item['orientacoes_crianca']) ? $item['orientacoes_crianca'] : '';
            $orientacoesCript = ($orientacoes !== null && $orientacoes !== '')
                ? CriptografiaHelper::criptografar($orientacoes) : '';
            $sql = "INSERT INTO acad_registro_supervisor_crianca
                (cod_registro, cod_evento, cod_aluno, atividades_crianca, descricao_crianca, orientacoes_crianca, status, data_inclusao, id_inclusao)
                VALUES ("
                . intval($codRegistro) . ", "
                . intval($codEvento) . ", "
                . $this->conex->qstr($item['cod_aluno']) . ", "
                . $this->conex->qstr($item['atividades_crianca']) . ", "
                . $this->conex->qstr($item['descricao_crianca']) . ", "
                . $this->conex->qstr($orientacoesCript) . ", "
                . "'ATIVADA', "
                . $this->conex->qstr(date('Y-m-d H:i:s')) . ", "
                . intval($_SESSION['_cod_usuario'])
                . ")";
            $this->conex->debug = $debug;
            $this->conex->Execute($sql);
            return $this->conex->Insert_ID();
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function updateItemCrianca($codRegistroCrianca, $codEvento, $item, $debug = false) {
        try {
            $orientacoes = isset($item['orientacoes_crianca']) ? $item['orientacoes_crianca'] : '';
            $orientacoesCript = ($orientacoes !== null && $orientacoes !== '')
                ? CriptografiaHelper::criptografar($orientacoes) : '';
            $sql = "UPDATE acad_registro_supervisor_crianca SET "
                . "cod_aluno = " . $this->conex->qstr($item['cod_aluno']) . ", "
                . "atividades_crianca = " . $this->conex->qstr($item['atividades_crianca']) . ", "
                . "descricao_crianca = " . $this->conex->qstr($item['descricao_crianca']) . ", "
                . "orientacoes_crianca = " . $this->conex->qstr($orientacoesCript)
                . " WHERE cod_registro_crianca = " . intval($codRegistroCrianca)
                . " AND cod_evento = " . intval($codEvento);
            $this->conex->debug = $debug;
            $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function deleteItemCrianca($codRegistroCrianca, $codEvento, $debug = false) {
        try {
            $sql = sprintf("DELETE FROM acad_registro_supervisor_crianca WHERE cod_registro_crianca = %d AND cod_evento = %d",
                intval($codRegistroCrianca), intval($codEvento));
            $this->conex->debug = $debug;
            $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function getItemCriancaById($codRegistroCrianca, $codEvento, $debug = false) {
        try {
            $sql = sprintf("SELECT c.*, a.iniciais, a.codigo_exibicao, a.nivel, a.serie_turma, a.turno,
                    col.nome AS nome_colegio
                FROM acad_registro_supervisor_crianca c
                INNER JOIN acad_aluno_contexto a ON a.cod_aluno = c.cod_aluno AND a.cod_evento = c.cod_evento
                INNER JOIN acad_colegio col ON col.cod_colegio = a.cod_colegio AND col.cod_evento = a.cod_evento
                WHERE c.cod_registro_crianca = %d AND c.cod_evento = %d AND c.status = 'ATIVADA'",
                intval($codRegistroCrianca), intval($codEvento));
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if (!$rs || $rs->EOF) return null;
            foreach ($rs as $row) {
                if (!empty($row['orientacoes_crianca'])) {
                    $row['orientacoes_crianca'] = CriptografiaHelper::descriptografar($row['orientacoes_crianca']);
                }
                return $row;
            }
            return null;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return null;
        }
    }

    // --- Itens Crianca (operações em lote - mantidas por compatibilidade) ---

    public function salvarItensCrianca($codRegistro, $codEvento, $itens) {
        if (empty($itens)) return;
        try {
            foreach ($itens as $item) {
                $sql = "INSERT INTO acad_registro_supervisor_crianca
                    (cod_registro, cod_evento, cod_aluno, atividades_crianca, descricao_crianca, status, data_inclusao, id_inclusao)
                    VALUES ("
                    . intval($codRegistro) . ", "
                    . intval($codEvento) . ", "
                    . intval($item['cod_aluno']) . ", "
                    . $this->conex->qstr($item['atividades_crianca']) . ", "
                    . $this->conex->qstr($item['descricao_crianca']) . ", "
                    . "'ATIVADA', "
                    . $this->conex->qstr(date('Y-m-d H:i:s')) . ", "
                    . intval($_SESSION['_cod_usuario'])
                    . ")";
                $this->conex->Execute($sql);
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
        }
    }

    public function excluirItensCrianca($codRegistro, $codEvento) {
        try {
            $sql = sprintf("DELETE FROM acad_registro_supervisor_crianca WHERE cod_registro='%s' AND cod_evento='%s'",
                addslashes($codRegistro), addslashes($codEvento));
            $this->conex->Execute($sql);
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
        }
    }

    public function carregarItensCrianca($codRegistro, $codEvento, $debug = false) {
        try {
            $sql = sprintf("SELECT c.*, a.iniciais, a.codigo_exibicao, a.serie_turma, a.turno, col.nome AS nome_colegio
                FROM acad_registro_supervisor_crianca c
                INNER JOIN acad_aluno_contexto a ON a.cod_aluno = c.cod_aluno AND a.cod_evento = c.cod_evento
                INNER JOIN acad_colegio col ON col.cod_colegio = a.cod_colegio AND col.cod_evento = a.cod_evento
                WHERE c.cod_registro='%s' AND c.cod_evento='%s' AND c.status='ATIVADA'
                ORDER BY c.cod_registro_crianca ASC",
                addslashes($codRegistro), addslashes($codEvento));

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            $itens = array();
            if ($rs && !$rs->EOF) {
                foreach ($rs as $row) {
                    if (!empty($row['orientacoes_crianca'])) {
                        $row['orientacoes_crianca'] = CriptografiaHelper::descriptografar($row['orientacoes_crianca']);
                    }
                    $itens[] = $row;
                }
            }
            return $itens;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return array();
        }
    }

    // --- Orientacoes para o PES (visualizacao na area do inscrito) ---

    public function carregarOrientacoesPorPes($codEvento, $codInscritoPes, $debug = false) {
        try {
            $sql = sprintf("SELECT p.cod_registro_pes, p.cod_agente, p.orientacoes_pes,
                    p.data_leitura_pes,
                    r.cod_registro, r.data_registro, r.cod_supervisor,
                    COALESCE(tc.nome_convidado, su.nome_usuario, su.login) AS nome_supervisor
                FROM acad_registro_supervisor_pes p
                INNER JOIN acad_registro_supervisor r ON r.cod_registro = p.cod_registro AND r.cod_evento = p.cod_evento
                LEFT JOIN sis_usuarios su ON su.cod_usuario = r.cod_supervisor
                LEFT JOIN tab_convidados tc ON tc.cod_usuario = su.cod_usuario
                WHERE p.cod_evento = %d
                    AND p.cod_agente = %d
                    AND p.status = 'ATIVADA'
                    AND r.status = 'ATIVADA'
                    AND p.orientacoes_pes IS NOT NULL
                    AND TRIM(p.orientacoes_pes) <> ''
                ORDER BY r.data_registro DESC, p.cod_registro_pes DESC",
                intval($codEvento), intval($codInscritoPes));

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            $lista = array();
            if ($rs && !$rs->EOF) {
                foreach ($rs as $row) {
                    $lista[] = $row;
                }
            }
            return $lista;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return array();
        }
    }

    public function contarOrientacoesNaoLidas($codEvento, $codInscritoPes, $debug = false) {
        try {
            $sql = sprintf("SELECT COUNT(*) AS total
                FROM acad_registro_supervisor_pes p
                INNER JOIN acad_registro_supervisor r ON r.cod_registro = p.cod_registro AND r.cod_evento = p.cod_evento
                WHERE p.cod_evento = %d
                    AND p.cod_agente = %d
                    AND p.status = 'ATIVADA'
                    AND r.status = 'ATIVADA'
                    AND p.orientacoes_pes IS NOT NULL
                    AND TRIM(p.orientacoes_pes) <> ''
                    AND p.data_leitura_pes IS NULL",
                intval($codEvento), intval($codInscritoPes));

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if (!$rs || $rs->EOF) return 0;
            foreach ($rs as $row) { return intval($row['total']); }
            return 0;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return 0;
        }
    }

    // Marca como lida apenas se ainda nao estava (primeira leitura).
    // Valida cod_agente no WHERE para impedir que um PES marque orientacao de outro.
    public function marcarOrientacaoComoLida($codRegistroPes, $codEvento, $codInscritoPes, $debug = false) {
        try {
            $sql = sprintf("UPDATE acad_registro_supervisor_pes
                SET data_leitura_pes = %s, ip_leitura_pes = %s
                WHERE cod_registro_pes = %d
                    AND cod_evento = %d
                    AND cod_agente = %d
                    AND status = 'ATIVADA'
                    AND data_leitura_pes IS NULL",
                $this->conex->qstr(date('Y-m-d H:i:s')),
                $this->conex->qstr(isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : ''),
                intval($codRegistroPes), intval($codEvento), intval($codInscritoPes));

            $this->conex->debug = $debug;
            $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    // --- Orientacoes por crianca (visualizacao na area do PES) ---

    public function carregarOrientacoesCriancaPorPes($codEvento, $codInscritoPes, $debug = false) {
        try {
            $sql = sprintf("SELECT c.cod_registro_crianca, c.cod_aluno, c.orientacoes_crianca,
                    c.data_leitura_crianca,
                    r.cod_registro, r.data_registro, r.cod_supervisor,
                    a.iniciais, a.codigo_exibicao, a.serie_turma, a.turno,
                    col.nome AS nome_colegio,
                    COALESCE(tc.nome_convidado, su.nome_usuario, su.login) AS nome_supervisor
                FROM acad_registro_supervisor_crianca c
                INNER JOIN acad_registro_supervisor r ON r.cod_registro = c.cod_registro AND r.cod_evento = c.cod_evento
                INNER JOIN acad_lig_agente_aluno v ON v.cod_aluno = c.cod_aluno AND v.cod_evento = c.cod_evento AND v.status = 'ATIVADA'
                INNER JOIN acad_aluno_contexto a ON a.cod_aluno = c.cod_aluno AND a.cod_evento = c.cod_evento
                INNER JOIN acad_colegio col ON col.cod_colegio = a.cod_colegio AND col.cod_evento = a.cod_evento
                LEFT JOIN sis_usuarios su ON su.cod_usuario = r.cod_supervisor
                LEFT JOIN tab_convidados tc ON tc.cod_usuario = su.cod_usuario
                WHERE c.cod_evento = %d
                    AND v.cod_agente = %d
                    AND c.status = 'ATIVADA'
                    AND r.status = 'ATIVADA'
                    AND c.orientacoes_crianca IS NOT NULL
                    AND TRIM(c.orientacoes_crianca) <> ''
                ORDER BY r.data_registro DESC, c.cod_registro_crianca DESC",
                intval($codEvento), intval($codInscritoPes));

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            $lista = array();
            if ($rs && !$rs->EOF) {
                foreach ($rs as $row) {
                    if (!empty($row['orientacoes_crianca'])) {
                        $row['orientacoes_crianca'] = CriptografiaHelper::descriptografar($row['orientacoes_crianca']);
                    }
                    $lista[] = $row;
                }
            }
            return $lista;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return array();
        }
    }

    public function contarOrientacoesCriancaNaoLidas($codEvento, $codInscritoPes, $debug = false) {
        try {
            $sql = sprintf("SELECT COUNT(*) AS total
                FROM acad_registro_supervisor_crianca c
                INNER JOIN acad_registro_supervisor r ON r.cod_registro = c.cod_registro AND r.cod_evento = c.cod_evento
                INNER JOIN acad_lig_agente_aluno v ON v.cod_aluno = c.cod_aluno AND v.cod_evento = c.cod_evento AND v.status = 'ATIVADA'
                WHERE c.cod_evento = %d
                    AND v.cod_agente = %d
                    AND c.status = 'ATIVADA'
                    AND r.status = 'ATIVADA'
                    AND c.orientacoes_crianca IS NOT NULL
                    AND TRIM(c.orientacoes_crianca) <> ''
                    AND c.data_leitura_crianca IS NULL",
                intval($codEvento), intval($codInscritoPes));

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if (!$rs || $rs->EOF) return 0;
            foreach ($rs as $row) { return intval($row['total']); }
            return 0;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return 0;
        }
    }

    // Marca como lida apenas se ainda nao estava (primeira leitura).
    // Valida via acad_lig_agente_aluno que o PES e agente da crianca.
    public function marcarOrientacaoCriancaComoLida($codRegistroCrianca, $codEvento, $codInscritoPes, $debug = false) {
        try {
            $sql = sprintf("UPDATE acad_registro_supervisor_crianca c
                INNER JOIN acad_lig_agente_aluno v ON v.cod_aluno = c.cod_aluno AND v.cod_evento = c.cod_evento AND v.status = 'ATIVADA'
                SET c.data_leitura_crianca = %s, c.ip_leitura_crianca = %s
                WHERE c.cod_registro_crianca = %d
                    AND c.cod_evento = %d
                    AND v.cod_agente = %d
                    AND c.status = 'ATIVADA'
                    AND c.data_leitura_crianca IS NULL",
                $this->conex->qstr(date('Y-m-d H:i:s')),
                $this->conex->qstr(isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : ''),
                intval($codRegistroCrianca), intval($codEvento), intval($codInscritoPes));

            $this->conex->debug = $debug;
            $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    // --- Listas auxiliares ---

    public function carregarPesDasEscolasSupervisor($codEvento, $codSupervisor, $debug = false) {
        try {
            // Retorna 1 linha por (PES, colegio onde atende), pra permitir filtro por colegio no select.
            // Um mesmo PES pode aparecer em mais de um colegio quando atende alunos de escolas diferentes
            // cobertas pelo supervisor.
            $sql = sprintf("SELECT DISTINCT i.cod_inscrito, i.nome_inscrito, a.cod_colegio, c.nome AS nome_colegio
                FROM acad_lig_supervisor_colegio vsc
                INNER JOIN acad_aluno_contexto a ON a.cod_colegio = vsc.cod_colegio AND a.cod_evento = vsc.cod_evento AND a.status = 'ATIVADA'
                INNER JOIN acad_lig_agente_aluno v ON v.cod_aluno = a.cod_aluno AND v.cod_evento = a.cod_evento AND v.status = 'ATIVADA'
                INNER JOIN tab_inscritos i ON i.cod_inscrito = v.cod_agente AND i.cod_evento = v.cod_evento AND i.status = 'ATIVADA'
                INNER JOIN acad_colegio c ON c.cod_colegio = a.cod_colegio AND c.cod_evento = a.cod_evento
                WHERE vsc.cod_evento = '%s' AND vsc.cod_supervisor = '%s' AND vsc.status = 'ATIVADA'
                ORDER BY c.nome, i.nome_inscrito",
                addslashes($codEvento), addslashes($codSupervisor));

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            // Agrupa pra que cada PES vire 1 item com array 'cod_colegios' (pra filtro no front).
            $pesMap = array();
            if ($rs && !$rs->EOF) {
                foreach ($rs as $row) {
                    $codInscrito = $row['cod_inscrito'];
                    if (!isset($pesMap[$codInscrito])) {
                        $pesMap[$codInscrito] = array(
                            'cod_inscrito'  => $codInscrito,
                            'nome_inscrito' => $row['nome_inscrito'],
                            'cod_colegios'  => array()
                        );
                    }
                    $pesMap[$codInscrito]['cod_colegios'][] = $row['cod_colegio'];
                }
            }
            return array_values($pesMap);
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return array();
        }
    }

    public function carregarCriancasDasEscolasSupervisor($codEvento, $codSupervisor, $debug = false) {
        try {
            $sql = sprintf("SELECT a.cod_aluno, a.cod_colegio, a.iniciais, a.codigo_exibicao,
                    a.nivel, a.serie_turma, a.turno,
                    c.nome AS nome_colegio
                FROM acad_lig_supervisor_colegio vsc
                INNER JOIN acad_aluno_contexto a ON a.cod_colegio = vsc.cod_colegio AND a.cod_evento = vsc.cod_evento AND a.status = 'ATIVADA'
                INNER JOIN acad_colegio c ON c.cod_colegio = a.cod_colegio AND c.cod_evento = a.cod_evento
                WHERE vsc.cod_evento = '%s' AND vsc.cod_supervisor = '%s' AND vsc.status = 'ATIVADA'
                ORDER BY c.nome, CAST(SUBSTRING_INDEX(a.codigo_exibicao, '-', -1) AS UNSIGNED)",
                addslashes($codEvento), addslashes($codSupervisor));

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            $lista = array();
            if ($rs && !$rs->EOF) {
                foreach ($rs as $row) {
                    $lista[] = $row;
                }
            }
            return $lista;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return array();
        }
    }

    public function getNomeSupervisor($codUsuario, $debug = false) {
        try {
            $sql = sprintf("SELECT COALESCE(tc.nome_convidado, su.nome_usuario, su.login) AS nome
                FROM sis_usuarios su
                LEFT JOIN tab_convidados tc ON tc.cod_usuario = su.cod_usuario
                WHERE su.cod_usuario = %d",
                intval($codUsuario));
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if (!$rs || $rs->EOF) return '';
            foreach ($rs as $row) { return $row['nome']; }
            return '';
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return '';
        }
    }

    // Exportacao por periodo: retorna linhas pre-mescladas (DIA + PES) para CSV.
    // Linhas DIA primeiro, depois 1 linha por PES dentro do mesmo dia.
    // $codSupervisor vazio/0 = todos os supervisores (ordenados por nome).
    public function getRegistrosParaExportacao($codEvento, $codSupervisor, $dataInicio, $dataFim, $debug = false) {
        $linhas = array();
        try {
            $codEvento = intval($codEvento);
            $codSupervisor = intval($codSupervisor);
            $dataInicioSql = $this->conex->qstr($dataInicio);
            $dataFimSql = $this->conex->qstr($dataFim);

            $filtroSupervisor = $codSupervisor > 0
                ? sprintf(" AND r.cod_supervisor = %d", $codSupervisor)
                : "";

            $sqlDias = sprintf("SELECT r.cod_registro, r.cod_supervisor, r.data_registro, r.atividades_dia,
                    COALESCE(tc.nome_convidado, su.nome_usuario, su.login) AS nome_supervisor
                FROM acad_registro_supervisor r
                LEFT JOIN sis_usuarios su ON su.cod_usuario = r.cod_supervisor
                LEFT JOIN tab_convidados tc ON tc.cod_usuario = su.cod_usuario
                WHERE r.cod_evento = %d
                    %s
                    AND r.status = 'ATIVADA'
                    AND r.data_registro BETWEEN %s AND %s
                ORDER BY nome_supervisor ASC, r.data_registro ASC, r.cod_registro ASC",
                $codEvento, $filtroSupervisor, $dataInicioSql, $dataFimSql);

            $this->conex->debug = $debug;
            $rsDias = $this->conex->Execute($sqlDias);

            $dias = array();
            if ($rsDias && !$rsDias->EOF) {
                foreach ($rsDias as $row) {
                    $dias[$row['cod_registro']] = $row;
                }
            }
            if (empty($dias)) {
                return array();
            }

            $idsRegistros = implode(',', array_map('intval', array_keys($dias)));
            $sqlPes = sprintf("SELECT p.cod_registro, p.atividades_pes, p.descricao_pes, p.orientacoes_pes,
                    i.nome_inscrito AS nome_pes
                FROM acad_registro_supervisor_pes p
                LEFT JOIN tab_inscritos i ON i.cod_inscrito = p.cod_agente AND i.cod_evento = p.cod_evento
                WHERE p.cod_evento = %d
                    AND p.status = 'ATIVADA'
                    AND p.cod_registro IN (%s)
                ORDER BY p.cod_registro ASC, p.cod_registro_pes ASC",
                $codEvento, $idsRegistros);

            $rsPes = $this->conex->Execute($sqlPes);
            $pesPorRegistro = array();
            if ($rsPes && !$rsPes->EOF) {
                foreach ($rsPes as $row) {
                    $cod = $row['cod_registro'];
                    if (!isset($pesPorRegistro[$cod])) {
                        $pesPorRegistro[$cod] = array();
                    }
                    $pesPorRegistro[$cod][] = $row;
                }
            }

            foreach ($dias as $codRegistro => $dia) {
                $linhas[] = array(
                    'tipo'             => 'DIA',
                    'data_registro'    => $dia['data_registro'],
                    'nome_supervisor'  => $dia['nome_supervisor'],
                    'nome_pes'         => '',
                    'atividades'       => $dia['atividades_dia'],
                    'descricao'        => '',
                    'orientacoes_pes'  => ''
                );
                if (!empty($pesPorRegistro[$codRegistro])) {
                    foreach ($pesPorRegistro[$codRegistro] as $pes) {
                        $linhas[] = array(
                            'tipo'             => 'PES',
                            'data_registro'    => $dia['data_registro'],
                            'nome_supervisor'  => $dia['nome_supervisor'],
                            'nome_pes'         => $pes['nome_pes'],
                            'atividades'       => $pes['atividades_pes'],
                            'descricao'        => $pes['descricao_pes'],
                            'orientacoes_pes'  => $pes['orientacoes_pes']
                        );
                    }
                }
            }
            return $linhas;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return array();
        }
    }

    // --- Consulta consolidada (admin): por PES e por Aluno ---
    // Reúne, num só lugar, o que hoje só aparece espalhado dia a dia.
    // "Por PES" reaproveita carregarOrientacoesPorPes(); aqui ficam as listas dos
    // seletores e a consulta por aluno (espelho da por PES, mas na orientacao_crianca).

    // PES (agentes) que possuem ao menos uma orientação registrada (de qualquer supervisor).
    public function carregarPesComOrientacoes($codEvento, $debug = false) {
        try {
            $sql = sprintf("SELECT DISTINCT p.cod_agente, i.nome_inscrito
                FROM acad_registro_supervisor_pes p
                INNER JOIN acad_registro_supervisor r ON r.cod_registro = p.cod_registro AND r.cod_evento = p.cod_evento
                LEFT JOIN tab_inscritos i ON i.cod_inscrito = p.cod_agente AND i.cod_evento = p.cod_evento
                WHERE p.cod_evento = %d
                    AND p.status = 'ATIVADA'
                    AND r.status = 'ATIVADA'
                    AND p.orientacoes_pes IS NOT NULL
                    AND TRIM(p.orientacoes_pes) <> ''
                ORDER BY i.nome_inscrito",
                intval($codEvento));

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            $lista = array();
            if ($rs && !$rs->EOF) {
                foreach ($rs as $row) {
                    $lista[] = $row;
                }
            }
            return $lista;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return array();
        }
    }

    // Alunos (crianças) que possuem ao menos uma orientação registrada (de qualquer supervisor).
    public function carregarAlunosComOrientacoes($codEvento, $debug = false) {
        try {
            $sql = sprintf("SELECT DISTINCT c.cod_aluno, a.iniciais, a.codigo_exibicao,
                    a.serie_turma, a.turno, col.nome AS nome_colegio
                FROM acad_registro_supervisor_crianca c
                INNER JOIN acad_registro_supervisor r ON r.cod_registro = c.cod_registro AND r.cod_evento = c.cod_evento
                INNER JOIN acad_aluno_contexto a ON a.cod_aluno = c.cod_aluno AND a.cod_evento = c.cod_evento
                INNER JOIN acad_colegio col ON col.cod_colegio = a.cod_colegio AND col.cod_evento = a.cod_evento
                WHERE c.cod_evento = %d
                    AND c.status = 'ATIVADA'
                    AND r.status = 'ATIVADA'
                    AND c.orientacoes_crianca IS NOT NULL
                    AND TRIM(c.orientacoes_crianca) <> ''
                ORDER BY col.nome, a.codigo_exibicao",
                intval($codEvento));

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            $lista = array();
            if ($rs && !$rs->EOF) {
                foreach ($rs as $row) {
                    $lista[] = $row;
                }
            }
            return $lista;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return array();
        }
    }

    // Todas as orientações registradas sobre um aluno, de todos os supervisores (ordem desc por data).
    // Espelho de carregarOrientacoesPorPes(), mas keyed por aluno na orientacao_crianca.
    public function carregarOrientacoesPorAluno($codEvento, $codAluno, $debug = false) {
        try {
            $sql = sprintf("SELECT c.cod_registro_crianca, c.cod_aluno, c.orientacoes_crianca,
                    c.data_leitura_crianca,
                    r.cod_registro, r.data_registro, r.cod_supervisor,
                    a.iniciais, a.codigo_exibicao, a.serie_turma, a.turno,
                    col.nome AS nome_colegio,
                    COALESCE(tc.nome_convidado, su.nome_usuario, su.login) AS nome_supervisor
                FROM acad_registro_supervisor_crianca c
                INNER JOIN acad_registro_supervisor r ON r.cod_registro = c.cod_registro AND r.cod_evento = c.cod_evento
                INNER JOIN acad_aluno_contexto a ON a.cod_aluno = c.cod_aluno AND a.cod_evento = c.cod_evento
                INNER JOIN acad_colegio col ON col.cod_colegio = a.cod_colegio AND col.cod_evento = a.cod_evento
                LEFT JOIN sis_usuarios su ON su.cod_usuario = r.cod_supervisor
                LEFT JOIN tab_convidados tc ON tc.cod_usuario = su.cod_usuario
                WHERE c.cod_evento = %d
                    AND c.cod_aluno = %s
                    AND c.status = 'ATIVADA'
                    AND r.status = 'ATIVADA'
                    AND c.orientacoes_crianca IS NOT NULL
                    AND TRIM(c.orientacoes_crianca) <> ''
                ORDER BY r.data_registro DESC, c.cod_registro_crianca DESC",
                intval($codEvento), $this->conex->qstr($codAluno));

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            $lista = array();
            if ($rs && !$rs->EOF) {
                foreach ($rs as $row) {
                    if (!empty($row['orientacoes_crianca'])) {
                        $row['orientacoes_crianca'] = CriptografiaHelper::descriptografar($row['orientacoes_crianca']);
                    }
                    $lista[] = $row;
                }
            }
            return $lista;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return array();
        }
    }

    public function carregarListaSupervisores($codEvento, $debug = false) {
        try {
            // Buscar cod_grupo do supervisor no evento
            $sqlGrupo = sprintf("SELECT dgs.cod_grupo FROM sis_direitos_grupos_sistemas dgs
                WHERE dgs.cod_sistema = %s AND dgs.cod_evento = %s LIMIT 1",
                enumSistema::SIS_PEDAGOGICO_SUPERVISOR, addslashes($codEvento));
            $rsGrupo = $this->conex->Execute($sqlGrupo);
            if (!$rsGrupo || $rsGrupo->EOF) {
                return array();
            }
            $codGrupo = intval($rsGrupo->fields['cod_grupo']);

            $sql = sprintf("SELECT DISTINCT su.cod_usuario, COALESCE(tc.nome_convidado, su.nome_usuario, su.login) as nome
                FROM sis_usuarios su
                INNER JOIN sis_ligacao_usuarios_eventos lue ON su.cod_usuario = lue.cod_usuario AND lue.cod_evento = %d
                INNER JOIN sis_ligacao_usuarios_grupos lug ON su.cod_usuario = lug.cod_usuario AND lug.cod_evento = %d AND lug.cod_grupo = %d
                LEFT JOIN tab_convidados tc ON su.cod_usuario = tc.cod_usuario
                WHERE su.status = 'ATIVADA'
                ORDER BY nome",
                intval($codEvento), intval($codEvento), $codGrupo);

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            $lista = array();
            if ($rs && !$rs->EOF) {
                foreach ($rs as $row) {
                    $lista[] = $row;
                }
            }
            return $lista;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return array();
        }
    }

}

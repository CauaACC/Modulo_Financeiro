<?php

class CursoItemDAO extends DAO {

    public function __construct() {
        parent::__construct();
    }

    public function getById(CursoItem $obj, $debug = false) {
        try {
            $sql = sprintf("SELECT * FROM acad_curso_item
                WHERE cod_evento=%d AND cod_curso_item=%d",
                (int) $obj->getCodEvento(), (int) $obj->getCodCursoItem());
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if ((!$rs) or ($rs->EOF)) {
                return false;
            }
            foreach ($rs as $rows) {
                $obj->setAll($rows);
            }
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function carregarLista(CursoItem $obj, $somenteAtivos = false, $debug = false) {
        try {
            $where = "cod_evento=" . (int) $obj->getCodEvento();
            if ($somenteAtivos) {
                $where .= " AND status='ATIVADA'";
            }
            $sql = "SELECT * FROM acad_curso_item WHERE $where ORDER BY ordem ASC, cod_curso_item ASC";
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $return = array();
            if ($rs && !$rs->EOF) {
                foreach ($rs as $rows) {
                    $novo = new CursoItem();
                    $novo->setAll($rows);
                    $return[] = $novo;
                }
            }
            $obj->setListaCursoItem($return);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function insert(CursoItem $obj, $debug = false) {
        try {
            $obj->setDatIncl(date('Y-m-d H:i:s'));
            $obj->setResIncl($_SESSION['_cod_usuario']);
            $obj->setIp($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgent($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao(1);
            if (!$obj->getStatus()) {
                $obj->setStatus('ATIVADA');
            }

            $sql = "SELECT * FROM acad_curso_item WHERE cod_evento=" . (int) $obj->getCodEvento() . " AND cod_curso_item=0";
            $rs = $this->conex->Execute($sql);
            $arrayColunas = $this->conex->MetaColumnNames("acad_curso_item");
            $fws_ = $obj->getAllForArray($arrayColunas, false);
            $insertSQL = $this->conex->GetInsertSQL($rs, $fws_, true, true);
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($insertSQL);
            $obj->setCodCursoItem($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function update(CursoItem $obj, $debug = false) {
        try {
            $obj->setDatAlter(date('Y-m-d H:i:s'));
            $obj->setResAlter($_SESSION['_cod_usuario']);
            $obj->setIp($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgent($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao((int) $obj->getVersao() + 1);
            if (!$obj->getStatus()) {
                $obj->setStatus('ATIVADA');
            }

            $sql = "SELECT * FROM acad_curso_item WHERE cod_evento=" . (int) $obj->getCodEvento() . " AND cod_curso_item=" . (int) $obj->getCodCursoItem();
            $rs = $this->conex->Execute($sql);
            $arrayColunas = $this->conex->MetaColumnNames("acad_curso_item");
            $fws_ = $obj->getAllForArray($arrayColunas, false);
            $updateSQL = $this->conex->GetUpdateSQL($rs, $fws_, true, false, true);
            if (!$updateSQL) {
                return true;
            }
            $this->conex->debug = $debug;
            return $this->conex->Execute($updateSQL);
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function delete(CursoItem $obj, $debug = false) {
        try {
            $sql = sprintf("DELETE FROM acad_curso_item WHERE cod_evento=%d AND cod_curso_item=%d",
                (int) $obj->getCodEvento(), (int) $obj->getCodCursoItem());
            $this->conex->debug = $debug;
            $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    /**
     * Exclusao com espelho de auditoria (acad_curso_item_excluidos).
     *
     * A copia e' INSERT ... SELECT *, entao o espelho precisa ter as mesmas
     * colunas da base -- ver sql/create_acad_curso_item_excluidos.sql. A autoria
     * da exclusao viaja dentro da propria linha: carimbamos dat_alter/res_alter/
     * ip/http_user_agent na base ANTES de copiar.
     *
     * So apaga depois de conferir que a copia chegou (mesmo cuidado de
     * view/admin/perfil-cadastro-excluir-grava.php). Se o espelho falhar, nada
     * e' apagado.
     */
    public function deleteComEspelho(CursoItem $obj, $debug = false) {
        try {
            $codEvento    = (int) $obj->getCodEvento();
            $codCursoItem = (int) $obj->getCodCursoItem();
            if (!$codEvento || !$codCursoItem) return false;

            $this->conex->debug = $debug;

            $sqlCarimbo = sprintf("UPDATE acad_curso_item
                    SET dat_alter=%s, res_alter=%d, ip=%s, http_user_agent=%s
                  WHERE cod_evento=%d AND cod_curso_item=%d",
                $this->conex->qstr(date('Y-m-d H:i:s')),
                (int) (isset($_SESSION['_cod_usuario']) ? $_SESSION['_cod_usuario'] : 0),
                $this->conex->qstr(isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : ''),
                $this->conex->qstr(isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 500) : ''),
                $codEvento, $codCursoItem);
            $this->conex->Execute($sqlCarimbo);

            $sqlEspelho = sprintf("INSERT INTO acad_curso_item_excluidos
                    SELECT * FROM acad_curso_item
                     WHERE cod_evento=%d AND cod_curso_item=%d", $codEvento, $codCursoItem);
            $this->conex->Execute($sqlEspelho);

            $sqlConfere = sprintf("SELECT cod_curso_item FROM acad_curso_item_excluidos
                     WHERE cod_evento=%d AND cod_curso_item=%d LIMIT 1", $codEvento, $codCursoItem);
            $rs = $this->conex->Execute($sqlConfere);
            if ((!$rs) or ($rs->EOF)) {
                // sem copia, sem exclusao
                return false;
            }

            $sql = sprintf("DELETE FROM acad_curso_item WHERE cod_evento=%d AND cod_curso_item=%d",
                $codEvento, $codCursoItem);
            $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    /**
     * Quantos dependentes cada item do evento tem, em UMA consulta.
     * Devolve mapa cod_curso_item => array(filhos, prereq, presenca, conclusao,
     * tarefa, comentario, mensagem). Usado pela grade admin para saber, antes do
     * clique, quem pode ser excluido.
     */
    public function contarDependentesPorItem($codEvento, $debug = false) {
        $mapa = array();
        try {
            $codEvento = (int) $codEvento;
            if (!$codEvento) return $mapa;
            $sql = "
                SELECT cod_pai AS cod_curso_item, 'filhos' AS origem, COUNT(*) AS qtd
                  FROM acad_curso_item
                 WHERE cod_evento = $codEvento AND cod_pai IS NOT NULL AND cod_pai > 0
                 GROUP BY cod_pai
                UNION ALL
                SELECT cod_pre_requisito, 'prereq', COUNT(*)
                  FROM acad_curso_item
                 WHERE cod_evento = $codEvento AND cod_pre_requisito IS NOT NULL AND cod_pre_requisito > 0
                 GROUP BY cod_pre_requisito
                UNION ALL
                SELECT d.cod_curso_item, 'presenca', COUNT(*)
                  FROM acad_curso_item_presenca d
                  JOIN acad_curso_item i ON i.cod_curso_item = d.cod_curso_item AND i.cod_evento = $codEvento
                 GROUP BY d.cod_curso_item
                UNION ALL
                SELECT d.cod_curso_item, 'conclusao', COUNT(*)
                  FROM acad_curso_item_conclusao d
                  JOIN acad_curso_item i ON i.cod_curso_item = d.cod_curso_item AND i.cod_evento = $codEvento
                 GROUP BY d.cod_curso_item
                UNION ALL
                SELECT d.cod_curso_item, 'tarefa', COUNT(*)
                  FROM acad_curso_item_tarefa d
                  JOIN acad_curso_item i ON i.cod_curso_item = d.cod_curso_item AND i.cod_evento = $codEvento
                 GROUP BY d.cod_curso_item
                UNION ALL
                SELECT d.cod_curso_item, 'comentario', COUNT(*)
                  FROM acad_curso_item_comentario d
                  JOIN acad_curso_item i ON i.cod_curso_item = d.cod_curso_item AND i.cod_evento = $codEvento
                 GROUP BY d.cod_curso_item
                UNION ALL
                SELECT d.cod_curso_item, 'mensagem', COUNT(*)
                  FROM acad_curso_tarefa_mensagem d
                  JOIN acad_curso_item i ON i.cod_curso_item = d.cod_curso_item AND i.cod_evento = $codEvento
                 GROUP BY d.cod_curso_item";
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if ($rs && !$rs->EOF) {
                foreach ($rs as $r) {
                    $cod = (int) $r['cod_curso_item'];
                    if (!$cod) continue;
                    if (!isset($mapa[$cod])) $mapa[$cod] = self::dependentesZerados();
                    $mapa[$cod][$r['origem']] = (int) $r['qtd'];
                }
            }
            return $mapa;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return $mapa;
        }
    }

    /**
     * Dependentes de UM item. Em caso de erro devolve array('erro'=>1): quem
     * chama trata como "nao da pra conferir, entao nao exclui" (fail-safe, igual
     * ao CursoDAO::possuiItens).
     */
    public function contarDependentesDoItem($codEvento, $codCursoItem, $debug = false) {
        try {
            $codEvento    = (int) $codEvento;
            $codCursoItem = (int) $codCursoItem;
            if (!$codEvento || !$codCursoItem) return array('erro' => 1);

            $dep = self::dependentesZerados();
            $sql = "
                SELECT 'filhos' AS origem, COUNT(*) AS qtd FROM acad_curso_item
                 WHERE cod_evento = $codEvento AND cod_pai = $codCursoItem
                UNION ALL
                SELECT 'prereq', COUNT(*) FROM acad_curso_item
                 WHERE cod_evento = $codEvento AND cod_pre_requisito = $codCursoItem
                UNION ALL
                SELECT 'presenca',   COUNT(*) FROM acad_curso_item_presenca   WHERE cod_curso_item = $codCursoItem
                UNION ALL
                SELECT 'conclusao',  COUNT(*) FROM acad_curso_item_conclusao  WHERE cod_curso_item = $codCursoItem
                UNION ALL
                SELECT 'tarefa',     COUNT(*) FROM acad_curso_item_tarefa     WHERE cod_curso_item = $codCursoItem
                UNION ALL
                SELECT 'comentario', COUNT(*) FROM acad_curso_item_comentario WHERE cod_curso_item = $codCursoItem
                UNION ALL
                SELECT 'mensagem',   COUNT(*) FROM acad_curso_tarefa_mensagem WHERE cod_curso_item = $codCursoItem";
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if (!$rs) return array('erro' => 1);
            foreach ($rs as $r) { $dep[$r['origem']] = (int) $r['qtd']; }
            return $dep;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return array('erro' => 1);
        }
    }

    public static function dependentesZerados() {
        return array('filhos' => 0, 'prereq' => 0, 'presenca' => 0, 'conclusao' => 0,
                     'tarefa' => 0, 'comentario' => 0, 'mensagem' => 0);
    }

    /**
     * Solta os itens que apontavam para este como pre-requisito. Sem isto, um
     * item excluido (ou inativado) deixa os dependentes travados para sempre:
     * a conclusao do pre-requisito nunca vai existir.
     * Devolve quantos itens foram liberados.
     */
    public function limparPreRequisitoApontandoPara($codEvento, $codCursoItem, $debug = false) {
        try {
            $codEvento    = (int) $codEvento;
            $codCursoItem = (int) $codCursoItem;
            if (!$codEvento || !$codCursoItem) return 0;
            $sql = sprintf("UPDATE acad_curso_item
                    SET cod_pre_requisito=NULL, dat_alter=%s, res_alter=%d
                  WHERE cod_evento=%d AND cod_pre_requisito=%d",
                $this->conex->qstr(date('Y-m-d H:i:s')),
                (int) (isset($_SESSION['_cod_usuario']) ? $_SESSION['_cod_usuario'] : 0),
                $codEvento, $codCursoItem);
            $this->conex->debug = $debug;
            $this->conex->Execute($sql);
            return (int) $this->conex->Affected_Rows();
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return 0;
        }
    }

    /**
     * Troca so o status do item (ATIVADA / DESATIVADA). Direto, sem
     * GetUpdateSQL: o update generico monta o SQL a partir do objeto inteiro e
     * apagaria titulo/url/janela se o objeto vier de um formulario parcial.
     */
    public function alterarStatus(CursoItem $obj, $status, $debug = false) {
        try {
            $codEvento    = (int) $obj->getCodEvento();
            $codCursoItem = (int) $obj->getCodCursoItem();
            if (!$codEvento || !$codCursoItem) return false;
            $sql = sprintf("UPDATE acad_curso_item
                    SET status=%s, dat_alter=%s, res_alter=%d, versao=versao+1
                  WHERE cod_evento=%d AND cod_curso_item=%d",
                $this->conex->qstr($status),
                $this->conex->qstr(date('Y-m-d H:i:s')),
                (int) (isset($_SESSION['_cod_usuario']) ? $_SESSION['_cod_usuario'] : 0),
                $codEvento, $codCursoItem);
            $this->conex->debug = $debug;
            $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    /**
     * Itens ATIVADA dos cursos informados, dentro do evento. Base do mapa de
     * escopo do aluno (FWA_itensCursoPermitidos): item que nao esta aqui nao
     * pode receber presenca, conclusao, tarefa nem comentario daquele inscrito.
     */
    public function itensPermitidosParaInscrito($codEvento, array $codCursos, $debug = false) {
        $mapa = array();
        try {
            $codEvento = (int) $codEvento;
            if (!$codEvento || empty($codCursos)) return $mapa;
            $ids = array();
            foreach ($codCursos as $c) { $c = (int) $c; if ($c) $ids[$c] = $c; }
            if (empty($ids)) return $mapa;
            $sql = "SELECT cod_curso_item FROM acad_curso_item
                     WHERE cod_evento = $codEvento AND status = 'ATIVADA'
                       AND cod_curso IN (" . implode(',', $ids) . ")";
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if ($rs && !$rs->EOF) {
                foreach ($rs as $r) { $mapa[(int) $r['cod_curso_item']] = true; }
            }
            return $mapa;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return $mapa;
        }
    }

    public function carregarArvore($codEvento, $codCurso = null, $debug = false) {
        try {
            $where = "i.cod_evento=" . (int) $codEvento;
            if ($codCurso) $where .= " AND i.cod_curso=" . (int) $codCurso;
            $sql = "SELECT i.*, c.titulo AS curso_titulo
                FROM acad_curso_item i
                LEFT JOIN acad_curso c ON c.cod_curso = i.cod_curso
                WHERE $where
                ORDER BY i.cod_curso ASC, IFNULL(i.cod_pai, 0) ASC, i.ordem ASC, i.cod_curso_item ASC";
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $linhas = array();
            if ($rs && !$rs->EOF) foreach ($rs as $r) $linhas[] = $r;
            return $linhas;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return array();
        }
    }

    public function carregarPossiveisPais($codEvento, $codCurso, $excluirCodItem = null, $debug = false) {
        try {
            $where = "cod_evento=" . (int) $codEvento . " AND tipo='CAPITULO'";
            if ($codCurso) $where .= " AND cod_curso=" . (int) $codCurso;
            if ($excluirCodItem) $where .= " AND cod_curso_item != " . (int) $excluirCodItem;
            $sql = "SELECT cod_curso_item, cod_pai, titulo FROM acad_curso_item
                WHERE $where ORDER BY ordem, cod_curso_item";
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $linhas = array();
            if ($rs && !$rs->EOF) foreach ($rs as $r) $linhas[] = $r;
            return $linhas;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return array();
        }
    }

    public function carregarPossiveisPreRequisitos($codEvento, $codCurso, $excluirCodItem = null, $debug = false) {
        try {
            $where = "cod_evento=" . (int) $codEvento . " AND tipo != 'CAPITULO'";
            if ($codCurso) $where .= " AND cod_curso=" . (int) $codCurso;
            if ($excluirCodItem) $where .= " AND cod_curso_item != " . (int) $excluirCodItem;
            $sql = "SELECT cod_curso_item, titulo, tipo FROM acad_curso_item
                WHERE $where ORDER BY ordem, cod_curso_item";
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $linhas = array();
            if ($rs && !$rs->EOF) foreach ($rs as $r) $linhas[] = $r;
            return $linhas;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return array();
        }
    }
}

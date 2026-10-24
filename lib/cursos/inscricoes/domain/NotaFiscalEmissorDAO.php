<?php

class NotaFiscalEmissorDAO extends DAO {

    public function __construct() {
        $this->tableName = "fin_nota_fiscal_emissor";
        parent::__construct();
    }

    public function getById(NotaFiscalEmissor $obj, $debug = false) {
        try {
            $sql = sprintf("SELECT * FROM " . $this->tableName . " WHERE cod_emissor=%d",
                (int) $obj->getCodEmissor());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if ((!$rs) or ($rs->EOF)) {
                return false;
            }
            foreach ($rs as $rows) {}
            $obj->setAll($rows);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    /**
     * Emissor ativo do gestor. Havendo mais de um, retorna o mais antigo
     * (o cadastro principal) — a escolha explicita fica por conta de quem emite.
     */
    public function getAtivoByGestor(NotaFiscalEmissor $obj, $debug = false) {
        try {
            $sql = sprintf("SELECT * FROM " . $this->tableName . "
                WHERE cod_gestor=%d AND status='ATIVADA'
                ORDER BY cod_emissor ASC LIMIT 1",
                (int) $obj->getCodGestor());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if ((!$rs) or ($rs->EOF)) {
                return false;
            }
            foreach ($rs as $rows) {}
            $obj->setAll($rows);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    /**
     * Resolve o emissor a partir do evento. O vinculo evento->gestor e por NOME,
     * mesmo criterio ja usado na geracao de lancamentos (GerenciarMotorFinanceiroAC).
     */
    public function getAtivoByEvento(NotaFiscalEmissor $obj, $codEvento, $debug = false) {
        try {
            $sql = sprintf("SELECT em.* FROM " . $this->tableName . " em
                INNER JOIN sis_gestor g ON g.cod_gestor = em.cod_gestor
                INNER JOIN tab_eventos e ON UPPER(e.gestor) = UPPER(g.nome_gestor)
                WHERE e.cod_evento=%d AND em.status='ATIVADA'
                ORDER BY em.cod_emissor ASC LIMIT 1",
                (int) $codEvento);

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if ((!$rs) or ($rs->EOF)) {
                return false;
            }
            foreach ($rs as $rows) {}
            $obj->setAll($rows);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    /**
     * Ja existe outro emissor ATIVO com este CNPJ no mesmo gestor?
     * Sem esta trava dava para cadastrar o mesmo CNPJ varias vezes e a emissao
     * escolhia um deles em silencio — com dados possivelmente desatualizados.
     */
    public function existeDuplicado(NotaFiscalEmissor $obj, $debug = false) {
        try {
            $sql = sprintf("SELECT cod_emissor FROM " . $this->tableName . "
                WHERE cod_gestor=%d AND cnpj='%s' AND status='ATIVADA' AND cod_emissor <> %d
                LIMIT 1",
                (int) $obj->getCodGestor(),
                addslashes((string) $obj->getCnpj()),
                (int) $obj->getCodEmissor());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            return !((!$rs) or ($rs->EOF));
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    /**
     * @param array|null $codsGestor null = todos (so ADM_GERAL); array = so
     *        esses gestores. Dados fiscais de um gestor nao podem aparecer
     *        para o financeiro de outro.
     */
    public function listar($codsGestor = null, $debug = false) {
        try {
            $where = "";
            if (is_array($codsGestor)) {
                if (empty($codsGestor)) {
                    return array(); // sem gestor vinculado: nada a mostrar
                }
                $ids = array();
                foreach ($codsGestor as $g) {
                    $ids[] = (int) $g;
                }
                $where = " WHERE em.cod_gestor IN (" . implode(',', $ids) . ")";
            }

            $sql = "SELECT em.*, g.nome_gestor
                    FROM " . $this->tableName . " em
                    LEFT JOIN sis_gestor g ON g.cod_gestor = em.cod_gestor"
                 . $where .
                   " ORDER BY g.nome_gestor, em.razao_social";

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            $lista = array();
            if ($rs && !$rs->EOF) {
                foreach ($rs as $row) {
                    $item = new NotaFiscalEmissor();
                    $item->setAll($row);
                    $lista[] = $item;
                }
            }
            return $lista;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return array();
        }
    }

    /**
     * Gestores ativos, para o select do cadastro. Fica aqui e nao no dominio de
     * Gestor porque e uma lista de apoio desta tela, nao regra de gestor.
     */
    /**
     * @param string|null $codUsuario quando informado, so os gestores aos quais
     *        o usuario esta vinculado (sis_ligacao_usuarios_gestor).
     */
    public function listarGestoresAtivos($codUsuario = null, $debug = false) {
        try {
            if ($codUsuario !== null && $codUsuario !== '') {
                $sql = sprintf("SELECT g.cod_gestor, g.nome_gestor
                    FROM sis_gestor g
                    INNER JOIN sis_ligacao_usuarios_gestor lg ON lg.cod_gestor = g.cod_gestor
                    WHERE lg.cod_usuario='%s' AND g.status='ATIVADO'
                    ORDER BY g.nome_gestor", addslashes($codUsuario));
            } else {
                $sql = "SELECT cod_gestor, nome_gestor FROM sis_gestor
                        WHERE status='ATIVADO' ORDER BY nome_gestor";
            }

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            $lista = array();
            if ($rs && !$rs->EOF) {
                foreach ($rs as $row) {
                    $lista[] = array(
                        'cod_gestor'  => $row['cod_gestor'],
                        'nome_gestor' => $row['nome_gestor']
                    );
                }
            }
            return $lista;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return array();
        }
    }

    /**
     * Reserva o proximo numero de DPS (NFS-e) de forma ATOMICA.
     * @return int numero reservado, ou 0 se nao conseguiu
     */
    public function reservarNumeroDps(NotaFiscalEmissor $obj, $ambiente = 'producao', $debug = false) {
        return $this->reservarNumero($obj, 'proximo_numero_dps', $ambiente, $debug);
    }

    /**
     * Mesma reserva atomica, para a NF-e de mercadoria (modelo 55).
     *
     * O contador e do EMISSOR, nao do canal: a numeracao da NF-e pertence ao
     * emitente (CNPJ + serie). Canais diferentes do mesmo CNPJ dividem a
     * sequencia — um contador por canal daria numero repetido na SEFAZ.
     */
    public function reservarNumeroNfe(NotaFiscalEmissor $obj, $ambiente = 'producao', $debug = false) {
        return $this->reservarNumero($obj, 'proximo_numero_nfe', $ambiente, $debug);
    }

    /**
     * Reserva atomica do proximo numero de um contador do emissor.
     *
     * O truque do LAST_INSERT_ID(expr) faz o MySQL guardar o valor lido na
     * mesma instrucao que o incrementa, por conexao — duas emissoes ao mesmo
     * tempo nunca recebem o mesmo numero. Um SELECT seguido de UPDATE daria
     * numero repetido e a prefeitura/SEFAZ rejeitaria a segunda nota.
     *
     * O numero e consumido mesmo se a nota for rejeitada depois: isso deixa
     * buraco na sequencia, que e aceitavel — repetir numero nao e.
     *
     * @param string $colunaBase nome da coluna do contador de PRODUCAO; a de
     *        homologacao e a mesma com o sufixo _homologacao.
     * @return int numero reservado, ou 0 se nao conseguiu
     */
    private function reservarNumero(NotaFiscalEmissor $obj, $colunaBase, $ambiente, $debug = false) {
        try {
            $this->conex->debug = $debug;

            // Contador por AMBIENTE. Na prefeitura/SEFAZ homologacao e producao
            // sao independentes; com um contador so, testar em homologacao
            // empurrava a numeracao de producao e a primeira nota real ja sairia
            // com buraco na sequencia.
            $coluna = ($ambiente === 'producao') ? $colunaBase : $colunaBase . '_homologacao';

            $sql = sprintf("UPDATE " . $this->tableName . "
                SET `%s` = LAST_INSERT_ID(IFNULL(`%s`,1)) + 1
                WHERE cod_emissor = %d",
                $coluna, $coluna, (int) $obj->getCodEmissor());
            $this->conex->Execute($sql);

            $rs = $this->conex->Execute("SELECT LAST_INSERT_ID() AS numero");
            if ((!$rs) or ($rs->EOF)) {
                return 0;
            }
            foreach ($rs as $row) {
                return (int) $row['numero'];
            }
            return 0;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return 0;
        }
    }

    public function insert(NotaFiscalEmissor $obj, $debug = false) {
        try {
            $obj->setDataInclusao(date('Y-m-d H:i:s'));
            $obj->setIdInclusao(isset($_SESSION['_cod_usuario']) ? $_SESSION['_cod_usuario'] : '');
            $obj->setSessionIdInclusao(isset($_SESSION['_cod_inscrito']) ? $_SESSION['_cod_inscrito'] : '');
            $obj->setIpInclusao(isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '');
            $obj->setHttpUserAgentInclusao(isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '');
            $obj->setVersao(1);
            if ($obj->getStatus() == '') {
                $obj->setStatus('ATIVADA');
            }

            $sql = "SELECT * FROM " . $this->tableName . " WHERE cod_emissor=0";
            $rs = $this->conex->Execute($sql);

            $arrayColunas = $this->conex->MetaColumnNames($this->tableName);
            $fws_ = $obj->getAllForArray($arrayColunas, false);

            $insertSQL = $this->conex->GetInsertSQL($rs, $fws_, true, true);

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($insertSQL);
            $obj->setCodEmissor($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function update(NotaFiscalEmissor $obj, $debug = false) {
        try {
            $obj->setDataAlteracao(date('Y-m-d H:i:s'));
            $obj->setIdAlteracao(isset($_SESSION['_cod_usuario']) ? $_SESSION['_cod_usuario'] : '');
            $obj->setSessionIdAlteracao(isset($_SESSION['_cod_inscrito']) ? $_SESSION['_cod_inscrito'] : '');
            $obj->setIpAlteracao(isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '');
            $obj->setHttpUserAgentAlteracao(isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '');

            $sql = sprintf("SELECT * FROM " . $this->tableName . " WHERE cod_emissor=%d",
                (int) $obj->getCodEmissor());
            $rs = $this->conex->Execute($sql);

            $arrayColunas = $this->conex->MetaColumnNames($this->tableName);
            $fws_ = $obj->getAllForArray($arrayColunas, false);

            $updateSQL = $this->conex->GetUpdateSQL($rs, $fws_, true, true);

            $this->conex->debug = $debug;
            if ($updateSQL) {
                $this->conex->Execute($updateSQL);
            }
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

}

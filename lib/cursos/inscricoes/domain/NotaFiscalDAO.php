<?php

class NotaFiscalDAO extends DAO {

    public function __construct() {
        $this->tableName = "fin_nota_fiscal";
        parent::__construct();
    }

    public function getById(NotaFiscal $obj, $debug = false) {
        try {
            $sql = sprintf("SELECT * FROM " . $this->tableName . " WHERE cod_nota_fiscal=%d",
                (int) $obj->getCodNotaFiscal());

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

    public function getByReferencia(NotaFiscal $obj, $debug = false) {
        try {
            $sql = sprintf("SELECT * FROM " . $this->tableName . " WHERE referencia='%s'",
                addslashes($obj->getReferencia()));

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
     * Nota vigente da origem: qualquer uma que nao esteja cancelada nem em erro.
     * E o que impede emitir duas vezes o mesmo pedido.
     */
    public function getVigenteByOrigem(NotaFiscal $obj, $debug = false) {
        try {
            $sql = sprintf("SELECT * FROM " . $this->tableName . "
                WHERE origem='%s' AND origem_id='%s'
                  AND status IN ('PROCESSANDO','AUTORIZADA')
                ORDER BY cod_nota_fiscal DESC LIMIT 1",
                addslashes($obj->getOrigem()),
                addslashes($obj->getOrigemId()));

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

    public function listarByOrigem(NotaFiscal $obj, $debug = false) {
        try {
            $sql = sprintf("SELECT * FROM " . $this->tableName . "
                WHERE origem='%s' AND origem_id='%s'
                ORDER BY cod_nota_fiscal DESC",
                addslashes($obj->getOrigem()),
                addslashes($obj->getOrigemId()));

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            $lista = array();
            if ($rs && !$rs->EOF) {
                foreach ($rs as $row) {
                    $item = new NotaFiscal();
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
     * Notas de um evento. $filtroStatus vazio traz todas.
     */
    public function listarByEvento(NotaFiscal $obj, $filtroStatus = '', $debug = false) {
        try {
            $where = sprintf(" WHERE cod_evento=%d", (int) $obj->getCodEvento());
            if ($filtroStatus != '') {
                $where .= sprintf(" AND status='%s'", addslashes($filtroStatus));
            }
            $sql = "SELECT * FROM " . $this->tableName . $where . " ORDER BY cod_nota_fiscal DESC";

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            $lista = array();
            if ($rs && !$rs->EOF) {
                foreach ($rs as $row) {
                    $item = new NotaFiscal();
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

    // ==================================================================
    // Consulta fiscal (tela Financeiro > Notas Fiscais)
    //
    // NFS-e e NF-e moram na mesma tabela mas guardam numero e valor em
    // colunas diferentes. O COALESCE unifica isso aqui, no SQL, para a tela
    // nao precisar saber o tipo de cada linha.
    // ==================================================================

    /**
     * Monta o WHERE compartilhado pela listagem e pelo resumo, para os dois
     * nunca divergirem (cards batendo com a lista).
     *
     * @param array $f codEvento, gestores (array|null), status, tipo, ambiente,
     *                 dataIni, dataFim, numero, busca, semEnvio
     */
    private function montarFiltrosConsulta(array $f) {
        $where = array("1=1");

        if (!empty($f['codEvento'])) {
            $where[] = sprintf("n.cod_evento = %d", (int) $f['codEvento']);
        }
        // null = ve todos (ADM_GERAL); array = so os gestores desses emissores
        if (isset($f['gestores']) && is_array($f['gestores'])) {
            $ids = array_map('intval', $f['gestores']);
            $where[] = "e.cod_gestor IN (" . (empty($ids) ? "0" : implode(',', $ids)) . ")";
        }
        if (!empty($f['status'])) {
            $where[] = sprintf("n.status = '%s'", addslashes($f['status']));
        }
        if (!empty($f['tipo'])) {
            $where[] = sprintf("n.tipo = '%s'", addslashes($f['tipo']));
        }
        if (!empty($f['ambiente'])) {
            $where[] = sprintf("n.ambiente = '%s'", addslashes($f['ambiente']));
        }
        // Nota sem data_emissao (erro/processando) ainda precisa aparecer no
        // periodo: cai para a data em que foi registrada.
        if (!empty($f['dataIni'])) {
            $where[] = sprintf("DATE(COALESCE(n.data_emissao, n.data_inclusao)) >= '%s'",
                addslashes($f['dataIni']));
        }
        if (!empty($f['dataFim'])) {
            $where[] = sprintf("DATE(COALESCE(n.data_emissao, n.data_inclusao)) <= '%s'",
                addslashes($f['dataFim']));
        }
        if (!empty($f['numero'])) {
            $num = preg_replace('/[^0-9]/', '', (string) $f['numero']);
            if ($num !== '') {
                $where[] = sprintf("(n.numero = '%s' OR n.numero_nfe = %d OR n.numero_dps = %d)",
                    addslashes($num), (int) $num, (int) $num);
            }
        }
        if (!empty($f['busca'])) {
            $b = addslashes(trim((string) $f['busca']));
            $so = preg_replace('/[^0-9]/', '', $b);
            $partes = array(
                sprintf("n.tomador_nome LIKE '%%%s%%'", $b),
                sprintf("n.referencia LIKE '%%%s%%'", $b),
                sprintf("n.origem_id LIKE '%%%s%%'", $b),
                sprintf("n.chave_nfe LIKE '%%%s%%'", $b),
            );
            if ($so !== '') {
                $partes[] = sprintf("REPLACE(REPLACE(REPLACE(n.tomador_cpf_cnpj,'.',''),'-',''),'/','') LIKE '%%%s%%'", addslashes($so));
            }
            $where[] = "(" . implode(" OR ", $partes) . ")";
        }
        // Autorizada que ainda nao foi mandada para o tomador
        if (!empty($f['semEnvio'])) {
            $where[] = "n.status = 'AUTORIZADA' AND n.data_envio_email IS NULL";
        }

        return implode(" AND ", $where);
    }

    public function listarComFiltros(array $f, $debug = false) {
        try {
            $sql = "SELECT n.*,
                           COALESCE(n.numero, CAST(n.numero_nfe AS CHAR)) AS numero_exibicao,
                           COALESCE(n.valor_total_nfe, n.valor_servico)   AS valor_exibicao,
                           COALESCE(n.data_emissao, n.data_inclusao)      AS data_referencia,
                           e.razao_social AS emissor_razao_social,
                           e.cnpj         AS emissor_cnpj,
                           e.cod_gestor   AS emissor_cod_gestor
                      FROM " . $this->tableName . " n
                      LEFT JOIN fin_nota_fiscal_emissor e ON e.cod_emissor = n.cod_emissor
                     WHERE " . $this->montarFiltrosConsulta($f) . "
                     ORDER BY COALESCE(n.data_emissao, n.data_inclusao) DESC, n.cod_nota_fiscal DESC";

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

    /**
     * Quantidade e valor por status, no MESMO recorte da listagem.
     */
    public function resumoPorStatus(array $f, $debug = false) {
        try {
            $sql = "SELECT n.status,
                           COUNT(*) AS qtd,
                           COALESCE(SUM(COALESCE(n.valor_total_nfe, n.valor_servico)), 0) AS valor
                      FROM " . $this->tableName . " n
                      LEFT JOIN fin_nota_fiscal_emissor e ON e.cod_emissor = n.cod_emissor
                     WHERE " . $this->montarFiltrosConsulta($f) . "
                     GROUP BY n.status";

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            $resumo = array();
            if ($rs && !$rs->EOF) {
                foreach ($rs as $row) {
                    $resumo[$row['status']] = array(
                        'qtd'   => (int) $row['qtd'],
                        'valor' => (float) $row['valor']
                    );
                }
            }
            return $resumo;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return array();
        }
    }

    /**
     * Completa o cadastro do inscrito com os dados do tomador informados na
     * emissao — SOMENTE nos campos que estao vazios.
     *
     * O CASE WHEN garante isso no proprio SQL: campo com conteudo fica como
     * esta. Campo vazio nao tem o que perder; campo preenchido pode ser
     * proposital (o tomador nem sempre e o inscrito), e sobrescrever sujaria
     * o cadastro de uma pessoa com dados de outra entidade.
     *
     * @param array $dados chave => valor, com nomes de coluna de tab_inscritos
     * @return array nomes das colunas que foram efetivamente preenchidas
     */
    public function completarCadastroInscrito($codEvento, $codInscrito, array $dados, $debug = false) {
        try {
            if (!$codEvento || !$codInscrito || empty($dados)) {
                return array();
            }

            $permitidas = array('cep', 'endereco', 'numero', 'complemento', 'bairro',
                                'cidade', 'uf', 'estado', 'nome_recibo', 'e_mail');

            // Descobre o que esta vazio hoje: so isso sera tocado, e so isso e
            // reportado de volta para a tela.
            $sql = sprintf("SELECT * FROM tab_inscritos WHERE cod_evento='%s' AND cod_inscrito='%s'",
                addslashes($codEvento), addslashes($codInscrito));
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if ((!$rs) or ($rs->EOF)) {
                return array();
            }
            $atual = array();
            foreach ($rs as $row) { $atual = $row; break; }

            $sets = array();
            $preenchidas = array();
            foreach ($dados as $coluna => $valor) {
                if (!in_array($coluna, $permitidas, true)) {
                    continue;
                }
                $valor = trim((string) $valor);
                if ($valor === '') {
                    continue;
                }
                $valorAtual = isset($atual[$coluna]) ? trim((string) $atual[$coluna]) : '';
                if ($valorAtual !== '') {
                    continue; // ja tem conteudo: nao mexe
                }
                $sets[] = sprintf("`%s` = CASE WHEN (`%s` IS NULL OR `%s`='') THEN '%s' ELSE `%s` END",
                    $coluna, $coluna, $coluna, addslashes($valor), $coluna);
                $preenchidas[] = $coluna;
            }

            if (empty($sets)) {
                return array();
            }

            $sqlUp = sprintf("UPDATE tab_inscritos SET %s WHERE cod_evento='%s' AND cod_inscrito='%s'",
                implode(', ', $sets), addslashes($codEvento), addslashes($codInscrito));

            $this->conex->debug = $debug;
            $this->conex->Execute($sqlUp);

            return $preenchidas;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return array();
        }
    }

    public function insert(NotaFiscal $obj, $debug = false) {
        try {
            $obj->setDataInclusao(date('Y-m-d H:i:s'));
            $obj->setIdInclusao(isset($_SESSION['_cod_usuario']) ? $_SESSION['_cod_usuario'] : '');
            $obj->setSessionIdInclusao(isset($_SESSION['_cod_inscrito']) ? $_SESSION['_cod_inscrito'] : '');
            $obj->setIpInclusao(isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '');
            $obj->setHttpUserAgentInclusao(isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '');
            $obj->setVersao(1);
            if ($obj->getStatus() == '') {
                $obj->setStatus('PROCESSANDO');
            }

            $sql = "SELECT * FROM " . $this->tableName . " WHERE cod_nota_fiscal=0";
            $rs = $this->conex->Execute($sql);

            $arrayColunas = $this->conex->MetaColumnNames($this->tableName);
            $fws_ = $obj->getAllForArray($arrayColunas, false);

            $insertSQL = $this->conex->GetInsertSQL($rs, $fws_, true, true);

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($insertSQL);
            $obj->setCodNotaFiscal($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    /**
     * Grava o que voltou da Focus. Campos de retorno so — os dados que foram
     * enviados sao snapshot e nao mudam depois da emissao.
     */
    /**
     * Ultima copia de envio usada para este inscrito. Serve para o campo
     * "enviar copia para" ja vir preenchido nas proximas notas dele.
     *
     * Ignora nota com copia vazia: se o gestor emitiu uma sem copia, isso nao
     * apaga o que ele configurou antes.
     */
    public function getUltimaCopiaDoInscrito($codEvento, $codInscrito, $debug = false) {
        try {
            $sql = sprintf("SELECT email_copia FROM " . $this->tableName . "
                WHERE cod_evento=%d AND cod_inscrito=%d
                  AND email_copia IS NOT NULL AND email_copia <> ''
                ORDER BY cod_nota_fiscal DESC LIMIT 1",
                (int) $codEvento, (int) $codInscrito);

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if ((!$rs) or ($rs->EOF)) {
                return '';
            }
            return (string) $rs->fields[0];
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return '';
        }
    }

    public function atualizarRetorno(NotaFiscal $obj, $debug = false) {
        try {
            $codUsuario = isset($_SESSION['_cod_usuario']) ? $_SESSION['_cod_usuario'] : '';
            $sql = sprintf("UPDATE " . $this->tableName . "
                SET status='%s',
                    numero='%s',
                    codigo_verificacao='%s',
                    url_pdf='%s',
                    url_xml='%s',
                    url_consulta_publica='%s',
                    chave_nfe=%s,
                    protocolo_autorizacao=%s,
                    data_emissao=%s,
                    mensagem_erro=%s,
                    response_json='%s',
                    data_alteracao='%s',
                    id_alteracao='%s',
                    ip_alteracao='%s',
                    http_user_agent_alteracao='%s',
                    versao=versao+1
                WHERE cod_nota_fiscal=%d",
                addslashes($obj->getStatus()),
                addslashes((string) $obj->getNumero()),
                addslashes((string) $obj->getCodigoVerificacao()),
                addslashes((string) $obj->getUrlPdf()),
                addslashes((string) $obj->getUrlXml()),
                addslashes((string) $obj->getUrlConsultaPublica()),
                // Campos so da NF-e: NULL quando vazios, para nao sujar de string
                // vazia as notas de servico, que nunca os preenchem.
                trim((string) $obj->getChaveNfe()) !== ''
                    ? "'" . addslashes($obj->getChaveNfe()) . "'" : "NULL",
                trim((string) $obj->getProtocoloAutorizacao()) !== ''
                    ? "'" . addslashes($obj->getProtocoloAutorizacao()) . "'" : "NULL",
                $obj->getDataEmissao() ? "'" . addslashes($obj->getDataEmissao()) . "'" : "NULL",
                $obj->getMensagemErro() ? "'" . addslashes($obj->getMensagemErro()) . "'" : "NULL",
                addslashes((string) $obj->getResponseJson()),
                date('Y-m-d H:i:s'),
                $codUsuario,
                isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '',
                addslashes(isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : ''),
                (int) $obj->getCodNotaFiscal());

            $this->conex->debug = $debug;
            $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function atualizarArquivosLocais(NotaFiscal $obj, $debug = false) {
        try {
            $sql = sprintf("UPDATE " . $this->tableName . "
                SET arquivo_pdf=%s, arquivo_xml=%s, versao=versao+1
                WHERE cod_nota_fiscal=%d",
                $obj->getArquivoPdf() ? "'" . addslashes($obj->getArquivoPdf()) . "'" : "NULL",
                $obj->getArquivoXml() ? "'" . addslashes($obj->getArquivoXml()) . "'" : "NULL",
                (int) $obj->getCodNotaFiscal());

            $this->conex->debug = $debug;
            $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    /**
     * Marca a nota como enviada por e-mail. Guarda o destinatario e o MessageID
     * do provedor: a tela bloqueia o botao depois disso e, se o inscrito disser
     * que nao recebeu, da para provar quando e para onde foi.
     */
    public function registrarEnvioEmail(NotaFiscal $obj, $debug = false) {
        try {
            $codUsuario = isset($_SESSION['_cod_usuario']) ? $_SESSION['_cod_usuario'] : '';
            $sql = sprintf("UPDATE " . $this->tableName . "
                SET data_envio_email='%s',
                    email_envio='%s',
                    email_copia=%s,
                    id_envio_email='%s',
                    data_alteracao='%s',
                    id_alteracao='%s',
                    ip_alteracao='%s',
                    http_user_agent_alteracao='%s',
                    versao=versao+1
                WHERE cod_nota_fiscal=%d",
                date('Y-m-d H:i:s'),
                addslashes((string) $obj->getEmailEnvio()),
                // Grava a copia usada: e ela que volta como padrao na proxima
                // nota deste inscrito. NULL quando vazia, para nao poluir com
                // string vazia quem nunca usou copia.
                trim((string) $obj->getEmailCopia()) !== ''
                    ? "'" . addslashes($obj->getEmailCopia()) . "'" : "NULL",
                addslashes(substr((string) $obj->getIdEnvioEmail(), 0, 20)),
                date('Y-m-d H:i:s'),
                $codUsuario,
                isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '',
                addslashes(isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : ''),
                (int) $obj->getCodNotaFiscal());

            $this->conex->debug = $debug;
            $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function registrarCancelamento(NotaFiscal $obj, $debug = false) {
        try {
            $codUsuario = isset($_SESSION['_cod_usuario']) ? $_SESSION['_cod_usuario'] : '';
            $sql = sprintf("UPDATE " . $this->tableName . "
                SET status='CANCELADA',
                    motivo_cancelamento='%s',
                    data_cancelamento='%s',
                    response_json='%s',
                    data_alteracao='%s',
                    id_alteracao='%s',
                    ip_alteracao='%s',
                    http_user_agent_alteracao='%s',
                    versao=versao+1
                WHERE cod_nota_fiscal=%d",
                addslashes((string) $obj->getMotivoCancelamento()),
                date('Y-m-d H:i:s'),
                addslashes((string) $obj->getResponseJson()),
                date('Y-m-d H:i:s'),
                $codUsuario,
                isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '',
                addslashes(isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : ''),
                (int) $obj->getCodNotaFiscal());

            $this->conex->debug = $debug;
            $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

}

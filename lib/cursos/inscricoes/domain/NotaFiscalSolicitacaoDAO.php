<?php

class NotaFiscalSolicitacaoDAO extends DAO {

    public function __construct() {
        parent::__construct();
    }

    public function getById(NotaFiscalSolicitacao $obj, $debug = false) {
        try {
            $sql = sprintf("SELECT * FROM fin_nota_fiscal_solicitacao
                WHERE cod_evento='%s' AND cod_solicitacao='%s'",
                $obj->getCodEvento(), $obj->getCodSolicitacao());

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

    public function getByIdExiste(NotaFiscalSolicitacao $obj, $debug = false) {
        try {
            $sql = sprintf("SELECT cod_solicitacao FROM fin_nota_fiscal_solicitacao
                WHERE cod_evento='%s' AND cod_solicitacao='%s'",
                $obj->getCodEvento(), $obj->getCodSolicitacao());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            return !((!$rs) or ($rs->EOF));
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function getAtivaByPagamento(NotaFiscalSolicitacao $obj, $debug = false) {
        try {
            $sql = sprintf("SELECT * FROM fin_nota_fiscal_solicitacao
                WHERE cod_evento='%s' AND cod_pagamento='%s' AND status='ATIVADA'
                ORDER BY cod_solicitacao DESC LIMIT 1",
                $obj->getCodEvento(), $obj->getCodPagamento());

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

    public function existeAtivaPorPagamento(NotaFiscalSolicitacao $obj, $debug = false) {
        try {
            $sql = sprintf("SELECT cod_solicitacao FROM fin_nota_fiscal_solicitacao
                WHERE cod_evento='%s' AND cod_pagamento='%s' AND status='ATIVADA' LIMIT 1",
                $obj->getCodEvento(), $obj->getCodPagamento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            return !((!$rs) or ($rs->EOF));
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function insert(NotaFiscalSolicitacao $obj, $debug = false) {
        try {
            $obj->setDataInclusao(date('Y-m-d H:i:s'));
            $obj->setIdInclusao(isset($_SESSION['_cod_usuario']) ? $_SESSION['_cod_usuario'] : '');
            $obj->setSessionIdInclusao(isset($_SESSION['_cod_inscrito']) ? $_SESSION['_cod_inscrito'] : '');
            $obj->setIpInclusao(isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '');
            $obj->setHttpUserAgentInclusao(isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '');
            $obj->setVersao(1);
            $obj->setStatus('ATIVADA');

            $sql = "SELECT * FROM fin_nota_fiscal_solicitacao WHERE cod_solicitacao=0";
            $rs = $this->conex->Execute($sql);

            $arrayColunas = $this->conex->MetaColumnNames("fin_nota_fiscal_solicitacao");
            $fws_ = $obj->getAllForArray($arrayColunas, false);

            $insertSQL = $this->conex->GetInsertSQL($rs, $fws_, true, true);
            $sql = sprintf($insertSQL);

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $obj->setCodSolicitacao($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function insertComArquivo(NotaFiscalSolicitacao $obj, $debug = false) {
        // Usado na substituicao: insere ja com arquivo anexado
        try {
            $obj->setDataInclusao(date('Y-m-d H:i:s'));
            $obj->setIdInclusao(isset($_SESSION['_cod_usuario']) ? $_SESSION['_cod_usuario'] : '');
            $obj->setSessionIdInclusao(isset($_SESSION['_cod_inscrito']) ? $_SESSION['_cod_inscrito'] : '');
            $obj->setIpInclusao(isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '');
            $obj->setHttpUserAgentInclusao(isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '');
            $obj->setVersao(1);
            $obj->setStatus('ATIVADA');
            $obj->setDataAnexo(date('Y-m-d H:i:s'));
            $obj->setIdAnexo(isset($_SESSION['_cod_usuario']) ? $_SESSION['_cod_usuario'] : '');

            $sql = "SELECT * FROM fin_nota_fiscal_solicitacao WHERE cod_solicitacao=0";
            $rs = $this->conex->Execute($sql);

            $arrayColunas = $this->conex->MetaColumnNames("fin_nota_fiscal_solicitacao");
            $fws_ = $obj->getAllForArray($arrayColunas, false);

            $insertSQL = $this->conex->GetInsertSQL($rs, $fws_, true, true);
            $sql = sprintf($insertSQL);

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $obj->setCodSolicitacao($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function anexarArquivo(NotaFiscalSolicitacao $obj, $debug = false) {
        try {
            $codUsuario = isset($_SESSION['_cod_usuario']) ? $_SESSION['_cod_usuario'] : '';
            $sql = sprintf("UPDATE fin_nota_fiscal_solicitacao
                SET arquivo_nf='%s',
                    data_anexo='%s',
                    id_anexo='%s',
                    data_alteracao='%s',
                    id_alteracao='%s',
                    ip_alteracao='%s',
                    http_user_agent_alteracao='%s',
                    versao=versao+1
                WHERE cod_evento='%s' AND cod_solicitacao='%s' AND status='ATIVADA'",
                addslashes($obj->getArquivoNf()),
                date('Y-m-d H:i:s'),
                $codUsuario,
                date('Y-m-d H:i:s'),
                $codUsuario,
                isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '',
                addslashes(isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : ''),
                $obj->getCodEvento(),
                $obj->getCodSolicitacao());

            $this->conex->debug = $debug;
            $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    /**
     * Liga a solicitacao a uma nota emitida pela Focus e a da por atendida.
     * O PDF da nota entra no MESMO campo do anexo manual (arquivo_nf), entao o
     * inscrito baixa pelo botao que ja existe — nenhuma tela dele muda.
     */
    public function vincularNotaFiscal(NotaFiscalSolicitacao $obj, $debug = false) {
        try {
            $codUsuario = isset($_SESSION['_cod_usuario']) ? $_SESSION['_cod_usuario'] : '';
            $sql = sprintf("UPDATE fin_nota_fiscal_solicitacao
                SET cod_nota_fiscal=%d,
                    arquivo_nf=%s,
                    data_anexo='%s',
                    id_anexo='%s',
                    data_alteracao='%s',
                    id_alteracao='%s',
                    ip_alteracao='%s',
                    http_user_agent_alteracao='%s',
                    versao=versao+1
                WHERE cod_evento='%s' AND cod_solicitacao='%s' AND status='ATIVADA'",
                (int) $obj->getCodNotaFiscal(),
                $obj->getArquivoNf() ? "'" . addslashes($obj->getArquivoNf()) . "'" : "arquivo_nf",
                date('Y-m-d H:i:s'),
                $codUsuario,
                date('Y-m-d H:i:s'),
                $codUsuario,
                isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '',
                addslashes(isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : ''),
                $obj->getCodEvento(),
                $obj->getCodSolicitacao());

            $this->conex->debug = $debug;
            $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    /**
     * Nota cancelada deixa de atender a solicitacao: ela volta a ficar pendente,
     * porque o inscrito continua querendo a nota. O arquivo so e limpo se for o
     * PDF daquela nota — anexo manual anterior nao se perde.
     */
    public function desvincularNotaFiscal(NotaFiscalSolicitacao $obj, $arquivoDaNota, $debug = false) {
        try {
            $limpaArquivo = ($arquivoDaNota != '')
                ? sprintf("arquivo_nf = CASE WHEN arquivo_nf='%s' THEN NULL ELSE arquivo_nf END,
                           data_anexo  = CASE WHEN arquivo_nf='%s' THEN NULL ELSE data_anexo END,",
                          addslashes($arquivoDaNota), addslashes($arquivoDaNota))
                : "";

            $sql = sprintf("UPDATE fin_nota_fiscal_solicitacao
                SET cod_nota_fiscal=NULL,
                    " . $limpaArquivo . "
                    data_alteracao='%s',
                    versao=versao+1
                WHERE cod_nota_fiscal=%d",
                date('Y-m-d H:i:s'),
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
     * Solicitacao ainda sem resposta: nem nota emitida, nem PDF anexado a mao.
     * Quem ja recebeu o anexo manual NAO conta como pendente — senao a emissao
     * passaria por cima de um arquivo que o gestor colocou de proposito.
     */
    public function getAtivaSemNotaByPagamento(NotaFiscalSolicitacao $obj, $debug = false) {
        try {
            $sql = sprintf("SELECT * FROM fin_nota_fiscal_solicitacao
                WHERE cod_evento='%s' AND cod_pagamento='%s' AND status='ATIVADA'
                  AND cod_nota_fiscal IS NULL
                  AND (arquivo_nf IS NULL OR arquivo_nf='')
                ORDER BY cod_solicitacao DESC LIMIT 1",
                $obj->getCodEvento(), $obj->getCodPagamento());

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

    public function desativar(NotaFiscalSolicitacao $obj, $debug = false) {
        try {
            $codUsuario = isset($_SESSION['_cod_usuario']) ? $_SESSION['_cod_usuario'] : '';
            $sql = sprintf("UPDATE fin_nota_fiscal_solicitacao
                SET status='DESATIVADA',
                    data_fim='%s',
                    motivo_desativacao='%s',
                    data_alteracao='%s',
                    id_alteracao='%s',
                    ip_alteracao='%s',
                    http_user_agent_alteracao='%s',
                    versao=versao+1
                WHERE cod_evento='%s' AND cod_solicitacao='%s'",
                date('Y-m-d H:i:s'),
                addslashes($obj->getMotivoDesativacao()),
                date('Y-m-d H:i:s'),
                $codUsuario,
                isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '',
                addslashes(isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : ''),
                $obj->getCodEvento(),
                $obj->getCodSolicitacao());

            $this->conex->debug = $debug;
            $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function listarHistoricoByPagamento(NotaFiscalSolicitacao $obj, $debug = false) {
        try {
            $sql = sprintf("SELECT * FROM fin_nota_fiscal_solicitacao
                WHERE cod_evento='%s' AND cod_pagamento='%s'
                ORDER BY cod_solicitacao DESC",
                $obj->getCodEvento(), $obj->getCodPagamento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            $lista = array();
            if ($rs && !$rs->EOF) {
                foreach ($rs as $row) {
                    $item = new NotaFiscalSolicitacao();
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

}

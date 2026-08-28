<?php

class RegistroAtividadeArquivoDAO extends DAO {

    public function __construct() {
        parent::__construct();
    }

    public function inserir($codRegAtividade, $codEvento, $arquivoNome, $arquivoPath) {
        try {
            $sql = sprintf(
                "INSERT INTO tab_registro_atividade_arquivo (cod_reg_atividade, cod_evento, arquivo_nome, arquivo_path, data_inclusao, id_inclusao, status)
                 VALUES ('%s', '%s', '%s', '%s', NOW(), '%s', 'ATIVADA')",
                addslashes($codRegAtividade),
                addslashes($codEvento),
                addslashes($arquivoNome),
                addslashes($arquivoPath),
                addslashes($_SESSION['_cod_usuario'])
            );
            $this->conex->Execute($sql);
            return $this->conex->Insert_ID();
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function listarPorRegistro($codRegAtividade, $codEvento) {
        try {
            $sql = sprintf(
                "SELECT * FROM tab_registro_atividade_arquivo WHERE cod_reg_atividade = '%s' AND cod_evento = '%s' AND status = 'ATIVADA' ORDER BY cod_arquivo",
                addslashes($codRegAtividade),
                addslashes($codEvento)
            );
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

    public function buscarPorCodigo($codArquivo, $codEvento) {
        try {
            $sql = sprintf(
                "SELECT * FROM tab_registro_atividade_arquivo WHERE cod_arquivo = '%s' AND cod_evento = '%s' AND status = 'ATIVADA'",
                addslashes($codArquivo),
                addslashes($codEvento)
            );
            $rs = $this->conex->Execute($sql);
            if ($rs && !$rs->EOF) {
                return $rs->fields;
            }
            return false;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function contarPorRegistro($codRegAtividade, $codEvento) {
        try {
            $sql = sprintf(
                "SELECT COUNT(*) as total FROM tab_registro_atividade_arquivo WHERE cod_reg_atividade = '%s' AND cod_evento = '%s' AND status = 'ATIVADA'",
                addslashes($codRegAtividade),
                addslashes($codEvento)
            );
            $rs = $this->conex->Execute($sql);
            return ($rs && !$rs->EOF) ? (int) $rs->fields['total'] : 0;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return 0;
        }
    }

    public function contarPorInscrito($codInscrito, $codEvento) {
        try {
            $sql = sprintf(
                "SELECT tra.cod_reg_atividade, COUNT(*) as total
                 FROM tab_registro_atividade_arquivo tra
                 INNER JOIN tab_registro_atividade tr ON tra.cod_reg_atividade = tr.cod_reg_atividade AND tra.cod_evento = tr.cod_evento
                 WHERE tr.cod_inscrito = '%s' AND tra.cod_evento = '%s' AND tra.status = 'ATIVADA'
                 GROUP BY tra.cod_reg_atividade",
                addslashes($codInscrito),
                addslashes($codEvento)
            );
            $rs = $this->conex->Execute($sql);
            $resultado = array();
            if ($rs && !$rs->EOF) {
                foreach ($rs as $row) {
                    $resultado[$row['cod_reg_atividade']] = (int) $row['total'];
                }
            }
            return $resultado;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return array();
        }
    }

    public function excluir($codArquivo, $codEvento) {
        try {
            $sql = sprintf(
                "DELETE FROM tab_registro_atividade_arquivo WHERE cod_arquivo = '%s' AND cod_evento = '%s'",
                addslashes($codArquivo),
                addslashes($codEvento)
            );
            $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function excluirPorRegistro($codRegAtividade, $codEvento) {
        try {
            $arquivos = $this->listarPorRegistro($codRegAtividade, $codEvento);
            $sql = sprintf(
                "DELETE FROM tab_registro_atividade_arquivo WHERE cod_reg_atividade = '%s' AND cod_evento = '%s'",
                addslashes($codRegAtividade),
                addslashes($codEvento)
            );
            $this->conex->Execute($sql);
            return $arquivos;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return array();
        }
    }
}

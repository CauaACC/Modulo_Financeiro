<?php
class FiltroSalvoDAO extends DAO {

    public function __construct() {
        parent::__construct();
    }

    public function insert(FiltroSalvo $obj, $debug = false) {
        try {
            $sql = "INSERT INTO tab_filtros_salvos (cod_usuario, cod_evento, nom_filtro, dat_filtro, dat_incl, res_incl, ip, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $params = [
                $obj->getCodUsuario(),
                $obj->getCodEvento(),
                $obj->getNomFiltro(),
                $obj->getDatFiltro(),
                date('Y-m-d H:i:s'),
                $_SESSION['_cod_usuario'],
                $_SERVER['REMOTE_ADDR'],
                $obj->getStatus() ? $obj->getStatus() : 'ATIVADA'
            ];
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql, $params);
            $obj->setCodFiltroSalvo($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function getListByUsuarioEvento(FiltroSalvo $obj, $debug = false) {
        try {
            $sql = sprintf(
                "SELECT * FROM tab_filtros_salvos WHERE cod_usuario='%s' AND cod_evento='%s' AND status='ATIVADA' ORDER BY nom_filtro",
                addslashesopctec($obj->getCodUsuario()),
                addslashesopctec($obj->getCodEvento())
            );
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if ((!$rs) or ($rs->EOF)) {
                return false;
            } else {
                $return = [];
                foreach ($rs as $rows) {
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaFiltrosSalvos($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function getById(FiltroSalvo $obj, $debug = false) {
        try {
            $sql = sprintf(
                "SELECT * FROM tab_filtros_salvos WHERE cod_filtro_salvo='%s'",
                addslashesopctec($obj->getCodFiltroSalvo())
            );
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if ((!$rs) or ($rs->EOF)) {
                return false;
            } else {
                foreach ($rs as $rows) {
                }
                $obj->setAll($rows);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function delete(FiltroSalvo $obj, $debug = false) {
        try {
            $sql = sprintf(
                "DELETE FROM tab_filtros_salvos WHERE cod_filtro_salvo='%s'",
                addslashesopctec($obj->getCodFiltroSalvo())
            );
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
}

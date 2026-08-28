<?php
class GrupoSistemaDAO extends DAO{

    public function __construct() {
        parent::__construct();
    }
    public function getById(GrupoSistema $obj, $debug=false) {
        try{
            $sql = sprintf('SELECT * FROM sis_direitos_grupos_sistemas WHERE cod_evento=\'%s\' '
            . ' AND cod_grupo=\'%s\' AND cod_sistema=\'%s\' AND cod_direito=\'%s\'',
            $obj->getCodEvento(), $obj->getCodGrupo(), $obj->getCodSistema(), $obj->getCodDireito());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                }
                $obj->setAll($rows);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function getByIdExiste(GrupoSistema $obj, $debug=false) {
        try {
            $sql = sprintf('SELECT 1 FROM sis_direitos_grupos_sistemas WHERE cod_evento=\'%s\'
            AND cod_grupo=\'%s\' AND cod_sistema=\'%s\' AND cod_direito=\'%s\'',
            $obj->getCodEvento(), $obj->getCodGrupo(), $obj->getCodSistema(), $obj->getCodDireito());

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
    public function getListaGruposByIdEvento(GrupoSistema $obj, $debug=false) {
        try{
            $sql = sprintf('SELECT g.nome_grupo, sg.* FROM sis_direitos_grupos_sistemas sg'
                    . ' INNER JOIN `sis_grupos` g ON g.cod_grupo=sg.cod_grupo'
                    . ' WHERE sg.cod_evento=\'%s\'  order by g.ordem, g.nome_grupo', $obj->getCodEvento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaGrupos($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function getListaGruposByIdLogin(GrupoSistema $obj, $debug=false) {
        try{
            $sql = sprintf('SELECT g.nome_grupo, sg.* FROM sis_direitos_grupos_sistemas sg'
                    . ' INNER JOIN `sis_grupos` g ON g.cod_grupo=sg.cod_grupo'
                    . ' INNER JOIN `sis_ligacao_usuarios_grupos` lug ON lug.cod_grupo=g.cod_grupo'
                    . ' WHERE lug.cod_usuario=\'%s\' '
                    . ' and sg.cod_evento=\'%s\' order by g.ordem, g.nome_grupo', $obj->getCodUsuario(), $obj->getCodEvento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaGrupos($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function getListaGruposByIdUsuario(GrupoSistema $obj, $debug=false) {
        try{
            $sql = sprintf('SELECT g.nome_grupo, sg.* FROM sis_direitos_grupos_sistemas sg'
                    . ' INNER JOIN `sis_grupos` g ON g.cod_grupo=sg.cod_grupo'
                    . ' INNER JOIN `sis_ligacao_usuarios_grupos` lug ON lug.cod_grupo=g.cod_grupo'
                    . ' WHERE lug.cod_usuario=\'%s\' '
                    . ' and lug.cod_evento=\'%s\' '
                    . ' AND lug.cod_evento=sg.cod_evento order by g.ordem, g.nome_grupo', $obj->getCodUsuario(), $obj->getCodEvento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaGrupos($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function insert(GrupoSistema $obj, $debug=false) {
        try {
            $sql = "SELECT * FROM sis_direitos_grupos_sistemas WHERE 1=0";
            $rs = $this->conex->Execute($sql);

            $arrayColunas = $this->conex->MetaColumnNames("sis_direitos_grupos_sistemas");
            $fws_ = $obj->getAllForArray($arrayColunas,false);

            $insertSQL = $this->conex->GetInsertSQL($rs ,$fws_, true, true);
            $sql = sprintf($insertSQL);

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function delete(GrupoSistema $obj, $debug=false) {
        try {
            $sql = sprintf('DELETE FROM sis_direitos_grupos_sistemas WHERE cod_evento=\'%s\' AND cod_grupo=\'%s\' AND cod_sistema=\'%s\' AND cod_direito=\'%s\'',
                $obj->getCodEvento(), $obj->getCodGrupo(), $obj->getCodSistema(), $obj->getCodDireito());

            $this->conex->debug = $debug;

            $rs = $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function getListaModulosByEvento($codEvento, $debug=false) {
        try {
            $sql = sprintf('SELECT sg.nome_grupo, ss.nome_sistema,
                    CASE dgs.cod_direito
                        WHEN 1 THEN \'Leitura\'
                        WHEN 2 THEN \'Adicionar\'
                        WHEN 3 THEN \'Alterar\'
                        WHEN 4 THEN \'Excluir\'
                        WHEN 5 THEN \'Administrar\'
                    END as nome_direito,
                    dgs.cod_grupo, dgs.cod_sistema, dgs.cod_direito
                FROM sis_direitos_grupos_sistemas dgs
                INNER JOIN sis_grupos sg ON sg.cod_grupo = dgs.cod_grupo
                INNER JOIN sis_sistemas ss ON ss.cod_sistema = dgs.cod_sistema
                WHERE dgs.cod_evento = \'%s\'
                    AND sg.cod_grupo <> 999
                ORDER BY sg.nome_grupo, ss.nome_sistema, dgs.cod_direito', intval($codEvento));

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }
            $lista = array();
            while (!$rs->EOF) {
                $lista[] = $rs->fields;
                $rs->MoveNext();
            }
            return $lista;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function existeAssociacao($codEvento, $codGrupo, $codSistema, $codDireito, $debug=false) {
        try {
            $sql = sprintf('SELECT 1 FROM sis_direitos_grupos_sistemas
                WHERE cod_evento = \'%s\'
                AND cod_grupo = \'%s\'
                AND cod_sistema = \'%s\'
                AND cod_direito = \'%s\'',
                intval($codEvento), intval($codGrupo), intval($codSistema), intval($codDireito));

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function getModulosByEvento($codEvento, $debug=false) {
        try {
            $sql = sprintf('SELECT cod_grupo, cod_sistema, cod_direito
                FROM sis_direitos_grupos_sistemas
                WHERE cod_evento = \'%s\'', intval($codEvento));

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }
            $lista = array();
            while (!$rs->EOF) {
                $lista[] = $rs->fields;
                $rs->MoveNext();
            }
            return $lista;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function getSelectEventos($debug=false) {
        try {
            $sql = "SELECT cod_evento, titulo FROM tab_eventos ORDER BY cod_evento DESC";

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }
            $lista = array();
            while (!$rs->EOF) {
                $lista[] = $rs->fields;
                $rs->MoveNext();
            }
            return $lista;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
}
?>
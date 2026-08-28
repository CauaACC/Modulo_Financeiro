<?php

class ProjetoDAO extends DAO
{

    public function __construct()
    {
        parent::__construct();
    }

    public function carregarProjetoTotal(Projeto $obj, $debug = false)
    {
        try {

            $sql = sprintf('SELECT d.*, COUNT(*) AS qtd_desconto
                , SUM(porcentagem_desconto) AS porcentagem_total_desconto
                , SUM(valor_desconto) AS valor_total_desconto
            FROM tab_projetos d WHERE d.cod_evento=\'%s\' and d.cpf= \'%s\' and d.status=\'ATIVADA\'
            group by d.cpf', $obj->getCodEvento(), $obj->getCpf());

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

    public function getById(Projeto $obj, $debug = false)
    {
        try {

            $sql = sprintf('SELECT tis.* FROM tab_projetos as tis
            WHERE tis.cod_evento=\'%s\' and tis.cod_projeto= \'%s\'', $obj->getCodEvento(), $obj->getCodProjeto());

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

    public function getByCpf(Projeto $obj, $debug = false)
    {
        try {

            $sql = sprintf("SELECT tis.* FROM tab_projetos tis
            WHERE tis.cod_evento='%s'" , $obj->getCodEvento());

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

    public function getLastId($condicao = false, $debug = false)
    {
        try {
            $sql = "SELECT ifnull(MAX(cod_projeto),0)+1 FROM tab_projetos";

            $rs = $this->conex->Execute($sql);
            if ((!$rs) or ($rs->EOF)) {
                return false;
            } else {
                foreach ($rs as $rows) {

                }
                return $rows['0'];
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function getByIdExiste(Projeto $obj, $debug = false)
    {
        try {
            $sql = sprintf('SELECT cod_projeto FROM tab_projetos WHERE cod_evento=\'%s\' and cod_projeto= \'%s\'', $obj->getCodEvento(), $obj->getCodProjeto());

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

    public function getAll(Projeto $obj, $debug = false)
    {
        try {
            $sql = sprintf('SELECT tiac.* FROM tab_projetos tiac');

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if ((!$rs) or ($rs->EOF)) {
                return false;
            } else {
                foreach ($rs as $rows) {
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaProjetos($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function carregarListaProjetoByIdEvento(Projeto $obj, $condicoes = false, $debug = false)
    {
        try {
            switch ($condicoes['ordenar']) {
                default:
                    $orderBy = " order by tiac.cod_projeto desc ";
                    break;
            }

//            $sql = sprintf('SELECT tis.*, ti.cod_inscrito, ti.cod_inscrito as cod_inscricao FROM tab_projetos tis
//            LEFT JOIN tab_inscritos ti on (tis.cpf=ti.cpf)  and ti.cpf<>\'\' and ti.status=\'ATIVADA\' and tis.cod_evento=ti.cod_evento
//            WHERE tis.cod_evento=\'%s\'
//            '.$orderBy,$obj->getCodEvento());

            $sql = sprintf('SELECT tiac.* FROM tab_projetos tiac
            WHERE tiac.cod_evento=\'%s\'' . $orderBy . ' ', $obj->getCodEvento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if ((!$rs) or ($rs->EOF)) {
                return false;
            } else {
                foreach ($rs as $rows) {
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaProjetos($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function insert(Projeto $obj, $debug = false)
    {
        try {
            $obj->setDatIncl(date('Y-m-d H:i:s'));
            $obj->setResIncl($_SESSION['_cod_usuario']);
            $obj->setIp($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgent($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao(1);
            $obj->setStatus('ATIVADA');


            $sql = "SELECT * FROM tab_projetos WHERE cod_evento=" . $obj->getCodEvento() . " and cod_projeto=0";
            $rs = $this->conex->Execute($sql);

            $arrayColunas = $this->conex->MetaColumnNames("tab_projetos");
            $fws_ = $obj->getAllForArray($arrayColunas, false);

            $insertSQL = $this->conex->GetInsertSQL($rs, $fws_, true, true);
            $sql = sprintf($insertSQL);

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $obj->setCodProjeto($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function update(Projeto $obj, $debug = false)
    {
        try {
            $obj->setDatAlter(date('Y-m-d H:i:s'));
            $obj->setResAlter($_SESSION['_cod_usuario']);
            $obj->setIp($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgent($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao($obj->getVersao() + 1);
            $obj->setStatus('ATIVADA');

            $sql = "SELECT * FROM tab_projetos WHERE cod_evento = " . $obj->getCodEvento() . " and cod_projeto = " . $obj->getCodProjeto();
            $rs = $this->conex->Execute($sql);
            $arrayColunas = $this->conex->MetaColumnNames("tab_projetos");
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

    public function delete(Projeto $obj, $debug = false)
    {
        try {
            $sql = sprintf('DELETE FROM tab_projetos WHERE cod_evento=\'%s\' and cod_projeto= \'%s\'', $obj->getCodEvento(), $obj->getCodProjeto());

            $this->conex->debug = $debug;

            $rs = $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
}
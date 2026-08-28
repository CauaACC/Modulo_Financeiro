<?php

class MidiaUploadDAO extends DAO
{

    public function __construct()
    {
        $this->tableName = "tab_arquivo";
        parent::__construct();
    }

    public function getById(MidiaUpload $obj, $debug = false)
    {
        try {
            $sql = sprintf('SELECT * FROM ' . $this->tableName . ' '
                . 'WHERE cod_evento=\'%s\' and cod_arquivo= \'%s\' '
                , $obj->getCodEvento(), $obj->getCodArquivo());

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

    public function getByIdExiste(MidiaUpload $obj, $debug = false)
    {
        try {
            $sql = sprintf('SELECT cod_arquivo FROM ' . $this->tableName . ' '
                . 'WHERE cod_evento=\'%s\' and cod_arquivo= \'%s\' '
                , $obj->getCodEvento(), $obj->getCodArquivo());

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

    public function carregarListaMidiaUploadsByPedidoId(MidiaUpload $obj, $debug = false)
    {
        try {
            $sql = sprintf('SELECT * FROM ' . $this->tableName . ' '
                . 'WHERE cod_evento=\'%s\' and pedido_id= \'%s\' '
                , $obj->getCodEvento(), $obj->getPedidoId());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if ((!$rs) or ($rs->EOF)) {
                return false;
            } else {
                foreach ($rs as $rows) {
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaMidiaUploads($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function insert(MidiaUpload $obj, $debug = 1)
    {
        try {
            $obj->setDatIncl(date('Y-m-d H:i:s'));
            $obj->setResIncl($_SESSION['_cod_usuario']);
            $obj->setIpIncl($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentIncl($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao(1);
            $obj->setStatus('ATIVADA');

            $sql = "SELECT * FROM $this->tableName WHERE "
                . " cod_evento=" . $obj->getCodEvento() . " and cod_arquivo=0 ";

            $rs = $this->conex->Execute($sql);
            $arrayColunas = $this->conex->MetaColumnNames($this->tableName);
            $fws_ = $obj->getAllForArray($arrayColunas, false);
            $insertSQL = $this->conex->GetInsertSQL($rs, $fws_, true, false);
            $sql = sprintf($insertSQL);

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $obj->setCodArquivo($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function update(MidiaUpload $obj, $debug = false)
    {
        try {
            $obj->setDatAlter(date('Y-m-d H:i:s'));
            $obj->setResAlter($_SESSION['_cod_usuario']);
            $obj->setIpAlter($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentAlter($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao($obj->getVersao() + 1);
            $sql = "SELECT * FROM " . $this->tableName . " WHERE cod_evento = " . $obj->getCodEvento()
                . " and cod_arquivo = " . $obj->getCodArquivo();
            $rs = $this->conex->Execute($sql);
            $arrayColunas = $this->conex->MetaColumnNames($this->tableName);
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

    public function delete(MidiaUpload $obj, $debug = false)
    {
        try {
            $sql = sprintf('DELETE FROM ' . $this->tableName . ' WHERE cod_evento=\'%s\' AND cod_arquivo= \'%s\' ', $obj->getCodEvento(), $obj->getCodArquivo());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            return $rs !== false;
        } catch (ADODB_Exception $e) {
            echo 'Erro: ' . $e->getMessage();
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function getAll(MidiaUpload $obj, $debug = false)
    {
        try {
            $whereSql = "";
            if($obj->getTipoArquivo()!=''){
                $whereSql = " and tipo_arquivo = '".$obj->getTipoArquivo()."' ";
            }
            if($obj->getArrayTipoArquivo()!=''){
                $arrayTipoArquivo = $obj->getArrayTipoArquivo();
                $arrayTipoArquivo = "('" . implode("','", $arrayTipoArquivo) . "')";
                $whereSql = " and tipo_arquivo in ".$arrayTipoArquivo." ";
            }            
            $sql = sprintf('SELECT * FROM ' . $this->tableName . ' WHERE cod_evento=\'%s\' '.$whereSql.' ', $obj->getCodEvento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if ((!$rs) or ($rs->EOF)) {
                return false;
            } else {
                $lista = array();
                while (!$rs->EOF) {
                    $vo = new MidiaUpload();
                    $vo->setAll($rs->fields);
                    $lista[] = $vo;
                    $rs->MoveNext();
                }
                // Inverte a ordem dos resultados
                $lista = array_reverse($lista);
                return $lista;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function carregarListaMidiasUploadByMidias(MidiaUpload $obj, $debug = false)
    {
        try {
            $whereSql = "";
            if($obj->getTipoArquivo()!=''){
                $whereSql = " and tipo_arquivo = '".$obj->getTipoArquivo()."' ";
            }
            if($obj->getArrayTipoArquivo()!=''){
                $arrayTipoArquivo = $obj->getArrayTipoArquivo();
                $arrayTipoArquivo = "('" . implode("','", $arrayTipoArquivo) . "')";
                $whereSql = " and tipo_arquivo in ".$arrayTipoArquivo." ";
            }  

            $sql = sprintf('SELECT ta.*, too.titulo as titulo_oficina, top.titulo as titulo_perfil  
            FROM tab_arquivo AS ta  
            LEFT JOIN tab_opcoes_oficinas AS too ON too.cod_oficina = ta.cod_oficina  
            LEFT JOIN tab_opcoes_perfis AS top ON top.cod_perfil = ta.cod_perfil  
            WHERE ta.cod_evento=%s '.$whereSql.' ', $obj->getCodEvento());

//            and (tipo_arquivo=\'ASSINATURA-COORDENADOR\' or tipo_arquivo=\'PROGRAMA\')
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if ((!$rs) or ($rs->EOF)) {
                return false;
            } else {
                $lista = array();
                while (!$rs->EOF) {
                    $vo = new MidiaUpload();
                    $vo->setAll($rs->fields);
                    $lista[] = $vo;
                    $rs->MoveNext();
                }
                // Inverte a ordem dos resultados
                $lista = array_reverse($lista);
                return $lista;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    /** Retorna o cod_arquivo do PROGRAMA ja registrado para o escopo, ou false. */
    public function getCodProgramaByEscopo(MidiaUpload $obj, $debug = false)
    {
        try {
            // Escopo GERAL (sem oficina) grava cod_oficina NULL em uns registros
            // e 0 em outros, porque o controller passa string vazia. Como
            // "cod_oficina = 0" nunca casa com NULL em SQL, a busca falhava
            // sempre e cada "Gerar verso" inseria uma linha nova em vez de
            // atualizar a existente. Aqui os dois valem como "geral".
            $codOficina = (int) $obj->getCodOficina();
            $filtroOficina = ($codOficina > 0)
                ? sprintf('cod_oficina=%d', $codOficina)
                : '(cod_oficina=0 OR cod_oficina IS NULL)';

            $sql = sprintf(
                "SELECT cod_arquivo FROM %s WHERE cod_evento=%d AND tipo_arquivo='PROGRAMA' "
                . "AND %s ORDER BY cod_arquivo LIMIT 1",
                $this->tableName, (int) $obj->getCodEvento(), $filtroOficina
            );
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if ((!$rs) or ($rs->EOF)) {
                return false;
            }
            return $rs->fields['cod_arquivo'];
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    /**
     * Upsert de um registro de PROGRAMA gerado (sem $_FILES). Reusa insert/update.
     */
    public function upsertProgramaGerado(MidiaUpload $obj, $debug = false)
    {
        $cod = $this->getCodProgramaByEscopo($obj, $debug);
        if ($cod) {
            $obj->setCodArquivo($cod);
            return $this->update($obj, $debug);
        }
        return $this->insert($obj, $debug);
    }
}
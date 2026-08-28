<?php
class InscritoPerfilDAO extends DAO{
 
    public function __construct() {        
        parent::__construct();
    }  
    public function getById(InscritoPerfil $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT * FROM tab_lig_inscritos_perfis 
            WHERE cod_evento=\'%s\' and cod_perfil= \'%s\' and cod_inscrito= \'%s\''
            , $obj->getCodEvento(),$obj->getCodPerfil(),$obj->getCodInscrito());

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

    public function getByIdExiste(InscritoPerfil $obj, $debug=false) {
        try {
            $sql = sprintf('SELECT * FROM tab_lig_inscritos_perfis 
            WHERE cod_evento=\'%s\' and cod_perfil= \'%s\' and cod_inscrito= \'%s\''
            , $obj->getCodEvento(),$obj->getCodPerfil(),$obj->getCodInscrito());

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
    public function getListaPerfilByIdInscrito(InscritoPerfil $obj, $debug=false) {
        try{
            if ($obj->getCodEvento() != 98) {
                $sql = sprintf('SELECT lp.*, op.titulo, op.liberar_certificado, op.liberar_certificado_congressista, op.carga_horaria_certificado FROM tab_lig_inscritos_perfis lp
                    inner join tab_opcoes_perfis op on op.cod_perfil=lp.cod_perfil
                WHERE lp.cod_evento=\'%s\' and lp.cod_inscrito=\'%s\''
                , $obj->getCodEvento(),$obj->getCodInscrito());
            } else {
                $sql = sprintf("
                SELECT * FROM(
                SELECT 
                    lp.cod_perfil, 
                    op.titulo, 
                    op.liberar_certificado, 
                    op.liberar_certificado_congressista, 
                    op.carga_horaria_certificado 
                FROM 
                    tab_lig_inscritos_perfis lp
                INNER JOIN tab_opcoes_perfis op ON op.cod_perfil = lp.cod_perfil
                WHERE 
                    lp.cod_evento = '%s' 
                    AND lp.cod_inscrito = '%s'
                
                UNION ALL

                SELECT 
                    ti.cod_perfil, 
                    op.titulo, 
                    op.liberar_certificado, 
                    op.liberar_certificado_congressista, 
                    op.carga_horaria_certificado 
                FROM 
                    tab_inscritos ti
                INNER JOIN tab_opcoes_perfis op ON op.cod_perfil = ti.cod_perfil
                WHERE 
                    ti.cod_evento = '%s' and ti.cod_inscrito = '%s'
                    ) AS lp1 JOIN tab_opcoes_perfis op1 ON op1.cod_perfil = lp1.cod_perfil
                ", $obj->getCodEvento(), $obj->getCodInscrito(),$obj->getCodEvento(), $obj->getCodInscrito());
            }

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaPerfil($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }       
    public function carregarListaInscritoPerfil(InscritoPerfil $obj, $condicoes, $debug=false) {
        try{
            $orderBy = "";
            if($condicoes['ordenar']!=''){
                switch ($condicoes['ordenar']) {
                    case 'cod_inscrito':
                        $orderBy = " order by i.cod_inscrito ";
                        break;
                    case 'cpf':
                        $orderBy = " order by i.cpf ";
                        break;
                    
                    default:
                        $orderBy = " order by i.nome_inscrito ";
                        break;
                }
            }
            $sqlWhere = "";
            if ($condicoes['cod_inscrito'] != '') {
                $sqlWhere .= " and i.cod_inscrito = '" . $condicoes['cod_inscrito'] . "' ";
            }
            // if ($condicoes['cpf'] != '') {
            //     $sqlWhere .= " and i.cpf = '" . $condicoes['cpf'] . "' ";
            // }
            if ($condicoes['nome_inscrito'] != '') {
                $sqlWhere .= " and i.nome_inscrito like '%" . $condicoes['nome_inscrito'] . "%' ";
            }
            if ($condicoes['cod_perfil'] != '') {
                $sqlWhere .= " and (i.cod_perfil = " . $condicoes['cod_perfil'] . " or lip.cod_perfil=" . $condicoes['cod_perfil'] . ") ";
            }

            // $sql = "SELECT distinct i.nome_inscrito, i.cpf, i.e_mail, i.cod_inscrito, i.cod_inscrito as cod_inscricao, i.status, lip.cod_perfil 
            //     ,(SELECT GROUP_CONCAT(p.cod_perfil SEPARATOR ',')
            //         FROM tab_lig_inscritos_perfis p
            //         WHERE p.cod_inscrito = i.cod_inscrito) AS lista_perfis
            // FROM tab_inscritos i
            // left join tab_lig_inscritos_perfis lip on lip.cod_inscrito=i.cod_inscrito
            // WHERE i.cod_evento=".$obj->getCodEvento()." and i.status='ATIVADA' ".$sqlWhere." ".$orderBy;
            $sql = "
            SELECT 
                i.nome_inscrito,
                i.cpf,
                i.e_mail,
                i.cod_inscrito,
                i.cod_inscrito AS cod_inscricao,
                i.status,
                i.flg_presenca_inscrito,
                i.flg_bloquear_certificado_geral,
                GROUP_CONCAT(DISTINCT lip.cod_perfil ORDER BY lip.cod_perfil SEPARATOR ',') AS lista_perfis
            FROM tab_inscritos i
            LEFT JOIN tab_lig_inscritos_perfis lip
                ON lip.cod_inscrito = i.cod_inscrito
            WHERE i.cod_evento = ".$obj->getCodEvento()."
            AND i.status = 'ATIVADA' ".$sqlWhere."
            GROUP BY
                i.nome_inscrito, i.cpf, i.e_mail, i.cod_inscrito, i.status
            ".$orderBy;

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    // try {
                    //     $obj->carregarListaPerfil();
                    // } catch (Exception $exc) {}                    
                    $return[] = clone $obj;
                }
                $obj->setListaInscritoPerfil($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }       

    public function insert(InscritoPerfil $obj, $debug=false) {
        try {           
            $obj->setStatus('ATIVADA');
            $obj->setDatInscricao(date('Y-m-d H:i:s'));
            $obj->setResIncl($_SESSION['_cod_usuario']);
            $obj->setIpIncl($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentIncl($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao(1);
            
            $sql = "SELECT * FROM tab_lig_inscritos_perfis 
            WHERE cod_evento=".$obj->getCodEvento()." and cod_perfil=0  and cod_inscrito=0"; 
            
            $rs = $this->conex->Execute($sql);     
            
            $arrayColunas = $this->conex->MetaColumnNames("tab_lig_inscritos_perfis");
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
    public function update(InscritoPerfil $obj, $debug=false) {
        try {           
            $obj->setDatAlter(date('Y-m-d H:i:s'));
            $obj->setResAlter($_SESSION['_cod_usuario']);
            $obj->setIpAlter($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentAlter($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao($obj->getVersao()+1);
            
            $sql = "SELECT * FROM tab_lig_inscritos_perfis 
            WHERE cod_evento = ".$obj->getCodEvento()." and cod_inscrito = ".$obj->getCodInscrito()." and cod_perfil = ".$obj->getCodPerfil(); 
            
            $rs = $this->conex->Execute($sql); 
            $arrayColunas = $this->conex->MetaColumnNames("tab_lig_inscritos_perfis");
            $fws_ = $obj->getAllForArray($arrayColunas,false);
            $updateSQL = $this->conex->GetUpdateSQL($rs,$fws_, true, false, true);            
            
            $sql = sprintf($updateSQL);
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            return $rs;
            
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    /**
     * Atualiza SOMENTE as 3 colunas de carga horaria da ligacao inscrito<->perfil.
     * A tabela tem PK COMPOSTA (cod_evento+cod_inscrito+cod_perfil) e NAO tem
     * cod_inscrito_perfil, entao salvar() so sabe INSERIR (daria PK duplicada no
     * editar). Aqui e um UPDATE direcionado -- mesmo espirito de marcarPresenca.
     * Vazio grava NULL = volta a usar o padrao do perfil (carga_horaria_certificado).
     */
    public function atualizarCarga(InscritoPerfil $obj, $debug=false) {
        try {
            $ch  = $obj->getCargaHoraria();
            $cht = $obj->getCargaHorariaTeorica();
            $chp = $obj->getCargaHorariaPratica();
            $sql = sprintf(
                "UPDATE tab_lig_inscritos_perfis SET carga_horaria = %s, carga_horaria_teorica = %s, "
                . "carga_horaria_pratica = %s, data_alteracao = %s "
                . "WHERE cod_evento = %d AND cod_inscrito = %d AND cod_perfil = %d",
                ($ch  === null || $ch  === '') ? 'NULL' : $this->conex->qstr($ch),
                ($cht === null || $cht === '') ? 'NULL' : $this->conex->qstr($cht),
                ($chp === null || $chp === '') ? 'NULL' : $this->conex->qstr($chp),
                $this->conex->qstr(date('Y-m-d H:i:s')),
                (int)$obj->getCodEvento(),
                (int)$obj->getCodInscrito(),
                (int)$obj->getCodPerfil()
            );
            $this->conex->debug = $debug;
            $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function delete(InscritoPerfil $obj, $debug=false) {
        try {
            $sql = sprintf('DELETE FROM tab_lig_inscritos_perfis
            WHERE cod_evento=\'%s\' and cod_perfil= \'%s\' and cod_inscrito= \'%s\''
            , $obj->getCodEvento(),$obj->getCodPerfil(),$obj->getCodInscrito());
            
            $this->conex->debug = $debug;
            
            $rs = $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }    
}
?>
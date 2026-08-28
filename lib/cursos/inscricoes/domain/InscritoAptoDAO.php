<?php
class InscritoAptoDAO extends DAO{
 
    public function __construct() {        
        parent::__construct();
    }  
    public function getById(InscritoApto $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT * FROM tab_inscritos_aptos WHERE cod_evento=\'%s\' '
                    . 'and cod_inscrito= \'%s\'', $obj->getCodEvento(),$obj->getCodInscritoApto());

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
    public function getByCpf(InscritoApto $obj, $condicoes=false, $debug=false) {
        try{
            $whereCodPerfil = "";
            if($obj->getCodEvento()==44){
                $whereCodPerfil = " and cod_perfil=".$obj->getCodPerfil();
            }
            if($obj->getCodEvento()==42){
                $whereCodPerfil = " and cod_perfil=".$obj->getCodPerfil();
            }
            if($obj->getCodEvento()==50){
                $whereCodPerfil = " and cod_perfil=".$obj->getCodPerfil();
            }
            if($obj->getCodEvento()==73){
                $whereCodPerfil = " and cod_perfil=".$obj->getCodPerfil();
            }
            if($condicoes['verificarAptoPorPerfil']==true){
                $whereCodPerfil = " and cod_perfil=".$obj->getCodPerfil();
            }
            $sql = sprintf('SELECT * FROM tab_inscritos_aptos WHERE cod_evento=\'%s\' '
                    . 'and cpf= \'%s\' and status=\'ATIVADA\' '.$whereCodPerfil.' ', $obj->getCodEvento(),$obj->getCpf());

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
    public function getByEmail(InscritoApto $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT * FROM tab_inscritos_aptos WHERE cod_evento=\'%s\' '
                    . 'and email= \'%s\' and status=\'ATIVADA\' ', $obj->getCodEvento(),$obj->getEmail());

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
    public function getLastId($condicao=false, $debug=false) {
        try {
            $sql = "SELECT ifnull(MAX(cod_inscrito),0)+1 FROM tab_inscritos_aptos";

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
    public function getByIdExiste(InscritoApto $obj, $debug=false) {
        try {
            $sql = sprintf('SELECT cod_inscrito FROM tab_inscritos_aptos '
            . 'WHERE cod_evento=\'%s\' and cod_inscrito= \'%s\'', $obj->getCodEvento(),$obj->getCodInscritoApto());

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
    public function getAll(InscritoApto $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT tiac.*, ti.cod_inscrito as cod_inscricao, ti.nome_inscrito as nome_participante FROM tab_inscritos_aptos tiac
            LEFT JOIN tab_inscritos ti on tiac.cpf=ti.cpf AND ti.status=\'ATIVADA\' AND ti.cod_evento=tiac.cod_evento
            order by cod_evento desc ');

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaEventos($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }       
    public function carregarListaInscritoAptosByIdEvento(InscritoApto $obj, $debug=false) {
        try{
            $sql = sprintf('SELECT tiac.*, ti.cod_inscrito as cod_inscricao, ti.nome_inscrito as nome_participante 
                , o.titulo as nome_oficina, p.titulo as nome_perfil, ti.status as status_inscrito, ti.status_processo
                FROM tab_inscritos_aptos tiac
                LEFT JOIN tab_inscritos ti on tiac.cpf=ti.cpf AND ti.status=\'ATIVADA\' AND ti.cod_evento=tiac.cod_evento
                LEFT JOIN tab_opcoes_oficinas o on o.cod_oficina=tiac.cod_oficina
                LEFT JOIN tab_opcoes_perfis p on p.cod_perfil=tiac.cod_perfil
            WHERE tiac.cod_evento=\'%s\'
            order by tiac.cod_inscrito desc ',$obj->getCodEvento());
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaInscritoAptos($return);
                return true;                
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function carregarListaInscritoAptosBy(InscritoApto $obj, $condicoes, $debug=false) {
        try{
            $whereSql = "";
            if($obj->getCodPerfil()){
                $whereSql .= " and cod_perfil = ".$obj->getCodPerfil();
            }  
            $sql = sprintf('SELECT * FROM tab_inscritos_aptos '
                    . ' WHERE cod_evento=\'%s\' '
                    . $whereSql
                    . ' ',$obj->getCodEvento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaInscritoAptos($return);
                return true;                
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function insert(InscritoApto $obj, $debug=false) {
        try {           
            $obj->setDatIncl(date('Y-m-d H:i:s'));
            $obj->setResIncl($_SESSION['_cod_usuario']);
            $obj->setIp($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgent($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao(1);
            $obj->setNomeInscrito($obj->getNomeInscritoApto());
            
                        
            $sql = "SELECT * FROM tab_inscritos_aptos "
            . "WHERE cod_evento=".$obj->getCodEvento()." and cod_inscrito=0"; 
            $rs = $this->conex->Execute($sql);     
            
            $arrayColunas = $this->conex->MetaColumnNames("tab_inscritos_aptos");
            $fws_ = $obj->getAllForArray($arrayColunas,false);
            
            $insertSQL = $this->conex->GetInsertSQL($rs ,$fws_, true, true);
            $sql = sprintf($insertSQL);
            
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $obj->setCodInscrito($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function update(InscritoApto $obj, $debug=false) {
        try {           
            $obj->setDatAlter(date('Y-m-d H:i:s'));
            $obj->setResAlter($_SESSION['_cod_usuario']);
            $obj->setIp($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgent($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao($obj->getVersao()+1);
            $obj->setNomeInscrito($obj->getNomeInscritoApto());
            
            $sql = "SELECT * FROM tab_inscritos_aptos "
            . "WHERE cod_evento = ".$obj->getCodEvento()." and cod_inscrito = ".$obj->getCodInscrito(); 
            $rs = $this->conex->Execute($sql); 
            $arrayColunas = $this->conex->MetaColumnNames("tab_inscritos_aptos");
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
    public function delete(InscritoApto $obj, $debug=false) {
        try {
            $sql = sprintf('DELETE FROM tab_inscritos_aptos '
            . 'WHERE cod_evento=\'%s\' and cod_inscrito= \'%s\'', $obj->getCodEvento(),$obj->getCodInscrito());
            
            $this->conex->debug = $debug;
            
            $rs = $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }    
    public function desativar(InscritoApto $obj, $debug=false) {
        try {
            $sql = sprintf('UPDATE tab_inscritos_aptos SET status=\'DESATIVADA\' '
            . 'WHERE cod_evento=\'%s\' and cod_inscrito= \'%s\'', $obj->getCodEvento(),$obj->getCodInscrito());
            
            $this->conex->debug = $debug;
            
            $rs = $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }    

/** @todo o sql abaixo é usado para importar inscrito aptos de outro evento
 * porém uma ideia que pode ser aplicada é ao invés de importar, vc escrever uma ação para em ação em lote para exportar a partir do evento de origem os inscritos aptos para o evento futuro. Assim sendo seria necessário que o evento futuro permitisse receber exportação de inscrito apto do evento de origem, assim seria mais facil filtar e executar a ação a a partir do event o de oriem e averia um controle para que isso fosse possível a partir do evento futuro */    
// #edital 010 evento 50 assistente 213 e 237 -> 248, psicologo 212 e 236 -> 247
// INSERT INTO `tab_inscritos_aptos` (cod_evento, nome_inscrito, cpf, email, telefone, cod_perfil)
// SELECT 50
// #, i.cod_inscrito
// , i.nome_inscrito, i.cpf, i.e_mail, CONCAT(i.fone_contato_ddd, '-', i.fone_contato) AS telefone
// #, p.cod_perfil, p.titulo
// , CASE 
//     WHEN i.cod_perfil = 213 THEN 248
//     WHEN i.cod_perfil = 237 THEN 248
//     WHEN i.cod_perfil = 212 THEN 247
//     WHEN i.cod_perfil = 236 THEN 247
//     ELSE i.cod_perfil
//   END AS cod_perfil
// FROM tab_inscritos i
// INNER JOIN tab_opcoes_perfis p ON i.cod_perfil = p.cod_perfil
// WHERE i.cod_inscrito IN (23053,24307 ,24419 ,22423 ,23511 ,24053 ,24165 ,24792 ,24829 ,24832 ,24934 ,24947 ,24985 ,25005 ,25016 ,25045 ,25047 ,25084 ,25122 ,25135 ,25142 ,24882);

// #edital 002 - 230 serviço social e 231 psicoloco carga no evento 42
// INSERT INTO `tab_inscritos_aptos` (cod_evento, nome_inscrito, cpf, email, telefone, cod_perfil)
// SELECT 42
// #, i.cod_inscrito
// , i.nome_inscrito, i.cpf, i.e_mail, CONCAT(i.fone_contato_ddd, '-', i.fone_contato) AS telefone
// #, p.cod_perfil, p.titulo
// , CASE 
//     WHEN i.cod_perfil = 215 THEN 230
//     WHEN i.cod_perfil = 214 THEN 231
//     ELSE i.cod_perfil
//   END AS cod_perfil
// FROM tab_inscritos i
// INNER JOIN tab_opcoes_perfis p ON i.cod_perfil = p.cod_perfil
// WHERE i.cod_inscrito IN (24453 ,24309 ,23951 ,23558 ,22055 ,23245 ,22500);

// #edital 001 evento 44, assistente 213 e 237 -> 232, psicologo 212 e 236 -> 233
// INSERT INTO `tab_inscritos_aptos` (cod_evento, nome_inscrito, cpf, email, telefone, cod_perfil)
// SELECT 44
// #, i.cod_inscrito
// , i.nome_inscrito, i.cpf, i.e_mail, CONCAT(i.fone_contato_ddd, '-', i.fone_contato) AS telefone
// #, p.cod_perfil, p.titulo
// , CASE 
//     WHEN i.cod_perfil = 213 THEN 232
//     WHEN i.cod_perfil = 212 THEN 233
//     WHEN i.cod_perfil = 236 THEN 233
//     ELSE i.cod_perfil
//   END AS cod_perfil
// FROM tab_inscritos i
// INNER JOIN tab_opcoes_perfis p ON i.cod_perfil = p.cod_perfil
// WHERE i.cod_inscrito IN (21471 ,21596  ,21601  ,21639  ,21659  ,21687  ,21760  ,21817  ,21830  ,21834  ,21971  ,22002  ,22095  ,22122  ,22177  ,22263  ,22265  ,22441  ,22456  ,22539  ,22548  ,22578  ,22609  ,22621  ,22725  ,22751  ,22814  ,22953  ,23013  ,23053  ,23054  ,23074  ,23095  ,23106  ,23121  ,23148  ,23198  ,23225  ,23235  ,23236  ,23258  ,23359  ,23404  ,23497  ,23504  ,23507  ,23537  ,23576  ,23589  ,23613  ,23663  ,23697  ,23702  ,23722  ,23752  ,23767  ,23821  ,23859  ,23861  ,23876  ,23902  ,23949  ,23959  ,23960  ,23982  ,24001  ,24036  ,24078  ,24133  ,24162  ,24181  ,24204  ,24222  ,24227  ,24241  ,24287  ,24307  ,24318  ,24320  ,24334  ,24362  ,24408  ,24419  ,24809  ,25007);
}

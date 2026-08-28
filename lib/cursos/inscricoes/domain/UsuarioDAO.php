<?php
class UsuarioDAO extends DAO{

    public function __construct() {        
        parent::__construct();
    }  
    public function getById(Usuario $obj, $debug=false) {
        try{
            
//            $sql = sprintf('SELECT * FROM sis_usuarios WHERE cod_evento=\'%s\' '
//            . ' and cod_login= \'%s\'', $obj->getCodEvento(),$obj->getCodUsuario());
            $sql = sprintf('SELECT u.*, IFNULL(u.nome_usuario,tc.nome_convidado) nome_convidado FROM sis_usuarios u 
            inner join sis_ligacao_usuarios_eventos lue ON u.cod_usuario=lue.cod_usuario    
            left join tab_convidados tc ON tc.cod_usuario=u.cod_usuario    
            WHERE lue.cod_evento = %s and lue.cod_usuario= \'%s\'', $obj->getCodEvento(),$obj->getCodUsuario());

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
    public function getByEmail(Usuario $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT u.* FROM sis_usuarios u   
            WHERE u.status=\'ATIVADA\' and u.login = \'%s\' LIMIT 1', $obj->getLogin());

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
    public function getByLogin(Usuario $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT u.* FROM sis_usuarios u   
            WHERE u.status=\'ATIVADA\' and u.login = \'%s\' LIMIT 1 ', $obj->getLogin());

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
    public function getByLoginDesc(Usuario $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT u.* FROM sis_usuarios u   
            WHERE u.status=\'ATIVADA\' and u.login = \'%s\' LIMIT 1 order by u.cod_usuario desc', $obj->getLogin());

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
    public function getByCodConvidado(Usuario $obj, $debug=false) {
        try{
            
//            $sql = sprintf('SELECT * FROM sis_usuarios WHERE cod_evento=\'%s\' '
//            . ' and cod_convidado= \'%s\'', $obj->getCodEvento(),$obj->getCodConvidado());
            $sql = sprintf('SELECT u.* FROM sis_usuarios u 
            inner join sis_ligacao_usuarios_eventos lue ON u.cod_usuario=lue.cod_usuario    
            WHERE lue.cod_evento = %s and u.cod_convidado= \'%s\'', $obj->getCodEvento(),$obj->getCodConvidado());

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
            $sql = "SELECT ifnull(MAX(cod_login),0)+1 FROM sis_usuarios";

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
    public function getByIdExiste(Usuario $obj, $debug=false) {
        try {
            $sql = sprintf('SELECT cod_usuario FROM sis_usuarios WHERE cod_evento=\'%s\'
            and cod_usuario= \'%s\'', $obj->getCodEvento(),$obj->getCodUsuario());

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
    public function getAll(Usuario $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT * FROM sis_usuarios order by cod_evento desc');

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
    public function insert(Usuario $obj, $debug=false) {
        try {           
            $obj->setDataInclusao(date('Y-m-d H:i:s'));
            $obj->setSessaoIdInclusao($_SESSION['_cod_usuario']);
            $obj->setIpClienteInclusao($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentInclusao($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao(1);
            $obj->setStatus('ATIVADA');
            if (is_null($obj->getTokenRecuperacaoUsado()) || $obj->getTokenRecuperacaoUsado() === '') {
                $obj->setTokenRecuperacaoUsado(0);
            }
            if (is_null($obj->getTentativasFalhas()) || $obj->getTentativasFalhas() === '') {
                $obj->setTentativasFalhas(0);
            }
            if (is_null($obj->getForcarTrocaSenha()) || $obj->getForcarTrocaSenha() === '') {
                $obj->setForcarTrocaSenha(0);
            }
                        
            $sql = "SELECT * FROM sis_usuarios WHERE cod_evento=0 and cod_usuario=0"; 
            $rs = $this->conex->Execute($sql);     
            
            $arrayColunas = $this->conex->MetaColumnNames("sis_usuarios");
            $fws_ = $obj->getAllForArray($arrayColunas,false);
            
            $insertSQL = $this->conex->GetInsertSQL($rs ,$fws_, true, true);
            $sql = sprintf($insertSQL);
            
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $obj->setCodUsuario($this->conex->Insert_ID());
            
//            /************ LIGAR USUARIOS AO EVENTO *****************************/
//            $sqlUE = "INSERT INTO sis_ligacao_usuarios_eventos(cod_evento, cod_usuario, status)values(".$obj->getCodEvento().",".$obj->getCodUsuario().",'ATIVADA')";
//            $rsUE = $this->conex->Execute($sqlUE);
            
      
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function update(Usuario $obj, $debug=false) {
        try {           
            $obj->setDataAlteracao(date('Y-m-d H:i:s'));
            $obj->setSessaoIdAlteracao($_SESSION['_cod_usuario']);
            $obj->setIpAlteracao($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentAlteracao($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao($obj->getVersao()+1);
            
            $sql = "SELECT * FROM sis_usuarios WHERE cod_usuario = ".$obj->getCodUsuario(); 
            $rs = $this->conex->Execute($sql); 
            $arrayColunas = $this->conex->MetaColumnNames("sis_usuarios");
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
    public function delete(Usuario $obj, $debug=false) {
        try {
            $sql = sprintf('DELETE FROM sis_usuarios WHERE cod_evento=\'%s\' and cod_usuario= \'%s\'', $obj->getCodEvento(),$obj->getCodUsuario());
            
            $this->conex->debug = $debug;
            
            $rs = $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }    
    public function trocarSenha(Usuario $obj, $debug=false) {
        try{
            $obj->setDataAlteracao(date('Y-m-d H:i:s'));
            $obj->setSessaoIdAlteracao($_SESSION['_cod_usuario']);
            $obj->setIpAlteracao($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentAlteracao($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao($obj->getVersao()+1);

            $sql = sprintf(
                "UPDATE sis_usuarios SET
                senha='%s',
                senha_nova='%s',
                session_id='%s',
                dat_alter='%s',
                ip_alter='%s',
                http_user_agent_alter='%s',
                versao=%d
                WHERE login='%s' AND senha='%s'",
                $obj->getSenhaNova(),
                $obj->getSenhaNova(),
                $obj->getSessaoIdAlteracao(),
                $obj->getDataAlteracao(),
                $obj->getIpAlteracao(),
                $obj->getHttpUserAgentAlteracao(),
                $obj->getVersao(),
                $obj->getLogin(),
                $obj->getSenhaAtual()
            );

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    // ==================== METODOS DE SEGURANCA ====================

    /**
     * Busca usuario por login para autenticacao (sem filtrar por senha).
     * Retorna dados de seguranca: senha, senha_hash, tentativas, bloqueio, etc.
     */
    public function getByLoginParaAutenticacao(Usuario $obj, $debug = false) {
        try {
            $sql = sprintf("SELECT su.cod_usuario, su.login, su.senha, su.senha_hash,
                    su.forcar_troca_senha, su.tentativas_falhas, su.bloqueado_ate,
                    su.session_token, su.versao, su.status,
                    IFNULL(su.nome_usuario, tc.nome_convidado) AS nome_convidado,
                    su.cod_inscrito, su.cod_convidado,
                    ti.cod_inscrito AS _cod_inscrito, ti.e_mail
                FROM sis_usuarios su
                LEFT JOIN tab_convidados tc ON tc.cod_usuario = su.cod_usuario
                LEFT JOIN tab_inscritos ti ON su.login = ti.e_mail AND ti.cod_evento = '%s' AND ti.status = 'ATIVADA'
                WHERE su.login = '%s' AND su.status = 'ATIVADA'
                ORDER BY su.cod_usuario DESC LIMIT 1",
                COD_EVENTO, addslashes($obj->getLogin()));

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

    /**
     * Atualiza a senha para bcrypt hash.
     * Limpa senha em texto puro, reseta tentativas, gera novo session_token.
     */
    public function atualizarSenhaHash(Usuario $obj, $debug = false) {
        try {
            $novoSessionToken = SenhaHelper::gerarToken();
            $obj->setSessionToken($novoSessionToken);

            $sql = sprintf(
                "UPDATE sis_usuarios SET
                    senha_hash = '%s',
                    forcar_troca_senha = %d,
                    tentativas_falhas = 0,
                    bloqueado_ate = NULL,
                    session_token = '%s',
                    dat_alter = '%s',
                    ip_alter = '%s',
                    http_user_agent_alter = '%s'
                WHERE cod_usuario = '%s'",
                addslashes($obj->getSenhaHash()),
                intval($obj->getForcarTrocaSenha()),
                addslashes($novoSessionToken),
                date('Y-m-d H:i:s'),
                addslashes($_SERVER['REMOTE_ADDR']),
                addslashes($_SERVER['HTTP_USER_AGENT']),
                addslashes($obj->getCodUsuario())
            );

            $this->conex->debug = $debug;
            $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    /**
     * Incrementa tentativas falhas. Bloqueia por 15 minutos apos 5 falhas.
     */
    public function registrarTentativaFalha($codUsuario, $debug = false) {
        try {
            $sql = sprintf(
                "UPDATE sis_usuarios SET
                    tentativas_falhas = tentativas_falhas + 1,
                    bloqueado_ate = CASE
                        WHEN tentativas_falhas + 1 >= 5 THEN DATE_ADD(NOW(), INTERVAL 15 MINUTE)
                        ELSE bloqueado_ate
                    END
                WHERE cod_usuario = '%s'",
                addslashes($codUsuario)
            );
            $this->conex->debug = $debug;
            $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            return false;
        }
    }

    /**
     * Reseta tentativas e registra ultimo login.
     */
    public function registrarLoginSucesso($codUsuario, $debug = false) {
        try {
            $sql = sprintf(
                "UPDATE sis_usuarios SET
                    tentativas_falhas = 0,
                    bloqueado_ate = NULL,
                    ultimo_login = NOW()
                WHERE cod_usuario = '%s'",
                addslashes($codUsuario)
            );
            $this->conex->debug = $debug;
            $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            return false;
        }
    }

    /**
     * Salva token de recuperacao de senha.
     */
    public function salvarTokenRecuperacao($codUsuario, $token, $expira, $debug = false) {
        try {
            $sql = sprintf(
                "UPDATE sis_usuarios SET
                    token_recuperacao = '%s',
                    token_recuperacao_expira = '%s',
                    token_recuperacao_usado = 0
                WHERE cod_usuario = '%s'",
                addslashes($token),
                addslashes($expira),
                addslashes($codUsuario)
            );
            $this->conex->debug = $debug;
            $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            return false;
        }
    }

    /**
     * Busca usuario por token de recuperacao valido (nao expirado, nao usado).
     */
    public function getByTokenRecuperacao(Usuario $obj, $token, $debug = false) {
        try {
            $sql = sprintf(
                "SELECT * FROM sis_usuarios
                WHERE token_recuperacao = '%s'
                    AND token_recuperacao_usado = 0
                    AND token_recuperacao_expira >= NOW()
                    AND status = 'ATIVADA'
                LIMIT 1",
                addslashes($token)
            );

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

    /**
     * Invalida o token de recuperacao (uso unico).
     */
    public function invalidarTokenRecuperacao($codUsuario, $debug = false) {
        try {
            $sql = sprintf(
                "UPDATE sis_usuarios SET token_recuperacao_usado = 1 WHERE cod_usuario = '%s'",
                addslashes($codUsuario)
            );
            $this->conex->debug = $debug;
            $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            return false;
        }
    }

    /**
     * Busca o session_token atual do usuario (para validacao de sessao).
     */
    public function getSessionToken($codUsuario, $debug = false) {
        try {
            $sql = sprintf(
                "SELECT session_token FROM sis_usuarios WHERE cod_usuario = '%s' AND status = 'ATIVADA'",
                addslashes($codUsuario)
            );
            $rs = $this->conex->Execute($sql);
            if ((!$rs) or ($rs->EOF)) {
                return null;
            }
            foreach ($rs as $rows) {}
            return $rows['session_token'];
        } catch (ADODB_Exception $e) {
            return null;
        }
    }

    /**
     * Registra log de tentativa de login.
     */
    public function registrarLogLogin($codUsuario, $login, $sucesso, $motivo = '', $debug = false) {
        try {
            $sql = sprintf(
                "INSERT INTO sis_log_login (cod_usuario, login, sucesso, motivo, ip, http_user_agent, data_acesso)
                VALUES ('%s', '%s', %d, '%s', '%s', '%s', NOW())",
                addslashes($codUsuario ? $codUsuario : ''),
                addslashes($login),
                $sucesso ? 1 : 0,
                addslashes($motivo),
                addslashes($_SERVER['REMOTE_ADDR']),
                addslashes(substr($_SERVER['HTTP_USER_AGENT'], 0, 255))
            );
            $this->conex->debug = $debug;
            $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            return false;
        }
    }

    /**
     * Busca email do usuario para recuperacao de senha.
     * Tenta: campo login (se for email), ou e_mail do tab_inscritos vinculado.
     */
    public function getEmailParaRecuperacao($login, $debug = false) {
        try {
            $sql = sprintf(
                "SELECT su.cod_usuario, su.login,
                    COALESCE(ti.e_mail, tc.e_mail, su.login) AS email_recuperacao
                FROM sis_usuarios su
                LEFT JOIN tab_inscritos ti ON su.login = ti.e_mail AND ti.status = 'ATIVADA'
                LEFT JOIN tab_convidados tc ON tc.cod_usuario = su.cod_usuario
                WHERE su.login = '%s' AND su.status = 'ATIVADA'
                ORDER BY su.cod_usuario DESC LIMIT 1",
                addslashes($login)
            );
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if ((!$rs) or ($rs->EOF)) {
                return null;
            }
            foreach ($rs as $rows) {}
            return $rows;
        } catch (ADODB_Exception $e) {
            return null;
        }
    }
}

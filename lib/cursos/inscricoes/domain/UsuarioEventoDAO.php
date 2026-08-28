<?php
class UsuarioEventoDAO extends DAO{

    public function __construct() {        
        parent::__construct();
    }  
    
    public function getById(UsuarioEvento $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT lue.*, c.cod_convidado, c.nome_convidado, u.login FROM sis_ligacao_usuarios_eventos lue '
            . ' INNER JOIN `sis_usuarios` u ON u.cod_usuario=lue.cod_usuario '
            . ' LEFT JOIN `tab_convidados` c ON u.cod_usuario=c.cod_usuario '
            . ' WHERE lue.cod_evento=\'%s\' '
            . ' and lue.cod_usuario= \'%s\''
            . '', $obj->getCodEvento(),$obj->getCodUsuario());
            
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
    
    public function getListaUsuariosByIdEvento(UsuarioEvento $obj, $debug=false) {
        try{
            $sql = sprintf('SELECT e.titulo, e.titulo as titulo_evento, lue.*, u.*, c.cod_convidado, c.nome_convidado '
                    . ' FROM sis_ligacao_usuarios_eventos lue '
                    . ' INNER JOIN `tab_eventos` e ON e.cod_evento=lue.cod_evento '
                    . ' INNER JOIN `sis_usuarios` u ON u.cod_usuario=lue.cod_usuario '
                    . ' left JOIN `tab_convidados` c ON u.cod_usuario=c.cod_usuario '
                    . ' WHERE lue.cod_evento=\'%s\'', $obj->getCodEvento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaUsuarios($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }       
    
    public function getListaEventosByIdLogin(UsuarioEvento $obj, $debug=false) {
        try{
            $sql = sprintf('SELECT e.titulo, e.titulo as titulo_evento, lue.* '
                    . ' FROM sis_ligacao_usuarios_eventos lue'
                    . ' INNER JOIN `tab_eventos` e ON e.cod_evento=lue.cod_evento'
                    . ' INNER JOIN `sis_usuarios` u ON u.cod_usuario=lue.cod_usuario'
                    . ' WHERE lue.cod_usuario=\'%s\'', $obj->getCodUsuario());

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
    
    public function insert(UsuarioEvento $obj, $debug=false) {
        try {           
            $obj->setDataInclusao(date('Y-m-d H:i:s'));
            $obj->setSessaoIdInclusao($_SESSION['_cod_usuario']);
            $obj->setIpClienteInclusao($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentInclusao($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao(1);
            $obj->setStatus('ATIVADA');
            
            $sql = "SELECT * FROM sis_ligacao_usuarios_eventos WHERE cod_usuario=0  and cod_evento=0"; 
            $rs = $this->conex->Execute($sql);     
            
            $arrayColunas = $this->conex->MetaColumnNames("sis_ligacao_usuarios_eventos");
            $fws_ = $obj->getAllForArray($arrayColunas,false);
            
            $insertSQL = $this->conex->GetInsertSQL($rs ,$fws_, true, true);
            $sql = sprintf($insertSQL);
            
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            //$obj->setCodUsuarioEvento($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function delete(UsuarioEvento $obj, $debug=false) {
        try {
            $sql = sprintf('DELETE FROM sis_ligacao_usuarios_eventos WHERE cod_evento=\'%s\''
            . ' and cod_usuario= \'%s\' ', $obj->getCodEvento(),$obj->getCodUsuario());
            
            $this->conex->debug = $debug;
            
            $rs = $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    } 

    /**
     * Traz para o projeto novo as pessoas ESCOLHIDAS na tela de duplicar.
     *
     * A lista vem de UsuarioEvento::setUsuariosSelecionados() -- codigos que o
     * usuario conferiu e manteve marcados na modal. Lista vazia nao copia
     * ninguem.
     *
     * So entra quem esta ATIVADA na origem: sis_ligacao_usuarios_grupos nao tem
     * status, entao o filtro sai de sis_ligacao_usuarios_eventos. Isso vale
     * mesmo que o codigo venha marcado -- acesso desativado nao volta a viver
     * por causa de um checkbox.
     *
     * O NOT IN preserva o comportamento original: quem ja tem vinculo no
     * projeto novo (quem duplicou e o coringa) nao e' tocado.
     */
    public function importarUsuariosEvento(UsuarioEvento $obj, $debug=false) {
        try{
            $codNovo   = (int) $obj->getCodEvento();
            $codOrigem = (int) $obj->getCodEventoAnt();

            $escolhidos = $obj->getUsuariosSelecionados();
            if (!is_array($escolhidos) || count($escolhidos) === 0) {
                return true; // ninguem marcado: nada a fazer
            }
            $escolhidos = array_filter(array_map('intval', $escolhidos));
            if (count($escolhidos) === 0) {
                return true;
            }
            $listaIn = implode(",", $escolhidos);

            $sqlUG = sprintf("INSERT INTO sis_ligacao_usuarios_grupos (cod_usuario, cod_grupo, cod_evento)
                SELECT g.cod_usuario, g.cod_grupo, %d as cod_evento
                  FROM sis_ligacao_usuarios_grupos g
                 WHERE g.cod_evento = %d
                   AND g.cod_usuario IN (%s)
                   AND g.cod_usuario IN (SELECT e.cod_usuario FROM sis_ligacao_usuarios_eventos e
                                          WHERE e.cod_evento = %d AND e.status = 'ATIVADA')
                   AND g.cod_usuario NOT IN (SELECT cod_usuario FROM sis_ligacao_usuarios_grupos WHERE cod_evento = %d)",
                $codNovo, $codOrigem, $listaIn, $codOrigem, $codNovo);
            $this->conex->debug = $debug;
            $rsUG = $this->conex->Execute($sqlUG);

            /* So liga ao evento quem de fato recebeu grupo agora. */
            $sql = sprintf("INSERT INTO sis_ligacao_usuarios_eventos (cod_evento, cod_usuario, status)
                SELECT %d as cod_evento, e.cod_usuario, e.status
                  FROM sis_ligacao_usuarios_eventos e
                 WHERE e.status = 'ATIVADA' AND e.cod_evento = %d
                   AND e.cod_usuario IN (SELECT g.cod_usuario FROM sis_ligacao_usuarios_grupos g WHERE g.cod_evento = %d)
                   AND e.cod_usuario NOT IN (SELECT cod_usuario FROM sis_ligacao_usuarios_eventos WHERE cod_evento = %d)",
                $codNovo, $codOrigem, $codNovo, $codNovo);
            $rs = $this->conex->Execute($sql);
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    /**
     * Quem PODE ser trazido do projeto de origem, para a tela conferir antes.
     *
     * Identifica pelo login (e-mail): nome_usuario e' NULL em boa parte da base,
     * e o mesmo e-mail aparece em codigos diferentes -- por isso o codigo vai
     * junto.
     */
    public function listarCandidatosImportacao($codOrigem, $debug=false) {
        $codOrigem = (int) $codOrigem;
        $grupos = implode(",", array_map('intval', enumGrupo::gruposAdministracao()));
        $sql = sprintf("SELECT u.cod_usuario, u.nome_usuario, u.login,
                   MAX(CASE WHEN g.cod_grupo IN (%s) THEN 1 ELSE 0 END) AS eh_admin,
                   COUNT(*) AS qtd_grupos
              FROM sis_ligacao_usuarios_grupos g
              JOIN sis_usuarios u ON u.cod_usuario = g.cod_usuario
             WHERE g.cod_evento = %d
               AND g.cod_usuario IN (SELECT e.cod_usuario FROM sis_ligacao_usuarios_eventos e
                                      WHERE e.cod_evento = %d AND e.status = 'ATIVADA')
             GROUP BY u.cod_usuario, u.nome_usuario, u.login
             ORDER BY eh_admin DESC, u.login", $grupos, $codOrigem, $codOrigem);
        $this->conex->debug = $debug;
        $rs = $this->conex->Execute($sql);
        $lista = array();
        while ($rs && !$rs->EOF) {
            $lista[] = array(
                "cod_usuario" => $rs->fields["cod_usuario"],
                "nome"        => $rs->fields["nome_usuario"],
                "login"       => $rs->fields["login"],
                "eh_admin"    => (int) $rs->fields["eh_admin"],
                "qtd_grupos"  => (int) $rs->fields["qtd_grupos"],
            );
            $rs->MoveNext();
        }
        return $lista;
    }
}

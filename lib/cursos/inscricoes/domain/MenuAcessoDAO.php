<?php

class MenuAcessoDAO extends DAO {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Registra um clique. Nao cria linha nova a cada clique:
     * a chave unica (usuario+evento+view) faz o contador incrementar.
     */
    public function registrar(MenuAcesso $obj, $debug = false) {
        try {
            $sql = "INSERT INTO tab_menu_acesso (cod_usuario, cod_evento, des_view, qtd_acesso, dat_ultimo, dat_incl, ip)
                    VALUES (?, ?, ?, 1, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE qtd_acesso = qtd_acesso + 1, dat_ultimo = VALUES(dat_ultimo)";
            $agora  = date('Y-m-d H:i:s');
            $params = [
                (int) $obj->getCodUsuario(),
                (int) $obj->getCodEvento(),
                $obj->getDesView(),
                $agora,
                $agora,
                $_SERVER['REMOTE_ADDR']
            ];
            $this->conex->debug = $debug;
            return $this->conex->Execute($sql, $params);
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    /**
     * Telas mais acessadas por este usuario neste evento.
     * Retorna array simples de des_view, do mais usado para o menos usado.
     * cod_* vao por intval — addslashes nao protege campo numerico.
     */
    public function getMaisUsados(MenuAcesso $obj, $limite = 4, $debug = false) {
        try {
            $sql = sprintf(
                "SELECT des_view FROM tab_menu_acesso
                  WHERE cod_usuario = %d AND cod_evento = %d
                  ORDER BY qtd_acesso DESC, dat_ultimo DESC
                  LIMIT %d",
                (int) $obj->getCodUsuario(),
                (int) $obj->getCodEvento(),
                (int) $limite
            );
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if ((!$rs) or ($rs->EOF)) {
                return array();
            }
            $return = array();
            foreach ($rs as $rows) {
                $return[] = $rows['des_view'];
            }
            return $return;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return array();
        }
    }
}

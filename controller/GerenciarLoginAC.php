<?php
/**
 * Login simplificado para o módulo financeiro standalone.
 */
class GerenciarLoginAC {

    public function loginAdministrativo($fws_, $debug = false) {
        try {
            $login = $fws_['login'];
            $senha = $fws_['senha'];

            if (empty($login) || empty($senha)) {
                throw new Exception("Login e senha são obrigatórios.");
            }

            ConexaoDB::getInstance();
            $conex = ConexaoDB::getConexaoDB();

            $sql = sprintf("SELECT u.cod_usuario, u.nome_usuario, u.login, u.senha,
                    COALESCE(tc.nome_convidado, u.nome_usuario) as nome_exibicao
                FROM sis_usuarios u
                LEFT JOIN tab_convidados tc ON tc.cod_usuario = u.cod_usuario
                WHERE (u.login = '%s' OR u.e_mail = '%s')
                  AND u.status = 'ATIVADA'
                LIMIT 1",
                addslashes($login), addslashes($login));

            $rs = $conex->Execute($sql);

            if (!$rs || $rs->EOF) {
                throw new Exception("Usuário não encontrado.");
            }

            $row = $rs->fields;

            if (!password_verify($senha, $row['senha'])) {
                throw new Exception("Senha incorreta.");
            }

            // Verificar se tem permissão financeira
            $codUsuario = $row['cod_usuario'];
            if (!FWA_Autenticacao($codUsuario, enumSistema::SIS_CONTROLE_FINANCEIRO, 5, false, false)) {
                throw new Exception("Usuário não possui permissão para o módulo financeiro.");
            }

            // Criar sessão
            $_SESSION['_cod_usuario']    = $codUsuario;
            $_SESSION['_login']          = $row['login'];
            $_SESSION['_nome_usuario']   = $row['nome_exibicao'];
            $_SESSION['_logado']         = 1;
            $_SESSION['_administrativo'] = 1;
            $_SESSION['userLogado']['_cod_inscrito'] = $codUsuario;

            $URL = WB_URL_VIEW . "?content=view/admin/GerenciarLancamentosContabeis.php";
            echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $URL . "\"></HTML>";
            exit;

        } catch (Exception $e) {
            $_SESSION['MSG'] = $e->getMessage();
            $URL = WB_URL_VIEW . "?content=view/admin/login.php";
            echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $URL . "\"></HTML>";
            exit;
        }
    }

    public function efetuarLogout($fws_ = null, $debug = false) {
        session_destroy();
        $URL = WB_URL_VIEW . "?content=view/admin/login.php";
        echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $URL . "\"></HTML>";
        exit;
    }
}

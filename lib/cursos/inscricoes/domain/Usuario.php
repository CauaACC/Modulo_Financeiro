<?php

class Usuario extends UsuarioVO {

    private $objDAO;

    function __construct() {
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new UsuarioDAO();
    }

    public function UsuarioExiste() {
        if (!$this->getCodUsuario()) {
            throw new Exception('Cód. Usuario inválido ou não informado.');
        }
        if (!$this->objDAO->getByIdExiste($this)) {
            throw new Exception("Nenhum Usuario foi encontrado para o código informado: " . $this->getCodUsuario() . ".");
            //throw new Exception("Id informado para deleção não existe");
        }
    }

    public function novo($debug = false) {
        
    }

    public function salvar($debug = false) {
        
        $this->validatorObj();
        if (is_null($this->getCodUsuario()) or $this->getCodUsuario() == "") {
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Usuario salvo com sucesso.");
            } else {
                throw new Exception("Novo Usuario não foi salvo.");
            }
        } else {
            $this->UsuarioExiste($debug);

            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Usuario foi atualizado com sucesso.");
            } else {
                throw new Exception("Usuario atual não foi atualizado.");
            }
        }
    }
    
    public function excluir($debug = false) {
        $this->validatorEcluir();
        if ($this->objDAO->delete($this, $debug)) {
            throw new OKException("Usuario cód:" . $this->getCodUsuario() . " foi excluído com sucesso.");
        } else {
            throw new Exception("Usuario cód:" . $this->getCodUsuario() . " não foi excluído.");
        }
    }

    public function getUsuarioByEmail($debug = false) {
        return $this->objDAO->getByEmail($this, $debug);
    }
    
    public function getUsuarioByLogin($debug = false) {
        return $this->objDAO->getByLogin($this, $debug);
    }
    
    public function getUsuarioById($debug = false) {
        return $this->objDAO->getById($this, $debug);
    }
    
    public function getLoginByCodUsuario($debug = false) {
        return $this->objDAO->getById($this, $debug);
    }
    
    public function trocarSenha($debug = false) {
        $this->validarSenha();
        return $this->objDAO->trocarSenha($this, $debug);
    }
    
    public function getUsuarioByCodConvidado($debug = false) {
        return $this->objDAO->getByCodConvidado($this, $debug);
    }
    
    public function getLoginByCodConvidado($debug = false) {
        return $this->objDAO->getByCodConvidado($this, $debug);
    }

    public function getListaUsuariosByIdEvento($debug = false) {
        if (!$this->getCodEvento()) {
            throw new Exception("Id do evento não informado.");
        }
        $this->objDAO->carregarListaUsuariosByIdEvento($this, $debug);
        if ($this->getListaUsuarios()) {
            return $this->getListaUsuarios();
        } else {
            throw new Exception("Evento não possui Usuario cadastrada.");
        }
    }
    
    public function carregarListaUsuarios($condicoes = false , $debug=false){
        $this->objDAO->carregarListaUsuarios($this, $condicoes, $debug);
    }
    
    public function carregarListaGruposByIdUsuario($debug=false){
        $obj = new GrupoSistema();
        $obj->setCodUsuario($this->getCodUsuario());
        $obj->carregarListaGruposByIdUsuario($debug);
        $this->setListaGrupos($obj->getListaGrupos());
    }

    public function carregarListaAreasTematicasByIdUsuario($debug=false){
        $obj = new AreaTematica();
        $obj->setCodUsuario($this->getCodUsuario());
        $obj->carregarListaAreasTematicasByIdUsuario($debug);
        $this->setListaAreasTematicas($obj->getListaAreasTematicas());
    }

    public function getEvento() {
        $obj = new InscEvento();
        $obj->setCodEvento($this->getCodEvento());
        $obj->getEventoById();
        return $obj;
    }

    public function verificarUsuarioAssociarEvento($debug=false){


    }

    // ==================== METODOS DE SEGURANCA ====================

    /**
     * Autentica o usuario administrativo.
     * Suporta migracao gradual: se senha_hash vazia, usa texto puro e migra para bcrypt.
     * Controla throttling (bloqueio apos 5 tentativas).
     */
    public function autenticar($senhaPlainText, $debug = false) {
        if (!$this->getLogin() || !$senhaPlainText) {
            throw new Exception("Login e senha sao obrigatorios.");
        }

        // Buscar usuario por login
        if (!$this->objDAO->getByLoginParaAutenticacao($this, $debug)) {
            // Delay para evitar enumeracao de usuarios via timing
            usleep(random_int(200000, 400000));
            $this->objDAO->registrarLogLogin('', $this->getLogin(), false, 'usuario_nao_encontrado');
            throw new Exception("Login ou senha invalidos.");
        }

        // Verificar bloqueio
        if ($this->getBloqueadoAte() && strtotime($this->getBloqueadoAte()) > time()) {
            $minutos = ceil((strtotime($this->getBloqueadoAte()) - time()) / 60);
            $this->objDAO->registrarLogLogin($this->getCodUsuario(), $this->getLogin(), false, 'conta_bloqueada');
            throw new Exception("Conta temporariamente bloqueada. Tente novamente em " . $minutos . " minuto(s).");
        }

        // Verificar senha
        $senhaValida = false;
        $precisaMigrar = false;

        if ($this->getSenhaHash() && SenhaHelper::isBcrypt($this->getSenhaHash())) {
            // Ja tem hash bcrypt — validar com password_verify
            $senhaValida = SenhaHelper::verify($senhaPlainText, $this->getSenhaHash());
        } elseif ($this->getSenha() && $this->getSenha() !== '') {
            // Senha legada em texto puro — comparar diretamente
            $senhaValida = ($senhaPlainText === $this->getSenha());
            if ($senhaValida) {
                $precisaMigrar = true;
            }
        }

        if (!$senhaValida) {
            $this->objDAO->registrarTentativaFalha($this->getCodUsuario(), $debug);
            $this->objDAO->registrarLogLogin($this->getCodUsuario(), $this->getLogin(), false, 'senha_invalida');
            usleep(random_int(200000, 400000));
            throw new Exception("Login ou senha invalidos.");
        }

        // Senha valida — migrar para bcrypt se necessario
        if ($precisaMigrar) {
            $this->setSenhaHash(SenhaHelper::hash($senhaPlainText));
            $this->objDAO->atualizarSenhaHash($this, $debug);
        }

        // Registrar sucesso
        $this->objDAO->registrarLoginSucesso($this->getCodUsuario(), $debug);
        $this->objDAO->registrarLogLogin($this->getCodUsuario(), $this->getLogin(), true, 'login_ok');

        return true;
    }

    /**
     * Troca a senha com verificacao segura (bcrypt).
     * Usado tanto na troca voluntaria quanto na troca obrigatoria.
     */
    public function trocarSenhaSegura($senhaAtual, $senhaNova, $debug = false) {
        if (!$this->getCodUsuario()) {
            throw new Exception("Usuario nao identificado.");
        }

        // Carregar dados atuais
        $this->objDAO->getByLogin($this, $debug);

        // Verificar senha atual
        $senhaAtualValida = false;
        if ($this->getSenhaHash() && SenhaHelper::isBcrypt($this->getSenhaHash())) {
            $senhaAtualValida = SenhaHelper::verify($senhaAtual, $this->getSenhaHash());
        } elseif ($this->getSenha()) {
            $senhaAtualValida = ($senhaAtual === $this->getSenha());
        }

        if (!$senhaAtualValida) {
            throw new Exception("Senha atual incorreta.");
        }

        // Hash da nova senha
        $this->setSenhaHash(SenhaHelper::hash($senhaNova));
        $this->setForcarTrocaSenha(0);
        if (!$this->objDAO->atualizarSenhaHash($this, $debug)) {
            throw new Exception("Erro ao atualizar a senha.");
        }

        $this->objDAO->registrarLogLogin($this->getCodUsuario(), $this->getLogin(), true, 'troca_senha');
        return $this->getSessionToken(); // retorna novo session_token
    }

    /**
     * Troca de senha disparada por administrador (admin geral via Gestao de Acessos).
     * NAO valida senha atual — o admin nao a possui. Forca troca no proximo login.
     * Reutiliza atualizarSenhaHash que ja limpa tentativas/bloqueio e gira o session_token
     * (invalidando sessoes ativas do usuario alvo).
     *
     * Quem chama eh responsavel por:
     *   - garantir auth do admin com SIS_GERENCIAR_ADM_GERAL
     *   - impedir troca da propria senha por esta via
     *   - registrar log de auditoria com identidade do admin
     */
    public function trocarSenhaAdmin($senhaNova, $debug = false) {
        if (!$this->getCodUsuario()) {
            throw new Exception("Usuario nao identificado.");
        }
        if ($senhaNova === '' || strlen($senhaNova) < 8) {
            throw new Exception("Senha nova invalida (minimo 8 caracteres).");
        }
        $this->setSenhaHash(SenhaHelper::hash($senhaNova));
        $this->setForcarTrocaSenha(1);
        if (!$this->objDAO->atualizarSenhaHash($this, $debug)) {
            throw new Exception("Erro ao atualizar a senha.");
        }
        $this->objDAO->registrarLogLogin($this->getCodUsuario(), $this->getLogin(), true, 'senha_trocada_por_admin');
        return true;
    }

    /**
     * Solicita recuperacao de senha — gera token e retorna dados para envio de email.
     * Retorna array com cod_usuario e email, ou null se nao encontrou.
     */
    public function solicitarRecuperacaoSenha($login, $debug = false) {
        $dados = $this->objDAO->getEmailParaRecuperacao($login, $debug);
        if (!$dados || !$dados['email_recuperacao']) {
            return null;
        }

        $token = SenhaHelper::gerarToken();
        $expira = date('Y-m-d H:i:s', time() + 3600); // 1 hora

        $this->objDAO->salvarTokenRecuperacao($dados['cod_usuario'], $token, $expira, $debug);
        $this->objDAO->registrarLogLogin($dados['cod_usuario'], $login, true, 'token_recuperacao_gerado');

        return array(
            'codUsuario' => $dados['cod_usuario'],
            'email'      => $dados['email_recuperacao'],
            'token'      => $token
        );
    }

    /**
     * Troca senha usando token de recuperacao.
     */
    public function validarTokenRecuperacao($token, $debug = false) {
        $obj = new Usuario();
        return $this->objDAO->getByTokenRecuperacao($obj, $token, $debug);
    }

    public function trocarSenhaPorToken($token, $senhaNova, $debug = false) {
        $obj = new Usuario();
        if (!$this->objDAO->getByTokenRecuperacao($obj, $token, $debug)) {
            throw new Exception("Token invalido ou expirado.");
        }

        // Definir nova senha
        $obj->setSenhaHash(SenhaHelper::hash($senhaNova));
        $obj->setForcarTrocaSenha(0);
        if (!$this->objDAO->atualizarSenhaHash($obj, $debug)) {
            throw new Exception("Erro ao redefinir a senha.");
        }

        // Invalidar token
        $this->objDAO->invalidarTokenRecuperacao($obj->getCodUsuario(), $debug);
        $this->objDAO->registrarLogLogin($obj->getCodUsuario(), $obj->getLogin(), true, 'senha_redefinida_por_token');

        return true;
    }
}

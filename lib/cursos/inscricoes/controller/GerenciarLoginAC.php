<?php
//if($_SERVER['REMOTE_ADDR']=='189.90.65.203'){
//    error_reporting(E_ALL ^ E_NOTICE);
//    ini_set("display_errors", "On");
//}
class GerenciarLoginAC {

    function __construct() {

    }

    // ========================================================================
    // CSRF simples para os formularios de login/senha do inscrito.
    // Token gerado no render da tela, guardado na sessao (lista, para varias
    // abas) e consumido no POST -- mesmo padrao do token de acao em lote.
    // ========================================================================
    const CSRF_SESSAO = '_csrf_login_inscrito';
    const CSRF_MAX = 20;

    /** Gera um token CSRF novo, guarda na sessao e devolve (chamar no render). */
    public static function novoTokenCsrf() {
        try { $t = SenhaHelper::gerarToken(); }
        catch (Exception $e) { $t = md5(uniqid('csrf', true) . mt_rand()); }
        if (!isset($_SESSION[self::CSRF_SESSAO]) || !is_array($_SESSION[self::CSRF_SESSAO])) {
            $_SESSION[self::CSRF_SESSAO] = array();
        }
        $_SESSION[self::CSRF_SESSAO][] = $t;
        while (count($_SESSION[self::CSRF_SESSAO]) > self::CSRF_MAX) {
            array_shift($_SESSION[self::CSRF_SESSAO]);
        }
        return $t;
    }

    /** Valida e CONSOME o token CSRF do POST. false = ausente/forjado/ja usado. */
    public static function validarTokenCsrf($token) {
        $token = trim((string) $token);
        if ($token === '' || empty($_SESSION[self::CSRF_SESSAO]) || !is_array($_SESSION[self::CSRF_SESSAO])) {
            return false;
        }
        $i = array_search($token, $_SESSION[self::CSRF_SESSAO], true);
        if ($i === false) return false;
        unset($_SESSION[self::CSRF_SESSAO][$i]);
        $_SESSION[self::CSRF_SESSAO] = array_values($_SESSION[self::CSRF_SESSAO]);
        return true;
    }

    public function trocarSenha($fws_, $debug = 1){
        $ok = false;
        try {
            if(!($fws_['senha_nova']===$fws_['senha_nova_repetir'])){
                throw new Exception("Senhas novas não conferem");
            }
            $objLogin = new Usuario();
            $objLogin->setLogin($_SESSION['_login']);
            $objLogin->getUsuarioByLogin($debug);
            if ($objLogin->getCodUsuario()){
                $objLogin->setSenhaAtual($fws_['senha_atual']);
                $objLogin->setSenhaNova($fws_['senha_nova']);
                echo $debug? 'Login existe-' : '';
                if($objLogin->trocarSenha($debug)){
                    throw new OKException('Senha alterada com sucesso!');
                }else{
                    throw new Exception('Não foi possível alterar.');
                }
            }else{
                echo $debug? 'Login não existe-('.$objLogin->getCodUsuario().')' : '';
                throw new Exception('Login não cadastrado.');
            }
        }catch(OKException $exc){
            $msg = $exc->getMessage();
            $ok = true;
        }catch(Exception $exc){
            $msg = $exc->getMessage();
            $ok = false;
        }
        echo '{"message":"' . tratarJson($msg) . '","statusMessage":"' . $ok . '"}';
        exit();

    }

    /**
     * Troca de senha segura (com bcrypt) — usada na troca obrigatoria e voluntaria.
     */
    public function trocarSenhaSegura($fws_, $debug = false) {
        $ok = false;
        $msg = '';
        try {
            if (empty($fws_['senhaAtual'])) {
                throw new Exception("Informe a senha atual.");
            }
            if (empty($fws_['senhaNova']) || empty($fws_['senhaNovaRepetir'])) {
                throw new Exception("Informe a nova senha e a confirmacao.");
            }
            if ($fws_['senhaNova'] !== $fws_['senhaNovaRepetir']) {
                throw new Exception("As senhas novas nao conferem.");
            }
            if (strlen($fws_['senhaNova']) < 8) {
                throw new Exception("A nova senha deve ter pelo menos 8 caracteres.");
            }
            if (!preg_match('/[a-zA-Z]/', $fws_['senhaNova']) || !preg_match('/[0-9]/', $fws_['senhaNova']) || !preg_match('/[^a-zA-Z0-9]/', $fws_['senhaNova'])) {
                throw new Exception("A nova senha deve conter letras, numeros e caracteres especiais.");
            }

            $objUsuario = new Usuario();
            $objUsuario->setLogin($_SESSION['_login']);
            $objUsuario->setCodUsuario($_SESSION['_cod_usuario']);

            $novoSessionToken = $objUsuario->trocarSenhaSegura($fws_['senhaAtual'], $fws_['senhaNova'], $debug);

            // Atualizar session_token na sessao atual
            $_SESSION['_session_token'] = $novoSessionToken;

            $ok = true;
            $msg = 'Senha alterada com sucesso!';
        } catch (Exception $e) {
            $ok = false;
            $msg = $e->getMessage();
        }

        echo json_encode(array(
            'statusMessage' => $ok,
            'message'       => $msg
        ));
        exit();
    }

    /**
     * Solicitar recuperacao de senha admin — sempre retorna mensagem neutra.
     */
    public function solicitarRecuperacaoSenha($fws_, $debug = false) {
        $msg = 'Se existe uma conta com este login, um e-mail com instrucoes foi enviado.';
        try {
            $login = isset($fws_['login']) ? trim($fws_['login']) : '';
            if (empty($login)) {
                throw new Exception("Informe seu login ou e-mail.");
            }

            $objUsuario = new Usuario();
            $dados = $objUsuario->solicitarRecuperacaoSenha($login, $debug);

            if ($dados && $dados['email'] && filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
                // Montar link de redefinicao
                $linkReset = WB_URL_VIEW . '?content=view/admin/login-redefinir-senha.php&token=' . $dados['token'];

                $assunto = '[' . ABREV_EVENTO_EMAIL . '] Redefinicao de senha';
                $corpo = '<p>Voce solicitou a redefinicao de sua senha de acesso ao sistema administrativo.</p>'
                    . '<p>Clique no link abaixo para definir uma nova senha:</p>'
                    . '<p><a href="' . $linkReset . '">' . $linkReset . '</a></p>'
                    . '<p>Este link e valido por 1 hora e pode ser usado apenas uma vez.</p>'
                    . '<p>Se voce nao solicitou esta alteracao, ignore este e-mail.</p>'
                    . '<p><small>IP da solicitacao: ' . $_SERVER['REMOTE_ADDR'] . '</small></p>';

                GerenciarEmailAC::enviarEmail($dados['email'], $assunto, $corpo, false, true);
            }
        } catch (Exception $e) {
            // Nao revelar erro — manter mensagem neutra
        }

        echo json_encode(array(
            'statusMessage' => true,
            'message'       => $msg
        ));
        exit();
    }

    /**
     * Valida se o token de recuperacao ainda e valido (nao expirado, nao usado).
     */
    public function validarTokenRecuperacao($fws_, $debug = false) {
        $token = isset($fws_['token']) ? trim($fws_['token']) : '';
        $valido = false;
        if (!empty($token)) {
            $objUsuario = new Usuario();
            $valido = $objUsuario->validarTokenRecuperacao($token, $debug);
        }
        echo json_encode(array(
            'statusMessage' => $valido
        ));
        exit();
    }

    /**
     * Redefinir senha usando token de recuperacao.
     */
    public function redefinirSenhaPorToken($fws_, $debug = false) {
        $ok = false;
        $msg = '';
        try {
            $token = isset($fws_['token']) ? trim($fws_['token']) : '';
            if (empty($token)) {
                throw new Exception("Token nao informado.");
            }
            if (empty($fws_['senhaNova']) || empty($fws_['senhaNovaRepetir'])) {
                throw new Exception("Informe a nova senha e a confirmacao.");
            }
            if ($fws_['senhaNova'] !== $fws_['senhaNovaRepetir']) {
                throw new Exception("As senhas nao conferem.");
            }
            if (strlen($fws_['senhaNova']) < 8) {
                throw new Exception("A nova senha deve ter pelo menos 8 caracteres.");
            }
            if (!preg_match('/[a-zA-Z]/', $fws_['senhaNova']) || !preg_match('/[0-9]/', $fws_['senhaNova']) || !preg_match('/[^a-zA-Z0-9]/', $fws_['senhaNova'])) {
                throw new Exception("A nova senha deve conter letras, numeros e caracteres especiais.");
            }

            $objUsuario = new Usuario();
            $objUsuario->trocarSenhaPorToken($token, $fws_['senhaNova'], $debug);

            $ok = true;
            $msg = 'Senha redefinida com sucesso! Faca login com sua nova senha.';
        } catch (Exception $e) {
            $ok = false;
            $msg = $e->getMessage();
        }

        echo json_encode(array(
            'statusMessage' => $ok,
            'message'       => $msg
        ));
        exit();
    }

    // ========================================================================
    // Recuperacao/definicao de senha do INSCRITO (Parte 1) -- so por e-mail.
    // Manda um LINK (nao a senha nem o numero). Resposta sempre neutra.
    // ========================================================================

    /** Solicita link de definicao de senha do inscrito -- resposta SEMPRE neutra. */
    public function solicitarLinkSenhaInscrito($fws_, $debug = false) {
        $msg = 'Se existe uma inscricao com este e-mail, enviamos um link para criar uma nova senha.';
        if (!self::validarTokenCsrf(isset($fws_['csrf']) ? $fws_['csrf'] : '')) {
            echo json_encode(array('statusMessage' => false, 'message' => 'Sessao do formulario expirada. Recarregue a pagina e tente novamente.'));
            exit();
        }
        try {
            $email = isset($fws_['e_mail']) ? trim($fws_['e_mail']) : '';
            if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $obj = new Inscrito();
                $dados = $obj->solicitarDefinicaoSenha($email, $debug);
                if ($dados && filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
                    $link = WB_URL_VIEW . '?content=view/inscricao/inscricao_definir_senha.php&token=' . $dados['token'];
                    $assunto = '[' . ABREV_EVENTO_EMAIL . '] Criar/redefinir senha de acesso';
                    $corpo = '<p>Voce pediu para criar/redefinir a senha de acesso a sua area do inscrito.</p>'
                        . '<p>Clique no link abaixo para definir uma nova senha:</p>'
                        . '<p><a href="' . $link . '">' . $link . '</a></p>'
                        . '<p>Este link e valido por 1 hora e pode ser usado apenas uma vez.</p>'
                        . '<p>Se voce nao pediu isso, ignore este e-mail.</p>'
                        . '<p><small>IP da solicitacao: ' . @$_SERVER['REMOTE_ADDR'] . '</small></p>';
                    GerenciarEmailAC::enviarEmail($dados['email'], $assunto, $corpo, false, true);
                }
            }
        } catch (Exception $e) {
            // nunca revelar -- mensagem neutra
        }
        echo json_encode(array('statusMessage' => true, 'message' => $msg));
        exit();
    }

    /**
     * Esconde o miolo do e-mail: joao.silva@escola.pr.gov.br -> jo*******a@escola.pr.gov.br
     * Mantem o dominio inteiro e as pontas da parte local, que e o que a pessoa
     * precisa para reconhecer a conta sem que o endereco fique exposto na tela.
     */
    private function mascararEmail($email) {
        $email = trim((string) $email);
        $at = strrpos($email, '@');
        if ($at === false || $at === 0) return '';
        $local   = substr($email, 0, $at);
        $dominio = substr($email, $at);
        $tam = strlen($local);
        if ($tam <= 2) {
            return substr($local, 0, 1) . str_repeat('*', 3) . $dominio;
        }
        $visivel = $tam <= 4 ? 1 : 2;   // local curto mostra menos
        return substr($local, 0, $visivel)
             . str_repeat('*', max(3, $tam - $visivel - 1))
             . substr($local, -1) . $dominio;
    }

    /** Token de definicao de senha do inscrito ainda valido? (JSON) */
    public function validarTokenSenhaInscrito($fws_, $debug = false) {
        $token = isset($fws_['token']) ? trim($fws_['token']) : '';
        $valido = false;
        $email = '';
        $minutos = 0;
        if ($token !== '') {
            $obj = new Inscrito();
            $dados = $obj->dadosTokenDefinicaoSenha($token, $debug);
            if ($dados) {
                $valido  = true;
                // Sai daqui JA mascarado: o e-mail inteiro nunca chega ao navegador.
                $email   = $this->mascararEmail($dados['e_mail']);
                $minutos = isset($dados['minutos_restantes']) ? (int) $dados['minutos_restantes'] : 0;
            }
        }
        echo json_encode(array('statusMessage' => $valido,
                               'email' => $email,
                               'minutosRestantes' => $minutos));
        exit();
    }

    /** Define a senha do inscrito pelo token. Senha minima 6 caracteres. */
    public function definirSenhaInscritoPorToken($fws_, $debug = false) {
        $ok = false; $msg = '';
        try {
            $token = isset($fws_['token']) ? trim($fws_['token']) : '';
            if ($token === '') {
                throw new Exception('Token nao informado.');
            }
            if (empty($fws_['senhaNova']) || empty($fws_['senhaNovaRepetir'])) {
                throw new Exception('Informe a nova senha e a confirmacao.');
            }
            if ($fws_['senhaNova'] !== $fws_['senhaNovaRepetir']) {
                throw new Exception('As senhas nao conferem.');
            }
            if (strlen($fws_['senhaNova']) < 6) {
                throw new Exception('A senha deve ter pelo menos 6 caracteres.');
            }
            $obj = new Inscrito();
            $obj->definirSenhaPorToken($token, $fws_['senhaNova'], $debug);
            $ok = true;
            $msg = 'Senha criada com sucesso! Faca login com sua nova senha.';
        } catch (Exception $e) {
            $ok = false; $msg = $e->getMessage();
        }
        echo json_encode(array('statusMessage' => $ok, 'message' => $msg));
        exit();
    }

    public function recuperarSenha($fws_, $debug = false) {
        try {
            if ($fws_['e_mail'] == '') {
                $codRetorno = "verificarEmailInscrito";
                throw new Exception('E-mail não informado.');
            }
            $objInscrito = new Inscrito();
            $objInscrito->setEMail($fws_['e_mail']);
            $objInscrito->setCodInscrito($fws_['cod_inscrito']);
            $objInscrito->getInscritoByEmailToCredencial($debug);
            // dd($objInscrito);
            // exit();
            if (!$objInscrito->getCodInscrito()) {
                $codRetorno = "verificarEmailInscrito";
                throw new Exception('E-mail: (' . $fws_['e_mail'] . '), não encontrando.');
            }

            $msgTo = $objInscrito->getEMail();
            $msgSubject = ABREV_EVENTO." - Recuperação Credenciais - " . $objInscrito->getNomeApelido();
            $msgBody = ""
                    . "\r\n\r\nOla " . $objInscrito->getNomeApelido()
                    . "\r\n\r\nInformações de sua inscrição no ".ABREV_EVENTO."."
                    . "\r\n"
                    . "\r\nLogin: " . $objInscrito->getEMail()
                    . "\r\nSenha: " . $objInscrito->getCodInscrito()
                    ." \r\nLink de Acesso: ".EMAIL_CONTATO_LINK_LOGIN 
                    . "\r\n\r\n ".ABREV_EVENTO." | ".NOME_EVENTO
                    . "\r\n\r\nData: " . Date('d/m/Y H:i')
                    . "";
//            mail($msgTo, $msgSubject, $msgBody, HEADER_EMAIL);
            GerenciarEmailAC::enviarEmail($msgTo, $msgSubject, $msgBody);
            if($objInscrito->getEMail2()!='' and $objInscrito->getEMail2()!=$objInscrito->getEMail()){
                GerenciarEmailAC::enviarEmail($objInscrito->getEMail2(), $msgSubject, $msgBody);
            }

            /**Email de monitoramento */
            // GerenciarEmailAC::enviarEmail('eventos@opctec.com.br', 'Monitorado: '.$msgSubject, $msgBody);            

            /**enviando via whats */
            $txtComplemento = "";
            $email = $objInscrito->getEMail();
            $subjectExtra = '';
            
            if (strpos($email, '@hotmail.com') !== false || strpos($email, '@hotmail.com.br') !== false) {
                // if (!$fws_['receber_via_whats']) {
                //     $fws_['receber_via_whats'] = true;
                //     $subjectExtra = ' (Forçado via WhatsApp)';
                // }
            } else {
                if ($fws_['receber_via_whats']) {
                    $subjectExtra = ' (Solicitado via WhatsApp)';
                }
            }
            if($fws_['receber_via_whats']){
                $foneTo = limpaFoneWhats($objInscrito->getFoneContatoDdd().$objInscrito->getFoneContato());
                $msgWhats = ' e para o número de WhatsApp cadastrado';
                if (!isValidFoneNumber($foneTo)) {
                    $msgWhats = ', número de WhatsApp inválido ou não encontrado, entre em contato com a organização.';
                }
                ob_start();
                echo $objInscrito->getCodInscrito(). " - ".$objInscrito->getNomeApelido(). "<br>";
                echo $objInscrito->getEMail(). " :";
                echo "<a target='_blank' href='";
                echo "https://".$_SERVER["SERVER_NAME"]."/".$objInscrito->getUrlPrefixo()."/inscricoes";
                echo "/index.php?content=view/admin/inscritos-cadastro-ok.php&e_mail=";
                echo $objInscrito->getEMail(). "'>";
                echo "Localizar inscrição via e-mail.</a>";
                echo $foneTo;
                /**
                echo "<a target='_blank' href='https://api.whatsapp.com/send?phone=".$foneTo
                    . "&text=Olá ".$objInscrito->getPreNome()
                    . ". Segue seus dados para acesso: "
                    . " https://".$_SERVER["SERVER_NAME"]."/".$objInscrito->getUrlPrefixo()."/inscricoes"
                    . " login: ". $objInscrito->getEMail()
                    . " senha: ". $objInscrito->getCodInscrito()
                    . "'>Enviar login via whats</a>";
                */
                $msgBody = ob_get_clean();
                /**aqui envia o pedido para ser processado manualmente */
                GerenciarEmailAC::enviarEmail('eventos@opctec.com.br', $subjectExtra.' '.$msgSubject, $msgBody);

                $txtComplemento = $msgWhats;
                executarLog($subjectExtra.' '.$msgSubject. ' '.$msgWhats,'recuperacao-senha', true);

                // if ($_SERVER['REMOTE_ADDR'] == '177.82.0.28'){ 
                /**bloqueado até liberar whats */
                    // $objWhats = new GerenciarWhatsAC();
                    // $objWhats->enviarCredenciaisByWhats($fws_, $objInscrito, $debug);
                // }                
            }
            $msg = "Seu login e senha foram enviados para: " . $objInscrito->getEMail().$txtComplemento;
            $_SESSION['MSG'] = $msg;
            $url = WB_URL_VIEW . "?content=view/inscricao/inscricao_01.php&codRetorno=efetuarLogin&e_mail=" . $fws_['e_mail'] . "";
        } catch (Exception $exc) {
            $msg = $exc->getMessage();
            $_SESSION['MSG'] = $msg;
            $url = WB_URL_VIEW . "?content=view/inscricao/inscricao_esqueceu_credenciais.php&codRetorno=msgRecuperacao";
        }
        echo "<br><a href='{$url}'>Redirecionando... {$msg}</a><br>";
        if (!$debug) {
            echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $url . "\">";
        }
    }
    
    public function recuperarSenhaAdmin($fws_, $debug = false) {
        try {
            if ($fws_['e_mail'] == '') {
                $codRetorno = "verificarEmailInscrito";
                throw new Exception('E-mail não informado.');
            }
            $objInscrito = new Inscrito();
            $objInscrito->setEMail($fws_['e_mail']);
            $objInscrito->setCodInscrito($fws_['codInscrito']);
            if($fws_['codInscrito']==''){
                $objInscrito->getInscritoByEmail($debug);
            }else{
                $objInscrito->getInscritoByIdSemEvento($debug);
            }

            if (!$objInscrito->getCodInscrito()) {
                $codRetorno = "verificarEmailInscrito";
                throw new Exception('E-mail: (' . $fws_['e_mail'] . '), não encontrando.');
            }

            $objInscrito->carregarObjEvento();
            $objEv=$objInscrito->getObjEvento();
            
            $msgTo = $objInscrito->getEMail();
            $msgSubject = ABREV_EVENTO." - Recuperação Credenciais - " . $objInscrito->getNomeApelido();
            $msgBody = ""
                    . "\r\n\r\nOla " . $objInscrito->getNomeApelido()
                    . "\r\n\r\nInformações de sua inscrição no ".ABREV_EVENTO."."
                    . "\r\n\r\nLogin: " . $objInscrito->getEMail()
                    . "\r\n\r\nSenha: " . $objInscrito->getCodInscrito()
                    ."\r\n url do sistema: ".EMAIL_CONTATO_LINK_ADMIN
                    . "\r\n\r\n ".ABREV_EVENTO." | ".NOME_EVENTO
                    . "\r\n\r\nData: " . Date('d/m/Y H:i')
                    . "";
//            mail($msgTo, $msgSubject, $msgBody, HEADER_EMAIL);
            GerenciarEmailAC::enviarEmail($msgTo, $msgSubject, $msgBody);
            $msg = "Seu login e senha foram enviados para: " . $objInscrito->getEMail();
            $ok = 1;
        } catch (Exception $exc) {
            $msg = $exc->getMessage();
            $ok = 0;
        }
        // NAO gravar $_SESSION['MSG'] aqui: este metodo responde JSON e sai com
        // exit(), entao FWA_Mostrar_Mensagem nunca roda nesta requisicao para
        // consumir e limpar a mensagem. Ela sobrevivia na sessao e reaparecia na
        // ficha do PROXIMO inscrito aberto ("Seu login e senha foram enviados
        // para: <e-mail de outra pessoa>"). A tela ja exibe o texto pela
        // resposta abaixo. O $url daqui tambem era codigo morto -- nao ha
        // redirecionamento num endpoint AJAX.
        echo '{"message":"' . tratarJson($msg) . '","statusMessage":"' . $ok . '"}';
        exit();
    }

    public function verificarInscrito($fws_, $debug = false) {
        if ($fws_['estrangeiro']) {
            return $this->verificarEmailInscrito($fws_);
        }
        return $this->verificarCpfInscrito($fws_);
    }

    public function verificarEmailInscrito($fws_, $debug = false) {
        try {
            $objEv = new Evento();
            $objEv->setCodEvento(COD_EVENTO);
            $objEv->getEventoById();
            $objConf = $objEv->getEventoConfiguracao();
            
            if ($fws_['e_mail'] == '') {
                $codRetorno = "verificarEmailInscrito";
                throw new Exception('E-mail não informado.');
            }
            $obj = new Inscrito();
            $obj->setEMail($fws_['e_mail']);

            if ($obj->verificarEmailEstaInscrito($debug)) {
                $codRetorno = "efetuarLogin";
                /*permitirndo inscrição duplicada*/
                if ($objConf->getInscDuplicada() == 1) {
                    //$msg = "Você já possui um cadastro ";
                }else{
                    throw new Exception($fws_['e_mail'] . ' já cadastrado, é necessário efetuar login.');
                }                
            }
            $msg = "Redirecionando para formulário de cadastro";
            $_SESSION['MSG'] = $msg;
            $url = WB_URL_VIEW . "?content=view/inscricao/inscricao_congresso_perfil_seleciona.php&e_mail=" . $fws_['e_mail'] . "&cpf=" . $fws_['cpf'] . "";
        } catch (Exception $exc) {
            $msg = $exc->getMessage();
            $_SESSION['MSG'] = $msg;
            $url = WB_URL_VIEW . "?content=view/inscricao/inscricao_01.php&codRetorno=" . $codRetorno . "";
        }
        echo "<br><a href='{$url}'>Redirecionando... {$msg}</a><br>";
        if (!$debug) {
            echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $url . "\">";
        }
    }

    public function verificarCpfInscrito($fws_, $debug = false) {
        try {
            $objEv = new Evento();
            $objEv->setCodEvento(COD_EVENTO);
            $objEv->getEventoById();
            $objConf = $objEv->getEventoConfiguracao();
            
            if ($fws_['cpf'] == '') {
                $codRetorno = "verificarCpfInscrito";
                throw new Exception('Cpf não informado.');
            }
            if (!validarCPF($fws_['cpf'])) {
                $codRetorno = "verificarCpfInscrito";
                throw new Exception('CPF informado é inválido.');
            }
            $obj = new Inscrito();
            $obj->setCpf(formatCpfInsert($fws_['cpf']));

            if ($obj->verificarCpfEstaInscrito($debug)) {
                /*permitirndo inscrição duplicada*/
                if ($objConf->getInscDuplicada() == 1) {
                    $codRetorno = "efetuarCompra";
                    $msg = "Você já possui uma compra no cpf: ".$fws_['cpf'];
                }else{
                    $codRetorno = "efetuarLogin";
                    throw new Exception(formatCpfView($fws_['cpf']) . ' já cadastrado, é necessário efetuar login.');
                }
            }
            $msg = "Redirecionando para formulário de cadastro";
            $_SESSION['MSG'] = $msg;
            $url = WB_URL_VIEW . "?content=view/inscricao/inscricao_congresso_perfil_seleciona.php&cpf=" . $fws_['cpf'] . "";
        } catch (Exception $exc) {
            $msg = $exc->getMessage();
            $_SESSION['MSG'] = $msg;
            $url = WB_URL_VIEW . "?content=view/inscricao/inscricao_01.php&codRetorno=" . $codRetorno . "";
        }
        echo "<br><a href='{$url}'>Redirecionando... {$msg}</a><br>";
        if (!$debug) {
            echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $url . "\">";
        }
    }

    public function carregarParticipacao($fws_, $debug = false) {
        try {
            $statusMessage = 0;
            if (!(($_SESSION['_logado']==1) and ($_SESSION['_inscrito']==1))) {
                throw new Exception('Não há usuário logado');
            }
            
            $obj = new Inscrito();
            $obj->setLogin($_SESSION['_login']);
//            $obj->setCpf($_SESSION['_cpf']);
//            $obj->setCodInscrito($_SESSION['_cod_inscrito']);
            $obj->setCodEvento($fws_['cod_evento']);
            $obj->setCodInscrito($fws_['cod_inscrito']);
//            $obj->carregarParticipacaoByLogin($debug);
            $obj->carregarParticipacaoByLoginCodInscrito($debug);
            if(!$obj->getCodInscrito()){
                throw new Exception('Não foi possível encontrar sua inscrição no evento informado');
            }
            
            $_SESSION['_tempo'] = time();
            $_SESSION['_login'] = $obj->getEMail();
            $_SESSION['_nome_usuario'] = $obj->getNomeApelido();
            $_SESSION['_cod_inscrito'] = $obj->getCodInscrito();
            $_SESSION['_cod_usuario'] = $obj->getCodUsuario();
            $_SESSION['_cpf'] = $obj->getCpf();
            $_SESSION['_logado'] = 1;
            $_SESSION['_inscrito'] = 1;
            $statusMessage = 1;

            $message = "Participação carregada com sucesso!";
            $obj->carregarObjEvento($debug);
            $objEv = $obj->getObjEvento(); 
            $url =  UrlView($objEv, $debug) . "?content=view/inscricao/inscricao_congresso_ok.php&codRetorno=" . $codRetorno . "";
        } catch (Exception $exc) {
            $message = $exc->getMessage();
            $url = '';
        }
        
        try {
            $objLogAcesso = new LogAcesso();
            $objLogAcesso->setLogin($_SESSION['_login']);
            $objLogAcesso->setSucesso($statusMessage);
            $objLogAcesso->setOrigem('Carregando Participação');
            $objLogAcesso->salvar($debug);
        } catch (Exception $exc) {                     
        } catch (OKException $exc) { }      
            
        echo '{
            "statusMessage":"' . $statusMessage . '",
            "message":"' . $message . '",
            "url":"' . $url . '"
        }';
        exit();
    }

    public static function efetuarLoginComo($fws_, $debug = false) {
        $_SESSION['_cod_inscrito'] = addslashesopctec($_GET['cod_inscrito']);
        $_SESSION['_cpf']          = addslashesopctec($_GET['cpf']);
        $_SESSION['_cod_evento']   = addslashesopctec($_GET['cod_evento']);
        $_SESSION['_logadoComo']   = 'inscrito';
        $_SESSION['_direcioandoLogarComo']   = 'inscrito';
        /*Redirecionando logar como para url do evento especifico*/
        if($_SESSION['_cod_evento']!=COD_EVENTO){
            $url = "https://opctec.net.br/".WB_GRUPO."/".WP_PROJETO."/inscricoes/?content=view/inscricao/inscricao_congresso_ok.php&acao=logarComo&cpf=".$_SESSION['_cpf']."&cod_inscrito=".$_SESSION['_cod_inscrito']."&cod_evento=".$_SESSION['_cod_evento'];
            echo "<br><a href='{$url}'>Redirecionando para logar como... {$msg}</a><br>";
            if (!$debug) {
                echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $url . "\">";
            }
            exit();
        }
//        $_SESSION['_direcioandoLogarComo']   = NULL;
    }
    
    public function efetuarLogin($fws_, $debug = false) {
        // CSRF no login: PENDENTE. A tela de login faz session_destroy() ao abrir
        // (desloga), o que quebra CSRF por sessao. Fazer depois com double-submit
        // por cookie (nao precisa de sessao).
        try {
            $fws_['loginSucesso'] = 0;
            $fws_['loginOrigem']  = 'inscrito';
            //session_start();
            $_SESSION['_logado'] = false;
            $_SESSION = array();

            $obj = new Inscrito();

            // Copiar/colar costuma trazer espaco ou Enter nas pontas e faz o login
            // falhar. Limpa SO as bordas (nao mexe no meio da senha) de todos os
            // campos de credencial, antes de qualquer validacao/comparacao.
            foreach (array('senha', 'e_mail', '4cpf', 'cpf') as $campoLogin) {
                if (isset($fws_[$campoLogin])) {
                    $fws_[$campoLogin] = trim($fws_[$campoLogin]);
                }
            }
//            if(($fws_['e_mail']!='')and($fws_['cpf']=='')){
//                $fws_['cpf'] = $fws_['e_mail'];
//            }
//            if(($fws_['e_mail']=='')and($fws_['cpf']!='')){
//                $fws_['e_mail'] = $fws_['cpf'];
//            }
            if($fws_['senha']!=''){
                if (!isEntradaSegura($fws_['e_mail'], 'email')) {
                    $codRetorno = "efetuarLogin";
                    throw new Exception('E-mail inválido.');
                }
            }
            if($fws_['cpf']!=''){
                if (!isEntradaSegura($fws_['cpf'], 'cpf')) {
                    $codRetorno = "efetuarLogin";
                    throw new Exception('CPF inválido.');
                }
            }
            if($fws_['senha']!=''){
                if (!isEntradaSegura($fws_['senha'], 'senha')) {
                    $codRetorno = "efetuarLogin";
                    throw new Exception('Senha inválida!');
                }
            }
            if($fws_['4cpf']!=''){
                if (!isEntradaSegura($fws_['4cpf'], 'numeros')) {
                    $codRetorno = "efetuarLogin";
                    throw new Exception('Código inválido!');
                }
            }
            $obj->setEMail($fws_['e_mail']);
            $obj->setCpf($fws_['cpf']);
            $obj->setSenha($fws_['senha']);
            $obj->set4cpf($fws_['4cpf']);
//            if (!$obj->efetuarLogin($debug)) {

            $loginAdmin = false;
            if(COD_EVENTO==10){
                if (!$obj->efetuarLoginCpf($debug)) {
                    $codRetorno = "efetuarLogin";
                    throw new Exception('CPF ou senha não encontrados.');
                }
            }else{
//                if(($_SERVER['REMOTE_ADDR']=='177.82.0.28')or($_SERVER['REMOTE_ADDR']=='200.203.142.25')){
//                    $debug = 1;
//                    try {
//                        $obj->efetuarLoginSisUsuario(1);
//                        if($obj->getCodUsuario()!=''){
//                            $loginAdmin = true;
//                        }else{
//                            $loginAdmin = false;
//                        }
//                    } catch (Exception $exc) {
//                        $loginAdmin = false;
//                        echo $exc->getTraceAsString();
//                        echo __line__.'-';
//                        exit();
//                    }
//                }else{
//                    $loginAdmin = false;
//                }
                try {
                    $obj->efetuarLoginSisUsuario();
                    if($obj->getCodUsuario()!='' && $obj->getCodEvento() == COD_EVENTO){
                        $loginAdmin = true;
                    }else{
                        $loginAdmin = false;
                        // Admin de outro evento - resetar objeto para tentar como inscrito
                        $obj = new Inscrito();
                        $obj->setEMail($fws_['e_mail']);
                        $obj->setCpf($fws_['cpf']);
                        $obj->setSenha($fws_['senha']);
                        $obj->set4cpf($fws_['4cpf']);
                    }
                } catch (Exception $exc) {
                    $loginAdmin = false;
                }
                if (!$loginAdmin) {
                    // 1) Tenta a senha REAL (bcrypt). Se a pessoa ja definiu senha,
                    //    entra por aqui em qualquer evento (mesmo e-mail + CPF).
                    $objHash = new Inscrito();
                    $codEvBcrypt = (COD_EVENTO != 0) ? COD_EVENTO : null;
                    if ($objHash->autenticarPorHash($fws_['e_mail'], $fws_['senha'], $fws_['4cpf'], $codEvBcrypt, $debug)) {
                        $obj = $objHash;
                    } else {
                        // 2) Legado: numero da inscricao (quem ainda nao definiu senha).
                        if($obj->getCodEvento()!=0){
                            if (!$obj->efetuarLogin($debug)) {
                                $codRetorno = "efetuarLogin";
                                throw new Exception('E-mail,senha ou quatro últimos CPF não encontrados.');
                            }
                        }else{
                            if (!$obj->efetuarLoginSemEvento($debug)) {
                                $codRetorno = "efetuarLogin";
                                throw new Exception('E-mail,senha ou quatro CPF não encontrados.');
                            }
                        }
                    }
                }
            }
            if( $obj->getCodUsuario()!='') {
                $_SESSION['userLogado']['_tempo'] = time();
                $_SESSION['userLogado']['_login'] = $obj->getEMail();
                $_SESSION['userLogado']['_cod_usuario'] = $obj->getCodUsuario();
//                $_SESSION['userLogado']['_cod_convidado'] = $obj->getCodUsuario();
                $_SESSION['userLogado']['_cod_inscrito'] = $obj->getCodInscrito();
                $_SESSION['userLogado']['_nome_usuario'] = $obj->getNomeApelido();
                $_SESSION['userLogado']['_logado'] = 1;
                $_SESSION['userLogado']['_administrativo'] = 1;
                $_SESSION['_administrativo'] = 1;
            }
            $_SESSION['_tempo'] = time();
            $_SESSION['_login'] = $obj->getEMail();
            $_SESSION['_nome_usuario'] = $obj->getNomeApelido();
            $_SESSION['_cod_inscrito'] = $obj->getCodInscrito();
            $_SESSION['_cod_usuario'] = $obj->getCodUsuario();
            $_SESSION['_cpf'] = $obj->getCpf();
            $_SESSION['_logado'] = 1;
            $_SESSION['_inscrito'] = 1;
            $fws_['loginSucesso'] = 1;
//            echo "<pre>";
//            echo $_SESSION['_cod_usuario'].'<-';
//            print_r($_SESSION);
//            echo "</pre>";
//            exit();
            $msg = "Login efetuado com sucesso!";
            $_SESSION['MSG'] = $msg;
            $obj->carregarObjEvento($debug);
            $objEv = $obj->getObjEvento(); 
            $objEvConf = $objEv->getEventoConfiguracao($debug);
            if($_SESSION['_administrativo']==1){
                $url =  UrlView($objEv, $debug) . "?content=view/admin/default.php&codRetorno=" . $codRetorno . "";
            }else{
                $url =  UrlView($objEv, $debug) . "?content=view/inscricao/inscricao_congresso_ok.php&codRetorno=" . $codRetorno . "";
            }
            if($objEvConf->getHallVirtualAtivar()==1){
                $url = UrlView($objEv, $debug) . "?content=view/inscricao/inscricao_congresso_hall_virtual.php&codRetorno=" . $codRetorno . "";
            }
            } catch (Exception $exc) {
            $msg = $exc->getMessage();
            $_SESSION['MSG'] = $msg;
            $url = WB_URL_VIEW . "?content=view/inscricao/inscricao_01.php&codRetorno=" . $codRetorno . "&e_mail=" . $fws_['e_mail'] . "";
        }
        
        try {
            $objLogAcesso = new LogAcesso();
            $objLogAcesso->setLogin($fws_['e_mail']);
            $objLogAcesso->setSucesso($fws_['loginSucesso']);
            $objLogAcesso->setOrigem($fws_['loginOrigem']);
            $objLogAcesso->setDescricao($msg);
            $objLogAcesso->salvar($debug);
        } catch (Exception $exc) {                     
        } catch (OKException $exc) { }      
            
        echo "<br><a href='{$url}'>Redirecionando... {$msg}</a><br>";
        if (!$debug) {
            echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $url . "\">";
            exit();
        }
    }

    public function efetuarLogout($fws_, $debug = false) {
        //session_start();
        $_SESSION['_logado'] = false;
        $_SESSION = array();
        unset($_SESSION);
        session_destroy();
        //session_start();
        $_SESSION['MSG'] = $msg;
        $url = WB_URL_VIEW . "?content=view/inscricao/inscricao_01.php&codRetorno=" . $codRetorno . "";
        echo "<br><a href='{$url}'>Redirecionando... {$msg}</a><br>";
        if (!$debug) {
            echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $url . "\">";
        }
    }

    public function efetuarLogoutLogarComo($fws_, $debug = false) {
        try {
            // Registrar log de saida do "logar como inscrito"
            try {
                $objUsuarioDAO = new UsuarioDAO();
                $objUsuarioDAO->registrarLogLogin(
                    $_SESSION['userLogado']['_cod_usuario'],
                    $_SESSION['userLogado']['_login'],
                    true,
                    'logout_logar_como_inscrito:' . $_SESSION['_cod_inscrito']
                );
            } catch (Exception $e) {
                // nao bloquear
            }

            // Restaurar sessao do usuario admin original
            $_SESSION['_login']         = $_SESSION['userLogado']['_login'];
            $_SESSION['_cod_usuario']   = $_SESSION['userLogado']['_cod_usuario'];
            $_SESSION['_cod_convidado'] = $_SESSION['userLogado']['_cod_convidado'];
            $_SESSION['_nome_usuario']  = $_SESSION['userLogado']['_nome_usuario'];
            $_SESSION['_logado']        = $_SESSION['userLogado']['_logado'];
            $_SESSION['_administrativo'] = $_SESSION['userLogado']['_administrativo'];
            $_SESSION['_cod_inscrito']  = $_SESSION['userLogado']['_cod_inscrito'];
            $_SESSION['_logadoComo']    = '';

        } catch (Exception $exc) {
            $MSG = $exc->getMessage();
        }

        FWA_Mostrar_Mensagem($MSG);
        $_SESSION['MSG'] = $MSG;
        echo "<html><head><title>Sessao encerrada</title></head><body style='font-family:Arial,sans-serif; text-align:center; padding-top:80px;'>";
        echo "<h3>Sess&atilde;o de &quot;logar como inscrito&quot; encerrada.</h3>";
        echo "<p>Voc&ecirc; pode fechar esta janela.</p>";
        echo "<script>try { window.close(); } catch(e) {}</script>";
        echo "</body></html>";
        exit();
    }

    public function loginAdministrativo($fws_, $debug = false) {
        $fws_['loginSucesso'] = 0;
        $fws_['loginOrigem']  = 'administrativo';
        $url = "" . WB_URL_VIEW . "?content=view/admin/login.php";

        if (empty($fws_['login']) || empty($fws_['senha'])) {
            $_SESSION['MSG'] = "Informe login e senha.";
        } else {
            $fws_['senha'] = trim($fws_['senha']);

            $_SESSION['_logado'] = false;
            $_SESSION = array();

            try {
                $objUsuario = new Usuario();
                $objUsuario->setLogin($fws_['login']);
                $objUsuario->autenticar($fws_['senha'], $debug);

                // Autenticacao OK — montar sessao
                $codInscrito = $objUsuario->getCodInscrito();
                if (empty($codInscrito)) {
                    $codInscrito = $objUsuario->get_CodInscrito(); // campo _cod_inscrito do JOIN
                }

                $_SESSION['_tempo'] = time();
                $_SESSION['_login'] = $fws_['login'];
                $_SESSION['_cod_usuario'] = $objUsuario->getCodUsuario();
                $_SESSION['_cod_convidado'] = $objUsuario->getCodUsuario();
                $_SESSION['_cod_inscrito'] = $codInscrito;
                $_SESSION['_nome_usuario'] = $objUsuario->getNomeConvidado();
                $_SESSION['_logado'] = 1;
                $_SESSION['_administrativo'] = 1;
                $_SESSION['_session_token'] = $objUsuario->getSessionToken();

                $_SESSION['userLogado']['_tempo'] = time();
                $_SESSION['userLogado']['_login'] = $fws_['login'];
                $_SESSION['userLogado']['_cod_usuario'] = $objUsuario->getCodUsuario();
                $_SESSION['userLogado']['_cod_convidado'] = $objUsuario->getCodUsuario();
                $_SESSION['userLogado']['_cod_inscrito'] = $codInscrito;
                $_SESSION['userLogado']['_nome_usuario'] = $objUsuario->getNomeConvidado();
                $_SESSION['userLogado']['_logado'] = 1;
                $_SESSION['userLogado']['_administrativo'] = 1;

                $fws_['loginSucesso'] = 1;

                // Verificar se precisa forcar troca de senha
                if ($objUsuario->getForcarTrocaSenha() == 1) {
                    $url = "" . WB_URL_VIEW . "?content=view/admin/login-forcar-troca-senha.php&motivo=obrigatoria";
                } else {
                    // Se existir URL de retorno salva em cookie (link externo), redireciona pra ela
                    if (!empty($_COOKIE['opctec_retorno_pos_login'])) {
                        $urlCookie = $_COOKIE['opctec_retorno_pos_login'];
                        setcookie('opctec_retorno_pos_login', '', time() - 3600, '/');
                        if (filter_var($urlCookie, FILTER_VALIDATE_URL)
                            && parse_url($urlCookie, PHP_URL_HOST) === $_SERVER['HTTP_HOST']) {
                            $url = $urlCookie;
                        } else {
                            $url = "" . WB_URL_VIEW . "?content=view/admin/default.php";
                        }
                    } else {
                        $url = "" . WB_URL_VIEW . "?content=view/admin/default.php";
                    }
                }

            } catch (Exception $e) {
                $_SESSION['MSG'] = $e->getMessage();
            }

            // Log de acesso (manter compatibilidade com LogAcesso existente)
            try {
                $objLogAcesso = new LogAcesso();
                $objLogAcesso->setLogin($fws_['login']);
                $objLogAcesso->setSucesso($fws_['loginSucesso']);
                $objLogAcesso->setOrigem($fws_['loginOrigem']);
                $objLogAcesso->salvar($debug);
            } catch (Exception $exc) {
            } catch (OKException $exc) { }
        }

        echo "<a href='" . $url . "'>" . $url . "</a>";
        if (!$debug) {
            echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $url . "\"></HTML>";
        }
    }

    public function loginComoAdministrativo($fws_, $debug = false) {
        // Aceitar login via POST (seguro) ou GET (legado)
        $loginAlvo = isset($fws_['login']) && $fws_['login'] != '' ? $fws_['login'] : addslashesopctec($_GET['login']);
        try {
            // Verificar permissao — somente quem tem acesso ao ADM Geral (cod_sistema=999)
            if (!FWA_Autenticacao($_SESSION['userLogado']['_cod_usuario'], enumSistema::SIS_GERENCIAR_ADM_GERAL, 5, false, false)) {
                throw new Exception("Usuario logado nao tem permissao para logar como outro usuario.");
            }
            if ($loginAlvo == '') {
                throw new Exception("Login nao informado!");
            }

            $var_sql = "SELECT su.cod_usuario, IFNULL(su.nome_usuario, tc.nome_convidado) as nome_convidado "
                    . "FROM sis_usuarios su "
                    . "LEFT JOIN tab_convidados tc ON su.cod_usuario = tc.cod_usuario "
                    . "INNER JOIN sis_ligacao_usuarios_eventos lue ON su.cod_usuario = lue.cod_usuario AND lue.cod_evento = " . intval(COD_EVENTO) . " "
                    . "WHERE su.status = 'ATIVADA' "
                    . "AND su.login = '" . addslashes($loginAlvo) . "'";

            $Conexao_db = FWA_Conexao('OpcTec');
            $Conexao_db->debug = $debug;
            $RS = $Conexao_db->Execute($var_sql);

            if (!$RS || $RS->EOF) {
                throw new Exception("Login nao encontrado!");
            }

            // Registrar log de "logar como"
            try {
                $objUsuarioDAO = new UsuarioDAO();
                $objUsuarioDAO->registrarLogLogin(
                    $_SESSION['userLogado']['_cod_usuario'],
                    $_SESSION['userLogado']['_login'],
                    true,
                    'logar_como:' . $loginAlvo
                );
            } catch (Exception $e) {
                // nao bloquear
            }

            $_SESSION['_login']         = $loginAlvo;
            $_SESSION['_cod_usuario']   = $RS->fields['cod_usuario'];
            $_SESSION['_cod_convidado'] = $RS->fields['cod_usuario'];
            $_SESSION['_nome_usuario']  = $RS->fields['nome_convidado'];
            $_SESSION['_logadoComo']    = 'administrativo';
            $_SESSION['_logadoComoExpira'] = time() + 1800; // expira em 30 minutos

        } catch (Exception $exc) {
            $MSG = $exc->getMessage();
        }

        FWA_Mostrar_Mensagem($MSG);
        $_SESSION['MSG'] = $MSG;
        $url = "" . WB_URL_VIEW . "?content=view/admin/default.php";
        echo "<a href='" . $url . "'>" . $url . "->codigo do usuario:" . $_SESSION['_cod_usuario'] . "</a>";
        if (!$debug) {
            echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $url . "\"></HTML>";
        }
    }
    public function logoutComoAdministrativo($fws_, $debug = false) {

        try {
            // Registrar log de saida do "logar como"
            try {
                $objUsuarioDAO = new UsuarioDAO();
                $objUsuarioDAO->registrarLogLogin(
                    $_SESSION['userLogado']['_cod_usuario'],
                    $_SESSION['userLogado']['_login'],
                    true,
                    'logout_logar_como:' . $_SESSION['_login']
                );
            } catch (Exception $e) {
                // nao bloquear
            }

            $_SESSION['_login']         = $_SESSION['userLogado']['_login'];
            $_SESSION['_cod_usuario']   = $_SESSION['userLogado']['_cod_usuario'];
            $_SESSION['_cod_convidado'] = $_SESSION['userLogado']['_cod_convidado'];
            $_SESSION['_nome_usuario']  = $_SESSION['userLogado']['_nome_usuario'];
            $_SESSION['_logadoComo']    = '';
            unset($_SESSION['_logadoComoExpira']);

        } catch (Exception $exc) {
            $MSG = $exc->getMessage();
        }
        FWA_Mostrar_Mensagem($MSG);
        $_SESSION['MSG'] = $MSG;
        $url = "" . WB_URL_VIEW . "?content=view/admin/default.php";
        echo "<a href='" . $url . "'>" . $url . "->codigo do usuario:" . $_SESSION['_cod_usuario'] . "</a>";
        if (!$debug) {
            echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $url . "\"></HTML>";
        }
    }

    /**
     * Carrega dados da conta do usuario logado (nome, e-mail, celular).
     */
    public function carregarDadosConta($fws_, $debug = false) {
        ob_clean();
        try {
            $codUsuario = intval($_SESSION['_cod_usuario']);
            if (!$codUsuario) {
                throw new Exception("Usu&aacute;rio n&atilde;o identificado.");
            }

            ConexaoDB::getInstance();
            $conex = ConexaoDB::getConexaoDB();

            $sql = sprintf("SELECT su.login,
                    COALESCE(tc.nome_convidado, su.nome_usuario) as nome,
                    tc.fone_celular as celular
                FROM sis_usuarios su
                LEFT JOIN tab_convidados tc ON tc.cod_usuario = su.cod_usuario
                WHERE su.cod_usuario = %d AND su.status = 'ATIVADA'
                LIMIT 1",
                $codUsuario);

            $conex->debug = $debug;
            $rs = $conex->Execute($sql);

            if (!$rs || $rs->EOF) {
                throw new Exception("Dados n&atilde;o encontrados.");
            }

            echo json_encode(array(
                'statusMessage' => '1',
                'nome'    => $rs->fields['nome'] ?: '',
                'login'   => $rs->fields['login'] ?: '',
                'celular' => $rs->fields['celular'] ?: ''
            ));
        } catch (Exception $e) {
            echo json_encode(array('statusMessage' => '', 'message' => $e->getMessage()));
        }
        exit();
    }

    /**
     * Atualiza dados da conta do usuario logado (nome, celular).
     */
    public function atualizarDadosConta($fws_, $debug = false) {
        ob_clean();
        try {
            $codUsuario = intval($_SESSION['_cod_usuario']);
            if (!$codUsuario) {
                throw new Exception("Usu&aacute;rio n&atilde;o identificado.");
            }

            $nome    = trim($fws_['nome']);
            $celular = trim($fws_['celular']);

            if ($nome == '') {
                throw new Exception("Nome &eacute; obrigat&oacute;rio.");
            }

            ConexaoDB::getInstance();
            $conex = ConexaoDB::getConexaoDB();

            // Atualizar nome em tab_convidados
            $sql = sprintf("UPDATE tab_convidados SET
                    nome_convidado = '%s',
                    fone_celular = '%s'
                WHERE cod_usuario = %d",
                addslashes($nome),
                addslashes($celular),
                $codUsuario);

            $conex->debug = $debug;
            $conex->Execute($sql);

            echo json_encode(array(
                'statusMessage' => '1',
                'message' => 'Dados atualizados com sucesso.'
            ));
        } catch (Exception $e) {
            echo json_encode(array('statusMessage' => '', 'message' => $e->getMessage()));
        }
        exit();
    }

}


<?php

class GerenciarSupervisorAC {

    function __construct() {
        $cod_usuario = $_SESSION['_cod_usuario'];
        if (!FWA_Autenticacao($cod_usuario, enumSistema::SIS_PEDAGOGICO, 5, false, false)
            && !FWA_Autenticacao($cod_usuario, enumSistema::SIS_PEDAGOGICO_GERENTE, 5, false, false)) {
            throw new Exception("Acesso restrito a coordenadores e gerentes.");
        }
    }

    /**
     * Busca o cod_grupo que tem direito sobre SIS_PEDAGOGICO_SUPERVISOR no evento atual.
     * Retorna o cod_grupo ou false se nao encontrar.
     */
    private function getCodGrupoSupervisor($debug = false) {
        ConexaoDB::getInstance();
        $conex = ConexaoDB::getConexaoDB();

        $sql = sprintf("SELECT dgs.cod_grupo FROM sis_direitos_grupos_sistemas dgs
            WHERE dgs.cod_sistema = %s AND dgs.cod_evento = %s LIMIT 1",
            enumSistema::SIS_PEDAGOGICO_SUPERVISOR, COD_EVENTO);

        $conex->debug = $debug;
        $rs = $conex->Execute($sql);

        if ($rs && !$rs->EOF) {
            return intval($rs->fields['cod_grupo']);
        }
        return false;
    }

    private function gerarSenhaAleatoria($tamanho = 10) {
        return SenhaHelper::gerarSenhaAleatoria($tamanho);
    }

    public function cadastrarSupervisor($fws_, $debug = false) {
        ob_clean();
        $ok = false;
        $MSG = '';
        try {
            $nome  = trim($fws_['nome']);
            $login = trim($fws_['login']);
            $cpf     = preg_replace('/[^0-9]/', '', trim(isset($fws_['cpf']) ? $fws_['cpf'] : ''));
            $celular = trim(isset($fws_['celular']) ? $fws_['celular'] : '');

            if ($nome == '') {
                throw new Exception("Nome &eacute; obrigat&oacute;rio.");
            }
            if ($login == '') {
                throw new Exception("Login (e-mail) &eacute; obrigat&oacute;rio.");
            }
            if ($cpf == '') {
                throw new Exception("CPF &eacute; obrigat&oacute;rio.");
            }
            // Celular opcional

            $codGrupoSupervisor = $this->getCodGrupoSupervisor($debug);
            if (!$codGrupoSupervisor) {
                throw new Exception("Grupo de supervisor n&atilde;o est&aacute; configurado para este evento. Configure primeiro em Gest&atilde;o de Acessos.");
            }

            $senha = $this->gerarSenhaAleatoria();

            // 1. Criar usuario (ou localizar existente)
            $objLogin = new Usuario();
            $objLogin->setLogin($login);
            $objLogin->setSenha(''); // senha em texto puro vazia — usar hash
            $objLogin->setSenhaHash(SenhaHelper::hash($senha));
            $objLogin->setForcarTrocaSenha(1);
            $objLogin->setNome($nome);
            $objLogin->getUsuarioByLogin($debug);

            $loginExistente = false;
            if ($objLogin->getCodUsuario() == '') {
                // Usuario::salvar() lanca OKException no sucesso. Sem o catch local
                // a excecao quebra o fluxo aqui e os passos 2/3/4 (associar evento,
                // grupo e convidado) nao rodam — o usuario fica orfao em sis_usuarios.
                try {
                    $objLogin->salvar($debug);
                } catch (OKException $e) {
                    // ok - INSERT concluido, cod_usuario ja setado em $objLogin
                }
            } else {
                $loginExistente = true;
            }

            $codUsuario = $objLogin->getCodUsuario();

            // 2. Associar ao evento atual
            $objUE = new UsuarioEvento();
            $objUE->setCodEvento(COD_EVENTO);
            $objUE->setCodUsuario($codUsuario);
            $objUE->getUsuarioEventoById($debug);

            if ($objUE->getStatus() != 'ATIVADA') {
                try {
                    $objUE->adicionarUsuarioEvento($debug);
                } catch (OKException $e) {
                    // ok - associado com sucesso
                } catch (ADODB_Exception $e) {
                    if (strpos($e->getMessage(), 'Duplicate') === false) {
                        throw $e;
                    }
                }
            }

            // 3. Associar ao grupo de supervisor
            $objUG = new UsuarioGrupo();
            $objUG->setCodUsuario($codUsuario);
            $objUG->setCodGrupo($codGrupoSupervisor);
            $objUG->setCodEvento(COD_EVENTO);

            // Tentar adicionar ao grupo (se ja estiver, ignora duplicidade)
            try {
                $objUG->adicionarUsuarioGrupo();
            } catch (OKException $e) {
                // ok - adicionado com sucesso
            } catch (ADODB_Exception $e) {
                // Duplicidade (ja esta no grupo) - ignorar
                if (strpos($e->getMessage(), 'Duplicate') === false) {
                    throw $e; // erro real - propagar
                }
            }

            // 4. Criar ou associar convidado
            // Convidado::salvar() tambem lanca OKException no sucesso — catch local
            // garante que o fluxo termine no echo final em vez de cair no catch
            // externo, que retorna mensagem generica em vez da customizada.
            $objConv = new Convidado();
            $objConv->setEMail($login);
            if ($objConv->getConvidadoByEmail($debug)) {
                if ($objConv->getCodUsuario() == '' || $objConv->getCodUsuario() == '0') {
                    $objConv->setCodUsuario($codUsuario);
                }
                if ($cpf) {
                    $objConv->setCpf($cpf);
                }
                if ($celular) {
                    $objConv->setFoneCelular($celular);
                }
                try {
                    $objConv->salvar($debug);
                } catch (OKException $e) {
                    // ok - UPDATE concluido
                }
            } else {
                $objConv->setNomeConvidado($nome);
                $objConv->setCodUsuario($codUsuario);
                if ($cpf) {
                    $objConv->setCpf($cpf);
                }
                if ($celular) {
                    $objConv->setFoneCelular($celular);
                }
                try {
                    $objConv->salvar($debug);
                } catch (OKException $e) {
                    // ok - INSERT concluido
                }
            }

            $ok = true;
            if ($loginExistente) {
                $MSG = 'Login j&aacute; existia. Usu&aacute;rio vinculado ao grupo de supervisor.';
            } else {
                $MSG = 'Supervisor cadastrado com sucesso.';
            }

            echo json_encode(array(
                'statusMessage' => '1',
                'message' => $MSG,
                'codUsuario' => $codUsuario,
                'login' => $login,
                'loginExistente' => $loginExistente
            ));
        } catch (OKException $e) {
            echo json_encode(array(
                'statusMessage' => '1',
                'message' => $e->getMessage(),
                'codUsuario' => isset($codUsuario) ? $codUsuario : '',
                'login' => isset($login) ? $login : ''
            ));
        } catch (Exception $e) {
            echo json_encode(array(
                'statusMessage' => '',
                'message' => $e->getMessage()
            ));
        }
        exit();
    }

    public function resetarSenha($fws_, $debug = false) {
        ob_clean();
        try {
            $codUsuario = intval($fws_['codUsuario']);
            if (!$codUsuario) {
                throw new Exception("Usu&aacute;rio n&atilde;o informado.");
            }

            // Buscar login do usuario
            $objUsuario = new Usuario();
            $objUsuario->setCodUsuario($codUsuario);
            $objUsuario->setCodEvento(COD_EVENTO);
            $objUsuario->getUsuarioById($debug);

            $login = $objUsuario->getLogin();
            if (!$login) {
                throw new Exception("Login do usu&aacute;rio n&atilde;o encontrado.");
            }

            // Gerar token de recuperacao (mesmo fluxo do "esqueceu senha")
            $dados = $objUsuario->solicitarRecuperacaoSenha($login, $debug);

            if ($dados && $dados['email'] && filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
                $linkReset = WB_URL_VIEW . '?content=view/admin/login-redefinir-senha.php&token=' . $dados['token'];

                $assunto = '[' . ABREV_EVENTO_EMAIL . '] Redefinicao de senha';
                $corpo = '<p>Sua senha de acesso ao sistema administrativo foi resetada.</p>'
                    . '<p>Clique no link abaixo para definir uma nova senha:</p>'
                    . '<p><a href="' . $linkReset . '">' . $linkReset . '</a></p>'
                    . '<p>Este link e valido por 1 hora e pode ser usado apenas uma vez.</p>';

                GerenciarEmailAC::enviarEmail($dados['email'], $assunto, $corpo, false, true);

                echo json_encode(array(
                    'statusMessage' => '1',
                    'message' => 'Link de redefini&ccedil;&atilde;o de senha enviado para ' . htmlspecialchars($dados['email']) . '. O usu&aacute;rio dever&aacute; definir uma nova senha.'
                ));
            } else {
                throw new Exception("N&atilde;o foi poss&iacute;vel enviar o e-mail de redefini&ccedil;&atilde;o. Verifique se o usu&aacute;rio possui e-mail cadastrado.");
            }
        } catch (Exception $e) {
            echo json_encode(array(
                'statusMessage' => '',
                'message' => $e->getMessage()
            ));
        }
        exit();
    }

    public function carregarTabelaSupervisores($fws_, $debug = false) {
        try {
            $codGrupoSupervisor = $this->getCodGrupoSupervisor($debug);
            if (!$codGrupoSupervisor) {
                echo "<tr><td colspan='4'>Grupo de supervisor n&atilde;o configurado.</td></tr>";
                return;
            }

            ConexaoDB::getInstance();
            $conex = ConexaoDB::getConexaoDB();

            $sql = sprintf("SELECT su.cod_usuario, su.login,
                    COALESCE(tc.nome_convidado, su.nome_usuario, su.login) as nome,
                    su.ultimo_login,
                    tc.cpf, tc.fone_celular,
                    (SELECT COUNT(*) FROM acad_aluno_contexto a
                        WHERE a.id_inclusao = su.cod_usuario AND a.cod_evento = %d AND a.status = 'ATIVADA') as total_alunos
                FROM sis_usuarios su
                INNER JOIN sis_ligacao_usuarios_eventos lue ON su.cod_usuario = lue.cod_usuario AND lue.cod_evento = %d
                INNER JOIN sis_ligacao_usuarios_grupos lug ON su.cod_usuario = lug.cod_usuario AND lug.cod_evento = %d AND lug.cod_grupo = %d
                LEFT JOIN tab_convidados tc ON su.cod_usuario = tc.cod_usuario
                WHERE su.status = 'ATIVADA'
                ORDER BY su.cod_usuario DESC",
                COD_EVENTO, COD_EVENTO, COD_EVENTO, $codGrupoSupervisor);

            $conex->debug = $debug;
            $rs = $conex->Execute($sql);

            if (!$rs || $rs->EOF) {
                echo "<tr><td colspan='5'>Nenhum supervisor cadastrado.</td></tr>";
                return;
            }

            $ii = 0;
            while (!$rs->EOF) {
                $ii++;
                $codUsuario   = intval($rs->fields['cod_usuario']);
                $login        = htmlspecialchars($rs->fields['login']);
                $nome         = htmlspecialchars($rs->fields['nome']);
                $totalAlunos  = intval($rs->fields['total_alunos']);
                $ultimoLogin  = $rs->fields['ultimo_login'];
                $cpf          = htmlspecialchars($rs->fields['cpf'] ?: '');
                $celular      = htmlspecialchars($rs->fields['fone_celular'] ?: '');
                $celularLimpo = preg_replace('/[^0-9]/', '', $rs->fields['fone_celular'] ?: '');
                $ultimoLoginFmt = $ultimoLogin ? date('d/m/Y H:i', strtotime($ultimoLogin)) : '<span class="text-muted">Nunca</span>';
                ?>
                <tr data-cod-usuario="<?php echo $codUsuario ?>" data-nome="<?php echo $nome ?>" data-login="<?php echo $login ?>" data-cpf="<?php echo $cpf ?>" data-celular="<?php echo $celular ?>">
                    <td><?php echo $nome ?></td>
                    <td><a href="mailto:<?php echo $login ?>" title="Enviar e-mail"><?php echo $login ?></a></td>
                    <td style="text-align:center;"><?php echo $totalAlunos ?></td>
                    <td style="text-align:center;">
                        <?php echo $ultimoLoginFmt ?>
                        <a href="javascript:void(0)" class="btn-historico-acessos" title="Ver hist&oacute;rico de acessos" style="margin-left:4px;">
                            <i class="fa fa-history text-primary"></i>
                        </a>
                    </td>
                    <td class="text-nowrap" style="font-size:16px;">
                        <a href="javascript:void(0)" class="btn-editar-supervisor" title="Editar dados" style="margin-right:6px;">
                            <i class="fa fa-pencil text-info"></i>
                        </a>
                        <?php if ($celularLimpo) { ?>
                        <a href="https://wa.me/55<?php echo $celularLimpo ?>" target="_blank" title="WhatsApp: <?php echo $celular ?>" style="margin-right:6px;">
                            <i class="fa fa-whatsapp text-success"></i>
                        </a>
                        <?php } else { ?>
                        <span title="Celular n&atilde;o cadastrado" style="cursor:not-allowed; opacity:0.4; margin-right:6px;">
                            <i class="fa fa-whatsapp"></i>
                        </span>
                        <?php } ?>
                        <a href="javascript:void(0)" onclick="confirmarLogarComo('<?php echo $login ?>')" title="Logar como este supervisor" style="margin-right:6px;">
                            <img src="resources/image/icone_preview.gif" border="0">
                        </a>
                        <a href="javascript:void(0)" class="btn-resetar-senha" title="Resetar senha" style="margin-right:6px;">
                            <i class="fa fa-key text-warning"></i>
                        </a>
                        <button class="btn btn-xs btn-acao-excluir btn-remover-supervisor" title="Remover do grupo supervisor">
                            <i class="fa fa-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php
                $rs->MoveNext();
            }
            echo "<tr><td colspan='5'>Total de supervisores: $ii</td></tr>";
        } catch (Exception $e) {
            echo "<tr><td colspan='4'>Erro: " . $e->getMessage() . "</td></tr>";
        }
    }

    public function enviarEmailBoasVindas($fws_, $debug = false) {
        ob_clean();
        try {
            $codUsuario = intval($fws_['codUsuario']);
            $login = trim($fws_['login']);
            if (!$codUsuario || !$login) {
                throw new Exception("Dados insuficientes para envio.");
            }

            // Gerar token de redefinicao
            $objUsuario = new Usuario();
            $objUsuario->setCodUsuario($codUsuario);
            $objUsuario->setCodEvento(COD_EVENTO);
            $dados = $objUsuario->solicitarRecuperacaoSenha($login, $debug);

            if (!$dados || !$dados['email'] || !filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception("N&atilde;o foi poss&iacute;vel localizar o e-mail do usu&aacute;rio.");
            }

            $linkReset = WB_URL_VIEW . '?content=view/admin/login-redefinir-senha.php&token=' . $dados['token'];

            $assunto = '[' . ABREV_EVENTO_EMAIL . '] Acesso ao sistema administrativo';
            $corpo = '<p>Ol&aacute;,</p>'
                . '<p>Seu acesso ao sistema administrativo do ' . NOME_EVENTO . ' foi criado.</p>'
                . '<p><strong>Login:</strong> ' . htmlspecialchars($login) . '</p>'
                . '<p>Para definir sua senha, clique no link abaixo:</p>'
                . '<p><a href="' . $linkReset . '">' . $linkReset . '</a></p>'
                . '<p>Este link &eacute; v&aacute;lido por 1 hora.</p>'
                . '<p>' . EMAIL_CONTATO_ATT . '</p>';

            $enviou = GerenciarEmailAC::enviarEmail($dados['email'], $assunto, $corpo, false, true);

            if ($enviou) {
                echo json_encode(array(
                    'statusMessage' => '1',
                    'message' => 'E-mail enviado com sucesso para ' . htmlspecialchars($dados['email']) . '.'
                ));
            } else {
                throw new Exception("Falha ao enviar o e-mail.");
            }
        } catch (Exception $e) {
            echo json_encode(array(
                'statusMessage' => '',
                'message' => $e->getMessage()
            ));
        }
        exit();
    }

    public function carregarHistoricoAcessos($fws_, $debug = false) {
        ob_clean();
        try {
            $codUsuario = intval($fws_['codUsuario']);
            if (!$codUsuario) {
                throw new Exception("Usu&aacute;rio n&atilde;o informado.");
            }

            ConexaoDB::getInstance();
            $conex = ConexaoDB::getConexaoDB();

            $sql = sprintf(
                "SELECT data_acesso, sucesso, motivo, ip, http_user_agent
                 FROM sis_log_login
                 WHERE cod_usuario = '%s'
                   AND motivo IN ('login_ok', 'senha_invalida')
                 ORDER BY data_acesso DESC
                 LIMIT 50",
                addslashes($codUsuario)
            );

            $conex->debug = $debug;
            $rs = $conex->Execute($sql);

            $registros = array();
            if ($rs && !$rs->EOF) {
                while (!$rs->EOF) {
                    $registros[] = array(
                        'data_acesso'    => $rs->fields['data_acesso'],
                        'sucesso'        => intval($rs->fields['sucesso']),
                        'motivo'         => $rs->fields['motivo'],
                        'ip'             => $rs->fields['ip'],
                        'http_user_agent' => $rs->fields['http_user_agent']
                    );
                    $rs->MoveNext();
                }
            }

            echo json_encode(array(
                'statusMessage' => '1',
                'registros' => $registros
            ));
        } catch (Exception $e) {
            echo json_encode(array(
                'statusMessage' => '',
                'message' => $e->getMessage()
            ));
        }
        exit();
    }

    public function logarComoSupervisor($fws_, $debug = false) {
        $loginAlvo = trim(isset($fws_['login']) ? $fws_['login'] : '');
        $MSG = '';
        try {
            if ($loginAlvo == '') {
                throw new Exception("Login n&atilde;o informado.");
            }

            // Verificar se o alvo e realmente um supervisor do evento atual
            $codGrupoSupervisor = $this->getCodGrupoSupervisor($debug);
            if (!$codGrupoSupervisor) {
                throw new Exception("Grupo de supervisor n&atilde;o configurado.");
            }

            ConexaoDB::getInstance();
            $conex = ConexaoDB::getConexaoDB();

            $sql = sprintf(
                "SELECT su.cod_usuario, IFNULL(su.nome_usuario, tc.nome_convidado) as nome_convidado
                 FROM sis_usuarios su
                 INNER JOIN sis_ligacao_usuarios_eventos lue ON su.cod_usuario = lue.cod_usuario AND lue.cod_evento = %d
                 INNER JOIN sis_ligacao_usuarios_grupos lug ON su.cod_usuario = lug.cod_usuario AND lug.cod_evento = %d AND lug.cod_grupo = %d
                 LEFT JOIN tab_convidados tc ON su.cod_usuario = tc.cod_usuario
                 WHERE su.status = 'ATIVADA' AND su.login = '%s'",
                COD_EVENTO, COD_EVENTO, $codGrupoSupervisor, addslashes($loginAlvo)
            );

            $conex->debug = $debug;
            $RS = $conex->Execute($sql);

            if (!$RS || $RS->EOF) {
                throw new Exception("Usu&aacute;rio n&atilde;o encontrado ou n&atilde;o &eacute; supervisor deste evento.");
            }

            // Registrar log
            try {
                $objUsuarioDAO = new UsuarioDAO();
                $objUsuarioDAO->registrarLogLogin(
                    $_SESSION['userLogado']['_cod_usuario'],
                    $_SESSION['userLogado']['_login'],
                    true,
                    'logar_como_supervisor:' . $loginAlvo
                );
            } catch (Exception $e) {
                // nao bloquear
            }

            $_SESSION['_login']         = $loginAlvo;
            $_SESSION['_cod_usuario']   = $RS->fields['cod_usuario'];
            $_SESSION['_cod_convidado'] = $RS->fields['cod_usuario'];
            $_SESSION['_nome_usuario']  = $RS->fields['nome_convidado'];
            $_SESSION['_logadoComo']    = 'administrativo';
            $_SESSION['_logadoComoExpira'] = time() + 1800;

        } catch (Exception $exc) {
            $MSG = $exc->getMessage();
        }

        FWA_Mostrar_Mensagem($MSG);
        $_SESSION['MSG'] = $MSG;
        $url = "" . WB_URL_VIEW . "?content=view/admin/default.php";
        if (!$debug) {
            echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $url . "\"></HTML>";
        }
    }

    public function editarSupervisor($fws_, $debug = false) {
        ob_clean();
        try {
            $codUsuario = intval($fws_['codUsuario']);
            if (!$codUsuario) {
                throw new Exception("Usu&aacute;rio n&atilde;o informado.");
            }

            $nome    = trim($fws_['nome']);
            $cpf     = preg_replace('/[^0-9]/', '', trim(isset($fws_['cpf']) ? $fws_['cpf'] : ''));
            $celular = trim(isset($fws_['celular']) ? $fws_['celular'] : '');

            if ($nome == '') {
                throw new Exception("Nome &eacute; obrigat&oacute;rio.");
            }

            ConexaoDB::getInstance();
            $conex = ConexaoDB::getConexaoDB();

            // Atualizar nome no sis_usuarios
            $sql = sprintf("UPDATE sis_usuarios SET nome_usuario = '%s' WHERE cod_usuario = %d",
                addslashes($nome), $codUsuario);
            $conex->debug = $debug;
            $conex->Execute($sql);

            // Atualizar tab_convidados
            $sqlConv = sprintf("UPDATE tab_convidados SET nome_convidado = '%s', cpf = '%s', fone_celular = '%s' WHERE cod_usuario = %d",
                addslashes($nome), addslashes($cpf), addslashes($celular), $codUsuario);
            $conex->Execute($sqlConv);

            echo json_encode(array(
                'statusMessage' => '1',
                'message' => 'Dados atualizados com sucesso.'
            ));
        } catch (Exception $e) {
            echo json_encode(array(
                'statusMessage' => '',
                'message' => $e->getMessage()
            ));
        }
        exit();
    }

    public function removerSupervisor($fws_, $debug = false) {
        ob_clean();
        try {
            $codUsuario = intval($fws_['codUsuario']);
            if (!$codUsuario) {
                throw new Exception("Usu&aacute;rio n&atilde;o informado.");
            }

            $codGrupoSupervisor = $this->getCodGrupoSupervisor($debug);
            if (!$codGrupoSupervisor) {
                throw new Exception("Grupo de supervisor n&atilde;o configurado.");
            }

            $objUG = new UsuarioGrupo();
            $objUG->setCodUsuario($codUsuario);
            $objUG->setCodGrupo($codGrupoSupervisor);
            $objUG->setCodEvento(COD_EVENTO);
            $objUG->removerUsuarioGrupo();
        } catch (OKException $e) {
            echo json_encode(array('statusMessage' => '1', 'message' => 'Supervisor removido do grupo com sucesso.'));
            exit();
        } catch (Exception $e) {
            echo json_encode(array('statusMessage' => '', 'message' => $e->getMessage()));
            exit();
        }
    }

}

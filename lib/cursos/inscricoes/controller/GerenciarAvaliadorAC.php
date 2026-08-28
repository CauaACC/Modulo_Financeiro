<?php

/**
 * GerenciarAvaliadorAC
 *
 * Cadastro/listagem/remocao de avaliadores (logins com perfil
 * SIS_AVALIACAO_INSCRITO) usados nas telas de Avaliacao por candidato
 * (GerenciarAvaliacaoInscrito.php / GerenciarAvaliacaoFase2.php).
 *
 * Espelha GerenciarSupervisorAC (modulo pedagogico) — mesma estrutura
 * de cadastrar / resetar senha / listar / editar / remover, ajustando
 * para o grupo SIS_AVALIACAO_INSCRITO em vez de SIS_PEDAGOGICO_SUPERVISOR.
 *
 * Quem pode usar: usuarios com permissao SIS_GERENCIAR_AVALIACAO_TRABALHOS
 * (coordenador/admin da avaliacao).
 */
class GerenciarAvaliadorAC {

    function __construct() {
        $cod_usuario = $_SESSION['_cod_usuario'];
        if (!FWA_Autenticacao($cod_usuario, enumSistema::SIS_GERENCIAR_AVALIACAO_TRABALHOS, 5, false, false)) {
            throw new Exception("Acesso restrito a coordenadores da avalia&ccedil;&atilde;o.");
        }
    }

    /**
     * Busca o cod_grupo que tem direito sobre SIS_AVALIACAO_INSCRITO no evento atual.
     * Retorna o cod_grupo ou false se nao encontrar.
     */
    private function getCodGrupoAvaliador($debug = false) {
        ConexaoDB::getInstance();
        $conex = ConexaoDB::getConexaoDB();

        $sql = sprintf("SELECT dgs.cod_grupo FROM sis_direitos_grupos_sistemas dgs
            WHERE dgs.cod_sistema = %s AND dgs.cod_evento = %s LIMIT 1",
            enumSistema::SIS_AVALIACAO_INSCRITO, COD_EVENTO);

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

    public function cadastrarAvaliador($fws_, $debug = false) {
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

            $codGrupoAvaliador = $this->getCodGrupoAvaliador($debug);
            if (!$codGrupoAvaliador) {
                throw new Exception("Grupo de avaliador n&atilde;o est&aacute; configurado para este evento. Configure primeiro em Gest&atilde;o de Acessos.");
            }

            $senha = $this->gerarSenhaAleatoria();

            // 1. Criar usuario (ou localizar existente)
            $objLogin = new Usuario();
            $objLogin->setLogin($login);
            $objLogin->setSenha('');
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
                    // ok
                } catch (ADODB_Exception $e) {
                    if (strpos($e->getMessage(), 'Duplicate') === false) {
                        throw $e;
                    }
                }
            }

            // 3. Associar ao grupo de avaliador
            $objUG = new UsuarioGrupo();
            $objUG->setCodUsuario($codUsuario);
            $objUG->setCodGrupo($codGrupoAvaliador);
            $objUG->setCodEvento(COD_EVENTO);

            try {
                $objUG->adicionarUsuarioGrupo();
            } catch (OKException $e) {
                // ok
            } catch (ADODB_Exception $e) {
                if (strpos($e->getMessage(), 'Duplicate') === false) {
                    throw $e;
                }
            }

            // 4. Criar ou associar convidado
            // Convidado::salvar() tambem lanca OKException no sucesso — catch local
            // garante que o passo 5 (e-mail) rode mesmo no fluxo "tudo certo".
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
                $MSG = 'Login j&aacute; existia. Usu&aacute;rio vinculado ao grupo de avaliador.';
            } else {
                $MSG = 'Avaliador cadastrado com sucesso.';
            }

            // 5. Disparar e-mail de boas-vindas com link para definir a senha.
            //    Falhas no envio NAO devem invalidar o cadastro — apenas adicionam
            //    aviso na resposta para o admin reenviar manualmente se quiser.
            $emailEnviado = false;
            $emailMsg     = '';
            try {
                $emailEnviado = $this->dispararEmailBoasVindas($codUsuario, $login, $debug);
                if ($emailEnviado) {
                    $MSG .= ' E-mail com link para definir senha enviado para ' . htmlspecialchars($login) . '.';
                } else {
                    $emailMsg = ' Aten&ccedil;&atilde;o: e-mail de boas-vindas n&atilde;o foi enviado. Use o bot&atilde;o "Resetar senha" para reenviar.';
                }
            } catch (Exception $eMail) {
                $emailMsg = ' Aten&ccedil;&atilde;o: falha ao enviar e-mail (' . $eMail->getMessage() . '). Use "Resetar senha" para reenviar.';
            }

            echo json_encode(array(
                'statusMessage'  => '1',
                'message'        => $MSG . $emailMsg,
                'codUsuario'     => $codUsuario,
                'login'          => $login,
                'loginExistente' => $loginExistente,
                'emailEnviado'   => $emailEnviado
            ));
        } catch (OKException $e) {
            echo json_encode(array(
                'statusMessage' => '1',
                'message'       => $e->getMessage(),
                'codUsuario'    => isset($codUsuario) ? $codUsuario : '',
                'login'         => isset($login) ? $login : ''
            ));
        } catch (Exception $e) {
            echo json_encode(array(
                'statusMessage' => '',
                'message'       => $e->getMessage()
            ));
        }
        exit();
    }

    /**
     * Gera token de recuperacao + envia e-mail de boas-vindas com link para o
     * usuario definir sua senha. Retorna true em sucesso, false caso o envio
     * pelo provedor falhe (ex: SMTP offline). Lanca excecao em problema de
     * dados (usuario sem e-mail valido).
     *
     * Reutilizado pelo cadastrarAvaliador.
     */
    private function dispararEmailBoasVindas($codUsuario, $login, $debug = false) {
        $objUsuario = new Usuario();
        $objUsuario->setCodUsuario($codUsuario);
        $objUsuario->setCodEvento(COD_EVENTO);
        $dados = $objUsuario->solicitarRecuperacaoSenha($login, $debug);

        if (!$dados || empty($dados['email']) || !filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("e-mail do usu&aacute;rio inv&aacute;lido");
        }

        $linkReset = WB_URL_VIEW . '?content=view/admin/login-redefinir-senha.php&token=' . $dados['token'];
        $assunto = '[' . ABREV_EVENTO_EMAIL . '] Acesso como avaliador';
        $corpo = '<p>Ol&aacute;,</p>'
            . '<p>Seu acesso como avaliador do ' . NOME_EVENTO . ' foi criado.</p>'
            . '<p><strong>Login:</strong> ' . htmlspecialchars($login) . '</p>'
            . '<p>Para definir sua senha, clique no link abaixo:</p>'
            . '<p><a href="' . $linkReset . '">' . $linkReset . '</a></p>'
            . '<p>Este link &eacute; v&aacute;lido por 1 hora.</p>'
            . '<p>' . EMAIL_CONTATO_ATT . '</p>';

        return (bool) GerenciarEmailAC::enviarEmail($dados['email'], $assunto, $corpo, false, true);
    }

    public function resetarSenha($fws_, $debug = false) {
        ob_clean();
        try {
            $codUsuario = intval($fws_['codUsuario']);
            if (!$codUsuario) {
                throw new Exception("Usu&aacute;rio n&atilde;o informado.");
            }

            $objUsuario = new Usuario();
            $objUsuario->setCodUsuario($codUsuario);
            $objUsuario->setCodEvento(COD_EVENTO);
            $objUsuario->getUsuarioById($debug);

            $login = $objUsuario->getLogin();
            if (!$login) {
                throw new Exception("Login do usu&aacute;rio n&atilde;o encontrado.");
            }

            $dados = $objUsuario->solicitarRecuperacaoSenha($login, $debug);

            if ($dados && $dados['email'] && filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
                $linkReset = WB_URL_VIEW . '?content=view/admin/login-redefinir-senha.php&token=' . $dados['token'];

                $assunto = '[' . ABREV_EVENTO_EMAIL . '] Redefini&ccedil;&atilde;o de senha';
                $corpo = '<p>Sua senha de acesso ao sistema administrativo foi resetada.</p>'
                    . '<p>Clique no link abaixo para definir uma nova senha:</p>'
                    . '<p><a href="' . $linkReset . '">' . $linkReset . '</a></p>'
                    . '<p>Este link &eacute; v&aacute;lido por 1 hora e pode ser usado apenas uma vez.</p>';

                GerenciarEmailAC::enviarEmail($dados['email'], $assunto, $corpo, false, true);

                echo json_encode(array(
                    'statusMessage' => '1',
                    'message'       => 'Link de redefini&ccedil;&atilde;o enviado para ' . htmlspecialchars($dados['email']) . '.'
                ));
            } else {
                throw new Exception("N&atilde;o foi poss&iacute;vel enviar o e-mail. Verifique se o usu&aacute;rio possui e-mail cadastrado.");
            }
        } catch (Exception $e) {
            echo json_encode(array(
                'statusMessage' => '',
                'message'       => $e->getMessage()
            ));
        }
        exit();
    }

    public function carregarTabelaAvaliadores($fws_, $debug = false) {
        try {
            $codGrupoAvaliador = $this->getCodGrupoAvaliador($debug);
            if (!$codGrupoAvaliador) {
                echo "<tr><td colspan='5'>Grupo de avaliador n&atilde;o configurado.</td></tr>";
                return;
            }

            ConexaoDB::getInstance();
            $conex = ConexaoDB::getConexaoDB();

            // Total avaliacoes feitas pelo usuario no evento atual.
            // Conta linhas de tab_trabalhos_avaliacao_inscricao onde id_inclusao = login.
            // Why: o INSERT em AvaliacaoInscritoDAO::insert grava id_inclusao = $_SESSION['_login']
            //      (o e-mail/login do avaliador), e nao popula cod_avaliador. Por isso a contagem
            //      precisa bater pelo login, nao pelo cod_usuario.
            $sql = sprintf("SELECT su.cod_usuario, su.login,
                    COALESCE(tc.nome_convidado, su.nome_usuario, su.login) as nome,
                    su.ultimo_login,
                    tc.cpf, tc.fone_celular,
                    (SELECT COUNT(*) FROM tab_trabalhos_avaliacao_inscricao tia
                        WHERE tia.id_inclusao = su.login
                          AND tia.cod_evento = %d
                          AND tia.status = 'ATIVADA') as total_avaliacoes
                FROM sis_usuarios su
                INNER JOIN sis_ligacao_usuarios_eventos lue ON su.cod_usuario = lue.cod_usuario AND lue.cod_evento = %d
                INNER JOIN sis_ligacao_usuarios_grupos lug ON su.cod_usuario = lug.cod_usuario AND lug.cod_evento = %d AND lug.cod_grupo = %d
                LEFT JOIN tab_convidados tc ON su.cod_usuario = tc.cod_usuario
                WHERE su.status = 'ATIVADA'
                ORDER BY su.cod_usuario DESC",
                COD_EVENTO, COD_EVENTO, COD_EVENTO, $codGrupoAvaliador);

            $conex->debug = $debug;
            $rs = $conex->Execute($sql);

            if (!$rs || $rs->EOF) {
                echo "<tr><td colspan='5'>Nenhum avaliador cadastrado.</td></tr>";
                return;
            }

            $podeLogarComo = FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_GERENCIAR_ADM_GERAL, 5, false, false);

            $ii = 0;
            while (!$rs->EOF) {
                $ii++;
                $codUsuario      = intval($rs->fields['cod_usuario']);
                $login           = htmlspecialchars($rs->fields['login']);
                $nome            = htmlspecialchars($rs->fields['nome']);
                $totalAvaliacoes = intval($rs->fields['total_avaliacoes']);
                $ultimoLogin     = $rs->fields['ultimo_login'];
                $cpf             = htmlspecialchars($rs->fields['cpf'] ?: '');
                $celular         = htmlspecialchars($rs->fields['fone_celular'] ?: '');
                $celularLimpo    = preg_replace('/[^0-9]/', '', $rs->fields['fone_celular'] ?: '');
                $ultimoLoginFmt  = $ultimoLogin ? date('d/m/Y H:i', strtotime($ultimoLogin)) : '<span class="text-muted">Nunca</span>';
                ?>
                <tr data-cod-usuario="<?php echo $codUsuario ?>" data-nome="<?php echo $nome ?>" data-login="<?php echo $login ?>" data-cpf="<?php echo $cpf ?>" data-celular="<?php echo $celular ?>">
                    <td><?php echo $nome ?></td>
                    <td><a href="mailto:<?php echo $login ?>" title="Enviar e-mail"><?php echo $login ?></a></td>
                    <td style="text-align:center;"><?php echo $totalAvaliacoes ?></td>
                    <td style="text-align:center;"><?php echo $ultimoLoginFmt ?></td>
                    <td class="text-nowrap" style="font-size:16px;">
                        <a href="javascript:void(0)" class="btn-editar-avaliador" title="Editar dados" style="margin-right:6px;">
                            <i class="fa fa-pencil text-info"></i>
                        </a>
                        <?php if ($celularLimpo) { ?>
                        <a href="https://wa.me/55<?php echo $celularLimpo ?>" target="_blank" title="WhatsApp: <?php echo $celular ?>" style="margin-right:6px;">
                            <i class="fa fa-whatsapp text-success"></i>
                        </a>
                        <?php } ?>
                        <?php if ($podeLogarComo) { ?>
                        <a href="<?php echo WB_URL_COMMAND ?>&c=GerenciarLogin&m=loginComoAdministrativo&login=<?php echo urlencode($login) ?>&_submit_check=1" title="Logar como este avaliador" style="margin-right:6px;">
                            <img src="resources/image/icone_preview.gif" border="0">
                        </a>
                        <?php } ?>
                        <a href="javascript:void(0)" class="btn-resetar-senha" title="Resetar senha" style="margin-right:6px;">
                            <i class="fa fa-key text-warning"></i>
                        </a>
                        <button class="btn btn-xs btn-acao-excluir btn-remover-avaliador" title="Remover do grupo de avaliador">
                            <i class="fa fa-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php
                $rs->MoveNext();
            }
            echo "<tr><td colspan='5' style='text-align:center; color:#888; font-size:12px;'>Total de avaliadores: $ii</td></tr>";
        } catch (Exception $e) {
            echo "<tr><td colspan='5'>Erro: " . $e->getMessage() . "</td></tr>";
        }
    }

    public function editarAvaliador($fws_, $debug = false) {
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

            $sql = sprintf("UPDATE sis_usuarios SET nome_usuario = '%s' WHERE cod_usuario = %d",
                addslashes($nome), $codUsuario);
            $conex->debug = $debug;
            $conex->Execute($sql);

            $sqlConv = sprintf("UPDATE tab_convidados SET nome_convidado = '%s', cpf = '%s', fone_celular = '%s' WHERE cod_usuario = %d",
                addslashes($nome), addslashes($cpf), addslashes($celular), $codUsuario);
            $conex->Execute($sqlConv);

            echo json_encode(array(
                'statusMessage' => '1',
                'message'       => 'Dados atualizados com sucesso.'
            ));
        } catch (Exception $e) {
            echo json_encode(array(
                'statusMessage' => '',
                'message'       => $e->getMessage()
            ));
        }
        exit();
    }

    public function removerAvaliador($fws_, $debug = false) {
        ob_clean();
        try {
            $codUsuario = intval($fws_['codUsuario']);
            if (!$codUsuario) {
                throw new Exception("Usu&aacute;rio n&atilde;o informado.");
            }

            $codGrupoAvaliador = $this->getCodGrupoAvaliador($debug);
            if (!$codGrupoAvaliador) {
                throw new Exception("Grupo de avaliador n&atilde;o configurado.");
            }

            $objUG = new UsuarioGrupo();
            $objUG->setCodUsuario($codUsuario);
            $objUG->setCodGrupo($codGrupoAvaliador);
            $objUG->setCodEvento(COD_EVENTO);
            $objUG->removerUsuarioGrupo();
        } catch (OKException $e) {
            echo json_encode(array('statusMessage' => '1', 'message' => 'Avaliador removido do grupo com sucesso.'));
            exit();
        } catch (Exception $e) {
            echo json_encode(array('statusMessage' => '', 'message' => $e->getMessage()));
            exit();
        }
    }

}

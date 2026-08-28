<?php
class GerenciarGestaoAcessosAC
{
    private $obj;

    function __construct()
    {
        FWA_exigirPermissao(enumSistema::SIS_GERENCIAR_ADM_GERAL);
        $this->EventoId = COD_EVENTO;
        $this->obj = new GestaoAcessos();
    }

    /* ================================================================
       Tab 1: Eventos do Gestor
       ================================================================ */
    public function carregarTabelaEventosGestor($fws_, $debug = false)
    {
        try {
            $gestor = trim($fws_['gestor']);
            if (!$gestor) {
                echo "<tr><td colspan='4'>Selecione um gestor.</td></tr>";
                return;
            }

            $this->obj->setGestor($gestor);
            $RS = $this->obj->getEventosByGestor($debug);

            if ($RS->EOF) {
                echo "<tr><td colspan='4'>Nenhum evento encontrado para este gestor.</td></tr>";
                return;
            }

            $ii = 0;
            while (!$RS->EOF) {
                $ii++;
                $codEvento     = intval($RS->fields['cod_evento']);
                $titulo        = htmlspecialchars($RS->fields['titulo']);
                $status        = $RS->fields['status'];
                $totalUsuarios = intval($RS->fields['total_usuarios']);
                $modulos       = $RS->fields['modulos'];

                $badgeStatus = ($status == 'ATIVADO')
                    ? '<span class="label label-success">Ativado</span>'
                    : '<span class="label label-default">' . htmlspecialchars($status) . '</span>';

                $modulosHtml = '<span class="text-muted"><em>Nenhum</em></span>';
                if ($modulos) {
                    $arrModulos = explode('||', $modulos);
                    $modulosHtml = '';
                    foreach ($arrModulos as $mod) {
                        $modulosHtml .= '<span class="label label-info" style="margin:1px 2px;display:inline-block;">'
                            . $mod . '</span> ';
                    }
                }
                ?>
                <tr>
                    <td><?php echo $codEvento; ?></td>
                    <td>
                        <a href="javascript:void(0);" onclick="irParaUsuariosEvento(<?php echo $codEvento; ?>)" title="Ver usu&aacute;rios deste evento" class="link-nav">
                            <?php echo $titulo; ?>
                        </a>
                        <?php echo $badgeStatus; ?>
                    </td>
                    <td style="text-align:center;">
                        <a href="javascript:void(0);" onclick="irParaUsuariosEvento(<?php echo $codEvento; ?>)" title="Ver usu&aacute;rios deste evento" class="badge badge-link">
                            <?php echo $totalUsuarios; ?>
                        </a>
                    </td>
                    <td><?php echo $modulosHtml; ?></td>
                </tr>
                <?php
                $RS->MoveNext();
            }
            echo "<tr class='total-row'><td colspan='4'>Total de eventos: $ii</td></tr>";
        } catch (Exception $e) {
            echo "<tr><td colspan='4'>Erro: " . $e->getMessage() . "</td></tr>";
        }
    }

    /* ================================================================
       Tab 2: Usuários do Gestor
       ================================================================ */
    public function carregarTabelaUsuariosGestor($fws_, $debug = false)
    {
        try {
            $gestor = trim($fws_['gestor']);
            if (!$gestor) {
                echo "<tr><td colspan='4'>Selecione um gestor.</td></tr>";
                return;
            }

            $this->obj->setGestor($gestor);
            $RS = $this->obj->getUsuariosByGestor($debug);

            if ($RS->EOF) {
                echo "<tr><td colspan='4'>Nenhum usu&aacute;rio encontrado para este gestor.</td></tr>";
                return;
            }

            $ii = 0;
            while (!$RS->EOF) {
                $ii++;
                $codUsuario = intval($RS->fields['cod_usuario']);
                $login      = htmlspecialchars($RS->fields['login']);
                $nome       = htmlspecialchars($RS->fields['nome']);
                $eventos    = $RS->fields['eventos'];

                // Badges de eventos clicáveis
                $eventosHtml = '<span class="text-muted"><em>Nenhum</em></span>';
                $totalEventos = 0;
                if ($eventos) {
                    $arrEventos = explode('||', $eventos);
                    $totalEventos = count($arrEventos);
                    $eventosHtml = '';
                    foreach ($arrEventos as $ev) {
                        $partes = explode(':', $ev, 2);
                        $codEv  = intval($partes[0]);
                        $tituloEv = htmlspecialchars($partes[1]);
                        $eventosHtml .= '<a href="javascript:void(0);" onclick="irParaUsuariosEvento(' . $codEv . ')"'
                            . ' class="label label-info link-badge" title="Ver usu&aacute;rios deste evento">'
                            . $codEv . ' - ' . $tituloEv
                            . '</a> ';
                    }
                }
                $btnLogarComo = '';
                if (@FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_GERENCIAR_ADM_GERAL, 5, false, false)) {
                    $btnLogarComo = ' <a href="javascript:void(0);" onclick="confirmarLogarComo(\'' . addslashes($login) . '\')"'
                        . ' title="Logar como este usu&aacute;rio" class="text-danger" style="margin-left:5px;">'
                        . '<i class="fa fa-sign-in"></i></a>';
                }
                ?>
                <tr>
                    <td>
                        <?php echo $btnLogarComo; ?>
                        <a href="javascript:void(0);" onclick="irParaEventosUsuario(<?php echo $codUsuario; ?>)" title="Ver eventos deste usu&aacute;rio" class="link-nav">
                            <?php echo $login; ?>
                        </a>
                    </td>
                    <td><?php echo $nome; ?></td>
                    <td style="text-align:center;">
                        <span class="badge badge-link-static"><?php echo $totalEventos; ?></span>
                    </td>
                    <td><?php echo $eventosHtml; ?></td>
                </tr>
                <?php
                $RS->MoveNext();
            }
            echo "<tr class='total-row'><td colspan='4'>Total de usu&aacute;rios: $ii</td></tr>";
        } catch (Exception $e) {
            echo "<tr><td colspan='4'>Erro: " . $e->getMessage() . "</td></tr>";
        }
    }

    /* ================================================================
       Tab 3: Usuários do Evento
       ================================================================ */
    public function carregarTabelaUsuariosEvento($fws_, $debug = false)
    {
        try {
            $codEvento = intval($fws_['cod_evento']);
            if (!$codEvento) {
                echo "<tr><td colspan='4'>Selecione um evento.</td></tr>";
                return;
            }

            $this->obj->setCodEvento($codEvento);
            $RS = $this->obj->getUsuariosByEvento($debug);

            if ($RS->EOF) {
                echo "<tr><td colspan='4'>Nenhum usu&aacute;rio neste evento.</td></tr>";
                return;
            }

            $ii = 0;
            while (!$RS->EOF) {
                $ii++;
                $codUsuario = intval($RS->fields['cod_usuario']);
                $login      = htmlspecialchars($RS->fields['login']);
                $nome       = htmlspecialchars($RS->fields['nome']);
                $gruposDet  = $RS->fields['grupos_det'];

                // Badges de grupos com × para remover
                $gruposHtml = '<span class="text-muted"><em>Nenhum</em></span>';
                if ($gruposDet) {
                    $gruposHtml = '';
                    foreach (explode('||', $gruposDet) as $g) {
                        $partes = explode(':', $g, 2);
                        $codGrupo  = intval($partes[0]);
                        $nomeGrupo = htmlspecialchars($partes[1]);
                        $gruposHtml .= '<span class="label label-primary" style="margin:1px 2px;display:inline-block;">'
                            . $nomeGrupo
                            . ' <a href="javascript:void(0);" onclick="removerGrupoUsuario(' . $codUsuario . ',' . $codGrupo . ')"'
                            . ' style="color:#fff;text-decoration:none;font-weight:bold;" title="Remover grupo">&times;</a>'
                            . '</span> ';
                    }
                }
                // Botão de gerenciar grupos no início da célula
                $btnGrupos = '<a href="javascript:void(0);" onclick="abrirModalGrupos(' . $codUsuario . ',' . $codEvento . ')"'
                    . ' class="label label-success" style="margin:1px 4px 1px 0;display:inline-block;cursor:pointer;" title="Gerenciar grupos">'
                    . '<i class="fa fa-pencil"></i></a> ';
                $gruposHtml = $btnGrupos . $gruposHtml;
                $btnLogarComo = '';
                if (@FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_GERENCIAR_ADM_GERAL, 5, false, false)) {
                    $btnLogarComo = ' <a href="javascript:void(0);" onclick="confirmarLogarComo(\'' . addslashes($login) . '\')"'
                        . ' title="Logar como este usu&aacute;rio" class="text-danger" style="margin-left:5px;">'
                        . '<i class="fa fa-sign-in"></i></a>';
                }
                ?>
                <tr>
                    <td>
                        <?php echo $btnLogarComo; ?>
                        <a href="javascript:void(0);" onclick="irParaEventosUsuario(<?php echo $codUsuario; ?>)" title="Ver eventos deste usu&aacute;rio" class="link-nav">
                            <?php echo $login; ?>
                        </a>
                    </td>
                    <td><?php echo $nome; ?></td>
                    <td><?php echo $gruposHtml; ?></td>
                    <td width="50px" style="text-align:center;">
                        <a href="javascript:void(0);" onclick="removerUsuarioEvento(<?php echo $codUsuario; ?>, <?php echo $codEvento; ?>, '<?php echo addslashes($login); ?>')" title="Remover do evento" class="text-danger">
                            <i class="fa fa-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php
                $RS->MoveNext();
            }
            echo "<tr class='total-row'><td colspan='4'>Total de usu&aacute;rios: $ii</td></tr>";
        } catch (Exception $e) {
            echo "<tr><td colspan='4'>Erro: " . $e->getMessage() . "</td></tr>";
        }
    }

    /* ================================================================
       Tab 4: Eventos do Usuário
       ================================================================ */
    public function carregarTabelaEventosUsuario($fws_, $debug = false)
    {
        try {
            $codUsuario = intval($fws_['cod_usuario']);
            if (!$codUsuario) {
                echo "<tr><td colspan='4'>Selecione um usu&aacute;rio.</td></tr>";
                return;
            }

            $this->obj->setCodUsuario($codUsuario);
            $RS = $this->obj->getEventosByUsuario($debug);

            if ($RS->EOF) {
                echo "<tr><td colspan='4'>Nenhum evento encontrado para este usu&aacute;rio.</td></tr>";
                return;
            }

            $ii = 0;
            while (!$RS->EOF) {
                $ii++;
                $codEvento  = intval($RS->fields['cod_evento']);
                $titulo     = htmlspecialchars($RS->fields['titulo']);
                $gestor     = htmlspecialchars($RS->fields['gestor']);
                $status     = $RS->fields['status'];
                $gruposDet  = $RS->fields['grupos_det'];

                $badgeStatus = ($status == 'ATIVADO')
                    ? '<span class="label label-success">Ativado</span>'
                    : '<span class="label label-default">' . htmlspecialchars($status) . '</span>';

                // Badges de grupos com × para remover
                $gruposHtml = '<span class="text-muted"><em>Nenhum</em></span>';
                if ($gruposDet) {
                    $gruposHtml = '';
                    foreach (explode('||', $gruposDet) as $g) {
                        $partes = explode(':', $g, 2);
                        $codGrupo  = intval($partes[0]);
                        $nomeGrupo = htmlspecialchars($partes[1]);
                        $gruposHtml .= '<span class="label label-primary" style="margin:1px 2px;display:inline-block;">'
                            . $nomeGrupo
                            . ' <a href="javascript:void(0);" onclick="removerGrupoUsuarioTab4(' . $codUsuario . ',' . $codGrupo . ',' . $codEvento . ')"'
                            . ' style="color:#fff;text-decoration:none;font-weight:bold;" title="Remover grupo">&times;</a>'
                            . '</span> ';
                    }
                }
                // Botão de gerenciar grupos no início da célula
                $btnGrupos = '<a href="javascript:void(0);" onclick="abrirModalGrupos(' . $codUsuario . ',' . $codEvento . ')"'
                    . ' class="label label-success" style="margin:1px 4px 1px 0;display:inline-block;cursor:pointer;" title="Gerenciar grupos">'
                    . '<i class="fa fa-pencil"></i></a> ';
                $gruposHtml = $btnGrupos . $gruposHtml;
                ?>
                <tr>
                    <td>
                        <?php echo $badgeStatus; ?>
                        <a href="javascript:void(0);" onclick="irParaUsuariosEvento(<?php echo $codEvento; ?>)" title="Ver usu&aacute;rios deste evento" class="link-nav">
                            <?php echo $codEvento . ' - ' . $titulo; ?>
                        </a>
                    </td>
                    <td><?php echo $gestor; ?></td>
                    <td><?php echo $gruposHtml; ?></td>
                    <td width="100px" style="text-align:center;">
                        <a href="javascript:void(0);" onclick="abrirModalCopiarDireitos(<?php echo $codEvento; ?>, '<?php echo addslashes($titulo); ?>')" title="Copiar direitos de outro evento" class="text-info" style="font-size:16px; padding:4px 8px;">
                            <i class="fa fa-copy"></i>
                        </a>
                        <a href="javascript:void(0);" onclick="removerUsuarioEvento(<?php echo $codUsuario; ?>, <?php echo $codEvento; ?>, '<?php echo addslashes($titulo); ?>')" title="Remover deste evento" class="text-danger" style="font-size:16px; padding:4px 8px;">
                            <i class="fa fa-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php
                $RS->MoveNext();
            }
            echo "<tr class='total-row'><td colspan='4'>Total de eventos: $ii</td></tr>";
        } catch (Exception $e) {
            echo "<tr><td colspan='4'>Erro: " . $e->getMessage() . "</td></tr>";
        }
    }

    /* ================================================================
       Ações CRUD
       ================================================================ */
    public function adicionarGrupoUsuario($fws_, $debug = false)
    {
        $ok = false;
        $MSG = '';
        try {
            $codUsuario = intval($fws_['cod_usuario']);
            $codGrupo   = intval($fws_['cod_grupo']);
            $codEvento  = intval($fws_['cod_evento']);

            if (!$codUsuario || !$codGrupo || !$codEvento) {
                throw new Exception("Preencha todos os campos.");
            }

            $obj = new UsuarioGrupo();
            $obj->setCodUsuario($codUsuario);
            $obj->setCodGrupo($codGrupo);
            $obj->setCodEvento($codEvento);
            $obj->adicionarUsuarioGrupo();
        } catch (OKException $e) {
            $ok = true;
            $MSG = $e->getMessage();
        } catch (Exception $e) {
            $MSG = $e->getMessage();
        }

        echo '{
            "statusMessage":"' . ($ok ? '1' : '') . '",
            "message":"' . addslashes($MSG) . '"
        }';
        exit();
    }

    public function removerGrupoUsuario($fws_, $debug = false)
    {
        $ok = false;
        $MSG = '';
        try {
            $codUsuario = intval($fws_['cod_usuario']);
            $codGrupo   = intval($fws_['cod_grupo']);
            $codEvento  = intval($fws_['cod_evento']);

            if (!$codUsuario || !$codGrupo || !$codEvento) {
                throw new Exception("Dados insuficientes.");
            }

            $obj = new UsuarioGrupo();
            $obj->setCodUsuario($codUsuario);
            $obj->setCodGrupo($codGrupo);
            $obj->setCodEvento($codEvento);
            $obj->removerUsuarioGrupo();
        } catch (OKException $e) {
            $ok = true;
            $MSG = $e->getMessage();
        } catch (Exception $e) {
            $MSG = $e->getMessage();
        }

        echo '{
            "statusMessage":"' . ($ok ? '1' : '') . '",
            "message":"' . addslashes($MSG) . '"
        }';
        exit();
    }

    public function removerUsuarioEvento($fws_, $debug = false)
    {
        $ok = false;
        $MSG = '';
        try {
            $codUsuario = intval($fws_['cod_usuario']);
            $codEvento  = intval($fws_['cod_evento']);

            if (!$codUsuario || !$codEvento) {
                throw new Exception("Dados insuficientes.");
            }

            $this->obj->setCodUsuario($codUsuario);
            $this->obj->setCodEvento($codEvento);
            $this->obj->removerUsuarioEvento($debug);

            $ok = true;
            $MSG = 'Usu&aacute;rio removido do evento com sucesso.';
        } catch (Exception $e) {
            $MSG = $e->getMessage();
        }

        echo '{
            "statusMessage":"' . ($ok ? '1' : '') . '",
            "message":"' . addslashes($MSG) . '"
        }';
        exit();
    }

    public function adicionarUsuarioEvento($fws_, $debug = false)
    {
        $ok = false;
        $MSG = '';
        try {
            $codUsuario = intval($fws_['cod_usuario']);
            $codEvento  = intval($fws_['cod_evento']);

            if (!$codUsuario || !$codEvento) {
                throw new Exception("Preencha todos os campos.");
            }

            $this->obj->setCodUsuario($codUsuario);
            $this->obj->setCodEvento($codEvento);
            $this->obj->adicionarUsuarioEvento($debug);

            $ok = true;
            $MSG = 'Usu&aacute;rio adicionado ao evento com sucesso.';
        } catch (Exception $e) {
            $MSG = $e->getMessage();
        }

        echo '{
            "statusMessage":"' . ($ok ? '1' : '') . '",
            "message":"' . addslashes($MSG) . '"
        }';
        exit();
    }

    public function copiarDireitosEvento($fws_, $debug = false)
    {
        $ok = false;
        $MSG = '';
        try {
            $codUsuario       = intval($fws_['cod_usuario']);
            $codEventoOrigem  = intval($fws_['cod_evento_origem']);
            $codEventoDestino = intval($fws_['cod_evento_destino']);

            if (!$codUsuario || !$codEventoOrigem || !$codEventoDestino) {
                throw new Exception("Preencha todos os campos.");
            }

            if ($codEventoOrigem == $codEventoDestino) {
                throw new Exception("Evento de origem e destino devem ser diferentes.");
            }

            $this->obj->setCodUsuario($codUsuario);
            $inseridos = $this->obj->copiarGruposEntreEventos($codEventoOrigem, $codEventoDestino, $debug);

            $ok = true;
            $MSG = $inseridos . ' grupo(s) copiado(s) com sucesso.';
        } catch (Exception $e) {
            $MSG = $e->getMessage();
        }

        echo '{
            "statusMessage":"' . ($ok ? '1' : '') . '",
            "message":"' . addslashes($MSG) . '"
        }';
        exit();
    }

    public function carregarDadosUsuario($fws_, $debug = false)
    {
        try {
            if (!@FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_GERENCIAR_ADM_GERAL, 5, false, false)) {
                echo json_encode(['sucesso' => false, 'mensagem' => 'Sem permiss&atilde;o para visualizar dados do usu&aacute;rio.']);
                exit();
            }

            $codUsuario = intval($fws_['cod_usuario']);
            if (!$codUsuario) {
                echo json_encode(['sucesso' => false, 'mensagem' => 'Usu&aacute;rio n&atilde;o informado.']);
                exit();
            }

            $objDAO = new GestaoAcessosDAO();
            $dados = $objDAO->getUsuarioById($codUsuario, $debug);

            if (!$dados) {
                echo json_encode(['sucesso' => false, 'mensagem' => 'Usu&aacute;rio n&atilde;o encontrado.']);
                exit();
            }

            echo json_encode([
                'sucesso' => true,
                'cod_usuario' => $dados['cod_usuario'],
                'nome' => $dados['nome'],
                'login' => $dados['login'],
                'status' => $dados['status'],
                'celular' => $dados['fone_celular'] ?: ''
            ]);
        } catch (Exception $e) {
            echo json_encode(['sucesso' => false, 'mensagem' => $e->getMessage()]);
        }
        exit();
    }

    public function salvarEdicaoUsuario($fws_, $debug = false)
    {
        $ok = false;
        $MSG = '';
        try {
            // Verificar permissão admin geral
            if (!@FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_GERENCIAR_ADM_GERAL, 5, false, false)) {
                throw new Exception("Sem permiss&atilde;o para editar usu&aacute;rios.");
            }

            $codUsuario = intval($fws_['cod_usuario']);
            $nome       = trim($fws_['nome']);
            $login      = trim($fws_['login']);
            $status     = trim($fws_['status']);
            $celular    = trim(isset($fws_['celular']) ? $fws_['celular'] : '');

            if (!$codUsuario || !$login) {
                throw new Exception("Login &eacute; obrigat&oacute;rio.");
            }

            if (!in_array($status, ['ATIVADO', 'INATIVO'])) {
                throw new Exception("Situa&ccedil;&atilde;o inv&aacute;lida.");
            }

            $objDAO = new GestaoAcessosDAO();
            $objDAO->atualizarUsuario($codUsuario, $nome, $login, $status, $celular, $debug);

            $ok = true;
            $MSG = 'Usu&aacute;rio atualizado com sucesso.';
        } catch (Exception $e) {
            $MSG = $e->getMessage();
        }

        echo '{
            "statusMessage":"' . ($ok ? '1' : '') . '",
            "message":"' . addslashes($MSG) . '"
        }';
        exit();
    }

    /* ================================================================
       Trocar Senha do usuario selecionado (somente admin geral)

       Seguranca:
         - Requer SIS_GERENCIAR_ADM_GERAL server-side (gate da view nao basta)
         - Bloqueia troca da propria senha (use o fluxo de troca pessoal)
         - Se o alvo tambem eh admin geral, exige confirmacao extra
           (campo confirmarAdminGeral=1) — protecao contra escalada/erro
         - Senha gerada com SenhaHelper::gerarSenhaAleatoria (CSPRNG)
         - Hash bcrypt via SenhaHelper::hash
         - atualizarSenhaHash gira session_token (invalida sessoes ativas
           do alvo), limpa tentativas/bloqueio
         - forcar_troca_senha=1 obriga troca no proximo login
         - Senha retornada UMA UNICA VEZ no JSON (sem cache)
         - Log de auditoria via executarLog
       ================================================================ */
    public function trocarSenhaUsuario($fws_, $debug = false)
    {
        ob_clean();
        $ok = false;
        $MSG = '';
        $senha = '';
        $requerConfirmacao = false;

        try {
            // 1. Auth server-side
            if (!@FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_GERENCIAR_ADM_GERAL, 5, false, false)) {
                throw new Exception("Sem permiss&atilde;o para trocar senhas de usu&aacute;rios.");
            }

            $codUsuarioAlvo = intval($fws_['codUsuario'] ?? 0);
            $codUsuarioAdm  = intval($_SESSION['_cod_usuario']);
            $confirmarAG    = !empty($fws_['confirmarAdminGeral']);

            if ($codUsuarioAlvo <= 0) {
                throw new Exception("Usu&aacute;rio n&atilde;o informado.");
            }
            if ($codUsuarioAlvo === $codUsuarioAdm) {
                throw new Exception("Voc&ecirc; n&atilde;o pode trocar a pr&oacute;pria senha por esta tela. Use a tela de troca de senha pessoal.");
            }

            // 2. Carregar dados do alvo SEM filtrar por COD_EVENTO
            // (admin geral troca senha de qualquer usuario, mesmo sem vinculo
            // ao evento corrente). Pegamos o login via GestaoAcessosDAO::getUsuarioById
            // (busca direta em sis_usuarios) e depois carregamos o Usuario por login.
            $dadosBase = (new GestaoAcessosDAO())->getUsuarioById($codUsuarioAlvo, $debug);
            if (!$dadosBase || empty($dadosBase['login'])) {
                throw new Exception("Usu&aacute;rio n&atilde;o encontrado.");
            }
            $objAlvo = new Usuario();
            $objAlvo->setLogin($dadosBase['login']);
            $objAlvo->getUsuarioByLogin($debug);
            if (!$objAlvo->getCodUsuario()) {
                throw new Exception("Usu&aacute;rio n&atilde;o encontrado (login).");
            }

            // 3. Confirmacao extra se alvo tambem eh admin geral
            // (FWA_Autenticacao com cod_evento='TODOS' = checagem global, equivalente a FWA_AutenticacaoGeral)
            $alvoEhAdminGeral = @FWA_Autenticacao($codUsuarioAlvo, enumSistema::SIS_GERENCIAR_ADM_GERAL, 5, false, false, 'TODOS');
            if ($alvoEhAdminGeral && !$confirmarAG) {
                $requerConfirmacao = true;
                throw new Exception("O usu&aacute;rio selecionado tamb&eacute;m &eacute; administrador geral. Confirme explicitamente esta opera&ccedil;&atilde;o.");
            }

            // 4. Gerar senha forte e aplicar
            $senha = SenhaHelper::gerarSenhaAleatoria(12);
            $objAlvo->trocarSenhaAdmin($senha, $debug);

            // 5. Log de auditoria (sem registrar a senha — apenas a operacao)
            $detalhe = sprintf(
                "Admin %s (cod_usuario=%d) trocou a senha de %s (cod_usuario=%d) | IP=%s | UA=%s",
                $_SESSION['_login'] ?? '?',
                $codUsuarioAdm,
                $objAlvo->getLogin(),
                $codUsuarioAlvo,
                $_SERVER['REMOTE_ADDR'] ?? '?',
                substr($_SERVER['HTTP_USER_AGENT'] ?? '?', 0, 200)
            );
            if (function_exists('executarLog')) {
                @executarLog($detalhe, 'trocar-senha-admin', true);
            }

            $ok = true;
            $MSG = 'Senha trocada com sucesso. O usu&aacute;rio ser&aacute; obrigado a redefini-la no pr&oacute;ximo login.';
        } catch (Exception $e) {
            $ok = false;
            $MSG = $e->getMessage();
        }

        // Resposta SEM cache (a senha vai aqui, uma vez)
        header('Content-Type: application/json; charset=UTF-8');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo json_encode(array(
            'statusMessage'     => $ok ? '1' : '',
            'message'           => $MSG,
            'senha'             => $ok ? $senha : '',
            'login'             => $ok && isset($objAlvo) ? $objAlvo->getLogin() : '',
            'requerConfirmacao' => $requerConfirmacao,
        ));
        exit();
    }

    /* ================================================================
       Cadastrar Login (mesmo fluxo de login-cadastro-incluir-grava)
       ================================================================ */
    public function cadastrarLogin($fws_, $debug = false)
    {
        ob_clean();
        $ok = false;
        $MSG = '';
        $codUsuarioCriado = 0;
        try {
            $login = trim($fws_['login']);
            $nome  = trim($fws_['nome']);

            if ($login == '') {
                throw new Exception("Login &eacute; obrigat&oacute;rio.");
            }

            $objLogin = new Usuario();
            $objLogin->setLogin($login);
            $objLogin->setNome($nome);
            $objLogin->getUsuarioByLogin($debug);

            $loginExistente = false;
            if ($objLogin->getCodUsuario() == '') {
                $senha = SenhaHelper::gerarSenhaAleatoria(10);
                $objLogin->setSenha('');
                $objLogin->setSenhaHash(SenhaHelper::hash($senha));
                $objLogin->setForcarTrocaSenha(1);
                $objLogin->salvar($debug);
                $MSG = 'Login cadastrado com sucesso.';
            } else {
                $loginExistente = true;
                $MSG = 'Login j&aacute; existente, vinculado ao evento.';
            }

            $codUsuarioCriado = $objLogin->getCodUsuario();

            // Associar ao evento atual
            $objUE = new UsuarioEvento();
            $objUE->setCodEvento(COD_EVENTO);
            $objUE->setCodUsuario($objLogin->getCodUsuario());
            $objUE->getUsuarioEventoById($debug);

            if ($objUE->getStatus() != 'ATIVADA') {
                try {
                    $objUE->adicionarUsuarioEvento($debug);
                } catch (OKException $e) {
                    // ok - sucesso via OKException
                }
            }

            // Criar ou associar convidado (dados pessoais)
            $objConv = new Convidado();
            $objConv->setEMail($login);
            if ($objConv->getConvidadoByEmail($debug)) {
                if ($objConv->getCodUsuario() == '' || $objConv->getCodUsuario() == '0') {
                    $objConv->setCodUsuario($objLogin->getCodUsuario());
                    $objConv->salvar($debug);
                }
            } else {
                $objConv->setNomeConvidado($nome);
                $objConv->setCodUsuario($objLogin->getCodUsuario());
                $objConv->salvar($debug);
            }

            $ok = true;
        } catch (OKException $e) {
            $ok = true;
            $MSG = $e->getMessage();
            if (isset($objLogin) && $objLogin->getCodUsuario()) {
                $codUsuarioCriado = $objLogin->getCodUsuario();
            }
        } catch (Exception $e) {
            $MSG = $e->getMessage();
        }

        echo json_encode(array(
            'statusMessage' => $ok ? '1' : '',
            'message' => $MSG,
            'cod_usuario' => $codUsuarioCriado
        ));
        exit();
    }

    /* ================================================================
       Modal: Checkboxes de grupos do evento para o usuario
       ================================================================ */
    public function carregarCheckboxGruposEvento($fws_, $debug = false)
    {
        try {
            $codUsuario = intval($fws_['cod_usuario']);
            $codEvento  = intval($fws_['cod_evento']);

            if (!$codUsuario || !$codEvento) {
                echo '<p>Dados insuficientes.</p>';
                return;
            }

            // Grupos que o usuario ja possui neste evento
            $this->obj->setCodUsuario($codUsuario);
            $this->obj->setCodEvento($codEvento);
            $RS = $this->obj->getGruposUsuarioEvento($debug);
            $gruposUsuario = array();
            while (!$RS->EOF) {
            $gruposUsuario[] = intval($RS->fields['cod_grupo']);
                $RS->MoveNext();
            }

            // Todos os grupos disponiveis (já ordenados por categoria, ordem)
            $RS = $this->obj->getTodosGrupos($debug);

            if ($RS->EOF) {
                echo '<p>Nenhum grupo cadastrado.</p>';
                return;
            }

            $categoriaAtual = '';
            while (!$RS->EOF) {
                $codGrupo  = intval($RS->fields['cod_grupo']);
                $nomeGrupo = htmlspecialchars($RS->fields['nome_grupo']);
                $categoria = htmlspecialchars($RS->fields['categoria']);
                $checked   = in_array($codGrupo, $gruposUsuario) ? ' checked' : '';

                if ($categoria !== $categoriaAtual) {
                    if ($categoriaAtual !== '') {
                        echo '</div></div>';
                    }
                    $categoriaAtual = $categoria;
                    echo '<div style="margin-bottom:8px;">'
                        . '<strong style="font-size:12px;color:#777;text-transform:uppercase;">' . $categoria . '</strong>'
                        . '<div style="display:flex;flex-wrap:wrap;">';
                }

                echo '<div class="checkbox" style="margin:2px 0;width:50%;">'
                    . '<label>'
                    . '<input type="checkbox"' . $checked
                    . ' onchange="alternarGrupoEvento(' . $codUsuario . ',' . $codGrupo . ',' . $codEvento . ',this)">'
                    . ' ' . strtoupper($nomeGrupo)
                    . '</label>'
                    . '</div>';

                $RS->MoveNext();
            }
            if ($categoriaAtual !== '') {
                echo '</div></div>';
            }
        } catch (Exception $e) {
            echo '<p>Erro: ' . $e->getMessage() . '</p>';
        }
    }

    /* ================================================================
       Associar Inscrito
       ================================================================ */
    public function buscarInscritoParaAssociar($fws_, $debug = false)
    {
        $ok = false;
        $dados = array();
        try {
            $codUsuario = intval($fws_['cod_usuario']);
            $codEvento  = intval($fws_['cod_evento']);
            if (!$codUsuario || !$codEvento) throw new Exception("Usuário e evento são obrigatórios.");

            $rsAssoc = $this->obj->getInscritoAssociado($codUsuario, $codEvento, $debug);
            if ($rsAssoc && !$rsAssoc->EOF && $rsAssoc->fields['cod_inscrito']) {
                $dados['jaAssociado'] = true;
                $dados['codInscrito'] = $rsAssoc->fields['cod_inscrito'];
                $dados['nomeInscrito'] = $rsAssoc->fields['nome_inscrito'];
                $dados['emailInscrito'] = $rsAssoc->fields['e_mail'];
            } else {
                $dados['jaAssociado'] = false;
                $dados['inscritoEncontrado'] = false;

                // Usar email/cpf do POST (vem do formulário do modal)
                $email = trim($fws_['email'] ?? '');
                $cpf   = preg_replace('/\D/', '', trim($fws_['cpf'] ?? ''));

                // Se não veio do POST, tentar buscar do cadastro do usuário
                if (!$email && !$cpf) {
                    $rsUsr = $this->obj->getDadosUsuarioParaInscricao($codUsuario, $debug);
                    if ($rsUsr && !$rsUsr->EOF) {
                        $email = $rsUsr->fields['email'] ?: '';
                        $cpf   = $rsUsr->fields['cpf'] ?: '';
                    }
                }

                if ($email || $cpf) {
                    $rsIns = $this->obj->buscarInscritoPorEmailOuCpf($email, $cpf, $codEvento, $debug);
                    if ($rsIns && !$rsIns->EOF) {
                        $dados['inscritoEncontrado'] = true;
                        $dados['codInscrito'] = $rsIns->fields['cod_inscrito'];
                        $dados['nomeInscrito'] = $rsIns->fields['nome_inscrito'];
                        $dados['emailInscrito'] = $rsIns->fields['e_mail'];
                        $dados['perfilInscrito'] = $rsIns->fields['nome_perfil'];
                    }
                }
            }
            $ok = true;
        } catch (Exception $e) {
            $dados['message'] = $e->getMessage();
        }
        $dados['statusMessage'] = $ok ? '1' : '';
        echo json_encode($dados);
        exit();
    }

    public function associarInscrito($fws_, $debug = false)
    {
        $ok = false;
        $MSG = '';
        try {
            $codUsuario  = intval($fws_['cod_usuario']);
            $codEvento   = intval($fws_['cod_evento']);
            $codInscrito = intval($fws_['cod_inscrito']);
            $codPerfil   = intval($fws_['cod_perfil']);
            $criarNovo   = $fws_['criar_novo'] == '1';

            if (!$codUsuario || !$codEvento) throw new Exception("Usuário e evento são obrigatórios.");

            if ($criarNovo) {
                $nome  = trim($fws_['nome']);
                $email = trim($fws_['email']);
                $cpf   = preg_replace('/\D/', '', trim($fws_['cpf']));
                if (!$nome)  throw new Exception("Nome é obrigatório.");
                if (!$email) throw new Exception("Email é obrigatório.");
                if (!$codPerfil) throw new Exception("Perfil é obrigatório.");

                if ($cpf) {
                    $rsCheck = $this->obj->buscarInscritoPorEmailOuCpf('', $cpf, $codEvento, $debug);
                    if ($rsCheck && !$rsCheck->EOF) {
                        throw new Exception("Já existe um inscrito com este CPF no evento (cód: " . $rsCheck->fields['cod_inscrito'] . ").");
                    }
                }

                $codInscrito = $this->obj->criarInscrito($codEvento, $nome, $email, $cpf, $codPerfil, $_SESSION['_cod_usuario'], $debug);
                if (!$codInscrito) throw new Exception("Erro ao criar inscrição.");
                $MSG = "Inscrição criada (cód: $codInscrito) e associada ao usuário.";
            } else {
                if (!$codInscrito) throw new Exception("Código do inscrito não informado.");
                $MSG = "Inscrito (cód: $codInscrito) associado ao usuário.";
            }

            $this->obj->atualizarCodInscrito($codUsuario, $codEvento, $codInscrito, $debug);
            $ok = true;
        } catch (Exception $e) {
            $MSG = $e->getMessage();
        }

        echo json_encode(array('statusMessage' => $ok ? '1' : '', 'message' => $MSG));
        exit();
    }

    public function desassociarInscrito($fws_, $debug = false)
    {
        $ok = false;
        $MSG = '';
        try {
            $codUsuario = intval($fws_['cod_usuario']);
            $codEvento  = intval($fws_['cod_evento']);
            if (!$codUsuario || !$codEvento) throw new Exception("Usuário e evento são obrigatórios.");

            $this->obj->removerCodInscrito($codUsuario, $codEvento, $debug);
            $ok = true;
            $MSG = "Associação removida.";
        } catch (Exception $e) {
            $MSG = $e->getMessage();
        }

        echo json_encode(array('statusMessage' => $ok ? '1' : '', 'message' => $MSG));
        exit();
    }

    /* ================================================================
       Selects auxiliares
       ================================================================ */
    public function carregarSelectGestores($fws_, $debug = false)
    {
        try {
            $RS = $this->obj->getGestoresDistintos($debug);

            echo '<option value="">Selecione um gestor</option>';
            while (!$RS->EOF) {
                $gestor = $RS->fields['gestor'];
                echo '<option value="' . htmlspecialchars($gestor) . '">'
                    . htmlspecialchars($gestor)
                    . '</option>';
                $RS->MoveNext();
            }
        } catch (Exception $e) {
            echo '<option value="">Erro ao carregar gestores</option>';
        }
    }

    public function carregarSelectEventos($fws_, $debug = false)
    {
        try {
            $RS = $this->obj->getEventosAtivos($debug);

            echo '<option value="">Selecione um evento</option>';
            while (!$RS->EOF) {
                $selected = ($RS->fields['cod_evento'] == COD_EVENTO) ? ' selected' : '';
                echo '<option value="' . $RS->fields['cod_evento'] . '"' . $selected . '>'
                    . $RS->fields['cod_evento'] . ' - ' . htmlspecialchars($RS->fields['titulo'])
                    . '</option>';
                $RS->MoveNext();
            }
        } catch (Exception $e) {
            echo '<option value="">Erro ao carregar eventos</option>';
        }
    }

    public function carregarSelectUsuarios($fws_, $debug = false)
    {
        try {
            $RS = $this->obj->getTodosUsuarios($debug);

            echo '<option value="">Selecione um usu&aacute;rio</option>';
            while (!$RS->EOF) {
                $nome = htmlspecialchars($RS->fields['nome']);
                $login = htmlspecialchars($RS->fields['login']);
                echo '<option value="' . $RS->fields['cod_usuario'] . '" data-login="' . $login . '">'
                    . $login . ' (' . $nome . ')'
                    . '</option>';
                $RS->MoveNext();
            }
        } catch (Exception $e) {
            echo '<option value="">Erro ao carregar usu&aacute;rios</option>';
        }
    }

    public function carregarSelectGrupos($fws_, $debug = false)
    {
        try {
            $RS = $this->obj->getTodosGrupos($debug);

            echo '<option value="">Selecione um grupo</option>';
            while (!$RS->EOF) {
                echo '<option value="' . $RS->fields['cod_grupo'] . '">'
                    . htmlspecialchars($RS->fields['nome_grupo'])
                    . '</option>';
                $RS->MoveNext();
            }
        } catch (Exception $e) {
            echo '<option value="">Erro ao carregar grupos</option>';
        }
    }

    public function carregarSelectEventosDisponiveisUsuario($fws_, $debug = false)
    {
        try {
            $codUsuario = intval($fws_['cod_usuario']);
            $this->obj->setCodUsuario($codUsuario);
            $RS = $this->obj->getEventosDisponiveisUsuario($debug);

            echo '<option value="">Selecione um evento</option>';
            while (!$RS->EOF) {
                echo '<option value="' . $RS->fields['cod_evento'] . '">'
                    . $RS->fields['cod_evento'] . ' - ' . htmlspecialchars($RS->fields['titulo'])
                    . '</option>';
                $RS->MoveNext();
            }
        } catch (Exception $e) {
            echo '<option value="">Erro</option>';
        }
    }

    public function enviarEmailLogin($fws_, $debug = false)
    {
        ob_clean();
        try {
            $login = trim($fws_['login']);
            $codUsuario = intval($fws_['cod_usuario']);
            if (!$login || !$codUsuario) {
                throw new Exception("Login ou usu&aacute;rio n&atilde;o informado.");
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

    /* ================================================================
       Logar como usuario
       ================================================================ */
    public function logarComoUsuario($fws_, $debug = false)
    {
        $loginAlvo = trim(isset($fws_['login']) ? $fws_['login'] : '');
        $MSG = '';
        try {
            if ($loginAlvo == '') {
                throw new Exception("Login n&atilde;o informado.");
            }

            // Verificar se usuario existe e esta ativo
            ConexaoDB::getInstance();
            $conex = ConexaoDB::getConexaoDB();

            $sql = sprintf(
                "SELECT su.cod_usuario, IFNULL(su.nome_usuario, tc.nome_convidado) as nome_convidado
                 FROM sis_usuarios su
                 LEFT JOIN tab_convidados tc ON su.cod_usuario = tc.cod_usuario
                 WHERE su.status = 'ATIVADA' AND su.login = '%s'",
                addslashes($loginAlvo)
            );

            $conex->debug = $debug;
            $RS = $conex->Execute($sql);

            if (!$RS || $RS->EOF) {
                throw new Exception("Usu&aacute;rio n&atilde;o encontrado ou inativo.");
            }

            // Registrar log
            try {
                $objUsuarioDAO = new UsuarioDAO();
                $objUsuarioDAO->registrarLogLogin(
                    $_SESSION['userLogado']['_cod_usuario'],
                    $_SESSION['userLogado']['_login'],
                    true,
                    'logar_como_usuario:' . $loginAlvo
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

    public function importarUsuariosEvento($fws_, $debug = false)
    {
        $ok = false;
        $MSG = '';
        try {
            FWA_AutenticacaoGeral($_SESSION['_cod_usuario'], enumSistema::SIS_GERENCIAR_ADM_GERAL);

            $codEventoOrigem  = intval($fws_['cod_evento_origem']);
            $codEventoDestino = intval($fws_['cod_evento_destino']);

            if (!$codEventoOrigem || !$codEventoDestino) {
                throw new Exception("Selecione o evento de origem e o evento de destino.");
            }
            if ($codEventoOrigem == $codEventoDestino) {
                throw new Exception("Evento de origem e destino devem ser diferentes.");
            }

            $resultado = $this->obj->importarUsuariosDeEvento($codEventoOrigem, $codEventoDestino, $debug);

            $ok = true;
            $MSG = $resultado['usuarios'] . ' usu&aacute;rio(s) importado(s) e ' . $resultado['grupos'] . ' grupo(s) copiado(s) com sucesso.';
        } catch (Exception $e) {
            $MSG = $e->getMessage();
        }

        echo json_encode(array(
            'statusMessage' => $ok ? '1' : '',
            'message' => $MSG
        ));
        exit();
    }

    public function carregarSelectUsuariosEvento($fws_, $debug = false)
    {
        try {
            $codEvento = intval($fws_['cod_evento']);
            $this->obj->setCodEvento($codEvento);
            $RS = $this->obj->getUsuariosDoEvento($debug);

            echo '<option value="">Selecione um usu&aacute;rio</option>';
            while (!$RS->EOF) {
                echo '<option value="' . $RS->fields['cod_usuario'] . '">'
                    . htmlspecialchars($RS->fields['login'])
                    . '</option>';
                $RS->MoveNext();
            }
        } catch (Exception $e) {
            echo '<option value="">Erro</option>';
        }
    }
}

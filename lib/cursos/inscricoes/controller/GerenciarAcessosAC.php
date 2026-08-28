<?php
class GerenciarAcessosAC
{
    function __construct()
    {
        FWA_exigirPermissao(enumSistema::SIS_GERENCIAR_ADM_GERAL);
        $this->EventoId = COD_EVENTO;
    }

    public function carregarTabelaUsuarios($fws_, $debug = false)
    {
        try {
            $Conexao_db = FWA_Conexao("OpcTec");

            $var_sql = "
                SELECT su.cod_usuario, su.login, tc.nome_convidado, tc.cod_convidado,
                    GROUP_CONCAT(DISTINCT sg.nome_grupo ORDER BY sg.nome_grupo SEPARATOR ',') as grupos,
                    GROUP_CONCAT(DISTINCT sg.cod_grupo ORDER BY sg.nome_grupo SEPARATOR ',') as cod_grupos
                FROM sis_usuarios su
                INNER JOIN sis_ligacao_usuarios_eventos le ON su.cod_usuario = le.cod_usuario
                LEFT JOIN tab_convidados tc ON su.cod_usuario = tc.cod_usuario
                LEFT JOIN sis_ligacao_usuarios_grupos lug ON lug.cod_usuario = su.cod_usuario AND lug.cod_evento = le.cod_evento
                LEFT JOIN sis_grupos sg ON sg.cod_grupo = lug.cod_grupo AND sg.cod_grupo <> 999
                WHERE le.cod_evento = " . intval(COD_EVENTO) . "
                GROUP BY su.cod_usuario
                ORDER BY su.login
            ";

            $RS = $Conexao_db->Execute($var_sql);

            if ($RS->EOF) {
                echo "<tr><td colspan='4'>Nenhum usuário cadastrado neste evento.</td></tr>";
                return;
            }

            $ii = 0;
            while (!$RS->EOF) {
                $ii++;
                $cod_usuario    = $RS->fields['cod_usuario'];
                $login          = $RS->fields['login'];
                $nome_convidado = $RS->fields['nome_convidado'];
                $cod_convidado  = $RS->fields['cod_convidado'];
                $grupos         = $RS->fields['grupos'];
                $cod_grupos     = $RS->fields['cod_grupos'];

                // Montar badges de grupos
                $badgesHtml = '';
                if ($grupos) {
                    $arrGrupos    = explode(',', $grupos);
                    $arrCodGrupos = explode(',', $cod_grupos);
                    for ($i = 0; $i < count($arrGrupos); $i++) {
                        $nomeG = trim($arrGrupos[$i]);
                        $codG  = trim($arrCodGrupos[$i]);
                        $badgesHtml .= '<span class="label label-info" style="margin-right:4px;display:inline-block;margin-bottom:2px;">'
                            . htmlspecialchars($nomeG)
                            . ' <a href="javascript:void(0);" onclick="removerGrupo(' . intval($cod_usuario) . ',' . intval($codG) . ')" '
                            . 'style="color:#fff;text-decoration:none;font-weight:bold;" title="Remover grupo">&times;</a>'
                            . '</span> ';
                    }
                } else {
                    $badgesHtml = '<span class="text-muted"><em>Nenhum grupo</em></span>';
                }

                echo '<tr id="usuario-' . intval($cod_usuario) . '">';
                echo '<td width="120px">';

                // Ação: Editar dados pessoais
                if ($cod_convidado) {
                    echo '<a href="index.php?content=view/admin/convidados-cadastro-ok.php&COD_CONVIDADO=' . intval($cod_convidado) . '" title="Editar dados pessoais">'
                        . '<i class="fa fa-pencil"></i></a> ';
                }

                // Ação: Excluir do evento
                echo '<a href="javascript:void(0);" onclick="excluirUsuario(' . intval($cod_usuario) . ',\'' . htmlspecialchars(addslashes($login)) . '\')" title="Remover do evento">'
                    . '<i class="fa fa-trash"></i></a> ';

                // Ação: Enviar email com senha
                if ($cod_convidado) {
                    echo '<a href="javascript:void(0);" onclick="enviarEmail(' . intval($cod_convidado) . ')" title="Enviar senha por e-mail">'
                        . '<i class="fa fa-envelope"></i></a>';
                }

                echo '</td>';
                echo '<td>' . htmlspecialchars($login) . '</td>';
                echo '<td>' . htmlspecialchars(textoMaiusculoMinusculo($nome_convidado)) . '</td>';
                echo '<td>' . $badgesHtml . '</td>';
                echo '</tr>';

                $RS->MoveNext();
            }
            echo '<tr><td colspan="4">Total de usuários: ' . $ii . '</td></tr>';
        } catch (Exception $e) {
            echo '<tr><td colspan="4">' . ($e->getMessage() ? $e->getMessage() : 'Erro ao carregar usuários.') . '</td></tr>';
        }
    }

    public function carregarTabelaGrupos($fws_, $debug = false)
    {
        try {
            $Conexao_db = FWA_Conexao("OpcTec");

            $var_sql = "
                SELECT sg.cod_grupo, sg.nome_grupo,
                    GROUP_CONCAT(DISTINCT ss.nome_sistema SEPARATOR ', ') as sistemas,
                    (SELECT COUNT(*) FROM sis_ligacao_usuarios_grupos lug
                     WHERE lug.cod_grupo = sg.cod_grupo AND lug.cod_evento = " . intval(COD_EVENTO) . ") as qtd
                FROM sis_grupos sg
                LEFT JOIN sis_direitos_grupos_sistemas dgs ON dgs.cod_grupo = sg.cod_grupo AND dgs.cod_evento = " . intval(COD_EVENTO) . "
                LEFT JOIN sis_sistemas ss ON ss.cod_sistema = dgs.cod_sistema
                WHERE sg.cod_grupo <> 999
                GROUP BY sg.cod_grupo
                ORDER BY sg.nome_grupo
            ";

            $RS = $Conexao_db->Execute($var_sql);

            if ($RS->EOF) {
                echo "<tr><td colspan='3'>Nenhum grupo encontrado.</td></tr>";
                return;
            }

            while (!$RS->EOF) {
                $cod_grupo   = $RS->fields['cod_grupo'];
                $nome_grupo  = $RS->fields['nome_grupo'];
                $sistemas    = $RS->fields['sistemas'];
                $qtd         = $RS->fields['qtd'];

                echo '<tr>';
                echo '<td>' . htmlspecialchars($nome_grupo) . '</td>';
                echo '<td>' . ($sistemas ? htmlspecialchars($sistemas) : '<em class="text-muted">Nenhum módulo</em>') . '</td>';
                echo '<td>' . intval($qtd) . '</td>';
                echo '</tr>';

                $RS->MoveNext();
            }
        } catch (Exception $e) {
            echo '<tr><td colspan="3">' . ($e->getMessage() ? $e->getMessage() : 'Erro ao carregar grupos.') . '</td></tr>';
        }
    }

    public function salvarLogin($fws_, $debug = false)
    {
        $ok = false;
        $MSG = '';
        try {
            @$fws_['data_alteracao']  = date("YmdHi");
            @$fws_['data_inclusao']   = date("YmdHi");
            @$fws_['status']          = "ATIVADO";
            @$fws_['session_id']      = session_id();
            @$fws_['http_user_agent'] = $_SERVER["HTTP_USER_AGENT"];
            @$fws_['ip_cliente']      = $_SERVER["REMOTE_ADDR"];

            $objLogin = new Usuario();
            $objLogin->setLogin($fws_['login']);
            $objLogin->setSenha($fws_['senha']);
            $objLogin->setNome($fws_['nome']);
            $objLogin->getUsuarioByLogin($debug);

            if ($objLogin->getCodUsuario() == '') {
                $objLogin->salvar($debug);
            } else {
                throw new OKException('Login já cadastrado');
            }
        } catch (OKException $exc) {
            // Verificar se usuario já está associado ao evento
            $objUE = new UsuarioEvento();
            $objUE->setCodEvento(COD_EVENTO);
            $objUE->setCodUsuario($objLogin->getCodUsuario());
            $objUE->getUsuarioEventoById($debug);

            if ($objUE->getStatus() == 'ATIVADA') {
                $MSG .= 'Login ' . $objLogin->getLogin() . ' já está associado ao evento.';
                $ok = true;
            } else {
                try {
                    $objUE->adicionarUsuarioEvento($debug);
                } catch (OKException $exc2) {
                    // Adicionado com sucesso
                }
            }

            // Criar ou associar convidado
            try {
                $objConv = new Convidado();
                $objConv->setEMail($objLogin->getLogin());

                if ($objConv->getConvidadoByEmail($debug)) {
                    if ($objConv->getCodUsuario() == '' || $objConv->getCodUsuario() == '0') {
                        $objConv->setCodUsuario($objLogin->getCodUsuario());
                        $objConv->salvar($debug);
                    }
                } else {
                    $objConv->setNomeConvidado($objLogin->getNome());
                    $objConv->setCodUsuario($objLogin->getCodUsuario());
                    $objConv->salvar($debug);
                }
                $ok = true;
            } catch (OKException $exc3) {
                $ok = true;
                $MSG .= $exc3->getMessage();
            } catch (Exception $exc3) {
                $MSG .= $exc3->getMessage();
            }
        } catch (Exception $exc) {
            $MSG .= $exc->getMessage();
        }

        echo '{
            "statusMessage":"' . ($ok ? '1' : '') . '",
            "message":"' . addslashes($MSG) . '"
        }';
    }

    public function adicionarGrupo($fws_, $debug = false)
    {
        $ok = false;
        $MSG = '';
        try {
            $obj = new UsuarioGrupo();
            $obj->setCodUsuario($fws_['cod_usuario']);
            $obj->setCodGrupo($fws_['cod_grupo']);
            $obj->setCodEvento(COD_EVENTO);
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
    }

    public function removerGrupo($fws_, $debug = false)
    {
        $ok = false;
        $MSG = '';
        try {
            $obj = new UsuarioGrupo();
            $obj->setCodUsuario($fws_['cod_usuario']);
            $obj->setCodGrupo($fws_['cod_grupo']);
            $obj->setCodEvento(COD_EVENTO);
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
    }

    public function excluirUsuario($fws_, $debug = false)
    {
        $ok = false;
        $MSG = '';
        try {
            $Conexao_db = FWA_Conexao("OpcTec");
            $cod_usuario = intval($fws_['cod_usuario']);
            $cod_evento  = intval(COD_EVENTO);

            // Remover de todos os grupos do evento
            $sql = "DELETE FROM sis_ligacao_usuarios_grupos WHERE cod_usuario = " . $cod_usuario . " AND cod_evento = " . $cod_evento;
            $Conexao_db->Execute($sql);

            // Remover do evento
            $sql = "DELETE FROM sis_ligacao_usuarios_eventos WHERE cod_usuario = " . $cod_usuario . " AND cod_evento = " . $cod_evento;
            $Conexao_db->Execute($sql);

            $ok = true;
            $MSG = 'Usuário removido do evento com sucesso.';
        } catch (Exception $e) {
            $MSG = $e->getMessage();
        }

        echo '{
            "statusMessage":"' . ($ok ? '1' : '') . '",
            "message":"' . addslashes($MSG) . '"
        }';
    }

    public function carregarSelectUsuarios($fws_, $debug = false)
    {
        $Conexao_db = FWA_Conexao("OpcTec");
        $var_sql = "
            SELECT u.cod_usuario, u.login
            FROM sis_usuarios u
            INNER JOIN sis_ligacao_usuarios_eventos lue ON lue.cod_usuario = u.cod_usuario
            WHERE lue.cod_evento = " . intval(COD_EVENTO) . "
            ORDER BY u.login
        ";
        $RS = $Conexao_db->Execute($var_sql);

        echo '<option value="">Selecione um Login</option>';
        while (!$RS->EOF) {
            echo '<option value="' . $RS->fields['cod_usuario'] . '">' . htmlspecialchars($RS->fields['login']) . '</option>';
            $RS->MoveNext();
        }
    }

    public function carregarSelectGrupos($fws_, $debug = false)
    {
        $Conexao_db = FWA_Conexao("OpcTec");
        $var_sql = "
            SELECT cod_grupo, nome_grupo
            FROM sis_grupos
            WHERE cod_grupo <> 999
            ORDER BY nome_grupo
        ";
        $RS = $Conexao_db->Execute($var_sql);

        echo '<option value="">Selecione um Grupo</option>';
        while (!$RS->EOF) {
            echo '<option value="' . $RS->fields['cod_grupo'] . '">' . htmlspecialchars($RS->fields['nome_grupo']) . '</option>';
            $RS->MoveNext();
        }
    }
}

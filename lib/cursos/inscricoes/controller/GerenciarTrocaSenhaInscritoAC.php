<?php

/**
 * Tela admin "Trocas de senha do inscrito": quem clicou no link de recuperacao
 * e criou uma senha nova, e quando. Os dados vem do InscritoDAO
 * (carregarTrocasSenhaInscrito) -- SQL fica no DAO, aqui so formatacao/HTML.
 * Mostra ultimo login com sucesso, ultima tentativa (com/sem sucesso) e links
 * para "logar como" e abrir a inscricao. So leitura -- nao mostra senha.
 */
class GerenciarTrocaSenhaInscritoAC
{
    public function __construct()
    {
        FWA_AutenticacaoGeral($_SESSION['_cod_usuario'], enumSistema::SIS_GERENCIAR_ADM_GERAL, 5, false, false);
    }

    /** Aceita 'AAAA-MM-DD' ou vazio. Devolve o valor limpo ou ''. */
    private function dataFiltro($v)
    {
        $v = trim((string) $v);
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $v) ? $v : '';
    }

    /** 'AAAA-MM-DD HH:MM:SS' -> 'DD/MM/AAAA HH:MM'. */
    private function dataHora($v)
    {
        $v = trim((string) $v);
        if ($v === '' || strpos($v, '-') === false) return '&mdash;';
        $d = date_create($v);
        return $d ? $d->format('d/m/Y H:i') : htmlspecialchars($v);
    }

    public function carregarTabelaTrocaSenha($fws_, $debug = false)
    {
        $de  = $this->dataFiltro(isset($fws_['de'])  ? $fws_['de']  : '');
        $ate = $this->dataFiltro(isset($fws_['ate']) ? $fws_['ate'] : '');
        $email = trim(isset($fws_['email']) ? $fws_['email'] : '');
        $soUsados = !(isset($fws_['incluirPendentes']) && $fws_['incluirPendentes'] == '1');

        $dao = new InscritoDAO();
        $lista = $dao->carregarTrocasSenhaInscrito(
            array('de' => $de, 'ate' => $ate, 'email' => $email, 'soUsados' => $soUsados),
            $debug
        );

        $totTrocaram = 0;
        $totPendentes = 0;
        foreach ($lista as $r) {
            if ($r['usado'] == 1) { $totTrocaram++; } else { $totPendentes++; }
        }
        ?>
        <div style="padding:8px 18px;">
            <span class="badge-tipo"><span class="dot dot-green"></span>Trocaram: <?php echo $totTrocaram ?></span>
            &nbsp;&nbsp;
            <?php if (!$soUsados) { ?>
            <span class="badge-tipo"><span class="dot dot-orange"></span>Pendentes/expirados: <?php echo $totPendentes ?></span>
            <?php } ?>
        </div>
        <table class="tbl-fin" id="tblTrocaSenha" style="width:100%;">
            <thead>
                <tr>
                    <th style="width:40px;">#</th>
                    <th class="ts-sort" data-col="1" style="cursor:pointer;">Nome <span class="sort-arrow">&uarr;&darr;</span></th>
                    <th class="ts-sort" data-col="2" style="cursor:pointer;">E-mail <span class="sort-arrow">&uarr;&darr;</span></th>
                    <th class="ts-sort" data-col="3" style="cursor:pointer; width:120px;">CPF <span class="sort-arrow">&uarr;&darr;</span></th>
                    <th class="ts-sort" data-col="4" style="cursor:pointer; width:130px;">Pediu em <span class="sort-arrow">&uarr;&darr;</span></th>
                    <th class="ts-sort" data-col="5" style="cursor:pointer; width:130px;">Trocou em <span class="sort-arrow">&uarr;&darr;</span></th>
                    <th class="ts-sort" data-col="6" style="cursor:pointer; width:130px;">&Uacute;ltimo login <span class="sort-arrow">&uarr;&darr;</span></th>
                    <th class="ts-sort" data-col="7" style="cursor:pointer; width:170px;">&Uacute;ltima tentativa <span class="sort-arrow">&uarr;&darr;</span></th>
                    <th class="ts-sort" data-col="8" style="cursor:pointer; width:110px;">IP <span class="sort-arrow">&uarr;&darr;</span></th>
                    <th style="width:130px;">Senha atual</th>
                    <th style="width:80px; text-align:center;">A&ccedil;&otilde;es</th>
                </tr>
            </thead>
            <tbody>
        <?php
        $i = 0;
        if (!empty($lista)) {
            foreach ($lista as $r) {
                $i++;
                $nome    = $r['nome'] ? mb_strtoupper($r['nome']) : '<span style="color:#999;">&mdash;</span>';
                $cpf     = $r['cpf']  ? formatCpfCnpjView($r['cpf']) : '<span style="color:#999;">&mdash;</span>';
                $pediu   = $this->dataHora($r['dat_incl']);
                $trocou  = ($r['usado'] == 1 && $r['dat_uso'])
                         ? $this->dataHora($r['dat_uso'])
                         : '<span style="color:#c77; font-size:12px;">n&atilde;o trocou</span>';
                $ultimo  = $r['ultimo_login']
                         ? $this->dataHora($r['ultimo_login'])
                         : '<span style="color:#999; font-size:12px;">nunca logou</span>';

                // ultima tentativa: data + sucesso/falha
                if ($r['ult_tentativa']) {
                    $dataTent = $this->dataHora($r['ult_tentativa']);
                    $tentativa = ($r['ult_tentativa_sucesso'] == 1)
                        ? $dataTent . ' <span style="color:#2d6a4f; font-size:11px;">&#10003; sucesso</span>'
                        : $dataTent . ' <span style="color:#c0392b; font-size:11px;">&#10007; falhou</span>';
                    $ordTent = $r['ult_tentativa'];
                } else {
                    $tentativa = '<span style="color:#999; font-size:12px;">&mdash;</span>';
                    $ordTent = '';
                }

                // objeto Inscrito so para reusar os componentes de botao da lista de
                // inscritos (CarregarBotoesAC) -- padrao unico, troca centralizada.
                $objBtn = null;
                if (!empty($r['cod_inscrito']) && !empty($r['url_prefixo'])) {
                    $objBtn = new Inscrito();
                    $objBtn->setCpf($r['cpf']);
                    $objBtn->setCodInscrito($r['cod_inscrito']);
                    $objBtn->setCodEvento($r['cod_evento']);
                    $objBtn->setUrlPrefixo($r['url_prefixo']);
                }

                // valores crus para ordenacao correta (data ISO ordena como texto)
                $ordPediu  = $r['dat_incl'] ? $r['dat_incl'] : '';
                $ordTrocou = ($r['usado'] == 1 && $r['dat_uso']) ? $r['dat_uso'] : '';
                $ordUltimo = $r['ultimo_login'] ? $r['ultimo_login'] : '';

                // ja tem senha propria (bcrypt) HOJE em tab_inscritos -- independe
                // de qual caminho gerou (este token ou o admin ter definido direto).
                $comSenhaPropria = !empty($r['com_senha_propria']);
                $situacaoSenha = $comSenhaPropria
                    ? "<span class='badge-tipo'><span class='dot dot-green'></span>senha nova</span>"
                    : "<span class='badge-tipo'><span class='dot dot-orange'></span>legado</span>";
                ?>
                <tr>
                    <td><?php echo $i ?></td>
                    <td><?php echo $nome ?></td>
                    <td><?php echo htmlspecialchars((string)$r['e_mail']) ?></td>
                    <td><?php echo $cpf ?></td>
                    <td data-ord="<?php echo htmlspecialchars($ordPediu) ?>"><?php echo $pediu ?></td>
                    <td data-ord="<?php echo htmlspecialchars($ordTrocou) ?>"><?php echo $trocou ?></td>
                    <td data-ord="<?php echo htmlspecialchars($ordUltimo) ?>"><?php echo $ultimo ?></td>
                    <td data-ord="<?php echo htmlspecialchars($ordTent) ?>"><?php echo $tentativa ?></td>
                    <td><?php echo htmlspecialchars((string)$r['ip']) ?></td>
                    <td data-ord="<?php echo $comSenhaPropria ? 1 : 0 ?>"><?php echo $situacaoSenha ?></td>
                    <td style="text-align:center; white-space:nowrap;"><?php
                        // abre o modal de acoes (gerar senha, logar como, abrir inscricao)
                        if ($r['e_mail']) { ?>
                        <a href="javascript:void(0)" onclick="abrirModalLoginEmail('<?php echo htmlspecialchars($r['e_mail'], ENT_QUOTES) ?>')" title="Ver/gerenciar login deste e-mail" style="margin-right:8px;"><i class="fa fa-key"></i></a>
                        <?php }
                        if ($objBtn) {
                            CarregarBotoesAC::btnLogarComo($fws_, $objBtn, $debug);
                            echo ' ';
                            CarregarBotoesAC::btnAbrirInscricao($fws_, $objBtn, $debug);
                        }
                    ?></td>
                </tr>
                <?php
            }
        } else {
            echo '<tr><td colspan="11" style="text-align:center; padding:24px; color:#888;">Nenhuma troca de senha encontrada no per&iacute;odo.</td></tr>';
        }
        ?>
            </tbody>
        </table>
        <?php
    }

    /**
     * Badge com quantas pessoas (dos inscritos ATIVADOS) ja definiram senha
     * propria (bcrypt), pra dar uma ideia geral de quanto da base ja migrou.
     */
    public function resumoMigracao($debug = false)
    {
        $dao = new InscritoDAO();
        $r = $dao->resumoMigracaoSenhaPropria($debug);
        $pct = $r['total'] > 0 ? round($r['com_senha'] * 100 / $r['total']) : 0;
        ?>
        <div style="margin-top:8px;">
            <span class="badge-tipo"><span class="dot dot-green"></span>J&aacute; migraram para senha pr&oacute;pria (bcrypt): <b><?php echo $r['com_senha'] ?></b> de <?php echo $r['total'] ?> (<?php echo $pct ?>%)</span>
        </div>
        <?php
    }

    /**
     * AJAX: renderiza os botoes "logar como" e "abrir inscricao" pra um e-mail,
     * usando a inscricao representativa da pessoa (ATIVADA mais recente).
     * Reusado pelo modal de tentativas de login do Dashboard Geral.
     */
    public function renderBotoesAcessoPorEmail($fws_, $debug = false)
    {
        $email = trim(isset($fws_['email']) ? $fws_['email'] : '');
        if ($email === '') { return; }

        $dao = new InscritoDAO();
        $r = $dao->buscarInscritoRepresentativoPorEmail($email, $debug);
        if (!$r) {
            echo '<span class="text-muted" style="font-size:12px;">Nenhuma inscri&ccedil;&atilde;o encontrada pra abrir/logar.</span>';
            return;
        }

        $obj = new Inscrito();
        $obj->setCpf($r['cpf']);
        $obj->setCodInscrito($r['cod_inscrito']);
        $obj->setCodEvento($r['cod_evento']);
        $obj->setUrlPrefixo($r['url_prefixo']);

        echo '<span style="font-size:12px; color:#888;">Inscri&ccedil;&atilde;o #' . (int) $r['cod_inscrito'] . ' (evento ' . (int) $r['cod_evento'] . '): </span> ';
        CarregarBotoesAC::btnLogarComo($fws_, $obj, $debug);
        echo ' ';
        CarregarBotoesAC::btnAbrirInscricao($fws_, $obj, $debug);
    }

    /**
     * AJAX: busca um inscrito por e-mail para o admin confirmar quem e antes de
     * gerar a senha (nome, 4 digitos do CPF, quantas inscricoes, se ja tem senha).
     * Devolve tambem um token CSRF novo, que o cliente tem que mandar de volta em
     * definirSenhaAdmin -- essas duas acoes MUDAM senha/mandam e-mail, e o app nao
     * tem protecao CSRF por padrao (so gate de sessao), entao aqui e reforcado.
     */
    public function buscarInscritoSenha($fws_, $debug = false)
    {
        $email = trim(isset($fws_['email']) ? $fws_['email'] : '');
        if ($email === '') { echo json_encode(array('ok' => 0, 'msg' => 'Informe o e-mail.')); return; }

        $dao = new InscritoDAO();
        $r = $dao->buscarInscritoParaSenhaAdmin($email, $debug);
        if (!$r) { echo json_encode(array('ok' => 0, 'msg' => 'Nenhum inscrito ATIVADO com esse e-mail.')); return; }

        echo json_encode(array(
            'ok' => 1,
            'nome' => $r['nome'] ? $r['nome'] : '(sem nome)',
            'ult4' => $r['ult4'],
            'tem_cpf' => $r['ult4'] !== '' ? 1 : 0,
            'ativas' => $r['ativas'],
            'com_senha_propria' => $r['com_senha_propria'],
            'csrf' => GerenciarLoginAC::novoTokenCsrf(),
        ));
    }

    /**
     * AJAX: gera uma senha aleatoria, grava em bcrypt em TODAS as inscricoes da
     * pessoa (definirSenhaHashInscrito) e devolve a senha em claro para o admin
     * copiar e repassar. A pessoa entra com e-mail + essa senha + 4 digitos do CPF.
     *
     * Exige o token CSRF devolvido por buscarInscritoSenha -- sem ele, um site
     * malicioso poderia forjar um POST usando a sessao logada do admin (CSRF
     * classico) e trocar a senha de qualquer inscrito sem o admin saber.
     *
     * A senha gerada NAO volta pro cliente ecoar de volta em enviarSenhaEmail:
     * fica guardada na sessao (com um token proprio, de uso unico) pra aquele
     * metodo ler -- assim nao da pra CSRFar/forjar o texto que vai no e-mail.
     */
    public function definirSenhaAdmin($fws_, $debug = false)
    {
        if (!GerenciarLoginAC::validarTokenCsrf(isset($fws_['csrf']) ? $fws_['csrf'] : '')) {
            echo json_encode(array('ok' => 0, 'msg' => 'Sess&atilde;o do formul&aacute;rio expirada. Feche e reabra o modal.'));
            return;
        }

        $email = trim(isset($fws_['email']) ? $fws_['email'] : '');
        if ($email === '') { echo json_encode(array('ok' => 0, 'msg' => 'Informe o e-mail.')); return; }

        $dao = new InscritoDAO();
        $ec = $dao->getEmailCpfParaRecuperacao($email, $debug);
        if (!$ec) { echo json_encode(array('ok' => 0, 'msg' => 'Inscrito nao encontrado (ATIVADO).')); return; }

        // senha manual (admin digitou) tem prioridade; senao gera aleatoria.
        // Digitar a senha e restrito ao super admin (mesmo criterio do Dashboard Geral).
        $senhaManual = isset($fws_['senha']) ? trim($fws_['senha']) : '';
        if ($senhaManual !== '') {
            if ((int) (isset($_SESSION['_cod_usuario']) ? $_SESSION['_cod_usuario'] : 0) !== 668) {
                echo json_encode(array('ok' => 0, 'msg' => 'Sem permiss&atilde;o para definir senha manual.'));
                return;
            }
            if (mb_strlen($senhaManual) < 6) {
                echo json_encode(array('ok' => 0, 'msg' => 'A senha deve ter ao menos 6 caracteres.'));
                return;
            }
            $senha = $senhaManual;
        } else {
            $senha = SenhaHelper::gerarSenhaAleatoria(8);
        }
        $hash  = SenhaHelper::hash($senha);
        $n     = $dao->definirSenhaHashInscrito($ec['e_mail'], $ec['cpf'], $hash, $debug);
        $ult4  = $ec['cpf'] ? substr($ec['cpf'], -4) : '';

        // Guarda na sessao (nao no cliente) o que enviarSenhaEmail pode enviar --
        // one-shot, expira em 10 minutos.
        $_SESSION['_dsa_pendente'] = array(
            'email' => $ec['e_mail'],
            'senha' => $senha,
            'ult4'  => $ult4,
            'exp'   => time() + 600,
        );

        echo json_encode(array(
            'ok' => 1,
            'email' => $ec['e_mail'],
            'senha' => $senha,
            'ult4' => $ult4,
            'inscricoes' => (int) $n,
            'csrfEnviar' => GerenciarLoginAC::novoTokenCsrf(),
        ));
    }

    /**
     * AJAX: envia por e-mail para o inscrito a senha ja gerada por definirSenhaAdmin.
     * Nao confia em e-mail/senha vindos do POST -- le da sessao (gravado por
     * definirSenhaAdmin, one-shot) pra ninguem conseguir usar este endpoint pra
     * mandar um e-mail "do sistema" com texto arbitrario no lugar da senha.
     */
    public function enviarSenhaEmail($fws_, $debug = false)
    {
        if (!GerenciarLoginAC::validarTokenCsrf(isset($fws_['csrf']) ? $fws_['csrf'] : '')) {
            echo json_encode(array('ok' => 0, 'msg' => 'Sess&atilde;o do formul&aacute;rio expirada. Gere a senha novamente.'));
            return;
        }

        $pend = isset($_SESSION['_dsa_pendente']) ? $_SESSION['_dsa_pendente'] : null;
        unset($_SESSION['_dsa_pendente']); // one-shot, usada ou nao

        if (!$pend || empty($pend['email']) || empty($pend['senha']) || $pend['exp'] < time()) {
            echo json_encode(array('ok' => 0, 'msg' => 'Nada pendente pra enviar. Gere a senha novamente.'));
            return;
        }

        $assunto = "Acesso ao sistema - sua senha";
        $corpo = "Ola,\r\n\r\n"
               . "Foi gerada uma senha para o seu acesso ao sistema.\r\n\r\n"
               . "Login (e-mail): " . $pend['email'] . "\r\n"
               . "Senha: " . $pend['senha'] . "\r\n"
               . "4 ultimos digitos do CPF: " . $pend['ult4'] . "\r\n\r\n"
               . "Ao entrar, recomendamos trocar a senha.\r\n";
        try {
            // enviarEmail() devolve false (sem lancar excecao) quando o provedor
            // recusa o envio -- ex: Postmark com o endereco em supressao por bounce
            // antigo/spam/manual (2026-08-27: era exatamente isso e o admin via
            // "enviado com sucesso" sem o e-mail nunca ter saido). Checar o retorno
            // e obrigatorio aqui.
            $enviou = GerenciarEmailAC::enviarEmail($pend['email'], $assunto, $corpo);
            if (!$enviou) {
                echo json_encode(array('ok' => 0, 'msg' => 'O servidor de e-mail recusou o envio (endere&ccedil;o pode estar suprimido por bounce/spam anterior). Confira o log ou o painel do provedor antes de tentar de novo.'));
                return;
            }
            echo json_encode(array('ok' => 1, 'para' => $pend['email']));
        } catch (Exception $e) {
            echo json_encode(array('ok' => 0, 'msg' => 'Falha ao enviar o e-mail.'));
        }
    }
}

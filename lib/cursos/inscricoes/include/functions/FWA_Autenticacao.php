<?php

/**
 * Verifica permissão sem considerar cod_evento.
 * Chama FWA_Autenticacao passando 'TODOS' como cod_evento,
 * removendo o filtro por evento específico.
 * Os sistemas globais devem ser cadastrados com prefixo "Geral"
 * (ex: SIS_FINANCEIRO_GERAL, SIS_CARGA_TRANSPARENCIA_GERAL).
 */
Function FWA_AutenticacaoGeral($cod_usuario, $cod_sistema, $cod_direito=5, $cod_msg=false, $redirect=false) {
    return FWA_Autenticacao($cod_usuario, $cod_sistema, $cod_direito, $cod_msg, $redirect, 'TODOS');
}

/**
 *
 * @param type $cod_usuario
 * @param type $cod_sistema
 * @param type $cod_direito
 * @param type $cod_msg
 * @param type $redirect
 * @return boolean
 */
Function FWA_Autenticacao($cod_usuario, $cod_sistema, $cod_direito=5, $cod_msg=false, $redirect=false, $cod_evento=false) {
    $URL = WB_URL_VIEW . "?content=view/admin/login.php";
    $URL_ACESSO_NEGADO = WB_URL_VIEW . "?content=view/admin/acesso-negado.php";
    if($cod_evento==false){
       $cod_evento = COD_EVENTO; 
    }

    // Verificacao de token externo (Python scripts, Chatwoot). definido externamente
    if (defined('WB_API_TOKEN') && WB_API_TOKEN !== ''
        && isset($_POST['api_token']) && hash_equals(WB_API_TOKEN, $_POST['api_token'])) {
        return true;
    }

    If (FWA_UsuarioLogado($cod_usuario) == true) { #Testa se a usuario logado, se sim ele entra
        if (enumSistema::SIS_INCLUIR_EVENTO!=$cod_sistema) {
            If (FWA_DireitoSobreSistema($cod_usuario, $cod_sistema, $cod_direito, $cod_evento) == true) { #Testa se o usuario logado tem direito se acesso ao sistema, se sim ele entra
                If ((@!$cod_msg)or ( $cod_msg == false)) {# se estiver difinido retorna a mensagem de erro
                    $FWA_Autenticacao = true;
                } Else {
                    //echo "Usuário logado, com direito de acesso a este sistema!<br>";
                    $FWA_Autenticacao = true;
                }
            } Else {
                If ((@!$cod_msg)or ( $cod_msg == false)) {# se estiver difinido retorna a mensagem de erro
                    If ((isset($redirect) == true)and ( empty($redirect) == true)) {# se estiver difinido redireciona
                    } Else {
                        echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $URL_ACESSO_NEGADO . "\"></HTML>";
                        exit();
                    }
                    $FWA_Autenticacao = false;
                } Else {
                    echo "<a href='" . $URL_ACESSO_NEGADO . "'>Usuário logado, sem direito de acesso a este sistema!</a><br>";
                    $FWA_Autenticacao = false;
                }
            }
        }else{
            $FWA_Autenticacao = true;
        }
    } Else {
        If ((@!$cod_msg)or ( $cod_msg == false)) {# se estiver difinido retorna a mensagem de erro
            If ((isset($redirect) == true)and ( empty($redirect) == true)) {# se estiver difinido redireciona para tela de login
            } Else {
                $_SESSION['MSG'] = "Sessão expirou ou não há usuário logado.";
                echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $URL . "\"></HTML>";
                exit();
            }
            $FWA_Autenticacao = false;
        } Else {
            // Sessao caiu num ponto que pediu mensagem em vez de redirecionar.
            // Impresso UMA vez por requisicao: varias telas encadeiam dois ou tres
            // guards (*-seguranca.php) e cada chamada repetia a mesma linha solta.
            static $jaAvisouSessao = false;
            if (!$jaAvisouSessao) {
                $jaAvisouSessao = true;
                $segundosSessao = 15;   // tempo de leitura antes do redirect
                // Mesmo desenho da tela irma acesso-negado.php: panel + alert
                // com icone fa, para nao destoar do resto do admin.
                echo '<div style="min-height:60vh;display:flex;align-items:center;'
                   . 'justify-content:center;padding:24px;margin-bottom:28px;">'
                   . '<div class="panel panel-default" '
                   . 'style="max-width:480px;width:100%;margin:0;">'
                   . '<div class="panel-heading"><h4 style="margin:0;">Sessão Expirada</h4></div>'
                   . '<div class="panel-body" style="text-align:center;">'
                   . '<div class="alert alert-warning" role="alert" style="text-align:left;">'
                   . '<i class="fa fa-clock-o"></i> '
                   . '<strong>Sua sessão expirou.</strong> '
                   . 'Por segurança, o acesso é encerrado após um período sem uso.'
                   . '</div>'
                   . '<p>Redirecionando para o login em '
                   . '<strong><span id="fwaSessaoContador">' . $segundosSessao . '</span></strong>'
                   . ' segundos.</p>'
                   . '<a href="' . htmlspecialchars($URL, ENT_QUOTES) . '" class="btn btn-primary">'
                   . 'Fazer login agora</a>'
                   . '</div></div></div>'
                   . '<script>(function(){'
                   . 'var s=' . $segundosSessao . ','
                   . 'e=document.getElementById("fwaSessaoContador"),'
                   . 'u=' . json_encode($URL) . ';'
                   . 'var t=setInterval(function(){s--;'
                   . 'if(e){e.textContent=(s>0?s:0);}'
                   . 'if(s<=0){clearInterval(t);window.location.href=u;}'
                   . '},1000);})();</script>';
            }
            $FWA_Autenticacao = false;
        }
    }
    return $FWA_Autenticacao;
}


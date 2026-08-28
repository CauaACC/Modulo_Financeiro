<?php
require_once(dirname(__FILE__)."/../include/config.php");
//controleAcesso($gerenciarEventos);
//$fws_['gerenciarEventos'] = $gerenciarEventos;
try {

    if(!$fws_['c']){

        $fws_['c'] = addslashesopctec($fwsGet_['c']);
        $fws_['m'] = addslashesopctec($fwsGet_['m']);
    }

    if($fws_['c']=='' or $fws_['m']==''){
        throw new Exception("Uma tentativa de acesso direto foi detectada.
            <br>É possível que você tentou acessar algum recurso de forma direta sem clicar no botão especifico da ação.
            <br>Ex: guardar a url do boleto bancário e tentar acessa-la posteriomente sem acessar a área do inscrito.
            <br><a href='".WB_URL_VIEW."'>Neste caso, clique aqui para reiniciar o processo</a>
            <br>Se o problema persistir entre em contato com administração.
            .");
    }
    class Command {
        public function __construct($fws_) {
            $classe = $fws_['c'];
            $metodo = $fws_['m'];

            require_once dirname(__FILE__).'/'.$classe.'AC.php';
            $classe .= 'AC';
            $obj = new $classe;
            $obj->$metodo($fws_);
        }
   }
   //$action = $fwsGet_['action'];
   //$command = new Command($action);
   $command = new Command($fws_);

} catch (Exception $exc) {
    //echo $exc->getTraceAsString();
    $msgErro = $exc->getMessage();
    // Se a resposta esperada era JSON (maioria dos AJAX), devolve JSON pra nao quebrar o handler do cliente.
    $aceitaJson = isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;
    $ehXhr = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

    // Sessao caiu, ou o usuario esta logado e so nao tem direito? O cliente precisa
    // dessa distincao: no primeiro caso leva pro login, no segundo so mostra a
    // mensagem (redirecionar viraria laco). Decidido pelo estado da sessao, nao
    // pelo texto da excecao -- comparar string quebraria ao reescrever a frase.
    $temAdmin    = !empty($_SESSION['_cod_usuario']);
    $temInscrito = !empty($_SESSION['_cod_inscrito']);
    $sessaoExpirada = (!$temAdmin && !$temInscrito);
    // Sem sessao nao da pra saber quem era; o endereco da tela diz de que area
    // veio a chamada e, portanto, para qual login mandar.
    $origem = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
    $areaSessao = ($temInscrito || (!$temAdmin && strpos($origem, 'view/inscricao/') !== false))
                ? 'inscrito' : 'admin';

    if ($aceitaJson || $ehXhr) {
        header('Content-Type: application/json; charset=UTF-8');
        // Os dois contratos de resposta convivem no sistema: a maioria das telas le
        // statusMessage/message, algumas leem sucesso/mensagem. Preencher ambos faz
        // o erro chegar em todas sem precisar editar tela por tela -- sem 'mensagem'
        // a consulta do PagSeguro caia no fallback e dizia "Nenhuma transacao
        // encontrada", afirmando ausencia de dados por falta de login.
        echo json_encode(array(
            'statusMessage'  => false,
            'sucesso'        => false,
            'message'        => strip_tags($msgErro),
            'mensagem'       => strip_tags($msgErro),
            'sessaoExpirada' => $sessaoExpirada,
            'area'           => $areaSessao,
        ));
    } else {
        echo '<!DOCTYPE html><html lang="pt-Br"><head><meta charset="UTF-8"><title>Aviso</title></head><body style="margin:0;padding:40px;background:#f5f5f5;font-family:Arial,Helvetica,sans-serif;">';
        echo '<div style="max-width:600px;margin:60px auto;background:#fff;border:1px solid #e0e0e0;border-radius:8px;padding:28px;box-shadow:0 1px 4px rgba(0,0,0,0.06);">';
        echo '<div style="display:flex;align-items:center;margin-bottom:12px;">';
        echo '<span style="font-size:28px;color:#d9534f;margin-right:12px;">&#9888;</span>';
        // Sessao expirada nao e' "acesso nao permitido": o usuario tinha acesso, o
        // tempo e' que acabou. Dizer a coisa certa evita que ele pense que perdeu
        // permissao e va reclamar de um problema que nao existe.
        echo '<h3 style="margin:0;color:#333;font-size:18px;">'
           . ($sessaoExpirada ? 'Sess&atilde;o expirada' : 'Acesso n&atilde;o permitido') . '</h3>';
        echo '</div>';
        echo '<div style="color:#555;font-size:14px;line-height:1.5;padding:8px 0;border-top:1px solid #eee;">' . $msgErro . '</div>';
        echo '<div style="margin-top:20px;text-align:right;">';
        echo '<a href="javascript:history.back()" style="display:inline-block;padding:8px 18px;background:#f0f0f0;color:#333;border:1px solid #ccc;border-radius:4px;text-decoration:none;font-size:13px;margin-right:6px;">Voltar</a>';
        if ($sessaoExpirada) {
            $urlLogin = WB_URL_VIEW . '?content=view/'
                      . ($areaSessao === 'inscrito' ? 'inscricao/inscricao_login.php' : 'admin/login.php');
            echo '<a href="' . $urlLogin . '" style="display:inline-block;padding:8px 18px;background:#4A90E2;color:#fff;border-radius:4px;text-decoration:none;font-size:13px;">Fazer login</a>';
        } else {
            echo '<a href="' . WB_URL_VIEW . '" style="display:inline-block;padding:8px 18px;background:#4A90E2;color:#fff;border-radius:4px;text-decoration:none;font-size:13px;">In&iacute;cio</a>';
        }
        echo '</div>';
        echo '</div></body></html>';
    }
}

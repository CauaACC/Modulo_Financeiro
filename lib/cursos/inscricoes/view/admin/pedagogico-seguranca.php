<?php
require_once(dirname(__FILE__)."/../../include/config.php");

@$cod_usuario = $_SESSION['_cod_usuario'];

// Determinar papel do usuario no modulo pedagogico
$_papelPedagogico = '';
if (FWA_Autenticacao($cod_usuario, enumSistema::SIS_PEDAGOGICO, 5, false, false)) {
    $_papelPedagogico = 'COORDENADOR';
} elseif (FWA_Autenticacao($cod_usuario, enumSistema::SIS_PEDAGOGICO_GERENTE, 5, false, false)) {
    $_papelPedagogico = 'GERENTE';
} elseif (FWA_Autenticacao($cod_usuario, enumSistema::SIS_PEDAGOGICO_SUPERVISOR, 5, false, false)) {
    $_papelPedagogico = 'SUPERVISOR';
}

if (!$_papelPedagogico) {
    require_once(dirname(__FILE__)."/acesso-negado.php");
    exit;
}

$_isAdminPedagogico = in_array($_papelPedagogico, array('COORDENADOR', 'GERENTE'));

// Telas restritas a coordenador/gerente — supervisor nao pode acessar
$_viewsAdminOnly = array(
    'GerenciarColegios',
    'GerenciarAvaliacaoInicial',
    'GerenciarLaudosAluno',
    'DashboardPedagogico',
    'GerenciarProgramacaoAvaliacao',
    'GerenciarSupervisores',
    'GerenciarVinculoSupervisor',
    'GerenciarRegistroAtividadePedagogico',
    'GerenciarRegistroAtividadePedagogicoAvaliacao'
);

if (!$_isAdminPedagogico) {
    // Detectar a view atual pelo content na URL
    $__viewAtual = isset($fwsGet_['content']) ? $fwsGet_['content'] : (isset($_GET['content']) ? $_GET['content'] : '');
    foreach ($_viewsAdminOnly as $__viewRestrita) {
        if (strpos($__viewAtual, $__viewRestrita) !== false) {
            $_SESSION['MSG'] = "Acesso restrito a coordenadores e gerentes.";
            echo "<script>window.location.href='index.php?content=view/admin/GerenciarAlunos.php';</script>";
            exit;
        }
    }
}

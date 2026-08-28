<?php

/**
 * Controller da tela de recuperação de pedidos excluidos.
 *
 * Acesso restrito ao usuario 668 (super admin), mesmo gate usado em
 * DashboardUsuariosGeralAC. Qualquer outro usuario recebe redirect silencioso.
 */
class GerenciarPedidoExcluidoAC {

    const COD_USUARIO_AUTORIZADO = 668;

    public static function autorizado() {
        return isset($_SESSION['_cod_usuario'])
            && (int) $_SESSION['_cod_usuario'] === self::COD_USUARIO_AUTORIZADO;
    }

    public static function guard() {
        if (!self::autorizado()) {
            $url = WB_URL_VIEW . "?content=view/admin/default.php";
            @header("Location: " . $url);
            echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $url . "\"></HTML>";
            exit();
        }
    }

    public static function listar($codEvento=null, $codInscrito=null, $debug=false) {
        self::guard();
        $dao = new PedidoDAO();
        return $dao->listarExcluidos($codEvento, $codInscrito, $debug);
    }

    public static function restaurar($codPedido, $codEvento, $debug=false) {
        self::guard();

        $codPedido = (int)$codPedido;
        $codEvento = (int)$codEvento;

        if($codPedido<=0 || $codEvento<=0){
            return ['ok'=>false, 'msg'=>'Parâmetros inválidos.'];
        }

        $obj = new Pedido();
        $obj->setId($codPedido);
        $obj->setEventoId($codEvento);

        $dao = new PedidoDAO();
        if($dao->restaurar($obj, $debug)){
            return ['ok'=>true, 'msg'=>"Pedido {$codPedido} restaurado com sucesso."];
        }
        return ['ok'=>false, 'msg'=>"Falha ao restaurar pedido {$codPedido}. Pode já existir, ou o snapshot não foi encontrado."];
    }
}

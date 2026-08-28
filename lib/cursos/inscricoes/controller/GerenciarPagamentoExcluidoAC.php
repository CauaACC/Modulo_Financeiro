<?php

/**
 * Restauracao de pagamentos excluidos (tela GerenciarPagamentoExcluido.php).
 * A CONSULTA continua aberta a quem tem SIS_CONTROLE_FINANCEIRO (gate na
 * propria view, como sempre foi). RESTAURAR e' restrito ao usuario 668
 * (super admin), mesmo padrao do GerenciarPedidoExcluidoAC.
 */
class GerenciarPagamentoExcluidoAC {

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

    public static function restaurar($codPagamento, $codEvento, $debug=false) {
        self::guard();

        $codPagamento = (int)$codPagamento;
        $codEvento    = (int)$codEvento;

        if($codPagamento<=0 || $codEvento<=0){
            return ['ok'=>false, 'msg'=>'Parâmetros inválidos.'];
        }

        $obj = new Pagamento();
        $obj->setCodPagamento($codPagamento);
        $obj->setCodEvento($codEvento);

        $dao = new PagamentoDAO();
        if($dao->restaurar($obj, $debug)){
            return ['ok'=>true, 'msg'=>"Pagamento {$codPagamento} restaurado com sucesso."];
        }
        return ['ok'=>false, 'msg'=>"Falha ao restaurar pagamento {$codPagamento}. Pode já existir, ou o snapshot não foi encontrado."];
    }
}

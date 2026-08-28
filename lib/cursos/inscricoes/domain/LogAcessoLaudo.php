<?php

class LogAcessoLaudo extends LogAcessoLaudoVO {

    private $objDAO;

    function __construct() {
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new LogAcessoLaudoDAO();
    }

    public static function registrar($codLaudo, $codAluno, $acao, $justificativa = null, $debug = false) {
        try {
            $obj = new LogAcessoLaudo();
            $obj->setCodLaudo($codLaudo);
            $obj->setCodAluno($codAluno);
            $obj->setAcao($acao);
            $obj->setCodUsuario(isset($_SESSION['_cod_usuario']) ? $_SESSION['_cod_usuario'] : '');
            $obj->setDataAcesso(date('Y-m-d H:i:s'));
            $obj->setIp(isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '');
            $obj->setHttpUserAgent(isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '');
            $obj->setJustificativa($justificativa);
            $obj->objDAO->insert($obj, $debug);
        } catch (Exception $e) {
            // Nao interromper a operacao principal
        }
    }

    public function getLogsPorLaudo($codLaudo, $debug = false) {
        return $this->objDAO->carregarLogsPorLaudo($this->getCodEvento(), $codLaudo, $debug);
    }

}

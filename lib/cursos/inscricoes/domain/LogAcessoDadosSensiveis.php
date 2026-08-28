<?php

class LogAcessoDadosSensiveis extends LogAcessoDadosSensiveisVO {

    private $objDAO;

    function __construct() {
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new LogAcessoDadosSensiveisDAO();
    }

    /**
     * Registra um acesso a dado sensivel. Nunca interrompe a operacao principal.
     *
     * @param string $tipoDado   Ex: 'NOME_ALUNO', 'LAUDO', 'AVALIACAO'
     * @param string $codReferencia  Codigo do registro acessado
     * @param string $acao       Ex: 'REVELAR_NOME', 'LER', 'INSERIR', 'ALTERAR', 'EXCLUIR', 'DOWNLOAD'
     * @param string|null $detalhes  Informacoes adicionais
     * @param bool $debug
     */
    public static function registrar($tipoDado, $codReferencia, $acao, $detalhes = null, $debug = false) {
        try {
            $obj = new LogAcessoDadosSensiveis();
            $obj->setTipoDado($tipoDado);
            $obj->setCodReferencia($codReferencia);
            $obj->setAcao($acao);
            $obj->setCodUsuario(isset($_SESSION['_cod_usuario']) ? $_SESSION['_cod_usuario'] : '');
            $obj->setDataAcesso(date('Y-m-d H:i:s'));
            $obj->setIp(isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '');
            $obj->setHttpUserAgent(isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '');
            $obj->setDetalhes($detalhes);
            $obj->objDAO->insert($obj, $debug);
        } catch (Exception $e) {
            // Nao interromper a operacao principal
        }
    }

    public function getLogsPorReferencia($tipoDado, $codReferencia, $debug = false) {
        return $this->objDAO->carregarLogsPorReferencia($this->getCodEvento(), $tipoDado, $codReferencia, $debug);
    }
}

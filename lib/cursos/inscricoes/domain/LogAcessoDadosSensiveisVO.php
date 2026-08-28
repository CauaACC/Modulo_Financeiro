<?php

class LogAcessoDadosSensiveisVO extends PadraoVO {

    protected $codLog;
    protected $codEvento;
    protected $tipoDado;
    protected $codReferencia;
    protected $acao;
    protected $codUsuario;
    protected $dataAcesso;
    protected $ip;
    protected $httpUserAgent;
    protected $detalhes;

    function validarAdicionar() {
        if (!$this->codEvento) throw new Exception("Evento não informado.");
        if (!$this->tipoDado) throw new Exception("Tipo de dado não informado.");
        if (!$this->acao) throw new Exception("Ação não informada.");
    }
}

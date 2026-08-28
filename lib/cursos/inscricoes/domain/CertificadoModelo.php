<?php

/**
 * Entidade do Modelo da FRENTE do certificado.
 * Instancia o DAO e expoe os metodos de negocio. Nenhum SQL aqui.
 */
class CertificadoModelo extends CertificadoModeloVO
{
    private $objDAO;

    function __construct()
    {
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new CertificadoModeloDAO();
    }

    /** Carrega um modelo pelo codigo (dentro do evento). */
    public function carregarPorCod($debug = false)
    {
        return $this->objDAO->getByCod($this, $debug);
    }

    /** Modelos ATIVADOS do evento, para listagem e seletores. */
    public function listar($debug = false)
    {
        return $this->objDAO->listar($this, $debug);
    }

    /**
     * Resolve qual modelo vale para um perfil, na ordem combinada:
     *   1) perfil.modelo_certificado   (excecao)
     *   2) evento.modelo_certificado   (padrao do evento)
     *   3) NULL                        -> chamador mantem o comportamento atual
     *
     * Devolve o cod_certificado_modelo ou false. Nao lanca excecao: se nao ha
     * modelo configurado, o certificado tem que continuar saindo como sempre.
     */
    public function resolverModelo($codPerfil = null, $debug = false)
    {
        return $this->objDAO->resolverModelo($this, $codPerfil, $debug);
    }

    public function salvar($debug = false)
    {
        $this->validatorObj();

        if ((int) $this->getCodCertificadoModelo() > 0) {
            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Modelo salvo com sucesso.");
            }
            throw new Exception("Nao foi possivel salvar o modelo.");
        }
        if ($this->objDAO->insert($this, $debug)) {
            throw new OKException("Modelo criado com sucesso.");
        }
        throw new Exception("Nao foi possivel criar o modelo.");
    }

    /**
     * Exclusao logica (status EXCLUIDA). Nao apaga a linha: evento ou perfil
     * podem estar apontando para este modelo, e um DELETE deixaria o
     * certificado sem layout no meio de uma emissao.
     */
    public function excluir($debug = false)
    {
        $this->validatorExcluir();
        if ($this->objDAO->desativar($this, $debug)) {
            throw new OKException("Modelo excluido com sucesso.");
        }
        throw new Exception("Nao foi possivel excluir o modelo.");
    }
}

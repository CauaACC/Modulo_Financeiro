<?php
class GestaoAcessos extends GestaoAcessosVO
{
    private $objDAO;

    function __construct()
    {
        $this->setCodEvento(COD_EVENTO);
        $this->objDAO = new GestaoAcessosDAO();
    }

    /* ========== Tab 1: Eventos do Gestor ========== */
    function getEventosByGestor($debug = false)
    {
        return $this->objDAO->getEventosByGestor($this->getGestor(), $debug);
    }

    /* ========== Tab 2: Usuários do Gestor ========== */
    function getUsuariosByGestor($debug = false)
    {
        return $this->objDAO->getUsuariosByGestor($this->getGestor(), $debug);
    }

    /* ========== Tab 3: Usuários do Evento ========== */
    function getUsuariosByEvento($debug = false)
    {
        return $this->objDAO->getUsuariosByEvento($this->getCodEvento(), $debug);
    }

    /* ========== Tab 4: Eventos do Usuário ========== */
    function getEventosByUsuario($debug = false)
    {
        return $this->objDAO->getEventosByUsuario($this->getCodUsuario(), $debug);
    }

    /* ========== CRUD ========== */
    function adicionarUsuarioEvento($debug = false)
    {
        $this->validarAdicionarUsuarioEvento();
        $this->objDAO->adicionarUsuarioEvento($this->getCodUsuario(), $this->getCodEvento(), $debug);
    }

    function removerUsuarioEvento($debug = false)
    {
        $this->validarAdicionarUsuarioEvento();
        $this->objDAO->removerUsuarioEvento($this->getCodUsuario(), $this->getCodEvento(), $debug);
    }

    /* ========== Modal: Grupos ========== */
    function getGruposUsuarioEvento($debug = false)
    {
        return $this->objDAO->getGruposUsuarioEvento($this->getCodUsuario(), $this->getCodEvento(), $debug);
    }

    function getTodosGrupos($debug = false)
    {
        return $this->objDAO->getTodosGrupos($debug);
    }

    function copiarGruposEntreEventos($codEventoOrigem, $codEventoDestino, $debug = false)
    {
        return $this->objDAO->copiarGruposEntreEventos($this->getCodUsuario(), $codEventoOrigem, $codEventoDestino, $debug);
    }

    /* ========== Selects ========== */
    function getGestoresDistintos($debug = false)
    {
        return $this->objDAO->getGestoresDistintos($debug);
    }

    function getEventosAtivos($debug = false)
    {
        return $this->objDAO->getEventosAtivos($debug);
    }

    function getTodosUsuarios($debug = false)
    {
        return $this->objDAO->getTodosUsuarios($debug);
    }

    function getEventosDisponiveisUsuario($debug = false)
    {
        return $this->objDAO->getEventosDisponiveisUsuario($this->getCodUsuario(), $debug);
    }

    function getUsuariosDoEvento($debug = false)
    {
        return $this->objDAO->getUsuariosDoEvento($this->getCodEvento(), $debug);
    }

    function getInscritoAssociado($codUsuario, $codEvento, $debug = false)
    {
        return $this->objDAO->getInscritoAssociado($codUsuario, $codEvento, $debug);
    }

    function buscarInscritoPorEmailOuCpf($email, $cpf, $codEvento, $debug = false)
    {
        return $this->objDAO->buscarInscritoPorEmailOuCpf($email, $cpf, $codEvento, $debug);
    }

    function getDadosUsuarioParaInscricao($codUsuario, $debug = false)
    {
        return $this->objDAO->getDadosUsuarioParaInscricao($codUsuario, $debug);
    }

    function criarInscrito($codEvento, $nome, $email, $cpf, $codPerfil, $codUsuario, $debug = false)
    {
        return $this->objDAO->criarInscrito($codEvento, $nome, $email, $cpf, $codPerfil, $codUsuario, $debug);
    }

    function atualizarCodInscrito($codUsuario, $codEvento, $codInscrito, $debug = false)
    {
        return $this->objDAO->atualizarCodInscrito($codUsuario, $codEvento, $codInscrito, $debug);
    }

    function removerCodInscrito($codUsuario, $codEvento, $debug = false)
    {
        return $this->objDAO->removerCodInscrito($codUsuario, $codEvento, $debug);
    }

    function importarUsuariosDeEvento($codEventoOrigem, $codEventoDestino, $debug = false)
    {
        return $this->objDAO->importarUsuariosDeEvento($codEventoOrigem, $codEventoDestino, $debug);
    }
}

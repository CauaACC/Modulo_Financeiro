<?php
/**
 * Stub mínimo da classe Evento para compatibilidade.
 * No sistema principal, Evento carrega dados de tab_eventos.
 * Aqui só mantemos o necessário para o módulo financeiro funcionar.
 */
class Evento {
    private $codEvento;
    private $gestor;

    public function setCodEvento($cod) { $this->codEvento = $cod; }
    public function getCodEvento() { return $this->codEvento; }

    public function getEventoById($debug = false) {
        // Stub: o módulo financeiro usa getEvento() nos domain classes
        // mas só chama getGestor() na tabela de listagem
    }

    public function getGestor() { return $this->gestor; }
    public function setGestor($g) { $this->gestor = $g; }

    public function controlarAcessoProposta($tipo = 'boolean') {
        return true;
    }
}

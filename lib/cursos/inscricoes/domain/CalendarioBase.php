<?php
class CalendarioBase extends CalendarioBaseVO
{
    private $objDAO;

    function __construct()
    {
        $this->ano = Date('Y');
        $this->objDAO = new CalendarioBaseDAO();
    }

    public function CalendarioBaseExiste($debug = false)
    {
        if (!$this->getId()) {
            throw new Exception('Código inválido ou não informado: ' . $this->getId());
        }
        return $this->objDAO->getByIdExiste($this, $debug);
    }

    public function carregarListaCalendarioBaseByIdEvento($debug = false)
    {
        $this->objDAO->carregarListaCalendarioBaseByIdEvento($this, $debug);
        if ($this->{"getListaCalendarioBases"}()) {
            return true;
        } else {
            throw new Exception("Evento não possui registros associados.");
        }
    }

    public function getAnosDisponiveis($debug = false)
    {
        return $this->objDAO->carregarAnosDisponiveis($debug);
    }

    public function carregarListaCalendarioBaseByIdEventoRegAtividade($debug = false)
    {
        $this->objDAO->carregarListaCalendarioBaseByIdEventoRegAtividade($this, $debug);
        if ($this->{"getListaCalendarioBases"}()) {
            return true;
        } else {
            throw new Exception("Evento não possui registros associados.");
        }
    }

    public function salvar($debug = false)
    {
        try {
            if (is_null($this->getId()) || $this->getId() == "") {
                $this->validatorInsert();
                if ($this->objDAO->insert($this, $debug)) {
                    throw new OKException("CalendarioBase gravado com sucesso.");
                } else {
                    throw new Exception("CalendarioBase não foi gravado com sucesso.");
                }
            } else {
                $this->validatorUpdate();
                $this->CalendarioBaseExiste($debug);
                if ($this->objDAO->update($this, $debug)) {
                    throw new OKException("CalendarioBase atualizado com sucesso.");
                } else {
                    throw new Exception("CalendarioBase não foi atualizado.");
                }
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function excluir($debug = false)
    {
        $this->validatorExcluir();
        $this->CalendarioBaseExiste($debug);
        if ($this->objDAO->delete($this, $debug)) {
            throw new OKException("CalendarioBase cód. " . $this->getId() . " foi excluído com sucesso");
        } else {
            throw new Exception("CalendarioBase cód. " . $this->getId() . " NÃO foi excluído");
        }
    }

    public function getCalendarioBaseById($debug = false)
    {
        $this->objDAO->getById($this, $debug);
    }

    public function gerarCalendario($ano, $debug = false)
    {
        if (!$ano || $ano < 2020 || $ano > 2050) {
            throw new Exception("Ano inválido.");
        }
        if ($this->objDAO->gerarCalendarioSQL($ano, $debug)) {
            $total = $this->objDAO->contarAno($ano, $debug);
            throw new OKException("Calendário $ano gerado com sucesso. $total dias criados.");
        } else {
            throw new Exception("Erro ao gerar calendário para o ano $ano.");
        }
    }
}
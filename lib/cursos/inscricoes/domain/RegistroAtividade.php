<?php

class RegistroAtividade extends RegistroAtividadeVO {

    private $objDAO;

    function __construct() {
        if ((!isset($_SESSION['_cod_inscrito']) || empty($_SESSION['_cod_inscrito']))and(!isset($_SESSION['_cod_usuario']) || empty($_SESSION['_cod_usuario']))) {
            throw new Exception("Usuário não está logado. Por favor, faça login e tente novamente.");
        }        
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new RegistroAtividadeDAO();
    }

    public function RegistroAtividadeExiste() {
        if (!$this->getCodRegAtividade()) {
            throw new Exception('Código da atividade inválido ou não informado.');
        }
        if (!$this->objDAO->getByIdExiste($this)) {
            throw new Exception("Nenhuma Atividade foi encontrada para o código informado: " . $this->getCodRegAtividade() . ".");
            //throw new Exception("Id informado para deleção não existe");
        }
    }

    public function novo($debug = false) {
        
    }

    public function getPessoasValidadasPorMesPerfil($debug=false) {
        return $this->objDAO->carregarPessoasValidadasPorMesPerfil($this->getCodEvento(), $debug);
    }

    /**
     * Snapshot do estado atual: inscritos com status_processo='PARTICIPANDO'
     * por perfil. Nao filtra por mes/ano — e a foto do "agora".
     */
    public function getParticipandoPorPerfil($debug=false) {
        return $this->objDAO->carregarParticipandoPorPerfil($this->getCodEvento(), $debug);
    }

    public function getArrayDiasMesIdEvento($mes, $ano, $codEvento, $debug=false) {
        $objCB = new CalendarioBase();
        $objCB->setAno($ano);
        $objCB->setMes($mes);
        $objCB->setCodEvento($codEvento);
        $objCB->carregarListaCalendarioBaseByIdEventoRegAtividade($debug);
        $lista = $objCB->getListaCalendarioBases();
        if (!$lista) {
            throw new Exception("Nenhum registro de calendário encontrado para o evento: " . $this->getCodEvento() . " no ano: " . $this->getAno() . " e mês: " . $this->getMes() . ".");
        }   
        $listaDiasMes = [];
        // dd($lista);
        foreach ($lista as $objL) {
            if($objL->getTipoDia() != 'Util') {
                continue; // Ignora feriados e finais de semana
            }
            $listaDiasMes[] = $objL->getData(); // assumindo retorno no formato "YYYY-MM-DD"
        }
        return $listaDiasMes;
    }

    public function salvar($debug = false) {
        
        $this->validatorObj();
        if (is_null($this->getCodRegAtividade()) or $this->getCodRegAtividade() == "") {
            /** @todo 
             * Não sei se isso vai ficar aqui, mas por hora vamos deixa assim
             * nesse caso estará setando o cod_inscrito de quem estiver carregado na sessão.
             * dessa forma, sempre que for inserir um novo registro, o registro pertencerá ao cara que esta logado no momento
             */
            $this->setCodInscrito($_SESSION['_cod_inscrito']);
            if (COD_EVENTO != 115 && $this->objDAO->getByDiaRegistroExiste($this)) {
                throw new Exception("Já existe um registro de atividade para este dia. Altere o dia ou selecione o dia na listagem abaixo.");
            }
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Atividade salva com sucesso.");
            } else {
                throw new Exception("Novo Atividade não foi salva.");
            }
        } else {
            $this->RegistroAtividadeExiste($debug);

            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Atividade foi atualizada com sucesso.");
            } else {
                throw new Exception("Atividade atual não foi atualizada.");
            }
        }
    }

    public function salvarAvaliacao($debug = false) {
        
        $this->validatorAvaliacaoObj();
        if (is_null($this->getCodRegAtividade()) or $this->getCodRegAtividade() == "") {
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Atividade salva com sucesso.");
            } else {
                throw new Exception("Novo Atividade não foi salva.");
            }
        } else {
            $this->RegistroAtividadeExiste($debug);

            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Atividade foi atualizada com sucesso.");
            } else {
                throw new Exception("Atividade atual não foi atualizada.");
            }
        }
    }

    public function excluir($debug = false) {
        $this->validatorEcluir();
        if ($this->objDAO->delete($this, $debug)) {
            throw new OKException("Atividade cód:" . $this->getCodRegAtividade() . " foi excluída com sucesso.");
        } else {
            throw new Exception("Atividade cód:" . $this->getCodRegAtividade() . " não foi excluída.");
        }
    }

    public function getRegistroAtividadeById($debug = false) {
        $this->objDAO->getById($this, $debug);
    }

    public function getListaRegistroAtividadesByIdEvento($debug = false) {
        if (!$this->getCodEvento()) {
            throw new Exception("Id do evento não informado.");
        }
        $this->objDAO->carregarListaRegistroAtividadesByIdEvento($this, $debug);
        if ($this->getListaRegistroAtividades()) {
            return $this->getListaRegistroAtividades();
        } else {
            throw new Exception("Evento não possui Atividade cadastrada.");
        }
    }
    public function getListaRegistroAtividadesByIdCodInscrito($condicoes = false,$debug = false) {
        if (!$this->getCodInscrito()) {
            throw new Exception("Id do Inscrito não informado.");
        }
        $this->objDAO->carregarListaRegistroAtividadesByIdCodInscrito($this, $condicoes, $debug);
        if ($this->getListaRegistroAtividades()) {
            return $this->getListaRegistroAtividades();
        } else {
            throw new Exception("O inscrito não possui Atividade cadastrada.");
        }
    }
    public function getListaRegistroAtividadesByNre($debug = false) {
        // if(($_SERVER['REMOTE_ADDR'] != '177.82.0.28')and($_SERVER['REMOTE_ADDR'] != '127.0.0.1')){
        //     if (!$this->getCodNre()) {
        //         throw new Exception("Nenhuma atividade disponível para avaliação.".$_SERVER['REMOTE_ADDR']);
        //     }
            $listaOficinas = $this->getListaAtividadesByIdAvaliador();
            $listaCodNre = [];
            foreach ($listaOficinas as $oficina) {
                $listaCodNre[] = $oficina->getCodOficina();
            }
            $this->setListaCodNre($listaCodNre);
            if (!$this->getListaCodNre()) {
                throw new Exception("Nenhuma atividade disponível para avaliação.".$_SERVER['REMOTE_ADDR']);
            }
        // }
        $this->objDAO->carregarListaRegistroAtividadesByIdNre($this, $debug);
        if ($this->getListaRegistroAtividades()) {
            return $this->getListaRegistroAtividades();
        } else {
            throw new Exception("O Avaliador não possui Atividade para avaliar.");
        }
    }
    public function getListaRegistroAtividadesByIdCodInscritoLogado($debug = false) {
        $this->setCodInscrito($_SESSION['_cod_inscrito']);
        $this->objDAO->carregarListaRegistroAtividadesByIdCodInscrito($this, $debug);
    }
    /** carrega lista de atividades das quais o avaliador da nre esta associado */
    public function getListaAtividadesByIdAvaliador($debug = false) {
        $objInscrito = new Inscrito();
        $objInscrito->setCodInscrito($_SESSION['_cod_inscrito']);
        $objInscrito->setCodCodEvento(COD_EVENTO);
        $objInscrito->carregarListaOficinas();
        return $objInscrito->getListaOficinas();
    }

    // Lista todas atividades do evento, com filtros (gerente/status/mes/ano).
    // Usada pela tela de avaliacao do coordenador pedagogico.
    public function getListaByEvento($filtros = array(), $debug = false) {
        $this->objDAO->carregarListaByEvento($this, $filtros, $debug);
        return $this->getListaRegistroAtividades() ? $this->getListaRegistroAtividades() : array();
    }

    public function getGerentesComAtividade($debug = false) {
        return $this->objDAO->carregarGerentesComAtividade($this->getCodEvento(), $debug);
    }

    public function getLinhasParaExportacao($codInscrito, $dataInicio, $dataFim, $debug = false) {
        if (empty($dataInicio) || empty($dataFim)) {
            throw new Exception("Informe a data inicial e a data final.");
        }
        $tsInicio = strtotime($dataInicio);
        $tsFim = strtotime($dataFim);
        if (!$tsInicio || !$tsFim) {
            throw new Exception("Datas invalidas.");
        }
        if ($tsFim < $tsInicio) {
            throw new Exception("A data final nao pode ser anterior a data inicial.");
        }
        // Why: evitar export gigante; coordenador pode rodar varias vezes em janelas menores.
        $diffDias = ($tsFim - $tsInicio) / 86400;
        if ($diffDias > 366) {
            throw new Exception("O intervalo nao pode ultrapassar 12 meses.");
        }
        return $this->objDAO->getRegistrosParaExportacao(
            $this->getCodEvento(), $codInscrito, $dataInicio, $dataFim, $debug
        );
    }

}


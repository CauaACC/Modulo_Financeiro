<?php

class RegistroOrientacao extends RegistroOrientacaoVO {

    private $objDAO;

    function __construct() {
        if ((!isset($_SESSION['_cod_inscrito']) || empty($_SESSION['_cod_inscrito'])) && (!isset($_SESSION['_cod_usuario']) || empty($_SESSION['_cod_usuario']))) {
            throw new Exception("Usuario nao esta logado. Por favor, faca login e tente novamente.");
        }
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new RegistroOrientacaoDAO();
    }

    public function RegistroOrientacaoExiste() {
        if (!$this->getCodRegistro()) {
            throw new Exception('Codigo do registro invalido ou nao informado.');
        }
        if (!$this->objDAO->getByIdExiste($this)) {
            throw new Exception("Nenhum registro encontrado para o codigo: " . $this->getCodRegistro() . ".");
        }
    }

    public function getOuCriarByData($debug = false) {
        $this->validatorObj();
        if ($this->objDAO->getByData($this, $debug)) {
            return $this->getCodRegistro();
        }
        if ($this->objDAO->insert($this, $debug)) {
            return $this->getCodRegistro();
        }
        throw new Exception("Nao foi possivel criar o registro do dia.");
    }

    public function carregarDia($debug = false) {
        $this->validatorObj();
        return $this->objDAO->getByData($this, $debug);
    }

    public function salvarDia($debug = false) {
        $this->validatorObj();
        // Why: getByData() chama setAll() e sobrescreve atividadesDia/descricaoDia
        // recém-setados pelo controller com os valores existentes no banco.
        // Preservamos os novos valores antes de buscar e restauramos antes do update.
        $novasAtividades = $this->getAtividadesDia();
        $novaDescricao = $this->getDescricaoDia();
        if ($this->objDAO->getByData($this, $debug)) {
            $this->setAtividadesDia($novasAtividades);
            $this->setDescricaoDia($novaDescricao);
            if (!$this->objDAO->update($this, $debug)) {
                throw new Exception("Registro nao foi atualizado.");
            }
        } else {
            if (!$this->objDAO->insert($this, $debug)) {
                throw new Exception("Registro nao foi salvo.");
            }
        }
        throw new OKException("Registro do dia salvo com sucesso.");
    }

    public function salvarItemPes($item, $codRegistroPes = null, $debug = false) {
        $this->validatorItemPes($item);
        $codRegistro = $this->getOuCriarByData($debug);
        if ($codRegistroPes) {
            if (!$this->objDAO->updateItemPes($codRegistroPes, $this->getCodEvento(), $item, $debug)) {
                throw new Exception("Supervisao de PES nao foi atualizada.");
            }
        } else {
            $novoId = $this->objDAO->insertItemPes($codRegistro, $this->getCodEvento(), $item, $debug);
            if (!$novoId) {
                throw new Exception("Supervisao de PES nao foi salva.");
            }
            $codRegistroPes = $novoId;
        }
        throw new OKException("Supervisao de PES salva.");
    }

    public function excluirItemPes($codRegistroPes, $debug = false) {
        if (empty($codRegistroPes)) {
            throw new Exception("Codigo da supervisao de PES nao informado.");
        }
        if (!$this->objDAO->deleteItemPes($codRegistroPes, $this->getCodEvento(), $debug)) {
            throw new Exception("Supervisao de PES nao foi excluida.");
        }
        throw new OKException("Supervisao de PES excluida.");
    }

    public function salvarItemCrianca($item, $codRegistroCrianca = null, $debug = false) {
        $this->validatorItemCrianca($item);
        $codRegistro = $this->getOuCriarByData($debug);
        if ($codRegistroCrianca) {
            if (!$this->objDAO->updateItemCrianca($codRegistroCrianca, $this->getCodEvento(), $item, $debug)) {
                throw new Exception("Intervencao com crianca nao foi atualizada.");
            }
        } else {
            $novoId = $this->objDAO->insertItemCrianca($codRegistro, $this->getCodEvento(), $item, $debug);
            if (!$novoId) {
                throw new Exception("Intervencao com crianca nao foi salva.");
            }
            $codRegistroCrianca = $novoId;
        }
        throw new OKException("Intervencao com crianca salva.");
    }

    public function excluirItemCrianca($codRegistroCrianca, $debug = false) {
        if (empty($codRegistroCrianca)) {
            throw new Exception("Codigo da intervencao nao informado.");
        }
        if (!$this->objDAO->deleteItemCrianca($codRegistroCrianca, $this->getCodEvento(), $debug)) {
            throw new Exception("Intervencao com crianca nao foi excluida.");
        }
        throw new OKException("Intervencao com crianca excluida.");
    }

    public function temFilhos($debug = false) {
        $pes = $this->objDAO->carregarItensPes($this->getCodRegistro(), $this->getCodEvento(), $debug);
        if (!empty($pes)) return true;
        $criancas = $this->objDAO->carregarItensCrianca($this->getCodRegistro(), $this->getCodEvento(), $debug);
        return !empty($criancas);
    }

    // Exclusão sem cascade. Se tiver filhos, bloqueia.
    // Why: decidido que nenhum papel (supervisor ou admin) pode excluir em cascata pela UI.
    //      Filhos precisam ser excluídos individualmente antes.
    public function excluirSemFilhos($debug = false) {
        $this->validatorExcluir();
        if ($this->temFilhos($debug)) {
            throw new Exception("Este registro possui supervisoes de PES ou intervencoes com criancas. Exclua os itens primeiro ou solicite ao administrador.");
        }
        if ($this->objDAO->delete($this, $debug)) {
            throw new OKException("Registro excluido com sucesso.");
        } else {
            throw new Exception("Registro nao foi excluido.");
        }
    }

    public function getRegistroOrientacaoById($debug = false) {
        $this->objDAO->getById($this, $debug);
    }

    public function getListaRegistrosBySupervisor($debug = false) {
        if (!$this->getCodSupervisor()) {
            throw new Exception("Codigo do supervisor nao informado.");
        }
        return $this->objDAO->carregarListaBySupervisor($this, $debug);
    }

    public function getItensPesByRegistro($debug = false) {
        return $this->objDAO->carregarItensPes($this->getCodRegistro(), $this->getCodEvento(), $debug);
    }

    public function getItensCriancaByRegistro($debug = false) {
        return $this->objDAO->carregarItensCrianca($this->getCodRegistro(), $this->getCodEvento(), $debug);
    }

    public function getItemPesById($codRegistroPes, $debug = false) {
        return $this->objDAO->getItemPesById($codRegistroPes, $this->getCodEvento(), $debug);
    }

    public function getItemCriancaById($codRegistroCrianca, $debug = false) {
        return $this->objDAO->getItemCriancaById($codRegistroCrianca, $this->getCodEvento(), $debug);
    }

    public function getListaPesDasEscolas($codSupervisor, $debug = false) {
        return $this->objDAO->carregarPesDasEscolasSupervisor($this->getCodEvento(), $codSupervisor, $debug);
    }

    public function getListaCriancasDasEscolas($codSupervisor, $debug = false) {
        return $this->objDAO->carregarCriancasDasEscolasSupervisor($this->getCodEvento(), $codSupervisor, $debug);
    }

    public function getListaSupervisores($debug = false) {
        return $this->objDAO->carregarListaSupervisores($this->getCodEvento(), $debug);
    }

    // --- Consulta consolidada (admin): por PES / por Aluno ---

    public function getListaPesComOrientacoes($debug = false) {
        return $this->objDAO->carregarPesComOrientacoes($this->getCodEvento(), $debug);
    }

    public function getListaAlunosComOrientacoes($debug = false) {
        return $this->objDAO->carregarAlunosComOrientacoes($this->getCodEvento(), $debug);
    }

    public function getOrientacoesPorAluno($codAluno, $debug = false) {
        if (empty($codAluno)) {
            throw new Exception("Codigo do aluno nao informado.");
        }
        return $this->objDAO->carregarOrientacoesPorAluno($this->getCodEvento(), $codAluno, $debug);
    }

    public function getLinhasParaExportacao($codSupervisor, $dataInicio, $dataFim, $debug = false) {
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
        // Why: evitar export gigante que trava o servidor; coordenador pode rodar varias vezes.
        $diffDias = ($tsFim - $tsInicio) / 86400;
        if ($diffDias > 366) {
            throw new Exception("O intervalo nao pode ultrapassar 12 meses.");
        }
        // codSupervisor opcional: vazio/0 = todos os supervisores.
        return $this->objDAO->getRegistrosParaExportacao(
            $this->getCodEvento(), $codSupervisor, $dataInicio, $dataFim, $debug
        );
    }

    public function getNomeSupervisor($debug = false) {
        if (!$this->getCodSupervisor()) return '';
        return $this->objDAO->getNomeSupervisor($this->getCodSupervisor(), $debug);
    }

    // --- Orientacoes recebidas pelo PES (area do inscrito) ---

    public function getOrientacoesRecebidas($codInscritoPes, $debug = false) {
        if (empty($codInscritoPes)) {
            throw new Exception("Codigo do PES nao informado.");
        }
        return $this->objDAO->carregarOrientacoesPorPes($this->getCodEvento(), $codInscritoPes, $debug);
    }

    public function contarOrientacoesNaoLidas($codInscritoPes, $debug = false) {
        if (empty($codInscritoPes)) return 0;
        return $this->objDAO->contarOrientacoesNaoLidas($this->getCodEvento(), $codInscritoPes, $debug);
    }

    public function marcarOrientacaoLida($codRegistroPes, $codInscritoPes, $debug = false) {
        if (empty($codRegistroPes) || empty($codInscritoPes)) {
            throw new Exception("Parametros invalidos.");
        }
        if (!$this->objDAO->marcarOrientacaoComoLida($codRegistroPes, $this->getCodEvento(), $codInscritoPes, $debug)) {
            throw new Exception("Nao foi possivel registrar a leitura.");
        }
        throw new OKException("Orientacao registrada como lida.");
    }

    // --- Orientacoes por crianca (area do PES) ---

    public function getOrientacoesCriancaRecebidas($codInscritoPes, $debug = false) {
        if (empty($codInscritoPes)) {
            throw new Exception("Codigo do PES nao informado.");
        }
        return $this->objDAO->carregarOrientacoesCriancaPorPes($this->getCodEvento(), $codInscritoPes, $debug);
    }

    public function contarOrientacoesCriancaNaoLidas($codInscritoPes, $debug = false) {
        if (empty($codInscritoPes)) return 0;
        return $this->objDAO->contarOrientacoesCriancaNaoLidas($this->getCodEvento(), $codInscritoPes, $debug);
    }

    public function marcarOrientacaoCriancaLida($codRegistroCrianca, $codInscritoPes, $debug = false) {
        if (empty($codRegistroCrianca) || empty($codInscritoPes)) {
            throw new Exception("Parametros invalidos.");
        }
        if (!$this->objDAO->marcarOrientacaoCriancaComoLida($codRegistroCrianca, $this->getCodEvento(), $codInscritoPes, $debug)) {
            throw new Exception("Nao foi possivel registrar a leitura.");
        }
        throw new OKException("Orientacao registrada como lida.");
    }

}

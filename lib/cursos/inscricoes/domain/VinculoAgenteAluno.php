<?php

class VinculoAgenteAluno extends VinculoAgenteAlunoVO {

    private $objDAO;

    function __construct() {
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new VinculoAgenteAlunoDAO();
    }

    public function VinculoAgenteAlunoExiste() {
        if (!$this->getCodVinculo()) {
            throw new Exception('Codigo do vinculo invalido ou nao informado.');
        }
        if (!$this->objDAO->getByIdExiste($this)) {
            throw new Exception("Nenhum vinculo foi encontrado para o codigo informado: " . $this->getCodVinculo() . ".");
        }
    }

    public function validarLimitePES($debug = false) {
        $contagem = $this->objDAO->contarVinculosPorAgenteTurno($this, $debug);

        if ($contagem['total'] >= 4) {
            throw new Exception("Limite atingido: o agente ja possui 3 alunos vinculados (maximo permitido).");
        }

        // Verificar turno do aluno que está sendo vinculado
        $objAluno = new AlunoPublico();
        $objAluno->setCodAluno($this->getCodAluno());
        $objAluno->getAlunoPublicoById($debug);
        $turnoAluno = $objAluno->getTurno();

        if ($turnoAluno && $turnoAluno != 'Integral') {
            $totalTurno = isset($contagem[$turnoAluno]) ? $contagem[$turnoAluno] : 0;
            if ($totalTurno >= 2) {
                throw new Exception("Limite atingido: o agente ja possui 2 alunos no turno " . $turnoAluno . " (maximo permitido por turno).");
            }
        }
    }

    /**
     * Valida limite ANTES de cadastrar o aluno (usa turno informado direto, sem buscar no banco)
     */
    public function validarLimitePreCadastro($turno, $debug = false) {
        $contagem = $this->objDAO->contarVinculosPorAgenteTurno($this, $debug);

        if ($contagem['total'] >= 4) {
            throw new Exception("Limite atingido: o agente ja possui 3 alunos vinculados (maximo permitido).");
        }

        if ($turno && $turno != 'Integral') {
            $totalTurno = isset($contagem[$turno]) ? $contagem[$turno] : 0;
            if ($totalTurno >= 2) {
                throw new Exception("Limite atingido: o agente ja possui 2 alunos no turno " . $turno . " (maximo permitido por turno).");
            }
        }
    }

    public function salvar($debug = false) {
        $this->validatorObj();
        if (is_null($this->getCodVinculo()) or $this->getCodVinculo() == "") {
            $this->validarLimitePES($debug);
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Aluno associado ao agente com sucesso.");
            } else {
                throw new Exception("Vinculo nao foi salvo.");
            }
        } else {
            $this->VinculoAgenteAlunoExiste($debug);
            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Vinculo foi atualizado com sucesso.");
            } else {
                throw new Exception("Vinculo atual nao foi atualizado.");
            }
        }
    }

    public function excluir($debug = false) {
        $this->validatorExcluir();
        if ($this->objDAO->delete($this, $debug)) {
            throw new OKException("Vinculo cod:" . $this->getCodVinculo() . " foi desassociado com sucesso.");
        } else {
            throw new Exception("Vinculo cod:" . $this->getCodVinculo() . " nao foi desassociado.");
        }
    }

    public function getVinculoAgenteAlunoById($debug = false) {
        $this->objDAO->getById($this, $debug);
    }

    public function getMapaAgenteAluno($debug = false) {
        return $this->objDAO->carregarMapaAgenteAluno($this->getCodEvento(), $debug);
    }

    public function getListaVinculosByAgente($debug = false) {
        if (!$this->getCodAgente()) {
            throw new Exception("Codigo do agente nao informado.");
        }
        $this->objDAO->carregarListaVinculosByAgente($this, $debug);
        if ($this->getListaVinculos()) {
            return $this->getListaVinculos();
        } else {
            throw new Exception("Agente nao possui alunos vinculados.");
        }
    }

    /**
     * Cache dos perfis marcados como agente pedagogico, por evento.
     * @var array  [codEvento => [codPerfil => true]]
     */
    private static $perfisAgente = array();

    /**
     * Perfis liberados de origem, por codigo.
     *
     * O 614 (PES do Edital 032/2025) continua aqui de proposito: e o perfil de
     * quem ja trabalha no projeto. Se dependesse da marca no banco, um ALTER que
     * falhasse ou um UPDATE que nao casasse tirariam o acesso de todo mundo de
     * uma vez. Mantido como piso, o deploy nao tem como causar regressao.
     * Perfil novo NAO entra aqui -- usa a marca (ver perfilEhAgentePedagogico).
     */
    const PERFIS_AGENTE_LEGADO = array('614');

    /**
     * O perfil informado esta marcado como agente pedagogico?
     *
     * A marca vive em tab_opcoes_perfis.flg_agente_pedagogico, e nao numa lista
     * de codigos no codigo-fonte, porque cod_perfil e AUTO_INCREMENT: o mesmo
     * perfil nasce com numero diferente em cada banco. Cada edital novo passa a
     * ser so um perfil marcado, sem mexer aqui.
     *
     * @param string $codPerfil
     * @param bool $debug
     * @return bool
     */
    public static function perfilEhAgentePedagogico($codPerfil, $debug = false) {
        if ($codPerfil === '' or is_null($codPerfil)) {
            return false;
        }
        $codEvento = COD_EVENTO;
        if (!isset(self::$perfisAgente[$codEvento])) {
            self::$perfisAgente[$codEvento] = array();

            ConexaoDB::getInstance();
            $conex = ConexaoDB::getConexaoDB();
            $sql = sprintf("SELECT cod_perfil FROM tab_opcoes_perfis
                WHERE cod_evento = '%s' AND flg_agente_pedagogico = 1", $codEvento);
            $conex->debug = $debug;
            $rs = $conex->Execute($sql);
            if ($rs && !$rs->EOF) {
                foreach ($rs as $row) {
                    self::$perfisAgente[$codEvento][(string) $row['cod_perfil']] = true;
                }
            }
        }
        return isset(self::$perfisAgente[$codEvento][(string) $codPerfil]);
    }

    /**
     * Verifica se um inscrito é elegível como agente pedagógico.
     * Regra idêntica à de inscricao_congresso_ok_menu_principal_115.php (exibirRegistroPedagogico):
     *   - PARTICIPANDO num perfil legado (614) OU num perfil marcado no banco, OU
     *   - Exceção individual (cod_inscrito = 40920)
     *   - E NÃO DESISTENTE
     *
     * O ramo do 614 e' o comportamento antigo, preservado inteiro: quem ja
     * trabalhava continua entrando exatamente como antes, sem depender de dado
     * novo. A marca no banco so ACRESCENTA perfis (Edital 003/2026 em diante).
     *
     * @param string $codInscrito
     * @param string $statusProcesso
     * @param string $codPerfil
     * @return bool
     */
    public static function isAgentePedagogico($codInscrito, $statusProcesso, $codPerfil) {
        if ($statusProcesso == 'DESISTENTE') {
            return false;
        }
        $isExcecao = ($codInscrito == '40920');
        $perfilLiberado = in_array($codPerfil, self::PERFIS_AGENTE_LEGADO)
                          || self::perfilEhAgentePedagogico($codPerfil);
        return (($statusProcesso == 'PARTICIPANDO') && $perfilLiberado) || $isExcecao;
    }

    /**
     * Retorna lista de inscritos elegíveis como agentes pedagógicos para o evento atual.
     * Regra de contratação: classificados 1-150, ou CONVOCADO, ou exceção 40920, excluindo DESISTENTE.
     *
     * @param bool $debug
     * @return array Array de arrays com 'codInscrito', 'nome' e 'statusProcesso'
     */
    public static function listarAgentesPedagogicos($debug = false) {
        ConexaoDB::getInstance();
        $conex = ConexaoDB::getConexaoDB();

        $sql = sprintf("SELECT i.cod_inscrito, i.nome_inscrito, i.status_processo, i.cpf
            FROM tab_inscritos i
            WHERE i.cod_evento = '%s'
            AND i.status != 'EXCLUIDA'
            AND (i.status_processo = 'PARTICIPANDO' OR i.cod_inscrito = '40920')
            ORDER BY i.nome_inscrito ASC", COD_EVENTO);

        $conex->debug = $debug;
        $rs = $conex->Execute($sql);

        $agentes = array();
        if ($rs && !$rs->EOF) {
            foreach ($rs as $row) {
                $agentes[] = array(
                    'codInscrito'    => $row['cod_inscrito'],
                    'nome'           => $row['nome_inscrito'],
                    'statusProcesso' => $row['status_processo'],
                    'cpf'            => $row['cpf']
                );
            }
        }
        return $agentes;
    }

    /**
     * Retorna lista de agentes pedagógicos com contagem de alunos vinculados.
     *
     * @param bool $debug
     * @return array
     */
    public static function listarAgentesPedagogicosComAlunos($debug = false) {
        // 1. Buscar agentes ativos (PARTICIPANDO)
        $agentes = self::listarAgentesPedagogicos($debug);

        // Marcar todos como ativos
        foreach ($agentes as &$ag) {
            $ag['ativo'] = 1;
        }
        unset($ag);

        ConexaoDB::getInstance();
        $conex = ConexaoDB::getConexaoDB();

        // 2. Contagem de alunos por agente
        $sql = sprintf("SELECT v.cod_agente, COUNT(*) AS total_alunos
            FROM acad_lig_agente_aluno v
            INNER JOIN acad_aluno_contexto a ON a.cod_aluno = v.cod_aluno AND a.cod_evento = v.cod_evento
            WHERE v.cod_evento = '%s' AND v.status = 'ATIVADA' AND a.status = 'ATIVADA'
            GROUP BY v.cod_agente", COD_EVENTO);

        $conex->debug = $debug;
        $rs = $conex->Execute($sql);

        $contagens = array();
        if ($rs && !$rs->EOF) {
            foreach ($rs as $row) {
                $contagens[$row['cod_agente']] = intval($row['total_alunos']);
            }
        }

        foreach ($agentes as &$ag) {
            $ag['totalAlunos'] = isset($contagens[$ag['codInscrito']]) ? $contagens[$ag['codInscrito']] : 0;
        }
        unset($ag);

        // 3. Buscar inativos que tenham alunos vinculados (não PARTICIPANDO, não EXCLUIDA)
        $codAtivos = array();
        foreach ($agentes as $ag) {
            $codAtivos[] = $ag['codInscrito'];
        }

        $sqlInativos = sprintf("SELECT DISTINCT i.cod_inscrito, i.nome_inscrito, i.status_processo, i.cpf
            FROM tab_inscritos i
            INNER JOIN acad_lig_agente_aluno v ON v.cod_agente = i.cod_inscrito AND v.cod_evento = i.cod_evento AND v.status = 'ATIVADA'
            WHERE i.cod_evento = '%s'
            AND i.status != 'EXCLUIDA'
            AND i.status_processo != 'PARTICIPANDO'
            ORDER BY i.nome_inscrito ASC", COD_EVENTO);

        $rsI = $conex->Execute($sqlInativos);
        if ($rsI && !$rsI->EOF) {
            foreach ($rsI as $row) {
                // Evitar duplicados (caso já esteja na lista por ser o 40920)
                if (!in_array($row['cod_inscrito'], $codAtivos)) {
                    $agentes[] = array(
                        'codInscrito'    => $row['cod_inscrito'],
                        'nome'           => $row['nome_inscrito'],
                        'statusProcesso' => $row['status_processo'],
                        'cpf'            => $row['cpf'],
                        'ativo'          => 0,
                        'totalAlunos'    => isset($contagens[$row['cod_inscrito']]) ? $contagens[$row['cod_inscrito']] : 0
                    );
                }
            }
        }

        return $agentes;
    }

}

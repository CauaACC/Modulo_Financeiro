<?php

class EventoDivulgacao extends EventoDivulgacaoVO
{

    private $objDAO;

    function __construct()
    {
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new EventoDivulgacaoDAO();
    }

    public function carregarPorEvento($debug = false)
    {
        return $this->objDAO->getByCodEvento($this, $debug);
    }

    public function existe($debug = false)
    {
        return $this->objDAO->getByCodEventoExiste($this, $debug);
    }

    public function salvar($debug = false)
    {
        $this->validatorObj();
        if ($this->existe($debug)) {
            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Divulgação do evento atualizada com sucesso.");
            } else {
                throw new Exception("A divulgação do evento não foi atualizada.");
            }
        } else {
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Divulgação do evento salva com sucesso.");
            } else {
                throw new Exception("A divulgação do evento não foi salva.");
            }
        }
    }

    /**
     * Grava sem o teatro de exceptions do salvar() — para uso interno do
     * publicar, que precisa continuar executando depois de persistir.
     */
    public function gravar($debug = false)
    {
        $this->validatorObj();
        if ($this->existe($debug)) {
            return $this->objDAO->update($this, $debug);
        }
        return $this->objDAO->insert($this, $debug);
    }

    /**
     * Sugestao de preenchimento a partir do que o InscriTec ja sabe do evento
     * (tab_eventos e a solicitacao de projeto). So preenche campos VAZIOS —
     * nunca sobrescreve o que o coordenador ja digitou/gravou.
     */
    public function sugerirCampos($debug = false)
    {
        $ev = $this->objDAO->getDadosEvento($this->getCodEvento(), $debug);
        $pj = $this->objDAO->getDadosProjeto($this->getCodEvento(), $debug);

        $sugestoes = array();

        if ($ev) {
            $sugestoes['titulo'] = isset($ev['titulo']) ? $ev['titulo'] : '';

            $periodo = '';
            if (!empty($ev['data_ini']) && !empty($ev['data_fim'])) {
                $periodo = $this->formatarData($ev['data_ini']) . ' a ' . $this->formatarData($ev['data_fim']);
            } elseif (!empty($ev['periodo'])) {
                $periodo = $ev['periodo'];
            }
            $sugestoes['periodo_do_curso'] = $periodo;

            $local = isset($ev['desc_local_realizacao']) ? trim($ev['desc_local_realizacao']) : '';
            if (!empty($ev['cidade_uf'])) {
                $local .= ($local != '' ? "\n" : '') . $ev['cidade_uf'];
            }
            $sugestoes['local'] = $local;
            $sugestoes['carga_horaria'] = isset($ev['duracao']) ? $ev['duracao'] : '';
            $sugestoes['descricao'] = isset($ev['desc_evento']) ? $ev['desc_evento'] : '';

            if (!empty($ev['url_prefixo'])) {
                $sugestoes['link_inscricoes'] = 'https://opctec.net.br/hutec/' . $ev['url_prefixo'] . '/inscricoes';
            }
        }

        if ($pj) {
            if (!empty($pj['publico_alvo'])) {
                $sugestoes['publico_alvo'] = $pj['publico_alvo'];
            }
            $vagasMin = isset($pj['quantidade_vagas_minima']) ? trim($pj['quantidade_vagas_minima']) : '';
            $vagasMax = isset($pj['quantidade_vagas_maxima']) ? trim($pj['quantidade_vagas_maxima']) : '';
            if ($vagasMin != '' || $vagasMax != '') {
                $sugestoes['vagas'] = trim(($vagasMin != '' ? 'Mínimo ' . $vagasMin : '')
                    . ($vagasMin != '' && $vagasMax != '' ? ' e ' : '')
                    . ($vagasMax != '' ? 'Máximo ' . $vagasMax : ''));
            }
            if (!empty($pj['programacao'])) {
                $sugestoes['programacao'] = $pj['programacao'];
            }
            if (!empty($pj['outras_informacoes_publicar'])) {
                $sugestoes['outras_informacoes'] = $pj['outras_informacoes_publicar'];
            }
            if (!empty($pj['descricao_valores_inscricao'])) {
                $sugestoes['investimento'] = $pj['descricao_valores_inscricao'];
            }
            if (!empty($pj['data_inicio_inscricoes']) && !empty($pj['data_fim_inscricoes'])) {
                $sugestoes['periodo_de_inscricoes'] = $this->formatarData($pj['data_inicio_inscricoes'])
                    . ' a ' . $this->formatarData($pj['data_fim_inscricoes']);
            }
        }

        foreach ($sugestoes as $campo => $valor) {
            if ($valor === '' || $valor === null) {
                continue;
            }
            if ($this->isNullOrEmpty($this->getPadrao($campo))) {
                $metodo = 'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', $campo)));
                $this->$metodo($valor);
            }
        }
    }

    private function formatarData($data)
    {
        $ts = strtotime($data);
        return $ts ? date('d/m/Y', $ts) : $data;
    }
}

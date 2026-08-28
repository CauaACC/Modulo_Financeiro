<?php

/**
 * Proposta do evento — o que o coordenador leva para instituicoes e editais
 * publicos, e que alimenta a geracao do PDF/DOCX.
 *
 * So guarda o que NAO existe em outro lugar. Titulo, sigla, finalidade,
 * coordenador, modalidade, programacao e orcamento vem das tabelas donas
 * (ver cabecalho de sql/tab_evento_proposta.sql).
 */
class EventoPropostaVO extends PadraoVO
{

    /** Campos editaveis da proposta, na ordem em que aparecem no formulario. */
    public function getListaCampos()
    {
        return array(
            'justificativa', 'objetivo', 'cronograma',
            'comissao_organizadora', 'partes_envolvidas', 'apoio', 'departamentos',
            'declaracao_atuacao', 'previsao_orcamentaria',
            'orgao_interveniente', 'acordo_vigencia_ini', 'acordo_vigencia_fim',
            'origem_proposta', 'proposta_de', 'ambito', 'modalidade', 'carga_horaria_distancia',
            'area_tematica_predominante', 'ods',
            'vinculado_programa', 'vinculado_projeto_numero',
            'sistema_inscricao', 'enviar_email_inscricao', 'recebimento_relatorios',
        );
    }

    /** Objetivos de Desenvolvimento Sustentavel (ONU). Selecionar no maximo 3. */
    public static function getListaOds()
    {
        return array(
            '01' => 'Erradicação da Pobreza',
            '02' => 'Fome Zero e Agricultura Sustentável',
            '03' => 'Saúde e Bem-Estar',
            '04' => 'Educação de Qualidade',
            '05' => 'Igualdade de Gênero',
            '06' => 'Água Potável e Saneamento',
            '07' => 'Energia Acessível e Limpa',
            '08' => 'Trabalho Decente e Crescimento Econômico',
            '09' => 'Indústria, Inovação e Infraestrutura',
            '10' => 'Redução de Desigualdades',
            '11' => 'Cidades e Comunidades Sustentáveis',
            '12' => 'Consumo e Produção Responsáveis',
            '13' => 'Ação contra a Mudança Global do Clima',
            '14' => 'Vida na Água',
            '15' => 'Vida Terrestre',
            '16' => 'Paz, Justiça e Instituições Eficazes',
            '17' => 'Parcerias e Meios de Implementação',
        );
    }

    const MAX_ODS = 3;

    public static function getListaAmbito()
    {
        return array('Municipal', 'Regional', 'Estadual', 'Nacional', 'Internacional');
    }

    /** Modalidade de execucao — nao confundir com tab_eventos_configuracao.tipo_projeto,
     *  que guarda o TIPO do evento (CURSO, CONGRESSO). */
    public static function getListaModalidade()
    {
        return array('Presencial', 'A distância', 'Híbrida');
    }

    public static function getListaPropostaDe()
    {
        return array('Evento', 'Curso', 'Treinamento', 'Outras atividades');
    }

    /** Areas tematicas da extensao (nao confundir com eixos tematicos de trabalhos). */
    public static function getListaAreaTematica()
    {
        return array(
            'Comunicação', 'Cultura', 'Direitos Humanos e Justiça', 'Educação',
            'Meio Ambiente', 'Saúde Animal', 'Saúde Humana',
            'Tecnologia e Produção', 'Trabalho',
        );
    }

    public static function getListaSistemaInscricao()
    {
        return array(
            'ONLINE'   => 'Inscrições online',
            'GRATUITA' => 'Inscrição gratuita',
            'OFFLINE'  => 'Inscrição offline',
        );
    }

    /** ODS marcados, como array de codigos. */
    public function getOdsArray()
    {
        $ods = $this->getOds();
        if ($ods == '') {
            return array();
        }
        return array_filter(array_map('trim', explode(',', $ods)));
    }

    public function getPreAvaliacaoRespostasArray()
    {
        $json = $this->getPreAvaliacaoRespostas();
        if ($json == '') {
            return array();
        }
        $dados = json_decode(stripslashes($json), true);
        return is_array($dados) ? $dados : array();
    }

    public function validatorObj()
    {
        $message = "";
        $return  = true;

        if ($this->isNullOrEmpty($this->getCodEvento())) {
            $message .= "Código do evento não foi informado.<br>";
            $return = false;
        }
        if (count($this->getOdsArray()) > self::MAX_ODS) {
            $message .= "Selecione no máximo " . self::MAX_ODS . " Objetivos de Desenvolvimento Sustentável.<br>";
            $return = false;
        }
        $ini = $this->getAcordoVigenciaIni();
        $fim = $this->getAcordoVigenciaFim();
        if ($ini != '' && $fim != '' && strtotime($fim) < strtotime($ini)) {
            $message .= "A vigência do acordo termina antes de começar.<br>";
            $return = false;
        }
        if ($return == false) {
            throw new Exception($message);
        }
        return true;
    }
}

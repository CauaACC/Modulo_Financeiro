<?php

/**
 * Divulgacao do evento no site da HUTEC (hutec.org.br).
 *
 * As propriedades de conteudo tem o MESMO nome dos campos ACF do post type
 * "curso" (grupo "CURSOS NEW") do WordPress; o payload de publicacao e montado
 * direto delas (getCamposAcf).
 */
class EventoDivulgacaoVO extends PadraoVO
{

    /** Campos ACF publicados no WordPress, na ordem do grupo "CURSOS NEW". */
    public function getListaCamposAcf()
    {
        return array(
            'titulo',
            'periodo_de_inscricoes',
            'publico_alvo',
            'periodo_do_curso',
            'vagas',
            'local',
            'carga_horaria',
            'investimento',
            'descricao',
            'programacao',
            'outras_informacoes',
            'link_inscricoes',
        );
    }

    /** Bloco acf COMPLETO — o ACF REST exige todos os campos em qualquer update. */
    public function getCamposAcf()
    {
        $acf = array();
        foreach ($this->getListaCamposAcf() as $campo) {
            $valor = $this->getPadrao($campo);
            // getPadrao passa por addslashesopctec (escape p/ SQL); aqui o destino
            // e JSON, entao desfazemos para nao publicar barras no site.
            $acf[$campo] = $valor !== null ? stripslashes($valor) : '';
        }
        return $acf;
    }

    public function validatorObj()
    {
        $message = "";
        $return  = true;

        if ($this->isNullOrEmpty($this->getCodEvento())) {
            $message .= "Código do evento não foi informado.<br>";
            $return = false;
        }
        if ($this->isNullOrEmpty($this->getTitulo())) {
            $message .= "Informe o título da divulgação.<br>";
            $return = false;
        }
        if ($return == false) {
            throw new Exception($message);
        }
        return true;
    }
}

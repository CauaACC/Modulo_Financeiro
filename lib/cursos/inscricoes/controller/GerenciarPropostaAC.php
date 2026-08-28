<?php

/**
 * Proposta do evento — o documento que o coordenador monta no InscriTec e leva
 * para instituicoes e editais publicos (orgao interveniente, Lei Rouanet,
 * protocolos em prefeituras). Daqui saem as exportacoes (PDF/DOCX).
 *
 * Aqui SO entra o que nao existe em outra tela. Identificacao, coordenacao,
 * programacao (Atividades) e orcamento (Previsao Orcamentaria) sao apenas
 * EXIBIDOS a partir das tabelas donas — nunca redigitados.
 *
 * Substitui a antiga tela GerenciarProjeto, que gravava em tab_projetos
 * (tabela inexistente). As classes Projeto* seguem no projeto por decisao do
 * usuario — podem servir a regras de negocio especificas.
 */
class GerenciarPropostaAC
{

    function __construct()
    {
        // O coordenador do evento monta a propria proposta.
        FWA_exigirPermissao(array(enumSistema::SIS_GERENCIAR_EVENTO, enumSistema::SIS_GERENCIAR_ADM_GERAL));
    }

    /**
     * Carrega a proposta do evento corrente, o resumo do que vem de outras
     * telas e o formulario de pre-avaliacao do gestor (quando ele exigir).
     */
    public function carregar($fws_, $debug = false)
    {
        $obj = new EventoProposta();
        $existe = $obj->carregarPorEvento($debug);

        $campos = array();
        foreach ($obj->getListaCampos() as $campo) {
            $valor = $obj->getPadrao($campo);
            $campos[$campo] = $valor !== null ? stripslashes($valor) : '';
        }

        $resumo = $obj->carregarResumoEvento($debug);

        // Cronograma em branco ganha uma sugestao a partir das datas do evento.
        if (trim($campos['cronograma']) == '') {
            $campos['cronograma'] = $obj->sugerirCronograma(
                isset($resumo['data_ini']) ? $resumo['data_ini'] : '',
                isset($resumo['data_fim']) ? $resumo['data_fim'] : '', $debug);
        }

        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(array(
            'statusMessage'   => '1',
            'existe'          => $existe ? '1' : '',
            'campos'          => $campos,
            'resumo'          => $resumo,
            'ods'             => $obj->getOdsArray(),
            'listaOds'        => EventoPropostaVO::getListaOds(),
            'maxOds'          => EventoPropostaVO::MAX_ODS,
            'listaAmbito'     => EventoPropostaVO::getListaAmbito(),
            'listaModalidade' => EventoPropostaVO::getListaModalidade(),
            'listaPropostaDe' => EventoPropostaVO::getListaPropostaDe(),
            'listaAreaTematica'     => EventoPropostaVO::getListaAreaTematica(),
            'listaSistemaInscricao' => EventoPropostaVO::getListaSistemaInscricao(),
            'camposEvento'    => EventoProposta::getCamposEventoEditaveis(),
            'preAvaliacaoCampos'    => $obj->carregarCamposPreAvaliacao($debug),
            'preAvaliacaoRespostas' => $obj->getPreAvaliacaoRespostasArray(),
            'nomeGestor'            => $obj->getNomeGestor($debug),
        ));
    }

    public function salvar($fws_, $debug = false)
    {
        $ok = false;
        $e = null;
        try {
            $obj = new EventoProposta();
            $obj->carregarPorEvento($debug);

            foreach ($obj->getListaCampos() as $campo) {
                $valor = isset($fws_[$campo]) ? $fws_[$campo] : '';
                $valor = $this->codificarSuplementares($valor);
                $metodo = 'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', $campo)));
                $obj->$metodo($valor != '' ? stripslashes($valor) : NULL);
            }

            // Respostas da pre-avaliacao: grava so o que o gestor perguntou,
            // ignorando qualquer campo extra vindo do navegador.
            $definicao = $obj->carregarCamposPreAvaliacao($debug);
            if (!empty($definicao)) {
                $respostas = array();
                foreach ($definicao as $c) {
                    $chave = 'pa_' . $c['campo'];
                    if (isset($fws_[$chave])) {
                        $respostas[$c['campo']] = $this->codificarSuplementares(stripslashes($fws_[$chave]));
                    }
                }
                $obj->setPreAvaliacaoRespostas(json_encode($respostas, JSON_UNESCAPED_UNICODE));
            }

            // Identificacao editada aqui volta para tab_eventos, que e a dona
            // desses campos — nao duplicamos nada na proposta.
            $dadosEvento = array();
            foreach (EventoProposta::getCamposEventoEditaveis() as $coluna) {
                $chave = 'ev_' . $coluna;
                if (isset($fws_[$chave])) {
                    $dadosEvento[$coluna] = $this->codificarSuplementares(stripslashes($fws_[$chave]));
                }
            }
            if (!empty($dadosEvento)) {
                $obj->salvarDadosEvento($dadosEvento, $debug);
            }

            $obj->salvar($debug);
        } catch (OKException $ex) {
            $ok = true;
            $e = $ex;
        } catch (Exception $ex) {
            $e = $ex;
        }

        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(array(
            'statusMessage' => $ok ? '1' : '',
            'message'       => $e ? $e->getMessage() : '',
        ));
    }

    /**
     * Converte caracteres fora do BMP (emoji etc., 4 bytes em UTF-8) para
     * entidades HTML. A conexao e o banco sao utf8 de 3 bytes — um emoji cru
     * TRUNCA a coluna no primeiro emoji.
     */
    private function codificarSuplementares($texto)
    {
        if ($texto == '') {
            return $texto;
        }
        return preg_replace_callback('/[\x{10000}-\x{10FFFF}]/u', function ($m) {
            $cp = hexdec(bin2hex(mb_convert_encoding($m[0], 'UTF-32BE', 'UTF-8')));
            return '&#' . $cp . ';';
        }, $texto);
    }
}

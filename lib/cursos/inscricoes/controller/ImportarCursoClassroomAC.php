<?php

/**
 * Importa um curso inteiro (curso + capitulos + itens) a partir de um manifesto
 * JSON gerado do Google Classroom (script classroomManifesto.py).
 *
 * Rota (dispatcher nativo CommandAC):
 *     POST WB_URL_COMMAND  ->  c=ImportarCursoClassroom & m=importar
 *
 * Entrada do manifesto (uma das duas):
 *     - upload:  campo de arquivo  name="manifesto"  (multipart/form-data)
 *     - caminho: $fws_['caminho']  = path absoluto do JSON no servidor (uso local)
 *
 * Formato do manifesto:
 *     { "cod_evento":115, "curso":{"titulo","descricao"},
 *       "capitulos":[ {"titulo","descricao","ordem","itens":[
 *           {"tipo","titulo","descricao","url","ordem"} ]} ] }
 *
 * Estrategia: transacao unica (tudo-ou-nada). Cria o acad_curso, e para cada
 * capitulo cria um item CAPITULO (cod_pai=null), captura o cod_curso_item novo
 * e usa como cod_pai dos itens filhos.
 */
class ImportarCursoClassroomAC {

    private $cursoDAO;
    private $itemDAO;

    /** Importar cria curso e itens: e' montagem (coordenador do EAD ou ADM_GERAL). */
    function __construct() {
        FWA_exigirPermissao(FWA_permissoesCoordenarEAD());
        $this->cursoDAO = new CursoDAO();
        $this->itemDAO  = new CursoItemDAO();
    }

    /** Le e decodifica o manifesto (upload ou caminho). */
    private function lerManifesto($fws_) {
        $conteudo = null;
        if (isset($_FILES['manifesto']) && $_FILES['manifesto']['error'] === UPLOAD_ERR_OK) {
            $conteudo = file_get_contents($_FILES['manifesto']['tmp_name']);
        } elseif (!empty($fws_['caminho'])) {
            $caminho = $fws_['caminho'];
            if (!is_file($caminho)) {
                throw new Exception("Manifesto não encontrado no caminho informado.");
            }
            $conteudo = file_get_contents($caminho);
        } else {
            throw new Exception("Envie o arquivo do manifesto (campo 'manifesto') ou informe 'caminho'.");
        }
        $dados = json_decode($conteudo, true);
        if (!is_array($dados) || !isset($dados['capitulos']) || !is_array($dados['capitulos'])) {
            throw new Exception("Manifesto inválido: JSON não contém 'capitulos'.");
        }
        return $dados;
    }

    /** Aplica os mesmos defaults por tipo do GerenciarCursoItemAC::salvarCursoItem. */
    private function aplicarDefaultsItem(CursoItem $obj) {
        $tipo = $obj->getTipo();
        $obj->setControlePresenca(in_array($tipo, array('VIDEO','MEET')) ? 1 : 0);
        $obj->setPermiteComentarios(in_array($tipo, array('LINK','FORMULARIO','CAPITULO')) ? 0 : 1);
        if (!$obj->getStatus()) $obj->setStatus('ATIVADA');
        if (!$obj->getIntervaloConfirmacaoMin()) $obj->setIntervaloConfirmacaoMin(5);
        if (!$obj->getIntervaloConfirmacaoMax()) $obj->setIntervaloConfirmacaoMax((int) $obj->getIntervaloConfirmacaoMin());
        if (!$obj->getTimeoutConfirmacaoSeg()) $obj->setTimeoutConfirmacaoSeg(60);
    }

    /** Grava um item e devolve o cod_curso_item recem-criado. */
    private function inserirItem($codCurso, $codPai, $tipo, $titulo, $descricao, $url, $ordem) {
        $obj = new CursoItem();
        $obj->setCodEvento(COD_EVENTO);
        $obj->setCodCurso($codCurso);
        $obj->setCodPai($codPai);          // null para CAPITULO raiz
        $obj->setTipo($tipo);
        $obj->setTitulo($titulo);
        $obj->setDescricao($descricao);
        $obj->setUrl($url);
        $obj->setOrdem((int) $ordem);
        $this->aplicarDefaultsItem($obj);
        $obj->validatorSalvar();
        if (!$this->itemDAO->insert($obj)) {
            throw new Exception("Falha ao inserir item '" . $titulo . "'.");
        }
        return $obj->getCodCursoItem();
    }

    public function importar($fws_, $debug = false) {
        $ok = false;
        $message = '';
        $resumo = array('cod_curso' => null, 'capitulos' => 0, 'itens' => 0, 'por_tipo' => array());

        $conex = ConexaoDB::getConexaoDB();
        try {
            $dados = $this->lerManifesto($fws_);

            // Coerência de evento: o manifesto precisa ser do evento em contexto.
            if (isset($dados['cod_evento']) && (int) $dados['cod_evento'] !== (int) COD_EVENTO) {
                throw new Exception("O manifesto é do evento " . (int) $dados['cod_evento']
                    . ", mas você está no evento " . (int) COD_EVENTO
                    . ". Acesse o evento correto para importar.");
            }

            // Evitar duplicar: se já existe curso com o mesmo título, exige confirmação.
            $tituloCurso = isset($dados['curso']['titulo']) ? trim($dados['curso']['titulo']) : 'Curso importado';
            if (empty($fws_['forcar'])) {
                $lista = new Curso();
                $lista->carregarLista(false);
                foreach ($lista->getListaCurso() as $c) {
                    if (mb_strtolower(trim($c->getTitulo())) === mb_strtolower($tituloCurso)) {
                        throw new Exception("Já existe um curso chamado \"" . $tituloCurso
                            . "\" neste evento. Reenvie marcando 'forçar' para importar mesmo assim.");
                    }
                }
            }

            $conex->StartTrans();

            // 1) Curso
            $curso = new Curso();
            $curso->setCodEvento(COD_EVENTO);
            $curso->setTitulo($tituloCurso);
            $curso->setDescricao(isset($dados['curso']['descricao']) ? $dados['curso']['descricao'] : '');
            $curso->setStatus('ATIVADA');
            $curso->setOrdem(0);
            $curso->validatorSalvar();
            if (!$this->cursoDAO->insert($curso)) {
                throw new Exception("Não foi possível criar o curso.");
            }
            $codCurso = $curso->getCodCurso();
            $resumo['cod_curso'] = $codCurso;

            // 2) Capítulos + itens
            foreach ($dados['capitulos'] as $cap) {
                $codPai = $this->inserirItem(
                    $codCurso, null, 'CAPITULO',
                    isset($cap['titulo']) ? $cap['titulo'] : 'Capítulo',
                    isset($cap['descricao']) ? $cap['descricao'] : '',
                    '', isset($cap['ordem']) ? $cap['ordem'] : 0
                );
                $resumo['capitulos']++;
                $resumo['por_tipo']['CAPITULO'] = ($resumo['por_tipo']['CAPITULO'] ?? 0) + 1;

                $itens = isset($cap['itens']) && is_array($cap['itens']) ? $cap['itens'] : array();
                foreach ($itens as $it) {
                    $tipo = isset($it['tipo']) ? $it['tipo'] : 'LINK';
                    $this->inserirItem(
                        $codCurso, $codPai, $tipo,
                        isset($it['titulo']) ? $it['titulo'] : 'Item',
                        isset($it['descricao']) ? $it['descricao'] : '',
                        isset($it['url']) ? $it['url'] : '',
                        isset($it['ordem']) ? $it['ordem'] : 0
                    );
                    $resumo['itens']++;
                    $resumo['por_tipo'][$tipo] = ($resumo['por_tipo'][$tipo] ?? 0) + 1;
                }
            }

            $conex->CompleteTrans();
            $ok = true;
            $message = "Curso importado com sucesso (cód. " . $codCurso . "): "
                . $resumo['capitulos'] . " capítulos e " . $resumo['itens'] . " itens.";
        } catch (Exception $e) {
            if ($conex && $conex->transCnt > 0) {
                $conex->FailTrans();
                $conex->CompleteTrans();
            }
            $ok = false;
            $message = $e->getMessage();
        }

        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(array(
            'statusMessage' => $ok,
            'message'       => $message,
            'resumo'        => $resumo,
        ));
    }
}

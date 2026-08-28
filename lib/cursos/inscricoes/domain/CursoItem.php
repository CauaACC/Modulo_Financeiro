<?php

class CursoItem extends CursoItemVO {

    private $objDAO;

    function __construct() {
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new CursoItemDAO();
    }

    /**
     * Carrega o item pelo cod_curso_item DENTRO do evento corrente.
     * Devolve false quando o item nao existe ou e' de outro evento -- quem
     * chama PRECISA testar este retorno: como o codigo ja foi setado antes da
     * carga, `getCodCursoItem()` continua preenchido mesmo no item inexistente.
     */
    public function getCursoItemById($debug = false) {
        return $this->objDAO->getById($this, $debug);
    }

    public function carregarLista($somenteAtivos = false, $debug = false) {
        $this->objDAO->carregarLista($this, $somenteAtivos, $debug);
    }

    public function carregarArvore($codCurso = null, $debug = false) {
        return $this->objDAO->carregarArvore($this->getCodEvento(), $codCurso, $debug);
    }

    public function carregarPossiveisPais($codCurso, $excluirCodItem = null, $debug = false) {
        return $this->objDAO->carregarPossiveisPais($this->getCodEvento(), $codCurso, $excluirCodItem, $debug);
    }

    public function carregarPossiveisPreRequisitos($codCurso, $excluirCodItem = null, $debug = false) {
        return $this->objDAO->carregarPossiveisPreRequisitos($this->getCodEvento(), $codCurso, $excluirCodItem, $debug);
    }

    public function salvar($debug = false) {
        $this->validatorSalvar();
        if (is_null($this->getCodCursoItem()) or $this->getCodCursoItem() == "") {
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Item salvo com sucesso.");
            } else {
                throw new Exception("Não foi possível salvar o item.");
            }
        } else {
            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Item atualizado com sucesso.");
            } else {
                throw new Exception("Não foi possível atualizar o item.");
            }
        }
    }

    public function contarDependentes($debug = false) {
        return $this->objDAO->contarDependentesDoItem($this->getCodEvento(), $this->getCodCursoItem(), $debug);
    }

    /**
     * Traduz o mapa de dependentes numa lista legivel ("3 subitens", "12
     * registros de presenca"...). Vazio = item pode ser excluido.
     */
    public static function descreverDependentes($dep) {
        $rotulos = array(
            'filhos'     => array('subitem', 'subitens'),
            'presenca'   => array('registro de presença', 'registros de presença'),
            'conclusao'  => array('conclusão de aluno', 'conclusões de aluno'),
            'tarefa'     => array('tarefa enviada', 'tarefas enviadas'),
            'comentario' => array('comentário', 'comentários'),
            'mensagem'   => array('mensagem de tarefa', 'mensagens de tarefa'),
            'prereq'     => array('item que o tem como pré-requisito', 'itens que o tem como pré-requisito'),
        );
        $travas = array();
        foreach ($rotulos as $chave => $par) {
            $qtd = isset($dep[$chave]) ? (int) $dep[$chave] : 0;
            if ($qtd > 0) $travas[] = $qtd . ' ' . ($qtd == 1 ? $par[0] : $par[1]);
        }
        return $travas;
    }

    /**
     * Exclusao definitiva -- so quando o item nao tem nada pendurado nele.
     *
     * O criterio e' o mesmo do Curso, que ja se recusa a ser excluido enquanto
     * tiver itens: nada de historico de aluno some em silencio. Item com
     * dependentes nao e' apagado; a saida e' inativar.
     */
    public function excluir($debug = false) {
        $this->validatorExcluir();

        $dep = $this->contarDependentes($debug);
        if (!empty($dep['erro'])) {
            throw new Exception("Não foi possível verificar as dependências deste item. Por segurança, a exclusão foi cancelada.");
        }
        $travas = self::descreverDependentes($dep);
        if (!empty($travas)) {
            throw new Exception("Este item não pode ser excluído porque possui " . implode(', ', $travas)
                . ". Para tirá-lo do ar sem perder o histórico, use <strong>Inativar</strong>.");
        }

        if ($this->objDAO->deleteComEspelho($this, $debug)) {
            throw new OKException("Item excluído com sucesso. Uma cópia foi guardada para auditoria.");
        }
        throw new Exception("Não foi possível excluir o item.");
    }

    /**
     * Tira o item do ar preservando todo o historico. Solta tambem quem
     * dependia dele como pre-requisito -- senao o aluno ficaria esperando a
     * conclusao de um item que nao aparece mais.
     */
    public function inativar($debug = false) {
        $this->validatorExcluir();
        $liberados = $this->objDAO->limparPreRequisitoApontandoPara($this->getCodEvento(), $this->getCodCursoItem(), $debug);
        if ($this->objDAO->alterarStatus($this, 'DESATIVADA', $debug)) {
            $extra = $liberados > 0
                ? " " . $liberados . " item(ns) que dependiam dele como pré-requisito foram liberados."
                : "";
            throw new OKException("Item inativado. Ele deixa de aparecer para os alunos e todo o histórico foi preservado." . $extra);
        }
        throw new Exception("Não foi possível inativar o item.");
    }

    public function reativar($debug = false) {
        $this->validatorExcluir();
        if ($this->objDAO->alterarStatus($this, 'ATIVADA', $debug)) {
            throw new OKException("Item reativado. Ele volta a aparecer para os alunos.");
        }
        throw new Exception("Não foi possível reativar o item.");
    }
}

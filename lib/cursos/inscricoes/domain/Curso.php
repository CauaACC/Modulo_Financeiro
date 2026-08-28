<?php

class Curso extends CursoVO {

    private $objDAO;

    function __construct() {
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new CursoDAO();
    }

    public function getCursoById($debug = false) {
        $this->objDAO->getById($this, $debug);
    }

    public function carregarLista($somenteAtivos = false, $debug = false) {
        $this->objDAO->carregarLista($this, $somenteAtivos, $debug);
    }

    public function contarItens($debug = false) {
        return $this->objDAO->contarItensPorCurso($this->getCodEvento(), $debug);
    }

    public function salvar($debug = false) {
        $this->validatorSalvar();
        if (!$this->getCodCurso()) {
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Curso salvo com sucesso.");
            }
            throw new Exception("Não foi possível salvar o curso.");
        }
        if ($this->objDAO->update($this, $debug)) {
            throw new OKException("Curso atualizado com sucesso.");
        }
        throw new Exception("Não foi possível atualizar o curso.");
    }

    /**
     * O inscrito conta como "pago" para as regras de divulgacao do curso?
     * Extraido da tela do aluno (inscricao_congresso_curso.php) para poder ser
     * usado tambem pelos controllers -- a regra tem de ser UMA so, senao a tela
     * mostra o curso e o AJAX recusa (ou o contrario).
     */
    public static function calcularInscricaoPaga($objInscrito) {
        try {
            return ($objInscrito->getCodVoucher() != ''
                 || $objInscrito->getCodIsento() != ''
                 || $objInscrito->getTotalPagamentos() > 0
                 || $objInscrito->getTotalPerfisIsentos() > 0
                 || $objInscrito->getValorTotalPedidos() == 0);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Este inscrito pode acessar ESTE curso?
     *
     * O curso ja vem do evento corrente (Curso::carregarLista filtra por
     * cod_evento), entao aqui valem so as regras de publicacao:
     *   exibir_para (PAGO/NAO_PAGO/TODOS), filtro_status, filtro_status_processo,
     *   cod_perfil e o vinculo por oficina.
     * Curso vinculado a oficina(s) so aparece para quem esta em pelo menos uma
     * delas; curso sem vinculo segue apenas as regras acima.
     *
     * @param array $oficinasInscrito  mapa cod_oficina => true (OficinaDAO::getCodOficinasByInscrito)
     * @param array $cursosComOficina  mapa cod_curso => array(cod_oficina) (OficinaDAO::getCursosComOficina)
     */
    public function inscritoPodeVer($objInscrito, $inscricaoPaga, array $oficinasInscrito, array $cursosComOficina) {
        $exibirPara = $this->getExibirPara() ?: 'TODOS';
        if ($exibirPara == 'PAGO' && !$inscricaoPaga) return false;
        if ($exibirPara == 'NAO_PAGO' && $inscricaoPaga) return false;

        $filtroStatus = $this->getFiltroStatus();
        if ($filtroStatus && $filtroStatus != $objInscrito->getStatus()) return false;

        $filtroStatusProcesso = $this->getFiltroStatusProcesso();
        if ($filtroStatusProcesso && $filtroStatusProcesso != $objInscrito->getStatusProcesso()) return false;

        $codPerfilCurso = $this->getCodPerfil();
        if ($codPerfilCurso && $codPerfilCurso != $objInscrito->getCodPerfil()) return false;

        $codCurso = (int) $this->getCodCurso();
        if (!empty($cursosComOficina[$codCurso])) {
            foreach ($cursosComOficina[$codCurso] as $co) {
                if (isset($oficinasInscrito[$co])) return true;
            }
            return false;
        }
        return true;
    }

    public function excluir($debug = false) {
        $this->validatorExcluir();
        if ($this->objDAO->possuiItens($this, $debug)) {
            throw new Exception("Este curso possui itens cadastrados. Remova os itens antes de excluir o curso.");
        }
        if ($this->objDAO->delete($this, $debug)) {
            throw new OKException("Curso excluído com sucesso.");
        }
        throw new Exception("Não foi possível excluir o curso.");
    }
}

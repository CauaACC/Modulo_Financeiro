<?php
/**
 * Controller do fluxo de Recurso da Avaliação do Inscrito (etapa 1) — evento 129.
 * Espelha o fluxo legado de envio de trabalho-recurso, mas grava em
 * tab_trabalhos_avaliacao_inscricao.recurso_texto via DAO/Entidade.
 */
class GerenciarRecursoInscritoAC
{
    public function salvarRecursoFase1($fws_, $debug = false)
    {
        $urlVoltar    = WB_URL_VIEW . "?content=view/inscricao/inscricao_congresso_ok.php";
        $urlFormulario = WB_URL_VIEW . "?content=view/inscricao/inscricao_recurso_fase1_129.php";

        $codInscrito  = (int) ($_SESSION['_cod_inscrito'] ?? 0);
        $textoRecurso = trim((string) ($fws_['recurso_texto'] ?? ''));
        $confirmou    = ((string) ($fws_['confirma_recurso'] ?? '0')) === '1';

        if ($codInscrito <= 0) {
            $_SESSION['MSG'] = "Sessão expirada. Faça login novamente.";
            $this->redirect($urlVoltar);
        }
        if (!$confirmou) {
            $_SESSION['MSG'] = "Marque \"Sim, Confirmo essa solicitação de recurso\" antes de salvar.";
            $this->redirect($urlFormulario);
        }
        if ($textoRecurso === '') {
            $_SESSION['MSG'] = "O texto do recurso é obrigatório.";
            $this->redirect($urlFormulario);
        }

        $objAval = new AvaliacaoInscrito();
        $objAval->setCodInscrito($codInscrito);
        $objAval->setRecursoTexto($textoRecurso);
        $ok = $objAval->salvarRecursoFase1ByIdInscrito($debug);

        if (!$ok) {
            $_SESSION['MSG'] = "Não foi possível salvar o recurso. Tente novamente.";
            $this->redirect($urlFormulario);
        }

        $_SESSION['MSG'] = "Pedido de recurso registrado com sucesso.";
        $this->redirect($urlVoltar);
    }

    /**
     * Fluxo de recurso da Fase 2 (Avaliacao de Documentos). Aceita tanto
     * INDEFERIDO quanto DEFERIDO (este ultimo pode contestar a pontuacao).
     * Grava em tab_trabalhos_avaliacao_inscricao.recurso_texto da linha
     * com ordem_etapa=2.
     */
    public function salvarRecursoFase2($fws_, $debug = false)
    {
        $urlVoltar     = WB_URL_VIEW . "?content=view/inscricao/inscricao_congresso_ok.php";
        $urlFormulario = WB_URL_VIEW . "?content=view/inscricao/inscricao_recurso_fase2_129.php";

        $codInscrito  = (int) ($_SESSION['_cod_inscrito'] ?? 0);
        $textoRecurso = trim((string) ($fws_['recurso_texto'] ?? ''));
        $confirmou    = ((string) ($fws_['confirma_recurso'] ?? '0')) === '1';

        if ($codInscrito <= 0) {
            $_SESSION['MSG'] = "Sessão expirada. Faça login novamente.";
            $this->redirect($urlVoltar);
        }
        if (!$confirmou) {
            $_SESSION['MSG'] = "Marque \"Sim, Confirmo essa solicitação de recurso\" antes de salvar.";
            $this->redirect($urlFormulario);
        }
        if ($textoRecurso === '') {
            $_SESSION['MSG'] = "O texto do recurso é obrigatório.";
            $this->redirect($urlFormulario);
        }

        $objAval = new AvaliacaoInscrito();
        $objAval->setCodInscrito($codInscrito);
        $objAval->setRecursoTexto($textoRecurso);
        $ok = $objAval->salvarRecursoFase2ByIdInscrito($debug);

        if (!$ok) {
            $_SESSION['MSG'] = "Não foi possível salvar o recurso. Tente novamente.";
            $this->redirect($urlFormulario);
        }

        $_SESSION['MSG'] = "Pedido de recurso registrado com sucesso.";
        $this->redirect($urlVoltar);
    }

    private function redirect($url)
    {
        header("Location: {$url}");
        exit();
    }
}

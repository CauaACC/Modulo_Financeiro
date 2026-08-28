<?php

/**
 * GerenciarEditalPublicacaoAC
 *
 * Controller pro download do edital de publicacao (DOCX) gerado pela
 * entidade EditalPublicacao. Toda a regra/config fica em EditalPublicacao;
 * aqui so faz validacao de input e dispara o download.
 *
 * Acesso via CommandAC: index-action.php?content=controller/CommandAC.php
 *     &c=GerenciarEditalPublicacao&m=gerarEditalDOCX
 *     &ordemEtapa=2&filtro=todos&_submit_check=1
 */
class GerenciarEditalPublicacaoAC
{
    /**
     * Garante autenticacao no construtor (mesmo padrao do GerenciarAvaliacaoFase2AC).
     */
    public function __construct()
    {
        require_once(dirname(__FILE__) . "/../view/admin/consulta-avaliacao-inscrito-seguranca.php");
    }

    /**
     * Gera e stream do DOCX do edital. Erros sao apresentados em HTML simples
     * (download falha antes do save), nao em JSON, pra dar feedback visivel.
     */
    public function gerarEditalDOCX($fws_, $debug = false)
    {
        try {
            $ordemEtapa = isset($fws_['ordemEtapa']) ? (int) $fws_['ordemEtapa'] : 0;
            if ($ordemEtapa <= 0) {
                // tenta fwsGet_ (form GET vem como $_GET tambem)
                $ordemEtapa = isset($_GET['ordemEtapa']) ? (int) $_GET['ordemEtapa'] : 0;
            }
            if ($ordemEtapa <= 0) {
                throw new Exception('Ordem da etapa nao informada (ordemEtapa).');
            }

            $filtro = isset($fws_['filtro']) ? (string) $fws_['filtro'] : '';
            if ($filtro === '' && isset($_GET['filtro'])) {
                $filtro = (string) $_GET['filtro'];
            }
            if ($filtro === '') {
                $filtro = 'todos';
            }
            // sanea — so aceita filtros conhecidos (mesma lista da listagem)
            $filtrosValidos = array('todos', 'pendente', 'avaliado', 'habilitados',
                                    'nao_habilitados', 'com_recurso', 'com_docs_ilegiveis');
            if (!in_array($filtro, $filtrosValidos, true)) {
                $filtro = 'todos';
            }

            $obj = new EditalPublicacao();
            $obj->gerarDOCX($ordemEtapa, $filtro, $debug);
            // gerarDOCX chama exit() — nada apos esta linha executa
        } catch (Exception $e) {
            // Erro pre-download: pagina HTML simples
            while (ob_get_level() > 0) { ob_end_clean(); }
            header('Content-Type: text/html; charset=UTF-8');
            echo '<!DOCTYPE html><html lang="pt-Br"><head><meta charset="UTF-8"><title>Erro</title></head><body style="font-family:Arial,sans-serif;padding:40px;">';
            echo '<div style="max-width:600px;margin:0 auto;background:#fff;border:1px solid #f5c6cb;border-radius:8px;padding:24px;">';
            echo '<h3 style="color:#a94442;margin-top:0;">N&atilde;o foi poss&iacute;vel gerar o edital</h3>';
            echo '<p style="color:#555;">' . htmlspecialchars($e->getMessage()) . '</p>';
            echo '<p><a href="javascript:history.back()" style="display:inline-block;padding:8px 18px;background:#4A90E2;color:#fff;border-radius:4px;text-decoration:none;">&larr; Voltar</a></p>';
            echo '</div></body></html>';
            exit();
        }
    }
}

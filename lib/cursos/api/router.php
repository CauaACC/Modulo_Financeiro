<?php
require_once(dirname(__FILE__) . '/../inscricoes/include/functions/router.php');
$router = new Router($fws_);

// ✅ Aqui você define só as personalizadas
$router->post('/cargaboletos/sisprime', 'GerenciarCargaBoleto@carregarBoletos');
$router->get('/chatwoot/localizarcontato', 'GerenciarChatWoot@localizarContato');
$router->get('/transparencia/pagamentos', 'GerenciarTransparencia@localizarPagamentos');
$router->get('/atendimento/inscrito', 'GerenciarAtendimento@buscarInscritoPorCpf');
$router->post('/atendimento/inscrito', 'GerenciarAtendimento@buscarInscritoPorCpf');
$router->post('/atendimento/cancelar', 'GerenciarAtendimento@cancelarInscricao');
// Ficha do inscrito - link enviado ao financeiro do gestor no aviso de
// cancelamento de inscricao paga (sessao de admin, nao usa token)
$router->get('/inscrito/abrir', 'GerenciarAtendimento@abrirInscricao');
$router->post('/transparencia/carga', 'GerenciarCargaTransparencia@receberCargaAPI');
$router->post('/appboleto/sync', 'GerenciarAppBoleto@sincronizarConfigEvento');

// Carga de pagamentos por arquivo (PagSeguro/maquininha, PayPal, Itau).
// Chamadas por pagSeguroOrquidarioHvCargaByCSV.py em recursosExtras, na ordem
// upload -> carregar -> associar. Rodam sem sessao, autorizadas pelo api_token.
$router->post('/pagamento/upload',   'GerenciarPagamentoUpload@salvarPagamentoUpload');
$router->post('/pagamento/carregar', 'GerenciarPagamentoUpload@carregarInscritoPagamento');
$router->post('/pagamento/associar', 'GerenciarPagamentoUpload@associarInscritoPagamento');
$router->get('/bolecode/teste', 'GerenciarBolecode@testarConexao');
$router->get('/bolecode/simular', 'GerenciarBolecode@simular');
$router->get('/bolecode/consultar', 'GerenciarBolecode@consultarPagamento');
$router->post('/bolecode/emitir', 'GerenciarBolecode@emitirBoleto');

// Nota Fiscal - solicitacao pelo inscrito e anexacao pelo gestor
$router->get ('/nota-fiscal/abrir',      'GerenciarNotaFiscalSolicitacao@abrirSolicitacao');
$router->post('/nota-fiscal/solicitar',  'GerenciarNotaFiscalSolicitacao@solicitar');
$router->post('/nota-fiscal/anexar',     'GerenciarNotaFiscalSolicitacao@anexar');
$router->post('/nota-fiscal/substituir', 'GerenciarNotaFiscalSolicitacao@substituir');
$router->get ('/nota-fiscal/baixar',     'GerenciarNotaFiscalSolicitacao@baixar');
$router->get ('/nota-fiscal/historico',  'GerenciarNotaFiscalSolicitacao@carregarHistorico');

// Nota Fiscal - emissao via Focus NFe (NFS-e nacional)
$router->post('/nfse/emitir',          'GerenciarNotaFiscal@emitir');
$router->post('/nfse/consultar',       'GerenciarNotaFiscal@consultarStatus');
$router->post('/nfse/cancelar',        'GerenciarNotaFiscal@cancelar');
$router->post('/nfse/notas-do-pedido', 'GerenciarNotaFiscal@carregarNotasDoPedido');
$router->post('/nfse/enviar-email',    'GerenciarNotaFiscal@enviarPorEmail');
$router->get ('/nfse/pdf',             'GerenciarNotaFiscal@baixarPdf');
$router->get ('/nfse/xml',             'GerenciarNotaFiscal@baixarXml');
$router->get ('/nfse/testar-conexao',  'GerenciarNotaFiscal@testarConexao');
// $router->get('/exemplo', fn() => echo "Olá mundo");

try {
    $router->executar();
} catch (\Throwable $e) {
    // Sem isso, qualquer excecao nao tratada dentro do controller (ex: gate de
    // permissao negando o contexto de servico) vira fatal sem handler = corpo
    // vazio com display_errors off. O script Python so via "0 bytes" no log,
    // sem pista nenhuma do que quebrou. Ver pagSeguroOrquidarioHvCargaByCSV.py.
    if (function_exists('executarLog')) {
        executarLog($e->getMessage() . ' | ' . $e->getFile() . ':' . $e->getLine(), 'api_erro_fatal', true);
    }
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['erro' => $e->getMessage()]);
}

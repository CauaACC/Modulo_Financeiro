<?php

/**
 * Emissao de Nota Fiscal de Servico (NFS-e) via Focus NFe.
 *
 * A nota e por PEDIDO e pelo VALOR DO PEDIDO — nao pelo valor pago: e comum
 * emitir a nota antes de receber (faturamento contra empresa/orgao, empenho).
 * O que foi enviado fica gravado como snapshot: nota emitida nao se edita,
 * so se cancela ou substitui.
 *
 * Fluxo manual: o gestor clica em Emitir na aba Pedidos do inscrito.
 * A solicitacao do inscrito (GerenciarNotaFiscalSolicitacaoAC) continua sendo
 * o gatilho e o anexo manual segue valendo como plano B.
 */
class GerenciarNotaFiscalAC {

    function __construct() {
        // Sem bloqueio global: cada metodo aplica o gate que precisa.
    }

    // ------------------------------------------------------------------
    // Gates e helpers
    // ------------------------------------------------------------------

    /**
     * Gate do modulo. ADM_GERAL entra explicitamente porque 999 nao e superset
     * automatico neste sistema — sem listar, o super admin ficaria de fora.
     */
    private function assegurarPermissaoGestor($codEvento = null) {
        $codEvento = $codEvento === null ? COD_EVENTO : $codEvento;
        $codUsuario = isset($_SESSION['_cod_usuario']) ? $_SESSION['_cod_usuario'] : '';

        if (empty($codUsuario)) {
            throw new Exception("Sessao expirada. Faca login novamente.");
        }

        $permissoes = array(enumSistema::SIS_CONTROLE_FINANCEIRO, enumSistema::SIS_GERENCIAR_ADM_GERAL);
        foreach ($permissoes as $perm) {
            if (@FWA_Autenticacao($codUsuario, $perm, 5, false, false, $codEvento)) {
                return;
            }
        }
        throw new Exception("Acesso restrito a usuarios com permissao de Controle Financeiro.");
    }

    /**
     * Carrega a nota garantindo que ela pertence ao evento da sessao e que o
     * usuario tem permissao NAQUELE evento. Sem isto, bastaria trocar o id na
     * requisicao para ler/cancelar nota de outro evento.
     */
    private function carregarNotaComPermissao($codNota, $debug = false) {
        if (!$codNota) {
            throw new Exception("Codigo da nota fiscal nao informado.");
        }

        $objNota = new NotaFiscal();
        $objNota->setCodNotaFiscal($codNota);
        if (!$objNota->getNotaFiscalById($debug)) {
            throw new Exception("Nota fiscal nao encontrada.");
        }

        $codEventoNota = $objNota->getCodEvento();
        if ($codEventoNota && COD_EVENTO && $codEventoNota != COD_EVENTO) {
            throw new Exception("Nota fiscal nao pertence a este evento.");
        }

        // Revalida no evento da propria nota, nao so no da sessao.
        $this->assegurarPermissaoGestor($codEventoNota ? $codEventoNota : COD_EVENTO);

        return $objNota;
    }

    private function getDiretorioNF() {
        $dir = WB_PATH_LOG_NOTA_FISCAL;
        verificarECriarPasta($dir);
        return $dir;
    }

    private function responder($ok, $mensagem, $extra = array()) {
        $retorno = array_merge(array(
            'statusMessage' => $ok,
            'message'       => $mensagem
        ), $extra);
        echo json_encode($retorno, JSON_UNESCAPED_UNICODE);
    }

    private function carregarPedido($codPedido, $codEvento) {
        if (!$codPedido) {
            throw new Exception("Codigo do pedido nao informado.");
        }
        $objPedido = new Pedido();
        $objPedido->setEventoId($codEvento);
        $objPedido->setId($codPedido);
        $objPedido->getPedidoById();

        // Pedido::getPedidoById() nao devolve o resultado do DAO (sempre null),
        // entao a carga se confirma por um campo que so existe vindo do banco.
        if (!$objPedido->getInscritoId()) {
            throw new Exception("Pedido nao encontrado neste evento.");
        }
        if ($objPedido->getEventoId() != $codEvento) {
            throw new Exception("Pedido nao pertence a este evento.");
        }
        return $objPedido;
    }

    private function carregarEmissor($codEvento) {
        $objEmissor = new NotaFiscalEmissor();
        if (!$objEmissor->getEmissorAtivoByEvento($codEvento)) {
            throw new Exception("Nenhum emissor de nota fiscal cadastrado para o gestor deste evento. "
                . "Cadastre em Financeiro > Emissores de Nota Fiscal.");
        }
        $objEmissor->validatorAptoParaEmitir();
        return $objEmissor;
    }

    /**
     * De onde saem os dados do tomador, em ordem de prioridade:
     *   1. Solicitacao de NF feita pelo inscrito (ele digitou para quem quer a nota)
     *   2. nome_recibo / cpf_recibo do cadastro (nota em nome da empresa dele)
     *   3. Cadastro do inscrito
     */
    private function resolverTomador(NotaFiscal $objNota, Pedido $objPedido, $codEvento) {
        $codInscrito = $objPedido->getInscritoId();

        $objInscrito = new Inscrito();
        $objInscrito->setCodEvento($codEvento);
        $objInscrito->setCodInscrito($codInscrito);
        if (!$objInscrito->getInscritoById()) {
            throw new Exception("Inscrito do pedido nao encontrado.");
        }

        // 3) Base: cadastro do inscrito
        $doc    = $objInscrito->getCpf();
        $nome   = $objInscrito->getNomeInscrito();
        $email  = $objInscrito->getEMail();
        $cep    = $objInscrito->getCep();
        $logra  = $objInscrito->getEndereco();
        $numero = $objInscrito->getNumero();
        $compl  = $objInscrito->getComplemento();
        $bairro = $objInscrito->getBairro();
        $cidade = $objInscrito->getCidade();
        $uf     = $objInscrito->getUf() ? $objInscrito->getUf() : $objInscrito->getEstado();
        $fone   = $objInscrito->getFoneCelular();

        // 2) Dados de recibo, quando o inscrito pede em nome de outro CPF/CNPJ
        if (trim((string) $objInscrito->getCpfRecibo()) != '') {
            $doc = $objInscrito->getCpfRecibo();
            if (trim((string) $objInscrito->getNomeRecibo()) != '') {
                $nome = $objInscrito->getNomeRecibo();
            }
        }

        // 1) Solicitacao do inscrito, se existir para algum pagamento deste pedido
        $objSolic = $this->buscarSolicitacaoDoPedido($objPedido, $codEvento);
        if ($objSolic !== null) {
            if (trim((string) $objSolic->getTomadorCpfCnpj()) != '') {
                $doc = $objSolic->getTomadorCpfCnpj();
            }
            if (trim((string) $objSolic->getTomadorNome()) != '') {
                $nome = $objSolic->getTomadorNome();
            }
            if (trim((string) $objSolic->getTomadorEmail()) != '') {
                $email = $objSolic->getTomadorEmail();
            }
            if (trim((string) $objSolic->getTomadorCep()) != '') {
                $cep = $objSolic->getTomadorCep();
            }
            // A solicitacao guarda o endereco num campo unico de texto. Se ela
            // informa endereco, ele vale INTEIRO: numero/complemento/bairro do
            // cadastro do inscrito nao podem ser grudados nele, senao a nota sai
            // com rua de um lugar e numero/bairro de outro.
            if (trim((string) $objSolic->getTomadorEndereco()) != '') {
                $logra  = $objSolic->getTomadorEndereco();
                $numero = '';
                $compl  = '';
                $bairro = '';
            }
            if (trim((string) $objSolic->getTomadorCidade()) != '') {
                $cidade = $objSolic->getTomadorCidade();
            }
            if (trim((string) $objSolic->getTomadorUf()) != '') {
                $uf = $objSolic->getTomadorUf();
            }
        }

        $objNota->setCodInscrito($codInscrito);
        $objNota->setTomadorCpfCnpj(preg_replace('/[^0-9]/', '', (string) $doc));
        $objNota->setTomadorNome($nome);
        $objNota->setTomadorEmail($email);
        $objNota->setTomadorTelefone($fone);
        $objNota->setTomadorCep(preg_replace('/[^0-9]/', '', (string) $cep));
        $objNota->setTomadorLogradouro($logra);
        $objNota->setTomadorNumero($numero);
        $objNota->setTomadorComplemento($compl);
        $objNota->setTomadorBairro($bairro);
        $objNota->setTomadorUf($uf);

        // Guarda tambem o NOME da cidade. Nao e coluna da tabela (a nota grava
        // o codigo IBGE), entao o insert ignora — serve para a tela poder
        // pre-selecionar a cidade no select, que precisa do nome.
        $objNota->setTomadorCidadeNome($cidade);

        // Codigo IBGE nao existe no cadastro; resolvemos por nome + UF.
        $codigoMunicipio = MunicipioIbge::resolver($cidade, $uf);
        if ($codigoMunicipio != '') {
            $objNota->setTomadorCodigoMunicipio($codigoMunicipio);
        }

        return $objNota;
    }

    /**
     * Fecha o ciclo com a solicitacao do inscrito: nota autorizada vira o
     * arquivo_nf da solicitacao, que passa a estar atendida, e o inscrito e
     * avisado. Sem isto ele pede a nota, a nota sai, e ele nunca fica sabendo.
     *
     * Nao lanca: falha aqui nao pode derrubar uma nota que ja foi autorizada
     * na prefeitura — o vinculo se refaz na proxima consulta de status.
     */
    private function atenderSolicitacaoDaNota(NotaFiscal $objNota, $debug = false) {
        try {
            if (!$objNota->ehAutorizada()) {
                return false;
            }
            if ($objNota->getOrigem() != 'PEDIDO') {
                return false;
            }

            $objPedido = new Pedido();
            $objPedido->setEventoId($objNota->getCodEvento());
            $objPedido->setId($objNota->getOrigemId());
            $objPedido->getPedidoById();
            if (!$objPedido->getInscritoId()) {
                return false;
            }

            $objPag = new Pagamento();
            $objPag->setCodEvento($objNota->getCodEvento());
            $objPag->setPedidoId($objPedido->getId());
            $objPag->getPagamentoByPedidoId();
            if (!$objPag->getCodPagamento()) {
                return false;
            }

            $objSolic = new NotaFiscalSolicitacao();
            $objSolic->setCodEvento($objNota->getCodEvento());
            $objSolic->setCodPagamento($objPag->getCodPagamento());
            if (!$objSolic->getSolicitacaoPendenteByPagamento($debug)) {
                return false; // ninguem pediu, ou ja foi atendida
            }

            // O PDF precisa estar em disco: a area do inscrito serve o arquivo
            // local, nao a URL da Focus.
            $arquivoPdf = '';
            try {
                $arquivoPdf = $this->garantirArquivoLocal($objNota, 'pdf', $debug);
            } catch (Exception $e) {
                $arquivoPdf = ''; // vincula mesmo sem o PDF; a consulta refaz depois
            }

            $objSolic->setCodNotaFiscal($objNota->getCodNotaFiscal());
            $objSolic->setArquivoNf($arquivoPdf);
            $objSolic->vincularNotaFiscal($debug);

            try {
                $objSolicAC = new GerenciarNotaFiscalSolicitacaoAC();
                $objSolicAC->enviarEmailAoInscrito($objSolic);
            } catch (Exception $e) {
                // aviso e desejavel, nao essencial
            }

            try {
                LogAcessoDadosSensiveis::registrar(
                    'NOTA_FISCAL',
                    $objNota->getCodNotaFiscal(),
                    'ATENDER_SOLICITACAO',
                    'solicitacao=' . $objSolic->getCodSolicitacao()
                );
            } catch (Exception $ignore) {}

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Desfaz o atendimento quando a nota e cancelada: a solicitacao volta a
     * ficar pendente, porque o inscrito continua querendo a nota.
     */
    private function desatenderSolicitacaoDaNota(NotaFiscal $objNota, $debug = false) {
        try {
            $objSolic = new NotaFiscalSolicitacao();
            $objSolic->setCodEvento($objNota->getCodEvento());
            $objSolic->setCodNotaFiscal($objNota->getCodNotaFiscal());
            $objSolic->desvincularNotaFiscal((string) $objNota->getArquivoPdf(), $debug);
        } catch (Exception $e) {
            // idem: nao derruba o cancelamento ja aceito pela prefeitura
        }
    }

    private function buscarSolicitacaoDoPedido(Pedido $objPedido, $codEvento) {
        try {
            $objPag = new Pagamento();
            $objPag->setCodEvento($codEvento);
            $objPag->setPedidoId($objPedido->getId());
            $objPag->getPagamentoByPedidoId();

            // Mesmo caso do Pedido: o metodo nao devolve o resultado do DAO.
            if (!$objPag->getCodPagamento()) {
                return null;
            }
            $objSolic = new NotaFiscalSolicitacao();
            $objSolic->setCodEvento($codEvento);
            $objSolic->setCodPagamento($objPag->getCodPagamento());
            if ($objSolic->getSolicitacaoAtivaByPagamento()) {
                return $objSolic;
            }
        } catch (Exception $e) {
            // Solicitacao e um plus: nao ter nao impede emitir.
        }
        return null;
    }

    private function montarDescricaoDoPedido(Pedido $objPedido) {
        $descricao = trim((string) $objPedido->getDescricaoCompleta());
        if ($descricao == '') {
            $descricao = trim((string) $objPedido->getDescricao());
        }
        if ($descricao == '') {
            $descricao = 'Inscricao no evento ' . (defined('NOME_EVENTO') ? NOME_EVENTO : '');
        }
        return $descricao;
    }

    // ------------------------------------------------------------------
    // Acoes
    // ------------------------------------------------------------------

    /**
     * Emite a nota do pedido. Um clique no caminho feliz: os dados saem do
     * pedido + cadastro + emissor. Se faltar dado obrigatorio, a validacao
     * diz exatamente o que falta para a tela pedir so isso.
     */
    public function emitir($fws_, $debug = false) {
        try {
            $this->assegurarPermissaoGestor();

            $codEvento = COD_EVENTO;
            $codPedido = isset($fws_['cod_pedido']) ? $fws_['cod_pedido'] : '';

            $objPedido = $this->carregarPedido($codPedido, $codEvento);
            $objEmissor = $this->carregarEmissor($codEvento);

            // Idempotencia no nosso lado: pedido que ja tem nota valida nao emite de novo.
            $objExistente = new NotaFiscal();
            $objExistente->setOrigem('PEDIDO');
            $objExistente->setOrigemId($objPedido->getId());
            if ($objExistente->getNotaVigenteByOrigem()) {
                throw new Exception("Este pedido ja possui nota fiscal ("
                    . $objExistente->getDescricaoStatus() . "). Cancele a atual antes de emitir outra.");
            }

            // Ambiente do EMISSOR: permite testar um gestor em homologacao
            // enquanto os demais seguem emitindo em producao. Vazio/NULL cai
            // no global do env.php (comportamento antigo).
            $client = new FocusNFeClient($objEmissor->getAmbiente());

            $objNota = $this->montarNotaDoPedido($fws_, $objPedido, $objEmissor, $client->getAmbiente(), $codEvento);
            $objNota->setReferencia($this->gerarReferenciaDisponivel('PEDIDO', $objPedido->getId()));

            // Numero do DPS e reservado ANTES de montar o payload e fica
            // gravado na nota. Consumido mesmo em rejeicao — buraco na
            // sequencia e aceitavel, numero repetido nao e.
            $numeroDps = $objEmissor->reservarNumeroDps($client->getAmbiente(), $debug);
            if ($numeroDps > 0) {
                $objNota->setSerieDps($objEmissor->getSerieDps());
                $objNota->setNumeroDps($numeroDps);
            }

            $payload = NotaFiscalPayloadNacional::montar($objEmissor, $objNota);
            $objNota->setRequestJson(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            // Grava antes de chamar a API: se a chamada cair no meio, o registro
            // existe e a consulta de status recupera o que aconteceu.
            $objNota->gravar($debug);

            $camposCompletados = $this->completarCadastroDoInscrito($objNota, $fws_, $debug);

            // Conta o uso para o autocomplete aprender o que este emissor
            // realmente fatura. Fica na emissao, nao na previa: so olhar nao
            // pode reordenar a lista.
            try {
                $objDAOTrib = new NotaFiscalTributacaoDAO();
                $objDAOTrib->registrarUso($objEmissor->getCodEmissor(),
                                          $objNota->getCodigoTributacaoNacionalIss(), $debug);
            } catch (Exception $e) {}

            $resposta = $client->emitirNfse($objNota->getReferencia(), $payload);
            $objNota->aplicarRespostaFocus($resposta);
            $objNota->atualizarRetorno($debug);

            // Autorizou de primeira: ja fecha a solicitacao do inscrito.
            // Se voltou PROCESSANDO, quem fecha e a consulta de status.
            $this->atenderSolicitacaoDaNota($objNota, $debug);

            try {
                LogAcessoDadosSensiveis::registrar(
                    'NOTA_FISCAL',
                    $objNota->getCodNotaFiscal(),
                    'EMITIR',
                    'pedido=' . $objPedido->getId() . '; ref=' . $objNota->getReferencia()
                );
            } catch (Exception $ignore) {}

            if ($objNota->getStatus() == 'ERRO') {
                $this->responder(false,
                    "A nota foi rejeitada: " . $objNota->getMensagemErro(),
                    array('codNotaFiscal' => $objNota->getCodNotaFiscal(), 'status' => $objNota->getStatus()));
                return;
            }

            $msg = $objNota->getStatus() == 'AUTORIZADA'
                 ? "Nota fiscal autorizada com sucesso."
                 : "Nota enviada. A prefeitura esta processando — a tela atualiza sozinha em alguns segundos.";

            if (!empty($camposCompletados)) {
                $msg .= "\nCadastro do inscrito completado: " . implode(', ', $camposCompletados) . '.';
            }

            $this->responder(true, $msg, array(
                'codNotaFiscal' => $objNota->getCodNotaFiscal(),
                'status'        => $objNota->getStatus(),
                'numero'        => $objNota->getNumero(),
                'camposCompletados' => $camposCompletados
            ));

        } catch (Exception $e) {
            $this->responder(false, $e->getMessage());
        }
    }

    /**
     * Monta a nota a partir do pedido — SEM reservar numero, SEM gravar e SEM
     * chamar a API. Usado pela emissao E pela previa: se fossem caminhos
     * diferentes, a previa poderia mostrar uma coisa e a nota sair outra.
     */
    private function montarNotaDoPedido($fws_, Pedido $objPedido, NotaFiscalEmissor $objEmissor,
                                        $ambiente, $codEvento) {
        $objNota = new NotaFiscal();
        $objNota->setCodEmissor($objEmissor->getCodEmissor());
        $objNota->setCodEvento($codEvento);
        $objNota->setOrigem('PEDIDO');
        $objNota->setOrigemId($objPedido->getId());
        $objNota->setAmbiente($ambiente);
        $objNota->setTipo('NFSE');
        $objNota->setStatus('PROCESSANDO');
        $objNota->setValorServico($objPedido->getValorTotal());

        // Servico e complemento sao campos DIFERENTES na NFS-e nacional:
        //   xDescServ (descricao_servico)          -> o que foi prestado
        //   xInfComp  (informacoes_complementares) -> dado acessorio
        // Antes iam concatenados num campo so, o que misturava as duas coisas
        // e ainda gastava o limite do campo errado.
        $partes = $this->partesDaDiscriminacao($fws_, $objPedido, $objEmissor);
        $objNota->setDescricaoServico(trim($partes['servico']));
        $objNota->setInformacoesComplementares(trim($partes['complemento']));
        $objNota->setDataCompetencia(date('Y-m-d'));

        // Codigo de tributacao: o do emissor e o PADRAO, nao uma regra fixa.
        // O mesmo CNPJ fatura servicos diferentes (mensalidade, inscricao,
        // ingresso) e cada um tem seu codigo.
        $objNota->setCodigoTributacaoNacionalIss($this->resolverCodigoTributacao($fws_, $objEmissor));

        $this->resolverTomador($objNota, $objPedido, $codEvento);

        // O que foi digitado na tela vence o que veio do cadastro: a nota e um
        // snapshot, entao corrigir aqui nao altera o cadastro do inscrito.
        $this->aplicarTomadorInformado($objNota, $fws_);

        return $objNota;
    }

    /**
     * Sobrescreve o tomador com o que veio do formulario de emissao, quando
     * veio. Campo em branco no form significa "mantem o resolvido", nao "apaga".
     */
    private function aplicarTomadorInformado(NotaFiscal $objNota, $fws_) {
        $mapa = array(
            'tomador_nome'        => 'setTomadorNome',
            'tomador_cpf_cnpj'    => 'setTomadorCpfCnpj',
            'tomador_email'       => 'setTomadorEmail',
            'tomador_cep'         => 'setTomadorCep',
            'tomador_logradouro'  => 'setTomadorLogradouro',
            'tomador_numero'      => 'setTomadorNumero',
            'tomador_complemento' => 'setTomadorComplemento',
            'tomador_bairro'      => 'setTomadorBairro',
            'tomador_uf'          => 'setTomadorUf'
        );

        foreach ($mapa as $campo => $metodo) {
            if (!isset($fws_[$campo])) {
                continue;
            }
            $valor = trim((string) $fws_[$campo]);
            if ($valor === '') {
                continue;
            }
            if ($campo === 'tomador_cpf_cnpj' || $campo === 'tomador_cep') {
                $valor = preg_replace('/[^0-9]/', '', $valor);
            }
            $objNota->$metodo($valor);
        }

        // Cidade digitada: resolve o codigo IBGE de novo, porque e ele que a
        // prefeitura le — o nome da cidade sozinho nao serve.
        $cidade = isset($fws_['tomador_cidade']) ? trim((string) $fws_['tomador_cidade']) : '';
        if ($cidade !== '') {
            $codigo = MunicipioIbge::resolver($cidade, $objNota->getTomadorUf());
            if ($codigo != '') {
                $objNota->setTomadorCodigoMunicipio($codigo);
            }
        }
    }

    /**
     * Normaliza a lista de e-mails de copia: aceita virgula ou ponto-e-virgula,
     * descarta o que nao tem cara de e-mail e nao repete endereco.
     *
     * Filtrar aqui, e nao na hora de enviar, evita que um endereco torto fique
     * gravado na nota e volte a aparecer em todas as emissoes seguintes daquele
     * inscrito — que e justamente o que este campo faz.
     */
    private static function normalizarListaEmails($texto) {
        $partes = preg_split('/[;,]/', (string) $texto);
        $validos = array();

        foreach ($partes as $parte) {
            $email = trim($parte);
            if ($email === '') {
                continue;
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }
            if (!in_array(strtolower($email), array_map('strtolower', $validos))) {
                $validos[] = $email;
            }
        }

        $lista = implode(', ', $validos);
        return (strlen($lista) > 500) ? substr($lista, 0, 500) : $lista;
    }

    /**
     * Copia usada na ULTIMA nota deste inscrito. E o que faz o campo "guardar":
     * o gestor digita uma vez e as proximas emissoes ja saem preenchidas.
     *
     * Fica na nota e nao em `tab_inscritos` porque aquela tabela e core e tem
     * espelhos de auditoria — coluna nova ali obrigaria a replicar nos dois.
     */
    private function copiaAnteriorDoInscrito($codEvento, $codInscrito, $debug = false) {
        if (!$codInscrito) {
            return '';
        }
        try {
            $objDAO = new NotaFiscalDAO();
            return (string) $objDAO->getUltimaCopiaDoInscrito($codEvento, $codInscrito, $debug);
        } catch (Exception $e) {
            return '';
        }
    }

    /**
     * Completa o cadastro do inscrito com o que foi digitado — so nos campos
     * que estao vazios. Pedido do usuario: "poderia arrumar o cadastro a partir
     * daqui se nao estiver preenchido".
     */
    private function completarCadastroDoInscrito(NotaFiscal $objNota, $fws_, $debug = false) {
        try {
            if (empty($fws_['completar_cadastro']) || $fws_['completar_cadastro'] == '0') {
                return array();
            }
            if (!$objNota->getCodInscrito()) {
                return array();
            }

            $dados = array(
                'cep'         => $objNota->getTomadorCep(),
                'endereco'    => $objNota->getTomadorLogradouro(),
                'numero'      => $objNota->getTomadorNumero(),
                'complemento' => $objNota->getTomadorComplemento(),
                'bairro'      => $objNota->getTomadorBairro(),
                'cidade'      => isset($fws_['tomador_cidade']) ? trim((string) $fws_['tomador_cidade']) : '',
                'uf'          => $objNota->getTomadorUf(),
                'estado'      => $objNota->getTomadorUf(),
                // Razao social vai para nome_recibo, nao para nome_inscrito: o
                // nome do inscrito pode ser apelido proposital.
                'nome_recibo' => $objNota->getTomadorNome(),
                // So entra se o inscrito estiver SEM e-mail (regra do DAO). Nao
                // troca o e-mail de quem ja tem: e por onde ele acessa o sistema.
                'e_mail'      => $objNota->getTomadorEmail()
            );

            $objDAO = new NotaFiscalDAO();
            return $objDAO->completarCadastroInscrito($objNota->getCodEvento(),
                                                      $objNota->getCodInscrito(), $dados, $debug);
        } catch (Exception $e) {
            return array();
        }
    }

    /**
     * Previa da nota: mostra o que sera emitido ANTES de emitir.
     *
     * Nota fiscal e documento com valor legal — depois de autorizada, corrigir
     * significa cancelar dentro do prazo da prefeitura.
     *
     * NAO reserva numero de DPS, NAO grava e NAO chama a API: so olhar nao pode
     * queimar numero. O numero mostrado e o proximo previsto.
     */
    public function previaEmissao($fws_, $debug = false) {
        try {
            $this->assegurarPermissaoGestor();

            $codEvento = COD_EVENTO;
            $codPedido = isset($fws_['cod_pedido']) ? $fws_['cod_pedido'] : '';

            $objPedido = $this->carregarPedido($codPedido, $codEvento);
            $objEmissor = $this->carregarEmissor($codEvento);
            // Ambiente do EMISSOR: permite testar um gestor em homologacao
            // enquanto os demais seguem emitindo em producao. Vazio/NULL cai
            // no global do env.php (comportamento antigo).
            $client = new FocusNFeClient($objEmissor->getAmbiente());

            $objNota = $this->montarNotaDoPedido($fws_, $objPedido, $objEmissor,
                                                 $client->getAmbiente(), $codEvento);

            $payload = NotaFiscalPayloadNacional::montar($objEmissor, $objNota);
            $partesDisc = $this->partesDaDiscriminacao($fws_, $objPedido, $objEmissor);

            $this->responder(true, '', array(
                'ambiente' => $client->getAmbiente(),
                'emissor'  => array(
                    'razaoSocial'   => $objEmissor->getRazaoSocial(),
                    'cnpj'          => $objEmissor->getCnpj(),
                    'serieDps'      => $objEmissor->getSerieDps(),
                    'proximoNumero' => $objEmissor->getProximoNumeroDpsDoAmbiente($client->getAmbiente())
                ),
                'tomador'  => array(
                    'nome'      => $objNota->getTomadorNome(),
                    'documento' => $objNota->getTomadorCpfCnpj(),
                    'endereco'  => $this->formatarEnderecoPrevia($objNota),
                    'cep'       => $objNota->getTomadorCep(),
                    'municipio' => $objNota->getTomadorCodigoMunicipio(),
                    'uf'        => $objNota->getTomadorUf(),
                    'email'     => $objNota->getTomadorEmail(),
                    'cidade'    => $objNota->getTomadorCidadeNome(),
                    'logradouro'  => $objNota->getTomadorLogradouro(),
                    'numeroEnd'   => $objNota->getTomadorNumero(),
                    'complemento' => $objNota->getTomadorComplemento(),
                    'bairro'      => $objNota->getTomadorBairro()
                ),
                'servico'  => array(
                    'descricao'          => $objNota->getDescricaoServico(),
                    'descricaoPedido'    => $this->montarDescricaoDoPedido($objPedido),
                    'parteServico'       => $partesDisc['servico'],
                    'parteComplemento'   => $partesDisc['complemento'],
                    'complementoEmissor' => (string) $objEmissor->getInformacoesComplementares(),
                    'codigoTributacao' => $objNota->getCodigoTributacaoNacionalIss(),
                    'descricaoTributacao' => $this->descricaoTributacao($objNota->getCodigoTributacaoNacionalIss()),
                    'valor'            => number_format((float) $objNota->getValorServico(), 2, ',', '.'),
                    'competencia'      => $objNota->getDataCompetencia()
                ),
                'avisos'   => $this->avisosDaPrevia($objNota, $objEmissor),
                'payload'  => $payload
            ));

        } catch (Exception $e) {
            $this->responder(false, $e->getMessage());
        }
    }

    /**
     * Descricao oficial do cTribNac. Sem ela o campo e um numero sem
     * significado — e numero errado so aparece na rejeicao.
     */
    private function descricaoTributacao($codigo) {
        if (trim((string) $codigo) == '') {
            return '';
        }
        try {
            $objDAO = new NotaFiscalTributacaoDAO();
            return $objDAO->getDescricao($codigo);
        } catch (Exception $e) {
            return '';
        }
    }

    /**
     * Junta logradouro/numero/bairro sem deixar virgula ou espaco solto quando
     * alguma parte esta vazia (caso comum: endereco veio da solicitacao, que
     * guarda tudo num campo unico).
     */
    private function formatarEnderecoPrevia(NotaFiscal $objNota) {
        $partes = array();
        foreach (array($objNota->getTomadorLogradouro(), $objNota->getTomadorNumero(),
                       $objNota->getTomadorBairro()) as $parte) {
            $parte = trim((string) $parte);
            if ($parte !== '') {
                $partes[] = $parte;
            }
        }
        return implode(', ', $partes);
    }

    /**
     * Autocomplete do codigo de tributacao: busca por numero ou por texto do
     * servico, com os mais usados por este emissor no topo.
     */
    public function buscarTributacao($fws_, $debug = false) {
        try {
            $this->assegurarPermissaoGestor();

            $termo = isset($fws_['termo']) ? $fws_['termo'] : '';

            $codEmissor = 0;
            try {
                $objEmissor = $this->carregarEmissor(COD_EVENTO);
                $codEmissor = $objEmissor->getCodEmissor();
            } catch (Exception $e) {
                $codEmissor = 0; // sem emissor: busca sem favoritos
            }

            $objDAO = new NotaFiscalTributacaoDAO();
            $lista = $objDAO->buscar($termo, $codEmissor, 20, $debug);

            $this->responder(true, '', array('itens' => $lista));

        } catch (Exception $e) {
            $this->responder(false, $e->getMessage(), array('itens' => array()));
        }
    }

    /**
     * Problemas que a prefeitura costuma rejeitar, detectados ANTES do envio.
     * Nao impedem a emissao — quem decide e o gestor —, mas ninguem deveria
     * emitir sem ver.
     */
    private function avisosDaPrevia(NotaFiscal $objNota, NotaFiscalEmissor $objEmissor) {
        $avisos = array();

        $doc = preg_replace('/[^0-9]/', '', (string) $objNota->getTomadorCpfCnpj());
        if ($doc == '') {
            $avisos[] = 'Tomador sem CPF/CNPJ — a prefeitura deve rejeitar.';
        } elseif (strlen($doc) == 11 && function_exists('validarCPF') && !validarCPF($doc)) {
            $avisos[] = 'CPF do tomador e invalido.';
        } elseif (strlen($doc) == 14 && function_exists('validarCNPJ') && !validarCNPJ($doc)) {
            $avisos[] = 'CNPJ do tomador e invalido.';
        } elseif (strlen($doc) != 11 && strlen($doc) != 14) {
            $avisos[] = 'CPF/CNPJ do tomador nao tem 11 nem 14 digitos.';
        }

        if (trim((string) $objNota->getTomadorNome()) == '') {
            $avisos[] = 'Tomador sem nome/razao social.';
        }
        if (!$objNota->getTomadorCodigoMunicipio()) {
            $avisos[] = 'Municipio do tomador nao identificado pelo nome da cidade — a nota vai sem ele.';
        }
        if (trim((string) $objNota->getTomadorCep()) == '') {
            $avisos[] = 'Tomador sem CEP.';
        }
        // O endereco do tomador so entra na nota COMPLETO (logradouro, bairro,
        // CEP e municipio). Faltando um, nenhum vai — e melhor avisar do que
        // deixar a prefeitura rejeitar por schema.
        $faltando = array();
        if (trim((string) $objNota->getTomadorLogradouro()) == '') { $faltando[] = 'logradouro'; }
        if (trim((string) $objNota->getTomadorBairro()) == '')     { $faltando[] = 'bairro'; }
        if (trim((string) $objNota->getTomadorCep()) == '')        { $faltando[] = 'CEP'; }
        if (!$objNota->getTomadorCodigoMunicipio())                { $faltando[] = 'municipio'; }

        if (!empty($faltando) && count($faltando) < 4) {
            $avisos[] = 'Endereco do tomador incompleto (falta ' . implode(', ', $faltando)
                      . ') — a nota vai SEM endereco. Complete para que ele apareca.';
        }
        if (count($faltando) == 0 && trim((string) $objNota->getTomadorNumero()) == '') {
            $avisos[] = 'Endereco sem numero — a nota sai com S/N.';
        }

        if ((float) $objNota->getValorServico() <= 0) {
            $avisos[] = 'Valor do pedido e zero.';
        }
        if (trim((string) $objNota->getCodigoTributacaoNacionalIss()) == '') {
            $avisos[] = 'Sem codigo de tributacao nacional.';
        } elseif ($this->descricaoTributacao($objNota->getCodigoTributacaoNacionalIss()) == '') {
            $avisos[] = 'Codigo de tributacao ' . $objNota->getCodigoTributacaoNacionalIss()
                      . ' nao consta na tabela nacional — confira antes de emitir.';
        }
        if (trim((string) $objEmissor->getSerieDps()) == '') {
            $avisos[] = 'Emissor sem serie de DPS: a numeracao ficara por conta da prefeitura.';
        }

        return $avisos;
    }

    /**
     * Texto final da discriminacao: o que descreve o servico mais o rodape de
     * informacoes complementares (dados bancarios, PIX). Os dois vem do
     * formulario quando informados; senao, do pedido e do emissor.
     *
     * Fica junto porque e assim que a prefeitura recebe: um campo so. Separar
     * na tela e conveniencia de edicao, nao estrutura do documento.
     */
    private function montarDiscriminacao($fws_, Pedido $objPedido, NotaFiscalEmissor $objEmissor) {
        $partes = $this->partesDaDiscriminacao($fws_, $objPedido, $objEmissor);

        $texto = trim($partes['servico']);
        if (trim($partes['complemento']) !== '') {
            $texto .= "\n" . trim($partes['complemento']);
        }
        return $texto;
    }

    /**
     * As duas metades separadas, para a tela editar cada uma. Campo ausente no
     * form = usa o padrao; campo presente e vazio = o usuario apagou de
     * proposito (some da nota).
     */
    private function partesDaDiscriminacao($fws_, Pedido $objPedido, NotaFiscalEmissor $objEmissor) {
        // Campo AUSENTE = a tela ainda nao editou nada, entao vale o padrao.
        // Campo PRESENTE (mesmo vazio) = o usuario mexeu e mandou assim.
        $servico = isset($fws_['descricao_servico'])
                 ? trim((string) $fws_['descricao_servico'])
                 : $this->montarDescricaoDoPedido($objPedido);

        $complemento = isset($fws_['informacoes_complementares'])
                     ? trim((string) $fws_['informacoes_complementares'])
                     : trim((string) $objEmissor->getInformacoesComplementares());

        return array('servico' => $servico, 'complemento' => $complemento);
    }

    /**
     * Codigo de tributacao nacional (cTribNac) desta nota: o informado na tela,
     * ou o padrao do emissor quando nao vier nada.
     */
    private function resolverCodigoTributacao($fws_, NotaFiscalEmissor $objEmissor) {
        $informado = isset($fws_['codigo_tributacao_nacional_iss'])
                   ? preg_replace('/[^0-9]/', '', (string) $fws_['codigo_tributacao_nacional_iss'])
                   : '';

        if ($informado == '') {
            return $objEmissor->getCodigoTributacaoNacionalIss();
        }
        if (strlen($informado) != 6) {
            throw new Exception("Codigo de tributacao nacional deve ter 6 digitos. Informado: " . $informado);
        }
        return $informado;
    }

    /**
     * A ref e unica por nota na Focus. Reemissao apos rejeicao precisa de ref
     * nova, senao a API devolve a nota anterior em vez de emitir.
     */
    private function gerarReferenciaDisponivel($origem, $origemId) {
        for ($tentativa = 1; $tentativa <= 50; $tentativa++) {
            $ref = NotaFiscal::montarReferencia($origem, $origemId, $tentativa);
            $obj = new NotaFiscal();
            $obj->setReferencia($ref);
            if (!$obj->getNotaFiscalByReferencia()) {
                return $ref;
            }
        }
        throw new Exception("Nao foi possivel gerar referencia para a nota (muitas tentativas anteriores).");
    }

    public function consultarStatus($fws_, $debug = false) {
        try {
            $this->assegurarPermissaoGestor();

            $codNota = isset($fws_['cod_nota_fiscal']) ? $fws_['cod_nota_fiscal'] : '';
            $objNota = $this->carregarNotaComPermissao($codNota, $debug);

            $client = new FocusNFeClient($objNota->getAmbiente());
            $resposta = $client->consultarNfse($objNota->getReferencia());

            $objNota->aplicarRespostaFocus($resposta);
            $objNota->atualizarRetorno($debug);

            // A nota pode ter acabado de ser autorizada pela prefeitura.
            $this->atenderSolicitacaoDaNota($objNota, $debug);

            $this->responder(true, "Status atualizado: " . $objNota->getDescricaoStatus(), array(
                'codNotaFiscal' => $objNota->getCodNotaFiscal(),
                'status'        => $objNota->getStatus(),
                'numero'        => $objNota->getNumero(),
                'mensagemErro'  => $objNota->getMensagemErro()
            ));

        } catch (Exception $e) {
            $this->responder(false, $e->getMessage());
        }
    }

    public function cancelar($fws_, $debug = false) {
        try {
            $this->assegurarPermissaoGestor();

            $codNota = isset($fws_['cod_nota_fiscal']) ? $fws_['cod_nota_fiscal'] : '';
            $motivo  = isset($fws_['motivo_cancelamento']) ? trim($fws_['motivo_cancelamento']) : '';

            $objNota = $this->carregarNotaComPermissao($codNota, $debug);
            $objNota->setMotivoCancelamento($motivo);
            $objNota->validatorCancelar();

            if ($objNota->ehCancelada()) {
                throw new Exception("Esta nota ja esta cancelada.");
            }
            if (!$objNota->ehAutorizada()) {
                throw new Exception("So e possivel cancelar nota autorizada. Status atual: "
                    . $objNota->getDescricaoStatus() . ".");
            }

            $client = new FocusNFeClient($objNota->getAmbiente());
            $resposta = $client->cancelarNfse($objNota->getReferencia(), $motivo);

            if ($resposta['http'] >= 400) {
                $objNota->aplicarRespostaFocus($resposta);
                $objNota->atualizarRetorno($debug);
                throw new Exception("A prefeitura recusou o cancelamento: " . $objNota->getMensagemErro());
            }

            $objNota->setResponseJson(json_encode($resposta['corpo'], JSON_UNESCAPED_UNICODE));
            $objNota->registrarCancelamento($debug);

            // Nota cancelada nao atende mais ninguem: a solicitacao volta a pendente.
            $this->desatenderSolicitacaoDaNota($objNota, $debug);

            try {
                LogAcessoDadosSensiveis::registrar(
                    'NOTA_FISCAL',
                    $objNota->getCodNotaFiscal(),
                    'CANCELAR',
                    'motivo=' . $motivo
                );
            } catch (Exception $ignore) {}

            $this->responder(true, "Nota fiscal cancelada.");

        } catch (Exception $e) {
            $this->responder(false, $e->getMessage());
        }
    }

    /**
     * Lista as notas do pedido para a aba Pedidos. Inclui as canceladas e
     * rejeitadas: o historico e o que explica por que houve reemissao.
     */
    public function carregarNotasDoPedido($fws_, $debug = false) {
        try {
            $this->assegurarPermissaoGestor();

            $codPedido = isset($fws_['cod_pedido']) ? $fws_['cod_pedido'] : '';

            // Carrega o pedido filtrando por evento: impede listar notas de
            // pedido de outro evento so trocando o id na requisicao.
            $objPedido = $this->carregarPedido($codPedido, COD_EVENTO);

            $objNota = new NotaFiscal();
            $objNota->setOrigem('PEDIDO');
            $objNota->setOrigemId($objPedido->getId());
            $notas = $objNota->listarNotasByOrigem($debug);

            $lista = array();
            foreach ($notas as $item) {
                $lista[] = array(
                    'codNotaFiscal'     => $item->getCodNotaFiscal(),
                    'referencia'        => $item->getReferencia(),
                    'status'            => $item->getStatus(),
                    'descricaoStatus'   => $item->getDescricaoStatus(),
                    'numero'            => $item->getNumero(),
                    'valorServico'      => $item->getValorServico(),
                    'ambiente'          => $item->getAmbiente(),
                    'dataEmissao'       => $item->getDataEmissao(),
                    'tomadorNome'       => $item->getTomadorNome(),
                    'tomadorCpfCnpj'    => $item->getTomadorCpfCnpj(),
                    'mensagemErro'      => $item->getMensagemErro(),
                    'temPdf'            => ($item->getUrlPdf() || $item->getArquivoPdf()) ? 1 : 0,
                    'temXml'            => ($item->getUrlXml() || $item->getArquivoXml()) ? 1 : 0,
                    'motivoCancelamento' => $item->getMotivoCancelamento(),
                    'dataEnvioEmail'    => $item->getDataEnvioEmail()
                                           ? date('d/m/Y H:i', strtotime($item->getDataEnvioEmail())) : '',
                    'emailEnvio'        => $item->getEmailEnvio()
                );
            }

            // Padrao do emissor vai junto para a tela pre-preencher o codigo
            // de tributacao sem uma segunda requisicao.
            $padraoEmissor = null;
            try {
                $objEmissor = $this->carregarEmissor(COD_EVENTO);
                $padraoEmissor = array(
                    'razaoSocial' => $objEmissor->getRazaoSocial(),
                    'codigoTributacaoNacionalIss' => $objEmissor->getCodigoTributacaoNacionalIss()
                );
            } catch (Exception $e) {
                $padraoEmissor = null;
            }

            $this->responder(true, '', array(
                'notas' => $lista,
                'solicitacao' => $this->resumirSolicitacaoPendente($objPedido, $debug),
                'emissor' => $padraoEmissor
            ));

        } catch (Exception $e) {
            $this->responder(false, $e->getMessage(), array('notas' => array()));
        }
    }

    /**
     * O inscrito pediu nota para este pedido e ainda nao foi atendido?
     * E o que justifica o gestor clicar em Emitir agora.
     */
    private function resumirSolicitacaoPendente(Pedido $objPedido, $debug = false) {
        try {
            $objSolic = $this->buscarSolicitacaoDoPedido($objPedido, COD_EVENTO);
            if ($objSolic === null || $objSolic->getCodNotaFiscal()) {
                return null;
            }
            return array(
                'codSolicitacao'  => $objSolic->getCodSolicitacao(),
                'dataSolicitacao' => $objSolic->getDataInclusao(),
                'tomadorNome'     => $objSolic->getTomadorNome(),
                'tomadorCpfCnpj'  => $objSolic->getTomadorCpfCnpj(),
                'observacao'      => $objSolic->getObservacaoInscrito()
            );
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Envia a nota autorizada por e-mail ao inscrito, com PDF e XML anexos.
     *
     * Decisoes que o usuario definiu e que o codigo precisa respeitar:
     *  - vai SEMPRE para o e-mail cadastrado do inscrito. Enviar para outro
     *    destinatario nao passa por aqui: baixa a nota e manda por fora. Assim
     *    o sistema nunca vira ferramenta de disparo para endereco arbitrario.
     *  - envia UMA vez. Depois disso o botao trava (data_envio_email preenchida).
     *  - so nota AUTORIZADA. Rejeitada nao existe fiscalmente e cancelada nao
     *    vale nada para o tomador.
     */
    /**
     * Alimenta o modal de confirmacao do envio: para quem vai e qual copia
     * usar. A copia vem da ULTIMA nota deste inscrito — e o que faz o campo
     * "guardar" sem obrigar a redigitar a cada nota.
     *
     * Existe separado do envio de proposito: quem confirma precisa VER o
     * destino antes, e um confirm() do navegador nao mostra nem deixa editar.
     */
    public function dadosDoEnvio($fws_, $debug = false) {
        try {
            $this->assegurarPermissaoGestor();

            $codNota = isset($fws_['cod_nota_fiscal']) ? (int) $fws_['cod_nota_fiscal'] : 0;
            $objNota = $this->carregarNotaComPermissao($codNota, $debug);

            $para = trim((string) $objNota->getTomadorEmail());
            if ($para == '') {
                // Sem e-mail na nota, o do cadastro do inscrito vale — e o
                // mesmo criterio que o envio usa.
                $objInscrito = new Inscrito();
                $objInscrito->setCodEvento($objNota->getCodEvento());
                $objInscrito->setCodInscrito($objNota->getCodInscrito());
                if ($objInscrito->getInscritoById()) {
                    $para = trim((string) $objInscrito->getEMail());
                }
            }

            // Copia ja usada nesta nota tem precedencia sobre a do historico:
            // reabrir o modal depois de um erro nao pode perder o que foi
            // digitado.
            $copia = trim((string) $objNota->getEmailCopia());
            if ($copia == '') {
                $copia = $this->copiaAnteriorDoInscrito(
                    $objNota->getCodEvento(), $objNota->getCodInscrito(), $debug);
            }

            $this->responder(true, '', array(
                'para'   => $para,
                'copia'  => $copia,
                'numero' => (string) $objNota->getNumero()
            ));

        } catch (Exception $e) {
            $this->responder(false, $e->getMessage());
        }
    }

    public function enviarPorEmail($fws_, $debug = false) {
        try {
            $this->assegurarPermissaoGestor();

            $codNota = isset($fws_['cod_nota_fiscal']) ? $fws_['cod_nota_fiscal'] : '';
            $objNota = $this->carregarNotaComPermissao($codNota, $debug);

            if ($objNota->getStatus() != 'AUTORIZADA') {
                throw new Exception("So e possivel enviar nota autorizada. Esta nota esta como: "
                    . $objNota->getDescricaoStatus() . ".");
            }
            if ($objNota->getDataEnvioEmail()) {
                throw new Exception("Esta nota ja foi enviada em "
                    . date('d/m/Y H:i', strtotime($objNota->getDataEnvioEmail()))
                    . " para " . $objNota->getEmailEnvio() . ".");
            }

            $objInscrito = new Inscrito();
            $objInscrito->setCodInscrito($objNota->getCodInscrito());
            $objInscrito->setCodEvento($objNota->getCodEvento());
            $objInscrito->getInscritoById($debug);

            // Destino: o e-mail do TOMADOR gravado na nota, que e o que aparece
            // na previa e pode ser corrigido na emissao. So cai no cadastro do
            // inscrito quando a nota nao tem e-mail — a nota costuma sair no nome
            // da empresa/escola, e o e-mail dela nao e o do inscrito.
            $email = trim((string) $objNota->getTomadorEmail());
            if ($email == '') {
                $email = trim((string) $objInscrito->getEMail());
            }
            if ($email == '') {
                throw new Exception("A nota nao tem e-mail do tomador e o inscrito tambem nao tem e-mail cadastrado. "
                    . "Baixe o PDF e envie por fora, ou cadastre o e-mail antes.");
            }

            // TRAVA DE AMBIENTE: fora de producao a nota nao tem valor fiscal,
            // entao mandar para o destinatario real e so incomodar alguem com um
            // documento invalido. A regra mora no canal de e-mail, para valer
            // igual em todos os pontos que disparam mensagem deste modulo.
            $emailReal = $email;
            $destino = GerenciarEmailAC::destinoParaAmbienteDeTeste($email);
            if ($destino === null) {
                throw new Exception("Ambiente de homologacao: o envio real esta bloqueado. "
                    . "Defina WB_EMAIL_TESTE no env.php para testar o envio, "
                    . "ou baixe o PDF para conferir.");
            }
            $email    = $destino['email'];
            $desviado = $destino['desviado'];

            // PDF e obrigatorio (e a nota que o tomador ve). O XML e desejavel
            // mas nao pode impedir o envio: quem escritura usa o XML, quem so
            // guarda comprovante usa o PDF.
            $anexos = array();
            $nomePdf = $this->garantirArquivoLocal($objNota, 'pdf', $debug);
            $anexos[] = array(
                'caminho' => $this->getDiretorioNF() . $nomePdf,
                'nome'    => 'NotaFiscal-' . $objNota->getNumero() . '.pdf',
                'tipo'    => 'application/pdf',
            );
            try {
                $nomeXml = $this->garantirArquivoLocal($objNota, 'xml', $debug);
                $anexos[] = array(
                    'caminho' => $this->getDiretorioNF() . $nomeXml,
                    'nome'    => 'NotaFiscal-' . $objNota->getNumero() . '.xml',
                    'tipo'    => 'application/xml',
                );
            } catch (Exception $semXml) {
            }

            // Evento sem ABREV_EVENTO deixava o assunto comecando com ": " —
            // o prefixo so entra quando existe algo para prefixar.
            $abrev = defined('ABREV_EVENTO') ? trim((string) ABREV_EVENTO) : '';
            $assunto = ($abrev != '' ? $abrev . ': ' : '') . 'Nota Fiscal ' . $objNota->getNumero();

            // Texto proposital sem "inscrito" e sem "pedido": quem recebe nao
            // usa esse vocabulario e pode nem ser a pessoa inscrita (nota sai
            // no nome da empresa/escola com frequencia).
            $corpo  = "Ola " . htmlspecialchars((string) $objInscrito->getNomeInscrito(), ENT_QUOTES, 'UTF-8') . ",<br><br>";
            $corpo .= "Segue em anexo a Nota Fiscal.<br><br>";
            $corpo .= "<b>Numero:</b> " . htmlspecialchars((string) $objNota->getNumero(), ENT_QUOTES, 'UTF-8') . "<br>";
            $corpo .= "<b>Emitida para:</b> " . htmlspecialchars((string) $objNota->getTomadorNome(), ENT_QUOTES, 'UTF-8') . "<br>";
            $corpo .= "<b>Valor:</b> R$ " . number_format((float) $objNota->getValorServico(), 2, ',', '.') . "<br>";
            if ($objNota->getDataEmissao()) {
                $corpo .= "<b>Emissao:</b> " . date('d/m/Y', strtotime($objNota->getDataEmissao())) . "<br>";
            }
            if ($objNota->getUrlConsultaPublica()) {
                $url = htmlspecialchars((string) $objNota->getUrlConsultaPublica(), ENT_QUOTES, 'UTF-8');
                $corpo .= "<br>Para conferir a nota no site da prefeitura:<br>";
                $corpo .= "<a href='" . $url . "'>" . $url . "</a><br>";
            }
            if ($objNota->getAmbiente() != 'producao') {
                $corpo .= "<br><b>ATENCAO: nota de HOMOLOGACAO, sem valor fiscal.</b><br>";
            }
            if ($desviado) {
                $corpo .= "<br><i>(teste — em producao este e-mail iria para "
                        . htmlspecialchars($emailReal, ENT_QUOTES, 'UTF-8') . ")</i><br>";
            }
            $corpo .= "<br>" . ABREV_EVENTO;

            // A copia vem do modal de confirmacao, ja que e la que ela e
            // decidida. Fica gravada na nota para servir de padrao nas
            // proximas deste inscrito.
            if (isset($fws_['email_copia'])) {
                $objNota->setEmailCopia(self::normalizarListaEmails($fws_['email_copia']));
            }

            // Copia: vai como Bcc, e SO em producao. Em ambiente de teste o
            // destinatario principal ja e desviado — mandar copia para o
            // financeiro real seria furar a propria trava de ambiente.
            $copia = $desviado ? '' : trim((string) $objNota->getEmailCopia());

            $messageId = GerenciarEmailAC::enviarEmailComAnexo(
                $email, $assunto, $corpo, $anexos, ($copia !== '' ? $copia : false));

            $objNota->setEmailEnvio($email);
            $objNota->setIdEnvioEmail($messageId);
            $objNota->registrarEnvioEmail($debug);

            try {
                LogAcessoDadosSensiveis::registrar(
                    'NOTA_FISCAL',
                    $objNota->getCodNotaFiscal(),
                    'ENVIO_EMAIL',
                    'Enviada para ' . $email
                );
            } catch (Exception $ignore) {
            }

            $msg = $desviado
                ? 'TESTE: enviada para ' . $email . ' (em producao iria para ' . $emailReal . ').'
                : 'Nota enviada para ' . $email . '.';
            if ($copia !== '') {
                $msg .= ' Com cópia para ' . $copia . '.';
            }
            $this->responder(true, $msg);
        } catch (Exception $e) {
            $this->responder(false, $e->getMessage());
        }
    }

    public function baixarPdf($fws_, $debug = false) {
        $this->baixarArquivo($fws_, 'pdf', $debug);
    }

    public function baixarXml($fws_, $debug = false) {
        $this->baixarArquivo($fws_, 'xml', $debug);
    }

    /**
     * Garante o arquivo (pdf|xml) em disco: baixa da Focus na primeira vez e
     * guarda copia local. Se um dia a conta na Focus for encerrada, o documento
     * fiscal continua sendo nosso.
     *
     * @return string nome do arquivo dentro de WB_PATH_LOG_NOTA_FISCAL
     */
    private function garantirArquivoLocal(NotaFiscal $objNota, $formato, $debug = false) {
        $dir = $this->getDiretorioNF();
        $arquivoLocal = $formato == 'pdf' ? $objNota->getArquivoPdf() : $objNota->getArquivoXml();

        if ($arquivoLocal != '' && file_exists($dir . $arquivoLocal)) {
            return $arquivoLocal;
        }

        $urlRemota = $formato == 'pdf' ? $objNota->getUrlPdf() : $objNota->getUrlXml();
        if (!$urlRemota) {
            throw new Exception("Arquivo " . strtoupper($formato) . " ainda nao disponivel para esta nota.");
        }

        $client = new FocusNFeClient($objNota->getAmbiente());
        $conteudo = $client->baixarArquivo($urlRemota);

        $nomeArquivo = 'nf_' . $objNota->getCodNotaFiscal() . '_' . $objNota->getReferencia() . '.' . $formato;
        $nomeArquivo = preg_replace('/[^A-Za-z0-9_.\-]/', '', $nomeArquivo);

        if (@file_put_contents($dir . $nomeArquivo, $conteudo) === false) {
            throw new Exception("Nao foi possivel gravar o arquivo no servidor.");
        }

        if ($formato == 'pdf') {
            $objNota->setArquivoPdf($nomeArquivo);
        } else {
            $objNota->setArquivoXml($nomeArquivo);
        }
        $objNota->atualizarArquivosLocais($debug);

        return $nomeArquivo;
    }

    private function baixarArquivo($fws_, $formato, $debug = false) {
        try {
            $this->assegurarPermissaoGestor();

            $codNota = isset($fws_['cod_nota_fiscal']) ? $fws_['cod_nota_fiscal']
                     : (isset($_GET['cod_nota_fiscal']) ? $_GET['cod_nota_fiscal'] : '');

            $objNota = $this->carregarNotaComPermissao($codNota, $debug);

            $nomeArquivo = $this->garantirArquivoLocal($objNota, $formato, $debug);
            $caminhoLocal = $this->getDiretorioNF() . $nomeArquivo;

            try {
                LogAcessoDadosSensiveis::registrar(
                    'NOTA_FISCAL',
                    $objNota->getCodNotaFiscal(),
                    'DOWNLOAD_' . strtoupper($formato),
                    'ref=' . $objNota->getReferencia()
                );
            } catch (Exception $ignore) {}

            $nomeDownload = 'nota_fiscal_' . $objNota->getNumero() . '.' . $formato;
            header('Content-Type: ' . ($formato == 'pdf' ? 'application/pdf' : 'application/xml'));
            header('Content-Disposition: inline; filename="' . $nomeDownload . '"');
            header('Content-Length: ' . filesize($caminhoLocal));
            readfile($caminhoLocal);
            exit;

        } catch (Exception $e) {
            echo "Erro: " . htmlspecialchars($e->getMessage());
            exit;
        }
    }

    /**
     * Diagnostico: confirma token, ambiente e se o municipio do emissor esta
     * habilitado. Sem isto, o primeiro erro de emissao vira adivinhacao.
     */
    public function testarConexao($fws_, $debug = false) {
        try {
            $this->assegurarPermissaoGestor();

            // O emissor do evento e carregado ANTES do cliente para que o teste
            // use o ambiente DELE — testar em "producao" um gestor configurado
            // para homologacao diria que esta tudo certo no lugar errado.
            // Sem emissor cadastrado, cai no global do env.php.
            $objEmissor = new NotaFiscalEmissor();
            $temEmissor = false;
            try {
                $temEmissor = (bool) $objEmissor->getEmissorAtivoByEvento(COD_EVENTO);
            } catch (Exception $exEmissor) {
                $temEmissor = false;
            }

            $client = new FocusNFeClient($temEmissor ? $objEmissor->getAmbiente() : null);
            $teste = $client->testarConexao();

            $extra = array('ambiente' => $client->getAmbiente());

            try {
                if ($temEmissor) {
                    $mun = $client->consultarMunicipioPorCodigo($objEmissor->getCodigoMunicipio());
                    if ($mun['http'] < 400 && isset($mun['corpo']['nome_municipio'])) {
                        $extra['municipio'] = $mun['corpo']['nome_municipio'] . '/' . $mun['corpo']['sigla_uf'];
                        $extra['provedor'] = isset($mun['corpo']['provedor_nfse']) ? $mun['corpo']['provedor_nfse'] : '';
                        $extra['nfseHabilitada'] = !empty($mun['corpo']['nfse_habilitada']) ? 1 : 0;
                        $extra['requerCertificado'] = !empty($mun['corpo']['requer_certificado_nfse']) ? 1 : 0;
                    }
                    $extra['emissor'] = $objEmissor->getDescricaoAmigavel();
                } else {
                    $extra['emissor'] = 'Nenhum emissor cadastrado para o gestor deste evento.';
                }
            } catch (Exception $e) {
                $extra['emissor'] = 'Erro ao carregar emissor: ' . $e->getMessage();
            }

            $this->responder($teste['ok'], $teste['mensagem'], $extra);

        } catch (Exception $e) {
            $this->responder(false, $e->getMessage());
        }
    }

}

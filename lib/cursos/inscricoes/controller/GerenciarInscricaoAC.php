<?php

class GerenciarInscricaoAC {

    /** Comprovante exigido pelo perfil na tela de dados pessoais (tab_opcoes_perfis.comprovante_*). Gravado em tab_trabalhos. */
    const TIPO_DOC_COMPROVANTE_PERFIL = 'COMPROVANTE_PERFIL';
    const SLUG_DOC_COMPROVANTE_PERFIL = 'comprovante_perfil';
    const TAMANHO_MAX_COMPROVANTE_PERFIL = 20971520; // 20MB

    function __construct() {

    }

    /**
     * Extensoes aceitas para o comprovante do perfil (config comprovante_extensoes; vazio = pdf,jpg,jpeg,png).
     */
    public static function extensoesComprovantePerfil($objPerfil) {
        $lista = array();
        if ($objPerfil && trim($objPerfil->getComprovanteExtensoes()) != '') {
            foreach (explode(',', $objPerfil->getComprovanteExtensoes()) as $ext) {
                $ext = strtolower(trim(str_replace('.', '', $ext)));
                if ($ext != '') {
                    $lista[] = $ext;
                }
            }
        }
        if (count($lista) == 0) {
            $lista = array('pdf', 'jpg', 'jpeg', 'png');
        }
        return $lista;
    }

    /**
     * Carrega o perfil para checar se exige comprovante. Retorna null quando nao exige.
     */
    private function carregarPerfilComprovante($codPerfil, $debug = false) {
        if ($codPerfil == '') {
            return null;
        }
        $objPerfil = new Perfil();
        $objPerfil->setCodEvento(COD_EVENTO);
        $objPerfil->setCodPerfil($codPerfil);
        $objPerfil->getPerfilById($debug);
        if ($objPerfil->getComprovanteExigir() != 1) {
            return null;
        }
        return $objPerfil;
    }

    /**
     * Valida o comprovante do perfil ANTES de gravar o inscrito. Na inscricao nova o arquivo e obrigatorio;
     * na edicao so e obrigatorio se o inscrito ainda nao tem comprovante ativo (entao pode substituir).
     */
    private function validarComprovantePerfil($objPerfil, $codInscrito = null, $debug = false) {
        if (!$objPerfil) {
            return;
        }
        $titulo = trim($objPerfil->getComprovanteTitulo()) != '' ? $objPerfil->getComprovanteTitulo() : 'Comprovante';
        $veioArquivo = isset($_FILES['arquivo']) && $_FILES['arquivo']['error'] == 0 && $_FILES['arquivo']['name'] != '';
        if (!$veioArquivo) {
            if (isset($_FILES['arquivo']) && $_FILES['arquivo']['error'] == UPLOAD_ERR_INI_SIZE) {
                throw new Exception('Arquivo muito grande. Tamanho máximo permitido: 20MB.');
            }
            $jaTem = false;
            if ($codInscrito) {
                $objTrab = new Trabalho();
                $objTrab->setCodEvento(COD_EVENTO);
                $objTrab->setCodInscrito($codInscrito);
                $objTrab->setTipoDocumento(self::TIPO_DOC_COMPROVANTE_PERFIL);
                $objTrab->setSlugTipoDocumento(self::SLUG_DOC_COMPROVANTE_PERFIL);
                $jaTem = $objTrab->getByDocumentoUltimo($debug) && $objTrab->getArquivoGerado() != '' && $objTrab->getStatus() == 'ATIVADA';
            }
            if (!$jaTem) {
                throw new Exception($titulo . ': o comprovante é obrigatório para este perfil. Anexe o arquivo para concluir a inscrição.');
            }
            return;
        }
        if ($_FILES['arquivo']['size'] > self::TAMANHO_MAX_COMPROVANTE_PERFIL) {
            throw new Exception('Arquivo muito grande. Tamanho máximo permitido: 20MB.');
        }
        $extPermitidas = self::extensoesComprovantePerfil($objPerfil);
        try {
            validarTipoArquivoUpload($_FILES['arquivo']['tmp_name'], $_FILES['arquivo']['name'], $extPermitidas);
        } catch (Exception $exArq) {
            throw new Exception($titulo . ': formato de arquivo não permitido. Envie ' . strtoupper(implode(', ', $extPermitidas)) . '.');
        }
    }

    /**
     * Grava/substitui o comprovante do perfil em tab_trabalhos (tipo COMPROVANTE_PERFIL) DEPOIS da inscricao gravada.
     * Mesmo cod_trabalho, versao incrementada pelo DAO a cada substituicao. Erro aqui so loga: a inscricao ja existe.
     * Nao marca flg_enviado_avaliacao/avaliacao_final - comprovante nao entra no fluxo de avaliacao de trabalhos.
     */
    private function gravarComprovantePerfil($objInscrito, $objPerfil, $debug = false) {
        if (!$objPerfil || !isset($_FILES['arquivo']) || $_FILES['arquivo']['error'] != 0 || $_FILES['arquivo']['name'] == '') {
            return;
        }
        $arquivoGerado = '';
        try {
            $titulo = trim($objPerfil->getComprovanteTitulo()) != '' ? $objPerfil->getComprovanteTitulo() : 'Comprovante';
            $objTrabalho = new Trabalho();
            $objTrabalho->setCodEvento(COD_EVENTO);
            $objTrabalho->setCodInscrito($objInscrito->getCodInscrito());
            $objTrabalho->setTipoDocumento(self::TIPO_DOC_COMPROVANTE_PERFIL);
            $objTrabalho->setSlugTipoDocumento(self::SLUG_DOC_COMPROVANTE_PERFIL);
            $existe = $objTrabalho->getByDocumentoUltimo($debug) && $objTrabalho->getCodTrabalho() != '';
            if ($existe) {
                $versaoArquivo = (int) $objTrabalho->getVersao() + 1; // TrabalhoDAO::update incrementa versao
                $objTrabalho->setNovo(0);
            } else {
                $objTrabalho = new Trabalho();
                $objTrabalho->setCodEvento(COD_EVENTO);
                $objTrabalho->setCodInscrito($objInscrito->getCodInscrito());
                $objTrabalho->setTipoDocumento(self::TIPO_DOC_COMPROVANTE_PERFIL);
                $objTrabalho->setSlugTipoDocumento(self::SLUG_DOC_COMPROVANTE_PERFIL);
                $objTrabalho->carregarCodNovoTrabalho();
                $objTrabalho->setNovo('1');
                $objTrabalho->setFlgEnviadoAvaliacao(0);
                $versaoArquivo = 1;
            }
            $objTrabalho->setTituloTrabalho($titulo);
            $objTrabalho->setStatus('ATIVADA');

            $extensao = strtolower(pathinfo($_FILES['arquivo']['name'], PATHINFO_EXTENSION));
            $arquivoGerado = numberPad($objInscrito->getCodInscrito(), 6) . '_' . numberPad($objTrabalho->getCodTrabalho(), 6) . '_' . $versaoArquivo . '.' . $extensao;
            if (!file_exists(WB_PATH_LOG_TRABALHO)) {
                mkdir(WB_PATH_LOG_TRABALHO, 0755, true);
            }
            if (!move_uploaded_file($_FILES['arquivo']['tmp_name'], WB_PATH_LOG_TRABALHO . $arquivoGerado)) {
                executarLog('Erro ao salvar comprovante de perfil - inscrito: ' . $objInscrito->getCodInscrito(), 'inscricao-log', true);
                return;
            }
            $objTrabalho->setArquivoUpload($_FILES['arquivo']['name']);
            $objTrabalho->setArquivoGerado($arquivoGerado);
            $objTrabalho->salvar($debug);
        } catch (OKException $exOk) {
            executarLog('Comprovante de perfil salvo - inscrito: ' . $objInscrito->getCodInscrito() . ' arquivo: ' . $arquivoGerado, 'inscricao-log', true);
        } catch (Exception $exTrabalho) {
            executarLog('Erro comprovante de perfil: ' . $exTrabalho->getMessage() . ' - inscrito: ' . $objInscrito->getCodInscrito(), 'inscricao-log', true);
        }
    }

    /**
     * Cancela uma agenda (inscrição) do próprio inscrito logado no fluxo AGENDA.
     * Segurança: exige que a inscrição alvo tenha o MESMO cpf e e_mail da sessão atual
     * (impede cancelar inscrição de outra pessoa). Cancela mesmo se está paga — a área
     * pede confirmação no front. Retorna JSON {ok, message}.
     */
    public function cancelarMinhaAgenda($fws_, $debug = false){
        header('Content-Type: application/json; charset=utf-8');
        try {
            if (empty($_SESSION['_cpf'])) {
                echo json_encode(array('ok' => false, 'message' => 'Sessão inválida'));
                return;
            }
            $codInscritoAlvo = isset($fws_['cod_inscrito']) ? (int) $fws_['cod_inscrito'] : 0;
            if (!$codInscritoAlvo) {
                echo json_encode(array('ok' => false, 'message' => 'cod_inscrito ausente'));
                return;
            }
            $obj = new Inscrito();
            $obj->setCodInscrito($codInscritoAlvo);
            $obj->setCodEvento(COD_EVENTO);
            $obj->getInscritoById($debug);

            if (!$obj->getCodInscrito()) {
                echo json_encode(array('ok' => false, 'message' => 'Inscrição não encontrada'));
                return;
            }
            // Ownership: a inscrição deve pertencer ao mesmo CPF e e-mail da sessão atual
            $cpfSessao   = preg_replace('/\D/', '', $_SESSION['_cpf']);
            $cpfAlvo     = preg_replace('/\D/', '', $obj->getCpf());
            $emailSessao = mb_strtolower(trim($_SESSION['_login'] ?? ''));
            $emailAlvo   = mb_strtolower(trim($obj->getEMail()));
            if ($cpfSessao !== $cpfAlvo || ($emailSessao && $emailSessao !== $emailAlvo)) {
                echo json_encode(array('ok' => false, 'message' => 'Você não pode cancelar uma inscrição de outra pessoa'));
                return;
            }
            if ($obj->getStatus() !== 'ATIVADA') {
                echo json_encode(array('ok' => false, 'message' => 'Inscrição já está cancelada'));
                return;
            }
            // Bloqueio servidor: inscrição paga não pode ser excluída diretamente — orientar WhatsApp
            $pago = $obj->getCodVoucher()
                 || $obj->getCodIsento()
                 || $obj->getTotalPagamentos()
                 || $obj->getTotalPerfisIsentos();
            if ($pago) {
                echo json_encode(array(
                    'ok' => false,
                    'message' => 'Esta agenda está paga. Para cancelar, solicite via WhatsApp.',
                    'pago' => true,
                ));
                return;
            }
            // Cancela
            $obj->setStatus('DESATIVADA');
            $obj->salvar($debug);
            if (function_exists('executarLog')) {
                executarLog('AGENDA cancelar: cod_inscrito=' . $codInscritoAlvo . ' por cpf=' . $cpfSessao, 'agenda-cancelar', true);
            }
            echo json_encode(array('ok' => true, 'message' => 'Agenda cancelada com sucesso'));
        } catch (Exception $e) {
            echo json_encode(array('ok' => false, 'message' => $e->getMessage()));
        }
    }

    public function salvarInscricaoOficina($objInscrito, $objEv, $fws_, $debug = false){
        /*ini bloco salvar oficina*/
        $exec = false;
        $valorTotalOficina = '';
        $qtdOficina = 0;
        foreach ($fws_['cod_oficina'] as $value) {
            try {
                $objOficina = new Oficina();
                $objOficina->setCodOficina($value);
                $objOficina->getOficinaById();
                $valorTotalOficina = floatval($valorTotalOficina) + floatval($objOficina->getValorAtividade());
                $qtdOficina = $qtdOficina + 1;
                
                $objInscritoOficina = new InscritoOficina();
                $objInscritoOficina->setCodInscrito($objInscrito->getCodInscrito());
                $objInscritoOficina->setCodOficina($value);
                $objInscritoOficina->setTipoRelacao('Participante');
                $objInscritoOficina->salvar($debug);   
                
            } catch (OKException $exc) {
                $exec = true;
            }
        }
        if($exec == true){
            $objInscrito->setQtdOficina($qtdOficina);
            $objInscrito->setValorTotalOficina($valorTotalOficina);
            throw new OKException("Atividades associadas com sucesso.");
        }
        /*fim bloco salvar oficina*/
    }
    public function salvarPedidoOficina($objInscrito, $objEv, $fws_, $debug=false){

        $objInscrito->carregarOjbPerfil($debug);
        $objPerfil = $objInscrito->getObjPerfil();
        /*Localizar taxas por período*/
        if($objPerfil->getValoresPorPeriodo()){
            $condicoes['data_hoje'] = Date('Y-m-d H:i:s');  
        }else{
            $condicoes = false;  
        }
        $objPerfil->carregarTaxaAtiva($condicoes, $debug);
        $objTaxaPerfil = $objPerfil->getObjTaxa();

        /*inicio calculo valor do pedido*/
        $valorTotalPedido = $objTaxaPerfil->getTxUnicaAVistaValor();       
        if ($fws_['op_atividade_valor_variavel'] == 1){
            $valorTotalPedido = floatval($valorTotalPedido) + floatval($objInscrito->getValorTotalOficina());
        }

        /*regra de aplicação de desconto por associado*/
        if($objEv->getCodEvento()==23){
            $valorAssociacao = 65.00;
            $valorTotalAtividade = $objInscrito->getValorTotalOficina();

            $valorTotalAssociado    = floatval($valorTotalAtividade)-(floatval($valorTotalAtividade)*0.20);
            $valorTotalAssociando   = floatval($valorTotalAtividade)-(floatval($valorTotalAtividade)*0.20)+floatval($valorAssociacao);
            $valorTotalNaoAssociado = floatval($valorTotalAtividade);

            $objAssociado = new Associado();
            $objAssociado->setCpf($objInscrito->getCpf());
            $objAssociado->setCodEvento($objInscrito->getCodEvento());
            $objAssociado->getAssociadoByCpf();
            if($objAssociado->getStatus()=='ATIVADA'){
                $valorTotalPedido = $valorTotalAssociado;
            }else{
                if($fws_['que_ser_associado']=='SIM'){
                    $valorTotalPedido = $valorTotalAssociando;
                }else{
                    $valorTotalPedido = $valorTotalNaoAssociado;
                }
            }
        }
        /*regra de aplicação de desconto por associado*/
        if($objEv->getCodEvento()==28){
//            echo __line__.'-<br>';
            $totalOficinas = $objInscrito->getQtdOficina();
//            echo $totalOficinas.'<br>';
//            echo $valorTotalPedido.'<br>';
            $valorTotalProfissional  = $valorTotalPedido;
            $valorTotalAlunoPoseGrad = $valorTotalPedido;
            if($totalOficinas >= 3){
//                echo __line__.'-';
                $valorTotalProfissional  = floatval($valorTotalProfissional)-50.00;
                $valorTotalAlunoPoseGrad = floatval($valorTotalAlunoPoseGrad)*0.50;
            }
//            echo __line__.'-<br>';
//            echo $valorTotalProfissional.'<br>';
//            echo $valorTotalAlunoPoseGrad.'<br>';
            $codPerfil = $objPerfil->getCodPerfil();
            $perfisPermitidos = [175, 176, 177];
            if (in_array($codPerfil, $perfisPermitidos)) {
//                echo __line__.'-';
                $valorTotalPedido = $valorTotalAlunoPoseGrad;
            } else if ($codPerfil==178){
//                echo __line__.'-';
                $valorTotalPedido = $valorTotalProfissional;
            } else if ($codPerfil==192){
//                echo __line__.'-';
                $valorTotalPedido = $valorPerfil;
            }
//            echo $valorTotalPedido.'<br>';
//            exit();
        }
        /*fim calculo valor do pedido*/
        $objPedido = new Pedido();
        $objPedido->setInscritoId($objInscrito->getCodInscrito());
        $objPedido->setEventoId($objInscrito->getCodEvento());
        $objPedido->setTipoProdutoId(2);
        $objPedido->setQuantidade(1);
        $objPedido->setValorTotal($valorTotalPedido);
        //$objPedido->setDescricao("Cod.Insc.: ".$objInscrito->getCodInscrito(). "-".ANO_EVENTO. " - ".ABREV_EVENTO." - ".$objPerfil->getTitulo());
        $stringDescricao = $objInscrito->getCodInscrito(). "-" .ABREV_EVENTO." - ".$objPerfil->getTitulo();
        $objPedido->setDescricao(substr($stringDescricao,0,98));
        /* vencimento pela taxa do perfil, igual ao salvarPedidoEvento() */
        $dataVencimento = calcularDataVencimentoPorTaxa($objTaxaPerfil); // 'Y-m-d' ou null
        if ($dataVencimento !== null) {
            $objPedido->setDatVencimento($dataVencimento);
        }

        if(intval($objPedido->getValorTotal())<=100) {
            $objPedido->setMaxQtdParcela('1');
        }else if(intval($objPedido->getValorTotal())<=500) {
            $objPedido->setMaxQtdParcela('11');
        }else if(intval($objPedido->getValorTotal())>500) {
            $objPedido->setMaxQtdParcela('15');
        }else if(intval($objPedido->getValorTotal())>1000) {
            $objPedido->setMaxQtdParcela('21');
        }else{
            $objPedido->setMaxQtdParcela('1');
        }
        $objPedido->salvar($debug);
            
        /*fim bloco gravar pedido*/
    }
    public function salvarPedidoEvento($objInscrito, $objEv, $fws_, $debug=false){

        try {
            $objEvConf = $objEv->getEventoConfiguracao();
            if(!$objEvConf->getRecebePagamento()){
                throw new OKException("Evento não possui taxa", 1);
                
            }
            $objInscrito->carregarOjbPerfil($debug);
            $objPerfil = $objInscrito->getObjPerfil();
            /*Localizar taxas por período*/
            if($objPerfil->getValoresPorPeriodo()){
                $condicoes['data_hoje'] = Date('Y-m-d H:i:s');  
            }else{
                $condicoes = false;  
            }
            $objPerfil->carregarTaxaAtiva($condicoes, $debug);
            $objTaxa= $objPerfil->getObjTaxa();

            $objPedido = new Pedido();
            $objPedido->setInscritoId($objInscrito->getCodInscrito());
            $objPedido->setEventoId($objInscrito->getCodEvento());
            $objPedido->setTipoProdutoId(2);
            $objPedido->setQuantidade(1);
            //$objPedido->setDescricao("Cod.Insc.: ".$objInscrito->getCodInscrito(). "-".ANO_EVENTO. " - ".ABREV_EVENTO." - ".$objPerfil->getTitulo());
//            $objPedido->setDescricao("Insc: ".$objInscrito->getCodInscrito()." - ".$objEv->getProjeto()." - ".$objEv->getCodEvento()."-".$objEv->getAbrevEvento()." - ".$objPerfil->getTitulo());
            $stringDescricao = $objInscrito->getCodInscrito()." ".$objEv->getProjeto()." ".$objEv->getCodEvento()."-".$objEv->getAbrevEvento()." ".$objPerfil->getTitulo();
            $objPedido->setDescricao(substr($stringDescricao,0,98));
            
//             /*@todo tratar data de vencimento quando não houver data de vencimento definida*/
//             /*definindo data de vencimento*/
//             $maxVencimento  = $objTaxa->getMaxDataVencimento();
//             $diasVencimento = $objTaxa->getDiasVencimento();
//             /*verificar que dia é hoje*/
//             /*Calcular a data de vencimento baseado nos dias*/
//             /*verificar dentre a data máxima e a data calculada, qual é a menor e usala como data de vencimento do pedido*/
//             // Verificar a data atual
//             $hoje = date('Y-m-d');
//             // Calcular a data de vencimento baseada nos dias de vencimento a partir da data atual
//             $dataCalculada = date('Y-m-d', strtotime($hoje . ' + ' . $diasVencimento . ' days'));
//             // Verificar qual data é menor: a data máxima ou a data calculada
//             $dataVencimento = min($maxVencimento, $dataCalculada);        
// //            $objPedido->setDatVencimento($dataVencimento);
            $maxVencimento  = $objTaxa->getMaxDataVencimento();
            $diasVencimento = $objTaxa->getDiasVencimento();
            $dataVencimento = calcularDataVencimentoPorTaxa($objTaxa); // 'Y-m-d' ou null
            if ($dataVencimento !== null) {
                $objPedido->setDatVencimento($dataVencimento);
            }
            if(COD_EVENTO=='108'){
                $objPedido->setDatVencimento('2025-10-08');
            }

            $objPedido->setValorTotal($objTaxa->getTxUnicaAVistaValor());
            if(intval($objPedido->getValorTotal())<=100) {
                $objPedido->setMaxQtdParcela('1');
            }else if(intval($objPedido->getValorTotal())<=500) {
                $objPedido->setMaxQtdParcela('11');
            }else if(intval($objPedido->getValorTotal())>500) {
                $objPedido->setMaxQtdParcela('15');
            }else if(intval($objPedido->getValorTotal())>1000) {
                $objPedido->setMaxQtdParcela('21');
            }else{
                $objPedido->setMaxQtdParcela('1');
            }

            $objPedido->salvar($debug);

        } catch (OKException $exc) {

            $exec = true;
        }
        /*fim bloco gravar pedido*/
        if($objEvConf->getAtividade()!=1){
            throw new OKException("Ação executada com sucesso.");
        }        
        /*ini bloco salvar oficina*/
        $exec = false;
        if (is_array($fws_['cod_oficina']) && !empty($fws_['cod_oficina'])) {
            foreach ($fws_['cod_oficina'] as $value) {
                try {
                    $objInscritoOficina = new InscritoOficina();
                    $objInscritoOficina->setCodInscrito($objInscrito->getCodInscrito());
                    $objInscritoOficina->setCodOficina($value);
                    $objInscritoOficina->salvar($debug);   
                } catch (OKException $exc) {
                    $exec = true;
                }
            }
        }else{
            throw new OKException("Não possui atividades associadas.");
        }
        if($exec == true){

            throw new OKException("Atividades associadas com sucesso.");
        }
        /*fim bloco salvar oficina*/
    }
    public function salvarPedidoIngresso($objInscrito, $objEv, $fws_, $debug=false){
        try {
            $json = retirarBarras($fws_['ingressos']);
            $json = json_decode($json,true);
            $qtdInsc     = count($json);
            /*@todo calcular dias de estadia*/
            $qtdDias = $this->calcularDias($fws_['data_estadia_ini'], $fws_['data_estadia_fim']);

            $descricao = "Requisição: ".$objInscrito->getCodInscrito()." referente a compra de ".$qtd." ingressos";
            /*@todo calcular data de vencimento e definir valor do ingresso no cadastro da taxa*/
            $valorReferencia = '11.00';
            $valorTotal      = '';
            foreach ($json as $key) {
                $valorUnitario = $this->calcularValorUnitario($key['categoria_ingresso'],$key['idade']);
                $valorUnitario = $valorUnitario*$qtdDias;
                $valorTotal = $valorTotal+$valorUnitario;
            }
            $objPedido = new Pedido();
            $objPedido->setInscritoId($objInscrito->getCodInscrito());
            $objPedido->setEventoId($objInscrito->getCodEvento());
            $objPedido->setTipoProdutoId(1);
            $objPedido->setQuantidade($qtdInsc);
//            $objPedido->setDescricao($descricao);
            $stringDescricao = substr($descricao,0,98);
            $objPedido->setDescricao($stringDescricao);

            $objPedido->setValorTotal($valorTotal);
            $objPedido->salvar($debug);

        } catch (OKException $exc) {
            foreach ($json as $key) {
                try {

                    $valorUnitario = $this->calcularValorUnitario($key['categoria_ingresso'],$key['idade']);
                    $valorUnitario = $valorUnitario*$qtdDias;

                    $objIngresso = new Ingresso();
                    $objIngresso->setInscritoId($objInscrito->getCodInscrito());
                    $objIngresso->setEventoId($objInscrito->getCodEvento());
                    $objIngresso->setTipoProdutoId(1);
                    $objIngresso->setPedidoId($objPedido->getId());
                    $objIngresso->setQuantidade(1);
                    $objIngresso->setNome($key['nome']);
                    $objIngresso->setRg($key['rg']);
                    $objIngresso->setCategoria($key['categoria_ingresso']);
                    $objIngresso->setIdade($key['idade']);
                    $objIngresso->setValor($valorUnitario);
                    $objIngresso->setDias($qtdDias);
                    $objIngresso->salvar($debug);
                } catch (OKException $exc) {
                    $exec = true;
                }
            }
        }
    }
    public function salvarPedidoIngressoTickets($objInscrito, $objEv, $fws_, $debug=false){
        // 1. Decodificar JSON dos tickets (retirarBarras desfaz addslashes do config.php)
        $ticketsRaw = retirarBarras($fws_['tickets_json']);
        $tickets = json_decode($ticketsRaw, true);

        if (empty($tickets)) {
            throw new Exception("Dados de ingressos não informados.");
        }

        $nomeComprador = $objInscrito->getNomeInscrito();

        // 2. Montar mapa de preços por perfil (buscar taxa ativa de cada)
        $mapaPerfil = [];
        foreach ($tickets as $tk) {
            $codP = $tk['cod_perfil'];
            if (isset($mapaPerfil[$codP])) continue;
            $objPerfilTmp = new Perfil();
            $objPerfilTmp->setCodPerfil($codP);
            $objPerfilTmp->setCodEvento($objInscrito->getCodEvento());
            $objPerfilTmp->getPerfilById($debug);
            $cond = $objPerfilTmp->getValoresPorPeriodo() ? ['data_hoje'=>date('Y-m-d H:i:s')] : false;
            $objPerfilTmp->carregarTaxaAtiva($cond, $debug);
            $objTaxa = $objPerfilTmp->getObjTaxa();
            $valorUnit = $objTaxa ? $objTaxa->getTxUnicaAVistaValor() : 0;
            $mapaPerfil[$codP] = [
                'titulo' => $objPerfilTmp->getTitulo(),
                'valor'  => floatval($valorUnit),
                'taxa'   => $objTaxa
            ];
        }

        // 3. Expandir tickets em lista de ingressos individuais (1 por unidade)
        $listaIngressos = [];
        $numIngresso = 1;
        foreach ($tickets as $tk) {
            $codP = $tk['cod_perfil'];
            $qtd = intval($tk['quantidade']);
            for ($i = 0; $i < $qtd; $i++) {
                $listaIngressos[] = [
                    'cod_perfil' => $codP,
                    'nome' => ($numIngresso === 1) ? $nomeComprador : 'A identificar',
                    'categoria' => $mapaPerfil[$codP]['titulo'],
                    'valor' => $mapaPerfil[$codP]['valor']
                ];
                $numIngresso++;
            }
        }

        // 4. Calcular total
        $valorTotal = 0;
        $qtdTotal = count($listaIngressos);
        foreach ($listaIngressos as $ing) {
            $valorTotal += $ing['valor'];
        }

        // 5. Verificar se evento recebe pagamento
        $objEvConf = $objEv->getEventoConfiguracao();
        if(!$objEvConf->getRecebePagamento() || $valorTotal <= 0){
            // Evento gratuito ou sem pagamento — criar ingressos sem pedido
            foreach ($listaIngressos as $ing) {
                $_POST['nome'] = $ing['nome'];
                $_POST['rg'] = 'N/I';
                $_POST['categoria'] = $ing['categoria'];
                $_POST['valor'] = '0.01';
                $_POST['idade'] = '1';
                $_POST['dias'] = '1';
                $_POST['quantidade'] = '1';

                $objIng = new Ingresso();
                $objIng->setInscritoId($objInscrito->getCodInscrito());
                $objIng->setEventoId($objInscrito->getCodEvento());
                $objIng->setTipoProdutoId(2);
                $objIng->setNome($ing['nome']);
                $objIng->setRg('N/I');
                $objIng->setCategoria($ing['categoria']);
                $objIng->setValor(0);
                $objIng->setIdade(0);
                $objIng->setDias(1);
                $objIng->setQuantidade(1);
                try { $objIng->salvar($debug); } catch (OKException $e) {}
            }
            throw new OKException("Ingressos registrados com sucesso.");
        }

        // 6. Criar Pedido
        $stringDescricao = $objInscrito->getCodInscrito()." ".$objEv->getProjeto()." ".$objEv->getCodEvento()."-".$objEv->getAbrevEvento()." ".$qtdTotal." ingresso(s)";

        $objPedido = new Pedido();
        $objPedido->setInscritoId($objInscrito->getCodInscrito());
        $objPedido->setEventoId($objInscrito->getCodEvento());
        $objPedido->setTipoProdutoId(2);
        $objPedido->setQuantidade($qtdTotal);
        $objPedido->setDescricao(substr($stringDescricao, 0, 98));
        $objPedido->setValorTotal($valorTotal);

        // Data de vencimento — usar taxa do primeiro perfil
        $primeiraTaxa = $mapaPerfil[$tickets[0]['cod_perfil']]['taxa'];
        if ($primeiraTaxa) {
            $dataVencimento = calcularDataVencimentoPorTaxa($primeiraTaxa);
            if ($dataVencimento !== null) {
                $objPedido->setDatVencimento($dataVencimento);
            }
        }

        // Max parcelas
        if(intval($valorTotal)<=100) { $objPedido->setMaxQtdParcela('1'); }
        else if(intval($valorTotal)<=500) { $objPedido->setMaxQtdParcela('11'); }
        else if(intval($valorTotal)>500) { $objPedido->setMaxQtdParcela('15'); }
        else { $objPedido->setMaxQtdParcela('1'); }

        try {
            $objPedido->salvar($debug);
        } catch (OKException $exc) {
            // 7. Criar Ingressos (1 por unidade)
            foreach ($listaIngressos as $ing) {
                $_POST['nome'] = $ing['nome'];
                $_POST['rg'] = 'N/I';
                $_POST['categoria'] = $ing['categoria'];
                $_POST['valor'] = $ing['valor'] > 0 ? strval($ing['valor']) : '0.01';
                $_POST['idade'] = '1';
                $_POST['dias'] = '1';
                $_POST['quantidade'] = '1';

                $objIng = new Ingresso();
                $objIng->setInscritoId($objInscrito->getCodInscrito());
                $objIng->setEventoId($objInscrito->getCodEvento());
                $objIng->setTipoProdutoId(2);
                $objIng->setPedidoId($objPedido->getId());
                $objIng->setNome($ing['nome']);
                $objIng->setRg('N/I');
                $objIng->setCategoria($ing['categoria']);
                $objIng->setValor($ing['valor']);
                $objIng->setIdade(0);
                $objIng->setDias(1);
                $objIng->setQuantidade(1);
                try { $objIng->salvar($debug); } catch (OKException $e) {}
            }
        }
        $_SESSION['_ultimo_pedido_id'] = $objPedido->getId();
        throw new OKException("Pedido e ingressos registrados com sucesso.");
    }
    public function salvarInscricao($fws_, $debug = false) {
        try {
            
            $objEv = new Evento();
            $objEv->setCodEvento(COD_EVENTO);
            $objEv->getEventoById();
            $objEvConf = $objEv->getEventoConfiguracao();

            if ($fws_['e_mail'] == '') {
                throw new Exception('E-mail não informado.');
            }
            if ($fws_['estrangeiro'] != 'ON' && !validarCPF($fws_['cpf']) && !validarCNPJ($fws_['cpf'])) {
                throw new Exception('CPF/CNPJ informado é inválido.');
            }
            $objInscrito = new Inscrito();
            if($_SESSION['_cod_inscrito']){
                $objInscrito->setCodInscrito($_SESSION['_cod_inscrito']);
                $objInscrito->getInscritoById($debug);
                if($fws_['cpf']!=$objInscrito->getCpf()){
                    throw new Exception('Não é permitido alterar cpf, caso necessário entre em contato com a administração.');
                }
                if($fws_['e_mail']!=$objInscrito->getEMail()){
                    throw new Exception('Não é permitido alterar e-mail, caso necessário entre em contato com a administração.');
                }
                /*para que não permita que alguém altere seu perfil por conta própria*/
                $fws_['cod_perfil']=$objInscrito->getCodPerfil();
                if($fws_['cod_perfil']!=$objInscrito->getCodPerfil()){
                    throw new Exception('Não é permitido alterar perfil de inscrição, caso necessário entre em contato com a administração.');
                }
            }else{
                $objInscrito->setEMail($fws_['e_mail']);
                $objInscrito->setCpf(limparCpf($fws_['cpf']));
                /*permitirndo inscrição duplicada*/
                if ($objEvConf->getInscDuplicada() != 1) {
                    if($fws_['cod_perfil']!='' and COD_EVENTO==108){
                        $objInscrito->setCodPerfil($fws_['cod_perfil']);
                        /** @todo Permitir inscrição duplicada, mas não no mesmo perfil/cargo */
                        if ($objInscrito->verificarEmailEstaInscritoMesmoPerfil($debug)) {
                            $url = WB_URL_VIEW . '?content=view/inscricao/inscricao_01.php&codRetorno=' . $codRetorno . '&e_mail='.$fws_['e_mail'];
                            throw new Exception('E-mail informado: '.$fws_['e_mail'] . ' já cadastrado, é necessário efetuar login senha para o perfil/cargo selecionado.');
                        }
                    }else{
                        if ($objInscrito->verificarEmailEstaInscrito($debug)) {
                            $url = WB_URL_VIEW . '?content=view/inscricao/inscricao_01.php&codRetorno=' . $codRetorno . '&e_mail='.$fws_['e_mail'];
                            throw new Exception('E-mail informado: '.$fws_['e_mail'] . ' já cadastrado, é necessário efetuar login');
                        }
                    }
                    if($fws_['estrangeiro']!='ON'){
                        if ($objInscrito->verificarCpfEstaInscrito($debug)) {
                            $url = WB_URL_VIEW . '?content=view/inscricao/inscricao_01.php&codRetorno=' . $codRetorno;
                            throw new Exception('Cpf informado: '.$fws_['cpf'] . ' já cadastrado, é necessário efetuar login');
                        }
                    }
                }
            }
//            if($fws_['institucional']==''){
//                $objInscrito->setInstitucional('');
//            }
            $objInscrito->setAll($fws_);
            if(!$objInscrito->getPossuiBoletoGerado()){
                if($fws_['info_almoco']==''){
                    $objInscrito->setInfoAlmoco('');
                }
            }
//            if (IP_LIBERADO) {
//                $debug=true;
//            }
//            throw new OKException('Evitando Gravação para teste');
            /*Comprovante exigido pelo perfil (config do perfil) - valida antes de gravar o inscrito*/
            $objPerfilComprovante = $this->carregarPerfilComprovante($fws_['cod_perfil'], $debug);
            $this->validarComprovantePerfil($objPerfilComprovante, null, $debug);
            /*Evento 122 - Maple Bear: unidade e comprovante de vínculo obrigatórios*/
            if (COD_EVENTO == 122) {
                if (trim($fws_['instituicao']) == '') {
                    throw new Exception('A Unidade Maple Bear é obrigatória.');
                }
                if (!isset($_FILES['arquivo']) || $_FILES['arquivo']['error'] != 0) {
                    throw new Exception('O comprovante de vínculo com a Maple Bear é obrigatório.');
                }
                $extPermitidas = array('pdf', 'jpg', 'jpeg', 'png');
                $extensao = strtolower(pathinfo($_FILES['arquivo']['name'], PATHINFO_EXTENSION));
                if (!in_array($extensao, $extPermitidas)) {
                    throw new Exception('Formato de arquivo não permitido. Envie PDF, JPG ou PNG.');
                }
                if ($_FILES['arquivo']['size'] > 20971520) {
                    throw new Exception('Arquivo muito grande. Tamanho máximo permitido: 20MB.');
                }
            }
            executarLog(__line__.'-', 'inscricao-log', false);
            $objInscrito->salvar($debug);
        } catch (OKException $exc) {
            try {
                executarLog(__line__.'-', 'inscricao-log', false);
                 $objInscrito->carregarOjbPerfil();
                 $objPerfil = $objInscrito->getObjPerfil();
//                echo $objPerfil->getVerificarSelecionarAtividade();
                /*inicio bloco gravar pedido*/
                 executarLog(__line__.'-', 'inscricao-log', false);
                if(($fws_['op_atividade_valor_variavel']==1)and($objPerfil->getVerificarSelecionarAtividade()==1)){
                    try {
                        executarLog(__line__.'-', 'inscricao-log', false);
                        $this->salvarInscricaoOficina($objInscrito, $objEv, $fws_, $debug);
                    } catch (OKException $exc) {
                        executarLog(__line__.'-', 'inscricao-log', false);
                        if($objEvConf->getRecebePagamento()){
                            $this->salvarPedidoOficina($objInscrito, $objEv, $fws_, $debug);
                        }else{
                            throw new OKException("Atividades associadas com sucesso.");
                        }
                    }

                }else{
                    executarLog(__line__.'-', 'inscricao-log', false);
                    if ($objEvConf->getTipoFluxoInscricao() == 'TICKETS' && !empty($fws_['tickets_json'])) {
                        $this->salvarPedidoIngressoTickets($objInscrito, $objEv, $fws_, $debug);
                    } else {
                        $this->salvarPedidoEvento($objInscrito, $objEv, $fws_, $debug);
                    }
                }

            } catch (OKException $exc) {
                session_start();
                executarLog(__line__.'-', 'inscricao-log', false);
                if(!$_SESSION['_logado']){
                    $objEmail = new GerenciarEmailAC();
                    $objEmail->enviarEmailInscricao($objEv, $objInscrito, $objPerfil, $debug);                 
                }
                /**@todo Adicionar Configuração para liberar envio de link de pagamento por WhatsApp */
                if($objEv->getCodEvento()==93){
                    $objWhats = new GerenciarWhatsAC();
                    $objWhats->enviarLinkPagamentoByWhats($fws_, $objInscrito, $debug);
                }  

                $codRetorno = "inscricaoCadastrada";

                /*Evento 122 - Maple Bear: salvar comprovante de vínculo em tab_trabalhos*/
                if (COD_EVENTO == 122 && isset($_FILES['arquivo']) && $_FILES['arquivo']['error'] == 0) {
                    try {
                        $objTrabalho = new Trabalho();
                        $objTrabalho->setCodInscrito($objInscrito->getCodInscrito());
                        $objTrabalho->carregarCodNovoTrabalho();
                        $objTrabalho->setNovo('1');
                        $objTrabalho->setTipoDocumento('COMPROVANTE_VINCULO');
                        $objTrabalho->setTituloTrabalho('Comprovante de Vínculo - Maple Bear');
                        $objTrabalho->setStatus('ATIVADA');
                        $objTrabalho->setFlgEnviadoAvaliacao(1);
                        $objTrabalho->setAvaliacaoFinal('SUBMETIDO');

                        $extensao = '.' . strtolower(pathinfo($_FILES['arquivo']['name'], PATHINFO_EXTENSION));
                        $arquivoGerado = numberPad($objInscrito->getCodInscrito(), 6) . '_' . numberPad($objTrabalho->getCodTrabalho(), 6) . '_1' . $extensao;

                        if (!file_exists(WB_PATH_LOG_TRABALHO)) {
                            mkdir(WB_PATH_LOG_TRABALHO, 0755, true);
                        }
                        if (!move_uploaded_file($_FILES['arquivo']['tmp_name'], WB_PATH_LOG_TRABALHO . $arquivoGerado)) {
                            executarLog('Erro ao salvar comprovante vinculo Maple Bear - inscrito: ' . $objInscrito->getCodInscrito(), 'inscricao-log', true);
                        } else {
                            $objTrabalho->setArquivoUpload($_FILES['arquivo']['name']);
                            $objTrabalho->setArquivoGerado($arquivoGerado);
                            $objTrabalho->salvar($debug);
                        }
                    } catch (OKException $exOk) {
                        executarLog('Comprovante vinculo salvo - inscrito: ' . $objInscrito->getCodInscrito() . ' arquivo: ' . $arquivoGerado, 'inscricao-log', true);
                    } catch (Exception $exTrabalho) {
                        executarLog('Erro trabalho Maple Bear: ' . $exTrabalho->getMessage(), 'inscricao-log', true);
                    }
                }

                /*Comprovante exigido pelo perfil: grava em tab_trabalhos apos a inscricao*/
                $this->gravarComprovantePerfil($objInscrito, $objPerfilComprovante, $debug);

                /*Mensalidades automaticas (config mens_gerar_auto do evento). O metodo
                  engole qualquer excecao e loga — nada aqui pode quebrar a inscricao.*/
                GerenciarPedidoAC::gerarMensalidadesAutomaticas($objInscrito, $objEvConf, $debug);

                $_SESSION['_logado'] = false;
                $_SESSION = array();
    //            unset($_SESSION);

                $_SESSION['_tempo'] = time();
                $_SESSION['_login'] = $objInscrito->getEMail();
                $_SESSION['_nome_inscrito'] = $objInscrito->getNomeApelido();
                if( $objInscrito->getNomeApelido()){
                    $_SESSION['_nome_usuario'] = $objInscrito->getNomeApelido();
                }else{
                    $_SESSION['_nome_usuario'] = $objInscrito->getNomeInscrito();
                }
                $_SESSION['_cod_inscrito'] = $objInscrito->getCodInscrito();
                $_SESSION['_cpf'] = $objInscrito->getCpf();
                $_SESSION['_logado'] = 1;
                $_SESSION['_inscrito'] = 1;

                $msg = $exc->getMessage();
                $_SESSION['MSG'] = $msg;
                if ($objEvConf->getTipoFluxoInscricao() == 'TICKETS') {
                    $url = WB_URL_VIEW . "?content=view/inscricao/ticket_confirmacao.php&codRetorno=" . $codRetorno;
                    if (!empty($_SESSION['_ultimo_pedido_id'])) {
                        $url .= "&pedido_id=" . $_SESSION['_ultimo_pedido_id'];
                    }
                } else {
                    $url = WB_URL_VIEW . "?content=view/inscricao/inscricao_congresso_ok.php&codRetorno=" . $codRetorno;
                }
                executarLog(__line__.'-', 'inscricao-log', false);
            }
        } catch (Exception $exc) {
            executarLog(__line__.'-', 'inscricao-log', false);
            $msg = $exc->getMessage();
            $_SESSION['MSG'] = $msg;
            // Guardar dados preenchidos na sessão para não perder ao redirecionar
            $_SESSION['_form_dados'] = $fws_;
            if($url==''){
                if ($objEvConf->getTipoFluxoInscricao() == 'TICKETS') {
                    $url = WB_URL_VIEW . "?content=view/inscricao/ticket_dados.php&codRetorno=" . $codRetorno . "&cod_perfil=" . $fws_['cod_perfil'] . "&e_mail=" . $fws_['e_mail'];
                } else {
                    $url = WB_URL_VIEW . "?content=view/inscricao/inscricao_congresso_cadastro_dados_pessoais.php&codRetorno=" . $codRetorno . "&e_mail=".$fws_['e_mail'];
                }
            }else{
                $url = $url;
            }
        }
        echo "<br><a href='{$url}'>Redirecionando... {$msg}</a><br>";
        if (!$debug) {
            echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $url . "\">";
        }
    }
    public function atualizarDados($fws_, $debug = false) {
        // if($_SERVER['REMOTE_ADDR']=='177.82.0.28') {
        //     $debug = true;
        //     echo __LINE__.'-';
        //     exit();
        // }
        try {
            $objEv = new Evento();
            $objEv->setCodEvento(COD_EVENTO);
            $objEv->getEventoById();
            
            if ($fws_['db_banco'] == '') {
                throw new Exception('Banco não informado.');
            }
            if ($fws_['db_agencia'] == '') {
                throw new Exception('Agência não informada.');
            }
            if ($fws_['db_conta_corrente'] == '') {
                throw new Exception('Conta Corrente não informada.');
            }
            if ($fws_['db_pix'] == '') {
                throw new Exception('Pix não informado.');
            }
            session_start();
            if (empty($_SESSION['_cod_inscrito']) or (!$_SESSION['_logado'])) {
                 throw new Exception('Sessão expirada ou tentativa de acesso inválida.');
            }
            $objInscrito = new Inscrito();
            $objInscrito->setCodInscrito($_SESSION['_cod_inscrito']);
            $objInscrito->getInscritoById($debug);
            $objInscrito->setAll($fws_);
            $objInscrito->salvar($debug);
        } catch (OKException $exc) {

            // session_start();
            // if(!$_SESSION['_logado']){
            //     $objEmail = new GerenciarEmailAC();
            //     $objEmail->enviarEmailInscricao($objEv, $objInscrito, $objPerfil, $debug);
            // }
            // $codRetorno = "inscricaoCadastrada";
            $msg = $exc->getMessage();
            $_SESSION['MSG'] = $msg;
            $url = WB_URL_VIEW . "?content=view/inscricao/inscricao_congresso_ok.php&codRetorno=" . $codRetorno;
                
        } catch (Exception $exc) {
            $msg = $exc->getMessage();
            $_SESSION['MSG'] = $msg;
            if($url==''){
                $url = WB_URL_VIEW . "?content=view/inscricao/inscricao_congresso_cadastro_dados_outros.php&codRetorno=" . $codRetorno . "&e_mail=".$fws_['e_mail'];
            }else{
                $url = $url;
            }    
        }
        echo "<br><a href='{$url}'>Redirecionando... 1 {$msg}</a><br>";
        if (!$debug) {
            echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"1; URL=" . $url . "\">";
        }
    }
    public function salvarInscricaoEditar($fws_, $debug = false) {
        
        try {
            $objEv = new Evento();
            $objEv->setCodEvento(COD_EVENTO);
            $objEv->getEventoById();
            
            if ($fws_['e_mail'] == '') {
                throw new Exception('E-mail não informado.');
            }
            if ($fws_['estrangeiro'] != 'ON' && !validarCPF($fws_['cpf']) && !validarCNPJ($fws_['cpf'])) {
                throw new Exception('CPF/CNPJ informado é inválido.');
            }
            $objInscrito = new Inscrito();
            if($_SESSION['_cod_inscrito']){
                $objInscrito->setCodInscrito($_SESSION['_cod_inscrito']);
                $objInscrito->getInscritoById($debug);
                if($fws_['cpf']!=$objInscrito->getCpf()){
                    throw new Exception('Não é permitido alterar cpf, caso necessário entre em contato com a administração.');
                }
                if($fws_['e_mail']!=$objInscrito->getEMail()){
                    throw new Exception('Não é permitido alterar e-mail, caso necessário entre em contato com a administração.');
                }
                /*para que não permita que alguém altere seu perfil por conta própria*/
                $fws_['cod_perfil']=$objInscrito->getCodPerfil();
                if($fws_['cod_perfil']!=$objInscrito->getCodPerfil()){
                    throw new Exception('Não é permitido alterar perfil de inscrição, caso necessário entre em contato com a administração.');
                }
            }else{
                $objInscrito->setEMail($fws_['e_mail']);
                $objInscrito->setCpf(limparCpf($fws_['cpf']));
                if ($objInscrito->verificarEmailEstaInscrito($debug)) {
                    $url = WB_URL_VIEW . '?content=view/inscricao/inscricao_01.php&codRetorno=' . $codRetorno . '&e_mail='.$fws_['e_mail'];
                    throw new Exception('E-mail informado: '.$fws_['e_mail'] . ' já cadastrado, é necessário efetuar login');
                }
                if($fws_['estrangeiro']!='ON'){
                    if ($objInscrito->verificarCpfEstaInscrito($debug)) {
                        $url = WB_URL_VIEW . '?content=view/inscricao/inscricao_01.php&codRetorno=' . $codRetorno;
                        throw new Exception('Cpf informado: '.$fws_['cpf'] . ' já cadastrado, é necessário efetuar login');
                    }
                }
            }
//            if($fws_['institucional']==''){
//                $objInscrito->setInstitucional('');
//            }
            $objInscrito->setAll($fws_);
            if(!$objInscrito->getPossuiBoletoGerado()){
                if($fws_['info_almoco']==''){
                    $objInscrito->setInfoAlmoco('');
                }
            }
            /*Comprovante exigido pelo perfil: na edicao so obriga se ainda nao tem; se veio arquivo, valida*/
            $objPerfilComprovante = $this->carregarPerfilComprovante($fws_['cod_perfil'], $debug);
            $this->validarComprovantePerfil($objPerfilComprovante, $objInscrito->getCodInscrito(), $debug);
            $objInscrito->salvar($debug);
        } catch (OKException $exc) {

            session_start();
            if(!$_SESSION['_logado']){
                $objEmail = new GerenciarEmailAC();
                $objEmail->enviarEmailInscricao($objEv, $objInscrito, $objPerfil, $debug);
            }

            /*Evento 122 - Maple Bear: salvar/substituir comprovante de vínculo na edição*/
            if (COD_EVENTO == 122 && isset($_FILES['arquivo']) && $_FILES['arquivo']['error'] == 0) {
                try {
                    $extPermitidas = array('pdf', 'jpg', 'jpeg', 'png');
                    $extensao = strtolower(pathinfo($_FILES['arquivo']['name'], PATHINFO_EXTENSION));
                    if (!in_array($extensao, $extPermitidas)) {
                        throw new Exception('Formato de arquivo não permitido. Envie PDF, JPG ou PNG.');
                    }
                    if ($_FILES['arquivo']['size'] > 10485760) {
                        throw new Exception('Arquivo muito grande. Tamanho máximo: 10MB.');
                    }

                    $objTrabalho = new Trabalho();
                    $objTrabalho->setCodInscrito($objInscrito->getCodInscrito());
                    $objTrabalho->carregarCodNovoTrabalho();
                    $objTrabalho->setNovo('1');
                    $objTrabalho->setTipoDocumento('COMPROVANTE_VINCULO');
                    $objTrabalho->setTituloTrabalho('Comprovante de Vínculo - Maple Bear');
                    $objTrabalho->setStatus('ATIVADA');
                    $objTrabalho->setFlgEnviadoAvaliacao(1);
                    $objTrabalho->setAvaliacaoFinal('SUBMETIDO');

                    $extensao = '.' . $extensao;
                    $arquivoGerado = numberPad($objInscrito->getCodInscrito(), 6) . '_' . numberPad($objTrabalho->getCodTrabalho(), 6) . '_1' . $extensao;

                    if (!file_exists(WB_PATH_LOG_TRABALHO)) {
                        mkdir(WB_PATH_LOG_TRABALHO, 0755, true);
                    }
                    if (!move_uploaded_file($_FILES['arquivo']['tmp_name'], WB_PATH_LOG_TRABALHO . $arquivoGerado)) {
                        executarLog('Erro ao salvar comprovante vinculo edição - inscrito: ' . $objInscrito->getCodInscrito(), 'inscricao-log', true);
                    } else {
                        $objTrabalho->setArquivoUpload($_FILES['arquivo']['name']);
                        $objTrabalho->setArquivoGerado($arquivoGerado);
                        $objTrabalho->salvar($debug);
                    }
                } catch (OKException $exOk) {
                    executarLog('Comprovante vinculo atualizado - inscrito: ' . $objInscrito->getCodInscrito() . ' arquivo: ' . $arquivoGerado, 'inscricao-log', true);
                } catch (Exception $exTrabalho) {
                    executarLog('Erro trabalho edição Maple Bear: ' . $exTrabalho->getMessage(), 'inscricao-log', true);
                }
            }

            /*Comprovante exigido pelo perfil: grava/substitui em tab_trabalhos*/
            $this->gravarComprovantePerfil($objInscrito, $objPerfilComprovante, $debug);

            $codRetorno = "inscricaoCadastrada";

            $_SESSION['_logado'] = false;
            $_SESSION = array();
//            unset($_SESSION);

            $_SESSION['_tempo'] = time();
            $_SESSION['_login'] = $objInscrito->getEMail();
            $_SESSION['_nome_inscrito'] = $objInscrito->getNomeApelido();
            if( $objInscrito->getNomeApelido()){
                $_SESSION['_nome_usuario'] = $objInscrito->getNomeApelido();
            }else{
                $_SESSION['_nome_usuario'] = $objInscrito->getNomeInscrito();
            }
            $_SESSION['_cod_inscrito'] = $objInscrito->getCodInscrito();
            $_SESSION['_cpf'] = $objInscrito->getCpf();
            $_SESSION['_logado'] = 1;
            $_SESSION['_inscrito'] = 1;

            $msg = $exc->getMessage();
            $_SESSION['MSG'] = $msg;
            $url = WB_URL_VIEW . "?content=view/inscricao/inscricao_congresso_ok.php&codRetorno=" . $codRetorno;

        } catch (Exception $exc) {
            $msg = $exc->getMessage();
            $_SESSION['MSG'] = $msg;
            if($url==''){
                $url = WB_URL_VIEW . "?content=view/inscricao/inscricao_congresso_cadastro_dados_pessoais.php&codRetorno=" . $codRetorno . "&e_mail=".$fws_['e_mail'];
            }else{
                $url = $url;
            }
        }
        echo "<br><a href='{$url}'>Redirecionando... 1 {$msg}</a><br>";
        if (!$debug) {
            echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"1; URL=" . $url . "\">";
        }
    }
    public function salvarOficina($fws_, $debug = false) {
        try {
            $obj = new InscritoOficina();
            $obj->setCodInscrito($fws_['cod_inscrito']);
            $obj->setCodOficina($fws_['cod_oficina']);
            if($obj->inscritoOficinaExiste($debug)){
                throw new Exception('Você já está associado a está atividade.');
            }           
            $obj->salvar($debug);
            
        } catch (OKException $exc) {
            $msg = $exc->getMessage();
            $_SESSION['MSG'] = $msg;
            $url = WB_URL_VIEW . "?content=view/inscricao/inscricao_congresso_ok.php";
        } catch (Exception $exc) {
            $msg = $exc->getMessage();
            $_SESSION['MSG'] = $msg;
            $url = WB_URL_VIEW . "?content=view/inscricao/inscricao_congresso_ok.php";
        }
        echo "<br><a href='{$url}'>Redirecionando... {$msg}</a><br>";
        if (!$debug) {
            echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $url . "\">";
        }
    }
    public function salvarAtividadeParalela($fws_, $debug = false) {
        try {
            $obj = new InscritoOficina();
            $obj->setCodInscrito($fws_['cod_inscrito']);
            $obj->setCodOficina($fws_['cod_oficina']);
            if($obj->inscritoOficinaExiste($debug)){
                throw new Exception('Você já está associado a está atividade.');
            }
            if($obj->getInscritoOficinaExisteByDtHr($debug)){
                throw new Exception('Você já está associado a uma atividade neste mesmo horário.');
            }
            
            $obj->salvar($debug);
            
        } catch (OKException $exc) {
            $msg = $exc->getMessage();
            $_SESSION['MSG'] = $msg;
            $url = WB_URL_VIEW . "?content=view/inscricao/inscricao_congresso_ok.php";
        } catch (Exception $exc) {
            $msg = $exc->getMessage();
            $_SESSION['MSG'] = $msg;
            $url = WB_URL_VIEW . "?content=view/inscricao/inscricao_congresso_seleciona_atividade_parelela.php";
        }
        echo "<br><a href='{$url}'>Redirecionando... {$msg}</a><br>";
        if (!$debug) {
            echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $url . "\">";
        }
    }
    public function desativarTrabalho($fws_, $debug = false) {
        try {
            $codTrabalho = $fws_['cod_trabalho'];
            $justificativa = isset($fws_['justificativa']) ? trim($fws_['justificativa']) : '';

            if (empty($codTrabalho)) {
                throw new Exception('Código do trabalho não informado.');
            }

            $obj = new Trabalho();
            $obj->setCodInscrito($_SESSION['_cod_inscrito']);
            $obj->setCodTrabalho($codTrabalho);
            $obj->getTrabalhoById($debug);

            if ($obj->getStatus() != 'ATIVADA') {
                throw new Exception('Trabalho não encontrado ou já desativado.');
            }
            if ($obj->getCodInscrito() != $_SESSION['_cod_inscrito']) {
                throw new Exception('Sem permissão para desativar este trabalho.');
            }

            $objHistorico = new HistoricoAvaliacao();
            $objHistorico->setCodTrabalho($codTrabalho);
            $objHistorico->setCodInscrito($_SESSION['_cod_inscrito']);
            $objHistorico->carregarUltimaAvaliacaoByIdTrabalho($debug);
            $emTramitacao = ($objHistorico->getAvaliacao() != '');

            if ($emTramitacao && empty($justificativa)) {
                throw new Exception('Justificativa obrigatória para trabalho em tramitação.');
            }

            $obj->setStatus('DESATIVADA');
            if ($emTramitacao && $justificativa != '') {
                $obj->setJustificativaExclusao($justificativa);
            }
            $obj->salvarPresenca($debug);

        } catch (OKException $exc) {
            $ok = 1;
            $message = 'Trabalho desativado com sucesso.';
            if ($emTramitacao) {
                $message .= ' Justificativa: ' . $justificativa;
            }
            echo json_encode([
                'message' => $message,
                'statusMessage' => $ok
            ]);
            exit();
        } catch (Exception $exc) {
            echo json_encode([
                'message' => $exc->getMessage(),
                'statusMessage' => '0'
            ]);
            exit();
        }
    }

    public function salvarTrabalho($fws_, $debug = false) {
        try {
            $this->salvarTrabalhoInscricao($fws_, $debug);
            
        } catch (OKException $exc) {
            $msg = $msgErroTrabalho."<br>".$exc->getMessage();
            $_SESSION['MSG'] = $msg;
            $url = WB_URL_VIEW . "?content=view/inscricao/inscricao_congresso_ok.php";
        } catch (Exception $exc) {
            $msg = $msgErroTrabalho."<br>".$exc->getMessage();
            $_SESSION['MSG'] = $msg;
            $url = WB_URL_VIEW . "?content=view/inscricao/inscricao_trabalhos_cadastro_dados_trabalho.php";
        }
        
        echo "<br><a href='{$url}'>Redirecionando... {$msg}</a><br>";
        if (!$debug) {
            echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $url . "\">";
        }
    }
    
    public function salvarTrabalhoInscricao($fws_, $debug = false) {
        $ips_permitidos = ['200.203.142.25-', '201.66.95.230-', '177.82.0.28-','45.230.27.11-'];
        if (in_array($_SERVER['REMOTE_ADDR'], $ips_permitidos)) {
//              echo '-'.COD_EVENTO.'-<br>';
            $debug = true;
        }
        $obj = new Inscrito();
        $obj->setCodInscrito($_SESSION['_cod_inscrito']);
        
        if(!$obj->inscritoExiste($debug)){
            throw new Exception('Código de inscrito não cadastrado.');
        }
        $objEvConf = $obj->getObjEventoConfiguracao($obj->getCodEvento());

        $obj = new Trabalho();
        $obj->setCodInscrito($_SESSION['_cod_inscrito']);
        if($fws_['cod_trabalho']!=''){
            $obj->setCodTrabalho($fws_['cod_trabalho']);
            $obj->getTrabalhoById($debug);
            $obj->setVersao($obj->getVersao()+1);
        }else{
            $obj->carregarCodNovoTrabalho();
            $obj->setVersao($obj->getVersao()+1);
            $obj->setNovo('1');
        }
        
        if(in_array($obj->getCodEvento(), array(37,46,69,80,88,107))){
        //if((in_array($obj->getCodEvento(), array(37,46))) or ($objEvConf->getTrabAvaliacaoComplementar()==1)){ 
            /** @todo Avaliação Inscrito populando campos critérios*/
            // if ($objEvConf->getTrabAvaliacaoCriterioQtd()!='') {
            if (in_array($objEvConf->getCodEvento(),[107])) {
                $fws_['qtdCriterios'] = $objEvConf->getTrabAvaliacaoCriterioQtd();
            }else if (in_array($objEvConf->getCodEvento(),[80,88])) {
                $fws_['qtdCriterios'] = $objEvConf->getTrabAvaliacaoCriterioQtd();    
                $fws_['qtdCriterios'] = 13;   
            }else{
                $fws_['qtdCriterios'] = 12;
            }            
            function gerarJsonCriterios($fws_, $prefixo){
                $dados = array();
                $qtdCriterios = $fws_['qtdCriterios'];
                for ($i = 1; $i <= $qtdCriterios; $i++) {
                    $chave = $prefixo . $i;
                    if (isset($fws_[$chave])) {
                        $dados[$chave] = $fws_[$chave];
                    } else {
                        $dados[$chave] = null; // ou qualquer valor padrão que você queira usar
                    }
                }
                return json_encode($dados);
            }
            $fws_['criterios_respostas'] = gerarJsonCriterios($fws_, "criterio_");
            $fws_['criterios_pesos']     = gerarJsonCriterios($fws_, "peso_");
            $fws_['criterios_pontos']    = gerarJsonCriterios($fws_, "pontos_");
        } 

        if (TRABALHO_ANEXA_RESUMO==true) {
            echo $debug? __LINE__.'-' : '';
//        if ($objEvConf->getTrabResumoArquivo()==true) {
            try {
                $tiposPermitidosFull = array('pdf','doc', 'docx', 'rtf');
//                $stringEntrada = $objEvConf->getTrabArquivoExtensao();
                // if($objEvConf->getCodEvento()==53){
                if(in_array($objEvConf->getCodEvento(), [53, 81])){
                    $stringEntrada = "doc, docx";
                }
                if(in_array($objEvConf->getCodEvento(), [107])){
                    $tiposPermitidosFull = array('pdf','doc', 'docx', 'rtf', 'jpg', 'jpeg', 'png');
                }
//                $stringEntrada = $objEvConf->getTrabArquivoExtensao();
                $tiposSelecionados = array_map('trim', explode(',', $stringEntrada));
                if($debug){
                    print_r(tiposPermitidos);
                }
                $tiposPermitidos = array_intersect($tiposPermitidosFull, $tiposSelecionados);                
                $mimeTypesAssociados = array(
                    'pdf'  => 'application/pdf',
                    'doc'  => 'application/msword',
                    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'rtf'  => 'text/rtf'
                );
                echo $debug? __LINE__.'-' : '';
                /*verificar se arquivo foi submetido*/
                if($_FILES['arquivo']['name']){
                    echo $debug? __LINE__.'-' : '';
//                        if ($_FILES['arquivo']['size'] > 5242880){
//                           throw new Exception("Não é permitido arquivos maiores que 5MB.");
//                        }
//                        if ($_FILES['arquivo']['size'] > 36700160 ){
//                        if ($_FILES['arquivo']['size'] > 73400320 ){
                    if ($_FILES['arquivo']['size'] > 104857600 ){
                       throw new Exception("Não é permitido arquivos maiores que 90MB.");
                    }
                    if ($_FILES['arquivo']['name'] == '') {
                        throw new Exception("Arquivo não selecionado.");
                    }
                    if ($_FILES['arquivo']['tmp_name'] == '') {
                        echo $debug? __LINE__.'-' : '';
                        throw new Exception("Arquivo temporário não encontrado.");
                    }
                    echo $debug? __LINE__.'-' : '';
//                    $fileMimeType = mime_content_type($_FILES['arquivo']['tmp_name']);
//                    $extensao = array_search($fileMimeType, $mimeTypesAssociados);
                    $extensao = pathinfo($_FILES['arquivo']['name'], PATHINFO_EXTENSION);
                    if (!$extensao || !in_array($extensao, $tiposPermitidosFull)) {
                        echo $debug? __LINE__.'-' : '';
                        if($debug){
                            echo $extensao.'-';
                            //print_r($tiposPermitidos);
                        }
                        throw new Exception("Extensão de arquivo não permitida.");
                    }
                    echo $debug? __LINE__.'-' : '';
                    if(!file_exists(WB_PATH_LOG_TRABALHO)){
                        echo $debug? __LINE__.'-' : '';
                        mkdir(WB_PATH_LOG_TRABALHO);
                    }
                    $extensao = ".".$extensao;
                    $fws_['arquivo_gerado']  = numberPad($obj->getCodInscrito(), 6). "_" . numberPad($obj->getCodTrabalho(), 6)  ."_" . $obj->getVersao() . $extensao;
                    echo $debug? __LINE__.'-' : '';
                    $fws_['arquivo_destino'] = WB_PATH_LOG_TRABALHO.$fws_['arquivo_gerado'];
                    echo $debug? __LINE__.'-' : '';
                    if (!move_uploaded_file($_FILES['arquivo']['tmp_name'], $fws_['arquivo_destino'])) {
                        echo $debug? __LINE__.'-' : '';
                        throw new Exception("Ocorreu um erro durante upload do arquivo.");
                    }

                    $obj->setArquivoUpload($_FILES['arquivo']['name']);
                    $obj->setArquivoGerado($fws_['arquivo_gerado']);
                    $text = $fws_['arquivo_gerado'].'- tamanho: '.$_FILES['arquivo']['size'];
                    echo $debug? __LINE__.'-' : '';
                    executarLog($text, 'tamanho-anexo-log', true);
                }else{
                    echo $debug? __LINE__.'-' : '';
//                        $msgErroTrabalho = "Arquivo não foi enviado.";
                }
            } catch (Exception $exc) {
                echo $debug? __LINE__.'-' : '';
                $msgErroTrabalho = $exc->getMessage();
                 executarLog($msgErroTrabalho, 'erro-log', true);
            }
        }
        /** @todo anexar arquivo identificado, controle de liberação não esta funcinando */
        if ($objEvConf->getTrabResumoArquivoSemIdentificacao()==1 or $objEvConf->getCodEvento()==103) {
            echo $debug? __LINE__.'-' : '';
            executarLog(__LINE__.'-', 'arquivoIdentificado', true);
//        if ($objEvConf->getTrabResumoArquivo()==true) {
            try {
                $tiposPermitidosFull = array('pdf','doc', 'docx', 'rtf');
//                $stringEntrada = $objEvConf->getTrabArquivoExtensao();
                // if($objEvConf->getCodEvento()==53){
                if(in_array($objEvConf->getCodEvento(), [53, 81])){
                    $stringEntrada = "doc, docx";
                }
//                $stringEntrada = $objEvConf->getTrabArquivoExtensao();
                $tiposSelecionados = array_map('trim', explode(',', $stringEntrada));
                if($debug){
                    print_r(tiposPermitidos);
                }
                $tiposPermitidos = array_intersect($tiposPermitidosFull, $tiposSelecionados);                
                $mimeTypesAssociados = array(
                    'pdf'  => 'application/pdf',
                    'doc'  => 'application/msword',
                    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'rtf'  => 'text/rtf'
                );
                echo $debug? __LINE__.'-' : '';
                /*verificar se arquivo_identificado foi submetido*/
                if($_FILES['arquivo_identificado']['name']){
                    executarLog(__LINE__.'-', 'arquivoIdentificado', true);
                    echo $debug? __LINE__.'-' : '';
//                        if ($_FILES['arquivo_identificado']['size'] > 5242880){
//                           throw new Exception("Não é permitido arquivo_identificados maiores que 5MB.");
//                        }
//                        if ($_FILES['arquivo_identificado']['size'] > 36700160 ){
//                        if ($_FILES['arquivo_identificado']['size'] > 73400320 ){
                    if ($_FILES['arquivo_identificado']['size'] > 104857600 ){
                       throw new Exception("Não é permitido arquivo identificados maiores que 90MB.");
                    }
                    if ($_FILES['arquivo_identificado']['name'] == '') {
                        throw new Exception("Arquivo identificado não selecionado.");
                    }
                    if ($_FILES['arquivo_identificado']['tmp_name'] == '') {
                        echo $debug? __LINE__.'-' : '';
                        throw new Exception("Arquivo identificado temporário não encontrado.");
                    }
                    echo $debug? __LINE__.'-' : '';
//                    $fileMimeType = mime_content_type($_FILES['arquivo_identificado']['tmp_name']);
//                    $extensao = array_search($fileMimeType, $mimeTypesAssociados);
                    $extensao = pathinfo($_FILES['arquivo_identificado']['name'], PATHINFO_EXTENSION);
                    if (!$extensao || !in_array($extensao, $tiposPermitidosFull)) {
                        echo $debug? __LINE__.'-' : '';
                        if($debug){
                            echo $extensao.'-';
                            //print_r($tiposPermitidos);
                        }
                        throw new Exception("Extensão de arquivo_identificado não permitida.");
                    }
                    echo $debug? __LINE__.'-' : '';
                    if(!file_exists(WB_PATH_LOG_TRABALHO)){
                        echo $debug? __LINE__.'-' : '';
                        mkdir(WB_PATH_LOG_TRABALHO);
                    }
                    $extensao = ".".$extensao;
                    $fws_['arquivo_identificado_gerado']  = numberPad($obj->getCodInscrito(), 6). "_" . numberPad($obj->getCodTrabalho(), 6)  ."_identificado_" . $obj->getVersao() . $extensao;
                    echo $debug? __LINE__.'-' : '';
                    $fws_['arquivo_identificado_destino'] = WB_PATH_LOG_TRABALHO.$fws_['arquivo_identificado_gerado'];
                    echo $debug? __LINE__.'-' : '';
                    if (!move_uploaded_file($_FILES['arquivo_identificado']['tmp_name'], $fws_['arquivo_identificado_destino'])) {
                        echo $debug? __LINE__.'-' : '';
                        throw new Exception("Ocorreu um erro durante upload do arquivo identificado.");
                    }

                    $obj->setArquivoIdentificadoUpload($_FILES['arquivo_identificado']['name']);
                    $obj->setArquivoIdentificadoGerado($fws_['arquivo_identificado_gerado']);
                    $text = $fws_['arquivo_identificado_gerado'].'- tamanho: '.$_FILES['arquivo_identificado']['size'];
                    echo $debug? __LINE__.'-' : '';
                    executarLog($text, 'tamanho-anexo-log', true);
                }else{
                    echo $debug? __LINE__.'-' : '';
//                        $msgErroTrabalho = "arquivo_identificado não foi enviado.";
                }
            } catch (Exception $exc) {
                echo $debug? __LINE__.'-' : '';
                $msgErroTrabalho = $exc->getMessage();
                 executarLog($msgErroTrabalho, 'erro-log', true);
            }
        }
        if($obj->getStatus()==''){
           $obj->setStatus('ATIVADA'); 
        }
        $obj->setAll($fws_);
        echo $debug? __LINE__.'-' : '';
//            if($obj->getCodAreaTematica()=='-1'){
//                $obj->setCodAreaTematica('26');
//            }
        $obj->salvar($debug);
    }

    public function salvarAnexoApresentacao($fws_, $debug = false) {
        try {
            $objI = new Inscrito();
            $objI->setCodInscrito($_SESSION['_cod_inscrito']);
            
            if(!$objI->inscritoExiste($debug)){
                throw new Exception('Código de inscrito não cadastrado.');
            }
            
            $obj = new ArquivoApresentacao();
            $obj->setCodInscrito($_SESSION['_cod_inscrito']);
            if ($fws_['cod_trabalho']==''){
               throw new Exception("Código do trabalho não informado.");
            }
            $obj->setCodTrabalho($fws_['cod_trabalho']);
            $obj->carregarArquivoApresentacaoById();
            $obj->setVersao($obj->getVersao()+1);

            /*verificar se arquivo foi submetido*/
            if($_FILES['arquivo']['name']==''){
                throw new Exception("Arquivo não informado");
            }
            if ($_FILES['arquivo']['size'] > 3597152*10){
               throw new Exception("Não é permitido arquivos maiores que 30MB.");
            }
            if ($_FILES['arquivo']['name'] == '') {
                throw new Exception("Arquivo não selecionado.");
            }
            if ($_FILES['arquivo']['tmp_name'] == '') {
                throw new Exception("Arquivo temporário não encontrado.");
            }
            $extensao = substr(strrchr($_FILES['arquivo']['name'], "."), 1);
            if ($extensao != 'pdf' and $extensao != 'pptx' and $extensao != 'ppt' and $extensao != 'pptm') {
                throw new Exception("Extensão de arquivo não permitida.");
            }

            $fws_['arquivo_gerado']  = numberPad($obj->getCodInscrito(), 6). "_" . numberPad($fws_['cod_trabalho'], 6)  ."_" . $obj->getVersao() . ".".$extensao;
            if (!file_exists(WB_PATH_LOG_APRESENTACAO)){
                mkdir(WB_PATH_LOG_APRESENTACAO, 0700);
                echo "Criou diretório";
            }
            $fws_['arquivo_destino'] = WB_PATH_LOG_APRESENTACAO.$fws_['arquivo_gerado'];

            if (!move_uploaded_file($_FILES['arquivo']['tmp_name'], $fws_['arquivo_destino'])) {
                throw new Exception("Ocorreu um erro durante upload do arquivo.");
            }

            $obj->setArquivoUpload($_FILES['arquivo']['name']);
            $obj->setArquivoGerado($fws_['arquivo_gerado']);

            $obj->setAll($fws_);
            $obj->substituirArquivo($debug);
            
        } catch (OKException $exc) {
            $msg = $exc->getMessage();
            $_SESSION['MSG'] = $msg;
            $url = WB_URL_VIEW . "?content=view/inscricao/inscricao_congresso_ok.php";
        } catch (Exception $exc) {
            $msg = $exc->getMessage();
            $_SESSION['MSG'] = $msg;
            $url = WB_URL_VIEW . "?content=view/inscricao/inscricao_congresso_ok.php";
        }
        
        echo "<br><a href='{$url}'>Redirecionando... <h3>{$msg}</h3></a><br>";
        if (!$debug) {
            echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $url . "\">";
        }
    }
    public function salvarAnexoAvulso($fws_, $debug = false) {
        try {
            $objEv = new Evento();
            $objEv->setCodEvento(COD_EVENTO);
            $objEv->getEventoById();
            $objEvConf = $objEv->getEventoConfiguracao();

            $objI = new Inscrito();
            $objI->setCodInscrito($_SESSION['_cod_inscrito']);
            
            if(!$objI->inscritoExiste($debug)){
                throw new Exception('Código de inscrito não cadastrado.');
            }
            
            $obj = new ArquivoAvulso();
            $obj->setCodInscrito($_SESSION['_cod_inscrito']);
            if ($fws_['cod_trabalho']==''){
               throw new Exception("Código do trabalho não informado.");
            }
            $obj->setCodTrabalho($fws_['cod_trabalho']);
            $obj->carregarArquivoAvulsoById();
            $obj->setVersao($obj->getVersao()+1);

            $tamanho =  1024*1024*1024*70;
            /**
            * array('doc', 'docx', 'pdf', 'csv', 'txt', 'xls')
            * pode-se usar apenas a lista interna do array
            */
            $tiposPermitidos = array($objEvConf->getAvulsoArquivoExtensao());
            validarArquivo($_FILES, $tamanho, $tiposPermitidos);
            $arrayMineTypeString = createMimeTypesArray($tiposPermitidos,true);
            $extensao = $arrayMineTypeString['typesString'];
            $fws_['arquivo_gerado']  = numberPad($obj->getCodInscrito(), 6). "_" . numberPad($fws_['cod_trabalho'], 6)  ."_" . $obj->getVersao() . ".".$extensao;
            if (!file_exists(WB_PATH_LOG_AVULSO)){
                mkdir(WB_PATH_LOG_AVULSO, 0700);
                echo "Criou diretório";
            }
            $fws_['arquivo_destino'] = WB_PATH_LOG_AVULSO.$fws_['arquivo_gerado'];

            if (!move_uploaded_file($_FILES['arquivo']['tmp_name'], $fws_['arquivo_destino'])) {
                throw new Exception("Ocorreu um erro durante upload do arquivo.");
            }

            $obj->setArquivoUpload($_FILES['arquivo']['name']);
            $obj->setArquivoGerado($fws_['arquivo_gerado']);

            $obj->setAll($fws_);
            $obj->substituirArquivo($debug);
            
        } catch (OKException $exc) {
            $msg = $exc->getMessage();
            $_SESSION['MSG'] = $msg;
            $url = WB_URL_VIEW . "?content=view/inscricao/inscricao_congresso_ok.php";
        } catch (Exception $exc) {
            $msg = $exc->getMessage();
            $_SESSION['MSG'] = $msg;
            $url = WB_URL_VIEW . "?content=view/inscricao/inscricao_congresso_ok.php";
        }
        
        echo "<br><a href='{$url}'>Redirecionando... <h3>{$msg}</h3></a><br>";
        if (!$debug) {
            echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $url . "\">";
        }
    }
    public function salvarAnexoVideo($fws_, $debug = false) {
        try {
//            if($_SERVER['REMOTE_ADDR']!='189.90.70.56'){
//                print_r($_FILES);
//                exit();
//            }
            $objI = new Inscrito();
            $objI->setCodInscrito($_SESSION['_cod_inscrito']);
            
            if(!$objI->inscritoExiste($debug)){
                throw new Exception('Código de inscrito não cadastrado.');
            }
            
            $obj = new ArquivoVideo();
            $obj->setCodInscrito($_SESSION['_cod_inscrito']);
            if ($fws_['cod_trabalho']==''){
               throw new Exception("Código do trabalho não informado.");
            }
            $obj->setCodTrabalho($fws_['cod_trabalho']);
            $obj->carregarArquivoVideoById();
            $obj->setVersao($obj->getVersao()+1);

            /*verificar se arquivo foi submetido*/
            if($_FILES['arquivo']['name']==''){
                throw new Exception("Arquivo não informado");
            }
//            if ($_FILES['arquivo']['size'] > 500000000){
            if ($_FILES['arquivo']['size'] > 1200000000){
               throw new Exception("Não é permitido arquivos maiores que 500MB.");
            }
            if ($_FILES['arquivo']['name'] == '') {
                throw new Exception("Arquivo não selecionado.");
            }
            if ($_FILES['arquivo']['tmp_name'] == '') {
                throw new Exception("Arquivo temporário não encontrado.");
            }
//            $extensao = substr(strrchr($_FILES['arquivo']['name'], "."), 1);
//            if ($extensao != 'mp4') {
//                throw new Exception("Extensão de arquivo não permitida.");
//            }
            $uploadedFile = $_FILES['arquivo']['tmp_name'];
            $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($fileInfo, $uploadedFile);

            if ($mimeType != 'video/mp4') {
                throw new Exception("Tipo de arquivo não permitido.");
            }
            $fws_['arquivo_gerado']  = numberPad($obj->getCodInscrito(), 6). "_" . numberPad($fws_['cod_trabalho'], 6)  ."_" . $obj->getVersao() . ".".$extensao;
            if (!file_exists(WB_PATH_LOG_VIDEO)){
                mkdir(WB_PATH_LOG_VIDEO, 0700);
                echo "Criou diretório";
            }
            $fws_['arquivo_destino'] = WB_PATH_LOG_VIDEO.$fws_['arquivo_gerado'];

            if (!move_uploaded_file($_FILES['arquivo']['tmp_name'], $fws_['arquivo_destino'])) {
                throw new Exception("Ocorreu um erro durante upload do arquivo.");
            }

            $obj->setArquivoUpload($_FILES['arquivo']['name']);
            $obj->setArquivoGerado($fws_['arquivo_gerado']);

            $obj->setAll($fws_);
            $obj->substituirArquivo($debug);
            
        } catch (OKException $exc) {
            $msg = $exc->getMessage();
            $_SESSION['MSG'] = $msg;
            $url = WB_URL_VIEW . "?content=view/inscricao/inscricao_congresso_ok.php";
        } catch (Exception $exc) {
            $msg = $exc->getMessage();
            $_SESSION['MSG'] = $msg;
            $url = WB_URL_VIEW . "?content=view/inscricao/inscricao_congresso_ok.php";
        }
        
        echo "<br><a href='{$url}'>Redirecionando... <h3>{$msg}</h3></a><br>";
        if (!$debug) {
            echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $url . "\">";
        }
        exit();
    }
    public function salvarAnexoContrato($fws_, $debug = false) {
        try {
            $urlView = "view/inscricao/inscricao_congresso_ok.php";
            if($fws_['perspectiva']=='admin'){
                $urlView = "view/admin/inscritos-cadastro-ok.php&acao=Visualizar&cod_inscrito=".$fws_['cod_inscrito']."&cod_evento=".COD_EVENTO;
            }else{
                $fws_['cod_inscrito'] = $_SESSION['_cod_inscrito'];
            }

            $objI = new Inscrito();
            $objI->setCodInscrito($fws_['cod_inscrito']);
            
            if(!$objI->inscritoExiste($debug)){
                throw new Exception('Código de inscrito não cadastrado.');
            }
            
            $obj = new ArquivoContrato();
            $obj->setCodInscrito($fws_['cod_inscrito']);
            if ($fws_['cod_trabalho']==''){
               throw new Exception("Código do trabalho não informado.");
            }
            $obj->setCodTrabalho($fws_['cod_trabalho']);
            $obj->carregarArquivoContratoById();
            $obj->setVersao($obj->getVersao()+1);

            /*verificar se arquivo foi submetido*/
            if($_FILES['arquivo']['name']==''){
                throw new Exception("Arquivo não informado");
            }
            if ($_FILES['arquivo']['size'] > 3597152*10){
               throw new Exception("Não é permitido arquivos maiores que 30MB.");
            }
            if ($_FILES['arquivo']['name'] == '') {
                throw new Exception("Arquivo não selecionado.");
            }
            if ($_FILES['arquivo']['tmp_name'] == '') {
                throw new Exception("Arquivo temporário não encontrado.");
            }
            $extensao = substr(strrchr($_FILES['arquivo']['name'], "."), 1);
            if ($extensao != 'pdf' and $extensao != 'pptx' and $extensao != 'ppt' and $extensao != 'pptm') {
                throw new Exception("Extensão de arquivo não permitida.");
            }

            $fws_['arquivo_gerado']  = numberPad($obj->getCodInscrito(), 6). "_" . numberPad($fws_['cod_trabalho'], 6)  ."_" . $obj->getVersao() . ".".$extensao;
            if (!file_exists(WB_PATH_LOG_CONTRATO)){
                mkdir(WB_PATH_LOG_CONTRATO, 0700);
                echo "Criou diretório";
            }
            $fws_['arquivo_destino'] = WB_PATH_LOG_CONTRATO.$fws_['arquivo_gerado'];

            if (!move_uploaded_file($_FILES['arquivo']['tmp_name'], $fws_['arquivo_destino'])) {
                throw new Exception("Ocorreu um erro durante upload do arquivo.");
            }

            $obj->setArquivoUpload($_FILES['arquivo']['name']);
            $obj->setArquivoGerado($fws_['arquivo_gerado']);

            $obj->setAll($fws_);
            $obj->substituirArquivo($debug);
            
        } catch (OKException $exc) {
            $content = 0;
            $msg = $exc->getMessage();
            $_SESSION['MSG'] = $msg;
            $url = WB_URL_VIEW . "?content=".$urlView;
        } catch (Exception $exc) {
            $content = 5;
            $msg = $exc->getMessage();
            $_SESSION['MSG'] = $msg;
            $url = WB_URL_VIEW . "?content=".$urlView;
        }
        
        echo "<br><a href='{$url}'>Redirecionando... <h3>{$msg}</h3></a><br>";
        if (!$debug) {
            echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"".$content."; URL=" . $url . "\">";
        }
    }
    public function salvarAnexoBoleto($fws_, $debug = false) {
        try {
            $urlView = "view/inscricao/inscricao_congresso_ok.php";
            if($fws_['perspectiva']=='admin'){
                $urlView = "view/admin/inscritos-cadastro-ok.php&acao=Visualizar&cod_inscrito=".$fws_['cod_inscrito']."&cod_evento=".COD_EVENTO;
            }else{
                $fws_['cod_inscrito'] = $_SESSION['_cod_inscrito'];
            }

            $objI = new Inscrito();
            $objI->setCodInscrito($fws_['cod_inscrito']);
            
            if(!$objI->inscritoExiste($debug)){
                throw new Exception('Código de inscrito não cadastrado.');
            }
            
            $obj = new ArquivoBoleto();
            $obj->setCodInscrito($fws_['cod_inscrito']);
            if ($fws_['cod_trabalho']==''){
               throw new Exception("Código do trabalho não informado.");
            }
            $obj->setCodTrabalho($fws_['cod_trabalho']);
            $obj->carregarArquivoBoletoById();
            $obj->setVersao($obj->getVersao()+1);

            /*verificar se arquivo foi submetido*/
            if($_FILES['arquivo']['name']==''){
                throw new Exception("Arquivo não informado");
            }
            if ($_FILES['arquivo']['size'] > 3597152*10){
               throw new Exception("Não é permitido arquivos maiores que 30MB.");
            }
            if ($_FILES['arquivo']['name'] == '') {
                throw new Exception("Arquivo não selecionado.");
            }
            if ($_FILES['arquivo']['tmp_name'] == '') {
                throw new Exception("Arquivo temporário não encontrado.");
            }
            $extensao = substr(strrchr($_FILES['arquivo']['name'], "."), 1);
            if ($extensao != 'pdf' and $extensao != 'pptx' and $extensao != 'ppt' and $extensao != 'pptm') {
                throw new Exception("Extensão de arquivo não permitida.");
            }

            $fws_['arquivo_gerado']  = numberPad($obj->getCodInscrito(), 6). "_" . numberPad($fws_['cod_trabalho'], 6)  ."_" . $obj->getVersao() . ".".$extensao;
            if (!file_exists(WB_PATH_LOG_BOLETO)){
                mkdir(WB_PATH_LOG_BOLETO, 0700);
                echo "Criou diretório";
            }
            $fws_['arquivo_destino'] = WB_PATH_LOG_BOLETO.$fws_['arquivo_gerado'];

            if (!move_uploaded_file($_FILES['arquivo']['tmp_name'], $fws_['arquivo_destino'])) {
                throw new Exception("Ocorreu um erro durante upload do arquivo.");
            }

            $obj->setArquivoUpload($_FILES['arquivo']['name']);
            $obj->setArquivoGerado($fws_['arquivo_gerado']);

            $obj->setAll($fws_);
            $obj->substituirArquivo($debug);
            
        } catch (OKException $exc) {
            $content = 0;
            $msg = $exc->getMessage();
            $_SESSION['MSG'] = $msg;
            $url = WB_URL_VIEW . "?content=".$urlView;
        } catch (Exception $exc) {
            $content = 5;
            $msg = $exc->getMessage();
            $_SESSION['MSG'] = $msg;
            $url = WB_URL_VIEW . "?content=".$urlView;
        }
        
        echo "<br><a href='{$url}'>Redirecionando... <h3>{$msg}</h3></a><br>";
        if (!$debug) {
            echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"".$content."; URL=" . $url . "\">";
        }
    }    
    public function verificarEmailInscritoApto($fws_, $debug = false) {
        try {
            if ($fws_['e_mail'] == '') {
                $codRetorno = "verificarEmailInscrito";
                throw new Exception('E-mail não informado.');
            }
            $obj = new Inscrito();
            $obj->setEMail($fws_['e_mail']);

            if ($obj->verificarEstaInscrito($debug)) {
                $codRetorno = "efetuarLogin";
                throw new Exception($fws_['e_mail'] . ' já cadastrado, é necessário efetuar login.');
            }
            //$msg = "Redirecionando para formulário de cadastro";
            //$_SESSION['MSG'] = $msg;
            $msg = '';
            $url = WB_URL_VIEW . "?content=view/inscricao/inscricao_congresso_perfil_seleciona.php&e_mail=" . $fws_['e_mail'] . "";
        } catch (Exception $exc) {
            $msg = $exc->getMessage();
            $_SESSION['MSG'] = $msg;
            $url = WB_URL_VIEW . "?content=view/inscricao/inscricao_01.php&codRetorno=" . $codRetorno . "";
        }
        echo "<br><a href='{$url}'>Redirecionando... {$msg}</a><br>";
        if (!$debug) {
            echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $url . "\">";
        }
    }
    public function verificarCpfInscritoApto($fws_, $debug = false) {
        try {
            if ($fws_['e_mail'] == '') {
                throw new Exception('Cpf não informado.');
            }
            $obj = new InscritoApto();
            $obj->setCpf($fws_['cpf']);
            $obj->carregarInscritoAptoByCpf($debug);
            if ($obj->getCodInscrito()=='') {
                throw new Exception("O CPF informado: ".$fws_['cpf'].", está na lista de participantes pré-selecionados");
            }
            return true;
        } catch (Exception $exc) {
            $_SESSION['MSG'] = $exc->getMessage();
            return false;
        }
    }
    public function recuperarInscricao($fws_, $debug = false){
        $to = $objInscrito->getEMail();
        $subject="".ABREV_EVENTO." - Recuperação Inscrição";
        $message=$subject
                . "\r\n\r\nOla ".$objInscrito->getNomeApelido()
                . "\r\n\r\nInformações sobre sua inscrição no evento."
                . "\r\n"
                . "\r\nLogin: ".$objInscrito->getEMail()
                . "\r\nSenha: ".$objInscrito->getCodInscrito()
                . "\r\nurl:".EMAIL_CONTATO_LINK_LOGIN 
                . "\r\n"
                . "\r\n".ABREV_EVENTO." - ".NOME_EVENTO
                . "\r\nDúvidas: ".EMAIL_CONTATO
                . "\r\nData: ".Date('d/m/Y H:i')
                . "";
//        mail($to, $subject, $message, HEADER_EMAIL);
        GerenciarEmailAC::enviarEmail($to, $subject, $message);
    }
    public function testarEmail($fws_, $debug = false){

        $subject="".ABREV_EVENTO." - Teste Inscricao";
        $message=$subject
                ."\r\n\r\nOla Teste"
                ."\r\n\r\nInformações sobre sua inscrição no evento."
                ."\r\n\r\nLogin: nononono@nononono.com.br"
                ."\r\nSenha: nononono"
                ."\r\nurl: ".EMAIL_CONTATO_LINK_LOGIN 
                ."\r\n\r\n".ABREV_EVENTO." - ".NOME_EVENTO
                ."\r\nDúvidas: ".EMAIL_CONTATO
                ."\r\nData: ".Date('d/m/Y H:i')
                ."";
        $to = "zakacc@gmail.com";
        echo $fws_['headers']."<br>";
//        $envio = mail($to, $subject, $message, HEADER_EMAIL);
        $envio = GerenciarEmailAC::enviarEmail($to, $subject, $message);
        
        if($envio){
            echo "email enviado com sucesso:<br>".$message;
        }else{
            echo "email não enviado";
        }
    }
    
    public function carregarOficina($fws_, $debug = false){
        $objPerfil = new Perfil();
        $objPerfil->setCodPerfil($fws_['cod_erfil']);
        $objPerfil->carregarPerfilById();
    }
    
    public static function gerarLinksCertificados(Inscrito $objInscrito, $fws_, $debug = false){
        if($_SERVER['REMOTE_ADDR']=='177.82.0.288'){
            echo '-'.$objInscrito->getCodVoucher().'-'.$objInscrito->getCodIsento().'-'.$objInscrito->getTotalPagamentos().'-'.$objInscrito->getTotalPerfisIsentos().'-';
            dd($objInscrito);
        }
        $msgPagamento = "";
        if(!$objInscrito->getCodVoucher() and !$objInscrito->getCodIsento() and !$objInscrito->getTotalPagamentos() and !$objInscrito->getTotalPerfisIsentos()){
            $msgPagamento = "<br>Não encontramos registro de pagamento ou isenção para sua inscrição";
        }   
        if(!$objInscrito->getCodVoucher() and !$objInscrito->getCodIsento() and !$objInscrito->getTotalPagamentos() and !$objInscrito->getTotalPerfisIsentos()){
            throw new Exception("Certificados são liberados neste local após o evento.<br>Caso necessário, entre em contato com a administração do evento (".EMAIL_CONTATO.").".$msgPagamento);
        }
        if(in_array($objInscrito->getCodEvento(), array(2,3))){
            $objInscrito->setFlgPresencaInscrito(1);
        }
//        dd($objInscrito);
        //$msgCertificadoNaoLiberado = "Emissão de certificado bloqueada.<br>Presença não foi confirmada.";
        $msgCertificadoNaoLiberado = "Não há certificados liberados até o momento.";
        if(!$objInscrito->getFlgPresencaInscrito()){
            if(in_array($objInscrito->getCodEvento(), array(61))){
            }else{
                throw new Exception($msgCertificadoNaoLiberado);
            }
        }
//        if((in_array($objInscrito->getCodEvento(), array(21)))and($_SERVER['REMOTE_ADDR']!='177.82.0.28')){
//            throw new Exception("Aguardando liberação");
//        }
        if(!$_SESSION['_logadoComo']){
            #@todo liberar certificado sem avaliacao
            if ($objInscrito->getAvaliouEvento() !='1' and $fws_['op_avaliar_ev_certificado']==true) {
                throw new Exception("<a href='".WB_URL_AVALIAR_EVENTO."/'>Clique aqui para Avaliar o Evento e Baixar Certificados</a>");
            }
        }
        try {
    //        if($_SERVER['REMOTE_ADDR']=='177.82.0.28'){
    //            $debug = true;
    //        }
            $liberarCertificadoCongressista = false;
            $objInscrito->carregarListaPerfil($condicoes = false, $debug);
            if($objInscrito->getListaPerfil()){
                echo "<ul>";
                $qtdAtividadePermite = 0;
                foreach ($objInscrito->getListaPerfil() as $objPerfis) {
                    if($objPerfis->getLiberarCertificado()){
                        echo "<li><a href='javascript:void(0);' onclick=\"gerarCertificadoPerfil('".removerAcentos($objPerfis->getTitulo())."','".$objPerfis->getCodPerfil()."');\">Certificado de ".$objPerfis->getTitulo()."</a><br></li>";
                        if($objPerfis->getQtdAtividadePermite()>=$qtdAtividadePermite){
                            $qtdAtividadePermite = $objPerfis->getQtdAtividadePermite();
                        }
                        if(!$objPerfis->getLiberarCertificadoCongressista()){
                            $liberarCertificadoCongressista = false;
                        }
                    }
                }
                echo "</ul>";
            }
        } catch (Exception $exc) {
            //echo $exc->getMessage();
        }

        echo "<ul>";
        $objEvConf = EventoConfiguracao::getInstancePopulado(COD_EVENTO);
        $liberarCertificadoCongressista = $objEvConf->getCertificadoCongressista()==1? true : false;
        if(in_array($objInscrito->getCodEvento(), array(2,6,7,21,61,57,70))){
           $liberarCertificadoCongressista = true; 
           $liberarCertificadoAtividade = true; 
        }
        /**@todo liberando certificado somente de trabalho */
        if(in_array($objInscrito->getCodEvento(), array(61))){
            if(!$objInscrito->getFlgPresencaInscrito()){
                $liberarCertificadoCongressista = false; 
                $liberarCertificadoAtividade = false;
            }
        }
        // Bloqueio POR PESSOA do certificado geral de participacao (opt-out).
        // NULL/0 = recebe (comportamento antigo); 1 = nao mostra o link do geral.
        if($objInscrito->getFlgBloquearCertificadoGeral()){
            $liberarCertificadoCongressista = false;
        }
        if($liberarCertificadoCongressista){
            echo "<li><a href='javascript:void(0);' onclick=\"gerarCertificado('congressista');\">Certificado de Participação</a></li>";
        }
        if($objInscrito->getTotalOficinas()>=1){
            //echo "<li>Certificado de Participação em Oficina - previsão de liberação: 23/08/2017 16h</li>";
    //        echo "<li><a href='javascript:void(0);' onclick=\"javascript:gerarCertificado('oficina');\">Certificado de Participação em ".DESC_ATIVIDADE."</a></li>";
            $tituloLoop = "<li>Certificado de Participação em Atividades:<br>";
            $objInscrito->carregarListaOficinas();
            if($objInscrito->getListaOficinas()){
                $i=0;
                foreach ($objInscrito->getListaOficinas() as $objOf) {
                    if ($objOf->getStatus() == 'ATIVADA' and $objOf->getLiberarCertificado() == 1){ 
                        if($tituloLoop!=''){
//                            echo $tituloLoop;
                        }
                        $tituloLoop = '';
                        if($objOf->getFlgPresenca()==1 or $liberarCertificadoAtividade==1){
                            echo "<a href='javascript:void(0);' onclick=\"gerarCertificadoAtividade('participou_atividade','".$objOf->getCodInscrito()."','".$objOf->getCodOficina()."');\">".$objOf->getCodOficina()." - ".$objOf->getTituloCertificado()."</a><br>";
                        }
                        else{   
                            echo $msgCertificadoNaoLiberado;
                            // echo "(Aguarando processamento) - ".$objOf->getCodOficina()." - ".$objOf->getTituloCertificado()."<br>";
                        }
                        $i = $i+1;
                    }
                }
            }
            echo "</li>";
        }
        if($objInscrito->getTotalTrabalhos()>=1){
            if ($objInscrito->getCodEvento()!=78){
                echo "<li>Certificado de Apresentação do(s) Trabalho(s):<br>";
            }
//            if($_SERVER['REMOTE_ADDR']=='177.82.0.28'){
//                $objInscrito->carregarListaTrabalhosByIdInscrito(1);
//            }else{
                $objInscrito->carregarListaTrabalhosByIdInscrito();
//            }
            if($objInscrito->getListaTrabalhos()){
                foreach ($objInscrito->getListaTrabalhos() as $objTrabalho) {
                    if((($objInscrito->getTotalPerfisIsentos()>=1) OR ($objInscrito->getImpressaoCracha()==1) OR ($objTrabalho->getFlgPresenca()==1 or $objTrabalho->getCodEvento()==70)) and ($objTrabalho->getAvaliacaoFinal()=='APROVADO')){
                        echo "<a href='javascript:void(0);' onclick=\"gerarCertificadoTrabalho('apresentador_trabalho','".$objTrabalho->getCodTrabalho()."');\">".$objTrabalho->getTituloTrabalho()."</a><br>";
                    }
                }
            }
            echo "</li>";
        }
        echo "</ul>";
    }
    
    public static function gerarLinksCertificadosEventos($objInscrito, $fws_, $debug = false){
        //echo '-'.$objInscrito->getCodVoucher().'-'.$objInscrito->getCodIsento().'-'.$objInscrito->getTotalPagamentos().'-'.$objInscrito->getTotalPerfisIsentos().'-';
        //dd($objInscrito);
        if(!$objInscrito->getCodVoucher() and !$objInscrito->getCodIsento() and !$objInscrito->getTotalPagamentos() and !$objInscrito->getTotalPerfisIsentos()){
            throw new Exception("Certificados são liberados neste local após o evento.<br>Caso necessário, entre em contato com a administração do evento (".EMAIL_CONTATO.").");
        }
        if(in_array($objInscrito->getCodEvento(), array(2,3))){
            $objInscrito->setFlgPresencaInscrito(1);
        }
//        dd($objInscrito);
        //$msgCertificadoNaoLiberado = "Emissão de certificado bloqueada.<br>Presença não foi confirmada.";
        $msgCertificadoNaoLiberado = "Não há certificados liberados até o momento.";
        if(!$objInscrito->getFlgPresencaInscrito()){
            throw new Exception($msgCertificadoNaoLiberado);
        }
        if(!$_SESSION['_logadoComo']){
            #@todo liberar certificado sem avaliacao
            if ($objInscrito->getAvaliouEvento() !='1' and $fws_['op_avaliar_ev_certificado']==true) {
                throw new Exception("<a href='".WB_URL_AVALIAR_EVENTO."'>Clique aqui para Avaliar o Evento e Baixar Certificados</a>");
            }
        }
        try {
    //        if($_SERVER['REMOTE_ADDR']=='189.90.79.10'){
    //            $debug = true;
    //        }
            $liberarCertificadoCongressista = false;
            $objInscrito->carregarListaPerfil($condicoes = false, $debug);
            if($objInscrito->getListaPerfil()){
                echo "<ul>";
                $qtdAtividadePermite = 0;
                foreach ($objInscrito->getListaPerfil() as $objPerfis) {
                    if($objPerfis->getLiberarCertificado()){
                        echo "<li><a href='javascript:void(0);' onclick=\"gerarCertificado('".removerAcentos($objPerfis->getTitulo())."',".$objInscrito->getCodEvento().",".$objInscrito->getCodInscrito().");\">Certificado de ".$objPerfis->getTitulo()."</a><br></li>";
                        if($objPerfis->getQtdAtividadePermite()>=$qtdAtividadePermite){
                            $qtdAtividadePermite = $objPerfis->getQtdAtividadePermite();
                        }
                        if(!$objPerfis->getLiberarCertificadoCongressista()){
                            $liberarCertificadoCongressista = false;
                        }
                    }
                }
                echo "</ul>";
            }
        } catch (Exception $exc) {
            //echo $exc->getMessage();
        }

        echo "<ul>";
        if(in_array($objInscrito->getCodEvento(), array(2,3,6,7))){
           $liberarCertificadoCongressista = true; 
           $liberarCertificadoAtividade = true; 
        }
        // Bloqueio POR PESSOA do certificado geral de participacao (opt-out).
        // NULL/0 = recebe (comportamento antigo); 1 = nao mostra o link do geral.
        if($objInscrito->getFlgBloquearCertificadoGeral()){
            $liberarCertificadoCongressista = false;
        }
        if($liberarCertificadoCongressista){
            echo "<li><a href='javascript:void(0);' onclick=\"gerarCertificado('congressista',".$objInscrito->getCodEvento().",".$objInscrito->getCodInscrito().");\">Certificado de Congressita do Evento</a></li>";
        }
        if($objInscrito->getTotalOficinas()>=1){
            //echo "<li>Certificado de Participação em Oficina - previsão de liberação: 23/08/2017 16h</li>";
    //        echo "<li><a href='javascript:void(0);' onclick=\"javascript:gerarCertificado('oficina');\">Certificado de Participação em ".DESC_ATIVIDADE."</a></li>";
            $tituloLoop = "<li>Certificado de Participação em Atividades:<br>";
            $objInscrito->carregarListaOficinas();
            if($objInscrito->getListaOficinas()){
                $i=0;
                foreach ($objInscrito->getListaOficinas() as $objOf) {
                    if ($objOf->getStatus() == 'ATIVADA' and $objOf->getLiberarCertificado() == 1){ 
                        if($tituloLoop!=''){
//                            echo $tituloLoop;
                        }
                        $tituloLoop = '';
                        if($objOf->getFlgPresenca()==1 or $liberarCertificadoAtividade==1){
                            echo "<a href='javascript:void(0);' onclick=\"gerarCertificadoAtividade('participou_atividade',".$objInscrito->getCodEvento().",".$objInscrito->getCodInscrito().",'".$objOf->getCodOficina()."');\">".$objOf->getCodOficina()." - ".$objOf->getTituloCertificado()."</a><br>";
                        }
                        else{
                            echo "(Aguarando processamento) - ".$objOf->getCodOficina()." - ".$objOf->getTituloCertificado()."<br>";
                        }
                        $i = $i+1;
                    }
                }
            }
            echo "</li>";
        }
        if($objInscrito->getTotalTrabalhos()>=1){
            if ($objInscrito->getCodEvento()!=78){
                echo "<li>Certificado de Apresentação do(s) Trabalho(s):<br>";
            }
            $objInscrito->carregarListaTrabalhosByIdInscrito();
            if($objInscrito->getListaTrabalhos()){
                foreach ($objInscrito->getListaTrabalhos() as $objTrabalho) {
                    if((($objInscrito->getTotalPerfisIsentos()>=1) OR ($objInscrito->getImpressaoCracha()==1) OR ($objTrabalho->getFlgPresenca()==1)) and ($objTrabalho->getAvaliacaoFinal()=='APROVADO')){
                        echo "<a href='javascript:void(0);' onclick=\"gerarCertificadoTrabalho('apresentador_trabalho',".$objInscrito->getCodEvento().",".$objInscrito->getCodInscrito().",'".$objTrabalho->getCodTrabalho()."');\">".$objTrabalho->getTituloTrabalho()."</a><br>";
                    }
                }
            }
            echo "</li>";
        }
        echo "</ul>";
    }
    
    public static function gerarLinksEmailCertificados($objInscrito, $fws_, $debug = false){
//        dd($objInscrito);
//        echo __LINE__.'-';
//        if(!$objInscrito->getCodVoucher() and !$objInscrito->getCodIsento() and !$objInscrito->getTotalPagamentos() and !$objInscrito->getTotalPerfisIsentos()){
//            echo __LINE__.'-';
//            throw new Exception("Certificados são liberados neste local após o evento.<br>Caso necessário, entre em contato com a administração do evento ");
//        }
//        echo __LINE__.'-';
        $msgCertificadoNaoLiberado = "Emissão de certificado bloqueada.<br>Aguardando confirmação de presença.";
        if(!$objInscrito->getFlgPresencaInscrito()){
//            echo __LINE__.'-';
            throw new Exception($msgCertificadoNaoLiberado);
        }
 
        try {
    //        if($_SERVER['REMOTE_ADDR']=='189.90.79.10'){
    //            $debug = true;
    //        }
            $liberarCertificadoCongressista = false;
            if($objInscrito->getCodEvento()==21){
                $liberarCertificadoCongressista = true;
            }
            $objInscrito->carregarListaPerfil($condicoes = false, $debug);
            if($objInscrito->getListaPerfil()){
                echo "<ul>";
                $qtdAtividadePermite = 0;
                foreach ($objInscrito->getListaPerfil() as $objPerfis) {
                    if($objPerfis->getLiberarCertificado()){
                        echo "<li><a href='".WB_URL_VALIDAR_CERTIFICADO."/?id=".removerEspacos(removerAcentos(minusculo($objPerfis->getTitulo())))."-".$objInscrito->getCodInscrito()
                                ."' target='certificado'>Certificado de ".$objPerfis->getTitulo()."</a></li>";
                        if($objPerfis->getQtdAtividadePermite()>=$qtdAtividadePermite){
                            $qtdAtividadePermite = $objPerfis->getQtdAtividadePermite();
                        }
                        if(!$objPerfis->getLiberarCertificadoCongressista()){
                            $liberarCertificadoCongressista = false;
                        }
                    }
                }
                echo "</ul>";
            }
        } catch (Exception $exc) {
//            echo __LINE__.'-';
            //echo $exc->getMessage();
        }
//        echo __LINE__.'-';
        echo "<ul>";
        if($liberarCertificadoCongressista){
//            echo __LINE__.'-';
            echo "<li><a href='".WB_URL_VALIDAR_CERTIFICADO."/?id=congressista-".$objInscrito->getCodInscrito()."'>"
                    . "Certificado de Congressita</a></li>";
        }
        if($objInscrito->getTotalOficinas()>=1){
//            echo __LINE__.'-';
            $tituloLoop = "<li>Certificado de Participação em Atividades:<br>";
            $objInscrito->carregarListaOficinas();
            if($objInscrito->getListaOficinas()){
                $i=0;
                foreach ($objInscrito->getListaOficinas() as $objOf) {
                    if ($objOf->getStatus() == 'ATIVADA' and $objOf->getLiberarCertificado() == 1){ 
                        if($tituloLoop!=''){
                            echo $tituloLoop;
                        }
                        $tituloLoop = '';
                        if($objOf->getFlgPresenca()==1){
                            echo "<a href='".WB_URL_VALIDAR_CERTIFICADO."/?id=participou_atividade-".$objOf->getCodInscrito()."-".$objOf->getCodOficina()."' target='certificado'>".$objOf->getTituloCertificado()."</a><br>\r\n ";
                        }
                        $i = $i+1;
                    }
                }
            }
            echo "</li>";
        }
        if($objInscrito->getTotalTrabalhos()>=1){
//            echo __LINE__.'-';
            if ($objInscrito->getCodEvento()!=78){
                echo "<li>Certificado de Apresentação do(s) Trabalho(s):<br>";
            }
            $objInscrito->carregarListaTrabalhosByIdInscrito();
            if($objInscrito->getListaTrabalhos()){
                foreach ($objInscrito->getListaTrabalhos() as $objTrabalho) {
                    if((($objInscrito->getTotalPerfisIsentos()>=1) OR ($objInscrito->getImpressaoCracha()==1) OR ($objTrabalho->getFlgPresenca()==1)) and ($objTrabalho->getAvaliacaoFinal()=='APROVADO')){
                        echo "<a href='".WB_URL_VALIDAR_CERTIFICADO."/?id=apresentador_trabalho-".$objTrabalho->getCodInscrito()."-".$objTrabalho->getCodTrabalho()."'>".$objTrabalho->getTituloTrabalho()."</a><br>";
                    }
                }
            }
            echo "</li>";
        }
        echo "</ul>";
    }
}

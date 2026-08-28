<?php

class Inscrito extends InscritoVO {

    private $objDAO;

    function __construct() {
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new InscritoDAO();
    }

    // ========================================================================
    // Recuperacao/definicao de senha do inscrito (Parte 1).
    // ========================================================================

    /**
     * Gera e grava o token de definicao de senha; devolve ['email','token'] ou
     * false (e-mail nao encontrado OU ja existe token ativo -- limite de reenvio).
     * A resposta ao usuario e sempre neutra: quem chama nao revela o motivo.
     */
    public function solicitarDefinicaoSenha($email, $debug=false) {
        $email = trim($email);
        if ($email === '') return false;
        if ($this->objDAO->tokenRecuperacaoAtivoInscrito($email, $debug)) return false;
        $dados = $this->objDAO->getEmailCpfParaRecuperacao($email, $debug);
        if (!$dados) return false;
        $token  = SenhaHelper::gerarToken();
        $expira = date('Y-m-d H:i:s', time() + 3600); // 1 hora
        $this->objDAO->salvarTokenRecuperacaoInscrito($dados['e_mail'], $dados['cpf'], $token, $expira, $debug);
        return array('email' => $dados['e_mail'], 'token' => $token);
    }

    /** Token de definicao de senha ainda valido? */
    public function validarTokenDefinicaoSenha($token, $debug=false) {
        return $this->objDAO->getByTokenRecuperacaoInscrito(trim($token), $debug) !== false;
    }

    /**
     * Dados do token valido (e_mail, cpf, expira, minutos_restantes) ou false.
     * A tela de definir senha usa para dizer DE QUEM e a senha e quanto tempo resta;
     * o e-mail so sai daqui mascarado, ver GerenciarLoginAC::mascararEmail.
     */
    public function dadosTokenDefinicaoSenha($token, $debug=false) {
        return $this->objDAO->getByTokenRecuperacaoInscrito(trim($token), $debug);
    }

    /**
     * Define a senha (bcrypt) em TODAS as contas da pessoa (mesmo e-mail + cpf)
     * e invalida o token. Lanca Exception se o token nao vale.
     */
    public function definirSenhaPorToken($token, $senhaNova, $debug=false) {
        $token = trim($token);
        $dados = $this->objDAO->getByTokenRecuperacaoInscrito($token, $debug);
        if (!$dados) {
            throw new Exception('Link invalido ou expirado.');
        }
        $hash = SenhaHelper::hash($senhaNova);
        $this->objDAO->definirSenhaHashInscrito($dados['e_mail'], $dados['cpf'], $hash, $debug);
        $this->objDAO->marcarTokenRecuperacaoInscritoUsado($token, $debug);
        return true;
    }

    /**
     * Login por senha real (bcrypt). Se casar, seta a identidade neste objeto e
     * retorna true; senao false. Nao interfere no login legado (numero).
     */
    public function autenticarPorHash($email, $senha, $quatroCpf, $codEvento=null, $debug=false) {
        if (trim($email) === '' || (string)$senha === '') return false;
        $cands = $this->objDAO->carregarCandidatosBcrypt($email, $quatroCpf, $codEvento, $debug);
        foreach ($cands as $c) {
            if (SenhaHelper::isBcrypt($c['senha_criptografada'])
                && SenhaHelper::verify($senha, $c['senha_criptografada'])) {
                $this->setCodInscrito($c['cod_inscrito']);
                $this->setCodEvento($c['cod_evento']);
                $this->setNomeInscrito($c['nome_inscrito']);
                $this->setNomeApelido($c['nome_apelido']);
                $this->setEMail($c['e_mail']);
                $this->setCpf($c['cpf']);
                return true;
            }
        }
        return false;
    }

    public function getCadastradosPorMesPerfil($debug=false) {
        return $this->objDAO->carregarCadastradosPorMesPerfil($this->getCodEvento(), $debug);
    }
    public function carregarCondicoes($fws_, $debug = false) {
        $condicoes = array();
        $condicoes['estado'] = $fws_['estado'];
        $condicoes['cidade'] = $fws_['cidade'];
        $condicoes['cod_area_tematica'] = $fws_['cod_area_tematica'];
        $condicoes['cod_macrorregiao'] = $fws_['cod_macrorregiao'];
        $condicoes['cod_oficina']   = $fws_['cod_oficina'];

        $condicoes['ordenar']       = $fws_['ordenar'];
        $condicoes['pagamentos']    = $fws_['pagamentos'];
        $condicoes['pagamentosQtdAte']    = $fws_['pagamentosQtdAte'];
        $condicoes['pagamentosQtdMaior']    = $fws_['pagamentosQtdMaior'];
        $condicoes['pagamentosQtdMenor']    = $fws_['pagamentosQtdMenor'];
        $condicoes['pedidos']       = $fws_['pedidos'];
        $condicoes['pedidosQtdAte']       = $fws_['pedidosQtdAte'];
        $condicoes['pedidosQtdMaior']       = $fws_['pedidosQtdMaior'];
        $condicoes['pedidosQtdMenor']       = $fws_['pedidosQtdMenor'];
        $condicoes['xml_pagamentos']    = $fws_['xml_pagamentos'];
        $condicoes['xml_pagamentos_carregados']    = $fws_['xml_pagamentos_carregados'];
        $condicoes['forma_pagamento'] = $fws_['forma_pagamento'];
        $condicoes['trabalhos']     = $fws_['trabalhos'];
        $condicoes['flg_assinado'] = $fws_['flg_assinado'];
        $condicoes['contrato_anexo']     = $fws_['contrato_anexo'];
        $condicoes['rpa_anexo']     = $fws_['rpa_anexo'];
        $condicoes['comp_transf_anexo']     = $fws_['comp_transf_anexo'];
        $condicoes['termo_desist_anexo']     = $fws_['termo_desist_anexo'];
        $condicoes['boletos_anexo']     = $fws_['boleto_anexo'];
        $condicoes['oficinas']      = $fws_['oficinas'];
        $condicoes['avaliacao']     = $fws_['avaliacao'];
        $condicoes['situacao']      = $fws_['situacao'];
        $condicoes['status_processo'] = $fws_['status_processo'];
        $condicoes['cod_perfil']    = $fws_['cod_perfil'];
        $condicoes['tipo_inscricao']    = $fws_['tipo_inscricao'];
        $condicoes['tipo_perfil']   = $fws_['tipo_perfil'];
        $condicoes['tipo_pedido']   = $fws_['tipo_pedido'];

        $condicoes['data_pagamento_ini']    = $fws_['data_pagamento_ini'];
        $condicoes['data_pagamento_fim']    = $fws_['data_pagamento_fim'];
        $condicoes['data_pagamento_incl_ini']    = $fws_['data_pagamento_incl_ini'];
        $condicoes['data_pagamento_incl_fim']    = $fws_['data_pagamento_incl_fim'];
        $condicoes['data_inscricao_ini']    = $fws_['data_inscricao_ini'];
        $condicoes['data_inscricao_fim']    = $fws_['data_inscricao_fim'];
        $condicoes['data_vencimento_ini']    = $fws_['data_vencimento_ini'];
        $condicoes['data_vencimento_fim']    = $fws_['data_vencimento_fim'];
        $condicoes['almoco']        = $fws_['almoco'];
        $condicoes['perspectiva']   = $fws_['perspectiva'];
        $condicoes['tela']              = $fws_['tela'];
        $condicoes['patrocinador_voucher'] = $fws_['patrocinador_voucher'];
        $condicoes['presenca_inscrito']    = $fws_['presenca_inscrito'];
        $condicoes['solicitaram_recurso']  = $fws_['solicitaram_recurso'];
        $condicoes['avaliacao_inscrito']  = $fws_['avaliacao_inscrito'];
        $condicoes['registro_atividade_status']  = $fws_['registro_atividade_status'];
        $condicoes['periodo_apuracao_reg_ativ_ini']  = $fws_['periodo_apuracao_reg_ativ_ini'];
        $condicoes['periodo_apuracao_reg_ativ_fim']  = $fws_['periodo_apuracao_reg_ativ_fim'];
        $condicoes['outros_filtros_inscrito'] = $fws_['outros_filtros_inscrito'];
        $condicoes['cod_evento']   = $fws_['cod_evento'];
        $condicoes['cod_evento_vinculado']   = $fws_['cod_evento_vinculado'];
        $condicoes['status_processo_vinculado']   = $fws_['status_processo_vinculado'];
        $condicoes['tipo_produto'] = $fws_['tipo_produto'];
        $condicoes['negar_filtro'] = $fws_['negar_filtro'];
        $condicoes['avaliacao_trabalhos'] = $fws_['avaliacao_trabalhos'];

        $condicoes['nome_inscrito'] = trim($fws_['nome_inscrito']);
        $condicoes['cod_inscrito']  = trim($fws_['cod_inscrito']);
        $condicoes['cod_trabalho']  = trim($fws_['cod_trabalho']);
        $condicoes['e_mail']        = trim($fws_['e_mail']);
        $condicoes['cpf']           = formatCpfInsert(trim($fws_['cpf']));
        $condicoes['fone_contato']  = trim($fws_['fone_contato']);
        $condicoes['titulo_inscrito'] = trim($fws_['titulo_inscrito']);
        $condicoes['id_publico']   = trim($fws_['id_publico']);
        $condicoes['pedido_id']   = trim($fws_['pedido_id']);
        $condicoes['transacao_id']   = trim($fws_['transacao_id']);

        $condicoes['mostrarInscritos'] = 0;
        if (
            !empty($fws_['nome_inscrito']) ||
            !empty($fws_['cod_inscrito']) ||
            !empty($fws_['cod_trabalho']) ||
            !empty($fws_['e_mail']) ||
            !empty($fws_['cpf']) ||
            !empty($fws_['fone_contato']) ||
            !empty($fws_['id_publico']) ||
            !empty($fws_['pedido_id']) ||
            !empty($fws_['transacao_id']) 
        ) {
            $condicoes['mostrarInscritos'] = 1;
        }
        
        return $condicoes;
    }
    /**
     * Verifica se o inscrito possui voucher cadastrado, caso positivo:
     * carrega  objInscritoVoucher
     * returna true
     */
    public function getInscritoPossuiVoucher(){
        $obj = new InscritoVoucher();
        $obj->setCodInscrito($this->getCodInscrito());
        if($obj->inscritoPossuiVoucher($debug)){
            $this->setObjInscritoVoucher($obj);
            return true;
        }
        return false;
    }
    function carregarObjEvento($debug = false){
        $objEvento = new Evento();
        $objEvento->setCodEvento($this->getCodEvento());
        $objEvento->getEventoById($debug);
        $this->setObjEvento($objEvento);
    }
    function carregarOjbPerfil($debug = false){
        $objPerfil = new Perfil();
        $objPerfil->setCodPerfil($this->getCodPerfil());
        $objPerfil->setCodEvento($this->getCodEvento());
        $objPerfil->getPerfilById($debug);
        $this->setObjPerfil($objPerfil);
    }
    function carregarUltimoPagamento($debug = false){
        $objPagamento = new Pagamento();
        $objPagamento->setCodInscrito($this->getCodInscrito());
        $objPagamento->getPagamentoByUltimo($debug);
        $this->setObjPagamento($objPagamento);
    }
    function carregarOjbOficina($debug = false){
        $objOficina = new Oficina();
        $objOficina->setCodOficina($this->getCodOficina());
        $objOficina->getOficinaById($debug);
        $this->setObjOficina($objOficina);
    }
    function carregarListaBoletosByCodInscrito($debug = false){
        $objBoleto = new Boleto();
        $objBoleto->setCodInscrito($this->getCodInscrito());
        try {
            $objBoleto->carregarListaBoletosByCodInscrito($debug);
            $this->setListaBoletos($objBoleto->getListaBoletos());
            $this->setPossuiBoletoGerado(true);
        } catch (Exception $exc) {
            $this->setPossuiBoletoGerado(false);
        }
    }
    public function localizarInscricaoAtivada($debug = false) {
        return $this->objDAO->localizarInscricaoAtivada($this, $debug);
    }
    public function registrarImpressaoCracha($debug = false) {
        return $this->objDAO->registrarImpressaoCracha($this, $debug);
    }
    public function verificarInscritoApto($debug = false) {
        if ($this->getEMail() == '') {
            new Exception('E-mail não informado.');
        }
        if($this->objDAO->getByEmailInscritoApto($this, $debug)){
            return true;
        }
        if($_SERVER['REMOTE_ADDR']=='189.90.70.566'){
            if ($this->getCpf() != '') {
                return $this->objDAO->getByCpfInscritoApto($this, $debug);
            }
        return false;

        }
    }
    public function verificarCpfInscritoApto($debug = false) {
        if ($this->getCpf() == '') {
            new Exception('Cpf não informado.');
        }
        return $this->objDAO->getByCpfInscritoApto($this, $debug);
    }
    public function verificarEMailInscritoApto($debug = false) {
        if ($this->getEMail() == '') {
            new Exception('E-mail não informado.');
        }
        return $this->objDAO->getByCpfInscritoApto($this, $debug);
    }
    public function getListaInscritosStatusByIdEvento($condicoes, $debug = false) {
        if (!$this->getCodEvento()) {
            throw new Exception("Id do evento não informado.");
        }
        $this->objDAO->carregarListaInscritosStatusByIdEvento($this, $condicoes, $debug);
        if ($this->getListaInscritosStatus()) {
            return $this->getListaInscritosStatus();
        } else {
            throw new Exception("Nenhuma alteração de situação no processo foi registrada para este evento. O histórico é gerado quando o status de um inscrito é alterado (ex: Inscrito → Convocado, Convocado → Participando, etc).");
        }
    }
    public function verificarEstaInscrito($debug = false) {
        return $this->verificarEmailEstaInscrito($debug);
    }
    public function verificarEmailEstaInscrito($debug = false) {
        if ($this->getEMail() == '') {
            new Exception('E-mail não informado.');
        }
        return $this->objDAO->getByEmailExiste($this, $debug);
    }
    public function verificarCpfEstaInscritoMesmoPerfil($debug = false) {
        if ($this->getCpf() == '') {
            new Exception('Cpf não informado.');
        }
        return $this->objDAO->getByCpfExisteMesmoPerfil($this, $debug);
    }
    public function getByEmailExistePopular($debug = false) {
        if ($this->getEMail() == '') {
            new Exception('E-mail não informado.');
        }
        return $this->objDAO->getByEmailExistePopular($this, $debug);
    }
    public function verificarCpfEstaInscrito($debug = false) {
        if ($this->getCpf() == '') {
            new Exception('Cpf não informado.');
        }
        return $this->objDAO->getByCpfExiste($this, $debug);
    }
    /**
     * Critica da carga: quais CPFs do arquivo ja tem inscrito ATIVADA no evento.
     * Consulta unica, sem gravar nada -- quem decide o que fazer e o usuario.
     */
    public function carregarConflitosPorCpf($cpfs, $debug = false) {
        return $this->objDAO->carregarConflitosPorCpf($this->getCodEvento(), $cpfs, $debug);
    }
    /**
     * Substitui, no inscrito ja existente, apenas os campos que vieram na planilha.
     * Usado quando o usuario confirma a substituicao na critica de conflito.
     */
    public function atualizarCamposCarga($codInscrito, $valores, $debug = false) {
        return $this->objDAO->atualizarCamposCarga($this->getCodEvento(), $codInscrito, $valores, $debug);
    }
    public function verificarTransacaoIdEstaInscrito($debug = false) {
        if ($this->getTransacaoId() == '') {
            new Exception('Transação Id não informado.');
        }
        return $this->objDAO->getByTransacaoIdExiste($this, $debug);
    }
    public function carregarParticipacaoByLoginCpf($debug = false) {
        if ($this->getLogin() == '') {
            new Exception('Login não informado.');
        }
        if ($this->getCodEvento() == '') {
            new Exception('Código do Evento não informada.');
        }
        return $this->objDAO->carregarParticipacaoByLoginCpf($this, $debug);
    }
    public function carregarParticipacaoByLoginCodInscrito($debug = false) {
        if ($this->getLogin() == '') {
            new Exception('Login não informado.');
        }
        if ($this->getCodInscrito() == '') {
            new Exception('Login não informado.');
        }
        if ($this->getCodEvento() == '') {
            new Exception('Código do Evento não informada.');
        }
        return $this->objDAO->carregarParticipacaoByLoginCodInscrito($this, $debug);
    }
    public function carregarParticipacaoByLogin($debug = false) {
        if ($this->getLogin() == '') {
            new Exception('Login não informado.');
        }
        if ($this->getCodEvento() == '') {
            new Exception('Código do Evento não informada.');
        }
        return $this->objDAO->carregarParticipacaoByLogin($this, $debug);
    }
    public function efetuarLogin($debug = false) {
        if ($this->getEMail() == '') {
            new Exception('E-mail não informado.');
        }
        if ($this->getSenha() == '') {
            new Exception('Senha não informada.');
        }
        if ($this->get4cpf() == '') {
            new Exception('Quatro últimos dígitos do seu CPF');
        }
        return $this->objDAO->efetuarLogin($this, $debug);
    }
    public function efetuarLoginSemEvento($debug = false) {
        if ($this->getEMail() == '') {
            new Exception('E-mail não informado.');
        }
        if ($this->getSenha() == '') {
            new Exception('Senha não informada.');
        }
        if ($this->get4cpf() == '') {
            new Exception('Quatro últimos dígitos do seu CPF');
        }
        return $this->objDAO->efetuarLoginSemEvento($this, $debug);
    }
    public function efetuarLoginCpf($debug = false) {
        if ($this->getCpf() == '') {
            new Exception('Cpf não informado.');
        }
        if ($this->getSenha() == '') {
            new Exception('Senha não informada.');
        }
        return $this->objDAO->efetuarLogin($this, $debug);
    }
    public function efetuarLoginSisUsuario($debug = false) {
        if ($this->getEMail() == '') {
            new Exception('E-mail não informado.');
        }
        if ($this->getSenha() == '') {
            new Exception('Senha não informada.');
        }
        // Buscar usuario por login (sem senha no WHERE)
        if (!$this->objDAO->efetuarLoginSisUsuario($this, $debug)) {
            return false;
        }
        // Verificar senha — bcrypt primeiro, depois texto puro (migracao gradual)
        $senhaPlainText = $this->getSenha();
        $senhaValida = false;
        if ($this->getSenhaHash() && SenhaHelper::isBcrypt($this->getSenhaHash())) {
            $senhaValida = SenhaHelper::verify($senhaPlainText, $this->getSenhaHash());
        } elseif ($this->getSenhaLegada() && $this->getSenhaLegada() !== '') {
            $senhaValida = ($senhaPlainText === $this->getSenhaLegada());
        }
        if (!$senhaValida) {
            // Limpar dados carregados
            $this->setCodUsuario('');
            return false;
        }
        return true;
    }

    public function InscritoExiste() {
        if (!$this->getCodInscrito()) {
            throw new Exception('Código do Inscrito inválido ou não informado.');
        }
        return $this->objDAO->getByIdExiste($this);
    }

    public function novo($debug = false) {
        
    }

    public function salvar($debug = false) {

        $this->validatorObj();
        if (is_null($this->getCodInscrito()) or $this->getCodInscrito() == "") {
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Inscriçao cadastrada com sucesso.<br>Senha de acesso é seu número de inscrição<br>A confirmação de sua inscrição foi enviada para:".$this->getEMail());
            } else {
                throw new Exception("Nova Inscriçao não foi salva.");
            }
        } else {
            $this->InscritoExiste($debug);

            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Inscriçao foi atualizada com sucesso.");
            } else {
                throw new Exception("Inscriçao atual não foi atualizada.");
            }
        }
    }
    
    public function salvarCarga($debug = false) {

        $this->validatorObjCarga();

        if (is_null($this->getCodInscrito()) or $this->getCodInscrito() == "") {
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Inscriçao cadastrada com sucesso.<br>Senha de acesso é seu número de inscrição<br>A confirmação de sua inscrição foi enviada para:".$this->getEMail());
            } else {
                throw new Exception("Nova Inscriçao não foi salva.");
            }
        } else {
            $this->InscritoExiste($debug);

            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Inscriçao foi atualizada com sucesso.");
            } else {
                throw new Exception("Inscriçao atual não foi atualizada.");
            }
        }
    }
    
    public function arquivarParaExcluidos($debug = false) {
        if (is_null($this->getCodInscrito()) || $this->getCodInscrito() == '') {
            throw new Exception('Codigo do Inscrito invalido para arquivamento.');
        }
        if (is_null($this->getCodEvento()) || $this->getCodEvento() == '') {
            throw new Exception('Codigo do Evento invalido para arquivamento.');
        }
        return $this->objDAO->arquivarParaExcluidos($this, $debug);
    }

    public function salvarPresenca($debug = false) {
        $this->InscritoExiste($debug);
        if ($this->objDAO->update($this, $debug)) {
            throw new OKException("Presença alterada com sucesso.");
        } else {
            throw new Exception("Presença NÃO alterada com sucesso.");
        }
    }

    /**
     * Atualiza SOMENTE flg_presenca_inscrito via UPDATE direcionado (sem getById,
     * que corrompe cod_inscrito, nem GetUpdateSQL que reescreve a linha inteira).
     * Usado pela carga de presenca por inscrito. Requer cod_evento e cod_inscrito ja setados.
     */
    public function marcarPresenca($debug = false) {
        return $this->objDAO->marcarPresenca($this, $debug);
    }

    /**
     * Grava SOMENTE o bloqueio do certificado GERAL de participacao (opt-out).
     * Requer cod_evento e cod_inscrito ja setados. NULL/0 = recebe.
     */
    public function bloquearCertificadoGeral($debug = false) {
        return $this->objDAO->bloquearCertificadoGeral($this, $debug);
    }

    /* Contadores de APTOS para o Painel de Certificados (so leitura). */
    public function contarAptosPorPerfil($codEvento, $debug = false) {
        return $this->objDAO->contarAptosPorPerfil($codEvento, $debug);
    }
    public function contarAptosPorOficina($codEvento, $debug = false) {
        return $this->objDAO->contarAptosPorOficina($codEvento, $debug);
    }
    public function contarPresencaEvento($codEvento, $debug = false) {
        return $this->objDAO->contarPresencaEvento($codEvento, $debug);
    }

    public function excluir($debug = false) {
        $this->validatorEcluir();
        if ($this->objDAO->delete($this, $debug)) {
            throw new OKException("Inscrito cód:" . $this->getCodInscrito() . " foi excluída com sucesso.");
        } else {
            throw new Exception("Inscrito cód:" . $this->getCodInscrito() . " não foi excluída.");
        }
    }

    public function getInscritoByQrCode($debug = false) {
        return $this->objDAO->getByQrCode($this, $debug);
    }

    public function getInscritoById($debug = false) {
        return $this->objDAO->getById($this, $debug);
    }
    
    public function getInscritoByIdSemEvento($debug = false) {
        return $this->objDAO->getByIdSemEvento($this, $debug);
    }
    
    public function getInscritoByEmail($debug = false) {
        return $this->objDAO->getByEmail($this, $debug);
    }

    public function getInscritoByEmailToCredencial($debug = false) {
        return $this->objDAO->getByEmailCredencial($this, $debug);
    }

    public function getListaInscritosByIdEvento($debug = false) {
        if (!$this->getCodEvento()) {
            throw new Exception("Id do evento não informado.");
        }
        $this->objDAO->carregarListaInscritosByIdEvento($this, $debug);
        if ($this->getListaInscritos()) {
            return $this->getListaInscritos();
        } else {
            throw new Exception("Evento não possui Inscrito cadastrada.");
        }
    }

    public function carregarListaInscritos($condicoes = false, $debug = false) {
        return $this->objDAO->carregarListaInscritos($this, $condicoes, $debug);
    }

    public function carregarListaOficinas($debug = false){
        $obj = new InscritoOficina();
        $obj->setCodInscrito($this->getCodInscrito());
        $obj->setCodEvento($this->getCodEvento());
        if($this->getCodEvento()==49){
            $obj->carregarListaOficinasLiberadasByIdEvento($debug);    
        }else{
            $obj->carregarListaOficinasByIdInscrito($debug);
        }
        $this->setListaOficinas($obj->getListaOficinas());
    }
    public function carregarListaMinistrantesOficinas($debug = false){
        $obj = new InscritoOficina();
        $obj->setCodInscrito($this->getCodInscrito());
        $obj->setCodPerfil($this->getCodPerfil());
        $obj->carregarListaMinistrantesOficinasByIdInscrito($debug);
        $this->setListaMinistrantesOficinas($obj->getListaMinistrantesOficinas());
    }
    
    public function carregarListaTrabalhos($debug = false){
        $obj = new InscritoTrabalho();
        $obj->setCodInscrito($this->getCodInscrito());
        $obj->carregarListaTrabalhosByIdInscrito($debug);
        $this->setListaTrabalhos($obj->getListaTrabalhos());
    }
    
    public function carregarListaTrabalhosByIdInscrito($debug = false){
        $obj = new Trabalho();
        $obj->setCodInscrito($this->getCodInscrito());
        $obj->carregarListaTrabalhosByIdInscrito($debug);
        $this->setListaTrabalhos($obj->getListaTrabalhos());
    }
    public function carregarListaTrabalhosAceitosByIdInscrito($debug = false){
        $obj = new Trabalho();
        $obj->setCodInscrito($this->getCodInscrito());
        $obj->carregarListaTrabalhosAceitosByIdInscrito($debug);
        $this->setListaTrabalhos($obj->getListaTrabalhos());
    }
    
    public function carregarListaPagamentosByIdInscrito($debug = false){
        $obj = new Pagamento();
        $obj->setCodInscrito($this->getCodInscrito());
        $obj->carregarListaPagamentosByIdInscrito($debug);
        $this->setListaPagamentos($obj->getListaPagamentos());
    }
    
    public function carregarListaPedidosIdInscrito($debug = false){
        $obj = new Pedido();
        $obj->setInscritoId($this->getCodInscrito());
        $obj->carregarListaPedidosByInscritoId($debug);
        $this->setListaPedidos($obj->getListaPedidos());
    }
    function carregarListaEventsByEmailInscrito($debug = false){
        $obj = new Evento();
        if($this->getEmail()==''){
            throw new Exception('Não foi possível localizar eventos por e-mail do inscrito');
        }
        $obj->setEmailInscrito($this->getEmail());
        $obj->carregarListaEventosByEmailInscrito($debug);
        $this->setListaEventos($obj->getListaEventos());
    }    
    function carregarListaEventosByCpfInscrito($debug = false){
        $obj = new Evento();
        if($this->getCpf()==''){
            throw new Exception('Não foi possível localizar eventos por e-mail do inscrito');
        }
        $obj->setCpfInscrito($this->getCpf());
        $obj->carregarListaEventosByCpfInscrito($debug);
        $this->setListaEventos($obj->getListaEventos());
    }
    /**
     * Carrega outras inscrições (agendas) do mesmo CPF no mesmo evento. Excluindo a do
     * próprio cod_inscrito atual. Usado no fluxo AGENDA para o bloco "Minhas Outras Agendas".
     */
    function carregarListaAgendasMesmoCpfMesmoEvento($debug = false){
        if($this->getCpf()==''){
            return array();
        }
        if(!$this->getCodEvento()){
            return array();
        }
        return $this->objDAO->carregarListaAgendasMesmoCpfMesmoEvento($this, $this->getCodInscrito(), $debug);
    }

    function carregarListaEventosByCpfInscritoApto($debug = false){
        $obj = new Evento();
        if($this->getCpf()==''){
            throw new Exception('Não foi possível localizar eventos por e-mail do inscrito');
        }
        $obj->setCpfInscrito($this->getCpf());
        $obj->carregarListaEventosByCpfInscritoApto($debug);
        $this->setListaEventos($obj->getListaEventos());
    }    
    public function getEvento() {
        $obj = new InscEvento();
        $obj->setCodEvento($this->getCodEvento());
        $obj->getEventoById();
        return $obj;
    }
    
    public function carregarListaPerfil($condicoes=false, $debug = false) {
        $obj= new InscritoPerfil();
        $obj->setCodInscrito($this->getCodInscrito());
        $obj->setCodEvento($this->getCodEvento());
        $obj->carregarListaPerfilByIdInscrito($debug);
        $this->setListaPerfil($obj->getListaPerfil());
        if ($this->getListaPerfil()) {
            
        } else {
            throw new Exception("Inscrito não possui perfis cadastrados.");
        }
    }
}


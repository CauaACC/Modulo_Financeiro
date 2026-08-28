<?php

class Evento extends EventoVO {

    private $objDAO;

    function __construct() {
        $this->objDAO = new EventoDAO();
    }

    function getEventoConfiguracao($debug = false) {
        $objEvC = new EventoConfiguracao();
        $objEvC->setCodEvento($this->getCodEvento());
        $objEvC->getEventoConfiguracaoById($debug);
        return $objEvC;
    }

    public function EventoExiste() {
        if (!$this->getCodEvento()) {
            throw new Exception('Código da Evento é inválido ou não informado.');
        }
        if (!$this->objDAO->getByIdExiste($this)) {
            throw new Exception("Nenhuma Evento foi encontrada para o código informado: " . $this->getCodEvento() . ".");
            //throw new Exception("Id informado para deleção não existe");
        }
    }

    public function novo($debug = false) {
        
    }

    public function salvar($debug = false) {
        if (is_null($this->getCodEvento()) or $this->getCodEvento() == "") {
            $this->validatorIncluir();
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Evento salvo com sucesso.");
            } else {
                throw new Exception("Evento não foi salvo.");
            }
        } else {
            $this->EventoExiste($debug);
            $this->validatorObj();
            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Evento foi atualizada com sucesso.");
            } else {
                throw new Exception("Evento atual não foi atualizada.");
            }
        }
    }

    public function excluir($debug = false) {
        $this->validatorEcluir();
        if ($this->objDAO->delete($this, $debug)) {
            throw new OKException("Evento cód:" . $this->getCodEvento() . " foi excluída com sucesso.");
        } else {
            throw new Exception("Evento cód:" . $this->getCodEvento() . " não foi excluída.");
        }
    }

    public function getEventoByCodInscrito($codInscrito, $debug = false) {
        $this->setCodInscrito($codInscrito);
        if (!$this->getCodInscrito()) {
            // throw new Exception('Código do Inscrito inválido ou não informado: ' . $this->getCodInscrito());
        }
        $this->objDAO->getByCodInscrito($this, $debug);
    }

    public function getEventoById($debug = false) {
        $this->objDAO->getById($this, $debug);
    }
    
    public function getEventoByUrlPrefixo($debug = false) {
        $this->objDAO->getByUrlPrefixo($this, $debug);
    }

    public function getListaEventosByIdEvento($debug = false) {
        if (!$this->getCodEvento()) {
            throw new Exception("Id do evento não informado.");
        }
        $this->objDAO->carregarListaEventosByIdEvento($this, $debug);
        if ($this->getListaEventos()) {
            return $this->getListaEventos();
        } else {
            throw new Exception("Evento não possui Evento cadastrada.");
        }
    }

    public function carregarListaEventosByGestor($debug = false) {
        $this->objDAO->carregarListaEventosByGestor($this, $debug);
        if ($this->getListaEventos()) {
            return $this->getListaEventos();
        } else {
            throw new Exception("Lista de eventos não econtrada.");
        }
    }

    public function carregarListaEventosByEmailInscrito($debug = false) {
        $this->objDAO->carregarListaEventosByEmailInscrito($this, $debug);
        if ($this->getListaEventos()) {
            return $this->getListaEventos();
        } else {
            throw new Exception("Lista de eventos não econtrada.");
        }
    }

    public function carregarListaEventosByCpfInscrito($debug = false) {
        $this->objDAO->carregarListaEventosByCpfInscrito($this, $debug);
        if ($this->getListaEventos()) {
            return $this->getListaEventos();
        } else {
            throw new Exception("Lista de eventos não econtrada.");
        }
    }

    public function carregarListaEventosByCpfInscritoApto($debug = false) {
        $this->objDAO->carregarListaEventosByCpfInscritoApto($this, $debug);
        if ($this->getListaEventos()) {
            return $this->getListaEventos();
        } else {
            throw new Exception("Lista de eventos não econtrada.");
        }
    }
    
    public function carregarListaEventos($debug = false) {
        $this->objDAO->carregarListaEventos($this, $debug);
        if ($this->getListaEventos()) {
            return $this->getListaEventos();
        } else {
            throw new Exception("Lista de eventos não econtrada.");
        }
    }

    public function carregarListaEventosByIdLogin($debug = false) {
        $this->objDAO->carregarListaEventosByIdLogin($this, $debug);
        if ($this->getListaEventos()) {
            return $this->getListaEventos();
        } else {
            throw new Exception("Lista de eventos não econtrada.");
        }
    }

    /**
     * @param array $usuariosEscolhidos cod_usuario marcados na tela de duplicar.
     *        Quem duplicou SEMPRE recebe acesso (carregarPermissoes, no insert);
     *        esta lista decide so quem MAIS vem junto da origem. Vazia = ninguem.
     */
    public function duplicar($debug = 1, $usuariosEscolhidos = array()) {
        /* Inicializados aqui de proposito: se o salvar() abaixo nao lancar
         * OKException, estas duas ficavam indefinidas e o metodo seguia
         * copiando as listas para um cod_evento vazio. */
        $resultExec   = false;
        $codEventoAtu = null;
        $codEventoAnt = $this->getCodEvento();

        /*duplicando evento*/
        try {
            $this->getEventoById();
            $this->setTitulo('Cópia de '.$this->getTitulo());
            $this->setUrlPrefixo('');
            $this->setProjeto('');
            $this->setCodEvento(NULL);
            /** na duplicação já é carregado grupos, direitos e usuários padrão */
            /** tanto ao duplicar como ao salvar um novo, já executa o carregarPermissoes
             * porém importa outros usuários */
            $this->salvar($debug);
        } catch (OKException $exc) {
            $resultExec = true;
            $codEventoAtu = $this->getCodEvento();
        }

        /* Sem projeto novo nao ha para onde copiar. Antes o metodo seguia e
         * gravava as cinco listas com cod_evento indefinido -- lixo silencioso
         * no banco, e o usuario via "duplicado" sem projeto nenhum na lista. */
        if (!$codEventoAtu) {
            throw new Exception('Não foi possível duplicar Projeto: o projeto novo não chegou a ser criado.');
        }

        /*importando configuração*/
        try {
            $objEvC = new EventoConfiguracao();
            $objEvC->setCodEvento($codEventoAnt);
            $objEvC->getEventoConfiguracaoById($debug);
            $objEvC->setCodEvento($codEventoAtu);
            /** Configuração padrão */
            $objEvC->setEmitirCertificado(NULL);
            $objEvC->salvar($debug);
        } catch (OKException $exc) { $resultExec = true; }

        /*importando template de e-mail*/
        try {
            $objET = new EmailTemplate();
            $objET->setCodEventoAnt($codEventoAnt);
            $objET->setCodEvento($codEventoAtu);
            $objET->importarEmailTemplates($debug);
        } catch (OKException $exc) { $resultExec = true; }

         /*importando perfis*/
        try{
            $objP = new Perfil();
            $objP->setCodEventoAnt($codEventoAnt);
            $objP->setCodEvento($codEventoAtu);
            /** Configuração padrão */
            $objP->setLiberarCertificado(0);
            $objP->importarPerfis();
        } catch (OKException $ex) { $resultExec = true; }

         /*importando atividades*/
        try{
            $objP = new Oficina();
            $objP->setCodEventoAnt($codEventoAnt);
            $objP->setCodEvento($codEventoAtu);
            /** Configuração padrão */
            $objP->setLiberarCertificado(0);
            $objP->importarOficinas();
        } catch (OKException $ex) { $resultExec = true; }

         /*importando taxas*/
        try{
            $objP = new Taxa();
            $objP->setCodEventoAnt($codEventoAnt);
            $objP->setCodEvento($codEventoAtu);
            $objP->importarTaxas();
        } catch (OKException $ex) { $resultExec = true; }

        /*importando Usuarios da ORIGEM (opcional, escolhido na tela)
         * Ficou anos inalcancavel abaixo de um throw; agora roda aqui, antes
         * do fim, e so quando pedido. */
        if (is_array($usuariosEscolhidos) && count($usuariosEscolhidos) > 0) {
            try{
                $objU = new UsuarioEvento();
                $objU->setCodEventoAnt($codEventoAnt);
                $objU->setCodEvento($codEventoAtu);
                $objU->importarUsuariosEvento($debug, $usuariosEscolhidos);
            } catch (OKException $ex) { $resultExec = true; }
        }

        if($resultExec==true){
            throw new OKException('Projeto duplicado com sucesso.');
        }else{
            throw new Exception('Não foi possível duplicar Projeto.');
        }

        /* ATENCAO -- DAQUI PARA BAIXO O PHP NUNCA CHEGA.
         * O throw acima e incondicional, entao a importacao de usuarios abaixo
         * nunca executou desde que o duplicar existe: o projeto duplicado nasce
         * so com o molde do evento 91 + quem clicou + o coringa, sem a equipe
         * do projeto de origem.
         * Mantido intacto, no lugar, aguardando a decisao de como religar
         * (levantado em 2026-08-20: garantir primeiro o acesso de quem duplicou
         * e perguntar em modal o que carregar -- admins e operacional, ou so
         * admins). Nao apagar. */

        /*importando Usuarios*/
        try{
            $objU = new UsuarioEvento();
            $objU->setCodEventoAnt($codEventoAnt);
            $objU->setCodEvento($codEventoAtu);
            $objU->importarUsuariosEvento();
        } catch (OKException $ex) { $resultExec = true; }
        
        if($resultExec==true){
            throw new OKException('Projeto duplicado com sucesso.');
        }else{
            throw new Exception('Não foi possível duplicar Projeto.');
        }
    }

    /**
     * Traz para ESTE projeto (que ja existe) o conteudo de outro projeto.
     *
     * Irmao do duplicar(): copia as mesmas coisas, com o proposito invertido.
     * duplicar responde "quero OUTRO projeto igual a este" e por isso cria o
     * registro em tab_eventos; importar responde "este projeto aqui ja existe,
     * quero que ele fique igual aquele" e por isso NAO toca em tab_eventos.
     *
     * Comportamento nas tabelas de lista (perfis, atividades, taxas, templates):
     * ADICIONA aos que ja existem, igual ao duplicar — os INSERT ... SELECT sao
     * literalmente os mesmos. Nao ha dedup nem substituicao: nada que ja esta no
     * destino e alterado ou apagado. A consequencia pratica, que a tela avisa, e
     * que importar duas vezes da mesma origem traz o jogo duas vezes.
     *
     * A configuracao e a excecao: e UMA linha por projeto, entao ali a copia
     * sobrescreve mesmo.
     */
    public function importarConfiguracaoDe($codEventoOrigem, $debug = false) {
        $codDestino = $this->getCodEvento();

        /* A configuracao vem primeiro e e a unica que pode abortar tudo: se ela
         * falhar, alguma premissa esta errada (origem inexistente, sem config) e
         * anexar perfis e atividades so deixaria o projeto pela metade. */
        $objEvC = new EventoConfiguracao();
        $objEvC->setCodEventoOrigem($codEventoOrigem);
        $objEvC->setCodEventoDestino($codDestino);
        try {
            $objEvC->importarDeOutroEvento($debug);
        } catch (OKException $exc) {
            /* segue o baile */
        }

        $trazidos = array();
        $falhas   = array();

        /* Conexao e singleton — e a MESMA que os DAOs usam. Serve para ler o
         * Affected_Rows de cada INSERT ... SELECT e dizer QUANTOS itens vieram.
         * Sem isso a mensagem diria "vieram perfis" mesmo quando vieram zero,
         * que e exatamente a duvida de quem acabou de clicar. */
        $conex = FWA_Conexao();

        /* Perfis ANTES das taxas: o SQL das taxas casa o perfil do destino pelo
         * titulo "Copia de X", entao sem os perfis novos ele nao acha par. */
        $etapas = array(
            'perfis'      => array('classe' => 'Perfil',        'metodo' => 'importarPerfis',
                                   'padrao' => 'setLiberarCertificado'),
            'atividades'  => array('classe' => 'Oficina',       'metodo' => 'importarOficinas',
                                   'padrao' => 'setLiberarCertificado'),
            'taxas'       => array('classe' => 'Taxa',          'metodo' => 'importarTaxas',
                                   'padrao' => null),
            'templates de e-mail' => array('classe' => 'EmailTemplate', 'metodo' => 'importarEmailTemplates',
                                   'padrao' => null)
        );

        foreach ($etapas as $rotulo => $etapa) {
            try {
                $obj = new $etapa['classe']();
                $obj->setCodEventoAnt($codEventoOrigem);
                $obj->setCodEvento($codDestino);
                /* Certificado nasce desligado no que foi trazido, igual ao
                 * duplicar: liberar certificado sem querer e irreversivel. */
                if ($etapa['padrao']) {
                    $obj->{$etapa['padrao']}(0);
                }
                $obj->{$etapa['metodo']}($debug);
            } catch (OKException $exc) {
                /* Lido logo apos o INSERT, antes de qualquer outra query. */
                $trazidos[] = ((int) $conex->Affected_Rows()) . ' ' . $rotulo;
            } catch (Exception $exc) {
                $falhas[] = $rotulo;
            }
        }

        $msg = 'Configuração importada do projeto ' . $codEventoOrigem . '.';
        if ($trazidos) {
            $msg .= ' Também vieram: ' . implode(', ', $trazidos) . '.';
        }
        if ($falhas) {
            $msg .= ' <b>Não vieram:</b> ' . implode(', ', $falhas) . '.';
        }
        throw new OKException($msg);
    }

    public function carregarCondicoes($fws_, $debug = false) {
        $this->setCodEvento(trim($fws_['cod_evento']));
        $this->setGestor(trim($fws_['gestor']));
        $this->setProjeto(trim($fws_['projeto']));
        $this->setTitulo(trim($fws_['titulo']));
        $this->setNomeInscrito(trim($fws_['nome_inscrito']));
        $this->setFoneContato(trim($fws_['fone_contato']));
        $this->setCodInscrito(trim($fws_['cod_inscrito']));
        $this->setCodTrabalho(trim($fws_['cod_trabalho']));
        $this->setIdPublico(trim($fws_['id_publico']));
        $this->setPedidoId(trim($fws_['pedido_id']));
        $this->setTransacaoId(trim($fws_['transacao_id']));
        $this->setCpf(trim($fws_['cpf']));
        $this->setEEmail(trim($fws_['e_mail']));
        $this->setDataInscricaoIni($fws_['data_inscricao_ini']);
        $this->setDataInscricaoFim($fws_['data_inscricao_fim']);
        $this->setRealizacaoIni($fws_['realizacao_ini']);
        $this->setRealizacaoFim($fws_['realizacao_fim']);
        $this->setRecebePagamento($fws_['recebe_pagamento']);
        $this->setRecebePagamentoMensalidade($fws_['recebe_pagamento_mensalidade']);
        $this->setTrabSubmissao($fws_['trab_submissao']);
    }
}

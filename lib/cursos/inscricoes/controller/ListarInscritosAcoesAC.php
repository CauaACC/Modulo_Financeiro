<?php

class ListarInscritosAcoesAC {

    private static $arquivosGerados = [];

    /** Permissoes que liberam a area de Acao em Lote (semantica OU). */
    public static function permissoesAcaoEmLote() {
        return array(
            enumSistema::cod('SIS_ACOES_EM_LOTE'),   // cadastrado em sis_sistemas (Grupos e Sistemas)
            enumSistema::SIS_GERENCIAR_ADM_GERAL,    // 999 nao e superset automatico: precisa estar aqui
        );
    }

    public function __construct() {
        // era FWA_Autenticacao(..., 999, ...) cravado -- so ADM_GERAL executava, e o
        // retorno nem era testado. Agora e gate de verdade e liberavel por permissao.
        FWA_exigirPermissao(self::permissoesAcaoEmLote());
    }

    /** Quantos tokens EMITIDOS guardar na sessao (o usuario pode ter varias abas abertas). */
    const MAX_TOKENS_LOTE = 50;

    /** Campo hidden que carrega o token CSRF / de execucao unica. */
    const CAMPO_TOKEN_LOTE = 'tokenAcaoLote';

    /** Chave na sessao com os tokens emitidos e ainda nao usados. */
    const SESSAO_TOKENS_LOTE = 'acaoLoteTokensEmitidos';

    /**
     * Token novo para o formulario da listagem, um por render.
     *
     * Faz as duas coisas de uma vez:
     * - CSRF: e imprevisivel (random_bytes) e fica REGISTRADO na sessao. Um POST vindo
     *   de fora nao tem como adivinhar um valor que esteja nessa lista.
     * - Execucao unica: quem valida remove da lista, entao o mesmo envio nao passa duas
     *   vezes -- e o F5 (que reenvia o POST) nao refaz a acao.
     */
    public static function novoTokenAcaoLote() {
        try {
            $token = bin2hex(random_bytes(16));
        } catch (Exception $e) {
            // fallback para ambiente sem CSPRNG; melhor um token fraco que nenhum
            $token = md5(uniqid('lote', true) . mt_rand());
        }
        if (!isset($_SESSION[self::SESSAO_TOKENS_LOTE]) || !is_array($_SESSION[self::SESSAO_TOKENS_LOTE])) {
            $_SESSION[self::SESSAO_TOKENS_LOTE] = array();
        }
        $_SESSION[self::SESSAO_TOKENS_LOTE][] = $token;
        // janela: guarda os ultimos N emitidos (varias abas/telas abertas ao mesmo tempo)
        while (count($_SESSION[self::SESSAO_TOKENS_LOTE]) > self::MAX_TOKENS_LOTE) {
            array_shift($_SESSION[self::SESSAO_TOKENS_LOTE]);
        }
        return $token;
    }

    /**
     * Valida e CONSOME o token deste envio.
     *
     * So autoriza se o token veio do formulario que este servidor gerou para esta sessao.
     * Depois de consumido, o mesmo valor nunca mais passa: e o que impede o F5 de refazer
     * a acao (pedidos, e-mails e certificados em duplicidade).
     *
     * @return bool true = pode executar; false = token ausente, forjado ou ja usado
     */
    public static function consumirTokenAcaoLote($fws_) {
        $token = isset($fws_[self::CAMPO_TOKEN_LOTE]) ? trim($fws_[self::CAMPO_TOKEN_LOTE]) : '';
        if ($token === '') {
            return false;
        }
        if (!isset($_SESSION[self::SESSAO_TOKENS_LOTE]) || !is_array($_SESSION[self::SESSAO_TOKENS_LOTE])) {
            return false;
        }
        $posicao = array_search($token, $_SESSION[self::SESSAO_TOKENS_LOTE], true);
        if ($posicao === false) {
            return false;   // nao foi emitido aqui, ou ja foi usado (reload)
        }
        unset($_SESSION[self::SESSAO_TOKENS_LOTE][$posicao]);
        $_SESSION[self::SESSAO_TOKENS_LOTE] = array_values($_SESSION[self::SESSAO_TOKENS_LOTE]);
        return true;
    }

    /**
     * Aviso no lugar da execucao quando o token nao vale.
     * Na pratica quase sempre e reload; por isso a mensagem fala disso primeiro.
     */
    public static function avisoAcaoJaExecutada() {
        ?>
        <div class="alert alert-warning" style="margin:10px 0;">
            <b>Esta ação em lote não foi executada de novo.</b><br>
            O envio já tinha sido processado (a página foi recarregada) — nada foi refeito,
            para não duplicar. A ação continua selecionada abaixo: se precisar executar
            outra vez, confira os filtros e clique em <b>Listar na Tela</b> novamente.
        </div>
        <?php
    }

    public function executarAcoesEmLote($objL, $fws_, $debug = false){      
        if($fws_['acaoEmLote']=='gerarMensalidade'){
            echo "<br>Gerando Despesas/Pedidos: <br>";
            $qtdLote = isset($fws_['qtdParcelasGerar']) ? intval($fws_['qtdParcelasGerar']) : 1;
            if ($qtdLote > 1) {
                GerenciarPedidoAC::acaoGerarMensalidadesLote($objL,'MSG',$fws_, false);
            } else {
                GerenciarPedidoAC::acaoGerarMensalidade($objL,'MSG',$fws_, false);
            }
        }

        if($fws_['acaoEmLote']=='alterarVencimento'){
            GerenciarPedidoAC::acaoAlterarDiaVencimento($objL,'MSG',$fws_, false);
        }

        if($fws_['acaoEmLote']=='enviarComunicados'){
            GerenciarEmailComunicadoAC::acaoEnviarComunicados($objL, $fws_, $debug = false);
        }

        if($fws_['acaoEmLote']=='enviarComunicadoWhats'){
            sleep(3); // 2 a 5 segundos
            GerenciarWhatsAC::acaoEnviarComunicados($objL, $fws_, $debug = false);
        }

        if($fws_['acaoEmLote']=='definirPresenca'){
            $this->definirPresenca($objL, $fws_, $debug);
        }
        if($fws_['acaoEmLote']=='alterarStatusProcesso'){
            $this->alterarStatusProcesso($objL, $fws_, $debug);
        }
        if($fws_['acaoEmLote']=='gerarCertificado'){
            self::gerarCertificadosEmLote($objL, $fws_, $debug);
        }
    }

    public function definirPresenca($objL, $fws_, $debug = false){
                /*definir string ou array contendo titulos que faltaram, marcar falta ou presença*/
        //                $tituloComparar = substr(maiusculo($objL->getTituloTrabalho()),0,35);
        //                if(stristr(strtoupper($this->titulosFaltaram), "|".$tituloComparar."")){
        //                     GerenciarTrabalhoAC::definirPresenca($objL,'falta',false);
        //                }else{
        //                     GerenciarTrabalhoAC::definirPresenca($objL,'presenca',false);
        //                }
        if (($objL->getCodInscrito()!='') and ($objL->getCodOficina()=='')){
            ?>
            <script language="JavaScript" type="text/javascript">
                $(document).ready(function () {
                    alternarPresencaInscrito(<?php echo $objL->getCodInscrito()?>)
                });
            </script>
            <?php
            }
            if (($objL->getCodInscrito()!='') and ($objL->getCodOficina()!='')){
            ?>
            <script language="JavaScript" type="text/javascript">
                $(document).ready(function () {
                    alternarPresencaOficina(<?php echo $objL->getCodInscrito().','.$objL->getCodOficina()?>)
                });
            </script>
            <?php
            }
        //                try {
        //                    $objL->setFlgPresencaInscrito('1');
        //                    $obj->salvarPresenca($debug);
        //                } catch (Exception $exc) {
        //                    echo $exc->getTraceAsString();
        //                }
    }

    public static function gerarCertificado($objL, $fws_, $debug = false) {
        echo "<br><br>";
        // try {
            
        //     echo GerenciarInscricaoAC::gerarLinksEmailCertificados($objL, $fws_, $debug);
        //     $gerarCertificado = new GerenciarCertificadoAC();
        //     $fws_['tipoCertificado'] = 'participou_atividade';
        //     //$fws_['cod_trabalho'] = 'curso';
        //     $fws_['cod_inscrito'] = $objL->getCodInscrito();
        //     $fws_['cod_oficina']  = $objL->getCodOficina();
        //     if($fws_['cod_oficina']!=''){
        //         /*@todo o problema para gerar certificado durante a listagem, acontece dentro desse método no momento em que vai anexar o verso do certificado*/
        //         $gerarCertificado->gerarCertificadoArquivo($fws_, $debug);
        //     }
        // } catch (Exception $exc) {
        //     echo $exc->getTraceAsString();
        // }
    }
    public static function alterarStatusProcesso($obj, $fws_, $debug=false){
        try {
            // echo "->".$fws_['statusProcessoNovo']."<-<br>";
            if (!isset($fws_['statusProcessoNovo'])) {
                throw new Exception("Novo status no processo não definido.");
            }
            $objInscrito = new Inscrito();
            $objInscrito->setCodInscrito($obj->getCodInscrito());
            $objInscrito->getInscritoById();
            $objInscrito->setStatusProcesso($fws_['statusProcessoNovo']);
            $objInscrito->salvar();
        }catch (OKException $e){
            echo "<br>".$e->getMessage()." - ".$fws_['statusProcessoNovo']."<br>";
        }catch (Exception $e){
            echo "<br>".$e->getMessage();
        }
    }

    public static function gerarCertificadosEmLote($objL, $fws_, $debug = false){
        $codInscrito = $objL->getCodInscrito();
        if ($codInscrito == '') {
            return;
        }

        $objInscrito = new Inscrito();
        $objInscrito->setCodInscrito($codInscrito);
        $objInscrito->getInscritoById($debug);

        $nomeInscrito = $objInscrito->getNomeInscrito();
        $certGerados = 0;

        /* Validar pagamento/isenção */
        if (!$objInscrito->getCodVoucher() and !$objInscrito->getCodIsento() and !$objInscrito->getTotalPagamentos() and !$objInscrito->getTotalPerfisIsentos()) {
            echo "<br><font color='gray'>#{$codInscrito} {$nomeInscrito} - sem pagamento/isenção (pulando)</font>";
            return;
        }

        /* Verificar presença */
        $temPresenca = $objInscrito->getFlgPresencaInscrito();
        if (!$temPresenca && !in_array($objInscrito->getCodEvento(), array(61))) {
            echo "<br><font color='gray'>#{$codInscrito} {$nomeInscrito} - presença não confirmada (pulando)</font>";
            return;
        }

        $objEvConf = EventoConfiguracao::getInstancePopulado(COD_EVENTO);
        $gerarCertificado = new GerenciarCertificadoAC();

        /* --- Certificado de Congressista --- */
        $liberarCertificadoCongressista = $objEvConf->getCertificadoCongressista() == 1 ? true : false;
        if (in_array($objInscrito->getCodEvento(), array(2, 6, 7, 21, 61, 57, 70))) {
            $liberarCertificadoCongressista = true;
        }
        if (in_array($objInscrito->getCodEvento(), array(61)) && !$temPresenca) {
            $liberarCertificadoCongressista = false;
        }

        if ($liberarCertificadoCongressista) {
            try {
                $fwsCert = $fws_;
                $fwsCert['tipoCertificado'] = 'congressista';
                $fwsCert['cod_inscrito'] = $codInscrito;
                $fwsCert['cod_oficina'] = '';
                $fwsCert['cod_trabalho'] = '';
                $_SESSION['_cod_inscrito'] = $codInscrito;
                $arquivo = $gerarCertificado->gerarCertificadoEmLote($fwsCert, $debug);
                $_SESSION['_cod_inscrito'] = null;
                if ($arquivo) { self::$arquivosGerados[] = $arquivo; }
                $certGerados++;
                echo "<font color='green'> [congressista OK]</font>";
            } catch (Exception $e) {
                echo "<font color='red'> [congressista ERRO: " . $e->getMessage() . "]</font>";
            }
        }

        /* --- Certificados de Atividades/Oficinas --- */
        $liberarCertificadoAtividade = false;
        if (in_array($objInscrito->getCodEvento(), array(2, 6, 7, 21, 61, 57, 70))) {
            $liberarCertificadoAtividade = true;
        }
        if (in_array($objInscrito->getCodEvento(), array(61)) && !$temPresenca) {
            $liberarCertificadoAtividade = false;
        }

        if ($objInscrito->getTotalOficinas() >= 1) {
            $objInscrito->carregarListaOficinas();
            if ($objInscrito->getListaOficinas()) {
                foreach ($objInscrito->getListaOficinas() as $objOf) {
                    if ($objOf->getStatus() == 'ATIVADA' && $objOf->getLiberarCertificado() == 1) {
                        if ($objOf->getFlgPresenca() == 1 || $liberarCertificadoAtividade) {
                            try {
                                $fwsCert = $fws_;
                                $fwsCert['tipoCertificado'] = 'participou_atividade';
                                $fwsCert['cod_inscrito'] = $codInscrito;
                                $fwsCert['cod_oficina'] = $objOf->getCodOficina();
                                $fwsCert['cod_trabalho'] = '';
                                $_SESSION['_cod_inscrito'] = $codInscrito;
                                $arquivo = $gerarCertificado->gerarCertificadoEmLote($fwsCert, $debug);
                                $_SESSION['_cod_inscrito'] = null;
                                if ($arquivo) { self::$arquivosGerados[] = $arquivo; }
                                $certGerados++;
                                echo "<font color='green'> [atividade {$objOf->getCodOficina()} OK]</font>";
                            } catch (Exception $e) {
                                echo "<font color='red'> [atividade {$objOf->getCodOficina()} ERRO: " . $e->getMessage() . "]</font>";
                            }
                        }
                    }
                }
            }
        }

        /* --- Certificados de Trabalhos --- */
        if ($objInscrito->getTotalTrabalhos() >= 1) {
            $objInscrito->carregarListaTrabalhosByIdInscrito();
            if ($objInscrito->getListaTrabalhos()) {
                foreach ($objInscrito->getListaTrabalhos() as $objTrabalho) {
                    if ((($objInscrito->getTotalPerfisIsentos() >= 1) || ($objInscrito->getImpressaoCracha() == 1) || ($objTrabalho->getFlgPresenca() == 1 || $objTrabalho->getCodEvento() == 70)) && ($objTrabalho->getAvaliacaoFinal() == 'APROVADO')) {
                        try {
                            $fwsCert = $fws_;
                            $fwsCert['tipoCertificado'] = 'apresentador_trabalho';
                            $fwsCert['cod_inscrito'] = $codInscrito;
                            $fwsCert['cod_trabalho'] = $objTrabalho->getCodTrabalho();
                            $fwsCert['cod_oficina'] = '';
                            $_SESSION['_cod_inscrito'] = $codInscrito;
                            $arquivo = $gerarCertificado->gerarCertificadoEmLote($fwsCert, $debug);
                            $_SESSION['_cod_inscrito'] = null;
                            if ($arquivo) { self::$arquivosGerados[] = $arquivo; }
                            $certGerados++;
                            echo "<font color='green'> [trabalho {$objTrabalho->getCodTrabalho()} OK]</font>";
                        } catch (Exception $e) {
                            echo "<font color='red'> [trabalho {$objTrabalho->getCodTrabalho()} ERRO: " . $e->getMessage() . "]</font>";
                        }
                    }
                }
            }
        }

        echo "<br>#{$codInscrito} {$nomeInscrito} - <b>{$certGerados} certificado(s) gerado(s)</b>";
    }

    public static function getArquivosGerados() {
        return self::$arquivosGerados;
    }

    public static function gerarZipCertificados() {
        $arquivos = self::$arquivosGerados;
        if (empty($arquivos)) {
            echo "<br><font color='gray'>Nenhum certificado foi gerado para compactar.</font>";
            return;
        }

        $zipDir = WB_PATH_LOG_CERTIFICADOS;
        if (!is_dir($zipDir)) {
            mkdir($zipDir, 0777, true);
        }

        $zipFile = $zipDir . 'certificados-lote-' . numberPad(COD_EVENTO, 5) . '.zip';
        $zip = new ZipArchive();
        if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            echo "<br><font color='red'>Erro ao criar arquivo ZIP.</font>";
            return;
        }

        $adicionados = 0;
        foreach ($arquivos as $arquivo) {
            if (file_exists($arquivo)) {
                $zip->addFile($arquivo, basename($arquivo));
                $adicionados++;
            }
        }
        $zip->close();

        if ($adicionados == 0) {
            echo "<br><font color='gray'>Nenhum arquivo válido para compactar.</font>";
            return;
        }

        echo "<br><br><b>{$adicionados} certificado(s) compactado(s).</b>";
        self::exibirBotaoDownloadZip();
    }

    public static function exibirBotaoDownloadZip() {
        $zipFile = WB_PATH_LOG_CERTIFICADOS . 'certificados-lote-' . numberPad(COD_EVENTO, 5) . '.zip';
        if (!file_exists($zipFile)) {
            return;
        }
        $dataModificacao = date('d/m/Y H:i', filemtime($zipFile));
        $urlDownload = WB_URL_COMMAND . '&c=GerenciarCertificado&m=downloadZipCertificados&arquivo=' . urlencode(basename($zipFile));
        echo "<br><a href='{$urlDownload}' class='btn btn-success btn-sm' target='_blank'>";
        echo "<i class='fa fa-download'></i> Baixar ZIP de certificados</a>";
        echo " <small>(atualizado em {$dataModificacao})</small>";
    }
}
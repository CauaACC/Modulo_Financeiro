<?php

class GerenciarMidiaUploadAC
{
    public $tiposPermitidos;
    public $tamanho;
    public $varDiretorio;
    public $varArquivo;

    function __construct()
    {
        $this->codEvento = COD_EVENTO;
        $objConf = new GerenciarConfiguracaoEventoAC();
        $objConf->carregarConfiguracao($this);
    }

    public function carregarObjMidiaUpload($fws_, $debug = false)
    {
        $obj = new MidiaUpload();
        if ($fws_['codArquivo']) {
            $obj->setCodArquivo($fws_['codArquivo']);
            $obj->getMidiaUploadById($debug);
        }
        return $obj;
    }

    public function executarUpload(MidiaUpload $obj, $fws_, $debug = false){
        
        /**tamanhos, extensão e diretorio são setados no objeto VO */
        // $obj->carregarAtributosArquivos($fws_, $debug);
        $this->tamanho = $obj->getMidiaTamanho();
        $this->tiposPermitidos = array('pdf', 'doc', 'docx', 'rtf', 'png', 'ico', 'jpeg', 'jpg');
        $this->varDiretorio = rtrim($obj->getMidiaCaminho(), '/\\');
        // validarArquivo($_FILES,$this->tamanho,$this->tiposPermitidos);
        // Cria a pasta de destino se ainda nao existir e garante escrita.
        // Eventos novos nao tem a pasta media/; o sistema cria sozinho aqui.
        // IMPORTANTE: WB_PATH_LOG usa caminho relativo com ".." (WB_ROOT.'../logn/...').
        // O mkdir(recursive) do PHP falha com ".." no caminho, entao a media/
        // nunca era criada e o move_uploaded_file dava o confuso "Ocorreu um erro: 0".
        // Resolvemos o ".." via realpath do diretorio-pai (que ja existe) antes do mkdir.
        $pai = dirname($this->varDiretorio);
        if (is_dir($pai)) {
            $paiReal = realpath($pai);
            if ($paiReal !== false) {
                $this->varDiretorio = $paiReal . DIRECTORY_SEPARATOR . basename($this->varDiretorio);
            }
        }
        if (!is_dir($this->varDiretorio)) {
            // @ evita vazar warning no meio do JSON de resposta; a causa real
            // e capturada via error_get_last() e jogada na mensagem da exception.
            if (!@mkdir($this->varDiretorio, 0777, true)) {
                $err = error_get_last();
                $paiDir = dirname($this->varDiretorio);
                throw new Exception("Nao foi possivel CRIAR a pasta de destino da midia."
                    . " [pasta=" . $this->varDiretorio . "]"
                    . " [pai_existe=" . (is_dir($paiDir) ? 'sim' : 'NAO') . "]"
                    . " [pai_gravavel=" . (is_writable($paiDir) ? 'sim' : 'NAO') . "]"
                    . ($err && !empty($err['message']) ? " [php=" . $err['message'] . "]" : '')
                    . " - verifique as permissoes de escrita em logn/eventos/.");
            }
        }
        @chmod($this->varDiretorio, 0777);
        if (!is_dir($this->varDiretorio) || !is_writable($this->varDiretorio)) {
            throw new Exception("A pasta de destino da midia existe mas NAO e gravavel."
                . " [pasta=" . $this->varDiretorio . "]"
                . " [gravavel=" . (is_writable($this->varDiretorio) ? 'sim' : 'NAO') . "]"
                . " - ajuste as permissoes (chmod) em logn/eventos/.");
        }
        $obj->setArquivoUpload($_FILES['arquivo']['name']);

        $extensao = pathinfo($_FILES['arquivo']['name'], PATHINFO_EXTENSION);
        /** definindo nome */
        $this->varArquivo = $obj->gerarNomeArquivo();
       
        gravandoArquivo($_FILES, $this->varDiretorio, $this->varArquivo, $debug);

        $obj->setArquivoGerado($this->varArquivo);
        $text = $this->varArquivo.'- tamanho: '.$_FILES['arquivo']['size'];
        executarLog($text, 'tamanho-anexo-log', true);

        return $obj;
    }

    public function salvarMidiaUpload($fws_, $debug = false)
    {
        // Endpoint acessivel por POST direto via CommandAC (nao passa pelo
        // seguranca.php da view). Sem isto, qualquer um chamaria o upload e
        // ainda colheria os caminhos do servidor expostos no diagnostico.
        $this->autenticarAdminMidia();

        $ok = false;
        $e = null;

        try {
            if ($fws_['novoCodArquivo'] == 0) {
                $fws_['codArquivo'] = NULL;
            }
            $obj = $this->carregarObjMidiaUpload($fws_);

            // Sets explicitos no lugar de setAll($fws_, true): o setAll aborta no
            // 1o campo do form (_submit_check) e deixa o objeto ZERADO -> localMidia
            // vazio -> upload grava em pasta errada e sem nome de arquivo.
            // (Armadilha setAll + _submit_check, documentada na memoria do projeto.)
            $localMidia = isset($fws_['localMidia']) ? $fws_['localMidia'] : '';
            $obj->setLocalMidia($localMidia);
            $obj->setTipoArquivo($localMidia); // coluna tipo_arquivo persiste a escolha do form
            $obj->setCodPerfil(isset($fws_['codPerfil']) ? $fws_['codPerfil'] : '');
            $obj->setCodOficina(isset($fws_['codOficina']) ? $fws_['codOficina'] : '');

            // Whitelist: localMidia tem que ser uma das chaves validas
            // (getListaAtributosMidia). Barra valores forjados antes de montar
            // qualquer caminho de gravacao no executarUpload.
            if (!$obj->isValidLocalMidia()) {
                throw new Exception("Local de midia invalido ou nao informado.");
            }

            $obj = $this->executarUpload($obj, $fws_, $debug);
           
            $obj->salvar();
            $ok = true;
        } catch (OkException $ex) {
            $ok = true;
        } catch (Exception $ex) {
        }

        // json_encode garante que a mensagem de diagnostico (com paths, aspas,
        // barras, etc.) chegue integra ao cliente sem quebrar o parse do JSON.
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(array(
            'statusMessage' => $ok ? '1' : '',
            'message'       => isset($ex) && $ex ? $ex->getMessage() : '',
            'codArquivo'    => isset($obj) && $obj ? $obj->getCodArquivo() : ''
        ));
    }

    public function editarMidiaUpload($fws_, $debug = false)
    {
        $this->autenticarAdminMidia();
        $obj = $this->carregarObjMidiaUpload($fws_, $debug);
        echo '{
                  "statusMessage":"' . $ok . '",
                  "message":"' . $obj->getMessage() . '",
                  "codArquivo":"' . ($obj ? $obj->getCodArquivo() : '') . '",
                  "localMidia": "' . $obj->getLocalMidia() . '",
                  "tipoArquivo": "' . $obj->getTipoArquivo() . '",
                  "codPerfil": "' . $obj->getCodPerfil() . '",
                  "codOficina": "' . $obj->getCodOficina() . '"
                 }';
        exit();
    }

    public function excluirMidiaUpload($fws_, $debug = false)
    {
        $this->autenticarAdminMidia();
        try {
            $obj = $this->carregarObjMidiaUpload($fws_);
            // Resolve o caminho fisico ANTES de apagar o registro (precisamos dos
            // dados do objeto). Confinado em logn/eventos/{cod}/ pelo helper.
            $caminhoFisico = $this->resolverCaminhoFisicoMidia($obj);
            $obj->excluir();
        } catch (OKException $e) {
            $ok = true;
            // Registro removido com sucesso -> apaga o arquivo fisico orfao.
            // @ pois a ausencia do arquivo nao deve quebrar a exclusao do registro.
            if (!empty($caminhoFisico)) {
                @unlink($caminhoFisico);
            }
        } catch (Exception $e) {
            $ok = false;
        }

        echo '{
            "statusMessage":"' . $ok . '",
            "message":"' . $e->getMessage() . '",
            "codArquivo":"' . $fws_['codArquivo'] . '",
            "localMidia": "' . $obj->getLocalMidia() . '",
            "codPerfil": "' . $obj->getCodPerfil() . '",
            "codOficina": "' . $obj->getCodOficina() . '"
            }';
        exit();
    }

    /**
     * Verifica se o usuario logado tem permissao de admin para acessar este modulo.
     * Necessario porque a rota CommandAC e acessivel direto por GET e NAO passa
     * pelo consulta-dados-congresso-seguranca.php da view. Sem isto o download
     * seria publico. Espelha exatamente os perfis daquele arquivo de seguranca.
     */
    private function autenticarAdminMidia()
    {
        $cod_msg = false;
        $cod = isset($_SESSION['_cod_usuario']) ? $_SESSION['_cod_usuario'] : 0;
        if (
            FWA_Autenticacao($cod, enumSistema::SIS_GERENCIAR_ADM_GERAL, 5, $cod_msg, false) == false
            and FWA_Autenticacao($cod, enumSistema::SIS_GERENCIAR_INSCRITO, 5, $cod_msg, false) == false
            and FWA_Autenticacao($cod, enumSistema::SIS_GERENCIAR_AREAS_TEMATICAS, 5, $cod_msg, false) == false
            and FWA_Autenticacao($cod, enumSistema::SIS_CONSULTAR_PAINEL_BORDO, 5, $cod_msg, false) == false
            and FWA_Autenticacao($cod, enumSistema::SIS_CONSULTAR_INSCRITOS, 5, $cod_msg, false) == false
        ) {
            throw new Exception("Voc&ecirc; n&atilde;o tem permiss&atilde;o para baixar este arquivo.");
        }
    }

    /**
     * Resolve o caminho fisico real de uma midia ja carregada do banco, confinado
     * dentro de WB_PATH_LOG (logn/eventos/{COD_EVENTO}/) como defesa anti
     * path-traversal. Retorna o caminho absoluto validado, ou null se o arquivo
     * nao existir / estiver fora da pasta permitida. Usado pelo download e pela
     * exclusao do arquivo fisico.
     */
    private function resolverCaminhoFisicoMidia(MidiaUpload $obj)
    {
        // getMidiaCaminho() depende de getLocalMidia(); registros carregados do
        // banco trazem apenas tipo_arquivo, entao alinhamos antes.
        if ($obj->getLocalMidia() == '') {
            $obj->setLocalMidia($obj->getTipoArquivo());
        }

        $nomeArquivo = basename($obj->getArquivoGerado());
        if ($nomeArquivo === '' || $nomeArquivo === '.' || $nomeArquivo === '..') {
            return null;
        }

        $diretorio = rtrim($obj->getMidiaCaminho(), '/\\');
        $caminhoCompleto = $diretorio . DIRECTORY_SEPARATOR . $nomeArquivo;

        $real     = realpath($caminhoCompleto);
        $baseReal = realpath(WB_PATH_LOG);
        if ($real === false || $baseReal === false
            || strpos($real, $baseReal . DIRECTORY_SEPARATOR) !== 0
            || !is_file($real)
        ) {
            return null;
        }
        return $real;
    }

    /**
     * Faz o download de uma midia ja enviada.
     * Seguranca:
     *  - exige autenticacao de admin (autenticarAdminMidia);
     *  - aceita apenas codArquivo (inteiro) do cliente; o registro e carregado
     *    escopado por COD_EVENTO (getMidiaUploadById), entao nao da pra puxar
     *    midia de outro evento;
     *  - o diretorio vem do mapa estatico (getMidiaCaminho) e o nome vem do banco
     *    (arquivo_gerado) com basename() aplicado; nada de caminho vindo do cliente;
     *  - confina o caminho final via realpath dentro de WB_PATH_LOG (anti path-traversal).
     */
    public function downloadMidiaUpload($fws_, $debug = false)
    {
        $this->autenticarAdminMidia();

        $codArquivo = isset($fws_['codArquivo']) ? (int) $fws_['codArquivo'] : 0;
        if ($codArquivo <= 0) {
            throw new Exception("Arquivo inv&aacute;lido.");
        }

        $obj = new MidiaUpload();
        $obj->setCodArquivo($codArquivo);
        $obj->getMidiaUploadById($debug);

        if (!$obj->getCodArquivo()) {
            throw new Exception("Arquivo n&atilde;o encontrado.");
        }

        // Caminho confinado dentro de logn/eventos/{COD_EVENTO}/ (anti path-traversal).
        $real = $this->resolverCaminhoFisicoMidia($obj);
        if ($real === null) {
            throw new Exception("Arquivo n&atilde;o encontrado no servidor.");
        }
        $nomeArquivo = basename($real);

        $this->enviarArquivoParaDownload($real);
    }

    /** Envia o binario ja confinado por resolverCaminhoFisicoMidia(). */
    private function enviarArquivoParaDownload($real)
    {
        $nomeArquivo = basename($real);

        // Limpa qualquer saida acumulada (config.php etc.) antes de enviar o binario.
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $nomeArquivo . '"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($real));
        readfile($real);
        exit;
    }

    /**
     * Modelo do resumo (local de midia MODELO-DO-RESUMO) do evento corrente.
     * Retorna o MidiaUpload ou null. Usado na configuracao, na area do inscrito
     * e no download.
     */
    public static function localizarModeloResumo($debug = false)
    {
        $obj = new MidiaUpload();
        $obj->setArrayTipoArquivo(array('MODELO-DO-RESUMO'));
        $lista = $obj->carregarListaMidiasUploadByMidias($debug);
        if (!$lista) {
            return null;
        }
        return $lista[0];
    }

    /**
     * Download do modelo do resumo pelo INSCRITO (area do trabalho).
     * Nao recebe codArquivo do cliente: o registro e localizado pelo local de
     * midia dentro do COD_EVENTO da sessao, e o caminho passa pelo mesmo
     * confinamento do downloadMidiaUpload.
     */
    public function baixarModeloResumo($fws_, $debug = false)
    {
        if (empty($_SESSION['_cod_inscrito']) and empty($_SESSION['_cod_usuario'])) {
            throw new Exception("Sess&atilde;o encerrada. Fa&ccedil;a login novamente.");
        }

        $obj = self::localizarModeloResumo($debug);
        if (!$obj) {
            throw new Exception("Modelo n&atilde;o dispon&iacute;vel para este evento.");
        }

        $real = $this->resolverCaminhoFisicoMidia($obj);
        if ($real === null) {
            throw new Exception("Modelo n&atilde;o encontrado no servidor.");
        }

        $this->enviarArquivoParaDownload($real);
    }

    public function carregarTabelaMidiaUpload($fws_, $debug = false)
    {
        $this->autenticarAdminMidia();
        $obj = new MidiaUpload();
        $lista = [];
        try {
            $lista = $obj->carregarListaMidiasUploadByMidias($debug);
        } catch (Exception $e) {}
        $ii = 0;
        if (!empty($lista)) {
            foreach ($lista as $objL) {
                $ii++;
                ?>
                <tr id="<?php echo $objL->getCodArquivo() ?>">
                    <td>
                        <span class="btn-acao editar" title="Editar"><i class="fa fa-pencil"></i></span>
                        <span class="btn-acao btn-acao-excluir excluir" title="Excluir"><i class="fa fa-trash"></i></span>
                    </td>

                    <td><?php echo $objL->getLocalMidiaView()?></td>
                    <td><a href="#" class="baixar" title="Baixar arquivo"><i class="fa fa-download"></i> <?php echo $objL->getArquivoGerado() ?></a></td>
                    <td><?php echo $objL->getTituloPerfil() ?></td>
                    <td><?php echo $objL->getTituloOficina() ?></td>
                </tr>
                <?php
            }
        }
        if ($ii === 0) {
            echo '<tr><td colspan="5" class="text-center" style="padding:30px 10px; color:#999; font-style:italic;">Nenhuma m&iacute;dia encontrada.</td></tr>';
        }
    }
}
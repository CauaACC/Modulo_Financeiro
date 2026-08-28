<?php
include(dirname(__FILE__) .'/../include/functions/LeitorArquivo.php');

class GerenciarCargaPessoaUploadAC {

    function __construct() {
        FWA_exigirPermissao(enumSistema::SIS_GERENCIAR_ADM_GERAL);
        $this->tiposPermitidos = array('csv', 'txt', 'xls', 'xlsx');
        $this->tamanho = '30MB';
        $this->varDiretorio = WB_PATH_LOG_CARGA.'cargapessoas/';
        $this->prefixo = 'pessoa';
        $this->arrayTipoArquivo = array('LOGIN','INSCRITOS','INSCRITOS-APTOS','INSCRITOS-DESCONTOS','INSCRITOS-CERTIFICADO','INSCRITOS-DINAMICO','ENSALAMENTO_INSCRITO','PRESENCA_INSCRITO');
    }

    /**
     * Como o tipo aparece para quem usa. O VALOR gravado em tab_arquivo continua o
     * mesmo -- ha registros antigos com ele e o codigo compara por esse valor --,
     * muda so o rotulo. "DINAMICO" nao dizia nada a quem carrega; "COMPLETA" diz:
     * e a carga que traz todos os campos do inscrito, e nao so nome/CPF/e-mail.
     */
    public static function rotuloTipoArquivo($tipo, $curto = false) {
        $nomes = array(
            'INSCRITOS-DINAMICO' => 'INSCRITOS-COMPLETA',
        );
        $explicacoes = array(
            'INSCRITOS-DINAMICO'   => ' (todos os campos, mapear colunas)',
            'ENSALAMENTO_INSCRITO' => ' (sala por inscrito)',
            'PRESENCA_INSCRITO'    => ' (marcar presença)',
        );
        $nome = isset($nomes[$tipo]) ? $nomes[$tipo] : $tipo;
        if ($curto) {
            return $nome;   // listagem: so o nome, para nao alargar a coluna
        }
        return $nome . (isset($explicacoes[$tipo]) ? $explicacoes[$tipo] : '');
    }

    public function carregarArquivo($fws_, $debug = false) {
        $obj = new Arquivo();
        $obj->setCodArquivo($fws_['codArquivo']);
        $obj->carregarArquivoById($debug);
        return $obj;
    }

    public function editarArquivo($fws_, $debug = false) {
        $obj = $this->carregarArquivo($fws_,$debug);

        echo '{
                  "statusMessage":"' . $ok . '",
                  "message":      "' . $obj->getMessage() . '",
                  "codArquivo":   "' . $obj->getCodArquivo() . '",
                  "tipoArquivo":  "' . $obj->getTipoArquivo() . '",
                  "arquivoGerado":"' . $obj->geArquivoGerado() . '",
                  "dataInclusao": "' . $obj->getDataInclusao() . '" 
                 }';
        exit();
    }

    public function salvarArquivo($fws_, $debug = false) {
        try {
            if ($fws_['novoCodArquivo'] == 0) {
                $fws_['codArquivo'] = NULL;
            }
            $isForm = true;
            $obj = $this->carregarArquivo($fws_);
            $obj->setAll($fws_, $isForm);
            $obj->salvar($debug);
        } catch (OkException $e) {
            $ok = true;
        } catch (Exception $e) {
            $ok = false;
        }

        echo '{
        "statusMessage":"' . $ok . '",
        "message":      "' . $e->getMessage() . '",
        "codArquivo":"' . $obj->getCodArquivo() . '"
        }';
    }

    public function downloadArquivo($fws_, $debug = false) {
        try {
            // GET params ficam em $fwsGet_, nao em $fws_ - ler de ambos
            if (empty($fws_['codArquivo']) && !empty($_GET['codArquivo'])) {
                $fws_['codArquivo'] = addslashes($_GET['codArquivo']);
            }
            $obj = $this->carregarArquivo($fws_, $debug);
            $caminhoArquivo = $this->varDiretorio . $obj->getArquivoGerado();

            if (!file_exists($caminhoArquivo)) {
                executarLog("Download falhou - arquivo nao encontrado: " . $caminhoArquivo, "carga-pessoa", true);
                throw new Exception("Arquivo nao encontrado no servidor: " . $obj->getArquivoGerado());
            }

            $tamanho = filesize($caminhoArquivo);
            if ($tamanho === false || $tamanho == 0) {
                executarLog("Download falhou - arquivo vazio: " . $caminhoArquivo . " | size: " . $tamanho, "carga-pessoa", true);
                throw new Exception("Arquivo vazio no servidor: " . $obj->getArquivoGerado());
            }

            $nomeOriginal = $obj->getArquivoUpload() ? $obj->getArquivoUpload() : $obj->getArquivoGerado();
            $nomeOriginal = preg_replace('/[^\w\.\-]/', '_', $nomeOriginal);

            // Liberar sessao e limpar buffers antes de enviar
            session_write_close();
            while (ob_get_level()) { ob_end_clean(); }

            header("Content-Description: File Transfer");
            header("Cache-Control: private");
            header("Content-Type: application/octet-stream");
            header("Content-Disposition: attachment; filename=\"" . $nomeOriginal . "\"");
            header("Content-Transfer-Encoding: binary");
            header("Content-Length: " . $tamanho);
            header("Pragma: public");
            header("Expires: 0");
            flush();
            readfile($caminhoArquivo);
            exit();
        } catch (Exception $e) {
            executarLog("Download erro: " . $e->getMessage(), "carga-pessoa", true);
            echo '<script>alert("' . addslashes($e->getMessage()) . '"); window.history.back();</script>';
            exit();
        }
    }

    public function excluirArquivo($fws_, $debug = false) {
        try {
            $obj = $this->carregarArquivo($fws_, $debug);
            
            if(file_exists($this->varDiretorio.$obj->getArquivoGerado())) {
                if(!unlink($this->varDiretorio.$obj->getArquivoGerado())) {
                    throw new Exception("Não foi possível remover o arquivo fisicamente.");
                }
            }    
            $obj->excluir();
            
        } catch (OKException $e) {
            $ok = true;
        } catch (Exception $e) {
            $ok = false;
        }

        echo '{
        "statusMessage":"' . $ok . '",
        "message":      "' . $e->getMessage() . '",
        "codArquivo":"' . $fws_['codArquivo'] . '"
        }';
    }

    public function carregarTabelaArquivos($fws_, $debug = false) {
        try {
            $obj = new Arquivo();
            $obj->setArrayTipoArquivo($this->arrayTipoArquivo);
            $obj->carregarListaArquivoByIdEvento($debug);
            $ii = 0;
            foreach ($obj->getListaArquivo() as $objL) {
                $ii++;
                ?>  
                <tr id="<?php echo  $objL->getCodArquivo() ?>">
                    <td>
                        <div style="display:flex; align-items:center; gap:4px; flex-wrap:nowrap;">
                        <span class="excluir btn-acao btn-acao-excluir" title="Excluir"><i class="fa fa-trash"></i></span>
                        <?php
                        $classCarga = false;
                        $classAssoc = false;
                        $status = $objL->getStatusCarga();
                        if ($status == 'ASSOCIADO') {
                            $dotClass = 'dot-green';
                        } elseif ($status == 'CARREGADO') {
                            $dotClass = 'dot-blue';
                            $classAssoc = true;
                        } else {
                            $status = 'AGUARDANDO';
                            $dotClass = 'dot-orange';
                            $classCarga = true;
                        }
                        if ($classCarga) {
                            if ($objL->getTipoArquivo() == 'INSCRITOS-DINAMICO') { ?>
                        <button class="btn-mini-acao botaoMapear" type="button">Mapear</button>
                        <?php   } else { ?>
                        <button class="btn-mini-acao botaoCarga" type="button">Carregar</button>
                        <?php   }
                        } ?>
                        <?php if ($classAssoc) { ?>
                        <button class="btn-mini-acao botaoAssociar" type="button">Associar</button>
                        <?php } ?>
                        </div>
                    </td>
                    <td>
                        <span class="badge-tipo"><span class="dot <?php echo $dotClass; ?>"></span><?php echo $status; ?></span>
                        <?php if ($objL->getMessageLog()) {
                            $logCompleto = htmlspecialchars($objL->getMessageLog());
                            $logLinhas = explode("\n", $logCompleto);
                            $resumo = $logLinhas[0];
                        ?>
                        <br><small style="color:#666; font-size:11px;"><?php echo $resumo; ?></small>
                        <?php if (count($logLinhas) > 1) { ?>
                        <br><a href="#" onclick="$('#modalLogConteudo').text($(this).data('log')); $('#modalLog').modal('show'); return false;" data-log="<?php echo $logCompleto; ?>" style="font-size:11px; color:#2d6a4f;">ver log</a>
                        <?php }
                        } ?>
                    </td>
                    <td>
                        <?php echo self::rotuloTipoArquivo($objL->getTipoArquivo(), true); ?>
                        <?php echo $objL->getCodPerfil() ? '<br><small style="color:#888;">Perfil: '.$objL->getNomePerfil().'</small>' : '' ?>
                        <br><small style="color:#aaa;"><?php echo $objL->getArquivoGerado(); ?></small>
                    </td>
                    <td>
                        <?php
                        $nomeDownload = $objL->getArquivoUpload() ? $objL->getArquivoUpload() : $objL->getArquivoGerado();
                        ?>
                        <a href="<?php echo WB_URL_COMMAND ?>&c=GerenciarCargaPessoaUpload&m=downloadArquivo&codArquivo=<?php echo $objL->getCodArquivo() ?>&_submit_check=1" title="Download" download="<?php echo htmlspecialchars($nomeDownload) ?>" style="color:#2d6a4f; text-decoration:none;">
                            <i class="fa fa-download" style="margin-right:4px;"></i><?php echo htmlspecialchars($nomeDownload); ?>
                        </a>
                    </td>
                    <td><?php echo formatDateView($objL->getDataInclusao()); ?></td>
                </tr>
                <?php
            }
            ?>
                <tr class="tbl-fin-total"><td colspan="5" style="text-align:right; color:#999; font-size:12px; padding:8px 10px; border-top:1px solid #eee;">Total: <?php echo $ii ?> registro<?php echo $ii != 1 ? 's' : '' ?></td></tr>
            <?php
        } catch (Exception $e) {
            echo "<tr><td></td><td colspan=3>";
            if ($e->getMessage()) {
                echo $e->getMessage();
            } else {
                echo "Lista vazia.";
            }
            echo "</td></tr>";
        }
    }
    
    public function salvarCargaPessoaUpload($fws_, $debug = false) {
        try {
            $objAr = new Arquivo();
            $objAr->setAll($fws_,$camelCase=1);
            $this->uploadArquivo($fws_, $objAr, $debug);
            $objAr->salvar($debug);
            
        } catch (OkException $e) {
            $ok = true;
        } catch (Exception $e) {
            $ok = false;
        }
        // tipoArquivo vai no retorno para a tela encadear o proximo passo sozinha
        echo '{
        "statusMessage":"' . $ok . '",
        "message":      "' . $e->getMessage() . '",
        "codArquivo":"' . $objAr->getCodArquivo() . '",
        "tipoArquivo":"' . $objAr->getTipoArquivo() . '"
        }';
    }

    public function uploadArquivo($fws_, $obj, $debug = false) {

        if($_FILES['arquivo']['name']==''){
            throw new Exception("Arquivo não informado");
        }
        if ($_FILES['arquivo']['size'] > convertSizeToBytes($this->tamanho)){
           throw new Exception("Não é permitido arquivos maiores que ".$this->tamanho);
        }
        if ($_FILES['arquivo']['tmp_name'] == '') {
            throw new Exception("Arquivo temporário não encontrado.");
        }
        $arrayMineTypeString = createMimeTypesArray($this->tiposPermitidos,true);
        if (!in_array($_FILES['arquivo']['type'], $arrayMineTypeString['mimeTypes'])) {
            throw new Exception("Extensão de arquivo somente tipo .(".$arrayMineTypeString['typesString'].")");
        }
        $extensao = pathinfo($_FILES['arquivo']['name'], PATHINFO_EXTENSION);
        $obj->setArquivoUpload($_FILES['arquivo']['name']);
        if($fws_['tipoArquivo']!='PAGSEGURO'){
            $this->prefixo = strtolower($fws_['tipoArquivo']);
        }
        $this->varArquivo = $this->prefixo.'-'.numberPad($obj->getCodEvento(), 4).'-'.numberPad($obj->getLastId(), 4).'.'.$extensao;
        $obj->setArquivoGerado($this->varArquivo);
        
        if (!file_exists($this->varDiretorio)){
            if(mkdir($this->varDiretorio, 0777, true)){
                echo $debug? "Criou diretório<br>" : "";
            }else{
               throw new Exception("Ocorreu um erro ao criar diretório");
            }
        }
        if (move_uploaded_file($_FILES['arquivo']['tmp_name'], $this->varDiretorio.$this->varArquivo)) {
            echo $debug? "Arquivo carregado com sucesso!<br>" : "";
            return true;
        } else {
            throw new Exception("Ocorreu um erro durante upload do arquivo: " . $_FILES["file"]["error"]);
        }
    }    
    
    public function carregarInscritoCargaPessoa($fws_, $debug = false) {
        $message = '';
        $logNome = 'carga-pessoa';
        try {
            $objAr = $this->carregarArquivo($fws_,$debug);
            $objEv = $objAr->getEvento();
            if($objAr->getStatusCarga() != ''){
                throw new Exception("O arquivo já foi processado.");
            }

            // Ensalamento por inscrito le a planilha (xlsx/xls/csv) por CABECALHO, nao posicional.
            if($objAr->getTipoArquivo() == 'ENSALAMENTO_INSCRITO'){
                $this->carregarEnsalamentoInscrito($objAr, $debug);
                return;
            }

            // Presenca por inscrito tambem le por CABECALHO (nome/cpf/email/presenca).
            if($objAr->getTipoArquivo() == 'PRESENCA_INSCRITO'){
                $this->carregarPresencaInscrito($objAr, $debug);
                return;
            }

            $objConf = $objAr->getEventoConfiguracao();

            $file = new LeitorArquivo();
            $file->setNmArquivo($objAr->getArquivoGerado());
            $file->carregarArquivo($this->varDiretorio . $file->getNmArquivo(),$debug);

            $listaLinhas = $file->getListaLinhas();
            $totalLinhas = $file->getTotalLinhas();

            executarLog("Inicio carga - Arquivo: " . $objAr->getArquivoGerado() . " | Tipo: " . $objAr->getTipoArquivo() . " | Total linhas: " . $totalLinhas, $logNome, true);

            $contadorInverso = 0;
            $totalRegistros = $totalLinhas;
            $totalErros = 0;
            $totalSucesso = 0;

            //Loop em cima das linhas retornadas do arquivo
            $arrayColunas = [];
            while($totalLinhas > 0){

                $linha = $listaLinhas[$contadorInverso];
                unset($arrayColunas);  // Atribuir um array vazio para limpar o array original
                $arrayColunas = explode(';', $linha);

                try{
                    switch ($objAr->getTipoArquivo()) {
                        case 'LOGIN':
                            GerenciarCargaPessoaUploadLoginAC::carregarInscrito($arrayColunas, $objConf, $objAr, $objEv);
                        break;
                        case 'INSCRITOS':
                            GerenciarCargaPessoaUploadInscritosAC::carregarInscrito($arrayColunas, $objConf, $objAr, $objEv);
                        break;
                        case 'INSCRITOS-APTOS':
                            GerenciarCargaPessoaUploadInscritosAptosAC::carregarInscrito($arrayColunas, $objConf, $objAr, $objEv);
                        break;
                        case 'INSCRITOS-DESCONTOS':
                            GerenciarCargaPessoaUploadInscritosDescontosAC::carregarInscrito($arrayColunas, $objConf, $objAr, $objEv);
                        break;
                        case 'INSCRITOS-CERTIFICADO':
                            GerenciarCargaPessoaUploadInscritosCertificadoAC::carregarInscrito($arrayColunas, $objConf, $objAr, $objEv);
                        break;
                        case 'INSCRITOS-DINAMICO':
                            throw new Exception("Use o botao Mapear Colunas para este tipo de arquivo.");
                        break;
                        default:
                            throw new Exception("Tipo de arquivo (".$objAr->getTipoArquivo().") não reconhecido");
                        break;
                    }
                    $totalSucesso++;
                }catch (OKException $e){
                    $totalSucesso++;
                    $message .= "<br>linha " . ($contadorInverso+1) . ": " . $e->getMessage() . " [" . $arrayColunas[0] . "]";
                }catch (Exception $e){
                    if($e->getMessage()!=''){
                        $totalErros++;
                        $msgErro = "linha " . ($contadorInverso+1) . ": " . $e->getMessage() . " [" . $arrayColunas[0] . "]";
                        $message .= "<br>" . $msgErro;
                        executarLog("ERRO - " . $msgErro, $logNome, true);
                    }
                }

                $totalLinhas--;
                $contadorInverso++;
            }

            executarLog("Fim loop - Sucesso: $totalSucesso | Erros: $totalErros | Total: $totalRegistros", $logNome, true);

            // Atualiza o status do arquivo carregado, indicando que a carga já foi feita.
            $logCarga = "Processadas: $totalRegistros | Sucesso: $totalSucesso | Erros: $totalErros";
            if ($totalErros > 0 && $message != '') {
                $logCarga .= "\n" . strip_tags($message);
            }
            if ($contadorInverso == $totalRegistros){
                try {
                    $objAr->getByIdExiste();
                    $statusArquivo = 'CARREGADO';
                    $objAr->setStatusCarga($statusArquivo);
                    $objAr->setMessageLog($logCarga);
                    $objAr->atualizar();
                } catch (OKException $e) {
                    $statusArquivo = $e->getMessage();
                    $ok = true;
                } catch (Exception $e) {
                    $statusArquivo = $e->getMessage();
                    $ok = false;
                }
            }else{
                $ok = false;
                $message = 'Arquivo não carregado';
                $statusArquivo = 'Não carregado';
            }

            // Resumo para exibir ao usuario
            $resumo = "Processadas: $totalRegistros linhas. Sucesso: $totalSucesso. Erros: $totalErros.";
            if ($totalErros > 0) {
                $resumo .= $message;
            }
            $message = $resumo;

        } catch (Exception $e) {
            $statusArquivo = $e->getMessage();
            $ok = false;
            executarLog("ERRO GERAL carga - " . $e->getMessage(), $logNome, true);
            // Enviar email em producao
            if ($_SERVER['HTTP_HOST'] != 'eventos.test') {
                $msgSubject = "[" . ABREV_EVENTO . "][" . $_SERVER["SERVER_NAME"] . "] Erro Carga Pessoa";
                $msgTo = "eventos@opctec.com.br";
                $msgBody = "Erro na carga de pessoas:\n" . $e->getMessage() . "\nArquivo: " . ($objAr ? $objAr->getArquivoGerado() : 'N/A') . "\nUsuario: " . $_SESSION['_login'];
                new enviarErro($msgTo, $msgSubject, $msgBody);
            }
        }
        echo '{
        "statusMessage":"' . $ok . '",
        "message":      "' .str_replace("\n","",str_replace('"',"'",$message .'<br>'. $statusArquivo)) . '",
        "codArquivo":"' . $objAr->getCodArquivo() . '"
        }';
    }
    
    public function associarInscritoCargaPessoa($fws_, $debug = false) {
        $message = '';
        $logNome = 'carga-pessoa';
        try{
            $objAr = $this->carregarArquivo($fws_,$debug);
            if($objAr->getStatusCarga() != 'CARREGADO'){
                throw new Exception("O arquivo deve ser carregado antes de associado.");
            }

            // Carga dinamica ja gravou tudo na propria carga -- nao ha o que associar.
            // Sai antes de abrir o arquivo: o LeitorArquivo le por linha de texto e a
            // planilha pode ser .xlsx (binaria).
            if($objAr->getTipoArquivo() == 'INSCRITOS-DINAMICO'){
                $objAr->setStatusCarga('ASSOCIADO');
                try {
                    $objAr->atualizar();
                } catch (OKException $e) {
                }
                echo json_encode(array(
                    'statusMessage' => true,
                    'message' => 'Nada a associar para este tipo de carga.',
                    'codArquivo' => $objAr->getCodArquivo()
                ));
                exit();
            }

            $objConf = $objAr->getEventoConfiguracao();

            $file = new LeitorArquivo();
            $file->setNmArquivo($objAr->getArquivoGerado());
            $file->carregarArquivo($this->varDiretorio . $file->getNmArquivo(),$debug);

            $listaLinhas = $file->getListaLinhas();
            $totalLinhas = $file->getTotalLinhas();

            executarLog("Inicio associacao - Arquivo: " . $objAr->getArquivoGerado() . " | Tipo: " . $objAr->getTipoArquivo() . " | Total linhas: " . $totalLinhas, $logNome, true);

            $contadorInverso = 0;
            $totalRegistros = $totalLinhas;
            $totalErros = 0;
            $totalSucesso = 0;

            //Loop em cima das linhas retornadas do arquivo
            $arrayColunas = [];
            while($totalLinhas > 0){
                $linha = $listaLinhas[$contadorInverso];
                unset($arrayColunas);  // Atribuir um array vazio para limpar o array original
                $arrayColunas = explode(';', $linha);

                try{
                    switch ($objAr->getTipoArquivo()) {
                        case 'LOGIN':
                            GerenciarCargaPessoaUploadLoginAC::associarCargaPessoa($arrayColunas, $objConf, $objAr);
                        break;
                        case 'INSCRITOS':
                            GerenciarCargaPessoaUploadInscritosAC::associarCargaPessoa($arrayColunas, $objConf, $objAr, $objEv);
                        break;
                        case 'INSCRITOS-APTOS':
                            GerenciarCargaPessoaUploadInscritosAptosAC::associarCargaPessoa($arrayColunas, $objConf, $objAr, $objEv);
                        break;
                        case 'INSCRITOS-DESCONTOS':
                            GerenciarCargaPessoaUploadInscritosDescontosAC::associarCargaPessoa($arrayColunas, $objConf, $objAr, $objEv);
                        break;
                        case 'INSCRITOS-CERTIFICADO':
                            GerenciarCargaPessoaUploadInscritosCertificadoAC::associarCargaPessoa($arrayColunas, $objConf, $objAr, $objEv);
                        break;
                        default:
                            throw new Exception("Tipo de arquivo (".$objAr->getTipoArquivo().") não reconhecido");
                        break;
                    }
                    $totalSucesso++;
                }catch (OKException $e){
                    $totalSucesso++;
                    $message .= "<br>linha " . ($contadorInverso+1) . ": " . $e->getMessage() . " [" . $arrayColunas[0] . "]";
                }catch (Exception $e){
                    if($e->getMessage()!=''){
                        $totalErros++;
                        $msgErro = "linha " . ($contadorInverso+1) . ": " . $e->getMessage() . " [" . $arrayColunas[0] . "]";
                        $message .= "<br>" . $msgErro;
                        executarLog("ERRO associacao - " . $msgErro, $logNome, true);
                    }
                }

                $totalLinhas--;
                $contadorInverso++;
            }

            executarLog("Fim associacao - Sucesso: $totalSucesso | Erros: $totalErros | Total: $totalRegistros", $logNome, true);

            // Atualiza o status do arquivo carregado, indicando que a associacao já foi feita.
            if ($contadorInverso == $totalRegistros){
                try {
                    $statusArquivo = 'ASSOCIADO';
                    $objAr->setStatusCarga($statusArquivo);
                    $objAr->atualizar();
                } catch (OkException $e) {
                    $statusArquivo = $e->getMessage();
                    $ok = true;
                } catch (Exception $e) {
                    $statusArquivo = $e->getMessage();
                    $ok = false;
                }
            }else{
                $ok = false;
                $message = 'Arquivo não associado';
                $statusArquivo = 'Não associado';
            }

            // Resumo para exibir ao usuario
            $resumo = "Processadas: $totalRegistros linhas. Sucesso: $totalSucesso. Erros: $totalErros.";
            if ($totalErros > 0) {
                $resumo .= $message;
            }
            $message = $resumo;

        } catch (Exception $e) {
            $statusArquivo = $e->getMessage();
            $ok = false;
            executarLog("ERRO GERAL associacao - " . $e->getMessage(), $logNome, true);
            // Enviar email em producao
            if ($_SERVER['HTTP_HOST'] != 'eventos.test') {
                $msgSubject = "[" . ABREV_EVENTO . "][" . $_SERVER["SERVER_NAME"] . "] Erro Associacao Carga Pessoa";
                $msgTo = "eventos@opctec.com.br";
                $msgBody = "Erro na associacao de carga:\n" . $e->getMessage() . "\nArquivo: " . ($objAr ? $objAr->getArquivoGerado() : 'N/A') . "\nUsuario: " . $_SESSION['_login'];
                new enviarErro($msgTo, $msgSubject, $msgBody);
            }
        }
        echo '{
        "statusMessage":"' . $ok . '",
        "message":      "' .str_replace("\n","",str_replace('"',"'",$message .'<br>'. $statusArquivo)) . '",
        "codArquivo":"' . $objAr->getCodArquivo() . '"
        }';
    }

    // --- CARGA DINAMICA ---

    public static function getCamposDisponiveis() {
        return array(
            '' => '(Ignorar coluna)',
            'nome_inscrito' => 'Nome',
            'cpf' => 'CPF',
            'e_mail' => 'E-mail',
            'fone_contato' => 'Telefone',
            'fone_contato_ddd' => 'DDD',
            'fone_celular' => 'Celular',
            'rg' => 'RG',
            'numero_matricula' => 'Matricula',
            'endereco' => 'Endereco',
            'numero' => 'Numero',
            'complemento' => 'Complemento',
            'bairro' => 'Bairro',
            'cidade' => 'Cidade',
            'estado' => 'Estado/UF',
            'cep' => 'CEP',
            'sexo' => 'Sexo',
            'estado_civil' => 'Estado Civil',
            'data_nascimento' => 'Data Nascimento',
            'instituicao' => 'Instituicao',
            'profissao_atuacao' => 'Profissao',
            'nome_apelido' => 'Apelido/Cracha',
            'observacao' => 'Observacao'
        );
    }

    private static function getMapaAutoDeteccao() {
        return array(
            'nome' => 'nome_inscrito', 'name' => 'nome_inscrito',
            'cpf' => 'cpf',
            'email' => 'e_mail', 'e-mail' => 'e_mail', 'mail' => 'e_mail',
            'fone' => 'fone_contato', 'telefone' => 'fone_contato', 'phone' => 'fone_contato', 'tel' => 'fone_contato',
            'celular' => 'fone_celular',
            'ddd' => 'fone_contato_ddd',
            'rg' => 'rg', 'identidade' => 'rg',
            'matricula' => 'numero_matricula',
            'endereco' => 'endereco', 'endereço' => 'endereco', 'address' => 'endereco',
            'numero' => 'numero',
            'complemento' => 'complemento',
            'bairro' => 'bairro',
            'cidade' => 'cidade', 'city' => 'cidade',
            // "estado civil" vem ANTES de "estado": a busca parcial percorre o array na
            // ordem, e "estado civil" contem "estado" -- invertido, cairia no campo errado
            'estado civil' => 'estado_civil', 'estado_civil' => 'estado_civil', 'estadocivil' => 'estado_civil',
            // destino e a coluna 'estado', nao 'uf': o cadastro do inscrito grava em
            // 'estado' (21 mil registros) e o contrato le de la; 'uf' e residual (8 registros)
            'estado' => 'estado', 'uf' => 'estado',
            'cep' => 'cep', 'zip' => 'cep',
            'sexo' => 'sexo', 'genero' => 'sexo',
            'nascimento' => 'data_nascimento', 'data_nascimento' => 'data_nascimento',
            'instituicao' => 'instituicao', 'instituição' => 'instituicao',
            'profissao' => 'profissao_atuacao',
            'apelido' => 'nome_apelido', 'cracha' => 'nome_apelido',
            'observacao' => 'observacao', 'obs' => 'observacao'
        );
    }

    private static function detectarCampo($colunaCsv) {
        $mapa = self::getMapaAutoDeteccao();
        $chave = mb_strtolower(trim($colunaCsv), 'UTF-8');
        // Remover acentos para comparacao
        $chave = str_replace(
            array('á','à','ã','â','é','ê','í','ó','ô','õ','ú','ç'),
            array('a','a','a','a','e','e','i','o','o','o','u','c'),
            $chave
        );
        if (isset($mapa[$chave])) {
            return $mapa[$chave];
        }
        // Busca parcial
        foreach ($mapa as $palavra => $campo) {
            if (strpos($chave, $palavra) !== false) {
                return $campo;
            }
        }
        return '';
    }

    public function lerCabecalhoCarga($fws_, $debug = false) {
        try {
            if (empty($fws_['codArquivo']) && !empty($_GET['codArquivo'])) {
                $fws_['codArquivo'] = addslashes($_GET['codArquivo']);
            }
            $objAr = $this->carregarArquivo($fws_, $debug);

            if ($objAr->getTipoArquivo() != 'INSCRITOS-DINAMICO') {
                throw new Exception("Este metodo so funciona para a carga INSCRITOS-COMPLETA.");
            }

            // Le CSV/TXT e tambem XLSX/XLS pelo mesmo caminho: lerPlanilhaGrade detecta o
            // separador do CSV e usa PhpSpreadsheet na planilha. Antes era file()+str_getcsv,
            // que lia um .xlsx como bytes binarios e devolvia cabecalho ilegivel.
            $caminhoArquivo = $this->varDiretorio . $objAr->getArquivoGerado();
            if (!file_exists($caminhoArquivo)) {
                throw new Exception("Arquivo nao encontrado: " . $objAr->getArquivoGerado());
            }
            $linhas = $this->lerPlanilhaGrade($caminhoArquivo);
            if (empty($linhas)) {
                throw new Exception("Arquivo vazio.");
            }

            // Cabecalho
            $colunasCsv = array_map('trim', $linhas[0]);

            // Auto-detectar mapeamento
            $sugestoes = array();
            foreach ($colunasCsv as $col) {
                $sugestoes[] = self::detectarCampo($col);
            }

            // Preview (3 primeiras linhas de dados)
            $preview = array();
            $totalLinhas = count($linhas);
            for ($i = 1; $i < min(4, $totalLinhas); $i++) {
                $preview[] = array_map('trim', $linhas[$i]);
            }

            echo json_encode(array(
                'statusMessage' => true,
                'colunasCsv' => $colunasCsv,
                'sugestoes' => $sugestoes,
                'preview' => $preview,
                // mantido pelo JS que devolve o valor no processar; a leitura ja resolve sozinha
                'separador' => ';',
                'totalLinhas' => $totalLinhas - 1,
                'camposDisponiveis' => self::getCamposDisponiveis()
            ));
        } catch (Exception $e) {
            echo json_encode(array(
                'statusMessage' => false,
                'message' => $e->getMessage()
            ));
        }
        exit();
    }

    /**
     * Traduz uma linha da planilha em [coluna_da_tabela => valor], conforme o mapeamento
     * escolhido no modal.
     *
     * Existe uma implementacao so, usada pela critica e pela gravacao: se as duas lessem
     * a linha de um jeito diferente, a critica poderia apontar um conflito que a carga
     * nao veria (ou o contrario).
     */
    private function montarValoresLinha($arrayColunas, $mapeamento) {
        $valores = array();
        for ($c = 0; $c < count($mapeamento); $c++) {
            $campo = $mapeamento[$c];
            if (empty($campo)) continue;   // coluna ignorada no mapeamento
            $valor = isset($arrayColunas[$c]) ? trim($arrayColunas[$c]) : '';
            if ($valor === '') continue;

            if ($campo == 'cpf') {
                $valor = formatCpfInsert(limparCpf($valor));
            } elseif ($campo == 'e_mail' && !validarEmail($valor)) {
                throw new Exception("E-mail invalido: " . $valor);
            }
            $valores[$campo] = $valor;
        }
        return $valores;
    }

    /**
     * Critica ANTES de gravar: quem do arquivo ja esta inscrito no evento (mesmo CPF),
     * quem aparece repetido dentro do proprio arquivo e quais linhas tem problema.
     *
     * Nao grava nada. O usuario e quem decide: substituir os dados dos existentes ou
     * sair, tirar as linhas do arquivo e subir de novo.
     */
    public function analisarConflitosCarga($fws_, $debug = false) {
        try {
            if (empty($fws_['codArquivo']) && !empty($_GET['codArquivo'])) {
                $fws_['codArquivo'] = addslashes($_GET['codArquivo']);
            }
            $objAr = $this->carregarArquivo($fws_, $debug);

            if ($objAr->getTipoArquivo() != 'INSCRITOS-DINAMICO') {
                throw new Exception("Este metodo so funciona para a carga INSCRITOS-COMPLETA.");
            }
            $mapeamento = isset($fws_['mapeamento']) ? $fws_['mapeamento'] : array();
            if (empty($mapeamento) || !is_array($mapeamento)) {
                throw new Exception("Mapeamento de colunas nao informado.");
            }

            $caminhoArquivo = $this->varDiretorio . $objAr->getArquivoGerado();
            if (!file_exists($caminhoArquivo)) {
                throw new Exception("Arquivo nao encontrado: " . $objAr->getArquivoGerado());
            }
            $linhas = $this->lerPlanilhaGrade($caminhoArquivo);
            $totalLinhas = count($linhas);

            $temColunaCpf = in_array('cpf', $mapeamento);
            $dadosLinha  = array();   // numero da linha => valores
            $semCpf      = 0;
            $problemas   = array();
            $vistosNoArquivo = array();
            $repetidosNoArquivo = array();

            for ($i = 1; $i < $totalLinhas; $i++) {
                try {
                    $valores = $this->montarValoresLinha(array_map('trim', $linhas[$i]), $mapeamento);
                } catch (Exception $e) {
                    $problemas[] = array('linha' => $i, 'mensagem' => $e->getMessage());
                    continue;
                }
                if (empty($valores)) continue;   // linha em branco

                $dadosLinha[$i] = $valores;
                $cpf = isset($valores['cpf']) ? $valores['cpf'] : '';
                if ($cpf === '') {
                    $semCpf++;
                    continue;
                }
                if (isset($vistosNoArquivo[$cpf])) {
                    $repetidosNoArquivo[] = array(
                        'linha'      => $i,
                        'linhaAnterior' => $vistosNoArquivo[$cpf],
                        'cpf'        => $cpf,
                        'nome'       => isset($valores['nome_inscrito']) ? $valores['nome_inscrito'] : '',
                    );
                } else {
                    $vistosNoArquivo[$cpf] = $i;
                }
            }

            $objI = new Inscrito();
            $objI->setCodEvento($objAr->getCodEvento());
            $jaInscritos = $temColunaCpf ? $objI->carregarConflitosPorCpf(array_keys($vistosNoArquivo), $debug) : array();

            // cruza o que veio do banco com a linha do arquivo em que o CPF apareceu
            $conflitos = array();
            foreach ($jaInscritos as $cpf => $existente) {
                $linha = isset($vistosNoArquivo[$cpf]) ? $vistosNoArquivo[$cpf] : '';
                $conflitos[] = array(
                    'linha'          => $linha,
                    'cpf'            => formatCpfCnpjView($cpf),
                    'nomeArquivo'    => ($linha && isset($dadosLinha[$linha]['nome_inscrito'])) ? $dadosLinha[$linha]['nome_inscrito'] : '',
                    'codInscrito'    => $existente['codInscrito'],
                    'nomeSistema'    => $existente['nomeInscrito'],
                    'eMailSistema'   => $existente['eMail'],
                    'statusProcesso' => $existente['statusProcesso'],
                    'inscritoDesde'  => formatDateView($existente['datIncl']),
                );
            }

            echo json_encode(array(
                'statusMessage'      => true,
                'temColunaCpf'       => $temColunaCpf,
                'totalRegistros'     => count($dadosLinha),
                'semCpf'             => $semCpf,
                'conflitos'          => $conflitos,
                'repetidosNoArquivo' => $repetidosNoArquivo,
                'problemas'          => $problemas,
            ));
        } catch (Exception $e) {
            echo json_encode(array(
                'statusMessage' => false,
                'message' => $e->getMessage()
            ));
        }
        exit();
    }

    public function processarCargaDinamica($fws_, $debug = false) {
        $message = '';
        $logNome = 'carga-pessoa';
        try {
            if (empty($fws_['codArquivo']) && !empty($_GET['codArquivo'])) {
                $fws_['codArquivo'] = addslashes($_GET['codArquivo']);
            }
            $objAr = $this->carregarArquivo($fws_, $debug);
            $objEv = $objAr->getEvento();

            if ($objAr->getStatusCarga() != '') {
                throw new Exception("O arquivo ja foi processado.");
            }
            if ($objAr->getTipoArquivo() != 'INSCRITOS-DINAMICO') {
                throw new Exception("Este metodo so funciona para a carga INSCRITOS-COMPLETA.");
            }

            // Receber mapeamento do frontend
            $mapeamento = isset($fws_['mapeamento']) ? $fws_['mapeamento'] : array();
            if (empty($mapeamento) || !is_array($mapeamento)) {
                throw new Exception("Mapeamento de colunas nao informado.");
            }

            // Verificar se tem pelo menos nome ou cpf
            if (!in_array('nome_inscrito', $mapeamento) && !in_array('cpf', $mapeamento)) {
                throw new Exception("O mapeamento deve incluir pelo menos Nome ou CPF.");
            }

            // Mesma leitura do lerCabecalhoCarga: CSV/TXT ou XLSX/XLS. O separador nao vem
            // mais do frontend -- lerPlanilhaGrade detecta o do CSV e a planilha nem usa.
            $caminhoArquivo = $this->varDiretorio . $objAr->getArquivoGerado();
            if (!file_exists($caminhoArquivo)) {
                throw new Exception("Arquivo nao encontrado: " . $objAr->getArquivoGerado());
            }
            $linhas = $this->lerPlanilhaGrade($caminhoArquivo);
            $totalLinhas = count($linhas);

            // ---------------------------------------------------------------------
            // Validacao ANTES de gravar. Arquivo com linha invalida ou CPF repetido
            // nao entra pela metade: aqui desfazer e de graca, depois do INSERT nao.
            // ---------------------------------------------------------------------
            $problemas = array();
            $vistos = array();
            $cpfsArquivo = array();
            for ($i = 1; $i < $totalLinhas; $i++) {
                try {
                    $v = $this->montarValoresLinha(array_map('trim', $linhas[$i]), $mapeamento);
                } catch (Exception $e) {
                    $problemas[] = "linha " . $i . ": " . $e->getMessage();
                    continue;
                }
                if (empty($v)) continue;   // linha em branco
                if (empty($v['cpf'])) continue;

                if (isset($vistos[$v['cpf']])) {
                    $problemas[] = "linha " . $i . ": CPF repetido na linha " . $vistos[$v['cpf']] . " do proprio arquivo";
                } else {
                    $vistos[$v['cpf']] = $i;
                    $cpfsArquivo[] = $v['cpf'];
                }
            }
            if (!empty($problemas)) {
                throw new Exception("Arquivo com " . count($problemas) . " problema(s) -- nada foi carregado. Corrija e envie de novo:<br>" . implode("<br>", $problemas));
            }

            // Conflito de CPF: quem ja esta inscrito no evento. So passa daqui com decisao
            // explicita do usuario (a critica roda antes, no modal). Sem decisao, nada e
            // gravado -- caso contrario a carga criaria um segundo cadastro para a mesma
            // pessoa sem ninguem perceber.
            $acaoConflito = isset($fws_['acaoConflito']) ? strtoupper(trim($fws_['acaoConflito'])) : '';
            $conflitos = array();
            if (in_array('cpf', $mapeamento)) {
                $objCon = new Inscrito();
                $objCon->setCodEvento($objAr->getCodEvento());
                $conflitos = $objCon->carregarConflitosPorCpf($cpfsArquivo, $debug);

                if (!empty($conflitos) && $acaoConflito != 'SUBSTITUIR') {
                    throw new Exception(count($conflitos) . " CPF(s) do arquivo ja tem inscrito neste evento. Revise a critica e escolha o que fazer antes de carregar.");
                }
            }

            executarLog("Inicio carga dinamica - Arquivo: " . $objAr->getArquivoGerado() . " | Mapeamento: " . implode(',', $mapeamento) . " | Total linhas: " . $totalLinhas . " | Conflitos: " . count($conflitos) . " | Acao: " . ($acaoConflito ?: 'nenhuma'), $logNome, true);

            // Pular cabecalho (linha 0)
            $contadorInverso = 1;
            $totalRegistros = $totalLinhas - 1;
            $totalErros = 0;
            $totalSucesso = 0;
            $totalAtualizados = 0;

            // Tudo o que era previsivel ja foi barrado acima. Daqui para a frente, erro e
            // acidente (banco fora, deadlock) -- e ai a carga inteira volta atras, em vez
            // de deixar meia planilha gravada. Conexao e singleton (ConexaoDB), entao a
            // transacao vale para todos os objetos criados dentro do loop.
            ConexaoDB::getInstance();
            $conex = ConexaoDB::getConexaoDB();
            $conex->BeginTrans();

            while ($contadorInverso < $totalLinhas) {
                $arrayColunas = array_map('trim', $linhas[$contadorInverso]);

                try {
                    $objI = new Inscrito();
                    $objI->setCodEvento($objAr->getCodEvento());
                    $objI->setCodArquivo($objAr->getCodArquivo());
                    $objI->setCodInscrito(NULL);
                    $objI->setCodPerfil($objAr->getCodPerfil());
                    $objI->setStatus('ATIVADA');

                    $valores = $this->montarValoresLinha($arrayColunas, $mapeamento);
                    if (empty($valores)) {
                        throw new Exception("Linha vazia");
                    }

                    // Ja inscrito com este CPF: substitui SO os campos que vieram na
                    // planilha, em vez de criar um segundo cadastro para a mesma pessoa.
                    $cpfLinha = isset($valores['cpf']) ? $valores['cpf'] : '';
                    if ($cpfLinha !== '' && isset($conflitos[$cpfLinha])) {
                        $objI->atualizarCamposCarga($conflitos[$cpfLinha]['codInscrito'], $valores, $debug);
                        $totalAtualizados++;
                        $contadorInverso++;
                        continue;
                    }

                    foreach ($valores as $campo => $valor) {
                        if ($campo == 'e_mail') {
                            $objI->setEMail($valor);
                            continue;
                        }
                        // setter generico via camelCase
                        $setter = 'set' . str_replace('_', '', ucwords($campo, '_'));
                        $objI->$setter($valor);
                    }

                    /* Sem origem_cadastro o inscrito nao consegue logar: a consulta
                       de login exige 'INSCRICAO' (InscritoDAO::efetuarLogin), e o
                       salvarCarga() usa validatorObjCarga(), que nao preenche o campo.
                       So define se a propria planilha nao tiver mapeado a coluna. */
                    if (!$objI->getOrigemCadastro()) {
                        $objI->setOrigemCadastro('INSCRICAO');
                    }

                    $objI->salvarCarga();
                    $totalSucesso++;

                } catch (OKException $e) {
                    $totalSucesso++;
                } catch (Exception $e) {
                    if ($e->getMessage() != '') {
                        $totalErros++;
                        $msgErro = "linha " . $contadorInverso . ": " . $e->getMessage();
                        $message .= "<br>" . $msgErro;
                        executarLog("ERRO carga dinamica - " . $msgErro, $logNome, true);
                    }
                }

                $contadorInverso++;
            }

            executarLog("Fim carga dinamica - Novos: $totalSucesso | Atualizados: $totalAtualizados | Erros: $totalErros | Total: $totalRegistros", $logNome, true);

            // Erro aqui e inesperado (a validacao previa cobre o previsivel): desfaz tudo
            if ($totalErros > 0) {
                $conex->RollbackTrans();
                executarLog("ROLLBACK carga dinamica - " . $totalErros . " erro(s), nada foi gravado", $logNome, true);
                throw new Exception("A carga foi desfeita: " . $totalErros . " linha(s) falharam ao gravar e nada foi mantido." . $message);
            }
            $conex->CommitTrans();

            // Atualizar status e salvar log na tabela
            $logCarga = "Processadas: $totalRegistros | Novos: $totalSucesso | Atualizados: $totalAtualizados | Erros: $totalErros";
            if ($totalErros > 0 && $message != '') {
                $logCarga .= "\n" . strip_tags($message);
            }
            try {
                $objAr->getByIdExiste();
                $objAr->setStatusCarga('CARREGADO');
                $objAr->setMessageLog($logCarga);
                $objAr->atualizar();
                $ok = true;
                $statusArquivo = 'CARREGADO';
            } catch (OKException $e) {
                $statusArquivo = $e->getMessage();
                $ok = true;
            } catch (Exception $e) {
                $statusArquivo = $e->getMessage();
                $ok = false;
            }

            $resumo = "Processadas: $totalRegistros linhas. Novos inscritos: $totalSucesso.";
            $resumo .= $totalAtualizados > 0 ? " Substituidos (CPF ja inscrito): $totalAtualizados." : "";
            $resumo .= " Erros: $totalErros.";
            if ($totalErros > 0) {
                $resumo .= $message;
            }
            $message = $resumo;

        } catch (Exception $e) {
            // rede de seguranca: nenhuma saida deste metodo pode deixar transacao aberta
            if (isset($conex) && $conex->transCnt > 0) {
                $conex->RollbackTrans();
            }
            $statusArquivo = $e->getMessage();
            $ok = false;
            executarLog("ERRO GERAL carga dinamica - " . $e->getMessage(), $logNome, true);
            if ($_SERVER['HTTP_HOST'] != 'eventos.test') {
                $msgSubject = "[" . ABREV_EVENTO . "][" . $_SERVER["SERVER_NAME"] . "] Erro Carga Dinamica";
                $msgTo = "eventos@opctec.com.br";
                $msgBody = "Erro na carga dinamica:\n" . $e->getMessage() . "\nArquivo: " . ($objAr ? $objAr->getArquivoGerado() : 'N/A') . "\nUsuario: " . $_SESSION['_login'];
                new enviarErro($msgTo, $msgSubject, $msgBody);
            }
        }
        echo json_encode(array(
            'statusMessage' => $ok,
            'message' => $message . '<br>' . $statusArquivo,
            'codArquivo' => $objAr ? $objAr->getCodArquivo() : ''
        ));
        exit();
    }

    // --- ENSALAMENTO POR INSCRITO ---

    private static $mapaCabecalhoEnsalamento = array(
        'codinscrito' => 'cod_inscrito', 'cod' => 'cod_inscrito', 'codigo' => 'cod_inscrito',
        'id' => 'cod_inscrito', 'idinscrito' => 'cod_inscrito', 'inscrito' => 'cod_inscrito',
        'cpf' => 'cpf',
        'email' => 'email', 'mail' => 'email',
        'sala' => 'sala',
        'bloco' => 'bloco',
        'dia' => 'dia', 'data' => 'dia',
        'hora' => 'hora', 'horario' => 'hora',
        'obs' => 'obs', 'observacao' => 'obs', 'observacoes' => 'obs',
    );

    private function normalizarCabecalhoEnsalamento($col){
        $chave = mb_strtolower(trim($col), 'UTF-8');
        $chave = str_replace(
            array('á','à','ã','â','é','ê','í','ó','ô','õ','ú','ç'),
            array('a','a','a','a','e','e','i','o','o','o','u','c'),
            $chave
        );
        // remove tudo que nao for letra/numero (espacos, _, -, etc)
        $chave = preg_replace('/[^a-z0-9]/', '', $chave);
        return isset(self::$mapaCabecalhoEnsalamento[$chave]) ? self::$mapaCabecalhoEnsalamento[$chave] : '';
    }

    /**
     * Le a planilha de ensalamento (xlsx/xls via PhpSpreadsheet, ou csv) mapeando as colunas
     * pelo CABECALHO e grava cada linha em tab_ensalamento_inscrito.
     */
    public function carregarEnsalamentoInscrito($objAr, $debug = false) {
        $message = '';
        $logNome = 'carga-pessoa';
        $ok = false;
        $statusArquivo = '';
        try {
            $caminhoArquivo = $this->varDiretorio . $objAr->getArquivoGerado();
            if (!file_exists($caminhoArquivo)) {
                throw new Exception("Arquivo nao encontrado: " . $objAr->getArquivoGerado());
            }

            $linhas = $this->lerPlanilhaGrade($caminhoArquivo);
            if (count($linhas) < 2) {
                throw new Exception("Planilha sem linhas de dados.");
            }

            // Primeira linha = cabecalho -> mapeia indice de coluna para campo canonico
            $cabecalho = array_shift($linhas);
            $mapa = array();
            foreach ($cabecalho as $idx => $col) {
                $campo = $this->normalizarCabecalhoEnsalamento($col);
                if ($campo !== '') {
                    $mapa[$idx] = $campo;
                }
            }
            if (!in_array('cod_inscrito', $mapa) && !in_array('cpf', $mapa) && !in_array('email', $mapa)) {
                throw new Exception("O cabecalho precisa ter ao menos uma coluna: cod_inscrito, cpf ou email.");
            }

            executarLog("Inicio carga ensalamento - Arquivo: " . $objAr->getArquivoGerado() . " | Total linhas: " . count($linhas), $logNome, true);

            $totalRegistros = count($linhas);
            $totalErros = 0;
            $totalSucesso = 0;
            $numLinha = 1; // 1 = cabecalho ja removido; dados comecam na linha 2 da planilha

            foreach ($linhas as $linhaArr) {
                $numLinha++;
                $assoc = array();
                foreach ($mapa as $idx => $campo) {
                    $assoc[$campo] = isset($linhaArr[$idx]) ? $linhaArr[$idx] : '';
                }
                try {
                    GerenciarCargaPessoaUploadEnsalamentoInscritoAC::carregarLinha($assoc, $objAr);
                    $totalSucesso++;
                } catch (OKException $e) {
                    // OKException com mensagem vazia = linha em branco ignorada; com mensagem = update ok
                    $totalSucesso++;
                } catch (Exception $e) {
                    if ($e->getMessage() != '') {
                        $totalErros++;
                        // htmlspecialchars: a mensagem embute valores brutos da planilha (cpf/email);
                        // o <br> fica fora para preservar a separacao das linhas no #resultadoCarga.
                        $msgErro = "linha " . $numLinha . ": " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
                        $message .= "<br>" . $msgErro;
                        executarLog("ERRO carga ensalamento - " . strip_tags($e->getMessage()), $logNome, true);
                    }
                }
            }

            executarLog("Fim carga ensalamento - Sucesso: $totalSucesso | Erros: $totalErros | Total: $totalRegistros", $logNome, true);

            $logCarga = "Processadas: $totalRegistros | Sucesso: $totalSucesso | Erros: $totalErros";
            if ($totalErros > 0 && $message != '') {
                $logCarga .= "\n" . strip_tags($message);
            }
            // Ensalamento nao tem passo de associacao -> ja marca como ASSOCIADO (concluido).
            // atualizar() lanca OKException no sucesso: isolar em try proprio para nao pular o resumo.
            try {
                $objAr->getByIdExiste();
                $objAr->setStatusCarga('ASSOCIADO');
                $objAr->setMessageLog($logCarga);
                $objAr->atualizar();
                $ok = true;
                $statusArquivo = 'Concluído.';
            } catch (OKException $e) {
                $ok = true;
                $statusArquivo = $e->getMessage();
            } catch (Exception $e) {
                $ok = false;
                $statusArquivo = $e->getMessage();
            }

            $resumo = "Processadas: $totalRegistros linhas. Sucesso: $totalSucesso. Erros: $totalErros.";
            if ($totalErros > 0) {
                $resumo .= $message;
            }
            $message = $resumo;

        } catch (OKException $e) {
            $ok = true;
            $statusArquivo = $e->getMessage();
        } catch (Exception $e) {
            $ok = false;
            $statusArquivo = $e->getMessage();
            executarLog("ERRO GERAL carga ensalamento - " . $e->getMessage(), $logNome, true);
            if ($_SERVER['HTTP_HOST'] != 'eventos.test') {
                $msgSubject = "[" . ABREV_EVENTO . "][" . $_SERVER["SERVER_NAME"] . "] Erro Carga Ensalamento";
                $msgTo = "eventos@opctec.com.br";
                $msgBody = "Erro na carga de ensalamento:\n" . $e->getMessage() . "\nArquivo: " . ($objAr ? $objAr->getArquivoGerado() : 'N/A') . "\nUsuario: " . $_SESSION['_login'];
                new enviarErro($msgTo, $msgSubject, $msgBody);
            }
        }
        echo '{
        "statusMessage":"' . $ok . '",
        "message":      "' . str_replace("\n","",str_replace('"',"'",$message .'<br>'. $statusArquivo)) . '",
        "codArquivo":"' . ($objAr ? $objAr->getCodArquivo() : '') . '"
        }';
    }

    // --- PRESENCA POR INSCRITO ---

    private static $mapaCabecalhoPresenca = array(
        'codinscrito' => 'cod_inscrito', 'cod' => 'cod_inscrito', 'codigo' => 'cod_inscrito',
        'id' => 'cod_inscrito', 'idinscrito' => 'cod_inscrito', 'inscrito' => 'cod_inscrito',
        'nome' => 'nome', 'nomeinscrito' => 'nome', 'nomecompleto' => 'nome',
        'cpf' => 'cpf',
        'email' => 'email', 'mail' => 'email', 'emailinscrito' => 'email',
        'presenca' => 'presenca', 'presente' => 'presenca', 'presencainscrito' => 'presenca',
        'flgpresenca' => 'presenca', 'compareceu' => 'presenca', 'frequencia' => 'presenca',
    );

    private function normalizarCabecalhoPresenca($col){
        $chave = mb_strtolower(trim($col), 'UTF-8');
        $chave = str_replace(
            array('á','à','ã','â','é','ê','í','ó','ô','õ','ú','ç'),
            array('a','a','a','a','e','e','i','o','o','o','u','c'),
            $chave
        );
        // remove tudo que nao for letra/numero (espacos, _, -, etc)
        $chave = preg_replace('/[^a-z0-9]/', '', $chave);
        return isset(self::$mapaCabecalhoPresenca[$chave]) ? self::$mapaCabecalhoPresenca[$chave] : '';
    }

    /**
     * Le a planilha de presenca (xlsx/xls via PhpSpreadsheet, ou csv) mapeando as colunas
     * pelo CABECALHO e grava flg_presenca_inscrito de cada inscrito em tab_inscritos.
     */
    public function carregarPresencaInscrito($objAr, $debug = false) {
        $message = '';
        $logNome = 'carga-pessoa';
        $ok = false;
        $statusArquivo = '';
        try {
            $caminhoArquivo = $this->varDiretorio . $objAr->getArquivoGerado();
            if (!file_exists($caminhoArquivo)) {
                throw new Exception("Arquivo nao encontrado: " . $objAr->getArquivoGerado());
            }

            $linhas = $this->lerPlanilhaGrade($caminhoArquivo);
            if (count($linhas) < 2) {
                throw new Exception("Planilha sem linhas de dados.");
            }

            // Primeira linha = cabecalho -> mapeia indice de coluna para campo canonico
            $cabecalho = array_shift($linhas);
            $mapa = array();
            foreach ($cabecalho as $idx => $col) {
                $campo = $this->normalizarCabecalhoPresenca($col);
                if ($campo !== '') {
                    $mapa[$idx] = $campo;
                }
            }
            if (!in_array('cod_inscrito', $mapa) && !in_array('cpf', $mapa) && !in_array('email', $mapa)) {
                throw new Exception("O cabecalho precisa ter ao menos uma coluna: cod_inscrito, cpf ou email.");
            }
            if (!in_array('presenca', $mapa)) {
                throw new Exception("O cabecalho precisa ter a coluna presenca.");
            }

            executarLog("Inicio carga presenca - Arquivo: " . $objAr->getArquivoGerado() . " | Total linhas: " . count($linhas), $logNome, true);

            $totalRegistros = count($linhas);
            $totalErros = 0;
            $totalSucesso = 0;
            $numLinha = 1; // 1 = cabecalho ja removido; dados comecam na linha 2 da planilha

            foreach ($linhas as $linhaArr) {
                $numLinha++;
                $assoc = array();
                foreach ($mapa as $idx => $campo) {
                    $assoc[$campo] = isset($linhaArr[$idx]) ? $linhaArr[$idx] : '';
                }
                try {
                    GerenciarCargaPessoaUploadPresencaInscritoAC::carregarLinha($assoc, $objAr);
                    $totalSucesso++;
                } catch (OKException $e) {
                    // OKException com mensagem vazia = linha em branco ignorada
                    $totalSucesso++;
                } catch (Exception $e) {
                    if ($e->getMessage() != '') {
                        $totalErros++;
                        // htmlspecialchars: a mensagem embute valores brutos da planilha (cpf/email);
                        // o <br> fica fora para preservar a separacao das linhas no #resultadoCarga.
                        $msgErro = "linha " . $numLinha . ": " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
                        $message .= "<br>" . $msgErro;
                        executarLog("ERRO carga presenca - " . strip_tags($e->getMessage()), $logNome, true);
                    }
                }
            }

            executarLog("Fim carga presenca - Sucesso: $totalSucesso | Erros: $totalErros | Total: $totalRegistros", $logNome, true);

            $logCarga = "Processadas: $totalRegistros | Sucesso: $totalSucesso | Erros: $totalErros";
            if ($totalErros > 0 && $message != '') {
                $logCarga .= "\n" . strip_tags($message);
            }
            // Presenca nao tem passo de associacao -> ja marca como ASSOCIADO (concluido).
            try {
                $objAr->getByIdExiste();
                $objAr->setStatusCarga('ASSOCIADO');
                $objAr->setMessageLog($logCarga);
                $objAr->atualizar();
                $ok = true;
                $statusArquivo = 'Concluído.';
            } catch (OKException $e) {
                $ok = true;
                $statusArquivo = $e->getMessage();
            } catch (Exception $e) {
                $ok = false;
                $statusArquivo = $e->getMessage();
            }

            $resumo = "Processadas: $totalRegistros linhas. Sucesso: $totalSucesso. Erros: $totalErros.";
            if ($totalErros > 0) {
                $resumo .= $message;
            }
            $message = $resumo;

        } catch (OKException $e) {
            $ok = true;
            $statusArquivo = $e->getMessage();
        } catch (Exception $e) {
            $ok = false;
            $statusArquivo = $e->getMessage();
            executarLog("ERRO GERAL carga presenca - " . $e->getMessage(), $logNome, true);
            if ($_SERVER['HTTP_HOST'] != 'eventos.test') {
                $msgSubject = "[" . ABREV_EVENTO . "][" . $_SERVER["SERVER_NAME"] . "] Erro Carga Presenca";
                $msgTo = "eventos@opctec.com.br";
                $msgBody = "Erro na carga de presenca:\n" . $e->getMessage() . "\nArquivo: " . ($objAr ? $objAr->getArquivoGerado() : 'N/A') . "\nUsuario: " . $_SESSION['_login'];
                new enviarErro($msgTo, $msgSubject, $msgBody);
            }
        }
        echo '{
        "statusMessage":"' . $ok . '",
        "message":      "' . str_replace("\n","",str_replace('"',"'",$message .'<br>'. $statusArquivo)) . '",
        "codArquivo":"' . ($objAr ? $objAr->getCodArquivo() : '') . '"
        }';
    }

    /**
     * Le uma planilha (xlsx/xls via PhpSpreadsheet ou csv) devolvendo um array de linhas,
     * cada linha um array indexado por coluna. Datas vem como texto formatado.
     */
    private function lerPlanilhaGrade($caminhoArquivo) {
        $extensao = strtolower(pathinfo($caminhoArquivo, PATHINFO_EXTENSION));

        if ($extensao == 'csv' || $extensao == 'txt') {
            $conteudo = file($caminhoArquivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if (empty($conteudo)) {
                return array();
            }
            $separador = (substr_count($conteudo[0], ',') > substr_count($conteudo[0], ';')) ? ',' : ';';
            $linhas = array();
            foreach ($conteudo as $l) {
                $linhas[] = array_map('trim', str_getcsv(trim($l), $separador, '"'));
            }
            return $linhas;
        }

        // xlsx / xls -> PhpSpreadsheet (mesma lib isolada usada no resto do sistema)
        $autoload = dirname(__FILE__) . '/../lib/spreadsheet/vendor/autoload.php';
        if (!file_exists($autoload)) {
            throw new Exception("Biblioteca PhpSpreadsheet nao encontrada no servidor.");
        }
        require_once($autoload);

        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader(
            \PhpOffice\PhpSpreadsheet\IOFactory::identify($caminhoArquivo)
        );
        $sheet = $reader->load($caminhoArquivo)->getActiveSheet();
        // toArray(null, calcularFormulas, formatarDados, retornarRef) -> datas viram texto formatado
        $grade = $sheet->toArray(null, true, true, false);

        // remove linhas totalmente vazias
        $linhas = array();
        foreach ($grade as $linhaArr) {
            $temAlgo = false;
            foreach ($linhaArr as $v) {
                if (trim((string)$v) !== '') { $temAlgo = true; break; }
            }
            if ($temAlgo) {
                $linhas[] = array_map(function($v){ return trim((string)$v); }, $linhaArr);
            }
        }
        return $linhas;
    }

    /**
     * Gera e faz download do modelo .xlsx da carga de ensalamento por inscrito.
     */
    public function baixarModeloEnsalamento($fws_, $debug = false) {
        try {
            $autoload = dirname(__FILE__) . '/../lib/spreadsheet/vendor/autoload.php';
            if (!file_exists($autoload)) {
                throw new Exception("Biblioteca PhpSpreadsheet nao encontrada no servidor.");
            }
            require_once($autoload);

            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

            // Aba 1: planilha de preenchimento
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Ensalamento');

            $cabecalhos = array('cod_inscrito', 'cpf', 'email', 'sala', 'bloco', 'dia', 'hora', 'obs');
            foreach ($cabecalhos as $i => $titulo) {
                $col = chr(ord('A') + $i);
                $sheet->setCellValue($col . '1', $titulo);
            }
            $larguras = array('A' => 14, 'B' => 18, 'C' => 30, 'D' => 16, 'E' => 14, 'F' => 14, 'G' => 12, 'H' => 30);
            foreach ($larguras as $col => $w) {
                $sheet->getColumnDimension($col)->setWidth($w);
            }
            $sheet->getStyle('A1:H1')->applyFromArray(array(
                'font' => array('bold' => true, 'color' => array('rgb' => 'FFFFFF')),
                'fill' => array(
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => array('rgb' => '2E5C8A')
                ),
                'alignment' => array('vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER),
            ));
            $sheet->getRowDimension(1)->setRowHeight(22);
            $sheet->freezePane('A2');
            // Forca a coluna cpf como texto para nao perder zeros/formatacao
            $sheet->getStyle('B2:B999')->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);

            // Linha de exemplo (o usuario substitui pelos dados reais)
            $exemplo = array('', '123.456.789-00', 'fulano@email.com', 'Sala 12', 'Bloco B', '10/08/2026', '14h00', '');
            foreach ($exemplo as $i => $valor) {
                $col = chr(ord('A') + $i);
                $sheet->setCellValueExplicit($col . '2', $valor, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            }
            $sheet->getStyle('A2:H2')->applyFromArray(array(
                'font' => array('italic' => true, 'color' => array('rgb' => '999999')),
            ));

            // Aba 2: instrucoes
            $inst = $spreadsheet->createSheet();
            $inst->setTitle('Como preencher');
            $textos = array(
                'Como preencher a carga de ensalamento',
                '',
                '1) Identifique o inscrito por UMA das colunas: cod_inscrito, cpf ou email.',
                '   - cod_inscrito e o mais seguro (casamento exato).',
                '   - Se nao souber o cod_inscrito, preencha cpf; se nao tiver cpf, preencha email.',
                '2) Informe a alocacao nas colunas: sala, bloco, dia, hora, obs (ao menos uma).',
                '   - dia e hora sao texto livre (ex.: 10/08/2026 e 14h00).',
                '3) Apague a linha de exemplo (em cinza) antes de enviar.',
                '4) Salve como .xlsx (ou .csv separado por ponto e virgula) e envie na tela de carga',
                '   escolhendo a origem ENSALAMENTO_INSCRITO.',
                '',
                'Obs.: o inscrito precisa existir no evento com status ATIVADA.',
                'Reenviar a planilha atualiza o ensalamento ja existente do inscrito.',
            );
            $l = 1;
            foreach ($textos as $t) {
                $inst->setCellValue('A' . $l, $t);
                $l++;
            }
            $inst->getColumnDimension('A')->setWidth(90);
            $inst->getStyle('A1')->applyFromArray(array('font' => array('bold' => true, 'size' => 13)));

            $spreadsheet->setActiveSheetIndex(0);

            $nomeArquivo = 'modelo_ensalamento_inscrito.xlsx';
            while (ob_get_level() > 0) { ob_end_clean(); }
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $nomeArquivo . '"');
            header('Cache-Control: no-cache, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);
            exit();
        } catch (Exception $e) {
            while (ob_get_level() > 0) { ob_end_clean(); }
            header('Content-Type: text/html; charset=UTF-8');
            echo '<script>alert("' . addslashes($e->getMessage()) . '"); window.history.back();</script>';
            exit();
        }
    }

    /**
     * Gera e faz download do modelo .xlsx da carga de presenca por inscrito.
     */
    public function baixarModeloPresenca($fws_, $debug = false) {
        try {
            $autoload = dirname(__FILE__) . '/../lib/spreadsheet/vendor/autoload.php';
            if (!file_exists($autoload)) {
                throw new Exception("Biblioteca PhpSpreadsheet nao encontrada no servidor.");
            }
            require_once($autoload);

            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

            // Aba 1: planilha de preenchimento
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Presenca');

            $cabecalhos = array('cod_inscrito', 'nome', 'cpf', 'email', 'presenca');
            foreach ($cabecalhos as $i => $titulo) {
                $col = chr(ord('A') + $i);
                $sheet->setCellValue($col . '1', $titulo);
            }
            $larguras = array('A' => 14, 'B' => 30, 'C' => 18, 'D' => 30, 'E' => 12);
            foreach ($larguras as $col => $w) {
                $sheet->getColumnDimension($col)->setWidth($w);
            }
            $sheet->getStyle('A1:E1')->applyFromArray(array(
                'font' => array('bold' => true, 'color' => array('rgb' => 'FFFFFF')),
                'fill' => array(
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => array('rgb' => '2E5C8A')
                ),
                'alignment' => array('vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER),
            ));
            $sheet->getRowDimension(1)->setRowHeight(22);
            $sheet->freezePane('A2');
            // Forca a coluna cpf como texto para nao perder zeros/formatacao
            $sheet->getStyle('C2:C999')->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);

            // Linha de exemplo (o usuario substitui pelos dados reais)
            $exemplo = array('', 'Fulano de Tal', '123.456.789-00', 'fulano@email.com', '1');
            foreach ($exemplo as $i => $valor) {
                $col = chr(ord('A') + $i);
                $sheet->setCellValueExplicit($col . '2', $valor, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            }
            $sheet->getStyle('A2:E2')->applyFromArray(array(
                'font' => array('italic' => true, 'color' => array('rgb' => '999999')),
            ));

            // Aba 2: instrucoes
            $inst = $spreadsheet->createSheet();
            $inst->setTitle('Como preencher');
            $textos = array(
                'Como preencher a carga de presenca',
                '',
                '1) Identifique o inscrito por UMA das colunas: cod_inscrito, cpf ou email.',
                '   - cod_inscrito e o mais seguro (casamento exato).',
                '   - Se nao souber o cod_inscrito, preencha cpf; se nao tiver cpf, preencha email.',
                '   - A coluna nome e apenas informativa (aparece no log), nao e usada no casamento.',
                '2) Na coluna presenca informe se o inscrito compareceu:',
                '   - Vazio ou 0  -> AUSENTE (flg_presenca_inscrito = 0).',
                '   - Qualquer outro valor (1, SIM, X, etc.) -> PRESENTE (flg_presenca_inscrito = 1).',
                '3) Apague a linha de exemplo (em cinza) antes de enviar.',
                '4) Salve como .xlsx (ou .csv separado por ponto e virgula) e envie na tela de carga',
                '   escolhendo a origem PRESENCA_INSCRITO.',
                '',
                'Obs.: o inscrito precisa existir no evento com status ATIVADA.',
                'Reenviar a planilha SOBRESCREVE a presenca ja existente do inscrito.',
            );
            $l = 1;
            foreach ($textos as $t) {
                $inst->setCellValue('A' . $l, $t);
                $l++;
            }
            $inst->getColumnDimension('A')->setWidth(90);
            $inst->getStyle('A1')->applyFromArray(array('font' => array('bold' => true, 'size' => 13)));

            $spreadsheet->setActiveSheetIndex(0);

            $nomeArquivo = 'modelo_presenca_inscrito.xlsx';
            while (ob_get_level() > 0) { ob_end_clean(); }
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $nomeArquivo . '"');
            header('Cache-Control: no-cache, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);
            exit();
        } catch (Exception $e) {
            while (ob_get_level() > 0) { ob_end_clean(); }
            header('Content-Type: text/html; charset=UTF-8');
            echo '<script>alert("' . addslashes($e->getMessage()) . '"); window.history.back();</script>';
            exit();
        }
    }
}

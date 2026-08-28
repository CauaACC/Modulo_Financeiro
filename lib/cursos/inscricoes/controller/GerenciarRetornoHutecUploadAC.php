<?php
include(dirname(__FILE__) .'/../include/functions/LeitorArquivo.php');

class GerenciarRetornoHutecUploadAC {

    public $tiposPermitidos;
    public $tamanho;
    public $varDiretorio;
    public $varArquivo;

    function __construct() {
        // Mesma verificacao de permissao da view (consulta-dados-congresso-seguranca.php),
        // mas lancando Exception (o CommandAC devolve JSON/pagina) em vez de imprimir HTML.
        $this->verificarPermissao();
        $this->tiposPermitidos = array('csv', 'txt', 'xls');
        $this->tamanho = '30MB';
        $this->varDiretorio = WB_PATH_LOG_CARGA.'RetornoHutec/';
    }

    /**
     * Gate de acesso das acoes de carga (upload/carregar/excluir). Espelha o conjunto de
     * permissoes usado na view. Sem permissao/sessao -> lanca Exception (tratada pelo CommandAC).
     */
    private function verificarPermissao() {
        $cod_msg = false;
        if ((@FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_GERENCIAR_ADM_GERAL,      5, $cod_msg, false) == false)
            and (@FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_GERENCIAR_INSCRITO,       5, $cod_msg, false) == false)
            and (@FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_GERENCIAR_AREAS_TEMATICAS, 5, $cod_msg, false) == false)
            and (@FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_CONSULTAR_PAINEL_BORDO,    5, $cod_msg, false) == false)
            and (@FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_CONSULTAR_INSCRITOS,       5, $cod_msg, false) == false)) {
            throw new Exception("Sem permissão de acesso ou usuário não autenticado.");
        }
    }

    public function carregarArquivo($fws_, $debug = false) {
        $obj = new Arquivo();
        $obj->setCodArquivo($fws_['codArquivo']);
        $obj->carregarArquivoById($debug);
        return $obj;
    }

    public function editarArquivo($fws_, $debug = false) {
        $ok = false;
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

    public function excluirArquivo($fws_, $debug = false) {
        try {
            $obj = $this->carregarArquivo($fws_, $debug);
            excluirArquivo($this->varDiretorio.$obj->getArquivoGerado());
            try {
                $objRH = new RetornoHutec();
                $objRH->setCodEvento($obj->getCodEvento());
                $objRH->setCodArquivo($obj->getCodArquivo());
                $objRH->excluirCargaArquivo();
            } catch (OKException $exc) { }
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

    // Verifica se o evento ja tem carga de retorno HUTEC (para perguntar se deseja substituir).
    public function verificarCargaExistente($fws_, $debug = false) {
        $qtd = 0;
        try {
            $obj = new Arquivo();
            $obj->setTipoArquivo('RETORNOHUTEC');
            $obj->carregarListaArquivoByIdEvento($debug);
            $lista = $obj->getListaArquivo();
            $qtd = is_array($lista) ? count($lista) : 0;
        } catch (Exception $e) {
            $qtd = 0;
        }
        echo '{"existe":"' . ($qtd > 0 ? 1 : 0) . '","qtd":"' . $qtd . '"}';
    }

    // Substitui a carga: apaga TODOS os arquivos de retorno HUTEC do evento e seus registros (fin_carga_hutec).
    public function substituirCargaEvento($fws_, $debug = false) {
        $ok = false; $e = null; $qtd = 0;
        try {
            $obj = new Arquivo();
            $obj->setTipoArquivo('RETORNOHUTEC');
            $obj->carregarListaArquivoByIdEvento($debug);
            $lista = $obj->getListaArquivo();
            if (is_array($lista)) {
                foreach ($lista as $objL) {
                    // apaga o arquivo fisico
                    @excluirArquivo($this->varDiretorio . $objL->getArquivoGerado());
                    // apaga os registros da carga (fin_carga_hutec) desse arquivo
                    try {
                        $objRH = new RetornoHutec();
                        $objRH->setCodEvento($objL->getCodEvento());
                        $objRH->setCodArquivo($objL->getCodArquivo());
                        $objRH->excluirCargaArquivo();
                    } catch (OKException $exc) {}
                    // apaga o registro do arquivo
                    try {
                        $objL->excluir();
                    } catch (OKException $exc) {}
                    $qtd++;
                }
            }
            $ok = true;
        } catch (OkException $ex) {
            $ok = true;
        } catch (Exception $ex) {
            $e = $ex;
        }
        echo '{"statusMessage":"' . $ok . '","message":"' . ($e ? $e->getMessage() : '') . '","qtd":"' . $qtd . '"}';
    }

    public function carregarTabelaArquivos($fws_, $debug = false) {
        try {
            $obj = new Arquivo();
            $obj->setTipoArquivo('RETORNOHUTEC');
            $obj->carregarListaArquivoByIdEvento($debug);
            $ii = 0;
            foreach ($obj->getListaArquivo() as $objL) {
                $ii++;
                ?>  
                <tr id="<?php echo  $objL->getCodArquivo() ?>">
                    <td>
                        <?php //if ($objEv->controlarAcessoProposta('boolean')) { ?>
                        <!--<img class="editar"  src="<?php echo  WB_PATH ?>/resources/image/alterar.gif" alt="Editar Arquivo" border="0">-->
                        <img class="excluir" src="<?php echo  WB_PATH ?>/resources/image/fig-excluir.gif" alt="Excluir Arquivo" border="0">
                        
                        <?php //} ?>
                    </td>
                   
                    <td>
                        <?php
                        $classCarga = false;
                        $classAssoc = false;
                        if ($objL->getStatusCarga() =='ASSOCIADO') {
                            echo $objL->getStatusCarga();
                            $classCarga = false;
                            $classAssoc = false;
                        }else{
                            if ($objL->getStatusCarga() =='CARREGADO') {
                                echo $objL->getStatusCarga();
                                $classCarga = false;
                                $classAssoc = true;
                            }else{
                                echo 'AGUARDANDO';
                                $classCarga = true;
                                $classAssoc = false;
                            }
                        }
                        echo $classCarga? '<input class="button_text botaoCarga"  type="button" id="carregarInscrito" name="carregarInscrito" value="Carregar" />' : '';
                        echo '<br>';
                        
                        ?>
                    </td>
                    <td>
                        <?php echo  $objL->getTipoArquivo()?>
                    </td>
                    <td><?php echo  $objL->getArquivoGerado() ?></td>
                    <td><?php echo  $objL->getArquivoUpload() ?></td>
                    <td><?php echo  formatDateView($objL->getDataInclusao()) ?></td>
                </tr>
                <?php
            }
            ?>
                <br>Total de cadastrados: <?php echo  $ii ?>.
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
    
    public function salvarRetornoHutecUpload($fws_, $debug = false) {
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
        echo '{
        "statusMessage":"' . $ok . '",
        "message":      "' . $e->getMessage() . '",
        "codArquivo":"' . $objAr->getCodArquivo() . '"
        }';
    }    
    
    public function uploadArquivo($fws_, $obj, $debug = false) {

        validarArquivo($_FILES,$this->tamanho,$this->tiposPermitidos);
        verificarECriarPasta($this->varDiretorio);

        /** definindo nome */
        $extensao = pathinfo($_FILES['arquivo']['name'], PATHINFO_EXTENSION);
        $obj->setArquivoUpload($_FILES['arquivo']['name']);
        $this->varArquivo = 'retornoHutec-'.numberPad($obj->getCodEvento(), 4).'-'.numberPad($obj->getLastId(), 4).'.'.$extensao;
        $obj->setArquivoGerado($this->varArquivo);

        gravandoArquivo($_FILES, $this->varDiretorio, $this->varArquivo, $debug);

    }    
    
    public function carregarRetornoHutec($fws_, $debug = false) {
        try {
            $objAr = $this->carregarArquivo($fws_,$debug);     
            $objEv = $objAr->getEvento();     
            if($objAr->getStatusCarga() != ''){
                throw new Exception("O arquivo já foi processado.");
            }

            $objConf = $objAr->getEventoConfiguracao();

            $file = new LeitorArquivo();
            $file->setNmArquivo($objAr->getArquivoGerado());
            // Leitor ATIVO: Excel/SpreadsheetML do HUTEC (FastReport) - le o .xls direto, sem conversao
            $file->carregarArquivoXls($this->varDiretorio . $file->getNmArquivo(), $debug);
            // Leitor STANDBY (CSV/generico) - reativar se voltar a subir CSV:
            // $file->carregarArquivo($this->varDiretorio . $file->getNmArquivo(), $debug);

            $listaLinhas = $file->getListaLinhas();
            $totalLinhas = $file->getTotalLinhas();

    //        $file->imprimirTituloColunas();;
    //        $this->DAO();
    //        $this->conex->BeginTrans();
    //        $this->conex->CommitTrans();
    //        $this->conex->RollbackTrans();

            //Começar com contador inverso igual a 1 para não gravar a primeira linha 
            //onde deve constar o titulo das colunas
            //$contadorInverso = 1;
            $contadorInverso = 0;
            $totalRegistros = $totalLinhas;

            //Loop em cima das linhas retornadas do arquivo
            $arrayColunas = [];
            $message = '';
            while($totalLinhas > 0){

                $linha = $listaLinhas[$contadorInverso];
                unset($arrayColunas);  // Atribuir um array vazio para limpar o array original
                $arrayColunas = explode(';', $linha);

                // Só recebimentos (créditos) entram na conciliação; débitos/despesas do balancete são ignorados
                if (!isset($arrayColunas[22]) || $arrayColunas[22] != 'C') {
                    $totalLinhas--;
                    $contadorInverso++;
                    continue;
                }

                try{
    //                /*Se diferente, não pertence*/
                    if($arrayColunas[0]==''){
                        throw new Exception("Data não informada");
                    }
                    if($arrayColunas[2]==''){
                        throw new Exception("CodPagamento não informada");
                    }
                    if($arrayColunas[4]==''){
                        throw new Exception("Nome não informada");
                    }
                    // if($arrayColunas[6]==''){
                    //     throw new Exception("Descrição não informada");
                    // }
                    // if($arrayColunas[16]==''){
                    //     throw new Exception("Extra não informada");
                    // }
                    // if($arrayColunas[20]==''){
                    //     throw new Exception("Valor não informada");
                    // }
                    // if($arrayColunas[21]==''){
                    //     throw new Exception("Operação não informada");
                    // }
                    $descricao = "";
                    if($arrayColunas[6]=='' and $arrayColunas[7]=='' and $arrayColunas[8]==''){
                        throw new Exception("Descrição não informada");
                    }else{
                        $descricao .= $arrayColunas[6]!=''? $arrayColunas[6].'-' : '';
                        $descricao .= $arrayColunas[7]!=''? $arrayColunas[7].'-' : '';
                        $descricao .= $arrayColunas[8]!=''? $arrayColunas[8].'-' : '';
                    }
                    $descricao = mb_substr($descricao, 0, 200); // coluna descricao é varchar(200) — evita "Data too long" (ex.: NF com texto longo)
                    $extra = "";
                    if($arrayColunas[15]=='' and $arrayColunas[16]==''){
                        throw new Exception("Extra não informada");
                    }else{
                        $extra .= $arrayColunas[15]!=''? $arrayColunas[15].'-' : '';
                        $extra .= $arrayColunas[16]!=''? $arrayColunas[16].'-' : '';
                    }
                    $extra = mb_substr($extra, 0, 200);
                    $valor = "";
                    if($arrayColunas[21]==''){
                        throw new Exception("Valor não informada");
                    }else{
                        $valor = $arrayColunas[21];
                    }
                    $operacao = "";
                    if($arrayColunas[22]==''){
                        throw new Exception("Operação não informada");
                    }else{
                        $operacao = $arrayColunas[22];
                    }

                    /*Definindo codigo da forma de RetornoHutec a ser associado*/
                    if($objAr->getTipoArquivo()=='RETORNOHUTEC'){
                        /*criar inscrito para a RetornoHutec*/
                        $objCH = new RetornoHutec();
                        $objCH->setCodEvento($objAr->getCodEvento()); 
                        $objCH->setCodArquivo($objAr->getCodArquivo());

                        $objCH->setData($arrayColunas[0]);
                        $objCH->setCodPagamento($arrayColunas[2]);
                        $objCH->setNome(mb_substr(mb_convert_encoding($arrayColunas[4],'UTF-8'), 0, 200));
                        // $objCH->setNome($arrayColunas[4]);
                        $objCH->setDescricao($descricao);
                        $objCH->setCodExtra($extra);
                        $objCH->setValor($valor);
                        $objCH->setTipoOperacao($operacao);
                        $objCH->salvarCarga();
                    }

                }catch (OKException $e){
                    $message .= "<br>linha: ".$contadorInverso.":".$e->getMessage().": Cód.Pagamento: ".$arrayColunas[1]." ";
                }catch (Exception $e){
                    if($e->getMessage()!=''){
                        $message .= "<br>linha: ".$contadorInverso.":".$e->getMessage()." [".$linha."]-";
                    }
                }catch (\Throwable $e){
                    // uma linha problemática (ex.: Cód.Pagamento não numérico como 'NF-144') NÃO aborta a carga inteira
                    $message .= "<br>linha: ".$contadorInverso." ignorada (Cód.Pagamento inválido: '".$arrayColunas[2]."'): ".$e->getMessage();
                }
                // $objInscApto->limparObj(); Verificar se precisa
                
                $totalLinhas--;
                $contadorInverso++;
            }
            // echo $message;
            // exit();
            // $message NÃO é mais zerado aqui: preserva o log das linhas ignoradas para o admin ver
            // Atualiza o status do arquivo carregado, indicando que a carga já foi feita.
            if ($contadorInverso == $totalRegistros){
                try {
                    $objAr->getByIdExiste();
                    $statusArquivo = 'CARREGADO';
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
                $message = 'Arquivo não carregado';
                $statusArquivo = 'Não carregado';
            }
        } catch (Exception $e) {echo 
            $statusArquivo = $e->getMessage();
            $ok = false;
        }
        echo '{
        "statusMessage":"' . $ok . '",
        "message":      "' .str_replace("\n","",$message .'<br>'. $statusArquivo) . '",
        "codArquivo":"' . $objAr->getCodArquivo() . '"            
        }';
    }
}

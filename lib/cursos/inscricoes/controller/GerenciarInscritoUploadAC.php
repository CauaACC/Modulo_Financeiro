 <?php
    
include(WB_ROOT. WB_PATH .'/lib/util/LeitorArquivo.php');

class GerenciarInscritoUploadAC {
    
    function __construct() {
        FWA_exigirPermissao(enumSistema::SIS_GERENCIAR_ADM_GERAL);
        $this->varDiretorio = WB_PATH_LOG . "/inscritos/";
    }

    function getEvento($fws_, $debug = false) {
        $objEAC = new GerenciarEventoAC();
        $this->objEvento = $objEAC->carregarEvento($fws_);
        return $this->objEvento;
    }

    public function carregarObjEventoArquivo($fws_, $debug = false) {
        $obj = new InscInscritoUpload();
        $obj->setCodEvento($fws_['codEvento']);
        $obj->setCodEventoArquivo($fws_['codEventoArquivo']);
        $obj->carregarObjEventoArquivoById($debug);
        $obj->carregarObjEvento($debug);
        return $obj;
    }
    
    public function getListaEventoArquivos($fws_, $debug = false) {
        $obj = new InscInscritoUpload();
        $obj->carregarListaEventoArquivos($debug);
        return $obj->getListaEventoArquivos();
    }

    public function editarEventoArquivo($fws_, $debug = false) {
        $obj = $this->carregarObjEventoArquivo($fws_, $debug);
        echo '{
                  "statusMessage":    "' . $ok . '",
                  "message":          "' . $obj->getMessage() . '",
                  "codEvento":        "' . $obj->getCodEvento() . '",
                  "codEventoArquivo":"' . $obj->getCodEventoArquivo() . '",
                  "descricao":"' . tratarJson($obj->getDescricao()) . '"
                 }';

        exit();
    }

    public function salvarInscritoUpload($fws_, $debug = false) {
        try {
            if ($fws_['novoCodEventoArquivo'] == 0) {
                $fws_['codEventoArquivo'] = NULL;
            }
            $isForm = true;
            
            $obj = $this->carregarObjEventoArquivo($fws_, $debug);
            $obj->setAll($fws_, $isForm);
            
            if($obj->getCodFormaPagamento()=='' and $obj->getCodPerfilColaborador()=='' and $obj->getCodCurso()==''){
                throw new Exception("� necess�rio associar o arquivo a algum perfil ou atividade.");
            }
            
            if($obj->getTipoCodPerfil() == 'perfilInscricao')
                $obj->setCodPerfil($obj->getCodFormaPagamento());

            if($obj->getTipoCodPerfil() == 'perfilColaboracao'){
                $obj->setCodPerfil($obj->getCodPerfilColaborador());
            }
            if($obj->getTipoCodPerfil() == 'listagemCursos'){
                $obj->setCodPerfil($obj->getCodCurso());
            }
            // a sequ�ncia ser� usada para compor o nome do arquivo, uma vez que � poss�vel o usu�rio fazer upload de mais de 1 arquivo.
            $obj->gerarSequenciaArquivo();  
            
            $fileOk = $this->uploadArquivo($fws_, $obj, $debug);
            if (!$fileOk){
                throw new Exception("N�o foi poss�vel gravar arquivo.");
            }
//            else {
//                throw new OKException("Arquivo gravado com sucesso.");
//            }

            $obj->salvar();
        } catch (OkException $e) {
            $ok = true;
        } catch (Exception $e) {
            $ok = false;
        }
        echo '{
        "statusMessage":"' . $ok . '",
        "message":      "' . $e->getMessage() . '",
        "codEvento":"' . $obj->getCodEvento() . '"            
        }';
        }

    public function excluirInscritoUpload($fws_, $debug = false) {
        try {
            $obj = $this->carregarObjEventoArquivo($fws_, $debug);
            
            if(!unlink($this->varDiretorio.$obj->getArquivo())){
                throw new Exception("N�o foi poss�vel remover o arquivo fisicamente.");
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
        "codEventoArquivo":"' . $fws_['codEventoArquivo'] . '"
        }';
    }
    public function abrirArquivoInscritoUpload($fws_, $debug = false) {
        try {
            $obj = $this->carregarObjEventoArquivo($fws_, $debug);

            $objArquivo = new LeitorArquivo();
            $objArquivo->setNmArquivo($obj->getArquivo()); 
            $objArquivo->carregarArquivo($this->varDiretorio . $objArquivo->getNmArquivo(),$debug);

            $listaLinhas = $objArquivo->getListaLinhas();
            $totalLinhas = $objArquivo->getTotalLinhas();
            $contadorInverso = 0;

            $message = "";
            while($totalLinhas > 0){
                $message .= $listaLinhas[$contadorInverso];
                $totalLinhas--;
                $contadorInverso++;
            }
            $message = str_replace("\n", '<br>', $message);
            $ok = true;
        } catch (Exception $e) {
            $message = $e->getMessage();
            $ok = false;
        }
        echo '{
        "statusMessage":"' . $ok . '",
        "message":      "' . $message . '",
        "codEventoArquivo":"' . $fws_['codEventoArquivo'] . '"
        }';
    }
    public function abrirArquivoDownload($fws_, $debug = false) {
        try {
            $obj = $this->carregarObjEventoArquivo($fws_, $debug);

            $objArquivo = new LeitorArquivo();
            $objArquivo->setNmArquivo($obj->getArquivo());
            $objArquivo->carregarArquivo($this->varDiretorio . $objArquivo->getNmArquivo(), $debug);
            $nomeArquivo = $objArquivo->getNmArquivo();

            $ok = true;
        } catch (Exception $e) {
            $message = $e->getMessage();
            $ok = false;
        }

        header("Content-Type: text/csv; charset=ISO-8859-1");
        header("Content-Disposition: attachment; filename=$nomeArquivo");
        header("Pragma: no-cache");
        header("Expires: 0");
        readfile($this->varDiretorio . $objArquivo->getNmArquivo());
        print("\n");
    }

    public function carregarTabelaInscritoUpload($fws_, $debug = false) {
        $obj = new InscInscritoUpload();
        $obj->setCodEvento($fws_['codEvento']);
        
        try { ?>
            <?php    
            $obj->carregarListaEventoArquivosByIdEvento($debug);
            $lista = $obj->getListaEventoArquivos();
            foreach ($lista as $objL) {
                ?>  
                <tr id="<?= $objL->getCodEventoArquivo() ?>" data-arquivo="<?= $objL->getArquivo() ?>">
                    <td> 
                        <?php 
                        if (($objL->getStatus() != "CARREGADO")or($objL->getStatus() != "ASSOCIADO")){ ?>
                            <img class="excluir" src="<?= WB_PATH ?>/view/image/fig-excluir.gif" alt="Excluir EventoArquivo" border="0">    
                            <input class="button_text botaoCarga"    type="button" id="carregarInscrito" name="carregarInscrito" value="Carregar" />
                            <input class="button_text botaoAssociar" type="button" id="associarInscrito" name="associarInscrito" value="Associar" />
                      <?php } ?>
                    </td>
                    <td><?= $objL->getCodEventoArquivo() ?></td>
                    <td>
                    <?php
                    echo $objL->getNomeTipoPerfil();
                    ?></td>
                    <td>
                        <a href="javascript:void(0);" class="downloadArquivo">
                        <img src="<?php echo WB_PATH; ?>/view/image/alterar.gif" title="Abrir Arquivo" border="0">
                        <?php echo $objL->getArquivo(); ?></a>
                        - <?php echo $objL->getArquivoOriginal()."<br>";
                        echo $objL->getTotalCarregado()!=''? "<br>Total carregado: ".$objL->getTotalCarregado() : '';
                        ?>
                    </td>
                    <td><?php echo $objL->getStatus() ?></td>
                </tr>
                <?php
            }
        } catch (Exception $e) {
            ?>
            <tr>
                <td></td>
                <td colspan="5">
                    <?= $e->getMessage() ?>
                </td>
            </tr>
            <?php
        }
    }

    public function uploadArquivo($fws_, $obj, $debug = false) {

        if ($_FILES['arquivo']['name'] == '') {
            throw new Exception("Arquivo n�o selecionado.");
        }

        $obj->setArquivoOriginal($_FILES['arquivo']['name']);

        $extensao = strtolower(substr($obj->getArquivoOriginal(), -3));
        if ($extensao != 'csv') {
            throw new Exception("Extens�o de arquivo n�o permitida.");
        }
        
        $this->varArquivo = $obj->getObjEvento()->getAnoEvento() . "_" . $obj->getObjEvento()->getCodEvento() . "_" . $obj->getCodEventoArquivo()  . "." . $extensao;
        $obj->setArquivo($this->varArquivo);
        
        if (!file_exists($this->varDiretorio)) {
            mkdir($this->varDiretorio, 0755);
        }
        
        if (move_uploaded_file($_FILES['arquivo']['tmp_name'], $this->varDiretorio.$this->varArquivo)) {
            return true;
        } else {
            return false;
        }
    }

    public function associarArquivoInscritos($fws_, $debug = false) {
        try {
            /*definindo se somente inserir novas associacoes ou inserir novas e atualizar atualizar existentes*/
            $fws_['atualizarAssociacao'] = 0;
            if($fws_['codEvento']==6423){
                $fws_['atualizarAssociacao'] = 1;
            }
            $objInscUp = new InscInscritoUpload();
            $objInscUp->setCodEventoArquivo($fws_['codEventoArquivo']);
            $objInscUp->setCodEvento($fws_['codEvento']);
            $objInscUp->carregarObjEventoArquivoById();

            if($objInscUp->getStatus() != 'CARREGADO' and  $objInscUp->getStatus() != 'ASSOCIADO'){
                throw new Exception("O arquivo deve ser carregado antes de associado.");
            }
            $objArquivo = new LeitorArquivo();
            $objArquivo->setNmArquivo($fws_['arquivo']); 
            $objArquivo->carregarArquivo($this->varDiretorio . $objArquivo->getNmArquivo(),$debug);

            $listaLinhas = $objArquivo->getListaLinhas();
            $totalLinhas = $objArquivo->getTotalLinhas();

    //        $this->DAO();
    //        $this->conex->BeginTrans();
    //        $this->conex->CommitTrans();
    //        $this->conex->RollbackTrans();

            //Come�ar com contador inverso igual a 1 para n�o gravar a primeira linha onde deve constar o titulo das colunas
            //$contadorInverso = 1;
            $contadorInverso = 0;
            $totalRegistros = $totalLinhas;


            //Loop em cima das linhas retornadas do arquivo        
            while($totalLinhas > 0){
                $linha = $listaLinhas[$contadorInverso];
                $arrayColunas = explode(';', $linha);
                try{
                    $objInsc = new InscInscrito();
                    if($arrayColunas[0]=='' and $arrayColunas[1]=='' and $arrayColunas[2]==''){
                        throw new Exception("");
                    }
                    if($arrayColunas[1]==''){
                        throw new Exception("Cpf n�o informado");
                    }
                    
                    $arrayColunas[2] = minusculo($arrayColunas[2]);
                    $objInsc->setCodEvento($fws_['codEvento']); 
                    $objInsc->setCpf($arrayColunas[1]);
                    $objInsc->setEMail($arrayColunas[2]); 
                    $objInsc->setFoneResidencial($arrayColunas[3]); 
                    $objInsc->setCodArea($arrayColunas[4]); 
                    $objInsc->setAreaTematica($arrayColunas[5]);  
                    $objInsc->setCodEventoArquivo($objInscUp->getCodEventoArquivo()); 
                    if(!$objInsc->carregarObjInscritoByCpf()){
                        throw new Exception("Cpf:".$objInsc->getCpf()." n�o encontrado");
                    }
                    /*Definindo codigo da forma de pagamento a ser associado*/
                    if($objInscUp->getTipoCodPerfil()=='perfilInscricao'){
                        /*associar a forma de pagamento*/
                        $fws_['codFormaPagamento'] = $objInscUp->getCodPerfil();
                        $fws_['codInscrito'] = $objInsc->getCodInscrito();
                        $fws_['codEvento'] = $objInsc->getCodEvento();
                        $fws_['nota'] = $arrayColunas[6]; 
                        $fws_['frequencia'] = $arrayColunas[7]; 
                        $fws_['presenca']   = $arrayColunas[8];
                        $objAssociar = new GerenciarInscritoFormaPagamentoAC();
                        $objAssociar->cargaAssociarInscritoFormaPagamento($fws_,$debug); 
                    }
                    if($objInscUp->getTipoCodPerfil()=='listagemCursos'){
                        /*associar a curso*/
                        $fws_['codCurso'] = $objInscUp->getCodPerfil();
                        $fws_['codInscrito'] = $objInsc->getCodInscrito();
                        $fws_['codEvento'] = $objInsc->getCodEvento();
                        $fws_['nota'] = $arrayColunas[6]; 
                        $fws_['frequencia'] = $arrayColunas[7]; 
                        $fws_['presenca']   = $arrayColunas[8];
                        $objAssociar = new GerenciarInscritoCursoAC;
                        $objAssociar->cargaAssociarInscritoCurso($fws_,$debug); 
                    }
                    if($objInscUp->getTipoCodPerfil()=='perfilColaboracao'){
                        /*associar a perfil colaborador*/
                        $objPerfil = new InscInscritoPerfilColaborador();
                        $objPerfil->setCodPerfilColaborador($objInscUp->getCodPerfil());
                        $objPerfil->setCodInscrito($objInsc->getCodInscrito());
                        $objPerfil->setCodEvento($objInsc->getCodEvento());
                        $objPerfil->setCodEventoArquivo($fws_['codEventoArquivo']);
                        $objPerfil->associarInscritoPerfilColaborador($debug);   

                    }                
                }catch (OKException $e){
                    $message .= "<br>linha: ".$contadorInverso.":".$e->getMessage().": CPF: ".$objInsc->getCpf();
                }catch (Exception $e){
                    $message .= "<br>linha: ".$contadorInverso.":".$e->getMessage().": CPF: ".$objInsc->getCpf();
                }
                $totalLinhas--;
                $contadorInverso++;
            }

            // Atualiza o status do arquivo carregado, indicando que a carga j� foi feita.
            if ($contadorInverso == $totalRegistros){
                try {
                    $objInscUp->setStatus("ASSOCIADO");                
                    $objInscUp->atualizar($debug);
                } catch (OkException $e) {
                    $statusArquivo = $e->getMessage();
                    $ok = true;
                } catch (Exception $e) {
                    $statusArquivo = $e->getMessage();
                    $ok = false;
                }
                echo '{
                "statusMessage":"' . $ok . '",
                "message":      "' .str_replace("\n","",$message .'<br>'. $statusArquivo) . '",
                "codEventoArquivo":"' . $objInscUp->getCodEventoArquivo() . '"            
                }';
            }           
        } catch (Exception $e) {
            echo '{
            "statusMessage":"0",
            "message":      "' .$e->getMessage() . '",
            "codEventoArquivo":"' . $objInscUp->getCodEventoArquivo() . '"            
            }';
        }
    }
    
    public function carregarArquivoInscritos($fws_, $debug = false) {
        
        $objArquivo = new LeitorArquivo();
        $objArquivo->setNmArquivo($fws_['arquivo']); 
        $objArquivo->carregarArquivo($this->varDiretorio . $objArquivo->getNmArquivo(),$debug);
        $listaLinhas = $objArquivo->getListaLinhas();
        $totalLinhas = $objArquivo->getTotalLinhas();
        
        //$objArquivo->imprimirTituloColunas();
//        $this->DAO();
//        $this->conex->BeginTrans();
//        $this->conex->CommitTrans();
//        $this->conex->RollbackTrans();


        //Come�ar com contador inverso igual a 1 para n�o gravar a primeira linha onde deve constar o titulo das colunas
        //$contadorInverso = 1;
        $contadorInverso = 0;
        $totalRegistros = $totalLinhas;

                
        //Loop em cima das linhas retornadas do arquivo        
        while($totalLinhas > 0){
    
            $linha = $listaLinhas[$contadorInverso];
            $arrayColunas = explode(';', $linha);

            try{
                $objInsc = new InscInscrito();
                if($arrayColunas[0]=='' and $arrayColunas[1]=='' and $arrayColunas[2]==''){
                    throw new Exception("");
                }
                if($arrayColunas[1]==''){
                    throw new Exception("Cpf n�o informado");
                }
                
                $objInsc->setCodEvento($fws_['codEvento']); 
                $objInsc->setCodInscrito(null);
                $objInsc->setNomeInscrito($arrayColunas[0]);
                $objInsc->setCpf($arrayColunas[1]);
                $objInsc->setEMail($arrayColunas[2]);                
                $objInsc->setFoneResidencial($arrayColunas[3]);
                $objInsc->setCodArea($arrayColunas[4]);
                $objInsc->setStatus('ATIVADO');
                $objInsc->setCodEventoArquivo($fws_['codEventoArquivo']);
                $objInsc->salvar($debug);
                
            }catch (OKException $e){
                $message .= "<br>linha: ".$contadorInverso.":".$e->getMessage().": CPF: ".$objInsc->getCpf();
            }catch (Exception $e){
                if($e->getMessage()!=''){
                    $message .= "<br>linha: ".$contadorInverso.":".$e->getMessage().": CPF: ".$objInsc->getCpf();
                }
            }
            // $objInscApto->limparObj(); Verificar se precisa
                
            $totalLinhas--;
            $contadorInverso++;
        }

        // Atualiza o status do arquivo carregado, indicando que a carga j� foi feita.
        if ($contadorInverso == $totalRegistros){            
            try {
                $objInscUp = new InscInscritoUpload();
                $objInscUp->setCodEvento($fws_['codEvento']);
                $objInscUp->setCodEventoArquivo($fws_['codEventoArquivo']);
                $objInscUp->carregarObjEventoArquivoById($debug);
                $objInscUp->setStatus("CARREGADO");                
                
                $objInscUp->atualizar($debug);
            } catch (OkException $e) {
                $statusArquivo = $e->getMessage();
                $ok = true;
            } catch (Exception $e) {
                $statusArquivo = $e->getMessage();
                $ok = false;
            }
            echo '{
            "statusMessage":"' . $ok . '",
            "message":      "' .str_replace("\n","",$message .'<br>'. $statusArquivo) . '",
            "codEventoArquivo":"' . $objInscUp->getCodEventoArquivo() . '"            
            }';
        }
        $objInsc = NULL;
    }
}
<?php
include(dirname(__FILE__) .'/../include/functions/LeitorArquivo.php');

class GerenciarArquivoAtividadeAC {

    function __construct() {       
//        ControleAcesso::ifUserInRole('gerenciarEventos,coordenarEventos', 'url');
        $this->tiposPermitidos = array('pdf', 'doc', 'docx', 'ppt', 'pptx');
        $this->tamanho = '90MB';
        $this->varDiretorio = WB_PATH_LOG_CARGA.'atividades/';
    }

    public function carregarArquivoAtividade($fws_, $debug = false) {
        $obj = new ArquivoAtividade();
        $obj->setCodArquivoAtividade($fws_['codArquivoAtividade']);
        $obj->carregarArquivoAtividadeById($debug);
        return $obj;
    }

    public function editarArquivoAtividade($fws_, $debug = false) {
        $obj = $this->carregarArquivoAtividade($fws_,$debug);

        echo '{
                  "statusMessage":"' . $ok . '",
                  "message":      "' . $obj->getMessage() . '",
                  "codArquivoAtividade":   "' . $obj->getCodArquivoAtividade() . '",
                  "tipoArquivo":  "' . $obj->getTipoArquivo() . '",
                  "arquivoGerado":"' . $obj->geArquivoGerado() . '",
                  "dataInclusao": "' . $obj->getDataInclusao() . '" 
                 }';
        exit();
    }

     public function excluirArquivoAtividade($fws_, $debug = false) {
        try {
            $obj = $this->carregarArquivoAtividade($fws_, $debug);
            
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
        "codArquivoAtividade":"' . $fws_['codArquivoAtividade'] . '"
        }';
    }

    public function carregarTabelaArquivos($fws_, $debug = false) {
        try {
            $obj = new ArquivoAtividade();
            $obj->carregarListaArquivoAtividadeByIdEvento($debug);
            $ii = 0;
            foreach ($obj->getListaArquivoAtividade() as $objL) {
                $ii = $ii+1;
                ?>  
                <tr id="<?php echo  $objL->getCodArquivoAtividade() ?>">
                    <td>
                        <?php //if ($objEv->controlarAcessoProposta('boolean')) { ?>
                        <!--<img class="editar"  src="<?php echo  WB_PATH ?>/resources/image/alterar.gif" alt="Editar Arquivo" border="0">-->
                        <img class="excluir" src="<?php echo  WB_PATH ?>/resources/image/fig-excluir.gif" alt="Excluir Arquivo" border="0">
                         <button id="btnVisualizarPrograma" title="Visualizar"
                            href="javascript:void(0);" onclick="javascript:visualizarAtividade('<?php echo  $objL->getCodArquivoAtividade() ?>');">
                            Visualizar</button> 
                        <?php //} ?>
                    </td>
                   
                    <td><?php echo  $objL->getDescricao()?></td>
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
    public function carregarTabelaConteudoPublico($fws_, $debug = false) {
        try {
            $obj = new ArquivoAtividade();
            $obj->carregarListaArquivoAtividadeByIdEvento($debug);
            $ii = 0;
            foreach ($obj->getListaArquivoAtividade() as $objL) {
                $ii = $ii+1;
                ?>  
                <tr id="<?php echo  $objL->getCodArquivoAtividade() ?>">
                    <td>
                        <button id="btnVisualizarPrograma" title="Visualizar Arquivo" class="btn-block" style="margin:5px;text-align: left;"
                            href="javascript:void(0);" onclick="javascript:visualizarAtividade('<?php echo  $objL->getCodArquivoAtividade() ?>');">
                            <?php echo  $objL->getDescricao()?></button> 
                    </td>
                    <td><span style="padding-left: 15px;"><?php echo  formatDateView($objL->getDataInclusao()) ?></span></td>
                </tr>
                <?php
            }
            ?>
            <br>Total de conteúdo: <?php echo  $ii ?>.
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
    
    public function salvarArquivoAtividade($fws_, $debug = false) {
        try {

            $objAr = new ArquivoAtividade();
            $objAr->setAll($fws_,1,$debug);
            $this->uploadArquivoAtividade($fws_, $objAr, $debug);
            $objAr->salvar($debug);
            
        } catch (OkException $e) {
            $ok = true;
        } catch (Exception $e) {
            $ok = false;
        }
        echo '{
        "statusMessage":"' . $ok . '",
        "message":      "' . $e->getMessage() . '",
        "codArquivoAtividade":"' . $objAr->getCodArquivoAtividade() . '"
        }';
    }    
    
    public function uploadArquivoAtividade($fws_, $obj, $debug = false) {

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
        $this->varArquivo = 'atividade-'.numberPad($obj->getCodEvento(), 4).'-'.numberPad($obj->getLastId(), 4).'.'.$extensao;
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
    public function abrirArquivoAtividade($fws_, $debug = false) {
        $id=$fws_['cod'];
        $filepath =  $this->varDiretorio;
        $codEvento = $numero_formatado = str_pad(COD_EVENTO, 4, '0', STR_PAD_LEFT);
//        $arquivo = $id.".pdf";
        $arquivo = 'atividade-'.$codEvento.'-'.numberPad($id, 4).'.pdf';
//        echo $filepath.$arquivo."<br>";
//        exit();
        if (file_exists($filepath.$arquivo)) {
            header("Content-Description: File Transfer");
            header("Cache-Control: private"); // work-around for IE
            header("Content-Type: application/pdf");
//            header("Content-Type: application/force-download");
            header("Content-Disposition: attachment; filename=\"".$arquivo."\"");
            header("Content-Length: " . @filesize($filepath.$arquivo));
            header("Pragma: public");
            header("Expires: 0");
            readfile($filepath.$arquivo) or die();
        } else {
            echo "<script language=\"Javascript\"> alert('Arquivo (".$arquivo.")não encontrado!'); window.close();</script>";
        }
    }    
}

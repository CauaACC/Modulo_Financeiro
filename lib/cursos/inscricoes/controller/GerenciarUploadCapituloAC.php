<?php

class GerenciarUploadCapituloAC {

    function __construct(){
        $this->codEvento = COD_EVENTO;
        $this->varDiretorio = WB_PATH_LOG_TRABALHO;
    }

    public static function gerarBlocoUpload($objInscrito, $tipoDocumento, $numCapitulo = null)
    {
        $objTrabalho = new Trabalho();
        $objTrabalho->setCodInscrito($objInscrito->getCodInscrito());
        $objTrabalho->setCodEvento(COD_EVENTO);
        $objTrabalho->setTipoDocumento($tipoDocumento);
        $objTrabalho->setStatus('ATIVADA');

        if ($numCapitulo !== null) {
            $fws_['numCapitulo'] = $numCapitulo;
        } else {
            $fws_['numCapitulo'] = 1;
        }

        ob_start();
        ?>
        <div class="list-group-item item-liberado">
            <strong>📎 Enviar <?php echo htmlspecialchars($label); ?></strong>
            <ul>
                <li>
                    <div class="upload-capitulo" data-capitulo="<?php echo $fws_['numCapitulo']; ?>">
                        <?php
                        $gerenciarUploadCapitulo = new GerenciarUploadCapituloAC();
                        $gerenciarUploadCapitulo->formEnviarCapitulo($objInscrito, $objTrabalho, $fws_, $debug = false);
                        ?>
                    </div>
                    <?php if (!empty($objTrabalho->getArquivoGerado())): ?>
                        <div class="botaoAbrir" data-cod-area="<?php echo $fws_['numCapitulo']; ?>">
                            <button type="button" onclick="abrirArquivoGerado('<?php echo $objTrabalho->getArquivoGerado(); ?>', '<?php echo $objTrabalho->getCodEvento(); ?>')">
                                📄 Abrir Arquivo Enviado
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="botaoAbrir" data-cod-area="<?php echo $tipoDocumento; ?>"></div>
                    <?php endif; ?>
                </li>
            </ul>
        </div>
        <?php
        return ob_get_clean();
    }

    public function formEnviarCapitulo($objInscrito, $objTrabalho, $fws_, $debug = false){ 
        ?>
        <form class="formUpload" enctype="multipart/form-data">
          <input type="hidden" name="capitulo" value="<?php echo $fws_['numCapitulo']?>">
          <input type="hidden" name="codAreaTematica" value="<?php echo $objTrabalho->getCodAreaTematica()?>">
          <input type="hidden" name="c" value="GerenciarUploadCapitulo">
          <input type="hidden" name="m" value="salvarTrabalho">
          <input type="hidden" name="codInscrito" value="<?php echo $objInscrito->getCodInscrito() ?>">
          <?php 
          if ($objTrabalho->getCodTrabalho() != '') {
            echo '<input type="hidden" name="codTrabalho" id="codTrabalho-'.$objInscrito->getCodInscrito().'" value="' . $objTrabalho->getCodTrabalho() . '">';
          } else {
            echo '<input type="hidden" name="codTrabalho" id="codTrabalho-'.$objInscrito->getCodInscrito().'" value="">';
          }
          ?>
          <input type="file" name="arquivo" required>
          <button type="submit">Enviar Arquivo</button>
          <progress class="barra-progresso" value="0" max="100" style="width: 100%; display:none;"></progress>
          <div class="mensagem-upload"></div>
        </form>
        <div class="historico-envios">
        <?php
        if ($objTrabalho->getVersao() != ''){
            echo '<span style="color: green;">';
            echo 'Última versão: ' . $objTrabalho->getVersao();
            $data = $objTrabalho->getDataAlteracao()? $objTrabalho->getDataAlteracao() : $objTrabalho->getDataInclusao();
                $data = formatDateView($data);
            echo '<br>Data do envio: ' . $data;
            echo '</span>';
        }else{
            echo '<span style="color: red;">';
            echo 'Nenhum arquivo enviado.';
            echo '</span>';
        } 
    }

    public function salvarTrabalho($fws_, $debug = false){
        try {
            $objI = new Inscrito();
            $objI->setCodInscrito($_SESSION['_cod_inscrito']);

            if(!$objI->inscritoExiste($debug)){
                throw new Exception('Código de inscrito não cadastrado.');
            }
            $obj = new Trabalho();
            /*
            if ($fws_['cod_trabalho']==''){
               throw new Exception("Código do trabalho não informado.");
            }*/
            //dd($fws_);
            $obj->setAll($fws_);
            $obj->setCodInscrito($_SESSION['_cod_inscrito']);
            // $obj->setCodAreaTematica($fws_['codAreaTematica']);
            // if($obj->getCodAreaTematica() == ''){
            //     throw new Exception("Área temática não informada.");
            // }
            $capitulo = str_pad($fws_['capitulo'], 2, '0', STR_PAD_LEFT);
            $obj->setTipoDocumento('Capitulo-'.$capitulo);            
            if($obj->getStatus()==''){
                $obj->setStatus('ATIVADA'); 
            }
            if($fws_['codTrabalho']!=''){
                $obj->setCodTrabalho($fws_['codTrabalho']);
                //$obj->getByCapituloUltimo($debug);
                $obj->setVersao($obj->getVersao()+1);
            }else{
                $obj->carregarCodNovoTrabalho();
                $obj->setVersao($obj->getVersao()+1);
                $obj->setNovo('1');
            }
            /*verificar se arquivo foi submetido*/
            if($_FILES['arquivo']['name']==''){
                throw new Exception("Arquivo não informado");
            }
            if ($_FILES['arquivo']['size'] > 1024*1024*1024*70){
            throw new Exception("Não é permitido arquivos maiores que 30MB.");
            }
            if ($_FILES['arquivo']['name'] == '') {
                throw new Exception("Arquivo não selecionado.");
            }
            if ($_FILES['arquivo']['tmp_name'] == '') {
                throw new Exception("Arquivo temporário não encontrado.");
            }
            $extensao = validarTipoArquivoUpload(
                $_FILES['arquivo']['tmp_name'],
                $_FILES['arquivo']['name'],
                array('doc','docx','pdf','ppt','pptx','pptm')
            );

            $fws_['arquivo_gerado']  = numberPad($obj->getCodInscrito(), 6). "_" . numberPad($obj->getCodTrabalho(), 6)  ."_" . $obj->getVersao() . ".".$extensao;
            if (!file_exists($this->varDiretorio)){
                mkdir($this->varDiretorio, 0700);
                echo "Criou diretório";
            }
            $fws_['arquivo_destino'] = $this->varDiretorio.$fws_['arquivo_gerado'];
            if (!move_uploaded_file($_FILES['arquivo']['tmp_name'], $fws_['arquivo_destino'])) {
                throw new Exception("Ocorreu um erro durante upload do arquivo.");
            }
            $obj->setArquivoUpload($_FILES['arquivo']['name']);
            $obj->setArquivoGerado($fws_['arquivo_gerado']);

            $obj->salvarTarefa($debug);
        } catch (OKException $exc) {
            $ok = 1;
            $msg = $exc->getMessage();
            $_SESSION['MSG'] = $msg;
            $url = WB_URL_VIEW . "?content=view/inscricao/inscricao_congresso_ok.php";
            $data = $obj->getDataAlteracao()? $obj->getDataInclusao() : $obj->getDataInclusao();
            $data = formatDateView($data);
        } catch (Exception $exc) {
            $ok = 0;
            $msg = $exc->getMessage();
            $_SESSION['MSG'] = $msg;
            $url = WB_URL_VIEW . "?content=view/inscricao/inscricao_congresso_ok.php";
        }
        $data = $obj->getDataAlteracao()? $obj->getDataAlteracao() : $obj->getDataInclusao();
        echo '{
            "statusMessage":"' . $ok . '",
            "message":      "' . $msg . '",
            "versao":       "' . $obj->getVersao() . '",
            "data":"'. $data . '",
            "codInscrito":"' . $obj->getCodInscrito() . '",
            "codTrabalho":"' . $obj->getCodTrabalho() . '",
            "codAreaTematica":"' . $obj->getCodAreaTematica() . '",
            "tipoDocumento":"' . $obj->getTipoDocumento() . '",
            "codEvento":"' . $obj->getCodEvento() . '",
            "arquivoGerado":"' . $obj->getArquivoGerado() . '"
            }';
    }
    public function abrirDocumentoTela($fws_, $debug = false) {
        if ($_SESSION['_cod_usuario'] or $_SESSION['_cod_inscrito']){
            $id=$fws_['cod'];
            $filepath = $this->varDiretorio;
            if($fws_['codEvento']){
                // echo $fws_['codEvento'];
                // echo __LINE__.'<br>';   
                $codEvento = str_pad($fws_['codEvento'],'3','0',STR_PAD_LEFT);
                $filepath = WB_PATH_LOG_TRABALHO.'../../'.$codEvento.'/trabalhos/';
                // echo $filepath.'<br>';  
                // echo $id;
                // exit();
            }
            $arquivo = $id;
            if (file_exists($filepath.$arquivo)) {
                header("Content-Description: File Transfer");
                header("Cache-Control: private"); // work-around for IE
                header("Content-Type: application/msword");
                header("Content-Disposition: attachment; filename=\"".$arquivo."\"");
                header("Content-Length: " . @filesize($filepath.$arquivo));
                header("Pragma: public");
                readfile($filepath.$arquivo) or die();
            } else {
                echo "<script language=\"Javascript\"> alert('Arquivo não existe!'); window.close();</script>";
            }
        }else{
            echo "<script language=\"Javascript\"> alert('Acesso negado!'); window.close();</script>";
        }
    }
}

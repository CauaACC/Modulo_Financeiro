<?php
class GerenciarArquivoAC {
    
    function __construct() {
        FWA_exigirPermissao(enumSistema::SIS_GERENCIAR_ADM_GERAL);
        
    }
    
    public function salvarAnexoMediaUpload(MediaUpload $obj, $fws_, $debug = false){

        $tiposPermitidos = array('pdf','doc', 'docx', 'rtf');
        $tamanhoMaximo = 597152*10;
        $tamanhoMaximoTx = '30MB';
        $extensaoPermitida = '';
        $fws_['arquivo_gerado']  = 'coordenador-'.numberPad($obj->getCodOficina(), 7).'.'.$extensao;

        $wb_path_log_arquivo = WB_PATH_LOG_CERTIFICADOS.'../assinaturas/';

        // stringEntrada = $objEvConf->getTrabArquivoExtensao();
        $tiposSelecionados = array_map('trim', explode(',', $stringEntrada));
        $tiposPermitidos = array_intersect($tiposPermitidos, $tiposSelecionados);                
        $mimeTypesAssociados = array(
            'pdf'  => 'application/pdf',
            'doc'  => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'rtf'  => 'text/rtf'
        );        

        if($_FILES['arquivo']['name']==''){
            throw new Exception("Arquivo não informado");
        }
        if ($_FILES['arquivo']['size'] > $tamanhoMaximo){
           throw new Exception("Não é permitido arquivos maiores que ".$tamanhoMaximoTx);
        }
        if ($_FILES['arquivo']['name'] == '') {
            throw new Exception("Arquivo não selecionado.");
        }
        if ($_FILES['arquivo']['tmp_name'] == '') {
            throw new Exception("Arquivo temporário não encontrado.");
        }
        
        $extensao = pathinfo($_FILES['arquivo']['name'], PATHINFO_EXTENSION);
        if (!$extensao || !in_array($extensao, $tiposPermitidos)) {
            throw new Exception("Extensão de arquivo não permitida.");
        }

        if($extensaoPermitida!=''){
            if ($_FILES['arquivo']['type'] != 'image/jpg') {
                throw new Exception("Extensão de arquivo somente tipo JPG.");
            }
        }
        
        $extensao = substr(strrchr($_FILES['arquivo']['name'], "."), 1);

        if (!file_exists($wb_path_log_arquivo)){
            echo $debug? $wb_path_log_arquivo.'<br>' : '';
            if(mkdir($wb_path_log_arquivo, 0777, true)){
                echo $debug? "Criou diretório<br>" : '';
            }else{
                echo "Ocorreu um erro ao criar diretório<br>";
            }
        }
        $fws_['arquivo_destino'] = $wb_path_log_arquivo.$fws_['arquivo_gerado'];

        if (move_uploaded_file($_FILES['arquivo']['tmp_name'], $fws_['arquivo_destino'])) {
            echo "Arquivo carregado com sucesso!<br>";
        } else {
            //echo $_FILES['arquivo']['tmp_name'].', '.$fws_['arquivo_destino'];
            echo "Ocorreu um erro ao carregar o arquivo: " . $_FILES["file"]["error"];
            throw new Exception("Ocorreu um erro durante upload do arquivo.");
        }

        $obj->setArquivoUpload($_FILES['arquivo']['name']);

    }
    
    public function salvarAnexoAssinatura($fws_, $debug = false) {
        try {
            $objOf = new Oficina();
            $objOf->setCodOficina($fws_['codOficina']);
            
            if(!$objOf->OficinaExiste($debug)){
                throw new Exception('Código de oficina não cadastrado.');
            }
            
            try {
                $objAr = new Arquivo();
                $objAr->setCodOficina($objOf->getCodOficina());
                $objAr->setTipoArquivo('ASSINATURA-COORDENADOR');
                $objAr->carregarArquivoByIdOficinaAssCoordenador($debug);
                $objAr->setVersao($objAr->getVersao()+1);
            } catch (Exception $exc) {}
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
//            if ($_FILES['arquivo']['type'] != 'image/jpg') {
//                throw new Exception("Extensão de arquivo somente tipo JPG.");
//            }
            
            $extensao = substr(strrchr($_FILES['arquivo']['name'], "."), 1);
            $fws_['arquivo_gerado']  = 'coordenador-'.numberPad($objAr->getCodOficina(), 7).'.'.$extensao;
            if(!verificarECriarPasta(WB_PATH_LOG_CERTIFICADOS)){
                throw new Exception("Erro ao tentar criar pata para certificados");
            }
            $wb_path_log_arquivo = WB_PATH_LOG_CERTIFICADOS.'../assinaturas/';
            if(!verificarECriarPasta($wb_path_log_arquivo)){
                throw new Exception("Erro ao tentar criar pata para assinaturas");
            }
            $fws_['arquivo_destino'] = $wb_path_log_arquivo.$fws_['arquivo_gerado'];

            if (move_uploaded_file($_FILES['arquivo']['tmp_name'], $fws_['arquivo_destino'])) {
                echo "Arquivo carregado com sucesso!<br>";
            } else {
                //echo $_FILES['arquivo']['tmp_name'].', '.$fws_['arquivo_destino'];
                echo "Ocorreu um erro ao carregar o arquivo: " . $_FILES["file"]["error"];
                throw new Exception("Ocorreu um erro durante upload do arquivo.");
            }

            $objAr->setArquivoUpload($_FILES['arquivo']['name']);
            $objAr->setArquivoGerado($fws_['arquivo_gerado']);

            $objAr->setAll($fws_);
            //$objAr->substituirArquivo($debug);
            $objAr->salvar($debug);
            
        } catch (OKException $exc) {
            $msg = $exc->getMessage();
            $_SESSION['MSG'] = $msg;
            $url = WB_URL_VIEW . "?content=view/admin/oficina-cadastro-ok.php&COD_OFICINA=".$objOf->getCodOficina();
        } catch (Exception $exc) {
            $msg = $exc->getMessage();
            $_SESSION['MSG'] = $msg;
            $url = WB_URL_VIEW . "?content=view/admin/oficina-cadastro-ok.php&COD_OFICINA=".$objOf->getCodOficina();
        }
        
        echo "<br><a href='{$url}'>Redirecionando... <h3>{$msg}</h3></a><br>";
        if (!$debug) {
            echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $url . "\">";
        }
    }
    
    /**@todo essa função é chamada ato de fazer o upload do programa na área de certificado da atividade ele também atualiza a tabela tab_arquivos, a mesma tabela usada em GerneicarMidiaUpload */
    public function salvarAnexoPrograma($fws_, $debug = false) {
        try {
            $objOf = new Oficina();
            $objOf->setCodOficina($fws_['codOficina']);
            
            if(!$objOf->OficinaExiste($debug)){
                throw new Exception('Código de oficina não cadastrado.');
            }
            
            try {
                $objAr = new Arquivo();
                $objAr->setCodOficina($objOf->getCodOficina());
                $objAr->setTipoArquivo('PROGRAMA');
                $objAr->carregarArquivoByIdOficinaPrograma($debug);
                $objAr->setVersao($objAr->getVersao()+1);
            } catch (Exception $exc) {}
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
            if ($_FILES['arquivo']['type'] != 'application/pdf') {
                throw new Exception("Extensão de arquivo somente tipo PDF.");
            }
            
            $extensao = substr(strrchr($_FILES['arquivo']['name'], "."), 1);
            $fws_['arquivo_gerado']  = 'programa-'.numberPad($objAr->getCodOficina(), 7).'.'.$extensao;
            if(!verificarECriarPasta(WB_PATH_LOG_CERTIFICADOS)){
                throw new Exception("Erro ao tentar criar pata para certificados");
            }
            $wb_path_log_arquivo = WB_PATH_LOG_CERTIFICADOS.'/verso/';
            if(!verificarECriarPasta($wb_path_log_arquivo)){
                throw new Exception("Erro ao tentar criar pata para verso");
            }

            $fws_['arquivo_destino'] = $wb_path_log_arquivo.$fws_['arquivo_gerado'];

            if (move_uploaded_file($_FILES['arquivo']['tmp_name'], $fws_['arquivo_destino'])) {
                echo "Arquivo carregado com sucesso!<br>";
            } else {
                //echo $_FILES['arquivo']['tmp_name'].', '.$fws_['arquivo_destino'];
                echo "Ocorreu um erro ao carregar o arquivo: " . $_FILES["file"]["error"];
                throw new Exception("Ocorreu um erro durante upload do arquivo.");
            }

            $objAr->setArquivoUpload($_FILES['arquivo']['name']);
            $objAr->setArquivoGerado($fws_['arquivo_gerado']);

            $objAr->setAll($fws_);
            $objAr->substituirArquivo($debug);
            
        } catch (OKException $exc) {
            $msg = $exc->getMessage();
            $_SESSION['MSG'] = $msg;
            $url = WB_URL_VIEW . "?content=view/admin/oficina-cadastro-ok.php&COD_OFICINA=".$objOf->getCodOficina();
        } catch (Exception $exc) {
            $msg = $exc->getMessage();
            $_SESSION['MSG'] = $msg;
            $url = WB_URL_VIEW . "?content=view/admin/oficina-cadastro-ok.php&COD_OFICINA=".$objOf->getCodOficina();
        }
        
        echo "<br><a href='{$url}'>Redirecionando... <h3>{$msg}</h3></a><br>";
        if (!$debug) {
            echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $url . "\">";
        }
    }
    
    public function abrirPrograma($fws_, $debug = false) {
        $id=$fws_['cod'];
        
        $filepath = WB_PATH_LOG_CERTIFICADOS.'verso/';
//        $arquivo = $id.".pdf";
        $arquivo = 'programa-'.numberPad($id, 7).'.pdf';
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
            echo "<script language=\"Javascript\"> alert('Arquivo não existe!'); window.close();</script>";
        }
    }
    
    public function excluirArqPrograma($fws_, $debug = false) {
        $id=$fws_['codArqPrograma'];
        
        $filepath = WB_PATH_LOG_CERTIFICADOS.'verso/';
//        $arquivo = $id.".pdf";
        $arquivo = 'programa-'.numberPad($id, 7).'.pdf';
//        echo $filepath.$arquivo."<br>";
//        exit();
        if (file_exists($filepath.$arquivo)) {
            if(!unlink($filepath.$arquivo)){
                throw new Exception("Não foi possível remover o arquivo fisicamente.");
            }
        }else{
            echo "<script language=\"Javascript\"> alert('Arquivo não existe!'); window.close();</script>";
        }
    }
    
    public function excluirArqAssCoordenador($fws_, $debug = false) {
        $id=$fws_['codArqCoordenador'];
        
        $filepath = WB_PATH_LOG_CERTIFICADOS.'../assinaturas/';
        $arquivo = 'coordenador-'.numberPad($id, 7).'.jpg';
//        echo $filepath.$arquivo."<br>";
//        exit();
        if (file_exists($filepath.$arquivo)) {
            if(!unlink($filepath.$arquivo)){
                throw new Exception("Não foi possível remover o arquivo fisicamente.");
            }
        }else{
            echo "<script language=\"Javascript\"> alert('Arquivo não existe!'); window.close();</script>";
        }
    }
}

<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/inscritos-seguranca.php");
#-------------------------------------------
$debug = false;
try {
    $objI = new Inscrito();
    $objI->setCodInscrito($fws_['cod_inscrito']);

    if (!$objI->inscritoExiste($debug)) {
        throw new Exception('Código de inscrito não cadastrado.');
    }

    $obj = new Trabalho();
    $obj->setCodInscrito($fws_['cod_inscrito']);
    if ($fws_['cod_trabalho'] != '') {
        $obj->setCodTrabalho($fws_['cod_trabalho']);
        $obj->getTrabalhoById($debug);
        $obj->setVersao($obj->getVersao() + 1);
    } else {
        $obj->carregarCodNovoTrabalho();
        $obj->setVersao($obj->getVersao() + 1);
        $obj->setNovo('1');
    }

    if (TRABALHO_ANEXA_RESUMO==true) {
        try {
            $tiposPermitidos = array('pdf','doc', 'docx', 'rtf');
            $mimeTypesAssociados = array(
                'pdf'  => 'application/pdf',
                'doc'  => 'application/msword',
                'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'rtf'  => 'text/rtf'
            );

            /*verificar se arquivo foi submetido*/
            if($_FILES['arquivo']['name']){
    //                        if ($_FILES['arquivo']['size'] > 5242880){
    //                           throw new Exception("Não é permitido arquivos maiores que 5MB.");
    //                        }
    //                        if ($_FILES['arquivo']['size'] > 36700160 ){
    //                        if ($_FILES['arquivo']['size'] > 73400320 ){
                if ($_FILES['arquivo']['size'] > 104857600 ){
                   throw new Exception("Não é permitido arquivos maiores que 90MB.");
                }
                if ($_FILES['arquivo']['name'] == '') {
                    throw new Exception("Arquivo não selecionado.");
                }
                if ($_FILES['arquivo']['tmp_name'] == '') {
                    throw new Exception("Arquivo temporário não encontrado.");
                }

                $fileMimeType = mime_content_type($_FILES['arquivo']['tmp_name']);
                $extensao = array_search($fileMimeType, $mimeTypesAssociados);
                if (!$extensao || !in_array($extensao, $tiposPermitidos)) {
                    throw new Exception("Extensão de arquivo não permitida.");
                }

                if(!file_exists(WB_PATH_LOG_TRABALHO)){
                    mkdir(WB_PATH_LOG_TRABALHO);
                }
                $extensao = ".".$extensao;
                $fws_['arquivo_gerado']  = numberPad($obj->getCodInscrito(), 6). "_" . numberPad($obj->getCodTrabalho(), 6)  ."_" . $obj->getVersao() . $extensao;
                $fws_['arquivo_destino'] = WB_PATH_LOG_TRABALHO.$fws_['arquivo_gerado'];

                if (!move_uploaded_file($_FILES['arquivo']['tmp_name'], $fws_['arquivo_destino'])) {

                    throw new Exception("Ocorreu um erro durante upload do arquivo.");
                }

                $obj->setArquivoUpload($_FILES['arquivo']['name']);
                $obj->setArquivoGerado($fws_['arquivo_gerado']);
                $text = $fws_['arquivo_gerado'].'- tamanho: '.$_FILES['arquivo']['size'];
                executarLog($text, 'tamanho-anexo-log', true);

            }else{
    //                        $msgErroTrabalho = "Arquivo não foi enviado.";
            }
        } catch (Exception $exc) {
            $msgErroTrabalho = $exc->getMessage();
        }
    }

    if ($obj->getStatus() == '') {
        $obj->setStatus('ATIVADA');
    }
    $obj->setAll($fws_);
    $obj->salvar($debug);
} catch (OKException $exc) {
    $msg = $msgErroTrabalho . "<br>" . $exc->getMessage();
    $_SESSION['MSG']=$msg;
    $url = WB_URL_VIEW . "?content=view/admin/inscritos-cadastro-ok.php&cod_inscrito=".$fws_['cod_inscrito']."&cod_trabalho=".$fws_['cod_trabalho'];
} catch (Exception $exc) {
    $msg = $msgErroTrabalho . "<br>" . $exc->getMessage();
    $_SESSION['MSG']=$msg;
    $url = WB_URL_VIEW . "?content=view/admin/inscritos-cadastro-ok.php&cod_inscrito=".$fws_['cod_inscrito']."&cod_trabalho=".$fws_['cod_trabalho'];
}

echo "<br><a href='{$url}'>Redirecionando... {$msg}</a><br>";
if (!$debug) {
    echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $url . "\">";
}#echo $Var_Sql."<br>".$Var_Campos."<br>";


<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/avaliacao_inscrito/avaliacao-inscritos-seguranca.php");
$debug = false;

try {
    
    if ($fws_['acao'] != "Avaliação") {
        throw new Exception("Operação-Não-Executada,-Acao-Diferente-Ativar--");
    }
    $fws_['avaliacao'] = 'APROVADO';
    $objTrabalhoHistorico = new HistoricoAvaliacaoPremiacao();
    $objTrabalhoHistorico->setCodAreaTematica($fws_['cod_area_tematica']);
    $objTrabalhoHistorico->setCodTrabalho($fws_['cod_trabalho']);
    $objTrabalhoHistorico->setCodInscrito($fws_['cod_inscrito']);
    $objTrabalhoHistorico->setCodAvaliador($_SESSION['_cod_usuario']);
    $objTrabalhoHistorico->setAvaliacao($fws_['formato_texto']);
    $objTrabalhoHistorico->setAvaliacao($fws_['avaliacao']);
    $objTrabalhoHistorico->setCriterio1($fws_['criterio_1']);
    $objTrabalhoHistorico->setCriterio2($fws_['criterio_2']);
    $objTrabalhoHistorico->setCriterio3($fws_['criterio_3']);
    $objTrabalhoHistorico->setCriterio4($fws_['criterio_4']);
    $objTrabalhoHistorico->setObservacao($fws_['observacao']);
    
    $objTrabalho = new Trabalho();
    $objTrabalho->setCodInscrito($fws_['cod_inscrito']);
    $objTrabalho->setCodTrabalho($fws_['cod_trabalho']);
    if(!$objTrabalho->getTrabalhoById($debug)){
        throw new Exception("Não foi possível devolver para correção");
    }
    
    /*INICIO UPLOAD TRABALHO*/
    if (TRABALHO_ANEXA_RESUMO==true) {
        if($_FILES['arquivo']['name']){
            if ($_FILES['arquivo']['size'] > 3597152){
               throw new Exception("Não é permitido arquivos maiores que 3MB.");
            }
            if ($_FILES['arquivo']['name'] == '') {
                throw new Exception("Arquivo não selecionado.");
            }
            if ($_FILES['arquivo']['tmp_name'] == '') {
                throw new Exception("Arquivo temporário não encontrado.");
            }
            $extensao = strtolower(substr($_FILES['arquivo']['name'], -4));
            if ($extensao != '.doc' and $extensao != 'docx' and $extensao != '.rtf') {
                throw new Exception("Extensão de arquivo não permitida.");
            }
            if($extensao == 'docx'){
                $extensao = ".".$extensao;
            }
            $objTrabalho->setVersao($objTrabalho->getVersao()+1);
            $fws_['arquivo_gerado']  = numberPad($objTrabalho->getCodInscrito(), 6). "_" . numberPad($objTrabalho->getCodTrabalho(), 6)  ."_" . $objTrabalho->getVersao() ."_AV". $extensao;
            $fws_['arquivo_destino'] = WB_PATH_LOG_TRABALHO.$fws_['arquivo_gerado'];

            if (!move_uploaded_file($_FILES['arquivo']['tmp_name'], $fws_['arquivo_destino'])) {
                throw new Exception("Ocorreu um erro durante upload do arquivo.");
            }

            $objTrabalho->setArquivoUpload($_FILES['arquivo']['name']);
            $objTrabalho->setArquivoGerado($fws_['arquivo_gerado']);

        }else{
             $msgErroTrabalho = "Arquivo não substituído.";
        }
    }
    /*FIM UPLOAD TRABALHO*/
    
    if ($fws_['avaliacao'] == 'LIBERAR') {
        $fws_['detalhes'] = 2;
        $objTrabalhoHistorico->getHistoricoAvaliacaoByIdAvaliador($debug);
        $objTrabalhoHistorico->desativarAvaliacoesAvaliador($debug);
        $MSG = "Trabalho liberado para outro avaliador";
    }else if ($fws_['avaliacao'] == 'DEVOLVIDO') {
        $fws_['detalhes'] = 2;
        try {
            /*devolvendo para o inscrito*/
            $objTrabalho->setFlgEnviadoAvaliacaoPremiacao('0');
            $objTrabalho->enviarAvaliacao($debug);
        } catch (OKException $exc){
            $objTrabalhoHistorico->salvar($debug);
        }
    }else{
        try {
            $objTrabalho->enviarAvaliacao($debug);
        } catch (OKException $exc){
            try {
                $objTrabalhoAvaliacao = new TrabalhoAvaliacaoPremiacao();
                $objTrabalhoAvaliacao->setCodAreaTematica($fws_['cod_area_tematica']);
                $objTrabalhoAvaliacao->setCodTrabalho($fws_['cod_trabalho']);
                $objTrabalhoAvaliacao->setCodInscrito($fws_['cod_inscrito']);
                $objTrabalhoAvaliacao->setCodAvaliador($_SESSION['_cod_usuario']);
                $objTrabalhoAvaliacao->setAvaliacao($fws_['formato_texto']);
                $objTrabalhoAvaliacao->setAvaliacao($fws_['avaliacao']);
                $objTrabalhoAvaliacao->setCriterio1($fws_['criterio_1']);
                $objTrabalhoAvaliacao->setCriterio2($fws_['criterio_2']);
                $objTrabalhoAvaliacao->setCriterio3($fws_['criterio_3']);
                $objTrabalhoAvaliacao->setCriterio4($fws_['criterio_4']);
                $objTrabalhoAvaliacao->salvar($debug);
            } catch (OKException $exc){
                $objTrabalhoHistorico->salvar($debug);
            }
        }
    }

} catch (Exception $exc) {
    $MSG = $exc->getMessage();
} catch (OKException $exc) {
//    echo $exc->getMessage();
    if($MSG){
        $MSG = "Operação-de-" . $fws_['acao'] . "-Executada-Codigo-trabalho:" . $fws_['cod_trabalho'] . "--";
    }
}
$CONTENT = "view/avaliacao_inscrito/avaliacao-inscritos-listagem-por-avaliador.php";
$URL = WB_URL_VIEW . "?content=" . $CONTENT  . "&cod_area_tematica=" . $fws_['cod_area_tematica']. "&detalhes=" . $fws_['detalhes']. "&MSG=" . $MSG;
#--------------------------------------------------------------

FWA_Mostrar_Mensagem($MSG);
#--------------------------------------------------------------
if ($debug == true) {
    echo $MSG_Imprimir;
    echo "<br><a href='{$URL}'>{$URL}</a><br>";
} else {
    echo "<br>Aguarde um momento - " . date("d/m/Y - H:i:s");
    echo "<br>Gravando seus dados no sistema<BR>";
    echo "<br>Este procedimento pode demorar algun tempo caso a lista de trabalhos a serem avaliados ainda seja grande<BR>";
    echo "<br><a href='{$URL}'>{$URL}</a><br>";
    echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $URL . "\">";
}
?>


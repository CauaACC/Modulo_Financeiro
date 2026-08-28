<?php
require_once(dirname(__FILE__)."/../../view/admin/consulta-dados-congresso-seguranca.php");

$diretorio = WB_PATH_LOG_VIDEO;
//echo $diretorio.'-';
//exit();
$zip = new ZipArchive();
// Cria o Arquivo Zip, caso não consiga exibe mensagem de erro e finaliza script
if ($zip->open($diretorio.'nome_arquivo_zip.zip', ZIPARCHIVE::CREATE) == TRUE) {
// Insere os arquivos que devem conter no arquivo zip
    $zip->addFile($diretorio . '006334_002529_1.mp4', '006334_002529_1.mp4');
    $zip->addFile($diretorio . '006344_002751_1.mp4', '006344_002751_1.mp4');

    echo 'Arquivo criado com sucesso.'.$diretorio;
} else {
    exit('O Arquivo não pode ser criado.');
}

// Fecha arquivo Zip aberto
$zip->close();

<?php
/**
 * Valida o tipo real do arquivo enviado em upload comparando o MIME detectado
 * (via finfo no conteudo binario) com a extensao do nome.
 *
 * Importante: NAO confia em $_FILES['arquivo']['type'], que vem do navegador
 * e pode ser forjado. Tambem nao confia so em pathinfo da extensao do nome.
 *
 * Em caso de rejeicao:
 *   - grava log em 'upload_errors' com nome do arquivo, extensao, MIME real e IP
 *   - lanca Exception com mensagem amigavel (sem expor MIME interno ao usuario)
 *
 * @param string $tmpPath              Caminho temporario do upload ($_FILES['arquivo']['tmp_name'])
 * @param string $nomeOriginal         Nome original enviado ($_FILES['arquivo']['name']) - para o log
 * @param array  $extensoesPermitidas  Lista de extensoes aceitas (ex: ['pdf','doc','docx'])
 * @return string  A extensao validada (lowercase, sem ponto)
 * @throws Exception  Se a extensao nao estiver na lista ou o MIME real nao bater
 */
function validarTipoArquivoUpload($tmpPath, $nomeOriginal, array $extensoesPermitidas) {
    $extensao = strtolower(pathinfo($nomeOriginal, PATHINFO_EXTENSION));

    // Whitelist de MIMEs reais aceitos por extensao.
    // Office novo (docx/pptx/pptm) sao ZIPs; alguns servidores reportam application/zip.
    // Office antigo (doc/ppt) sao OLE Compound; finfo pode reportar variantes.
    // JSON pode vir como application/json ou text/plain dependendo do conteudo.
    $mapaMimes = array(
        'pdf'  => array('application/pdf'),
        'doc'  => array('application/msword', 'application/CDFV2', 'application/x-ole-storage', 'application/vnd.ms-office'),
        'docx' => array('application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip'),
        'ppt'  => array('application/vnd.ms-powerpoint', 'application/CDFV2', 'application/x-ole-storage', 'application/vnd.ms-office'),
        'pptx' => array('application/vnd.openxmlformats-officedocument.presentationml.presentation', 'application/zip'),
        'pptm' => array('application/vnd.ms-powerpoint.presentation.macroEnabled.12', 'application/zip'),
        'jpg'  => array('image/jpeg'),
        'jpeg' => array('image/jpeg'),
        'jfif' => array('image/jpeg'),
        'png'  => array('image/png'),
        'gif'  => array('image/gif'),
        'json' => array('application/json', 'text/plain', 'text/x-c', 'text/x-c++'),
    );

    if (!in_array($extensao, $extensoesPermitidas, true)) {
        // Loga e lanca generico (sem expor lista interna ao usuario)
        if (function_exists('executarLog')) {
            executarLog(sprintf(
                "REJEITADO extensao_invalida arquivo=\"%s\" extensao=\"%s\" permitidas=\"%s\" ip=%s",
                str_replace('"', "'", (string) $nomeOriginal),
                $extensao,
                implode(',', $extensoesPermitidas),
                isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '-'
            ), 'upload_errors', true);
        }
        throw new Exception("Extensão de arquivo não permitida.");
    }

    if (!is_string($tmpPath) || !is_file($tmpPath)) {
        throw new Exception("Arquivo temporário não encontrado.");
    }

    $mimeReal = '';
    if (class_exists('finfo')) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeReal = (string) $finfo->file($tmpPath);
    } elseif (function_exists('mime_content_type')) {
        $mimeReal = (string) mime_content_type($tmpPath);
    }

    // Se nao conseguiu detectar (raro), loga aviso mas nao bloqueia (fallback para extensao)
    if ($mimeReal === '') {
        if (function_exists('executarLog')) {
            executarLog(sprintf(
                "AVISO mime_indetectavel arquivo=\"%s\" extensao=\"%s\" ip=%s",
                str_replace('"', "'", (string) $nomeOriginal),
                $extensao,
                isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '-'
            ), 'upload_errors', true);
        }
        return $extensao;
    }

    $aceitos = isset($mapaMimes[$extensao]) ? $mapaMimes[$extensao] : array();
    if (!in_array($mimeReal, $aceitos, true)) {
        if (function_exists('executarLog')) {
            executarLog(sprintf(
                "REJEITADO mime_divergente arquivo=\"%s\" extensao=\"%s\" mime_real=\"%s\" mimes_aceitos=\"%s\" ip=%s",
                str_replace('"', "'", (string) $nomeOriginal),
                $extensao,
                $mimeReal,
                implode(',', $aceitos),
                isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '-'
            ), 'upload_errors', true);
        }
        throw new Exception("O conteúdo do arquivo não corresponde à extensão .{$extensao}.");
    }

    return $extensao;
}

/**
 *
 */
function startUpload(tagName){    
    document.getElementById(tagName+"_process").style.display = "block";
    document.getElementById(tagName+"_success").style.display = "none";
    return true;
}

/**
 *
 */
function stopUpload(success, valido, mensagem, tagName, caminho, arquivo, existe, $arquivoExistente){    
    var result = "";
    var link = "";
    if (success == 1){
        result = "<span class='msg'>O Arquivo foi carregado com sucesso!</span><br/>";
        link = "<div id='success_arq'>Arquivo Carregado. Para visualizar o arquivo, <a href='"+caminho+arquivo+"' target='_index'>clique aqui</a>.</div>";
    } else {
        if(valido == 1) {
            result = "<span class='emsg'>Ocorreu um erro ao carregar o arquivo! Por favor, tente novamente.</span><br/>";            
        } else {
            result = mensagem;
        }
        if(existe == 1) {
            link = "<div id='alert_arq'>O novo arquivo não foi carregado. Para visualizar o antigo, " +
                   "<a href='"+caminho+$arquivoExistente+"' target='_index'>clique aqui</a>.</div>";
            arquivo = $arquivoExistente;
        } else {
            link = "<div id='alert_arq'>O arquivo ainda não foi carregado.</div>";
        }
    }
    document.getElementById(tagName+"_process").style.display = "none";
    document.getElementById(tagName+"_success").style.display = "block";
    document.getElementById(tagName+"_success").innerHTML = result;
    document.getElementById(tagName+"_link").innerHTML = link;
    document.getElementById("arq_"+tagName).value = arquivo;
    return true;
}
        
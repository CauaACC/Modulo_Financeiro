<?php

function isEntradaSegura($entrada, $tipo = 'email') {
    switch ($tipo) {
        case 'email':
            // Só letras, números, pontos, traço, underline e arroba
            return preg_match('/^[a-zA-Z0-9._@-]+$/', $entrada);
        case 'cpf':
            // Apenas números, ponto e traço
            return preg_match('/^[0-9.-]+$/', $entrada);
        case 'senha':
            // Aceita letras, números e caracteres especiais básicos, mas **não** aceita <, >, ", '
            return !preg_match('/[<>"\']/', $entrada);
        case 'numeros':
            // Apenas números
            return preg_match('/^[0-9]+$/', $entrada);            
        default:
            // Para outros casos, permite só letras e números
            return preg_match('/^[a-zA-Z0-9]+$/', $entrada);
    }
}

function utf8Convert($stringTxt){
    if (mb_detect_encoding($stringTxt . 'x', 'UTF-8, ISO-8859-1') == 'UTF-8') {
        return utf8_decode($stringTxt);
    }
}
/**
 * 
 * @param string $nomeArquivo
 * @param type $colunas
 * @param type $linhas
 * @param type $debug
 */
function gerarArquivoExcel($nomeArquivo, $colunas, $linhas, $debug = false) {

    $nomeArquivo = $nomeArquivo . "-" . date('d-m-Y') . "-" . date('H') . "h" . date('i');
    $file_ending = "xls";
    // header("Content-Type: application/xls; charset=ISO-8859-1");
    header("Content-Disposition: attachment; filename=$nomeArquivo.xls");
    header("Pragma: no-cache");
    header("Expires: 0");
    $sep = "\t";
    foreach ($colunas as $name) {
        print ($name . $sep);
    }
    print "\n";
    foreach ($linhas as $objL) {
        $schema_insert = "";
        foreach ($colunas as $name) {
            $value = $objL->getPadrao($name);
            $value = trim($value);
            if($name=='e_mail'){
                $value = strtolower($value);
            }
            if (!isset($value)){
                $schema_insert .= "NULL" . $sep;
            } else if ($value != ""){
                $value = utf8_decode ($value);
                $schema_insert .= "$value" . $sep;
            }else{
                $schema_insert .= "" . $sep;
            }
        }
        $schema_insert = str_replace($sep . "$", "", $schema_insert);
        $schema_insert = preg_replace("/\r\n|\n\r|\n|\r/", " ", $schema_insert);
        $schema_insert .= "\t";
        print(trim($schema_insert));
        print "\n";
    }
    print("\n");
}
/**
 * 
 * @param string $nomeArquivo
 * @param type $colunas
 * @param type $linhas
 * @param type $debug
 */
function gerarArquivoEditalHtml($nomeArquivo, $colunas, $linhas, $debug = false) {
    $nomeArquivo = $nomeArquivo . "-data-" . date('d-m-Y') . "-" . date('H') . "h" . date('i');
    header("Content-Type: text/html; charset=ISO-8859-1");
    header("Content-Disposition: attachment; filename=$nomeArquivo.doc");
    header("Pragma: no-cache");
    header("Expires: 0");
?>
<html><head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<style>
table {
  --width: 650px;
  width: 1050px;
  border: 1px solid black;
  border-collapse: collapse;
}
th, td {
  border: 1px solid black;
}
th, td {
  padding: 5px;
  text-align: left;
}
.categoria-header {
  background-color: #f2f2f2;
  font-weight: bold;
}
.sequencia, .classificacao, .status, .obs {
  font-family: sans-serif, Arial;
  font-size: 14px;
}
.sequencia { width: 10%; }
.cod_inscrito { width: 15%; font-size: 14px; }
.nome_inscrito { width: 25%; font-size: 14px; }
.arquivo_gerado { width: 20%; font-size: 14px; }
.pontos_total { width: 15%; font-size: 14px; }
.classificacao { width: 10%; }
.status { width: 5%; }

.classificacao, .status, .obs {
  border: 1px solid black;  /* Adicionando borda */
  text-align: center;       /* Alinhamento centralizado */
  font-size: 14px;
}
</style>
</head>
<body>
<?php 
    if($linhas){
        $contarCidade     = 0;
        $contarConvocados = 0;
        $cargo_atual      = '';
        $cidade_ativ_atual = '';

        foreach ($linhas as $idx => $objL) {
             dd($objL);
            // $tituloEvento = $objL->getTituloEvento();
            $tituloEvento = $objL->getAbrevEvento();
            $cargo  = $objL->getNomePerfil();
            $cidade_ativ = $objL->getNomeOficina();

            // Quando mudar de cargo
            if($cargo_atual!=$cargo){
                $contarConvocados = 0;
                // echo "<h2>Complemento - ". date('d/m/Y') ." Convocação ". $cargo ." - ". $tituloEvento ."</h2>";
                echo "<h3>Complemento - ". date('d/m/Y') ." Convocação ". $cargo ."</h2>";
            }
            $cargo_atual = $cargo;

            // Quando mudar de cidade → fecha a tabela anterior
            if ($cidade_ativ_atual != '' && $cidade_ativ_atual != $cidade_ativ) {
                echo "</tbody></table><br>";
            }

            // Quando for nova cidade → abre nova tabela
            if($cidade_ativ_atual!=$objL->getNomeOficina()){
                $contarCidade++;
                echo "<h3>$cidade_ativ - Cargo: $cargo</h3>";
                ?>
                <table width="650px">
                    <thead><tr class="categoria-header">
                        <th width="1%">Sequência</th>
                        <th width="1%">Id. Público</th>
                        <th width="600%">Nome Inscrito</th>
                        <th width="5%">Pontos<br> na Avaliação</th>
                        <th width="2%">Classificação</th>
                        <th width="1%">Status</th>
                    </tr></thead>
            <?php
            }
            $cidade_ativ_atual = $cidade_ativ;
            ?>
            <tr style="border: 1px solid black;">
                <td></td>
                <td><?php echo $objL->getIdPublico(); ?></td>
                <td><?php echo $objL->getNomeInscrito(); ?></td>
                <td class="classificacao"><?php echo $objL->getPontosTotalConfirmado(); ?></td>
                <td class="classificacao"0</td>
                <td class="status">
                    <?php
                    $status = $objL->getClassificacaoStatus();
                    echo $status=='H' ? 'Habilitado' : 'Não Habilitado';
                    ?>
                </td>
            </tr>
            <?php
            $contarConvocados++; 

            // Se for o último registro → fecha a tabela
            if (!isset($linhas[$idx + 1]) || $linhas[$idx + 1]->getNomeOficina() != $cidade_ativ) {
                echo "</tbody></table><br>";
            }
        }
        ?>    
        <br><p>Total de Cidades Listadas: <?php echo $contarCidade; ?></p>
        <p>Total de Convocados Cargo Psicólogos: <?php echo $contarConvocados; ?></p>
        </body></html>
    <?php
    }
}

/**
 * 
 * @param string $nomeArquivo
 * @param type $colunas
 * @param type $linhas
 * @param type $debug
 */
function gerarArquivoHtml($nomeArquivo, $colunas, $linhas, $debug = false) {
    $nomeArquivo = $nomeArquivo . "-data-" . date('d-m-Y') . "-" . date('H') . "h" . date('i');
    header("Content-Type: text/html; charset=ISO-8859-1");
    header("Content-Disposition: attachment; filename=$nomeArquivo.html");
    header("Pragma: no-cache");
    header("Expires: 0");
    
//    $sep = "</font></td><td><font size=2 face=arial>";
    $sep = "";
    $sepCabecalho = "</font></b></td><td><b><font size=2 face=arial>";

    // echo "<NewDataSet>";
    if($colunas){
    $colunas = tratarColunas($colunas);
    foreach ($colunas as $name) {
        if(COD_EVENTO==2){
            $name = str_replace('oficina', "minicurso", $name);
        }else{
            $name = str_replace('oficina', "atividade", $name);
        }
//        print (str_replace("_", " ", $name) . $sepCabecalho);
    }}else{
        //echo "Nenhuma coluna encontrada";
    }
//    print "</td></tr>\n";
    if($linhas){
    foreach ($linhas as $objL) {
        echo "<Table>";
        $schema_insert = "";
//        dd($colunas);
        foreach ($colunas as $name) {
            $value = $objL->getPadrao($name);
            if (!isset($value)){
                if($name=='fone_contato_ddd'){
                    $value='43';
                }else if($name=='fone_contato'){
                    $value='9999-9999';
                }else if($name=='data_nascimento'){
                    $value = '1999-01-01';
                }else if($name=='rg_data_emissao'){
                    $value = '1999-01-01';
                }else{
                    $value = 'null';
                    $value = 'NULL';
                }
                $schema_insert .= "<".$name.">".$value. "</".$name.">";
            }elseif ($value != ""){
                if($name=='nome_inscrito' or $name=='Nome' or $name=='como_quero_ser_chamado'){
                    $value = textoMaiusculoMinusculo($value);
                }
                if($name=='email_inscrito' or $name=='e-mail' or $name=='E-mail' or $name=='email' or $name=='Email'){
                    $value = minusculo($value);
                }
                if($name=='cpf_inscrito'){
                    if(strlen($value)>14 and (!is_numeric($value))){
                        $value = '11111111111';
                    }
                }  
                if($name=='data_nascimento'){
                    // $value = formatDateHoraTrans($value, 'DD/MM/YYYY', 'YYYY-MM-DD');
                }
                if($name=='nm_banco'){
                    $value = 'NULL';
                }
                $value = utf8_decode($value);
                $schema_insert .= "<".$name.">".$value. "</".$name.">";
            }else{
                $value='';
                if($name=='fone_contato_ddd'){
                    $value='43';
                }
                if($name=='fone_contato'){
                    $value='9999-9999';
                }
                $schema_insert .= "<".$name.">".$value."</".$name.">";
            }
        }
        //$schema_insert = str_replace($sep . "$", "", $schema_insert);
        $schema_insert = preg_replace("/\r\n|\n\r|\n|\r/", " ", $schema_insert);
        $schema_insert .= "\t";
        print(trim($schema_insert));
        print "</Table>\n";
    }
    }else{
        echo "Lista está vazia.";
    }
    // print("</NewDataSet>\n");
}
function tratarColunas($colunas){
    #museu
    if (in_array(COD_EVENTO, array(35,52,72,79,90,105,119))) {    
        $lista_itens = [
            "titulo_evento",
            "gestor",
            "projeto",
            "url_prefixo",
            "como_quero_ser_chamado",
            "profissao_atuacao",
            "tipo_instituicao",
            "instituicao",
            "receber_inscricao",
            "total_atividades",
            "total_oficinas",
            "total_minicursos",
            "total_simposios",
            "total_outras_atividades",
            "total_trabalhos",
            "instituicao_trabalho",
            "total_avaliacoes",
            "total_boletos",
            "cod_isento",
            "total_perfis_isentos",
            "cod_voucher",
            "total_vouchers",
            "total_pedidos",
            "flg_presenca_atividade",
            "flg_presenca_oficina",
            "ordem_atividade",
            "ordem_oficina"
        ];
        $colunas = array_diff($colunas, $lista_itens);
    }
    #editais
    if (in_array(COD_EVENTO, array(37,38,46,42,44,50,69,115))) {
        $lista_itens = [
            "gestor",
            "e_mail_2",
            "url_prefixo",
            "como_quero_ser_chamado",
            "profissao_atuacao",
            "tipo_instituicao",
            "instituicao",
            "receber_inscricao",
            "total_atividades",
            "total_oficinas",
            "total_minicursos",
            "total_simposios",
            "total_outras_atividades",
            "total_trabalhos",
            "instituicao_trabalho",
            "total_avaliacoes",
            "total_boletos",
            "cod_isento",
            "total_perfis_isentos",
            "cod_voucher",
            "total_vouchers",
            "total_pedidos",
            "flg_presenca_atividade",
            "flg_presenca_oficina",
            "categoria_vaga",
            "titulo_trabalhp",
            "nome_co_autor_1",
            "nome_co_autor_2",
            "nome_co_autor_3",
            "nome_co_autor_4",
            "nome_co_autor_5",
            "nome_co_autor_6",
            "co_autores",
            "cod_area_tematica",
            "titulo_area_tematica",
            "ordem_area_tematica",
            "co_autores",
            "ordem_atividade",
            "total_pagamentos",
            "valor_pagamentos",
            "valor_total_pedidos",
            "dat_incl",
            "pagamento",
            "status_pagamento",
            "ordem_oficina",
            "url_cobranca",
            "orgao_expedidor",
            "pais",
            "db_conta_corrente_dv",
            "db_agencia_dv",
            "arquivo_gerado",
            "email_2",
            "categoria_agrupada"
        ];
        $colunas = array_diff($colunas, $lista_itens);
    }
    if (in_array(COD_EVENTO, array(82,88))) {
        $lista_itens = ['tipo_instituicao','instituicao','total_pagamentos','cod_isento','valor_pagamentos','cod_voucher','url_cobranca','data_ultimo_login','sucesso','tentantivas_login','status_pagamento'];
        $colunas = array_diff($colunas, $lista_itens);
    }
    if (in_array(COD_EVENTO, array(115))) {
        $lista_itens = ['recurso_trabalho_solicitar','recurso_trabalho','recurso_trabalho_txt','pontos_total','pontos_total_confirmado','data_inclusao_recurso','data_ultimo_login','sucesso','tentantivas_login'];
        $colunas = array_diff($colunas, $lista_itens);
    }

    // if (COD_EVENTO == 45) {
    //     $lista_itens = [
    //         "gestor",
    //         "url_prefixo",
    //         "como_quero_ser_chamado",
    //         "profissao_atuacao",
    //         "tipo_instituicao",
    //         "instituicao",
    //         "receber_inscricao",
    //         "total_atividades",
    //         "total_oficinas",
    //         "total_minicursos",
    //         "total_simposios",
    //         "total_outras_atividades",
    //         "total_trabalhos",
    //         "instituicao_trabalho",
    //         "total_avaliacoes",
    //         "total_boletos",
    //         "cod_isento",
    //         "total_perfis_isentos",
    //         "cod_voucher",
    //         "total_vouchers",
    //         "total_pedidos",
    //         "flg_presenca_atividade",
    //         "flg_presenca_oficina",
    //         "categoria_vaga",
    //         "titulo_trabalhp",
    //         "nome_co_autor_1",
    //         "nome_co_autor_2",
    //         "nome_co_autor_3",
    //         "nome_co_autor_4",
    //         "nome_co_autor_5",
    //         "nome_co_autor_6",
    //         "co_autores",
    //         "cod_area_tematica",
    //         "titulo_area_tematica",
    //         "ordem_area_tematica",
    //         "co_autores",
    //         "ordem_atividade",
    //         "total_pagamentos",
    //         "valor_pagamentos",
    //         "valor_total_pedidos",
    //         "dat_incl",
    //         "pagamento",
    //         "status_pagamento",
    //         "ordem_oficina",
    //         "url_cobranca",
    //         "orgao_expedidor",
    //         "pais",
    //         "db_conta_corrente_dv",
    //         "db_agencia_dv",
    //         "arquivo_gerado",
    //         "email_2",
    //         "categoria_agrupada"
    //     ];
    //     $colunas = array_diff($colunas, $lista_itens);
    // }
    return $colunas;
}
/**
 * 
 * @param string $nomeArquivo
 * @param type $colunas
 * @param type $linhas
 * @param type $debug
 */
function gerarArquivoTable2Excel($nomeArquivo, $colunas, $linhas, $debug = false) {

    $nomeArquivo = $nomeArquivo . "-data-" . date('d-m-Y') . "-" . date('H') . "h" . date('i');
    $file_ending = "xls";
//    header("Content-Type: application/xls; charset=ISO-8859-1");
    header("Content-Disposition: attachment; filename=$nomeArquivo.xls");
    header("Pragma: no-cache");
    header("Expires: 0");
    
    $sep = "</font></td><td><font size=2 face=arial>";
    $sepCabecalho = "</font></b></td><td><b><font size=2 face=arial>";
    
    echo "<table border=1><tr><td><b><font size=2 face=arial>";
    if($colunas){
        $colunas = tratarColunas($colunas);
    foreach ($colunas as $name) {
        if(COD_EVENTO==2){
            $name = str_replace('oficina', "minicurso", $name);
        }else{
            $name = str_replace('oficina', "atividade", $name);
        }
        print (str_replace("_", " ", $name) . $sepCabecalho);
    }}else{
        echo "Nenhuma coluna encontrada";
    }
    print "</td></tr>\n";
    if($linhas){
    foreach ($linhas as $objL) {
        echo "<tr><td>";
        $schema_insert   = "";
        $valor_pagamento = "";
        $cod_voucher     = "";
        $cod_isento      = "";
        foreach ($colunas as $name) {
            $value = $objL->getPadrao($name);
            /*escrevendo voucher na coluna de forma de pagamento*/
            if($name=='valor_pagamento'){ $valor_pagamento=$value; }
            if($name=='cod_voucher')    { $cod_voucher    =$value; }
            if($name=='cod_isento')     { $cod_isento     =$value; }
            if($name=='forma_pagamento'){
                if($cod_voucher!='' and $value==''){
                    $value = 'VOUCHER';
                }
                if($cod_isento!=''  and $value==''){
                    $value = 'ISENTADO';
                }
            }
            if (!isset($value)){
                $schema_insert .= "NULL" . $sep;
            }elseif ($value != ""){
                if($name=='nome_inscrito' or $name=='Nome' or $name=='como_quero_ser_chamado'){
                    $value = textoMaiusculoMinusculo($value);
                }
                if($name=='email_inscrito' or $name=='e-mail' or $name=='E-mail' or $name=='email' or $name=='Email' or $name=='url_cobranca'){
                    $value = minusculo($value);
                }
                $value = utf8_decode ($value);                
                $schema_insert .= "$value" . $sep;
            }else{
                $schema_insert .= "" . $sep;
            }
        }
        $schema_insert = str_replace($sep . "$", "", $schema_insert);
        $schema_insert = preg_replace("/\r\n|\n\r|\n|\r/", " ", $schema_insert);
        $schema_insert .= "\t";
        print(trim($schema_insert));
        print "</td></tr>\n";
    }
    }else{
        echo "Lista está vazia.";
    }
    print("</table>\n");
}
/**
 * 
 * @param string $nomeArquivo
 * @param type $colunas
 * @param type $linhas
 * @param type $debug
 */
function gerarArquivoTable2Xml($nomeArquivo, $colunas, $linhas, $debug = false) {

    $nomeArquivo = $nomeArquivo . "-data-" . date('d-m-Y') . "-" . date('H') . "h" . date('i');
    $file_ending = "xml";
    header("Content-Type: application/xml; charset=ISO-8859-1");
    header("Content-Disposition: attachment; filename=$nomeArquivo.xml");
    header("Pragma: no-cache");
    header("Expires: 0");
    
//    $sep = "</font></td><td><font size=2 face=arial>";
    $sep = "";
    $sepCabecalho = "</font></b></td><td><b><font size=2 face=arial>";

    echo "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>";
    echo "<NewDataSet>";
    if($colunas){
    $colunas = tratarColunas($colunas);
    foreach ($colunas as $name) {
        if(COD_EVENTO==2){
            $name = str_replace('oficina', "minicurso", $name);
        }else{
            $name = str_replace('oficina', "atividade", $name);
        }
//        print (str_replace("_", " ", $name) . $sepCabecalho);
    }}else{
        //echo "Nenhuma coluna encontrada";
    }
//    print "</td></tr>\n";
    if($linhas){
    foreach ($linhas as $objL) {
        echo "<Table>";
        $schema_insert = "";
//        dd($colunas);
        foreach ($colunas as $name) {
            $value = $objL->getPadrao($name);
            if (!isset($value)){
                if($name=='fone_contato_ddd'){
                    $value='43';
                }else if($name=='fone_contato'){
                    $value='9999-9999';
                }else if($name=='data_nascimento'){
                    $value = '1999-01-01';
                }else if($name=='rg_data_emissao'){
                    $value = '1999-01-01';
                }else{
                    $value = 'null';
                    $value = 'NULL';
                }
                $schema_insert .= "<".$name.">".$value. "</".$name.">";
            }elseif ($value != ""){
                if($name=='nome_inscrito' or $name=='Nome' or $name=='como_quero_ser_chamado'){
                    $value = textoMaiusculoMinusculo($value);
                    if (strpos($value, 'Transação Id:') !== false) {
                        $value = 'PagSeguro';
                    }else{
                    }
                }
                if($name=='email_inscrito' or $name=='e-mail' or $name=='E-mail' or $name=='email' or $name=='Email'){
                    $value = minusculo($value);
                }
                if($name=='cpf_inscrito'){
                    if(strlen($value)>14 and (!is_numeric($value))){
                        $value = '11111111111';
                    }
                }  
                if($name=='data_nascimento'){
                    /**alterado dia 05/04/2025 porque estava exportando no formato visual e deu erro na carga do hutec */
                    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                        $value = formatDateHoraTrans($value, 'DD/MM/YYYY', 'YYYY-MM-DD');
                    }
                }
                if($name=='nm_banco'){
                    $value = 'NULL';
                }
                $value = utf8_decode($value);
                $schema_insert .= "<".$name.">".$value. "</".$name.">";
            }else{
                $value='';
                if($name=='fone_contato_ddd'){
                    $value='43';
                }
                if($name=='fone_contato'){
                    $value='9999-9999';
                }
                $schema_insert .= "<".$name.">".$value."</".$name.">";
            }
        }
        //$schema_insert = str_replace($sep . "$", "", $schema_insert);
        $schema_insert = preg_replace("/\r\n|\n\r|\n|\r/", " ", $schema_insert);
        $schema_insert .= "\t";
        print(trim($schema_insert));
        print "</Table>\n";
    }
    }else{
        echo "Lista está vazia.";
    }
    print("</NewDataSet>\n");
}
// Função para converter o limite para bytes
function convertToBytes($valor) {
    $unidade = strtoupper(substr($valor, -1));
    $valor = (int)$valor;
    switch ($unidade) {
        case 'K': return $valor * 1024;
        case 'M': return $valor * 1048576; // 1024 * 1024
        case 'G': return $valor * 1073741824; // 1024 * 1024 * 1024
        default: return $valor;
    }
}
function gerarArquivoZip($nomeArquivoLog, $fws_, $objEv, $linhas, $debug = false) {
    $debug = false;
    try {
        // Verifica se o evento é permitido
        $codigosEventos = [61, 70, 81, 116];
        $objEvConf = new EventoConfiguracao();
        $objEvConf->setCodEvento($objEv->getCodEvento());
        $objEvConf->getEventoConfiguracaoById();
        if (!in_array($objEv->getCodEvento(), $codigosEventos) && $objEvConf->getTrabDownloadZip() != 1) {
           throw new Exception("Evento não liberado para download.");
        }
        if (count($linhas) == 0) {
           throw new Exception("A lista de arquivos a serem compactados está vazia.");
        }
        
        $codEvento = $objEv->getCodEvento();
        // Define o nome do arquivo zip já com a extensão
        $nomeArquivoLog = 'download-' . $codEvento . '-' . date('d-m-Y') . '-' . date('H') . 'h.zip';
        
        // Seleciona o diretório conforme o tipo de arquivo
        $tipoArquivo = 'TRABALHOS-IDENTIFICADOS';
        // Tratamento conforme o tipo de arquivo
        switch ($tipoArquivo) {
            case 'AVULSO':
                $diretorio = WB_PATH_LOG_AVULSO;
                $getNomeArquivo = 'getArquivoGeradoAvulso';
                break;
            case 'APRESENTACAO':
                $diretorio = WB_PATH_LOG_APRESENTACAO;
                $getNomeArquivo = 'getArquivoGeradoApresentacao';
                break;
            case 'TRABALHOS':
                $diretorio = WB_PATH_LOG_TRABALHO;
                $getNomeArquivo = 'getArquivoGerado';
                break;
            case 'TRABALHOS-IDENTIFICADOS':
                $diretorio = WB_PATH_LOG_TRABALHO;
                $getNomeArquivo = 'getArquivoIdentificadoGerado';
                break;
            default:
                throw new Exception("Tipo do arquivo não definido.");
        }

        $arquivoZip = $diretorio . $nomeArquivoLog;
        $zip = new ZipArchive;
        
        if ($zip->open($arquivoZip, ZipArchive::CREATE) !== TRUE) {
            throw new Exception("Não foi possível criar o arquivo zip.");
        }

        // Gera o arquivo index.html com a listagem dos arquivos
        $conteudoHtml = "<!DOCTYPE html>\n<html lang='pt-BR'>\n<head>\n<meta charset='UTF-8'>\n";
        $conteudoHtml .= "<style>table{border-collapse:collapse;width:100%;font-family:Arial,sans-serif;font-size:13px;}th,td{border:1px solid #ddd;padding:6px 10px;text-align:left;}th{background:#f5f5f5;}tr:hover{background:#f9f9f9;}</style>\n";
        $conteudoHtml .= "<title>Listagem de Arquivos</title>\n</head>\n<body>\n";
        $conteudoHtml .= "<h3>Arquivos - Evento " . $codEvento . " (" . count($linhas) . " trabalhos)</h3>\n";
        $conteudoHtml .= "<table>\n<tr><th>Cod.Trab.</th><th>Inscrito</th><th>Area Tematica</th><th>Titulo</th><th>Arquivo</th><th>Identificado</th></tr>\n";
        $areaAtual = '';
        foreach ($linhas as $linha) {
            $codTrab = $linha->getCodTrabalho();
            $codInsc = $linha->getCodInscrito();
            $nomeInsc = $linha->getNomeInscrito();
            $titulo = $linha->getTituloTrabalho();
            $area = $linha->getTituloAreaTematica() ?: 'Sem area';
            $arqOriginal = $linha->getArquivoGerado();
            $arqIdentificado = ($getNomeArquivo == 'getArquivoIdentificadoGerado') ? $linha->getArquivoIdentificadoGerado() : '';

            // Separador visual por area tematica
            if ($area != $areaAtual) {
                $areaAtual = $area;
                $conteudoHtml .= "<tr><td colspan='6' style='background:#e8e8e8;font-weight:bold;'>{$area}</td></tr>\n";
            }

            $linkOriginal = $arqOriginal ? "<a href='{$arqOriginal}'>{$arqOriginal}</a>" : '-';
            $linkIdentificado = ($arqIdentificado && $arqIdentificado != $arqOriginal) ? "<a href='{$arqIdentificado}'>{$arqIdentificado}</a>" : '-';

            $conteudoHtml .= "<tr><td>{$codTrab}</td><td>{$codInsc} - {$nomeInsc}</td><td>{$area}</td><td>{$titulo}</td><td>{$linkOriginal}</td><td>{$linkIdentificado}</td></tr>\n";
        }
        $conteudoHtml .= "</table>\n</body>\n</html>";
        
        // Caminho completo do index.html
        $arquivoIndex = $diretorio . "index.html";
        file_put_contents($arquivoIndex, $conteudoHtml);
        
        // Percorre cada arquivo e adiciona no zip (organizado por area tematica)
        foreach ($linhas as $linha) {
            $area = $linha->getTituloAreaTematica() ?: 'Sem area';
            // Limpar caracteres especiais para nome de pasta
            $pastaArea = preg_replace('/[^a-zA-Z0-9\s\-]/', '', iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $area));
            $pastaArea = trim($pastaArea);
            if (!$pastaArea) $pastaArea = 'Sem area';

            // Arquivo original
            $arqOriginal = $linha->getArquivoGerado();
            if ($arqOriginal && file_exists($diretorio . $arqOriginal)) {
                $zip->addFile($diretorio . $arqOriginal, $pastaArea . '/' . $arqOriginal);
            }
            // Arquivo identificado (se existir e for diferente do original)
            if ($getNomeArquivo == 'getArquivoIdentificadoGerado') {
                $arqIdentificado = $linha->getArquivoIdentificadoGerado();
                if ($arqIdentificado && $arqIdentificado != $arqOriginal && file_exists($diretorio . $arqIdentificado)) {
                    $zip->addFile($diretorio . $arqIdentificado, $pastaArea . '/' . $arqIdentificado);
                }
            }
        }
        $zip->addFile($arquivoIndex, 'index.html');
        $zip->close();
        
        // Força o download do arquivo zip
        if (file_exists($arquivoZip)) {
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . basename($arquivoZip) . '"');
            header('Content-Length: ' . filesize($arquivoZip));
            ob_clean();
            flush();
            readfile($arquivoZip);
            exit;
        } else {
            throw new Exception("Arquivo zip não foi criado.");
        }
        
        
    } catch (Exception $e) {
        echo "Erro: " . $e->getMessage();
    }
    unset($_SESSION["param_gerar_pdf"]);
}

function gerarArquivoZipOld($nomeArquivoLog, $fws_, $objEv, $linhas, $debug = false) {
    $debug=false;
    // ini_set('display_errors', 1);
    // error_reporting(E_ALL);

    // echo '<pre>';
    // print_r($linhas);
    // echo '</pre>';
    // exit();
   try {
        $codigosEventos = [61, 70, 81]; 
        if (!in_array($objEv->getCodEvento(), $codigosEventos)) {
           throw new Exception("Evento não liberado para donwload.");
        }
        if (count($linhas)==0) {
           throw new Exception("A lista de arquivos a serem compactados está vazia.");
        }

       $codEvento = $objEv->getCodEvento();
       $nomeArquivoLog = 'donwload-'.$codEvento.'-'.date('d-m-Y').'-'.date('H').'h';

    //    $fileName = $nomeArquivoLog;
    //    $tipoArquivo = 'AVULSO';
    //    $tipoArquivo = 'APRESENTACAO';
    //    $tipoArquivo = 'TRABALHOS';
        $tipoArquivo = 'TRABALHOS-IDENTIFICADOS';
       switch ($tipoArquivo) {
        case 'AVULSO':
            $diretorio = WB_PATH_LOG_AVULSO;
            $getNomeArquivo = 'getArquivoGeradoAvulso';
            // $fileName = $codEvento."_avulso-".Date('Y-m-d-H-i-s').".zip";
            break;
        case 'APRESENTACAO':
            $diretorio = WB_PATH_LOG_APRESENTACAO;
            $getNomeArquivo = 'getArquivoGeradoApresentacao';
            // $fileName = $codEvento."_apresentacao-".Date('Y-m-d-H-i-s').".zip";
            break;
        case 'TRABALHOS':
            $diretorio = WB_PATH_LOG_TRABALHO;
            $getNomeArquivo = 'getArquivoGerado';
            // $fileName = $codEvento."_apresentacao-".Date('Y-m-d-H-i-s').".zip";
            break;
        case 'TRABALHOS-IDENTIFICADOS':
            $diretorio = WB_PATH_LOG_TRABALHO;
            $getNomeArquivo = 'getArquivoIdentificadoGerado()';
            // $fileName = $codEvento."_apresentacao-".Date('Y-m-d-H-i-s').".zip";
            break;
        
        default:
            echo "Tipo do arquivo não definido";
            exit();
            break;
       }

        // $arquivoPDFFinal = $diretorio.$nomeArquivoLog;
        // $resultado = file_put_contents($arquivoPDFFinal, '');
        $arquivoLogTxt = $diretorio."logzip.txt";
        $arquivoLogHtml = $diretorio.'listagem.html';
        /*array que recebe array da lista de arquivos*/
        $ii = 0;
        foreach ($linhas as $linha) {
            //dd($linha);
            // echo $getNomeArquivo."<-1<br>";
            // $nomeArquivo = $linha->$getNomeArquivo;
            $nomeArquivo = $linha->getArquivoIdentificadoGerado();
            // echo $nomeArquivo().'<-2<br>';
            // exit();
            // $param_gerar_pdf["arrArq"][$ii] = $diretorio.$nomeArquivo;
            $param_gerar_pdf["arrArq"][$ii] = $diretorio.$nomeArquivo;
            $textoArquivo = $diretorio.$linha->$getNomeArquivo();
            file_put_contents($arquivoLogHtml, $textoArquivo, FILE_APPEND);
            $ii++;
        }

        // Inicializa o conteúdo HTML
        $conteudoHtml = <<<HTML
        <!DOCTYPE html>
        <html lang="pt-BR">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>{$fws_['titulo']}</title>
        </head>
        <body>
            <h3>{$fws_['titulo']}</h3>
            <p>Abaixo estão os arquivos disponíveis para download:</p>
        HTML;

        // Loop para processar cada linha e adicionar links
        $iii=0;
        foreach ($linhas as $linha) {
            $nomeArquivo = $linha->getArquivoIdentificadoGerado();;
            // $caminhoArquivo = $diretorio . $nomeArquivo;
            $statuEncontrato = "";
            $tamanhoArq = 0;
            if(!file_exists($diretorio.$nomeArquivo)){
                $statuEncontrato = "(não encontrado)";
                $tamanhoArq = filesize($diretorio.$nomeArquivo);
            }
            // Adiciona um link para o arquivo no conteúdo HTML
            $iii++;
            $conteudoHtml .= "<p><a href=\"{$nomeArquivo}\" target='_blank'>{$iii}- Baixar -> {$nomeArquivo} ({filesize($tamanhoArq})</a></p>";

            // Adiciona o caminho do arquivo ao log HTML
            // file_put_contents($arquivoLogHtml, $caminhoArquivo . PHP_EOL, FILE_APPEND);
        }

        // Fecha o conteúdo HTML
        $conteudoHtml .= <<<HTML
        </body>
        </html>
        HTML;

        // Salva o conteúdo HTML no arquivo
        $resultado = file_put_contents($arquivoLogHtml, $conteudoHtml);

        /**para download do html */
        // if(file_exists($arquivoLogHtml)){
        //     // echo 'Arquivo criado com sucesso!';
        //     // Forçamos o donwload do arquivo.
        //     header('Content-Type: text/html');
        //     header('Content-Disposition: attachment; filename="'.$nomeArquivoLog.'.html"');
        //     readfile($arquivoLogHtml);
        //     //removemos o arquivo zip após download
        //     //    unlink($arquivoPDFFinal);
        // }

        // exit();

        
        // Criamos o arquivo e verificamos se ocorreu tudo certo
        $msgFalha = '';
        $msgFalha .= 'formato do nome dos aquivos seguem o seguinte padrão:'. chr(13);
        $msgFalha .= 'codEvento-codInscrito-codTrabalho-dataUltimaAtualizacao-versao.tipoExtensao'. chr(13);
        $msgFalha .= 'Caso falte algum arquivo, procure o link (abrir arquivo) na listagem de trabalhos, para download individual'. chr(13);

        // Obter os limites do sistema
        $limiteMemoria = ini_get('memory_limit');
        $limiteUpload = ini_get('upload_max_filesize');
        $limitePost = ini_get('post_max_size');
        $tempoExecucao = ini_get('max_execution_time');

        // Converta os valores de limites para bytes
        $limiteMemoriaBytes = convertToBytes($limiteMemoria);
        $limiteUploadBytes = convertToBytes($limiteUpload);
        $limitePostBytes = convertToBytes($limitePost);

        // Iniciar contador de tempo de execução
        $inicioTempo = microtime(true);
        $maxArquivosPorZip = 20; // Número máximo de arquivos por ZIP
        $zipContador = 1; // Contador de arquivos ZIP
                
        $zip = new ZipArchive;
        $arquivoPDFFinal = $diretorio.$nomeArquivoLog;
        if ($zip->open($arquivoPDFFinal, ZipArchive::CREATE) === TRUE) {
            echo $debug? __LINE__.'-' : '';


            for ($ii = 0; $ii < count($param_gerar_pdf["arrArq"]); $ii++) {
                echo $debug? __LINE__.'-' : '';
                // Nome do arquivo
                $arqNames = explode("/", $param_gerar_pdf["arrArq"][$ii]);
                $arqName = end($arqNames);
                
                echo $debug? '->'.$param_gerar_pdf["arrArq"][$ii].'<-<br>' : '';
                
                // Verificar se o arquivo existe
                if (file_exists($param_gerar_pdf["arrArq"][$ii])) {
                    echo $debug? __LINE__.'-' : '';
                    $tamanhoArq = filesize($param_gerar_pdf["arrArq"][$ii]);
                    echo $tamanhoArq.'<br>';
                    // if($tamanhoArq>1024000){

                    // }
                    if ($ii % $maxArquivosPorZip == 0) {
                        $zipContador++;
                        $zip->close();
                        $arquivoPDFFinal = $diretorio.$nomeArquivoLog . '-' . $zipContador . '.zip';
                        $zip->open($arquivoPDFFinal, ZipArchive::CREATE);
                    }
        
                    // Verificar uso de memória
                    $usoMemoriaAtual = memory_get_usage(true);
                    if ($usoMemoriaAtual >= $limiteMemoriaBytes * 0.8) {
                        echo "Atingido 80% do limite de memória. Interrompendo processo.";
                        break;
                    }

                    // Verificar tempo de execução
                    $tempoDecorrido = microtime(true) - $inicioTempo;
                    if ($tempoDecorrido >= $tempoExecucao * 0.8) {
                        echo "Atingido 80% do limite de tempo de execução. Interrompendo processo.";
                        break;
                    }

                    // Adicionar arquivo ao ZIP
                    $zip->addFile($param_gerar_pdf["arrArq"][$ii], $arqName);
                } else {
                    echo $debug? __LINE__.'-' : '';
                    $msgFalha .= 'Arquivo não encontrado: ' . $arqName . PHP_EOL;
                }
                
                echo $debug? '<BR>' : '';

                // // Limitar a quantidade de arquivos processados (por exemplo, 5)
                // if ($ii >= 50) {
                //     break;
                // }
            }

            echo $debug? __LINE__.'-' : '';
            if($msgFalha){
                echo $debug? __LINE__.'-'.$msgFalha : '';
                file_put_contents($arquivoLogTxt, $msgFalha);
                $zip->addFile($arquivoLogTxt,"leiame.txt");
                $zip->addFile($arquivoLogHtml,"listagem.html");
            }
            
            $zip->close();
            if(file_exists($arquivoPDFFinal)){
                // echo 'Arquivo criado com sucesso!';
                // Forçamos o donwload do arquivo.
                header('Content-Type: application/zip');
                header('Content-Disposition: attachment; filename="'.$nomeArquivoLog.'"');
                readfile($arquivoPDFFinal);
                //removemos o arquivo zip após download
                //    unlink($arquivoPDFFinal);
            }
            showMessage($msgFalha);
        } else {
                throw new Exception("Arquivo não localizado.");
        }
    } catch (Exception $e) {
        echo $msgFalha."<br>";
        echo $e->getMessage()." ".__LINE__."<br>";
    }
    unset($_SESSION["param_gerar_pdf"]);
}
/**
 * Prepara os dados do arquivo enviado via formulário para anexo em um e-mail.
 *
 * Este método recebe um arquivo do formulário ($_FILES['arquivo']) e retorna uma array contendo:
 * - O nome original do arquivo.
 * - O tipo MIME do arquivo.
 * - O conteúdo do arquivo codificado em base64.
 *
 * @param array $arquivo O arquivo enviado pelo formulário (exemplo: $_FILES['arquivo']).
 *                       Deve conter os índices: 'tmp_name', 'name', 'type', 'size', 'error'.
 *
 * @return array Retorna um array associativo com os seguintes elementos:
 *               - 'nome' => string (Nome do arquivo).
 *               - 'tipo' => string (Tipo MIME do arquivo).
 *               - 'conteudo' => string (Conteúdo do arquivo codificado em base64).
 *
 * @throws Exception Se houver erro no upload do arquivo.
 *
 * @example
 * $dadosArquivo = prepararArquivoParaAnexo($_FILES['arquivo']);
 * // Retorno:
 * // [
 * //   'nome' => 'documento.pdf',
 * //   'tipo' => 'application/pdf',
 * //   'conteudo' => 'base64string...'
 * // ]
 */
function prepararArquivoParaAnexo($arquivo) {
    // Verificar se o arquivo foi enviado corretamente
    if (isset($arquivo) && $arquivo['error'] == UPLOAD_ERR_OK) {
        // Obter as informações do arquivo
        $fileTmpPath = $arquivo['tmp_name'];  // Caminho temporário do arquivo
        $fileName = $arquivo['name'];         // Nome original do arquivo
        $fileType = $arquivo['type'];         // Tipo MIME do arquivo
        $fileSize = $arquivo['size'];         // Tamanho do arquivo (em bytes)

        // Ler o conteúdo do arquivo e codificar em base64
        $fileContent = file_get_contents($fileTmpPath);
        $fileEncoded = base64_encode($fileContent);

        // Retornar os dados necessários em um array
        return [
            'nome' => $fileName,
            'tipo' => $fileType,
            'conteudo' => $fileEncoded
        ];
    } else {
        throw new Exception("Erro no envio do arquivo.");
    }
}
function isValidFoneNumber($number) {
    // Remove tudo que não for número
    $number = preg_replace('/\D/', '', $number);

    // Remove o 55 do começo, se tiver
    if (substr($number, 0, 2) === '55') {
        $number = substr($number, 2);
    }

    // Agora verifica se tem entre 8 e 11 dígitos
    return preg_match('/^\d{8,11}$/', $number);
}


function limpaFoneWhats($valor) {
    $valor = trim($valor);
    $valor = str_replace(".", "", $valor);
    $valor = str_replace(",", "", $valor);
    $valor = str_replace("-", "", $valor);
    $valor = str_replace("/", "", $valor);
    $valor = str_replace(" ", "", $valor);
    if(strlen($valor)==9){
        $valor = '43'.$valor;
    }
    $valor = '55'.$valor;
    return $valor;
}

function limparCpf($valor) {
    $valor = trim($valor);
    $valor = str_replace(".", "", $valor);
    $valor = str_replace(",", "", $valor);
    $valor = str_replace("-", "", $valor);
    $valor = str_replace("/", "", $valor);
    return $valor;
}

function getPhpTempoExecucao($php_start_time) {
    //Finalizando contador de tempo
    list($usec, $sec) = explode(' ', microtime());
    $script_end = (float) $sec + (float) $usec;
    $elapsed_time = round($script_end - $php_start_time, 5);
    return $elapsed_time;
}

function debug($debug = false) {
    echo($debug ? __LINE__ . "-" . __FILE__ . "<hr>" : "");
}

function getHttpUserAgent() {
    return $_SERVER["HTTP_USER_AGENT"];
}

/*
 * Utilize tambem:
 * include_once(WB_PATH."/lib/util/IPUtils.php");
 * $IPLiberado = new IPUtils();
 * if($IPLiberado->ipLiberado()){
 */

function getIp() {
    if ($_SERVER["REMOTE_ADDR"]) {
        $FWS_IP_VISITANTE = $_SERVER["REMOTE_ADDR"];
        #echo $FWS_IP_VISITANTE." - REMOTE_ADDR<br>";
    }
    if ($_SERVER["HTTP_X_FORWARDED_FOR"]) {
        $FWS_IP_VISITANTE = $_SERVER["HTTP_X_FORWARDED_FOR"];
        #echo $FWS_IP_VISITANTE." - HTTP_X_FORWARDED_FOR<br>";
    }
    return $FWS_IP_VISITANTE;
}

function debugByIp() {
    if (getIp() == '189.90.70.566' or getIp() == '189.90.70.511' or getIp() == '189.90.70.577') {
        return true;
    }
}

// Redirect via javascript
function confirmRedirect($pMessage, $pUrl, $arrayCampos, $debug = false) {
    if($arrayCampos){
        $bodyForm = criaHiddens($arrayCampos, $debug);
    }
    if ($debug == true) {
        echo $pMessage;
    } else {
        echo " 
        <form action=\"/index.php?contents={WB_PATH}/index.php&pagina={$pUrl}\" method=\"POST\" name=\"confirmRedirect\">
            {$bodyForm}
        </form>
        <script language=\'javascript\'>
            <!--
                if (confirm(\'{$pMessage}\')) {
                    document.forms[\"confirmRedirect\"].submit();
                }
            -->
        </script>";
    }
}

function postUrl($pUrl, $arrayCampos = false, $debug = false) {
    if ($debug) {
        print_r($arrayCampos) . "<hr>";
    }
    if($arrayCampos){
        $bodyForm = criaHiddens($arrayCampos, $debug);
    }
    $urlAction = WB_URL . "/index.php?contents=" . WB_PATH . "/index.php&pagina=" . $pUrl;
    echo " 
        <form id=\"postUrl\" name=\"postUrl\" action=\"{$urlAction}\" method=\"post\">
        {$bodyForm}
        </form>
        <script language=\'javascript\'>
            <!--
            function enviarFormAcao() {
                document.forms[\"postUrl\"].submit();
            }
            -->
        </script>";

    if ($debug) {
        echo $urlAction;
        echo "<input type=\"button\" onclick=\"enviarFormAcao()\" value=\"Continuar\">";
    } else {
        echo " 
        <script language=\'javascript\'>
        <!--
            enviarFormAcao();
         -->
        </script>";
    }
    exit();
}

function postExternalUrl($pUrl, $arrayCampos, $debug = false) {
    if($arrayCampos){
        $bodyForm = criaHiddens($arrayCampos, $debug);
    }

    $urlAction = $pUrl;
    echo " 
        <form action=\"{$urlAction}\" method=\"POST\" name=\"postExternalUrl\">
            ";
        criaHiddens($arrayCampos, $debug);
        echo "</form>
        <script language='javascript'>
                    <!--
            function enviarFormAcao() {
            document.forms[\"postExternalUrl\"].submit();
            }            
            -->
        </script>";
    if ($debug) {
        echo $urlAction;
        echo "<input type=\"button\" onclick=\"enviarFormAcao()\" value=\"Continuar\">";
    } else {
        echo " 
        <script language='javascript'>
            enviarFormAcao();
        </script>";
    }
    exit();
}

function goUrl($pUrl, $debug = false) {
    if ($debug == true) {
        echo "location.href:" . $pUrl . "<hr>";
    } else {
        echo "\n <script language='javascript'> ";
        echo "\n   <!-- ";
        echo "\n      location.href = '" . $pUrl . "'; ";
        echo "\n   --> ";
        echo "\n </script> ";
        echo "\n";
    }
   exit();
}

// Mostra mensagem
function showMessage($pMessage, $debug = false) {
    if ($debug == true) {
        echo $pMessage . "<hr>";
    } else {
        echo "\n <script language='javascript'> ";
        echo "\n   <!-- ";
        echo "\n      alert('" . $pMessage . "'); ";
        echo "\n   --> ";
        echo "\n </script> ";
        echo "\n";
    }
}



/*
 * Verifica se o item esta contido no array
 * return true
 */
// function verifItemArray($item, $arrayList, $debug = false) {
//     if (is_array($arrayList)) {
//         sort($arrayList);
//         echo($debug ? "count:" . count($arrayList) . "<-<br>" : "");

// //        $item = htmlentities(addslashesopctec($item));
//         if (($item != 0) and ($item != '1')) {
//             for ($i = 0; $i < count($arrayList); $i++) {
//                 $valArray = $arrayList[$i];
//                 ($valArray == "Teru00e7a" ? $valArray = "TerÃÂ§a" : "");
//                 ($valArray == "Su00e1bado" ? $valArray = "Sábado" : "");

//                 if (mb_detect_encoding($valArray . 'x', 'UTF-8, ISO-8859-1') == 'UTF-8') {
//                     $valArray = utf8_decode(addslashesopctec($valArray));
//                     echo($debug ? $item . "<->" . $valArray . "<-1<br>" : "");
//                 } else {
//                     $valArray = addslashesopctec($valArray);
//                     echo($debug ? $item . "<->" . $valArray . "<-2<br>" : "");
//                 }
//                 echo($debug ? __LINE__ . "<hr>" : "");
//                 echo($debug ? html_entity_decode($item) . "==" . html_entity_decode($valArray) . "<hr>" : "");
//                 if (html_entity_decode($item) == html_entity_decode($valArray)) {
//                     echo($debug ? "igual<br>" : "");
//                     return true;
//                 }
//             }
//         } else {
//             for ($i = 0; $i < count($arrayList); $i++) {
//                 $var = $arrayList[$i];
//                 if ($debug) {
//                     echo "-".$item."-" . $var . "-<br>";
//                 }
//                 if ($item===$var) {
//                     echo ($debug? $item.' == '.$var.'-'.__LINE__."<br>" : "");
//                     return true;
//                 }
//                 if ($item==$var) {
//                     echo ($debug? $item.' == '.$var.'-'.__LINE__."<br>" : "");
//                     return true;
//                 }
//             }
//         }
//     } else {
//         echo($debug ? "Não é um array list: ->".$item."<==>".$arrayList."<-<hr>" : "");
//         if ($item == $arrayList) {
//             return true;
//         }
//     }
//     return false;
// }

/**
 * Verifica se $item (valor ou array) está contido em $arrayList (valor ou array).
 * Retorna true se houver pelo menos uma correspondência.
 */
function verifItemArray($item, $arrayList, $debug = false) {
    // Sempre trabalhar como arrays
    $itens = is_array($item) ? $item : [$item];
    $lista = is_array($arrayList) ? $arrayList : [$arrayList];

    // Normalizador leve (ajusta bugs comuns e entidades)
    $norm = function($s) {
        // Correções específicas vistas no seu código
        if ($s === "Teru00e7a")  $s = "Terça";
        if ($s === "Su00e1bado") $s = "Sábado";

        // Garante string, remove espaços e normaliza entidades
        $s = (string)$s;
        $s = trim($s);
        $s = html_entity_decode($s, ENT_QUOTES, 'UTF-8');

        // Se detectar que NÃO é UTF-8 válido, força para UTF-8
        if (!mb_detect_encoding($s, 'UTF-8', true)) {
            // tenta de ISO-8859-1 -> UTF-8
            $s = utf8_encode($s);
        }
        return $s;
    };

    // Normaliza tudo
    $itensN = array_map($norm, $itens);
    $listaN = array_map($norm, $lista);

    // Comparação: bateu == ou ===, considera encontrado
    foreach ($itensN as $i) {
        foreach ($listaN as $l) {
            if ($debug) {
                echo "cmp: [" . $i . "] vs [" . $l . "] => "
                   . (($i === $l) ? "===" : (($i == $l) ? "==" : "!=")) . "<br>";
            }
            if ($i === $l || $i == $l) {
                if ($debug) echo ">> MATCH<br>";
                return true;
            }
        }
    }

    return false;
}

/*
 * Retorna para o camando select para definir qual dos option de um select foi selecionado
 */

function verifItemSelected($item, $arrayList,$debug=false) {
    if (verifItemArray($item, $arrayList,$debug)) {
        return  "selected='selected'";
    }
}

/*
 * Retorna para o camando checked para definir qual dos radion button foram selecionados
 */

function verifItemChecked($item, $arrayList, $debug = false) {
    if (verifItemArray($item, $arrayList, $debug)) {
        return  "checked";
    }
}

function verifItemHidden($item, $arrayList) {
    if (verifItemArray($item, $arrayList)) {
        return  "show";
    } else {
        return  "hidden";
    }
}

function verifItemDisabled($item = false) {
    if ($item) {
        return "disabled=\"\"";
    }
}

function equalsReturn($a, $b, $c) {
    if ($a == $b)
        return $c;
}

function formatHoraView($hora) {
    if ($hora) {
        $subject = $hora;
        $pattern = "/([0-9]{2})([0-9]{2})/";
        preg_match($pattern, $subject, $matchesarray);
        return $matchesarray[1] . "h" . $matchesarray[2];
    }
}

function formatHoraInsert($hora) {
    if ($hora) {
        $subject = $hora;
        $pattern = "/([0-9]{2})h([0-9]{2})/";
        preg_match($pattern, $subject, $matchesarray);
        return $matchesarray[1] . $matchesarray[2];
    }
}

function formatCpfInsert($mystring, $completa = true) {
    $loc = array(" ", "-", "/", ":", "T");
    if ($mystring != ''){
    $mystring = str_replace(" ", "", $mystring);
    $mystring = str_replace("-", "", $mystring);
    $mystring = str_replace(".", "", $mystring);
    $mystring = str_replace("/", "", $mystring);
    }
    if($mystring!=''){
        $mystring = numberPad($mystring, 11);
    }
    return $mystring;
}
function formatCnpjInsert($mystring, $completa = true) {
    $loc = array(" ", "-", "/", ":", "T");
    if ($mystring != ''){
    $mystring = str_replace(" ", "", $mystring);
    $mystring = str_replace("-", "", $mystring);
    $mystring = str_replace(".", "", $mystring);
    $mystring = str_replace("/", "", $mystring);
    }
    if($mystring!=''){
        $mystring = numberPad($mystring, 14);
    }
    return $mystring;
}

function formatCpfView($mystring, $completa = true) {
    $mystring = str_replace("-", "", $mystring);
    $mystring = str_replace(".", "", $mystring);
    $mystring = str_replace(" ", "", $mystring);
    $mystring = numberPad($mystring, 11);
    $pattern = "/([0-9]{3})([0-9]{3})([0-9]{3})([0-9]{2})/";
    preg_match($pattern, $mystring, $matchesarray);
    $mystring = $matchesarray[1] . "." . $matchesarray[2] . "." . $matchesarray[3]. "-" . $matchesarray[4];
    return $mystring;
}
/**
 * Reduz para so os digitos e ajusta ao tamanho alvo (11 p/ CPF, 14 p/ CNPJ):
 * completa com zero a esquerda se faltar digito, corta os excedentes se
 * sobrar. Antes o ramo de 14 digitos exigia bater um regex EXATO -- um
 * cadastro com 12 ou 13 digitos reais (nao so um caractere estranho junto,
 * o proprio numero errado) fazia o match falhar e devolvia os grupos vazios
 * concatenados, ex: "../-", no lugar do CPF/CNPJ do pagador no recibo.
 */
function ajustarDigitosCpfCnpj($mystring, $tamanho){
    $digitos = preg_replace('/\D/', '', (string) $mystring);
    if(strlen($digitos) > $tamanho){
        $digitos = substr($digitos, 0, $tamanho);
    }
    return str_pad($digitos, $tamanho, '0', STR_PAD_LEFT);
}
/**
 * 11 (CPF) ou 14 (CNPJ) digitos -- o alvo mais PROXIMO da quantidade real
 * de digitos. Ate 11 digitos e sempre CPF; de 12 a 14 fica mais perto de
 * CNPJ so a partir de 13 (12 = 1 digito sobrando de um CPF, mais provavel
 * ser erro de digitacao do que um CNPJ faltando 2). Sem isso, um CPF com
 * um digito a mais saia formatado como CNPJ no recibo.
 */
function tamanhoAlvoCpfCnpj($digitos){
    $len = strlen($digitos);
    return ($len <= 12) ? 11 : 14;
}
function limparCpfCnpj($mystring){
    $digitos = preg_replace('/\D/', '', (string) $mystring);
    return ajustarDigitosCpfCnpj($digitos, tamanhoAlvoCpfCnpj($digitos));
}
function formatCpfCnpjView($mystring, $completa = true) {
    if($mystring){
        $digitos = preg_replace('/\D/', '', (string) $mystring);
        if(tamanhoAlvoCpfCnpj($digitos) == 11){
            $mystring = ajustarDigitosCpfCnpj($digitos, 11);
            return substr($mystring,0,3) . "." . substr($mystring,3,3) . "." . substr($mystring,6,3). "-" . substr($mystring,9,2);
        }else{
    //        80.511.173/0001-03
            $mystring = ajustarDigitosCpfCnpj($digitos, 14);
            return substr($mystring,0,2) . "." . substr($mystring,2,3) . "." . substr($mystring,5,3). "/" . substr($mystring,8,4). "-" . substr($mystring,12,2);
        }
    }
}
function validarEmail($email) {
    // Verifica se o e-mail é válido usando a função filter_var com o filtro FILTER_VALIDATE_EMAIL
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return true;
    } else {
        return false;
    }
}
function validarCPF($cpf) {
    // Remover caracteres não numéricos
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    
    // Verificar se possui 11 dígitos
    if (strlen($cpf) !== 11) {
        return false;
    }
    
    // Verificar se todos os dígitos são iguais
    if (preg_match('/(\d)\1{10}/', $cpf)) {
        return false;
    }
    
    // Calcular os dígitos verificadores
    $digitos = str_split($cpf);
    $soma = 0;
    
    for ($i = 0; $i < 9; $i++) {
        $soma += $digitos[$i] * (10 - $i);
    }
    
    $resto = $soma % 11;
    
    $digito1 = ($resto < 2) ? 0 : 11 - $resto;
    
    $soma = 0;
    
    for ($i = 0; $i < 10; $i++) {
        $soma += $digitos[$i] * (11 - $i);
    }
    
    $resto = $soma % 11;
    
    $digito2 = ($resto < 2) ? 0 : 11 - $resto;
    
    // Verificar se os dígitos calculados correspondem aos dígitos do CPF
    if ($digitos[9] != $digito1 || $digitos[10] != $digito2) {
        return false;
    }

    return true;
}
function validarCNPJ($cnpj) {
    // Remover caracteres não numéricos
    $cnpj = preg_replace('/[^0-9]/', '', $cnpj);

    // Verificar se possui 14 dígitos
    if (strlen($cnpj) !== 14) {
        return false;
    }

    // Verificar se todos os dígitos são iguais
    if (preg_match('/(\d)\1{13}/', $cnpj)) {
        return false;
    }

    $digitos = str_split($cnpj);

    // Primeiro dígito verificador
    $pesos1 = array(5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2);
    $soma = 0;
    for ($i = 0; $i < 12; $i++) {
        $soma += $digitos[$i] * $pesos1[$i];
    }
    $resto = $soma % 11;
    $digito1 = ($resto < 2) ? 0 : 11 - $resto;

    // Segundo dígito verificador
    $pesos2 = array(6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2);
    $soma = 0;
    for ($i = 0; $i < 13; $i++) {
        $soma += $digitos[$i] * $pesos2[$i];
    }
    $resto = $soma % 11;
    $digito2 = ($resto < 2) ? 0 : 11 - $resto;

    // Verificar se os dígitos calculados correspondem aos do CNPJ
    if ($digitos[12] != $digito1 || $digitos[13] != $digito2) {
        return false;
    }

    return true;
}
function formatCepView($mystring, $completa = true) {
    $mystring = str_replace("-", "", $mystring);
    $mystring = str_replace(".", "", $mystring);
    $mystring = str_replace(" ", "", $mystring);
    $mystring = str_pad($mystring, 8, "0", STR_PAD_RIGHT);
    $pattern = "/([0-9]{5})([0-9]{3})/";
    preg_match($pattern, $mystring, $matchesarray);
    $mystring = $matchesarray[1] . "-" . $matchesarray[2];
    return $mystring;
}
// verifica se um esta esta de escrito de forma correta
function validarCep($cep) {
    // retira espacos em branco
    $cep = trim($cep);
    // expressao regular para avaliar o cep
    $avaliaCep = preg_match("/^[0-9]{5}-[0-9]{3}$/", $cep);
    
    // verifica o resultado
    if(!$avaliaCep) {            
        echo "CEP nao Valido";
    }
    else
    {
        echo "CEP Valido";
    }
}
function formataNumeroBoleto($numero,$loop,$insert,$tipo = "geral") {
	if ($tipo == "geral") {
		$numero = str_replace(",","",$numero);
		while(strlen($numero)<$loop){
			$numero = $insert . $numero;
		}
	}
	if ($tipo == "valor") {
		/*
		retira as virgulas
		formata o numero
		preenche com zeros
		*/
		$numero = str_replace(",","",$numero);
		while(strlen($numero)<$loop){
			$numero = $insert . $numero;
		}
	}
	if ($tipo == "convenio") {
		while(strlen($numero)<$loop){
			$numero = $numero . $insert;
		}
	}
	return $numero;
}
function somarDiasNaData($dataInicial,$dias){
    $data = new DateTime($dataInicial);
    $data->modify('+'.$dias.' days');
    $dataFinal = $data->format('Y-m-d');
    return $dataFinal;
}
function subtrairDiasDaData($dataInicial, $dias) {
    $data = new DateTime($dataInicial);
    $data->modify('-' . $dias . ' days'); // Use o sinal de menos para subtrair os dias
    $dataFinal = $data->format('Y-m-d');
    return $dataFinal;
}
function isDateStringValid($str) {
        if ($str != ''){
        $timestamp = strtotime($str);
        return $timestamp !== false && date("Y-m-d", $timestamp) === $str;
    }
}
function formatarCargaHoraria($cargaHoraria) {
    // Verifica se a string já contém "h" ou "horas"
    if (strpos($cargaHoraria, 'h') !== false || strpos($cargaHoraria, 'horas') !== false) {
        return $cargaHoraria;
    }

    // Remove "HORAS" e acrescenta " horas" ao final
    $cargaHoraria = str_replace('HORAS', '', $cargaHoraria);
    return $cargaHoraria . ' horas';
}
function formatTimeView($hora=false){
    $hora = substr($hora,0,5);
    $hora = str_replace(':','h',$hora);
    return $hora;
}
function formatDateRemessa($subject,$mascaraEntrada = "dd/mm/yyyy"){
    $subject = str_replace('/', '', $subject);
    $pattern = "/([0-9]{2})([0-9]{2})([0-9]{4})/";
    preg_match($pattern, $subject, $matchesarray);
    return $matchesarray[1]  . $matchesarray[2] . substr($matchesarray[3],2,2);
}
function formatDateView($data, $completa = true) {
    if($data!=''){
        $mystring = $data;
        $findme = '-';
        $pos = strpos($mystring, $findme);
        if ($pos === false) {
            $subject = $data;
            if (strlen($subject) == 8) {
                $pattern = "/([0-9]{4})([0-9]{2})([0-9]{2})/";
                preg_match($pattern, $subject, $matchesarray);
                return $matchesarray[3] . "/" . $matchesarray[2] . "/" . $matchesarray[1];
            }else if (strlen($subject) == 14) {
                $pattern = "/([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})/";
                preg_match($pattern, $subject, $matchesarray);
                return $matchesarray[3] . "/" . $matchesarray[2] . "/" . $matchesarray[1];
            }
            return $data;
        } else {
            $subject = $data;
            //echo "->".strlen($subject)."<-";
            if (strlen($subject) == 10) {
                $pattern = "/([0-9]{4})-([0-9]{2})-([0-9]{2})/";
                preg_match($pattern, $subject, $matchesarray);
                return $matchesarray[3] . "/" . $matchesarray[2] . "/" . $matchesarray[1];
            }else if (strlen($subject) == 14) {
                $pattern = "/([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})/";
                preg_match($pattern, $subject, $matchesarray);
                return $matchesarray[3] . "/" . $matchesarray[2] . "/" . $matchesarray[1];
            } else {
                if ($completa == true) {
                    $pattern = "/([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})/";
                    preg_match($pattern, $subject, $matchesarray);
                    return $matchesarray[3] . "/" . $matchesarray[2] . "/" . $matchesarray[1] . " " . $matchesarray[4] . ":" . $matchesarray[5];
                } else {
                    $pattern = "/([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})/";
                    preg_match($pattern, $subject, $matchesarray);
                    return $matchesarray[3] . "/" . $matchesarray[2] . "/" . $matchesarray[1];
                }
            }
        }
    }
}
//$dataEmissao = $objEv->getDataEmissaoDoc(); // Supondo que essa função retorna "2023-09-04"
//
//// Converte a data para um objeto DateTime
//$dataObj = new DateTime($dataEmissao);
//
//// Obtém o dia, mês e ano da data
//$dia = $dataObj->format('d');
//$mes = $dataObj->format('F'); // 'F' retorna o mês por extenso
//$ano = $dataObj->format('Y');
//
//// Array associativo para converter o nome do mês para português
//$mesesEmPortugues = [
//    'January' => 'janeiro',
//    'February' => 'fevereiro',
//    'March' => 'março',
//    'April' => 'abril',
//    'May' => 'maio',
//    'June' => 'junho',
//    'July' => 'julho',
//    'August' => 'agosto',
//    'September' => 'setembro',
//    'October' => 'outubro',
//    'November' => 'novembro',
//    'December' => 'dezembro'
//];
//
//$mesEmPortugues = $mesesEmPortugues[$mes];
//
//// Monta a data formatada
//$dataFormatada = "{$dia} de {$mesEmPortugues} de {$ano}";
//
//echo $dataFormatada; // Saída: "04 de setembro de 2023"
function formatDateViewPorExtenso($data, $completa = true) {
    $mystring = $data;
    $findme = '-';
    $pos = strpos($mystring, $findme);
    if ($pos === false) {
        return $data;
    } else {
        $subject = $data;
        //echo "->".strlen($subject)."<-";
        if (strlen($subject) == 10) {
            $pattern = "/([0-9]{4})-([0-9]{2})-([0-9]{2})/";
            preg_match($pattern, $subject, $matchesarray);
            return $matchesarray[3] . " de " . getMonthName($matchesarray[2]) . " de " . $matchesarray[1];
        } else {
            if ($completa == true) {
                $pattern = "/([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})/";
                preg_match($pattern, $subject, $matchesarray);
                return $matchesarray[3] . " de " . getMonthName($matchesarray[2]) . " de " . $matchesarray[1] . " às " . $matchesarray[4] . " horas e " . $matchesarray[5] . " minutos";
            } else {
                $pattern = "/([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})/";
                preg_match($pattern, $subject, $matchesarray);
                return $matchesarray[3] . " de " . getMonthName($matchesarray[2]) . " de " . $matchesarray[1];
            }
        }
    }
}

function formatDateInsert($data, $completa = true) {
    $mystring = $data;
    $findme = '/';
    $pos = strpos($mystring, $findme);
    if ($pos === false) {
//        if (strpos($data, 'T') !== false) {
//            $separado = explode('T', $data);
//            $data = $separado[0];
//            $hora = $separado[1];
//            if (strlen($hora) === 5) {
//                $hora .= ':00';
//            }
//            $data = $data . ' '.$hora
//        }
        return $data;
    } else {
        $subject = $data;
        if (strlen($subject) == 10) {
            $pattern = "/([0-9]{2})\/([0-9]{2})\/([0-9]{4})/";
            preg_match($pattern, $subject, $matchesarray);
            return $matchesarray[3] . "-" . $matchesarray[2] . "-" . $matchesarray[1];
        } else {
            if ($completa == true) {
                $pattern = "/([0-9]{2})\/([0-9]{2})\/([0-9]{4}) ([0-9]{2}):([0-9]{2})/";
                preg_match($pattern, $subject, $matchesarray);
                return $matchesarray[3] . "-" . $matchesarray[2] . "-" . $matchesarray[1] . " " . $matchesarray[4] . ":" . $matchesarray[5];
            } else {
                $pattern = "/([0-9]{2})\/([0-9]{2})\/([0-9]{4}) ([0-9]{2}):([0-9]{2})/";
                preg_match($pattern, $subject, $matchesarray);
                return $matchesarray[3] . "-" . $matchesarray[2] . "-" . $matchesarray[1] . " " . $matchesarray[4] . ":" . $matchesarray[5];
            }
        }
    }
}
/**
 * 
 * @param type $data
 * @return inteiro 
 * independente do formato da data de entrada , ex: 2008-04-18 00:01:00,
 * remove-se todos os sinais e espaços e returna o valor informado no formato YYYYMMDDHHIISS ex: 20080418000100
 */
function formatDateTimeSemSeparador($data = null) {
    if (is_null($data) || $data === '') {
        return '';
    }
    $loc = array(" ", "-", "/", ":"); 
    return str_replace($loc, "", $data);
}
/**
 * @param type $data 
 * @param type $formatIn DDMMYYYY, YYYYMMDD, DD/MM/YYYY, YYYY/MM/DD, DD-MM-YYYY, YYYY-MM-DD
 * @param type $formatOut DDMMYYYY, YYYYMMDD, DD/MM/YYYY, YYYY/MM/DD, DD-MM-YYYY, YYYY-MM-DD
 * @return data no formato definido em $formatOut
 */
function formatDateHoraTrans($data, $formatIn=false, $formatOut=false) {
    
    if (!isset($data) || empty($data)) {
        return null;
    }
    
    $data = str_replace('T', ' ', $data);
    $formatIn = str_replace('T', ' ', $formatIn);
    $data      = explode(' ', $data);
    $formatIn  = explode(' ', $formatIn);
//    $formatOut = explode(' ', $formatOut);
    
    /*Remove separadores (-,/) da data de entrada e do formato de entrada 
     *para reduzir opções no switch de entrada*/
    $data[0]     = formatDateTimeSemSeparador($data[0]);
    $formatIn[0] = formatDateTimeSemSeparador($formatIn[0]);
        $data[1]     = isset($data[1])?      formatDateTimeSemSeparador($data[1]) : null;
    $formatIn[1] = isset($formatIn[1])?  formatDateTimeSemSeparador($formatIn[1]) : null;
    
    /*Independente do formato de estrada, transformar sempre para o padão brasileiro DD/MM/YYYY*/
//    echo $formatIn[0].$formatIn[1];
    switch ($formatIn[0].$formatIn[1]) {
        case 'YYYYMMDD':
            $pattern = "/([0-9]{4})([0-9]{2})([0-9]{2})/";
            preg_match($pattern, $data[0], $matchesarray);
            $data[0] = $matchesarray[3] . "/". $matchesarray[2] . "/".  $matchesarray[1]." 00:00:00";    
            break;
        case 'YYYYMMDDHHII':
            $pattern = "/([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})/";
            preg_match($pattern, $data[0].$data[1], $matchesarray);
            $data[0] = $matchesarray[3] . "/". $matchesarray[2] . "/".  $matchesarray[1]." ".$matchesarray[4].":".$matchesarray[5].":00";    
            break;
        case 'YYYY-MM-DD HH:II:SS':
            $pattern = "/([0-9]{4})-([0-9]-{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})/";
            preg_match($pattern, $data[0].$data[1], $matchesarray);
            $data[0] = $matchesarray[3] . "/". $matchesarray[2] . "/".  $matchesarray[1]." ".$matchesarray[4].":".$matchesarray[5].":00";    
            break;
        case 'YYYYMMDDHHIISS':
            $pattern = "/([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})/";
            preg_match($pattern, $data[0].$data[1], $matchesarray);
            $data[0] = $matchesarray[3] . "/". $matchesarray[2] . "/".  $matchesarray[1]." ".$matchesarray[4].":".$matchesarray[5].":".$matchesarray[6];    
            break;
        case 'DDMMYYYY':
            $pattern = "/([0-9]{2})([0-9]{2})([0-9]{4})/";
            preg_match($pattern, $data[0], $matchesarray);
            $data[0] = $matchesarray[1] . "/". $matchesarray[2] . "/".  $matchesarray[3]." 00:00:00";    
            break;
        case 'DDMMYYYYHHII':
            $pattern = "/([0-9]{2})([0-9]{2})([0-9]{4})([0-9]{2})([0-9]{2})/";
            preg_match($pattern, $data[0].$data[1], $matchesarray);
            $data[0] = $matchesarray[1] . "/". $matchesarray[2] . "/".  $matchesarray[3]." ".$matchesarray[4].":".$matchesarray[5].":00";   
            break;
        case 'DDMMYYYYHHIISS':
            $pattern = "/([0-9]{2})([0-9]{2})([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{2})/";
            preg_match($pattern, $data[0].$data[1], $matchesarray);
            $data[0] = $matchesarray[1] . "/". $matchesarray[2] . "/".  $matchesarray[3]." ".$matchesarray[4].":".$matchesarray[5].":".$matchesarray[6];    
            break;
        case 'DDMMYY':
            $pattern = "/([0-9]{2})([0-9]{2})([0-9]{2})/";
            preg_match($pattern, $data[0], $matchesarray);
            $data[0] = $matchesarray[1] . "/". $matchesarray[2] . "/20".  $matchesarray[3];    
            break;
        default:
            echo "Formato de entrada não identificado";
            break;
    }
    unset($matchesarray);
    $pattern = "/([0-9]{2})\/([0-9]{2})\/([0-9]{4}) ([0-9]{2}):([0-9]{2}):([0-9]{2})/";
    preg_match($pattern, $data[0], $matchesarray);

    switch ($formatOut) {
        case 'DD/MM/YYYY HH:II':
            $data[0] = $matchesarray[1] . "/". $matchesarray[2] . "/".  $matchesarray[3]." ". $matchesarray[4].":".$matchesarray[5];    
            break;
        case 'DD/MM/YYYY':
            $data[0] = $matchesarray[1] . "/". $matchesarray[2] . "/".  $matchesarray[3];    
            break;
        case 'DD-MM-YYYY':
            $data[0] = $matchesarray[1] . "-". $matchesarray[2] . "-".  $matchesarray[3];    
            break;
        case 'DDMMYYYY':
            $data[0] = $matchesarray[1] . $matchesarray[2] . $matchesarray[3];    
            break;
        case 'YYYY/MM/DD':
            $data[0] = $matchesarray[3] . "/". $matchesarray[2] . "/".  $matchesarray[1];    
            break;
        case 'YYYY-MM-DD':
            $data[0] = $matchesarray[3] . "-". $matchesarray[2] . "-".  $matchesarray[1];    
            break;
        case 'YYYYMMDD':
            $data[0] = $matchesarray[3] . $matchesarray[2] . $matchesarray[1];    
            break;
        case 'YYYY-MM-DD':
            $data[0] = $matchesarray[3] .'-'. $matchesarray[2] .'-'. $matchesarray[1];    
            break;
        case 'YYYYMMDDHHII':
            $data[0] = $matchesarray[3] . "". $matchesarray[2] ."". $matchesarray[1]."".$matchesarray[4]."".$matchesarray[5];    
            break;
        case 'YYYYMMDDHHIISS':
            $data[0] = $matchesarray[3] . "". $matchesarray[2] ."". $matchesarray[1]."".$matchesarray[4]."".$matchesarray[5]."".$matchesarray[6];    
            break;
        case 'YYYY-MM-DD HH:II:SS':
            $data[0] = $matchesarray[3] . "-". $matchesarray[2] ."-". $matchesarray[1]." ".$matchesarray[4].":".$matchesarray[5].":".$matchesarray[6];    
            break;
        case 'YYYY-MM-DDTHH:II':
            $data[0] = $matchesarray[3] . "-". $matchesarray[2] ."-". $matchesarray[1]."T".$matchesarray[4].":".$matchesarray[5];      
            break;
        case 'YYYY-MM-DD HH:II':
            $data[0] = $matchesarray[3] . "-". $matchesarray[2] ."-". $matchesarray[1]." ".$matchesarray[4].":".$matchesarray[5];      
            break;
        default:
//            echo "Formato de saida não identificado";
            break;
    }
    return $data[0];
}
function formatDateCompare($data, $completa = true) {
    $mystring = $data;
    $findme = '/';
    $pos = strpos($mystring, $findme);
    if ($pos === false) {
        return $data;
    } else {
        $subject = $data;
        if (strlen($subject) == 10) {
            $pattern = "/([0-9]{2})\/([0-9]{2})\/([0-9]{4})/";
            preg_match($pattern, $subject, $matchesarray);
            return $matchesarray[3] . "" . $matchesarray[2] . "" . $matchesarray[1];
        } else {
            if ($completa == true) {
                $pattern = "/([0-9]{2})\/([0-9]{2})\/([0-9]{4}) ([0-9]{2}):([0-9]{2})/";
                preg_match($pattern, $subject, $matchesarray);
                return $matchesarray[3] . "" . $matchesarray[2] . "" . $matchesarray[1] . "" . $matchesarray[4] . "" . $matchesarray[5];
            } else {
                $pattern = "/([0-9]{2})\/([0-9]{2})\/([0-9]{4}) ([0-9]{2}):([0-9]{2})/";
                preg_match($pattern, $subject, $matchesarray);
                return $matchesarray[3] . "" . $matchesarray[2] . "" . $matchesarray[1] . "" . $matchesarray[4] . "" . $matchesarray[5];
            }
        }
    }
}
function isValidDate($date_str, $format = 'Y-m-d') {
    $date = DateTime::createFromFormat($format, $date_str);
    return $date && $date->format($format) === $date_str;
}
function formatMoneyItauShopline($moneyValue) {
    if (strlen($moneyValue) >= 1) {
        return str_replace('.', '', formatMoneyView($moneyValue));
    }
}
function formatMoneyBoleto($moneyValue) {
    // Remover qualquer caractere não numérico, exceto ponto e vírgula
    $moneyValue = str_replace(',', '.', preg_replace('/[^\d,]/', '', $moneyValue));
    
    // Verificar se o valor é numérico após a limpeza
    if (is_numeric($moneyValue)) {
        return number_format((float)$moneyValue, 2, ',', '');
    }
    
    // Retornar null ou lançar uma exceção se o valor não for numérico
    return null; // ou lançar uma exceção, conforme necessário
}
function formatMoneySisprime($moneyValue) {
    // Substitui o ponto pela vírgula
    $formattedValue = str_replace('.', ',', $moneyValue);
    return $formattedValue;
}
function formatMoneyView($moneyValue) {
//    $loc = array(" ", "-", "/", ":", "T");
//    if ($moneyvalue != ''){
        if ((strlen($moneyValue) >= 1)and($moneyValue)) {
            //       $removeZero = strlen($moneyValue)-2;
            //       $moneyValue = substr($moneyValue,0, $removeZero);
            //       return number_format($moneyValue, 2, ',', '.');
            //        echo $moneyValue.'<br>';
            if (strpos($moneyValue, '.') !== false) {
                return number_format($moneyValue, 2, ',', '');
            }else{
                $moneyValue = $moneyValue/100;
                return number_format($moneyValue, 2, ',', '');
            }
        }
//    }

}

function formatMoneyIntView($moneyValue) {
    if ((strlen($moneyValue) >= 1)and($moneyValue)) {
//       $removeZero = strlen($moneyValue)-2;
//       $moneyValue = substr($moneyValue,0, $removeZero);
//       return number_format($moneyValue, 2, ',', '.');
//        echo $moneyValue.'<br>';
        if (strpos($moneyValue, '.') !== false) {
            return number_format($moneyValue, 2, ',', '');
        }else{
            $moneyValue = $moneyValue/100;
            return number_format($moneyValue, 2, ',', '');
        }
    }
}
function formatValueToDotSeparator($value) {
    // Remove qualquer possível separador de milhar e substitui vírgula por ponto
    $value = str_replace([',', '.'], '', $value);
    // Insere o ponto como separador decimal
    $value = substr_replace($value, '.', -2, 0);
    return $value;
}
function formatMoneyInsert($moneyValue) {
    if (strlen($moneyValue) >= 1) {

        $money['i'] = substr($moneyValue, 0,-3);
        $money['c'] = substr($moneyValue, -3);

        $money['i'] = str_replace(".", "", $money['i']);
        $money['c'] = str_replace(",", ".", $money['c']);

        $moneyValue = $money['i'].$money['c'];
        $moneyValue = str_replace(",", ".", $moneyValue);
        return $moneyValue;
    }
}
function formatMoneyInsertPagamento($moneyValue) {
    if (strlen($moneyValue) >= 1) {
        
        $money['i'] = substr($moneyValue, 0,-3);
        $money['c'] = substr($moneyValue, -3);

        $money['i'] = str_replace(".", "", $money['i']);
        $money['c'] = str_replace(",", "", $money['c']);

        $moneyValue = $money['i'].'.'.$money['c'];
//        $moneyValue = str_replace(",", "", $moneyValue);
//        $moneyValue = str_replace(".", "", $moneyValue);
        return $moneyValue;
    }
}

function is_date($date) {
    $date = str_replace(array('\'', '-', '.', ','), '/', $date);
    $date = explode('/', $date);

    if (count($date) == 1 // No tokens
            and is_numeric($date[0]) and $date[0] < 20991231 and
            ( checkdate(substr($date[0], 4, 2)
                    , substr($date[0], 6, 2)
                    , substr($date[0], 0, 4)))
    ) {
        return true;
    }

    if (count($date) == 3 and is_numeric($date[0]) and is_numeric($date[1]) and is_numeric($date[2]) and
            ( checkdate($date[0], $date[1], $date[2]) //mmddyyyy
            or checkdate($date[1], $date[0], $date[2]) //ddmmyyyy
            or checkdate($date[1], $date[2], $date[0])) //yyyymmdd
    ) {
        return true;
    }

    return false;
}

function getMonthName($mes) {
    //$strTime = mktime(1, 1, 1, $Month, 1, date("Y"));
    //return date("F", $strTime);
    $mes = intval($mes);
    switch ($mes) {
        case 1:
            return "Janeiro";
            break;
        case 2:
            return "Fevereiro";
            break;
        case 3:
            return "Março";
            break;
        case 4:
            return "Abril";
            break;
        case 5:
            return "Maio";
            break;
        case 6:
            return "Junho";
            break;
        case 7:
            return "Julho";
            break;
        case 8:
            return "Agosto";
            break;
        case 9:
            return "Setembro";
            break;
        case 10:
            return "Outubro";
            break;
        case 11:
            return "Novembro";
            break;
        case 12:
            return "Dezembro";
            break;
        default:
            break;
    }
}

/**
 * Função que retorna o dia da semana em extenÃÂ§o
 * de uma determinada data, se não informar a data mostra o dia da
 * data corrente
 * @param int[optional] $dia
 * @return string Dia da semana
 */
function diasemana($dia) {

    if (empty($dia)) {
        $dia = date("w");
    }
    switch ($dia) {
        case 0:
            $diasemana = "Domingo";
            break;
        case 1:
            $diasemana = "Segunda-Feira";
            break;
        case 2:
            $diasemana = "Teça-Feira";
            break;
        case 3:
            $diasemana = "Quarta-Feira";
            break;
        case 4:
            $diasemana = "Quinta-Feira";
            break;
        case 5:
            $diasemana = "Sexta-Feira";
            break;
        case 6:
            $diasemana = "Sábado";
            break;
    }

    return $diasemana;
}

/*
 * @fws['Data']=dd/mm/YYYY
 * data[0]=dia
 * data[1]=mes
 * data[2]=ano
 */

function getDiaSemana($dataTeste, $debug = false) {

    $data = explode("/", $dataTeste);
    setlocale(LC_TIME, "portuguese");
    $diaSemana = date("l", mktime(0, 0, 0, $data[1], $data[0], $data[2]));
    return $diaSemana;
}

/*
 * @fws['Data']=dd/mm/YYYY
 * data[0]=dia
 * data[1]=mes
 * data[2]=ano
 */

function testDiaUtil($fws, $debug = false) {

    $data = explode("/", $fws['dataTeste']);
    setlocale(LC_TIME, "portuguese");
    $diaSemana = date("l", mktime(0, 0, 0, $data[1], $data[0], $data[2]));
    if (
            ($diaSemana == "Sábado") or ($diaSemana == "Saturday") or
            ($diaSemana == "Domingo") or ($diaSemana == "Sunday") or
            (FWF_Feriado($y . "-" . $m . "-" . $d))
    ) {
        $testDiaUtil = false;
    } else {
        $testDiaUtil = true;
    }
    return $testDiaUtil;
}
/**
 * Verifica se a data atual está entre as datas especificadas.
 *
 * @param string $dataInicio Data de início no formato YYYYMMDDHHMMSS.
 * @param string $dataFim Data de término no formato YYYYMMDDHHMMSS.
 * @return bool Retorna verdadeiro se a data atual estiver entre as datas de início e fim, caso contrário, retorna falso.
 * # Se informado 2008-04-18 00:01:00, ficará assim aaaammddhhii
 */
function testaPeriodo($data_ini, $data_fim, $debug = false) {
    /*@todo
     *  usando a constante Data_HojeHM não esta dando certo
     *  para setar a data de simulação
     */
    $Data_HojeHM = Date('YmdHis');

    $loc = array(" ", "-", "/", ":", "T"); 
    $data_ini = limparData($data_ini);
    $data_fim = limparData($data_fim);
    echo $debug? $data_ini.'<br>' : '';
    if (($data_ini <= $Data_HojeHM) and ($Data_HojeHM <= $data_fim)) {
        $FWS_Testa_Periodo = true;
        echo $debug? 'true<br>' : '';
    } else {
        echo $debug? 'false<br>' : '';
        $FWS_Testa_Periodo = false;
    }

    if ($debug) {
        echo $data_ini . "<=" . $Data_HojeHM . "<=" . $data_fim . "<br>";
    }

    return $FWS_Testa_Periodo;
}

function testaNaoIniciadaOuJaEncerrada($data_ini, $data_fim, $debug = false) {
    /*
     * Retorna verdadeiro se hj estiver entre as datas especificadas.
     * formato das datas de entrada YYYYMMDDHHMMSS
     */
    /*@todo
     *  usando a constante Data_HojeHM não esta dando certo
     *  para setar a data de simulação
     */
    $Data_HojeHM = Date('YmdHis');    
    echo $debug? $data_ini.'<br>' : '';
    $data_ini = limparData($data_ini);
    $data_fim = limparData($data_fim);
    echo $debug? $data_ini.'<br>' : '';

    if ($debug) {
        echo $data_ini . "<=" . $Data_HojeHM . "<=" . $data_fim . "<br>";
    }

    if ($data_fim < $Data_HojeHM) {
        return "Encerrada";
    }
    if ($Data_HojeHM < $data_ini) {
        return "Aguardando Período";
    }
}

/*retorna true se estiver vencimento*/
function testeDataVencimento($data_vencimento, $debug = false) {
    if($_SERVER['REMOTE_ADDR']=='177.82.0.28-'){
        $debug=true;
    }
    
    $Data_HojeHM = Date('YmdHis');
    $data_vencimento = limparData($data_vencimento);

    echo $debug? $data_vencimento.'<br>' : '';
    if ($debug) {
        echo  $data_vencimento. "<" .$Data_HojeHM. "<br>";
    }
    if ($data_vencimento < $Data_HojeHM) {
        return true;
    }
    return false;
}
function limparData($dataString){
    $loc = array(" ", "-", "/", ":", "T"); # ÃÂ© assim 2008-04-18 00:01:00, mas deve ficar assim aaaammddhhii
    if ($dataString != ''){
        $dataString = str_replace($loc, "", $dataString);
        $dataString = str_pad($dataString, 14, "0"); 
    }
    return $dataString;
}
/**
 * Adiciona 'n' zeros(0) ÃÂ  esquerda de um numero informado
 * @param String $number NÃºmero a ser adicionado os zeros(0)
 * @param Integer $n Quantidade de zeros(0) a serem adicionados
 * @return String Valor do numero concatenado com os zeros (0) a esquerda.
 */
function numberPad($number, $n) {
    $number = $number? $number : 0;
    return str_pad($number, $n, "0", STR_PAD_LEFT);
}

//function rrmdir($dir) {
//    foreach(glob($dir . '/*') as $file) {
//        echo $file."<br>";
//        if(is_dir($file))
//            rrmdir($file);
//        else
//            unlink($file);
//    }
//    rmdir($dir);
//}
//
//$user_name = 'f-sisweb';
//$path = '/insc/view/certificado/certificados/06/';
//
//rrmdir($path);
////chown($path, $user_name);
////unlink(555555550000_55555555_555555.pdf');
//exit();
// Gera campos input hidden a partir de um array de campos.
function criaHiddens($pArray, $debug = false) {
    echo($debug ? $pArray . "<hr>" : "");

    foreach ($pArray as $key => $value) {
        if (is_array($value)) {
            if ($key != 'Logado') {
                echo($debug ? "Logado<hr>" : "");
                foreach ($value as $key1 => $value1) {
                    if ($debug) {
                        echo "\n " . $key1
                        . ":<input type='text' name='"
                        . $key1 . "' value='"
                        . $value1 . "' /><br>";
                    } else {
                        echo "\n    <input type=\"hidden\" name=\"" . $key1 . "\" value=\"" . $value1 . "\" />";
                    }
                }
            }
        } else {
            if ($debug) {
                echo "\n    " . $key . ":<input type='text' name='" . $key . "' value='" . $value . "' /><br>";
            } else {
                echo "\n    <input type=\"hidden\" name=\"" . $key . "\" value=\"" . $value . "\" />";
            }
        }
    }
}
function verificarECriarPasta($caminho) {
    if (!file_exists($caminho)) {
        if (!mkdir($caminho, 0777, true)) {
            return false;
        }
    }
    return true;
}
function excluirArquivo($arquivo){ 
    if(file_exists($arquivo)) {
        if(!unlink($arquivo)) {
            throw new Exception("Não foi possível remover o arquivo fisicamente.");
        }
    }
}
/**
* array('doc', 'docx', 'pdf', 'csv', 'txt', 'xls')
* pode-se usar apenas a lista interna do array
*/
function validarArquivo($FILES, $tamanho, $tiposPermitidos){
    if($FILES['arquivo']['name']==''){
        throw new Exception("Arquivo não informado");
    }
    if ($FILES['arquivo']['size'] > convertSizeToBytes($tamanho, )){
       throw new Exception("Não é permitido arquivos maiores que ".$tamanho, );
    }
    if ($FILES['arquivo']['tmp_name'] == '') {
        throw new Exception("Arquivo temporário não encontrado.");
    }

    $arrayMineTypeString = createMimeTypesArray($tiposPermitidos,true);
    if (!in_array($FILES['arquivo']['type'], $arrayMineTypeString['mimeTypes'])) {
        throw new Exception("Extensão de arquivo somente tipo .(".$arrayMineTypeString['typesString'].")");
    }
}
function gravandoArquivo($FILES, $pastaDestino, $nomeArquivo, $debug=false){

    $destinoFinal = $pastaDestino.'/'.$nomeArquivo;
    if(move_uploaded_file($FILES['arquivo']['tmp_name'], $destinoFinal)){
        echo $debug? "Arquivo carregado com sucesso!<br>" : "";
        return true;
    } else {
        if ($debug){
            print_r($FILES);
            echo $destinoFinal;
        }
        // Diagnostico: o codigo de upload do PHP costuma ser 0 (UPLOAD_ERR_OK),
        // ou seja o upload em si deu certo; o que falha aqui e MOVER o arquivo
        // para a pasta de destino. Por isso enriquecemos a mensagem com o estado
        // real da pasta (existe? gravavel?) + ultimo erro do PHP (error_get_last),
        // que funciona mesmo com display_errors desligado em producao.
        $codUpload = isset($FILES["arquivo"]["error"]) ? $FILES["arquivo"]["error"] : 'desconhecido';
        $ultimoErro = error_get_last();
        $msg  = "Falha ao gravar o arquivo enviado.";
        $msg .= " [cod_upload_php=" . $codUpload . ($codUpload === 0 ? ' (upload OK, falhou ao mover)' : '') . "]";
        $msg .= " [pasta_destino=" . $pastaDestino . "]";
        $msg .= " [pasta_existe=" . (is_dir($pastaDestino) ? 'sim' : 'NAO') . "]";
        $msg .= " [pasta_gravavel=" . (is_writable($pastaDestino) ? 'sim' : 'NAO') . "]";
        if ($ultimoErro && !empty($ultimoErro['message'])) {
            $msg .= " [php=" . $ultimoErro['message'] . "]";
        }
        throw new Exception($msg);
    }
}
function verificarArquivoExiste($pathArquivo, $debug=false){
    if (file_exists($pathArquivo)) {
        return true;
    }
    return false;
}
/**
 * 
 * @param type $text conteudo
 * @param type $fnName nome do arquivo
 * @param type $executar true para ser executado
 */
function executarLog($text, $fnName = 'log', $executar = false) {
    if ($executar) {
        if (is_object($fnName)) {
            $nomeClasse = get_class($fnName);
            $nomeArquivoLog = $nomeClasse;
        } else {
            $nomeArquivoLog = $fnName;
        }
        // $dirLog = WB_PATH_LOG;
        $dirLog = WB_ROOT . '../logn/eventos/';
        ((file_exists($dirLog)) ? "" : mkdir($dirLog, 755));
        $dirLog .= "geral/";
        ((file_exists($dirLog)) ? "" : mkdir($dirLog, 755));

        if ($nomeArquivoLog === 'roteamento-bloqueado' && $text) {
            require_once dirname(__FILE__) . '/protecaoIPBloqueado.php';
            $ipReq = $_SERVER['REMOTE_ADDR'];
            if (protecaoIPBloqueado_verificar($ipReq)) {
                exit;
            }
            $contentPost = isset($_POST['content']) ? $_POST['content'] : '';
            $text .= " | Method: " . @$_SERVER['REQUEST_METHOD']
                  . " | URL: " . @$_SERVER['REQUEST_URI']
                  . ($contentPost !== '' ? " | POST.content: " . $contentPost : "");
        }

        $fileLog = $dirLog . Date('Ymd') . "-" . $nomeArquivoLog . ".txt";
        if ($text) {
            $text = Date('Ymd H:i:s-') . "<->" . $text . "<->" . $_SERVER["REMOTE_ADDR"] . "<->" . $_SERVER["HTTP_USER_AGENT"] . "\n";
        } else {
            $text = Date('Ymd H:i:s-') . "<->" . $_SERVER["REMOTE_ADDR"] . "<->" . $_SERVER["HTTP_USER_AGENT"] . "\n";
        }
        file_put_contents($fileLog, $text, FILE_APPEND);

        if ($nomeArquivoLog === 'roteamento-bloqueado' && function_exists('protecaoIPBloqueado_registrar')) {
            $bloqueouAgora = protecaoIPBloqueado_registrar($_SERVER['REMOTE_ADDR']);
            if ($bloqueouAgora) {
                executarLog("IP AUTO-BLOQUEADO: " . $_SERVER['REMOTE_ADDR'] . " | Limite excedido (" . PROTECAO_IP_LIMITE_HITS . " hits em " . PROTECAO_IP_JANELA_SEG . "s) | Validade: " . PROTECAO_IP_BLOQUEIO_SEG . "s", "ip-auto-bloqueado", true);
            }
        }
    }
}
function executarLogApp($text, $fnName = 'log', $executar = true) {
    if ($executar) {
        if (is_object($fnName)) {
            $nomeClasse = get_class($fnName);
            $nomeArquivoLog = $nomeClasse;
        } else {
            $nomeArquivoLog = $fnName;
        }
        
        if (is_array($text)){
            ob_start();
            echo "Texto Log:<br>";
            print_r($text);
            $text = ob_get_clean();
        }
        
        $dirLog = WB_PATH_LOG_APP;
        ((file_exists($dirLog)) ? "" : mkdir($dirLog, 755));
        $dirLog .= "log/";
        ((file_exists($dirLog)) ? "" : mkdir($dirLog, 755));
        $fileLog = $dirLog . Date('Ymd') . "-" . $nomeArquivoLog . ".txt";
        if ($text) {
            $text = Date('Ymd H:i:s-') . "<->" . $text . "<->" . $_SERVER["REMOTE_ADDR"] . "<->" . $_SERVER["HTTP_USER_AGENT"] . "\n";
        } else {
            $text = Date('Ymd H:i:s-') . "<->" . $_SERVER["REMOTE_ADDR"] . "<->" . $_SERVER["HTTP_USER_AGENT"] . "\n";
        }
        file_put_contents($fileLog, $text, FILE_APPEND);
    }
}
function executarLogReg($text, $fnName = 'log', $executar = false) {
    if ($executar) {
        if (is_object($fnName)) {
            $nomeClasse = get_class($fnName);
            $nomeArquivoLog = $nomeClasse;
        } else {
            $nomeArquivoLog = $fnName;
        }

        $dirLog = WB_PATH_LOG;
        ((file_exists($dirLog)) ? "" : mkdir($dirLog, 755));

        $fileLog = $dirLog .  $nomeArquivoLog;
//        if ($text) {
//            $text = Date('Ymd H:i:s-') . "<->" . $text . "<->" . $_SERVER["REMOTE_ADDR"] . "<->" . $_SERVER["HTTP_USER_AGENT"] . "\n";
//        } else {
//            $text = Date('Ymd H:i:s-') . "<->" . $_SERVER["REMOTE_ADDR"] . "<->" . $_SERVER["HTTP_USER_AGENT"] . "\n";
//        }
        file_put_contents($fileLog, $text, FILE_APPEND);
    }
}

function calcularIdade($data_nasc) {

    $pattern = "/([0-9]{2})/([0-9]{2})/([0-9]{4})/";
    //separa a data de nascimento em array
    preg_match($pattern, $data_nasc, $matchesarray);
    $data_nasc = $matchesarray;

    $data_hoje = date("d/m/Y"); //data de hoje
    //separa a data de hoje em array
    preg_match($pattern, $data_hoje, $matchesarray);
    $data_hoje = $matchesarray;

    $idade = $data_hoje[3] - $data_nasc[3];

    if ($data_nasc[2] > $data_hoje[2]) { //verifica se o mÃÂªs de nascimento ÃÂ© maior que o mÃÂªs atual
        $idade--; //tira um ano, já que ele não fez aniversário ainda
    } elseif ($data_nasc[2] == $data_hoje[2] && $data_nasc[1] > $data_hoje[1]) { //verifica se o dia de hoje ÃÂ© maior que o dia do aniversário
        $idade--; //tira um ano se não fez aniversário ainda
    }
    return $idade; //retorna a idade da pessoa em anos
}

//converteLatinHTML
function tratarJson($texto) {
    $texto = str_replace("\r", '\r', $texto);
    $texto = str_replace("\n", '\n', $texto);
    $texto = str_replace("\t", '\t', $texto);
    $texto = addslashesopctec($texto);
    return $texto;
}

function addslashesopctec($texto) {
    if ($texto != ''){
        $texto = addslashes($texto);
    }
    return $texto;
}

function retirarBarras($texto) {
    $texto = str_replace("\\", "", $texto);
    $texto = str_replace("/", "", $texto);
    return $texto;
}

function limparString($texto){
    $texto = trim($texto);
    $texto = strip_tags($texto,",");
    $texto = str_replace(" ", "", $texto);
    $texto = str_replace("&#039;", "", $texto);
    $texto = preg_replace('/^\xEF\xBB\xBF/', '', $texto);
    // Now remove any doubled-up whitespace
    $texto = preg_replace('/\s(?=\s)/', '', $texto);
    // Finally, replace any non-space whitespace, with a space
    $texto = preg_replace('/[\n\r\t\"]/', ' ', $texto);
    $texto = addslashesopctec($texto);
    return $texto;
}

function removerHifen($texto) {
    $texto = str_replace("-", "", $texto);
    return $texto;
}

function removerHifenToEspaco($texto) {
    $texto = str_replace("-", " ", $texto);
    return $texto;
}

/**
 * Verifica o dominio do arquivo de origem
 * barra normal / se servidor for  linux
 * duas contra barras para se servidor for windows
 * @param __FILE__ arquivo de origem
 * @return String com a perspectiva
 */
function getPerspectivaInternaUI($arquivoOrigem, $debug = false) {
    echo($debug ? $arquivoOrigem . "<hr>" : "");
    switch (true) {
        case (stristr($arquivoOrigem, "/view/view/admin/") or stristr($arquivoOrigem, "\\view\\admin\\")):
            echo($debug ? __LINE__ . " admin<hr>" : "");
            return 'perspectivaAdministrador';
            break;
        case (stristr($arquivoOrigem, "/view/owner/") or stristr($arquivoOrigem, "\\view\\owner\\")):
            echo($debug ? __LINE__ . " owner<hr>" : "");
            return 'perspectivaCoordenador';
            break;
        default:
            echo($debug ? "->" . stristr($arquivoOrigem, "\\view\\admin\\") . "<-<hr>" : "");
            echo($debug ? __LINE__ . " default<hr>" : "");
            break;
    }
}
/*
function viewTempoLogado() {
    if ($fwsLogado["Logged"] == 1) {
        ?>
        <div id="sessao"></div>
        <script language="JavaScript">
                    tempo = 0;
                    tempoMaximo = 1800;

            function startCountdown() {
                // Se o tempo não for zerado
                //if((tempo + 1) >= 0){
                if (tempoMaximo >= (tempo + 1)) {
                    // Pega a parte inteira dos minutos
                    var min = parseInt(tempo / 60);
                    // Calcula os segundos restantes
                    var seg = tempo % 60;
                    // Formata o nÃºmero menor que dez, ex: 08, 07, ...
                    if (min < 10) {
                        min = "0" + min;
                        min = min.substr(0, 2);
                    }
                    if (seg <= 9) {
                        seg = "0" + seg;
                    }
                    // Cria a variável para formatar no estilo hora/cronÃÂ´metro
                    //horaImprimivel = '00:' + min + ':' + seg;
                    horaImprimivel = min + ':' + seg;
                    //JQuery pra setar o valor
                    //$("#sessao").html(horaImprimivel);
                    document.getElementById("sessao").innerHTML = 'Hora: ' + horaImprimivel;
                    // Define que a função será executada novamente em 1000ms = 1 segundo
                    setTimeout('startCountdown()', 1000);
                    // diminui o tempo
                    //tempo--;
                    tempo++;
                    // Quando o contador chegar a zero faz esta ação
                } else {
                    window.open('<?php echo  WB_URL . '/index.php?contents=' . WB_PATH . '/index.php' ?>&pagina=view/view/admin/principal.php', '_self');
                }
            }
            // Chama a função ao carregar a tela
            startCountdown();
        </script>
        <?php
    }
}
*/
function alternarBarras($texto) {
    switch ($_SERVER["SERVER_NAME"]) {
        case "dese.sistemasweb.localhost":
        case "localhost":            
        case "particular.localhost":
            $texto = str_replace("\\", "/", $texto);
            break;
        default:
            //$texto = str_replace("\\", "/", $texto);
            break;
    }
    return $texto;
}

function relativeURL() {
    $dir = str_replace('\\', '/', __DIR__);
    // Resolves inconsistency with PATH_SEPARATOR on Windows vs. Linux
    // Use dirname(__FILE__) in place of __DIR__ for older PHP versions
    return substr($dir, strlen($_SERVER['DOCUMENT_ROOT']));
    // Clip off the part of the path outside of the document root
}

function textoMaiusculoMinusculo($string) {
    $string = minusculo($string);
    $string = ucwords($string);
    $string = str_replace(" De ", " de ", $string);
    $string = str_replace(" Para ", " para ", $string);
    $string = str_replace(" e A ", " e a ", $string);
    $string = str_replace(" Aos ", " aos ", $string);
    $string = str_replace(" Em ", " em ", $string);
    $string = str_replace(" Sua ", " sua ", $string);
    $string = str_replace(" Como ", " como ", $string);
    $string = str_replace(" Por ", " por ", $string);
    $string = str_replace(" Da ", " da ", $string);
    $string = str_replace(" Das ", " das ", $string);
    $string = str_replace(" Do ", " do ", $string);
    $string = str_replace(" Dos ", " dos ", $string);
    $string = str_replace(" Do(a) ", " do(a) ", $string);
    $string = str_replace(" E ", " e ", $string);
    $string = str_replace(" Na ", " na ", $string);
    $string = str_replace(" No ", " no ", $string);
    $string = str_replace("Depart.", "Departamento ", $string);
    return $string;
}
function negritarResumo($string) {
//    $string = minusculo($string);
    $string = str_replace("Efeitos alcançados:", "<b>Efeitos alcançados:</b>", $string);
    $string = str_replace("Introdução:", "<b>Introdução:</b>", $string);
    $string = str_replace("Objetivo:", "<b>Objetivo:</b>", $string);
    $string = str_replace("Objetivos:", "<b>Objetivos:</b>", $string);
    $string = str_replace("Conclusão:", "<b>Conclusão:</b>", $string);
    $string = str_replace("Conclusões:", "<b>Conclusões:</b>", $string);
    $string = str_replace("Caracterização do problema:", "<b>Caracterização do problema:</b>", $string);
    $string = str_replace("Caracterização do Problema:", "<b>Caracterização do problema:</b>", $string);
    $string = str_replace("Fundamentação teórica:", "<b>Fundamentação teórica:</b>", $string);
    $string = str_replace("Descrição da experiência:", "<b>Descrição da experiência:</b>", $string);
    $string = str_replace("Método:", "<b>Método:</b>", $string);
    $string = str_replace("Métodos:", "<b>Métodos:</b>", $string);
    $string = str_replace("Metodologia:", "<b>Metodologia:</b>", $string);
    $string = str_replace("Metodologias:", "<b>Metodologias:</b>", $string);
    $string = str_replace("Resultado:", "<b>Resultado:</b>", $string);
    $string = str_replace("Resultados:", "<b>Resultados:</b>", $string);
    $string = str_replace("Resultado e conclusão:", "<b>Resultado e conclusão:</b>", $string);
    $string = str_replace("Resultados e discussão:", "<b>Resultados e discussão:</b>", $string);
    $string = str_replace("Resultado e discussões:", "<b>Resultado e discussões:</b>", $string);
    $string = str_replace("Resultados/Discussão:", "<b>Resultados/Discussão:</b>", $string);
    $string = str_replace("Resultados/Discussões:", "<b>Resultados/Discussões:</b>", $string);
    $string = str_replace("Resultado/Discussão:", "<b>Resultado/Discussão:</b>", $string);
    $string = str_replace("Resultado/Discussões:", "<b>Resultado/Discussões:</b>", $string);
    $string = str_replace("Recomendações:", "<b>Recomendações:</b>", $string);
    $string = str_replace("Reflexão sobre a experiência:", "<b>Reflexão sobre a experiência:</b>", $string);
    $string = str_replace("Reflexão:", "<b>Reflexão:</b>", $string);
    $string = str_replace("Reflexões:", "<b>Reflexões:</b>", $string);
    $string = str_replace("Justificativa:", "<b>Justificativa:</b>", $string);
    
    $string = str_replace("efeitos alcançados:", "<b>efeitos alcançados:</b>", $string);
    $string = str_replace("introdução:", "<b>introdução:</b>", $string);
    $string = str_replace("objetivo:", "<b>objetivo:</b>", $string);
    $string = str_replace("objetivos:", "<b>objetivos:</b>", $string);
    $string = str_replace("conclusão:", "<b>conclusão:</b>", $string);
    $string = str_replace("conclusões:", "<b>conclusões:</b>", $string);
    $string = str_replace("caracterização do problema:", "<b>caracterização do problema:</b>", $string);
    $string = str_replace("fundamentação teórica:", "<b>fundamentação teórica:</b>", $string);
    $string = str_replace("descrição da experiência:", "<b>descrição da experiência:</b>", $string);
    $string = str_replace("método:", "<b>método:</b>", $string);
    $string = str_replace("métodos:", "<b>métodos:</b>", $string);
    $string = str_replace("metodologia:", "<b>metodologia:</b>", $string);
    $string = str_replace("metodologias:", "<b>metodologias:</b>", $string);
    $string = str_replace("resultado:", "<b>resultado:</b>", $string);
    $string = str_replace("resultados:", "<b>resultados:</b>", $string);
    $string = str_replace("resultado e conclusão:", "<b>resultado e conclusão:</b>", $string);
    $string = str_replace("resultados e discussão:", "<b>resultados e discussão:</b>", $string);
    $string = str_replace("resultado e discussões:", "<b>resultado e discussões:</b>", $string);
    $string = str_replace("resultados/discussão:", "<b>resultados/discussão:</b>", $string);
    $string = str_replace("resultados/discussões:", "<b>resultados/discussões:</b>", $string);
    $string = str_replace("resultado/discussão:", "<b>resultado/discussão:</b>", $string);
    $string = str_replace("resultado/discussões:", "<b>resultado/discussões:</b>", $string);
    $string = str_replace("recomendações:", "<b>recomendações:</b>", $string);
    $string = str_replace("reflexão sobre a experiência:", "<b>reflexão sobre a experiência:</b>", $string);
    $string = str_replace("reflexão:", "<b>reflexão:</b>", $string);
    $string = str_replace("reflexões:", "<b>reflexões:</b>", $string);
    $string = str_replace("justificativa:", "<b>justificativa:</b>", $string);
    
    $string = str_replace("EFEITOS ALCANÇADOS:", "<b>Efeitos alcançados:</b>", $string);
    $string = str_replace("INTRODUÇÃO:", "<b>Introdução:</b>", $string);
    $string = str_replace("OBJETIVO:", "<b>Objetivo:</b>", $string);
    $string = str_replace("OBJETIVOS:", "<b>Objetivos:</b>", $string);
    $string = str_replace("CONCLUSÃO:", "<b>Conclusão:</b>", $string);
    $string = str_replace("CONCLUSÕES:", "<b>Conclusões:</b>", $string);
    $string = str_replace("CARACTERIZAÇÃO DO PROBLEMA:", "<b>Caracterização do problema:</b>", $string);
    $string = str_replace("FUNDAMENTAÇÃO TEÓRICA:", "<b>Fundamentação teórica:</b>", $string);
    $string = str_replace("DESCRIÇÃO DA EXPERIÊNCIA:", "<b>Descrição da experiência:</b>", $string);
    $string = str_replace("MÉTODO:", "<b>Método:</b>", $string);
    $string = str_replace("MÉTODOS:", "<b>Métodos:</b>", $string);
    $string = str_replace("METODOLOGIA:", "<b>Metodologia:</b>", $string);
    $string = str_replace("METODOLOGIAS:", "<b>Metodologias:</b>", $string);
    $string = str_replace("RESULTADO:", "<b>Resultado:</b>", $string);
    $string = str_replace("RESULTADOS:", "<b>Resultados:</b>", $string);
    $string = str_replace("RESULTADO E CONCLUSÃO:", "<b>Resultado e conclusão:</b>", $string);
    $string = str_replace("RESULTADOS E DISCUSSÃO:", "<b>Resultados e discussão:</b>", $string);
    $string = str_replace("RESULTADO E DISCUSSÕES:", "<b>Resultado e discussões:</b>", $string);
    $string = str_replace("RESULTADOS/DISCUSSÃO:", "<b>Resultados/Discussão:</b>", $string);
    $string = str_replace("RESULTADOS/DISCUSSÕES:", "<b>Resultados/Discussões:</b>", $string);
    $string = str_replace("RESULTADO/DISCUSSÃO:", "<b>Resultado/Discussão:</b>", $string);
    $string = str_replace("RESULTADO/DISCUSSÕES:", "<b>Resultado/Discussões:</b>", $string);
    $string = str_replace("RECOMENDAÇÕES:", "<b>Recomendações:</b>", $string);
    $string = str_replace("REFLEXÃO SOBRE A EXPERIÊNCIA:", "<b>Reflexão sobre a experiência:</b>", $string);
    $string = str_replace("REFLEXÃO:", "<b>Reflexão:</b>", $string);
    $string = str_replace("REFLEXÕES:", "<b>Reflexões:</b>", $string);
    $string = str_replace("JUSTIFICATIVA:", "<b>Justificativa:</b>", $string);
    
    $string =  str_replace('\"', '"', $string);
    return $string;
}

function minusculo($string) {

    $string = strtolower($string);

    $string = str_replace("Á", "á", $string);
    $string = str_replace("É", "é", $string);
    $string = str_replace("Í", "í", $string);
    $string = str_replace("Ó", "ó", $string);
    $string = str_replace("Ú", "ú", $string);
    $string = str_replace("Â", "â", $string);
    $string = str_replace("Ê", "ê", $string);
    $string = str_replace("Ô", "ô", $string);
    $string = str_replace("Î", "i", $string);
    $string = str_replace("Û", "u", $string);
    $string = str_replace("Ã", "ã", $string);
    $string = str_replace("Õ", "õ", $string);
    $string = str_replace("Ç", "ç", $string);
    $string = str_replace("À", "à", $string);

    return $string;
}

function snakeCaseParaCamelCase($string) {
    $string = strtolower($string); // Primeiro, converte toda a string para minúscula
    $string = ucfirst($string); // Depois, converte apenas a primeira letra para maiúscula
    return $string;
}

function classificarNome($nome) {
    // Convertendo o nome para minúsculas para facilitar a comparação
    $nome = strtolower($nome);
    
    // Listas de terminações comuns para nomes masculinos e femininos
    $terminacoesMasculinas = ['o', 'r', 'l', 'n'];
    $terminacoesFemininas = ['a', 'e', 'ia', 'ina'];
    
    // Verifica se o nome termina com uma das terminações femininas ou masculinas
    foreach ($terminacoesMasculinas as $terminacao) {
        if (substr($nome, -strlen($terminacao)) === $terminacao) {
            return 'Masculino';
        }
    }
    
    foreach ($terminacoesFemininas as $terminacao) {
        if (substr($nome, -strlen($terminacao)) === $terminacao) {
            return 'Feminino';
        }
    }
    
    // Se não encontrar uma terminação conhecida, marca como indefinido
    return 'Indefinido';
}

function maiusculo($string) {

    $string = strtoupper($string);

    $string = str_replace("á", "Á", $string);
    $string = str_replace("é", "É", $string);
    $string = str_replace("í", "Í", $string);
    $string = str_replace("ó", "Ó", $string);
    $string = str_replace("ú", "Ú", $string);
    $string = str_replace("â", "Â", $string);
    $string = str_replace("ê", "Ê", $string);
    $string = str_replace("ô", "Ô", $string);
    $string = str_replace("Î", "I", $string);
    $string = str_replace("Û", "U", $string);
    $string = str_replace("ã", "Ã", $string);
    $string = str_replace("õ", "Õ", $string);
    $string = str_replace("ç", "Ç", $string);
    $string = str_replace("à", "A", $string);

    return $string;
}
function removerAcentos($string) {
    $string = str_replace("Á", "A", $string);
    $string = str_replace("É", "E", $string);
    $string = str_replace("Í", "I", $string);
    $string = str_replace("Ó", "O", $string);
    $string = str_replace("Ú", "U", $string);
    $string = str_replace("Â", "A", $string);
    $string = str_replace("Ê", "E", $string);
    $string = str_replace("Î", "I", $string);
    $string = str_replace("Ô", "O", $string);
    $string = str_replace("Û", "U", $string);
    $string = str_replace("Ã", "A", $string);
    $string = str_replace("Õ", "O", $string);
    $string = str_replace("Ç", "C", $string);
    $string = str_replace("À", "A", $string);
    $string = str_replace("á", "a", $string);
    $string = str_replace("é", "e", $string);
    $string = str_replace("í", "i", $string);
    $string = str_replace("ó", "o", $string);
    $string = str_replace("ú", "u", $string);
    $string = str_replace("â", "a", $string);
    $string = str_replace("ê", "e", $string);
    $string = str_replace("ô", "o", $string);
    $string = str_replace("î", "i", $string);
    $string = str_replace("û", "u", $string);
    $string = str_replace("ã", "a", $string);
    $string = str_replace("õ", "o", $string);
    $string = str_replace("ç", "c", $string);
    $string = str_replace("à", "a", $string);
    return $string;
}
function removerEspacos($string) {
    $string = str_replace(" ", "", $string);
    return $string;
}
function removerPontos($string) {
    $string = str_replace(".", "", $string);
    return $string;
}

function carregarPost(){
    foreach ($_POST as $key => $val) {
        //Com essa opção não estava gravando os acentos
        //Detectando se a variavel esta
        if (mb_detect_encoding($val . 'x', 'UTF-8, ISO-8859-1') == 'UTF-8') {
            $val = utf8_decode(addslashesopctec($val));
        } else {
            $val = addslashesopctec($val);
        }

        $fws_[$key] = $val;
        if ($fws_[$key] == 'undefined') {
            $fws_[$key] = "";
        }

        if ($fwsDebug) {
            echo "post -> fws_['" . $key . "'] => " . $val . " - " . $fws_[$key] . "<br>";
        }
        //Capturando array
        if (is_array($_POST[$key])) {
            //echo count($_POST[$key])."<-<br>";
            $fws_[$key] = $_POST[$key];
            for ($ii = 0; $ii < count($_POST[$key]); $ii++) {
                $valArray = $_POST[$key][$ii];
                if (mb_detect_encoding($valArray . 'x', 'UTF-8, ISO-8859-1') == 'UTF-8') {
                    $valArray = utf8_decode(addslashesopctec($valArray));
                } else {
                    $valArray = addslashesopctec($valArray);
                }
                $fws_[$key][$ii] = $valArray;
                if ($fwsDebug) {
                    echo "Array post -> fws_['" . $key . "']['" . $ii . "'] => " . $valArray . " - " . $fws_[$key][$ii] . "<br>";
                }
            }
        }
    }
    return $fws_;
}
/*Capturando array*/
function capturarArray($field){
    $lista = $_POST[$field];
    if (is_array($lista)) {
        for ($ii = 0; $ii < count($lista); $ii++) {
            $valArray = $lista[$ii];
            if (mb_detect_encoding($valArray . 'x', 'UTF-8, ISO-8859-1') == 'UTF-8') {
                $valArray = utf8_decode(addslashesopctec($valArray));
            } else {
                $valArray = addslashesopctec($valArray);
            }
            $lista[$ii] = $valArray;
            if ($fwsDebug) {
                echo "Array post -> fws_['" . $field . "']['" . $ii . "'] => " . $valArray . " - " . $lista[$ii] . "<br>";
            }
        }
    }
    return $lista;
}
/*Preparando variavel a ser gravada no banco em formato Array*/
function  prepararArrayJson($value){
    $value=array_map('htmlentities',$value);
    $value = json_encode($value);
    return $value;
}

function retorno($acessar = false, $retorno = 'URL') {
    $retorno = strtoupper($retorno);
//        echo "->".$acessar."<-<hr>";
    if ($acessar != 1) {
//            try {
//                throw new Exception('teste');
//            } catch (Exception $exc) {
//                showException($exc);
//                exit();
//            }

        if ($retorno == 'URL') {
            echo " 
            <script>
                window.location = \'".WB_URL_VIEW."&pagina=acessoNegado.php\'';
            </script>
            ".$fnName->msgPermissao . ".<br>";
            exit();
        } else if ($retorno == 'STRING' or $retorno == 'MSG') {
            echo $fnName->msgPermissao;
            exit();
        } else if ($retorno == 'ALERT') {
            echo " 
            <script>
                alert(\'".$fnName->msgPermissao."\');
            </script>";
            exit();
        } else if ($retorno == 'JSON') {
            echo '{"controleAcessoStatus":"0","controleAcessoMsg":"' . $fnName->msgPermissao . '"}';
            exit();
        } else if ($retorno == 'EXCEPTION') {
            throw new Exception($fnName->msgPermissao);
        } else if ($retorno == 'BOOLEAN') {
            return false;
            exit();
        } else {
            echo 'forma de retorno não especificado<br>';
            return false;
            exit();
        }
    } else {
        if ($retorno == 'BOOLEAN') {
            return true;
        }
    }
}

function lpad($str, $qtdade, $valor){
    return str_pad($str,$qtdade,$valor,STR_PAD_LEFT);
}
function rpad($str, $qtdade, $valor){
    return str_pad($str,$qtdade,$valor,STR_PAD_RIGHT);
}
/**
 * Função para gerar senhas aleatórias
 *
 * @author    Thiago Belem <contato@thiagobelem.net>
 *
 * @param integer $tamanho Tamanho da senha a ser gerada
 * @param boolean $maiusculas Se terá letras maiúsculas
 * @param boolean $numeros Se terá números
 * @param boolean $simbolos Se terá símbolos
 *
 * @return string A senha gerada
 */
function geraSenha($tamanho = 6, $maiusculas = true, $numeros = true, $simbolos = false) {
    $lmin = 'abcdefghijkmnpqrstuvwxyz';
    $lmai = 'ACDEFGHIJKLMNPQRSTUVWXYZ';
    $num = '2345679';
    $simb = '!@#$%*-';
    $retorno = '';
    $caracteres = '';
    $caracteres .= $lmin;
    if ($maiusculas)
        $caracteres .= $lmai;
    if ($numeros)
        $caracteres .= $num;
    if ($simbolos)
        $caracteres .= $simb;
    $len = strlen($caracteres);
    for ($n = 1; $n <= $tamanho; $n++) {
        $rand = mt_rand(1, $len);
        $retorno .= $caracteres[$rand - 1];
    }
    return $retorno;
}
function dd($varArray){
    echo "<pre>";
    print_r($varArray);
    echo "</pre>";
}

Function FWF_Feriado($FWS_Data_Entrega, $FWS_Datas_Nao_Entrega = false){
    /**
     * Marcelo Luiz - 11/04/2016 08:55 - UEL-ATI-DDS-DSW
     * $FWS_Data_Entrega: Data de entrega do documento
     * $FWS_Datas_Nao_Entrega: Lista de datas, separadas por vírgula, que não devem ser levadas em conta para entrega do documento.
     * Essas datas podem ser por diversos motivos: feriados, recesso, serviço interno, manutenção no departamento, etc; seja qual
     * for o motivo essas datas não devem ser liberadas para entrega do documento.
     */
    
    if(!$FWS_Datas_Nao_Entrega) {
        #Matriz dos dias em que os formulário não poderão ser entregues.
        $_DATA_ENTREGA_FERIADO[0] = "2012-11-20";
        $_DATA_ENTREGA_FERIADO[1] = "2012-12-10";
    } else {
        $_DATA_ENTREGA_FERIADO = explode(",", $FWS_Datas_Nao_Entrega);
    }

  #echo  "teste-0<br>";
  $FWF_Feriado = false;
  For ($ii=0;$ii<count($_DATA_ENTREGA_FERIADO);$ii++){
    #echo  $ii."<br>";
    If($FWS_Data_Entrega==$_DATA_ENTREGA_FERIADO[$ii]){
      #echo  "teste-1<br>";
      $FWF_Feriado = true;
      return $FWF_Feriado;
      //exit(1);
    }
  }
}

Function UrlGestor($objEv, $debug=false){
    $urlPrefixo = 'opctec';
    if(WB_GRUPO!=''){
        $urlPrefixo = WB_GRUPO;
    }
    return "https://".FWA_Dominio()."/".$urlPrefixo."/admin/inscricoes/index.php";
}

Function UrlView($objEv, $debug=false){
    if (isset($objEv)) {
        $urlPrefixo = $objEv->getUrlPrefixo();
        if($urlPrefixo=='' or $urlPrefixo=='/'){
            $urlPrefixo = $objEv->getGestor().'/'.$objEv->getCodEvento();
        }
        return "https://".FWA_Dominio()."/".$urlPrefixo."/inscricoes/index.php";
    }
}

Function UrlInscBase($objI, $debug=false){
    if (isset($objI)) {
        return "https://".FWA_Dominio()."/".$objI->getUrlPrefixo()."/";
    }
}

Function UrlInscView($objI, $debug=false){
    if (isset($objI)) {
        return "https://".FWA_Dominio()."/".$objI->getUrlPrefixo()."/inscricoes/";
    }
}

Function UrlViewAdmin($organizacao, $debug=false){
    return "https://".FWA_Dominio()."/".$organizacao->getPrefixo()."/admin/inscricoes";
}
function tratarConstantes($constante, $valor) {
    // Usar o operador ternário para definir a constante apenas se ela ainda não foi definida
    defined($constante) || define($constante, $valor);
}

function definir_variaveis_vazias($variaveis) {
    foreach ($variaveis as $nome) {
        $$nome = '';
    }
}

function convertSizeToBytes($sizeString) {
    $valorNumerico = intval($sizeString); // Extrai o valor numérico da string
    $sufixo = strtoupper(substr($sizeString, -2)); // Extrai o sufixo e converte para maiúsculas

    $fator = 1; // Fator de conversão inicial
    if ($sufixo === 'KB') {
        $fator = 1024;
    } elseif ($sufixo === 'MB') {
        $fator = 1024 * 1024;
    } elseif ($sufixo === 'GB') {
        $fator = 1024 * 1024 * 1024;
    }

    $tamanhoBytes = $valorNumerico * $fator; // Calcula o tamanho em bytes
    return $tamanhoBytes;
}

/**
 * array('doc', 'docx', 'pdf', 'csv', 'txt', 'xls')
 */
function createMimeTypesArray($allowedExtensions, $includeTypesString = false) {
    $mimeTypes = array(
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'pdf' => 'application/pdf',
        'csv' => 'text/csv',
        'txt' => 'text/plain',
        'ret' => 'text/plain',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'mp3' => 'audio/mpeg',
        'mp4' => 'video/mp4',
        'ico' => 'image/x-icon'
        // Adicione mais extensões e minetypes conforme necessário
    );

    $resultArray = array();
    $typesString = '';

    foreach ($allowedExtensions as $extension) {
        $extension = strtolower($extension);
        if (array_key_exists($extension, $mimeTypes)) {
            $resultArray[$extension] = $mimeTypes[$extension];
            $typesString .= $extension . ', ';
        }
    }

    $typesString = rtrim($typesString, ', ');

    if ($includeTypesString) {
        return array('mimeTypes' => $resultArray, 'typesString' => $typesString);
    } else {
        return $resultArray;
    }
}

function descSimNao($var){
    if($var==1){
        return "Sim";
    }
    return "Não";
}

function descAtivoInativo($var){
    if($var==1){
        return "Ativo";
    }
    return "Inativo";
}

function converterParaUTF8($string) {
    $string = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $string);
    $string = strtr($string, "áÁéÉíÍóÓúÚâÂêÊôÔãÃõÕçÇ", "aAeEiIoOuUaAeEoOaAoOcC");
    return $string;
}

function verificaIP($listaIPs, $ipProcurado) {
    // Divide a lista de IPs em um array usando o hífen como delimitador
    $ips = explode('-', $listaIPs);

    // Remove espaços em branco de cada IP na lista
    $ips = array_map('trim', $ips);

    // Verifica se o IP procurado está na lista
    // Verifica se o IP procurado está na lista
    if (in_array($ipProcurado, $ips)) {
        return true;
    } else {
        // Se o IP não estiver na lista, verifique se a lista contém apenas um IP (sem hífen)
        if (count($ips) === 1 && $ips[0] === $ipProcurado) {
            return true;
        }
        return false;
    }
}

function retornarIconeExtensao($fileName){
    $extensao = substr($fileName, -4);
    if($extensao=='.doc'){
        echo "<img src='resources/image/doc.gif' border='0'>";
    }
    if($extensao=='docx'){
        echo "<img src='resources/image/doc.gif' border='0'>";
    }
    if($extensao=='.pdf'){
        echo "<img src='resources/image/pdf.gif' border='0'>";
    }
    if($extensao=='.gif' and $extensao!='.jpg' and $extensao!='.png' and $extensao!='jpeg'){
        echo "<img src='resources/image/icone_online.gif' border='0'>";
    }   
}

function confirmaExtensao($fileName){
    $extensao = substr($fileName, -4);
    if($extensao=='.doc'){
        return true;
    }
    if($extensao=='.pdf'){
        return true;
    }
}

/** Detecta o header do arquivo, informar endereço e nome completo do arquivo */
function detectarHeaderArquivo($fullFilePath) {
    $extensao = pathinfo($fullFilePath, PATHINFO_EXTENSION);
    
    // Define o Content-Type baseado na extensão
    switch (strtolower($extensao)) {
        case 'pdf':
            header('Content-Type: application/pdf');
            break;
        case 'doc':
        case 'docx':
            header('Content-Type: application/msword');
            break;
        case 'jpg':
        case 'jpeg':
            header('Content-Type: image/jpeg');
            break;
        case 'png':
            header('Content-Type: image/png');
            break;
        case 'gif':
            header('Content-Type: image/gif');
            break;
        default:
            header('Content-Type: application/octet-stream');
    }
}

function formatMoneyInView($moneyValue) {
    return 'R$ ' . $moneyValue;
}

function formatarTitulo($tipo, $titulo) {
    if (stripos($titulo, $tipo) !== false) {
        return $titulo;
    } else {
        return $tipo . " - " . $titulo;
    }
}

function processGetArray($debug = false) {
    $processedData = [];
    foreach ($_GET as $key => $val) {
        if ($val == "") {
            $val = NULL;
        }
        // Descomente se necessário excluir variáveis que começam com "cod"
        // if (substr($key, 0, 3) == "cod") {
        //     continue;
        // }
        // $val = strtoupper($val); // Descomente se necessário transformar em maiúsculas
        $val = addslashesopctec($val);
        $processedData[strtolower($key)] = $val;
        if ($debug) {
            echo $key . " -> " . $processedData[strtolower($key)] . "<br>";
        }
        // Descomente para salvar na sessão, se necessário
        // $_SESSION[$key] = $val;
    }
    return $processedData;
}
function processPostArray($debug = false) {
    $processedData = [];
    foreach ($_POST as $key => $val) {
        if ($val == "") {
            $val = NULL;
        }
        // Descomente se necessário excluir variáveis que começam com "cod"
        // if (substr($key, 0, 3) == "cod") {
        //     continue;
        // }
        // $val = strtoupper($val); // Converte valores para maiúsculas
        $val = addslashesopctec($val); // Aplica tratamento personalizado
        $processedData[strtolower($key)] = $val;
        if ($debug) {
            echo "POST -> $key => " . $processedData[strtolower($key)] . "<br>";
        }
        // Descomente para salvar na sessão, se necessário
        // $_SESSION[$key] = $val;
    }
    return $processedData;
}

function converterMinutosParaHoras($minutos) {
    $horas = floor($minutos / 60);
    $minutosRestantes = $minutos % 60;
    return "{$horas} horas e {$minutosRestantes} minutos";
}

// Função para obter o mês a partir da data
function obterMesData($data_inicial) {
    $data = new DateTime($data_inicial);
    return $data->format('m');
}
function obterAnoMesData($data_inicial) {
    $data = new DateTime($data_inicial);
    return $data->format('Y-m');
}

/**
reaplicar tablesorter após carregamento via ajax
    success: function(html) {
        $('#div-da-tabela').html(html); // substitui a <table> inteira
        $("#sua-tabela").tablesorter(); // reaplica o tablesorter
    }
*/
function montarTabelaFromJson($dados) {
    if (empty($dados)) return "<p>Nenhum dado encontrado.</p>";

    $html = "<table id='sua-tabela' class='tablesorter'><thead><tr>";
    
    // Cabeçalhos dinâmicos
    foreach (array_keys($dados[0]) as $coluna) {
        $html .= "<th>" . htmlspecialchars($coluna) . "</th>";
    }

    $html .= "</tr></thead><tbody>";

    // Linhas
    foreach ($dados as $linha) {
        $html .= "<tr>";
        foreach ($linha as $valor) {
            $html .= "<td>" . htmlspecialchars($valor) . "</td>";
        }
        $html .= "</tr>";
    }

    $html .= "</tbody></table>";

    return $html;
}

/**
 * Calcula vencimento ('Y-m-d') a partir de $objTaxa, sem strict_types.
 *
 * Regras:
 * - Base: hoje (timezone configurável, default America/Sao_Paulo).
 * - Retorna null apenas quando NÃO houver dias nem data máxima.
 * - Sem dias e com máxima => retorna a máxima.
 * - Com dias => hoje + dias, limitado pela máxima (se existir).
 *
 * $objTaxa precisa expor:
 *  - getDiasVencimento(): mixed (int ou string numérica)
 *  - getMaxDataVencimento(): DateTimeInterface|string|null ('Y-m-d')
 */
/* ----------------- Exemplo de uso no seu trecho -----------------
$maxVencimento  = $objTaxa->getMaxDataVencimento();
$diasVencimento = $objTaxa->getDiasVencimento();
$dataVencimento = calcularDataVencimentoPorTaxa($objTaxa); // 'Y-m-d' ou null

if ($dataVencimento !== null) {
    $objPedido->setDatVencimento($dataVencimento);
}
------------------------------------------------------------------ */
function calcularDataVencimentoPorTaxa($objTaxa, $timezone = 'America/Sao_Paulo')
{
    $tz   = new DateTimeZone($timezone);
    $dias = sanitizeDias($objTaxa->getDiasVencimento());            // aceita "10"
    $max  = normalizeMaxDate($objTaxa->getMaxDataVencimento(), $tz);

    if ($dias === null && $max === null) {
        return null;
    }

    if ($dias === null) {
        // Por quê: sem dias não se usa "hoje" para calcular.
        return $max ? $max->format('Y-m-d') : null;
    }

    if ($dias < 0) {
        throw new InvalidArgumentException('dias_vencimento deve ser >= 0');
    }

    $hoje      = new DateTimeImmutable('today', $tz);
    $calculada = $hoje->modify(sprintf('+%d day', $dias));
    $final     = $max ? minDate($calculada, $max) : $calculada;

    return $final->format('Y-m-d');
}

/**
 * Converte dias para inteiro válido ou null.
 * Por quê: evitar erros e coerção silenciosa de strings não numéricas.
 */
function sanitizeDias($value)
{
    if ($value === null || $value === '') {
        return null;
    }
    if (is_int($value)) {
        return $value;
    }
    if (is_string($value)) {
        $filtered = filter_var($value, FILTER_VALIDATE_INT);
        if ($filtered === false) {
            throw new InvalidArgumentException('dias_vencimento inválido');
        }
        return (int)$filtered;
    }
    if (is_numeric($value)) {
        return (int)$value;
    }
    throw new InvalidArgumentException('dias_vencimento inválido');
}

/**
 * Normaliza data máxima para DateTimeImmutable ou null.
 */
function normalizeMaxDate($value, DateTimeZone $tz)
{
    if ($value instanceof DateTimeInterface) {
        return DateTimeImmutable::createFromInterface($value)->setTimezone($tz);
    }
    if (is_string($value)) {
        $dt = DateTimeImmutable::createFromFormat('Y-m-d', $value, $tz);
        $errors = DateTimeImmutable::getLastErrors();
        if ($dt !== false && empty($errors['warning_count']) && empty($errors['error_count'])) {
            return $dt;
        }
        return null; // string inválida -> ausente
    }
    return null;
}

/**
 * Menor entre duas datas.
 */
function minDate(DateTimeInterface $a, DateTimeInterface $b)
{
    return ($a <= $b) ? $a : $b;
}


require_once(dirname(__FILE__)."/FWA_Mostrar_Mensagem.php");
require_once(dirname(__FILE__)."/FWA_Zero_Esquerda.php");
require_once(dirname(__FILE__)."/FWA_isEventoProRede.php");
require_once(dirname(__FILE__)."/FWA_CursoEadEscopo.php");
require_once(dirname(__FILE__)."/FWA_ehProfessorEAD.php");
require_once(dirname(__FILE__)."/FWA_Dominio.php");
require_once(dirname(__FILE__)."/FWA_URL_Prefixo.php");
require_once(dirname(__FILE__)."/FWA_Conexao.php");
require_once(dirname(__FILE__)."/FWA_UsuarioLogado.php");
require_once(dirname(__FILE__)."/FWA_DireitoSobreSistema.php");
require_once(dirname(__FILE__)."/FWA_Autenticacao.php");
require_once(dirname(__FILE__)."/FWA_ValidarSessaoAdmin.php");
require_once(dirname(__FILE__)."/FWA_JaVotou.php");
require_once(dirname(__FILE__)."/FWA_BarraLogin.php");
require_once(dirname(__FILE__)."/FWA_Option_Dia.php");
require_once(dirname(__FILE__)."/FWA_Option_Mes.php");
require_once(dirname(__FILE__)."/FWA_Option_Ano.php");
require_once(dirname(__FILE__)."/FWA_Option_Hora.php");
require_once(dirname(__FILE__)."/FWA_Option_Minuto.php");
require_once(dirname(__FILE__)."/FWA_Arquivo_Existe.php");
require_once(dirname(__FILE__)."/FWA_Upload.php");
require_once(dirname(__FILE__)."/FWA_Executa_SQL.php");
require_once(dirname(__FILE__)."/FWA_Cheked.php");
require_once(dirname(__FILE__)."/FWA_Manda_Email.php");
require_once(dirname(__FILE__)."/FWA_Select_Inscrito.php");
require_once(dirname(__FILE__)."/FWA_Select_Area_Tematica.php");
require_once(dirname(__FILE__)."/FWA_Select_Oficina.php");
require_once(dirname(__FILE__)."/somar_datas.php");
require_once(dirname(__FILE__)."/validarTipoArquivoUpload.php");

/**
 * Retorna o valor cru (sem addslashes) de $fws_ ou $fwsGet_.
 * Usar quando o valor não vai para SQL (ex: json_decode, comparações lógicas).
 */
function fws_raw($key, $source = 'post') {
    global $fws_, $fwsGet_;
    if ($source === 'get') {
        return isset($fwsGet_[$key]) ? stripslashes($fwsGet_[$key]) : '';
    }
    return isset($fws_[$key]) ? stripslashes($fws_[$key]) : '';
}

/**
 * Retorna o valor seguro para impressão em HTML (protege contra XSS).
 * Usar sempre que for imprimir valor do usuário na tela.
 */
function fws_html($key, $source = 'post') {
    global $fws_, $fwsGet_;
    if ($source === 'get') {
        $val = isset($fwsGet_[$key]) ? $fwsGet_[$key] : '';
    } else {
        $val = isset($fws_[$key]) ? $fws_[$key] : '';
    }
    return htmlspecialchars(stripslashes($val), ENT_QUOTES, 'UTF-8');
}

/**
 * Frase unica de sessao expirada. Existe como constante porque a mesma mensagem
 * sai por tres gates diferentes e e' o que o usuario le na tela -- mudar em um
 * lugar so evita tres frases levemente diferentes para o mesmo problema.
 */
if (!defined('WB_MSG_SESSAO_EXPIRADA')) {
    define('WB_MSG_SESSAO_EXPIRADA', 'Sessão expirada. Faça login novamente.');
}

/**
 * Gate de permissao para controllers (as acoes via CommandAC nao tem gate proprio).
 * Aceita UMA permissao (enumSistema::SIS_XXX) ou um ARRAY delas (semantica OU: basta ter alguma).
 * Lanca Exception se nao autenticado / sem permissao -> o CommandAC devolve JSON/pagina de erro.
 * Uso:
 *   FWA_exigirPermissao(enumSistema::SIS_GERENCIAR_ADM_GERAL);
 *   FWA_exigirPermissao([enumSistema::SIS_GERENCIAR_ADM_GERAL, enumSistema::SIS_CONTROLE_FINANCEIRO]);
 */
function FWA_exigirPermissao($perms) {
    // Contexto de servico: rota declarada em lib/cursos/api/router.php executada com
    // api_token valido (ver Router::executar). Nao ha sessao nem deve haver -- quem
    // autorizou foi o token, na porta de entrada. O fallback generico c=/m= nao define
    // esta constante, entao continua caindo na exigencia de sessao abaixo.
    if (defined('FWA_API_SERVICO') && FWA_API_SERVICO) {
        return;
    }
    $lista = is_array($perms) ? $perms : array($perms);
    $cod_usuario = isset($_SESSION['_cod_usuario']) ? $_SESSION['_cod_usuario'] : null;
    // Sessao ANTES de permissao, como GerenciarNotaFiscalAC::assegurarPermissaoGestor
    // ja fazia: sao problemas diferentes e pedem respostas diferentes. Sessao morta
    // manda para o login; falta de direito so mostra a mensagem (redirecionar viraria
    // laco -- o login devolveria o usuario para a mesma tela proibida).
    if (empty($cod_usuario)) {
        // Veio api_token mas nao bateu (ou WB_API_TOKEN nao esta definido) -- se fosse
        // sessao de admin/inscrito caida de verdade, nao haveria esse campo no POST.
        // Mensagem separada pra nao confundir com sessao expirada de fato no log.
        if (!empty($_POST['api_token'])) {
            throw new Exception('Token de API invalido ou nao autorizado para esta rota.');
        }
        throw new Exception(WB_MSG_SESSAO_EXPIRADA);
    }
    foreach ($lista as $p) {
        if ($p === null || $p === '') {
            continue;   // enum dinamico ainda nao cadastrado em sis_sistemas
        }
        if (@FWA_Autenticacao($cod_usuario, $p, 5, false, false)) {
            return; // tem pelo menos uma das permissoes -> libera
        }
    }
    throw new Exception("Sem permissão de acesso para esta ação.");
}

/**
 * Mesma pergunta do FWA_exigirPermissao, mas em forma de resposta: retorna true/false
 * em vez de lancar Exception. Serve para a TELA decidir se mostra um bloco -- quem nao
 * pode executar nao deve nem ver a opcao e descobrir no erro.
 * O gate de verdade continua sendo o do controller.
 * Uso: if (FWA_temPermissao([enumSistema::cod('SIS_ACOES_EM_LOTE'), enumSistema::SIS_GERENCIAR_ADM_GERAL])) {...}
 */
function FWA_temPermissao($perms) {
    $lista = is_array($perms) ? $perms : array($perms);
    $cod_usuario = isset($_SESSION['_cod_usuario']) ? $_SESSION['_cod_usuario'] : null;
    foreach ($lista as $p) {
        if ($p === null || $p === '') {
            continue;   // enum nao cadastrado no banco ainda
        }
        if (@FWA_Autenticacao($cod_usuario, $p, 5, false, false)) {
            return true;
        }
    }
    return false;
}

/**
 * Gate de permissao GERAL (sem considerar cod_evento), para telas/controllers que
 * vivem FORA de um evento -- ex: modulo Gestor, Transparencia, Usuarios.
 * Mesma semantica de OU do FWA_exigirPermissao, mas usando FWA_AutenticacaoGeral
 * (que consulta o direito com cod_evento='TODOS').
 * Uso: FWA_exigirPermissaoGeral(enumSistema::SIS_GERENCIAR_ADM_GERAL);
 */
function FWA_exigirPermissaoGeral($perms) {
    $lista = is_array($perms) ? $perms : array($perms);
    $cod_usuario = isset($_SESSION['_cod_usuario']) ? $_SESSION['_cod_usuario'] : null;
    // Mesma separacao do FWA_exigirPermissao: sessao morta x falta de direito.
    if (empty($cod_usuario)) {
        throw new Exception(WB_MSG_SESSAO_EXPIRADA);
    }
    foreach ($lista as $p) {
        if (@FWA_AutenticacaoGeral($cod_usuario, $p, 5, false, false)) {
            return; // tem pelo menos uma das permissoes em algum evento -> libera
        }
    }
    throw new Exception("Sem permissão de acesso para esta ação.");
}

/**
 * Gate para acoes de INSCRITO (metodos chamados por inscrito logado, nao admin).
 * Exige um inscrito logado na sessao. Lanca Exception se nao houver.
 * Uso: FWA_exigirInscrito();
 */
function FWA_exigirInscrito() {
    if (empty($_SESSION['_cod_inscrito'])) {
        // Mesma frase dos gates de admin: para quem esta na tela o caso e' o mesmo
        // (a sessao caiu), e e' ela que dispara o aviso + ida ao login.
        throw new Exception(WB_MSG_SESSAO_EXPIRADA);
    }
}

/* ============================================================================
   Cartao de listagem — montagem do HTML das abas do cadastro do inscrito

   O CSS mora em resources/css/cartao-listagem.css (classes .card-*). Estas
   funcoes existem para que as abas nao repitam a mesma montagem de HTML e,
   principalmente, para que a regra "campo vazio continua visivel" valha em
   todas elas sem depender de cada view lembrar disso.

   Prefixo montar* e nao format*: elas devolvem HTML, nao convertem dado para
   formato de exibicao — que e o que format* significa no resto do arquivo
   (formatCpfView, formatMoneyInsert, formatDateView). A unica excecao e
   formatData, no fim do bloco, que trata dado e nao gera markup.
   ========================================================================== */

/** Faixa de titulo de grupo dentro do cartao (Documentos, Endereco, Formacao...). */
function montarSecao($titulo) {
    return "<div class='card-secao'>" . htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') . "</div>";
}

/**
 * Um campo da grade: rotulo em cima, valor embaixo.
 *
 * Campo sem conteudo NAO some — vira "—" em cinza claro. Some seria pratico,
 * mas quem confere um cadastro precisa distinguir "nao tem" de "nao existe
 * esse campo aqui".
 *
 * @param bool $html true quando o valor ja e HTML pronto (link, badge).
 * @param bool $full true ocupa a linha inteira da grade (texto longo).
 */
function montarCampo($rotulo, $valor, $html = false, $full = false) {
    $v = trim((string) $valor);
    // "0" e valor legitimo (parcelas, pontos); so string vazia e vazio.
    $vazio = ($v === '' || $v === '-' || $v === '/' || $v === '00/00/0000');

    // A classe no campo (e nao so no valor) permite clarear tambem o rotulo do
    // campo vazio — sem ela o CSS precisaria de :has(), que nem todo navegador
    // em uso aqui entende.
    $classeCampo = 'card-campo' . ($full ? ' card-campo-full' : '') . ($vazio ? ' campo-vazio' : '');
    $classeVal   = 'card-val'   . ($vazio ? ' vazio' : '');

    $conteudo = $vazio ? '&mdash;' : ($html ? $v : htmlspecialchars($v, ENT_QUOTES, 'UTF-8'));

    return "<div class='" . $classeCampo . "'>"
         . "<span class='card-rot'>" . htmlspecialchars($rotulo, ENT_QUOTES, 'UTF-8') . "</span>"
         . "<span class='" . $classeVal . "'>" . $conteudo . "</span>"
         . "</div>";
}

/**
 * Bloco de texto longo (observacao, resumo, justificativa).
 * Aparece INTEIRO: nesta tela nada e truncado.
 */
function montarTexto($rotulo, $texto, $html = false) {
    $t = trim((string) $texto);
    if ($t === '') {
        return "<div class='card-texto'><span class='card-rot'>"
             . htmlspecialchars($rotulo, ENT_QUOTES, 'UTF-8')
             . "</span><span class='card-val vazio'>&mdash;</span></div>";
    }
    return "<div class='card-texto'>"
         . "<span class='card-rot'>" . htmlspecialchars($rotulo, ENT_QUOTES, 'UTF-8') . "</span>"
         . "<div class='card-texto-box'>" . ($html ? $t : nl2br(htmlspecialchars($t, ENT_QUOTES, 'UTF-8'))) . "</div>"
         . "</div>";
}

/**
 * Linha de metadados "Rotulo <b>valor</b> · Rotulo <b>valor</b>".
 * Par com valor vazio entra como "—" para nao dar a impressao de que o campo
 * deixou de existir.
 *
 * @param array $pares  ['Venc.' => '31/07/2026', 'Parcelas' => 3]
 * @param array $htmlOk rotulos cujo valor ja e HTML (link) e nao deve escapar
 */
function montarMeta($pares, $htmlOk = array()) {
    $itens = array();
    foreach ($pares as $rotulo => $valor) {
        $v = trim((string) $valor);
        if ($v === '') {
            $itens[] = htmlspecialchars($rotulo, ENT_QUOTES, 'UTF-8') . " <b style='color:#c8c8c8;font-weight:400;'>&mdash;</b>";
            continue;
        }
        $v = in_array($rotulo, $htmlOk, true) ? $v : htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
        $itens[] = htmlspecialchars($rotulo, ENT_QUOTES, 'UTF-8') . " <b>" . $v . "</b>";
    }
    if (!$itens) return '';
    return "<div class='card-meta'>" . implode(" &middot; ", $itens) . "</div>";
}

/** Estado vazio da listagem, no lugar de um espaco em branco sem explicacao. */
function montarVazio($mensagem) {
    return "<div class='card-vazio-lista'>" . htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8') . "</div>";
}

/**
 * Data no formato da tela, sem o "00:00" que aparece quando a hora nunca foi
 * registrada e nao informa nada.
 *
 * Nao confundir com formatDateView (que e quem faz a formatacao de fato e e
 * chamada aqui dentro) nem com formatarData/formatarDataBR.
 */
function formatData($data) {
    $d = trim((string) formatDateView($data));
    $d = str_replace(' 00:00', '', $d);
    if ($d === '00/00/0000' || $d === '/' || $d === '-') return '';
    return $d;
}

/**
 * Volta para uma tela do sistema depois de gravar (Post/Redirect/Get).
 *
 * O padrao herdado terminava cada *-grava.php assim:
 *
 *     echo "Aguarde um momento - <data>";      // impresso DEPOIS de gravar
 *     echo "Gravando seus dados no sistema";   // e tambem quando dava erro
 *     echo "<a href='{$URL}'>{$URL}</a>";      // o endereco cru na cara do usuario
 *     echo "<META HTTP-EQUIV=REFRESH ...>";
 *
 * Tres problemas: a frase mente (o trabalho ja terminou), o endereco e ruido —
 * ainda por cima com cod_inscrito/cod_pedido a mostra — e a resposta do POST e
 * uma pagina, nao um redirecionamento; o POST fica vivo no historico e o
 * "Voltar" do navegador pode oferecer reenviar o formulario, o que numa tela de
 * gravacao significa registro repetido.
 *
 * A mensagem viaja em $_SESSION['MSG'] (mesmo contrato de sempre — quem le e
 * FWA_Mostrar_Mensagem; '--' vira quebra de linha). O $tipo e opcional e so
 * define a cor da caixa no destino: sucesso, erro ou aviso.
 *
 * Para o 303 valer, o arquivo que chama precisa comecar com ob_start() — sem
 * isso a quebra de linha de qualquer require ja envia o cabecalho. Quando a
 * saida ja comecou, cai num plano B curto: location.replace (troca a entrada no
 * historico em vez de empilhar) e um link "Continuar" para quem estiver sem
 * JavaScript. Em nenhum dos dois casos o endereco aparece na tela.
 *
 * Uso:
 *     redirecionar($URL, "Operação executada com sucesso", 'sucesso');
 *     redirecionar($URL, "Erro--Motivo não informado", 'erro');
 */
function redirecionar($url, $msg = '', $tipo = '') {

    if ($msg !== '' && $msg !== null) {
        $_SESSION['MSG'] = $msg;
        // Sem tipo informado, deduz pelo texto — as mensagens antigas comecam
        // com "Erro" e nao vao ser todas reescritas de uma vez.
        if ($tipo === '' || $tipo === null) {
            $tipo = (stripos($msg, 'erro') !== false) ? 'erro' : 'sucesso';
        }
        $_SESSION['MSG_TIPO'] = $tipo;
    }

    if (!headers_sent()) {
        header('Location: ' . $url, true, 303);
        exit();
    }

    $urlHtml = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
    $urlJs   = json_encode($url);

    echo "<div style='font-family:Arial,Helvetica,sans-serif;color:#555;padding:20px;font-size:14px;'>"
       . "Processando... <a href='" . $urlHtml . "'>Continuar</a>"
       . "</div>"
       . "<script type='text/javascript'>location.replace(" . $urlJs . ");</script>"
       . "<noscript><meta http-equiv='refresh' content='0; url=" . $urlHtml . "'></noscript>";
    exit();
}

/**
 * Cobertura de "processando" para formulario que grava e troca de tela.
 *
 * Sem a pagina de espera, o clique ficava sem resposta nenhuma ate a tela nova
 * carregar — e quem clica duas vezes numa tela de gravacao grava duas vezes. O
 * aviso passa a sair no lugar certo, no CLIQUE: a tela escurece, aparece o
 * texto, e a cobertura some junto com a pagina antiga.
 *
 * Uso: chamar uma vez na tela e marcar o formulario com o texto desejado.
 *
 *     <?php overlayProcessando(); ?>
 *     <form ... data-processando="Salvando alterações...">
 *
 * Chamar de novo na mesma pagina nao repete o bloco (a aba Atividades e
 * incluida dentro do laco de inscritos).
 *
 * O div#carregando de mensagens.css nao serve aqui: e uma caixinha de 110px
 * para requisicao AJAX dentro do fluxo, nao cobre a tela nem barra o 2o clique.
 */
function overlayProcessando() {

    if (isset($GLOBALS['__overlayProcessandoRenderizado'])) {
        return;
    }
    $GLOBALS['__overlayProcessandoRenderizado'] = true;
    ?>
<style>
    #overlayProcessando{
        display:none; position:fixed; top:0; left:0; right:0; bottom:0;
        background:rgba(255,255,255,.75); z-index:2050;
    }
    #overlayProcessando .op-caixa{
        position:absolute; top:50%; left:50%; transform:translate(-50%,-50%);
        background:#fff; border:1px solid #e0e0e0; border-radius:6px;
        box-shadow:0 2px 10px rgba(0,0,0,.12); padding:18px 26px;
        font-family:Arial,Helvetica,sans-serif; font-size:14px; color:#444;
        display:flex; align-items:center; gap:12px; white-space:nowrap;
    }
    #overlayProcessando .op-spin{
        width:20px; height:20px; border:3px solid #d9e4f0; border-top-color:#4A90E2;
        border-radius:50%; animation:opGira .8s linear infinite; flex:none;
    }
    @keyframes opGira{ to{ transform:rotate(360deg); } }
    /* Quem prefere menos movimento continua vendo a cobertura, sem o giro. */
    @media (prefers-reduced-motion: reduce){
        #overlayProcessando .op-spin{ animation:none; }
    }
</style>

<div id="overlayProcessando" role="status" aria-live="polite">
    <div class="op-caixa">
        <div class="op-spin"></div>
        <span id="overlayProcessandoTexto">Processando...</span>
    </div>
</div>

<script type="text/javascript">
function mostrarProcessando(texto){
    $('#overlayProcessandoTexto').text(texto || 'Processando...');
    $('#overlayProcessando').show();
}
function esconderProcessando(){
    $('#overlayProcessando').hide();
}

$(document).ready(function(){
    $(document).on('submit', 'form[data-processando]', function(){
        mostrarProcessando($(this).data('processando'));
    });
});

// Voltar pelo historico devolve a pagina do cache com a cobertura ainda ligada;
// sem isto a tela volta "travada" atras dela.
window.addEventListener('pageshow', function(e){
    if (e.persisted) { esconderProcessando(); }
});
</script>
    <?php
}

/**
 * Imprime a mensagem que veio de outra tela (a que redirecionar() guardou na
 * sessao) e limpa a sessao em seguida.
 *
 * Contrato de sempre: '--' vira quebra de linha; hifen simples fica como esta
 * (as mensagens antigas sao hifenizadas e nao vao ser todas reescritas de uma
 * vez). O $_SESSION['MSG_TIPO'], quando existe, define a cor da caixa; sem ele,
 * amarelo, exatamente como era antes — nenhuma tela antiga muda de aparencia.
 *
 * Substitui FWA_Mostrar_Mensagem(), que virou casca e sai quando as 157
 * chamadas espalhadas pelo sistema forem migradas.
 */
function mostrarMensagem($MSG = false) {
    $MSG = addslashes($MSG);
    if ($MSG) {
        $MSG = preg_replace("/--/", "<br>", $MSG);

        $classeAlerta = 'alert-warning';
        if (!empty($_SESSION['MSG_TIPO'])) {
            $mapa = array('sucesso' => 'alert-success', 'erro' => 'alert-danger', 'aviso' => 'alert-warning');
            if (isset($mapa[$_SESSION['MSG_TIPO']])) {
                $classeAlerta = $mapa[$_SESSION['MSG_TIPO']];
            }
        }
        echo "<div class=\"alert " . $classeAlerta . "\" role=\"alert\">" . $MSG . "</div>";
    }
    $_SESSION['MSG']      = "";
    $_SESSION['MSG_TIPO'] = "";
}

/**
 * Mesma caixa, na tela de login (classe form-signin). Substitui
 * FWA_Mostrar_Login() — 4 chamadas no sistema.
 */
function mostrarMensagemLogin($MSG = false) {
    $MSG = addslashes($MSG);
    $MSG = preg_replace("/--/", "<br>", $MSG);
    echo "<div class=\"form-signin alert alert-warning\" role=\"alert\">" . $MSG . "</div>";
}

/**
 * Rotulo de atividade com o codigo na frente do titulo.
 *
 * As telas de certificado listam atividade por titulo, e titulo se repete
 * ("Minicurso de Suporte Basico" em duas edicoes) ou vem cortado. Quem trabalha
 * no sistema procura pelo CODIGO — e' ele que aparece na ficha do inscrito, na
 * URL e no nome do arquivo do certificado. Uma funcao so para as quatro telas
 * mostrarem do mesmo jeito.
 *
 * $comHtml=false devolve texto puro, para <option> e atributos.
 */
function rotuloAtividade($codOficina, $titulo, $comHtml = true) {
    $cod = (int) $codOficina;
    $tit = trim((string) $titulo);

    // Escopo geral (codOficina = 0) nao tem codigo para mostrar.
    if ($cod <= 0) {
        return $comHtml ? htmlspecialchars($tit, ENT_QUOTES, 'UTF-8') : $tit;
    }

    if (!$comHtml) {
        return $cod . ' - ' . $tit;
    }
    return '<span style="display:inline-block; min-width:44px; margin-right:6px; padding:1px 6px;'
         . ' border-radius:4px; background:#f0f2f5; color:#5a6673; font-size:12px;'
         . ' font-family:Consolas,Menlo,monospace;" title="Código da atividade">' . $cod . '</span>'
         . htmlspecialchars($tit, ENT_QUOTES, 'UTF-8');
}

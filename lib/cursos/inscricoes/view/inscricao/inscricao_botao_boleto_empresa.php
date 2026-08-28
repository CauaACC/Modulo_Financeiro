<?php
require_once(dirname(__FILE__)."/../../include/config.php");

$fws_['nome_inscrito'] = "Fundação de Desenvolvimento, Assistência Técnica  Extensão Rural de Goiás";
$fws_['cod_inscrito'] = 1175;
$fws_['cpf']         = '00597134000212';
$fws_['endereco']    = 'rodovia GO 536 km 3,7 Fazenda Vagem Bonita';
$fws_['numero']      = '';
$fws_['complemento'] = 'Zona Rural';
$fws_['cidade'] = 'Senador Canedo';
$fws_['estado'] = 'GO';
$fws_['cep']    = '75250-000';
$fws_['data_vencimento'] = "17/05/2017";

//$fws_['cod_boleto'] =1;


$var_sql = "SELECT tp.cod_evento, tp.cod_pagamento, tp.cod_inscrito, tp.valor, tp.forma_pagamento"
        . ", tp.campo_7, tp.data_inclusao, tp.data_alteracao, tp.observacao "
        . "FROM tab_pagamentos tp "
        . "WHERE (tp.cod_inscrito=" . $fws_['cod_inscrito'] . ")and(tp.cod_evento=" . COD_EVENTO . ")";

//echo "</blockquote><fieldset><legend> Pagamento da Inscrição </legend><blockquote>";
$FWS_Pagamento = false;
//If ($RS_P = FWA_Executa_SQL($FWS_Debug, $Conexao_db, $var_sql)) {
//    If (!$RS_P->EOF) {
//        While (!$RS_P->EOF) {
//            #Adicionando nas variaveis, os valores dos campos retirados dos registros
//            $cod_evento    = $RS_P->fields[0];
//            $cod_pagamento = $RS_P->fields[1];
//            $valor         = $RS_P->fields[3];
//            $forma_pagamento = $RS_P->fields[4];
//            $numero_boleto   = $RS_P->fields[5];
//            $data_inclusao_pagamento  = $RS_P->fields[6];
//            $data_alteracao_pagamento = $RS_P->fields[7];
//            $observacao    = $RS_P->fields[8];
//
//            If ($valor != "") {
//                $valor = substr($valor, 0, strlen($valor) - 2) . "," . substr($valor, -2);
//            } Else {
//                $valor = "O Pagamento ainda não consta no sistema.";
//            }
//
//            $data_inclusao_pagamento = substr($data_inclusao_pagamento, 6, 2) . "/" . substr($data_inclusao_pagamento, 4, 2) . "/" . substr($data_inclusao_pagamento, 0, 4) . " - " . substr($data_inclusao_pagamento, 8, 2) . "h" . substr($data_inclusao_pagamento, 10, 2);
//            If ($data_alteracao_pagamento != "") {
//                $data_alteracao_pagamento = substr($data_alteracao_pagamento, 6, 2) . "/" . substr($data_alteracao_pagamento, 4, 2) . "/" . substr($data_alteracao_pagamento, 0, 4) . " - " . substr($data_alteracao_pagamento, 8, 2) . "h" . substr($data_alteracao_pagamento, 10, 2);
//            }
//
//            echo "<br>Recebemos o seu Pagamento:";
//            if($forma_pagamento)
//                echo "<br> <b> {$forma_pagamento} </b>";
//            //echo "<br>Data Inclusão: <b> {$data_inclusao_pagamento}  </b>";
//            echo "<br>Valor Pago:     <b> {$valor}</b>";
//
//            #Movendo para o proximo registro
//            $RS_P->MoveNext();
//            $FWS_Pagamento = true;
//        }
//    }
//} 

If ($FWS_Pagamento == false) {
    
//    #Executa Existe Minicurso
//    $var_sql_consulta = "
//     SELECT ti.nome_inscrito, ti.desconto
//     , (select oo.cod_oficina from tab_lig_inscritos_oficinas as lio, tab_opcoes_oficinas as oo where (ti.cod_inscrito=lio.cod_inscrito)and(lio.cod_oficina=oo.cod_oficina)) as cod_oficina
//     , (select oo.titulo      from tab_lig_inscritos_oficinas as lio, tab_opcoes_oficinas as oo where (ti.cod_inscrito=lio.cod_inscrito)and(lio.cod_oficina=oo.cod_oficina)) as titulo
//     FROM tab_inscritos ti
//     WHERE (ti.cod_evento   = " . COD_EVENTO . ")
//        and(ti.cod_inscrito = " . $fws_['cod_inscrito'] . ")
//    ";
//
//    #echo $var_sql_consulta."<br><br>";
//
//    $RS_1 = $Conexao_db->Execute($var_sql_consulta);
//    If (!$RS_1->EOF) {
//        $desconto    = $RS_1->fields[1];
//        $cod_oficina = $RS_1->fields[2];
//        $titulo      = $RS_1->fields[3];
//    }
    #Fim Executa Existe}


//    $data_inscricao = substr($data_inscricao, 0, -4);
    #echo $data_inscricao;
    #If($data_inscricao<20060601){
    #  $NossoNumero="123588402";
    #  $data_inscricao = "20060630";
    #  $TamNossoNumero = 8;


//    If ($data_inscricao <= 20060701) {
//        $NossoNumero = "123588403";
//        $data_inscricao = "20060712";
//        $TamNossoNumero = 8;
//    } Else {
//        $NossoNumero = "1235884";
//        $TamNossoNumero = 10;
//    }


//
//    $refTran = $cod_inscrito;
//    $FWS_Len = $TamNossoNumero - strlen($refTran);
//    For ($ii = 0; $ii < $FWS_Len; $ii++) {
//        $refTran = "0" . $refTran;
//    }
//    $refTran = $NossoNumero . $refTran;
//
//    $endereco = $endereco . ", nº:" . $numero . ", " . $bairro;
//
//    switch (true) {
//        case ($data_inscricao <= 20060531):
//            $fws_['valor_congresso'] = 15000;
//            $fws_['valor_oficina'] = 6000;
//            $fws_['data_vencimento'] = "13/03/2017";
//            break;
//        case ($data_inscricao <= 20060630):
//            $fws_['valor_congresso'] = 17000;
//            $fws_['valor_oficina'] = 6000;
//            $fws_['data_vencimento'] = "13/03/2017";
//            break;
//        case ($data_inscricao <= 20060712):
//            $fws_['valor_congresso'] = 19000;
//            $fws_['valor_oficina'] = 6000;
//            $fws_['data_vencimento'] = "13/03/2017";
//            break;
//    }
    #echo $FWS_Data_Venciment
//
//    $MSG_SITE = "<b>Número de Inscrição: " . $fws_['cod_inscricao'] . "</b>
//                  <br>
//                  <br>Para submissão de trabalhos será necessário informar ao sistema o número de seu <b>CPF e NÃMERO DE INSCRIÃÃO</b>.
//                  <br><br>
//                 ";
//
//    $fws_['msg_boleto'] = "<b>Número de sua Inscrição no Congresso: &nbsp; " . $fws_['cod_inscrito'] . "</b>
//                  <br>Para submeter TRABALHO informar ao sistema o número de <b>CPF e NuMERO DE INSCRIÃÃO</b>.
//                  <br>
//                 ";

    #echo $desconto."-desconto<br>";
//    If ($desconto == "SIM") {
//        $Porcent = '50';
//        $fws_['valor_congresso'] = ($Porcent / 100) * $fws_['valor_congresso'];
//    }

//    switch (true) {
//        case ($cod_oficina != ""):
//            $fws_['valor_cobrado'] = $fws_['valor_congresso'] + $fws_['valor_oficina'];
//            $fws_['msg_site']  .="Congressista com Opção de Minicurso:<br> <b>" . $titulo . ".</b>";
//            $fws_['msg_boleto'].="Congressista com Opção de Minicurso Titulo: " . substr($titulo, 0, 30) . "...</b>";
//            break;
//        case ($cod_oficina == ""):
//            $fws_['valor_cobrado'] = $fws_['valor_congresso'];
//            $fws_['msg_site']   .="Congressista, sem Opção em Minicurso.";
//            $fws_['msg_boleto'] .= $fws_['msg_site'];
//            break;
//        default:
//            echo "";
//    }

    /*calcular baseado na data de inscrição
     * data de inscrição não esta gravando, verificar
     */
    //$fws_['data_vencimento'] = "17/03/2017";
    //$fws_['data_vencimento'] = date("d/m/Y", time() + ($FWS_Dias_Prazo_Pagamento * 86400));
//    echo $valor_inscricao."-".$valor_cobrado."<br>";
    $num_inscricoes = 3;
    $valor_inscricao = 290;
    $num_atividade = 0;
    $valor_atividade = 60;
    $num_dias_almoco = 3;
    $valor_almoco = 49;
    $valor_cada_inscricao = $valor_inscricao + ($num_atividade*$valor_atividade) + ($num_dias_almoco*$valor_almoco);
    $fws_['valor_cobrado'] = $num_inscricoes*($valor_cada_inscricao);
    $fws_['valor_cobrado'] = $fws_['valor_cobrado'].",00";
    $MSG_AMBOS = "
    <br>Valor total da inscrição: R$ " .$fws_['valor_cobrado'] . "
    <br>Até a data de Vencimento: " . $fws_['data_vencimento'] . "
    <br>
    ";
    $MSG_AMBOS = "
    <br>Inscritos contemplados:
    <br>Isadora Sanchez Maia Carneiro - Cpf: 032840601-54
    <br>Valor inscrição: ".$valor_cada_inscricao." Perfil: Pós Graduação não sócio + 3 refeições
    <br>Isadora Fernanda de Cássia Silva - Cpf: 007026121-03
    <br>Valor inscrição: ".$valor_cada_inscricao." Perfil: Pós Graduação não sócio + 3 refeições
    <br>Isadora Lorenna Lopes de Sousa - Cpf: 969527091-34
    <br>Valor inscrição: ".$valor_cada_inscricao." Perfil: Pós Graduação não sócio + 3 refeições
    <br>Valor total da inscrição: R$ " .$fws_['valor_cobrado'] . "
    ";
    
    $fws_['msg_boleto'] = $fws_['msg_boleto'] . $MSG_AMBOS;
    $fws_['msg_site']   = $fws_['msg_site'] . $MSG_AMBOS;

    #echo $_SESSION['content'];
    If (($_SESSION['content'] == "view/inscricao/inscricao_congresso_ok.php") or ($_SESSION['content'] == "view/inscricao/inscricao_trabalhos_inicio.php")) {
        echo $MSG_AMBOS;
    }
    ?>
    <font color='red'>
    <!--<br>Atenção: O valor do boleto bancário esta atrelado apenas ao fato do inscrito ter optado ou não em participar uma oficina.-->
    <!--    <br>
        <br><a href='mailto:9cbmp@opctec.com.br?subject=[Boleto Bancário] - '>duvidas sobre boleto bancário clique aqui.</a>-->
    <br>
    <?php
    /*Gerar botão boleto aqui*/
    $objBoleto = new GerenciarBoletoAC();
    $fws_['nomeBanco'] = 'CAIXAParaEmpresa';
    $objBoleto->btnBoleto($fws_, $debug);
    
    ?>
    </blockquote></fieldset>
<?php }#Pagamento Efetuado ?>

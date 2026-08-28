<?php
require_once(dirname(__FILE__)."/../../include/config.php");
$debug = false;
require_once 'inscricao_congresso_tit_area_inscrito.php';
?>
<h3>Desativar Inscrição</h3>  
<div class="panel-group" >
    <?php

    try {
        $objInscrito = new Inscrito();
        $objInscrito->setQrCode($fwsGet_['transacao_id']);
        $objInscrito->getInscritoByQrCode();
        if($objInscrito->getStatus()!='ATIVADA'){
            throw new Exception('Participante não encontrado');
        }    
        $nome_apelido = textoMaiusculoMinusculo($objInscrito->getPreNome());

        $objIsento = new Isento();
        $objIsento->setCpf($objInscrito->getCpf());
        $objIsento->getIsentoByCpf();

        $objInscrito->carregarOjbPerfil($debug);
        $objPerfil = $objInscrito->getObjPerfil();    
        ?>
        <div class="panel panel-default">
            <div class="panel-heading">
                <FORM NAME='exit' METHOD='POST' action="<?php echo WB_URL_COMMAND?>">
                    <input type="hidden" name="c" value="GerenciarLogin">
                    <h4>Ola <?php echo $nome_apelido?>.
                        &nbsp;&nbsp;&nbsp;
                        <INPUT TYPE='hidden' VALUE='Voltar' NAME='acao'>
                        <input type="hidden" name="m" value="efetuarLogout">
                        <button class="btn btn-xs btn-primary" type="submit">Sair</button>
                    </h4>
                </FORM>
            </div>
            <div class="panel-body">
            <?php
                echo "<br>Número de Inscrição: {$objInscrito->getCodInscrito()}<br><br>";
            ?>
            </div>
        </div>
        <style>
            .ul-blue a {
                color:#1BA9C1
            }
            .ul-green a {
                color:#8EC03D
            }
            .ul-purple a{
                color: #8a2be2
            }
            .panel-default li {
                margin-bottom: 12px;
            }
        </style>
    
        <?php
        if (!validarCPF($objInscrito->getCpf())) {
            //echo "CPF válido.";
            echo "CPF inválido.";
            echo "Solicite a correção via whats pelo link no rodapé do site.";
        }else{
            try {
                $objInscrito->carregarListaOficinas($debug);
                $valor_atividade = 0;
                foreach ($objInscrito->getListaOficinas() as $objL) {
                    if(($objL->getStatus()=='ATIVADA')){
                        if($objL->getTipo()=='Agenda' and (in_array(COD_EVENTO, array(35,52,72,105,119)))){
                            $fws_['data_inicio_agenda'] = $objL->getDataInicio();
                            $fws_['desc_atividade'] = formatDateHoraTrans($objL->getDataInicio(), $formatIn='YYYYMM-DD', $formatOut='DD/MM/YYYY')." - ".$objL->getTitulo()."<br>";
                            $fws_['desc_atividade'] = "Agendado para: ".$fws_['desc_atividade'];
                        } 
                    }
                }
                           
            } catch (Exception $exc) {
                #Não possui atividade
            }

        ?>
        <div class="panel panel-default" style="display:true">
            <div class="panel-heading"><b>Pedidos</b></div>
            <div class="panel-body">
                <div class='row' style='margin-bottom:5px;'>
                    <div class='col-sm-12' >
                        Confira a lista de itens abaixo que ainda aguardam pagamento. 
                        <br>Caso algum pagamento já foi realizado, ignore o respectivo item.
                        <?php
                        echo "<br>Quantidade de pagamentos recebidos: ".$objInscrito->getTotalPagamentos();
                        //echo "<br>Total de valores recebidos: ".formatMoneyView($objInscrito->getValorPagamentos());
                        echo $fws_['desc_atividade']? "<br><br><b>".$fws_['desc_atividade']."</b>" : "";
                        try {
                        $objPedido = new Pedido();
                        $objPedido->setInscritoId($objInscrito->getCodInscrito());
                        $objPedido->setEventoId($objInscrito->getCodEvento());
                        $objPedido->carregarListaPedidosByInscritoId($debug);
                            if ($obj != ''){
                        $data1 = new DateTime($objInscrito->getDataEstadiaIni());
                        $data2 = new DateTime($objInscrito->getDataEstadiaFim());
                            }
                            if ($intervalo != '') {
                        $intervalo = $data1->diff( $data2 );
                            }
                       //echo "Intervalo é de {$intervalo->y} anos, {$intervalo->m} meses e {$intervalo->d} dias";
                        $pagos = $objInscrito->getTotalPagamentos();
                        foreach ($objPedido->getListaPedidos() as $objLI) {
                            if($objLI->getStatus()!='DESATIVADA'){
                                echo "<span style='color:black;font-size:15px;'>";
                                echo "<br><br><b>Descrição do Pedido ".$objLI->getInscritoId()." - ". numberPad($objLI->getId(),8).":</b>";
                                //echo "Pedido Extra:";
                                echo "<br><span style='font-size:15px;font-weight: bold;'>".$objLI->getDescricao()."</span>";
                                echo "<br>Valor: R$ ". formatMoneyView($objLI->getValorTotal());
                                echo isDateStringValid($objLI->getDatVencimento())? " Vencimento: ".formatDateView($objLI->getDatVencimento()) : "";
                                echo "<span style='color:black;font-size:15px;'>";
                                /**
                                 * @todo Zaqueu 2023-09-08 
                                 * Esse tratamento com $pagos esta sendo usado verificar os pagamentos que não possuem id do pedido identificado
                                 * Essa forma não é definitiva, né necerrário melhorala ou removela ao longo do tempo
                                 * na medida em que metodos os pagamento de eventos ativos já possuam id do pagamento em todos os pagamentos registrados
                                 * inclusive os cadastrados manualmente
                                 */
//                                if($objLI->getCodPagamento()!='' and $objLI->getStatusPagamento()=='ATIVADA'){
                                // if($pagos > 0){
                                //     // echo "<br>Pagamento registrado<br>";
                                // }else
                                // if(($objLI->getStatus()=='aguardando pagamento')or($objLI->getCodPagamento()=='')){
                                if($objLI->getCodPagamento()!=''){
                                    echo "<br>Pagamento registrado<br>";
                                }else{
                                    $ogDescricao = $objLI->getDescricao();
//                                    $ogDescricao .= $fws_['desc_atividade']!=''? " - ".$fws_['desc_atividade'] : "" ;
                                    
                                    $ogTitulo = $objEv->getTitulo();
                                    $ogTitulo .= $fws_['desc_atividade']!=''? " ".$fws_['desc_atividade'] : "" ;
                                ?>
                                    <head>
                                    <meta property="og:title"       content="<?php echo $ogTitulo ?>">
                                    <meta property="og:description" content="<?php echo $ogDescricao ?>">
                                    <!--<meta property="og:image" content="URL_DA_IMAGEM_DE_CAPA">-->
                                    <!--<meta property="og:url" content="URL_DA_SUA_PÁGINA">-->
                                    </head>
                                    <?php if ($objEvConf->getGatewayPagamento()=='ITAU-SHOPLINE'){ ?>
                                        <?php
//                                        if((isValidDate($objLI->getDatVencimento())) or ($objLI->getDatVencimento() == '0000-00-00') or ($objLI->getDatVencimento() == '')){
//                                            //$objLI->setDatVencimento(Date('Y-m-d'));
//                                            $dataVencimento = somarDiasNaData(Date('Y-m-d'),3);
//                                            if(in_array(COD_EVENTO, array(35,52,72))){
//                                                $dataVencimento = subtrairDiasDaData($fws_['data_inicio_agenda'],10);
//                                            }
//                                            $objLI->setDatVencimento($dataVencimento);
//                                        }
                                        $objLI->testarDatVencimentoItauShopline($fws_);
                                        ?>
                                        <?php include('itaushoplineForm.php') ?>
                                        <?php }else if ($objEv->getCodEvento()==49 or $objEv->getCodEvento()==25){ ?>
<!-- INICIO DO BOTAO PAGSEGURO -->
<br><a href="https://pag.ae/7-mi47hcN/button" target="_blank" title="Pagar com PagSeguro"><img src="//assets.pagseguro.com.br/ps-integration-assets/botoes/pagamentos/205x30-pagar.gif" alt="Pague com PagSeguro - é rápido, grátis e seguro!" /></a>
<!-- FIM DO BOTAO PAGSEGURO -->
                                        <?php }else{ ?>
                                        <?php if ($_SERVER['HTTP_HOST']=='eventos.test') { ?>
                                            <form method="POST" action="http://appboleto.test/api/login/evento" target="_blank">
                                        <?php } else { ?>
                                            <form method="POST" action="https://pg.opctec.net/api/login/evento" target="_blank">
                                        <?php } ?>
                                            Selecione uma forma de pagamento:
                                            <?php if ($objEvConf->getGatewayPagamento()!=''){ ?>
                                                <?php if (verifItemArray('pix', json_decode($objEvConf->getOpcaoPagamento()))) { ?>
                                                    <BR><input type="radio" name="tipo_pagamento" value="pix"    required="require"/> PIX
                                                <?php } ?>
                                                <?php if (verifItemArray('boleto', json_decode($objEvConf->getOpcaoPagamento()))) { ?>
                                                    <BR><input type="radio" name="tipo_pagamento" value="Boleto" required="require"/> Boleto
                                                <?php } ?>
                                                <?php if (verifItemArray('cartaoCredito', json_decode($objEvConf->getOpcaoPagamento()))) { ?>
                                                <BR><input type="radio" name="tipo_pagamento" value="cartao" required="require"/> Cartão
                                                <?php } ?>
                                            <?php }else if ($objPedido->getEventoId()==23 or $objPedido->getEventoId()==24 or $objPedido->getEventoId()==25 or $objPedido->getEventoId()==21) {?>
                                                <BR><input type="radio" name="tipo_pagamento" value="cartao" required="require"/> Cartão
                                            <?php }else if ($objPedido->getEventoId()==34){?>
                                                <BR>
                                                <input style="margin-left:10px;margin-right: 10px" type="radio" name="tipo_pagamento" value="pix"    required="require"/> PIX
                                                &nbsp|&nbsp
                                                <input style="margin-left:10px;margin-right: 10px" type="radio" name="tipo_pagamento" value="cartao" required="require"/> Cartão
                                            <?php }else{?>
                                                <BR>
                                                <input style="margin-left:10px;margin-right: 10px" type="radio" name="tipo_pagamento" value="pix"    required="require"/> PIX
                                                &nbsp|&nbsp
                                                <input style="margin-left:10px;margin-right: 10px" type="radio" name="tipo_pagamento" value="cartao" required="require"/> Cartão
                                                &nbsp|&nbsp
                                                <input style="margin-left:10px;margin-right: 10px" type="radio" name="tipo_pagamento" value="Boleto" required="require"/> Boleto
                                            <?php } ?>
                                            <input type="hidden" name="nome"  value="<?php echo $objInscrito->getNomeInscrito() ?>">
                                            <input type="hidden" name="email" value="<?php echo $objInscrito->getEMail()?>">
                                            <input type="hidden" name="cod_inscrito" value="<?php echo $objInscrito->getCodInscrito()?>">
                                            <input type="hidden" name="pedido_id" value="<?php echo $objLI->getId()?>">
                                            <input type="hidden" name="evento_id" value="<?php echo $objInscrito->getCodEvento()?>">
                                            <input type="hidden" name="rota_retorno" value="<?php echo 'hutec'?>">
                                            <h4><button class="btn btn-primary" type="submit" >Clique aqui para efetuar pagamento</button></h4>
                                        </form>
                                    <?php
                                    }
                                }
                                $pagos = $pagos - 1;
                                echo "</span>";
                                //echo "<br>".$objLI->getDescricao()." - R$ ".$objLI->getValorTotal()."<br>";
                                if($fws_['op_selecionar_ingresso']){
                                    $objIngresso = new Ingresso();
                                    $objIngresso->setEventoId($objPedido->getCodEvento());
                                    $objIngresso->setPedidoId($objPedido->getId());
                                    $objIngresso->carregarListaIngressosByPedidoId();
                                    echo "<br>Pessoas cadastradas:<br>";
                                    echo "<table with=150px><tr><td style='width: 155px;'>Nome</td><td>Rg</td></tr>";
                                    foreach ($objIngresso->getListaIngressos() as $objLI) {
                                        echo "<tr style='font-size:12px;'><td>".$objLI->getNome().'</td><td>'.$objLI->getRg().'</td></tr>';
                                    }
                                    echo "</table>";
                                    echo ""
                                    . "<br>Quantidade de Pessoas: ". count($objIngresso->getListaIngressos())
                                    . "<br>Quantidade de Dias: ", $intervalo->d
                                    . "<br>Valor total do pedido: R$ ". formatMoneyView($objPedido->getValorTotal())
                                    . "</span>";
                                }
                            }
                        }
                    } catch (Exception $exc) {
                        if($objEv->getCodEvento()>=9){
                            echo "<br>".$exc->getMessage()."<br>";
                        }
                    }
                    ?>
                    </div>
                </div>
            </div>
        </div>
    <?php
        }
} catch (Exception $exc) {
    $_SESSION['MSG']=$exc->getMessage();
    $url = WB_URL_VIEW."?content=view/inscricao/inscricao_01.php";
    echo "<a href='" . $url . "'>" . FWA_Mostrar_Mensagem($exc->getMessage()) . "</a>";
    if (!$debug) {
//        echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $url . "\"></HTML>";
    }
}
?>
    <div class="panel panel-default">
        <div class="panel-body">
            <br>
            <br>
            <br>
            <br>
            <br>
            <br>
            <br>
        </div>
    </div>
</div>

<script>
    var links = document.getElementsByClassName('action-js');

    var myFunction = function() {
        var attribute = this.getAttribute("data-myattribute");
        alert("Aguardando horário para liberação");
        return false;
    };

    for (var i = 0; i < links.length; i++) {
        links[i].addEventListener('click', myFunction, false);
    }
</script>

<?php
require_once(dirname(__FILE__)."/../../include/config.php");
$FWA_Option_Dia    = FWA_Option_Dia();
$FWA_Option_Mes    = FWA_Option_Mes();
$FWA_Option_Ano    = FWA_Option_Ano(3);
$FWA_Option_Hora   = FWA_Option_Hora();
$FWA_Option_Minuto = FWA_Option_Minuto();


#$STATUS="DESATIVADO";
#echo $status."<br>";

If(isset($status)){
  $OPTION_Status = "<option selected value='".$status."'>".$status."</option>";
}Else{
  $OPTION_Status = "<option selected value='ATIVADA'>ATIVADA</option>";
}

If(isset($forma_pagamento)){
  $OPTION_Forma_Pagamento= "<option selected value='".$forma_pagamento."'>".$forma_pagamento."</option>";
}Else{
  $OPTION_Forma_Pagamento = "<option selected value='Outras'>Outras</option>";
}

$objInscrito = new Inscrito();
$objInscrito->setCodInscrito($cod_inscrito);
$objInscrito->getInscritoById();

echo "<TABLE WIDTH='100%' BORDER='0' CELLSPACING='0' CELLPADDING='4'>
        <TR>
          <TD VALIGN='top'>
            Inscrito: {$cod_inscrito} - {$nome_inscrito}
            <input type='hidden' name='cod_inscrito' value='{$cod_inscrito}'>
            <br>&nbsp;
          </TD>
        </TR>";

        echo "<TR><TD VALIGN='top' colspan='2'>";
        try {
            $objInscrito->carregarListaPedidosIdInscrito($debug);
            echo "Lista de pedidos:<br>";
            foreach ($objInscrito->getListaPedidos() as $objPedido) {
                if ($objPedido->getStatusPagamento()=='ATIVADA'){
                    echo "<font color=green>";
                }else{
                    echo "<font color=red>";
                    
                }
                if($objPedido->getId()==$fwsGet_['cod_pedido']){
                  $valor      = $objPedido->getValorTotal();  
                  $pedido_id  = $objPedido->getId();  
                }
                echo "Cód.Pedido: ".$objPedido->getId()
                        ." - R$ ".$objPedido->getValorTotal()
                        ." - ".$objPedido->getDescricao();
                echo $objPedido->getDataPagamento()!=''? " (".$objPedido->getFormaPagamento().") (PG:".formatDateView($objPedido->getDataPagamento()).")" : '';
                echo "<br>";
                echo "</font>";
            }   
        } catch (Exception $exc) {
            echo $exc->getMessage();
        }
        echo "<br>&nbsp;";
        echo "</TD></TR>";
        echo "
        <TR>
          <TD VALIGN='top' colspan='2'>
             Código do Pedido Associado a este Pagamento:
            <INPUT CLASS='form_campos' TYPE='text' NAME='pedido_id' value='{$pedido_id}' SIZE='15' maxlength='50' onBlur='trim(this);' onkeypress='javascript: return isNum(window.event.keyCode);'>
          </TD>
        </TR>
        <TR><TD>Status:
              <select name='status'>
                {$OPTION_Status}
                <option value='ATIVADA '>ATIVADA </option>
                <option value='DESATIVA'>DESATIVA</option>
                <option value='EXCLUÍDA'>EXCLUÍDA</option>
                <option value='CANCELADO'>CANCELADO</option>
                <option value='DEVOLVIDO'>DEVOLVIDO</option>
                <option value='ESTORNADO'>ESTORNADO</option>
              </select>
            </TD>
        </TR>
        <TR>
          <TD VALIGN='top' colspan='2'>
              Valor do Pagamento: 
            R$ <INPUT CLASS='form_campos' TYPE='text' NAME='valor' value='{$valor}' SIZE='15' maxlength='255' onBlur='trim(this);' onkeypress='javascript: return isNum(window.event.keyCode);' require='require'> ex: (150,00)
          </TD>
        </TR>

        <TR><TD COLSPAN='2'>Forma de Pagamento:
              <select name='forma_pagamento'>
                {$OPTION_Forma_Pagamento}
                <option value='EMPENHO'>Empenho</option>
                <option value='INEXIGIBILIDADE'>Inexigibilidade</option>
                <option value='BOLETO'>Boleto</option>
                <option value='BOLETO BANCÁRIO'>Boleto Bancário</option>
                <option value='BOLETO BANCÁRIO UNIFICADO'>Boleto Bancário Unificado</option>
                <option value='BOLETO A COMPENSAR'>Boleto Bancário a Compensar (Liberar recibo antecipado)</option>
                <option value='DEPOSITO CONTA CORRENTE'>Deposito Conta Corrente</option>
                <option value='DOACAO'>Doação</option>
                <option value='APOIO'>APOIO</option>
                <option value='TESOURARIA'>Tesouraria-Dinheiro</option>
                <option value='CARTAO'>Tesouraria-Cartão</option>
                <option value='CONVÊNIO'>Convênio</option>
                <option value='TRANSFERENCIA'>Transferência de Recurso</option>
                <option value='PIX'>PIX</option>
                <option value='CARTAO'>Cartão Online</option>
              </select>
            </TD>
        </TR>
        <TR>
          <TD VALIGN='top'>
           <br>Data Pagamento: <INPUT CLASS='form_campos' TYPE='date' NAME='data_pagamento' value='{$data_pagamento}' SIZE='15' onBlur='trim(this);' onkeypress='javascript: return isNum(window.event.keyCode);'> 
          </TD>
        </TR>
        <TR>
          <TD VALIGN='top'>
           Transação Id: <INPUT CLASS='form_campos' TYPE='text' NAME='transacao_id' value='{$transacao_id}' SIZE='60' maxlength='150' onBlur='trim(this);'>
          </TD>
        </TR>
        <TR>
          <TD VALIGN='top'>
           Número Empenho: <INPUT CLASS='form_campos' TYPE='text' NAME='numero_empenho' value='{$numero_empenho}' SIZE='15' maxlength='50' onBlur='trim(this);' onkeypress='javascript: return isNum(window.event.keyCode);'> ex: (99999/2018)
          </TD>
        </TR>
        <TR>
          <TD VALIGN='top'>
              <br>Anotações Diversas: <FONT FACE='verdana' SIZE=1 COLOR='#555555'>(Máximo 255 caracteres, com espaços)
              <br>
              <font id=Digitado color=red>0</font> Caracteres digitados &nbsp; ==> &nbsp; restam <font id=Restante color=red>255</font>
          </TD>
        </TR>
        <TR>
          <TD VALIGN='top'>
              <textarea  onkeyup='MaxCaracter(this,255)' onkeypress='MaxCaracter(this,255)'  rows='5' cols='80' maxlength='255' name='observacao' class='textob'>{$observacao}</textarea>
          </TD>
        </TR>
        <TR>
          <TD VALIGN='top'>
           <br>Data Emissão para o Recibo: <INPUT CLASS='form_campos' TYPE='date' NAME='data_emissao_recibo' value='{$data_emissao_recibo}' SIZE='15' onBlur='trim(this);' onkeypress='javascript: return isNum(window.event.keyCode);'> 
          </TD>
        </TR>
        <TR>
          <TD VALIGN='top'>
              Texto complementar para recibo de Apoio/Empenho:
              <br> <FONT FACE='verdana' SIZE=1 COLOR='#555555'>
              Veja abaixo como ficará o texto no recibo de apoio:<br>
              (Recebemos de xxxxxx, cpf/cnpj:xxxxxx, <b>texto complementar entra aqui</b>, a importância de R$(999,99), ...)
              <br><font id=DigitadoRecibo color=red>0</font> Caracteres digitados &nbsp; ==> &nbsp; restam <font id=RestanteRecibo color=red>255</font>
          </TD>
        </TR>";
           ?>
        <TR>
          <TD VALIGN='top'>
              <textarea  onkeyup="ContarMaximoCaracter(this,255,$('#DigitadoRecibo'),$('#RestanteRecibo'))" onkeypress="ContarMaximoCaracter(this,255,$('#DigitadoRecibo'),$('#RestanteRecibo'))"  rows='5' cols='80' maxlength='255' name='observacao_recibo' class='textob'><?php echo $observacao_recibo?></textarea>
          </TD>
        </TR>
        <TR>
          <TD COLSPAN='2'>&nbsp;</TD>
        </TR>
       </TABLE>
       <br>&nbsp;

<script>
    function ContarMaximoCaracter(txarea, limite, vardigitado, varrestante) {

        /****************************************
         * Para contar caracteres e bloquear a digitação ao exceder o limite de 50 caracteres;
         * Siga o exemplo abaixo:
         *
         * <font id=Digitado color=red>0</font> Caracteres digitados &nbsp; / &nbsp; restam <font id=Restante color=red>50</font>
         * <textarea onkeyup="MaxCaracter(this,50)" onkeypress="MaxCaracter(this,50)" rows="2" cols="35" name="Area"></textarea><br>
         *****************************************/

        total = limite;
        tam = txarea.value.length;
        str = "";
        str = str + tam;
        vardigitado.html(str);
        varresto = total - str;
        varrestante.html(varresto);

        if (tam > total) {

            alert(varrestante);

            aux = txarea.value;

            txarea.value = aux.substring(0, total);
            vardigitado.html(total)
            varrestante.html(0)
            alert("Seu resumo atingiu o limite de " + limite + " caracteres");
        }
    }
</script>
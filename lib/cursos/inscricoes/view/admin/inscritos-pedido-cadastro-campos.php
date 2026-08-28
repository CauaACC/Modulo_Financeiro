
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

If(isset($tipo)){
  $OPTION_Tipo= "<option selected value='".$tipo."'>".$tipo."</option>";
}Else{
  $OPTION_Tipo = "<option selected value='extra'>Extra</option>";
}
If(!isset($dat_vencimento)){
  $dat_vencimento = Date('d/m/Y');
}
If(isset($max_qtd_parcela)){
  $OPTION_MaxQtdParcelas = "<option selected value='".$max_qtd_parcela."'>".$max_qtd_parcela."</option>";
}Else{
  $OPTION_MaxQtdParcelas = "<option selected value='1'>1</option>";
}
echo " 
            
      <TABLE WIDTH='100%' BORDER='0' CELLSPACING='0' CELLPADDING='4'>
        <TR>
          <TD VALIGN='top'>";

            echo "Inscrito: {$cod_inscrito} - {$nome_inscrito}";
            echo "<input type='hidden' name='cod_inscrito' value='{$cod_inscrito}' onBlur='trim(this);' >";  
        ?>
        <?php
          echo "<TR><TD VALIGN='top'>";
          if($objEvConf->getRecebePagamentoMensalidade()){
              echo "<br><b>Sugestão de descrição para mensalidade:</b>
              <br>Descrição: 2° Parcela Mensalidade - {$objEv->getProjeto()} - {$objEv->getCodEvento()}-{$objEv->getAbrevEvento()} - {$objI->getTituloPerfil()}";
                $descricaoSugestao = "Xº Mensalidade: {$objEv->getProjeto()} - {$objEv->getCodEvento()}-{$objEv->getAbrevEvento()} - {$objI->getTituloPerfil()}";
          }else{
              echo "<br><b>Sugestão de descrição de valores para inscrição:</b>
              <br>Descrição: {$cod_inscrito} - {$nome_inscrito} - {$objEv->getProjeto()} - {$objEv->getCodEvento()}-{$objEv->getAbrevEvento()} - {$objI->getTituloPerfil()}";
                $descricaoSugestao = "Inscrição: {$objEv->getProjeto()} - {$objEv->getCodEvento()}-{$objEv->getAbrevEvento()} - {$objI->getTituloPerfil()}";
          }
          echo "<br>Valor: {$valorTaxa}";
          // echo "De acordo com o perfil do inscrito associado abaixo: <br>";
          // echo "<br>".$objPerfil->getTitulo();
          // echo "<br>".$objPerfil->getInvestimento();
          echo ' <button type="button" onclick="preencherCampos()">Preencher Automático</button>';
          echo "</TD></TR>"; 
        ?>
        <script>
        function preencherCampos() {
          document.getElementById("tipo").value         = "<?php echo $tigo ?>";
          document.getElementById("descricao").value    = "<?php echo $descricaoSugestao ?>";
          document.getElementById("descricao_completa").value = "<?php echo $descricaoSugestao ?>";
          document.getElementById("valor_total").value  = "<?php echo $valorTaxa ?>";
        }
        </script>
        <?php
        echo "      
        <TR><TD><br><b>Cadastrar de Pedido:</b><br></TD></TR>
        <TR><TD>Status:
              <select name='status' require='require' id='status'>
                {$OPTION_Status}
                <option value='ATIVADA '>ATIVADA </option>
                <option value='DESATIVADA'>DESATIVADA</option>
                <option value='aguardando pagamento'>Aguardando Pagamento</option>
                <option value='PAGO'>PAGO</option>
                <option value='Pago via acerto'>Pago via acerto</option>
              </select>
            </TD>
        </TR>
        <TR>
          <TD VALIGN='top' colspan='2'>
              Valor do Pedido: 
            R$ <INPUT CLASS='form_campos' TYPE='text' NAME='valor_total' id='valor_total' value='{$valor_total}' 
                SIZE='15' maxlength='255' onBlur='trim(this);' 
                onkeypress='javascript: return isNum(window.event.keyCode);' require='require'> ex: (150.00)
          </TD>
        </TR>

        <TR><TD COLSPAN='2'>Tipo do Pedido:
              <select name='tipo' id='tipo' require='require'>
                {$OPTION_Tipo}
                <option value=''>Inscrição</option>
                <option value='mensalidade'>mensalidade</option>
                <option value='extra'>Extra</option>
              </select>
            </TD>
        </TR>";
        if($objEvConf->getRecebePagamentoMensalidade()){
        echo "
        <TR><TD COLSPAN='2'>
                Mês de referência/texto:
                    <div class='tooltip'> ? <span class='tooltiptext'>Ou texto para agrupar ex: Agosto ou Agosto-2024 ou Refeição</span></div>

                    <input type='text' id='referencia' value='{$referencia}' name='referencia' size='60' maxlength='50'><br>
            </TD>
        </TR>";
        }
        echo "
        <TR><TD COLSPAN='2'>Máx. Qtd. Parcelas:
              <select name='max_qtd_parcela' id='max_qtd_parcela'>
                {$OPTION_MaxQtdParcelas}
                <option value='1'>1</option>
                <option value='2'>2</option>
                <option value='3'>3</option>
                <option value='4'>4</option>
                <option value='5'>5</option>
                <option value='6'>6</option>
                <option value='7'>7</option>
                <option value='8'>8</option>
                <option value='9'>9</option>
                <option value='10'>10</option>
                <option value='11'>11</option>
                <option value='12'>12</option>
                <option value='13'>13</option>
                <option value='14'>14</option>
                <option value='15'>15</option>
                <option value='16'>16</option>
                <option value='17'>17</option>
                <option value='18'>18</option>
                <option value='19'>19</option>
                <option value='20'>20</option>
                <option value='21'>11</option>
              </select>
            </TD>
        </TR>
        <TR><TD COLSPAN='2'>Data de Vencimento:
              <INPUT CLASS='form_campos' TYPE='date' NAME='dat_vencimento' id='dat_vencimento' value='{$dat_vencimento}' 
                SIZE='15' maxlength='255' onBlur='trim(this);' 
                require='require'> ex: ({$dat_vencimento}) 
                <br>obs: Quando não defindo, vence em 3 dias automaticamente.
            </TD>
        </TR>";
          ?>  
        <TR>
          <TD VALIGN='top'>
              <br>Descrição para Sistema Pagamento: 
              <br>
              <FONT FACE='verdana' SIZE=1 COLOR='#555555'>(Máximo 98 caracteres, com espaços)
              <br>
              <font id="Digitado" color="red">0</font> Caracteres digitados &nbsp; / &nbsp; restam <font id="Restante" color="red">98</font>      
              <br>
              <textarea onkeyup="contarMaximoCaracter(this,98,'Digitado','Restante')" onkeypress="contarMaximoCaracter(this,98,'Digitado','Restante')" rows='5' cols='80' maxlength='98' name='descricao' id='descricao' class='textob'><?php echo $descricao ?></textarea>
          </TD>
        </TR>        
        <TR>
          <TD VALIGN='top'>
              <br>Descrição do Completa:
              <br>
              <FONT FACE='verdana' SIZE=1 COLOR='#555555'>(Máximo 1000 caracteres, com espaços)
              <br>         
              <font id="DigitadoC" color="red">0</font> Caracteres digitados &nbsp; / &nbsp; restam <font id="RestanteC" color="red">1000</font>
              <br>
              <textarea onkeyup="contarMaximoCaracter(this, 1000, 'DigitadoC', 'RestanteC')" onkeypress="contarMaximoCaracter(this, 1000, 'DigitadoC', 'RestanteC')" rows="5" cols="80" maxlength="1000" name="descricao_completa" id="descricao_completa" class="textob"><?php echo $descricao_completa ?></textarea><br>
          </TD>
        </TR>
       </TABLE>
       <br>&nbsp;

<script>
function contarMaximoCaracter(txarea, limite, idDigitado, idRestante) {   
    const total = limite;
    const tam = txarea.value.length;
    const digitado = document.getElementById(idDigitado);
    const restante = document.getElementById(idRestante);
    
    if (digitado && restante) {
        digitado.innerHTML = tam;
        restante.innerHTML = total - tam;

        if (tam > total) {
            alert(`Seu resumo atingiu o limite de ${limite} caracteres`);

            txarea.value = txarea.value.substring(0, total);
            digitado.innerHTML = total;
            restante.innerHTML = 0;
        }
    } else {
        console.error('Os IDs fornecidos para digitado e restante não foram encontrados no DOM.');
    }
}

</script>
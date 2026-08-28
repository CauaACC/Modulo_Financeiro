<?php

class GerenciarBoletoBancoBrasilAC {

    public function btnBoleto($fws_, $debug = false) {

        $data_inscricao = substr($data_inscricao, 0, -4);
        #echo $data_inscricao;
        #If($data_inscricao<20060601){
        #  $NossoNumero="123588402";
        #  $data_inscricao = "20060630";
        #  $TamNossoNumero = 8;


        If ($data_inscricao <= 20060701) {
            $NossoNumero = "123588403";
            $data_inscricao = "20060712";
            $TamNossoNumero = 8;
        } Else {
            $NossoNumero = "1235884";
            $TamNossoNumero = 10;
        }

        $refTran = $cod_inscrito;
        $FWS_Len = $TamNossoNumero - strlen($refTran);
        For ($ii = 0; $ii < $FWS_Len; $ii++) {
            $refTran = "0" . $refTran;
        }
        $refTran = $NossoNumero . $refTran;

        ?>
        <form action="https://www11.bb.com.br/site/mpag/" method="post" name="pagamento">
            <INPUT TYPE='hidden' VALUE='Inserir' NAME='acao'>
            <button class="btn btn-xs btn-primary" type="submit">Gerar Boleto Bancário</button>

            <!--idConv-->
            <!--
                <input type="hidden" name=" "           value="<?php echo  @$idConv ?>">
                <input type="hidden" name="valor"       value="<?php echo  @$FWS_Valor_Tx ?>">
                <input type="hidden" name="refTran"     value="<?php echo  @$cod_inscricao ?>">
                <input type="hidden" name="urlRetorno"  value="http://www.redeunida.org.br">
                <input type="hidden" name="nome"        value="<?php echo  @nome_inscrito ?>">
                <input type="hidden" name="endereco"    value="<?php echo  @endereco ?>">
                <input type="hidden" name="cidade"      value="<?php echo  @cidade ?>">
                <input type="hidden" name="uf"          value="<?php echo  @estado ?>">
                <input type="hidden" name="cep"         value="<?php echo  @$cep ?>">
                <input type="hidden" name="dtVenc"      value="<?php echo  @$FWS_Data_Vencimento ?>">
                <input type="hidden" name="msgLoja"     value="<?php echo  @$FWS_MSG ?>">
                <input type="hidden" name="versao"      value="002">
                <input type="hidden" name="moeda"       value="986">
                <input type="hidden" name="convClasse"  value="001">
                <input type="hidden" name="tpPagamento" value= "2">


                  <input type="hidden" name="urlRetorno"  value="/inscricoes/">
                  <input type="hidden" name="urlRetorno"  value="/inscricoes/index.php?content=view/inscricao/inscricao_login.php">


            -->
            <input type="hidden" name="idConv"      value="300470">
            <input type="hidden" name="valor"       value="<?php echo  @$FWS_Valor_Tx ?>">
            <input type="hidden" name="refTran"     value="<?php echo  @$refTran ?>">
            <input type="hidden" name="urlRetorno"  value="/inscricoes/index.php?content=view/inscricao/inscricao_trabalhos_inicio.php&cpf=<?php echo  $cpf ?>&cod_inscrito=<?php echo  $cod_inscrito ?>">
            <input type="hidden" name="nome"        value="<?php echo  @$nome_inscrito ?>">
            <input type="hidden" name="endereco"    value="<?php echo  @$endereco ?>">
            <input type="hidden" name="cidade"      value="<?php echo  @$cidade ?>">
            <input type="hidden" name="uf"          value="<?php echo  @$estado ?>">
            <input type="hidden" name="cep"         value="<?php echo  @$cep ?>">
            <input type="hidden" name="dtVenc"      value="<?php echo  @$FWS_Data_Vencimento ?>">
            <input type="hidden" name="msgLoja"     value="<?php echo  @$FWS_MSG_BOLETO ?>">
            <input type="hidden" name="versao"      value="002">
            <input type="hidden" name="moeda"       value="986">
            <input type="hidden" name="convClasse"  value="001">
            <input type="hidden" name="tpPagamento" value="21">

        </form>
        <?php
    }

    public function htmlBoleto($fws_, $debug = false) {
        ?>
        HTML DO BOLETO Banco do Brasil 
        <?php
    }

}

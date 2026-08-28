<table>
    <tr><td width="100%" height="10">
    <?php echo (verifItemChecked('Terca',  json_decode($objInscrito->getInfoAlmoco()))=='checked')? "Terça-feira – 15/08/2017<br>"  : ""; ?>
    <?php echo (verifItemChecked('Quarta', json_decode($objInscrito->getInfoAlmoco()))=='checked')? "Quarta-feira – 16/08/2017<br>" : ""; ?>
    <?php echo (verifItemChecked('Quinta', json_decode($objInscrito->getInfoAlmoco()))=='checked')? "Quinta-feira – 17/08/2017<br>" : ""; ?>

    <?php
    $qtdade_almoco = count(json_decode($objInscrito->getInfoAlmoco()));
    $valor_almoco = ($qtdade_almoco * 49);

    /*zerando valor da inscrição em virtude do inscrito ser isento*/
    if($objIsento->getCodIsento()!=''){
        echo "<b>Total de refeições: ". $qtdade_almoco . ", isento</b>";
        $valor_almoco = 0;
    }else{
        echo "<b>Total de refeições: ". $qtdade_almoco . ", totalizando R$ ". $valor_almoco.",00</b>";
    }    
    ?>
        </td><td></td>
    </tr>
    <tr><td width="100%" height="10" colspan=2 class="td-valor"><br>Valor unitário R$ 49,00 (inclui um refrigerante ou água mineral por pessoa/refeição).</td></tr>
 </table>    
<?php if(!$objInscrito->getPossuiBoletoGerado() and $OP_PROCESSO_INSCRICAO){?>
    <br>
    <a href='index.php?content=view/inscricao/inscricao_congresso_cadastro_dados_pessoais.php'
    class='btn btn-xs btn-primary' role='button'>Alterar Refeições</a>
    <!--<font color="red">(Após gerar boleto bancário esta função será bloqueada)</font>-->
<?php }?>
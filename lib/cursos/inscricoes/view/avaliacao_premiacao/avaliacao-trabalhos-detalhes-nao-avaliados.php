<?php
require_once(dirname(__FILE__)."/../../include/config.php");
?>
<script type="text/javascript">
// prepare the form when the DOM is ready
$(document).ready(function() {
    $("#criterio_1").change(somarPontos);
    $("#criterio_2").change(somarPontos);
    $("#criterio_3").change(somarPontos);
    $("#criterio_4").change(somarPontos);

function somarPontos(){
    var totalPontos = 0;
    $("#criterio_1").val()!=0? totalPontos = totalPontos+parseFloat($("#criterio_1").val()) : "";
    $("#criterio_2").val()!=0? totalPontos = totalPontos+parseFloat($("#criterio_2").val()) : "";
    $("#criterio_3").val()!=0? totalPontos = totalPontos+parseFloat($("#criterio_3").val()) : "";
    $("#criterio_4").val()!=0? totalPontos = totalPontos+parseFloat($("#criterio_4").val()) : "";
    $("#total_pontos").val(totalPontos);
}
    
});
</script>
<h3>Área do Avaliador da Premiação </h3>
<div class="panel-group">
    <div class="panel panel-default">
        <div class="panel-heading">
            <?php require_once(dirname(__FILE__)."/../../view/avaliacao_premiacao/avaliacao-trabalhos-cabecalho.php"); ?>
        </div>
        <div class="panel-body"  id="myAnchor">
            <h3>Avaliar Trabalho para Premiação - <?php echo $objEvConf->getTrabPremiacaoTitulo() ?></h3>
            <hr size='1' width='100%'>
<?php
try{
    $objTrabalho = new Trabalho();
    $objTrabalho->setCodTrabalho($fws_['cod_trabalho']);
    //$objTrabalho->setFlgEnviadoAvaliacao('1');
    $objTrabalho->carregarTrabalhoByIdTrabalho($debug);

    #Definindo cabeçalho da tabela dos resultados da consula
    echo "
      <table border='0' width='100%' colspan='2' cellspacing='3'>
      <!--<tr><td colspan='3'>Área Temática:<br>{$RS->fields[0]}<br>&nbsp;</td></tr>-->
      <!--<tr><td colspan='3'>
      Resumo de trabalho <b>A SER AVALIADO</b>:
      </td></tr>-->
      ";

    echo "
        <tr><td colspan='3'>
            <b>Titulo:</b><h4 style='color:#337ab7;'>{$objTrabalho->getTituloTrabalho()}</h4><br>
            <p align='justify'>
            <b>Palavras-chave:</b>
            <br>{$objTrabalho->getPalavrasChave()}<br>
    ";          
    if ($objTrabalho->getResumoTrabalho() != ''){
        echo "
            <br><b>Resumo:</b>
            <br>{$objTrabalho->getResumoTrabalho()}
        ";
    }
    if ($objTrabalho->getArquivoGerado() != ''){
        echo "
            <br><a href=\"javascript: visualiza('{$objTrabalho->getArquivoGerado()}');\"  title='Visualizar Resumo'>
                <img src='resources/image/doc.gif' border='0'>
                Visualizar Arquivo Resumo</a>
            <br>
        ";
    }
    if ($objTrabalho->getResumoTrabalho() != ''){
        echo "
        <br><br><b>Eixo Temática:</b>
        <br>{$objTrabalho->getTituloAreaTematica()}<br>
        ";
    }

    if($objTrabalho->getFlgEnviadoAvaliacaoPremiacao()==1){
        /*Gravando avaliador*/
        try {
            $objTrabalhoHistorico = new HistoricoAvaliacaoPremiacao();
            $objTrabalhoHistorico->setCodAreaTematica($objTrabalho->getCodAreaTematica());
            $objTrabalhoHistorico->setCodTrabalho($objTrabalho->getCodTrabalho());
            $objTrabalhoHistorico->setCodInscrito($objTrabalho->getCodInscrito());
            $objTrabalhoHistorico->setCodAvaliador($_SESSION['_cod_usuario']);
            $objTrabalhoHistorico->getHistoricoAvaliacaoByIdAvaliador();
            if($objTrabalhoHistorico->getAvaliacao()==''){
                $objTrabalhoHistorico->setAvaliacao('SELECIONADO');
                $objTrabalhoHistorico->salvar();
            }
        } catch (OKException $exc) {
            echo "<br>Selecionado para avaliação";
        }
        ?>
          <hr>
          <FORM method='POST' name='form' action='view/avaliacao_premiacao/avaliacao-trabalhos-grava.php'
                enctype='multipart/form-data'  data-toggle="validator" role="form">
              <input type="hidden" value="<?php echo $objTrabalho->getFormatoTexto()?>" name="formato_texto" id="formato_texto">
              <div class="panel">
                <h4>Verfique se o trabalho atendeu aos requisitos abaixo:</h4>
                <div class="form-group col-dm-12" style="padding-left: 0px;">
                    <label for="nome_inscrito">Caráter inovador? </label>
                    <select name="criterio_1" id="criterio_1" class="form-control col-dm-6" required>
                        <option value=''></option>
                        <option value='10'>Nota - 10 - Atendeu Plenamente</option>
                        <option value='9'>Nota - 09 - Atendeu Plenamente</option>
                        <option value='8'>Nota - 08 - Atendeu Plenamente</option>
                        <option value='7'>Nota - 07 - Atendeu Parcialmente</option>
                        <option value='6'>Nota - 06 - Atendeu Parcialmente</option>
                        <option value='5'>Nota - 05 - Atendeu Parcialmente</option>
                        <option value='4'>Nota - 04 - Atendeu Parcialmente</option>
                        <option value='3'>Nota - 03 - Atendeu Parcialmente</option>
                        <option value='2'>Nota - 02 - Atendeu Parcialmente</option>
                        <option value='1'>Nota - 01 - Atendeu Parcialmente</option>
                        <option value='0'>Nota - 00 - Não Atendeu</option>
                    </select>
                    <div class="help-block with-errors"></div>
                </div>
                <div class="form-group col-dm-12" style="padding-left: 0px;">
                    <label for="nome_inscrito">Resultados? </label>
                    <select name="criterio_2" id="criterio_2" class="form-control col-dm-6" required>
                        <option value=''></option>
                        <option value='10'>Nota - 10 - Atendeu Plenamente</option>
                        <option value='9'>Nota - 09 - Atendeu Plenamente</option>
                        <option value='8'>Nota - 08 - Atendeu Plenamente</option>
                        <option value='7'>Nota - 07 - Atendeu Parcialmente</option>
                        <option value='6'>Nota - 06 - Atendeu Parcialmente</option>
                        <option value='5'>Nota - 05 - Atendeu Parcialmente</option>
                        <option value='4'>Nota - 04 - Atendeu Parcialmente</option>
                        <option value='3'>Nota - 03 - Atendeu Parcialmente</option>
                        <option value='2'>Nota - 02 - Atendeu Parcialmente</option>
                        <option value='1'>Nota - 01 - Atendeu Parcialmente</option>
                        <option value='0'>Nota - 00 - Não Atendeu</option>
                    </select>
                    <div class="help-block with-errors"></div>
                </div>
                <div class="form-group col-dm-12" style="padding-left: 0px;">
                    <label for="nome_inscrito">Relevância Social? </label>
                    <select name="criterio_3" id="criterio_3" class="form-control col-dm-6" required>
                        <option value=''></option>
                        <option value='10'>Nota - 10 - Atendeu Plenamente</option>
                        <option value='9'>Nota - 09 - Atendeu Plenamente</option>
                        <option value='8'>Nota - 08 - Atendeu Plenamente</option>
                        <option value='7'>Nota - 07 - Atendeu Parcialmente</option>
                        <option value='6'>Nota - 06 - Atendeu Parcialmente</option>
                        <option value='5'>Nota - 05 - Atendeu Parcialmente</option>
                        <option value='4'>Nota - 04 - Atendeu Parcialmente</option>
                        <option value='3'>Nota - 03 - Atendeu Parcialmente</option>
                        <option value='2'>Nota - 02 - Atendeu Parcialmente</option>
                        <option value='1'>Nota - 01 - Atendeu Parcialmente</option>
                        <option value='0'>Nota - 00 - Não Atendeu</option>
                    </select>
                    <div class="help-block with-errors"></div>
                </div>
                <div class="form-group col-dm-12" style="padding-left: 0px;">
                    <label for="nome_inscrito" class="col-dm-6">Potêncial de Aplicabilidade? </label>
                    <select name="criterio_4" id="criterio_4" class="form-control col-dm-6" required>
                        <option value=''></option>
                        <option value='10'>Nota - 10 - Atendeu Plenamente</option>
                        <option value='9'>Nota - 09 - Atendeu Plenamente</option>
                        <option value='8'>Nota - 08 - Atendeu Plenamente</option>
                        <option value='7'>Nota - 07 - Atendeu Parcialmente</option>
                        <option value='6'>Nota - 06 - Atendeu Parcialmente</option>
                        <option value='5'>Nota - 05 - Atendeu Parcialmente</option>
                        <option value='4'>Nota - 04 - Atendeu Parcialmente</option>
                        <option value='3'>Nota - 03 - Atendeu Parcialmente</option>
                        <option value='2'>Nota - 02 - Atendeu Parcialmente</option>
                        <option value='1'>Nota - 01 - Atendeu Parcialmente</option>
                        <option value='0'>Nota - 00 - Não Atendeu</option>
                    </select>
                    <div class="help-block with-errors"></div>
                </div>
            </div> 

            <h4>Nota da Avalição:
            <input type="text" readonly style="border:0px; font-size: 20px" name="total_pontos" id="total_pontos" size="3" value="0"></h4>
            Nota máxima 40 pontos.     
            <br>
            <br>
            <b>Justificar/Sugerir:</b><br>
            <textarea name='observacao' cols='100' rows='5' style='width:100%;'></textarea>
            <br>
            Justificativa (não é obrigatória):
            
            <br>
            
            <input type='hidden' value='{$COD_EVENTO}'        name='cod_evento'>
            <input type='hidden' value='<?php echo $objTrabalho->getCodAreaTematica(); ?>' name='cod_area_tematica'>
            <input type='hidden' value='<?php echo $objTrabalho->getCodInscrito();     ?>' name='cod_inscrito'>          
            <input type='hidden' value='<?php echo $objTrabalho->getCodTrabalho();     ?>' name='cod_trabalho'>          
            <input type='hidden' value='Avaliação'            name='acao'>
            <input type='hidden' value='detalhes'             name='<?php echo $fws_['detalhes'];     ?>'>
            <input type='submit' value='Clique aqui para Confirmar Avaliação da Premiação' class='btn btn-xs btn-primary'>
            <input type='reset' value='Limpar Campos' class='btn btn-xs btn-primary' onclick="getfocus()">
          </form>
        </td></tr>
        <?php
    }else{
        echo "<tr><td><br>Status Avaliação: Devolvido o autor, aguardando correções para liberar re-avaliação<br></td></tr>";
    }
    echo "</table><br>";
      

} catch (Exception $exc) {
    echo $exc->getMessage();
}

?>
<hr size='1' width='100%'>
<!--a class='link' href='javascript:history.go(-1)'><b>voltar<b></a>-->
<a class='link' href='index.php?content=view/avaliacao_premiacao/avaliacao-trabalhos-listagem-por-avaliador.php&cod_area_tematica=<?php echo $fws_['cod_area_tematica']?>&detalhes=<?php echo $fws_['detalhes']?>'><b>voltar<b></a>
</body>
</html>
        </div>
    </div>
</div>

<form name="getdoc" method="post" action="<?php echo WB_URL_COMMAND?>" target="_blank">
    <input type="hidden" name="c" value="GerenciarUpload">
    <input type="hidden" name="m" value="abrirWord">
    <input type="hidden" value="" name="cod">
</form>
<SCRIPT LANGUAGE="JavaScript1.2" src="resources/js/avaliacao-trabalhos.js"></SCRIPT>
<SCRIPT LANGUAGE="JavaScript" src="https://cdnjs.cloudflare.com/ajax/libs/1000hz-bootstrap-validator/0.11.9/validator.min.js"></SCRIPT>
<script language="JavaScript" type="text/javascript">
    function visualiza(cod){
        document.getdoc.cod.value=cod;
        document.getdoc.submit();
    }
    function getfocus() {
         $(window).scrollTop(0);
    }

</script>
<style>
    .with-errors{
        font-size: 12px;
    }
    label {
        margin-bottom: 0px;
        margin-top: 5px;
    }
</style>

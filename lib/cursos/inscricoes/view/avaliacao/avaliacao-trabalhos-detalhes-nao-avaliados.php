<?php
require_once(dirname(__FILE__)."/../../include/config.php");
// $perpectiva['perspectiva'] = 'por-area-tematica';
// $perpectiva['perspectiva'] = 'por-avaliador';
$perpectiva['perspectiva'] = 'avulsa';
$condicoes['area-tematica'] = '';
$condicoes['enviado-avalicao'] = 1;

/** @todo adicionar regra para eventos específicos
 * configurando quantidade de criterios na avaliação */
if (COD_EVENTO==81) {
    $condicoes['enviado-avalicao'] = 0;
    // $txtTotalPontos = 10;    
    // $txtPerguntas   = 5;    
    $txtTotalPontos = 0;    
    $txtPerguntas   = 0;    
} else {
    $txtTotalPontos = 8;    
    $txtPerguntas   = 4;    
}
?>
<script type="text/javascript">
// prepare the form when the DOM is ready
$(document).ready(function() {
    // Captura mudanças em qualquer select cujo id comece com 'criterio_'
    $(document).on("change", "[id^='criterio_']", somarPontos);

    function somarPontos(){
        var totalPontos = 0;

        $("[id^='criterio_']").each(function() {
            var valor = $(this).val(); // Obtém o valor do select
            if (valor !== "" && !isNaN(parseFloat(valor)) && isFinite(valor)) { // Verifica se é numérico
                totalPontos += parseFloat(valor);
            }
        });

        $("#total_pontos").val(totalPontos); // Atualiza o campo total
    }
});
</script>
<h3>Área do Avaliador</h3>
<div class="panel-group">
    <div class="panel panel-default">
        <div class="panel-heading">
            <?php require_once(dirname(__FILE__)."/../../view/avaliacao/avaliacao-trabalhos-cabecalho.php"); ?>
        </div>
        <div class="panel-body"  id="myAnchor">
            <h3>Avaliar Trabalho</h3>
            <hr size='1' width='100%'>
<?php
try{
    $objTrabalho = new Trabalho();
    $objTrabalho->setCodTrabalho($fws_['cod_trabalho']);
    /** @todo adicionar regra para alterar flg_enviado_avaliacao quando arquivo for selecionado para avaliação sem distribuição */
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
    if ($objTrabalho->getReferenciasTrabalho() != ''){
        echo "
            <br><br><b>Referências:</b>
            <br>{$objTrabalho->getReferenciasTrabalho()}
        ";
    }
    if ($objTrabalho->getArquivoGerado() != ''){
        echo "
            <br><br><a href=\"javascript: visualiza('{$objTrabalho->getArquivoGerado()}');\"  title='Visualizar Resumo'>
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
    /** @todo acrescentar regra para configurar a forma de liberação da avaliação */
    if($objTrabalho->getFlgEnviadoAvaliacao()==1 or $condicoes['enviado-avalicao'] == 0 or in_array(COD_EVENTO, [103])){
        /*Gravando avaliador*/
        try {
            $objTrabalhoHistorico = new HistoricoAvaliacao();
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
          <FORM method='POST' name='form' action='view/avaliacao/avaliacao-trabalhos-grava.php'
                enctype='multipart/form-data'  data-toggle="validator" role="form">
              <input type="hidden" value="<?php echo $objTrabalho->getFormatoTexto()?>" name="formato_texto" id="formato_texto">
<!--            <b>Defina uma pontução para premiação:</b> <select name="pontuacao">
                            <option>1</option>
                            <option>2</option>
                            <option>3</option>
                            <option>4</option>
                            <option>5</option>
                            <option>6</option>
                            <option>7</option>
                            <option>8</option>
                            <option>9</option>
                            <option>10</option>
                        </select>
            <br><br>-->
              <div class="panel">
                <h4>Verfique se o trabalho atendeu aos requisitos abaixo:</h4>
                <?php if (COD_EVENTO==81) { ?>             
                    <?php require_once(dirname(__FILE__)."/../../view/avaliacao/avaliacao-trabalhos-criterios-81.php"); ?>
                <?php } else { ?>  
                <div class="form-group col-dm-12" style="padding-left: 0px;">
                    <label for="nome_inscrito">Clareza dos Objetivos? </label>
                    <select name="criterio_1" id="criterio_1" class="form-control col-dm-6" required>
                        <option value=''></option>
                        <option value='2'>Atendeu Plenamente</option>
                        <option value='1'>Atendeu Parcialmente</option>
                        <option value='0'>Não Atendeu</option>
                    </select>
                    <div class="help-block with-errors"></div>
                </div>
                <div class="form-group col-dm-12" style="padding-left: 0px;">
                    <label for="nome_inscrito" class="col-dm-6">Metodologia Adequada? </label>
                    <select name="criterio_2" id="criterio_2" class="form-control col-dm-6" required>
                        <option value=''></option>
                        <option value='2'>Atendeu Plenamente</option>
                        <option value='1'>Atendeu Parcialmente</option>
                        <option value='0'>Não Atendeu</option>
                    </select>
                    <div class="help-block with-errors"></div>
                </div>
                <div class="form-group col-dm-12" style="padding-left: 0px;">
                    <label for="nome_inscrito">Resultado e Discussão Relevante? </label>
                    <select name="criterio_3" id="criterio_3" class="form-control col-dm-6" required>
                        <option value=''></option>
                        <option value='2'>Atendeu Plenamente</option>
                        <option value='1'>Atendeu Parcialmente</option>
                        <option value='0'>Não Atendeu</option>
                    </select>
                    <div class="help-block with-errors"></div>
                </div>   
                <div class="form-group col-dm-12" style="padding-left: 0px;">
                    <label for="nome_inscrito">Sugerir Alteração de Eixo Temático? </label>
                    <select name="alterar_area_tematica"  id="alterar_area_tematica" class="form-control col-dm-6" required>
                        <option value=''></option>
                        <option value='0'>Não</option>
                        <option value='1'>Sim (vou informar sugestão de eixo temático no campo justificativa)</option>
                    </select>
                    <div class="help-block with-errors"></div>
                </div>                           
                <div class="form-group col-dm-12" style="padding-left: 0px;">
                    <label for="nome_inscrito">Atendeu as Normas? </label>
                    <select name="criterio_4" id="criterio_4" class="form-control col-dm-6" required>
                    <option value=''></option>
                        <option value='2'>Atendeu Plenamente</option>
                        <option value='1'>Atendeu Parcialmente</option>
                        <option value='0'>Não Atendeu</option>
                    </select>
                    <div class="help-block with-errors"></div>
                </div>
                <?php } ?>
            </div>

            <?php if (TRABALHO_ANEXA_RESUMO) { ?>
                <!-- <br><br><b>Anexar Arquivo do Trabalho Alterado: </b>
                <input type="file" name="arquivo" id="arquivo" value="" placeholder="Inserir arquivo" required>
                <span style="font-size:12px;">Extensões de arquivo permitidas são: doc, docx ou rtf.</span> -->
            <?php } ?>

            <h4>Decisão de Avaliação:</h4>
            Nota máxima <?php echo $txtTotalPontos ?> pontos.
            <br>Total de pontos de acordo com os critérios definidos acima: 
            <input type="text" readonly style="border:0px;" name="total_pontos" id="total_pontos" size="3" value="0">
            <div class="form-group col-dm-12" style="padding-left: 0px;">
                <label for="avaliacao">Sendo assim, recomendo a: (aprovação/reprovação)?</label>
                <select name="avaliacao"  id="avaliacao" class="form-control col-dm-6" required>
                    <option value=''></option>
                    <option value='APROVADO'>Aprovação do trabalho</option>
                    <option value='REPROVADO'>Reprovação do trabalho</option>
                    <?php /**@todo Avaliação de trabalho, configurar permitir devolver para correção ou não */ ?>
                    <?php if (in_array(COD_EVENTO, [103]) || $objEvConf->getTrabAvaliacaoPermitirRetorno()==1) { ?>
                    <option value='DEVOLVIDO'>Devolver ao autor para correção</option>
                    <?php } ?>
                </select>
                <div class="help-block with-errors"></div>
            </div>
            <!--<br>&nbsp;&nbsp;&nbsp;<input type='radio' name='avaliacao' value='APROVADO'  required> Aprovar-->
            <!--<br>&nbsp;&nbsp;&nbsp;<input type='radio' name='avaliacao' value='REPROVADO' required> Não Aprovar-->
            <!--<br>&nbsp;&nbsp;&nbsp;<input type='radio' name='avaliacao' value='DEVOLVIDO'> Devolver ao autor para correção-->
            <!--<br>&nbsp;&nbsp;&nbsp;<input type='radio' name='avaliacao' value='LIBERAR'> Liberar para outro avaliador-->        
                        
            <br><br>
            <b>Justificar/Sugerir:</b><br>
            <textarea name='observacao' cols='100' rows='5' style='width:100%;'></textarea>
            <br>
            Justificativa (Exigida somente em caso de não aprovação):
            
            <br><br>
            <input type='hidden' value='{$COD_EVENTO}'        name='cod_evento'>
            <input type='hidden' value='<?php echo $objTrabalho->getCodAreaTematica(); ?>' name='cod_area_tematica'>
            <input type='hidden' value='<?php echo $objTrabalho->getCodInscrito();     ?>' name='cod_inscrito'>          
            <input type='hidden' value='<?php echo $objTrabalho->getCodTrabalho();     ?>' name='cod_trabalho'>          
            <input type='hidden' value='Avaliação'            name='acao'>
            <input type='hidden' value='detalhes'             name='<?php echo $fws_['detalhes'];     ?>'>
            <input type='submit' value='Clique aqui para Confirmar Avaliação' class='btn btn-xs btn-primary'>
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
<a class='link' href='index.php?content=view/avaliacao/avaliacao-trabalhos-listagem-por-avaliador.php&cod_area_tematica=<?php echo $fws_['cod_area_tematica']?>&detalhes=<?php echo $fws_['detalhes']?>'><b>voltar<b></a>
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

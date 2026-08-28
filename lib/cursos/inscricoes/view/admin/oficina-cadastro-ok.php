<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/oficina-cabecalho.php");

@$COD_OFICINA   = $_GET['COD_OFICINA'];
require_once(dirname(__FILE__)."/../../view/admin/oficina-cadastro-busca.php");
if(@$COD_OFICINA!=''){
} else {
    require_once(dirname(__FILE__)."/../../view/admin/oficina-cadastro-listar.php");    
}

If(isset($COD_OFICINA)){

  #echo $COD_OFICINA."<br>";
    $objOficina= new Oficina();
    $objOficina->setCodOficina($COD_OFICINA);
    if($objOficina->getOficinaById($debug)){
    ?>
    <hr>
    <h4><b><?php
             $tituloAtividade = $objOficina->getCodOficina();
        if($objOficina->getTituloCertificado()!=''){
          $tituloAtividade .= ' - '. $objOficina->getTituloCertificado();
        }else{
          $tituloAtividade .= ' - '. $objOficina->getTitulo();
        }
        echo $tituloAtividade;
     ?></b></h4>
    <?php
echo <<<corpo

      <hr size='1' width='100%'>
      &nbsp;&nbsp;&nbsp;&nbsp;
      <a href='index.php?content=view/admin/oficina-cadastro-incluir.php'  class='button btn btn-primary btn-xs'> Incluir</a>
      &nbsp;&nbsp;&nbsp;&nbsp;
      <a href='index.php?content=view/admin/oficina-cadastro-alterar.php&COD_OFICINA={$objOficina->getCodOficina()}'  class='button btn btn-primary btn-xs'> Alterar</a>
      &nbsp;&nbsp;&nbsp;&nbsp;
      <br><br>
      <fieldset width='570'><legend> Dados da Atividade </legend>
corpo;
//      <a href='view/admin/oficina-cadastro-excluir-grava.php?COD_OFICINA={$objOficina->getCodOficina()}&COD_EVENTO={COD_EVENTO}&acao=Excluir'  class='button btn btn-primary btn-xs'> Excluir</a>
//      <hr size='1' width='100%'>

        FWA_Mostrar_Mensagem($_SESSION['MSG']);
        $camposEditar = false;
        include_once("oficina-cadastro-campos.php");
?>
        <div class="form-group col-sm-12" style="padding-left: 0px;">
            <div class="form-group col-sm-12" style="padding-left: 0px;">
            <hr>
            <h4>Arquivos para Certificado.</h4>
            </div>  
        </div>

        <div class="form-group col-sm-12" style="padding-left: 0px;">
            <div class="form-group col-sm-12" style="padding-left: 0px;">
                <label for="arquivo_assinatura">Assinatura do Coordenador: (JPG)</label>
                <br>Obs: para assinatura duplicada anexe arquivo com duas assinaturas juntas
                <?php if ($objOficina->verifAssinaturaCoordenador()) {?>
                    <br>
                    <button onclick="javascript:excluirArqAssCoordenador('<?php echo $objOficina->getCodOficina(); ?>');">Excluir</button>
                    <img border="1" z-index="98" style="width:250px;"
                     src="<?php echo WB_URL ?>/<?php echo WB_GRUPO?>/<?php echo WB_PROJETO?>/inscricoes/view/certificados/modelos/img/ass-coordenador.php?id=<?php echo $objOficina->getCodOficina(); ?>">  
                <?php }else{ ?>
                    <font color=red>Assinatura não anexada.</font>
                    <br>
                    <form name="form-anexo-assinatura" method="POST" action="<?php echo WB_URL_COMMAND?>" 
                          onsubmit="return criticarAnexoApresentacao();" enctype="multipart/form-data"  >
                        <input type="hidden" name="c" value="GerenciarArquivo">
                        <input type="hidden" name="m" value="salvarAnexoAssinatura">
                         <input type="hidden" name="codOficina" value="<?php echo $objOficina->getCodOficina()?>">
                        <input type="file" name="arquivo" id="arquivo" value=""
                               placeholder="Inserir arquivo"  class="btn-xs" required="">             
                        <input type="submit" class="btn btn-xs btn-primary" 
                        role="button" value="Anexar Assinatura para Certificado">
                    </form>
                <?php } ?>
                <br><br>
            </div>
        </div>
        <div class="form-group col-sm-12" style="padding-left: 0px;">
            <div class="form-group col-sm-12" style="padding-left: 0px;">
                <label for="arquivo_assinatura">Programa/Verso do Certificado: (PDF)</label>
                <br>
                <?php if ($objOficina->verifProgramaCertificado()) {?>
                    <button onclick="javascript:excluirArqPrograma('<?php echo $objOficina->getCodOficina(); ?>');">Excluir</button>
                    &nbsp;&nbsp;|&nbsp;&nbsp;
                    <button id="btnVisualizarPrograma" title="Visualizar Programa"
                    href="javascript:void(0);" onclick="javascript:visualizarPrograma('<?php echo  $objOficina->getCodOficina() ?>');">
                    <font color='green'>Visualizar Programa Anexado</font></button> 
                <?php }else{ ?>
                    <font color=red>Programa não anexado.</font>
                    <br>
                    <form name="form-anexo-programa" method="POST" action="<?php echo WB_URL_COMMAND?>"  onsubmit="return criticarAnexoApresentacao();" enctype="multipart/form-data"  >
                    <input type="hidden" name="c" value="GerenciarArquivo">
                    <input type="hidden" name="m" value="salvarAnexoPrograma">
                    <input type="hidden" name="codOficina" value="<?php echo $objOficina->getCodOficina()?>">
                    <input type="file" name="arquivo" id="arquivo" value=""
                           placeholder="Inserir arquivo"  class="btn-xs" required="">             
                    <input type="submit" class="btn btn-xs btn-primary" 
                    role="button" value="Anexar Programa/Verso do Certificado">
                    </form>
                <?php } ?>
                <br><br>
            </div>
        </div>

      <?php
    }Else{

        echo "
              <hr size=1 width=100%>
              &nbsp;&nbsp;&nbsp;&nbsp;
              <a href='index.php?content=view/admin/oficina-cadastro-incluir.php'><img src='resources/image/txt-incluir.gif' border='0'></a>
              &nbsp;&nbsp;&nbsp;&nbsp;

              <!--
              <a href=index.php?content=view/admin/oficina-cadastro-alterar.php&COD_OFICINA=".$COD_OFICINA.">Alterar</a>
              &nbsp;&nbsp;&nbsp;&nbsp;
              <a href='view/admin/oficina-cadastro-grava-exclusao.php?COD_OFICINA=".$COD_OFICINA."&acao=Excluir'>Excluir</a>
              -->
                <hr size=1 width=100%>
                <table border=0 width=570>
                  <tr><td>
                    &nbsp;&nbsp;&nbsp;&nbsp;
                    ".FWA_Mostrar_Mensagem("A Atividade Número: ".$COD_OFICINA.", não Existe ou foi Excluída!")."
                  </td></tr>
                </table>
                <br>
            ";

     }
     echo "</fieldset>";
}Else{

        echo "
              <hr size=1 width=100%>
              &nbsp;&nbsp;&nbsp;&nbsp;
              <a href='index.php?content=view/admin/oficina-cadastro-incluir.php'><img src='resources/image/txt-incluir.gif' border='0'></a>
              &nbsp;&nbsp;&nbsp;&nbsp;
                <hr size=1 width=100%>
                <table border=0 width=570>
                  <tr><td>
                    &nbsp;&nbsp;&nbsp;&nbsp;
                    Clique no Botão Incluir para Inserir uma Atividade!
                  </td></tr>
                </table>
                <br>
            ";



}
?>
</div></div></div></div>

<form name="getdocumento" method="post" action="<?php echo WB_URL_COMMAND?>" target="_blank">
    <input type="hidden" name="c" value="GerenciarArquivo">
    <input type="hidden" name="m" value="abrirPrograma">
    <input type="hidden" value="" name="cod">
</form>
<script language="JavaScript" type="text/javascript">
$(document).ready(function() {
    $('.tablesorter').tablesorter();
});
    
function visualizarPrograma(cod){
    document.getdocumento.cod.value=cod;
    document.getdocumento.submit();
}
function excluirArqPrograma(codArqPrograma){
    if(codArqPrograma){
         if(confirm('Deseja realmente excluir o arquivo?')){
            $.ajax({
                type: "POST",
                url: '<?php echo WB_URL_COMMAND ?>&t='+Math.random(),
                data: ({'codArqPrograma': codArqPrograma, 'c':'GerenciarArquivo', 'm':'excluirArqPrograma','_submit_check':'1'}),
                dataType: "json",
                async: true,
                cache: false,
                beforeSend: function() {
//                   startLoading();
                },
                success: function(data){
//                   processJsonSemFade(data);
                   if(data.statusMessage){ 
                       alert(data.message)
                       location.reload();
                   }

                }
               ,complete: function(){
//                   stopLoading();
                    location.reload();
               }
            });
        }
    }else{
        alert('Código do arquivo não foi informado!');
    }        
}
function excluirArqAssCoordenador(codArqCoordenador){
    if(codArqCoordenador){
         if(confirm('Deseja realmente excluir o arquivo?')){
            $.ajax({
                type: "POST",
                url: '<?php echo WB_URL_COMMAND ?>&t='+Math.random(),
                data: ({'codArqCoordenador': codArqCoordenador, 'c':'GerenciarArquivo', 'm':'excluirArqAssCoordenador','_submit_check':'1'}),
                dataType: "json",
                async: true,
                cache: false,
                beforeSend: function() {
                   startLoading();
                },
                success: function(data){
//                   processJsonSemFade(data);
                   if(data.statusMessage){ 
                       alert(data.message)
                       location.reload();
                   }

                }
               ,complete: function(){
                    location.reload()
                   stopLoading();
               }
            });
        }
    }else{
        alert('Código do arquivo não foi informado!');
    }        
}
</script>
<?php
/*
    <link type="text/css" href="resources/js/jquery-ui-1.7.3.custom/css/smoothness/jquery-ui-1.7.3.custom.css" rel="stylesheet" />
    <!-- <script type="text/javascript" src="resources/js/jquery-ui-1.7.3.custom/js/jquery-1.3.2.min.js"></script> -->
    <script type="text/javascript" src="resources/js/jquery-ui-1.7.3.custom/js/jquery-ui-1.7.3.custom.min.js"></script>
    <!--script type="text/javascript" src="resources/js/jquery-ui-1.7.3.custom/development-bundle/ui/i18n/ui.datepicker-pt-BR.js"></script-->
    <script type="text/javascript" src="resources/js/jquery-ui-timepicker-addon.js"></script>
    <script type="text/javascript" src="resources/js/jquery-ui-1.7.3.custom/development-bundle/ui/ui.dialog.js"></script>
    <script type="text/javascript" src="resources/js/jquery/jquery.form.js"></script>        
    <script type="text/javascript" src="resources/js/jquery/jquery.price_format.1.7.min.js"></script>
    */
    ?>
    <link   rel="stylesheet"      href="resources/js/jquery.tablesorter/themes/blue/style.css" type="text/css" id="" media="print, projection, screen" />
    <script type="text/javascript" src="resources/js/jquery.tablesorter/jquery.tablesorter.js"></script>
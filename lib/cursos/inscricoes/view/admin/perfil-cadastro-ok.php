<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/perfil-cabecalho.php");
require_once(dirname(__FILE__)."/../../view/admin/perfil-cadastro-busca.php");

@$cod_perfil   = $fwsGet_['cod_perfil'];
if(@$cod_perfil!=''){
    require_once(dirname(__FILE__)."/../../view/admin/perfil-cadastro-busca.php");
} else {
    require_once(dirname(__FILE__)."/../../view/admin/perfil-cadastro-listar.php");    
}

If(isset($cod_perfil)){

  #echo $cod_perfil."<br>";
    $objPerfil = new Perfil();
    $objPerfil->setCodPerfil($cod_perfil);
    $objPerfil->carregarPerfilById($debug);
  

    #Verificando se houve retorno de algum registro
    If ($objPerfil->getStatus()!='') {
        #Definindo cabeçalho da tabela dos resultados da consula

        #Adicionando nas variaveis, os valores dos campos retirados dos registros
        $ORDEM              = $objPerfil->getOrdem();
        $STATUS             = $objPerfil->getStatus();
        $TITULO             = $objPerfil->getTitulo();
        $PERIODO_INSCRICAO_INICIAL = formatDateView($objPerfil->getPeriodoInscricaoInicial());
        $PERIODO_INSCRICAO_FINAL   = formatDateView($objPerfil->getPeriodoInscricaoFinal());
        $PUBLICO_ALVO       = $objPerfil->getPublicoAlvo();
        $PRE_REQUISITO      = $objPerfil->getPreRequisito();
        $DESCRICAO          = $objPerfil->getDescricao();
        $OBSERVACAO         = $objPerfil->getObservacao();
        $INVESTIMENTO       = $objPerfil->getInvestimento();

        #Definindo tabela com resultado

echo <<<corpo

      <hr size='1' width='100%'>
      &nbsp;&nbsp;&nbsp;&nbsp;
      <a href='index.php?content=view/admin/perfil-cadastro-incluir.php'><img src='resources/image/txt-incluir.gif' border='0'></a>
      &nbsp;&nbsp;&nbsp;&nbsp;
      <a href='index.php?content=view/admin/perfil-cadastro-alterar.php&cod_perfil={$cod_perfil}'><img src='resources/image/txt-alterar.gif' border='0'></a>
      &nbsp;&nbsp;&nbsp;&nbsp;
      <a href='view/admin/perfil-cadastro-excluir-grava.php?cod_perfil={$cod_perfil}&COD_EVENTO={COD_EVENTO}&acao=Excluir' 
          onclick="return confirm('Tem certeza que deseja excluir?')"><img src='resources/image//txt-excluir.gif' border='0'></a>
      <hr size='1' width='100%'>
corpo;

FWA_Mostrar_Mensagem($_SESSION['MSG']);

echo <<<corpo
      <table border='0' width='570'>

      <tr><td colspan='2'><b> Código da Perfil: {$cod_perfil}</td></tr>

      <tr><td colspan='2'><b> Status:  {$STATUS}</b></td></tr>

      <tr><td width='110'></td><td width='460'></td></tr>

      <tr><td colspan='2'><b> Posição na Listagem das Perfis:  </b> {$ORDEM}º </td></tr>
      <tr><td colspan='2'><b> Titulo da Perfil:  </b> {$TITULO            }</td></tr>
        <tr><td colspan='2'><b> Investimento:  </b><br> {$INVESTIMENTO}</td></tr>
      
        <tr><td colspan='2'> <b>Período de inscrição:</b>  </td><td>
        <tr><td colspan='2'>
            Inicio: {$PERIODO_INSCRICAO_INICIAL}
            Fim:    {$PERIODO_INSCRICAO_FINAL}' 
        </td></tr>

      <tr><td colspan=2><b> Publico Alvo: </td></tr>
      <tr><td colspan=2><blockquote>{$PUBLICO_ALVO  }</td></tr>

      <tr><td colspan=2><b> Pré-Requisito: </td></tr>
      <tr><td colspan=2><blockquote>{$PRE_REQUISITO }</td></tr>

      <tr><td colspan=2><b> Descrição: </td></tr>
      <tr><td colspan=2><blockquote>{$DESCRICAO     }</td></tr>

      <tr><td colspan=2><b> Observação: </td></tr>
      <tr><td colspan=2><blockquote>{$OBSERVACAO    }</td></tr>
      </table>
      <br>
corpo;
        
        ?>
        <br><a title="Ad Taxa" href="<?php echo WB_URL_VIEW?>?content=view/admin/taxa-cadastro-incluir.php&cod_perfil=<?php echo $objPerfil->getCodPerfil()?>">ad Taxas</a>
            <?php
            try {
                $objPerfil->carregarListaTaxas('', $debug);
                foreach ($objPerfil->getListaTaxas() as $objLT) {
                  if($objLT->getStatus() == 'ATIVADA') { 
                  ?>
                    <br><a title="Alterar Taxa" href="<?php echo WB_URL_VIEW?>?content=view/admin/taxa-cadastro-alterar.php&cod_taxa=<?php echo $objLT->getCodTaxa()?>"
                    class="button btn btn-default btn-xs">
                    <span class="glyphicon glyphicon-folder-open"></span></a>
                    <?php echo " R$ ".$objLT->getTxUnicaAVistaValor()." - Período: ".formatDateView($objLT->getPeriodoInicial())." - ".formatDateView($objLT->getPeriodoFinal())." - Venc.: ".formatDateView($objLT->getMaxDatVencimento())." - status: ".$objLT->getStatus(); ?>.
                  <?php    
                  }
                }
            } catch (Exception $exc) {
                //echo $exc->getMessage();
            }
            
    }Else{

        echo "
              <hr size=1 width=100%>
              &nbsp;&nbsp;&nbsp;&nbsp;
              <a href='index.php?content=view/admin/perfil-cadastro-incluir.php'><img src='resources/image/txt-incluir.gif' border='0'></a>
              &nbsp;&nbsp;&nbsp;&nbsp;

              <!--
              <a href=index.php?content=view/admin/perfil-cadastro-alterar.php&cod_perfil=".$cod_perfil.">Alterar</a>
              &nbsp;&nbsp;&nbsp;&nbsp;
              <a href='view/admin/perfil-cadastro-grava-exclusao.php?cod_perfil=".$cod_perfil."&acao=Excluir'>Excluir</a>
              -->
                <hr size=1 width=100%>
                <table border=0 width=570>
                  <tr><td>
                    &nbsp;&nbsp;&nbsp;&nbsp;
                    ".FWA_Mostrar_Mensagem("A PERFIL Número: ".$cod_perfil.", não Existe ou foi Excluída!")."
                  </td></tr>
                </table>
                <br>
            ";

     }
}Else{

        echo "
              <hr size=1 width=100%>
              &nbsp;&nbsp;&nbsp;&nbsp;
              <a href='index.php?content=view/admin/perfil-cadastro-incluir.php'><img src='resources/image/txt-incluir.gif' border='0'></a>
              &nbsp;&nbsp;&nbsp;&nbsp;
                <hr size=1 width=100%>
                <table border=0 width=570>
                  <tr><td>
                    &nbsp;&nbsp;&nbsp;&nbsp;
                    Clique no Botão Incluir para Inserir uma PERFIL!
                  </td></tr>
                </table>
                <br>
            ";



}


?>
</div></div></div></div>
</body>
</html>

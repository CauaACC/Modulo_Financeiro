<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/ligacao-convidados-areas-tematicas-cabecalho.php");
#require_once(dirname(__FILE__)."/../../view/admin/ligacao-convidados-areas-tematicas-busca.php");

#----------------------------------------------------------------------
#Conectar no Banco

$FWS_Debug = false;

$Conexao_db = FWA_Conexao("OpcTec");

#----------------------------------------------------------------------
#Mensagens de ERRO
if($fws_['msg']){
    FWA_Mostrar_Mensagem($fws_['msg']);
}
#----------------------------------------------------------------------
#
require_once(dirname(__FILE__)."/../../view/admin/ligacao-convidados-areas-tematicas-incluir.php");

#----------------------------------------------------------------------
#Lista Trabalhos a serem avaliados da Area Tematica a qual o convidado esta associado

    ## Consulta Trabalhos por Área Temática ##

    #Definindo comando SQL
    $whereAreaTematica = "";
    if($_GET['cod_area_tematica']){
        $whereAreaTematica = " and tlca.cod_area_tematica = ".addslashesopctec($_GET['cod_area_tematica']);
    }
    
    $var_sql = "
        SELECT
        toa.cod_area_tematica
        ,toa.ordem
        ,toa.titulo
        ,tc.cod_convidado
        ,tc.nome_convidado
        FROM tab_convidados tc, tab_lig_convidados_areas_tematicas tlca
        , tab_opcoes_areas_tematicas toa
        WHERE
          (tlca.cod_evento   =".COD_EVENTO.")
        and(tlca.cod_usuario=tc.cod_usuario)
        and(tlca.cod_area_tematica=toa.cod_area_tematica)
        and(tlca.cod_evento=toa.cod_evento)
        ".$whereAreaTematica."
        and(toa.status='ATIVADO')
        ORDER BY toa.ordem, toa.titulo, tc.nome_convidado
    ";
    
    $RS=FWA_Executa_SQL($FWS_Debug,$Conexao_db,$var_sql); //Consulta login existe

    #Verificando se houve retorno de algum registro
    If (!$RS->EOF) {
      echo "<table border='0' width='100%' colspan='0' cellspacing='0'>";
      While (!$RS->EOF) {

        $cod_area_tematica    = $RS->fields[0];
        $ordem                = $RS->fields[1];
        $titulo_area_tematica = $RS->fields[2];
        $cod_convidado        = $RS->fields[3];
        $nome_convidado       = $RS->fields[4];

        If(@$titulo_area_tematica_ant!=$titulo_area_tematica){
          echo "
          <tr><td colspan='3'>
            <hr size='1' width='100%'>
            <h4 style='color:#337ab7;'>Área Temática: ".$titulo_area_tematica."</h4><br>
            Cod.Sistema:<b>".$cod_area_tematica."</b> - Ordem: <b>".$ordem."</b>

          </td></tr>
          <tr><td colspan='3'>
          Segue abaixo lista de convidados ligados a área-tematica acima:
          <br>&nbsp;
          </td></tr>
          ";
        }
        $titulo_area_tematica_ant=$titulo_area_tematica;

        echo "<tr valign='middle'><td width=6%>";
        
        echo "<a href='index.php?content=view/admin/convidados-cadastro-ok.php&COD_CONVIDADO=".$cod_convidado."'>
                    <img src='resources/image/fig-alterar.gif' alt='Editar' title='Editar Convidado' border='0'></a>";
        echo "<a href='view/admin/ligacao-convidados-areas-tematicas-excluir-grava.php?acao=Excluir&cod_convidado=".$cod_convidado."&cod_evento=".COD_EVENTO."&cod_area_tematica=".$cod_area_tematica."'>
                <img src='resources/image/fig-excluir.gif' alt='Excluir Ligação' border='0' style='margin-right:10px;'></a>";
        
        echo "</td>";
        echo "<td colspan='2' width='100%' >";

        if($cod_convidado==$fws_['cod_convidado']){
            echo "<b>".$nome_convidado."</b>";
        }  else {
            echo $nome_convidado;
        }
        echo "</td></tr>";

        #Movendo para o proximo registro
        $RS->MoveNext();
      }
      echo "</table><br>";
    }
    
?>
<hr size='1' width='100%'>
<a class='link' href='javascript:history.go(-1)'><b>voltar<b></a>
            
&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
<a class='titulo' href='index.php?content=view/admin/consulta-avaliacao-trabalhos-listagem.php'>
<B>Gerenciar Avaliação de Trabalhos</B>
</a>
</body>
</html>
</div></div></div></div></div></div></div></div>

<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/inscritos-cabecalho.php");
#require_once(dirname(__FILE__)."/../../view/admin/inscritos-cadastro-busca.php");

#--------------------------------------------------------------------
$FWS_Debug = false;
@$cod_inscrito    = $_GET['cod_inscrito'];
@$COD_PEDIDO   = $_GET['cod_pedido'];

$MSG             = $_SESSION['MSG'];
If($MSG){
  echo "<hr size='1' width='100%'>";
  FWA_Mostrar_Mensagem($MSG);  
}
$objI = new Inscrito();
$objI->setCodInscrito($cod_inscrito);
$objI->getInscritoByIdSemEvento();
if($objI->getCodEvento()!=''){
//    $objI->get();
    $cod_evento = $objI->getCodEvento(); 
}else{
    $cod_evento = COD_EVENTO; 
}
$objEv = new Evento();
$objEv->setCodEvento($cod_evento);
$objEv->getEventoById();

$objI->carregarOjbPerfil($debug);
$objPerfil = $objI->getObjPerfil();
#--------------------------------------------------------------------
If(isset($COD_PEDIDO)){

  #echo $COD_PEDIDO."<br>";

  $Conexao_db = FWA_Conexao("OpcTec");

  $var_sql = "SELECT ti.nome_inscrito, ti.cod_inscrito, tp.id as cod_pedido, tp.valor_total"
          . ", tp.tipo, tp.status, tp.descricao, tp.max_qtd_parcela, tp.dat_vencimento, tp.descricao_completa, tp.referencia "
          . "  FROM pedidos tp, tab_inscritos ti "
          . "WHERE (tp.inscrito_id=".$cod_inscrito.")and(tp.id=".$COD_PEDIDO.")"
          . "and(tp.inscrito_id=ti.cod_inscrito)and(tp.evento_id=".$cod_evento." )";

  If($RS_P=FWA_Executa_SQL($FWS_Debug,$Conexao_db,$var_sql)){
    If (!$RS_P->EOF) {
      While (!$RS_P->EOF) {
      
        #Adicionando nas variaveis, os valores dos campos retirados dos registros
             $nome_inscrito = $RS_P->fields[0];
             $cod_inscrito  = $RS_P->fields[1];
             $cod_pedido = $RS_P->fields[2];
             $valor_total   = $RS_P->fields[3];
             $tipo      = $RS_P->fields[4];
             $status    = $RS_P->fields[5];
             $descricao = $RS_P->fields[6];
             $max_qtd_parcela = $RS_P->fields[7];
//             $dat_vencimento = formatDateView($RS_P->fields[8]);
             $dat_vencimento = $RS_P->fields[8];
             $descricao_completa = $RS_P->fields[9];
             $referencia = $RS_P->fields[10];
                                                                                 
                                                   
        #Definindo tabela com resultado
        $WB_URL_VIEW = WB_URL_VIEW;
        echo "
        <hr size='1' width='100%'>
        <span style class='texto'> <B> Ficha de Alteração de Pedido</B><br>
        <span style class='textop'> <B> * Campos Obrigatórios</B></span>

        <table border='0' width='565'>
        <tr><td colspan='2' align='left'>

        <FORM method='POST' name='form' id='form' action='view/admin/inscritos-pedido-cadastro-alterar-grava.php' onsubmit=\"return criticar()\">
          <a href='{$WB_URL_VIEW}?content=view/admin/inscritos-cadastro-ok.php&cod_pedido={$COD_PEDIDO}&cod_inscrito={$cod_inscrito}'><button type='button' style='margin-left:15px;'>Voltar</button></a>
          <input type='hidden' name='COD_PEDIDO' value='{$COD_PEDIDO}'>
          <input type='hidden' name='COD_EVENTO'    value='{$$cod_evento}'>
          <input type='submit' value='Alterar' style='margin-left:15px;'>
          <input type='reset' value='Limpar' style='margin-left:15px;'>
          <br>&nbsp;
        </td></tr>
        </table>
        <fieldset width='570'><legend> Dados do Pedido </legend>
          <span style class='texto'>&nbsp;&nbsp;<b>Cógido do Pedido: {$COD_PEDIDO}</b></span>
          <blockquote>
        ";
        
        require_once("inscritos-pedido-cadastro-campos.php");

        echo "
        </blockquote>
        </fieldset>
        <table border='0' width='565'>
        <tr><td colspan='2' align='left'>
              <br><br>
              <input type='submit' value='Alterar'>
              &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='reset' value='Limpar'>
              &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' id='salvarComoNovo'>Salvar como novo</button>
        </td></tr>
        </table>
        </FORM>
        ";

        #Movendo para o proximo registro
        $RS_P->MoveNext();
      }


    }Else{
      echo "Codigo não encontrado.";
    }
  }Else{
    echo "Problema no SQL.";
  }
}Else{
  echo "É Necessário Codigo do Pedido.";
}
?>
</div></div></div></div></div>

<script>
    document.getElementById("salvarComoNovo").addEventListener("click", function(event) {
        if (confirm("Tem certeza que deseja salvar os dados como novo registro?")) {
            // Se o usuário confirmar, altere o 'action' do formulário
            document.getElementById("form").action = "view/admin/inscritos-pedido-cadastro-incluir-grava.php";
            // Em seguida, envie o formulário
            document.getElementById("form").submit();
        } else {
            // Se o usuário não confirmar, impeça a submissão do formulário
            event.preventDefault();
        }
    });
</script>
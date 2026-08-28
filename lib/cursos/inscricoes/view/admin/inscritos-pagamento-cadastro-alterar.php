<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/inscritos-cabecalho.php");
#require_once(dirname(__FILE__)."/../../view/admin/inscritos-cadastro-busca.php");

#--------------------------------------------------------------------
@$cod_inscrito    = $_GET['cod_inscrito'];
@$COD_PAGAMENTO   = $_GET['cod_pagamento'];
if($fwsGet_['cod_evento']!=''){
   $COD_EVENTO  = $fwsGet_['cod_evento'];
}else{
   $COD_EVENTO  = COD_EVENTO; 
}
$MSG             = $_SESSION['MSG'];
If($MSG){
  echo "<hr size='1' width='100%'>";
  FWA_Mostrar_Mensagem($MSG);  
}

#--------------------------------------------------------------------
If(isset($COD_PAGAMENTO)){

  #echo $COD_PAGAMENTO."<br>";

  $Conexao_db = FWA_Conexao("OpcTec");

  $var_sql = "SELECT ti.nome_inscrito, tp.cod_evento, tp.cod_pagamento, tp.cod_inscrito, tp.valor"
          . ", tp.forma_pagamento, tp.campo_7, tp.data_inclusao, tp.data_alteracao, tp.observacao, tp.numero_empenho"
          . ", tp.data_emissao_recibo, tp.observacao_recibo, tp.status, tp.pedido_id , tp.data_pagamento, tp.transacao_id "
          . "FROM tab_pagamentos tp, tab_inscritos ti "
          . "WHERE (tp.cod_inscrito=".$cod_inscrito.")and(tp.cod_pagamento=".$COD_PAGAMENTO.")"
          . "and(tp.cod_inscrito=ti.cod_inscrito)";
//  echo $var_sql;

  If($RS_P=FWA_Executa_SQL($debug,$Conexao_db,$var_sql)){
    If (!$RS_P->EOF) {
      While (!$RS_P->EOF) {

        #Adicionando nas variaveis, os valores dos campos retirados dos registros
             $nome_inscrito       = $RS_P->fields[0];
             $cod_evento_real     = $RS_P->fields[1];
             $cod_pagamento       = $RS_P->fields[2];
             $cod_inscrito        = $RS_P->fields[3];
             $valor               = formatMoneyView($RS_P->fields[4]);
             $forma_pagamento     = $RS_P->fields[5];
             $numero_boleto       = $RS_P->fields[6];
             $data_inclusao_pagamento  = $RS_P->fields[7];
             $data_alteracao_pagamento = $RS_P->fields[8];
             $observacao          = $RS_P->fields[9];
             $numero_empenho      = $RS_P->fields[10];
             $data_emissao_recibo = substr($RS_P->fields[11],0,10);
             $observacao_recibo   = $RS_P->fields[12];
             $status   = $RS_P->fields[13];
             $pedido_id = $RS_P->fields[14];
             $data_pagamento = substr($RS_P->fields[15],0,10);
             $transacao_id = $RS_P->fields[16];
                                                   
        #Verificando se o evento do registro é diferente do evento da rota
        if ($cod_evento_real != COD_EVENTO) {
            $temAcesso = @FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_GERENCIAR_INSCRITO, 5, false, false, $cod_evento_real);
            if ($temAcesso) {
                $urlCorreta = WB_URL_VIEW . "?content=view/admin/inscritos-pagamento-cadastro-alterar.php&cod_evento=" . $cod_evento_real . "&cod_pagamento=" . $COD_PAGAMENTO . "&cod_inscrito=" . $cod_inscrito;
                echo "
                <hr size='1' width='100%'>
                <div class='alert alert-warning' style='margin:15px;'>
                    <strong>Atenção!</strong> Este pagamento pertence ao evento <b>{$cod_evento_real}</b>, mas você está acessando pelo evento <b>" . COD_EVENTO . "</b>.<br>
                    Redirecionando para o evento correto em <b><span id='countdown'>3</span></b> segundos...
                    <br><br>
                    <a href='{$urlCorreta}' class='btn btn-primary btn-sm'>Ir agora</a>
                </div>
                <script>
                    var segundos = 3;
                    var timer = setInterval(function() {
                        segundos--;
                        document.getElementById('countdown').textContent = segundos;
                        if (segundos <= 0) {
                            clearInterval(timer);
                            window.location.href = '{$urlCorreta}';
                        }
                    }, 1000);
                </script>
                ";
            } else {
                echo "
                <hr size='1' width='100%'>
                <div class='alert alert-danger' style='margin:15px;'>
                    <strong>Acesso negado!</strong> Este pagamento pertence ao evento <b>{$cod_evento_real}</b>, mas você está no evento <b>" . COD_EVENTO . "</b> e não possui permissão de acesso ao evento deste registro.
                    <br><br>
                    <a href='{$WB_URL_VIEW}?content=view/admin/inscritos-cadastro-ok.php&cod_inscrito={$cod_inscrito}' class='btn btn-secondary btn-sm'>Voltar</a>
                </div>
                ";
            }
            $RS_P->MoveNext();
            continue;
        }

        #Usando o cod_evento real do registro para o hidden field
        $COD_EVENTO = $cod_evento_real;

        #Definindo tabela com resultado

        echo "
        <hr size='1' width='100%'>
        <span style class='texto'> <B> Ficha de Alteração de Pagamento</B><br>
        <span style class='textop'> <B> * Campos Obrigatórios</B></span>

        <table border='0' width='565'>
        <tr><td colspan='2' align='left'>

        <FORM method='POST' name='form' id='form' action='view/admin/inscritos-pagamento-cadastro-alterar-grava.php' onsubmit=\"return criticar()\">
          <a href='{$WB_URL_VIEW}?content=view/admin/inscritos-cadastro-ok.php&cod_pagamento={$COD_PAGAMENTO}&cod_inscrito={$cod_inscrito}'><button type='button' style='margin-left:15px;'>Voltar</button></a>        
          <input type='hidden' name='COD_PAGAMENTO' value='{$COD_PAGAMENTO}'>
          <input type='hidden' name='COD_EVENTO'    value='{$COD_EVENTO}'>
          <input type='submit' value='Alterar' style='margin-left:15px;'> 
          <input type='reset' value='Limpar' style='margin-left:15px;'>
          <br>&nbsp;
        </td></tr>
        </table>
        <fieldset width='570'><legend> Dados do Pagamento </legend>
          <span style class='texto'>&nbsp;&nbsp;<b>Cógido do Pagamento: {$COD_PAGAMENTO}</b></span>
          <blockquote>
        ";
        
        require_once("inscritos-pagamento-cadastro-campos.php");

        echo "
        </blockquote>
        </fieldset>
        <table border='0' width='565'>
        <tr><td colspan='2' align='left'>
              <br><br>
              <input type='submit' value='Alterar'> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='reset' value='Limpar'>
              &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
              <button type='button' id='salvarComoNovo'>Salvar como novo</button>
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
  echo "É Necessário Codigo do Pagamento.";
}
?>
</div></div></div></div></div>
<script>
    document.getElementById("salvarComoNovo").addEventListener("click", function(event) {
        if (confirm("Tem certeza que deseja salvar os dados como novo registro?")) {
            // Se o usuário confirmar, altere o 'action' do formulário
            document.getElementById("form").action = "view/admin/inscritos-pagamento-cadastro-incluir-grava.php";
            // Em seguida, envie o formulário
            document.getElementById("form").submit();
        } else {
            // Se o usuário não confirmar, impeça a submissão do formulário
            event.preventDefault();
        }
    });
</script>
<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/inscritos-cabecalho.php");
require_once(dirname(__FILE__)."/../../view/admin/inscritos-cadastro-busca.php");

$FWS_Debug = false;
@$COD_INSCRITO   = $fwsGet_['cod_inscrito'];

#--------------------------------------------------------------------
$MSG   = $_SESSION['MSG'];

$MSG_Imprimir = $MSG;
$loc   = "/--/";
$subst = "<br>";
$MSG_Imprimir = preg_replace($loc,$subst,$MSG_Imprimir);
$loc   = "/-/";
$subst = " ";
$MSG_Imprimir = preg_replace($loc,$subst,$MSG_Imprimir);

If($MSG_Imprimir){
  echo "<hr size='1' width='100%'>";
  echo $MSG_Imprimir;
  #echo "<hr size='1' width='100%'>";
}

#--------------------------------------------------------------------
If(isset($COD_INSCRITO)){

  #echo $COD_INSCRITO."<br>";

  $Conexao_db = FWA_Conexao("OpcTec");

  $var_Sql = "SELECT
                ti.cod_inscrito,
                ti.nome_inscrito,
                ti.cpf,
                ti.rg,
                ti.e_mail,
                ti.data_nascimento,
                ti.sexo,
                ti.profissao_atuacao,
                ti.grau_escolaridade,
                ti.instituicao,
                ti.endereco,
                ti.numero,
                ti.complemento,
                ti.bairro,
                ti.cep,
                ti.cidade,
                ti.estado,
                ti.uf,
                ti.fone_residencial,
                ti.fone_comercial,
                ti.fone_celular,
                ti.desconto,
                ti.data_inscricao,
                ti.data_alteracao,
                ti.http_user_agent,
                ti.ip_cliente,
                ti.status,
                ti.session_id
                , IFNULL(ip.total_pagamentos,0) as total_pagamentos 
                , IFNULL(ip.valor_pagamentos,0) as valor_pagamentos 
                ,ti.cod_perfil
                ,top.titulo as titulo_perfil
                ,ti.info_almoco
,ti.nome_recibo       
,ti.institucional     
,ti.endereco_recibo   
,ti.numero_recibo     
,ti.complemento_recibo
,ti.bairro_recibo     
,ti.cep_recibo        
,ti.cidade_recibo     
,ti.estado_recibo        
,ti.cpf_recibo  
,ti.tipo_instituicao
,ti.tipo_inscricao
                FROM tab_inscritos ti
                inner join tab_opcoes_perfis top on ti.cod_perfil=top.cod_perfil
                left join (SELECT count(ip.cod_inscrito) as total_pagamentos,sum(ip.valor) as valor_pagamentos, ip.cod_inscrito FROM tab_pagamentos ip 
                       group by ip.cod_inscrito) as ip 
                       on ti.cod_inscrito=ip.cod_inscrito
                WHERE
                (ti.cod_inscrito = ".$COD_INSCRITO.")AND
                (ti.cod_evento   = ".COD_EVENTO.")
               ";

  If($RS=FWA_Executa_SQL($FWS_Debug,$Conexao_db,$var_Sql)){
    If (!$RS->EOF) {
      While (!$RS->EOF) {

      #Adicionando nas variaveis, os valores dos campos retirados dos registros
        $cod_inscrito          = $RS->fields[0];
        $nome_inscrito         = $RS->fields[1];
        $cpf                   = $RS->fields[2];
        $rg                    = $RS->fields[3];
        $e_mail                = $RS->fields[4];
        $data_nascimento       = $RS->fields[5];
        $sexo                  = $RS->fields[6];
        $profissao_atuacao     = $RS->fields[7];
        $fws_['profissao_atuacao']     = $RS->fields[7];
        $grau_escolaridade     = $RS->fields[8];
        $instituicao           = $RS->fields[9];;
        $endereco              = $RS->fields[10];
        $numero                = $RS->fields[11];
        $complemento           = $RS->fields[12];
        $bairro                = $RS->fields[13];
        $cep                   = $RS->fields[14];
        $cidade                = $RS->fields[15];
        $estado                = $RS->fields[16];
        $uf                    = $RS->fields[17];
        $fone_residencial      = $RS->fields[18];
        $fone_comercial        = $RS->fields[19];
        $fone_celular          = $RS->fields[20];
        $desconto              = $RS->fields[21];
        $data_inscricao        = $RS->fields[22];
        $data_alteracao        = $RS->fields[23];
        $http_user_agent       = $RS->fields[24];
        $ip_cliente            = $RS->fields[25];
        $status                = $RS->fields[26];
        $session_id            = $RS->fields[27];

        $valor                 = $RS->fields[28];
        If($valor!=""){
          $valor = substr($valor,0,strlen($valor)-2).",".substr($valor,-2);
        }Else{
          $valor = "O Pagamento ainda não consta no sistema.";
        }

        $cod_perfil     = $RS->fields[30];
        $fws_['cod_perfil']    = $RS->fields[30];
        $titulo_perfil         = $RS->fields[31];
        $info_almoco           = $RS->fields[32];
        $nome_recibo           = $RS->fields[33];
        $institucional         = $RS->fields[34];
        $endereco_recibo       = $RS->fields[35];
        $numero_recibo         = $RS->fields[36];
        $complemento_recibo    = $RS->fields[37];
        $bairro_recibo         = $RS->fields[38];
        $cep_recibo            = $RS->fields[39];
        $cidade_recibo         = $RS->fields[40];
        $estado_recibo         = $RS->fields[41];
        $cpf_recibo            = $RS->fields[42];
        $tipo_instituicao      = $RS->fields[43];
        $tipo_inscricao      = $RS->fields[44];
        $fws_['tipo_instituicao']      = $RS->fields[43];
        $fws_['tipo_inscricao']= $RS->fields[44];

        #Definindo tabela com resultado

        $DATA_INSCRICAO = substr($data_inscricao,6,2)."/".substr($data_inscricao,4,2)."/".substr($data_inscricao,0,4)." - ".substr($data_inscricao,8,2)."h".substr($data_inscricao,10,2);


        /* Moldura no mesmo desenho da previa da NFS-e: cabecalho com filete,
           acoes a direita, e os campos em caixas por assunto (o <fieldset> com
           <legend> e as <table border=0> de espacamento sairam — quem delimita
           agora sao as caixas .dp-grupo). */

        /* Um jogo de botoes so, usado em cima e embaixo. "Salvar como novo" vai
           por CLASSE e nao por id: agora sao dois botoes na pagina, e
           getElementById so acharia o primeiro. */
        $BOTOES_FICHA = "
              <button type='submit' class='btn btn-primary btn-sm'>
                <i class='glyphicon glyphicon-ok'></i> Salvar altera&ccedil;&atilde;o</button>
              <button type='button' class='btn btn-default btn-sm js-salvar-como-novo'>Salvar como novo</button>
              <input type='reset' class='btn btn-default btn-sm' value='Limpar'>
              <button type='button' class='btn btn-default btn-sm' onclick='history.back()'>Voltar</button>";

        echo "
        <SCRIPT LANGUAGE=\"JavaScript1.2\" SRC=\"resources/js/inscritos-cadastro.js\"></SCRIPT>

        <FORM method='POST' name='form' id='form' action='view/admin/inscritos-cadastro-alterar-grava.php' onsubmit=\"return criticarConvidado()\">
          <input type='hidden' name='COD_INSCRITO' value='{$COD_INSCRITO}'>
          <input type='hidden' name='COD_EVENTO'    value='{$COD_EVENTO}'>

          <div class='dp-cab'>
            <span>Alterar inscrito &middot; código {$COD_INSCRITO}</span>
            <span class='dp-cab-acoes'>{$BOTOES_FICHA}</span>
          </div>
          <div style='font-size:11px; color:#999; margin-bottom:4px;'>* Campos obrigat&oacute;rios</div>
        ";

        include_once("inscritos-cadastro-campos.php");

        /* Os mesmos botoes repetidos no rodape: a ficha tem 60+ campos, e quem
           termina de preencher esta no fim da pagina, nao no topo. Sao o MESMO
           conjunto e o MESMO tamanho em cima e embaixo — dois jogos diferentes
           fariam quem rolou a pagina procurar o que ja tinha visto. */
        echo "
        <div class='dp-cab' style='border-bottom:0; border-top:1px solid #eee;
                                   padding-top:10px; margin-top:14px;'>
          <span></span>
          <span class='dp-cab-acoes'>{$BOTOES_FICHA}</span>
        </div>
        </FORM>
        ";

        #Movendo para o proximo registro
        $RS->MoveNext();
      }


    }Else{
      echo "Codigo não encontrado.";
    }
  }Else{
    echo "Problema no SQL.";
  }
}Else{
  echo "É Necessário Codigo do Inscrito.";
}

?>
    </div><!-- panel-body -->
</div><!-- panel panel-default -->
</div><!-- panel-group -->
<script>
    // Dois botoes iguais (topo e rodape) — por isso querySelectorAll por classe.
    document.querySelectorAll(".js-salvar-como-novo").forEach(function (btn) {
        btn.addEventListener("click", function(event) {
            if (confirm("Tem certeza que deseja salvar os dados como novo registro?")) {
                // Se o usuário confirmar, altere o 'action' do formulário
                document.getElementById("form").action = "view/admin/inscritos-cadastro-incluir-grava.php";
                // Em seguida, envie o formulário
                document.getElementById("form").submit();
            } else {
                // Se o usuário não confirmar, impeça a submissão do formulário
                event.preventDefault();
            }
        });
    });
</script>

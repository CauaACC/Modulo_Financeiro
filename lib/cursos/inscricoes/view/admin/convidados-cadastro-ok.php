<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/convidados-cabecalho.php");
require_once(dirname(__FILE__)."/../../view/admin/convidados-cadastro-busca.php");

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

@$COD_CONVIDADO   = $_GET['COD_CONVIDADO'];
If(isset($COD_CONVIDADO)){

  #echo $COD_CONVIDADO."<br>";

  $Conexao_db = FWA_Conexao("OpcTec");

  $var_sql = "select 
                tc.cod_convidado,
                tc.cod_atuacao_convidado,
                tc.nome_convidado,
                tc.cpf,
                tc.rg,
                tc.e_mail,
                tc.data_nascimento,
                tc.sexo,
                tc.profissao_atuacao,
                tc.grau_escolaridade,
                tc.instituicao,
                tc.endereco,
                tc.numero,
                tc.complemento,
                tc.bairro,
                tc.cep,
                tc.cidade,
                tc.estado,
                tc.uf,
                tc.fone_residencial,
                tc.fone_comercial,
                tc.fone_celular,
                tc.desconto,
                tc.data_inclusao,
                tc.data_alteracao,
                tc.http_user_agent,
                tc.ip_cliente,
                tc.status,
                tc.session_id,
                (SELECT toa.titulo
                  FROM tab_opcoes_atuacoes_convidados toa
                  WHERE
                  (toa.cod_evento            = ".COD_EVENTO.")AND
                  (toa.cod_atuacao_convidado = tc.cod_atuacao_convidado)
                  ORDER BY toa.titulo
                )
                ,u.login , u.cod_usuario
                 FROM tab_convidados tc
                 inner join sis_usuarios u ON u.cod_usuario=tc.cod_usuario
                 inner join sis_ligacao_usuarios_eventos lue ON u.cod_usuario=lue.cod_usuario  and  lue.cod_evento  = ".COD_EVENTO."
                 WHERE
                 (tc.cod_convidado = ".$COD_CONVIDADO.")
                 ORDER BY tc.cod_convidado DESC
                ";

//   echo $var_sql;

    #Fim da definição do comando SQL

    #Executando a consulta no banco SQL
    $RS = $Conexao_db->Execute($var_sql);

    #Verificando se houve retorno de algum registro
    If (!$RS->EOF) {
      #Definindo cabeçalho da tabela dos resultados da consula

      #Efetuando loop para o visualização dos registros retornados pela consulta SQL
      While (!$RS->EOF) {

      #Adicionando nas variaveis, os valores dos campos retirados dos registros
        $cod_convidado         = $RS->fields[0];
        $cod_atuacao_convidado = $RS->fields[1];
        $nome_convidado        = $RS->fields[2];
        $cpf                   = $RS->fields[3];
        $rg                    = $RS->fields[4];
        $e_mail                = $RS->fields[5];
        $data_nascimento       = $RS->fields[6];
        $sexo                  = $RS->fields[7];
        $profissao_atuacao     = $RS->fields[8];
        $grau_escolaridade     = $RS->fields[9];;
        $instituicao           = $RS->fields[10];
        $endereco              = $RS->fields[11];
        $numero                = $RS->fields[12];
        $complemento           = $RS->fields[13];
        $bairro                = $RS->fields[14];
        $cep                   = $RS->fields[15];
        $cidade                = $RS->fields[16];
        $estado                = $RS->fields[17];
        $uf                    = $RS->fields[18];
        $fone_residencial      = $RS->fields[19];
        $fone_comercial        = $RS->fields[20];
        $fone_celular          = $RS->fields[21];
        $desconto              = $RS->fields[22];
        $data_inscricao        = $RS->fields[23];
        $data_alteracao        = $RS->fields[24];
        $http_user_agent       = $RS->fields[25];
        $ip_cliente            = $RS->fields[26];
        $status                = $RS->fields[27];
        $session_id            = $RS->fields[28];
        $nome_atuacao_candidato= $RS->fields[29];
        $login                 = $RS->fields[30];
        $cod_usuario           = $RS->fields[31];

        #Definindo tabela com resultado
        
echo "
      <hr size='1' width='100%'>
      &nbsp;&nbsp;&nbsp;&nbsp;
      <a href='index.php?content=view/admin/login-cadastro-incluir.php'>Cadastrar Login</a>
      <!--
      &nbsp;&nbsp;&nbsp;&nbsp;
      <a href='index.php?content=view/admin/convidados-cadastro-alterar.php&COD_CONVIDADO={$COD_CONVIDADO}'><img src='resources/image/txt-alterar.gif' border='0'></a>
      &nbsp;&nbsp;&nbsp;&nbsp;
      <a href='view/admin/convidados-cadastro-excluir-grava.php?COD_CONVIDADO={$COD_CONVIDADO}&COD_EVENTO={COD_EVENTO}&acao=Excluir'><img src='resources/image//txt-excluir.gif' border='0'></a>
          -->
      <hr size='1' width='100%'>

      <table border='0' width='570'>

        <tr><td colspan='2'>&nbsp;<b> Nome Convidado:
          <a href='index.php?content=view/admin/convidados-cadastro-ok.php&cod_convidado={$cod_convidado}'>
          {$nome_convidado}</b></a></td></tr>

      <tr><td colspan='2'>&nbsp;<b> Status:  {$status}</b></td></tr>
      <tr><td colspan='2'>&nbsp;<b> Categoria Convidado:<br>  {$nome_atuacao_candidato}</b></td></tr>

      <tr><td width='110'>&nbsp;</td><td width='460'>&nbsp;</td></tr>
      <tr><td colspan='2'><hr size=1 width=100%>
";      

echo  "LOGIN: ";      

if($login == ''){
    echo  "<a href='index.php?content=view/admin/login-cadastro-incluir.php&cod_convidado=".$cod_convidado."'>Cadastrar login para o convidado</a>";
}else{
    
    echo "
    <a href='index.php?content=view/admin/login-cadastro-alterar.php&COD_USUARIO=".$cod_usuario."'>
        <img src='resources/image/fig-alterar.gif' title='Editar login' border='0'></a>    
    <a title='Logar Como' target='_blank' 
    href='index.php?content=view/admin/login.php&acao=logarComo&login=".$login."&COD_USUARIO=".$cod_usuario."'>
    <img src='resources/image/icone_preview.gif' border='0'></a>
    
    <a title='Enviar senha para e-mail' target='_blank' onclick='executarAcao('{$cod_convidado}');' href=\"#\">
    <img src='resources/image/email-2.gif' border='0'></a>
    ".$login."<br>";
        ?>
    <?php /** @todo Mover esse botão e suas ações para um componete de botões centralizado  */ ?>
    <form name="form" method="POST" onsubmit="return criticar();"  target="_blank"
    action="<?php echo WB_URL_COMMAND?>">
    <input type="hidden" name="c" value="GerenciarEmail">
    <input type="hidden" name="m" value="enviarEmailLoginConvidado">
    <input type="hidden" name="cod_convidado" value="<?php echo $cod_convidado?>">
    <button class="btn btn-xs btn-primary btn-enviar-login" type="submit">Enviar login para</button>
    <?php echo  mb_strtolower($e_mail) ?>
    <input type="hidden" name="e_mail" id="e_mail" value="<?php echo  mb_strtolower($e_mail) ?>">
    </form>
    <div id='carregando' class='hidden' hidden='hidden'>
        <img src='<?php echo WB_URL_IMG ?>/loading-9.gif' width='20' height='20' border='0'>
    </div>
    <div id='msgConvidado' style='float: left;margin-left:10px;'></div>
    <?php
}
    echo "<hr size='1' width='100%'>";
    
    echo "&nbsp;&nbsp;&nbsp;&nbsp;
    <a href='index.php?content=view/admin/convidados-cadastro-alterar.php&COD_CONVIDADO={$COD_CONVIDADO}'' target='_blank'>
    Dados pessoais</a>";
    
    echo "&nbsp;&nbsp;&nbsp;&nbsp;
    <a href='index.php?content=view/admin/ligacao-usuarios-grupos-listagem.php&COD_USUARIO={$cod_usuario}' target='_blank'>
    Listar grupos</a>";

    
    if($login != ''){
        echo "&nbsp;&nbsp;&nbsp;&nbsp;
          <a href='javascript:void(0);' id='btnBuscarGruposConvidado' data-cod_convidado='{$cod_convidado}'>
        Adicionar grupos</a>";
        
        echo "&nbsp;&nbsp;&nbsp;&nbsp;
        <a href='index.php?content=view/admin/convidados-cadastro-ok-listagem-areas-tematicas.php&COD_CONVIDADO={$cod_convidado}' target='_blank'>
        Adicionar Eixos</a>";
    }    

      


echo     "<hr size=1 width=100%></td></tr>";
echo     "<tr><td colspan='2'>

      <TABLE WIDTH='500' BORDER='0' CELLSPACING='0' CELLPADDING='4'>
         <TR><TD VALIGN='top' width='30'>Nome:     </TD><TD width='470'> <b>{$nome_convidado} </TD></TR>
         <TR><TD VALIGN='top' >CPF:      </TD><TD> <b>{$cpf}           </TD></TR>
         <TR><TD VALIGN='top' >RG:       </TD><TD> <b>{$rg}            </TD></TR>
         <TR><TD VALIGN='top' >E-Mail:   </TD><TD> <b>{$e_mail}        </TD></TR>
         <TR><TD VALIGN='top' COLSPAN='2'>Data de Nascimento (dd/mm/aaaa): &nbsp;&nbsp;  <b>{$data_nascimento}</TD></TR>
         <TR><TD VALIGN='top' COLSPAN='2'>Sexo:                  <b>{$sexo}              </TD></TR>
         <TR><TD VALIGN='top' >Rua:      </TD><TD> <b>{$endereco}                        </TD></TR>
         <TR><TD VALIGN='top' >Número:   </TD><TD> <b>{$numero}                          </TD></TR>
         <TR><TD VALIGN='top' COLSPAN='2'>Complemento:  <b>{$complemento}                </TD></TR>
         <TR><TD VALIGN='top' >Bairro:   </TD><TD> <b>{$bairro}</b> &nbsp;&nbsp;&nbsp; Cep:     <b>{$cep}</b>    </TD></TR>
         <TR><TD VALIGN='top' >Cidade:   </TD><TD> <b>{$cidade}</b> &nbsp;&nbsp;&nbsp; Estado:  <b>{$estado}</b> </TD></TR>
         <TR><TD VALIGN='top' COLSPAN='2'>Telefone Res:  <b>{$fone_residencial}</TD></TR>
         <TR><TD VALIGN='top' COLSPAN='2'>Telefone Com:  <b>{$fone_comercial}</TD></TR>
         <TR><TD VALIGN='top' COLSPAN='2'>Telefone Cel:  <b>{$fone_celular}</TD></TR>
         <TR><TD VALIGN='top' COLSPAN='2'>Profissão/Atuação:     <b>{$profissao_atuacao} </TD></TR>
         <TR><TD VALIGN='top' COLSPAN='2'>Instituição/Entidade:           <b>{$instituicao}       </TD></TR>
      </TABLE>

      </td></tr></table>
      <br>
";

        #Movendo para o proximo registro
        $RS->MoveNext();
      }
    }Else{

        echo "
              <hr size=1 width=100%>
              &nbsp;&nbsp;&nbsp;&nbsp;
              <a href='index.php?content=view/admin/convidados-cadastro-incluir.php'><img src='resources/image/txt-incluir.gif' border='0'></a>
              &nbsp;&nbsp;&nbsp;&nbsp;

              <!--
              <a href=index.php?content=view/admin/convidados-cadastro-alterar.php&COD_CONVIDADO=".$COD_CONVIDADO.">Alterar</a>
              &nbsp;&nbsp;&nbsp;&nbsp;
              <a href='view/admin/convidados-cadastro-grava-exclusao.php?COD_CONVIDADO=".$COD_CONVIDADO."&acao=Excluir'>Excluir</a>
              -->
                <hr size=1 width=100%>
                <table border=0 width=570>
                  <tr><td>
                    &nbsp;&nbsp;&nbsp;&nbsp;
                    O Convidado Número: ".$COD_CONVIDADO.", não Existe ou foi Excluído!
                  </td></tr>
                </table>
                <br>
            ";

     }
}Else{

        echo "
              <hr size=1 width=100%>
              &nbsp;&nbsp;&nbsp;&nbsp;
              <a href='index.php?content=view/admin/convidados-cadastro-incluir.php'><img src='resources/image/txt-incluir.gif' border='0'></a>
              &nbsp;&nbsp;&nbsp;&nbsp;
                <hr size=1 width=100%>
                <table border=0 width=570>
                  <tr><td>
                    &nbsp;&nbsp;&nbsp;&nbsp;
                    Clique no Botão Incluir para Inserir um Convidado!
                  </td></tr>
                </table>
                <br>
            ";



}


?>
<hr size='1' width='100%'>
<a class='link' href='javascript:history.go(-1);'><b>voltar<b></a>
</body>
</html>
<form name="formAcao" method="post" action="<?php echo WB_URL_COMMAND?>" target="_blank">
    <input type="hidden" name="c" value="GerenciarEmail">
    <input type="hidden" name="m" value="enviarEmailLoginConvidado">
    <input type="hidden" name="cod_convidado" value="">
</form>
</div></div></div</div></div>
<SCRIPT LANGUAGE="JavaScript1.2" src="resources/js/avaliacao-trabalhos.js"></SCRIPT>
<script language="JavaScript" type="text/javascript">
    function executarAcao(cod){
//        alert(cod+'1')
        return false;
        document.formAcao.cod_convidado.value=cod;
        document.formAcao.submit();
    }
    function enviarFormulario(form) {
        var formData = form.serialize();

        $.ajax({
            type: "POST",
            url: form.attr("action"),
            data: formData,
            dataType: "json",
            async: false,
            cache: false,
            success: function(data){
                var elementMsg = '#msgConvidado';
                processJsonElement(data, elementMsg);
            },
            timeout: 4000,
            beforeSend: function() {
                loading('div#carregando', 'show');
            },
            error: function(ajax) {
                // Tratamento de erro
            },
            complete: function() {
                loading('div#carregando', 'hidden');
            }
        });
    }
$(document).ready(function () {
    $(".btn-enviar-login").click(function(event){
        event.preventDefault();
        var form = $(this).closest("form");
        enviarFormulario(form);
    });
});
</script>
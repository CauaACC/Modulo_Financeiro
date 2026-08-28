<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/login-cabecalho.php");
exit();
//require_once(dirname(__FILE__)."/../../view/admin/login-cadastro-busca.php");
require_once(dirname(__FILE__)."/../../view/admin/convidados-cadastro-busca.php");

$debug = false;

$COD_USUARIO   = $fws_['cod_usuario']? $fws_['cod_usuario'] : $fwsGet_['cod_usuario'];

// if($COD_USUARIO){
//     require_once(dirname(__FILE__)."/../../view/admin/login-cadastro-listar.php");    
// }

if ((isset($COD_USUARIO)) AND ($COD_USUARIO != "")) {

    $objUE = new UsuarioEvento();
    $objUE->setCodEvento(COD_EVENTO);
    $objUE->setCodUsuario($COD_USUARIO);
    $objUE->getUsuarioEventoById($debug);

    #Verificando se houve retorno de algum registro
    if ($objUE->getStatus() != '') {

        $nome_convidado = $objUE->getNomeConvidado()? $objUE->getNomeConvidado() : 'Dados Pessoais nao Cadastrados';
        
        echo " 
        <hr size='1' width='100%'>
        &nbsp;&nbsp;&nbsp;&nbsp;
        <a href='index.php?content=view/admin/login-cadastro-incluir.php'><img src='resources/image/txt-incluir.gif' border='0'></a>
        &nbsp;&nbsp;&nbsp;&nbsp;
        <a href='index.php?content=view/admin/login-cadastro-alterar.php&COD_USUARIO={$objUE->getCodUsuario()}'><img src='resources/image/txt-alterar.gif' border='0'></a>
        &nbsp;&nbsp;&nbsp;&nbsp;
        <a href='view/admin/login-cadastro-excluir-grava.php?COD_USUARIO={$objUE->getCodUsuario()}&COD_EVENTO={COD_EVENTO}&acao=Excluir'><img src='resources/image/txt-excluir.gif' border='0'></a>
        <hr size='1' width='100%'>

        <table border='0' width='100%'>

        <tr><td colspan='2'>&nbsp;<b> Código do Login: 
        {$objUE->getCodUsuario()}</td></tr>";
        ?>
        <tr><td colspan='2'>&nbsp;<b> Nome Convidado:
        <?php if ($objUE->getCodConvidado()!='') { ?>
            <a href='index.php?content=view/admin/convidados-cadastro-ok.php&cod_convidado=<?php echo $objUE->getCodConvidado() ?>'>
            <?php echo $nome_convidado ?></b></a>
        <?php }else{ ?>
            <a href='index.php?content=view/admin/convidados-cadastro-incluir.php&cod_usuario=<?php echo $objUE->getCodUsuario() ?>'>
            <?php echo $nome_convidado ?></b> (clique aqui para completar cadastro)</a>
        <?php } ?>             
        </td></tr>
        <?php  
        echo " 
        <tr><td width='110'>&nbsp;</td><td width='460'>&nbsp;</td></tr>
        <tr><td colspan='2'>
        <TABLE WIDTH='500' BORDER='0' CELLSPACING='0' CELLPADDING='4'>
           <TR><TD VALIGN='top' width='30'>Login:     </TD><TD width='470'> <b>{$objUE->getLogin()} </TD></TR>
        </TABLE>";

        echo "<hr size='1' width='100%'>";

        echo "&nbsp;&nbsp;&nbsp;&nbsp;
        <a href='index.php?content=view/admin/convidados-cadastro-ok.php&COD_USUARIO={$objUE->getCodUsuario()}' target='_blank'>
        Dados pessoais</a>";

        echo "&nbsp;&nbsp;&nbsp;&nbsp;
        <a href='index.php?content=view/admin/ligacao-usuarios-grupos-listagem.php&COD_USUARIO={$objUE->getCodUsuario()}' target='_blank'>
        Listar grupos</a>";

        if ($objUE->getLogin() != '') {
            echo "&nbsp;&nbsp;&nbsp;&nbsp;
              <a href='index.php?content=view/admin/convidados-cadastro-ok-listagem-grupos.php&COD_CONVIDADO={$objUE->getCodConvidado()}'>
            Adicionar grupos</a>";
        }

        echo "    
        &nbsp;&nbsp;&nbsp;&nbsp;
        <a href='index.php?content=view/admin/convidados-cadastro-ok-listagem-areas-tematicas.php&COD_CONVIDADO={$objUE->getCodConvidado()}' target='_blank'>
            Adicionar Eixos</a>

        </td></tr></table>
        <br>";
    } else {

        echo "
              <hr size=1 width=100%>
              &nbsp;&nbsp;&nbsp;&nbsp;
              <a href='index.php?content=view/admin/login-cadastro-incluir.php'><img src='resources/image/txt-incluir.gif' border='0'></a>
              &nbsp;&nbsp;&nbsp;&nbsp;
                
              <!--
              <a href=index.php?content=view/admin/login-cadastro-alterar.php&COD_USUARIO=" . $objUE->getCodUsuario() . ">Alterar</a>
              &nbsp;&nbsp;&nbsp;&nbsp;
              <a href='view/admin/login-cadastro-grava-exclusao.php?COD_USUARIO=" . $objUE->getCodUsuario() . "&acao=Excluir'>Excluir</a>
              -->
                <hr size=1 width=100%>
                <table border=0 width=570>
                  <tr><td>
                    &nbsp;&nbsp;&nbsp;&nbsp;
                    O Login Número: " . $objUE->getCodUsuario() . ", não Existe ou foi Excluído!
                  </td></tr>
                </table>
                <br>
            ";
    }
} else {

    echo "
              <hr size=1 width=100%>
              &nbsp;&nbsp;&nbsp;&nbsp;
              <a href='index.php?content=view/admin/login-cadastro-incluir.php'><img src='resources/image/txt-incluir.gif' border='0'></a>
              &nbsp;&nbsp;&nbsp;&nbsp;
                <hr size=1 width=100%>
                <table border=0 width=570>
                  <tr><td>
                    &nbsp;&nbsp;&nbsp;&nbsp;
                    Clique no Botão Incluir para Inserir um Login!
                  </td></tr>
                </table>
                <br>
            ";
}
?>
<hr size='1' width='100%'>
<a class='link' href='javascript:history.go(-1)'><b>voltar<b></a>
<form action='index.php' method='get' name='buscarGruposConvidado' id='buscarGruposConvidado'>
    <input type='hidden' name='content' value='view/admin/convidados-cadastro-ok-listagem-grupos.php'>
    <input type='hidden' name='COD_CONVIDADO' id='cod_convidado' value=''>
</form>
</body>
</html>
</div></div></div</div></div>
<script language="JavaScript" type="text/javascript">
    function executarAcao(cod){
        document.formAcao.cod_convidado.value=cod;
        document.formAcao.submit();
    }
</script>
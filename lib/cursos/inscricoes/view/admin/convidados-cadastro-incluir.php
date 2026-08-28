<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/convidados-cabecalho.php");
require_once(dirname(__FILE__)."/../../view/admin/convidados-cadastro-busca.php");

try {
    $objUsuario = new Usuario();
    $objUsuario->setCodUsuario($fwsGet_['cod_usuario']);
    $objUsuario->setCodEvento(COD_EVENTO);
    $objUsuario->getUsuarioById();
    if($objUsuario->getStatus()!='ATIVADA'){
        throw new Exception("Usuário não encontrado ou login não cadastrado.");
    }
    ?>
    <hr size='1' width='100%'>
    <span style class='texto'> <B> Ficha de Cadastro de Convidados</B></span><br>
    <span style class='textop'> <B> * Campos Obrigatórios</B></span>
    <span style class='texto'>

    <FORM method="POST" name="form" action="view/admin/convidados-cadastro-incluir-grava.php" onsubmit="return criticar()">

    <fieldset width=570><legend> Dados dos Convidado </legend>
    <?php
    FWA_Mostrar_Mensagem($_SESSION['MSG']);
    ?>
    <?php
    include_once("convidados-cadastro-campos.php");
    ?>

    </fieldset>

    <table border=0 width=565>
    <tr><td colspan=2 align=center>
          <input type='submit' value='Cadastrar'> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='reset' value='Limpar'>
    </td></tr>
    </table>

    </FORM>
<?php
} catch (Exception $exc) {
    echo $exc->getMessage();
}
?>

<hr size='1' width='100%'>
<a class='link' href='javascript:history.go(-1)'><b>voltar<b></a>
</body>
</html>
</div></div></div</div></div>
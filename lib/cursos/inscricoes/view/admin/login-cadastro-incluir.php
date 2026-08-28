<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/login-cabecalho.php");
require_once(dirname(__FILE__)."/../../view/admin/login-cadastro-busca.php");

?>

<hr size='1' width='100%'>
<span style class='texto'> <B> Ficha de Cadastro de Login</B><br>
<span style class='textop'> <B> * Campos Obrigatórios</B></span>
<span style class='texto'>

<FORM method="POST" name="form" action="view/admin/login-cadastro-incluir-grava.php" onsubmit="return criticar()">
<?php
//if($fws_['cod_convidado']!=''){
//    $objConvidado= new Convidado();
//    $objConvidado->setCodConvidado($fws_['cod_convidado']);
//    $objConvidado->getConvidadoById();
//    $login = $objConvidado->getEMail();
//    if($objConvidado->getCpf()!='' and strlen($objConvidado->getCpf())>=8){
//        $senha = substr($objConvidado->getCpf(), 0, 8);
//    }else{
        $senha = geraSenha();
//    }
//}
?>
  <fieldset width=570><legend> Dados dos Login</legend>
    <?php include_once("login-cadastro-campos.php");?>
  </fieldset>
  <br>
  <table border=0 width=565>
  <tr><td colspan=2 align=center>
        <input type='submit' value='Cadastrar'>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <input type='reset' value='Limpar'>
  </td></tr>
  </table>

</FORM>

<hr size='1' width='100%'>
<a class='link' href='javascript:history.go(-1)'><b>voltar<b></a>
</body>
</html>
</div></div></div</div></div>
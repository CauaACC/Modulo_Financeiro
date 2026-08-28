<?php
require_once(dirname(__FILE__)."/../../include/config.php");
?>
<a href='index.php?content=view/admin/consulta-dados-congresso-listagem-inscritos.php'>
Listar Todos</a>
<br>
Localizar Inscrição:<span style class='textop'>
<table>
<tr>
    <td><span style class='textop'>
    &nbsp;Por nome ou parte do nome do cadastrado:
    <form action='index.php' method='get'>
      <input type='hidden' name='content' value='view/admin/inscritos-cadastro-ok.php'>
      &nbsp;<input type='text' name='nome_inscrito' value=''>
    <input type='submit' value='ok'></form>
    </td>
    <td><span style class='textop'>
        &nbsp;Por e-mail:
        <form action='index.php' method='get'>
          <input type='hidden' name='content' value='view/admin/inscritos-cadastro-ok.php'>
          &nbsp;<input type='text' name='e_mail' value=''>
        <input type='submit' value='ok'></form>

    </td>
    <td><span style class='textop'>
        &nbsp;Por fone contato:
        <form action='index.php' method='get'>
          <input type='hidden' name='content' value='view/admin/inscritos-cadastro-ok.php'>
          &nbsp;<input type='text' name='fone_contato' value=''>
        <input type='submit' value='ok'></form>

    </td>
</tr>
<tr>
<td><span style class='textop'>
    &nbsp;Por código de inscrição:
    <form action='index.php' method='get'>
      <input type='hidden' name='content' value='view/admin/inscritos-cadastro-ok.php'>
      &nbsp;<input type='text' name='cod_inscrito' value=''>
    <input type='submit' value='ok'></form>

</td>
<td><span style class='textop'>
    &nbsp;Por cpf do inscrito:
    <form action='index.php' method='get'>
      <input type='hidden' name='content' value='view/admin/inscritos-cadastro-ok.php'>
      &nbsp;<input type='text' name='cpf' value=''>
    <input type='submit' value='ok'></form>
</td>
<td><span style class='textop'>
    &nbsp;Por código do trabalho:
    <form action='index.php' method='get'>
      <input type='hidden' name='content' value='view/admin/inscritos-cadastro-localiza-inscrito-por-trabalho.php'>
      &nbsp;<input type='text' name='cod_trabalho' value=''>
    <input type='submit' value='ok'></form>
</td></tr></table>    

</form>

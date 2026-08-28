<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
<meta name="GENERATOR" content="Microsoft FrontPage 4.0">
<meta name="ProgId" content="FrontPage.Editor.Document">
<title>Resumos</title>
</head>

<body>

      <blockquote>
      <table width='520' border='1' bgcolor='FFFFFF' CELLSPACING='0' CELLPADDING='10'><tr><td>
        <FONT FACE='verdana' SIZE='3'>
          <B>Inscrição no Congresso e Submissão de Trabalhos.</B>
        </FONT>
        <br><br>

<font face="Verdana" size="2">Ocorreu um erro durante o processo de sua inscrição.<br>
</font><br>
<font face="Verdana" size="2" >
<br>Mensagens de erro:<br><br>
<?php
#require_once(dirname(__FILE__)."/../../include/inscricao_request_variaveis.php");

  #echo "--".$_SESSION['MSG']."--";

  $MSG   = addslashesopctec($fwsGet_['MSG']);
  $MSG_Imprimir = $MSG;

  $loc   = "/--/";
  $subst = "<br>";
  $MSG_Imprimir = preg_replace($loc,$subst,$MSG_Imprimir);

  $loc   = "/-/";
  $subst = " ";
  $MSG_Imprimir = preg_replace($loc,$subst,$MSG_Imprimir);

  echo $MSG_Imprimir;

?>


</font><br>
<blockquote>
    <a href="index.php?content=view/inscricao/inscricao_congresso_confirmar.php"><font face="Verdana" size="3" color="black">Clique aqui retornar ao processo de inscrição no Congresso.</font></a>
    <br><br>
    <a href="mailto:contato.opctec@gmail.com?Subject=[<?php echo COD_EVENTO ?>]--<?php echo $MSG?>"><font face="Verdana" size="3" color="black">Se o erro persistir entre em contato com adminstrador do sistema!</font></a><font size="2" face="Verdana">.</font>

</blockquote>

</td></tr></table></blockquote>
</body>

</html>

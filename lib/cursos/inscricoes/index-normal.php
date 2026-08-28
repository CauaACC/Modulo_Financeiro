<?php
require_once(dirname(__FILE__)."/../inscricoes/include/config.php");
?>
<html>
<head>
    <title><?php echo ABREV_EVENTO."-".NOME_EVENTO; ?></title>
    <script language="JavaScript1.2" src="resources/js/default.js"></script>
    <script language="JavaScript1.2" src="resources/js/abrelink.js"></script>
    <link rel="stylesheet" type="text/css" href="resources/css/redeunida.css">
    <!--<LINK href="../css/modelo.css" type=text/css rel=stylesheet>-->
    <style type=text/css>

    </style>
</head>

<BODY bgColor=#a3b0bc>
<TABLE height="100%" cellSpacing=0 cellPadding=0 width=765 align=center border=0>
  <TBODY>
  <TR>
    <TD width=14 background="resources/image/effect.gif">&nbsp;</TD>
      <TD vAlign=top width=737 bgColor=B5C2CB>
        <TABLE height=80 cellSpacing=0 cellPadding=0 width=737 border=0>
        <TBODY>
        <TR>
              <TD bgcolor="F2F4F6">
                  <h3><?php echo ABREV_EVENTO."-".NOME_EVENTO; ?></h3>
                    <?php
                    #include "../destaque.php";
                    ?>
                    <?php
                    include "menu.php";
                    ?>
                <div align="right"></div></TD>
            </TR></TBODY></TABLE>

        <TABLE height=232 cellSpacing=0 cellPadding=0 width=737 border=0>
          <TBODY>
        <TR>
              <TD  height=41 valign="top" bgcolor="F2F4F6"> <table width="100%" border="0" align="center">
                  <tr>
                    <td>

<!--- Inicio Corpo Conteudo --->

  <?
require 'miolo.php';

  ?>

<!-- Fim Corpo Conteudo --->


                    </td>
                  </tr>
                </table>

              </TD>
            </TR>
            <TR>
              <TD height=15 valign="top" bgcolor="#F2F4F6">&nbsp;</TD>
            </TR>
        <TR>
              <TD background="../images/fill_envolving_down.gif"  height=41>
    <?
    include "rodape.php";
    ?>
      </TD>
            </TR></TBODY></TABLE>
        <TABLE height=28 cellSpacing=0 cellPadding=0 width="737%" border=0>

        </TABLE>
      </TD>
    <TD width=14 background="resources/image/effect.gif">&nbsp;</TD></TR></TBODY></TABLE></BODY></HTML>

<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/ligacao-usuarios-grupos-cabecalho.php");
exit();
#require_once(dirname(__FILE__)."/../../view/admin/ligacao-usuarios-grupos-busca.php");

#----------------------------------------------------------------------
#Conectar no Banco
$FWS_Debug = false;
$Conexao_db = FWA_Conexao("OpcTec");

#----------------------------------------------------------------------
#Mensagens de ERRO

$MSG   = $_SESSION['MSG'];

$MSG_Imprimir = $MSG;
$loc   = "/--/";
$subst = "<br>";
$MSG_Imprimir = preg_replace($loc,$subst,$MSG_Imprimir);
$loc   = "/-/";
$subst = " ";
$MSG_Imprimir = preg_replace($loc,$subst,$MSG_Imprimir);

If($MSG_Imprimir){
  echo $MSG_Imprimir."<hr size='1' width='100%'>";
}
#----------------------------------------------------------------------
#
require_once(dirname(__FILE__)."/../../view/admin/ligacao-usuarios-grupos-incluir.php");

#----------------------------------------------------------------------
#Lista usuarios e grupos associados
    
    #Definindo comando SQL
    $var_sql = "
                 SELECT 
                  sg.cod_grupo
                 ,sg.nome_grupo
                 ,su.cod_usuario
                 ,su.login
                 ,tc.cod_convidado
                 ,tc.nome_convidado
                 ,su.cod_inscrito
                FROM sis_usuarios su
                INNER JOIN sis_ligacao_usuarios_eventos le ON su.cod_usuario = le.cod_usuario
                LEFT JOIN tab_convidados tc ON su.cod_usuario = tc.cod_usuario
                LEFT JOIN (
                    sis_ligacao_usuarios_grupos tl
                    INNER JOIN sis_grupos sg ON tl.cod_grupo = sg.cod_grupo
                ) ON tl.cod_usuario = le.cod_usuario AND tl.cod_evento=le.cod_evento
                 WHERE
                      (le.cod_evento =".COD_EVENTO.")
                   and(sg.cod_grupo<>999)
                 ORDER BY sg.nome_grupo, su.login
               ";

//    echo $var_sql;
    
    $RS=FWA_Executa_SQL($FWS_Debug,$Conexao_db,$var_sql); //Consulta login existe   

    #Verificando se houve retorno de algum registro
    If (!$RS->EOF) {
      echo "<table border='0' width='100%' colspan='0' cellspacing='0'>";
      While (!$RS->EOF) {

        $cod_grupo  = $RS->fields[0];
        $nome_grupo = $RS->fields[1];
        $cod_usuario      = $RS->fields[2];
        $login     = $RS->fields[3];
        $cod_convidado  = $RS->fields[4];
        $nome_convidado = $RS->fields[5];
        $cod_inscrito_su = $RS->fields[6];
//        $cod_inscrito_i  = $RS->fields[7];

        If(@$nome_grupo_ant!=$nome_grupo){
            echo "<tr><td>Total Cadastrados: ".numberPad($total, 2)."</td></tr>";
            echo "<tr v-show=\"(codGrupo=='".$cod_grupo."' || codGrupo=='-1')\"><td colspan='3'><br><hr size='1' width='100%'>"
            . "<span style='font-size=15px;color:#337ab7;'>Grupo - ".$cod_grupo.": ".$nome_grupo." </span><br>";
            $total = 0;
            $sqlSistemaGrupo = "select ss.*,dgs.cod_direito from sis_sistemas ss "
            . "inner join sis_direitos_grupos_sistemas dgs on dgs.cod_sistema=ss.cod_sistema "
            . "where dgs.cod_evento=".COD_EVENTO." and dgs.cod_grupo = ".$cod_grupo;
            $rsSisGrupo=FWA_Executa_SQL($FWS_Debug,$Conexao_db,$sqlSistemaGrupo); //Consulta login existe   
            if(!$rsSisGrupo->EOF){
//                echo "Sistemas associados:<br>";
                While (!$rsSisGrupo->EOF) {
                    echo $rsSisGrupo->fields['cod_sistema']." - ".$rsSisGrupo->fields['nome_sistema']."<br>";
                    $rsSisGrupo->MoveNext();
                }
//                echo "<br>";
            }
//            echo "<b>Logins:</b>&nbsp;";
            echo "</td></tr>";
        }
        $total++;
        $nome_grupo_ant=$nome_grupo;
        
        echo "<tr valign='middle' v-show=\"(codGrupo=='".$cod_grupo."' || codGrupo=='-1')\"><td width=150px>";
//        echo numberPad($total, 2)." - ";
        echo "<a href='index.php?content=view/admin/ligacao-usuarios-grupos-listagem.php&COD_USUARIO=".$cod_usuario."'>
            <img src='resources/image/fig-incluir.gif' alt='Add Novo Grupo' title='Add Novo Grupo' border='0'></a>"; 
        
        echo "<a href='index.php?content=view/admin/convidados-cadastro-ok.php&COD_CONVIDADO=".$cod_convidado."'>
            <img src='resources/image/fig-alterar.gif' alt='Editar' title='Abrir Convidado' border='0'></a>";
        
        echo "<a href='view/admin/ligacao-usuarios-grupos-excluir-grava.php?acao=Excluir&cod_usuario=".$cod_usuario."&cod_evento=".COD_EVENTO."&cod_grupo=".$cod_grupo."'>
        <img src='resources/image/fig-excluir.gif' title='Excluir Ligação' border='0' style='margin-right:10px;'></a>";
        
        echo "<a title='Enviar senha para e-mail' target='_blank' onclick=\"executarAcao('{$cod_convidado}');\" href=\"javascript:void(0);\">
        <img src='resources/image/email-2.gif' border='0'></a>";
        
        echo "</td><td width='150px' >";
            if($cod_usuario==$fws_['cod_usuario']){
                echo "<b>".$login."</b>";
            }  else {
                echo $login;
            }   
        echo "</td>";
        echo "<td>";
        echo textoMaiusculoMinusculo($nome_convidado);
        if($cod_inscrito_su==''){
            echo " - <font color=red>Código de inscrito não informado</font>";
        }
        
        echo "</td>";
        
        echo "</tr>";

        #Movendo para o proximo registro
        $RS->MoveNext();
      }
      echo "</table><br>";
    }
?>
<hr size='1' width='100%'>
<a class='link' href='javascript:history.go(-1);'><b>voltar<b></a>
</body>
</html>
</div></div></div></div></div>
<form name="formAcao" method="post" action="<?php echo WB_URL_COMMAND?>" target="_blank">
    <input type="hidden" name="c" value="GerenciarEmail">
    <input type="hidden" name="m" value="enviarEmailLoginConvidado">
    <input type="hidden" value="" name="cod_convidado">
</form>
<script>
    function executarAcao(cod){
        document.formAcao.cod_convidado.value=cod;
        document.formAcao.submit();
    }
</script>
<script>
    var app = new Vue({
      el: '#container',
      data: {
        codGrupo: '-1'
      },
      computed:{
        
      }
    })
</script>
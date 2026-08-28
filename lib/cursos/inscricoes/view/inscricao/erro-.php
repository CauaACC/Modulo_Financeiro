<?php
require_once(dirname(__FILE__)."/../inscricoes/include/config.php");
#Fazendo conexão com o banco
//$Conexao_db = FWA_Conexao("OpcTec");
require_once 'inscricao_congresso_tit_area_inscrito.php';
?>
<div class="panel-group">
    <div class="panel panel-default">
        <div class="panel-heading"><h4>Aviso</h4></div>
        <div class="panel-body">
            <blockquote>
                Ocorreu um erro durante o processo de sua inscrição.
                <br>Mensagens de erro:<br>
                Se você está lendo essa mensagem, provavelmente não está conseguindo anexar algum documento ou vídeo.
                <br>Neste caso é possível que seu documento esta um pouco acima do tamanho.
                <br>Neste caso, envie o documento anexado para 
                <a href="mailto:contato.opctec@gmali.com?Subject=[<?php echo ABREV_EVENTO?>]" target="_blank">contato.opctec@gmail.com</a>
                <br>Informe seu nome completo, código da inscrição, código trabalho e titulo do mesmo
                <br>Obrigado!
                <?php
                //$MSG = $fwsGet_['MSG'];
                //FWA_Mostrar_Mensagem($MSG);
                ?>
                <br>
                <blockquote>
                    <a href="index.php?content=view/inscricao/inscricao_congresso_confirmar.php"><font face="Verdana" size="3" color="black">Clique aqui retornar ao processo de inscrição no Congresso.</font></a>
                    <br><br>
                    <a href="mailto:contato.opctec@gmali.com?Subject=[<?php echo ABREV_EVENTO?>]<a href="mailto:contato.opctec@gmali.com?Subject=[<?php echo ABREV_EVENTO?>--<?php echo  $MSG ?>"><font face="Verdana" size="3" color="black">Se o erro persistir entre em contato com adminstrador do sistema!</font></a><font size="2" face="Verdana">.</font>
                </blockquote>
            </blockquote>
        </div>
    </div>
</div>

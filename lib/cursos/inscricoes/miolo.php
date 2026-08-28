<?php
    If(FWA_Arquivo_Existe($content,0)){
//        echo '';
        require $content;
        //require 'view/admin/default-teste.php';
    }Else{
        ?>
<h3>Área do Inscrito</h3>  
<div class="panel-group">
    <div class="panel panel-default">
        <div class="panel-heading"><h4>Aviso</h4></div>
        <div class="panel-body">
            <blockquote>
                  <?php
                  echo "<h3>Atenção</h3><p>Arquivo ";
                  echo $content==''? 'não informado' : 'não encontrado';
                  echo "<p>Provavelmente você está tentando acessar um link quebrado.
                      <p>Use o menu ao lado para encontrar a informação de seu interesse,
                      ou entre em contato conosco utilizando o email no rodape desta página.\n";
                  
//                Ocorreu um erro durante o processo de sua inscrição.
//                <br>Mensagens de erro:<br>
//                Se você está lendo essa mensagem, provavelmente não está conseguindo anexar algum documento ou vídeo.
//                <br>Neste caso, envie o documento a ser anexado para
                  ?>
                <a href="mailto:contato.opctec@gmali.com?Subject=[<?php echo ABREV_EVENTO?>]" target="_blank">contato.opctec@gmail.com</a>
                <br>Informe seu nome completo, código da inscrição
                <br>Obrigado!
                
                <br>
                <blockquote>
                    <a href="index.php?content=view/inscricao/inscricao_congresso_ok.php"><font face="Verdana" size="3" color="black">Clique aqui retornar ao processo de inscrição no Congresso.</font></a>
                    <br><br>
                    <a href="mailto:contato.opctec@gmali.com?Subject=[<?php echo ABREV_EVENTO?>]<a href="mailto:contato.opctec@gmali.com?Subject=[<?php echo ABREV_EVENTO?>--<?php echo  $MSG ?>"><font face="Verdana" size="3" color="black">Se o erro persistir entre em contato com adminstrador do sistema!</font></a><font size="2" face="Verdana">.</font>
                </blockquote>
            </blockquote>
        </div>
    </div>
</div><?php
    }
    
    If(substr($content,0,6)=="view/admin/"){
      echo "</td></tr></table>";
    }    
//$_SESSION['MSG'] = '';
//$_SESSION['MSG'] = "";
<?php
require_once(dirname(__FILE__)."/../../include/config.php");
#require_once(dirname(__FILE__)."/../../include/inscricao_request_variaveis.php");
#Fazendo conexão com o banco
$Conexao_db = FWA_Conexao("OpcTec");
$debug = false;
?>
<style>
.conteudo{
  background-color:#f1f1f1;
  width:100%;
  height:100%;
  max-width:100%;
  max-height:100%;
}
iframe[src*="youtube"] {
  width:100%;
  height:100%;
}
</style>
<h3>Área de Apresentação</h3>  
<div class="panel-group">
    <?php
    try {
        
        if($fws_['msg']){
            FWA_Mostrar_Mensagem($fws_['msg']);
        }
        require_once 'inscricao_congresso_cabecalho.php';
        
        try {
            $objInscrito = new Inscrito();
            $objInscrito->setCodInscrito($_SESSION['_cod_inscrito']);
            $objInscrito->getInscritoById($debug);
            
            $objIsento = new Isento();
            $objIsento->setCpf($objInscrito->getCpf());
            $objIsento->getIsentoByCpf();
            
            $objInscrito->carregarOjbPerfil($debug);
            $objPerfil = $objInscrito->getObjPerfil();
        ?>
       
        <?php
//        if($OP_EVENTO_ONLINE_YOUTUBE_LIBERADO){?>
        <div class="panel panel-default">
            <div class="panel-heading"><b>Apresentação Virtual</b></div>
            <div class="panel-body">

                <div class="video-responsive">
                    <iframe width="853" height="480" src="https://www.youtube.com/embed/E73TB66KOnk" 
                            title="2ª Dia da 7º Mostra" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen>
                        
                    </iframe>

                    <br>&nbsp;    
                </div>
                <br>

            </div>
        </div>
        <?php
        if($OP_EVENTO_AVISOS){?>
        <div class="panel panel-default">
            <div class="panel-heading"><b>Avisos</b></div>
            <div class="panel-body"><br>
                <ul>
                    <?php
                    require_once('inscricao_congresso_avisos.php');
                    ?>
                </ul>
            </div>
        </div>
        <?php } ?>
        <?php
        } catch (Exception $exc) {
            echo $exc->getMessage();
        }
    } catch (Exception $exc) {
        $_SESSION['MSG']=$exc->getMessage();
        echo "<a href='" . $url . "'>" . FWA_Mostrar_Mensagem($exc->getMessage()) . "</a>";
        if (!$debug) {
            echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $url . "\"></HTML>";
        }
    }
    ?>
    <div class="panel panel-default">
        <div class="panel-body">
            <br>
            <br>
            <br>
            <br>
            <br>
            <br>
            <br>
        </div>
    </div>
</div>

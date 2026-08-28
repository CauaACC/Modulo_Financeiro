<?php
require_once(dirname(__FILE__)."/../../include/config.php");
#require_once(dirname(__FILE__)."/../../include/inscricao_request_variaveis.php");
#Fazendo conexão com o banco
$Conexao_db = FWA_Conexao("OpcTec");
$debug = false;
?>
<h3>Área do Inscrito</h3>  
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
            $objInscrito->setCodEvento($cod_evento);
            $objInscrito->getInscritoById($debug);
            
            $objIsento = new Isento();
            $objIsento->setCpf($objInscrito->getCpf());
            $objIsento->setCodEvento($objInscrito->getCodEvento());
            $objIsento->getIsentoByCpf();
            
            $objInscrito->carregarOjbPerfil($debug);
            $objPerfil = $objInscrito->getObjPerfil();
        ?>
        <?php
        if($OP_EMISSAO_CERTIFICADOS){?>
        <div class="panel panel-default">
            <div class="panel-heading"><b>Emissão de Certificados</b></div>
            <div class="panel-body">
                <?php
                require_once('inscricao_consulta_certificados.php');
                ?>    
            </div>
        </div>
        <?php } ?>
        <?php
          } catch (Exception $exc) {
            echo $exc->getMessage();
        }
    } catch (Exception $exc) {
        $_SESSION['MSG'] = $exc->getMessage();
        echo "<a href='" . $url."'>" . FWA_Mostrar_Mensagem($exc->getMessage()) . "</a>";
        if (!$debug) {
            echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $url . "\"></HTML>";
        }
    }
    ?>
    <div class="panel panel-default">
        <div class="panel-body">
            <br>
        </div>
    </div>
</div>

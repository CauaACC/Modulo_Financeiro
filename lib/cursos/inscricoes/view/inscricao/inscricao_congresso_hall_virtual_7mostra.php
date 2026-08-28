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
            $objInscrito->getInscritoById($debug);
            
            $objIsento = new Isento();
            $objIsento->setCpf($objInscrito->getCpf());
            $objIsento->getIsentoByCpf();
            
            $objInscrito->carregarOjbPerfil($debug);
            $objPerfil = $objInscrito->getObjPerfil();
        ?>
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
//        if($OP_EVENTO_ONLINE_YOUTUBE_LIBERADO){?>
            <div class="panel panel-default">
                <div class="panel-heading"><b>Conferências, Sessões e Cerimoniais</b></div>
                <div class="panel-body">
                    <div class='row' style='margin-bottom:5px;'>
                        <div class='col-sm-12' style='padding:10px;'>
                            1º Dia - 09 de Julho de 2021 (Sexta Feira)
                            <br>Período da manhã
                        </div>
                    </div>
                    <div class='row'>
                        <div class='col-sm-12' >
                            
                            <ul style="list-style-type:none;padding:0px;">
                                <li>
                                    <a style="width: 100%;" href="https://www.youtube.com/watch?v=Tqf7JRYZtB8&t=6s" target="_blank" class="btn btn-primary" role="button"
                                       class="button">Sessão de Abertura - 08h30 às 09h00
                                        <br>Membros do Inesco e Demais autoridades
                                    </a><br>&nbsp;
                                </li>
                                <li>
                                    <a style="width: 100%;" href="https://www.youtube.com/playlist?list=PLe-1YNwVuMRqJvLJW_yt4CtVmg8_MEFL7" target="_blank"  class="btn btn-primary" role="button"
                                       class="button">Conferência de Abertura  - 09h00 às 10h00<br>
                                        com a Dr. Nízia Trindade
                                        <br>Presidente da FIOCRUZ
                                    </a><br>&nbsp;
                                </li>
                                <li>
                                    <a style="width: 100%;" href="https://www.youtube.com/playlist?list=PLe-1YNwVuMRqJvLJW_yt4CtVmg8_MEFL7" target="_blank"  class="btn btn-primary" role="button"
                                       class="button">Conferência - 11h00 às 12h00
                                        <br>com Dr. Marcelo Bello
                                        <br>diretor do Hospital do Câncer 3, INCA, Rio de Janeiro
                                        <br>"As políticas públicas contra o câncer durante e após a pandemia"
                                    </a><br>&nbsp;
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class='row' style='margin-bottom:5px;'>
                        <div class='col-sm-12' style='padding:10px;'>
                            1º Dia - 09 de Julho de 2021 (Sexta Feira)
                            <br>Período da tarde
                        </div>
                    </div>
                    <div class='row'>
                        <div class='col-sm-12' >
                            <ul style="list-style-type:none;padding:0px;">
                                <li>
                                    <a style="width: 100%;" href="https://www.youtube.com/playlist?list=PLe-1YNwVuMRpZlC6CBT_vANw6LMh5WY1k" target="_blank"  class="btn btn-primary" role="button"
                                       class="button">Sessão - 18h00 as 19h00
                                        <br>Assembléia Geral Ordinária do Inesco</a><br>&nbsp;
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class='row' style='margin-bottom:5px;'>
                        <div class='col-sm-12'>
                            2º Dia - 10 de Julho de 2021 (Sábado)
                            <br>Período da manhã
                        </div>
                    </div>
                    <div class='row'>
                        <div class='col-sm-12' >
                            <ul style="list-style-type:none;padding:0px;">
                                <li>
                                <a style="width: 100%;" href="https://youtu.be/uw2TLwrp5gQ" target="_blank"  class="btn btn-primary" role="button"
                                class="button">Encontro - 09h00 as 12h00
                                <br>II Encontro dos Profissionais de Educação Física</a><br>&nbsp;
                                </il>
                            </ul>
                        </div>
                    </div>
                    <div class='row' style='margin-bottom:5px;'>
                        <div class='col-sm-12'>
                            2º Dia - 10 de Julho de 2021 (Sábado)
                            <br>Período da tarde
                        </div>
                    </div>                    
                    <div class='row'>
                        <div class='col-sm-12' >
                            <ul style="list-style-type:none;padding:0px;">
                                <li>
                                    <a style="width: 100%;" href="https://youtu.be/ZEh5uM60K-4?list=PLe-1YNwVuMRqJvLJW_yt4CtVmg8_MEFL7" target="_blank"  class="btn btn-primary" role="button"
                                       class="button">Divulgação - 16h30
                                         <br>Resultado do 6º Prêmio- Inova Sáude Paraná</a><br>&nbsp;
                                </li>

                                <li>
                                    <a style="width: 100%;" href="https://www.youtube.com/playlist?list=PLe-1YNwVuMRoJZBG1Y3W55VVOYrJDM9dH" target="_blank"  class="btn btn-primary" role="button"
                                       class="button">Conferência - 17h00 as 18h00
                                        <br>com Dr. Gonzalo Vecina Neto
                                        médico, professor da USP e da FGV-SP</a><br>&nbsp;
                                </li>
                                <li>
                                    <a style="width: 100%;" href="https://youtu.be/EQXYIpOv2So?list=PLe-1YNwVuMRqJvLJW_yt4CtVmg8_MEFL7" target="_blank"  class="btn btn-primary" role="button"
                                       class="button">Sessão - 18h00 as 19h00
                                        <br>Sessão de Encerramento do Evento</a><br>&nbsp;
                                </li>
                            </ul>            
                        </div>
                    </div>
                </div>
            </div>
        <?php // } ?>
        <?php
//        if($OP_EVENTO_ONLINE_MOSTRA_LIBERADO){?>
        <div class="panel panel-default">
            <div class="panel-heading"><b>Exposição de Posters</b></div>
            <div class="panel-body"><br>
                Disponível durante todo o evento.
                <br><br>
                <a href='<?php echo URL_HALL?>'>7ª Mostra Paranaense de Pesquisas e Relatos de Experiências em Saúde</a>
                <br>&nbsp;
            </div>
        </div>
        <?php // } ?>
        <?php
        if($fws_['op_selecionar_oficina']){
        if($objPerfil->getVerificarSelecionarAtividade()){
            $OP_PERMITIR_ALTERAR_OFICINA = false;
        ?>
        <div class="panel panel-default">
            <div class="panel-heading"><b>Atividades Selecionadas </b></div>
            <div class="panel-body">
                <?php
                try {
                    $ii = 0;
                    $objInscrito->carregarListaOficinas($debug);
                    $valor_atividade = 0;
                    foreach ($objInscrito->getListaOficinas() as $objL) {
                        /*Lista apenas atividades ativas*/
                        if(($objL->getStatus()=='ATIVADA')and($objL->getTipo()=='Minicurso')){
                            echo "<div class='row' style='margin-top:15px;'>";
                            $ii++;
                            echo "<div class='col-sm-11'>";
                            echo $objL->getTipo()." ".lpad($objL->getOrdem(),2,0)." - ".$objL->getTitulo()."<br>";
                            echo $objL->getDataIni()? $objL->getDataIni()." - ".$objL->getHorarioIni() : "";
//                            if($OP_EVENTO_ONLINE_LINK_MINICURSO_LIBERADO){
//                                if($objL->getLinkAula()!='' and $objL->getLinkAulaPublicar()=='1'){
                                    echo "  <a href='".$objL->getLinkAula()."' "
                                        . "target='_blank' style='margin-left:35px;font-size:18px'>"
                                        . " <i class='fa fa-group'></i> Acessar Sala</a><br>";
//                                }
//                            }
                            echo "</div>";
                            echo "</div>";
                        }
                    }
                } catch (Exception $exc) {
                    echo $exc->getMessage();
                }
                ?>    
            </div>
        </div>
        <?php } } ?>
        <?php
        if($fws_['op_selecionar_evento_paralelo']){
//            if($objPerfil->getVerificarSelecionarAtividade()){  
            ?>
            <div class="panel panel-default">
                <div class="panel-heading"><b>Ativides/Eventos Paralelos</b></div>
                <div class="panel-body">
                    <?php
                    try {
                        $ii_p = 0;
                        $objInscrito->carregarListaOficinas($debug);
                        $valor_atividade = 0;
                        foreach ($objInscrito->getListaOficinas() as $objL) {
                            /*Lista apenas atividades ativas*/
                            if(($objL->getStatus()=='ATIVADA')and($objL->getTipo()=='Evento Paralelo')){
                                echo ($ii_p=='0'? "<br>" : "");
                                $ii_p++;
                                echo "<div class='col-sm-11'>";
                                echo $objL->getTipo()." - ".$objL->getTitulo()."<br>";
                                echo $objL->getDataIni()? $objL->getDataIni()." - ".$objL->getHorarioIni() : "";
    //                            if($OP_EVENTO_ONLINE_LINK_MINICURSO_LIBERADO){
    //                                if($objL->getLinkAula()!='' and $objL->getLinkAulaPublicar()=='1'){
                                        echo "  <a href='".$objL->getLinkAula()."' "
                                            . "target='_blank' style='margin-left:35px;font-size:18px'>"
                                            . " <i class='fa fa-group'></i> Acessar Sala</a><br>";
    //                                }
    //                            }
                                echo "</div>";
                                echo "</div>";
                            }
                        }
                    } catch (Exception $exc) {
                        echo $exc->getMessage();
                    }
                    ?>
                </div>
            </div>
            <?php
//            }
        }?>
        <div class="panel panel-default">
            <div class="panel-heading"><b>Salas com Apresentação de Trabalhos por Eixo Temático</b></div>
            <div class="panel-body">
                <br>Dia 10 de Julho de 2021 das 09h as 12h
                <br>Entrada livre, salas simultaneas, com apresentações no inicio e perguntas ao final: 
                <br>
                <br>
                <?php
                $objAreaTematica = new AreaTematica();
                $objAreaTematica->carregarListaAreaTematica($debug);
                foreach ($objAreaTematica->getListaAreasTematicas() as $objL) {
                    echo "<div class='row' style='margin-bottom:10px;'>";
                    echo "<div class='col-sm-11'>";
                    echo lpad($objL->getOrdem(),2,0)." - ".$objL->getTitulo()."<br>";
                    echo $objL->getDataIni()? $objL->getDataIni()." - ".$objL->getHorarioIni() : "";
//                    if($OP_EVENTO_ONLINE_LINK_AREA_TEMATICA_LIBERADO){
//                        if ($objL->getLinkAula()!=''){
                            echo "  <a href='".$objL->getLinkAula()."' "
                                . "target='_blank' style='margin-left:35px;font-size:18px'>"
                                . " <i class='fa fa-group'></i> Acessar Sala</a><br>";
//                        }
//                    }
                    echo "</div>";
                    echo "</div>";
                }
                ?>
            </div>
        </div>
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

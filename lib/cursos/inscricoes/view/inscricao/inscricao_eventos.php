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
        <div class="panel panel-default">
            <div class="panel-heading"><b>Participações em Outros Eventos ou Cursos</b></div>
            <div class="panel-body">

                <?php
                try {
                    $objInscrito->carregarListaEventsByEmailInscrito($debug);
//                    echo "<br>";
                    echo "<ul>";
                    foreach ($objInscrito->getListaEventos() as $objL) {
                        if ($objL->getVisibilidade()=='PUBLICO' and $objL->getCpf()==$objInscrito->getCpf()){
//                            echo '<li style="margin-top:10px;"><a href="'.$PROTOCOLO.'://'.FWA_Dominio().'/'.$objL->getUrlPrefixo().'/index.php">'.$objL->getTitulo().'</a>';
//                            echo '<li style="margin-top:10px;">'.$objL->getTitulo().'';
                            
                            echo '<li style="margin-top:10px;">'
                            . '<a href="javascript:void(0);" '
                            . 'onclick="carregarParticipacaoEvento('.$objL->getCodEvento().','.$objL->getCodInscrito().');">'
                            . 'Inscrição: '.$objL->getCodInscrito().' - '. $objL->getTitulo().'</a>';
                            
//                            $objLIns = new Inscrito();
//                            $objLIns->setCodInscrito($objL->getCodInscrito());
//                            $objLIns->setCodEvento($objL->getCodEvento());
//                            $objLIns->getInscritoById($debug);
//                            //dd($objLIns);
//                            try {       
//                                GerenciarInscricaoAC::gerarLinksCertificadosEventos($objLIns, $fws_, $debug);
//                            } catch (Exception $exc) {
//                                echo '<ul><li>'.$exc->getMessage().'</li></ul>';
////                                echo '<ul><li>Não Liberado</li></ul>';
//                            }
                            echo "</li>";
                        }
                    }
                    echo "</ul>";
                } catch (Exception $exc) {
                   echo $exc->getMessage();
                }
                ?>
            </div>
        </div>
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

<form name="abrirCertificado" id="abrirCertificado" method="post" action="<?php echo WB_URL_ACTION?>?content=view/certificados/certificado.php" target="_blank">
    <input type="hidden" value="" name="tipoCertificado" id="tipoCertificado">
    <input type="hidden" value="" name="cod_evento"   id="cod_evento">
    <input type="hidden" value="" name="cod_inscrito" id="cod_inscrito">
    <input type="hidden" value="" name="cod_trabalho" id="cod_trabalho">
    <input type="hidden" value="" name="cod_oficina"  id="cod_oficina">
    <input type="hidden" value="" name="cod_perfil"   id="cod_perfil">
</form>
<script language="JavaScript" type="text/javascript">
    function gerarCertificado(tipoCertificado, codEvento, codInscrito){
        $('#tipoCertificado').val(tipoCertificado);
        $('#cod_evento').val(codEvento);
        $('#cod_inscrito').val(codInscrito);
//         abrirCertificado.attr('action', 'https://opctec.net.br/lib/cursos/inscricoes/index-action.php?content=view/certificados/certificado.php');
        $('#abrirCertificado').submit();
    }
    function gerarCertificadoPerfil(tipoCertificado, codEvento, codInscrito, codPerfil){
        $('#tipoCertificado').val(tipoCertificado);
        $('#cod_evento').val(codEvento);
        $('#cod_inscrito').val(codInscrito);
        $('#cod_perfil').val(codPerfil);
        $('#abrirCertificado').submit();
    }
    function gerarCertificadoTrabalho(tipoCertificado, codEvento, codInscrito, codTrabalho){
        $('#tipoCertificado').val(tipoCertificado);
        $('#cod_evento').val(codEvento);
        $('#cod_inscrito').val(codInscrito);
        $('#cod_trabalho').val(codTrabalho);
        $('#abrirCertificado').submit();
    }
    function gerarCertificadoAtividade(tipoCertificado, codEvento, codInscrito, codOficina){
        $('#tipoCertificado').val(tipoCertificado);
        $('#cod_evento').val(codEvento);
        $('#cod_inscrito').val(codInscrito);
        $('#cod_oficina').val(codOficina);
        $('#abrirCertificado').submit();
    }
    function carregarParticipacaoEvento(codEvento, codInscrito){
        if(codInscrito){
            if(confirm('Carregando outro evento. Deseja continuar?')){
                $.ajax({
                    type: "POST",
                    url: '<?php echo WB_URL_COMMAND ?>&t='+Math.random(),
                    data: ({'cod_evento': codEvento,'cod_inscrito': codInscrito, 'c':'GerenciarLogin', 'm':'carregarParticipacao','_submit_check':'1'}),
                    dataType: "json",
                    async: true,
                    cache: false,
                    beforeSend: function() {
                       startLoading();
                    },
                    success: function(data){
                       processJsonSemFade(data);
                       if(data.statusMessage){ 
                            window.open(data.url, '_self');
                       }
                    },
                    complete: function(){
                       stopLoading();
                   }
                });
            }
        }else{
            alert('Código do evento não foi informado!');
        }        
    }
</script> 
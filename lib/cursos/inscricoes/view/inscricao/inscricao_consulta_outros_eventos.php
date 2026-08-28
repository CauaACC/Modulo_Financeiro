<?php
// if($_SERVER['REMOTE_ADDR']=='189.90.70.56'){
    try {
        $objInscrito->carregarListaEventosByCpfInscrito($debug);
        echo "<ul>";
        $listouAlgumEventoInscrito = 0;
        foreach ($objInscrito->getListaEventos() as $objL) {
            if ($objL->getVisibilidade() == 'PUBLICO' and $objL->getCpf() == $objInscrito->getCpf()) {
                /**@todo aqui devemos inserir uma configuração
                 * nos eventos que se relacionam internamente */
                // if ($objL->getCodEvento() == '44' or $objL->getCodEvento() == '50' or $objL->getCodEvento() == '73') {
                    // if (in_array($objL->getCodEvento(), [73])) {
                if ($objL->getCodEvento() != $objEv->getCodEvento()) {
                    $listouAlgumEventoInscrito = 1;
                    echo '<li style="margin-top:10px;">'
                        . '<a href="javascript:void(0);" '
                        . 'onclick="carregarParticipacaoEvento(' . $objL->getCodEvento() . ',' . $objL->getCodInscrito() . ');">'
                        .  $objL->getTitulo() . '</a>';
                    echo "</li>";
                    ?>
                    <form name="abrirCertificado" id="abrirCertificado" method="post" action="<?php echo WB_URL_ACTION ?>?content=view/certificados/certificado.php" target="_blank">
                        <input type="hidden" value="" name="tipoCertificado" id="tipoCertificado">
                        <input type="hidden" value="" name="cod_evento" id="cod_evento">
                        <input type="hidden" value="" name="cod_inscrito" id="cod_inscrito">
                        <input type="hidden" value="" name="cod_trabalho" id="cod_trabalho">
                        <input type="hidden" value="" name="cod_oficina" id="cod_oficina">
                        <input type="hidden" value="" name="cod_perfil" id="cod_perfil">
                    </form>
                    <script language="JavaScript" type="text/javascript">
                        function carregarParticipacaoEvento(codEvento, codInscrito) {
                            if (codInscrito) {
                                if (confirm('Carregando acesso ao contrato. Deseja continuar?')) {
                                    $.ajax({
                                        type: "POST",
                                        url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
                                        data: ({
                                            'cod_evento': codEvento,
                                            'cod_inscrito': codInscrito,
                                            'c': 'GerenciarLogin',
                                            'm': 'carregarParticipacao',
                                            '_submit_check': '1'
                                        }),
                                        dataType: "json",
                                        async: true,
                                        cache: false,
                                        beforeSend: function() {
                                            startLoading();
                                        },
                                        success: function(data) {
                                            processJsonSemFade(data);
                                            if (data.statusMessage) {
                                                window.open(data.url, '_self');
                                            }
                                        },
                                        complete: function() {
                                            stopLoading();
                                        }
                                    });
                                }
                            } else {
                                alert('Código do evento não foi informado!');
                            }
                        }
                    </script>
                <?php
                }
            }
        }
        if($listouAlgumEventoInscrito==0){
            echo "Nenhum projeto/evento/curso encontrado.";
        }
        echo "</ul>";
    } catch (Exception $exc) {
        //echo $exc->getMessage();
    }      
// }
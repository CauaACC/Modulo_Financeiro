<?php
// if($_SERVER['REMOTE_ADDR']=='177.82.0.28'){
    try{
        $objInscrito->carregarListaEventosByCpfInscritoApto($debug);
        echo "<ul>";
        foreach ($objInscrito->getListaEventos() as $objL) {
            if ($objL->getVisibilidade() == 'PUBLICO' and $objL->getCpf() == $objInscrito->getCpf()) {
            //listando apenas para quem já esta cadastrado
            // if ($objL->getVisibilidade() == 'PUBLICO' and $objL->getCpf() == $objInscrito->getCpf() and $objL->getCodInscrito()!='') {
                /**@todo aqui devemos inserir uma configuração
                 * nos eventos que se relacionam internamente */
                // if (in_array($objL->getCodEvento(), [42,44,50,73])) {
                if (in_array($objL->getCodEvento(), [80,82,88])) {
                    $txtBotaoApto = "RECADASTRAR na ";
                    if($objL->getCodInscrito()!=''){
                        $txtBotaoApto = "ACESSAR a ";
                    }
                    ?>
                    <h4><font color=red><b>Atenção:</b></font>
                    Nessa nova estapa, para ter acesso ao contrato, é necessário efetuar o recadastramento
                    </h4>
                    <?php
                    echo '<li style="margin-top:10px;">'
                        . '<a href="javascript:void(0);" '
                        . 'onclick="carregarParticipacaoEventos(' . $objL->getCodEvento() . ',' . $objL->getCodInscrito() . ');">'
                        . 'Clique aqui para <b>'.$txtBotaoApto.'</b> Área do Contrato:<br> ' . $objL->getCodInscrito() . ' - ' . $objL->getTitulo() . '</a>';
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
                        function carregarParticipacaoEventos(codEvento, codInscrito) {
                            window.location.href = 'https://opctec.net.br/hutec/preduc-administracao-001-2025/inscricoes';
                            return true
                            // if (codInscrito) {
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
                            // } else {
                            //     alert('Código do evento não foi informado!');
                            // }
                        }
                    </script>
                <?php
                }
            }
        }
        echo "</ul>";
    } catch (Exception $exc) {
        //echo $exc->getMessage();
    }
// }
<?php
try {
    $obj = new MsgAviso();
    $obj->setCodPerfil($objInscrito->getCodPerfil());
    $obj->setCodInscrito($objInscrito->getCodInscrito());
    $obj->carregarListaMsgAvisosForAreaInscrito($debug);
    $lista = $obj->getListaMsgAvisos($debug);
    $ii = 0;
    $total_lido = 0;

    if (empty($lista)) {
        throw new Exception("Não possui Avisos cadastradas.");
    }

    date_default_timezone_set("America/Sao_Paulo");
    $dataAtual = date("Y-m-d H:i:s");

    // CSS embutido
    ?>
    <style>
        .aviso-container {
            max-width: 800px;
            margin: 20px auto;
            font-family: Arial, sans-serif;
        }

        .aviso-header {
            background-color: #007BFF;
            color: white;
            padding: 16px 24px;
            border-radius: 6px 6px 0 0;
            font-size: 18px;
            font-weight: bold;
        }

        .aviso-box-lido {
            background-color: #f9f9f9;
            border-left: 5px solid #007BFF;
            border-radius: 0 0 6px 6px;
            padding: 16px 24px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .aviso-box-nao-lido {
            background-color: #f9f9f9;
            border-left: 5px solid orangered;
            border-radius: 0 0 6px 6px;
            padding: 16px 24px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .aviso-assunto {
            font-size: 17px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }

        .aviso-corpo {
            font-size: 15px;
            color: #555;
            white-space: pre-wrap;
        }

    </style>

    <div class="aviso-container">
    <?php
    // Verificar se inscrito pagou (para filtro exibir_para)
    $inscricaoPaga = ($objInscrito->getCodVoucher() != '' || $objInscrito->getCodIsento() != '' || $objInscrito->getTotalPagamentos() > 0 || $objInscrito->getTotalPerfisIsentos() > 0 || $objInscrito->getValorTotalPedidos() == 0);

    // Primeiro laço para contar
    foreach ($lista as $objL) {
        if ($objL->getStatus() != "DESATIVADA" &&
            !(strtotime($objL->getDataPublicacaoFim()) < strtotime($dataAtual) && strtotime($objL->getDataPublicacaoFim()) != NULL) &&
            !(strtotime($objL->getDataPublicacaoIni()) > strtotime($dataAtual))) {

            // Filtro por pagamento
            $exibirPara = $objL->getExibirPara() ?: 'TODOS';
            if ($exibirPara == 'PAGO' && !$inscricaoPaga) continue;
            if ($exibirPara == 'NAO_PAGO' && $inscricaoPaga) continue;

            // Filtro por status da inscrição
            $filtroStatus = $objL->getFiltroStatus();
            if ($filtroStatus && $filtroStatus != $objInscrito->getStatus()) continue;

            // Filtro por status do processo
            $filtroStatusProcesso = $objL->getFiltroStatusProcesso();
            if ($filtroStatusProcesso && $filtroStatusProcesso != $objInscrito->getStatusProcesso()) continue;

            $ii++;
            if ($objL->getAvisoLido() == 1) {
                $total_lido++;
            }
        }
    }

    $total_falta_ler = $ii - $total_lido;

    foreach ($lista as $objL) {
        if ($objL->getStatus() == "DESATIVADA") {
            continue;
        }

        if (strtotime($objL->getDataPublicacaoFim()) < strtotime($dataAtual) && strtotime($objL->getDataPublicacaoFim()) != NULL || strtotime($objL->getDataPublicacaoIni()) > strtotime($dataAtual)) {
            continue;
        }

        // Filtro por pagamento
        $exibirPara = $objL->getExibirPara() ?: 'TODOS';
        if ($exibirPara == 'PAGO' && !$inscricaoPaga) continue;
        if ($exibirPara == 'NAO_PAGO' && $inscricaoPaga) continue;

        $isFixado = ($objL->getFixado() == 1);
        if ($isFixado) {
            ?>
            <div class="aviso-box-nao-lido" style="border-left-color:#e74c3c;">
                <div class="aviso-assunto"><i class="fa fa-thumb-tack" style="color:#e74c3c; margin-right:5px;"></i><?php echo htmlspecialchars($objL->getAssunto()); ?></div>
                <div class="aviso-corpo"><?php echo nl2br($objL->getCorpo()); ?></div>
            </div>
            <?php
        } elseif ($objL->getAvisoLido() == 1) {
            ?>
            <div class="aviso-box-lido">
                <div class="aviso-assunto"><?php echo htmlspecialchars($objL->getAssunto()); ?></div>
                <div class="aviso-corpo"><?php echo nl2br($objL->getCorpo()); ?></div>
            </div>
            <?php
        } else {
            ?>
            <div class="aviso-box-nao-lido">
                <div class="aviso-assunto"><?php echo htmlspecialchars($objL->getAssunto()); ?></div>
                <div class="aviso-corpo"><?php echo nl2br($objL->getCorpo()); ?></div>
            </div>
            <?php
        }

        if ($objL->getAvisoLido() == '') {
            $objL->insertLigacaoInscritoAviso();
        }
    }
    ?>
    </div>

    <?php
} catch (Exception $e) {
    echo '<div style="color: red; text-align: center; font-family: Arial;">' . ($e->getMessage() ?: "Lista vazia.") . '</div>';
}
?>


<?php if(COD_EVENTO == 78099){ ?>
<li>
    <a href="<?php echo WB_URL_VIEW."?content=view/inscricao/inscricao_congresso_hall_virtual.php#inscricao" ?>">Salas e Conteúdos do Curso</a>
    <br><a href="<?php echo WB_URL_VIEW."?content=view/inscricao/inscricao_congresso_hall_virtual.php#inscricao" ?>">
    Clique aqui para ter acesso a links de outros conteúdos digitais produzidos para o curso</a>
    <br>&nbsp;
</li>
<?php } ?>    
<?php if(COD_EVENTO == 82099){ ?>
    <?php if($objInscrito->getStatusProcesso()=='DESISTENTE'){ ?>
        <div class='alert alert-danger' role='alert'>
        <p><strong>Desistência</strong></p>
        <p style='text-align: justify;'>É necessário assinar eletronicamente o termo de desistencia disponível ná área de contratos e documentos.
        </p>
        </div>
    <?php } ?>
    <?php if(in_array($objInscrito->getStatusProcesso(), ['INSCRITO','PARTICIPANDO'])){ ?>
        <div class="alert alert-warning" role="alert">
        <p><strong>Live n.4 – As atribuições de Psicologas/os na educação básica: desafios e possibilidades (publicado em: 14/03/2025)</strong></p>
        <p style="text-align: justify;">
        Live n.4 – As atribuições de Psicologas/os na educação básica: desafios e possibilidades
<BR>Profa. Dra. Marilda Goncalves Dias Facci (UEM)
<BR>Data: 13/03/2025, das 16h as 18h
<BR>Link: <a href="https://meet.google.com/uqa-kzbf-jii" target="_blank" class="highlight-link">https://meet.google.com/uqa-kzbf-jii</a>
    </p>
    </div>
    <?php } ?>
    <?php if($objInscrito->getStatusProcesso()=='INSCRITO'){ ?>
    <div class="alert alert-warning" role="alert">
        <p><strong>Convocação para Realização de Atividade (publicado em: 21/02/2025)</strong></p>

        <p style="text-align: justify;">Prezadas/os Assistentes Sociais e Psicólogas/os contratadas/os pela Fundação HUTEC, 
<br>Convocamos vocês para as nossas primeiras atividades formativas a realizar-se nos dias 24 e 26/02/25, das 16h as 18h, pelo Google Meet, a partir dos links abaixo:
    <br>
    <br>Convocação para Primeiras Atividades Formativas
    <br>Segunda-feira, 24 de fevereiro · 4:00 até 6:00pm
    <br>Link da videochamada: <a href="https://meet.google.com/szp-rckx-dru" target="_blank">https://meet.google.com/szp-rckx-dru</a>
    <br>
    <br>Convocação para Primeiras Atividades Formativas
    <br>Quarta-feira, 26 de fevereiro · 4:00 até 6:00pm
    <br>Link da videochamada: <a href="https://meet.google.com/abc-defg-hij" target="_blank">https://meet.google.com/abc-defg-hij</a>
<br>
<br>Nesta ocasião, apresentaremos com maior detalhamento a proposta de formação continuada a iniciaremos as reflexões sobre os conceitos de educação e de escola, bem como sobre as atribuições profissionais.
<br>Gostaríamos muito de contar com a presença e participação de Assistentes Sociais e Psicólogas/os concursadas/os atuantes nos NREs nas atividades de formação. 
<br>Contamos com a presença e participação de todas/os!!
                </p>               
        </div>
        <div class="alert alert-warning" role="alert">
            <p><strong>Aviso Importante: Reunião de Acolhimento (publicado em: 17/002/2025) </strong></p>

            <p style="text-align: justify;">Prezadas/os profissionais ingressantes, convocamos todas/os para a reunião de acolhimento o orientações administrativas a ser realizada no dia 21/02/25, das 9h as 11h e das 14h às 16h, pelo Google Meet. 
                <br>As/os profissionais devem ingressar nos horários compatíveis com seu horário de trabalho no NRE, podendo ser de manhã ou à tarde, conforme sua disponibilidade. 
                <br>Os conteúdos das reuniões serão os mesmos para os dois horários.
                <br>Os links para acesso às reuniões serão enviados por e-mail e também estarão disponíveis aqui na Área do Colaborador.
                <br>
                <br>REUNIÃO NRE E PROFISSIONAIS
                <br>PERÍODO DA MANHÃ
                <br>Sexta-feira, 21 de fevereiro · 9h00 até 11h00
                <br>Link da videochamada: <a href="https://meet.google.com/wrf-psfu-pdu" target="_blank">https://meet.google.com/wrf-psfu-pdu</a>
                <br>
                <br>PERÍODO DA MANHÃ
                <br>Sexta-feira, 21 de fevereiro · 14h00 até 16h00
                <br>Link da videochamada: <a href="https://meet.google.com/jgh-zoai-gex" target="_blank">https://meet.google.com/jgh-zoai-gex</a>
                </p>               
        </div>
    <?php } ?>
<?php
}
/*        <!-- <div class="alert alert-warning" role="alert">
            <p><strong>Aviso Importante</strong></p>
            <p style="text-align: justify;">Na segunda feira (17/02), vocês, profissionais selecionados para o projeto de atuação multiprofissional em formação continuada interdisciplinar e multiprofissional na educação, iniciam suas atividades nos Núcleos regionais em todo estado do Paraná.
            <br>A orientação é que vocês se apresentem em seus Núcleos de Educação às 08h30 da manhã.
            <br>A Equipe Hutec parabeniza os profissionais selecionados e deseja um bom começo de atividades à todos.</p>
        </div> -->

    <!-- <div class="aviso-box">
        <h3>Nova Atualização de Documentos</h3>
        <p>Atualizamos o contrato de aceitação, baixe a versão mais recente e assine novamente.</p>
    </div> -->
<!--<li>
    <a href="<?php echo WB_URL_VIEW."?content=view/inscricao/inscricao_congresso_hall_virtual.php#inscricao" ?>">SALAS VIRTUAIS</a>
    <br><a href="<?php echo WB_URL_VIEW."?content=view/inscricao/inscricao_congresso_hall_virtual.php#inscricao" ?>">
    Clique aqui para ter acesso</a>
    a links de outros conteúdos digitais produzidos durante o evento   
    <br>&nbsp;
</li>-->
<!-- <li>
    <b><font color=red>Atenção</font>:</b> Certificados estarão disponíveis na Área do Inscrito a partir do dia 31/08/2024 - 18h.
    <br>&nbsp;
</li> -->
<!-- <li>
<p><span style="font-size: 10pt;"><span style="font-size: 12pt;">Ol&aacute;, sejam bem-vindos ao 7 Congresso!</span><br /></span></p>
<p><span style="font-size: 10pt;">Hoje, 24/07/2024, teremos apenas atividades para os inscritos nos minicursos a partir das 08h30. </span></p>
<p><span style="font-size: 10pt;">As demais atividades serão liberadas gradativamente neste local a partir do dia 25/07/2024.</span></p>
<p><span style="font-size: 10pt;">Links serão liberados gradativamente.</span></p>
<p><span style="font-size: 10pt;"><a href="https://congressosaudepublica.org.br/programacao/" target="_blank">Para mais detalhes sobre a programa&ccedil;&atilde;o no site do congresso</a> <br /></span></p>
</li>
<br> -->
*/

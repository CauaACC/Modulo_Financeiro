<?php //if ($objEvConf->getDadosPessoaisTermoCompromisso()==1) {?>
<div class="form-group col-sm-12" style="margin-bottom:15px;margin-top:15px;padding-left: 0px;">
    <span style="text-size:18px;text-align: justify">

    <?php if (in_array($objInscrito->getCodPerfil(), [681, 688, 689, 690, 694])) { ?>
    <p>Prezadas/os Psicólogas/os e Assistentes Sociais das Equipes Multiprofissionais da SEED e Técnicas/os dos NREs</p>

    <p>O II Seminário das Equipes Multiprofissionais da Rede Estadual de Educação do Paraná tem como objetivo refletir sobre as ações profissionais desenvolvidas por profissionais do Serviço Social e da Psicologia no contexto educacional, particularmente junto aos estabelecimentos estaduais de ensino, no contexto da implementação da Lei 13.935/2019. Objetiva ainda propiciar momentos de troca de experiências profissionais e fortalecer a inserção das/os Psicólogas/os e Assistentes Social na rede estadual de educação.</p>

    <p>Este evento se apresenta como mais um momento no processo de formação continuada, organizado pela Universidade Estadual de Londrina em parceria com a Secretaria de Estado da Educação do Paraná e com a Fundação HUTEC, e preparado para que seja uma oportunidade de aprendizagens e intercâmbios presenciais nesta experiência paranaense inédita e realizada por muitos esforços e compromissos, principalmente de quem está no chão dos NREs e das escolas.</p>

    <p>O nosso Encontro se realizará nos dias 8 e 9 de julho de 2026, na Gran Faculdade (Rua Luiz Parigot de Souza, 961 - Portão, Curitiba - PR, 81070-050), tendo início sempre às 08h da manhã para aproveitarmos nosso tempo juntas/os.</p>

    <p>Durante a realização do Seminário poderá ocorrer a captação de imagem, áudio, vídeo, gravação, destinados à divulgação em geral, sem fins comerciais, tanto ao público interno ou ao público externo ao projeto.</p>
    <?php } else {?>

    <p>Prezadas/os Psicólogas/os e Assistentes Sociais das Equipes Multiprofissionais,</p>

    <p>O II Seminário das Equipes Multiprofissionais da Rede Estadual de Educação do Paraná tem como objetivo refletir sobre as ações profissionais desenvolvidas por profissionais do Serviço Social e da Psicologia no contexto educacional, particularmente junto aos estabelecimentos estaduais de ensino, no contexto da implementação da Lei 13.935/2019. Objetiva ainda propiciar momentos de troca de experiências profissionais e fortalecer a inserção das/os Psicólogas/os e Assistentes Social na rede estadual de educação.</p>

    <p>Este evento se apresenta como mais um momento no processo de formação continuada, organizado pela Universidade Estadual de Londrina em parceria com a Secretaria de Estado da Educação do Paraná e com a Fundação HUTEC, e preparado para que seja uma oportunidade de aprendizagens e intercâmbios presenciais nesta experiência paranaense inédita e realizada por muitos esforços e compromissos, principalmente de quem está no chão dos NREs e das escolas.</p>

    <p>O nosso Encontro se realizará nos dias 8 e 9 de julho de 2026, na Gran Faculdade (Rua Luiz Parigot de Souza, 961 - Portão, Curitiba - PR, 81070-050), tendo início sempre às 08h da manhã para aproveitarmos nosso tempo juntos.</p>

    <p>Para viabilizar a participação de todas/os, cada participante receberá antecipadamente o valor correspondente para custear as despesas de deslocamento, hospedagem e alimentação referente aos dias que estará em Curitiba. Os valores são distintos considerando a localização da sede do NRE onde a/o participante reside até Curitiba. Os valores foram baseados na tabela de valores de custeio para eventos já utilizada para eventos organizados pela SEED. Caso a/o participante se inscreva, receba o valor de custeio e não compareça no evento, esta/e deverá devolver o valor recebido para a Fundação HUTEC sob pena de ter seu contrato rescindido caso não efetue a devolução. Todas/os as/os participantes deverão assinar lista de presença do evento considerando que este será o documento de prestação de contas das despesas de custeio.</p>

    <p>Durante a realização do Seminário poderá ocorrer a captação de imagem, áudio, vídeo, gravação, destinados à divulgação em geral, sem fins comerciais, tanto ao público interno ou ao público externo ao projeto.</p>
    <?php } ?>
    </span>
    <span style="text-size:18px;color:red;font-weight: bold;">
        Sendo assim, para participar do evento é necessário ACEITAR TODOS OS TERMOS ABAIXO:<br>
        </span>
    <span style="text-size:18px;font-weight: bold;">
        <span style="text-size:15px;font-weight: bold;">
        <?php if (!in_array($objInscrito->getCodPerfil(), [681, 688, 689, 690, 694])) { ?>
            <br>
            <span style="color:green;display:block; margin-bottom:10px;">
                <input type="checkbox" id="aceitou_termos_recurso" required>
                No ato de aceite desta inscrição, tendo ciência de que receberei recursos financeiros previamente à realização do evento e assumo a responsabilidade em ressarcir a Fundação HUTEC em caso de ausência, justificada ou não, devolvendo todos os recursos recebidos para tal, no prazo máximo de 03 dias úteis após a data final do evento.</span>

        <?php } ?>
            <br>
            <span style="color:green;display:block; margin-bottom:10px;">
            <input type="checkbox" id="aceitou_termos_imagem" required>
                 O/A participante inscrita/o autoriza expressamente a utilização total ou parcial do seu nome, imagem e voz, capturadas durante a execução do SEMINÁRIO, a título gratuito, por qualquer meio, destinadas à divulgação, sem fins comerciais, ao público em geral e/ou apenas para uso interno, desde que não haja desvirtuamento da sua finalidade estritamente vinculada aos propósitos do PROJETO.</span>
        </span>
    </span>
</div>
<div class="form-group col-sm-12" style="padding-bottom:15px;padding-top:15px;padding-left: 0px;">
    <hr width="100%" border="1">
</div>
<?php //} ?>

<script language="JavaScript" type="text/javascript">
    $(document).ready(function() {

        const termoNaoImagem = document.getElementById("nao_aceitou_termos_imagem");
        if (termoNaoImagem) {
            termoNaoImagem.addEventListener("click", function() {
                const confirmacao = confirm("Para sair sem aceitar os termos clique em OK?");
                if (confirmacao) {
                    window.location.href = "https://opctec.net.br/hutec/132/inscricoes/index.php";
                }
            });
        }
        <?php if (!in_array($objInscrito->getCodPerfil(), [681, 688, 689, 690, 694])) { ?>
        const termoNaoRecurso = document.getElementById("nao_aceitou_termos_recurso");
        if (termoNaoRecurso) {
            termoNaoRecurso.addEventListener("click", function() {
                const confirmacao = confirm("Para sair sem aceitar os termos clique em OK?");
                if (confirmacao) {
                    window.location.href = "https://opctec.net.br/hutec/132/inscricoes/index.php";
                }
            });
        }
        <?php } ?>
        // BLOQUEAR INSCRIÇÃO SE OS TERMOS NÃO FOREM ACEITOS
        $("button[type=submit]").on("click", function (e) {
            const aceitouImagem  = $("#aceitou_termos_imagem").is(":checked");
            <?php if (!in_array($objInscrito->getCodPerfil(), [681, 688, 689, 690, 694])) { ?>
                const aceitouRecurso = $("#aceitou_termos_recurso").is(":checked");
            <?php } else { ?>
                const aceitouRecurso = true;
            <?php } ?>

             if (!aceitouRecurso || !aceitouImagem) {
                e.preventDefault(); // impede o submit
                alert("Você precisa aceitar todos os termos para concluir a inscrição.");
            }
        });
    });
</script>

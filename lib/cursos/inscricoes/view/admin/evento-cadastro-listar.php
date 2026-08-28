<?php
require_once(dirname(__FILE__) . "/../../include/config.php");
$tituloPagina = 'Filtrar Projetos/Eventos:';
$submenu = 'area_admin_menu_projetos.php';
require_once(dirname(__FILE__)."/../../view/admin/cabecalho-geral.php");

@$cod_usuario = $_SESSION['_cod_usuario'];
FWA_UsuarioLogado($cod_usuario, 'true');

$urlComplemento = "";
if ($fws_['cpf'] != '') {
    $urlComplemento = "&cpf=" . $fws_['cpf'];
}
if ($fws_['e_mail'] != '') {
    $urlComplemento .= "&e_mail=" . $fws_['e_mail'];
}
if ($fws_['cod_trabalho'] != '') {
    $urlComplemento .= "&cod_trabalho=" . $fws_['cod_trabalho'];
}
if ($fws_['pedido_id'] != '') {
    $urlComplemento .= "&pedido_id=" . $fws_['pedido_id'];
}
if ($fws_['transacao_id'] != '') {
    $urlComplemento .= "&transacao_id=" . $fws_['transacao_id'];
}
if ($fws_['id_publico'] != '') {
    $urlComplemento .= "&id_publico=" . $fws_['id_publico'];
}
if ($fws_['cod_inscrito'] != '') {
    $urlComplemento .= "&cod_inscrito=" . $fws_['cod_inscrito'];
}
if ($fws_['nome_inscrito'] != '') {
    $urlComplemento .= "&nome_inscrito=" . $fws_['nome_inscrito'];
}
if ($fws_['fone_contato'] != '') {
    $urlComplemento .= "&fone_contato=" . $fws_['fone_contato'];
}
// if ($fws_['recebe_pagamento_mensalidade'] != '') {
//     $urlComplemento .= "&recebe_pagamento_mensalidade=" . $fws_['recebe_pagamento_mensalidade'];
// }
// if ($fws_['recebe_pagamento_mensalidade'] != '') {
//     $urlComplemento .= "&recebe_pagamento_mensalidade=" . $fws_['recebe_pagamento_mensalidade'];
// }

//    &nbsp;&nbsp;&nbsp;&nbsp;

// echo "<a href='index.php?content=view/admin/evento-cadastro-incluir.php' class='button btn btn-primary btn-xs'>Novo Evento/Curso</a>";
?>
<!-- <br> -->
<form method="post" id="formListaEventos" action="<?php echo WB_URL_VIEW ?>?content=view/admin/evento-cadastro-listar.php#rsltdoListaInscritos">
    <!--        <div class="form-group col-sm-12" style="padding-left: 0px;">
            <input type="hidden" value="1" name="listar"> 
            <input type='hidden' name='content' value='<?php echo  $content ?>'>
            <input type='hidden' id='c' name='c' value=''>
            <input type='hidden' id='m' name='m' value=''>
            <input type='hidden' id='media' name='media' value='tela'>
            <input type="button" id="listarTela" value="Listar na Tela" class="btn btn-xs btn-primary"> 
            &nbsp;&nbsp;&nbsp;&nbsp;
            <input type="button" id="exportarExcel" value="Exportar para Excel" class="btn btn-xs btn-primary"> 
            &nbsp;&nbsp;&nbsp;&nbsp;
            <input type="button" value="Limpar Campos" onclick="javascript:limparCampos();" class="btn btn-xs btn-primary"> 
        </div>-->
    <!--<input type="button" value="Limpar Campos" onclick="javascript:limparCampos();" class="btn btn-xs btn-primary">-->
    <!-- <br> -->
    <!-- <label for="titulo">Filtrar Projetos/Eventos:</label> -->
    <div class="form-group col-sm-12" style="padding-left: 0px;">
        <select class='form-control' name='gestor' id='gestor' placeholder="Gestor" required autofocus>
            <!-- <option class='form_campos' value='' selected>Selecione um gestor</option> -->
            <option class='form_campos' value='-1' selected>Selecione um gestor</option>
            <?php
            if ($fws_['gestor'] == '') {
                $fws_['gestor'] = $objEv->getGestor();
            }
            if ($fws_['gestor'] == '-1') {
                $fws_['gestor'] = "";
            }
            CarregarOptionsAC::listarGestoresByCodUsuario($fws_, $debug);
            ?>
        </select>
    </div>
    <div class="form-group col-sm-2" style="padding-left: 0px;">
        <input type="text" id="projeto" name="projeto" value="<?php echo $fws_['projeto'] ?>" <?php
                                                                                                $placeHoldProjeto = "Código externo do projeto/evento";
                                                                                                if ($objEv->getGestor()) {
                                                                                                    $placeHoldProjeto = "Código no(a) " . $objEv->getGestor();
                                                                                                }
                                                                                                ?> class="form-control" placeholder="<?php echo $placeHoldProjeto; ?>" SIZE='60' maxlength='255' onBlur='trim(this);'>
    </div>
    <div class="form-group col-sm-10" style="padding-left: 0px;">
        <!--<label for="titulo">Filtrar Projetos:</label>-->
        <input type="text" id="titulo" name="titulo" value="<?php echo $fws_['titulo'] ?>" class="form-control" placeholder="Titulo do projeto" SIZE='60' maxlength='255' onBlur='trim(this);'>
    </div>
    <div class="form-group col-sm-9" style="padding-left: 0px;">
        <!--<label for="nome_inscrito">Nome Inscrito:</label>-->
        <input type="text" id="nome_inscrito" name="nome_inscrito" value="<?php echo $fws_['nome_inscrito'] ?>" class="form-control" placeholder="Nome do Inscrito" SIZE='60' maxlength='255' onBlur='trim(this);' onkeypress='javascript: return isAlfa(getKey(event));'>
    </div>
    <div class="form-group col-sm-3" style="padding-left: 0px;">
        <!--<label for="cpf">Cpf:</label>-->
        <input type="text" id="fone_contato" name="fone_contato" value="<?php echo $fws_['fone_contato'] ?>" class="form-control" placeholder="Fone do Inscrito" SIZE='60' maxlength='15' onBlur='trim(this);' onkeypress='javascript: return isNum(getKey(event));'>
    </div>
    <div class="form-group col-sm-2" style="padding-left: 0px;">
        <input type="text" id="cod_evento" name="cod_evento" value="<?php echo $fws_['cod_evento'] ?>" class="form-control" placeholder="Cód. Interno do Projeto" SIZE='60' maxlength='255' onBlur='trim(this);' onkeypress='javascript: return isNum(getKey(event));'>
    </div>
    <div class="form-group col-sm-2" style="padding-left: 0px;">
        <!--<label for="cod_inscrito">Cód. Inscrito:</label>-->
        <input type="text" id="cod_inscrito" name="cod_inscrito" value="<?php echo $fws_['cod_inscrito'] ?>" class="form-control" placeholder="Cód. do Inscrito" SIZE='60' maxlength='255' onBlur='trim(this);' onkeypress='javascript: return isNum(getKey(event));'>
    </div>
    <div class="form-group col-sm-2" style="padding-left: 0px;">
        <!--<label for="cod_inscrito">Cód. Inscrito:</label>-->
        <input type="text" id="cod_trabalho" name="cod_trabalho" value="<?php echo $fws_['cod_trabalho'] ?>" class="form-control" placeholder="Cód. do Trabalho" SIZE='60' maxlength='255' onBlur='trim(this);' onkeypress='javascript: return isNum(getKey(event));'>
    </div>
    <div class="form-group col-sm-2" style="padding-left: 0px;">
        <!--<label for="cod_inscrito">Id Público:</label>-->
        <input type="text" id="id_publico" name="id_publico" value="<?php echo $fws_['id_publico'] ?>" class="form-control" placeholder="Id Público" SIZE='60' maxlength='255' onBlur='trim(this);' onkeypress='javascript: return isNum(getKey(event));'>
    </div>
    <div class="form-group col-sm-2" style="padding-left: 0px;">
        <!--<label for="e_mail">E-mail:</label>-->
        <input type="text" id="e_mail" name="e_mail" value="<?php echo $fws_['e_mail'] ?>" class="form-control" placeholder="E-mail do Inscrito" SIZE='60' maxlength='255' onBlur='trim(this);'>
    </div>
    <div class="form-group col-sm-2" style="padding-left: 0px;">
        <!--<label for="cpf">Cpf:</label>-->
        <input type="text" id="cpf" name="cpf" value="<?php echo $fws_['cpf'] ?>" class="form-control" placeholder="Cpf do Inscrito" SIZE='60' maxlength='15' onBlur='trim(this);' onkeypress='javascript: return isNum(getKey(event));'>
    </div>
    <div class="form-group col-sm-2" style="padding-left: 0px;">
        <!--<label for="cod_inscrito">Cód. Inscrito:</label>-->
        <input type="text" id="pedido_id" name="pedido_id" value="<?php echo $fws_['pedido_id'] ?>" class="form-control" placeholder="Cód. do Pedido" SIZE='60' maxlength='255' onBlur='trim(this);' onkeypress='javascript: return isNum(getKey(event));'>
    </div>
    <div class="form-group col-sm-4" style="padding-left: 0px;">
        <!--<label for="cod_inscrito">Cód. Inscrito:</label>-->
        <input type="text" id="transacao_id" name="transacao_id" value="<?php echo $fws_['transacao_id'] ?>" class="form-control" placeholder="Transação ID" SIZE='60' maxlength='255' onBlur='trim(this);' onkeypress='javascript: return isNum(getKey(event));'>
    </div>    
    <div class="form-group col-sm-12" style="padding-left: 0px;">
        <label for="data_inscricao" class="col-sm-3">Período de Inscrição: </label>
        <div class="form-group col-sm-12" style="padding-left: 0px;">
            <div class="col-sm-3">
                <input type="date" id="data_inscricao_ini" name="data_inscricao_ini" class="form-control" value="<?php echo $fws_['data_inscricao_ini'] ?>">
            </div>
            <div class="col-sm-1"> à </div>
            <div class="col-sm-3">
                <input type="date" id="data_inscricao_fim" name="data_inscricao_fim" class="form-control" value="<?php echo $fws_['data_inscricao_fim'] ?>">
            </div>
        </div>
    </div>
    <div class="form-group col-sm-12" style="padding-left: 0px;">
        <label for="data_realizacao" class="col-sm-3">Período de Realização: </label>
        <div class="form-group col-sm-12" style="padding-left: 0px;">
            <div class="col-sm-3">
                <input type="date" id="realizacao_ini" name="realizacao_ini" class="form-control" value="<?php echo $fws_['realizacao_ini'] ?>">
            </div>
            <div class="col-sm-1"> à </div>
            <div class="col-sm-3">
                <input type="date" id="realizacao_fim" name="realizacao_fim" class="form-control" value="<?php echo $fws_['realizacao_fim'] ?>">
            </div>
        </div>
    </div>
    <div class="form-group col-sm-12" style="padding-left: 15px;">
        
        <table>
            <tr>
                <td style="font-size: small;">Recebe inscrições pagas: </td>
                <td style="font-size: small;">
                    <input type="radio" name="recebe_pagamento" class="recebe_pagamento" value="0" <?= verifItemChecked($fws_['recebe_pagamento'], array('', 0, false), false) ?> style="margin-left:10px" /> Todos

                    <input type="radio" name="recebe_pagamento" class="recebe_pagamento" value="1" <?= verifItemChecked($fws_['recebe_pagamento'], '1', false) ?> style="margin-left:10px" /> Sim

                    <input type="radio" name="recebe_pagamento" class="recebe_pagamento" value="2" <?= verifItemChecked($fws_['recebe_pagamento'], '2', false) ?> style="margin-left:10px" /> Não

                </td>
            </tr>
            <tr>
                <td style="font-size: small;">Recebe mensalidade: </td>
                <td style="font-size: small;">
                    <input type="radio" name="recebe_pagamento_mensalidade" class="recebe_pagamento_mensalidade" value="0" <?= verifItemChecked($fws_['recebe_pagamento_mensalidade'], array('', 0, false), false) ?> style="margin-left:10px"/> Todos

                    <input type="radio" name="recebe_pagamento_mensalidade" class="recebe_pagamento_mensalidade" value="1" <?= verifItemChecked($fws_['recebe_pagamento_mensalidade'], '1', false) ?> style="margin-left:10px"/> Sim

                    <input type="radio" name="recebe_pagamento_mensalidade" class="recebe_pagamento_mensalidade" value="2" <?= verifItemChecked($fws_['recebe_pagamento_mensalidade'], '2', false) ?> style="margin-left:10px" /> Não
                </td>
            </tr>
            <tr>
                <td style="font-size: small;">Envio de documento: </td>
                <td style="font-size: small;">
                    <input type="radio" name="trab_submissao" class="trab_submissao" value="0" <?= verifItemChecked($fws_['trab_submissao'], array('', 0, false), false) ?> style="margin-left:10px"/> Todos

                    <input type="radio" name="trab_submissao" class="trab_submissao" value="1" <?= verifItemChecked($fws_['trab_submissao'], '1', false) ?> style="margin-left:10px"/> Sim

                    <input type="radio" name="trab_submissao" class="trab_submissao" value="2" <?= verifItemChecked($fws_['trab_submissao'], '2', false) ?> style="margin-left:10px" /> Não
                </td>
            </tr>
        </table>
    </div>
    <div class="form-group col-sm-12" style="padding-left: 0px;">
        <input type="hidden" id="paginarMais" name="paginarMais" value="">
        <input type="hidden" value="1" name="listar">
        <input type='hidden' name='content' value='<?php echo  $content ?>'>
        <input type='hidden' id='c' name='c' value=''>
        <input type='hidden' id='m' name='m' value=''>
        <input type='hidden' id='media' name='media' value='tela'>
        <input type="button" id="listarTela" value="Listar na Tela" class="btn btn-xs btn-primary">

        <input type="button" value="Limpar Campos" onclick="javascript:limparCampos();" class="btn btn-xs btn-primary">
        <!--            &nbsp;&nbsp;&nbsp;&nbsp;
            <input type="button" id="exportarExcel" value="Exportar para Excel" class="btn btn-xs btn-primary"> 
            &nbsp;&nbsp;&nbsp;&nbsp;
            <input type="button" id="exportarXml" value="Exportar para Xml" class="btn btn-xs btn-primary"> 
            &nbsp;&nbsp;&nbsp;&nbsp;
            <input type="button" value="Limpar Campos" onclick="javascript:limparCampos();" class="btn btn-xs btn-primary"> -->
    </div>
</form>
<hr size='1' width='100%'>

<table class="table tablesorter" style="border-collapse: unset">
    <thead>
        <tr>
            <th>
                <!--              <a title="Listar todos Inscritos " 
              href="<?php echo UrlGestor($objEv, $debug); ?>?content=view/admin/consulta-dados-congresso-listagem-inscritos.php&cod_evento=<?php echo $objEv->getCodEvento() ?>" class="button btn btn-default btn-xs" target="_blank">
              <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 640 512">! Font Awesome Free 6.4.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. <path d="M144 0a80 80 0 1 1 0 160A80 80 0 1 1 144 0zM512 0a80 80 0 1 1 0 160A80 80 0 1 1 512 0zM0 298.7C0 239.8 47.8 192 106.7 192h42.7c15.9 0 31 3.5 44.6 9.7c-1.3 7.2-1.9 14.7-1.9 22.3c0 38.2 16.8 72.5 43.3 96c-.2 0-.4 0-.7 0H21.3C9.6 320 0 310.4 0 298.7zM405.3 320c-.2 0-.4 0-.7 0c26.6-23.5 43.3-57.8 43.3-96c0-7.6-.7-15-1.9-22.3c13.6-6.3 28.7-9.7 44.6-9.7h42.7C592.2 192 640 239.8 640 298.7c0 11.8-9.6 21.3-21.3 21.3H405.3zM224 224a96 96 0 1 1 192 0 96 96 0 1 1 -192 0zM128 485.3C128 411.7 187.7 352 261.3 352H378.7C452.3 352 512 411.7 512 485.3c0 14.7-11.9 26.7-26.7 26.7H154.7c-14.7 0-26.7-11.9-26.7-26.7z"/></svg></a>-->
            </th>
            <th>Sit.Inscrição</th>
            <th>Titulo do Projeto</th>
            <th>Total<br>Inscritos</th>
            <th>Vagas<br>Disponíveis</th>
            <th>Env.Documento</th>
            <th>Sel.Atividade</th>
            <!--<th>Status</th>-->
            <th>Visibilidade</th>
        </tr>
    </thead>
    <tbody>
        <?php
        try {

            $objEv = new Evento();
            $objEv->setCodUsuario($_SESSION['_cod_usuario']);
            $objEv->carregarCondicoes($fws_, $debug);
            $objEv->carregarListaEventosByIdLogin($debug);
            foreach ($objEv->getListaEventos() as $objL) {
                if ($objL->getStatus() == 'ATIVADO') {
                    $objConf = $objL->getEventoConfiguracao();
                    $atencaoAtiv = '';
                    $atencaoPerf = '';
        ?>
                    <tr>
                        <td width="200px">
                        <a style="width: 86%;text-align: left;" title="Listar Inscritos" href="<?php echo UrlView($objL, $debug); ?>?content=view/admin/consulta-dados-congresso-listagem-inscritos.php&cod_evento=<?php echo $objL->getCodEvento() ?><?php echo $urlComplemento ?>" class="button btn btn-default btn-xs" target="_blank" >
                                <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 640 512"><!--! Font Awesome Free 6.4.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. -->
                                    <path d="M144 0a80 80 0 1 1 0 160A80 80 0 1 1 144 0zM512 0a80 80 0 1 1 0 160A80 80 0 1 1 512 0zM0 298.7C0 239.8 47.8 192 106.7 192h42.7c15.9 0 31 3.5 44.6 9.7c-1.3 7.2-1.9 14.7-1.9 22.3c0 38.2 16.8 72.5 43.3 96c-.2 0-.4 0-.7 0H21.3C9.6 320 0 310.4 0 298.7zM405.3 320c-.2 0-.4 0-.7 0c26.6-23.5 43.3-57.8 43.3-96c0-7.6-.7-15-1.9-22.3c13.6-6.3 28.7-9.7 44.6-9.7h42.7C592.2 192 640 239.8 640 298.7c0 11.8-9.6 21.3-21.3 21.3H405.3zM224 224a96 96 0 1 1 192 0 96 96 0 1 1 -192 0zM128 485.3C128 411.7 187.7 352 261.3 352H378.7C452.3 352 512 411.7 512 485.3c0 14.7-11.9 26.7-26.7 26.7H154.7c-14.7 0-26.7-11.9-26.7-26.7z" />
                                </svg> Listar Inscritos</a>
                                <a style="width: 86%;text-align: left;" title="Menu Geral do Evento" href="<?php echo UrlView($objL, $debug); ?>?content=view/admin/default.php&cod_evento=<?php echo $objL->getCodEvento() ?>" class="button btn btn-default btn-xs" target="_blank">
                                    <span class="glyphicon glyphicon-list"></span> Menu Geral</a>
                            <br>
                            <a href="<?php echo $objL->getUrlOficial(); ?>" target="_blank" class="btn btn-default btn-xs" style="border-radius: 0;" title="Acessar site oficial do evento">
                                    <i class="fa fa-globe"></i></a>  
                                </a>

                            <a title="Iniciar Inscrição." href="<?php echo UrlView($objL, $debug); ?>" class="button btn btn-default btn-xs" target="_blank">
                                <span class="glyphicon glyphicon-eye-open"></span></a>
                            <?php if (FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_GERENCIAR_EVENTO, 5, false, false, $objL->getCodEvento())) { ?>
                                <a title="Alterar Evento" href="<?php echo UrlView($objL, $debug); ?>?content=view/admin/evento-cadastro-alterar.php&cod_evento=<?php echo $objL->getCodEvento() ?>" class="button btn btn-default btn-xs" target="_blank">
                                    <!--<span class="glyphicon glyphicon-folder-open"></span></a>
                            <a title="Configurar Evento" href="<?php echo UrlView($objL, $debug); ?>?content=view/admin/evento-configurar-alterar.php&cod_evento=<?php echo $objL->getCodEvento() ?>" class="button btn btn-default btn-xs">-->
                                    <span class="glyphicon glyphicon-cog"></span></a>
                            <?php } ?>

                            <!-- Botão para abrir o modal -->
                            <button title="Informação de Contato" class="btn btn-default btn-xs" style="border-radius: 0;" data-toggle="modal" data-target="#infoModal-<?php echo $objL->getCodEvento() ?>">
                            <i class="fa fa-info-circle"></i>
                            </button>
                                
<!-- Modal -->
<div id="infoModal-<?php echo $objL->getCodEvento() ?>" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Informações de Contato</h4>
            </div>
            <div class="modal-body">
                <p>Fone Privado: <?php echo $objL->getFoneOrganizador(); ?></p>
                <p>E-mail Privado: <?php echo $objL->getEmailOrganizador(); ?></p>
                <p>Fone Público: <?php echo $objL->getFoneContato(); ?></p>
                <p>E-mail Público: <?php echo $objL->getEmailContato(); ?></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>



                        </td>
                        <td>
                            <?php
                            $corTextAviso = '#000000';
                            if ($objL->getStatus() != 'ATIVADO') {
                                $corTextAviso = '#8C1F14';
                                echo "<font color=" . $corTextAviso . ">Desativado</font>";
                            } else {
                                if (testaPeriodo($objConf->getDataInscricaoIni(), $objConf->getDataInscricaoFim())) {
                                    //echo "<br><font color=green>Dentro do Período</font>";
                                    if ($objConf->getProcessoInscricaoSuspenso() == 1) {
                                        $corTextAviso = '#8C1F14';
                                        echo "<font color=" . $corTextAviso . ">Suspensa</font>";
                                    } else {
                                        if ($objConf->getProcessoInscricao() == 1) {
                                            $corTextAviso = '#2D937E';
                                            echo "<font color=" . $corTextAviso . ">Ativada</font>";
                                        } else {
                                            // Dentro do periodo, nao suspensa, mas ProcessoInscricao != 1:
                                            // inscricao esta desabilitada explicitamente.
                                            $corTextAviso = '#8C1F14';
                                            echo "<font color=" . $corTextAviso . ">Fechada</font>";
                                        }
                                    }
                                } else {
                                    $corTextAviso = '#8C1F14';
                                    echo "<br><font color=" . $corTextAviso . ">";
                                    echo testaNaoIniciadaOuJaEncerrada($objConf->getDataInscricaoIni(), $objConf->getDataInscricaoFim());
                                    echo "</font>";
                                }
                            }
                            ?>
                        </td>
                        <td width="40%" style="padding-bottom:10px;">
                            <?php
                            //                echo '<a href="'.$PROTOCOLO.'://'.FWA_Dominio().'/'.$prefixoEvento.'/index.php">';
                            $titulo = $objL->getTitulo();
                            if ($objL->getProjeto() != '') {
                                $titulo = $objL->getProjeto() . " - " . $titulo;
                            }
                            echo "<span style='font-size:14px'>" . $titulo . "</span>";
                            //                echo '</a>';
                            if ($objConf->getEmitirCertificado() == 1) {
                                echo "<br><font color=#2D937E>Certificado Liberado</font>";
                                if ($objConf->getCertificadoDatIni() != '') {
                                    echo " - <font color=#2D937E>A partir de:" . formatDateView($objConf->getCertificadoDatIni()) . "</font>";
                                }
                            }
                            echo "<br>Realização: <span style='color:" . $corTextAviso . "'>"
                                . "" . formatDateView($objConf->getRealizacaoIni()) . " até " . formatDateView($objConf->getRealizacaoFim()) . "</span>";
                            echo "<br>Inscrições: <span style='color:" . $corTextAviso . "'>"
                                . "" . formatDateView($objConf->getDataInscricaoIni()) . " até " . formatDateView($objConf->getDataInscricaoFim()) . "</span>";
                            if ($objConf->getTrabSubmissao()) {
                                $tipoEnvio = trim($objConf->getTrabAnexoTipo()) != '' ? $objConf->getTrabAnexoTipo() : 'Trabalho/Documentos';
                                echo "<br>Envio de " . $tipoEnvio . ": <span style='color:" . $corTextAviso . "'>"
                                    . "" . formatDateView($objConf->getTrabSubmissaoDatIni()) . " até " . formatDateView($objConf->getTrabSubmissaoDatFim()) . "</span>";
                            }
                            if ($objConf->getRecebePagamentoMensalidade()) {
                                $dataUltima = $objConf->getMensDataUltima();
                                $cor = (strtotime($dataUltima) >= strtotime(date('Y-m-d'))) ? 'green' : $corTextAviso;
                                $cor = ($objConf->getMensAtiva() === '1') ? $cor : 'gray';
                                echo "<br>Mensalidades: <span style='color:" . $cor . "'>"
                                    . $objConf->getMensQtd() . " parcelas, última em " 
                                    . formatDateView($objConf->getMensDataUltima()) . "</span>";
                            }
                            /** @todo listar os inscritos encontrados para esse evento aqui */
                            try {
                                $objInscrito = new Inscrito();
                                $condicoes = $objInscrito->carregarCondicoes($fws_, $debug);
                                if ($condicoes['mostrarInscritos']!=1) {
                                    throw new Exception('');
                                }
                                if (empty($condicoes)) {
                                    throw new Exception("Nenhuma condição encontrada para a pesquisa.");
                                }
                                $condicoes['cod_evento'] = $objL->getCodEvento();
                                $objInscrito->carregarListaInscritos($condicoes, $debug);
                                if (!$objInscrito->getListaInscritos()) {
                                    throw new Exception('Nenhum inscrito foi localizado de acordo com o filtro defindo acima.');
                                }
                                echo "<br><strong>Inscritos localizados:</strong>";
                                foreach ($objInscrito->getListaInscritos() as $objLI) {
                                    if ($objLI->getStatus() != 'EXCLUÍDA') {
                                        echo '<br>';
                                        // CarregarBotoesAC::btnGerenciarInscrito($fws_, $objLI, $debug);    
                                        
                                        echo '<a href="'.WB_URL.'/'.$objLI->getGestor().'/'.$objLI->getCodEvento().'/inscricoes/index.php?content=view/admin/consulta-dados-congresso-listagem-inscritos.php&cod_evento='.$objLI->getCodEvento().'&cod_inscrito='.$objLI->getCodInscrito().'" target="_blank">';
                                        echo ' -> ';
                                        echo $objLI->getCodInscrito() . " - " . $objLI->getNomeInscrito();
                                        echo '</a>';
                                    }
                                }
                            } catch (Exception $exc) {
                                echo "<br><font color=red>".$exc->getMessage()."</font>";
                            }
                            ?>
                        </td>
                        <td>
                            (<a class='link' title="Listar inscritos neste evento" href='<?php echo UrlView($objL, $debug); ?>?content=view/admin/consulta-dados-congresso-listagem-inscritos.php&cod_evento=<?php echo $objL->getCodEvento() ?>'>
                                <?php echo numberPad($objL->getTotalInscritos(), 3) ?></a>)
                        </td>
                        <td>
                            <?php
                            $difPerf = intval($objL->getTotalVagasPerfis()) - intval($objL->getTotalInscritosPerfis());
                            if (0 <= $difAtiv and $difAtiv <= 10) {
                                $atencaoPerf = 'text-danger';
                            }
                            ?>
                            <?php if ($objL->getTotalVagasPerfis()) { ?>
                                <a class="link <?php echo $atencaoPerf ?>" title="Listar Perfis" href="<?php echo UrlView($objL, $debug); ?>?content=view/admin/perfil-cadastro-ok.php&cod_evento=<?php echo $objL->getCodEvento() ?>">
                                    <?php echo $difPerf ?> em Perfil(s)</a><br>
                            <?php } ?>
                            <?php
                            $difAtiv = intval($objL->getTotalVagasAtividades()) - intval($objL->getTotalInscritosAtividades());
                            if (0 <= $difAtiv and $difAtiv <= 10) {
                                $atencaoAtiv = 'text-danger';
                            }
                            ?>
                            <?php if ($objL->getTotalVagasAtividades()) { ?>
                                <a class="link <?php echo $atencaoAtiv ?>" title="Listar Atividades" href="<?php echo UrlView($objL, $debug); ?>?content=view/admin/oficina-cadastro-ok.php&cod_evento=<?php echo $objL->getCodEvento() ?>">
                                    <?php echo $difAtiv ?> em Atividade(s)</a><br>
                            <?php } ?>
                        </td>
                        <td><?php if ($objConf->getTrabSubmissao() == 1) {
                                $tipoEnvioTd = trim($objConf->getTrabAnexoTipo()) != '' ? $objConf->getTrabAnexoTipo() : 'Trabalho';
                                echo $tipoEnvioTd;
                            } ?></td>
                        <td><?php if ($objConf->getAtividade() == 1) {
                                echo "Sim";
                            } ?></td>
                        <!--<td><?php echo $objL->getDesStatus() ?></td>-->
                        <td><?php echo textoMaiusculoMinusculo($objL->getVisibilidade()) ?></td>
                    </tr>
        <?php
                }
            }
        } catch (Exception $exc) {
            echo "<tr><td colspan=\"5\">" . $exc->getMessage() . "</td></tr>";
        }
        echo "</table>";
        ?>
        <link rel="stylesheet" href="resources/js/jquery.tablesorter/themes/blue/style.css" type="text/css" id="" media="print, projection, screen" />
        <script type="text/javascript" src="resources/js/jquery.tablesorter/jquery.tablesorter.js"></script>
        <script language="JavaScript" type="text/javascript">
            $(document).ready(function() {
                var $table = $("table.tablesorter");
                if ($table.find('tbody tr').length > 1) {
                    $table.tablesorter({
                        widgets: ['zebra'],
                        sortList: [
                            [1, 0]
                        ] // Ordena pela quinta coluna (índice 4)
                    });
                }
                $('#listarTela').click(function() {
                    submitConsulta();
                });
                $("input[type='text']").keypress(function(e) {
                    if (e.which == 13) {
                        submitConsulta();
                    }
                });
            });

            function submitConsulta() {
                //    var codEvento =  $('#cod_evento').val();
                //    if(codEvento!='-1'){
                //        $('#formListaInscritos').attr('action', '<?php echo WB_URL_DOMINIO . '/' . WB_GRUPO ?>/'+codEvento+'/inscricoes/index.php?content=view/admin/consulta-dados-congresso-listagem-inscritos.php');
                //    }else{
                //        $('#formListaInscritos').attr('action', '<?php echo WB_URL_DOMINIO . '/' . WB_GRUPO . '/' . COD_EVENTO ?>/inscricoes/index.php?content=view/admin/consulta-dados-congresso-listagem-inscritos.php');
                //    }
                $('#c').val('');
                $('#m').val('');
                $("input[name=media]").val(['tela']);
                $('#formListaEventos').submit();
            }

            function limparCampos() {
                //Limpando campos
                $('#gestor').val('');
                $('#projeto').val('');
                $('#titulo').val('');
                $('#nome_inscrito').val('');
                $('#cpf').val('');
                $('#cod_evento').val('');
                $('#cod_inscrito').val('');
                $('#id_publico').val('');
                $('#pedido_id').val('');
                $('#transacao_id').val('');
                $('#cod_trabalho').val('');
                $('#fone_contato').val('');
                $('#realizacao_ini').val('');
                $('#realizacao_fim').val('');
                $('#data_inscricao_ini').val('');
                $('#data_inscricao_fim').val('');
                $('.recebe_pagamento').prop('checked', false);
                $('.recebe_pagamento_mensalidade').prop('checked', false);
                $('.trab_submissao').prop('checked', false);

                $('#e_mail').val('');
            }
        </script>
    </div><!-- panel-body -->
</div><!-- panel -->
</div><!-- panel-group -->